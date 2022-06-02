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
use WC_Shipping_Method;

/**
 * API
 */
class Shipping_Calculation_Shortcodes {
	protected array $package;
	protected WC_Shipping_Method $shipping_method;
	public function __construct( array $package, WC_Shipping_Method $shipping_method ) {
		$this->package         = $package;
		$this->shipping_method = $shipping_method;
	}

	public static function setup(): void {
		add_action( 'dropp_before_calculate_shipping', __CLASS__ . '::register', 10, 2 );
		add_action( 'dropp_after_calculate_shipping', __CLASS__ . '::unregister' );
	}

	public static function register( array $package, WC_Shipping_Method $shipping_method ): void {
		$instance = new static( $package, $shipping_method );
		add_shortcode( 'kg', [ $instance, 'kg' ] );
		add_shortcode( 'pricetype', [ $instance, 'pricetype' ] );
	}

	public static function unregister(): void {
		remove_shortcode( 'kg' );
		remove_shortcode( 'pricetype' );
	}

	public function kg( array $atts, $content ): float {
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
			return 0;
		}

		return floatval( $content );
	}

	public function pricetype( array $atts, $content ): float {
		$pricetype = $this->shipping_method->get_pricetype();
		if ( ! $this->test( $pricetype, $atts, 'pricetype' ) ) {
			return '';
		}
		return floatval( $content );
	}

	public function test( $value, array $atts, string $shortcode ): bool {
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
