<?php
	
	add_filter('stock_location_selected_warning', 'stock_location_selected_warning_callback', 10, 3);
	
	function stock_location_selected_warning_callback($html='', $new_id=0, $old_id=0){
		
		return $html;
		
	}
	
	add_filter('slw_notice_msg', 'slw_notice_msg_callback', 10, 5);
	
	function slw_notice_msg_callback($html='', $product_id=0, $product_name='', $location_id=0, $product_locations=array()){

		return $html;
		
	}	