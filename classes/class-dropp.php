<?php
/**
 * Dropp
 *
 * @package dropp-for-woocommerce
 */

namespace Dropp;

/**
 * Dropp
 */
class Dropp {

	const VERSION = '1.3.6';

	/**
	 * Setup
	 */
	public static function loaded() {
		$plugin_dir = dirname( __DIR__ );

		// Models.
		require_once $plugin_dir . '/classes/class-api.php';
		require_once $plugin_dir . '/classes/class-collection.php';
		require_once $plugin_dir . '/classes/class-dropp-product-line.php';
		require_once $plugin_dir . '/classes/class-dropp-customer.php';
		require_once $plugin_dir . '/classes/class-dropp-location.php';
		require_once $plugin_dir . '/classes/class-dropp-consignment.php';
		require_once $plugin_dir . '/classes/class-dropp-pdf-collection.php';
		require_once $plugin_dir . '/classes/class-dropp-pdf.php';
		require_once $plugin_dir . '/classes/class-order-adapter.php';

		// Shipping method.
		require_once $plugin_dir . '/traits/trait-shipping-settings.php';
		require_once $plugin_dir . '/classes/shipping-method/class-shipping-method.php';
		require_once $plugin_dir . '/classes/shipping-method/class-dropp.php';
		require_once $plugin_dir . '/classes/shipping-method/class-home-delivery.php';
		require_once $plugin_dir . '/classes/shipping-method/class-flytjandi.php';
		require_once $plugin_dir . '/classes/shipping-method/class-pickup.php';

		add_filter( 'woocommerce_shipping_dropp_is_instance_option', 'Dropp\Shipping_Method\Dropp::get_cost_option', 10, 3 );

		// Ajax helper class.
		require_once $plugin_dir . '/classes/class-ajax.php';

		// WooCommerce utility classes.
		require_once $plugin_dir . '/classes/class-shipping-meta-box.php';
		require_once $plugin_dir . '/classes/class-shipping-item-meta.php';
		require_once $plugin_dir . '/classes/class-pending-shipping.php';
		require_once $plugin_dir . '/classes/class-order-bulk-actions.php';
		require_once $plugin_dir . '/classes/class-social-security-number.php';
		require_once $plugin_dir . '/classes/class-postcode-validation.php';
		require_once $plugin_dir . '/classes/class-tracking-code.php';

		// Attach meta field to the shipping method in the checkout that saves to the shipping items.
		Shipping_Item_Meta::setup();
		// Initialise pending shipping status for orders.
		Pending_Shipping::setup();
		// Display a meta box on orders for booking with dropp.
		Shipping_Meta_Box::setup();
		Ajax::setup();
		Order_Bulk_Actions::setup();
		Social_Security_Number::setup();
		Postcode_Validation::setup();
		Tracking_Code::setup();

		add_action( 'wp_enqueue_scripts', __CLASS__ . '::checkout_javascript' );
		add_filter( 'woocommerce_shipping_methods', __CLASS__ . '::add_shipping_method' );
		add_action( 'admin_init', __CLASS__ . '::upgrade' );
		add_action( 'admin_enqueue_scripts', __CLASS__ . '::admin_enqueue_scripts' );

		// Add settings link on plugin page.
		$plugin_path = basename( dirname( __DIR__ ) );
		$hook        = "plugin_action_links_{$plugin_path}/dropp-for-woocommerce.php";
		add_filter( $hook, __CLASS__ . '::plugin_action_links' );

		load_plugin_textdomain( 'dropp-for-woocommerce', false, basename( dirname(__DIR__) ) . '/languages/' );
	}

	/**
	 * Admin enqueue script
	 *
	 * @param string $hook Hook.
	 */
	public static function admin_enqueue_scripts( $hook ) {
		if ( 'woocommerce_page_wc-settings' !== $hook ) {
			return;
		}
		wp_enqueue_script( 'dropp-admin-js', plugin_dir_url( __DIR__ ) . '/assets/js/dropp-admin.js', [], Dropp::VERSION, true );
	}

	/**
	 * Upgrade
	 */
	public static function upgrade() {
		$saved_version = get_site_option( 'woocommerce_dropp_shipping_db_version' );
		if ( version_compare( $saved_version, '0.0.1' ) === -1 && self::upgrade_001() ) {
			update_site_option( 'woocommerce_dropp_shipping_db_version', '0.0.1' );
		}
	}

