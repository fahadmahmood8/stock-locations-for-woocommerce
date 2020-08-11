<?php
/**
 * SLW Cart Class
 *
 * @since 1.2.0
 */

namespace SLW\SRC\Classes;

use SLW\SRC\Helpers\SlwStockAllocationHelper;

if ( !defined( 'WPINC' ) ) {
    die;
}

if(!class_exists('SlwCart')) {

    class SlwCart
    {
        /**
         * Construct.
         *
         * @since 1.2.0
         */
        public function __construct()
        {
			// get option
			$plugin_settings = get_option( 'slw_settings' );

			// check if show in cart is enabled
			if( $plugin_settings['show_in_cart'] == 'yes' ) {
				add_action( 'woocommerce_after_cart_item_name', array($this, 'add_cart_item_stock_locations'), 10, 2 );
				add_action( 'wp_ajax_update_cart_stock_locations', array($this, 'update_cart_stock_locations') );
				add_action( 'wp_ajax_nopriv_update_cart_stock_locations', array($this, 'update_cart_stock_locations') );
            	add_action( 'woocommerce_checkout_create_order_line_item', array($this, 'create_order_line_item_meta'), 10, 4 );
			}

			// check if different location per cart item is enabled
			if( $plugin_settings['different_location_per_cart_item'] == 'no' ) {
				add_action( 'wp_footer', array($this, 'lock_cart_item_location') );
			}
        }

		/**
         * Add stock locations to cart item.
         *
         * @since 1.2.0
         */
        public function add_cart_item_stock_locations( $cart_item, $cart_item_key )
        {
			if( empty($cart_item) ) return;

            $product_id = $cart_item['variation_id'] != 0 ? $cart_item['variation_id'] : $cart_item['product_id'];
            
            if( !empty($stock_locations = SlwStockAllocationHelper::getProductStockLocations($product_id, true, null)) ) {
                echo '<select class="slw_cart_item_stock_location" style="display:block;" required>';
                echo '<option disabled selected>'.__('Select location...', 'stock-locations-for-woocommerce').'</option>';
                foreach( $stock_locations as $id => $location ) {
                    if( $location->quantity > 0 && $location->quantity >= $cart_item['quantity'] ) {
                        echo '<option class="cart_item_stock_location_'.$cart_item_key.'" data-cart_id="'.$cart_item_key.'" value="'.$location->term_id.'">'.$location->name.'</option>';
                    }
                }
                echo '</select>';
            }
            
        }

		/**
         * Update cart with stock locations.
         *
         * @since 1.2.0
         */
        public function update_cart_stock_locations()
        {
            // Do a nonce check
            if( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'woocommerce-cart' ) ) {
                wp_send_json( array( 'nonce_fail' => 1 ) );
                exit;
            }
            // Save the stock locations to the cart meta
            $cart = WC()->cart->cart_contents;
            $cart_id = $_POST['cart_id'];
            $stock_location = $_POST['stock_location'];
            $cart_item = $cart[$cart_id];
            $cart_item['stock_location'] = $stock_location;
            WC()->cart->cart_contents[$cart_id] = $cart_item;
            WC()->cart->set_session();
            wp_send_json( array( 'success' => 1 ) );
            exit;
        }
        
        /**
         * Save stock locations to order item meta.
         *
         * @since 1.2.0
         */
        public function create_order_line_item_meta( $item, $cart_item_key, $values, $order )
        {
            foreach( $item as $cart_item_key => $cart_item ) {
                if( isset( $cart_item['stock_location'] ) ) {
                    $item->add_meta_data( '_stock_location', $cart_item['stock_location'], true );
                }
            }
		}
		
		/**
         * Locks the cart item location.
         *
         * @since 1.2.1
         */
        public function lock_cart_item_location()
        {
			?>
			<script>
			jQuery( function ( $ ) {
				$('.slw_cart_item_stock_location').on('change', function() {
					var location_id = $(this).val();
					$(this).closest('.woocommerce-cart-form').find('.slw_cart_item_stock_location').val(location_id).prop('disabled', true);
				});
			} );
			</script>
			<?php
		}

    }

}
