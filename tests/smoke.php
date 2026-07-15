<?php
/**
 * Lightweight plugin smoke tests with WordPress function stubs.
 *
 * @package Qasper_Booking
 */

define( 'ABSPATH', __DIR__ . '/../' );
define( 'QASPER_BOOKING_VERSION', '1.1.0' );
define( 'QASPER_BOOKING_URL', 'https://example.test/wp-content/plugins/qasper-booking/' );
define( 'QASPER_BOOKING_WIDGET_SCRIPT', 'https://qasper.ai/embed/qasper-widget.js' );
define( 'QASPER_BOOKING_AGENT_URL_BASE', 'https://qasper.ai/business-agent' );
define( 'QASPER_BOOKING_OPTION_NAME', 'qasper_booking_settings' );
define( 'QASPER_BOOKING_SCRIPT_HANDLE', 'qasper-widget' );

$qasper_booking_options                 = array();
$qasper_booking_scripts                 = array();
$qasper_booking_styles                  = array();
$qasper_booking_settings_errors         = array();
$qasper_booking_actions                 = array();
$qasper_booking_object_cache_flushes    = 0;
$qasper_booking_page_cache_flushes      = 0;
$qasper_booking_manage_options_allowed  = true;

function qasper_smoke_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, $message . PHP_EOL );
		exit( 1 );
	}
}

function get_option( $name, $default = false ) {
	global $qasper_booking_options;
	return array_key_exists( $name, $qasper_booking_options ) ? $qasper_booking_options[ $name ] : $default;
}

function update_option( $name, $value, $autoload = null ) {
	global $qasper_booking_options;
	$qasper_booking_options[ $name ] = $value;
	return true;
}

function delete_option( $name ) {
	global $qasper_booking_options;
	unset( $qasper_booking_options[ $name ] );
	return true;
}

function shortcode_atts( $pairs, $atts, $shortcode = '' ) {
	return array_merge( $pairs, (array) $atts );
}

function __( $text, $domain = 'default' ) {
	return $text;
}

function esc_html__( $text, $domain = 'default' ) {
	return esc_html( $text );
}

function esc_url( $text ) {
	return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
}

function esc_attr( $text ) {
	return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
}

function esc_html( $text ) {
	return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
}

function esc_attr_e( $text, $domain = 'default' ) {
	echo esc_attr( $text );
}

function esc_html_e( $text, $domain = 'default' ) {
	echo esc_html( $text );
}

function sanitize_text_field( $text ) {
	return trim( wp_strip_all_tags( (string) $text ) );
}

function wp_strip_all_tags( $text ) {
	return strip_tags( (string) $text );
}

function add_settings_error( $setting, $code, $message, $type = 'error' ) {
	global $qasper_booking_settings_errors;
	$qasper_booking_settings_errors[] = compact( 'setting', 'code', 'message', 'type' );
}

function settings_errors( $setting = '' ) {
	global $qasper_booking_settings_errors;
	foreach ( $qasper_booking_settings_errors as $error ) {
		if ( '' === $setting || $setting === $error['setting'] ) {
			echo '<div class="notice notice-' . esc_attr( $error['type'] ) . '"><p>' . esc_html( $error['message'] ) . '</p></div>';
		}
	}
}

function get_locale() {
	return 'en_US';
}

function wp_json_encode( $data, $flags = 0 ) {
	return json_encode( $data, $flags );
}

function wp_register_script( $handle, $src, $deps = array(), $ver = false, $in_footer = false ) {
	global $qasper_booking_scripts;
	$qasper_booking_scripts[ $handle ]['registered'] = compact( 'src', 'deps', 'ver', 'in_footer' );
}

function wp_add_inline_script( $handle, $data, $position = 'after' ) {
	global $qasper_booking_scripts;
	$qasper_booking_scripts[ $handle ]['inline'][] = compact( 'data', 'position' );
}

function wp_enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {
	global $qasper_booking_scripts;
	$qasper_booking_scripts[ $handle ]['enqueued'] = true;
	if ( '' !== $src ) {
		$qasper_booking_scripts[ $handle ]['source'] = $src;
	}
}

function wp_enqueue_style( $handle, $src = '', $deps = array(), $ver = false ) {
	global $qasper_booking_styles;
	$qasper_booking_styles[ $handle ] = compact( 'src', 'deps', 'ver' );
}

