<?php
/**
 * Shipping method
 *
 * @package dropp-for-woocommerce
 */

namespace Dropp\Shipping_Method;

use Dropp\Shipping_Settings;

/**
 * Shipping method
 */
abstract class Shipping_Method extends \WC_Shipping_Flat_Rate {
	use Shipping_Settings;

	/**
	 * Constructor.
	 *
	 * @param int $instance_id Shipping method instance.
	 */
	public function __construct( $instance_id = 0 ) {
		$this->id                 = 'dropp_is';
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = __( 'Dropp', 'dropp-for-woocommerce' );
		$this->method_description = __( 'Deliver parcels at delivery locations in Iceland', 'dropp-for-woocommerce' );

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
	 * Get setting form fields for instances of this shipping method within zones.
	 *
	 * @return array
	 */
	public function get_instance_form_fields() {
		$form_fields                     = parent::get_instance_form_fields();
		$form_fields['title']['default'] = $this->method_title;
		if ( empty( $form_fields['cost'] ) ) {
			return $form_fields;
		}
		$additional = $this->get_additional_form_fields($form_fields);
		if ( empty( $additional ) ) {
			return $form_fields;
		}
		$pos = array_search( 'cost', array_keys( $form_fields ) ) + 1;
		$len = count( $form_fields );
		$form_fields = array_merge(
			array_slice( $form_fields, 0, $pos ),
			$additional,
			array_slice( $form_fields, $pos, null )
		);
		return $form_fields;
	}

	/**
	 * Get additional form fields
	 * @return array Additional form fields.
	 */
	public function get_additional_form_fields($form_fields) {
		return [
			'free_shipping' => [
				'title'             => __( 'Free shipping', 'dropp-for-woocommerce' ),
				'label'             => __( 'Enable', 'dropp-for-woocommerce' ),
				'type'              => 'checkbox',
				'placeholder'       => '',
				'description'       => '',
				'default'           => '0',
				'desc_tip'          => false,
			],
			'free_shipping_threshold' => [
				'title'             => __( 'Free shipping for orders above', 'dropp-for-woocommerce' ),
				'type'              => 'text',
				'placeholder'       => '0',
				'description'       => __( 'Only enable free shipping if the cart total exceeds this value.', 'dropp-for-woocommerce' ),
				'default'           => '0',
				'desc_tip'          => true,
				'sanitize_callback' => 'floatval',
			],
		];
	}

	/**
	 * Evaluate a cost from a sum/string.
	 *
	 * @param  string $sum Sum of shipping.
	 * @param  array  $args Args, must contain `cost` and `qty` keys. Having `array()` as default is for back compat reasons.
	 * @return string
	 */
	protected function evaluate_cost( $sum, $args = array() ) {
		$cost          = parent::evaluate_cost( $sum, $args );
		$free_shipping = $this->get_instance_option( 'free_shipping' );
		$threshold     = $this->get_instance_option( 'free_shipping_threshold' );
		if ( apply_filters( 'dropp_free_shipping_enabled', 'yes' !== $free_shipping, $this, $sum, $args ) ) {
			return $cost;
		}
		if ( empty( $threshold ) || empty( $cost ) ) {
			// No threshold or no cost specified. Shipping is free.
			return 0;
		}
		$cart                 = WC()->cart;
		$cart_items           = $cart ? $cart->get_cart() : [];
		$cart_total           = 0;

		foreach ( $cart_items as $values ) {
			$_product    = $values['data'];
			$cart_total += $_product->get_price() * $values['quantity'];
		}

		if ($cart_total < $threshold) {
			// Cart is less than threshold. Shipping is not free.
			return $cost;
		}

		// Free shipping aquired.
		return 0;
	}
}
