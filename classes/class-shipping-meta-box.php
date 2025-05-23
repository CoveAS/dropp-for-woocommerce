<?php
/**
 * Shipping item meta
 *
 * @package dropp-for-woocommerce
 */

namespace Dropp;

use Dropp\Actions\Convert_Dropp_Order_Ids_To_Consignments_Action;
use Dropp\Actions\Create_Dropp_Location_Script_Url_Action;
use Dropp\Actions\Get_Shipping_Method_From_Shipping_Item_Action;
use Dropp\Models\Dropp_Consignment;
use Dropp\Models\Dropp_Location;
use Dropp\Models\Dropp_Product_Line;
use WP_Post;

/**
 * Shipping item meta
 */
class Shipping_Meta_Box
{

	/**
	 * Setup
	 */
	public static function setup(): void
	{
		add_action('add_meta_boxes', array(
			__CLASS__,
			'add_booking_meta_boxes'
		), 1);
		add_action('admin_enqueue_scripts', __CLASS__ . '::admin_enqueue_scripts');
	}

	/**
	 * Add booking meta box
	 *
	 * @param string $post_type Post type.
	 */
	public static function add_booking_meta_boxes(string $post_type): void
	{
		if ('shop_order' !== $post_type && 'woocommerce_page_wc-orders' !== $post_type) {
			return;
		}

		add_meta_box(
			'woocommerce-order-dropp-consignments',
			__('Dropp Booked Consignments', 'dropp-for-woocommerce'),
			__CLASS__ . '::render_consignments_meta_box',
			$post_type,
			'normal',
			'high'
		);

		add_meta_box(
			'woocommerce-order-dropp-booking',
			__('Dropp Booking', 'dropp-for-woocommerce'),
			__CLASS__ . '::render_booking_meta_box',
			$post_type,
			'normal',
			'high'
		);
	}

