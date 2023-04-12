<?php
/**
 * Price Info
 *
 * @package dropp-for-woocommerce
 */

namespace Dropp\Data;

use Dropp\Actions\Get_Remote_Price_Info_Action;
use Dropp\Shipping_Method\Dropp;

/**
 * Dropp PDF
 *
 * @property array<array> $items
 */
class Price_Info_Data {
	const TTL = 3600;
	protected static self $instance;

	private function __construct(
		protected int $expire_at,
		protected array $items = [],
	) {
	}

	public static function get_instance()
	{
		if (isset(self::$instance) && self::$instance->expire_at > time()) {
			return self::$instance;
		}
		// Attempt to get from options
		$dropp = Dropp::get_instance();
		$price_info = $dropp->get_option('price_info', []);
		$items = $price_info['items'] ?? [];
		$expire_at = $price_info['expire_at'] ?? 0;

		// Get from remote when option is empty or expired
		if (empty($items) || $expire_at < time() ) {
			$items = (new Get_Remote_Price_Info_Action)();
			$expire_at = time() + self::TTL;

			// Save to options
			$dropp->update_option(
				'price_info',
				[
					'items' => $items,
					'expire_at' => $expire_at,
				]
			);
		}

		// Map to Price Data
		$mapped_items = [];
		foreach ($items as $key => $prices) {
			$mapped_prices = array_map(
				fn(array $price) => new Price_Data(
					$price['price'],
					$price['maxweight']
				),
				$prices
			);
			usort(
				$mapped_prices,
				fn(Price_Data $a, Price_Data $b) => $a->max_weight < $b->max_weight ? 1 : -1
			);
			$mapped_items[$key] = $mapped_prices;
		}

		self::$instance = new self(
			$expire_at,
			$mapped_items
		);
		return self::$instance;
	}
}
