<?php

defined( 'ABSPATH' ) || exit;

final class Sunshine_Photo_Cart {

	protected static $_instance = null;
	private $log_file;
	public $customer = null;
	public $session;
    public $cart;
    public $options;
    public $version;
	public $notices = array();
	public $countries;
	private $post_types = array();
	public $prefix;
	public $pages = array();
	public $payment_methods = array();
	public $shipping_methods = array();

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		add_action( 'init', array( $this, 'init' ), 0 );
	}

	public function log( $message ) {

		if ( !SPC()->get_option( 'log' ) ) {
			return;
		}

		$log_message = current_time( 'y-m-d H:i:s' ) . ': ' . $message;
		if ( is_user_logged_in() ) {
			$log_message .= ' (User ID: ' . get_current_user_id() . ')';
		}
		$log_message .= "\n";
		$fp = fopen( $this->log_file, 'a' );
		fwrite( $fp, $log_message );
		fclose( $fp );

	}

    public function includes() {

		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/vendor/autoload.php';

		// Utilities
        include_once SUNSHINE_PHOTO_CART_PATH . 'includes/functions/misc.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/functions/delivery-methods.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/functions/shipping-methods.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/functions/payment-methods.php';
        include_once SUNSHINE_PHOTO_CART_PATH . 'includes/functions/template.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/functions/account.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/functions/cart.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/functions/checkout.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/functions/order.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/functions/product.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/functions/gallery.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/functions/taxes.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/functions/formatting.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/functions/add-to-cart.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/functions/favorites.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/functions/comments.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/functions/share.php';

        include_once SUNSHINE_PHOTO_CART_PATH . 'includes/widgets.php';
        include_once SUNSHINE_PHOTO_CART_PATH . 'includes/shortcodes.php';

		// Delivery Methods
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/class-delivery-method.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/delivery-methods/shipping.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/delivery-methods/pickup.php';

		// Shipping
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/class-shipping-method.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/shipping-methods/local.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/shipping-methods/flat-rate.php';

		// Payment Methods
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/class-payment-methods.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/class-payment-method.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/payment-methods/test.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/payment-methods/free.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/payment-methods/offline.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/payment-methods/paypal.php';

		// Important classes
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/class-email.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/class-notices.php';
        include_once SUNSHINE_PHOTO_CART_PATH . 'includes/class-customer.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/class-countries.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/class-data.php';
        include_once SUNSHINE_PHOTO_CART_PATH . 'includes/class-session.php';
        include_once SUNSHINE_PHOTO_CART_PATH . 'includes/class-product.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/class-product-category.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/class-price-level.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/class-image.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/class-gallery.php';
        include_once SUNSHINE_PHOTO_CART_PATH . 'includes/class-cart.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/class-cart-item.php';
        include_once SUNSHINE_PHOTO_CART_PATH . 'includes/class-order.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/class-order-item.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/class-order-status.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/class-shipping.php';

		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/class-frontend.php';
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/template-hooks.php';
		// TODO: include_once SUNSHINE_PHOTO_CART_PATH . 'includes/blocks/gallery-images/gallery-images.php';

		// TODO: Why does this fail register_post_meta when behind is_admin()??
		include_once SUNSHINE_PHOTO_CART_PATH . 'includes/admin/class-admin-meta-box.php';

		// Various admin functions
		if ( is_admin() ) {

			// TODO: Only load some of these on necessary admin screens. Use admin version of is_sunshine somehow?
			// Notices likely needs to be on all pages
            include_once SUNSHINE_PHOTO_CART_PATH . 'includes/admin/menu.php'; //TODO: Remove this
			include_once SUNSHINE_PHOTO_CART_PATH . 'includes/admin/class-admin.php';
			//include_once SUNSHINE_PHOTO_CART_PATH . 'includes/admin/class-license.php';
			include_once SUNSHINE_PHOTO_CART_PATH . 'includes/admin/addons.php';
			include_once SUNSHINE_PHOTO_CART_PATH . 'includes/admin/dashboard.php';
			include_once SUNSHINE_PHOTO_CART_PATH . 'includes/admin/sunshine-gallery.php';
			include_once SUNSHINE_PHOTO_CART_PATH . 'includes/admin/sunshine-product.php';
			include_once SUNSHINE_PHOTO_CART_PATH . 'includes/admin/sunshine-order.php';
			include_once SUNSHINE_PHOTO_CART_PATH . 'includes/admin/system-info.php';

			include_once SUNSHINE_PHOTO_CART_PATH . 'includes/admin/settings-fields.php';
			include_once SUNSHINE_PHOTO_CART_PATH . 'includes/admin/class-options.php';
			include_once SUNSHINE_PHOTO_CART_PATH . 'includes/admin/options/shipping-methods.php';
			include_once SUNSHINE_PHOTO_CART_PATH . 'includes/admin/options/payment-methods.php';
			include_once SUNSHINE_PHOTO_CART_PATH . 'includes/admin/options/taxes.php';

			include_once SUNSHINE_PHOTO_CART_PATH . 'includes/admin/tools.php';
			include_once SUNSHINE_PHOTO_CART_PATH . 'includes/admin/class-tool.php';
			include_once SUNSHINE_PHOTO_CART_PATH . 'includes/admin/tools/regenerate.php';
			include_once SUNSHINE_PHOTO_CART_PATH . 'includes/admin/tools/sessions.php';
			include_once SUNSHINE_PHOTO_CART_PATH . 'includes/admin/tools/orphans.php';
			include_once SUNSHINE_PHOTO_CART_PATH . 'includes/admin/tools/duplicate-images.php';

			include_once SUNSHINE_PHOTO_CART_PATH . 'includes/admin/reports.php';

		}

	}

	private function hooks() {


		//add_filter( 'attachment_link', array( $this, 'image_permalink' ), 10, 2 );
		/*
		add_filter( 'post_type_link', array( 'set_permalinks' ), 999, 2 );
		add_filter( 'the_permalink', array( 'set_permalinks' ), 999 );
		*/

	}

	public function init() {

		// Set log file
		$wp_upload_dir = wp_upload_dir();
		$this->log_file = $wp_upload_dir['basedir'] . '/sunshine/sunshine.log';

		$this->prefix = apply_filters( 'sunshine_prefix', 'sunshine_' );

		$this->includes();

		$this->session = new SPC_Session();
		$this->version = $this->get_option( 'version' );

		$this->customer = new SPC_Customer( get_current_user_id() );
		$this->countries = new SPC_Countries();
		$this->notices = new SPC_Notices();

		$this->cart = new SPC_Cart();

		$this->payment_methods = new SPC_Payment_Methods();

		if ( !is_admin() || ( defined('DOING_AJAX') && DOING_AJAX ) ) {
			$this->frontend = new SPC_Frontend();
		}

		if ( class_exists( 'SPC_License' ) && is_admin() ) {
			$sunshine_license = new SPC_License( 'sunshine', 'Sunshine Photo Cart', SUNSHINE_PHOTO_CART_VERSION, 'Sunshine Photo Cart' );
		}

		$this->post_types();
        $this->image_sizes();

        load_plugin_textdomain( 'sunshine-photo-cart', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );

		// TODO: move to function
		add_rewrite_endpoint( $this->get_option( 'account_orders_endpoint', 'my-orders' ), EP_PERMALINK | EP_PAGES );
		add_rewrite_endpoint( $this->get_option( 'account_addresses_endpoint', 'my-addresses' ), EP_PERMALINK | EP_PAGES );
		add_rewrite_endpoint( $this->get_option( 'account_edit_endpoint', 'my-profile' ), EP_PERMALINK | EP_PAGES );
		/*
        add_rewrite_endpoint( $this->get_option( 'endpoint_gallery' ), EP_PERMALINK | EP_PAGES );
        add_rewrite_endpoint( $this->get_option( 'endpoint_image' ), EP_PERMALINK | EP_PAGES );
        add_rewrite_endpoint( $this->get_option( 'endpoint_order' ), EP_PERMALINK | EP_PAGES );
		*/

		do_action( 'sunshine_init' );

	}

	public function payment_methods() {
		return SPC_Payment_Methods::instance();
	}

	private function post_types() {

		// TODO: move to separate file, add filters
		$this->post_types = array( 'sunshine-gallery', 'sunshine-product', 'sunshine-order' );

        $plugin_dir_path = dirname( __FILE__ );
		$menu_icon = plugins_url( 'assets/images/sunshine-icon.png', $plugin_dir_path );

		/* SUNSHINE GALLERIES post type */
		$labels = array(
			'name' => _x( 'Galleries', 'post type general name' ),
			'singular_name' => _x( 'Gallery', 'post type singular name' ),
			'add_new' => _x( 'Add New', 'gallery' ),
			'add_new_item' => __( 'Add New Gallery' ),
			'edit_item' => __( 'Edit Gallery' ),
			'new_item' => __( 'New Gallery' ),
			'all_items' => __( 'All Galleries' ),
			'view_item' => __( 'View Gallery' ),
			'search_items' => __( 'Search Galleries' ),
			'not_found' =>  __( 'No galleries found' ),
			'not_found_in_trash' => __( 'No galleries found in trash' ),
			'parent_item_colon' => '',
			'menu_name' => __( 'Galleries' )
		);
		$args = array(
			'labels' => $labels,
			'public' => ( !empty( SPC()->get_option( 'hide_galleries' ) ) ) ? false : true,
			'exclude_from_search' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_nav_menus' => true,
			'show_in_menu' => true,
			'menu_icon' => $menu_icon,
			'menu_position' => 45,
			'query_var' => true,
			'has_archive' => false,
			'hierarchical' => true,
			'capability_type' => array( 'sunshine_gallery', 'sunshine_galleries' ),
			'map_meta_cap' => true,
			//'show_in_rest' => true,
			'rewrite' => array(
				'slug' =>  SPC()->get_option( 'endpoint_gallery', 'gallery' ),
			),
			'show_in_rest' => true,
			'supports' => array( 'title', 'editor', 'page-attributes', 'thumbnail' )
		);
		register_post_type( 'sunshine-gallery', $args );

		/* SUNSHINE_PRODUCTS Custom Post Type */
		$labels = array(
			'name' => _x( 'Products', 'post type general name' ),
			'singular_name' => _x( 'Product', 'post type singular name' ),
			'add_new' => _x( 'Add Product', 'product' ),
			'add_new_item' => __( 'Add New Product' ),
			'edit_item' => __( 'Edit Product' ),
			'new_item' => __( 'New Product' ),
			'all_items' => __( 'All Products' ),
			'view_item' => __( 'View Products' ),
			'search_items' => __( 'Search Products' ),
			'not_found' =>  __( 'No products found' ),
			'not_found_in_trash' => __( 'No products found in trash' ),
			'parent_item_colon' => '',
			'menu_name' => __( 'Products' )
		);
		$args = array(
			'labels' => $labels,
			'public' => false,
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'show_ui' => true,
			'show_in_nav_menus' => false,
			'show_in_menu' => true,
			'menu_icon' => $menu_icon,
			'menu_position' => 46,
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'sunshine_product',
			'map_meta_cap' => true,
			'has_archive' => false,
			'hierarchical' => false,
			'show_in_rest' => true,
			'supports' => array( 'title', 'editor', 'thumbnail', 'page-attributes' )
		);
		register_post_type( 'sunshine-product', $args );

		$labels = array(
			'name'             => __( 'Product Categories', 'sunshine-photo-cart' ),
			'singular_name'    => __( 'Product Category', 'sunshine-photo-cart' ),
			'search_items'     =>  __( 'Search Product Categories', 'sunshine-photo-cart' ),
			'all_items'        => __( 'All Product Categories', 'sunshine-photo-cart' ),
			'parent_item'      => __( 'Parent Product Category', 'sunshine-photo-cart' ),
			'parent_item_colon'=> __( 'Parent Product Category:', 'sunshine-photo-cart' ),
			'edit_item'        => __( 'Edit Product Category', 'sunshine-photo-cart' ),
			'update_item'      => __( 'Update Product Category', 'sunshine-photo-cart' ),
			'add_new_item'     => __( 'Add New Product Category', 'sunshine-photo-cart' ),
			'new_item_name'    => __( 'New Product Category Name', 'sunshine-photo-cart' ),
			'separate_items_with_commas' => __( 'Separate categories with commas', 'sunshine-photo-cart' ),
			'choose_from_most_used' => __( 'Choose from the most used categories', 'sunshine-photo-cart' ),
			'popular_items' => NULL
		);
		$args = array(
			'label' => __( 'Product Category', 'sunshine-photo-cart' ),
			'labels' => $labels,
			'public' => false,
			'publicly_queryable' => false,
			'show_ui'  => true,
			'show_in_nav_menus' => false,
			'capabilities' => array(
				'manage_terms' => 'edit_sunshine_products',
				'edit_terms' => 'edit_sunshine_products',
				'delete_terms' => 'edit_sunshine_products',
				'assign_terms' => 'edit_sunshine_products'
			),
			'hierarchical' => true,
			'query_var'=> false,
			'show_in_rest' => true
		);
		register_taxonomy( 'sunshine-product-category', 'sunshine-product', $args );

		$labels = array(
			'name'             => __( 'Price Levels', 'sunshine-photo-cart' ),
			'singular_name'    => __( 'Price Level', 'sunshine-photo-cart' ),
			'search_items'     =>  __( 'Search Price Levels', 'sunshine-photo-cart' ),
			'all_items'        => __( 'All Price Levels', 'sunshine-photo-cart' ),
			'parent_item'      => __( 'Parent Price Level', 'sunshine-photo-cart' ),
			'parent_item_colon'=> __( 'Parent Price Level:', 'sunshine-photo-cart' ),
			'edit_item'        => __( 'Edit Price Level', 'sunshine-photo-cart' ),
			'update_item'      => __( 'Update Price Level', 'sunshine-photo-cart' ),
			'add_new_item'     => __( 'Add New Price Level', 'sunshine-photo-cart' ),
			'new_item_name'    => __( 'New Price Level', 'sunshine-photo-cart' )
		);
		$args = array(
			'label' => __( 'Price Level', 'sunshine-photo-cart' ),
			'labels' => $labels,
			'capability_type' => 'sunshine_product',
			'public' => false,
			'hierarchical' => false,
			'show_ui'  => true,
			'show_in_menu' => false,
			'query_var'=> true,
			'show_in_nav_menus' => false,
			'show_in_quick_edit' => false
		);
		register_taxonomy( 'sunshine-product-price-level', 'sunshine-product', $args );

		/* SUNSHINE_ORDERS Custom Post Type */
		$labels = array(
			'name' => _x( 'Orders', 'post type general name' ),
			'singular_name' => _x( 'Order', 'post type singular name' ),
			'add_new' => _x( 'Add New', 'order' ),
			'add_new_item' => __( 'Add New Order' ),
			'edit_item' => __( 'Edit Order' ),
			'new_item' => __( 'New Order' ),
			'all_items' => __( 'All Orders' ),
			'view_item' => __( 'View Orders' ),
			'search_items' => __( 'Search Orders' ),
			'not_found' =>  __( 'No orders found' ),
			'not_found_in_trash' => __( 'No orders found in trash' ),
			'parent_item_colon' => '',
			'menu_name' => __( 'Orders' )
		);
		$args = array(
			'labels' => $labels,
			'public' => true,
			'exclude_from_search' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_nav_menus' => false,
			'show_in_menu' => true,
			'menu_icon' => $menu_icon,
			'menu_position' => 47,
			'query_var' => true,
			'capability_type' => 'sunshine_order',
			'capabilities' => array(
				'edit_post' => 'edit_sunshine_order',
	            'read_post' => 'read_sunshine_order',
	            'delete_post' => 'delete_sunshine_order',
	            'edit_posts' => 'edit_sunshine_orders',
	            'edit_others_posts' => 'edit_others_sunshine_orders',
	            'publish_posts' => 'publish_sunshine_orders',
	            'read_private_posts' => 'read_private_sunshine_orders',
	            'read' => 'read',
	            'delete_posts' => 'delete_sunshine_orders',
	            'delete_private_posts' => 'delete_private_sunshine_orders',
	            'delete_published_posts' => 'delete_published_sunshine_orders',
	            'delete_others_posts' => 'delete_others_sunshine_orders',
	            'edit_private_posts' => 'edit_private_sunshine_orders',
	            'edit_published_posts' => 'edit_published_sunshine_orders',
	            'create_posts' => 'do_not_allow'
			),
			'map_meta_cap' => false,
			'has_archive' => false,
			'hierarchical' => false,
			'rewrite' => array(
				'slug' =>  SPC()->get_option( 'endpoint_order', 'order' ),
			),
			//'supports' => array( 'comments' )
		);
		register_post_type( 'sunshine-order', $args );

		$labels = array(
			'name'             => __( 'Order Statuses', 'sunshine-photo-cart' ),
			'singular_name'    => __( 'Order Status', 'sunshine-photo-cart' ),
			'search_items'     =>  __( 'Search Order Status', 'sunshine-photo-cart' ),
			'all_items'        => __( 'All Order Status', 'sunshine-photo-cart' ),
			'parent_item'      => __( 'Parent Order Status', 'sunshine-photo-cart' ),
			'parent_item_colon'=> __( 'Parent Order Status:', 'sunshine-photo-cart' ),
			'edit_item'        => __( 'Edit Order Status', 'sunshine-photo-cart' ),
			'update_item'      => __( 'Update Order Status', 'sunshine-photo-cart' ),
			'add_new_item'     => __( 'Add New Order Status', 'sunshine-photo-cart' ),
			'new_item_name'    => __( 'New Order Status', 'sunshine-photo-cart' )
		);
		$args = array(
			'label' => 'Order Status',
			'labels' => $labels,
			'public' => false,
			'hierarchical' => false,
			'show_ui'  => true,
			'query_var'=> true,
			'show_in_nav_menus' => false
		);
		register_taxonomy( 'sunshine-order-status', 'sunshine-order',$args );

	}

	public function get_post_types() {
		return apply_filters( 'sunshine_post_types', $this->post_types );
	}

    private function image_sizes() {

        // Allow post thumbnails if current theme doesn't have it already
        if ( ! current_theme_supports( 'post-thumbnails' ) ) {
            add_theme_support( 'post-thumbnails' );
        }

        // Define Sunshine's thumbnail image size
        add_image_size( 'sunshine-thumbnail', $this->get_option( 'thumbnail_width' ), $this->get_option( 'thumbnail_height' ), $this->get_option( 'thumbnail_crop' ) );

        if ( is_sunshine() ) {
            set_post_thumbnail_size( $this->get_option( 'thumbnail_width' ), $this->get_option( 'thumbnail_height' ), $this->get_option( 'thumbnail_crop' ) );
        }

    }

    public function get_option( $key, $default = false ) {
		$value = get_option( $this->prefix . $key, $default );
		if ( empty( $value ) && $default ) {
			$value = $default;
		}
        return ( $value !== '' ) ? maybe_unserialize( $value ) : '';
    }

	public function update_option( $key, $value ) {
		update_option( $this->prefix . $key, $value );
	}

	/* Frontend */
	public function login( $user_login, $user ) {

	}

	public function logout( $user_id ) {

	}

	// Backwards compat
	public function is_pro() {
		return $this->has_plan();
	}
	public function has_plan() {
		return ( get_option( 'sunshine_license_type' ) ) ? true : false;
	}
	public function get_plan() {
		return get_option( 'sunshine_license_type' );
	}
	public function get_license_key() {
		return get_option( 'sunshine_license_key' );
	}

	public function has_addon( $slug ) {
		if ( is_plugin_active( 'sunshine-' . $slug ) ) {
			return true;
		}
		return false;
	}


}
