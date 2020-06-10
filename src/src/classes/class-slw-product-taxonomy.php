<?php
/**
 * SLW Product Taxonomy Class
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

if(!class_exists('SlwProductTaxonomy')) {

    class SlwProductTaxonomy
    {
        public static $tax_plural_name = 'locations';
		public static $tax_singular_name = 'location';

		/**
         * Construct.
         *
         * @since 1.1.0
         */
		public function __construct()
		{
			add_action( 'init', array($this, 'create_taxonomy'), 1 );
            add_action('location_edit_form', array($this, 'hideFields'));
            add_action('location_add_form', array($this, 'hideFields'));
            add_filter('manage_edit-location_columns', array($this, 'editColumns'));
            add_action('location_edit_form', array($this, 'formFields'), 100, 2);
            add_action('location_add_form_fields', array($this, 'formFields'), 10, 2);
            add_action('edited_location', array($this, 'formSave'), 10, 2);
            add_action('created_location', array($this, 'formSave'), 10, 2);
        }

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
        public function create_taxonomy()
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
			$capabilities = array(
				'manage_terms'               => 'manage_woocommerce',
				'edit_terms'                 => 'manage_woocommerce',
				'delete_terms'               => 'manage_woocommerce',
				'assign_terms'               => 'manage_woocommerce',
			);
            $args = array(
                'labels'                     => $labels,
                'hierarchical'               => true,
                'public'                     => true,
                'show_ui'                    => true,
                'show_admin_column'          => true,
                'show_in_nav_menus'          => true,
				'show_tagcloud'              => true,
				'capabilities'               => $capabilities,
            );

            register_taxonomy( $this->get_tax_names('singular'), 'product', $args );
            register_taxonomy_for_object_type( $this->get_tax_names('singular'), 'product' );

        }

        /**
         * Hide unused fields from admin
         */
        public function hideFields()
        {
            echo '<style>.term-description-wrap, .term-parent-wrap { display:none; } </style>';
        }

        /**
         * Change columns displayed in table
         *
         * @param $columns
         *
         * @return mixed
         */
        public function editColumns($columns) {
            if(isset($columns['description'])) {
                unset($columns['description']);
            }

            return $columns;
        }

        /**
         * Form fields
         *
         * @param $tag
         */
        public function formFields($tag) {
            // Defaults
            $view = 'taxonomy-fields-new';
            $primary_location = 0;
            $auto_order_allocate = 0;
            $auto_order_allocate_priority = 0;

            // Is edit screen
            if (is_object($tag)) {
                $view = 'taxonomy-fields-edit';
                $primary_location = get_term_meta($tag->term_id, 'slw_primary_location', true);
                $auto_order_allocate = get_term_meta($tag->term_id, 'slw_auto_order_allocate', true);
                $auto_order_allocate_priority = get_term_meta($tag->term_id, 'slw_auto_order_allocate_priority', true);
            }

            // Echo view
            echo view($view, [
                'primary_location' => $primary_location,
                'auto_order_allocate' => $auto_order_allocate,
                'auto_order_allocate_priority' => $auto_order_allocate_priority
            ]);
        }

        /**
         * Save term meta
         *
         * @param $term_id
         */
        public function formSave($term_id) {
            if ($_POST && isset($_POST['auto_order_allocate']) && isset($_POST['auto_order_allocate']) && isset($_POST['auto_order_allocate_priority'])) {
                update_term_meta($term_id, 'slw_primary_location', $_POST['primary_location']);
                update_term_meta($term_id, 'slw_auto_order_allocate', $_POST['auto_order_allocate']);
                update_term_meta($term_id, 'slw_auto_order_allocate_priority', $_POST['auto_order_allocate_priority']);
            }
        }

    }

}