function current_user_can( $capability ) {
	global $qasper_booking_manage_options_allowed;
	return 'manage_options' === $capability && $qasper_booking_manage_options_allowed;
}

function settings_fields( $option_group ) {
	echo '<input type="hidden" name="option_page" value="' . esc_attr( $option_group ) . '" />';
	echo '<input type="hidden" name="_wpnonce" value="smoke" />';
}

function submit_button( $text = 'Save Changes' ) {
	echo '<input type="submit" class="button button-primary" value="' . esc_attr( $text ) . '" />';
}

function selected( $selected, $current, $display = true ) {
	$result = (string) $selected === (string) $current ? ' selected="selected"' : '';
	if ( $display ) {
		echo $result;
	}
	return $result;
}

function checked( $checked, $current = true, $display = true ) {
	$result = (bool) $checked === (bool) $current ? ' checked="checked"' : '';
	if ( $display ) {
		echo $result;
	}
	return $result;
}

function home_url( $path = '' ) {
	return 'https://example.test' . $path;
}

function admin_url( $path = '' ) {
	return 'https://example.test/wp-admin/' . ltrim( $path, '/' );
}

function wp_cache_flush() {
	global $qasper_booking_object_cache_flushes;
	++$qasper_booking_object_cache_flushes;
	return true;
}

function wp_cache_clear_cache() {
	global $qasper_booking_page_cache_flushes;
	++$qasper_booking_page_cache_flushes;
}

function do_action( $hook_name ) {
	global $qasper_booking_actions;
	$qasper_booking_actions[] = $hook_name;
}

require_once __DIR__ . '/../includes/class-qasper-snippet-builder.php';
require_once __DIR__ . '/../includes/class-qasper-shortcodes.php';
require_once __DIR__ . '/../includes/class-qasper-script-injector.php';
require_once __DIR__ . '/../includes/class-qasper-cache-service.php';
require_once __DIR__ . '/../includes/class-qasper-settings.php';
require_once __DIR__ . '/../includes/class-qasper-plugin-setup-service.php';

$button_html = Qasper_Shortcodes::render_button(
	array(
		'slug'           => 'new-york-barber',
		'label'          => 'Book now',
		'accent'         => '#3B82F6',
		'channel_source' => 'website',
	)
);
qasper_smoke_assert(
	false !== strpos( $button_html, 'https://qasper.ai/business-agent/new-york-barber/chat?channelSource=wordpress_site&amp;lang=en' ),
	'Button href did not force WordPress channel attribution.'
);
qasper_smoke_assert( false !== strpos( $button_html, 'background:#3b82f6;' ), 'Button accent was not normalized into the style.' );
qasper_smoke_assert( false !== strpos( $button_html, '>Book now</a>' ), 'Button label was not rendered.' );

$qasper_booking_scripts = array();
Qasper_Shortcodes::render_chat(
	array(
		'slug'     => 'new-york-barber',
		'position' => 'left',
		'accent'   => '#abc',
		'theme'    => 'dark',
	)
);
$inline_script = $qasper_booking_scripts[ QASPER_BOOKING_SCRIPT_HANDLE ]['inline'][0]['data'];
qasper_smoke_assert( ! empty( $qasper_booking_scripts[ QASPER_BOOKING_SCRIPT_HANDLE ]['enqueued'] ), 'Chat widget script was not enqueued.' );
qasper_smoke_assert( false !== strpos( $inline_script, '"theme":"dark"' ), 'Chat theme was not threaded into the widget config.' );
qasper_smoke_assert( false !== strpos( $inline_script, '"accent":"#abc"' ), 'Chat accent was not threaded into the widget config.' );
qasper_smoke_assert( false !== strpos( $inline_script, '"position":"left"' ), 'Chat position was not threaded into the widget config.' );
qasper_smoke_assert( false !== strpos( $inline_script, '"channelSource":"wordpress_site"' ), 'Chat channel attribution was not threaded into the widget config.' );

$sanitized = Qasper_Settings::sanitize(
	array(
		'slug'            => ' NutriPass-S7QN ',
		'default_label'   => '<b>Chat now</b>',
		'position'        => 'left',
		'sitewide'        => '1',
		'locale_override' => 'el',
		'accent_mode'     => 'custom',
		'accent'          => '#ABC',
		'theme'           => 'dark',
	)
);
qasper_smoke_assert( 'nutripass-s7qn' === $sanitized['slug'], 'Settings did not normalize the business slug.' );
qasper_smoke_assert( '#abc' === $sanitized['accent'], 'Settings did not normalize the custom accent.' );
qasper_smoke_assert( 'dark' === $sanitized['theme'], 'Settings did not save the theme.' );
qasper_smoke_assert( true === $sanitized['sitewide'], 'Settings did not save the site-wide toggle.' );
qasper_smoke_assert( 'Chat now' === $sanitized['default_label'], 'Settings did not sanitize the default label.' );

