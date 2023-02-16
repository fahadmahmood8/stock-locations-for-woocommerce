<?php
	
	add_filter('stock_location_selected_warning', 'stock_location_selected_warning_callback', 9, 3);
	
	function stock_location_selected_warning_callback($html='', $new_id=0, $old_id=0){
		
		return $html;
		
	}
	
	add_filter('slw_notice_msg', 'slw_notice_msg_callback', 9, 5);
	
	function slw_notice_msg_callback($html='', $product_id=0, $product_name='', $location_id=0, $product_locations=array()){

		return $html;
		
	}	
	
	add_filter('slw-map-location-label', 'slw_map_location_label_callback', 9, 3);
	
	function slw_map_location_label_callback($str='', $name='', $location_id=0){

		return $str;
		
	}		
	
	add_filter('slw-map-location-name', 'slw_map_location_name_callback', 9, 3);
	
	function slw_map_location_name_callback($str='', $label='', $location_id=0){

		return $str;
		
	}	
	
	add_filter('slw_output_product_locations_for_shortcode', 'slw_output_product_locations_for_shortcode_callback', 9, 4);
	
	function slw_output_product_locations_for_shortcode_callback($product, $locations, $values, $output){

		return $output;
		
	}		
	