<?php

class SPC_Shipping_Method_Flat_Rate extends SPC_Shipping_Method {

    public function init() {
        $this->id = 'flat_rate';
        $this->name = __( 'Flat Rate', 'sunshine-photo-cart' );
        $this->class = 'SPC_Shipping_Method_Flat_Rate';
        $this->description = __( 'A single shipping fee for the entire order', 'sunshine-photo-cart' );
        $this->can_be_cloned = true;
    }

}

$sunshine_shipping_flat_rate = new SPC_Shipping_Method_Flat_Rate();
