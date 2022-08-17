<?php
function sunshine_activation() {
    update_option( 'sunshine_install_time', current_time( 'timestamp' ) );
    update_option( 'sunshine_install_redirect', 1 );
    update_option( 'sunshine_version', SUNSHINE_PHOTO_CART_VERSION );
}

add_action( 'admin_init', 'sunshine_install_redirect' );
function sunshine_install_redirect() {
	if ( get_option( 'sunshine_install_redirect', false ) ) {
		delete_option( 'sunshine_install_redirect' );
		wp_redirect( admin_url( '/admin.php?page=sunshine_install' ) );
		exit;
	}
}

function sunshine_deactivation() {
    wp_clear_scheduled_hook( 'sunshine_addon_check' );
    wp_clear_scheduled_hook( 'sunshine_session_garbage_collection' );
    do_action( 'sunshine_deactivation' );
}

function sunshine_base_setup() {
    global $wpdb;

    update_option( 'sunshine_version', SUNSHINE_PHOTO_CART_VERSION );

    // Capabilities
    $sub = get_role( 'subscriber' );
    $sub->add_cap( 'read_private_sunshine_galleries' );
    $sub->add_cap( 'edit_others_posts' ); // Workaround to let users see attachments of private galleries

    $admin = get_role( 'administrator' );
    add_role( 'sunshine_manager', 'Sunshine Manager' );
    $manager = get_role( 'sunshine_manager' );

    $admin_rules = array(
        'edit_sunshine_gallery',
        'read_sunshine_gallery',
        'delete_sunshine_gallery',
        'edit_sunshine_galleries',
        'edit_others_sunshine_galleries',
        'publish_sunshine_galleries',
        'publish_sunshine_gallery',
        'read_private_sunshine_galleries',
        'delete_sunshine_galleries',
        'delete_private_sunshine_galleries',
        'delete_published_sunshine_galleries',
        'delete_others_sunshine_galleries',
        'edit_private_sunshine_galleries',
        'edit_published_sunshine_galleries',
        'edit_sunshine_product',
        'read_sunshine_product',
        'delete_sunshine_product',
        'edit_sunshine_products',
        'edit_others_sunshine_products',
        'publish_sunshine_products',
        'publish_sunshine_product',
        'read_private_sunshine_products',
        'delete_sunshine_products',
        'delete_private_sunshine_products',
        'delete_published_sunshine_products',
        'delete_others_sunshine_products',
        'edit_private_sunshine_products',
        'edit_published_sunshine_products',
        'edit_sunshine_order',
        'read_sunshine_order',
        'delete_sunshine_order',
        'edit_sunshine_orders',
        'edit_others_sunshine_orders',
        //'publish_sunshine_orders',
        //'publish_sunshine_order',
        'read_private_sunshine_orders',
        'delete_sunshine_orders',
        'delete_private_sunshine_orders',
        'delete_published_sunshine_orders',
        'delete_others_sunshine_orders',
        'edit_private_sunshine_orders',
        'edit_published_sunshine_orders',
        'sunshine_manage_options',
        'read'
    );
    foreach ( $admin_rules as $rule ) {
        $admin->add_cap( $rule );
        $manager->add_cap( $rule );
    }

    // Get default options so we don't redo any
    $options = array();
    $options_rows = $wpdb->get_results( "SELECT * FROM {$wpdb->options} WHERE option_name LIKE 'sunshine_%'" );
    foreach ( $options_rows as $option_row ) {
        $key = str_replace( 'sunshine_', '', $option_row->option_name );
        $options[ $key ] = $option_row->option_value;
    }

    // Default options
    $defaults = array(
        'rows' => 5,
        'columns' => 4,
        'thumbnail_width' => 400,
        'thumbnail_height' => 300,
        'thumbnail_crop' => 1,
        'email_register' => __( 'Thank you for registering! You can now mark your favorite photos and make purchases on the website.','sunshine-photo-cart' ),
        'email_receipt' => __( 'Thank you for your order! Below is a summary of the order for your records. You will receive a separate email when your item(s) have shipped.','sunshine-photo-cart' ),
        'email_signature' => __( 'Thank you!','sunshine-photo-cart' ),
        'from_email' => get_bloginfo( 'admin_email' ),
        'from_name' => get_bloginfo( 'name' ),
        'header_footer' => 'standard',
        'currency' => 'USD',
        'currency_symbol_position' => 'left',
        'currency_thousands_separator' => ',',
        'currency_decimal_separator' => '.',
        'currency_decimals' => '2',
        'share_gallery' => '0',
        'share_image' => '0',
        'theme' => 'hooks',
        'country' => 'US',
        'tax_location' => '',
        'tax_basis' => 'shipping',
        'display_price' => 'without_tax',
        'price_has_tax' => 'no',
        'email_subject_register' => __( 'New user account info at [sitename]','sunshine-photo-cart' ),
        'email_subject_order_receipt' => __( 'Receipt for order #[order_id] from [sitename]','sunshine-photo-cart' ),
        'email_subject_order_status' => __( 'Your order #[order_id] from [sitename] has been updated','sunshine-photo-cart' ),
        'email_subject_order_comment' => __( 'A new comment on order #[order_id] at [sitename]','sunshine-photo-cart' ),
        'allow_guest_checkout' => 0,
        'allowed_countries' => 'all',
        'endpoint_gallery' => 'gallery',
        'endpoint_order' => 'purchase',
        'account_orders_endpoint' => 'my-orders',
        'account_addresses_endpoint' => 'my-addresses',
        'account_edit_endpoint' => 'my-details',
        'email_updates' => '1',
        'main_menu' => 1
    );

    $options = wp_parse_args( $options, $defaults );

    if ( empty( $options['endpoint_gallery'] ) ) {
        $post_types = get_post_types();
        foreach ( $post_types as $post_type ) {
            if ( $post_type == 'gallery' ) {
                $options['endpoint_gallery'] = 'sgallery';
            }
        }
    }

    if ( empty( $options['page'] ) ) {
        $options['page'] = wp_insert_post( array(
                'post_title' => __( 'Client Galleries', 'sunshine-photo-cart' ),
                'post_content' => '<!-- wp:shortcode -->[sunshine_galleries]<!-- /wp:shortcode -->',
                'post_type' => 'page',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
                'post_status' => 'publish'
            ) );
    }
    if ( empty( $options['page_account'] ) ) {
        $options['page_account'] = wp_insert_post( array(
                'post_title' => __( 'Account', 'sunshine-photo-cart' ),
                'post_content' => '<!-- wp:shortcode -->[sunshine_account]<!-- /wp:shortcode -->',
                'post_type' => 'page',
                'post_status' => 'publish',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
                'post_parent' => $options['page']
            ) );
    }
    if ( empty( $options['page_cart'] ) ) {
        $options['page_cart'] = wp_insert_post( array(
                'post_title' => __( 'Cart','sunshine-photo-cart' ),
                'post_content' => '<!-- wp:shortcode -->[sunshine_cart]<!-- /wp:shortcode -->',
                'post_type' => 'page',
                'post_status' => 'publish',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
                'post_parent' => $options['page']
            ) );
    }
    if ( empty( $options['page_checkout'] ) ) {
        $options['page_checkout'] = wp_insert_post( array(
                'post_title' => __( 'Checkout','sunshine-photo-cart' ),
                'post_content' => '<!-- wp:shortcode -->[sunshine_checkout]<!-- /wp:shortcode -->',
                'post_type' => 'page',
                'post_status' => 'publish',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
                'post_parent' => $options['page']
            ) );
    }
    if ( empty( $options['page_terms'] ) ) {
        $options['page_terms'] = wp_insert_post( array(
                'post_title' => __( 'Terms & Conditions','sunshine-photo-cart' ),
                'post_content' => '',
                'post_type' => 'page',
                'post_status' => 'publish',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
                'post_parent' => $options['page']
            ) );
    }
    if ( empty( $options['page_favorites'] ) ) {
        $options['page_favorites'] = wp_insert_post( array(
                'post_title' => __( 'Favorites','sunshine-photo-cart' ),
                'post_content' => '<!-- wp:shortcode -->[sunshine_favorites]<!-- /wp:shortcode -->',
                'post_type' => 'page',
                'post_status' => 'publish',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
                'post_parent' => $options['page']
            ) );
    }

    if ( !term_exists( 'pending', 'sunshine-order-status' ) ) {
        wp_insert_term( __( 'Pending','sunshine-photo-cart' ), 'sunshine-order-status', array( 'slug' => 'pending', 'description' => __( 'We have received your order but payment is still pending','sunshine-photo-cart' ) ) );
    }
    if ( !term_exists( 'new', 'sunshine-order-status' ) ) {
        wp_insert_term( __( 'New','sunshine-photo-cart' ), 'sunshine-order-status', array( 'slug' => 'new', 'description' => __( 'We have received your order and payment','sunshine-photo-cart' ) ) );
    }
    if ( !term_exists( 'processing', 'sunshine-order-status' ) ) {
        wp_insert_term( __( 'Processing','sunshine-photo-cart' ), 'sunshine-order-status', array( 'slug' => 'processing', 'description' => __( 'The images in your order are being processed and/or printed','sunshine-photo-cart' ) ) );
    }
    if ( !term_exists( 'shipped', 'sunshine-order-status' ) ) {
        wp_insert_term( __( 'Shipped/Completed','sunshine-photo-cart' ), 'sunshine-order-status', array( 'slug' => 'shipped', 'description' => __( 'Your items have shipped (or are available for download)!','sunshine-photo-cart' ) ) );
    }
    if ( !term_exists( 'cancelled', 'sunshine-order-status' ) ) {
        wp_insert_term( __( 'Cancelled','sunshine-photo-cart' ), 'sunshine-order-status', array( 'slug' => 'cancelled', 'description' => __( 'Your order was cancelled','sunshine-photo-cart' ) ) );
    }
    if ( !term_exists( 'refunded', 'sunshine-order-status' ) ) {
        wp_insert_term( __( 'Refunded','sunshine-photo-cart' ), 'sunshine-order-status', array( 'slug' => 'refunded', 'description' => __( 'Your order was refunded','sunshine-photo-cart' ) ) );
    }
    if ( !term_exists( 'pickup', 'sunshine-order-status' ) ) {
        wp_insert_term( __( 'Ready for pickup','sunshine-photo-cart' ), 'sunshine-order-status', array( 'slug' => 'pickup', 'description' => __( 'Your order is ready to be picked up','sunshine-photo-cart' ) ) );
    }

    $terms = get_terms( 'sunshine-product-price-level', array( 'hide_empty' => 0 ) );
    if ( empty( $terms ) ) {
        wp_insert_term( __( 'Default', 'sunshine-photo-cart' ), 'sunshine-product-price-level' );
    }

    $terms = get_terms( 'sunshine-product-category', array( 'hide_empty' => 0 ) );
    if ( empty( $terms ) ) {
        wp_insert_term( __( 'Default', 'sunshine-photo-cart' ), 'sunshine-product-category' );
    }

    if ( ! wp_next_scheduled( 'sunshine_addon_check' ) ) {
        wp_schedule_event( time(), 'weekly', 'sunshine_addon_check' );
    }
    if ( ! wp_next_scheduled( 'sunshine_session_garbage_collection' ) ) {
        wp_schedule_event( current_time( 'timestamp' ), 'twicedaily', 'sunshine_session_garbage_collection' );
    }

    $upload_dir = wp_upload_dir();
    if ( !is_dir( $upload_dir['basedir'] . '/sunshine' ) ) {
        wp_mkdir_p( $upload_dir['basedir'] . '/sunshine' );
    }

    foreach ( $options as $key => $value ) {
        update_option( 'sunshine_' . $key, $value );
    }

    // Setup session database
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    $collate = '';
    if ( $wpdb->has_cap( 'collation' ) ) {
        $collate = $wpdb->get_charset_collate();
    }
    $created = maybe_create_table(
        "{$wpdb->prefix}sunshine_sessions",
        "CREATE TABLE {$wpdb->prefix}sunshine_sessions (
            session_id char(32) NOT NULL,
            data LONGTEXT NOT NULL,
            expiration BIGINT(20) UNSIGNED NOT NULL,
            PRIMARY KEY  (session_id)
        ) $collate;"
    );

    flush_rewrite_rules();

}

