<?php
function sunshine_system_info() {
	global $sunshine;
	?>
<div class="wrap wps-wrap">
		<h2>System Information</h2>
		<p>Use the information below when submitting tickets or questions via <a href="http://www.sunshinephotocart.com/support" target="_blank">Sunshine Support</a>.</p>

<textarea id="sunshine-system-info" readonly="readonly" style="font-family: 'courier new', monospace; margin: 10px 0 0 0; width: 900px; height: 400px;" title="To copy the system info, click below then press Ctrl + C (PC) or Cmd + C (Mac).">

### Begin System Info ###

Home Page:                <?php echo site_url() . "\n"; ?>
Gallery URL:              <?php echo get_permalink( SPC()->get_option( 'page' ) ) . "\n"; ?>
Admin:                 	  <?php echo admin_url() . "\n"; ?>

WordPress Version:        <?php echo get_bloginfo( 'version' ) . "\n"; ?>

PHP Version:              <?php echo PHP_VERSION . "\n"; ?>
PHP Memory Limit:         <?php echo ini_get( 'memory_limit' ) . "\n"; ?>
WordPress Memory Limit:   <?php echo ( sunshine_let_to_num( WP_MEMORY_LIMIT ) / ( 1024 * 1024 ) ) . 'MB'; ?><?php echo "\n"; ?>
ImageMagick:              <?php
	echo ( extension_loaded( 'imagick' ) ) ? 'Yes' : 'No';
	echo  "\n";
	?>
Image Quality:            <?php echo apply_filters( 'jpeg_quality', 60 ); ?>
<?php do_action( 'sunshine_sunshine_info' ); ?>


ACTIVE PLUGINS:

<?php
$plugins        = get_plugins();
$active_plugins = get_option( 'active_plugins', array() );

foreach ( $plugins as $plugin_path => $plugin ) :

//If the plugin isn't active, don't show it.
if ( ! in_array( $plugin_path, $active_plugins ) ) {
	continue;
}
?>
<?php echo $plugin['Name']; ?>: <?php echo $plugin['Version']; ?>

<?php endforeach; ?>

CURRENT THEME:

<?php
if ( get_bloginfo( 'version' ) < '3.4' ) {
	$theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
	echo $theme_data['Name'] . ': ' . $theme_data['Version'];
} else {
	$theme_data = wp_get_theme();
	echo $theme_data->Name . ': ' . $theme_data->Version;
}
?>


SUNSHINE SETTINGS:

<?php
$fields = sunshine_get_settings_fields();
foreach ( $fields as $section ) :
	foreach ( $section['fields'] as $field ) {
		if ( $field['type'] == 'header' || strpos( 'token', $field['id'] ) !== false || strpos( 'key', $field['id'] ) !== false ) {
			continue; // Exclude some more sensitive items
		}
		$value = SPC()->get_option( $field['id'] );
		if ( is_array( $value ) ) {
			$values = $value;
			$value  = '';
			foreach ( $values as $k => $v ) {
				$value .= $k . ': ' . $v . '|';
			}
		}
		echo $field['name'] . ': ' . $value . "\r\n";
	}
endforeach;
?>

IMAGE SIZES:

<?php
global $_wp_additional_image_sizes;
foreach ( $_wp_additional_image_sizes as $name => $image_size ) {
$crop = ( $image_size['crop'] ) ? 'cropped' : 'not cropped';
?>
<?php echo $name . ': ' . $image_size['width'] . 'x' . $image_size['height'] . ' (' . $crop . ')'; ?>

<?php } ?>

### End System Info ###
</textarea>

	</div>
	<p><button class="button button-primary" onclick="sunshine_copy_system_info()"><?php _e( 'Copy system info to clipboard', 'sunshine-photo-cart' ); ?></button></p>
	<script>
	function sunshine_copy_system_info() {
		var copyText = document.getElementById( "sunshine-system-info" );
		copyText.select();
		document.execCommand( "copy" );
		jQuery( '.button-primary' ).after( '<span class="copied" style="display: inline-block; margin-left: 20px; font-size: 16px; color: green; font-weight: bold;">Copied!</div>' );
		jQuery( '.copied' ).delay( 3000 ).fadeOut();
	}
	</script>
	<p><?php _e( 'Our support team may ask you to manually run the update process.', 'sunshine-photo-cart' ); ?> <a href="admin.php?page=sunshine_system_info&amp;sunshine_force_update=1"><?php _e( 'Click here to do so', 'sunshine-photo-cart' ); ?></a></p>
	<?php

}
