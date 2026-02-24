<?php
/**
 * OL-API Settings Repository
 *
 * @package OL_API\Repositories
 * @since 1.0.0
 */

namespace OL_API\Repositories;

/**
 * SettingsRepository Class
 * 
 * Repository for managing plugin settings and configuration.
 * Acts as a proxy to WordPress options while providing type-safe access.
 * 
 * Responsibilities:
 * - Manage plugin settings
 * - Provide typed getters/setters
 * - Cache settings in memory
 * - Handle serialized data
 * 
 * @since 1.0.0
 */
class SettingsRepository {

	/**
	 * Settings cache
	 * 
	 * @var array<string, mixed>
	 */
	private array $cache = [];

	/**
	 * Cache loaded flag
	 * 
	 * @var bool
	 */
	private bool $cache_loaded = false;

	/**
	 * Settings option prefix
	 * 
	 * @var string
	 */
	private const OPTION_PREFIX = 'ol_api_';

	/**
	 * Constructor
	 * 
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->load_cache();
	}

	/**
	 * Load all settings into memory cache
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	private function load_cache(): void {
		if ( $this->cache_loaded ) {
			return;
		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$options = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name, option_value FROM $wpdb->options 
				 WHERE option_name LIKE %s",
				self::OPTION_PREFIX . '%'
			),
			ARRAY_A
		);

		if ( ! empty( $options ) ) {
			foreach ( $options as $option ) {
				$key = str_replace( self::OPTION_PREFIX, '', $option['option_name'] );
				$this->cache[ $key ] = maybe_unserialize( $option['option_value'] );
			}
		}

		$this->cache_loaded = true;
	}

	/**
	 * Get setting value
	 * 
	 * @param string $key Setting key
	 * @param mixed  $default Default value if not found
	 * @return mixed Setting value
	 * @since 1.0.0
	 */
	public function get( string $key, $default = null ) {
		if ( isset( $this->cache[ $key ] ) ) {
			return $this->cache[ $key ];
		}

		$value = get_option( self::OPTION_PREFIX . $key, $default );
		$this->cache[ $key ] = $value;
		return $value;
	}

	/**
	 * Get setting as boolean
	 * 
	 * @param string $key Setting key
	 * @param bool   $default Default value
	 * @return bool Setting value as boolean
	 * @since 1.0.0
	 */
	public function get_bool( string $key, bool $default = false ): bool {
		$value = $this->get( $key, $default );
		return (bool) $value;
	}

	/**
	 * Get setting as integer
	 * 
	 * @param string $key Setting key
	 * @param int    $default Default value
	 * @return int Setting value as integer
	 * @since 1.0.0
	 */
	public function get_int( string $key, int $default = 0 ): int {
		$value = $this->get( $key, $default );
		return (int) $value;
	}

	/**
	 * Get setting as string
	 * 
	 * @param string $key Setting key
	 * @param string $default Default value
	 * @return string Setting value as string
	 * @since 1.0.0
	 */
	public function get_string( string $key, string $default = '' ): string {
		$value = $this->get( $key, $default );
		return (string) $value;
	}

	/**
	 * Get setting as array
	 * 
	 * @param string $key Setting key
	 * @param array<mixed> $default Default value
	 * @return array<mixed> Setting value as array
	 * @since 1.0.0
	 */
	public function get_array( string $key, array $default = [] ): array {
		$value = $this->get( $key, $default );
		return is_array( $value ) ? $value : $default;
	}

	/**
	 * Set setting value
	 * 
	 * @param string $key Setting key
	 * @param mixed  $value Setting value
	 * @return bool True if saved successfully
	 * @since 1.0.0
	 */
	public function set( string $key, $value ): bool {
		$option_key = self::OPTION_PREFIX . $key;
		$result     = update_option( $option_key, $value );

		// Update cache
		$this->cache[ $key ] = $value;

		return $result !== false;
	}

	/**
	 * Delete setting
	 * 
	 * @param string $key Setting key
	 * @return bool True if deleted successfully
	 * @since 1.0.0
	 */
	public function delete( string $key ): bool {
		$option_key = self::OPTION_PREFIX . $key;
		$result     = delete_option( $option_key );

		// Remove from cache
		unset( $this->cache[ $key ] );

		return $result;
	}

	/**
	 * Check if setting exists
	 * 
	 * @param string $key Setting key
	 * @return bool True if setting exists
	 * @since 1.0.0
	 */
	public function has( string $key ): bool {
		return isset( $this->cache[ $key ] ) || 
		       ( false !== get_option( self::OPTION_PREFIX . $key, false ) );
	}

	/**
	 * Get all settings
	 * 
	 * @return array<string, mixed> All settings
	 * @since 1.0.0
	 */
	public function get_all(): array {
		return $this->cache;
	}

	/**
	 * Reset specific setting to default
	 * 
	 * @param string $key Setting key
	 * @param mixed  $default Default value
	 * @return void
	 * @since 1.0.0
	 */
	public function reset( string $key, $default = null ): void {
		$this->delete( $key );
		if ( $default !== null ) {
			$this->set( $key, $default );
		}
	}

	/**
	 * Increment numeric setting
	 * 
	 * Increments a numeric setting value by 1.
	 * 
	 * @param string $key Setting key
	 * @param int    $increment Amount to increment (default: 1)
	 * @return int|false New value or false on failure
	 * @since 1.0.0
	 */
	public function increment( string $key, int $increment = 1 ) {
		$current = $this->get_int( $key, 0 );
		$new_value = $current + $increment;
		return $this->set( $key, $new_value ) ? $new_value : false;
	}

	/**
	 * Get setting with automatic defaults
	 * 
	 * Returns setting value with sensible defaults if not found.
	 * Useful for common plugin settings.
	 * 
	 * @return array<string, mixed> Plugin settings with defaults
	 * @since 1.0.0
	 */
	public function get_config(): array {
		return [
			'enabled'              => $this->get_bool( 'enabled', true ),
			'version'              => $this->get_string( 'version', '1.0.0-beta.1' ),
			'require_api_key'      => $this->get_bool( 'require_api_key', true ),
			'api_key_expiry_days'  => $this->get_int( 'api_key_expiry_days', 90 ),
			'log_requests'         => $this->get_bool( 'log_requests', true ),
			'log_retention_days'   => $this->get_int( 'log_retention_days', 30 ),
			'cache_ttl'            => $this->get_int( 'cache_ttl', 86400 ),
			'max_endpoints'        => $this->get_int( 'max_endpoints', 100 ),
			'openapi_auto_refresh' => $this->get_bool( 'openapi_auto_refresh', true ),
		];
	}

	/**
	 * Clear all cached settings
	 * 
	 * Reloads settings from database on next access.
	 * Useful after external updates to settings.
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	public function clear_cache(): void {
		$this->cache = [];
		$this->cache_loaded = false;
		$this->load_cache();
	}
}
