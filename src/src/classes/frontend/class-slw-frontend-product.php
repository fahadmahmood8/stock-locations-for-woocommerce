<?php
/**
 * SLW Frontend Product Class
 *
 * @since 1.3.0
 */

namespace SLW\SRC\Classes\Frontend;

use SLW\SRC\Helpers\SlwStockAllocationHelper;

if ( !defined( 'WPINC' ) ) {
	die;
}

if( !class_exists('SlwFrontendProduct') ) {

	class SlwFrontendProduct
	{
		/**
		 * Construct.
		 *
		 * @since 1.3.0
		 */
		public function __construct()
		{
			// get settings
			$plugin_settings = get_option( 'slw_settings' );

			// check if show in cart is enabled
			if( isset($plugin_settings['show_in_product_page']) && $plugin_settings['show_in_product_page'] == 'yes' ) {
				add_action( 'woocommerce_before_add_to_cart_button', array($this, 'simple_location_select') );
				add_action( 'woocommerce_single_variation', array($this, 'variable_location_select') );
				add_filter( 'woocommerce_add_cart_item_data', array($this, 'add_to_cart_location_validation'), 10, 3 );
				add_action( 'wp_ajax_get_variation_locations', array($this, 'get_variation_locations') );
			}
		}

		/**
		 * Add stock locations selection to simple product page.
		 *
		 * @since 1.3.0
		 */
		public function simple_location_select()
		{
			global $product;
			if( empty($product) || $product->get_type() != 'simple' ) return;

			if( !empty($stock_locations = SlwStockAllocationHelper::getProductAvailableStockLocations($product->get_id(), true)) ) {
				echo '<select id="slw_item_stock_location_simple_product" class="slw_item_stock_location" name="slw_add_to_cart_item_stock_location" style="display:block;" required>';
				echo '<option disabled selected>'.__('Select location...', 'stock-locations-for-woocommerce').'</option>';
				foreach( $stock_locations as $id => $location ) {
					echo '<option value="'.$location->term_id.'">'.$location->name.'</option>';
				}
				echo '</select>';
			}
		}

		/**
		 * Add stock locations selection to variable product page.
		 *
		 * @since 1.3.0
		 */
		public function variable_location_select()
		{
			global $product;
			if( empty($product) || $product->get_type() != 'variable' ) return;
			
			echo '<select id="slw_item_stock_location_variable_product" class="slw_item_stock_location" name="slw_add_to_cart_item_stock_location">';
			echo '<option disabled selected>'.__('Select location...', 'stock-locations-for-woocommerce').'</option>';
			echo '</select>';
		}

		/**
		 * Get variation locations.
		 *
		 * @since 1.3.0
		 */
		public function get_variation_locations()
		{
			if( $_POST && isset($_POST['action']) && isset($_POST['variation_id']) && $_POST['action'] == 'get_variation_locations' ) {
				$variation_id = sanitize_text_field($_POST['variation_id']);
				if( !empty($stock_locations = SlwStockAllocationHelper::getProductAvailableStockLocations($variation_id, true)) ) {
					wp_send_json_success( compact('stock_locations') );
				} else {
					return;
				}
			}
		}

		/**
		 * Validate cart item selected location.
		 *
		 * @since 1.3.0
		 */
		function add_to_cart_location_validation( $cart_item_data, $product_id, $variation_id ) {
			if( isset( $_POST['slw_add_to_cart_item_stock_location'] ) ) {
				$cart_item_data['stock_location'] = sanitize_text_field( $_POST['slw_add_to_cart_item_stock_location'] );
			}
			return $cart_item_data;
		}

	}

}
