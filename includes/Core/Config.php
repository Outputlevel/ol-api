<?php
/**
 * OL-API Configuration Manager
 *
 * @package OL_API\Core
 * @since 1.0.0
 */

namespace OL_API\Core;

/**
 * Config Class
 * 
 * Manages plugin configuration from various sources.
 * Loads settings from WordPress options, config files, and environment.
 * 
 * Responsibilities:
 * - Load configuration from multiple sources
 * - Provide type-safe access to settings
 * - Cache configuration values
 * - Support defaults and fallback values
 * 
 * @since 1.0.0
 */
class Config {

	/**
	 * Configuration values cache
	 * 
	 * @var array<string, mixed>
	 */
	private array $config = [];

	/**
	 * Configuration loaded flag
	 * 
	 * @var bool
	 */
	private bool $loaded = false;

	/**
	 * Constructor
	 * 
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->load();
	}

	/**
	 * Load configuration from all sources
	 * 
	 * Loads configuration in order:
	 * 1. WordPress options (ol_api_* options)
	 * 2. Config files if they exist
	 * 3. Environment variables
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	private function load(): void {
		if ( $this->loaded ) {
			return;
		}

		// Load from WordPress options
		$this->load_from_options();

		// Load from config files if they exist
		$this->load_from_files();

		$this->loaded = true;
	}

	/**
	 * Load configuration from WordPress options
	 * 
	 * Loads all options with 'ol_api_' prefix into config.
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	private function load_from_options(): void {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$options = $wpdb->get_results(
			"SELECT option_name, option_value FROM $wpdb->options 
			 WHERE option_name LIKE 'ol_api_%'",
			ARRAY_A
		);

		if ( ! empty( $options ) ) {
			foreach ( $options as $option ) {
				$key   = str_replace( 'ol_api_', '', $option['option_name'] );
				$value = maybe_unserialize( $option['option_value'] );
				$this->config[ $key ] = $value;
			}
		}
	}

	/**
	 * Load configuration from config files
	 * 
	 * Loads PHP config files from config directory.
	 * Currently looks for:
	 * - plugin.config.php
	 * - database.config.php (future)
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	private function load_from_files(): void {
		// Future implementation: Load from config files
		// Config files would be in /config directory
	}

	/**
	 * Get configuration value
	 * 
	 * @param string $key     Configuration key (dot notation supported: section.key)
	 * @param mixed  $default Default value if key not found
	 * @return mixed Configuration value or default
	 * @since 1.0.0
	 */
	public function get( string $key, $default = null ) {
		// Support dot notation for nested values
		if ( strpos( $key, '.' ) !== false ) {
			return $this->get_nested( $key, $default );
		}

		return $this->config[ $key ] ?? $default;
	}

	/**
	 * Get nested configuration value
	 * 
	 * Supports dot notation: 'database.host' returns config['database']['host']
	 * 
	 * @param string $key     Configuration key with dot notation
	 * @param mixed  $default Default value
	 * @return mixed Configuration value or default
	 * @since 1.0.0
	 */
	private function get_nested( string $key, $default = null ) {
		$keys = explode( '.', $key );
		$value = $this->config;

		foreach ( $keys as $k ) {
			if ( ! is_array( $value ) || ! isset( $value[ $k ] ) ) {
				return $default;
			}
			$value = $value[ $k ];
		}

		return $value;
	}

	/**
	 * Set configuration value
	 * 
	 * Sets a configuration value. Changes persist if saved to WordPress options.
	 * 
	 * @param string $key   Configuration key
	 * @param mixed  $value Configuration value
	 * @return void
	 * @since 1.0.0
	 */
	public function set( string $key, $value ): void {
		$this->config[ $key ] = $value;
	}

	/**
	 * Save configuration to WordPress options
	 * 
	 * Persists configuration changes to WordPress options.
	 * Prepends 'ol_api_' to key names.
	 * 
	 * @param string $key Configuration key
	 * @return bool True if saved successfully
	 * @since 1.0.0
	 */
	public function save( string $key ): bool {
		if ( ! isset( $this->config[ $key ] ) ) {
			return false;
		}

		$option_name = 'ol_api_' . $key;
		return update_option( $option_name, $this->config[ $key ] );
	}

	/**
	 * Get all configuration
	 * 
	 * @return array<string, mixed> All configuration values
	 * @since 1.0.0
	 */
	public function get_all(): array {
		return $this->config;
	}

	/**
	 * Check if configuration key exists
	 * 
	 * @param string $key Configuration key
	 * @return bool True if key exists
	 * @since 1.0.0
	 */
	public function has( string $key ): bool {
		return isset( $this->config[ $key ] );
	}

	/**
	 * Get boolean configuration value
	 * 
	 * Safely retrieves a boolean configuration value.
	 * 
	 * @param string $key     Configuration key
	 * @param bool   $default Default value
	 * @return bool Configuration value as boolean
	 * @since 1.0.0
	 */
	public function get_bool( string $key, bool $default = false ): bool {
		$value = $this->get( $key, $default );
		return (bool) $value;
	}

	/**
	 * Get integer configuration value
	 * 
	 * Safely retrieves an integer configuration value.
	 * 
	 * @param string $key     Configuration key
	 * @param int    $default Default value
	 * @return int Configuration value as integer
	 * @since 1.0.0
	 */
	public function get_int( string $key, int $default = 0 ): int {
		$value = $this->get( $key, $default );
		return (int) $value;
	}

	/**
	 * Get string configuration value
	 * 
	 * Safely retrieves a string configuration value.
	 * 
	 * @param string $key     Configuration key
	 * @param string $default Default value
	 * @return string Configuration value as string
	 * @since 1.0.0
	 */
	public function get_string( string $key, string $default = '' ): string {
		$value = $this->get( $key, $default );
		return (string) $value;
	}
}
