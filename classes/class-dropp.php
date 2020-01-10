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
		require_once __DIR__ . '/class-dropp-consignment.php';
		require_once __DIR__ . '/class-ajax.php';
		require_once __DIR__ . '/class-booking.php';
		require_once __DIR__ . '/class-shipping-method.php';
		require_once __DIR__ . '/class-shipping-meta-box.php';
		require_once __DIR__ . '/class-shipping-item-meta.php';

		// Attach meta field to the shipping method in the checkout that saves to the shipping items.
		Shipping_Item_Meta::setup();
		// Display a meta box on orders for booking with dropp.
		Shipping_Meta_Box::setup();
		Ajax::setup();

		add_action( 'wp_enqueue_scripts', __CLASS__ . '::checkout_javascript' );
		add_filter( 'woocommerce_shipping_methods', __CLASS__ . '::add_shipping_method' );
	}

	/**
	 * Install Consignment table
	 */
	public static function install_consignment_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'dropp_consignments';

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			shipping_item_id varchar() NOT NULL,
			location_id varchar() NOT NULL,
			products text NOT NULL,
			customer text NOT NULL,
			time created_at DEFAULT '0000-00-00 00:00:00' NOT NULL,
			time updated_at DEFAULT '0000-00-00 00:00:00' NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
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
