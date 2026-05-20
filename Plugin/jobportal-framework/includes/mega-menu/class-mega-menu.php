<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'JobPortal_Mega_Menu' ) ) {
    class JobPortal_Mega_Menu {

        protected static $instance = null;

        static function instance() {
            if ( null === self::$instance ) {
                self::$instance = new self();
            }

            return self:: $instance;
        }

        public function initialize() {
            add_action( 'init', array( $this, 'register_mega_menu') );
            add_post_type_support( 'jobportal_mega_menu', 'elementor' );
        }

        /**
         * Register Mega_Menu Post Type
         */
        function register_mega_menu() {

            			$labels = array(
				'name'               => _x( 'Mega Menus', 'Post Type General Name', 'jobportal-framework' ),
				'singular_name'      => _x( 'Mega Menus', 'Post Type Singular Name', 'jobportal-framework' ),
				'menu_name'          => esc_html__( 'Mega Menu', 'jobportal-framework' ),
				'name_admin_bar'     => esc_html__( 'Mega Menu', 'jobportal-framework' ),
				'parent_item_colon'  => esc_html__( 'Parent Menu:', 'jobportal-framework' ),
				'all_items'          => esc_html__( 'Mega Menus', 'jobportal-framework' ),
				'add_new_item'       => esc_html__( 'Add New Menu', 'jobportal-framework' ),
				'add_new'            => esc_html__( 'Add New', 'jobportal-framework' ),
				'new_item'           => esc_html__( 'New Menu', 'jobportal-framework' ),
				'edit_item'          => esc_html__( 'Edit Menu', 'jobportal-framework' ),
				'update_item'        => esc_html__( 'Update Menu', 'jobportal-framework' ),
				'view_item'          => esc_html__( 'View Menu', 'jobportal-framework' ),
				'search_items'       => esc_html__( 'Search Menu', 'jobportal-framework' ),
				'not_found'          => esc_html__( 'Not found', 'jobportal-framework' ),
				'not_found_in_trash' => esc_html__( 'Not found in Trash', 'jobportal-framework' ),
			);

            			$args = array(
				'label'       => esc_html__( 'Mega Menus', 'jobportal-framework' ),
				'description' => esc_html__( 'Mega Menus', 'jobportal-framework' ),
                'labels'      => $labels,
                'supports'    => array(
                    'title',
                    'editor',
                    'revisions',
                    'elementor',
                ),
                'hierarchical'        => false,
                'public'              => true,
                'menu_position'       => 14,
                'menu_icon'           => 'dashicons-menu-alt',
                'show_in_admin_bar'   => true,
                'show_in_nav_menus'   => true,
                'can_export'          => true,
                'has_archive'         => false,
                'exclude_from_search' => true,
                'publicly_queryable'  => false,
                'rewrite'             => false,
                'capability_type'     => 'page',
                'publicly_queryable'  => true, // Enable TRUE for Elementor Editing
            );

            register_post_type( 'jobportal_mega_menu', $args );

        }
    }

    JobPortal_Mega_Menu:: instance()->initialize();
}
