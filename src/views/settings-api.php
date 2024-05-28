<?php if ( ! defined('WPINC') ) die; ?>
<?php global $wc_slw_pro, $wc_slw_premium_copy, $slw_api_valid_keys, $slw_theme_name; ?>
<?php
	$all_requests = get_option('slw_api_request_sources', array());
				
	$all_requests = (is_array($all_requests)?$all_requests:array());
	
	$validated_requests = get_option('slw_api_request_validated', array());
				
	$validated_requests = (is_array($validated_requests)?$validated_requests:array());
	
	//pree($validated_requests);
?>
<div class="slw_api_crons mt-4">
       
<div class="alert alert-info" role="alert">
  <?php echo __('This section is for advanced level users. Please do not try with inadequate knowledge.', 'stock-locations-for-woocommerce'); ?> <a class="btn btn-sm btn-danger" href="<?php echo $wc_slw_premium_copy; ?>?help" target="_blank"><?php echo __('Contact Developer', 'stock-locations-for-woocommerce'); ?></a></div>
  


<label class="switch" style="float:right;">
  <input <?php checked(get_option('slw_api_status')==true); ?> name="slw-api-status" id="slw-api-status" value="yes" type="checkbox" data-on="<?php echo __('Enabled', 'stock-locations-for-woocommerce'); ?>" data-off="<?php echo __('Disabled', 'stock-locations-for-woocommerce'); ?>" />
  <span class="slider round"></span>
</label>


<?php			

	if(!empty($slw_api_valid_keys)){
?>
<div class="slw-api-urls">
	<ul>
    	<li><b><?php echo home_url(); ?>/?slw-api&</b><?php echo '<span>'.implode('=</span>&<span>', array_keys($slw_api_valid_keys)).'</span>'; ?> <a href="https://www.youtube.com/embed/si_DUe-8ncY" target="_blank"><i class="fab fa-youtube"></i></a></li>
	</ul>        
</div>
<table cellpadding="0" cellspacing="0">
<?php		
		foreach($slw_api_valid_keys as $param=>$param_data){
?>
<tr title="<?php echo (isset($param_data['tooltip'])?$param_data['tooltip']:''); ?>">
	<td><?php echo $param; ?></td><td><?php echo $param_data['type']; ?></td><td><?php echo $param_data['options']; ?></td>
</tr>    
<?php			
		}
?>
</table>
<?php		
	}
	

?>
<div class="slw-api-requests">
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

<br />
<br />

<ul>
	<li>
    	add_filter('slw_stock_allocation_notification_message', '<?php echo $slw_theme_name; ?>_stock_allocation_notification_message_callback', 10, 4);
    </li>
    <li>
    	add_filter('allow_stock_allocation_notification', '<?php echo $slw_theme_name; ?>_allow_stock_allocation_notification_callback', 10, 4);
    </li>    
    <li>
    	add_filter('slw_stock_allocation_notification_subject', '<?php echo $slw_theme_name; ?>_allow_stock_allocation_notification_callback', 10, 4);
    </li>    
    <li>
    	add_filter('slw_edit_stocks_filter', '<?php echo $slw_theme_name; ?>_slw_edit_stocks_filter_callback', 10, 3);
    </li>    
     <li>
    	add_filter('slw_product_stock_location_notice', '<?php echo $slw_theme_name; ?>_slw_product_stock_location_notice_callback', 10, 4);
    </li>       
    
    
    
    
    
    
</ul>
</div>		