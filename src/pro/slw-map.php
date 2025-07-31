<?php
	$locations_list = array();
	$locations_coord = array();
	
	function slw_map_controls($placeholder=''){
		
?>
<div class="slw-map-controls">
<?php do_action('slw-map-before-search-box', $placeholder); ?>
<input
      id="slw-search"
      class="slw-search"
      type="text"
      
      placeholder="<?php echo $placeholder; ?>"
    />
</div>
<?php do_action('slw-map-before-after-box', $placeholder); ?>
<?php		
	}
	
?>


<script type="text/javascript" language="javascript">
	var slw_search_list_diameter = parseInt('<?php echo $attributes['diameter-range']; ?>');
	var distance_unit = '<?php echo strtolower($attributes['distance-unit']); ?>';
	var slw_locations_off = true;
	var slw_locations_udist = slw_locations_distance = [];
	var slw_bounds_changed = true;
	let map, infoWindow, slw_visitor_position, slw_visitor_map, slw_locations_coord;
	slw_locations_coord = {};
	
	var distance_measures = [];
	
	switch(distance_unit){
		default:
		case 'km':
		case 'kms':
		case 'kilometer':
		case 'kilometers':
			distance_measures['fraction'] = 0.001;
			distance_measures['decimal'] = 1000; 
		break;
		
		case 'mi':
		case 'mile':
		case 'miles':
			distance_measures['fraction'] = 0.000621;
			distance_measures['decimal'] = 1609.344; 		
		break;

	}
	
	function slw_calc_distance(p1, p2) {
	  var R = 6378137; // Earth's mean radius in meter
	  var dLat = slw_rad(p2.lat - p1.lat);
	  var dLong = slw_rad(p2.lng - p1.lng);
	  var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
		Math.cos(slw_rad(p1.lat)) * Math.cos(slw_rad(p2.lat)) *
		Math.sin(dLong / 2) * Math.sin(dLong / 2);
	  var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
	  var d = R * c;
	  
	  return Math.round(d/distance_measures['decimal']); // returns the distance in meter
	}
	
	function initSlwMap() {
		
		map = new google.maps.Map(document.getElementById("slw-map"), {   
			zoom: <?php echo $attributes['zoom']; ?>,
		});
	  
	  	const input = document.getElementById("slw-search");
  		const searchBox = new google.maps.places.Autocomplete(input);
		

		google.maps.event.addListener(searchBox, 'place_changed', function() {
			
			if(typeof searchBox.getPlace=='function'){
				var place = searchBox.getPlace();		
				if(typeof place.geometry!='undefined'){
					slw_bounds_changed = false;
					var pos = {lat:place.geometry.location.lat(),lng:place.geometry.location.lng()};
					map.setCenter(pos);
					map.setZoom(<?php echo $attributes['zoom']; ?>);
					
					var slw_locations_div = jQuery("div.slw-locations div.slw-location");
					slw_locations_div.hide();
					
					jQuery.each(slw_locations_coord, function(loc_id, loc_coord){
						
						var calc_distance = slw_calc_distance(loc_coord, pos);	
						var distance_in_unit = calc_distance;			
						slw_locations_distance[loc_id] = distance_in_unit;	
						
						var loc_obj = jQuery('div.slw-locations div.slw-location[data-id="'+loc_id+'"]');
						loc_obj.attr('data-distance', distance_in_unit);
						loc_obj.find('div.slw-location-distance span').removeAttr('title').html(distance_in_unit+' '+distance_unit);
						
						if(calc_distance<=slw_search_list_diameter){						
							
							loc_obj.show();
						
						}
					});
					setTimeout(function(){ slw_bounds_changed = true; }, 1000);
					
					slw_locations_div = jQuery("div.slw-locations div.slw-location");
					
					slw_locations_div.sort(function(a, b){
						return jQuery(a).data("distance")-jQuery(b).data("distance")
					});
					jQuery("div.slw-locations").html(slw_locations_div);
				}
			}
		});

		google.maps.event.addListener(map, 'bounds_changed', function() {
			
			if(!slw_bounds_changed){ return; }
			
			var bounds =  map.getBounds();
			
			if(bounds==null || typeof bounds!='object' || (((typeof bounds=='object') && bounds!=null && Object.keys(bounds).length === 0))){ return; }

			var ne = bounds.getNorthEast();
			var sw = bounds.getSouthWest();
			
				
			
			
				
			var pos_1 = {lat:ne.lat(), lng:ne.lng()};
			var pos_2 = {lat:sw.lat(), lng:sw.lng()};
			
			jQuery('div.slw-locations div.slw-location').hide();
			jQuery.each(slw_locations_coord, function(loc_id, loc_coord){
				
				if(
						(parseFloat(loc_coord.lat)>=parseFloat(pos_2.lat) && parseFloat(loc_coord.lat)<=pos_1.lat)
					&&
						parseFloat(loc_coord.lng)>=parseFloat(pos_2.lng) && parseFloat(loc_coord.lng)<=pos_1.lng
				){
					jQuery('div.slw-locations div.slw-location[data-id="'+loc_id+'"]').show();
				}
			});

		});
		infoWindow = new google.maps.InfoWindow();
	
	
		if (navigator.geolocation && false) {
			
		 
			navigator.geolocation.getCurrentPosition(
			(position) => {
					const pos = {
						lat: position.coords.latitude,
						lng: position.coords.longitude,
					};
					var geocoder= new google.maps.Geocoder();
					var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
					var country = '';
					var state = '';
					var city = '';
					var address;
					geocoder.geocode({'latLng': latlng}, function(results, status) {
						if (status == google.maps.GeocoderStatus.OK) {
							
							if (results[1]) {
								//formatted address
								for (var i=0; i<results[0].address_components.length; i++) {
									for (var b=0;b<results[0].address_components[i].types.length;b++) {
										address= results[0].address_components[i];
										
										//console.log(results[0].address_components[i].types[b]+' = '+address.short_name);

										switch(results[0].address_components[i].types[b]){
											case 'country':
												country = address.short_name;
											break;
											case 'administrative_area_level_2':
												city = address.short_name;
											break;
											case 'administrative_area_level_1':
												state = address.short_name;
											break;
										}
									}
								}
    
								infoWindow.setPosition(pos);
								
								var address_str = city+', '+state+', '+country+'.';

								if(jQuery.trim(address_str)){
									infoWindow.setContent(address_str);
									infoWindow.open(map);
								}
								
								slw_visitor_position = pos;
								map.setCenter(pos);
								
							}
						}
					});
					
					
				},
				() => {
				  handleLocationError(true, infoWindow, map.getCenter());
				}
			);
		}else{
			//navigator.geolocation.getCurrentPosition(slw_navigator_success, slw_navigator_error, slw_location_options);	
		}
	  const iconBase =
		"<?php echo SLW_PLUGIN_URL; ?>/images/icons/";
	  const icons = {
		empty: {
		  icon: iconBase+"map-icon.png",
		}, 
	  };
		function handleLocationError(browserHasGeolocation, infoWindow, pos) {
			infoWindow.setPosition(pos);
			infoWindow.setContent(
			browserHasGeolocation
			  ? "Error: The Geolocation service failed."
			  : "Error: Your browser doesn't support geolocation."
			);
			infoWindow.open(map);
		}

	  const features = [
		
<?php 
	if(!empty($locations)){ 
		foreach($locations as $location){

			$locations_coord[] = 'slw_locations_coord['.$location['id'].'] = {}; ';
			$locations_coord[] = 'slw_locations_coord['.$location['id'].']["lat"] = "'.$location['lat'].'"; ';
			$locations_coord[] = 'slw_locations_coord['.$location['id'].']["lng"] = "'.$location['lng'].'"; ';
			
			$locations_list[] = array('id'=>$location['id'], 'location_timings'=>$location['location_timings'], 'location_phone'=>$location['location_phone'], 'title'=>$location['label'], 'address'=>$location['title'], 'email'=>$location['email'], 'distance'=>'', 'link'=>$location['link'], 'directions'=>'https://www.google.com/maps/dir//'.$location['title'].'/@'.$location['lat'].','.$location['lng'].',16z', 'location_popup'=>$location['location_popup']);
?>	
			{					
			  position: { lat: <?php echo $location['lat']; ?>, lng: <?php echo $location['lng']; ?> },
			  type: "<?php echo $location['type']; ?>",
			  label: "<?php echo $location['label']; ?>",
			  title: "<?php echo $location['title']; ?>",
			  location_id: "<?php echo $location['id']; ?>",
			},		  
<?php
		}
		
		
	}
?>		
		
	  ];
	  
		
			
	
		
		
		for (let i = 0; i < features.length; i++) {
			const marker = new google.maps.Marker({
			  position: features[i].position,
			  label: features[i].label,
			  title: features[i].title,
			  //icon: icons[features[i].type].icon,
			  map: map,
			});
			
			marker.addListener("click", () => {				
				var slw_popup = slw_map_popup(features[i].location_id, jQuery);
				if(slw_popup){
					jQuery.blockUI({ message: slw_popup, blockMsgClass: 'slw-blockui-wrapper', css: { backgroundColor: 'transparent', border: 'none', padding: '15px'} });
				}
			});

		
		}
	}
	jQuery('body').on('click', 'a.slw-map-close, div.blockUI.blockOverlay', function() { 
		jQuery.unblockUI(); 
	}); 
	
	
	function slw_map_popup(location_id, $){
		var ret;
		if($('#slw-location-'+location_id).length>0){
			ret = $.trim($('#slw-location-'+location_id).html());
		}else{
			ret = '';
		}
		return ret;
	}
	var slw_location_options = {
		enableHighAccuracy: true,
		timeout: 5000,
		maximumAge: 0
	};
	
	function slw_navigator_success(pos) {
		slw_visitor_position = pos.coords;
		//console.log(slw_visitor_position);
		
	}
	
	function slw_navigator_error(err) {
	  //console.warn(`ERROR(${err.code}): ${err.message}`);
	}
	

	
	
	var slw_rad = function(x) {
	  return x * Math.PI / 180;
	};
	
	var slw_getDistance = function(p1, p2) {
	  var R = 6378137; // Earth's mean radius in meter
	  var dLat = slw_rad(p2.lat() - p1.lat());
	  var dLong = slw_rad(p2.lng() - p1.lng());
	  var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
		Math.cos(slw_rad(p1.lat())) * Math.cos(slw_rad(p2.lat())) *
		Math.sin(dLong / 2) * Math.sin(dLong / 2);
	  var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
	  var d = R * c;
	  return d; // returns the distance in meter
	};
	
	

	<?php echo implode('', $locations_coord); ?>
	
	var slw_allow_geo = setInterval(function(){
		if(jQuery('div.slw-location-distance span').length>0){
			
			jQuery('div.slw-location-distance span').prop('title', slw_frontend.slw_allow_geo_tip).html(slw_frontend.slw_allow_geo).show();
			//jQuery('div.slw-location-distance span').eq(0).click();
			if (typeof slw_visitor_position!='undefined' && typeof slw_visitor_map!='undefined'){
				
			}else{
				navigator.geolocation.getCurrentPosition(slw_navigator_success, slw_navigator_error, slw_location_options);			
						
				jQuery('div.slw-map-wrapper .slw-locations .slw-location').eq(0).click();
				setTimeout(function(){ jQuery('div.slw-locations div.slw-location').show(); }, 500);				
			}
			clearInterval(slw_allow_geo);
		}
	}, 1000);
	
	var for_visitor_coords = setInterval(function(){
		
		
		
		if (typeof slw_visitor_position!='undefined' && typeof slw_visitor_position.latitude!='undefined' && typeof slw_visitor_position.longitude!='undefined'){
			//console.log(slw_visitor_position.latitude+' / '+slw_visitor_position.longitude);
			slw_visitor_map = new google.maps.LatLng(slw_visitor_position.latitude, slw_visitor_position.longitude);
			//console.log(slw_visitor_map);
		}
		
		if (typeof slw_visitor_position!='undefined' && typeof slw_visitor_map!='undefined'){
			//console.log(slw_visitor_map.lat()+' '+slw_visitor_map.lng());
			var location_position;
			jQuery.each(slw_locations_coord, function(i,v){
				var location_item = jQuery('div.slw-location[data-id="'+i+'"]'); 
				if(location_item.length>0){
					location_position = new google.maps.LatLng(v.lat, v.lng);
					//console.log(i);
					//console.log(location_position.lat()+' '+location_position.lng()+' '+slw_visitor_map.lat()+' '+slw_visitor_map.lng());
					var distance_in_meters = slw_getDistance(slw_visitor_map, location_position);
					
					if(distance_in_meters){
						distance_in_meters = distance_in_meters*distance_measures['fraction'];//0.000621;
						distance_in_meters = distance_in_meters.toFixed(2);
						slw_locations_udist[i] = distance_in_meters;
						
						var loc_obj = jQuery(location_item).attr('data-udist', distance_in_meters);
						loc_obj.find('div.slw-location-distance span').removeAttr('title').html(distance_in_meters+' '+distance_unit).show();
					}
				}
				
				
			});
			if(slw_locations_udist.length>0){
				var slw_locations_div = jQuery("div.slw-locations div.slw-location");
				slw_locations_div.sort(function(a, b){
					return jQuery(a).data("udist")-jQuery(b).data("udist")
				});
				jQuery("div.slw-locations").html(slw_locations_div);
				
				if(jQuery('div.slw-map-wrapper .slw-locations .slw-location').length>0){
					setTimeout(function(){
						jQuery('div.slw-map-wrapper .slw-locations .slw-location').eq(0).click();
						setTimeout(function(){ jQuery('div.slw-locations div.slw-location').show(); }, 500);
					}, 500);
				}

			}
			
			clearInterval(for_visitor_coords);
		}else{
			
		}
			
	}, 1000);
	
	
	
	jQuery(document).ready(function($){
		$('body').on('click', 'div.slw-location-distance span', function(){
			
			if (typeof slw_visitor_position!='undefined' && typeof slw_visitor_map!='undefined'){
				
			}else{
				navigator.geolocation.getCurrentPosition(slw_navigator_success, slw_navigator_error, slw_location_options);					
			}
			
		});
		$('body').on('click', 'div.slw-location-details a', function(){
			var location_id = jQuery(this).parents().eq(2).data('id');
			var slw_popup = slw_map_popup(location_id, jQuery);
			if(slw_popup){
				jQuery.blockUI({ message: slw_popup, css: { backgroundColor: 'transparent', blockMsgClass: 'slw-blockui-wrapper', border: 'none', padding: '15px'} });
			}
			
		});

		$('body').on('click', 'div.slw-map-wrapper .slw-locations .slw-location', function(){
			
			var location_id = $(this).data('id');
			slw_bounds_changed = false;
			
			var slw_location_coord = slw_locations_coord[location_id];
			
			if(typeof slw_location_coord!='undefined'){
				$('div.slw-map-wrapper .slw-locations .slw-location').removeClass('clicked');
				$(this).addClass('clicked');
				var pos = {lat:parseFloat(slw_location_coord.lat),lng:parseFloat(slw_location_coord.lng)};
				if(typeof map=='object'){
					map.setCenter(pos);
					map.setZoom(<?php echo $attributes['zoom']; ?>);
				}
				<?php if(wp_is_mobile() && false): ?>
				$('html, body').animate({
					scrollTop: $("#slw-map").offset().top
				}, 2000);
				<?php endif; ?>
				
				if(!slw_locations_off){
					slw_locations_off = true;
					$('div.slw-map-wrapper .slw-locations').hide();
					$('a.slw-locations-close').hide();
					$('a.slw-locations-open').show();					
				}
				$('div.slw-map-wrapper #slw-map').show();				
			}
			setTimeout(function(){ slw_bounds_changed = true; }, 1000);
		});
		
		$('body').on('click', '#slw-search', function(){
			$('div.slw-locations div.slw-location').show();
		});
		
		$('body').on('click', 'a.slw-locations-close', function(){
			$(this).hide();
			$('a.slw-locations-open').show();
			$('div.slw-map-wrapper .slw-locations').hide();
			slw_locations_off = true;
			$('div.slw-map-wrapper #slw-map').show();
		});
		$('body').on('click', 'a.slw-locations-open', function(){
			$(this).hide();
			$('a.slw-locations-close').show();
			$('div.slw-map-wrapper .slw-locations').show();
			slw_locations_off = false;
			$('div.slw-map-wrapper #slw-map').hide();
		});
		
		
		$(window).resize(function(){
			$('div.slw-map-wrapper .slw-locations').show();
			$('div.slw-map-wrapper #slw-map').show();
		});
	});
