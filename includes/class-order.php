<?php
class SPC_Order extends Sunshine_Data {

    protected $post_type = 'sunshine-order';
    protected $status;
    protected $cart = array();
    protected $meta = array(
        'cart' => array(),

        'currency' => '',
        'display_price' => '',

        'discounts' => array(),
        'discount' => 0,
        'shipping' => 0,
        'tax' => 0,
        'subtotal' => 0,
        'total' => 0,

        'customer_id'          => 0,
        'order_key'            => '',

        'billing_first_name' => '',
        'billing_last_name'  => '',
        'billing_address1'  => '',
        'billing_address2'  => '',
        'billing_city'       => '',
        'billing_state'      => '',
        'billing_postcode'        => '',
        'billing_country'    => '',
        'shipping_first_name' => '',
        'shipping_last_name'  => '',
        'shipping_address1'  => '',
        'shipping_address2'  => '',
        'shipping_city'       => '',
        'shipping_state'      => '',
        'shipping_postcode'        => '',
        'shipping_country'    => '',

        'delivery_method'       => '',
        'delivery_method_name' => '',
        'shipping_method'       => '',
        'shipping_method_name' => '',
        'payment_method'       => '',
        'payment_method_name' => '',
        'transaction_id' => '',

        //'created_via'          => '',
        'notes'                => '',

        'mode' => 'live'
    );

    public function __construct( $object = '' ) {

        if ( is_numeric( $object ) && $object > 0 ) {
            $post = get_post( $object );
            if ( empty( $post ) || $post->post_type != $this->post_type ) { return false; }
            $this->id = $post->ID;
            $this->data = $post;
        } elseif ( is_a( $object, 'WP_Post' ) ) {
            if ( $object->post_type != $this->post_type ) { return false; }
            $this->id = $object->ID;
            $this->data = $object;
        }

        if ( $this->id > 0 ) {
            $this->set_meta_data();
        }

    }

    public function get_status() {
        if ( empty( $this->status ) ) {
            $current_status = get_the_terms( $this->get_id(), 'sunshine-order-status' );
            if ( !empty( $current_status ) ) {
                $this->status = $current_status[0]->slug;
            }
        }
        return $this->status;
    }

    public function get_status_object() {
        if ( empty( $this->status ) ) {
            $this->get_status();
        }
        return new SPC_Order_Status( $this->status );
    }

    public function get_status_name() {
        $status = $this->get_status_object();
        if ( $status ) {
            return $status->get_name();
        }
        return false;
    }

    public function get_status_description() {
        $status = $this->get_status_object();
        if ( $status ) {
            return $status->get_description();
        }
        return false;
    }

    public function set_status( $new_status, $custom_log = '' ) {
        if ( sunshine_order_status_is_valid( $new_status ) && $this->status != $new_status ) {
            do_action( 'sunshine_status_change_' . $new_status, $this->get_id() );
            if ( !empty( $this->status ) ) {
                do_action( 'sunshine_status_change_' . $this->status . '_to_' . $new_status, $this->get_id() );
                $log = sprintf( __( 'Status change from %s to %s', 'sunshine-photo-cart' ), $this->status, $new_status );
            } else {
                $log = sprintf( __( 'Status change to %s', 'sunshine-photo-cart' ), $new_status );
            }
            wp_set_object_terms( $this->get_id(), $new_status, 'sunshine-order-status' );
            $this->add_log( ( $custom_log ) ? $custom_log : $log );
            $this->status = get_term_by( 'slug', $new_status, 'sunshine-order-status' );
            return true;
        }
        return false;
    }

    public function has_status( $status ) {
        $current_status = $this->get_status();
        if ( $current_status && $current_status->slug == $status ) {
            return true;
        }
        return false;
    }

    public function get_order_key() {
        // TODO: Do we use this? I think original idea was for guest orders to view receipt page
        return $this->get_meta_value( 'order_key' );
    }

