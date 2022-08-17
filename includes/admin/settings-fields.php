<?php
function sunshine_get_settings_fields() {
    /* GENERAL */

    $general_fields = array();
    $general_fields['1000'] = array(
        'id' => 'address',
        'name'=> __( 'Address' , 'sunshine-photo-cart' ),
        'type'=> 'header',
    );

        $general_fields['1100'] = array(
            'id' => 'address1',
            'name'=> __( 'Your Address Line 1' , 'sunshine-photo-cart' ),
            'type' => 'text'
        );
        $general_fields['1200'] = array(
            'id' => 'address2',
            'name'=> __( 'Address Line 2' , 'sunshine-photo-cart' ),
            'type' => 'text',
        );
        $general_fields['1300'] = array(
            'id' => 'city',
            'name'=> __( 'City' , 'sunshine-photo-cart' ),
            'type' => 'text',
        );
        $general_fields['1400'] = array(
            'id' => 'state',
            'name'=> __( 'State / Province' , 'sunshine-photo-cart' ),
            'type' => 'text',
        );
        $general_fields['1500'] = array(
            'id' => 'postcode',
            'name'=> __( 'Zip / Postcode' , 'sunshine-photo-cart' ),
            'type' => 'text',
        );
        $general_fields['1600'] = array(
            'id' => 'country',
            'name'=> __( 'Country' , 'sunshine-photo-cart' ),
            'type' => 'select',
            'select2' => true,
            'options' => SPC()->countries->get_countries()
        );

    $general_fields['3000'] = array(
        'id' => 'currency_formatting',
        'name'=> __( 'Currency Formatting' , 'sunshine-photo-cart' ),
        'type'=> 'header',
    );

        $currencies = sunshine_get_currencies();

        $general_fields['3100'] = array(
            'name' => __( 'Currency', 'sunshine-photo-cart' ),
            'id'   => 'currency',
            'type' => 'select',
            'select2' => true,
            'options' => $currencies
        );
        $general_fields['3200'] = array(
            'name' => __( 'Currency symbol position', 'sunshine-photo-cart' ),
            'id'   => 'currency_symbol_position',
            'type' => 'select',
            'options' => array( 'left' => __( 'Left', 'sunshine-photo-cart' ), 'right' => __( 'Right', 'sunshine-photo-cart' ), 'left_space' => __( 'Left space', 'sunshine-photo-cart' ), 'right_space' => __( 'Right space', 'sunshine-photo-cart' ) )
        );
        $general_fields['3300'] = array(
            'name' => __( 'Thousands separator', 'sunshine-photo-cart' ),
            'id'   => 'currency_thousands_separator',
            'type' => 'text',
            'css' => 'width: 50px;'
        );
        $general_fields['3400'] = array(
            'name' => __( 'Decimal separator', 'sunshine-photo-cart' ),
            'id'   => 'currency_decimal_separator',
            'type' => 'text',
            'css' => 'width: 50px;'
        );
        $general_fields['3500'] = array(
            'name' => __( 'Number of decimals', 'sunshine-photo-cart' ),
            'id'   => 'currency_decimals',
            'type' => 'number',
            'css' => 'width: 50px;'
        );

    $general_fields['5000'] = array(
        'id' => 'data',
        'name'=> __( 'Data & Logging' , 'sunshine-photo-cart' ),
        'type'=> 'header',
    );
        $general_fields['5100'] = array(
            'name' => __( 'Data Management', 'sunshine-photo-cart' ),
            'id'   => 'uninstall_delete_data',
            'type' => 'checkbox',
            'description' => __( 'Delete all Galleries, Products, Orders, and settings data will be removed when Sunshine is uninstalled', 'sunshine-photo-cart' )
        );
        $general_fields['5200'] = array(
            'name' => __( 'Enable logging', 'sunshine-photo-cart' ),
            'id'   => 'log',
            'type' => 'checkbox',
            'description' => __( 'Enable logging of all events within Sunshine to help with debugging. Disable when not needed.', 'sunshine-photo-cart' ) . ' ' . (( SPC()->get_option( 'log' ) ) ? '<a href="' . get_bloginfo( 'url' ) . '/wp-content/uploads/sunshine/sunshine.log" target="_blank">' . __( 'View log', 'sunshine-photo-cart' ) . '</a>' : '')
        );


    $general_fields = apply_filters( 'sunshine_options_general', $general_fields );
    ksort( $general_fields );
    $settings[] = array(
        'id' => 'general',
        'title' => __( 'General', 'sunshine-photo-cart' ),
        'fields' => $general_fields,
        //'icon' => SUNSHINE_PHOTO_CART_PATH . 'assets/images/icons/settings.svg'
    );


    /* PAGES */
    $pages_fields = array();
    $pages_fields['1000'] = array(
        'id' => 'pages',
        'name'=> __( 'Page Options' , 'sunshine-photo-cart' ),
        'description' => __( 'The following pages need selecting so that Sunshine knows where they are. These pages should have been created upon installation, if not you will need to create them.', 'sunshine-photo-cart' ),
        'type'=> 'header',
    );
        /*
        $pages_fields['1100'] = array(
            'name' => __( 'Use Shortcode', 'sunshine-photo-cart' ),
            'id'   => 'use_shortcode',
            'type' => 'checkbox',
            'description' => __( 'By default Sunshine is automatically shown on the below pages. However, if you are using the Block Editor or a 3rd party Page Builder you may want to be more specific about where Sunshine appears by using the shortcode [sunshine]. Check this option, select the new page in the dropdown above, <em>and</em> use the shortcode on each of the pages below.','sunshine-photo-cart' ),
            'options' => array( 1 )
        );
        */
        $pages_fields['1200'] = array(
            'id' => 'page',
            'name'=> __( 'Main Galleries Page' , 'sunshine-photo-cart' ),
            'type'=> 'single_select_page',
        );
        $pages_fields['1300'] = array(
            'id' => 'page_cart',
            'name'=> __( 'Cart' , 'sunshine-photo-cart' ),
            'type'=> 'single_select_page',
        );
        $pages_fields['1400'] = array(
            'id' => 'page_checkout',
            'name'=> __( 'Checkout' , 'sunshine-photo-cart' ),
            'type'=> 'single_select_page',
        );
        $pages_fields['1500'] = array(
            'id' => 'page_account',
            'name'=> __( 'Account' , 'sunshine-photo-cart' ),
            'type'=> 'single_select_page',
        );
        $pages_fields['1600'] = array(
            'id' => 'page_favorites',
            'name'=> __( 'Favorites' , 'sunshine-photo-cart' ),
            'type'=> 'single_select_page',
        );
        $pages_fields['1700'] = array(
            'id' => 'page_terms',
            'name'=> __( 'Terms & Conditions' , 'sunshine-photo-cart' ),
            'type'=> 'single_select_page',
        );

    $pages_fields['2000'] = array(
            'id' => 'urls',
            'name'=> __( 'URLs' , 'sunshine-photo-cart' ),
            'type'=> 'header',
    );
        $pages_fields['2100'] = array(
            'name' => __( 'Gallery', 'sunshine-photo-cart' ),
            'id'   => 'endpoint_gallery',
            'type' => 'text',
            'callback' => 'sanitize_title_with_dashes',
            'description' => 'Current gallery URL example: <pre style="display: inline;">'.get_bloginfo( 'url' ).'/<strong>'.SPC()->get_option( 'endpoint_gallery' ).'</strong>/gallery-slug</pre>', // TODO: Use JS to make this dynamic as user types
            'required' => true
        );
        $pages_fields['2300'] = array(
            'name' => __( 'Order', 'sunshine-photo-cart' ),
            'id'   => 'endpoint_order',
            'type' => 'text',
            'description' => 'Current order URL example: <pre style="display: inline;">'.get_bloginfo( 'url' ).'/<strong>'.SPC()->get_option( 'endpoint_order' ).'</strong>/42</pre>',
            'required' => true
        );

    $pages_fields['3000'] = array(
        'id' => 'endpoints',
        'name'=> __( 'Account Endpoints' , 'sunshine-photo-cart' ),
        'description' => __( 'Endpoints are appended to your page URLs to handle specific actions on the account page.', 'sunshine-photo-cart' ),
        'type'=> 'header',
    );
        $pages_fields['3100'] = array(
            'id' => 'account_orders_endpoint',
            'name'=> __( 'Orders' , 'sunshine-photo-cart' ),
            'type'=> 'text',
            'callback' => 'sanitize_title_with_dashes',
            'required' => true
        );
        $pages_fields['3200'] = array(
            'id' => 'account_addresses_endpoint',
            'name'=> __( 'Addresses' , 'sunshine-photo-cart' ),
            'type'=> 'text',
            'callback' => 'sanitize_title_with_dashes',
            'required' => true
        );
        $pages_fields['3300'] = array(
            'id' => 'account_edit_endpoint',
            'name'=> __( 'Account Details' , 'sunshine-photo-cart' ),
            'type'=> 'text',
            'callback' => 'sanitize_title_with_dashes',
            'required' => true
        );

    $page_fields = apply_filters( 'sunshine_options_pages', $pages_fields );
    ksort( $page_fields );
    $settings[] = array(
        'id' => 'pages',
        'title' => __( 'Pages & URLs', 'sunshine-photo-cart' ),
        'fields' => $page_fields,
        'icon' => SUNSHINE_PHOTO_CART_PATH . 'assets/images/icons/pages.svg'
    );

    /* GALLERIES */
    $galleries_fields = array();
    $galleries_fields['1000'] = array(
            'id' => 'admin_options',
            'name'=> __( 'Administration Options' , 'sunshine-photo-cart' ),
            'type'=> 'header',
    );
        $galleries_fields['1100'] = array(
            'name' => __( 'Remove images', 'sunshine-photo-cart' ),
            'id'   => 'delete_images',
            'type' => 'checkbox',
            'description' => __( 'When a gallery is permanently deleted, remove all associated attachments and image files from the servers','sunshine-photo-cart' ),
        );
        $galleries_fields['1200'] = array(
            'name' => __( 'Delete FTP folder', 'sunshine-photo-cart' ),
            'id'   => 'delete_images_folder',
            'type' => 'checkbox',
            'description' => __( 'This will remove the folder and images added via FTP, if this was used to create the gallery','sunshine-photo-cart' ),
        );
        $galleries_fields['1300'] = array(
            'name' => __( 'Show images in Media Library', 'sunshine-photo-cart' ),
            'id'   => 'show_media_library',
            'type' => 'checkbox',
            'description' => __( 'By default Sunshine hides images uploaded to Sunshine galleries in the Media Library, enabling this option will show them instead. Use at your own risk.','sunshine-photo-cart' ),
        );
    $galleries_fields['2000'] = array(
            'id' => 'display_options',
            'name'=> __( 'Display Options' , 'sunshine-photo-cart' ),
            'type'=> 'header',
    );
        $galleries_fields['2100'] = array(
            'name' => __( 'Hide galleries from search engines', 'sunshine-photo-cart' ),
            'id'   => 'hide_galleries',
            'type' => 'checkbox',
            'description' => __( 'Enabling this option will attempt to block search engine bots from crawling and indexing galleries and images','sunshine-photo-cart' ),
        );
        $galleries_fields['2150'] = array(
            'name' => __( 'Gallery Order', 'sunshine-photo-cart' ),
            'id'   => 'gallery_order',
            'type' => 'select',
            'options' => array(
                'menu_order' => __( 'Custom ordering', 'sunshine-photo-cart' ),
                'date_new_old' => __( 'Gallery Creation Date (New to Old)', 'sunshine-photo-cart' ),
                'date_old_new' => __( 'Gallery Creation Date (Old to New)', 'sunshine-photo-cart' ),
                'title' => __( 'Alphabetical', 'sunshine-photo-cart' )
            )
        );
        $galleries_fields['2200'] = array(
            'name' => __( 'Image Order', 'sunshine-photo-cart' ),
            'id'   => 'image_order',
            'type' => 'select',
            'options' => array(
                'menu_order' => __( 'Custom ordering', 'sunshine-photo-cart' ),
                'shoot_order' => __( 'Order images shot (Images MUST have EXIF field "DateTimeDigitized")', 'sunshine-photo-cart' ),
                'date_new_old' => __( 'Image Upload Date (New to Old)', 'sunshine-photo-cart' ),
                'date_old_new' => __( 'Image Upload Date (Old to New)', 'sunshine-photo-cart' ),
                'title' => __( 'Alphabetical', 'sunshine-photo-cart' )
            )
        );
        $galleries_fields['2250'] = array(
            'name' => __( 'Columns', 'sunshine-photo-cart' ),
            'id'   => 'columns',
            'type' => 'select',
            'options' => array( 2 => 2, 3 => 3, 4 => 4, 5 => 5 )
        );
        $galleries_fields['2300'] = array(
            'name' => __( 'Rows', 'sunshine-photo-cart' ),
            'id'   => 'rows',
            'type' => 'number',
            'css' => 'width: 50px;'
        );
        $galleries_fields['2350'] = array(
            'name' => __( 'Image Theft Prevention', 'sunshine-photo-cart' ),
            'id'   => 'disable_right_click',
            'type' => 'checkbox',
            'tip' => __( 'Enabling this option will disable the right click menu and also not allow images to be dragged/dropped to the desktop. NOT a 100% effective method, but should stop most people.','sunshine-photo-cart' ),
            'options' => array( 1 )
        );
        $galleries_fields['2400'] = array(
            'name' => __( 'Proofing Only', 'sunshine-photo-cart' ),
            'id'   => 'proofing',
            'type' => 'checkbox',
            'tip' => __( 'This will remove all aspects of purchasing abilities throughout the site, leaving just image viewing and adding to favorites','sunshine-photo-cart' ),
            'options' => array( 1 )
        );
        $galleries_fields['2450'] = array(
            'name' => __( 'Thumbnail Width', 'sunshine-photo-cart' ),
            'id'   => 'thumbnail_width',
            'type' => 'number',
            'css' => 'width: 50px;'
        );
        $galleries_fields['2500'] = array(
            'name' => __( 'Thumbnail Height', 'sunshine-photo-cart' ),
            'id'   => 'thumbnail_height',
            'type' => 'number',
            'css' => 'width: 50px;'
        );
        $galleries_fields['2550'] = array(
            'name' => __( 'Crop', 'sunshine-photo-cart' ),
            'id'   => 'thumbnail_crop',
            'description' => sprintf( __( 'Enabling this option will not affect already uploaded images. <a href="%s" target="_blank">Please see this help article</a>','sunshine-photo-cart' ), 'http://www.sunshinephotocart.com/docs/thumbnails-not-cropping/' ),
            'tip' => __( 'Should images be cropped to the exact dimensions of your thumbnail width / height','sunshine-photo-cart' ),
            'type' => 'checkbox',
            'options' => array( 1 )
        );
        $galleries_fields['2600'] = array(
            'name' => __( 'Show Image Data', 'sunshine-photo-cart' ),
            'id'   => 'show_image_data',
            'description' => __( 'What to show below image thumbnails','sunshine-photo-cart' ),
            'type' => 'select',
            'options' => array(
                '' => __( 'Nothing', 'sunshine-photo-cart' ),
                'filename' => __( 'Filename', 'sunshine-photo-cart' ),
                'title' => __( 'Title (Images MUST have EXIF field "Title")', 'sunshine-photo-cart' ),
            )
        );
        $galleries_fields['2700'] = array(
    		'name' => __( 'Disable Favorites', 'sunshine-photo-cart' ),
    		'id'   => 'disable_favorites',
    		'type' => 'checkbox',
    	);
        $galleries_fields['2800'] = array(
    		'name' => __( 'Disable Sharing', 'sunshine-photo-cart' ),
    		'id'   => 'disable_sharing',
    		'type' => 'checkbox',
    	);


    $galleries_fields = apply_filters( 'sunshine_options_galleries', $galleries_fields );
    ksort( $galleries_fields );
    $settings[] = array(
        'id' => 'galleries',
        'title' => __( 'Galleries', 'sunshine-photo-cart' ),
        'fields' => $galleries_fields,
        'icon' => SUNSHINE_PHOTO_CART_PATH . 'assets/images/icons/galleries.svg'
    );


    $tax_fields = array();
        $tax_fields['1000'] = array(
            'id' => 'taxes_enabled',
            'name'=> __( 'Enable Taxes' , 'sunshine-photo-cart' ),
            'type'          => 'checkbox',
        );
        $tax_fields['1100'] = array(
            'id' => 'tax_rates',
            'name'=> __( 'Tax Rates' , 'sunshine-photo-cart' ),
            'type' => 'taxes',
            'description' => __( 'Be as specific as you need. Order for most priority. You can include multiple zip/postal codes with commas.'),
            'conditions' => array(
                array(
                    'compare' => '==',
                    'value' => '1',
                    'field' => 'taxes_enabled',
                    'action' => 'show'
                )
            )
        );
        $tax_fields['1300'] = array(
            'id' => 'tax_basis',
            'name'=> __( 'Calculate tax based on	', 'sunshine-photo-cart' ),
            'description'	=> __( 'Which address is used to determine if tax is calculated','sunshine-photo-cart' ),
            'type'=> 'select',
            'options' => array(
                'shipping' => __( 'Shipping Address', 'sunshine-photo-cart' ),
                'billing' => __( 'Billing Address', 'sunshine-photo-cart' ),
                //'all' => __( 'Tax everyone', 'sunshine-photo-cart' )
            ),
            'conditions' => array(
                array(
                    'compare' => '==',
                    'value' => '1',
                    'field' => 'taxes_enabled',
                    'action' => 'show'
                )
            )
        );
        $tax_fields['1400'] = array(
            'name' => __( 'Display prices', 'sunshine-photo-cart' ),
            'id'   => 'display_price',
            'type' => 'radio',
            'options' => array( 'without_tax' => 'Excluding tax', 'with_tax' => 'Including tax' )
        );
        $tax_fields['1500'] = array(
            'name' => __( 'Prices entered with tax', 'sunshine-photo-cart' ),
            'id'   => 'price_has_tax',
            'type' => 'radio',
            'options' => array( 'no' => 'No, prices do not have tax included', 'yes' => 'Yes, prices do have tax included' )
        );
        $tax_fields['1600'] = array(
            'name' => __( 'Price with tax suffix', 'sunshine-photo-cart' ),
            'id'   => 'price_with_tax_suffix',
            'type' => 'text',
            'description' => __( 'This shows after the price', 'sunshine-photo-cart' )
        );

    $tax_fields = apply_filters( 'sunshine_options_taxes', $tax_fields );
    ksort( $tax_fields );
    $settings[] = array(
        'id' => 'taxes',
        'title' => __( 'Taxes', 'sunshine-photo-cart' ),
        'fields' => $tax_fields,
        'icon' => SUNSHINE_PHOTO_CART_PATH . 'assets/images/icons/taxes.svg'
    );

    $checkout_fields = array();
        $checkout_fields['100'] = array(
            'name' => __( 'Distraction Free Checkout', 'sunshine-photo-cart' ),
            'id'   => 'checkout_standalone',
            'type' => 'checkbox',
            'description' => __( 'Remove site header/footer and let user focus only the checkout experience','sunshine-photo-cart' ),
        );

        $checkout_fields['1100'] = array(
            'name' => __( 'Allow Guest Checkout', 'sunshine-photo-cart' ),
            'id'   => 'allow_guest_checkout',
            'type' => 'checkbox',
            'description' => __( 'Allow users to checkout as a guest (do not require a user account)','sunshine-photo-cart' ),
        );

        $checkout_fields['1200'] = array(
            'name' => __( 'Allowed Countries', 'sunshine-photo-cart' ),
            'description' => __( 'Which countries users can select at checkout. If empty, all countries are allowed.','sunshine-photo-cart' ),
            'id'   => 'allowed_countries',
            'type' => 'select',
            'select2' => true,
            'multiple' => true,
            'options' => SPC()->countries->get_countries()
        );
        $checkout_fields['1300'] = array(
            'name' => __( 'Google Maps API Key', 'sunshine-photo-cart' ),
            'id'   => 'google_maps_api_key',
            'type' => 'text',
            'description' => sprintf( __( 'Enter a Google API key to enable address autocomplete at checkout, <a href="%s" target="_blank">learn more here</a>','sunshine-photo-cart' ), 'https://www.sunshinephotocart.com/docs/address-autocomplete' ),
        );


    $checkout_fields['2000'] = array( 'id' => 'display_fields', 'name' => __( 'Display Fields', 'sunshine-photo-cart' ), 'type' => 'header', 'description' => '' );
    /* TODO: Let users check these fields or a more advanced checkout form builder?
        $checkout_fields['2100'] = array(
            'name' => __( 'Billing Fields', 'sunshine-photo-cart' ),
            'id'   => 'billing_fields',
            'type' => 'checkbox_multi',
            'description' => __( 'Check fields you want visible','sunshine-photo-cart' ),
            'options' => array(
                'country' => __( 'Country', 'sunshine-photo-cart' ),
                'first_name' => __( 'First Name', 'sunshine-photo-cart' ),
                'last_name' => __( 'Last Name', 'sunshine-photo-cart' ),
                'address' => __( 'Address', 'sunshine-photo-cart' ),
                'address2' => __( 'Address 2', 'sunshine-photo-cart' ),
                'city' => __( 'City', 'sunshine-photo-cart' ),
                'state' => __( 'State / Province', 'sunshine-photo-cart' ),
                'zip' => __( 'Zip / Postcode', 'sunshine-photo-cart' ),
            )
        );
        $checkout_fields['2200'] = array(
            'name' => __( 'Shipping Fields', 'sunshine-photo-cart' ),
            'id'   => 'shipping_fields',
            'type' => 'checkbox_multi',
            'description' => __( 'Check fields you want visible','sunshine-photo-cart' ),
            'options' => array(
                'country' => __( 'Country', 'sunshine-photo-cart' ),
                'first_name' => __( 'First Name', 'sunshine-photo-cart' ),
                'last_name' => __( 'Last Name', 'sunshine-photo-cart' ),
                'address' => __( 'Address', 'sunshine-photo-cart' ),
                'address2' => __( 'Address 2', 'sunshine-photo-cart' ),
                'city' => __( 'City', 'sunshine-photo-cart' ),
                'state' => __( 'State / Province', 'sunshine-photo-cart' ),
                'zip' => __( 'Zip / Postcode', 'sunshine-photo-cart' ),
            )
        );
        */
        $checkout_fields['2300'] = array(
            'name' => __( 'Other Fields', 'sunshine-photo-cart' ),
            'id'   => 'general_fields',
            'type' => 'checkbox_multi',
            'options' => array(
                'phone' => __( 'Phone', 'sunshine-photo-cart' ),
                'notes' => __( 'Notes', 'sunshine-photo-cart' ),
                'vat' => __( 'VAT Number', 'sunshine-photo-cart' ),
            )
        );
        $checkout_fields['2301'] = array(
            'name' => __( 'VAT Label', 'sunshine-photo-cart' ),
            'id'   => 'vat_label',
            'type' => 'text',
        );

    $checkout_fields = apply_filters( 'sunshine_options_checkout', $checkout_fields );
    ksort( $checkout_fields );
    $settings[] = array(
        'id' => 'checkout',
        'title' => __( 'Checkout', 'sunshine-photo-cart' ),
        'fields' => $checkout_fields,
        'icon' => SUNSHINE_PHOTO_CART_PATH . 'assets/images/icons/checkout.svg'
    );

    /* PAYMENTS */

    $payment_fields = array();

    $payment_methods = SPC()->payment_methods->get_payment_methods();
    if ( empty( $_GET['payment_method'] ) ) {
        $payment_fields['1'] = array(
            'name' => '',//__( 'Available payment methods', 'sunshine-photo-cart' ),
            'id'   => 'payment_methods_wrapper',
            'type' => 'payment_methods',
            'options' => $payment_methods,
        );
    }

    if ( !empty( $payment_methods ) ) {
        foreach ( $payment_methods as $id => $payment_method ) {
            $payment_method_fields = apply_filters( 'sunshine_options_payment_method_' . $id, array() );
            if ( !empty( $payment_method_fields ) ) {
                foreach ( $payment_method_fields as &$field ) {
                    if ( empty( $_GET['payment_method'] ) || ( isset( $_GET['payment_method'] ) && $_GET['payment_method'] != $id ) ) {
                        $field['class'] = ( !empty( $field['class'] ) ) ? $field['class'] . ' hidden' : 'hidden';
                    }
                }
            }
            $payment_fields = array_merge( $payment_fields, $payment_method_fields );
        }
    }

    $payment_fields = apply_filters( 'sunshine_options_payment_methods', $payment_fields );
    ksort( $payment_fields );
    $settings[] = array(
        'id' => 'payment_methods',
        'title' => __( 'Payments', 'sunshine-photo-cart' ),
        'fields' => $payment_fields,
        'icon' => SUNSHINE_PHOTO_CART_PATH . 'assets/images/icons/payment.svg'
    );

    /* SHIPPING */

    $shipping_fields = array();

    $available_shipping_methods = sunshine_get_available_shipping_methods();

    if ( empty( $_GET['instance_id'] ) ) {
        $shipping_fields['1'] = array(
            'name' => '',//__( 'Available shipping methods', 'sunshine-photo-cart' ),
            'id'   => 'shipping_methods_wrapper',
            'type' => 'shipping_methods',
            'options' => $available_shipping_methods,
        );
    }

    if ( !empty( $available_shipping_methods ) ) {
        foreach ( $available_shipping_methods as $instance_id => $shipping_method ) {
            $shipping_method_fields = apply_filters( 'sunshine_options_shipping_method_' . $shipping_method['id'], array(), $instance_id );
            if ( !empty( $shipping_method_fields ) ) {
                foreach ( $shipping_method_fields as &$field ) {
                    if ( empty( $_GET['instance_id'] ) || ( isset( $_GET['instance_id'] ) && $_GET['instance_id'] != $instance_id ) ) {
                        $field['class'] = ( !empty( $field['class'] ) ) ? $field['class'] . ' hidden' : 'hidden';
                    }
                }
            }
            $shipping_fields = array_merge( $shipping_fields, $shipping_method_fields );
        }
    }

    $shipping_fields = apply_filters( 'sunshine_options_shipping', $shipping_fields );

    ksort( $shipping_fields );
    $settings[] = array(
        'id' => 'shipping_methods',
        'title' => __( 'Delivery & Shipping', 'sunshine-photo-cart' ),
        'fields' => $shipping_fields,
        'icon' => SUNSHINE_PHOTO_CART_PATH . 'assets/images/icons/shipping.svg'
    );

    $design_fields = array();
        $design_fields[1000] = array( 'name' => __( 'Design Elements','sunshine-photo-cart' ), 'type' => 'header', 'description' => '' );
            $design_fields[1100] = array(
                'name' => __( 'Theme', 'sunshine-photo-cart' ),
                'id'   => 'theme',
                'type' => 'select',
                'options' => array(
                    'theme' => __( 'My WordPress Theme', 'sunshine-photo-cart' ),
                    'default' => __( 'Default Sunshine Theme', 'sunshine-photo-cart' ),
                    '2013' => __( 'Modern Sunshine Theme', 'sunshine-photo-cart' ),
                    'hooks' => __( 'Hook-Based Theme', 'sunshine-photo-cart' )
                )
            );
            $design_fields[1200] = array(
                'name' => __( 'Logo', 'sunshine-photo-cart' ),
                'id'   => 'logo',
                'type' => 'image'
            );
            $design_fields[1300] = array(
                'name' => __( 'Dark mode', 'sunshine-photo-cart' ),
                'id'   => 'dark_mode',
                'type' => 'checkbox',
                'description' => __( 'If you have a dark theme, enable this option and Sunshine will adjust styles to be dark to match','sunshine-photo-cart' ),
                'conditions' => array(
                    array(
                        'compare' => '==',
                        'value' => 'hooks',
                        'field' => 'theme',
                        'action' => 'show'
                    )
                )
            );


        $design_fields[2000] = array( 'name' => __( 'Miscellaneous Elements','sunshine-photo-cart' ), 'type' => 'header', 'description' => '' );
            $design_fields[2100] = array(
                'name' => __( 'Auto-include Sunshine main menu', 'sunshine-photo-cart' ),
                'id'   => 'main_menu',
                'type' => 'checkbox',
                'description' => __( 'Automatically have the Sunshine Main Menu appear above the Sunshine content','sunshine-photo-cart' ),
            );
            $design_fields[2200] = array(
                'name' => __( 'Hide link to main galleries page', 'sunshine-photo-cart' ),
                'id'   => 'hide_galleries_link',
                'type' => 'checkbox',
                'description' => __( 'Hide the link to your main galleries page in any Sunshine menus. Helpful if you want users to stick to just a single gallery.','sunshine-photo-cart' ),
            );

    $design_fields = apply_filters( 'sunshine_options_templates', $design_fields );
    ksort( $design_fields );
    $settings[] = array(
        'id' => 'display',
        'title' => __( 'Design', 'sunshine-photo-cart' ),
        'fields' => $design_fields,
        'icon' => SUNSHINE_PHOTO_CART_PATH . 'assets/images/icons/design.svg'
    );

    /* Email Settings */
    $email_fields = array();
        $email_fields[1000] = array( 'name' => __( 'Notifications', 'sunshine-photo-cart' ), 'type' => 'header', 'description' => '' );
            $email_fields[1100] = array(
                'name' => __( 'Order Notifications', 'sunshine-photo-cart' ),
                'description' => __( 'Email address(es) to receive order notifications. Separate multiple emails with a comma.','sunshine-photo-cart' ),
                'id'   => 'order_notifications',
                'type' => 'text',
            );
            $email_fields[1200] = array(
                'name' => __( 'Favorite Notifications', 'sunshine-photo-cart' ),
                'description' => __( 'Email address(es) to receive submitted favorites notifications. Separate multiple emails with a comma.','sunshine-photo-cart' ),
                'id'   => 'favorite_notifications',
                'type' => 'text',
            );

        $email_fields[2000] = array( 'name' => __( 'Email From', 'sunshine-photo-cart' ), 'type' => 'header', 'description' => '' );
            $email_fields[2100] = array(
                'name' => __( 'From Name', 'sunshine-photo-cart' ),
                'description' => __( 'When emails are sent to customers, what name should they come from','sunshine-photo-cart' ),
                'id'   => 'from_name',
                'type' => 'text',
            );
            $email_fields[2200] = array(
                'name' => __( 'From Email', 'sunshine-photo-cart' ),
                'description' => __( 'When emails are sent to customers, what email address should they come from','sunshine-photo-cart' ),
                'id'   => 'from_email',
                'type' => 'text',
            );

        /* Extra Email Content */
        $email_fields[4000] = array( 'name' => __( 'Email Text', 'sunshine-photo-cart' ), 'type' => 'header', 'description' => '' );
            $email_fields[4200] = array(
                'name' => __( 'Order Receipt: Subject','sunshine-photo-cart' ),
                'id'   => 'email_subject_order_receipt',
                'type' => 'text',
            );
            $email_fields[4201] = array(
                'name' => __( 'Order Receipt: Content', 'sunshine-photo-cart' ),
                'description' => __( 'Message at the top of email receipts','sunshine-photo-cart' ),
                'id'   => 'email_receipt',
                'type' => 'wysiwyg',
                'settings' => array( 'textarea_rows' => 4 )
            );
            $email_fields[4300] = array(
                'name' => __( 'Register: Subject','sunshine-photo-cart' ),
                'id'   => 'email_subject_register',
                'type' => 'text',
            );
            $email_fields[4301] = array(
                'name' => __( 'Register: Content', 'sunshine-photo-cart' ),
                'description' => __( 'Message at top of new user registration email','sunshine-photo-cart' ),
                'id'   => 'email_register',
                'type' => 'wysiwyg',
                'settings' => array( 'textarea_rows' => 4 )
            );
            $email_fields[4400] = array(
                'name' => __( 'Order Status: Subject','sunshine-photo-cart' ),
                'id'   => 'email_subject_order_status',
                'type' => 'text',
            );
            $email_fields[4401] = array(
                'name' => __( 'Order Status: Content', 'sunshine-photo-cart' ),
                'description' => __( 'Message added to bottom order status change email','sunshine-photo-cart' ),
                'id'   => 'email_order_status',
                'type' => 'wysiwyg',
                'settings' => array( 'textarea_rows' => 4 )
            );
            $email_fields[4500] = array(
                'name' => __( 'Reset Password: Subject', 'sunshine-photo-cart' ),
                'id'   => 'email_subject_reset_password',
                'type' => 'text',
            );
            $email_fields[4501] = array(
                'name' => __( 'Reset Password: Content', 'sunshine-photo-cart' ),
                'id'   => 'email_reset_password',
                'type' => 'wysiwyg',
                'settings' => array( 'textarea_rows' => 4 )
            );
            $email_fields[9999] = array(
                'name' => __( 'Email Signature','sunshine-photo-cart' ),
                'description' => __( 'Appears at the end of every email message','sunshine-photo-cart' ),
                'id'   => 'email_signature',
                'type' => 'wysiwyg',
                'settings' => array( 'textarea_rows' => 4 )
            );


    $email_fields = apply_filters( 'sunshine_options_email', $email_fields );
    ksort( $email_fields );
    $settings[] = array(
        'id' => 'email',
        'title' => __( 'Email', 'sunshine-photo-cart' ),
        'fields' => $email_fields,
        'icon' => SUNSHINE_PHOTO_CART_PATH . 'assets/images/icons/email.svg'
    );

    $settings = apply_filters( 'sunshine_options_extra', $settings );

    /*
    $license_fields = array();
    $license_fields[1000] = array( 'name' => __( 'Sunshine Photo Cart License', 'sunshine-photo-cart' ), 'type' => 'header', 'description' => '' );
    $license_fields = apply_filters( 'sunshine_options_licenses_primary', $license_fields );
    $addon_license_options = apply_filters( 'sunshine_options_licenses', array() );
    if ( !empty( $addon_license_options ) ) {
        $license_fields[] = array( 'name' => __( 'Add-on Sunshine Licenses', 'sunshine-photo-cart' ), 'type' => 'header', 'description' => '' );
        $license_fields = array_merge( $license_fields, $addon_license_options );
    }

    $license_fields = apply_filters( 'sunshine_options_licenses', $license_fields );
    ksort( $license_fields );
    $settings[] = array(
        'id' => 'licenses',
        'title' => __( 'Licenses', 'sunshine-photo-cart' ),
        'fields' => $license_fields,
        'icon' => SUNSHINE_PHOTO_CART_PATH . 'assets/images/icons/licenses.svg'
    );
    */

    $settings = apply_filters( 'sunshine_options', $settings );

    return $settings;

}
