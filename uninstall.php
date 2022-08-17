<?php
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$uninstall = get_option( 'sunshine_uninstall_delete_data' );

if ( $uninstall ) {

    global $wpdb, $current_user;

    //$galleries = $wpdb->query( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'sunshine-gallery';" );
    //sunshine_log( $galleries, 'GALLERIES DURING DELETE' );

    // Remove settings
    $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'sunshine_%'" );
    // Remove user meta data
    $wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'sunshine_%'" );

    // Remove pages
    $pages = array(
        get_option( 'sunshine_page' ),
        get_option( 'sunshine_page_cart' ),
        get_option( 'sunshine_page_checkout' ),
        get_option( 'sunshine_page_account' ),
        get_option( 'sunshine_page_favorites' )
    );
    foreach ( $pages as $page_id ) {
        if ( !empty( $page_id ) ) {
            wp_delete_post( $page_id, true );
        }
    }

    // Get all galleries for use in deleting attachments
    $galleries = $wpdb->query( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'sunshine-gallery';" );

    // Remove user meta
    $wpdb->query( "DELETE FROM $wpdb->usermeta WHERE meta_key LIKE 'sunshine_%';" );

    // Remove post type data
    $wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type IN ( 'sunshine-product', 'sunshine-gallery', 'sunshine-order' );" );
    // Delete all meta data that is not assigned anymore
    $wpdb->query( "DELETE meta FROM {$wpdb->postmeta} meta LEFT JOIN {$wpdb->posts} posts ON posts.ID = meta.post_id WHERE posts.ID IS NULL;" );

    // Remove taxonomy data
    foreach ( array( 'sunshine-product-category', 'sunshine-product-price-level', 'sunshine-order-status' ) as $taxonomy ) {
        $wpdb->delete(
            $wpdb->term_taxonomy,
            array(
                'taxonomy' => $taxonomy,
            )
        );
    }

    // Remove session table
    $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}sunshine_sessions" );

    do_action( 'sunshine_uninstall' );

}

/* Not ready yet
// Remove attachments
if ( $options['uninstall_delete_attachments'] ) {

    if ( !empty( $galleries ) ) {

        $gallery_ids = array();
        foreach ( $galleries as $gallery_id ) {
            $gallery_ids[] = $gallery_id;
        }

        // Build single query to delete all attachment posts
        $wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type='attachment' AND post_parent IN ( " . join( ',', $gallery_ids ) . " );" );

        // Clear all unattached meta data query
        $wpdb->query( "DELETE meta FROM {$wpdb->postmeta} meta LEFT JOIN {$wpdb->posts} posts ON posts.ID = meta.post_id WHERE posts.ID IS NULL;" );

        // Delete files from server
        $upload_dir = wp_upload_dir();
        $folder = $upload_dir['basedir'] . '/sunshine/*';
        array_map( 'unlink', array_filter( (array) glob( $folder ) ) );

    }

}
*/

wp_cache_flush();
?>
