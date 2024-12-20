<?php
/**
 * Location
 *
 * @package dropp-for-woocommerce
 */

namespace Dropp\Models;

use Dropp\Actions\Get_Shipping_Method_From_Shipping_Item_Action;
use Dropp\API;
use Dropp\Utility\Dropp_Special_Location_Map;
use WC_Order;
use WC_Order_Item;
use WC_Order_Item_Shipping;

/**
 * Shipping method
 * @property $day_delivery
 */
class Dropp_Location extends Model {
	/**
	 * WC_Order_Item_Shipping $order_item
	 */
	protected WC_Order_Item_Shipping $order_item;
	public int $order_item_id;

	public ?string $id = null;
	public string $name;
	public ?string $barcode = null;
	public string $type;

	/**
	 * Weight Limit in KG
	 */
	public int $weight_limit = 10;

	/**
	 * Constructor.
	 *
	 * @param string $type
	 */
	public function __construct( string $type = 'dropp_is' ) {
		$this->type = $type;

		// Special location handling for home deliveries.
		$dropp_home_ids = [
			'dropp_home',
			'dropp_home_oca',
			'dropp_daytime',
		];

		if ( in_array( $type, $dropp_home_ids, true ) ) {
			$this->id           = '9ec1f30c-2564-4b73-8954-25b7b3186ed3';
			$this->name         = __( 'Home delivery', 'dropp-for-woocommerce' );
			$this->weight_limit = 60;
			$this->address      = '';
		}

		// Special location handling for flytjandi deliveries.
		if ( 'dropp_flytjandi' === $type ) {
			$this->id           = 'a178c25e-bb35-4420-8792-d5295f0e7fcc';
			$this->name         = __( 'Dropp - Other pickup locations', 'dropp-for-woocommerce' );
			$this->weight_limit = 0;
			$this->address      = '';
		}

		// Special location handling for pickup.
		if ( 'dropp_pickup' === $type ) {
			$this->id      = '30e06a53-2d65-46e7-adc1-18e60de28ecc';
			$this->name    = __( 'Pick-up at warehouse', 'dropp-for-woocommerce' ) . ' (Vatnagarðar 22)';
			$this->address = '';
		}
	}

	/**
	 * From Shipping Item
	 *
	 * @param WC_Order_Item_Shipping $shipping_item
	 * @param null $day_delivery
	 *
	 * @return Dropp_Location Array of Dropp_Location.
	 */
	public static function from_shipping_item( WC_Order_Item_Shipping $shipping_item, $day_delivery = null ): Dropp_Location {
		$location                = new self( $shipping_item->get_method_id() );
		$location->order_item    = $shipping_item;
		$location->order_item_id = $shipping_item->get_id();

		if ( 'dropp_is' === $location->type || 'dropp_is_oca' === $location->type ) {
			$meta_data = $shipping_item->get_meta( 'dropp_location' );
			if ( is_array( $meta_data ) ) {
				$location->id      = $meta_data['id'] ?? null;
				$location->name    = $meta_data['name'] ?? null;
				$location->address = $meta_data['address'] ?? null;
			}
		}

		if ( is_null( $day_delivery ) ) {
			return $location;
		}

		if ( 'dropp_home' === $location->type || 'dropp_daytime' === $location->type ) {
			$location->type = ( $day_delivery ? 'dropp_daytime' : 'dropp_home' );
		}

		return $location;
	}

	/**
	 * From Order
	 *
	 * @param integer $order_id (optional) Order ID.
	 *
	 * @return array             Array of Dropp_Location.
	 */
	public static function from_order( $order_id = false ): array {
		$order      = wc_get_order( $order_id );
		$line_items = $order->get_items( 'shipping' );
		$collection = [];
		foreach ( $line_items as $order_item_id => $order_item ) {
			/** @var WC_Order_Item_Shipping $order_item */
			$location = self::from_shipping_item( $order_item );
			if ( ! $location->id ) {
				continue;
			}
			$collection[] = $location;
		}

		return $collection;
	}

	public static function remote_find( string $location_id ): ?Dropp_Location
	{
		// Special logic for hard-coded locations.
		$special_location_map = Dropp_Special_Location_Map::get_instance();
		if ( $special_location_map->has($location_id) ) {
			return $special_location_map->get($location_id);
		}

		// Ask the API about the dropp order id.
		$api      = new API();
		$response = $api->get( 'dropp/locations' );

		if ( empty( $response['locations'] ) ) {
			throw new \Exception( 'Could not find any locations' );
		}
		$locations = $response['locations'];

		$location = null;
		foreach ( $locations as $location_data ) {
			if ( $location_id != $location_data['id'] ) {
				continue;
			}
			$location            = new self();
			$location->id        = $location_id;
			$location->name      = $location_data['name'] ?? '';
			$location->address   = $location_data['address'] ?? '';
			$location->pricetype = $location_data['pricetype'] ?? '1';
			break;
		}

		return $location;
	}

	/**
	 * Array From Order
	 *
	 * @param integer $order_id (optional) Order ID.
	 *
	 * @return array             Array of Dropp_Location arrays.
	 */
	public static function array_from_order( $order_id = false ) {
		return self::from_order( $order_id );
	}

	/**
	 * To array
	 *
	 * @return array Array representation.
	 */
	public function to_array(): array {
		return [
			'order_item_id' => $this->order_item_id,
			'id'            => $this->id,
			'name'          => $this->name,
			'day_delivery'  => $this->day_delivery,
			'weight_limit'  => $this->weight_limit,
			'address'       => $this->address,
			'barcode'       => $this->barcode,
			'type'          => $this->type,
		];
	}
}
