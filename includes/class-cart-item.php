<?php

class SPC_Cart_Item {

    public $item = array();
    public $object_id;
    public $type;
    public $object;
    protected $name = '';
    public $product_id;
    public $product;
    public $gallery_id;
    public $gallery_name;
    public $gallery;
    public $qty = 0;
    public $price_level;
    public $price;
    public $taxable;
    protected $total = 0;
    public $comments = '';
    public $hash;

    function __construct( $item ) {

        if ( empty( $item['object_id'] ) ) {
            return false;
        }

        $this->item = $item;

        $this->object_id = $item['object_id'];

        if ( !empty( $item['type'] ) ) {
            $this->type = $item['type'];
            if ( $this->type == 'image' ) {
                $this->object = new SPC_Image( $item['object_id'] );
            }
        }
        $this->object = apply_filters( 'sunshine_cart_item_object', $this->object, $this );

        if ( !empty( $item['price_level'] ) ) {
            $this->price_level = $item['price_level'];
        }

        if ( !empty( $item['product_id'] ) && !empty( $item['price_level'] ) ) {
            $this->product_id = $item['product_id'];
            $product = new SPC_Product( $item['product_id'], $item['price_level'] );
            $this->product = $product;
            $this->name = $product->get_display_name();
        }

        if ( !empty( $item['gallery_id'] ) ) {
            $this->gallery_id;
            $this->gallery = new SPC_Gallery( $item['gallery_id'] );
        }
        if ( !empty( $item['gallery_name'] ) ) {
            $this->gallery_name = $item['gallery_name'];
        }

        if ( !empty( $item['price'] ) ) {
            $this->price = $item['price'];
        }
        $this->price = apply_filters( 'sunshine_cart_item_price', floatval( $this->price ), $item );

        if ( !empty( $item['qty'] ) ) {
            $this->qty = intval( $item['qty'] );
        }

        if ( !empty( $item['taxable'] ) ) {
            $this->taxable = $item['taxable'];
        }

        // TODO: determine taxes for this line item (if tax is included in price or not )

        $this->total = apply_filters( 'sunshine_cart_item_total', floatval( $this->qty * $this->price ), $item );

        if ( !empty( $item['comments'] ) ) {
            $this->comments = $item['comments'];
        }
        $this->comments = apply_filters( 'sunshine_cart_item_comments', $this->comments, $item );

        if ( !empty( $item['hash'] ) ) {
            $this->hash = $item['hash'];
        }

    }

    public function get_object_id() {
        return $this->object_id;
    }

    public function get_product_id() {
        return $this->product_id;
    }

    public function get_type() {
        return $this->type;
    }

    public function get_image_url() {
        $image_url = '';
        if ( is_a( $this->object, 'SPC_Image' ) ) {
            $image_url = $this->object->get_image_url();
        }
        $image_url = apply_filters( 'sunshine_cart_item_image_url', $image_url, $this );
        return $image_url;
    }

    public function get_image_html( $size = 'sunshine-thumbnail', $use_placeholder = true, $args = array() ) {
        $image_url = $this->get_image_url();
        $image_name = $this->get_image_name();
        $atts = '';
        if ( !empty( $args ) ) {
            foreach ( $args as $key => $value ) {
                $atts .= ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
            }
        }
        if ( $image_url ) {
            return '<img src="' . $image_url . '" alt="' . esc_attr( $image_name ) . '" ' . $atts . ' />';
        } elseif ( $use_placeholder ) {
            if ( empty( $image_url ) ) {
                // TODO: Make custom placeholder image
                return '<img src="' . SUNSHINE_PHOTO_CART_URL . '/assets/images/missing-image.png" alt="' . esc_attr( $image_name ) . '" ' . $atts . ' />';
            }

        }
    }

    public function get_image_name() {
        $image_name = '';
        if ( is_a( $this->object, 'SPC_Image' ) && $this->object->exists() ) {
            $image_name = $this->object->get_name() . ' &mdash; <a href="' . $this->object->gallery->get_permalink() . '">' . $this->object->gallery->get_name() . '</a>';
        } elseif ( !empty( $this->item['image_name'] ) ) {
            $image_name = $this->item['image_name'];
        }
        $image_name = apply_filters( 'sunshine_cart_item_image_name', $image_name, $this );
        return $image_name;
    }

    public function get_name() {
        return $this->name;
    }

    public function get_name_raw() {
        return strip_tags( $this->name );
    }

    public function get_qty() {
        return $this->qty;
    }

    public function get_price() {
        return $this->price;
    }
    public function get_price_formatted() {
        return sunshine_price( $this->get_price() );
    }

    public function is_taxable() {
        return $this->taxable;
    }

    public function get_comments() {
        return $this->comments;
    }

    public function get_total() {
        return $this->total;
    }
    public function get_total_formatted() {
        return sunshine_price( $this->get_total() );
    }

    public function get_hash() {
        return $this->hash;
    }

    public function get_filename() {
        if ( !empty( $this->item['filename'] ) ) {
            return $this->item['filename'];
        }
        return false;
    }

    public function get_remove_url() {
        $url = SPC()->get_option( 'page_cart' );
        $url = add_query_arg( 'delete_cart_item', $this->get_hash(), $url );
        $url = add_query_arg( 'nonce', wp_create_nonce( 'sunshine_delete_cart_item' ), $url );
        return $url;
    }

    public function classes() {
        if ( !empty( $this->product ) && is_a( $this->product, 'SPC_Product' ) ) {
            return $this->product->classes();
        }
        return false;
    }

}
