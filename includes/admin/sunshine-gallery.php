<?php
class Sunshine_Admin_Meta_Boxes_Gallery extends Sunshine_Admin_Meta_Boxes {

	protected $post_type = 'sunshine-gallery';

	public function set_meta_boxes( $meta_boxes ) {
		$meta_boxes['sunshine-gallery'] = array(
			array(
				'id' => 'sunshine-gallery-options', // Unique box id
				'name' => __( 'Gallery Options', 'sunshine-photo-cart' ), // Label/name
				'context' => 'advanced', // normal/side/advanced
				'priority' => 'high', // priority
			)
		);
		return $meta_boxes;
	}

	public function set_options( $options ) {

		$price_level_terms = get_terms(array(
			'taxonomy' => 'sunshine-product-price-level',
			'hide_empty' => false
		));
		$price_levels = array();
		foreach ( $price_level_terms as $price_level ) {
			$price_levels[ $price_level->term_id ] = $price_level->name;
		}

		if ( SPC()->has_addon( 'price-levels' ) ) {
			$price_levels_after = '<a href="' . admin_url( 'edit-tags.php?taxonomy=sunshine-product-price-level&post_type=sunshine-product' ) . '" target="_blank">' . __( 'Manage price levels', 'sunshine-photo-cart' ) . '</a>';
		} else {
			$price_levels_after = '<a href="' . admin_url( 'admin.php?page=sunshine_addons' ) . '" class="button" target="_blank">' . __( 'Upgrade to manage more price levels', 'sunshine-photo-cart' ) . '</a>';
		}

		$options['sunshine-gallery-options'] = array(
			'0' => array(
				'id' => 'images',
				'name' => __( 'Images', 'sunshine-photo-cart' ) . ' (<span class="sunshine-gallery-image-count">0</span>)',
				'icon' => SUNSHINE_PHOTO_CART_PATH . 'assets/images/icons/galleries.svg',
				'fields' => array(
					array(
						'id' => 'gallery_images',
						//'name' => __( 'Images', 'sunshine-photo-cart' ),
						'type' => 'gallery_images',
					),
				),
			),
			'1000' => array(
				'id' => 'general',
				'name' => __( 'General Options', 'sunshine-photo-cart' ),
				'icon' => SUNSHINE_PHOTO_CART_PATH . 'assets/images/icons/settings.svg',
				'fields' => array(
					array(
						'id' => 'status',
						'name' => __( 'Gallery Type', 'sunshine-photo-cart' ),
						'type' => 'radio',
						'options' => array(
							'default' => __( 'Default', 'sunshine-photo-cart' ),
							'password' => __( 'Password Protected', 'sunshine-photo-cart' ),
							'private' => __( 'Private (only specified users)', 'sunshine-photo-cart' ),
						)
					),
					array(
						'id' => 'password',
						'name' => __( 'Password', 'sunshine-photo-cart' ),
						'type' => 'text',
						'conditions' => array(
			                array(
			                    'field' => 'status',
			                    'compare' => '==',
			                    'value' => 'password',
			                    'action' => 'show'
			                )
			            )
					),
					array(
						'id' => 'password_hint',
						'name' => __( 'Password Hint', 'sunshine-photo-cart' ),
						'type' => 'text',
						'conditions' => array(
			                array(
			                    'field' => 'status',
			                    'compare' => '==',
			                    'value' => 'password',
			                    'action' => 'show'
			                )
			            ),
						'description' => __( 'Optionally include a hint for the password', 'sunshine-photo-cart' )
					),
					array(
						'id' => 'private_users',
						'name' => __( 'Allowed Users', 'sunshine-photo-cart' ),
						'type' => 'select_multi',
						'select2' => true,
						'options' => 'users',
						'conditions' => array(
			                array(
			                    'field' => 'status',
			                    'compare' => '==',
			                    'value' => 'private',
			                    'action' => 'show'
			                )
			            )
					),
					array(
						'id' => 'access_type',
						'name' => __( 'Access Type', 'sunshine-photo-cart' ),
						'type' => 'radio',
						'options' => array(
							'' => __( 'Default', 'sunshine-photo-cart' ),
							'email' => __( 'Provide email address', 'sunshine-photo-cart' ),
							'url' => __( 'Direct URL', 'sunshine-photo-cart' ),
						)
					),
					array(
						'id' => 'end_date',
						'name' => __( 'Expiration', 'sunshine-photo-cart' ),
						'type' => 'date_time',
						'description' => __( 'When will this gallery expire and no longer be accessible', 'sunshine-photo-cart' )
					),
					array(
						'id' => 'image_comments',
						'name' => __( 'Allow image comments', 'sunshine-photo-cart' ),
						'type' => 'checkbox',
						'description' => __( 'Allow users to make comments on images', 'sunshine-photo-cart' )
					),
					array(
						'id' => 'image_comments_approval',
						'name' => __( 'Comments require approval', 'sunshine-photo-cart' ),
						'type' => 'checkbox',
						'description' => __( 'Should comments require approval before being shown', 'sunshine-photo-cart' ),
						'conditions' => array(
							array(
								'field' => 'image_comments',
								'compare' => '==',
								'value' => '1',
								'action' => 'show'
							)
						)
					),
					array(
						'id' => 'disable_favorites',
						'name' => __( 'Disable Favorites', 'sunshine-photo-cart' ),
						'type' => 'checkbox',
					),
					array(
						'id' => 'disable_sharing',
						'name' => __( 'Disable Sharing', 'sunshine-photo-cart' ),
						'type' => 'checkbox',
					),
				)
			),
			'2000' => array(
				'id' => 'products',
				'name' => __( 'Products', 'sunshine-photo-cart' ),
				'icon' => SUNSHINE_PHOTO_CART_PATH . 'assets/images/icons/product.svg',
				'fields' => array(
					array(
						'id' => 'disable_products',
						'name' => __( 'Disable Products', 'sunshine-photo-cart' ),
						'type' => 'checkbox',
						'description' => __( 'Users will not be able to purchase any products for this gallery', 'sunshine-photo-cart' ),
						'conditions' => array(
							array(
								'field' => 'disable_products',
								'compare' => '==',
								'value' => '1',
								'action' => 'hide',
								'action_target' => '#sunshine-admin-meta-box-tab-fields-products tr:not(#sunshine-meta-fields-disable_products)'
							)
						)
					),
					array(
						'id' => 'price_level',
						'name' => __( 'Price Level', 'sunshine-photo-cart' ),
						'type' => 'select',
						'options' => $price_levels,
						'after' => $price_levels_after
					),
				)
			),
			'3000' => array(
				'id' => 'email',
				'name' => __( 'Emails', 'sunshine-photo-cart' ),
				'icon' => SUNSHINE_PHOTO_CART_PATH . 'assets/images/icons/settings.svg',
				'fields' => array(
					array(
						'id' => 'provided_emails',
						'name' => __( 'Provided Emails', 'sunshine-photo-cart' ),
						'type' => 'gallery_emails',
					),
				)
			)


		);

		return $options;
	}

