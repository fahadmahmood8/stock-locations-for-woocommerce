<?php
/**
 * SLW Stock Locations Tab Class
 * @since 1.0.0
 */

namespace SLW\SRC\Classes;

use SLW\SRC\Helpers\SlwStockAllocationHelper;
use SLW\SRC\Helpers\SlwProductHelper;
use SLW\SRC\Helpers\SlwWpmlHelper;


if ( !defined( 'WPINC' ) ) {
	die;
}

if(!class_exists('SlwStockLocationsTab')) {

	class SlwStockLocationsTab
	{
		private $tab_stock_locations = SLW_PLUGIN_SLUG . '_tab_stock_locations';
		private $plugin_settings;

		/**
		 * Construct.
		 *
		 * @since 1.1.0
		 */
		public function __construct()
		{
			// get settings
			$this->plugin_settings = get_option( 'slw_settings' );

			add_filter('woocommerce_product_data_tabs', array($this, 'create_custom_stock_locations_tab_wc_product'), 10, 1); // Since WC 3.0.2
			add_action('woocommerce_product_data_panels', array($this, 'tab_content_stock_locations_wc_product'), 10, 1); // Since WC 3.0.2
			add_action('save_post', array($this, 'save_tab_data_stock_locations_wc_product_save'), 10, 3);
			add_action('do_meta_boxes', array($this, 'location_sidebar_meta_box'), 10, 3);

			// check setting
			if( isset($this->plugin_settings['delete_unused_product_locations_meta']) && $this->plugin_settings['delete_unused_product_locations_meta'] == 'yes' ) {
				// Action scheduler action
				add_action( 'init', array($this, 'schedule_action_to_delete_product_locations_meta') );
				add_action( 'slw_delete_unused_product_locations_meta', array($this, 'delete_product_meta_callback') );	
			}
		}

		/**
		 * Creates the Stock Locations tab in WC Product.
		 *
		 * @since 1.0.0
		 * @return array
		 */
		public function create_custom_stock_locations_tab_wc_product( $original_tabs )
		{
			// Define custom tabs
			$new_tab[$this->tab_stock_locations] = array(
				'label' 	=> __( 'Stock Locations', 'stock-locations-for-woocommerce' ),
				'target'    => $this->tab_stock_locations,
				'class'     => array( 'show_if_simple', 'show_if_variable' ),
			);

			// Define tab positions
			$insert_at_position = 4;
			$tabs = array_slice( $original_tabs, 0, $insert_at_position, true );
			$tabs = array_merge( $tabs, $new_tab );
			$tabs = array_merge( $tabs, array_slice( $original_tabs, $insert_at_position, null, true ) );

			return $tabs;
		}

		/**
		 * Add data to the Stock Locations tab in WC Product.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function tab_content_stock_locations_wc_product( $array )
		{
			global $wpdb;
			// Get the product ID
			$product_id = SlwWpmlHelper::object_id( get_the_ID() );

			// Get the product object
			$product = wc_get_product( $product_id );
			if( empty($product) ) return;

			// if product is variable
			if( $product_id && $product->is_type('variable') ) {
				// Get product variations
				//$product_variations_ids = $product->get_children();
				$product_variations_ids = $wpdb->get_results("SELECT ID AS variation_id FROM $wpdb->posts WHERE post_parent IN ($product_id) AND post_type='product_variation'");
				$product_variations = array();
				foreach( $product_variations_ids as $variation_obj ) {
					$variation_id = $variation_obj->variation_id;
					$product_variations[] = $product->get_available_variation( $variation_id );
				}
			}

			// Get product location terms
			$product_stock_location_terms = wp_get_post_terms($product_id, SlwLocationTaxonomy::get_tax_Names('singular'));

			// Define $postmeta variable as array type
			$postmeta = array();

			// Define $postmeta_variations variable as array type
			$postmeta_variations = array();

			// Populate the tab content
			echo '<div id="' . $this->tab_stock_locations . '" class="panel woocommerce_options_panel">';
			echo '<div id="' . $this->tab_stock_locations . '_notice">' . __('To be able to manage stock locations, please activate the <b>Stock Management</b> option under the <b>Inventory Tab</b>, and add a location to this product.', 'stock-locations-for-woocommerce') . '</div>';

			// WPML Lock on non default language products
			if( $product_id != get_the_ID() ) {
				printf(
					'<div id="' . $this->tab_stock_locations . '_notice">&#128274; %s</div></div>',
					__( 'Stock locations are locked for editing because WPML will copy its value from the original language.', 'stock-locations-for-woocommerce' )
				);
				return;
			}

			// Check if the product has terms
			if($product_stock_location_terms) {
				
				$backorder_status = $product->get_backorders() === 'no' ? __('No', 'stock-locations-for-woocommerce'):__('Yes', 'stock-locations-for-woocommerce');

				echo '<div id="' . $this->tab_stock_locations . '_wrapper" style="display:none;">';
				echo '<div id="' . $this->tab_stock_locations . '_title"><h4>#'.$product->get_id().' ('. $product->get_title() . ') <span class="backorder-status"><span class="backorder-'.strtolower($backorder_status).'">'.__('Backorder', 'stock-locations-for-woocommerce').': '.$backorder_status.'</span></span></h4></div>';

				// Loop throw terms
				foreach($product_stock_location_terms as $term) {
					
					$postmeta[] = $this->create_stock_location_input($product_id, $term);

				}
								
				$total_location_stock = 0;
				foreach ($postmeta as $entry) {
					$total_location_stock += isset($entry['stock']) ? (float) $entry['stock'] : 0;
				}



				if( $product->managing_stock() ) {
					echo '<div id="' . $this->tab_stock_locations . '_total"><u>' . __('Total Stock:', 'stock-locations-for-woocommerce') . ' <b>' . ($product->get_stock_quantity() + 0) . '</b></u></div>';
					echo '<hr>';
				}
				//pree($product->get_stock_quantity());pree($postmeta);
				// Convert $postmeta array values from string to int

				// Check if the total stock matches the sum of the locations stock, if not show warning message

				if( $product->get_stock_quantity() != $total_location_stock ) {
					echo '<div id="' . $this->tab_stock_locations . '_alert" style="display:none;">' . __('The total stock does not match the sum of the locations stock. Please update this product to fix it or use', 'stock-locations-for-woocommerce') .' <a href="'.admin_url('admin.php?page=slw-settings&tab=crons').'" target="_blank">'.__('cron jobs.', 'stock-locations-for-woocommerce').'</a>.</div>';
				}

				echo '</div>';

				// If product is variable but no active variations show message
				if( $product->is_type('variable') && empty($product_variations) ) {
					echo '<div id="' . $this->tab_stock_locations . '_notice_variations">' . __('To be able to manage stock locations for variations please create them and add a price to each one.', 'stock-locations-for-woocommerce') . '</div>';
				}

				// Check if product has variations
				if( isset($product_variations) && ( !empty($product_variations) || ($product_variations !== 0) ) ) {
					
					$hide_out_of_stock_items = get_option( 'woocommerce_hide_out_of_stock_items' );

					// Interate over variations
					foreach( $product_variations as $variation ) {
						
						

						$variation_id = $variation['variation_id'];
						
						$variation_obj = wc_get_product($variation_id);
						
						$backorder_status = $variation_obj->get_backorders() === 'no' ? __('No', 'stock-locations-for-woocommerce'):__('Yes', 'stock-locations-for-woocommerce');

						if( is_array($variation['attributes']) ) {
							$variation_attributes = implode(",", $variation['attributes']);
						} else {
							$variation_attributes = $variation['attributes'];
						}

						$variation_manage_stock = get_post_meta($variation_id, '_manage_stock', true);
						$variation_price = get_post_meta($variation_id, '_price', true);

						// Check if variation allow manage stock and has price
						if( ( $variation_manage_stock === 'yes' ) && ( !empty($variation_price) || !isset($variation_price) ) ) {
							echo '<div id="' . $this->tab_stock_locations . '_wrapper_variations">';
						} else {
							echo '<div id="' . $this->tab_stock_locations . '_notice_variations">' . sprintf( __('To be able to manage stock locations in <b>%1$s</b>, please add a <b>price</b> and activate the <b>Stock Management</b> under the variation settings.', 'stock-locations-for-woocommerce'), ucfirst($variation_attributes) ) . '</div>';
							echo '<div id="' . $this->tab_stock_locations . '_wrapper_variations" style="display:none;">';
						}

						echo '<div id="' . $this->tab_stock_locations . '_title"><h4>#'.$variation_id.' ('. ucfirst($variation_attributes) . ') ('. $product->get_title() . ') <span class="backorder-status"><span class="backorder-'.strtolower($backorder_status).'">'.__('Hide outofstock', 'stock-locations-for-woocommerce').': '.$hide_out_of_stock_items.' / '.__('Backorder', 'stock-locations-for-woocommerce').': '.$backorder_status.'</span></span></h4></div>';

						// Loop throw terms
						foreach($product_stock_location_terms as $term) {

							// Create the inputs for the variations
							$postmeta_variations[] = $this->create_stock_location_input($variation_id, $term);

						}
						
						$total_location_stock = 0;
						foreach ($postmeta_variations as $entry) {
							$total_location_stock += isset($entry['stock']) ? (float) $entry['stock'] : 0;
						}


						// Get Variation Object
						//$variation_obj = wc_get_product($variation_id);


						if( $variation_obj->managing_stock() ) {
							echo '<div id="' . $this->tab_stock_locations . '_total"><u>' . __('Total Stock:', 'stock-locations-for-woocommerce') . ' <b>' . ($variation_obj->get_stock_quantity() + 0) . '</b></u></div>';
							echo '<hr>';
						}

		
						
						echo '</div>';

					}

				}

			} else {
				echo '<div id="' . $this->tab_stock_locations . '_alert">' . __('You need to add a stock location to this product.', 'stock-locations-for-woocommerce') . '</div>';
			}

			echo '</div>';

		}

		/**
		 * Create Stock Locations inputs in WC Product.
		 *
		 * @since 1.0.0
		 * @return array
		 */
		private function create_stock_location_input( $id, $term ) {
			// Ensure correct ID for current language if WPML is active
			$id = SlwWpmlHelper::object_id( $id );
			
			// Fetch meta values
			$stock_qty   = get_post_meta( $id, '_stock_at_' . $term->term_id, true );
			$stock_price = get_post_meta( $id, '_stock_location_price_' . $term->term_id, true );
			
			// Stock Quantity input
			woocommerce_wp_text_input( array(
			'id'            => '_' . SLW_PLUGIN_SLUG . $id . '_stock_location_' . $term->term_id,
			'label'         => '<b>' . esc_html( $term->name ) . '</b><br />' . __( 'Stock Qty.', 'stock-locations-for-woocommerce' ),
			'description'   => __( 'Enter the stock amount for this location.', 'stock-locations-for-woocommerce' ),
			'desc_tip'      => true,
			'class'         => 'woocommerce',
			'type'          => 'number',
			'data_type'     => 'stock',
			'value'         => $stock_qty,
			'wrapper_class' => 'stock_location_qty',
			) );
			
			// Stock Price input
			woocommerce_wp_text_input( array(
			'id'                => '_' . SLW_PLUGIN_SLUG . $id . '_stock_location_price_' . $term->term_id,
			'label'             => '<br />' . __( 'Stock Price', 'stock-locations-for-woocommerce' ),
			'description'       => __( 'Enter the price for the stock from this location.', 'stock-locations-for-woocommerce' ),
			'desc_tip'          => true,
			'class'             => 'woocommerce',
			'type'              => 'number',
			'data_type'         => 'decimal',
			'value'             => $stock_price,
			'wrapper_class'     => 'stock_location_price price-' . esc_attr( $stock_price ),
			'custom_attributes' => array(
				'step' => '0.01',
				'min'  => '0',
			),
			) );
			
			// Determine if location is enabled and stock is not an array
			$location_enabled = get_term_meta( $term->term_id, 'slw_location_status', true );
			
			if ( ! is_array( $stock_qty ) && $location_enabled ) {
				return $stock_qty;
			}
			
			return 0;
		}

		private function create_stock_location_input($id, $term) {
			$id = SlwWpmlHelper::object_id($id);
		
			$_stock_at = get_post_meta($id, '_stock_at_' . $term->term_id, true);
			$_stock_location_price = get_post_meta($id, '_stock_location_price_' . $term->term_id, true);
		
			// Render inputs
			woocommerce_wp_text_input([
				'id'            => '_' . SLW_PLUGIN_SLUG . $id . '_stock_location_' . $term->term_id,
				'label'         => '<b>' . $term->name . '</b><br />' . __('Stock Qty.', 'stock-locations-for-woocommerce'),
				'description'   => __('Enter the stock amount for this location.', 'stock-locations-for-woocommerce'),
				'desc_tip'      => true,
				'class'         => 'woocommerce',
				'type'          => 'number',
				'data_type'     => 'stock',
				'value'         => $_stock_at,
				'wrapper_class' => 'stock_location_qty',
			]);
		
			woocommerce_wp_text_input([
				'id'            => '_' . SLW_PLUGIN_SLUG . $id . '_stock_location_price_' . $term->term_id,
				'label'         => '<br />' . __('Stock Price', 'stock-locations-for-woocommerce'),
				'description'   => __('Enter the price for the stock from this location.', 'stock-locations-for-woocommerce'),
				'desc_tip'      => true,
				'class'         => 'woocommerce',
				'type'          => 'number',
				'data_type'     => 'decimal',
				'custom_attributes' => ['step' => '0.01', 'min' => '0'],
				'value'         => $_stock_location_price,
				'wrapper_class' => 'stock_location_price price-' . $_stock_location_price,
			]);
		
			return [
				'location_term_id' => $term->term_id,
				'stock'            => $_stock_at,
				'price'            => $_stock_location_price
			];
		}


		/**
		 * Saves data from custom Stock Locations tab upon WC Product save.
		 *
		 * @since 1.0.0
		 * @return int|void
		 */
		public static function save_tab_data_stock_locations_wc_product_save( $post_id, $post, $update, $force=false )
		{
			global $wpdb;
			
			$stock_value = 0;
			
			if ( !$force && defined( 'DOING_AJAX' ) && DOING_AJAX )
				return $post_id;

			if ( !$force && defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
				return $post_id;

			if ( !$force && ! current_user_can( 'edit_product', $post_id ))
				return $post_id;
			


			// WPML
			$post_id = SlwWpmlHelper::object_id( $post_id );
			//pree($post_id);exit;
			// Get product object
			$product = wc_get_product( $post_id );
			
			
			if( empty($product) ) return;
			
			$product_id = $product->get_id();

			$product_variations = array();
			
			$product_variations_stock = array();
			
			// If product is type variable
			if( $product_id && is_a( $product, 'WC_Product' ) ){
				if($product->is_type('variable')) {
					// Get product variations
					//$product_variations_ids = $product->get_children();
					$product_variations_ids = $wpdb->get_results("SELECT ID AS variation_id FROM $wpdb->posts WHERE post_parent IN ($product_id) AND post_type='product_variation'");
					
					//pre($product_variations_ids);
					
					if(is_array($product_variations_ids)){
						foreach( $product_variations_ids as $variation_obj ) {
							//pre($variation_obj);
							
							$variation_id = $variation_obj->variation_id;
							$variation = wc_get_product($variation_id);
							$variation_arr_data = $product->get_available_variation( $variation_id );
							$product_variations[] = $variation_arr_data;
							//if($variation->get_stock_quantity()){
								$product_variations_stock[$variation_id] = $variation->get_stock_quantity();
							//}
						}
						
						//pre($product_variations);
						//pre($product_variations_stock);
					}
				}elseif($product->is_type('simple')){
					
					
				}
			}
			
			//pree($product_variations_stock);exit;
			
			// Product location terms
			$product_stock_location_terms = wp_get_post_terms($post_id, SlwLocationTaxonomy::get_tax_Names('singular'));
			
			//pre($product_stock_location_terms);
			
			// Count how many terms exist for this product
			if( empty($product_stock_location_terms) ){
				$terms_total = 0;
			} else{
				$terms_total = count($product_stock_location_terms);
			}
			
			// On product update
			if( $update ){

				// If has terms
				if( $terms_total>0 ) {
					//pree($product);
					//pree($product_stock_location_terms);pree($terms_total);pree($force);exit;
					//pree($post_id);
					$stock_value = self::update_product_stock($product, $product_stock_location_terms, $terms_total, $force); //IT WILL NOT UPDATE THE MAIN VARIABLE PRODUCT BECAUSE IT HAS PARENT=0 AND ITS VARIABLE
					
					//pree($stock_value);exit;

					
					$master_stock_value = 0;

					// Check if product has variations
					if( is_array($product_variations_stock) && !empty($product_variations_stock) ) {
						
						// Interate over variations
						foreach( $product_variations_stock as $item_id=>$stock_value ) {
							
							//pre($item_id.' => '.$stock_value);

							//$item_id = $item['variation_id'];
							
							$stock_value = self::update_product_stock($item_id, $product_stock_location_terms, $terms_total, $force);
							
							//slw_update_product_stock_status($item_id, $stock_value);
							
							if($stock_value>0){
								$master_stock_value += $stock_value;
							}

						}
						
						$stock_value = $master_stock_value;
						
						//pre('$master_stock_value: '.$master_stock_value);

					}else{
						if($stock_value>0){
							$master_stock_value = $stock_value;
						}
					}
					
					//pree($master_stock_value);exit;
					//pre('$post_id: '.$post_id.', $master_stock_value: '.$master_stock_value);
					
					

					slw_update_product_stock_status($post_id, $stock_value);
					

				}
			}
			//pre($stock_value);
			return $stock_value;
		}



		/**
		 * Deletes inactive stock locations meta from product on Action Scheduler event
		 *
		 * @since 1.2.3
		 * @return void
		 */
		public function delete_product_meta_callback()
		{
			// args
			$args = array(
				'post_type' 		=> 'product',
				'post_status'		=> 'published',
				'meta_query' 		=> array(
					array(
						'key'     => '_manage_stock',
						'value'   => 'yes',
						'compare' => 'LIKE',
					),
				),
				'posts_per_page'	=> -1
			);

			// query
			$query = new \WP_Query($args);
			if ( empty($query) ) return;

			// get posts
			$posts = $query->get_posts();

			// iterate over posts
			foreach ( $posts as $post ) { if(!is_object($post)){ continue; }
				$post_id = $post->ID;

				// get post location terms
				$product_stock_location_terms = SlwStockAllocationHelper::getProductStockLocations($post_id);
				// save location term IDs to array
				$location_term_ids = array();
				$location_terms_stock = (int) '';
				foreach ( $product_stock_location_terms as $term ) {
					$location_term_ids[] = $term->term_id;
					$location_terms_stock += (int) $term->quantity;
				}

				// get post meta
				$postmeta = get_post_meta($post_id);
				// iterate over post meta
				foreach ( $postmeta as $key => $value ) {
					if (strpos($key, '_stock_at_') === 0) {
						$term_id = (int) str_replace('_stock_at_', '', $key);
						// check if post meta exists on terms array
						if ( ! in_array($term_id, $location_term_ids) ) {
							// don't exist, delete post meta
							delete_post_meta($post_id, '_stock_at_'.$term_id);
						}
					}
				}

			}
			
			// Restore original Post Data
			wp_reset_postdata();
		}

		/**
		 * Schedules action at midnight
		 *
		 * @since 1.2.3
		 * @return void
		 */
		public function schedule_action_to_delete_product_locations_meta()
		{
			if( !function_exists('as_schedule_recurring_action') ) return;

			if ( false === as_next_scheduled_action( 'slw_delete_unused_product_locations_meta' ) ) {
				as_schedule_recurring_action( strtotime( 'tomorrow' ), DAY_IN_SECONDS, 'slw_delete_unused_product_locations_meta' );
			}
		}

		/**
		 * Check if product is in the default WPML language, if not remove the location meta box in product sidebar
		 *
		 * @since 1.5.0
		 * @return void
		 */
		public function location_sidebar_meta_box( $post_type, $priority, $post )
		{
			
			if( ! empty($post) && is_object($post) && $post_type == 'product' ) {
				$product_id = SlwWpmlHelper::object_id( $post->ID );
				if( $product_id != $post->ID ) {
					remove_meta_box( 'locationdiv', $post_type, 'side' );
				}
			}
		}
		
		/**

		 *
		 * @since 1.0.0
		 * @return void
		 */
		public static function update_product_stock( $id, $product_stock_location_terms, $terms_total, $force_main_product_update=false )
		{
			$stock_ret = 0;
			// WPML
			if(is_numeric($id)){
				$id = SlwWpmlHelper::object_id( $id );
				$product = wc_get_product($id);
			}
			if(is_object($id)){
				$product = $id;
				$id = $product->get_id();	
				
			}
			
			
			
			
			if( !get_post_meta($id, '_manage_stock', true) ) { // 21/01/2024 @mrmelson
				return $stock_ret;
			}


			//pree($_POST);exit;
			
			// Grab stock amount from all terms
			$product_terms_stock = array();

			// Grab input amounts
			$input_amounts = array();

			// Define counter
			$counter = 0;
			$parent_product_id = $id;
			
			
			
			//pree($product_stock_location_terms);exit;
			
			if(is_array($product_stock_location_terms) && !empty($product_stock_location_terms)){
			// Loop through terms
				foreach ( $product_stock_location_terms as $term ) {
					
					
					//pree($product->get_type().' | '.$product->get_parent_id());exit;
					if($product->get_type()=='variable' && $product->get_parent_id()==0){ continue; }
					
					$parent_product_id = $product->get_parent_id();
					
					$stock_input_id = '_' . SLW_PLUGIN_SLUG . $id . '_stock_location_' . $term->term_id;
					$price_input_id = '_' . SLW_PLUGIN_SLUG . $id . '_stock_location_price_' . $term->term_id;
					$slw_location_status = get_term_meta($term->term_id, 'slw_location_status', true);
					
					//pree($_POST);pree($stock_input_id.' | '.$price_input_id);//exit;
					if($product->get_parent_id()){
						//pree('$stock_input_id: '.$stock_input_id.' - '.$_POST[$stock_input_id]);exit;//pree($_POST);
					}
									
					if( !empty($_POST) && isset($_POST[$stock_input_id])) {
	
						// Initiate counter
						$counter++;
						
						if(!$slw_location_status){ continue; }
						// Save input amounts to array					
						
						$input_amount = sanitize_slw_data($_POST[$stock_input_id]);
						
						if($product->get_parent_id()){
							//pree('$input_amount: '.$input_amount);exit;
						}
						
						if($input_amount>=0){
							$input_amounts[] = $input_amount;
						}else{
							continue;
						}
						
	
						// Check if input is empty
						if(strlen($_POST[$stock_input_id]) === '') {
							// Show admin notice
							SlwAdminNotice::displayError(__('An error occurred. Some field was empty.', 'stock-locations-for-woocommerce'));
	
						} else {
							
							
	
							$stock_location_term_input = sanitize_slw_data($_POST[$stock_input_id]);
							$stock_location_price_term_input = sanitize_slw_data($_POST[$price_input_id]);
							
							
							
							// Get post meta
							$postmeta_stock_at_term = get_post_meta($id, '_stock_at_' . $term->term_id, true);
							
							if($product->get_parent_id()){
								//pree($stock_location_term_input.' - '.$postmeta_stock_at_term);exit;
							}
							
	
	
							// Check if the $_POST value is the same as the postmeta, if not update the postmeta
							if( $stock_location_term_input !== $postmeta_stock_at_term && $stock_location_term_input!='' && $stock_location_term_input>=0) {
	
								// Update the post meta
								update_post_meta( $id, '_stock_at_' . $term->term_id, $stock_location_term_input );
								
								//pree('update_post_meta( '.$id.', _stock_at_'.$term->term_id.', '.$stock_location_term_input.' );');
							}
							
							if($product->get_parent_id()){
								//pree('$id: '.$id.' - '.$counter.' === '.$terms_total);
								//pree(get_post_meta($id, '_stock_at_' . $term->term_id, true));
								//exit;
							}
							
							$postmeta_stock_price_at_term = get_post_meta($id, '_stock_location_price_' . $term->term_id, true);
							
							
							
							if( $stock_location_price_term_input !== $postmeta_stock_price_at_term ) {
								
								update_post_meta( $id, '_stock_location_price_' . $term->term_id, $stock_location_price_term_input );
								
							}
	
							// Update stock when reach the last term

							
	
						}
						continue;
	
					}else{
						
					}
					
					
					$slw_location_status = get_term_meta($term->term_id, 'slw_location_status', true);			
					
					if($slw_location_status){
						// Get post meta
						$postmeta_stock_at_term = get_post_meta($id, '_stock_at_' . $term->term_id, true);
						
						// Pass terms stock to variable
						if( $postmeta_stock_at_term ) {
							$product_terms_stock[] = $postmeta_stock_at_term;
						}
					}
					
	
				}
				
				if($counter === $terms_total) {											
					
					$stock_ret = array_sum($input_amounts);
					
					//pree('$id: '.$id.', $stock_ret: '.$stock_ret.', $parent_product_id: '.$parent_product_id);exit;
					
					if($stock_ret>0){
						update_post_meta($parent_product_id, '_stock_status', 'instock');
					}else{
						update_post_meta($parent_product_id, '_stock_status', 'outofstock');
					}								
					slw_update_product_stock_status( $id, $stock_ret );
					
				}
			}
			//pree($product_terms_stock);pree($stock_ret);exit;
			if($stock_ret){				
				return $stock_ret;
			}else{
				//pree($product_terms_stock);
				// Check if stock in terms exist
				if( is_array( $product_terms_stock ) ) {
					$product_terms_stock = array_sum($product_terms_stock);
					// update stock status
					if(!empty($product_terms_stock)){
						$updated_wc_stock_status = SlwProductHelper::update_wc_stock_status( $product, $product_terms_stock, $force_main_product_update );//array_sum($input_amounts)
					}
				}
				
				return $product_terms_stock;
				
			}

		}		

	}
	
	

}
