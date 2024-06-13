<?php if ( ! defined( 'ABSPATH' ) ){ exit; }else{ clearstatcache(); }

if(!function_exists('pre')){
function pre($data){
	if(isset($_GET['debug'])){
	  pree($data);
	}
  }
}
if(!function_exists('pree')){
function pree($data){
	
	$debug_backtrace = debug_backtrace();
		
	$function = $debug_backtrace[0]['function'];
	$function .= ' / '.$debug_backtrace[1]['function'];
	$function .= ' / '.$debug_backtrace[2]['function'];
	$function .= ' / '.$debug_backtrace[3]['function'];
	$function .= ' / '.$debug_backtrace[4]['function'];
	
	//if(function_exists('wc_slw_logger')){ wc_slw_logger('debug', $function); }
	
	echo '<pre>';
	//print_r($function);
	print_r($data);
	echo '</pre>';

  }
}
if(!function_exists('slw_notices')){
	function slw_notices($data, $echo = false){
		$ret = '<div class="slw-notice">';
		$ret .= $data;
		$ret .= '</div>';  
		
		if($echo){
			echo $ret;
		}else{
			return $ret;
		}
	
	}
}
if(!function_exists('sanitize_slw_data')){
	function sanitize_slw_data( $input ) {
		if(is_array($input)){		
			$new_input = array();	
			foreach ( $input as $key => $val ) {
				$new_input[ $key ] = (is_array($val)?sanitize_slw_data($val):sanitize_text_field( $val ));
			}			
		}else{
			$new_input = sanitize_text_field($input);			
			if(stripos($new_input, '@') && is_email($new_input)){
				$new_input = sanitize_email($new_input);
			}
			if(stripos($new_input, 'http') || wp_http_validate_url($new_input)){
				$new_input = sanitize_url($new_input);
			}			
		}	
		return $new_input;
	}	
}
		
if(!function_exists('wc_slw_logger')){
	function wc_slw_logger($type='debug', $data=array()){
		
		$types = array('debug');
		
		if(is_array($type) || is_object($type)){
			$data = (array)$type;
			$type = 'debug';
		}else{
			if(!array_key_exists($type, $types) && empty($data)){
				$data = $type;
				$type = 'debug';
			}
		}

		$slw_logger = get_option('slw_logger');
		
		$slw_logger = is_array($slw_logger)?$slw_logger:array();		
		
		if(empty($data) || $type==$data){ return $slw_logger; }
		
		
		
		$debug_backtrace = debug_backtrace();
		$function = $debug_backtrace[1]['function'];
		$function .= (array_key_exists(2, $debug_backtrace)?' / '.$debug_backtrace[2]['function']:'');
		$function .= (array_key_exists(3, $debug_backtrace)?' / '.$debug_backtrace[3]['function']:'');
		$function .= (array_key_exists(4, $debug_backtrace)?' / '.$debug_backtrace[4]['function']:'');
		$function .= (array_key_exists(5, $debug_backtrace)?' / '.$debug_backtrace[5]['function']:'');
		
		switch($type){
			case 'debug':

				
				
				if((is_array($data) || is_object($data)) && !empty($data)){
					$slw_logger[] = $data;
					$slw_logger[] = '<small>('.$function.')</small> - '.date('d M, Y h:i:s A');
					update_option('slw_logger', $slw_logger);
				}else{				
					$slw_logger[] = $data.' <small>('.$function.')</small> - '.date('d M, Y h:i:s A');
					if(trim($data)){
						update_option('slw_logger', $slw_logger);
					}
				}
				
				
			break;
		}
		
		return $slw_logger;
	}
}



add_action('wp_ajax_slw_location_status', 'slw_location_status');

if(!function_exists('slw_location_status')){
	function slw_location_status(){
		if(!empty($_POST) && isset($_POST['status'])){
			if (! isset( $_POST['slw_nonce_field'] ) || ! wp_verify_nonce( $_POST['slw_nonce_field'], 'slw_nonce' )	) {	
				echo '0';		
			} else {
				$status = ($_POST['status']=='yes');
				$location_id = sanitize_slw_data($_POST['location_id']);
				update_term_meta($location_id, 'slw_location_status', $status);				
				echo '1';
			}
		}

		wp_die();
	}
}

add_action('wp_ajax_slw_map_status', 'slw_map_status');

if(!function_exists('slw_map_status')){
	function slw_map_status(){
		if(!empty($_POST) && isset($_POST['status'])){
			if (! isset( $_POST['slw_nonce_field'] ) || ! wp_verify_nonce( $_POST['slw_nonce_field'], 'slw_nonce' )	) {	
				echo '0';		
			} else {
				$status = ($_POST['status']=='yes');
				$location_id = sanitize_slw_data($_POST['location_id']);
				update_term_meta($location_id, 'slw_map_status', $status);				
				echo '1';
			}
		}

		wp_die();
	}
}


add_action('wp_ajax_slw_api_status', 'slw_api_status');

if(!function_exists('slw_api_status')){
	function slw_api_status(){

		if(!empty($_POST) && isset($_POST['status'])){

			if (
				! isset( $_POST['slw_nonce_field'] )
				|| ! wp_verify_nonce( $_POST['slw_nonce_field'], 'slw_nonce' )
			) {

				echo '0';
				

			} else {
				$status = ($_POST['status']=='yes');
				update_option('slw_api_status', $status);
				
				echo '1';

			}
		}

		wp_die();
	}
}

