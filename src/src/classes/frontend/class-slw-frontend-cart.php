<?php
/**
 * SLW Frontend Cart Class
 *
 * @since 1.2.0
 */

namespace SLW\SRC\Classes\Frontend;

use SLW\SRC\Helpers\SlwStockAllocationHelper;
use SLW\SRC\Helpers\SlwFrontendHelper;
use SLW\SRC\Helpers\SlwWpmlHelper;

if ( !defined( 'WPINC' ) ) {
	die;
}

if( !class_exists('SlwFrontendCart') ) {

	class SlwFrontendCart
	{

		/**
		 * Construct.
		 *
		 * @since 1.2.0
		 */
		public function __construct()
		{
			// get settings
			$this->plugin_settings = get_option( 'slw_settings' );

			// check if show in cart is enabled
			if( isset($this->plugin_settings['show_in_cart']) && $this->plugin_settings['show_in_cart'] == 'yes' ) {
				add_action( 'woocommerce_after_cart_item_name', array($this, 'add_cart_item_stock_locations'), 10, 2 );
				add_action( 'wp_ajax_update_cart_stock_locations', array($this, 'update_cart_stock_locations') );
				add_action( 'wp_ajax_nopriv_update_cart_stock_locations', array($this, 'update_cart_stock_locations') );
				add_action( 'woocommerce_checkout_create_order_line_item', array($this, 'create_order_line_item_meta'), 10, 4 );
			}

			// check if different location per cart item is enabled
			if( isset($this->plugin_settings['different_location_per_cart_item']) && $this->plugin_settings['different_location_per_cart_item'] == 'no' ) {
				add_action( 'wp_footer', array($this, 'lock_cart_item_location') );
			}

			// check if location selection required is enabled
			if( isset($this->plugin_settings['cart_location_selection_required']) && $this->plugin_settings['cart_location_selection_required'] == 'on' ) {
				add_action( 'wp_footer', array($this, 'cart_item_location_selection_required') );
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

			$product_id            = $cart_item['variation_id'] != 0 ? $cart_item['variation_id'] : $cart_item['product_id'];
			$product_id            = SlwWpmlHelper::object_id( $product_id );
			$stock_locations       = SlwFrontendHelper::get_all_product_stock_locations_for_selection( $product_id );
			$default_location      = isset( $this->plugin_settings['default_location_in_frontend_selection'] ) ? get_post_meta( $product_id, '_slw_default_location', true ) : 0;
			$lock_default_location = isset( $this->plugin_settings['lock_default_location_in_frontend'] ) && $this->plugin_settings['lock_default_location_in_frontend'] == 'on' ? true : false;

			if( !empty($stock_locations) ) {
				echo '<label class="slw_cart_item_stock_location_label">'.__('Nearest Location', 'stock-locations-for-woocommerce').':</label>';

				// lock to default location if enabled
				if( $lock_default_location && $default_location != 0 ) {
					echo '<select class="slw_item_stock_location slw_cart_item_stock_location_selection" style="display:block;" required disabled>';
					echo '<option class="cart_item_stock_location_'.$cart_item_key.'" data-cart_id="'.$cart_item_key.'" value="'.$default_location.'" selected="selected" disabled="disabled">'.$stock_locations[$default_location]['name'].'</option>';
					echo '</select>';
					return;
				}

				// default behaviour
				if( isset($cart_item['stock_location']) ) {
					echo '<select class="slw_item_stock_location slw_cart_item_stock_location_selection" style="display:block;" required>';
					echo '<option disabled>'.__('Select location...', 'stock-locations-for-woocommerce').'</option>';
				} else {
					echo '<select class="slw_item_stock_location slw_cart_item_stock_location_selection" style="display:block;" required>';
					echo '<option disabled selected>'.__('Select location...', 'stock-locations-for-woocommerce').'</option>';
				}

				foreach( $stock_locations as $id => $location ) {
					$selected = $disabled = '';
					if( ($location['quantity'] > 0 && $location['quantity'] >= $cart_item['quantity']) || ($location['quantity'] < 1 && $location['allow_backorder'] == 1) ) {
						if( isset($cart_item['stock_location']) && $cart_item['stock_location'] == $location['term_id'] ) {
							$selected = 'selected="selected"';
						}
					} else {
						$disabled = 'disabled="disabled"';
					}
					echo '<option class="cart_item_stock_location_'.$cart_item_key.'" data-cart_id="'.$cart_item_key.'" value="'.$location['term_id'].'" '.$selected.' '.$disabled.'>'.$location['name'].'</option>';
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
			if( ! isset($_POST['cart_id']) || ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'woocommerce-cart' ) ) {
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
			if( is_cart() ) {
				?>
				<script>
				jQuery( function ( $ ) {
					$(document).ready(function() {
						$('.slw_cart_item_stock_location_selection').on('change', function() {
							var location_id = $(this).val();
							$(this).closest('.woocommerce-cart-form').find('.slw_cart_item_stock_location_selection').val(location_id).prop('disabled', true);
						});
					});
				} );
				</script>
				<?php
			}
		}

		/**
		 * Make locations selection in cart required.
		 *
		 * @since 1.3.3
		 */
		public function cart_item_location_selection_required()
		{
			if( is_cart() ) {
				?>
				<script>
				jQuery( function( $ ) {
					$(document).ready(function() {
						validate();
						$('.slw_cart_item_stock_location_selection').on('change', function() {
							validate();
						});
					});

					function validate() {
						var inputsWithValues = 0;
						var myInputs = $(".slw_cart_item_stock_location_selection");

						myInputs.each(function(e) {
							if ($(this).val()) {
								inputsWithValues += 1;
							}
						});

						if (inputsWithValues != myInputs.length) {
							$('.checkout-button').addClass('slw_checkout_disable');
						} else {
							$('.checkout-button').removeClass('slw_checkout_disable');
						}
					}
				} );
				</script>
				<?php
			}
		}

	}

}
