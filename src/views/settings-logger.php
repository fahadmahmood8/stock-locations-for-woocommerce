<?php 
namespace SLW\SRC\Classes;
namespace SLW\SRC\Helpers;

use SLW\SRC\Classes\SlwAdminNotice;
use SLW\SRC\Helpers\SlwOrderItemHelper;
use SLW\SRC\Helpers\SlwStockAllocationHelper;
use SLW\SRC\Helpers\SlwWpmlHelper;
use SLW\SRC\Helpers\SlwProductHelper;
use SLW\SRC\Classes\SlwLocationTaxonomy;
use SLW\SRC\Helpers\SlwMailHelper;

//$stockAllocation = SlwStockAllocationHelper::getStockAllocation(33, 4, 0, false, 31);

global $slw_logs_status;
?>

<div class="slw_logger mt-4">


<label class="switch" style="float:right; margin-bottom:20px;">
  <input <?php checked($slw_logs_status); ?> name="slw-logs-status" id="slw-logs-status" value="yes" type="checkbox" data-on="<?php echo __('Enabled', 'stock-locations-for-woocommerce'); ?>" data-off="<?php echo __('Disabled', 'stock-locations-for-woocommerce'); ?>" />
  <span class="slider round"></span>
</label>

        <?php
		
		if(function_exists('wc_slw_logger_extended') && class_exists('SlwOrderItem')){
			$obj = new SlwOrderItem;
			wc_slw_logger_extended($obj);
		}else{

			//wc_slw_logger_extended();
		}
		
		$slw_logger = wc_slw_logger('debug');
        
        if(!empty($slw_logger)){
            krsort($slw_logger);
            ?>
            
       
            <div style="float: right"><a class="btn btn-sm btn-danger slw_clear_debug_log"><?php _e('Clear Debug Log', 'stock-locations-for-woocommerce'); ?> <i class="fas fa-trash"></i></a> </div>
       
                    
            <ul class="slw_debug_log">
                <?php

                foreach($slw_logger as $log){
                    ?>
                    <li>
					<?php 
					if(is_array($log) || is_object($log)){
						pree($log);
					}else{
						echo $log;
					}
					?>
                    </li>
                    <?php
                }
                ?>
            </ul>
            <?php
        }else{

if($slw_logs_status){
?>
<div class="alert alert-info" role="alert" style="clear:both">
  <?php echo __('Nothing logged yet.', 'stock-locations-for-woocommerce'); ?>
</div>
<?php			
}else{
?>
<div class="alert alert-danger" role="alert" style="clear:both">
  <?php echo __('Logs are disabled.', 'stock-locations-for-woocommerce'); ?>
</div>
<?php			
}
		}
        ?>
</div>		