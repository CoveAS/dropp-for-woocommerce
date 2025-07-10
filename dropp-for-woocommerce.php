<?php
/**
 * Plugin Name:          Dropp for WooCommerce
 * Requires Plugins:     woocommerce
 * Plugin URI:           https://hjalp.dropp.is/article/29-hvernig-tengi-eg-woocommerce
 * Description:          Shipping method
 * Author:               Cove AS
 * Author URI:           https://cove.no/dropp
 *
 * Version:              ###DROPP_VERSION###
 * Requires at least:    5.2
 * Tested up to:         ###WP_VERSION###
 *
 * WC requires at least: 3.8.1
 * WC tested up to:      ###WC_VERSION###
 *
 * Text Domain:          dropp-for-woocommerce
 * Domain Path:          /languages
 * License:              GPL v3
 *
 * @package              WooCommerce
 * @category             Shipping Method
 * @author               Cove AS
 * @license              http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Dropp;

use Automattic\WooCommerce\Utilities\FeaturesUtil;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if (version_compare(PHP_VERSION, '8.1', '<')) {
	add_action(
		'admin_notices',
		function () {
			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				esc_html__('Dropp for WooCommerce requires PHP version 8.1 or higher. The websites PHP version is ', 'dropp-for-woocommerce') . PHP_VERSION
			);
		}
	);
	return;
}

require_once __DIR__ . '/classes/class-dropp.php';

add_action(
	'before_woocommerce_init',
	fn() => class_exists(FeaturesUtil::class) ?
	FeaturesUtil::declare_compatibility(
		'custom_order_tables',
		__FILE__
	) : null
);

add_action( 'plugins_loaded', 'Dropp\Dropp::loaded' );
