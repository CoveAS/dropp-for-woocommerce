<?php
/**
 * API
 *
 * @package dropp-for-woocommerce
 */

namespace Dropp;

use Exception;
use WC_Log_Levels;
use WC_Logger;

/**
 * API
 */
class API {
	public $require_auth = true;
	public $test         = false;
	public $debug        = false;
	public $errors       = [];
	protected $shipping_method;

	public function __construct( $shipping_method = null ) {
		if ( empty( $shipping_method ) ) {
			$shipping_method = new Shipping_Method\Dropp;
		}
		$this->shipping_method = $shipping_method;

		$this->test  = $shipping_method->test_mode;
		$this->debug = $shipping_method->debug_mode;
	}

	/**
	 * No Authentization
	 *
	 * @return API This object
	 */
	public function noauth() {
		$this->require_auth = false;
		return $this;
	}

	/**
	 * Get API key
	 *
	 * @throws Exception When API key is not available.
	 * @return string API key.
	 */
	public function get_api_key() {
		$option_name     = 'api_key';
		if ( $this->test ) {
			$option_name = 'api_key_test';
		}
		$api_key = $this->shipping_method->get_option( $option_name );
		if ( $this->require_auth && empty( $api_key ) ) {
			throw new Exception( __( 'No API key could be found.', 'dropp-for-woocommerce' ), 1 );
		}
		return $api_key;
	}

	/**
	 * Get URL
	 *
	 * @param  string $endpoint Endpoint.
	 * @return string URL.
	 */
	public function endpoint_url( $endpoint ) {
		$baseurl = 'https://api.dropp.is/dropp/api/v1/';
		if ( $this->test ) {
			$baseurl = 'https://stage.dropp.is/dropp/api/v1/';
		}
		return $baseurl . $endpoint;
	}

	/**
	 * Remote get
	 *
	 * @param  string $endpoint  Endpoint.
	 * @param  string $data_type (optional) 'json', 'body' or 'raw'.
	 * @return array|string      Decoded json, string body or raw response object.
	 */
	public function get( $endpoint, $data_type = 'json' ) {
		$response = $this->remote( 'get', self::endpoint_url( $endpoint ) );
		return $this->process_response( 'get', $response, $data_type );
	}

	/**
	 * Remote post
	 *
	 * @param  string      $endpoint  Endpoint.
	 * @param  Dropp\Model $model     Model.
	 * @param  string      $data_type (optional) 'json', 'body' or 'raw'.
	 * @return array|string           Decoded json, string body or raw response object.
	 */
	public function post( $endpoint, Model $model, $data_type = 'json' ) {
		$response = $this->remote( 'post', self::endpoint_url( $endpoint ), $model );
		return $this->process_response( 'post', $response, $data_type );
	}

	/**
	 * Remote args
	 *
	 * @throws Exception           Unknown method.
	 * @param  string      $method Remote method, either 'get' or 'post'.
	 * @param  string      $url    Url.
	 * @param  Dropp\Model $model  Model.
	 * @return array               Remote arguments.
	 */
	public function remote( $method, $url, Model $model = null ) {
		$log  = new WC_Logger();
		$args = [
			'headers' => [
				'Content-Type'  => 'application/json;charset=UTF-8',
			],
		];
		if ( $this->require_auth ) {
			$args['headers']['Authorization'] = 'Basic ' . $this->get_api_key();
		}

		$allowed_methods = [ 'get', 'post', 'delete', 'patch' ];
		if ( ! in_array( $method, $allowed_methods, true ) ) {
			throw new Exception( "Unknown method, \"$method\"" );
		}
		$args['method'] = strtoupper( $method );
		if ( 'delete' === $method ) {
			$args['method'] = 'DELETE';
		}
		if ( 'patch' === $method || 'post' === $method ) {
			$args['body'] = wp_json_encode( $model->to_array() ?? '' );
		}
		if ( $this->debug ) {
			$log->add(
				'dropp-for-woocommerce',
				'[DEBUG] Remote ' . strtoupper( $method ) . ' request:' . PHP_EOL . $url . PHP_EOL . wp_json_encode( $args, JSON_PRETTY_PRINT ),
				WC_Log_Levels::DEBUG
			);
		}
		return wp_remote_request( $url, $args );
	}

	/**
	 * Process response
	 *
	 * @throws Exception      $e         Response exception.
	 * @param  string         $method    Remote method, either 'get' or 'post'.
	 * @param  WP_Error|array $response  Array with response data on success.
	 * @param  string         $data_type (optional) 'json', 'body' or 'raw'
	 * @return array|string              Decoded json, string body or raw response object.
	 */
	protected function process_response( $method, $response, $data_type = 'json' ) {
		$log = new WC_Logger();
		if ( is_wp_error( $response ) ) {
			$log->add(
				'dropp-for-woocommerce',
				'[ERROR] Remote ' . strtoupper( $method ) . ' response:' . PHP_EOL . wp_json_encode( $response->errors, JSON_PRETTY_PRINT ),
				WC_Log_Levels::ERROR
			);
		} elseif ( $this->debug ) {
			$data = json_decode( $response['body'] );
			if ( ! empty( $data ) ) {
				$body = wp_json_encode( $data, JSON_PRETTY_PRINT );
			} else {
				$body = $response['body'];
			}
			if ( 'raw' === $data_type ) {
				if ( 100 < strlen( $body ) ) {
					$body = substr( $body, 0, 100 ) . '...';
				}
				$body = htmlspecialchars( $body );
			}
			$log->add(
				'dropp-for-woocommerce',
				'[DEBUG] Remote ' . strtoupper( $method ) . ' response:' . PHP_EOL . $body,
				WC_Log_Levels::DEBUG
			);
		}

		// Validate response.
		if ( is_wp_error( $response ) ) {
			$this->errors = $response->get_error_messages();
			throw new Exception( __( 'Response error', 'dropp-for-woocommerce' ) );
		}

		if ( 'raw' === $data_type ) {
			return $response;
		}

		if ( 'body' === $data_type ) {
			return $response['body'];
		}

		$data = json_decode( $response['body'], true );
		if ( ! is_array( $data ) ) {
			$this->errors['invalid_json'] = $response['body'];
			throw new Exception( __( 'Invalid json', 'dropp-for-woocommerce' ) );
		}

		return $data;
	}
}
