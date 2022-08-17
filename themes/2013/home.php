<?php global $sunshine; load_template( SUNSHINE_PHOTO_CART_PATH . 'themes/2013/header.php' ); ?>

<h1><?php echo get_the_title( $post->ID ); ?></h1>

<?php if ( $post->post_content ) { ?>
	<div id="sunshine-content">
		<?php echo apply_filters( 'the_content', $post->post_content ); ?>
	</div>
<?php } ?>


<div id="sunshine-gallery-list" class="sunshine-clearfix">
	<?php
	$galleries = sunshine_get_galleries();
	if ( $galleries->have_posts() ) {
	?>
	<ul class="sunshine-col-<?php echo esc_attr( SPC()->get_option( 'columns' ) ); ?>">
	<?php while ( $galleries->have_posts() ) : $galleries->the_post(); ?>
		<li class="<?php sunshine_gallery_class(); ?>">
			<?php do_action( 'sunshine_before_loop_gallery_item' ); ?>
			<a href="<?php the_permalink(); ?>"><?php sunshine_featured_image(); ?></a>
			<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			<?php do_action( 'sunshine_after_loop_gallery_item' ); ?>
		</li>
	<?php endwhile; wp_reset_postdata(); ?>
	</ul>
	<?php } else { ?>
		<p><?php esc_html_e( 'Sorry, no galleries have been setup yet', 'sunshine-photo-cart' ); ?></p>
	<?php } ?>
</div>

<script type="text/javascript">
jQuery(document).ready(function(){
	jQuery('#sunshine-gallery-list li:nth-child(<?php echo esc_js( SPC()->get_option( 'columns' ) ); ?>n+1), #sunshine-gallery-list li:first-child').addClass('first');
});
</script>

<?php load_template( SUNSHINE_PHOTO_CART_PATH . 'themes/2013/footer.php' ); ?>
