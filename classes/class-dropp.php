<?php
/**
 * Dropp
 *
 * @package woocommerce-dropp-shipping
 */

namespace Dropp;

/**
 * Dropp
 */
class Dropp {

	const VERSION = '0.0.1';

	/**
	 * Setup
	 */
	public static function loaded() {
		require_once dirname( __DIR__ ) . '/traits/trait-shipping-settings.php';
		require_once __DIR__ . '/class-shipping-method.php';

		// Attach meta field to the shipping method in the checkout that saves to the shipping items.
		require_once __DIR__ . '/class-shipping-item-meta.php';
		Shipping_Item_Meta::setup();

		add_action( 'wp_enqueue_scripts', __CLASS__ . '::checkout_javascript' );
		add_filter( 'woocommerce_shipping_methods', __CLASS__ . '::add_shipping_method' );
	}

	/**
	 * Add shipping methods
	 *
	 * @param  array $shipping_methods Array of WC_Shipping mehtods.
	 *
	 * @return array $shipping_methods Array of WC_Shipping mehtods.
	 */
	public static function add_shipping_method( $shipping_methods ) {
		$shipping_methods['dropp_is'] = 'Dropp\Shipping_Method';
		return $shipping_methods;
	}

	/**
	 * Load checkout javascript
	 */
	public static function checkout_javascript() {
		if ( function_exists( 'is_checkout' ) && is_checkout() ) {
			wp_register_script(
				'woocommerce-dropp-shipping',
				plugins_url( 'assets/js/dropp.js', __DIR__ ),
				array( 'jquery' ),
				Dropp::VERSION,
				true
			);
			wp_enqueue_script( 'woocommerce-dropp-shipping' );
			wp_localize_script(
				'woocommerce-dropp-shipping',
				'_dropp',
				[
					'ajaxurl'           => admin_url( 'admin-ajax.php' ),
					'dropplocationsurl' => '//app.dropp.is/dropp-locations.min.js',
				]
			);
		}
	}
}
