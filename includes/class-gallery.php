<?php
class SPC_Gallery extends Sunshine_Data {

    protected $post_type = 'sunshine-gallery';
    protected $name;
    private $parent_gallery_id;

    public function __construct( $object ) {

        if ( is_numeric( $object ) ) {
            if ( $object > 0 ) {
                $post = get_post( $object );
                if ( empty( $post ) || $post->post_type != $this->post_type ) { return false; }
                $this->id = $post->ID;
                $this->data = $post;
            }
        } elseif ( is_a( $object, 'WP_Post' ) ) {
            if ( $object->post_type != $this->post_type ) { return false; }
            $this->data = $object;
            $this->id = $object->ID;
        }

        if ( !empty( $this->data->post_title ) ) {
            $this->name = $this->data->post_title;
        }
        if ( !empty( $this->data->post_parent ) ) {
            $this->parent_gallery_id = $this->data->post_parent;
        }

        if ( $this->id > 0 ) {
            $this->set_meta_data();
        }

    }

    public function get_name() {
        return $this->name;
    }

    public function get_content() {
        return apply_filters( 'the_content', $this->data->post_content );
    }

    public function get_parent_gallery_id() {
        return $this->parent_gallery_id;
    }

    public function get_image_directory() {
        return $this->images_directory;
    }

    // TODO: Check based on proofing option
    public function can_purchase() {
        if ( $this->products_disabled() || $this->is_expired() || SPC()->get_option( 'proofing' ) ) {
            return false;
        }
        return true;
    }

    // Can the user even view or see that this gallery exists
    public function can_view() {

        if ( current_user_can( 'sunshine_manage_options' ) ) {
            return true;
        }

        if ( $this->get_status() == 'private' ) {
            if ( !is_user_logged_in() ) {
                return false;
            }
            $allowed_users = $this->get_private_users();
            if ( !in_array( get_current_user_id(), $allowed_users ) ) {
                return false;
            }
        }

        // TODO: URL ACCESS?

        return true;

    }

    // Can they access it?
    public function can_access() {

        if ( !$this->can_view() ) {
            return false;
        }

        if ( $this->password_required() ) {
            return false;
        }

        return true;

    }

    public function get_private_users() {
        return $this->get_meta_value( 'private_users' );
    }

    public function get_price_level() {
        return $this->price_level;
    }

    public function products_disabled() {
        return $this->disable_products;
    }

    public function get_featured_image_id() {

        if ( has_post_thumbnail( $this->get_id() ) ) {
            return get_post_thumbnail_id( $this->get_id() );
        } else if ( $images = get_children( array(
                    'post_parent' => $this->get_id(),
                    'post_type' => 'attachment',
                    'numberposts' => 1,
                    'post_mime_type' => 'image',
                    'orderby' => 'menu_order ID',
                    'order' => 'ASC' ) ) ) {
            foreach( $images as $image ) {
                return $image->ID;
            }
        }
        return false;

    }
    public function featured_image( $size = 'sunshine-thumbnail', $echo = 1 ) {

        if ( has_post_thumbnail( $this->get_id() ) ) {
            $src = wp_get_attachment_image_src( get_post_thumbnail_id( $this->get_id() ), $size );
        } else if ( $images = get_children( array(
                    'post_parent' => $this->get_id(),
                    'post_type' => 'attachment',
                    'numberposts' => 1,
                    'post_mime_type' => 'image',
                    'orderby' => 'menu_order ID',
                    'order' => 'ASC' ) ) ) {
            foreach ( $images as $image ) {
                $src = wp_get_attachment_image_src( $image->ID, $size );
            }
        }

        if ( !empty( $src ) ) {
            if ( $echo ) {
                echo '<img src="' . $src[0] . '" alt="' . esc_attr( get_the_title( $this->get_id() ) ) . '" />';
            } else {
                return $src[0];
            }
        }

        return;

    }

