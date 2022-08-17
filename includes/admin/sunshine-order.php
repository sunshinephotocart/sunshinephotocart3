<?php
/* TODO: Remove this before release */
add_action( 'admin_notices', function(){
    global $wpdb;

	if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'sunshine-order' ) {
		echo '<div class="notice notice-info"><form method="get" action="edit.php"><p>Create random orders <input type="number" name="random_orders" size="6" style="width:50px;" value="20" /> <input type="submit" value="GO!" class="button" /><input type="hidden" name="post_type" value="sunshine-order" /></p><p><em>You must have some products, galleries created and shipping, payment methods all configured first for this to work. Dates will be set to random so the order sequence will be a little off from normal.<br />This feature will not be part of the final release and is only available for testing purposes.</em></form></div>';
	}

    if ( !isset( $_GET['random_orders'] ) ) {
        return;
    }

    $count = ( $_GET['random_orders'] > 0 ) ? intval( $_GET['random_orders'] ) : 2;

    $products = sunshine_get_products();
    if ( empty( $products ) ) {
        echo '<div class="notice notice-error"><p>No products setup yet</p></div>';
        return;
    }
    $galleries = sunshine_get_galleries();
    if ( empty( $galleries ) ) {
        echo '<div class="notice notice-error"><p>No galleries setup yet</p></div>';
        return;
    }
    $images = array();
    foreach ( $galleries as $gallery ) {
        $gallery_images = $gallery->get_images();
        if ( $gallery_images ) {
            $images = array_merge( $gallery_images, $images );
        }
    }

    $shipping_methods = sunshine_get_active_shipping_methods();
    if ( empty( $shipping_methods ) ) {
        echo '<div class="notice notice-error"><p>No shipping methods setup yet</p></div>';
        return;
    }

    $payment_methods = SPC()->payment_methods->get_active_payment_methods();
    if ( empty( $payment_methods ) ) {
        echo '<div class="notice notice-error"><p>No payment methods setup yet</p></div>';
        return;
    }

    echo '<div class="notice notice-success">';
    ?>
    <script>
    for (let i = 0; i < <?php echo $count; ?>; i++) {

        jQuery.ajaxq( 'sunshinetestorders', {
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'sunshine_test_add_order'
            },
            success: function( result, textStatus, XMLHttpRequest) {
                jQuery( '.notice-success p' ).append( '<a href="post.php?post=' + result.data.id + '&action=edit">' + result.data.name + '</a> has been created<br />' );
            },
            error: function( MLHttpRequest, textStatus, errorThrown ) {
                alert( 'Sorry, there was an error with your request: ' + errorThrown + MLHttpRequest + textStatus ); // TODO: Better error
            }
        });

    }
    </script>
    <?php

	echo '</p></div>';

});

add_action( 'wp_ajax_sunshine_test_add_order', 'sunshine_test_add_order' );
function sunshine_test_add_order() {

    $products = sunshine_get_products();
    $galleries = sunshine_get_galleries();
    $images = array();
    foreach ( $galleries as $gallery ) {
        $gallery_images = $gallery->get_images();
        if ( $gallery_images ) {
            $images = array_merge( $gallery_images, $images );
        }
    }

    $shipping_methods = sunshine_get_active_shipping_methods();
    $payment_methods = SPC()->payment_methods->get_active_payment_methods();

    $item_count = rand( 1, 3 );
    for ( $j = 1; $j <= $item_count; $j++ ) {
        $image = $images[ array_rand( $images ) ];
        $product = $products[ array_rand( $products ) ];
        $options = array(
            'qty' => 1,
            'image_id' => $image->get_id(),
        );
        $result = SPC()->cart->add_item( $image->get_id(), $product->get_id(), $image->get_gallery_id(), $options );
    }

    $first_names = array( 'John', 'Chris', 'David', 'Tom', 'Sally', 'Jenny', 'Katie' );
    $last_names = array( 'Smith', 'Johnson', 'Longbottom', 'Brown', 'Williams', 'Jones', 'Taylor' );
    $address1 = array( '387 Mountain Ave', '994 South Street', '7633 Flower Rd', '5636 Longfellow St', '334 5th St', '220 9th Ave' );
    $cities = array( 'Fort Collins', 'Longmont', 'Boulder', 'Denver', 'Colorado Springs', 'Arvada', 'Greeley', 'Windsor', 'Loveland' );
    $override_data = array(
        'shipping_country' => 'US',
        'shipping_first_name' => $first_names[ array_rand( $first_names ) ],
        'shipping_last_name' => $last_names[ array_rand( $last_names ) ],
        'shipping_address1' => $address1[ array_rand( $address1 ) ],
        'shipping_city' => $cities[ array_rand( $cities ) ],
        'shipping_state' => 'CO',
        'shipping_postcode' => rand( 80520, 80645 ),
    );
    $override_data['email'] = strtolower( $override_data['shipping_first_name'] ) . strtolower( $override_data['shipping_last_name'] ) . '@yahoo.com';

    SPC()->cart->set_checkout_data_item( 'shipping_method', array_rand( $shipping_methods ) );
    SPC()->cart->set_checkout_data_item( 'payment_method', array_rand( $payment_methods ) );
    SPC()->cart->set_checkout_data_item( 'customer_id', 0 );

    $order = SPC()->cart->create_order( $override_data );
    SPC()->cart->empty_cart();

    //* Generate a random date between January 1st, 2015 and now
    $random_date = mt_rand( strtotime( '1 January 2020' ), time() );
    $date_format = 'Y-m-d H:i:s';

    //* Format the date that WordPress likes
    $post_date = date( $date_format, $random_date );

    //* We only want to update the post date
    $update = array(
        'ID' => $order->get_id(),
        'post_date' => $post_date,
        'post_date_gmt' => null,
    );

    //* Update the post
    wp_update_post( $update );

    wp_send_json_success( array( 'name' => $order->get_name(), 'id' => $order->get_id() ) );

}


