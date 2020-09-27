<?php
/**
 * Dropp
 *
 * @package dropp-for-woocommerce
 */

namespace Dropp\Shipping_Method;

use Dropp\Dropp_Location;

/**
 * Shipping method
 */
class Dropp extends Shipping_Method {

	public static function get_cost_option( $value, $option, $shipping_method ) {
		if ( 'cost' !== $option || is_admin() || ! WC()->session ) {
			return $value;
		}

		$location_data = WC()->session->get( 'dropp_location_' . $shipping_method->get_instance_id() );

		if ( empty( $location_data ) ) {
			return $value;
		}

		if ( '2' !== $location_data['pricetype'] ) {
			return $value;
		}

		return $shipping_method->get_instance_option( 'cost_2' );
	}

	public function get_instance_form_fields() {
		$form_fields = parent::get_instance_form_fields();
		if ( ! empty( $form_fields['cost'] ) ) {
			$form_fields['cost']['title'] = __( 'Capital area', 'dropp-for-woocommerce' );
		}
		return $form_fields;
	}

	public function get_additional_form_fields( $form_fields ) {
		return array_merge(
			[
				'cost_2' => [
					'title'             => __( 'Outside capital area', 'dropp-for-woocommerce' ),
					'type'              => 'text',
					'placeholder'       => '',
					'description'       => $form_fields['cost']['description'],
					'default'           => '0',
					'desc_tip'          => true,
					'sanitize_callback' => array( $this, 'sanitize_cost' ),
				],
			],
			parent::get_additional_form_fields( $form_fields )
		);
	}
}
