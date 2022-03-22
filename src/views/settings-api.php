<?php if ( ! defined('WPINC') ) die; ?>
<?php global $wc_slw_pro, $wc_slw_premium_copy, $slw_api_valid_keys; ?>

<div class="slw_api_crons mt-4">
        <?php

			
?>
<div class="alert alert-info" role="alert">
  <?php echo __('This section is for advanced level users. Please do not try with inadequate knowledge.', 'stock-locations-for-woocommerce'); ?> <a class="btn btn-sm btn-danger" href="<?php echo $wc_slw_premium_copy; ?>?help" target="_blank"><?php echo __('Contact Developer', 'stock-locations-for-woocommerce'); ?></a></div>
  


<label class="switch" style="float:right;">
  <input <?php checked(get_option('slw_api_status')==true); ?> name="slw-api-status" id="slw-api-status" value="yes" type="checkbox" data-toggle="toggle" data-on="<?php echo __('Enabled', 'stock-locations-for-woocommerce'); ?>" data-off="<?php echo __('Disabled', 'stock-locations-for-woocommerce'); ?>" />
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
<tr>
	<td><?php echo $param; ?></td><td><?php echo $param_data['type']; ?></td><td><?php echo $param_data['options']; ?></td>
</tr>    
<?php			
		}
?>
</table>
<?php		
	}

?>
</div>		