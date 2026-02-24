<?php
/**
 * OL-API Plugin Interface
 *
 * @package OL_API
 * @subpackage Core
 * @since 1.0.0
 */

namespace OL_API\Core;

/**
 * Plugin Interface
 * 
 * Defines the contract for the main plugin class
 * 
 * @since 1.0.0
 */
interface PluginInterface {

	/**
	 * Get plugin instance (Singleton pattern)
	 * 
	 * @return self
	 * @since 1.0.0
	 */
	public static function getInstance(): self;

	/**
	 * Plugin activation hook
	 * 
	 * Called when plugin is activated in WordPress admin
	 * Should create database tables, set defaults, etc.
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	public function activate(): void;

	/**
	 * Plugin deactivation hook
	 * 
	 * Called when plugin is deactivated in WordPress admin
	 * Should cleanup transients, clear caches, etc.
	 * NOTE: Does NOT delete tables (data preservation)
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	public function deactivate(): void;

	/**
	 * Register plugin components
	 * 
	 * Called during plugins_loaded hook
	 * Initializes all plugin components
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	public function register(): void;

	/**
	 * Initialize plugin
	 * 
	 * Called during init hook
	 * Performs plugin initialization logic
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	public function init(): void;

	/**
	 * Register REST API routes
	 * 
	 * Called during rest_api_init hook
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	public function register_rest_routes(): void;

	/**
	 * Get plugin name
	 * 
	 * @return string
	 * @since 1.0.0
	 */
	public function get_name(): string;

	/**
	 * Get plugin version
	 * 
	 * @return string
	 * @since 1.0.0
	 */
	public function get_version(): string;

	/**
	 * Get plugin path
	 * 
	 * @return string Absolute path to plugin directory
	 * @since 1.0.0
	 */
	public function get_path(): string;

	/**
	 * Get plugin URL
	 * 
	 * @return string URL to plugin directory
	 * @since 1.0.0
	 */
	public function get_url(): string;
}
