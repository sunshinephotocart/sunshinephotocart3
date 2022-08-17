<?php

class Sunshine_Admin {

    protected $notices;
    protected $tabs = array();

    public function __construct() {

        add_filter( 'admin_body_class', array( $this, 'admin_body_class' ) );
        add_action( 'in_admin_header', array( $this, 'in_admin_header' ) );
        add_action( 'admin_footer', array( $this, 'admin_footer' ) );
        add_action( 'sunshine_header_links', array( $this, 'header_links' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        add_filter( 'display_post_states', array( $this, 'post_states' ), 10, 2 );

        add_action( 'admin_init', array( $this, 'update_check' ) );
        add_action( 'admin_init', array( $this, 'warnings' ) );

        add_filter( 'jpeg_quality', function( $arg ) { return 100; }, 999 );
        add_filter( 'wp_image_editors', array( $this, 'force_imagick' ) );
        add_filter( 'intermediate_image_sizes', array( $this, 'image_sizes' ), 99999, 1 );
		add_filter( 'big_image_size_threshold', array( $this, 'big_image_size_threshold' ), 999, 4 );

        add_action( 'save_post', array( $this, 'flush_rewrite_page_save' ) );

        // Filtering out images from galleries in media library if needed
        add_filter( 'ajax_query_attachments_args', array( $this, 'clean_media_library' ) );
        add_filter( 'pre_get_posts', array( $this, 'media_library_list' ) );

        // Show the links on the Plugins page
        add_filter( 'plugin_action_links_sunshine-photo-cart-v3/sunshine-photo-cart.php', array( $this, 'plugin_action_links' ) );

        // Add link to main Sunshine page in admin bar top left
        add_action( 'admin_bar_menu', array( $this, 'admin_bar_view_client_galleries' ), 768 );

        // Don't let them delete the core Order Statuses
        add_action( 'admin_head', array( $this, 'order_status_admin_customizations' ) );

        // Post updated status messages
        add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );

        // Show notice if logging is enabled so it doesn't stay on forever
        add_action( 'admin_notices', array( $this, 'logging_notice' ), 5 );

        // Sorting
        add_action( 'admin_footer', array( $this, 'sortable' ), 9999 );
        add_action( 'wp_ajax_sunshine_product_category_sort', array( $this, 'product_category_sort' ) );
        add_action( 'wp_ajax_sunshine_product_sort', array( $this, 'product_sort' ) );
        add_filter( 'pre_get_posts', array( $this, 'pre_get_posts_sort' ) );
        add_filter( 'create_term', array( $this, 'create_product_category' ), 10, 3 );
        add_filter( 'edit_term', array( $this, 'create_product_category' ), 10, 3 );
        add_filter( 'terms_clauses', array( $this, 'product_category_term_clauses' ), 10, 3 );

    }

    public function is_sunshine() {

        $screen = get_current_screen();

        $is_sunshine = false;

        if ( in_array( $screen->post_type, SPC()->get_post_types() ) ) {
            $is_sunshine = true;
        }
        if ( strpos( $screen->id, 'sunshine' ) !== false ) {
            $is_sunshine = true;
        }
        if ( strpos( $screen->id, 'sunshine_addons' ) !== false ) {
            $is_sunshine = false;
        }

        return $is_sunshine;

    }

    public function is_page( $page ) {
        $screen = get_current_screen();

        if ( $screen->id == 'sunshine-gallery_page_sunshine_' . $page ) {
            return true;
        }

        return false;
    }

    public function admin_body_class( $classes ) {
        if ( $this->is_sunshine() ) {
            $classes .= ' sunshine';
        }
        return $classes;
    }

    public function in_admin_header() {
        if ( !$this->is_sunshine() ) {
            return;
        }
        // Exclusions
        $screen = get_current_screen();
        if ( ( $screen->post_type == 'sunshine-gallery' || $screen->post_type == 'sunshine-product' ) && $screen->base == 'post' ) {
            return;
        }
    ?>

        <?php if ( !isset( $_GET['page'] ) || $_GET['page'] != 'sunshine_install' ) { ?>

            <?php if ( !SPC()->get_option( 'address1' ) ) { ?>
                <div class="sunshine-header-notice">
                    Start configuring your store including address, pages, URLs, and more...
                    <a href="<?php echo admin_url( 'edit.php?post_type=sunshine-gallery&page=sunshine' ); ?>">See settings</a>
                </div>
            <?php } elseif ( empty( sunshine_get_products() ) ) { ?>
                <div class="sunshine-header-notice">
                    Create products and set prices to start selling
                    <a href="<?php echo admin_url( 'edit.php?post_type=sunshine-product' ); ?>">Add products</a>
                </div>
            <?php } elseif ( empty( sunshine_get_active_payment_methods() ) ) { ?>
                <div class="sunshine-header-notice">
                    Configure payment methods to start receiving money
                    <a href="<?php echo admin_url( 'admin.php?page=sunshine&section=payment_methods' ); ?>">Select payment methods</a>
                </div>
            <?php } elseif ( empty( sunshine_get_active_shipping_methods() ) ) { ?>
                <div class="sunshine-header-notice">
                    Configure shipping methods to get orders to customers
                    <a href="<?php echo admin_url( 'admin.php?page=sunshine&section=shipping_methods' ); ?>">Setup shipping methods</a>
                </div>
            <?php } elseif ( !SPC()->is_pro() ) { ?>
                <div class="sunshine-header-notice">
                    <?php echo sprintf( __( 'Unlock more professional level features for Sunshine Photo Cart by upgrading, <a href="%s" target="_blank">learn more</a>', 'sunshine-photo-cart' ), 'https://www.sunshinephotocart.com/pricing/?utm_source=plugin&utm_medium=link&utm_campaign=pluginheader' ); ?>
                </div>
            <?php } ?>

        <?php } ?>

        <div class="sunshine-header">
            <a href="https://www.sunshinephotocart.com/?utm_source=plugin&utm_medium=link&utm_campaign=pluginheader" target="_blank" class="sunshine-logo"><img src="<?php echo SUNSHINE_PHOTO_CART_URL; ?>/assets/images/logo.svg" alt="Sunshine Photo Cart by WP Sunshine" /></a>

            <?php
            $header_links = apply_filters( 'sunshine_header_links', array() );
            if ( !empty( $header_links ) ) {
                echo '<div id="sunshine-header-links">';
                foreach ( $header_links as $key => $link ) {
                    echo '<a href="' . $link['url'] . '?utm_source=plugin&utm_medium=link&utm_campaign=pluginheader" target="_blank" class="sunshine-header-link--' . $key . '">' . $link['label'] . '</a>';
                }
                echo '</div>';
            }
            ?>

            <?php if ( count( $this->tabs ) > 1 ) { ?>
            <nav class="sunshine-options-menu">
                <ul>
                    <?php foreach ( $this->tabs as $key => $label ) { ?>
                        <li<?php if ( $this->tab == $key ) {?> class="sunshine-options-active"<?php } ?>><a href="<?php echo admin_url( 'options-general.php?page=sunshine&tab=' . $key ); ?>"><?php echo $label; ?></a></li>
                    <?php } ?>
                </ul>
            </nav>
            <?php } ?>

        </div>
    <?php
    }

    public function header_links( $links ) {
        $links = array(
            'documentation' => array(
                'url' => 'https://www.sunshinephotocart.com/docs/',
                'label' => __( 'Documentation', 'sunshine-photo-cart' )
            ),
            'review' => array(
                'url' => 'https://wordpress.org/support/plugin/sunshine-photo-cart/reviews/#new-post',
                'label' => __( 'Write a Review', 'sunshine-photo-cart' )
            ),
            'feedback' => array(
                'url' => 'https://www.sunshinephotocart.com/feedback',
                'label' => __( 'Feedback', 'sunshine-photo-cart' )
            ),
            'upgrade' => array(
                'url' => 'https://www.sunshinephotocart.com/pricing/',
                'label' => __( 'Upgrade', 'sunshine-photo-cart' )
            )
        );
        return $links;
    }

    public function admin_footer() {
        if ( !$this->is_sunshine() || SPC()->is_pro() ) {
            return;
        }
        // Exclusions
        $screen = get_current_screen();
        if ( ( $screen->post_type == 'sunshine-gallery' || $screen->post_type == 'sunshine-product' ) && $screen->base == 'post' ) {
            return;
        }
    ?>
        <div id="pro-upgrade">
    		<div class="sunshine-page-container">
    			<div id="pro-upgrade-content">
    				<h2>Upgrade Today!</h2>
    				<p>Extend Sunshine to make the most of your client photo gallery along with 1-on-1 support
    					<br /><strong>Starting at only <span style="text-decoration: line-through; font-weight: normal; color: #CCC;">$99</span> $59!</strong></p>
    				<p class="guarantee">30-day money back guarantee!</p>
    				<p><a href="https://www.sunshinephotocart.com/pricing/?utm_source=plugin&utm_medium=link&utm_campaign=pluginfooter" class="sunshine-button" target="_blank">Learn more</a></p>
    			</div>
    		</div>
    	</div>
    <?php
    }

    public function admin_enqueue_scripts() {

        wp_enqueue_style( 'sunshine-icons',  SUNSHINE_PHOTO_CART_URL . 'assets/css/icons.css' );
        wp_enqueue_style( 'sunshine-admin',  SUNSHINE_PHOTO_CART_URL . 'assets/css/admin.css' );

        wp_register_style( 'sunshine-jquery-ui', SUNSHINE_PHOTO_CART_URL . 'assets/jqueryui/smoothness/jquery-ui-1.9.2.custom.css', '', SUNSHINE_PHOTO_CART_VERSION );
        wp_register_script( 'select2', SUNSHINE_PHOTO_CART_URL . 'assets/js/select2/select2.min.js', array( 'jquery' ), '4.0.13' );
        wp_register_style( 'select2', SUNSHINE_PHOTO_CART_URL . 'assets/js/select2/select2.min.css', '4.0.13' );

        wp_register_script( 'ajaxq', SUNSHINE_PHOTO_CART_URL . 'assets/js/ajaxq.js', array( 'jquery' ), SUNSHINE_PHOTO_CART_VERSION );
        wp_register_script( 'chartjs', SUNSHINE_PHOTO_CART_URL . 'assets/js/chart.min.js', '', '3.8' );

        if ( $this->is_sunshine() ) {
            wp_enqueue_script( 'jquery-ui-sortable' );
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_script( 'wp-color-picker' );
            wp_enqueue_style( 'select2' );
            wp_enqueue_script( 'select2' );
            wp_enqueue_style( 'farbtastic' );
            wp_enqueue_script( 'farbtastic' );
            wp_enqueue_script( 'ajaxq' );
            wp_enqueue_media();
        }

        if ( $this->is_page( 'reports' ) ) {
            wp_enqueue_script( 'chartjs' );
        }

	}

    public function enqueue_block_editor_assets() {

        wp_enqueue_script(
            'sunshine-gallery-meta',
            SUNSHINE_PHOTO_CART_URL . '/assets/js/admin/meta/sunshine-gallery.js',
            array( 'wp-edit-post', 'wp-element', 'wp-components', 'wp-plugins', 'wp-data' ),
            filemtime( SUNSHINE_PHOTO_CART_PATH . 'assets/js/admin/meta/sunshine-gallery.js' )
        );

    }

    public function post_states( $post_states, $post ) {

        if ( SPC()->get_option( 'page' ) == $post->ID ) {
            $post_states['sunshine_page'] = __( 'Sunshine Main Page', 'sunshine-photo-cart' );
        }
        if ( SPC()->get_option( 'page_account' ) == $post->ID ) {
            $post_states['sunshine_page_account'] = __( 'Sunshine Account', 'sunshine-photo-cart' );
        }
        if ( SPC()->get_option( 'page_cart' ) == $post->ID ) {
            $post_states['sunshine_page_cart'] = __( 'Sunshine Cart', 'sunshine-photo-cart' );
        }
        if ( SPC()->get_option( 'page_checkout' ) == $post->ID ) {
            $post_states['sunshine_page_checkout'] = __( 'Sunshine Checkout', 'sunshine-photo-cart' );
        }
        if ( SPC()->get_option( 'page_favorites' ) == $post->ID ) {
            $post_states['sunshine_page_favorites'] = __( 'Sunshine Favorites', 'sunshine-photo-cart' );
        }
        if ( SPC()->get_option( 'page_terms' ) == $post->ID ) {
            $post_states['sunshine_page_terms'] = __( 'Sunshine Terms & Conditions', 'sunshine-photo-cart' );
        }

        return $post_states;

    }

    public function update_check() {
        if ( version_compare( SPC()->version, SUNSHINE_PHOTO_CART_VERSION, '<' ) || isset( $_GET['sunshine_force_update'] ) ) {
            sunshine_update();
        }
    }

    /* TODO: Check for various setting warnings and what not to notify the user of */
    public function warnings() {

        // When checking, might need to do transients so it is not getting checked on every admin page load

        // If shortcode option enabled but any of the existing pages do not have the shortcode



    }

    function force_imagick( $editors ) {
        if ( extension_loaded( 'imagick' ) ) {
            $editors = array( 'WP_Image_Editor_Imagick' );
        }
        return $editors;
    }

    function plugin_action_links( $links ) {
        if ( !SPC()->is_pro() ) {
            $upgrade_page = '<a href="https://www.sunshinephotocart.com/pricing/?utm_source=plugin&utm_medium=link&utm_campaign=upgrade" target="_blank"><b style="color: orange;">' . __( 'Upgrade', 'sunshine-photo-cart' ) . '</b></a>';
            array_unshift( $links, $upgrade_page );
        }
        return $links;
    }

    function admin_footer_text( $footer_text ) {
    	global $typenow;

    	if ( $typenow == 'sunshine-gallery' || $typenow == 'sunshine-product' || $typenow == 'sunshine-order' || $typenow == 'sunshine-product' || isset( $_GET['page'] ) && strpos( $_GET['page'], 'sunshine-photo-cart' ) !== false ) {
    		$rate_text = sprintf(
    			__( 'Thank you for using <a href="%1$s" target="_blank">Sunshine Photo Cart</a>! Please <a href="%2$s" target="_blank">rate us</a> on <a href="%2$s" target="_blank">WordPress.org</a>', 'sunshine-photo-cart' ),
    			'https://www.sunshinephotocart.com?utm_source=plugin&utm_medium=link&utm_campaign=rate',
    			'https://wordpress.org/support/view/plugin-reviews/sunshine-photo-cart?filter=5#postform'
    		);

    		return str_replace( '</span>', '', $footer_text ) . ' | ' . $rate_text . '</span>';
    	}

    	return $footer_text;

    }

    function flush_rewrite_page_save( $post_id ) {
    	if ( $post_id == SPC()->get_option( 'page' ) ) {
    		flush_rewrite_rules();
    	}
    }

    function image_sizes( $image_sizes ) {
        if ( isset( $_POST['action'] ) && ( $_POST['action'] == 'sunshine_gallery_upload' || $_POST['action'] == 'sunshine_gallery_import' || $_POST['action'] == 'sunshine_regenerate_image' ) ) {
            $new_image_sizes = array();
            foreach ( $image_sizes as $image_size ) {
                if ( strpos( $image_size, 'sunshine-photo-cart' ) ) {
                    $new_image_sizes[] = $image_size;
                }
            }
            $new_image_sizes[] = 'sunshine-thumbnail';
            $image_sizes = apply_filters( 'sunshine_image_sizes', $new_image_sizes );
        }
        return $image_sizes;
    }

    function big_image_size_threshold( $threshold, $imagesize, $file, $attachment_id ) {
        if ( isset( $_POST['action'] ) && ( $_POST['action'] == 'sunshine_gallery_upload' || $_POST['action'] == 'sunshine_file_save' ) ) {
            return false;
        } else {
            // If attachment is from sunshine-gallery post type also return false
            // For when rebuilding thumbnails via 3rd party plugins
            $attachment_parent_id = wp_get_post_parent_id( $attachment_id );
            if ( 'sunshine-gallery' == get_post_type( $attachment_parent_id ) ) {
                return false;
            }
        }
        return $threshold;
    }


    function clean_media_library( $query ) {

        if ( isset( $_POST['action'] ) && $_POST['action'] = 'query-attachments' && isset( $_POST['post_id'] ) && get_post_type( $_POST['post_id'] ) == 'sunshine-gallery' ) {
            return $query;
        }

    	if ( !SPC()->get_option( 'show_media_library' ) ) {
    		$args = array(
    			'post_type'   => 'sunshine-gallery',
    			'nopaging'    => true,
    			'post_status' => 'publish,private,trash',
    			'fields' => 'ids'
    		);
    		$gallery_ids = get_posts( $args );
    		if ( !empty( $gallery_ids ) ) {
    			$query['post_parent__not_in'] = $gallery_ids;
    		}
    	}
    	return $query;

    }

    function media_library_list( $query ) {
    	if ( $query->is_main_query() && !SPC()->get_option( 'show_media_library' ) ) {
    		$screen = get_current_screen();
    		if ( !empty( $screen ) && $screen->base == 'upload' ) {
    			$args = array(
    				'post_type' => 'sunshine-gallery',
    				'nopaging' => true,
    				'post_status' => 'any',
    				'fields' => 'ids'
    			);
    			$gallery_ids = get_posts( $args );
    			if ( !empty( $gallery_ids ) ) {
    				$query->set( 'post_parent__not_in', $gallery_ids );
    			}
    		}
    	}
    }

    function admin_bar_view_client_galleries() {
    	global $wp_admin_bar;
    	if ( is_admin() ) {
    		$wp_admin_bar->add_node(
    			array(
    				'id'     => 'sunshine-client-galleries',
    				'title'  => __( 'View Client Galleries', 'sunshine-photo-cart' ),
    				'href'   => get_permalink( SPC()->get_option( 'page' ) ),
    				'parent' => 'site-name',
    			)
    		);
    	}
    }

    function order_status_admin_customizations() {
    	$screen = get_current_screen();
    	if ( $screen->id == 'edit-sunshine-order-status' ) {
        ?>
            <script>
            jQuery( document ).ready( function($) {
                $( '.bulkactions' ).remove();
                $( '.inline-edit-row label:nth-child(2)' ).remove();
                		<?php
                		if ( isset( $_GET['tag_ID'] ) ) {
                            $current_status = get_term( intval( $_GET['tag_ID'] ) );
                			$core_statuses = sunshine_core_order_statuses();
                			if ( in_array( $current_status->slug, $core_statuses ) ) {
                		?>
                                $( '#delete-link' ).remove();
                                $( '.form-table tr:nth-child(2) p.description' ).html( '<?php echo esc_js( __( 'The slug for core order statuses is not editable as it could break Sunshine functionality', 'sunshine-photo-cart' ) ); ?>' );
                                $( '.form-field input[name="slug"]' ).attr( 'disabled', 'disabled' );
                        <?php } } ?>
        	});
        	</script>
    	<?php
        }
    }

    function post_updated_messages( $messages ) {
        global $post;
        $messages[ 'sunshine-order' ][1] = __( 'Order Updated', 'sunshine-photo-cart' );
        $messages[ 'sunshine-gallery' ][1] = sprintf( __( '<strong>Gallery updated</strong>, <a href="%s">view gallery</a>', 'sunshine-photo-cart' ), get_permalink( $post->ID ) );
        $messages[ 'sunshine-gallery' ][6] = sprintf( __( '<strong>Gallery created</strong>, <a href="%s">view gallery</a>', 'sunshine-photo-cart' ), get_permalink( $post->ID ) );
        return $messages;
    }

    function logging_notice() {
        if ( SPC()->get_option( 'log' ) ) {
            SPC()->notices->add_admin( 'log', __( 'Sunshine logging is enabled. Please disable when no longer in use.', 'sunshine-photo-cart' ), 'notice' );
        }
    }

    function sortable() {
        $screen = get_current_screen();

        // Sortable product categories
        if ( $screen->id == 'edit-sunshine-product-category' ) {
        ?>
            <script>
            jQuery( document ).ready(function($){
                var item_list = jQuery( '#the-list' );
                item_list.sortable({
                    update: function(event, ui) {
                        item_list.addClass( 'sunshine-loading' );
                        var category_order = item_list.sortable( 'toArray' ).toString();
                        opts = {
                            url: ajaxurl,
    						type: 'POST',
    						async: true,
    						cache: false,
    						dataType: 'json',
    						data:{
    							action: 'sunshine_product_category_sort',
    							categories: category_order
    						},
                            complete: function() {
                                item_list.removeClass( 'sunshine-loading' );
    							return;
                            }
                        };
                        jQuery.ajax( opts );
                    }
                });
            });
            </script>
        <?php
        }

        if ( $screen->id == 'edit-sunshine-product' ) {
        ?>
            <script>
            jQuery( document ).ready(function($){
                var item_list = jQuery( '#the-list' );
                item_list.sortable({
                    update: function(event, ui) {
                        item_list.addClass( 'sunshine-loading' );
                        var product_order = item_list.sortable( 'toArray' ).toString();
                        opts = {
                            url: ajaxurl,
                            type: 'POST',
                            async: true,
                            cache: false,
                            dataType: 'json',
                            data:{
                                action: 'sunshine_product_sort',
                                products: product_order
                            },
                            complete: function() {
                                item_list.removeClass( 'sunshine-loading' );
                                return;
                            }
                        };
                        jQuery.ajax( opts );
                    }
                });
            });
            </script>
        <?php
        }

    }

    public function product_category_sort() {
        $categories = sanitize_text_field( $_POST['categories'] );
        $categories = str_replace( 'tag-', '', $categories );
        $categories = explode( ',', $categories );
        $i = 1;

        foreach ( $categories as $category_id ) {
            update_term_meta( $category_id, 'order', $i );
            $i++;
        }
    }

    public function product_sort() {
        $products = sanitize_text_field( $_POST['products'] );
        $products = str_replace( 'post-', '', $products );
        $products = explode( ',', $products );
        $i = 1;
        foreach ( $products as $post_id ) {
            wp_update_post(array(
                'ID' => $post_id,
                'menu_order' => $i
            ));
            $i++;
        }
    }

    public function pre_get_posts_sort( $query ) {

        if ( $query->get( 'post_type' ) == 'sunshine-product' ) {
            $query->set( 'orderby', 'menu_order' );
            $query->set( 'order', 'ASC' );
        }

        return $query;

    }

    public function create_product_category( $term_id = 0, $tt_id = 0, $taxonomy = '' ) {
        $order = get_term_meta( $term_id, 'order', true );
        if ( empty( $order ) ) {
            add_term_meta( $term_id, 'order', 1 );
        }
    }

    public function product_category_term_clauses( $pieces, $taxonomies, $args ) {
        global $wpdb;
        if ( in_array( 'sunshine-product-category', $taxonomies ) ) {
            $pieces['join']  .= ' INNER JOIN ' . $wpdb->termmeta . ' AS tm ON t.term_id = tm.term_id ';
            $pieces['where'] .= ' AND tm.meta_key = "order"';
            $pieces['orderby']  = ' ORDER BY tm.meta_value ';
        }
        return $pieces;
    }


}

$sunshine_admin = new Sunshine_Admin();
