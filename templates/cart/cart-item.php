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
        <input type="number" name="item[<?php echo esc_attr( $iterator ); ?>][qty]" class="sunshine--qty" value="<?php echo $cart_item->get_qty(); ?>" size="4" tabindex="<?php echo esc_attr( $iterator ); ?>" min="0" />
        <a href="<?php echo $cart_item->get_remove_url(); ?>" class="sunshine--cart-item--delete"></a>
    </td>
    <td class="sunshine--cart-item--price" data-label="<?php esc_attr_e( 'Price', 'sunshine-photo-cart' ); ?>">
        <?php echo $cart_item->get_price_formatted(); ?>
    </td>
    <td class="sunshine--cart-item--total" data-label="<?php esc_attr_e( 'Total', 'sunshine-photo-cart' ); ?>">
        <?php echo $cart_item->get_total_formatted(); ?>
        <input type="hidden" name="item[<?php echo esc_attr( $iterator ); ?>][object_id]" value="<?php echo esc_attr( $cart_item->object->get_id() ); ?>" />
        <input type="hidden" name="item[<?php echo esc_attr( $iterator ); ?>][product_id]" value="<?php echo esc_attr( $cart_item->product->get_id() ); ?>" />
        <input type="hidden" name="item[<?php echo esc_attr( $iterator ); ?>][hash]" value="<?php echo esc_attr( $cart_item->get_hash() ); ?>" />
    </td>
</tr>
