<?php
	
	add_action('init', function(){
		if(isset($_GET['slw-api'])){
			global $slw_api_valid_keys;
			//pree($_GET);
			$response = array();
			
			$data = array();
			if(!empty($_GET)){
				$received = sanitize_slw_data($_GET);
				foreach($received as $k=>$v){
					if(array_key_exists($k, $slw_api_valid_keys)){
						$data[$k] = $v;
					}
				}
			}
			
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
								$response = get_terms( 'location', array(
									'hide_empty' => false,
								) );
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
									foreach($products as $product){
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
						
						break;
						case 'stock':
							if($data['product_id'] && $data['location_id'] && $data['stock_value']){
								$response['response'] = update_post_meta($data['product_id'], '_stock_at_' . $data['location_id'], $data['stock_value']);
							}
						break;
						case 'product':
							
						break;
					}
				break;
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