function sunshine_update() {

    global $wpdb;
    sunshine_base_setup();
    $version = get_option( 'sunshine_version' );

    if ( version_compare($version, '1.9.6', '<') ) {

        $wpdb->query(
            $wpdb->prepare("DELETE FROM $wpdb->usermeta WHERE meta_key = %s", 'sunshine_card_number')
        );

        // Changes for all galleries
        $args = array(
            'post_type' => 'sunshine-gallery',
            'nopaging' => true
        );
        $the_query = new WP_Query( $args );
        while ( $the_query->have_posts() ) : $the_query->the_post();
            // Change values for gallery access
            $require_account = get_post_meta(get_the_ID(), 'sunshine_gallery_require_account', true);
            if ( $require_account ) {
                update_post_meta(get_the_ID(), 'sunshine_gallery_access', 'account');
            }
        endwhile; wp_reset_postdata();
    }

    if ( version_compare($version, '2.2.5', '<') ) {
        // Changes for all galleries
        $args = array(
            'post_type' => 'sunshine-gallery',
            'has_password' => true,
            'nopaging' => true
        );
        $the_query = new WP_Query( $args );
        while ( $the_query->have_posts() ) : $the_query->the_post();
            update_post_meta(get_the_ID(), 'sunshine_gallery_access', 'password');
        endwhile; wp_reset_postdata();
    }

    if ( version_compare($version, '2.2.10', '<') ) {
        // Changes for requiring email for all galleries
        $args = array(
            'post_type' => 'sunshine-gallery',
            'nopaging' => true
        );
        $the_query = new WP_Query( $args );
        while ( $the_query->have_posts() ) : $the_query->the_post();
            $access = get_post_meta( get_the_ID(), 'sunshine_gallery_access', true );
            $require_email = get_post_meta( get_the_ID(), 'sunshine_gallery_require_email', true );
            update_post_meta( get_the_ID(), 'sunshine_gallery_status', $access );
            delete_post_meta( get_the_ID(), 'sunshine_gallery_access' );
            if ( $access == 'account' ) {
                $new_access[] = 'account';
            }
            if ( $require_email ) {
                $new_access[] = 'email';
            }
            if ( is_array( $new_access ) ) {
                update_post_meta( get_the_ID(), 'sunshine_gallery_access', $new_access );
            }
        endwhile; wp_reset_postdata();
    }

    if ( version_compare($version, '2.2.7', '<') ) {
        // Changes for requiring email for all galleries
        $args = array(
            'post_type' => 'sunshine-gallery',
            'nopaging' => true
        );
        $the_query = new WP_Query( $args );
        while ( $the_query->have_posts() ) : $the_query->the_post();
            $access = get_post_meta( get_the_ID(), 'sunshine_gallery_access', true );
            if ( $access == 'email' ) {
                update_post_meta( get_the_ID(), 'sunshine_gallery_access', '' );
                update_post_meta( get_the_ID(), 'sunshine_gallery_require_email', '1' );
            }
        endwhile; wp_reset_postdata();
    }

    if ( version_compare($version, '2.4', '<') ) {
        update_option('sunshine_update_image_location', 'yes' );
    }

    if ( version_compare($version, '2.5.4', '<') ) {
        if ( empty( $options['sharing_services'] ) ) {
            $options['sharing_services'] = array( 'facebook', 'twitter', 'pinterest', 'google' );
        }
    }

    if ( version_compare( $version, '2.5.7', '<' ) ) {
        if ( !$options['allowed_countries'] ) {}
            $options['allowed_countries'] = 'all';
        if ( !$options['tax_basis'] )
            $options['tax_basis'] = 'shipping';

        if ( !$options['billing_fields'] || !array_search( 1, maybe_unserialize( $options['billing_fields' ] ) ) )
            $options['billing_fields'] = array( 'country' => 1, 'first_name' => 1, 'last_name' => 1, 'address' => 1, 'address2' => 1, 'city' => 1, 'state' => 1, 'zip' => 1 );
        if ( !$options['shipping_fields'] || !array_search( 1, maybe_unserialize( $options['shipping_fields' ] ) ) )
            $options['shipping_fields'] = array( 'country' => 1, 'first_name' => 1, 'last_name' => 1, 'address' => 1, 'address2' => 1, 'city' => 1, 'state' => 1, 'zip' => 1 );
        if ( !$options['billing_fields_required'] || !array_search( 1, maybe_unserialize( $options['billing_fields_required' ] ) ) )
            $options['billing_fields_required'] = array( 'country' => 1, 'first_name' => 1, 'last_name' => 1, 'address' => 1, 'city' => 1, 'state' => 1, 'zip' => 1 );
        if ( !$options['shipping_fields_required'] || !array_search( 1, maybe_unserialize( $options['shipping_fields_required' ] ) ) )
            $options['shipping_fields_required'] = array( 'country' => 1, 'first_name' => 1, 'last_name' => 1, 'address' => 1, 'city' => 1, 'state' => 1, 'zip' => 1 );
    }

    if ( version_compare( $version, '2.6', '<' ) ) {
        $options['other_fields_required'] = array( 'phone' => 1 );
    }

    if ( version_compare( $version, '2.8.7', '<' ) ) {
        if ( $options['show_image_names'] ) {
            $options['show_image_data'] = 'title';
        } else {
            $options['show_image_data'] = 'nothing';
        }
    }

    // Separate upgrade process for this version
    if ( version_compare( $version, '2.8.26', '<' ) ) {
        $sunshine->notices->add_notice( 'upgrade_2826', sprintf( __( 'Sunshine needs to run an upgrade process. <a href="%s">Please click here to start it</a>', 'sunshine-photo-cart' ), admin_url( '?page=sunshine_upgrade_2826' ) ), 'notice-notice', true, true, true );
    }

    // For 2.9, Sunshine Pro license key change
    if ( !empty( $options['sunshine_pro_license_key'] ) && empty( $options['sunshine_license_key'] ) ) {
        $options['sunshine_license_key'] = $options['sunshine_pro_license_key'];
        update_option( 'sunshine_license_type', 'pro' );
        $sunshine_pro_license_active = get_option( 'sunshine_pro_license_active' );
        update_option( 'sunshine_license_active', $sunshine_pro_license_active );
        unset( $options['sunshine_pro_license_key'] );
        delete_option( 'sunshine_pro_license_active' );
    }

    $options = apply_filters( 'sunshine_update_options', $options );
    //update_option( 'sunshine_options', $options );
    foreach ( $options as $key => $value ) {
        update_option( 'sunshine_' . $key, $value );
    }

    do_action( 'sunshine_update' );

    flush_rewrite_rules();

    wp_redirect( admin_url( 'admin.php?page=sunshine_updated' ) );
    exit;

}

