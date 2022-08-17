<div id="sunshine--image--cart-review">
    <?php echo sunshine_get_template_html( 'cart/mini-cart' ); ?>
</div>
<div id="sunshine--image--add-to-cart">
    <div id="sunshine--image--add-to-cart--header">
        <div id="sunshine--image--add-to-cart--header--image">
            <?php $image->output(); ?>
            <span><?php echo $image->get_name(); ?></span>
        </div>
    </div>
    <div id="sunshine--image--add-to-cart--products">
        <?php
        $product_categories = sunshine_get_product_categories();
        if ( !empty( $product_categories ) ) { ?>
            <nav id="sunshine--image--add-to-cart--categories">
                <ul>
                    <?php
                    $i = 0;
                    foreach ( $product_categories as $product_category ) {
                        $i++;
                        if ( $i == 1 ) {
                            $primary_category_id = $product_category->get_id();
                        }
                    ?>
                        <li><a href="#<?php echo $product_category->get_key(); ?>" data-id="<?php echo $product_category->get_id(); ?>"<?php echo ( $i == 1 ) ? ' class="active"' : ''; ?>><?php echo $product_category->get_name(); ?></a></li>
                    <?php } ?>
                </ul>
            </nav>
        <?php } ?>
        <div id="sunshine--image--add-to-cart--product-list">
            <?php
            $products = sunshine_get_products( $image->get_gallery()->get_price_level() );
            if ( !empty( $products ) ) {
            ?>
                <table>
                    <?php foreach ( $products as $product ) { ?>
                        <tr class="sunshine--category-<?php echo $product->get_category_id(); ?>"<?php echo ( $product->get_category_id() != $primary_category_id ) ? ' style="display: none;"' : ''; ?>>
                            <td class="sunshine--image--add-to-cart--product-list--name">
                                <?php $product->get_image_html(); ?>
                                <?php echo $product->get_name(); ?>
                            </td>
                            <td class="sunshine--image--add-to-cart--product-list--price">
                                <?php echo $product->get_price_formatted(); ?>
                            </td>
                            <td class="sunshine--image--add-to-cart--product-list--action">
                                <button class="sunshine--qty--down"><span><?php esc_html_e( 'Increase quantity', 'sunshine-photo-cart' ); ?></span></button>
                                <input type="text" name="qty" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>" data-image-id="<?php echo esc_attr( $image->get_id() ); ?>" data-gallery-id="<?php echo esc_attr( $image->get_gallery()->get_id() ); ?>" class="sunshine--qty" min="0" pattern="[0-9]+" value="<?php esc_attr_e( sunshine_get_cart_item_qty( $image->get_id(), $product->get_id() ) ); ?>" />
                                <button class="sunshine--qty--up"><span><?php esc_html_e( 'Decrease quantity', 'sunshine-photo-cart' ); ?></span></button>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            <?php }?>
        </div>
    </div>
</div>
