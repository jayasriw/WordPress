<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Civi_Search')) {
    class Civi_Search
    {
        private static $instance = null;
        private $supported_post_types = array('jobs', 'company', 'candidate', 'service');
        private $allowed_search_post_types = array('post', 'page', 'jobs', 'company', 'candidate', 'service');
        private static $fulltext_index_exists = null;

        public static function instance()
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct()
        {
            // Hook FULLTEXT filter
            add_filter('posts_search', array($this, 'posts_search_filter'), 500, 2);

            // Filter search post types for security
            add_action('pre_get_posts', array($this, 'filter_search_post_types'));

            // Admin notice create FULLTEXT index
            add_action('admin_notices', array($this, 'fulltext_index_notice'));
            add_action('wp_ajax_civi_create_fulltext_index', array($this, 'ajax_create_fulltext_index'));
            add_action('wp_ajax_civi_dismiss_fulltext_notice', array($this, 'ajax_dismiss_fulltext_notice'));

            // Enqueue admin script for ajax actions
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

            // Hook into archive template filters for initial page load optimization
            add_filter('civi/archive-jobs/layout-default/query/args', array(__CLASS__, 'optimize_args'), 10, 1);
            add_filter('civi/archive-service/layout-default/query/args', array(__CLASS__, 'optimize_args'), 10, 1);
            add_filter('civi/archive-candidate/layout-default/query/args', array(__CLASS__, 'optimize_args'), 10, 1);
            add_filter('civi/archive-company/layout-default/query/args', array(__CLASS__, 'optimize_args'), 10, 1);
        }

        public function enqueue_admin_scripts($hook)
        {
            if (strpos($hook, 'civi_page_civi-setup') === false && strpos($hook, 'theme-options') === false) {
                // return; // Or check specific pages
            }
            wp_enqueue_script('civi-admin-search', CIVI_PLUGIN_URL . 'assets/js/admin/civi-search.js', array('jquery'), CIVI_THEME_VERSION, true);
            wp_localize_script('civi-admin-search', 'civi_search_vars', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce_create_index' => wp_create_nonce('civi_create_fulltext_index'),
                'nonce_dismiss_notice' => wp_create_nonce('civi_dismiss_fulltext_notice'),
            ));
        }

        /**
         * Detect search plugin active
         */
        public static function get_active_search_plugin()
        {
            if (function_exists('relevanssi_do_query')) {
                return 'relevanssi';
            }
            if (function_exists('ep_is_activated') && ep_is_activated()) {
                return 'elasticpress';
            }
            if (class_exists('ElasticPress\Elasticsearch')) {
                return 'elasticpress';
            }
            if (class_exists('SearchWP') || function_exists('searchwp_get_engine')) {
                return 'searchwp';
            }
            return false;
        }

        /**
         * Check FULLTEXT enabled
         */
        public static function is_fulltext_enabled()
        {
            if (defined('CIVI_ENABLE_FULLTEXT_SEARCH') && CIVI_ENABLE_FULLTEXT_SEARCH) {
                return true;
            }
            $option = civi_get_option('enable_fulltext_search', '0');
            return $option === '1';
        }

        /**
         * Check FULLTEXT index exists
         */
        public static function fulltext_index_exists()
        {
            if (self::$fulltext_index_exists !== null) {
                return self::$fulltext_index_exists;
            }
            global $wpdb;
            $indexes = $wpdb->get_results(
                "SHOW INDEX FROM {$wpdb->posts} WHERE Index_type = 'FULLTEXT'"
            );

            if (empty($indexes)) {
                self::$fulltext_index_exists = false;
                return false;
            }

            $indexed_columns = array();
            foreach ($indexes as $index) {
                $indexed_columns[$index->Key_name][] = $index->Column_name;
            }

            foreach ($indexed_columns as $key_name => $columns) {
                if (in_array('post_title', $columns) && in_array('post_content', $columns)) {
                    self::$fulltext_index_exists = true;
                    return true;
                }
            }

            self::$fulltext_index_exists = false;
            return false;
        }

        /**
         * Optimize query args
         */
        public static function optimize_args($args)
        {
            if (empty($args['s'])) {
                return $args;
            }

            $search_plugin = self::get_active_search_plugin();
            switch ($search_plugin) {
                case 'relevanssi':
                    $args['relevanssi'] = true;
                    break;
                case 'elasticpress':
                    $args['ep_integrate'] = true;
                    break;
                // case 'searchwp':
                //     // SearchWP automatically integrates with WP_Query when suppress_filters is false.
                //     // Passing 'searchwp' => true can sometimes be interpreted as an invalid engine name.
                //     break;
                default:
                    $args['civi_optimize_search'] = true;
                    break;
            }

            // Important for some plugins or standard WP_Query filters
            $args['suppress_filters'] = false;

            return $args;
        }

        /**
         * Filter posts_search to use FULLTEXT
         */
        public function posts_search_filter($search, $wp_query)
        {
            if (empty($search)) return $search;

            $search_term = $wp_query->get('s');
            if (empty($search_term)) return $search;

            // If plugin active -> let plugin handle it
            if (self::get_active_search_plugin()) return $search;

            // Check post type
            $post_type = $wp_query->get('post_type');

            // Support multiple post types in query
            $is_supported = false;
            if (is_array($post_type)) {
                $is_supported = !empty(array_intersect($post_type, $this->supported_post_types));
            } else {
                $is_supported = in_array($post_type, $this->supported_post_types);
            }

            if (!$is_supported && empty($wp_query->get('civi_optimize_search'))) {
                return $search;
            }

            if (!self::is_fulltext_enabled() || !self::fulltext_index_exists()) {
                return $search;
            }

            // Fallback to standard search (LIKE) for short terms (< 4 chars)
            // MySQL FULLTEXT often ignores words < 4 chars (MyISAM) or < 3 chars (InnoDB)
            // This ensures users still get results for "IT", "HR", "Go", etc.
            if (strlen(trim($search_term)) < 4) {
                return $search;
            }

            // Build FULLTEXT query
            global $wpdb;
            $prepared_term = $this->prepare_fulltext_term($search_term);

            if (empty($prepared_term)) return $search;

            return $wpdb->prepare(
                " AND MATCH({$wpdb->posts}.post_title, {$wpdb->posts}.post_content) AGAINST(%s IN BOOLEAN MODE) ",
                $prepared_term
            );
        }

        /**
         * Filter search post types for security
         */
        public function filter_search_post_types($query)
        {
            if (is_admin() || !$query->is_main_query() || !$query->is_search()) {
                return;
            }

            // Edge Case: REST API
            if (defined('REST_REQUEST') && REST_REQUEST) {
                return;
            }

            // Edge Case: Bypass Flag
            if ($query->get('civi_bypass_search_filter')) {
                return;
            }

            // Edge Case: AJAX with specific post_type
            if (defined('DOING_AJAX') && DOING_AJAX && $query->get('post_type')) {
                return;
            }

            // Set allowed post types
            $query->set('post_type', $this->allowed_search_post_types);
        }

        /**
         * Prepare search term for FULLTEXT
         */
        private function prepare_fulltext_term($term)
        {
            $term = trim($term);
            if (strlen($term) > 200) {
                $term = substr($term, 0, 200);
            }

            // Remove special chars that might break Boolean mode syntax logic
            // Keep spaces, alphanumeric, and maybe - or + if user intends to use them?
            // For safety, let's treat it as simple keywords.
            $term = preg_replace('/[+\-><\(\)~*\"@]+/', ' ', $term);

            $words = preg_split('/\s+/', $term, -1, PREG_SPLIT_NO_EMPTY);
            if (empty($words)) return '';

            $prepared_words = array();
            foreach ($words as $word) {
                $word = trim($word);
                if (strlen($word) >= 2) {
                    $prepared_words[] = '+' . $word . '*';
                }
            }

            return implode(' ', $prepared_words);
        }

        /**
         * Admin notice for FULLTEXT index
         */
        public function fulltext_index_notice()
        {
            if (!self::is_fulltext_enabled()) {
                return;
            }

            if (self::fulltext_index_exists()) {
                return;
            }

            $dismissed = get_option('civi_dismiss_fulltext_notice');
            if ($dismissed) {
                return;
            }

            $class = 'notice notice-warning is-dismissible civi-fulltext-notice';
            $message = esc_html__('Civi Theme: You have enabled FULLTEXT search but the database index is missing. Please create the index to improve search performance.', 'civi-framework');
            $btn_text = esc_html__('Create Index Now', 'civi-framework');

            printf(
                '<div class="%1$s"><p>%2$s</p><p><button id="civi-create-fulltext-index" class="button button-primary">%3$s</button> <span class="spinner" style="float:none"></span></p></div>',
                esc_attr($class),
                esc_html($message),
                esc_html($btn_text)
            );
        }

        /**
         * Ajax create FULLTEXT index
         */
        public function ajax_create_fulltext_index()
        {
            check_ajax_referer('civi_create_fulltext_index', 'security');

            if (!current_user_can('manage_options')) {
                wp_send_json_error(array('message' => esc_html__('Permission denied.', 'civi-framework')));
            }

            global $wpdb;
            $table = $wpdb->posts;
            $index_name = 'civi_fulltext_idx';

            // Check if index exists again to be safe
            if (self::fulltext_index_exists()) {
                wp_send_json_success(array('message' => esc_html__('Index already exists.', 'civi-framework')));
            }

            // Create index
            // Note: This operation can be slow on large tables
            $sql = "ALTER TABLE $table ADD FULLTEXT INDEX $index_name (post_title, post_content)";

            if ($wpdb->query($sql) === false) {
                wp_send_json_error(array('message' => esc_html__('Failed to create index: ', 'civi-framework') . $wpdb->last_error));
            }

            self::$fulltext_index_exists = true;

            wp_send_json_success(array('message' => esc_html__('Index created successfully!', 'civi-framework')));
        }

        /**
         * Ajax dismiss notice
         */
        public function ajax_dismiss_fulltext_notice()
        {
            check_ajax_referer('civi_dismiss_fulltext_notice', 'security');
            update_option('civi_dismiss_fulltext_notice', 1);
            wp_send_json_success();
        }

        /**
         * Get status for debugging
         */
        public static function get_status()
        {
            return array(
                'active_plugin'       => self::get_active_search_plugin() ?: 'none',
                'fulltext_enabled'    => self::is_fulltext_enabled(),
                'fulltext_index'      => self::fulltext_index_exists(),
                'optimization_active' => self::get_active_search_plugin() || (self::is_fulltext_enabled() && self::fulltext_index_exists()),
            );
        }
    }
}
