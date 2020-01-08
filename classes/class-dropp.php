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
		require_once __DIR__ . '/class-dropp-product-line.php';
		require_once __DIR__ . '/class-dropp-location.php';
		require_once __DIR__ . '/class-shipping-method.php';
		require_once __DIR__ . '/class-shipping-meta-box.php';
		require_once __DIR__ . '/class-shipping-item-meta.php';

		// Attach meta field to the shipping method in the checkout that saves to the shipping items.
		Shipping_Item_Meta::setup();
		// Display a meta box on orders for booking with dropp.
		Shipping_Meta_Box::setup();

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
			// Add styles.
			wp_register_style(
				'woocommerce-dropp-shipping',
				plugins_url( 'assets/css/dropp.css', __DIR__ ),
				[],
				Dropp::VERSION
			);
			wp_enqueue_style( 'woocommerce-dropp-shipping' );

			// Add javascript.
			wp_register_script(
				'woocommerce-dropp-shipping',
				plugins_url( 'assets/js/dropp.js', __DIR__ ),
				array( 'jquery' ),
				Dropp::VERSION,
				true
			);
			wp_enqueue_script( 'woocommerce-dropp-shipping' );

			// Add javascript variables.
			wp_localize_script(
				'woocommerce-dropp-shipping',
				'_dropp',
				[
					'ajaxurl'           => admin_url( 'admin-ajax.php' ),
					'dropplocationsurl' => '//app.dropp.is/dropp-locations.min.js',
					'i18n'              => [
						'error_loading' => esc_html__( 'Could not load the location selector. Someone from the store will contact you regarding the delivery location.', 'woocommerce-dropp-shipping' ),
					],
				]
			);
		}
	}
}
