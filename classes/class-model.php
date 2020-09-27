<?php
/**
 * Arrayable
 *
 * @package dropp-for-woocommerce
 */

namespace Dropp;

/**
 * Arrayable
 */
abstract class Model {

	/**
	 * To array
	 *
	 * @return array Array representation.
	 */
	abstract public function to_array();
}
