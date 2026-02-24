<?php
/**
 * OL-API Endpoint Repository
 *
 * @package OL_API\Repositories
 * @since 1.0.0
 */

namespace OL_API\Repositories;

/**
 * EndpointRepository Class
 * 
 * Repository for managing API endpoints.
 * Extends BaseRepository with endpoint-specific logic.
 * 
 * Responsibilities:
 * - CRUD operations for endpoints
 * - Query and filter endpoints
 * - Manage endpoint configuration
 * - Cache endpoint data
 * 
 * @since 1.0.0
 */
class EndpointRepository extends BaseRepository {

	/**
	 * Table name
	 * 
	 * @var string
	 */
	protected string $table = 'ol_api_endpoints';

	/**
	 * Find enabled endpoints
	 * 
	 * Returns only active endpoints.
	 * 
	 * @return array<array<string, mixed>> Array of enabled endpoints
	 * @since 1.0.0
	 */
	public function find_enabled(): array {
		return $this->find_where( [ 'enabled' => 1 ] );
	}

	/**
	 * Find endpoints by post type
	 * 
	 * @param string $post_type Post type name
	 * @return array<array<string, mixed>> Array of endpoints for post type
	 * @since 1.0.0
	 */
	public function find_by_post_type( string $post_type ): array {
		return $this->find_where( [ 'post_type' => $post_type ] );
	}

	/**
	 * Find endpoint by name
	 * 
	 * @param string $name Endpoint name
	 * @return array<string, mixed>|null Endpoint data or null
	 * @since 1.0.0
	 */
	public function find_by_name( string $name ): ?array {
		return $this->find_by( [ 'name' => $name ] );
	}

	/**
	 * Get endpoint with all fields
	 * 
	 * Returns endpoint data including all associated fields.
	 * 
	 * @param int $endpoint_id Endpoint ID
	 * @return array<string, mixed>|null Endpoint with fields or null
	 * @since 1.0.0
	 */
	public function find_with_fields( int $endpoint_id ): ?array {
		$endpoint = $this->find( $endpoint_id );
		if ( ! $endpoint ) {
			return null;
		}

		// Load associated fields
		$fields_repo = new EndpointFieldRepository();
		$endpoint['fields'] = $fields_repo->find_by_endpoint( $endpoint_id );

		return $endpoint;
	}

	/**
	 * Create endpoint with validation
	 * 
	 * @param array<string, mixed> $data Endpoint data
	 * @return int|false Insert ID or false on failure
	 * @since 1.0.0
	 */
	public function create( array $data ) {
		// Check if endpoint with same name already exists
		if ( isset( $data['name'] ) && $this->find_by_name( $data['name'] ) ) {
			return false; // Endpoint already exists
		}

		return parent::create( $data );
	}

