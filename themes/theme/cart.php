<div id="sunshine" class="sunshine-clearfix <?php sunshine_classes(); ?>">

	<?php do_action( 'sunshine_before_content' ); ?>

	<div id="sunshine--main">

		<form method="post" action="" id="sunshine-cart">
		<input type="hidden" name="sunshine_update_cart" value="1" />
		<input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'sunshine_update_cart' ); ?>" />

		<?php do_action( 'sunshine_before_cart_items' ); ?>

		<?php if ( !SPC()->cart->is_empty() ) { ?>
			<table id="sunshine--cart--items">
			<thead>
				<tr>
					<th class="sunshine--cart-item--image"><?php esc_html_e( 'Image', 'sunshine-photo-cart' ); ?></th>
					<th class="sunshine--cart-item--name"><?php esc_html_e( 'Product', 'sunshine-photo-cart' ); ?></th>
					<th class="sunshine--cart-item--qty"><?php esc_html_e( 'Qty', 'sunshine-photo-cart' ); ?></th>
					<th class="sunshine--cart-item--price"><?php esc_html_e( 'Item Price', 'sunshine-photo-cart' ); ?></th>
					<th class="sunshine--cart-item--total"><?php esc_html_e( 'Item Total', 'sunshine-photo-cart' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php
			$i = 1; $tabindex = 0;
			foreach ( SPC()->cart->get_cart_items() as $cart_item ) {
				$tabindex++;
			?>
				<tr class="sunshine--cart-item <?php echo $cart_item->classes(); ?>">
					<td class="sunshine--cart-item--image" data-label="<?php esc_attr_e( 'Image', 'sunshine-photo-cart' ); ?>">
						<?php
						$image_name = $cart_item->get_image_name();
						$image_url = $cart_item->get_image_url();
						if ( $image_url ) {
							echo '<img src="' . $image_url . '" alt="' . esc_attr( $image_name ) . '" />';
						}
						?>
					</td>
					<td class="sunshine--cart-item--name" data-label="<?php esc_attr_e( 'Product', 'sunshine-photo-cart' ); ?>">
						<?php
						if ( $image_name ) {
							echo '<div class="sunshine--cart-item--image-name">' . $image_name . '</div>';
						}
						?>
						<div class="sunshine--cart-item--product-name"><?php echo $cart_item->get_name(); ?></div>
						<div class="sunshine--cart-item--comments"><?php echo $cart_item->get_comments(); ?></div>
					</td>
					<td class="sunshine--cart-item--qty" data-label="<?php esc_attr_e( 'Qty', 'sunshine-photo-cart'); ?>">
						<input type="number" name="item[<?php echo esc_attr( $i ); ?>][qty]" class="sunshine--qty" value="<?php echo $cart_item->get_qty(); ?>" size="4" tabindex="<?php echo esc_attr( $tabindex ); ?>" min="0" />
						<a href="<?php echo $cart_item->get_remove_url(); ?>" class="sunshine--cart-item--delete"></a>
					</td>
					<td class="sunshine--cart-item--price" data-label="<?php esc_attr_e( 'Price', 'sunshine-photo-cart' ); ?>">
						<?php echo $cart_item->get_price_formatted(); ?>
					</td>
					<td class="sunshine--cart-item--total" data-label="<?php esc_attr_e( 'Total', 'sunshine-photo-cart' ); ?>">
						<?php echo $cart_item->get_total_formatted(); ?>
						<input type="hidden" name="item[<?php echo esc_attr( $i ); ?>][object_id]" value="<?php echo esc_attr( $cart_item->object->get_id() ); ?>" />
						<input type="hidden" name="item[<?php echo esc_attr( $i ); ?>][product_id]" value="<?php echo esc_attr( $cart_item->product->get_id() ); ?>" />
						<input type="hidden" name="item[<?php echo esc_attr( $i ); ?>][hash]" value="<?php echo esc_attr( $cart_item->get_hash() ); ?>" />
					</td>
				</tr>
			<?php $i++; } ?>
			</tbody>
			</table>

			<?php do_action( 'sunshine_after_cart_items' ); ?>

			<div id="sunshine--cart--update-button">
				<input type="submit" value="<?php esc_attr_e( 'Update Cart', 'sunshine-photo-cart'); ?>" class="sunshine-button-alt" />
			</div>

			</form>

			<?php do_action( 'sunshine_after_cart_form' ); ?>

			<div id="sunshine--cart--totals">
				<?php sunshine_cart_totals(); ?>
				<p id="sunshine--cart--checkout-button"><a href="<?php echo sunshine_url( 'checkout' ); ?>" class="sunshine-button"><?php esc_html_e( 'Continue to checkout', 'sunshine-photo-cart' ); ?> &rarr;</a></p>
			</div>

			<script>
			jQuery( document ).ready( function($){
				var sunshine_cart_change = false;
				$( '#sunshine input' ).change(function(){
					sunshine_cart_change = true;
				});
				$( '#sunshine--cart--checkout-button a' ).click(function(){
					if ( sunshine_cart_change ) {
						var r = confirm( '<?php echo esc_js( 'You have changed items in your cart but have not yet updated. Do you want to continue to checkout?', 'sunshine-photo-cart' ); ?>');
						if ( !r ) {
							return false;
						}
					}
				});
			});
			</script>

		<?php } else { ?>
			<p><?php esc_html_e( 'You do not have anything in your cart yet!', 'sunshine-photo-cart' ); ?></p>
		<?php } ?>

		<?php do_action( 'sunshine_after_cart' ); ?>

	</div>

	<?php do_action( 'sunshine_after_content' ); ?>

</div>
