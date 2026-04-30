<?php

namespace Dropp\Components;

class Choose_Location_Button
{
	public function render(): string
	{
		if (!defined('DROPP_WEB_COMPONENT_BTN') || DROPP_WEB_COMPONENT_BTN) {
			return sprintf(
				'<dropp-location-button label="%s"></dropp-location-button>',
				esc_attr__('Choose location', 'dropp-for-woocommerce')
			);
		}

		return sprintf(
			'<span class="dropp-location__button button">%s</span>',
			esc_html__('Choose location', 'dropp-for-woocommerce')
		);
	}
}
