<?php
/**
 * SLW Frontend Cart Class
 *
 * @since 1.2.0
 */

namespace SLW\SRC\Classes\Frontend;

use SLW\SRC\Helpers\SlwStockAllocationHelper;
use SLW\SRC\Helpers\SlwFrontendHelper;
use SLW\SRC\Helpers\SlwWpmlHelper;

if ( !defined( 'WPINC' ) ) {
	die;
}

if( !class_exists('SlwFrontendCart') ) {

	class SlwFrontendCart
	{
		
		/**
		 * Construct.
		 *
		 * @since 1.2.0
		 */
		public function __construct()
		{
			// get settings
			$this->plugin_settings = get_option( 'slw_settings' );
			
			// check if show in cart is enabled
			add_action( 'woocommerce_after_cart_item_name', array($this, 'add_cart_item_stock_locations'), 99, 2 );
			add_filter( 'woocommerce_get_item_data', array($this, 'show_cart_item_stock_locations'), 99, 2 );
			
			
			if( isset($this->plugin_settings['show_in_cart']) && $this->plugin_settings['show_in_cart'] == 'yes' ) {				
				add_action( 'wp_ajax_update_cart_stock_locations', array($this, 'update_cart_stock_locations') );
				add_action( 'wp_ajax_nopriv_update_cart_stock_locations', array($this, 'update_cart_stock_locations') );
				add_action( 'woocommerce_checkout_create_order_line_item', array($this, 'create_order_line_item_meta_with_selected_location'), 10, 4 );
			}else{
				
				add_action( 'woocommerce_checkout_create_order_line_item', array($this, 'create_order_line_item_meta_with_auto_location'), 10, 4 );
			}

		 
			// check if different location per cart item is enabled
			if( isset($this->plugin_settings['different_location_per_cart_item']) && $this->plugin_settings['different_location_per_cart_item'] == 'no' ) {
				add_action( 'wp_footer', array($this, 'lock_cart_item_location') );
			}

		}

		/**
		 * Add stock locations to cart item.
		 *
		 * @since 1.2.0
		 */
		public function show_cart_item_stock_locations( $cart_item_data, $cart_item ){

	
			$proceed = (
					
					(
					
							( is_cart() && isset($this->plugin_settings['general_display_settings']) && isset($this->plugin_settings['general_display_settings']['cart_page']) && $this->plugin_settings['general_display_settings']['cart_page'] == 'on' )
						
						&&
						
							!(is_cart() && (isset($this->plugin_settings['show_in_cart']) && $this->plugin_settings['show_in_cart'] == 'yes'))
					)
				
				||
					( is_checkout() && isset($this->plugin_settings['general_display_settings']) && isset($this->plugin_settings['general_display_settings']['checkout_page']) && $this->plugin_settings['general_display_settings']['checkout_page'] == 'on' )	
					
				
			);						

			//TESTED FOR THE FOLLOWING PAGE
			//CART PAGE
			//CHECKOUT PAGE
						
			if($proceed && array_key_exists('stock_location', $cart_item)){
				//pree($cart_item);
				$stock_location = '';
				if( isset($cart_item['stock_location']) ) {
					$product_id = ($cart_item['variation_id']?$cart_item['variation_id']:$cart_item['product_id']);
					
					$stock_locations = SlwFrontendHelper::get_all_product_stock_locations_for_selection( $product_id );
					if(array_key_exists($cart_item['stock_location'], $stock_locations)){
						$stock_location = $stock_locations[$cart_item['stock_location']]['name'];
					}
					//$stock_location = '<p>'.$stock_location.(is_cart().' / '.is_checkout().' / '.is_admin().' / '.date('d M, Y H:i:s A')).'</p>';
					if($stock_location){
						$cart_item_data[] = array(
							'name'    => __('Location', 'stock-locations-for-woocommerce'),
							'display' => '<p>'.$stock_location.'</p>'
						);
					}
				}
				
			}
			
			return $cart_item_data;
		}
		public function add_cart_item_stock_locations( $cart_item, $cart_item_key )
		{
			
			if( empty($cart_item) ) return;
			
			
			$product_id            = $cart_item['variation_id'] != 0 ? $cart_item['variation_id'] : $cart_item['product_id'];
			$product_id            = SlwWpmlHelper::object_id( $product_id );
			$stock_locations       = SlwFrontendHelper::get_all_product_stock_locations_for_selection( $product_id );
			$default_location      = isset( $this->plugin_settings['default_location_in_frontend_selection'] ) ? get_post_meta( $product_id, '_slw_default_location', true ) : 0;
			$lock_default_location = isset( $this->plugin_settings['lock_default_location_in_frontend'] ) && $this->plugin_settings['lock_default_location_in_frontend'] == 'on' ? true : false;

			if( !empty($stock_locations) ) {
				
				if( isset($this->plugin_settings['show_in_cart']) && $this->plugin_settings['show_in_cart'] == 'yes' ) {
					
					echo '<label class="slw_cart_item_stock_location_label">'.__('Nearest Location', 'stock-locations-for-woocommerce').':</label>';
					
					// lock to default location if enabled
					if( $lock_default_location && $default_location != 0 ) {
						echo '<select class="slw_item_stock_location slw_cart_item_stock_location_selection" style="display:block;" required disabled>';
						echo '<option class="cart_item_stock_location_'.$cart_item_key.'" data-cart_id="'.$cart_item_key.'" value="'.$default_location.'" selected="selected" disabled="disabled">'.$stock_locations[$default_location]['name'].'</option>';
						echo '</select>';
						return;
					}
	
					// default behaviour
					if( isset($cart_item['stock_location']) ) {
						echo '<select class="slw_item_stock_location slw_cart_item_stock_location_selection" style="display:block;" required>';
						echo '<option disabled>'.__('Select location...', 'stock-locations-for-woocommerce').'</option>';
					} else {
						echo '<select class="slw_item_stock_location slw_cart_item_stock_location_selection" style="display:block;" required>';
						echo '<option disabled selected>'.__('Select location...', 'stock-locations-for-woocommerce').'</option>';
					}
	
					foreach( $stock_locations as $id => $location ) {
						$selected = $disabled = '';
						if( ($location['quantity'] > 0 && $location['quantity'] >= $cart_item['quantity']) || ($location['quantity'] < 1 && $location['allow_backorder'] == 1) ) {
							if( isset($cart_item['stock_location']) && $cart_item['stock_location'] == $location['term_id'] ) {
								$selected = 'selected="selected"';
							}
						} else {
							$disabled = 'disabled="disabled"';
						}
						echo '<option class="cart_item_stock_location_'.$cart_item_key.'" data-cart_id="'.$cart_item_key.'" value="'.$location['term_id'].'" '.$selected.' '.$disabled.'>'.$location['name'].'</option>';
					}
	
					echo '</select>';
					
				}else{
					
					
				}
			}
		}

		/**
		 * Update cart with stock locations.
		 *
		 * @since 1.2.0
		 */
		public function update_cart_stock_locations()
		{
			// Do a nonce check
			if( ! isset($_POST['cart_id']) || ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'woocommerce-cart' ) ) {
				wp_send_json( array( 'nonce_fail' => 1 ) );
				exit;
			}
			// Save the stock locations to the cart meta
			$cart = WC()->cart->cart_contents;
			$cart_id = $_POST['cart_id'];
			$stock_location = $_POST['stock_location'];
			$cart_item = $cart[$cart_id];
			$cart_item['stock_location'] = $stock_location;
			WC()->cart->cart_contents[$cart_id] = $cart_item;
			WC()->cart->set_session();
			wp_send_json( array( 'success' => 1 ) );
			exit;
		}
		
		/**
		 * Save stock locations to order item meta.
		 *
		 * @since 1.2.0
		 */
		 
		public function create_order_line_item_meta_with_selected_location( $item, $cart_item_key, $values, $order )
		{
			
			foreach( $item as $cart_item_key => $cart_item ) {
				if( isset( $cart_item['stock_location'] ) ) {
					$item->add_meta_data( '_stock_location', $cart_item['stock_location'], true );					
				}
			}
		}
		
		public function create_order_line_item_meta_with_auto_location( $item, $cart_item_key_this, $values, $order )
		{
			//wc_slw_logger('create_order_line_item_meta_with_auto_location');
			$product_id = ($item->get_variation_id()?$item->get_variation_id():$item->get_product_id());
			//wc_slw_logger('$product_id: '.$product_id);
			$stock_locations = SlwStockAllocationHelper::getStockAllocation($product_id, $item->get_quantity());
			//wc_slw_logger($stock_locations);
			//wc_slw_logger('$stock_location->term_id: '.$stock_location->term_id);
			if(!empty($stock_locations)) {				
				foreach($stock_locations as $stock_location){					
					if($stock_location->allocated_quantity>0){
						$item->add_meta_data( '_stock_location', $stock_location->term_id, true );		
					}else{
						foreach( $item as $cart_item_key => $cart_item ) {
							if( isset( $cart_item['stock_location'] ) && $cart_item['stock_location']==$stock_location->term_id ) {
								$item->add_meta_data( '_stock_location', $stock_location->term_id, true );
							}
						}
					}
				}
			}
	
		}
		
		
		
		
		/**
		 * Locks the cart item location.
		 *
		 * @since 1.2.1
		 */
		public function lock_cart_item_location()
		{
			if( is_cart() ) {
				?>
				<script>
				jQuery( function ( $ ) {
					$(document).ready(function() {
						$('.slw_cart_item_stock_location_selection').on('change', function() {
							var location_id = $(this).val();
							$(this).closest('.woocommerce-cart-form').find('.slw_cart_item_stock_location_selection').val(location_id).prop('disabled', true);
						});
					});
				} );
				</script>
				<?php
			}
		}


	}

}
