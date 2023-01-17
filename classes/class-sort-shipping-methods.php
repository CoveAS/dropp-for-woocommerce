<?php

namespace Dropp;

class Sort_Shipping_Methods
{

	public static function setup()
	{
		add_filter('woocommerce_package_rates', __CLASS__.'::sort_package_rates', 100, 2);
	}

	public static function sort_package_rates($rates, $package): array
	{
		$shipping_method = new Shipping_Method\Dropp();
		if ( ! filter_var(
				$shipping_method->get_option('dropp_rates_first', 'yes'),
				FILTER_VALIDATE_BOOLEAN
			) ) {
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
