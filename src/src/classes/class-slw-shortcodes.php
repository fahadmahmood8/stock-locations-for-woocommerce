<?php
/**
 * SLW Shortcodes Class
 *
 * @since 1.0.0
 */

namespace SLW\SRC\Classes;

/**
 * If this file is called directly, abort.
 *
 * @since 1.0.0
 */
if ( !defined( 'WPINC' ) ) {
    die;
}

if(!class_exists('SlwShortcodes')) {

    class SlwShortcodes
    {
		protected $barcodes;

		/**
         * Construct.
         *
         * @since 1.1.0
         */
		public function __construct()
		{
			$this->shortcodes_init();
		}

        /**
         * Displays the barcodes.
         *
         * @since 1.0.0
         * @return string
         */
        public function display_barcode($atts)
        {
            $values = shortcode_atts(array(
                'type' => '' // Default value
            ), $atts);

            if(!$values) {
                return;
            }

            // Get the plugin barcodes
            $barcodes = SlwBarcodesTab::get_barcodes();

            // Save barcodes names into array
            $barcode_names = [];
            foreach($barcodes as $barcode) {
                $barcode_names[] = $barcode['name'];
            }

            // Based on input determine what to return
            $output = '';

            // If input 'type' is a valid barcode name
            if(in_array($values['type'], $barcode_names)) {
                // Get post meta
                $postmeta = get_post_meta( get_the_ID(), '_' . $values['type'] , true );

                // If post meta exists
                if($postmeta) {
                    $output = '<p class="' . SLW_PLUGIN_SLUG . '_barcode">' . $postmeta . '</p>';
                } else {
                    return;
                }
            } else {
                return __('Barcode type not found!', 'stock-locations-for-woocommerce');
            }

            return $output;

        }

        /**
         * Initiates the shortcodes.
         *
         * @since 1.0.0
         * @return void
         */
        public function shortcodes_init()
        {
            add_shortcode('slw_barcode', array($this, 'display_barcode'));
        }
    }

}
