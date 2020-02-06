<?php
/**
 * Booking
 *
 * @package woocommerce-dropp-shipping
 */

namespace Dropp;

/**
 * Booking
 */
class Booking {

	const URL = 'https://stage.dropp.is/dropp/api/v1/orders';

	protected $consignment;

	public $errors = [];

	/**
	 * Construct
	 */
	public function __construct( Dropp_Consignment $consignment ) {
		$this->consignment = $consignment;
	}

	/**
	 * Send
	 *
	 * @throws \Exception $e Sending exception.
	 * @return Booking       This object.
	 */
	public function send() {
		if ( empty( $this->consignment ) ) {
			throw new \Exception( 'Error Processing Request', 1 );
		}

		$response = wp_remote_get(
			self::URL . '/barcode/',
			[
				'headers' => [
					'Authorization' => 'Basic ' . $this->consignment->get_api_key(),
					'Content-Type'  => 'application/json;charset=UTF-8',
				],
			]
		);

		if ( is_wp_error( $response ) ) {
			$this->errors = $response->get_error_messages();
			throw new \Exception( __( 'Barcode response error', 'woocommerce-dropp-shipping' ) );
		}
		$barcode_data = json_decode( $response['body'], true );
		if ( ! is_array( $barcode_data ) ) {
			throw new \Exception( __( 'Invalid barcode json', 'woocommerce-dropp-shipping' ) );
		}
		if ( empty( $barcode_data['barcode'] ) ) {
			throw new \Exception( __( 'Empty barcode in the response', 'woocommerce-dropp-shipping' ) );
		}

		$this->consignment->barcode = $barcode_data['barcode'];

		// @TODO: Debug mode - log request
		$response = wp_remote_post(
			self::URL,
			[
				'headers' => [
					'Authorization' => 'Basic ' . $this->consignment->get_api_key(),
					'Content-Type'  => 'application/json;charset=UTF-8',
				],
				'body' => wp_json_encode( $this->consignment->to_array() ),
			]
		);
		// @TODO: Debug mode - log response

		if ( is_wp_error( $response ) ) {
			$this->errors = $response->get_error_messages();
			throw new \Exception( __( 'Response error', 'woocommerce-dropp-shipping' ) );
		}

		$dropp_order = json_decode( $response['body'], true );

		if ( ! is_array( $dropp_order ) ) {
			throw new \Exception( __( 'Invalid json', 'woocommerce-dropp-shipping' ) );
		}
		if ( ! empty( $dropp_order['error'] ) ) {
			throw new \Exception( $dropp_order['error'] );
		}
		if ( empty( $dropp_order['id'] ) ) {
			throw new \Exception( __( 'Empty ID in the response', 'woocommerce-dropp-shipping' ) );
		}

		$this->consignment->dropp_order_id = $dropp_order['id'] ?? '';
		$this->consignment->status         = $dropp_order['status'] ?? '';

		$this->validate_response( $response );

		return $this;
	}

	/**
	 * Valitdate response
	 *
	 * @param  WP_Error|array $response Response from dropp.
	 * @return boolean                  True on a valid response
	 */
	public function validate_response( $response ) {
		// throw new Exception("Error Processing Request", 1);
		return true;
	}
}
