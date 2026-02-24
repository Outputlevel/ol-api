<?php
/**
 * OL-API - REST API without Code
 *
 * Plugin Name:       OL-API
 * Plugin URI:        https://github.com/you/ol-api
 * Description:       Transform WordPress into a configurable REST API without touching code
 * Version:           1.0.0-beta.1
 * Author:            Your Name
 * Author URI:        https://example.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ol-api
 * Domain Path:       /languages
 * Requires at least: 5.9
 * Requires PHP:      8.0
 *
 * @package OL_API
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define plugin constants
if ( ! defined( 'OL_API_VERSION' ) ) {
	define( 'OL_API_VERSION', '1.0.0-beta.1' );
}

if ( ! defined( 'OL_API_PLUGIN_FILE' ) ) {
	define( 'OL_API_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'OL_API_PLUGIN_DIR' ) ) {
	define( 'OL_API_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'OL_API_PLUGIN_URL' ) ) {
	define( 'OL_API_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'OL_API_INCLUDES_DIR' ) ) {
	define( 'OL_API_INCLUDES_DIR', OL_API_PLUGIN_DIR . 'includes/' );
}

// Check PHP version
if ( version_compare( PHP_VERSION, '8.0', '<' ) ) {
	add_action( 'admin_notices', function() {
		?>
		<div class="notice notice-error is-dismissible">
			<p>
				<?php
				printf(
					/* translators: %s: Required PHP version */
					esc_html__( 'OL-API requires PHP 8.0 or higher. You are using PHP %s', 'ol-api' ),
					esc_html( PHP_VERSION )
				);
				?>
			</p>
		</div>
		<?php
	} );
	return;
}

// Load the PSR-4 autoloader
require_once OL_API_INCLUDES_DIR . 'Loader.php';
\OL_API\Loader::register();

// Get plugin instance and register it
try {
	$plugin = \OL_API\Plugin::getInstance( __FILE__ );
	$plugin->register();
} catch ( \Exception $e ) {
	// Log initialization error
	if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		error_log( 'OL-API initialization error: ' . $e->getMessage() );
	}
}

/**
 * Plugin activation hook
 * 
 * @since 1.0.0
 */
function ol_api_activate() {
	try {
		$plugin = \OL_API\Plugin::getInstance();
		$plugin->activate();
	} catch ( \Exception $e ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( 'OL-API activation failed: ' . $e->getMessage() );
	}
}
register_activation_hook( __FILE__, 'ol_api_activate' );

/**
 * Plugin deactivation hook
 * 
 * @since 1.0.0
 */
function ol_api_deactivate() {
	try {
		$plugin = \OL_API\Plugin::getInstance();
		$plugin->deactivate();
	} catch ( \Exception $e ) {
		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			error_log( 'OL-API deactivation error: ' . $e->getMessage() );
		}
	}
}
register_deactivation_hook( __FILE__, 'ol_api_deactivate' );
