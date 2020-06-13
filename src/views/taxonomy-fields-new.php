<div class="form-field term-slug-wrap">
    <label for="tag-slug"><?php echo __('Default for New Products', 'stock-locations-for-woocommerce'); ?></label>
    <select name="default_location">
        <option value="0" <?php echo ($default_location == 0) ? 'selected="selected"' : ''; ?>>No</option>
        <option value="1" <?php echo ($default_location == 1) ? 'selected="selected"' : ''; ?>>Yes</option>
    </select>
    <p><?php echo __('Should location be selected by default for new products?', 'stock-locations-for-woocommerce'); ?></p>
</div>

<div class="form-field term-slug-wrap">
    <label for="tag-slug"><?php echo __('Backorder Location', 'stock-locations-for-woocommerce'); ?></label>
    <select name="primary_location">
        <option value="0" <?php echo ($primary_location == 0) ? 'selected="selected"' : ''; ?>>No</option>
        <option value="1" <?php echo ($primary_location == 1) ? 'selected="selected"' : ''; ?>>Yes</option>
    </select>
    <p><?php echo __('Should backorder stock be allocated to this location? Only used if auto order allocate is enabled. Please ensure only one backorder location is set.', 'stock-locations-for-woocommerce'); ?></p>
</div>

<div class="form-field term-slug-wrap">
    <label for="tag-slug"><?php echo __('Auto Order Allocate', 'stock-locations-for-woocommerce'); ?></label>
    <select name="auto_order_allocate">
        <option value="0" <?php echo ($auto_order_allocate == 0) ? 'selected="selected"' : ''; ?>>No</option>
        <option value="1" <?php echo ($auto_order_allocate == 1) ? 'selected="selected"' : ''; ?>>Yes</option>
    </select>
    <p><?php echo __('Should stock be auto allocated to stock locations when an order is placed? <br />Sse priority field below to set priority.', 'stock-locations-for-woocommerce'); ?></p>
</div>

<div class="form-field term-slug-wrap">
    <label for="tag-slug"><?php echo __('Stock Location Priority', 'stock-locations-for-woocommerce'); ?></label>
    <input name="auto_order_allocate_priority" type="number" value="<?php echo $auto_order_allocate_priority; ?>" size="40">
    <p><?php echo __('This is the order in which stock is auto allocated if enabled', 'stock-locations-for-woocommerce'); ?></p>
</div>