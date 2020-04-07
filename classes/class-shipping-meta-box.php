<?php
/**
 * Shipping item meta
 *
 * @package dropp-for-woocommerce
 */

namespace Dropp;

/**
 * Shipping item meta
 */
class Shipping_Meta_Box {

	/**
	 * Setup
	 */
	public static function setup() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_booking_meta_box' ), 1, 2 );
		add_action( 'admin_enqueue_scripts', __CLASS__ . '::admin_enqueue_scripts' );
	}

	/**
	 * Add booking meta box
	 *
	 * @param string  $post_type Post type.
	 * @param WP_Post $post      Post.
	 */
	public static function add_booking_meta_box( $post_type, $post ) {
		if ( 'shop_order' !== $post_type ) {
			return;
		}
		add_meta_box(
			'woocommerce-order-dropp-booking',
			__( 'Dropp Booking', 'dropp-for-woocommerce' ),
			array( __CLASS__, 'render_booking_meta_box' ),
			'shop_order',
			'normal',
			'high'
		);
	}

	/**
	 * Admin enqueue script
	 * Add custom styling and javascript to the admin options
	 *
	 * @param string $hook Hook.
	 */
	public static function admin_enqueue_scripts( $hook ) {
		if ( 'post.php' !== $hook ) {
			return;
		}
		if ( 'shop_order' !== get_post_type() ) {
			return;
		}

		$order_id         = get_the_ID();
		$order            = wc_get_order( $order_id );
		$adapter          = new Order_Adapter( $order );
		$consignments     = $adapter->consignments();
		$billing_address  = $order->get_address();
		$shipping_address = $order->get_address( 'shipping' );
		$line_items       = $order->get_items( 'shipping' );
		$shipping_items   = [];

		// Maybe update the consignments.
		$consignments->map( 'maybe_update' );

		$dropp_methods  = [ 'dropp_is', 'dropp_home' ];
		foreach ( $line_items as $line_item ) {
			if ( ! in_array( $line_item->get_method_id(), $dropp_methods, true ) ) {
				continue;
			}
			$shipping_items[] = [
				'id'          => $line_item->get_id(),
				'instance_id' => $line_item->get_instance_id(),
				'label'       => $line_item->get_name(),
			];
		}
		if ( empty( $shipping_address['email'] ) ) {
			$shipping_address['email'] = $billing_address['email'];
		}
		if ( empty( $shipping_address['phone'] ) ) {
			$shipping_address['phone'] = $billing_address['phone'];
		}
		$shipping_address['ssn'] = $order->get_meta( '_billing_dropp_ssn', true );
		$shipping_method = new Shipping_Method();
		wp_enqueue_script( 'dropp-admin-js', plugin_dir_url( __DIR__ ) . '/assets/js/dropp-admin.js', [], Dropp::VERSION, true );
		wp_localize_script(
			'dropp-admin-js',
			'_dropp',
			[
				'nonce'                  => wp_create_nonce( 'dropp' ),
				'time_now'               => current_time( 'mysql' ),
				'order_id'               => $order_id,
				'ajaxurl'                => admin_url( 'admin-ajax.php' ),
				'dropplocationsurl'      => '//app.dropp.is/dropp-locations.min.js',
				'storeid'                => $shipping_method->store_id,
				'ssn_enabled'            => $shipping_method->enable_ssn,
				'products'               => Dropp_Product_Line::array_from_order(),
				'locations'              => Dropp_Location::array_from_order(),
				'home_delivery_location' => (new Dropp_Location)->home_delivery(),
				'consignments'           => $consignments->map( 'to_array', false ),
				'customer'               => $shipping_address,
				'shipping_items'         => $shipping_items,
				'status_list'            => Dropp_Consignment::get_status_list(),
				'i18n'                   => self::nbsp( [
					'actions'                => __( 'Actions', 'dropp-for-woocommerce' ),
					'check_status'           => __( 'Check status', 'dropp-for-woocommerce' ),
					'download'               => __( 'Download', 'dropp-for-woocommerce' ),
					'update_order'           => __( 'Update order', 'dropp-for-woocommerce' ),
					'view_order'             => __( 'View order', 'dropp-for-woocommerce' ),
					'cancel_order'           => __( 'Cancel order', 'dropp-for-woocommerce' ),
					'barcode'                => __( 'Barcode', 'dropp-for-woocommerce' ),
					'customer'               => __( 'Customer', 'dropp-for-woocommerce' ),
					'status'                 => __( 'Status', 'dropp-for-woocommerce' ),
					'created'                => __( 'Created', 'dropp-for-woocommerce' ),
					'updated'                => __( 'Updated', 'dropp-for-woocommerce' ),
					'product'                => __( 'Product', 'dropp-for-woocommerce' ),
					'products'               => __( 'Products', 'dropp-for-woocommerce' ),
					'booked_consignments'    => __( 'Booked consignments', 'dropp-for-woocommerce' ),
					'submit'                 => __( 'Book now', 'dropp-for-woocommerce' ),
					'remove'                 => __( 'Remove location', 'dropp-for-woocommerce' ),
					'add_location'           => __( 'Add shipment', 'dropp-for-woocommerce' ),
					'add_home_delivery'      => __( 'Add home delivery', 'dropp-for-woocommerce' ),
					'change_ocation'         => __( 'Change location', 'dropp-for-woocommerce' ),
					'customer'               => __( 'Customer', 'dropp-for-woocommerce' ),
					'name'                   => __( 'Name', 'dropp-for-woocommerce' ),
					'email_address'          => __( 'Email address', 'dropp-for-woocommerce' ),
					'social_security_number' => __( 'Social security number', 'dropp-for-woocommerce' ),
					'address'                => __( 'Address', 'dropp-for-woocommerce' ),
					'phone_number'           => __( 'Phone number', 'dropp-for-woocommerce' ),
				] ),
			]
		);
	}

	/**
	 * Non-breaking spaces
	 *
	 * @param  array $strings Strings.
	 * @return array          Strings.
	 */
	public static function nbsp( $strings ) {
		foreach ( $strings as &$string ) {
			$string = str_replace( ' ', '&nbsp;', $string );
		}
		return $strings;
	}

	/**
	 * Render booking meta box
	 *
	 * @param WP_Post $post Post.
	 */
	public static function render_booking_meta_box( $post ) {
		echo '<div id="dropp-booking"><span class="loading-message" v-if="0">Loading ...</span></div>';
	}
}
