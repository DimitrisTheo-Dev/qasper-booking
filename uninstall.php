<?php
/**
 * Fires when the plugin is uninstalled from the WordPress admin.
 * Removes stored settings and setup-notice options so no residue is left behind.
 *
 * @package Qasper_Booking
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'qasper_booking_settings' );
delete_option( 'qasper_booking_show_setup_notice' );
