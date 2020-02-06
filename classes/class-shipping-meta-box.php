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
		$order            = new \WC_Order( $order_id );
		$billing_address = $order->get_address();
		$shipping_address = $order->get_address( 'shipping' );
		$line_items = $order->get_items( 'shipping' );
		$shipping_items = [];
		foreach ( $line_items as $line_item ) {
			if ( 'dropp_is' !== $line_item->get_method_id() ) {
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
		wp_enqueue_script( 'dropp-admin-js', plugin_dir_url( __DIR__ ) . '/assets/js/dropp-admin.js', [], Dropp::VERSION, true );
		wp_localize_script(
			'dropp-admin-js',
			'_dropp',
			[
				'order_id'            => $order_id,
				'ajaxurl'             => admin_url( 'admin-ajax.php' ),
				'dropplocationsurl'   => '//app.dropp.is/dropp-locations.min.js',
				'products'            => self::get_dropp_products(),
				'locations'           => Dropp_Location::array_from_order(),
				'consignments'        => Dropp_Consignment::array_from_order(),
				'customer'            => $shipping_address,
				'shipping_items'      => $shipping_items,
				'i18n'                => [
					'barcode'                => __( 'Barcode', 'woocommerce-dropp-shipping' ),
					'customer'               => __( 'Customer', 'woocommerce-dropp-shipping' ),
					'status'                 => __( 'Status', 'woocommerce-dropp-shipping' ),
					'created'                => __( 'Created', 'woocommerce-dropp-shipping' ),
					'products'               => __( 'Products', 'woocommerce-dropp-shipping' ),
					'booked_consignments'    => __( 'Booked consignments', 'woocommerce-dropp-shipping' ),
					'submit'                 => __( 'Book now', 'woocommerce-dropp-shipping' ),
					'add_location'           => __( 'Add shipping location', 'woocommerce-dropp-shipping' ),
					'change_ocation'         => __( 'Change location', 'woocommerce-dropp-shipping' ),
					'customer'               => __( 'Customer', 'woocommerce-dropp-shipping' ),
					'name'                   => __( 'Name', 'woocommerce-dropp-shipping' ),
					'email_address'          => __( 'Email address', 'woocommerce-dropp-shipping' ),
					'social_security_number' => __( 'Social security number', 'woocommerce-dropp-shipping' ),
					'address'                => __( 'Address', 'woocommerce-dropp-shipping' ),
					'phone_number'           => __( 'Phone number', 'woocommerce-dropp-shipping' ),
				],
			]
		);
	}

	/**
	 * Get Dropp Products
	 *
	 * @param  integer $order_id (optional) Order ID.
	 * @return array             Array of Dropp_Product.
	 */
	public static function get_dropp_products( $order_id = false ) {
		if ( false === $order_id ) {
			$order_id = get_the_ID();
		}
		$order      = new \WC_Order( $order_id );
		$line_items = $order->get_items( 'line_item' );
		$collection = [];
		foreach ( $line_items as $order_item_id => $order_item ) {
			$collection[] = new Dropp_Product_Line( $order_item );
		}
		return $collection;
	}

	/**
	 * Render booking meta box
	 *
	 * @param WP_Post $post Post.
	 */
	public static function render_booking_meta_box( $post ) {
		$order          = new \WC_Order( $post->ID );
		echo '<div id="dropp-booking"><span class="loading-message" v-if="0">Loading ...</span></div>';
	}
}
