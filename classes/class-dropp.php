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

	const VERSION = '1.3.10';

	/**
	 * Setup
	 */
	public static function loaded() {
		self::load_classes();

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
		Checkout::setup();

		add_filter( 'woocommerce_shipping_methods', __CLASS__ . '::add_shipping_method' );
		add_action( 'admin_init', __CLASS__ . '::upgrade' );
		add_action( 'admin_enqueue_scripts', __CLASS__ . '::admin_enqueue_scripts' );
		add_action( 'woocommerce_before_order_object_save', __CLASS__ . '::maybe_convert_dropp_order_ids' );

		// Add settings link on plugin page.
		$plugin_path = basename( dirname( __DIR__ ) );
		$hook        = "plugin_action_links_{$plugin_path}/dropp-for-woocommerce.php";
		add_filter( $hook, __CLASS__ . '::plugin_action_links' );

		load_plugin_textdomain( 'dropp-for-woocommerce', false, basename( dirname( __DIR__ ) ) . '/languages/' );
	}

	/**
	 * Validate shipping class file
	 *
	 * @param string $class_file Path to class file.
	 * @return boolean
	 */
	public static function shipping_method_class_loader( $class_file ) {
		if ( ! preg_match( '/^Dropp\\\Shipping_Method\\\(.*)$/', $class_file, $matches ) ) {
			return false;
		}
		$file_name = strtolower( $matches[1] );
		$file_name = preg_replace( '/_/', '-', $file_name );
		$file_name = __DIR__ . "/shipping-method/class-{$file_name}.php";
		if ( file_exists( $file_name ) ) {
			require_once $file_name;
		}
	}

	/**
	 * Load classes
	 */
	public static function load_classes() {
		$plugin_dir = dirname( __DIR__ );

		// Utility classes.
		require_once $plugin_dir . '/classes/class-api.php';
		require_once $plugin_dir . '/classes/class-collection.php';
		require_once $plugin_dir . '/classes/class-dropp-pdf-collection.php';
		require_once $plugin_dir . '/classes/class-order-adapter.php';
		require_once $plugin_dir . '/classes/class-checkout.php';

		// Models.
		require_once $plugin_dir . '/classes/models/class-model.php';
		require_once $plugin_dir . '/classes/models/class-dropp-product-line.php';
		require_once $plugin_dir . '/classes/models/class-dropp-customer.php';
		require_once $plugin_dir . '/classes/models/class-dropp-location.php';
		require_once $plugin_dir . '/classes/models/class-dropp-consignment.php';
		require_once $plugin_dir . '/classes/models/class-dropp-pdf.php';

		// Actions.
		require_once $plugin_dir . '/classes/actions/class-convert-dropp-order-ids-to-consignments-action.php';

		// Shipping method.
		require_once $plugin_dir . '/traits/trait-shipping-settings.php';
		spl_autoload_register( __CLASS__ . '::shipping_method_class_loader' );

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
		if ( version_compare( $saved_version, '0.0.3' ) === -1 ) {
			/**
			 * In version 1.4.0 (database 0.0.3) we removed cost_2 field from the dropp_is shipping method.
			 * This field is now refactored as a separate shipping method (Dropp Outside Capital Area).
			 *
			 * Note: cost_2 was used based on location pricetype being '2'.
			 * Pricetype is now refactored to be a parameter/shortcode as part of the cost setting.
			 */
			$zones = \WC_Shipping_Zones::get_zones();
			foreach ( $zones as $zone_data ) {
				$zone = \WC_Shipping_Zones::get_zone( $zone_data['zone_id'] );
				foreach ( $zone_data['shipping_methods'] as $shipping_method ) {
					if ( 'Dropp\Shipping_Method\Dropp' !== get_class( $shipping_method ) ) {
						continue;
					}
					$instance_id = $zone->add_shipping_method( 'dropp_is_oca' );
					if ( ! $instance_id ) {
						continue;
					}
					$shipping_methods    = $zone->get_shipping_methods();
					$new_shipping_method = $shipping_methods[ $instance_id ];
					$instance_settings   = $shipping_method->instance_settings;

					// Configure shipping method.
					if ( $instance_settings['cost_2'] ) {
						$instance_settings['cost'] = $instance_settings['cost_2'];
					}
					unset( $instance_settings['cost_2'] );
					update_option(
						$new_shipping_method->get_instance_option_key(),
						apply_filters(
							'woocommerce_shipping_' . $new_shipping_method->id . '_instance_settings_values',
							$instance_settings,
							$new_shipping_method
						),
						'yes'
					);
				}
			}
		}
		if ( version_compare( $saved_version, '0.0.3' ) === -1 && self::schema() ) {
			update_site_option( 'woocommerce_dropp_shipping_db_version', '0.0.3' );
		}
	}

	/**
	 * Install Consignment table
	 */
	public static function schema() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'dropp_consignments';

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			barcode varchar(63) NULL,
			day_delivery tinyint(1) DEFAULT 0 NOT NULL,
			dropp_order_id varchar(63) NULL,
			status varchar(15) NOT NULL,
			`comment` text NOT NULL,
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
	 * @param WC_Abstract_Order $order Order.
	 */
	public static function maybe_convert_dropp_order_ids( $order ) {
		$adapter = new Order_Adapter( $order );
		$action  = new Actions\Convert_Dropp_Order_Ids_To_Consignments_Action( $adapter );
		$action->handle();
	}

	/**
	 * Add shipping methods
	 *
	 * @param  WC_Shipping[] $shipping_methods Array of WC_Shipping mehtods.
	 *
	 * @return WC_Shipping[] $shipping_methods Array of WC_Shipping mehtods.
	 */
	public static function add_shipping_method( $shipping_methods ) {
		return $shipping_methods + self::get_shipping_methods( self::is_pickup_enabled() );
	}

	/**
	 * Get shipping methods
	 *
	 * @return WC_Shipping[] $shipping_methods Array of WC_Shipping mehtods.
	 */
	public static function get_shipping_methods( $with_pickup = false ) {
		$shipping_methods = [
			'dropp_is'        => 'Dropp\Shipping_Method\Dropp',
			'dropp_is_oca'    => 'Dropp\Shipping_Method\Dropp_Outside_Capital_Area',
			'dropp_home'      => 'Dropp\Shipping_Method\Home_Delivery',
			'dropp_home_oca'  => 'Dropp\Shipping_Method\Home_Delivery_Outside_Capital_Area',
			'dropp_daytime'   => 'Dropp\Shipping_Method\Daytime_Delivery',
			'dropp_flytjandi' => 'Dropp\Shipping_Method\Flytjandi',
		];
		if ( $with_pickup ) {
			$shipping_methods['dropp_pickup'] = 'Dropp\Shipping_Method\Pickup';
		}
		return $shipping_methods;
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
	 *
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
			} catch ( \Exception $e ) {
				$pickup_enabled = false;
			}
		}
		return 'yes' === $pickup_enabled;
	}
}
