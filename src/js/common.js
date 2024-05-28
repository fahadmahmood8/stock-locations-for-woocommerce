// JavaScript Document
jQuery(document).ready(function($){
	
	$('select[id^="slw-product-"]').on('change', function(){
		var v = $(this).val();
		if(v!=''){
			$(this).parent().find('.slw-variable-btn').prop('href', slw_frontend.slw_term_add_to_cart_url+v).css({'visibility':'visible','display':'inline-block'});
		}else{
			$(this).parent().find('.slw-variable-btn').css({'visibility':'hidden','display':'none'});
		}
	});		
		
	$('body').on('click', '.slw-dismiss-notice', function(){
		$(this).closest('.woocommerce-info').fadeOut().delay(3000).remove();
	});

	
	setTimeout(function(){
		if(slw_frontend.slw_location_selection_popup!='' && typeof slw_frontend.stock_location_selected==null){
			$.blockUI({message:slw_frontend.slw_location_selection_popup});
			$('.blockUI:before').hide();
			
		}
	}, 2000);
	
	$('body').on('click', 'div.slw-location-selection-popup ul li a', function(event){
		
		event.preventDefault();
		
		var location_id = $(this).parent().data('id');
		var location_url = $(this).attr('href');
		var data = {
				'action': 'slw_update_stock_location_session',
				'location_id': location_id,
				'slw_nonce_field': slw_frontend.nonce
		};
		$.post(slw_frontend.ajaxurl, data, function(response) {
			//document.location.href = location_url;
			document.location.reload();
		});
		
	});
	
});