// Single order display
class SPC_Admin_Order {

	function __construct() {

		// Sorting/filtering
		add_filter( 'views_edit-sunshine-order', array( $this, 'views_edit' ), 999 );
		add_action( 'restrict_manage_posts', array( $this, 'filter_by_customer' ) );
		add_action( 'wp_ajax_sunshine_customer_search', array( $this, 'customer_search' ) );
		add_filter( 'bulk_actions-edit-sunshine-order', array( $this, 'bulk_actions' ) );
		add_filter( 'handle_bulk_actions-edit-sunshine-order', array( $this, 'handle_bulk_actions' ), 10, 3 );
		add_filter( 'parse_query', array( $this, 'parse_query_customer' ) );

		// Columns on main order page
		add_filter( 'manage_sunshine-order_posts_columns', array( $this, 'column_headers' ), 9999 );
		add_action( 'manage_sunshine-order_posts_custom_column', array( $this, 'column_data' ), 10, 2 );
		add_action( 'manage_edit-sunshine-order_sortable_columns', array( $this, 'sortable_columns' ) );
		add_action( 'pre_get_posts', array( $this, 'sortable_query' ) );

		// Invoice
		add_action( 'admin_init', array( $this, 'invoice' ) );

		// Order meta boxes
		add_action( 'add_meta_boxes', array( $this, 'meta_boxes' ), 9999 );

		/* Order edit tabs */
		add_action( 'sunshine_admin_order_tab_items', array( $this, 'items_tab' ) );
		add_action( 'sunshine_admin_order_tab_images', array( $this, 'images_tab' ) );
		add_action( 'sunshine_admin_order_tab_notes', array( $this, 'notes_tab' ) );
		add_action( 'sunshine_admin_order_tab_log', array( $this, 'log_tab' ) );

		// Save order
		add_action( 'save_post', array( $this, 'save_post' ) );

		// Order actions
		add_action( 'admin_init', array( $this, 'process_order_action' ) );
		add_action( 'sunshine_order_process_action_resend_order_email', array( $this, 'resend_order_email' ) );

        add_action( 'untrashed_post', array( $this, 'untrash' ) );

	}

	function views_edit( $views ) {

        $views = array(); // Reset to stop other plugins from adding here

        $counts = wp_count_posts( 'sunshine-order' );

        $class = ( !isset( $_GET['sunshine-order-status'] ) ) ? 'current' : '';
        $views[ 'all' ] = '<a class="' . $class . '" href="?post_type=sunshine-order">' . __( 'All' ) . ' <span class="count">(' . array_sum( (array) $counts ) . ')</span></a>';

		$statuses = sunshine_get_order_statuses( 'object' );
		foreach ( $statuses as $status ) {
			$class = ( isset( $_GET['sunshine-order-status'] ) && $_GET['sunshine-order-status'] == $status->get_key() ) ? 'current' : '';
			$views[ $status->get_key() ] = '<a class="' . $class . '" href="?post_type=sunshine-order&amp;sunshine-order-status=' . $status->get_key() . '">' . $status->get_name() . ' <span class="count">(' . $status->get_count() . ')</span></a>';
		}

        if ( $counts->trash > 0 ) {
            $views[ 'trash' ] = '<a class="' . $class . '" href="?post_type=sunshine-order&post_status=trash">' . __( 'Trash' ) . ' <span class="count">(' . $counts->trash . ')</span></a>';
        }


		return $views;

	}

