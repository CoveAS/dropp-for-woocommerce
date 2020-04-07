<?php
/**
 * Booking
 *
 * @package dropp-for-woocommerce
 */

namespace Dropp;

use WC_Logger;
use WC_Log_Levels;
use Exception;

/**
 * Dropp PDF
 */
class Dropp_PDF {

	protected $test = false;
	protected $debug = false;
	protected $consignment;

	public $errors = [];

	/**
	 * Construct
	 */
	public function __construct( Dropp_Consignment $consignment, $test, $debug ) {
		$this->consignment = $consignment;
		$this->test        = $test;
		$this->debug       = $debug;
	}

	/**
	 * Request
	 *
	 * @throws Exception $e     Sending exception.
	 * @param  Boolean   $debug Debug.
	 * @return Booking          This object.
	 */
	public function remote_get() {
		if ( empty( $this->consignment ) ) {
			throw new Exception( 'Error Processing Request', 1 );
		}
		$log  = new WC_Logger();
		$args = [
			'headers' => [
				'Authorization' => 'Basic ' . $this->consignment->get_api_key(),
				'Content-Type'  => 'application/json;charset=UTF-8',
			],
		];
		$url = $this->get_url( 'orders/pdf' ) . '/' . $this->consignment->dropp_order_id;
		if ( $this->debug ) {

			$log->add(
				'dropp-for-woocommerce',
				'[DEBUG] PDF request:' . PHP_EOL . $url . PHP_EOL . wp_json_encode( $args, JSON_PRETTY_PRINT ),
				WC_Log_Levels::DEBUG
			);
		}

		$response = wp_remote_get( $url, $args );

		if ( is_wp_error( $response ) ) {
			$log->add(
				'dropp-for-woocommerce',
				'[ERROR] PDF response:' . PHP_EOL . wp_json_encode( $response->errors, JSON_PRETTY_PRINT ),
				WC_Log_Levels::ERROR
			);
		} elseif ( $this->debug ) {
			$data = json_decode( $response['body'] );
			if ( ! empty( $data ) ) {
				$body = wp_json_encode( $data, JSON_PRETTY_PRINT );
			} else {
				$body = $response['body'];
			}
			$log->add(
				'dropp-for-woocommerce',
				'[DEBUG] PDF response:' . PHP_EOL . $body,
				WC_Log_Levels::DEBUG
			);
		}

		if ( is_wp_error( $response ) ) {
			$this->errors = $response->get_error_messages();
			throw new Exception( __( 'Response error', 'dropp-for-woocommerce' ) );
		}
		if ( ! $response['headers'] ) {
			throw new Exception( __( 'Missing response headers', 'dropp-for-woocommerce' ) );
		}
		if ( 'application/json' === $response['headers']->offsetGet( 'content-type' ) ) {
			$data = json_decode( $response['body'] , true );
			$this->errors[] = $data['error'];
			throw new Exception( __( 'API Error', 'dropp-for-woocommerce' ) );
		}
		if ( 'application/pdf' !== $response['headers']->offsetGet( 'content-type' ) ) {
			throw new Exception( __( 'Invalid json', 'dropp-for-woocommerce' ) );
		}

		return $response['body'];
	}
	/**
	 * Get filename
	 *
	 * @return  string Filename.
	 */
	public function get_filename() {
		$uploads_dir = self::get_dir();
		$filename    = $uploads_dir['subdir'] . '/' . $this->consignment->dropp_order_id . '.pdf';
		return $filename;
	}

	/**
	 * Download
	 *
	 * @throws Exception $e        Sending exception.
	 * @param  Boolean   $this->debug    Debug.
	 * @return Dropp_PDF             This object.
	 */
	public function download() {
		global $wp_filesystem;

		require_once ABSPATH . '/wp-admin/includes/file.php';
		WP_Filesystem();
		$filename = $this->get_filename();
		if ( ! $wp_filesystem->exists( $filename ) ) {
			$pdf = $this->remote_get();
			$wp_filesystem->put_contents( $filename, $pdf );
		}
		return $this;
	}

	/**
	 * Get content.
	 *
	 * First attempts to get a downloaded PDF, then tries to get from remote.
	 *
	 * @throws Exception $e        Sending exception.
	 * @return string              PDF content.
	 */
	public function get_content() {
		global $wp_filesystem;

		require_once ABSPATH . '/wp-admin/includes/file.php';
		WP_Filesystem();

		$uploads_dir = self::get_dir();
		$filename = $this->get_filename();
		if ( ! $wp_filesystem->exists( $filename ) ) {
			return $this->remote_get();
		}
		return $wp_filesystem->get_contents( $filename );
	}

	/**
	 * Get pdf from consignment
	 *
	 * @param  string $consignment_id Consignment ID.
	 * @return string                 PDF content.
	 */
	public static function get_pdf_from_consignment( $consignment_id ) {
		$consignment = Dropp_Consignment::find( $consignment_id );
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
		$api_pdf         = new self( $consignment, $shipping_method->test_mode, $shipping_method->debug_mode );
		return $api_pdf;
	}

	/**
	 * Get URL
	 *
	 * @return string URL.
	 */
	public function get_url( $endpoint ) {
		$baseurl = 'https://api.dropp.is/dropp/api/v1/';
		if ( $this->test ) {
			$baseurl = 'https://stage.dropp.is/dropp/api/v1/';
		}

		return $baseurl . $endpoint;
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
			$uploads_dir['error'] = __( 'Could not create directory', 'dropp-for-woocommerce' ) . ", \"$dir\"";
			return $uploads_dir;
		}
		$year                   = gmdate( 'Y' );
		$uploads_dir['subdir'] .= "/$year";
		$uploads_dir['url']    .= "/$year";
		$uploads_dir['path']   .= "/$year";
		$dir                    = $uploads_dir['subdir'];
		if ( ! is_dir( $dir ) && ! mkdir( $dir ) ) {
			$uploads_dir['error'] = __( 'Could not create directory', 'dropp-for-woocommerce' ) . ", \"$dir\"";
			return $uploads_dir;
		}

		$month                  = gmdate( 'm' );
		$uploads_dir['subdir'] .= "/$month";
		$uploads_dir['url']    .= "/$month";
		$uploads_dir['path']   .= "/$month";
		$dir                    = $uploads_dir['subdir'];
		if ( ! is_dir( $dir ) && ! mkdir( $dir ) ) {
			$uploads_dir['error'] = __( 'Could not create directory', 'dropp-for-woocommerce' ) . ", \"$dir\"";
			return $uploads_dir;
		}
		return $uploads_dir;
	}
}
