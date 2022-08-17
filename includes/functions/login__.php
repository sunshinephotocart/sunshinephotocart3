<?php
/**
 * Customize new user registration only when registering from Sunshine related registration page
 *
 * @since 1.0
 * @param int $user_id User ID
 * @param string $plaintext_pass Password in plaintext
 * @return void
 */
if ( isset( $_GET['sunshine-photo-cart'] ) ) {
	if ( !function_exists( 'wp_new_user_notification' ) && $_GET['sunshine-photo-cart'] == 1 ) {
		function wp_new_user_notification( $user_id, $plaintext_pass = '' ) {
			global $sunshine;
			$user = new WP_User( $user_id );
			$user_login = stripslashes( $user->user_login );
			$user_email = stripslashes( $user->user_email );

			$message  = __( 'New user registration on your Sunshine Photo Cart','sunshine-photo-cart' ) . "\r\n\r\n";
			$message .= sprintf( __( 'E-mail: %s' ), $user_email ) . "\r\n";
			$message .= sprintf( __( 'Name: %s' ), $user->first_name.' '.$user->last_name ) . "\r\n";

			@wp_mail( get_option( 'admin_email' ), sprintf( __( '[%s] New User Registration' ), get_option( 'blogname' ) ), $message );

			if ( empty( $_POST['password'] ) )
				return;

			$search = array( '[username]', '[password]', '[email]' );
			$replace = array( $user_login, $_POST['password'], $user_email );
			$mail_result = SunshineEmail::send_email( 'register', $user_email, SPC()->get_option( 'email_subject_register' ), SPC()->get_option( 'email_subject_register' ), $search, $replace );

		}
	}
}

/**
 * After user registers, log then in automatically and change all session cart values to meta data
 *
 * @since 1.0
 * @param int $user_id User ID
 * @return void
 */
add_action( 'user_register', 'sunshine_after_register' );
function sunshine_after_register( $user_id ) {
	global $sunshine;

	if ( !isset( $_GET['sunshine-photo-cart'] ) ) return;

	$userdata = array();
	$userdata['ID'] = $user_id;
	if ( isset( $_POST['password'] ) && $_POST['password'] != '' ) {
		add_filter( 'send_password_change_email', '__return_false' );
		$userdata['user_pass'] = sanitize_text_field( $_POST['password'] );
		wp_update_user( $userdata );
	}

	if ( !is_admin() ) {
		$user = new WP_User( $user_id );
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id );
		SPC()->notices->add( __( 'Thank you for registering! You have been automatically logged into your new account','sunshine-photo-cart' ) );
		if ( !empty( $_COOKIE['sunshine_cart_hash'] ) ) {
			$cart = maybe_unserialize( get_option( 'sunshine_cart_hash_' . $_COOKIE['sunshine_cart_hash'] ) );
			if ( is_array( $cart ) ) {
				//$sunshine->cart->empty_cart();
				foreach ( $cart as $item ) {
					SunshineUser::add_user_meta_by_id( $user_id, 'cart', $item, false );
				}
				$discounts = SunshineSession::instance()->discounts;
				if ( is_array( $discounts ) ) {
					foreach ( $discounts as $discount ) {
						SunshineUser::add_user_meta_by_id( $user_id, 'discount', $discount, false );
					}
				}
			}
		}
	}
}

/**
 * After logging in, adjust cart cookie as needed
 * If items in cart, keep those items in cart regardless of what was saved to user account
 * If no items in cart but items in user's saved cart, add those back into the current cart
 *
 * @since 1.0
 * @param string $user_login Username
 * @param object $user WP_User
 * @return void
 */
