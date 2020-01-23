<?php
/**
 * Customer
 *
 * @package woocommerce-dropp-shipping
 */

namespace Dropp;

/**
 * Dropp Customer
 */
class Dropp_Customer {

	public $name;
	public $email_address;
	public $address;
	public $social_security_number;
	public $phone_number;

	/**
	 * Constructor
	 */
	public function __construct() {
	}

	/**
	 * Json decode
	 *
	 * @param  string              $json JSON string.
	 * @return Dropp_Customer|null       Customer object.
	 */
	public static function json_decode( $json ) {
		$data = json_decode( $json, true );

		if ( ! is_array( $data ) || empty( $data ) ) {
			return null;
		}

		return ( new self() )->fill( $data );
	}

	/**
	 * To array
	 *
	 * @return array             Customer array.
	 */
	public function fill( $data ) {
		$data = wp_parse_args(
			$data,
			[
				'name'                 => '',
				'emailAddress'         => '',
				'socialSecurityNumber' => '1234567890',
				'address'              => '',
				'phoneNumber'          => '',
			]
		);
		$this->name                   = $data['name'];
		$this->email_address          = $data['emailAddress'];
		$this->social_security_number = $data['socialSecurityNumber'];
		$this->address                = $data['address'];
		$this->phone_number           = $data['phoneNumber'];
		return $this;
	}

	/**
	 * To array
	 *
	 * @return array             Customer array.
	 */
	public function to_array() {
		return [
			'name'                 => $this->name,
			'emailAddress'         => $this->email_address,
			'socialSecurityNumber' => $this->social_security_number,
			'address'              => $this->address,
			'phoneNumber'          => $this->phone_number,
		];
	}
}
