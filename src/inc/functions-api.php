<?php
	
	add_action('init', function(){
		
		
		
		if(isset($_GET['slw-api'])){
			
			global $slw_api_valid_keys;
			
			$response = array('response'=>false);
			
			
			$data = array('action'=>'','format'=>'');
			if(!empty($_GET)){
				
				$received = sanitize_slw_data($_GET);
				
				if ( empty( $slw_api_valid_keys ) || ! is_array( $slw_api_valid_keys ) ) {
					$slw_api_valid_keys = [];
				}
				foreach($received as $k=>$v){
					if(array_key_exists($k, $slw_api_valid_keys)){
						$data[$k] = $v;
					}
				}
			}
			
			if(get_option('slw_api_status')==true){
				
				$current_source = ($_SERVER['REMOTE_ADDR'].'/'.$_SERVER['SERVER_NAME']);
				
				$validated_requests = get_option('slw_api_request_validated', array());
				
				$validated_requests = (is_array($validated_requests)?$validated_requests:array());
				
				$all_requests = get_option('slw_api_request_sources', array());
				
				$all_requests = (is_array($all_requests)?$all_requests:array());
				
				
			
				$all_requests[time()] = $current_source;
				
				
				$all_requests = array_unique($all_requests);
				
				update_option('slw_api_request_sources', $all_requests);
	
				
				if(!in_array($current_source, $validated_requests)){
					
					_e('Sorry, you are not allowed to proceed.', 'stock-locations-for-woocommerce');
					exit;
				}
				
				//pree($data['action']);exit;
				
				switch($data['action']){
					case 'read':
					case 'get':
					case 'fetch':
					case 'pull':
						
						switch($data['item']){
							case 'location':
								if($data['id']){
									$response = get_term_by('id', $data['id'], 'location');
								}else{
									$response = slw_get_locations();
								}
							break;
							case 'stock':
								if($data['product_id'] && $data['location_id']){
									$response['stock_value'] = get_post_meta($data['product_id'], '_stock_at_' . $data['location_id'], true);
								}
							break;
							case 'product':
								//pree($data['id']);
								if($data['id']){
									$response[$data['id']] = wc_get_product($data['id']);
								}else{
									$products = get_posts( array('post_type'=>'product') );								
									if(!empty($products)){
										foreach($products as $product){ if(!is_object($product)){ continue; }
											//pree($product->ID);
											$response[$product->ID] = wc_get_product($product->ID);
										}
									}
								}
							break;
							case 'price':
								$response['price'] = get_post_meta($data['product_id'], '_price', true);
							break;
						}
						
					break;
					case 'replace':
					case 'update':
					case 'put':
					case 'set':
						switch($data['item']){
							case 'location':
								if($data['product_id'] && $data['location_id']){
									$product_locations = wp_get_object_terms($data['product_id'], 'location');
									$paux = array(intval($data['location_id']));
									foreach($product_locations as $termVal) {
										if ($termVal->term_id != $paux[0]) $paux[] = $termVal->term_id;
									}
									// Only update terms if different
									$current_terms = wp_get_object_terms($data['product_id'], 'location', array('fields' => 'ids'));
									if (array_diff($paux, $current_terms) || array_diff($current_terms, $paux)) {
										$response['response'] = wp_set_object_terms($data['product_id'], $paux, 'location');
									} else {
										$response['response'] = false; // no change
									}
								}
								
							break;
							case 'stock':
								if($data['product_id'] && $data['location_id'] && $data['value'] >= 0){
									$current_value = get_post_meta($data['product_id'], '_stock_at_' . $data['location_id'], true);
						
									if((string)$current_value !== (string)$data['value']){
										// Update location terms if not already set
										$product_locations = wp_get_object_terms($data['product_id'], 'location');
										$paux = array(intval($data['location_id']));
										foreach($product_locations as $termVal) {
											if ($termVal->term_id != $paux[0]) $paux[] = $termVal->term_id;
										}
										wp_set_object_terms($data['product_id'], $paux, 'location');
						
										// Update stock for this location
										$response['response'] = update_post_meta($data['product_id'], '_stock_at_' . $data['location_id'], $data['value']);
						
										// Recalculate total stock and sync WC stock/status
										$locations_total = \SLW\SRC\Helpers\SlwProductHelper::get_product_locations_stock_total($data['product_id']);
										if($locations_total !== null){
											slw_update_product_stock_status($data['product_id'], (int)$locations_total);
										}
						
										// Sync parent product if variation
										$wc_product_obj = wc_get_product($data['product_id']);
										if($wc_product_obj && $wc_product_obj->is_type('variation')){
											\SLW\SRC\Helpers\SlwProductHelper::update_wc_stock_status($wc_product_obj->get_parent_id(), null, true);
										}
									} else {
										$response['response'] = false; // no change
									}
								}
							break;
							case 'product':
								
							break;
							case 'price':
								if($data['product_id'] && isset($data['value']) && is_numeric($data['value']) && $data['value'] >= 0){
									$updated = false;
									$current_regular = get_post_meta($data['product_id'], '_regular_price', true);
									$current_price = get_post_meta($data['product_id'], '_price', true);
						
									if((string)$current_regular !== (string)$data['value']){
										update_post_meta($data['product_id'], '_regular_price', $data['value']);
										$updated = true;
									}
									if((string)$current_price !== (string)$data['value']){
										update_post_meta($data['product_id'], '_price', $data['value']);
										$updated = true;
									}
						
									$response['response'] = $updated ? true : false;
								}
							break;
						}
					break;
					default:
						//pree($data['format']);exit;
						$response['response'] = true;
						$raw_json = file_get_contents('php://input');
						$decoded_payload = json_decode($raw_json, true);
						
						if (json_last_error() !== JSON_ERROR_NONE) {
							$decoded_payload = []; // prevent crashes on invalid JSON
						}
						
						//pree($decoded_payload['payload']);exit;
						
						// Only process if it is a valid JSON array/object with payloads
						if(function_exists('slw_api_payload_update')){
							slw_api_payload_update($decoded_payload);
						}
					
						
						// After processing JSON payload, we can exit if we want to avoid the old URL-style handling
						
						echo json_encode($response);
						exit;
					
				
					break;
				}
				
			}else{
				_e('Sorry, API is not enabled.', 'stock-locations-for-woocommerce');
				exit;
			}
			
			if($data['product_id']){
				$response['product_id'] = $data['product_id'];
				slw_update_products($data['product_id'], false, 'update-stock');
			}
			
			switch($data['format']){
				default:
				case 'default':
					pree($response);
				break;
				case 'json':
					echo json_encode($response);
				break;
			}
				
			exit;	
		}
	}, 100);
	
	add_action('wp_ajax_slw_api_get_product_stock_data', 'slw_api_get_product_stock_data');
	
	if(!function_exists('slw_api_get_product_stock_data')){
		function slw_api_get_product_stock_data(){
			global $wc_slw_premium_copy;
			echo __('This is a premium feature!', 'stock-locations-for-woocommerce').' '.__('Click here to', 'stock-locations-for-woocommerce').' <a class="gopro" target="_blank" href="'.esc_url($wc_slw_premium_copy).'">'.__("Go Premium",'stock-locations-for-woocommerce').'</a>';
			exit;
		}
	}