add_action('wp_ajax_slw_crons_status', 'slw_crons_status');

if(!function_exists('slw_crons_status')){
	function slw_crons_status(){

		if(!empty($_POST) && isset($_POST['status'])){

			if (
				! isset( $_POST['slw_nonce_field'] )
				|| ! wp_verify_nonce( $_POST['slw_nonce_field'], 'slw_nonce' )
			) {

				echo '0';
				

			} else {
				$status = ($_POST['status']=='yes');
				update_option('slw_crons_status', $status);
				
				echo '1';

			}
		}

		wp_die();
	}
}


add_action('wp_ajax_slw_widgets_settings', 'slw_widgets_settings');

if(!function_exists('slw_widgets_settings')){
	function slw_widgets_settings(){
		
		$wc_slw_widgets = wc_slw_widgets('fields');
		if(!empty($_POST) && isset($_POST['slw_widget_key']) && in_array($_POST['slw_widget_key'], $wc_slw_widgets)){

			if (
				! isset( $_POST['slw_nonce_field'] )
				|| ! wp_verify_nonce( $_POST['slw_nonce_field'], 'slw_nonce' )
			) {

				echo '0';
				

			} else {
				$posted = sanitize_slw_data($_POST);
				$slw_widget_key = $posted['slw_widget_key'];
				$slw_widget_value = $posted['slw_widget_value'];
				update_option($slw_widget_key, $slw_widget_value);
				
				echo '1';

			}
		}

		wp_die();
	}
}


add_action('wp_ajax_slw_validate_api_requests', 'slw_validate_api_requests_callback');

if(!function_exists('slw_validate_api_requests_callback')){
	function slw_validate_api_requests_callback(){

		if(!empty($_POST) && isset($_POST['slw_validate_request'])){

			if (
				! isset( $_POST['slw_nonce_check'] )
				|| ! wp_verify_nonce( $_POST['slw_nonce_check'], 'slw_nonce' )
			) {

				_e('Sorry, your nonce did not verify.', 'stock-locations-for-woocommerce');
				exit;

			} else {
				$slw_validate_request = sanitize_slw_data($_POST['slw_validate_request']);
			
				if(is_array($slw_validate_request)){
					update_option('slw_api_request_validated', $slw_validate_request);
				}else{
					update_option('slw_api_request_validated', array());
				}
				

			}
		}

		wp_die();
	}
}


add_action('wp_ajax_slw_validate_cron_requests', 'slw_validate_cron_requests_callback');

if(!function_exists('slw_validate_cron_requests_callback')){
	function slw_validate_cron_requests_callback(){

		if(!empty($_POST) && isset($_POST['slw_validate_request'])){

			if (
				! isset( $_POST['slw_nonce_check'] )
				|| ! wp_verify_nonce( $_POST['slw_nonce_check'], 'slw_nonce' )
			) {

				_e('Sorry, your nonce did not verify.', 'stock-locations-for-woocommerce');
				exit;

			} else {
				$slw_validate_request = sanitize_slw_data($_POST['slw_validate_request']);
			
				if(is_array($slw_validate_request)){
					update_option('slw_cron_request_validated', $slw_validate_request);
				}else{
					update_option('slw_cron_request_validated', array());
				}
				

			}
		}

		wp_die();
	}
}


add_action('wp_ajax_slw_clear_debug_log', 'slw_clear_debug_log');

if(!function_exists('slw_clear_debug_log')){
	function slw_clear_debug_log(){

		if(!empty($_POST) && isset($_POST['slw_clear_debug_log'])){

			if (
				! isset( $_POST['slw_clear_debug_log_field'] )
				|| ! wp_verify_nonce( $_POST['slw_clear_debug_log_field'], 'slw_nonce' )
			) {

				_e('Sorry, your nonce did not verify.', 'stock-locations-for-woocommerce');
				exit;

			} else {
				
				update_option('slw_logger', array());

			}
		}

		wp_die();
	}
}

add_action( 'pmxi_saved_post', function( $id )
{
	$import_id = ( isset( $_GET['id'] ) ? $_GET['id'] : ( isset( $_GET['import_id'] ) ? $_GET['import_id'] : 'new' ) );
	// get locations total stock
	$locations_total_stock = \SLW\SRC\Helpers\SlwProductHelper::get_product_locations_stock_total( $id );

	// update stock
	slw_update_product_stock_status( $id, $locations_total_stock );
	
	// update stock status
	\SLW\SRC\Helpers\SlwProductHelper::update_wc_stock_status( $id );

}, 10, 1 );

