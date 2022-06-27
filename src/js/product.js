	function slw_update_input_and_price(obj, $){
		
		var location_id = obj.val();
		if(slw_frontend.wc_slw_pro==true && slw_frontend.show_in_product_page=='yes_radio'){
			var qty_obj = $('input[name^="slw_add_to_cart_item_stock_location"]:checked');
		}else{
			var qty_obj = $('select[name^="slw_add_to_cart_item_stock_location"] option:selected');
		}

		var product_id = slw_frontend.product_id;
		var variation_id = 0;
		if(typeof $(".woocommerce-variation-add-to-cart").find('.variation_id').val()!='undefined'){
			variation_id = $(".woocommerce-variation-add-to-cart").find('.variation_id').val();
		}
		var product_item_id = (variation_id?variation_id:product_id);
		var backorders_allowed = slw_frontend.allow_backorder[product_item_id];
		
		var cart_qty = (
						(	
								typeof slw_frontend.slw_cart_items[product_id]!='undefined'
							&&
								typeof slw_frontend.slw_cart_items[product_id][variation_id]!='undefined'
							&&
								typeof slw_frontend.slw_cart_items[product_id][variation_id][location_id]!='undefined'
						
						)?slw_frontend.slw_cart_items[product_id][variation_id][location_id]:0);
		
		var location_qty = qty_obj.data('quantity');
		
		var halt = (location_id==0 || location_qty<1 || location_qty<=cart_qty);

		if(halt){
			if(backorders_allowed=='no'){
				if($('button[name="add-to-cart"]').length>0){ $('button[name="add-to-cart"]').prop('disabled', true); }			
			}
		}else{
			if($('button[name="add-to-cart"]').length>0){ $('button[name="add-to-cart"]').prop('disabled', false); }
		}
		
		if($('input[name="quantity"]').length>0){
			var qty_now = $('input[name="quantity"]').val();
			if(qty_now<=parseFloat(slw_frontend.stock_quantity_sum)){
			}else{
				$('input[name="quantity"]').val(slw_frontend.stock_quantity_sum);
			}
		}
		
		var price_dom = $('.woocommerce-variation-price .woocommerce-Price-amount.amount');
		
		var price = qty_obj.data('price');
		
		if(price && (typeof slw_frontend.product_stock_price_status!='undefined' && slw_frontend.product_stock_price_status=='on')){
			var price_html = '<bdi><span class="woocommerce-Price-currencySymbol">'+slw_frontend.currency_symbol+'</span>'+price+'</bdi>';
	
			if(price_dom.length>0){
				price_dom.html(price_html);
			}else{
				var price_doms = $('.product-type-simple .summary .price .woocommerce-Price-amount.amount');
				if(price_doms.length>0){
					$.each(price_doms, function(i, v){
						if(!obj.parent().is('del')){
							obj.html(price_html);
						}
					});
				}
			}
		}		
	}
