<?php
/**
 * Shipping method
 *
 * @package woocommerce-dropp-shipping
 */

namespace Dropp;

/**
 * Shipping method
 */
class Shipping_Method extends \WC_Shipping_Flat_Rate {
	use Shipping_Settings;

	/**
	 * Constructor.
	 *
	 * @param int $instance_id Shipping method instance.
	 */
	public function __construct( $instance_id = 0 ) {
		$this->id                 = 'dropp_is';
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = __( 'Dropp', 'woocommerce-dropp-shipping' );
		$this->method_description = __( 'Deliver parcels at delivery locations in Iceland', 'woocommerce-dropp-shipping' );

		$this->supports           = array(
			'shipping-zones',
			'settings',
			'instance-settings',
			'instance-settings-modal',
		);

		$this->init();
	}

	/**
	 * Initialize free shipping.
	 */
	public function init() {
		parent::init();

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();
		$this->init_properties();

		// Actions.
		add_action( 'woocommerce_update_options_shipping_' . $this->id, [ $this, 'process_admin_options' ] );
	}

	/**
	 * See if free shipping is available based on the package and cart.
	 *
	 * @param array $package Shipping package.
	 * @return bool
	 */
	public function is_available( $package ) {
		$is_available = true;
		return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', $is_available, $package, $this );
	}

	/**
	 * Called to calculate shipping rates for this method. Rates can be added using the add_rate() method.
	 *
	 * @uses WC_Shipping_Method::add_rate()
	 *
	 * @param array $package Shipping package.
	 */
	public function calculate_shipping( $package = array() ) {
		$this->add_rate(
			array(
				'label'   => $this->title,
				'cost'    => 0,
				'taxes'   => false,
				'package' => $package,
			)
		);
	}
}
