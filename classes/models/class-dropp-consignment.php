<?php
/**
 * Consignment
 *
 * @package dropp-for-woocommerce
 */

namespace Dropp\Models;

use Dropp\API;
use Dropp\Shipping_Method\Dropp;
use Exception;
use WC_Log_Levels;
use WC_Logger;
use WC_Order_Item_Shipping;

/**
 * Consignment
 */
class Dropp_Consignment extends Model {

	public $id;
	public $barcode = '';
	public $comment = '';
	public $dropp_order_id;
	public $status = 'ready';
	public $shipping_item_id;
	public $location_id;
	public $products;
	public $customer;
	public $test = false;
	public $updated_at;
	public $created_at;
	public $debug = false;
	public $errors = [];

	/**
	 * Constructor.
	 */
	public function __construct() {
	}

	/**
	 * Get status list.
	 *
	 * @return array List of status.
	 */
	public static function get_status_list() {
		return [
			'ready'       => __( 'Ready', 'dropp-for-woocommerce' ),
			'error'       => __( 'Error', 'dropp-for-woocommerce' ),
			'initial'     => __( 'Initial', 'dropp-for-woocommerce' ),
			'transit'     => __( 'Transit', 'dropp-for-woocommerce' ),
			'consignment' => __( 'Consignment', 'dropp-for-woocommerce' ),
			'delivered'   => __( 'Delivered', 'dropp-for-woocommerce' ),
			'cancelled'   => __( 'Cancelled', 'dropp-for-woocommerce' ),
		];
	}

