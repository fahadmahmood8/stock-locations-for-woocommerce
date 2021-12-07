<?php if ( ! defined('WPINC') ) die; ?>

<div class="slw-stock-loations mt-4">


<?php
	// show locations table
	$terms = get_terms( 'location', array(
		'hide_empty' => false,
	) );

	?>
	<a class="btn btn-sm btn-success mb-4" href="<?= admin_url( 'edit-tags.php?taxonomy=location&post_type=product' ); ?>"><?php _e( 'Add new', 'stock-locations-for-woocommerce' ); ?></a>
	<table class="widefat w-50">
		<thead>
			<tr>
            	<th><?php _e( 'ID', 'stock-locations-for-woocommerce' ); ?></th>
				<th><?php _e( 'Location', 'stock-locations-for-woocommerce' ); ?></th>
				
			</tr>
		</thead>
		<tbody>
			<?php
				if( ! empty( $terms ) ) {
					foreach( $terms as $location ) {
						?>
						<tr>							
							<td><?= $location->term_id; ?></td>
                            <td><?= $location->name; ?></td>
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

</div>