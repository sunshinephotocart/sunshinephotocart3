<form method="post" action="" id="sunshine-cart">

<input type="hidden" name="sunshine_update_cart" value="1" />
<input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'sunshine_update_cart' ); ?>" />

<?php //do_action( 'sunshine_before_cart_items' ); ?>

<?php sunshine_get_template( 'cart/cart-items', array( 'cart_items' => SPC()->cart->get_cart_items() ) ); ?>

<?php //do_action( 'sunshine_after_cart_items' ); ?>

<div id="sunshine--cart--update-button">
    <input type="submit" value="<?php esc_attr_e( 'Update Cart', 'sunshine-photo-cart'); ?>" class="button sunshine-button-alt" />
</div>

</form>

<?php do_action( 'sunshine_after_cart_form' ); ?>

<div id="sunshine--cart--totals">
    <?php sunshine_get_template( 'cart/totals' ); ?>
    <p id="sunshine--cart--checkout-button"><a href="<?php echo sunshine_url( 'checkout' ); ?>" class="button sunshine-button"><?php esc_html_e( 'Continue to checkout', 'sunshine-photo-cart' ); ?> &rarr;</a></p>
</div>
