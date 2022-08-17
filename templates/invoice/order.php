<style type="text/css">
    @page {
        header: html_pageHeader;
        footer: html_pageFooter;
    }
    body, div, h1, h2, p, td, th { font-family: sans-serif; font-size: 12px; text-align: left; }
    table { margin: 0; padding: 0; border-spacing: 0; border-collapse: separate; }
    table th, table td { vertical-align: top; }

    #pageheader { width: 100%; }
    #logo { font-size: 26px; font-weight: bold; }
    #logo img { margin: 0 0 5px 0; max-height: 50px; width: auto;}
    #title { font-size: 26px; font-weight: bold; text-transform: uppercase; text-align: right; }

    #header { padding: 20px 0; width: 100%; }
    #address { font-size: 16px; }
    #basics { text-align: right; }
    #basics table { width: auto; }
    #basics table th { padding: 0 15px 5px 0; font-size: 14px; }
    #basics table td { padding: 0 0 5px 0; font-size: 14px; }

    #order-status { text-align: center; background: #EFEFEF; padding: 10px; font-weight: bold; }

    #data { padding: 20px 0; }
    #general, #shipping { padding: 0 50px 0 0; }
    #general table { width: auto; }
    #general table th { padding: 0 15px 5px 0; }
    #general table td { padding: 0 0 5px 0; }

    #sunshine-cart-items { width: 100%; padding: 0 0 30px 0; }
    #sunshine-cart-items thead th { background: #f1f1f1; padding: 5px; font-weight: normal; font-size: 10; text-transform: uppercase; color: #999; }
    #sunshine-cart-items tbody td { padding: 10px 5px; border-bottom: 1px solid #f1f1f1; }
    td.sunshine-cart-item-image { width: 75px; }
    td.sunshine-cart-item-image img { display: block; width: 75px; height: auto; }
    td.sunshine-cart-item-qty,
    td.sunshine-cart-item-price,
    td.sunshine-cart-item-total { width: 10%; }

    #sunshine-order-totals { margin-left: auto; }
    #sunshine-order-totals th { padding: 0 15px 5px 0; font-size: 14px; text-align: right; }
    #sunshine-order-totals td { padding: 0 0 5px 0; font-size: 14px; text-align: right; }

</style>

<htmlpageheader name="pageHeader" style="display:none">
    <table id="pageheader">
        <tr>
            <td id="logo">
            <?php
            if ( SPC()->get_option( 'template_logo' ) > 0 ) {
                echo '<img src="' . get_attached_file( SPC()->get_option( 'template_logo' ) ) . '" alt="" style="max-height:50px;" />';
            } else {
                bloginfo( 'name' );
            }
            ?>
            </td>
            <td id="title">
                <?php echo apply_filters( 'sunshine_invoice_title', __( 'Invoice', 'sunshine-photo-cart' ), $order ); ?>
            </td>
        </tr>
    </table>
</htmlpageheader>

<table id="header">
    <tr>
        <td id="address">
            <?php
            $address = array(
    			'address1' => SPC()->get_option( 'address1' ),
    			'address2' => SPC()->get_option( 'address2' ),
    			'city' => SPC()->get_option( 'city' ),
    			'state' => SPC()->get_option( 'state' ),
    			'postcode' => SPC()->get_option( 'postcode' ),
    			'country' => SPC()->get_option( 'country' ),
            );
            echo SPC()->countries->get_formatted_address( $address );
            do_action( 'sunshine_invoice_after_address', $order );
            ?>
        </td>
        <td id="basics">
            <table>
                <tr>
                    <th colspan="2"><?php echo $order->get_name(); ?></th>
                </tr>
                <tr>
                    <th><?php _e( 'Date', 'sunshine-photo-cart' ); ?></th>
                    <td><?php echo $order->get_date( get_option( 'date_format' ) ); ?></td>
                </tr>
                <tr>
                    <th><?php _e( 'Total', 'sunshine-photo-cart' ); ?></th>
                    <td><?php echo $order->get_total_formatted(); ?></td>
                </tr>
            </table>
            <?php do_action( 'sunshine_invoice_after_basics', $order ); ?>
        </td>
    </tr>
</table>

<div id="order-status"><?php echo $order->get_status_name(); ?>: <?php echo $order->get_status_description(); ?></div>

<table id="data">
    <tr>
        <td id="general">
            <table>
                <tr>
                    <th><?php _e( 'Payment Method', 'sunshine-photo-cart' ); ?></th>
                    <td><?php echo $order->get_payment_method_name(); ?></td>
                </tr>
                <tr>
                    <th><?php _e( 'Shipping Method', 'sunshine-photo-cart' ); ?></th>
                    <td><?php echo $order->get_shipping_method_name(); ?></td>
                </tr>
                <?php if ( $order->get_vat() ) { ?>
                    <tr>
                        <th><?php echo ( SPC()->get_option( 'vat_label' ) ) ? SPC()->get_option( 'vat_label' ) : __( 'EU VAT Number', 'sunshine-photo-cart' ); ?></th>
                        <td><?php echo $order->get_vat(); ?></td>
                    </tr>
                <?php } ?>
            </table>
        </td>
        <?php if ( $order->has_billing_address() ) { ?>
            <td id="shipping"><strong><?php _e( 'Billing Address', 'sunshine-photo-cart' ); ?></strong><br /><?php echo $order->get_billing_address_formatted(); ?></td>
        <?php } ?>
        <?php if ( $order->has_shipping_address() ) { ?>
            <td id="billing"><strong><?php _e( 'Shipping Address', 'sunshine-photo-cart' ); ?></strong><br /><?php echo $order->get_shipping_address_formatted(); ?></td>
        <?php } ?>
    </tr>
