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
				'slug'           => '',
				'label'          => '',
				'accent'         => '',
				'channel_source' => Qasper_Snippet_Builder::WORDPRESS_CHANNEL_SOURCE,
			),
			$atts,
			'qasper_button'
		);

		$settings       = self::settings();
		$slug           = $atts['slug'] !== '' ? $atts['slug'] : ( isset( $settings['slug'] ) ? $settings['slug'] : '' );
		$label          = self::resolve_label( $atts['label'], $settings, __( 'Book', 'qasper-booking' ) );
		$locale         = Qasper_Snippet_Builder::resolve_locale( isset( $settings['locale_override'] ) ? $settings['locale_override'] : 'auto' );
		$accent_source  = $atts['accent'] !== '' ? $atts['accent'] : ( isset( $settings['accent'] ) ? $settings['accent'] : '' );
		$accent         = Qasper_Snippet_Builder::sanitize_accent( $accent_source );
		$channel_source = Qasper_Snippet_Builder::normalize_channel_source( $atts['channel_source'] );

		return Qasper_Snippet_Builder::build_button_html( $slug, $label, $locale, $accent, $channel_source );
	}

	public static function render_chat( $atts ) {
		$atts = shortcode_atts(
			array(
				'slug'           => '',
				'label'          => '',
				'position'       => '',
				'accent'         => '',
				'theme'          => '',
				'channel_source' => Qasper_Snippet_Builder::WORDPRESS_CHANNEL_SOURCE,
			),
			$atts,
			'qasper_chat'
		);

		$settings = self::settings();
		$slug     = $atts['slug'] !== '' ? $atts['slug'] : ( isset( $settings['slug'] ) ? $settings['slug'] : '' );
		$label    = self::resolve_label( $atts['label'], $settings, __( 'Chat', 'qasper-booking' ) );

		$position_input = $atts['position'] !== '' ? $atts['position'] : ( isset( $settings['position'] ) ? $settings['position'] : 'right' );
		$position       = in_array( $position_input, array( 'left', 'right' ), true ) ? $position_input : 'right';

		$locale         = Qasper_Snippet_Builder::resolve_locale( isset( $settings['locale_override'] ) ? $settings['locale_override'] : 'auto' );
		$accent_source  = $atts['accent'] !== '' ? $atts['accent'] : ( isset( $settings['accent'] ) ? $settings['accent'] : '' );
		$accent         = Qasper_Snippet_Builder::sanitize_accent( $accent_source );
		$theme_source   = $atts['theme'] !== '' ? $atts['theme'] : ( isset( $settings['theme'] ) ? $settings['theme'] : 'system' );
		$theme          = Qasper_Snippet_Builder::sanitize_theme( $theme_source );
		$channel_source = Qasper_Snippet_Builder::normalize_channel_source( $atts['channel_source'] );

		if ( ! Qasper_Snippet_Builder::is_valid_slug( $slug ) ) {
			return '';
		}

		$cfg = array(
			'slug'          => $slug,
			'mode'          => 'floating',
			'position'      => $position,
			'label'         => $label,
			'locale'        => $locale,
			'channelSource' => $channel_source,
		);
		if ( '' !== $accent ) {
			$cfg['accent'] = $accent;
		}
		if ( 'system' !== $theme ) {
			$cfg['theme'] = $theme;
		}

		wp_register_script( QASPER_BOOKING_SCRIPT_HANDLE, QASPER_BOOKING_WIDGET_SCRIPT, array(), QASPER_BOOKING_VERSION, true );
		wp_add_inline_script( QASPER_BOOKING_SCRIPT_HANDLE, Qasper_Snippet_Builder::build_boot_js( $cfg ), 'before' );
		wp_enqueue_script( QASPER_BOOKING_SCRIPT_HANDLE );

		return '';
	}

	/**
	 * Resolve a shortcode or saved launcher label to a safe string value.
	 *
	 * @param mixed  $shortcode_label Shortcode label.
	 * @param array  $settings Saved plugin settings.
	 * @param string $fallback_label Fallback label.
	 * @return string
	 */
	private static function resolve_label( $shortcode_label, $settings, $fallback_label ) {
		if ( is_string( $shortcode_label ) && '' !== trim( $shortcode_label ) ) {
			return $shortcode_label;
		}

		if ( isset( $settings['default_label'] ) && is_string( $settings['default_label'] ) && '' !== trim( $settings['default_label'] ) ) {
			return $settings['default_label'];
		}

		return $fallback_label;
	}
}
