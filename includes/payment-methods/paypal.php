<?php
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Payments\CapturesRefundRequest;

class SPC_Payment_Method_PayPal extends SPC_Payment_Method {

    private $extra_meta_data = array();

    public function init() {

        $this->id = 'paypal';
        $this->name = __( 'PayPal', 'sunshine-photo-cart' );
        $this->class = get_class( $this );
        $this->description = __( 'Pay with credit card or your PayPal account', 'sunshine-photo-cart' );
        $this->can_be_enabled = true;
        $this->needs_billing_address = false;

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_filter( 'sunshine_checkout_field_payment_method_paypal', array( $this, 'buttons' ) );

        add_action( 'wp_ajax_sunshine_checkout_paypal_create_order', array( $this, 'create_order' ) );
        add_action( 'wp_ajax_nopriv_sunshine_checkout_paypal_create_order', array( $this, 'create_order' ) );

        /*
        add_action( 'wp_ajax_sunshine_checkout_paypal_approve', array( $this, 'approve' ) );
        add_action( 'wp_ajax_nopriv_sunshine_checkout_paypal_approve', array( $this, 'approve' ) );
        */
        add_action( 'sunshine_checkout_process_payment_paypal', array( $this, 'process_payment' ) );
        add_action( 'sunshine_checkout_create_order', array( $this, 'create_order_extra_data' ), 10, 2 );

        add_filter( 'sunshine_order_transaction_url', array( $this, 'transaction_url' ) );

        add_filter( 'sunshine_admin_order_tabs', array( $this, 'admin_order_tab' ), 10, 2 );
        add_action( 'sunshine_admin_order_tab_paypal', array( $this, 'admin_order_tab_content' ) );

        add_action( 'sunshine_order_actions', array( $this, 'order_actions' ) );
        add_action( 'sunshine_order_actions_options', array( $this, 'order_actions_options' ) );
        add_action( 'sunshine_order_process_action_paypal_refund', array( $this, 'process_refund' ) );

    }

