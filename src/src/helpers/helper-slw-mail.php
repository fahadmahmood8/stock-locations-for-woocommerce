<?php
/**
 * SLW Mail Helper Class
 * @since 1.3.0
 */

namespace SLW\SRC\Helpers;

if ( !defined('WPINC') ) {
	die;
}

if ( !class_exists('SlwMailHelper') ) {

	class SlwMailHelper
	{

		public static function stock_allocation_notification( $term, $item, $quantity )
		{
			if( empty($term) || empty($item) || empty($quantity) ) return;

			// get location meta
			$item_location_meta = SlwStockAllocationHelper::getLocationMeta( $term->term_id );

			// get plugin settings
			$plugin_settings = get_option( 'slw_settings' );

			// Send email notification to location
			if( isset($plugin_settings['location_email_notifications']) && $plugin_settings['location_email_notifications'] == 'on' && isset($item_location_meta['slw_auto_allocate']) && $item_location_meta['slw_auto_allocate'] == '1' && isset($item_location_meta['slw_location_email']) && !empty($item_location_meta['slw_location_email']) && is_email($item_location_meta['slw_location_email']) ) {
				$to = $item_location_meta['slw_location_email'];
				$subject = __('Stock allocated in', 'stock-locations-for-woocommerce') . ' ' . $term->name;
				$message = sprintf(__('This is an automatically generated notification informing that the quantity of <strong>%1$d</strong> was allocated for the item <strong>%2$s</strong> with the ID <strong>%3$d</strong>.', 'stock-locations-for-woocommerce'), $quantity, $item->get_name(), $item->get_id());
				$body = apply_filters( 'slw_stock_allocation_notification_message', $message, $term, $quantity, $item );
				$headers = array('Content-Type: text/html; charset=UTF-8');
				add_filter('wp_mail_content_type', function( $content_type ) { return 'text/html'; }); // forces wp_mail() to use 'text/html'

				$mail_output = wp_mail( $to, $subject, $body, $headers ); // try to send email
				wc_update_order_item_meta( $item->get_id(), '_slw_notification_mail_output', $mail_output );
			}
		}

	}
	
}