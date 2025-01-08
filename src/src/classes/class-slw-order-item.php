<?php
/**
 * SLW Order Item Class
 *
 * @since 1.0.0
 */

namespace SLW\SRC\Classes;

use SLW\SRC\Classes\SlwAdminNotice;
use SLW\SRC\Helpers\SlwOrderItemHelper;
use SLW\SRC\Helpers\SlwStockAllocationHelper;
use SLW\SRC\Helpers\SlwWpmlHelper;
use SLW\SRC\Helpers\SlwProductHelper;

if ( !defined( 'WPINC' ) ) {
	die;
}

if( !class_exists('SlwOrderItem') ) {

	class SlwOrderItem
	{
		private $items;
		private $plugin_settings;
		private $show_in_cart;
		private $wc_manage_stock;
		private $wc_hold_stock_minutes;
		private $different_location_per_cart_item;

		/**
		 * Construct.
		 *
		 * @since 1.1.0
		 */
		public function __construct()
		{
			add_action('woocommerce_admin_order_item_headers', array($this, 'add_stock_location_column_wc_order'), 10, 1);
			add_action('woocommerce_admin_order_item_values', array($this, 'add_stock_location_inputs_wc_order'), 10, 3);
			add_action('woocommerce_process_shop_order_meta', array($this, 'reduce_order_items_locations_stock_on_save'), 10, 3);
			add_action('woocommerce_before_save_order_item', array($this, 'disable_wc_order_adjust_line_item_product_stock'), 99, 1);
			add_filter('woocommerce_hidden_order_itemmeta', array($this, 'hide_stock_locations_itemmeta_wc_order'), 10, 1);
			add_action('woocommerce_new_order_item', array($this, 'newOrderItemAllocateStock'), 10, 3);
			
	
	

			// get plugin settings
			$this->plugin_settings = get_option( 'slw_settings' );

			// get show in cart value from settings
			if( isset($this->plugin_settings['show_in_cart']) ) {
				$this->show_in_cart = $this->plugin_settings['show_in_cart'];
			}

			$this->different_location_per_cart_item = !(isset($this->plugin_settings['different_location_per_cart_item']) && $this->plugin_settings['different_location_per_cart_item'] == 'no');
			// check if we can include location data in formatted item meta			
			
			add_filter( 'woocommerce_order_item_get_formatted_meta_data', array($this, 'include_location_data_in_formatted_item_meta'), 99, 2 );
			
			
			// WC manage stock
			$this->wc_manage_stock = get_option( 'woocommerce_manage_stock' );
			// WC hold stock minutes
			$this->wc_hold_stock_minutes = get_option( 'woocommerce_hold_stock_minutes' );

			// Send copy of WC New Order email to location address
			if( isset($this->plugin_settings['wc_new_order_location_copy']) ) {
				add_filter( 'woocommerce_email_headers', array($this, 'wc_new_order_email_copy_to_locations_email'), 10, 3);
			}

			
			//pree($this->wc_manage_stock);

			if( $this->wc_manage_stock === 'yes') {
				add_action( 'woocommerce_reduce_order_stock', array( $this, 'reduce_order_items_locations_stock' ), 10, 1 );
				add_action( 'woocommerce_restore_order_stock', array( $this, 'restore_order_items_locations_stock' ), 10, 1 );
			} else {
				// on maybe reduce stock levels
				add_action( 'woocommerce_payment_complete', array( $this, 'reduce_order_items_locations_stock' ), 10, 1 );
				add_action( 'woocommerce_order_status_completed', array( $this, 'reduce_order_items_locations_stock' ), 10, 1 );
				add_action( 'woocommerce_order_status_processing', array( $this, 'reduce_order_items_locations_stock' ), 10, 1 );
				add_action( 'woocommerce_order_status_on-hold', array( $this, 'reduce_order_items_locations_stock' ), 10, 1 );
				// on maybe restore stock levels
				//add_action( 'woocommerce_order_status_cancelled', array( $this, 'restore_order_items_locations_stock' ), 10, 1 );
				//add_action( 'woocommerce_order_status_pending', array( $this, 'restore_order_items_locations_stock' ), 10, 1 );
			}
			
			add_action( 'woocommerce_order_status_changed', array( $this, 'restore_order_items_stock' ), 10, 2 );
			
			/*
			if( isset($this->plugin_settings['wc_restore_stock_on_cancelled']) ) {
				add_action( 'woocommerce_order_status_cancelled', array( $this, 'restore_order_items_locations_stock' ), 10, 1 );
			}
			if( isset($this->plugin_settings['wc_restore_stock_on_failed']) ) {
				
				
			}
			if( isset($this->plugin_settings['wc_restore_stock_on_pending']) ) {
				add_action( 'woocommerce_order_status_pending', array( $this, 'restore_order_items_locations_stock' ), 10, 1 );
			}
			*/
		}
		
		public function restore_order_items_stock($order, $status){
			
			if(is_numeric($order)){
				$order = wc_get_order($order);
			}
			
			
			if( empty($order) || ! is_object($order) ) return;
			

			if( 
					(isset($this->plugin_settings['wc_restore_stock_on_cancelled']) && $order->get_status()=='cancelled')
				||
					(isset($this->plugin_settings['wc_restore_stock_on_failed']) && $order->get_status()=='failed')
				||
					(isset($this->plugin_settings['wc_restore_stock_on_pending']) && $order->get_status()=='pending')
			){
				//delete_post_meta($order->get_id(), '_slw_order_stock_reduced');

				$this->restore_order_items_locations_stock( $order );

			}			
			
			
		}
		public function restore_order_items_locations_stock( $order ){

			
			
			if( empty($order) || ! is_object($order) ) return;


			$wc_order_stock_reduced = get_post_meta( $order->get_id(), '_slw_order_stock_reduced', true );
		
			

			
			if( $wc_order_stock_reduced ) return;
			
			
			
			
			if(count($order->get_items( 'line_item' ))>0){
				foreach( $order->get_items( 'line_item' ) as $item_id => $item ) {
					$product_id = $item['variation_id'] != 0 ? $item['variation_id'] : $item['product_id'];
					$product_id = SlwWpmlHelper::object_id( $product_id );
					$product    = wc_get_product( $product_id );
					if( empty($product) ) continue;
	
					if ( ! SlwStockAllocationHelper::isManagedStock( $product_id ) ) continue;
	
					$itemStockLocationTerms = SlwStockAllocationHelper::getProductStockLocations( $product_id, false );
					if( empty($itemStockLocationTerms) ) continue;
	
					$slw_data = wc_get_order_item_meta( $item_id, '_slw_data', true );
					if( empty($slw_data) ) continue;
	
					foreach( $itemStockLocationTerms as $location_id => $location ) {
						if( isset( $slw_data[$location_id] ) ) {
							// update the product location stock
							update_post_meta( $product_id, '_stock_at_' . $location_id, $location->quantity + $slw_data[$location_id]['quantity_subtracted'] );
	
							// delete the order item meta
							wc_delete_order_item_meta( $item_id, '_item_stock_locations_updated' );
							wc_delete_order_item_meta( $item_id, '_item_stock_updated_at_' . $location_id );
							wc_delete_order_item_meta( $item_id, '_slw_data' );
	
							// add order note
							$order->add_order_note(
								sprintf( __('The stock in the location %1$s was restored in %2$d for the product %3$s', 'stock-locations-for-woocommerce'), $location->name, $slw_data[$location_id]['quantity_subtracted'], $product->get_name() )
							);
						}
					}
	
					// get product locations total stock
					$locations_total_stock = SlwProductHelper::get_product_locations_stock_total( $product_id );
	
					// update product main stock
					wc_slw_logger('debug', '$product_id: '.$product_id.', $locations_total_stock: '.$locations_total_stock);
					slw_update_product_stock_status( $product_id, $locations_total_stock );
					
	
					// update stock status
					SlwProductHelper::update_wc_stock_status( $product_id );
					wc_slw_order_update_post_meta( $order->get_id(), '_slw_order_stock_reduced', true );
				}
			
				
				
			}
		}

		public function disable_wc_order_adjust_line_item_product_stock( $item )
		{
			add_filter( 'woocommerce_prevent_adjust_line_item_product_stock', '__return_true' );
		}

		/**
		 * Adds custom column for Stock Location in WC Order items.
		 *
		 * @param $order
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function add_stock_location_column_wc_order( $order )
		{
			if( empty($order) ) return;
			
			$edit_order_page = (isset($this->plugin_settings['general_display_settings']) && isset($this->plugin_settings['general_display_settings']['edit_order_page']) && $this->plugin_settings['general_display_settings']['edit_order_page'] == 'on' );
			
			if(!$edit_order_page) return;
			
			// display the column name
			echo '<th>' . __('Stock Locations', 'stock-locations-for-woocommerce') . '</th>';

			// Declare variable as array type
			$items = [];
			// Loop through order items
			foreach ( $order->get_items() as $item_id => $item ) {
				$product_id = $item['variation_id'] != 0 ? $item['variation_id'] : $item['product_id'];
				$product_id = SlwWpmlHelper::object_id( $product_id );

				$items[] = [
					'product_id'    => $product_id,
					'order_item_id' => $item_id,
				];

				// Check if the stock locations are already updated in items of this order and show warning if necessary
				if( empty( wc_get_order_item_meta($item_id, '_item_stock_locations_updated', true) ) && $order->get_status() != 'completed' ) {
					$warning_str = __('Partial or total stock in locations is missing', 'stock-locations-for-woocommerce').' <a href="'.get_the_permalink($order->get_order_number()).'" target="_blank">'.__('in this order', 'stock-locations-for-woocommerce').'</a> '.__('Please fill the remaining stock.', 'stock-locations-for-woocommerce');					
					SlwAdminNotice::displayWarning($warning_str);
				}
			}
			// Assign variable to the class property
			$this->items = $items;
		}

		/**
		 * Adds inputs to custom column for Stock Locations in WC Order items.
		 *
		 * @param $product
		 * @param $item
		 * @param $item_id
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function add_stock_location_inputs_wc_order( $product, $item, $item_id )
		{
			if( empty($item) || empty($product) || empty($item_id) ) return;
			
			$edit_order_page = (isset($this->plugin_settings['general_display_settings']) && isset($this->plugin_settings['general_display_settings']['edit_order_page']) && $this->plugin_settings['general_display_settings']['edit_order_page'] == 'on' );
			
			if(!$edit_order_page) return;

			// Add the missing stock location column to item shipping and others
			if( $item->get_type() == 'shipping' ) {
				echo '<td></td>';
			}

			$product_id = SlwWpmlHelper::object_id( $product->get_id() );
			$product    = wc_get_product( $product_id );
			if( empty($product) ) return;

			if( is_object($product) ) {

				// Check if product is a variation
				if( $product->get_type() === 'variation' ) {

					// Get variation parent id
					$parent_id = $item->get_product_id();

					// Get the variation id
					$variation_id = $product->get_ID();

					// Get the parent location terms
					$product_stock_location_terms = SlwStockAllocationHelper::getProductStockLocations($parent_id, true, null);

					// If parent doesn't have terms show message
					if(!$product_stock_location_terms && !SlwOrderItemHelper::productStockLocationsInputsAddPreviousStock([], $item)) {
						echo '<td width="15%" title="variation">';
						echo '<div display="block">' . __('To be able to manage the stock for this product, please add it to a <b>Stock location</b>!', 'stock-locations-for-woocommerce') . '</div>';
						echo '</td>';
					} else {
						// Add stock location inputs
						$this->product_stock_location_inputs($variation_id, $product_stock_location_terms, $item, $item_id);
					}

				} else {

					// Get the product id
					$product_id = $item->get_product_id();

					// Product location terms
					$product_stock_location_terms = SlwStockAllocationHelper::getProductStockLocations($product_id, true, null);

					// If product doesn't have terms show message
					if(!$product_stock_location_terms && !SlwOrderItemHelper::productStockLocationsInputsAddPreviousStock([], $item)) {
						echo '<td width="15%" title="simple">';
						echo '<div display="block">' . __('To be able to manage the stock for this product, please add it to a <b>Stock location</b>!', 'stock-locations-for-woocommerce') . '</div>';
						echo '</td>';
					} else {
						// Add stock location inputs
						$this->product_stock_location_inputs($product_id, $product_stock_location_terms, $item, $item_id);
					}

				}

			}

		}

		/**
		 * Creates the inputs for Stock Locations in WC Order items.
		 *
		 * @param $id
		 * @param $product_stock_location_terms
		 * @param $item
		 * @param $item_id
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function product_stock_location_inputs( $id, $product_stock_location_terms, $item, $item_id )
		{
			
			$slw_order_id = wc_get_order_id_by_order_item_id( $item_id ); 
			$_slw_locations_stock_status = get_post_meta($slw_order_id, '_slw_locations_stock_status', true);
			
			$product_id = SlwWpmlHelper::object_id( $id );
			$product    = wc_get_product( $product_id );
			if( empty($product) ) return;
			if( empty($item) ) return;

            // Add previous stock locations to view, this is so users can see how stock was previous allocated on past orders,
            // for example if 2 items where allocated to location 2, but location 2 is no longer a valid location for this stock item,
            // for this order the stock was stilled fulfilled by location 2 at the time of the order being processed.
            $product_stock_location_terms = SlwOrderItemHelper::productStockLocationsInputsAddPreviousStock($product_stock_location_terms, $item);
			
			
            // If product allows stock management
			if( $product->get_manage_stock() == 'true' ) {

				// Add the input field to values table
				echo '<td width="15%">'.wc_slw_edit_stocks($slw_order_id, $item_id);

					// Loop throw location terms
					foreach($product_stock_location_terms as $term) {

						// Define $args_1 as array type
						$args_1 = array(
							'type' => 'number'
						);
						
						$postmeta_stock_at_term = 0;

						// Get the item meta
						$postmeta_stock_at_term = $product->get_meta('_stock_at_' . $term->term_id);
						//pree($postmeta_stock_at_term);
						if(is_array($_slw_locations_stock_status) && array_key_exists($product->get_id(), $_slw_locations_stock_status) && array_key_exists($term->term_id, $_slw_locations_stock_status[$product->get_id()])){
							
							//pree($_slw_locations_stock_status);
							
							$postmeta_stock_at_term = $_slw_locations_stock_status[$product->get_id()][$term->term_id];//$product->get_meta('_stock_at_' . $term->term_id);
							$postmeta_stock_at_term = (is_array($postmeta_stock_at_term)?current($postmeta_stock_at_term):0);
							//pree($postmeta_stock_at_term);
						}
						//pree($postmeta_stock_at_term);
						if(!$postmeta_stock_at_term) {
							$postmeta_stock_at_term = 0;
						}

						// Get the item meta
						$itemmeta_stock_update_at_term = wc_get_order_item_meta($item_id, '_item_stock_updated_at_' . $term->term_id, true);
						//pree($term->term_id);
						//pree($postmeta_stock_at_term);
						
						// If the order item has the stock locations updated, show the quantity already subtracted
						//pree('_item_stock_locations_updated: '.wc_get_order_item_meta($item_id, '_item_stock_locations_updated', true));
						if( wc_get_order_item_meta($item_id, '_item_stock_locations_updated', true) === 'yes' ) {
							$args_1['custom_attributes'] = array('readonly' => 'readonly');
							$args_1['type'] = 'hidden';
							
							//pree('$itemmeta_stock_update_at_term: '.$itemmeta_stock_update_at_term);

							if($itemmeta_stock_update_at_term) {
								$args_1['label'] = $term->name . ' <b>(' . $postmeta_stock_at_term . ')</b> <span style="color:green;">-' . $itemmeta_stock_update_at_term . '</span>';
							} else {
								$args_1['label'] = $term->name . ' <b>(' . $postmeta_stock_at_term . ')</b>';
							}

						} else {
							$args_1['label'] = $term->name . ' <b>(' . $postmeta_stock_at_term . ')</b>';
						}

						// If this location doesn't have stock don't show the input
						if( empty($postmeta_stock_at_term) || $postmeta_stock_at_term <= 0 ) {
							$args_1['description'] = __("This location doesn't have stock and can't be subtracted.", 'stock-locations-for-woocommerce');
							$args_1['type'] = 'hidden';
						} else {
							$args_1['description'] = __( 'Enter the stock amount you want to subtract from this location.', 'stock-locations-for-woocommerce' );
						}
						
						// Define $args_2 array
						$args_2 = array(
							'id'                => SLW_PLUGIN_SLUG . '_oitem_' . $item_id . '_' . $id . '_' . $term->term_id,
							'desc_tip'          => true,
							'class'             => 'woocommerce ' . SLW_PLUGIN_SLUG . '_oitem_' . $id . ' ' . SLW_PLUGIN_SLUG . '_oitem',
							'value'             => '0',
						);

						// Merge the two arrays
						$args = array_merge($args_1, $args_2);

						// Create the input
						if( function_exists('woocommerce_wp_text_input') ) {
							woocommerce_wp_text_input($args);
						}
						
						// Show location choosed by client in cart
						//if( !empty($this->show_in_cart) && $this->show_in_cart == 'yes' ) {
							$client_item_stock_location_id = $item->get_meta('_stock_location');
							$client_item_stock_location_ids = $item->get_meta('_stock_locations');
							$client_item_stock_location_ids = (is_array($client_item_stock_location_ids)?$client_item_stock_location_ids:array($client_item_stock_location_id));

							if(!empty($client_item_stock_location_ids)){
								$stock_location = SlwStockAllocationHelper::get_product_stock_location( $id, $term->term_id );
								if( in_array($term->term_id, $client_item_stock_location_ids) ) {
									echo '<span class="slw-client-chose-location '.($client_item_stock_location_id==$term->term_id?'primary-selection':'secondary-selection').'">'.($client_item_stock_location_id==$term->term_id?'✔ <strong>'.__('Client selected: ', 'stock-locations-for-woocommerce').'</strong>':'').'<u>'.$stock_location[$term->term_id]->name.'</u></span>';
								}else{
									
								}
							}
						//}

					}

				echo '</td>';

			} else {

				// Show message if the product/variant doesn't allow stock management
				echo '<td width="15%">';
				echo '<div display="block">' . __("This product/variation don't have stock management activated.", 'stock-locations-for-woocommerce') . '</div>';
				echo '</td>';

			}

		}

		/**
		 * Reduces order items locations stock.
		 *
		 * @param $order  can be the order ID in some hooks
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function reduce_order_items_locations_stock( $order ){ 
			
			//wc_slw_logger('debug', 'reduce_order_items_locations_stock: '.'Yes');
			
			global $current_screen;
			//pree($current_screen);exit;
			$is_shop_order = (is_object($current_screen) && isset($current_screen->post_type) && $current_screen->post_type=='shop_order');
			
			//pree('$is_shop_order: '.$is_shop_order);exit;
			
			//pree($order);exit;
			
			if( empty( $order ) ) return;

			// some actions provide the order_id directly instead of the order object
			if( ! is_object( $order ) ) {
				$order_id = $order;
				$order    = wc_get_order( $order_id );
			}
			
			//pree($order);exit;
			
			// Loop through order items
			foreach ( $order->get_items() as $item => $item_data ) {
				// Product ID
				$pid = ($item_data->get_variation_id()) ? $item_data->get_variation_id() : $item_data->get_product_id();
				$pid = SlwWpmlHelper::object_id( $pid );
				
				$isManagedStock = SlwStockAllocationHelper::isManagedStock($pid);
				
				//pree('$pid: '.$pid.', $isManagedStock: '.$isManagedStock);exit;
				
				// Not managed stock
				if (!$isManagedStock) {
					continue;
				}

				// Get locations
				$locations = SlwStockAllocationHelper::getProductStockLocations($pid, false);
				
				//pree($locations);exit;

				// No locations set
				if (empty($locations)) {
					continue;
				}
				//
				
				// Convert POST data to array
				$simpleLocationAllocations = array();
				foreach ($locations as $location) {
					$productId = $item_data->get_product()->get_id();
					$productId = SlwWpmlHelper::object_id( $productId );
					
					//pree('$productId: '.$productId);
					
					if (is_admin() && $is_shop_order){
						$postIdx   = SLW_PLUGIN_SLUG . '_oitem_' . $item_data->get_id() . '_' . $productId . '_' . $location->term_id;
						if(!empty($_POST) && isset($_POST[$postIdx])) {
							$simpleLocationAllocations[$location->term_id] = $_POST[$postIdx];
						}
					}else{
						
					}
				}
				//pree($simpleLocationAllocations);exit;
				
				// No location stock data for line
				if (empty($simpleLocationAllocations)) {
					continue;
				}
				
				// Allocate stock to locations
				$locationStockAllocationResponse = SlwOrderItemHelper::allocateLocationStock( $item_data->get_id(), $simpleLocationAllocations, $allocationType = 'manual' );

				// Check if stock in locations are updated for this item
				
				
				if(!$locationStockAllocationResponse) {
					$warning_str = __('Partial or total stock in locations is missing', 'stock-locations-for-woocommerce').' <a href="'.get_the_permalink($order->get_order_number()).'" target="_blank">'.__('in this order', 'stock-locations-for-woocommerce').'</a> '.__('Please fill the remaining stock.', 'stock-locations-for-woocommerce');					
					SlwAdminNotice::displayWarning($warning_str);
				} else {
					$notice_str = __('Stock in locations updated successfully', 'stock-locations-for-woocommerce').' <a href="'.get_the_permalink($order->get_order_number()).'" target="_blank">'.__('for this order', 'stock-locations-for-woocommerce').'</a>.';
					SlwAdminNotice::displaySuccess($notice_str);
				}
			}
			//exit;
		}

		/**
		 * Reduces order items locations stock on order save.
		 *
		 * @return void
		 * @since 1.5.2
		 */
		public function reduce_order_items_locations_stock_on_save( $order_id, $order, $order_ids=array() )
		{
			$this->reduce_order_items_locations_stock( $order_id );
			
			

		}

		/**
		 * Hides Stock Location item meta from WC Order.
		 *
		 * @since 1.0.1
		 * @return array
		 */
		public function hide_stock_locations_itemmeta_wc_order( $arr )
		{
			// Get an instance of the WC_Order object
			$order = wc_get_order( get_the_id() );

			if( !empty($order) && !empty($order->get_items()) ) {
				// Loop through order items
				foreach ( $order->get_items() as $item_id => $item ) {
					$product_id = $item['variation_id'] != 0 ? $item['variation_id'] : $item['product_id'];
					$product_id = SlwWpmlHelper::object_id( $product_id );
					$product    = wc_get_product( $product_id );
					if( empty( $product ) ) continue;

					// Get item location terms
					$item_stock_location_terms = SlwStockAllocationHelper::getProductStockLocations($product_id, true, null);

					if( !empty($item_stock_location_terms) ) {
						// Loop through location terms
						foreach ( $item_stock_location_terms as $term ) {
							$arr[] = '_item_stock_updated_at_' . $term->term_id;
							$arr[] = '_stock_location';
							$arr[] = '_slw_notification_mail_output';
							$arr[] = 'stock_location_' . $term->term_id;
						}
					}

				}

				$arr[] = '_item_stock_locations_updated';
			}

			return $arr;
		}


		
		/**
		 * Adds stock location data to item formatted meta.
		 *
		 * @since 1.2.4
		 * @return array
		 */
		public function include_location_data_in_formatted_item_meta( $formatted_meta, $item )
		{

			$order_id = $item->get_order_id();
			$_slw_ts = get_post_meta($order_id, '_slw_ts', true);
			$receipt_in_progress = get_post_meta($order_id, '_slw_ep', true);
			
			$ts = date('His');
			if(!$_slw_ts){			
				wc_slw_order_update_post_meta($order_id, '_slw_ts', $ts);
			}
			if($_slw_ts && $_slw_ts!=$ts && !$receipt_in_progress){
				$receipt_in_progress = true;
				wc_slw_order_update_post_meta($order_id, '_slw_ep', $receipt_in_progress);
			}
			
			
			$proceed = ( isset($this->plugin_settings['include_location_data_in_formatted_item_meta']) && $this->plugin_settings['include_location_data_in_formatted_item_meta'] == 'yes' );
			
			$proceed = (
					
					($receipt_in_progress && is_checkout() && isset($this->plugin_settings['general_display_settings']) && isset($this->plugin_settings['general_display_settings']['order_received_page']) && $this->plugin_settings['general_display_settings']['order_received_page'] == 'on' )
				
				||
				
					(!$receipt_in_progress && isset($this->plugin_settings['general_display_settings']) && isset($this->plugin_settings['general_display_settings']['order_email']) && $this->plugin_settings['general_display_settings']['order_email'] == 'on' )
					
					
			
			);
			

			
			
			//TESTED FOR THE FOLLOWING PAGE
			//RECEIVED ORDER PAGE
			//ORDER EMAIL
				
			if( !empty($item) && $proceed) {
				
				$item_location_data = (is_object($item)?$item->get_meta('_slw_data'):array());

				if( !empty($item_location_data) ) {

					
					foreach( $item_location_data as $location_id => $data ) {
						$value = $data['location_name'].' (-'.$data['quantity_subtracted'].')';
						$formatted_meta[] = (object) array(
							'key' 			=> 'stock_location_' . $location_id,
							'display_key'	=> __('Location', 'stock-locations-for-woocommerce'),
							'value'			=> $value,
							'display_value'	=> '<p>'.$value.'</p>'
						);
					}

				}else{

					$location_id = (is_object($item)?$item->get_meta('_stock_location'):0);

					
					if(is_numeric($location_id) && $location_id>0){
						
						$product_id = ($item->get_variation_id()?$item->get_variation_id():$item->get_product_id());
						$stock_location = SlwStockAllocationHelper::getProductStockLocations( $product_id, true, $location_id );
						
						$value = '';
						
						if(is_object($stock_location) && property_exists($stock_location, 'name')){
	
							$value = $stock_location->name;
							
						}

						if($value){
							$formatted_meta[] = (object) array(
								'key' 			=> 'stock_location_' . $location_id,
								'display_key'	=> __('Location', 'stock-locations-for-woocommerce'),
								'value'			=> $value,
								'display_value'	=> '<p>'.$value.'</p>'
							);
							
							
						}
							
						
					}
					
				}
			}
			
			return $formatted_meta;
		}
 
		/**
		 * Adds stock location email address to WC new order email.
		 *
		 * @since 1.3.0
		 * @return array
		 */
		public function wc_new_order_email_copy_to_locations_email( $headers, $email_id, $order, $email = null )
		{
			if( $email_id == 'new_order' && !empty($order) ) {
				$emails = array();
				foreach( $order->get_items() as $item_id => $item ) {
					if( $item->get_type() == 'line_item' ) {
						$item_slw_data = $item->get_meta('_slw_data');
						if( !empty($item_slw_data) ) {
							foreach( $item_slw_data as $location_id => $location ) {
								$location_meta = SlwStockAllocationHelper::getLocationMeta( $location_id );
								if( !empty($location_meta) && isset($location_meta['slw_location_email']) && is_email($location_meta['slw_location_email']) ) {
									$location_term = get_term_by('id', $location_id, SlwLocationTaxonomy::$tax_singular_name);
									$emails[$location_id]['name'] = $location_term->name;
									$emails[$location_id]['email'] = $location_meta['slw_location_email'];
								}
							}
						}
					}
				}
				// add email addresses to headers
				if( !empty($emails) ) {
					foreach( $emails as $key => $value ) {
						$headers .= 'BCC: '.$value['name'].' <'.$value['email'].'>' . "\r\n";
					}
				}
			}
			return $headers; 
		}

		/**
		 * Restore locations stock on WC restore
		 *
		 * @since 1.3.3
		 * @return void
		 */
		 
		/**
		 * New orders allocate stock to items if required
		 *
		 * @param $item_id
		 * @param $item
		 * @param $order_id
		 */
		public function newOrderItemAllocateStock( $item_id, $item, $order_id )
		{
			//wc_slw_logger('debug', 'newOrderItemAllocateStock: '.'Yes #'.$order_id);
			
			// add exception to third party plugins
			$disallow = apply_filters( 'slw_disallow_third_party_allocate_order_item_stock', true );
			if( is_admin() && $disallow ) {
				return;
			}

			// This is not the correct product
			if( !($item instanceof \WC_Order_Item_Product) ) {
				return;
			}

			// Get product ID
			$productId  = $item->get_variation_id() != 0 ? $item->get_variation_id() : $item->get_product_id();
			$productId  = SlwWpmlHelper::object_id( $productId );
			
			// Get item quantity
			$itemQuantity = $item->get_quantity();
			
			// Check if customer selected a location
			$userLocationChoiceId = null;
			$userStockLocation = null;

			$userLocationChoiceId = $item->get_meta('_stock_location');


			if( !empty($userLocationChoiceId)) { //16/05/2022 //23/05/2024

				$userStockLocation = SlwStockAllocationHelper::get_product_stock_location($productId, $userLocationChoiceId);


				if( !empty($userStockLocation) ) {
					// get location meta
					$location_meta = SlwStockAllocationHelper::getLocationMeta($userLocationChoiceId);
					// check if location has auto allocation enabled
					if( isset($location_meta['slw_auto_allocate']) && $location_meta['slw_auto_allocate'] == 1 ) {
						
						
						
						if( $userStockLocation[$userLocationChoiceId]->quantity > $itemQuantity ) {
							$userStockLocation[$userLocationChoiceId]->allocated_quantity = $itemQuantity;
						} else {
							$itemQuantity = $itemQuantity - $userStockLocation[$userLocationChoiceId]->quantity;
							$userStockLocation[$userLocationChoiceId]->allocated_quantity = $userStockLocation[$userLocationChoiceId]->quantity;
						}
						
						
					} else {
						return; // user selected location doesn't have auto allocation enabled so finish here and let the admin choose from the order
					}
				}
			}

			//wc_slw_logger('debug', '$userStockLocation: ');
			//wc_slw_logger('debug', $userStockLocation);
			
				
			// Get product stock allocation locations if customer haven't select a location
			//if( is_null($userStockLocation) ) { //16/05/2022
				//$stockAllocation = SlwStockAllocationHelper::getStockAllocation($productId, $itemQuantity, 0, false, $userLocationChoiceId);
				
				
				if ($this->different_location_per_cart_item) { //28/09/2022 - bbceg
					$stockAllocation = SlwStockAllocationHelper::getStockAllocation($productId, $itemQuantity, 0, false, $userLocationChoiceId);
				}else {
					$userStockLocation = SlwStockAllocationHelper::get_product_stock_location($productId, $userLocationChoiceId);
					//error_log("Same location per cart item. Item Quantity: $itemQuantity, UserLocationChoice ID: $userLocationChoiceId, User stock location quantity = " . $userStockLocation[$userLocationChoiceId]->quantity);
					//wc_slw_logger('debug', '$userLocationChoiceId: '.$userLocationChoiceId);
					//wc_slw_logger('debug', '$userStockLocation: ');
					//wc_slw_logger('debug', $userStockLocation);
					
					if( isset($userStockLocation[$userLocationChoiceId]) && $userStockLocation[$userLocationChoiceId]->quantity > $itemQuantity ) {
						$userStockLocation[$userLocationChoiceId]->allocated_quantity = $itemQuantity;
					}
					else {
						//Not enough stock error? (Though shouldn't have reached this point)
						error_log("Not enough stock for product (ID: $productId) at stock location (ID: $userLocationChoiceId)");
					}
				}
			//}
			
			//wc_slw_logger('debug', '$stockAllocation: ');
			//wc_slw_logger('debug', $stockAllocation);
			
			// define stock allocation
			if( !is_null($userStockLocation) ) {
				// if user selected a location and has auto allocation enabled
				$stockAllocation = $userStockLocation;
				
				$stockAllocation_additional = SlwStockAllocationHelper::getStockAllocation($productId, $item->get_quantity(), 0, false);
				
				//wc_slw_logger('debug', '!is_null($userStockLocation): '.$itemQuantity);
				
				//wc_slw_logger('debug', '$stockAllocation_additional: ');
				//wc_slw_logger('debug', $stockAllocation_additional);
				
				if(!empty($stockAllocation)){
					
					$stockAllocation_qty = array();
					
					foreach($stockAllocation as $stockAllocation_iter){
						$stockAllocation_qty[] = $stockAllocation_iter->quantity;
					}
					
					if(array_sum($stockAllocation_qty)<$item->get_quantity()){
						$diff = ($item->get_quantity()-array_sum($stockAllocation_qty));
						if(!empty($stockAllocation_additional)){
							$filled = false;
							foreach($stockAllocation_additional as $stockAllocation_additional_id=>$stockAllocation_additional_data){
								
								//wc_slw_logger('debug', '$diff<$stockAllocation_additional_data->quantity');
								
								//wc_slw_logger('debug', $diff.' < '.$stockAllocation_additional_data->quantity.' (term_id: '.$stockAllocation_additional_data->term_id.')');
								
								if(!$filled && $diff<$stockAllocation_additional_data->quantity && !array_key_exists($stockAllocation_additional_data->term_id, $stockAllocation)){
									$stockAllocation[$stockAllocation_additional_data->term_id] = $stockAllocation_additional_data;
									$stockAllocation[$stockAllocation_additional_data->term_id]->allocated_quantity = $itemQuantity;
									$filled = true;
								}
							}
						}
					}
					
					//wc_slw_logger('debug', '$stockAllocation: ');
					//wc_slw_logger('debug', $stockAllocation);
				}
				
				
			} elseif( is_null($userStockLocation) && isset($stockAllocation) && is_array($stockAllocation) ) {
				// if user haven't selected a location define by available locations for this product
				$stockAllocation = $stockAllocation;
			} else {
				// finish here if we don't have stock allocation set
				return;
			}
			
			// If WC manage stock is enabled
			if ( $this->wc_manage_stock == 'yes' && !empty($this->wc_hold_stock_minutes) ) {
				// Allocations exist, disable WC hold stock
				add_filter( 'woocommerce_hold_stock_for_checkout', '__return_false' );
			}
			
			// Build simple location term to stock quantity allocation array
			$simpleLocationAllocations = array();
			foreach ($stockAllocation as $allocation) {
				if(property_exists($allocation, 'allocated_quantity')){
					$simpleLocationAllocations[$allocation->term_id] = $allocation->allocated_quantity;
				}else{
					//$simpleLocationAllocations[$allocation->term_id] = $allocation->quantity;
				}
			}
			
			//wc_slw_logger('debug', '<hr />');
			
			//wc_slw_logger('debug', '$stockAllocation: ');
			//wc_slw_logger('debug', $stockAllocation);
					
			//wc_slw_logger('debug', '$item->get_id(): '.$item->get_id().', $allocationType: '.$allocationType);
			// Allocate order item stock to locations
			
			
			SlwOrderItemHelper::allocateLocationStock( $item->get_id(), $simpleLocationAllocations, $allocationType = 'auto' );

		}		
		

	}

}
