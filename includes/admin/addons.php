<?php
add_action( 'sunshine_addon_check', 'sunshine_get_addon_data', 20 );
add_action( 'sunshine_license_activate', 'sunshine_get_addon_data' );
function sunshine_get_addon_data( $force = false ) {

	$addons = get_transient( 'sunshine_addons_data' );

	if ( empty( $addons ) && isset( $sunshine ) && ( $sunshine->is_pro() || $force ) ) {
		$license_key = SPC()->get_license_key();
		$url = SUNSHINE_PHOTO_CART_STORE_URL . '/?sunshine_addons_feed&referrer=' . $_SERVER['SERVER_NAME'];
		if ( !empty( $license_key ) ) {
			$url = add_query_arg( 'license_key', $license_key, $url );
		}
		$feed = wp_remote_get( esc_url_raw( $url ), array( 'sslverify' => false ) );

		sunshine_log( 'API call: sunshine_get_addon_data' );

		if ( ! is_wp_error( $feed ) ) {
			if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
				$plugins = array();
				$remote_data_items = json_decode( wp_remote_retrieve_body( $feed ) );
				if ( !empty( $remote_data_items ) ) {
					foreach ( $remote_data_items as $remote_data_item ) {
						//if ( empty( $addon->file ) ) continue;
						$addons[] = array(
							'title' => $remote_data_item->title,
							'slug' => $remote_data_item->slug,
							'file' => $remote_data_item->file,
							'url' => $remote_data_item->url,
							'plan' => $remote_data_item->plan,
							'excerpt' => $remote_data_item->excerpt,
							'price' => $remote_data_item->price,
							'image' => $remote_data_item->image
						);
					}
				}
				set_transient( 'sunshine_addons_data', $addons, DAY_IN_SECONDS * 3 );
			}
		}
	}

	return $addons;

}


function sunshine_addon_manager_get_license( $shortname ) {
	global $sunshine;

	if ( empty( SPC()->get_license_key() ) ) {
		return;
	}

	// Get license data from sunshine website
	$url = SUNSHINE_PHOTO_CART_STORE_URL.'/?sunshine_get_license&referrer=' . $_SERVER['SERVER_NAME'] . '&plugin='.$shortname.'&license_key='.SPC()->get_license_key();
	$feed = wp_remote_get( esc_url_raw( $url ), array( 'sslverify' => false ) );
	$license = '';

	if ( ! is_wp_error( $feed ) ) {
		if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
			$license = wp_remote_retrieve_body( $feed );
		}
	}

	sunshine_log( $license, 'API call: sunshine_addon_manager_get_license' );


	return $license;

}

function sunshine_addon_manager_activate_license( $name, $shortname ) {
	global $sunshine;

	if ( empty( SPC()->get_license_key() ) ) {
		return;
	}

	$shortname = str_replace( array( '.php', '-' ) , array( '', '_' ), basename( $shortname ) );

	$license_key = sunshine_addon_manager_get_license( $shortname );

	if ( !$license_key ) {
		SPC()->notices->add_admin( 'sunshine_license_invalid_' . $shortname, sprintf( __( 'Could not automatically retrieve the license key for %s', 'sunshine-photo-cart' ), $name ), 'notice-error', true );
		return;
	}

	// Data to send to the API
	$api_params = array(
		'edd_action' => 'activate_license',
		'license'    => SPC()->get_license_key(),
		'item_name'  => urlencode( $name ),
		'url'        => home_url()
	);

	sunshine_log( 'API call: sunshine_addon_manager_activate_license' );

	// Call the API
	$response = wp_remote_post(
		SUNSHINE_PHOTO_CART_STORE_URL,
		array(
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => $api_params
		)
	);

	// Make sure there are no errors
	if ( is_wp_error( $response ) ) {
		SPC()->notices->add( 'sunshine_license_no_connection', sprintf( __( 'Your licenses could not be activated because your server failed to connect to SunshinePhotoCart.com server: %s', 'sunshine-photo-cart' ), $response->get_error_message() ), 'notice-error', true );
		return;
	}

	// Tell WordPress to look for updates
	set_site_transient( 'update_plugins', null );

	// Decode license data
	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	update_option( $shortname . '_license_active', $license_data->license );
	set_transient( $shortname . '_license_expiration', $license_data->expires, WEEK_IN_SECONDS );

	do_action( $shortname . '_license_activate', $license_data->success );

	if ( ! (bool) $license_data->success ) {
		set_transient( $shortname . '_license_error', $license_data, 1000 );
	} else {
		// Put license key into global Sunshine options
		$options = maybe_unserialize( get_option( 'sunshine_options' ) );
		$options[ $shortname . '_license_key' ] = $license_key;
		update_option( 'sunshine_options', $options );
		delete_transient( $shortname . '_license_error' );
	}

}

