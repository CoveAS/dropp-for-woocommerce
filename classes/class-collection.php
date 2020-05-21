<?php
/**
 * Dropp
 *
 * @package dropp-for-woocommerce
 */

namespace Dropp;

use ArrayAccess;

/**
 * Dropp
 */
class Collection implements ArrayAccess {
	/**
	 * $container
	 *
	 * @var array
	 */
	protected $container = [];

	/**
	 * Construct
	 *
	 * @param array $container Data to store.
	 */
	public function __construct( $container = [] ) {
		$this->container = $container;
	}

	/**
	 * Add
	 *
	 * @param  mixed      $item Item to add to the collection.
	 * @return Collection       This object.
	 */
	public function add( $item ) {
		$this->container[] = $item;
		return $this;
	}

	/**
	 * Merge
	 *
	 * @param  Collection $item Collection to merge with.
	 * @return Collection       This object.
	 */
	public function merge( $collection ) {
		$this->container = array_merge( $this->container, $collection->to_array() );
		return $this;
	}

	/**
	 * To array
	 *
	 * @return array Collection as array.
	 */
	public function to_array() {
		return $this->container;
	}

	/**
	 * To array
	 *
	 * @return array Collection converted to array.
	 */
	public function map( $callback, ...$params ) {
		return array_map(
			function( $item ) use ( $callback, $params ) {
				return call_user_func_array( [ $item, $callback ], $params );
			},
			$this->container
		);
	}

	/**
	 * Offset set
	 *
	 * @param int|string $offset Offset.
	 * @param mixed      $value  Value.
	 */
	public function offsetSet( $offset, $value ) {
		if ( is_null( $offset ) ) {
			$this->container[] = $value;
		} else {
			$this->container[ $offset ] = $value;
		}
	}

	/**
	 * Offset exists
	 *
	 * @param  int|string $offset Offset.
	 * @return boolean            True when offset exists.
	 */
	public function offsetExists( $offset ) {
		return isset( $this->container[ $offset ] );
	}

	/**
	 * Offset unset
	 *
	 * @param  int|string $offset Offset.
	 */
	public function offsetUnset( $offset ) {
		unset( $this->container[ $offset ] );
	}

	/**
	 * Offset get
	 *
	 * @param  int|string $offset Offset.
	 * @return mixed              Value.
	 */
	public function offsetGet( $offset ) {
		return isset( $this->container[ $offset ] ) ? $this->container[ $offset ] : null;
	}
}
