<?php
/**
 * OL-API Registry Test
 *
 * @package OL_API\Tests\Unit\Core
 * @since 1.0.0
 */

namespace OL_API\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use OL_API\Core\Registry;

/**
 * RegistryTest Class
 * 
 * Unit tests for the component registry.
 * 
 * @since 1.0.0
 */
class RegistryTest extends TestCase {

	/**
	 * Registry instance
	 * 
	 * @var Registry
	 */
	private Registry $registry;

	/**
	 * Set up test
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->registry = new Registry();
	}

	/**
	 * Test registering a component
	 * 
	 * @test
	 * @return void
	 * @since 1.0.0
	 */
	public function test_register_component(): void {
		$this->registry->register( 'test', '\stdClass' );
		$this->assertTrue( $this->registry->is_registered( 'test' ) );
	}

	/**
	 * Test registering duplicate component throws exception
	 * 
	 * @test
	 * @return void
	 * @since 1.0.0
	 */
	public function test_register_duplicate_throws_exception(): void {
		$this->expectException( \RuntimeException::class );
		$this->registry->register( 'test', '\stdClass' );
		$this->registry->register( 'test', '\ArrayObject' );
	}

	/**
	 * Test getting registered component
	 * 
	 * @test
	 * @return void
	 * @since 1.0.0
	 */
	public function test_get_registered_component(): void {
		$this->registry->register( 'test', '\stdClass' );
		$component = $this->registry->get( 'test' );

		$this->assertInstanceOf( '\stdClass', $component );
	}

	/**
	 * Test getting unregistered component throws exception
	 * 
	 * @test
	 * @return void
	 * @since 1.0.0
	 */
	public function test_get_unregistered_throws_exception(): void {
		$this->expectException( \RuntimeException::class );
		$this->registry->get( 'nonexistent' );
	}

	/**
	 * Test singleton behavior
	 * 
	 * @test
	 * @return void
	 * @since 1.0.0
	 */
	public function test_singleton_behavior(): void {
		$this->registry->singleton( 'singleton', '\stdClass' );

		$instance1 = $this->registry->get( 'singleton' );
		$instance2 = $this->registry->get( 'singleton' );

		// Should be the same instance
		$this->assertSame( $instance1, $instance2 );
	}

	/**
	 * Test factory behavior
	 * 
	 * @test
	 * @return void
	 * @since 1.0.0
	 */
	public function test_factory_behavior(): void {
		$this->registry->factory( 'factory', '\stdClass' );

		$instance1 = $this->registry->get( 'factory' );
		$instance2 = $this->registry->get( 'factory' );

		// Should be different instances
		$this->assertNotSame( $instance1, $instance2 );
	}

	/**
	 * Test callable resolver
	 * 
	 * @test
	 * @return void
	 * @since 1.0.0
	 */
	public function test_callable_resolver(): void {
		$this->registry->register( 'test', function( $registry ) {
			return new \stdClass();
		} );

		$component = $this->registry->get( 'test' );
		$this->assertInstanceOf( '\stdClass', $component );
	}

	/**
	 * Test aliasing
	 * 
	 * @test
	 * @return void
	 * @since 1.0.0
	 */
	public function test_alias(): void {
		$this->registry->register( 'test', '\stdClass' );
		$this->registry->alias( 'alias', 'test' );

		$component1 = $this->registry->get( 'test' );
		$component2 = $this->registry->get( 'alias' );

		// Singletons should be the same
		$this->assertSame( $component1, $component2 );
	}

	/**
	 * Test clear
	 * 
	 * @test
	 * @return void
	 * @since 1.0.0
	 */
	public function test_clear(): void {
		$this->registry->register( 'test', '\stdClass' );
		$this->assertTrue( $this->registry->is_registered( 'test' ) );

		$this->registry->clear();
		$this->assertFalse( $this->registry->is_registered( 'test' ) );
	}

	/**
	 * Test get_all_names
	 * 
	 * @test
	 * @return void
	 * @since 1.0.0
	 */
	public function test_get_all_names(): void {
		$this->registry->register( 'test1', '\stdClass' );
		$this->registry->register( 'test2', '\ArrayObject' );

		$names = $this->registry->get_all_names();

		$this->assertContains( 'test1', $names );
		$this->assertContains( 'test2', $names );
	}
}
