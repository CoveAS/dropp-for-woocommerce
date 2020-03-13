<?php
/**
 * Booking
 *
 * @package woocommerce-dropp-shipping
 */

namespace Dropp;

use WC_Order;
use WC_Logger;
use WC_Log_Levels;
use Exception;

/**
 * API Booking
 */
class Order_Adapter {

	protected $order;

	/**
	 * Construct
	 *
	 * @param WC_Order $order Order.
	 */
	public function __construct( WC_Order $order ) {
		$this->order = $order;
	}

	/**
	 * Is dropp
	 *
	 * @return boolean         True if the dropp shipping method is present on the order.
	 */
	public function is_dropp() {
		$shipping_items = $this->order->get_items( 'shipping' );
		foreach ( $shipping_items as $shipping_item ) {
			if ( 'dropp_is' === $shipping_item->get_method_id() ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Count consignments
	 *
	 * @param  boolean $only_booked (optional) Default is false. Only counts booked items when true.
	 * @return integer              Count.
	 */
	public function count_consignments( $only_booked = false ) {
		global $wpdb;
		$shipping_items    = $this->order->get_items( 'shipping' );
		$shipping_item_ids = [];
		foreach ( $shipping_items as $shipping_item ) {
			$shipping_item_ids[] = $shipping_item->get_id();
		}
		if ( empty( $shipping_item_ids ) ) {
			return 0;
		}
		$shipping_item_ids = implode( ',', $shipping_item_ids);
		$sql = "SELECT count(*) FROM {$wpdb->prefix}dropp_consignments WHERE shipping_item_id in ({$shipping_item_ids})";
		if ( $only_booked ) {
			$sql .= " AND status NOT IN ( 'ready', 'error', 'overweight' )";
		}
		$result = $wpdb->get_var( $sql );
		return (int) $result;
	}

	/**
	 * Consignments
	 *
	 * @return Collection Collection of consignments.
	 */
	public function consignments() {
		$line_items = $this->order->get_items( 'shipping' );
		$container  = [];
		foreach ( $line_items as $order_item_id => $order_item ) {
			$container = array_merge(
				$container,
				Dropp_Consignment::from_shipping_item( $order_item )
			);
		}
		return new Collection( $container );
	}

	/**
	 * Book order
	 *
	 * @return boolean True if any order was booked.
	 */
	public function book() {
		$shipping_items   = $this->order->get_items( 'shipping' );
		$billing_address  = $this->order->get_address();
		$shipping_address = $this->order->get_address( 'shipping' );
		$line_items       = $this->order->get_items( 'shipping' );

		// Fix missing args in address.
		if ( empty( $shipping_address['email'] ) ) {
			$shipping_address['email'] = $billing_address['email'];
		}
		if ( empty( $shipping_address['phone'] ) ) {
			$shipping_address['phone'] = $billing_address['phone'];
		}

		$any_booked = false;
		foreach ( $shipping_items as $shipping_item ) {
			if ( 'dropp_is' !== $shipping_item->get_method_id() ) {
				continue;
			}
			$instance_id     = $shipping_item->get_instance_id();
			$shipping_method = new Shipping_Method( $instance_id );
			$location        = new Dropp_Location( $shipping_item );

			if ( ! $location->id ) {
				continue;
			}

			$product_lines = Dropp_Product_Line::array_from_order( $this->order, true );

			$consignment        = new Dropp_Consignment();
			$consignment->debug = $shipping_method->debug_mode;
			$consignment->fill(
				[
					'shipping_item_id' => $shipping_item->get_id(),
					'location_id'      => $location->id,
					'customer'         => Dropp_Customer::from_shipping_address( $shipping_address ),
					'products'         => $product_lines,
					'test'             => $shipping_method->test_mode,
				]
			);

			$total_weight = 0;
			foreach ( $product_lines as $product_line ) {
				$total_weight += $product_line->weight * $product_line->quantity;
			}
			if ( $total_weight > 10 ) {
				$consignment->status         = 'overweight';
				$consignment->status_message = __( 'Cannot book the order because it\'s over the weight limit of 10 Kg', 'woocommerce-dropp-shipping' );
				$consignment->save();
				continue;
			}

			$consignment->save();

			try {
				$consignment->remote_post();
				$consignment->save();
				$any_booked = true;
			} catch ( \Exception $e ) {
				$consignment->status         = 'error';
				$consignment->status_message = $e->getMessage();
				$consignment->save();
			}
		}
		return $any_booked;
	}
}
