<?php if ( ! defined( 'ABSPATH' ) ){ exit; }else{ clearstatcache(); }

		
	if(!function_exists('wc_slw_edit_stocks')){
		function wc_slw_edit_stocks($slw_order_id, $item_id){
			
			$str = '<a class="slw_edit_stocks" data-order="'.$slw_order_id.'" data-id="'.$item_id.'" title="'.__('Click here to edit stock values', 'stock-locations-for-woocommerce').'"></a>';
			
			
			$str = apply_filters('slw_edit_stocks_filter', $str, $slw_order_id, $item_id);
			
			return $str;

		}
	}
	
	add_action('wp_ajax_wc_slw_edit_stock_values', 'wc_slw_edit_stock_values_callback');
	
	function wc_slw_edit_stock_values_callback(){
		if(!empty($_POST) && isset($_POST['slw_nonce_field'])){
			if (! isset( $_POST['slw_nonce_field'] ) || ! wp_verify_nonce( $_POST['slw_nonce_field'], 'slw_nonce' )	) {	
				echo '0';		
			} else {
				$posted = sanitize_slw_data($_POST);
				$order_id = $posted['order_id'];
				$item_id = $posted['item_id'];
				
				global $wpdb;
				
				$restore_query = $wpdb->get_results("SELECT meta_key, meta_value FROM ".$wpdb->prefix."woocommerce_order_itemmeta WHERE order_item_id='$item_id' AND meta_key LIKE '_item_stock_updated_at_%'");
				$wpdb->query("UPDATE ".$wpdb->prefix."woocommerce_order_itemmeta SET meta_value=0 WHERE meta_key LIKE '_item_stock_updated_at_%' AND order_item_id='$item_id'");
				
				if(!empty($restore_query)){
					$location_stocks = array();
					
					foreach($restore_query as $restore_iter){
						$restore_value = $restore_iter->meta_value;
						$restore_location_id = str_replace('_item_stock_updated_at_', '', $restore_iter->meta_key);
						
						$location_stocks[$restore_location_id] = $restore_value;
					}
					
					$order = wc_get_order($order_id);
					$order_items = $order->get_items();
				
					if(array_key_exists($item_id, $order_items)){
						$order_item = $order_items[$item_id];
						
						$product_id = ($order_item->get_variation_id()?$order_item->get_variation_id():$order_item->get_product_id());
						
						$location_stock_values = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."postmeta WHERE post_id='$product_id' AND meta_key LIKE '_stock_at_%'");
						
						if(!empty($location_stock_values)){
							$location_stock_value_arr = array();
							foreach($location_stock_values as $location_stock_value){
								
								$stock_location_id = str_replace('_stock_at_', '', $location_stock_value->meta_key);
								$location_stock_value_arr[$stock_location_id] = $location_stock_value->meta_value; 
							
							}
							
							if(!empty($location_stock_value_arr)){
								foreach($location_stock_value_arr as $location_id=>$location_stock){
									
									if(array_key_exists($location_id, $location_stocks)){
										$updated_stock_value = $location_stocks[$location_id]+$location_stock;
										
										//pree($location_stocks[$location_id].' + '.$location_stock.' = '.$updated_stock_value);
										$wpdb->query("UPDATE ".$wpdb->prefix."postmeta SET meta_value=".$updated_stock_value." WHERE meta_key='_stock_at_".$location_id."' AND post_id='$product_id'");
									}
								}
							}
							//pree($location_stock_value_arr);
							//pree($location_stocks);
							
						}
					}
					
				}
				
				//exit;
				
				$wpdb->query("DELETE FROM ".$wpdb->prefix."woocommerce_order_itemmeta WHERE meta_key='_item_stock_locations_updated' AND order_item_id='$item_id'");
				
				slw_update_products($product_id, false, 'update-stock');
			}
		}

		wp_die();
	}

	if(!function_exists('wc_slw_logger_extended')){
		function wc_slw_logger_extended($SlwOrderItem=array()){
			
			pree('LOGGER EXTENDED');	
			if(array_key_exists('order_id', $_GET)){
				$order = wc_get_order($_GET['order_id']);
				
				if(!empty($order)){
					pree(is_object($order)?'ORDER #'.$order->get_order_number():'');
					//$SlwOrderItem->restore_order_items_locations_stock($order);
					foreach($order->get_items() as $key=>$data){

						pree($data->get_id());
						
					}
				}
			}
			if(array_key_exists('product_id', $_GET)){
				//$productStockLocations = SlwStockAllocationHelper::sortLocationsByPriority(SlwStockAllocationHelper::getProductStockLocations($_GET['product_id']));

				$stockAllocation = SlwStockAllocationHelper::getStockAllocation($_GET['product_id'], 3);
				pree($stockAllocation);
			}
			
			
			
		}
	}
	
	
	add_action( 'woocommerce_before_calculate_totals', 'wc_slw_update_price' );
	//add_filter('woocommerce_product_get_price', 'wc_slw_display_stock_price', 10, 2);
		
	
	function slw_change_product_price_display( $price, $product ) {
		global $slw_plugin_settings, $wc_slw_pro;
			
		
		$product_stock_price_status = array_key_exists('product_stock_price_status', $slw_plugin_settings);
		
		$term_id = (is_archive()?get_queried_object_id():0);
		
		if($product_stock_price_status){
			$product_id = 0;
			if(is_object($product)){
				$product_id = $product->get_id();
			}else{
				if(is_array($product)){
					$product_id = array_key_exists('product_id', $product)?$product['product_id']:0;
					$variation_id = array_key_exists('variation_id', $product)?$product['variation_id']:0;
					$product_id = ($variation_id?$variation_id:$product_id);
				}
	
			}
	
			if($product_id){
				$_stock_location_price = get_post_meta($product_id, '_stock_location_price_'.$term_id, true);
				$price = ($_stock_location_price?wc_price($_stock_location_price):$price);
			}
		}else{
		}

		return $price;
	}
	add_filter( 'woocommerce_get_price_html', 'slw_change_product_price_display', 10, 2 );
	add_filter( 'woocommerce_cart_item_price', 'slw_change_product_price_display', 10, 2 );	
	
	
	if(!function_exists('wc_slw_display_stock_price')){
		function wc_slw_display_stock_price($price, $product) {
			global $post, $blog_id;		
			if(is_object($post) && $post->post_type=='product' && $post->ID==$product->get_id()){			
				
			}
			return $price;
		}	
	}
	
	if(!function_exists('wc_slw_update_price')){
		function wc_slw_update_price( $cart_object ) {
			global $slw_plugin_settings, $wc_slw_pro;
			
			$cart_items = $cart_object->cart_contents;
			
			if ( ! empty( $cart_items ) ) {		
				$product_stock_price_status = array_key_exists('product_stock_price_status', $slw_plugin_settings);
				if($product_stock_price_status){
					foreach ( $cart_items as $key => $value ) {
						$stock_location = (array_key_exists('stock_location', $value)?$value['stock_location']:0);

						$_stock_location_price = '_stock_location_price_'.$stock_location;
						$_stock_location_price = get_post_meta($value['data']->get_id(), $_stock_location_price, true);			

						if($_stock_location_price && $value['data']->get_id()){
							$value['data']->set_price( $_stock_location_price );
						}
					}
				}
			}
		}	
	}
	
	function slw_archive_per_page( $query ) {
		$term_id = (is_archive()?get_queried_object_id():0);
		if (!is_admin() && $query->is_main_query() && ($term_id && !is_shop() && (is_tax( 'location') || is_post_type_archive('product'))) ) {
			
	        $query->set( 'meta_query', array(
											'relation'    => 'AND',
										array(
											'key'   => '_stock_at_'.$term_id,
											'value'     => 0,
											'compare'   => '>',
										  )
										) );
	
		}
	}
	//add_filter( 'pre_get_posts', 'slw_archive_per_page', 9999 );
	
	
	function slw_woocommerce_loop_add_to_cart_link( $html, $product ) { //return $html;
		
		global $slw_plugin_settings, $wc_slw_pro, $wpdb, $wp_query;
		
		$term_id = 0;
		$product_id = 0;
		$stock_locations = array();
		$is_location_tax = false;
		$is_category = false;
		//$html = '';
		

		
		if(is_archive()){
			
			$category_pages_frontend = (isset($slw_plugin_settings['general_display_settings']) && isset($slw_plugin_settings['general_display_settings']['category_pages_frontend']) && $slw_plugin_settings['general_display_settings']['category_pages_frontend'] == 'on' );
			
			$queried_object = get_queried_object();
			$term_id = ((is_object($queried_object) && isset($queried_object->term_id))?$queried_object->term_id:0);
			
			
			if(!$term_id){
				$taxonomy = array_key_exists('taxonomy', $wp_query->query_vars)?$wp_query->query_vars['taxonomy']:'';
				switch($taxonomy){
					case 'location':
					
						$term = array_key_exists('term', $wp_query->query_vars)?$wp_query->query_vars['term']:'';
						
						if($term){
							$term_obj = get_term_by( 'slug', $term, $taxonomy );
							if(is_object($term_obj)){
								$term_id = $term_obj->term_id;
							}
							
						}

					break;
					
				}
			}
			
			
			$product_id = $product->get_id();
			$is_category = (is_object($queried_object) && isset($queried_object->taxonomy) && $queried_object->taxonomy=='product_cat');
			if($is_category && !$category_pages_frontend){ return $html; }
			$is_location = (is_object($queried_object) && isset($queried_object->taxonomy) && $queried_object->taxonomy=='location');
			if($is_location){ 
				$is_location_tax = true;
			}else{
				if(empty($stock_locations)){
					
					$everything_stock_status_to_instock = array_key_exists('everything_stock_status_to_instock', $slw_plugin_settings);
					
					$stock_locations = \SLW\SRC\Helpers\SlwFrontendHelper::get_all_product_stock_locations_for_selection( $product_id, $everything_stock_status_to_instock );
					
				}
			}
		}
		
		if($term_id){
		
			
			$product_stock_price_status = array_key_exists('product_stock_price_status', $slw_plugin_settings);				
			$show_in_product_page = array_key_exists('show_in_product_page', $slw_plugin_settings);
			$product_type = $product->get_type();
			
			
			switch($product_type){
				case 'simple':
					
					if(!empty($stock_locations) && $is_category){
						$location_select_input_type = 'select_simple';
						
						switch($show_in_product_page){
							case 'yes_radio':
								$location_select_input_type = 'radio_simple';
							break;
						}			
						
						$location_select_input = \SLW\SRC\Classes\Frontend\SlwFrontendProduct::location_select_input($location_select_input_type, $product_id, $stock_locations, $html);
						
						$html = $location_select_input;
						
					}else{
						$_stock_at = get_post_meta($product_id, '_stock_at_'.$term_id, true);
						$_stock_at = ($_stock_at>0?$_stock_at:0);
					
						$html = ($_stock_at?str_replace(array('add-to-cart=', 'add_to_cart_button'), array('stock-location='.$term_id.'&add-to-cart=', 'add-to-cart-button'), $html):'');
					}
					
				break;
				case 'variable':				
					
					//$product = (is_object($product)?$product:wc_get_product($product_id));
					//$variations = $product->get_available_variations();
					
					
					if($product_id && $is_location_tax){
						
						$variations = $wpdb->get_results("SELECT ID AS variation_id FROM $wpdb->posts WHERE post_parent IN ($product_id) AND post_type='product_variation'", ARRAY_A);
						
						$html_extended = '';
						
						if(!empty($variations)){
							$html_extended .= '<select id="slw-product-'.$product_id.'" class="slw-shop-item">';
							$html_extended .= '<option value="">'.__('Select', 'stock-locations-for-woocommerce').'</option>';
							$html_extended_inner = '';
	
							foreach($variations as $variation_data){
								$variation_id = $variation_data['variation_id'];
								
								$_stock_at = get_post_meta($variation_id, '_stock_at_'.$term_id, true);
								$_stock_at = ($_stock_at>0?$_stock_at:0);

								if(!$_stock_at){ continue; }
														
								//$attributes = $variation_data['attributes'];
								$attrib_query = "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id IN ($variation_id)";
								$attributes = $wpdb->get_results($attrib_query);
								
								
								
								
								//$attributes = $variation_data->get_attributes();
								//pree($attributes);
								
								$label = array();
								if(!empty($attributes)){
									foreach($attributes as $attribute){
										$label[] = str_replace('-', ' ', $attribute->meta_value);
									}
									$label = array_filter($label);
									$label = array_map('ucwords', $label);
								}
								$price = '';
								if($product_stock_price_status){
									$_stock_location_price = get_post_meta($variation_id, '_stock_location_price_'.$term_id, true);
									$variation = wc_get_product($variation_id);
									$price = $variation->get_price();
									$price = ($_stock_location_price?($_stock_location_price):($price));
								}else{
								}
								$html_extended_inner .= '<option data-price="'.trim($price).'" value="'.$variation_id.'">'.implode(', ', $label).'</option>';
							}
							if($html_extended_inner){
								$html_extended = $html_extended.$html_extended_inner.'</select>';
							}else{
								$html_extended = '';
							}
							
						}					
						
						$html = ($html_extended?$html_extended.str_replace('class="', 'class="slw-variable-btn ', $html):$html);
						
						
					}
					
				break;
			}
					
			
		}
		return $html;
	}
	
	add_filter( 'woocommerce_loop_add_to_cart_link', 'slw_woocommerce_loop_add_to_cart_link', 10, 2 );
	
	function slw_woocommerce_product_add_to_cart_text($html, $product){ 
		
		global $slw_plugin_settings;
		
		$category_pages_frontend = (isset($slw_plugin_settings['general_display_settings']) && isset($slw_plugin_settings['general_display_settings']['category_pages_frontend']) && $slw_plugin_settings['general_display_settings']['category_pages_frontend'] == 'on' );
		
		$is_category = false;
		
		if(is_archive()){
			$queried_object = get_queried_object();
			$is_category = (is_object($queried_object) && isset($queried_object->taxonomy) && $queried_object->taxonomy=='product_cat');
		}
		
		if($is_category && !$category_pages_frontend){ return $html; }
		
		$product_id = $product->get_id();
		$everything_stock_status_to_instock = array_key_exists('everything_stock_status_to_instock', $slw_plugin_settings);
		$default_location = array_key_exists('default_location_in_frontend_selection', $slw_plugin_settings) ? get_post_meta( $product_id, '_slw_default_location', true ) : 0;			
		$stock_locations = \SLW\SRC\Helpers\SlwFrontendHelper::get_all_product_stock_locations_for_selection( $product_id, $everything_stock_status_to_instock );

		$stock_locations_available = 0;
		if(!empty($stock_locations)){
			foreach( $stock_locations as $id => $location ) {
				
				if( $location['quantity'] < 1 && $location['allow_backorder'] != 1 && !$everything_stock_status_to_instock) {
					
				}else{
					$stock_locations_available++;
				}
			}		
		}
		
		switch($product->get_type()){
			case 'simple':
			break;
			case 'variable':
				$html = __('Add to cart', 'stock-locations-for-woocommerce');
			break;		
		}
		
		$html = ($stock_locations_available>1 && $is_category?__('Choose the stock', 'stock-locations-for-woocommerce'):$html);
		return $html;
	}
	
	add_filter( 'woocommerce_product_add_to_cart_text', 'slw_woocommerce_product_add_to_cart_text', 10, 2 );
	
	//add_filter( 'woocommerce_loop_product_link', 'slw_change_product_permalink_shop', 10, 2 );
	 
	function slw_change_product_permalink_shop( $link, $product ) {
		$product_id = $product->get_id();
		$term_id = (is_archive()?get_queried_object_id():0);

		
		switch($product->get_type()){
			case 'simple':
				//$link = wc_get_cart_url().'?add-to-cart='.$product_id.'&location='.$term_id;			
				$link = '?stock-location='.$term_id.'&add-to-cart='.$product_id;//get_term_link($term_id).
			break;
		}
		return $link;
	}
	


	function slw_get_products_by_location_term_id($location_term_id) {
		$product_ids = [];
	
		// Query all products and product variations with the given location term
		$args = [
			'post_type'      => ['product', 'product_variation'],
			'posts_per_page' => -1,
			'tax_query'      => [
				[
					'taxonomy' => 'location',
					'field'    => 'term_id',
					'terms'    => $location_term_id,
				],
			],
			'fields' => 'ids',
		];
	
		$query = new WP_Query($args);
	
		if ($query->have_posts()) {
			$product_ids = $query->posts;
		}
	
		wp_reset_postdata();
	
		return $product_ids;
	}

	add_action('wp_loaded', 'slw_get_cart');
	function slw_get_cart(){
		global $stock_location_selected_warning, $woocommerce;
		
		if(isset($_GET['slw-cart']) && $_GET['slw-cart']=='empty'){			
		    $woocommerce->cart->empty_cart();	
			$woocommerce->session->set('stock_location_selected', 0);
			wp_redirect(wc_get_cart_url());exit;
		}
		
		if(isset($_GET['slw-cart']) && $_GET['slw-cart']=='update-stock'){	
			//pree('in progress');
			//exit;
		}
		
		if(isset($_GET['set-location']) && is_numeric($_GET['set-location']) && $_GET['set-location']>0){	
			if(empty($woocommerce->cart->get_cart())){	
				$woocommerce->session->set_customer_session_cookie(true);
			}
			
			$stock_location_selected = ((isset($woocommerce->session) && $woocommerce->session->has_session())?$woocommerce->session->get('stock_location_selected'):0);
			$stock_location_updated = sanitize_slw_data($_GET['set-location']);
			

			if($stock_location_updated!=$stock_location_selected){
				
				if($stock_location_selected>0){
					
					
					$stock_location_selected_warning = '<div class="stock_location_selected_warning">
					'.__('You are about to change the store, it will affect the available/added stock quantity in your cart.', 'stock-locations-for-woocommerce').'<br /><br />'.__('Please choose one of the following options:', 'stock-locations-for-woocommerce').'<br />
					<a href="'.add_query_arg('slw-cart', 'empty', wc_get_cart_url()).'">'.__('Empty cart', 'stock-locations-for-woocommerce').'</a> '.__('and continue shopping.', 'stock-locations-for-woocommerce').'<br />OR<br />
					<a href="'.add_query_arg('slw-cart', 'update-stock', wc_get_cart_url()).'">'.__('Update cart', 'stock-locations-for-woocommerce').'</a> '.__('and adjust the stock quantity as per availability.', 'stock-locations-for-woocommerce').'

					
					</div>';

					$stock_location_selected_warning = apply_filters('stock_location_selected_warning', $stock_location_selected_warning, $stock_location_updated, $stock_location_selected);
				}
				
				$woocommerce->session->set('stock_location_selected', $stock_location_updated);
			}
			
			
		}
		if ( isset($_GET['slw_get_cart']) && sizeof( WC()->cart->get_cart() ) > 0 ) {
			foreach ( $woocommerce->cart->get_cart() as $cart_item_key => $values ) {
				 //$_product = $values['data'];
				 pree($values);
			}
			exit;
		}
	}

	add_action('woocommerce_before_main_content', 'slw_woocommerce_before_main_content');
	
	function slw_woocommerce_before_main_content(){
		global $woocommerce, $stock_location_selected_warning;
		$items = $woocommerce->cart->get_cart();
		if(count($items)>0){
			echo $stock_location_selected_warning;
		}
	}

	add_action('wp_loaded', 'slw_update_cart_stock_locations');
	
	function slw_update_cart_stock_locations(){



		if(is_admin() || wp_doing_ajax() || (array_key_exists('REDIRECT_URL', $_SERVER) && strpos($_SERVER['REDIRECT_URL'], '/wp-json/')>0)){ return false; }

		global $woocommerce, $slw_plugin_settings;
		
		if(!is_object($woocommerce) || !is_object($woocommerce->cart)){ return; }
		
		if(array_key_exists('different_location_per_cart_item', $slw_plugin_settings) && $slw_plugin_settings['different_location_per_cart_item']=='yes'){ return; }

		$cart_contents = $woocommerce->cart->get_cart();
		if(count($cart_contents)>0){
			
			$different_location_per_cart_item = (isset($slw_plugin_settings['different_location_per_cart_item'])?$slw_plugin_settings['different_location_per_cart_item']:'');
			$different_location_per_cart_item_no = (isset($slw_plugin_settings['different_location_per_cart_item_no'])?$slw_plugin_settings['different_location_per_cart_item_no']:'');
			
			
						
			$stock_location_selected = ((isset($woocommerce->session) && $woocommerce->session->has_session())?$woocommerce->session->get('stock_location_selected'):0);
			foreach($cart_contents as $cart_id=>$cart_item){					
			
				$product_id = $cart_item['product_id'];
				$variation_id = $cart_item['variation_id'];
				$product_item = ($variation_id?$variation_id:$product_id);
				$locations = get_the_terms( $product_id, 'location' );
				if(!empty($locations)){
					$product_locations = array();
					foreach($locations as $location){
						$product_locations[] = $location->term_id;
					}
					if(in_array($stock_location_selected, $product_locations)){
						$product_item_stock = get_post_meta($product_item, '_stock_at_'.$stock_location_selected, true);
						$cart_item['quantity'] = ($product_item_stock>$cart_item['quantity']?$cart_item['quantity']:$product_item_stock);
						$cart_item['stock_location'] = $stock_location_selected;
						WC()->cart->cart_contents[$cart_id] = $cart_item;
						
					}else{
						
						if($different_location_per_cart_item=='no' && $different_location_per_cart_item_no=='remove'){
							WC()->cart->remove_cart_item( $cart_id );
							$product_name = ($cart_item['data']->get_data()['name']);
							
							$slw_notice_msg = apply_filters('slw_notice_msg', sprintf( __('The following product item is not available on the selected store location, so it has been removed from the cart.  %s', 'stock-locations-for-woocommerce'), '<a target="_blank" href='.get_the_permalink($product_id).' class="button alt">'. $product_name .'</a>'), $product_id, $product_name, $stock_location_selected, $product_locations);
														
							wc_print_notice( '<span class="slw-notice-msg">'.$slw_notice_msg.'</span>', 'notice' );
						}
						
						if(isset($cart_item['stock_location'])){							
							unset($cart_item['stock_location']);
						}
					}
					
				}
			}
			WC()->cart->set_session();	
		}
	}
	
	
	add_action('woocommerce_add_to_cart', 'slw_add_to_cart', 1);
	function slw_add_to_cart($product_id=0, $location_id=0, $variation_id=0, $quantity=1) {

		global $woocommerce;
		
		$cart = $woocommerce->cart->cart_contents; 
		
		if(array_key_exists('add-to-cart', $_REQUEST)){
			$product_id = sanitize_slw_data($_REQUEST['add-to-cart']);
		}
		
		
		if(!is_numeric($product_id) && array_key_exists($product_id, $cart)){
			$product_id = (array_key_exists('product_id', $cart[$product_id])?$cart[$product_id]['product_id']:$product_id);
		}

		$location_id = ($location_id?$location_id:(array_key_exists('stock-location', $_REQUEST)?$_REQUEST['stock-location']:0));	
		$quantity = ($quantity?$quantity:(array_key_exists('quantity', $_REQUEST)?$_REQUEST['quantity']:$quantity));	
		
		//pree($quantity);exit;
		
		$found = false;
		
		$cart_item_key = 0;
		
		$update = false;
	
		$values = array();
		
		//pree('Start '.$quantity);
		
		//check if product already in cart
		if ( sizeof( WC()->cart->get_cart() ) > 0) {

			foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
				
				//pree($cart_item_key);
				//pree($cart_item_key.' = '.($found?'FOUND':''));
				if($found){ continue; }
				
				
				$proceed = ($values['data']->get_id() == $product_id && !array_key_exists('stock_location', $values));
				
				//pree('$found: '.$found.' && $proceed: '.$proceed);
				

				if(!$found && $proceed){ // && $values['stock_location']==$location_id
					//pree('A');
					$found = $update = true;	
					$woocommerce->cart->cart_contents[$cart_item_key]['stock_location'] = $location_id;
					
				}else{
					//pree('B '.$product_id.', '.$location_id.', '.$variation_id.', '.$quantity);
					slw_add_to_cart_inner( $product_id, $location_id, true, $cart_item_key, $variation_id, $quantity );
					
				}
				
				
			}
			
			
		} else {
			
			slw_add_to_cart_inner( $product_id, $location_id, false, 0, $variation_id, $quantity );
			
			//pree('C '.$quantity);
		}
		
		
		
		//exit;
	}
	function slw_add_to_cart_inner($product_id, $location_id, $update=false, $cart_item_key=0, $variation_id=0, $quantity=1){
	
		//pree('slw_add_to_cart_inner - '.$product_id.' - '.$location_id.' - '.$update.' - '.$cart_item_key.' - '.$variation_id.' - '.$quantity);
		
		
		
		
		if($location_id && is_numeric($location_id) && $product_id && is_numeric($product_id)){

			$post = get_post($product_id);
			
			//pree($post->post_type);
			
			if(is_object($post) && !empty($post) && in_array($post->post_type, array('product', 'product_variation'))){
				$custom_data = array();
				$variation_id = ($variation_id==$product_id?0:$variation_id);

				$custom_data['stock_location'] = $location_id;
				
				if($update){
					global $woocommerce;
					$cart = $woocommerce->cart->cart_contents;
					foreach ($cart as $key => $item) {
						if($key == $cart_item_key){

							if(array_key_exists('stock_location', $woocommerce->cart->cart_contents[$key])){


								if($woocommerce->cart->cart_contents[$key]['stock_location']!=$location_id){
									
									WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, array(), $custom_data);
									
									//pree('A - '.$location_id.' - '.$quantity);

								}else{
									

								}
							}else{
								$woocommerce->cart->cart_contents[$key]['stock_location'] = $location_id;
								
								//pree('B - '.$location_id.' - '.$quantity);
								
							}
						}
					}
				}else{
					
					WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, array(), $custom_data);
					
					//pree('C - '.$location_id.' - '.$quantity);

				}
				
			}

		}
		
		//exit;
	}	
	
	function slw_location_popup($data){
		
		$str = get_term_meta($data['id'], 'slw_location_popup', true);
		
		$arr = array(
			'LOCATION_ADDRESS'=>$data['title'],
			'LOCATION_PHONE'=>$data['location_phone'],
			'LOCATION_TIMING'=>$data['location_timings'],
			'LOCATION_NAME'=>$data['label'],
			'LOCATION_URL'=>$data['link']
		);
		
		$arr1 = array_keys($arr);
		$arr2 = array_values($arr);
		
		$str = str_replace($arr1,$arr2,$str);
		
		return $str;
	}
	
	function slw_map_with_markers( $atts ) {
		global $slw_gkey, $wp;
		$attributes = shortcode_atts( array(
			'map' => 'yes',
			'locations-list' => 'yes',
			'search-field' => 'yes',
			'shop-button-text'=>__('Search Locations', 'Shop This Location'),
			'shop-location-link'=>'default',
			'directions-button-text'=>__('Directions', 'stock-locations-for-woocommerce'),
			'search-field-placeholder' => __('Search Locations', 'stock-locations-for-woocommerce'),
			'map-width' => '68%',
			'list-width' => '400px',
			'diameter-range' => 100,
			'distance-unit' => 'km',
			'zoom' => '',
		), $atts );
		$attributes['zoom'] = ((is_numeric($attributes['zoom']) && $attributes['zoom']>0)?$attributes['zoom']:16);
		$meta_query = array(
			'key'=>'slw_map_status',
			'compare'=>'=',
			'value'=>true
		);
		$terms = slw_get_locations('location', $meta_query);
		

		
		$locations = array();
		if(!empty($terms)){
			foreach($terms as $term){
				$slw_lat = get_term_meta($term->term_id, 'slw_lat', true);
				$slw_lng = get_term_meta($term->term_id, 'slw_lng', true);
				if($slw_lat && $slw_lat){
					$address = get_term_meta($term->term_id, 'slw_location_address', true);
					$location_email = get_term_meta($term->term_id, 'slw_location_email', true);
					$location_timings = get_term_meta($term->term_id, 'slw_location_timings', true);
					$location_phone = get_term_meta($term->term_id, 'slw_location_phone', true);
					

					$location_data = array('label'=>apply_filters('slw-map-location-label', $term->name, $address, $term->term_id), 'title'=>apply_filters('slw-map-location-name', $address, $term->name, $term->term_id), 'id'=>$term->term_id, 'lat'=>$slw_lat, 'lng'=>$slw_lng, 'type'=>'empty', 'email'=>$location_email, 'link'=>get_term_link($term->term_id, 'location'), 'location_timings'=>$location_timings, 'location_phone'=>$location_phone);
					
					$location_data['location_popup'] = slw_location_popup($location_data);
					
					
					
					$locations[] = $location_data;
				}
			}
		}
		
		ob_start();

		include_once( 'slw-map.php' );
		
	 
		return ob_get_clean();
	 
	}
	add_shortcode( 'SLW-MAP', 'slw_map_with_markers' );
	add_shortcode( 'slw-archive-meta', 'slw_archive_meta_data' );
	
	function slw_archive_meta_data($attr=array()){
		$str = '';
		
		if(is_archive()){
			
			$queried_object = get_queried_object();
			if(!empty($queried_object)){				
				$term_id = $queried_object->term_id;
				$meta_key = (array_key_exists('meta_key', $attr)?$attr['meta_key']:'');
				if($meta_key){
					$term_value = get_term_meta( $term_id, $meta_key, true);
					if($term_value){
						$str = $term_value;
					}
				}
			}
		}
		
		return $str;
	}
	
	
	add_action('woocommerce_archive_description', 'slw_locations_archive_page');
	
	function slw_locations_archive_page(){
		
		$status = get_option('slw-archives-status');
		if($status=='yes'){
			ob_start();
	
			include_once( 'slw-archive-page.php' );
			
		 
			echo ob_get_clean();			
		}
	}
	
	function slw_locations_archive_page_query( $query ){
		$strpos = strpos($_SERVER['REQUEST_URI'], 'location/');
		$status = get_option('slw-archives-status');
		
		if($status=='yes' && !is_admin() && $query->is_main_query() && (function_exists('is_shop') && !is_shop()) && ($strpos!='' && $strpos>=0)){ //is_archive() && 
		
			$query->set( 'post_type', 'slw' );		

		}
		
		if (
			is_admin() || 
			!$query->is_main_query() || 
			!is_tax('location')
		) {
			return;
		}
		
		$term = get_queried_object();
		
		if (!$term || empty($term->term_id)) {
			return;
		}
			
		$location_term_id = $term->term_id;
		$stock_meta_key = '_stock_at_' . $location_term_id;
	
		$query->set('meta_query', array(
			array(
				'key'     => $stock_meta_key,
				'value'   => 0,
				'compare' => '>',
				'type'    => 'NUMERIC',
			),
		));
		
	}
	add_action( 'pre_get_posts', 'slw_locations_archive_page_query' );
	
	add_action( 'wp_ajax_slw_product_list_qty_update', 'slw_product_list_qty_update' );
	function slw_product_list_qty_update() {

		$resp = '';
		if (
			! isset( $_POST['slw_nonce_field'] )
			|| ! wp_verify_nonce( $_POST['slw_nonce_field'], 'slw_nonce' )
		) {

			
			

		} else {
			
			$product_id = (sanitize_slw_data($_POST['product_id']));
			$location_id = (sanitize_slw_data($_POST['location_id']));
			$quantity = (sanitize_slw_data($_POST['quantity']));
			
			if($quantity!='' && $quantity>=0){
				update_post_meta( $product_id, '_stock_at_' . $location_id, $quantity );
			}
			$product_parent = wc_get_product($product_id);
			$product_id = ($product_parent->get_parent_id()>0?$product_parent->get_parent_id():$product_id);
			
			$product_locations_total_stock = \SLW\SRC\Helpers\SlwProductHelper::get_product_locations_stock_total( $product_id );
			//pree($product_id.' - '.$product_locations_total_stock);exit;
			slw_update_product_stock_status( $product_id, $product_locations_total_stock );	
			//slw_update_products($product_id, false, 'update-stock');
			
		}
		echo json_encode($resp);
		wp_die();
	}
	
	
	add_action( 'wp_ajax_nopriv_slw_update_stock_location_session', 'slw_update_stock_location_session' );
	add_action( 'wp_ajax_slw_update_stock_location_session', 'slw_update_stock_location_session' );
	
	function slw_update_stock_location_session(){
		
		$resp = '';
		if (
			! isset( $_POST['slw_nonce_field'] )
			|| ! wp_verify_nonce( $_POST['slw_nonce_field'], 'slw_nonce' )
		) {

			
			

		} else {
			
			global $woocommerce;
			 
			$location_id = (sanitize_slw_data($_POST['location_id']));
			
			$woocommerce->session->set('stock_location_selected', $location_id);
			
		}
		
		echo json_encode($resp);
		wp_die();
		
	}
	
	add_action( 'wp_ajax_nopriv_slw_archive_add_to_cart', 'slw_archive_add_to_cart' );
	add_action( 'wp_ajax_slw_archive_add_to_cart', 'slw_archive_add_to_cart' );
	function slw_archive_add_to_cart() {

		if (
			! isset( $_POST['slw_nonce_field'] )
			|| ! wp_verify_nonce( $_POST['slw_nonce_field'], 'slw_nonce' )
		) {

			echo '0';
			

		} else {
			global $wpdb;
			
			$product_id = (sanitize_slw_data($_POST['product_id']));
			$quantity = (sanitize_slw_data($_POST['quantity']));
			$variation_id = (sanitize_slw_data($_POST['variation_id']));
			$location_id = (sanitize_slw_data($_POST['location_id']));
			
			//pree($product_id.' - '.$quantity.' - '.$variation_id.' - '.$location_id);
			//WC()->cart->add_to_cart( $product_id, $quantity, $variation_id );
			slw_add_to_cart( $product_id, $location_id, $variation_id, $quantity );
				
		}

		wp_die();
	}

	add_action('slw_archive_items_below_qty', 'slw_archive_items_below_qty_callback', 10, 3);
	function slw_archive_items_below_qty_callback( $product_id, $category_id, $location_id ) {
		global $slw_plugin_settings, $wc_slw_pro;
		$product_stock_price_status = array_key_exists('product_stock_price_status', $slw_plugin_settings);				
		$html = '';
		if($location_id){
			$product = wc_get_product($product_id);
			
			switch($product->get_type()){
				case 'simple':
					$product = wc_get_product($product->get_id());
					
					$formatted_attributes = array();

					$attributes = $product->get_attributes();
					
					foreach($attributes as $attr=>$attr_deets){
					
						$attribute_label = wc_attribute_label($attr);
					
						if ((isset( $attributes[ $attr ] ) || isset( $attributes[ 'pa_' . $attr ] )) ) {
					
							$attribute = isset( $attributes[ $attr ] ) ? $attributes[ $attr ] : $attributes[ 'pa_' . $attr ];
							
							if($attribute['visible']==1){
								if ( $attribute['is_taxonomy'] ) {
						
									$formatted_attributes[$attribute_label] = implode( ', ', wc_get_product_terms( $product->id, $attribute['name'], array( 'fields' => 'names' ) ) );
						
								} else {
						
									$formatted_attributes[$attribute_label] = $attribute['value'];
								}
							}
					
						}
					}
					
					if(!empty($formatted_attributes)){
						$html_extended = '<div class="slw-variations-wrapper"><div class="slw-variations" id="slw-product-'.$product->get_id().'">';
						$count = 0;
						foreach($formatted_attributes as $attribute_name=>$attribute_value){ $count++;
							
							$selected = ($count==1?'checked="checked"':'');
							
							
							
							$html_extended .= '<label data-price="'.trim($product->get_price()).'" data-id="'.$product->get_id().'"><input type="hidden" id="slw-variation-'.$product->get_id().'" name="slw-variation-'.$product->get_id().'" value="'.$product->get_id().'" '.$selected .' /><span><strong>'.$attribute_name.':</strong> '.$attribute_value.'</span></label>';
						}
						$html_extended .= '</div></div>';
						$html = $html_extended;
					}
						
				break;
				case 'variable':
				
				
					$product = wc_get_product($product->get_id());
					$variations = $product->get_available_variations();

					
					if(!empty($variations)){
						$html_extended = '<div class="slw-variations-wrapper"><div class="slw-variations" id="slw-product-'.$product->get_id().'">';
						$count = 0;
						foreach($variations as $variation_data){ $count++;
							$variation_id = $variation_data['variation_id'];
									
							$_stock_at = get_post_meta($variation_id, '_stock_at_'.$location_id, true);
							$_stock_at = ($_stock_at>0?$_stock_at:0);
							
							if(!$_stock_at){ continue; }
													
							$attributes = $variation_data['attributes'];
							$label = array();
							if(!empty($attributes)){
								foreach($attributes as $attribute){
									$label[] = str_replace('-', ' ', $attribute);
								}
								$label = array_filter($label);
								$label = array_map('ucwords', $label);
							}
							$price = '';
							if($product_stock_price_status){
								$_stock_location_price = get_post_meta($variation_id, '_stock_location_price_'.$location_id, true);
								$variation = wc_get_product($variation_id);
								$price = $variation->get_price();
								$price = ($_stock_location_price?($_stock_location_price):($price));
							}else{
							}
							$selected = ($count==1?'checked="checked"':'');
							
							$html_extended .= '<label data-price="'.trim($price).'" data-id="'.$variation_id.'"><input type="radio" id="slw-variation-'.$product->get_id().'-'.$variation_id.'" name="slw-variation-'.$product->get_id().'" value="'.$variation_id.'" '.$selected.' /><span>'.implode(', ', $label).'</span></label>';
						}
						$html_extended .= '</div></div>';
					}
					$html = $html_extended;
				
				break;
			}
					
			
		}
		echo $html;
	}
	
	add_filter('slw_archive_product_image', 'slw_archive_product_image_callback', 10, 2);
	function slw_archive_product_image_callback($str, $product_id=0){
		
		if($product_id){
			return wp_get_attachment_url( get_post_thumbnail_id($product_id) );
		}
	}
	function slw_archive_wrapper_attributes( $class = '' ) {
		$classes = array('slw-archive-wrapper');		
		$classes = apply_filters( 'slw-archive-wrapper', $classes, $class );
		$classes = array_map( 'esc_attr', $classes );
		// Separates class names with a single space, collates class names for body element.
		echo 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';
	}
	add_action('slw_archive_before_wrapper', 'slw_archive_before_wrapper_callback', 10, 1);
	function slw_archive_before_wrapper_callback( $location_id ) {	
	}
	add_action('slw_archive_after_wrapper', 'slw_archive_after_wrapper_callback', 10, 1);
	function slw_archive_after_wrapper_callback( $location_id ) {	
	}	
	add_action('slw_archive_inside_wrapper_start', 'slw_archive_inside_wrapper_start_callback', 10, 3);
	function slw_archive_inside_wrapper_start_callback( $obj, $cat_id, $location_id ) {	
	}
	add_action('slw_archive_inside_wrapper_end', 'slw_archive_inside_wrapper_end_callback', 10, 3);
	function slw_archive_inside_wrapper_end_callback( $obj, $cat_id, $location_id ) {	
	}	
	if(!function_exists('everything_stock_status_to_instock')){	
		function everything_stock_status_to_instock($product_id){
				update_post_meta($product_id, '_backorders', 'yes');
		}
	}
	if(!function_exists('location_select_input_inner')){	
		function location_select_input_inner($type='select', $product_id=0, $stock_locations=array(), $html=''){ 

			global $slw_plugin_settings;
			$default_location      = array_key_exists('default_location_in_frontend_selection', $slw_plugin_settings) ? get_post_meta( $product_id, '_slw_default_location', true ) : 0;
			$everything_stock_status_to_instock = array_key_exists('everything_stock_status_to_instock', $slw_plugin_settings);
			$product_stock_price_status = array_key_exists('product_stock_price_status', $slw_plugin_settings) && $slw_plugin_settings['product_stock_price_status'] == 'on' ? true : false;			
			
			
			if(is_archive()){
			
				$category_pages_frontend = (isset($slw_plugin_settings['general_display_settings']) && isset($slw_plugin_settings['general_display_settings']['category_pages_frontend']) && $slw_plugin_settings['general_display_settings']['category_pages_frontend'] == 'on' );
				
				$queried_object = get_queried_object();
				$is_category = ($queried_object->taxonomy=='product_cat');
				if($is_category && !$category_pages_frontend){ return $html; }

			}
			
			switch($type){
				case 'radio_simple':
					$hidden = (count($stock_locations)==1?'can-be-hidden':'');
					$ret_html = '<div class="slw_stock_location_selection '.$hidden.'">';

					$priority_used = 0;
					$term_id_default = 0;
					
					$selected = '';
					
					foreach( $stock_locations as $id => $location ) {
						
						
						
						$slw_location_priority = get_term_meta($id, 'slw_location_priority', true);
						
						if($location['quantity']>0){
							if( $default_location != 0 && $location['term_id'] == $default_location){
								$selected = ($selected?'':'checked="checked"');
							}else{
								if($slw_location_priority>$priority_used){						
									$priority_used = $slw_location_priority;
									$selected = ($selected?'':'checked="checked"');
								}						
							}
						}
						
						
						
						$stock_price = ($location['price']);
						
						$stock_location_name = $location['name'];
						
						if($product_stock_price_status){
							//$stock_location_name .= ' '.get_woocommerce_currency_symbol().($stock_price);
						}
	
	
						$disabled = '';
						if( $location['quantity'] < 1 && $location['allow_backorder'] != 1 && !$everything_stock_status_to_instock) {
							$disabled = 'disabled="disabled"';
						}
						
						
						
						$ret_html .= '<label for="slw-location-'.$location['term_id'].'-'.$product_id.'"><input name="slw_add_to_cart_item_stock_location['.$product_id.']" id="slw-location-'.$location['term_id'].'-'.$product_id.'" type="radio" class="'.($location['quantity']>0?' has-stock':'').'" data-priority="'.$slw_location_priority.'" data-price="'.trim($stock_price).'" data-quantity="'.$location['quantity'].'" value="'.$location['term_id'].'" '.$disabled.' '.$selected.'>'.$stock_location_name.'</label>';
						
						
						$term_id_default = ($term_id_default?$term_id_default:$location['term_id']);
						
						
					
					}
					$ret_html .= '</div>';
					
					if(function_exists('slw_archive_qty_box')){ 
						$ret_html .= slw_archive_qty_box ($product_id); 
					}
					
					$html = $ret_html.($term_id_default?str_replace(array('add-to-cart=', 'add_to_cart_button'), array('stock-location='.$term_id_default.'&quantity=1&add-to-cart=', 'add-to-cart-button'), $html):'');
				break;
				
				case 'radio_variable':
					
					$term_id = (is_product()?false:get_queried_object_id());
					
					$html .= '<div '.($term_id?'style="display:none !important; width:100%;"':'class="slw_stock_location_selection"').'>
					</div>';
					
				break;
			}
			
			return $html;
		}
	}
	
	add_filter('rtwpvs_variation_attribute_options_html', 'slw_rtwpvs_variation_attribute_options_html', 10, 2);
	
	function slw_rtwpvs_variation_attribute_options_html($html='', $args=array()){ 
	
		global $slw_plugin_settings;

		if(is_archive()){
		
			$category_pages_frontend = (isset($slw_plugin_settings['general_display_settings']) && isset($slw_plugin_settings['general_display_settings']['category_pages_frontend']) && $slw_plugin_settings['general_display_settings']['category_pages_frontend'] == 'on' );
			
			$queried_object = get_queried_object();
			$is_category = ($queried_object->taxonomy=='product_cat');
			
			if($is_category && !$category_pages_frontend){ return $html; }
			
		}
			
		$product = $args['product'];
		$visible_children = $product->get_visible_children();
		if(is_array($visible_children) && !empty($visible_children)){
			$product_id = $product->get_id();
			$attribute = $args['attribute'];
			$options = $args['options'];
			$attribute_key = 'attribute_'.$attribute;	
			$product_attributes = wc_get_product_terms($product_id, $attribute, array('fields' => 'all'));		
			$product_attributes_arr = array();
			foreach ($product_attributes as $product_attribute) {
				$product_attributes_arr[] = $product_attribute->slug;
			}
			$variation_attributes_arr = array();
			foreach($visible_children as $variation_id){
				$variation_attributes = wc_get_product_variation_attributes($variation_id); 
				if(array_key_exists($attribute_key, $variation_attributes)){
					$variation_attribute = $variation_attributes[$attribute_key];					
					if($variation_attribute){
						$variation_attributes_arr[] = $variation_attribute;						
					}
				}
			}
			
			$product_variation_attribute_diff = array_diff($product_attributes_arr, $variation_attributes_arr);
			
			if(is_array($product_variation_attribute_diff) && !empty($product_variation_attribute_diff)){
				foreach($product_variation_attribute_diff as $product_variation_attribute){
					$html = str_replace('button-variable-term-'.$product_variation_attribute, 'button-variable-term-'.$product_variation_attribute.' disabled rtwpvs-disabled', $html);
					$key = array_search($product_variation_attribute, $args['options']);
					if (false !== $key) {
						unset($args['options'][$key]);
					}
				}
				$args['options'] = array_values($args['options']);
				
			}
		}
		if(isset($_GET['debug'])){
			pre($args['options']);
			pre($html);exit;
		}
		return $html;
	}
	
	
	function slw_location_selection_popup(){
		ob_start();
		$terms = slw_get_locations();
		//pree($terms);exit;
		if(!empty($terms)){
			echo '<div class="slw-location-selection-popup"><ul>';
			foreach($terms as $term){
?>

	<li data-id="<?php echo $term->term_id; ?>"><a href="<?php echo get_term_link($term->term_id, 'location'); ?>"><?php echo $term->name; ?></a></li>

<?php		
			}
			echo '</ul></div>';
		}
		
		$ret = ob_get_contents();
		
		ob_end_clean();
		
		$ret = apply_filters('slw_location_selection_popup_content', $ret, $terms);
		//pree($ret);exit;
		return $ret;
	}
	
	if(isset($_GET['getProductStockLocations'])){
		
		add_action('admin_init', function(){
			
			
			
			
			$itemStockLocationTerms = \SLW\SRC\Helpers\SlwStockAllocationHelper::getProductStockLocations( 37, false );
			
			pree($itemStockLocationTerms);
			
			exit;
			
		});
		
	}