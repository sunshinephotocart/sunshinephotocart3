<?php
function sunshine_show_checkout_fields( $active_section = '' ) {
    SPC()->cart->show_checkout_fields( $active_section );
}

add_action( 'sunshine_checkout', 'sunshine_checkout_scripts', 999 );
function sunshine_checkout_scripts() {
    ?>
    <script id="sunshine-checkout-js">

    /*
    var sunshine_subtotal = <?php echo SPC()->cart->get_subtotal(); ?>;
    var sunshine_shipping = <?php echo SPC()->cart->get_shipping(); ?>;
    var sunshine_tax = <?php echo SPC()->cart->get_tax(); ?>;
    var sunshine_discount = <?php echo SPC()->cart->get_discount(); ?>;
    var sunshine_credits = <?php echo SPC()->cart->get_credits_applied(); ?>;
    var sunshine_total = <?php echo SPC()->cart->get_total(); ?>;
    */

    var sunshine_active_section = "<?php echo esc_js( SPC()->cart->get_active_section() ); ?>";

    /*
    function sunshine_checkout_updating() {
        jQuery( '#sunshine--checkout--review' ).append( '<div class="sunshine--loading"></div>' ).addClass( 'updating' );
        jQuery( '#sunshine--checkout--' + sunshine_active_section ).append( '<div class="sunshine--loading"></div>' ).addClass( 'updating' );
    }
    function sunshine_checkout_updating_done() {
        jQuery( '#sunshine--checkout--review .sunshine--loading, #sunshine--checkout--' + sunshine_active_section + ' .sunshine--loading' ).remove();
        jQuery( '#sunshine--checkout--review, #sunshine--checkout--' + sunshine_active_section ).removeClass( 'updating' );
    }
    */

    function sunshine_checkout_updating() {
        jQuery( '#sunshine--checkout' ).addClass( 'sunshine--loading' );
    }
    function sunshine_checkout_updating_done() {
        jQuery( '#sunshine--checkout' ).removeClass( 'sunshine--loading' );
    }

    jQuery( document ).ready(function($){

        // Fancy label interactions
        function sunshine_mark_filled() {
            $( '#sunshine--checkout input, #sunshine--checkout select' ).each(function() {
                $( this ).on( 'focus', function() {
                    $( this ).closest( '.sunshine--checkout--field' ).addClass( 'filled' );
                });
                $( this ).on( 'blur', function() {
                    if ( $( this ).val().length == 0 ) {
                        $( this ).closest( '.sunshine--checkout--field' ).removeClass( 'filled' );
                    }
                });
                if ( $( this ).val() != '' ) {
                    $( this ).closest( '.sunshine--checkout--field' ).addClass( 'filled' );
                }
            });
        }
        sunshine_mark_filled();

        function sunshine_reload_checkout( section = '' ) {

            sunshine_checkout_updating();

            $.ajax({
                type: 'GET',
                url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                data: {
                    action: 'sunshine_checkout_update',
                    section: section,
                    security: "<?php echo wp_create_nonce( 'sunshine-checkout-update' ); ?>"
                },
                success: function( result, textStatus, XMLHttpRequest ) {
                    if ( result.success ) {
                        $( '#sunshine--checkout' ).replaceWith( result.data.html );
                    }
                    sunshine_mark_filled();
                    $( '#sunshine--checkout input, #sunshine--checkout select' ).trigger( 'conditional' );
                    sunshine_checkout_updating_done();
                },
                error: function(MLHttpRequest, textStatus, errorThrown) {
                    alert( '<?php echo esc_js( __( 'Sorry, there was an error with your request', 'sunshine-photo-cart' ) ); ?>' );
                    sunshine_checkout_updating_done();
                }
            });

            return false;

            /*
            if ( !data ) {
                return;
            }

            if ( "shipping" in data ) {
                sunshine_shipping = data.shipping;
            }

            if ( "shipping_formatted" in data ) {
                // Update shipping formatted
                $( '#sunshine--checkout--order-review--shipping td' ).html( data.shipping_formatted );
            }

            if ( "tax" in data ) {
                sunshine_tax = data.tax;
            }

            if ( "tax_formatted" in data ) {
                // Update tax formatted
                $( '#sunshine--checkout--order-review--tax td' ).html( data.tax_formatted );
            }

            if ( "total" in data ) {
                sunshine_total = data.total;
            }

            if ( "total_formatted" in data ) {
                // Update total formatted
                $( '#sunshine--checkout--order-review--total td' ).html( data.total_formatted );
                $( '.sunshine-total' ).html( data.total_formatted );
            }

            if ( "credits_formatted" in data ) {
                // Update total formatted
                $( '#sunshine--checkout--order-review--credits td' ).html( '-' + data.credits_formatted );
            }

            if ( "needs_shipping" in data ) {
                if ( data.needs_shipping ) {
                    $( '#sunshine--checkout--order-review--shipping' ).show();
                    $( '#sunshine--checkout--shipping' ).show();
                    $( '#sunshine--checkout--shipping_method' ).show();
                } else {
                    $( '#sunshine--checkout--order-review--shipping' ).hide();
                    $( '#sunshine--checkout--shipping' ).hide();
                    $( '#sunshine--checkout--shipping_method' ).hide();
                }
            }

            sunshine_set_field_disabled();
            */

        }

        /*
        // Process a section
        $( 'body' ).on( 'click', '.sunshine--checkout--section-button button', function(e) {
            e.preventDefault();

            if ( $( '#sunshine--checkout' )[0].checkValidity() ) {

                var form_data = new FormData( document.getElementById( 'sunshine--checkout' ) );
                console.log( form_data );

                // Process section data
                $.ajax({
                    type: 'POST',
                    url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                    data: {
                        action: 'sunshine_checkout_process_section',
                        section: $( this ).data( 'section' ),

                        security: "<?php echo wp_create_nonce( 'sunshine-checkout-process-section' ); ?>"
                    },
                    success: function( result, textStatus, XMLHttpRequest ) {
                        if ( result.success ) {
                            sunshine_reload_checkout();
                        }
                    },
                    error: function(MLHttpRequest, textStatus, errorThrown) {
                        alert( '<?php echo esc_js( __( 'Sorry, there was an error with your request', 'sunshine-photo-cart' ) ); ?>' );
                    }
                });

                // Reload the checkout
                sunshine_reload_checkout();

            } else {
                $( '#sunshine--checkout' )[0].reportValidity()
                console.log( 'Not valid' );
            }

            return false;
        });
        */

        $( 'body' ).on( 'click', '#sunshine--checkout .sunshine--checkout--section-button button', function(e){
            e.preventDefault();
            var form_data = new FormData( $( '#sunshine--checkout' )[0] );
            form_data.append( 'action', 'sunshine_checkout_process_section' );
            form_data.append( 'security', "<?php echo wp_create_nonce( 'sunshine-checkout-process-section' ); ?>" );
            $.ajax({
                async: true,
                type: 'POST',
                url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                data: form_data,
                cache: false,
                processData: false,
                contentType: false,
                success: function( result, textStatus, XMLHttpRequest ) {
                    if ( result.success ) {
                        sunshine_reload_checkout();
                    }
                },
                error: function(MLHttpRequest, textStatus, errorThrown) {
                    alert( '<?php echo esc_js( __( 'Sorry, there was an error with your request', 'sunshine-photo-cart' ) ); ?>' );
                }
            });
        });

        $( 'body' ).on( 'click', '.sunshine--checkout--section-edit', function(e) {
            e.preventDefault();

            // Change URL
            var section = $( this ).data( 'section' );
            /*
            var url = new URL( window.location.href );
            var url_params = url.searchParams;
            url_params.set( 'section', section );
            url.search = url_params.toString();
            window.history.pushState( section, '', url.toString() );
            */

            // Change content
            sunshine_reload_checkout( section );

            return false;
        });

        $( document ).on( 'change', 'input[name="delivery_method"]', function(){
            var sunshine_selected_delivery_method = $( 'input[name="delivery_method"]:checked' ).val();
            sunshine_checkout_updating();
            $.ajax({
                type: 'POST',
                url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                data: {
                    action: 'sunshine_checkout_select_delivery_method',
                    delivery_method: sunshine_selected_delivery_method,
                    security: "<?php echo wp_create_nonce( 'sunshine-checkout-select-delivery-method' ); ?>"
                },
                success: function( result, textStatus, XMLHttpRequest ) {
                    if ( result.data.summary ) {
                        $( '#sunshine--checkout--summary' ).html( result.data.summary );
                    }
                    if ( result.data.needs_shipping ) {
                        $( '#sunshine--checkout--shipping, #sunshine--checkout--shipping_method' ).show();
                    } else {
                        $( '#sunshine--checkout--shipping, #sunshine--checkout--shipping_method' ).hide();
                    }
                    sunshine_checkout_updating_done();
                },
                error: function(MLHttpRequest, textStatus, errorThrown) {
                    alert( '<?php echo esc_js( __( 'Sorry, there was an error with your request', 'sunshine-photo-cart' ) ); ?>' );
                    sunshine_checkout_updating_done();
                }
            });
        });

        $( document ).on( 'change', 'input[name="shipping_method"]', function(){
            var sunshine_selected_shipping_method = $( 'input[name="shipping_method"]:checked' ).val();
            sunshine_checkout_updating();
            $.ajax({
                type: 'POST',
                url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                data: {
                    action: 'sunshine_checkout_select_shipping_method',
                    shipping_method: sunshine_selected_shipping_method,
                    security: "<?php echo wp_create_nonce( 'sunshine-checkout-select-shipping-method' ); ?>"
                },
                success: function( result, textStatus, XMLHttpRequest ) {
                    if ( result.data.summary ) {
                        $( '#sunshine--checkout--summary' ).html( result.data.summary );
                    }
                    sunshine_checkout_updating_done();
                },
                error: function(MLHttpRequest, textStatus, errorThrown) {
                    alert( '<?php echo esc_js( __( 'Sorry, there was an error with your request', 'sunshine-photo-cart' ) ); ?>' );
                    sunshine_checkout_updating_done();
                }
            });
        });

        $( document ).on( 'change', 'input[name="use_credits"]', function(){
            var sunshine_use_credits = $( 'input[name="use_credits"]:checked' ).val();
            sunshine_checkout_updating();
            $.ajax({
                type: 'POST',
                url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                data: {
                    action: 'sunshine_checkout_use_credits',
                    use_credits: sunshine_use_credits,
                    security: "<?php echo wp_create_nonce( 'sunshine-checkout-use-credits' ); ?>"
                },
                success: function( result, textStatus, XMLHttpRequest ) {
                    if ( result.success ) {
                        if ( sunshine_use_credits ) {
                            $( '#sunshine--checkout--order-review--credits' ).show();
                            $( '#sunshine--checkout--order-review--credits td' ).html( '-' + result.data.credits_formatted );
                        } else {
                            $( '#sunshine--checkout--order-review--credits' ).hide();
                        }
                        if ( result.data.total == 0 ) {
                            $( '#sunshine--checkout--field--submit button' ).show();
                            $( '#sunshine--checkout--field--payment_method' ).hide();
                        } else {
                            $( '#sunshine--checkout--field--payment_method' ).show();
                        }
                        sunshine_reload_checkout( result.data );
                    }
                    sunshine_checkout_updating_done();
                },
                error: function(MLHttpRequest, textStatus, errorThrown) {
                    alert( '<?php echo esc_js( __( 'Sorry, there was an error with your request', 'sunshine-photo-cart' ) ); ?>' );
                    sunshine_checkout_updating_done();
                }
            });
        });

        $( document ).on( 'change', 'input[name="payment_method"]', function(){
            var sunshine_selected_payment_method = $( 'input[name="payment_method"]:checked' ).val();
            sunshine_checkout_updating();
            $.ajax({
                type: 'POST',
                url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                data: {
                    action: 'sunshine_checkout_select_payment_method',
                    payment_method: sunshine_selected_payment_method,
                    security: "<?php echo wp_create_nonce( 'sunshine-checkout-select-payment-method' ); ?>"
                },
                success: function( result, textStatus, XMLHttpRequest ) {
                    sunshine_checkout_updating_done();
                },
                error: function(MLHttpRequest, textStatus, errorThrown) {
                    alert( '<?php echo esc_js( __( 'Sorry, there was an error with your request', 'sunshine-photo-cart' ) ); ?>' );
                    sunshine_checkout_updating_done();
                }
            });
        });


        sunshine_state_change_security = '<?php echo wp_create_nonce( 'sunshine-checkout-update-state' ); ?>';

        $( document ).on( 'change', 'select[name="shipping_country"]', function(){
            sunshine_checkout_updating();
            var sunshine_selected_shipping_country = $( this ).val();
            var sunshine_selected_shipping_country_required;
            if ( $( this ).prop( 'required' ) ) {
                sunshine_selected_shipping_country_required = true;
            }
            setTimeout( function () {
                $.ajax({
                    type: 'POST',
                    url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                    data: {
                        action: 'sunshine_checkout_update_state',
                        country: sunshine_selected_shipping_country,
                        type: 'shipping',
                        required: sunshine_selected_shipping_country_required,
                        security: sunshine_state_change_security
                    },
                    success: function(output, textStatus, XMLHttpRequest) {
                        if ( output ) {
                            $( '#sunshine--checkout--shipping .sunshine--checkout--fields' ).html( '' );
                            $( '#sunshine--checkout--shipping .sunshine--checkout--fields' ).html( output );
                            sunshine_mark_filled();
                        }
                        sunshine_checkout_updating_done();
                    },
                    error: function(MLHttpRequest, textStatus, errorThrown) {
                        alert('Sorry, there was an error with your request');
                    }
                });
            }, 500);
            return false;
        });

        $( document ).on( 'change', 'select[name="billing_country"]', function(){
            sunshine_checkout_updating();
            var sunshine_selected_billing_country = $( this ).val();
            var sunshine_selected_billing_country_required;
            if ( $( this ).prop( 'required' ) ) {
                sunshine_selected_billing_country_required = true;
            }
            setTimeout( function () {
                $.ajax({
                    type: 'POST',
                    url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                    data: {
                        action: 'sunshine_checkout_update_state',
                        country: sunshine_selected_billing_country,
                        type: 'billing',
                        required: sunshine_selected_billing_country_required,
                        security: sunshine_state_change_security
                    },
                    success: function(output, textStatus, XMLHttpRequest) {
                        if ( output ) {
                            $( '#sunshine--checkout--payment div[id*="billing_"]' ).remove();
                            //$( '#sunshine-checkout-payment .sunshine--checkout--fields' ).append( output );
                            $( output ).insertAfter( '#sunshine--checkout--field--different_billing' );
                            sunshine_mark_filled();
                        }
                        sunshine_checkout_updating_done();
                    },
                    error: function(MLHttpRequest, textStatus, errorThrown) {
                        alert('Sorry, there was an error with your request');
                    }
                });
            }, 500);
            return false;
        });

        // Field conditions
        function sunshine_get_condition_field_value( field_id ) {
            var field = $( '#sunshine--checkout--field--' + field_id );
            var field_type = field.data( 'type' );
            var value;
            if ( field_type == 'text' || field_type == 'email' || field_type == 'tel' || field_type == 'password' ) { // Text input box
                value = $( 'input', field ).val();
            } else if ( field_type == 'checkbox' ) {
                value = $( 'input:checked', field ).val();
                if ( typeof value === 'undefined' ) {
                    value = 'no';
                }
            } else if ( field_type == 'radio' ) {
                value = $( 'input:checked', field ).val();
                if ( typeof value === 'undefined' ) {
                    value = 0;
                }
            } else if ( field_type == 'select' ) {
                value = $( 'select option:selected', field ).val();
            }
            return value;
        }

        function sunshine_set_field_disabled() {
            $( '.sunshine--checkout--field' ).each(function(){
                if ( $( this ).is( ':visible' ) ) {
                    $( 'input, select, textarea', this ).prop( 'disabled', false );
                } else {
                    $( 'input, select, textarea', this ).prop( 'disabled', true );
                }
            });
        }

        function sunshine_checkout_conditionals() {

        }

        <?php
        $sections = SPC()->cart->get_checkout_fields();
        $i = 0;
        foreach ( $sections as $section_id => $section ) {
            if ( empty( $section['fields'] ) ) {
                continue;
            }
            foreach ( $section['fields'] as $field ) {
                if ( !empty( $field['conditions'] ) && is_array( $field['conditions'] ) ) {
                    foreach ( $field['conditions'] as $condition ) {
                        if ( empty( $condition['compare'] ) || empty( $condition['value'] ) || empty( $condition['field'] ) || empty( $condition['action'] ) ) {
                            continue;
                        }
                        if ( !in_array( $condition['action'], array( 'show', 'hide' ) ) ) {
                            continue;
                        }
                        if ( !in_array( $condition['compare'], array( '==', '!=', '<', '>', '<=', '>=' ) ) ) {
                            continue;
                        }
                        $i++;
                        ?>
                            var condition_field_value_<?php echo $i; ?> = sunshine_get_condition_field_value( '<?php echo esc_js( $condition['field'] ); ?>' );
                            function condition_field_action_<?php echo $i; ?>( value ) {
                                <?php
                                $action_target = ( isset( $condition['action_target'] ) ) ? $condition['action_target'] : '#sunshine--checkout--field--' . $field['id'];
                                $true_action = ( $condition['action'] == 'show' ) ? 'show' : 'hide';
                                $false_action = ( $condition['action'] == 'show' ) ? 'hide' : 'show';
                                $comparison_string = '';
                                if ( is_array( $condition['value'] ) ) { // If value is an array, need to compare against each array value
                                    $comparison_strings = array();
                                    foreach ( $condition['value'] as $value ) {
                                        $comparison_strings[] = '( value ' . esc_js( $condition['compare'] ) . ' "' . esc_js( $value ) . '" )';
                                    }
                                    $comparison_string = join( ' || ', $comparison_strings );
                                } else {
                                    $comparison_string = 'value ' . esc_js( $condition['compare'] ) . ' "' . esc_js( $condition['value'] ) . '"';
                                }
                                ?>
                                if ( <?php echo $comparison_string; ?> ) {
                                    $( '<?php echo esc_js( $action_target ); ?>' ).<?php echo $true_action; ?>();
                                } else {
                                    $( '<?php echo esc_js( $action_target ); ?>' ).<?php echo $false_action; ?>();
                                }
                                sunshine_set_field_disabled();
                            }

                            // Default action
                            condition_field_action_<?php echo $i; ?>( condition_field_value_<?php echo $i; ?> );

                            // On change action
                            $( 'body' ).on( 'change conditional', '#<?php echo esc_js( $condition['field'] ); ?>, #sunshine--checkout--field--<?php echo esc_js( $condition['field'] ); ?> input[type="radio"]', function(){
                                condition_field_value_<?php echo $i; ?> = sunshine_get_condition_field_value( '<?php echo esc_js( $condition['field'] ); ?>' );
                                condition_field_action_<?php echo $i; ?>( condition_field_value_<?php echo $i; ?> );
                            });
                        <?php
                    }
                }
            }
        }
        ?>
    });
    </script>

    <?php
    if ( SPC()->cart->get_active_section() == 'shipping' || SPC()->cart->get_active_section() == 'billing' ) {
        $prefix = ( SPC()->cart->get_active_section() == 'shipping' ) ? 'shipping' : 'billing';
        $google_maps_api_key = SPC()->get_option( 'google_maps_api_key' );
        if ( $google_maps_api_key ) {
            $allowed_countries = array();
            foreach ( SPC()->countries->get_allowed_countries() as $code => $name ) {
                $allowed_countries[] = '"' . strtolower( esc_js( $code ) ) . '"';
            }
        ?>
        <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo esc_attr( $google_maps_api_key ); ?>&callback=sunshine_<?php echo esc_attr( $prefix ); ?>_address_init_autocomplete&libraries=places&v=weekly" async></script>
        <script id="sunshine--checkout--autocomplete">
            function sunshine_<?php echo esc_attr( $prefix ); ?>_address_init_autocomplete() {
              address1Field = document.querySelector("#<?php echo esc_attr( $prefix ); ?>_address1");
              address2Field = document.querySelector("#<?php echo esc_attr( $prefix ); ?>_address2");
              postalField = document.querySelector("#<?php echo esc_attr( $prefix ); ?>_postcode");
              sunshine_autocomplete = new google.maps.places.Autocomplete(address1Field, {
                componentRestrictions: { country: [<?php echo join( ', ', $allowed_countries ); ?>] },
                fields: ["address_components", "geometry"],
                types: ["address"],
              });
              //address1Field.focus();
              sunshine_autocomplete.addListener("place_changed", sunshine_autopopulate_address);
            }

            function sunshine_autopopulate_address() {
              const place = sunshine_autocomplete.getPlace();
              let address1 = "";
              let postcode = "";

              for (const component of place.address_components) {
                const componentType = component.types[0];

                switch (componentType) {
                  case "street_number": {
                    address1 = `${component.long_name} ${address1}`;
                    break;
                  }

                  case "route": {
                    address1 += component.short_name;
                    break;
                  }

                  case "postal_code": {
                    postcode = `${component.long_name}${postcode}`;
                    break;
                  }

                  case "postal_code_suffix": {
                    postcode = `${postcode}-${component.long_name}`;
                    break;
                  }
                  case "locality":
                    jQuery( '#<?php echo esc_attr( $prefix ); ?>_city' ).val( component.short_name );
                    jQuery( '#<?php echo esc_attr( $prefix ); ?>_city' ).closest( '.sunshine--checkout--field' ).addClass( 'filled' );
                    break;
                  case "administrative_area_level_1": {
                      jQuery( '#<?php echo esc_attr( $prefix ); ?>_state' ).val( component.short_name );
                      jQuery( '#<?php echo esc_attr( $prefix ); ?>_state' ).closest( '.sunshine--checkout--field' ).addClass( 'filled' );
                    break;
                  }
                  case "country":
                    jQuery( '#<?php echo esc_attr( $prefix ); ?>_country' ).val( component.short_name );
                    jQuery( '#<?php echo esc_attr( $prefix ); ?>_country' ).closest( '.sunshine--checkout--field' ).addClass( 'filled' );
                    break;
                }
              }

              jQuery( '#<?php echo esc_attr( $prefix ); ?>_address1' ).val( address1 );
              jQuery( '#<?php echo esc_attr( $prefix ); ?>_address1' ).closest( '.sunshine--checkout--field' ).addClass( 'filled' );

              jQuery( '#<?php echo esc_attr( $prefix ); ?>_postcode' ).val( postcode );
              jQuery( '#<?php echo esc_attr( $prefix ); ?>_postcode' ).closest( '.sunshine--checkout--field' ).addClass( 'filled' );

              address2Field.focus();
            }
        </script>
        <?php
        }
    }
}