	function filter_by_customer() {
		if ( isset( $_GET['post_type'] ) && post_type_exists( $_GET['post_type'] ) && in_array( strtolower( $_GET['post_type'] ), array( 'sunshine-order' ) ) ) {
			?>
			<select name="customer">
				<option value="" selected="selected"></option>
			</select>
			<script>
			jQuery( 'select[name="customer"]' ).select2({
				width: 300,
				minimumInputLength: 3,
				placeholder: '<?php echo esc_js( __( 'Filter by customer', 'sunshine-photo-cart' ) ); ?>',
			  	ajax: {
			    	url: ajaxurl,
					delay: 1000,
					data: function( params ) {
						return {
							search: params.term,
							action: 'sunshine_customer_search',
						};
					},
					cache: true
			  	}
			});
			</script>
			<?php
		}

	}

	function customer_search() {
		$data = array();
		if ( isset( $_GET['search'] ) ) {
			$customers = get_users( array(
				'search' => '*' . sanitize_text_field( $_GET['search'] ) . '*',
				'search_columns' => array( 'user_login', 'user_email', 'first_name', 'last_name' )
			) );
			if ( !empty( $customers ) ) {
				foreach ( $customers as $customer ) {
					$customer = new SPC_Customer( $customer );
					$data[] = array( 'id' => $customer->get_id(), 'text' => $customer->get_name() );
				}
			}
		}
		return wp_send_json( array( 'results' => $data ) );
	}

	function parse_query_customer( $query ) {
	    global $pagenow;
	    if ( !empty( $_GET['customer'] ) && $pagenow == 'edit.php' && isset( $query->query_vars['post_type'] ) && $query->query_vars['post_type'] == 'sunshine-order' ) {
			$query->query_vars['author'] = intval( $_GET['customer'] );
	    }
	}


	function bulk_actions( $actions ) {

        unset( $actions['edit'] );

		$actions[ 'sunshine_order_view_items' ] = __( 'View ordered products', 'sunshine' );

		$statuses = sunshine_get_order_statuses( 'object' );
		foreach ( $statuses as $status ) {
			$actions[ 'sunshine_order_status_' . $status->get_key() ] = sprintf( __( 'Change status to: %s', 'sunshine' ), $status->get_name() );
		}
		return $actions;

	}

	function handle_bulk_actions( $redirect_to, $action, $ids ) {
		$changed = 0;
		if ( false !== strpos( $action, 'sunshine_order_status_' ) ) {
			$this_status = str_replace( 'sunshine_order_status_', '', $action );
			$statuses = sunshine_get_order_statuses( 'object' );
			foreach ( $statuses as $status ) {
				if ( $this_status == $status->get_key() ) {
					foreach ( $ids as $order_id ) {
						$order = new SPC_Order( $order_id );
						$order->set_status( $status->get_key() );
						$order->notify( 'order-status' );
						$changed++;
					}
					break;
				}
			}
		} elseif ( $action == 'sunshine_order_view_items' ) {

			// Get template
			if ( file_exists( TEMPLATEPATH . '/sunshine/templates/admin/order-items.php' ) ) {
				$template_path = TEMPLATEPATH . '/sunshine/templates/admin/order-items.php';
			} else {
				$template_path = SUNSHINE_PHOTO_CART_PATH . 'templates/admin/order-items.php';
			}

			ob_start();
				include( $template_path );
				$output = ob_get_contents();
			ob_end_clean();

			echo $output;

			exit();
		}

		$redirect_to = add_query_arg(
			array(
				'post_type'   => 'sunshine-order',
				'bulk_action' => $action,
				'changed'     => $changed,
				'ids'         => join( ',', $ids ),
			),
			$redirect_to
		);

		return esc_url_raw( $redirect_to );

	}


	function column_headers( $columns ) {
        $columns = array(); // Reset so we can defeat all the other plugins trying to add stuff here that we don't want
        $columns['cb'] = __( 'Select All' );
        $columns['title'] = __( 'Order' );
		$columns['order_date'] = __( 'Date', 'sunshine-photo-cart' );
	  	$columns['customer'] = __( 'Customer', 'sunshine-photo-cart' );
		$columns['status'] = __( 'Status', 'sunshine-photo-cart' );
		$columns['total'] = __( 'Order Total', 'sunshine-photo-cart' );
		$columns['galleries'] = __( 'Galleries', 'sunshine-photo-cart' );
		$columns['invoice'] = __( 'Invoice', 'sunshine-photo-cart' );
	  	return $columns;
	}

