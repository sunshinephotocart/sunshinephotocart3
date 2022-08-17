<?php
add_filter('sunshine_options_templates', 'sunshine_theme_options');
function sunshine_theme_options($options) {
	$options[] = array(
		'name' => __( 'Auto-include main menu', 'sunshine-photo-cart' ),
		'id'   => 'main_menu',
		'type' => 'checkbox',
		'desc' => __( 'Automatically have the Sunshine Main Menu appear above the Sunshine content','sunshine-photo-cart' ),
	);
	$options[] = array( 'name' => __('Custom Code', 'sunshine-photo-cart'), 'type' => 'header', 'desc' => '' );
	$options[] = array(
		'name' => __('Disable Sunshine CSS', 'sunshine-photo-cart'),
		'id'   => 'disable_sunshine_css',
		'desc' => __( 'Checking this will prevent the default sunshine CSS file from being loaded', 'sunshine-photo-cart' ),
		'type' => 'checkbox',
	);
	$options[] = array(
		'name' => __('Custom CSS', 'sunshine-photo-cart'),
		'id'   => 'theme_css',
		'type' => 'textarea',
		'css'  => 'height: 300px; width: 600px;'
	);
	$options[] = array(
		'name' => __('Before Sunshine', 'sunshine-photo-cart'),
		'id'   => 'theme_post_header',
		'type' => 'wysiwyg',
		'tip'  => 'This HTML code will get added immediately before Sunshine code is output in the page template',
		'css'  => 'height: 300px; width: 600px;'
	);
	$options[] = array(
		'name' => __('After Sunshine', 'sunshine-photo-cart'),
		'id'   => 'theme_pre_footer',
		'type' => 'wysiwyg',
		'tip'  => 'This HTML code will get added immediately after Sunshine code is output in the page template',
		'css'  => 'height: 300px; width: 600px;'
	);
	return $options;
}

add_action('wp_head', 'sunshine_template_head');
function sunshine_template_head() {
	global $sunshine;
	if ( !is_sunshine() ) return;

	if ( !empty( SPC()->get_option( 'theme_css' ) ) ) {
		echo '<!-- CUSTOM CSS FOR SUNSHINE -->';
		echo '<style type="text/css">';
		echo wp_kses_post( SPC()->get_option( 'theme_css' ) );
		echo '</style>';
	}
}

add_action( 'sunshine_before_content', 'sunshine_template_before_content', 999);
function sunshine_template_before_content( ) {
	global $sunshine;
	if ( !empty( SPC()->get_option( 'main_menu' ) ) && SPC()->get_option( 'main_menu' ) ) {
		echo do_shortcode( '[sunshine-menu]' );
	}
	if ( !empty( SPC()->get_option( 'theme_post_header' ) ) && SPC()->get_option( 'theme_post_header' ) ) {
		echo do_shortcode( wp_kses_post( SPC()->get_option( 'theme_post_header' ) ) );
	}
}

add_action( 'sunshine_after_content', 'sunshine_template_after_content', 999);
function sunshine_template_after_content( ) {
	global $sunshine;
	if ( !empty( SPC()->get_option( 'theme_pre_footer' ) ) && SPC()->get_option( 'theme_pre_footer' ) ) {
		echo do_shortcode( wp_kses_post( SPC()->get_option( 'theme_pre_footer' ) ) );
	}
}
?>
