<?php
/**
 * Dropp
 *
 * @package dropp-for-woocommerce
 */

namespace Dropp\Shipping_Method;

/**
 * Shipping method
 */
class Dropp extends Shipping_Method {

	/**
	 * Capital Area
	 *
	 * @var string One of 'inside', 'outside' or 'both'
	 */
	protected static $capital_area = 'inside';
}
