<?php if ( ! defined('WPINC') ) die; ?>
<?php global $wc_slw_pro, $wc_slw_premium_copy, $slw_crons_valid_keys, $wpdb; ?>

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

	<ul>
    	<li><i>curl "</i><?php echo home_url(); ?>/?slw-crons"</li></li>
    	<li><i>curl "</i><b><?php echo home_url(); ?>/?slw-crons&</b><?php echo '<span>'.implode('=</span>&<span>', array_keys($slw_crons_valid_keys)).'</span>'; ?></b><i>"</i> <a href="https://www.youtube.com/embed/si_DUe-8ncY?start=114" target="_blank"><i class="fab fa-youtube"></i></a></li>
        <li>&nbsp;</li>
	</ul>

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
        <li>&nbsp;</li>
        <li style="background-color: #4200FFB0;	color: #fff;	padding: 10px 20px;	border-radius: 22px;"><strong><?php _e('Try one of the following cron job arrangements:', 'stock-locations-for-woocommerce'); ?></strong> <a title="<?php _e('Click here to understand how to create a custom cron job command for your products stock status.', 'stock-locations-for-woocommerce'); ?>" style="font-size:12px; color:#FF0; margin-bottom:10px;" href="https://wordpress.org/support/topic/stock-not-reducing-when-orders-received/#post-17016960" target="_blank">[<?php _e('How does it work? Click here to understand.', 'stock-locations-for-woocommerce'); ?>]</a></li>

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