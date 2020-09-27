<?php
/**
 * Flytjandi
 *
 * @package dropp-for-woocommerce
 */

namespace Dropp\Shipping_Method;

use Dropp\API;

/**
 * Flytjandi
 */
class Flytjandi extends Home_Delivery {

	/**
	 * Constructor.
	 *
	 * @param int $instance_id Shipping method instance.
	 */
	public function __construct( $instance_id = 0 ) {
		$this->id                 = 'dropp_flytjandi';
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = __( 'Flytjandi', 'dropp-for-woocommerce' );
		$this->method_description = __( 'Deliver parcels at delivery locations in Iceland', 'dropp-for-woocommerce' );
		$this->supports           = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
		);

		$this->init();
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
		// Flytjandi is only available outside of the dropp home delivery zone.
		if ( in_array( $package['destination']['postcode'], $this->get_valid_postcodes(), false ) ) {
			return false;
		}
		return true;
	}
}
