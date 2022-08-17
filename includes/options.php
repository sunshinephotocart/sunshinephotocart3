<?php
global $sunshine;
//if ( is_array( SPC()->options ) ):

$options = array();

/* General Options */
$options[] = array( 'name' => __( 'General', 'sunshine-photo-cart' ), 'type' => 'heading' );

$options[] = array( 'name' => __( 'Localization', 'sunshine-photo-cart' ), 'type' => 'header', 'desc' => '' );
$options[] = array(
	'name' => __( 'Default Country', 'sunshine-photo-cart' ),
	'id'   => 'country',
	'type' => 'select',
	'select2' => true,
	'options' => SunshineCountries::$countries
);

$options[] = array( 'name' => __( 'Taxes', 'sunshine-photo-cart' ), 'type' => 'header', 'desc' => '' );
foreach ( SunshineCountries::$countries as $key => $country ) {
	$states = SunshineCountries::get_states( $key );
	if ( $states ) {
		$tax_options[$key] = $country;
		foreach ( $states as $state_key => $state )
			$tax_options["$key|$state_key"] = $country.' &mdash; '.$state;
	} else
		$tax_options[$key] = $country;
}
asort( $tax_options );
$tax_options = array_merge( array( '' => __( 'Do not use taxes', 'sunshine-photo-cart' ) ), $tax_options );
$options[] = array(
	'name' => __( 'Country / State', 'sunshine-photo-cart' ),
	'tip' => __( 'What country or state should have taxes applied','sunshine-photo-cart' ),
	'id'   => 'tax_location',
	'type' => 'select',
	'select2' => true,
	'options' => $tax_options
);
$options[] = array(
	'name' => __( 'Tax rate (%)', 'sunshine-photo-cart' ),
	'desc' => __( 'Number only', 'sunshine-photo-cart' ),
	'id'   => 'tax_rate',
	'type' => 'text',
	'css' => 'width: 50px;'
);
$options[] = array(
	'name' => __( 'Calculate tax based on', 'sunshine-photo-cart' ),
	'tip' => __( 'Which address is used to determine if tax is calculated','sunshine-photo-cart' ),
	'id'   => 'tax_basis',
	'type' => 'select',
	'options' => array(
		'shipping' => __( 'Shipping Address', 'sunshine-photo-cart' ),
		'billing' => __( 'Billing Address', 'sunshine-photo-cart' ),
		'all' => __( 'Tax everyone', 'sunshine-photo-cart' )
	)
);
$options[] = array(
	'name' => __( 'Tax entire order', 'sunshine-photo-cart' ),
	'id'   => 'tax_entire_order_one_item',
	'type' => 'checkbox',
	'desc' => __( 'If just one item in cart is taxable, apply tax to the entire order', 'sunshine-photo-cart' ),
	'options' => array( 1 => 'Tax entire order' )
);
$options[] = array(
	'name' => __( 'Display prices', 'sunshine-photo-cart' ),
	'id'   => 'display_price',
	'type' => 'radio',
	'options' => array( 'without_tax' => 'Excluding tax', 'with_tax' => 'Including tax' )
);
$options[] = array(
	'name' => __( 'Prices entered with tax', 'sunshine-photo-cart' ),
	'id'   => 'price_has_tax',
	'type' => 'radio',
	'options' => array( 'no' => 'No, prices do not have tax included', 'yes' => 'Yes, prices do have tax included' )
);
$options[] = array(
	'name' => __( 'Price with tax suffix', 'sunshine-photo-cart' ),
	'id'   => 'price_with_tax_suffix',
	'type' => 'text',
	'desc' => __( 'This shows after the price', 'sunshine-photo-cart' )
);

$options = apply_filters( 'sunshine_options_taxes', $options );

$options[] = array( 'name' => __( 'Currency Formatting', 'sunshine-photo-cart' ), 'type' => 'header', 'desc' => '' );