add_action( 'wp_ajax_sunshine_checkout_update', 'sunshine_checkout_update_summary' );
add_action( 'wp_ajax_nopriv_sunshine_checkout_update', 'sunshine_checkout_update_summary' );
function sunshine_checkout_update_summary() {

    if ( !wp_verify_nonce( $_REQUEST['security'], 'sunshine-checkout-update' ) ) {
        return false;
        exit;
    }

    SPC()->cart->setup();

    $html = sunshine_get_template_html( 'checkout' );
    wp_send_json_success( array( 'html' => $html ) );

}

add_action( 'wp_ajax_sunshine_checkout_process_section', 'sunshine_checkout_process_section' );
add_action( 'wp_ajax_nopriv_sunshine_checkout_process_section', 'sunshine_checkout_process_section' );
function sunshine_checkout_process_section() {

    if ( !wp_verify_nonce( $_REQUEST['security'], 'sunshine-checkout-process-section' ) || empty( $_POST['sunshine_checkout_section'] ) ) {
        return false;
        exit;
    }

    // Do validation
    SPC()->cart->setup();
    $result = SPC()->cart->process_section( $_POST['sunshine_checkout_section'], $_POST );

    if ( $result ) {
        // TODO: Passed validation!

    } else {
        // Need to show errors
        sunshine_log( SPC()->cart->get_errors() );
    }

    wp_send_json_success();
}