function sunshine_addon_manager_deactivate_license( $name, $shortname ) {
	global $sunshine;

	$shortname = str_replace( array( '.php', '-' ) , array( '', '_' ), basename( $shortname ) );

	$license = sunshine_addon_manager_get_license( $shortname );

	// Data to send to the API
	$api_params = array(
		'edd_action' => 'deactivate_license',
		'license'    => $license,
		'item_name'  => urlencode( $name ),
		'url'        => home_url()
	);

	sunshine_log( 'API call: sunshine_addon_manager_deactivate_license' );

	// Call the API
	$response = wp_remote_post(
		SUNSHINE_PHOTO_CART_STORE_URL,
		array(
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => $api_params
		)
	);

	// Make sure there are no errors
	if ( is_wp_error( $response ) ) {
		SPC()->notices->add( 'sunshine_license_no_connection', sprintf( __( 'Your licenses could not be de-activated because your server failed to connect to SunshinePhotoCart.com server to activate the license: %s', 'sunshine-photo-cart' ), $response->get_error_message() ), 'notice-error', true );
		return;
	}

	// Decode the license data
	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	delete_option( $shortname . '_license_active' );

	// Put empty license key into global Sunshine options
	$options = maybe_unserialize( get_option( 'sunshine_options' ) );
	$options[ $shortname . '_license_key' ] = '';
	update_option( 'sunshine_options', $options );

}

function sunshine_child_plugin_notice() {
?>
	<div class="error"><p><?php _e( 'Sorry, all Sunshine add-ons require that the main Sunshine Photo Cart plugin first be active','sunshine-photo-cart' ); ?></p></div>
<?php
}


function sunshine_addons() {

	$plan = get_option( 'sunshine_license_type' );
	?>
	<div id="sunshine-header">
		<h1><?php _e( 'Add-ons for Sunshine Photo Cart', 'sunshine-photo-cart' ); ?></h1>
		<p>
			<?php _e( 'Go beyond the basics, Sunshine’s add-ons let you maximize your profits to help you build an easier and more profitable photo sales process.', 'sunshine-photo-cart' ); ?>
		</p>
		<?php if ( !SPC()->is_pro() ) { ?>
			<p style="font-size: 16px;">Already purchased a license? <a href="<?php echo admin_url( 'admin.php?page=sunshine&tab=licenses' ); ?>" style="color: #fecd08;"><?php _e( 'Enter it here', 'sunshine-photo-cart' ); ?></a>
		<?php } ?>
	</div>

	<div class="wrap sunshine-wrap" id="sunshine-addons-wrap">

		<?php
		if ( SPC()->is_pro() ) {
			sunshine_addons_display_page();
		} else {
			sunshine_upgrade_display_page();
		} ?>

	</div>
	<?php
}

//add_action( 'sunshine_options_header', 'sunshine_promos' );

