<?php
/**
 * API
 *
 * @package dropp-for-woocommerce
 */

namespace Dropp;

use Exception;
use WC_Log_Levels;
use WC_Logger;
use WC_Shipping;
use Dropp\Models\Model;

/**
 * API
 */
class Shipping_Calculation_Shortcodes {
	protected $package;
	protected $shipping_method;
	public function __construct( $package, $shipping_method ) {
		$this->package         = $package;
		$this->shipping_method = $shipping_method;
	}

	public static function setup() {
		add_action( 'dropp_before_calculate_shipping', __CLASS__ . '::register', 10, 2 );
		add_action( 'dropp_after_calculate_shipping', __CLASS__ . '::unregister' );
	}

	public static function register( $package, $shipping_method ) {
		$instance = new static( $package, $shipping_method );
		add_shortcode( 'kg', [ $instance, 'kg' ] );
		add_shortcode( 'pricetype', [ $instance, 'pricetype' ] );
	}

	public static function unregister() {
		remove_shortcode( 'kg' );
		remove_shortcode( 'pricetype' );
	}

	public function kg( $atts, $content ) {
		$total_weight = 0;
		if ( ! empty( $this->package ) ) {
			foreach ( $this->package['contents'] as $item ) {
				if ( empty( $item['data'] ) ) {
					continue;
				}
				$total_weight += $item['quantity'] * wc_get_weight( $item['data']->get_weight(), 'kg' );
			}
		}

		if ( ! $this->test( $total_weight, $atts, 'kg' ) ) {
			return '';
		}

		return floatval( $content );
	}

	public function pricetype( $atts, $content ) {
		$pricetype = $this->shipping_method->get_pricetype();
		if ( ! $this->test( $pricetype, $atts, 'pricetype' ) ) {
			return '';
		}
		return floatval( $content );
	}

	public function test( $value, $atts, $shortcode ) {
		$atts = shortcode_atts(
			array(
				'lt'  => '',
				'lte' => '',
				'gt'  => '',
				'gte' => '',
				'eq'  => '',
			),
			$atts,
			$shortcode
		);

		if ( $atts['lt'] && ! ( $value < $atts['lt'] ) ) {
			return false;
		} elseif ( $atts['lte'] && ! ( $value <= $atts['lte'] ) ) {
			return false;
		} elseif ( $atts['gt'] && ! ( $value > $atts['gt'] ) ) {
			return false;
		} elseif ( $atts['gte'] && ! ( $value >= $atts['gte'] ) ) {
			return false;
		}
		return true;
	}
}
