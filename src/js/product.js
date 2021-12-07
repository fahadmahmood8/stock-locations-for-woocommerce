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
			let variation_id = $(".woocommerce-variation-add-to-cart").find('.variation_id').val();
			let product_id   = $(".woocommerce-variation-add-to-cart").find('input[name="product_id"]').val();
			$.ajax({
				type: 'POST',
				url: slw_frontend_product.ajaxurl,
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
								$('select#slw_item_stock_location_variable_product').append( new Option( obj.name, obj.term_id, selected, selected ) );
							}
						});
						$('select#slw_item_stock_location_variable_product').show();
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

}(jQuery));
