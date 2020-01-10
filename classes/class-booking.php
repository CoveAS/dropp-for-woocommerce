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
	 * @throws Exception $e Sending exception.
	 * @return Booking      This object.
	 */
	public function send() {
		if ( empty( $this->consignment ) ) {
			throw new \Exception( 'Error Processing Request', 1 );
		}
		$response = wp_remote_post(
		// $response = wp_send_json(
			self::URL,
			[
				'headers' => [
					'Authorization' => 'Basic ' . base64_encode( 'woocomm@dropp.is'. $this->consignment->get_api_key() ),
					'Content-Type'  => 'application/json;charset=UTF-8',
				],
				'body' => $this->consignment->to_array(),
			]
		);

		wp_send_json( $response );

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
