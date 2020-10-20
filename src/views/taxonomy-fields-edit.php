<table class="form-table" role="presentation">
	<tbody>
		<tr class="form-field form-required term-name-wrap">
			<th scope="row"><label><?php echo __('Default for new products', 'stock-locations-for-woocommerce'); ?></label></th>
			<td>
				<select name="default_location">
					<option value="0" <?php echo ($default_location == 0) ? 'selected="selected"' : ''; ?>>No</option>
					<option value="1" <?php echo ($default_location == 1) ? 'selected="selected"' : ''; ?>>Yes</option>
				</select>
				<p class="description"><?php echo __('Should location be selected by default for new products?', 'stock-locations-for-woocommerce'); ?></p>
			</td>
		</tr>
		<tr class="form-field form-required term-name-wrap">
			<th scope="row"><label><?php echo __('Backorder location', 'stock-locations-for-woocommerce'); ?></label></th>
			<td>
				<select name="primary_location">
					<option value="0" <?php echo ($primary_location == 0) ? 'selected="selected"' : ''; ?>>No</option>
					<option value="1" <?php echo ($primary_location == 1) ? 'selected="selected"' : ''; ?>>Yes</option>
				</select>
				<p class="description"><?php echo __('Should backorder stock be allocated to this location? Only used if auto order allocate is enabled. Please ensure only one backorder location is set.', 'stock-locations-for-woocommerce'); ?></p>
			</td>
		</tr>
		<tr class="form-field form-required term-name-wrap">
			<th scope="row"><label><?php echo __('Auto order allocate', 'stock-locations-for-woocommerce'); ?></label></th>
			<td>
				<select name="auto_order_allocate">
					<option value="0" <?php echo ($auto_order_allocate == 0) ? 'selected="selected"' : ''; ?>>No</option>
					<option value="1" <?php echo ($auto_order_allocate == 1) ? 'selected="selected"' : ''; ?>>Yes</option>
				</select>
				<p class="description"><?php echo __('Should stock be auto allocated to stock locations when an order is placed? <br />See priority field below to set priority.', 'stock-locations-for-woocommerce'); ?></p>
			</td>
		</tr>
		<tr class="form-field form-required term-name-wrap">
			<th scope="row"><label><?php echo __('Location priority', 'stock-locations-for-woocommerce'); ?></label></th>
			<td>
				<input name="auto_order_allocate_priority" type="number" value="<?php echo $auto_order_allocate_priority; ?>" size="40">
				<p class="description"><?php echo __('This is the order in which stock is auto allocated if enabled', 'stock-locations-for-woocommerce'); ?></p>
			</td>
		</tr>
		<?php if( isset($location_email) && !is_null($location_email) ) : ?>
		<tr class="form-field term-name-wrap">
			<th scope="row"><label><?php echo __('Location email', 'stock-locations-for-woocommerce'); ?></label></th>
			<td>
				<input type="email" name="location_email" value="<?php echo $location_email; ?>">
				<p class="description"><?php echo __('Email address for notifications when a customer buys from this location. Works only if auto order allocation is enabled for this location.', 'stock-locations-for-woocommerce'); ?></p>
			</td>
		</tr>
		<?php endif; ?>
	</tbody>
</table>