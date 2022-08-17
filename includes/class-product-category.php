<?php
class SPC_Product_Category extends Sunshine_Data {

    protected $post_type = 'sunshine-product';
    protected $taxonomy = 'sunshine-product-category';
    protected $name;
    protected $key;

    public function __construct( $object ) {

        if ( is_numeric( $object ) && $object > 0 ) {
            $object = get_term_by( 'id', $object, $this->taxonomy );
            if ( empty( $object ) || $object->taxonomy != $object->taxonomy ) { return false; }
        } elseif ( is_a( $object, 'WP_Term' ) ) {
            if ( $object->taxonomy != $this->taxonomy ) { return false; }
        } else {
            $object = get_term_by( 'slug', $object, $this->taxonomy );
            if ( empty( $object ) || $object->taxonomy != $object->taxonomy ) { return false; }
        }

        if ( !empty( $object->term_id ) ) {
            $this->id = $object->term_id;
            $this->data = $object;
            $this->name = $object->name;
            $this->key = $object->slug;
            $this->set_meta_data();
        }

    }

    public function get_name() {
        return apply_filters( 'sunshine_category_name', $this->name, $this );
    }

    public function get_description() {
        return $this->data->description;
    }

    public function get_key() {
        return $this->key;
    }


}
