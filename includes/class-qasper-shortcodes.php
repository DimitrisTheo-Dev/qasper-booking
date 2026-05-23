<?php
/**
 * Registers `[qasper_button]` and `[qasper_chat]` shortcodes.
 *
 * The chat shortcode does NOT echo a `<script>` tag (Plugin Check rejects
 * inline `<script>` from shortcode output). It instead enqueues the widget
 * script and pushes the config via wp_add_inline_script('before').
 *
 * @package Qasper_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Qasper_Shortcodes {

	public static function register() {
		add_shortcode( 'qasper_button', array( __CLASS__, 'render_button' ) );
		add_shortcode( 'qasper_chat', array( __CLASS__, 'render_chat' ) );
	}

	private static function settings() {
		return (array) get_option( QASPER_BOOKING_OPTION_NAME, array() );
	}

	public static function render_button( $atts ) {
		$atts = shortcode_atts(
			array(
				'slug'  => '',
				'label' => '',
			),
			$atts,
			'qasper_button'
		);

		$settings = self::settings();
		$slug     = $atts['slug'] !== '' ? $atts['slug'] : ( isset( $settings['slug'] ) ? $settings['slug'] : '' );
		$label    = $atts['label'] !== '' ? $atts['label'] : ( isset( $settings['default_label'] ) && $settings['default_label'] !== '' ? $settings['default_label'] : __( 'Book', 'qasper-booking' ) );
		$locale   = Qasper_Snippet_Builder::resolve_locale( isset( $settings['locale_override'] ) ? $settings['locale_override'] : 'auto' );
		$accent   = Qasper_Snippet_Builder::sanitize_accent( isset( $settings['accent'] ) ? $settings['accent'] : '' );

		return Qasper_Snippet_Builder::build_button_html( $slug, $label, $locale, $accent );
	}

	public static function render_chat( $atts ) {
		$atts = shortcode_atts(
			array(
				'slug'     => '',
				'label'    => '',
				'position' => '',
			),
			$atts,
			'qasper_chat'
		);

		$settings = self::settings();
		$slug     = $atts['slug'] !== '' ? $atts['slug'] : ( isset( $settings['slug'] ) ? $settings['slug'] : '' );
		$label    = $atts['label'] !== '' ? $atts['label'] : ( isset( $settings['default_label'] ) && $settings['default_label'] !== '' ? $settings['default_label'] : __( 'Chat', 'qasper-booking' ) );

		$position_input = $atts['position'] !== '' ? $atts['position'] : ( isset( $settings['position'] ) ? $settings['position'] : 'right' );
		$position       = in_array( $position_input, array( 'left', 'right' ), true ) ? $position_input : 'right';

		$locale = Qasper_Snippet_Builder::resolve_locale( isset( $settings['locale_override'] ) ? $settings['locale_override'] : 'auto' );
		$accent = Qasper_Snippet_Builder::sanitize_accent( isset( $settings['accent'] ) ? $settings['accent'] : '' );

		if ( ! Qasper_Snippet_Builder::is_valid_slug( $slug ) ) {
			return '';
		}

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

		return '';
	}
}
