<?php
/**
 * SLW Product Listing Class
 *
 * @since 1.0.0
 */

namespace SLW\SRC\Classes;

use SLW\SRC\Helpers\SlwWpmlHelper;

/**
 * If this file is called directly, abort.
 *
 * @since 1.0.0
 */
if ( !defined( 'WPINC' ) ) {
	die;
}

if(!class_exists('SlwProductListing')) {

	class SlwProductListing
	{
		/**
		 * Construct.
		 *
		 * @since 1.1.0
		 */
		public function __construct()
		{
			add_filter('manage_edit-product_columns', array($this, 'remove_product_listing_column'), 10, 1);
			add_action('restrict_manage_posts', array($this, 'filter_by_taxonomy_stock_location') , 10, 2);
			add_action('manage_posts_custom_column', array($this, 'populate_stock_locations_column') );
			add_action('admin_head-post-new.php', array($this, 'addNewPreSelectLocations'));
		}

		/**
		 * Remove column from post type 'product' listing.
		 *
		 * @since 1.0.0
		 * @return array
		 */
		public function remove_product_listing_column($columns)
		{

			unset($columns['taxonomy-' . SlwLocationTaxonomy::get_Tax_Names('singular')]);

			return array_slice( $columns, 0, 5, true )
			+ array( 'stock_at_locations' => __( 'Stock at locations', 'stock-locations-for-woocommerce' ) )
			+ array_slice( $columns, 5, NULL, true );

			return $columns;
		}

		/**
		 * Creates a filter for stock location in post type 'product' listing.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function filter_by_taxonomy_stock_location($post_type, $which)
		{

			// Apply this only on a specific post type
			if ( 'product' !== $post_type )
				return;

			// A list of taxonomy slugs to filter by
			$taxonomies = array( SlwLocationTaxonomy::get_Tax_Names('singular') );

			foreach ( $taxonomies as $taxonomy_slug ) {

				// Retrieve taxonomy data
				$taxonomy_name = SlwLocationTaxonomy::get_Tax_Names('plural');

				// Retrieve taxonomy terms
				$terms = slw_get_locations($taxonomy_slug);

				// Display filter HTML
				echo "<select name='{$taxonomy_slug}' id='{$taxonomy_slug}' class='postform'>";
				echo '<option value="">' . sprintf( esc_html__( 'Show all %s', 'stock-locations-for-woocommerce' ), $taxonomy_name ) . '</option>';
				foreach ( $terms as $term ) {
					printf(
						'<option value="%1$s" %2$s>%3$s (%4$s)</option>',
						$term->slug,
						( ( isset( $_GET[$taxonomy_slug] ) && ( $_GET[$taxonomy_slug] == $term->slug ) ) ? ' selected="selected"' : '' ),
						$term->name,
						$term->count
					);
				}
				echo '</select>';
			}

		}

		/**
		 * Populate 'Stock at locations' column.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function populate_stock_locations_column($column_name)
		{
			// Grab the correct column
			if( $column_name  == 'stock_at_locations' ) {

				$product_id = SlwWpmlHelper::object_id( get_the_ID() );
				$product    = wc_get_product( $product_id );
				
				if( !empty($product) ) {

					// Check for variations
					$variations_products = array();
					if( !empty($product) && $product->is_type( 'variable' ) ) {
						$product_variations_ids = $product->get_children();
						$product_variations = array();
						foreach( $product_variations_ids as $variation_id ) {
							$product_variations[] = $product->get_available_variation( $variation_id );
						}
						foreach ($product_variations as $variation) { 
							$variations_products[] = wc_get_product( $variation['variation_id'] );
						}
					}

					// Get locations from parent product
					$locations = wp_get_post_terms( $product->get_id(), SlwLocationTaxonomy::get_Tax_Names('singular'), array('meta_key'=>'slw_location_status', 'meta_value'=>true, 'meta_compare'=>'=') );
					if( empty($locations) ) return;

					// Print data
					if( $product->is_type( 'simple' ) ) {
						echo '<label>#'.$product->get_id().' &#8628;</label><br>';
						$this->output_product_locations_for_column($product->get_id(), $locations);
					} elseif( $product->is_type( 'variable' ) ) {
						echo '<label>#'.$product->get_id().' &#8628;</label><br>';
						$this->output_product_locations_for_column($product->get_id(), $locations);
						if( !empty($variations_products) ) {
							foreach( $variations_products as $variation_product ) {
								$attributes = $variation_product->get_variation_attributes();
								if( empty($attributes) || !is_array($attributes) ) return;
								echo '<label>#'.$variation_product->get_id();
								$c = 0;
								foreach( $attributes as $attribute ) {
									if( $c == 0 ) {
										echo ' ('.ucfirst($attribute).')';
									} else {
										echo ', ('.ucfirst($attribute).')';
									}
									$c++;
								}
								echo ' &#8628;</label><br>';
								$this->output_product_locations_for_column($variation_product->get_id(), $locations);
							}
						}
					}

				}

			}

		}

		/**
		 * Output locations for simple and variable products for column.
		 *
		 * @since 1.1.2
		 * @return void
		 */
		private function output_product_locations_for_column($product_id, $locations)
		{
			if( ! empty( $product_id ) ) {
				$product_id = SlwWpmlHelper::object_id( $product_id );
			}

			if( !empty($locations) ) {
				foreach($locations as $location) {
					// If out of stock
					$location_qty = get_post_meta( $product_id, '_stock_at_' . $location->term_id, true );

					if( $location_qty <= 0 ) {
						echo '<span style="margin-left: 10px;"><mark class="outofstock">' . $location->name . '</mark> (' . $location_qty . ')</span><br>';
					} else { // If in stock
						echo '<span style="margin-left: 10px;"><mark class="instock">' . $location->name . '</mark> (' . $location_qty . ')</span><br>';
					}
				}
			}
		}

		/**
		 * Pre select locations on creating a new product listing
		 */
		function addNewPreSelectLocations()
		{
			global $current_screen;

			// If not our post type, do nothing
			if($current_screen->post_type != 'product') {
				return;
			}

			// Locations
			$locations = array();

			// Get Terms
			$terms = slw_get_locations();

			foreach ($terms as $term) {
				$slw_default_location = get_term_meta($term->term_id, 'slw_default_location', true);

				if ($slw_default_location == 1) {
					$locations[] = $term->term_id;
				}
			}

			echo \SLW\SRC\Helpers\view('product-pre-select-location-scripts', ['locations' => $locations]);
		}

	}

}