    public function options( $options ) {
        $options[] = array(
            'name' => __( 'Mode', 'sunshine-photo-cart' ),
            'id'   => $this->id . '_mode',
            'type' => 'radio',
            'options' => array(
                'live' => __( 'Live', 'sunshine-photo-cart' ),
                'sandbox' => __( 'Sandbox', 'sunshine-photo-cart' )
            ),
            'default' => 'live'
        );
        $options[] = array(
            'name' => __( 'Live Client ID', 'sunshine-photo-cart' ),
            'id'   => $this->id . '_client_id',
            'type' => 'text',
            'conditions' => array(
                array(
                    'field' => $this->id . '_mode',
                    'compare' => '==',
                    'value' => 'live',
                    'action' => 'show'
                )
            )
        );
        $options[] = array(
            'name' => __( 'Live Secret', 'sunshine-photo-cart' ),
            'id'   => $this->id . '_secret',
            'type' => 'text',
            'conditions' => array(
                array(
                    'field' => $this->id . '_mode',
                    'compare' => '==',
                    'value' => 'live',
                    'action' => 'show'
                )
            )
        );
        $options[] = array(
            'name' => __( 'Sandbox Client ID', 'sunshine-photo-cart' ),
            'id'   => $this->id . '_client_id_sandbox',
            'type' => 'text',
            'conditions' => array(
                array(
                    'field' => $this->id . '_mode',
                    'compare' => '==',
                    'value' => 'sandbox',
                    'action' => 'show'
                )
            )
        );
        $options[] = array(
            'name' => __( 'Sandbox Live Secret', 'sunshine-photo-cart' ),
            'id'   => $this->id . '_secret_sandbox',
            'type' => 'text',
            'conditions' => array(
                array(
                    'field' => $this->id . '_mode',
                    'compare' => '==',
                    'value' => 'sandbox',
                    'action' => 'show'
                )
            )
        );
        $options[] = array(
            'name' => __( 'Button Layout', 'sunshine-photo-cart' ),
            'id'   => $this->id . '_style_layout',
            'type' => 'select',
            'options' => array(
                'vertical' => __( 'Vertical', 'sunshine-photo-cart' ),
                'horizontal' => __( 'Horizontal', 'sunshine-photo-cart' ),
            ),
            'default' => 'horizontal',
            'description' => '<a href="https://developer.paypal.com/sdk/js/reference/#link-layout" target="_blank">' . __( 'See details', 'sunshine-photo-cart' ) . '</a>'
        );
        $options[] = array(
            'name' => __( 'Color', 'sunshine-photo-cart' ),
            'id'   => $this->id . '_style_color',
            'type' => 'select',
            'options' => array(
                'gold' => __( 'Gold', 'sunshine-photo-cart' ),
                'blue' => __( 'Blue', 'sunshine-photo-cart' ),
                'silver' => __( 'Silver', 'sunshine-photo-cart' ),
                'white' => __( 'white', 'sunshine-photo-cart' ),
                'black' => __( 'Black', 'sunshine-photo-cart' ),
            ),
            'default' => 'gold',
            'description' => '<a href="https://developer.paypal.com/sdk/js/reference/#link-color" target="_blank">' . __( 'See details', 'sunshine-photo-cart' ) . '</a>'
        );
        $options[] = array(
            'name' => __( 'Button Shape', 'sunshine-photo-cart' ),
            'id'   => $this->id . '_style_shape',
            'type' => 'select',
            'options' => array(
                'rect' => __( 'Rectangle', 'sunshine-photo-cart' ),
                'pill' => __( 'Pill', 'sunshine-photo-cart' )
            ),
            'default' => 'rect',
            'description' => '<a href="https://developer.paypal.com/sdk/js/reference/#link-shape" target="_blank">' . __( 'See details', 'sunshine-photo-cart' ) . '</a>'
        );
        $options[] = array(
            'name' => __( 'Label', 'sunshine-photo-cart' ),
            'id'   => $this->id . '_style_label',
            'type' => 'select',
            'options' => array(
                'paypal' => __( 'PayPal', 'sunshine-photo-cart' ),
                'checkout' => __( 'Checkout', 'sunshine-photo-cart' ),
                'buynow' => __( 'Buy Now', 'sunshine-photo-cart' ),
                'pay' => __( 'Pay', 'sunshine-photo-cart' ),
                'installment' => __( 'Installment', 'sunshine-photo-cart' ),
            ),
            'default' => 'paypal',
            'description' => '<a href="https://developer.paypal.com/sdk/js/reference/#link-label" target="_blank">' . __( 'See details', 'sunshine-photo-cart' ) . '</a>'
        );
        $options[] = array(
            'name' => __( 'Tagline', 'sunshine-photo-cart' ),
            'id'   => $this->id . '_style_tagline',
            'type' => 'select',
            'options' => array(
                'true' => __( 'Show', 'sunshine-photo-cart' ),
                'false' => __( 'Hide', 'sunshine-photo-cart' ),
            ),
            'default' => 'true',
            'conditions' => array(
                array(
                    'field' => $this->id . '_style_layout',
                    'compare' => '==',
                    'value' => 'horizontal',
                    'action' => 'show'
                )
            ),
            'description' => '<a href="https://developer.paypal.com/sdk/js/reference/#link-tagline" target="_blank">' . __( 'See details', 'sunshine-photo-cart' ) . '</a>'
        );
        $options[] = array(
            'name' => __( 'Hide funding sources', 'sunshine-photo-cart' ),
            'id'   => $this->id . '_style_disable_funding_sources',
            'type' => 'select',
            'select2' => true,
            'multiple' => true,
            'options' => array(
                'card' => __( 'Credit or debit cards', 'sunshine-photo-cart' ),
                'credit' => __( 'PayPal Credit (US, UK)', 'sunshine-photo-cart' ),
                'paylater' => __( 'Pay Later (US, UK), Pay in 4 (AU), 4X PayPal (France), Später Bezahlen (Germany)', 'sunshine-photo-cart' ),
                'bancontact' => __( 'Bancontact', 'sunshine-photo-cart' ),
                'blik' => __( 'BLIK', 'sunshine-photo-cart' ),
                'eps' => __( 'eps', 'sunshine-photo-cart' ),
                'giropay' => __( 'giropay', 'sunshine-photo-cart' ),
                'ideal' => __( 'iDEAL', 'sunshine-photo-cart' ),
                'mercadopago' => __( 'Mercado Pago', 'sunshine-photo-cart' ),
                'mybank' => __( 'MyBank', 'sunshine-photo-cart' ),
                'p24' => __( 'Przelewy24', 'sunshine-photo-cart' ),
                'sepa' => __( 'SEPA-Lastschrift', 'sunshine-photo-cart' ),
                'sofort' => __( 'Sofort', 'sunshine-photo-cart' ),
                'venmo' => __( 'Venmo', 'sunshine-photo-cart' ),
            ),
            'default' => 'true',
            'description' => sprintf( __( 'By default, all possible funding sources will be shown. This setting can disable funding sources such as Credit Cards, Pay Later, Venmo, or other <a href="%s" target="_blank">Alternative Payment Methods</a>', 'sunshine-photo-cart' ), 'https://developer.paypal.com/docs/checkout/apm/' )
        );

        return $options;
    }

