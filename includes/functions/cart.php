<?php

/*
add_action( 'init', 'sunshine_add_to_cart', 99 );
function sunshine_add_to_cart() {
	if ( isset( $_POST['sunshine_add_to_cart'] ) && $_POST['sunshine_add_to_cart'] == 1 ) {
		if ( is_numeric( $_POST['sunshine_product'] ) ) {
			$image = new SPC_Image( intval( $_POST['sunshine_image'] ) );
			if ( empty( $image ) ) {
				return false;
			}
			$options = array(
				'qty' => ( isset( $_POST['sunshine_qty'] ) ) ? intval( $_POST['sunshine_qty'] ) : 1,
				'image_id' => intval( $_POST['sunshine_image'] ),
				'comments' => sanitize_text_field( $_POST['sunshine_comments'] )
			);
			$result = SPC()->cart->add_item( intval( $_POST['sunshine_image'] ), intval( $_POST['sunshine_product'] ), $image->get_gallery_id(), $options );
			if ( $result ) {
				$gallery_return_url = $image->gallery->get_permalink();
				$current_gallery_page = SPC()->session->get( 'current_gallery_page' );
				if ( !empty( $current_gallery_page ) ) {
					$gallery_return_url .= '?pagination='.$current_gallery_page[1];
				}
				$message = sprintf( __( 'Item added to cart! <a href="%s" target="_top">View cart</a> or <a href="%s">Return to %s</a>', 'sunshine-photo-cart' ), sunshine_url( 'cart' ), esc_url( $gallery_return_url ), $image->gallery->get_name() );
				SPC()->notices->add( $message );
				$redirect = $image->get_permalink();
			}

		} else {
			SPC()->notices->add( __( 'Sorry, something went wrong with adding the item to cart.', 'sunshine-photo-cart' ), 'error' );
		}
		if ( !empty( $redirect ) ) {
			wp_redirect( $redirect );
			exit;
		}
	}
}
*/

// 3.0
// If user is logged in, then set cart to the customer meta
function sunshine_maybe_set_customer_cart( $contents ) {
	if ( is_user_logged_in() ) {
		$customer = new SPC_Customer( get_current_user_id() );
		if ( $customer ) {
			$customer->set_cart( $contents );
		}
	}
}

/**
 * Listening to update cart request, updating cart
 *
 * @since 1.0
 * @return void
 */
add_action( 'init', 'sunshine_update_cart', 99 );
function sunshine_update_cart() {
	if ( isset( $_POST['sunshine_update_cart'] ) && $_POST['sunshine_update_cart'] == 1  && wp_verify_nonce( $_POST['nonce'], 'sunshine_update_cart' ) ) {
		$cart_items = SPC()->cart->get_cart();
		foreach ( $cart_items as $cart_key => &$cart_item ) {
			foreach ( $_POST['item'] as $key => $item ) {
				if ( $item['hash'] == $cart_item['hash'] ) {
					if ( !isset( $item['qty'] ) || $item['qty'] <= 0 ) {
						SPC()->cart->remove_item( $cart_key );
					} elseif ( $item['qty'] != $cart_item['qty'] ) {
						SPC()->cart->update_item_quantity( $cart_key, $item['qty'] );
					}
				}
			}
		}

		SPC()->cart->update_cart();
		SPC()->notices->add( __( 'Cart updated','sunshine-photo-cart' ) );
		do_action( 'sunshine_cart_updated' );

		wp_redirect( sunshine_url( 'cart' ) );
		exit;
	}
}

/**
 * Listening for delete cart item request, deleting item
 *
 * @since 1.0
 * @return void
 */
add_action( 'init', 'sunshine_delete_cart_item', 10 );
function sunshine_delete_cart_item() {
 	if ( isset( $_GET['delete_cart_item'] ) && wp_verify_nonce( $_GET['nonce'], 'sunshine_delete_cart_item' ) ) {
 		$items = SPC()->cart->get_cart();
 		foreach ( $items as $key => $item ) {
 			if ( $_GET['delete_cart_item'] == $item['hash'] ) {
 				SPC()->cart->remove_item( $key );
				SPC()->cart->update_cart();
 				SPC()->notices->add( __( 'Item removed from cart', 'sunshine-photo-cart' ) );
 				$redirect = add_query_arg( 'deleted', $item['hash'], sunshine_url( 'cart' ) );
 				wp_redirect( $redirect );
 				exit;
 			}
 		}
 	}
}