	/**
	 * Admin enqueue script
	 * Add custom styling and javascript to the admin options
	 *
	 * @param string $hook Hook.
	 */
	public static function admin_enqueue_scripts(string $hook): void
	{
		if ('post.php' !== $hook && 'woocommerce_page_wc-orders' !== $hook) {
			return;
		}
		$post_type = get_post_type();
		if ('shop_order' !== $post_type && 'woocommerce_page_wc-orders' !== $hook) {
			return;
		}
		if ('edit' !== ($_GET['action'] ?? '') && 'woocommerce_page_wc-orders' === $hook) {
			return;
		}

		$order            = wc_get_order();
		$order_id         = $order->get_id();
		$adapter          = new Order_Adapter($order);
		$billing_address  = $order->get_address();
		$shipping_address = $order->get_address('shipping');
		$line_items       = $order->get_items('shipping');
		$shipping_items   = [];

		$dropp_methods = array_keys(
			Dropp::get_shipping_methods(true)
		);
		foreach ($line_items as $line_item) {
			if (!in_array($line_item->get_method_id(), $dropp_methods, true)) {
				continue;
			}
			try {
				$shipping_method = (new Get_Shipping_Method_From_Shipping_Item_Action())($line_item);
			} catch (\Exception $e) {
				continue;
			}

			$shipping_items[] = [
				'id'           => $line_item->get_id(),
				'instance_id'  => $line_item->get_instance_id(),
				'label'        => $line_item->get_name(),
				'day_delivery' => $shipping_method->day_delivery,
			];
		}

		$action = new Convert_Dropp_Order_Ids_To_Consignments_Action($adapter);
		$action->handle();

		$consignments = $adapter->consignments()->filter(
			fn(Dropp_Consignment $consignment) => $consignment->status !== 'error'
		);
		// Maybe update the consignments.
		$activeConsignments = $consignments->filter(
			fn ( $consignment ) =>
				! empty( $consignment->dropp_order_id ) &&
				! in_array( $consignment->status, [ 'cancelled', 'error', 'ready' ] )
		);
		$activeConsignments->map('maybe_update');

		if (empty($shipping_address['email'])) {
			$shipping_address['email'] = $billing_address['email'];
		}
		if (empty($shipping_address['phone'])) {
			$shipping_address['phone'] = $billing_address['phone'];
		}
		$shipping_address['ssn'] = $order->get_meta('_billing_dropp_ssn', true);
		$shipping_method         = Shipping_Method\Dropp::get_instance();
		$customer_note           = $order->get_customer_note();
		$delivery_instructions   = '';
		if ($shipping_method->copy_order_notes) {
			$delivery_instructions = $customer_note;
		}
		$locations = [];
		if ($activeConsignments->isEmpty()) {
			$locations = Dropp_Location::array_from_order();
		}
		$dropp_object = [
			'testing'               => (new API())->test,
			'nonce'                 => wp_create_nonce('dropp'),
			'time_now'              => current_time('mysql'),
			'order_id'              => $order_id,
			'ajaxurl'               => admin_url('admin-ajax.php'),
			'dropplocationsurl'     => (new Create_Dropp_Location_Script_Url_Action())(),
			'customer_note'         => $customer_note,
			'delivery_instructions' => $delivery_instructions,
			'storeid'               => $shipping_method->store_id,
			'ssn_enabled'           => $shipping_method->enable_ssn,
			'products'              => Dropp_Product_Line::array_from_order(),
			'consignments'          => $consignments->map('to_array', false),
			'customer'              => $shipping_address,
			'shipping_items'        => $shipping_items,
			'status_list'           => Dropp_Consignment::get_status_list(),
			'locations'             => $locations,
			'special_locations'     => [
				'dropp_home'      => [
					'label'    => __('Add home delivery', 'dropp-for-woocommerce'),
					'location' => new Dropp_Location('dropp_home'),
				],
				'dropp_flytjandi' => [
					'label'    => __('Add Samskip delivery', 'dropp-for-woocommerce'),
					'location' => new Dropp_Location('dropp_flytjandi'),
				],
				'dropp_pickup'    => [
					'label'    => __('Add Pickup at warehouse', 'dropp-for-woocommerce'),
					'location' => new Dropp_Location('dropp_pickup'),
				],
			],
			'i18n'                  => self::nbsp(
				[
					'test'                   => __('Test', 'dropp-for-woocommerce'),
					'actions'                => __('Actions', 'dropp-for-woocommerce'),
					'check_status'           => __('Check status', 'dropp-for-woocommerce'),
					'download'               => __('Download', 'dropp-for-woocommerce'),
					'customer_note'          => __('Customer note', 'dropp-for-woocommerce'),
					'copy_to_delivery'       => __('Copy note to delivery instructions', 'dropp-for-woocommerce'),
					'delivery_instructions'  => __('Delivery instructions', 'dropp-for-woocommerce'),
					'update_order'           => __('Update order', 'dropp-for-woocommerce'),
					'extra_pdf'              => __('Add extra pdf', 'dropp-for-woocommerce'),
					'view_order'             => __('View order', 'dropp-for-woocommerce'),
					'cancel_order'           => __('Cancel order', 'dropp-for-woocommerce'),
					'barcode'                => __('Barcode', 'dropp-for-woocommerce'),
					'customer'               => __('Customer details', 'dropp-for-woocommerce'),
					'status'                 => __('Status', 'dropp-for-woocommerce'),
					'created'                => __('Created', 'dropp-for-woocommerce'),
					'updated'                => __('Updated', 'dropp-for-woocommerce'),
					'product'                => __('Product', 'dropp-for-woocommerce'),
					'products'               => __('Products', 'dropp-for-woocommerce'),
					'booked_consignments'    => __('Booked consignments', 'dropp-for-woocommerce'),
					'submit'                 => __('Book now', 'dropp-for-woocommerce'),
					'remove'                 => __('Remove location', 'dropp-for-woocommerce'),
					'add_location'           => __('Add shipment', 'dropp-for-woocommerce'),
					'change_location'        => __('Change pick up point', 'dropp-for-woocommerce'),
					'unknown_location'       => __('Unknown location', 'dropp-for-woocommerce'),
					'name'                   => __('Name', 'dropp-for-woocommerce'),
					'email_address'          => __('Email address', 'dropp-for-woocommerce'),
					'social_security_number' => __('Social security number', 'dropp-for-woocommerce'),
					'address'                => __('Address', 'dropp-for-woocommerce'),
					'phone_number'           => __('Phone number', 'dropp-for-woocommerce'),
					'day_delivery'           => __('day delivery', 'dropp-for-woocommerce'),
					'pick_up_point'          => __('Pick up point', 'dropp-for-woocommerce'),
					'weight'                 => __('Weight', 'dropp-for-woocommerce'),
					'total_weight'           => __('Total weight', 'dropp-for-woocommerce'),
					'quantity'               => __('Quantity', 'dropp-for-woocommerce'),
					'close_modal'            => __('Close', 'dropp-for-woocommerce'),
					'item'            => __('Item', 'dropp-for-woocommerce'),
					'products_description'            => __('Select items to book for shipping', 'dropp-for-woocommerce'),
				],
				[
					'error'  => __('Error', 'dropp-for-woocommerce'),
					'booking_error_general'  => __('An error occured while booking the consignment. Please try again or contact us at dropp@dropp.is if you continue to experience problems.', 'dropp-for-woocommerce'),
					'booking_error_connection'  => __('We lost connection with your website while booking the consignment. Please check your internet connection, refresh the page and/or try again later. If you continue to experience problems please contact us at dropp@dropp.is', 'dropp-for-woocommerce'),
					'no_consignments'  => __('No booked consignments yet. Please use the booking form to create new consignments.', 'dropp-for-woocommerce'),
					'weight_limit_exceeded'  => __('Weight limit exceeded', 'dropp-for-woocommerce'),
					'weight_limit_exceeded_message'  => __('The weight limit for the chosen location has been exceeded. Please reduce the number of products in the consignment to continue.', 'dropp-for-woocommerce'),
					'quantity_exceeded' => __('Quantity exceeded', 'dropp-for-woocommerce'),
					'quantity_exceeded_message' => __('Uh-oh! It seems like the quantity of selected items exceeds more than what’s allowed in the order. If this is intentional you can ignore this warning.', 'dropp-for-woocommerce'),
				]
			),
		];

		if (!Dropp::is_pickup_enabled($shipping_method)) {
			unset($dropp_object['special_locations']['dropp_pickup']);
		}

		wp_enqueue_style('dropp-admin-css', plugin_dir_url( __DIR__ ) . '/assets/css/dropp-admin.css', [], Dropp::VERSION);
		wp_enqueue_script('dropp-admin-js', plugin_dir_url(__DIR__) . '/assets/js/dropp-admin.js', [], Dropp::VERSION, true);
		wp_localize_script('dropp-admin-js', '_dropp', $dropp_object);
	}

	/**
	 * Non-breaking spaces
	 *
	 * @param array $strings Strings.
	 *
	 * @return array          Strings.
	 */
	public static function nbsp(array $strings, array $merge): array
	{
		foreach ($strings as &$string) {
			$string = str_replace(' ', '&nbsp;', $string);
		}

		return [...$strings, ...$merge];
	}

	/**
	 * Render booking meta box
	 */
	public static function render_booking_meta_box(): void
	{
		echo '<div id="dropp-booking"><span class="loading-message" v-if="0">Loading ...</span></div>';
	}

	public static function render_consignments_meta_box(): void
	{
		echo '<div id="dropp-consignments"><span class="loading-message" v-if="0">Loading ...</span></div>';
	}
}

