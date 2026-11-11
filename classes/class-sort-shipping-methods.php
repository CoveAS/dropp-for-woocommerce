<?php

namespace Dropp;

class Sort_Shipping_Methods
{

	public static function setup()
	{
		// Run last, so this setting decides the order over other plugins that sort the rates.
		add_filter('woocommerce_package_rates', __CLASS__.'::sort_package_rates', PHP_INT_MAX, 2);
		// WooCommerce runs this filter after all package rates are done, also for rates from the session cache.
		// ponytail: a plugin that also sorts here at PHP_INT_MAX and is added later still wins.
		add_filter('woocommerce_shipping_packages', __CLASS__.'::sort_packages', PHP_INT_MAX);
	}

	public static function sort_packages($packages)
	{
		if ( ! is_array($packages) ) {
			return $packages;
		}
		foreach ($packages as $key => $package) {
			if ( ! empty($package['rates']) ) {
				$packages[$key]['rates'] = self::sort_package_rates($package['rates'], $package);
			}
		}
		return $packages;
	}

	public static function sort_package_rates($rates, $package)
	{
		if ( ! is_array($rates) || ! Options::get_instance()->dropp_rates_first ) {
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
