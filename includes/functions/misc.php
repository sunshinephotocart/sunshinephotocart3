<?php
/******************
COMMON FUNCTIONS
******************/
/**
 * Log errors to debug file
 *
 * @since 1.0
 * @param mixed $message String or array to be written to log file
 * @return void
 */
function sunshine_log( $message, $pre = '' ) {
	if ( WP_DEBUG === true ) {
		/*
		$backtrace = debug_backtrace();
		error_log( print_r( $backtrace, 1 ) );
		return;
		//error_log( '*** ' . basename( $backtrace[0]['file'] ) . ', Line ' . $backtrace[0]['line'] );
		$i = '';
		foreach ( $backtrace as $b ) {
			$i .= '*';
			error_log( $i . ': ' . basename( $b['file'] ) . ', Line ' . $b['line'] );
		}
		*/
		if ( $pre ) {
			error_log( $pre );
		}
		if( is_array( $message ) || is_object( $message ) ){
			error_log( print_r( $message, true ) );
		} else {
			error_log( $message );
		}
	}
}

/**
 * Display variables nicely formatted
 *
 * @since 1.0
 * @param mixed $var String or array
 * @return void
 */
function sunshine_dump_var( $var, $echo = true ) {
	if ( $echo ) {
		echo '<pre>';
		print_r( $var );
		echo '</pre>';
	} else {
		$content = '<pre>';
		$content .= print_r( $var, true );
		$content .= '</pre>';
		return $content;
	}
}

/******************************
	SUNSHINE PAGES
******************************/
function is_sunshine_page( $page = '' ) {
	global $post;

	if ( is_admin() || empty( $post ) ) {
		return false;
	}
	if ( empty( $page ) && in_array( $page, SPC()->frontend->pages ) ) { // Is the passed value a page ID value in the pages array
		return true;
	} elseif ( !empty( $page ) && !empty( $post ) && array_key_exists( $page, SPC()->frontend->pages ) && SPC()->frontend->pages[ $page ] == $post->ID ) {
		return true;
	}
	return false;

}

function is_sunshine( $from = '' ) {
	global $post;
	$return = '';

	if ( empty( $post ) ) {
		return false;
	}

	if ( defined( 'IS_SUNSHINE' ) ) {
		return IS_SUNSHINE;
	}

	if ( ( $GLOBALS['pagenow'] === 'wp-login.php' && isset( $_GET['sunshine-photo-cart'] ) && $_GET['sunshine-photo-cart'] == 1 ) || ( isset( $_POST['sunshine-photo-cart'] ) && $_POST['sunshine-photo-cart'] == 1 ) ) {
		$return = 'sunshine-photo-cart';
	}

	if ( SPC()->frontend->get_page( $post->ID ) ) {
		$return = 'SUNSHINE-PAGE';
	}
	if ( get_post_type( $post ) == 'sunshine-gallery' ) {
		$return = 'SUNSHINE-GALLERY';
	}
	if ( is_post_type_archive( 'sunshine-gallery' ) ) {
		$return = 'SUNSHINE-GALLERY-ARCHIVE';
	}
	if ( !empty( $post ) && $post->post_parent > 0 && get_post_type( $post->post_parent ) == 'sunshine-gallery' ) {
		$return = 'SUNSHINE-IMAGE';
	}
	if ( get_post_type( $post ) == 'sunshine-order' ) {
		$return = 'SUNSHINE-ORDER';
	}

	if ( has_shortcode( $post->post_content, 'sunshine_gallery' ) ) {
		$return = 'SUNSHINE-SHORTCODE';
	}

	if ( $return ) {
		if ( !defined( 'IS_SUNSHINE' ) ) {
			define( 'IS_SUNSHINE', $return );
		}
		return $return;
	} else {
		return false;
	}
}

function sunshine_get_page_url( $page ) {
	return get_permalink( SPC()->frontend->get_page( $page ) );
}

/**
 * Change letter to number for file size
 *
 * @since 1.0
 * @param string $v string value
 * @return string
 */
function sunshine_let_to_num( $v ) {
	$l = substr( $v, -1 );
	$ret = substr( $v, 0, -1 );
	switch( strtoupper( $l ) ){
	case 'P':
		$ret *= 1024;
	case 'T':
		$ret *= 1024;
	case 'G':
		$ret *= 1024;
	case 'M':
		$ret *= 1024;
	case 'K':
		$ret *= 1024;
		break;
	}
	return $ret;
}

/**
 * Change letter to number for file size
 *
 * @since 2.4
 * @param string $needle string value
 * @param string $haystack array
 * @return boolean
 */
function sunshine_in_array_r( $needle, $haystack, $strict = false ) {
    foreach ( $haystack as $item ) {
        if ( ( $strict ? $item === $needle : $item == $needle ) || ( is_array( $item ) && sunshine_in_array_r( $needle, $item, $strict ) ) ) {
            return true;
        }
    }
    return false;
}

/**
 * Sort an array by a specific key/column
 *
 * @since 1.8
 * @return array
 */
