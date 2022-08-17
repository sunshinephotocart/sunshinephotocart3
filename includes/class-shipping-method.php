<?php
class SPC_Shipping_Method {

    protected $id;
    protected $instance_id;
    protected $active;
    protected $name;
    protected $description;
    protected $class;
    protected $can_be_cloned = false;

    public function __construct( $instance_id = 0 ) {
        $this->init();
        add_filter( 'sunshine_shipping_methods', array( $this, 'register' ) );
        add_filter( 'sunshine_options_shipping_method_' . $this->id, array( $this, 'default_options' ), 1, 2 );
        add_filter( 'sunshine_options_shipping_method_' . $this->id, array( $this, 'options' ), 10, 2 );
        if ( !empty( $instance_id ) ) {
            $this->instance_id = $instance_id;
            add_filter( 'sunshine_checkout_delivery_options', array( $this, 'delivery_options' ) );
        }
    }

    public function init() { }

    public function register( $shipping_methods = array() ) {
        if ( !empty( $this->id ) ) {
            $shipping_methods[ $this->id ] = array(
                'id' => $this->id,
                'name' => $this->name,
                'description' => $this->description,
                'class' => $this->class
            );
        }
        return $shipping_methods;
    }

    // Every shipping method will at least have these options
    public function default_options( $fields, $instance_id ) {
        $fields['1'] = array( 'id' => $this->id . '_header', 'name' => $this->name, 'type' => 'header', 'description' => '' );
        $fields['2'] = array(
            'name' => __( 'Name', 'sunshine-photo-cart' ),
            'id'   => $this->id . '_name_' . $instance_id,
            'type' => 'text',
            'description' => __( 'Name displayed on the checkout page to the customer', 'sunshine-photo-cart' ),
            'placeholder' => $this->name
        );
        $fields['3'] = array(
            'name' => __( 'Description', 'sunshine-photo-cart' ),
            'id'   => $this->id . '_description_' . $instance_id,
            'type' => 'text',
            'description' => __( 'Description displayed on the checkout page to the customer', 'sunshine-photo-cart' ),
            'placeholder' => $this->description
        );
        $fields['4'] = array(
            'name' => __( 'Price', 'sunshine-photo-cart' ),
            'id'   => $this->id . '_price_' . $instance_id,
            'type' => 'text',
        );
        $fields['5'] = array(
            'name' => __( 'Taxable', 'sunshine-photo-cart' ),
            'id'   => $this->id . '_taxable_' . $instance_id,
            'type' => 'checkbox',
        );
        return $fields;
    }

    public function delivery_options( $options ) {
        $options['shipping'] = __( 'Shipping or delivery', 'sunshine-photo-cart' );
        return $options;
    }

    public function options( $options, $instance_id ) {
        return $options;
    }

    private function set_id( $id ) {
        $this->id = sanitize_title( $id );
    }

    public function get_id() {
        return $this->id;
    }

    public function set_name( $name ) {
        $this->name = sanitize_text_field( $name );
    }

    public function get_name() {
        // If instance_id is set, get custom name
        if ( !empty( $this->instance_id ) ) {
            $custom_name = SPC()->get_option( $this->id . '_name_' . $this->instance_id );
            if ( !empty( $custom_name ) ) {
                return $custom_name;
            }
        }
        return $this->name;

    }

    public function set_description( $description ) {
        $this->description = esc_html( $description );
    }

    public function get_description() {
        // If instance_id is set, get custom desc
        if ( !empty( $this->instance_id ) ) {
            $custom_name = SPC()->get_option( $this->id . '_description_' . $this->instance_id );
            if ( !empty( $custom_name ) ) {
                return $custom_name;
            }
        }
        return $this->description;
    }

    public function can_be_cloned() {
        return $this->can_be_cloned;
    }

    public function get_price() {
        if ( !empty( $this->instance_id ) ) {
            return SPC()->get_option( $this->id . '_price_' . $this->instance_id );
        }
        return false;
    }

    public function get_price_formatted() {
        $price = $this->get_price();
        if ( $price !== '' && is_numeric( $price ) ) {
            return sunshine_price( $price );
        }
        return false;
    }

    public function is_taxable() {
        if ( !empty( $this->instance_id ) ) {
            return SPC()->get_option( $this->id . '_taxable_' . $this->instance_id );
        }
        return false;
    }

    public function is_active() {
        if ( !empty( $this->instance_id ) ) {
            $active_shipping_methods = sunshine_get_active_shipping_methods();
            if ( array_key_exists( $this->instance_id, $active_shipping_methods ) ) {
                return true;
            }
        }
        return false;
    }

    public function is_allowed() {
        return true;
    }


}

//$sunshine_shipping_method = new SPC_Shipping_Method();
