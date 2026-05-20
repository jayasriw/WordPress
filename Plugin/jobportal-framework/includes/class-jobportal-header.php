<?php
defined('ABSPATH') || exit;

if (!class_exists('JobPortal_Header')) {

    class JobPortal_Header
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
            add_post_type_support('jobportal_header', 'elementor');
        }

        /**
         * Register Header Post Type
         */
        function register_header()
        {
            $labels = array(
                'name' => __('Header', 'jobportal-framework'),
                'singular_name' => __('Header', 'jobportal-framework'),
                'add_new' => __('Add New', 'jobportal-framework'),
                'add_new_item' => __('Add New', 'jobportal-framework'),
                'edit_item' => __('Edit Header', 'jobportal-framework'),
                'new_item' => __('Add New Header', 'jobportal-framework'),
                'view_item' => __('View Header', 'jobportal-framework'),
                'search_items' => __('Search Header', 'jobportal-framework'),
                'not_found' => __('No items found', 'jobportal-framework'),
                'not_found_in_trash' => __('No items found in trash', 'jobportal-framework'),
            );

            $args = array(
                'menu_icon' => 'dashicons-buddicons-topics',
                'label' => esc_html__('Header', 'jobportal-framework'),
                'description' => esc_html__('Header', 'jobportal-framework'),
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
            register_post_type('jobportal_header', $args);
        }
    }

    JobPortal_Header::instance()->initialize();
}