	/**
	 * Get status list.
	 *
	 * @return array List of status.
	 */
	public function get_shipping_method() {
		if ( ! $this->shipping_item_id ) {
			return null;
		}
		$shipping_item = new WC_Order_Item_Shipping( $this->shipping_item_id );
		return new Dropp( $shipping_item->get_instance_id() );
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
				'comment'          => null,
				'location_id'      => null,
				'test'             => false,
				'debug'            => false,
				'updated_at'       => current_time( 'mysql' ),
				'created_at'       => current_time( 'mysql' ),
			]
		);

		$requires_value = [
			'barcode',
			'dropp_order_id',
			'shipping_item_id',
			'location_id',
		];
		foreach ( $requires_value as $name ) {
			// Skip if the property has a value, but the new value is empty.
			if ( ! empty( $this->{$name} ) && empty( $content[ $name ] ) ) {
				continue;
			}
			$this->{$name} = $content[ $name ];
		}

		if ( $content['debug'] ) {
			$this->debug = true;
		}
		$this->comment    = $content['comment'];
		$this->status     = $content['status'];
		$this->test       = (int) $content['test'];
		$this->updated_at = $content['updated_at'];
		$this->created_at = $content['created_at'];

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
			$this->set_products( $products );
		}
		return $this;
	}

	public function set_products( $products ) {
		$this->products = [];
		foreach ( $products as $product ) {
			$this->products[] = ( new Dropp_Product_Line() )->fill( $product );
		}
	}
	public function set_customer( $customer ) {
			$this->customer = ( new Dropp_Customer() )->fill( $customer );
	}

	/**
	 * To array
	 *
	 * @param  boolean $for_request True limits the fields to only those used to send to Dropp.is.
	 * @return array                Array representation.
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
			'comment'    => $this->comment,
		];
		if ( ! $for_request ) {
			$consignment_array['id']               = $this->id;
			$consignment_array['status']           = $this->status;
			$consignment_array['dropp_order_id']   = $this->dropp_order_id;
			$consignment_array['shipping_item_id'] = $this->shipping_item_id;
			$consignment_array['test']             = $this->test;
			$consignment_array['created_at']       = $this->created_at;
			$consignment_array['updated_at']       = $this->updated_at;

			// Add location.
			$shipping_item                 = new WC_Order_Item_Shipping( $this->shipping_item_id );
			$consignment_array['location'] = Dropp_Location::from_shipping_item( $shipping_item );
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
		$shipping_method = $this->get_shipping_method();
		$option_name     = 'api_key';
		if ( $this->test ) {
			$option_name = 'api_key_test';
		}
		$api_key = $shipping_method->get_option( $option_name );
		if ( empty( $api_key ) ) {
			throw new Exception( __( 'No API key could be found.', 'dropp-for-woocommerce' ), 1 );
		}
		return $api_key;
	}

	/**
	 * Find
	 *
	 * @param  int               $id ID.
	 * @return Dropp_Consignment     This.
	 */
	public static function find( $id ) {
		global $wpdb;
		$sql = $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}dropp_consignments WHERE id = %d",
			$id
		);
		$row         = $wpdb->get_row( $sql, ARRAY_A );
		$consignment = new self();
		if ( ! empty( $row ) ) {
			$consignment->fill( $row );
		}
		$shipping_method = $consignment->get_shipping_method();
		if ( ! empty( $shipping_method ) ) {
			$consignment->debug = $shipping_method->debug_mode;
		}
		return $consignment;
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
		$this->updated_at = current_time( 'mysql' );
		$row_count = $wpdb->update(
			$table_name,
			[
				'barcode'          => $this->barcode,
				'dropp_order_id'   => $this->dropp_order_id,
				'shipping_item_id' => $this->shipping_item_id,
				'location_id'      => $this->location_id,
				'products'         => wp_json_encode( $this->products ),
				'comment'          => $this->comment,
				'status'           => $this->status,
				'customer'         => wp_json_encode( $this->get_customer_array() ),
				'test'             => $this->test,
				'updated_at'       => $this->updated_at,
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
		$this->created_at = current_time( 'mysql' );
		$this->updated_at = current_time( 'mysql' );
		$table_name = $wpdb->prefix . 'dropp_consignments';
		$row_count = $wpdb->insert(
			$table_name,
			[
				'barcode'          => $this->barcode,
				'dropp_order_id'   => $this->dropp_order_id,
				'shipping_item_id' => $this->shipping_item_id,
				'location_id'      => $this->location_id,
				'products'         => wp_json_encode( $this->products ),
				'comment'          => $this->comment,
				'status'           => $this->status,
				'customer'         => wp_json_encode( $this->get_customer_array() ),
				'test'             => $this->test,
				'updated_at'       => $this->updated_at,
				'created_at'       => $this->created_at,
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
		$order      = wc_get_order( $order_id );
		$line_items = $order->get_items( 'shipping' );
		$collection = [];
		foreach ( $line_items as $order_item_id => $order_item ) {
			$collection = array_merge( $collection, self::from_shipping_item( $order_item ) );
		}
		return $collection;
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
	 * Maybe update
	 *
	 * @return boolean True when updated.
	 */
	public function maybe_update() {
		if ( time() < strtotime( $this->updated_at ) + 600 ) {
			return false;
		}
		try {
			$this->remote_get()->save();
		} catch ( Exception $e ) {
			return false;
		}
		return true;
	}

	/**
	 * Get URL
	 *
	 * @param  string $endpoint Endpoint.
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

	/**
	 * Remote args
	 *
	 * @throws Exception      Unknown method.
	 * @param  string $method Remote method, either 'get' or 'post'.
	 * @param  string $url    Url.
	 * @return array          Remote arguments.
	 */
	public function remote( $method, $url ) {
		$log  = new WC_Logger();
		$args = [
			'headers' => [
				'Authorization' => 'Basic ' . $this->get_api_key(),
				'Content-Type'  => 'application/json;charset=UTF-8',
			],
		];
		$allowed_methods = [ 'get', 'post', 'delete', 'patch' ];
		if ( ! in_array( $method, $allowed_methods, true ) ) {
			throw new Exception( "Unknown method, \"$method\"" );
		}
		$args['method'] = strtoupper( $method );
		if ( 'delete' === $method ) {
			$args['method'] = 'DELETE';
		}
		if ( 'patch' === $method || 'post' === $method ) {
			$args['body'] = wp_json_encode( $this->to_array() );
		}
		if ( $this->debug ) {
			$log->add(
				'dropp-for-woocommerce',
				'[DEBUG] Remote ' . strtoupper( $method ) . ' request:' . PHP_EOL . $url . PHP_EOL . wp_json_encode( $args, JSON_PRETTY_PRINT ),
				WC_Log_Levels::DEBUG
			);
		}
		return wp_remote_request( $url, $args );
	}

	/**
	 * Remote get
	 *
	 * @throws Exception   $e     Sending exception.
	 * @return Consignment          This object.
	 */
	public function remote_get() {
		if ( ! $this->dropp_order_id ) {
			throw new Exception(
				sprintf(
					// translators: Consignment ID.
					__( 'Consignment, %d, does not have a dropp order id.', 'dropp-for-woocommerce' ),
					$this->id
				)
			);
		}
		$response = $this->remote( 'get', self::get_url( 'orders/' . $this->dropp_order_id ) );
		return $this->process_response( 'get', $response );
	}

	/**
	 * Remote post / Booking
	 *
	 * @return Consignment          This object.
	 */
	public function remote_post() {
		$response = $this->remote( 'post', $this->get_url( 'orders' ) );
		return $this->process_response( 'post', $response );
	}

	/**
	 * Remote patch / Update booking
	 *
	 * @throws Exception   $e     Sending exception.
	 * @return Consignment          This object.
	 */
	public function remote_patch() {
		if ( ! $this->dropp_order_id ) {
			throw new Exception(
				sprintf(
					// translators: Consignment ID.
					__( 'Consignment, %d, does not have a dropp order id.', 'dropp-for-woocommerce' ),
					$this->id
				)
			);
		}
		$response = $this->remote( 'patch', $this->get_url( 'orders/' . $this->dropp_order_id ) );
		return $this->process_response( 'patch', $response );
	}

	/**
	 * Remote delete / Cancel order
	 *
	 * @throws Exception   $e     Sending exception.
	 * @return Consignment          This object.
	 */
	public function remote_delete() {
		if ( ! $this->dropp_order_id ) {
			throw new Exception(
				sprintf(
					// translators: Consignment ID.
					__( 'Consignment, %d, does not have a dropp order id.', 'dropp-for-woocommerce' ),
					$this->id
				)
			);
		}
		$response = $this->remote( 'delete', $this->get_url( 'orders/' . $this->dropp_order_id ) );
		return $this->process_response( 'delete', $response );
	}

	/**
	 * Remote add
	 *
	 * @return array|string Decoded json, string body or raw response object.
	 */
	public function remote_add() {
		$api       = new API( $this->get_shipping_method() );
		$api->test = $this->test;
		return $api->post( 'orders/addnew/', $this );
	}

	/**
	 * Process response
	 *
	 * @throws Exception      $e        Response exception.
	 * @param  string         $method   Remote method, either 'get' or 'post'.
	 * @param  WP_Error|array $response Array with response data on success.
	 * @return Consignment              This object.
	 */
	protected function process_response( $method, $response ) {
		$log = new WC_Logger();
		if ( is_wp_error( $response ) ) {
			$log->add(
				'dropp-for-woocommerce',
				'[ERROR] Remote ' . strtoupper( $method ) . ' response:' . PHP_EOL . wp_json_encode( $response->errors, JSON_PRETTY_PRINT ),
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
				'[DEBUG] Remote ' . strtoupper( $method ) . ' response:' . PHP_EOL . $body,
				WC_Log_Levels::DEBUG
			);
		}

		// Validate response.
		if ( is_wp_error( $response ) ) {
			$this->errors = $response->get_error_messages();
			throw new Exception( __( 'Response error', 'dropp-for-woocommerce' ) );
		}
		if ( 'delete' === $method ) {
			// Delete calls should return an empty response.
			// No need for further processing.
			$this->status = 'cancelled';
			return $this;
		}
		$dropp_order = json_decode( $response['body'], true );
		if ( ! is_array( $dropp_order ) ) {
			$this->errors['invalid_json'] = $response['body'];
			throw new Exception( __( 'Invalid json', 'dropp-for-woocommerce' ) );
		}
		if ( ! empty( $dropp_order['error'] ) ) {
			throw new Exception( $dropp_order['error'] );
		}
		if ( 'patch' === $method ) {
			// Patch calls should return an empty response.
			// No need for further processing.
			return $this;
		}
		if ( empty( $dropp_order['id'] ) ) {
			throw new Exception( __( 'Empty ID in the response', 'dropp-for-woocommerce' ) );
		}

		$dropp_order = json_decode( $response['body'], true );

		$this->dropp_order_id = $dropp_order['id'] ?? '';
		$this->status         = $dropp_order['status'] ?? '';
		if ( ! empty( $dropp_order['barcode'] ) ) {
			$this->barcode = $dropp_order['barcode'];
		}
		// $this->updated_at     = $dropp_order['updatedAt'] ?? '';
		return $this;
	}
}