add_action( 'wp_ajax_sunshine_checkout_select_delivery_method', 'sunshine_checkout_select_delivery_method' );
add_action( 'wp_ajax_nopriv_sunshine_checkout_select_delivery_method', 'sunshine_checkout_select_delivery_method' );
function sunshine_checkout_select_delivery_method() {

    if ( !wp_verify_nonce( $_REQUEST['security'], 'sunshine-checkout-select-delivery-method' ) ) {
        return false;
        exit;
    }

    SPC()->cart->setup();

    $selected_delivery_method = sanitize_text_field( $_REQUEST['delivery_method'] );
    $delivery_methods = sunshine_get_delivery_methods();
    if ( array_key_exists( $selected_delivery_method, $delivery_methods ) ) {

        $this_delivery_method = sunshine_get_delivery_method_by_id( $selected_delivery_method );
        SPC()->cart->set_delivery_method( $selected_delivery_method );
        SPC()->cart->update();

        $result = array(
            'needs_shipping' => $this_delivery_method->needs_shipping(),
            'summary' => sunshine_get_template_html( 'checkout/summary' ),
        );
        wp_send_json_success( $result );

    }

    wp_send_json_error();

}

add_action( 'wp_ajax_sunshine_checkout_select_shipping_method', 'sunshine_checkout_select_shipping_method' );
add_action( 'wp_ajax_nopriv_sunshine_checkout_select_shipping_method', 'sunshine_checkout_select_shipping_method' );
function sunshine_checkout_select_shipping_method() {

    if ( !wp_verify_nonce( $_REQUEST['security'], 'sunshine-checkout-select-shipping-method' ) ) {
        return false;
        exit;
    }

    SPC()->cart->setup();

    $selected_shipping_method_instance = sanitize_text_field( $_REQUEST['shipping_method'] );
    $active_shipping_methods = sunshine_get_active_shipping_methods();
    if ( array_key_exists( $selected_shipping_method_instance, $active_shipping_methods ) ) {

        $this_shipping_method = sunshine_get_shipping_method_by_instance( $selected_shipping_method_instance );
        SPC()->cart->set_shipping_method( $selected_shipping_method_instance );

        $result = array(
            'summary' => sunshine_get_template_html( 'checkout/summary' )
        );
        wp_send_json_success( $result );

    }

    wp_send_json_error();

}

