<?php
/**
 * Location
 *
 * @package woocommerce-dropp-shipping
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

	/**
	 * Constructor.
	 *
	 * @param int $instance_id Shipping method instance.
	 */
	public function __construct( $order_item ) {
		$this->order_item    = $order_item;
		$this->order_item_id = $order_item->get_id();
		$location            = $order_item->get_meta( 'dropp_location' );

		if ( is_array( $location ) ) {
			$this->id = $location['id'] ?? null;
			$this->name = $location['name'] ?? null;
			$this->address = $location['address'] ?? null;
		}
	}

	/**
	 * From Order
	 *
	 * @param  integer $order_id (optional) Order ID.
	 * @return array             Array of Dropp_Product.
	 */
	public static function from_order( $order_id = false ) {
		if ( false === $order_id ) {
			$order_id = get_the_ID();
		}
		$order      = new \WC_Order( $order_id );
		$line_items = $order->get_items( 'shipping' );
		$collection = [];
		foreach ( $line_items as $order_item_id => $order_item ) {
			$location = new Dropp_Location( $order_item );
			if ( ! $location->id ) {
				continue;
			}
			$collection[] = $location;
		}
		return $collection;
	}
}
