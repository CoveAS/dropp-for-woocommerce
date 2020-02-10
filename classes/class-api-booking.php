<?php
/**
 * Booking
 *
 * @package woocommerce-dropp-shipping
 */

namespace Dropp;

use WC_Logger;
use WC_Log_Levels;
use Exception;

/**
 * API Booking
 */
class API_Booking {

	protected $test = false;
	protected $consignment;

	public $errors = [];

	/**
	 * Construct
	 */
	public function __construct( Dropp_Consignment $consignment, $test ) {
		$this->consignment = $consignment;
		$this->test        = $test;
	}

	/**
	 * Is dropp
	 *
	 * @param  WC_Order $order Order.
	 * @return boolean         True if the dropp shipping method is present on the order.
	 */
	public static function is_dropp( $order ) {
		$shipping_items = $order->get_items( 'shipping' );
		foreach ( $shipping_items as $shipping_item ) {
			if ( 'dropp_is' === $shipping_item->get_method_id() ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Is booked
	 *
	 * @param  WC_Order $order Order.
	 * @return int             Number of booked consignments.
	 */
	public static function is_booked( $order ) {
		return Dropp_Consignment::count_consignments_on_order( $order, true );
	}

	/**
	 * Book order
	 *
	 * @param  WC_Order $order Order.
	 * @return boolean         True if any order was booked.
	 */
	public static function book_order( $order ) {
		$shipping_items   = $order->get_items( 'shipping' );
		$billing_address  = $order->get_address();
		$shipping_address = $order->get_address( 'shipping' );
		$line_items       = $order->get_items( 'shipping' );

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

			$product_lines = Dropp_Product_Line::array_from_order( $order, true );

			// @TODO: nonce verification.
			$consignment = new Dropp_Consignment();
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

			$booking = new self( $consignment, $shipping_method->test_mode );
			try {
				$booking->send( $shipping_method->debug_mode );
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

	/**
	 * Send
	 *
	 * @throws Exception $e     Sending exception.
	 * @param  Boolean   $debug Debug.
	 * @return Booking          This object.
	 */
	public function send( $debug = false ) {
		if ( empty( $this->consignment ) ) {
			throw new Exception( 'Error Processing Request', 1 );
		}
		$log  = new WC_Logger();
		$args = [
			'headers' => [
				'Authorization' => 'Basic ' . $this->consignment->get_api_key(),
				'Content-Type'  => 'application/json;charset=UTF-8',
			],
			'body' => wp_json_encode( $this->consignment->to_array() ),
		];
		if ( $debug ) {
			$log->add(
				'woocommerce-dropp-shipping',
				'[DEBUG] Booking request:' . PHP_EOL . $this->get_url() . PHP_EOL . wp_json_encode( $args, JSON_PRETTY_PRINT ),
				WC_Log_Levels::DEBUG
			);
		}

		// @TODO: Debug mode - log request
		$response = wp_remote_post(
			self::get_url(),
			$args
		);

		if ( is_wp_error( $response ) ) {
			$log->add(
				'woocommerce-dropp-shipping',
				'[ERROR] Booking response:' . PHP_EOL . wp_json_encode( $response->errors, JSON_PRETTY_PRINT ),
				WC_Log_Levels::ERROR
			);
		} elseif ( $debug ) {
			$data = json_decode( $response['body'] );
			if ( ! empty( $data ) ) {
				$body = wp_json_encode( $data, JSON_PRETTY_PRINT );
			} else {
				$body = $response['body'];
			}
			$log->add(
				'woocommerce-dropp-shipping',
				'[DEBUG] Booking response:' . PHP_EOL . $body,
				WC_Log_Levels::DEBUG
			);
		}

		// @TODO: Debug mode - log response
		$this->validate_response( $response );

		$dropp_order = json_decode( $response['body'], true );

		$this->consignment->dropp_order_id = $dropp_order['id'] ?? '';
		$this->consignment->status         = $dropp_order['status'] ?? '';
		$this->consignment->barcode        = $dropp_order['barcode'] ?? '';

		return $this;
	}

	/**
	 * Get URL
	 *
	 * @return string URL.
	 */
	public function get_url() {
		if ( $this->test ) {
			return 'https://stage.dropp.is/dropp/api/v1/orders';
		}
		return 'https://dropp.is/dropp/api/v1/orders';

	}

	/**
	 * Valitdate response
	 *
	 * @throws Exception                Error reason.
	 * @param  WP_Error|array $response Response from dropp.
	 * @return boolean                  True on a valid response
	 */
	public function validate_response( $response ) {
		if ( is_wp_error( $response ) ) {
			$this->errors = $response->get_error_messages();
			throw new Exception( __( 'Response error', 'woocommerce-dropp-shipping' ) );
		}
		$dropp_order = json_decode( $response['body'], true );
		if ( ! is_array( $dropp_order ) ) {
			throw new Exception( __( 'Invalid json', 'woocommerce-dropp-shipping' ) );
		}
		if ( ! empty( $dropp_order['error'] ) ) {
			throw new Exception( $dropp_order['error'] );
		}
		if ( empty( $dropp_order['id'] ) ) {
			throw new Exception( __( 'Empty ID in the response', 'woocommerce-dropp-shipping' ) );
		}
		return true;
	}
}
