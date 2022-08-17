<div id="sunshine--image-list" class="sunshine--favorites sunshine--col-<?php echo esc_attr( SPC()->get_option( 'columns' ) ); ?>">
    <?php
    foreach ( $images as $image ) {
        sunshine_get_template( 'gallery/single-image', array( 'image' => $image ) );
    }
    ?>
</div>
