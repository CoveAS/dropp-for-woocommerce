<?php
/**
 * Shipping item meta
 *
 * @package dropp-for-woocommerce
 */

namespace Dropp;

/**
 * Shipping item meta
 */
class Shipping_Item_Meta {

	/**
	 * Setup
	 */
	public static function setup() {
		// Add fields to the shipping rate.
		add_action( 'woocommerce_after_shipping_rate', __CLASS__ . '::choose_location_button', 10, 2 );

		// Save the fields during checkout.
		add_action( 'woocommerce_checkout_create_order_shipping_item', __CLASS__ . '::attach_item_meta' );

		// Validation that a location has been selected.
		add_action( 'woocommerce_after_checkout_validation', __CLASS__ . '::validate_location', 10, 2 );

		// Show the location name in the order totals.
		add_filter( 'woocommerce_order_item_get_method_title', __CLASS__ . '::get_order_item_title', 10, 2 );
	}

	/**
	 * Attach item meta
	 *
	 * @param WC_Order_Item_Shipping $item Shipping item.
	 */
	public static function attach_item_meta( $item ) {
		$location_data = WC()->session->get( 'dropp_session_location' );
		$location      = [
			'id'        => preg_replace( '/[^a-z\d\-]/', '', $location_data['id'] ),
			'name'      => $location_data['name'],
			'pricetype' => $location_data['pricetype'],
			'address'   => $location_data['address'],
		];
		$item->add_meta_data( 'dropp_location', $location_data, true );
	}

	/**
	 * Validate location
	 *
	 * @param array    $data   Posted data.
	 * @param WP_Error $errors Error object.
	 */
	public static function validate_location( $data, $errors ) {
		$shipping_methods    = $data['shipping_method'];
		$validation_required = false;
		$instance_id         = 0;
		foreach ( $shipping_methods as $method_id ) {
			if ( preg_match( '/^dropp_is:(\d+)$/', $method_id, $matches ) ) {
				// Note: This validation is not needed for dropp_home.
				$validation_required = true;
				$instance_id = $matches[1];
			}
		}
		if ( ! $validation_required ) {
			// Dropp is not used. No validation needed.
			return;
		}
		$location_data = WC()->session->get( 'dropp_session_location' );
		if ( empty( $location_data ) ) {
			// Validation failed. No location was selected.
			$errors->add(
				'shipping',
				__( 'No location selected. Please select a location for Dropp', 'dropp-for-woocommerce' )
			);
		}
	}

	/**
	 * Get order item title
	 *
	 * @param  string        $title Title.
	 * @param  WC_Order_Item $item  Order Item.
	 * @return string               New title.
	 */
	public static function get_order_item_title( $title, $item ) {
		global $wp;
		if ( empty( $wp->query_vars['order-received'] ) ) {
			// Skip on any page except on the thank you page.
			// @TODO: Also don't skip in the emails.
			return $title;
		}
		if ( 'shipping' !== $item->get_type() ) {
			return $title;
		}
		if ( ! in_array($item->get_method_id(), ['dropp_is', 'dropp_is_oca'] ) ) {
			return $title;
		}
		$location = $item->get_meta( 'dropp_location' );
		if ( empty( $location['name'] ) || ! is_string( $location['name'] ) ) {
			return $title;
		}
		return "{$title} ({$location['name']})";
	}

	/**
	 * Choose location button
	 *
	 * @param  WC_Shipping_Method $method Shipping method.
	 * @param  integer            $index  Index.
	 */
	public static function choose_location_button( $method, $index ) {
		if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
			return;
		}
		if ( ! in_array($method->get_method_id(), ['dropp_is', 'dropp_is_oca'] ) ) {
			return;
		}

		$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );

		if ( ! in_array( $method->get_id(), $chosen_methods ) ) {
			return;
		}
		$location_name = '';
		$location_data = WC()->session->get( 'dropp_session_location' );
		if ( ! empty( $location_data ) ) {
			$location_name = $location_data['name'];
		}

		printf(
			'<div class="dropp-location" data-instance_id="%d" style="display:none"><div class="dropp-location__actions"><span class="dropp-location__button button">%s</span></div><p class="dropp-location__name"%s>%s</p></div><div class="dropp-error" style="display:none"></div>',
			esc_attr( $method->get_instance_id() ),
			esc_html__( 'Choose location', 'dropp-for-woocommerce' ),
			( empty( $location_name ) ? ' style="display:none"' : '' ),
			esc_html( $location_name )
		);
	}
}
