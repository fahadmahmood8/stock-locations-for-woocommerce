(function($){

	// Init after DOM is ready
	$(document).ready(function() {
		init();
	});

	// Functions to initiate
	function init() {
		slwDisableVariableStockInput();
		slwWcProductManageStock();
		slwWcOrderItemStockPositiveNumbersOnly();
		slwEnableShowLocationsProductPage();
	}
	
	function slwDisableVariableStockInput()
	{
		$('#woocommerce-product-data').on('woocommerce_variations_loaded', function(event) {
			$('input.variable_manage_stock').each(function(){
				if( $(this).prop( "checked" ) === true ) {
					for(i=0; i < $('input.variable_manage_stock').length; i++) {
						$('input#variable_stock' + i).prop( "disabled", true );
					}
				}
			});
		});
	}

	function slwWcProductManageStock()
	{
		var pluginWrapper = $('#' + slw_plugin_slug.slug + '_tab_stock_locations_wrapper'); // Plugin class
		var pluginNotice = $('#' + slw_plugin_slug.slug + '_tab_stock_locations_notice'); // Plugin class
		var pluginAlert = $('#' + slw_plugin_slug.slug + '_tab_stock_locations_alert'); // Plugin class
		var wcStock = $('#_stock'); // Default WooCommerce class
		var wcManageStock = $('#_manage_stock'); // Default WooCommerce class

		if(wcManageStock !== null) {
			if(wcManageStock.is(':checked') === true) { // If stock management is active
				wcStock.prop( "disabled", true );
				pluginWrapper.show();
				pluginNotice.hide();
				if(pluginAlert !== null) {
					pluginAlert.show();
				}
			} else {
				wcManageStock.on('click', function () {
					if (pluginWrapper.css("display") === 'none') {
						wcStock.prop( "disabled", true );
						pluginWrapper.show();
						pluginNotice.hide();
						if(pluginAlert !== null) {
							pluginAlert.show();
						}
					} else {
						wcStock.prop( "disabled", false );
						pluginWrapper.hide();
						pluginNoticepluginWrapper.show();
						if(pluginAlert !== null) {
							pluginAlert.hide();
						}
					}
				});
			}
		}
	}

	function slwWcOrderItemStockPositiveNumbersOnly()
	{
		$('input.stock-locations-for-woocommerce_oitem').change(function() {
			if ($(this).val() < 0) {
				$(this).val('0');
				alert('Positive numbers only!');
			}
		});
	}

	function slwEnableShowLocationsProductPage()
	{
		// initial
		if( $('#show_in_cart').val() != 'yes' ) {
			$('#different_location_per_cart_item').prop('disabled', true);
			$('#show_in_product_page').prop('disabled', true);
		} else {
			$('#different_location_per_cart_item').prop('disabled', false);
			$('#show_in_product_page').prop('disabled', false);
		}
		// on change
		$('#show_in_cart').change(function() {
			if( $(this).val() == 'yes' ) {
				$('#different_location_per_cart_item').prop('disabled', false);
				$('#show_in_product_page').prop('disabled', false);
			} else {
				$('#different_location_per_cart_item').prop('disabled', true);
				$('#show_in_product_page').prop('disabled', true);
			}
		});
	}

}(jQuery));
