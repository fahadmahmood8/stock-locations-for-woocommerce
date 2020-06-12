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
            add_shortcode('slw_product_variations_locations', array($this, 'display_product_variations_locations'));
            add_shortcode('slw_product_message', array($this, 'display_product_message'));
            add_shortcode('slw_cart_message', array($this, 'display_cart_message'));
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
         * @return string
         */
		public function display_product_locations($atts)
		{
            global $woocommerce, $product, $post;

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

            $output = '';

            if( !empty($product) ) {
                // Get locations from parent product
                $locations = wp_get_post_terms($product->get_id(), SlwProductTaxonomy::$tax_singular_name);
                // Build output
                $output .= '<div class="slw-product-locations">';
                $output .= $this->output_product_locations_for_shortcode($product, $locations, $values);
                $output .= '</div>';
            }

            return $output;
            
        }

        /**
         * Displays the product variation locations.
         *
         * @since 1.1.2
         * @return string
         */
		public function display_product_variations_locations($atts)
		{
            global $woocommerce, $product, $post;

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

            $output = '';

            if( !empty($product) ) {
                // Check for variations
                $variations_products = array();
                if( !empty($product) && $product->is_type( 'variable' ) ) {
                    $available_variations = $product->get_available_variations();
                    foreach ($available_variations as $variation) { 
                        $variations_products[] = wc_get_product( $variation['variation_id'] );
                    }
                }

                // Get locations from parent product
                $locations = wp_get_post_terms($product->get_id(), SlwProductTaxonomy::$tax_singular_name);

                if( !empty($variations_products) ) {
                    foreach( $variations_products as $variation_product ) {
                        foreach( $attributes = $variation_product->get_variation_attributes() as $attribute ) {
                            $output .= '<div class="slw-variation-'.$attribute.'-locations"><label>'.ucfirst($attribute).'</label>';
                        }
                        $output .= $this->output_product_locations_for_shortcode($variation_product, $locations, $values);
                        $output .= '</div>';
                    }
                }
            }

            return $output;
            
        }
        
        /**
         * Output locations for simple and variable products shortcodes.
         *
         * @since 1.1.2
         * @return string
         */
        private function output_product_locations_for_shortcode($product, $locations, $values)
        {
            if( !empty($locations) ) {

                // Don't show locations with empty stock
                foreach( $locations as $key => $location ) {
                    if( $values['show_empty_stock'] == 'no' && $product->get_meta('_stock_at_'.$location->term_id) == 0 ) {
                        unset($locations[$key]);
                    }
                }

                // Process the other 3 parameters
                $output = '<ul class="slw-product-locations-list">';
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

        /**
         * Displays the product locations
         *
         * @param $atts
         * @param string $innerHtml
         *
         * @return string
         */
        public function display_product_message($atts, $innerHtml = '')
        {
            global $woocommerce, $product, $post;

            if( ! is_product() ) return;

            if( ! is_object( $product)) $product = wc_get_product( get_the_ID() );

            // Default values
            $values = shortcode_atts(array(
                'is_available' => 'yes',
                'only_location_available' => 'no',
                'location' => '',
            ), $atts);

            if( !$values ) {
                return;
            }

            // Data
            $isAvailable = $values['is_available'];
            $onlyLocationAvailable = $values['only_location_available'];
            $location = $values['location'];

            // Do nothing
            if ($location === '') {
                return '';
            }

            // Vars
            $locationSlugToTermId = array();
            $stockedAt = array();
            $hasAvailableStockMultipleLocations = false;
            $foundAvailableStock = false;

            // Get all available locations for product
            $locations = wp_get_post_terms($product->get_id(), SlwProductTaxonomy::$tax_singular_name);
            foreach ($locations as $loc) {
                // Look up vars
                $locationSlugToTermId[$loc->slug] = $loc->term_id;
                $stockedAt[$loc->term_id] = $product->get_meta('_stock_at_' . $loc->term_id, true);

                // We have multiple available stocked locations
                if ($stockedAt[$loc->term_id] > 0) {
                    if ($foundAvailableStock) {
                        $hasAvailableStockMultipleLocations = true;
                    }

                    $foundAvailableStock = true;
                }
            }

            // Get location stock
            $locationStock = $stockedAt[$locationSlugToTermId[$location]];

            // Multiple available stock
            if (strtoupper($onlyLocationAvailable) === 'YES' && $hasAvailableStockMultipleLocations) {
                return '';
            }

            // Decide when to show / hide
            if (strtoupper($isAvailable) === 'YES') {
                if ($locationStock > 0) {
                    return $innerHtml;
                }
            } else {
                if (is_null($locationStock) || empty($locationStock) || $locationStock <= 0) {
                    return $innerHtml;
                }
            }
        }

        /**
         * Displays the product locations
         *
         * @param $atts
         * @param string $innerHtml
         *
         * @return string
         */
        public function display_cart_message($atts, $innerHtml = '')
        {
            global $woocommerce, $post;

            if(!is_cart()) {
                return '';
            }

            // Default values
            $values = shortcode_atts(array(
                'qty_from_location' => '',
            ), $atts);

            if(!$values) {
                return '';
            }

            // Data
            $qtyFromLocation = $values['qty_from_location'];

            // Do nothing
            if ($qtyFromLocation === '') {
                return '';
            }

            // Location Term
            $locTerm = get_term_by('slug', $qtyFromLocation, SlwProductTaxonomy::$tax_singular_name);

            // Location was not found
            if (is_null($locTerm) || empty($locTerm)) {
                return '';
            }

            $items = $woocommerce->cart->get_cart();
            foreach($items as $item => $values) {
                $product = wc_get_product($values['data']->get_id());
                $stock = $product->get_meta('_stock_at_' . $locTerm->term_id, true);

                if ($stock > 0) {
                    return $innerHtml;
                }
            }
        }

    }

}
