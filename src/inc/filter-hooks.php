<?php
	
	add_filter('stock_location_selected_warning', 'stock_location_selected_warning_callback', 9, 3);
	
	if(!function_exists('stock_location_selected_warning_callback')){
		function stock_location_selected_warning_callback($html='', $new_id=0, $old_id=0){
			
			return $html;
			
		}
	}
	
	add_filter('slw_notice_msg', 'slw_notice_msg_callback', 9, 5);
	
	if(!function_exists('slw_notice_msg_callback')){
		function slw_notice_msg_callback($html='', $product_id=0, $product_name='', $location_id=0, $product_locations=array()){
	
			return $html;
			
		}	
	}
	
	add_filter('slw-map-location-label', 'slw_map_location_label_callback', 9, 3);
	
	if(!function_exists('slw_map_location_label_callback')){
		function slw_map_location_label_callback($str='', $name='', $location_id=0){
	
			return $str;
			
		}	
	}
		
	add_filter('slw-map-location-name', 'slw_map_location_name_callback', 9, 3);
	
	
	if(!function_exists('slw_map_location_name_callback')){
		function slw_map_location_name_callback($str='', $label='', $location_id=0){
	
			return $str;
			
		}	
	}
	
	add_filter('slw_output_product_locations_for_shortcode', 'slw_output_product_locations_for_shortcode_callback', 9, 4);
	
	if(!function_exists('slw_output_product_locations_for_shortcode_callback')){
		function slw_output_product_locations_for_shortcode_callback($product, $locations, $values, $output){
	
			return $output;
			
		}	
	}
	
	add_filter('slw_location_selection_popup_display', 'slw_location_selection_popup_display_callback', 9, 2);
	
	if(!function_exists('slw_location_selection_popup_display_callback')){
		function slw_location_selection_popup_display_callback($is_front_page=false, $is_shop=false){
			//pree($is_front_page);pree($is_shop);
			$output = ($is_front_page || $is_shop);
			return (boolean)$output;
			
		}			
	}
	
	add_filter('allow_stock_allocation_notification', 'allow_stock_allocation_notification_callback', 9, 4);
	
	if(!function_exists('allow_stock_allocation_notification_callback')){
		function allow_stock_allocation_notification_callback($term, $item, $quantity, $output = true){
			return (boolean)$output;			
		}			
	}
	
	
	