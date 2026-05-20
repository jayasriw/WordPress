<?php
defined('ABSPATH') || exit;

if (!class_exists('Civi_Header')) {

    class Civi_Header
    {

        protected static $instance = null;

        public static function instance()
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        public function initialize()
        {
            add_action('init', array($this, 'register_header'));
            add_post_type_support('civi_header', 'elementor');
        }

        /**
         * Register Header Post Type
         */
        function register_header()
        {
            $labels = array(
                'name' => __('Header', 'civi-framework'),
                'singular_name' => __('Header', 'civi-framework'),
                'add_new' => __('Add New', 'civi-framework'),
                'add_new_item' => __('Add New', 'civi-framework'),
                'edit_item' => __('Edit Header', 'civi-framework'),
                'new_item' => __('Add New Header', 'civi-framework'),
                'view_item' => __('View Header', 'civi-framework'),
                'search_items' => __('Search Header', 'civi-framework'),
                'not_found' => __('No items found', 'civi-framework'),
                'not_found_in_trash' => __('No items found in trash', 'civi-framework'),
            );

            $args = array(
                'menu_icon' => 'dashicons-buddicons-topics',
                'label' => esc_html__('Header', 'civi-framework'),
                'description' => esc_html__('Header', 'civi-framework'),
                'labels' => $labels,
                'supports' => array(
                    'title',
                    'editor',
                    'revisions',
                    'elementor',
                ),
                'hierarchical' => false,
                'public' => true,
                'menu_position' => 15,
                'show_in_admin_bar' => true,
                'show_in_nav_menus' => true,
                'can_export' => true,
                'has_archive' => false,
                'exclude_from_search' => true,
                'publicly_queryable' => false,
                'rewrite' => false,
                'capability_type' => 'page',
                'publicly_queryable' => true, // Enable TRUE for Elementor Editing
            );
            register_post_type('civi_header', $args);
        }
    }

    Civi_Header::instance()->initialize();
}
