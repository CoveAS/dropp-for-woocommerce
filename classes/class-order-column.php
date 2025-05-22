<?php

namespace Dropp;

use Automattic\WooCommerce\Admin\Overrides\Order;

class Order_Column
{

	public static function setup() {

		add_filter( 'manage_woocommerce_page_wc-orders_columns', self::class.'::columns', 21);
		add_filter( 'manage_woocommerce_page_wc-orders_custom_column',  self::class.'::intercept', 19, 2 );
		add_filter( 'manage_woocommerce_page_wc-orders_custom_column',  self::class.'::modify', 21, 2 );


	}

	public static function columns(array $columns): array
	{
		if (empty($columns['shipment_column'])) {
			$columns['shipment_column'] = __('Shipment', 'dropp-for-woocommerce');
		}
		if (empty($columns['pdf_column'])) {
			$columns['pdf_column'] = __('PDF', 'dropp-for-woocommerce');
		}
		return $columns;
	}

	/**
	 * @param string $column
	 * @param Order $order
	 * @return void
	 */
	public static function intercept($column, $order) {
		$adapter = new Order_Adapter($order);
		if ( ! $adapter->is_dropp() ) {
			return;
		}
		if (in_array($column, [
			'shipment_column',
			'pdf_column'
		], true)) {
			ob_start();
		}
	}
	public static function modify( $column, $order ) {
		$adapter = new Order_Adapter($order);
		if ( ! $adapter->is_dropp() ) {
			return;
		}
		if (in_array($column, [
			'shipment_column',
			'pdf_column'
		], true)) {
			$buffer = ob_get_clean();
		} else {
			return;
		}

		echo self::render( $column, $adapter);
	}

	public static function render(string $column, Order_Adapter $adapter): string
	{
		$booked = $adapter->count_consignments(true);
		$started = $adapter->count_consignments();

		if ('shipment_column' === $column) {
			if ($booked || $started) {
				// View order button
				return sprintf(
					'<a class="dropp-button dropp-shipment-view-link" href="%s">%s</a>',
					esc_url($adapter->order->get_edit_order_url()),
					esc_html__('View order', 'dropp-for-woocommerce')
				);
			} else {
				// Book now button with AJAX attributes
				return sprintf(
					'<a class="dropp-button dropp-shipment-book-link" href="#" data-order-id="%d" data-action="dropp_book_shipment">%s</a>',
					$adapter->order->get_id(),
					esc_html__('Book now', 'dropp-for-woocommerce')
				);
			}
		}

		if ('pdf_column' === $column) {
			if ($booked) {
				$tooltip = esc_attr__('Download your shipment', 'dropp-for-woocommerce');
				$icon = sprintf(
					'<span data-tip="%s" class="tips dashicons dashicons-download"></span>',
					$tooltip
				);

				return sprintf(
					'<a class="dropp-button dropp-button--secondary dropp-shipment-download-link" href="%s" target="_blank">%s %s</a>',
					$adapter->get_download_url(),
					esc_html__('Download', 'dropp-for-woocommerce'),
					$icon
				);
			}
		}
		return '';
	}
}