add_action( 'wp_ajax_sunshine_checkout_use_credits', 'sunshine_checkout_use_credits' );
add_action( 'wp_ajax_nopriv_sunshine_checkout_use_credits', 'sunshine_checkout_use_credits' );
function sunshine_checkout_use_credits() {

    if ( !wp_verify_nonce( $_REQUEST['security'], 'sunshine-checkout-use-credits' ) ) {
        return false;
        exit;
    }

    SPC()->cart->setup();

    if ( empty( $_REQUEST['use_credits'] ) ) {
        SPC()->cart->set_use_credits( false );
    } else {
        SPC()->cart->set_use_credits( true );
    }

    $result = array(
        'total' => SPC()->cart->get_total(),
        'total_formatted' => SPC()->cart->get_total_formatted(),
        'credits' => SPC()->cart->get_credits_applied(),
        'credits_formatted' => SPC()->cart->get_credits_applied_formatted()
    );
    wp_send_json_success( $result );
    exit;

}

add_action( 'wp_ajax_sunshine_checkout_select_payment_method', 'sunshine_checkout_select_payment_method' );
add_action( 'wp_ajax_nopriv_sunshine_checkout_select_payment_method', 'sunshine_checkout_select_payment_method' );
function sunshine_checkout_select_payment_method() {

    if ( !wp_verify_nonce( $_REQUEST['security'], 'sunshine-checkout-select-payment-method' ) ) {
        return false;
        exit;
    }

    SPC()->cart->setup();

    $selected_payment_method = sanitize_text_field( $_REQUEST['payment_method'] );
    $active_payment_methods = sunshine_get_active_payment_methods();
    if ( array_key_exists( $selected_payment_method, $active_payment_methods ) ) {
        SPC()->cart->set_payment_method( $selected_payment_method );
        SPC()->cart->update();
        wp_send_json_success();
    }

    wp_send_json_error();

}


