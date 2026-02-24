<?php
/**
 * OL-API Setup Handler
 *
 * @package OL_API
 * @since 1.0.0
 */

namespace OL_API;

use OL_API\Infrastructure\Database\DatabaseManager;

/**
 * Setup Class
 * 
 * Handles plugin activation and deactivation logic.
 * Creates/destroys database tables, sets defaults, manages transients.
 * 
 * Static class - use activate() and deactivate() methods directly
 * 
 * @since 1.0.0
 */
class Setup {

	/**
	 * Plugin activation hook handler
	 * 
	 * Called when plugin is activated.
	 * Creates database tables and sets initial options.
	 * 
	 * Tasks:
	 * - Create custom database tables
	 * - Set default plugin options
	 * - Create necessary folders (logs, cache)
	 * - Trigger setup action hook
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	public static function activate(): void {
		// Verify required WordPress capabilities
		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		// Initialize database manager
		$db_manager = new DatabaseManager();

		// Create database tables
		$db_manager->create_tables();

		// Set default plugin options
		self::set_defaults();

		// Create required directories
		self::create_directories();

		// Store activation timestamp
		update_option( 'ol_api_activated_at', current_time( 'mysql', true ) );

		/**
		 * Fires when plugin is successfully activated
		 * 
		 * @since 1.0.0
		 */
		do_action( 'ol_api_activated' );
	}

	/**
	 * Plugin deactivation hook handler
	 * 
	 * Called when plugin is deactivated.
	 * Clears cache, removes transients, cleans up temporary data.
	 * NOTE: Does NOT delete database tables (data preservation)
	 * 
	 * Tasks:
	 * - Clear all transients
	 * - Remove temporary options
	 * - Trigger deactivation action hook
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	public static function deactivate(): void {
		// Clear all plugin transients
		self::clear_transients();

		// Clear temporary options
		self::clear_temp_options();

		/**
		 * Fires when plugin is successfully deactivated
		 * 
		 * @since 1.0.0
		 */
		do_action( 'ol_api_deactivated' );
	}

	/**
	 * Set default plugin options
	 * 
	 * Initializes WordPress options with plugin defaults.
	 * Only sets if option doesn't already exist.
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	private static function set_defaults(): void {
		$defaults = [
			'ol_api_version'              => '1.0.0-beta.1',
			'ol_api_db_version'           => '1.0.0',
			'ol_api_enabled'              => true,
			'ol_api_require_api_key'      => true,
			'ol_api_api_key_expiry_days'  => 90,
			'ol_api_log_requests'         => true,
			'ol_api_log_retention_days'   => 30,
			'ol_api_cache_ttl'            => 86400, // 24 hours
			'ol_api_max_endpoints'        => 100,
			'ol_api_openapi_auto_refresh' => true,
		];

		foreach ( $defaults as $key => $value ) {
			if ( ! get_option( $key ) ) {
				add_option( $key, $value );
			}
		}
	}

	/**
	 * Create required directories
	 * 
	 * Creates plugin-specific directories for logs, cache, uploads, etc.
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	private static function create_directories(): void {
		$plugin = Plugin::getInstance();
		$dirs   = [
			'logs',
			'cache',
			'uploads',
		];

		foreach ( $dirs as $dir ) {
			$path = $plugin->get_path() . '/' . $dir;
			if ( ! is_dir( $path ) ) {
				wp_mkdir_p( $path );
				// Create .htaccess to prevent direct access
				self::create_htaccess( $path );
			}
		}
	}

	/**
	 * Create .htaccess file in directory
	 * 
	 * Prevents direct HTTP access to sensitive directories.
	 * 
	 * @param string $dir_path Directory path
	 * @return void
	 * @since 1.0.0
	 */
	private static function create_htaccess( string $dir_path ): void {
		$htaccess_file = $dir_path . '/.htaccess';
		if ( ! file_exists( $htaccess_file ) ) {
			$htaccess_content = "Deny from all\n";
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			file_put_contents( $htaccess_file, $htaccess_content );
		}
	}

	/**
	 * Clear all plugin transients
	 * 
	 * Removes all cached data stored via WordPress transients.
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	private static function clear_transients(): void {
		global $wpdb;

		// Delete all transients matching ol_api prefix
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $wpdb->options WHERE option_name LIKE %s",
				'%ol_api_%'
			)
		);
	}

	/**
	 * Clear temporary options
	 * 
	 * Removes temporary plugin options that shouldn't persist.
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	private static function clear_temp_options(): void {
		$temp_options = [
			'ol_api_setup_running',
			'ol_api_last_check',
		];

		foreach ( $temp_options as $option ) {
			delete_option( $option );
		}
	}
}