add_action( 'admin_menu', function() {
    add_submenu_page(
        null,
		'Sunshine Install',
        'Sunshine Install',
        'manage_options',
        'sunshine_install',
        'sunshine_install_page'
    );
    add_submenu_page(
        null,
		'Sunshine Install Success',
        'Sunshine Install Success',
        'manage_options',
        'sunshine_install_success',
        'sunshine_install_success_page'
    );
    add_submenu_page(
        null,
		'Sunshine Updated',
        'Sunshine Updated',
        'manage_options',
        'sunshine_updated',
        'sunshine_updated_page'
    );
} );


function sunshine_install_page() {
?>
<div id="sunshine-install">
    <div id="sunshine-install--step1" class="sunshine-install--step">
        <p><img src="<?php echo SUNSHINE_PHOTO_CART_URL; ?>assets/images/family-shoot.svg" alt="" /></p>
        <h1>Install Sunshine Photo Cart</h1>
        <p class="sunshine-install--tagline">A few small steps to get you started!</p>
        <ul id="sunshine-install--extras">
            <li>
                <label><input type="checkbox" name="sample_products" value="1" /> Create sample products</label>
                Some generic products will be installed for you to help give you a better idea how to set things up.
            </li>
            <li>
                <label><input type="checkbox" name="stay_updated" value="1" checked="checked" onchange="jQuery('#sunshine-stay-updated').toggle();" /> Stay updated about Sunshine Photo Cart</label>
                Sunshine can let you know of any issues, automatically email you reports, and keep you up-to-date with what is happening with Sunshine Photo Cart in general.
                <div id="sunshine-stay-updated">
                    <p><input type="email" style="width: 100%;" value="<?php echo get_bloginfo( 'admin_email' ); ?>" /></p>
                    <div style="font-size: 12px; color: #999; font-style: italic;">By checking this option and providing your email address, you understand that you will be added to the Sunshine Photo Cart email list and the Sunshine Photo Cart plugin being installed on your website will automatically email you reports and other information related to it's usage. You will be able to unsubscribe from any automated emails at any time.</div>
                </div>
            </li>
            <!--
            <li>
                <label>Sunshine Pro license key <input type="text" name="sunshine_license_key" /></label>
                If you are already a Pro license holder, enter it here.
            </li>
            -->
        </ul>
        <p class="sunshine-install--button"><a href="#" class="sunshine-button" id="sunshine-install--start">Get started!</a></p>
    </div>
    <div id="sunshine-install--step2" class="sunshine-install--step">
        <p><img src="<?php echo SUNSHINE_PHOTO_CART_URL; ?>assets/images/desk.svg" alt="" /></p>
        <ol id="sunshine-install--progress">
            <li>
                <span>Create pages</span>
                Sunshine adds some new Pages to your website: Client Galleries, Cart, Checkout, Favorites, Account.
                <br /><a href="<?php echo admin_url( 'edit.php?post_type=page' ); ?>">View pages</a>
            </li>
            <li>
                <span>Set up default settings</span>
                Getting your started with some necessary basic settings to get you started, but you will have full control to change these. This is where you can configure your payment methods, shipping, tax and other important aspects of Sunshine.
                <br /><a href="<?php echo admin_url( 'edit.php?post_type=sunshine-gallery&page=sunshine' ); ?>">View settings</a>
            </li>
            <li>
                <span>Create default order statuses</span>
                Each order has a status, such as Pending, New, Processing, Shipped/Completed, Refunded, Cancelled. You can even create custom order statuses to work with any custom workflows.
                <br /><a href="<?php echo admin_url( 'edit-tags.php?taxonomy=sunshine-order-status&post_type=sunshine-order' ); ?>">View order statuses</a></span>
            </li>
            <li>
                <span>Create default product category</span>
                Products can be organized into categories and need to be assigned to at least 1.
                <br /><a href="<?php echo admin_url( 'edit-tags.php?taxonomy=sunshine-product-category&post_type=sunshine-product' ); ?>">View product categories</a>
            </li>
            <li id="sunshine-install--sample-products">
                <span>Create sample products</span>
                A few sample products to show you common configurations for products.
                <br /><a href="<?php echo admin_url( 'edit.php?post_type=sunshine-product' ); ?>">View products</a>
            </li>
            <li>
                <span>Setting up user roles</span>
                Sunshine makes use of WordPress user capabilities to help determine who can do what. Also included is the Sunshine Manager that gives admin access but only to Sunshine related areas.
            </li>
            <li>
                <span>Setting up scheduled events</span>
                Sunshine does some things behind the scenes on a regular schedule to keep things lean and running smooth.
            </li>
        </ol>
        <p class="sunshine-install--button" style="display: none;"><a href="#" class="sunshine-button" id="sunshine-install--goto-step3">Next step</a></p>
    </div>

    <div id="sunshine-install--step3" class="sunshine-install--step">
        <p><img src="<?php echo SUNSHINE_PHOTO_CART_URL; ?>assets/images/man-desk.svg" alt="" /></p>
        <h2>Go Pro!</h2>
        <p class="sunshine-install--tagline">Get access to many more features to help increase sales!</p>
        <p><label><input type="checkbox" name="has_license" /> I already have my Sunshine Pro license</label></p>
        <p id="sunshine-install--license"><input type="text" name="sunshine_pro_license" /> <input type="submit" value="Activate" class="button" /> <!-- TODO -->(Not yet working)</p>
        <div id="sunshine-install--addons">
            <p>ADDONS GO HERE</p>
            <p class="sunshine-install--button"><a href="https://www.sunshinephotocart.com/pricing/" class="sunshine-button" target="_blank">Learn more</a></p>
            <p class="sunshine-install--button-alt">I'm not interested, <a href="<?php echo admin_url( 'edit.php?post_type=sunshine-gallery&page=sunshine' ); ?>">I want to start customizing and configuring Sunshine!</a></p>
        </div>
    </div>
    <script>
    jQuery( document ).ready(function($){

        $( 'input[name="sample_products"]' ).on( 'change', function(){
            $( '#sunshine-install--sample-products' ).toggle();
        });

        $( '#sunshine-install--start' ).on( 'click', function(){

            $( '#sunshine-install--step1' ).fadeOut( 'fast' );
            $( '#sunshine-install--step2' ).fadeIn( 'slow' );

            let progress_steps = $( '#sunshine-install--progress li:visible' ).length;

            var data = {
                'action': 'sunshine_install',
                'sample_products': $( 'input[name="sample_products"]:checked' ).val(),
                'stay_updated': $( 'input[name="stay_updated"]:checked' ).val(),
                'security': '<?php echo wp_create_nonce( 'sunshine_install' ); ?>'
            };
            $.post( ajaxurl, data, function( response ) {
                if ( response == 'success' ) {
                    $( '#sunshine-install--progress li:visible' ).each(function( index ){
                        $( this ).delay( 1500 * index ).queue(function() {
                            $( this ).addClass( 'success' ).dequeue();
                            if ( ( index + 1 ) == progress_steps ) {
                                $( '#sunshine-install--step2 .sunshine-install--button' ).show();
                            }
                        });
                    });
                } else {
                    console.log( 'ERROR' );
                }
            });

            if ( $( 'input[name="stay_updated"]:checked' ).val() && $( 'input[name="email"]' ).val() ) {
                $.get( 'https://www.sunshinephotocart.com/?email_signup=' + $( 'input[name="email"]' ).val(), { email: $( 'input[name="email"]' ).val() }, function() {});
            }

            return false;
        });

        $( '#sunshine-install--goto-step3' ).on( 'click', function(){

            $( '#sunshine-install--step2' ).fadeOut( 'fast' );
            $( '#sunshine-install--step3' ).fadeIn( 'slow' );

            return false;

        });

        $( 'input[name="has_license"]' ).on( 'change', function(){

            $( '#sunshine-install--license' ).toggle();
            $( '#sunshine-install--addons' ).toggle();

        });


    });
    </script>


</div>

<?php
}

