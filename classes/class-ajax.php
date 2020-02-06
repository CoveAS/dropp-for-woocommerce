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
		add_action( 'wp_ajax_dropp_pdf', __CLASS__ . '::dropp_pdf' );
		add_action( 'wp_ajax_dropp_pdf_merge', __CLASS__ . '::dropp_pdf_merge' );
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

		$booking = new API_Booking( $consignment, $shipping_method->test_mode );
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

	protected static function get_pdf( $consignment_id ) {
		$consignment = new Dropp_Consignment();
		$consignment->get( $consignment_id );
		if ( null === $consignment->id ) {
			wp_send_json(
				[
					'status'      => 'error',
					'consignment' => $consignment->to_array( false ),
					'message'     => 'Could not find consignment',
					'errors'      => [],
				]
			);
		}
		$shipping_method = new Shipping_Method( $consignment->shipping_item_id );
		$api_pdf = new API_PDF( $consignment, $shipping_method->test_mode );
		return $api_pdf->get_pdf( $shipping_method->debug_mode );
	}

	/**
	 * Dropp pdf
	 */
	public static function dropp_pdf() {
		$consignment_id = filter_input( INPUT_GET, 'consignment_id', FILTER_DEFAULT );
		self::dropp_pdf_single( $consignment_id );
	}

	/**
	 * Dropp pdf single
	 *
	 * @param string|int $consignment_id Consignment ID.
	 */
	private static function dropp_pdf_single( $consignment_id ) {
		try {
			$pdf = self::get_pdf( $consignment_id );
		} catch ( Exception $e ) {
			wp_send_json(
				[
					'status'  => 'error',
					'message' => $e->getMessage(),
					'errors'  => $api_pdf->errors,
				]
			);
		}
		header( 'Content-type: application/pdf' );
		echo $pdf;
		die;
	}

	/**
	 * Dropp pdf merge
	 *
	 * @throws Exception Exception.
	 */
	public static function dropp_pdf_merge() {
		$consignment_ids = filter_input( INPUT_GET, 'consignment_ids', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		if ( empty( $consignment_ids ) ) {
			wp_send_json(
				[
					'status'  => 'error',
					'message' => 'Missing consignment ids.',
				]
			);
		}

		$uploads_dir = API_PDF::get_dir();
		if ( $uploads_dir['error'] ) {
			wp_send_json(
				[
					'status'  => 'error',
					'message' => $uploads_dir['error'],
				]
			);
		}

		$consignment_ids = array_unique( $consignment_ids );
		if ( 1 === count( $consignment_ids ) ) {
			// No need to merge 1 pdf.
			self::dropp_pdf_single( reset( $consignment_ids ) );
			return;
		}

		// Grab pdf's and save them.
		$files = [];
		try {
			foreach ( $consignment_ids as $consignment_id ) {
				$consignment = new Dropp_Consignment();
				$consignment->get( $consignment_id );
				if ( null === $consignment->id ) {
					throw new Exception( __( 'Could not find consignment', 'woocommerce-dropp-shipping' ) . $consignment_id );
				}
				$shipping_method = new Shipping_Method( $consignment->shipping_item_id );
				$api_pdf         = new API_PDF( $consignment, $shipping_method->test_mode );
				$api_pdf->download( $shipping_method->debug_mode );
			}
		} catch ( Exception $e ) {
			wp_send_json(
				[
					'status'  => 'error',
					'message' => $e->getMessage(),
				]
			);
		}

		require_once dirname( __DIR__ ) . '/includes/loader.php';

		$merger = new \iio\libmergepdf\Merger;
		foreach ( $files as $file ) {
			$merger->addFile( $file );
		}
		$created_pdf = $merger->merge();
		header( 'Content-type: application/pdf' );
		echo $created_pdf;
		die;
	}
}
