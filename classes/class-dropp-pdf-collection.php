<?php
/**
 * Booking
 *
 * @package dropp-for-woocommerce
 */

namespace Dropp;

use Exception;
/**
 * Dropp PDF
 */
class Dropp_PDF_Collection extends Collection {
	/**
	 * From consignment
	 *
	 * @param  string|integer|Dropp_Consignment $consignment Consignment or consignment ID.
	 * @return Dropp_PDF_Collection                          PDF collection.
	 */
	public static function from_consignment( $consignment ) {
		if ( is_int( $consignment ) || ctype_digit( $consignment ) ) {
			$consignment = Dropp_Consignment::find( $consignment );
		}
		if ( empty( $consignment ) || null === $consignment->id ) {
			throw new Exception( 'Could not find consignment' );
		}
		// Create a new collection.
		$collection = new self();
		$collection->add(
			new Dropp_PDF( $consignment )
		);

		// Get list of consignments from the API.
		$api    = new API( $consignment->get_shipping_method() );
		$result = $api->get( "orders/extrabyorder/{$consignment->dropp_order_id}/" );

		// Add PDF's to collection.
		foreach ( $result['extraOrders'] as $extra_pdf ) {
			$collection->add(
				new Dropp_PDF( $consignment, $extra_pdf['barcode'] )
			);
		}
		return $collection;
	}

	public function get_content() {
		if ( 1 === count( $this->container ) ) {
			return $item->get_content();
		}
		require_once dirname( __DIR__ ) . '/includes/loader.php';
		$merger = new \iio\libmergepdf\Merger;
		foreach ( $this->container as $item ) {
			$file = $item->download()->get_filename();
			$merger->addFile( $file );
		}
		return $merger->merge();
	}
}
