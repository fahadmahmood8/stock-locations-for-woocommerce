<?php
/**
 * SLW Order Item Helper Class
 * @since 1.2.0
 */

namespace SLW\SRC\Helpers;

use SLW\SRC\Classes\SlwLocationTaxonomy;
use SLW\SRC\Helpers\SlwWpmlHelper;
use SLW\SRC\Helpers\SlwStockAllocationHelper;
use SLW\SRC\Helpers\SlwMailHelper;
use SLW\SRC\Helpers\SlwProductHelper;

if ( !defined('WPINC') ) {
	die;
}

if ( !class_exists('SlwOrderItemHelper') ) {

	class SlwOrderItemHelper
	{

		public static function allocateLocationStock( $orderItemId, $locationStockMap, $allocationType )
		{
			global $slw_proceed_order_note;
			
			$order_id = wc_get_order_id_by_order_item_id( $orderItemId );
			$order_obj = wc_get_order($order_id);
			//wc_slw_logger('debug', 'allocateLocationStock: '.'Yes #'.$order_id);
			
			
			//wc_slw_logger('debug', $_slw_locations_stock_status);
			// Get line item
			$lineItem = new \WC_Order_Item_Product($orderItemId);

			// Get item product
			$mainProductId = $lineItem->get_variation_id() != 0 ? $lineItem->get_variation_id() : $lineItem->get_product_id();
			$mainProductId = SlwWpmlHelper::object_id( $mainProductId );
			//pree($mainProductId);
			$mainProduct   = wc_get_product( $mainProductId );
			
			if( empty( $mainProduct ) ) return false;
			


			// Get item location terms
			$itemStockLocationTerms = SlwStockAllocationHelper::getProductStockLocations( $mainProductId, false );

			// Nothing to do, we should have gotten this far
			// Checks should have happened prior
			
			if (empty($itemStockLocationTerms)) {
				return false;
			}
			//wc_slw_logger('debug', 'allocateLocationStock: '.'Yes 54');
			// Grab all input values
			$totalQtyAllocated = 0;
			

			//wc_slw_logger('debug', '$itemStockLocationTerms');
			//wc_slw_logger('debug', $itemStockLocationTerms);

			//wc_slw_logger('debug', '$locationStockMap');
			//wc_slw_logger('debug', $locationStockMap);		

			// Loop through location terms
			$counter = 0;
			foreach ($itemStockLocationTerms as $term) {
				
				//wc_slw_logger('debug', 'allocateLocationStock: '.'Yes 65');
				// No quantity for this location term
				if (!isset($locationStockMap[$term->term_id])) {
					continue;
				}
				
				$auto_order_allocate = get_term_meta($term->term_id, 'slw_auto_allocate', true);
				
				$allocation_proceed = true;
				
				switch($allocationType){
					case 'auto':
						$allocationType = ($auto_order_allocate==1?'auto':'manual');
						$allocation_proceed = ($allocationType=='auto');
					break;					
				}
				
				//wc_slw_logger('debug', '$auto_order_allocate: '.$auto_order_allocate.', $allocationType: '.$allocationType.', $term->term_id: '.$term->term_id.', $allocation_proceed: '.($allocation_proceed?'Y':'N').' = '.$allocation_proceed);

				// Increment Counter
				$counter++;
				
				
				
				if( !$allocation_proceed ) { continue; } //23/05/2024
				
				//wc_slw_logger('debug', 'PASSED #1');

				// Get stock data
				$item_stock_location_subtract_input_qty = $locationStockMap[$term->term_id];
				if(is_object($item_stock_location_subtract_input_qty) && isset($item_stock_location_subtract_input_qty->quantity)){
					$item_stock_location_subtract_input_qty = $item_stock_location_subtract_input_qty->quantity;
				}

				$postmeta_stock_at_term = $term->quantity;
	
				//wc_slw_logger('debug', 'allocateLocationStock: '.'Yes 84');
				// Stock input is invalid
				if (empty($item_stock_location_subtract_input_qty) || $item_stock_location_subtract_input_qty == 0) {
					continue;
				}
				
				//wc_slw_logger('debug', 'PASSED #2');


				//wc_slw_logger('debug', 'allocateLocationStock: '.'Yes 90');
				// Stock input above needed quantity
				if ($item_stock_location_subtract_input_qty > $lineItem->get_quantity()) {
					continue;
				}

				//wc_slw_logger('debug', 'PASSED #3');
				
				//wc_slw_logger('debug', array_sum($locationStockMap).' !== '.$lineItem->get_quantity());
				
				
				//wc_slw_logger('debug', 'allocateLocationStock: '.'Yes 97');
				// Total quantity assignment does not match required quantity
				// Not all stock has been allocated to locations
				if (array_sum($locationStockMap) !== $lineItem->get_quantity()) {
					continue;
				}
				
				//wc_slw_logger('debug', 'PASSED #4');
				// Save input values to array
				$totalQtyAllocated += $item_stock_location_subtract_input_qty;

				// Update the postmeta of the product
				update_post_meta( $mainProductId, '_stock_at_' . $term->term_id, $postmeta_stock_at_term - $item_stock_location_subtract_input_qty );
				
				

				// Add the note
				if($slw_proceed_order_note && is_object($order_obj) && method_exists($order_obj, 'add_order_note')){
					$order_obj->add_order_note(
						sprintf(__('The stock in the location %1$s was updated in -%2$d for the product %3$s', 'stock-locations-for-woocommerce'), $term->name, $item_stock_location_subtract_input_qty, $mainProduct->get_name())
					);
				}
				
				
				
				//wc_slw_logger('debug', 'allocateLocationStock: '.'Yes #'.$order_id.' for ID: '.$orderItemId);
				// Update the itemmeta of the order item
				
				//wc_slw_logger('debug', '_item_stock_locations_updated > $orderItemId: '.$orderItemId);
				
				wc_update_order_item_meta($orderItemId, '_item_stock_locations_updated', 'yes');
				wc_update_order_item_meta($orderItemId, '_item_stock_updated_at_' . $term->term_id, $item_stock_location_subtract_input_qty);
				//pree($_slw_locations_stock_status);exit;

				// Save itemmeta _slw_data
				$current_slw_data = wc_get_order_item_meta( $orderItemId, '_slw_data', true );
				$new_data = array(
					$term->term_id => array(
						'location_name' 		=> $term->name,
						'quantity_subtracted'	=> $item_stock_location_subtract_input_qty
					)
				);
				if( !empty($current_slw_data) ) {
					$data = $current_slw_data + $new_data;
				} else {
					$data = $new_data;
				}

				wc_update_order_item_meta( $orderItemId, '_slw_data', $data );

				// Send email notification to location if enabled and if match conditions (see helper method)
				SlwMailHelper::stock_allocation_notification( $term, $lineItem, $item_stock_location_subtract_input_qty );
			}

			// Allow third party plugins to prevent WC stock reduction
			$allow_wc_stock_reduce = apply_filters( 'slw_allow_wc_stock_reduce', true );
			
			// Decrease woocommerce product stock level
			
			
			
			
			
			
			// Manual allocation doesn't need to be restricted to the order stock reduced meta
			if( $allocationType == 'auto' ) {				
				$wc_order_stock_reduced = wc_slw_order_get_post_meta( $order_id, '_slw_order_stock_reduced' );
				$wc_order_stock_reduced = (!is_bool($wc_order_stock_reduced)?wc_string_to_bool($wc_order_stock_reduced):$wc_order_stock_reduced);
			} else {
				$wc_order_stock_reduced = false;
			}

			if( $totalQtyAllocated && $allow_wc_stock_reduce && ! $wc_order_stock_reduced ) {
				// update product WC stock
				if( $mainProduct->get_stock_quantity() >= $totalQtyAllocated ) { // don't allow to decrease below zero
					$stock_qty = $mainProduct->get_stock_quantity() - $totalQtyAllocated;
					// update stock
					
					slw_update_product_stock_status( $mainProductId, $stock_qty );
					
					// update stock status
					SlwProductHelper::update_wc_stock_status( $mainProductId, $stock_qty );

					// allow other functions to deduct WC stock on the main variation product
					if( $mainProduct->get_type() == 'variation' ) {
						do_action( 'slw_allow_variation_product_stock_reduce', $mainProduct->get_parent_id() );
					}
				}
			}

			// Check if stock in locations are updated for this item
			if ($counter !== sizeof($itemStockLocationTerms)) {
			   return false;
			}

			
			return true;
		}

        public static function productStockLocationsInputsAddPreviousStock($product_stock_location_terms, $item)
        {
            // Additional locations to add on the fly
            $additionalLocations = array();

            // Get all locations
            $locations = SlwLocationTaxonomy::getLocations();

            // Find other locations which have been allocated previously but that location is no longer part of this product
            foreach ($locations as $location) {
                // Make sure we dont include already existing stock locations (duplicates)
                if (!isset($product_stock_location_terms[$location->term_id])) {
                    // Check if there is meta against the item
                    $hiddenLocationStock = $item->get_meta('_item_stock_updated_at_' . $location->term_id);

                    // Make sure we found meta and it is not empty
                    // This means this item has previously had stock allocated to a location, which is no longer part of this item,
                    // but for historic reasons we want to see how the stock was allocated at the time of the order.
                    if ($hiddenLocationStock !== false && !empty($hiddenLocationStock)) {
                        $additionalLocations[$location->term_id] = $location;
                    }
                }
            }

            return array_merge($product_stock_location_terms, $additionalLocations);
        }

	}
	
}