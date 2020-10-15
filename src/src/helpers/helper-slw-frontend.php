<?php
/**
 * SLW Frontend Helper Class
 * @since 1.3.1
 */

namespace SLW\SRC\Helpers;

if ( !defined('WPINC') ) {
    die;
}

use SLW\SRC\Helpers\SlwStockAllocationHelper;

if ( !class_exists('SlwFrontendHelper') ) {

    class SlwFrontendHelper
    {

        public static function get_all_product_stock_locations_for_selection( $product_id )
		{
			$stock_locations = SlwStockAllocationHelper::getProductStockLocations( $product_id );
			if( empty($stock_locations) ) return;

			$stock_locations_to_display = array();
			foreach( $stock_locations as $id => $location ) {
				$stock_locations_to_display[$id]['term_id'] = $location->term_id;
				$stock_locations_to_display[$id]['quantity'] = $location->quantity;
				$stock_locations_to_display[$id]['allow_backorder'] = $location->slw_backorder_location;
				$stock_locations_to_display[$id]['name'] = $location->name;
				if( $location->quantity < 1 ) {
					$stock_locations_to_display[$id]['name'] .= ' (' . __('Out of stock', 'stock-locations-for-woocommerce') . ')';
				}
			}

			return $stock_locations_to_display;
		}

	}
	
}