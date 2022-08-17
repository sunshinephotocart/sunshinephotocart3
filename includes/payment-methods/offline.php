<?php

class SPC_Payment_Method_Offline extends SPC_Payment_Method {

    public function init() {
        $this->id = 'offline';
        $this->name = __( 'Offline', 'sunshine-photo-cart' );
        $this->class = get_class( $this );
        $this->description = __( 'Payments handled offline (like check or cash)', 'sunshine-photo-cart' );
        $this->can_be_enabled = true;
        $this->needs_billing_address = false;
    }

    public function options( $options ) {
        $options[] = array(
            'name' => __( 'Instructions', 'sunshine-photo-cart' ),
            'id'   => $this->id . '_instructions',
            'type' => 'textarea',
            'description' => __( 'Instructions included on order receipt page and email with how to complete payment', 'sunshine-photo-cart' )
        );
        return $options;
    }

}

//$sunshine_payment_method_offline = new Sunshine_Payment_Method_Offline();
