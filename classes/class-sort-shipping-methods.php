<?php

namespace Dropp;

class Sort_Shipping_Methods
{

	public static function setup()
	{
		// Run after other carriers that sort at priority 100, so this setting decides the order.
		add_filter('woocommerce_package_rates', __CLASS__.'::sort_package_rates', 110, 2);
	}

	public static function sort_package_rates($rates, $package): array
	{
		if ( ! Options::get_instance()->dropp_rates_first ) {
			return $rates;
		}
		$dropp_rates = array_filter(
			$rates,
			fn($key) => str_starts_with($key, 'dropp_'),
			ARRAY_FILTER_USE_KEY
		);
		return array_merge(
			$dropp_rates,
			array_diff_key($rates, $dropp_rates)
		);
	}
}
