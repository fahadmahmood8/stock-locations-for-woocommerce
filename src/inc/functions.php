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
		
		$slw_logger = array();
		
		$debug_backtrace = debug_backtrace();
		$function = $debug_backtrace[1]['function'];
		$function .= (array_key_exists(2, $debug_backtrace)?' / '.$debug_backtrace[2]['function']:'');
		$function .= (array_key_exists(3, $debug_backtrace)?' / '.$debug_backtrace[3]['function']:'');
		$function .= (array_key_exists(4, $debug_backtrace)?' / '.$debug_backtrace[4]['function']:'');
		$function .= (array_key_exists(5, $debug_backtrace)?' / '.$debug_backtrace[5]['function']:'');
		
		switch($type){
			case 'debug':
				$slw_logger = get_option('slw_logger');
				
				$slw_logger = is_array($slw_logger)?$slw_logger:array();
				
				
				if(is_array($data) && !empty($data)){
					$slw_logger[] = $data;
					$slw_logger[] = '<small>('.$function.')</small> - '.date('d M, Y h:i:s A');
					update_option('slw_logger', $slw_logger);
				}else{				
					$slw_logger[] = $data.' <small>('.$function.')</small> - '.date('d M, Y h:i:s A');
					if($data){
						update_option('slw_logger', $slw_logger);
					}
				}
				
				
			break;
		}
		
		return $slw_logger;
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