    public function add_log( $message, $user_id = 0 ) {
        if ( ! $this->get_id() ) {
			return 0;
		}

        if ( $user_id ) {
            $user                 = get_user_by( 'id', $user_id );
            $comment_author       = $user->display_name;
			$comment_author_email = $user->user_email;
        } elseif ( is_user_logged_in() && current_user_can( 'sunshine_manage_options' ) ) {
			$user                 = get_user_by( 'id', get_current_user_id() );
			$comment_author       = $user->display_name;
			$comment_author_email = $user->user_email;
		} else {
			$comment_author        = __( 'Sunshine Photo Cart', 'sunshine-photo-cart' );
			$comment_author_email  = strtolower( __( 'Sunshine Photo Cart', 'sunshine-photo-cart' ) ) . '@';
			$comment_author_email .= isset( $_SERVER['HTTP_HOST'] ) ? str_replace( 'www.', '', sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) ) : 'noreply.com'; // WPCS: input var ok.
			$comment_author_email  = sanitize_email( $comment_author_email );
		}
		$commentdata = apply_filters(
			'sunshine_order_note',
			array(
				'comment_post_ID'      => $this->get_id(),
				'comment_author'       => $comment_author,
				'comment_author_email' => $comment_author_email,
				'comment_author_url'   => '',
				'comment_content'      => $message,
				'comment_agent'        => 'SunshinePhotoCart',
				'comment_type'         => 'sunshine_order_log',
				'comment_parent'       => 0,
				'comment_approved'     => 1,
			),
			$this->get_id()
		);

		$comment_id = wp_insert_comment( $commentdata );

		do_action( 'sunshine_order_note_added', $comment_id, $this );

        SPC()->log( 'Note added to ' . $this->get_name() . ': ' . $message );