    public function get_option( $key ) {
        return SPC()->get_option( $this->id . '_' . $key );
    }

    public function create_order_status( $status, $order ) {
        if ( $order->get_payment_method() == $this->id ) {
            return 'new'; // Straight to new
        }
        return $status;
    }

    public function get_mode() {
        return SPC()->get_option( $this->id . '_mode' );
    }

    public function get_client_id() {
        return ( $this->get_mode() == 'live' ) ? SPC()->get_option( $this->id . '_client_id' ) : SPC()->get_option( $this->id . '_client_id_sandbox' );
    }

    public function get_secret() {
        return ( $this->get_mode() == 'live' ) ? SPC()->get_option( $this->id . '_secret' ) : SPC()->get_option( $this->id . '_secret_sandbox' );
    }

    public function is_active() {
        $active = SPC()->get_option( $this->id . '_active' );
        if ( !empty( $active ) && $this->get_client_id() ) {
            return true;
        }
        return false;
    }

    public function get_environment() {
        if ( $this->get_mode() == 'live' ) {
            return new ProductionEnvironment( $this->get_client_id(), $this->get_secret() );
        } else {
            return new SandboxEnvironment( $this->get_client_id(), $this->get_secret() );
        }
    }

    public function enqueue_scripts() {
        if ( is_sunshine_page( 'checkout' ) ) {
            $url = 'https://www.paypal.com/sdk/js?client-id=' . $this->get_client_id() . '&currency=' . SPC()->get_option( 'currency' );
            //$url = add_query_arg( 'client-id', $this->get_client_id(), $url );
            //$url = add_query_arg( 'debug', 'true', $url );
            //$url = add_query_arg( 'currency', SPC()->get_option( 'currency' ), $url );
            wp_enqueue_script( 'sunshine-paypal-checkout', $url, '', null );
        }
    }

    public function buttons( $html ) {
        ob_start();
    ?>
        <div id="sunshine--checkout--paypal-buttons"></div>
        <script>
            jQuery( document ).ready(function($){
                $( '#sunshine--checkout--paypal-buttons' ).hide(); // Hide it by default

                // Show PayPal buttons when selected as the option
                $( document ).on( 'change', 'input[name="payment_method"]', function(){
                    var sunshine_paypal_selected_payment_method = $( 'input[name="payment_method"]:checked' ).val();
                    if ( sunshine_paypal_selected_payment_method == 'paypal' ) {
                        sunshine_render_paypal_buttons();
                        $( '#sunshine--checkout--field--submit button' ).hide();
                        $( '#sunshine--checkout--paypal-buttons' ).show();
                    } else {
                        $( '#sunshine--checkout--field--submit button' ).show();
                        $( '#sunshine--checkout--paypal-buttons' ).hide();
                    }
                });

                // Check if PayPal selected on load
                var sunshine_paypal_selected_payment_method = $( 'input[name="payment_method"]:checked' ).val();
                if ( sunshine_paypal_selected_payment_method == 'paypal' ) {
                    sunshine_render_paypal_buttons();
                    $( '#sunshine--checkout--field--submit button' ).hide();
                    $( '#sunshine--checkout--paypal-buttons' ).show();
                } else {
                    $( '#sunshine--checkout--field--submit button' ).show();
                    $( '#sunshine--checkout--paypal-buttons' ).hide();
                }

            });

            function sunshine_render_paypal_buttons() {

                jQuery( '#sunshine--checkout--paypal-buttons' ).html( '' );

                paypal.Buttons({

                    style: {
                        layout: '<?php echo esc_js( $this->get_option( 'style_layout' ) ); ?>',
                        color:  '<?php echo esc_js( $this->get_option( 'style_color' ) ); ?>',
                        shape:  '<?php echo esc_js( $this->get_option( 'style_shape' ) ); ?>',
                        label:  '<?php echo esc_js( $this->get_option( 'style_label' ) ); ?>',
                        <?php if ( $this->get_option( 'style_layout' ) == 'horizontal' ) { ?>
                            tagline:  <?php echo esc_js( $this->get_option( 'style_tagline' ) ); ?>
                        <?php } ?>
                    },
                    // Call your server to set up the transaction
                    createOrder: function(data, actions) {
                        sunshine_checkout_updating();
                        var data = new FormData();
                        data.append( 'action', 'sunshine_checkout_paypal_create_order' );
                        data.append( 'security', '<?php echo wp_create_nonce( 'sunshine_checkout_paypal_create_order' ); ?>' );
                        return fetch('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                          method: 'post',
                          body: data
                      }).then(function(result) {
                          return result.json();
                      }).then(function ( result ) {
                            return result.data.order_id; // the data is the order object returned from the api call, its not the BrainTree.Response object
                        });

                    },
                    // Call your server to finalize the transaction
                    onApprove: function(result, actions) {
                        sunshine_checkout_updating();
                        jQuery( '#sunshine--checkout' ).append( '<input type="hidden" name="paypal_order_id" value="' + result.orderID + '" />' );
                        jQuery( '#sunshine--checkout' ).append( '<input type="hidden" name="paypal_payer_id" value="' + result.payerID + '" />' );
                        jQuery( '#sunshine--checkout' ).append( '<input type="hidden" name="paypal_payment_source" value="' + result.paymentSource + '" />' );
                        jQuery( '#sunshine--checkout--field--submit button' ).show().trigger( 'click' );
                    }

                }).render( '#sunshine--checkout--paypal-buttons' );

            }

