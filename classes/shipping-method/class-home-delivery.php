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
	 * @var string One of 'inside', 'outside', '!inside' or 'both'
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
}
