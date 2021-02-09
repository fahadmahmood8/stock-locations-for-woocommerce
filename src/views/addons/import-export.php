<div class="plugin-card">
	<div class="plugin-card-top">
		<div class="name column-name">
			<h3>
				<?php esc_attr_e( 'Import/Export add-on', 'stock-locations-for-woocommerce' ); ?>
				<img class="plugin-icon" src="<?php echo Slw()->pluginDirUrl(); ?>/assets/img/import-export-add-on.svg" alt="Import/Export add-on">
			</h3>
		</div>
		<div class="action-links">
			<ul class="plugin-action-buttons">
				<?php if( ! class_exists( 'Slw_Import_Export_Add_On_Class' ) ) : ?>
					<li><a class="button button-primary" href="https://www.paypal.com/donate?hosted_button_id=B63VVEXUP6FB8" target="_blank"><?= __( 'Buy now', 'stock-locations-for-woocommerce' ); ?></a></li>
					<li style="text-align:center;"><strong>€29</strong></li>
				<?php else : ?>
					<li><a class="button button-disabled"><?= __( 'Installed', 'stock-locations-for-woocommerce' ); ?></a></li>
				<?php endif; ?>
			</ul>
		</div>
		<div class="desc column-description">
			<p>
				<?php esc_attr_e(
					'Import and export stock from locations keeping also your WooCommerce stock up-to-date.',
					'stock-locations-for-woocommerce'
				); ?>
			</p>
			<p class="authors">
				<cite><?php esc_attr_e( 'By Alexandre Faustino', 'stock-locations-for-woocommerce' ); ?></cite>
			</p>
		</div>
	</div>
	<div class="plugin-card-bottom">
		<div>
			<?php _e( 'Last version', 'stock-locations-for-woocommerce' ); ?>: <?php echo Slw()->import_export_addon_version; ?>
		</div>
		<div>
			<?php
				if( class_exists( 'Slw_Import_Export_Add_On_Class') && is_callable( 'Slw_Import_Export_Add_On_Class', 'version' ) ) {
					if( Slw_Import_Export()->version < Slw()->import_export_addon_version ) {
						$style = 'color:red;';
					} else {
						$style = 'color:green;';
					}
					echo '<span style="'.$style.'">'.__( 'Installed version', 'stock-locations-for-woocommerce' ) .': '.Slw_Import_Export()->version.'</span>';
				}
			?>
		</div>
	</div>
</div>