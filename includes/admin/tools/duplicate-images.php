<?php
class SPC_Tool_Duplicate_Images extends SPC_Tool {

    function __construct() {
        parent::__construct(
            __( 'Duplicate Images', 'sunshine-photo-cart' ),
            __( 'Sunshine stores information about user carts in sessions which are saved to the database. If your database is too big, you can clear all the session data here.', 'sunshine-photo-cart' ),
            __( 'Remove duplicate images', 'sunshine-photo-cart' )
        );
    }

    function pre_process() {
        $upload_dir = wp_upload_dir();
		$funny_images = glob( $upload_dir['basedir'] . '/sunshine/*.jpg' );
        if ( $funny_images ) {
            echo '<p>' . sprintf( __( 'Sunshine found <strong>%s images</strong> in your "wp-content/sunshine" folder - there should be 0. This tool attemps to clean them all up.', 'sunshine-photo-cart' ), count( $funny_images ) ) . '</p>';
            echo '<p>' . __( '<strong>CAUTION: It is highly recommended you take a full site backup (database and files) before running this tool.</strong>', 'sunshine-photo-cart' ) . '</p>';
        } else {
            echo '<p><em>' . __( 'No duplicate images found!', 'sunshine-photo-cart' ) . '</em></p>';
            $this->button_label = '';
        }
    }

    function process() {
        // Get any standalone images in the wp-content/uploads/sunshine directory
    	$upload_dir = wp_upload_dir();
    	$funny_images = glob( $upload_dir['basedir'] . '/sunshine/*.jpg' );
    	$matching_names = array();
    	foreach ( $funny_images as $funny_image ) {
    		$basename = basename( $funny_image, '.jpg' );
    		$matching_names[] = $basename;
    		@unlink( $funny_image );
    	}

    	if ( !empty( $matching_names ) ) {
    		// This was the f'd up query, so recreating it
    		$args = array(
    			'post_type' => 'sunshine-gallery',
    			'nopaging' => true,
    			'meta_query' => array(
    				array(
    					'key' => 'sunshine_gallery_images_directory',
    					'value'   => '!=',
    					'compare' => 'NOT LIKE'
    				)
    			)
    		);
    		$galleries = get_posts( $args );
    		$affected = 0;
    		foreach ( $galleries as $gallery ) {
    			$images = get_children( array( 'post_parent' => $gallery->ID, 'post_type' => 'attachment', 'post_mime_type' => 'image' ) );
    			foreach ( $images as $image ) {
    				if ( in_array( $image->post_title, $matching_names ) ) {
    					wp_delete_post( $image->ID, true );
    					$affected++;
    				}
    			}
    		}

    		if ( $affected ) {
    			echo '<p>' . sprintf( __( '%s images were removed in this process', 'sunshine-photo-cart' ), $affected ) . '<p>';
    		} else {
                echo '<p>' . __( 'No images were removed', 'sunshine-photo-cart' ) . '</p>';
            }

    	}

    }

}

$spc_tool_duplicate_images = new SPC_Tool_Duplicate_Images();
