<?php
/**
 * Plugin Name:         WooCommerce Dropp.is Shipping
 * Plugin URI:          https://dropp.is/woocommerce
 * Description:         Shipping method
 * Author:              Cove AS
 * Author URI:          https://cove.no/dropp
 *
 * Version:             0.0.1
 * Requires at least:   5.3.2
 * Tested up to:        5.3.2
 *
 * WC requires at least: 3.8.1
 * WC tested up to: 3.8.1
 *
 * Text Domain:         bring-fraktguiden-for-woocommerce
 * Domain Path:         /languages
 *
 * @package             WooCommerce
 * @category            Shipping Method
 * @author              Cove AS
 */

namespace Dropp;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once 'classes/class-dropp.php';

add_action( 'plugins_loaded', 'Dropp\Dropp::loaded' );
register_deactivation_hook( __FILE__, 'Bring_Fraktguiden::plugin_deactivate' );
