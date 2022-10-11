<?php
/**
 * SLW Shortcodes Class
 *
 * @since 1.0.0
 */

namespace SLW\SRC\Classes;

use SLW\SRC\Helpers\SlwStockAllocationHelper;
use SLW\SRC\Helpers\SlwWpmlHelper;

if ( !defined( 'WPINC' ) ) {
	die;
}

if(!class_exists('SlwShortcodes')) {

	class SlwShortcodes
	{

		/**
		 * Construct.
		 *
		 * @since 1.1.0
		 */
		public function __construct()
		{
			add_shortcode('slw_product_locations', array($this, 'display_product_locations'));
			add_shortcode('slw_product_variations_locations', array($this, 'display_product_variations_locations'));
			add_shortcode('slw_product_message', array($this, 'display_product_message'));
			add_shortcode('slw_cart_message', array($this, 'display_cart_message'));
		}

		/**
		 * Displays the product locations.
		 *
		 * @since 1.1.1
		 * @return string
		 */
		public function display_product_locations( $atts=array() )
		{
			global $woocommerce, $product, $post;

			$values = shortcode_atts(array(
				'show_qty'          => 'yes',
				'show_stock_status' => 'no',
				'show_empty_stock'  => 'yes',
				'separator'  => '-',
				'collapsed'  => 'no',
				'product_id' => get_the_ID(),
				'stock_location_status' => 'enabled'
			), $atts);
			
			if(!is_product()){ 
				if(is_numeric($values['product_id'])){
					$check_post_type = get_post($values['product_id']);
					if(is_object($check_post_type) && $check_post_type->post_type=='product'){
						
					}else{
						return;
					}
				}else{
					return;
				}
				
			}

			if( is_object( $product)){
				$product_obj = $product;
				if($product_obj->is_type('variation')){
					$product_obj = wc_get_product($product_obj->get_parent_id());
				}
			}else{
				
				$product_obj = wc_get_product($values['product_id']);
			}
			
			$output = '';

			$product_id = SlwWpmlHelper::object_id( $product_obj->get_id() );
			$product_obj    = wc_get_product( $product_id );
			
			if( empty($product_obj) ) return;

			// Default values
			
			
			if( !$values ) {
				return;
			}


			if( $product_obj->is_type( 'variable' ) || $product_obj->is_type('variation')) {
				return $this->display_product_variations_locations($values);
			}else{
				if( !empty($product_obj) ) {
					
					$slw_location_status = array('meta_key'=>'slw_location_status', 'meta_value'=>true, 'meta_compare'=>'=');
					switch($values['stock_location_status']){
						default:
						case 'enabled':
							
						break;
						case 'all':
							$slw_location_status = array();
						break;
						case 'disabled':
							$slw_location_status['meta_value'] = false;
						break;						
					}
					$locations = wp_get_post_terms($product_obj->get_id(), SlwLocationTaxonomy::$tax_singular_name, $slw_location_status);
					// Build output
					$output .= '<div class="slw-product-locations">';
					$output .= $this->output_product_locations_for_shortcode($product_obj, $locations, $values);
					$output .= '</div>';
				}
	
				return $output;
			}
			
		}

		/**
		 * Displays the product variation locations.
		 *
		 * @since 1.1.2
		 * @return string
		 */
		public function display_product_variations_locations( $atts=array() )
		{
			global $woocommerce, $product, $post, $wpdb;
			
			$values = shortcode_atts(array(
				'show_qty'          => 'yes',
				'show_stock_status' => 'no',
				'show_empty_stock'  => 'yes',
				'collapsed'  => 'no',
				'separator'  => '-',
				'product_id' => get_the_ID(),
				'stock_location_status' => 'enabled'
			), $atts);
			
			
			
			if(  !is_product() ){ 
				if(is_numeric($values['product_id'])){
					$check_post_type = get_post($values['product_id']);
					if(is_object($check_post_type) && $check_post_type->post_type=='product'){
						
					}else{
						return;
					}
				}else{
					return;
				}
				
			}
			
			
			if( is_object( $product)){
				$product_obj = $product;
				if($product_obj->is_type('variation')){
					$product_obj = wc_get_product($product_obj->get_parent_id());
				}
			}else{
				
				$product_obj = wc_get_product($values['product_id']);
			}

			$product_id = SlwWpmlHelper::object_id( $product_obj->get_id() );
			$product_obj    = wc_get_product( $product_id );
			
			if( empty($product_obj) ) return;

			// Default values
			
			
			if( !$values ) {
				return;
			}

			$output = '';

			if( !empty($product_obj) ) {
				// Check for variations
				$variations_products = array();

				if($product_id && $product_obj->is_type( 'variable' )) {
					//$product_variations_ids = $product_obj->get_children();
					$product_variations_ids = $wpdb->get_results("SELECT ID AS variation_id FROM $wpdb->posts WHERE post_parent IN ($product_id) AND post_type='product_variation'");
					$product_variations = array();
					foreach( $product_variations_ids as $variation_obj ) {
						$variation_id = $variation_obj->variation_id;
						$product_variations[$variation_id] = $product_obj->get_available_variation( $variation_id );
					}
					foreach ($product_variations as $variation) { 
						$variations_products[] = wc_get_product( $variation['variation_id'] );
					}
					
					$slw_location_status = array('meta_key'=>'slw_location_status', 'meta_value'=>true, 'meta_compare'=>'=');
					switch($values['stock_location_status']){
						default:
						case 'enabled':
							
						break;
						case 'all':
							$slw_location_status = array();
						break;
						case 'disabled':
							$slw_location_status['meta_value'] = false;
						break;						
					}
					
					$locations = wp_get_post_terms($product_obj->get_id(), SlwLocationTaxonomy::$tax_singular_name, $slw_location_status);
					
					
					if( !empty($product_variations) ) {
						$variation_attr_arr = array();
						foreach( $product_variations as $variation_id=>$product_variation ) {
							
							$variation_obj = wc_get_product( $variation_id );						
							$attributes = $product_variation['attributes'];
							$attribute = array_map('ucfirst', $attributes);
							$variation_attr = implode('/', $attribute);
							$variation_attr_str = implode('-', $attributes);
							
							$variation_attr_arr[$variation_attr] = '<div id="slw-'.$variation_id.'" data-id="'.$variation_id.'" class="slw-variations-listed slw-variation-'.$variation_attr_str.'-locations">'.'<label>'.$variation_attr.'</label>'.$this->output_product_locations_for_shortcode($variation_obj, $locations, $values).'</div>';
						}					
						ksort($variation_attr_arr);
						$output = implode('', $variation_attr_arr);
					}
				}else{
					$output = $this->display_product_locations($values);
				}
			}

			return $output;
			
		}
		
		/**
		 * Output locations for simple and variable products shortcodes.
		 *
		 * @since 1.1.2
		 * @return string
		 */
		private function output_product_locations_for_shortcode( $product, $locations, $values )
		{
			$separator = $values['separator'];
			
			if( !empty($locations) ) {

				// Don't show locations with empty stock
				foreach( $locations as $key => $location ) {
					if( $values['show_empty_stock'] == 'no' && $product->get_meta('_stock_at_'.$location->term_id) == 0 ) {
						unset($locations[$key]);
					}
				}

				// hook to filter product locations
				$locations = apply_filters( 'slw_shortcode_product_locations', $locations, $product );
				
				// Process the other 3 parameters
				$output = '<ul class="slw-product-locations-list" '.($values['collapsed']=='yes'?'style="display:none;"':'').'>';
				
				foreach( $locations as $location ) {
					if( $values['show_qty'] == 'yes' ) {
						$location_stock = $product->get_meta('_stock_at_'.$location->term_id);
						if( !empty($location_stock) ) {
							$output .= '<li class="slw-product-location">'.apply_filters( 'slw_shortcode_product_location_name', $location->name, $location ).' '.$separator.' <span class="slw-product-location-qty slw-product-location-qty__number">'.apply_filters( 'slw_shortcode_product_location_stock', $location_stock, $location ).'</span></li>';
						} else {
							$output .= '<li class="slw-product-location">'.apply_filters( 'slw_shortcode_product_location_name', $location->name, $location ).' <span class="slw-product-location-qty slw-product-location-qty__notavailable">'.__('Not available', 'stock-locations-for-woocommerce').'</span></li>';
						}
					} elseif( $values['show_qty'] == 'no' && $values['show_stock_status'] == 'yes' ) {
						$location_stock = $product->get_meta('_stock_at_'.$location->term_id);
						if( !empty($location_stock) && $location_stock >= 1 ) {
							$output .= '<li class="slw-product-location">'.apply_filters( 'slw_shortcode_product_location_name', $location->name, $location ).' <span class="slw-product-location-status slw-product-location-status__instock">'.__('In stock', 'woocommerce').'</span></li>';
						} else {
							$output .= '<li class="slw-product-location">'.apply_filters( 'slw_shortcode_product_location_name', $location->name, $location ).' <span class="slw-product-location-status slw-product-location-status__outofstock">'.__('Out of stock', 'woocommerce').'</span></li>';
						}
					} else {
						$output .= '<li class="slw-product-location">'.apply_filters( 'slw_shortcode_product_location_name', $location->name, $location ).'</li>';
					}
				}
				$output .= '</ul>';

			} else {
				$output = __('No locations found for this product!', 'stock-locations-for-woocommerce');
			}
			
			$output = apply_filters( 'slw_output_product_locations_for_shortcode', $product, $locations, $values, $output );

			return $output;

		}

		/**
		 * Displays a product message
		 *
		 * @param $atts
		 * @param string $innerHtml
		 *
		 * @return string
		 */
		public function display_product_message( $atts, $innerHtml = '' )
		{
			global $woocommerce, $product, $post;

			if( ! is_product() ) return;

			if( ! is_object( $product)) $product = wc_get_product( get_the_ID() );

			$product_id = SlwWpmlHelper::object_id( get_the_ID() );
			$product    = wc_get_product( $product_id );
			if( empty($product) ) return;

			// Default values
			$values = shortcode_atts(array(
				'is_available' => 'yes',
				'only_location_available' => 'no',
				'location' => '',
			), $atts);

			if( !$values ) {
				return;
			}

			// Stock is not managed
			if (!$product->get_manage_stock()) {
				return;
			}

			// Data
			$isAvailable = $values['is_available'];
			$onlyLocationAvailable = $values['only_location_available'];
			$location = $values['location'];

			// Do nothing
			if ($location === '') {
				return '';
			}

			// Get stock location data
			$stockLocation = SlwStockAllocationHelper::getProductStockLocations($product_id, false, $location);

			// Get available product stock locations
			$availableStockLocations = SlwStockAllocationHelper::getProductAvailableStockLocations($product_id, false);

			// Multiple available stock
			if (strtoupper($onlyLocationAvailable) === 'YES' && sizeof($availableStockLocations) > 1) {
				return '';
			}

			// Decide when to show / hide
			if (strtoupper($isAvailable) === 'YES') {
				if (isset($stockLocation->quantity) && $stockLocation->quantity > 0) {
					return $innerHtml;
				}
			} else {
				if (is_null($stockLocation) || empty($stockLocation) || $stockLocation->quantity <= 0) {
					return $innerHtml;
				}
			}
		}

		/**
		 * Displays a cart message
		 *
		 * @param $atts
		 * @param string $innerHtml
		 *
		 * @return string
		 */
		public function display_cart_message( $atts, $innerHtml = '' )
		{
			if( is_admin() ) return;

			global $woocommerce, $post;

			// Default values
			$values = shortcode_atts(array(
				'qty_from_location' => '',
				'only_location_available' => 'no'
			), $atts);

			if (!$values) {
				return '';
			}

			// Data
			$qtyFromLocation = $values['qty_from_location'];
			$onlyLocationAvailable = $values['only_location_available'];

			// Do nothing
			if ($qtyFromLocation === '') {
				return '';
			}

			// Allocated Locations
			$allocatedLocations = array();

			// Work out what locations stock will be allocated
			$items = $woocommerce->cart->get_cart();
			foreach ($items as $item => $values) {
				// get product id
				$product_id = $values['data']->get_id();
				$product_id = SlwWpmlHelper::object_id( $product_id );

				// Get product stock allocation
				$stockAllocation = SlwStockAllocationHelper::getStockAllocation( $product_id, $values['quantity'] );

				foreach ($stockAllocation as $location) {
					if (!isset($allocatedLocations[$location->slug])) {
						$allocatedLocations[$location->slug] = $location->slug;
					}
				}
			}

			// Multiple available stock
			if (strtoupper($onlyLocationAvailable) === 'YES' && sizeof($allocatedLocations) > 1) {
				return '';
			}

			// Is location part of allocated locations?
			if (in_array($qtyFromLocation, $allocatedLocations)) {
				return $innerHtml;
			}
		}

	}

}
