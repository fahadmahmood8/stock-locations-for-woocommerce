function slw_gmap_initialize(input_id) {
	var input = document.getElementById(input_id);//$('form#edittag input#name');//
	var autocomplete = new google.maps.places.Autocomplete(input);

	google.maps.event.addListener(autocomplete, 'place_changed', function() {
		var place = autocomplete.getPlace();	
		jQuery('#slw-lat').val(place.geometry.location.lat());
		jQuery('#slw-lng').val(place.geometry.location.lng());
	});
	
}
(function($){

	// Init after DOM is ready
	$(document).ready(function() {
		init();
		
		
		//google.maps.event.addDomListener(window, 'load', gmap_initialize);
		var input_id = '';
		if($('form#edittag input#location_address').length>0){
			input_id = 'location_address';
		}
		if($('form#addtag input#tag-name').length>0){
			//input_id = 'tag-name';
		}
		if(input_id){
			slw_gmap_initialize(input_id);
			
			
		}
		
		
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
			if(slw_admin_scripts.stock_locations==true){
				$('input.variable_manage_stock').each(function(){
					if( $(this).prop( "checked" ) === true ) {
						for(i=0; i < $('input.variable_manage_stock').length; i++) {
							$('input#variable_stock' + i).prop( "disabled", true );
						}
					}
				});
			}
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
				if(slw_admin_scripts.stock_locations==true){
					wcStock.prop( "disabled", true );
				}
				pluginWrapper.show();
				pluginNotice.hide();
				if(pluginAlert !== null) {
					pluginAlert.show();
				}
			} else {
				wcManageStock.on('click', function () {
					if (pluginWrapper.css("display") === 'none') {
						if(slw_admin_scripts.stock_locations==true){
							wcStock.prop( "disabled", true );
						}
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
			//$('#show_in_product_page').prop('disabled', true);
		} else {
			$('#different_location_per_cart_item').prop('disabled', false);
			//$('#show_in_product_page').prop('disabled', false);
		}
		// on change
		$('#show_in_cart').on('change', function() {
			if( $(this).val() == 'yes' ) {
				$('#different_location_per_cart_item').prop('disabled', false);
				//$('#show_in_product_page').prop('disabled', false);
			} else {
				$('#different_location_per_cart_item').prop('disabled', true);
				//$('#show_in_product_page').prop('disabled', true);
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
		
		$.blockUI({ message: false });
		$.post(ajaxurl, data, function (response, code) {
			$.unblockUI();
			if (code == 'success') {
			}
		});

	});
	
	$('input#slw-api-status').bind('click', function (e) {
		var data = {

			action: 'slw_api_status',
			status: $(this).is(':checked')?$(this).val():'',
			slw_nonce_field: slw_admin_scripts.nonce,
		}

		$.blockUI({ message: false });
		$.post(ajaxurl, data, function (response, code) {
			$.unblockUI();
			if (code == 'success') {
			}

		});


	});
	
	$('div.slw_widgets label.switch input[type="checkbox"]').bind('click', function (e) {
		
		var data = {

			action: 'slw_widgets_settings',
			slw_widget_key: $(this).attr('name'),
			slw_widget_value: $(this).is(':checked')?$(this).val():'',
			slw_nonce_field: slw_admin_scripts.nonce,			
		}

		$.blockUI({ message: false });
		$.post(ajaxurl, data, function (response, code) {
			$.unblockUI();			
			if (code == 'success') {
			}
		});

	});
	
	
	if($('select[name="auto_order_allocate"]').length>0){
		$('select[name="auto_order_allocate"]').on('change', function(){
			var id = $(this).data('id');
			if($('select[name="auto_order_allocate"] option:selected').val()=='1'){
				$('tr.'+id).addClass('selected');
			}else{
				$('tr.'+id).removeClass('selected');
			}
		});
		$('select[name="auto_order_allocate"]').trigger('change');
	}
	
	var slw_widgets_update_request = false;
	$('div.slw_widgets ul li').on('change', 'input', function(){
		var data = {
			action: 'slw_widgets_settings',
			slw_widget_key: $(this).attr('name'),
			slw_widget_value: $(this).val(),
			slw_nonce_field: slw_admin_scripts.nonce,
		}

		if(!slw_widgets_update_request){
			slw_widgets_update_request = true;
			$.blockUI({ message: false });
			$.post(ajaxurl, data, function (response, code) {
				$.unblockUI();
				
				if (code == 'success') {
					
				}
				
				slw_widgets_update_request = false;
			});
		}
	});
	if($('div.slw_widgets').length>0){
		$('div.slw_widgets ul li[data-type="screenshot"] a').magnificPopup({
		  type: 'image',
		  gallery: {
			// options for gallery
			enabled: false
		  },
		  mainClass: 'mfp-with-zoom', // this class is for CSS animation below
		
		  zoom: {
			enabled: true, // By default it's false, so don't forget to enable it
		
			duration: 400, // duration of the effect, in milliseconds
			easing: 'ease-in', // CSS transition easing function
		
			// The "opener" function should return the element from which popup will be zoomed in
			// and to which popup will be scaled down
			// By defailt it looks for an image tag:
			opener: function(openerElement) {
			  // openerElement is the element on which popup was initialized, in this case its <a> tag
			  // you don't need to add "opener" option if this code matches your needs, it's defailt one.
			  return openerElement.is('img') ? openerElement : openerElement.find('img');
			}
		  }
		});
	}
	
	$('div.slw-sample-codes > a').on('click', function(){
		$(this).parent().find('div.slw-sample-code').toggle();
	});
	

}(jQuery));
