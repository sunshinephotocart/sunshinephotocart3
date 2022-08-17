<?php global $sunshine; ?>
<?php load_template(SUNSHINE_PHOTO_CART_PATH.'themes/default/header.php'); ?>

<div id="sunshine-next-prev">
	<span id="sunshine-prev"><?php sunshine_adjacent_image_link( true, '', '&laquo;' ); ?></span>
	<span id="sunshine-next"><?php sunshine_adjacent_image_link( false, '', '&raquo;' ); ?></span>
</div>
<h1><?php echo apply_filters( 'the_title', SPC()->frontend->current_image->post_title, SPC()->frontend->current_image->ID ); ?></h1>
<div id="sunshine-action-menu" class="sunshine-clearfix">
	<?php sunshine_action_menu(); ?>
</div>
<div id="sunshine-image">
	<?php sunshine_image(); ?>
</div>
<div id="sunshine-add-form">
	<?php sunshine_add_to_cart_form(); ?>
</div>

<?php if ( get_post_meta( SPC()->frontend->current_gallery->ID, 'sunshine_gallery_image_comments', true ) ) { ?>
<div id="sunshine-image-comments">
	<h2><?php esc_html_e( 'Comments', 'sunshine-photo-cart' ); ?></h2>
	<?php
	$comments = get_comments('post_id='.SPC()->frontend->current_image->ID.'&order=ASC');
	if ( $comments ) {
		echo '<ol>';
		wp_list_comments('type=comment&avatar_size=0', $comments);
		echo '</ol>';
	}
	$sunshine->comment_status = 'IN_SUNSHINE';
	comment_form(
		array(
			'comment_notes_before' => '',
			'comment_notes_after' => '',
			'logged_in_as' => '',
			'id_form' => 'sunshine-image-comment',
			'id_submit' => 'sunshine-submit',
			'title_reply' => __( 'Add Comment', 'sunshine-photo-cart' )
		),
		SPC()->frontend->current_image->ID
	);
	$sunshine->comment_status = '';
	?>
</div>
<?php } ?>

<?php load_template(SUNSHINE_PHOTO_CART_PATH.'themes/default/footer.php'); ?>
