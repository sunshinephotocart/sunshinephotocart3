<?php
/**
 * License handler for Sunshine Photo Cart
 *
 * This class should simplify the process of adding license information
 * to new Sunshine add-ons.
 *
 * @version 1.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'SPC_License' ) ) :

/**
 * sunshine_License Class
 */
class SPC_License {
	private $file;
	private $license;
	private $item_name;
	private $item_shortname;
	private $version;
	private $author;
	private $api_url = SUNSHINE_PHOTO_CART_STORE_URL;
	private $type = 'addon';

	/**
	 * Class constructor
	 *
	 * @param string  $_file
	 * @param string  $_item_name
	 * @param string  $_version
	 * @param string  $_author
	 * @param string  $_api_url
	 */
	function __construct( $_file, $_item_name, $_version, $_author, $_api_url = null ) {
		global $sunshine;

		$this->file           = $_file;
		$this->item_name      = $_item_name;
		$this->item_shortname = str_replace( array( '.php', '-' ) , array( '', '_' ), basename( $_file ) );
		$this->version        = $_version;
		$this->author         = $_author;
		$this->api_url        = is_null( $_api_url ) ? $this->api_url : $_api_url;

		if ( !empty( $sunshine->options[$this->item_shortname . '_license_key'] ) ) {
			$this->license = $sunshine->options[$this->item_shortname . '_license_key'];
		}

		if ( $this->item_shortname == 'sunshine-photo-cart' ) {
			$this->type = 'primary';
		}

		// Setup hooks
		$this->includes();
		$this->hooks();
	}

	/**
	 * Include the updater class
	 *
	 * @access  private
	 * @return  void
	 */
	private function includes() {
		if ( ! class_exists( 'SunshineUpdate' ) ) require_once 'class-update.php';
	}

	/**
	 * Setup hooks
	 *
	 * @access  private
	 * @return  void
	 */
	private function hooks() {

		// Register settings
		add_filter( 'sunshine_options_licenses', array( $this, 'settings' ) );

		// Activate license key on settings save
		add_action( 'admin_init', array( $this, 'activate_license' ) );

		// Deactivate license key
		add_action( 'admin_init', array( $this, 'deactivate_license' ), 0 );

		// Updater
		add_action( 'admin_init', array( $this, 'auto_updater' ), 1 );

		//add_action( 'admin_notices', array( $this, 'notices' ) );

		add_action( 'admin_init', array( $this, 'check_license' ), 99 );

	}

	/**
	 * Auto updater
	 *
	 * @access  private
	 * @return  void
	 */
	public function auto_updater() {

		if ( 'valid' !== get_option( $this->item_shortname . '_license_active' ) || $this->item_shortname == 'sunshine-photo-cart' ) {
			return;
		}

		// Setup the updater
		$sunshine_updater = new SunshineUpdate(
			$this->api_url,
			$this->file,
			array(
				'version'   => $this->version,
				'license'   => $this->license,
				'item_name' => $this->item_name,
				'author'    => $this->author,
			)
		);

	}

