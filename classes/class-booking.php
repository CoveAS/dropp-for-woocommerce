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
		// $response = wp_remote_post(
		// // $response = wp_send_json(
		// 	self::URL,
		// 	[
		// 		'headers' => [
		// 			'Authorization' => 'Basic ' . $this->consignment->get_api_key(),
		// 			'Content-Type'  => 'application/json;charset=UTF-8',
		// 		],
		// 		'body' => wp_json_encode( $this->consignment->to_array() ),
		// 	]
		// );

		$json= '{"body": "{\"id\":\"5ed51d30-5673-4d6d-98e0-d7c61230f89b\",\"locationId\":\"16ac875d-1690-44f0-976d-26cb785080dc\",\"customer\":{\"name\":\"Eivin Landa\",\"address\":\"Blødekjær, 20, 20, 4844 Arendal\",\"phoneNumber\":\"7771414\",\"emailAddress\":\"eivin@cove.no\",\"socialSecurityNumber\":\"1234567890\"},\"orderStatusId\":\"a016372a-ba2c-485f-8220-b1359e0147c2\",\"createdAt\":\"2020-01-10T13:22:03.178Z\",\"updatedAt\":\"2020-01-10T13:22:03.178Z\",\"status\":\"initial\"}"}';
		$response = json_decode( $json, true );

		$dropp_order = json_decode( $response['body'], true );

		if ( is_array( $dropp_order ) ) {
			$this->consignment->dropp_order_id = $dropp_order['id'] ?? '';
			$this->consignment->status         = $dropp_order['status'] ?? '';
		}

		// wp_send_json( [
		// 	'request' => $this->consignment->to_array(),
		// 	'response' => json_decode( $response['body'] ),
		// ] );

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
