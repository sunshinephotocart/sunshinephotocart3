<?php
class Sunshine_Admin_Meta_Boxes_Product extends Sunshine_Admin_Meta_Boxes {

	protected $post_type = 'sunshine-product';

	public function set_meta_boxes( $meta_boxes ) {
		$meta_boxes['sunshine-product'] = array(
			array(
				'id' => 'sunshine-product-options', // Unique box id
				'name' => __( 'Product Info', 'sunshine-photo-cart' ), // Label/name
				'context' => 'advanced', // normal/side/advanced
				'priority' => 'high' // priority
			)
		);
		return $meta_boxes;
	}

	public function set_options( $options ) {
		$price_levels = sunshine_get_price_levels();
		$price_level_fields = array();
		if ( !empty( $price_levels ) ) {
			foreach ( $price_levels as $price_level ) {
				$price_level_fields[] = array(
					'id' => 'price_' . $price_level->get_id(),
					'name' => __( 'Price', 'sunshine-photo-cart' ),
					'type' => 'price'
				);
			}
		}
		$price_level_fields['1100'] = array(
			'id' => 'taxable',
			'name' => __( 'Taxable', 'sunshine-photo-cart' ),
			'type' => 'checkbox'
		);
		$price_level_fields['1200'] = array(
			'id' => 'shipping',
			'name' => __( 'Extra Shipping Cost', 'sunshine-photo-cart' ),
			'type' => 'price',
			'description' => __( 'Additional shipping cost, intended for larger items such as canvases', 'sunshine-photo-cart' )
		);

		$options['sunshine-product-options'] = array(
			'1000' => array(
				'id' => 'general',
				'name' => __( 'General', 'sunshine-photo-cart' ),
				'fields' => $price_level_fields
			)
		);

		return $options;
	}

}

$sunshine_admin_meta_boxes_product = new Sunshine_Admin_Meta_Boxes_Product();

add_filter( 'manage_edit-sunshine-product_columns', 'sunshine_products_columns', 10 );
function sunshine_products_columns( $columns ) {
	unset( $columns['date'] );
	$columns['price'] = __( 'Price', 'sunshine-photo-cart' );
	$columns['category'] = __( 'Category', 'sunshine-photo-cart' );
	return $columns;
}

add_action( 'manage_sunshine-product_posts_custom_column', 'sunshine_products_columns_content', 99, 2 );
function sunshine_products_columns_content( $column, $post_id ) {
	global $post;
	$product = new SPC_Product( $post );
	switch( $column ) {
		case 'price':
			echo $product->get_price_formatted();
			break;
		case 'category':
			echo '<a href="' . admin_url( 'term.php?taxonomy=sunshine-product-category&tag_ID=' . $product->get_category_id() . '&post_type=sunshine-product' ) . '">' . $product->get_category_name() . '</a>';
			break;
		default:
			break;
	}
}

add_action( 'quick_edit_custom_box', 'ws365150_custom_edit_box', 10, 3 );
function ws365150_custom_edit_box( $column_name, $post_type, $taxonomy ) {
    global $post;

    switch ( $post_type ) {
        case 'page':

        if ( $column_name === 'page_remark' ): // same column title as defined in previous step
        ?>
                <?php // echo get_post_meta( $post->ID, 'remark', true ); ?>
            <fieldset class="inline-edit-col-right" id="#edit-">
                <div class="inline-edit-col">
                    <label>
                        <span class="title">Remark</span>
                        <span class="input-text-wrap"><input type="text" name="remark" class="inline-edit-menu-order-input" value=""></span>
                    </label>
                </div>
            </fieldset>
            <?php
        endif;
            // echo 'custom page field';
            break;

        default:
            break;
    }
}


