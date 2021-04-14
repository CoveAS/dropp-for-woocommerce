<?php
/**
 * Home Delivery
 *
 * @package dropp-for-woocommerce
 */

namespace Dropp\Shipping_Method;

use Dropp\API;

/**
 * Home Delivery
 */
class Home_Delivery extends Shipping_Method {

	/**
	 * Weight Limit in KG
	 *
	 * @var int
	 */
	public $weight_limit = 60;

	/**
	 * Capital Area
	 *
	 * @var string One of 'inside', 'outside' or 'both'
	 */
	protected static $capital_area = 'inside';

	/**
	 * Constructor.
	 *
	 * @param int $instance_id Shipping method instance.
	 */
	public function __construct( $instance_id = 0 ) {
		$this->id                 = 'dropp_home';
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = __( 'Dropp Home Delivery', 'dropp-for-woocommerce' );
		$this->method_description = __( 'Home delivery in Iceland between 19:00-22:00', 'dropp-for-woocommerce' );
		$this->supports           = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
		);

		$this->init();
	}

	/**
	 * Validate postcode
	 *
	 * @return boolean Valid post code.
	 */
	public function validate_postcode( $postcode, $capital_area = 'inside' ) {
		$api       = new API( $this );
		$postcodes = get_transient( 'dropp_delivery_postcodes' );
		if ( empty( $postcodes ) || ! is_array( $postcodes[0] ) ) {
			$response  = $api->noauth()->get( 'dropp/location/deliveryzips' );
			$postcodes = $response['codes'];
			set_transient( 'dropp_delivery_postcodes', $postcodes, DAY_IN_SECONDS );
		}

		foreach ( $postcodes as $area ) {
			if ( "{$area['code']}" !== "{$postcode}" ) {
				continue;
			}
			if ( 'both' === $capital_area ) {
				return true;
			}
			// Check if area matches inside or outside capital area.
			return ( 'inside' === $capital_area ) === $area['capital'];
		}

		return false;
	}

	/**
	 * Validate package
	 *
	 * @param  array   $package Package.
	 * @return boolean          True for a valid package.
	 */
	public function validate_package( $package ) {
		if ( empty( $package['destination']['country'] ) || empty( $package['destination']['postcode'] ) ) {
			return false;
		}
		if ( 'IS' !== $package['destination']['country'] ) {
			return false;
		}
		return $this->validate_postcode( $package['destination']['postcode'], static::$capital_area );
	}

	/**
	 * Calculate the shipping costs.
	 *
	 * @param array $package Package of items from cart.
	 */
	public function calculate_shipping( $package = array() ) {
		if ( ! $this->validate_package( $package ) ) {
			return;
		}
		parent::calculate_shipping( $package );
	}
}
