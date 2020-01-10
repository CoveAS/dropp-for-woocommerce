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

	public $id;
	public $dropp_order_id;
	public $barcode;
	public $status;
	public $shipping_item_id;
	public $location_id;
	public $products;
	public $customer;
	public $created_at;
	public $updated_at;

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
		$this->products         = [];
		foreach ( $content['products'] as $product ) {
			$this->products[] = ( new Dropp_Product_Line() )->fill( $product );
		}
		return $this;
	}

	/**
	 * To array
	 *
	 * @return array Array representation.
	 */
	public function to_array( $for_request = true ) {
		$products = [];
		foreach ( $this->products as $product ) {
			$products[] = $product->to_array();
		}
		$consignment_array = [
			'locationId' => $this->location_id,
			'barcode'    => $this->barcode,
			'products'   => $products,
			'customer'   => [
				'name'                 => $this->customer['name'],
				'emailAddress'         => $this->customer['emailAddress'],
				'socialSecurityNumber' => "1234567890",
				'address'              => $this->customer['address'],
				'phoneNumber'          => $this->customer['phoneNumber'],
			],
		];
		if ( ! $for_request ) {
			$consignment_array['id']               = $this->id;
			$consignment_array['status']           = $this->status;
			$consignment_array['dropp_order_id']   = $this->dropp_order_id;
			$consignment_array['shipping_item_id'] = $this->shipping_item_id;
		}
		return $consignment_array;
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

	public function save() {
		if ( $this->id ) {
			// update
		} else {
		}
			$this->insert();
	}
	protected function insert() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'dropp_consignments';
		$row_count = $wpdb->insert(
			$table_name,
			[
				'dropp_order_id'   => $this->dropp_order_id,
				'shipping_item_id' => $this->shipping_item_id,
				'location_id'      => $this->location_id,
				'products'         => wp_json_encode( $this->products ),
				'status'           => $this->status,
				'customer'         => wp_json_encode( $this->customer ),
				'updated_at'       => current_time( 'mysql' ),
				'created_at'       => current_time( 'mysql' ),
			]
		);

		var_dump(
			$row_count,
			[
				'dropp_order_id'   => $this->dropp_order_id,
				'shipping_item_id' => $this->shipping_item_id,
				'location_id'      => $this->location_id,
				'status'      => $this->status,
				'products'         => wp_json_encode( $this->products ),
				'customer'         => wp_json_encode( $this->customer ),
				'updated_at'       => current_time( 'mysql' ),
				'created_at'       => current_time( 'mysql' ),
			]
		);
	}
}
