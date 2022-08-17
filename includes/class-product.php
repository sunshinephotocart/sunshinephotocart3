<?php
class SPC_Product extends Sunshine_Data {

    protected $post_type = 'sunshine-product';
    protected $product_type = 'image'; // TODO: Need to make dynamic
    protected $price_level; // int
    protected $price; // float
    protected $category; // int
    protected $shipping; // boolean
    protected $taxable; // boolean

    public function __construct( $object = '', $price_level = '' ) {

        if ( is_numeric( $object ) && $object > 0 ) {
            $object = get_post( $object );
            if ( empty( $object ) || $object->post_type != $this->post_type ) { return false; }
        } elseif ( is_a( $object, 'WP_Post' ) ) {
            if ( $object->post_type != $this->post_type ) { return false; }
        }

        if ( !empty( $object->ID ) ) {
            $this->id = $object->ID;
            $this->data = $object;

            if ( $object->post_title ) {
                $this->name = $object->post_title;
            }

            $this->set_meta_data();
        }

        if ( !empty( $price_level ) ) {
            $this->price_level = intval( $price_level );
        }

    }

    /*
    $type = string, 'image', 'download', etc.
    */
    public function set_product_type( $type ) {
        $this->product_type = $type;
    }

    public function get_product_type() {
        return apply_filters( 'sunshine_product_type', $this->product_type, $this );
    }

    /*
    $price_level = int
    */
    public function set_price_level( $price_level ) {
        $this->price_level = intval( $price_level );
    }

    public function get_price_level() {
        if ( empty( $this->price_level ) ) {
            $this->price_level = sunshine_get_default_price_level();
        }
        return apply_filters( 'sunshine_product_price_level', $this->price_level, $this );
    }

    public function set_price( $price ) {
        $this->price = floatval( $price );
    }

    public function get_price( $price_level = '' ) {
        if ( empty( $price_level ) ) {
            $price_level = $this->price_level;
        }
        if ( empty( $price_level ) ) {
            $default_price_level = sunshine_get_default_price_level();
            if ( $default_price_level ) {
                $price_level = $default_price_level->get_id();
            } else {
                return false; // We at minimum need to know a price level
            }
        }
        $price = 0;
        $key = 'price_' . intval( $price_level );
        $price = $this->get_meta_value( $key );
        return apply_filters( 'sunshine_product_price', $price, $this );
    }

    public function get_price_formatted( $price_level = '' ) {
        return sunshine_price( $this->get_price( $price_level ) );
    }

    public function has_image() {
        return ( $this->get_image_id() > 0 ) ? true : false;
    }
    public function get_image_id() {
        return get_post_thumbnail_id( $this->get_id() );
    }
    public function get_image_url( $size = 'medium' ) {
        $product_image_id = $this->get_image_id();
        if ( $product_image_id ) {
            return wp_get_attachment_url( $product_image_id );
        }
        return false;
    }
    public function get_image_html( $size = 'thumbnail', $echo = true ) {
        if ( !$this->has_image() ) {
            return false;
        }
        $output = '<img src="' . esc_url( $this->get_image_url( $size ) ) . '" alt="' . esc_attr( $this->get_name() ) . '" />';
        if ( $echo ) {
            echo $output;
            return;
        }
        return $output;
    }

    /*
    $category = int
    */
    public function set_category( $category ) {
        if ( is_a( $category, 'SPC_Product_Category' ) ) {
            $this->category = $category;
        } else {
            $this->category = new SPC_Product_Category( $category );
        }
    }

    public function get_category() {
        if ( empty( $this->category ) ) {
            $categories = wp_get_object_terms( $this->get_id(), 'sunshine-product-category' );
            if ( !empty( $categories ) ) {
                $this->category = new SPC_Product_Category( $categories[0] );
            }
        }
        return $this->category;
    }

    public function get_category_name() {
        if ( empty( $this->category ) ) {
            $this->get_category();
        }
        if ( !empty( $this->category ) ) {
            return $this->category->get_name();
        }
        return false;
    }

    public function get_category_id() {
        if ( empty( $this->category ) ) {
            $this->get_category();
        }
        if ( !empty( $this->category ) ) {
            return $this->category->get_id();
        }
        return false;
    }

    public function set_name( $name ) {
        $this->name = $name;
    }

    public function get_name() {
        return $this->name;
    }

    public function get_description() {
        if ( !empty( $this->data->post_content ) ) {
            return $this->data->post_content;
        }
        return false;
    }

    // category + name
    public function get_display_name() {
        $separator = apply_filters( 'sunshine_product_name_separator', ' &mdash; ' );
        return apply_filters( 'sunshine_product_display_name', '<span class="sunshine--product--category">' . $this->get_category_name() . '</span> <span class="sunshine--product--separator">' . $separator . '</span> <span class="sunshine--product--name">' . $this->get_name() . '</span>', $this );
    }

    public function get_shipping() {
        return apply_filters( 'sunshine_product_shipping', $this->shipping, $this );
    }

    public function set_shipping( $shipping ) {
        $this->shipping = floatval( $shipping );
    }

    public function needs_shipping() {
        return apply_filters( 'sunshine_product_' . $this->product_type, true );
    }

    // TODO
    public function can_purchase() {
        return apply_filters( 'sunshine_product_can_purchase', true, $this );
    }

    public function is_taxable() {
        return ( !empty( $this->meta['sunshine_product_taxable'] ) ? true : false );
    }
    public function set_taxable( $taxable ) {
        $this->taxable = ( $taxable ) ? true : false;
    }

    public function classes() {
        $classes = array();
    	$classes[] = 'sunshine-product';
    	$classes[] = 'sunshine-product-' . $this->get_id();
        $classes[] = 'sunshine-product-' . $this->get_product_type();
    	$classes = apply_filters( 'sunshine_product_class', $classes, $this );
    	echo join( ' ', $classes );
    }

    public function create() {

        if ( empty( $this->name ) ) {
            // At least need a name
            return false;
        }

        $product_id = wp_insert_post(array(
            'post_title' => $this->name,
            'post_status' => 'publish',
            'post_type' => $this->post_type,
            'comment_status' => 'closed',
            'meta_input' => $this->meta
        ));

        // Set price
        if ( !empty( $this->price ) ) {
            if ( empty( $this->price_level ) ) {
                $this->price_level = sunshine_get_default_price_level();
            }
            update_post_meta( $product_id, 'price_' . $this->price_level->get_id(), $this->price );
        }

        // Set category
        if ( !empty( $this->category ) ) {
            wp_set_object_terms( $product_id, $this->category->get_id(), 'sunshine-product-category' );
        }

        // Set shipping
        if ( !empty( $this->shipping ) ) {
            update_post_meta( $product_id, 'shipping', $this->shipping );
        }

        // Set taxable
        if ( !empty( $this->taxable ) ) {
            update_post_meta( $product_id, 'taxable', $this->taxable );
        }

        parent::create();

        SPC()->log( 'Product created: ' . $this->name );

        return $product_id;

    }

}
