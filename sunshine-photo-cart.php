<?php
/**
 * Plugin Name: Sunshine Photo Cart v3
 * Plugin URI: https://www.sunshinephotocart.com
 * Description: Client Gallery Photo Cart & Proofing Plugin for WordPress
 * Author: Sunshine Photo Cart
 * Author URI: https://www.sunshinephotocart.com
 * Version: 3.0-beta1
 * Text Domain: sunshine
 * Domain Path: languages
 *
 * Sunshine Photo Cart is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Sunshine Photo Cart is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Sunshine Photo Cart. If not, see <http://www.gnu.org/licenses/>.
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SUNSHINE_PHOTO_CART_PATH', plugin_dir_path( __FILE__ ) );
define( 'SUNSHINE_PHOTO_CART_URL', plugin_dir_url( __FILE__ ) );
define( 'SUNSHINE_PHOTO_CART_FILE', __FILE__ );
define( 'SUNSHINE_PHOTO_CART_VERSION', '3.0-beta1' );
define( 'SUNSHINE_PHOTO_CART_STORE_URL', 'http://www.sunshinephotocart.com' );

if ( ! class_exists( 'Sunshine_Photo_Cart', false ) ) {
    include_once SUNSHINE_PHOTO_CART_PATH . '/includes/class-sunshinephotocart.php';
}

include_once SUNSHINE_PHOTO_CART_PATH . '/includes/admin/setup.php';
register_activation_hook( __FILE__, 'sunshine_activation' );
register_deactivation_hook( __FILE__, 'sunshine_deactivation' );

function SPC() {
	return Sunshine_Photo_Cart::instance();
}
SPC();
