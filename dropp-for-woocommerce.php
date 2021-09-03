<?php
/**
 * Plugin Name:          Dropp for WooCommerce
 * Plugin URI:           https://dropp.is/woocommerce
 * Description:          Shipping method
 * Author:               Cove AS
 * Author URI:           https://cove.no/dropp
 *
 * Version:              1.4.6
 * Requires at least:    5.2
 * Tested up to:         5.7
 *
 * WC requires at least: 3.8.1
 * WC tested up to:      5.0.0
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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once __DIR__ . '/classes/class-dropp.php';

add_action( 'plugins_loaded', 'Dropp\Dropp::loaded' );
