<div id="sunshine" class="<?php sunshine_classes(); ?>">

	<?php do_action( 'sunshine_before_content' ); ?>

	<div id="sunshine--gallery-list">
		<?php
		$galleries = sunshine_get_galleries();
		if ( !empty( $galleries ) ) {
		?>
			<ul class="sunshine--col-<?php echo esc_attr( SPC()->get_option( 'columns' ) ); ?>">
			<?php foreach ( $galleries as $gallery ) { ?>
				<li class="<?php $gallery->classes(); ?>">
					<?php do_action( 'sunshine_before_loop_gallery_item' ); ?>
					<a href="<?php echo $gallery->get_permalink(); ?>"><?php $gallery->featured_image(); ?></a>
					<h2><a href="<?php echo $gallery->get_permalink(); ?>"><?php echo $gallery->get_name(); ?></a></h2>
					<?php do_action( 'sunshine_after_loop_gallery_item' ); ?>
				</li>
			<?php } ?>
			</ul>
		<?php } else { ?>
			<p><?php esc_html_e( 'Sorry, no galleries have been setup yet', 'sunshine-photo-cart' ); ?></p>
		<?php } ?>
	</div>

	<?php do_action( 'sunshine_after_content' ); ?>

</div>
