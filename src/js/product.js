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
			
			if(slw_frontend.stock_locations>0)
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
								$('select#slw_item_stock_location_variable_product').append('<option disabled="disabled">'+obj.name+'</option>');
							} else {
								let selected = false;
								if( obj.term_id == response.data.default_location ) {
									selected = true;
								}
								
								//new Option( obj.name, obj.term_id, selected, selected )
								var product_stock_location_name = obj.name;
								if(slw_frontend.product_stock_price_status=='on'){								
									product_stock_location_name += ' '+slw_frontend.currency_symbol+''+obj.price;
								}
								var option_str = '<option data-price="'+obj.price+'"  data-quantity="'+obj.quantity+'" value="'+obj.term_id+'" '+(selected?'selected="selected"':'')+'>'+product_stock_location_name+'</option>';
								$('select#slw_item_stock_location_variable_product').append(option_str);
							}
						});
						$('select#slw_item_stock_location_variable_product').show();

						$('select[name="slw_add_to_cart_item_stock_location"]').change();

					} else {
						$('.woocommerce-variation-availability p.stock').show();
						return;
					}
				},
				error ( xhr, error, status ) {
					//console.log( error, status );
				}
			});
		});
	}
	
	$('select[name="slw_add_to_cart_item_stock_location"]').on('change', function(){
		
		var obj = $('div.woocommerce p.stock');
		
		var qty_obj = $('select[name="slw_add_to_cart_item_stock_location"] option:selected');
		
		var price_dom = $('.woocommerce-variation-price .woocommerce-Price-amount.amount');
		
		var price = qty_obj.data('price');
		
		if(price && slw_frontend.product_stock_price_status=='on'){
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

		if(obj.length>0){
			
			
			if(obj.length>0){	
				var qty = qty_obj.data('quantity');
				var str = obj.html();

				
				if(typeof qty!='undefined'){					
					var arr = str.split(' ');
					str = str.replace(arr[0], qty);					
					obj.html(str).show();
				}else{				
					if(slw_frontend.stock_quantity==0){		
						str = slw_frontend.out_of_stock;		
						obj.html(str).removeClass('in-stock').addClass('out-of-stock').show();
					}					
				}
				
			}else{
				
			}
		}
	});
	
	if($('select[name="slw_add_to_cart_item_stock_location"]').length>0){
		$('select[name="slw_add_to_cart_item_stock_location"]').change();
	}

}(jQuery));
