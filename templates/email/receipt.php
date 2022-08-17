<h2><?php echo sprintf( __( '%s &mdash; %s', 'sunshine-photo-cart' ), $order->get_name(), $order->get_total_formatted() ); ?></h2>

<p id="order-status"><?php echo $order->get_status_name(); ?>: <?php echo $order->get_status_description(); ?></p>

<dl id="order-details">
    <dt><?php _e( 'Date', 'sunshine-photo-cart' ); ?></dt>
    <dd><?php echo $order->get_date(); ?></d>
    <dt><?php _e( 'Payment Method', 'sunshine-photo-cart' ); ?></dt>
    <dd><?php echo $order->get_payment_method_name(); ?></dd>
    <dt><?php _e( 'Shipping Method', 'sunshine-photo-cart' ); ?></dt>
    <dd><?php echo $order->get_shipping_method_name(); ?></d>
    <?php if ( $order->get_vat() ) { ?>
        <dt><?php echo ( SPC()->get_option( 'vat_label' ) ) ? SPC()->get_option( 'vat_label' ) : __( 'EU VAT Number', 'sunshine-photo-cart' ); ?></dt>
        <dd><?php echo $order->get_vat(); ?></d>
    <?php } ?>
</dl>
<?php do_action( 'sunshine_email_receipt_after_order_general', $order ); ?>

<table id="order-addresses">
    <tr>
    <td id="order-shipping">
        <h3><?php _e( 'Shipping', 'sunshine-photo-cart' ); ?></h3>
        <?php if ( $order->has_shipping_address() ) { ?>
            <p><?php echo $order->get_shipping_address_formatted(); ?></p>
        <?php } else { ?>
            <p><?php _e( 'No shipping information collected for this order', 'sunshine-photo-cart' ); ?>
        <?php } ?>
        <?php do_action( 'sunshine_email_receipt_after_order_shipping', $order ); ?>
    </td>
    <td id="order-billing">
        <h3><?php _e( 'Billing', 'sunshine-photo-cart' ); ?></h3>
        <?php if ( $order->has_billing_address() ) { ?>
            <p><?php echo $order->get_billing_address_formatted(); ?></p>
        <?php } else { ?>
            <p><?php _e( 'No billing information collected for this order', 'sunshine-photo-cart' ); ?>
        <?php } ?>
        <?php do_action( 'sunshine_email_receipt_after_order_billing', $order ); ?>
    </td>
    </tr>
</table>

<?php $cart = $order->get_cart();?>
<table id="order-items">
<thead>
    <tr>
        <th class="order-item-image"><?php esc_html_e( 'Image', 'sunshine-photo-cart' ); ?></th>
        <th class="order-item-name"><?php esc_html_e( 'Product', 'sunshine-photo-cart' ); ?></th>
        <th class="order-item-qty"><?php esc_html_e( 'Qty', 'sunshine-photo-cart' ); ?></th>
        <th class="order-item-price"><?php esc_html_e( 'Item Price', 'sunshine-photo-cart' ); ?></th>
        <th class="order-item-total"><?php esc_html_e( 'Item Total', 'sunshine-photo-cart' ); ?></th>
    </tr>
</thead>
<tbody>
<?php foreach ( $cart as $cart_item ) { ?>
    <tr class="order-item <?php echo $cart_item->classes(); ?>">
        <td class="order-item-image" data-label="<?php esc_attr_e( 'Image', 'sunshine-photo-cart' ); ?>">
            <?php echo $cart_item->get_image_html(); ?>
        </td>
        <td class="order-item-name" data-label="<?php esc_attr_e( 'Product', 'sunshine-photo-cart' ); ?>">
            <div class="sunshine-cart-item-name-image"><?php echo $cart_item->get_image_name(); ?></div>
            <div class="sunshine-cart-item-name-product"><?php echo $cart_item->get_name(); ?></div>
            <div class="sunshine-cart-item-comments"><?php echo $cart_item->get_comments(); ?></div>
        </td>
        <td class="order-item-qty" data-label="<?php esc_attr_e( 'Qty', 'sunshine-photo-cart'); ?>">
            <?php echo $cart_item->get_qty(); ?>
        </td>
        <td class="order-item-price" data-label="<?php esc_attr_e( 'Price', 'sunshine-photo-cart' ); ?>">
            <?php echo $cart_item->get_price_formatted(); ?>
        </td>
        <td class="order-item-total" data-label="<?php esc_attr_e( 'Total', 'sunshine-photo-cart' ); ?>">
            <?php echo $cart_item->get_total_formatted(); ?>
        </td>
    </tr>
<?php } ?>
</tbody>
</table>

<table id="order-totals">
    <tr class="order-subtotal">
        <th><?php _e( 'Subtotal', 'sunshine-photo-cart' ); ?></th>
        <td><?php echo $order->get_subtotal_formatted(); ?></td>
    </tr>
    <?php if ( !empty( $order->get_shipping() ) ) { ?>
    <tr class="order-shipping">
        <th><?php echo sprintf( __( 'Shipping via %s', 'sunshine-photo-cart' ), $order->get_shipping_method_name() ); ?></th>
        <td><?php echo $order->get_shipping_formatted(); ?></td>
    </tr>
    <?php } ?>
    <?php if ( !empty( $order->get_discounts() ) ) { ?>
    <tr class="order-discount">
        <th><?php _e( 'Discounts', 'sunshine-photo-cart' ); ?></th>
        <td><?php echo $order->get_discount_formatted(); ?></td>
    </tr>
    <?php } ?>
    <?php if ( $order->get_tax() ) { ?>
    <tr class="order-tax">
        <th><?php _e( 'Tax', 'sunshine-photo-cart' ); ?></th>
        <td><?php echo $order->get_tax_formatted(); ?></td>
    </tr>
    <?php } ?>
    <?php if ( $order->get_credits() > 0 ) { ?>
    <tr class="order-credits">
        <th><?php _e( 'Credits Applied', 'sunshine-photo-cart' ); ?></th>
        <td><?php echo $order->get_credits_formatted(); ?></td>
    </tr>
    <?php } ?>
    <tr class="order-total">
        <th><?php _e( 'Order Total', 'sunshine-photo-cart' ); ?></th>
        <td><?php echo $order->get_total_formatted(); ?></td>
    </tr>
</table>
