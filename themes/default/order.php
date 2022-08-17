<?php load_template(SUNSHINE_PHOTO_CART_PATH.'themes/default/header.php'); ?>

<?php
global $sunshine;
$order_data = sunshine_get_order_data(SPC()->frontend->current_order->ID);
$order_items = sunshine_get_order_items(SPC()->frontend->current_order->ID);
$customer_id = get_post_meta( SPC()->frontend->current_order->ID, '_sunshine_customer_id', true );
$status = sunshine_get_order_status(SPC()->frontend->current_order->ID);
?>
<h1><?php echo sprintf( esc_html__( 'Order #%s', 'sunshine-photo-cart' ), SPC()->frontend->current_order->ID ); ?></h1>

<p id="sunshine-order-status" class="sunshine-status-<?php echo esc_attr( $status->slug ); ?>">
	<strong><?php echo esc_html( $status->name ); ?>:</strong> <?php echo wp_kses_post( $status->description ); ?>
</p>
<?php do_action( 'sunshine_order_notes', SPC()->frontend->current_order->ID ); ?>
<div class="sunshine-form" id="sunshine-order">
	<div id="sunshine-order-contact-fields" class="sunshine-clearfix">
		<h2><?php esc_html_e('Contact Information','sunshine-photo-cart'); ?></h2>
		<div class="field field-left"><label><?php esc_html_e('Email','sunshine-photo-cart'); ?></label> <?php echo esc_html( $order_data['email'] ); ?></div>
		<?php if ( $order_data['phone'] ) { ?>
		<div class="field field-right"><label><?php esc_html_e('Phone','sunshine-photo-cart'); ?></label> <?php echo esc_html( $order_data['phone'] ); ?></div>
		<?php } ?>
	</div>
	<div id="sunshine-order-billing-fields">
		<h2><?php _e('Billing Information','sunshine-photo-cart'); ?></h2>
		<div class="field field-left"><label><?php esc_html_e('First Name','sunshine-photo-cart'); ?></label> <?php echo esc_html( $order_data['first_name'] ); ?></div>
		<div class="field field-right"><label><?php esc_html_e('Last Name','sunshine-photo-cart'); ?></label> <?php echo esc_html( $order_data['last_name'] ); ?></div>
		<div class="field field-left"><label><?php esc_html_e('Address','sunshine-photo-cart'); ?></label> <?php echo esc_html( $order_data['address'] ); ?></div>
		<div class="field field-right"><label><?php esc_html_e('Address 2','sunshine-photo-cart'); ?>	</label> <?php echo esc_html( $order_data['address2'] ); ?></div>
		<div class="field field-left"><label><?php esc_html_e('City','sunshine-photo-cart'); ?></label> <?php echo esc_html( $order_data['city'] ); ?></div>
		<div class="field field-right"><label><?php esc_html_e('State / Province','sunshine-photo-cart'); ?></label> <?php echo esc_html( ( isset( SunshineCountries::$states[$order_data['country']][$order_data['state']] ) ) ? SunshineCountries::$states[$order_data['country']][$order_data['state']] : $order_data['state'] ); ?></div>
		<div class="field field-left"><label><?php esc_html_e('Zip / Postcode','sunshine-photo-cart'); ?></label> <?php echo esc_html( $order_data['zip'] ); ?></div>
		<div class="field field-right"><label><?php esc_html_e('Country','sunshine-photo-cart'); ?></label> <?php echo esc_html( SunshineCountries::$countries[$order_data['country']] ); ?></div>
	</div>
	<?php if ( !empty( $order_data['shipping_first_name'] ) ) { ?>
		<div id="sunshine-order-shipping-fields">
			<h2><?php _e('Shipping Information','sunshine-photo-cart'); ?></h2>
			<div class="field field-left"><label><?php esc_html_e('First Name','sunshine-photo-cart'); ?></label> <?php echo esc_html( $order_data['shipping_first_name'] ); ?></div>
			<div class="field field-right"><label><?php esc_html_e('Last Name','sunshine-photo-cart'); ?></label> <?php echo esc_html( $order_data['shipping_last_name'] ); ?></div>
			<div class="field field-left"><label><?php esc_html_e('Address','sunshine-photo-cart'); ?></label> <?php echo esc_html( $order_data['shipping_address'] ); ?></div>
			<div class="field field-right"><label><?php esc_html_e('Address 2','sunshine-photo-cart'); ?>	</label> <?php echo esc_html( $order_data['shipping_address2'] ); ?></div>
			<div class="field field-left"><label><?php esc_html_e('City','sunshine-photo-cart'); ?></label> <?php echo esc_html( $order_data['shipping_city'] ); ?></div>
			<div class="field field-right"><label><?php _e('State / Province','sunshine-photo-cart'); ?></label> <?php echo esc_html( ( isset( SunshineCountries::$states[$order_data['shipping_country']][$order_data['shipping_state']] ) ) ? SunshineCountries::$states[$order_data['shipping_country']][$order_data['shipping_state']] : $order_data['shipping_state'] ); ?></div>
			<div class="field field-left"><label><?php esc_html_e('Zip / Postcode','sunshine-photo-cart'); ?></label> <?php echo esc_html( $order_data['shipping_zip'] ); ?></div>
			<div class="field field-right"><label><?php esc_html_e('Country','sunshine-photo-cart'); ?></label> <?php echo esc_html( SunshineCountries::$countries[$order_data['shipping_country']] ); ?></div>
		</div>
	<?php } ?>
