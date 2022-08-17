<?php
/******************************
	GETTING URLS
******************************/
function sunshine_url( $page = 'home', $args = array() ) {

	if ( !array_key_exists( $page, SPC()->frontend->pages ) ) {
		return false;
	}

	$url = '';
	if ( $page == 'home' ) {
		$url = get_permalink( SPC()->get_option( 'page' ) );
	} elseif ( SPC()->get_option( 'page_' . $page ) ) {
		$url = get_permalink( SPC()->get_option( 'page_' . $page ) );
	}
	if ( isset( $args['query_vars'] ) ) {
		foreach ( $args['query_vars'] as $name => $value ) {
			$param_pairs[] = $name.'='.$value;
		}
	} else {
		$param_pairs = '';
	}
	if ( !empty( $param_pairs ) ) {
		$url .= '?'.join( '&amp;',$param_pairs );
	}
	$url = apply_filters( 'sunshine_url', $url, $args );
	return $url;
}

function sunshine_current_url( $echo = 1 ) {
	$url = $_SERVER["REQUEST_URI"];
	$url = apply_filters( 'sunshine_current_url', $url );
	if ( $echo ) {
		echo esc_url( $url );
	} else {
		return $url;
	}
}

function sunshine_get_page( $page ) {
	return SPC()->frontend->get_page( $page );
}

function sunshine_get_page_permalink( $page ) {
	$page_id = sunshine_get_page( $page );
	if ( $page_id ) {
		return get_permalink( $page_id );
	}
	return false;
}

function sunshine_locate_template( $template, $args = array() ) {
	// See if it is in the theme
	$located_template = locate_template( 'sunshine/' . $template . '.php', false, true, $args );
	if ( $located_template ) {
		return $located_template;
	}
	// Now check default templates path
	$template_path = SUNSHINE_PHOTO_CART_PATH . 'templates/' . $template . '.php';
	if ( file_exists( $template_path ) ) {
		return $template_path;
	}
	return false;
}

function sunshine_get_template( $template, $args = array() ) {
	$located_template = sunshine_locate_template( $template, $args );
	if ( $located_template ) {
		extract( $args );
		include( $located_template );
		return;
	}
	return false;
}

function sunshine_get_template_html( $template, $args = array() ) {
	ob_start();
	sunshine_get_template( $template, $args );
	return ob_get_clean();
}

// TODO: Finish
function sunshine_page_title( $echo = true ) {

	$page_title = apply_filters( 'sunshine_page_title', SPC()->frontend->get_page_title() );

	if ( $echo ) {
		echo $page_title;
	} else {
		return $page_title;
	}

}

function sunshine_action_menu() {
	do_action( 'sunshine_before_action_menu' );
	$menu = array();
	$menu = apply_filters( 'sunshine_action_menu', $menu );
	if ( $menu ) {
		ksort( $menu );
		$menu_html = '<ul class="sunshine--action-menu">';
		foreach ( $menu as $key => $item ) {
			$attributes = '';
			if ( isset( $item['attr'] ) ) {
				foreach ( $item['attr'] as $attr => $value ) {
					$attributes .= ' '.$attr.'="'.$value.'"';
				}
			}
			$menu_html .=  '<li';
			if ( isset( $item['class'] ) ) {
				$menu_html .= ' class="'.$item['class'].' sunshine--action-menu--item-' . $key . '"';
			}
			$menu_html .= '>';
			if ( isset( $item['before_a'] ) )
				$menu_html .= $item['before_a'];
			if ( isset( $item['url'] ) ) {
				$menu_html .= '<a href="'.$item['url'].'"';
				if ( isset( $item['a_class'] ) ) {
					$menu_html .= ' class="'.$item['a_class'].'" ';
				}
				$menu_html .= $attributes;
				if ( isset( $item['target'] ) ) {
					$menu_html .= ' target="'.$item['target'].'" ';
				}
				$menu_html .= '>';
			}
			if ( isset( $item['svg_inline'] ) ) {
				$menu_html .= $item['svg_inline'];
			}
			if ( isset( $item['svg'] ) ) {
				$menu_html .= sunshine_get_svg( $item['svg'] );
			}
			/*
			if ( isset( $item['icon'] ) )
				$menu_html .= '<i class="fa fa-'.$item['icon'].'"></i> ';
			*/
			$menu_html .=  '<span class="sunshine--action-menu--name">'.$item['name'].'</span>';
			if ( isset( $item['url'] ) ) {
				$menu_html .=  '</a>';
			}
			if ( isset( $item['after_a'] ) ) {
				$menu_html .=  $item['after_a'].'</li>';
			}
		}
		$menu_html .= '</ul>';
		//$menu_html = wp_kses_post( $menu_html );
		echo $menu_html;
	}
	do_action( 'sunshine_after_action_menu', $menu );
}

