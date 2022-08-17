<?php
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

class SPC_Payment_Method_PayPal extends SPC_Payment_Method {

    public function init() {

        $this->id = 'paypal';
        $this->name = __( 'PayPal', 'sunshine-photo-cart' );
        $this->class = 'Sunshine_Payment_Method_PayPal';
        $this->description = __( 'Pay with credit card or your PayPal account', 'sunshine-photo-cart' );
        $this->can_be_enabled = true;
        $this->needs_billing_address = false;

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_filter( 'sunshine_checkout_field_payment_method_paypal', array( $this, 'buttons' ) );

        add_action( 'wp_ajax_sunshine_checkout_paypal_create_order', array( $this, 'create_order' ) );
        add_action( 'wp_ajax_nopriv_sunshine_checkout_paypal_create_order', array( $this, 'create_order' ) );

        add_action( 'wp_ajax_sunshine_checkout_paypal_approve', array( $this, 'approve' ) );
        add_action( 'wp_ajax_nopriv_sunshine_checkout_paypal_approve', array( $this, 'approve' ) );

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
        sunshine_log( $order->get_payment_method(), 'PayPal create_order_status' );

        if ( $order->get_payment_method() == $this->id ) {
            sunshine_log( 'Set to new' );
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
            wp_enqueue_script( 'sunshine-paypal-checkout', $url );
        }
    }

    public function buttons( $html ) {
        ob_start();
    ?>
        <div id="sunshine-checkout-paypal-buttons"></div>
        <script>
            jQuery( document ).ready(function($){
                $( '#sunshine-checkout-paypal-buttons' ).hide(); // Hide it by default

                // Show PayPal buttons when selected as the option
                $( document ).on( 'change', 'input[name="payment_method"]', function(){
                    var sunshine_paypal_selected_payment_method = $( 'input[name="payment_method"]:checked' ).val();
                    if ( sunshine_paypal_selected_payment_method == 'paypal' ) {
                        sunshine_render_paypal_buttons();
                        $( '#sunshine-checkout-field-submit button' ).hide();
                        $( '#sunshine-checkout-paypal-buttons' ).show();
                    } else {
                        $( '#sunshine-checkout-field-submit button' ).show();
                        $( '#sunshine-checkout-paypal-buttons' ).hide();
                    }
                });

                // Check if PayPal selected on load
                var sunshine_paypal_selected_payment_method = $( 'input[name="payment_method"]:checked' ).val();
                if ( sunshine_paypal_selected_payment_method == 'paypal' ) {
                    sunshine_render_paypal_buttons();
                    $( '#sunshine-checkout-field-submit button' ).hide();
                    $( '#sunshine-checkout-paypal-buttons' ).show();
                } else {
                    $( '#sunshine-checkout-field-submit button' ).show();
                    $( '#sunshine-checkout-paypal-buttons' ).hide();
                }

            });

            function sunshine_render_paypal_buttons() {

                jQuery( '#sunshine-checkout-paypal-buttons' ).html( '' );

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
                        var data = new FormData();
                        data.append( 'action', 'sunshine_checkout_paypal_approve' );
                        data.append( 'order_id', result.orderID );
                        data.append( 'payer_id', result.payerID );
                        data.append( 'payment_source', result.paymentSource );
                        data.append( 'facilitator_access_token', result.facilitatorAccessToken );
                        data.append( 'security', '<?php echo wp_create_nonce( 'sunshine_checkout_paypal_approve' ); ?>' );
                        return fetch( '<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                          method: 'post',
                          body: data
                        }).then(function( res ) {
                            return res.json();
                        }).then(function( order ) {

                            if ( order.success ) {
                                window.location.href = order.data.url;
                                return;
                            }

                            return actions.restart();

                        });
                    }

                }).render('#sunshine-checkout-paypal-buttons');

            }

        </script>

    <?php
        $output = ob_get_contents();
        ob_end_clean();
        return $html . $output;
    }

    public function create_order() {

        // TODO: Validate nonce

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
                 'shipping' => [
                     'name' => [
                       'full_name' => SPC()->cart->get_checkout_data_item( 'shipping_first_name' ) . ' ' . SPC()->cart->get_checkout_data_item( 'shipping_last_name' )
                        ],
                     'address' => [
                       'address_line_1' => SPC()->cart->get_checkout_data_item( 'shipping_address1' ),
                       'admin_area_2' => SPC()->cart->get_checkout_data_item( 'shipping_city' ),
                       'postal_code' => SPC()->cart->get_checkout_data_item( 'shipping_postcode' ),
                       'country_code' => SPC()->cart->get_checkout_data_item( 'shipping_country' ),
                   ]
               ]
             ]],
             "application_context" => [
                 'brand_name' => get_bloginfo( 'name' ),
                 'shipping_preference' => 'SET_PROVIDED_ADDRESS',
             ]
        ];

        $request->body = apply_filters( 'sunshine_paypal_order_args', $order_args );

        try {

            $response = $client->execute( $request );
            wp_send_json_success( array( 'order_id' => $response->result->id ) );

        } catch (HttpException $ex) {
            wp_send_json_error();
        }


    }

    public function approve() {

        // TODO: Validate nonce

        if ( !isset( $_POST['order_id'] ) ) {
            return false;
        }

        $paypal_order_id = sanitize_text_field( $_POST['order_id'] );

        $clientId = $this->get_client_id();
        $clientSecret = $this->get_secret();

        $client = new PayPalHttpClient( $this->get_environment() );

        $request = new OrdersCaptureRequest( $paypal_order_id );
        $request->prefer( 'return=representation' );
        try {
            $response = $client->execute( $request );
            $order = SPC()->cart->create_order();
            // Add extra meta
            $order->update_meta_value( 'paypal_order_id', $paypal_order_id );
            $order->update_meta_value( 'paypal_order_id', $paypal_order_id );
            $order->update_meta_value( 'payment_source', sanitize_text_field( $_POST['payment_source'] ) );
            $payment_data = $response->result->purchase_units[0]->payments->captures[0]->seller_receivable_breakdown;
            foreach ( $payment_data as $key => $data ) {
                $order->update_meta_value( sanitize_key( $key ), sanitize_text_field( $data->value ) );
            }
            wp_send_json_success( array( 'order_id' => $order->get_id(), 'url' => $order->get_permalink() ) );
        } catch ( HttpException $ex ) {
            sunshine_log( 'Paypal error code: ' . $ex->statusCode );
            sunshine_log($ex->getMessage());
            wp_send_json_error();
        }
        return false;
    }

}
