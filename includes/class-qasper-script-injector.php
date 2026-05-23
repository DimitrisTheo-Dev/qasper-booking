<?php
/**
 * Site-wide widget injector. If the admin enabled the sitewide toggle in
 * Settings → Qasper Booking, this enqueues the floating chat widget on
 * every public-facing page via `wp_enqueue_scripts`.
 *
 * @package Qasper_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Qasper_Script_Injector {

	public static function maybe_enqueue_sitewide() {
		$settings = (array) get_option( QASPER_BOOKING_OPTION_NAME, array() );

		if ( empty( $settings['sitewide'] ) ) {
			return;
		}

		$slug = isset( $settings['slug'] ) ? $settings['slug'] : '';
		if ( ! Qasper_Snippet_Builder::is_valid_slug( $slug ) ) {
			return;
		}

		$label    = isset( $settings['default_label'] ) && $settings['default_label'] !== '' ? $settings['default_label'] : __( 'Chat', 'qasper-booking' );
		$position = isset( $settings['position'] ) && in_array( $settings['position'], array( 'left', 'right' ), true ) ? $settings['position'] : 'right';
		$locale   = Qasper_Snippet_Builder::resolve_locale( isset( $settings['locale_override'] ) ? $settings['locale_override'] : 'auto' );
		$accent   = Qasper_Snippet_Builder::sanitize_accent( isset( $settings['accent'] ) ? $settings['accent'] : '' );

		$cfg = array(
			'slug'     => $slug,
			'mode'     => 'floating',
			'position' => $position,
			'label'    => (string) $label,
			'locale'   => $locale,
		);
		if ( '' !== $accent ) {
			$cfg['accent'] = $accent;
		}

		wp_register_script( QASPER_BOOKING_SCRIPT_HANDLE, QASPER_BOOKING_WIDGET_SCRIPT, array(), QASPER_BOOKING_VERSION, true );
		wp_add_inline_script( QASPER_BOOKING_SCRIPT_HANDLE, Qasper_Snippet_Builder::build_boot_js( $cfg ), 'before' );
		wp_enqueue_script( QASPER_BOOKING_SCRIPT_HANDLE );
	}
}
