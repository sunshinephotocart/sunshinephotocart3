<?php
class SPC_Tool_Regenerate extends SPC_Tool {

    function __construct() {
        parent::__construct(
            __( 'Regenerate Images', 'sunshine-photo-cart' ),
            __( 'If you have changed thumbnail size, digital download size or watermark settings, you need to regenerate images.', 'sunshine-photo-cart' ),
            __( 'Regenerate Images', 'sunshine-photo-cart' )
        );

        add_action( 'wp_ajax_sunshine_regenerate_image', array( $this, 'regenerate_image' ) );

    }

    function process() {
        global $wpdb;

    	$gallery_id = ( isset( $_GET['sunshine_gallery'] ) ) ? intval( $_GET['sunshine_gallery'] ) : '';
    	if ( isset( $_GET['sunshine_gallery'] ) ) {
            $title = sprintf( __( 'Regenerating images for "%s"', 'sunshine-photo-cart' ), get_the_title( $_GET['sunshine_gallery'] ) );
    		$image_sql = "SELECT COUNT(*) as total FROM {$wpdb->posts}
    					  WHERE post_type = 'attachment' AND post_parent = {$gallery_id};";
    	} else {
            $title = __( 'Regenerating images', 'sunshine-photo-cart' );
    		$image_sql = "SELECT COUNT(*) as total FROM {$wpdb->posts}
    					  WHERE post_type = 'attachment' AND post_parent IN (
    						SELECT ID FROM {$wpdb->posts}
    						WHERE post_type = 'sunshine-gallery'
    					  );";
    	}
    	$count = $wpdb->get_row( $image_sql )->total;

    ?>
    	<h3><?php echo $title; ?>...</h3>
    	<div id="sunshine-progress-bar" style="">
    		<div id="sunshine-percentage" style=""></div>
    		<div id="sunshine-processed" style="">
    			<span id="sunshine-processed-count">0</span> / <span id="processed-total"><?php echo $count; ?></span>
    		</div>
    	</div>
    	<p align="center" id="abort"><a href="<?php echo admin_url( 'admin.php?page=sunshine_tools' ); ?>"><?php _e( 'Abort', 'sunshine-photo-cart' ); ?></a></p>
    	<ul id="results"></ul>
    	<script type="text/javascript">
    	jQuery(document).ready(function($) {
    		var processed = 0;
    		var total = <?php echo esc_js( $count ); ?>;
    		var percent = 0;
    		function sunshine_regenerate_image( item_number ) {
    			var data = {
    				'action': 'sunshine_regenerate_image',
    				'gallery': '<?php echo esc_js( $gallery_id ); ?>',
    				'item_number': item_number
    			};
    			$.postq( 'sunshineimageregenerate', ajaxurl, data, function(response) {
    				processed++;
    				if ( processed >= total ) {
    					$('#abort').hide();
    					$('#return').show();
    				}
    				$( '#sunshine-processed-count' ).html( processed );
    				percent = Math.round( ( processed / total ) * 100 );
    				$( '#sunshine-percentage' ).css( 'width', percent + '%' );
    				if ( response.error ) {
    					$( '#results' ).append( '<li><a href="post.php?action=edit&post=' + response.image_id + '" style="color: #FF0000;">' + response.file + '</a>: ' + response.error + '</li>' );
    				} else {
    					$( '#results' ).append( '<li><a href="post.php?action=edit&post=' + response.image_id + '">' + response.file + '</a></li>' );
    				}
    			}).fail( function( jqXHR ) {
    				if ( jqXHR.status == 500 || jqXHR.status == 0 ){
    					$( '#results' ).append( '<li><strong><?php esc_js( __( 'Cannot process image, likely out of memory', 'sunshine-photo-cart' ) ); ?></strong></li>' );
    				}
    			});
    		}
    		for (i = 1; i <= total; i++) {
    			sunshine_regenerate_image( i );
    		}
    	});
    	</script>

    <?php

    }

    function regenerate_image() {
        global $wpdb;

        set_time_limit( 600 );

        $item_number = intval( $_POST['item_number'] );
        $limit = $item_number - 1;

        if ( !empty( $_POST['gallery'] ) ) {
            $gallery_id = intval( $_POST['gallery'] );
            $image_sql = "SELECT * FROM {$wpdb->posts}
                          WHERE post_type = 'attachment' AND post_parent = {$gallery_id}
                          ORDER BY ID DESC
                          LIMIT {$limit}, 1;";
        } else {
            $image_sql = "SELECT * FROM {$wpdb->posts}
                          WHERE post_type = 'attachment' AND post_parent IN (
                            SELECT ID FROM {$wpdb->posts}
                            WHERE post_type = 'sunshine-gallery'
                          )
                          ORDER BY ID DESC
                          LIMIT {$limit}, 1;";
        }

        $image = $wpdb->get_row( $image_sql );

        if ( function_exists( 'wp_get_original_image_path' ) ) {
            $fullsizepath = wp_get_original_image_path( $image->ID );
        } else {
            $fullsizepath = get_attached_file( $image->ID );
        }
        if ( is_wp_error( $fullsizepath ) ) {
            wp_send_json( array( 'status' => 'error', 'file' => $image->post_name, 'image_id' => $image->ID, 'error' => __( 'Could not find original file to regenerate from', 'sunshine-photo-cart' ) ) );
            return;
        }

        // Delete all but orig file
        $file_info = pathinfo( $fullsizepath );
        foreach ( glob( $file_info['dirname'] . DIRECTORY_SEPARATOR . $file_info['filename'] . '-*.' . $file_info['extension'] ) as $file_size ) {
            wp_delete_file( $file_size );
        }

        $old_metadata = wp_get_attachment_metadata( $image->ID );
        $new_metadata = wp_generate_attachment_metadata( $image->ID, $fullsizepath );

        $wp_upload_dir = dirname( $fullsizepath ) . DIRECTORY_SEPARATOR;
        $image_sizes[] = 'sunshine-thumbnail';
        $image_sizes = apply_filters( 'sunshine_image_sizes', $image_sizes );
        foreach ( $old_metadata['sizes'] as $old_size => $old_size_data ) {
            if ( in_array( $old_size, $image_sizes ) ) {
                continue;
            }
            //wp_delete_file( $wp_upload_dir . $old_size_data['file'] );
            unset( $new_metadata['sizes'][ $old_size ] );
        }

        wp_update_attachment_metadata( $image->ID, $new_metadata );

        wp_send_json( array( 'status' => 'success', 'file' => $image->post_name, 'image_id' => $image->ID ) );

    }

}

$spc_tool_regenerate = new SPC_Tool_Regenerate();
