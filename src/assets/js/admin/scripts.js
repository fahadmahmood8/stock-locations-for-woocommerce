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
		slwAjaxSaveProductDefaultLocation();
		slwAjaxRemoveProductDefaultLocation();
		slwEnableLockDefaultLocation();
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
		var pluginWrapper = $('#' + slw_admin_scripts.slug + '_tab_stock_locations_wrapper'); // Plugin class
		var pluginNotice = $('#' + slw_admin_scripts.slug + '_tab_stock_locations_notice'); // Plugin class
		var pluginAlert = $('#' + slw_admin_scripts.slug + '_tab_stock_locations_alert'); // Plugin class
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
		$('input.stock-locations-for-woocommerce_oitem').on('change', function() {
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
		$('#show_in_cart').on('change', function() {
			if( $(this).val() == 'yes' ) {
				$('#different_location_per_cart_item').prop('disabled', false);
				$('#show_in_product_page').prop('disabled', false);
			} else {
				$('#different_location_per_cart_item').prop('disabled', true);
				$('#show_in_product_page').prop('disabled', true);
			}
		});
	}

	function slwEnableLockDefaultLocation()
	{
		// initial
		if( $( '#default_location_in_frontend_selection' ).is(":checked") ) {
			$( '#lock_default_location_in_frontend' ).prop( 'disabled', false );
		} else {
			$( '#lock_default_location_in_frontend' ).prop( 'disabled', true );
		}
		// on change
		$( '#default_location_in_frontend_selection' ).on('change', function() {
			if( $( '#default_location_in_frontend_selection' ).is(":checked") ) {
				$( '#lock_default_location_in_frontend' ).prop( 'disabled', false );
			} else {
				$( '#lock_default_location_in_frontend' ).prop( 'disabled', true );
				$( '#lock_default_location_in_frontend' ).prop( 'checked', false );
			}
		} );
	}

	function slwAjaxSaveProductDefaultLocation()
	{
		$( '.post-type-product #taxonomy-location .slw_location_make_default' ).on( 'click', function( e ) {
			e.preventDefault();
			var elem       = $( this );
			var product_id = $( this ).data( 'product_id' );
			var term_id    = $( this ).data( 'term_id' );

			// block UI
			$( '#locationdiv' ).block({
				message:    null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});

			// ajax request
			$.ajax({
				url:  ajaxurl,
				data: {
					action:		'slw_save_product_default_location',
					nonce:	    slw_admin_scripts.nonce,
					product_id:	product_id,
					term_id:	term_id,
				},
				type: 'POST',
				cache: false,
				success: function( response ) {
					console.log( response );

					// reload page
					location.reload();
				},
				error: function( xhr, status, error ) {
					console.log( error );

					// unblock UI
					$( '#locationdiv' ).unblock();
				},
			});

		} );
	}

	function slwAjaxRemoveProductDefaultLocation()
	{
		$( '.post-type-product #taxonomy-location .slw_location_remove_default' ).on( 'click', function( e ) {
			e.preventDefault();
			var elem       = $( this );
			var product_id = $( this ).data( 'product_id' );

			// block UI
			$( '#locationdiv' ).block({
				message:    null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});

			// ajax request
			$.ajax({
				url:  ajaxurl,
				data: {
					action:		'slw_remove_product_default_location',
					nonce:	    slw_admin_scripts.nonce,
					product_id:	product_id,
				},
				type: 'POST',
				cache: false,
				success: function( response ) {
					console.log( response );

					// reload page
					location.reload();
				},
				error: function( xhr, status, error ) {
					console.log( error );

					// unblock UI
					$( '#locationdiv' ).unblock();
				},
			});

		} );
	}
	
	$('a.slw_clear_debug_log').on('click', function (e) {

		e.preventDefault();

		$('.slw_logger ul.slw_debug_log').html('');

		var data = {

			action: 'slw_clear_debug_log',
			slw_clear_debug_log: 'true',
			slw_clear_debug_log_field: slw_admin_scripts.nonce,
		}

		// console.log(data);
		$.post(ajaxurl, data, function (response, code) {

			// console.log(response);
			if (code == 'success') {


				//
			}

		});

	});

}(jQuery));
