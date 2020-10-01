<?php
/**
 * Tracking Code
 *
 * @package dropp-for-woocommerce
 */

namespace Dropp;

use Dropp\Models\Dropp_Consignment;

/**
 * Tracking Code
 */
class Tracking_Code {

	public $consignment;

	/**
	 * Setup
	 */
	public static function setup() {
		add_filter( 'woocommerce_order_shipping_to_display', __CLASS__ . '::tracking_html', 10, 2 );
	}

	/**
	 * Construct
	 * @param Dropp_Consignment $consignment Consignment.
	 */
	public function __construct( Dropp_Consignment $consignment ) {
		$this->consignment = $consignment;
	}

	/**
	 * HTML
	 *
	 * @return string HTML.
	 */
	public function html() {
		$format = '<li><a class="dropp-tracking-codes__item" href="%s" target="_blank">%s</a></li>';
		return sprintf(
			$format,
			$this->get_url() . $this->consignment->dropp_order_id,
			$this->consignment->barcode
		);
	}

	/**
	 * Tracking HTML
	 *
	 * @param  string   $shipping HTML.
	 * @param  WC_Order $order    Order.
	 *
	 * @return string             new HTML.
	 */
	public static function tracking_html( $shipping, $order ) {
		$adapter      = new Order_Adapter( $order );
		$consignments = $adapter->consignments();
		// Remove unwanted statuses.
		$consignments->filter( function( $consignment ) {
			return ! in_array( $consignment->status, ['cancelled', 'error', 'ready'] );
		} );
		if ( empty( $consignments ) ) {
			return $shipping;
		}

		// Get the tracking codes
		$codes = array_map(
			function( $consignment ) {
				$tracking_code = new Tracking_Code( $consignment );
				return $tracking_code->html();
			},
			$consignments->to_array()
		);
		$format = '<div class="dropp-tracking-codes"><strong class="dropp-tracking-codes__title">%s</strong><ul class="dropp-tracking-codes__list">%s</ul></div>';
		$html = sprintf(
			$format,
			esc_html( _n( 'Tracking code', 'Tracking codes', count( $consignments ), 'dropp-for-woocommerce' ) ),
			implode( ' ', $codes )
		);
		return "$shipping $html";
	}

	/**
	 * Get URL
	 *
	 * @return string URL.
	 */
	public function get_url() {
		$url = 'https://api.dropp.is/dropp/tracking/';
		if ( $this->consignment->test ) {
			$url = 'https://stage.dropp.is/dropp/tracking/';
		}
		return $url;
	}
}
