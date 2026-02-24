<?php
/**
 * OL-API Test Bootstrap
 *
 * @package OL_API\Tests
 * @since 1.0.0
 */

// Prevent WordPress from interfering with tests
define( 'WP_RUN_CORE_TESTS', true );
define( 'WP_DEBUG', true );

// Get the project root directory
$project_root = dirname( __DIR__ );

// Set up required WordPress constants if not already defined
if ( ! defined( 'ABSPATH' ) ) {
	// Find WordPress installation
	$wordpress_path = $project_root;
	while ( ! is_file( $wordpress_path . '/wp-load.php' ) ) {
		$wordpress_path = dirname( $wordpress_path );
		if ( $wordpress_path === dirname( $wordpress_path ) ) {
			// Reached filesystem root without finding WordPress
			die( 'Could not find WordPress installation.' );
		}
	}
	define( 'ABSPATH', $wordpress_path . '/' );
}

// Register PSR-4 autoloader for the plugin
$loader_file = dirname( __DIR__ ) . '/includes/Loader.php';
if ( file_exists( $loader_file ) ) {
	require_once $loader_file;
	\OL_API\Loader::register();
}

// Load WordPress for testing
if ( file_exists( ABSPATH . 'wp-load.php' ) ) {
	require_once ABSPATH . 'wp-load.php';
}

// Load PHPUnit compatibility layer if needed
if ( file_exists( ABSPATH . 'tests/phpunit/includes/functions.php' ) ) {
	require_once ABSPATH . 'tests/phpunit/includes/functions.php';
}

// Set test constants
define( 'OL_API_TESTING', true );
define( 'OL_API_TEST_DIR', dirname( __FILE__ ) );
define( 'OL_API_PLUGIN_DIR', dirname( OL_API_TEST_DIR ) );

/**
 * Mock WordPress functions if not available
 * 
 * This allows unit tests to run without a full WordPress installation.
 */

if ( ! function_exists( 'get_option' ) ) {
	/**
	 * Mock get_option for testing
	 * 
	 * @param string $option Option name
	 * @param mixed  $default Default value
	 * @return mixed Option value
	 */
	function get_option( string $option, $default = false ) {
		global $test_options;
		if ( ! isset( $test_options ) ) {
			$test_options = [];
		}
		return $test_options[ $option ] ?? $default;
	}
}

if ( ! function_exists( 'update_option' ) ) {
	/**
	 * Mock update_option for testing
	 * 
	 * @param string $option Option name
	 * @param mixed  $value Option value
	 * @return bool True
	 */
	function update_option( string $option, $value ): bool {
		global $test_options;
		if ( ! isset( $test_options ) ) {
			$test_options = [];
		}
		$test_options[ $option ] = $value;
		return true;
	}
}

if ( ! function_exists( 'delete_option' ) ) {
	/**
	 * Mock delete_option for testing
	 * 
	 * @param string $option Option name
	 * @return bool True
	 */
	function delete_option( string $option ): bool {
		global $test_options;
		if ( isset( $test_options ) && isset( $test_options[ $option ] ) ) {
			unset( $test_options[ $option ] );
		}
		return true;
	}
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	/**
	 * Mock sanitize_text_field for testing
	 * 
	 * @param string $value Value to sanitize
	 * @return string Sanitized value
	 */
	function sanitize_text_field( string $value ): string {
		return wp_check_plain_text( $value ) ?? trim( $value );
	}
}

if ( ! function_exists( 'sanitize_key' ) ) {
	/**
	 * Mock sanitize_key for testing
	 * 
	 * @param string $value Value to sanitize
	 * @return string Sanitized value
	 */
	function sanitize_key( string $value ): string {
		return strtolower( preg_replace( '/[^a-zA-Z0-9_-]/', '', $value ) );
	}
}

if ( ! function_exists( 'sanitize_title' ) ) {
	/**
	 * Mock sanitize_title for testing
	 * 
	 * @param string $value Value to sanitize
	 * @return string Sanitized value
	 */
	function sanitize_title( string $value ): string {
		return preg_replace( '/[^a-zA-Z0-9_-]/', '', strtolower( $value ) );
	}
}

if ( ! function_exists( 'wp_check_plain_text' ) ) {
	/**
	 * Mock wp_check_plain_text for testing
	 * 
	 * @param string $value Value to check
	 * @return string|null Value or null
	 */
	function wp_check_plain_text( string $value ) {
		return ! empty( $value ) ? $value : null;
	}
}

if ( ! function_exists( 'maybe_unserialize' ) ) {
	/**
	 * Mock maybe_unserialize for testing
	 * 
	 * @param mixed $original Value to possibly unserialize
	 * @return mixed Unserialized value
	 */
	function maybe_unserialize( $original ) {
		if ( is_serialized( $original ) ) {
			return @unserialize( $original );
		}
		return $original;
	}
}

if ( ! function_exists( 'is_serialized' ) ) {
	/**
	 * Mock is_serialized for testing
	 * 
	 * @param mixed $data Data to check
	 * @return bool True if serialized
	 */
	function is_serialized( $data ): bool {
		if ( ! is_string( $data ) ) {
			return false;
		}
		return ( $data === 'b:0;' ) || ( @unserialize( $data ) !== false );
	}
}

if ( ! function_exists( 'do_action' ) ) {
	/**
	 * Mock do_action for testing
	 * 
	 * @param string $tag Action tag
	 * @param mixed  ...$args Action arguments
	 * @return void
	 */
	function do_action( string $tag, ...$args ): void {
		// Mock implementation - no-op for testing
	}
}

// Initialize global $wpdb mock if needed
if ( ! isset( $GLOBALS['wpdb'] ) ) {
	$GLOBALS['wpdb'] = new class {
		public string $prefix = 'wp_';
		
		public function get_charset_collate(): string {
			return 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
		}
	};
}
