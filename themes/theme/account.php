<div id="sunshine" class="<?php sunshine_classes(); ?>">

	<?php do_action( 'sunshine_before_content' ); ?>

	<?php
	$credits = SPC()->customer->get_credits();
	if ( $credits > 0 ) {
	?>
		<div id="sunshine--account--credits">
			<h2><?php esc_html_e( 'Credits', 'sunshine-photo-cart' ); ?></h2>
			<p><?php printf( __( 'You have %s in credit', 'sunshine-photo-cart' ), sunshine_price( $credits ) ); ?></p>
		</div>
	<?php } ?>

	<?php
	$orders = SPC()->customer->get_orders();
	if ( $orders ) {
	?>
	<div id="sunshine--account--orders">
		<h2><?php esc_html_e( 'Orders', 'sunshine-photo-cart' ); ?></h2>
		<table>
		<tr>
			<th><?php esc_html_e( 'Order', 'sunshine-photo-cart' ); ?></th>
			<th><?php esc_html_e( 'Order Date', 'sunshine-photo-cart' ); ?></th>
			<th><?php esc_html_e( 'Order Total', 'sunshine-photo-cart' ); ?></th>
			<th><?php esc_html_e( 'Order Status', 'sunshine-photo-cart' ); ?></th>
			<th><?php esc_html_e( 'Invoice', 'sunshine-photo-cart' ); ?></th>
		</tr>
		<?php foreach ( $orders as $order ) { ?>
			<tr>
				<td><a href="<?php echo $order->get_permalink(); ?>"><?php echo $order->get_name(); ?></a></td>
				<td><?php echo $order->get_date(); ?></td>
				<td><?php echo $order->get_total_formatted(); ?></td>
				<td><?php echo $order->get_status_name(); ?></td>
				<td><a href="<?php echo $order->get_invoice_url(); ?>" target="_blank"><?php _e( 'View invoice', 'sunshine-photo-cart' ); ?></a></td>
			</tr>
		<?php } ?>
		</table>
	</div>
	<?php } ?>

	<!--
	<form method="post" id="sunshine-account" class="sunshine-form">
		<input type="hidden" name="sunshine_update_account" value="1" />

		<div id="sunshine-account-info">
			<?php //TODO: Account update fields sunshine_checkout_contact_fields(); ?>
			<?php //sunshine_checkout_shipping_fields(); ?>
			<?php //sunshine_checkout_billing_fields(); ?>
			<p class="sunshine-buttons"><input type="submit" class="sunshine-button" value="<?php _e('Update Account Info', 'sunshine-photo-cart'); ?>" /></p>
		</div>

	</form>
	-->

	<?php do_action( 'sunshine_after_content' ); ?>

</div>