add_action( 'wp_ajax_sunshine_install', 'sunshine_install_process' );
function sunshine_install_process() {

    sunshine_base_setup();

    if ( !empty( $_POST['sample_products'] ) ) {

        $default_cat = sunshine_get_default_product_category();
        $canvas_term = wp_insert_term( __( 'Canvas', 'sunshine-photo-cart' ), 'sunshine-product-category' );
        $canvas_cat = new SPC_Product_Category( $canvas_term['term_id'] );

        $products = array(
            array(
                'name' => '5x7',
                'price' => 5,
                'taxable' => true,
                'shipping' => 0,
                'category' => $default_cat
            ),
            array(
                'name' => '8x10',
                'price' => 10,
                'taxable' => true,
                'shipping' => 0,
                'category' => $default_cat
            ),
            array(
                'name' => '11x14',
                'price' => 20,
                'taxable' => true,
                'shipping' => 0,
                'category' => $default_cat
            ),
            array(
                'name' => '20x30',
                'price' => 100,
                'taxable' => true,
                'shipping' => 3,
                'category' => $canvas_cat
            ),
            array(
                'name' => '30x50',
                'price' => 200,
                'taxable' => true,
                'shipping' => 5,
                'category' => $canvas_cat
            ),
            array(
                'name' => '40x60',
                'price' => 500,
                'taxable' => true,
                'shipping' => 10,
                'category' => $canvas_cat
            ),
        );

        foreach ( $products as $product_data ) {
            $product = new SPC_Product();
            $product->set_name( $product_data['name'] );
            $product->set_price( $product_data['price'] );
            $product->set_taxable( $product_data['taxable'] );
            $product->set_shipping( $product_data['shipping'] );
            $product->set_category( $product_data['category'] );
            $product->save();
        }

    }

	die( 'success' );
}


