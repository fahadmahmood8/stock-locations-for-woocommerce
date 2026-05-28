=== Stock Locations for WooCommerce ===
Contributors: fahadmahmood,alexmigf,invoicepress
Tags: woocommerce, stock, stock locations, simple product, variable products
Requires at least: 4.9
Tested up to: 7.0
Requires PHP: 7.2
Stable tag: __STABLE_TAG__
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin will help you to manage WooCommerce Products stocks through locations.


== Description ==
Stock Locations for WooCommerce will help you manage your products stock across multiple locations easily. If you have multiple physical stores or storage locations, like warehouses, this plugin may help you.

You can print the locations inside a product page on the frontend, with this shortcodes:

= Product pages =

`[slw_product_locations show_qty="yes" show_stock_status="no" show_empty_stock="yes" collapsed="no" stock_location_status="enabled"]`
`[slw_product_variations_locations show_qty="yes" show_stock_status="no" show_empty_stock="yes" collapsed="yes" stock_location_status="all|disabled|enabled"]`
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
- Compatible with WPML


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

= Problems with wp all import? = 

**&#128073; 1.  How can we make them activate automatically during the import process?**

If you want to make them active, in Import settings check "Taxonomies (incl. Categories and Tags)",
under this chose "Update only these taxonomies, leave the rest alone" and type "location"

Use this with "custom fields: _stock_at_xxx" as warehouse number and import works perfectly.

= Stock Locations | Stock Deductions | Settings and Results =

