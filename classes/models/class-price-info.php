<?php
/**
 * Price Info
 *
 * @package dropp-for-woocommerce
 */

namespace Dropp\Models;

use Dropp\API;
use Exception;

/**
 * Dropp PDF
 */
class Price_Info extends Model {

	public array $errors = [];

	/**
	 * Construct
	 */
	public function __construct(
		public string $code,
		public float $price,
		public float $max_weight
	) {
	}

	/**
	 * To array
	 *
	 * @return array Array representation.
	 */
	public function to_array(): array {
		return [
			'code' => $this->code,
			'price' => $this->price,
			'max_weight' => $this->max_weight,
		];
	}

	/**
	 * Request
	 *
	 * @return string          This object.
	 * @throws Exception $e     Sending exception.
	 */
	public static function remote_get(): string {
		$api       = new API();

		$response = $api->get( "orders/store/priceinfo", 'json' );
		ray($response);die;

		if ( ! $response['headers'] ) {
			throw new Exception( __( 'Missing response headers', 'dropp-for-woocommerce' ) );
		}

		return $response['body'];
	}
}
