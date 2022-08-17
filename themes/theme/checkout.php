<div id="sunshine" class="<?php sunshine_classes(); ?>">

	<?php do_action( 'sunshine_before_content' ); ?>

	<div id="sunshine--main">

		<?php if ( !SPC()->cart->is_empty() ) { ?>

			<form method="post" action="<?php sunshine_url('checkout'); ?>" id="sunshine--checkout" class="sunshine-form">

				<?php do_action( 'sunshine_checkout_start_form' ); ?>
				<input type="hidden" name="sunshine_checkout" value="1" />

				<div id="sunshine--checkout">
					<div id="sunshine--checkout--steps">
						<?php do_action( 'sunshine_before_checkout_steps' ); ?>
						<?php sunshine_show_checkout_fields(); ?>
						<?php do_action( 'sunshine_after_checkout_steps' ); ?>
					</div>
					<div id="sunshine--checkout--review">
						<?php sunshine_checkout_order_review(); ?>
					</div>
				</div>
				<?php do_action( 'sunshine_checkout_end_form' ); ?>

			</form>

		<?php } else { ?>
			<p><?php esc_html_e( 'You do not have anything in your cart yet', 'sunshine-photo-cart' ); ?></p>
		<?php } ?>

	</div>

	<?php do_action( 'sunshine_after_content' ); ?>

</div>
