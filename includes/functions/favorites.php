<?php
add_action( 'sunshine_after_content', 'sunshine_favorites_add_to_favorites_js', 999 );
function sunshine_favorites_add_to_favorites_js() {
	if ( !SPC()->get_option( 'disable_favorites' ) ) {
?>
	<script>
    // Sunshine add image to favorites
	jQuery( document ).ready(function($) {
		jQuery( 'a.sunshine--add-to-favorites[data-image-id]' ).on( 'click', function() {
            var sunshine_favorite_image_id = jQuery( this ).data( 'image-id' );
    		$.ajax({
    		  	type: 'POST',
    		  	url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
    		  	data: {
    		  		action: 'sunshine_add_to_favorites',
    				image_id: sunshine_favorite_image_id,
					security: "<?php echo wp_create_nonce( 'sunshine-add-favorite' ); ?>"
    			},
    		  	success: function( result, textStatus, XMLHttpRequest) {
					if ( result.success ) {
						if ( result.data.action == 'ADD' ) {

							$( document ).trigger( 'sunshine_add_favorite' );

	    		  			$( '#sunshine--image-' + sunshine_favorite_image_id + ', .sunshine--image-' + sunshine_favorite_image_id ).addClass( 'sunshine--image--is-favorite' );
	                        $( '#sunshine--action-menu li.sunshine--favorites span.sunshine--image-menu--name' ).html( "<?php echo esc_js( 'Remove from favorites', 'sunshine-photo-cart' ); ?>" );
	    					if ( !$( '.sunshine--main-menu .sunshine--favorites .sunshine--favorites--count' ).length ) {
	                            $( '.sunshine--main-menu .sunshine--favorites' ).append( '<span class="sunshine--count sunshine--favorites--count">' + result.data.count + '</span>' );
	                        }

	    				} else if ( result.data.action == 'DELETE' ) {

							$( document ).trigger( 'sunshine_delete_favorite' );

	    		  			$( '#sunshine--image-' + sunshine_favorite_image_id + ', .sunshine--image-' + sunshine_favorite_image_id ).removeClass( 'sunshine--image--is-favorite' );
	    					$( '#sunshine--action-menu li.sunshine--favorites span.sunshine--action-menu--name' ).html( "<?php echo esc_js( 'Add to favorites', 'sunshine-photo-cart' ); ?>" );
	                        if ( result.data.count == 0 ) {
	                            $( '.sunshine--main-menu .sunshine--favorites--count' ).remove();
	                        }
	    					<?php if ( is_sunshine_page( 'favorites' ) ) { ?>
	    						$( '#sunshine--image-' + sunshine_favorite_image_id ).fadeOut();
	    					<?php } ?>

	    				}
	                    $( '#sunshine--action-menu li.sunshine--favorites a, #sunshine--image-' + sunshine_favorite_image_id + ' li.sunshine--favorites a' ).toggleClass( 'sunshine-favorite' );
	                    $( '.sunshine--favorites--count' ).html( parseInt( result.data.count ) );
					}
    		  	},
    		  	error: function( MLHttpRequest, textStatus, errorThrown ) {
    				alert( '<?php echo esc_js( 'Sorry, there was an error with your request', 'sunshine-photo-cart' ); ?> ' + errorThrown + MLHttpRequest + textStatus );
    		  	}
    		});
			return false;
		});
	});
	</script>
<?php
	}
}

add_filter( 'sunshine_account_require_login_message', 'sunshine_account_require_login_message_favorites', 10, 2 );
function sunshine_account_require_login_message_favorites( $message, $vars ) {

	if ( !empty( $vars['after'] ) && $vars['after'] == 'sunshine_add_favorite' ) {
		$message = __( 'An account is required to save your favorites so they can be tracked across multiple website visits.', 'sunshine-photo-cart' );
	}

	return $message;

}

add_action( 'sunshine_after_login', 'sunshine_after_login_add_to_favorites' );
add_action( 'sunshine_after_signup', 'sunshine_after_login_add_to_favorites' );
function sunshine_after_login_add_to_favorites( $vars ) {
	$image_id = SPC()->session->get( 'add_to_favorites' );
	if ( $image_id ) {
		SPC()->customer->add_favorite( $image_id );
		SPC()->session->delete( 'add_to_favorites' );
		SPC()->notices->add( __( 'Image added to favorites', 'sunshine-photo-cart' ) );
		$image = new SPC_Image( $image_id );
		SPC()->log( $image->get_name() . ' in ' . $image->get_gallery()->get_name() . ' added to favorites by ' . SPC()->customer->get_name() );
	}
}

