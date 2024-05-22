<?php
/**
 * SLW Mail Helper Class
 * @since 1.3.0
 */

namespace SLW\SRC\Helpers;

use SLW\SRC\Helpers\SlwStockAllocationHelper;

if ( !defined('WPINC') ) {
	die;
}

if ( !class_exists('SlwMailHelper') ) {

	class SlwMailHelper
	{

		public static function stock_allocation_notification( $term, $item, $quantity )
		{
			//wc_slw_logger('debug', 'stock_allocation_notification: '.'Yes');
			if( empty($term) || empty($item) || empty($quantity) ) return;
			
			if (!apply_filters('allow_stock_allocation_notification', $term, $item, $quantity, true)) {
				return;
			}

			// get location meta
			$item_location_meta = SlwStockAllocationHelper::getLocationMeta( $term->term_id );

			// get plugin settings
			$plugin_settings = get_option( 'slw_settings' );
			// Send email notification to location
			$proceed = ( isset($plugin_settings['location_email_notifications']) && $plugin_settings['location_email_notifications'] == 'on' && isset($item_location_meta['slw_auto_allocate']) && $item_location_meta['slw_auto_allocate'] == '1' && isset($item_location_meta['slw_location_email']) && !empty($item_location_meta['slw_location_email']) && is_email($item_location_meta['slw_location_email']) );
			//$proceed_inspect = isset($plugin_settings['location_email_notifications']).' - '.$plugin_settings['location_email_notifications'].' - '.isset($item_location_meta['slw_auto_allocate']).' - '.$item_location_meta['slw_auto_allocate'].' - '.isset($item_location_meta['slw_location_email']).' - '.$item_location_meta['slw_location_email'].' - '.is_email($item_location_meta['slw_location_email']);
			//wc_slw_logger('debug', '$proceed: '.$proceed.' ('.$proceed_inspect.')');
			if($proceed){
				$to = sanitize_slw_data( $item_location_meta['slw_location_email'] );
				$subject = __('Stock allocated in', 'stock-locations-for-woocommerce') . ' ' . $term->name;
				$subject = apply_filters('slw_stock_allocation_notification_subject', $subject, $term, $quantity, $item);
				
				$slw_stock_allocation_notification_message = sprintf(__('This is an automatically generated notification informing that the quantity of <strong>%1$d</strong> was allocated for the item <strong>%2$s</strong> with the ID <strong>%3$d</strong>.', 'stock-locations-for-woocommerce'), $quantity, $item->get_name(), $item->get_id());
				$message = apply_filters('slw_stock_allocation_notification_message', $slw_stock_allocation_notification_message, $quantity, $item->get_name(), $item->get_id());
				
				$body = apply_filters( 'slw_stock_allocation_notification_message', $message, $term, $quantity, $item );
				$headers = array('Content-Type: text/html; charset=UTF-8');
				add_filter('wp_mail_content_type', function( $content_type ) { return 'text/html'; }); // forces wp_mail() to use 'text/html'

				$mail_output = wp_mail( $to, $subject, $body, $headers ); // try to send email
				wc_update_order_item_meta( $item->get_id(), '_slw_notification_mail_output', $mail_output );
			}
		}

	}
	
}