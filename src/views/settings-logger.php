<?php if ( ! defined('WPINC') ) die; ?>


<div class="slw_logger mt-4">
        <?php
		
		$slw_logger = slw_logger('debug');
        
        if(!empty($slw_logger)){
            krsort($slw_logger);
            ?>
            
       
            <div style="float: right"><a class="slw_clear_debug_log"><?php _e('Clear Debug Log', 'woo-order-splitter'); ?> <i class="fas fa-trash"></i></a> </div>
       
                    
            <ul class="slw_debug_log">
                <?php
				//pree($slw_logger);
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
			slw_notices(__('Nothing logged yet.', 'stock-locations-for-woocommerce'), true);
		}
        ?>
</div>		