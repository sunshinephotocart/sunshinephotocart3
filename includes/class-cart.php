<?php

class SPC_Cart {

    // Cart variables
    protected $cart = array();
    protected $cart_items = array();
    protected $subtotal = 0.00;
    protected $taxable = 0.00;
    protected $tax = 0.00;
    protected $shipping = 0.00;
    //protected $fees = array();
    protected $total = 0.00;
    protected $discounts = array();
    protected $discount = 0.00;
    protected $use_credits = false;

    // Checkout variables
    private $active_section;
    private $fields = array();
    private $hidden_fields = array();
    private $delivery_method;
    private $shipping_method;
    private $payment_method;
    private $errors = array();
    private $data = array();


    function __construct() {
        add_action( 'wp', array( $this, 'setup' ), 1 );
        //add_action( 'init', array( $this, 'set_checkout_data' ), 1 );
        add_action( 'wp', array( $this, 'process_payment' ), 100 );
        add_action( 'wp', array( $this, 'check_expirations' ) );
    }

    public function add_error( $id, $error ) {
        SPC()->log( 'Add to cart error (' . $id . '):' . $error );
        $this->errors[ $id ] = $error;
    }

    public function get_errors() {
        return $this->errors;
    }

    public function has_errors() {
        if ( !empty( $this->get_errors() ) ) {
            return true;
        }
        return false;
    }

    public function setup() {

        // Get cart to start
        $this->get_cart_items();

        // Get any session saved data for the cart
        $data = SPC()->session->get( 'checkout_data' );

        if ( !empty( $data ) ) {
            $this->data = $data;
        }

        // Set delivery method
        if ( array_key_exists( 'delivery_method', $this->data ) ) {
            $this->delivery_method = sunshine_get_delivery_method_by_id( $this->data['delivery_method'] );
        }

        // Set shipping method
        if ( array_key_exists( 'shipping_method', $this->data ) ) {
            $this->shipping_method = sunshine_get_shipping_method_by_instance( $this->data['shipping_method'] );
        }

        // Set payment method
        if ( array_key_exists( 'payment_method', $this->data ) && SPC()->payment_methods->is_payment_method_allowed( $this->data['payment_method'] ) ) {
            $this->payment_method = SPC()->payment_methods->get_payment_method_by_id( $this->data['payment_method'] );
        }

        $this->set_checkout_fields();

        // Set which section we are in
        if ( isset( $_GET['section'] ) ) {
            $this->active_section = sanitize_key( $_GET['section'] );
            // TODO: Make other sections after this one not completed?
        }

        // If still no active section, go through all sections to see if any are completed and set the first one not completed to active
        if ( empty( $this->active_section ) ) {
            foreach ( $this->fields as $section_id => $section ) {
                if ( sunshine_checkout_section_completed( $section_id ) ) {
                    continue;
                }
                if ( empty( $section['fields'] ) ) { // If no fields in this section, we can skip as well
                    continue;
                }
                $this->active_section = $section_id;
                break;
            }
        }

    }

    // An alternate name for this to make it easier to understand elsewhere
    public function update() {
        SPC()->session->set( 'data', $this->data );
        $this->setup();
    }

    public function get_cart() {
        $cart = SPC()->session->get( 'cart' );
		$this->cart = $cart;
        return $this->cart;
	}

    public function get_cart_items( $refresh = false ) {

        $this->get_cart();

        if ( empty( $this->cart ) ) {
            return false;
        }

        // Return already set data so we don't run this more than necessary
        if ( !empty( $this->cart_items ) && !$refresh ) {
            return $this->cart_items;
        }

        $final_contents = array();
        foreach ( $this->cart as $item ) {
            $final_contents[] = new SPC_Cart_Item( $item );
        }

		$final_contents = apply_filters( 'sunshine_cart_items', $final_contents, $this );

        $this->cart_items = $final_contents;

		return (array) $final_contents;
    }

    public function add_item( $object_id, $product_id, $gallery_id, $options = array(), $overwrite = false ) {

        $can_add_item = true;

        // We need a gallery this is tied to so we know the price level
        if ( empty( $gallery ) && !empty( $gallery_id ) ) {
            $gallery = new SPC_Gallery( intval( $gallery_id ) );
    		if ( empty( $gallery->get_id() ) ) {
    			return false;
    		}
        } else {
            // If we don't have a gallery from the image and don't have a gallery ID, we cannot figure out price level so bail
            return false;
        }

        $product = new SPC_Product( intval( $product_id ), $gallery->get_price_level() );
        if ( empty( $product->get_id() ) ) {
            return false;
        }

        // If we are adding an image type product, then let's get the image as well
        if ( $product->get_product_type() == 'image' ) {
            $image = new SPC_Image( intval( $object_id ));
            if ( empty( $image ) ) {
                return false;
            }
            $gallery = $image->get_gallery();
        }

		if ( !$product->can_purchase() || !$gallery->can_purchase() ) {
			return false;
		}

        $this->get_cart();

        $options_default = array(
            'object_id' => $object_id,
            'gallery_id' => $gallery->get_id(),
            'gallery_name' => $gallery->get_name(),
            'product_id' => $product->get_id(),
            'product_name' => $product->get_name(),
            'filename' => ( !empty( $image ) ) ? $image->get_file_name() : '',
            'image_name' => ( !empty( $image ) ) ? $image->get_name() : '',
            'price_level' => $gallery->get_price_level(),
            'qty' => 1,
            'price' => $product->get_price( $gallery->get_price_level() ),
            'needs_shipping' => $product->needs_shipping(),
            'shipping' => $product->get_shipping(),
            'comments' => '',
            'type' => $product->get_product_type(),
            'taxable' => $product->is_taxable(),
            'hash' => md5( time() )
        );

        $item = wp_parse_args( $options, $options_default );

        // If object already in cart, increase quantity of existing and not add
        if ( !empty( $this->cart ) ) {
            foreach ( $this->cart as $key => &$cart_item ) {
                if ( $item['object_id'] == $cart_item['object_id'] && $item['product_id'] == $cart_item['product_id'] ) {
                    $this->remove_item( $key ); // Remove the existing item with old quantity
                    if ( !$overwrite ) {
                        $item['qty'] = $cart_item['qty'] + $item['qty'];
                        $this->remove_item( $key ); // Remove the existing item with old quantity
                    }
                }
            }
        }

        // TODO: Tie into this filter for digital downloads to limit them to quantity 1
        $item = apply_filters( 'sunshine_add_to_cart_item', $item );

        // Add item data to cart contents
        if ( $can_add_item && $item['qty'] > 0 ) {
            $this->cart[] = $item;
        }

        $this->update_cart();

        do_action( 'sunshine_add_cart_item', $item );

        SPC()->log( 'Item added to cart: ' . json_encode( $item ) );

        return $item;

    }

