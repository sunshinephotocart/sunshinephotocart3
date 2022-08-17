<?php
class SPC_Customer extends WP_User {

    public function __construct( $user_id ) {
        parent::__construct( $user_id );
        $all_meta = get_user_meta( $user_id );
        if ( !empty( $all_meta['first_name'][0] ) ) {
            $this->data->first_name = $all_meta['first_name'][0];
        }
        if ( !empty( $all_meta['last_name'][0] ) ) {
            $this->data->last_name = $all_meta['last_name'][0];
        }
    }

    public function get_id() {
        return $this->ID;
    }

    public function update_meta( $key, $value ) {
        if ( $this->ID > 0 ) {
            update_user_meta( $this->ID, SPC()->prefix . $key, $value );
        }
    }

    public function get_name() {
        if ( $this->get_first_name() ) {
            return $this->get_first_name() . ' ' . $this->get_last_name();
        }
        if ( $this->display_name ) {
            return $this->display_name;
        }
    }
    public function get_first_name() {
        return ( !empty( $this->data->first_name ) ) ? $this->data->first_name : '';
    }
    public function get_last_name() {
        return ( !empty( $this->data->last_name ) ) ? $this->data->last_name : '';
    }

    public function get_email() {
        return ( !empty( $this->data->user_email ) ) ? $this->data->user_email : '';
    }

    public function set_cart( $contents ) {
        update_user_meta( $this->ID, SPC()->prefix . 'cart', $contents );
    }

    public function get_cart() {
        return get_user_meta( $this->ID, SPC()->prefix . 'cart', $contents );
    }

    public function get_credits() {
        return (int) get_user_meta( $this->ID, SPC()->prefix . 'credits', true );
    }

    public function get_favorite_ids() {
        $favorites = get_user_meta( $this->ID, SPC()->prefix . 'favorites', true );
        if ( empty( $favorites ) ) {
            $favorites = array();
        }
        return $favorites;
    }

    public function get_favorites() {
        $favorite_ids = $this->get_favorite_ids();
        if ( !empty( $favorite_ids ) ) {
            $final_favorites = array();
            foreach ( $favorite_ids as $favorite_id ) {
                $final_favorites[] = new SPC_Image( $favorite_id );
            }
            return $final_favorites;
        }
        return false;
    }

    public function add_favorite( $image_id ) {

        $image_id = intval( $image_id );
        $favorites = $this->get_favorite_ids();
        if ( !in_array( $image_id, $favorites ) ) {
            $favorites[] = $image_id;
        }
        update_user_meta( $this->ID, SPC()->prefix . 'favorites', $favorites ); // TODO: Use the class function here and throughout for get/set/delete user meta

        $favorite_count = get_post_meta( $image_id, 'favorite_count', true );
        $favorite_count++;
        update_post_meta( $image_id, 'favorite_count', $favorite_count );

        do_action( 'sunshine_add_favorite', $image_id );

    }

    public function delete_favorite( $image_id ) {

        $favorites = $this->get_favorite_ids();
        $key = array_search( $image_id, $favorites );
        if ( $key !== false ) {
            unset( $favorites[ $key ] );
        }
        update_user_meta( $this->ID, SPC()->prefix . 'favorites', $favorites ); // TODO: Use the class function here and throughout for get/set/delete user meta

        $favorite_count = get_post_meta( $image_id, 'favorite_count', true );
        $favorite_count--;
        update_post_meta( $image_id, 'favorite_count', $favorite_count );

        do_action( 'sunshine_delete_favorite', $image_id );

    }

    public function has_favorite( $image_id ) {
        $favorites = $this->get_favorite_ids();
        if ( in_array( $image_id, $favorites ) ) {
            return true;
        }
        return false;
    }

    public function get_favorite_count() {
        $favorites = $this->get_favorites();
        if ( empty( $favorites ) ) {
            return 0;
        }
        return count( $favorites );
    }

    public function clear_favorites() {
        delete_user_meta( $this->ID, SPC()->prefix . 'favorite' );
    }

    /* SHIPPING */
    public function get_shipping_address() {
        $key = SPC()->prefix . 'shipping_address1';
        return $this->{$key};
    }
    public function get_shipping_address2() {
        $key = SPC()->prefix . 'shipping_address2';
        return $this->{$key};
    }
    public function get_shipping_city() {
        $key = SPC()->prefix . 'shipping_city';
        return $this->{$key};
    }
    public function get_shipping_state() {
        $key = SPC()->prefix . 'shipping_state';
        return $this->{$key};
    }
    public function get_shipping_postcode() {
        $key = SPC()->prefix . 'shipping_postcode';
        return $this->{$key};
    }
    public function get_shipping_country() {
        $key = SPC()->prefix . 'shipping_country';
        return $this->{$key};
    }

    /* BILLING */ // TODO: Use prefix like shipping
    public function get_billing_address() {
        $this->billing_address;
    }
    public function get_billing_address2() {
        $this->billing_address2;
    }
    public function get_billing_city() {
        $this->billing_city;
    }
    public function get_billing_state() {
        $this->billing_state;
    }
    public function get_billing_postcode() {
        $this->billing_postcode;
    }
    public function get_billing_country() {
        $this->billing_country;
    }

    /* OTHER FIELDS */
    public function get_phone() {
        $this->sunshine_phone;
    }
    public function get_vat() {
        $this->vat;
    }

    /* ORDERS */
    public function get_orders() {
        $orders = array();
        $args = array(
        	'post_type' => 'sunshine-order',
            'posts_per_page' => -1,
            'author' => $this->ID
        );
        $query = new WP_Query( $args );
        while ( $query->have_posts() ) : $query->the_post();
            $orders[] = new SPC_Order( $query->posts[ $query->current_post ] );
        endwhile; wp_reset_postdata();
        return $orders;
    }

}
