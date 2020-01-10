<?php
/**
 * Ajax
 *
 * @package woocommerce-dropp-shipping
 */

namespace Dropp;

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

		// @TODO: nonce verification.
		$consignment = new Dropp_Consignment();
		$consignment->fill(
			[
				'shipping_item_id' => $order_item_id,
				'location_id'      => filter_input( INPUT_POST, 'location_id', FILTER_DEFAULT ),
				'barcode'          => filter_input( INPUT_POST, 'barcode', FILTER_DEFAULT ),
				'customer'         => filter_input( INPUT_POST, 'customer', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ),
				'products'         => filter_input( INPUT_POST, 'products', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ),
			]
		);

		$booking = new Booking( $consignment );

		try {
			$booking->send();
		} catch ( \Exception $e ) {
			wp_send_json( '@TODO: Error message. '. $e->getMessage() );
		}
		$consignment->save();

		wp_send_json(
			[
				'status' => 'success',
				'consignment' => $consignment->to_array( false ),
				'errors' => [],
			]
		);
	}
}
