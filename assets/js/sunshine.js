jQuery( document ).ready(function($){

    // Modal
    $( '.sunshine--open-modal' ).click(function(){

        // Get the action needed to populate the content area
        const data = $( this ).data();

        // If no action, return false
        if ( !data.action ) {
            return false;
        }

        // Add the main structure for the modal
        $( 'body' ).addClass( 'sunshine--modal--open' ).append( '<div id="sunshine--modal--overlay" class="sunshine--loading"></div>' );

        // Run action and place that content
        $.ajax({
            type: 'POST',
            url: sunshine_photo_cart.ajax_url,
            data: data,
            success: function( result, textStatus, XMLHttpRequest) {
                $( '#sunshine--modal--overlay' ).removeClass( 'sunshine--loading' );
                if ( result.data.html ) {
                    $( 'body' ).append( '<div id="sunshine--modal"><a id="sunshine--modal--close"></a><div id="sunshine--modal--content">' + result.data.html + '</div></div>' );
                    $( '#sunshine--modal' ).addClass( data.action );
                } else {
                    $( '#sunshine--modal--overlay' ).append( '<div id="sunshine--modal--error">ERROR</div>' );
                }
            },
            error: function( MLHttpRequest, textStatus, errorThrown ) {
                alert( 'Sorry, there was an error with your request: ' + errorThrown + MLHttpRequest + textStatus ); // TODO: Better error
            }
        });

        return false; // Disable link, whatever it is
    });

    $( 'body' ).on( 'click', '#sunshine--modal--close', function(){
        $( 'body' ).removeClass( 'sunshine--modal--open' );
        $( '#sunshine--modal--overlay, #sunshine--modal' ).remove();
    });

    /* Modal category toggle */
    $( 'body' ).on( 'click', '#sunshine--image--add-to-cart--categories a', function(){
        $( '#sunshine--image--add-to-cart--product-list tr' ).hide();
        $( '#sunshine--image--add-to-cart--categories a' ).removeClass();
        $( this ).addClass( 'active' );
        let category_id = $( this ).data( 'id' );
        $( '#sunshine--image--add-to-cart--product-list tr.sunshine--category-' + category_id ).show();
        return false;
    });

    // Add to cart in modal
    $( 'body' ).on( 'change keyup', '#sunshine--image--add-to-cart--product-list input[name="qty"]', function(){

        let image_id = $( this ).data( 'image-id' );
        let product_id = $( this ).data( 'product-id' );
        let gallery_id = $( this ).data( 'gallery-id' );
        let qty = parseInt( $( this ).val() );

        // Mini cart set to loading
        $( '#sunshine--image--cart-review' ).addClass( 'sunshine--loading' );

        // TODO: Queue or some way to prevent this being run quantity gets hit many times quickly

        // Then run ajax request
        $.ajax({
            type: 'POST',
            url: sunshine_photo_cart.ajax_url,
            data: {
                action: 'sunshine_modal_add_item_to_cart',
                image_id: image_id,
                product_id: product_id,
                gallery_id: gallery_id,
                qty: qty
            },
            success: function( result, textStatus, XMLHttpRequest) {
                if ( result.success ) {
                    $( '#sunshine--image--cart-review' ).html( result.data.mini_cart );
                    $( '.sunshine--cart--count' ).html( result.data.count );
                    if ( qty > 0 ) {
                        $( '#sunshine, #sunshine--image-' + image_id ).addClass( 'sunshine--image--in-cart' );
                    } else {
                        $( '#sunshine, #sunshine--image-' + image_id ).removeClass( 'sunshine--image--in-cart' );
                    }
                } else {
                    alert( 'Error' ); // TODO: Better error
                }
            },
            error: function( MLHttpRequest, textStatus, errorThrown ) {
                alert( 'Sorry, there was an error with your request: ' + errorThrown + MLHttpRequest + textStatus ); // TODO: Better error
            }
        }).always(function(){
            $( '#sunshine--image--cart-review' ).removeClass( 'sunshine--loading' );
        });

    });

    $( 'body' ).on( 'click', '#sunshine--image--add-to-cart--product-list button.sunshine--qty--up', function(){
        let qty_input = $( this ).siblings( 'input' );
        let qty = parseInt( qty_input.val() );
        qty += 1;
        qty_input.val( qty );
        qty_input.trigger( 'change' );
    });

    $( 'body' ).on( 'click', '#sunshine--image--add-to-cart--product-list button.sunshine--qty--down', function(){
        let qty_input = $( this ).siblings( 'input' );
        let qty = parseInt( qty_input.val() );
        qty -= 1;
        if ( qty < 0 ) {
            return;
        }
        qty_input.val( qty );
        qty_input.trigger( 'change' );
    });

    // Add comment to image
    $( 'body' ).on( 'submit', '#sunshine--image--comments--add-form', function(e){

        e.preventDefault();

        // Remove error just in case it exists
        $( '.sunshine--image--comments--error' ).remove();

        let security = $( 'input[name="sunshine_image_comment_nonce"]' ).val();
        let image_id = $( 'input[name="sunshine_image_id"]' ).val();
        let name = $( 'input[name="sunshine_comment_name"]' ).val();
        let email = $( 'input[name="sunshine_comment_email"]' ).val();
        let content = $( 'textarea[name="sunshine_comment_content"]' ).val();

        // Mini cart set to loading
        $( '#sunshine--image--comments--add-form' ).addClass( 'sunshine--loading' );

        // Then run ajax request
        $.ajax({
            type: 'POST',
            url: sunshine_photo_cart.ajax_url,
            data: {
                action: 'sunshine_modal_add_comment',
                image_id: image_id,
                name: name,
                email: email,
                content: content,
                security: security
            },
            success: function( result, textStatus, XMLHttpRequest) {
                if ( result.success ) {
                    $( '#sunshine--image--comments--list' ).append( result.data.html );
                    // Reset the form fields
                    $( 'textarea[name="sunshine_comment_content"]' ).val( '' );

                    // TODO: Increase comment counts where needed on page
                    $( '.sunshine--image-' + image_id + ' .sunshine--comments .sunshine--count, #sunshine--image-' + image_id + ' .sunshine--comments .sunshine--count' ).html( result.data.count );

                    if ( result.data.count > 0 ) {
                        $( '.sunshine--image-' + image_id + ', #sunshine--image-' + image_id ).addClass( 'sunshine--image--has-comments' );
                    }
                } else {
                    $( '#sunshine--image--comments--list' ).append( '<div class="sunshine--image--comments--error">' + result.data + ' </div>');
                }
            },
            error: function( MLHttpRequest, textStatus, errorThrown ) {
                alert( 'Sorry, there was an error with your request: ' + errorThrown + MLHttpRequest + textStatus ); // TODO: Better error
            }
        }).always(function(){
            $( '#sunshine--image--comments--add-form' ).removeClass( 'sunshine--loading' );
        });

        return false;

    });

    // Login
    $( 'body' ).on( 'submit', '#sunshine--account--login-form', function(e){

        e.preventDefault();

        // Remove error just in case it exists
        $( '.sunshine--account--error' ).remove();

        let email = $( 'input[name="sunshine_login_email"]' ).val();
        let password = $( 'input[name="sunshine_login_password"]' ).val();
        let security = $( 'input[name="sunshine_login_nonce"]' ).val();

        // Form set to loading
        $( '#sunshine--account--login-form' ).addClass( 'sunshine--loading' );

        // Then run ajax request
        $.ajax({
            type: 'POST',
            url: sunshine_photo_cart.ajax_url,
            data: {
                action: 'sunshine_modal_login',
                email: email,
                password: password,
                security: security
            },
            success: function( result, textStatus, XMLHttpRequest) {
                if ( result.success ) {
                    // Refresh the page, should now be logged in
                    document.location.reload();
                } else {
                    $( '#sunshine--account--login-form' ).prepend( '<div class="sunshine--account--error">' + result.data + ' </div>');
                }
            },
            error: function( MLHttpRequest, textStatus, errorThrown ) {
                alert( 'Sorry, there was an error with your request: ' + errorThrown + MLHttpRequest + textStatus ); // TODO: Better error
            }
        }).always(function(){
            $( '#sunshine--account--login-form' ).removeClass( 'sunshine--loading' );
        });

        return false;

    });


    // Sign up
    $( 'body' ).on( 'submit', '#sunshine--account--signup-form', function(e){

        e.preventDefault();

        // Remove error just in case it exists
        $( '.sunshine--account--error' ).remove();

        let email = $( 'input[name="sunshine_signup_email"]' ).val();
        let password = $( 'input[name="sunshine_signup_password"]' ).val();
        let security = $( 'input[name="sunshine_signup_nonce"]' ).val();

        // Form set to loading
        $( '#sunshine--account--signup-form' ).addClass( 'sunshine--loading' );

        // Then run ajax request
        $.ajax({
            type: 'POST',
            url: sunshine_photo_cart.ajax_url,
            data: {
                action: 'sunshine_modal_signup',
                email: email,
                password: password,
                security: security
            },
            success: function( result, textStatus, XMLHttpRequest) {
                if ( result.success ) {
                    // Refresh the page, email should have been triggered
                    document.location.reload();
                } else {
                    $( '#sunshine--account--signup-form' ).prepend( '<div class="sunshine--account--error">' + result.data + ' </div>');
                }
            },
            error: function( MLHttpRequest, textStatus, errorThrown ) {
                alert( 'Sorry, there was an error with your request: ' + errorThrown + MLHttpRequest + textStatus ); // TODO: Better error
            }
        }).always(function(){
            $( '#sunshine--account--signup-form' ).removeClass( 'sunshine--loading' );
        });

        return false;

    });


    // Password Reset
    $( 'body' ).on( 'submit', '#sunshine--account--reset-password-form', function(e){

        e.preventDefault();

        // Remove error just in case it exists
        $( '.sunshine--account--error' ).remove();

        let email = $( 'input[name="sunshine_reset_password_email"]' ).val();
        let security = $( 'input[name="sunshine_reset_password_nonce"]' ).val();

        // Form set to loading
        $( '#sunshine--account--reset-password-form' ).addClass( 'sunshine--loading' );

        // Then run ajax request
        $.ajax({
            type: 'POST',
            url: sunshine_photo_cart.ajax_url,
            data: {
                action: 'sunshine_modal_reset_password',
                email: email,
                security: security
            },
            success: function( result, textStatus, XMLHttpRequest) {
                if ( result.success ) {
                    // Refresh the page, email should have been triggered
                    document.location.reload();
                } else {
                    $( '#sunshine--account--reset-password-form' ).prepend( '<div class="sunshine--account--error">' + result.data + ' </div>');
                }
            },
            error: function( MLHttpRequest, textStatus, errorThrown ) {
                alert( 'Sorry, there was an error with your request: ' + errorThrown + MLHttpRequest + textStatus ); // TODO: Better error
            }
        }).always(function(){
            $( '#sunshine--account--reset-password-form' ).removeClass( 'sunshine--loading' );
        });

        return false;

    });

    // TODO Move favorites in here

});