add_action( 'wp_login', 'sunshine_after_login', 10, 2 );
function sunshine_after_login( $user_login, $user ) {
	global $sunshine;
	if ( !is_admin() ) {
		if ( isset( $_COOKIE['sunshine_cart_hash'] ) ) {
			$cart = maybe_unserialize( get_option( 'sunshine_cart_hash_' . $_COOKIE['sunshine_cart_hash'] ) );
		}
		if ( !empty( $cart ) && is_array( $cart ) ) {
			$sunshine->cart->empty_products( $user->ID );
			foreach ( $cart as $item ) {
				SunshineUser::add_user_meta_by_id( $user->ID, 'cart', $item, false );
			}

			$shipping_method = SunshineSession::instance()->shipping_method;
			SunshineUser::add_user_meta_by_id( $user->ID, 'shipping_method', $shipping_method );

			$discounts = SunshineSession::instance()->discounts;
			if ( is_array( $discounts ) ) {
				foreach ( $discounts as $discount ) {
					SunshineUser::add_user_meta_by_id( $user->ID, 'discount', $discount, false );
				}
			}
			//$sunshine->add_message( sprintf( __( 'You are now logged in as <strong>%s</strong>','sunshine-photo-cart' ), $user->user_login ) );
		}
	}
}

/**
 * After logging out, clear the cart cookies
 *
 * @since 1.0
 * @return void
 */

add_action( 'wp_logout', 'sunshine_after_logout' );
function sunshine_after_logout() {
	//SPC()->cart->set_cart_cookies( false, 'empty_products' );
}

/**
 * Add password field on registration form
 *
 * @since 1.0
 * @return void
 */
add_action( 'register_form', 'sunshine_show_extra_register_fields' );
function sunshine_show_extra_register_fields(){
?>
	<p>
	<label for="password"><?php _e( 'Password','sunshine-photo-cart' ); ?><br/>
	<input id="password" class="input" type="password" size="25" value="" name="password" />
	</label>
	<label for="checkbox-unmask" class="label-unmask"><input id="checkbox-unmask" class="checkbox-unmask" type="checkbox" /><?php _e( 'Show my password', 'sunshine-photo-cart' ); ?></label>
	</p>
	<?php if ( !empty( $_GET['redirect_to'] ) ) { ?>
		<input type="hidden" value="<?php echo $_GET['redirect_to']; ?>" name="redirect_to" />
	<?php } ?>
	<script>
		var pw = jQuery( "#password" ),
	    cb = jQuery( "#checkbox-unmask" ),
	    mask = true;
		cb.on("click", function(){

	  	if( mask === true ){
	    	mask = false;
	    	pw.attr( "type", "text" );
	  	} else {
	    	mask = true;
	    	pw.attr( "type", "password" );
	  	}
	});
	</script>
<?php
	do_action( 'sunshine_register_fields' );
}

/**
 * Error checking for new password field on registration form
 *
 * @since 1.0
 * @param string $login Username
 * @param string $email Email address
 * @param array $errors Errors
 * @return void
 */
add_action( 'register_post', 'sunshine_check_extra_register_fields', 10, 3 );
function sunshine_check_extra_register_fields( $login, $email, $errors ) {
	if ( isset( $_POST['password'] ) && strlen( $_POST['password'] ) < 6 ) {
		$errors->add( 'password_too_short', '<strong>ERROR</strong>: '.__( 'Passwords must be at least six characters long','sunshine-photo-cart' ) );
	}
}

/**
 * Add hidden field on login form to identify when it is for Sunshine
 *
 * @since 1.0
 * @return void
 */
add_action( 'login_form', 'sunshine_login_form' );
function sunshine_login_form() {
	if ( ( isset( $_GET['sunshine-photo-cart'] ) && $_GET['sunshine-photo-cart'] == 1 ) || ( isset( $_POST['sunshine-photo-cart'] ) && $_POST['sunshine-photo-cart'] == 1 ) )
		echo '<input type="hidden" name="sunshine" value="1" />';
}

/**
 * Add custom logo image to login/registration form when Sunshine related
 *
 * @since 1.0
 * @return void
 */