	function column_data( $column, $post_id ) {
		$order = new SPC_Order( $post_id );
		switch( $column ) {
			case 'order_date':
				echo $order->get_date();
				break;
			case 'customer':
				if ( $order->get_customer_id() ) {
					echo '<a href="' . admin_url( 'user-edit.php?user_id=' . $order->get_customer_id() ) . '">' . $order->get_customer_name() . '</a>';
				} else {
					echo $order->get_customer_name();
				}
				break;
			case 'status':
				echo '<span class="sunshine-order-status-' . esc_attr( $order->get_status() ) . '">' . $order->get_status_name() . '</span>';
				break;
			case 'total':
				echo $order->get_total_formatted();
				break;
			case 'galleries':
				$galleries = array();
	            foreach ( $order->get_cart() as $order_item ) {
	                if ( !empty( $order_item->get_gallery_id() ) ) {
	                    $galleries[ $order_item->get_gallery_id() ] = '<a href="' . admin_url( 'post.php?action=edit&post=' . $order_item->get_gallery_id() ) . '">' . $order_item->get_gallery_name() . '</a>';
	                }
	            }
				echo join( ', ', $galleries );
				break;
			case 'invoice':
				echo '<a href="' . admin_url( 'post.php?sunshine_invoice=1&post=' . $post_id ) . '" class="invoice">' . __( 'View invoice', 'sunshine-photo-cart' ) . '</a>';
				break;
		}
	}

	function sortable_columns( $columns ) {
		$columns['order_date'] = 'date';
		$columns['total'] = 'total';
		unset( $columns['title'] );
		return $columns;
	}

	function sortable_query( $query ) {
	    if ( ! is_admin() ) {
			return;
		}

	    $orderby = $query->get( 'orderby' );

	    if ( 'order_date' == $orderby ) {
	        $query->set( 'orderby', 'date' );
	    } elseif ( 'total' == $orderby ) {
			$query->set( 'meta_key', 'total');
	        $query->set( 'orderby', 'meta_value_num' );
	    }

	}


	function meta_boxes() {
        global $wp_meta_boxes;

        // Remove any other meta box another plugin may have tried to add
        unset( $wp_meta_boxes[ 'sunshine-order' ] );

		add_meta_box(
			'sunshine-order-sidebar',
			__( 'Order Actions', 'sunshine-photo-cart' ),
			array( $this, 'order_sidebar' ),
			'sunshine-order',
			'side',
			'core',
		);

		add_meta_box(
			'sunshine-order-data',
            __( 'Order Data', 'sunshine-photo-cart' ),
			array( $this, 'order_data' ),
			'sunshine-order',
			'normal',
			'high',
		);

	}

	function order_sidebar() {
		global $post;
		$order = new SPC_Order( $post );
		$order_actions = $this->get_order_actions();
		?>

		<div id="sunshine-order-buttons">
			<button type="submit" class="button update button-primary" name="save"><?php echo esc_attr__( 'Update', 'sunshine-photo-cart' ); ?></button>
			<?php
			if ( current_user_can( 'delete_post', $post->ID ) ) {
				if ( ! EMPTY_TRASH_DAYS ) {
					$delete_text = __( 'Delete permanently', 'sunshine-photo-cart' );
				} else {
					$delete_text = __( 'Move to Trash', 'sunshine-photo-cart' );
				}
				?>
				<a class="delete" href="<?php echo esc_url( get_delete_post_link( $post->ID ) ); ?>"><?php echo esc_html( $delete_text ); ?></a>
				<?php
			}
			?>
			<a href="<?php echo admin_url( 'post.php?sunshine_invoice=1&post=' . $post->ID ); ?>" class="invoice"><?php _e( 'View invoice', 'sunshine-photo-cart' ); ?></a>
			<?php do_action( 'sunshine_admin_order_buttons', $order ); ?>
		</div>

		<div id="sunshine-order-actions">

			<h3><?php _e( 'Order Actions', 'sunshine-order-cart' ); ?></h3>

			<?php do_action( 'sunshine_order_actions_start', $order ); ?>

			<select name="sunshine_order_action">
				<option value=""><?php esc_html_e( 'Choose an action...', 'sunshine-photo-cart' ); ?></option>
				<?php foreach ( $order_actions as $action => $title ) { ?>
					<option value="<?php echo esc_attr( $action ); ?>"><?php echo esc_html( $title ); ?></option>
				<?php } ?>
			</select>
			<?php do_action( 'sunshine_order_actions_options', $order ); ?>
			<button class="button"><span><?php esc_html_e( 'Apply', 'sunshine-photo-cart' ); ?></span></button>

			<?php do_action( 'sunshine_order_actions_end', $order ); ?>

		</div>

		<?php
	}

