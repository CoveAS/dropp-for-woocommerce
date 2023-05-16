<?php

namespace Dropp\Actions;

use Dropp\Data\Shipping_Instance_Data;
use Dropp\Shipping_Method\Shipping_Method;
use WC_Shipping_Zone;
use WC_Shipping_Zones;

class Get_Shipping_Instance_Data_Action
{
	public function __invoke( int $instance_id ): ?Shipping_Instance_Data
	{
		$zones = WC_Shipping_Zones::get_zones();

		// Add root zone
		$root_zone = new WC_Shipping_Zone(0);
		$zones[$root_zone->get_id()] = [
			'zone_id' => $root_zone->get_id(),
			'shipping_methods' => $root_zone->get_shipping_methods( false, 'admin' ),
		];
		$zone  = false;
		$shipping_method = null;
		foreach ( $zones as $zone_data ) {
			foreach ( $zone_data['shipping_methods'] as $shipping_method ) {
				if ( $instance_id !== $shipping_method->instance_id ) {
					continue;
				}
				$zone = WC_Shipping_Zones::get_zone( $zone_data['zone_id'] );
				break 2;
			}
		}
		if (! $zone || ! $shipping_method instanceof Shipping_Method) {
			return null;
		}
		return new Shipping_Instance_Data($shipping_method, $zone);
	}
}