add_action( 'login_head', 'sunshine_custom_login_logo' );
add_action( 'login_enqueue_scripts', array( 'SunshineFrontend','frontend_cssjs' ), 1 );
function sunshine_custom_login_logo() {
	global $sunshine;

	if ( ( isset( $_GET['sunshine-photo-cart'] ) && $_GET['sunshine-photo-cart'] == 1 ) || ( isset( $_POST['sunshine-photo-cart'] ) && $_POST['sunshine-photo-cart'] == 1 ) ) {

		if ( !empty( SPC()->get_option( 'template_logo' ) ) ) {
			$logo = wp_get_attachment_image_src( SPC()->get_option( 'template_logo' ), 'full' );
			if ( $logo[1] > 320 ) {
				$logo[2] = $logo[2] * ( 320 / $logo[1] );
				$logo[1] = 320;
			}
			echo '<style type="text/css">
			h1 a { background: url('.$logo[0].') center top no-repeat !important; width: 100% !important; height: '.$logo[2].'px !important; background-size: contain !important;  }
			</style>';
		}

	}

}

add_filter( 'login_headerurl', 'sunshine_custom_login_url' );
function sunshine_custom_login_url( $url ) {
	if ( ( isset( $_GET['sunshine-photo-cart'] ) && $_GET['sunshine-photo-cart'] == 1 ) || ( isset( $_POST['sunshine-photo-cart'] ) && $_POST['sunshine-photo-cart'] == 1 ) ) {
		return sunshine_url( 'home' );
	}
	return $url;
}


/**
 * Hide the username text when registering, we want to show username or email text
 *
 * @since 1.0
 * @return void
 */
add_action( 'login_head', 'sunshine_register_hide_username' );
function sunshine_register_hide_username() {
	if ( !is_sunshine() ) {
		return;
	}
?>
    <style>
        #registerform > p:first-child{
            display:none;
        }
    </style>
<?php
}

/**
 * Disable username errors on registration, we will handle it ourselves
 *
 * @since 1.0
 * @return WP_Error
 */
add_filter( 'registration_errors', 'sunshine_register_errors' );
function sunshine_register_errors( $wp_error ) {
	if( isset( $wp_error->errors['empty_username'] ) ){
		unset( $wp_error->errors['empty_username'] );
	}

	if( isset( $wp_error->errors['username_exists'] ) ){
		unset( $wp_error->errors['username_exists'] );
	}
	return $wp_error;
}

/**
 * When user registers, make their login their email address
 *
 * @since 1.0
 * @return void
 */
add_action( 'login_form_register', 'sunshine_login_form_register' );
function sunshine_login_form_register() {
	if( isset( $_POST['user_login'] ) && isset( $_POST['user_email'] ) && !empty( $_POST['user_email'] ) ){
		$_POST['user_login'] = $_POST['user_email'];
	}
}

/**
 * Show text saying they can login with email or username
 *
 * @since 1.0
 * @return void
 */
add_action( 'login_form', 'sunshine_username_or_email_login' );
function sunshine_username_or_email_login() {
	if ( 'wp-login.php' != basename( $_SERVER['SCRIPT_NAME'] ) && !isset( $_GET['sunshine-photo-cart'] ) )
		return;

	?><script type="text/javascript">
	// Form Label
	if ( document.getElementById('loginform') )
		document.getElementById('loginform').childNodes[1].childNodes[1].childNodes[0].nodeValue = '<?php echo esc_js( __( 'Username or Email', 'email-login' ) ); ?>';

	// Error Messages
	if ( document.getElementById('login_error') )
		document.getElementById('login_error').innerHTML = document.getElementById('login_error').innerHTML.replace( '<?php echo esc_js( __( 'username' ) ); ?>', '<?php echo esc_js( __( 'Username or Email' , 'email-login' ) ); ?>' );
	</script><?php
}

/**
 * Allow users to login with their email address
 *
 * @since 1.0
 * @return WP_User or WP_Error
 */
add_filter( 'authenticate', 'sunshine_allow_email_login', 40, 3 );
function sunshine_allow_email_login( $user, $username, $password ) {
	if ( !empty( $username ) ) {
		$user = get_user_by( 'email', $username );
		if ( isset( $user, $user->user_login, $user->user_status ) && 0 == (int) $user->user_status )
			$username = $user->user_login;
	}
	return wp_authenticate_username_password( null, $username, $password );
}
