<div id="sunshine" class="<?php sunshine_classes(); ?>">

	<?php do_action( 'sunshine_before_content' ); ?>

	<div id="sunshine--image">

		<div id="sunshine--image--content">
			<?php sunshine_image_menu(); ?>
			<?php SPC()->frontend->current_image->output(); ?>
		</div>

		<?php if ( SPC()->frontend->current_gallery->allow_comments() ) { ?>
		<div id="sunshine--image--comments">
			<h2><?php esc_html_e( 'Comments', 'sunshine-photo-cart' ); ?></h2>
			<?php
			$comments = SPC()->frontend->current_image->get_comments();
			if ( $comments ) {
				echo '<ol>';
				wp_list_comments( 'type=comment&avatar_size=0', $comments );
				echo '</ol>';
			}
			comment_form(
				array(
					'comment_notes_before' => '',
					'comment_notes_after' => '',
					'logged_in_as' => '',
					'id_form' => 'sunshine-image-comment',
					'id_submit' => 'sunshine-submit',
					'title_reply' => __( 'Add Comment', 'sunshine-photo-cart' )
				),
				SPC()->frontend->current_image->get_id()
			);
			?>
		</div>
		<?php } ?>

	</div>

	<nav id="sunshine--image--nav">
		<span id="sunshine-prev"><?php sunshine_adjacent_image_link( SPC()->frontend->current_image, true ); ?></span>
		<span id="sunshine-next"><?php sunshine_adjacent_image_link( SPC()->frontend->current_image, false ); ?></span>
	</nav>


	<?php do_action( 'sunshine_after_content' ); ?>

</div>
