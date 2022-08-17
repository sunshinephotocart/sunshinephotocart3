<?php

add_action( 'sunshine_payment_methods_display', 'sunshine_payment_methods_display' );
function sunshine_payment_methods_display() {

    $id = ( isset( $_GET['id'] ) ) ? sanitize_key( $_GET['id'] ) : '';

    if ( $id ) {
        return false;
    }
    $payment_methods = SPC()->payment_methods->get_payment_methods();
?>
    <table id="sunshine-payment-methods" class="sunshine-table">
        <tbody>
        <?php
        if ( !empty( $payment_methods ) ) :
            foreach ( $payment_methods as $id => $payment_method ) {
                $payment_method_class = SPC()->payment_methods->get_payment_method_by_id( $id );
                if ( !$payment_method_class->can_be_enabled() ) {
                    continue;
                }
                ?>
                <tr id="sunshine-payment-method-<?php echo esc_attr( $id ); ?>" data-id="<?php echo esc_attr( $id ); ?>">
                    <!--<td><span class="dashicons dashicons-sort"></span></td>-->
                    <td>
                        <label class="sunshine-switch">
                          <input type="checkbox" name="sunshine_payment_method_active[<?php echo esc_attr( $id ); ?>]" <?php checked( $payment_method_class->is_active(), true ); ?> />
                          <span class="sunshine-switch-slider"></span>
                        </label>
                    </td>
                    <td>
                        <strong><?php echo esc_html( $payment_method_class->get_name() ); ?></strong><br />
                        <?php echo esc_html( $payment_method_class->get_description() ); ?>
                    </td>
                    <td class="sunshine-actions">
                        <a href="<?php echo admin_url( 'admin.php?page=sunshine&section=payment_methods&payment_method=' . esc_attr( $id ) ); ?>" class="button" <?php echo ( !$payment_method_class->is_active() ? 'style="display: none;"' : '' ); ?>><?php _e( 'Configure', 'sunshine-photo-cart' ); ?></a>
                    </td>
                </tr>
                <?php
            }
        endif;
        ?>
        </tbody>
    </table>

    <script>
    jQuery( document ).ready(function($){

        //$( '.wps-settings-payment_methods th' ).remove();

        /*
        $( '#sunshine-payment-methods tbody' ).sortable({
            stop: function( event, ui ) {
                var sorted_ids = new Array();
                $( '#sunshine-payment-methods tbody tr' ).each(function(){
                    var data_id = $( this ).data( 'id' );
                    if ( data_id ) {
                        sorted_ids.push( data_id );
                    }
                });
                $.ajax({
                    type: 'POST',
                    url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                    data: {
                        action: 'sunshine_sort_payment_methods',
                        sorted_ids: sorted_ids,
                        security: "<?php echo wp_create_nonce( 'sunshine-sort-payment-methods' ); ?>"
                    }
                });
            }
        });
        */

        $( document ).on( 'change', '.sunshine-switch', function( e ){
            var toggled_id = $( this ).closest( 'tr' ).data( 'id' );
            $.ajax({
                type: 'POST',
                url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                data: {
                    action: 'sunshine_active_payment_methods',
                    id: toggled_id,
                    security: "<?php echo wp_create_nonce( 'sunshine-active-payment-methods' ); ?>"
                },
                success: function( data, textStatus, XMLHttpRequest ) {
                    $( '#sunshine-payment-method-' + toggled_id + ' a' ).toggle();
                },
                error: function(MLHttpRequest, textStatus, errorThrown) {
                    alert( '<?php echo esc_js( __( 'Sorry, there was an error with your request', 'sunshine-photo-cart' ) ); ?>' );
                }
            });
        });


    });
    </script>
<?php
}

add_action( 'wp_ajax_sunshine_sort_payment_methods', 'sunshine_sort_payment_methods' );
function sunshine_sort_payment_methods() {

    if ( !wp_verify_nonce( $_REQUEST['security'], 'sunshine-sort-payment-methods' ) ) {
        return false;
    }

    $payment_methods = SPC()->payment_methods->get_payment_methods();
    $new_payment_methods_order = array();
    foreach ( $_REQUEST['sorted_ids'] as $payment_method_key ) {
        if ( array_key_exists( $payment_method_key, $payment_methods ) ) {
            $new_payment_methods_order[ $payment_method_key ] = $payment_methods[ $payment_method_key ];
        }
    }
    if ( !empty( $new_payment_methods_order ) ) {
        SPC()->update_option( 'payment_methods', $new_payment_methods_order );
    }
    exit;

}

add_action( 'wp_ajax_sunshine_delete_payment_method', 'sunshine_delete_payment_method' );
function sunshine_delete_payment_method() {
    global $wpdb;

    if ( !wp_verify_nonce( $_REQUEST['security'], 'sunshine-delete-payment-method' ) ) {
        return false;
    }

    $id = sanitize_text_field( $_REQUEST['instance_id'] );
    $payment_methods = sunshine_get_available_payment_methods();
    if ( array_key_exists( $id, $payment_methods ) ) {
        // Remove this from the available methods array
        unset( $payment_methods[ $id ] );
    }

    // Update the payment methods settings
    SPC()->update_option( 'payment_methods', $payment_methods );

    // Delete all settings related to this payment method
    $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%{$id}'" );

    echo true;
    exit;

}

add_action( 'wp_ajax_sunshine_active_payment_methods', 'sunshine_active_payment_methods' );
function sunshine_active_payment_methods() {

    if ( !wp_verify_nonce( $_REQUEST['security'], 'sunshine-active-payment-methods' ) ) {
        return false;
    }

    $id = sanitize_text_field( $_REQUEST['id'] );
    $current_status = SPC()->get_option( $id . '_active' );
    SPC()->update_option( $id . '_active', !$current_status );
    exit;

}