function sunshine_addons_display_page() {
	global $sunshine;
	$plan = get_option( 'sunshine_license_type' );
	$addons_data = sunshine_get_addon_data();
	$addons = array( 'pro' => array(), 'plus' => array(), 'basic' => array() );
	foreach ( $addons_data as $addon ) {
		$addons[ $addon['plan'] ][] = $addon;
	}
?>
	<h2></h2>

	<div id="sunshine-plans">
		<div class="sunshine-plan">
			<h2>Basic <?php if ( $plan == 'basic' ) { ?><span class="current-plan">&check; Your current plan!</span><?php } ?></h2>
			<ul>
				<?php
				foreach ( $addons['basic'] as $addon ) {
					$action = '';
					if ( is_plugin_active( 'sunshine-' . $addon['slug'] . '/' . $addon['slug'] . '.php' ) ) {
                        $action = '<p class="action"><span class="active">' . __( 'Active', 'sunshine-photo-cart' ) . '</span> <a href="#" class="deactivate" data-addon="' . esc_attr( $addon['slug'] ) . '">' . __( 'Deactivate', 'sunshine-photo-cart' ) . '</a></p>';
                    } elseif ( $plan == 'basic' || $plan == 'plus' || $plan == 'pro' ) {
						if ( is_dir( WP_PLUGIN_DIR . '/sunshine-' . $addon['slug'] ) ) {
							$action = '<p class="action"><a href="#" class="activate" data-addon="' . esc_attr( $addon['slug'] ) . '">' . __( 'Activate', 'sunshine-photo-cart' ) . '</a></p>';
						} else {
							$action = '<p class="action"><a href="#" class="install" data-addon="' . esc_attr( $addon['slug'] ) . '">' . __( 'Install', 'sunshine-photo-cart' ) . '</a></p>';
						}
					}
					echo '<li class="' . $addon['slug'] . '">
							<h3><a href="' . esc_url( $addon['url'] ) . '?utm_source=plugin&utm_medium=link&utm_campaign=addons-list" target="_blank">' . esc_html( $addon['title'] ) . '</a></h3>
							<p>' . esc_html( $addon['excerpt'] ) . '</p>' .
							$action . '
						</li>';
				}
				?>
			</ul>
		</div>
		<div class="sunshine-plan">
			<h2>
                Plus
                <?php if ( $plan == 'plus' ) { ?><span class="current-plan">&check; Your current plan!</span><?php } ?>
                <?php if ( $plan == 'basic' ) { ?><a href="https://www.sunshinephotocart.com/?upgrade=1&license_key=<?php echo SPC()->get_license_key(); ?>&to=plus&utm_source=plugin&utm_medium=link&utm_campaign=addons-list" class="sunshine-button" target="_blank">Upgrade</a><?php } ?>
            </h2>
			<ul>
				<?php
				foreach ( $addons['plus'] as $addon ) {
					$action = '';
					if ( is_plugin_active( 'sunshine-' . $addon['slug'] . '/' . $addon['slug'] . '.php' ) ) {
                        $action = '<p class="action"><span class="active">' . __( 'Active', 'sunshine-photo-cart' ) . '</span> <a href="#" class="deactivate" data-addon="' . esc_attr( $addon['slug'] ) . '">' . __( 'Deactivate', 'sunshine-photo-cart' ) . '</a></p>';
					} elseif ( $plan == 'plus' || $plan == 'pro' ) {
						if ( is_dir( WP_PLUGIN_DIR . '/sunshine-' . $addon['slug'] ) ) {
							$action = '<p class="action"><a href="#" class="activate" data-addon="' . esc_attr( $addon['slug'] ) . '">' . __( 'Activate', 'sunshine-photo-cart' ) . '</a></p>';
						} else {
							$action = '<p class="action"><a href="#" class="install" data-addon="' . esc_attr( $addon['slug'] ) . '">' . __( 'Install', 'sunshine-photo-cart' ) . '</a></p>';
						}
					}
					echo '<li class="' . $addon['slug'] . '">
							<h3><a href="' . esc_url( $addon['url'] ) . '?utm_source=plugin&utm_medium=link&utm_campaign=addons-list" target="_blank">' . esc_html( $addon['title'] ) . '</a></h3>
							<p>' . esc_html( $addon['excerpt'] ) . '</p>' .
							$action . '
						</li>';
				}
				?>
			</ul>
		</div>
		<div class="sunshine-plan">
			<h2>
                Pro
                <?php if ( $plan == 'pro' ) { ?><span class="current-plan">&check; Your current plan!</span><?php } ?>
                <?php if ( $plan == 'basic' || $plan == 'plus' ) { ?><a href="https://www.sunshinephotocart.com/?upgrade=1&license_key=<?php echo SPC()->get_license_key(); ?>&to=pro&utm_source=plugin&utm_medium=link&utm_campaign=addons-list" class="sunshine-button" target="_blank">Upgrade</a><?php } ?>
            </h2>
			<ul>
				<?php
				foreach ( $addons['pro'] as $addon ) {
					$action = '';
					if ( is_plugin_active( 'sunshine-' . $addon['slug'] . '/' . $addon['slug'] . '.php' ) ) {
						$action = '<p class="action"><span class="active">' . __( 'Active', 'sunshine-photo-cart' ) . '</span> <a href="#" class="deactivate" data-addon="' . esc_attr( $addon['slug'] ) . '">' . __( 'Deactivate', 'sunshine-photo-cart' ) . '</a></p>';
					} elseif ( $plan == 'pro' ) {
						if ( is_dir( WP_PLUGIN_DIR . '/sunshine-' . $addon['slug'] ) ) {
							$action = '<p class="action"><a href="#" class="activate" data-addon="' . esc_attr( $addon['slug'] ) . '">' . __( 'Activate', 'sunshine-photo-cart' ) . '</a></p>';
						} else {
							$action = '<p class="action"><a href="#" class="install" data-addon="' . esc_attr( $addon['slug'] ) . '">' . __( 'Install', 'sunshine-photo-cart' ) . '</a></p>';
						}
					}
					echo '<li class="' . $addon['slug'] . '">
							<h3><a href="' . esc_url( $addon['url'] ) . '?utm_source=plugin&utm_medium=link&utm_campaign=addons-list" target="_blank">' . esc_html( $addon['title'] ) . '</a></h3>
							<p>' . esc_html( $addon['excerpt'] ) . '</p>' .
							$action . '
						</li>';
				}
				?>
			</ul>
		</div>
	</div>
	<?php if ( !$sunshine->is_pro() ) { ?>
		<p id="sunshine-disclaimer">Add-ons may be purchased individually - <a href="https://www.sunshinephotocart.com/addons/" target="_blank">Click here to learn more</a></p>
	<?php } ?>

	<script>
	jQuery( document ).ready(function($){
        $( document ).on( 'click', 'a.activate', function(){
			var addon = $( this ).data( 'addon' );
			$( 'li.' + addon + ' p.action a' ).after( '<img src="<?php echo admin_url( 'images/spinner.gif' ); ?>" class="waiting" />' );
			var data = {
				'action': 'sunshine_addon_activate',
				'addon': addon,
				'security': '<?php echo wp_create_nonce( 'sunshine_addon_activate' ); ?>'
			};
			$.post( ajaxurl, data, function( response ) {
				$( 'img.waiting' ).remove();
				if ( response == 'success' ) {
                    $( 'li.' + addon + ' p.action' ).html( '<span class="active"><?php echo esc_js( __( 'Active', 'sunshine-photo-cart' ) ); ?></span>  <a href="#" class="deactivate" data-addon="' + addon + '"><?php echo esc_js( __( 'Deactivate', 'sunshine-photo-cart' ) ); ?></a>' );
				} else {
					$error = $( '<div class="sunshine-addon-error"><?php echo esc_js( __( 'Error attempting to activate add-on', 'sunshine-photo-cart' ) ); ?></div>' );
					$( 'li.' + addon + ' p.action' ).after( $error );
					$error.delay( 3000 ).fadeOut(function(){ $( this ).remove(); });
				}
			});
			return false;
		});
		$( '.install' ).on( 'click', function(){
			var addon = $( this ).data( 'addon' );
			$( 'li.' + addon + ' p.action a' ).after( '<img src="<?php echo admin_url( 'images/spinner.gif' ); ?>" class="waiting" />' );
			var data = {
				'action': 'sunshine_addon_install',
				'addon': addon,
				'security': '<?php echo wp_create_nonce( 'sunshine_addon_install' ); ?>'
			};
			$.post( ajaxurl, data, function( response ) {
				$( 'img.waiting' ).remove();
				if ( response.includes( 'success' ) ) {
					$( 'li.' + addon + ' p.action' ).html( '<span class="active"><?php echo esc_js( __( 'Active', 'sunshine-photo-cart' ) ); ?></span>  <a href="#" class="deactivate" data-addon="' + addon + '"><?php echo esc_js( __( 'Deactivate', 'sunshine-photo-cart' ) ); ?></a>' );
				} else if ( response == 'installed' ) {
					$( 'li.' + addon + ' p.action' ).html( '<a href="#" class="activate" data-addon="' + addon + '"><?php echo esc_js( __( 'Activate', 'sunshine-photo-cart' ) ); ?></a>' );
				} else {
					$error = $( '<div class="sunshine-addon-error"><?php echo esc_js( __( 'Error attempting to install add-on', 'sunshine-photo-cart' ) ); ?></div>' );
					$( 'li.' + addon + ' p.action' ).after( $error );
					$error.delay( 3000 ).fadeOut(function(){ $( this ).remove(); });
				}
			});
			return false;
		});
        $( document ).on( 'click', 'a.deactivate', function(){
			var addon = $( this ).data( 'addon' );
			$( 'li.' + addon + ' p.action' ).append( '<img src="<?php echo admin_url( 'images/spinner.gif' ); ?>" class="waiting" />' );
			var data = {
				'action': 'sunshine_addon_deactivate',
				'addon': addon,
				'security': '<?php echo wp_create_nonce( 'sunshine_addon_deactivate' ); ?>'
			};
			$.post( ajaxurl, data, function( response ) {
				$( 'img.waiting' ).remove();
				if ( response == 'success' ) {
					$( 'li.' + addon + ' p.action' ).html( '<a href="#" class="activate" data-addon="' + addon + '"><?php echo esc_js( __( 'Activate', 'sunshine-photo-cart' ) ); ?></a>' );
				} else {
					$error = $( '<div class="sunshine-addon-error"><?php echo esc_js( __( 'Error attempting to deactivate add-on', 'sunshine-photo-cart' ) ); ?></div>' );
					$( 'li.' + addon + ' p.action' ).after( $error );
					$error.delay( 3000 ).fadeOut(function(){ $( this ).remove(); });
				}
			});
			return false;
		});

	});
	</script>

<?php
}

