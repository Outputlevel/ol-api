<?php
/**
 * OL-API Base Repository
 *
 * @package OL_API\Repositories
 * @since 1.0.0
 */

namespace OL_API\Repositories;

use OL_API\Infrastructure\Database\DatabaseManager;

/**
 * BaseRepository Abstract Class
 * 
 * Abstract base class for all repository implementations.
 * Provides common data access patterns using Repository pattern.
 * 
 * Responsibilities:
 * - Encapsulate data access logic
 * - Provide common CRUD operations
 * - Handle query building helpers
 * - Manage model hydration
 * 
 * Child classes should:
 * - Define $table property pointing to custom table
 * - Implement model-specific methods
 * - Override CRUD methods if needed for custom logic
 * 
 * @since 1.0.0
 */
abstract class BaseRepository {

	/**
	 * Table name (without prefix)
	 * 
	 * Should be overridden in child classes
	 * 
	 * @var string
	 */
	protected string $table = '';

	/**
	 * Database manager instance
	 * 
	 * @var DatabaseManager
	 */
	protected DatabaseManager $db;

	/**
	 * Primary key column name
	 * 
	 * @var string
	 */
	protected string $primary_key = 'id';

	/**
	 * Constructor
	 * 
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->db = new DatabaseManager();
	}

	/**
	 * Find record by primary key
	 * 
	 * @param int|string $id Primary key value
	 * @return array<string, mixed>|null Record data or null if not found
	 * @since 1.0.0
	 */
	public function find( $id ): ?array {
		if ( empty( $this->table ) ) {
			throw new \RuntimeException( 'Table name not defined in repository' );
		}

		return $this->db->get_row(
			$this->table,
			[ $this->primary_key => $id ],
			ARRAY_A
		);
	}

	/**
	 * Find record by custom condition
	 * 
	 * @param array<string, mixed> $where WHERE conditions
	 * @return array<string, mixed>|null Record data or null if not found
	 * @since 1.0.0
	 */
	public function find_by( array $where ): ?array {
		return $this->db->get_row( $this->table, $where, ARRAY_A );
	}

	/**
	 * Find all records
	 * 
	 * @return array<array<string, mixed>> Array of records
	 * @since 1.0.0
	 */
	public function find_all(): array {
		$results = $this->db->get_results( $this->table );
		return (array) $results;
	}

	/**
	 * Find records by condition
	 * 
	 * @param array<string, mixed> $where WHERE conditions
	 * @return array<array<string, mixed>> Array of records
	 * @since 1.0.0
	 */
	public function find_where( array $where ): array {
		if ( empty( $where ) ) {
			return $this->find_all();
		}

		$results = $this->db->get_results( $this->table, null, $where );
		return (array) $results;
	}

	/**
	 * Create new record
	 * 
	 * @param array<string, mixed> $data Record data
	 * @return int|false Insert ID or false on failure
	 * @since 1.0.0
	 */
	public function create( array $data ) {
		// Sanitize data
		$data = $this->sanitize( $data );

		// Validate data
		if ( ! $this->validate( $data ) ) {
			return false;
		}

		return $this->db->insert( $this->table, $data );
	}

	/**
	 * Update existing record
	 * 
	 * @param int|string       $id   Primary key value
	 * @param array<string, mixed> $data Data to update
	 * @return int|false Number of rows updated or false on failure
	 * @since 1.0.0
	 */
	public function update( $id, array $data ) {
		// Sanitize data
		$data = $this->sanitize( $data );

		// Validate data
		if ( ! $this->validate( $data ) ) {
			return false;
		}

		return $this->db->update(
			$this->table,
			$data,
			[ $this->primary_key => $id ]
		);
	}

	/**
	 * Delete record
	 * 
	 * @param int|string $id Primary key value
	 * @return int|false Number of rows deleted or false on failure
	 * @since 1.0.0
	 */
	public function delete( $id ) {
		return $this->db->delete(
			$this->table,
			[ $this->primary_key => $id ]
		);
	}

	/**
	 * Delete records by condition
	 * 
	 * @param array<string, mixed> $where WHERE conditions
	 * @return int|false Number of rows deleted or false on failure
	 * @since 1.0.0
	 */
	public function delete_where( array $where ) {
		return $this->db->delete( $this->table, $where );
	}

	/**
	 * Count records
	 * 
	 * @param array<string, mixed>|null $where Optional WHERE conditions
	 * @return int Record count
	 * @since 1.0.0
	 */
	public function count( ?array $where = null ): int {
		if ( empty( $where ) ) {
			$results = $this->db->get_results( $this->table, 'COUNT(*) as count' );
		} else {
			$results = $this->db->get_results( $this->table, 'COUNT(*) as count', $where );
		}

		if ( ! empty( $results ) ) {
			return (int) $results[0]['count'];
		}

		return 0;
	}

	/**
	 * Check if record exists
	 * 
	 * @param array<string, mixed> $where WHERE conditions
	 * @return bool True if record exists
	 * @since 1.0.0
	 */
	public function exists( array $where ): bool {
		return ! empty( $this->find_by( $where ) );
	}

	/**
	 * Sanitize data
	 * 
	 * Override in child classes to implement custom sanitization.
	 * By default, applies WordPress sanitization functions.
	 * 
	 * @param array<string, mixed> $data Data to sanitize
	 * @return array<string, mixed> Sanitized data
	 * @since 1.0.0
	 */
	protected function sanitize( array $data ): array {
		return array_map(
			fn( $value ) => is_string( $value ) ? sanitize_text_field( $value ) : $value,
			$data
		);
	}

	/**
	 * Validate data
	 * 
	 * Override in child classes to implement custom validation.
	 * By default, returns true (no validation).
	 * 
	 * @param array<string, mixed> $data Data to validate
	 * @return bool True if valid
	 * @since 1.0.0
	 */
	protected function validate( array $data ): bool {
		return true;
	}

	/**
	 * Get table name with prefix
	 * 
	 * @return string Full table name
	 * @since 1.0.0
	 */
	protected function get_table_name(): string {
		return $this->db->get_table_name( $this->table );
	}

	/**
	 * Get database manager instance
	 * 
	 * For direct database operations if needed.
	 * 
	 * @return DatabaseManager Database manager
	 * @since 1.0.0
	 */
	protected function get_db(): DatabaseManager {
		return $this->db;
	}
}
