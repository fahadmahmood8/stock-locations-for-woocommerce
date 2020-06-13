<?php

namespace SLW\SRC\Helpers;

use SLW\SRC\Classes\SlwProductTaxonomy;

if (!defined('WPINC')) {
    die;
}

if(!class_exists('SlwStockAllocationHelper')) {
    class SlwStockAllocationHelper
    {
        /**
         * Decide how to allocate stock against a product and its locations
         *
         * @param $productId
         * @param $qtyToAllocation
         *
         * @return array
         */
        public static function getStockAllocation($productId, $qtyToAllocation)
        {
            $response = array();

            // Not stock managed
            if (!self::isManagedStock($productId)) {
                return $response;
            }

            // Keep track of what there is to allocate
            $remainingQty = $qtyToAllocation;

            // Get products stock locations
            // Sorted by priority
            $productStockLocations = self::sortLocationsByPriority(self::getProductStockLocations($productId));

            // Map stock to locations
            foreach ($productStockLocations as $idx => $location) {
                if (isset($location->slw_auto_allocate) && $location->slw_auto_allocate) {
                    // Not enough space
                    if ($location->quantity === 0) {
                        continue;
                    }

                    // Add to allocation response
                    $response[$location->term_id] = $productStockLocations[$idx];
                    $response[$location->term_id]->allocated_quantity = $remainingQty - (max(0, $remainingQty - $location->quantity));

                    // Subtract remaining to allocate
                    $remainingQty -= $response[$location->term_id]->allocated_quantity;
                }

                // No need to keep going if nothing to allocate
                if ($remainingQty <= 0) {
                    break;
                }
            }

            // Allocate remaining quantity to back order location if set
            if ($remainingQty) {
                $backorderLocation = self::getBackOrderLocation();

                if ($backorderLocation !== false && isset($productStockLocations[$backorderLocation->term_id])) {
                    $response[$backorderLocation->term_id]->allocated_quantity += $remainingQty;
                    $remainingQty = 0;
                }
            }

            return $response;
        }

        /**
         * Is product stock managed?
         *
         * @param $productId
         *
         * @return bool
         */
        public static function isManagedStock($productId)
        {
            $product = wc_get_product($productId);

            // Not managed stock
            if ($product->get_manage_stock() !== true && $product->get_manage_stock() !== 'parent') {
                return false;
            }

            return true;
        }

        /**
         * Return stock locations allocated to product and quantity available in order of priority
         *
         * @param $productId
         *
         * @param bool $needMetaData
         * @param null $filterByLocation
         *
         * @return false|\WP_Error|\WP_Term[]
         */
        public static function getProductStockLocations($productId, $needMetaData = true, $filterByLocation = null)
        {
            // Get correct top level product
            // The one the stock locations are actually allocated to
            $product = wc_get_product($productId);
            $parentProduct = wc_get_product($product->get_parent_id());

            $returnLocations = array();

            // Get locations and stock
            $locations = get_the_terms(((isset($parentProduct) && !empty($parentProduct)) ? $parentProduct->get_id() : $product->get_id()), SlwProductTaxonomy::$tax_singular_name);
            foreach ($locations as $idx => $location) {
                // Only return the filter location
                if ($filterByLocation != null && ($filterByLocation != $location->term_id && $filterByLocation != $location->slug)) {
                    continue;
                }

                if ($product->get_manage_stock() === true) {
                    $locations[$idx]->quantity = $product->get_meta('_stock_at_' . $location->term_id, true);
                } elseif($product->get_manage_stock() === 'parent') {
                    $locations[$idx]->quantity = $parentProduct->get_meta('_stock_at_' . $location->term_id, true);
                } else {
                    $locations[$idx]->quantity = 0;
                }

                if ($needMetaData) {
                    $returnLocations[$location->term_id] = (object)array_merge((array)$locations[$idx], self::getLocationMeta($location->term_id));
                } else {
                    $returnLocations[$location->term_id] = $locations[$idx];
                }
            }

            // Return a single record
            if ($filterByLocation != null && sizeof($filterByLocation)) {
                return reset($returnLocations);
            }

            // Locations
            return $returnLocations;
        }

        /**
         * Get locations meta data
         *
         * @param $locationId
         *
         * @return array|mixed
         */
        public static function getLocationMeta($locationId)
        {
            // Get all terms
            $termMeta = get_term_meta($locationId);

            // Flatten
            $termMeta = array_map(function ($value) {
                return $value[0];
            }, $termMeta);

            return $termMeta;
        }

        /**
         * Sort locations by priority
         *
         * @param $locations
         *
         * @return array
         */
        public static function sortLocationsByPriority($locations)
        {
            // Not an array of empty
            if (!is_array($locations) || !sizeof($locations)) {
                return $locations;
            }

            // Ensure meta data is retrieved
            foreach ($locations as $idx => $location) {
                // Get meta data if not already
                if (!isset($location->slw_location_priority)) {
                    $locations[$idx] = (object)array_merge((array)$location, self::getLocationMeta($location->term_id));
                }
            }

            // Sort
            uasort($locations, function($a, $b)
            {
                if ($a->slw_location_priority == $b->slw_location_priority) {
                    return 0;
                }

                return ($a->slw_location_priority < $b->slw_location_priority) ? -1 : 1;
            });

            return $locations;
        }

        /**
         * Get backorder location
         *
         * @return bool|\WP_Term
         */
        public static function getBackOrderLocation()
        {
            $terms = get_terms(array(
                'taxonomy'      =>  SlwProductTaxonomy::$tax_singular_name,
                'hide_empty'    =>  false,
                'meta_query' => array(array(
                    'key' => 'slw_backorder_location',
                    'value'   => 1,
                    'compare' => '='
                ))
            ));

            return (sizeof($terms)) ? $terms[0] : false;
        }

        /**
         * Return all available stock locations
         *
         * @param $productId
         * @param bool $needMetaData
         *
         * @return array
         */
        public static function getProductAvailableStockLocations($productId, $needMetaData = true)
        {
            $return = array();

            $stockLocations = self::getProductStockLocations($productId, $needMetaData);
            foreach ($stockLocations as $location) {
                if ($location->quantity > 0) {
                    $return[$location->term_id] = $location;
                }
            }

            return $return;
        }

    }
}