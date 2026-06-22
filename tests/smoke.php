<?php
/**
 * Lightweight plugin smoke tests with WordPress function stubs.
 *
 * @package Qasper_Booking
 */

define( 'ABSPATH', __DIR__ . '/../' );
define( 'QASPER_BOOKING_VERSION', '1.0.2' );
define( 'QASPER_BOOKING_WIDGET_SCRIPT', 'https://qasper.ai/embed/qasper-widget.js' );
define( 'QASPER_BOOKING_AGENT_URL_BASE', 'https://qasper.ai/business-agent' );
define( 'QASPER_BOOKING_OPTION_NAME', 'qasper_booking_settings' );
define( 'QASPER_BOOKING_SCRIPT_HANDLE', 'qasper-widget' );

$qasper_booking_options = array();
$qasper_booking_scripts = array();

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

function shortcode_atts( $pairs, $atts ) {
	return array_merge( $pairs, (array) $atts );
}

function __( $text, $domain = 'default' ) {
	return $text;
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

function esc_html_e( $text, $domain = 'default' ) {
	echo esc_html( $text );
}

function sanitize_text_field( $text ) {
	return trim( wp_strip_all_tags( (string) $text ) );
}

function wp_strip_all_tags( $text ) {
	return strip_tags( (string) $text );
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

function wp_enqueue_script( $handle ) {
	global $qasper_booking_scripts;
	$qasper_booking_scripts[ $handle ]['enqueued'] = true;
}

function current_user_can( $capability ) {
	return true;
}

function settings_fields( $option_group ) {
	echo '<input type="hidden" name="_wpnonce" value="smoke" />';
}

function submit_button() {
	echo '<button type="submit">Save Changes</button>';
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

require_once __DIR__ . '/../includes/class-qasper-snippet-builder.php';
require_once __DIR__ . '/../includes/class-qasper-shortcodes.php';
require_once __DIR__ . '/../includes/class-qasper-settings.php';

$button_html = Qasper_Shortcodes::render_button(
	array(
		'slug'   => 'new-york-barber',
		'label'  => 'Book now',
		'accent' => '#3B82F6',
	)
);
qasper_smoke_assert( false !== strpos( $button_html, 'https://qasper.ai/business-agent/new-york-barber/chat?lang=en' ), 'Button href was not rendered.' );
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

$sanitized = Qasper_Settings::sanitize(
	array(
		'slug'            => 'new-york-salon',
		'default_label'   => '<b>Chat now</b>',
		'position'        => 'left',
		'sitewide'        => '1',
		'locale_override' => 'fr',
		'accent'          => '#ABC',
		'theme'           => 'dark',
	)
);
qasper_smoke_assert( '#abc' === $sanitized['accent'], 'Settings did not normalize accent.' );
qasper_smoke_assert( 'dark' === $sanitized['theme'], 'Settings did not save theme.' );
qasper_smoke_assert( true === $sanitized['sitewide'], 'Settings did not save sitewide toggle.' );
qasper_smoke_assert( 'Chat now' === $sanitized['default_label'], 'Settings did not sanitize default label.' );

$qasper_booking_options[ QASPER_BOOKING_OPTION_NAME ] = $sanitized;
ob_start();
Qasper_Settings::render_page();
$settings_html = ob_get_clean();
qasper_smoke_assert( false !== strpos( $settings_html, 'id="qasper-accent"' ), 'Settings page did not render accent control.' );
qasper_smoke_assert( false !== strpos( $settings_html, 'value="#abc"' ), 'Settings page did not render saved accent.' );
qasper_smoke_assert( 1 === preg_match( '/<option value="dark"\\s+selected="selected">Dark<\\/option>/', $settings_html ), 'Settings page did not render saved theme.' );
qasper_smoke_assert( 1 === preg_match( '/name="qasper_booking_settings\\[sitewide\\]" value="1"\\s+checked="checked"/', $settings_html ), 'Settings page did not render saved sitewide toggle.' );
qasper_smoke_assert( false !== strpos( $settings_html, '[qasper_button slug="new-york-salon"' ), 'Settings page did not render the saved slug in the button shortcode example.' );
qasper_smoke_assert( false !== strpos( $settings_html, '[qasper_chat slug="new-york-salon"' ), 'Settings page did not render the saved slug in the chat shortcode example.' );
qasper_smoke_assert( false === strpos( $settings_html, 'slug="berlin-barber"' ), 'Settings page still rendered the old Berlin placeholder slug.' );

echo 'Qasper Booking smoke tests passed.' . PHP_EOL;
