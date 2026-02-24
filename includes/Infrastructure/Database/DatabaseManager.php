<?php
/**
 * OL-API Database Manager
 *
 * @package OL_API\Infrastructure\Database
 * @since 1.0.0
 */

namespace OL_API\Infrastructure\Database;

/**
 * DatabaseManager Class
 * 
 * Manages all database operations for the plugin.
 * Inherits wp_query capabilities via $wpdb global.
 * 
 * Responsibilities:
 * - Create and manage custom database tables
 * - Handle database versioning and migrations
 * - Provide helper methods for common operations
 * - Log database errors
 * 
 * @since 1.0.0
 */
class DatabaseManager {

	/**
	 * Current database version
	 * 
	 * @var string
	 */
	private const DB_VERSION = '1.0.0';

	/**
	 * WordPress database object
	 * 
	 * @var \wpdb
	 */
	private \wpdb $wpdb;

	/**
	 * Tables instance
	 * 
	 * @var Tables
	 */
	private Tables $tables;

	/**
	 * Constructor
	 * 
	 * @since 1.0.0
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb   = $wpdb;
		$this->tables = new Tables();
	}

	/**
	 * Create all custom database tables
	 * 
	 * Executes SQL to create all plugin tables.
	 * Uses dbDelta to handle upgrades safely.
	 * 
	 * Tables created:
	 * - ol_api_endpoints
	 * - ol_api_endpoint_fields
	 * - ol_api_api_keys
	 * - ol_api_tokens
	 * - ol_api_permissions
	 * - ol_api_logs
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	public function create_tables(): void {
		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		// Get all table schemas
		$sql = $this->tables->get_schema_sql();

		// Execute with dbDelta for safe schema updates
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		dbDelta( $sql );

		// Update database version
		update_option( 'ol_api_db_version', self::DB_VERSION );
	}

	/**
	 * Get table prefix
	 * 
	 * Returns the full table name with WordPress prefix.
	 * 
	 * @param string $table_name Table name without prefix
	 * @return string Full table name with prefix
	 * @since 1.0.0
	 */
	public function get_table_name( string $table_name ): string {
		return $this->wpdb->prefix . $table_name;
	}

	/**
	 * Insert record into table
	 * 
	 * Safe wrapper for inserting data into custom tables.
	 * 
	 * @param string       $table_name Table name (without prefix)
	 * @param array<string, mixed> $data Data to insert
	 * @param array<string, string>|null $format Data format specifiers
	 * @return int|false Insert ID or false on failure
	 * @since 1.0.0
	 */
	public function insert( string $table_name, array $data, ?array $format = null ) {
		$table = $this->get_table_name( $table_name );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $this->wpdb->insert( $table, $data, $format );

		if ( false === $result ) {
			$this->log_error( "Insert failed for table $table_name", $this->wpdb->last_error );
			return false;
		}

		return $this->wpdb->insert_id;
	}

	/**
	 * Update records in table
	 * 
	 * Safe wrapper for updating data in custom tables.
	 * 
	 * @param string       $table_name Table name (without prefix)
	 * @param array<string, mixed> $data Data to update
	 * @param array<string, mixed> $where WHERE clause conditions
	 * @param array<string, string>|null $data_format Data format specifiers
	 * @param array<string, string>|null $where_format WHERE format specifiers
	 * @return int|false Number of rows updated or false on failure
	 * @since 1.0.0
	 */
	public function update( 
		string $table_name, 
		array $data, 
		array $where, 
		?array $data_format = null, 
		?array $where_format = null 
	) {
		$table = $this->get_table_name( $table_name );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $this->wpdb->update( $table, $data, $where, $data_format, $where_format );

		if ( false === $result ) {
			$this->log_error( "Update failed for table $table_name", $this->wpdb->last_error );
			return false;
		}

		return $result;
	}

	/**
	 * Delete records from table
	 * 
	 * Safe wrapper for deleting data from custom tables.
	 * 
	 * @param string       $table_name Table name (without prefix)
	 * @param array<string, mixed> $where WHERE clause conditions
	 * @param array<string, string>|null $where_format WHERE format specifiers
	 * @return int|false Number of rows deleted or false on failure
	 * @since 1.0.0
	 */
	public function delete( 
		string $table_name, 
		array $where, 
		?array $where_format = null 
	) {
		$table = $this->get_table_name( $table_name );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $this->wpdb->delete( $table, $where, $where_format );

		if ( false === $result ) {
			$this->log_error( "Delete failed for table $table_name", $this->wpdb->last_error );
			return false;
		}

		return $result;
	}

	/**
	 * Query records from table
	 * 
	 * Helper method for SELECT queries on custom tables.
	 * 
	 * @param string       $table_name Table name (without prefix)
	 * @param string|null  $select Columns to select (default: *)
	 * @param array<string, mixed>|null $where WHERE conditions
	 * @param string       $output Output type (ARRAY_A, ARRAY_N, OBJECT, OBJECT_K)
	 * @return mixed Query results
	 * @since 1.0.0
	 */
	public function get_results( 
		string $table_name, 
		?string $select = null, 
		?array $where = null, 
		string $output = ARRAY_A 
	) {
		$table   = $this->get_table_name( $table_name );
		$select  = $select ?? '*';
		$sql     = "SELECT $select FROM $table";

		if ( ! empty( $where ) ) {
			$sql .= ' WHERE ' . implode( ' AND ', array_map(
				fn( $key ) => "$key = %s",
				array_keys( $where )
			) );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
			return $this->wpdb->get_results(
				$this->wpdb->prepare( $sql, array_values( $where ) ),
				$output
			);
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
		return $this->wpdb->get_results( $sql, $output );
	}

	/**
	 * Get single row from table
	 * 
	 * Helper method for SELECT with LIMIT 1.
	 * 
	 * @param string       $table_name Table name (without prefix)
	 * @param array<string, mixed> $where WHERE conditions
	 * @param string       $output Output type
	 * @return array|object|null Single row result or null
	 * @since 1.0.0
	 */
	public function get_row( 
		string $table_name, 
		array $where, 
		string $output = ARRAY_A 
	) {
		$results = $this->get_results( $table_name, null, $where, $output );
		return ! empty( $results ) ? reset( $results ) : null;
	}

	/**
	 * Get single value from table
	 * 
	 * Helper method for SELECT with LIMIT 1 for a single column.
	 * 
	 * @param string       $table_name Table name (without prefix)
	 * @param string       $column Column name to select
	 * @param array<string, mixed> $where WHERE conditions
	 * @return string|int|null Single column value or null
	 * @since 1.0.0
	 */
	public function get_var( 
		string $table_name, 
		string $column, 
		array $where 
	) {
		$row = $this->get_row( $table_name, $where, ARRAY_A );
		return ! empty( $row ) && isset( $row[ $column ] ) ? $row[ $column ] : null;
	}

	/**
	 * Log database error
	 * 
	 * Logs database errors for debugging.
	 * Future: Implement proper logging to separate log file.
	 * 
	 * @param string $message Error message
	 * @param string $error_detail Error details
	 * @return void
	 * @since 1.0.0
	 */
	private function log_error( string $message, string $error_detail ): void {
		// Future: Implement proper error logging
		// For now, errors are available in WordPress debug log if enabled
		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			error_log( "[OL-API DB] $message: $error_detail" );
		}
	}

	/**
	 * Get database version
	 * 
	 * @return string Database version
	 * @since 1.0.0
	 */
	public function get_version(): string {
		return self::DB_VERSION;
	}
}
