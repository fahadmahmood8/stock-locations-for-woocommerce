<?php if ( ! defined('WPINC') ) die; ?>
<?php global $wc_slw_pro, $wc_slw_premium_copy; ?>

<div class="slw_widgets slw_need_popup mt-4">
        <?php

		
        if($wc_slw_pro && function_exists('wc_slw_widgets')){
            wc_slw_widgets();

        }else{
			
?>
<div class="alert alert-info" role="alert">
  <?php echo __('This section is available in premium version.', 'stock-locations-for-woocommerce'); ?> <a class="btn btn-sm btn-danger" href="<?php echo $wc_slw_premium_copy; ?>" target="_blank"><?php echo __('Go Premium', 'stock-locations-for-woocommerce'); ?></a>
</div>
<?php			
		}
?>
</div>		