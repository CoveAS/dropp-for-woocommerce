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
	public function __construct( $order_item ) {
		$this->id = $order_item->get_id();
		$this->name = $order_item->get_name();
		$this->quantity = $order_item->get_quantity();
		$this->barcode = $order_item->get_product()->get_sku();
	}
}