add_action( 'wp_ajax_sunshine_checkout_update_state', 'sunshine_checkout_update_state' );
add_action( 'wp_ajax_nopriv_sunshine_checkout_update_state', 'sunshine_checkout_update_state' );
function sunshine_checkout_update_state() {

    if ( !wp_verify_nonce( $_REQUEST['security'], 'sunshine-checkout-update-state' ) ) {
        return false;
        exit;
    }

    SPC()->cart->setup();

	if ( isset( $_POST['country'] ) ) {

        $type = sanitize_key( $_POST['type'] );
        if ( $type == 'shipping' ) {
            $prefix = 'shipping_';
        } else {
            $prefix = 'billing_';
        }
        $country = sanitize_text_field( $_POST['country'] );
        $states = SPC()->countries->get_states( $country );

        SPC()->cart->set_checkout_data_item( $prefix . '_country', $country );

        $output = '';
        $address_fields = SPC()->countries->get_address_fields( $country, $prefix );
        foreach ( $address_fields as $address_field ) {
            $output .= SPC()->cart->get_checkout_field_html( $address_field['id'], $address_field );
        }
	}
	echo $output;
    exit;
}


function sunshine_get_sections_completed() {
    $completed = SPC()->session->get( 'checkout_sections_completed' );
    if ( empty( $completed ) ) {
        return array();
    }
    return $completed;
}

function sunshine_checkout_section_completed( $section ) {
    $completed = SPC()->session->get( 'checkout_sections_completed' );
    if ( is_array( $completed ) && in_array( $section, $completed ) ) {
        return true;
    }
    return false;
}

function sunshine_checkout_is_section_active( $section ) {
    if ( $section == SPC()->cart->active_section ) {
        return true;
    }
    return false;
}


function sunshine_value_comparison( $var1, $var2, $comparison ) {
    switch ( $comparison ) {
        case '=':
        case '==':
            return $var1 == $var2;
        case "!=":
            return $var1 != $var2;
        case ">=":
            return $var1 >= $var2;
        case "<=":
            return $var1 <= $var2;
        case ">":
            return $var1 >  $var2;
        case "<":
            return $var1 <  $var2;
        default:
            return false;
    }
}
