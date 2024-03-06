<?php

namespace Dropp\Utility;

use Dropp\Models\Dropp_Location;

class Dropp_Special_Location_Map
{
	private array $items;

	private function __construct()
	{
		$this->items = [
			'dropp_home'      => '9ec1f30c-2564-4b73-8954-25b7b3186ed3',
			'dropp_home_oca'  => '9ec1f30c-2564-4b73-8954-25b7b3186ed3',
			'dropp_daytime'   => '9ec1f30c-2564-4b73-8954-25b7b3186ed3',
			'dropp_flytjandi' => 'a178c25e-bb35-4420-8792-d5295f0e7fcc',
			'dropp_pickup'    => '30e06a53-2d65-46e7-adc1-18e60de28ecc',
		];
	}

	public static function get_instance(): Dropp_Special_Location_Map
	{
		static $map;
		if (!isset($map)) {
			$map = new self();
		}
		return $map;
	}

	public function has(string $location_id): bool
	{
		return in_array($location_id, $this->items, true);
	}

	public function get(string $location_id): Dropp_Location
	{
		$key = array_search($location_id, $this->items, true);
		return new Dropp_Location($key);
	}
}
