<?php
/* GENERAL */
add_action( 'sunshine_before_content', 'sunshine_before_content_start_sunshine', 1 );
function sunshine_before_content_start_sunshine() {
    echo '<div id="sunshine" class="' . sunshine_classes( false ) . '">';
}

add_action( 'sunshine_before_content', 'sunshine_before_content_start_main', 3 );
function sunshine_before_content_start_main() {
    echo '<main id="sunshine--main"><div class="sunshine--container">';
}

add_action( 'sunshine_before_content', 'sunshine_main_menu_display', 4 );
function sunshine_main_menu_display() {
    if ( SPC()->get_option( 'main_menu' ) ) {
        sunshine_main_menu();
    }
}

add_action( 'sunshine_single_gallery', 'sunshine_show_page_header', 5 );
add_action( 'sunshine_single_image', 'sunshine_show_page_header', 5 );
function sunshine_show_page_header() {
?>
    <header id="sunshine--page-header">
    	<?php if ( apply_filters( 'sunshine_show_page_title', true ) ) : ?>
    		<h2><?php sunshine_page_title(); ?></h2>
    	<?php endif; ?>
    </header>
<?php
}

add_action( 'sunshine_single_gallery', 'sunshine_show_page_content', 9 );
function sunshine_show_page_content() {

    $content = get_the_content();
    if ( $content ) {
        $content = '<div id="sunshine--content">' . $content . '</div>';
        echo apply_filters( 'the_content', $content );
    }

}

add_action( 'sunshine_after_content', 'sunshine_after_content_end_main', 995 );
function sunshine_after_content_end_main() {
    echo '</div></main> <!-- CLOSE "sunshine--main" -->';
}

add_action( 'sunshine_after_content', 'sunshine_after_content_end_sunshine', 1 );
function sunshine_after_content_end_sunshine() {
    echo '</div> <!-- CLOSE "sunshine" -->';
}


/* GALLERIES */
add_action( 'sunshine_gallery_loop', 'sunshine_gallery_loop_start', 1 );
function sunshine_gallery_loop_start() {
    echo '<div id="sunshine--gallery-list" class="sunshine--col-' . SPC()->get_option( 'columns' ) . '">';
}

add_action( 'sunshine_gallery_loop', 'sunshine_gallery_loop_display', 50 );
function sunshine_gallery_loop_display( $galleries = array() ) {
    if ( !empty( $galleries ) && !is_array( $galleries ) ) {
        $galleries = array( $galleries ); // Fixes issue where if only one gallery is passed do_action reduces it to a single object and removes the array ref. We want this to always be an array dammit!
    }
    sunshine_get_template( 'loop/galleries', array( 'galleries' => $galleries ) );
}

add_action( 'sunshine_gallery_loop', 'sunshine_gallery_loop_end', 100 );
function sunshine_gallery_loop_end() {
    echo '</div>';
}

/* SINGLE GALLERY */
add_action( 'sunshine_single_gallery', 'sunshine_single_gallery_display' );
function sunshine_single_gallery_display( $gallery = '' ){

    if ( empty( $gallery ) && !empty( $gallery = SPC()->frontend->current_gallery ) ) {
        $gallery = SPC()->frontend->current_gallery;
    } elseif ( !empty( $gallery ) ) {
        $gallery = sunshine_get_gallery( $gallery );
    } else {
        return false;
    }

    if ( empty( $gallery ) ) {
        return false;
    }

    $email_required = false;
    if ( $gallery->email_required() ) {
        $email_required = true;
    }

    if ( !$gallery->can_view() ) {
        sunshine_get_template( 'gallery/no-permission' );
        return;
    } else {
        if ( !current_user_can( 'sunshine_manage_optionsxx' ) ) { // TODO: DO actual check before pushing live
            if ( $gallery->password_required() ) {
                sunshine_get_template( 'gallery/access', array( 'gallery' => $gallery, 'password' => true, 'email' => true ) );
                return;
            } else {
                $needs_password = false;
                $password_content = '';
                $ancestors = get_ancestors( $gallery->get_id(), 'sunshine-gallery', 'post_type' );
                if ( $ancestors ) {
                    foreach ( $ancestors as $ancestor_id ) {
                        $ancestor_gallery = new SPC_Gallery( $ancestor_id );
                        if ( $ancestor_gallery->password_required() ) {
                            sunshine_get_template( 'gallery/access', array( 'gallery' => $gallery, 'password' => true, 'email' => true ) );
                            return;
                        }
                    }
                }
            }
        }
    }

    $child_galleries = $gallery->get_child_galleries();
    if ( $child_galleries ) {
        do_action( 'sunshine_gallery_loop', $child_galleries );
    } else {
        $images = $gallery->get_images();
        if ( !empty( $images ) ) {
            sunshine_get_template( 'gallery/images', array( 'images' => $images ) );
        } else {
            sunshine_get_template( 'gallery/no-images' );
        }
    }

}

