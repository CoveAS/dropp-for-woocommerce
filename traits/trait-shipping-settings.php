<?php
/**
 * Shipping settings
 *
 * @package woocommerce-dropp-shipping
 */

namespace Dropp;

/**
 * Shipping settings
 */
trait Shipping_Settings {


	/**
	 * API Key.
	 *
	 * @var string
	 */
	public $api_key = '';

	/**
	 * Init properties.
	 */
	public function init_properties() {
		// Define user set variables.
		$this->title   = $this->get_option( 'title' );
		$this->api_key = $this->get_option( 'api_key' );
	}

	/**
	 * Init form fields.
	 */
	public function init_form_fields() {
		$this->instance_form_fields = array(
			'title'      => array(
				'title'       => __( 'Title', 'woocommerce-dropp-shipping' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-dropp-shipping' ),
				'default'     => $this->method_title,
				'desc_tip'    => true,
			),
			'api_key' => array(
				'title'       => __( 'API key', 'woocommerce-dropp-shipping' ),
				'type'        => 'text',
				'placeholder' => __( 'API key from dropp.is. Eg.: NTAyZDIzZGYtNzg0Yi00OWVjLW......', 'woocommerce-dropp-shipping' ),
				'description' => sprintf(
					__( 'Click %s to find your API Key.', 'woocommerce-dropp-shipping' ),
					'<a target="_blank" href="https://stage.dropp.is/dropp/admin/store/api/">'. __( 'here', 'woocommerce-dropp-shipping' ) .'</a>'
				),
				'default'     => '',
				'desc_tip'    => false,
			),
		);
	}

	/**
	 * Get setting form fields for instances of this shipping method within zones.
	 *
	 * @return array
	 */
	public function get_instance_form_fields() {
		return parent::get_instance_form_fields();
	}
}
