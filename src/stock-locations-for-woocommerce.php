<?php if ( ! defined( 'ABSPATH' ) ){ exit; }else{ clearstatcache(); }
/**
 * Plugin Name:       		Stock Locations for WooCommerce
 * Description:       		This plugin will help you to manage WooCommerce Products stocks through locations.
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
 * WC tested up to: 		5.9
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


global $wc_slw_data, $wc_slw_pro, $wc_slw_premium_copy, $slw_plugin_settings, $slw_gkey, $slw_api_valid_keys, $slw_crons_valid_keys, $slw_widgets_arr, $slw_wc_stock_format, $slw_theme_name, $slw_order_id;

$slw_wc_stock_format = get_option('woocommerce_stock_format');
$slw_gkey = get_option('slw-google-api-key');
$slw_plugin_settings = get_option( 'slw_settings' );
$slw_plugin_settings = is_array($slw_plugin_settings)?$slw_plugin_settings:array();
$wc_slw_data = get_plugin_data(__FILE__);
define( 'SLW_PLUGIN_DIR', dirname( __FILE__ ) );
define( 'SLW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

//$slw_order_id = ((is_admin() && isset($_GET['page']) && $_GET['page']=='wc-orders' && isset($_GET['action']) && $_GET['action']=='edit' && isset($_GET['id']) && is_numeric($_GET['id']))?$_GET['id']:0);


$wp_theme_installed = wp_get_theme();
$slw_theme_name = str_replace(array('-'), '_', esc_html( $wp_theme_installed->get_stylesheet()));


$addons_file = realpath(SLW_PLUGIN_DIR . '/inc/addons.php');
if(file_exists($addons_file)){
	include_once($addons_file);
}

$wc_slw_premium_copy = 'https://shop.androidbubbles.com/product/stock-locations-for-woocommerce/';

$wc_slw_pro_file = realpath(SLW_PLUGIN_DIR . '/pro/functions.php');
$wc_slw_pro = file_exists($wc_slw_pro_file);

$slw_api_valid_keys = array(			
	'id' => array('type'=>'int', 'options'=>'', 'tooltip'=>__('When item is a location, so ID is location_id and when item is product so ID is considered as a product_id.', 'stock-locations-for-woocommerce')),
	'value' => array('type'=>'int', 'options'=>''),
	'action' => array('type'=>'string', 'options'=>'get|set'),
	'item' => array('type'=>'string', 'options'=>'location|product|stock|price', 'tooltip'=>__('When item is other than location and product so product_id or location_id would be required as a parameter, ID parameter will not be adequate.', 'stock-locations-for-woocommerce')),
	'format' => array('type'=>'string', 'options'=>'json|default'),
	'product_id'=>array('type'=>'int', 'options'=>'', 'tooltip'=>__('When item is other than product.', 'stock-locations-for-woocommerce')),
	'location_id'=>array('type'=>'int', 'options'=>'', 'tooltip'=>__('When item is other than location.', 'stock-locations-for-woocommerce')),
);

$slw_crons_valid_keys = array(				
	'action' => array('type'=>'string', 'options'=>'update-stock'),
	'limit' => array('type'=>'int', 'options'=>'Default: 10'),
	'reconsider' => array('type'=>'string', 'options'=>'second|minute|hour|day|month|year|once'),
	'product_id' => array('type'=>'int', 'options'=>'Default: 0 <small>(When you need to update just one product.)</small>'),
);
$slw_widgets_arr = array(
	'slw-map' => array(
		'type' => __('Premium', 'stock-locations-for-woocommerce'),
		'input' => array('name'=>'slw-google-api-key', 'type'=>'text', 'caption'=>__('Please enter Google API key here', 'stock-locations-for-woocommerce')),
		'title' => __('Google Map for Stock Locations', 'stock-locations-for-woocommerce'),
		'description' => __('This widget will detect the user location and zoom to current user latitude longitude by default.', 'stock-locations-for-woocommerce'),
		'shortcode' => array('<strong>Shortcode:</strong><br />[SLW-MAP search-field="yes" locations-list="yes" map="yes" map-width="68%" list-width="400px" diameter-range="100" distance-unit="km" zoom="13" search-field-placeholder="" shop-button-text="Shop This Location" directions-button-text="Directions" shop-location-link="default|shop|previous|store-link"]<br /><br />', '<strong>Hooks:</strong><br />add_action("before_slw_shop_button", function($location_data){ }, 11, 1);', 'add_action("after_slw_shop_button", function($location_data){ }, 11, 1);', 'add_filter("slw-map-location-label", function($label, $name, $location_id){ }, 11, 3);', 'add_filter("slw-map-location-name", function($name, $label, $location_id){ }, 11, 3);', 'add_action("slw-map-before-search-box", function($placeholder){ }, 11, 1);', 'add_action("slw-map-after-search-box", function($placeholder){ }, 11, 1);',),					
		'screenshot' => array(SLW_PLUGIN_URL.'images/slw-map-thumb.png', SLW_PLUGIN_URL.'images/slw-map-popup-thumb.png'),
		
	),
	'slw-archives' => array(
		'type' => __('Premium', 'stock-locations-for-woocommerce'),
		'input' => array('name'=>'slw-archives-status', 'type'=>'toggle', 'caption'=>''),
		'title' => __('Stock Locations Archive', 'stock-locations-for-woocommerce'),
		'description' => __('This widget will display the product items category wise on location specific archives.', 'stock-locations-for-woocommerce'),
		'shortcode' => array('add_action("<strong>slw_archive_items_below_title</strong>", "yourtheme_archive_items_below_title", 11, 3);','add_action("<strong>slw_archive_items_below_qty</strong>", "yourtheme_archive_items_below_qty", 11, 3);', 'add_filter("<strong>slw_archive_product_image</strong>", "yourtheme_archive_product_image_callback", 11, 2);', 'add_action("<strong>slw_archive_before_wrapper</strong>", "yourtheme_archive_before_wrapper_callback", 11, 1);', 'add_action("<strong>slw_archive_after_wrapper</strong>", "yourtheme_archive_after_wrapper_callback", 11, 1);', 'add_action("<strong>slw-archive-wrapper</strong>", "yourtheme_archive_wrapper_classes", 11, 1);','add_action("<strong>slw_archive_inside_wrapper_start</strong>", "yourtheme_archive_inside_wrapper_start_callback", 11, 3);','add_action("<strong>slw_archive_inside_wrapper_end</strong>", "yourtheme_archive_inside_wrapper_end_callback", 11, 3);<br /><br /><strong>Shortcodes:</strong><br />[slw-archive-meta meta_key="location_address"]'),					
		'screenshot' => array(SLW_PLUGIN_URL.'images/slw-archives-thumb.png'),
		
	),
	'slw-location-selection' => array(
		'type' => __('Premium', 'stock-locations-for-woocommerce'),
		'input' => array('name'=>'slw-location-selection', 'type'=>'toggle', 'caption'=>''),
		'title' => __('Stock Locations Selection (Popup)', 'stock-locations-for-woocommerce'),
		'description' => __('This widget will implement a popup with the location names to land on specific location archives.', 'stock-locations-for-woocommerce'),
		'shortcode' => array(
		
				'add_filter("<strong>slw_location_selection_popup_content</strong>", "<strong>yourtheme_location_selection_popup_content_callback</strong>", 10, 2);',
				'add_filter("<strong>slw_location_selection_popup_display</strong>", "<strong>yourtheme_location_selection_popup_display_callback</strong>", 10, 2);'),
		'screenshot' => array(SLW_PLUGIN_URL.'images/slw-location-popup-thumb.png', 'https://ps.w.org/stock-locations-for-woocommerce/assets/screenshot-17.png'),
		
	)
);

if($wc_slw_pro){
	include_once(SLW_PLUGIN_DIR . '/pro/functions.php');
}
require_once(realpath(SLW_PLUGIN_DIR . '/inc/functions.php'));


if(!class_exists('SlwMain')) {

	class SlwMain{
		// versions
		public           $version  = '2.7.3';
		public           $import_export_addon_version = '1.1.1';

		// others
		protected static $instance = null;
		private          $plugin_settings;

		/**
		 * Class Constructor.
		 * @since 1.0.0
		 */
		public function __construct(){
			
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
			$this->plugin_settings = get_option( 'slw_settings', array() );
			$this->plugin_settings = is_array($this->plugin_settings)?$this->plugin_settings:array();
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
			global $current_screen, $post, $slw_gkey, $wc_slw_pro;

					
			wp_enqueue_style( 'slw-admin-styles', SLW_PLUGIN_DIR_URL . 'css/admin-style.css', array(), time() );
			
			wp_enqueue_style( 'slw-common-styles', SLW_PLUGIN_DIR_URL . 'css/common-style.css', array(), time() );			
			wp_register_script( 'slw-admin-scripts', SLW_PLUGIN_DIR_URL . 'js/admin-scripts.js', array( 'jquery', 'jquery-blockui' ), time(), true );
			
			if($wc_slw_pro)
			wp_register_script( 'slw-pro-admin-scripts', SLW_PLUGIN_DIR_URL . 'pro/js/admin-scripts.js', array( 'jquery', 'jquery-blockui' ), time(), true );
			
			$slw_location_statuses = array();
			
			$terms = slw_get_locations('location', array(), false);
			if( ! empty( $terms ) ) {
				foreach( $terms as $location ) {
					$slw_location_status = get_term_meta($location->term_id, 'slw_location_status', true);
					$slw_location_statuses[$location->term_id] = ($slw_location_status=='1'?'yes':'no');
					
				}
			}
			
			
			$data = array(
				'slug'    => SLW_PLUGIN_SLUG,
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'slw_nonce' ),
				'slw_gkey' => $slw_gkey,
				'stock_locations' => false,
				'wc_slw_pro' => $wc_slw_pro,
				'wc_slw_premium_feature' => __('This is a premium feature!', 'stock-locations-for-woocommerce'),
				'wc_slw_stock_reset_msg' => __('This action will restore the stock values to the product. Do you want to proceed?', 'stock-locations-for-woocommerce'),
				'wc_slw_product_id' => (is_object($post)?$post->ID:0),
				'wc_slw_location_status' => $slw_location_statuses,
				'wc_slw_location_disabled_msg' => __('Enable this location from edit location page to save the stock value.', 'stock-locations-for-woocommerce'),
				'slw_import_export_tutorial' => __('CSV Import/Export Stock Locations?', 'stock-locations-for-woocommerce'),
			);
			$data['currency_symbol'] = get_woocommerce_currency_symbol();
			
			
			if(is_object($post) && $post->post_type=='product'){
				
				$terms = wp_get_post_terms( $post->ID, 'location', array('meta_key'=>'slw_location_status', 'meta_value'=>true, 'meta_compare'=>'=') );
				if(!empty($terms)){
					$data['stock_locations'] = true;
				}
			}
			wp_localize_script(
				'slw-admin-scripts',
				'slw_admin_scripts',
				$data
			);
			wp_enqueue_script( 'slw-admin-scripts' );
			
			if($wc_slw_pro){
				wp_localize_script(
					'slw-pro-admin-scripts',
					'slw_admin_scripts',
					$data
				);
				wp_enqueue_script( 'slw-pro-admin-scripts' );			
			}
				
			if(
					(isset($_GET['page']) && $_GET['page']=='slw-settings')
				||
					(isset($_GET['taxonomy']) && $_GET['taxonomy']=='location')
			){
				wp_enqueue_style( 'slw-bootstrap-styles', SLW_PLUGIN_DIR_URL . 'css/bootstrap.min.css', array(), date('m') );
				wp_enqueue_style( 'font-awesome', SLW_PLUGIN_DIR_URL . 'css/fontawesome.min.css', array(), date('Ymdh') );
				
				wp_enqueue_script( 'font-awesome', SLW_PLUGIN_DIR_URL . 'js/fontawesome.min.js', array( 'jquery' ), date('Ymdh') );
				wp_enqueue_script( 'bootstrap', SLW_PLUGIN_DIR_URL . 'js/bootstrap.min.js', array( 'jquery' ), date('m') );			
				
				if($slw_gkey){
					wp_enqueue_script( 'slw-googleapis-scripts', 'https://maps.googleapis.com/maps/api/js?key='.$slw_gkey.'&libraries=places', array(), time() );	
				}
				
				wp_enqueue_style( 'slw-magnific-popup', SLW_PLUGIN_DIR_URL . 'css/magnific-popup.css', array(), time() );
				wp_enqueue_script( 'magnific-popup', SLW_PLUGIN_DIR_URL . 'js/jquery.magnific-popup.min.js', array( 'jquery' ), date('m') );
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
			global $post, $wpdb, $wc_slw_pro, $woocommerce;
			wp_enqueue_style( 'slw-frontend-styles', SLW_PLUGIN_DIR_URL . 'css/frontend-style.css', null, time() );
			wp_enqueue_style( 'slw-common-styles', SLW_PLUGIN_DIR_URL . 'css/common-style.css', array(), time() );
			
			
			$product_id = (is_product()?$post->ID:0);
			
			$term_id = (int)(is_archive()?get_queried_object_id():0);
			$term_id = is_numeric($term_id)?$term_id:0;
			$term_link = ($term_id?get_term_link($term_id):'');
			$term_link = (!is_wp_error($term_link)?$term_link:'');
			
			$data = (is_array($this->plugin_settings)?$this->plugin_settings:array());
			$data['slw_location_selection'] = get_option('slw-location-selection', 'no');
			$data['slw_location_selection_popup'] = (apply_filters('slw_location_selection_popup_display', is_front_page(), is_shop()) && function_exists('slw_location_selection_popup') && (get_option('slw-location-selection', 'no')=='yes')?slw_location_selection_popup():'');
			$data['is_home_page'] = (is_home()?'yes':'no');
			$data['is_front_page'] = (is_front_page()?'yes':'no');
			$data['ajaxurl'] = admin_url( 'admin-ajax.php' );
			$data['cart_url'] = wc_get_cart_url();
			$data['wc_slw_pro'] = $wc_slw_pro;
			$data['is_cart'] = is_cart();
			$data['is_checkout'] = is_checkout();
			$data['is_product'] = is_product();
			$data['product_id'] = $product_id;
			$data['product_type'] = '';
			$data['show_in_product_page'] = (array_key_exists('show_in_product_page', $this->plugin_settings)?$this->plugin_settings['show_in_product_page']:'no');
			$data['stock_locations'] = 0;
			$data['stock_quantity'] = array();
			$data['stock_status'] = array();
			$data['stock_quantity_sum'] = 0;
			$data['out_of_stock'] = __('Out of stock', 'woocommerce');
			$data['in_stock'] = __('In stock', 'woocommerce');
			$data['backorder'] = __('Available on backorder', 'woocommerce');
			$data['max_available_qty_msg'] = __('Maximum available quantity has already been added to the cart.', 'woocommerce');
			$data['currency_symbol'] = get_woocommerce_currency_symbol();
			$data['slw_term_url'] = $term_link;
			$data['slw_term_id'] = $term_id;
			$data['slw_term_add_to_cart_url'] = $data['slw_term_url'].'?stock-location='.$data['slw_term_id'].'&add-to-cart=';
			$data['stock_location_selected'] = ((isset($woocommerce->session) && $woocommerce->session->has_session())?$woocommerce->session->get('stock_location_selected'):0);
			
			$stock_locations = array();
			$stock_locations_obj = slw_get_locations('location', array(), false, $data['product_id']);
			
			if( ! empty( $stock_locations_obj ) ) {
				foreach( $stock_locations_obj as $location ) { //pree($location);
					$location_notice = get_term_meta($location->term_id, 'slw_location_notice', true); //pree($location_notice);
					$location_priority = get_term_meta($location->term_id, 'slw_location_priority', true); //pree($location_priority);
					$stock_locations[$location->term_id] = array('id'=>$location->term_id, 'name'=>$location->name, 'priority'=>$location_priority, 'notice'=>$location_notice);
				}
			}
			
			
			$data['stock_locations_data'] = $stock_locations;
			
			
			
			
			
			$data['slw_allow_geo'] = __('Allow current location', 'stock-locations-for-woocommerce');
			$data['slw_allow_geo_tip'] = __('Allow current location to calculate the distance and sort by nearest', 'stock-locations-for-woocommerce');
			$data['dummy_price'] = wc_format_localized_price(111);
			$data['nonce']   = wp_create_nonce( 'slw_nonce' );
			
			$data['slw_archive_items_halt_msg'] = __('Sorry! You have already added the available stock quantity to your cart.', 'stock-locations-for-woocommerce');
			$data['slw_archive_items_max_msg'] = __('Sorry! You can add only the available stock quantity to your cart.', 'stock-locations-for-woocommerce');
			
			$data['slw_cart_items'] = array();
			if(is_object($woocommerce)){
				$items = (isset($woocommerce->cart) && method_exists($woocommerce->cart, 'get_cart')?$woocommerce->cart->get_cart():array());
				
				if(!empty($items)){
					$slw_cart_items = $data['slw_cart_items'];
					foreach($items as $item => $values) {
						$product_id =  $values['product_id'];
						$variation_id =  $values['variation_id'];
						$stock_location_id = array_key_exists('stock_location', $values)?$values['stock_location']:0;
						$stock_location_id = (is_array($stock_location_id)?$stock_location_id[$product_id]:$stock_location_id);

						$quantity = array_key_exists('quantity', $values)?$values['quantity']:0;
						
						$slw_cart_items[$product_id][$variation_id][$stock_location_id] = $quantity;
					}
					$data['slw_cart_items'] = $slw_cart_items;
					
					
				}
				
			}
			
			


			if($term_id && isset($this->plugin_settings['extra_assets_settings']) && isset($this->plugin_settings['extra_assets_settings']['font_awesome']) && $this->plugin_settings['extra_assets_settings']['font_awesome'] == 'on'){
				wp_enqueue_style( 'font-awesome', SLW_PLUGIN_DIR_URL . 'css/fontawesome.min.css', array(), date('Ymdh') );				
				wp_enqueue_script( 'font-awesome', SLW_PLUGIN_DIR_URL . 'js/fontawesome.min.js', array( 'jquery' ), date('Ymdh') );
			}

			$data['stock_locations_product_page_notice'] = '';

			
			
			if($data['is_product'] && (is_object($post) && $post->post_type=='product')){// && isset($this->plugin_settings['show_in_product_page']) && $this->plugin_settings['show_in_product_page'] == 'yes' ) {
				
				
				
				$everything_stock_status_to_instock = array_key_exists('everything_stock_status_to_instock', $this->plugin_settings);
				if($everything_stock_status_to_instock && function_exists('everything_stock_status_to_instock')){
					everything_stock_status_to_instock($data['product_id']);
				}
				
				
				$meta_obj = $wpdb->get_row('SELECT COUNT(*) AS total_locations FROM '.$wpdb->prefix.'postmeta pm WHERE pm.post_id="'.esc_sql($data['product_id']).'" AND pm.meta_key LIKE "_stock_at_%" AND pm.meta_value>0');
				$wc_product = wc_get_product($data['product_id']);
				
				$terms = slw_get_locations('location', array(), true, $data['product_id']);
				//pree($terms);
				
				if(!empty($meta_obj)){
					if($meta_obj->total_locations>0){
						$data['stock_locations'] = $meta_obj->total_locations;
					}
				}
				$product_price = trim(str_replace(get_woocommerce_currency_symbol(), '', strip_tags(wc_price($wc_product->get_price()))));
				$data['product_type'] = $wc_product->get_type();

				$data['product_price_format'] = trim(str_replace(array(get_woocommerce_currency_symbol(), $product_price), array('_CURRENCY_SYMBOL', '_PRODUCT_PRICE'), strip_tags(wc_price($wc_product->get_price()))));
				$data['product_price_raw'] = $product_price; 
				$data['product_price'] = $wc_product->get_price(); 
				$data['stock_status'][$data['product_id']] = $wc_product->get_availability();
				$data['allow_backorder'][$data['product_id']] = get_post_meta($data['product_id'], '_backorders', true);
				$data['product_price_decimals'] = apply_filters('slw_product_price_decimals', 2, $product_price);
				
				
				
				if($data['product_type']=='variable' && $data['product_id']>0){
				
					$product_variations_ids = $wpdb->get_results("SELECT ID AS variation_id FROM $wpdb->posts WHERE post_parent IN (".$data['product_id'].") AND post_type='product_variation'");
					//$product_variations_ids = $wc_product->get_children();
					//pree($product_variations_ids);
					$product_variations = array();
					
					//pre($product_variations_ids);	
					//pre($terms);
					$locations = array();
					
					foreach( $product_variations_ids as $variation_obj ) {
						$variation_id = $variation_obj->variation_id;
						if(!empty($terms)){
							$data['stock_quantity'][$variation_id][0] = 0;
							foreach($terms as $term){		
								$wc_variation = wc_get_product($variation_id);
								
								$data['stock_status'][$variation_id] = $wc_variation->get_availability();
								$data['allow_backorder'][$variation_id] = get_post_meta($variation_id, '_backorders', true);
								$data['stock_quantity'][$data['product_id']][$term->term_id] = get_post_meta($data['product_id'], '_stock_at_'.$term->term_id, true);			
								$data['stock_quantity'][$variation_id][$term->term_id] = get_post_meta($variation_id, '_stock_at_'.$term->term_id, true);
								
								$data['stock_quantity_sum'] += ((float)$data['stock_quantity'][$variation_id][$term->term_id])*1;
								
							}
							$data['stock_quantity'][$variation_id][0] = $data['stock_quantity_sum'];
						}
					}
					
				}else{
					if(!empty($terms)){
						$data['stock_quantity'][$data['product_id']][0] = 0;
						foreach($terms as $term){					
							$data['stock_quantity'][$data['product_id']][$term->term_id] = get_post_meta($data['product_id'], '_stock_at_'.$term->term_id, true);
							$data['stock_quantity_sum'] += ((float)$data['stock_quantity'][$data['product_id']][$term->term_id])*1;
						}
						$data['stock_quantity'][$data['product_id']][0] = $data['stock_quantity_sum'];
					}					
				}
				
				//pree($data);
				
				$data['stock_locations_product_page_notice'] = apply_filters('slw_product_stock_location_notice', sprintf(__('%s available at %s', 'stock-locations-for-woocommerce'), 'STOCK_QTY', 'LOCATION_NAME'), $data['product_id'], $terms, $data['stock_quantity']);
				
				wp_enqueue_script( 'slw-frontend-product-underscore', SLW_PLUGIN_DIR_URL . 'js/underscore-min.js', array( 'jquery' ), time(), true );
				wp_enqueue_script( 'slw-frontend-product-scripts', SLW_PLUGIN_DIR_URL . 'js/product.js', array( 'jquery-blockui' ), time(), true );
				wp_localize_script(
					'slw-frontend-product-scripts',
					'slw_frontend',
					$data
				);
			}
			
			
			
			
			
		
		
			wp_enqueue_script(
				'slw-common-scripts',
				SLW_PLUGIN_DIR_URL . 'js/common.js',
				array('jquery'),
				time(),
				true
			);
			wp_localize_script(
				'slw-common-scripts',
				'slw_frontend',
				$data
			);
			
			if( is_archive() ) {
				if(get_option('slw-archives-status')=='yes'){
					wp_enqueue_script(
						'slw-archive-scripts',
						SLW_PLUGIN_DIR_URL . 'js/archive.js',
						array('jquery-blockui'),
						time(),
						true
					);	
				}
			}
			
			if( isset($this->plugin_settings['show_in_cart']) && $this->plugin_settings['show_in_cart'] == 'yes' ) {
				wp_enqueue_script(
					'slw-frontend-cart-scripts',
					SLW_PLUGIN_DIR_URL . 'js/cart.js',
					array('jquery-blockui'),
					time(),
					true
				);	

				
			}else{
				
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
function initiate_slw_plugin(){

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