function sunshine_get_galleries() {

	if ( SPC()->get_option( 'gallery_order' ) == 'date_new_old' ) {
		$order = 'date';
		$orderby = 'DESC';
	} elseif ( SPC()->get_option( 'gallery_order' ) == 'date_old_new' ) {
		$order = 'date';
		$orderby = 'ASC';
	} elseif ( SPC()->get_option( 'gallery_order' ) == 'title' ) {
		$order = 'title';
		$orderby = 'ASC';
	} else {
		$order = 'menu_order';
		$orderby = 'ASC';
	}
	$args = array(
		'post_type' => 'sunshine-gallery',
		'post_parent' => 0,
		'orderby' => $order,
		'order' => $orderby,
		'nopaging' => true,
		'update_post_meta_cache' => false,
		'meta_query' => array(
			array(
				'key' => 'access_type',
				'value' => 'url',
				'compare' => '!='
			),
		)
	);
	if ( is_user_logged_in() && !current_user_can( 'sunshine_manage_options' ) ) {
		$args['post_status'] = array( 'publish', 'private' );
		$args['meta_query'][] = array(
			'relation' => 'OR',
			array(
				'key' => 'private_users',
				'value' => '"' . get_current_user_id() . '"',
				'compare' => 'LIKE'
			),
			array(
				'key' => 'private_users',
				'value' => ''
			),
		);
	}
	if ( current_user_can( 'sunshine_manage_options' ) ) {
		unset( $args['post_status'] );
	}

	$galleries = new WP_Query( $args );
	if ( $galleries->have_posts() ) {
		$final_galleries = array();
		foreach ( $galleries->posts as $gallery ) {
			$final_galleries[] = new SPC_Gallery( $gallery );
		}
		return $final_galleries;
	}
	return false;
}

function sunshine_image_class( $image_id, $classes = array(), $echo = true ) {

	$cart_contents = SPC()->cart->get_cart();
	if ( !empty( $cart_contents ) ) {
		foreach ( $cart_contents as $item ) {
			if ( !empty( $item['object_id'] ) && $item['object_id'] == $image_id ) {
				$classes[] = 'sunshine--image--in-cart';
				break;
			}
		}
	}
	$comments = get_comments( array( 'post_id' => $image_id ) );
	if ( $comments ) {
		$classes[] = 'sunshine--image--has-comments';
	}

	if ( SPC()->customer->has_favorite( $image_id ) ) {
		$classes[] = 'sunshine--image--is-favorite';
	}

	$class_names = '';
	$classes = apply_filters( 'sunshine_image_class', $classes, $image_id );
	if ( !empty( $classes ) ) {
		$class_names = join( ' ', $classes );
	}
	$class_names = $class_names;
	if ( $echo ) {
		echo esc_attr( $class_names );
	} else {
		return $class_names;
	}

}

function sunshine_classes( $echo = true ) {
	$classes = array();
	if ( SPC()->frontend->is_image() ) {
		$classes[] = 'sunshine-image';
		$classes[] = 'sunshine--image-' . SPC()->frontend->current_image->get_id();
		if ( SPC()->frontend->current_image->is_favorite() ) {
			$classes[] = 'sunshine--image--is-favorite';
		}
		if ( SPC()->frontend->current_image->in_cart() ) {
			$classes[] = 'sunshine--image--in-cart';
		}
	} elseif ( SPC()->frontend->is_gallery() ) {
		$classes[] = 'sunshine-gallery';
		$classes[] = SPC()->frontend->current_gallery->post_name;
	} elseif ( SPC()->frontend->is_order() ) {
		$classes[] = 'sunshine-order';
	}

	if ( !empty( SPC()->get_option( 'proofing' ) ) && SPC()->get_option( 'proofing' ) == 1 ) {
		$classes[] = 'proofing';
	}

	$html = esc_attr( join( ' ', apply_filters( 'sunshine_classes', $classes ) ) );
	if ( $echo ) {
		echo $html;
	}
	return $html;
}

