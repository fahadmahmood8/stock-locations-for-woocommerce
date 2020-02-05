(function($){

	$('#woocommerce-product-data').on('woocommerce_variations_loaded', function(event) {
		$('input.variable_manage_stock').each(function(){
			if( $(this).prop( "checked" ) === true ) {
				for(i=0; i < $('input.variable_manage_stock').length; i++) {
					$('input#variable_stock' + i).prop( "disabled", true );
				}
			}
		});
	});

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

}(jQuery));
