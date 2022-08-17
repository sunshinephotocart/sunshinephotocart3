<?php $cart = $order->get_cart();?>
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
<?php foreach ( $cart as $cart_item ) { ?>
    <tr class="sunshine--cart-item <?php echo $cart_item->classes(); ?>">
        <td class="sunshine--cart-item--image" data-label="<?php esc_attr_e( 'Image', 'sunshine-photo-cart' ); ?>">
            <?php echo $cart_item->get_image_html(); ?>
        </td>
        <td class="sunshine--cart-item--name" data-label="<?php esc_attr_e( 'Product', 'sunshine-photo-cart' ); ?>">
            <div class="sunshine--cart-item--image-name"><?php echo $cart_item->get_image_name(); ?></div>
            <div class="sunshine--cart-item--product-name"><?php echo $cart_item->get_name(); ?></div>
            <div class="sunshine--cart-item--comments"><?php echo $cart_item->get_comments(); ?></div>
        </td>
        <td class="sunshine--cart-item--qty" data-label="<?php esc_attr_e( 'Qty', 'sunshine-photo-cart'); ?>">
            <?php echo $cart_item->get_qty(); ?>
        </td>
        <td class="sunshine--cart-item--price" data-label="<?php esc_attr_e( 'Price', 'sunshine-photo-cart' ); ?>">
            <?php echo $cart_item->get_price_formatted(); ?>
        </td>
        <td class="sunshine--cart-item--total" data-label="<?php esc_attr_e( 'Total', 'sunshine-photo-cart' ); ?>">
            <?php echo $cart_item->get_total_formatted(); ?>
        </td>
    </tr>
<?php } ?>
</tbody>
</table>
