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
		$(document).on( 'found_variation', function() {
            var variation_id = $(".woocommerce-variation-add-to-cart").find('.variation_id').val();
            $.ajax({
				type: 'POST',
				url: slw_frontend_product.ajaxurl,
				data: {
					action: 'get_variation_locations',
					security: $('#woocommerce-cart-nonce').val(),
					variation_id: variation_id
				},
				success: function( response ) {
					$('select#slw_item_stock_location_variable_product').empty();
					$.each(response.data.stock_locations, function(i) {
						var obj = response.data.stock_locations[i];
						$('select#slw_item_stock_location_variable_product').append(new Option(obj.name, obj.term_id));
						$('select#slw_item_stock_location_variable_product').show();
					});
				}
			});
        });
	}

}(jQuery));