$currencies = apply_filters( 'sunshine_currencies',
	array(
		'AED' => __( 'United Arab Emirates Dirham', 'sunshine-photo-cart' ),
		'ARS' => __( 'Argentine Peso', 'sunshine-photo-cart' ),
		'AUD' => __( 'Australian Dollars', 'sunshine-photo-cart' ),
		'BDT' => __( 'Bangladeshi Taka', 'sunshine-photo-cart' ),
		'BRL' => __( 'Brazilian Real', 'sunshine-photo-cart' ),
		'BGN' => __( 'Bulgarian Lev', 'sunshine-photo-cart' ),
		'CAD' => __( 'Canadian Dollars', 'sunshine-photo-cart' ),
		'CLP' => __( 'Chilean Peso', 'sunshine-photo-cart' ),
		'CNY' => __( 'Chinese Yuan', 'sunshine-photo-cart' ),
		'COP' => __( 'Colombian Peso', 'sunshine-photo-cart' ),
		'CZK' => __( 'Czech Koruna', 'sunshine-photo-cart' ),
		'DKK' => __( 'Danish Krone', 'sunshine-photo-cart' ),
		'DOP' => __( 'Dominican Peso', 'sunshine-photo-cart' ),
		'EUR' => __( 'Euros', 'sunshine-photo-cart' ),
		'HKD' => __( 'Hong Kong Dollar', 'sunshine-photo-cart' ),
		'HRK' => __( 'Croatia kuna', 'sunshine-photo-cart' ),
		'HUF' => __( 'Hungarian Forint', 'sunshine-photo-cart' ),
		'ISK' => __( 'Icelandic krona', 'sunshine-photo-cart' ),
		'IDR' => __( 'Indonesia Rupiah', 'sunshine-photo-cart' ),
		'INR' => __( 'Indian Rupee', 'sunshine-photo-cart' ),
		'NPR' => __( 'Nepali Rupee', 'sunshine-photo-cart' ),
		'ILS' => __( 'Israeli Shekel', 'sunshine-photo-cart' ),
		'JPY' => __( 'Japanese Yen', 'sunshine-photo-cart' ),
		'KES' => __( 'Kenyan Shilling', 'sunshine-photo-cart' ),
		'KIP' => __( 'Lao Kip', 'sunshine-photo-cart' ),
		'KRW' => __( 'South Korean Won', 'sunshine-photo-cart' ),
		'MYR' => __( 'Malaysian Ringgits', 'sunshine-photo-cart' ),
		'MXN' => __( 'Mexican Peso', 'sunshine-photo-cart' ),
		'NGN' => __( 'Nigerian Naira', 'sunshine-photo-cart' ),
		'NOK' => __( 'Norwegian Krone', 'sunshine-photo-cart' ),
		'NZD' => __( 'New Zealand Dollar', 'sunshine-photo-cart' ),
		'PYG' => __( 'Paraguayan Guaraní', 'sunshine-photo-cart' ),
		'PEN' => __( 'Peruvian Sol', 'sunshine-photo-cart' ),
		'PHP' => __( 'Philippine Pesos', 'sunshine-photo-cart' ),
		'PLN' => __( 'Polish Zloty', 'sunshine-photo-cart' ),
		'GBP' => __( 'Pounds Sterling', 'sunshine-photo-cart' ),
		'QAR' => __( 'Qatari Riyal', 'sunshine-photo-cart' ),
		'RON' => __( 'Romanian Leu', 'sunshine-photo-cart' ),
		'RUB' => __( 'Russian Ruble', 'sunshine-photo-cart' ),
		'SCR' => __( 'Seychelles Rupee', 'sunshine-photo-cart' ),
		'SGD' => __( 'Singapore Dollar', 'sunshine-photo-cart' ),
		'ZAR' => __( 'South African rand', 'sunshine-photo-cart' ),
		'SEK' => __( 'Swedish Krona', 'sunshine-photo-cart' ),
		'CHF' => __( 'Swiss Franc', 'sunshine-photo-cart' ),
		'TWD' => __( 'Taiwan New Dollars', 'sunshine-photo-cart' ),
		'THB' => __( 'Thai Baht', 'sunshine-photo-cart' ),
		'TRY' => __( 'Turkish Lira', 'sunshine-photo-cart' ),
		'UAH' => __( 'Ukrainian Hryvnia', 'sunshine-photo-cart' ),
		'USD' => __( 'US Dollars', 'sunshine-photo-cart' ),
		'VUV' => __( 'Vanuatu', 'sunshine-photo-cart' ),
		'VEF' => __( 'Venezuelan bol&iacute;var', 'sunshine-photo-cart' ),
		'VND' => __( 'Vietnamese Dong', 'sunshine-photo-cart' ),
		'EGP' => __( 'Egyptian Pound', 'sunshine-photo-cart' ),
	)
);