add_action( 'wp_ajax_sunshine_addon_activate', 'sunshine_addon_activate' );
function sunshine_addon_activate() {
	$result = false;
	if ( isset( $_REQUEST['security'] ) && wp_verify_nonce( $_REQUEST['security'], 'sunshine_addon_activate' ) ) {
		$addon = sanitize_text_field( $_REQUEST['addon'] );
		$activate = activate_plugin( 'sunshine-' . $addon . '/' . $addon . '.php' );
		if ( !is_wp_error( $activate ) ) {
    		$result = 'success';
		}
	}
	die( $result );
}

add_action( 'wp_ajax_sunshine_addon_deactivate', 'sunshine_addon_deactivate' );
function sunshine_addon_deactivate() {
	$result = false;
	if ( isset( $_REQUEST['security'] ) && wp_verify_nonce( $_REQUEST['security'], 'sunshine_addon_deactivate' ) ) {
		$addon = sanitize_text_field( $_REQUEST['addon'] );
		$deactivate = deactivate_plugins( 'sunshine-' . $addon . '/' . $addon . '.php' );
		if ( !is_wp_error( $deactivate ) ) {
    		$result = 'success';
		}
	}
	die( $result );
}


add_action( 'wp_ajax_sunshine_addon_install', 'sunshine_addon_install' );
function sunshine_addon_install() {
	$result = false;
	if ( isset( $_REQUEST['security'] ) && wp_verify_nonce( $_REQUEST['security'], 'sunshine_addon_install' ) ) {

		$addon_slug = sanitize_text_field( $_REQUEST['addon'] );

		if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		}

		$addons_data = sunshine_get_addon_data();
		foreach ( $addons_data as $addon ) {
			if ( $addon_slug == $addon['slug'] ) {
				break;
			}
		}

		$skin_args = array(
			'type'   => 'web',
			'title'  => $addon['title'],
			'url'    => esc_url_raw( $addon['url'] ),
			//'nonce'  => $install_type . '-plugin_' . $slug,
			'plugin' => '',
			'api'    => null,
			'extra'  => null,
		);

		$skin = new Plugin_Installer_Skin( $skin_args );

		// Create a new instance of Plugin_Upgrader.
		$upgrader = new Plugin_Upgrader( $skin );

		$install = $upgrader->install( $addon['file'] );

		if ( $install ) {
			$result = 'installed';
			$activate = activate_plugin( 'sunshine-' . $addon_slug . '/' . $addon_slug . '.php' );
			if ( !is_wp_error( $activate ) ) {
	    		$result = 'success';
			}
		}

	}
	die( $result );
}


