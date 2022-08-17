<?php
function sunshine_reports() {

    // Get the very first order. Will determine if we have any orders at all and what dates to limit stuff to
    $init_order = sunshine_get_orders(array(
        'nopaging' => false,
        'posts_per_page' => 1,
        'order' => 'ASC',
    ));
    if ( empty( $init_order ) ) {
        echo '<div class="wrap" id="sunshine-reports--no-data">' . __( 'Sorry, you do not yet have any order data yet' ) . '</div>';
        return;
    }

    $first_order = $init_order[0];

    $durations = apply_filters( 'sunshine_reports_date_formats', array(
        'day' => array(
            'label' => __( 'Day', 'sunshine-photo-cart' ),
            'after' => date( 'Y-m-d' ),
            'before' => date( 'Y-m-d' )
        ),
        'week' => array(
            'label' => __( 'Week', 'sunshine-photo-cart' ),
            'after' => date( 'Y-m-d', strtotime( '-7 days' ) ),
            'before' => date( 'Y-m-d' )
        ),
        'month' => array(
            'label' => __( 'Month', 'sunshine-photo-cart' ),
            'after' => date( 'Y-m-d', strtotime( '-1 months' ) ),
            'before' => date( 'Y-m-d' )
        ),
        'year' => array(
            'label' => __( 'Year', 'sunshine-photo-cart' ),
            'active' => '',
            'after' => date( 'Y-m-d', strtotime( '-1 years' ) ),
            'before' => date( 'Y-m-d' )
        ),
        'all' => array(
            'label' => __( 'All time', 'sunshine-photo-cart' ),
            'after' => $first_order->get_date( 'Y-m-d' ),
            'before' => date( 'Y-m-d' )
        ),
    ) );
    $current_duration = ( isset( $_GET['duration'] ) ) ? sanitize_text_field( $_GET['duration'] ) : 'month';
    $current_after = ( isset( $_GET['after'] ) ) ? sanitize_text_field( $_GET['after'] ) : $durations[ $current_duration ]['after'];
    $current_before = ( isset( $_GET['before'] ) ) ? sanitize_text_field( $_GET['before'] ) : $durations[ $current_duration ]['before'];

    if ( isset( $_GET['after'] ) && isset( $_GET['before'] ) ) {
        if ( $_GET['after'] == $_GET['before'] ) {
            $current_duration = 'day';
        } else {
            $current_duration = 'custom';
        }
    }

    // Append time for more specific
    $current_after .= ' 00:00:00';
    $current_before .= ' 23:59:59';
?>
    <div class="wrap">
        <div id="sunshine-reports-header">
            <div id="sunshine-reports-header--title">
                <h1><?php _e( 'Reports', 'sunshine-photo-cart' ); ?></h1>
            </div>
            <div id="sunshine-reports-header--filter">
                <div id="sunshine-reports-header--filter--dates">
                    <form method="get" action="<?php echo admin_url( 'edit.php' ); ?>">
                        <input type="hidden" name="post_type" value="sunshine-gallery" />
                        <input type="hidden" name="page" value="sunshine_reports" />
                        <input type="hidden" name="duration" value="custom" />
                        <input type="date" name="after" value="<?php echo esc_attr( date( 'Y-m-d', strtotime( $current_after ) ) ); ?>" min="<?php echo esc_attr( $first_order->get_date( 'Y-m-d' ) ); ?>" max="<?php echo date( 'Y-m-d' ); ?>" />
                        <input type="date" name="before" value="<?php echo esc_attr( date( 'Y-m-d', strtotime( $current_before ) ) ); ?>" min="<?php echo esc_attr( $first_order->get_date( 'Y-m-d' ) ); ?>" max="<?php echo date( 'Y-m-d' ); ?>" />
                        <input type="submit" value="<?php esc_attr_e( 'Filter', 'sunshine-photo-cart' ); ?>" class="button" />
                    </form>
                </div>
                <nav>
                    <?php foreach ( $durations as $key => $duration ) {
                        $class = ( $key == $current_duration ) ? 'active' : '';
                        echo '<a class="' . $class . '" href="' . admin_url( 'edit.php?post_type=sunshine-gallery&page=sunshine_reports&duration=' . esc_attr( $key ) ) . '">' . esc_html( $duration['label'] ) . '</a>';
                    } ?>
                </nav>
            </div>
        </div>

        <?php
        $args = array(
            'status' => sunshine_order_statuses_paid(),
            'after' => array(
                'year' => date( 'Y', strtotime( $current_after ) ),
                'month' => date( 'n', strtotime( $current_after ) ),
                'day' => date( 'j', strtotime( $current_after ) ),
            ),
            'before' => array(
                'year' => date( 'Y', strtotime( $current_before ) ),
                'month' => date( 'n', strtotime( $current_before ) ),
                'day' => date( 'j', strtotime( $current_before ) ),
            ),
        );
        $paid_orders = sunshine_get_orders( $args );

        $args = array(
            'status' => sunshine_order_statuses_needs_payment(),
            'after' => array(
                'year' => date( 'Y', strtotime( $current_after ) ),
                'month' => date( 'n', strtotime( $current_after ) ),
                'day' => date( 'j', strtotime( $current_after ) ),
            ),
            'before' => array(
                'year' => date( 'Y', strtotime( $current_before ) ),
                'month' => date( 'n', strtotime( $current_before ) ),
                'day' => date( 'j', strtotime( $current_before ) ),
            ),
        );
        $needs_payment_orders = sunshine_get_orders( $args );

        $paid_labels = $paid_values = array();
        $paid_count = $paid_total = $needs_payment_count = $needs_payment_total = 0;
        $key_format = 'Y-m-d';
        $display_format = 'M j';

        switch( $current_duration ) {
            case 'day':
                $interval = DateInterval::createFromDateString( '1 hour' );
                $key_format = 'Y-m-d-H';
                $display_format = 'D gA';
                break;
            case 'all':
                $diff = date_diff( date_create( $current_after ), date_create( $current_before ) );
                if ( $diff->days > 365 ) {
                    $interval = DateInterval::createFromDateString( '1 month' );
                    $key_format = 'Y-m';
                    $display_format = 'M Y';
                } else {
                    $interval = DateInterval::createFromDateString( '1 day' );
                }
                break;
            default:
                $interval = DateInterval::createFromDateString( '1 day' );
                break;
        }

        $period = new DatePeriod( new DateTime( $current_after ), $interval, new DateTime( $current_before ) );
        foreach ( $period as $dt ) {
            $paid_labels[ $dt->format( $key_format ) ] = $dt->format( $display_format );
            $paid_values[ $dt->format( $key_format ) ] = 0;
        }


        if ( !empty( $paid_orders ) ) {

            foreach ( $paid_orders as $order ) {
                $paid_values[ $order->get_date( $key_format ) ] += $order->get_total();
                $paid_total += $order->get_total();
            }

            $paid_count = count( $paid_orders );

            ksort( $paid_labels );
            ksort( $paid_values );

        }

        $needs_payment_count = 0;
        $needs_payment_total = 0;

        if ( !empty( $needs_payment_orders ) ) {

            foreach ( $needs_payment_orders as $order ) {
                $needs_payment_total += $order->get_total();
            }

            $needs_payment_count = count( $needs_payment_orders );

        }

        ?>

        <div id="sunshine-reports--stats">
            <div clas="sunshine-report--stat">
                <h3><?php _e( 'Total Received', 'sunshine-photo-cart' ); ?></h3>
                <p><?php echo sunshine_price( $paid_total, true ); ?></p>
            </div>
            <div clas="sunshine-report--stat">
                <h3><?php _e( 'Completed Orders', 'sunshine-photo-cart' ); ?></h3>
                <p><?php echo $paid_count; ?></p>
            </div>
            <div clas="sunshine-report--stat">
                <h3><?php _e( 'Average Order', 'sunshine-photo-cart' ); ?></h3>
                <p><?php echo ( $paid_count ) ? sunshine_price( $paid_total / $paid_count, true ) : sunshine_price( 0, true ); ?></p>
            </div>
            <?php if ( $needs_payment_count ) { ?>
                <div clas="sunshine-report--stat">
                    <h3><?php _e( 'Total Unpaid Orders', 'sunshine-photo-cart' ); ?></h3>
                    <p><?php echo $needs_payment_count; ?> (<?php echo sunshine_price( $needs_payment_total, true ); ?>)</p>
                </div>
            <?php } ?>

        </div>

        <div id="sunshine-reports--chart">

            <canvas id="sunshine-chart" width="100%" height="600"></canvas>
            <script>
            const ctx = document.getElementById('sunshine-chart').getContext('2d');
            const myChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode( array_values( $paid_labels ) ); ?>,
                    datasets: [{
                        lineTension: 0.4,
                        //label: '# of Votes',
                        borderColor: "#FF8500",
                        backgroundColor: "rgba(255,133,0,.5)",
                        fill: true,
                        data: <?php echo json_encode( array_values( $paid_values ) ); ?>,
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if ( label ) {
                                        label += ': ';
                                    }
                                    if ( context.parsed.y !== null ) {
                                        label += new Intl.NumberFormat( 'en-US', { style: 'currency', currency: '<?php echo esc_js( SPC()->get_option( 'currency' ) ); ?>' } ).format( context.parsed.y );
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    hover: {
                        mode: 'nearest',
                        intersect: false
                    },
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value, index, ticks) {
                                    return new Intl.NumberFormat('en-US', { style: 'currency', currency: '<?php echo esc_js( SPC()->get_option( 'currency' ) ); ?>' }).format(value);
                                }
                            }
                        },
                        x: {
                            grid: {
                                drawBorder: false,
                                lineWidth: 0,
                            },
                            ticks: {
                                autoSkip: true,
                                maxTicksLimit: 15
                            }
                        }
                    }
                }
            });
            </script>

        </div>

    </div>
<?php
}
