<?php if ( ! defined('WPINC') ) die; 
global $slw_optional_features_key;

$option_key = $slw_optional_features_key;

$features = [
    'stock_status_column' => [
        'title'       => __('Stock Status Column', 'stock-locations-for-woocommerce'),
        'description' => __('Products list page in the admin to see actual stock status and product visibility together.', 'stock-locations-for-woocommerce'),
    ],

    'fix_stock_status_visibility_mismatch' => [
        'title'       => __('Auto Fix Visibility', 'stock-locations-for-woocommerce'),
        'description' => __('Automatically repair out of stock products with missing visibility taxonomy.', 'stock-locations-for-woocommerce'),
    ],
];

$saved_features = get_option($option_key, []);

$saved_features = is_array($saved_features)
    ? $saved_features
    : [];

?>

<div class="slw-settings-wrap">

    <table class="widefat striped slw-settings-table" cellspacing="0">

        <thead>
            <tr>
                <th>
                    <?php _e('Feature', 'stock-locations-for-woocommerce'); ?>
                </th>

                <th width="120">
                    <?php _e('Status', 'stock-locations-for-woocommerce'); ?>
                </th>
            </tr>
        </thead>

        <tbody>

            <?php foreach ($features as $feature_key => $feature) : ?>

                <?php

                $enabled = (
                    isset($saved_features[$feature_key])
                    && $saved_features[$feature_key]
                );

                ?>

                <tr>

                    <td>

                        <strong>
                            <?php echo esc_html($feature['title']); ?>
                        </strong>

                        <p style="margin:5px 0 0; opacity:.7;">
                            <?php echo esc_html($feature['description']); ?>
                        </p>

                    </td>

                    <td>

                        <label class="slw-optional-features-btn">

                            <input
                                type="checkbox"
                               data-feature-key="<?php echo esc_attr($feature_key); ?>"
                                value="1"
                                <?php checked($enabled); ?>
                            >

                            <span>
                                <i class="fas fa-toggle-on"></i>
                            </span>

                        </label>

                    </td>

                </tr>

            <?php endforeach; ?>

        </tbody>

    </table>

</div>