</script>
<style type="text/css">

	html,
	body {
	  height: 100%;
	  margin: 0;
	  padding: 0;
	}
	div.slw-map-wrapper{
		float: left;
		width: 100%;
		height: 500px;
		margin: 0 0 60px;
		position:relative;
	}
	div.slw-map-wrapper #slw-map {
		height: 500px;
		visibility: visible;		
		width:<?php echo $attributes['map-width']; ?>;
		float:left;
	}
	
	div.slw-map-controls{
		float:left;
		width:100%;
		margin-bottom:32px;
		text-align:center;
	}
	input.slw-search {
		height: 40px;
		width: 218px;
		display: block;
		margin: 0 auto !important;
		border: 1px solid #797373bf !important;
		padding: 10px !important;
		border-radius: 20px;
		font-weight: bold;
	}	
	
	div.slw-map-wrapper .slw-locations .slw-location {
		border: 1px solid #0000000d;
		margin: 0 0 10px 0;
		padding: 20px;
		min-height: 190px;
		border-radius: 12px;
		box-shadow: 0px 1px #ccccccc4;
		width:100%;
		position:relative;
		cursor:pointer;
	}
	div.slw-map-wrapper .slw-locations .slw-location:hover,
	div.slw-map-wrapper .slw-locations .slw-location.clicked {
		background-color:#6665651c;
	}
	div.slw-map-wrapper .slw-locations .slw-location .slw-location-title,
	div.slw-map-wrapper .slw-locations .slw-location .slw-location-timing,
	div.slw-map-wrapper .slw-locations .slw-location .slw-location-phone{
		display:none;
	}
	div.slw-map-wrapper .slw-locations .slw-location .slw-location-address{
		color:#DA121D;
		font-weight:bold;
		font-size: 15px;
		line-height: 20px;
	}
	div.slw-map-wrapper .slw-locations .slw-location .slw-location-email,
	div.slw-map-wrapper .slw-locations .slw-location .slw-location-distance,
	div.slw-map-wrapper .slw-locations .slw-location .slw-location-timing,
	div.slw-map-wrapper .slw-locations .slw-location .slw-location-phone,
	div.slw-map-wrapper .slw-locations .slw-location .slw-location-details{
		font-weight: normal;
		font-size: 12px;
	}
	
	div.slw-map-wrapper .slw-locations .slw-location .slw-location-distance{

	}
	div.slw-map-wrapper .slw-locations .slw-location .slw-location-email > i, 
	div.slw-map-wrapper .slw-locations .slw-location .slw-location-distance > i,
	div.slw-map-wrapper .slw-locations .slw-location .slw-location-timing > i,
	div.slw-map-wrapper .slw-locations .slw-location .slw-location-phone > i,
	div.slw-map-wrapper .slw-locations .slw-location .slw-location-details > i {
		margin: 0 6px 0 0;
		font-size: 15px;
		width: 16px;
	}
	div.slw-map-wrapper .slw-locations .slw-location .slw-location-distance > span {
		display:none;
	}
	div.slw-map-wrapper .slw-locations .slw-location .slw-location-timing a,
	div.slw-map-wrapper .slw-locations .slw-location .slw-location-phone a,
	div.slw-map-wrapper .slw-locations .slw-location .slw-location-details a{
		cursor:pointer;
		color:#000;
		text-decoration:none;
	}
	div.slw-map-wrapper .slw-locations .slw-location .slw-location-btns {
		width: 100%;
		position: absolute;
		bottom: 0;
		margin: 0 0 12px 0;
	}
	div.slw-map-wrapper .slw-locations .slw-location .slw-location-btns a{	
		height:32px;
		border-radius: 20px;
		text-align:center;
		font-weight:bold;
		text-transform:uppercase;
		color:#fff;
		display:block;
		line-height: 32px;
		font-size: 14px;
	}
	div.slw-map-wrapper .slw-locations .slw-location .slw-location-btns .slw-shop-btn {
		background-color:#FF000E;
		width:178px;
		float:left;
		text-decoration:none;
	}
	div.slw-map-wrapper .slw-locations .slw-location .slw-location-btns .slw-go-btn {
		background-color: #505050;
		width: 122px;
		float: left;
		margin: 0 0 0 16px;
		text-decoration:none;
	}
	
	
	div.slw-map-wrapper .slw-locations {
		
		width:<?php echo $attributes['list-width']; ?>;
		float:left;
		max-height: 500px;
		margin: 0 0 0 30px;				
		padding: 0 1rem 1rem 1rem;
		overflow-y: auto;
		overflow-x: hidden;
		direction: ltr;
		scrollbar-color: #FF000E #e4e4e4;
		scrollbar-width: thin;
		position:relative;
		h2 {
			font-size: 1.5rem;
			font-weight: 700;
			margin-bottom: 1rem;
		}
		
		p + p {
			margin-top: 1rem;
		}
	}
	
	div.slw-map-wrapper a.slw-locations-open, 
	div.slw-map-wrapper a.slw-locations-close {
		position: absolute;
		left: 18px;
		top: -24px;
		display: none;
		cursor: pointer;
		z-index:1;
	}
	div.slw-map-wrapper a.slw-locations-open{
		right: 18px;
		left: unset;
	}
	div.slw-map-wrapper a.slw-locations-open > i,
	div.slw-map-wrapper a.slw-locations-close > i{
		color:red;
		font-size:24px;
	}
	
	div.slw-map-wrapper .slw-locations:-webkit-scrollbar {
		width: 20px;
	}
	
	div.slw-map-wrapper .slw-locations:-webkit-scrollbar-track {
		background-color: #FF000E;
		border-radius: 100px;
	}
	
	div.slw-map-wrapper .slw-locations:-webkit-scrollbar-thumb {
		border-radius: 100px;
		background-image: linear-gradient(180deg, #FF000E 0%, #FF000E 99%);
		box-shadow: inset 2px 2px 5px 0 rgba(#fff, 0.5);
	}
	div.slw-map-wrapper.map-free .slw-locations {
		margin: 0 auto;
		float: none;
	}
	div.slw-map-wrapper.locations-free #slw-map {
		width: 99.9%;
		margin: 0 auto;
		float: none;
	}
	div.slw-map-wrapper .slw-locations .slw-location div.slw-map-popup{
		display:none !important;
	}
	div.blockUI div.slw-map-popup {
		margin: 10px;
		border: 2px solid #EBE9E3;
		border-radius: 12px;
		padding: 20px 10px 10px 68px;
		position: relative;
		text-align: left;
		float: left;
		width: 390px;
		height:345px;
		background-color: #fff;
		cursor: text;
	}
	div.blockUI div.slw-map-popup a.slw-map-marker{
		position: absolute;
		left: 16px;
		top: 20px;
	}
	div.blockUI div.slw-map-popup a.slw-map-marker i {
		color: #ff0000;
		font-size: 48px;
	}
	div.blockUI div.slw-map-popup a.slw-map-close {
		cursor: pointer;
		position: absolute;
		right: 36px;
		top: 20px;
	}
	div.blockUI div.slw-map-popup a.slw-map-close i{
		color:#ff0000;
		font-size:46px;
	}
	div.blockUI div.slw-map-popup div.underlined {
		text-decoration: underline;
		width: 264px;
	}
	div.blockUI div.slw-map-popup .red {
		color: #ff0000;
	}
	div.blockUI div.slw-map-popup strong{
		font-size: 18px;
	}
	div.blockUI div.slw-map-popup h6,
	div.blockUI div.slw-map-popup h5,
	div.blockUI div.slw-map-popup h4,
	div.blockUI div.slw-map-popup h3,
	div.blockUI div.slw-map-popup h2,
	div.blockUI div.slw-map-popup h1 {
		margin:8px 0 0 0;
	}
	div.blockUI div.slw-map-popup h6 {
		font-size: 18px;
	}
	div.blockUI div.slw-map-popup h5 {
		font-size: 20px;
	}
	div.blockUI div.slw-map-popup h4 {
		font-size: 22px;
	}
	div.blockUI div.slw-map-popup h3 {
		font-size: 24px;
	}
	div.blockUI div.slw-map-popup h2 {
		font-size: 26px;
	}
	div.blockUI div.slw-map-popup h1 {
		font-size: 28px;
	}
	div.blockUI div.slw-map-popup ul{
		margin:0;
		padding:0;
		display:table;
	}
	div.blockUI div.slw-map-popup ul li{
		list-style:none;
		line-height:20px;
		float:left;
		width:100%;
	}
	div.blockUI div.slw-map-popup ul li label{
		font-size:14px;
		color:#000;
		padding: 0 6px 0 0;
		float:left;
	}
	div.blockUI div.slw-map-popup ul li a{
		color:#000;		
		font-size:16px;
		cursor:pointer;
	}
	div.blockUI div.slw-map-popup ul li a:hover{
		text-decoration:underline;
	}
	div.blockUI div.slw-map-popup ul li span{
		font-size:14px;
		color: #ff0000;
		float:left;
	}
	div.blockUI div.slw-map-popup div.map-popup-footer {
		padding: 12px 0 0 0;
		float: left;
		width: 100%;
	}
	div.blockUI div.slw-map-popup div.map-popup-footer ul {
		width: 180px;
	}
	div.blockUI div.slw-map-popup div.map-popup-footer ul li {
		line-height: 26px;
	}
	div.blockUI div.slw-map-popup div.map-popup-footer .float-left{
		float:left;
	}
	div.blockUI div.slw-map-popup div.map-popup-footer .float-right{
		float: right;
	}
	div.blockUI div.slw-map-popup div.map-popup-footer .clear-both {
		clear: both;
		float: left;
		width: 100%;
		margin: 20px 0 0 0;
	}
	div.blockUI div.slw-map-popup div.map-popup-footer .slw-button {
		font-size: 24px;
		text-align: center;
		display: block;
		color: #fff;
		background-color: #ff0000;
		width: 115px;
		border-radius: 24px;
		height: 46px;
		line-height: 46px;
		cursor: pointer;
		position: relative;
		top: -4px;
	}
	div.blockUI.blockOverlay {
		cursor: pointer !important;
	}
	

	@media only screen and (max-device-width: 480px) {
		div.slw-map-wrapper{
			height:auto;
		}
		div.slw-map-wrapper #slw-map{
			width:100%;
			height:238px;
		}
		div.slw-map-wrapper .slw-locations{
			width:100%;
			margin: 20px 0 0 0;
			max-height:none;
			height:auto;
			overflow-y: visible;
		}
		div.slw-map-wrapper .slw-locations .slw-location{
			padding: 20px 10px;
		}
		div.slw-map-wrapper .slw-locations .slw-location .slw-location-btns a{
			font-size:12px;
		}
		div.slw-map-wrapper .slw-locations .slw-location .slw-location-btns .slw-shop-btn{
			width:150px;
		}
		div.slw-map-wrapper .slw-locations .slw-location .slw-location-btns .slw-go-btn{
			width:100px;
		}
		div.slw-map-wrapper .slw-locations .slw-location .slw-location-address {
			font-size: 13px;
			line-height: 16px;
			margin: 0 0 6px 0;
		}
		div.slw-map-wrapper .slw-locations .slw-location .slw-location-email, 
		div.slw-map-wrapper .slw-locations .slw-location .slw-location-distance{
			font-size:11px;
		}
		
		div.blockUI {
			width: 100% !important;
			left: 0 !important;
			top:25% !important;
		}
		div.blockUI div.slw-map-popup{
			width: 100%;
			height:auto;
			margin:0;
		}
		div.blockUI div.slw-map-popup div.underlined {
			width: 76%;
			line-height: 24px;
		}
		div.blockUI div.slw-map-popup div.map-popup-footer .float-right {
			display: table;
			margin: 12px auto;
			clear: both;
			float:left;
		}
		div.blockUI div.slw-map-popup div.map-popup-footer .slw-button{
			top:0;

		}
		
	}
	
	@media screen and (max-width: 740px),
	@media only screen and (max-device-width: 740px) {
		div.slw-map-wrapper .slw-locations{
			background-color: #fff;
			padding-top: 30px;
			width:100%;
			margin:0;
		}
		div.slw-map-wrapper a.slw-locations-open{
			display:block;
		}
		div.slw-map-wrapper #slw-map{
			width:100%;
		}
	}
	
	@media screen and (min-width: 741px) and (max-width: 950px),
	@media only screen and (min-device-width: 741px) and (max-device-width: 950px) {
		div.slw-map-wrapper .slw-locations{
			background-color: #fff;
			padding-top: 30px;
			width:50%;
			margin:0;
			right:0;
			float:right;
		}
		div.slw-map-wrapper #slw-map{
			width:50%;
		}		
		div.slw-map-wrapper .slw-locations .slw-location .slw-location-btns .slw-shop-btn{
			font-size: 12px;
			width: 146px;
		}
		div.slw-map-wrapper .slw-locations .slw-location .slw-location-btns .slw-go-btn{
			font-size: 12px;
		}
	}
	
	@media screen and (min-width: 951px) and (max-width: 1050px),
	@media only screen and (min-device-width: 951px) and (max-device-width: 1050px) {
		div.slw-map-wrapper .slw-locations{
			background-color: #fff;
			padding-top: 30px;
			width:45%;
			margin:0;
			right:0;
			float:right;
		}
		div.slw-map-wrapper #slw-map{
			width:55%;
		}
		div.slw-map-wrapper .slw-locations .slw-location .slw-location-btns .slw-shop-btn{
			font-size: 12px;
			width: 146px;
		}
		div.slw-map-wrapper .slw-locations .slw-location .slw-location-btns .slw-go-btn{
			font-size: 12px;
		}
	}
	
	@media screen and (min-width: 1050px) and (max-width: 1360px),
	@media only screen and (min-device-width: 1050px) and (max-device-width: 1360px) {
		div.slw-map-wrapper .slw-locations{
			background-color: #fff;
			padding-top: 30px;
			width:35%;
			margin:0;
			right:0;
			float:right;
		}
		div.slw-map-wrapper #slw-map{
			width:65%;
		}
	}	