$options[] = array(
	'name' => __( 'Currency', 'sunshine-photo-cart' ),
	'id'   => 'currency',
	'type' => 'select',
	'select2' => true,
	'options' => $currencies
);
$options[] = array(
	'name' => __( 'Currency symbol position', 'sunshine-photo-cart' ),
	'id'   => 'currency_symbol_position',
	'type' => 'select',
	'options' => array( 'left' => __( 'Left', 'sunshine-photo-cart' ), 'right' => __( 'Right', 'sunshine-photo-cart' ), 'left_space' => __( 'Left space', 'sunshine-photo-cart' ), 'right_space' => __( 'Right space', 'sunshine-photo-cart' ) )
);
$options[] = array(
	'name' => __( 'Thousands separator', 'sunshine-photo-cart' ),
	'id'   => 'currency_thousands_separator',
	'type' => 'text',
	'css' => 'width: 50px;'
);
$options[] = array(
	'name' => __( 'Decimal separator', 'sunshine-photo-cart' ),
	'id'   => 'currency_decimal_separator',
	'type' => 'text',
	'css' => 'width: 50px;'
);
$options[] = array(
	'name' => __( 'Number of decimals', 'sunshine-photo-cart' ),
	'id'   => 'currency_decimals',
	'type' => 'text',
	'css' => 'width: 50px;'
);

$options[] = array( 'name' => __( 'Cart, Checkout and Accounts', 'sunshine-photo-cart' ), 'type' => 'header', 'desc' => '' );
$options[] = array(
	'name' => __( 'Require account to see products', 'sunshine-photo-cart' ),
	'id'   => 'add_to_cart_require_account',
	'type' => 'checkbox',
	'tip' => __( 'Enabling this option means users cannot see products or add them to cart unless they have created an account and are logged in.','sunshine-photo-cart' ),
	'options' => array( 1 => 'Require account' )
);

$options[] = array( 'name' => __( 'URLs', 'sunshine-photo-cart' ), 'type' => 'header', 'desc' => '' );
$options[] = array(
	'name' => __( 'Gallery Endpoint', 'sunshine-photo-cart' ),
	'id'   => 'endpoint_gallery',
	'type' => 'text',
	'desc' => 'Current gallery URL example: <pre style="display: inline;">'.get_permalink( SPC()->get_option( 'page' ) ).'<strong>'.SPC()->get_option( 'endpoint_gallery' ).'</strong>/gallery-slug</pre>'
);
$options[] = array(
	'name' => __( 'Image Endpoint', 'sunshine-photo-cart' ),
	'id'   => 'endpoint_image',
	'type' => 'text',
	'desc' => 'Current image URL example: <pre style="display: inline;">'.get_permalink( SPC()->get_option( 'page' ) ).'<strong>'.SPC()->get_option( 'endpoint_image' ).'</strong>/image-slug</pre>'
);
$options[] = array(
	'name' => __( 'Order Endpoint', 'sunshine-photo-cart' ),
	'id'   => 'endpoint_order',
	'type' => 'text',
	'desc' => 'Current order URL example: <pre style="display: inline;">'.get_permalink( SPC()->get_option( 'page' ) ).'<strong>'.SPC()->get_option( 'endpoint_order' ).'</strong>/42</pre>'
);

$options[] = array( 'name' => __( 'Order Statuses', 'sunshine-photo-cart' ), 'type' => 'header', 'desc' => sprintf( __( 'To manage order status names and descriptions, <a href="%s">click here</a>', 'sunshine-photo-cart' ), admin_url( 'edit-tags.php?taxonomy=sunshine-order-status&post_type=sunshine-order' ) ) );

$options[] = array( 'name' => __( 'Uninstall', 'sunshine-photo-cart' ), 'type' => 'header', 'desc' => '' );
$options[] = array(
	'name' => __( 'Data', 'sunshine-photo-cart' ),
	'id'   => 'uninstall_delete_data',
	'type' => 'checkbox',
	'desc' => __( 'Delete all Galleries, Products, Orders, and settings data will be removed when Sunshine is uninstalled', 'sunshine-photo-cart' ),
	'options' => array( 1 )
);
/* Not ready yet
$options[] = array(
	'name' => __( 'Images', 'sunshine-photo-cart' ),
	'id'   => 'uninstall_delete_attachments',
	'type' => 'checkbox',
	'desc' => __( 'Delete all images/attachments when Sunshine is uninstalled (if you have a lot of images this may not fully process before your server times out and may have to manually finish deleting files via FTP)', 'sunshine-photo-cart' ),
	'options' => array( 1 )
);
*/
$options = apply_filters( 'sunshine_options_general', $options );

