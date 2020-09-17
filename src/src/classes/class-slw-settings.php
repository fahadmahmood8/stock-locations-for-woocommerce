<?php
/**
 * SLW Settings Class
 *
 * @since 1.2.0
 */

namespace SLW\SRC\Classes;

if ( !defined( 'WPINC' ) ) {
	die;
}

if(!class_exists('SlwSettings')) {

	class SlwSettings
	{
		private $plugin_settings;
		
		/**
		 * Construct.
		 *
		 * @since 1.2.0
		 */
		public function __construct()
		{
            add_action( 'admin_menu', array($this, 'create_admin_menu_page') );
			add_action( 'admin_init', array($this, 'register_settings') );
			add_filter( 'plugin_action_links_'.\SlwMain::$plugin_basename, array($this, 'settings_link') );
		}

		/**
         * Create Admin Menu Page.
         *
         * @since 1.2.0
         * @return void
         */
        public function create_admin_menu_page()
        {
            // This page will be under "Settings"
			add_options_page(
				__('SLW Settings', 'stock-locations-for-woocommerce'), 
				__('SLW Settings', 'stock-locations-for-woocommerce'), 
				'manage_options', 
				'slw-settings', 
				array( $this, 'admin_menu_page_callback' )
			);
		}
		
		/**
         * Admin Menu Page Callback.
         *
         * @since 1.2.0
         * @return void
         */
        public function admin_menu_page_callback()
        {
			$this->plugin_settings = get_option( 'slw_settings' );

			?>
			<div class="wrap">
				<h1><?php _e('Stock Locations for WooCommerce Settings', 'stock-locations-for-woocommerce'); ?></h1>
				<form method="post" action="options.php">
				<?php
					settings_fields( 'slw_setting_option_group' );
					do_settings_sections( 'slw-setting-admin' );
					submit_button();
				?>
				</form>
			</div>
			<?php
		}
		
		/**
         * Register Settings.
         *
         * @since 1.2.0
         * @return void
         */
        public function register_settings()
        {
            register_setting(
				'slw_setting_option_group',
				'slw_settings',
				array( $this, 'option_setting_sanitize' )
			);
	
			add_settings_section(
				'slw_setting_setting_section',
				__('Frontend settings', 'stock-locations-for-woocommerce'),
				array( $this, 'setting_section_info' ),
				'slw-setting-admin'
			);
	
			add_settings_field(
				'show_in_cart',
				__('Show stock locations in cart', 'stock-locations-for-woocommerce'),
				array( $this, 'show_in_cart_callback' ),
				'slw-setting-admin',
				'slw_setting_setting_section'
			);

			add_settings_field(
				'different_location_per_cart_item',
				__('Different location per cart item', 'stock-locations-for-woocommerce'),
				array( $this, 'different_location_per_cart_item_callback' ),
				'slw-setting-admin',
				'slw_setting_setting_section'
			);

			add_settings_field(
				'delete_unused_product_locations_meta',
				__('Auto delete unused product locations meta', 'stock-locations-for-woocommerce'),
				array( $this, 'delete_unused_product_locations_meta_callback' ),
				'slw-setting-admin',
				'slw_setting_setting_section'
			);

			add_settings_field(
				'include_location_data_in_formatted_item_meta',
				__('Include location data in formatted item meta', 'stock-locations-for-woocommerce'),
				array( $this, 'include_location_data_in_formatted_item_meta_callback' ),
				'slw-setting-admin',
				'slw_setting_setting_section'
			);

			add_settings_field(
				'display_barcodes_tab',
				__('Disable barcodes tab', 'stock-locations-for-woocommerce'),
				array( $this, 'display_barcodes_tab_callback' ),
				'slw-setting-admin',
				'slw_setting_setting_section'
			);
		}
		
		/**
         * Sanitize setting option value.
         *
         * @since 1.2.0
         * @return string
         */
		public function option_setting_sanitize( $input )
		{
			$sanitary_values = array();

			// sanitize option
			if ( isset( $input['show_in_cart'] ) ) {
				$sanitary_values['show_in_cart'] = $input['show_in_cart'];
			}
			if ( isset( $input['different_location_per_cart_item'] ) ) {
				$sanitary_values['different_location_per_cart_item'] = $input['different_location_per_cart_item'];
			}
			if ( isset( $input['delete_unused_product_locations_meta'] ) ) {
				$sanitary_values['delete_unused_product_locations_meta'] = $input['delete_unused_product_locations_meta'];
			}
			if ( isset( $input['include_location_data_in_formatted_item_meta'] ) ) {
				$sanitary_values['include_location_data_in_formatted_item_meta'] = $input['include_location_data_in_formatted_item_meta'];
			}
			if ( isset( $input['display_barcodes_tab'] ) ) {
				$sanitary_values['display_barcodes_tab'] = $input['display_barcodes_tab'];
			}
	
			return $sanitary_values;
		}

		/**
         * Setting section info.
         *
         * @since 1.2.0
         * @return void
         */
		public function setting_section_info() {}

		/**
         * Show in cart dropdown callback.
         *
         * @since 1.2.0
         * @return void
         */
		public function show_in_cart_callback()
		{
			$this->select_yes_no_callback('show_in_cart');
			?>
			<p>&#9888; <?= __('If auto order allocation is enabled for the selected location in the cart, this setting will be ignored for stock reduction.', 'stock-locations-for-woocommerce'); ?></p>
			<?php
		}

		/**
         * Different location per cart item dropdown callback.
         *
         * @since 1.2.1
         * @return void
         */
		public function different_location_per_cart_item_callback()
		{
			$this->select_yes_no_callback('different_location_per_cart_item');
		}

		/**
         * Delete unused product locations meta dropdown callback.
         *
         * @since 1.2.3
         * @return void
         */
		public function delete_unused_product_locations_meta_callback()
		{
			$this->select_yes_no_callback('delete_unused_product_locations_meta');
			?>
			<p><?= __('Runs every day at midnight.', 'stock-locations-for-woocommerce'); ?></p>
			<?php
		}

		/**
         * Delete unused product locations meta dropdown callback.
         *
         * @since 1.2.4
         * @return void
         */
		public function include_location_data_in_formatted_item_meta_callback()
		{
			$this->select_yes_no_callback('include_location_data_in_formatted_item_meta');
			?>
			<p><?= __('This special meta can be used by third party plugins to show the location name and quantity subtracted.', 'stock-locations-for-woocommerce'); ?></p>
			<?php
		}

		/**
         * Disable barcodes tab callback.
         *
         * @since 1.2.1
         * @return void
         */
		public function display_barcodes_tab_callback()
		{
			$this->select_yes_no_callback('display_barcodes_tab');
		}

		/**
         * Select yes/no callback.
         *
         * @since 1.2.1
         * @return void
         */
		public function select_yes_no_callback( $id )
		{
			?> 
			<select name="slw_settings[<?= $id; ?>]" id="<?= $id; ?>">
				<?php if( $id != 'display_barcodes_tab' ) : ?>
					<?php $selected = isset($this->plugin_settings[$id]) ?: 'selected'; ?>
					<option disabled <?= $selected; ?>><?= __('Select...', 'stock-locations-for-woocommerce'); ?></option>
				<?php endif; ?>
				<?php $selected = isset( $this->plugin_settings[$id] ) && $this->plugin_settings[$id] === 'yes' ? 'selected' : ''; ?>
				<option value="yes" <?= $selected; ?>><?= __('Yes', 'stock-locations-for-woocommerce'); ?></option>
				<?php $selected = isset( $this->plugin_settings[$id] ) && $this->plugin_settings[$id] === 'no' ? 'selected' : ''; ?>
				<?php $selected = !isset( $this->plugin_settings[$id] ) && $id == 'display_barcodes_tab' ? 'selected' : ''; ?>
				<option value="no" <?= $selected; ?>><?= __('No', 'stock-locations-for-woocommerce'); ?></option>
			</select>
			<?php
		}

		/**
		 * Adds plugin settings link.
		 *
		 * @since 1.2.0
		 * @return void
		 */
		public function settings_link( $links ) {
			$settings_link = '<a href="' . admin_url( 'options-general.php?page=slw-settings' ) . '">'. __( 'Settings', 'woocommerce' ) . '</a>';
			array_push( $links, $settings_link );
			return $links;
		}

	}

}
