<?php

class SPC_Image extends Sunshine_Data {

    protected $post_type = 'attachment';
    protected $name;
    public $gallery;

    public function __construct( $object ) {

        if ( is_numeric( $object ) ) {
            if ( $object > 0 ) {
                $post = get_post( $object );
                if ( empty( $post ) || empty( $post->post_parent ) || $post->post_type != $this->post_type ) { return false; }
                $this->id = $post->ID;
                $this->data = $post;
                if ( $post->post_title ) {
                    $this->name = $post->post_title;
                }
            }
        } elseif ( is_a( $object, 'WP_Post' ) ) {
            if ( $object->post_type != $this->post_type ) { return false; }
            $this->data = $object;
            $this->id = $object->ID;
            if ( $object->post_title ) {
                $this->name = $object->post_title;
            }
        }

        if ( !empty( $this->data->post_parent ) ) {
            $this->gallery = new SPC_Gallery( $this->data->post_parent );
        }

        if ( $this->id > 0 ) {
            $this->set_meta_data();
        }

    }

    public function get_name( $show = '' ) {
        if ( !$show ) {
            $show = SPC()->get_option( 'show_image_data' );
        }
        if ( is_admin() ) {
            $show = 'filename';
        }
        if ( empty( $show ) || $show == '' ) {
            $name = '';
        } elseif ( $show == 'filename' ) {
            $name = $this->get_file_name();
        } elseif ( $show == 'title' ) {
            $name = $image->name;
        }
        return apply_filters( 'sunshine_image_name', $this->name, $this );
    }

    public function get_file_name() {
        return basename( get_attached_file( $this->get_id() ) );
    }

    public function get_gallery() {
        if ( !empty( $this->gallery ) ) {
            return $this->gallery;
        }
        $this->gallery = new SPC_Gallery( $this->data->post_parent );
        return $this->gallery;
    }

    public function get_gallery_id() {
        if ( !empty( $this->gallery ) ) {
            return $this->gallery->get_id();
        }
        $this->get_gallery();
        return $this->gallery->get_id();
    }

    public function get_image_url( $size = 'sunshine-thumbnail' ) {
        $thumb = wp_get_attachment_image_src( $this->get_id(), $size );
        if ( !empty( $thumb ) ) {
            return $thumb[0];
        }
        return false;
    }

    public function get_permalink() {
        if ( empty( $this->gallery ) ) {
            $this->get_gallery();
        }
        if ( $this->gallery ) {
            //return trailingslashit( trailingslashit( $this->gallery->get_permalink() ) . SPC()->get_option( 'endpoint_image' ) . '/' . $this->data->post_name );
        }
        return get_permalink( $this->get_id() );
    }

    public function output( $size = 'full', $echo = true ) {
        $output = '<img src="' . esc_url( $this->get_image_url( $size ) ) . '" alt="' . esc_attr( $this->get_name() ) . '" />';
        if ( $echo ) {
            echo $output;
            return;
        }
        return $output;
    }

    public function get_comments() {
        $comments = get_comments( 'post_id=' . $this->get_id() . '&status=approve&order=ASC' );
        return $comments;
    }

    public function get_comment_count() {
        return count( $this->get_comments() );
    }

    public function can_view() {
        if ( empty( $this->gallery ) ) {
            $this->get_gallery();
        }
        if ( $this->gallery ) {
            return $this->gallery->can_view();
        }
        return false;
    }

    public function is_favorite() {
        if ( is_user_logged_in() ) {
            $favorites = SPC()->customer->get_favorite_ids();
            if ( !empty( $favorites ) && in_array( $this->get_id(), $favorites ) ) {
                return true;
            }
        }
        return false;
    }

    public function in_cart() {
        if ( !SPC()->cart->is_empty() ) {
            foreach ( SPC()->cart->get_cart() as $item ) {
                if ( isset( $item['object_id'] ) && $item['object_id'] == $this->get_id() ) {
                    return true;
                }
            }
        }
        return false;
    }

    public function products_disabled() {
        $gallery = $this->get_gallery();
        if ( $gallery && $gallery->products_disabled() ) {
            return true;
        }
        return false;
    }

    public function allow_comments() {
        $gallery = $this->get_gallery();
        if ( $gallery && $gallery->allow_comments() ) {
            return true;
        }
        return false;
    }

    public function comments_require_approval() {
        $gallery = $this->get_gallery();
        if ( $gallery && $gallery->comments_require_approval() ) {
            return true;
        }
        return false;
    }

    public function allow_sharing() {
        $gallery = $this->get_gallery();
        if ( $gallery && $gallery->allow_sharing() ) {
            return true;
        }
        return false;
    }

    public function allow_favorites() {
        $gallery = $this->get_gallery();
        if ( $gallery && $gallery->allow_favorites() ) {
            return true;
        }
        return false;
    }


}
