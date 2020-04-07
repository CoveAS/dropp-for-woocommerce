<?php
/**
 * Shipping item meta
 *
 * @package woocommerce-dropp-shipping
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
		add_action( 'woocommerce_checkout_create_order_shipping_item', __CLASS__ . '::attach_item_meta', 10, 4 );

		// Validation that a location has been selected.
		add_action( 'woocommerce_after_checkout_validation', __CLASS__ . '::validate_location', 10, 2 );

		// Show the location name in the order totals.
		add_filter( 'woocommerce_order_item_get_method_title', __CLASS__ . '::get_order_item_title', 10, 2 );
	}

	/**
	 * Attach item meta
	 *
	 * @param WC_Order_Item_Shipping $item        Shipping item.
	 * @param integer                $package_key Package key.
	 * @param array                  $package     Package.
	 * @param WC_Order               $order       Order.
	 */
	public static function attach_item_meta( $item, $package_key, $package, $order ) {
		$options  = FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_BACKTICK;
		$keys     = [
			'id'      => sprintf( 'dropp_%d_location_%s%d', $item->get_method_id(), 'id', $item->get_instance_id() ),
			'name'    => sprintf( 'dropp_%d_location_%s%d', $item->get_method_id(), 'name', $item->get_instance_id() ),
			'address' => sprintf( 'dropp_%d_location_%s%d', $item->get_method_id(), 'address', $item->get_instance_id() ),
		];
		$location = [
			'id'      => preg_replace( '/[^a-z\d\-]/', '', $_POST[ $keys['id'] ] ),
			'name'    => filter_input( INPUT_POST, $keys['name'], FILTER_SANITIZE_STRING, $options ),
			'address' => filter_input( INPUT_POST, $keys['address'], FILTER_SANITIZE_STRING, $options ),
		];

		$item->add_meta_data( 'dropp_location', $location, true );
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
		foreach ( $shipping_methods as $method_id ) {
			if ( preg_match( '/^dropp_is/', $method_id ) ) {
				// Note: This validation is not needed for dropp_home
				$validation_required = true;
			}
		}
		if ( ! $validation_required ) {
			// Dropp is not used. No validation needed.
			return;
		}

		// Assume that the posted data is not valid.
		$valid       = false;
		$posted_keys = array_keys( $_POST );
		foreach ( $posted_keys as $posted_key ) {
			if ( preg_match( '/^dropp_\d+_location_id\d+$/', $posted_key, $matches ) ) {
				// Found a correct match in posted data.
				if ( ! empty( $_POST[ $matches[0] ] ) ) {
					// This means that a location has been selected.
					$valid = true;
				}
			}
		}
		if ( ! $valid ) {
			// Validation failed. No location was selected.
			$errors->add(
				'shipping',
				__( 'No location selected. Please select a location for Dropp', 'woocommerce-dropp-shipping' )
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
		if ( 'dropp_is' !== $item->get_method_id() ) {
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
		if ( 'dropp_is' !== $method->get_method_id() ) {
			return;
		}
		$format = '<input class="dropp-location__input--%s" name="dropp_%d_location_%1$s%d" type="hidden" value="">';
		$keys   = [ 'id', 'address', 'name' ];
		$fields = [];
		foreach ( $keys as $key ) {
			$fields[] = sprintf(
				$format,
				$key,
				esc_attr( $index ),
				esc_attr( $method->get_instance_id() )
			);
		}
		printf(
			'<div class="dropp-location" style="display:none"><div class="dropp-location__actions"><span class="dropp-location__button button">%s</span></div>%s<p class="dropp-location__name" style="display:none"></p></div><div class="dropp-error" style="display:none"></div>',
			esc_html__( 'Choose location', 'woocommerce-dropp-shipping' ),
			implode( '', $fields )
		);
	}
}