function sunshine_install_success_page() {
?>
<div id="sunshine-header">
    <h1><?php printf( __( 'Welcome to Sunshine Photo Cart %s', 'sunshine-photo-cart' ), SPC()->version ); ?></h1>
    <p>
        <?php
        printf( __( '<strong>Thank you for installing!</strong> Sunshine %1$s is the most comprehensive client proofing and photo cart plugin for WordPress. We hope you enjoy greater selling success!', 'sunshine-photo-cart' ), SPC()->version );
        ?>
    </p>
</div>

<div class="sunshine-wrap">
    <div id="sunshine-get-started">
        <h2>Getting started is easy!</h2>
        <style>.embed-container { position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%; } .embed-container iframe, .embed-container object, .embed-container embed { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }</style>
        <div style="display: flex; justify-content: space-between; width: 100%;">
            <div style="flex-basis: 45%;">
                <ol>
                    <li><a href="https://www.facebook.com/sunshinephotocart" target="_blank">Follow us on Facebook</a> or <a href="http://eepurl.com/bzxukv" target="_blank">join our email newsletter</a> to stay updated with new features, important bug fixes, promos and more!</li>
                    <li><a href="<?php echo admin_url( 'admin.php?page=sunshine_addons' ); ?>">Check out the add-ons</a> to get more advanced functionality</li>
                    <li><a href="<?php echo admin_url( 'admin.php?page=sunshine' ); ?>">Configure your settings</a></li>
                    <li><a href="<?php echo admin_url( 'edit.php?post_type=sunshine-product' ); ?>">Create your products</a></li>
                    <li><a href="<?php echo admin_url( 'edit.php?post_type=sunshine-gallery' ); ?>">Create a gallery</a></li>
                    <li>Invite your clients/users to view your galleries</li>
                </ol>
                <p><strong>Get more in depth help and how-to articles by going through the <a href="https://www.sunshinephotocart.com/docs/?utm_source=plugin&utm_medium=link&utm_campaign=docs">documentation</a></strong></p>
            </div>
            <div style="flex-basis: 45%;">
                <div class='embed-container'><iframe width="420" height="285" src="https://www.youtube.com/embed/t1IRNUASJSA?modestbranding=1&rel=0&showinfo=0" frameborder="0" allowfullscreen></iframe></div>
            </div>
        </div>
    </div>
</div>
<?php
}

