<?php
// Price level must be int when passed
function sunshine_get_products( $price_level = 'all', $args = array() ) {

    $defaults = array(
        'nopaging' => true,
    );
    $args = wp_parse_args( $args, $defaults );

    $args['post_type'] = 'sunshine-product'; // Make sure we always get this post type

    if ( $price_level != 'all' ) {
        $args['meta_query'] = array(
            array(
                'key' => 'price_' . intval( $price_level ),
                'value' => '',
                'compare' => '!='
            )
        );
    }

    $products = get_posts( $args );
    if ( !empty( $products ) ) {
        $final_products = array();
        foreach ( $products as $product ) {
            $final_products[] = new SPC_Product( $product, ( $price_level == 'all' ) ? '' : intval( $price_level ) );
        }
    }
    return $final_products;

}

function sunshine_get_product( $product_id, $price_level_id ) {
    return new SPC_Product( $product_id, $price_level_id );
}

function sunshine_get_price_levels() {
    $terms = get_terms( 'sunshine-product-price-level', array( 'hide_empty' => false ) );
    if ( !empty( $terms ) ) {
        $price_levels = array();
        foreach ( $terms as $term ) {
            $price_levels[] = new SPC_Price_Level( $term );
        }
        return apply_filters( 'sunshine_price_levels', $price_levels );
    }
	return false;
}

function sunshine_get_default_price_level() {
    $price_levels = sunshine_get_price_levels();
    if ( !empty( $price_levels ) ) {
        return $price_levels[0];
    }
    return false;
}

function sunshine_get_default_product_category() {
    $terms = get_terms( 'sunshine-product-category', array( 'hide_empty' => 0 ) );
    if ( !empty( $terms ) ) {
        return new SPC_Product_Category( $terms[0] );
    }
    return false;
}

function sunshine_get_product_categories() {
    $terms = get_terms( 'sunshine-product-category', array( 'hide_empty' => 0, 'orderby' => 'meta_value', 'meta_key' => 'order', 'order' => 'ASC' ) );
    if ( !empty( $terms ) ) {
        $product_categories = array();
        foreach ( $terms as $term ) {
            $product_categories[] = new SPC_Product_Category( $term );
        }
        return $product_categories;
    }
    return false;
}
