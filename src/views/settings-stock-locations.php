<?php if ( ! defined('WPINC') ) die; ?>

<div class="slw-stock-loations mt-4">


<?php
	global $wc_slw_pro, $slw_widgets_arr;
	$slw_map_images = $slw_widgets_arr['slw-map']['screenshot'];
	$slw_archives_images = $slw_widgets_arr['slw-archives']['screenshot'];
	// show locations table
	$terms = slw_get_locations('location', array(), false);

?>

<div class="alert alert-info" role="alert">
  <?php echo __('If you want the system to update all the products stock for the locations enabled.', 'stock-locations-for-woocommerce'); ?> <?php echo __('This feature is introduced as a beta version for now.', 'stock-locations-for-woocommerce'); ?> <?php echo __('It can possibly replace the cron jobs schduling if it goes successful.', 'stock-locations-for-woocommerce'); ?>
<label class="switch" style="float:right;">
  <input <?php checked(get_option('slw_update_product_locations_stock_values') == true); ?> 
         name="slw-update-product-locations-stock-values" 
         id="slw-update-product-locations-stock-values" 
         value="yes" 
         type="checkbox" 
         data-on="<?php echo __('Enabled', 'stock-locations-for-woocommerce'); ?>" 
         data-off="<?php echo __('Disabled', 'stock-locations-for-woocommerce'); ?>" />
  <span class="slider round"></span>
</label>


  </div>
  
  
	<a class="btn btn-sm btn-success mb-4" href="<?php echo admin_url( 'edit-tags.php?taxonomy=location&post_type=product' ); ?>"><?php _e( 'Add new', 'stock-locations-for-woocommerce' ); ?></a> <a title="<?php _e( 'Video Tutorial', 'stock-locations-for-woocommerce' ); ?>" href="https://www.youtube.com/embed/7ZIv_d7prLA" style="float:right;" target="_blank"><i class="fab fa-youtube"></i></a>
	<table class="widefat w-75 slw_need_popup">
		<thead>
			<tr>
            	<th><?php _e( 'ID', 'stock-locations-for-woocommerce' ); ?></th>
				<th><?php _e( 'Location', 'stock-locations-for-woocommerce' ); ?></th>
                <th><?php _e( 'Enabled', 'stock-locations-for-woocommerce' ); ?></th>
                <th><?php _e( 'Auto Assign to Products', 'stock-locations-for-woocommerce' ); ?></th>
				<th><?php _e( 'Map', 'stock-locations-for-woocommerce' ); ?></th>
                <th><?php _e( 'Priority', 'stock-locations-for-woocommerce' ); ?></th>
                <th></th>
			</tr>
		</thead>
		<tbody>
			<?php
				if( ! empty( $terms ) ) {
					foreach( $terms as $location ) {
						$slw_location_status = get_term_meta($location->term_id, 'slw_location_status', true);
						$slw_map_status = get_term_meta($location->term_id, 'slw_map_status', true);
						$slw_location_assignment = get_term_meta($location->term_id, 'slw_location_assignment', true);
						$slw_location_priority = get_term_meta($location->term_id, 'slw_location_priority', true);
?>
						<tr>							
							<td><?php echo $location->term_id; ?></td>
                            <td><a target="_blank" title="<?php _e( 'View Archive', 'stock-locations-for-woocommerce' ); ?> <?php echo $location->name; ?>" href="<?php echo get_term_link($location->term_id); ?>"><?php echo $location->name; ?></a> <?php if(!$wc_slw_pro){ ?><?php foreach($slw_archives_images as $image){?> &nbsp; <a data-type="screenshot" title="<?php _e( 'Premium Feature', 'stock-locations-for-woocommerce' ); ?>" href="<?php echo $image; ?>"><i class="fas fa-image"></i><img style="display:none" src="<?php echo $image; ?>" /></a><?php } ?><?php } ?></td>
                            <td><a class="<?php echo ($slw_location_status?'checked':''); ?> slw-location-status" title="<?php _e( 'Click here to enable/disable', 'stock-locations-for-woocommerce' ); ?>" data-id="<?php echo $location->term_id; ?>"><i class="fas fa-eye"></i><i class="fas fa-eye-slash"></i></a></td>
                            <td><a class="<?php echo ($slw_location_assignment?'checked':''); ?> slw-location-assignment" title="<?php _e( 'Click here to enable/disable', 'stock-locations-for-woocommerce' ); ?>" data-id="<?php echo $location->term_id; ?>"><i class="fas fa-times-circle"></i><i class="fas fa-check-circle"></i></a></td>
                            <td><?php echo $slw_map_status?'<i class="fas fa-map-marked-alt"></i>':'<i class="fas fa-map-marked"></i>'; ?> <?php if(!$wc_slw_pro){ ?><?php foreach($slw_map_images as $image){?> &nbsp; <a data-type="screenshot" title="<?php _e( 'Premium Feature', 'stock-locations-for-woocommerce' ); ?>" href="<?php echo $image; ?>"><i class="fas fa-image"></i><img style="display:none" src="<?php echo $image; ?>" /></a><?php } ?><?php } ?></td>
                            <td><?php echo $slw_location_priority; ?></td>
                             <td><a title="<?php _e( 'Edit', 'stock-locations-for-woocommerce' ); ?> <?php echo $location->name; ?>" href="<?php echo admin_url('term.php?taxonomy=location&tag_ID='.$location->term_id.'&post_type=product'); ?>"><i class="fas fa-edit"></i></a> &nbsp; <a title="<?php _e( 'List', 'stock-locations-for-woocommerce' ); ?> <?php echo $location->name; ?>" href="<?php echo admin_url('edit.php?location='.$location->name.'&post_type=product'); ?>"><i class="fas fa-list-alt"></i></a></td>
                            
						</tr>
<?php
					}
				} else {
?>
					<tr><td colspan="2"><?php _e( 'No locations found in your store.', 'stock-locations-for-woocommerce' ); ?></td></tr>
<?php
				}
			?>
		</tbody>
		<tfoot>
			<tr>
            	<th><?php _e( 'ID', 'stock-locations-for-woocommerce' ); ?></th>
				<th><?php _e( 'Location', 'stock-locations-for-woocommerce' ); ?></th>
                <th><?php _e( 'Enabled', 'stock-locations-for-woocommerce' ); ?></th>
                <th><?php _e( 'Auto Assign to Products', 'stock-locations-for-woocommerce' ); ?></th>
				<th><?php _e( 'Map', 'stock-locations-for-woocommerce' ); ?></th>
                <th><?php _e( 'Priority', 'stock-locations-for-woocommerce' ); ?></th>
                <th></th>
			</tr>
		</tfoot>        
	</table>  

</div>