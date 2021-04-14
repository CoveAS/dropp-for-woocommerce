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
class Dropp_Outside_Capital_Area extends Dropp {
	/**
	 * Capital Area
	 *
	 * @var string One of 'inside', 'outside' or 'both'
	 */
	protected static $capital_area = 'outside';

	/**
	 * Constructor.
	 *
	 * @param int $instance_id Shipping method instance.
	 */
	public function __construct( $instance_id = 0 ) {
		$this->id                 = 'dropp_is_oca';
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = __( 'Dropp Outside Capital Area', 'dropp-for-woocommerce' );
		$this->method_description = __( 'Deliver parcels at delivery locations in Iceland', 'dropp-for-woocommerce' );
		$this->supports           = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
		);

		$this->init();
	}
}
