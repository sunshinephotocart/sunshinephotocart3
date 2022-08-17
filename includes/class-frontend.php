<?php
class SPC_Frontend {

	public $current_gallery;
	public $current_image;
	public $current_order;
	private $output;
	public $pages = array();
	private $page_title;

	function __construct() {

		$this->set_pages();

		add_action( 'wp', array( $this, 'set_view_values' ) );
		add_action( 'wp', array( $this, 'donotcachepage' ) );
		add_action( 'wp', array( $this, 'require_login' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_cssjs' ) );
		//add_action( 'wp', array( $this, 'redirect_to_endpoint_urls' ) );
		add_action( 'wp', array( $this, 'admin_bar' ) );
		add_action( 'wp', array( $this, 'remove_canonical' ), 99 );
		//add_action( 'wp_print_styles', array( $this, 'clear_queue' ) );
		add_filter( 'body_class',array( $this, 'body_class' ) );
		//add_filter( 'the_password_form', array( $this, 'gallery_password_form' ) );
		add_filter( 'the_title', array( $this, 'the_title' ), 10, 2 );
		add_filter( 'protected_title_format', array( $this, 'protected_title_format' ), 10, 2 );
		add_filter( 'private_title_format', array( $this, 'private_title_format' ), 10, 2 );
		add_filter( 'pre_comment_approved', array( $this, 'order_comment_auto_approve' ) , 99, 2 );
		add_filter( 'sunshine_main_menu', array( $this, 'build_main_menu' ), 10, 1 );
		add_filter( 'sunshine_image_menu', array( $this, 'build_image_menu' ), 10, 2 );
		add_action( 'wp_head', array( $this, 'required_meta' ), 1 );
		add_action( 'wp_footer', array( $this, 'protection' ) );
		add_action( 'wp_footer', array( $this, 'version_output' ), 9999 );
		add_action( 'template_redirect', array( $this, 'can_view_gallery' ) );
		add_action( 'template_redirect', array($this, 'can_view_image'));
		add_action( 'template_redirect', array( $this, 'can_view_order' ) );
		add_action( 'template_redirect', array( $this, 'can_use_cart' ) );
		add_filter( 'comments_open', array($this, 'disable_comments'), 10 , 2);
		add_filter( 'nav_menu_css_class', array( $this, 'add_class_to_wp_nav_menu' ), 10, 2 );
		add_filter( 'previous_post_link', array( $this, 'disable_prev_next' ), 99, 5 );
		add_filter( 'next_post_link', array( $this, 'disable_prev_next' ), 99, 5 );
		add_action( 'template_redirect', array( $this, 'order_invoice_pdf' ), 1 );
		add_action( 'wp', array( $this, 'process_access' ) );

		// Get selected theme's functions
		$theme = SPC()->get_option( 'theme' );
		$theme_functions_file = SUNSHINE_PHOTO_CART_PATH . 'themes/' . $theme . '/functions.php';
		if ( file_exists( $theme_functions_file ) ) {
			include_once( $theme_functions_file );
		}

		add_filter( 'template_include', array( $this, 'template_include' ), 999 );

	}

	private function set_pages() {
		$pages = array(
			'home' => SPC()->get_option( 'page' ),
			'account' => SPC()->get_option( 'page_account' ),
			'cart' => SPC()->get_option( 'page_cart' ),
			'checkout' => SPC()->get_option( 'page_checkout' ),
			'favorites' => SPC()->get_option( 'page_favorites' ),
			'terms' => SPC()->get_option( 'page_terms' ),
		);
		$this->pages = apply_filters( 'sunshine_pages', $pages );
	}

	public function get_page( $page ) {
		if ( is_numeric( $page ) ) {
			if ( in_array( $page, $this->pages ) ) {
				return $this->pages[ array_search( $page, $this->pages ) ];
			}
		} else {
			if ( array_key_exists( $page, $this->pages ) ) {
				return $this->pages[ $page ];
			}
		}
		return false;
	}

	function template_include( $template ) {

		if ( isset( $_GET['sunshine_search'] ) ) {
			$template = sunshine_locate_template( 'search-results' );
		} elseif ( $this->is_image() ) {
			$this->page_title = $this->current_image->get_name();
			$template = sunshine_locate_template( 'image' );
		} elseif ( $this->is_gallery() ) {
			$this->page_title = $this->current_gallery->get_name();
			$template = sunshine_locate_template( 'gallery' );
		} elseif ( $this->is_order() ) {
			$this->page_title = $this->current_order->get_name();
			$template = sunshine_locate_template( 'order' );
		}

		if ( is_sunshine_page( 'checkout' ) && SPC()->get_option( 'checkout_standalone' ) ) {
			$template = sunshine_locate_template( 'checkout-standalone' );
		}

		return $template;

	}

	function get_page_title() {

		if ( isset( $_GET['sunshine_search'] ) ) {
			$template = __( 'Search results', 'sunshine-photo-cart' );
		} elseif ( $this->is_image() ) {
			$this->page_title = $this->current_image->get_name();
		} elseif ( $this->is_gallery() ) {
			$this->page_title = $this->current_gallery->get_name();
		} elseif ( $this->is_order() ) {
			$this->page_title = $this->current_order->get_name();
		}

		return $this->page_title;
	}

	/*
	TODO:
	Switch theme to be Sunshine's only on Sunshine pages if selected in settings
	*/

	public function set_view_values( $wp ) {
		global $post, $wp_query, $wpdb;

		// Check by post type
		if ( is_attachment() ) {

			if ( $post->post_parent ) {
				$parent = get_post( $post->post_parent );
				if ( get_post_type( $parent ) == 'sunshine-gallery' ) {
					$this->current_gallery = new SPC_Gallery( $parent );
					$this->current_image = new SPC_Image( $post );
				}
			}

		} elseif ( is_singular( 'sunshine-gallery' ) ) {
			$this->current_gallery = new SPC_Gallery( $post );
		} elseif ( is_singular( 'sunshine-order' ) ) {
			$this->current_order = new SPC_Order( $post );
		}

		if ( !empty( $this->current_gallery ) ) {
			$this->set_last_viewed_gallery( $this->current_gallery->get_id() );
		}

	}

	public function set_gallery( $gallery ) {
		$gallery = sunshine_get_gallery( $gallery );
		if ( empty( $gallery ) ) {
			return;
		}
		$this->current_gallery = $gallery;
	}

	public function set_last_viewed_gallery( $gallery_id ) {
		SPC()->session->set( 'last_gallery', $gallery_id );
	}

	public function is_image() {
		if ( !empty( $this->current_image ) ) {
			return true;
		}
		return false;
	}

	public function is_gallery() {
		if ( empty( $this->current_image ) && !empty( $this->current_gallery ) ) {
			return true;
		}
		return false;
	}

	public function is_order() {
		if ( !empty( $this->current_order ) ) {
			return true;
		}
		return false;
	}

	public function image_permalink( $url, $post_id ) {

		// See if we are in a current gallery
		if ( $this->is_gallery() ) {

		}

		// Get current gallery image IDs

		// If in the list of gallery IDs, then build off this gallery URL base

		$post_obj = get_post( $post_id );

		if ( empty( $post_obj ) ) {
			return $url;
		}

		$parent = get_post( $post_obj->post_parent );
		if ( 2 == 1 ) {
			$url = trailingslashit( get_permalink( $parent ) . '/' . $post_obj->post_name );
		}

		return $url;

	}

	function admin_bar() {
		if ( !current_user_can( 'sunshine_manage_options' ) ) {
			show_admin_bar( apply_filters( 'sunshine_admin_bar', false ) );
		}
	}

	function require_login() {

		if ( is_page( SPC()->get_option( 'page_account' ) ) && !is_user_logged_in() ) {
			wp_redirect( apply_filters( 'sunshine_login_url', wp_login_url( sunshine_current_url( false ) ) ) );
			exit;
		}

	}

	/*
	TODO: REMOVE once we have seperate Sunshine themes
	*/
	public function locate_template( $template ) {
		// Check in theme
		$located_template = locate_template( 'sunshine/' . $template . '.php' );
		if ( $located_template ) {
			return $located_template;
		}

		// Now check default path
		if ( file_exists( SUNSHINE_PHOTO_CART_PATH . 'themes/' . SPC()->get_option( 'theme' ) . '/' . $template . '.php' ) ) {
			return SUNSHINE_PHOTO_CART_PATH . 'themes/' . SPC()->get_option( 'theme' ) . '/' . $template . '.php';
		}

		return false;
	}

	/*
	TODO: REMOVE once we have seperate Sunshine themes
	*/
	public function get_template_html( $template ) {

		if ( file_exists( get_stylesheet_directory() . '/sunshine/' . $template . '.php' ) ) {
			$file = get_stylesheet_directory() . '/sunshine/' . $template . '.php';
		} else {
			$file = SUNSHINE_PHOTO_CART_PATH . 'themes/' . SPC()->get_option( 'theme' ) . '/' . $template . '.php';
		}

		ob_start();
			load_template( $file );
			$output = ob_get_contents();
		ob_end_clean();
		return $output;

	}

	function the_title( $title, $id = '' ) {
		global $post;

		if ( !in_the_loop() && $id == SPC()->get_option( 'page_cart' ) && SPC()->get_option( 'theme' ) == 'theme' ) {
			$count = SPC()->cart->get_item_count();
			$title = $title . ' <span class="sunshine-count sunshine-cart-count">' . $count . '</span>';
		}

		/*
		if ( isset( $_GET['sunshine_search'] ) && in_the_loop() && $id == SPC()->get_option( 'page' ) ) {
			$title = __( 'Search for','sunshine-photo-cart' ).' "'.sanitize_text_field( $_GET['sunshine_search'] ).'"';
		}
		*/

		return $title;
	}

	function protected_title_format( $format, $post ) {
		if ( $post->post_type == 'sunshine-gallery' ) {
			$format = '%s';
		}
		return $format;
	}

	function private_title_format( $format, $post ) {
		if ( $post->post_type == 'sunshine-gallery' ) {
			$format = '%s';
		}
		return $format;
	}

	function disable_comments( $open, $post_id ) {
		$post = get_post( $post_id );
		if ( $post->post_type == 'attachment' && get_post_type( $post->post_parent ) == 'sunshine-gallery' ) {
			$gallery = new SPC_Gallery( $post->post_parent );
			if ( $gallery->allow_comments() ) {
				return true;
			}
			return false;
		} elseif ( $post->post_type == 'sunshine-order' ) {
			return false; // No comments on any order
		}
		return $open;
	}

	// Load select theme's stylesheet
	public function frontend_cssjs() {
		if ( is_sunshine( 'enqueue_scripts' ) ) {
			if ( empty( SPC()->get_option( 'disable_sunshine_css' ) ) || !SPC()->get_option( 'disable_sunshine_css' ) ) {
				if ( file_exists( get_stylesheet_directory() . '/sunshine/style.css' ) ) {
					$css_file = get_stylesheet_directory() . '/sunshine/style.css';
				} else {
					$css_file = SUNSHINE_PHOTO_CART_URL . 'themes/' . SPC()->get_option( 'theme' ) . '/style.css';
				}
				wp_enqueue_style( 'sunshine-photo-cart', $css_file );
				wp_enqueue_style( 'sunshine-photo-cart-icons', SUNSHINE_PHOTO_CART_URL . 'assets/css/icons.css' );
			}
			wp_enqueue_script( 'sunshine-photo-cart', SUNSHINE_PHOTO_CART_URL . 'assets/js/sunshine.js', array( 'jquery' ), SUNSHINE_PHOTO_CART_VERSION );
			wp_localize_script( 'sunshine-photo-cart', 'sunshine_photo_cart', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		}
	}

	// Add to body_class
	function body_class( $classes ) {
		if ( is_sunshine() ) {
			$classes[] = 'sunshine-photo-cart';
		}
		if ( is_sunshine_page( 'favorites' ) ) {
			$classes[] = 'sunshine--favorites';
		}
		if ( is_active_sidebar( 'sunshine-sidebar' ) ) {
			$classes[] = 'sunshine-sidebar';
		}
		if ( SPC()->get_option( 'dark_mode' ) ) {
			$classes[] = 'sunshine--dark';
		}
		if ( SPC()->get_option( 'checkout_standalone' ) ) {
			$classes[] = 'sunshine--checkout--standalone';
		}
		return $classes;
	}

	function build_main_menu( $menu ) {

		if ( is_sunshine_page( 'checkout' ) && SPC()->get_option( 'checkout_standalone' ) ) {
			return false;
		}

		if ( is_user_logged_in() ) {
			$menu[110] = array(
				'name' => __( 'Logout','sunshine-photo-cart' ),
				'url' => wp_logout_url( ( SPC()->get_option( 'hide_galleries_link' ) ) ? '' : sunshine_url( 'home' ) ),
				'class' => 'sunshine-logout'
			);

			$selected = '';
			if ( is_sunshine_page( 'account' ) ) {
				$selected = ' sunshine--selected';
			}
			$menu[100] = array(
				'name' => get_the_title( SPC()->get_option( 'page_account' ) ),
				'url' => sunshine_url( 'account' ),
				'class' => 'sunshine--account' . $selected
			);

			if ( SPC()->get_option( 'page_favorites' ) && !SPC()->get_option( 'disable_favorites' ) ) {

				$favorites = SPC()->customer->get_favorite_count();
				$count = '';
				$count = '<span class="sunshine--count sunshine--favorites--count">' . $favorites . '</span>';

				$selected = '';
				if ( is_sunshine_page( 'favorites' ) ) {
					$selected = ' sunshine--selected';
				}
				$menu[25] = array(
					'name' => __( 'Favorites','sunshine-photo-cart' ),
					'after_a' => $count,
					'url' => sunshine_url( 'favorites' ),
					'class' => 'sunshine--favorites' . $selected
				);
			}

		} else {
			$menu[100] = array(
				'name' => __( 'Login', 'sunshine-photo-cart' ),
				'url' => '#login',
				'class' => 'sunshine--login',
				'a_class' => 'sunshine--open-modal',
				'attr' => array(
					'data-action' => 'sunshine_modal_display_login',
				)
			);
			$menu[110] = array(
				'name' => __( 'Sign Up', 'sunshine-photo-cart' ),
				'url' => '#signup',
				'class' => 'sunshine--signup',
				'a_class' => 'sunshine--open-modal',
				'attr' => array(
					'data-action' => 'sunshine_modal_display_signup',
				)
			);
		}

		if ( empty( SPC()->get_option( 'hide_galleries_link' ) ) || SPC()->get_option( 'hide_galleries_link' ) != 1 ) {

			$selected = '';
			if ( is_sunshine_page( 'home' ) ) {
				$selected = ' sunshine--selected';
			}
			$menu[10] = array(
				'name' => get_the_title( sunshine_get_page( 'home' ) ),
				'url' => sunshine_url( 'home' ),
				'class' => 'sunshine--galleries' . $selected
			);
		}

		if ( !SPC()->get_option( 'proofing' ) ) {

			$cart_count = '';
			$cart_count = '<span class="sunshine--count sunshine--cart--count">' . SPC()->cart->get_item_count() . '</span>';

			$selected = '';
			$menu[40] = array(
				'name' => get_the_title( sunshine_get_page( 'cart' ) ),
				'url' => sunshine_url( 'cart' ),
				'class' => 'sunshine--cart',
				'after_a' => $cart_count
			);

			$selected = '';
			if ( is_sunshine_page( 'checkout' ) ) {
				$selected = ' sunshine--selected';
			}
			$menu[50] = array(
				'name' => get_the_title( sunshine_get_page( 'checkout' ) ),
				'url' => sunshine_url( 'checkout' ),
				'class' => 'sunshine--checkout' . $selected
			);

		}

		return $menu;
	}

	/*
	function build_action_menu( $menu ) {
		global $wp_query, $post, $sunshine;

		// Single gallery page
		if ( isset( SPC()->frontend->current_gallery->ID ) ) {

			if ( SPC()->frontend->current_gallery->post_parent != 0 ) { // If sub gallery
				$menu[10] = array(
					'icon' => 'undo',
					'name' => __( 'Return to', 'sunshine-photo-cart' ) . ' ' . get_the_title( SPC()->frontend->current_gallery->post_parent ),
					'url' => get_permalink( SPC()->frontend->current_gallery->post_parent ),
				);
			}
		}

		// Single image page
		if ( !empty( SPC()->frontend->current_image ) ) {
			$menu[10] = array(
				//'icon' => 'undo',
				'name' => __( 'Return to', 'sunshine-photo-cart' ) . ' ' . SPC()->frontend->current_gallery->get_name(),
				'url' => SPC()->frontend->current_gallery->get_permalink(),
			);
		}

		// Favorites
		if ( !SPC()->get_option( 'disable_favorites' ) && !empty( SPC()->frontend->current_image ) ) {
			if ( is_user_logged_in() ) {
				$menu[15] = array(
					'svg' => 'favorite',
					'name' => __( 'Add to favorites','sunshine-photo-cart' ),
					'url' => '#',
					'class' => 'sunshine--favorites',
					'a_class' => 'sunshine--add-to-favorites',
					'attr' => array(
						'data-image-id' => SPC()->frontend->current_image->get_id()
					)
				);
				if ( SPC()->customer->has_favorite( SPC()->frontend->current_image->get_id() ) ) {
					$menu[15]['a_class'] .= ' sunshine-favorite';
					$menu[15]['name'] = __( 'Remove from favorites', 'sunshine-photo-cart' );
				}
			} else {
				$menu[15] = array(
					'svg' => 'favorite',
					'class' => 'sunshine--favorites',
					'name' => __( 'Add to favorites', 'sunshine-photo-cart' ),
					'url' => wp_login_url( add_query_arg( 'sunshine_favorite', SPC()->frontend->current_image->get_id(), sunshine_current_url( false ) ) ),
					'a_class' => 'sunshine--add-to-favorites',
				);
			}
		}

		if ( !SPC()->get_option( 'disable_favorites' ) && is_sunshine_page( 'favorites' ) && !empty( SPC()->customer->get_favorites() ) ) {
			$nonce = wp_create_nonce( 'sunshine_clear_favorites' );
			$menu[50] = array(
				'svg' => 'delete',
				'name' => __( 'Remove All Favorites','sunshine-photo-cart' ),
				'url' => add_query_arg( array( 'clear_favorites' => 1, 'nonce' => $nonce ), sunshine_url( 'favorites' ) )
			);
			$nonce = wp_create_nonce( 'sunshine_submit_favorites' );
			$menu[60] = array(
				'svg' => 'envelope',
				'name' => __( 'Submit Favorites', 'sunshine-photo-cart' ),
				'url' => add_query_arg( array( 'submit_favorites' => 1, 'nonce' => $nonce ), sunshine_url( 'favorites' ) )
			);
		}

		return $menu;
	}
	*/

	function build_image_menu( $menu, $image ) {

		if ( SPC()->frontend->is_image() ) {
			$menu[0] = array(
				'name' => __( 'Return to gallery', 'sunshine-photo-cart' ),
				'class' => 'sunshine--return',
				'url' => $image->get_gallery()->get_permalink(),
			);
		}

		if ( !SPC()->get_option( 'disable_favorites' ) && $image->allow_favorites() ) {

			if ( is_user_logged_in() ) {
				$menu[10] = array(
					'name' => __( 'Favorite', 'sunshine-photo-cart' ),
					'class' => 'sunshine--favorite',
					'url' => $image->get_permalink(),
					'a_class' => 'sunshine--add-to-favorites',
					'attr' => array(
						'data-image-id' => $image->get_id()
					)
				);
			} else {
				$menu[10] = array(
					'name' => __( 'Favorite', 'sunshine-photo-cart' ),
					'url' => '#favorite',
					'class' => 'sunshine--favorite',
					'a_class' => 'sunshine--open-modal',
					'attr' => array(
						'data-image-id' => $image->get_id(),
						'data-action' => 'sunshine_modal_display_require_login',
						'data-after' => 'sunshine_add_favorite'
					)
				);
			}

		}

		if ( !$image->products_disabled() && !SPC()->get_option( 'proofing' ) ) {
			$menu[20] = array(
				'name' => __( 'Purchase options', 'sunshine-photo-cart' ),
				'url' => '#purchase',
				'class' => 'sunshine--purchase',
				'a_class' => 'sunshine--open-modal',
				'attr' => array(
					'data-image-id' => $image->get_id(),
					'data-action' => 'sunshine_modal_display_add_to_cart'
				)
			);
		}

		if ( $image->allow_comments() ) {
			$after_a = '';
			$comment_count = $image->get_comment_count();
			if ( $comment_count > 0 ) {
				$after_a = '<span class="sunshine--count sunshine--comment-count">' . esc_html( $comment_count ) . '</span>';
			}
			$menu[30] = array(
				'name' => __( 'Comments', 'sunshine-photo-cart' ),
				'url' => $image->get_permalink() . '#comments',
				'class' => 'sunshine--comments',
				'after_a' => $after_a,
				'a_class' => 'sunshine--open-modal',
				'attr' => array(
					'data-image-id' => $image->get_id(),
					'data-action' => 'sunshine_modal_display_comments'
				)
			);
		}

		if ( !SPC()->get_option( 'disable_sharing' ) && $image->allow_sharing() ) {
			$menu[50] = array(
				'name' => __( 'Share', 'sunshine-photo-cart' ),
				'url' => $image->get_permalink() . '#share',
				'class' => 'sunshine--share',
				'a_class' => 'sunshine--open-modal',
				'attr' => array(
					'data-image-id' => $image->get_id(),
					'data-action' => 'sunshine_modal_display_share'
				)
			);
		}

		return $menu;
	}

	function meta() {

		// TODO: Is this required now that we are using default attachment URL by WordPress? Can SEO plugins handle this?
		// Image page
		if ( $this->is_image() ) {

			if ( !$this->current_gallery->password_required() ) {

				$image = wp_get_attachment_image_src( $this->current_image->get_id(), apply_filters( 'sunshine_image_size', 'full' ) );

				echo '<meta property="og:title" content="' . apply_filters( 'sunshine_open_graph_image_title', $this->current_image->get_name() . ' by ' . get_bloginfo( 'name' ) ) . '"/>
			    <meta property="og:type" content="website"/>
			    <meta property="og:url" content="' . trailingslashit( get_permalink( $this->current_image->ID ) ) . '"/>
			    <meta property="og:site_name" content="' . get_bloginfo( 'name' ) . '"/>
			    <meta property="og:description" content="' . sprintf( __( 'A photo from the gallery %s by %s', 'sunshine-photo-cart' ), strip_tags( $this->current_image->gallery->get_name() ), get_bloginfo( 'name' ) ) . '"/>';
				if ( is_ssl() ) {
					$http_url = str_replace( 'https', 'http', $image[0] );
					echo '<meta property="og:image" content="' . esc_url( $http_url ) . '"/>
					<meta property="og:image:url" content="' . esc_url( $http_url ) . '"/>
					<meta property="og:image:secure_url" content="' . esc_url( $image[0] ) . '"/>';
				} else {
					echo '<meta property="og:image" content="' . esc_url( $image[0] ) . '"/>
					<meta property="og:image:url" content="' . esc_url( $image[0] ) . '"/>';
				}
				echo '<meta property="og:image:type" content="image/jpeg" />
				<meta property="og:image:height" content="' . esc_url( $image[2] ) . '"/>
			    <meta property="og:image:width" content="' . esc_url( $image[1] ) . '"/>';

			} else {

				echo '<meta name="robots" content="noindex" />';

			}


		} elseif ( $this->is_gallery() ) {

			$image_id = $this->current_gallery->get_featured_image_id();
			if ( $image_id ) {
				$image = wp_get_attachment_image_src( $image_id, apply_filters( 'sunshine_image_size', 'full' ) );
				if ( $image ) {
					echo '<meta property="og:title" content="' . esc_attr( apply_filters( 'sunshine_open_graph_gallery_title', $this->current_gallery->get_name() . ' by ' . get_bloginfo( 'name' ) ) ) . '"/>
				    <meta property="og:type" content="website"/>
				    <meta property="og:url" content="' . esc_url( trailingslashit( $this->current_gallery->get_permalink() ) ) . '"/>
				    <meta property="og:image" content="' . esc_url( $image[0] ) . '"/>
					<meta property="og:image:height" content="' . esc_url( $image[2] ) . '"/>
					<meta property="og:image:width" content="' . esc_url( $image[1] ) . '"/>
				    <meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '"/>
				    <meta property="og:description" content="' . esc_attr( sprintf( __( 'Photo gallery %s by %s', 'sunshine-photo-cart' ), get_the_title( $this->current_gallery->post_parent ), get_bloginfo( 'name' ) ) ) . '"/>';
				}
			}

		}

	}

	function required_meta() {

		// Block search engines from all orders
		if ( $this->is_order() ) {
			echo '<meta name="robots" content="noindex" />';
		}
		// Block search engines going to galleries if selected
		if ( !empty( SPC()->get_option( 'hide_galleries' ) ) && $this->is_gallery() ) {
			echo '<meta name="robots" content="noindex" />';
		}
	}

	function protection() {

		if ( is_sunshine() && SPC()->get_option( 'disable_right_click' ) ) {
?>
			<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery(document).bind("contextmenu",function(e){ return false; });
				jQuery("img").mousedown(function(){ return false; });
				document.body.style.webkitTouchCallout='none';
			});
			</script>
		<?php
		}

	}

	public function version_output() {
		if ( is_sunshine() ) {
			echo '<!-- Powered by Sunshine Photo Cart ' . SUNSHINE_PHOTO_CART_VERSION . ' -->';
		}
	}

	function can_view_gallery() {
		global $post, $current_user;
		if ( $this->current_gallery && $this->current_gallery->post_status == 'private' && !current_user_can( 'sunshine_manage_options' ) ) {
			$allowed_users = get_post_meta( $this->current_gallery->ID, 'sunshine_gallery_private_user' );
			if ( !in_array( $current_user->ID, $allowed_users ) ) {
				wp_redirect( add_query_arg( 'sunshine_login_notice','private_gallery',wp_login_url( sunshine_current_url( false ) ) ) );
				exit;
			}
		}
		if ( $this->current_gallery && get_post_meta( $this->current_gallery->ID, 'sunshine_gallery_access', true ) == 'account' && !is_user_logged_in() ) {
			wp_redirect( add_query_arg( 'sunshine_login_notice','gallery_requires_login',wp_login_url( sunshine_current_url( false ) ) ) );
			exit;
		}
	}

	function can_view_image() {
		if ( $this->is_image() && !$this->current_image->can_view() ) {
			wp_die( __( 'Sorry, you are not allowed to view this image','sunshine-photo-cart' ), __( 'Access denied','sunshine-photo-cart' ), array( 'back_link' => true ) );
			exit;
		}
	}

	function can_view_order() {
		global $wp_query, $current_user;
		if ( isset( $this->current_order->ID ) ) {
			$order_customer_id = get_post_meta( $this->current_order->ID, '_sunshine_customer_id', true );
			if ( current_user_can( 'sunshine_manage_options' ) ) {
				// Admin, always let through
			} elseif ( $order_customer_id && $current_user->ID != $order_customer_id ) {
				wp_die( __( 'Sorry, you are not allowed to access this order information','sunshine-photo-cart' ), __( 'Access denied','sunshine-photo-cart' ), array( 'back_link'=>true ) );
				exit;
			} elseif ( !$order_customer_id && SunshineSession::instance()->order_id != $this->current_order->ID ) {
				wp_die( __( 'Sorry, you are not allowed to access this order information','sunshine-photo-cart' ), __( 'Access denied','sunshine-photo-cart' ), array( 'back_link'=>true ) );
			}
		}
	}

	function can_use_cart() {

		if ( ( !empty( SPC()->get_option( 'proofing' ) ) && SPC()->get_option( 'proofing' ) ) && ( is_page( SPC()->get_option( 'page_cart' ) ) || is_page( SPC()->get_option( 'page_checkout' ) ) ) ) {
			wp_redirect( get_permalink( SPC()->get_option( 'page' ) ) );
			exit;
		}

	}

	function hide_order_comments( $template ) {
		global $post;
		if ( $post->post_type == 'sunshine-order' ) {
			return;
		}
		return $template;
	}

	function remove_image_commenting( $open, $post_id ) {
		global $post;
		if ( $post->post_type == 'attachment' ) {
			return false;
		}
		return $open;
	}

	function remove_parent_classes( $class ) {
		return ( $class == 'current_page_item' || $class == 'current_page_parent' || $class == 'current_page_ancestor'  || $class == 'current-menu-item' ) ? FALSE : TRUE;
	}

	function add_class_to_wp_nav_menu( $classes, $item ) {

		switch ( get_post_type() ) {
			case 'sunshine-gallery':
				$classes = array_filter( $classes, array( $this, 'remove_parent_classes' ) );
				if ( $item->object_id == SPC()->get_option( 'page' ) )
					$classes[] = 'current_page_parent';
				break;
			case 'sunshine-order':
				$classes = array_filter( $classes, array( $this, 'remove_parent_classes' ) );
				if ( $item->object_id == SPC()->get_option( 'page' ) )
					$classes[] = 'current_page_parent';
				break;
		}
		return $classes;
	}

	function check_expirations() {

		if ( !empty( $this->current_image ) && $this->current_gallery->is_expired() ) { // If looking at image but gallery is expired, redirect to gallery
			wp_redirect( get_permalink( $this->current_gallery->get_permalink() ) );
			exit;
		} elseif ( is_page( SPC()->get_option( 'page_cart' ) ) ) { // Remove items from cart if gallery is expired
			$cart = SPC()->cart->get_cart();
			$removed_items = false;
			if ( !empty( $cart ) ) {
				foreach ( $cart as $key => $item ) {
					if ( !empty( $item['image_id'] ) ) {
						$image = new SPC_Image( $item['image_id'] );
						if ( empty( $image->gallery->get_id() ) || $image->gallery->is_expired() ) {
							SPC()->cart->delete_item( $key );
							$removed_items = true;
						}
					}
				}
			}
			if ( $removed_items ) {
				SPC()->notices->add( __( 'Images in your cart have been removed because they are no longer available', 'sunshine-photo-cart' ) );
				wp_redirect( get_permalink( SPC()->get_option( 'page_cart' ) ) );
				exit;
			}
		}

	}

	function donotcachepage() {
		if ( is_sunshine() && !defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}
	}

	function remove_canonical() {
		if ( $this->is_gallery() ) {
			remove_action( 'wp_head', 'rel_canonical' );
		}
	}

	function disable_prev_next( $output, $format, $link, $post, $adjacent ) {
		if ( !empty( $post ) && $post->post_type == 'sunshine-order' ) {
			return false;
		}
		return $output;
	}

	function order_invoice_pdf() {

		if ( $this->is_order() && isset( $_GET['order_invoice'] ) && wp_verify_nonce( $_GET['order_invoice'], 'order_invoice_' . $this->current_order->get_id() ) ) {

			$output = sunshine_get_template_html( 'invoice/order', array( 'order' => $this->current_order ) );
			$output = str_replace( trailingslashit( get_bloginfo( 'url' ) ), ABSPATH, $output );

			require_once SUNSHINE_PHOTO_CART_PATH . 'includes/vendor/autoload.php';

			// Set it up
			$mpdf = new \Mpdf\Mpdf( array(
				'format' => 'Letter',
				'mode' => 'utf-8',
				'fontdata' => [
					'roboto' => [
						'R' => 'Roboto-Regular.ttf',
						'B' => 'Roboto-Bold.ttf',
					]
				],
				'default_font' => 'roboto'
			) );

			// Output PDF to browser
			$mpdf->shrink_tables_to_fit = 1;
			$mpdf->SetTitle( $this->current_order->get_name() );
			$mpdf->SetAuthor( get_bloginfo( 'sitename' ) );
			$mpdf->setAutoTopMargin = 'stretch';
			$mpdf->setAutoBottomMargin = 'stretch';
			$mpdf->WriteHTML( $output );
			$mpdf->Output( sanitize_file_name( apply_filters( 'sunshine_order_invoice_filename', $this->current_order->get_name(), $this->current_order ) ), 'I' );
			exit;

		}

	}

	function process_access() {

		if ( !isset( $_POST['sunshine_gallery_access'] ) || !isset( $_POST['sunshine_gallery_id'] ) ) {
			return false;
		}

		$redirect = false;
		$gallery_id = intval( $_POST['sunshine_gallery_id'] );

		if ( isset( $_POST['sunshine_gallery_password'] ) ) {
			// Verify nonce
			if ( !wp_verify_nonce( $_POST['sunshine_gallery_access'], 'sunshine_gallery_access' ) ) {
				SPC()->notices->add( __( 'Invalid submission', 'sunshine-photo-cart' ), 'error' );
				SPC()->log( __( 'Invalid gallery password submission', 'sunshine-photo-cart' ) );
				return;
			}

			// Check password against gallery
			$gallery = sunshine_get_gallery( $gallery_id );
			if ( empty( $gallery ) ) {
				SPC()->notices->add( __( 'Invalid gallery', 'sunshine-photo-cart' ), 'error' );
				SPC()->log( __( 'Invalid gallery password submission: gallery not found', 'sunshine-photo-cart' ) );
				return;
			}

			// Throw error if it does not match
			if ( $gallery->get_password() != $_POST['sunshine_gallery_password'] ) {
				SPC()->notices->add( __( 'Password is incorrect, please try again', 'sunshine-photo-cart' ), 'error' );
				SPC()->log( __( 'Invalid gallery password submission for ' . $gallery->get_name() . ': wrong password', 'sunshine-photo-cart' ) );
				return;
			}

			// Add gallery id to list of allowed password galleries
			$password_galleries = SPC()->session->get( 'gallery_passwords' );
			if ( is_array( $password_galleries ) ) {
				$password_galleries[] = $gallery_id;
			} else {
				$password_galleries = array( $gallery_id );
			}
			SPC()->session->set( 'gallery_passwords', $password_galleries );
			SPC()->log( __( 'Password access granted for ' . $gallery->get_name(), 'sunshine-photo-cart' ) );
			$redirect = true;

		}

		if ( isset( $_POST['sunshine_gallery_email'] ) ) {

			$email = sanitize_email( $_POST['sunshine_gallery_email'] );
			if ( is_email( $email ) ) {
				$gallery = new SPC_Gallery( $gallery_id );
				$gallery_emails = SPC()->session->get( 'gallery_emails' );
				$gallery_emails[] = $gallery_id;
				SPC()->session->set( 'gallery_emails', $gallery_emails );
				$existing_emails = $gallery->get_emails();
				if ( !is_array( $existing_emails ) || !in_array( $email, $existing_emails ) ) {
					$gallery->add_email( $email );
					SPC()->log( __( 'Email address provided for ' . $gallery->get_name() . ': ' . $email, 'sunshine-photo-cart' ) );
					do_action( 'sunshine_gallery_email', $email, $gallery_id );
				}
			} else {
				SPC()->notices->add( __( 'Not a valid email address', 'sunshine-photo-cart' ), 'error' );
				SPC()->log( __( 'Invalid email address provided for ' . $gallery->get_name() . ': ' . $email, 'sunshine-photo-cart' ) );
			}

		}


		if ( $redirect ) {
			wp_safe_redirect( $gallery->get_permalink() );
			exit;
		}

	}

}
?>
