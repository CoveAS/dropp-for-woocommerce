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
	 * Weight Limit in KG
	 * Flytjandi supports unlimited weight
	 *
	 * @var int
	 */
	public $weight_limit = 0;

	/**
	 * Capital Area
	 *
	 * @var string One of 'inside', 'outside', '!inside' or 'both'
	 */
	protected static $capital_area = '!inside';

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
