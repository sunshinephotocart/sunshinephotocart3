<?php
/*
add_shortcode( 'sunshine-photo-cart', 'sunshine_content_shortcode' );
add_shortcode( 'sunshine', 'sunshine_content_shortcode' );
function sunshine_content_shortcode() {
	if ( !is_admin() ) {
		return SPC()->frontend->sunshine_content( $content = '', true );
	}
}
*/

// TODO: Run everything off shortcodes?!

add_shortcode( 'sunshine_galleries', 'sunshine_galleries_shortcode' );
function sunshine_galleries_shortcode() {

	if ( is_admin() ) {
		return false;
	}

	ob_start();
	do_action( 'sunshine_before_content' );
	do_action( 'sunshine_gallery_loop' );
	do_action( 'sunshine_after_content' );
	$output = ob_get_contents();
	ob_end_clean();

	return $output;

}

add_shortcode( 'sunshine_gallery', 'sunshine_gallery_shortcode' );
function sunshine_gallery_shortcode( $atts ) {

	if ( is_admin() ) {
		return false;
	}

	$atts = shortcode_atts( array(
		'id' => '',
		'show_header' => true,
		'show_content' => false
	), $atts );

	SPC()->frontend->set_gallery( $atts['id'] );
	if ( empty( SPC()->frontend->current_gallery ) ) {
		return false;
	}

	if ( !$atts['show_header'] ) {
		remove_action( 'sunshine_single_gallery', 'sunshine_show_page_header', 5 );
	}
	if ( !$atts['show_content'] ) {
		remove_action( 'sunshine_single_gallery', 'sunshine_show_page_content', 9 );
	}

	ob_start();
	do_action( 'sunshine_before_content' );
	do_action( 'sunshine_single_gallery', $atts['id'] );
	do_action( 'sunshine_after_content' );
	$output = ob_get_contents();
	ob_end_clean();

	return $output;

}



add_shortcode( 'sunshine_cart', 'sunshine_cart_shortcode' );
function sunshine_cart_shortcode() {

	if ( is_admin() ) {
		return false;
	}

	ob_start();
	do_action( 'sunshine_before_content' );
	do_action( 'sunshine_cart' );
	do_action( 'sunshine_after_content' );
	$output = ob_get_contents();
	ob_end_clean();

	return $output;

}

add_shortcode( 'sunshine_checkout', 'sunshine_checkout_shortcode' );
function sunshine_checkout_shortcode() {

	if ( is_admin() ) {
		return false;
	}

	ob_start();
	do_action( 'sunshine_before_content' );
	do_action( 'sunshine_checkout' );
	do_action( 'sunshine_after_content' );
	$output = ob_get_contents();
	ob_end_clean();

	return $output;

}

add_shortcode( 'sunshine_favorites', 'sunshine_favorites_shortcode' );
function sunshine_favorites_shortcode() {

	if ( is_admin() ) {
		return false;
	}

	ob_start();
	do_action( 'sunshine_before_content' );
	do_action( 'sunshine_favorites' );
	do_action( 'sunshine_after_content' );
	$output = ob_get_contents();
	ob_end_clean();

	return $output;

}

add_shortcode( 'sunshine_account', 'sunshine_account_shortcode' );
function sunshine_account_shortcode() {

	if ( is_admin() ) {
		return false;
	}

	ob_start();
	do_action( 'sunshine_before_content' );
	do_action( 'sunshine_account' );
	do_action( 'sunshine_after_content' );
	$output = ob_get_contents();
	ob_end_clean();

	return $output;

}


add_shortcode( 'sunshine-gallery-password', 'sunshine_gallery_password_shortcode' );
add_shortcode( 'sunshine_gallery_password', 'sunshine_gallery_password_shortcode' );
function sunshine_gallery_password_shortcode() {
	return sunshine_gallery_password_form( false );
}

add_shortcode( 'sunshine-menu', 'sunshine_menu_shortcode' );
add_shortcode( 'sunshine_menu', 'sunshine_menu_shortcode' );
function sunshine_menu_shortcode() {
	return sunshine_main_menu( false );
}

add_shortcode( 'sunshine-search', 'sunshine_search_shortcode' );
add_shortcode( 'sunshine_search', 'sunshine_search_shortcode' );
function sunshine_search_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'gallery' => ''
	), $atts, 'sunshine-search' );
	if ( !$atts['gallery'] && isset( SPC()->frontend->current_gallery ) ) {
		$atts['gallery'] = SPC()->frontend->current_gallery->ID;
	}
	return sunshine_search( $atts['gallery'], false );
}
?>