		return $comment_id;
    }

    public function get_log() {
        $log = get_comments( array( 'post_id' => $this->get_id(), 'type' => 'sunshine_order_log' ) );
        return $log;
    }

    public function get_name() {
        $this->name = sprintf( __( 'Order #%s', 'sunshine-photo-cart' ), $this->get_id() );
        return apply_filters( 'sunshine_order_name', $this->name, $this );
    }

    function get_customer_id() {
        return $this->get_meta_value( 'customer_id' );
    }
    function get_user_id() {
        return $this->get_meta_value( 'customer_id' );
    }

    // Return a SPC_Customer for user tied to this order
    function get_customer() {
        $customer_id = $this->get_customer_id();
        if ( $customer_id ) {
            return new SPC_Customer( $customer_id );
        }
        return false;
    }

    // Return a WP_User for the user tied to this order
    public function get_user() {
		return $this->get_user_id() ? get_user_by( 'id', $this->get_user_id() ) : false;
	}

    public function get_customer_name() {
        return $this->get_customer_first_name() . ' ' . $this->get_customer_last_name();
    }
    public function get_customer_first_name() {
        // Check for customer
        $customer = $this->get_customer();
        if ( $customer ) {
            return $customer->get_first_name();
        }

        // Check for shipping name
        if ( $this->get_shipping_first_name() ) {
            return $this->get_shipping_first_name();
        }

        // Check for billing name
        if ( $this->get_billing_first_name() ) {
            return $this->get_billing_first_name();
        }

        return false;
    }

    public function get_customer_last_name() {
        // Check for customer
        $customer = $this->get_customer();
        if ( $customer ) {
            return $customer->get_last_name();
        }

        // Check for shipping name
        if ( $this->get_shipping_last_name() ) {
            return $this->get_shipping_last_name();
        }

        // Check for billing name
        if ( $this->get_billing_last_name() ) {
            return $this->get_billing_last_name();
        }

        return false;
    }


    public function get_permalink() {
        $url = get_permalink( $this->get_id() );
        $url = add_query_arg( 'order_key', $this->get_meta_value( 'order_key' ), $url );
        return $url;
    }

    public function get_phone() {
        return $this->get_meta_value( 'phone' );
    }

    public function get_email() {
        return $this->get_meta_value( 'email' );
    }

    public function get_billing_first_name() {
        return $this->get_meta_value( 'billing_first_name' );
    }

    public function get_billing_last_name() {
        return $this->get_meta_value( 'billing_last_name' );
    }

    public function get_billing_address1() {
        return $this->get_meta_value( 'billing_address1' );
    }

    public function get_billing_address2() {
        return $this->get_meta_value( 'billing_address2' );
    }

    public function get_billing_city() {
        return $this->get_meta_value( 'billing_city' );
    }

    public function get_billing_state() {
        return $this->get_meta_value( 'billing_state' );
    }

    public function get_billing_postcode() {
        return $this->get_meta_value( 'billing_postcode' );
    }

    public function get_billing_country() {
        return $this->get_meta_value( 'billing_country' );
    }

    public function get_billing_address_formatted() {
        $args = array(
            'first_name' => $this->get_billing_first_name(),
            'last_name' => $this->get_billing_last_name(),
            'address1' => $this->get_billing_address1(),
            'address2' => $this->get_billing_address2(),
            'city' => $this->get_billing_city(),
            'state' => $this->get_billing_state(),
            'postcode' => $this->get_billing_postcode(),
            'country' => $this->get_billing_country(),
        );
        return SPC()->countries->get_formatted_address( $args );
    }

    public function has_billing_address() {
        if ( $this->get_billing_address1() ) {
            return true;
        }
        return false;
    }


    public function get_shipping_first_name() {
        return $this->get_meta_value( 'shipping_first_name' );
    }

    public function get_shipping_last_name() {
        return $this->get_meta_value( 'shipping_last_name' );
    }

    public function get_shipping_address1() {
        return $this->get_meta_value( 'shipping_address1' );
    }

    public function get_shipping_address2() {
        return $this->get_meta_value( 'shipping_address2' );
    }

    public function get_shipping_city() {
        return $this->get_meta_value( 'shipping_city' );
    }

    public function get_shipping_state() {
        return $this->get_meta_value( 'shipping_state' );
    }

    public function get_shipping_postcode() {
        return $this->get_meta_value( 'shipping_postcode' );
    }

    public function get_shipping_country() {
        return $this->get_meta_value( 'shipping_country' );
    }

    public function get_shipping_address_formatted() {
        $args = array(
            'first_name' => $this->get_shipping_first_name(),
            'last_name' => $this->get_shipping_last_name(),
            'address1' => $this->get_shipping_address1(),
            'address2' => $this->get_shipping_address2(),
            'city' => $this->get_shipping_city(),
            'state' => $this->get_shipping_state(),
            'postcode' => $this->get_shipping_postcode(),
            'country' => $this->get_shipping_country(),
        );
        return SPC()->countries->get_formatted_address( $args );
    }

    public function has_shipping_address() {
        $delivery_method = $this->get_delivery_method();
        if ( $delivery_method == 'shipping' || $this->get_shipping_address1() ) {
            return true;
        }
        return false;
    }

    public function get_subtotal() {
        return $this->get_meta_value( 'subtotal' );
    }
    public function get_subtotal_formatted() {
        return sunshine_price( $this->get_subtotal() );
    }
    public function set_subtotal( $value ) {
        $this->meta['subtotal'] = floatval( $value );
    }

    public function get_total() {
        return $this->get_meta_value( 'total' );
    }
    public function get_total_formatted() {
        return sunshine_price( $this->get_total(), true );
    }
    public function set_total( $value ) {
        $this->meta['total'] = floatval( $value );
    }


    public function get_credits() {
        return $this->get_meta_value( 'credits' );
    }
    public function get_credits_formatted() {
        return sunshine_price( $this->get_credits() );
    }
    public function set_credits( $value ) {
        $this->meta['credits'] = floatval( $value );
    }

    public function get_tax() {
        return $this->get_meta_value( 'tax' );
    }
    public function get_tax_formatted() {
        return sunshine_price( $this->get_tax() );
    }
    public function set_tax( $value ) {
        $this->meta['tax'] = floatval( $value );
    }

    public function get_discounts() {
        return $this->get_meta_value( 'discounts' );
    }
    public function set_discounts( $value ) {
        $this->meta['discounts'] = $value;
    }

    public function get_discount() {
        return $this->get_meta_value( 'discount' );
    }
    public function get_discount_formatted() {
        return sunshine_price( $this->get_discount() );
    }
    public function set_discount( $value ) {
        $this->meta['discount'] = floatval( $value );
    }

    public function get_delivery_method() {
        return $this->get_meta_value( 'delivery_method' );
    }
    public function get_delivery_method_name() {
        return $this->get_meta_value( 'delivery_method_name' );
    }
    public function set_delivery_method( $value ) {
        $this->meta['delivery_method'] = $value;
    }

    public function get_shipping() {
        return $this->get_meta_value( 'shipping' );
    }
    public function get_shipping_formatted() {
        return sunshine_price( $this->get_shipping() );
    }
    public function set_shipping( $value ) {
        $this->meta['shipping'] = floatval( $value );
    }

    public function get_shipping_method() {
        return $this->get_meta_value( 'shipping_method' );
    }
    public function get_shipping_method_name() {
        return $this->get_meta_value( 'shipping_method_name' );
    }
    public function set_shipping_method( $value ) {
        $this->meta['shipping_method'] = $value;
    }

    public function get_payment_method() {
        return $this->get_meta_value( 'payment_method' );
    }
    public function get_payment_method_name() {
        return $this->get_meta_value( 'payment_method_name' );
    }
    public function set_payment_method( $value ) {
        $this->meta['payment_method'] = $value;
    }

    public function get_currency() {
        return $this->get_meta_value( 'currency' );
    }
    public function set_currency( $value ) {
        $this->meta['currency'] = $value;
    }

    public function get_date( $format = '' ) {
        if ( empty( $format ) ) {
            $format = get_option( 'date_format' ) . ' @ ' . get_option( 'time_format' );
        }
        return date( $format, strtotime( $this->data->post_date ) );
    }

    public function get_cart() {
        $cart = $this->get_meta_value( 'cart' );
        if ( empty( $cart ) ) {
            return false;
        }
        $cart_items = array();
        foreach ( $cart as $item ) {
            $cart_items[] = new SPC_Order_Item( $item );
        }
        return $cart_items;
    }

    public function set_cart( $cart ) {
        $this->cart = $cart;
        $this->update_meta_value( 'cart', $cart );
    }

    public function get_admin_notes() {
        return $this->get_meta_value( 'admin_notes' );
    }

    public function get_vat() {
        return $this->get_meta_value( 'vat' );
    }
    public function set_vat( $value ) {
        $this->meta['vat'] = $value;
    }

    function get_transaction_id() {
        return $this->get_meta_value( 'transaction_id' );
    }

    public function get_mode() {
        return $this->meta['mode'];
    }
    public function set_mode( $value ) {
        $this->meta['mode'] = $value;
    }

    function get_galleries() {
        $cart = $this->get_cart();
        if ( !empty( $cart ) ) {
            $galleries = array();
            foreach ( $cart as $order_item ) {
                if ( !empty( $order_item->get_gallery_id() ) ) {
                    $galleries[] = $order_item->get_gallery_id();
                }
            }
            return $galleries;
        }
        return false;
    }

    function get_invoice_url() {
        return wp_nonce_url( $this->get_permalink(), 'order_invoice_' . $this->get_id(), 'order_invoice' );
    }

    public function notify( $template, $copy_admin = false ) {
        if ( !in_array( $template, array( 'receipt', 'order_status' ) ) ) {
            return false;
        }
        $customer_email = $this->get_email();
        if ( !empty( $customer_email ) ) {
            $args = array(
                'order' => $this,
            );
            $search_replace = array(
                'order_id' => $this->get_id(),
                'order_name' => $this->get_name(),
                'first_name' => $this->get_customer_first_name(),
                'last_name' => $this->get_customer_last_name(),
                'status' => $this->get_status_name(),
            );
            $subject = ( $template == 'receipt' ) ? SPC()->get_option( 'email_subject_order_receipt' ) : SPC()->get_option( 'email_subject_order_status' );
            $email = new SPC_Email( $template, $customer_email, $subject, $args );
            $email->set_search_replace( $search_replace );

            // If we have a customer for this order, set the to name
            $customer = $this->get_customer();
            if ( $customer ) {
                $email->set_to_name( $customer->get_name() );
            }

            // Send email
			$result = $email->send();

            if ( $copy_admin ) {
                /* TODO: Send email to admin user */
            }

            return $result;
        }
    }

    public function create() {

        // Don't let through unless you have data required
        if ( empty( $this->get_payment_method() ) || empty( $this->cart ) ) {
            sunshine_log( $this, 'Did not have enough info to create an order' );
            sunshine_log( $this->get_payment_method(), 'payment method' );
            sunshine_log( $this->get_shipping_method(), 'shipping method' );
            sunshine_log( $this->cart, 'cart' );

            SPC()->log( 'Did not have enough info to create an order' );
            SPC()->log( $this->get_payment_method() );
            SPC()->log( $this->get_shipping_method() );
            SPC()->log( $this->cart );
            return false;
        }

        // Set payment method to free if order total is 0
        if ( $this->get_total() == 0 ) {
            $this->set_payment_method( 'free' );
        }

        $payment_method_id = $this->get_meta_value( 'payment_method' );
        $payment_method = SPC()->payment_methods->get_payment_method_by_id( $payment_method_id );
        $this->update_meta_value( 'payment_method_name', $payment_method->get_name() );

        $delivery_method_id = $this->get_meta_value( 'delivery_method' );
        if ( $delivery_method_id ) {
            $delivery_method = sunshine_get_delivery_method_by_id( $delivery_method_id );
            $this->update_meta_value( 'delivery_method_name', $delivery_method->get_name() );
        }

        $shipping_method_instance = $this->get_meta_value( 'shipping_method' );
        if ( $shipping_method_instance ) {
            $shipping_method = sunshine_get_shipping_method_by_instance( $shipping_method_instance );
            $this->update_meta_value( 'shipping_method_name', $shipping_method->get_name() );
        }

        // Let's populate any missing things with their defaults
        if ( empty( $this->get_meta_value( 'currency' ) ) ) {
            $this->update_meta_value( 'currency', SPC()->get_option( 'currency' ) );
        }
        if ( empty( $this->get_meta_value( 'display_price' ) ) ) {
            $this->update_meta_value( 'display_price', SPC()->get_option( 'display_price' ) );
        }

        if ( empty( $this->get_meta_value( 'order_key' ) ) ) {
            $this->update_meta_value( 'order_key', sunshine_generate_order_key() );
        }

        $order_id = wp_insert_post(array(
            'post_author' => $this->get_meta_value( 'customer_id' ),
            'post_title' => __( 'Order', 'sunshine-photo-cart' ),
            'post_status' => 'publish',
            'post_type' => $this->post_type,
            'comment_status' => 'closed',
            'meta_input' => $this->meta
        ));

        if ( is_wp_error( $order_id ) ){
            return false;
        }

        $this->set_id( $order_id );
        $this->data = get_post( $order_id );

        wp_update_post(array(
            'ID' => $order_id,
            'post_title' => apply_filters( 'sunshine_new_order_title', sprintf( __( 'Order #%s', 'sunshine-photo-cart' ), $order_id ) ),
            'post_name' => $order_id
        ));

        // Set order status
        $order_status_id = apply_filters( 'sunshine_create_order_status', 'pending', $this );
        $order_status = sunshine_get_order_status_by_id( $order_status_id );
        if ( !empty( $order_status ) ) {
            wp_set_object_terms( $order_id, $order_status_id, 'sunshine-order-status' );
            $this->add_log( sprintf( __( 'Order status set to %s', 'sunshine-photo-cart' ), $order_status->get_name() ) );
        }

        $this->notify( 'receipt' );
        //$this->notify_admin();

        //parent::create();

        do_action( 'sunshine_refresh_order_stats' );

        SPC()->log( $this->get_name() . ' created' );

        return $order_id;

    }


}
