<?php global $sunshine; load_template(SUNSHINE_PHOTO_CART_PATH.'themes/2013/header.php'); ?>

<div id="sunshine-breadcrumb">
	<?php sunshine_breadcrumb(); ?>
</div>
<h1><?php echo get_the_title(SPC()->frontend->current_gallery->ID); ?></h1>
<div id="sunshine-action-menu" class="sunshine-clearfix">
	<?php sunshine_action_menu(); ?>
</div>

		<?php
		$this_gallery_id = $post->ID;
		$child_galleries = sunshine_get_child_galleries();
		?>
		<?php
		if ( !sunshine_is_gallery_expired() ) {
			if ( post_password_required(SPC()->frontend->current_gallery) ) {
				echo get_the_password_form();
			} elseif ( sunshine_gallery_requires_email(SPC()->frontend->current_gallery->ID) ) {
				echo sunshine_gallery_email_form();
			} else {
				sunshine_gallery_expiration_notice();
				if (SPC()->frontend->current_gallery->post_content) { ?>
					<div id="sunshine-content">
						<?php echo apply_filters('the_content', SPC()->frontend->current_gallery->post_content); ?>
					</div>
				<?php }
				if ($child_galleries->have_posts()) {
				?>
				<div id="sunshine-gallery-list" class="sunshine-clearfix">
					<ul class="sunshine-col-<?php echo esc_attr( SPC()->get_option( 'columns' ) ); ?>">
					<?php while ( $child_galleries->have_posts() ) : $child_galleries->the_post(); ?>
						<li class="<?php sunshine_gallery_class(); ?>">
							<?php do_action( 'sunshine_before_loop_gallery_item' ); ?>
							<a href="<?php the_permalink(); ?>"><?php sunshine_featured_image(); ?></a>
							<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<?php do_action( 'sunshine_after_loop_gallery_item' ); ?>
						</li>
					<?php endwhile; ?>
					</ul>
				</div>
				<?php }	else {
					$images = sunshine_get_gallery_images();
					if ($images) {
						echo '<div id="sunshine-image-list">';
						echo '<ul class="sunshine-col-'.esc_attr( SPC()->get_option( 'columns' ) ).'">';
						foreach ($images as $image) {
							$thumb = wp_get_attachment_image_src( $image->ID, 'sunshine-thumbnail');
							$image_html = '<a href="' . get_permalink( $image->ID ) . '"><img src="' . esc_url( $thumb[0] ) . '" alt="" /></a>';
							$image_html = apply_filters( 'sunshine_gallery_image_html', $image_html, $image->ID, $thumb );
							?>
							<li id="sunshine-image-<?php echo esc_attr( $image->ID ); ?>" class="<?php esc_attr( sunshine_image_class( $image->ID, array( 'sunshine-image-thumbnail', false ) ) ); ?>">
								<?php do_action( 'sunshine_before_loop_image_item' ); ?>
								<?php echo wp_kses_post( $image_html ); ?>
								<?php if ( !empty( SPC()->get_option( 'show_image_data' ) ) ) { ?>
									<div class="sunshine-image-name"><?php echo esc_html( apply_filters('sunshine_image_name', $image->post_title, $image ) ); ?></div>
								<?php } ?>
								<div class="sunshine-image-menu-container">
									<?php sunshine_image_menu( $image ); ?>
								</div>
								<?php do_action( 'sunshine_image_thumbnail', $image ); ?>
								<?php do_action( 'sunshine_before_loop_image_item' ); ?>
							</li>
							<?php
						}
						echo '</ul>';
						echo '</div>';

						do_action( 'sunshine_after_gallery', SPC()->frontend->current_gallery );

						sunshine_pagination();

					} else {
						echo '<p>'.esc_html__('Sorry, no images have been added to this gallery yet', 'sunshine-photo-cart').'</p>';
					}
				}
			}
		} else {
			echo '<p>'.esc_html__('Sorry, this gallery has expired.','sunshine-photo-cart').'</p>';
		}
		?>

<?php load_template(SUNSHINE_PHOTO_CART_PATH.'themes/2013/footer.php'); ?>
