<?php
/**
 * Consignment
 *
 * @package woocommerce-dropp-shipping
 */

namespace Dropp;

/**
 * Consignment
 */
class Dropp_Consignment {

	protected $order_id;
	protected $location;
	protected $id;

	public $location_id;
	public $barcode;
	public $products;
	public $customer;

	/**
	 * Constructor.
	 */
	public function __construct() {
	}

	/**
	 * Fill
	 *
	 * @param  array            $content Content.
	 * @return Dropp_Consigment          This object.
	 */
	public function fill( $content ) {
		$this->shipping_item_id = $content['shipping_item_id'];
		$this->location_id      = $content['location_id'];
		$this->barcode          = $content['barcode'];
		$this->customer         = $content['customer'];
		$this->products         = $content['products'];
		return $this;
	}

	public function to_array() {
		return [
			'locationId' => "911a8ff4-a35a-4da3-ac3a-1797929242f8",
			'barcode' => "WCORDER-117",
			'products' => [
				'0' => [
					'id' => "114",
					'name' => "Beanie with Logo",
					'quantity' => "1",
					'barcode' => "Woo-beanie-logo",
				],
			],
			'customer' => [
				'name' => "Eivin Landa",
				'emailAddress' => "landa@drivdigital.no",
				'socialSecurityNumber' => "",
				'address' => "Blødekjær, 20, 20, 4844 Arendal",
				'phoneNumber' => "0901 234 5789",
			],
		];
	}

	public function get_api_key() {
		$shipping_item   = new \WC_Order_Item_Shipping( $this->shipping_item_id );
		$instance_id     = $shipping_item->get_instance_id();
		$shipping_method = new Shipping_Method( $instance_id );
		$api_key         = $shipping_method->get_option( 'api_key' );
		if ( empty( $api_key ) ) {
			throw new \Exception( __( 'No API key could be found.', 'woocommerce-dropp-shipping' ), 1 );
		}
		return $api_key;
	}

	protected function insert() {
		$table_name = $wpdb->prefix . 'dropp_consignments';
		$wpdb->insert(
			$table_name,
			[
				'shipping_item_id' => $this->shipping_item_id,
				'location_id'      => $this->location_id,
				'products'         => wp_json_encode( $this->products ),
				'customer'         => wp_json_encode( $this->customer ),
				'updated_at'       => current_time( 'mysql' ),
				'created_at'       => current_time( 'mysql' ),
			]
		);
	}
}
