<?php
/**
 * Location
 *
 * @package dropp-for-woocommerce
 */

namespace Dropp;

/**
 * Shipping method
 */
class Dropp_Location {
	/**
	 * WC_Order_Item $order_item
	 */
	protected $order_item;
	public $order_item_id;

	public $id;
	public $name;
	public $barcode;
	public $type;

	/**
	 * Constructor.
	 *
	 * @param int $instance_id Shipping method instance.
	 */
	public function __construct( $type = 'dropp_is' ) {
		$this->type = $type;
		// Special location handling for home deliveries.
		if ( 'dropp_home' === $type ) {
			$this->id      = '9ec1f30c-2564-4b73-8954-25b7b3186ed3';
			$this->name    = __( 'Home delivery', 'dropp-for-woocommerce' );
			$this->address = '';
		}

		// Special location handling for flytjandi deliveries.
		if ( 'dropp_flytjandi' === $type ) {
			$this->id      = 'a178c25e-bb35-4420-8792-d5295f0e7fcc';
			$this->name    = __( 'Flytjandi', 'dropp-for-woocommerce' );
			$this->address = '';
		}

		// Special location handling for pickup.
		if ( 'dropp_pickup' === $type ) {
			$this->id      = '30e06a53-2d65-46e7-adc1-18e60de28ecc';
			$this->name    = __( 'Pick-up at warehouse', 'dropp-for-woocommerce' ). ' (Vatnagarðar 22)';
			$this->address = '';
		}
	}

	/**
	 * From Shipping Item
	 *
	 * @param  integer $order_id (optional) Order ID.
	 * @return array             Array of Dropp_Location.
	 */
	public static function from_shipping_item( $shipping_item ) {
		$location = new self( $shipping_item->get_method_id() );
		$location->order_item    = $shipping_item;
		$location->order_item_id = $shipping_item->get_id();

		if ( 'dropp_is' === $location->type ) {
			$meta_data = $shipping_item->get_meta( 'dropp_location' );
			if ( is_array( $meta_data ) ) {
				$location->id      = $meta_data['id'] ?? null;
				$location->name    = $meta_data['name'] ?? null;
				$location->address = $meta_data['address'] ?? null;
			}
		}
		return $location;
	}

	/**
	 * From Order
	 *
	 * @param  integer $order_id (optional) Order ID.
	 * @return array             Array of Dropp_Location.
	 */
	public static function from_order( $order_id = false ) {
		if ( false === $order_id ) {
			$order_id = get_the_ID();
		}
		$order      = new \WC_Order( $order_id );
		$line_items = $order->get_items( 'shipping' );
		$collection = [];
		foreach ( $line_items as $order_item_id => $order_item ) {
			$location = Dropp_Location::from_shipping_item( $order_item );
			if ( ! $location->id ) {
				continue;
			}
			$collection[] = $location;
		}
		return $collection;
	}

	/**
	 * Array From Order
	 *
	 * @param  integer $order_id (optional) Order ID.
	 * @return array             Array of Dropp_Location arrays.
	 */
	public static function array_from_order( $order_id = false ) {
		return self::from_order($order_id);
	}
}
