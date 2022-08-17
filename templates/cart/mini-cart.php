<div id="sunshine--mini-cart">
<?php
if ( !SPC()->cart->is_empty() ) {
?>
    <a href="<?php echo sunshine_get_page_permalink( 'cart' ); ?>"><span class="sunshine--mini-cart--quantity"><?php echo sprintf( __( '%s items', 'sunshine-photo-cart' ), '<span class="sunshine--mini-cart--quantity--count">' . SPC()->cart->get_item_count() . '</span>' ); ?></span> <span class="sunshine--mini-cart--separator">&mdash;</span> <span class="sunshine--mini-cart--total"><?php echo SPC()->cart->get_subtotal_formatted(); ?></span></a>
<?php
} else {
    echo '<div id="sunshine--mini-cart--empty">' . __( 'Your cart is empty', 'sunshine-photo-cart' ) . '</div>';
}
?>
</div>
