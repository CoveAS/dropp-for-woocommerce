<?php
/**
 * Shipping item meta
 *
 * @package woocommerce-dropp-shipping
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
			__( 'Dropp Booking', 'woocommerce-dropp-shipping' ),
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
					'actions'                => __( 'Actions', 'woocommerce-dropp-shipping' ),
					'check_status'           => __( 'Check status', 'woocommerce-dropp-shipping' ),
					'download'               => __( 'Download', 'woocommerce-dropp-shipping' ),
					'update_order'           => __( 'Update order', 'woocommerce-dropp-shipping' ),
					'view_order'             => __( 'View order', 'woocommerce-dropp-shipping' ),
					'cancel_order'           => __( 'Cancel order', 'woocommerce-dropp-shipping' ),
					'barcode'                => __( 'Barcode', 'woocommerce-dropp-shipping' ),
					'customer'               => __( 'Customer', 'woocommerce-dropp-shipping' ),
					'status'                 => __( 'Status', 'woocommerce-dropp-shipping' ),
					'created'                => __( 'Created', 'woocommerce-dropp-shipping' ),
					'updated'                => __( 'Updated', 'woocommerce-dropp-shipping' ),
					'product'                => __( 'Product', 'woocommerce-dropp-shipping' ),
					'products'               => __( 'Products', 'woocommerce-dropp-shipping' ),
					'booked_consignments'    => __( 'Booked consignments', 'woocommerce-dropp-shipping' ),
					'submit'                 => __( 'Book now', 'woocommerce-dropp-shipping' ),
					'remove'                 => __( 'Remove location', 'woocommerce-dropp-shipping' ),
					'add_location'           => __( 'Add shipment', 'woocommerce-dropp-shipping' ),
					'add_home_delivery'      => __( 'Add home delivery', 'woocommerce-dropp-shipping' ),
					'change_ocation'         => __( 'Change location', 'woocommerce-dropp-shipping' ),
					'customer'               => __( 'Customer', 'woocommerce-dropp-shipping' ),
					'name'                   => __( 'Name', 'woocommerce-dropp-shipping' ),
					'email_address'          => __( 'Email address', 'woocommerce-dropp-shipping' ),
					'social_security_number' => __( 'Social security number', 'woocommerce-dropp-shipping' ),
					'address'                => __( 'Address', 'woocommerce-dropp-shipping' ),
					'phone_number'           => __( 'Phone number', 'woocommerce-dropp-shipping' ),
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
