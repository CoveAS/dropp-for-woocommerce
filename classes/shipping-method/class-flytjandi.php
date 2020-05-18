<?php
/**
 * Flytjandi
 *
 * @package dropp-for-woocommerce
 */

namespace Dropp\Shipping_Method;

/**
 * Flytjandi
 */
class Flytjandi extends Shipping_Method {

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
}
