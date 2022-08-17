<form method="post" action="" id="sunshine--account--login-form">
    <?php wp_nonce_field( 'sunshine_login_nonce', 'sunshine_login_nonce' ); ?>
    <div class="sunshine--account--login--title"><?php _e( 'Login', 'sunshine-photo-cart' ); ?></div>
    <div class="sunshine--account--field">
        <label for="sunshine-login-email"><?php _e( 'E-mail address', 'sunshine-photo-cart' ); ?></label>
        <input type="email" name="sunshine_login_email" id="sunshine-login-email" required="required" />
    </div>
    <div class="sunshine--account--field">
        <label for="sunshine-login-password"><?php _e( 'Password', 'sunshine-photo-cart' ); ?></label>
        <input type="password" name="sunshine_login_password" id="sunshine-login-password" required="required" />
    </div>
    <div class="sunshine--account--submit">
        <button type="submit" class="button sunshine--button"><?php _e( 'Login', 'sunshine-photo-cart' ); ?></button>
        <div class="sunshine--account--reset-password-toggle"><a href="#password" onclick="jQuery( '#sunshine--account--login-form, #sunshine--account--reset-password-form' ).toggle();"><?php _e( 'Reset password', 'sunshine-photo-cart' ); ?></a></div>
    </div>
</form>

<form method="post" action="" id="sunshine--account--reset-password-form" style="display: none;">
    <?php wp_nonce_field( 'sunshine_reset_password_nonce', 'sunshine_reset_password_nonce' ); ?>
    <div class="sunshine--account--login--title"><?php _e( 'Reset Password', 'sunshine-photo-cart' ); ?></div>
    <div class="sunshine--account--field">
        <label for="sunshine-reset-password-email"><?php _e( 'E-mail address', 'sunshine-photo-cart' ); ?></label>
        <input type="email" name="sunshine_reset_password_email" id="sunshine-reset-password-email" required="required" />
        <div class="sunshine--account--field--desc"><?php _e( 'An email will be sent to this address with instructions on how to reset your password', 'sunshine-photo-cart' ); ?></div>
    </div>
    <div class="sunshine--account--submit">
        <button type="submit" class="button sunshine--button"><?php _e( 'Get New Password', 'sunshine-photo-cart' ); ?></button>
        <div class="sunshine--account--reset-password-toggle"><a href="#password" onclick="jQuery( '#sunshine--account--login-form, #sunshine--account--reset-password-form' ).toggle();"><?php _e( 'Login', 'sunshine-photo-cart' ); ?></a></div>
    </div>
</form>
