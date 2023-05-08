<?php

namespace Dropp;

use Dropp\Utility\Admin_Notice_Utility;

class Admin_Notices {
	public static function setup(): void
	{
		Admin_Notice_Utility::setup();
		Admin_Notice_Utility::register(
			'dropp_cost_tier_upgrade_notice',
			new Admin_Notice(
				__(
					'Attention: Important Pricing Update for Dropp Shipping Methods ',
					'dropp-for-woocommerce'
				),
				__(
					"We've updated the pricing structure for Dropp shipping methods to a weight-based tier system. To ensure accurate pricing, you need to review the price settings for all of the enabled Dropp shipping methods in any shipping zone that has a Dropp shipping method.

To review your pricing settings, please go to the Dropp shipping settings and check the price settings for each Dropp shipping method.

%s

For your convenience, we've added a \"Use suggested prices\" button that will load suggested prices from Dropp's API. If the price is removed and saved as an empty field, it will by default use the suggested price.
Thank you for your attention to this matter.",
					'dropp-for-woocommerce'
				),
				[
					new Admin_Notice_Link(
						__('Click here to go to the shipping settings.', 'dropp-for-woocommerce'),
						admin_url('admin.php?page=wc-settings&tab=shipping')
					)
				],
			)
		);
		Admin_Notice_Utility::load_options();
	}
}
