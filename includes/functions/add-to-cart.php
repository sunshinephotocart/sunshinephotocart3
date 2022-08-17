<?php
add_action( 'wp_ajax_nopriv_sunshine_modal_display_add_to_cart', 'sunshine_modal_display_add_to_cart' );
add_action( 'wp_ajax_sunshine_modal_display_add_to_cart', 'sunshine_modal_display_add_to_cart' );
function sunshine_modal_display_add_to_cart() {

    extract( $_POST );
    if ( empty( $imageId ) ) {
        wp_send_json_error( __( 'No image ID provided', 'sunshine-photo-cart' ) );
    }

    $image = new SPC_Image( intval( $imageId ) );
    if ( empty( $image->get_id() ) ) {
        wp_send_json_error( __( 'Not a valid image ID', 'sunshine-photo-cart' ) );
    }

    $result = array( 'html' => sunshine_get_template_html( 'image/add-to-cart', array( 'image' => $image ) ) );
    wp_send_json_success( $result );

}

add_action( 'wp_ajax_nopriv_sunshine_modal_add_item_to_cart', 'sunshine_modal_add_item_to_cart' );
add_action( 'wp_ajax_sunshine_modal_add_item_to_cart', 'sunshine_modal_add_item_to_cart' );
function sunshine_modal_add_item_to_cart() {

    extract( $_POST );

    if ( empty( $image_id ) || empty( $product_id ) || empty( $gallery_id ) || !isset( $qty ) ) {
        wp_send_json_error( __( 'Invalid request', 'sunshine-photo-cart' ) );
    }

    // Add item to cart
    $options = array(
        'qty' => 1,
        'image_id' => $image_id,
    );
    $add_to_cart_result = SPC()->cart->add_item( $image_id, $product_id, $gallery_id, array( 'qty' => intval( $qty ) ), true );
    if ( $add_to_cart_result ) {
        $result = array(
            'item' => $add_to_cart_result,
            'count' => SPC()->cart->get_item_count(),
            'total_formatted' => SPC()->cart->get_total_formatted(),
            'mini_cart' => sunshine_get_template_html( 'cart/mini-cart' )
        );
        wp_send_json_success( $result );
    } else {
        wp_send_json_error( __( 'Item not added to cart', 'sunshine-photo-cart' ) );
    }
}

function sunshine_get_cart_item_qty( $object_id, $product_id ) {
    $cart_items = SPC()->cart->get_cart_items();
    if ( !empty( $cart_items ) ) {
        foreach ( $cart_items as $cart_item ) {
            if ( $cart_item->get_object_id() == $object_id && $cart_item->get_product_id() == $product_id ) {
                return $cart_item->get_qty();
            }
        }
    }
    return '0';
}