	public function enqueue( $page ) {

		if ( get_post_type() != 'sunshine-gallery' ) {
			return;
		}
		//wp_enqueue_script( 'jquery-ui' );
		//wp_enqueue_style( 'sunshine-jquery-ui' );
		//wp_enqueue_script( 'jquery-ui-datepicker' );
		//wp_enqueue_style( 'jquery-ui-theme', SUNSHINE_PHOTO_CART_URL . 'assets/jqueryui/smoothness/jquery-ui-1.9.2.custom.css' );
		//wp_enqueue_style( 'jquery-ui-theme-sunshine', SUNSHINE_PHOTO_CART_URL . 'assets/jqueryui/jquery-ui.theme.css' );
		wp_enqueue_script( 'select2' );
		wp_enqueue_style( 'select2' );
		wp_enqueue_script( 'plupload-all' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		//wp_enqueue_script( 'ajaxq', SUNSHINE_PHOTO_CART_URL . 'assets/js/ajaxq.js' );
		wp_enqueue_media();

	}

}

$sunshine_admin_meta_boxes_gallery = new Sunshine_Admin_Meta_Boxes_Gallery();


add_action( 'admin_head', 'sunshine_remove_add_media' );
function sunshine_remove_add_media(){
	if ( get_post_type() == 'sunshine-gallery' ) {
		remove_action( 'media_buttons', 'media_buttons' );
	}
}

add_filter( 'manage_edit-sunshine-gallery_columns', 'sunshine_galleries_columns', 10 );
function sunshine_galleries_columns( $columns ) {
	unset( $columns['date'] );
	unset( $columns['title'] );
	$columns['featured_image'] = '';
	$columns['title'] = __( 'Title' );
	$columns['expires'] = __( 'Expires', 'sunshine-photo-cart' );
	$columns['images'] = __( 'Images', 'sunshine-photo-cart' );
	$columns['date'] = __( 'Date' );
	return $columns;
}

add_action( 'manage_sunshine-gallery_posts_custom_column', 'sunshine_galleries_columns_content', 99, 2 );
function sunshine_galleries_columns_content( $column, $post_id ) {
	global $post;
	$gallery = new SPC_Gallery( $post );
	switch( $column ) {
		case 'featured_image':
			$gallery->featured_image();
			break;
		case 'images':
			echo $gallery->get_image_count();
			break;
		case 'expires':
			echo $gallery->get_expiration_date();
			if ( $gallery->is_expired() ) {
				echo ' - <em>' . __( 'Expired', 'sunshine-photo-cart' ) . '</em>';
			}
			break;
		case 'gallery_date':
			echo 'DATE';
			break;
		default:
			break;
	}
}

add_filter( 'post_row_actions', 'sunshine_regenerate_gallery_images_link_row', 10, 2 );
add_filter( 'page_row_actions', 'sunshine_regenerate_gallery_images_link_row', 10, 2 );
function sunshine_regenerate_gallery_images_link_row( $actions, $post ) {
	if ( $post->post_type == 'sunshine-gallery' ) {
		$actions['regenerate'] = '<a href="' . admin_url( 'edit.php?post_type=sunshine-gallery&page=sunshine_tools&tool=regenerate-images&sunshine_gallery=' . $post->ID ) . '">' . __( 'Regenerate Images', 'sunshine-photo-cart' ) . '</a>';
	}
	return $actions;
}


/* Custom Meta Box Field Display for gallery image upload */
add_action( 'sunshine_meta_gallery_images_display', 'sunshine_meta_gallery_images_display' );
function sunshine_meta_gallery_images_display() {
	global $post;
	$gallery = new SPC_Gallery( $post );
	$all_image_ids = $gallery->get_image_ids();
	$total_images = count( $all_image_ids );
	$images = $gallery->get_images( array( 'posts_per_page' => apply_filters( 'sunshine_admin_gallery_images_load', 20 ) ) );
?>
<div id="sunshine-gallery-images-processing"><div class="status"></div></div>

<!-- <input type="hidden" name="selected_images" value="<?php //echo join( ',', $image_ids ); ?>" /> -->

<div id="sunshine-gallery-upload-container">

	<div id="plupload-upload-ui" class="hide-if-no-js">
		<div id="drag-drop-area">
			<div class="drag-drop-inside">
				<p class="drag-drop-info"><?php _e( 'Drop files here', 'sunshine-photo-cart' ); ?> or <input id="plupload-browse-button" type="button" value="<?php esc_attr_e( 'Select Files from Computer', 'sunshine-photo-cart' ); ?>" class="button" /></p>
				<hr />
				<p class="import-info">
					<?php _e( 'FTP Folders', 'sunshine-photo-cart' ); ?>
					<select name="images_directory">
						<option value=""><?php _e( 'Select folder', 'sunshine-photo-cart' ); ?></option>
						<?php
						$selected_dir = get_post_meta( $post->ID, 'images_directory', true );
						sunshine_directory_to_options( sunshine_get_import_directory(), $selected_dir );
						?>
					</select>
					<button class="button" id="import"><?php _e( 'Import', 'sunshine-photo-cart' ); ?></button> <a href="https://www.sunshinephotocart.com/docs/how-to-create-a-new-gallery-via-ftp/" target="_blank" class="dashicons dashicons-editor-help"></a>
				</p>
			</div>
		</div>
	</div>

	<div id="sunshine-gallery-images">
		<ul id="sunshine-gallery-image-errors"></ul>
		<ul id="sunshine-gallery-image-list">
			<?php
			if ( !empty( $images ) ) {
				foreach ( $images as $image ) {
					sunshine_admin_gallery_image_thumbnail( $image );
				}
			}
			?>
		</ul>
		<div id="sunshine-gallery-image-actions">
			<div id="sunshine-gallery-select-all"><a class="button" data-action="all"><?php _e( 'Select all images', 'sunshine-photo-cart' ); ?></a></div>
			<div id="sunshine-gallery-delete-images" style="display: none;"><a class="button delete"><?php _e( 'Delete selected images', 'sunshine-photo-cart' ); ?></a><span class="spinner"></span></div>
			<?php
			if ( $total_images > 20 ) {
				echo '<div id="sunshine-gallery-load-more">';
				echo sprintf( __( 'Showing %s of %s images', 'sunshine-photo-cart' ), '<span id="sunshine-gallery-images-loaded">20</span>', '<span class="sunshine-gallery-image-count">' . $total_images . '</span>' );
				echo ' &mdash; ';
				echo sprintf( __( 'Load %s more images', 'sunshine-photo-cart' ), '<select name="count"><option value="20">20</option><option value="50">50</option><option value="100">100</option><option value="999999999">All</option></select>' );
				echo ' <input type="button" name="loadmorego" id="sunshine-load-more-go" value="' . __( 'GO', 'sunshine-photo-cart' ) . '" class="button" /> &nbsp;&nbsp;&nbsp; ';
				echo '</div>';
			}
			?>
		</div>
		<?php
		$files = get_children( 'post_type=attachment&post_parent='.$post->ID.'&nopaging=true&orderby=menu_order&order=ASC' );
		?>
		<ul id="files">
			<?php
				foreach ( $files as $file ) {
					$mime_type = get_post_mime_type( $file->ID );
					if ( $mime_type != 'image/jpeg' ) {
						$name = basename( get_attached_file( $file->ID ) );
						echo '<li id="image-'.esc_attr( $file->ID ).'">' . esc_html( $name ) . ' <a href="#" class="sunshine-image-delete" data-image-id="'.esc_attr( $file->ID ).'">' . __( 'Delete', 'sunshine-photo-cart' ).'</a></li>';
					}
				}
			?>
		</ul>
	</div>
	<script>
	jQuery(document).ready(function($) {

		/* Not allowing this for many reasons, but got it to work so leave it here just in case something changes
		jQuery( '#media-browse-button' ).click(function(e) {

	   		e.preventDefault();

	   		// Define image_frame as wp.media object
	   		gallery_images = wp.media({
				 title: 'Select Images for Gallery',
				 multiple : true,
				 library : {
					  type : 'image',
				  }
			 });

			 gallery_images.on( 'close', function() {
				var selection = gallery_images.state().get( 'selection' );
				var image_ids = new Array();
				var image_index = 0;
				selection.each(function(attachment) {
				   image_ids[image_index] = attachment['id'];
				   image_index++;
				});
				//jQuery( 'input[name="selected_images"]' ).val( image_ids.join(",") );
				var data = {
		            action: 'sunshine_gallery_refresh_images',
		            image_ids: image_ids
		        };
		        jQuery.get(ajaxurl, data, function(response) {
		            if ( response.success === true ) {
		                jQuery( '#sunshine-gallery-image-list' ).html( response.data.image_html );
						jQuery( document ).trigger( 'refresh_images' );
		            }
		        });
			 });

			gallery_images.on( 'open', function() {
			  	var selection = gallery_images.state().get( 'selection' );
			  	var image_ids = jQuery( 'input[name="selected_images"]' ).val().split(',');
			  	image_ids.forEach(function(id) {
					var attachment = wp.media.attachment( parseInt( id ) );
					attachment.fetch();
					selection.add( attachment ? [ attachment ] : [] );
			  	});

			});

		  	gallery_images.open();

	  	});
		*/

		/**********
		IMAGE ACTIONS
		**********/

		var total_images = <?php echo esc_js( $total_images ); ?>;
		var offset = 20;
		var count = 20;
		$( '#sunshine-load-more-go' ).on('click', function(){
			$( this ).html( '<?php echo esc_js( __( 'Loading', 'sunshine-photo-cart' ) ); ?> ' );
			count = parseInt( $( 'select[name="count"]' ).val() );
			var data = {
				'action': 'sunshine_gallery_load_more',
				'gallery_id': <?php echo esc_js( $post->ID ); ?>,
				'offset': offset,
				'count': count
			};
			$.post(ajaxurl, data, function( response ) {
				if ( response.success ) {
					//$( this ).data( 'offset', ( offset + 20 ) );
					$( '#sunshine-gallery-image-list' ).append( response.data.image_html );
					offset = offset + count;
					if ( offset > total_images ) {
						$( '#sunshine-gallery-load-more' ).remove();
					}
					$( '#sunshine-gallery-images-loaded' ).html( offset );
				}
			});
			return false;
		});

		<?php if ( SPC()->get_option( 'image_order' ) == 'menu_order' ) { ?>
			var itemList = $( '#sunshine-gallery-image-list' );
			itemList.sortable({
				update: function(event, ui) {
					$('#sunshine-gallery-images-processing div.status').html('<?php echo esc_js( __( 'Saving image order...', 'sunshine-photo-cart' ) ); ?>');
					$('#sunshine-gallery-images-processing').show();
					var image_order = itemList.sortable('toArray').toString();
					opts = {
						url: ajaxurl,
						type: 'POST',
						async: true,
						cache: false,
						dataType: 'json',
						data:{
							action: 'sunshine_gallery_image_sort',
							images: image_order,
							gallery: <?php echo esc_js( $gallery->get_id() ); ?>
						},
						success: function(response) {
							$('#sunshine-gallery-images-processing').hide();
							return;
						},
						error: function(xhr,textStatus,e) {
							$('#sunshine-gallery-images-processing').hide();
							return;
						}
					};
					$.ajax(opts);
				}
			});
		<?php } ?>

		$( document ).on( 'click', 'a.sunshine-image-delete', function(){
			var image_id = $( this ).data( 'image-id' );
			var data = {
				'action': 'sunshine_gallery_image_delete',
				'image_id': image_id
			};
			$.post( ajaxurl, data, function(response) {
				if ( response.success ) {
					total_images--;
					$( '.sunshine-gallery-image-count' ).html( total_images );
					$( 'li#image-' + image_id ).fadeOut();
					jQuery( document ).trigger( 'refresh_images' );
				} else {
					alert('<?php echo esc_js( __( 'Sorry, the image could not be deleted for some reason', 'sunshine-photo-cart' ) ); ?>');
				}
			});
			return false;
		});

		<?php
		$post_thumbnail_id = get_post_thumbnail_id( $post );
		if ( $post_thumbnail_id ) {
		?>
			$( 'li#image-<?php echo intval( $post_thumbnail_id ); ?>' ).addClass( 'featured' );
		<?php } ?>

		$( document ).on( 'click', 'a.sunshine-image-featured', function(){
			var image_id = $( this ).data( 'image-id' );
			var data = {
				'action': 'sunshine_gallery_image_featured',
				'gallery_id': <?php echo esc_js( $post->ID ); ?>,
				'image_id': image_id
			};
			$.post( ajaxurl, data, function(response) {
				if ( response.success ) {
					$( '#sunshine-gallery-image-list li' ).removeClass( 'featured' );
					// Replace existing Featured Image thumbnail with this new one if it exists
					if ( response.data.image_url ) {
						$( 'li#image-' + image_id ).addClass( 'featured' );
						$( '.editor-post-featured-image__container img' ).attr( 'src', response.data.image_url );
					} else {
						$( '.editor-post-featured-image__container img' ).attr( 'src', '' );
					}
				} else {
					alert('<?php echo esc_js( __( 'Sorry, the image could not be set as featured', 'sunshine-photo-cart' ) ); ?>');
				}
			});
			return false;
		});

		// TODO: This isn't working and don't know why. Should remove the highlighted image in the list that is featured
		$( document ).on( 'click', '.editor-post-featured-image .is-destructive', function(){
			$( '#sunshine-gallery-image-list li' ).removeClass( 'featured' );
		});

		$( document ).on( 'click', '#sunshine-gallery-image-list li', function(){
			$( this ).toggleClass( 'selected' );
			// If total image count is > 0, show button to delete
			if ( $( '#sunshine-gallery-image-list li.selected' ).length > 0 ) {
				$( '#sunshine-gallery-delete-images' ).show();
			} else {
				$( '#sunshine-gallery-delete-images' ).hide();
			}
		});

		$( document ).on( 'click', '#sunshine-gallery-select-all a', function(){
			var select_action = $( this ).data( 'action' );
			if ( select_action == 'all' ) {
				$( '#sunshine-gallery-image-list li' ).each( function(){
					$( this ).addClass( 'selected' );
					$( '#sunshine-gallery-delete-images' ).show();
				});
				$( this ).data( 'action', 'none' ).html( '<?php echo esc_js( __( 'Select no images', 'sunshine-photo-cart' ) ); ?>' );
			} else {
				$( '#sunshine-gallery-image-list li' ).each( function(){
					$( this ).removeClass( 'selected' );
					$( '#sunshine-gallery-delete-images' ).hide();
				});
				$( this ).data( 'action', 'all' ).html( '<?php echo esc_js( __( 'Select all images', 'sunshine-photo-cart' ) ); ?>' );
			}
		});

		$( document ).on( 'click', '#sunshine-gallery-delete-images a', function(){
			$( '#sunshine-gallery-delete-images .spinner' ).addClass( 'is-active' );
			var delete_count = $( '#sunshine-gallery-image-list li.selected' ).length;
			var processed_delete_count = 0;
			$( '#sunshine-gallery-image-list li.selected' ).each( function(){
				var image_id = $( this ).data( 'image-id' );
				if ( image_id ) {
					var data = {
						'action': 'sunshine_gallery_image_delete',
						'image_id': image_id
					};
					$.post( ajaxurl, data, function( response ) {
						processed_delete_count++;
						if ( response.success ) {
							total_images--;
							$( 'li#image-' + image_id ).fadeOut();
							jQuery( document ).trigger( 'refresh_images' );
						} else {
							alert( '<?php echo esc_js( __( 'Sorry, the image could not be deleted for some reason', 'sunshine-photo-cart' ) ); ?>');
						}
						if ( processed_delete_count >= delete_count ) {
							$( '#sunshine-gallery-delete-images .spinner' ).removeClass( 'is-active' );
							$( '#sunshine-gallery-delete-images' ).hide();
						}
					});
				}
			});
			return false;
		});


		/**********
		UPLOADER
		**********/

		<?php
		$plupload_init = array(
			'runtimes'            => 'html5,silverlight,flash,html4',
			'browse_button'       => 'plupload-browse-button',
			'container'           => 'plupload-upload-ui',
			'drop_element'        => 'drag-drop-area',
			'file_data_name'      => 'sunshine_gallery_image',
			'multiple_queues'     => true,
			'max_file_size'       => wp_max_upload_size().'b',
			'url'                 => admin_url( 'admin-ajax.php' ),
			//'flash_swf_url'       => includes_url( 'js/plupload/plupload.flash.swf' ),
			//'silverlight_xap_url' => includes_url( 'js/plupload/plupload.silverlight.xap' ),
			'filters'             => array( array( 'title' => __( 'Allowed Files' ), 'extensions' => join( ',', sunshine_allowed_file_extensions() ) ) ),
			'multipart'           => true,
			'urlstream_upload'    => true,

			// additional post data to send to our ajax hook
			'multipart_params'    => array(
				'_ajax_nonce' => wp_create_nonce( 'sunshine_gallery_upload' ),
				'action'      => 'sunshine_gallery_upload',            // the ajax action name
				'gallery_id'      => $post->ID,
			),
		);
		?>

		// create the uploader and pass the config from above
		var uploader = new plupload.Uploader(<?php echo json_encode( $plupload_init ); ?>);

		// checks if browser supports drag and drop upload, makes some css adjustments if necessary
		uploader.bind('Init', function(up){
			var uploaddiv = $('#plupload-upload-ui');

			if(up.features.dragdrop){
				uploaddiv.addClass('drag-drop');
					$('#drag-drop-area')
						.bind('dragover.wp-uploader', function(){ uploaddiv.addClass('drag-over'); })
						.bind('dragleave.wp-uploader, drop.wp-uploader', function(){ uploaddiv.removeClass('drag-over'); });
			} else{
				uploaddiv.removeClass('drag-drop');
				$('#drag-drop-area').unbind('.wp-uploader');
			}

		});

		uploader.init();

		uploader.bind( 'UploadComplete', function(){
			$( '#sunshine-gallery-images-processing div.status' ).html( 'Files uploaded successfully!' );
			$( '#sunshine-gallery-images-processing' ).addClass( 'success' ).delay( 2000 ).fadeOut( 400 );
			var elem = document.getElementById( 'sunshine-gallery-images' );
			elem.scrollTop = elem.scrollHeight;
		});

		// a file was added in the queue
		var current_image_count = 0;
		uploader.bind( 'FilesAdded', function(up, files){
			var hundredmb = 100 * 1024 * 1024, max = parseInt(up.settings.max_file_size, 10);
			var images_to_upload = files.length;
			plupload.each(files, function(file){
				if ( max > hundredmb && file.size > hundredmb && up.runtime != 'html5' ){
					alert( 'Your file was too large' );
				} else {
					current_image_count = 0;
					$( '#sunshine-gallery-images-processing').removeClass( 'success' );
					$( '#sunshine-gallery-images-processing div.status' ).html( 'Uploading <span class="processed">0</span> of <span class="total-files">' + images_to_upload + '</span> files...<span class="current-file"></span>' );
					$( '#sunshine-gallery-images-processing' ).show();
				}
			});

			up.refresh();
			up.start();
		});

		// a file was uploaded
		uploader.bind( 'FileUploaded', function(up, file, response) {
			var result = $.parseJSON( response.response );
			if ( result.success === true ) {
				current_image_count++;
				$( '#sunshine-gallery-images-processing span.processed' ).html( current_image_count );
				$( '#sunshine-gallery-images-processing div.status span.current-file' ).html( file.name + ' uploaded' );
				if ( result.data.image_html ) {
					//var image_ids = jQuery( 'input[name="selected_images"]' ).val();
					//jQuery( 'input[name="selected_images"]' ).val( image_ids + ',' + result.data.image_id );
					$( '#sunshine-gallery-image-list' ).append( result.data.image_html );
					total_images++;
					jQuery( document ).trigger( 'refresh_images' );
				} else {
					$( '#sunshine-gallery-images ul#files' ).append(
						$('<li/>', {
							'id': 'image-' + result.data.image_id,
							html: result.file.name
						})
					);
				}
			} else {
				$( '#image-errors' ).append( '<li>' + result.file.name + ' could not be uploadeed: ' + result.error + '</li>' );
			}
		});

		uploader.bind( 'ChunkUploaded', function(up, file, info) {
			var percent = Math.round( 100 - ( ( (info.total - info.offset) / info.total ) * 100 ) );
			$( '#sunshine-gallery-images-processing div.status span.current-file').html( 'Uploading file "'+file.name+'" ('+percent+'%)' );
		});

		uploader.bind('Error', function(up, err) {
			$( '#image-errors' ).append( '<li>' + err.file.name + 'X: ' + err.message + '</li>' );
		});

		/**********
		IMPORTING FOLDER
		**********/
		$( document ).on( 'click', '#import', function(){
			var images_to_upload = $( 'select[name="images_directory"] option:selected' ).data( 'count' );
			var processed_images = 0;
			$( '#sunshine-gallery-images-processing').removeClass( 'success' );
			$( '#sunshine-gallery-images-processing div.status' ).html( 'Uploading <span class="processed">0</span> of <span class="total-files">' + images_to_upload + '</span> files...<span class="current-file"></span>' );
			$( '#sunshine-gallery-images-processing' ).show();

			for ( i = 1; i <= images_to_upload; i++ ) {
				var data = {
					'action': 'sunshine_gallery_import',
					'gallery_id': <?php echo esc_js( $post->ID ); ?>,
					'dir': $( 'select[name="images_directory"] option:selected' ).val(),
					'item_number': i
				};
				$.postq( 'sunshinegalleryimport', ajaxurl, data, function(response) {
					processed_images++;
					if ( response.success === true ) {
						$( '#sunshine-gallery-images-processing span.processed' ).html( processed_images );
						$( '#sunshine-gallery-images-processing div.status span.current-file' ).html( response.data.file_name + ' uploaded' );
						if ( response.data.image_html ) {
							$( '#sunshine-gallery-image-list' ).append( response.data.image_html );
							total_images++;
							jQuery( document ).trigger( 'refresh_images' );
						} else {
							$( '#sunshine-gallery-images ul#files' ).append(
								$('<li/>', {
									'id': 'image-' + response.data.image_id,
									html: response.file_name
								})
							);
						}
					} else {
						$( '#sunshine-gallery-images-processing div.status span.current-file' ).html( response.data.file_name + ' not uploaded: ' + response.data.error );
					}
					if ( processed_images >= images_to_upload ) {
						// When done
						$( '#sunshine-gallery-images-processing div.status' ).html( 'Files imported successfully!' );
						$( '#sunshine-gallery-images-processing' ).addClass( 'success' ).delay( 2000 ).fadeOut( 400 );
					}
				}).fail( function( jqXHR ) {
                    if ( jqXHR.status == 500 || jqXHR.status == 0 ){
                        $( '#errors' ).append( '<li><strong><?php esc_js( __( 'Cannot process image, likely out of memory', 'sunshine-photo-cart' ) ); ?></strong></li>' );
                    }
                });
			}


		});


		/**********
		REFRESHING IMAGES ACTION
		**********/
		$( document ).on( 'refresh_images', function() {

			$( '.sunshine-gallery-image-count' ).html( total_images );

			if ( total_images > 0 ) {
				$( '#sunshine-gallery-select-all' ).show();
			} else {
				$( '#sunshine-gallery-select-all' ).hide();
			}

			/* Not necessary at the moment
			let image_ids = [];
			$.each( $( '#sunshine-gallery-image-list li' ), function(){
				var image_id = $( this ).data( 'image-id' );
				if ( image_id ) {
					image_ids.push( image_id );
				}
			});
			jQuery( 'input[name="selected_images"]' ).val( image_ids.join(",") );
			*/
		});

		$( document ).trigger( 'refresh_images' );


   });
  </script>

<?php
}

function sunshine_admin_gallery_image_thumbnail( $image, $echo = true ) {

	if ( is_numeric( $image ) ) {
		$image = new SPC_Image( $image );
	}

	$html = '<li id="image-' . esc_attr( $image->get_id() ) . '" data-image-id="' . esc_attr( $image->get_id() ) . '">';
	$html .= '<div class="sunshine-image-container"><img src="' . $image->get_image_url() . '" data-image-id="'.esc_attr( $image->get_id() ).'" alt="" /></div>';
	$html .= '<span class="sunshine-image-actions">';
	$html .= '<a href="media.php?attachment_id=' . esc_attr( $image->get_id() ) . '&action=edit" class="sunshine-image-edit dashicons dashicons-edit" target="_blank"></a> ';
	$html .= '<a href="#" class="sunshine-image-delete dashicons dashicons-trash remove" data-image-id="' . esc_attr( $image->get_id() ) . '"></a> ';
	$html .= '<a href="#" class="sunshine-image-featured dashicons dashicons-star-filled" data-image-id="' . esc_attr( $image->get_id() ) . '"></a> ';
	$html .= '</span>';
	$html .= '<span class="sunshine-image-name">' . esc_html( $image->get_name() ) . '</span>';
	$html .= '</li>';

	if ( $echo ) {
		echo $html;
		return;
	}
	return $html;

}

// Ajax action to refresh the selected images
add_action( 'wp_ajax_sunshine_gallery_refresh_images', 'sunshine_gallery_get_refreshed_images' );
function sunshine_gallery_get_refreshed_images() {
    if ( isset( $_GET['image_ids'] ) && is_array( $_GET['image_ids'] ) ) {
		$image_html = '';
		foreach ( $_GET['image_ids'] as $image_id ) {
			$image_html .= sunshine_admin_gallery_image_thumbnail( intval( $image_id ), false );
		}
        wp_send_json_success( array(
            'image_html' => $image_html
        ) );
    } else {
        wp_send_json_error();
    }
}

add_action( 'wp_ajax_sunshine_gallery_upload', 'sunshine_gallery_admin_ajax_upload' );
function sunshine_gallery_admin_ajax_upload(){

	check_ajax_referer( 'sunshine_gallery_upload' );

	$file = $_FILES['sunshine_gallery_image'];
	$result = array();
	$result['file'] = sanitize_text_field( $file['name'] );

	$file_info = wp_check_filetype( basename( $_FILES['sunshine_gallery_image']['name'] ) );
	if ( empty( $file_info['ext'] ) ) {
		$result['result'] = 'FAIL';
		$result['error'] = __( 'Invalid file type', 'sunshine-photo-cart' );
		echo json_encode( $result );
		exit;
	}

    add_filter( 'upload_dir', 'sunshine_custom_upload_dir' );

	set_time_limit( 600 );

	$gallery_id = intval( $_POST['gallery_id'] );

	$file_upload = wp_handle_upload( $file, array( 'test_form' => true, 'action' => 'sunshine_gallery_upload' ) );
	$post_parent_id = $gallery_id;

	sunshine_insert_gallery_image( $file_upload['file'], $post_parent_id );

}

function sunshine_insert_gallery_image( $file_path, $gallery_id ) {

	$file_type = wp_check_filetype( $file_path );
	$file_name = basename( $file_path );

	$menu_order = 0;
	$last_image = get_posts( 'post_type=attachment&post_parent=' . $gallery_id . '&posts_per_page=1&orderby=menu_order&order=DESC' );
	if ( $last_image ) {
		$menu_order = $last_image[0]->menu_order;
		$menu_order++;
	}

	//Adds file as attachment to WordPress
	$attachment_id = wp_insert_attachment( array(
			'post_mime_type' => $file_type['type'],
			'post_title' => preg_replace( '/\.[^.]+$/', '', $file_name ),
			'post_content' => '',
			'post_status' => 'inherit',
			'comment_status' => 'inherit',
			'ping_status' => 'inherit',
			'menu_order' => $menu_order
		), $file_path, $gallery_id );

	if ( !is_wp_error( $attachment_id ) ) {
		$attachment_image_meta = wp_generate_attachment_metadata( $attachment_id, $file_path );
		$image_meta = $attachment_image_meta['image_meta'];
		$update_args = array();
		if ( '' != trim( $image_meta['title'] ) ) {
			$update_args['post_title'] = trim( $image_meta['title'] );
		}
		if ( '' != trim( $image_meta['caption'] ) ) {
			$update_args['post_content'] = trim( $image_meta['caption'] );
		}
		if ( !empty( $update_args ) ) {
			$update_args['ID'] = $attachment_id;
			wp_update_post( $update_args );
		}
		$created_timestamp = current_time( 'timestamp' );
		if ( !empty( $image_meta['created_timestamp'] ) ) {
			$created_timestamp = $image_meta['created_timestamp'];
		}
		add_post_meta( $attachment_id, 'created_timestamp', $created_timestamp );
		add_post_meta( $attachment_id, 'sunshine_file_name', $file_name );

		do_action( 'sunshine_after_image_process', $attachment_id, $file_path );
		$attachment_meta_data = wp_update_attachment_metadata( $attachment_id, $attachment_image_meta );

		$result['image_id'] = $attachment_id;
		$result['file_name'] = $file_name;
		$result['image_html'] = sunshine_admin_gallery_image_thumbnail( $attachment_id, false );
		wp_send_json_success( $result );
	}

	wp_send_json_error();

}

add_action( 'wp_ajax_sunshine_gallery_image_sort', 'sunshine_gallery_image_sort' );
function sunshine_gallery_image_sort() {
	$images = sanitize_text_field( $_POST['images'] );
	$images = str_replace( 'image-', '', $images );
	$images = explode( ',', $images );
	$i = 1;
	foreach ( $images as $image_id ) {
		wp_update_post(array(
			'ID' => $image_id,
			'menu_order' => $i
		));
		$i++;
	}
	exit;
}

add_action( 'wp_ajax_sunshine_gallery_load_more', 'sunshine_gallery_load_more' );
function sunshine_gallery_load_more() {
	$gallery = new SPC_Gallery( intval( $_POST['gallery_id'] ) );
	$images = $gallery->get_images( array( 'posts_per_page' => $_POST['count'], 'offset' => intval( $_POST['offset'] ) ) );
	if ( empty( $images ) ) {
		wp_send_json_error();
	}
	$image_html = '';
	foreach ( $images as $image ) {
		$image_html .= sunshine_admin_gallery_image_thumbnail( $image, false );
	}
	wp_send_json_success( array(
		'image_html' => $image_html
	) );
}


add_action( 'wp_ajax_sunshine_gallery_image_delete', 'sunshine_gallery_image_delete' );
function sunshine_gallery_image_delete() {
	$image_id = intval( $_POST['image_id'] );
	if ( !empty( $image_id ) ) {
		if ( wp_delete_attachment( $image_id, true ) ) {
			wp_send_json_success( array( 'image_id' => $image_id ) );
		}
	}
	wp_send_json_error();
}

add_action( 'wp_ajax_sunshine_gallery_image_featured', 'sunshine_gallery_image_featured' );
function sunshine_gallery_image_featured() {
	$image_id = intval( $_POST['image_id'] );
	$gallery_id = intval( $_POST['gallery_id'] );
	if ( !empty( $image_id ) && !empty( $gallery_id ) ) {
		$current_post_thumbnail_id = get_post_thumbnail_id( $gallery_id );
		if ( $image_id == $current_post_thumbnail_id ) {
			set_post_thumbnail( $gallery_id, 1 );
			wp_send_json_success( array(
				'image_id' => $image_id,
				'image_url' => ''
			 ) );
		} elseif ( set_post_thumbnail( $gallery_id, $image_id ) ) {
			wp_send_json_success( array(
				'image_id' => $image_id,
				'image_url' => wp_get_attachment_image_url( $image_id, 'sunshine-thumbnail' )
			 ) );
		}
	}
	wp_send_json_error();
}


//add_action( 'sunshine_save_sunshine-gallery_meta', 'sunshine_gallery_save_postdata', 10, 2 );
function sunshine_gallery_save_postdata( $post_id, $post ) {
	global $wpdb;

	$gallery = new SPC_Gallery( $post );
	if ( !empty( $_POST['status'] ) && $_POST['status'] == 'password' ) {
		$password = sanitize_text_field( $_POST['password'] );
		$wpdb->query( "UPDATE $wpdb->posts SET post_status = 'publish', post_password = '$password' WHERE ID = $post_id" );
	} else {
		if ( !empty( $_POST['status'] ) && $_POST['status'] == 'private' ) {
			$wpdb->query( "UPDATE $wpdb->posts SET post_status = 'publish' WHERE ID = $post_id" );
		}
		$wpdb->query( "UPDATE $wpdb->posts SET post_password = '' WHERE ID = $post_id" );
		$gallery->update_meta_value( 'password_hint', '' );
	}

}

// Attempt to not have an image uploaded as Featured Image automatically be part of the gallery
add_filter( 'wp_insert_attachment_data', 'sunshine_featured_image_upload_situation', 10, 2 );
function sunshine_featured_image_upload_situation( $data, $postarr ) {
	$screen = get_current_screen();
	if ( isset( $_POST['action'] ) && $_POST['action'] == 'upload-attachment' && $screen->id == 'async-upload' ) {
		if ( !empty( $data['post_parent'] ) && get_post_type( $data['post_parent'] ) == 'sunshine-gallery' ) {
			$data['post_parent'] = 0;
		}
	}
	return $data;
}

/*************
IMPORTING FROM FTP FOLDER
**************/
function sunshine_get_import_directory() {
	$upload_dir = wp_upload_dir();
	return apply_filters( 'sunshine_import_directory', $upload_dir['basedir'] . '/sunshine' );
}

function sunshine_directory_to_options( $path = __DIR__, $selected_dir = '', $level = 0 ) {
    $items = scandir( $path );
    foreach( $items as $item ) {
		if ( is_numeric( $item ) || is_numeric( str_replace( '-download', '', $item ) ) ) continue; // Skip number folders, those were created by Sunshine
        if ( strpos( $item, '.' ) === 0) {
            continue;
        }
		$fullpath = $path . '/' . $item;
        if ( is_dir( $fullpath ) ) {
			$count = sunshine_image_folder_count( $fullpath );
			//if ( $count > 0 ) {
				$name = str_repeat( '&nbsp;', $level * 3 ) . $item;
				$path_array = array_reverse( explode( '/', $fullpath ) );
				$this_folder_path_array = array_slice( $path_array, 0, $level + 1 );
				$value = join( '/', array_reverse( $this_folder_path_array ) );
				echo '<option value="' . esc_attr( $value ) . '" data-count="' . intval( $count ) . '" ' . selected( $selected_dir, $value, 0 ) . '>' . esc_html( $name ) .' (' . $count . ' ' . __( 'images', 'sunshine-photo-cart' ) . ')</option>';
			//}
            sunshine_directory_to_options( $fullpath, $selected_dir, $level + 1 );
        }
    }
}

add_action( 'wp_ajax_sunshine_gallery_import', 'sunshine_ajax_gallery_import' );
function sunshine_ajax_gallery_import() {

	set_time_limit( 600 );

    add_filter( 'upload_dir', 'sunshine_custom_upload_dir' );

	$gallery_id = intval( $_POST['gallery_id'] );
	$item_number = intval( $_POST['item_number'] );
	$dir = $_POST['dir'];

	// Check if the image already exists in the gallery
	$existing_file_names = array();
	$existing_images = get_children( array( 'post_parent' => $gallery_id, 'post_type' => 'attachment', 'post_mime_type' => 'image' ) );
	foreach ( $existing_images as $existing_image ) {
		$existing_file_names[] = get_post_meta( $existing_image->ID, 'sunshine_file_name', true );
	}
	$folder = sunshine_get_import_directory() . '/' . $dir;
	$images = sunshine_get_images_in_folder( $folder );

	$file_path = $images[ $item_number - 1 ];
	$file_name = basename( $file_path );

	if ( is_array( $existing_file_names ) && in_array( $file_name, $existing_file_names ) ) {
		wp_send_json_error( array(
			'file_name' => $file_name,
			'error' => __( 'Already uploaded to gallery', 'sunshine-photo-cart' )
		));
	}

	// Make sure we have a unique file name in the directory we are moving it to
	$upload_dir = wp_upload_dir();
	$new_file_name = wp_unique_filename( $upload_dir['path'], $file_name );

	// copy the file to the uploads dir
	$new_file_path = $upload_dir['path'] . '/' . $new_file_name;
	if ( false === @copy( $file_path, $new_file_path ) ) {
		wp_send_json_error( array(
			'file' => $file_name,
			'error' => new WP_Error( 'upload_error', sprintf( __( 'The selected file could not be copied to %s.', 'sunshine-photo-cart' ), $upload_dir['path'] ) )
		));
	}

	// Set correct file permissions
	$stat = stat( dirname( $new_file_path ) );
	$perms = $stat['mode'] & 0000666;
	@ chmod( $new_file, $perms );
	$url = $upload_dir['url'] . '/' . $new_file_name;

	sunshine_insert_gallery_image( $new_file_path, $gallery_id );

}

add_action( 'sunshine_meta_gallery_emails_display', 'sunshine_meta_gallery_emails_display' );
function sunshine_meta_gallery_emails_display() {
	global $post;
	$gallery = new SPC_Gallery( $post );
	$emails = $gallery->get_emails();
	if ( !empty( $emails ) ) {
		echo join( '<br />', $emails );
	}
}
