<?php
/**
 * SLW Barcodes Tab Class
 *
 * @since 1.0.0
 */

namespace SLW\SRC\Classes;

if ( !defined( 'WPINC' ) ) {
	die;
}

if( !class_exists('SlwBarcodesTab') ) {

	class SlwBarcodesTab
	{
		private $tab_barcodes = 'slw_tab_barcodes';

		/**
		 * Construct.
		 *
		 * @since 1.1.0
		 */
		public function __construct()
		{
			// get settings
			$plugin_settings = get_option( 'slw_settings' );
			
			if( ! isset($plugin_settings['display_barcodes_tab']) ) {
				add_filter('woocommerce_product_data_tabs', array($this, 'create_custom_barcodes_tab_wc_product'), 10, 1); // Since WC 3.0.2
				add_action('woocommerce_product_data_panels', array($this, 'tab_content_barcodes_wc_product'), 10, 1); // Since WC 3.0.2
				add_action('woocommerce_process_product_meta', array($this, 'save_tab_data_stock_barcodes_wc_product_save'), 10, 2);
			}
		}

		/**
		 * Creates the custom Barcodes tab in WC Product.
		 *
		 * @since 1.0.0
		 * @return array
		 */
		public function create_custom_barcodes_tab_wc_product( $original_tabs )
		{
			// Define custom tab
			$new_tab[$this->tab_barcodes] = array(
				'label' 	=> __( 'Barcodes', 'stock-locations-for-woocommerce' ),
				'target'    => $this->tab_barcodes,
				'class'     => array( 'show_if_simple', 'show_if_variable' ),
			);

			// Define tab positions
			$insert_at_position = 5;
			$tabs = array_slice( $original_tabs, 0, $insert_at_position, true );
			$tabs = array_merge( $tabs, $new_tab );
			$tabs = array_merge( $tabs, array_slice( $original_tabs, $insert_at_position, null, true ) );

			return $tabs;
		}

		/**
		 * Add data to the Barcodes tab in WC Product.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function tab_content_barcodes_wc_product( $array )
		{
			// Replace the default WooCommerce icon for the this plugin tab
			echo '<style>#woocommerce-product-data ul.wc-tabs li.' . $this->tab_barcodes . '_options a:before { font-family: Font Awesome\ 5 Free; content: \'\f02a\'; font-weight: 900; }</style>';

			// Get the product ID
			$product_id = get_the_ID();

			// Populate the tab content
			echo '<div id="' . $this->tab_barcodes . '" class="panel woocommerce_options_panel">';

			// Define each barcode
			$barcodes = $this::get_barcodes();

			// Iterate over the barcodes
			foreach($barcodes as $barcode) {

				// Define $args;
				$args = null;

				$postmeta_barcode = get_post_meta($product_id, '_' . $barcode['name'], true);

				// Check if the postmeta has any value, if yes add it to the 'value' of the input
				if($postmeta_barcode) {
					$args = $postmeta_barcode;
				}

				// Create the input
				woocommerce_wp_text_input( array(
					'id'                => $this->tab_barcodes . '_' . $barcode['name'],
					'label'             => strtoupper($barcode['name']),
					'description'       => $barcode['description'],
					'value'             => $args,
					'desc_tip'          => true,
					'class'             => 'woocommerce',
					'type'              => $barcode['type'],
					'custom_attributes' => $barcode['specs'],
				) );

			}

			echo '</div>';

		}

		/**
		 * Saves data from custom Barcodes tab upon WC Product save.
		 *
		 * @since 1.0.0
		 * @return int|void
		 */
		public function save_tab_data_stock_barcodes_wc_product_save( $post_id, $post )
		{
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
				return $post_id;

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
				return $post_id;

			if ( ! current_user_can( 'edit_product', $post_id ) )
				return $post_id;


			if( !empty($post) ) {
				// Get barcodes
				$barcodes = $this::get_barcodes();

				foreach($barcodes as $barcode) {

					// Check if input has some value in it
					if( isset($_POST[$this->tab_barcodes . '_' . $barcode['name']]) ) {

						// Update post meta
						update_post_meta( $post_id, '_' . $barcode['name'] , sanitize_text_field($_POST[$this->tab_barcodes . '_' . $barcode['name']]) );

					}

				}
			}
		}

		/**
		 * Define and return Barcodes.
		 *
		 * @since 1.0.0
		 * @return array
		 */
		public static function get_barcodes()
		{
			$barcodes = array(
				array(
					'name'          =>  'isbn',
					'type'          =>  'text',
					'description'   =>  __('The International Standard Book Number (ISBN) is a unique commercial book identifier barcode.', 'stock-locations-for-woocommerce'),
					'specs'         =>  array(
						'maxlength' =>  '13',
						'pattern'   =>  '^[\d-]+$'
					)
				),
				array(
					'name'          =>  'upc',
					'type'          =>  'number',
					'description'   =>  __('Universal Product Code (UPC) is a 12-digit bar code used extensively for retail packaging in United States.', 'stock-locations-for-woocommerce'),
					'specs'         =>  array(
						'max'       =>  '999999999999',
						'maxlength' =>  '12',
						'pattern'   =>  '^[\d]+$'
					)
				),
				array(
					'name'          =>  'ean',
					'type'          =>  'number',
					'description'   =>  __('The European Article Number (EAN) is a barcode standard, a 12- or 13-digit product identification code.', 'stock-locations-for-woocommerce'),
					'specs'         =>  array(
						'max'       =>  '9999999999999',
						'maxlength' =>  '13',
						'pattern'   =>  '^[\d]+$'
					)
				),
				array(
					'name'          =>  'asin',
					'type'          =>  'text',
					'description'   =>  __('Amazon Standard Identification Numbers (ASINs) are unique blocks of 10 letters and/or numbers that identify items.', 'stock-locations-for-woocommerce'),
					'specs'         =>  array(
						'maxlength' =>  '10',
						'pattern'   =>  '^[A-Z0-9]+$'
					)
				)
			);

			return $barcodes;

		}

	}

}