add_action( 'wp_ajax_sunshine_add_to_favorites', 'sunshine_add_to_favorites' );
function sunshine_add_to_favorites() {
	if ( !is_user_logged_in() || !wp_verify_nonce( $_REQUEST['security'], 'sunshine-add-favorite' ) ) {
		return false;
		exit;
	}
    $action = '';
	if ( isset( $_POST['image_id'] ) ) {
        $image_id = intval( $_POST['image_id'] );
		$favorites = SPC()->customer->get_favorite_ids();
		$image = new SPC_Image( $image_id );
		if ( is_array( $favorites ) && in_array( $image_id, $favorites ) ) {
			$count = SPC()->customer->delete_favorite( $image_id );
            $action = 'DELETE';
			SPC()->log( $image->get_name() . ' in ' . $image->get_gallery()->get_name() . ' removed from favorites by ' . SPC()->customer->get_name() );
		} else {
            $count = SPC()->customer->add_favorite( $image_id );
			$action = 'ADD';
			SPC()->log( $image->get_name() . ' in ' . $image->get_gallery()->get_name() . ' added to favorites by ' . SPC()->customer->get_name() );
		}
		wp_send_json_success( array( 'action' => $action, 'count' => SPC()->customer->get_favorite_count() ) );
	}
	wp_send_json_error();
}

add_action( 'before_delete_post', 'sunshine_cleanup_favorites' );
function sunshine_cleanup_favorites( $post_id ) {
	global $wpdb, $post_type;
	if ( $post_type != 'sunshine-gallery' ) return;
	$args = array(
		'post_type' => 'attachment',
		'post_parent' => $post_id,
		'nopaging' => true
	);
	$images = get_posts( $args );
	foreach ( $images as $image )
		$image_ids[] = $image->ID;
	if ( !empty( $image_ids ) ) {
		$delete_ids = implode( $image_ids, ', ' );
		$query = "
			DELETE FROM $wpdb->usermeta
			WHERE meta_key = 'sunshine_favorite'
			AND meta_value in ($delete_ids)
		";
		$wpdb->query( $query );
	}
}
add_action( 'init', 'sunshine_favorites_clear', 100 );
function sunshine_favorites_clear() {
	global $sunshine;
	if ( isset( $_GET['clear_favorites'] ) && isset( $_GET['nonce'] ) && wp_verify_nonce( $_GET['nonce'], 'sunshine_clear_favorites' ) ) {
        SPC()->customer->clear_favorites();
		SPC()->notices->add( __( 'Favorites cleared', 'sunshine-photo-cart' ) );
		wp_redirect( sunshine_url( 'favorites' ) );
		exit;
	}
}

add_action( 'init', 'sunshine_favorites_submit', 110 );
function sunshine_favorites_submit() {
	global $sunshine, $current_user;
	if ( isset( $_GET['submit_favorites'] ) && isset( $_GET['nonce'] ) && wp_verify_nonce( $_GET['nonce'], 'sunshine_submit_favorites' ) ) {

		$heading = sprintf( __( '%s has submitted their favorites', 'sunshine-photo-cart' ), SPC()->customer->get_name(), admin_url( 'user-edit.php?user_id=' . SPC()->customer->ID . '#sunshine--favorites' ) );

		$favorites = SPC()->customer->get_favorites();
		if ( empty( $favorites ) ) {
			return;
		}

		if ( SPC()->get_option( 'favorite_notifications' ) ) {
            $admin_emails = explode( ',', SPC()->get_option( 'favorite_notifications' ) );
        } else {
            $admin_emails = array( get_bloginfo( 'admin_email' ) );
        }
		foreach ( $admin_emails as $admin_email ) {
			$args = array(
				'heading' => sprintf( __( '%s has submitted %s favorites', 'sunshine-photo-cart' ), SPC()->customer->get_name(), count( $favorites ) ),
				'favorites' => $favorites,
				'customer' => SPC()->customer
			);
            $email = new SPC_Email( 'favorites', trim( $admin_email ), sprintf( __( '%s has submitted %s favorites', 'sunshine-photo-cart' ), SPC()->customer->get_name(), count( $favorites ) ), $args );
			$result = $email->send();
        }

		SPC()->notices->add( __( 'Your favorite images have been sent','sunshine-photo-cart' ) );
		wp_redirect( sunshine_url( 'favorites' ) );
		exit;
	}
}


