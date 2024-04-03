<?php
/**
 * Booking
 *
 * @package dropp-for-woocommerce
 */

namespace Dropp;

use Dropp\Actions\Get_Plugin_Data_From_File;
use Dropp\Models\Dropp_Return_PDF;
use Exception;
use Dropp\Models\Dropp_PDF;
use Dropp\Models\Dropp_Consignment;
use iio\libmergepdf\Merger;
use WP_Error;

/**
 * Dropp PDF
 */
class Dropp_PDF_Collection extends Collection {
	public array $errors;

	/**
	 * From consignment
	 *
	 * @param string|integer|Dropp_Consignment $consignment Consignment or consignment ID.
	 *
	 * @return Dropp_PDF_Collection                                         PDF collection.
	 * @throws Exception
	 */
	public static function from_consignment( $consignment ): Dropp_PDF_Collection {
		if ( is_int( $consignment ) || ctype_digit( $consignment ) ) {
			$consignment = Dropp_Consignment::find( $consignment );
		}
		if (empty( $consignment )) {
			throw new Exception( 'Could not find consignment' );
		}
		// Create a new collection.
		$collection = new self();
		$collection->add(
			new Dropp_PDF( $consignment )
		);

		if ($consignment->return_barcode) {
			$collection->add(
				new Dropp_Return_PDF( $consignment )
			);
		}

		// Get list of consignments from the API.
		$api    = new API();
		$result = $api->get( "orders/extrabyorder/{$consignment->dropp_order_id}/" );

		// Add PDF's to collection.
		foreach ( $result['extraOrders'] as $extra_pdf ) {
			$collection->add(
				new Dropp_PDF( $consignment, $extra_pdf['barcode'] )
			);
		}
		return $collection;
	}

	public function get_content(): string {
		if ( 1 === count( $this->items ) ) {
			/** @var Dropp_PDF $item */
			$item = reset( $this->items );
			return $item->get_content();
		}
		// Check if other plugins use TCPDF first
		if (class_exists('TCPDF')) {
			// Attempt to call php merger script via shell
			/** @var Dropp_PDF $item */
			$buffer = [];
			foreach ($this->items as $item) {
				$item->get_content(); // download file
				$buffer[] = $item->get_filename();
			}
			// Script file
			$filename = escapeshellarg(dirname(__DIR__) . '/bin/merger.php');
			$names = escapeshellarg(implode(' ', $buffer));
			$phpPath = Options::get_instance()->php_path;
			if (! file_exists($phpPath)) {

				$reflector = new \ReflectionClass('TCPDF');

				$tcpdfFile = $reflector->getFileName();
				$pluginDir = realpath(WP_PLUGIN_DIR);
				if (str_starts_with($tcpdfFile, $pluginDir)) {
					$pluginData = (new Get_Plugin_Data_From_File)($tcpdfFile);
					wp_die(
						sprintf(
							esc_html__(
								'Another plugin, %s, is using the TCPDF library and a subprocess could not be started because the php path is invalid. Please go to the dropp settings and enter the path to the php binary, or disable the other plugin that is using TCPDF. File path for the current active TCPDF: %s',
								'dropp-for-woocommerce'
							),
							$pluginData['Name'] ?? '???',
							$tcpdfFile
						),
					);
				} else {
					wp_die(
						esc_html__(
							'The TCPDF library has already been included and a subprocess could not be started because the php path is invalid. Please go to the dropp settings and enter the path to the php binary.',
							'dropp-for-woocommerce'
						),
					);
				}
			}
			$phpPath = escapeshellarg($phpPath);
			$command = "$phpPath $filename $names";
			return shell_exec($command);
		}

		require_once dirname( __DIR__ ) . '/includes/loader.php';
		$merger = new Merger;
		foreach ($this->items as $item ) {
			/** @var Dropp_PDF $item */
			$merger->addRaw( $item->get_content() );
		}
		return $merger->merge();
	}
}