    // TODO
    public function remove_item( $key ) {
        if ( array_key_exists( $key, $this->cart ) ){
            $item = $this->cart[ $key ];
            unset( $this->cart[ $key ] );
            SPC()->session->set( 'cart', $this->cart );
            SPC()->log( 'Item removed from cart: ' . json_encode( $item ) );

        }
    }

    public function update_cart() {
        // Set cart session
        SPC()->session->set( 'cart', $this->cart );
        // Set user cart meta
        sunshine_maybe_set_customer_cart( $this->cart );
    }

    public function get_item_count() {
        if ( !empty( $this->cart ) && is_array( $this->cart ) ) {
            $count = 0;
            foreach ( $this->cart as $item ) {
                $count += $item['qty'];
            }
            return $count;
        }
        return 0;
    }

    public function is_empty() {
        return 0 == $this->get_item_count();
    }

    public function empty_cart() {

        SPC()->session->set( 'cart', NULL );

        // Remove any active discounts
        $this->remove_all_discounts();
        $this->cart = array();
        $this->cart_items = array();
        sunshine_maybe_set_customer_cart( NULL );

        do_action( 'sunshine_empty_cart' );

    }

    public function get_discounts() {
        $discounts = SPC()->session->get( 'discounts' );
		$this->discounts = ! empty( $discounts ) ? explode( '|', $discounts ) : array();
		return $this->discounts;
	}

    public function has_discounts() {
        if ( null !== $this->has_discounts ) {
            return $this->has_discounts;
        }

        $has_discounts = false;

        $discounts = $this->get_discounts();
        if ( ! empty( $discounts ) ) {
            $has_discounts = true;
        }

        $this->has_discounts = apply_filters( 'sunshine_cart_has_discounts', $has_discounts );

        return $this->has_discounts;
    }

    public function remove_discount( $code = '' ) {
        if ( empty( $code ) ) {
            return;
        }

        if ( $this->discounts ) {
            $key = array_search( $code, $this->discounts );

            if ( false !== $key ) {

                $discount = $this->discounts[ $key ];

                unset( $this->discounts[ $key ] );

                $this->discounts = implode( '|', array_values( $this->discounts ) );

                // update the active discounts
                SPC()->session->set( 'discounts', $this->discounts );

                do_action( 'sunshine_cart_discount_removed', $code, $this->discounts );
                do_action( 'sunshine_cart_discounts_updated', $this->discounts );

                SPC()->log( 'Discount removed: ' . $discount );

            }

        }

        return $this->discounts;
    }

    public function remove_all_discounts() {
        SPC()->session->set( 'discounts', null );
        do_action( 'sunshine_cart_discounts_removed' );
        SPC()->log( 'All discounts removed' );
    }

    public function get_subtotal() {

        $this->subtotal = 0;
        if ( !$this->is_empty() ) {

            $cart_items = $this->get_cart_items();
            foreach ( $cart_items as $item ) {
                $this->subtotal += $item->get_total();
            }
            if ( $this->subtotal < 0 ) {
                $this->subtotal = 0.00;
            }

        }

		return apply_filters( 'sunshine_get_cart_subtotal', $this->subtotal, $this );

	}

    public function get_subtotal_formatted() {
        return sunshine_price( $this->get_subtotal() );
    }

    public function get_discount() {
        return $this->discount;
    }
    public function get_discount_formatted() {
        return sunshine_price( $this->get_discount() );
    }

    public function get_tax() {
        $taxes_enabled = SPC()->get_option( 'taxes_enabled' );
        if ( $this->is_empty() || !$taxes_enabled ) {
            return 0;
        }

        $tax_rates = sunshine_get_tax_rates();
        if ( empty( $tax_rates ) || !is_array( $tax_rates ) ) {
            return 0;
        }

        // TODO: Determine tax on a PICKUP order

        // Which address is used for taxes
        $tax_basis = SPC()->get_option( 'tax_basis' );
        if ( $tax_basis == 'billing' ) {
            $prefix = 'billing_';
        } else {
            $prefix = 'shipping_';
        }

        // Match address to tax rate
        $customer_country = $this->get_checkout_data_item( $prefix . 'country' );
        $customer_state = $this->get_checkout_data_item( $prefix . 'state' );
        $customer_postcode = $this->get_checkout_data_item( $prefix . 'postcode' );

        $matched_tax_rate = '';
        foreach ( $tax_rates as $tax_rate ) {
            if ( empty( $tax_rate['rate'] ) ) {
                continue;
            }
            if ( !empty( $tax_rate['postcode'] ) ) {
                $postcodes = explode( ',', str_replace( ' ', '', $tax_rate['postcode'] ) );
                if ( in_array( $customer_postcode, $postcodes ) ) {
                    if ( !empty( $tax_rate['state'] ) && $customer_state == $tax_rate['state'] ) {
                        if ( !empty( $tax_rate['country'] ) && $customer_country == $tax_rate['country'] ) {
                            $matched_tax_rate = $tax_rate;
                            break;
                        }
                    }
                }
            } elseif ( !empty( $tax_rate['state'] ) ) {
                if ( !empty( $tax_rate['country'] ) && $customer_country == $tax_rate['country'] && !empty( $tax_rate['state'] ) && $customer_state == $tax_rate['state'] ) {
                    $matched_tax_rate = $tax_rate;
                    break;
                }
            } elseif ( !empty( $tax_rate['country'] ) ) {
                if ( $customer_country == $tax_rate['country'] ) {
                    $matched_tax_rate = $tax_rate;
                    break;
                }
            }
        }

        if ( !$matched_tax_rate ) {
            return 0;
        }

        // Figure out taxable amount
        $contents = $this->get_cart_items();
        $taxable = 0;
        foreach ( $contents as $item ) {
            if ( $item->is_taxable() ) {
                $taxable += $item->get_total();
            }
        }

        // Is selected shipping taxable?
        if ( $this->shipping_method && $this->shipping_method->is_taxable() ) {
            $taxable += $this->shipping_method->get_price();
        }

        $this->taxable = $taxable;

        // Apply tax rate to taxable amount
        $this->tax = $taxable * ( $matched_tax_rate['rate'] / 100 );
        $this->tax = apply_filters( 'sunshine_cart_tax', $this->tax, $tax_rate );
        if ( empty( $this->tax ) ) {
            $this->tax = 0;
        }

        return $this->tax;

    }
    public function get_tax_formatted() {
        return sunshine_price( $this->get_tax() );
    }