(function($){

	// Init after DOM is ready
	$(document).ready(function() {
		init();
	});

	// Functions to initiate
	function init() {
		slwVariableProductVariationFound();
	}
	

	function slwVariableProductVariationFound()
	{
		$('select#slw_item_stock_location_variable_product').hide();
		$(document).on( 'found_variation', function( event ) {
			event.preventDefault();
			
			//if(slw_frontend.stock_locations>0)
			$('.woocommerce-variation-availability p.stock').hide();
			
			let variation_id = $(".woocommerce-variation-add-to-cart").find('.variation_id').val();
			let product_id   = $(".woocommerce-variation-add-to-cart").find('input[name="product_id"]').val();
			var stock_quantity_sum = 0;
			
			$.ajax({
				type: 'POST',
				url: slw_frontend.ajaxurl,
				data: {
					action:       'get_variation_locations',
					security:     $('#woocommerce-cart-nonce').val(),
					variation_id: variation_id,
					product_id:   product_id,					
				},
				success ( response ) {
					if( response.success ) {
						
						if(slw_frontend.wc_slw_pro==true && slw_frontend.show_in_product_page=='yes_radio'){
							
							var wrapper = $('div.slw_stock_location_selection');
							
							$.each(response.data.stock_locations, function(i) {
								var obj = response.data.stock_locations[i];
								if( obj.quantity < 1 && obj.allow_backorder != 1 ) {
									
								} else {
									let selected = false;
									if( obj.term_id == response.data.default_location && obj.quantity>0) {
										selected = true;
									}
									
									
									//new Option( obj.name, obj.term_id, selected, selected )
									var product_stock_location_name = obj.name;
									if(typeof slw_frontend.product_stock_price_status!='undefined' && slw_frontend.product_stock_price_status=='on'){								
										//product_stock_location_name += ' '+slw_frontend.currency_symbol+''+obj.price;
									}
									var option_str = '<label for="slw-location-'+obj.term_id+'"><input name^="slw_add_to_cart_item_stock_location" id="slw-location-'+obj.term_id+'" type="radio" class="'+(obj.quantity>0?'has-stock':'')+'" data-backorder="'+obj.backorder_allowed+'" data-price="'+obj.price+'" data-quantity="'+obj.quantity+'" value="'+obj.term_id+'" '+(selected?'selected="selected"':'')+' />'+product_stock_location_name+'</label>';
									wrapper.append(option_str);
									
									stock_quantity_sum += parseInt(obj.quantity);
								}
							});
							
						}else{
						
							$('select#slw_item_stock_location_variable_product').find('option[value!="0"]').remove();
							$('select#slw_item_stock_location_variable_product').prop('required',true);
							$.each(response.data.stock_locations, function(i) {
								var obj = response.data.stock_locations[i];
								if( obj.quantity < 1 && obj.allow_backorder != 1 ) {
									$('select#slw_item_stock_location_variable_product').append('<option data-price="" data-quantity="" disabled="disabled">'+obj.name+'</option>');
								} else {
									let selected = false;
									if( obj.term_id == response.data.default_location && obj.quantity>0) {
										selected = true;
									}
									
									
									//new Option( obj.name, obj.term_id, selected, selected )
									var product_stock_location_name = obj.name;
									if(typeof slw_frontend.product_stock_price_status!='undefined' && slw_frontend.product_stock_price_status=='on'){								
										product_stock_location_name += ' '+slw_frontend.currency_symbol+''+obj.price;
									}
									var option_str = '<option class="'+(obj.quantity>0?'has-stock':'')+'" data-backorder="'+obj.backorder_allowed+'" data-price="'+obj.price+'" data-quantity="'+obj.quantity+'" value="'+obj.term_id+'" '+(selected?'selected="selected"':'')+'>'+product_stock_location_name+'</option>';
									$('select#slw_item_stock_location_variable_product').append(option_str);
									
									stock_quantity_sum += parseInt(obj.quantity);
								}
							});
							
							//slw_frontend.stock_quantity_sum = stock_quantity_sum;
							
							$('select#slw_item_stock_location_variable_product').show();
							
						}

						slw_add_to_cart_item_stock_location_trigger();
						

					} else {
						
						return;
					}
					if($('.woocommerce-variation-availability p.stock').length>0){
						$('.woocommerce-variation-availability p.stock').show();
					}
				},
				error ( xhr, error, status ) {
					//console.log( error, status );
				}
			});
		});
	}
	
	
	$('body').on('change', 'select#slw_item_stock_location_variable_product, select#slw_item_stock_location_simple_product, input[name^="slw_add_to_cart_item_stock_location"]', function(){
		
		slw_add_to_cart_item_stock_status_update();
		
	});
	
	function slw_add_to_cart_item_stock_location_trigger(){
		
		
		if(slw_frontend.wc_slw_pro==true && slw_frontend.show_in_product_page=='yes_radio'){
			$('input[type="radio"][name^="slw_add_to_cart_item_stock_location"]').change();
		}else if($('select[name^="slw_add_to_cart_item_stock_location"]').length>0){
			$('select[name^="slw_add_to_cart_item_stock_location"]').change();
		}
		slw_add_to_cart_item_stock_status_update();
	}
	
	function slw_add_to_cart_item_stock_status_update(){
		
		var obj = $('p.stock').eq(0);

		
		
		if(slw_frontend.wc_slw_pro==true && slw_frontend.show_in_product_page=='yes_radio'){
			var item_stock_location_selector = 'input[type="radio"][name^="slw_add_to_cart_item_stock_location"]';		
			var qty_obj = $(item_stock_location_selector+':checked');
			
			if(qty_obj.length==0 && $(item_stock_location_selector+'.has-stock').length>0){
				$(item_stock_location_selector+'.has-stock').eq(0).prop('checked', true);
				qty_obj = $(item_stock_location_selector+':checked');
			}			
		}else{
			var item_stock_location_selector = 'select[name^="slw_add_to_cart_item_stock_location"]';		
			var qty_obj = $(item_stock_location_selector+' option:selected');
			
			if(qty_obj.length==0 && $(item_stock_location_selector).find('.has-stock').length>0){
				$(item_stock_location_selector).find('.has-stock').eq(0).prop('selected', true);
				qty_obj = $(item_stock_location_selector+' option:selected');
			}
		}
		
		
		var location_id = (qty_obj.length>0?qty_obj.val():0);
		

		if(obj.length>0){	
			var qty = qty_obj.data('quantity');
			var backorder = (qty_obj.data('backorder')=='yes');
			var str_html = obj.html();
			var str = '';
			var stock_quantity = 0;
			
			var include_number = false;
			var availability_obj = {'availability':'', 'class':''};
			var variation_id = 0;

			if(typeof qty!='undefined'){					
				var arr = str_html.split(' ');
				var arr_elem = $.trim(arr[0]);
				
				if($.isNumeric(arr_elem)){
					str_html = str_html.replace(arr[0], qty);	
					//obj.html(str).show();
					include_number = true;
				}else{
					//str = qty+' '+str;
				}
				//obj.html(str).show();
				
			}else{			
			
				
			}
			

			
			switch(slw_frontend.product_type){
				case 'variable':
				
					variation_id = $('form.cart input[name="variation_id"]').val();
					
					if(variation_id>0){
						
						stock_quantity = slw_frontend.stock_quantity[variation_id][location_id];
						availability_obj = slw_frontend.stock_status[variation_id];
						
					}

				break;
				case 'simple':											
				
					stock_quantity = slw_frontend.stock_quantity[slw_frontend.product_id][location_id];
					availability_obj = slw_frontend.stock_status[slw_frontend.product_id];
					
				break;
			}
			
			var everything_stock_status_to_instock = (typeof slw_frontend.everything_stock_status_to_instock!='undefined' && slw_frontend.everything_stock_status_to_instock=='on');
			
			
		
			if(typeof availability_obj=='object'){
				str = (availability_obj.availability.replace(slw_frontend.stock_quantity_sum, stock_quantity));
				obj.removeAttr('class').addClass(availability_obj.class+' stock').html(str);
			}
			var cart_qty = 0;
			if(typeof slw_frontend.slw_cart_items[slw_frontend.product_id]!='undefined'){
				if(typeof slw_frontend.slw_cart_items[slw_frontend.product_id][variation_id]!='undefined'){
					if(typeof slw_frontend.slw_cart_items[slw_frontend.product_id][variation_id][location_id]!='undefined'){
						cart_qty = slw_frontend.slw_cart_items[slw_frontend.product_id][variation_id][location_id];
					}
				}
			}
			
			if(slw_frontend.different_location_per_cart_item=='no'){
				if($('input[name="quantity"]').length>0){
					var available_qty = (stock_quantity-cart_qty);
					$('input[name="quantity"]').prop('max', available_qty);
				}
			}
			
			
		}else{

		}
		
		
		
	}
	$('body').on('change', 'div.quantity input[name="quantity"]', function(){
		
		
		if(slw_frontend.wc_slw_pro==true && slw_frontend.show_in_product_page=='yes_radio'){
			$('input[type="radio"][name^="slw_add_to_cart_item_stock_location"]').change();
		}else if($('select[name^="slw_add_to_cart_item_stock_location"]').length>0){
			$('select[name^="slw_add_to_cart_item_stock_location"]').trigger('change');
		}
		
	});
	
	
	$('body').on('change', 'select[name^="slw_add_to_cart_item_stock_location"], input[type="radio"][name^="slw_add_to_cart_item_stock_location"]', function(){
		
		slw_update_input_and_price($(this), $);

		
	});
	
	
	if(slw_frontend.wc_slw_pro==true && slw_frontend.show_in_product_page=='yes_radio'){
		$('input[type="radio"][name^="slw_add_to_cart_item_stock_location"]').change();
	}else if($('select[name^="slw_add_to_cart_item_stock_location"]').length>0){
		$('select[name^="slw_add_to_cart_item_stock_location"]').change();
	}
	
	$('body').on('click', '.slw-variations-listed label', function(){
		$(this).parent().find('ul').toggle();
	});
	
	

}(jQuery));
