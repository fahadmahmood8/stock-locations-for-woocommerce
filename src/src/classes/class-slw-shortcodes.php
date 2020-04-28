<?php
/**
 * SLW Shortcodes Class
 *
 * @since 1.0.0
 */

namespace SLW\SRC\Classes;

use WP_Query;

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
			add_shortcode('slw_barcode', array($this, 'display_barcode'));
            add_shortcode('slw_product_locations', array($this, 'display_product_locations'));
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
         * Displays the product locations.
         *
         * @since 1.1.1
         * @return array
         */
		public function display_product_locations($atts)
		{
            if( ! is_product() ) return;

            if( ! is_object( $product)) $product = wc_get_product( get_the_ID() );

            // Default values
            $values = shortcode_atts(array(
                'show_qty'          => 'yes',
                'show_stock_status' => 'no',
                'show_empty_stock'  => 'yes'
            ), $atts);

            if( !$values ) {
                return;
            }

            $locations = wp_get_post_terms($product->get_id(), SlwProductTaxonomy::$tax_singular_name);

            if( !empty($locations) ) {

                // Don't show locations with empty stock
                foreach( $locations as $key => $location ) {
                    if( $values['show_empty_stock'] == 'no' && $product->get_meta('_stock_at_'.$location->term_id) == 0 ) {
                        unset($locations[$key]);
                    }
                }

                // Process the other 2 parameters
                $output .= '<ul class="slw-product-locations">';
                foreach( $locations as $location ) {
                    if( $values['show_qty'] == 'yes' ) {
                        $location_stock = $product->get_meta('_stock_at_'.$location->term_id);
                        if( !empty($location_stock) ) {
                            $output .= '<li class="slw-product-location">'.apply_filters('slw_shortcode_product_location_name', $location->name, $location ).' <span class="slw-product-location-qty">'.$location_stock.'</span></li>';
                        } else {
                            $output .= '<li class="slw-product-location">'.apply_filters('slw_shortcode_product_location_name', $location->name, $location ).' <span class="slw-product-location-qty">'.__('n/a', 'stock-locations-for-woocommerce').'</span></li>';
                        }
                    } elseif( $values['show_qty'] == 'no' && $values['show_stock_status'] == 'yes' ) {
                        $location_stock = $product->get_meta('_stock_at_'.$location->term_id);
                        if( !empty($location_stock) && $location_stock > 0 ) {
                            $output .= '<li class="slw-product-location">'.apply_filters('slw_shortcode_product_location_name', $location->name, $location ).' <span class="slw-product-location-status">'.__('In stock', 'stock-locations-for-woocommerce').'</span></li>';
                        } else {
                            $output .= '<li class="slw-product-location">'.apply_filters('slw_shortcode_product_location_name', $location->name, $location ).' <span class="slw-product-location-status">'.__('Out of stock', 'stock-locations-for-woocommerce').'</span></li>';
                        }
                    } else {
                        $output .= '<li class="slw-product-location">'.apply_filters('slw_shortcode_product_location_name', $location->name, $location ).'</li>';
                    }
                }
                $output .= '</ul>';

            } else {
                $output = __('No locations found for this product!', 'stock-locations-for-woocommerce');
            }

            return $output;
		}

    }

}
