<?php
/**
 * OL-API Database Manager Test
 *
 * @package OL_API\Tests\Unit\Infrastructure
 * @since 1.0.0
 */

namespace OL_API\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;
use OL_API\Infrastructure\Database\DatabaseManager;
use OL_API\Infrastructure\Database\Tables;

/**
 * DatabaseManagerTest Class
 * 
 * Unit tests for database management.
 * 
 * Note: These tests are basic structure validation.
 * Full integration tests with database would require database setup.
 * 
 * @since 1.0.0
 */
class DatabaseManagerTest extends TestCase {

	/**
	 * Database manager instance
	 * 
	 * @var DatabaseManager
	 */
	private DatabaseManager $manager;

	/**
	 * Set up test
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new DatabaseManager();
	}

	/**
	 * Test database manager instantiation
	 * 
	 * @test
	 * @return void
	 * @since 1.0.0
	 */
	public function test_instantiation(): void {
		$this->assertInstanceOf( DatabaseManager::class, $this->manager );
	}

	/**
	 * Test get_table_name includes prefix
	 * 
	 * @test
	 * @return void
	 * @since 1.0.0
	 */
	public function test_get_table_name_includes_prefix(): void {
		$table_name = $this->manager->get_table_name( 'ol_api_endpoints' );

		// Should include database prefix
		global $wpdb;
		$this->assertStringContainsString( $wpdb->prefix, $table_name );
		$this->assertStringContainsString( 'ol_api_endpoints', $table_name );
	}

	/**
	 * Test get_version returns version string
	 * 
	 * @test
	 * @return void
	 * @since 1.0.0
	 */
	public function test_get_version(): void {
		$version = $this->manager->get_version();

		$this->assertIsString( $version );
		$this->assertNotEmpty( $version );
		$this->assertEquals( '1.0.0', $version );
	}

	/**
	 * Test Tables class instantiation
	 * 
	 * @test
	 * @return void
	 * @since 1.0.0
	 */
	public function test_tables_instantiation(): void {
		$tables = new Tables();
		$this->assertInstanceOf( Tables::class, $tables );
	}

	/**
	 * Test get_schema_sql returns valid SQL
	 * 
	 * @test
	 * @return void
	 * @since 1.0.0
	 */
	public function test_get_schema_sql_returns_sql(): void {
		$tables = new Tables();
		$sql    = $tables->get_schema_sql();

		$this->assertIsString( $sql );
		$this->assertNotEmpty( $sql );

		// Should contain CREATE TABLE statements
		$this->assertStringContainsString( 'CREATE TABLE', $sql );
	}

	/**
	 * Test all expected tables are in schema
	 * 
	 * @test
	 * @return void
	 * @since 1.0.0
	 */
	public function test_schema_contains_all_tables(): void {
		$tables = new Tables();
		$sql    = $tables->get_schema_sql();

		$expected_tables = [
			'ol_api_endpoints',
			'ol_api_endpoint_fields',
			'ol_api_api_keys',
			'ol_api_tokens',
			'ol_api_permissions',
			'ol_api_logs',
		];

		foreach ( $expected_tables as $table ) {
			$this->assertStringContainsString( $table, $sql );
		}
	}

	/**
	 * Test get_table_schema returns individual table SQL
	 * 
	 * @test
	 * @return void
	 * @since 1.0.0
	 */
	public function test_get_table_schema(): void {
		$tables = new Tables();
		$sql    = $tables->get_table_schema( 'ol_api_endpoints' );

		$this->assertIsString( $sql );
		$this->assertStringContainsString( 'CREATE TABLE', $sql );
		$this->assertStringContainsString( 'ol_api_endpoints', $sql );
	}

	/**
	 * Test get_table_schema returns null for invalid table
	 * 
	 * @test
	 * @return void
	 * @since 1.0.0
	 */
	public function test_get_table_schema_invalid_returns_null(): void {
		$tables = new Tables();
		$sql    = $tables->get_table_schema( 'invalid_table' );

		$this->assertNull( $sql );
	}
}
