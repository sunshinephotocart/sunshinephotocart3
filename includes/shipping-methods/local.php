<?php

class SPC_Shipping_Method_Local extends SPC_Shipping_Method {

    public function init() {
        $this->id = 'local';
        $this->name = __( 'Local Delivery', 'sunshine-photo-cart' );
        $this->class = 'SPC_Shipping_Method_Local';
        $this->description = __( 'You personally deliver to customer with options to configure allowed locations', 'sunshine-photo-cart' );
        $this->can_be_cloned = true;
    }

    public function options( $fields, $instance_id ) {
        $fields['2200'] = array(
            'name' => __( 'Allowed Zip / Post codes', 'sunshine-photo-cart' ),
            'id'   => $this->id . '_postcodes_' . $instance_id,
            'type' => 'textarea',
            'description' => __( 'Allowed zipcode or postal codes that are allowed for this shipping option, separated by commas. Ex: 80525,80526,80527. Leave empty to allow all.', 'sunshine-photo-cart' )
        );
        return $fields;
    }

    public function is_allowed() {
        if ( empty( $this->instance_id ) ) {
            return false;
        }

        // If no zipcodes are set, let anyone use this
        $postcodes = SPC()->get_option( $this->id . '_postcodes_' . $this->instance_id );
        if ( empty( $postcodes ) ) {
            return true;
        }

        // If has any zipcodes, but no customer zipcode set somehow then we fail
        $customer_postcode = SPC()->cart->get_checkout_data_item( 'shipping_postcode' );
        if ( empty( $customer_postcode ) ) {
            return false;
        }

        // Customer zipcode not in allowed zipcodes
        $postcodes = explode( ',', str_replace( ' ', '', $postcodes ) );
        if ( !in_array( $customer_postcode, $postcodes ) ) {
            return false;
        }

        return true;

    }

}

$sunshine_shipping_local = new SPC_Shipping_Method_Local();
