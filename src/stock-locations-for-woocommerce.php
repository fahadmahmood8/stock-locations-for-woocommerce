<?php
/**
 * Plugin Name:       		Stock Locations for WooCommerce
 * Description:       		This plugin will help you manage WooCommerce Products stocks throw locations.
 * Version:					__STABLE_TAG__
 * Requires at least: 		4.9
 * Requires PHP:      		7.2
 * Author:            		Alexandre Faustino
 * Author URI:        		mailto:alexmigf@gmail.com
 * License:           		GPL v2 or later
 * License URI:       		https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       		stock-locations-for-woocommerce
 * Domain Path:       		/languages
 * WC requires at least:	3.4.0
 * WC tested up to: 		4.6.0
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
		private $plugin_dir;
		public static $plugin_basename;
		protected static $instance = null;
		private $plugin_settings;

		/**
		 * Class Constructor.
		 * @since 1.0.0
		 */
		public function __construct()
		{
			// Save plugin dir url to property
			$this->plugin_dir_url = plugin_dir_url(__FILE__);
			$this->plugin_dir = realpath(plugin_dir_path(__FILE__));
			self::$plugin_basename = plugin_basename(__FILE__);

			$this->init();

			// Instantiate classes
			new SLW\SRC\Classes\SlwLocationTaxonomy;
			new SLW\SRC\Classes\SlwStockLocationsTab;
			new SLW\SRC\Classes\SlwOrderItem;
			new SLW\SRC\Classes\SlwShortcodes;
			new SLW\SRC\Classes\SlwProductListing;
			new SLW\SRC\Classes\SlwProductRest;
			new SLW\SRC\Classes\SlwSettings;
			// Frontend
			new SLW\SRC\Classes\Frontend\SlwFrontendCart;
			new SLW\SRC\Classes\Frontend\SlwFrontendProduct;

			// get settings
			$this->plugin_settings = get_option( 'slw_settings' );
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
			// Enqueue scripts and styles
			add_action( 'admin_enqueue_scripts', array($this, 'enqueue_admin') );
			add_action( 'wp_enqueue_scripts', array($this, 'enqueue_frontend') );

			// Prevent WooCommerce from reduce stock
			add_filter( 'woocommerce_can_reduce_order_stock', '__return_false', 999 ); // Since WC 3.0.2

			// Display admin notices
			add_action( 'admin_notices', [new SLW\SRC\Classes\SlwAdminNotice(), 'displayAdminNotice'] );

			// Fix for Point of Sale for WooCommerce (https://woocommerce.com/products/point-of-sale-for-woocommerce/)
			if( class_exists('WC_POS') ) {
				remove_filter( 'woocommerce_stock_amount', 'floatval', 99 );
				add_filter( 'woocommerce_stock_amount', 'intval' );
			}
		}
		
		/**
		 * Adds scripts and styles for Admin.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function enqueue_admin()
		{
			wp_enqueue_style('styles-admin', $this->pluginDirUrl() . 'assets//css/admin/style.css', null, '1.3');
			wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css', null, '5.11.2');

			wp_register_script( 'scripts-admin', $this->pluginDirUrl() . 'assets/js/admin/scripts.js', null, '1.2', true );
			wp_localize_script( 'scripts-admin', 'slw_plugin_slug', array( 'slug' => SLW_PLUGIN_SLUG ) );
			wp_enqueue_script( 'scripts-admin' );
		}

		/**
		 * Adds scripts and styles for Frontend.
		 *
		 * @since 1.2.0
		 * @return void
		 */
		public function enqueue_frontend()
		{
			wp_enqueue_style('styles-frontend', $this->pluginDirUrl() . 'assets/css/frontend/style.css', null, '1.3.3');
			
			if( isset($this->plugin_settings['show_in_cart']) && $this->plugin_settings['show_in_cart'] == 'yes' ) {
				wp_register_script( 'scripts-frontend-cart', $this->pluginDirUrl() . 'assets/js/frontend/cart.js', array( 'jquery-blockui' ), '1.3', true );
				wp_localize_script( 'scripts-frontend-cart', 'slw_frontend_cart', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
				wp_enqueue_script( 'scripts-frontend-cart' );
			}
			if( isset($this->plugin_settings['show_in_product_page']) && $this->plugin_settings['show_in_product_page'] == 'yes' ) {
				wp_register_script( 'scripts-frontend-product', $this->pluginDirUrl() . 'assets/js/frontend/product.js', array( 'jquery-blockui' ), '1.3', true );
				wp_localize_script( 'scripts-frontend-product', 'slw_frontend_product', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
				wp_enqueue_script( 'scripts-frontend-product' );
			}
		}

		/**
		 * Plugin Url directory
		 *
		 * @return mixed
		 */
		public function pluginDirUrl()
		{
			return $this->plugin_dir_url;
		}

		/**
		 * Plugin directory
		 *
		 * @return mixed
		 */
		public function pluginDir()
		{
			return $this->plugin_dir;
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

/**
 * Return SlwMain instance
 *
 * @return object|SlwMain
 */
function Slw()
{
	return SlwMain::instance();
}

