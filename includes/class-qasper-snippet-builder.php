<?php
/**
 * Pure HTML/JS snippet builders. Mirrors src/lib/embedSnippets.ts so the
 * dashboard and WordPress sides agree on slug validation, escape rules,
 * and the queue-init boot shape.
 *
 * @package Qasper_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Qasper_Snippet_Builder {

	const SUPPORTED_LOCALES = array( 'en', 'el', 'de', 'es', 'fr', 'it' );
	const SUPPORTED_THEMES  = array( 'system', 'light', 'dark' );

	public static function is_valid_slug( $slug ) {
		return is_string( $slug ) && (bool) preg_match( '/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug );
	}

	/**
	 * Validate a business-supplied accent color: `#` followed by exactly 3 or
	 * 6 hex digits. Returns the canonical lowercased hex, or '' for anything
	 * else. Single source of truth for accent validation across the plugin —
	 * mirrors normalizeAccent() in the widget (see CONTRACT.md).
	 */
	public static function sanitize_accent( $raw ) {
		if ( ! is_string( $raw ) ) {
			return '';
		}
		$trimmed = strtolower( trim( $raw ) );
		return preg_match( '/^#(?:[0-9a-f]{3}|[0-9a-f]{6})$/', $trimmed ) ? $trimmed : '';
	}

	public static function normalize_locale( $raw ) {
		if ( ! is_string( $raw ) || '' === $raw ) {
			return 'en';
		}
		$short = strtolower( substr( $raw, 0, 2 ) );
		return in_array( $short, self::SUPPORTED_LOCALES, true ) ? $short : 'en';
	}

	public static function sanitize_theme( $raw ) {
		if ( ! is_string( $raw ) ) {
			return 'system';
		}
		$normalized = strtolower( trim( $raw ) );
		return in_array( $normalized, self::SUPPORTED_THEMES, true ) ? $normalized : 'system';
	}

	/**
	 * Resolve effective locale: explicit override (one of 6) wins over auto.
	 * `auto` falls back to the WordPress site locale.
	 */
	public static function resolve_locale( $locale_override ) {
		if ( in_array( $locale_override, self::SUPPORTED_LOCALES, true ) ) {
			return $locale_override;
		}
		return self::normalize_locale( get_locale() );
	}

	public static function build_button_html( $slug, $label, $locale = 'en', $accent = '' ) {
		if ( ! self::is_valid_slug( $slug ) ) {
			return '';
		}
		$loc  = self::normalize_locale( $locale );
		$href = QASPER_BOOKING_AGENT_URL_BASE . '/' . $slug . '/chat?lang=' . $loc;
		// $bg is a validated hex string (or the brand default); the whole
		// style string is esc_attr'd on output below.
		$bg     = self::sanitize_accent( $accent );
		$bg     = '' !== $bg ? $bg : '#EEA563';
		$styles = 'display:inline-block;padding:10px 18px;border-radius:10px;background:' . $bg . ';color:#121212;font-family:"Plus Jakarta Sans",sans-serif;font-weight:600;font-size:15px;text-decoration:none;line-height:1.2;';
		return sprintf(
			'<a href="%s" style="%s" target="_blank" rel="noopener">%s</a>',
			esc_url( $href ),
			esc_attr( $styles ),
			esc_html( $label )
		);
	}

	/**
	 * Build the synchronous boot script that establishes the queue and
	 * pushes the active widget config. Designed to be passed to
	 * `wp_add_inline_script( handle, $js, 'before' )` — the 'before'
	 * position guarantees this runs prior to the async loader.
	 *
	 * The config is encoded with wp_json_encode() and JSON_HEX_TAG, so the
	 * output can never contain a literal `<` or `>` and cannot break out
	 * of the inline <script> element regardless of the stored label.
	 * wp_json_encode() also escapes non-ASCII to \uXXXX, covering the
	 * U+2028/U+2029 separators that are invalid in JS string literals.
	 * Mirrors escapeJsonForScript() on the backend (see CONTRACT.md).
	 */
	public static function build_boot_js( $cfg ) {
		$stub = 'window.QasperWidget=window.QasperWidget||{q:[],init:function(c){this.q.push(c)}};';
		$json = wp_json_encode( $cfg, JSON_HEX_TAG );
		if ( false === $json ) {
			$json = '{}';
		}
		$init = 'window.QasperWidget.init(' . $json . ');';
		return $stub . $init;
	}
}
