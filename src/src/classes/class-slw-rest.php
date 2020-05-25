<?php
/**
 * SLW Product Rest Class
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

if(!class_exists('SlwProductRest')) {

    class SlwProductRest
    {

        /**
         * Construct.
         *
         * @since 1.1.0
         */
        public function __construct()
        {
            add_action('rest_api_init', array($this, 'rest_api_init'));
        }

        /**
         * Create REST field
         */
        public function rest_api_init()
        {
            // register_rest_field ( 'name-of-post-type', 'name-of-field-to-return', array-of-callbacks-and-schema() )
            register_rest_field('product', 'locations', array(
                'get_callback'    => array($this, 'product_get_callback'),
                'update_callback' => array($this, 'product_update_callback'),
                'schema' => null,
            ));

            // register_rest_field ( 'name-of-post-type', 'name-of-field-to-return', array-of-callbacks-and-schema() )
            register_rest_field('product_variation', 'locations', array(
                'get_callback'    => array($this, 'product_get_callback'),
                'update_callback' => array($this, 'product_update_callback'),
                'schema' => null,
            ));
        }

        /**
         * @param $post
         *
         * @return mixed
         */
        public function product_get_callback($post, $attr, $request, $object_type)
        {
            $terms = array();

            // Get parent post ID
            // This is either the current product or its parent_id
            $parentPostId = ($object_type === 'product_variation') ? wp_get_post_parent_id($post['id']) : $post['id'];

            // Get terms
            foreach (wp_get_post_terms($parentPostId, SlwProductTaxonomy::get_tax_names('singular')) as $term) {
                $terms[] = array(
                    'id'   => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'quantity' => get_post_meta($post['id'], '_stock_at_' . $term->term_id, true)
                );
            }

            return $terms;
        }

        /**
         * @param $post
         * @param $request
         */
        public function product_update_callback($values, $post, $attr, $request, $object_type)
        {
            // Data is not valid or empty, nothing to do
            if (!is_array($values) || !sizeof($values)) {
                return;
            }

            // Get post ID, important we use this and not ->id,
            // as this will return the correct variation ID if required
            $postId = $post->get_id();

            // Get parent post ID
            // This is either the current product or its parent_id
            $parentPostId = ($object_type === 'product_variation') ? $post->parent_id : $postId;

            $stockLocationTermIds = array();

            foreach ($values as $location) {
                $locationId = (isset($location['id'])) ? absint($location['id']) : get_term_by('slug', $location['slug'], SlwProductTaxonomy::$tax_plural_name)->term_id;
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
                }
            }

            // Set terms
            wp_set_object_terms($parentPostId, $stockLocationTermIds, SlwProductTaxonomy::$tax_plural_name);
        }

    }

}
