<?php
/**
 * OL-API Component Registry
 *
 * @package OL_API\Core
 * @since 1.0.0
 */

namespace OL_API\Core;

/**
 * Registry Class
 * 
 * Central registry for components within the plugin.
 * Implements the Registry pattern for dependency management.
 * 
 * Responsibilities:
 * - Register new components
 * - Retrieve registered components
 * - Manage component lifecycle
 * - Provide dependency resolution
 * 
 * @since 1.0.0
 */
class Registry {

	/**
	 * Registered components
	 * 
	 * @var array<string, mixed>
	 */
	private array $components = [];

	/**
	 * Component aliases for backward compatibility
	 * 
	 * @var array<string, string>
	 */
	private array $aliases = [];

	/**
	 * Singleton instances cache
	 * 
	 * @var array<string, object>
	 */
	private array $singletons = [];

	/**
	 * Constructor
	 * 
	 * @since 1.0.0
	 */
	public function __construct() {
		// Initialize empty registry
	}

	/**
	 * Register a component
	 * 
	 * Registers a component in the registry.
	 * Can be a class name (for lazy loading), factory callable, or instance.
	 * 
	 * @param string       $name     Component identifier
	 * @param mixed        $resolver Class name, callable (factory), or object instance
	 * @param bool         $shared   If true, same instance returned each time (singleton)
	 * @return void
	 * @since 1.0.0
	 */
	public function register( string $name, $resolver, bool $shared = true ): void {
		if ( $this->is_registered( $name ) ) {
			throw new \RuntimeException(
				sprintf(
					'Component "%s" is already registered in the registry',
					$name
				)
			);
		}

		$this->components[ $name ] = [
			'resolver' => $resolver,
			'shared'   => $shared,
		];
	}

	/**
	 * Register a singleton component (shared instance)
	 * 
	 * Convenience method for registering shared components.
	 * 
	 * @param string $name     Component identifier
	 * @param mixed  $resolver Class name, callable, or instance
	 * @return void
	 * @since 1.0.0
	 */
	public function singleton( string $name, $resolver ): void {
		$this->register( $name, $resolver, true );
	}

	/**
	 * Register a factory component (new instance each time)
	 * 
	 * Convenience method for registering non-shared components.
	 * 
	 * @param string $name     Component identifier
	 * @param mixed  $resolver Class name or callable
	 * @return void
	 * @since 1.0.0
	 */
	public function factory( string $name, $resolver ): void {
		$this->register( $name, $resolver, false );
	}

	/**
	 * Get a component instance
	 * 
	 * Resolves and returns a component from the registry.
	 * Handles lazy loading and singleton caching.
	 * 
	 * @param string $name Component identifier
	 * @return mixed Component instance
	 * @throws \RuntimeException If component not found
	 * @since 1.0.0
	 */
	public function get( string $name ) {
		// Check for alias
		if ( isset( $this->aliases[ $name ] ) ) {
			$name = $this->aliases[ $name ];
		}

		if ( ! $this->is_registered( $name ) ) {
			throw new \RuntimeException(
				sprintf(
					'Component "%s" is not registered in the registry',
					$name
				)
			);
		}

		$component = $this->components[ $name ];

		// Return cached singleton
		if ( $component['shared'] && isset( $this->singletons[ $name ] ) ) {
			return $this->singletons[ $name ];
		}

		// Resolve component
		$instance = $this->resolve( $component['resolver'] );

		// Cache if singleton
		if ( $component['shared'] ) {
			$this->singletons[ $name ] = $instance;
		}

		return $instance;
	}

	/**
	 * Check if component is registered
	 * 
	 * @param string $name Component identifier
	 * @return bool True if registered
	 * @since 1.0.0
	 */
	public function is_registered( string $name ): bool {
		return isset( $this->components[ $name ] ) || isset( $this->aliases[ $name ] );
	}

	/**
	 * Register a component alias
	 * 
	 * Creates an alias for an existing component registration.
	 * Useful for backward compatibility or alternative names.
	 * 
	 * @param string $alias Component alias
	 * @param string $name  Actual component identifier
	 * @return void
	 * @throws \RuntimeException If target not registered
	 * @since 1.0.0
	 */
	public function alias( string $alias, string $name ): void {
		if ( ! $this->is_registered( $name ) ) {
			throw new \RuntimeException(
				sprintf(
					'Cannot alias to unregistered component "%s"',
					$name
				)
			);
		}

		$this->aliases[ $alias ] = $name;
	}

	/**
	 * Get all registered component names
	 * 
	 * @return array<string> Component identifiers
	 * @since 1.0.0
	 */
	public function get_all_names(): array {
		return array_keys( $this->components );
	}

	/**
	 * Resolve a component
	 * 
	 * Resolves a component from its resolver definition.
	 * Supports:
	 * - Class names (instantiation)
	 * - Callables (factory functions)
	 * - Already instantiated objects
	 * 
	 * @param mixed $resolver Component resolver
	 * @return mixed Resolved instance
	 * @throws \RuntimeException If resolver is invalid
	 * @since 1.0.0
	 */
	private function resolve( $resolver ) {
		// Already an object instance
		if ( is_object( $resolver ) ) {
			return $resolver;
		}

		// Callable (factory function)
		if ( is_callable( $resolver ) ) {
			return call_user_func( $resolver, $this );
		}

		// Class name - instantiate
		if ( is_string( $resolver ) && class_exists( $resolver ) ) {
			return new $resolver();
		}

		throw new \RuntimeException(
			sprintf(
				'Invalid resolver type: %s',
				gettype( $resolver )
			)
		);
	}

	/**
	 * Clear all registered components
	 * 
	 * Resets the registry to empty state.
	 * Useful for testing.
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	public function clear(): void {
		$this->components = [];
		$this->aliases    = [];
		$this->singletons = [];
	}
}
