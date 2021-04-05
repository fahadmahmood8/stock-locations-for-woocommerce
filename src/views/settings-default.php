<?php if ( ! defined('WPINC') ) die; ?>
<form method="post" action="options.php">
<?php
	settings_fields( 'slw_setting_option_group' );
	do_settings_sections( 'slw-setting-admin' );
	submit_button();
?>
</form>

<?php
	// show locations table
	$terms = get_terms( 'location', array(
		'hide_empty' => false,
	) );

	?>
	<table style="width:20%;">
		<tr>
			<td>
				<h3><?php _e( 'Existing stock locations', 'stock-locations-for-woocommerce' ); ?></h3>
			</td>
			<td>
				<a class="button" style="float:right;" href="<?= admin_url( 'edit-tags.php?taxonomy=location&post_type=product' ); ?>"><?php _e( 'Add new', 'stock-locations-for-woocommerce' ); ?></a>
			</td>
		</tr>
	</table>
	<table class="widefat" style="width:20%;">
		<thead>
			<tr>
				<th><?php _e( 'Location', 'stock-locations-for-woocommerce' ); ?></th>
				<th><?php _e( 'ID', 'stock-locations-for-woocommerce' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
				if( ! empty( $terms ) ) {
					foreach( $terms as $location ) {
						?>
						<tr>
							<td><?= $location->name; ?></td>
							<td><?= $location->term_id; ?></td>
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
	</table>
	<?php