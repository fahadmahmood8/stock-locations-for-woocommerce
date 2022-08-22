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
use SLW\SRC\Helpers\SlwProductHelper;

if ( !class_exists('SlwFrontendHelper') ) {

	class SlwFrontendHelper
	{

		public static function get_all_product_stock_locations_for_selection( $product_id, $everything_stock_status_to_instock=false )
		{

			
			$stock_locations = SlwStockAllocationHelper::getProductStockLocations( $product_id );
			
			if( empty($stock_locations) ) return;

			$product = wc_get_product( $product_id );
			if( empty( $product ) ) return;// || $product->get_type() != 'simple'

			$product_price = trim(str_replace(get_woocommerce_currency_symbol(), '', strip_tags(wc_price($product->get_price()))));

			$plugin_settings = get_option( 'slw_settings' );

			// update stock and stock status first to not show wrong data to customers
			$product_locations_total_stock = SlwProductHelper::get_product_locations_stock_total( $product, $stock_locations, $product );
			
			$product_wc_stock = $product->get_stock_quantity();
			

			if( $product_wc_stock != $product_locations_total_stock ) {
				
				
				slw_update_product_stock_status( $product_id, $product_locations_total_stock );
				
				SlwProductHelper::update_wc_stock_status( $product_id );
				slw_notices(__('Stock value updated. Please refresh this page.', 'stock-locations-for-woocommerce'), true);
				
				// refresh page
				//echo("<meta http-equiv='refresh' content='1'>");
			}
			$_backorders = get_post_meta($product_id, '_backorders', true);
			$_backorder_status = ($_backorders!='no');
				
			$stock_locations_to_display = array();
			foreach( $stock_locations as $id => $location ) {
				
				$slw_backorder_location = (property_exists($location, 'slw_backorder_location')?$location->slw_backorder_location:false);
				
				if($location->quantity<=0 && !$slw_backorder_location){ continue; }
				
				$stock_locations_to_display[$id]['backorder_allowed'] = ($_backorder_status?'yes':'no');
				$stock_locations_to_display[$id]['term_id']         = $location->term_id;
				$stock_locations_to_display[$id]['quantity']        = slw_quantity_format($location->quantity);
				$stock_locations_to_display[$id]['allow_backorder'] = $slw_backorder_location;
				$stock_locations_to_display[$id]['name']            = $location->name;
				
				
				
				if(isset( $plugin_settings['product_stock_price_status'] ) && $plugin_settings['product_stock_price_status'] == 'on'){
			
					$_stock_location_price = '_stock_location_price_'.$id;
					$product_stock_price = get_post_meta( $product_id, $_stock_location_price, true );
					$product_stock_price = (float)$product_stock_price;
					
					$stock_locations_to_display[$id]['price']            = ($product_stock_price);
					
					$product_stock_price = $product_price;
					
					$stock_locations_to_display[$id]['price'] = ($stock_locations_to_display[$id]['price']>0?$stock_locations_to_display[$id]['price']:$product_stock_price);
					
				}else{
					$product_stock_price = $product_price;
					$stock_locations_to_display[$id]['price']            = ($product_stock_price);
				}



				if( $location->quantity <= 0 && !$everything_stock_status_to_instock) {
					if( $stock_locations_to_display[$id]['allow_backorder'] == 1 ) {
						$stock_locations_to_display[$id]['name'] .= ' (' . __('On backorder', 'stock-locations-for-woocommerce') . ')';
					} else {
						$stock_locations_to_display[$id]['name'] .= ' (' . __('Out of stock', 'stock-locations-for-woocommerce') . ')';
					}
				} else {
					
					if( isset($plugin_settings['product_location_selection_show_stock_qty']) && $plugin_settings['product_location_selection_show_stock_qty'] == 'on' ) {
						$stock_locations_to_display[$id]['name'] .= sprintf(
							' (%s %s)',
							$location->quantity,
							__( 'In stock', 'stock-locations-for-woocommerce' )
						);
					}
				}
			}
			
			
			return $stock_locations_to_display;
		}

	}
	
}