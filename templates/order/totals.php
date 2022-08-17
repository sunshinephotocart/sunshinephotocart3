
<table id="sunshine--cart--totals">
    <tr class="sunshine--cart--subtotal">
        <th><?php _e( 'Subtotal', 'sunshine-photo-cart' ); ?></th>
        <td><?php echo $order->get_subtotal_formatted(); ?></td>
    </tr>
    <?php if ( !empty( $order->get_shipping() ) ) { ?>
    <tr class="sunshine--cart--shipping">
        <th><?php echo sprintf( __( 'Shipping via %s', 'sunshine-photo-cart' ), $order->get_shipping_method_name() ); ?></th>
        <td><?php echo $order->get_shipping_formatted(); ?></td>
    </tr>
    <?php } ?>
    <?php if ( !empty( $order->get_discounts() ) ) { ?>
    <tr class="sunshine--cart--discount">
        <th><?php _e( 'Discounts', 'sunshine-photo-cart' ); ?></th>
        <td><?php echo $order->get_discount_formatted(); ?></td>
    </tr>
    <?php } ?>
    <?php if ( $order->get_tax() ) { ?>
    <tr class="sunshine--cart--tax">
        <th><?php _e( 'Tax', 'sunshine-photo-cart' ); ?></th>
        <td><?php echo $order->get_tax_formatted(); ?></td>
    </tr>
    <?php } ?>
    <?php if ( $order->get_credits() > 0 ) { ?>
    <tr class="sunshine--cart--credits">
        <th><?php _e( 'Credits Applied', 'sunshine-photo-cart' ); ?></th>
        <td><?php echo '-' . $order->get_credits_formatted(); ?></td>
    </tr>
    <?php } ?>
    <tr class="sunshine--cart--total">
        <th><?php _e( 'Order Total', 'sunshine-photo-cart' ); ?></th>
        <td><?php echo $order->get_total_formatted(); ?></td>
    </tr>
</table>
