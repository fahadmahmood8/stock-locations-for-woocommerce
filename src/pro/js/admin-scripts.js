// JavaScript Document
jQuery(document).ready(function($){
	$('body').on('click', '.slw_edit_stocks', function(){
		var ask = confirm(slw_admin_scripts.wc_slw_stock_reset_msg);
		if(ask){
			$.blockUI({message:''});
			var order_id = $(this).data('order');
			var item_id = $(this).data('id');
			var data = {
					'action': 'wc_slw_edit_stock_values',
					'order_id': order_id,
					'item_id': item_id,
					'slw_nonce_field': slw_admin_scripts.nonce
			};
			$.post(slw_admin_scripts.ajaxurl, data, function(response) {
				//document.location.href = location_url;
				document.location.reload();
			});
		}else{
			
		}
	});
});