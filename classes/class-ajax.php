<?php
/**
 * Ajax
 *
 * @package woocommerce-dropp-shipping
 */

namespace Dropp;

use WC_Logger;

/**
 * Ajax
 */
class Ajax {
	/**
	 * Setup
	 */
	public static function setup() {
		add_action( 'wp_ajax_dropp_booking', __CLASS__ . '::dropp_booking' );
	}

	/**
	 * Dropp booking
	 */
	public static function dropp_booking() {
		$order_item_id   = filter_input( INPUT_POST, 'order_item_id', FILTER_DEFAULT );
		$shipping_method = new Shipping_Method( $order_item_id );

		// @TODO: nonce verification.
		$consignment = new Dropp_Consignment();
		$consignment->fill(
			[
				'shipping_item_id' => $order_item_id,
				'location_id'      => filter_input( INPUT_POST, 'location_id', FILTER_DEFAULT ),
				'customer'         => filter_input( INPUT_POST, 'customer', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ),
				'products'         => filter_input( INPUT_POST, 'products', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ),
				'test'             => $shipping_method->test_mode,
			]
		);
		$consignment->save();

		$booking = new Booking( $consignment, $shipping_method->test_mode );
		try {
			$booking->send( $shipping_method->debug_mode );
			$consignment->save();
		} catch ( \Exception $e ) {
			$consignment->status = 'error';
			$consignment->save();

			wp_send_json(
				[
					'status'      => 'error',
					'consignment' => $consignment->to_array( false ),
					'message'     => $e->getMessage(),
					'errors'      => $booking->errors,
				]
			);
		}

		wp_send_json(
			[
				'status'      => 'success',
				'consignment' => $consignment->to_array( false ),
				'message'     => __( 'Booked', 'woocommerce-dropp-shipping' ),
				'errors'      => [],
			]
		);
	}
}
