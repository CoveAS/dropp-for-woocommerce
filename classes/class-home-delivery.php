<?php
/**
 * Shipping method
 *
 * @package woocommerce-dropp-shipping
 */

namespace Dropp;

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
		$this->method_title       = __( 'Dropp Home Delivery', 'woocommerce-dropp-shipping' );
		$this->method_description = __( 'Home delivery in Iceland', 'woocommerce-dropp-shipping' );

		$this->supports           = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
		);

		$this->init();
	}
}
