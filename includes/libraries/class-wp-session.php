<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

final class WP_Session extends Recursive_ArrayAccess implements Iterator, Countable {
	/**
	 * ID of the current session.
	 *
	 * @var string
	 */
	public $session_id;

	/**
	 * Unix timestamp when session expires.
	 *
	 * @var int
	 */
	protected $expires;

	/**
	 * Unix timestamp indicating when the expiration time needs to be reset.
	 *
	 * @var int
	 */
	protected $exp_variant;

	/**
	 * Singleton instance.
	 *
	 * @var bool|WP_Session
	 */
	private static $instance = false;

	/**
	 * Retrieve the current session instance.
	 *
	 * @param bool $session_id Session ID from which to populate data.
	 *
	 * @return bool|WP_Session
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Default constructor.
	 * Will rebuild the session collection from the given session ID if it exists. Otherwise, will
	 * create a new session with that ID.
	 *
	 * @param $session_id
	 * @uses apply_filters Calls `wp_session_expiration` to determine how long until sessions expire.
	 */
	protected function __construct() {
		global $wpdb;
		if ( isset( $_COOKIE[ SUNSHINE_SESSION_COOKIE ] ) ) {
			$cookie = stripslashes( $_COOKIE[ SUNSHINE_SESSION_COOKIE ] );
			$cookie_crumbs = explode( '||', $cookie );

			if( $this->is_valid_md5( $cookie_crumbs[0] ) ) {

				$this->session_id = $cookie_crumbs[0];

			} else {

				$this->regenerate_id( true );

			}

			$this->expires     = $cookie_crumbs[1];
			$this->exp_variant = $cookie_crumbs[2];

			// Update the session expiration if we're past the variant time
			if ( time() > $this->exp_variant ) {
				$this->set_expiration();
				$wpdb->update( "{$wpdb->prefix}sunshine_sessions", [ 'session_expiration' => $this->expires ], [ 'session_id' => $this->session_id ] );

				//delete_option( "_wp_session_expires_{$this->session_id}" );
				//add_option( "_wp_session_expires_{$this->session_id}", $this->expires, '', 'no' );
			}
		} else {
			$this->session_id = $this->generate_id();
			$this->set_expiration();
		}

		$this->read_data();

		$this->set_cookie();

	}

	protected function set_expiration() {
		$this->exp_variant = time() + (int) apply_filters( 'sunshine_session_expiration_variant', 24 * 60 );
		$this->expires = time() + (int) apply_filters( 'sunshine_session_expiration', 30 * 60 );
	}

	/**
	 * Set the session cookie
	 */
	protected function set_cookie() {
		@setcookie( SUNSHINE_SESSION_COOKIE, $this->session_id . '||' . $this->expires . '||' . $this->exp_variant , $this->expires, COOKIEPATH, COOKIE_DOMAIN );
	}

	/**
	 * Generate a cryptographically strong unique ID for the session token.
	 *
	 * @return string
	 */
	protected function generate_id() {
		require_once( ABSPATH . 'wp-includes/class-phpass.php');
		$hasher = new PasswordHash( 8, false );

		return md5( $hasher->get_random_bytes( 32 ) );
	}

	/**
	 * Checks if is valid md5 string
	 *
	 * @param string $md5
	 * @return int
	 */
	protected function is_valid_md5( $md5 = '' ){
		return preg_match( '/^[a-f0-9]{32}$/', $md5 );
	}

	/**
	 * Read data from a transient for the current session.
	 *
	 * Automatically resets the expiration time for the session transient to some time in the future.
	 *
	 * @return array
	 */
	protected function read_data() {
		global $wpdb;

		if ( null === $wpdb ) {
			return false;
		}

		$session = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}sunshine_sessions WHERE session_id = %s",
				$this->session_id
			),
			ARRAY_A
		);

		if ( null === $session ) {
			return false;
		}

		$this->container = maybe_unserialize( $session['session_value'] );
		return $this->container;

		/* Old wp_options way
		$this->container = get_option( "_wp_session_{$this->session_id}", array() );
		return $this->container;
		*/
	}

	/**
	 * Write the data from the current session to the data storage system.
	 */
	public function write_data() {
		global $wpdb;

		// Delete the existing and add new row
		//sunshine_log( 'Deleting old session data to replace with new' );
		//$wpdb->delete( "{$wpdb->prefix}sunshine_sessions", [ 'session_id' => $this->session_id ] );

		if ( empty( $this->container ) ) {
			return false;
		}

		$session = [
            'session_id'    => $this->session_id,
            'session_value'  => ( !empty( $this->container ) ) ? maybe_serialize( $this->container ) : '',
            'session_expiry' => $this->expires,
        ];
		$result = $wpdb->replace( "{$wpdb->prefix}sunshine_sessions", $session );

	}

	/**
	 * Output the current container contents as a JSON-encoded string.
	 *
	 * @return string
	 */
	public function json_out() {
		return json_encode( $this->container );
	}

	/**
	 * Decodes a JSON string and, if the object is an array, overwrites the session container with its contents.
	 *
	 * @param string $data
	 *
	 * @return bool
	 */
	public function json_in( $data ) {
		$array = json_decode( $data );

		if ( is_array( $array ) ) {
			$this->container = $array;
			return true;
		}

		return false;
	}

	/**
	 * Regenerate the current session's ID.
	 *
	 * @param bool $delete_old Flag whether or not to delete the old session data from the server.
	 */
	public function regenerate_id( $delete_old = false ) {
		global $wpdb;
		if ( $delete_old ) {
			$wpdb->delete( "{$wpdb->prefix}sunshine_sessions", [ 'session_id' => $this->session_id ] );
			//delete_option( "_wp_session_{$this->session_id}" );
		}

		$this->session_id = $this->generate_id();

		$this->set_cookie();
	}

	/**
	 * Check if a session has been initialized.
	 *
	 * @return bool
	 */
	public function session_started() {
		return !!self::$instance;
	}

	/**
	 * Return the read-only cache expiration value.
	 *
	 * @return int
	 */
	public function cache_expiration() {
		return $this->expires;
	}

	/**
	 * Flushes all session variables.
	 */
	public function reset() {
		$this->container = array();
	}

	/*****************************************************************/
	/*                     Iterator Implementation                   */
	/*****************************************************************/

	/**
	 * Current position of the array.
	 *
	 * @link http://php.net/manual/en/iterator.current.php
	 *
	 * @return mixed
	 */
	public function current() {
		return current( $this->container );
	}

	/**
	 * Key of the current element.
	 *
	 * @link http://php.net/manual/en/iterator.key.php
	 *
	 * @return mixed
	 */
	public function key() {
		return key( $this->container );
	}

	/**
	 * Move the internal point of the container array to the next item
	 *
	 * @link http://php.net/manual/en/iterator.next.php
	 *
	 * @return void
	 */
	public function next() {
		next( $this->container );
	}

	/**
	 * Rewind the internal point of the container array.
	 *
	 * @link http://php.net/manual/en/iterator.rewind.php
	 *
	 * @return void
	 */
	public function rewind() {
		reset( $this->container );
	}

	/**
	 * Is the current key valid?
	 *
	 * @link http://php.net/manual/en/iterator.rewind.php
	 *
	 * @return bool
	 */
	public function valid() {
		return $this->offsetExists( $this->key() );
	}

	/*****************************************************************/
	/*                    Countable Implementation                   */
	/*****************************************************************/

	/**
	 * Get the count of elements in the container array.
	 *
	 * @link http://php.net/manual/en/countable.count.php
	 *
	 * @return int
	 */
	public function count() {
		return count( $this->container );
	}
}
