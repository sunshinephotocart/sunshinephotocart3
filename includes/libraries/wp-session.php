<?php
/**
 * WordPress session managment.
 *
 * Standardizes WordPress session data and uses either database transients or in-memory caching
 * for storing user session information.
 *
 * @package WordPress
 * @subpackage Session
 * @since   3.7.0

 TODO: CHANGE ALL FUNCTION PREFIXES TO SUNSHINE, ditch every mention of WP_Session so we dont have to worry about other plugins conflicting
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



/**
 * Register the garbage collector as a twice daily event.
 */
function wp_session_register_garbage_collection() {
	if ( ! wp_next_scheduled( 'wp_session_garbage_collection' ) ) {
		wp_schedule_event( current_time( 'timestamp' ), 'twicedaily', 'wp_session_garbage_collection' );
	}
}
add_action( 'wp', 'wp_session_register_garbage_collection' );
