<?php
/**
 * OL-API Database Tables Schema
 *
 * @package OL_API\Infrastructure\Database
 * @since 1.0.0
 */

namespace OL_API\Infrastructure\Database;

/**
 * Tables Class
 * 
 * Defines schema SQL for all plugin database tables.
 * Uses WordPress dbDelta format for safe schema management.
 * 
 * Tables:
 * 1. ol_api_endpoints - REST endpoint definitions
 * 2. ol_api_endpoint_fields - Fields within endpoints
 * 3. ol_api_api_keys - API key management
 * 4. ol_api_tokens - Authentication tokens
 * 5. ol_api_permissions - Permission mappings
 * 6. ol_api_logs - Request/response logs
 * 
 * @since 1.0.0
 */
class Tables {

	/**
	 * Get complete schema SQL for all tables
	 * 
	 * Returns SQL for all plugin tables.
	 * Compatible with WordPress dbDelta function.
	 * Each table includes:
	 * - Proper charset and collation
	 * - Indexed columns for performance
	 * - Data types matching use cases
	 * - Created/modified timestamps
	 * 
	 * @return string Complete SQL schema
	 * @since 1.0.0
	 */
	public function get_schema_sql(): string {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "
		-- OL-API Endpoints Table
		CREATE TABLE {$wpdb->prefix}ol_api_endpoints (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			name varchar(120) NOT NULL,
			description longtext,
			post_type varchar(20) NOT NULL DEFAULT 'post',
			enabled tinyint(1) NOT NULL DEFAULT 1,
			require_api_key tinyint(1) NOT NULL DEFAULT 1,
			rate_limit_per_minute int(11),
			documentation longtext,
			created_at datetime DEFAULT current_timestamp,
			updated_at datetime DEFAULT current_timestamp ON UPDATE current_timestamp,
			PRIMARY KEY (id),
			UNIQUE KEY name (name),
			KEY post_type (post_type),
			KEY enabled (enabled)
		) $charset_collate;

		-- OL-API Endpoint Fields Table
		CREATE TABLE {$wpdb->prefix}ol_api_endpoint_fields (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			endpoint_id bigint(20) unsigned NOT NULL,
			field_name varchar(120) NOT NULL,
			field_type varchar(50) NOT NULL,
			field_label varchar(120),
			is_required tinyint(1) NOT NULL DEFAULT 0,
			is_searchable tinyint(1) NOT NULL DEFAULT 0,
			is_sortable tinyint(1) NOT NULL DEFAULT 0,
			is_filterable tinyint(1) NOT NULL DEFAULT 0,
			show_in_response tinyint(1) NOT NULL DEFAULT 1,
			meta_key varchar(120),
			created_at datetime DEFAULT current_timestamp,
			updated_at datetime DEFAULT current_timestamp ON UPDATE current_timestamp,
			PRIMARY KEY (id),
			KEY endpoint_id (endpoint_id),
			KEY field_name (field_name),
			FOREIGN KEY (endpoint_id) REFERENCES {$wpdb->prefix}ol_api_endpoints (id) ON DELETE CASCADE
		) $charset_collate;

		-- OL-API API Keys Table
		CREATE TABLE {$wpdb->prefix}ol_api_api_keys (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			key_hash varchar(255) NOT NULL,
			name varchar(120) NOT NULL,
			description longtext,
			user_id bigint(20) unsigned,
			is_active tinyint(1) NOT NULL DEFAULT 1,
			last_used_at datetime,
			expires_at datetime,
			created_at datetime DEFAULT current_timestamp,
			updated_at datetime DEFAULT current_timestamp ON UPDATE current_timestamp,
			PRIMARY KEY (id),
			UNIQUE KEY key_hash (key_hash),
			KEY user_id (user_id),
			KEY is_active (is_active),
			KEY expires_at (expires_at)
		) $charset_collate;

		-- OL-API Tokens Table (for session/JWT tokens)
		CREATE TABLE {$wpdb->prefix}ol_api_tokens (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			token_hash varchar(255) NOT NULL,
			api_key_id bigint(20) unsigned NOT NULL,
			token_type varchar(50) NOT NULL DEFAULT 'bearer',
			issued_at datetime DEFAULT current_timestamp,
			expires_at datetime NOT NULL,
			revoked_at datetime,
			PRIMARY KEY (id),
			UNIQUE KEY token_hash (token_hash),
			KEY api_key_id (api_key_id),
			KEY expires_at (expires_at),
			FOREIGN KEY (api_key_id) REFERENCES {$wpdb->prefix}ol_api_api_keys (id) ON DELETE CASCADE
		) $charset_collate;

		-- OL-API Permissions Table
		CREATE TABLE {$wpdb->prefix}ol_api_permissions (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			api_key_id bigint(20) unsigned NOT NULL,
			endpoint_id bigint(20) unsigned NOT NULL,
			method varchar(10) NOT NULL DEFAULT 'GET',
			can_read tinyint(1) NOT NULL DEFAULT 0,
			can_create tinyint(1) NOT NULL DEFAULT 0,
			can_update tinyint(1) NOT NULL DEFAULT 0,
			can_delete tinyint(1) NOT NULL DEFAULT 0,
			created_at datetime DEFAULT current_timestamp,
			updated_at datetime DEFAULT current_timestamp ON UPDATE current_timestamp,
			PRIMARY KEY (id),
			UNIQUE KEY unique_permission (api_key_id, endpoint_id, method),
			KEY api_key_id (api_key_id),
			KEY endpoint_id (endpoint_id),
			FOREIGN KEY (api_key_id) REFERENCES {$wpdb->prefix}ol_api_api_keys (id) ON DELETE CASCADE,
			FOREIGN KEY (endpoint_id) REFERENCES {$wpdb->prefix}ol_api_endpoints (id) ON DELETE CASCADE
		) $charset_collate;

		-- OL-API Logs Table (for audit trail and debugging)
		CREATE TABLE {$wpdb->prefix}ol_api_logs (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			request_id varchar(36) NOT NULL,
			api_key_id bigint(20) unsigned,
			endpoint_id bigint(20) unsigned,
			method varchar(10),
			url_path varchar(255),
			http_status int(3),
			response_time_ms int(11),
			request_size int(11),
			response_size int(11),
			error_message longtext,
			user_agent varchar(500),
			ip_address varchar(45),
			created_at datetime DEFAULT current_timestamp,
			PRIMARY KEY (id),
			KEY request_id (request_id),
			KEY api_key_id (api_key_id),
			KEY endpoint_id (endpoint_id),
			KEY created_at (created_at),
			KEY http_status (http_status)
		) $charset_collate;
		";

		return $sql;
	}

