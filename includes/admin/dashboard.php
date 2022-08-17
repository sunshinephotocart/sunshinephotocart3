<?php
class SPC_Dashboard_Widget {

    function __construct() {

        add_action( 'wp_dashboard_setup', array( $this, 'init' ) );
        add_action( 'wp_ajax_sunshine_dashboard_calculate_stats', array( $this, 'calculate_stats' ) );
        add_action( 'sunshine_refresh_order_stats', array( $this, 'refresh_stats' ) );

    }

    function init() {
        wp_add_dashboard_widget( 'sunshine-dashboard', __( 'Sunshine Photo Cart Sales Summary', 'sunshine-photo-cart' ), array( $this, 'sales' ), null, null, 'normal', 'high' );
        if ( $this->needs_setup() ) {
            wp_add_dashboard_widget( 'sunshine-dashboard-setup', __( 'Sunshine Photo Cart Setup', 'sunshine-photo-cart' ), array( $this, 'setup' ), null, null, 'side', 'high' );
        }
    }

    function sales() {
        //echo _n( 'sale', 'sales', $result->orders, 'sunshine-photo-cart' );
    ?>
        <div id="sunshine-dashboard-widget-sales" class="sunshine-loading">
            <div class="sunshine-dashboard-widget-sales--group" id="sunshine-this-month">
                <h3><?php _e( 'Current month sales', 'sunshine-photo-cart' ); ?></h3>
                <p>
                    <span class="total">&mdash;</span>
                    <span class="count">&mdash;</span>
                </p>
            </div>
            <div class="sunshine-dashboard-widget-sales--group" id="sunshine-last-month">
                <h3><?php _e( 'Last month sales', 'sunshine-photo-cart' ); ?></h3>
                <p>
                    <span class="total">&mdash;</span>
                    <span class="count">&mdash;</span>
                </p>
            </div>
            <div class="sunshine-dashboard-widget-sales--group" id="sunshine-lifetime">
                <h3><?php _e( 'Lifetime sales', 'sunshine-photo-cart' ); ?></h3>
                <p>
                    <span class="total">&mdash;</span>
                    <span class="count">&mdash;</span>
                </p>
            </div>
            <div class="sunshine-dashboard-widget-sales--group" id="sunshine-new">
                <h3><?php _e( 'New Orders', 'sunshine-photo-cart' ); ?></h3>
                <p>
                    <a href="<?php echo admin_url( 'edit.php?post_type=sunshine-order&sunshine-order-status=new' ); ?>"><span class="total">&mdash;</span></a>
                </p>
            </div>
        </div>
        <script>
        jQuery( document ).ready(function($) {

            var data = {
                'action': 'sunshine_dashboard_calculate_stats',
            };

            // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
            $.post( ajaxurl, data, function(response) {
                $( '#sunshine-this-month span.total' ).html( response.this_month.total );
                $( '#sunshine-this-month span.count' ).html( response.this_month.count );
                $( '#sunshine-last-month span.total' ).html( response.last_month.total );
                $( '#sunshine-last-month span.count' ).html( response.last_month.count );
                $( '#sunshine-lifetime span.total' ).html( response.lifetime.total );
                $( '#sunshine-lifetime span.count' ).html( response.lifetime.count );
                $( '#sunshine-new span.total' ).html( response.new.count );
                if ( response.new.count > 0 ) {
                    $( '#sunshine-new a' ).show();
                }
                $( '#sunshine-dashboard-widget-sales' ).removeClass( 'sunshine-loading' );
            });
        });
        </script>

        <?php
        $orders = sunshine_get_orders(array(
            'nopaging' => false,
            'posts_per_page' => apply_filters( 'sunshine_dashboard_recent_orders_count', 10 ),
        ));
        if ( !empty( $orders ) ) {
        ?>
        <div id="sunshine-dashboard-widget-recent">
            <h3><?php _e( 'Recent Orders', 'sunshine-photo-cart' ); ?></h3>
            <table>
                <?php foreach( $orders as $order ) { ?>
                <tr>
                    <td><a href="<?php echo admin_url( 'post.php?action=edit&post=' . $order->get_id() ); ?>"><?php echo $order->get_name(); ?></a></td>
                    <td><?php echo $order->get_date(); ?></td>
                    <td><?php echo $order->get_status_name(); ?></td>
                    <td><?php echo $order->get_total_formatted(); ?></td>
                </tr>
                <?php } ?>
            </table>
        </div>
        <?php } ?>
    <?php
    }

