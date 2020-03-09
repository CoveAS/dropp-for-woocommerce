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
        // Add fields to Billing address
        add_filter( 'woocommerce_checkout_fields' , __CLASS__. '::checkout_fields', 10, 1 );

        // Display field value on the order edit page
        add_action( 'woocommerce_admin_order_data_after_billing_address', __CLASS__ . '::admin_order_billing_details', 10, 1 );

        // Display field value in the order emails
        add_action( 'woocommerce_email_customer_details', __CLASS__ . '::order_email_details', 20, 1 );

        // @TODO: Validation
	}

    /**
     * Checkout fields
     *
     * @param  array $fields Checkout fields.
     * @return array         Checkout fields.
     */
    public static function checkout_fields( $fields ) {
        $fields['billing']['dropp_ssn'] = [
            'type'        => 'textarea',
            'label'       => __( 'Social Security Number', 'sage' ),
            'placeholder' => '0000000000',
            'required'    => true,
            'priority'    => 100,
        ];

        return $fields;
    }

    /**
     * Admin order billing details
     *
     * @param WC_Order $order Order.
     */
    public static function admin_order_billling_details( $order ) {
		$dropp_ssn = $order->get_meta( '_billing_dropp_ssn', true );
		require dirname( __DIR__ ) . '/templates/ssn/admin-billing-details.php';
    }

    /**
     * Customer details in the order confirmation
     *
     * @param WC_Order $order Order.
     */
    public static function order_email_details( $order ) {
		$dropp_ssn = $order->get_meta( '_billing_dropp_ssn', true );
		if ( $dropp_ssn ) {
			require dirname( __DIR__ ) . '/templates/ssn/order-details.php';
		}
    }
}