	/**
	 * Get individual table schema
	 * 
	 * Returns SQL for a specific table.
	 * Useful for migrations and testing.
	 * 
	 * @param string $table_name Table name (without prefix)
	 * @return string|null Table schema SQL or null if not found
	 * @since 1.0.0
	 */
	public function get_table_schema( string $table_name ): ?string {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$prefix           = $wpdb->prefix;

		$schemas = [
			'ol_api_endpoints'        => $this->get_endpoints_schema( $charset_collate, $prefix ),
			'ol_api_endpoint_fields'  => $this->get_endpoint_fields_schema( $charset_collate, $prefix ),
			'ol_api_api_keys'         => $this->get_api_keys_schema( $charset_collate, $prefix ),
			'ol_api_tokens'           => $this->get_tokens_schema( $charset_collate, $prefix ),
			'ol_api_permissions'      => $this->get_permissions_schema( $charset_collate, $prefix ),
			'ol_api_logs'             => $this->get_logs_schema( $charset_collate, $prefix ),
		];

		return $schemas[ $table_name ] ?? null;
	}

	/**
	 * Get endpoints table schema
	 * 
	 * @param string $charset_collate WordPress charset and collation string
	 * @param string $prefix WordPress table prefix
	 * @return string Table schema SQL
	 * @since 1.0.0
	 */
	private function get_endpoints_schema( string $charset_collate, string $prefix ): string {
		return "CREATE TABLE {$prefix}ol_api_endpoints (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			name varchar(120) NOT NULL,
			description longtext,
			post_type varchar(20) NOT NULL DEFAULT 'post',
			enabled tinyint(1) NOT NULL DEFAULT 1,
			require_api_key tinyint(1) NOT NULL DEFAULT 1,
			rate_limit_per_minute int(11),
			documentation longtext,
			created_at datetime DEFAULT current_timestamp,
			updated_at datetime DEFAULT current_timestamp ON UPDATE current_timestamp,
			PRIMARY KEY (id),
			UNIQUE KEY name (name),
			KEY post_type (post_type),
			KEY enabled (enabled)
		) $charset_collate;";
	}

	/**
	 * Get endpoint fields table schema
	 * 
	 * @param string $charset_collate WordPress charset and collation string
	 * @param string $prefix WordPress table prefix
	 * @return string Table schema SQL
	 * @since 1.0.0
	 */
	private function get_endpoint_fields_schema( string $charset_collate, string $prefix ): string {
		return "CREATE TABLE {$prefix}ol_api_endpoint_fields (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			endpoint_id bigint(20) unsigned NOT NULL,
			field_name varchar(120) NOT NULL,
			field_type varchar(50) NOT NULL,
			field_label varchar(120),
			is_required tinyint(1) NOT NULL DEFAULT 0,
			is_searchable tinyint(1) NOT NULL DEFAULT 0,
			is_sortable tinyint(1) NOT NULL DEFAULT 0,
			is_filterable tinyint(1) NOT NULL DEFAULT 0,
			show_in_response tinyint(1) NOT NULL DEFAULT 1,
			meta_key varchar(120),
			created_at datetime DEFAULT current_timestamp,
			updated_at datetime DEFAULT current_timestamp ON UPDATE current_timestamp,
			PRIMARY KEY (id),
			KEY endpoint_id (endpoint_id),
			KEY field_name (field_name),
			FOREIGN KEY (endpoint_id) REFERENCES {$prefix}ol_api_endpoints (id) ON DELETE CASCADE
		) $charset_collate;";
	}

	/**
	 * Get API keys table schema
	 * 
	 * @param string $charset_collate WordPress charset and collation string
	 * @param string $prefix WordPress table prefix
	 * @return string Table schema SQL
	 * @since 1.0.0
	 */
	private function get_api_keys_schema( string $charset_collate, string $prefix ): string {
		return "CREATE TABLE {$prefix}ol_api_api_keys (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			key_hash varchar(255) NOT NULL,
			name varchar(120) NOT NULL,
			description longtext,
			user_id bigint(20) unsigned,
			is_active tinyint(1) NOT NULL DEFAULT 1,
			last_used_at datetime,
			expires_at datetime,
			created_at datetime DEFAULT current_timestamp,
			updated_at datetime DEFAULT current_timestamp ON UPDATE current_timestamp,
			PRIMARY KEY (id),
			UNIQUE KEY key_hash (key_hash),
			KEY user_id (user_id),
			KEY is_active (is_active),
			KEY expires_at (expires_at)
		) $charset_collate;";
	}

	/**
	 * Get tokens table schema
	 * 
	 * @param string $charset_collate WordPress charset and collation string
	 * @param string $prefix WordPress table prefix
	 * @return string Table schema SQL
	 * @since 1.0.0
	 */
	private function get_tokens_schema( string $charset_collate, string $prefix ): string {
		return "CREATE TABLE {$prefix}ol_api_tokens (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			token_hash varchar(255) NOT NULL,
			api_key_id bigint(20) unsigned NOT NULL,
			token_type varchar(50) NOT NULL DEFAULT 'bearer',
			issued_at datetime DEFAULT current_timestamp,
			expires_at datetime NOT NULL,
			revoked_at datetime,
			PRIMARY KEY (id),
			UNIQUE KEY token_hash (token_hash),
			KEY api_key_id (api_key_id),
			KEY expires_at (expires_at),
			FOREIGN KEY (api_key_id) REFERENCES {$prefix}ol_api_api_keys (id) ON DELETE CASCADE
		) $charset_collate;";
	}

	/**
	 * Get permissions table schema
	 * 
	 * @param string $charset_collate WordPress charset and collation string
	 * @param string $prefix WordPress table prefix
	 * @return string Table schema SQL
	 * @since 1.0.0
	 */
	private function get_permissions_schema( string $charset_collate, string $prefix ): string {
		return "CREATE TABLE {$prefix}ol_api_permissions (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			api_key_id bigint(20) unsigned NOT NULL,
			endpoint_id bigint(20) unsigned NOT NULL,
			method varchar(10) NOT NULL DEFAULT 'GET',
			can_read tinyint(1) NOT NULL DEFAULT 0,
			can_create tinyint(1) NOT NULL DEFAULT 0,
			can_update tinyint(1) NOT NULL DEFAULT 0,
			can_delete tinyint(1) NOT NULL DEFAULT 0,
			created_at datetime DEFAULT current_timestamp,
			updated_at datetime DEFAULT current_timestamp ON UPDATE current_timestamp,
			PRIMARY KEY (id),
			UNIQUE KEY unique_permission (api_key_id, endpoint_id, method),
			KEY api_key_id (api_key_id),
			KEY endpoint_id (endpoint_id),
			FOREIGN KEY (api_key_id) REFERENCES {$prefix}ol_api_api_keys (id) ON DELETE CASCADE,
			FOREIGN KEY (endpoint_id) REFERENCES {$prefix}ol_api_endpoints (id) ON DELETE CASCADE
		) $charset_collate;";
	}

	/**
	 * Get logs table schema
	 * 
	 * @param string $charset_collate WordPress charset and collation string
	 * @param string $prefix WordPress table prefix
	 * @return string Table schema SQL
	 * @since 1.0.0
	 */
	private function get_logs_schema( string $charset_collate, string $prefix ): string {
		return "CREATE TABLE {$prefix}ol_api_logs (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			request_id varchar(36) NOT NULL,
			api_key_id bigint(20) unsigned,
			endpoint_id bigint(20) unsigned,
			method varchar(10),
			url_path varchar(255),
			http_status int(3),
			response_time_ms int(11),
			request_size int(11),
			response_size int(11),
			error_message longtext,
			user_agent varchar(500),
			ip_address varchar(45),
			created_at datetime DEFAULT current_timestamp,
			PRIMARY KEY (id),
			KEY request_id (request_id),
			KEY api_key_id (api_key_id),
			KEY endpoint_id (endpoint_id),
			KEY created_at (created_at),
			KEY http_status (http_status)
		) $charset_collate;";
	}
}