if(!function_exists('slw_quantity_format')){
	function slw_quantity_format($data){
		$plugin_settings = get_option( 'slw_settings' );
		$plugin_settings = (is_array($plugin_settings)?$plugin_settings:array());
		$max_number = ((array_key_exists('show_with_postfix', $plugin_settings) && is_numeric($plugin_settings['show_with_postfix']) && $plugin_settings['show_with_postfix']>1)?$plugin_settings['show_with_postfix']:0);
		
		if($max_number){
			$data = ($data>$max_number?$max_number.'+':$data);
		}
		return $data;
	}
}
if(!function_exists('wc_slw_admin_init')){
	function wc_slw_admin_init($data){
		//http://demo.gpthemes.com/wp-admin/post.php?post=320372&action=edit&get_keys&debug
		
		
		if((get_option('slw_crons_status')!=true)){
			$slw_update_products = get_option('slw_update_products', array());
			//pree($slw_update_products);exit;
			$slw_update_products = (is_array($slw_update_products)?$slw_update_products:array());
			
			if(is_array($slw_update_products) && !empty($slw_update_products)){
				$item_count = 0;
				foreach($slw_update_products as $product_id){ 
				
					if($item_count>=25){ continue; }
				
					$item_count++;
					slw_update_products($product_id, false, 'update-stock');
					
					/*if (($key = array_search($product_id, $slw_update_products)) !== false) {
						unset($slw_update_products[$key]);
					}*/
				}
				//pree($slw_update_products);
				//update_option('slw_update_products', $slw_update_products);
			}
		}
		$post_id = (isset($_GET['post'])?$_GET['post']:(isset($_GET['id'])?$_GET['id']:0));
		
		if(is_numeric($post_id) && $post_id>0 && isset($_GET['debug'])){
			
			$order = get_post(sanitize_slw_data($post_id));
			
			if(is_object($order) && in_array($order->post_type, array('product'))){
				if(isset($_GET['get_keys'])){
					

					
					pre(get_post_meta($order->ID));
					
					$product = wc_get_product($order->ID);
					
					pre($product);
					
					exit;
				}
			}
			if(is_object($order) && substr($order->post_type, 0, strlen('shop_order'))=='shop_order'){
				
				pree('get_keys: ');
				if(isset($_GET['get_keys'])){
					pree(get_post_meta($order->ID));
					
				}
				pree('get_items: ');
				if(isset($_GET['get_items'])){
					$order_obj = wc_get_order($order->ID);
					foreach($order_obj->get_items() as $item_key=>$item_data){
						pree($item_key);
						pree($item_data);
					}
				}
				pree('get_items_meta: ');
				if(isset($_GET['get_items_meta'])){
					$order_obj = wc_get_order($order->ID);
					foreach($order_obj->get_items() as $item_key=>$item_data){
						pree($item_key);
						pree(wc_get_order_item_meta($item_key, ''));
					}
					
				}
				exit;
				
			}
		}
		
	}
}
add_action('admin_init', 'wc_slw_admin_init');

