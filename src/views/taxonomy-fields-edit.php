<table class="form-table" role="presentation">
    <tbody>
        <tr class="form-field form-required term-name-wrap">
            <th scope="row"><label><?php echo __('Default for New Products', 'stock-locations-for-woocommerce'); ?></label></th>
            <td>
                <select name="default_location">
                    <option value="0" <?php echo ($default_location == 0) ? 'selected="selected"' : ''; ?>>No</option>
                    <option value="1" <?php echo ($default_location == 1) ? 'selected="selected"' : ''; ?>>Yes</option>
                </select>
                <p class="description"><?php echo __('Should location be selected by default for new products?', 'stock-locations-for-woocommerce'); ?></p>
            </td>
        </tr>
        <tr class="form-field form-required term-name-wrap">
            <th scope="row"><label><?php echo __('Primary Location', 'stock-locations-for-woocommerce'); ?></label></th>
            <td>
                <select name="primary_location">
                    <option value="0" <?php echo ($primary_location == 0) ? 'selected="selected"' : ''; ?>>No</option>
                    <option value="1" <?php echo ($primary_location == 1) ? 'selected="selected"' : ''; ?>>Yes</option>
                </select>
                <p class="description"><?php echo __('Only one primary location can be set, any over stock will be taken from this location.', 'stock-locations-for-woocommerce'); ?></p>
            </td>
        </tr>
        <tr class="form-field form-required term-name-wrap">
            <th scope="row"><label><?php echo __('Auto Order Allocate', 'stock-locations-for-woocommerce'); ?></label></th>
            <td>
                <select name="auto_order_allocate">
                    <option value="0" <?php echo ($auto_order_allocate == 0) ? 'selected="selected"' : ''; ?>>No</option>
                    <option value="1" <?php echo ($auto_order_allocate == 1) ? 'selected="selected"' : ''; ?>>Yes</option>
                </select>
                <p class="description"><?php echo __('Should stock be auto allocated to stock locations when an order is placed? <br />Sse priority field below to set priority.', 'stock-locations-for-woocommerce'); ?></p>
            </td>
        </tr>
        <tr class="form-field form-required term-name-wrap">
            <th scope="row"><label><?php echo __('Auto Order Allocate Priority', 'stock-locations-for-woocommerce'); ?></label></th>
            <td>
                <input name="auto_order_allocate_priority" type="number" value="<?php echo $auto_order_allocate_priority; ?>" size="40">
                <p class="description"><?php echo __('When auto order allocate is enabled, each location will subtract available stock and move onto the next location.', 'stock-locations-for-woocommerce'); ?></p>
            </td>
        </tr>
    </tbody>
</table>