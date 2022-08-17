
<div id="sunshine" class="<?php sunshine_classes(); ?>">

	<?php do_action( 'sunshine_before_content' ); ?>

	<div id="sunshine-action-menu">
		<?php sunshine_action_menu(); ?>
	</div>

	<div id="sunshine-gallery-content">
		<?php echo SPC()->frontend->current_gallery->get_content(); ?>
	</div>

	<?php
	$child_galleries = SPC()->frontend->current_gallery->get_child_galleries();
	if ( !empty( $child_galleries ) ) {
	?>
		<div id="sunshine--gallery-list">
			<ul class="sunshine--col-<?php echo esc_attr( SPC()->get_option( 'columns' ) ); ?>">
				<?php foreach ( $child_galleries as $gallery ) { ?>
					<li class="<?php $gallery->classes(); ?>">
						<?php do_action( 'sunshine_before_loop_gallery_item' ); ?>
						<a href="<?php echo $gallery->get_permalink(); ?>"><?php $gallery->featured_image(); ?></a>
						<h2><a href="<?php echo $gallery->get_permalink(); ?>"><?php echo $gallery->get_name(); ?></a></h2>
						<?php do_action( 'sunshine_after_loop_gallery_item' ); ?>
					</li>
				<?php } ?>
			</ul>
		</div>
	<?php }	else {
		$images = SPC()->frontend->current_gallery->get_images();
		if ( !empty( $images ) ) {
		?>
			<div id="sunshine--image-list" class="sunshine--col-<?php echo esc_attr( SPC()->get_option( 'columns' ) ); ?>">
				<?php
				foreach ( $images as $image ) {
					$image_html = '<a href="' . $image->get_permalink() . '">' . $image->output( 'sunshine-thumbnail', false ) . '</a>';
					$image_html = apply_filters( 'sunshine_gallery_image_html', $image_html, $image->get_id(), $image->get_image_url() );
					?>
					<figure id="sunshine--image-<?php echo esc_attr( $image->get_id() ); ?>" class="<?php esc_attr( sunshine_image_class( $image->get_id(), array( 'sunshine--image--thumbnail', false ) ) ); ?>">
						<?php do_action( 'sunshine_before_loop_image_item', $image ); ?>
						<?php echo wp_kses_post( $image_html ); ?>
						<?php if ( !empty( SPC()->get_option( 'show_image_data' ) ) ) { ?>
							<figcaption class="sunshine-image-name"><?php echo esc_html( $image->get_name() ); ?></figcaption>
						<?php } ?>
						<?php sunshine_image_menu( $image ); ?>
						<?php sunshine_image_status( $image ); ?>
						<?php do_action( 'sunshine_image_thumbnail', $image ); ?>
						<?php do_action( 'sunshine_after_loop_image_item', $image ); ?>
					</figure>
				<?php } ?>
			</div>

			<?php
			do_action( 'sunshine_after_gallery' );
			sunshine_gallery_pagination();

		} else {
			echo '<p>' . esc_html__( 'Sorry, no images have been added to this gallery yet', 'sunshine-photo-cart' ) . '</p>';
		}
	}
	?>

<?php do_action( 'sunshine_after_content' ); ?>

</div>
