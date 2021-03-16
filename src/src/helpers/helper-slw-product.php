<?php
/**
 * SLW Frontend Product Class
 * @since 1.4.5
 */

namespace SLW\SRC\Helpers;

if ( ! defined( 'WPINC' ) ) die;

if ( ! class_exists( 'SlwProductHelper' ) ) {

	class SlwProductHelper
	{

		public static function update_wc_stock_status( $product_id, $stock_qty = null )
		{
			$product = wc_get_product( $product_id );
			if( empty($product) ) return;

			if( is_null( $stock_qty ) ) {
				$stock_qty = $product->get_stock_quantity();
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
		}

	}
	
}