/* Pages */
$options[] = array( 'name' => __( 'Pages', 'sunshine-photo-cart' ), 'type' => 'heading' );
$options[] = array( 'name' => __( 'Page options', 'sunshine-photo-cart' ), 'type' => 'header', 'desc' => __( 'The following pages need selecting so that Sunshine knows where they are. These pages should have been created upon installation, if not you will need to create them.', 'sunshine-photo-cart' ) );

$options[] = array(
	'name' => __( 'Use Shortcode', 'sunshine-photo-cart' ),
	'id'   => 'use_shortcode',
	'type' => 'checkbox',
	'desc' => __( 'By default Sunshine is automatically shown on the below pages. However, if you are using the Block Editor or a 3rd party Page Builder you may want to be more specific about where Sunshine appears by using the shortcode [sunshine]. Check this option, select the new page in the dropdown above, <em>and</em> use the shortcode on each of the pages below.','sunshine-photo-cart' ),
	'options' => array( 1 )
);
$options[] = array(
	'name' => __( 'Main Galleries Page', 'sunshine-photo-cart' ),
	'desc' => __( 'Choose which page Sunshine will be displayed on','sunshine-photo-cart' ),
	'id'   => 'page',
	'select2' => true,
	'type' => 'single_select_page'
);
$options[] = array(
	'name' => __( 'Cart', 'sunshine-photo-cart' ),
	'id'   => 'page_cart',
	'select2' => true,
	'type' => 'single_select_page'
);
$options[] = array(
	'name' => __( 'Checkout', 'sunshine-photo-cart' ),
	'id'   => 'page_checkout',
	'select2' => true,
	'type' => 'single_select_page'
);
$options[] = array(
	'name' => __( 'Account', 'sunshine-photo-cart' ),
	'id'   => 'page_account',
	'select2' => true,
	'type' => 'single_select_page'
);
$options = apply_filters( 'sunshine_options_pages', $options );

/* Galleries */
$options[] = array( 'name' => __( 'Galleries', 'sunshine-photo-cart' ), 'type' => 'heading' );
$options[] = array( 'name' => __( 'Administration Options', 'sunshine-photo-cart' ), 'type' => 'header', 'desc' => '' );

$options[] = array(
	'name' => __( 'Delete Media Library images', 'sunshine-photo-cart' ),
	'id'   => 'delete_images',
	'type' => 'checkbox',
	'tip' => __( 'This will remove all images from the Media Library AND the actual image files from your server when a gallery is permanently deleted','sunshine-photo-cart' ),
	'options' => array( 1 )
);
$options[] = array(
	'name' => __( 'Delete FTP folder', 'sunshine-photo-cart' ),
	'id'   => 'delete_images_folder',
	'type' => 'checkbox',
	'tip' => __( 'This will remove the folder and images added via FTP, if this was used to create the gallery','sunshine-photo-cart' ),
	'options' => array( 1 )
);
$options[] = array(
	'name' => __( 'Show images in Media Library', 'sunshine-photo-cart' ),
	'id'   => 'show_media_library',
	'type' => 'checkbox',
	'tip' => __( 'By default Sunshine hides images uploaded to Sunshine galleries in the Media Library, enabling this option will show them instead. Use at your own risk.','sunshine-photo-cart' ),
	'options' => array( 1 )
);


