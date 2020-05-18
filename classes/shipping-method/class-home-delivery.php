<?php
/**
 * Shipping method
 *
 * @package dropp-for-woocommerce
 */

namespace Dropp\Shipping_Method;

/**
 * Home Delivery
 */
class Home_Delivery extends Shipping_Method {

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
}
