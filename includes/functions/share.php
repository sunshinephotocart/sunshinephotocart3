<?php
add_action( 'wp_ajax_nopriv_sunshine_modal_display_share', 'sunshine_modal_display_share' );
add_action( 'wp_ajax_sunshine_modal_display_share', 'sunshine_modal_display_share' );
function sunshine_modal_display_share() {

    extract( $_POST );
    if ( empty( $imageId ) ) {
        wp_send_json_error( __( 'No image ID provided', 'sunshine-photo-cart' ) );
    }

    $image = new SPC_Image( intval( $imageId ) );
    if ( empty( $image->get_id() ) ) {
        wp_send_json_error( __( 'Not a valid image ID', 'sunshine-photo-cart' ) );
    }

    $result = array( 'html' => sunshine_get_template_html( 'image/share', array( 'image' => $image ) ) );
    wp_send_json_success( $result );

}
