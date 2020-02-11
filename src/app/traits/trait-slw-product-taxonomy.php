<?php
/**
 * SLW Product Taxonomy Trait
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

if(!trait_exists('SlwProductTaxonomy')) {

    trait SlwProductTaxonomy
    {

        public static $tax_plural_name = 'locations';
        public static $tax_singular_name = 'location';

        /**
         * Returns the taxonomy default names.
         *
         * @since 1.0.0
         * @return array
         */
        public static function get_tax_names($type)
        {
            $data = [
                'plural' => self::$tax_plural_name,
                'singular' => self::$tax_singular_name
            ];

            return $data[$type];
        }

        /**
         * Creates the taxonomy.
         *
         * @since 1.0.0
         * @return void
         */
        public function create_taxonomy(): void
        {

            $labels = array(
                'name'                       => __('Location'),
                'singular_name'              => __('Location'),
                'menu_name'                  => __('Stock locations'),
                'all_items'                  => __('All Items'),
                'parent_item'                => __('Parent Item'),
                'parent_item_colon'          => __('Parent Item:'),
                'new_item_name'              => __('New Item Name'),
                'add_new_item'               => __('Add New Item'),
                'edit_item'                  => __('Edit Item'),
                'update_item'                => __('Update Item'),
                'separate_items_with_commas' => __('Separate Item with commas'),
                'search_items'               => __('Search Items'),
                'add_or_remove_items'        => __('Add or remove Items'),
                'choose_from_most_used'      => __('Choose from the most used Items'),
            );
            $args = array(
                'labels'                     => $labels,
                'hierarchical'               => true,
                'public'                     => true,
                'show_ui'                    => true,
                'show_admin_column'          => true,
                'show_in_nav_menus'          => true,
                'show_tagcloud'              => true,
            );
            
            register_taxonomy( $this->get_tax_names('singular'), 'product', $args );
            register_taxonomy_for_object_type( $this->get_tax_names('singular'), 'product' );
            
        }

    }

}