function sunshine_upgrade_display_page() {
	$addons_data = sunshine_get_addon_data();
	$addons = array( 'pro' => array(), 'plus' => array(), 'basic' => array() );
	foreach ( $addons_data as $addon ) {
		$addons[ $addon['plan'] ][] = $addon;
	}
?>

<section id="pricing">
<div class="container">
	<div id="plans-all-reasons">
		<span>All plans include:</span>
		<ul>
			<li>30-day money back guarantee</li>
			<li>No commission or limits</li>
			<li>1-on-1 email support</li>
		</ul>
	</div>

	<div id="plans">
		<div id="plan1" class="plan">
			<div class="plan-header">
				<div class="plan-title">Basic</div>
				<div class="plan-price">$99<span>/year</span></div>
				<div class="plan-description">Get started</div>
			</div>
			<div class="plan-specs">
				<p><strong>Add-ons included:</strong></p>
				<ul>
					<?php foreach ( $addons['basic'] as $addon ) { ?>
					<li><a href="<?php echo esc_url( $addon['url'] ); ?>?utm_source=plugin&utm_medium=link&utm_campaign=addons-list" class="addon-details" target="_blank"><?php echo esc_html( $addon['title'] ); ?></a></li>
					<?php } ?>
				</ul>
			</div>
			<div class="plan-buy">
				<a href="https://www.sunshinephotocart.com/checkout/?edd_action=add_to_cart&download_id=129769&utm_source=plugin&utm_medium=link&utm_campaign=addons-list" class="sunshine-button" target="_blank">Buy Now</a>
				<strong>Single site license</strong><br />
				10% annual renewal discount
			</div>
		</div>
		<div id="plan2" class="plan">
			<div class="plan-header">
				<div class="plan-title">Plus</div>
				<div class="plan-price">$129<span>/year</span></div>
				<div class="plan-description">Add great features</div>
			</div>
			<div class="plan-specs">
				<p><strong>All Basic add-ons, plus:</strong></p>
				<ul>
					<?php foreach ( $addons['plus'] as $addon ) { ?>
						<li><a href="<?php echo esc_url( $addon['url'] ); ?>?utm_source=plugin&utm_medium=link&utm_campaign=addons-list" class="addon-details" target="_blank"><?php echo esc_html( $addon['title'] ); ?></a></li>
					<?php } ?>
				</ul>
			</div>
			<div class="plan-buy">
				<a href="https://www.sunshinephotocart.com/checkout?edd_action=add_to_cart&download_id=129771&utm_source=plugin&utm_medium=link&utm_campaign=addons-list" class="sunshine-button" target="_blank">Buy Now</a>
				<strong>Single site license</strong><br />
				10% annual renewal discount
			</div>
		</div>
		<div id="plan3" class="plan">
			<div class="plan-header">
				<div class="plan-title">Pro</div>
				<div class="plan-price">$159<span>/year</span></div>
				<div class="plan-description">Increase revenue</div>
			</div>
			<div class="plan-specs">
				<p><strong>All Basic & Plus add-ons, plus:</strong></p>
				<ul>
					<?php foreach ( $addons['pro'] as $addon ) { ?>
						<li><a href="<?php echo esc_url( $addon['url'] ); ?>?utm_source=plugin&utm_medium=link&utm_campaign=addons-list" class="addon-details" target="_blank"><?php echo esc_html( $addon['title'] ); ?></a></li>
					<?php } ?>
				</ul>
			</div>
			<div class="plan-buy">
				<a href="https://www.sunshinephotocart.com/checkout?edd_action=add_to_cart&download_id=44&utm_source=plugin&utm_medium=link&utm_campaign=addons-list" class="sunshine-button" target="_blank">Buy Now</a>
				<strong>Single site license</strong><br />
				10% annual renewal discount
			</div>
		</div>
		<div id="plan4" class="plan">
			<div class="plan-header">
				<div class="plan-title">Agency</div>
				<div class="plan-price">Contact Us</div>
				<div class="plan-description">Provide value for your clients</div>
			</div>
			<div class="plan-specs">
				<ul>
					<li><strong>All add-ons</strong></li>
					<li>Our team and yours partnering to help create great client photo galleries for your photography website clients</li>
				</ul>
			</div>
			<div class="plan-buy">
				<a href="https://www.sunshinephotocart.com/contact/?utm_source=plugin&utm_medium=link&utm_campaign=addons-list" class="sunshine-button" target="_blank">Contact Us</a>
				<strong>Multiple site license</strong><br />&nbsp;
			</div>
		</div>
	</div>
	<div id="plan-notes">
		Licenses will automatically renew one calendar year after purchase. This can be cancelled at any time.
	</div>
	<div id="guarantee">
		<div id="guarantee-image"><img src="<?php echo SUNSHINE_PHOTO_CART_URL; ?>/assets/images/30days.svg" alt="30-day money back guarantee" /></div>
		<div id="guarantee-content">
			<p><strong>100% MONEY BACK GUARANTEE</strong>
			If for any reason you are not happy with this plugin, simply let me know within 30 days of your purchase and I will refund 100% of your money. No questions asked.</p>
			<p id="guarantee-signature"><img src="<?php echo SUNSHINE_PHOTO_CART_URL; ?>/assets/images/DerekAshauer@2x.png" alt="Derek Ashauer" /></p>
		</div>
	</div>
</div>

<div id="addons">
	<div class="container">
		<nav id="filters">
			<ul>
				<li><a href="#basic" data-type="basic">Basic</a></li>
				<li><a href="#plus" data-type="plus">Plus</a></li>
				<li><a href="#pro" data-type="pro" class="active">Pro</a></li>
			</ul>
		</nav>
		<ul id="addons-list">
			<?php
			$addons_totals = array(
				'basic' => array( 'count' => 0, 'price' => 0 ),
				'plus' => array( 'count' => 0, 'price' => 0 ),
				'pro' => array( 'count' => 0, 'price' => 0 ),
			);
			foreach ( $addons_data as $addon ) {
				$addons_totals[ $addon['plan'] ]['count']++;
				$addons_totals[ $addon['plan'] ]['price'] += $addon['price'];
			?>
				<li class="<?php echo esc_attr( $addon['plan'] ); ?>">
					<div class="addon">
						<?php if ( !empty( $addon['image'] ) ) { ?>
							<img src="<?php echo esc_url( $addon['image'] ); ?>" alt="<?php echo esc_attr( $addon['title'] ); ?>" />
						<?php } ?>
						<h3><?php echo wp_kses_post( $addon['title'] ); ?></h3>
						<div>
							<?php echo wp_kses_post( $addon['excerpt'] ); ?>
						</div>
						<p class="more"><a href="<?php echo esc_attr( $addon['url'] ); ?>?utm_source=plugin&utm_medium=link&utm_campaign=addons-list" target="_blank">Learn more</a></p>
					</div>
				</li>
			<?php } ?>
		</ul>
		<script>
		jQuery( document ).ready(function($){
			$( '#filters a' ).on( 'click', function( event ) {
				var selected_filter = $( this ).data( 'type' );
				$( '#filters a' ).removeClass( 'active' );
				$( this ).addClass( 'active' );
				$( '#addons-list > li, .feature-set-cta' ).hide();

				if ( selected_filter == 'basic' ) {
					$( '#addons-list > li.basic, .feature-set-cta.basic' ).show();
				} else if ( selected_filter == 'plus' ) {
					$( '#addons-list > li.basic, #addons-list li.plus, .feature-set-cta.plus' ).show();
				} else if ( selected_filter == 'pro' ) {
					$( '#addons-list > li.basic, #addons-list > li.plus, #addons-list > li.pro, .feature-set-cta.pro' ).show();
				}
				event.preventDefault();
			});
		});
		</script>

		<div class="feature-set-cta basic">
			<p>Get <strong>all <?php echo esc_html( $addons_totals['basic']['count'] ); ?> add-ons</strong> worth $<?php echo esc_html( $addons_totals['basic']['price'] ); ?> for <strong>only $99!</strong></p>
			<p><a href="https://www.sunshinephotocart.com/checkout?edd_action=add_to_cart&download_id=129769" class="sunshine-button">Buy Basic Now</a></p>
		</div>

		<div class="feature-set-cta plus">
			<p>Get <strong>all <?php echo esc_html( $addons_totals['basic']['count'] + $addons_totals['plus']['count'] ); ?> add-ons</strong> worth $<?php echo esc_html( $addons_totals['basic']['price'] + $addons_totals['plus']['price'] ); ?> for <strong>only $129!</strong></p>
			<p><a href="https://www.sunshinephotocart.com/checkout?edd_action=add_to_cart&download_id=129771" class="sunshine-button">Buy Plus Now</a></p>
		</div>

		<div class="feature-set-cta pro active">
			<p>Get <strong>all <?php echo esc_html( $addons_totals['basic']['count'] + $addons_totals['plus']['count'] + $addons_totals['pro']['count'] ); ?> add-ons</strong> worth $<?php echo esc_html( $addons_totals['basic']['price'] + $addons_totals['plus']['price'] + $addons_totals['pro']['price'] ); ?> for <strong>only $159!</strong></p>
			<p><a href="https://www.sunshinephotocart.com/checkout?edd_action=add_to_cart&download_id=44" class="sunshine-button">Buy Pro Now</a></p>
		</div>

	</div>
</div>

</section>

<div class="extra" id="faq" itemscope itemtype="https://schema.org/FAQPage">
<div class="container">
	<h2>Frequently Asked Questions</h2>
	<div itemscope itemprop="mainEntity" itemtype="https://schema.org/Question" class="question">
		<h3 itemprop="name">Can I purchase individual add-ons?</h3>
		<div itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
		  <div itemprop="text">
			<p>Absolutely. If you don't think any of our bundle plans fits your needs you are welcome to purchase individual add-ons that do.</p>
		  </div>
		</div>
	</div>
	<div itemscope itemprop="mainEntity" itemtype="https://schema.org/Question" class="question">
		<h3 itemprop="name">Do I have to purchase add-ons or a bundle plan to use Sunshine?</h3>
		<div itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
		  <div itemprop="text">
			<p>No. Add-ons are entirely optional but do help to dramatically extend the functionality of Sunshine Photo Cart.</p>
		  </div>
		</div>
	</div>
	<div itemscope itemprop="mainEntity" itemtype="https://schema.org/Question" class="question">
		<h3 itemprop="name">How many sites can I activate my license key on?</h3>
		<div itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
		  <div itemprop="text">
			<p>You can use a license and all the add-ons on one (1) live site and one (1) test/staging site. </p>
		  </div>
		</div>
	</div>
	<div itemscope itemprop="mainEntity" itemtype="https://schema.org/Question" class="question">
		<h3 itemprop="name">Is there additional fees?</h3>
		<div itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
		  <div itemprop="text">
			<p>Sunshine bundle plans renew annually with a 10% discount from your original purchase price.</p>
		  </div>
		</div>
	</div>
	<div itemscope itemprop="mainEntity" itemtype="https://schema.org/Question" class="question">
		<h3 itemprop="name">What happens if I do not renew my license?</h3>
		<div itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
		  <div itemprop="text">
			<p>License keys are subscription-based and will automatically renew every year. If you decide to cancel, you may still use the add-ons but you will not receive updates or support once the license key expires. We cannot guarantee add-ons will work with all future updates as Sunshine evolves and grows.</p>
		  </div>
		</div>
	</div>
	<div itemscope itemprop="mainEntity" itemtype="https://schema.org/Question" class="question">
		<h3 itemprop="name">Can I request a refund?</h3>
		<div itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
		  <div itemprop="text">
			<p>You are more than welcome to request a refund within 30 days of purchasing your license keys. Renewal payments are non-refundable. <a href="https://www.sunshinephotocart.com/terms-conditions/">View full terms and conditions</a>.</p>
		  </div>
		</div>
	</div>
</div>
</div>

<?php
}