    public function get_child_galleries() {

        if ( SPC()->get_option( 'gallery_order' ) == 'date_new_old' ) {
            $orderby = 'date';
            $order = 'DESC';
        } elseif ( SPC()->get_option( 'gallery_order' ) == 'date_old_new' ) {
            $orderby = 'date';
            $order = 'ASC';
        } elseif ( SPC()->get_option( 'gallery_order' ) == 'title' ) {
            $orderby = 'title';
            $order = 'ASC';
        } else {
            $orderby = 'menu_order';
            $order = 'ASC';
        }

        $args = array(
            'post_type' => 'sunshine-gallery',
            'post_parent' => $this->get_id(),
            'orderby' => $orderby,
            'order' => $order,
            'nopaging' => true,
            //'update_post_meta_cache' => false,
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'access_type',
                    'value' => 'url',
                    'compare' => '!='
                ),
                array(
                    'key' => 'access_type',
                    'compare' => 'NOT EXISTS'
                )

            )
        );
        if ( is_user_logged_in() && !current_user_can( 'sunshine_manage_options' ) ) {
            $args['post_status'] = array( 'publish', 'private' );
            $args['meta_query'] = array(
                'relation' => 'OR',
                array(
                    'key' => 'sunshine_gallery_private_user',
                    'value' => get_current_user_id()
                ),
                array(
                    'key' => 'sunshine_gallery_private_user',
                    'value' => '0'
                ),
            );
        }
        if ( current_user_can( 'sunshine_manage_options' ) ) {
            unset( $args['post_status'] );
        }

        $child_galleries = get_posts( $args );

        if ( !empty( $child_galleries ) ) {
            $final_galleries = array();
            foreach ( $child_galleries as $child_gallery ) {
                $final_galleries[] = new SPC_Gallery( $child_gallery );
            }
            return $final_galleries;
        }
        return false;

    }

    public function get_image_ids() {
        global $wpdb;
        // TODO: Somehow get these in orders
        $image_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_parent = {$this->get_id()}" );
        if ( empty( $image_ids ) ) {
            return array();
        }
        return $image_ids;
    }

    public function get_image_count() {
        global $wpdb;
        $image_sql = "SELECT COUNT(*) as total FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_parent = {$this->get_id()};";
        return $wpdb->get_row( $image_sql )->total;
    }

    public function get_images( $custom_args = array(), $get_ids = false ) {

        $args = array(
    		'post_type' => 'attachment',
    		'posts_per_page' => SPC()->get_option( 'columns' ) * SPC()->get_option( 'rows' ),
    		'post_mime_type' => 'image',
            'post_parent' => $this->get_id()
    	);

    	if ( SPC()->get_option( 'image_order' ) == 'shoot_order' ) {
    		$args['meta_key'] = 'created_timestamp';
    		$args['orderby'] = 'meta_value';
    		$args['order'] = 'ASC';
    	} elseif ( SPC()->get_option( 'image_order' ) == 'date_new_old' ) {
    		$args['orderby'] = 'date';
    		$args['order'] = 'DESC';
    	} elseif ( SPC()->get_option( 'image_order' ) == 'date_old_new' ) {
    		$args['orderby'] = 'date';
    		$args['order'] = 'ASC';
    	} elseif ( SPC()->get_option( 'image_order' ) == 'title' ) {
    		$args['orderby'] = 'title';
    		$args['order'] = 'ASC';
    	} else {
    		$args['orderby'] = 'menu_order';
    		$args['order'] = 'ASC';
    	}

        /*
    	if ( $use_pagination && $per_page > 0 ) {
    		$args['posts_per_page'] = $per_page;
    	}

    	if ( $use_pagination && $page > 1 ) {
    		$args['offset'] = $page * $per_page;
    	}
        */

        $args = wp_parse_args( $custom_args, $args );

        if ( isset( $_GET['pagination'] ) ) {
            $args['offset'] = $args['posts_per_page'] * ( intval( $_GET['pagination'] ) - 1 );
        }

    	$args = apply_filters( 'sunshine_gallery_get_images_args', $args, $this->get_id() );

    	$images = get_posts( $args );
        if ( !empty( $images ) ) {
            $final_images = array();
            foreach ( $images as $image ) {
                if ( $get_ids ) {
                    $final_images[] = $image->ID;
                } else {
                    $final_images[] = new SPC_Image( $image );
                }
            }
            return $final_images;
        }
    	return false;
    }

    public function classes( $echo = true ) {
        $classes = array();
    	$classes[] = 'sunshine-gallery';
    	if ( $this->password_required() ) {
            $classes[] = 'sunshine--password-required';
        }
    	$classes = apply_filters( 'sunshine_gallery_classes', $classes, $this );
        if ( $echo ) {
            echo esc_html( join( ' ', $classes ) );
            return;
        }
        return $classes;
    }

    public function get_permalink() {
        return get_permalink( $this->get_id() );
    }

    public function get_parent_gallery() {
        if ( $this->data->post_parent ) {
            return new SPC_Gallery( $this->data->post_parent );
        }
        return false;
    }

    public function get_end_date() {
        return $this->end_date;
    }

    public function get_expiration_date( $format = '' ) {
        if ( empty( $format ) ) {
            $format = get_option( 'date_format' ) . ' @ ' . get_option( 'time_format' );
        }
        $end_date = $this->get_meta_value( 'end_date' );
        if ( $end_date ) {
            echo date_i18n( $format, $end_date );
        }
    }

    public function is_expired() {
        if ( current_user_can( 'sunshine_manage_options' ) ) {
    		// Never expired for admin users
    		return false;
    	}

    	$end_date = $this->get_end_date();
    	if ( $end_date && $end_date < current_time( 'timestamp' ) ) {
            $expired = true;
        }
    	return false;
    }

    public function allow_comments() {
        return $this->image_comments;
    }

    public function comments_require_approval() {
        return $this->image_comments_approval;
    }

    public function password_required() {
        if ( current_user_can( 'sunshine_manage_optionsxx' ) ) {
            return false;
        }
        if ( $this->get_status() == 'password' ) {
            // Is password in our session data?
            $passwords = SPC()->session->get( 'gallery_passwords' );
            if ( !is_array( $passwords ) || !in_array( $this->get_id(), $passwords ) ) {
                return true;
            }
        }
        return false;
    }

    public function get_status() {
        return $this->get_meta_value( 'status' );
    }

    public function get_access_type() {
        return $this->get_meta_value( 'access_type' );
    }

    public function get_password() {
        return $this->get_meta_value( 'password' );
    }

    public function get_password_hint() {
        return $this->get_meta_value( 'password_hint' );
    }

    public function email_required() {
        if ( current_user_can( 'sunshine_manage_options' ) ) {
            return false;
        }
        if ( $this->get_access_type() == 'email' ) {
            $gallery_emails = SPC()->session->get( 'gallery_emails' );
            if ( !is_array( $gallery_emails ) ) {
                return true;
            }
            if ( in_array( $this->get_id(), $gallery_emails ) ) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function get_emails() {
        return $this->get_meta_value( 'emails' );
    }

    public function add_email( $email ) {
        if ( !is_email( $email ) ) {
            return;
        }
        $emails = $this->get_emails();
        $emails[] = $email;
        $this->update_meta_value( 'emails', $emails );
    }

    public function allow_favorites() {
        return !$this->disable_favorites;
    }

    public function allow_sharing() {
        return !$this->disable_sharing;
    }


}