</div>
<div id="sunshine-order-cart-items">
	<h2><?php esc_html_e('Items','sunshine-photo-cart'); ?></h2>
	<?php do_action('sunshine_before_order_items', SPC()->frontend->current_order->ID, $order_items); ?>
	<table id="sunshine-cart-items">
	<tr>
		<th class="sunshine-cart-image"><?php esc_html_e('Image','sunshine-photo-cart'); ?></th>
		<th class="sunshine-cart-name"><?php esc_html_e('Product','sunshine-photo-cart'); ?></th>
		<th class="sunshine-cart-qty"><?php esc_html_e('Qty','sunshine-photo-cart'); ?></th>
		<th class="sunshine-cart-price"><?php esc_html_e('Item Price','sunshine-photo-cart'); ?></th>
		<th class="sunshine-cart-total"><?php esc_html_e('Item Total','sunshine-photo-cart'); ?></th>
	</tr>
	<?php
	$i = 1; foreach ($order_items as $item) {
	?>
		<tr class="sunshine-cart-item">
			<td class="sunshine-cart-item-image">
				<?php
				$thumb = wp_get_attachment_image_src($item['image_id'], 'sunshine-thumbnail');
				$image_html = '<a href="'.get_permalink($item['image_id']).'"><img src="'.esc_url( $thumb[0] ).'" alt="" class="sunshine-image-thumb" /></a>';
				echo wp_kses_post( apply_filters('sunshine_order_image_html', $image_html, $item, $thumb ) );
				?>
			</td>
			<td class="sunshine-cart-item-name">
				<?php
				$product = get_post($item['product_id']);
				$cat = wp_get_post_terms($item['product_id'], 'sunshine-product-category');
				?>
				<h3><span class="sunshine-item-cat"><?php echo esc_html( apply_filters( 'sunshine_cart_item_category', (isset($cat[0]->name)) ? $cat[0]->name : '', $item ) ); ?></span> - <span class="sunshine-item-name"><?php echo esc_html( apply_filters('sunshine_cart_item_name', $product->post_title, $item ) ); ?></span></h3>
				<div class="sunshine-item-comments"><?php echo wp_kses_post( apply_filters('sunshine_order_line_item_comments', $item['comments'], SPC()->frontend->current_order->ID, $item ) ); ?></div>
			</td>
			<td class="sunshine-cart-item-qty">
				<?php echo esc_html( $item['qty'] ); ?>
			</td>
			<td class="sunshine-cart-item-price">
				<?php
				if ( empty( $item['price_with_tax'] ) ) {
					sunshine_money_format( $item['price'], true, true );
				} else {
					sunshine_money_format( $item['price_with_tax'] );
				}
				?>
			</td>
			<td class="sunshine-cart-item-total">
				<?php
				if ( empty( $item['total_with_tax'] ) ) {
					sunshine_money_format( $item['total'], true, true );
				} else {
					sunshine_money_format( $item['total_with_tax'] );
				}
				?>
			</td>
		</tr>

	<?php $i++; } ?>
	</table>

	<div id="sunshine-order-totals">
		<table>
		<tr class="sunshine-subtotal">
			<th><?php esc_html_e('Subtotal','sunshine-photo-cart'); ?></th>
			<td>
				<?php
				if ( empty( $order_data['subtotal_with_tax'] ) ) {
					sunshine_money_format( $order_data['subtotal'], true, true );
				} else {
					sunshine_money_format( $order_data['subtotal_with_tax'] );
				}
				?>
			</td>
		</tr>
		<?php if ( $order_data['shipping_method'] ) { ?>
		<tr class="sunshine-shipping">
			<th><?php esc_html_e('Shipping','sunshine-photo-cart'); ?> (<?php echo esc_html( sunshine_get_shipping_method_name( $order_data['shipping_method'] ) ); ?>)</th>
			<td>
				<?php
				if ( empty( $order_data['shipping_with_tax'] ) ) {
					sunshine_money_format( $order_data['shipping_cost'], true, true );
				} else {
					sunshine_money_format( $order_data['shipping_with_tax'] );
				}
				?>
			</td>
		</tr>
		<?php } ?>
		<?php if ( $order_data['discount_total'] > 0 ) { ?>
		<tr class="sunshine-discounts">
			<th><?php esc_html_e('Discounts','sunshine-photo-cart'); ?></th>
			<td>-<?php sunshine_money_format( $order_data['discount_total'] ); ?></td>
		</tr>
		<?php } ?>
		<?php if ( empty( $order_data['subtotal_with_tax'] ) && $order_data['tax'] > 0 ) { ?>
		<tr class="sunshine-tax">
			<th><?php esc_html_e('Tax','sunshine-photo-cart'); ?></th>
			<td><?php sunshine_money_format( $order_data['tax'], true, false ); ?></td>
		</tr>
		<?php } ?>
		<?php if ($order_data['credits'] > 0) { ?>
		<tr class="sunshine-credits">
			<th><?php esc_html_e('Credits','sunshine-photo-cart'); ?></th>
			<td>-<?php sunshine_money_format( $order_data['credits'], true, true ); ?></td>
		</tr>
		<?php } ?>

		<tr class="sunshine-total">
			<th><?php esc_html_e('Total','sunshine-photo-cart'); ?></th>
			<td>
				<?php
				sunshine_money_format( $order_data['total'] );
				?>
			</td>
		</tr>
		</table>
	</div>
