<?php
/**
 * Pending shipping
 *
 * @package woocommerce-dropp-shipping
 */

namespace Dropp;


/**
 * Pending shipping
 *
 * Creates a new order status for pending shipping.
 * Also adds a status column to the order view.
 */
class Pending_Shipping {
	/**
	 * Setup
	 */
	public static function setup() {
		add_filter( 'manage_edit-shop_order_columns', __CLASS__ . '::dropp_status_column', 15 );
		add_action( 'manage_shop_order_posts_custom_column', __CLASS__ . '::dropp_column_value' );
	}

	/**
	 * Dropp status column
	 *
	 * @param  array $columns Columns.
	 * @return array          Columns.
	 */
	public static function dropp_status_column( $columns ) {
		$columns['dropp_booking_count'] = __( 'Dropp', 'woocommerce-dropp-shipping' );
		return $columns;
	}

	/**
	 * Get booking column value
	 *
	 * @param string $column Column.
	 */
	public static function dropp_column_value( $column ) {
		global $the_order;

		if ( 'dropp_booking_count' === $column ) {
			$count = Dropp_Consignment::count_consignments_on_order( $the_order );
			echo esc_html( $count );
		}
	}
}
