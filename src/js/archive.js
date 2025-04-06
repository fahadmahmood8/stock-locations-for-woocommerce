// JavaScript Document
jQuery(document).ready(function($){
	
	
	$('body.archive.tax-location').on('change', 'div.slw_stock_location_selection input[name^="slw_add_to_cart_item_stock_location"]', function(){
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
	
	$('body.archive.tax-location').on('click', '.ajax_add_to_cart', function(event){
		event.preventDefault();
		
		//console.log('AAA');
	
		var obj = $(this);
		var product_id = obj.data('product_id') || 0;
		var variation_id = obj.data('variation_id') || 0;
		var location_id = 0;
		var location_obj;
		var location_qty = 0;
		var cart_qty = 0;
		var href = obj.prop('href');
		var urlParams = new URLSearchParams(href.split('?')[1] || '');
		var product_variation_id = (variation_id?variation_id:product_id);
		

	
		if (slw_frontend.wc_slw_pro == true && slw_frontend.show_in_product_page == 'yes_radio') {
			location_obj = obj.parents().eq(3).find('input[name^="slw_add_to_cart_item_stock_location[' + product_id + ']"]:checked');
		} else {
			location_obj = obj.parents().eq(3).find('select[name^="slw_add_to_cart_item_stock_location[' + product_id + ']"] option:selected');
		}
		
	
		location_id = location_obj?.val() 
						?? (typeof slw_frontend.slw_term_id !== 'undefined' ? slw_frontend.slw_term_id : location_id) 
						?? (urlParams.has('stock-location') ? parseInt(urlParams.get('stock-location')) : location_id);

		
		
		
		location_qty = (typeof location_obj!='undefined'?location_obj.data('quantity'):0);
		location_qty = (location_qty?location_qty:slw_frontend.stock_quantity?.[product_variation_id]?.[location_id] ?? 0);


			
		cart_qty = slw_get_location_qty(product_id, variation_id, location_id);

		
		
	
		if (cart_qty && location_qty <= cart_qty) {
			//console.log(location_qty+' <= '+cart_qty);
			alert(slw_frontend.slw_archive_items_halt_msg);
		} else {
			
			
			var quantityInput = obj.parents().eq(1).find('input.qty[name="quantity"]');
			var quantity = 1;
			
			if (quantityInput.length) {
				quantity = parseInt(quantityInput.val());
				href += (href.indexOf('?') !== -1 ? '&' : '?') + 'quantity=' + encodeURIComponent(quantity);
			}
			
			//console.log(href+' - '+location_qty+' <= '+cart_qty);
			//return;
			
			if (cart_qty && quantity > (location_qty-cart_qty)) {
				alert(slw_frontend.slw_archive_items_max_msg);
				return;
			}
			
			
			$.blockUI({ message: '' });
			$.post(href, {}, function () {
				$.unblockUI();
			});

		}
	});

	
	$('body.archive.tax-locations').on('click', '.ajax_add_to_cart', function(event){
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
		
		//console.log(location_qty+' <= '+cart_qty);
			
		if(location_qty<=cart_qty){
			alert(slw_frontend.slw_archive_items_halt_msg);
		}else{		
			$.blockUI({message:''});
			$.post($(this).prop('href'), {}, function(){ 
				$.unblockUI();
				//document.location.href = slw_frontend.cart_url; 
				//document.location.reload();
			}); 			
		}
	});
	
	$('body.archive.tax-location').on('click', 'div.slw-item-qty a', function(){
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
	function slw_get_location_qty(product_id, variation_id = 0, location_id = 0) {

		product_id = parseInt(product_id);
		variation_id = parseInt(variation_id);
		location_id = parseInt(location_id);
		
		var proceed = (
			typeof slw_frontend !== 'undefined' &&
			slw_frontend.slw_cart_items &&
			slw_frontend.slw_cart_items[product_id] &&
			typeof slw_frontend.slw_cart_items[product_id] !== 'undefined' &&
			Array.isArray(slw_frontend.slw_cart_items[product_id]) &&
			slw_frontend.slw_cart_items[product_id][variation_id] &&
			slw_frontend.slw_cart_items[product_id][variation_id][location_id] !== 'undefined'
		);
		//console.log('proceed: '+proceed);
		if (proceed){
			return slw_frontend.slw_cart_items[product_id][variation_id][location_id];
		}
		return 0; // fallback if not found
	}
	
	
});
