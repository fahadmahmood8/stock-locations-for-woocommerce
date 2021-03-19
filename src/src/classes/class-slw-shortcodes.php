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
		public function display_product_locations( $atts )
		{
			global $woocommerce, $product, $post;

			if( ! is_product() ) return;

			if( ! is_object( $product)) $product = wc_get_product( get_the_ID() );

			$product_id = SlwWpmlHelper::object_id( $product->get_id(), $product->get_type() );
			$product    = wc_get_product( $product_id );
			if( empty($product) ) return;

			// Default values
			$values = shortcode_atts(array(
				'show_qty'          => 'yes',
				'show_stock_status' => 'no',
				'show_empty_stock'  => 'yes'
			), $atts);

			if( !$values ) {
				return;
			}

			$output = '';

			if( !empty($product) ) {
				// Get locations from parent product
				$locations = wp_get_post_terms($product->get_id(), SlwLocationTaxonomy::$tax_singular_name);
				// Build output
				$output .= '<div class="slw-product-locations">';
				$output .= $this->output_product_locations_for_shortcode($product, $locations, $values);
				$output .= '</div>';
			}

			return $output;
			
		}

		/**
		 * Displays the product variation locations.
		 *
		 * @since 1.1.2
		 * @return string
		 */
		public function display_product_variations_locations( $atts )
		{
			global $woocommerce, $product, $post;

			if( ! is_product() ) return;

			if( ! is_object( $product)) $product = wc_get_product( get_the_ID() );

			$product_id = SlwWpmlHelper::object_id( $product->get_id(), $product->get_type() );
			$product    = wc_get_product( $product_id );
			if( empty($product) ) return;

			// Default values
			$values = shortcode_atts(array(
				'show_qty'          => 'yes',
				'show_stock_status' => 'no',
				'show_empty_stock'  => 'yes'
			), $atts);

			if( !$values ) {
				return;
			}

			$output = '';

			if( !empty($product) ) {
				// Check for variations
				$variations_products = array();
				if( $product->is_type( 'variable' ) ) {
					$product_variations_ids = $product->get_children();
					$product_variations = array();
					foreach( $product_variations_ids as $variation_id ) {
						$product_variations[] = $product->get_available_variation( $variation_id );
					}
					foreach ($product_variations as $variation) { 
						$variations_products[] = wc_get_product( $variation['variation_id'] );
					}
				}

				// Get locations from parent product
				$locations = wp_get_post_terms($product->get_id(), SlwLocationTaxonomy::$tax_singular_name);

				if( !empty($variations_products) ) {
					foreach( $variations_products as $variation_product ) {
						foreach( $attributes = $variation_product->get_variation_attributes() as $attribute ) {
							$output .= '<div class="slw-variation-'.$attribute.'-locations"><label>'.ucfirst($attribute).'</label>';
						}
						$output .= $this->output_product_locations_for_shortcode($variation_product, $locations, $values);
						$output .= '</div>';
					}
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
			if( !empty($locations) ) {

				// Don't show locations with empty stock
				foreach( $locations as $key => $location ) {
					if( $values['show_empty_stock'] == 'no' && $product->get_meta('_stock_at_'.$location->term_id) == 0 ) {
						unset($locations[$key]);
					}
				}

				// Process the other 3 parameters
				$output = '<ul class="slw-product-locations-list">';
				foreach( $locations as $location ) {
					if( $values['show_qty'] == 'yes' ) {
						$location_stock = $product->get_meta('_stock_at_'.$location->term_id);
						if( !empty($location_stock) ) {
							$output .= '<li class="slw-product-location">'.apply_filters( 'slw_shortcode_product_location_name', $location->name, $location ).' <span class="slw-product-location-qty slw-product-location-qty__number">'.apply_filters( 'slw_shortcode_product_location_stock', $location_stock, $location ).'</span></li>';
						} else {
							$output .= '<li class="slw-product-location">'.apply_filters( 'slw_shortcode_product_location_name', $location->name, $location ).' <span class="slw-product-location-qty slw-product-location-qty__notavailable">'.__('Not available', 'stock-locations-for-woocommerce').'</span></li>';
						}
					} elseif( $values['show_qty'] == 'no' && $values['show_stock_status'] == 'yes' ) {
						$location_stock = $product->get_meta('_stock_at_'.$location->term_id);
						if( !empty($location_stock) && $location_stock > 0 ) {
							$output .= '<li class="slw-product-location">'.apply_filters( 'slw_shortcode_product_location_name', $location->name, $location ).' <span class="slw-product-location-status slw-product-location-status__instock">'.__('In stock', 'stock-locations-for-woocommerce').'</span></li>';
						} else {
							$output .= '<li class="slw-product-location">'.apply_filters( 'slw_shortcode_product_location_name', $location->name, $location ).' <span class="slw-product-location-status slw-product-location-status__outofstock">'.__('Out of stock', 'stock-locations-for-woocommerce').'</span></li>';
						}
					} else {
						$output .= '<li class="slw-product-location">'.apply_filters( 'slw_shortcode_product_location_name', $location->name, $location ).'</li>';
					}
				}
				$output .= '</ul>';

			} else {
				$output = __('No locations found for this product!', 'stock-locations-for-woocommerce');
			}

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

			$product_id = SlwWpmlHelper::object_id( get_the_ID(), get_post_type( get_the_ID() ) );
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
				$product_id = SlwWpmlHelper::object_id( $product_id, get_post_type( $product_id ) );

				// Get product stock allocation
				$stockAllocation = SlwStockAllocationHelper::getStockAllocation( $product_id, $values['quantity'], null );

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
