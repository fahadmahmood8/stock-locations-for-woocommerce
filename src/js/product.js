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
						$('select#slw_item_stock_location_variable_product').empty();
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
							}
						});
						$('select#slw_item_stock_location_variable_product').show();

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
	
	
	$('body').on('change', 'select#slw_item_stock_location_variable_product, select#slw_item_stock_location_simple_product', function(){
		
		slw_add_to_cart_item_stock_status_update();
		
	});
	
	function slw_add_to_cart_item_stock_location_trigger(){
		
		$('select[name="slw_add_to_cart_item_stock_location"]').change();
		slw_add_to_cart_item_stock_status_update();
	}
	
	function slw_add_to_cart_item_stock_status_update(){
		
		var obj = $('p.stock').eq(0);

		var item_stock_location_selector = 'select[name="slw_add_to_cart_item_stock_location"]';
		var qty_obj = $(item_stock_location_selector+' option:selected');
		
		if(qty_obj.length==0 && $(item_stock_location_selector).find('.has-stock').length>0){
			$(item_stock_location_selector).find('.has-stock').eq(0).prop('selected', true);
			qty_obj = $(item_stock_location_selector+' option:selected');
		}
		
		var location_id = (qty_obj.length>0?qty_obj.val():0);
		

		if(obj.length>0){	
			var qty = qty_obj.data('quantity');
			var backorder = (qty_obj.data('backorder')=='yes');
			var str_html = obj.html();
			var str = '';
			var stock_quantity = 0;
			var include_number = false;

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
					var variation_id = $('form.cart input[name="variation_id"]').val();
					
					
					if(variation_id>0){
						stock_quantity = slw_frontend.stock_quantity[variation_id][location_id];
					}

				break;
				case 'simple':						
					stock_quantity = slw_frontend.stock_quantity[slw_frontend.product_id][location_id];
				break;
			}
			
			var everything_stock_status_to_instock = (typeof slw_frontend.everything_stock_status_to_instock!='undefined' && slw_frontend.everything_stock_status_to_instock=='on');
			
			if(stock_quantity==0 && !everything_stock_status_to_instock){		
				
				
				if(backorder){
					str = slw_frontend.backorder;				
					obj.removeClass('in-stock out-of-stock').addClass('available-on-backorder');
				}else{
					str = slw_frontend.out_of_stock;
					obj.removeClass('in-stock available-on-backorder').addClass('out-of-stock');
				}
				
				
				
				
			}else if(stock_quantity>0){
				str = slw_frontend.in_stock;
				
				obj.removeClass('out-of-stock available-on-backorder').addClass('in-stock');
			}

			str = ((typeof str!='undefined')?str:'');
			stock_quantity = ((typeof stock_quantity!='undefined' && stock_quantity!='0')?stock_quantity:'');
			if(include_number){
				obj.html(stock_quantity+' '+str);
			}else{
				obj.html(str);
			}
			
			
			
			
		}else{

		}
		
	}
	$('body').on('change', 'div.quantity input[name="quantity"]', function(){
		
		if($('select[name="slw_add_to_cart_item_stock_location"]').length>0){
			$('select[name="slw_add_to_cart_item_stock_location"]').trigger('change');
		}
		
	});
	$('select[name="slw_add_to_cart_item_stock_location"]').on('change', function(){
		
		
		var qty_obj = $('select[name="slw_add_to_cart_item_stock_location"] option:selected');
		
		var location_qty = qty_obj.data('quantity');
		
		if($(this).val()==0 || location_qty<1){
			if($('button[name="add-to-cart"]').length>0){ $('button[name="add-to-cart"]').prop('disabled', true); }			
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
				price_dom = $('.price .woocommerce-Price-amount.amount');
				if(price_dom.length>0){
					price_dom.html(price_html);
				}
			}
		}

		
	});
	
	if($('select[name="slw_add_to_cart_item_stock_location"]').length>0){
		$('select[name="slw_add_to_cart_item_stock_location"]').change();
	}
	
	$('body').on('click', '.slw-variations-listed label', function(){
		$(this).parent().find('ul').toggle();
	});

}(jQuery));
