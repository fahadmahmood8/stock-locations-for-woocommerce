<?php
/**
 * SLW Product Rest Class
 *
 * @since 1.1.4
 */

namespace SLW\SRC\Classes;

use SLW\SRC\Helpers\SlwWpmlHelper;

if ( !defined( 'WPINC' ) ) {
	die;
}

if(!class_exists('SlwProductRest')) {

	class SlwProductRest
	{

		/**
		 * Construct.
		 *
		 * @since 1.1.4
		 */
		public function __construct()
		{
			add_action('rest_api_init', array($this, 'rest_api_init'));
		}

		/**
		 * Create REST field
		 * 
		 * @since 1.1.4
		 */
		public function rest_api_init()
		{
			register_rest_field('product', SlwLocationTaxonomy::$tax_plural_name, array(
				'get_callback'    => array($this, 'product_get_callback'),
				'update_callback' => array($this, 'product_update_callback'),
				'schema' => null,
			));

			register_rest_field('product_variation', SlwLocationTaxonomy::$tax_plural_name, array(
				'get_callback'    => array($this, 'product_get_callback'),
				'update_callback' => array($this, 'product_update_callback'),
				'schema' => null,
			));
		}

		/**
		 * Get product callback
		 * 
		 * @since 1.1.4
		 * 
		 * @param $post
		 * @return mixed
		 */
		public function product_get_callback( $post, $attr, $request, $object_type )
		{
			$terms = array();

			$product_id = SlwWpmlHelper::object_id( $post['id'], get_post_type( $post['id'] ) );

			// Get parent post ID
			// This is either the current product or its parent_id
			$parentPostId = ($object_type === 'product_variation') ? wp_get_post_parent_id($product_id) : $product_id;

			// Get terms
			foreach (wp_get_post_terms($parentPostId, SlwLocationTaxonomy::$tax_singular_name) as $term) {
				$terms[] = array(
					'id'        => $term->term_id,
					'name'      => $term->name,
					'slug'      => $term->slug,
					'quantity'  => get_post_meta($post['id'], '_stock_at_' . $term->term_id, true)
				);
			}

			return $terms;
		}

		/**
		 * Update product callback
		 * 
		 * @since 1.1.4
		 * 
		 * @param $post
		 * @param $request
		 */
		public function product_update_callback( $values, $post, $attr, $request, $object_type )
		{
			// Data is not valid or empty, nothing to do
			if (!is_array($values) || !sizeof($values)) {
				return;
			}

			// Get post ID, important we use this and not ->id,
			// as this will return the correct variation ID if required
			$postId = SlwWpmlHelper::object_id( $post->get_id(), $post->get_type() );

			// Get parent post ID
			// This is either the current product or its parent_id
			$parentPostId = ($object_type === 'product_variation') ? $post->get_parent_id() : $postId;

			$stockLocationTermIds = array();

			$totalQuantity = 0;

			foreach ($values as $location) {
				$locationId = (isset($location['id'])) ? absint($location['id']) : get_term_by('slug', $location['slug'], SlwLocationTaxonomy::$tax_singular_name)->term_id;
				$quantity = (isset($location['quantity'])) ? $location['quantity'] : 0;

				// It is possible to provide a null quantity to delete product from location
				if (is_null($quantity)) {
					// Delete post meta
					delete_post_meta($postId, '_stock_at_' . $locationId);
				} else {
					// We must only keep location IDs we wish to keep as valid locations
					$stockLocationTermIds[] = $locationId;

					// Set locations stock level
					update_post_meta($postId, '_stock_at_' . $locationId, $quantity);

					$totalQuantity += $quantity;
				}
			}

			// Update product stock
			if( $totalQuantity != 0 ) {
				$product = wc_get_product($parentPostId);
				wc_update_product_stock( $product, $totalQuantity, 'set', false );
			}

			// Set terms
			wp_set_object_terms($parentPostId, $stockLocationTermIds, SlwLocationTaxonomy::$tax_singular_name);
		}

	}

}
