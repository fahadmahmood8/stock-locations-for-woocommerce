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
		$('select.slw_cart_item_stock_location_selection').on('change keyup paste',function(){
			$('.cart_totals').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			var cart_id = $('option:selected',this).data('cart_id');
			var location_id = parseInt($('option:selected',this).val());
			$.ajax({
				type: 'POST',
				url: slw_frontend.ajaxurl,
				data: {
					action: 'update_cart_stock_locations',
					security: $('#woocommerce-cart-nonce').val(),
					stock_location: location_id,
					cart_id: cart_id
				},
				success: function( response ) {
					$('.cart_totals').unblock();
				}
			});
			$(this).parent().find('div.store-notice').remove();
			if(location_id){				
				var location_notice = slw_frontend.stock_locations_data[location_id]['notice'];
				
				if(location_notice!=''){
					$('<div class="store-notice">'+location_notice+'</div>').insertAfter($(this).parent().find('select.slw_item_stock_location'));
				}
			}
		});
	}
	
	function slw_cart_validate() {
		var inputsWithValues = 0;
		var myInputs = $(".slw_cart_item_stock_location_selection");
		
		myInputs.each(function(e) {			
			var v = $.trim($(this).val());
			if (v!='' && v!=0) {
				inputsWithValues += 1;
			}
		});
		if (inputsWithValues != myInputs.length) {
			$('.checkout-button').addClass('slw_checkout_disable');
		} else {
			$('.checkout-button').removeClass('slw_checkout_disable');
		}
	}	

	if(slw_frontend.is_cart){
		
		
		if(slw_frontend.show_in_cart=='yes'){
			
			setTimeout(function(){
				$.each($('select.slw_cart_item_stock_location_selection'), function(){
					$(this).trigger('change');
				});
			}, 2000);
		
			if(slw_frontend.cart_location_selection_required=='on'){
			
				$('.slw_cart_item_stock_location_selection').on('change', function() {
					slw_cart_validate();
				});
				
					
				
				
				setInterval(function(){	
					slw_cart_validate();
				}, 500);
				
			}
			
		}
	}

}(jQuery));
