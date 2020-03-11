<?php
/**
 * Social security number
 *
 * @package woocommerce-dropp-shipping
 */

namespace Dropp;

/**
 * Social security number
 */
class Social_Security_Number {

	/**
	 * Setup
	 */
	public static function setup() {

		// Display field value on the order edit page
		add_action( 'woocommerce_admin_order_data_after_billing_address', __CLASS__ . '::admin_order_billing_details', 10, 1 );

		// Display field value in the order emails
		add_action( 'woocommerce_email_customer_details', __CLASS__ . '::order_email_details', 20, 1 );

		// Display field value on the thank you page and order page
		add_filter( 'woocommerce_order_details_after_customer_details', __CLASS__ . '::after_customer_details', 10, 1 );

		$shipping_method = new Shipping_Method;
		if ( $shipping_method->enable_ssn ) {
			// Add fields to Billing address
			add_filter( 'woocommerce_checkout_fields' , __CLASS__. '::checkout_fields', 10, 1 );

			// Validate SSN number
			add_action( 'woocommerce_after_checkout_validation', __CLASS__ . '::validate_ssn', 10, 2 );
		}
	}

	/**
	 * Validate ssn
	 *
	 * @param  array $fields Checkout fields.
	 * @return array         Checkout fields.
	 */
	public static function validate_ssn( $data, $error ) {
		if ( empty( $data['billing_dropp_ssn'] ) ) {
			return;
		}
		$ssn = $data['billing_dropp_ssn'];
		if ( ! preg_match( '/^\d{10}$/', $ssn ) ) {
			$error->add( 'billing', __( 'Social security number must be 10 digits.', 'woocommerce-dropp-shipping' ) );
			return;
		}
		$nums  = str_split( $ssn );
		$combo = (3 * $nums[0]) + (2 * $nums[1]) + (7 * $nums[2]) + (6 * $nums[3]) + (5 * $nums[4]) + (4 * $nums[5]) + (3 * $nums[6]) + (2 * $nums[7]) + (1 * $nums[8]);
		if ( $combo % 11 <= 0 ) {
			$error->add( 'billing', __( 'Invalid social security number.', 'woocommerce-dropp-shipping' ) );
		}
	}

	/**
	 * Checkout fields
	 *
	 * @param  array $fields Checkout fields.
	 * @return array         Checkout fields.
	 */
	public static function checkout_fields( $fields ) {
		// Get the shipping method.
		$shipping_method = new Shipping_Method;

		// Add the new field.
		$fields['billing']['billing_dropp_ssn'] = [
			'type'        => 'textarea',
			'label'       => __( 'Social Security Number', 'sage' ),
			'placeholder' => '0000000000',
			'required'    => $shipping_method->require_ssn,
			'priority'    => 100,
		];

		return $fields;
	}

	/**
	 * After customer details
	 *
	 * @param  WC_Order $order   Order.
	 */
	public static function after_customer_details( $order ) {
		$dropp_ssn = $order->get_meta( '_billing_dropp_ssn', true );
		if ( $dropp_ssn ) {
			require dirname( __DIR__ ) . '/templates/ssn/customer-details.php';
		}
	}

	/**
	 * Admin order billing details
	 *
	 * @param WC_Order $order Order.
	 */
	public static function admin_order_billing_details( $order ) {
		$dropp_ssn = $order->get_meta( '_billing_dropp_ssn', true );
		if ( $dropp_ssn ) {
			require dirname( __DIR__ ) . '/templates/ssn/admin-billing-details.php';
		}
	}

	/**
	 * Customer details in the order confirmation
	 *
	 * @param WC_Order $order Order.
	 */
	public static function order_email_details( $order ) {
		$dropp_ssn = $order->get_meta( '_billing_dropp_ssn', true );
		if ( $dropp_ssn ) {
			require dirname( __DIR__ ) . '/templates/ssn/email-order-details.php';
		}
	}
}
