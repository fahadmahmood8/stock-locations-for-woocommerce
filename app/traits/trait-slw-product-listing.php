<?php
/**
 * SLW Product Listing Trait
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

if(!trait_exists('SlwProductListing')) {

    trait SlwProductListing
    {

        /**
         * Remove column from post type 'product' listing.
         *
         * @since 1.0.0
         * @return array
         */
        public function remove_product_listing_column($columns)
        {

            unset($columns['taxonomy-' . SlwProductTaxonomy::get_Tax_Names('singular')]);

            // return array_slice( $columns, 0, 5, true )
            // + array( 'stock_at_locations' => __( 'Stock at locations', 'stock-locations-for-woocommerce' ) )
            // + array_slice( $columns, 5, NULL, true );

            return $columns;
        }

        /**
         * Creates a filter for stock location in post type 'product' listing.
         *
         * @since 1.0.0
         * @return void
         */
        public function filter_by_taxonomy_stock_location($post_type, $which): void
        {

            // Apply this only on a specific post type
            if ( 'product' !== $post_type )
                return;

            // A list of taxonomy slugs to filter by
            $taxonomies = array( SlwProductTaxonomy::get_Tax_Names('singular') );

            foreach ( $taxonomies as $taxonomy_slug ) {

                // Retrieve taxonomy data
                $taxonomy_name = SlwProductTaxonomy::get_Tax_Names('plural');

                // Retrieve taxonomy terms
                $terms = get_terms( $taxonomy_slug );

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
        public function populate_stock_locations_column($column_name): void
        {
            // Grab the correct column
            if( $column_name  == 'stock_at_locations' ) {

                // Get location terms
                $locations = get_the_terms( get_the_ID(), SlwProductTaxonomy::get_Tax_Names('singular') );

                // Check if product has location terms assigned
                if( $locations ) {

                    // Iterate over terms
                    foreach($locations as $location) {
                        // If out of stock
                        if( get_post_meta( get_the_ID(), '_stock_at_' . $location->term_id, true ) <= 0 ) {
                            echo '<mark class="outofstock">' . $location->name . '</mark> (' . get_post_meta( get_the_ID(), '_stock_at_' . $location->term_id, true ) . ')<br>';
                        } else { // If in stock
                            echo '<mark class="instock">' . $location->name . '</mark> (' . get_post_meta( get_the_ID(), '_stock_at_' . $location->term_id, true ) . ')<br>';
                        }
                    }

                }

            }

        }

    }

}