$qasper_booking_options[ QASPER_BOOKING_OPTION_NAME ] = $sanitized;
ob_start();
Qasper_Settings::render_page();
$settings_html = ob_get_clean();
qasper_smoke_assert( false !== strpos( $settings_html, 'id="qasper-accent"' ), 'Settings page did not render the accent control.' );
qasper_smoke_assert( false !== strpos( $settings_html, 'value="#abc"' ), 'Settings page did not preserve the saved accent.' );
qasper_smoke_assert( 1 === preg_match( '/id="qasper-accent-mode-custom"[\s\S]*?checked="checked"/', $settings_html ), 'Settings page did not preserve custom color mode.' );
qasper_smoke_assert( 1 === preg_match( '/<option value="dark"\s+selected="selected">Dark<\/option>/', $settings_html ), 'Settings page did not render the saved theme.' );
qasper_smoke_assert( 1 === preg_match( '/name="qasper_booking_settings\[sitewide\]"[\s\S]*?checked="checked"/', $settings_html ), 'Settings page did not render the saved site-wide toggle.' );
qasper_smoke_assert( false !== strpos( $settings_html, 'Qasper is active site-wide.' ), 'Settings page did not render the public activation status.' );
qasper_smoke_assert( false !== strpos( $settings_html, '[qasper_button slug="nutripass-s7qn"' ), 'Settings page did not render the saved slug in the button shortcode example.' );
qasper_smoke_assert( false !== strpos( $settings_html, '[qasper_chat slug="nutripass-s7qn"' ), 'Settings page did not render the saved slug in the chat shortcode example.' );
qasper_smoke_assert( false !== strpos( $settings_html, 'channel_source="wordpress_site"' ), 'Settings page shortcode examples omitted WordPress attribution.' );

unset( $qasper_booking_options[ QASPER_BOOKING_OPTION_NAME ] );
$qasper_booking_settings_errors = array();
ob_start();
Qasper_Settings::render_page();
$first_run_html = ob_get_clean();
qasper_smoke_assert( false !== strpos( $first_run_html, 'Save &amp; Activate Qasper' ), 'First-run form did not use the one-save activation label.' );
qasper_smoke_assert( false !== strpos( $first_run_html, 'Setup is incomplete.' ), 'First-run form did not explain its incomplete state.' );
qasper_smoke_assert( 1 === preg_match( '/name="qasper_booking_settings\[sitewide\]"[\s\S]*?checked="checked"/', $first_run_html ), 'First-run form did not enable site-wide chat by default.' );

$qasper_booking_options[ QASPER_BOOKING_OPTION_NAME ] = $sanitized;
$default_accent_settings = Qasper_Settings::sanitize(
	array(
		'slug'            => 'nutripass-s7qn',
		'default_label'   => 'Chat now',
		'position'        => 'left',
		'sitewide'        => '1',
		'locale_override' => 'el',
		'accent_mode'     => 'default',
		'accent'          => '#112233',
		'theme'           => 'dark',
	)
);
qasper_smoke_assert( '' === $default_accent_settings['accent'], 'Default color mode did not clear the custom accent override.' );

$qasper_booking_settings_errors = array();
$invalid_accent_settings = Qasper_Settings::sanitize(
	array(
		'slug'            => 'nutripass-s7qn',
		'default_label'   => 'Chat now',
		'position'        => 'left',
		'sitewide'        => '1',
		'locale_override' => 'el',
		'accent_mode'     => 'custom',
		'accent'          => 'not-a-color',
		'theme'           => 'dark',
	)
);
qasper_smoke_assert( '#abc' === $invalid_accent_settings['accent'], 'Malformed custom color erased the previously saved accent.' );
qasper_smoke_assert( ! empty( $qasper_booking_settings_errors ), 'Malformed custom color did not add a settings error.' );

