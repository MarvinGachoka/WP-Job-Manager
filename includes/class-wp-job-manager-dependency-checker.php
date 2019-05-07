<?php
/**
 * File containing the class WP_Job_Manager_Dependency_Checker.
 *
 * @package wp-job-manager
 * @since   1.33.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles checking for WP Job Manager's dependencies.
 *
 * @since 1.33.0
 */
class WP_Job_Manager_Dependency_Checker {
	const MINIMUM_PHP_VERSION = '5.6.20';
	const MINIMUM_WP_VERSION  = '4.7.0';

	/**
	 * Check if WP Job Manager's dependencies have been met.
	 *
	 * @return bool True if we should continue to load the plugin.
	 */
	public static function check_dependencies() {
		if ( self::check_php() ) {
			add_action( 'admin_notices', array( 'WP_Job_Manager_Dependency_Checker', 'add_php_notice' ) );
		}

		return true;
	}

	/**
	 * Checks for our PHP version requirement.
	 *
	 * @return bool
	 */
	private static function check_php() {
		return version_compare( phpversion(), self::MINIMUM_PHP_VERSION, '>=' );
	}

	/**
	 * Adds notice in WP Admin that minimum version of PHP is not met.
	 *
	 * @access private
	 */
	public static function add_php_notice() {
		$screen        = get_current_screen();
		$valid_screens = array( 'dashboard', 'plugins', 'plugins-network', 'edit-job_listing', 'job_listing_page_job-manager-settings' );

		if ( ! current_user_can( 'activate_plugins' ) || ! in_array( $screen->id, $valid_screens, true ) ) {
			return;
		}

		// translators: %1$s is version of PHP that WP Job Manager requires; %2$s is the version of PHP WordPress is running on.
		$message = sprintf( __( 'The next release of <strong>WP Job Manager</strong> will require a minimum PHP version of %1$s, but you are running %2$s. Please update PHP to continue using this plugin.', 'wp-job-manager' ), self::MINIMUM_PHP_VERSION, phpversion() );

		echo '<div class="error"><p>';
		echo wp_kses( $message, array( 'strong' => array() ) );
		$php_update_url = 'https://wordpress.org/support/update-php/';
		if ( function_exists( 'wp_get_update_php_url' ) ) {
			$php_update_url = wp_get_update_php_url();
		}
		printf(
			'<p><a class="button button-primary" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a></p>',
			esc_url( $php_update_url ),
			esc_html__( 'Learn more about updating PHP', 'wp-job-manager' ),
			/* translators: accessibility text */
			esc_html__( '(opens in a new tab)', 'wp-job-manager' )
		);
		echo '</p></div>';
	}


	/**
	 * Checks for our PHP version requirement.
	 *
	 * @return bool
	 */
	public static function check_wp() {
		global $wp_version;

		return version_compare( $wp_version, self::MINIMUM_PHP_VERSION, '>=' );
	}

	/**
	 * Adds notice in WP Admin that minimum version of WordPress is not met.
	 *
	 * @access private
	 */
	public static function add_wp_notice() {
		// We only want to show the notices on the plugins page and WPJM admin pages.
		$screen        = get_current_screen();
		$valid_screens = array( 'dashboard', 'plugins', 'plugins-network', 'edit-job_listing', 'job_listing_page_job-manager-settings' );
		if ( null === $screen || ! in_array( $screen->id, $valid_screens, true ) ) {
			return;
		}

		echo '<div class="error">';
		// translators: %s is the URL for the page where users can go to update WordPress.
		echo '<p>' . wp_kses_post( sprintf( __( '<strong>WP Job Manager</strong> requires a more recent version of WordPress. <a href="%s">Please update WordPress</a> to avoid issues.', 'wp-job-manager' ), esc_url( self_admin_url( 'update-core.php' ) ) ) ) . '</p>';
		echo '</div>';
	}

	/**
	 * Add admin notice when WP upgrade is required.
	 *
	 * @param array $actions
	 * @return array
	 */
	public function wp_version_plugin_action_notice( $actions ) {
		// translators: Placeholder (%s) is the URL where users can go to update WordPress.
		$actions[] = wp_kses_post( sprintf( __( '<a href="%s" style="color: red">WordPress Update Required</a>', 'wp-job-manager' ), esc_url( self_admin_url( 'update-core.php' ) ) ) );
		return $actions;
	}
}
