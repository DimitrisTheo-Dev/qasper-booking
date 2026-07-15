<?php
/**
 * Plugin Name:       Qasper Booking
 * Plugin URI:        https://qasper.ai/wordpress
 * Description:       Embed a Qasper booking button or AI chat widget on your WordPress site.
 * Version:           1.1.0
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            Qasper
 * Author URI:        https://qasper.ai
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       qasper-booking
 *
 * @package Qasper_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'QASPER_BOOKING_VERSION', '1.1.0' );
define( 'QASPER_BOOKING_FILE', __FILE__ );
define( 'QASPER_BOOKING_DIR', plugin_dir_path( __FILE__ ) );
define( 'QASPER_BOOKING_URL', plugin_dir_url( __FILE__ ) );
define( 'QASPER_BOOKING_WIDGET_SCRIPT', 'https://qasper.ai/embed/qasper-widget.js' );
define( 'QASPER_BOOKING_AGENT_URL_BASE', 'https://qasper.ai/business-agent' );
define( 'QASPER_BOOKING_OPTION_NAME', 'qasper_booking_settings' );
define( 'QASPER_BOOKING_SCRIPT_HANDLE', 'qasper-widget' );

require_once QASPER_BOOKING_DIR . 'includes/class-qasper-snippet-builder.php';
require_once QASPER_BOOKING_DIR . 'includes/class-qasper-shortcodes.php';
require_once QASPER_BOOKING_DIR . 'includes/class-qasper-script-injector.php';
require_once QASPER_BOOKING_DIR . 'includes/class-qasper-cache-service.php';
require_once QASPER_BOOKING_DIR . 'includes/class-qasper-plugin-setup-service.php';

register_activation_hook( QASPER_BOOKING_FILE, array( 'Qasper_Plugin_Setup_Service', 'handle_activation' ) );
register_deactivation_hook( QASPER_BOOKING_FILE, array( 'Qasper_Plugin_Setup_Service', 'handle_deactivation' ) );

add_action(
	'add_option_' . QASPER_BOOKING_OPTION_NAME,
	array( 'Qasper_Cache_Service', 'purge_public_page_caches' ),
	10,
	0
);
add_action(
	'update_option_' . QASPER_BOOKING_OPTION_NAME,
	array( 'Qasper_Cache_Service', 'purge_public_page_caches' ),
	10,
	0
);

if ( is_admin() ) {
	require_once QASPER_BOOKING_DIR . 'includes/class-qasper-settings.php';
	add_action( 'admin_menu', array( 'Qasper_Settings', 'register_menu' ) );
	add_action( 'admin_init', array( 'Qasper_Settings', 'register_settings' ) );
	add_action( 'admin_enqueue_scripts', array( 'Qasper_Settings', 'enqueue_admin_assets' ) );
	add_action( 'admin_notices', array( 'Qasper_Plugin_Setup_Service', 'render_setup_notice' ) );
	add_filter(
		'plugin_action_links_' . plugin_basename( QASPER_BOOKING_FILE ),
		array( 'Qasper_Plugin_Setup_Service', 'add_settings_action_link' )
	);
}

add_action( 'init', array( 'Qasper_Shortcodes', 'register' ) );
add_action( 'wp_enqueue_scripts', array( 'Qasper_Script_Injector', 'maybe_enqueue_sitewide' ) );
