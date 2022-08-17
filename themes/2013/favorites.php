<?php global $sunshine; load_template(SUNSHINE_PHOTO_CART_PATH.'themes/2013/header.php'); ?>

<h1><?php esc_html_e('Favorites', 'sunshine-photo-cart'); ?></h1>
<?php echo apply_filters('the_content', $post->post_content); ?>

<div id="sunshine-action-menu" class="sunshine-clearfix">
	<?php sunshine_action_menu(); ?>
</div>
<div id="sunshine-image-list">
<?php
if (!empty($sunshine->favorites)) {
	echo '<ul class="sunshine-clearfix sunshine-col-'.esc_attr( SPC()->get_option( 'columns' ) ).'">';
	foreach ($sunshine->favorites as $image_id) {
		$image = get_post($image_id);
		$thumb = wp_get_attachment_image_src($image->ID, 'sunshine-thumbnail');
		$image_html = '<a href="'.get_permalink($image->ID).'"><img src="'.esc_url( $thumb[0] ).'" alt="" /></a>';
		$image_html = apply_filters('sunshine_gallery_image_html', $image_html, $image->ID, $thumb);
?>
		<li id="sunshine-image-<?php echo esc_attr( $image->ID ); ?>" class="<?php esc_attr( sunshine_image_class( $image->ID, array( 'sunshine-image-thumbnail', false ) ) ); ?>">
			<?php echo wp_kses_post( $image_html ); ?>
			<?php if ( !empty( SPC()->get_option( 'show_image_data' ) ) ) { ?>
				<div class="sunshine-image-name"><?php echo esc_html( apply_filters( 'sunshine_image_name', $image->post_title, $image ) ); ?></div>
			<?php } ?>
			<div class="sunshine-image-menu-container">
				<?php sunshine_image_menu($image); ?>
			</div>
			<?php do_action('sunshine_image_thumbnail', $image); ?>
		</li>
<?php
	}
	echo '</ul>';
} else {
	echo '<p>'.esc_html__('You have no images marked as a favorite', 'sunshine-photo-cart').'</p>';
}

do_action( 'sunshine_after_favorites' );
?>
</div>

<?php load_template(SUNSHINE_PHOTO_CART_PATH.'themes/2013/footer.php'); ?>
