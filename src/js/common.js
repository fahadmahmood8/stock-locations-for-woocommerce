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
		if(slw_frontend.slw_location_selection_popup!=''){
			$.blockUI({message:slw_frontend.slw_location_selection_popup});
		}
	}, 2000);
	
});