	/**
	 * Add license field to settings
	 *
	 * @access  public
	 * @param array   $settings
	 * @return  array
	 */
	public function settings( $options ) {

		$desc = '';
		$id = $this->item_shortname . '_license_key';
		if ( $this->item_shortname == 'sunshine' ) {
			$desc = __( 'Enter your Basic, Plus, or Pro license key', 'sunshine-photo-cart' );
			$id = 'license_key';
		}
		$status = get_option( $this->item_shortname . '_license_active' );
		$expiration = get_transient( $this->item_shortname . '_license_expiration' );
		$expiration_readable = $after = '';
		$error = get_transient( $this->item_shortname . '_license_error' );
		if ( $expiration == 'lifetime' ) {
			$expiration_readable = __( 'You have a very special lifetime license, congrats!', 'sunshine-photo-cart' );
		} elseif ( $expiration && $expiration != 'lifetime' ) {
			$expiration_readable = date( get_option( 'date_format' ), strtotime( $expiration ) );
			$expiration_readable = sprintf( __( 'Your license expires on %s', 'sunshine-photo-cart' ), $expiration_readable );
		}
		if ( ( $status == 'invalid' || $this->license == 'invalid' ) && $this->license != '' ) {
			if ( !empty( $error ) ) {
				$desc = '<span style="color: #FF0000; font-weight: bold;">' . __( 'Invalid license key','sunshine-photo-cart' ) . '</span>';
				if ( isset( $error->site_count ) && $error->site_count >= $error->license_limit ) {
					$desc = '<span style="color: #FF0000; font-weight: bold;">' .sprintf( __( 'You have reached the limit on number of sites this license can be activated on. <a href="%s" target="_blank">Manage sites here</a>', 'sunshine-photo-cart' ), 'https://www.sunshinephotocart.com/account/licenses'  ) . '</span>';
				}
			} else {
				$desc = '<span style="color: #FF0000; font-weight: bold;">' . __( 'Invalid license key','sunshine-photo-cart' ) . '</span>';
			}
		} elseif ( $status == 'expired' ) {
			$desc = '<span style="color: #FF0000; font-weight: bold;">' . sprintf( __( 'Your license expired on %s. <a href="https://www.sunshinephotocart.com/account" target="_blank">click here to renew</a>', 'sunshine-photo-cart' ), $expiration_readable ) . '</span>';
		} elseif ( $status == 'valid' ) {
			$after = '<a href="' . wp_nonce_url( 'admin.php?page=sunshine&section=licenses&deactivate=' . $this->item_shortname . '_license_key', 'deactivate_sunshine_license', 'deactivate_'. $this->item_shortname . '_license_key' ) . '" class="button">' . __( 'Deactivate','sunshine-photo-cart' ) . '</a>';
			if ( $expiration_readable ) {
				$desc = $expiration_readable;
			}
		} elseif ( $this->license != '' && $status == '' ) {
			$desc = __( 'License not yet activated', 'sunshine-photo-cart' );
		}

		$options[] = array(
			'name' => $this->item_name,
			'id' => $id,
			'type' => 'text',
			'description' => $desc,
			'after' => $after,
			'disabled' => true
		);

		return $options;
	}


	/**
	 * Activate the license key
	 *
	 * @access  public
	 * @return  void
	 */
	public function activate_license() {
		global $sunshine;

		if ( ! isset( $_POST ) ) {
			return;
		}

		if ( ! isset( $_POST[ $this->item_shortname . '_license_key'] ) ) {
			return;
		}

		foreach ( $_POST as $key => $value ) {
			if ( false !== strpos( $key, 'license_key_deactivate' ) ) {
				// Don't activate a key when deactivating a different key
				return;
			}
		}

		if ( 'valid' === get_option( $this->item_shortname . '_license_active' ) ) {
			return;
		}

		$license = sanitize_text_field( $_POST[ $this->item_shortname . '_license_key'] );

		if ( empty( $license ) ) {
			return;
		}

		// Data to send to the API
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license,
			'item_name'  => urlencode( $this->item_name ),
			'url'        => home_url()
		);