/* SETUP META BOXES
add_action( 'add_meta_boxes', 'sunshine_products_meta_boxes' );
function sunshine_products_meta_boxes() {
	add_meta_box(
		'sunshine_products',
		__( 'Product Info', 'sunshine-photo-cart' ),
		'sunshine_products_box',
		'sunshine-product',
		'advanced',
		'high',
	);
	remove_meta_box( 'commentstatusdiv', 'sunshine_products' , 'normal' );
	remove_meta_box( 'slugdiv', 'sunshine_products' , 'normal' );
}

function sunshine_products_box( $post ) {
	global $sunshine;
	// Use nonce for verification
	wp_nonce_field( plugin_basename( __FILE__ ), 'sunshine_noncename' );

	$currency_symbol = sunshine_currency_symbol();
	$currency_symbol_format = sunshine_currency_symbol_format();

	$product = new SPC_Product( $post );

	echo '<table class="sunshine-meta">';
	echo '<tr><th><label for="sunshine_product_price">' . __( 'Price', 'sunshine-photo-cart' ) . '</label></th>';
	echo '<td>';
	$price_levels = sunshine_get_price_levels();
	$price_levels_count = count( $price_levels );
	if ( $price_levels_count > 1 ) {
		echo '<ul class="sunshine-price-levels">';
		foreach ( $price_levels as $price_level ) {
			echo '<li>' . $price_level->name . ':<br />';
			$text_field = '<input type="text" name="sunshine_product_price_' . esc_attr( $price_level->term_id ) . '" value="' . esc_attr( $product->get_price( $price_level->term_id ) ) . '" />';
			echo sprintf( $currency_symbol_format, $currency_symbol, $text_field );
			echo '</li>';
		}
		echo '</ul>';
	} elseif ( $price_levels_count == 0 ) {
		echo __( 'No price levels setup', 'sunshine-photo-cart' );
	} else {
		$text_field = '<input type="text" name="sunshine_product_price_' . esc_attr( $price_levels[0]->term_id ).'" value="' . esc_attr( $product->get_price( $price_levels[0]->term_id ) ) . '" />';
		echo sprintf( $currency_symbol_format, $currency_symbol, $text_field );
	}
	echo '</td></tr>';
	echo '<tr><th><label for="sunshine_product_taxable">' . __( 'Taxable', 'sunshine-photo-cart' ) . '</label></th>';
	echo '<td><input type="checkbox" name="sunshine_product_taxable" value="1" '.checked( $product->is_taxable(), 1, 0 ).' /></td></tr>';
	echo '<tr><th><label for="sunshine_product_shipping">' . __( 'Extra Shipping Fee', 'sunshine-photo-cart' ) . '</label></th>';
	echo '<td>';
	$text_field = '<input type="text" name="sunshine_product_shipping" value="'.esc_attr( $product->get_shipping()  ).'" />';
	echo sprintf( $currency_symbol_format, $currency_symbol, $text_field );
	echo '<span class="desc">' . __( 'This is an additional shipping price which will transparently be added to the total Flat Rate shipping price. Basically use this for large or heavy items like a canvas where shipping costs more than small prints.', 'sunshine-photo-cart' ) . '</span>';
	echo '</td></tr>';
	do_action( 'sunshine_admin_products_meta', $post );
	echo '</table>';

}

add_action( 'save_post', 'sunshine_products_quicksave_post', 10, 3 );
function sunshine_products_quicksave_post( $post_id, $post, $update ) {

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( !isset( $_POST['_inline_edit'] ) || !wp_verify_nonce( $_POST['_inline_edit'], 'inlineeditnonce' ) ) {
		return false;
	}

	if ( isset( $_POST ) && 'sunshine-product' == $post->post_type ) {
		sunshine_products_save_postdata( $post_id, $_POST );
	}

}

add_action( 'save_post', 'sunshine_products_save_post', 10, 3 );
function sunshine_products_save_post( $post_id, $post, $update ) {

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( !isset( $_POST['sunshine_noncename'] ) || !wp_verify_nonce( $_POST['sunshine_noncename'], plugin_basename( __FILE__ ) ) ) {
		return;
	}

	if ( isset( $_POST ) && 'sunshine-product' == $post->post_type ) {
		sunshine_products_save_postdata( $post_id, $_POST );
	}

}

function sunshine_products_save_postdata( $post_id, $data ) {

	$price_levels = get_terms( 'sunshine-product-price-level', array( 'hide_empty' => false ) );
	foreach ( $price_levels as $price_level ) {
		$key = 'sunshine_product_price_' . $price_level->term_id;
		$price = sanitize_text_field( $data[ $key ] );
		if ( !isset( $data[ $key ] ) ) continue;
		if ( $price == '' ) {
			delete_post_meta( $post_id, 'sunshine_product_price_' . $price_level->term_id );
		} else {
			update_post_meta( $post_id, 'sunshine_product_price_' . $price_level->term_id, $price );
		}
	}
	$taxable = ( isset( $data['sunshine_product_taxable'] ) ) ? intval( $_POST['sunshine_product_taxable'] ) : '0';
	if ( $taxable ) {
		update_post_meta( $post_id, 'sunshine_product_taxable', $taxable );
	} else {
		delete_post_meta( $post_id, 'sunshine_product_taxable' );
	}
	if ( isset( $data['sunshine_product_shipping'] ) ) {
		update_post_meta( $post_id, 'sunshine_product_shipping', sanitize_text_field( $data['sunshine_product_shipping'] ) );
	}

}

add_action( 'restrict_manage_posts', 'sunshine_restrict_products_by_category' );
function sunshine_restrict_products_by_category() {
	global $typenow;
	$post_type = 'sunshine-product'; // change to your post type
	$taxonomy  = 'sunshine-product-category'; // change to your taxonomy
	if ( $typenow == $post_type ) {
		$selected      = isset( $_GET[ $taxonomy ] ) ? sanitize_text_field( $_GET[ $taxonomy ] ) : '';
		wp_dropdown_categories( array(
			'show_option_all' => __( "Show All Categories", 'sunshine-photo-cart' ),
			'taxonomy'        => $taxonomy,
			'name'            => $taxonomy,
			'orderby'         => 'name',
			'selected'        => $selected,
			'show_count'      => true,
			'hide_empty'      => true,
		));
	};
}

add_filter( 'parse_query', 'sunshine_restrict_products_by_category_query' );
function sunshine_restrict_products_by_category_query( $query ) {
	global $pagenow;
	$post_type = 'sunshine-product';
	$taxonomy  = 'sunshine-product-category';
	$q_vars    = &$query->query_vars;
	if ( $pagenow == 'edit.php' && isset( $q_vars['post_type'] ) && $q_vars['post_type'] == $post_type && isset( $q_vars[ $taxonomy ] ) && is_numeric( $q_vars[ $taxonomy ] ) && $q_vars[ $taxonomy ] != 0 ) {
		$term = get_term_by( 'id', $q_vars[ $taxonomy ], $taxonomy );
		$q_vars[ $taxonomy ] = $term->slug;
	}
}

add_filter( 'manage_edit-sunshine-product_columns', 'sunshine_product_columns' ) ;
function sunshine_product_columns( $columns ) {
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __( 'Name' ),
		'category' => __( 'Category' ),
		'price' => __( 'Price' )
	);
	return $columns;
}

add_action( 'manage_sunshine-product_posts_custom_column', 'sunshine_product_columns_content', 10, 2 );
function sunshine_product_columns_content( $column, $post_id ) {
	global $post, $sunshine;

	switch( $column ) {
		case 'category':
			$package = get_post_meta( $post_id, 'sunshine_product_package', true );
			if ( $package ) {
				_e( 'Package', 'sunshine-photo-cart' );
				break;
			}
			$terms = get_the_terms( $post_id, 'sunshine-product-category' );
			if ( !empty( $terms ) ) {
				$out = array();
				foreach ( $terms as $term ) {
					$out[] = sprintf( '<a href="%s">%s</a>',
						add_query_arg( array( 'post_type' => $post->post_type, 'sunshine-product-category' => $term->slug ), 'edit.php' ),
						sanitize_term_field( 'name', $term->name, $term->term_id, 'genre', 'display' )
					);
				}
				echo join( ', ', $out );
			}
			else {
				_e( 'No categories' );
			}
			break;
		case 'price':
			$price_levels = get_terms( 'sunshine-product-price-level', array( 'hide_empty' => false ) );
			foreach ( $price_levels as $price_level ) {
				$price_raw = $sunshine->cart->get_product_price( $post_id, $price_level->term_id, false );
				$price = $sunshine->cart->get_product_display_price( $post_id, $price_level->term_id );
				//$price = get_post_meta( $post->ID, 'sunshine_product_price_'.$price_level->term_id, true );
				echo $price_level->name . ': ';
				if ( $price_raw == '' )
					echo '&mdash;';
				else {
					echo $price;
					echo '<input type="hidden" name="sunshine_product_price_' . $price_level->term_id . '" data-price-level="' . esc_attr( $price_level->term_id ) . '" value="' . esc_attr( $price_raw ) . '" />';
				}
				echo '<br />';
			}
			echo '<input type="hidden" name="sunshine_product_taxable_value" value="' . esc_attr( get_post_meta( $post_id, 'sunshine_product_taxable', true ) ) . '" />';
			echo '<input type="hidden" name="sunshine_product_shipping_value" value="' . esc_attr( get_post_meta( $post_id, 'sunshine_product_shipping', true ) ) . '" />';
			break;
		default:
			break;
	}
}

add_filter( 'post_row_actions', 'sunshine_duplicate_product_link_row',10,2 );
add_filter( 'page_row_actions', 'sunshine_duplicate_product_link_row',10,2 );
function sunshine_duplicate_product_link_row( $actions, $post ) {
	if ( $post->post_type == 'sunshine-product' ) {
		$actions['duplicate'] = '<a href="edit.php?post_type=sunshine-product&sunshine_action=duplicate&product_id='.$post->ID.'">' . __( 'Duplicate Product', 'sunshine-photo-cart' ) . '</a>';
	}
	return $actions;
}

add_action( 'admin_init', 'sunshine_duplicate_product' );
function sunshine_duplicate_product() {
	if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'sunshine-product' && isset( $_GET['sunshine_action'] ) && $_GET['sunshine_action'] == 'duplicate' && is_numeric( $_GET['product_id'] ) ) {
		// Get the original product
		$product = get_post( intval( $_GET['product_id'] ) );
		// Get custom fields
		$price_levels = get_terms( 'sunshine-product-price-level', array( 'hide_empty' => false ) );
		foreach ( $price_levels as $price_level )
			$prices[$price_level->term_id] = get_post_meta( $product->ID,'sunshine_product_price_'.$price_level->term_id, true );
		$taxable = get_post_meta( $product->ID,'sunshine_product_taxable', true );
		$shipping = get_post_meta( $product->ID,'sunshine_product_shipping', true );
		// Get categories
		$categories = wp_get_object_terms( $product->ID, 'sunshine-product-category' );
		foreach ( $categories as $category ) {
			$cats[] = $category->term_id;
		}
		// Set new title
		$product->post_title = $product->post_title . ' DUPLICATE';
		// Remove ID so we don't update the existing product
		unset( $product->ID );
		// Insert new product, update custom fields, assign taxonomies
		$new_product_id = wp_insert_post( $product );
		foreach ( $prices as $price_level => $price ) {
			if ( is_null( $price ) )
				delete_post_meta( $new_product_id, 'sunshine_product_price_'.$price_level );
			else
				update_post_meta( $new_product_id, 'sunshine_product_price_'.$price_level, $price );
		}
		update_post_meta( $new_product_id, 'sunshine_product_taxable', $taxable );
		update_post_meta( $new_product_id, 'sunshine_product_shipping', $shipping );
		wp_set_post_terms( $new_product_id, $cats, 'sunshine-product-category' );
		wp_redirect( get_admin_url().'edit.php?post_type=sunshine-product' );
		exit;
	}
}

add_filter( 'get_sample_permalink_html', 'sunshine_product_sample_permalink_html', 10, 4 );
function sunshine_product_sample_permalink_html( $html, $id, $new_title, $new_slug ) {
	if ( get_post_type( $id ) == 'sunshine-product' ) {
		return '';
	}
	return $html;
}

add_filter( 'post_updated_messages', 'sunshine_product_post_updated_messages' );
function sunshine_product_post_updated_messages( $messages ) {
	$messages['sunshine-product'][1] = __( 'Product updated','sunshine-photo-cart' );
	$messages['sunshine-product'][6] = __( 'Product created','sunshine-photo-cart' );
	return $messages;
}

add_action( 'quick_edit_custom_box', 'sunshine_product_quick_edit', 10, 2 );
function sunshine_product_quick_edit( $column_name, $post_type ) {
   switch ( $post_type ) {
      case 'sunshine-product':

         switch( $column_name ) {
            case 'price':
               ?>
				<fieldset class="inline-edit-col-left sunshine-inline-edit-col">
                  	<div class="inline-edit-col">
						<legend class="inline-edit-legend"><?php _e( 'Product Options', 'sunshine-photo-cart' ); ?></legend>
						<?php
						$currency_symbol = sunshine_currency_symbol();
						$currency_symbol_format = sunshine_currency_symbol_format();
						$price_levels = get_terms( 'sunshine-product-price-level', array( 'hide_empty' => false ) );
						$price_levels_count = count( $price_levels );
						if ( $price_levels_count > 1 ) {
							foreach ( $price_levels as $price_level ) {
								echo '<label><span class="title">' . esc_html( $price_level->name ) . '</span>';
								$text_field = '<input type="text" name="sunshine_product_price_'.esc_attr( $price_level->term_id ).'" size="5" value="" />';
								echo sprintf( $currency_symbol_format, $currency_symbol, $text_field );
								echo '</label>';
							}
						} elseif ( $price_levels_count == 0 ) {
							echo __( 'No price levels setup', 'sunshine-photo-cart' );
						} else {
							echo '<label><span class="title">' . __( 'Price', 'sunshine-photo-cart' ) . '</span>';
							$text_field = '<input type="text" name="sunshine_product_price_'.esc_attr( $price_levels[0]->term_id ).'" size="5" value="" />';
							echo sprintf( $currency_symbol_format, $currency_symbol, $text_field );
						}
						?>
						<label>
	                       <span class="title"><?php _e( 'Taxable', 'sunshine-photo-cart' ); ?></span>
	                       <input type="checkbox" name="sunshine_product_taxable" value="1" />
	                    </label>
						<label>
	                       <span class="title"><?php _e( 'Extra Shipping Fee', 'sunshine-photo-cart' ); ?></span>
							<?php
							$text_field = '<input type="text" name="sunshine_product_shipping" size="5" value="" />';
							echo sprintf( $currency_symbol_format, $currency_symbol, $text_field );
							?>
	                    </label>
                  	</div>
               	</fieldset>
				<?php
               break;
         }
         break;

   }
}

add_action( 'admin_enqueue_scripts', 'sunshine_products_admin_enqueue_scripts' );
function sunshine_products_admin_enqueue_scripts( $hook ) {
	global $post_type;
	if ( 'edit.php' === $hook && isset( $_GET['post_type'] ) && 'sunshine-product' === $_GET['post_type'] ) {
		wp_enqueue_script( 'sunshine-product-quick-edit', SUNSHINE_PHOTO_CART_URL . 'assets/js/sunshine-product-quick-edit.js', false, null, true );
	}
}

add_filter( 'quick_edit_show_taxonomy', 'sunshine_price_level_hide_quick_edit', 10, 3 );
function sunshine_price_level_hide_quick_edit( $value, $taxonomy, $post_type ) {
	if ( $taxonomy == 'sunshine-price-level' ) {
		$value = false;
	}
	return $value;
}

add_action( 'admin_notices', 'sunshine_product_admin_no_category' );
function sunshine_product_admin_no_category() {
	$screen = get_current_screen();
	if ( $screen->post_type == 'sunshine-product' && $screen->parent_base == 'edit' && isset( $_GET['post'] ) ) {
		$categories = wp_get_post_terms( intval( $_GET['post'] ), 'sunshine-product-category' );
		$package = get_post_meta( intval( $_GET['post'] ), 'sunshine_product_package', true );
		if ( empty( $categories ) && !$package ) {
			echo '<div class="notice error"><p>' . __( '<strong>This product is not assigned to any categories.</strong> Products must be assigned to at least one category before they are visible.', 'sunshine-photo-cart' ) . '</p></div>';
			echo '<style type="text/css">#sunshine-product-categorydiv { border: 3px solid red; }</style>';
		}
	}
}


add_filter( 'wp_terms_checklist_args', 'sunshine_products_single_category', '', 2 );
function sunshine_products_single_category( $args, $post_id ) {
	if ( get_post_type( $post_id ) == 'sunshine-product' ) {
		$args['walker'] = new SunshineProductsSingleCategory;
	}
    return $args;
}

class SunshineProductsSingleCategory extends Walker {
    var $tree_type = 'category';
    var $db_fields = array ('parent' => 'parent', 'id' => 'term_id'); //TODO: decouple this

    function start_lvl( &$output, $depth = 0, $args = array() ) {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent<ul class='children'>\n";
    }

    function end_lvl( &$output, $depth = 0, $args = array() ) {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent</ul>\n";
    }

    function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
        extract($args);
        if ( empty($taxonomy) )
            $taxonomy = 'category';

        if ( $taxonomy == 'category' )
            $name = 'post_category';
        else
            $name = 'tax_input['.$taxonomy.']';

        $class = in_array( $category->term_id, $popular_cats ) ? ' class="popular-category"' : '';
        if ( $taxonomy == 'sunshine-product-category' )
            $output .= "\n<li id='{$taxonomy}-{$category->term_id}'$class>" . '<label class="selectit"><input value="' . $category->term_id . '" type="radio" name="'.$name.'[]" id="in-'.$taxonomy.'-' . $category->term_id . '"' . checked( in_array( $category->term_id, $selected_cats ), true, false ) . disabled( empty( $args['disabled'] ), false, false ) . ' /> ' . apply_filters('the_category', $category->name ) . '</label>';
        else
            $output .= "\n<li id='{$taxonomy}-{$category->term_id}'$class>" . '<label class="selectit"><input value="' . $category->term_id . '" type="checkbox" name="'.$name.'[]" id="in-'.$taxonomy.'-' . $category->term_id . '"' . checked( in_array( $category->term_id, $selected_cats ), true, false ) . disabled( empty( $args['disabled'] ), false, false ) . ' /> ' . apply_filters('the_category', $category->name ) . '</label>';
    }

    function end_el( &$output, $category, $depth = 0, $args = array() ) {
            $output .= "</li>\n";
    }
}
*/
?>
