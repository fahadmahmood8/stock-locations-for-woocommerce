<?php
/**
 * SLW Order Item Class
 *
 * @since 1.0.0
 */

namespace SLW\SRC\Classes;

use SLW\SRC\Classes\SlwAdminNotice;

/**
 * If this file is called directly, abort.
 *
 * @since 1.0.0
 */
if ( !defined( 'WPINC' ) ) {
    die;
}

if(!class_exists('SlwOrderItem')) {

    class SlwOrderItem
    {
		private $items;

		/**
         * Construct.
         *
         * @since 1.1.0
         */
		public function __construct()
		{
			add_action('woocommerce_admin_order_item_headers', array($this, 'add_stock_location_column_wc_order'), 10, 1);  // Since WC 3.0.2
			add_action('woocommerce_admin_order_item_values', array($this, 'add_stock_location_inputs_wc_order'), 10, 3);   // Since WC 3.0.2
			add_action('save_post_shop_order', array($this, 'update_stock_locations_data_wc_order_save'), 10, 3);
			add_filter('woocommerce_hidden_order_itemmeta', array($this, 'hide_stock_locations_itemmeta_wc_order'), 10, 1); // Since WC 3.0.2
		}

        /**
         * Adds custom column for Stock Location in WC Order items.
         *
         * @since 1.0.0
         * @return void
         */
        public function add_stock_location_column_wc_order($order)
        {
            // display the column name
            echo '<th>' . __('Stock Locations', 'stock-locations-for-woocommerce') . '</th>';

            // Declare variable as array type
            $items = [];
            // Loop through order items
            foreach ( $order->get_items() as $item => $item_data ) {
                $items[] = [
                    'product_id' => $item_data['product_id'],
                    'order_item_id' => $item,
                ];
            }
            // Assign variable to the class property
            $this->items = $items;

            // Loop throw order items
            foreach ( $order->get_items() as $item => $item_data ) {
                // Check if the stock locations are already updated in items of this order and show warning if necessary
                if( empty( wc_get_order_item_meta($item, '_item_stock_locations_updated', true) ) ) {
                    SlwAdminNotice::displayWarning(__('Partial or total stock in locations is missing in this order. Please fill the remaining stock.', 'stock-locations-for-woocommerce'));
                }
            }
        }

        /**
         * Adds inputs to custom column for Stock Locations in WC Order items.
         *
         * @since 1.0.0
         * @return void
         */
        public function add_stock_location_inputs_wc_order($_product, $item, $item_id)
        {

            if( is_object($_product) ) {

                // Check if product is a variation
                if( $_product->get_type() === 'variation' ) {

                    // Get variation parent id
                    $parent_id = $item->get_product_id();

                    // Get the variation id
                    $variation_id = $_product->get_ID();

                    // Get the parent location terms
                    $product_stock_location_terms = get_the_terms($parent_id, SlwProductTaxonomy::get_Tax_Names('singular'));

                    // If parent doesn't have terms show message
                    if(!$product_stock_location_terms) {
                        echo '<td width="15%">';
                        echo '<div display="block">' . __('To be able to manage the stock for this product, please add it to a <b>Stock location</b>!', 'stock-locations-for-woocommerce') . '</div>';
                        echo '</td>';
                    } else {
                        // Add stock location inputs
                        $this->product_stock_location_inputs($variation_id, $product_stock_location_terms, $item, $item_id);
                    }

                } else {

                    // Get the product id
                    $product_id = $item->get_product_id();

                    // Product location terms
                    $product_stock_location_terms = get_the_terms($product_id, SlwProductTaxonomy::get_Tax_Names('singular'));

                    // If product doesn't have terms show message
                    if(!$product_stock_location_terms) {
                        echo '<td width="15%">';
                        echo '<div display="block">' . __('To be able to manage the stock for this product, please add it to a <b>Stock location</b>!', 'stock-locations-for-woocommerce') . '</div>';
                        echo '</td>';
                    } else {
                        // Add stock location inputs
                        $this->product_stock_location_inputs($product_id, $product_stock_location_terms, $item, $item_id);
                    }

                }

            }

        }

        /**
         * Creates the inputs for Stock Locations in WC Order items.
         *
         * @since 1.0.0
         * @return void
         */
        public function product_stock_location_inputs($id, $product_stock_location_terms, $item, $item_id)
        {

            // Get stock management status for this product
            $stock_management = get_post_meta($id, '_manage_stock', true);

            // If product allows stock management
            if($stock_management === 'yes') {

                // Add the input field to values table
                echo '<td width="15%">';

                    // Loop throw location terms
                    foreach($product_stock_location_terms as $term) {

                        // Define $args_1 as array type
                        $args_1 = array();

                        // Get the item meta
                        $postmeta_stock_at_term = get_post_meta($id, '_stock_at_' . $term->term_id, true);
                        if(!$postmeta_stock_at_term) {
                            $postmeta_stock_at_term = 0;
                        }

                        // Get the item meta
                        $itemmeta_stock_update_at_term = wc_get_order_item_meta($item_id, '_item_stock_updated_at_' . $term->term_id, true);

                        // If the order item has the stock locations updated, show the quantity already subtracted
                        if(wc_get_order_item_meta($item_id, '_item_stock_locations_updated', true) === 'yes') {
                            $args_1['custom_attributes'] = array('readonly' => 'readonly');

                            if($itemmeta_stock_update_at_term) {
                                $args_1['label'] = $term->name . ' <b>(' . $postmeta_stock_at_term . ')</b> <span style="color:green;">-' . $itemmeta_stock_update_at_term . '</span>';
                            } else {
                                $args_1['label'] = $term->name . ' <b>(' . $postmeta_stock_at_term . ')</b>';
                            }

                        } else {
                            $args_1['label'] = $term->name . ' <b>(' . $postmeta_stock_at_term . ')</b>';
                        }

                        // If this location doesn't have stock don't show the input
                        if( empty($postmeta_stock_at_term) || ($postmeta_stock_at_term <= 0) ) {
                            $args_1['description'] = __('This location doesn\'t have stock and can\'t be subtracted.', 'stock-locations-for-woocommerce');
                            $args_1['type'] = 'hidden';
                        } else {
                            $args_1['description'] = __( 'Enter the stock amount you want to subtract from this location.', 'stock-locations-for-woocommerce' );
                            $args_1['type'] = 'number';
                        }


                        // Define $args_2 array
                        $args_2 = array(
                            'id'                => SLW_PLUGIN_SLUG . '_oitem_' . $id . '_' . $term->term_id,
                            'desc_tip'          => true,
                            'class'             => 'woocommerce ' . SLW_PLUGIN_SLUG . '_oitem_' . $id . ' ' . SLW_PLUGIN_SLUG . '_oitem',
                            'data_type'         => 'stock',
                        );

                        // Merge the two arrays
                        $args = array_merge($args_1, $args_2);

                        // Create the input
                        woocommerce_wp_text_input($args);

                    }

                echo '</td>';

            } else {

                // Show message if the product/variant doesn't allow stock management
                echo '<td width="15%">';
                echo '<div display="block">' . __('This product/variation don\'t have stock management activated.', 'stock-locations-for-woocommerce') . '</div>';
                echo '</td>';

            }

        }

        /**
         * Updates Stock Locations upon WC Order save.
         *
         * @since 1.0.0
         * @return int|void
         */
        public function update_stock_locations_data_wc_order_save($post_id, $post, $update)
        {

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
                return $post_id;

            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
                return $post_id;

            if ( ! current_user_can( 'edit_shop_order', $post_id ) )
                return $post_id;

            // Get an instance of the WC_Order object
            $order = wc_get_order( $post_id );

            // On order update
            if( $update ) {

                // Loop through order items
                foreach ( $order->get_items() as $item => $item_data ) {

                    // Get item product
                    $item_product = $item_data->get_product();

                    // Get item product id
                    $item_id = $item_product->get_ID();

                    // Check if item is variation
                    if( $item_product->get_type() === 'variation' ) {
                        // Get variation parent
                        $product = wc_get_product( $item_product->get_parent_id() );
                        // Get item parent location terms
                        $item_stock_location_terms = get_the_terms($product->get_ID(), SlwProductTaxonomy::get_Tax_Names('singular'));
                    } else {
                        // Get item location terms
                        $item_stock_location_terms = get_the_terms($item_id, SlwProductTaxonomy::get_Tax_Names('singular'));
                    }

                    // Check if location terms exist for this item or parent
                    if( isset($item_stock_location_terms) && !empty($item_stock_location_terms) ) {

                        // Get item quantity
                        $item_quantity = $item_data->get_quantity();

                        // Get $_POST input quantities and save to array
                        $input_quantities = [];
                        foreach ( $item_stock_location_terms as $term ) {
                            $input_quantities[] = sanitize_text_field($_POST[SLW_PLUGIN_SLUG . '_oitem_' . $item_id . '_' . $term->term_id]);
                        }
                        $input_quantities = array_sum($input_quantities);

                        // Get post meta
                        $postmeta_stock = get_post_meta($item_id, '_stock', true);
                        $postmeta_stock_status = get_post_meta($item_id, '_stock_status', true);

                        // Grab all input values
                        $input_values = array();

                        // Set terms counter
                        $counter = 0;

                        // Count how many terms exist for this product
                        $terms_total = count($item_stock_location_terms);

                        // Loop through location terms
                        foreach ( $item_stock_location_terms as $term ) {

                            if( isset($_POST[SLW_PLUGIN_SLUG . '_oitem_' . $item_id . '_' . $term->term_id]) ) {

                                // Initiate counter
                                $counter++;

                                $item_stock_location_subtract_input_qty = sanitize_text_field($_POST[SLW_PLUGIN_SLUG . '_oitem_' . $item_id . '_' . $term->term_id]);
                                $postmeta_stock_at_term = get_post_meta($item_id, '_stock_at_' . $term->term_id, true);

                                // Check some conditions before updates
                                if( ($item_stock_location_subtract_input_qty !== 0) && !empty($item_stock_location_subtract_input_qty) && ($item_stock_location_subtract_input_qty <= $item_quantity) && ($input_quantities === $item_quantity) ) {

                                    // Save input values to array
                                    $input_values[] = $item_stock_location_subtract_input_qty;

                                    // Update the postmeta of the product
                                    update_post_meta( $item_id, '_stock_at_' . $term->term_id, $postmeta_stock_at_term - $item_stock_location_subtract_input_qty );

                                    // Note message
                                    $note = sprintf( __('The stock in the location %1$s was updated in -%2$d for the product %3$s', 'stock-locations-for-woocommerce'), $term->name, $item_stock_location_subtract_input_qty, $item_product->get_name() );

                                    // Add the note
                                    $order->add_order_note( $note );

                                    // Update product Stock and Stock Status
                                    if( ( ($postmeta_stock - $item_stock_location_subtract_input_qty) <= 0 ) && ($postmeta_stock_status === 'instock') ) {
                                        update_post_meta($item_id, '_stock_status', 'outofstock');
                                    } else {
                                        update_post_meta($item_id, '_stock_status', 'instock');
                                    }

                                    // Update the itemmeta of the order item
                                    wc_update_order_item_meta($item, '_item_stock_locations_updated', 'yes');
                                    wc_update_order_item_meta($item, '_item_stock_updated_at_' . $term->term_id, $item_stock_location_subtract_input_qty);

                                }

                            }

                        }

                        // Update stock when reach the last term
                        if($counter === $terms_total) {
                            update_post_meta( $item_id, '_stock', $postmeta_stock - array_sum($input_values) );
                        }

                    }

                    // Check if stock in locations are updated for this item
                    if( empty( wc_get_order_item_meta($item_id, '_item_stock_locations_updated', true) ) ) {
                        SlwAdminNotice::displayWarning(__('Partial or total stock in locations is missing in this order. Please fill the remaining stock.', 'stock-locations-for-woocommerce'));
                    } else {
                        SlwAdminNotice::displaySuccess(__('Stock in locations updated successfully!', 'stock-locations-for-woocommerce'));
                    }

                }
            }

        }

        /**
         * Hides Stock Location item meta from WC Order.
         *
         * @since 1.0.1
         * @return array
         */
        public function hide_stock_locations_itemmeta_wc_order($arr)
        {
            // Get an instance of the WC_Order object
			$order = wc_get_order( get_the_id() );

			if( $order && $order->get_items() ) {
				// Loop through order items
				foreach ( $order->get_items() as $item => $item_data ) {
					if( $item_data->get_product() ) {
						// Get item ID
						$product = $item_data->get_product();
						$item_id = $product->get_ID();

						// If variation get the parent ID instead
						if($product->post_type === 'product_variation') {
							$item_id = $product->get_parent_id();
						}

						// Get item location terms
						$item_stock_location_terms = get_the_terms($item_id, SlwProductTaxonomy::get_Tax_Names('singular'));

						if($item_stock_location_terms) {
							// Loop through location terms
							foreach ( $item_stock_location_terms as $term ) {
								$arr[] = '_item_stock_updated_at_' . $term->term_id;
							}
						}
					}

				}

				$arr[] = '_item_stock_locations_updated';
			}

            return $arr;
        }

    }

}