</table>
<?php do_action( 'sunshine_invoice_after_address', $order ); ?>

<?php $cart = $order->get_cart(); ?>
<table id="sunshine-cart-items">
<thead>
    <tr>
        <th class="sunshine-cart-image"><?php esc_html_e( 'Image', 'sunshine-photo-cart' ); ?></th>
        <th class="sunshine-cart-name"><?php esc_html_e( 'Product', 'sunshine-photo-cart' ); ?></th>
        <th class="sunshine-cart-qty"><?php esc_html_e( 'Qty', 'sunshine-photo-cart' ); ?></th>
        <th class="sunshine-cart-price"><?php esc_html_e( 'Item Price', 'sunshine-photo-cart' ); ?></th>
        <th class="sunshine-cart-total"><?php esc_html_e( 'Item Total', 'sunshine-photo-cart' ); ?></th>
    </tr>
</thead>
<tbody>
<?php foreach ( $cart as $cart_item ) { ?>
    <tr class="sunshine-cart-item <?php echo $cart_item->classes(); ?>">
        <td class="sunshine-cart-item-image" data-label="<?php esc_attr_e( 'Image', 'sunshine-photo-cart' ); ?>">
            <?php echo $cart_item->get_image_html( '', '', array( 'width' => '50' ) ); ?>
        </td>
        <td class="sunshine-cart-item-name" data-label="<?php esc_attr_e( 'Product', 'sunshine-photo-cart' ); ?>">
            <div class="sunshine-cart-item-name-image"><?php echo strip_tags( $cart_item->get_image_name() ); ?></div>
            <div class="sunshine-cart-item-name-product"><?php echo $cart_item->get_name(); ?></div>
            <div class="sunshine-cart-item-comments"><?php echo $cart_item->get_comments(); ?></div>
        </td>
        <td class="sunshine-cart-item-qty" data-label="<?php esc_attr_e( 'Qty', 'sunshine-photo-cart'); ?>">
            <?php echo $cart_item->get_qty(); ?>
        </td>
        <td class="sunshine-cart-item-price" data-label="<?php esc_attr_e( 'Price', 'sunshine-photo-cart' ); ?>">
            <?php echo $cart_item->get_price_formatted(); ?>
        </td>
        <td class="sunshine-cart-item-total" data-label="<?php esc_attr_e( 'Total', 'sunshine-photo-cart' ); ?>">
            <?php echo $cart_item->get_total_formatted(); ?>
        </td>
    </tr>
<?php } ?>
</tbody>
</table>

<table id="sunshine-order-totals">
    <tr class="sunshine-subtotal">
        <th><?php _e( 'Subtotal', 'sunshine-photo-cart' ); ?></th>
        <td><?php echo $order->get_subtotal_formatted(); ?></td>
    </tr>
    <?php if ( !empty( $order->get_shipping() ) ) { ?>
    <tr class="sunshine-shipping">
        <th><?php echo sprintf( __( 'Shipping via %s', 'sunshine-photo-cart' ), $order->get_shipping_method_name() ); ?></th>
        <td><?php echo $order->get_shipping_formatted(); ?></td>
    </tr>
    <?php } ?>
    <?php if ( !empty( $order->get_discounts() ) ) { ?>
    <tr class="sunshine-discount">
        <th><?php _e( 'Discounts', 'sunshine-photo-cart' ); ?></th>
        <td><?php echo $order->get_discount_formatted(); ?></td>
    </tr>
    <?php } ?>
    <?php if ( $order->get_tax() ) { ?>
    <tr class="sunshine-tax">
        <th><?php _e( 'Tax', 'sunshine-photo-cart' ); ?></th>
        <td><?php echo $order->get_tax_formatted(); ?></td>
    </tr>
    <?php } ?>
    <?php if ( $order->get_credits() > 0 ) { ?>
    <tr class="sunshine-credits">
        <th><?php _e( 'Credits Applied', 'sunshine-photo-cart' ); ?></th>
        <td><?php echo $order->get_credits_formatted(); ?></td>
    </tr>
    <?php } ?>
    <tr class="sunshine-total">
        <th><?php _e( 'Order Total', 'sunshine-photo-cart' ); ?></th>
        <td><?php echo $order->get_total_formatted(); ?></td>
    </tr>
</table>

<htmlpagefooter name="pageFooter" style="display:none">
    <div id="pages">{PAGENO}/{nbpg}</div>
</htmlpagefooter>
