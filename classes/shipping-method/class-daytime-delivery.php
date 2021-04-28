<?php
/**
 * Home Delivery
 *
 * @package dropp-for-woocommerce
 */

namespace Dropp\Shipping_Method;

use Dropp\API;

/**
 * Daytime Delivery
 */
class Daytime_Delivery extends Home_Delivery {

	/**
	 * Capital Area
	 *
	 * @var string One of 'inside', 'outside', '!inside' or 'both'
	 */
	protected static $capital_area = 'inside';

	/**
	 * Constructor.
	 *
	 * @param int $instance_id Shipping method instance.
	 */
	public function __construct( $instance_id = 0 ) {
		$this->id                 = 'dropp_daytime';
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = __( 'Dropp Daytime Delivery', 'dropp-for-woocommerce' );
		$this->method_description = __( 'Home delivery in Iceland between 10:00-17:00', 'dropp-for-woocommerce' );
		$this->supports           = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
		);
		$this->init();
	}
}
