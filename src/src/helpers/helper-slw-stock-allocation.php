<?php
/**
 * SLW Stock Allocation Helper Class
 * @since 1.2.0
 */

namespace SLW\SRC\Helpers;

use SLW\SRC\Classes\SlwLocationTaxonomy;
use SLW\SRC\Helpers\SlwWpmlHelper;

if (!defined('WPINC')) {
	die;
}

if( !class_exists('SlwStockAllocationHelper') ) {
	
	class SlwStockAllocationHelper
	{
		/**
		 * Decide how to allocate stock against a product and its locations
		 *
		 * @param $productId
		 * @param $qtyToAllocation
		 * @param $ignoreLocationId
		 *
		 * @return array
		 */

		
		public static function rearrangeByPriority($response=array(), $priority=false, $client_item_stock_location_id=0){
		
			$response_updated = array();
			if(!empty($response)){
				$replace_selected = '';
				$r = 1;
				foreach($response as $term_id=>$term_data){
					
					
					
					if($priority && isset($term_data->slw_location_priority) && !array_key_exists($term_data->slw_location_priority, $response_updated)){
						$response_updated[$term_data->slw_location_priority] = $term_data;
						if($client_item_stock_location_id && $client_item_stock_location_id==$term_id){
							$replace_selected = $term_data->slw_location_priority;
						}
					}else{
						$response_updated[$r] = $term_data;
						if($client_item_stock_location_id && $client_item_stock_location_id==$term_id){
							$replace_selected = $r;
						}
						$r++;
					}
					
				}
				
				if($priority || $client_item_stock_location_id){

					if($replace_selected!='' && array_key_exists($replace_selected, $response_updated)){
						$response_updated[10000] = $response_updated[$replace_selected];
						unset($response_updated[$replace_selected]);
					}
					
					krsort($response_updated);
				}
			}
			
			return $response_updated;
		
		}

		/**
		 * Is product stock managed?
		 *
		 * @param $productId
		 *
		 * @return bool
		 */
		public static function isManagedStock( $productId )
		{
			$product_id = SlwWpmlHelper::object_id( $productId );
			$product    = wc_get_product( $product_id );

			// Not a product
			if (is_null($product) || empty($product)) {
				return false;
			}

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
		public static function getProductStockLocations( $product, $needMetaData = true, $filterByLocation = null )
		{
			//return array();
			
			global $wpdb;
			
			$productId = (is_object($product)?$product->get_id():$product);
			
			
			
			// Get correct top level product
			// The one the stock locations are actually allocated to
			$product_id = SlwWpmlHelper::object_id( $productId );
			$product    = wc_get_product( $product_id );
			if( empty($product) || ! is_callable( array( $product, 'get_id' ) ) ) return array();

			$parentProduct = '';
			if( ! empty($product) && is_callable( array( $product, 'get_parent_id' ) ) ) {
				$parentProduct = wc_get_product( $product->get_parent_id() );
			}
			
			$product_type = (is_object($product)?$product->get_type():'');

			$returnLocations = array();

			// Get locations and stock
			$locations = wp_get_post_terms( ( ( isset($parentProduct) && !empty($parentProduct) ) ? $parentProduct->get_id() : $product->get_id() ), SlwLocationTaxonomy::$tax_singular_name, array('meta_key'=>'slw_location_status', 'meta_value'=>true, 'meta_compare'=>'=') );
		
		

			if( empty($locations) || ! is_array($locations) ) return $returnLocations;
			
			$product_variations_ids = array();
			
			if($product_id){
				switch($product_type){
					case 'variable':
						
						$product_variations_ids = $wpdb->get_results("SELECT ID AS variation_id FROM $wpdb->posts WHERE post_parent IN ($product_id) AND post_type='product_variation'");
					break;	
				}
			}

			foreach ($locations as $idx => $location) {
				// Only return the filter location
				if ($filterByLocation != null && ($filterByLocation != $location->term_id && $filterByLocation != $location->slug)) {
					continue;
				}
				
				
					
						
				if(!empty($product_variations_ids)){
					//pree($product_variations_ids);
					$locations[$idx]->quantity = 0;
					foreach($product_variations_ids as $product_variation){
						$product_variation_id = $product_variation->variation_id;						
					
						if(!is_numeric($product_variation_id)){ continue; }
						
						$variation    = wc_get_product( $product_variation_id );
						
						if ($variation->get_manage_stock() === true) {
							$_stock_at_location = (int)$variation->get_meta('_stock_at_' . $location->term_id, true);
							$_stock_at_location = (is_numeric($_stock_at_location)?$_stock_at_location:0);
							$locations[$idx]->quantity += $_stock_at_location;
						}
		
						if ($needMetaData) {
							$returnLocations[$location->term_id] = (object)array_merge((array)$locations[$idx], self::getLocationMeta($location->term_id));
						} else {
							$returnLocations[$location->term_id] = $locations[$idx];
						}	
					}
				}else{
					
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

				
			}

			// Return a single record
			if ($filterByLocation != null && ($filterByLocation > 0 || strlen($filterByLocation))) {
				return reset($returnLocations);
			}
			
			//pree($returnLocations);exit;
			
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
		public static function getLocationMeta( $locationId )
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
		public static function sortLocationsByPriority( $locations )
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
				$a_priority = isset($a->slw_location_priority) ? $a->slw_location_priority: $a->term_id;
				$b_priority = isset($b->slw_location_priority) ? $b->slw_location_priority: $b->term_id;
				
				if ($a_priority == $b_priority) {
					return 0;
				}

				return ($a_priority < $b_priority) ? -1 : 1;
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
			$meta_query = array(
					'key'		=> 'slw_backorder_location',
					'value'   	=> 1,
					'compare'	=> '='
			);
			$terms = slw_get_locations(SlwLocationTaxonomy::$tax_singular_name, $meta_query);

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
		public static function getProductAvailableStockLocations( $productId, $needMetaData = true )
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
		
		/**
		 * Get product stock location
		 *
		 * @return array
		 */
		public static function get_product_stock_location( $product_id, $location_id )
		{
			$product_stock_locations = self::getProductStockLocations($product_id, true, null);

			$stock_location = array();
			foreach( $product_stock_locations as $id => $location ) {
				if( $id == $location_id ) {
					$stock_location[$id] = $location;
				}
			}

			return $stock_location;
		}
		
		public static function getStockAllocation( $productId, $qtyToAllocation=0, $ignoreLocationId = null, $sortedByPriority=false, $client_item_stock_location_id=0 )
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
			
			$productStockLocations = self::rearrangeByPriority($productStockLocations, $sortedByPriority, $client_item_stock_location_id);

			
			//exit;
			// Remove ignored location from the array
			if( !is_null($ignoreLocationId) && !empty($ignoreLocationId) ) {
				//unset($productStockLocations[$ignoreLocationId]);
				foreach($productStockLocations as $priority_number=>$productStockLocation_data){
					if($productStockLocation_data->term_id==$ignoreLocationId){
						unset($productStockLocations[$priority_number]);
					}
				}
			}
			
			
			// Map stock to locations
			foreach ($productStockLocations as $priority_number => $location) {
				$idx = $location->term_id;
				
				
				if (isset($location->slw_auto_allocate) && $location->slw_auto_allocate) {
					
					// Not enough space
					if ($location->quantity === 0 || $qtyToAllocation==0) {
						continue;
					}

					// Add to allocation response
					
					$response[$priority_number] = $productStockLocations[$priority_number];
					$response[$priority_number]->allocated_quantity = $remainingQty - (max(0, $remainingQty - $location->quantity));

					// Subtract remaining to allocate
					$remainingQty -= $response[$priority_number]->allocated_quantity;
				}
				
				// No need to keep going if nothing to allocate
				if ($remainingQty <= 0) {
					break;
				}
			}

			if(empty($response)){
				$response = $productStockLocations;
			}

			// Allocate remaining quantity to back order location if set
			if ($remainingQty) {
				$backorderLocation = self::getBackOrderLocation();
				
				foreach($productStockLocations as $priority_number=>$productStockLocation_data){
	
					if (
							$backorderLocation !== false 
						&& 
							$productStockLocation_data->term_id==$backorderLocation->term_id
						&& 
							array_key_exists($priority_number, $response)
						&&
							is_object($response[$priority_number])
						&& 
							isset($response[$priority_number]->allocated_quantity)		
					) {
						
						$response[$priority_number]->allocated_quantity += $remainingQty;
						$remainingQty = 0;
					}
					
				}
			}
			

			

			return $response;
		}		

	}

	
}