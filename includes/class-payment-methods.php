<?php
class SPC_Payment_Methods {

    public $payment_methods = array();

    protected static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    function __construct() {
        $this->init();
    }

    public function init() {

        $methods_to_load = array(
            'SPC_Payment_Method_Test',
			'SPC_Payment_Method_Free',
			'SPC_Payment_Method_Offline',
            'SPC_Payment_Method_PayPal'
		);

        $methods_to_load = apply_filters( 'sunshine_payment_methods', $methods_to_load );

        foreach ( $methods_to_load as $method ) {

            if ( is_string( $method ) && class_exists( $method ) ) {
                $method = new $method();
            }

            if ( ! is_a( $method, 'SPC_Payment_Method' ) ) {
                continue;
            }

            $this->payment_methods[ $method->id ] = $method;

        }

    }

    public function get_payment_methods() {
        $available_methods = array();

        if ( count( $this->payment_methods ) > 0 ) {
            foreach ( $this->payment_methods as $method ) {
                $available_methods[ $method->id ] = $method;
            }
        }

        return $available_methods;
    }

    function get_payment_method_by_id( $id ) {
        if ( array_key_exists( $id, $this->payment_methods ) ) {
            return $this->payment_methods[ $id ];
        }
        return false;
    }

    function get_selected_payment_method() {
        $id = SPC()->cart->get_checkout_data_item( 'payment_method' );
        if ( $id ) {
            $allowed_payment_methods = $this->get_active_payment_methods();
            if ( array_key_exists( $id, $allowed_payment_methods ) ) {
                return $this->get_payment_method_by_id( $id );
            }
        }
        return false;
    }

    public function is_payment_method_allowed( $id ) {
        $active_payment_methods = $this->get_active_payment_methods();
        if ( array_key_exists( $id, $active_payment_methods ) ) {
            return true;
        }
        return false;
    }

    function get_active_payment_methods() {
        $active_methods = array();
        foreach ( $this->payment_methods as $id => $payment_method ) {
            $payment_method_class = $this->get_payment_method_by_id( $id );
            if ( $payment_method_class && $payment_method_class->is_active() ) {
                $active_methods[ $id ] = $payment_method_class;
            }
        }
        return $active_methods;
    }

}