    public function get_credits() {
        if ( !$this->use_credits() ) {
            return 0;
        }
        return SPC()->customer->get_credits();
    }

    public function get_credits_applied() {
        if ( !$this->use_credits() ) {
            return 0;
        }
        $total_credits = SPC()->customer->get_credits();
        $order_total_without_credits = $this->get_total( array( 'credits' ) );
        if ( $total_credits > $order_total_without_credits ) {
            $credits_applied = $order_total_without_credits;
        }
        $this->credits = $credits_applied;
        return $credits_applied;
    }
    public function get_credits_applied_formatted() {
        return sunshine_price( $this->get_credits_applied() );
    }

    public function use_credits() {
        return $this->get_checkout_data_item( 'use_credits' );
    }

    public function set_use_credits( $value ) {
        $this->use_credits = false;
        if ( !empty( $value ) ) {
            $this->use_credits = true;
        }
        $this->set_checkout_data_item( 'use_credits', $this->use_credits );
        $this->update();
    }

    public function set_delivery_method( $method ) {
        if ( is_string( $method ) ) {
            // Let's see if this string is in the available instances
            $active_methods = sunshine_get_delivery_methods();
            if ( array_key_exists( $method, $active_methods ) ) {
                $this->delivery_method = sunshine_get_delivery_method_by_id( $method );
                $this->set_checkout_data_item( 'delivery_method', $method );
            }
        } else {
            $this->delivery_method = $method;
        }
        $this->set_checkout_data_item( 'delivery_method', $this->delivery_method->get_id() );
    }
    public function get_delivery_method_id() {
        if ( $this->delivery_method ) {
            return $this->delivery_method->get_id();
        }
        return false;
    }

    // Must pass instance string or full object of shipping class
    public function set_shipping_method( $method ) {
        if ( is_string( $method ) ) {
            // Let's see if this string is in the available instances
            $active_methods = sunshine_get_active_shipping_methods();
            if ( array_key_exists( $method, $active_methods ) ) {
                $this->shipping_method = sunshine_get_shipping_method_by_instance( $method );
            }
        } else {
            $this->shipping_method = $method;
        }
        $this->set_checkout_data_item( 'shipping_method', $this->shipping_method->get_id() );
    }

    public function set_payment_method( $method ) {
        if ( is_string( $method ) ) {
            // Let's see if this string is in the available instances
            $active_methods = sunshine_get_active_payment_methods();
            if ( array_key_exists( $method, $active_methods ) ) {
                $this->payment_method = sunshine_get_payment_method_by_id( $method );
            }
        } else {
            $this->payment_method = $method;
        }
        $this->set_checkout_data_item( 'payment_method', $this->payment_method->get_id() );
    }

    public function needs_shipping() {

        if ( empty( $this->cart ) ) {
            //$this->setup();
        }

        $needs_shipping = false;
        if ( !empty( $this->delivery_method ) ) {
            $needs_shipping = $this->delivery_method->needs_shipping();
        } else {
            if ( !SPC()->cart->is_empty() ) {
                foreach ( SPC()->cart->get_cart_items() as $item ) {
                    if ( $item->product->needs_shipping() ) {
                        $needs_shipping = true;
                        break;
                    }
                }
            }
        }
        return apply_filters( 'sunshine_cart_needs_shipping', $needs_shipping );
    }

    public function get_shipping() {
        if ( !$this->shipping_method ) {
            return 0;
        }
        $shipping = $this->shipping_method->get_price();
        return $shipping;
    }

    public function get_shipping_formatted() {
        $amount = $this->get_shipping();
        return sunshine_price( $amount );
    }

    // TODO: Use fees somehow?
    public function get_fees() {
        return 0;
    }

    public function get_total( $exclude = array() ) {
        $total = 0;
        if ( !in_array( 'subtotal', $exclude ) ) {
            $subtotal = (float) $this->get_subtotal();
            $total += $subtotal;
        }
        if ( !in_array( 'discounts', $exclude ) ) {
            $discounts = (float) $this->get_discount();
            $total -= $discounts;
        }
        if ( !in_array( 'credits', $exclude ) ) {
            $credits = (float) $this->get_credits_applied();
            $total -= $credits;
        }
        if ( !in_array( 'fees', $exclude ) ) {
            $fees = (float) $this->get_fees();
            $total += $fees;
        }
        if ( !in_array( 'shipping', $exclude ) ) {
            $shipping = (float) $this->get_shipping();
            $total += $shipping;
        }
        if ( !in_array( 'tax', $exclude ) ) {
            $tax = (float) $this->get_tax();
            $total += $tax;
        }

        if ( $total < 0 ) {
            $total = 0;
        }

        $this->total = (float) apply_filters( 'sunshine_get_cart_total', $total, $this );

        return $this->total;
    }

    public function get_total_formatted( $where = '' ) {
        $total = $this->get_total();
        return sunshine_price( $total, true );
    }

    public function update_item_quantity( $key, $qty ) {
        if ( array_key_exists( $key, $this->cart ) ) {
            $this->cart[ $key ]['qty'] = intval( $qty );
            $this->update_cart();
        }
    }

    public function has_image( $image_id ) {
        if ( !$this->is_empty() ) {
            foreach ( $this->cart as $item ) {
                if ( isset( $item['image_id'] ) && $item['image_id'] == $image_id ) {
                    return true;
                }
            }
        }
        return false;
    }

    /*
    *
    * CHECKOUT
    *
    */