        </script>

    <?php
        $output = ob_get_contents();
        ob_end_clean();
        return $html . $output;
    }

    public function create_order() {

        // TODO: Validate nonce

        SPC()->cart->setup();

        // Creating an environment
        $clientId = $this->get_client_id();
        $clientSecret = $this->get_secret();

        $client = new PayPalHttpClient( $this->get_environment() );

        $request = new OrdersCreateRequest();
        $request->prefer( 'return=representation' );

        $items = array();
        foreach ( SPC()->cart->get_cart_items() as $cart_item ) {
            $items[] = [
                'name' => $cart_item->get_name_raw(),
                'unit_amount' => [
                    'value' => $cart_item->get_price(),
                    'currency_code' => SPC()->get_option( 'currency' )
                ],
                'quantity' => $cart_item->get_qty()
            ];
        }

        $order_args = [
             "intent" => "CAPTURE",
             "purchase_units" => [[
                 "amount" => [
                     "value" => SPC()->cart->get_total(),
                     'currency_code' => SPC()->get_option( 'currency' ),
                     'breakdown' => [
                         'item_total' => [
                             'value' => SPC()->cart->get_subtotal(),
                             'currency_code' => SPC()->get_option( 'currency' )
                         ],
                         'tax_total' => [
                             'value' => SPC()->cart->get_tax(),
                             'currency_code' => SPC()->get_option( 'currency' )
                         ],
                         'shipping' => [
                             'value' => SPC()->cart->get_shipping(),
                             'currency_code' => SPC()->get_option( 'currency' )
                         ],
                         'discount' => [
                             'value' => SPC()->cart->get_discount() + SPC()->cart->get_credits_applied(),
                             'currency_code' => SPC()->get_option( 'currency' )
                         ]
                     ]
                 ],
                 'items' => $items,
                 //'shipping' => $shipping
             ]],
             "application_context" => [
                 'brand_name' => get_bloginfo( 'name' ),
                 //'shipping_preference' => ( !empty( $shipping ) ) ? 'SET_PROVIDED_ADDRESS' : '',
             ]
        ];

        $shipping = array();
        if ( SPC()->cart->needs_shipping() ) {
            $order_args['purchase_units'][0]['shipping'] = array(
                'name' => [
                    'full_name' => SPC()->cart->get_checkout_data_item( 'shipping_first_name' ) . ' ' . SPC()->cart->get_checkout_data_item( 'shipping_last_name' )
                ],
                'address' => [
                    'address_line_1' => SPC()->cart->get_checkout_data_item( 'shipping_address1' ),
                    'address_line_2' => SPC()->cart->get_checkout_data_item( 'shipping_address2' ),
                    'admin_area_2' => SPC()->cart->get_checkout_data_item( 'shipping_city' ),
                    'postal_code' => SPC()->cart->get_checkout_data_item( 'shipping_postcode' ),
                    'country_code' => SPC()->cart->get_checkout_data_item( 'shipping_country' ),
                ],
                'type' => 'SHIPPING'
            );
            $order_args['application_context']['shipping_preference'] = 'SET_PROVIDED_ADDRESS';
        }

        $request->body = apply_filters( 'sunshine_paypal_order_args', $order_args );

        try {

            $response = $client->execute( $request );
            wp_send_json_success( array( 'order_id' => $response->result->id ) );

        } catch (HttpException $ex) {
            wp_send_json_error();
        }


    }

    public function process_payment( $cart ) {

        if ( !isset( $_POST['paypal_order_id'] ) ) {
            return false;
        }

        $paypal_order_id = sanitize_text_field( $_POST['paypal_order_id'] );

        SPC()->log( 'Processing PayPal payment for PayPal Order ID ' . $paypal_order_id );

        $clientId = $this->get_client_id();
        $clientSecret = $this->get_secret();

        $client = new PayPalHttpClient( $this->get_environment() );

        $request = new OrdersCaptureRequest( $paypal_order_id );
        $request->prefer( 'return=representation' );
        try {
            $response = $client->execute( $request );
            if ( $response->result->status == 'COMPLETED' ) {
                // Add extra meta
                $payment_breakdown_data = $response->result->purchase_units[0]->payments->captures[0]->seller_receivable_breakdown;
                if ( !empty( $payment_breakdown_data ) ) {
                    foreach ( $payment_breakdown_data as $key => $data ) {
                        $this->extra_meta_data[ $key ] = $data->value;
                    }
                }
                $this->extra_meta_data['paypal_capture_id'] = $response->result->purchase_units[0]->payments->captures[0]->id;
                $this->extra_meta_data['paypal_order_id'] = $paypal_order_id;
                $this->extra_meta_data['paypal_payer_id'] = $_POST['paypal_payer_id'];
                $this->extra_meta_data['paypal_payment_source'] = $_POST['paypal_payment_source'];
            } else {
                $cart->add_error( 'paypal_not_completed_' . timestamp(), __( 'PayPal payment not completed', 'sunshine-photo-cart' ) );
            }

        } catch ( HttpException $ex ) {
            $cart->add_error( $ex->getMessage() );
        }
    }

    // After order is successfully done, add extra info for PayPal
    public function create_order_extra_data( $order, $data ) {

        if ( !empty( $this->extra_meta_data ) ) {

            SPC()->log( 'Adding PayPal extra meta data to order: ' . json_encode( $this->extra_meta_data ) );

            foreach ( $this->extra_meta_data as $key => $value ) {
                $order->update_meta_value( sanitize_key( $key ), sanitize_text_field( $value ) );
            }

            $note = sprintf( __( 'Payment processed by %s', 'sunshine-photo-cart' ), $this->name );
            $order->add_log( $note );

        }

    }

    public function get_transaction_id( $order ) {
        return $order->get_meta_value( 'paypal_order_id' );
    }

    public function get_capture_id( $order ) {
        return $order->get_meta_value( 'paypal_capture_id' );
    }

    public function get_transaction_url( $order ) {
        if ( $order->get_payment_method() == 'paypal' ) {
            $capture_id = $order->get_meta_value( 'paypal_capture_id' );
            if ( $capture_id ) {
                $mode = $order->get_meta_value( 'paypal_mode' );
                $transaction_url = ( $mode == 'test' || $mode == 'sandbox' ) ? 'https://www.sandbox.paypal.com/activity/payment/' : 'https://www.paypal.com/activity/payment/';
                $transaction_url .= $capture_id;
                return $transaction_url;
            }
        }
        return false;
    }

    public function admin_order_tab( $tabs, $order ) {
        if ( $order->get_payment_method() == $this->id ) {
            $tabs['paypal'] = __( 'PayPal', 'sunshine-photo-cart' );
        }
        return $tabs;
    }

    public function admin_order_tab_content( $order ) {
        echo '<table class="sunshine-data">';
        echo '<tr><th>' . __( 'PayPal fees', 'sunshine-photo-cart' ) . '</th><td>' . sunshine_price( $order->get_meta_value( 'paypal_fee' ), true ) . '</td></tr>';
        echo '<tr><th>' . __( 'Net amount', 'sunshine-photo-cart' ) . '</th><td>' . sunshine_price( $order->get_meta_value( 'net_amount' ), true ) . '</td></tr>';
        echo '<tr><th>' . __( 'Payment Source', 'sunshine-photo-cart' ) . '</th><td>' . $order->get_meta_value( 'paypal_payment_source' ) . '</td></tr>';
        echo '<tr><th>' . __( 'Capture ID', 'sunshine-photo-cart' ) . '</th><td>' . $this->get_capture_id( $order ) . '</td></tr>';
        echo '</table>';
    }

    function order_actions( $actions ) {
        $actions['paypal_refund'] = __( 'Refund payment in PayPal', 'sunshine-photo-cart' );
        return $actions;
    }

    function order_actions_options( $order ) {
    ?>
        <div id="paypal-refund-order-actions" style="display: none;">
            <p><label><input type="checkbox" name="paypal_refund_notify" value="yes" checked="checked" /> <?php _e( 'Notify customer via email', 'sunshine-photo-cart' ); ?></label></p>
            <p><label><input type="checkbox" name="paypal_refund_full" value="yes" checked="checked" /> <?php _e( 'Full refund', 'sunshine-photo-cart' ); ?></label></p>
            <p id="paypal-refund-amount" style="display: none;"><label><input type="number" name="paypal_refund_amount" step=".01" size="6" style="width:100px" max="<?php echo esc_attr( $order->get_total() ); ?>" value="<?php echo esc_attr( $order->get_total() ); ?>" /> <?php _e( 'Amount to refund', 'sunshine-photo-cart' ); ?></label></p>
        </div>
        <script>
            jQuery( 'select[name="sunshine_order_action"]' ).on( 'change', function(){
                let selected_action = jQuery( 'option:selected', this ).val();
                if ( selected_action == 'paypal_refund' ) {
                    jQuery( '#paypal-refund-order-actions' ).show();
                } else {
                    jQuery( '#paypal-refund-order-actions' ).hide();
                }
            });
            jQuery( 'input[name="paypal_refund_full"]' ).on( 'change', function(){
                if ( !jQuery(this).prop( "checked" ) ) {
                    jQuery( '#paypal-refund-amount' ).show();
                } else {
                    jQuery( '#paypal-refund-amount' ).hide();
                }
            });
        </script>
    <?php
    }

    function process_refund( $order_id ) {

        $order = new SPC_Order( $order_id );
        $capture_id = $this->get_capture_id( $order );
        if ( !empty( $capture_id ) ) {

            $clientId = $this->get_client_id();
            $clientSecret = $this->get_secret();
            $client = new PayPalHttpClient( $this->get_environment() );

            $request = new CapturesRefundRequest( $capture_id );

            $refund_amount = $order->get_total();

            if ( !empty( $_POST['paypal_refund_full'] ) && $_POST['paypal_refund_amount'] < $refund_amount ) {
                $refund_amount = sanitize_text_field( $_POST['paypal_refund_amount'] );
            }

            $request->body = array(
                'amount' =>
                    array(
                        'value' => $refund_amount,
                        'currency_code' => $order->get_currency()
                    )
            );

            try {
                $response = $client->execute( $request );
                if ( !empty( $partial_refund_amount ) ) {
                    $order->add_log( __( 'Order partially refunded in PayPal', 'sunshine-photo-cart' ) . ': ' . sunshine_price( $partial_refund_amount ) );
                    SPC()->notices->add_admin( 'paypal_refund_' . $capture_id, sprintf( __( '%s refunded in PayPal', 'sunshine-photo-cart' ), sunshine_price( $partial_refund_amount ) ) );
                } else {
                    $order->add_log( __( 'Order fully refunded in PayPal', 'sunshine-photo-cart' ) );
                    $order->set_status( 'refunded' );
                    SPC()->notices->add_admin( 'paypal_refund_' . $capture_id, __( 'Order fully refunded in PayPal', 'sunshine-photo-cart' ) );
                }
            } catch (PayPalHttp\HttpException $ex) {
                $error = json_decode( $ex->getMessage() );
                SPC()->session->set( 'admin_notices', array( 'paypal_refund_' . $capture_id => array( 'message' => $error->details[0]->description, 'type' => 'success' ) ) );
                SPC()->notices->add_admin( 'paypal_refund_' . $capture_id, $error->details[0]->description, 'error' );
                $order->add_log( __( 'Order failed refund in PayPal', 'sunshine-photo-cart' ) . ': ' . $error->details[0]->description );
            }

        }

    }

    public function mode( $mode, $order ) {
        if ( $order->get_payment_method() == 'paypal' ) {
            return ( $this->get_mode() == 'live' ) ? 'live' : 'test';
        }
        return $mode;
    }

}
