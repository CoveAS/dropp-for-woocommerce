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
