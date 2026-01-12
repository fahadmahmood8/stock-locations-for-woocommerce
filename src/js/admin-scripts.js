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
		if(input_id && slw_admin_scripts.slw_gkey!=''){
			slw_gmap_initialize(input_id);
		}
		
		
	});

	// Functions to initiate
	function init() {
		slwDisableVariableStockInput();
		slwWcProductManageStock();
		slwWcOrderItemStockPositiveNumbersOnly();
		//slwEnableShowLocationsProductPage();
		slwAjaxSaveProductDefaultLocation();
		slwAjaxRemoveProductDefaultLocation();
		slwEnableLockDefaultLocation();
		slwApiDemo();
	}
	function slw_build_api_url(params) {
		//console.log(params);
		var base = slw_admin_scripts.home_url + '/?slw-api&';
		var parts = [];
	
		$.each(slw_admin_scripts.wc_slw_api_valid_keys, function (key, config) {
	

			if (config.scope === 'variable') {
				return;
			}

	
			var value = params[key] !== undefined ? params[key] : '';
	
			parts.push('<span>' + key + '=' + value + '</span>');
		});
	
		return base + parts.join('&amp;');
	}
	/**
	 * Build JSON payload for product variations + locations
	 * @param {Object} data - AJAX response from slw_api_get_product_stock_data
	 * @param {String} actionType - 'get' or 'set'
	 * @param {String} formatType - 'json' or 'default'
	 * @returns {Array} - array of payload objects for each variation
	 */
	function slw_build_json_payload(data, actionType = 'get', formatType = 'json') {
		var payloads = [];
	
		$.each(data.variations, function (i, variation) {
	
			// Construct base object
			var baseObj = {
				id: variation.variation_id || data.product_id, // variation or main product
				product_id: data.product_id,
				variation_id: variation.variation_id,
				action: actionType,
				format: formatType
			};
	
			// Each location becomes an object with id, stock_value, stock_price
			var locations = [];

			$.each(variation.locations, function (j, loc) {
				locations.push({
					variation_id: variation.variation_id,
					location_id: loc.location_id,
					stock_value: loc.stock_value,
					stock_price: loc.stock_price
				});
			});
	
			// Attach locations array
			baseObj.location = locations;
	
			// Add to final array
			payloads.push(baseObj);
		});
	
		return payloads;
	}

	function slwApiDemo(){
		
		$('button.slw-api-id-try').on('click', function(){
			$('.slw-api-id-input').change();
		});
		
		$('.slw-api-id-input').on('change', function () {
		
				var product_id = $(this).val();
		
				if (!product_id) {
					return;
				}
				$.blockUI({ message: false });
				$.ajax({
					url: slw_admin_scripts.ajaxurl,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'slw_api_get_product_stock_data',
						nonce: slw_admin_scripts.nonce,
						product_id: product_id
					},
					success: function (response) {
						
						$.unblockUI(); // always first
						
						if (!response.success) {
							
							return;
						}

		
						var data = response.data;
						var payloads = slw_build_json_payload(response.data, 'set', 'json');

			
						var html = '';
						
						$.each(data.variations, function (i, variation) {
						
							// Iterate over locations inside this variation
							$.each(variation.locations, function (j, loc) {
						
								// STOCK URL
								var stockParams = {
									id: variation.variation_id || data.product_id, // variation or main product
									product_id: data.product_id,
									variation_id: variation.variation_id,
									item: 'stock',
									value: loc.stock_value,
									location_id: loc.location_id
								};
						
								var stockUrl = slw_build_api_url(stockParams);
								html += '<b>' + stockUrl + '</b><br/>';
						
								// PRICE URL
								var priceParams = {
									id: variation.variation_id || data.product_id,
									product_id: data.product_id,
									variation_id: variation.variation_id,
									item: 'price',
									value: loc.stock_price,
									location_id: loc.location_id
								};
						
								var priceUrl = slw_build_api_url(priceParams);
								html += '<b>' + priceUrl + '</b><br/>';
							});
						
						});
						
						$('.slw-api-urls ul li u').html(html);
						
						var prettyJson = JSON.stringify(payloads, null, 2);
						$('div.slw-api-json-payload div').html('var payloads = '+prettyJson + `;
$.ajax({
	url: '`+slw_admin_scripts.home_url+`?slw-api',
	type: 'POST',
	dataType: 'json',
	contentType: 'application/json',
	data: JSON.stringify({ payload: payloads }),
	success: function(res) {
		
	}
});
						`).fadeIn();

					},
					error: function (xhr, textStatus, errorThrown) {
						//console.log('error');
						console.log(xhr);
						
						$('div.slw-api-json-payload div').html(xhr.responseText).show();
						$.unblockUI();
					
					},
					complete: function (xhr) {
						//console.log('complete');
						//console.log(xhr);
						$.unblockUI();
					}
					
				});
		
		});	
		
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
	$('#different_location_per_cart_item').on('change', function(){
		if($(this).val()=='no'){
			$('.different_location_per_cart_item_no').show();
		}else{
			$('.different_location_per_cart_item_no').hide();
		}
	});
	
	function slwEnableShowLocationsProductPage()
	{
		// initial
		if( $('#show_in_cart').val() != 'yes' ) {
			$('#different_location_per_cart_item').prop('disabled', true);

		} else {
			$('#different_location_per_cart_item').prop('disabled', false);

		}
		// on change
		$('#show_in_cart').on('change', function() {
			if( $(this).val() == 'yes' ) {
				$('#different_location_per_cart_item').prop('disabled', false);

			} else {
				$('#different_location_per_cart_item').prop('disabled', true);

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
		
	$('input#slw-logs-status').on('click', function () {
		var isChecked = $(this).is(':checked');
		var data = {
			action: 'slw_logs_status',
			status: isChecked ? $(this).val() : '',
			slw_nonce_field: slw_admin_scripts.nonce
		};
	
		$.blockUI({ message: false });
	
		$.post(ajaxurl, data, function (response) {
			var message = '';
	
			if (response.success) {
				message = response.data.message + ' (Status: ' + response.data.status + ')';
			} else {
				message = 'Error: ' + response.data.message;
			}
	
			$.blockUI({ message: '<h4>' + message + '</h4>' });
	
			setTimeout(function () {
				$.unblockUI();
			}, 3000);
		}).fail(function () {
			$.unblockUI();
			alert(slw_admin_scripts.slw_error_occurred);
		});
	});
	
	$('input#slw-update-product-locations-stock-values').bind('click', function (e) {
		var data = {
			action: 'slw_update_product_locations_stock_values',
			status: $(this).is(':checked') ? $(this).val() : '',
			slw_nonce_field: slw_admin_scripts.nonce,
		};
	
		$.blockUI({ message: false });
		$.post(ajaxurl, data, function (response, code) {
			$.unblockUI();
			if (code == 'success') {
				// Action after success (if needed)
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
	$('input#slw-crons-status').on('click', function () {
		var data = {
			action: 'slw_crons_status',
			status: $(this).is(':checked') ? $(this).val() : '',
			slw_nonce_field: slw_admin_scripts.nonce
		};
	
		$.blockUI({ message: false });
	
		$.post(ajaxurl, data, function (response) {
			var message = '';
	
			if (response.success) {
				message = response.data.message + ' (Status: ' + response.data.status + ')';
			} else {
				message = 'Error: ' + response.data.message;
			}
	
			$.blockUI({ message: '<h4>' + message + '</h4>' });
	
			setTimeout(function () {
				$.unblockUI();
			}, 3000);
		});
	});

	
	$('#slw-location-assignment, a.slw-location-assignment').bind('click', function (e) {
		var assignment_val = '';
	
		if ($(this).is('input[type="checkbox"]')) {
			assignment_val = $(this).is(':checked') ? $(this).val() : '';
		} else {
			assignment_val = $(this).hasClass('checked') ? '' : 'yes';
			$(this).toggleClass('checked');
		}
	
		var data = {
			action: 'slw_location_assignment',
			assignment: assignment_val,
			location_id: $(this).data('id'),
			slw_nonce_field: slw_admin_scripts.nonce,
		};
	
		$.blockUI({ message: false });
		$.post(ajaxurl, data, function (response, code) {
			$.unblockUI();
			if (code == 'success' && response.success) {

			}
		});
	});

	
	$('#slw-location-status, a.slw-location-status').bind('click', function (e) {
		
		
		var status_val = '';
		
		if($(this).is('input[type="checkbox"]')){
			status_val = ($(this).is(':checked')?$(this).val():'');
		}else{
			status_val = $(this).hasClass('checked')?'':'yes';
			$(this).toggleClass('checked');
		}
		
		var data = {
			action: 'slw_location_status',
			status: status_val,
			location_id: $(this).data('id'),
			slw_nonce_field: slw_admin_scripts.nonce,
		}

		$.blockUI({ message: false });
		$.post(ajaxurl, data, function (response, code) {
			$.unblockUI();
			if (code == 'success') {
			}

		});


	});	
	
	
	$('input#slw-map-status, a.slw-map-status').bind('click', function (e) {
		var obj = $(this);
		var is_checkbox = obj.is(':input[type="checkbox"]');
		var is_status = (is_checkbox?($(this).is(':checked')?obj.val():''):obj.data('status'));
		
		var data = {

			action: 'slw_map_status',
			status: is_status,
			location_id: obj.data('id'),
			slw_nonce_field: slw_admin_scripts.nonce,
		}

		$.blockUI({ message: false });
		$.post(ajaxurl, data, function (response, code) {
			$.unblockUI();
			if (code == 'success') {
				if(!is_checkbox){
					obj.data('status', (is_status=='yes'?'':'yes'));
					
					if(is_status=='yes'){
						obj.find('.slw_map_status-disabled').hide();
						obj.find('.slw_map_status-enabled').show();
					}else{
						obj.find('.slw_map_status-disabled').show();
						obj.find('.slw_map_status-enabled').hide();						
					}
				}
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
	if($('.slw_need_popup').length>0){
		$('.slw_need_popup ul li[data-type="screenshot"] a, .slw_need_popup a[data-type="screenshot"]').magnificPopup({
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
	$('body').on('click', 'li[data-type="shortcode"] > svg', function(){
		$(this).parent().toggleClass('collapsed');
	});
	
	$('body').on('click', 'table.table-view-list td.stock_at_locations span i', function(){
		var n = $(this).html().replace('(', '').replace(')', '');
		$('table.table-view-list td.stock_at_locations span.clicked').removeClass('clicked');
		$(this).parent().addClass('clicked');
		$('table.table-view-list td.stock_at_locations span input.location_qty_update').val(n);
	});
	$('body').on('keydown', 'table.table-view-list td.stock_at_locations span input.location_qty_update', function(e){	
		if (e.which == 13) {
			e.preventDefault();
			$(this).trigger('blur');
			return false;
		}
	
	});
	$('body').on('blur', 'table.table-view-list td.stock_at_locations span input.location_qty_update', function(e){		
		
		
		$(this).focus();
		$.blockUI({message:slw_admin_scripts.wc_slw_pro?'':slw_admin_scripts.wc_slw_premium_feature, blockMsgClass: 'slw-premium-block',});
		var n = ($(this).val())*1;
		var obj = $(this).parents().closest('tr').find('td[data-colname="Stock"]');

		$(this).parent().removeClass('clicked');		
		$(this).parent().find('i').html('('+n+')');		
		if(slw_admin_scripts.wc_slw_pro){
		
				var data = {
					'action': 'slw_product_list_qty_update',
					'quantity': n,
					'product_id': $(this).data('product'),				
					'location_id': $(this).data('location'),
					'slw_nonce_field': slw_admin_scripts.nonce
				};

				$.post(slw_admin_scripts.ajaxurl, data, function(response) {
					
					obj.html('');
					
					$.unblockUI();
					
					
					
				});
				
				$(this).parent().find('mark').prop('class', (n>0?'instock':'outofstock'));
	
			
		}else{
			setTimeout(function(){
				$.unblockUI();
			}, 30000);
		}
		
	});
	
	setTimeout(function(){
		if($('#different_location_per_cart_item').length>0){
			$('#different_location_per_cart_item').trigger('change');
		}
		if($('#stock-locations-for-woocommerce_tab_stock_locations_wrapper').length){
			
			var product_id = slw_admin_scripts.wc_slw_product_id;
			//console.log(product_id);
			if(product_id){
				$.each(slw_admin_scripts.wc_slw_location_status, function(i,v){
					
					var obj_str = '_stock-locations-for-woocommerce'+product_id+'_stock_location_'+i+'_field';

					if($('p.'+obj_str).length>0){
						if(v!='yes'){
							$('p.'+obj_str).addClass('slw-location-disabled').attr('title', slw_admin_scripts.wc_slw_location_disabled_msg);
						}
					}
					
				});
			}
		}
		
	}, 3000);
	
	$('body').on('click', 'div.slw-api-requests input[name^="validate_request"]', function(){
		
		
		var requests = {};
		
		$.each($('div.slw-api-requests input[name^="validate_request"]:checked'), function(i,v){
			requests[i] = $(this).val();
		})
		
		var data = {

			action: 'slw_validate_api_requests',
			slw_validate_request: requests,
			slw_nonce_check: slw_admin_scripts.nonce,
		}
		
		$.blockUI({ message: false });
		$.post(ajaxurl, data, function (response, code) {
			$.unblockUI();
			if (code == 'success') {
			}
		});
		
	});
	
	$('body').on('click', 'div.slw-cron-requests input[name^="validate_request"]', function(){
		
		
		var requests = {};
		
		$.each($('div.slw-cron-requests input[name^="validate_request"]:checked'), function(i,v){
			requests[i] = $(this).val();
		})
		
		var data = {

			action: 'slw_validate_cron_requests',
			slw_validate_request: requests,
			slw_nonce_check: slw_admin_scripts.nonce,
		}
		
		$.blockUI({ message: false });
		$.post(ajaxurl, data, function (response, code) {
			$.unblockUI();
			if (code == 'success') {
			}
		});
		
	});
	
	setTimeout(function(){
		

		if($('a.page-title-action[href*="page=product_exporter"]').length>0 && $('#slw_import_export_video').length==0){
			$('<input type="button" id="slw_import_export_video" class="page-title-action" value="'+slw_admin_scripts.slw_import_export_tutorial+'">').insertAfter($('body.post-type-product a.page-title-action[href*="page=product_exporter"]').eq(0));
		}
		
		if($('#doaction').length>0 && $('#slw_import_export_video').length==0){
			$('<input type="button" id="slw_import_export_video" class="button action" value="'+slw_admin_scripts.slw_import_export_tutorial+'">').insertAfter($('body.post-type-product #doaction').eq(0));
		}		
		
	}, 1000);
	
	$('body').on('click', '#slw_import_export_video', function(){
		window.open('https://www.youtube.com/embed/4KCexCuVetk', '');
	});
	

}(jQuery));
