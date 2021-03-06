<?php
/**
 * Shipping method
 *
 * @package dropp-for-woocommerce
 */

namespace Dropp\Shipping_Method;

use Dropp\Shipping_Settings;
use Dropp\API;


/**
 * Shipping method
 */
abstract class Shipping_Method extends \WC_Shipping_Flat_Rate {
	use Shipping_Settings;

	/**
	 * Weight Limit in KG
	 *
	 * @var int
	 */
	public $weight_limit = 10;

	/**
	 * Capital Area
	 *
	 * @var string One of 'inside', 'outside', '!inside' or 'both'
	 */
	protected static $capital_area = 'both';

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
	 * Is this method available?
	 *
	 * @param array $package Package.
	 * @return bool
	 */
	public function is_available( $package ) {
		$is_available = true;
		$total_weight = 0;
		foreach ( $package['contents'] as $item ) {
			if ( empty( $item['data'] ) ) {
				continue;
			}
			$total_weight += $item['quantity'] * wc_get_weight( $item['data']->get_weight(), 'kg' );
		}
		if ( $total_weight > $this->weight_limit && 0 !== $this->weight_limit ) {
			$is_available = false;
		} elseif ( empty( $package['destination']['country'] ) || empty( $package['destination']['postcode'] ) ) {
			$is_available = false;
		} elseif ( 'IS' !== $package['destination']['country'] ) {
			$is_available = false;
		} elseif ( ! $this->validate_postcode( $package['destination']['postcode'], static::$capital_area ) ) {
			$is_available = false;
		}
		return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', $is_available, $package, $this );
	}

	/**
	 * Validate postcode
	 *
	 * @param string $postcode     Postcode.
	 * @param string $capital_area (optional) One of 'inside', 'outside', '!inside' or 'both'.
	 * @return boolean Valid post code.
	 */
	public function validate_postcode( $postcode, $capital_area = 'inside' ) {
		if ('both' === static::$capital_area ) {
			return true;
		}
		$api       = new API( $this );
		$postcodes = get_transient( 'dropp_delivery_postcodes' );
		if ( empty( $postcodes ) || ! is_array( $postcodes[0] ) ) {
			$response  = $api->noauth()->get( 'dropp/location/deliveryzips' );
			$postcodes = $response['codes'];
			set_transient( 'dropp_delivery_postcodes', $postcodes, 600 );
		}

		foreach ( $postcodes as $area ) {
			if ( "{$area['code']}" !== "{$postcode}" ) {
				continue;
			}
			if ( 'both' === $capital_area ) {
				return true;
			}
			// Check if area matches inside or outside capital area.
			return ( 'inside' === $capital_area ) === $area['capital'];
		}

		if ( '!inside' === $capital_area ) {
			return true;
		}

		return false;
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
		$additional = $this->get_additional_form_fields( $form_fields );
		if ( empty( $additional ) ) {
			return $form_fields;
		}
		$pos = array_search( 'cost', array_keys( $form_fields ) ) + 1;
		$len = count( $form_fields );

		// Insert additional fields after costs.
		$form_fields = array_merge(
			array_slice( $form_fields, 0, $pos ),
			$additional,
			array_slice( $form_fields, $pos, null )
		);
		return $form_fields;
	}

	/**
	 * Get additional form fields
	 *
	 * @param  array $form_fields Form fields.
	 * @return array              Additional form fields.
	 */
	public function get_additional_form_fields( $form_fields ) {
		return [
			'free_shipping'           => [
				'title'       => __( 'Free shipping', 'dropp-for-woocommerce' ),
				'label'       => __( 'Enable', 'dropp-for-woocommerce' ),
				'type'        => 'checkbox',
				'placeholder' => '',
				'description' => '',
				'default'     => '0',
				'desc_tip'    => false,
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
		$cart       = WC()->cart;
		$cart_items = $cart ? $cart->get_cart() : [];
		$cart_total = 0;



		foreach ( $cart_items as $values ) {
			$_product    = $values['data'];
			$cart_total += $_product->get_price() * $values['quantity'];
		}

		if ( $cart_total < $threshold ) {
			// Cart is less than threshold. Shipping is not free.
			return $cost;
		}

		// Free shipping aquired.
		return 0;
	}

	/**
	 * Get pricetype
	 *
	 * @return int
	 */
	public function get_pricetype() {
		$location_data = WC()->session->get( 'dropp_session_location' );
		return intval( $location_data['pricetype'] ?? 1 );
	}

	/**
	 * Calculate the shipping costs.
	 *
	 * @param array $package Package of items from cart.
	 */
	public function calculate_shipping( $package = array() ) {
		do_action( 'dropp_before_calculate_shipping', $package, $this );
		parent::calculate_shipping( $package );
		do_action( 'dropp_after_calculate_shipping', $package, $this );
	}

	/**
	 * Sanitize the cost field.
	 *
	 * @since 3.4.0
	 * @param string $value Unsanitized value.
	 * @throws Exception Last error triggered.
	 * @return string
	 */
	public function sanitize_cost( $value ) {
		do_action( 'dropp_before_calculate_shipping', [], $this );
		$value = parent::sanitize_cost( $value );
		do_action( 'dropp_after_calculate_shipping', [], $this );
		return $value;
	}
}