    function calculate_stats() {
        global $wpdb;

        $data = get_transient( 'sunshine-dashboard-sales' );

        if ( empty( $data ) ) {

            $this_month = $wpdb->get_row( "
                SELECT SUM(pm.meta_value) as total, COUNT(*) as order_count FROM {$wpdb->postmeta} pm
                LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                WHERE pm.meta_key = 'total'
                AND p.post_type = 'sunshine-order'
                AND YEAR(post_date_gmt) = '" . date( 'Y', time() ) . "'
                AND MONTH(post_date_gmt) = '" . date( 'm', time() ) . "'
            " );

            $last_month = $wpdb->get_row( "
                SELECT SUM(pm.meta_value) as total, COUNT(*) as order_count FROM {$wpdb->postmeta} pm
                LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                WHERE pm.meta_key = 'total'
                AND p.post_type = 'sunshine-order'
                AND YEAR(post_date_gmt) = '" . date( 'Y', strtotime( "-1 months" ) ) . "'
                AND MONTH(post_date_gmt) = '" . date( 'm', strtotime( "-1 months" ) ) . "'
            " );

            $lifetime = $wpdb->get_row( $wpdb->prepare( "
                SELECT SUM(pm.meta_value) as total, COUNT(*) as order_count FROM {$wpdb->postmeta} pm
                LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                WHERE pm.meta_key = '%s'
                AND p.post_type = '%s'
              ", 'total', 'sunshine-order' ) );

            $new_orders = sunshine_get_orders( array( 'status' => 'new' ) );

            $data = array(
                'this_month' => array(
                    'count' => $this_month->order_count,
                    'total' => sunshine_price( $this_month->total, true )
                ),
                'last_month' => array(
                    'count' => $last_month->order_count,
                    'total' => sunshine_price( $last_month->total, true )
                ),
                'lifetime' => array(
                    'count' => $lifetime->order_count,
                    'total' => sunshine_price( $lifetime->total, true )
                ),
                'new' => array(
                    'count' => count( $new_orders ),
                ),
            );

            set_transient( 'sunshine-dashboard-sales', $data, DAY_IN_SECONDS );

        }

        wp_send_json( $data );

    }

    function refresh_stats() {
        delete_transient( 'sunshine-dashboard-sales' );
    }

    function needs_setup() {
        if ( !SPC()->get_option( 'address1' ) ) {
            return true;
        } elseif ( empty( sunshine_get_products() ) ) {
            return true;
        } elseif( empty( sunshine_get_active_payment_methods() ) ) {
            return true;
        } elseif ( empty( sunshine_get_active_shipping_methods() ) ) {
            return true;
        } elseif ( !SPC()->get_option( 'logo' ) ) {
            return true;
        }
        return false;
    }

    function setup() {
    ?>
        <div id="sunshine-dashboard-widget-setup">
            <ol>

                <?php if ( !SPC()->get_option( 'address1' ) ) { ?>
                    <li>
                        <div>
                            <p>Start configuring your store including address, pages, URLs, and more...</p>
                            <p><a href="<?php echo admin_url( 'edit.php?post_type=sunshine-gallery&page=sunshine' ); ?>">See settings</a></p>
                        </div>
                    </li>
                <?php } else { ?>
                    <li class="completed"><p>Store Configuration</p></li>
                <?php } ?>

                <?php if ( empty( sunshine_get_products() ) ) { ?>
                    <li>
                        <div>
                            <p>Create products and set prices to start selling</p>
                            <p><a href="<?php echo admin_url( 'edit.php?post_type=sunshine-product' ); ?>">Add products</a></p>
                        </div>
                    </li>
                <?php } else { ?>
                    <li class="completed"><p>Products</p></li>
                <?php } ?>

                <?php if ( empty( sunshine_get_active_payment_methods() ) ) { ?>
                    <li>
                        <div>
                            <p>Configure payment methods to start receiving money</p>
                            <p><a href="<?php echo admin_url( 'admin.php?page=sunshine&section=payment_methods' ); ?>">Select payment methods</a></p>
                        </div>
                    </li>
                <?php } else { ?>
                    <li class="completed"><p>Payment methods</p></li>
                <?php } ?>

                <?php if ( empty( sunshine_get_active_shipping_methods() ) ) { ?>
                    <li>
                        <div>
                            <p>Configure shipping methods to get orders to customers</p>
                            <p><a href="<?php echo admin_url( 'admin.php?page=sunshine&section=shipping_methods' ); ?>">Setup shipping methods</a></p>
                        </div>
                    </li>
                <?php } else { ?>
                    <li class="completed"><p>Shipping methods</p></li>
                <?php } ?>

                <?php if ( !SPC()->get_option( 'logo' ) ) { ?>
                    <li>
                        <div>
                            <p>Customize the look of Sunshine with your logo and other options</p>
                            <p><a href="<?php echo admin_url( 'admin.php?page=sunshine&section=display' ); ?>">Configure display options</a></p>
                        </div>
                    </li>
                <?php } else { ?>
                    <li class="completed"><p>Customization</p></li>
                <?php } ?>

                <?php if ( !SPC()->is_pro() ) { ?>
                    <li>
                        <div>
                            <p>Upgrade for more features and increase revenue</p>
                            <p><a href="<?php echo admin_url( 'https://www.sunshinephotocart.com/pricing/?utm_source=plugin&utm_medium=link&utm_campaign=dashboardwidget' ); ?>" target="_blank">Learn more about Pro</a></p>
                        </div>
                    </li>
                <?php } else { ?>
                    <li class="completed"><p>Customization</p></li>
                <?php } ?>

            </ol>
        </div>
    <?php
    }

}

$spc_dashboard_widget = new SPC_Dashboard_Widget();