    public function set_checkout_fields() {

        $general_fields = SPC()->get_option( 'general_fields' );

        // Get allowed shipping methods from the start so dlivery method section works
        $active_shipping_methods = sunshine_get_allowed_shipping_methods();
        $shipping_methods = array();
        if ( $active_shipping_methods ) {
            foreach ( $active_shipping_methods as $instance_id => $active_shipping_method ) {
                $this_shipping_method = sunshine_get_shipping_method_by_instance( $instance_id );
                $label = $this_shipping_method->get_name();
                $price_html = '';
                $price = $this_shipping_method->get_price();
                $price_html = '<span class="sunshine--checkout--shipping-method--price" data-price="' . esc_attr( $price ) . '">' . sunshine_price( $price ) . '</span>';
                $description_html = '';
                $description = $this_shipping_method->get_description();
                if ( $description ) {
                    $description_html = '<span class="sunshine--checkout--shipping-method--description">' . $description . '</span>';
                }
                $shipping_methods[ $instance_id ] = $label . $price_html . $description_html;
            }
        }

        $fields['contact'] = array(
            'name' => __( 'Contact Information', 'sunshine-photo-cart' ),
            'fields' => array(
                array(
                    'id' => 'email',
                    'type' => 'email',
                    'name' => __( 'Email', 'sunshine-photo-cart' ),
                    'required' => true,
                    'default' => ( $this->get_checkout_data_item( 'email' ) ) ? $this->get_checkout_data_item( 'email' ) : SPC()->customer->get_email(),
                    'autocomplete' => 'email'
                ),
                array(
                    'id' => 'create_account',
                    'type' => 'checkbox',
                    //'name' => __( 'Create an account?', 'sunshine-photo-cart' ),
                    'description' => __( 'Create an account for easier access', 'sunshine-photo-cart' ),
                    'visible' => !is_user_logged_in()
                ),
                array(
                    'id' => 'password',
                    'type' => 'password',
                    'name' => __( 'Password', 'sunshine-photo-cart' ),
                    'required' => true,
                    'visible' => !is_user_logged_in(),
                    'conditions' => array(
                        array(
                            'field' => 'create_account',
                            'compare' => '==',
                            'value' => 'yes',
                            'action' => 'show'
                        )
                    )
                ),
                array(
                    'id' => 'phone',
                    'type' => 'tel',
                    'name' => __( 'Phone', 'sunshine-photo-cart' ),
                    'required' => false,
                    'default' => ( $this->get_checkout_data_item( 'phone' ) ) ? $this->get_checkout_data_item( 'phone' ) : SPC()->customer->get_phone(),
                    'autocomplete' => 'phone',
                    'visible' => ( is_array( $general_fields ) && in_array( 'phone', $general_fields ) )
                ),
                array(
                    'id' => 'vat',
                    'type' => 'text',
                    'name' => __( 'VAT', 'sunshine-photo-cart' ),
                    'default' => $this->get_checkout_data_item( 'vat' ),
                    'visible' => ( is_array( $general_fields ) && in_array( 'vat', $general_fields ) )
                )
            )
        );

        if ( sunshine_checkout_section_completed( 'contact' ) ) {
            $fields['contact']['summary'] = $this->get_checkout_data_item( 'email' );
        }

        $fields['contact'] = apply_filters( 'sunshine_checkout_section_contact', $fields['contact'] );

        $delivery_methods = sunshine_get_delivery_methods();

        $delivery_fields = array();
        if ( !empty( $delivery_methods ) && is_array( $delivery_methods ) ) {
            if ( count( $delivery_methods ) == 1 && array_key_exists( 'shipping', $delivery_methods ) ) {
                $this->hidden_fields['delivery_method'] = 'shipping';
            } else {
                $delivery_methods_options = array();
                foreach ( $delivery_methods as $delivery_method ) {
                    $delivery_methods_options[ $delivery_method['id'] ] = $delivery_method['name'];
                }
                $delivery_fields = array(array(
                    'id' => 'delivery_method',
                    'type' => 'radio',
                    //'name' => __( 'Choose method of delivery', 'sunshine-photo-cart' ),
                    'required' => true,
                    'options' => $delivery_methods_options,
                    'default' => ( !$this->delivery_method ) ? 'shipping' : $this->delivery_method->get_id()
                ));
            }
        }
        $fields['delivery'] = array(
            'active' => true,
            'name' => __( 'Delivery Method', 'sunshine-photo-cart' ),
            'fields' => $delivery_fields
        );

        if ( sunshine_checkout_section_completed( 'delivery' ) && !empty( $this->delivery_method ) ) {
            $fields['delivery']['summary'] = $this->delivery_method->get_name();
        }

        $fields['delivery'] = apply_filters( 'sunshine_checkout_section_delivery', $fields['delivery'] );

        $default_country = SPC()->customer->get_shipping_country();
        if ( $this->get_checkout_data_item( 'shipping_country' ) ) {
            $default_country = $this->get_checkout_data_item( 'shipping_country' );
        }

        if ( $this->needs_shipping() ) {

            $fields['shipping'] = array(
                'active' => false,
                'name' => __( 'Shipping Address', 'sunshine-photo-cart' ),
                'fields' => SPC()->countries->get_address_fields( $default_country, 'shipping_' )
            );

            if ( sunshine_checkout_section_completed( 'shipping' ) ) {
                $fields['shipping']['summary'] = SPC()->countries->get_formatted_address(
                    array(
                        'address1' => $this->get_checkout_data_item( 'shipping_address1' ),
                        'address2' => $this->get_checkout_data_item( 'shipping_address2' ),
                        'city' => $this->get_checkout_data_item( 'shipping_city' ),
                        'state' => $this->get_checkout_data_item( 'shipping_state' ),
                        'postcode' => $this->get_checkout_data_item( 'shipping_postcode' ),
                        'country' => $this->get_checkout_data_item( 'shipping_country' ),
                    ),
                    ', '
                );
            }

            $fields['shipping'] = apply_filters( 'sunshine_checkout_section_shipping', $fields['shipping'] );

            if ( !empty( $shipping_methods ) ) {

                $fields['shipping_method'] = array(
                    'active' => false,
                    'name' => __( 'Shipping Method', 'sunshine-photo-cart' ),
                    'fields' => array(
                        array(
                            'id' => 'shipping_method',
                            'type' => 'radio',
                            //'name' => __( 'Shipping Method', 'sunshine-photo-cart' ),
                            'options' => $shipping_methods
                        )
                    )
                );
                if ( sunshine_checkout_section_completed( 'shipping_method' ) &&  !empty( $this->shipping_method ) ) {
                    $fields['shipping_method']['summary'] = $this->shipping_method->get_name();
                }

            }

        }

        $order_total = $this->get_total();
        $different_billing = $this->get_checkout_data_item( 'different_billing' );

        $payment_methods = SPC()->payment_methods->get_active_payment_methods();
        $payment_methods_options = $needs_billing = array();
        if ( !empty( $payment_methods ) && is_array( $payment_methods ) ) {
            foreach ( $payment_methods as $id => $payment_method_class ) {
                $payment_methods_options[ $id ] = array(
                    'label' => $payment_method_class->get_name(),
                    'description' => $payment_method_class->get_description()
                );
                if ( $payment_method_class->needs_billing_address() ) {
                    $needs_billing[] = $id;
                }
            }
        }

        $fields['payment'] = array(
            'active' => false,
            'name' => __( 'Payment', 'sunshine-photo-cart' ),
            'fields' => array(
                array(
                    'id' => 'payment_method',
                    'type' => 'radio',
                    'required' => ( $order_total > 0 ) ? true : false,
                    //'name' => __( 'Select Payment Method', 'sunshine-photo-cart' ),
                    'options' => $payment_methods_options,
                    'visible' => ( $order_total > 0 ) ? true : false,
                    'default' => ( count( $payment_methods_options ) == 1 ) ? key( $payment_methods_options ) : ''
                )
            )
        );

        $billing_fields = array();
        if ( !empty( $needs_billing ) ) {

            $fields['payment']['fields'][] = array(
                'id' => 'different_billing',
                'type' => 'radio',
                'name' => __( 'Billing Address', 'sunshine-photo-cart' ),
                'options' => array( 'no' => 'Same as shipping address', 'yes' => 'Use a different billing address' ),
                'default' => 'no',
                'conditions' => array(
                    array(
                        'field' => 'payment_method',
                        'compare' => '==',
                        'value' => $needs_billing, //TODO: Figure out how to show these fields based on payment method
                        'action' => 'show',
                        'action_target' => '#sunshine--checkout--field--different_billing'
                    )
                ),
            );

            $billing_address_fields = SPC()->countries->get_address_fields( $default_country, 'billing_' );
            foreach ( $billing_address_fields as &$billing_address_field ) {
                $billing_address_field['conditions'] = array(
                    array(
                        'field' => 'different_billing',
                        'compare' => '==',
                        'value' => 'yes',
                        'action' => 'show',
                    ),
                );
                $fields['payment']['fields'][] = $billing_address_field;
            }

        }

        $credits = SPC()->customer->get_credits();
        if ( $credits ) {
            $credits_field = array(
                'id' => 'use_credits',
                'type' => 'checkbox',
                'name' => sprintf( __( 'Use my credits (%s available)', 'sunshine-photo-cart' ), sunshine_price( $credits ) ),
                'default' => $this->get_checkout_data_item( 'use_credits' )
            );
            array_unshift( $fields['payment']['fields'], $credits_field );
        }

        /*
        $fields['payment']['fields'][] = array(
            'id' => 'notes',
            'type' => 'textarea',
            'name' => __( 'Notes', 'sunshine-photo-cart' ),
            'default' => $this->get_checkout_data_item( 'notes' ),
            'visible' => ( is_array( $general_fields ) && in_array( 'notes', $general_fields ) )
        );
        */

        $after_submit = '';
        if ( SPC()->get_option( 'page_terms' ) ) {
            $after_submit = sprintf( __( 'By submitting this order, you agree to our <a href="%s" target="_blank">%s</a>', 'sunshine-photo-cart' ), get_permalink( SPC()->get_option( 'page_terms' ) ), get_the_title( SPC()->get_option( 'page_terms' ) ) );
        }
        $fields['payment']['fields'][] = array(
            'id' => 'submit',
            'type' => 'submit',
            'name' => sprintf( __( 'Submit Order & Pay %s', 'sunshine-photo-cart' ), '<span class="sunshine-total">' . SPC()->cart->get_total_formatted( 'checkout fields' ) . '</span>' ),
            'after' => $after_submit
        );

        $fields['payment'] = apply_filters( 'sunshine_checkout_section_payment', $fields['payment'] );

        $this->fields = apply_filters( 'sunshine_checkout_fields', $fields );

    }