	function get_order_actions() {
		global $post;
		$post_id = '';
		if ( !empty( $_POST['post_ID'] ) ) {
			$post_id = intval( $_POST['post_ID'] );
		} elseif ( !empty( $post_id ) ) {
			$post_id = $post->ID;
		}
		$actions = array(
			'resend_order_email' => __( 'Resend order email to customer', 'sunshine-photo-cart' ),
		);
		return apply_filters( 'sunshine_order_actions', $actions, $post_id );
	}

	function order_data() {
		global $post;
		$order = new SPC_Order( $post );
	?>
		<h2><?php echo sprintf( __( '%s &mdash; %s', 'sunshine-photo-cart' ), $order->get_name(), $order->get_total_formatted() ); ?></h2>
		<ul id="sunshine-order-basics">
			<li id="sunshine-order-date">
                <span><?php _e( 'Date', 'sunshine-photo-cart' ); ?></span>
			    <?php echo $order->get_date(); ?>
            </li>
			<li id="sunshine-order-payment-method">
                <span><?php _e( 'Payment Method', 'sunshine-photo-cart' ); ?></span>
				<?php echo $order->get_payment_method_name(); ?>
				<?php
					$payment_method = SPC()->payment_methods->get_payment_method_by_id( $order->get_payment_method() );
					$transaction_id = $payment_method->get_transaction_id( $order );
					$transaction_url = $payment_method->get_transaction_url( $order );
					if ( $transaction_url ) {
						echo ' (<a href="' . esc_url( $transaction_url ) . '" target="_blank">' . $transaction_id . '</a>)';
					} elseif ( $transaction_id ) {
						echo ' (' . $transaction_id . ')';
					}
				?>
			</li>
			<li id="sunshine-order-shipping">
                <span><?php _e( 'Delivery/Shipping Method', 'sunshine-photo-cart' ); ?></span>
                <?php echo $order->get_delivery_method_name(); ?>
                <?php if ( $order->get_shipping_method() ) { ?>
                    (<?php echo $order->get_shipping_method_name(); ?>)
                <?php } ?>
            </li>
            <li id="sunshine-order-mode">
                <span><?php _e( 'Mode', 'sunshine-photo-cart' ); ?></span>
			    <?php echo $order->get_mode(); ?>
            </li>
		</ul>
		<div id="sunshine-order-addresses">
			<div id="sunshine-order-general">
				<h3><?php _e( 'General', 'sunshine-photo-cart' ); ?></h3>
				<p>
					<label><?php _e( 'Customer', 'sunshine-photo-cart' ); ?></label>
					<?php if ( $order->get_customer_id() ) { ?>
						<a href="<?php echo admin_url( 'user-edit.php?user_id=' . $order->get_customer_id() ); ?>"><?php echo $order->get_customer_name(); ?></a>
					<?php } else {
						echo $order->get_customer_name();
					} ?>
					<br />
					<a href="mailto:<?php echo $order->get_email(); ?>"><?php echo $order->get_email(); ?></a>
					<?php
					if ( $order->get_phone() ) {
						echo ' / ' . $order->get_phone();
					}
					?>
				</p>
				<p>
					<label for="order-status"><?php _e( 'Order Status', 'sunshine-photo-cart' ); ?></label>
					<select id="order-status" name="order_status">
						<?php
						$current_order_status = $order->get_status();
						$order_statuses = sunshine_get_order_statuses( 'object' );
						foreach ( $order_statuses as $order_status ) {
							echo '<option value="' . $order_status->get_key() . '" ' . selected( $current_order_status, $order_status->get_key(), false ) . '>' . $order_status->get_name() . '</option>';
						}
						?>
					</select>
				</p>
				<p id="order-status-change-notify" style="display: none;"><label><input type="checkbox" name="order_status_change_notify" value="yes" /> <?php _e( 'Notify customer of status change', 'sunshine-photo-cart' ); ?></label></p>
				<script>
					jQuery( 'select[name="order_status"]' ).change(function() {
						if ( jQuery('select[name="order_status"]').val() != '<?php echo esc_js( $current_order_status ); ?>' ) {
							jQuery( '#order-status-change-notify' ).show();
						} else {
							jQuery( '#order-status-change-notify' ).hide();
						}
					});
				</script>
				<?php do_action( 'sunshine_admin_after_order_general', $order ); ?>
			</div>
			<div id="sunshine-order-shipping">
				<h3><?php _e( 'Shipping', 'sunshine-photo-cart' ); ?></h3>
				<?php if ( $order->has_shipping_address() ) { ?>
					<p><?php echo $order->get_shipping_address_formatted(); ?></p>
				<?php } else { ?>
					<p><?php _e( 'No shipping address collected for this order', 'sunshine-photo-cart' ); ?>
				<?php } ?>
				<?php do_action( 'sunshine_admin_after_order_shipping', $order ); ?>
			</div>
			<div id="sunshine-order-billing">
				<h3><?php _e( 'Billing', 'sunshine-photo-cart' ); ?></h3>
				<?php if ( $order->has_billing_address() ) { ?>
					<p><?php echo $order->get_billing_address_formatted(); ?></p>
				<?php } else { ?>
					<p><?php _e( 'No billing address collected for this order', 'sunshine-photo-cart' ); ?>
				<?php } ?>
				<?php if ( $order->get_vat() ) { ?>
					<p><strong><?php echo ( SPC()->get_option( 'vat_label' ) ) ? SPC()->get_option( 'vat_label' ) : __( 'EU VAT Number', 'sunshine-photo-cart' ); ?></strong><br />
					<?php echo $order->get_vat(); ?></p>
				<?php } ?>

				<?php do_action( 'sunshine_admin_after_order_billing', $order ); ?>
			</div>
		</div>

		<?php
		$admin_order_tabs = apply_filters( 'sunshine_admin_order_tabs', array(
			'items' => __( 'Items', 'sunshine-photo-cart' ),
			'images' => __( 'Images', 'sunshine-photo-cart' ),
			'notes' => __( 'Notes', 'sunshine-photo-cart' ),
			'log' => __( 'Log', 'sunshine-photo-cart' ),
		), $order );

		echo '<nav class="nav-tab-wrapper" id="sunshine-admin-order-tabs">';
		$i = 1;
		foreach ( $admin_order_tabs as $key => $label ) {
			echo '<a class="nav-tab ' . ( ( $i == 1 ) ? 'nav-tab-active' : '' ) . '" id="sunshine-admin-order-tab-' . esc_attr( $key ) . '" data-tab="' . esc_attr( $key ) . '" title="' . esc_attr( $label ) . '" href="#' . esc_attr( $key ) . '">' . esc_html( $label ) . '</a>';
			$i++;
		}
		echo '</nav>';
		?>
		<script>
			jQuery( '#sunshine-admin-order-tabs a' ).on( 'click', function(){
				jQuery( '.sunshine-admin-order-tab-content' ).hide();
				jQuery( '#sunshine-admin-order-tab-content-' + jQuery( this ).data( 'tab' ) ).show();
				jQuery( '#sunshine-admin-order-tabs a' ).removeClass( 'nav-tab-active' );
				jQuery( this ).addClass( 'nav-tab-active' );
				return false;
			});
		</script>
		<?php

		echo '<div id="sunshine-admin-order-tab-content">';
		foreach ( $admin_order_tabs as $key => $label ) {
			echo '<div class="sunshine-admin-order-tab-content" id="sunshine-admin-order-tab-content-' . esc_attr( $key ) . '">';
			do_action( 'sunshine_admin_order_tab_' . $key, $order );
			echo '</div>';
		}
		echo '</div>';

	}

