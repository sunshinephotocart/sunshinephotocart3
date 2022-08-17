<?php
class SPC_Tool_Orphans extends SPC_Tool {

    function __construct() {
        parent::__construct(
            __( 'Orphaned Images', 'sunshine-photo-cart' ),
            __( 'Sometimes when deleting galleries the associated images are not fully deleted. This tool will remove those orphaned images to help reduce file storage.', 'sunshine-photo-cart' ),
            __( 'Delete orphaned images', 'sunshine-photo-cart' )
        );
    }

    function pre_process() {
        global $wpdb;
        $sql = "SELECT COUNT(*) as total FROM {$wpdb->posts} AS p
            INNER JOIN {$wpdb->postmeta} AS pm
                ON p.ID = pm.post_id AND pm.meta_key = 'sunshine_file_name'
            WHERE p.post_parent = 0 AND p.post_type = 'attachment'
            AND pm.meta_value != ''
            ORDER BY p.ID DESC";
        $count = $wpdb->get_row($sql)->total;
        if ( $count ) {
            echo '<p>';
            echo sprintf( __( 'Sunshine found %s orphaned images.', 'sunshine-photo-cart' ), $count );
            _e( '<strong style="color: red;">It is recommended to make a backup before running this tool. Images will be completed deleted from your server.</strong></p>', 'sunshine-photo-cart' );
            echo '</p>';
        } else {
            echo '<p><em>' . __( 'No orphans found!', 'sunshine-photo-cart' ) . '</em></p>';
            $this->button_label = '';
        }
    }

    function process() {
        global $wpdb;

    	$sql = "SELECT COUNT(*) as total FROM {$wpdb->posts} AS p
    		INNER JOIN {$wpdb->postmeta} AS pm
    			ON p.ID = pm.post_id AND pm.meta_key = 'sunshine_file_name'
    		WHERE p.post_parent = 0 AND p.post_type = 'attachment'
    		AND pm.meta_value != ''
    		ORDER BY p.ID DESC";
    	$count = $wpdb->get_row($sql)->total;

    ?>
    	<div id="progress-bar" style="background: #000; height: 30px; position: relative;">
    		<div id="percentage" style="height: 30px; background-color: green; width: 0%;"></div>
    		<div id="processed" style="position: absolute; top: 0; left: 0; width: 100%; color: #FFF; text-align: center; font-size: 18px; height: 30px; line-height: 30px;">
    			<span id="processed-count">0</span> / <span id="processed-total"><?php echo $count; ?></span>
    		</div>
    	</div>
    	<p align="center" id="abort"><a href="<?php echo admin_url( 'admin.php?page=sunshine_tools' ); ?>"><?php _e( 'Abort', 'sunshine' ); ?></a></p>
    	<ol id="results"></ol>
    	<script type="text/javascript">
    	jQuery( document ).ready(function($) {
    		var processed = 0;
    		var total = <?php echo esc_js( $count ); ?>;
    		var percent = 0;
    		function sunshine_clear_orphan( item_number ) {
    			var data = {
    				'action': 'sunshine_clear_orphan',
    				'item_number': item_number
    			};
    			$.postq( 'sunshineclearorphan', ajaxurl, data, function(response) {
    				var obj = $.parseJSON( response );
    				processed++;
    				if ( processed >= total ) {
    					$( '#abort' ).hide();
    					$( '#return' ).show();
    				}
    				$( '#processed-count' ).html( processed );
    				percent = Math.round( ( processed / total ) * 100);
    				$( '#percentage' ).css( 'width', percent+'%' );
    				if ( obj.error ) {
    					$( '#results' ).append( '<li style="color: red;">' + obj.file + ': ' + obj.error + '</li>' );
    				} else {
    					$( '#results' ).append( '<li>' + obj.file + ' removed</li>' );
    				}
    			}).fail( function( jqXHR ) {
    				if ( jqXHR.status == 500 || jqXHR.status == 0 ){
    					$( '#results' ).append( '<li><strong><?php esc_js( __( 'Cannot process image, likely out of memory', 'sunshine' ) ); ?></strong></li>' );
    				}
    			});
    		}
    		for (i = 1; i <= total; i++) {
    			sunshine_clear_orphan( i );
    		}
    	});
    	</script>

    <?php
    }

}

$spc_tool_orphans = new SPC_Tool_Orphans();
