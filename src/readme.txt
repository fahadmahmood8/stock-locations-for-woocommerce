=== Stock Locations for WooCommerce ===
Contributors: alexmigf
Tags: woocommerce, stock, stock locations, simple, variable, products, product
Requires at least: 4.9
Tested up to: 5.6
Requires PHP: 7.2
Stable tag: __STABLE_TAG__
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin will help you manage WooCommerce Products stocks through locations.


== Description ==
Stock Locations for WooCommerce will help you manage your products stock across multiple locations easily. If you have multiple physical stores or storage locations, like warehouses, this plugin may help you.

You can print the locations inside a product page on the frontend, with this shortcodes:

= Product pages =

`[slw_product_locations show_qty="yes" show_stock_status="no" show_empty_stock="yes"]`
`[slw_product_variations_locations show_qty="yes" show_stock_status="no" show_empty_stock="yes"]`
`[slw_product_message is_available="yes" only_location_available="no" location="location-slug"]Your custom product message/HTML here[/slw_product_message]`

= Cart page =

`[slw_cart_message qty_from_location="location-slug" only_location_available="no"]Your custom cart message/HTML here[/slw_cart_message]`

= REST API =

REST API endpoints (both accept `GET` and `PUT` requests):

`/wp-json/wc/v3/products/id`
`/wp-json/wc/v3/products/id/variations/id (first ID is for parent product, the second one for the variation ID)`
`/wp-json/wp/v2/location/`
`/wp-json/wp/v2/location/id`


This plugin requires at least *WooCommerce 3.4*.


= Features =

- New taxonomy for stock locations
- Works on both, simple and variable products
- Easy management of stock with multiple locations, both in product and orders
- Get and update product stock locations from the REST API
- Allow customers to select locations when purchasing
- Auto order allocation for locations stock reduction
- Send email notifications when stock is allocated for a product in a location
- Send WooCommerce New Order email copy to item location


= Compatibility =

- PHP 7.2+


== Installation ==

1. Upload "stock-locations-for-woocommerce" to the "/wp-content/plugins/" directory.
2. Check if you have WooCommerce 3.4+ plugin activated
3. Activate SLW plugin through the "Plugins" menu in WordPress.

**Simple Products**

1. Assign Stock Locations to the product > Update Post
2. Under Inventory Tab > Activate Manage Stock
3. Under Stock Locations Tab > Manage the stock for the locations

**Variable Products**

1. Assign Stock Locations to the product > Update Post
2. Under Inventory Tab > Deativate Manage Stock
3. Under Attributes Tab > Create attributes
4. Under Variations Tab > Create variations based on attributes
5. In each variation > Activate Manage Stock & Add Price > Update Post
6. Under Stock Locations Tab > Manage the stock for the locations for each variation


== Frequently Asked Questions ==

= I found a bug, where i can report it? =
I prefer you to use the Github issues. You can submit a new one here [GitHub](https://github.com/alexmigf/stock-locations-for-woocommerce/issues/new)


== Screenshots ==

1. Product list filter and stock locations column
2. Stock locations taxonomy
3. Manage stock locations in simple products
4. Manage stock locations in variable products
5. Deduct stock from locations manually
6. Deduct stock from location automatically
7. Allow customers to select locations in cart page
8. Allow customers to select location in variable products
9. Allow customers to select location in simple products
10. Plugin settings


== Changelog ==

= 1.3.2 =
- Fix: jQuery request returning 404 because of missing hook

= 1.3.1 =
- New: include 'Out of stock' and 'On backorder' locations in frontend selections
- New: filter 'slw_allow_wc_stock_reduce' to allow third party plugins to prevent WC stock reduction
- Fix: jQuery error for variants without locations
- Fix: jQuery bug on clearing location selection on variation in product page
- Fix: jQuery error when frontend settings not enabled
- Fix: meta '_slw_data' not beeing saved correctly

= 1.3.0 =
- New: filter 'slw_stock_allocation_notification_message' to customize the email notification message
- New: send email copy of New Order WC email to location registered address
- New: send email notification when stock is allocated for a product in some location
- New: show location taxonomy in REST API
- New: setting to enable/disable the barcodes tab
- Fix: stock allocation when customer selects or not a location
- Fix: bug preventing stock deduction when using in conjugation with Point of Sale for WooCommerce plugin

= 1.2.4 =
- New: setting to include location data in formatted item meta
- Fix: several bugs showing warnings

= 1.2.3 =
- New: setting to auto delete unused product stock locations meta
- New: filter 'slw_shortcode_product_location_stock' for shortcodes 'slw_product_locations' and 'slw_product_variations_locations'
- New: CSS classes for shortcodes 'slw_product_locations' and 'slw_product_variations_locations'
- Fix: 'outofstock' taxonomy on product save/update
- Fix: setting 'Different location per cart item', values were exchanged

= 1.2.2 =
- Fix: helper 'view' function call, preventing colision with other plugins with the same function name

= 1.2.1 =
- New: plugin setting to lock selected location in cart for every cart item
- Fix: check if location stock is enough for the cart item, if not hide from select option
- Fix: if auto allocation exist for the location disable WC hold stock

= 1.2.0 =
- New: stock locations select in cart page
- New: settings page
- New: auto order allocation for stock
- New: shortcode 'slw_cart_message' to display a custom message in the cart page
- New: shortcode 'slw_product_message' to displaying a custom message on product page
- New: options for stock locations: Default for New Products, Backorder Location, Auto Order Allocate and Stock Location Priority
- Fix: several bugs and PHP legacy compatibilities

= 1.1.5 =
- Fixes bug on shortcodes method

= 1.1.4 =
- Improved stock locations levels products listing
- Locations added to REST API products and variations endpoints for GET and PUT requests, thanks to @shanerutter

= 1.1.3 =
- Remove flush rewrite rules, should be avoided for now

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
