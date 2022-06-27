<?php
/**
 * SLW Frontend Product Class
 *
 * @since 1.3.0
 */

namespace SLW\SRC\Classes\Frontend;

use SLW\SRC\Helpers\SlwFrontendHelper;
use SLW\SRC\Helpers\SlwWpmlHelper;

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
			$this->plugin_settings = get_option( 'slw_settings' );

			// check if show in cart is enabled
			if( isset( $this->plugin_settings['show_in_product_page']) && $this->plugin_settings['show_in_product_page'] != 'no' ) {
				add_action( 'woocommerce_before_add_to_cart_button', array($this, 'simple_location_select') );
				add_action( 'woocommerce_single_variation', array($this, 'variable_location_select') );
				add_filter( 'woocommerce_add_cart_item_data', array($this, 'add_to_cart_location_validation'), 10, 3 );
			}

			add_action( 'wp_ajax_get_variation_locations', array($this, 'get_variation_locations') );
			add_action( 'wp_ajax_nopriv_get_variation_locations', array($this, 'get_variation_locations') );
			
		}

		/**
		 * Add stock locations selection to simple product page.
		 *
		 * @since 1.3.0
		 */
		 
		public static function location_select_input($type='select', $product_id=0, $stock_locations=array(), $html=''){
			
			$ret = '';

			global $slw_plugin_settings, $wc_slw_pro;
			
			$default_location      = array_key_exists('default_location_in_frontend_selection', $slw_plugin_settings ) ? get_post_meta( $product_id, '_slw_default_location', true ) : 0;
			$lock_default_location = ((array_key_exists('lock_default_location_in_frontend', $slw_plugin_settings ) && $slw_plugin_settings['lock_default_location_in_frontend'] == 'on') ? true : false);
			
			$everything_stock_status_to_instock = array_key_exists('everything_stock_status_to_instock', $slw_plugin_settings);
			
			if(empty($stock_locations)){
				
				$stock_locations = SlwFrontendHelper::get_all_product_stock_locations_for_selection( $product_id, $everything_stock_status_to_instock );
			}
			
			
			$product_stock_price_status = ((array_key_exists('product_stock_price_status', $slw_plugin_settings ) && $slw_plugin_settings['product_stock_price_status'] == 'on') ? true : false);
			

			$type = ($wc_slw_pro?$type:str_replace('radio', 'select', $type));
			
			
			
			switch($type){
			
				case 'select_simple_default':
				
					$stock_price = $stock_locations[$default_location]['price'];
					$stock_location_name = $stock_locations[$default_location]['name'];
				
					if($product_stock_price_status){
						$stock_location_name .= ' '.wc_price($stock_price);
					}
					
					$selected = ($stock_locations[$default_location]['quantity']>0?'selected="selected"':'');
				
					$ret .= '<div class="slw_stock_location_selection">
					
					<select id="slw_item_stock_location_simple_product" class="slw_item_stock_location sls display_'.$slw_plugin_settings['show_in_product_page'].' default" name="slw_add_to_cart_item_stock_location" style="display:block;" required disabled><option data-price="'.$stock_price.'" data-quantity="'.$stock_locations[$default_location]['quantity'].'" value="'.$default_location.'" '.$selected.'>'.$stock_location_name.'</option></select>
					
					</div>';
				break;
				
				case 'select_simple':
				
					$ret .= '<div class="slw_stock_location_selection">
					
					<select id="slw_item_stock_location_simple_product" class="slw_item_stock_location sls display_'.$slw_plugin_settings['show_in_product_page'].' remaining" name="slw_add_to_cart_item_stock_location" style="display:block;" required>';
					if( $default_location != 0 ) {
						$ret .= '<option data-price="" data-quantity="" value="0">'.__('Select location...', 'stock-locations-for-woocommerce').'</option>';
					} else {
						$ret .= '<option data-price="" data-quantity="" value="0" selected>'.__('Select location...', 'stock-locations-for-woocommerce').'</option>';
					}
					
					
					$priority_used = -1;
					foreach( $stock_locations as $id => $location ) {
						
						$selected = '';
						
						$slw_location_priority = get_term_meta($id, 'slw_location_priority', true);


						
						if($location['quantity']>0){
							if( $default_location != 0 && $location['term_id'] == $default_location){
								$selected = 'selected="selected"';
							}else{								
								if($slw_location_priority>$priority_used){						
									$priority_used = $slw_location_priority;
									$selected = 'selected="selected"';
								}						
							}
						}
						
						
						$stock_price = $location['price'];
						
						$stock_location_name = $location['name'];
						
						if($product_stock_price_status){
							$stock_location_name .= ' '.get_woocommerce_currency_symbol().($stock_price);
						}
	
	
						$disabled = '';
						if( $location['quantity'] < 1 && $location['allow_backorder'] != 1 && !$everything_stock_status_to_instock) {
							$disabled = 'disabled="disabled"';
						}
					
						$ret .= '<option data-priority="'.$slw_location_priority.'" data-price="'.$stock_price.'" data-quantity="'.$location['quantity'].'" value="'.$location['term_id'].'" '.$disabled.' '.$selected.'>'.$stock_location_name.'</option>';
					
					}
					$ret .= '</select></div>';				
				break;
				
				case 'radio_simple':
				case 'radio_variable':
					if($wc_slw_pro && function_exists('location_select_input_inner')){	

						$ret = location_select_input_inner($type, $product_id, $stock_locations, $html);
					}
				break;			
				
				case 'select_variable':
					
					$term_id = (is_product()?false:get_queried_object_id());
					
					
					
					$ret .= '<div '.($term_id?'style="display:none !important; width:100%;"':'class="slw_stock_location_selection"').'>';
					if( $lock_default_location && $default_location != 0 ) {
						$ret .= '<select id="slw_item_stock_location_variable_product" class="slw_item_stock_location vls display_'.$this->plugin_settings['show_in_product_page'].'" name="slw_add_to_cart_item_stock_location" required disabled>';
					} else {
						$ret .= '<select id="slw_item_stock_location_variable_product" class="slw_item_stock_location vls display_'.$this->plugin_settings['show_in_product_page'].'" name="slw_add_to_cart_item_stock_location" required>';
					}
					if($term_id){
						$ret .= '<option data-price="" data-quantity="" value="'.$term_id.'" selected></option>';
					}else{
						if( $default_location != 0 ) {
							$ret .= '<option data-price="" data-quantity="" value="0">'.__('Select location...', 'stock-locations-for-woocommerce').'</option>';
						} else {
							$ret .= '<option data-price="" data-quantity="" value="0" selected>'.__('Select location...', 'stock-locations-for-woocommerce').'</option>';
						}
					}
					$ret .= '</select></div>';
							
				break;
							
			}
			
			return $ret;
		}
		public function simple_location_select()
		{
			global $product, $slw_plugin_settings;
			
			if( empty( $product ) || $product->get_type() != 'simple' ) return;
			
			$product_id            = SlwWpmlHelper::object_id( $product->get_id() );
			$default_location      = isset( $this->plugin_settings['default_location_in_frontend_selection'] ) ? get_post_meta( $product_id, '_slw_default_location', true ) : 0;
			$lock_default_location = isset( $this->plugin_settings['lock_default_location_in_frontend'] ) && $this->plugin_settings['lock_default_location_in_frontend'] == 'on' ? true : false;
			
			
			$everything_stock_status_to_instock = array_key_exists('everything_stock_status_to_instock', $slw_plugin_settings);				
			$stock_locations       = SlwFrontendHelper::get_all_product_stock_locations_for_selection( $product_id, $everything_stock_status_to_instock );

			if( ! empty( $stock_locations ) ) {
				
				$show_in_product_page = $this->plugin_settings['show_in_product_page'];
				$location_select_input_type = 'select_simple';
				switch($show_in_product_page){
					case 'yes_radio':
						$location_select_input_type = 'radio_simple';
					break;
				}

				if( $lock_default_location && $default_location != 0 ) {					
					
					
					$location_select_input = $this->location_select_input($location_select_input_type.'_default', $product_id, $stock_locations);
					echo $location_select_input;
					
					return;
				}
				
				$location_select_input = $this->location_select_input($location_select_input_type, $product_id, $stock_locations);
				echo $location_select_input;
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
			if( empty($product) ) return;
			$product_id            = SlwWpmlHelper::object_id( $product->get_id() );
			$product = wc_get_product( $product_id );
			if( empty($product) || $product->get_type() != 'variable' ) return;
			
			$show_in_product_page = $this->plugin_settings['show_in_product_page'];
			$location_select_input_type = 'select_variable';
			switch($show_in_product_page){
				case 'yes_radio':
					$location_select_input_type = 'radio_variable';
				break;
			}			
			$location_select_input = $this->location_select_input($location_select_input_type, $product_id);
			echo $location_select_input;
			
		}

		/**
		 * Get variation locations.
		 *
		 * @since 1.3.0
		 */
		public function get_variation_locations()
		{
			
			if( isset( $_POST['variation_id'] ) && isset( $_POST['product_id'] ) && $_POST['action'] == 'get_variation_locations' ) {
				
				
				$variation_id          = sanitize_text_field( $_POST['variation_id'] );
				$variation_id          = SlwWpmlHelper::object_id( $variation_id );
				$product_id            = sanitize_text_field( $_POST['product_id'] );
				$product_id            = SlwWpmlHelper::object_id( $product_id );
				$product_variation_id  = ($variation_id?$variation_id:$product_id);

				$stock_locations       = SlwFrontendHelper::get_all_product_stock_locations_for_selection( $variation_id );
				$default_location      = isset( $this->plugin_settings['default_location_in_frontend_selection'] ) ? get_post_meta( $product_id, '_slw_default_location', true ) : 0;
				
				
				
				
				if( !empty($stock_locations) ) {
					wp_send_json_success( compact( 'stock_locations', 'default_location' ) );
				} else {
					wp_send_json_error( array(
						'error' => __('No locations found for this product/variant!', 'stock-locations-for-woocommerce')
					) );
				}
			}
			die();
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
