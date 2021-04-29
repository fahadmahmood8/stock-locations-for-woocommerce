<?php
/**
 * SLW Product Helper Class
 * @since 1.4.5
 */

namespace SLW\SRC\Helpers;

use SLW\SRC\Helpers\SlwWpmlHelper;
use SLW\SRC\Helpers\SlwStockAllocationHelper;

if ( ! defined( 'WPINC' ) ) die;

if ( ! class_exists( 'SlwProductHelper' ) ) {

	class SlwProductHelper
	{

		public static function update_wc_stock_status( $product_id, $stock_qty = null )
		{
			$product_id = SlwWpmlHelper::object_id( $product_id );
			$product    = wc_get_product( $product_id );
			if( empty($product) ) return;

			// check if we are dealing with a variable product
			$variations_stock = 0;
			if( $product->get_type() == 'variable' ) {
				$variation_ids = $product->get_children();
				if( ! empty( $variation_ids ) ) {
					foreach( $variation_ids as $variation_id ) {
						$variation_stock_total = SlwProductHelper::get_product_locations_stock_total( $variation_id );
						$variations_stock     += $variation_stock_total;
						self::update_wc_stock_status( $variation_id, $variation_stock_total );
					}
				}
			}

			// product stock
			if( empty( $stock_qty ) ) {
				$stock_qty = SlwProductHelper::get_product_locations_stock_total( $product_id );
			}

			// sum product stock with variations stock, if any
			if( $variations_stock > 0 ) {
				$stock_qty += $variations_stock;
			}

			// backorder disabled
			if( ! $product->is_on_backorder() ) {
				if( $stock_qty > 0 ) {
					update_post_meta( $product_id, '_stock_status', 'instock' );

					// remove the link in outofstock taxonomy for the current product
					wp_remove_object_terms( $product_id, 'outofstock', 'product_visibility' ); 

				} else {
					update_post_meta( $product_id, '_stock_status', 'outofstock' );

					// add the link in outofstock taxonomy for the current product
					wp_set_post_terms( $product_id, 'outofstock', 'product_visibility', true ); 

				}

			// backorder enabled
			} else {
				$current_stock_status = get_post_meta( $product_id, '_stock_status', true );
				if( $stock_qty > 0 && $current_stock_status != 'instock' ) {
					update_post_meta( $product_id, '_stock_status', 'instock' );

					// remove the link in outofstock taxonomy for the current product
					wp_remove_object_terms( $product_id, 'outofstock', 'product_visibility' ); 
				} else {
					update_post_meta( $product_id, '_stock_status', 'onbackorder' );
				}
			}

			// hook
			do_action( 'slw_product_wc_stock_status', $stock_qty, $product_id );
		}

		public static function get_product_locations_stock_total( $product_id )
		{
			if( empty( $product_id ) ) return;

			$stock_locations = SlwStockAllocationHelper::getProductStockLocations( $product_id );
			if( empty( $stock_locations ) ) return;

			$product_locations_total_stock = 0;
			foreach( $stock_locations as $id => $location ) {
				$product_locations_total_stock += intval( $location->quantity );
			}

			return $product_locations_total_stock;
		}

	}
	
}