	/**
	 * Validate endpoint data
	 * 
	 * @param array<string, mixed> $data Data to validate
	 * @return bool True if valid
	 * @since 1.0.0
	 */
	protected function validate( array $data ): bool {
		// Name is required
		if ( empty( $data['name'] ) ) {
			return false;
		}

		// Name must be alphanumeric with underscores and hyphens
		if ( ! preg_match( '/^[a-zA-Z0-9_-]+$/', $data['name'] ) ) {
			return false;
		}

		// Post type is required
		if ( empty( $data['post_type'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Sanitize endpoint data
	 * 
	 * @param array<string, mixed> $data Data to sanitize
	 * @return array<string, mixed> Sanitized data
	 * @since 1.0.0
	 */
	protected function sanitize( array $data ): array {
		$sanitized = parent::sanitize( $data );

		// Sanitize name - alphanumeric, underscores, hyphens only
		if ( isset( $sanitized['name'] ) ) {
			$sanitized['name'] = sanitize_title( $sanitized['name'] );
		}

		// Sanitize post type
		if ( isset( $sanitized['post_type'] ) ) {
			$sanitized['post_type'] = sanitize_key( $sanitized['post_type'] );
		}

		// Ensure boolean fields are integers
		foreach ( [ 'enabled', 'require_api_key' ] as $bool_field ) {
			if ( isset( $sanitized[ $bool_field ] ) ) {
				$sanitized[ $bool_field ] = (int) (bool) $sanitized[ $bool_field ];
			}
		}

		// Rate limit must be positive if set
		if ( isset( $sanitized['rate_limit_per_minute'] ) && $sanitized['rate_limit_per_minute'] ) {
			$sanitized['rate_limit_per_minute'] = max( 1, (int) $sanitized['rate_limit_per_minute'] );
		}

		return $sanitized;
	}

	/**
	 * Enable endpoint
	 * 
	 * @param int $endpoint_id Endpoint ID
	 * @return int|false Rows updated or false on failure
	 * @since 1.0.0
	 */
	public function enable( int $endpoint_id ) {
		return $this->update( $endpoint_id, [ 'enabled' => 1 ] );
	}

	/**
	 * Disable endpoint
	 * 
	 * @param int $endpoint_id Endpoint ID
	 * @return int|false Rows updated or false on failure
	 * @since 1.0.0
	 */
	public function disable( int $endpoint_id ) {
		return $this->update( $endpoint_id, [ 'enabled' => 0 ] );
	}

	/**
	 * Count endpoints by post type
	 * 
	 * @param string $post_type Post type name
	 * @return int Count of endpoints
	 * @since 1.0.0
	 */
	public function count_by_post_type( string $post_type ): int {
		return $this->count( [ 'post_type' => $post_type ] );
	}
}

/**
 * EndpointFieldRepository Class
 * 
 * Repository for managing fields within endpoints.
 * 
 * @since 1.0.0
 */
class EndpointFieldRepository extends BaseRepository {

	/**
	 * Table name
	 * 
	 * @var string
	 */
	protected string $table = 'ol_api_endpoint_fields';

	/**
	 * Find fields by endpoint
	 * 
	 * @param int $endpoint_id Endpoint ID
	 * @return array<array<string, mixed>> Array of fields
	 * @since 1.0.0
	 */
	public function find_by_endpoint( int $endpoint_id ): array {
		return $this->find_where( [ 'endpoint_id' => $endpoint_id ] );
	}

	/**
	 * Find field by name
	 * 
	 * @param int    $endpoint_id Endpoint ID
	 * @param string $field_name Field name
	 * @return array<string, mixed>|null Field data or null
	 * @since 1.0.0
	 */
	public function find_by_name( int $endpoint_id, string $field_name ): ?array {
		return $this->find_by( [
			'endpoint_id' => $endpoint_id,
			'field_name'  => $field_name,
		] );
	}

	/**
	 * Find searchable fields
	 * 
	 * @param int $endpoint_id Endpoint ID
	 * @return array<array<string, mixed>> Array of searchable fields
	 * @since 1.0.0
	 */
	public function find_searchable( int $endpoint_id ): array {
		return $this->find_where( [
			'endpoint_id'  => $endpoint_id,
			'is_searchable' => 1,
		] );
	}

	/**
	 * Find sortable fields
	 * 
	 * @param int $endpoint_id Endpoint ID
	 * @return array<array<string, mixed>> Array of sortable fields
	 * @since 1.0.0
	 */
	public function find_sortable( int $endpoint_id ): array {
		return $this->find_where( [
			'endpoint_id' => $endpoint_id,
			'is_sortable'  => 1,
		] );
	}

	/**
	 * Validate field data
	 * 
	 * @param array<string, mixed> $data Data to validate
	 * @return bool True if valid
	 * @since 1.0.0
	 */
	protected function validate( array $data ): bool {
		// Endpoint ID is required
		if ( empty( $data['endpoint_id'] ) ) {
			return false;
		}

		// Field name is required
		if ( empty( $data['field_name'] ) ) {
			return false;
		}

		// Field type is required and must be valid
		$valid_types = [ 'string', 'integer', 'boolean', 'date', 'datetime', 'number' ];
		if ( empty( $data['field_type'] ) || ! in_array( $data['field_type'], $valid_types, true ) ) {
			return false;
		}

		return true;
	}
}