function sunshine_array_sort_by_column( &$arr, $col, $dir = SORT_ASC ) {
	$sort_col = array();
	if ( empty( $arr ) ) return;
	foreach ( $arr as $key=> $row ) {
		$sort_col[$key] = $row[$col];
	}
	array_multisort( $sort_col, $dir, $arr );
}

function sunshine_get_currencies() {
	return apply_filters( 'sunshine_currencies',
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
}
/**
 * Get all images from a folder
 *
 * @since 1.0
 * @return array of file names
 */
function sunshine_get_images_in_folder( $folder ) {
	$images = glob( $folder.'/*.[jJ][pP][gG]' );
	$images = apply_filters( 'sunshine_images_in_folder', $images, $folder );
	$i = 0;
	if ( $images ) {
		// ProPhoto hack because they regenerate the Featured Image every time a new PP Theme is activated and save it in our folder
		foreach ( $images as &$image ) {
			if ( strpos( $image, '(pp_' ) !== false )
				unset( $images[$i] );
			$i++;
		}
	}
	return $images;
}

/**
 * Count how many images are in a folder
 *
 * @since 1.0
 * @return number
 */
function sunshine_image_folder_count( $folder ) {
	return count( sunshine_get_images_in_folder( $folder ) );
}

/**********************
GALLERY PASSWORD BOX
***********************/

/**
 * Check if valid password, redirect to gallery if exists
 * From gallery password widget
 *
 * @since 1.0
 * @return void
 */
 /*
add_action( 'init', 'sunshine_gallery_password_redirect' );
function sunshine_gallery_password_redirect() {
	global $wpdb, $post;
	if ( !is_admin() && isset( $_POST['sunshine_gallery_password'] ) ) {
		$password = sanitize_text_field( $_POST['sunshine_gallery_password'] );
		$querystr = "
		    SELECT $wpdb->posts.*
		    FROM $wpdb->posts
			LEFT JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id
		    WHERE
				$wpdb->posts.post_status = 'publish'
		    	AND $wpdb->posts.post_type = 'sunshine-gallery'
		    	AND $wpdb->posts.post_password = '$password'
				AND $wpdb->postmeta.meta_key = 'status'
				AND $wpdb->postmeta.meta_value = 'password'
	 	";
		$pageposts = $wpdb->get_results( $querystr, OBJECT );
		if ( $pageposts ) {
			require_once ABSPATH . 'wp-includes/class-phpass.php';
			foreach ( $pageposts as $post ) {
				$hasher = new PasswordHash( 8, true );
				setcookie( 'wp-postpass_' . COOKIEHASH, $hasher->HashPassword( wp_unslash( $password ) ), time() + 10 * DAY_IN_SECONDS, COOKIEPATH );
				wp_safe_redirect( get_permalink( $post->ID ) );
				exit();
			}
		}

		wp_die( __( 'Sorry, no galleries matched that password.','sunshine-photo-cart' ), 'No galleries matched password', 'back_link=true' );
		exit();
	}
}
*/

/**********************
	GALLERY EMAIL BOX
***********************/
/**
 * Redirect user back to gallery if successfully providing their email address
 */
 /*
add_action( 'template_redirect', 'sunshine_gallery_email_redirect' );
function sunshine_gallery_email_redirect() {
	if ( isset( $_POST['sunshine_gallery_email'] ) ) {
		$email = sanitize_email( $_POST['sunshine_gallery_email'] );
		$gallery_id = intval( $_POST['sunshine_gallery_id'] );
		if ( is_email( $email ) ) {
			$gallery = new SPC_Gallery( $gallery_id );
			$gallery_emails = SPC()->session->get( 'gallery_emails' );
			$gallery_emails[] = $gallery_id;
			SPC()->session->set( 'gallery_emails', $gallery_emails );
			$existing_emails = $gallery->get_emails();
			if ( !is_array( $existing_emails ) || !in_array( $email, $existing_emails ) ) {
				$gallery->add_email( $email );
				do_action( 'sunshine_gallery_email', $email, $gallery_id );
			}
		} else {
			SPC()->add_error( __( 'Not a valid email address', 'sunshine-photo-cart' ) );
		}
		wp_safe_redirect( $gallery->get_permalink() );
		exit();
	}
}
*/

/**********************
IMAGE PAGE
***********************/
/**
 * Force the Client Galleries page to have comments closed when viewing an image
 * We then use comment_form in Sunshine's image.php template because most themes do not have comments setup for the page template
 *
 * @since 1.0
 * @return void
 */
//add_filter( 'comments_open', 'sunshine_comments_open', 10, 2 );
function sunshine_comments_open( $open, $post_id ) {
	global $sunshine;
	if ( $sunshine->comment_status == 'IN_SUNSHINE' ) {
		return true;
	}
	return $open;
}


/**
 * Allow file extensions
 *
 * @since 1.8
 * @return array
 */
function sunshine_allowed_file_extensions() {
	$extensions = array( 'jpg' );
	return apply_filters( 'sunshine_allowed_file_extensions', $extensions );
}



/**********************
ADMIN TOOLBAR
***********************/
add_action( 'wp_before_admin_bar_render', 'sunshine_customize_admin_toolbar' );
function sunshine_customize_admin_toolbar() {
    global $wp_admin_bar, $sunshine;
	if ( is_sunshine_page( 'home' ) && !empty( SPC()->frontend->current_gallery ) ) {
	    $wp_admin_bar->add_menu( array(
	        'id' => 'edit',
	        'parent' => false,
	        'title' => __( 'Edit Gallery', 'sunshine-photo-cart' ),
	        'href' => admin_url( 'post.php?post=' . SPC()->frontend->current_gallery->get_id() . '&action=edit' ),
			'class' => 'ab-item'
	    ) );

	}
}

/**********************
CUSTOM IMAGE UPLOAD LOCATION
***********************/
function sunshine_custom_upload_dir( $param ) {
    $id = intval( $_REQUEST['gallery_id'] );
    if( !empty( $id ) && 'sunshine-gallery' == get_post_type( $id ) ) {
        $mydir         = '/sunshine/' . $id;
        $param['path'] = $param['basedir'] . $mydir;
        $param['url']  = $param['baseurl'] . $mydir;
    }
    return $param;
}

/**********************
PROPHOTO 4/5 retina workaround
***********************/
add_action( 'the_content', 'sunshine_prevent_prophoto_retina_sunshine', 5000 );
function sunshine_prevent_prophoto_retina_sunshine( $content ) {
	$theme = wp_get_theme();
	if ( 'ProPhoto' != $theme->name || !is_sunshine() ) {
		return $content;
	}
	$pattern = "/(<a[^>]+href=(?:\"|')([^'\"]+)(?:\"|')[^>]*>)?(?:[ \t\n]+)?(<img[^>]*>)(?:[ \t\n]+)?(<\/a>)?/i";
	preg_match( $pattern, $content, $matches );
	if ( empty( $matches ) ) {
		return $content;
	}
  	// prevent p5 retina-zation by fooling it to think it already has a `data-src-2x` attr
	return str_replace( ' src=', ' data-prevent-data-src-2x="no" src=', $content );
}


function sunshine_core_order_statuses() {
	return array( 'new', 'cancelled', 'pending', 'processing', 'refunded', 'pickup', 'shipped' );
}

/*
WORKAROUND for WordPress core bug that does not check proper capabilities
for attachments of private custom post types
WP will *always* check for 'read_private_posts' when it should check for 'read_private_sunshine_galleries'
This will give a user the 'read_private_posts' capability when posting to a private sunshine gallery
*/
add_filter( 'user_has_cap', 'sunshine_user_has_cap', 9996, 4 );
function sunshine_user_has_cap( $allcaps, $caps, $args, $user ) {
	if ( isset( $_POST ) && !empty( $_POST['comment'] ) ) {
		$image_id = intval( $_POST['comment_post_ID'] );
		// Get gallery ID
		$gallery_id = wp_get_post_parent_id( $image_id );
		// If sunshine gallery and set to private, add 'read_post' to capabilities
		if ( get_post_type( $gallery_id ) == 'sunshine-gallery' && get_post_status( $gallery_id ) == 'private' ) {
			$allcaps['read_private_posts'] = 1;
			//$allcaps['unfiltered_html'] = 1;
		}
	}
	return $allcaps;
}


function get_gallery_descendants( $gallery_id ){
    $children = array();
    $galleries = get_posts( array( 'numberposts' => -1, 'post_status' => 'publish', 'post_type' => 'sunshine-gallery', 'post_parent' => $gallery_id, 'suppress_filters' => false ));
    // now grab the grand children
    foreach ( $galleries as $child ){
        $gchildren = get_gallery_descendants( $child->ID );
        if ( !empty( $gchildren ) ) {
            $children = array_merge( $children, $gchildren );
        }
    }
    $children = array_merge( $children, $galleries );
    return $children;
}

function get_gallery_descendant_ids( $gallery_id ){
	$galleries = get_gallery_descendants( $gallery_id );
	$ids = array();
	foreach ( $galleries as $gallery ) {
		$ids[] = $gallery->ID;
	}
	return $ids;
}

/* Prevent order comments/log from appearing in various places */
add_action( 'pre_get_comments', 'sunshine_hide_comments', 10 );
function sunshine_hide_comments( $query ) {

	$sunshine_comment_types = array(
		'sunshine_order_log',
		'sunshine_order_comment'
	);
	if ( isset( $query->query_vars['type'] ) && in_array( $query->query_vars['type'], $sunshine_comment_types ) ) {
		return;
	}
	$types = isset( $query->query_vars['type__not_in'] ) ? $query->query_vars['type__not_in'] : array();
	if ( !is_array( $types ) ) {
		$types = array( $types );
	}
	$query->query_vars['type__not_in'] = array_merge( $types, $sunshine_comment_types );

}
?>
