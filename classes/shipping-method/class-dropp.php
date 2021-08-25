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
	 * Price Type
	 *
	 * @var integer Either 1 or 2. One being inside capital area, and 2 outside.
	 */
	protected static $price_type = 1;

	/**
	 * No address available
	 *
	 * @var boolean Available when no address is provided
	 */
	protected static $no_address_available = true;

	/**
	 * Validate postcode
	 *
	 * @param string $postcode     Postcode.
	 * @param string $capital_area (optional) One of 'inside', 'outside', '!inside' or 'both'.
	 * @return boolean Valid post code.
	 */
	public function validate_postcode( $postcode, $capital_area = 'inside' ) {
		if ( is_admin() || ! WC()->session ) {
			return true;
		}
		return static::$price_type === $this->get_pricetype();
	}
}
