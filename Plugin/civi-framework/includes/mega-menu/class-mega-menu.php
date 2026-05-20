<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Civi_Mega_Menu' ) ) {
    class Civi_Mega_Menu {

        protected static $instance = null;

        static function instance() {
            if ( null === self::$instance ) {
                self::$instance = new self();
            }

            return self:: $instance;
        }

        public function initialize() {
            add_action( 'init', array( $this, 'register_mega_menu') );
            add_post_type_support( 'civi_mega_menu', 'elementor' );
        }

        /**
         * Register Mega_Menu Post Type
         */
        function register_mega_menu() {

            			$labels = array(
				'name'               => _x( 'Mega Menus', 'Post Type General Name', 'civi-framework' ),
				'singular_name'      => _x( 'Mega Menus', 'Post Type Singular Name', 'civi-framework' ),
				'menu_name'          => esc_html__( 'Mega Menu', 'civi-framework' ),
				'name_admin_bar'     => esc_html__( 'Mega Menu', 'civi-framework' ),
				'parent_item_colon'  => esc_html__( 'Parent Menu:', 'civi-framework' ),
				'all_items'          => esc_html__( 'Mega Menus', 'civi-framework' ),
				'add_new_item'       => esc_html__( 'Add New Menu', 'civi-framework' ),
				'add_new'            => esc_html__( 'Add New', 'civi-framework' ),
				'new_item'           => esc_html__( 'New Menu', 'civi-framework' ),
				'edit_item'          => esc_html__( 'Edit Menu', 'civi-framework' ),
				'update_item'        => esc_html__( 'Update Menu', 'civi-framework' ),
				'view_item'          => esc_html__( 'View Menu', 'civi-framework' ),
				'search_items'       => esc_html__( 'Search Menu', 'civi-framework' ),
				'not_found'          => esc_html__( 'Not found', 'civi-framework' ),
				'not_found_in_trash' => esc_html__( 'Not found in Trash', 'civi-framework' ),
			);

            			$args = array(
				'label'       => esc_html__( 'Mega Menus', 'civi-framework' ),
				'description' => esc_html__( 'Mega Menus', 'civi-framework' ),
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

            register_post_type( 'civi_mega_menu', $args );

        }
    }

    Civi_Mega_Menu:: instance()->initialize();
}
