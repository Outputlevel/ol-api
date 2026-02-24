<?php
/**
 * OL-API Loader Test
 *
 * @package OL_API\Tests\Unit\Core
 * @since 1.0.0
 */

namespace OL_API\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use OL_API\Loader;

/**
 * LoaderTest Class
 * 
 * Unit tests for the PSR-4 autoloader.
 * 
 * @since 1.0.0
 */
class LoaderTest extends TestCase {

	/**
	 * Test autoloader registration
	 * 
	 * @test
	 * @return void
	 * @since 1.0.0
	 */
	public function test_register(): void {
		// Get existing autoloaders before registration
		$before = spl_autoload_functions();

		// Register our loader
		Loader::register();

		// Get autoloaders after registration
		$after = spl_autoload_functions();

		// Should have one more autoloader
		$this->assertGreaterThan( count( $before ), count( $after ) );
	}

	/**
	 * Test class path resolution
	 * 
	 * @test
	 * @return void
	 * @since 1.0.0
	 */
	public function test_get_path_resolution(): void {
		$loader = new \ReflectionClass( Loader::class );
		$method = $loader->getMethod( 'get_path' );
		$method->setAccessible( true );

		// Test path conversion
		$path = $method->invokeArgs( null, [ 'OL_API\\Core\\Plugin' ] );

		// Should convert backslashes to forward slashes or directory separators
		$this->assertStringContainsString( 'Core' . DIRECTORY_SEPARATOR . 'Plugin.php', $path );
	}

	/**
	 * Test loading non-OL_API class returns false
	 * 
	 * @test
	 * @return void
	 * @since 1.0.0
	 */
	public function test_load_non_ol_api_class_returns_false(): void {
		$loader = new \ReflectionClass( Loader::class );
		$method = $loader->getMethod( 'load' );
		$method->setAccessible( true );

		// Should return false for non-OL_API classes
		$result = $method->invokeArgs( null, [ 'SomeOtherNamespace\\Class' ] );
		$this->assertFalse( $result );
	}

	/**
	 * Test NAMESPACE constant is correct
	 * 
	 * @test
	 * @return void
	 * @since 1.0.0
	 */
	public function test_namespace_constant(): void {
		// Use reflection to access private constant
		$loader = new \ReflectionClass( Loader::class );
		$constant = $loader->getConstant( 'NAMESPACE' );

		$this->assertEquals( 'OL_API\\', $constant );
	}

	/**
	 * Test BASE_PATH constant is set
	 * 
	 * @test
	 * @return void
	 * @since 1.0.0
	 */
	public function test_base_path_constant(): void {
		$loader = new \ReflectionClass( Loader::class );
		$constant = $loader->getConstant( 'BASE_PATH' );

		// Should be a valid path
		$this->assertNotEmpty( $constant );
		$this->assertIsString( $constant );
	}
}
