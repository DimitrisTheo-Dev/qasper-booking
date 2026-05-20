<?php
/**
 * Fires when the plugin is uninstalled from the WordPress admin.
 * Removes the stored settings option so no plugin residue is left behind.
 *
 * @package Qasper_Booking
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'qasper_booking_settings' );
