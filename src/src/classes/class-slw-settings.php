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
			add_action( 'admin_menu', array($this, 'create_admin_menu_page'), 99 );
			add_action( 'admin_init', array($this, 'register_settings') );
			add_action( 'slw_after_settings_tabs', array( $this, 'import_export_addon_notice' ), 10, 1 );

			add_filter( 'plugin_action_links_'.\SlwMain::$plugin_basename, array($this, 'settings_link') );

			$this->plugin_settings = get_option( 'slw_settings' );
		}

		/**
		 * Create Admin Menu Page.
		 *
		 * @since 1.2.0
		 * @return void
		 */
		public function create_admin_menu_page()
		{
			// This page will be under "WooCommerce"
			add_submenu_page(
				'woocommerce',
				__('SLW Settings', 'stock-locations-for-woocommerce'), 
				__('SLW Settings', 'stock-locations-for-woocommerce'), 
				'manage_woocommerce',
				'slw-settings', 
				array( $this, 'admin_menu_page_callback' )
			);
		}

		public function settings_tabs()
		{
			return apply_filters( 'slw_settings_tabs', array(
				'default'	=> __( 'Settings', 'stock-locations-for-woocommerce' ),
				'sponsor'	=> __( 'Sponsor', 'stock-locations-for-woocommerce' ),
			) );
		}
		
		/**
		 * Admin Menu Page Callback.
		 *
		 * @since 1.2.0
		 * @return void
		 */
		public function admin_menu_page_callback()
		{
			?>
			<div class="wrap">
				<h1><?php _e('Stock Locations for WooCommerce Settings', 'stock-locations-for-woocommerce'); ?></h1>
				<h2 class="nav-tab-wrapper">
					<?php isset( $_REQUEST['tab'] ) ?: $_REQUEST['tab'] = 'default'; ?>
					<?php foreach( $this->settings_tabs() as $key => $label ) : $class = 'nav-tab'; ?>
					<?php if( isset($_REQUEST['tab']) && $_REQUEST['tab'] == $key ) { $class .= ' nav-tab-active'; } ?>
					<a href="<?= admin_url( 'admin.php?page=slw-settings' ); ?>&tab=<?= $key; ?>" class="<?= $class; ?>"><?= $label; ?></a>
					<?php endforeach; ?>
				</h2>
				<?php do_action( 'slw_after_settings_tabs', $_REQUEST['tab'] ); ?>
				<?php
					// echo view
					if( isset( $_REQUEST['tab'] ) ) {
						echo \SLW\SRC\Helpers\view( 'settings-'.$_REQUEST['tab'] );
					}
				?>
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
				null,
				array( $this, 'setting_section_info' ),
				'slw-setting-admin'
			);
	
			add_settings_field(
				'show_in_cart',
				__('Show location selection in cart', 'stock-locations-for-woocommerce'),
				array( $this, 'show_in_cart_callback' ),
				'slw-setting-admin',
				'slw_setting_setting_section'
			);

			add_settings_field(
				'cart_location_selection_required',
				null,
				array( $this, 'cart_location_selection_required_callback' ),
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
				'show_in_product_page',
				__('Show location selection in product page', 'stock-locations-for-woocommerce'),
				array( $this, 'show_in_product_page_callback' ),
				'slw-setting-admin',
				'slw_setting_setting_section'
			);

			add_settings_field(
				'product_location_selection_show_stock_qty',
				__('Show stock quantities in location selection in frontend', 'stock-locations-for-woocommerce'),
				array( $this, 'product_location_selection_show_stock_qty_callback' ),
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
				'location_email_notifications',
				__('Enable location email notifications', 'stock-locations-for-woocommerce'),
				array( $this, 'location_email_notifications_callback' ),
				'slw-setting-admin',
				'slw_setting_setting_section'
			);

			add_settings_field(
				'wc_new_order_location_copy',
				__('Send copy of WC New Order email to location address', 'stock-locations-for-woocommerce'),
				array( $this, 'wc_new_order_location_copy_callback' ),
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
			if ( isset( $input['cart_location_selection_required'] ) ) {
				$sanitary_values['cart_location_selection_required'] = $input['cart_location_selection_required'];
			}
			if ( isset( $input['different_location_per_cart_item'] ) ) {
				$sanitary_values['different_location_per_cart_item'] = $input['different_location_per_cart_item'];
			}
			if ( isset( $input['show_in_product_page'] ) ) {
				$sanitary_values['show_in_product_page'] = $input['show_in_product_page'];
			}
			if ( isset( $input['product_location_selection_show_stock_qty'] ) ) {
				$sanitary_values['product_location_selection_show_stock_qty'] = $input['product_location_selection_show_stock_qty'];
			}
			if ( isset( $input['delete_unused_product_locations_meta'] ) ) {
				$sanitary_values['delete_unused_product_locations_meta'] = $input['delete_unused_product_locations_meta'];
			}
			if ( isset( $input['include_location_data_in_formatted_item_meta'] ) ) {
				$sanitary_values['include_location_data_in_formatted_item_meta'] = $input['include_location_data_in_formatted_item_meta'];
			}
			if ( isset( $input['location_email_notifications'] ) ) {
				$sanitary_values['location_email_notifications'] = $input['location_email_notifications'];
			}
			if ( isset( $input['wc_new_order_location_copy'] ) ) {
				$sanitary_values['wc_new_order_location_copy'] = $input['wc_new_order_location_copy'];
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
		 * Make cart location selection required.
		 *
		 * @since 1.3.0
		 * @return void
		 */
		public function cart_location_selection_required_callback()
		{
			$this->checkbox_callback('cart_location_selection_required');
			?>
			<span><?= __('Make location selection in cart required.', 'stock-locations-for-woocommerce'); ?></span>
			<?php
		}

		/**
		 * Make cart location selection required.
		 *
		 * @since 1.3.0
		 * @return void
		 */
		public function product_location_selection_show_stock_qty_callback()
		{
			$this->checkbox_callback('product_location_selection_show_stock_qty');
			?>
			<span><?= __('It will affect location selectors on product and cart pages.', 'stock-locations-for-woocommerce'); ?></span>
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
		 * Different location per cart item dropdown callback.
		 *
		 * @since 1.3.0
		 * @return void
		 */
		public function show_in_product_page_callback()
		{
			$this->select_yes_no_callback('show_in_product_page');
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
		 * Location email notifications callback.
		 *
		 * @since 1.3.0
		 * @return void
		 */
		public function location_email_notifications_callback()
		{
			$this->checkbox_callback('location_email_notifications');
			?>
			<p><?= __('Auto order allocation must be enabled in the location.', 'stock-locations-for-woocommerce'); ?></p>
			<?php
		}

		/**
		 * Send copy of WC New Order email to location address callback.
		 *
		 * @since 1.3.0
		 * @return void
		 */
		public function wc_new_order_location_copy_callback()
		{
			$this->checkbox_callback('wc_new_order_location_copy');
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
				<?php $selected = isset($this->plugin_settings[$id]) ?: 'selected'; ?>
				<option disabled <?= $selected; ?>><?= __('Select...', 'stock-locations-for-woocommerce'); ?></option>
				<?php $selected = isset( $this->plugin_settings[$id] ) && $this->plugin_settings[$id] === 'yes' ? 'selected' : ''; ?>
				<option value="yes" <?= $selected; ?>><?= __('Yes', 'stock-locations-for-woocommerce'); ?></option>
				<?php $selected = isset( $this->plugin_settings[$id] ) && $this->plugin_settings[$id] === 'no' ? 'selected' : ''; ?>
				<option value="no" <?= $selected; ?>><?= __('No', 'stock-locations-for-woocommerce'); ?></option>
			</select>
			<?php
		}

		/**
		 * Checkbox callback.
		 *
		 * @since 1.2.1
		 * @return void
		 */
		public function checkbox_callback( $id )
		{
			?> 
			<input name="slw_settings[<?= $id; ?>]" id="<?= $id; ?>" type="checkbox" <?= isset($this->plugin_settings[$id]) && in_array($this->plugin_settings[$id], array('yes', 'on')) ? 'value="on"' : null; ?> <?= isset($this->plugin_settings[$id]) && in_array($this->plugin_settings[$id], array('yes', 'on')) ? 'checked="checked"' : null; ?>>
			<?php
		}

		/**
		 * Adds plugin settings link.
		 *
		 * @since 1.2.0
		 * @return void
		 */
		public function settings_link( $links ) {
			$settings_link = '<a href="' . admin_url( 'admin.php?page=slw-settings' ) . '">'. __( 'Settings', 'stock-locations-for-woocommerce' ) . '</a>';
			array_push( $links, $settings_link );
			return $links;
		}

		/**
		 * Show Import/Export add-on notice
		 *
		 * @since 1.4.4
		 * @return void
		 */
		public function import_export_addon_notice( $active_tab ) {
			if( $active_tab != 'sponsor' && ! class_exists( 'Slw_Import_Export_Add_On_Class' ) ) :
			?>
			<div class="notice notice-info inline">
				<p style="height:80px;">
					<img class="slw-import-export-addon-logo" src="<?php echo Slw()->pluginDirUrl(); ?>/assets/img/import-export-add-on.svg" alt="Import/Export add-on">
					<span style="display:block; margin-top:10px; margin-bottom:10px;">
					<?php
						printf(
							esc_attr__( 'New stock locations %s!', 'stock-locations-for-woocommerce' ),
							'<strong>'.esc_attr__( 'Import/Export add-on', 'stock-locations-for-woocommerce' ).'</strong>'
						);
					?>
					</span>
					<a class="button button-primary" href="<?php echo admin_url( 'admin.php?page=slw-settings&tab=sponsor' ); ?>"><?php esc_attr_e( 'Grab it here', 'stock-locations-for-woocommerce' ); ?></a>
				</p>
			</div>
			<?php
			endif;
		}

	}

}
