<?php
add_action( 'admin_menu', 'sunshine_admin_menu', 10 );
function sunshine_admin_menu() {
	global $menu, $submenu;

	/* TODO: Re-eval this whole thing */
	$counter = '';
	$orders = sunshine_get_orders( array( 'status' => 'new' ) );
	$order_count = count( $orders );
	if ( $order_count > 0 ) {
		$notifications = sprintf( _n( '%s order', '%s orders', $order_count, 'sunshine-photo-cart' ), number_format_i18n( $order_count ) );
		$counter = sprintf( '<span class="sunshine-menu-count" aria-hidden="true">%1$d</span><span class="screen-reader-text">%2$s</span>', $order_count, $notifications );
		$menu[ 47 ][ 0 ] .= ' ' . $counter;
	}

	//add_menu_page( 'Sunshine', 'Sunshine', 'sunshine_manage_options', 'sunshine_admin', 'sunshine_dashboard_display', SUNSHINE_PHOTO_CART_URL . 'assets/images/sunshine-icon.png', 44 );
	//add_submenu_page( 'sunshine_admin', __( 'Dashboard', 'sunshine-photo-cart' ), __( 'Dashboard', 'sunshine-photo-cart' ), 'sunshine_manage_options', 'sunshine_admin', 'sunshine_dashboard_display' );

	$sunshine_admin_submenu = array();

	//$sunshine_admin_submenu[110] = array( __( 'Add-Ons','sunshine-photo-cart' ), __( 'Add-Ons','sunshine-photo-cart' ), 'sunshine_manage_options', 'sunshine_addons', 'sunshine_addons' );
	$sunshine_admin_submenu[120] = array( __( 'Reports','sunshine-photo-cart' ), __( 'Reports','sunshine-photo-cart' ), 'sunshine_manage_options', 'sunshine_reports', 'sunshine_reports' );
	$sunshine_admin_submenu[130] = array( __( 'Tools','sunshine-photo-cart' ), __( 'Tools','sunshine-photo-cart' ), 'sunshine_manage_options', 'sunshine_tools', 'sunshine_tools' );
	$sunshine_admin_submenu[150] = array( __( 'System Info','sunshine-photo-cart' ), __( 'System Info','sunshine-photo-cart' ), 'sunshine_manage_options', 'sunshine_system_info', 'sunshine_system_info' );

	if ( !SPC()->is_pro() ) {
		$sunshine_admin_submenu[200] = array( __( 'Upgrade','sunshine-photo-cart' ), '<span class="sunshine-menu-highlight-link">' . __( 'Upgrade','sunshine-photo-cart' ) . '</span>', 'sunshine_manage_options', 'https://www.sunshinephotocart.com/pricing/?utm_source=plugin&utm_medium=link&utm_campaign=menu' );
	}

	$sunshine_admin_submenu = apply_filters( 'sunshine_admin_menu', $sunshine_admin_submenu );
	ksort( $sunshine_admin_submenu );
	foreach ( $sunshine_admin_submenu as $item ) {
		$page = add_submenu_page( 'edit.php?post_type=sunshine-gallery', $item[0], $item[1], $item[2], $item[3], ( !empty( $item[4] ) ) ? $item[4] : '' );
	}

	/*
	add_submenu_page(
		'edit.php?post_type=sunshine-product',
		__( 'Bulk add products','sunshine-photo-cart'),
		__( 'Bulk add products','sunshine-photo-cart'),
		'edit_sunshine_products',
		'sunshine_bulk_add_products',
		'sunshine_bulk_add_products'
	);
	*/

}

?>
