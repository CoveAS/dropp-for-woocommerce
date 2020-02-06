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
 * API PDF
 */
class API_PDF {

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
	 * Request
	 *
	 * @throws Exception $e     Sending exception.
	 * @param  Boolean   $debug Debug.
	 * @return Booking          This object.
	 */
	public function remote_get( $debug = false ) {
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
				'[DEBUG] PDF request:' . PHP_EOL . $url . PHP_EOL . wp_json_encode( $args, JSON_PRETTY_PRINT ),
				WC_Log_Levels::DEBUG
			);
		}

		// @TODO: Debug mode - log request
		$response = wp_remote_get(
			self::get_url() . $this->consignment->dropp_order_id,
			$args
		);

		if ( is_wp_error( $response ) ) {
			$log->add(
				'woocommerce-dropp-shipping',
				'[ERROR] PDF response:' . PHP_EOL . wp_json_encode( $response->errors, JSON_PRETTY_PRINT ),
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
				'[DEBUG] PDF response:' . PHP_EOL . $body,
				WC_Log_Levels::DEBUG
			);
		}

		// @TODO: Debug mode - log response
		$this->validate_response( $response );

		return $response['body'];
	}


	/**
	 * Download
	 *
	 * @throws Exception $e        Sending exception.
	 * @param  Boolean   $debug    Debug.
	 * @param  string    $filename Filename.
	 * @return Booking             This object.
	 */
	public function download( $debug = false ) {
		global $wp_filesystem;

		require_once ABSPATH . '/wp-admin/includes/file.php';
		WP_Filesystem();
		$uploads_dir = self::get_dir();
		$filename    = $uploads_dir['subdir'] . $this->consignment->dropp_order_id . '.pdf';
		if ( ! $wp_filesystem->exists( $filename ) ) {
			$pdf = $this->remote_get( $debug );
			$wp_filesystem->put_contents( $filename, $pdf );
		}
		return $this;
	}
	/**
	 * Download
	 *
	 * @throws Exception $e        Sending exception.
	 * @param  Boolean   $debug    Debug.
	 * @param  string    $filename Filename.
	 * @return Booking             This object.
	 */
	public function get_pdf( $debug = false ) {
		global $wp_filesystem;

		require_once ABSPATH . '/wp-admin/includes/file.php';
		WP_Filesystem();

		$uploads_dir = self::get_dir();
		$filename    = $uploads_dir['subdir'] . $this->consignment->dropp_order_id . '.pdf';
		if ( ! $wp_filesystem->exists( $filename ) ) {
			return $this->remote_get();
		}
		return $wp_filesystem->get_contents( $filename );
	}

	/**
	 * Get URL
	 *
	 * @return string URL.
	 */
	public function get_url() {
		if ( $this->test ) {
			return 'https://stage.dropp.is/dropp/api/v1/orders/pdf/';
		}
		return 'https://api.dropp.is/dropp/api/v1/orders/pdf/';
	}

	/**
	 * Validate response
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
		if ( ! $response['headers'] ) {
			throw new Exception( __( 'Missing response headers', 'woocommerce-dropp-shipping' ) );
		}
		if ( 'application/json' === $response['headers']->offsetGet( 'content-type' ) ) {
			$data = json_decode( $response->body , true );
			$this->errors[] = $data['error'];
			throw new Exception( __( 'API Error', 'woocommerce-dropp-shipping' ) );
		}
		if ( 'application/pdf' !== $response['headers']->offsetGet( 'content-type' ) ) {
			throw new Exception( __( 'Invalid json', 'woocommerce-dropp-shipping' ) );
		}
		return true;
	}

	/**
	 * Get dir
	 *
	 * @return array
	 */
	public static function get_dir() {
		$uploads_dir = wp_upload_dir();
		if ( $uploads_dir['error'] ) {
			return $uploads_dir;
		}

		$uploads_dir['baseurl'] .= '/dropp-labels';
		$uploads_dir['basedir'] .= '/dropp-labels';
		$uploads_dir['subdir']   = $uploads_dir['basedir'];
		$uploads_dir['path']     = $uploads_dir['basedir'];
		$uploads_dir['url']      = $uploads_dir['baseurl'];

		$dir = $uploads_dir['basedir'];
		if ( ! is_dir( $dir ) && ! mkdir( $dir ) ) {
			$uploads_dir['error'] = __( 'Could not create directory', 'woocommerce-dropp-shipping' ) . ", \"$dir\"";
			return $uploads_dir;
		}
		$year                   = gmdate( 'Y' );
		$uploads_dir['subdir'] .= "/$year";
		$uploads_dir['url']    .= "/$year";
		$uploads_dir['path']   .= "/$year";
		$dir                    = $uploads_dir['subdir'];
		if ( ! is_dir( $dir ) && ! mkdir( $dir ) ) {
			$uploads_dir['error'] = __( 'Could not create directory', 'woocommerce-dropp-shipping' ) . ", \"$dir\"";
			return $uploads_dir;
		}

		$month                  = gmdate( 'm' );
		$uploads_dir['subdir'] .= "/$month";
		$uploads_dir['url']    .= "/$month";
		$uploads_dir['path']   .= "/$month";
		$dir                    = $uploads_dir['subdir'];
		if ( ! is_dir( $dir ) && ! mkdir( $dir ) ) {
			$uploads_dir['error'] = __( 'Could not create directory', 'woocommerce-dropp-shipping' ) . ", \"$dir\"";
			return $uploads_dir;
		}
		return $uploads_dir;
	}
}
