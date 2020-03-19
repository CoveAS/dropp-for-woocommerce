=== Dropp for WooCommerce ===
Contributors: Forsvunnet
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html
Tags: Shipping, WooCommerce, Iceland
Requires at least: 5.2
Tested up to: 5.3.2
Stable tag: 1.1.1
Requires PHP: 7.1

Deliver parcels at delivery locations in Iceland

== Installation ==

1. Download the plugin [here](https://github.com/CoveAS/woocommerce-dropp-shipping/archive/1.0.0.zip)
2. Unzip the archive into your `wp-content/plugins` directory.
3. Rename the plugin directory from **woocommerce-dropp-shipping-1.x** to **woocommerce-dropp-shipping**
4. Activate the plugin under WP-Admin → Plugins

== Configuration ==

### Adding the shipping method to a shipping zone

1. Navigate to WooCommerce → Settings → Shipping
2. Click on a zone you want to activate Dropp for. For more information about WooCommerce zones click [here](https://docs.woocommerce.com/document/setting-up-shipping-zones/)
3. Click on **Add shipping method**
4. Select **Dropp** in the dropdown menu and click **Add shipping method** in the modal.
5. Click on the shipping method that was added to configure the name and price.

### Connect to the Dropp.is API for booking

1. Navigate to  WooCommerce → Settings → Shipping → Dropp
2. Fill in your API key and Store ID
3. Click the **Save changes** button

== Booking ==

### Bulk booking

On the orders view in the admin panel you can select all the orders you want to book by checking the checkbox to the left of the order. Next select "Dropp - Book orders" in the Bulk Actions dropdown menu and click the **Apply** button. Only orders that have 0 in the Dropp column will be booked using bulk booking. After the orders have been booked you will be given a link to download the labels for the selected orders. If an order for some reason could not be booked then you will have to book those individually. The number in the Dropp column in the orders table indicate how many times an order has been successfully booked.

### Individual booking

On the order screen there is a new meta box for Dropp booking. An order can be shipped to multiple locations or have multiple consignments sent to the same location. Click on the **Add shipment** button to add additional shipments.

For each shipment you can select which products should be sent and the quantity of each product. When you are ready to book, click the **Book now** button.

When an order has the status **Initial** it can be updated or cancelled.

### Enabling Dropp for orders that has a different shipping method

If the order does not have a dropp shipping method attached to one of the order lines then dropp booking will not be available. To enable it simply add a new shipping line to the order and edit it to use dropp shipping. If an order cannot be edited then try to change the order status to **pending** first.

== Changelog ==

= 1.1.1 =

* Added icelandic translations
* Changed plugin name from WooCommerce Dropp Shipping to Dropp for WooCommerce
* Corrected the login URL for getting the live API key
* Fixed a bug that caused dropp consignments to revert back to test-mode

= 1.1.0 =

* Implemented status updates from dropp
* Implemented methods to cancel and update dropp orders
* Added bulk booking and printing

= 1.0.0 =

* First version
