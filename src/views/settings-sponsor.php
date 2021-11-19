<?php if ( ! defined('WPINC') ) die; ?>

<!-- Donate -->

<h3><?php _e( 'Donate', 'stock-locations-for-woocommerce' ); ?> ❤️</h3>
<p><?= __( 'Would like to sponsor the development of new features and keep me motivated?', 'stock-locations-for-woocommerce' ); ?><a class="button button-primary" style="margin-left:10px;" href="https://www.paypal.com/donate?hosted_button_id=XP8W2VK666WFW" target="_blank"><?= __( 'Donate', 'stock-locations-for-woocommerce' ); ?></a></p>

<!-- Add-ons -->

<h3><?php _e( 'Available add-ons', 'stock-locations-for-woocommerce' ); ?></h3>
<div class="wp-list-table widefat">
	<p><?php _e( 'Add-ons are sent by email to you after buying process completed. This is a manual procedure, so please be patient!', 'stock-locations-for-woocommerce' ); ?></p>
	<div id="the-list">
		<!-- Import/Export -->
		<?php include_once( SLW_PLUGIN_DIR_URL_ABSOLUTE_PATH.'/views/addons/import-export.php' ); ?>
	</div>
</div>

<!-- Support -->

<div class="tablenav bottom">
	<p><?php _e( 'Having problems with add-ons or need updated versions?', 'stock-locations-for-woocommerce' ); ?> <a href="https://wordpress.org/support/plugin/stock-locations-for-woocommerce/" target="_blank"><?php _e( 'Create a Ticket', 'stock-locations-for-woocommerce' ); ?></a></p>
</div>