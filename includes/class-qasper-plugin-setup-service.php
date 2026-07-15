<?php
/**
 * Activation guidance and plugin-list actions.
 *
 * @package Qasper_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Qasper_Plugin_Setup_Service {

	const SETUP_NOTICE_OPTION = 'qasper_booking_show_setup_notice';

	/**
	 * Record a one-time setup notice and clear stale public caches.
	 *
	 * @param bool $network_wide Whether the plugin was network activated.
	 */
	public static function handle_activation( $network_wide ) {
		if ( ! $network_wide ) {
			update_option( self::SETUP_NOTICE_OPTION, '1', false );
		}

		Qasper_Cache_Service::purge_public_page_caches();
	}

	/**
	 * Clear public caches when the widget is deactivated.
	 */
	public static function handle_deactivation() {
		Qasper_Cache_Service::purge_public_page_caches();
	}

	/**
	 * Add a direct Settings link to the Plugins screen.
	 *
	 * @param array $links Existing plugin action links.
	 * @return array
	 */
	public static function add_settings_action_link( $links ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $links;
		}

		$settings_url  = admin_url( 'options-general.php?page=' . Qasper_Settings::PAGE_SLUG );
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( $settings_url ),
			esc_html__( 'Settings', 'qasper-booking' )
		);

		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Show setup guidance once, to an administrator, after activation.
	 */
	public static function render_setup_notice() {
		if ( ! current_user_can( 'manage_options' ) || '1' !== get_option( self::SETUP_NOTICE_OPTION ) ) {
			return;
		}

		$settings_url = admin_url( 'options-general.php?page=' . Qasper_Settings::PAGE_SLUG );
		?>
		<div class="notice notice-info is-dismissible">
			<p>
				<strong><?php esc_html_e( 'Finish setting up Qasper Booking.', 'qasper-booking' ); ?></strong>
				<?php esc_html_e( 'Add your business slug, review the appearance, then save once to activate Qasper for public visitors.', 'qasper-booking' ); ?>
				<a href="<?php echo esc_url( $settings_url ); ?>"><?php esc_html_e( 'Open Qasper Settings', 'qasper-booking' ); ?></a>
			</p>
		</div>
		<?php
		delete_option( self::SETUP_NOTICE_OPTION );
	}
}
