<?php

class SPC_Payment_Method_Test extends SPC_Payment_Method {

    public function init() {
        $this->id = 'test';
        $this->name = __( 'Test', 'sunshine-photo-cart' );
        $this->class = get_class( $this );
        $this->description = __( 'For processing test orders', 'sunshine-photo-cart' );
        $this->can_be_enabled = true;
    }

    public function create_order_status( $status, $order ) {
        if ( $order->get_payment_method() == $this->id ) {
            return 'new'; // Straight to new, no payment needed
        }
        return $status;
    }

    public function mode( $mode, $order ) {
        if ( $order->get_payment_method() == 'test' ) {
            return 'test';
        }
        return $mode;
    }

}
