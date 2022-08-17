<?php
add_action( 'wp_ajax_nopriv_sunshine_modal_display_signup', 'sunshine_modal_display_signup' );
add_action( 'wp_ajax_sunshine_modal_display_signup', 'sunshine_modal_display_signup' );
function sunshine_modal_display_signup() {

    $result = array( 'html' => sunshine_get_template_html( 'account/signup' ) );
    wp_send_json_success( $result );

}

add_action( 'wp_ajax_nopriv_sunshine_modal_display_login', 'sunshine_modal_display_login' );
add_action( 'wp_ajax_sunshine_modal_display_login', 'sunshine_modal_display_login' );
function sunshine_modal_display_login() {

    $result = array( 'html' => sunshine_get_template_html( 'account/login' ) );
    wp_send_json_success( $result );

}


add_action( 'wp_ajax_nopriv_sunshine_modal_display_require_login', 'sunshine_modal_display_require_login' );
add_action( 'wp_ajax_sunshine_modal_display_require_login', 'sunshine_modal_display_require_login' );
function sunshine_modal_display_require_login() {

	if ( is_user_logged_in() ) {
		return false;
	}

	$vars = array_map( 'sanitize_text_field', $_POST );

	// If we passed the image id to add to favorites, set session to process after login
	if ( isset( $vars['after'] ) && isset( $vars['imageId'] ) ) {
		SPC()->session->set( 'add_to_favorites', intval( $vars['imageId'] ) );
	}

	// Build a message that can appear at the top of this modal template
	$message = apply_filters( 'sunshine_account_require_login_message', '', $vars );

    $result = array( 'html' => sunshine_get_template_html( 'account/login-signup', array( 'message' => $message ) ) );
    wp_send_json_success( $result );

}

add_action( 'wp_ajax_nopriv_sunshine_modal_login', 'sunshine_modal_login' );
add_action( 'wp_ajax_sunshine_modal_login', 'sunshine_modal_login' );
function sunshine_modal_login() {

	if ( !wp_verify_nonce( $_POST['security'], 'sunshine_login_nonce' ) ) {
        SPC()->log( 'Login failed nonce' );
        wp_send_json_error( __( 'Invalid login attempt', 'sunshine-photo-cart' ) );
    }

	extract( $_POST );

	$creds = array(
		'user_login' => $email,
		'user_password' => $password,
		'remember' => true
	);
	$login = wp_signon( $creds, is_ssl() );
	if ( is_wp_error( $login ) ) {
		wp_send_json_error( __( 'Invalid email or password, please try again', 'sunshine-photo-cart' ) );
	}

	SPC()->customer = new SPC_Customer( $login->ID );

	SPC()->notices->add( __( 'You have been logged in', 'sunshine-photo-cart' ) );

	// Let after login actions have a change to do something
	do_action( 'sunshine_after_login', $_POST );

    wp_send_json_success();

}

add_action( 'wp_ajax_nopriv_sunshine_modal_signup', 'sunshine_modal_signup' );
add_action( 'wp_ajax_sunshine_modal_signup', 'sunshine_modal_signup' );
function sunshine_modal_signup() {

	sunshine_log( $_POST );
	if ( !wp_verify_nonce( $_POST['security'], 'sunshine_signup_nonce' ) ) {
        SPC()->log( 'Signup failed nonce' );
        wp_send_json_error( __( 'Invalid signup attempt', 'sunshine-photo-cart' ) );
    }

	extract( $_POST );

	// Check if valid email
	if ( !is_email( $email ) ) {
		wp_send_json_error( __( 'Invalid email address', 'sunshine-photo-cart' ) );
	}

	// Get user by email address
	$user = get_user_by( 'email', sanitize_text_field( $email ) );
	if ( $user ) {
		wp_send_json_error( __( 'User account already exists with that email address', 'sunshine-photo-cart' ) );
	}

	if ( empty( $password ) ) {
		wp_send_json_error( __( 'No password provided', 'sunshine-photo-cart' ) );
	}

	$user_id = wp_insert_user(array(
		'user_login' => $email,
		'user_email' => $email,
		'user_pass' => $password,
	));
	if ( is_wp_error( $login ) ) {
		wp_send_json_error( $login->get_error_message() );
	}

	$creds = array(
		'user_login' => $email,
		'user_password' => $password,
		'remember' => true
	);
	$login = wp_signon( $creds, is_ssl() );
	if ( is_wp_error( $login ) ) {
		wp_send_json_error( $login->get_error_message() );
	}

	SPC()->customer = new SPC_Customer( $login->ID );

	SPC()->notices->add( sprintf( __( 'A new user account for %s has been created and you have been automatically logged in', 'sunshine-photo-cart' ), $email ) );

	do_action( 'sunshine_after_signup', $_POST );

    wp_send_json_success();

}


