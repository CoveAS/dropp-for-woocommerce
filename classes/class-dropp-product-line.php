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
	public $weight;
	public $quantity;
	public $barcode;
	public $needs_shipping = true;

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

	public function fill( $args ) {
		$args = wp_parse_args(
			$args,
			[
				'id'       => '',
				'name'     => '',
				'weight'   => '',
				'quantity' => 1,
				'barcode'  => '',
			]
		);

		$this->id       = $args['id'];
		$this->name     = $args['name'];
		$this->weight   = $args['weight'];
		$this->quantity = $args['quantity'];
		$this->barcode  = $args['barcode'];
		return $this;
	}

	public function from_order_item( $order_item ) {
		$this->id             = $order_item->get_id();
		$this->name           = $order_item->get_name();
		$this->weight         = $order_item->get_product()->get_weight();
		$this->quantity       = $order_item->get_quantity();
		$this->barcode        = $order_item->get_product()->get_sku();
		$this->needs_shipping = $order_item->get_product()->needs_shipping();
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
