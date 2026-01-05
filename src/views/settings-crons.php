<?php if ( ! defined('WPINC') ) die; ?>
<?php global $wc_slw_pro, $wc_slw_premium_copy, $slw_crons_valid_keys, $wpdb; ?>
<?php
	$all_requests = get_option('slw_cron_request_sources', array());
				
	$all_requests = (is_array($all_requests)?$all_requests:array());
	
	$validated_requests = get_option('slw_cron_request_validated', array());
				
	$validated_requests = (is_array($validated_requests)?$validated_requests:array());
	
	//pree($validated_requests);
?>
<div class="slw_api_crons mt-4">

    
    
    <?php			

	if(!empty($slw_crons_valid_keys)){
		
		$total_fetched = $wpdb->get_row("SELECT COUNT(*) AS total_products FROM $wpdb->posts WHERE post_type IN ('product','product_variation') AND post_status NOT IN ('trash')");
		
		$total_products = (is_object($total_fetched) && isset($total_fetched->total_products))?$total_fetched->total_products:0;
		
		$recommended_curl = '';
		
		$intervals = array(5,10,15,30,60); 
		
		//pree($intervals);
?>
<div class="slw-api-urls">
<label class="switch red" style="float:right;" title="<?php echo __('Click here to enable/disable IP based restrictions for the cron job scripts', 'stock-locations-for-woocommerce'); ?>">
  <input <?php checked(get_option('slw_crons_status')==true); ?> name="slw-crons-status" id="slw-crons-status" value="yes" type="checkbox" data-on="<?php echo __('Enabled', 'stock-locations-for-woocommerce'); ?>" data-off="<?php echo __('Disabled', 'stock-locations-for-woocommerce'); ?>" />
  <span class="slider round"></span>
</label>
	<ul>
    	<li><i>curl "</i><?php echo home_url(); ?>/?slw-crons"</li></li>
    	<li><i>curl "</i><b><?php echo home_url(); ?>/?slw-crons&</b><?php echo '<span>'.implode('=</span>&<span>', array_keys($slw_crons_valid_keys)).'</span>'; ?></b><i>"</i> <a href="https://www.youtube.com/embed/si_DUe-8ncY?start=114" target="_blank"><i class="fab fa-youtube"></i></a></li>
        <li>&nbsp;</li>
	</ul>
    
<div class="slw-cron-requests">




	<input name="validate_request[]" type="checkbox" value="default" checked="checked" style="display:none" />
	<table cellpadding="10" cellspacing="0">
    	<thead>
        	<tr>
                <th><?php echo __('Request Source', 'stock-locations-for-woocommerce'); ?></th>
                <th><?php echo __('Last Ping', 'stock-locations-for-woocommerce'); ?></th>
                <th><?php echo __('Allow/Reject?', 'stock-locations-for-woocommerce'); ?></th>
			</tr>                
        </thead>
        <tbody>
        	<?php if(!empty($all_requests)): foreach($all_requests as $timestamp=>$source): $valid = in_array($source, $validated_requests); ?>
    		<tr>
            	<td><?php echo $source; ?></td>
                <td><?php echo $timestamp?date('d M, Y h:i:s A', $timestamp):'-'; ?></td>
                <td><a class="<?php echo $valid?'valid':'invalid'; ?>"><input name="validate_request[]" value="<?php echo $source; ?>" type="checkbox" <?php echo checked($valid); ?> /></a></td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    
    </table>

</div>    

<table cellpadding="0" cellspacing="0">
<?php		
		foreach($slw_crons_valid_keys as $param=>$param_data){
?>
<tr>
	<td><?php echo $param; ?></td><td><?php echo $param_data['type']; ?></td><td><?php echo $param_data['options']; ?></td>
</tr>    
<?php			
		}
		
		
		
?>
</table>
    
    <ul>
        <li></li>
        <li style="background-color: #4200FFB0;	color: #fff;	padding: 10px 20px;	border-radius: 22px;"><strong><?php _e('Try one of the following cron job arrangements:', 'stock-locations-for-woocommerce'); ?></strong> <a title="<?php _e('Click here to understand how to create a custom cron job command for your products stock status.', 'stock-locations-for-woocommerce'); ?>" style="font-size:12px; color:#FF0; margin-bottom:10px;" href="https://wordpress.org/support/topic/stock-not-reducing-when-orders-received/#post-17016960" target="_blank">[<?php _e('How does it work? Click here to understand.', 'stock-locations-for-woocommerce'); ?>]</a> <strong style="color:yellow; float:right;" title="<?php _e('You can use this shortcode to check the stock statuses and extra taxonomies related to the products listed.', 'stock-locations-for-woocommerce'); ?>"><?php _e('Shortcode', 'stock-locations-for-woocommerce'); ?>: [SLW-SHOW-PRODUCTS-STOCK-OVERVIEW]</strong></li>

        <?php if(!empty($intervals)){  foreach($intervals as $interval){ ?>
        <?php 
		
		if($total_products>0){
			$round = (60/$interval);
			$recommended_curl = 'action=update-stock&limit='.ceil($total_products/$round).'&reconsider=hour';
		?>		
        
        <li style="padding-left:20px;"><i style="color:#0C6;">curl </i><b>"<?php echo home_url(); ?>/?slw-crons&</b><?php echo $recommended_curl; ?>"</b> <span style="color:#06F; padding-left:120px;">[<?php echo sprintf(__('Every %dth minute interval', 'stock-locations-for-woocommerce'), $interval); ?>]</span></li>
        
        <?php 
		
		}
		
		?>
        <?php } } ?>
        <li>&nbsp;</li>
        <li>&nbsp;</li>

	</ul>        
</div>



<?php		
	
		
		
	}

?>
</div>		