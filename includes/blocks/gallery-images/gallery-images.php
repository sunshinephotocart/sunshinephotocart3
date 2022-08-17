<?php
class SPC_Block_Gallery_Images {

    public function __construct() {

        add_action( 'init', array( $this, 'init' ) );
        //add_action( 'enqueue_block_editor_assets', array( $this, 'editor_scripts' ) );
        //add_action( 'enqueue_block_assets', array( $this, 'scripts' ) );
        add_filter( 'render_block', array( $this, 'render_block' ), 10, 2 );
    }

    public function init() {

        // Check if Gutenberg is active.
    	if ( ! function_exists( 'register_block_type' ) ) {
    		return;
    	}

        wp_register_script(
    		'sunshine-photo-cart-gallery-images-block',
    		SUNSHINE_PHOTO_CART_URL . 'includes/blocks/gallery-images/gallery-images.js',
    		array( 'wp-blocks', 'wp-element', 'wp-editor', 'jquery' ),
    		SUNSHINE_PHOTO_CART_VERSION
    	);

    	register_block_type( 'sunshine-photo-cart/gallery-images', array(
    		'editor_script' => 'sunshine-photo-cart-gallery-images-block',
            //'editor_style' => 'confetti-style',
    	) );

    }

    public function render_block( $content, $attributes ) {

        if ( $attributes['blockName'] != 'sunshine-photo-cart/gallery-images' ) {
            return $content;
        }

        return 'SUNSHINE IMAGES';

    }

}

$spc_block_gallery_images = new SPC_Block_Gallery_Images();
