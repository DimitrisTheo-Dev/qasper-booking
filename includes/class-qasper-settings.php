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

	const PAGE_SLUG              = 'qasper-booking';
	const OPTION_GROUP           = 'qasper_booking_group';
	const ADMIN_CSS_TAG          = 'qasper-booking-admin';
	const ADMIN_JS_TAG           = 'qasper-booking-admin';
	const DEFAULT_EXAMPLE_SLUG   = 'new-york-barber';
	const ACCENT_MODE_DEFAULT    = 'default';
	const ACCENT_MODE_CUSTOM     = 'custom';
	const DEFAULT_PREVIEW_ACCENT = '#EEA563';
	const MAXIMUM_SLUG_LENGTH    = 200;
	const MAXIMUM_LABEL_LENGTH   = 120;

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
				'default'           => self::get_default_settings(),
			)
		);
	}

	/**
	 * Return the settings used before an administrator has saved the form.
	 *
	 * @return array
	 */
	public static function get_default_settings() {
		return array(
			'slug'            => '',
			'default_label'   => 'Chat',
			'position'        => 'right',
			'sitewide'        => true,
			'locale_override' => 'auto',
			'accent'          => '',
			'theme'           => 'system',
		);
	}

	public static function enqueue_admin_assets( $hook ) {
		if ( 'settings_page_' . self::PAGE_SLUG !== $hook ) {
			return;
		}

		wp_enqueue_style(
			self::ADMIN_CSS_TAG,
			QASPER_BOOKING_URL . 'assets/admin.css',
			array(),
			QASPER_BOOKING_VERSION
		);
		wp_enqueue_script(
			self::ADMIN_JS_TAG,
			QASPER_BOOKING_URL . 'assets/admin.js',
			array(),
			QASPER_BOOKING_VERSION,
			true
		);
	}

	/**
	 * Validate and normalize every value submitted through the Settings API.
	 *
	 * @param mixed $input Submitted option value.
	 * @return array
	 */
	public static function sanitize( $input ) {
		$input             = is_array( $input ) ? $input : array();
		$existing_settings = self::get_saved_settings_with_defaults();
		$valid_positions   = array( 'left', 'right' );
		$valid_locales     = array( 'auto', 'en', 'el', 'de', 'es', 'fr', 'it' );
		$raw_slug          = isset( $input['slug'] ) && is_string( $input['slug'] ) ? $input['slug'] : '';
		$slug              = sanitize_text_field( $raw_slug );
		$slug              = strtolower( trim( $slug ) );

		if ( strlen( $slug ) > self::MAXIMUM_SLUG_LENGTH || ! Qasper_Snippet_Builder::is_valid_slug( $slug ) ) {
			add_settings_error(
				QASPER_BOOKING_OPTION_NAME,
				'qasper_booking_invalid_slug',
				__( 'Enter a valid business slug using lowercase letters, numbers, and single hyphens, such as new-york-barber.', 'qasper-booking' ),
				'error'
			);
			return $existing_settings;
		}

		$raw_default_label = isset( $input['default_label'] ) && is_string( $input['default_label'] )
			? $input['default_label']
			: 'Chat';
		$default_label     = sanitize_text_field( $raw_default_label );
		$default_label     = '' !== trim( $default_label ) ? $default_label : 'Chat';
		$default_label     = self::truncate_text( $default_label, self::MAXIMUM_LABEL_LENGTH );
		$position          = isset( $input['position'] ) && in_array( $input['position'], $valid_positions, true )
			? $input['position']
			: 'right';
		$locale            = isset( $input['locale_override'] ) && in_array( $input['locale_override'], $valid_locales, true )
			? $input['locale_override']
			: 'auto';
		$accent_mode       = self::resolve_submitted_accent_mode( $input );
		$accent            = self::sanitize_submitted_accent( $input, $accent_mode, $existing_settings );

		return array(
			'slug'            => $slug,
			'default_label'   => $default_label,
			'position'        => $position,
			'sitewide'        => ! empty( $input['sitewide'] ),
			'locale_override' => $locale,
			'accent'          => $accent,
			'theme'           => Qasper_Snippet_Builder::sanitize_theme(
				isset( $input['theme'] ) ? $input['theme'] : 'system'
			),
		);
	}

	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings               = self::get_saved_settings_with_defaults();
		$slug                   = isset( $settings['slug'] ) ? $settings['slug'] : '';
		$is_configured          = Qasper_Snippet_Builder::is_valid_slug( $slug );
		$sitewide               = $is_configured ? ! empty( $settings['sitewide'] ) : true;
		$accent                 = Qasper_Snippet_Builder::sanitize_accent( $settings['accent'] );
		$accent_mode            = '' !== $accent ? self::ACCENT_MODE_CUSTOM : self::ACCENT_MODE_DEFAULT;
		$theme                  = Qasper_Snippet_Builder::sanitize_theme( $settings['theme'] );
		$shortcode_example_slug = self::get_valid_slug_for_shortcode_examples( $slug );
		$submit_label           = $is_configured
			? __( 'Save Changes', 'qasper-booking' )
			: __( 'Save & Activate Qasper', 'qasper-booking' );
		?>
		<div class="wrap qasper-booking-wrap">
			<h1><?php esc_html_e( 'Qasper Booking', 'qasper-booking' ); ?></h1>

			<?php settings_errors( QASPER_BOOKING_OPTION_NAME ); ?>
			<?php self::render_setup_status( $is_configured, $sitewide ); ?>

			<form
				method="post"
				action="options.php"
				data-qasper-settings-form
				data-saving-label="<?php esc_attr_e( 'Saving…', 'qasper-booking' ); ?>"
			>
				<?php settings_fields( self::OPTION_GROUP ); ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="qasper-slug"><?php esc_html_e( 'Business slug', 'qasper-booking' ); ?></label></th>
						<td>
							<p id="qasper-slug-description" class="description"><?php esc_html_e( 'Your Qasper business slug, for example new-york-barber.', 'qasper-booking' ); ?></p>
							<input
								type="text"
								id="qasper-slug"
								name="<?php echo esc_attr( QASPER_BOOKING_OPTION_NAME ); ?>[slug]"
								value="<?php echo esc_attr( $slug ); ?>"
								class="regular-text"
								autocomplete="off"
								spellcheck="false"
								maxlength="<?php echo esc_attr( self::MAXIMUM_SLUG_LENGTH ); ?>"
								pattern="[A-Za-z0-9]+(-[A-Za-z0-9]+)*"
								autocapitalize="none"
								aria-describedby="qasper-slug-description"
								required
							/>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="qasper-label"><?php esc_html_e( 'Default label', 'qasper-booking' ); ?></label></th>
						<td>
							<p id="qasper-label-description" class="description"><?php esc_html_e( 'Used as the button text and the chat launcher tooltip.', 'qasper-booking' ); ?></p>
							<input
								type="text"
								id="qasper-label"
								name="<?php echo esc_attr( QASPER_BOOKING_OPTION_NAME ); ?>[default_label]"
								value="<?php echo esc_attr( $settings['default_label'] ); ?>"
								class="regular-text"
								autocomplete="off"
								maxlength="<?php echo esc_attr( self::MAXIMUM_LABEL_LENGTH ); ?>"
								aria-describedby="qasper-label-description"
							/>
						</td>
					</tr>

					<tr>
						<th scope="row" id="qasper-brand-color-label"><?php esc_html_e( 'Brand color', 'qasper-booking' ); ?></th>
						<td>
							<fieldset aria-labelledby="qasper-brand-color-label" aria-describedby="qasper-accent-description">
								<label class="qasper-choice-row" for="qasper-accent-mode-default">
									<input
										type="radio"
										id="qasper-accent-mode-default"
										name="<?php echo esc_attr( QASPER_BOOKING_OPTION_NAME ); ?>[accent_mode]"
										value="<?php echo esc_attr( self::ACCENT_MODE_DEFAULT ); ?>"
										<?php checked( self::ACCENT_MODE_DEFAULT, $accent_mode ); ?>
									/>
									<span><?php esc_html_e( 'Use Qasper’s default color', 'qasper-booking' ); ?></span>
								</label>
								<label class="qasper-choice-row" for="qasper-accent-mode-custom">
									<input
										type="radio"
										id="qasper-accent-mode-custom"
										name="<?php echo esc_attr( QASPER_BOOKING_OPTION_NAME ); ?>[accent_mode]"
										value="<?php echo esc_attr( self::ACCENT_MODE_CUSTOM ); ?>"
										<?php checked( self::ACCENT_MODE_CUSTOM, $accent_mode ); ?>
									/>
									<span><?php esc_html_e( 'Use a custom color', 'qasper-booking' ); ?></span>
								</label>
								<div class="qasper-color-picker-row">
									<label for="qasper-accent"><?php esc_html_e( 'Custom color', 'qasper-booking' ); ?></label>
									<input
										type="color"
										id="qasper-accent"
										name="<?php echo esc_attr( QASPER_BOOKING_OPTION_NAME ); ?>[accent]"
										value="<?php echo esc_attr( '' !== $accent ? $accent : self::DEFAULT_PREVIEW_ACCENT ); ?>"
										aria-describedby="qasper-accent-description"
									/>
								</div>
								<p id="qasper-accent-description" class="description"><?php esc_html_e( 'Tints the launcher, links, and booking button. Choose a medium-to-bright color so icons remain legible.', 'qasper-booking' ); ?></p>
							</fieldset>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="qasper-position"><?php esc_html_e( 'Launcher position', 'qasper-booking' ); ?></label></th>
						<td>
							<select id="qasper-position" name="<?php echo esc_attr( QASPER_BOOKING_OPTION_NAME ); ?>[position]" autocomplete="off">
								<option value="right" <?php selected( $settings['position'], 'right' ); ?>><?php esc_html_e( 'Bottom right', 'qasper-booking' ); ?></option>
								<option value="left" <?php selected( $settings['position'], 'left' ); ?>><?php esc_html_e( 'Bottom left', 'qasper-booking' ); ?></option>
							</select>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="qasper-theme"><?php esc_html_e( 'Widget theme', 'qasper-booking' ); ?></label></th>
						<td>
							<select id="qasper-theme" name="<?php echo esc_attr( QASPER_BOOKING_OPTION_NAME ); ?>[theme]" autocomplete="off">
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
							<select id="qasper-locale" name="<?php echo esc_attr( QASPER_BOOKING_OPTION_NAME ); ?>[locale_override]" autocomplete="off">
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
								foreach ( $locales as $code => $label ) {
									printf(
										'<option value="%s" %s>%s</option>',
										esc_attr( $code ),
										selected( $settings['locale_override'], $code, false ),
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
							<label for="qasper-sitewide">
								<input
									type="checkbox"
									id="qasper-sitewide"
									name="<?php echo esc_attr( QASPER_BOOKING_OPTION_NAME ); ?>[sitewide]"
									value="1"
									<?php checked( $sitewide ); ?>
								/>
								<?php esc_html_e( 'Show the floating chat launcher on every public page.', 'qasper-booking' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'Saving refreshes WordPress and WP Super Cache. If visitors still see an older page, also purge your host or CDN cache.', 'qasper-booking' ); ?></p>
						</td>
					</tr>
				</table>

				<?php submit_button( $submit_label ); ?>
			</form>

			<h2><?php esc_html_e( 'Shortcodes', 'qasper-booking' ); ?></h2>
			<ul class="qasper-shortcodes">
				<li><code translate="no">[qasper_button slug="<?php echo esc_html( $shortcode_example_slug ); ?>" label="Book now" channel_source="wordpress_site" accent="#eea563"]</code> &mdash; <?php esc_html_e( 'render a booking link button.', 'qasper-booking' ); ?></li>
				<li><code translate="no">[qasper_chat slug="<?php echo esc_html( $shortcode_example_slug ); ?>" label="Chat with us" position="right" channel_source="wordpress_site" accent="#eea563" theme="dark"]</code> &mdash; <?php esc_html_e( 'render the floating chat launcher on one page only.', 'qasper-booking' ); ?></li>
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

	/**
	 * Infer the submitted color mode, including the 1.0.x checkbox contract.
	 *
	 * @param array $input Submitted settings.
	 * @return string
	 */
	private static function resolve_submitted_accent_mode( $input ) {
		if ( isset( $input['accent_mode'] ) && self::ACCENT_MODE_CUSTOM === $input['accent_mode'] ) {
			return self::ACCENT_MODE_CUSTOM;
		}

		if ( isset( $input['accent_mode'] ) || ! empty( $input['accent_default'] ) ) {
			return self::ACCENT_MODE_DEFAULT;
		}

		return self::ACCENT_MODE_CUSTOM;
	}

	/**
	 * Sanitize the custom accent without letting a malformed request erase it.
	 *
	 * @param array  $input Submitted settings.
	 * @param string $accent_mode Selected color mode.
	 * @param array  $existing_settings Previously saved settings.
	 * @return string
	 */
	private static function sanitize_submitted_accent( $input, $accent_mode, $existing_settings ) {
		if ( self::ACCENT_MODE_DEFAULT === $accent_mode ) {
			return '';
		}

		$accent = Qasper_Snippet_Builder::sanitize_accent( isset( $input['accent'] ) ? $input['accent'] : '' );
		if ( '' !== $accent ) {
			return $accent;
		}

		add_settings_error(
			QASPER_BOOKING_OPTION_NAME,
			'qasper_booking_invalid_accent',
			__( 'Choose a valid custom color or select Qasper’s default color.', 'qasper-booking' ),
			'error'
		);

		return Qasper_Snippet_Builder::sanitize_accent( $existing_settings['accent'] );
	}

	/**
	 * Merge legacy stored values with current defaults.
	 *
	 * @return array
	 */
	private static function get_saved_settings_with_defaults() {
		$saved_settings = get_option( QASPER_BOOKING_OPTION_NAME, array() );
		if ( ! is_array( $saved_settings ) ) {
			$saved_settings = array();
		}

		$settings = array_merge(
			self::get_default_settings(),
			$saved_settings
		);

		$settings['slug']            = is_string( $settings['slug'] ) ? $settings['slug'] : '';
		$settings['default_label']   = is_string( $settings['default_label'] ) && '' !== trim( $settings['default_label'] )
			? self::truncate_text( $settings['default_label'], self::MAXIMUM_LABEL_LENGTH )
			: 'Chat';
		$settings['position']        = in_array( $settings['position'], array( 'left', 'right' ), true )
			? $settings['position']
			: 'right';
		$settings['sitewide']        = ! empty( $settings['sitewide'] );
		$settings['locale_override'] = in_array(
			$settings['locale_override'],
			array( 'auto', 'en', 'el', 'de', 'es', 'fr', 'it' ),
			true
		) ? $settings['locale_override'] : 'auto';
		$settings['accent']          = Qasper_Snippet_Builder::sanitize_accent( $settings['accent'] );
		$settings['theme']           = Qasper_Snippet_Builder::sanitize_theme( $settings['theme'] );

		return $settings;
	}

	/**
	 * Render a textual status that does not rely on color alone.
	 *
	 * @param bool $is_configured Whether a valid slug is saved.
	 * @param bool $sitewide Whether the public site-wide launcher is enabled.
	 */
	private static function render_setup_status( $is_configured, $sitewide ) {
		if ( ! $is_configured ) {
			?>
			<div class="notice notice-info inline qasper-status-notice">
				<p><strong><?php esc_html_e( 'Setup is incomplete.', 'qasper-booking' ); ?></strong> <?php esc_html_e( 'Enter your business slug and save once to activate Qasper for public visitors.', 'qasper-booking' ); ?></p>
			</div>
			<?php
			return;
		}

		if ( ! $sitewide ) {
			?>
			<div class="notice notice-warning inline qasper-status-notice">
				<p><strong><?php esc_html_e( 'Qasper is configured for shortcode placement.', 'qasper-booking' ); ?></strong> <?php esc_html_e( 'Enable site-wide floating chat to show it automatically on every public page.', 'qasper-booking' ); ?></p>
			</div>
			<?php
			return;
		}

		?>
		<div class="notice notice-success inline qasper-status-notice">
			<p>
				<strong><?php esc_html_e( 'Qasper is active site-wide.', 'qasper-booking' ); ?></strong>
				<?php esc_html_e( 'Public visitors should see the launcher on every page.', 'qasper-booking' ); ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener">
					<?php esc_html_e( 'View Site', 'qasper-booking' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( ' (opens in a new tab)', 'qasper-booking' ); ?></span>
				</a>
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

	/**
	 * Truncate administrator-provided text without splitting UTF-8 characters.
	 *
	 * @param string $text Text to truncate.
	 * @param int    $maximum_length Maximum character count.
	 * @return string
	 */
	private static function truncate_text( $text, $maximum_length ) {
		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $text, 0, $maximum_length );
		}

		return substr( $text, 0, $maximum_length );
	}
}