add_action('wp_head', 'slw_wp_head');
function slw_wp_head(){
?>
<script type="text/javascript" language="javascript">
<?php if(!is_admin() && !wp_doing_ajax() && array_key_exists('add-to-cart', $_GET)  && array_key_exists('stock-location', $_GET)){ ?>
var newURL = location.href.split("?")[0];
window.history.pushState('object', document.title, newURL);
<?php } ?>
jQuery(document).ready(function($){

});
</script>
<?php	
}

	if(!function_exists('slw_widget_val')){
		function slw_widget_val($type, $val=''){
			$ret = (is_array($val)?'':$val);
			if($val==''){return;}
			switch($type){
				case 'screenshot':
					if(is_array($val)){
						foreach($val as $v){
							$ret .= '<a href="'.$v.'"><img src="'.$v.'" /></a>';
						}
					}else{
						$ret = '<a href="'.$val.'"><img src="'.$val.'" /></a>';
					}
				break;
				case 'input':
					$db_val = get_option($val['name']);

					$ret = ($val['caption']?'<label>'.$val['caption'].':</label>':'');
					
					switch($val['type']){
						case 'text':
							$ret .= '<input type="'.$val['type'].'" name="'.$val['name'].'" id="'.$val['name'].'" value="'.$db_val.'" />';
						break;
						case 'toggle':
							$ret .= '<label data-val="'.$db_val.'" class="switch" style="float:none; clear:both;"><input '.checked($db_val=='yes', true, false).' name="'.$val['name'].'" id="'.$val['name'].'" value="yes" type="checkbox" data-on="'.__('Enabled', 'stock-locations-for-woocommerce').'" data-off="'.__('Disabled', 'stock-locations-for-woocommerce').'" /><span class="slider round"></span></label>';
						break;
					}
				break;
				case 'shortcode':
					if(is_array($val)){
						$ret .= '<i class="fas fa-code" title="'.__('Click here to show available hooks', 'stock-locations-for-woocommerce').'"></i><ul><li><span>'.implode('</span></li><li><span>', $val).'</span></li></ul>';
					}else{
						$ret .= '<span>'.$val.'</span>';
					}
				break;
				default:
					$ret = '<span data-val="'.$val.'">'.$val.'</span>';
				break;
			}
			return $ret;
		}
	}
	if(!function_exists('slw_update_products')){
		function slw_update_products($product_id=0, $cron=true, $action='update-stock'){

		
			global $wpdb;



			$slw_crons = isset($_GET['slw-crons']);
			
			$limited = (isset($_GET['limit'])?sanitize_slw_data($_GET['limit']):0);
			$reconsider = (isset($_GET['reconsider'])?sanitize_slw_data($_GET['reconsider']):'');
			$limit = (is_numeric($limited) && $limited>0?$limited:10);
			$action = ($slw_crons && isset($_GET['action'])?sanitize_slw_data($_GET['action']):sanitize_slw_data($action));
			$product_id = (isset($_GET['product_id'])?sanitize_slw_data($_GET['product_id']):sanitize_slw_data($product_id));
			$product_ids = 	array();
			$slw_default_locations = slw_get_locations('location', array('key'=>'slw_default_location', 'value'=>1, 'compare'=>'='), true);	
			//pree($wpdb->last_query);
			//pree($slw_default_locations);
			//pree($terms);exit;
			
			if(!empty($slw_default_locations )){
				$location_ids = array();
				foreach($slw_default_locations as $slw_default_location){
					$location_ids[] = $slw_default_location->term_id;
				}
				

			}
			
			$slw_default_locations_query = "
												SELECT 
														p.ID 
												FROM 
													`".$wpdb->posts."` p, 
													`".$wpdb->postmeta."` pm 
												WHERE 
														pm.post_id=p.ID 
													AND 
														p.post_type='product' 
													AND 
														p.post_date>=date_sub(now(),interval 1 hour) 
													AND 
														p.post_modified>=date_sub(now(),interval 1 hour) 
												GROUP BY 
														p.ID
											";
			$limiting = (is_numeric($limited) && $limited>0?$limited:false);												
			if($limiting){
				$slw_default_locations_query .= ' LIMIT '.$limiting;
			}
			//pree($slw_default_locations_query);										
			//pree($location_ids);exit;
			$slw_default_locations_products = $wpdb->get_results($slw_default_locations_query);								
			//pree($slw_default_locations_products);exit;
			if(!empty($slw_default_locations_products)){					
				foreach($slw_default_locations_products as $slw_default_locations_product){
					if(!empty($location_ids)){
						wp_set_object_terms($slw_default_locations_product->ID, $location_ids, 'location');
						
					}
					$product_ids[] = $slw_default_locations_product->ID;
					//pree($slw_default_locations_product->ID);
				}
				//exit;
			}
			
			$timestamp = 'once';
			switch($reconsider){
				default:
					
				break;
				case 'second':
					$timestamp = date('YmdHis');
				break;
				case 'minute':
					$timestamp = date('YmdHi');
				break;
				case 'hour':
					$timestamp = date('YmdH');
				break;
				case 'day':
					$timestamp = date('Ymd');
				break;
				case 'month':
					$timestamp = date('Ym');
				break;
				case 'year':
					$timestamp = date('Y');
				break;				
			}
			
			$today_slw_cron_sniffed = '_slw_cron_sniffed_'.$timestamp;
			
			$q = "DELETE FROM $wpdb->postmeta WHERE meta_key LIKE '_slw_cron_sniffed_%' AND meta_value!='".$timestamp."'".($product_id?" AND post_id='$product_id'":'');		
			if($cron){ pree($q); }
			//wc_slw_logger($q);
			$wpdb->query($q);
			
			$args = array(
				'numberposts' => $limit,
				'post_type' => 'product',
				'post_status' => 'publish',
				'meta_query' => array(
					array(
						'key'       => $today_slw_cron_sniffed,
						'compare' => 'NOT EXISTS'
					)
				),
				'date_query' => array(
					'relation'=>'OR',
					array(
						'column' => 'post_date',
						'after'     => date('Y-m-d', strtotime('-1 day')).'',
						'before'    => date('Y-m-d').'',
						'inclusive' => true,
					),
					array(
						'column' => 'post_modified',
						'after'     => date('Y-m-d', strtotime('-1 day')).'',
						'before'    => date('Y-m-d').'',
						'inclusive' => true,
					),
				),
			);
			if($product_id || (is_array($product_ids) && !empty($product_ids))){

				if($product_id){
					$args['include'] = array($product_id);
				}elseif(is_array($product_ids) && !empty($product_ids)){
					$args['include'] = $product_ids;
				}

				unset($args['meta_query']);
				unset($args['date_query']);
			}
			
			$products = get_posts($args);
			//pree($wpdb->last_query);exit;
			//pree($action);exit;
			//pree($args);
			//pree($products);
			//exit;
			if(!empty($products)){
				if($cron){ echo '<ul>'; }
				foreach($products as $product_post){ if(!is_object($product_post)){ continue; }
	
					//$product_post = get_post($res_obj->ID);
					if($cron){ echo '<li>ID: '.$product_post->ID.'- <a href="'.get_permalink($product_post->ID).'" target="_blank">'.$product_post->post_title.'</a>'; }
					//pree($action);//exit;
					switch($action){
						case 'update-stock':
							//pree($product_post);
							$SlwStockLocationsTab = \SLW\SRC\Classes\SlwStockLocationsTab::save_tab_data_stock_locations_wc_product_save($product_post->ID, $product_post, true, true);
							
							update_post_meta($product_post->ID, $today_slw_cron_sniffed, $timestamp);
							update_post_meta($product_post->ID, '_manage_stock', 'yes');
							
							if($cron){ echo ' stock updated to '.$SlwStockLocationsTab.'.'; }
							
							/*Start - Fix added by Stefan Murawski - 15/02/2023*/

							if($cron){
								$qry = $wpdb->prepare("
													SELECT
															p.ID, sum(pm.meta_value) as total
													FROM
														`".$wpdb->posts."` p
													LEFT JOIN
														`".$wpdb->postmeta."` pm
													ON
														p.ID=pm.post_id
													WHERE
														  p.post_type='product_variation'
													AND
														  p.post_parent='%d'
														AND
															pm.meta_key LIKE %s
													GROUP BY
															p.ID
												",
												$product_post->ID,
												'_stock_at_%'
												);
								//pree($qry);
								$abf = $wpdb->get_results($qry);
								if(!empty($abf)){
									foreach($abf as $subProd) {
										update_post_meta($subProd->ID, '_stock', $subProd->total);
										echo " Product Variant ".$subProd->ID." updated."; 
									}
								}
							}
							
							/*End - Fix added by Stefan Murawski - 15/02/2023*/

						break;

					}	
					if($cron){ echo '</li>'; }
				}
				if($cron){ echo '</ul>'; }
			}
			
			if($cron){ exit; }
			
		}
	}
	if(!function_exists('slw_crons')){
		function slw_crons(){	
		
		
			$current_source = ($_SERVER['REMOTE_ADDR'].'/'.$_SERVER['SERVER_NAME']);
			
			$validated_requests = get_option('slw_cron_request_validated', array());
			
			$validated_requests = (is_array($validated_requests)?$validated_requests:array());
			
			$all_requests = get_option('slw_cron_request_sources', array());
			
			$all_requests = (is_array($all_requests)?$all_requests:array());
			
			
		
			$all_requests[time()] = $current_source;
			
			
			$all_requests = array_unique($all_requests);
			
			update_option('slw_cron_request_sources', $all_requests);

			if((get_option('slw_crons_status')==true)){
				if(!in_array($current_source, $validated_requests)){
					
					_e('Sorry, you are not allowed to proceed.', 'stock-locations-for-woocommerce');
					exit;
				}
			}
			
			slw_update_products();
		}
	}
	
	if(isset($_GET['slw-crons'])){
		
		
		add_action('init', 'slw_crons');
	}
	

	function slw_get_locations($taxonomy='location', $additional_meta_query=array(), $enabled_only=true, $product_id=0){
		
		$args = array('hide_empty' => false, 'meta_query' => array());
		
		switch($taxonomy){
			case 'location':
				if($enabled_only){
					$args['meta_query'][] =	array(
						'key'       => 'slw_location_status',
						'value'     => true,
						'compare'   => '='
					);		
				}
			break;
		}
		
		if(!empty($additional_meta_query)){
			$args['meta_query']['relation'] = 'AND';
			$args['meta_query'][] = $additional_meta_query;
		}
		if($product_id){
			$product_terms = wc_get_product_terms($product_id, $taxonomy);
			
			if(!empty($product_terms)){
				$include_arr = array();
				foreach($product_terms as $product_term){
					$include_arr[] = $product_term->term_id;
				}
				if(!empty($include_arr)){
					$args['include'] = $include_arr;
				}
			}
		}
		
		//pree($taxonomy);
		//pree($args);
		$terms = get_terms($taxonomy, $args);
		//pree($terms);
		
		
		return $terms;
	}

	if(!function_exists('wc_slw_widgets')){
		function wc_slw_widgets($ret_type = ''){
			
			global $slw_widgets_arr;
			$arr = $slw_widgets_arr;
			
			
			switch($ret_type){
				default:
				break;
				case 'array':
					return $arr;
				break;
				case 'fields':
					$ret = array();
					if(!empty($arr)):foreach($arr as $slug=>$wdata):foreach($wdata as $dtype=>$dvalue):
						switch($dtype){
							case 'input':
								$ret[] = $dvalue['name'];
							break;
						}
					endforeach;endforeach;endif;
					return $ret;	
				break;
			}
?>
<?php if(!empty($arr)): ?>
<ul>
<?php foreach($arr as $slug=>$wdata): ?>
	<li data-slug="<?php echo $slug; ?>">
                
        <?php if(!empty($wdata)): ?>
        <ul>
        <?php foreach($wdata as $dtype=>$dvalue): ?>
        	<li data-type="<?php echo $dtype; ?>" data-is="<?php echo is_array($dvalue)?'array':'string'; ?>"><?php echo slw_widget_val($dtype, $dvalue); ?></li>            
        <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    
    </li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
<?php			
		}
	}

	if(!function_exists('slw_parcels_meta_data_callback')){
		function slw_parcels_meta_data_callback($meta_data=array(), $product_id=0, $variation_id=0){
			
			$str = '';
			if(!empty($meta_data)){
				foreach($meta_data as $key=>$val){
					
					switch($key){
						case 'stock_location':
							$location = get_term_by('id', $val, 'location');
							if(is_object($location) && !empty($location)){
								
								$str = '<span data-product="'.$product_id.'" data-variation="'.$variation_id.'"><label><strong>'.__('Location', 'stock-locations-for-woocommerce').':</strong> <u>'.$location->name.'</u></span>';
							}
						break;
					}
				}
			}
			echo $str;
		}
		add_action('wc_os_parcels_meta_data', 'slw_parcels_meta_data_callback', 11, 3);
	}
	add_filter( 'admin_body_class', 'slw_admin_body_class' );
	if(!function_exists('slw_admin_body_class')){
		function slw_admin_body_class($classes){
			global $post;
			$class = '';
			if(is_object($post) && $post->post_type=='product'){
				$product = wc_get_product($post->ID);
				
				if(is_object($product)){
					$class = 'wc-'.$product->get_type().'-product';
				}
			}

			return "$classes $class";
		}
	}

	add_action('admin_head', 'slw_admin_head_init');
	
	if(!function_exists('slw_admin_head_init')){
		function slw_admin_head_init(){
?>

<?php			
		}
	}
	
	function manage_my_category_columns($columns){		
		$columns['slw_location_status'] = '<small>'.__('Enabled/Disabled', 'stock-locations-for-woocommerce').'</small>';
		$columns['slw_location_auto_allocate'] = '<small>'.__('Auto Allocation', 'stock-locations-for-woocommerce').'</small>';
		$columns['slw_location_map_visibility'] = '<small>'.__('Map Visibility', 'stock-locations-for-woocommerce').'</small>';
		$columns['slw_location_priority'] = '<small title="'.__('Higher the number will have higher the priority.', 'stock-locations-for-woocommerce').'">'.__('Priority', 'stock-locations-for-woocommerce').'</small>';
		$columns['slw_default_location'] = '<small title="'.__('Default for new products', 'stock-locations-for-woocommerce').'">'.__('Default Location', 'stock-locations-for-woocommerce').'</small>';
	
		return $columns;
	}
	add_filter('manage_edit-location_columns','manage_my_category_columns');
	
	function manage_category_custom_fields($deprecated, $column_name, $term_id){
		if ($column_name == 'slw_location_status') {
			$slw_location_status = get_term_meta($term_id, 'slw_location_status', true);
			echo '<a data-id="'.$term_id.'" class="slw-location-status '.($slw_location_status?'checked':'').'"><i class="fas fa-check-square slw_location_status-enabled"></i><i class="fas fa-eye-slash slw_location_status-disabled"></i></a>';
		}
		if ($column_name == 'slw_location_auto_allocate') {
			$slw_auto_allocate = get_term_meta($term_id, 'slw_auto_allocate', true);
			echo '<a data-id="'.$term_id.'" class="slw-location-allocate '.($slw_auto_allocate?'checked':'').'"><i class="fas fa-check-square slw_location_allocate-enabled"></i><i class="fas fa-times-circle slw_location_allocate-disabled"></i></a>';
		}	
		if ($column_name == 'slw_location_map_visibility') {
			$slw_map_status = get_term_meta($term_id, 'slw_map_status', true);

			echo '<a data-id="'.$term_id.'" data-status="'.($slw_map_status?'':'yes').'" class="slw-map-status '.($slw_map_status?'checked':'').'"><i class="fas fa-map-marked slw_map_status-enabled"></i><i class="fas fa-map-marked slw_map_status-disabled"></i></a>';
		}				
		
		if ($column_name == 'slw_location_priority') {
			$slw_location_priority = get_term_meta($term_id, 'slw_location_priority', true);
			echo '<a title="'.__('Higher the number will have higher the priority.', 'stock-locations-for-woocommerce').'" data-id="'.$term_id.'" class="slw-location-priority">'.$slw_location_priority.'</a>';
		}	
		if ($column_name == 'slw_default_location') {
			$slw_default_location = get_term_meta($term_id, 'slw_default_location', true);
			echo '<a title="'.__('Default for new products', 'stock-locations-for-woocommerce').'" data-id="'.$term_id.'" class="slw-default-location">'.($slw_default_location?'<i class="fas fa-check-circle"></i>':'').'</a>';
		}	 
	}
	add_filter ('manage_location_custom_column', 'manage_category_custom_fields', 10,3);

    function slw_woocommerce_product_is_in_stock($instock_status=false, $product_id=0, $string=false) {

		global $product, $slw_plugin_settings, $wpdb;
		
		
		
		$product = ($product_id?wc_get_product($product_id):$product);
		
		$type = (is_object($product)?$product->get_type():'');
		
		switch($type){
			case 'variable':
				//$variations = $product->get_children();
				if($product_id>0){
					$variations = $wpdb->get_results("SELECT ID AS variation_id FROM $wpdb->posts WHERE post_parent IN ($product_id) AND post_type='product_variation'");
					
					if(!empty($variations)){
							$variations_stock_status = array();
							foreach($variations as $variation_obj){
								$variation_id = $variation_obj->variation_id;
								$product_variation = wc_get_product($variation_id);
								
								$instock_statuses = (
										(
				
												($product_variation->get_manage_stock() && ($product_variation->get_stock_quantity()>0 || $product_variation->get_backorders()!='no'))
											||
											
												(!$product_variation->get_manage_stock() && $product_variation->get_stock_status()!='outofstock')			
										)
										
								);
								$variations_stock_status[$variation_id] = $instock_statuses;
								
								
							}
	
							$instock_status = (array_sum($variations_stock_status)>0);
					}
				}
			
			break;
			case 'simple':
			
				
				
				$instock_status = (
										(
				
												($product->get_manage_stock() && ($product->get_stock_quantity()>0 || $product->get_backorders()!='no'))
											||
											
												(!$product->get_manage_stock() && $product->get_stock_status()!='outofstock')			
										)
										
								);
				
				
			break;
		}
		
		if($instock_status && $product_id){

			update_post_meta($product_id, '_stock_status', 'instock');

		}
		
		$everything_stock_status_to_instock = array_key_exists('everything_stock_status_to_instock', $slw_plugin_settings);
		if($everything_stock_status_to_instock){ $instock_status = true; }
		
		if($string){
			$instock_status = ($instock_status?'instock':'outofstock');
		}
		
		return $instock_status;
	}
	
	add_filter('woocommerce_product_is_in_stock', 'slw_woocommerce_product_is_in_stock' );
	
	function slw_woocommerce_format_localized_price($value=''){
		$symbol = get_woocommerce_currency_symbol();
		return (substr($value, 0, 1)!=$symbol?$symbol:'').$value;
	}
	//add_filter('woocommerce_format_localized_price', 'slw_woocommerce_format_localized_price');
		
	add_filter( 'woocommerce_get_availability_text', 'slw_change_stock_text', 9, 2 ); //
	add_filter( 'woocommerce_get_availability', 'slw_filter_woocommerce_get_availability', 9, 2 ); 	
	
	function slw_change_stock_text ( $availability, $product) {
		
		global $slw_wc_stock_format;
		
		if($product) {
			$stock = $product->get_stock_quantity();

			$_product = wc_get_product( $product );
			if ( !$_product->is_in_stock() ) {
				$availability = __(  'Out of stock', 'woocommerce' );
			} 
				
			if ( $_product->is_in_stock() && $stock>0) {
				switch($slw_wc_stock_format){
					default:
						$availability = $stock .' '. strtolower(__(  'In stock', 'woocommerce' ));
					break;
					case 'low_amount':
						$_low_stock_amount = get_post_meta($product->get_id(), '_low_stock_amount', true);										
						if($_low_stock_amount && $stock<=$_low_stock_amount){
							$availability = $stock .' '. strtolower(__(  'In stock', 'woocommerce' ));
						}
					break;
					case 'no_amount':
						
					break;
				}
			}else{
				
				$_backorders = get_post_meta($_product->get_id(), '_backorders', true);			
	
				if($_backorders=='yes'){
					$availability = __(  'On backorder', 'woocommerce' );
				}
			}
			

		}
		
		
		return $availability;
	}	
	function slw_filter_woocommerce_get_availability( $array, $product ) { 
		$array['availability'] = apply_filters('woocommerce_get_availability_text', $array['availability'], $product);
		return $array; 
	}; 
			 
	// add the filter 
	
	
	if(!function_exists('slw_archive_qty_box')){
		function slw_archive_qty_box ($slw_id=0) {
			if(is_archive()){
				return '<div class="slw-item-qty-wrapper">
						<div class="slw-item-qty"><a class="decrease"><i class="fas fa-caret-left"></i></a><input type="text" name="qty" id="qty-'.$slw_id.'" value="0" /><a class="increase"><i class="fas fa-caret-right"></i></a></div>
						</div>';
			}
		}
		
	}
	
	add_action( 'woocommerce_product_import_before_import', 'slw_woocommerce_product_import_before_import', 11, 1 );
	
	function slw_woocommerce_product_import_before_import($product_data=array()){
		//wc_slw_pree($product_data);exit;
		//if(!empty($parsed_data)){
			//foreach($parsed_data as $product_data){
				$product_id = (is_array($product_data) && array_key_exists('id', $product_data)?$product_data['id']:0);
				$sku = (is_array($product_data) && array_key_exists('sku', $product_data)?$product_data['sku']:'');

				if(!$product_id && $sku){
					$product_id = wc_get_product_id_by_sku($sku);	
				}
				
				if($product_id>0){
					$location_ids = array();
					if(array_key_exists('meta_data', $product_data)){
						$meta_data = $product_data['meta_data'];
						if(!empty($meta_data)){
							foreach($meta_data as $meta_iter){
								if(is_array($meta_iter)){
									list($meta_key, $meta_val) = array_values($meta_iter);
									
									//wc_slw_logger('debug', $sku.' - '.$product_id.' - ~ - '.$meta_val);	
									
									if(substr($meta_key, 0, strlen('_stock_at_'))=='_stock_at_' && $meta_val!='' && $meta_val>=0){
										
										$location_id = str_replace('_stock_at_', '', $meta_key);
										
										//wc_slw_logger('debug', $sku.' - '.$product_id.' - '.$location_id.' - '.$meta_val);	
										
										if($location_id && !in_array($location_id, $location_ids)){
											$location_ids[] = (int)$location_id;
											update_post_meta( $product_id, '_stock_at_' . $location_id, $meta_val );											
										}
									}
									if(substr($meta_key, 0, strlen('_stock_location_price_'))=='_stock_location_price_' && $meta_val>0){
										$location_id = str_replace('_stock_location_price_', '', $meta_key);
										update_post_meta( $product_id, '_stock_location_price_' . $location_id, $meta_val );										
									}
								}
							}
						}
					}
					
					if(!empty($location_ids)){
						global $wpdb;
						$parent_query = "SELECT post_parent FROM $wpdb->posts WHERE ID='$product_id' AND post_type='product_variation'";
						$parent_product = $wpdb->get_row($parent_query);
						//wc_slw_logger('debug', $parent_product);
						$product_parent_id = $product_id;
						if(is_object($parent_product) && !empty($parent_product)){
							if($parent_product->post_parent>0){
								$product_parent_id = $parent_product->post_parent;
							}
						}
						
						//wc_slw_logger('debug', $product_id);	
						//wc_slw_logger('debug', $product_parent_id.' * '.$product_id.' - '.$wpdb->prefix.' A<br />'.$parent_query);
						//wc_slw_logger('debug', $product_id.' A');
						//wc_slw_logger('debug', $location_ids);
						
						
						update_post_meta($product_id, '_manage_stock', 'yes');
						$locations = wp_get_object_terms($product_parent_id, 'location');
						//wc_slw_logger('debug', $locations);
						if(!empty($locations)){
							foreach($locations as $location_obj){
								if(!in_array($location_obj->term_id, $location_ids)){
									$location_ids[] = $location_obj->term_id;
								}
							}
						}
						//wc_slw_logger('debug', $product_id.' B');
						//wc_slw_logger('debug', $location_ids);
						wp_set_object_terms($product_parent_id, $location_ids, 'location');
						

						$slw_update_products = get_option('slw_update_products', array());
						$slw_update_products = (is_array($slw_update_products)?$slw_update_products:array());
						$slw_update_products[] = $product_id;
						update_option('slw_update_products', $slw_update_products);
						
					}
					
				}
			//}
		//}
		
	}
	
	function slw_update_product_stock_status($product_id=0, $stock_qty=0){
		
		//slw_location_status
		$debug_backtrace = debug_backtrace();
			
		$function = $debug_backtrace[0]['function'];
		$function .= ' / '.$debug_backtrace[1]['function'];
		$function .= ' / '.$debug_backtrace[2]['function'];
		$function .= ' / '.$debug_backtrace[3]['function'];
		$function .= ' / '.$debug_backtrace[4]['function'];		
		
		//wc_slw_logger('debug', $product_id.'='.$stock_qty.' - '.$function);
		
		if(is_numeric($product_id)){
			$stock_qty = (int)$stock_qty;
			update_post_meta($product_id, '_stock', $stock_qty);
			
			if($stock_qty>0)
			update_post_meta($product_id, '_stock_status', 'instock');
			else
			update_post_meta($product_id, '_stock_status', 'outofstock');
		}
		
	}
	function slw_override_stock_quantity( $quantity, $product ) {
			
			if(is_admin()){ return $quantity; }
			
			global $woocommerce;
 
		   $selected_stock_location_id = ((isset($woocommerce->session) && $woocommerce->session->has_session())?$woocommerce->session->get('stock_location_selected'):0);
		   
		   if($selected_stock_location_id>0){

			   $stock_location = \SLW\SRC\Helpers\SlwStockAllocationHelper::getProductStockLocations($product->get_id(), false, $selected_stock_location_id);
	
			   $quantity = $stock_location->quantity;
			   
		   }

			
		  return $quantity;
	}
	
	//add_filter( 'woocommerce_product_get_stock_quantity', 'slw_override_stock_quantity', 10, 2 );
		
	function slw_woocommerce_cart_item_name_callback( $product_name, $cart_item, $cart_item_key ){
		
		return $product_name;
	}
	
	//add_filter( 'woocommerce_cart_item_name', 'slw_woocommerce_cart_item_name_callback', 10, 3 );
	
	
	function slw_woocommerce_get_item_data($item_data, $cart_item ){

		if(!empty($item_data)){
			$stock_location = (array_key_exists('stock_location', $cart_item)?$cart_item['stock_location']:0);
			if($stock_location>0){
				$stock_location_notice = get_term_meta($stock_location, 'slw_location_notice', true);				
				if($stock_location_notice!=''){
					foreach($item_data as $index=>$item){
						switch($item['name']){
							case 'Location':
								$item_data[$index]['display'] .= '<div class="store-notice">'.$stock_location_notice.'</div>';
							break;
						}
					}
				}
			}
		}
		return $item_data;
	}
	
	add_filter( 'woocommerce_get_item_data', 'slw_woocommerce_get_item_data', PHP_INT_MAX, 2 );	
	
	function slw_woocommerce_thankyou( $order_id ) {  

		if ( ! $order_id )
        return;
		
		$_slw_locations_stock_status = get_post_meta($order_id, '_slw_locations_stock_status', true);
		$_slw_locations_stock_status = (is_array($_slw_locations_stock_status)?$_slw_locations_stock_status:array());
		
		$order = wc_get_order( $order_id );
		
		//wc_slw_logger('debug', 'reduce_order_items_locations_stock_on_save: '.'Yes #'.$order_id);

		if( !empty($order) && !empty($order->get_items()) ) {
			// Loop through order items
			foreach ( $order->get_items() as $item_id => $item ) {
				
				$product_id = $item['variation_id'] != 0 ? $item['variation_id'] : $item['product_id'];
				$itemStockLocationTerms = \SLW\SRC\Helpers\SlwStockAllocationHelper::getProductStockLocations( $product_id, false );
				
				foreach ($itemStockLocationTerms as $term) {
					$_slw_locations_stock_status[$product_id][$term->term_id] = get_post_meta($product_id, '_stock_at_' . $term->term_id);
				}
				
			}
			//wc_slw_logger('debug', $_slw_locations_stock_status);
			update_post_meta($order_id, '_slw_locations_stock_status', $_slw_locations_stock_status);
			
		}
	}	
	
	add_action('woocommerce_thankyou', 'slw_woocommerce_thankyou' , 10, 1);	
	
	if(!function_exists('wc_slw_edit_stocks')){
		function wc_slw_edit_stocks($slw_order_id, $item_id){
			
			$str = '<a href="https://www.youtube.com/embed/Q1Lq-cbv2hE" target="_blank" class="slw_edit_stocks" title="'.__('This is a premium feature!', 'stock-locations-for-woocommerce').'"></a>';
			
			return $str;
			
		}
	}
	
	include_once('functions-api.php');
	include_once('filter-hooks.php');