add_action( 'sunshine_single_gallery', 'sunshine_gallery_pagination_display', 1000 );
function sunshine_gallery_pagination_display( $gallery_id ) {
    sunshine_gallery_pagination();
}

/* SINGLE IMAGE */
add_action( 'sunshine_single_image', 'sunshine_single_image_display' );
function sunshine_single_image_display(){
    sunshine_get_template( 'image/image', array( 'image' => SPC()->frontend->current_image ) );
}

add_action( 'sunshine_single_image', 'sunshine_image_nav', 20 );

/* CART */
add_action( 'sunshine_cart', 'sunshine_display_cart' );
function sunshine_display_cart() {
    if ( SPC()->cart->is_empty() ) {
        sunshine_get_template( 'cart/empty' );
    } else {
        sunshine_get_template( 'cart' );
    }
}

/* CHECKOUT */
add_action( 'sunshine_checkout', 'sunshine_display_checkout' );
function sunshine_display_checkout() {
    if ( SPC()->cart->is_empty() ) {
        sunshine_get_template( 'cart/empty' );
    } else {
        sunshine_get_template( 'checkout' );
    }
}

/* ORDER */
add_action( 'sunshine_order', 'sunshine_display_order_title', 1 );
function sunshine_display_order_title() {
    if ( SPC()->frontend->current_order ) {
        sunshine_get_template( 'order/title', array( 'order' => SPC()->frontend->current_order ) );
    }
}

add_action( 'sunshine_order', 'sunshine_display_order_status', 10 );
function sunshine_display_order_status() {
    if ( SPC()->frontend->current_order ) {
        sunshine_get_template( 'order/status', array( 'order' => SPC()->frontend->current_order ) );
    }
}

add_action( 'sunshine_order', 'sunshine_display_order_details', 20 );
function sunshine_display_order_details() {
    if ( SPC()->frontend->current_order ) {
        sunshine_get_template( 'order/details', array( 'order' => SPC()->frontend->current_order ) );
    }
}

add_action( 'sunshine_order', 'sunshine_display_order_items', 30 );
function sunshine_display_order_items() {
    if ( SPC()->frontend->current_order ) {
        sunshine_get_template( 'order/items', array( 'order' => SPC()->frontend->current_order ) );
    }
}

add_action( 'sunshine_order', 'sunshine_display_order_totals', 40 );
function sunshine_display_order_totals() {
    if ( SPC()->frontend->current_order ) {
        sunshine_get_template( 'order/totals', array( 'order' => SPC()->frontend->current_order ) );
    }
}


/* FAVORITES */
add_action( 'sunshine_favorites', 'sunshine_display_favorites' );
function sunshine_display_favorites() {
    if ( !SPC()->customer->get_favorite_count() ) {
        sunshine_get_template( 'favorites/empty' );
    } else {
        sunshine_get_template( 'favorites/favorites', array( 'images' => SPC()->customer->get_favorites() ) );
    }
}

/* ACCOUNT */
add_action( 'sunshine_account', 'sunshine_display_account' );
function sunshine_display_account() {
    sunshine_get_template( 'account' );
}

add_action( 'sunshine_account_menu', 'sunshine_display_account_menu' );
function sunshine_display_account_menu() {
    sunshine_get_template( 'account/menu' );
}

add_action( 'sunshine_account_content', 'sunshine_display_account_content' );
function sunshine_display_account_content() {
    global $wp_query;
    $items = sunshine_get_account_menu_items();
    foreach ( $items as $key => $item ) {
        if ( isset( $wp_query->query_vars[ $item['endpoint'] ] ) ) {
            do_action( 'sunshine_account_' . $key );
            return;
        }
    }
    // Default to dashboard
    sunshine_get_template( 'account/dashboard' );
}

add_action( 'sunshine_account_orders', 'sunshine_display_account_orders' );
function sunshine_display_account_orders() {
    sunshine_get_template( 'account/orders', array( 'orders' => SPC()->customer->get_orders() ) );
}

add_action( 'sunshine_account_addresses', 'sunshine_display_account_addresses' );
function sunshine_display_account_addresses() {
    sunshine_get_template( 'account/addresses' );
}

add_action( 'sunshine_account_profile', 'sunshine_display_account_profile' );
function sunshine_display_account_profile() {
    sunshine_get_template( 'account/profile' );
}
