<?php 
namespace SLW\SRC\Classes;
use SLW\SRC\Classes\SlwAdminNotice;
use SLW\SRC\Helpers\SlwOrderItemHelper;
use SLW\SRC\Helpers\SlwStockAllocationHelper;
use SLW\SRC\Helpers\SlwWpmlHelper;
use SLW\SRC\Helpers\SlwProductHelper;
?>

<div class="slw_logger mt-4">
        <?php
		
		if(array_key_exists('order_id', $_GET)){
			$order = wc_get_order($_GET['order_id']);
			if(!empty($order)){
				foreach($order->get_items() as $key=>$data){
					pree($data->get_product_id().' / '.$data->get_variation_id().' / '.$data->get_quantity());
					pree(wc_get_order_item_meta($key, ''));
				}
			}
		}
		if(array_key_exists('product_id', $_GET)){
			//$productStockLocations = SlwStockAllocationHelper::sortLocationsByPriority(SlwStockAllocationHelper::getProductStockLocations($_GET['product_id']));
			//pree($productStockLocations);
			$stockAllocation = SlwStockAllocationHelper::getStockAllocation($_GET['product_id'], 3);
			pree($stockAllocation);
		}
		
		$slw_logger = wc_slw_logger('debug');
        
        if(!empty($slw_logger)){
            krsort($slw_logger);
            ?>
            
       
            <div style="float: right"><a class="btn btn-sm btn-danger slw_clear_debug_log"><?php _e('Clear Debug Log', 'woo-order-splitter'); ?> <i class="fas fa-trash"></i></a> </div>
       
                    
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

?>
<div class="alert alert-info" role="alert">
  <?php echo __('Nothing logged yet.', 'stock-locations-for-woocommerce'); ?>
</div>
<?php			
		}
        ?>
</div>		