</style>    
<?php if($attributes['search-field']=='yes'): slw_map_controls($attributes['search-field-placeholder']); endif; ?>
<?php if($attributes['map']=='yes' || $attributes['locations-list']=='yes'): ?>

<div class="slw-map-wrapper <?php echo ($attributes['map']=='yes'?'':'map-free'); ?> <?php echo ($attributes['locations-list']=='yes'?'':'locations-free'); ?>">
<?php if($attributes['search-field']=='slw-map-wrapper'): slw_map_controls($attributes['search-field-placeholder']); endif; ?>

<?php if($attributes['map']=='yes'): ?>
<div id="slw-map"></div>
<?php endif; ?>
<?php if($attributes['locations-list']=='yes'): ?>
<a class="slw-locations-open"><i class="fas fa-angle-double-left"></i></a>
<a class="slw-locations-close"><i class="fas fa-angle-double-right"></i></a>
<div class="slw-locations">
    <?php if(!empty($locations_list)): ?>
    <?php foreach($locations_list as $location_data): 
	
			$location_popup = trim($location_data['location_popup']);
			
			switch($attributes['shop-location-link']){
				
				case 'default':
					$location_data['link'] = add_query_arg('set-location', $location_data['id'], home_url( $wp->request ));
				break;			
				
				case 'shop':
					$location_data['link'] = add_query_arg('set-location', $location_data['id'], get_permalink( wc_get_page_id( 'shop' ) ));
				break;
				
				case 'return':
				case 'previous':
				case 'back':
				case 'referrer':
					$location_data['link'] = add_query_arg('set-location', $location_data['id'], wp_get_referer() );
				break;
				
				case 'store-link':
					$location_data['link'] = add_query_arg('set-location', $location_data['id'], $location_data['link']);
				break;
								
				default:
					$location_data['link'] = add_query_arg('set-location', $location_data['id'], $attributes['shop-location-link']);
				break;
				
			}
			
			
	?>
    	<div class="slw-location" data-id="<?php echo $location_data['id']; ?>">
        	<div class="slw-location-title"><?php echo $location_data['title']; ?></div>
            <div class="slw-location-address"><?php echo $location_data['address']; ?></div>
            <?php if($location_data['email']): ?><div class="slw-location-email"><i class="far fa-envelope"></i><?php echo $location_data['email']; ?></div><?php endif; ?>
            <?php if($location_data['location_phone']): ?>
            <div class="slw-location-phone"><i class="fas fa-phone"></i><?php echo $location_data['location_phone']; ?></span></div>
            <?php endif; ?>
            <div class="slw-location-distance"><i class="fas fa-map-marker-alt"></i><span><?php echo $location_data['distance']; ?></span></div>
            <?php if($location_data['location_timings']): ?>
            <div class="slw-location-timing"><i class="fas fa-clock"></i><span><?php echo $location_data['location_timings']; ?></span></div>
            <?php endif; ?>
            
            <div class="slw-location-details"><i class="fas fa-info"></i><span><a class="slw-location-detail"><?php _e('More Details', 'stock-locations-for-woocommerce'); ?></a></span></div>
            <div class="slw-location-btns">
            <?php do_action('before_slw_shop_button', $location_data); ?>
            <a class="slw-shop-btn" href="<?php echo $location_data['link']; ?>"><?php echo $attributes['shop-button-text']; ?></a><a target="_blank" class="slw-go-btn" href="<?php echo $location_data['directions']; ?>"><?php echo $attributes['directions-button-text']; ?></a>
            <?php do_action('after_slw_shop_button', $location_data); ?>
            </div>
            
            <?php if($location_popup): ?>
            <div id="slw-location-<?php echo $location_data['id']; ?>">
            	<div class="slw-map-popup">
                <a class="slw-map-marker"><i class="fas fa-map-marker-alt"></i></a>
            	<div class="slw-map-popup-content"><?php echo $location_popup; ?></div>
                <a class="slw-map-close"><i class="fas fa-angle-up"></i></a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    <?php endif; ?>

</div>
<?php endif; ?>

</div>
<?php endif; ?>
<?php 
	if($slw_gkey){
		echo '
		<script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
		<script
		  src="https://maps.googleapis.com/maps/api/js?key='.$slw_gkey.'&callback=initSlwMap&v=weekly&libraries=places"
		  async
		></script>';	
	}
	
	//wp_enqueue_style( 'font-awesome', SLW_PLUGIN_DIR_URL . 'css/fontawesome.min.css', array(), date('m') );
?>	

