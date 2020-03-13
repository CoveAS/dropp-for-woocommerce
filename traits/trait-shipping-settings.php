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
		$this->title            = $this->get_option( 'title' );
		$this->api_key          = $this->get_option( 'api_key' );
		$this->api_key_test     = $this->get_option( 'api_key_test' );
		$this->store_id         = $this->get_option( 'store_id' );
		$this->new_order_status = $this->get_option( 'new_order_status' );
		$this->test_mode        = 'yes' === $this->get_option( 'test_mode' );
		$this->debug_mode       = 'yes' === $this->get_option( 'debug_mode' );
		$this->enable_ssn       = 'yes' === $this->get_option( 'enable_ssn' );
		$this->require_ssn      = 'yes' === $this->get_option( 'require_ssn' );
	}

	/**
	 * Init form fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'api_key' => array(
				'title'       => __( 'API key', 'woocommerce-dropp-shipping' ),
				'type'        => 'text',
				'placeholder' => __( 'API key from dropp.is. Eg.: NTAyZDIzZGYtNzg0Yi00OWVjLW......', 'woocommerce-dropp-shipping' ),
				'description' => sprintf(
					__( 'Click %s to find your API Key.', 'woocommerce-dropp-shipping' ),
					'<a target="_blank" href="https://umsjon.dropp.is/login">' . __( 'here', 'woocommerce-dropp-shipping' ) . '</a>'
				),
				'default'     => '',
				'desc_tip'    => false,
			),
			'api_key_test' => array(
				'title'       => __( 'API key (test)', 'woocommerce-dropp-shipping' ),
				'type'        => 'text',
				'placeholder' => __( 'API key from dropp.is. Eg.: NTAyZDIzZGYtNzg0Yi00OWVjLW......', 'woocommerce-dropp-shipping' ),
				'description' => sprintf(
					__( 'Click %s to find your test API Key.', 'woocommerce-dropp-shipping' ),
					'<a target="_blank" href="https://stage.dropp.is/dropp/admin/store/api/">' . __( 'here', 'woocommerce-dropp-shipping' ) . '</a>'
				),
				'default'     => '',
				'desc_tip'    => false,
			),
			'store_id' => array(
				'title'       => __( 'Store ID', 'woocommerce-dropp-shipping' ),
				'type'        => 'text',
				'placeholder' => __( 'Store ID from dropp.is', 'woocommerce-dropp-shipping' ),
				'description' => '',
				'default'     => '',
				'desc_tip'    => true,
			),
			'new_order_status' => array(
				'title'       => __( 'New order status', 'woocommerce-dropp-shipping' ),
				'type'        => 'select',
				'description' => __( 'Automatically change order status after booking.', 'woocommerce-dropp-shipping' ),
				'class'       => 'chosen_select',
				'css'         => 'width: 400px;',
				'options'     => array( '' => __( '- No change -', 'woocommerce-dropp-shipping' ) ) + wc_get_order_statuses(),
				'desc_tip'    => true,
				'default'     => '',
			),
			'enable_ssn' => array(
				'title'       => __( 'Social security number', 'woocommerce-dropp-shipping' ),
				'label'       => __( 'Enable social security number', 'woocommerce-dropp-shipping' ),
				'type'        => 'checkbox',
				'description' => __( 'Enables a new field on checkout for icelandic social security number.', 'woocommerce-dropp-shipping' ),
				'default'     => '',
				'desc_tip'    => false,
			),
			'require_ssn' => array(
				'title'       => __( 'Require SSN', 'woocommerce-dropp-shipping' ),
				'label'       => __( 'Required field', 'woocommerce-dropp-shipping' ),
				'type'        => 'checkbox',
				'description' => __( 'Make social security a required field on checkout.', 'woocommerce-dropp-shipping' ),
				'default'     => 'yes',
				'desc_tip'    => false,
			),
			'test_mode' => array(
				'title'       => __( 'Test mode', 'woocommerce-dropp-shipping' ),
				'label'       => __( 'Enable test mode', 'woocommerce-dropp-shipping' ),
				'type'        => 'checkbox',
				'description' => sprintf(
					__( 'Makes the plugin do requests against staging instead of the live API.', 'woocommerce-dropp-shipping' ),
					'<a href="#">' . __( 'here', 'woocommerce-dropp-shipping' ) . '</a>'
				),
				'default'     => '',
				'desc_tip'    => true,
			),
			'debug_mode' => array(
				'title'       => __( 'Debug mode', 'woocommerce-dropp-shipping' ),
				'label'       => __( 'Enable debug mode', 'woocommerce-dropp-shipping' ),
				'type'        => 'checkbox',
				'description' => sprintf(
					__( 'Logs requests and other data to a log file. Click %s to see the logs.', 'woocommerce-dropp-shipping' ),
					'<a href="' . admin_url( 'admin.php?page=wc-status&tab=logs' ) . '">' . __( 'here', 'woocommerce-dropp-shipping' ) . '</a>'
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
		$form_fields                     = parent::get_instance_form_fields();
		$form_fields['title']['default'] = __( 'Dropp', 'woocommerce' );
		return $form_fields;
	}
}