add_filter( 'user_row_actions', 'sunshine_user_favorites_link_row',5,2 );
function sunshine_user_favorites_link_row( $actions, $user ) {
	if ( current_user_can( 'sunshine_manage_options', $user->ID ) ) {
		$actions['sunshine_favorites'] = '<a href="user-edit.php?user_id=' . $user->ID . '#sunshine--favorites">' . __( 'Favorites', 'sunshine-photo-cart' ) . '</a>';
	}
	return $actions;
}

add_action( 'show_user_profile', 'sunshine_admin_user_show_favorites' );
add_action( 'edit_user_profile', 'sunshine_admin_user_show_favorites' );
function sunshine_admin_user_show_favorites( $user ) {
	if ( current_user_can( 'manage_options' ) ) {
		$favorites = get_user_meta( $user->ID, 'sunshine_favorite' );
		if ( $favorites ) {
			echo '<h3 id="sunshine--favorites">'.__( 'Sunshine Favorites','sunshine-photo-cart' ).' ('.count( $favorites ).')</h3>';
			?>
				<p><a href="#sunshine--favorites-file-list" id="sunshine--favorites-file-list-link"><?php _e( 'Image File List', 'sunshine-photo-cart' ); ?></a></p>
				<div id="sunshine--favorites-file-list" style="display: none;">
				<?php
				foreach ( $favorites as $image_id ) {
					$image_file_list[$image_id] = get_post_meta( $image_id, 'sunshine_file_name', true );
				}
				foreach ( $image_file_list as &$file ) {
					$file = str_replace( array( '.jpg','.JPG' ), '', $file );
				}
			?>
					<textarea rows="4" cols="50" onclick="this.focus();this.select()" readonly="readonly"><?php echo esc_textarea( join( ', ', $image_file_list ) ); ?></textarea>
					<p><?php _e( 'Copy and paste the file names above into Lightroom\'s search feature (Library filter) to quickly find and create a new collection to make processing this order easier. Make sure you are using the "Contains" (and not "Contains All") search parameter.', 'sunshine-photo-cart' ); ?></p>
				</div>
				<script>
				jQuery(document).ready(function($){
					$('#sunshine--favorites-file-list-link').click(function(){
						$('#sunshine--favorites-file-list').slideToggle();
						return false;
					});
				});
				</script>

			<?php
			echo '<ul>';
			foreach ( $favorites as $favorite ) {
				$attachment = get_post( $favorite );
				$image = wp_get_attachment_image_src( $attachment->ID, 'sunshine-thumbnail' );
				$url = get_permalink( $attachment->ID );
?>
			<li style="list-style: none; float: left; margin: 0 20px 20px 0;">
				<a href="<?php echo $url; ?>"><img src="<?php echo $image[0]; ?>" height="100" alt="" /></a><br />
				<?php echo get_the_title( $attachment->ID ); ?>
			</li>
		<?php }
			echo '</ul><br clear="all" />';
		}
	}

}


add_action( 'wp', 'sunshine_favorites_check_availability' );
function sunshine_favorites_check_availability() {
	if ( empty( SPC()->customer ) || empty( SPC()->customer->get_favorite_ids() ) || !is_sunshine_page( 'favorites' ) ) {
        return;
    }
	$removed_items = false;
	foreach ( SPC()->customer->get_favorite_ids() as $favorite_id ) {
		$image = get_post( $favorite_id );
		$image_url = get_attached_file( $favorite_id );
		if ( !$image || !file_exists( $image_url ) ) {
			SPC()->customer->delete_favorite( $favorite_id );
			$removed_items = true;
		}
	}
	if ( $removed_items ) {
		SPC()->notices->add( __( 'Images in your favorites have been removed because they are no longer available', 'sunshine-photo-cart' ) );
		wp_redirect( sunshine_url( 'favorites' ) );
		exit;
	}
}

/**************************
	EMAIL AUTOMATION
	Add trigger
**************************/
add_filter( 'sunshine_email_triggers', 'sunshine_favorites_email_triggers' );
function sunshine_favorites_email_triggers( $triggers ) {
	$triggers['favorite'] = __( 'After user adds image to favorites', 'sunshine-photo-cart' );
	return $triggers;
}
