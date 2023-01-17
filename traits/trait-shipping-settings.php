<?php
/**
 * Shipping settings
 *
 * @package dropp-for-woocommerce
 */

namespace Dropp;

/**
 * Shipping settings
 */
trait Shipping_Settings
{


	/**
	 * API Key.
	 *
	 * @var string
	 */
	public string $api_key = '';

	/**
	 * @var string
	 */
	public string $api_key_test;

	/**
	 * @var string
	 */
	public string $store_id;

	/**
	 * @var string
	 */
	public string $new_order_status;

	/**
	 * @var bool
	 */
	public bool $enable_return_labels;

	/**
	 * @var string
	 */
	public string $copy_order_notes;


	/**
	 * @var bool
	 */
	public bool $test_mode;

	/**
	 * @var bool
	 */
	public bool $debug_mode;

	/**
	 * @var bool
	 */
	public bool $enable_ssn;

	/**
	 * @var bool
	 */
	public bool $require_ssn;

	/**
	 * @var bool
	 */
	public bool $location_name_in_label;

	/**
	 * Init properties.
	 */
	public function init_properties()
	{
		// Define user set variables.
		$this->title                  = $this->get_option('title');
		$this->api_key                = $this->get_option('api_key');
		$this->api_key_test           = $this->get_option('api_key_test');
		$this->store_id               = $this->get_option('store_id');
		$this->new_order_status       = $this->get_option('new_order_status');
		$this->enable_return_labels   = 'yes' === $this->get_option('enable_return_labels');
		$this->copy_order_notes       = $this->get_option('copy_order_comment', 'yes');
		$this->test_mode              = 'yes' === $this->get_option('test_mode');
		$this->debug_mode             = 'yes' === $this->get_option('debug_mode');
		$this->enable_ssn             = 'yes' === $this->get_option('enable_ssn');
		$this->require_ssn            = 'yes' === $this->get_option('require_ssn', 'yes');
		$this->location_name_in_label = 'yes' === $this->get_option('location_name_in_label');
	}

	/**
	 * Init form fields.
	 */
	public function init_form_fields()
	{
		$this->form_fields = [
			'api_key'                => [
				'title'       => __('API key', 'dropp-for-woocommerce'),
				'type'        => 'text',
				'placeholder' => __('API key from dropp.is. Eg.: NTAyZDIzZGYtNzg0Yi00OWVjLW......',
					'dropp-for-woocommerce'),
				'description' => sprintf(
					__('Click %s to find your API Key.', 'dropp-for-woocommerce'),
					'<a target="_blank" href="https://umsjon.dropp.is/login">'.__('here',
						'dropp-for-woocommerce').'</a>'
				),
				'default'     => '',
				'desc_tip'    => false,
			],
			'api_key_test'           => [
				'title'       => __('API key (test)', 'dropp-for-woocommerce'),
				'type'        => 'text',
				'placeholder' => __('API key from dropp.is. Eg.: NTAyZDIzZGYtNzg0Yi00OWVjLW......',
					'dropp-for-woocommerce'),
				'description' => sprintf(
					__('Click %s to find your test API Key.', 'dropp-for-woocommerce'),
					'<a target="_blank" href="https://stage.dropp.is/dropp/admin/store/api/">'.__('here',
						'dropp-for-woocommerce').'</a>'
				),
				'default'     => '',
				'desc_tip'    => false,
			],
			'store_id'               => [
				'title'       => __('Store ID', 'dropp-for-woocommerce'),
				'type'        => 'text',
				'placeholder' => __('Store ID from dropp.is', 'dropp-for-woocommerce'),
				'description' => '',
				'default'     => '',
				'desc_tip'    => true,
			],
			'new_order_status'       => [
				'title'       => __('New order status', 'dropp-for-woocommerce'),
				'type'        => 'select',
				'description' => __('Automatically change order status after booking.', 'dropp-for-woocommerce'),
				'class'       => 'chosen_select',
				'css'         => 'width: 400px;',
				'options'     => ['' => __('- No change -', 'dropp-for-woocommerce')] + wc_get_order_statuses(),
				'desc_tip'    => true,
				'default'     => '',
			],
			'dropp_rates_first'      => [
				'title'       => __('Dropp first', 'dropp-for-woocommerce'),
				'label'       => __('Put dropp shipping rates first in cart and checkout', 'dropp-for-woocommerce'),
				'type'        => 'checkbox',
				'default'     => 'yes'
			],
			'enable_return_labels'   => [
				'title'       => __('Return labels', 'dropp-for-woocommerce'),
				'label'       => __('Generate return labels when booking', 'dropp-for-woocommerce'),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no',
				'desc_tip'    => false,
			],
			'copy_order_notes'       => [
				'title'       => __('Copy customer notes ', 'dropp-for-woocommerce'),
				'label'       => __('Copy customer notes from the order to delivery instructions in the dropp booking',
					'dropp-for-woocommerce'),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'yes',
				'desc_tip'    => false,
			],
			'enable_ssn'             => [
				'title'       => __('Social security number', 'dropp-for-woocommerce'),
				'label'       => __('Enable social security number', 'dropp-for-woocommerce'),
				'type'        => 'checkbox',
				'description' => __('Enables a new field on checkout for icelandic social security number.',
					'dropp-for-woocommerce'),
				'default'     => '',
				'desc_tip'    => true,
			],
			'require_ssn'            => [
				'title'       => __('Require SSN', 'dropp-for-woocommerce'),
				'label'       => __('Required field', 'dropp-for-woocommerce'),
				'type'        => 'checkbox',
				'description' => __('Make social security a required field on checkout.', 'dropp-for-woocommerce'),
				'default'     => 'yes',
				'desc_tip'    => false,
			],
			'location_name_in_label' => [
				'title'    => __('Location in label', 'dropp-for-woocommerce'),
				'label'    => __('Enable location name in the shipping item label', 'dropp-for-woocommerce'),
				'type'     => 'checkbox',
				'default'  => '',
				'desc_tip' => false,
			],
			'test_mode'              => [
				'title'       => __('Test mode', 'dropp-for-woocommerce'),
				'label'       => __('Enable test mode', 'dropp-for-woocommerce'),
				'type'        => 'checkbox',
				'description' => sprintf(
					__('Makes the plugin do requests against staging instead of the live API.',
						'dropp-for-woocommerce'),
					'<a href="#">'.__('here', 'dropp-for-woocommerce').'</a>'
				),
				'default'     => '',
				'desc_tip'    => true,
			],
			'debug_mode'             => [
				'title'       => __('Debug mode', 'dropp-for-woocommerce'),
				'label'       => __('Enable debug mode', 'dropp-for-woocommerce'),
				'type'        => 'checkbox',
				'description' => sprintf(
					__('Logs requests and other data to a log file. Click %s to see the logs.',
						'dropp-for-woocommerce'),
					'<a href="'.admin_url('admin.php?page=wc-status&tab=logs').'">'.__('here',
						'dropp-for-woocommerce').'</a>'
				),
				'default'     => '',
				'desc_tip'    => false,
			],
		];
	}

	/**
	 * Get setting form fields for instances of this shipping method within zones.
	 *
	 * @return array
	 */
	public function get_instance_form_fields()
	{
		$form_fields                     = parent::get_instance_form_fields();
		$form_fields['title']['default'] = $this->method_title;

		return $form_fields;
	}
}