$qasper_booking_settings_errors = array();
$invalid_slug_settings = Qasper_Settings::sanitize(
	array(
		'slug'        => '../bad-slug',
		'accent_mode' => 'default',
	)
);
qasper_smoke_assert( $sanitized === $invalid_slug_settings, 'Malformed slug did not preserve all previously saved settings.' );
qasper_smoke_assert( ! empty( $qasper_booking_settings_errors ), 'Malformed slug did not add a settings error.' );

$qasper_booking_options[ QASPER_BOOKING_OPTION_NAME ] = $sanitized;
$qasper_booking_scripts = array();
Qasper_Script_Injector::maybe_enqueue_sitewide();
$sitewide_script = $qasper_booking_scripts[ QASPER_BOOKING_SCRIPT_HANDLE ]['inline'][0]['data'];
qasper_smoke_assert( false !== strpos( $sitewide_script, '"slug":"nutripass-s7qn"' ), 'Site-wide injector did not use the saved slug.' );
qasper_smoke_assert( false !== strpos( $sitewide_script, '"channelSource":"wordpress_site"' ), 'Site-wide injector omitted WordPress attribution.' );

$qasper_booking_object_cache_flushes = 0;
$qasper_booking_page_cache_flushes   = 0;
$qasper_booking_actions              = array();
Qasper_Cache_Service::purge_public_page_caches();
qasper_smoke_assert( 1 === $qasper_booking_object_cache_flushes, 'WordPress object cache was not flushed.' );
qasper_smoke_assert( 1 === $qasper_booking_page_cache_flushes, 'WP Super Cache was not flushed.' );
qasper_smoke_assert( in_array( 'qasper_booking_public_page_caches_purged', $qasper_booking_actions, true ), 'Cache integration action was not fired.' );

unset( $qasper_booking_options[ Qasper_Plugin_Setup_Service::SETUP_NOTICE_OPTION ] );
Qasper_Plugin_Setup_Service::handle_activation( false );
qasper_smoke_assert( '1' === get_option( Qasper_Plugin_Setup_Service::SETUP_NOTICE_OPTION ), 'Activation did not schedule setup guidance.' );
ob_start();
Qasper_Plugin_Setup_Service::render_setup_notice();
$setup_notice_html = ob_get_clean();
qasper_smoke_assert( false !== strpos( $setup_notice_html, 'Finish setting up Qasper Booking.' ), 'Activation setup guidance was not rendered.' );
qasper_smoke_assert( false === get_option( Qasper_Plugin_Setup_Service::SETUP_NOTICE_OPTION, false ), 'Activation setup guidance was not one-time.' );

$action_links = Qasper_Plugin_Setup_Service::add_settings_action_link( array( '<a href="#">Deactivate</a>' ) );
qasper_smoke_assert( false !== strpos( $action_links[0], 'options-general.php?page=qasper-booking' ), 'Plugin list did not get a direct Settings link.' );

$qasper_booking_manage_options_allowed = false;
$unauthorized_action_links = Qasper_Plugin_Setup_Service::add_settings_action_link( array( '<a href="#">Deactivate</a>' ) );
qasper_smoke_assert( 1 === count( $unauthorized_action_links ), 'Settings action link rendered without manage_options.' );
$qasper_booking_manage_options_allowed = true;

$qasper_booking_scripts = array();
$qasper_booking_styles  = array();
Qasper_Settings::enqueue_admin_assets( 'settings_page_qasper-booking' );
qasper_smoke_assert( isset( $qasper_booking_scripts[ Qasper_Settings::ADMIN_JS_TAG ]['source'] ), 'Settings JavaScript was not enqueued.' );
qasper_smoke_assert( isset( $qasper_booking_styles[ Qasper_Settings::ADMIN_CSS_TAG ] ), 'Settings stylesheet was not enqueued.' );

$qasper_booking_manage_options_allowed = false;
ob_start();
Qasper_Settings::render_page();
$unauthorized_html = ob_get_clean();
qasper_smoke_assert( '' === $unauthorized_html, 'Settings page rendered without manage_options.' );
$qasper_booking_manage_options_allowed = true;

$admin_script = file_get_contents( __DIR__ . '/../assets/admin.js' );
qasper_smoke_assert( false !== strpos( $admin_script, 'customColorRadio.checked = true' ), 'Color picker does not select custom color mode.' );
qasper_smoke_assert( false !== strpos( $admin_script, "beforeunload" ), 'Settings form does not protect unsaved changes.' );

echo 'Qasper Booking smoke tests passed.' . PHP_EOL;
