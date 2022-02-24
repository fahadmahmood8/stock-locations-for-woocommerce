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
	  echo '<pre>';
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

				
				
				if(is_array($data) && !empty($data)){
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
	// get locations total stock
	$locations_total_stock = \SLW\SRC\Helpers\SlwProductHelper::get_product_locations_stock_total( $id );

	// update stock
	update_post_meta( $id, '_stock', $locations_total_stock );

	// update stock status
	\SLW\SRC\Helpers\SlwProductHelper::update_wc_stock_status( $id );

}, 10, 1 );

if(!function_exists('slw_quantity_format')){
	function slw_quantity_format($data){
		$plugin_settings = get_option( 'slw_settings' );
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

		
		if(isset($_GET['post']) && is_numeric($_GET['post']) && $_GET['post']>0 && isset($_GET['debug'])){
			
			$order = get_post(sanitize_slw_data($_GET['post']));
			if(is_object($order) && $order->post_type=='shop_order'){
				
				if(isset($_GET['get_keys'])){
					pree(get_post_meta($order->ID));
				}
				if(isset($_GET['get_items'])){
					$order_obj = wc_get_order($order->ID);
					foreach($order_obj->get_items() as $item_key=>$item_data){
						pree($item_key);
						pree($item_data);
					}
				}
				
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
	if(!function_exists('wc_slw_widgets')){
		function wc_slw_widgets($ret_type = ''){
			
			$arr = array(
				'slw-map' => array(
					'type' => __('Premium', 'stock-locations-for-woocommerce'),
					'input' => array('name'=>'slw-google-api-key', 'type'=>'text', 'caption'=>__('Please enter Google API key here', 'stock-locations-for-woocommerce')),
					'title' => __('Google Map for Stock Locations', 'stock-locations-for-woocommerce'),
					'description' => __('This widget will detect the user location and zoom to current user latitude longitude by default.', 'stock-locations-for-woocommerce'),
					'shortcode' => '[SLW-MAP search-field="yes" locations-list="yes" map="yes"]',					
					'screenshot' => SLW_PLUGIN_URL.'images/slw-map-thumb.png',
					
				)
			);
			
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
        	<li data-type="<?php echo $dtype; ?>"><?php echo slw_widget_val($dtype, $dvalue); ?></li>            
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
	if(!function_exists('slw_widget_val')){
		function slw_widget_val($type, $val){
			$ret = $val;
			switch($type){
				case 'screenshot':
					$ret = '<a href="'.$val.'"><img src="'.$val.'" /></a>';
				break;
				case 'input':
					$db_val = get_option($val['name']);
					$ret = '<label>'.$val['caption'].':</label><input type="'.$val['type'].'" name="'.$val['name'].'" id="'.$val['name'].'" value="'.$db_val.'" />';
				break;
				default:
					$ret = '<span data-val="'.$val.'">'.$val.'</span>';
				break;
			}
			return $ret;
		}
	}
	include_once('functions-api.php');