// BACKWARDS COMPAT
function sunshine_featured_image( $gallery_id = '', $size = 'sunshine-thumbnail', $echo = 1 ) {
	$gallery = new SPC_Gallery( $gallery_id );
	return $gallery->featured_image( $size, $echo );
}

// BACKWARDS COMPAT
function sunshine_featured_image_id( $gallery_id = '' ) {
	global $post;
	if ( empty( $gallery_id ) ) {
		$gallery_id = $post->ID;
	}
	$gallery = new SPC_Gallery( $gallery_id );
	return $gallery->get_featured_image_id();
}

// BACKWARDS COMPAT
function sunshine_is_gallery_expired( $gallery_id = '' ) {
	$gallery = new SPC_Gallery( $gallery_id );
	return $gallery->is_expired();
}

function sunshine_gallery_columns() {
	return SPC()->get_option( 'columns' );
}

function sunshine_gallery_rows() {
	SPC()->get_option( 'rows' );
}

function sunshine_gallery_images_per_page() {
	return SPC()->get_option( 'columns' ) * SPC()->get_option( 'rows' );
}

/* TODO: Check this still works */
function sunshine_get_search_images() {

	$images = array();

	if ( !empty( $_GET['sunshine_search'] ) ) {

		if ( !empty( $_GET['sunshine_gallery'] ) ) {
			$searchable_galleries = array( intval( $_GET['sunshine_gallery'] ) );
			$children_galleries = sunshine_get_children_galleries_of( intval( $_GET['sunshine_gallery'] ) );
			if ( !empty( $children_galleries ) ) {
				$searchable_galleries = array_merge( $searchable_galleries, $children_galleries );
			}
		} else {
			$args = array(
				'post_type' => 'sunshine-gallery',
				'nopaging' => true
			);
			if ( is_user_logged_in() && !current_user_can( 'sunshine_manage_options' ) ) {
				$args['post_status'] = array( 'publish', 'private' );
				$args['meta_query'] = array(
					'relation' => 'OR',
					array(
						'key' => 'sunshine_gallery_private_user',
						'value' => $current_user->ID
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
			$galleries = new WP_Query( $args );
			while ( $galleries->have_posts() ) : $galleries->the_post();
				if ( !post_password_required( get_the_ID() ) ) {
					$searchable_galleries[] = get_the_ID();
				}
			endwhile; wp_reset_postdata();
		}

		$searching = sanitize_text_field( $_GET['sunshine_search'] );

		// Search based on title
		$args = array(
			'post_type' => 'attachment',
			'post_status' => 'any',
			'nopaging' => true,
			'post_parent__in' => $searchable_galleries,
			'orderby' => 'menu_order ID',
			'order' => 'ASC',
			's' => $searching,
		);
		$sunshine_search_query = new WP_Query( $args );
		while ( $sunshine_search_query->have_posts() ) : $sunshine_search_query->the_post();
			$images[] = $sunshine_search_query->post;
		endwhile; wp_reset_postdata();

		// Search based on meta data
		$args = array(
			'post_type' => 'attachment',
			'post_status' => 'any',
			'nopaging' => true,
			'post_parent__in' => $searchable_galleries,
			'orderby' => 'menu_order ID',
			'order' => 'ASC',
			'sunshine_search' => true,
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key' => '_wp_attachment_metadata',
					'value' => '"' . $searching . '"', // This is searching the image_meta>keywords for an exact match
					'compare' => 'LIKE'
				)
			)
		);
		// If spaces, let's search for each word separately
		if ( strpos( $searching, ' ' ) !== false ) {
			$search_terms = explode( ' ', $searching );
			foreach ( $search_terms as $term ) {
				$args['meta_query'][] = array(
					'key' => '_wp_attachment_metadata',
					'value' => '"' . $term . '"',
					'compare' => 'LIKE'
				);
			}
		}
		$sunshine_search_query = new WP_Query( $args );
		while ( $sunshine_search_query->have_posts() ) : $sunshine_search_query->the_post();
			$images[] = $sunshine_search_query->post;
		endwhile; wp_reset_postdata();
	}
	return $images;
}

add_filter( 'posts_where', 'sunshine_search_where', 10, 2 );
function sunshine_search_where( $where, $wp_query_obj ) {
    global $pagenow, $wpdb;

    if ( !empty( $wp_query_obj->query_vars['sunshine_search'] ) ) {
        $where = preg_replace(
            "/\(\s*".$wpdb->posts.".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
            "(".$wpdb->posts.".post_title LIKE $1) OR (".$wpdb->postmeta.".meta_value LIKE $1)", $where );
    }

    return $where;
}

function sunshine_gallery_pagination( $echo = true, $class = "sunshine--pagination" ) {
	global $wp_query;

	if ( empty( SPC()->frontend->current_gallery ) || !SPC()->frontend->current_gallery->can_access() ) {
		return;
	}

	$image_count = SPC()->frontend->current_gallery->get_image_count();

	if ( $image_count > ( SPC()->get_option( 'columns' ) * SPC()->get_option( 'rows' ) ) ) {

		$page_number = ( isset( $_GET['pagination'] ) ) ? intval( $_GET['pagination'] ) : 1;
		$current_gallery_page = array( SPC()->frontend->current_gallery->ID, $page_number );
		SPC()->session->set( 'current_gallery_page', $current_gallery_page );

		$base_url = sunshine_current_url( false );
		$pages = ceil( $image_count / ( SPC()->get_option( 'columns' ) * SPC()->get_option( 'rows' ) ) );
		$html = '<nav class="' . esc_attr( $class ) . '">';
		if ( $page_number > 1 ) {
			$prev_page = $page_number - 1;
			$html .= '<a href="' . $base_url . '?pagination=' . $prev_page . '">' . apply_filters( 'sunshine_pagination_previous_label', '&laquo; ' . __( 'Previous', 'sunshine-photo-cart' ) ) . '</a> ';
		}
		for ( $i = 1; $i <= $pages; $i++ ) {
			$class = ( $page_number == $i || ( $page_number == 0 && $i == 1 ) ) ? 'current' : '';
			$html .= '<a href="' . $base_url . '?pagination=' . $i . '" class="' . $class . '">' . $i . '</a> ';
		}
		if ( $page_number < $pages ) {
			$next_page = $page_number + 1;
			$html .= ' <a href="'. $base_url . '?pagination=' . $next_page . '">' . apply_filters( 'sunshine_pagination_next_label', __( 'Next','sunshine-photo-cart' ) . '  &raquo;' ) . '</a>';
		}

		$html .= '</nav>';

		if ( $echo ) {
			echo $html;
		} else {
			return $html;
		}

	}

}

// BACKWARDS COMPAT
function sunshine_product_class( $product_id = '' ) {
	if ( $product_id ) {
		$product = new SPC_Product( $product_id );
		$product->classes();
	}
}

function sunshine_main_menu( $echo=true ) {
	$menu = array();
	$menu = apply_filters( 'sunshine_main_menu', $menu );
	if ( $menu ) {
		ksort( $menu );
		//$menu_html = '<div>';
		$menu_html =  '<nav id="sunshine--main-menu"><ul>';
		foreach ( $menu as $item ) {
			$attributes = '';
			if ( isset( $item['attr'] ) ) {
				foreach ( $item['attr'] as $attr => $value )
					$attributes .= ' '.$attr.'="'.$value.'"';
			}
			$menu_html .=  '<li';
			if ( isset( $item['class'] ) )
				$menu_html .= ' class="'.$item['class'].'"';
			$menu_html .= '>';
			if ( isset( $item['before_a'] ) )
				$menu_html .= $item['before_a'];
			if ( isset( $item['url'] ) ) {
				$menu_html .= '<a href="'.$item['url'].'"';
				if ( isset( $item['a_class'] ) )
					$menu_html .= ' class="'.$item['a_class'].'" ';
				$menu_html .= $attributes;
				if ( isset( $item['target'] ) )
					$menu_html .= ' target="'.$item['target'].'" ';
				$menu_html .= '>';
			}
			if ( isset( $item['icon'] ) )
				$menu_html .= '<i class="fa fa-'.$item['icon'].'"></i> ';
			if ( isset( $item['name'] ) )
				$menu_html .=  '<span class="sunshine--main-menu--name">'.$item['name'].'</span>';
			if ( isset( $item['url'] ) )
				$menu_html .=  '</a>';
			if ( isset( $item['after_a'] ) )
				$menu_html .=  $item['after_a'];
			$menu_html .= '</li>';
		}
		$menu_html .=  '</ul></nav>';
		
		//$menu_html .=  '</div>';

		//$menu_html = wp_kses_post( $menu_html );
		if ( $echo ) {
			echo wp_kses_post( $menu_html );
		} else {
			return wp_kses_post( $menu_html );
		}

	}
}

function sunshine_image_menu( $image = '', $echo = true ) {

	if ( empty( $image ) ) {
		$image = SPC()->frontend->current_image;
	}
	if ( empty( $image ) ) {
		return false;
	}
	$menu = array();
	$menu = apply_filters( 'sunshine_image_menu', $menu, $image );
	if ( $menu ) {
		ksort( $menu );
		$menu_html = '<nav class="sunshine--image-menu"><ul>';
		foreach ( $menu as $item ) {
			$attributes = '';
			if ( isset( $item['attr'] ) ) {
				foreach ( $item['attr'] as $attr => $value ) {
					$attributes .= ' ' . esc_attr( $attr ) . '="' . esc_attr( $value ) . '"';
				}
			}
			$menu_html .=  '<li';
			if ( isset( $item['class'] ) ) {
				$menu_html .= ' class="' . esc_attr( $item['class'] ) . '"';
			}
			$menu_html .= '>';
			if ( isset( $item['before_a'] ) ) {
				$menu_html .= wp_kses_post( $item['before_a'] );
			}
			if ( isset( $item['url'] ) ) {
				$menu_html .= '<a href="' . esc_url( $item['url'] ) . '"';
				if ( isset( $item['a_class'] ) ) {
					$menu_html .= ' class="' . esc_attr( $item['a_class'] ) . '" ';
				}
				$menu_html .= $attributes;
				if ( isset( $item['target'] ) ) {
					$menu_html .= ' target="' . esc_attr( $item['target'] ) . '" ';
				}
				$menu_html .= '>';
			}
			if ( isset( $item['svg_inline'] ) ) {
				$menu_html .= wp_kses_post( $item['svg_inline'] );
			}
			if ( isset( $item['svg'] ) ) {
				$menu_html .= sunshine_get_svg( $item['svg'] );
			}
			if ( isset( $item['icon'] ) ) {
				$menu_html .= '<i class="' . esc_attr( $item['icon'] ) . '"></i> ';
			}
			if ( isset( $item['name'] ) ) {
				$menu_html .=  '<span class="sunshine--image-menu--name">' . esc_html( $item['name'] ) . '</span>';
			}
			if ( isset( $item['url'] ) ) {
				$menu_html .=  '</a>';
			}
			if ( isset( $item['after_a'] ) ) {
				$menu_html .=  wp_kses_post( $item['after_a'] );
			}
			$menu_html .= '</li>';
		}
		$menu_html .= '</ul></nav>';
		if ( $echo ) {
			echo $menu_html;
		} else {
			return $menu_html;
		}
	}
}

function sunshine_image_status( $image ) {
	$status = array();
	$status[] = '<span class="sunshine--image--is-favorite"></span>';
	$status[] = '<span class="sunshine--image--in-cart"></span>';
	$status[] = '<span class="sunshine--image--has-comments"></span>';
	$status = apply_filters( 'sunshine_image_status', $status, $image );
	if ( !empty( $status ) ) {
		echo '<div class="sunshine--image-status">' . join( '', $status ) . '</div>';
	}
}

function sunshine_image_nav( $image = '' ) {
	if ( !SPC()->frontend->is_image() ) {
		return false;
	}

	if ( empty( $image ) ) {
		$image = SPC()->frontend->current_image;
	}
?>
	<nav id="sunshine--image--nav">
		<span id="sunshine-prev"><?php sunshine_adjacent_image_link( $image, true ); ?></span>
		<span id="sunshine-next"><?php sunshine_adjacent_image_link( $image, false ); ?></span>
	</nav>
<?php
}

function sunshine_get_svg( $file ) {
	if ( file_exists( $file ) ) {
		return file_get_contents( $file );
	} else {
		$path = SUNSHINE_PHOTO_CART_PATH . 'assets/images/' . sanitize_text_field( $file ) . '.svg';
		if ( file_exists( $path ) ) {
			return file_get_contents( $path );
		}
	}
	return false;
}

// BACKWARDS COMPAT
function sunshine_cart_items() {
	return SPC()->cart->get_content();
}

function sunshine_head() {
	do_action( 'sunshine_head' );
}

add_action( 'sunshine_checkout_start_form', 'sunshine_checkout_login_form' );
function sunshine_checkout_login_form() {

	if ( is_user_logged_in() || SPC()->cart->get_item_count() == 0 ) {
		return;
	}
?>

	<div id="sunshine-checkout-login"><?php echo sprintf( __( 'Already have an account? <a href="%s">Click here to login</a>', 'sunshine-photo-cart' ), wp_login_url( sunshine_current_url( false ) ) ); ?></div>

<?php
}

function sunshine_logo() {
	if ( SPC()->get_option( 'template_logo' ) > 0 ) {
		echo wp_get_attachment_image( SPC()->get_option( 'template_logo' ), 'full' );
	} else {
		bloginfo( 'name' );
	}
}

function sunshine_sidebar() {
	// No longer used
	return;
}

function sunshine_adjacent_image_link( $image, $prev = true, $echo = true ) {

	$image_ids = $image->gallery->get_images( array( 'posts_per_page' => -1 ), true );
	if ( count( $image_ids ) <= 1 ) {
		return;
	}

	$link_image_id = 0;

	$current_image_id = 0;
	foreach ( $image_ids as $k => $image_id ) {
		if ( $image_id == $image->get_id() ) {
			$current_image_id = $image_id;
			break;
		}
	}

	// Do we want the one before or after
	if ( $prev ) {
		$k -= 1;
		$direction = 'prev';
	} else {
		$k += 1;
		$direction = 'next';
	}

	// Let's determine which image ID we want here
	if ( array_key_exists( $k, $image_ids ) ) { // Key we are looking for exists!
		$link_image_id = $image_ids[ $k ];
	} else { // Doesn't exist, boo
		// If we are looking for previous, then there are no more in front and we want to then get the last one to circle around backwards
		if ( $prev ) {
			$link_image_id = end( $image_ids );
		} else { // If looking for the next, loop around and get the first one
			$link_image_id = $image_ids[0];
		}
	}

	if ( $link_image_id ) {
		$link_image = new SPC_Image( $link_image_id );
		$link_url = $link_image->get_permalink();
		if ( $prev ) {
			$label = apply_filters( 'sunshine_image_previous_label', '&laquo; ' . __( 'Previous', 'sunshine-photo-cart' ) );
		} else {
			$label = apply_filters( 'sunshine_image_next_label', __( 'Next', 'sunshine-photo-cart' ) . ' &raquo;' );
		}
		$link = '<a href="' . esc_url( $link_url ) . '" class="sunshine-adjacent-link sunshine-adjacent-link-' . esc_attr( $direction ) . '">' . esc_html( $label ) . '</a>';
		if ( $echo ) {
			echo $link;
		} else {
			return $link;
		}
	}

}

function sunshine_breadcrumb( $divider = ' / ', $echo = true ) {

	if ( !empty( SPC()->get_option( 'disable_breadcrumbs' ) ) && SPC()->get_option( 'disable_breadcrumbs' ) ) {
		return;
	}
	$breadcrumb = '<a href="' . get_permalink( SPC()->get_option( 'page' ) ) . '">' . get_the_title( SPC()->get_option( 'page' ) ) . '</a>';
	if ( SPC()->frontend->is_gallery() ) {
		$breadcrumb .= sunshine_breadcrumb_gallery( SPC()->frontend->current_gallery, $divider );
	} elseif ( SPC()->frontend->is_image() ) {
		$breadcrumb .= sunshine_breadcrumb_gallery( SPC()->frontend->current_gallery, $divider );
		$breadcrumb .= $divider . '<a href="' . SPC()->frontend->current_image->get_permalink() . '">' . SPC()->frontend->current_image->get_name() . '</a>';
	}
	$breadcrumb = wp_kses_post( $breadcrumb );
	if ( $echo ) {
		echo $breadcrumb;
		return;
	}
	return $breadcrumb;
}

// Adds the parent gallery this current gallery to breadcrumb, iterates on itself for full hierarchy of galleries
function sunshine_breadcrumb_gallery( $gallery, $divider = ' / ' ) {

	$parent = $gallery->get_parent_gallery();
	if ( empty( $parent ) ) {
		$breadcrumb = $divider . '<a href="' . $gallery->get_permalink() . '">' . $gallery->get_name() . '</a>';
	} else {
		$breadcrumb = sunshine_breadcrumb_gallery( $parent, $divider );
		$breadcrumb .= $divider . '<a href="' . $gallery->get_permalink() . '">' . $gallery->get_name() . '</a>';
	}
	return $breadcrumb;

}

function sunshine_gallery_password_form( $echo = true ) {
	$form = '<form method="post" action="" id="sunshine-gallery-password">
			<input type="text" name="sunshine_gallery_password" />
			<input type="submit" value="' . esc_attr__( 'Go', 'sunshine-photo-cart' ) . '" class="sunshine-button" />
	</form>';
	if ( $echo ) {
		echo $form;
	} else {
		return $form;
	}
}

/* TODO: Redo */
function sunshine_gallery_expiration_notice() {
	if ( isset( SPC()->frontend->current_gallery->ID ) ) {
		$end_date = get_post_meta( SPC()->frontend->current_gallery->ID, 'sunshine_gallery_end_date', true );
		if ( $end_date != '' && $end_date > current_time( 'timestamp' ) ) {
			echo '<div id="sunshine--gallery--expiration-notice">';
			echo wp_kses_post( apply_filters( 'sunshine_gallery_expiration_notice', sprintf( __( 'This gallery is set to expire on <strong>%s</strong>','sunshine-photo-cart' ), date_i18n( get_option( 'date_format' ).' @ '.get_option( 'time_format' ), $end_date ) ) ) );
			echo '</div>';
		}
	}
}

/*
* 	Show a search form
*
*	@return void
*/
function sunshine_search_form( $gallery = '', $echo = true ) {

	if ( $gallery ) {
		$action_url = get_permalink( $gallery );
	} else {
		$action_url = get_permalink( SPC()->get_option( 'page' ) );
	}
	$form = '<form method="get" action="' . esc_url( $action_url ) . '" class="sunshine--search">
			<input type="hidden" name="sunshine_gallery" value="' . esc_attr( $gallery ) . '" />
			<input type="text" name="sunshine_search" />
			<input type="submit" value="' . __( 'Search', 'sunshine-photo-cart' ) . '" class="sunshine-button" />
	</form>';
	if ( $echo )
		echo $form;
	else
		return $form;
}


add_action( 'sunshine_after_cart', 'sunshine_gallery_return', 5 );
add_action( 'sunshine_after_favorites', 'sunshine_gallery_return', 5 );
function sunshine_gallery_return() {
	$last_gallery = SPC()->session->get( 'last_gallery' );
	if ( !empty( $last_gallery ) ) {
		$gallery = new SPC_Gallery( $last_gallery );
		if ( empty( $gallery ) ) {
			return;
		}
		$url = $gallery->get_permalink();
		$current_gallery_page = SPC()->session->get( 'current_gallery_page' );
		if ( !empty( $current_gallery_page ) ) {
			$url = add_query_arg( 'pagination', $current_gallery_page[1], $url );
		}
?>
	<div id="sunshine--cart--gallery-return">
		<a href="<?php echo esc_url( $url ); ?>"><?php echo sprintf( __( 'Return to gallery "%s"', 'sunshine-photo-cart' ), esc_html( $gallery->get_name() ) ); ?></a>
	</div>
<?php
	}
}

?>
