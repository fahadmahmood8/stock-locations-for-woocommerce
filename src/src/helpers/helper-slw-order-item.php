<?php
/**
 * SLW Order Item Helper Class
 * @since 1.2.0
 */

namespace SLW\SRC\Helpers;

if ( !defined('WPINC') ) {
	die;
}

if ( !class_exists('SlwOrderItemHelper') ) {

	class SlwOrderItemHelper
	{

		public static function allocateLocationStock( $orderItemId, $locationStockMap, $allocationType )
		{
			// Get line item
			$lineItem = new \WC_Order_Item_Product($orderItemId);

			// Get item product
			$mainProductId = $lineItem->get_variation_id() != 0 ? $lineItem->get_variation_id() : $lineItem->get_product_id();
			$mainProductId = SlwWpmlHelper::object_id( $mainProductId, get_post_type( $mainProductId ) );
			$mainProduct   = wc_get_product( $mainProductId );
			if( empty( $mainProduct ) ) return false;

			// Resolve product ID from parent or self
			$resolvedProductId = ($lineItem->get_variation_id()) ? $lineItem->get_variation_id() : $lineItem->get_product_id();

			// Get item location terms
			$itemStockLocationTerms = SlwStockAllocationHelper::getProductStockLocations($resolvedProductId, false);

			// Nothing to do, we should have gotten this far
			// Checks should have happened prior
			if (empty($itemStockLocationTerms)) {
				return false;
			}

			// Grab all input values
			$totalQtyAllocated = 0;

			// Loop through location terms
			$counter = 0;
			foreach ($itemStockLocationTerms as $term) {
				// No quantity for this location term
				if (!isset($locationStockMap[$term->term_id])) {
					continue;
				}

				// Increment Counter
				$counter++;

				// Get stock data
				$item_stock_location_subtract_input_qty = $locationStockMap[$term->term_id];
				$postmeta_stock_at_term = $term->quantity;

				// Stock input is invalid
				if (empty($item_stock_location_subtract_input_qty) || $item_stock_location_subtract_input_qty == 0) {
					continue;
				}

				// Stock input above needed quantity
				if ($item_stock_location_subtract_input_qty > $lineItem->get_quantity()) {
					continue;
				}

				// Total quantity assignment does not match required quantity
				// Not all stock has been allocated to locations
				if (array_sum($locationStockMap) !== $lineItem->get_quantity()) {
					continue;
				}

				// Save input values to array
				$totalQtyAllocated += $item_stock_location_subtract_input_qty;

				// Update the postmeta of the product
				update_post_meta( $mainProductId, '_stock_at_' . $term->term_id, $postmeta_stock_at_term - $item_stock_location_subtract_input_qty );

				// Add the note
				$lineItem->get_order()->add_order_note(
					sprintf(__('The stock in the location %1$s was updated in -%2$d for the product %3$s', 'stock-locations-for-woocommerce'), $term->name, $item_stock_location_subtract_input_qty, $mainProduct->get_name())
				);

				// Update the itemmeta of the order item
				wc_update_order_item_meta($orderItemId, '_item_stock_locations_updated', 'yes');
				wc_update_order_item_meta($orderItemId, '_item_stock_updated_at_' . $term->term_id, $item_stock_location_subtract_input_qty);

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
			$order_id = wc_get_order_id_by_order_item_id( $orderItemId );
		
			// Manual allocation doesn't need to be restricted to the order stock reduced meta
			if( $allocationType == 'auto' ) {
				$wc_order_stock_reduced = get_post_meta( $order_id, '_order_stock_reduced', true ); // prevents reducing stock twice for the product
			} else {
				$wc_order_stock_reduced = false;
			}

			if( $totalQtyAllocated && $allow_wc_stock_reduce && ! $wc_order_stock_reduced ) {
				// update product WC stock
				if( $mainProduct->get_stock_quantity() >= $totalQtyAllocated ) { // don't allow to decrease below zero
					$stock_qty = $mainProduct->get_stock_quantity() - $totalQtyAllocated;
					// update stock
					update_post_meta( $mainProductId, '_stock', $stock_qty );
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

	}
	
}