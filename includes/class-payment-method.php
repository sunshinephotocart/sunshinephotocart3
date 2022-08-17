<?php
class SPC_Payment_Method {

    public $id;
    protected $active;
    protected $name;
    protected $description;
    protected $class;
    protected $can_be_enabled = true;
    protected $needs_billing_address = false;

    public function __construct() {
        $this->init();
        add_filter( 'sunshine_payment_methods', array( $this, 'register' ) );
        add_filter( 'sunshine_options_payment_method_' . $this->id, array( $this, 'default_options' ), 1 );
        add_filter( 'sunshine_options_payment_method_' . $this->id, array( $this, 'options' ), 10 );
        add_filter( 'sunshine_create_order_status', array( $this, 'create_order_status' ), 10, 2 );
        add_filter( 'sunshine_order_transaction_url', array( $this, 'get_transaction_url' ) );
        add_filter( 'sunshine_checkout_create_order_mode', array( $this, 'mode' ), 10, 2 );
    }

    public function init() { }

    public function register( $payment_methods = array() ) {
        if ( !empty( $this->id ) ) {
            $payment_methods[ $this->id ] = array(
                'id' => $this->id,
                'name' => $this->name,
                'description' => $this->description,
                'class' => $this->class
            );
        }
        return $payment_methods;
    }

    // Every payment method will at least have these options
    public function default_options( $fields ) {
        $fields['1'] = array( 'id' => $this->id . '_header', 'name' => $this->name, 'type' => 'header', 'description' => '' );
        $fields['2'] = array(
            'name' => __( 'Name', 'sunshine-photo-cart' ),
            'id'   => $this->id . '_name',
            'type' => 'text',
            'description' => __( 'Name displayed on the checkout page to the customer', 'sunshine-photo-cart' ),
            'placeholder' => $this->name
        );
        $fields['3'] = array(
            'name' => __( 'Description', 'sunshine-photo-cart' ),
            'id'   => $this->id . '_description',
            'type' => 'text',
            'description' => __( 'Description displayed on the checkout page to the customer', 'sunshine-photo-cart' ),
            'placeholder' => $this->description
        );
        return $fields;
    }

    public function create_order_status( $status, $order ) {
        return $status;
    }

    public function options( $options ) {
        return $options;
    }

    private function set_id( $id ) {
        $this->id = sanitize_key( $id );
    }

    public function get_id() {
        return $this->id;
    }

    public function set_name( $name ) {
        $this->name = sanitize_text_field( $name );
    }

    public function get_name() {
        $custom_name = SPC()->get_option( $this->id . '_name' );
        if ( !empty( $custom_name ) ) {
            return $custom_name;
        }
        return $this->name;
    }

    public function set_description( $description ) {
        $this->description = esc_html( $description );
    }

    public function get_description() {
        $custom_description = SPC()->get_option( $this->id . '_description' );
        if ( !empty( $custom_description ) ) {
            return $custom_description;
        }
        return $this->description;
    }

    public function is_active() {
        $active = SPC()->get_option( $this->id . '_active' );
        if ( !empty( $active ) ) {
            return true;
        }
        return false;
    }

    public function can_be_enabled() {
        return $this->can_be_enabled;
    }

    public function needs_billing_address() {
        return $this->needs_billing_address;
    }

    public function get_transaction_id( $order ) {
        return false;
    }

    public function get_transaction_url( $order ) {
        return false;
    }

    public function mode( $mode, $order ) {
        return $mode;
    }

}
