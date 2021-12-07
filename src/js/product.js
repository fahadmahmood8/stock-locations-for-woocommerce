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
								var option_str = '<option data-quantity="'+obj.quantity+'" value="'+obj.term_id+'" '+(selected?'selected="selected"':'')+'>'+obj.name+'</option>';
								$('select#slw_item_stock_location_variable_product').append(option_str);
							}
						});
						$('select#slw_item_stock_location_variable_product').show();

						$('select[name="slw_add_to_cart_item_stock_location"]').change();

					} else {
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
		if(obj.length>0){
			var qty = $('select[name="slw_add_to_cart_item_stock_location"] option:selected').data('quantity');
			var str = obj.html();
			var arr = str.split(' ');
			str = str.replace(arr[0], qty);
			obj.html(str).show();
		}
	});

}(jQuery));
