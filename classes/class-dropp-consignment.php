<?php
/**
 * Consignment
 *
 * @package woocommerce-dropp-shipping
 */

namespace Dropp;

use Exception;

/**
 * Consignment
 */
class Dropp_Consignment {

	public $id;
	public $barcode = '';
	public $dropp_order_id;
	public $status = 'ready';
	public $shipping_item_id;
	public $location_id;
	public $products;
	public $customer;
	public $test = false;
	public $updated_at;
	public $created_at;

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
		if ( ! empty( $content['id'] ) ) {
			$this->id = (int) $content['id'];
		}
		$content = wp_parse_args(
			$content,
			[
				'barcode'          => null,
				'dropp_order_id'   => null,
				'shipping_item_id' => null,
				'status'           => 'ready',
				'location_id'      => null,
				'test'             => false,
				'created_at'       => current_time( 'mysql' ),
				'updated_at'       => current_time( 'mysql' ),
			]
		);

		$this->barcode          = $content['barcode'];
		$this->dropp_order_id   = $content['dropp_order_id'];
		$this->shipping_item_id = $content['shipping_item_id'];
		$this->status           = $content['status'];
		$this->location_id      = $content['location_id'];
		$this->test             = $content['test'];
		$this->created_at       = $content['created_at'];
		$this->updated_at       = $content['updated_at'];

		// Process customer.
		if ( $content['customer'] instanceof Dropp_Customer ) {
			$this->customer = $content['customer'];
		} elseif ( is_array( $content['customer'] ) ) {
			$this->customer = new Dropp_Customer();
			$this->customer->fill( $content['customer'] );
		} elseif ( is_string( $content['customer'] ) ) {
			$this->customer = Dropp_Customer::json_decode( $content['customer'] );
		} else {
			$this->customer = new Dropp_Customer();
		}

		// pre-process product lines.
		$this->products = [];
		$products       = [];
		if ( is_array( $content['products'] ) ) {
			$products = $content['products'];
		} elseif ( is_string( $content['products'] ) ) {
			$products = json_decode( $content['products'], true );
		}