add_action( 'wp_ajax_nopriv_sunshine_modal_reset_password', 'sunshine_modal_reset_password' );
add_action( 'wp_ajax_sunshine_modal_reset_password', 'sunshine_modal_reset_password' );
function sunshine_modal_reset_password() {

	if ( !wp_verify_nonce( $_POST['security'], 'sunshine_reset_password_nonce' ) ) {
        SPC()->log( 'Password reset failed nonce' );
        wp_send_json_error( __( 'Invalid password reset attempt', 'sunshine-photo-cart' ) );
    }

	extract( $_POST );

	// Check if valid email
	if ( !is_email( $email ) ) {
		wp_send_json_error( __( 'Invalid email address', 'sunshine-photo-cart' ) );
	}

	// Get user by email address
	$user = get_user_by( 'email', sanitize_text_field( $email ) );
	if ( empty( $user ) ) {
		wp_send_json_error( __( 'No user with that email address', 'sunshine-photo-cart' ) );
	}

	$message = new SPC_Email( 'reset-password', $user->user_email, SPC()->get_option( 'email_subject_reset_password' ) );
	$search_replace = array(
		'reset_password_link' => get_bloginfo( 'url' ) . '?sunshine_reset_password=' // TODO: Finish this URL
	);
	$message->set_search_replace( $search_replace );

	// Send email
	$result = $message->send();
	if ( !$result ) {
		wp_send_json_error( __( 'Failed to send password reset email', 'sunshine-photo-cart' ) );
	}

	SPC()->notices->add( sprintf( __( 'An email has been sent to %s with information on resetting your password', 'sunshine-photo-cart' ), $email ) );

    wp_send_json_success();

}

function sunshine_get_account_menu_items() {
	$items = array(
        'dashboard' => array(
            'endpoint' => 'dashboard',
            'label' => __( 'Dashboard', 'sunshine-photo-cart')
        ),
        'orders' => array(
            'endpoint' => SPC()->get_option( 'account_orders_endpoint', 'my-orders' ),
            'label' => __( 'Orders', 'sunshine-photo-cart')
        ),
        'addresses' => array(
            'endpoint' => SPC()->get_option( 'account_addresses_endpoint', 'my-addresses' ),
            'label' => __( 'Addresses', 'sunshine-photo-cart')
        ),
        'profile' => array(
            'endpoint' => SPC()->get_option( 'account_edit_endpoint', 'my-profile' ),
            'label' => __( 'Account Details', 'sunshine-photo-cart')
        ),
        'logout' => array(
            'endpoint' => 'logout',
            'label' => __( 'Logout', 'sunshine-photo-cart')
        ),
	);
	return apply_filters( 'sunshine_account_menu_items', $items );
}

function sunshine_get_account_endpoint_url( $endpoint ) {
    $account_url = sunshine_get_page_url( SPC()->frontend->get_page( 'account' ) );
    if ( $endpoint == 'logout' ) {
        return wp_logout_url( sunshine_get_page_url( SPC()->frontend->get_page( 'home' ) ) );
    } elseif ( $endpoint == 'dashboard' ) {
        return $account_url;
    }
    $url = trailingslashit( $account_url ) . $endpoint;
    $url = apply_filters( 'sunshine_account_endpoint_url', $url, $endpoint );
    return $url;
}
