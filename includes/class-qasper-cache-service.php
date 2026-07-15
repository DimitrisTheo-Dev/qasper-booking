<?php
/**
 * Clears caches that can otherwise keep stale public widget markup visible.
 *
 * @package Qasper_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Qasper_Cache_Service {

	/**
	 * Clear WordPress object caches and WP Super Cache when it is available.
	 *
	 * The custom action lets hosting integrations purge an additional full-page
	 * or CDN cache without making this plugin depend on a specific provider.
	 */
	public static function purge_public_page_caches() {
		wp_cache_flush();

		if ( function_exists( 'wp_cache_clear_cache' ) ) {
			wp_cache_clear_cache();
		}

		do_action( 'qasper_booking_public_page_caches_purged' );
	}
}
