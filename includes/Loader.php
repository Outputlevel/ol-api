<?php
/**
 * OL-API Plugin Autoloader (PSR-4)
 *
 * @package OL_API
 * @subpackage Core
 * @since 1.0.0
 */

namespace OL_API;

/**
 * PSR-4 Autoloader for OL-API Plugin
 * 
 * Handles automatic loading of classes following PSR-4 standard:
 * Namespace: OL_API\{Module}\{Class}
 * Path: includes/{Module}/{Class}.php
 * 
 * @since 1.0.0
 */
class Loader {

	/**
	 * Base namespace for plugin classes
	 * 
	 * @var string
	 */
	private const NAMESPACE = 'OL_API\\';

	/**
	 * Base path for plugin classes
	 * 
	 * @var string
	 */
	private const BASE_PATH = __DIR__;

	/**
	 * Register PSR-4 autoloader
	 * 
	 * Called during plugin initialization to register the autoloader function
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	public static function register(): void {
		spl_autoload_register( [ self::class, 'load' ] );
	}

	/**
	 * Load class file based on PSR-4 namespace
	 * 
	 * Examples:
	 * OL_API\Core\Plugin → includes/Core/Plugin.php
	 * OL_API\Infrastructure\Database\DatabaseManager → includes/Infrastructure/Database/DatabaseManager.php
	 * OL_API\Traits\SingletonTrait → includes/Traits/SingletonTrait.php
	 * 
	 * @param string $class Fully qualified class name
	 * @return bool True if class loaded, false otherwise
	 * @since 1.0.0
	 */
	public static function load( string $class ): bool {
		// Check if class belongs to OL_API namespace
		if ( strpos( $class, self::NAMESPACE ) !== 0 ) {
			return false;
		}

		// Remove namespace prefix and convert to file path
		$relative_class = substr( $class, strlen( self::NAMESPACE ) );
		$file_path      = self::BASE_PATH . DIRECTORY_SEPARATOR . str_replace( '\\', DIRECTORY_SEPARATOR, $relative_class ) . '.php';

		// Load file if exists
		if ( file_exists( $file_path ) ) {
			require_once $file_path;
			return true;
		}

		return false;
	}

	/**
	 * Get absolute path for a class file
	 * 
	 * Useful for debugging or file operations
	 * 
	 * @param string $class Fully qualified class name
	 * @return string|null Absolute file path or null if not found
	 * @since 1.0.0
	 */
	public static function get_path( string $class ): ?string {
		if ( strpos( $class, self::NAMESPACE ) !== 0 ) {
			return null;
		}

		$relative_class = substr( $class, strlen( self::NAMESPACE ) );
		$file_path      = self::BASE_PATH . DIRECTORY_SEPARATOR . str_replace( '\\', DIRECTORY_SEPARATOR, $relative_class ) . '.php';

		return file_exists( $file_path ) ? $file_path : null;
	}
}
