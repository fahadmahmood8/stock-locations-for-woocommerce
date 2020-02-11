=== Stock Locations for WooCommerce ===
Contributors: alexmigf
Tags: woocommerce, stock, stock locations, barcode, barcodes, ean, upc, asin, isbn, simple, variable, products, product
Requires at least: 4.9
Tested up to: 5.3.2
Requires PHP: 7.2
Stable tag: trunk
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin will help you manage WooCommerce Products stocks throw locations and also different traditional barcodes.


== Description ==
Stock Locations for WooCommerce will help you manage your products stock across multiple locations easily. If you have multiple physical stores or storage locations, like warehouses, this plugin may help you.

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

= Can i translate this plugin? =
Of course. If you wish you can send me your translation files (.mo and .po) for another language, and i will make them available in a future release.

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

= 1.0.0 =
- Initial release
