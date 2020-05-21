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
	 * Request
	 *
	 * @throws Exception $e     Sending exception.
	 * @param  Boolean   $debug Debug.
	 * @return Booking          This object.
	 */
	public function remote_get() {
		$api       = new API( $this->consignment->get_shipping_method() );
		$api->test = $this->consignment->test;

		$endpoint = "orders/pdf/{$this->consignment->dropp_order_id}";
		if ( $this->barcode ) {
			$endpoint = "web/pdf/getpdf/{$this->consignment->dropp_order_id}/{$this->barcode}/";
		}
		var_dump( $endpoint );
		$response   = $api->get( $endpoint, 'raw' );
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
		if ( $this->barcode ) {
			$filename = $uploads_dir['subdir'] . '/' . $this->consignment->dropp_order_id . '-' . $this->barcode . '.pdf';
		}
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