	function items_tab( $order ) {
		$cart = $order->get_cart();
		?>

		<table id="sunshine-admin-cart-items">
		<thead>
			<tr>
				<th class="sunshine-cart-image"><?php esc_html_e( 'Image', 'sunshine-photo-cart' ); ?></th>
				<th class="sunshine-cart-name"><?php esc_html_e( 'Product', 'sunshine-photo-cart' ); ?></th>
				<th class="sunshine-cart-qty"><?php esc_html_e( 'Qty', 'sunshine-photo-cart' ); ?></th>
				<th class="sunshine-cart-price"><?php esc_html_e( 'Item Price', 'sunshine-photo-cart' ); ?></th>
				<th class="sunshine-cart-total"><?php esc_html_e( 'Item Total', 'sunshine-photo-cart' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ( $cart as $cart_item ) { ?>
			<tr class="sunshine-cart-item <?php echo $cart_item->classes(); ?>">
				<td class="sunshine-cart-item-image" data-label="<?php esc_attr_e( 'Image', 'sunshine-photo-cart' ); ?>">
					<?php echo $cart_item->get_image_html(); ?>
				</td>
				<td class="sunshine-cart-item-name" data-label="<?php esc_attr_e( 'Product', 'sunshine-photo-cart' ); ?>">
					<div class="sunshine-cart-item-name-image"><?php echo $cart_item->get_image_name(); ?></div>
					<div class="sunshine-cart-item-name-product"><?php echo $cart_item->get_name(); ?></div>
					<div class="sunshine-cart-item-comments"><?php echo $cart_item->get_comments(); ?></div>
				</td>
				<td class="sunshine-cart-item-qty" data-label="<?php esc_attr_e( 'Qty', 'sunshine-photo-cart'); ?>">
					<?php echo $cart_item->get_qty(); ?>
				</td>
				<td class="sunshine-cart-item-price" data-label="<?php esc_attr_e( 'Price', 'sunshine-photo-cart' ); ?>">
					<?php echo $cart_item->get_price_formatted(); ?>
				</td>
				<td class="sunshine-cart-item-total" data-label="<?php esc_attr_e( 'Total', 'sunshine-photo-cart' ); ?>">
					<?php echo $cart_item->get_total_formatted(); ?>
				</td>
			</tr>
		<?php } ?>
		</tbody>
		</table>

		<table id="sunshine-admin-order-totals">
            <tr class="sunshine-subtotal">
                <th><?php _e( 'Subtotal', 'sunshine-photo-cart' ); ?></th>
                <td><?php echo $order->get_subtotal_formatted(); ?></td>
            </tr>
			<?php if ( !empty( $order->get_shipping() ) ) { ?>
    		<tr class="sunshine-shipping">
    			<th><?php echo sprintf( __( 'Shipping via %s', 'sunshine-photo-cart' ), $order->get_shipping_method_name() ); ?></th>
    			<td><?php echo $order->get_shipping_formatted(); ?></td>
    		</tr>
			<?php } ?>
    		<?php if ( !empty( $order->get_discounts() ) ) { ?>
    		<tr class="sunshine-discount">
    			<th><?php _e( 'Discounts', 'sunshine-photo-cart' ); ?></th>
    			<td><?php echo $order->get_discount_formatted(); ?></td>
    		</tr>
    		<?php } ?>
    		<?php if ( $order->get_tax() ) { ?>
    		<tr class="sunshine-tax">
    			<th><?php _e( 'Tax', 'sunshine-photo-cart' ); ?></th>
    			<td><?php echo $order->get_tax_formatted(); ?></td>
    		</tr>
    		<?php } ?>
			<?php if ( $order->get_credits() > 0 ) { ?>
    		<tr class="sunshine-credits">
    			<th><?php _e( 'Credits Applied', 'sunshine-photo-cart' ); ?></th>
    			<td><?php echo $order->get_credits_formatted(); ?></td>
    		</tr>
			<?php } ?>
    		<tr class="sunshine-total">
    			<th><?php _e( 'Order Total', 'sunshine-photo-cart' ); ?></th>
                <td><?php echo $order->get_total_formatted(); ?></td>
    		</tr>
		</table>

		<?php
	}

	function images_tab( $order ) {
		$cart = $order->get_cart();
		$filenames = array();
		foreach ( $cart as $item ) {
			$filenames[] = $item->get_filename();
		}
		if ( !empty( $filenames ) ) {
			echo '<input type="text" id="filenames" style="width:70%" value="' . esc_attr( join( ',', $filenames ) ) . '" />';
			echo ' <a id="copy-filenames" class="button">' . __( 'Copy to clipboard', 'sunshine-photo-cart' ) . '</a><br /><br />';
			_e( 'Copy and paste the file names above into Lightroom search feature (Library filter) to quickly find and create a new collection to make processing this order easier. Make sure you are using the "Contains" (and not "Contains All") search parameter.', 'sunshine-photo-cart' );
			echo '<script>
				jQuery("#copy-filenames").click(function(){
					jQuery("#filenames").select();
					document.execCommand( "copy" );
					jQuery( this ).html( "Copied!" );
					return false;
				});
				</script>';
		}
	}

	function notes_tab( $order ) {
	?>
		<p><textarea name="admin_notes" rows="10"><?php echo esc_attr( $order->get_admin_notes() ); ?></textarea></p>
		<p><?php _e( 'This is for internal use only and is not visible to your customer', 'sunshine-photo-cart' ); ?></p>
	<?php
	}

	function log_tab( $order ) {

		$log = $order->get_log();
		if ( !empty( $log ) ) {
			echo '<ol>';
			foreach ( $log as $entry ) {
				echo '<li><span class="log-date">' . date( get_option( 'date_format' ) . ' @ ' . get_option( 'time_format' ), strtotime( $entry->comment_date_gmt ) ) . '</span> <span class="log-content">' . $entry->comment_content . '</span></li>';
			}
			echo '</ol>';
		} else {
			echo '<p>' . __( 'No log entries yet', 'sunshine-photo-cart' ) . '</p>';
		}

	}

	function save_post( $post_id ) {

        if ( get_post_type( $post_id ) != 'sunshine-order' || !empty( $_POST['sunshine_order_action']) ) {
			return;
		}

		$meta_data_keys = array(
			'admin_notes' => 'sanitize_textarea_field'
		);
		$meta_data = array();
		foreach ( $meta_data_keys as $key => $function ) {
			if ( array_key_exists( $key, $_POST ) ) {
				if ( is_array( $_POST[ $key ] ) ) {
					$meta_data[ $key ] = array_map( $function, $_POST[ $key ] );
				} else {
					$meta_data[ $key ] = $function( $_POST[ $key ] );
				}
			}
		}

		foreach ( $meta_data as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		// Update order status
		if ( !empty( $_POST['order_status'] ) ) {
			$order = new SPC_Order( $post_id );
			$current_order_status = $order->get_status();
			$new_order_status = sanitize_key( $_POST['order_status'] );
			if ( $current_order_status != $new_order_status ) {
				$order->set_status( $new_order_status );
				if ( !empty( $_POST['order_status_change_notify'] ) && $_POST['order_status_change_notify'] == 'yes' ) {
					if ( $order->notify( 'order-status' ) ) {
						SPC()->notices->add_admin( 'order_status_change', sprintf( __( 'Order status notification email sent to %s', 'sunshine-photo-cart' ), $order->get_email() ), 'success' );
						$order->add_log( sprintf( __( 'Order status notification email sent to %s', 'sunshine-photo-cart' ), $order->get_email() ) );
					} else {
						SPC()->notices->add_admin( 'order_status_change', sprintf( __( 'Order status notification email failed to send to %s', 'sunshine-photo-cart' ), $order->get_email() ), 'error' );
						$order->add_log( sprintf( __( 'Order status notification email failed to send to %s', 'sunshine-photo-cart' ), $order->get_email() ) );
					}
				}
			}
		}

	}

	function process_order_action() {

		if ( empty( $_POST['sunshine_order_action'] ) || empty( $_POST['post_ID'] ) ) {
			return;
		}

		$available_actions = $this->get_order_actions();
		if ( !array_key_exists( $_POST['sunshine_order_action'], $available_actions ) ) {
			return false;
		}

		do_action( 'sunshine_order_process_action_' . sanitize_key( $_POST['sunshine_order_action'] ), intval( $_POST['post_ID'] ) );

	}

	function resend_order_email( $order_id ) {

		$order = new SPC_Order( $order_id );
		$order->notify( 'receipt' );
		SPC()->notices->add_admin( 'resend_order_email', __( 'Order email successfully resent', 'sunshine-photo-cart' ), 'success' );

	}

	function invoice() {

		if ( isset( $_GET['sunshine_invoice'] ) && isset( $_GET['post'] ) ) {

			$order = new SPC_Order( intval( $_GET['post'] ) );
			if ( empty( $order ) ) {
				wp_die( __( 'Invalid order ID', 'sunshine-photo-cart' ) );
				exit;
			}

            $output = sunshine_get_template_html( 'invoice/admin', array( 'order' => $order ) );
            if ( !isset( $_GET['html'] ) ) {
                $output = str_replace( trailingslashit( get_bloginfo( 'url' ) ), ABSPATH, $output );
            }

			if ( isset( $_GET['html'] ) ) {
				echo $output;
				exit;
			}

			require_once SUNSHINE_PHOTO_CART_PATH . 'includes/vendor/autoload.php';

			// Set it up
			$mpdf = new \Mpdf\Mpdf( array(
                'format' => 'Letter',
                'mode' => 'utf-8',
                'fontdata' => [
                    'roboto' => [
                        'R' => 'Roboto-Regular.ttf',
                        'B' => 'Roboto-Bold.ttf',
                    ]
                ],
                'default_font' => 'roboto'
            ) );

			// Output PDF to browser
			$mpdf->shrink_tables_to_fit = 1;
			$mpdf->SetTitle( $order->get_name() );
			$mpdf->SetAuthor( get_bloginfo( 'sitename' ) );
			$mpdf->setAutoTopMargin = 'stretch';
			$mpdf->setAutoBottomMargin = 'stretch';
			$mpdf->WriteHTML( $output );
			$mpdf->Output( sanitize_file_name( apply_filters( 'sunshine_order_invoice_filename', $order->get_name(), $order ) ), 'I' );
			exit;

		}

	}

    function untrash( $post_id ) {
    	if ( get_post_type( $post_id ) == 'sunshine-order' ) {
    		wp_update_post(array(
    			'ID' => $post_id,
    			'post_status' => 'publish'
    		));
    	}
    }


}


$SPC_Admin_Order = new SPC_Admin_Order();
?>
