<?php
/**
 * Settings → Qasper Booking admin page.
 *
 * @package Qasper_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Qasper_Settings {

	const PAGE_SLUG            = 'qasper-booking';
	const OPTION_GROUP         = 'qasper_booking_group';
	const ADMIN_CSS_TAG        = 'qasper-booking-admin';
	const DEFAULT_EXAMPLE_SLUG = 'new-york-barber';

	public static function register_menu() {
		add_options_page(
			__( 'Qasper Booking', 'qasper-booking' ),
			__( 'Qasper Booking', 'qasper-booking' ),
			'manage_options',
			self::PAGE_SLUG,
			array( __CLASS__, 'render_page' )
		);
	}

	public static function register_settings() {
		register_setting(
			self::OPTION_GROUP,
			QASPER_BOOKING_OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => array(
					'slug'            => '',
					'default_label'   => 'Chat',
					'mode'            => 'floating',
					'position'        => 'right',
					'sitewide'        => false,
					'locale_override' => 'auto',
					'accent'          => '',
					'theme'           => 'system',
				),
			)
		);
	}

	public static function enqueue_admin_assets( $hook ) {
		if ( $hook !== 'settings_page_' . self::PAGE_SLUG ) {
			return;
		}
		wp_enqueue_style( self::ADMIN_CSS_TAG, QASPER_BOOKING_URL . 'assets/admin.css', array(), QASPER_BOOKING_VERSION );
	}

	public static function sanitize( $input ) {
		$valid_modes     = array( 'button', 'chat', 'floating' );
		$valid_positions = array( 'left', 'right' );
		$valid_locales   = array( 'auto', 'en', 'el', 'de', 'es', 'fr', 'it' );

		$raw_slug = isset( $input['slug'] ) ? sanitize_text_field( $input['slug'] ) : '';

		// The "Use Qasper's default color" checkbox, when ticked, stores an
		// empty accent so the widget falls back to its built-in default.
		$accent = empty( $input['accent_default'] )
			? Qasper_Snippet_Builder::sanitize_accent( isset( $input['accent'] ) ? $input['accent'] : '' )
			: '';

		return array(
			'slug'            => $raw_slug,
			'default_label'   => isset( $input['default_label'] ) ? sanitize_text_field( $input['default_label'] ) : 'Chat',
			'mode'            => ( isset( $input['mode'] ) && in_array( $input['mode'], $valid_modes, true ) ) ? $input['mode'] : 'floating',
			'position'        => ( isset( $input['position'] ) && in_array( $input['position'], $valid_positions, true ) ) ? $input['position'] : 'right',
			'sitewide'        => ! empty( $input['sitewide'] ),
			'locale_override' => ( isset( $input['locale_override'] ) && in_array( $input['locale_override'], $valid_locales, true ) ) ? $input['locale_override'] : 'auto',
			'accent'          => $accent,
			'theme'           => Qasper_Snippet_Builder::sanitize_theme( isset( $input['theme'] ) ? $input['theme'] : 'system' ),
		);
	}

	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$settings  = (array) get_option( QASPER_BOOKING_OPTION_NAME, array() );
		$slug      = isset( $settings['slug'] ) ? $settings['slug'] : '';
		$slug_ok   = '' === $slug || Qasper_Snippet_Builder::is_valid_slug( $slug );
		$accent    = Qasper_Snippet_Builder::sanitize_accent( isset( $settings['accent'] ) ? $settings['accent'] : '' );
		$accent_on = '' !== $accent;
		$theme     = Qasper_Snippet_Builder::sanitize_theme( isset( $settings['theme'] ) ? $settings['theme'] : 'system' );
		$shortcode_example_slug = self::get_valid_slug_for_shortcode_examples( $slug );
		?>
		<div class="wrap qasper-booking-wrap">
			<h1><?php esc_html_e( 'Qasper Booking', 'qasper-booking' ); ?></h1>

			<?php if ( ! $slug_ok ) : ?>
				<div class="notice notice-error">
					<p>
						<?php esc_html_e( 'Slug must be lowercase letters, digits, and single hyphens (e.g. new-york-barber).', 'qasper-booking' ); ?>
					</p>
				</div>
			<?php endif; ?>

			<form method="post" action="options.php">
				<?php settings_fields( self::OPTION_GROUP ); ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="qasper-slug"><?php esc_html_e( 'Business slug', 'qasper-booking' ); ?></label></th>
						<td>
							<input type="text" id="qasper-slug" name="<?php echo esc_attr( QASPER_BOOKING_OPTION_NAME ); ?>[slug]" value="<?php echo esc_attr( $slug ); ?>" class="regular-text" autocomplete="off" spellcheck="false" />
							<p class="description"><?php esc_html_e( 'Your Qasper business slug, e.g. new-york-barber.', 'qasper-booking' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="qasper-label"><?php esc_html_e( 'Default label', 'qasper-booking' ); ?></label></th>
						<td>
							<input type="text" id="qasper-label" name="<?php echo esc_attr( QASPER_BOOKING_OPTION_NAME ); ?>[default_label]" value="<?php echo esc_attr( isset( $settings['default_label'] ) ? $settings['default_label'] : 'Chat' ); ?>" class="regular-text" />
							<p class="description"><?php esc_html_e( 'Used as the button text and the chat launcher tooltip.', 'qasper-booking' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Brand color', 'qasper-booking' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( QASPER_BOOKING_OPTION_NAME ); ?>[accent_default]" value="1" <?php checked( ! $accent_on ); ?> />
								<?php esc_html_e( "Use Qasper's default color", 'qasper-booking' ); ?>
							</label>
							<p>
								<label for="qasper-accent"><?php esc_html_e( 'Or pick your brand color:', 'qasper-booking' ); ?></label>
								<input type="color" id="qasper-accent" name="<?php echo esc_attr( QASPER_BOOKING_OPTION_NAME ); ?>[accent]" value="<?php echo esc_attr( $accent_on ? $accent : '#EEA563' ); ?>" />
							</p>
							<p class="description"><?php esc_html_e( 'Tints the chat icon, the send button, links, and the booking button. Pick a medium-to-bright color so text and icons stay legible.', 'qasper-booking' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="qasper-position"><?php esc_html_e( 'Launcher position', 'qasper-booking' ); ?></label></th>
						<td>
							<select id="qasper-position" name="<?php echo esc_attr( QASPER_BOOKING_OPTION_NAME ); ?>[position]">
								<option value="right" <?php selected( isset( $settings['position'] ) ? $settings['position'] : 'right', 'right' ); ?>><?php esc_html_e( 'Bottom right', 'qasper-booking' ); ?></option>
								<option value="left" <?php selected( isset( $settings['position'] ) ? $settings['position'] : 'right', 'left' ); ?>><?php esc_html_e( 'Bottom left', 'qasper-booking' ); ?></option>
							</select>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="qasper-theme"><?php esc_html_e( 'Widget theme', 'qasper-booking' ); ?></label></th>
						<td>
							<select id="qasper-theme" name="<?php echo esc_attr( QASPER_BOOKING_OPTION_NAME ); ?>[theme]">
								<option value="system" <?php selected( $theme, 'system' ); ?>><?php esc_html_e( 'System preference', 'qasper-booking' ); ?></option>
								<option value="light" <?php selected( $theme, 'light' ); ?>><?php esc_html_e( 'Light', 'qasper-booking' ); ?></option>
								<option value="dark" <?php selected( $theme, 'dark' ); ?>><?php esc_html_e( 'Dark', 'qasper-booking' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'Applies to the floating chat widget and its embedded conversation. Booking buttons keep the current page theme.', 'qasper-booking' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="qasper-locale"><?php esc_html_e( 'Locale', 'qasper-booking' ); ?></label></th>
						<td>
							<select id="qasper-locale" name="<?php echo esc_attr( QASPER_BOOKING_OPTION_NAME ); ?>[locale_override]">
								<?php
								$locales = array(
									'auto' => __( 'Auto-detect from WordPress', 'qasper-booking' ),
									'en'   => __( 'English', 'qasper-booking' ),
									'el'   => __( 'Greek (Ελληνικά)', 'qasper-booking' ),
									'de'   => __( 'German (Deutsch)', 'qasper-booking' ),
									'es'   => __( 'Spanish (Español)', 'qasper-booking' ),
									'fr'   => __( 'French (Français)', 'qasper-booking' ),
									'it'   => __( 'Italian (Italiano)', 'qasper-booking' ),
								);
								$current = isset( $settings['locale_override'] ) ? $settings['locale_override'] : 'auto';
								foreach ( $locales as $code => $label ) {
									printf(
										'<option value="%s" %s>%s</option>',
										esc_attr( $code ),
										selected( $current, $code, false ),
										esc_html( $label )
									);
								}
								?>
							</select>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Site-wide floating chat', 'qasper-booking' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( QASPER_BOOKING_OPTION_NAME ); ?>[sitewide]" value="1" <?php checked( ! empty( $settings['sitewide'] ) ); ?> />
								<?php esc_html_e( 'Show the floating chat launcher on every public page.', 'qasper-booking' ); ?>
							</label>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>

			<h2><?php esc_html_e( 'Shortcodes', 'qasper-booking' ); ?></h2>
			<ul class="qasper-shortcodes">
				<li><code>[qasper_button slug="<?php echo esc_html( $shortcode_example_slug ); ?>" label="Book now" accent="#eea563"]</code> &mdash; <?php esc_html_e( 'render a booking link button.', 'qasper-booking' ); ?></li>
				<li><code>[qasper_chat slug="<?php echo esc_html( $shortcode_example_slug ); ?>" label="Chat with us" position="right" accent="#eea563" theme="dark"]</code> &mdash; <?php esc_html_e( 'render the floating chat launcher on one page only.', 'qasper-booking' ); ?></li>
			</ul>

			<h2><?php esc_html_e( 'Privacy notice', 'qasper-booking' ); ?></h2>
			<p class="qasper-privacy">
				<?php
				esc_html_e(
					'When this widget loads, it fetches a small script from qasper.ai. The script does not collect personal data or set cookies. When a visitor clicks the launcher, an iframe loads chat content from qasper.ai. Add qasper.ai to your privacy policy and, if you use a cookie banner, list it under the categories your visitors must consent to.',
					'qasper-booking'
				);
				?>
			</p>
		</div>
		<?php
	}

	private static function get_valid_slug_for_shortcode_examples( $slug ) {
		if ( Qasper_Snippet_Builder::is_valid_slug( $slug ) ) {
			return $slug;
		}

		return self::DEFAULT_EXAMPLE_SLUG;
	}
}