$options[] = array( 'name' => __( 'Display Options', 'sunshine-photo-cart' ), 'type' => 'header', 'desc' => '' );
$options[] = array(
	'name' => __( 'Hide galleries from search engines', 'sunshine-photo-cart' ),
	'id'   => 'hide_galleries',
	'type' => 'checkbox',
	'tip' => __( 'Enabling this option will keep Sunshine galleries out of other plugins XML sitemaps and attempt to block search engine bots from crawling and indexing galleries','sunshine-photo-cart' ),
	'options' => array( 1 )
);
$options[] = array(
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
$options[] = array(
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

$options[] = array(
	'name' => __( 'Columns', 'sunshine-photo-cart' ),
	'id'   => 'columns',
	'type' => 'select',
	'options' => array( 2 => 2, 3 => 3, 4 => 4, 5 => 5 )
);
$options[] = array(
	'name' => __( 'Rows', 'sunshine-photo-cart' ),
	'id'   => 'rows',
	'type' => 'text',
	'css' => 'width: 50px;'
);
$options[] = array(
	'name' => __( 'Image Theft Prevention', 'sunshine-photo-cart' ),
	'id'   => 'disable_right_click',
	'type' => 'checkbox',
	'tip' => __( 'Enabling this option will disable the right click menu and also not allow images to be dragged/dropped to the desktop. NOT a 100% effective method, but should stop most people.','sunshine-photo-cart' ),
	'options' => array( 1 )
);
$options[] = array(
	'name' => __( 'Proofing Only', 'sunshine-photo-cart' ),
	'id'   => 'proofing',
	'type' => 'checkbox',
	'tip' => __( 'This will remove all aspects of purchasing abilities throughout the site, leaving just image viewing and adding to favorites','sunshine-photo-cart' ),
	'options' => array( 1 )
);
$options[] = array(
	'name' => __( 'Thumbnail Width', 'sunshine-photo-cart' ),
	'id'   => 'thumbnail_width',
	'type' => 'text',
	'css' => 'width: 50px;'
);
$options[] = array(
	'name' => __( 'Thumbnail Height', 'sunshine-photo-cart' ),
	'id'   => 'thumbnail_height',
	'type' => 'text',
	'css' => 'width: 50px;'
);
$options[] = array(
	'name' => __( 'Crop', 'sunshine-photo-cart' ),
	'id'   => 'thumbnail_crop',
	'desc' => sprintf( __( 'Enabling this option will not affect already uploaded images. <a href="%s" target="_blank">Please see this help article</a>','sunshine-photo-cart' ), 'http://www.sunshinephotocart.com/docs/thumbnails-not-cropping/' ),
	'tip' => __( 'Should images be cropped to the exact dimensions of your thumbnail width / height','sunshine-photo-cart' ),
	'type' => 'checkbox',
	'options' => array( 1 )
);
$options[] = array(
	'name' => __( 'Show Image Data', 'sunshine-photo-cart' ),
	'id'   => 'show_image_data',
	'tip' => __( 'What to show below images','sunshine-photo-cart' ),
	'type' => 'select',
	'options' => array(
		'' => __( 'Nothing', 'sunshine-photo-cart' ),
		'filename' => __( 'Filename', 'sunshine-photo-cart' ),
		'title' => __( 'Title (Images MUST have EXIF field "Title")', 'sunshine-photo-cart' ),
	)
);


$options = apply_filters( 'sunshine_options_galleries', $options );

/* Checkout */
$options[] = array( 'name' => __( 'Checkout', 'sunshine-photo-cart' ), 'type' => 'heading' );

$options[] = array(
	'name' => __( 'Allow Guest Checkout', 'sunshine-photo-cart' ),
	'id'   => 'allow_guest_checkout',
	'type' => 'checkbox',
	'tip' => __( 'Allow users to checkout as a guest (do not require a user account)','sunshine-photo-cart' ),
	'options' => array( 1 )
);

$options[] = array(
	'name' => __( 'Allowed Countries', 'sunshine-photo-cart' ),
	'tip' => __( 'Which countries users can select at checkout','sunshine-photo-cart' ),
	'id'   => 'allowed_countries',
	'type' => 'select',
	'select2' => true,
	'multiple' => true,
	'options' => array_merge( array( 'all' => __( 'All countries', 'sunshine-photo-cart' ) ), SunshineCountries::$countries )
);

$options[] = array( 'name' => __( 'Display Fields', 'sunshine-photo-cart' ), 'type' => 'header', 'desc' => '' );
$options[] = array(
	'name' => __( 'Billing Fields', 'sunshine-photo-cart' ),
	'id'   => 'billing_fields',
	'type' => 'checkbox',
	'tip' => __( 'Check fields you want visible','sunshine-photo-cart' ),
	'multiple' => true,
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
$options[] = array(
	'name' => __( 'Shipping Fields', 'sunshine-photo-cart' ),
	'id'   => 'shipping_fields',
	'type' => 'checkbox',
	'tip' => __( 'Check fields you want visible','sunshine-photo-cart' ),
	'multiple' => true,
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
$options[] = array(
	'name' => __( 'Other Fields', 'sunshine-photo-cart' ),
	'id'   => 'general_fields',
	'type' => 'checkbox',
	//'tip' => __( 'Check fields you want to be displayed','sunshine-photo-cart' ),
	'multiple' => true,
	'options' => array(
		'phone' => __( 'Phone', 'sunshine-photo-cart' ),
		'notes' => __( 'Notes', 'sunshine-photo-cart' )
	)
);

$options[] = array( 'name' => __( 'Required Fields', 'sunshine-photo-cart' ), 'type' => 'header', 'desc' => '' );
$options[] = array(
	'name' => __( 'Billing Fields', 'sunshine-photo-cart' ),
	'id'   => 'billing_fields_required',
	'type' => 'checkbox',
	'tip' => __( 'Check fields you want to be required','sunshine-photo-cart' ),
	'multiple' => true,
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
$options[] = array(
	'name' => __( 'Shipping Fields', 'sunshine-photo-cart' ),
	'id'   => 'shipping_fields_required',
	'type' => 'checkbox',
	'tip' => __( 'Check fields you want to be required','sunshine-photo-cart' ),
	'multiple' => true,
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
$options[] = array(
	'name' => __( 'Other Fields', 'sunshine-photo-cart' ),
	'id'   => 'general_fields_required',
	'type' => 'checkbox',
	//'tip' => __( 'Check fields you want to be displayed','sunshine-photo-cart' ),
	'multiple' => true,
	'options' => array(
		'phone' => __( 'Phone', 'sunshine-photo-cart' ),
		'notes' => __( 'Notes', 'sunshine-photo-cart' )
	)
);
$options[] = array( 'name' => __( 'Terms','sunshine-photo-cart' ), 'type' => 'header', 'desc' => '' );
$options[] = array(
	'name' => __( 'Require Approval of Terms', 'sunshine-photo-cart' ),
	'id'   => 'require_terms',
	'type' => 'checkbox',
	'tip' => __( 'Force users to check that they have read and agree to the terms (below)','sunshine-photo-cart' ),
	'options' => array( 1 )
);
$options[] = array(
	'name' => __( 'Terms','sunshine-photo-cart' ),
	'id'   => 'terms',
	'type' => 'wysiwyg',
	'settings' => array( 'textarea_rows' => 4 )
);

$options = apply_filters( 'sunshine_options_checkout', $options );

/* Payment Methods */
$options[] = array( 'name' => __( 'Payments', 'sunshine-photo-cart' ), 'type' => 'heading' );

$options = apply_filters( 'sunshine_options_payment_methods', $options );


/* Shipping */
$options[] = array( 'name' => __( 'Shipping', 'sunshine-photo-cart' ), 'type' => 'heading' );
$options = apply_filters( 'sunshine_options_shipping_methods', $options );

/* Templates */
$options[] = array( 'name' => __( 'Design', 'sunshine-photo-cart' ), 'type' => 'heading' );
$options[] = array( 'name' => __( 'Design Elements','sunshine-photo-cart' ), 'type' => 'header', 'desc' => '' );
$options[] = array(
	'name' => __( 'Theme', 'sunshine-photo-cart' ),
	'id'   => 'theme',
	'type' => 'select',
	'options' => array(
		'theme' => __( 'My WordPress Theme', 'sunshine-photo-cart' ),
		'default' => __( 'Default Sunshine Theme', 'sunshine-photo-cart' ),
		'2013' => __( 'Modern Sunshine Theme', 'sunshine-photo-cart' )
	)
);

$options[] = array(
	'name' => __( 'Logo', 'sunshine-photo-cart' ),
	'id'   => 'template_logo',
	'type' => 'image',
	'select2' => true
);

$options[] = array( 'name' => __( 'Miscellaneous Elements','sunshine-photo-cart' ), 'type' => 'header', 'desc' => '' );
$options[] = array(
	'name' => __( 'Disable breadcrumbs', 'sunshine-photo-cart' ),
	'id'   => 'disable_breadcrumbs',
	'type' => 'checkbox',
	'desc' => __( 'Do not show breadcrumbs throughout Sunshine pages. Helpful if you want users to stick to just a single gallery.','sunshine-photo-cart' ),
	'options' => array( 1 )
);
$options[] = array(
	'name' => __( 'Hide link to main galleries page', 'sunshine-photo-cart' ),
	'id'   => 'hide_galleries_link',
	'type' => 'checkbox',
	'desc' => __( 'Hide the link to your main galleries page in any Sunshine menus. Helpful if you want users to stick to just a single gallery.','sunshine-photo-cart' ),
	'options' => array( 1 )
);

$options = apply_filters( 'sunshine_options_templates', $options );

/* Email Settings */
$options[] = array( 'name' => __( 'Email', 'sunshine-photo-cart' ), 'type' => 'heading' );
$options[] = array( 'name' => __( 'Notifications', 'sunshine-photo-cart' ), 'type' => 'header', 'desc' => '' );

$options[] = array(
	'name' => __( 'Order Notifications', 'sunshine-photo-cart' ),
	'desc' => __( 'Email address(es) to receive order notifications. Separate multiple emails with a comma.','sunshine-photo-cart' ),
	'id'   => 'order_notifications',
	'type' => 'text',
);
$options[] = array(
	'name' => __( 'Favorite Notifications', 'sunshine-photo-cart' ),
	'desc' => __( 'Email address(es) to receive submitted favorites notifications. Separate multiple emails with a comma.','sunshine-photo-cart' ),
	'id'   => 'favorite_notifications',
	'type' => 'text',
);


$options[] = array( 'name' => __( 'Email From', 'sunshine-photo-cart' ), 'type' => 'header', 'desc' => '' );

$options[] = array(
	'name' => __( 'From Name', 'sunshine-photo-cart' ),
	'desc' => __( 'When emails are sent to customers, what name should they come from','sunshine-photo-cart' ),
	'id'   => 'from_name',
	'type' => 'text',
);
$options[] = array(
	'name' => __( 'From Email', 'sunshine-photo-cart' ),
	'desc' => __( 'When emails are sent to customers, what email address should they come from','sunshine-photo-cart' ),
	'id'   => 'from_email',
	'type' => 'text',
);

/* Email Subjects */
$options[] = array( 'name' => __( 'Email Subjects', 'sunshine-photo-cart' ), 'type' => 'header', 'desc' => __( 'Allowed template variables are:','sunshine-photo-cart' ).' [sitename], [order_id], [first_name], [last_name]' );
$options[] = array(
	'name' => __( 'Register','sunshine-photo-cart' ),
	'id'   => 'email_subject_register',
	'type' => 'text',
);
$options[] = array(
	'name' => __( 'Order Receipt','sunshine-photo-cart' ),
	'id'   => 'email_subject_order_receipt',
	'type' => 'text',
);
$options[] = array(
	'name' => __( 'Order Status','sunshine-photo-cart' ),
	'id'   => 'email_subject_order_status',
	'type' => 'text',
);
$options[] = array(
	'name' => __( 'Order Comment','sunshine-photo-cart' ),
	'id'   => 'email_subject_order_comment',
	'type' => 'text',
);

/* Extra Email Content */
$options[] = array( 'name' => __( 'Email Text', 'sunshine-photo-cart' ), 'type' => 'header', 'desc' => '' );
$options[] = array(
	'name' => __( 'Email Signature','sunshine-photo-cart' ),
	'desc' => __( 'Appears at the end of every email message','sunshine-photo-cart' ),
	'id'   => 'email_signature',
	'type' => 'wysiwyg',
	'settings' => array( 'textarea_rows' => 4 )
);
$options[] = array(
	'name' => __( 'Receipt', 'sunshine-photo-cart' ),
	'desc' => __( 'Message at the top of email receipts','sunshine-photo-cart' ),
	'id'   => 'email_receipt',
	'type' => 'wysiwyg',
	'settings' => array( 'textarea_rows' => 4 )
);
$options[] = array(
	'name' => __( 'Registration', 'sunshine-photo-cart' ),
	'desc' => __( 'Message at top of new user registration email','sunshine-photo-cart' ),
	'id'   => 'email_register',
	'type' => 'wysiwyg',
	'settings' => array( 'textarea_rows' => 4 )
);
$options[] = array(
	'name' => __( 'Order Status', 'sunshine-photo-cart' ),
	'desc' => __( 'Message added to bottom order status change email','sunshine-photo-cart' ),
	'id'   => 'email_order_status',
	'type' => 'wysiwyg',
	'settings' => array( 'textarea_rows' => 4 )
);
$options = apply_filters( 'sunshine_options_email', $options );

$options = apply_filters( 'sunshine_options_extra', $options );

	$options[] = array( 'name' => __( 'Licenses','sunshine-photo-cart' ), 'type' => 'heading', 'desc' => __( 'Manage licenses for your Sunshine add-ons here','sunshine-photo-cart' ) );
	//$options[] = array( 'name' => __( 'Sunshine Photo Cart License', 'sunshine-photo-cart' ), 'type' => 'header', 'desc' => '' );
	//$options = apply_filters( 'sunshine_options_licenses_primary', $options );
	$addon_license_options = apply_filters( 'sunshine_options_licenses', array() );
	if ( !empty( $addon_license_options ) ) {
		//$options[] = array( 'name' => __( 'Add-on Sunshine Licenses', 'sunshine-photo-cart' ), 'type' => 'header', 'desc' => '' );
		$options = array_merge( $options, $addon_license_options );
	}

/*

$options[] = array( 'name' => __( 'Licenses','sunshine-photo-cart' ), 'type' => 'heading', 'desc' => __( 'Manage licenses for your Sunshine add-ons here','sunshine-photo-cart' ) );
$options[] = array(
	'name' => __( 'Sunshine License Key', 'sunshine-photo-cart' ),
	'desc' => __( 'Enter your Basic, Plus, or Pro license key here','sunshine-photo-cart' ),
	'id'   => 'sunshine_license',
	'type' => 'license',
	'option_class' => 'sunshine-license-input'
);
$options = apply_filters( 'sunshine_options_licenses', $options );


$options[] = array( 'name' => 'Language', 'type' => 'heading' );
$options[] = array( 'name' => 'Order Status', 'type' => 'header', 'desc' => '' );
*/
/*
$options[] = array( 'name' => 'Samples', 'type' => 'heading' );
$options[] = array(
	'name' => __( 'Age', 'geczy' ),
	'desc' => __( 'What\'s your age, buddy?.', 'geczy' ),
	'tip'  => __( 'It\'s simple, just enter your age!', 'geczy'),
	'id'   => 'number_sample',
	'css' => 'width:70px;',
	'type' => 'number',
	'restrict' => array(
		'min' => 0,
		'max' => 100
	)
);

$options[] = array(
	'name' => __( 'Describe yourself', 'geczy' ),
	'desc' => __( 'Which word describes you best?.', 'geczy' ),
	'tip'  => __( 'If you can\'t choose, I\'ve defaulted an option for you.', 'geczy'),
	'std'  => 'gorgeous',
	'id'   => 'radio_sample',
	'type' => 'radio',
	'options' => array(
		'gorgeous' => 'Gorgeous',
		'pretty' => 'Pretty'
	)
);

$options[] = array(
	'name' => __( 'Biography', 'geczy' ),
	'desc' => __( 'So tell me about yourself.', 'geczy' ),
	'id'   => 'textarea_sample',
	'type' => 'textarea',
);

$options[] = array(
	'name' => __( 'Wordpress page', 'geczy' ),
	'desc' => __( 'Pick your favorite page!', 'geczy' ),
	'tip'  => __( 'Or maybe you don\'t have a favorite?', 'geczy'),
	'id'   => 'single_select_page_sample',
	'type' => 'single_select_page',
);

$options[] = array(
	'name' => __( 'Would you rather have', 'geczy' ),
	'desc' => __( 'Which would you rather have?.', 'geczy' ),
	'id'   => 'select_sample',
	'type' => 'select',
	'options' => array(
		'tenbucks' => 'Ten dollars',
		'redhead' => 'A readheaded girlfriend',
		'tofly' => 'Flying powers',
		'lolwhat' => 'Three hearts',
	)
);

$options[] = array(
	'name' => __( 'Terms', 'geczy' ),
	'desc' => __( 'Agree to my terms...Or else.', 'geczy' ),
	'id'   => 'checkbox_sample',
	'type' => 'checkbox',
);


$options[] = array(
	'name' => __( 'Awesome', 'geczy' ),
	'desc' => __( 'Is this awesome or what?', 'geczy' ),
	'id'   => 'checkbox_sample2',
	'type' => 'checkbox',
);
*/

//endif;
