<?php
	
	add_action('init', function(){
		
		
		
		if(isset($_GET['slw-api'])){
			
			global $slw_api_valid_keys;
			
			$response = array('response'=>false);
			
			
			$data = array('action'=>'','format'=>'');
			if(!empty($_GET)){
				
				$received = sanitize_slw_data($_GET);
				foreach($received as $k=>$v){
					if(array_key_exists($k, $slw_api_valid_keys)){
						$data[$k] = $v;
					}
				}
			}
			
			if(get_option('slw_api_status')==true){
			
				
	
				
				
				
				
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
								if($data['id']){
									$response[$data['id']] = wc_get_product($data['id']);
								}else{
									$products = get_posts( array('post_type'=>'product') );								
									if(!empty($products)){
										foreach($products as $product){ if(!is_object($product)){ continue; }
											$response[$product->ID] = wc_get_product($product->ID);
										}
									}
								}
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
									$product_locations = wp_get_object_terms($data['product_id'],  'location' );
									$paux = array(intval($data['location_id']));
									foreach($product_locations as $termVal) {
										if ($termVal -> term_id != $paux[0]) $paux[] = $termVal -> term_id;
									}
									$response['response'] = wp_set_object_terms($data['product_id'], $paux, 'location');
								}
								
							break;
							case 'stock':
								if($data['product_id'] && $data['location_id'] && $data['stock_value']>=0){
									$response['response'] = update_post_meta($data['product_id'], '_stock_at_' . $data['location_id'], $data['stock_value']);
								}
							break;
							case 'product':
								
							break;
						}
					break;
				}
				
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
	});