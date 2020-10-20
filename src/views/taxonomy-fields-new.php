<div class="form-field term-slug-wrap">
	<label for="tag-slug"><?php echo __('Default for new products', 'stock-locations-for-woocommerce'); ?></label>
	<select name="default_location">
		<option value="0" <?php echo ($default_location == 0) ? 'selected="selected"' : ''; ?>>No</option>
		<option value="1" <?php echo ($default_location == 1) ? 'selected="selected"' : ''; ?>>Yes</option>
	</select>
	<p><?php echo __('Should location be selected by default for new products?', 'stock-locations-for-woocommerce'); ?></p>
</div>

<div class="form-field term-slug-wrap">
	<label for="tag-slug"><?php echo __('Backorder location', 'stock-locations-for-woocommerce'); ?></label>
	<select name="primary_location">
		<option value="0" <?php echo ($primary_location == 0) ? 'selected="selected"' : ''; ?>>No</option>
		<option value="1" <?php echo ($primary_location == 1) ? 'selected="selected"' : ''; ?>>Yes</option>
	</select>
	<p><?php echo __('Should backorder stock be allocated to this location? Only used if auto order allocate is enabled. Please ensure only one backorder location is set.', 'stock-locations-for-woocommerce'); ?></p>
</div>

<div class="form-field term-slug-wrap">
	<label for="tag-slug"><?php echo __('Auto order allocate', 'stock-locations-for-woocommerce'); ?></label>
	<select name="auto_order_allocate">
		<option value="0" <?php echo ($auto_order_allocate == 0) ? 'selected="selected"' : ''; ?>>No</option>
		<option value="1" <?php echo ($auto_order_allocate == 1) ? 'selected="selected"' : ''; ?>>Yes</option>
	</select>
	<p><?php echo __('Should stock be auto allocated to stock locations when an order is placed?<br>See priority field below to set priority.', 'stock-locations-for-woocommerce'); ?></p>
</div>

<div class="form-field term-slug-wrap">
	<label for="tag-slug"><?php echo __('Location priority', 'stock-locations-for-woocommerce'); ?></label>
	<input name="auto_order_allocate_priority" type="number" value="<?php echo $auto_order_allocate_priority; ?>" size="40">
	<p><?php echo __('This is the order in which stock is auto allocated if enabled.', 'stock-locations-for-woocommerce'); ?></p>
</div>

<?php if( isset($location_email) && !is_null($location_email) ) : ?>
	<div class="form-field term-slug-wrap">
		<label for="tag-slug"><?php echo __('Location email', 'stock-locations-for-woocommerce'); ?></label>
		<input name="location_email" type="email" value="<?php echo $location_email; ?>" size="40">
		<p><?php echo __('Email address for notifications when a customer buys from this location.<br>Works only if auto order allocation is enabled for this location.', 'stock-locations-for-woocommerce'); ?></p>
	</div>
<?php endif; ?>