<?php
/**
 * Booking
 *
 * @package dropp-for-woocommerce
 */

namespace Dropp\Models;

use Dropp\API;
use Exception;
use WC_Log_Levels;
use WC_Logger;

/**
 * Dropp PDF
 */
class Dropp_PDF extends Model {

	protected $barcode = false;
	public $consignment;

	public $errors = [];

	/**
	 * Construct
	 */
	public function __construct( Dropp_Consignment $consignment, $barcode = false ) {
		$this->consignment = $consignment;
		$this->barcode     = $barcode;
	}

	/**
	 * To array
	 *
	 * @return array Array representation.
	 */
	public function to_array() {
		return [
			'barcode' => $this->barcode,
		];
	}

	protected function get_endpoint() {
		if ( $this->barcode ) {
			return "web/pdf/getpdf/{$this->consignment->dropp_order_id}/{$this->barcode}/";
		}

		return "orders/pdf/{$this->consignment->dropp_order_id}";
	}

	/**
	 * Request
	 *
	 * @param Boolean $debug Debug.
	 *
	 * @return Booking          This object.
	 * @throws Exception $e     Sending exception.
	 */
	public function remote_get() {
		$api       = new API();
		$api->test = $this->consignment->test;

		$endpoint = $this->get_endpoint();
		$response = $api->get( $endpoint, 'raw' );
		if ( ! $response['headers'] ) {
			throw new Exception( __( 'Missing response headers', 'dropp-for-woocommerce' ) );
		}
		if ( 'application/json' === $response['headers']->offsetGet( 'content-type' ) ) {
			$data           = json_decode( $response['body'], true );
			$this->errors[] = $data['error'];
			throw new Exception( __( 'API Error', 'dropp-for-woocommerce' ) );
		}
		if ( 'application/pdf' !== $response['headers']->offsetGet( 'content-type' ) ) {
			throw new Exception( __( 'Invalid PDF', 'dropp-for-woocommerce' ) );
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
		if ( $this->barcode ) {
			$filename = $uploads_dir['subdir'] . '/' . $this->consignment->dropp_order_id . '-' . $this->barcode . '.pdf';
		}

		return $filename;
	}

	/**
	 * Download
	 *
	 * @param Boolean $this- >debug    Debug.
	 *
	 * @return Dropp_PDF             This object.
	 * @throws Exception $e        Sending exception.
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
	 * @return string              PDF content.
	 * @throws Exception $e        Sending exception.
	 */
	public function get_content() {
		global $wp_filesystem;

		require_once ABSPATH . '/wp-admin/includes/file.php';
		WP_Filesystem();

		$uploads_dir = self::get_dir();
		$filename    = $this->get_filename();
		if ( ! $wp_filesystem->exists( $filename ) ) {
			$this->download();
		}
		if ( ! $wp_filesystem->exists( $filename ) ) {
			return $this->remote_get();
		}

		return $wp_filesystem->get_contents( $filename );
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
		$uploads_dir['subdir']  = $uploads_dir['basedir'];
		$uploads_dir['path']    = $uploads_dir['basedir'];
		$uploads_dir['url']     = $uploads_dir['baseurl'];

		$dir = $uploads_dir['basedir'];
		if ( ! is_dir( $dir ) && ! mkdir( $dir ) ) {
			$uploads_dir['error'] = __( 'Could not create directory', 'dropp-for-woocommerce' ) . ", \"$dir\"";

			return $uploads_dir;
		}
		$year                  = gmdate( 'Y' );
		$uploads_dir['subdir'] .= "/$year";
		$uploads_dir['url']    .= "/$year";
		$uploads_dir['path']   .= "/$year";
		$dir                   = $uploads_dir['subdir'];
		if ( ! is_dir( $dir ) && ! mkdir( $dir ) ) {
			$uploads_dir['error'] = __( 'Could not create directory', 'dropp-for-woocommerce' ) . ", \"$dir\"";

			return $uploads_dir;
		}

		$month                 = gmdate( 'm' );
		$uploads_dir['subdir'] .= "/$month";
		$uploads_dir['url']    .= "/$month";
		$uploads_dir['path']   .= "/$month";
		$dir                   = $uploads_dir['subdir'];
		if ( ! is_dir( $dir ) && ! mkdir( $dir ) ) {
			$uploads_dir['error'] = __( 'Could not create directory', 'dropp-for-woocommerce' ) . ", \"$dir\"";

			return $uploads_dir;
		}

		return $uploads_dir;
	}
}
