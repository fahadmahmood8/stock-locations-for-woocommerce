<?php if ( ! defined('WPINC') ) die; ?>
<?php global $wc_slw_pro, $wc_slw_premium_copy, $slw_crons_valid_keys; ?>

<div class="slw_api_crons mt-4">

    
    
    <?php			

	if(!empty($slw_crons_valid_keys)){
?>
<div class="slw-api-urls">

	<ul>
    	<li><?php echo home_url(); ?>/?slw-crons</li>
    	<li><b><?php echo home_url(); ?>/?slw-crons&</b><?php echo '<span>'.implode('=</span>&<span>', array_keys($slw_crons_valid_keys)).'</span>'; ?> <a href="https://www.youtube.com/embed/si_DUe-8ncY?start=114" target="_blank"><i class="fab fa-youtube"></i></a></li>
	</ul>        
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
<?php		
	}

?>
</div>		