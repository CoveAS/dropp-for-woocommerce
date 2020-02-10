<?php
/**
 * Product
 *
 * @package woocommerce-dropp-shipping
 */

namespace Dropp;

use WC_Order_Item_Product;

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

	/**
	 * Fill
	 *
	 * @param  array              $args Arguments.
	 * @return Dropp_Product_Line       This.
	 */
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

		if ( ! empty( $this->id ) ) {
			$order_item           = new WC_Order_Item_Product( $this->id );
			$this->name           = $order_item->get_name();
			$this->weight         = $order_item->get_product()->get_weight();
			$this->barcode        = $order_item->get_product()->get_sku();
			$this->needs_shipping = $order_item->get_product()->needs_shipping();
		}

		return $this;
	}

	/**
	 * Dropp Product Line arrays from order
	 *
	 * @param  integer $order_id (optional) Order ID.
	 * @return array             Array of Dropp_Product_Line.
	 */
	public static function array_from_order( $order_id = false, $only_shipable = false  ) {
		$collection = self::from_order( $order_id, $only_shipable );
		return array_map(
			function( $item ) {
				return $item->to_array();
			},
			$collection
		);
	}

	/**
	 * Dropp Product Lines from order
	 *
	 * @param  integer $order_id (optional) Order ID.
	 * @return array             Array of Dropp_Product_Line.
	 */
	public static function from_order( $order_id = false, $only_shipable = false ) {
		if ( false === $order_id ) {
			$order_id = get_the_ID();
		}
		$order      = wc_get_order( $order_id );
		$line_items = $order->get_items( 'line_item' );
		$collection = [];
		foreach ( $line_items as $order_item_id => $order_item ) {
			$product_line = new self( $order_item );
			if ( ! $product_line->needs_shipping && $only_shipable ) {
				continue;
			}
			$collection[] = $product_line;
		}
		return $collection;
	}

	/**
	 * From order item
	 *
	 * @param  WC_Order_Item      $order_item Order item.
	 * @return Dropp_Product_Line             This.
	 */
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
			'id'             => $this->id,
			'name'           => $this->name,
			'quantity'       => $this->quantity,
			'weight'         => $this->weight,
			'needs_shipping' => $this->needs_shipping,
			'barcode'        => $this->barcode,
		];
	}
}
