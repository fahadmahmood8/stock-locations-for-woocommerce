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
			// Actions
            add_action( 'admin_menu', array($this, 'create_admin_menu_page') );
            add_action( 'admin_init', array($this, 'register_settings') );
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
				array( $this, 'cart_dropdown_options_callback' ),
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

			// sanitize show stock locations in cart dropdown option
			if ( isset( $input['show_in_cart'] ) ) {
				$sanitary_values['show_in_cart'] = $input['show_in_cart'];
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
		public function cart_dropdown_options_callback()
		{
			?> 
			<select name="slw_settings[show_in_cart]" id="show_in_cart">
				<?php $selected = isset($this->plugin_settings['show_in_cart']) ?: 'selected'; ?>
				<option disabled <?= $selected; ?>><?= __('Select...', 'stock-locations-for-woocommerce'); ?></option>
				<?php $selected = isset( $this->plugin_settings['show_in_cart'] ) && $this->plugin_settings['show_in_cart'] === 'yes' ? 'selected' : ''; ?>
				<option value="yes" <?= $selected; ?>><?= __('Yes', 'stock-locations-for-woocommerce'); ?></option>
				<?php $selected = isset( $this->plugin_settings['show_in_cart'] ) && $this->plugin_settings['show_in_cart'] === 'no' ? 'selected' : ''; ?>
				<option value="no" <?= $selected; ?>><?= __('No', 'stock-locations-for-woocommerce'); ?></option>
			</select>
			<?php
		}

	}

}
