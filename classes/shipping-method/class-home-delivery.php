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
	public $weight_limit = 20;

	/**
	 * Constructor.
	 *
	 * @param int $instance_id Shipping method instance.
	 */
	public function __construct( $instance_id = 0 ) {
		$this->id                 = 'dropp_home';
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = __( 'Dropp Home Delivery', 'dropp-for-woocommerce' );
		$this->method_description = __( 'Home delivery in Iceland', 'dropp-for-woocommerce' );
		$this->supports           = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
		);

		$this->init();
	}

	/**
	 * Get valid post codes
	 *
	 * @return array Valid post codes.
	 */
	public function get_valid_postcodes() {
		$api = new API( $this );
		$valid_postcodes = get_transient( 'dropp_delivery_postcodes' );
		if ( empty( $valid_postcodes ) ) {
			$response = $api->noauth()->get( 'dropp/location/deliveryzips' );
			$valid_postcodes = array_map(
				function( $item ) {
					return $item['code'];
				},
				$response['codes']
			);
			set_transient( 'dropp_delivery_postcodes', $valid_postcodes, DAY_IN_SECONDS );
		}
		return $valid_postcodes;
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
		if ( ! in_array( $package['destination']['postcode'], $this->get_valid_postcodes(), false ) ) {
			return false;
		}
		return true;
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