	/**
	 * Install Consignment table
	 */
	public static function upgrade_001() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'dropp_consignments';

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			barcode varchar(63) NULL,
			dropp_order_id varchar(63) NULL,
			status varchar(15) NOT NULL,
			shipping_item_id varchar(63) NOT NULL,
			location_id varchar(63) NOT NULL,
			products text NOT NULL,
			customer text NOT NULL,
			test tinyint(1) DEFAULT 0 NOT NULL,
			created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( $sql );

		return true;
	}

	/**
	 * Add shipping methods
	 *
	 * @param  array $shipping_methods Array of WC_Shipping mehtods.
	 *
	 * @return array $shipping_methods Array of WC_Shipping mehtods.
	 */
	public static function add_shipping_method( $shipping_methods ) {
		$shipping_methods['dropp_is']        = 'Dropp\Shipping_Method\Dropp';
		$shipping_methods['dropp_home']      = 'Dropp\Shipping_Method\Home_Delivery';
		$shipping_methods['dropp_flytjandi'] = 'Dropp\Shipping_Method\Flytjandi';
		if ( self::is_pickup_enabled() ) {
			$shipping_methods['dropp_pickup'] = 'Dropp\Shipping_Method\Pickup';
		}
		return $shipping_methods;
	}

	/**
	 * Load checkout javascript
	 */
	public static function checkout_javascript() {
		if ( function_exists( 'is_checkout' ) && is_checkout() ) {
			// Add styles.
			wp_register_style(
				'dropp-for-woocommerce',
				plugins_url( 'assets/css/dropp.css', __DIR__ ),
				[],
				self::VERSION
			);
			wp_enqueue_style( 'dropp-for-woocommerce' );

			// Add javascript.
			wp_register_script(
				'dropp-for-woocommerce',
				plugins_url( 'assets/js/dropp.js', __DIR__ ),
				array( 'jquery' ),
				self::VERSION,
				true
			);
			wp_enqueue_script( 'dropp-for-woocommerce' );

			$shipping_method = new Shipping_Method\Dropp();
			// Add javascript variables.
			wp_localize_script(
				'dropp-for-woocommerce',
				'_dropp',
				[
					'ajaxurl'           => admin_url( 'admin-ajax.php' ),
					'storeid'           => $shipping_method->store_id,
					'dropplocationsurl' => 'https://app.dropp.is/dropp-locations.min.js',
					'i18n'              => [
						'error_loading' => esc_html__( 'Could not load the location selector. Someone from the store will contact you regarding the delivery location.', 'dropp-for-woocommerce' ),
					],
				]
			);
		}
	}

	/**
	 * Show action links on the plugin screen
	 *
	 * @param array $links The action links displayed for each plugin in the Plugins list table.
	 * @return array
	 */
	public static function plugin_action_links( $links ) {
		$url          = admin_url( 'admin.php?page=wc-settings&tab=shipping&section=dropp_is' );
		$action_links = array(
			'settings' => '<a href="' . $url . '" title="' . esc_attr__( 'View Dropp Settings', 'dropp-for-woocommerce' ) . '">' . esc_html__( 'Settings', 'dropp-for-woocommerce' ) . '</a>',
		);
		return array_merge( $action_links, $links );
	}

	/**
	 * Is pickup enabled
	 * @param  Shipping_Method $shipping_method (optional) Shipping method.
	 * @return boolean                          True if pickup is enabled.
	 */
	public static function is_pickup_enabled( $shipping_method = null ) {
		$pickup_enabled = get_transient( 'dropp_pickup_enabled' );
		if ( empty( $pickup_enabled ) ) {
			try {
				$api            = new API( $shipping_method );
				$result         = $api->get( 'orders/havepickup/' );
				$pickup_enabled = ( ! empty( $result['pickup'] ) && $result['pickup'] ? 'yes' : 'no' );
				set_transient( 'dropp_pickup_enabled', $pickup_enabled, ( 'yes' === $pickup_enabled ? DAY_IN_SECONDS : 300 ) );
			}
			catch (\Exception $e) {
				$pickup_enabled = false;
			}
		}
		return 'yes' === $pickup_enabled;
	}
}
