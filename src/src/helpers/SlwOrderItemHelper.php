<?php

namespace SLW\SRC\Helpers;


if (!defined('WPINC')) {
    die;
}

if(!class_exists('SlwOrderItemHelper')) {
    class SlwOrderItemHelper
    {

        public static function allocateLocationStock($orderItemId, $locationStockMap)
        {
            // Get line item
            $lineItem = new \WC_Order_Item_Product($orderItemId);

            // Get item product
            $mainProduct = $lineItem->get_product();

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
                update_post_meta($mainProduct->get_id(), '_stock_at_' . $term->term_id, $postmeta_stock_at_term - $item_stock_location_subtract_input_qty);

                // Add the note
                $lineItem->get_order()->add_order_note(
                    sprintf(__('The stock in the location %1$s was updated in -%2$d for the product %3$s', 'stock-locations-for-woocommerce'), $term->name, $item_stock_location_subtract_input_qty, $mainProduct->get_name())
                );

                // Update the itemmeta of the order item
                wc_update_order_item_meta($orderItemId, '_item_stock_locations_updated', 'yes');
                wc_update_order_item_meta($orderItemId, '_item_stock_updated_at_' . $term->term_id, $item_stock_location_subtract_input_qty);
            }

            // Update woocommerce product stock level
            if ($totalQtyAllocated) {
                $mainProduct->set_stock_quantity($mainProduct->get_stock_quantity() - $totalQtyAllocated);
            }

            // Check if stock in locations are updated for this item
            if($counter !== sizeof($itemStockLocationTerms)) {
               return false;
            }

            return true;
        }

    }
}