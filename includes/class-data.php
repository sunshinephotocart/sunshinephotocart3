<?php
abstract class Sunshine_Data {

    protected $id = 0;
    protected $data = array();
    protected $meta = array();
    protected $post_type;
    protected $taxonomy;

    public function __get( $key ) {
        if ( array_key_exists( $key, $this->meta ) ) {
            return $this->meta[ $key ];
        } else {
            return get_post_meta( $this->id, $key, true );
        }
        return null;
    }

    public function show_data() {
        echo '<pre>';
        var_dump( $this->data );
        echo '</pre>';
    }

    public function get_data() {
        return array_merge( array( 'id' => $this->get_id() ), (array) $this->data, array( 'meta_data' => $this->get_meta_data() ) );
    }

    public function get_meta_data() {
        if ( empty( $this->meta ) ) {
            $this->set_meta_data();
        }
        return $this->meta;
    }

    public function update_meta_data() {
        foreach ( $this->meta as $key => $value ) {
            update_post_meta( $this->id, $key, $value );
        }
    }

    public function set_meta_data() {
        $meta = get_post_meta( $this->id );
        if ( empty( $meta ) ) {
            return;
        }
        foreach ( $meta as $key => $value ) {
            if ( !empty( $value ) ) {
                if ( count( $value ) == 1 ) {
                    $this->meta[ $key ] = $value[ 0 ];
                } else {
                    $this->meta[ $key ] = array();
                    foreach ( $meta[ $key ] as $item ) {
                        $this->meta[ $key ][] = $item;
                    }
                }
            }
        }
    }

    public function get_id() {
        return $this->id;
    }
    public function set_id( $id ) {
        $this->id = (int) $id;
    }

    public function get_meta_value( $key ) {
        if ( empty( $this->meta ) ) {
            $this->set_meta_data();
        }
        if ( array_key_exists( $key, $this->meta ) ) {
            return maybe_unserialize( $this->meta[ $key ] );
        }
        $value = get_post_meta( $this->id, $key, true );
        if ( !empty( $value ) ) {
            return maybe_unserialize( $value );
        }
        return false;
    }

    public function update_meta_value( $key, $value ) {
        $this->meta[ $key ] = $value;
        update_post_meta( $this->id, $key, $value );
    }

    public function exists() {
        if ( !empty( $this->id ) ) {
            return true;
        }
        return false;
    }

    public function save() {

        if ( $this->get_id() > 0 ) {
            $this->update( $this );
        } else {
            $this->create( $this );
        }

    }

    public function update() {
        $args = array(
            'ID' => $this->get_id(),
            'meta_input' => $this->meta
        );
        return wp_update_post( $args );
    }

    public function create() { }

    public function delete( $force_delete = false ) {
        if ( !empty( $this->taxonomy ) ) {
            // Delete the term
            wp_delete_term( $this->get_id(), $this->taxonomy );
        } else {
            // Post type
            if ( $this->get_id() ) {
                wp_delete_post( $this->get_id(), $force_delete );
            }
        }
    }

}