    public function show_checkout_fields() {

        if ( empty( $this->fields ) ) {
            return;
        }

        // If no specific section set in URL, get the first one
        if ( empty( $this->active_section ) ) {
            $this->active_section = key( $this->fields );
        }

        $i = 1;
        foreach ( $this->fields as $section_id => $section ) {
            if ( empty( $section['fields'] ) ) {
                continue;
            }

            $classes = array();
            if ( $section_id == $this->active_section ) {
                $classes[] = 'sunshine--checkout--section-active';
            }
            $summary = '';
            if ( sunshine_checkout_section_completed( $section_id ) && $section_id != $this->active_section ) {
                $classes[] = 'sunshine--checkout--section-completed';
                if ( !empty( $section['summary'] ) ) {
                    $summary = '<div class="sunshine--checkout--section-summary">' . $section['summary'] . '</div>';
                }
            }
            echo '<fieldset id="sunshine--checkout--' . esc_attr( $section_id ) . '" class="' . join( ' ', $classes ) . '">';
            echo '<legend>' . $section['name'] . '<a href="' . add_query_arg( 'section', $section_id, sunshine_url( 'checkout' ) ) . '" class="sunshine--checkout--section-edit" data-section="' . esc_attr( $section_id ) . '">' . __( 'Edit', 'sunshine-photo-cart' ) . '</a></legend>';
            echo $summary;
            if ( $section_id == $this->active_section ) {
                echo '<input type="hidden" name="sunshine_checkout_section" value="' . esc_attr( $section_id ) . '" />';
                echo '<div class="sunshine--checkout--fields">';
                foreach ( $section['fields'] as $id => $field ) {
                    if ( empty( $field['id'] ) ) {
                        continue;
                    }
                    $this->show_checkout_field( $field['id'], $field );
                }
                echo '</div>';
                if ( $i < count( $this->fields ) ) {
                    echo '<div id="sunshine--checkout--' . esc_attr( $section_id ) . '-button-step" class="sunshine--checkout--section-button">';
                    echo '<button type="submit" class="sunshine-button" data-section="' . esc_attr( $section_id ) . '">' . __( 'Next Step', 'sunshine-photo-cart' ) . '</button>';
                    echo '</div>';
                }
            }
            echo '</fieldset>';
            $i++;
        }

        do_action( 'sunshine_checkout_after_' . $section_id );

        wp_nonce_field( 'sunshine_checkout', 'sunshine_checkout' );

    }


