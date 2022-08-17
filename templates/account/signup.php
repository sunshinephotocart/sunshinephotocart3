<form method="post" action="" id="sunshine--account--signup-form">
    <?php wp_nonce_field( 'sunshine_signup_nonce', 'sunshine_signup_nonce' ); ?>
    <div class="sunshine--account--login--title"><?php _e( 'Sign Up', 'sunshine-photo-cart' ); ?></div>
    <div class="sunshine--account--field">
        <label for="sunshine-signup-email"><?php _e( 'E-mail address', 'sunshine-photo-cart' ); ?></label>
        <input type="email" name="sunshine_signup_email" id="sunshine-signup-email" required="required" />
    </div>
    <div class="sunshine--account--field">
        <label for="sunshine-signup-password"><?php _e( 'Password', 'sunshine-photo-cart' ); ?></label>
        <input type="password" name="sunshine_signup_password" id="sunshine-signup-password" />
        <div class="sunshine--account--field--desc"><?php _e( 'Optionally set a password', 'sunshine-photo-cart' ); ?></div>
    </div>
    <div class="sunshine--account--submit">
        <button type="submit" class="button sunshine--button"><?php _e( 'Create Account', 'sunshine-photo-cart' ); ?></button>
    </div>
</form>