</div>

<?php if ( $order_data['notes'] ) { ?>
	<h3><?php esc_html_e( 'Additional Order Notes', 'sunshine-photo-cart' ); ?></h3>
	<p><?php echo wp_kses_post( nl2br( htmlspecialchars( $order_data['notes'] ) ) ); ?></p>
<?php } ?>

<?php if ( $customer_id ) { ?>
<div id="sunshine-order-comments">
	<h2><?php esc_html_e( 'Order Comments', 'sunshine-photo-cart' ); ?></h2>
	<?php
	$comments = get_comments('post_id='.SPC()->frontend->current_order->ID.'&post_type=sunshine-order&order=ASC');
	if ($comments) {
	?>
	<ol>
	<?php
	wp_list_comments('type=comment&avatar_size=0', $comments);
	?>
	</ol>
	<?php
	}
	$sunshine->comment_status = 'IN_SUNSHINE';
	comment_form(
		array(
			'comment_notes_before' => '',
			'comment_notes_after' => '',
			'logged_in_as' => '',
			'id_form' => 'sunshine-order-comment',
			'id_submit' => 'sunshine-submit',
			'title_reply' => __('Add Comment', 'sunshine-photo-cart')
		),
		SPC()->frontend->current_order->ID
	);
	$sunshine->comment_status = '';
	?>
</div>
<?php } ?>

<?php load_template(SUNSHINE_PHOTO_CART_PATH.'themes/default/footer.php'); ?>