		// Call the API
		$response = wp_remote_post(
			$this->api_url,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params
			)
		);

		// Make sure there are no errors
		if ( is_wp_error( $response ) ) {
			SPC()->notices->add_admin( 'sunshine_license_no_connection_' . current_time, sprintf( __( 'Your licenses could not be activated because your server failed to connect to SunshinePhotoCart.com server: %s', 'sunshine-photo-cart' ), $response->get_error_message() ), 'notice-error', true, '', true );
			return;
		}

		// Tell WordPress to look for updates
		set_site_transient( 'update_plugins', null );

		// Decode license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		SPC()->notices->delete_admin( $this->item_shortname . '_no_license' );

		update_option( $this->item_shortname . '_license_type', $license_data->license_type );
		update_option( $this->item_shortname . '_license_active', $license_data->license );
		set_transient( $this->item_shortname . '_license_expiration', $license_data->expires, WEEK_IN_SECONDS );

		do_action( $this->item_shortname . '_license_activate', $license_data->success );

		if( ! (bool) $license_data->success ) {
			set_transient( $this->item_shortname . '_license_error', $license_data );
		} else {
			delete_transient( $this->item_shortname . '_license_error' );
		}
	}


	/**
	 * Deactivate the license key
	 *
	 * @access  public
	 * @return  void
	 */
	public function deactivate_license() {

		// Run on deactivate button press
		if ( isset( $_GET['deactivate'] ) && $_GET['deactivate'] == $this->item_shortname . '_license_key' && check_admin_referer( 'deactivate_sunshine_license', 'deactivate_' . $this->item_shortname . '_license_key' ) ) {

			// Data to send to the API
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'    => $this->license,
				'item_name'  => urlencode( $this->item_name ),
				'url'        => SUNSHINE_PHOTO_CART_STORE_URL
			);

			// Call the API
			$response = wp_remote_post(
				$this->api_url,
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params
				)
			);

			// Make sure there are no errors
			if ( is_wp_error( $response ) ) {
				return;
			}

			// Decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			delete_option( $this->item_shortname . '_license_type' );
			delete_option( $this->item_shortname . '_license_active' );

			if( ! (bool) $license_data->success ) {
				set_transient( $this->item_shortname . '_license_error', $license_data );
			} else {
				delete_transient( $this->item_shortname . '_license_error' );
			}

			wp_redirect( admin_url( 'admin.php?page=sunshine&section=licenses' ) );
			exit;
		}
	}

	/**
	 * Check the license key
	 *
	 * @access  public
	 * @return  void
	 */
	public function check_license() {
		global $sunshine;

		$expiration = get_transient( $this->item_shortname . '_license_expiration' );

		if ( $expiration === FALSE ) {

			// Data to send to the API
			$api_params = array(
				'edd_action' => 'check_license',
				'license'    => $this->license,
				'item_name'  => urlencode( $this->item_name ),
				'url'        => home_url()
			);

			// Call the API
			$response = wp_remote_post(
				$this->api_url,
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params
				)
			);

			// Make sure there are no errors
			if ( is_wp_error( $response ) ) {
				return;
			}

			// Decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			if ( !empty( $license_data->license ) ) {
				update_option( $this->item_shortname . '_license_active', $license_data->license );

				if ( !empty( $license_data->expires ) ) {
					set_transient( $this->item_shortname . '_license_expiration', $license_data->expires, WEEK_IN_SECONDS );
				}

				if ( $license_data->license == 'expired' ) {
					SPC()->notices->add_admin( $this->item_shortname . '_expired', sprintf( __( 'Your license for %s has expired - you are no longer eligible for further updates but you may continue to use it. <a href="%s" target="_blank">Go here to renew</a>', 'sunshine-photo-cart' ), $this->item_name, 'https://www.sunshinephotocart.com/account' ), 'error' );
				}
			} else {
				SPC()->notices->add_admin( $this->item_shortname . '_no_license', sprintf( __( 'You have not provided a license key for %s. <a href="%s" target="_blank">Enter your license key here</a>. If you do not know your license key, you can <a href="%s">get it here</a>', 'sunshine-photo-cart' ), $this->item_name, admin_url( 'admin.php?page=sunshine&section=licenses' ), 'https://www.sunshinephotocart.com/account/licenses' ), 'error', true );
			}
		}


	}



	/**
	 * Admin notices for errors
	 *
	 * @access  public
	 * @return  void
	 */
	public function notices() {

		if( ! isset( $_GET['page'] ) || 'sunshine-photo-cart' !== $_GET['page'] ) {
			return;
		}

		if( ! isset( $_GET['tab'] ) || 'licenses' !== $_GET['tab'] ) {
			return;
		}

		$license_error = get_transient( $this->item_shortname . '_license_error' );

		if( false === $license_error ) {
			return;
		}

		if( ! empty( $license_error->error ) ) {

			switch( $license_error->error ) {

			case 'item_name_mismatch' :

				$message = __( 'This license does not belong to the product you have entered it for.', 'sunshine-photo-cart' );
				break;

			case 'license_not_activable' :

			$message = sprintf( __( 'You have reached the limit on number of sites this license can be activated on. <a href="%s" target="_blank">Manage sites here</a>', 'sunshine-photo-cart' ), 'https://www.sunshinephotocart.com/account/licenses'  );
				break;

			case 'expired' :

				$message = __( 'This license key is expired. Please renew it.', 'sunshine-photo-cart' );
				break;

			default :

				$message = sprintf( __( 'There was a problem activating your license key for %s, please try again or contact support. Error code: %s', 'sunshine-photo-cart' ), urldecode( $license_error->item_name ), $license_error->error );
				break;

			}

		}

		if( ! empty( $message ) ) {

			echo '<div class="error">';
			echo wp_kses_post( '<p>' . $message . '</p>' );
			echo '</div>';

		}

		//delete_transient( $this->item_shortname . '_license_error' );

	}
}

endif; // end class_exists check