**&#128073; 1. How auto order allocation work with multiple stock locations?**
[youtube http://www.youtube.com/watch?v=0UbAPmZ2Kco]

**&#128073; 2. How location priority work with auto order stock allocation?**
[youtube http://www.youtube.com/watch?v=9kGVJZNNxRk]

**&#128073; 3. What if all of my locations don't have the sufficient stock ordered?**
If order quantity is within the summation of all stock locations available stock quantity, it will be simply served. If not, auto allocation will not work so admin can decide if he want to deliver the order partially or wait for stock comes in.
[youtube http://www.youtube.com/watch?v=4NXYr24OKFg]

**&#128073; 4. How location email works?**
[youtube http://www.youtube.com/watch?v=zdCdckXEbNw]

**&#128073; 5. Do you want to sell products from only one stock location?**
[youtube http://www.youtube.com/watch?v=rznc0WMbmh4]
[youtube http://www.youtube.com/watch?v=7ZIv_d7prLA]

**&#128073; 6. How to make location selection required on cart page?**
[youtube http://www.youtube.com/watch?v=64N7-b90r3E]

**&#128073; 7. How does it manage the maximum qty. to order according to the stock in a location?**
[youtube http://www.youtube.com/watch?v=gmU3cnk0LjY]

**&#128073; 8. How to allow editing location values on orders after being reduced?**
https://github.com/fahadmahmood8/stock-locations-for-woocommerce/issues/90
[youtube http://www.youtube.com/watch?v=Q1Lq-cbv2hE]

= How Import/Export work with CSV files? =
[youtube http://www.youtube.com/watch?v=4KCexCuVetk]

= How to use Cron Job? =
[youtube http://www.youtube.com/watch?v=si_DUe-8ncY&t=114s]

= How to use REST API? =
[youtube http://www.youtube.com/watch?v=si_DUe-8ncY]


= How Google Map and Location Archives work in Premium Version? =
[youtube http://www.youtube.com/watch?v=ZgmNWuKFyQI]

= How to lock pre-selected location on frontend? =

Turn ON "Enable default location in frontend selection" from WooCommerce > Stock Locations for WC. This option will allow you to select default location for a product on Product Edit page. Now turn ON "Lock frontend location to default" and it will lock frontend location which is selected as default.

= I found a bug, where I can report it? =
I prefer you to use the Github issues. You can submit a new one here [GitHub](https://github.com/fahadmahmood8/stock-locations-for-woocommerce/issues/new)

= How to show location stock quantity with a postfix e.g. 20+? =
On settings page you can define a number. If location stock value will be less than the given number, it will display the stock value else will not show the exact number but a plus sign "+" just after the maximum number given.

[youtube http://www.youtube.com/watch?v=nWj5MTLcPjI]




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
11. Show location stock quantity with a postfix e.g. 20+
12. Edit Stock locations taxonomy
13. Lock default location on frontend.
14. Set price for product as per each stock location.
15. Pre-select default location on product page (frontend).
16. Stock locations for each variation.
17. How to allow editing location values on orders after being reduced?


== Changelog ==
= 3.1.7 = 
* Fix: Variable products showing out of stock even when inventory exists - resolved stock status handling for variations without manual location stock entry [Thanks to Ben Croft][28/05/2026]

= 3.1.4 =
* Fix: Bug with variation stock status querying (PR #171). Variations were getting their is_in_stock queried, and reporting incorrectly they were out of stock. [Thanks to kennydude / Joe Simpson][30/04/2026]

= 3.1.1 =
* Fix: Corrected stock status calculation when all locations have zero stock to prevent false "in stock" state. [Thanks to @armanuniverse][06/04/2026]
* Fix: Ensured proper handling and saving of _stock_status (instock/outofstock) in database.
* Improvement: Replaced unreliable stock status checks with aggregated per-location stock calculation.
* Improvement: Standardized return values to boolean for accurate WooCommerce compatibility.
* Props: Thanks to armanuniverse for identifying and sharing the fix.

= 3.1.0 =
* Resolved 'get_variation_default_attribute' fatal error and out-of-stock display issue caused by Stock Locations plugin update, ensuring variable and simple products now show correct stock status. [Thanks to Renzo Westenbroek | Webreturn][12/03/2026]

= 3.0.9 =
* Fixed: Including API response and optimized approach to DB writes, backorder related status and a few more improvements suggested in support threads since October 2025. [07/03/2026]
= 3.0.8 =
* Fixed: Compatibility issue with Woodmart theme where early returns in slw_wp_head() prevented closing of <style> tag, causing invalid HTML and breaking mobile viewport (meta viewport ignored, pages rendering in desktop mode on mobile). Refactored dynamic CSS output to collect rules first and only inject <style> block when content exists; bumped hook priority to 20 for safer execution after theme styles. [Thanks to Spikee] [22/02/2026]

= 3.0.7 =
* Added: JSON payload-based API for bulk updating stock and price per location for products and variations. [Thanks to Mark Boorman][12/01/2026]

= 3.0.6 =
* Added: WooCommerce variation availability now respects location stock for swatch-based themes like Woodmart. [Thanks to Renzo Westenbroek][05/01/2026]

= 3.0.4 =
* Tested: WooCommerce variation availability now respects location stock for swatch-based themes like Woodmart. [Thanks to Renzo Westenbroek][05/01/2026]

= 3.0.2 =
* Fixed: Only enabled/active stock locations will contribute in total stock value but it would still be editable. [Thanks to @josephkallinit][21/10/2025]
* Fixed: Prevented potential PHP error when `$slw_api_valid_keys` is null during API validation. [Thanks to Tushar Tajane][21/10/2025]
* New: Order notes can be turned off from the settings. [Thanks to Rob Wood][21/10/2025]
* Fixed: Incorrect price range display for variable products when location-based stock prices are higher than variation base prices. The location price now overrides confusing WooCommerce default range formatting.
* Improved: Sale price logic now fully respects location-based pricing — ensuring both range and sale indicators are hidden when local price is higher. [21/10/2025]

= 3.0.1 =
* Added: Conditional CSS injection for WooCommerce Blocks to hide sale and del elements when location price exceeds the base or sale price.
* Improved: Price hiding logic refactored for better compatibility across all product types (simple, variable, grouped). [20/10/2025]

== Upgrade Notice ==
= 3.1.7 =
This release fixes a critical issue where variable products were displaying as out of stock despite having available inventory. After updating, variable products will correctly reflect stock status without requiring manual stock entry for each variation in the location tab. Simple products already working with the "Everything stock status to instock" setting will continue to function normally. We highly recommend updating to restore proper stock display for your variable products.