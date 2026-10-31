(function($){

	// Init after DOM is ready
    $(document).ready(function() {
        init();
    });

    // Functions to initiate
    function init() {
		slwWcCartItemStockLocation();
		slwVariableProductVariationFound();
	}

	function slwWcCartItemStockLocation()
	{
		$('select.slw_cart_item_stock_location_selection').on('change keyup paste',function(){
			$('.cart_totals').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			var cart_id = $('option:selected',this).data('cart_id');
			$.ajax({
				type: 'POST',
				url: slw_frontend_ajax_url.ajaxurl,
				data: {
					action: 'update_cart_stock_locations',
					security: $('#woocommerce-cart-nonce').val(),
					stock_location: $('option:selected',this).val(),
					cart_id: cart_id
				},
				success: function( response ) {
					$('.cart_totals').unblock();
				}
			});
		});
	}

	function slwVariableProductVariationFound()
	{
		$('select#slw_item_stock_location_variable_product').hide();
		$(document).on( 'found_variation', function() {
            var variation_id = $(".woocommerce-variation-add-to-cart").find('.variation_id').val();
            $.ajax({
				type: 'POST',
				url: slw_frontend_ajax_url.ajaxurl,
				data: {
					action: 'get_variation_locations',
					security: $('#woocommerce-cart-nonce').val(),
					variation_id: variation_id
				},
				success: function( response ) {
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
