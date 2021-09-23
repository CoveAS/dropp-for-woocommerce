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
	 * Original title
	 *
	 * @var string
	 */
	protected $original_title = '';

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

	/**
	 * Calculate the shipping costs.
	 *
	 * @param array $package Package of items from cart.
	 */
	public function calculate_shipping( $package = array() ) {
		$location_data = WC()->session->get( 'dropp_session_location' );
		if ( self::get_instance()->location_name_in_label && ! empty( $location_data['name'] ) ) {
			if ( ! $this->original_title ) {
				$this->original_title = $this->title;
			}
			$this->title = $this->original_title . ' - ' . $location_data['name'];
		}
		parent::calculate_shipping( $package );
	}


	/**
	 * Get instance of \Dropp\Shipping_Method\Dropp
	 *
	 * @return Dropp\Shipping_Method\Dropp
	 */
	public static function get_instance() {
		static $instance = false;
		if (! $instance) {
			if (class_exists('WC_Shipping')) {
				$shipping_methods = \WC_Shipping::instance()->get_shipping_methods();
				$instance = $shipping_methods['dropp_is'] ?? null;
			}
			$instance = $instance ?: new self;
		}
		return $instance;
	}
}
