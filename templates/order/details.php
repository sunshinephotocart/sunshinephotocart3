<div id="sunshine--order--details">
    <div id="sunshine--order--data">
        <dl>
            <dt><?php _e( 'Date', 'sunshine-photo-cart' ); ?></dt>
            <dd><?php echo $order->get_date(); ?></d>
            <dt><?php _e( 'Payment Method', 'sunshine-photo-cart' ); ?></dt>
            <dd><?php echo $order->get_payment_method_name(); ?></dd>
            <dt><?php _e( 'Shipping Method', 'sunshine-photo-cart' ); ?></dt>
            <dd>
                <?php echo $order->get_delivery_method_name(); ?>
                <?php if ( $order->get_shipping_method() ) { ?>
                    (<?php echo $order->get_shipping_method_name(); ?>)
                <?php } ?>
            </dd>
            <?php if ( $order->get_vat() ) { ?>
                <dt><?php echo ( SPC()->get_option( 'vat_label' ) ) ? SPC()->get_option( 'vat_label' ) : __( 'EU VAT Number', 'sunshine-photo-cart' ); ?></dt>
                <dd><?php echo $order->get_vat(); ?></d>
            <?php } ?>
        </dl>
    </div>
    <div id="sunshine--order--shipping">
        <h3><?php _e( 'Shipping', 'sunshine-photo-cart' ); ?></h3>
        <?php if ( $order->has_shipping_address() ) { ?>
            <address><?php echo $order->get_shipping_address_formatted(); ?></address>
        <?php } else { ?>
            <p><?php _e( 'No shipping information collected for this order', 'sunshine-photo-cart' ); ?>
        <?php } ?>
    </div>
    <div id="sunshine--order--billing">
        <h3><?php _e( 'Billing', 'sunshine-photo-cart' ); ?></h3>
        <?php if ( $order->has_billing_address() ) { ?>
            <address><?php echo $order->get_billing_address_formatted(); ?></address>
        <?php } else { ?>
            <p><?php _e( 'No billing information collected for this order', 'sunshine-photo-cart' ); ?>
        <?php } ?>
    </div>
</div>