    public function get_checkout_field_html( $id, $field ) {

        if ( isset( $field['visible'] ) && !$field['visible'] ) {
            return;
        }

        $value = '';

        $checkout_data_value = $this->get_checkout_data_item( $id );
        if ( !empty( $checkout_data_value ) ) {
            $value = $checkout_data_value;
        }

        if ( !empty( $field['default'] ) ) {
            $value = $field['default'];
        }

        $defaults = array(
            'name' => '',
            'description' => '',
            'type' => '',
            'min' => '',
            'max' => '',
            'step' => '',
            'default' => '',
            'placeholder' => '',
            'select2' => false,
            'multiple' => false,
            'options' => array(),
            'before' => '',
            'after' => '',
            'html' => '',
            'required' => false
        );
        $field = wp_parse_args( $field, $defaults );

        $html = '';

        switch( $field['type'] ) {

            case 'legend':
                $html .= '<legend>' . esc_html( $field['name'] ) . '</legend>';
            break;

            case 'email':
            case 'tel':
            case 'text':
            case 'password':
                $html .= '<input ' . ( ( $field['required'] ) ? 'required="required"' : '' ) . ' id="' . esc_attr( $field['id'] ) . '" type="' . $field['type'] . '" name="' . esc_attr( $id ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . esc_attr( $value ) . '" />' . "\n";
            break;

            case 'number':
                $html .= '<input ' . ( ( $field['required'] ) ? 'required="required"' : '' ) . ' id="' . esc_attr( $field['id'] ) . '" type="' . $field['type'] . '" name="' . esc_attr( $id ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" min="' . esc_attr( $field['min'] ) . '" max="' . esc_attr( $field['max'] ) . '" step="' . esc_attr( $field['step'] ) . '" value="' . esc_attr( $value ) . '" />' . "\n";
            break;

            case 'textarea':
                $html .= '<textarea ' . ( ( $field['required'] ) ? 'required="required"' : '' ) . ' id="' . esc_attr( $field['id'] ) . '" rows="5" cols="50" name="' . esc_attr( $id ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '">' . wp_kses_post( $value ) . '</textarea>'. "\n";
            break;

            case 'checkbox':
                $html .= '<label><input ' . ( ( $field['required'] ) ? 'required="required"' : '' ) . ' id="' . esc_attr( $field['id'] ) . '" type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $id ) . '" value="yes" ' . checked( $value, 1, false ) . '/>' . $field['name'] . '</label>' . "\n";
            break;

            case 'checkbox_multi':
                foreach( $field['options'] as $k => $v ) {
                    $html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '" class="sunshine--checkout--field--checkbox-option"><input type="checkbox" ' . checked( ( is_array( $value ) && in_array( $k, $value ) ), true, false ) . ' name="' . esc_attr( $field['id'] ) . '[]" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . wp_kses_post( $v ) . '</label>';
                    $html = apply_filters( 'sunshine_checkout_field_' . $field['id'] . '_' . $k, $html );
                }
            break;

            case 'radio':
                foreach( $field['options'] as $k => $v ) {
                    $html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '" class="sunshine--checkout--field--radio-option"><input type="radio" ' . checked( $k, $value, false ) . ' name="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ';
                    if ( is_array( $v ) ) {
                        if ( !empty( $v['label'] ) ) {
                            $html .= wp_kses_post( $v['label'] );
                        }
                        if ( !empty( $v['description'] ) ) {
                            $html .= '<span class="sunshine--checkout--label-description">' . $v['description'] . '</span>';
                        }
                    } else {
                        $html .= wp_kses_post( $v );
                    }
                    $html .= '</label>';
                    $html = apply_filters( 'sunshine_checkout_field_' . $field['id'] . '_' . $k, $html );
                }
            break;

            case 'select':
                $html .= '<select ' . ( ( $field['required'] ) ? 'required="required"' : '' ) . ' name="' . esc_attr( $id ) . '" id="' . esc_attr( $field['id'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '">';
                foreach( $field['options'] as $k => $v ) {
                    $html .= '<option ' . selected( ( $value == $k ) || ( is_array( $value ) && in_array( $k, $value ) ), true, false ) . ' value="' . esc_attr( $k ) . '">' . wp_kses_post( $v ) . '</option>';
                }
                $html .= '</select> ';
                if ( $field['select2'] ) {
                    $html .= '
                    <script type="text/javascript">jQuery(function () {
                        jQuery("#' . esc_js( $field['id'] ) . '").select2({ width: "350px", placeholder: "' . esc_js( $field['placeholder'] ). '" });
                        });</script>';
                }
            break;

            case 'country':
                $html .= '<select ' . ( ( $field['required'] ) ? 'required="required"' : '' ) . ' name="' . esc_attr( $id ) . '" id="' . esc_attr( $field['id'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '">';
                foreach( $field['options'] as $k => $v ) {
                    $html .= '<option ' . selected( ( $value == $k ) || ( is_array( $value ) && in_array( $k, $value ) ), true, false ) . ' value="' . esc_attr( $k ) . '">' . wp_kses_post( $v ) . '</option>';
                }
                $html .= '</select> ';
                if ( $field['select2'] ) {
                    $html .= '
                    <script type="text/javascript">jQuery(function () {
                        jQuery("#' . esc_js( $field['id'] ) . '").select2({ width: "350px", placeholder: "' . esc_js( $field['placeholder'] ). '" });
                        });</script>';
                }
            break;

            case 'state':
                if ( empty( $field['options'] ) ) {
                    $html .= '<input ' . ( ( $field['required'] ) ? 'required="required"' : '' ) . ' id="' . esc_attr( $field['id'] ) . '" type="text" name="' . esc_attr( $id ) . '" value="' . esc_attr( $value ) . '" />' . "\n";
                } else {
                    $html .= '<select ' . ( ( $field['required'] ) ? 'required="required"' : '' ) . ' name="' . esc_attr( $id ) . '" id="' . esc_attr( $field['id'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '">';
                    foreach( $field['options'] as $k => $v ) {
                        $html .= '<option ' . selected( ( $value == $k ) || ( is_array( $value ) && in_array( $k, $value ) ), true, false ) . ' value="' . esc_attr( $k ) . '">' . wp_kses_post( $v ) . '</option>';
                    }
                    $html .= '</select> ';
                    if ( $field['select2'] ) {
                        $html .= '
                        <script type="text/javascript">jQuery(function () {
                            jQuery("#' . esc_js( $field['id'] ) . '").select2({ width: "350px", placeholder: "' . esc_js( $field['placeholder'] ). '" });
                            });</script>';
                    }
                }
            break;

            case 'select_multi':
                $html .= '<select ' . ( ( $field['required'] ) ? 'required="required"' : '' ) . ' name="' . esc_attr( $id ) . '[]" id="' . esc_attr( $field['id'] ) . '" multiple="multiple">';
                foreach( $field['options'] as $k => $v ) {
                    $html .= '<option ' . selected( in_array( $k, $value ), true, false ) . ' value="' . esc_attr( $k ) . '" />' . wp_kses_post( $v ) . '</label> ';
                }
                $html .= '</select> ';
            break;

            case 'html':
                $html .= $field['html'];
            break;

            case 'submit':
                $html .= '<button id="' . esc_attr( $id ) . '" type="submit" class="sunshine-button">' . $field['name'] . '</button>';
            break;

            default:
                do_action( 'sunshine_checkout_field_' . $field['type'] . '_display' );
            break;

        }

        $required = '';
        if ( isset( $field['required'] ) && $field['required'] ) {
            $required = '<abbr title="required" aria-label="required">' . apply_filters( 'sunshine_checkout_field_required_symbol', '*' ) . '</abbr>';
        }

        // Add label
        switch ( $field['type'] ) {

            case 'radio':
            case 'checkbox_multi':
                $html = '<span class="sunshine--checkout--field--name">' . $field['name'] . '</span>' . $html;
            break;

            case 'legend':
            case 'header':
            case 'submit':
                $html = $html;
            break;

            case 'checkbox':
                break;
            case 'checkboxXXXX':
                $html = '<span class="sunshine--checkout--field--name">' . $field['name'] . '</span><label for="' . esc_attr( $field['id'] ) . '"><span class="sunshine--checkout--field--name">' . $html . esc_html( $field['description'] ) . $required . '</span></label>';
            break;

            default:
                $html = '<label for="' . esc_attr( $field['id'] ) . '"><span class="sunshine--checkout--field--name">' . esc_html( $field['name'] ) . $required . '</span></label>' . $html;
            break;

        }

        // Add description
        switch ( $field['type'] ) {

            case 'radio':
            case 'checkbox_multi':
            case 'legend':
                if ( !empty( $field['description'] ) ) {
                    $html .= '<span class="sunshine--checkout--field-description">' . $field['description'] . '</span>';
                }
            break;

            case 'checkbox':
                break;

            default:
                if ( !empty( $field['description'] ) ) {
                    $html .= '<br /><span class="sunshine--checkout--field-description">' . $field['description'] . '</span>' . "\n";
                }
            break;
        }

        $html .= apply_filters( 'sunshine_checkout_field_extra_' . $field['id'], '' );

        $size = ( isset( $field['size'] ) && in_array( $field['size'], array( 'full', 'half', 'third' ) ) ) ? $field['size'] : 'full';
        $show = ( isset( $field['show'] ) && !$field['show'] ) ? ' style="display: none;"' : '';

        $classes = array(
            'sunshine--checkout--field-' . $field['type'],
            'sunshine--checkout--field-' . $size
        );
        if ( !empty( $this->errors[ $id ] ) ) {
            $classes[] = 'sunshine--checkout--field-has-error';
            $html .= '<div class="sunshine--checkout--field-error">' . wp_kses_post( $this->errors[ $id ] ) . '</div>';
        }

        /*
        if ( isset( $field['visible'] ) && !$field['visible'] ) {
            $classes[] = 'sunshine--checkout--field-hidden';
        }
        */

        if ( isset( $field['required'] ) && $field['required'] ) {
            $classes[] = 'sunshine--checkout--field-required';
        }

        $before = ( !empty( $field['before'] ) ) ? '<span class="sunshine--checkout--field-before">' . wp_kses_post( $field['before'] ) . '</span>' : '';
        $after = ( !empty( $field['after'] ) ) ? '<span class="sunshine--checkout--field-after">' . wp_kses_post( $field['after'] ) . '</span>' : '';

        $html = '<div class="sunshine--checkout--field ' . esc_attr( join( ' ', $classes ) ) . '" id="sunshine--checkout--field--' . esc_attr( $field['id'] ) . '" data-type="' . esc_attr( $field['type'] ) . '"' . $show . '>' . $before . $html . $after . '</div>';

        return $html;

    }

    public function show_checkout_field( $id, $field ) {
        echo $this->get_checkout_field_html( $id, $field );
    }

    public function process_section( $section, $data ) {

        $this->active_section = sanitize_key( $section );

        // Validate all fields in this section
        if ( empty( $this->fields[ $this->active_section ] ) || empty( $this->fields[ $this->active_section ]['fields'] ) ) {
            SPC()->notices->add( __( 'Invalid checkout section', 'sunshine-photo-cart' ) );
            return;
        }

        $errors = array();

        foreach ( $this->fields[ $this->active_section ]['fields'] as $field ) {

            if ( empty( $field['type'] ) || $field['type'] == 'submit' || $field['type'] == 'legend' || empty( $data[ $field['id'] ] ) ) {
                continue;
            }

            $value = !empty( $data[ $field['id'] ] ) ? $data[ $field['id'] ] : '';

            // Save this value to checkout session data (even if invalid, so when we reload form it can prepopulate still)
            $this->set_checkout_data_item( $field['id'], $value );

           // Check conditional state, is this even showing to the user to truly make it required?
           if ( !empty( $field['conditions'] ) ) {
               foreach ( $field['conditions'] as $condition ) {
                   $comparison_field = $this->get_checkout_field( $condition['field'] );
                   $comparison_field_value = !empty( $values[ $condition['field'] ] ) ? $values[ $condition['field'] ] : '';
                   $comparison_state = sunshine_value_comparison( $comparison_field_value, $condition['value'], $condition['compare'] );

                   if ( ( $comparison_state && $condition['action'] == 'show' ) || ( !$comparison_state && $condition['action'] == 'hide' ) ) {
                       // This field is shown and thus subject to additional validation so it it go through
                   } else {
                       // Field not being shown so don't validate, go to next field
                       continue 2;
                   }
               }
           }

           if ( isset( $field['required'] ) && $field['required'] && empty( $value ) ) {
               $this->add_error( $field['id'], __( 'Field is required', 'sunshine-photo-cart' ) );
               continue;
           }

           if ( empty( $value ) ) { // No need to try and validate any further if nothing passed
               continue;
           }

           switch ( $field['type'] ) {
               case 'email':
                   if ( !is_email( $value ) ) {
                       $this->add_error( $field['id'], __( 'Invalid email', 'sunshine-photo-cart' ) );
                   }
                   break;
           }

           // Let hooks take a stab at validation if we still have no errors yet
           if ( empty( $this->errors[ $field['id'] ] ) ) {
               $error = apply_filters( 'sunshine_validate_' . $field['type'], false, $value, $field );
               if ( $error ) {
                   $this->errors[ $field['id'] ] = $error;
               }
           }

       }

       // This is where other add-ons can hook in, likely payment methods
       do_action( 'sunshine_checkout_validation' );

       // Set section to completed via session
       end( $this->fields );
       if ( $this->active_section != key( $this->fields ) && !sunshine_checkout_section_completed( $this->active_section ) ) {
           $checkout_sections_completed = SPC()->session->get( 'checkout_sections_completed' );
           $checkout_sections_completed[] = $this->active_section;
           SPC()->session->set( 'checkout_sections_completed', $checkout_sections_completed );
       }

       $this->update();

       return true;

    }

    public function process_payment() {

        if ( $this->active_section != 'payment' || !isset( $_POST['sunshine_checkout'] ) || !wp_verify_nonce( $_POST['sunshine_checkout'], 'sunshine_checkout' ) ) {
            return false;
        }

        // If we have any errors then we do not process payment
        if ( $this->has_errors() ) {
            SPC()->log( 'Checkout has errors and cannot process payment: ' . json_encode( $this->get_errors() ) );
            return false;
        }

        do_action( 'sunshine_checkout_process_payment', $this );
        if ( $this->get_checkout_data_item( 'payment_method' ) ) {
            do_action( 'sunshine_checkout_process_payment_' . $this->get_checkout_data_item( 'payment_method' ), $this );
        }

        $order = $this->create_order();
        if ( $order ) {
            $url = apply_filters( 'sunshine_checkout_redirect', $order->get_permalink() );
            SPC()->log( 'Checkout created new order and is redirecting to ' . $url );
            wp_redirect( $url );
            exit;
        } else {
            // There was some kind of issue creating the order so just return and show last section again
            SPC()->log( 'Checkout had error and could not create order' );
            return false;
        }


    }

    public function get_checkout_field( $field_id ) {
        foreach ( $this->fields as $section_id => $section ) {
            foreach ( $section['fields'] as $field ) {
                if ( $field['id'] == $field_id ) {
                    return $field;
                }
            }
        }
        return false;
    }

    public function set_checkout_data_item( $key, $value ) {
        $this->data[ sanitize_key( $key ) ] = sanitize_text_field( $value );
        SPC()->session->set( 'checkout_data', $this->data );
    }

    public function get_checkout_data() {
        return $this->data;
    }
    public function get_checkout_data_item( $key ) {
        if ( isset( $this->data[ $key ] ) ) {
            return $this->data[ $key ];
        }
        return false;
    }


    public function get_checkout_fields() {
        return $this->fields;
    }

    public function create_order( $override_data = array() ) {

        $order = new SPC_Order();

        if ( is_user_logged_in() ) {
            $order->update_meta_value( 'customer_id', get_current_user_id() );
        }

        // Setting all various meta data including delivery method, shipping method, payment method
        $data = $this->get_checkout_data();
        $data = wp_parse_args( $override_data, $data );

        foreach ( $data as $key => $value ) {
            $order->update_meta_value( $key, $value );
            SPC()->customer->update_meta( $key, $value );
        }

        $order->set_cart( $this->get_cart() );

        $order->set_subtotal( $this->get_subtotal() );
        $order->set_shipping( $this->get_shipping() );
        $order->set_tax( $this->get_tax() );
        $order->set_discount( $this->get_discount() );
        $order->set_discounts( $this->get_discounts() );
        $order->set_credits( $this->get_credits_applied() );
        $order->set_total( $this->get_total() );
        $order->set_mode( apply_filters( 'sunshine_checkout_create_order_mode', 'live', $order ) );

        $order->save();

        if ( $order->get_id() ) {
            // Clear checkout data from session data
            SPC()->session->set( 'checkout_data', '' );
            SPC()->session->set( 'checkout_sections_completed', '' );
            SPC()->cart->empty_cart();

            do_action( 'sunshine_checkout_create_order', $order, $data );
            return $order;
        }

        return false;

    }

    public function get_active_section() {
        return $this->active_section;
    }

    function check_expirations() {

        if ( !empty( $this->current_image ) && $this->current_gallery->is_expired() ) { // If looking at image but gallery is expired, redirect to gallery
            wp_redirect( get_permalink( $this->current_gallery->get_permalink() ) );
            exit;
        } elseif ( is_page( SPC()->get_option( 'page_cart' ) ) ) { // Remove items from cart if gallery is expired
            $cart = SPC()->cart->get_cart();
            $removed_items = false;
            if ( !empty( $cart ) ) {
                foreach ( $cart as $key => $item ) {
                    if ( !empty( $item['image_id'] ) ) {
                        $image = new SPC_Image( $item['image_id'] );
                        if ( empty( $image->get_id() ) ) {
                            $this->remove_item( $key );
                            $removed_items = true;
                            continue;
                        }
                        $gallery = $image->get_gallery();
                        if ( empty( $gallery ) || $gallery->is_expired() ) {
                            $this->remove_item( $key );
                            $removed_items = true;
                        }
                    }
                }
            }
            if ( $removed_items ) {
                SPC()->notices->add( __( 'Images in your cart have been removed because they are no longer available', 'sunshine-photo-cart' ) );
                SPC()->log( 'Images in cart removed because they were no longer available' );
                wp_redirect( get_permalink( SPC()->get_option( 'page_cart' ) ) );
                exit;
            }
        }

    }


}
