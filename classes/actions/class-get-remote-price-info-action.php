<?php

namespace Dropp\Actions;

use Dropp\API;
use Dropp\Collection;
use Exception;

class Get_Remote_Price_Info_Action
{
	public function __invoke(): array
	{
		$api = new API();

		$response = $api->get("orders/store/priceinfo/", 'json');

		return array_filter($response, fn(mixed $value) => is_array($value));
	}

}
