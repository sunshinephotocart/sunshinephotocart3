<div id="sunshine--account--login-signup">

    <?php if ( !empty( $message ) ) { ?>
        <div id="sunshine--account--login-signup--header">
            <?php echo esc_html( $message ); ?>
        </div>
    <?php } ?>

    <div id="sunshine--account--login">
        <?php echo sunshine_get_template_html( 'account/login' ); ?>
    </div>

    <div id="sunshine--account--signup">
        <?php echo sunshine_get_template_html( 'account/signup' ); ?>
    </div>

</div>
