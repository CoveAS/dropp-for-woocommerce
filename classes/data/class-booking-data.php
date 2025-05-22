<?php

namespace Dropp\Data;

use Dropp\Models\Dropp_Consignment;

class Booking_Data
{

	/**
	 * @param bool $any_booked
	 * @param bool $all_booked
	 * @param Dropp_Consignment[] $consignments
	 * @param string[] $messages
	 */
	public function __construct(
		public bool $any_booked,
		public bool $all_booked,
		public array $consignments,
		public array $messages,
	)
	{
	}
}
