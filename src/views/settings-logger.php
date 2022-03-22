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
?>

<div class="slw_logger mt-4">
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

?>
<div class="alert alert-info" role="alert">
  <?php echo __('Nothing logged yet.', 'stock-locations-for-woocommerce'); ?>
</div>
<?php			
		}
        ?>
</div>		