// TODO: Remove?
add_filter( 'cron_schedules', 'sunshine_cron_add_weekly' );
function sunshine_cron_add_weekly( $schedules ) {
	// add a 'weekly' schedule to the existing set
	$schedules['weekly'] = array(
		'interval' => 604800,
		'display'  => __( 'Once Weekly' ),
	);
	return $schedules;
}

/*
add_action( 'admin_init', 'sunshine_addon_check_cron' );
function sunshine_addon_check_cron() {
	if ( isset( $_GET['page'] ) && $_GET['page'] == 'sunshine_addons' ) {
		$plugins = get_option( 'sunshine_addons' );
		if ( empty( $plugins ) || isset( $_GET['refresh'] ) ) {
			sunshine_get_addons();
		}
	}
}

add_action( 'sunshine_addon_check', 'sunshine_get_addons' );
add_action( 'sunshine_install', 'sunshine_get_addons' );
function sunshine_get_addons() {
	global $sunshine;

	$pro = 0;
	if ( $sunshine->is_pro() ) {
		$pro = 1;
	}

	$url = SUNSHINE_PHOTO_CART_STORE_URL . '/?sunshine_addons_feed&pro=' . $pro;

	sunshine_log( 'API call: sunshine_get_addons' );

	$feed = wp_remote_get( esc_url_raw( $url ), array( 'sslverify' => false ) );
	if ( ! is_wp_error( $feed ) ) {
		if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
			$addons = json_decode( wp_remote_retrieve_body( $feed ) );
			update_option( 'sunshine_addons', $addons );
		}
	}

	return $addons;
}
*/
