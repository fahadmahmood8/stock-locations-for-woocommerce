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
});
