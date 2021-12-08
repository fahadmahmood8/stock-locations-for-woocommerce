<?php if ( ! defined( 'ABSPATH' ) ){ exit; }else{ clearstatcache(); }
/**
 * Plugin Name:       		Stock Locations for WooCommerce
 * Description:       		This plugin will help you manage WooCommerce Products stocks throw locations.
 * Version:					__STABLE_TAG__
 * Requires at least: 		4.9
 * Requires PHP:      		7.2
 * Author:            		Fahad Mahmood & Alexandre Faustino
 * Author URI:        		https://profiles.wordpress.org/fahadmahmood/#content-plugins
 * License:           		GPL v2 or later
 * License URI:       		https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       		stock-locations-for-woocommerce
 * Domain Path:       		/languages
 * WC requires at least:	3.4
 * WC tested up to: 		5.3
 */

/**
 * If this file is called directly, abort.
 *
 * @since 1.0.0
 */
if ( !defined( 'WPINC' ) ) {
	die;
}
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');


global $wc_slw_data, $wc_slw_pro, $wc_slw_premium_copy;
$wc_slw_data = get_plugin_data(__FILE__);
define( 'SLW_PLUGIN_DIR', dirname( __FILE__ ) );
$wc_slw_premium_copy = 'https://shop.androidbubbles.com/product/stock-locations-for-woocommerce/';

$wc_slw_pro = file_exists(realpath(SLW_PLUGIN_DIR . '/pro/functions.php'));
require_once(realpath(SLW_PLUGIN_DIR . '/inc/functions.php'));
if($wc_slw_pro){
	include_once(realpath(SLW_PLUGIN_DIR . '/pro/functions.php'));
}


if(!class_exists('SlwMain')) {

	class SlwMain
	{
		// versions
		public           $version  = '1.6.1';
		public           $import_export_addon_version = '1.1.1';

		// others
		protected static $instance = null;
		private          $plugin_settings;

		/**
		 * Class Constructor.
		 * @since 1.0.0
		 */
		public function __construct()
		{
			define( 'SLW_PLUGIN_VERSION', $this->version );

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
			
					
			wp_enqueue_style( 'slw-admin-styles', SLW_PLUGIN_DIR_URL . 'css/admin-style.css', array(), time() );
			wp_enqueue_style( 'slw-common-styles', SLW_PLUGIN_DIR_URL . 'css/common-style.css', array(), time() );			
			wp_register_script( 'slw-admin-scripts', SLW_PLUGIN_DIR_URL . 'js/admin-scripts.js', array( 'jquery', 'jquery-blockui' ), SLW_PLUGIN_VERSION, true );
			
			wp_localize_script(
				'slw-admin-scripts',
				'slw_admin_scripts',
				array(
					'slug'    => SLW_PLUGIN_SLUG,
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'slw_nonce' ),
				)
			);
			wp_enqueue_script( 'slw-admin-scripts' );
				
			if(isset($_GET['page']) && $_GET['page']=='slw-settings'){
				wp_enqueue_style( 'slw-bootstrap-styles', SLW_PLUGIN_DIR_URL . 'css/bootstrap.min.css', array(), date('m') );
				wp_enqueue_style( 'font-awesome', SLW_PLUGIN_DIR_URL . 'css/fontawesome.min.css', array(), date('m') );
				
				wp_enqueue_script( 'font-awesome', SLW_PLUGIN_DIR_URL . '/js/fontawesome.min.js', array( 'jquery' ), date('m') );
				wp_enqueue_script( 'bootstrap', SLW_PLUGIN_DIR_URL . '/js/bootstrap.min.js', array( 'jquery' ), date('m') );				
			}
		}

		/**
		 * Adds scripts and styles for Frontend.
		 *
		 * @since 1.2.0
		 * @return void
		 */
		public function enqueue_frontend()
		{
			wp_enqueue_style( 'slw-frontend-styles', SLW_PLUGIN_DIR_URL . 'css/frontend-style.css', null, time() );
			wp_enqueue_style( 'slw-common-styles', SLW_PLUGIN_DIR_URL . 'css/common-style.css', array(), time() );
			
			
			$data = $this->plugin_settings;
			$data['ajaxurl'] = admin_url( 'admin-ajax.php' );
			$data['is_cart'] = is_cart();
			$data['is_checkout'] = is_checkout();
			$data['is_product'] = is_product();
			
			
			if( isset($this->plugin_settings['show_in_cart']) && $this->plugin_settings['show_in_cart'] == 'yes' ) {
				wp_enqueue_script(
					'slw-frontend-cart-scripts',
					SLW_PLUGIN_DIR_URL . 'js/cart.js',
					array('jquery-blockui'),
					time(),
					true
				);	

				wp_localize_script(
					'slw-frontend-cart-scripts',
					'slw_frontend',
					$data
				);
			}
			if( isset($this->plugin_settings['show_in_product_page']) && $this->plugin_settings['show_in_product_page'] == 'yes' ) {
				wp_register_script( 'slw-frontend-product-scripts', SLW_PLUGIN_DIR_URL . 'js/product.js', array( 'jquery-blockui' ), time(), true );
				wp_localize_script(
					'slw-frontend-product-scripts',
					'slw_frontend',
					$data
				);
				wp_enqueue_script( 'slw-frontend-product-scripts' );
			}
		}

	}

}

/**
 * Initiate the plugin.
 *
 * @since 1.0.0
 */
add_action( 'plugins_loaded', 'initiate_slw_plugin' );
function initiate_slw_plugin()
{

	// check if WooCommerce is active
	if ( ! class_exists( 'woocommerce' ) ) {

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		// deactivate the plugin
		deactivate_plugins( plugin_basename( __FILE__ ) );

		// show error
		echo '<div class="error"><p>' . __('Stock Locations for WooCommerce requires WooCommerce to be activaded. Please active WooCommerce plugin first.', 'stock-locations-for-woocommerce') . '</p></div>';

	} else {

		// require autoload
		require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

		// define constants
		define( 'SLW_PLUGIN_SLUG', dirname( plugin_basename( __FILE__ ) ) );
		define( 'SLW_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
		define( 'SLW_PLUGIN_DIR_URL_ABSOLUTE_PATH', realpath( plugin_dir_path( __FILE__ ) ) );
		define( 'SLW_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
		define( 'SLW_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

		// intantiate
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