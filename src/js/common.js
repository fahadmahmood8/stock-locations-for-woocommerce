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
	
	$('body.archive').on('change', 'div.slw_stock_location_selection input[name^="slw_add_to_cart_item_stock_location"]', function(){
		var obj = $(this).parents().eq(2).find('.ajax_add_to_cart');		
		var qty_wrapper = $(this).parents().eq(2).find('.slw-item-qty-wrapper');
		var stock_location_qty = 0;
		

		if(obj.length>0){
			var href = obj.prop('href');
			var url = new URL(href);
			var search_params = url.searchParams;
			search_params.set('stock-location', $(this).val());
			var qty = 0;
			if(qty_wrapper.length>0 && qty_wrapper.find('.slw-item-qty input[name="qty"]').length>0){	
				qty = parseInt(qty_wrapper.find('.slw-item-qty input[name="qty"]').val());
				
				qty_wrapper.find('.slw-item-qty input[name="qty"]').prop('max', $(this).data('quantity'));
			}
			if(qty>0){
				
				stock_location_qty = parseInt(qty_wrapper.find('.slw-item-qty input[name="qty"]').prop('max'));
				
				if(qty>stock_location_qty){
					alert(slw_frontend.slw_archive_items_max_msg);
					qty = stock_location_qty;
					qty_wrapper.find('.slw-item-qty input[name="qty"]').val(qty);
					
				}
				
				
				
				search_params.set('quantity', qty);
			}
			
			
			url.search = search_params.toString();			
			obj.prop('href', url.toString());
			
		}
	});
	
	$('body.archive').on('click', '.ajax_add_to_cart', function(event){
		event.preventDefault();
		
		var obj = $(this);
		var product_id = (typeof obj.data('product_id')!='undefined'?obj.data('product_id'):0);
		var variation_id = (typeof obj.data('variation_id')!='undefined'?obj.data('variation_id'):0);
		var location_obj;
		var location_id = 0;
		var location_qty = 0;
		var cart_qty = 0;
		
		if(slw_frontend.wc_slw_pro==true && slw_frontend.show_in_product_page=='yes_radio'){
			location_obj = $('input[name^="slw_add_to_cart_item_stock_location['+product_id+']"]:checked');
		}else{
			location_obj = $('select[name^="slw_add_to_cart_item_stock_location['+product_id+']"] option:selected');
		}
		
		location_id = (typeof location_obj!='undefined'?location_obj.val():0);
		location_qty = (typeof location_obj!='undefined'?location_obj.data('quantity'):0);
		
		if(typeof slw_frontend.slw_cart_items[product_id]!='undefined'){
			if(typeof slw_frontend.slw_cart_items[product_id][variation_id]!='undefined'){
				if(typeof slw_frontend.slw_cart_items[product_id][variation_id][location_id]!='undefined'){
					cart_qty = slw_frontend.slw_cart_items[product_id][variation_id][location_id];
				}
			}
		}
			
		if(location_qty<=cart_qty){
			alert(slw_frontend.slw_archive_items_halt_msg);
		}else{		
			$.blockUI({message:''});
			$.post($(this).prop('href'), {}, function(){ 
				//document.location.href = slw_frontend.cart_url; 
				document.location.reload();
			}); 			
		}
	});
	
	$('body.archive').on('click', 'div.slw-item-qty a', function(){
		var increase = $(this).hasClass('increase');
		var qty = $(this).parent().find('input');
		var qty_val = $.trim(qty.val());
		qty_val = (qty_val>=0?qty_val:0);
		
		var obj = $(this).parents().eq(3).find('.ajax_add_to_cart');
		var product_id = (typeof obj.data('product_id')!='undefined'?obj.data('product_id'):0);
		var variation_id = (typeof obj.data('variation_id')!='undefined'?obj.data('variation_id'):0);
		var location_obj;
		var location_id = 0;
		var location_qty = 0;
		var cart_qty = 0;
		
		var qty_wrapper = $(this).parents().eq(3).find('.slw-item-qty-wrapper');
						
		if(slw_frontend.wc_slw_pro==true && slw_frontend.show_in_product_page=='yes_radio'){
			location_obj = $('input[name^="slw_add_to_cart_item_stock_location['+product_id+']"]:checked');
		}else{
			location_obj = $('select[name^="slw_add_to_cart_item_stock_location['+product_id+']"] option:selected');
		}

		location_id = (typeof location_obj!='undefined'?location_obj.val():0);
		location_qty = (typeof location_obj!='undefined'?location_obj.data('quantity'):0);
								
		if(typeof slw_frontend.slw_cart_items[product_id]!='undefined'){
			if(typeof slw_frontend.slw_cart_items[product_id][variation_id]!='undefined'){
				if(typeof slw_frontend.slw_cart_items[product_id][variation_id][location_id]!='undefined'){
					cart_qty = slw_frontend.slw_cart_items[product_id][variation_id][location_id];
				}
			}
		}
		
		
		if(qty_wrapper.length>0 && qty_wrapper.find('.slw-item-qty input[name="qty"]').length>0){	
			qty_wrapper.find('.slw-item-qty input[name="qty"]').prop('max', location_qty);
		}
		
		//console.log(product_id+' - '+variation_id+' - '+location_id+' - '+location_qty+' - '+qty_val+'<='+cart_qty);
		if(cart_qty>0 && qty_val<=cart_qty){
			alert(slw_frontend.slw_archive_items_halt_msg);
		}else{
			

			
			
			if(increase){
				if(qty_val<location_qty){
					qty_val++;				
				}
			}else{
				qty_val--;
			}
			qty_val = (qty_val>=0?qty_val:0);
			qty.val(qty_val);
			
		
		}
		
		
		slw_archive_elements_refresh($(this).parents().eq(3));
		
	});
	
	function slw_archive_elements_refresh(obj){
		if(slw_frontend.wc_slw_pro==true && slw_frontend.show_in_product_page=='yes_radio'){
			obj.find('input[name^="slw_add_to_cart_item_stock_location"]:checked').change();
		}else{
			obj.find('select[name^="slw_add_to_cart_item_stock_location"] option:selected').change();
		}
	}
	
	

});
