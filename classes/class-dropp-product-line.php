<?php
/**
 * Product
 *
 * @package woocommerce-dropp-shipping
 */

namespace Dropp;

/**
 * Product
 */
class Dropp_Product_Line {
	/**
	 * WC_Order_Item $order_item
	 */
	protected $order_item;

	public $id;
	public $name;
	public $quantity;
	public $barcode;

	/**
	 * Constructor.
	 *
	 * @param int $instance_id Shipping method instance.
	 */
	public function __construct( $order_item = false ) {
		if ( ! empty( $order_item ) ) {
			$this->from_order_item( $order_item );
		}
	}

	public function fill( $content ) {
		$this->id       = $content['id'];
		$this->name     = $content['name'];
		$this->quantity = $content['quantity'];
		$this->barcode  = $content['barcode'];
		return $this;
	}

	public function from_order_item( $order_item ) {
		$this->id       = $order_item->get_id();
		$this->name     = $order_item->get_name();
		$this->quantity = $order_item->get_quantity();
		$this->barcode  = $order_item->get_product()->get_sku();
		return $this;
	}

	/**
	 * To array
	 *
	 * @return array Array representation.
	 */
	public function to_array() {
		return [
			'name'     => $this->name,
			'quantity' => $this->quantity,
			'barcode'  => $this->barcode,
		];
	}
}
