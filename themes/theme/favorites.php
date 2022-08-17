<?php global $sunshine; ?>
<div id="sunshine" class="sunshine-clearfix <?php sunshine_classes(); ?>">

	<?php do_action('sunshine_before_content'); ?>

	<div id="sunshine--main">

		<div id="sunshine-action-menu" class="sunshine-clearfix">
			<?php sunshine_action_menu(); ?>
		</div>
		<div id="sunshine-image-list">
		<?php
		$favorites = SPC()->customer->get_favorites();
		if ( !empty( $favorites ) ) {
			echo '<div id="sunshine-image-list">';
			echo '<ul class="sunshine-col-' . esc_attr( SPC()->get_option( 'columns' ) ) . '">';
			foreach ( $favorites as $image_id ) {
				$image = new SPC_Image( $image_id );
				$image_html = '<a href="' . $image->get_permalink() . '">' . $image->output( 'sunshine-thumbnail', false ) . '</a>';
				$image_html = apply_filters( 'sunshine_gallery_image_html', $image_html, $image->get_id(), $image->get_image_url() );
				?>
				<li id="sunshine-image-<?php echo esc_attr( $image->get_id() ); ?>" class="<?php esc_attr( sunshine_image_class( $image->get_id(), array( 'sunshine-image-thumbnail', false ) ) ); ?>">
					<?php do_action( 'sunshine_before_loop_image_item' ); ?>
					<?php echo wp_kses_post( $image_html ); ?>
					<?php if ( !empty( SPC()->get_option( 'show_image_data' ) ) ) { ?>
						<div class="sunshine-image-name"><?php echo esc_html( $image->get_name() ); ?></div>
					<?php } ?>
					<div class="sunshine-image-menu-container">
						<?php sunshine_image_menu( $image ); ?>
					</div>
					<?php do_action( 'sunshine_image_thumbnail', $image ); ?>
					<?php do_action( 'sunshine_after_loop_image_item' ); ?>
				</li>
				<?php
			}
			echo '</ul>';
			echo '</div>';

			do_action( 'sunshine_after_gallery' );

		} else {
			echo '<p>'.esc_html__('You have no images marked as a favorite', 'sunshine-photo-cart').'</p>';
		}

		do_action( 'sunshine_after_favorites' );
		?>
		</div>

	</div>

	<?php do_action('sunshine_after_content'); ?>

</div>
