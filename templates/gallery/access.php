<form method="post" action="<?php echo $gallery->get_permalink(); ?>" id="sunshine--gallery--access">
    <?php wp_nonce_field( 'sunshine_gallery_access', 'sunshine_gallery_access' ); ?>
    <input type="hidden" name="sunshine_gallery_id" value="<?php echo esc_attr( $gallery->get_id() ); ?>" />
    <?php if ( $password ) { ?>
        <div class="sunshine--gallery--access--field">
            <label for="sunshine--gallery--password"><?php _e( 'Password', 'sunshine-photo-cart' ); ?></label>
            <input type="password" name="sunshine_gallery_password" id="sunshine--gallery--password" required="required" />
            <?php if ( $gallery->get_password_hint() ) { ?>
                <div class="sunshine--gallery--access--password-hint"><?php _e( 'Password hint:', 'sunshine-photo-cart' ); ?> <?php echo $gallery->get_password_hint(); ?></div>
            <?php } ?>
        </div>
    <?php } ?>

    <?php if ( $email ) { ?>
        <div class="sunshine--gallery--access--field">
            <label for="sunshine--gallery--email"><?php _e( 'Email', 'sunshine-photo-cart' ); ?></label>
            <input type="email" name="sunshine_gallery_email" id="sunshine--gallery--email" required="required" />
        </div>
    <?php } ?>

    <div class="sunshine--gallery-access--submit">
        <button type="submit" class="sunshine--button"><?php _e( 'Submit', 'sunshine-photo-cart' ); ?></button>
    </div>

</form>
