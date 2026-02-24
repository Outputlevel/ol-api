<?php
/**
 * OL-API Main Plugin Class
 *
 * @package OL_API
 * @since 1.0.0
 */

namespace OL_API;

use OL_API\Core\PluginInterface;
use OL_API\Core\Registry;
use OL_API\Core\Config;
use OL_API\Setup;

/**
 * Main Plugin Class
 * 
 * Central coordinator for the OL-API plugin.
 * Implements Singleton pattern.
 * 
 * Responsibilities:
 * - Plugin lifecycle management (activation, deactivation)
 * - Component registration and initialization
 * - WordPress hook registration
 * - Provides access to plugin metadata
 * 
 * @since 1.0.0
 */
class Plugin implements PluginInterface {

	/**
	 * Plugin name
	 * 
	 * @var string
	 */
	private const NAME = 'OL-API';

	/**
	 * Plugin version
	 * 
	 * @var string
	 */
	private const VERSION = '1.0.0-beta.1';

	/**
	 * Plugin text domain for translations
	 * 
	 * @var string
	 */
	private const TEXT_DOMAIN = 'ol-api';

	/**
	 * Singleton instance
	 * 
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Plugin base path
	 * 
	 * @var string
	 */
	private string $path;

	/**
	 * Plugin base URL
	 * 
	 * @var string
	 */
	private string $url;

	/**
	 * Component Registry
	 * 
	 * @var Registry|null
	 */
	private ?Registry $registry = null;

	/**
	 * Plugin Configuration
	 * 
	 * @var Config|null
	 */
	private ?Config $config = null;

	/**
	 * Plugin initialization flag
	 * 
	 * @var bool
	 */
	private bool $initialized = false;

	/**
	 * Private constructor - use getInstance()
	 * 
	 * @param string $plugin_file Absolute path to main plugin file
	 * @since 1.0.0
	 */
	private function __construct( string $plugin_file ) {
		$this->path = dirname( $plugin_file );
		$this->url  = plugins_url( '/', $plugin_file );
	}

	/**
	 * Get singleton instance
	 * 
	 * @param string $plugin_file Absolute path to main plugin file (only on first call)
	 * @return self Plugin instance
	 * @since 1.0.0
	 */
	public static function getInstance( string $plugin_file = '' ): self {
		if ( self::$instance === null ) {
			if ( empty( $plugin_file ) ) {
				throw new \RuntimeException( 'Plugin file is required on first instantiation' );
			}
			self::$instance = new self( $plugin_file );
		}

		return self::$instance;
	}

	/**
	 * Register plugin components
	 * 
	 * Called on plugins_loaded hook.
	 * Registers autoloader and initializes core components.
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	public function register(): void {
		// Register PSR-4 autoloader
		Loader::register();

		// Initialize Registry and Config
		$this->registry = new Registry();
		$this->config   = new Config();

		// Register activation/deactivation hooks
		$plugin_file = $this->path . '/ol-api.php';
		register_activation_hook( $plugin_file, [ $this, 'activate' ] );
		register_deactivation_hook( $plugin_file, [ $this, 'deactivate' ] );

		// Register core WordPress hooks
		add_action( 'init', [ $this, 'init' ], 10 );
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ], 10 );

		/**
		 * Fires after plugin components are registered
		 * 
		 * @since 1.0.0
		 */
		do_action( 'ol_api_loaded' );
	}

	/**
	 * Initialize plugin
	 * 
	 * Called on init hook.
	 * Performs plugin initialization logic.
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	public function init(): void {
		if ( $this->initialized ) {
			return;
		}

		$this->initialized = true;

		// Future: Initialize database, load settings, etc.
		// This will be expanded in subsequent sprints

		/**
		 * Fires after plugin is initialized
		 * 
		 * @since 1.0.0
		 */
		do_action( 'ol_api_init' );
	}

	/**
	 * Register REST API routes
	 * 
	 * Called on rest_api_init hook.
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	public function register_rest_routes(): void {
		// Future: Register API routes
		// This will be implemented in Sprint 1.3

		/**
		 * Fires after REST API routes are registered
		 * 
		 * @since 1.0.0
		 */
		do_action( 'ol_api_rest_api_init' );
	}

	/**
	 * Plugin activation hook
	 * 
	 * Called when plugin is activated in WordPress admin.
	 * Creates database tables, sets defaults, etc.
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	public function activate(): void {
		Setup::activate();
	}

	/**
	 * Plugin deactivation hook
	 * 
	 * Called when plugin is deactivated in WordPress admin.
	 * Cleans up transients, clears caches, etc.
	 * NOTE: Does NOT delete tables (data preservation)
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	public function deactivate(): void {
		Setup::deactivate();
	}

	/**
	 * Get plugin name
	 * 
	 * @return string
	 * @since 1.0.0
	 */
	public function get_name(): string {
		return self::NAME;
	}

	/**
	 * Get plugin version
	 * 
	 * @return string
	 * @since 1.0.0
	 */
	public function get_version(): string {
		return self::VERSION;
	}

	/**
	 * Get plugin text domain
	 * 
	 * @return string
	 * @since 1.0.0
	 */
	public function get_text_domain(): string {
		return self::TEXT_DOMAIN;
	}

	/**
	 * Get plugin base path
	 * 
	 * @return string Absolute path to plugin directory
	 * @since 1.0.0
	 */
	public function get_path(): string {
		return $this->path;
	}

	/**
	 * Get plugin base URL
	 * 
	 * @return string URL to plugin directory
	 * @since 1.0.0
	 */
	public function get_url(): string {
		return $this->url;
	}

	/**
	 * Get component registry
	 * 
	 * @return Registry|null
	 * @since 1.0.0
	 */
	public function get_registry(): ?Registry {
		return $this->registry;
	}

	/**
	 * Get configuration
	 * 
	 * @return Config|null
	 * @since 1.0.0
	 */
	public function get_config(): ?Config {
		return $this->config;
	}
}
