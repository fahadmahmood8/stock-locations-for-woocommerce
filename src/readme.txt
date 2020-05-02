=== Stock Locations for WooCommerce ===
Contributors: alexmigf
Tags: woocommerce, stock, stock locations, barcode, barcodes, ean, upc, asin, isbn, simple, variable, products, product
Requires at least: 4.9
Tested up to: 5.4
Requires PHP: 7.2
Stable tag: __STABLE_TAG__
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin will help you manage WooCommerce Products stocks through locations and also different traditional barcodes.


== Description ==
Stock Locations for WooCommerce will help you manage your products stock across multiple locations easily. If you have multiple physical stores or storage locations, like warehouses, this plugin may help you.

You can print the locations inside a product page on the frontend, with this shortcodes:

* [slw_product_locations show_qty="yes" show_stock_status="no" show_empty_stock="yes"]
* [slw_product_variations_locations show_qty="yes" show_stock_status="no" show_empty_stock="yes"]

SLW also adds standardized barcode fields to WooCommerce products, for you to use.

Barcodes available:

* UPC
* EAN
* ISBN
* ASIN

You can call the barcodes inside a product page on the frontend, with this shortcodes:

* [slw_barcode type="upc"]
* [slw_barcode type="ean"]
* [slw_barcode type="isbn"]
* [slw_barcode type="asin"]

This plugin requires at least *WooCommerce 3.4*.


= Features =

- New taxonomy for stock locations
- Works on both, simple and variable products
- Easy management of stock with multiple locations, both in product and orders
- Standardized barcodes


= Compatibility =

- PHP 7+


== Installation ==

1. Upload "stock-locations-for-woocommerce" to the "/wp-content/plugins/" directory.
2. Check if you have WooCommerce 3.4+ plugin activated
3. Activate SLW plugin through the "Plugins" menu in WordPress.

Simple Products:
1. Assign Stock Locations to the product > Update Post
2. Under Inventory Tab > Activate Manage Stock
3. Under Stock Locations Tab > Manage the stock for the locations

Variable Products:
1. Assign Stock Locations to the product > Update Post
2. Under Inventory Tab > Deativate Manage Stock
3. Under Attributes Tab > Create attributes
4. Under Variations Tab > Create variations based on attributes
5. In each variation > Activate Manage Stock & Add Price > Update Post
6. Under Stock Locations Tab > Manage the stock for the locations for each variation

Barcodes:
1. Under Product > Barcodes Tab > Fill the barcodes


== Frequently Asked Questions ==

= I found a bug, where i can report it? =
I prefer you to use the Github repo, you can find it here [GitHub](https://github.com/alexmigf/stock-locations-for-woocommerce)


== Screenshots ==

1. Product list filter based on stock locations
2. Stock locations taxonomy
3. Manage stock locations in simple products
4. Manage barcodes
5. Manage stock locations in variable products
6. After successful subtraction of stock the locations inputs become inactive
7. Insert the correct amount to subtract from one or more locations per item


== Changelog ==

= 1.1.2 =
- Add variations stock levels to products listing column
- New shortcode 'slw_product_variations_locations'
- Fixes on shortcode 'slw_product_locations'

= 1.1.1 =
- New filter 'slw_shortcode_product_location_name'
- New shortcode 'slw_product_locations'

= 1.1.0 =
- New column in product listing with stock locations imventory
- Fix bug on getting barcodes data for shortcodes
- Fix bug reported by @sebtoombs
- Plugin structure refactoring

= 1.0.3 =
- Tested up to WordPress 5.4
- Fix - Removed condition that prevent other roles beyond the admin from accessing the plugin functionality
- Added Capabilities to the location taxonomy
- Fix - Some actions priorities and arguments number were missing

= 1.0.2 =
- Fix - Bug creating new order, trying to get items on nonexistent order

= 1.0.1 =
- Tested up to WooCommerce 4.0.1
- Fix - Added condition to check variation products in hide meta function
- Fix - Check if order item product exists before trying to hide meta
- Fix - Check if order has items before trying to hide meta

= 1.0.0 =
- Initial release
