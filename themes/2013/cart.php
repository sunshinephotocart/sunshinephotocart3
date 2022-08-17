<?php load_template(SUNSHINE_PHOTO_CART_PATH.'themes/2013/header.php'); ?>

<h1><?php _e('Cart', 'sunshine-photo-cart'); ?></h1>

<?php echo apply_filters('the_content', $post->post_content); ?>

<form method="post" action="" id="sunshine-cart">
<input type="hidden" name="sunshine_update_cart" value="1" />
<input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'sunshine_update_cart' ); ?>" />

<?php do_action( 'sunshine_before_cart_items' ); ?>

<?php if ( SPC()->cart->get_cart() ) { ?>
	<table id="sunshine-cart-items">
	<tr>
		<th class="sunshine-cart-image"><?php esc_html_e('Image', 'sunshine-photo-cart'); ?></th>
		<th class="sunshine-cart-name"><?php esc_html_e('Product', 'sunshine-photo-cart'); ?></th>
		<th class="sunshine-cart-qty"><?php esc_html_e('Qty', 'sunshine-photo-cart'); ?></th>
		<th class="sunshine-cart-price"><?php esc_html_e('Item Price', 'sunshine-photo-cart'); ?></th>
		<th class="sunshine-cart-total"><?php esc_html_e('Item Total', 'sunshine-photo-cart'); ?></th>
	</tr>
	<?php $i = 1; $tabindex = 0; foreach ( SPC()->cart->get_cart() as $item ) { $tabindex++; ?>
		<tr class="sunshine-cart-item <?php sunshine_product_class($item['product_id']); ?>">
			<td class="sunshine-cart-item-image" data-label="<?php esc_attr_e('Image', 'sunshine-photo-cart'); ?>">
				<?php
				$thumb = wp_get_attachment_image_src( intval( $item['image_id'] ), 'sunshine-thumbnail' );
				$image_html = '<a href="' . get_permalink( intval( $item['image_id'] ) ).'"><img src="'.esc_url( $thumb[0] ).'" alt="" class="sunshine-image-thumb" /></a>';
				echo wp_kses_post( apply_filters( 'sunshine_cart_image_html', $image_html, $item, $thumb ) );
				?>
			</td>
			<td class="sunshine-cart-item-name" data-label="<?php esc_attr_e('Product', 'sunshine-photo-cart'); ?>">
				<?php
				$product = get_post( intval( $item['product_id'] ) );
				$cat = wp_get_post_terms( intval( $item['product_id'] ), 'sunshine-product-category' );
				?>
				<h2><span class="sunshine-item-cat"><?php echo esc_html( apply_filters( 'sunshine_cart_item_category', (isset($cat[0]->name)) ? $cat[0]->name : '', $item ) ); ?></span> - <span class="sunshine-item-name"><?php echo esc_html( apply_filters( 'sunshine_cart_item_name', $product->post_title, $item ) ); ?></span></h2>
				<div class="sunshine-item-comments"><?php echo wp_kses_post( apply_filters('sunshine_cart_item_comments', $item['comments'], $item ) ); ?></div>
			</td>
			<td class="sunshine-cart-item-qty" data-label="<?php esc_attr_e('Qty', 'sunshine-photo-cart'); ?>">
				<input type="number" name="item[<?php echo esc_attr( $i ); ?>][qty]" class="sunshine-qty" value="<?php echo $item['qty']; ?>" size="4" tabindex="<?php echo esc_attr( $tabindex ); ?>" min="0" />
				<a href="?delete_cart_item=<?php echo esc_attr( $item['hash'] ); ?>&nonce=<?php echo wp_create_nonce( 'sunshine_delete_cart_item' ); ?>"><?php esc_html_e( 'Remove','sunshine-photo-cart' ); ?></a>
			</td>
			<td class="sunshine-cart-item-price" data-label="<?php esc_attr_e( 'Price', 'sunshine-photo-cart' ); ?>">
				<?php
				if ( empty( $item['price_with_tax'] ) ) {
					sunshine_money_format( $item['price'], true, true );
				} else {
					sunshine_money_format( $item['price_with_tax'] );
				}
				?>
			</td>
			<td class="sunshine-cart-item-total" data-label="<?php esc_attr_e( 'Total', 'sunshine-photo-cart' ); ?>">
				<?php
				if ( empty( $item['total_with_tax'] ) ) {
					sunshine_money_format( $item['total'], true, true );
				} else {
					sunshine_money_format( $item['total_with_tax'] );
				}
				?>
				<input type="hidden" name="item[<?php echo esc_attr( $i ); ?>][image_id]" value="<?php echo esc_attr( $item['image_id'] ); ?>" />
				<input type="hidden" name="item[<?php echo esc_attr( $i ); ?>][product_id]" value="<?php echo esc_attr( $item['product_id'] ); ?>" />
				<input type="hidden" name="item[<?php echo esc_attr( $i ); ?>][comments]" value="<?php echo esc_attr( $item['comments'] ); ?>" />
				<input type="hidden" name="item[<?php echo esc_attr( $i ); ?>][hash]" value="<?php echo esc_attr( $item['hash'] ); ?>" />
			</td>
		</tr>
	<?php $i++; } ?>
	</table>

	<?php do_action('sunshine_after_cart_items'); ?>

	<div id="sunshine-cart-update-button">
		<input type="submit" value="<?php esc_attr_e('Update Cart', 'sunshine-photo-cart'); ?>" class="sunshine-button-alt" />
	</div>

	</form>

	<?php do_action( 'sunshine_after_cart_form' ); ?>

	<div id="sunshine-cart-totals">
		<?php sunshine_cart_totals(); ?>
		<p id="sunshine-cart-checkout-button"><a href="<?php echo sunshine_url( 'checkout' ); ?>" class="sunshine-button"><?php esc_html_e( 'Continue to checkout', 'sunshine-photo-cart' ); ?> &rarr;</a></p>
	</div>

	<script>
	jQuery(document).ready(function($){
		var sunshine_cart_change = false;
		$('#sunshine input').change(function(){
			sunshine_cart_change = true;
		});
		$('#sunshine-cart-checkout-button a').click(function(){
			if ( sunshine_cart_change ) {
				var r = confirm( '<?php echo esc_js( __( 'You have changed items in your cart but have not yet updated. Do you want to continue to checkout?', 'sunshine-photo-cart' ) ); ?>');
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

<?php do_action('sunshine_after_cart'); ?>

<?php load_template(SUNSHINE_PHOTO_CART_PATH.'themes/2013/footer.php'); ?>
