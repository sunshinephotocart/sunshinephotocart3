<table id="photo-list">
    <tr>
        <?php
        $i = 0;
        foreach ( $favorites as $image_id ) {
            $i++;
            $image = new SPC_Image( $image_id );
        ?>
            <td>
                <a href="<?php echo $image->get_permalink(); ?>"><img src="<?php echo $image->get_image_url(); ?>" alt="<?php echo esc_attr( $image->get_name() ); ?>" /></a>
                <span class="image-name"><?php echo $image->get_file_name(); ?></span>
            </td>
            <?php if ( $i == 6 ) { break; } ?>
            <?php if ( $i % 3 == 0 ) { ?>
                </tr><tr>
            <?php } ?>
        <?php } ?>
    </tr>
</table>

<p align="center">
    <a href="<?php echo admin_url( 'user-edit.php?user_id=' . SPC()->customer->ID . '#sunshine--favorites' ); ?>" class="button">
        <?php echo sprintf( __( 'View all %s favorites', 'sunshine-photo-cart' ), count( $favorites ) ); ?>
    </a>
</p>
