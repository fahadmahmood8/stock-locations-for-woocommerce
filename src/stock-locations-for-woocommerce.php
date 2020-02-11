<?php
/**
 * Plugin Name:       		Stock Locations for WooCommerce
 * Description:       		This plugin will help you manage WooCommerce Products stocks throw locations and also different traditional barcodes.
 * Version:					__STABLE_TAG__
 * Requires at least: 		4.9
 * Requires PHP:      		7.0
 * Author:            		Alexandre Faustino
 * Author URI:        		mailto:alexmigf@gmail.com
 * License:           		GPL v2 or later
 * License URI:       		https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       		stock-locations-for-woocommerce
 * Domain Path:       		/languages
 * WC requires at least:	3.4.0
 * WC tested up to: 		3.9.0
 */

/**
 * If this file is called directly, abort.
 *
 * @since 1.0.0
 */
if ( !defined( 'WPINC' ) ) {
    die;
}

/**
 * Define SLW_PLUGIN_SLUG.
 *
 * @since 1.0.0
 */
if ( !defined( 'SLW_PLUGIN_SLUG' ) ) {
    define( 'SLW_PLUGIN_SLUG', dirname( plugin_basename( __FILE__ ) ) );
}

/**
 * Initiate the plugin.
 *
 * @since 1.0.0
 */
add_action( 'plugins_loaded', 'initiate_slw_plugin' );
function initiate_SLW_plugin()
{

    // Allow only if the user has the correct capabilities
    if( current_user_can( 'activate_plugins' ) ) {

        // Check if WooCommerce is active
        if ( ! class_exists( 'woocommerce' ) ) {

            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

            // Deactivate the plugin
            deactivate_plugins( plugin_basename( __FILE__ ) );

            // Show error
            echo '<div class="error"><p>' . __('Stock Locations for WooCommerce requires WooCommerce to be activaded. Please active WooCommerce plugin first.', 'stock-locations-for-woocommerce') . '</p></div>';

            flush_rewrite_rules();

        } else {

            // Require autoload
            require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

            // Intantiate
            App\SlwMain::instance();

        }

    }

}
