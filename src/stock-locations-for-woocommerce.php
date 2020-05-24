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
 * WC tested up to: 		4.0.1
 */

/**
 * If this file is called directly, abort.
 *
 * @since 1.0.0
 */
if ( !defined( 'WPINC' ) ) {
    die;
}

if(!class_exists('SlwMain')) {

    class SlwMain
    {
        private $plugin_dir_url;
		protected static $instance = null;

        /**
         * Class Constructor.
         * @since 1.0.0
         */
        public function __construct()
        {
            // Save plugin dir url to property
            $this->plugin_dir_url = plugin_dir_url(__FILE__);

			$this->init();

			// Instantiate classes
			new SLW\SRC\Classes\SlwProductTaxonomy;
			new SLW\SRC\Classes\SlwStockLocationsTab;
			new SLW\SRC\Classes\SlwBarcodesTab;
			new SLW\SRC\Classes\SlwOrderItem;
			new SLW\SRC\Classes\SlwShortcodes;
			new SLW\SRC\Classes\SlwProductListing;
            new SLW\SRC\Classes\SlwProductRest;
        }

        /**
         * Ensures only one instance of our plugin is loaded or can be loaded.
         *
         * @since 1.0.0
         * @return object
         */
        public static function instance()
        {

            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }

            return self::$instance;

        }

        /**
         * Initiates the hooks.
         *
         * @since 1.0.0
         * @return void
         */
        public function init()
        {
            // Action to load textdomain
            add_action( 'init', array($this, 'load_textdomain') );

            // Enqueue scripts and styles
            add_action( 'admin_enqueue_scripts', array($this, 'enqueue') );

            // Prevent WooCommerce from reduce stock
            add_filter( 'woocommerce_can_reduce_order_stock', '__return_false', 999 ); // Since WC 3.0.2

            // Display admin notices
			add_action( 'admin_notices', [new SLW\SRC\Classes\SlwAdminNotice(), 'displayAdminNotice'] );
		}

        /**
         * Adds scripts and styles.
         *
         * @since 1.0.0
         * @return void
         */
        public function enqueue()
        {
            wp_enqueue_style('admin-style', $this->plugin_dir_url . 'admin/css/style.css', null, '1.1');
            wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css', null, '5.11.2');

            // Register the script
            wp_register_script( 'scripts', $this->plugin_dir_url . 'admin/js/scripts.js', null, '1.0', true );
            // Localize the script passing the plugin slug constant
            $params = array(
                'slug' => SLW_PLUGIN_SLUG
            );
            wp_localize_script( 'scripts', 'slw_plugin_slug', $params );
            // Enqueued script with localized data.
            wp_enqueue_script( 'scripts' );
        }

        /**
         * Load plugin textdomain.
         *
         * @since 1.0.0
         * @return void
         */
        public function load_textdomain()
        {
            load_plugin_textdomain( 'stock-locations-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
        }

    }

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
function initiate_slw_plugin()
{

	// Check if WooCommerce is active
	if ( ! class_exists( 'woocommerce' ) ) {

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		// Deactivate the plugin
		deactivate_plugins( plugin_basename( __FILE__ ) );

		// Show error
		echo '<div class="error"><p>' . __('Stock Locations for WooCommerce requires WooCommerce to be activaded. Please active WooCommerce plugin first.', 'stock-locations-for-woocommerce') . '</p></div>';

	} else {

		// Require autoload
		require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

		// Intantiate
		SlwMain::instance();

	}


}
