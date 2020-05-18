<?php
/**
 * Shipping method
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
		if ( 'cost' !== $option ) {
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

	/**
	 * Get setting form fields for instances of this shipping method within zones.
	 *
	 * @return array
	 */
	public function get_instance_form_fields() {
		$form_fields                     = parent::get_instance_form_fields();
		$form_fields['title']['default'] = $this->method_title;
		if ( isset( $form_fields['cost'] ) ) {
			$form_fields['cost_2'] = array(
				'title'             => __( 'Cost 2', 'dropp-for-woocommerce' ),
				'type'              => 'text',
				'placeholder'       => '',
				'description'       => $form_fields['cost']['description'],
				'default'           => '0',
				'desc_tip'          => true,
				'sanitize_callback' => array( $this, 'sanitize_cost' ),
			);
		}
		return $form_fields;
	}
}
