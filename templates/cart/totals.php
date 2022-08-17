<table id="sunshine--cart--totals">
    <tr class="sunshine--cart--subtotal">
        <th><?php _e( 'Subtotal', 'sunshine-photo-cart' ); ?></th>
        <td><?php echo SPC()->cart->get_subtotal_formatted(); ?></td>
    </tr>
    <!-- TODO
    <tr class="sunshine--cart--shipping">
        <th><?php _e( 'Shipping', 'sunshine-photo-cart' ); ?></th>
        <td><?php echo SPC()->cart->get_shipping_formatted(); ?></td>
    </tr>
    -->
    <?php if ( SPC()->cart->get_discount() > 0 ) { ?>
    <tr class="sunshine--cart--discount">
        <th><?php _e( 'Discounts', 'sunshine-photo-cart' ); ?></th>
        <td><?php echo SPC()->cart->get_discounts_total_formatted(); ?></td>
    </tr>
    <?php } ?>
    <?php if ( SPC()->get_option( 'display_price' ) != 'with_tax' && ( !empty( SPC()->get_option( 'tax_location' ) ) && SPC()->get_option( 'tax_location' ) != '' && !empty( SPC()->get_option( 'tax_rate' ) ) && SPC()->get_option( 'tax_rate' ) != '' ) ) { ?>
    <tr class="sunshine--cart--tax">
        <th><?php _e( 'Tax', 'sunshine-photo-cart' ); ?></th>
        <td><?php echo SPC()->cart->get_tax_formatted(); ?></td>
    </tr>
    <?php } ?>
    <?php if ( SPC()->cart->use_credits() ) { ?>
    <tr class="sunshine--cart--credits">
        <th><?php _e( 'Credits', 'sunshine-photo-cart' ); ?></th>
        <td>
            <?php
            $credits = SPC()->cart->get_credits_applied();
            if ( $credits ) {
                echo '-' . SPC()->cart->get_credits_applied_formatted();
            }
            ?>
        </td>
    </tr>
    <?php } ?>
    <tr class="sunshine--cart--total">
        <th><?php _e( 'Total', 'sunshine-photo-cart' ); ?></th>
        <td><?php echo SPC()->cart->get_total_formatted(); ?></td>
    </tr>
</table>
