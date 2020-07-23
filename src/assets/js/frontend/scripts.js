(function($){

	// Init after DOM is ready
    $(document).ready(function() {
        init();
    });

    // Functions to initiate
    function init() {
		slwWcCartItemStockLocation();
	}

	function slwWcCartItemStockLocation()
	{
		$('.slw_cart_item_stock_location').on('change keyup paste',function(){
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

}(jQuery));
