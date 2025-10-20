<?php
/**
 * SLW Product Helper Class
 * @since 1.4.5
 */

namespace SLW\SRC\Helpers;

use SLW\SRC\Helpers\SlwWpmlHelper;
use SLW\SRC\Helpers\SlwStockAllocationHelper;

if ( ! defined( 'WPINC' ) ) die;



if ( ! function_exists( __NAMESPACE__ . '\slw_product_wc_stock_status_callback' ) ) {
function slw_product_wc_stock_status_callback( $locations_stock, $id, $force_main_product_stock_status_to_instock = false ) {
	
	static $processing = [];
	
	if ( isset($processing[$id]) && $processing[$id] ) {
		return;
	}
	
	$processing[$id] = true;
	
	global $slw_plugin_settings;
	if ( ! is_array( $slw_plugin_settings ) ) {
		$slw_plugin_settings = [];
	}

	$force_main_product_stock_status_to_instock = (!$force_main_product_stock_status_to_instock?array_key_exists('force_main_product_stock_status_to_instock', $slw_plugin_settings):$force_main_product_stock_status_to_instock);

	if(is_numeric($id)){
		//pree($id);
		$product = wc_get_product( $id );
	}
	
	if(is_object($id)){
		$product = $id;
		$id = $product->get_id();
	}
	
	if( ! empty( $id ) && $force_main_product_stock_status_to_instock) {
		
		if( empty( $product ) ) return;
	
		$parent_id = $product->get_parent_id();
		
		
		if( $parent_id == 0 ) {
			
			
			
			if(!$locations_stock){
				$locations_stock = \SLW\SRC\Helpers\SlwProductHelper::get_product_locations_stock_total( $id );
			}

			$_backorders = get_post_meta($id, '_backorders', true);
			$_backorder_status = ($_backorders!='no');
			
			if($_backorder_status){
				update_post_meta( $id, '_stock_status', 'onbackorder' );
			}else{
				if( $locations_stock > 0 ) {
					update_post_meta( $id, '_stock_status', 'instock' );
				}elseif( $locations_stock <= 0 ) {
					
					update_post_meta( $id, '_stock_status', 'outofstock' );
					
				}
			}
			
			// Temporarily remove this callback while we call the updater to avoid recursion
		remove_action( 'slw_product_wc_stock_status', __NAMESPACE__ . '\slw_product_wc_stock_status_callback', 10 );
		
		// call the function that actually updates the main product stock status
		\slw_update_product_stock_status( $id, $locations_stock );
		
		// re-add the callback
		add_action( 'slw_product_wc_stock_status', __NAMESPACE__ . '\slw_product_wc_stock_status_callback', 10, 3 );



		}
	}
	
	unset( $processing[$id] );
	
}
}
add_action( 'slw_product_wc_stock_status', __NAMESPACE__ . '\slw_product_wc_stock_status_callback', 10, 3 );


if ( ! class_exists( 'SlwProductHelper' ) ) {

	class SlwProductHelper
	{

		public static function update_wc_stock_status( $product_id, $stock_qty = null, $force_main_product_update=false )
		{
			global $wpdb;
			
			if(is_numeric($product_id)){
				$product_id = SlwWpmlHelper::object_id( $product_id );
				//pree($product_id);
				$product    = wc_get_product( $product_id );
			}
			if(is_object($product_id)){
				$product    = $product_id;
				$product_id = $product->get_id();
			}
			if( empty($product) ) return;

			// check if we are dealing with a variable product
			$variations_stock = 0;
			if($product_id && $product->get_type() == 'variable' ) {
				//$variation_ids = $product->get_children();
				$product_variations_ids = $wpdb->get_results("SELECT ID AS variation_id FROM $wpdb->posts WHERE post_parent IN ($product_id) AND post_type='product_variation'");
				
				if( ! empty( $product_variations_ids ) ) {
					foreach( $product_variations_ids as $variation_obj ) {
						$variation_id = $variation_obj->variation_id;
						$variation_stock_total = SlwProductHelper::get_product_locations_stock_total( $variation_id );
						$variations_stock     += $variation_stock_total;
						self::update_wc_stock_status( $variation_id, $variation_stock_total );
					}
				}
			}elseif( $product->get_type() == 'simple' ) {
				
			}
			
			// product stock
			if ( $stock_qty === null ) {
				$stock_qty = SlwProductHelper::get_product_locations_stock_total( $product_id );
			}
			
			// sum product stock with variations stock, if any
			if( $variations_stock > 0 ) {
				$stock_qty += $variations_stock;
			}
			
			// backorder disabled
			if(!$product->backorders_allowed()){//( ! $product->is_on_backorder() ) { //20/01/2022 // https://github.com/fahadmahmood8/stock-locations-for-woocommerce/issues/121
				if( $stock_qty > 0 ) {
					update_post_meta( $product_id, '_stock_status', 'instock' );
					
					SlwProductHelper::call_wc_product_stock_status_action( $product_id, 'instock' );
					// remove the link in outofstock taxonomy for the current product
					wp_remove_object_terms( $product_id, 'outofstock', 'product_visibility' ); 

				} else {
					update_post_meta( $product_id, '_stock_status', 'outofstock' );
					SlwProductHelper::call_wc_product_stock_status_action( $product_id, 'outofstock' );
					// add the link in outofstock taxonomy for the current product
					wp_set_post_terms( $product_id, 'outofstock', 'product_visibility', true ); 

				}

			// backorder enabled
			} else {
				
				$current_stock_status = get_post_meta( $product_id, '_stock_status', true );

				if( $stock_qty > 0 ) { //&& $current_stock_status != 'instock'
					update_post_meta( $product_id, '_stock_status', 'instock' );
					SlwProductHelper::call_wc_product_stock_status_action( $product_id, 'instock' );
					// remove the link in outofstock taxonomy for the current product
					wp_remove_object_terms( $product_id, 'outofstock', 'product_visibility' ); 
				} else {
					update_post_meta( $product_id, '_stock_status', 'onbackorder' );
					SlwProductHelper::call_wc_product_stock_status_action( $product_id, 'onbackorder' );
				}
			}
			
			// hook
			do_action( 'slw_product_wc_stock_status', $stock_qty, $product_id, $force_main_product_update );
		}
		public static function call_wc_product_stock_status_action( $product_id, $status = '' ){ //20/01/2022 https://github.com/fahadmahmood8/stock-locations-for-woocommerce/pull/120
			if( empty( $product_id ) ) return;
			
			

			// Check if status string is in array
			$approved_status_string = array('instock', 'outofstock', 'onbackorder');
			if ( ! in_array( $status, $approved_status_string ) ) return;
			$product_id = (int) $product_id;
			//pree($product_id);
			$product = wc_get_product( $product_id );
			if ( $product->is_type( 'variation' ) ) {
				do_action( 'woocommerce_variation_set_stock_status', $product_id, $status, $product );
			} else {
				do_action( 'woocommerce_product_set_stock_status', $product_id, $status, $product );
			}
		}
		public static function get_product_locations_stock_total( $product, $stock_locations=array() ){
			
			
			
			$product_id = (is_object($product)?$product->get_id():$product);
			
			if( empty( $product_id ) ) return;

			$stock_locations = (empty($stock_locations)?SlwStockAllocationHelper::getProductStockLocations( $product ):$stock_locations);
			
			if( empty( $stock_locations ) ) return;
			
			

			$product_locations_total_stock = 0; 
			foreach( $stock_locations as $id => $location ){
				$product_locations_total_stock += intval( $location->quantity );
			}

			return $product_locations_total_stock;
		}

	}
	
}
