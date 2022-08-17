<?php global $sunshine; ?>
<div id="sunshine" class="sunshine-clearfix <?php sunshine_classes(); ?>">

	<?php do_action( 'sunshine_before_content' ); ?>

	<?php if ( isset( $_GET['sunshine_gallery'] ) ) { ?>
	<div id="sunshine-breadcrumb">
		<?php sunshine_breadcrumb(); ?>
	</div>
	<?php } ?>
	<h2><?php echo sprintf( __( 'Search for "%s"', 'sunshine-photo-cart' ), sanitize_text_field( $_GET['sunshine_search'] ) ); ?></h2>

	<div id="sunshine--main">

		<?php
		$images = sunshine_get_search_images();
		if ($images) {
			echo '<div id="sunshine-image-list">';
			echo '<ul class="sunshine-clearfix sunshine-col-'.esc_attr( SPC()->get_option( 'columns' ) ).'">';
			foreach ( $images as $image ) {
				$thumb = wp_get_attachment_image_src( $image->ID, 'sunshine-thumbnail' );
				$image_html = '<a href="'.get_permalink( $image->ID ) . '"><img src="'.esc_url( $thumb[0] ).'" alt="" /></a>';
				$image_html = apply_filters( 'sunshine_gallery_image_html', $image_html, $image->ID, $thumb );
		?>
				<li id="sunshine-image-<?php echo esc_attr( $image->ID ); ?>" class="<?php esc_attr( sunshine_image_class( $image->ID, array( 'sunshine-image-thumbnail', false ) ) ); ?>">
					<?php echo wp_kses_post( $image_html ); ?>
					<div class="sunshine-image-name"><?php echo esc_html( $image->post_title ); ?></div>
					<div class="sunshine-image-menu-container">
						<?php sunshine_image_menu($image); ?>
					</div>
					<?php do_action( 'sunshine_image_thumbnail', $image ); ?>
				</li>
		<?php
			}
			echo '</ul>';
			echo '</div>';

			do_action( 'sunshine_after_search_results' );

		} else {
			echo '<p>'.esc_html__('Sorry, no images match your search', 'sunshine-photo-cart').'</p>';
		}
		?>
	</div>

	<?php do_action( 'sunshine_after_content' ); ?>

</div>
