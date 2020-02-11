<?php
/**
 * SLW Main Class
 *
 * @since 1.0.0
 */

namespace App;

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
        use Traits\SlwProductTaxonomy;
        use Traits\SlwStockLocationsTab;
        use Traits\SlwBarcodesTab;
        use Traits\SlwOrderItem;
        use Traits\SlwShortcodes;
        use Traits\SlwProductListing;

        private $plugin_dir_url;
        protected static $instance = null;

        /**
         * Class Constructor.
         * @since 1.0.0
         */
        public function __construct()
        {
            // Save plugin dir url to property
            $this->plugin_dir_url = plugin_dir_url(__DIR__);

            $this->init();
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
                self::$instance = new SlwMain();
            }

            return self::$instance;

        }

        /**
         * Initiates the hooks.
         *
         * @since 1.0.0
         * @return void
         */
        public function init(): void
        {

            // On plugin activation
            register_activation_hook(__CLASS__, array($this, 'activate'));
            add_action('init', array($this, 'activate'));

            // Action to load textdomain
            add_action( 'init', array($this, 'load_textdomain') );

            // Enqueue scripts and styles
            add_action( 'admin_enqueue_scripts', array($this, 'enqueue') );

            // Prevent WooCommerce from reduce stock
            add_filter( 'woocommerce_can_reduce_order_stock', array($this, 'disable_wc_reduce_stock'), 10, 2 ); // Since WC 3.0.2

            // Actions and filters from 'SlwStockLocationsTab' trait
            add_filter('woocommerce_product_data_tabs', array($this, 'create_custom_stock_locations_tab_wc_product')); // Since WC 3.0.2
            add_action('woocommerce_product_data_panels', array($this, 'tab_content_stock_locations_wc_product')); // Since WC 3.0.2
            add_action('save_post', array($this, 'save_tab_data_stock_locations_wc_product_save'), 10, 3);

            // Actions and filters from 'SlwBarcodesTab' trait
            add_filter('woocommerce_product_data_tabs', array($this, 'create_custom_barcodes_tab_wc_product')); // Since WC 3.0.2
            add_action('woocommerce_product_data_panels', array($this, 'tab_content_barcodes_wc_product')); // Since WC 3.0.2
            add_action('save_post', array($this, 'save_tab_data_stock_barcodes_wc_product_save'), 10, 3);

            // Actions and filters from 'SlwOrderItem' trait
            add_action('woocommerce_admin_order_item_headers', array($this, 'add_stock_location_column_wc_order'), 10, 1);  // Since WC 3.0.2
            add_action('woocommerce_admin_order_item_values', array($this, 'add_stock_location_inputs_wc_order'), 10, 3);   // Since WC 3.0.2
            add_action('save_post_shop_order', array($this, 'update_stock_locations_data_wc_order_save'), 10, 3);
            add_filter('woocommerce_hidden_order_itemmeta', array($this, 'hide_stock_locations_itemmeta_wc_order'), 10, 1); // Since WC 3.0.2

            // Actions and filters from 'SlwProductListing' trait
            add_filter('manage_edit-product_columns', array($this, 'remove_product_listing_column'), 10, 1);
            add_action('restrict_manage_posts', array($this, 'filter_by_taxonomy_stock_location') , 10, 2);
            //add_action('manage_posts_custom_column', array($this, 'populate_stock_locations_column') );

            // Display admin notices
            add_action('admin_notices', [new SlwAdminNotice(), 'displayAdminNotice']);

        }

        /**
         * Initiates some methods first on plugin activation.
         *
         * @since 1.0.0
         * @return void
         */
        public function activate(): void
        {
            $this->create_taxonomy(); // Trait function
            $this->shortcodes_init(); // Trait function

            flush_rewrite_rules();
        }

        /**
         * Adds scripts and styles.
         *
         * @since 1.0.0
         * @return void
         */
        public function enqueue(): void
        {
            wp_enqueue_style('admin-style', $this->plugin_dir_url . 'admin/css/style.css', null, '1.0');
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
        public function load_textdomain(): void
        {
            load_plugin_textdomain( 'stock-locations-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
        }

    }

}