		// Fill products.
		if ( is_array( $products ) ) {
			foreach ( $products as $product ) {
				$this->products[] = ( new Dropp_Product_Line() )->fill( $product );
			}
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
			'customer'   => $this->get_customer_array(),
		];
		if ( ! $for_request ) {
			$consignment_array['id']               = $this->id;
			$consignment_array['status']           = $this->status;
			$consignment_array['dropp_order_id']   = $this->dropp_order_id;
			$consignment_array['shipping_item_id'] = $this->shipping_item_id;
			$consignment_array['test']             = $this->test;
			$consignment_array['created_at']       = $this->created_at;
			$consignment_array['updated_at']       = $this->updated_at;
		}
		return $consignment_array;
	}


	/**
	 * Get API key
	 *
	 * @throws Exception When API key is not available.
	 * @return string API key.
	 */
	public function get_api_key() {
		$shipping_item   = new \WC_Order_Item_Shipping( $this->shipping_item_id );
		$instance_id     = $shipping_item->get_instance_id();
		$shipping_method = new Shipping_Method( $instance_id );
		$option_name     = 'api_key';
		if ( $this->test ) {
			$option_name = 'api_key_test';
		}
		$api_key = $shipping_method->get_option( $option_name );
		if ( empty( $api_key ) ) {
			throw new Exception( __( 'No API key could be found.', 'woocommerce-dropp-shipping' ), 1 );
		}
		return $api_key;
	}

	/**
	 * Get
	 *
	 * @param  int               $id ID.
	 * @return Dropp_Consignment     This.
	 */
	public function get( $id ) {
		global $wpdb;
		$sql = $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}dropp_consignments WHERE id = %d",
			$id
		);
		$row = $wpdb->get_row( $sql, ARRAY_A );
		if ( ! empty( $row ) ) {
			$this->fill( $row );
		}
		return $this;
	}

	/**
	 * Save
	 *
	 * @return Dropp_Consignment This.
	 */
	public function save() {
		if ( ! empty( $this->id ) ) {
			$this->update();
		} else {
			$this->insert();
		}
		return $this;
	}

	/**
	 * Update
	 *
	 * @return Dropp_Consignment This.
	 */
	protected function update() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'dropp_consignments';
		$row_count = $wpdb->update(
			$table_name,
			[
				'barcode'          => $this->barcode,
				'dropp_order_id'   => $this->dropp_order_id,
				'shipping_item_id' => $this->shipping_item_id,
				'location_id'      => $this->location_id,
				'products'         => wp_json_encode( $this->products ),
				'status'           => $this->status,
				'customer'         => wp_json_encode( $this->get_customer_array() ),
				'test'             => $this->test,
				'updated_at'       => current_time( 'mysql' ),
			],
			[
				'id' => $this->id,
			]
		);
		return $this;
	}

	/**
	 * Insert
	 *
	 * @return Dropp_Consignment This.
	 */
	protected function insert() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'dropp_consignments';
		$row_count = $wpdb->insert(
			$table_name,
			[
				'barcode'          => $this->barcode,
				'dropp_order_id'   => $this->dropp_order_id,
				'shipping_item_id' => $this->shipping_item_id,
				'location_id'      => $this->location_id,
				'products'         => wp_json_encode( $this->products ),
				'status'           => $this->status,
				'customer'         => wp_json_encode( $this->get_customer_array() ),
				'test'             => $this->test,
				'updated_at'       => current_time( 'mysql' ),
				'created_at'       => current_time( 'mysql' ),
			]
		);

		$this->id = $wpdb->insert_id;
		return $this;
	}

	/**
	 * From Order
	 *
	 * @param  integer $order_id (optional) Order ID.
	 * @return array             Array of Dropp_Consignment.
	 */
	public static function from_order( $order_id = false ) {
		if ( false === $order_id ) {
			$order_id = get_the_ID();
		}
		$order      = new \WC_Order( $order_id );
		$line_items = $order->get_items( 'shipping' );
		$collection = [];
		foreach ( $line_items as $order_item_id => $order_item ) {
			$collection = array_merge( $collection, self::from_shipping_item( $order_item ) );
		}
		return $collection;
	}

	/**
	 * Array from Order
	 *
	 * @param  integer $order_id (optional) Order ID.
	 * @return array             Array of Dropp_Consignment.
	 */
	public static function array_from_order( $order_id = false ) {
		$collection = self::from_order( $order_id );
		return array_map(
			function( $item ) {
				return $item->to_array( false );
			},
			$collection
		);
	}

	/**
	 * From Shipping Item
	 *
	 * @param  WC_Order_Item_Shipping $shipping_item Shipping item.
	 * @return array                                 Array of Dropp_Consignment.
	 */
	public static function from_shipping_item( $shipping_item ) {
		global $wpdb;

		$sql = $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}dropp_consignments WHERE shipping_item_id = %d",
			$shipping_item->get_id()
		);

		$result = $wpdb->get_results( $sql, ARRAY_A );
		if ( empty( $result ) ) {
			return [];
		}
		$collection = [];
		foreach ( $result as $consignment_data ) {
			$consignment = new self();
			$consignment->fill( $consignment_data );
			$collection[] = $consignment;
		}
		return $collection;
	}

	/**
	 * Count consignments on order
	 *
	 * @param  WC_Order $order Order.
	 * @return integer         Count.
	 */
	public static function count_consignments_on_order( $order ) {
		global $wpdb;
		$shipping_items    = $order->get_items( 'shipping' );
		$shipping_item_ids = [];
		foreach ( $shipping_items as $shipping_item ) {
			$shipping_item_ids[] = $shipping_item->get_id();
		}
		if ( empty( $shipping_item_ids ) ) {
			return 0;
		}
		$shipping_item_ids = implode( ',', $shipping_item_ids);
		$result            = $wpdb->get_var(
			"SELECT count(*) FROM {$wpdb->prefix}dropp_consignments WHERE shipping_item_id in ({$shipping_item_ids})"
		);
		return (int) $result;
	}


	/**
	 * Get customer array
	 *
	 * @return array Customer data.
	 */
	public function get_customer_array() {
		if ( empty( $this->customer ) ) {
			return [];
		}
		return $this->customer->to_array();
	}

	public function render_pdf() {
		// dropp/api/v1/orders/pdf/
	}
}
