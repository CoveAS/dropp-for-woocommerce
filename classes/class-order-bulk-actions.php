<?php
/**
 * Ajax
 *
 * @package woocommerce-dropp-shipping
 */

namespace Dropp;

use WC_Logger;

/**
 * Ajax
 */
class Order_Bulk_Actions {
	/**
	 * Setup
	 */
	public static function setup() {
		add_filter( 'bulk_actions-edit-shop_order', __CLASS__ . '::define_bulk_actions' );
		add_filter( 'admin_notices', __CLASS__ . '::bulk_admin_notices' );
		add_filter( 'handle_bulk_actions-edit-shop_order', __CLASS__ . '::handle_bulk_actions', 10, 3 );
	}


	/**
	 * Define bulk actions.
	 *
	 * @param  array $actions Existing actions.
	 * @return array
	 */
	public static function define_bulk_actions( $actions ) {
		$actions['dropp_bulk_booking']  = __( 'Dropp - Book orders', 'woocommerce' );
		$actions['dropp_bulk_printing'] = __( 'Dropp - Print labels', 'woocommerce' );
		return $actions;
	}


	/**
	 * Handle bulk actions.
	 *
	 * @param  string $redirect_to URL to redirect to.
	 * @param  string $action      Action name.
	 * @param  array  $ids         List of ids.
	 * @return string
	 */
	public static function handle_bulk_actions( $redirect_to, $action, $ids ) {
		if ( 'dropp_bulk_booking' === $action ) {
			return handle_bulk_booking( $redirect_to, $ids );
		}
		if ( 'dropp_bulk_printing' === $action ) {
			return handle_bulk_booking( $redirect_to, $ids );
		}
		return $redirect_to;
	}

	/**
	 * Handle bulk actions.
	 *
	 * @param  string $redirect_to URL to redirect to.
	 * @param  array  $ids         List of ids.
	 * @return string
	 */
	public static function handle_bulk_booking( $redirect_to, $ids ) {
		if ( $changed ) {
			$redirect_to = add_query_arg(
				array(
					'post_type'   => $this->list_table_type,
					'bulk_action' => $report_action,
					'changed'     => $changed,
					'ids'         => join( ',', $ids ),
				),
				$redirect_to
			);
		}
		return esc_url_raw( $redirect_to );
	}

	/**
	 * Handle bulk actions.
	 *
	 * @param  string $redirect_to URL to redirect to.
	 * @param  array  $ids         List of ids.
	 * @return string
	 */
	public static function handle_bulk_printing( $redirect_to, $ids ) {
		if ( $changed ) {
			$redirect_to = add_query_arg(
				array(
					'post_type'   => $this->list_table_type,
					'bulk_action' => $report_action,
					'changed'     => $changed,
					'ids'         => join( ',', $ids ),
				),
				$redirect_to
			);
		}
		return esc_url_raw( $redirect_to );
	}

	/**
	 * Show confirmation message that order status changed for number of orders.
	 */
	public static function bulk_admin_notices() {
		global $post_type, $pagenow;

		// Bail out if not on shop order list page.
		if ( 'edit.php' !== $pagenow || 'shop_order' !== $post_type || ! isset( $_REQUEST['bulk_action'] ) ) { // WPCS: input var ok, CSRF ok.
			return;
		}

		$order_statuses = wc_get_order_statuses();
		$number         = isset( $_REQUEST['changed'] ) ? absint( $_REQUEST['changed'] ) : 0; // WPCS: input var ok, CSRF ok.
		$bulk_action    = wc_clean( wp_unslash( $_REQUEST['bulk_action'] ) ); // WPCS: input var ok, CSRF ok.

		// Check if any status changes happened.
		foreach ( $order_statuses as $slug => $name ) {
			if ( 'marked_' . str_replace( 'wc-', '', $slug ) === $bulk_action ) { // WPCS: input var ok, CSRF ok.
				/* translators: %d: orders count */
				$message = sprintf( _n( '%d order status changed.', '%d order statuses changed.', $number, 'woocommerce' ), number_format_i18n( $number ) );
				echo '<div class="updated"><p>' . esc_html( $message ) . '</p></div>';
				break;
			}
		}

		if ( 'removed_personal_data' === $bulk_action ) { // WPCS: input var ok, CSRF ok.
			/* translators: %d: orders count */
			$message = sprintf( _n( 'Removed personal data from %d order.', 'Removed personal data from %d orders.', $number, 'woocommerce' ), number_format_i18n( $number ) );
			echo '<div class="updated"><p>' . esc_html( $message ) . '</p></div>';
		}
	}
}