function sunshine_updated_page() {
?>
<div id="sunshine-header">
    <h1><?php printf( __( 'Welcome to Sunshine Photo Cart %s', 'sunshine-photo-cart' ), SPC()->version ); ?></h1>
    <p>
        <?php
        printf( __( '<strong>Thank you for updating to the latest version!</strong> Sunshine %1$s is the most comprehensive client proofing and photo cart plugin for WordPress. We hope you enjoy greater selling success!', 'sunshine-photo-cart' ), SPC()->version );
        ?>
    </p>
</div>

<div class="sunshine-wrap">

    <div class="sunshine-about-content">

            <div class="sunshine-changelog">
                <?php
                $readme        = file_get_contents( SUNSHINE_PHOTO_CART_PATH . '/readme.txt' );
                $readme_pieces = explode( '== Changelog ==', $readme );
                $changelog     = nl2br( htmlspecialchars( trim( $readme_pieces[1] ) ) );
                $changelog     = str_replace( array( ' =', '= ' ), array( '</h3>', '<h3>' ), $changelog );
                $nth = nth_strpos( $changelog, '<h3>', 7, true );
                if ( $nth !== false ) {
                    $changelog = substr( $changelog, 0, $nth );
                }
                ?>
                <h2><?php _e( 'Recent Improvements', 'sunshine-photo-cart' ); ?></h2>
                <div class="changelog"><?php echo $changelog; ?></div>
                <p><a href="https://wordpress.org/plugins/sunshine-photo-cart/#developers" target="_blank"><?php _e( 'See the full Changelog', 'sunshine-photo-cart' ); ?></a></p>
            </div>

    </div>

</div>
<?php
}

function nth_strpos( $str, $substr, $n, $stri = false ) {
	if ( $stri ) {
		$str    = strtolower( $str );
		$substr = strtolower( $substr );
	}
	$ct  = 0;
	$pos = 0;
	while ( ( $pos = strpos( $str, $substr, $pos ) ) !== false ) {
		if ( ++$ct == $n ) {
			return $pos;
		}
		$pos++;
	}
	return false;
}
