<?php
/**
 * SLW Shortcodes Trait
 *
 * @since 1.0.0
 */

namespace App\Traits;

/**
 * If this file is called directly, abort.
 *
 * @since 1.0.0
 */
if ( !defined( 'WPINC' ) ) {
    die;
}

use App\Traits\SlwBarcodes;

if(!trait_exists('SlwShortcodes')) {

    trait SlwShortcodes
    {
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
            $barcodes = SlwBarcodes::barcodes();

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
        public function shortcodes_init(): void
        {
            add_shortcode('slw_barcode', array($this, 'display_barcode'));
        }
    }

}