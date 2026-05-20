<?php
defined('ABSPATH') || exit;

if (!class_exists('JobPortal_Footer')) {

    class JobPortal_Footer
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
            add_action('init', array($this, 'register_footer'));
            add_post_type_support('jobportal_footer', 'elementor');
			add_action('wp_footer', array($this, 'render_back_to_top'));
        }

        /**
         * Register Footer Post Type
         */
        function register_footer()
        {
            $labels = array(
                'name' => __('Footer', 'jobportal-framework'),
                'singular_name' => __('Footer', 'jobportal-framework'),
                'add_new' => __('Add New', 'jobportal-framework'),
                'add_new_item' => __('Add New', 'jobportal-framework'),
                'edit_item' => __('Edit Footer', 'jobportal-framework'),
                'new_item' => __('Add New Footer', 'jobportal-framework'),
                'view_item' => __('View Footer', 'jobportal-framework'),
                'search_items' => __('Search Footer', 'jobportal-framework'),
                'not_found' => __('No items found', 'jobportal-framework'),
                'not_found_in_trash' => __('No items found in trash', 'jobportal-framework'),
            );

            $args = array(
                'menu_icon' => 'dashicons-arrow-down-alt',
                'label' => esc_html__('Footer', 'jobportal-framework'),
                'description' => esc_html__('Footer', 'jobportal-framework'),
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
            register_post_type('jobportal_footer', $args);
        }

		public function render_back_to_top(){
			if (jobportal_get_option('enable_back_top') != '1') {
				return;
			}
			?>
			<div id="back-to-top" class="back-to-top">
				<a href="#" class="back-top">
					<i class="far fa-chevron-up"></i>
				</a>
			</div>
			<?php
		}
    }

    JobPortal_Footer::instance()->initialize();
}
