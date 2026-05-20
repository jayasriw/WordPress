<?php

/**
 * This file define demos for the theme using class-based structure.
 */

class JobPortal_Demo_Importer
{
    /**
     * Cleanup task delay constants (in seconds)
     */
    const CLEANUP_DELAY_BASIC = 5;
    const CLEANUP_DELAY_FULL = 30;
    const CLEANUP_DELAY_STANDARD = 5;

    /**
     * Import resource limits
     */
    const MEMORY_LIMIT_IMPORT = '512M';
    const MEMORY_LIMIT_THRESHOLD = 512;
    const MAX_EXECUTION_TIME = 300;
    const MAX_INPUT_VARS = '3000';


    /**
     * Constructor - Register all WordPress hooks and filters
     *
     * Registers hooks for:
     * - Import file definitions (OCDI)
     * - Pre/post import actions
     * - Media upload handling
     * - Scheduled cleanup tasks
     * - Icon fixes
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        add_filter('ocdi/import_files', [$this, 'getImportFiles']);
        add_filter('ocdi/plugin_intro_text', [$this, 'getPluginIntroText']);
        add_action('ocdi/after_import', [$this, 'afterImportSetup']);

        add_action('ocdi/before_content_import', [$this, 'before_content_import']);
        add_action('ocdi/after_content_import', [$this, 'after_content_import']);

        add_filter('ocdi/pre_download_import_file', [$this, 'validate_import_file'], 10, 2);
        add_filter('ocdi/regenerate_thumbnails_in_content_import', '__return_false');

        add_action('admin_init', [$this, 'ensure_service_enabled_on_import']);

        add_action('jobportal_freelancer_basic_cleanup', [$this, 'run_freelancer_basic_cleanup']);
        add_action('jobportal_freelancer_full_cleanup', [$this, 'run_freelancer_full_cleanup']);
        add_action('jobportal_standard_cleanup', [$this, 'run_standard_cleanup']);

        add_action('ocdi/before_content_import', [$this, 'prepare_media_import']);
        add_filter('upload_mimes', [$this, 'add_custom_mime_types']);
        add_filter('wp_handle_upload_prefilter', [$this, 'handle_upload_errors']);

        add_action('init', [$this, 'fix_fontawesome_icons']);
        add_filter('elementor/icons_manager/additional_tabs', [$this, 'add_missing_fontawesome_icons']);
    }


    /**
     * Import theme options from JSON file
     *
     * Loads theme options from JSON file, cleans URLs, and merges with existing options.
     *
     * @since 1.0.0
     * @param array $selected_import Import configuration array containing 'theme_options_file' key
     * @return void
     */
    public function import_theme_options(array $selected_import): void
    {
        if (!empty($selected_import['theme_options_file'])) {
            $file = $selected_import['theme_options_file'];

            if (file_exists($file)) {
                $json = file_get_contents($file);
                $data = json_decode($json, true);

                if (is_array($data)) {
                    $cleaned_data = $this->clean_theme_options_urls($data, $selected_import);
                    $existing_options = get_option(JOBPORTAL_OPTIONS_NAME, array());
                    $merged_options = array_merge($existing_options, $cleaned_data);
                    update_option(JOBPORTAL_OPTIONS_NAME, $merged_options);
                }
            }
        }
    }


    private function clean_theme_options_urls($data, $selected_import)
    {
        if (!is_array($data)) {
            return $data;
        }

        $new_domain = untrailingslashit(home_url());
        $old_domain = untrailingslashit($this->jobportal_get_old_domain($selected_import));

        $cleanup_patterns = [];

        if ($selected_import['import_file_name'] === 'JobPortal Freelancer') {
            $cleanup_patterns = [
                $old_domain => str_replace('/freelance', '', $old_domain),
                '/wp-content/uploads/sites/2/' => '/wp-content/uploads/',
                '/version-2-0/' => '/',
            ];
        } else {
            $cleanup_patterns = [
                $old_domain => $new_domain
            ];
        }

        $cleaned_data = $this->recursive_clean_urls($data, $cleanup_patterns);

        return $cleaned_data;
    }


    private function recursive_clean_urls($data, $patterns)
    {
        if (is_string($data)) {
            // Apply all cleanup patterns
            foreach ($patterns as $old_pattern => $new_pattern) {
                $data = str_replace($old_pattern, $new_pattern, $data);
            }
            return $data;
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->recursive_clean_urls($value, $patterns);
            }
            return $data;
        }

        if (is_object($data)) {
            foreach ($data as $key => $value) {
                $data->$key = $this->recursive_clean_urls($value, $patterns);
            }
            return $data;
        }

        return $data;
    }


    /**
     * Define available demo import files
     *
     * Returns configuration for One Click Demo Import (OCDI) plugin.
     * Currently supports:
     * - JobPortal Jobs Board (main demo)
     * - JobPortal Freelancer (alternative demo)
     *
     * @since 1.0.0
     * @return array[] Array of import file configurations
     */
    public function getImportFiles(): array
    {
        $import_files = array(
            array(
                'import_file_name'              => 'JobPortal Jobs Board',
                'local_import_file'             => plugin_dir_path(__DIR__) . 'assets/import/01/content.xml',
                'local_import_widget_file'      => plugin_dir_path(__DIR__) . 'assets/import/01/widgets.json',
                'local_import_customizer_file'  => plugin_dir_path(__DIR__) . 'assets/import/01/customizer.dat',
                'import_preview_image_url'      => plugins_url('assets/import/01/screenshot.png', dirname(__FILE__)),
                'import_notice'                 => __('After importing this demo, please go to Settings > Permalinks and click "Save Changes" to update your permalink structure.', 'jobportal-framework'),
                'preview_url' => 'https://jobportal.example.com/',
                'theme_options_file'           => plugin_dir_path(__DIR__) . 'assets/import/01/theme-options.json',
            ),
            array(
                'import_file_name'              => 'JobPortal Freelancer',
                'local_import_file'             => plugin_dir_path(__DIR__) . 'assets/import/02/content.xml',
                'local_import_customizer_file'  => plugin_dir_path(__DIR__) . 'assets/import/02/customizer.dat',
                'import_preview_image_url'      => plugins_url('assets/import/02/screenshot.png', dirname(__FILE__)),
                'import_notice'                 => __('After importing this demo, please go to Settings > Permalinks and click "Save Changes" to update your permalink structure.', 'jobportal-framework'),
                'preview_url' => 'https://jobportal.example.com/',
                'theme_options_file'           => plugin_dir_path(__DIR__) . 'assets/import/02/theme-options.json',
            ),
        );


        return $import_files;
    }


    public function getPluginIntroText($default_text)
    {
        $default_text = '
        <div class="ocdi__intro-text" style="margin-bottom: 30px;">
            <div class="jobportal-box jobportal-box--orange jobportal-box--import-notes">
                <div class="jobportal-box__header">
                    <span class="jobportal-box__icon"><i class="fad fa-comment-exclamation"></i></span>
                    <h3>Important Notes</h3>
                </div>
                <div class="jobportal-box__body">
                    <ol>
                        <li>
                            No existing posts, pages, categories, images, widgets or any other data will be deleted or modified, but we recommend installing demo data on a clean WordPress website to prevent conflicts with your current content.<br>
                            To reset your website before importing, use
                            <a href="https://jobportal.example.com/wp-admin/plugin-install.php?tab=plugin-information&amp;plugin=wordpress-reset&amp;TB_iframe=true&amp;width=800&amp;height=550" class="thickbox" title="Install WordPress Reset">WordPress Reset</a> plugin.
                        </li>
                        <li><strong>All required plugins</strong> should be installed.</li>
                        <li>
                            Posts, pages, images, widgets, menus and more data will get imported.<br>
                            Please click on the "Import" button only once and wait until the process is completed, it may take a while.
                        </li>
                    </ol>
                </div>
            </div>
        </div>';

        return $default_text;
    }


    private function setElementorSettings($selected_import)
    {
        $elementor_settings = '';
        if (!empty($selected_import['import_file_name'])) {
            $import_file_name = $selected_import['import_file_name'];

            if ($import_file_name == 'JobPortal Jobs Board') {
                $elementor_settings = plugin_dir_path(__DIR__) . 'assets/import/01/elementor.json';
            } elseif ($import_file_name == 'JobPortal Freelancer') {
                $elementor_settings = plugin_dir_path(__DIR__) . 'assets/import/02/elementor.json';
            }
        }
        if (empty($elementor_settings)) {
            return;
        }
        $this->import_elementor_site_settings_from_json($elementor_settings);
    }

    private function import_elementor_site_settings_from_json($json_file_path)
    {
        if (!class_exists('Elementor\Plugin')) {
            return false;
        }

        if (!file_exists($json_file_path)) {
            return false;
        }

        $json_data = file_get_contents($json_file_path);
        $data = json_decode($json_data, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        $this->import_elementor_global_options($data);

        if (isset($data['kit_settings'])) {
            $this->import_elementor_kit_settings($data['kit_settings']);
        }

        return true;
    }

    /**
     * Import global Elementor options
     */
    private function import_elementor_global_options($data)
    {
        $global_options = [
            'elementor_cpt_support',
            'elementor_disable_typography_schemes',
            'elementor_unfiltered_files_upload',
            'elementor_scheme_color',
            'elementor_scheme_typography',
            'elementor_scheme_color-picker'
        ];

        foreach ($global_options as $option) {
            if (isset($data[$option])) {
                update_option($option, $data[$option]);
            }
        }

        // Note: We don't import elementor_active_kit from JSON as we use the current site's kit
    }

    /**
     * Import Elementor Kit settings (Theme Style settings)
     */
    private function import_elementor_kit_settings($kit_settings)
    {
        $kit_id = false;
        if (class_exists('\Elementor\Plugin') && isset(\Elementor\Plugin::$instance) && isset(\Elementor\Plugin::$instance->kits_manager)) {
            $kit_id = \Elementor\Plugin::$instance->kits_manager->get_active_id();
        }

        if (!$kit_id) {
            $kit_id = $this->get_or_create_elementor_kit();
            if (!$kit_id) {
                return false;
            }
        }

        // Get existing settings and merge with new ones
        $existing_settings = get_post_meta($kit_id, '_elementor_page_settings', true);
        if (!is_array($existing_settings)) {
            $existing_settings = [];
        }

        // Merge settings (new settings override existing ones)
        $merged_settings = array_merge($existing_settings, $kit_settings);

        // Update kit settings
        update_post_meta($kit_id, '_elementor_page_settings', $merged_settings);

        // Clear cache and regenerate
        $this->refresh_elementor_cache();

        // Log for debugging


        return true;
    }

    /**
     * Get or create Elementor Kit
     * Priority: current active kit > existing kit > create new kit
     */
    private function get_or_create_elementor_kit()
    {
        // First try to get current active kit
        $current_kit_id = get_option('elementor_active_kit');
        if ($current_kit_id && get_post($current_kit_id) && get_post_meta($current_kit_id, '_elementor_template_type', true) === 'kit') {
            return $current_kit_id;
        }

        // Find any existing kit
        $existing_kits = get_posts([
            'post_type' => 'elementor_library',
            'meta_key' => '_elementor_template_type',
            'meta_value' => 'kit',
            'posts_per_page' => 1,
            'post_status' => 'publish'
        ]);

        if (!empty($existing_kits)) {
            $kit_id = $existing_kits[0]->ID;
            update_option('elementor_active_kit', $kit_id);
            return $kit_id;
        }

        // Create new kit if none exists
        $kit_id = wp_insert_post([
            'post_title' => 'Default Kit',
            'post_status' => 'publish',
            'post_type' => 'elementor_library',
            'meta_input' => [
                '_elementor_template_type' => 'kit'
            ]
        ]);

        if (!$kit_id || is_wp_error($kit_id)) {
            return false;
        }

        update_option('elementor_active_kit', $kit_id);
        return $kit_id;
    }

    /**
     * Clear Elementor cache and regenerate CSS
     */
    private function refresh_elementor_cache()
    {
        if (class_exists('\Elementor\Plugin') && isset(\Elementor\Plugin::$instance)) {
            if (isset(\Elementor\Plugin::$instance->files_manager)) {
                \Elementor\Plugin::$instance->files_manager->clear_cache();
            }
            if (isset(\Elementor\Plugin::$instance->posts_css_manager)) {
                \Elementor\Plugin::$instance->posts_css_manager->clear_cache();
            }
            if (isset(\Elementor\Plugin::$instance->kits_manager) && method_exists(\Elementor\Plugin::$instance->kits_manager, 'refresh_kit_data')) {
                \Elementor\Plugin::$instance->kits_manager->refresh_kit_data();
            }
        }
    }

    /**
     * Get page by title (replacement for deprecated get_page_by_title)
     * Uses WP_Query with title parameter for better compatibility
     */
    private function get_page_by_title_safe($page_title, $output = OBJECT)
    {
        // Try WP_Query with title parameter first (WP 6.2+)
        $query = new WP_Query([
            'post_type' => 'page',
            'title' => $page_title,
            'post_status' => ['publish', 'draft', 'private'],
            'posts_per_page' => 1,
            'no_found_rows' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false
        ]);

        if ($query->have_posts()) {
            $page = $query->posts[0];
            wp_reset_postdata();

            if ($output == OBJECT) {
                return $page;
            } elseif ($output == ARRAY_A) {
                return (array) $page;
            } elseif ($output == ARRAY_N) {
                return array_values((array) $page);
            }
        }

        // Fallback: Use post_title search if title parameter doesn't work
        $query = new WP_Query([
            'post_type' => 'page',
            'post_status' => ['publish', 'draft', 'private'],
            'posts_per_page' => 1,
            'no_found_rows' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            's' => $page_title
        ]);

        if ($query->have_posts()) {
            foreach ($query->posts as $post) {
                if ($post->post_title === $page_title) {
                    wp_reset_postdata();
                    if ($output == OBJECT) {
                        return $post;
                    } elseif ($output == ARRAY_A) {
                        return (array) $post;
                    } elseif ($output == ARRAY_N) {
                        return array_values((array) $post);
                    }
                }
            }
            wp_reset_postdata();
        }

        return null;
    }

    /**
     * Set up pages configuration
     */
    private function setPages($selected_import)
    {
        $front_page = $this->get_page_by_title_safe('Home 01');
        if ($selected_import['import_file_name'] == 'JobPortal Freelancer') {
            $front_page = $this->get_page_by_title_safe('Freelance 01');
        }
        $blog_page = $this->get_page_by_title_safe('Blogs');

        if ($front_page && $blog_page) {
            update_option('show_on_front', 'page');
            update_option('page_on_front', $front_page->ID);
            update_option('page_for_posts', $blog_page->ID);
        }

        $pages = [
            // Common Pages
            'terms_condition'                   => ($page = $this->get_page_by_title_safe('Terms & Conditions')) ? $page->ID : 0,
            'privacy_policy'                    => ($page = $this->get_page_by_title_safe('Privacy Policy')) ? $page->ID : 0,
            'jobportal_update_profile_page_id'       => ($page = $this->get_page_by_title_safe('Profile')) ? $page->ID : 0,
            'jobportal_add_jobs_page_id'             => ($page = $this->get_page_by_title_safe('Post Job')) ? $page->ID : 0,
            'jobportal_add_jobs_not_page_id'         => ($page = $this->get_page_by_title_safe('Employer Landing')) ? $page->ID : 0,

            // Employer Pages
            'jobportal_dashboard_page_id'                 => ($page = $this->get_page_by_title_safe('Dashboard Employers')) ? $page->ID : 0,
            'jobportal_jobs_dashboard_page_id'            => ($page = $this->get_page_by_title_safe('Job Employer')) ? $page->ID : 0,
            'jobportal_jobs_submit_page_id'               => ($page = $this->get_page_by_title_safe('Post Job')) ? $page->ID : 0,
            'jobportal_applicants_page_id'                => ($page = $this->get_page_by_title_safe('Applicants')) ? $page->ID : 0,
            'jobportal_candidates_page_id'                => ($page = $this->get_page_by_title_safe('Candidate')) ? $page->ID : 0,
            'jobportal_user_package_page_id'              => ($page = $this->get_page_by_title_safe('User Package')) ? $page->ID : 0,
            'jobportal_company_page_id'                   => ($page = $this->get_page_by_title_safe('Company Employer')) ? $page->ID : 0,
            'jobportal_submit_company_page_id'            => ($page = $this->get_page_by_title_safe('Submit Company')) ? $page->ID : 0,
            'jobportal_messages_page_id'                  => ($page = $this->get_page_by_title_safe('Messages')) ? $page->ID : 0,
            'jobportal_meetings_page_id'                  => ($page = $this->get_page_by_title_safe('Meetings Dashboard')) ? $page->ID : 0,
            'jobportal_settings_page_id'                  => ($page = $this->get_page_by_title_safe('Settings')) ? $page->ID : 0,
            'jobportal_package_page_id'                   => ($page = $this->get_page_by_title_safe('Packages')) ? $page->ID : 0,
            'jobportal_payment_page_id'                   => ($page = $this->get_page_by_title_safe('Payment')) ? $page->ID : 0,
            'jobportal_payment_completed_page_id'         => ($page = $this->get_page_by_title_safe('Payment Completed')) ? $page->ID : 0,

            // Candidate Pages
            'jobportal_candidate_dashboard_page_id'           => ($page = $this->get_page_by_title_safe('Dashboard Candidates')) ? $page->ID : 0,
            'jobportal_candidate_profile_page_id'             => ($page = $this->get_page_by_title_safe('Profile')) ? $page->ID : 0,
            'jobportal_my_jobs_page_id'                       => ($page = $this->get_page_by_title_safe('My Jobs')) ? $page->ID : 0,
            'jobportal_candidate_user_package_page_id'        => ($page = $this->get_page_by_title_safe('Candidate User Package')) ? $page->ID : 0,
            'jobportal_candidate_reviews_page_id'             => ($page = $this->get_page_by_title_safe('My Review')) ? $page->ID : 0,
            'jobportal_candidate_company_page_id'             => ($page = $this->get_page_by_title_safe('Company Candidate')) ? $page->ID : 0,
            'jobportal_candidate_messages_page_id'            => ($page = $this->get_page_by_title_safe('Message')) ? $page->ID : 0,
            'jobportal_candidate_meetings_page_id'            => ($page = $this->get_page_by_title_safe('Candidate Meetings')) ? $page->ID : 0,
            'jobportal_candidate_settings_page_id'            => ($page = $this->get_page_by_title_safe('Setting Candidate')) ? $page->ID : 0,
            'jobportal_candidate_package_page_id'             => ($page = $this->get_page_by_title_safe('Candidate Package')) ? $page->ID : 0,
            'jobportal_candidate_payment_page_id'             => ($page = $this->get_page_by_title_safe('Candidate Payment')) ? $page->ID : 0,
            'jobportal_candidate_payment_completed_page_id'   => ($page = $this->get_page_by_title_safe('Candidate Payment Completed')) ? $page->ID : 0,
        ];

        update_option(JOBPORTAL_OPTIONS_NAME, $pages);
    }

    /**
     * Set up menu locations với improved handling và verification
     */
    private function setMenus()
    {


        $all_menus = wp_get_nav_menus();

        foreach ($all_menus as $menu) {
        }

        $menu_locations = [];

        // Tìm Main Menu - thử nhiều cách
        $main_menu = null;
        $main_menu_names = ['Main Menu', 'main-menu', 'Primary Menu', 'Header Menu'];

        foreach ($main_menu_names as $menu_name) {
            $main_menu = get_term_by('name', $menu_name, 'nav_menu');
            if ($main_menu && !is_wp_error($main_menu)) {

                break;
            }
        }

        // Tìm Mobile Menu - thử nhiều cách
        $mobile_menu = null;
        $mobile_menu_names = ['Mobile Menu', 'mobile-menu', 'Mobile Navigation'];

        foreach ($mobile_menu_names as $menu_name) {
            $mobile_menu = get_term_by('name', $menu_name, 'nav_menu');
            if ($mobile_menu && !is_wp_error($mobile_menu)) {

                break;
            }
        }

        // Assign menu locations
        if ($main_menu && !is_wp_error($main_menu)) {
            $menu_locations['main_menu'] = $main_menu->term_id;
            $menu_locations['primary'] = $main_menu->term_id;
        }

        if ($mobile_menu && !is_wp_error($mobile_menu)) {
            $menu_locations['mobile_menu'] = $mobile_menu->term_id;
        }

        // Nếu không tìm thấy menu, thử tạo menu mặc định
        if (empty($menu_locations)) {
            $this->create_default_menus();

            // Thử lại sau khi tạo
            $main_menu = get_term_by('name', 'Main Menu', 'nav_menu');
            $mobile_menu = get_term_by('name', 'Mobile Menu', 'nav_menu');

            if ($main_menu && !is_wp_error($main_menu)) {
                $menu_locations['main_menu'] = $main_menu->term_id;
                $menu_locations['primary'] = $main_menu->term_id;
            }

            if ($mobile_menu && !is_wp_error($mobile_menu)) {
                $menu_locations['mobile_menu'] = $mobile_menu->term_id;
            }
        }

        if (!empty($menu_locations)) {
            // Sử dụng both set_theme_mod và wp_customize_manager để đảm bảo persist
            set_theme_mod('nav_menu_locations', $menu_locations);

            // Alternative method: Update option directly
            update_option('theme_mods_' . get_option('stylesheet'), array_merge(
                get_option('theme_mods_' . get_option('stylesheet'), array()),
                array('nav_menu_locations' => $menu_locations)
            ));
        }
    }

    private function simple_menu_check()
    {
        $current_locations = get_theme_mod('nav_menu_locations', array());

        // Tìm menu cần thiết
        $main_menu = get_term_by('name', 'Main Menu', 'nav_menu');
        $mobile_menu = get_term_by('name', 'Mobile Menu', 'nav_menu');

        $expected_locations = [];
        if ($main_menu && !is_wp_error($main_menu)) {
            $expected_locations['main_menu'] = $main_menu->term_id;
            $expected_locations['primary'] = $main_menu->term_id;
        }
        if ($mobile_menu && !is_wp_error($mobile_menu)) {
            $expected_locations['mobile_menu'] = $mobile_menu->term_id;
        }

        $needs_fix = false;
        foreach ($expected_locations as $location => $expected_menu_id) {
            if (!isset($current_locations[$location]) || $current_locations[$location] != $expected_menu_id) {
                $needs_fix = true;
                break;
            }
        }

        if ($needs_fix) {
            $this->force_menu_assignment($expected_locations);
        }
    }



    public function create_default_menus()
    {
        // Tạo Main Menu nếu không tồn tại
        if (!get_term_by('name', 'Main Menu', 'nav_menu')) {
            $main_menu_id = wp_create_nav_menu('Main Menu');
            if (!is_wp_error($main_menu_id)) {


                // Thêm menu items mặc định
                wp_update_nav_menu_item($main_menu_id, 0, array(
                    'menu-item-title' => 'Home',
                    'menu-item-url' => home_url('/'),
                    'menu-item-status' => 'publish',
                    'menu-item-type' => 'custom'
                ));

                wp_update_nav_menu_item($main_menu_id, 0, array(
                    'menu-item-title' => 'Jobs',
                    'menu-item-url' => home_url('/jobs/'),
                    'menu-item-status' => 'publish',
                    'menu-item-type' => 'custom'
                ));
            }
        }

        // Tạo Mobile Menu nếu không tồn tại
        if (!get_term_by('name', 'Mobile Menu', 'nav_menu')) {
            $mobile_menu_id = wp_create_nav_menu('Mobile Menu');
            if (!is_wp_error($mobile_menu_id)) {


                // Thêm menu items mặc định cho mobile
                wp_update_nav_menu_item($mobile_menu_id, 0, array(
                    'menu-item-title' => 'Home',
                    'menu-item-url' => home_url('/'),
                    'menu-item-status' => 'publish',
                    'menu-item-type' => 'custom'
                ));
            }
        }
    }

    public function create_and_activate_child_theme()
    {
        $child_slug = 'jobportal-child';
        $child_dir = get_theme_root() . '/' . $child_slug;

        if (!file_exists($child_dir)) {
            mkdir($child_dir, 0755, true);
            $parent_theme = wp_get_theme()->get_template();
            $style = <<<CSS
                /*
                Theme Name:JobPortalChild
                Theme URI: https://jobportal.example.com/
                Author: JobPortal Team
                Author URI: http://uxper.co/
                Description: This is a child theme of JobPortal
                Template: jobportal
                Version: 1.0.0
                Requires at least: 5.0
                Tested up to: 5.4
                Requires PHP: 7.4
                License URI: https://choosealicense.com/licenses/gpl-2.0/
                Text Domain:jobportal-child
                Tags: editor-style, featured-images, microformats, post-formats, rtl-language-support, sticky-post, threaded-comments, translation-ready
                */
            @import url("../$parent_theme/style.css");
            CSS;
            file_put_contents("$child_dir/style.css", $style);
            file_put_contents("$child_dir/screenshot.png", file_get_contents(get_template_directory() . '/screenshot.jpg'));
            file_put_contents("$child_dir/README.txt", "This is a child theme of $parent_theme.");
            file_put_contents("$child_dir/functions.php", "<?php\n// Child theme functions\n");
        }

        switch_theme($child_slug);
    }

    private function jobportal_replace_demo_domain($selected_import)
    {
        $new_domain = untrailingslashit(home_url());
        $old_domain = untrailingslashit($this->jobportal_get_old_domain($selected_import));

        if (!$old_domain || $old_domain === $new_domain) {
            return;
        }

        // 1. Replace in menu items
        $this->replace_domain_in_menus($old_domain, $new_domain);

        // 2. Replace in all WordPress options
        $this->replace_domain_in_options($old_domain, $new_domain);

        // 3. Replace in post content and meta
        $this->replace_domain_in_posts($old_domain, $new_domain);

        // 4. Replace in Elementor content specifically
        $this->replace_url_elementor_content($old_domain, $new_domain);

        // 5. Update home and site URLs if needed
        $this->update_site_urls($old_domain, $new_domain);

        wp_cache_flush();
    }

    /**
     * Replace domain in menu items
     */
    private function replace_domain_in_menus($old_domain, $new_domain)
    {
        $menus = wp_get_nav_menus();
        $replaced_count = 0;

        foreach ($menus as $menu) {
            $items = wp_get_nav_menu_items($menu->term_id);
            if (!$items) continue;

            foreach ($items as $item) {
                if ($item->type === 'custom' && strpos($item->url, $old_domain) === 0) {
                    $new_url = str_replace($old_domain, $new_domain, $item->url);
                    update_post_meta($item->ID, '_menu_item_url', $new_url);
                    $replaced_count++;
                }
            }
        }
    }

    /**
     * Replace domain in WordPress options table
     */
    private function replace_domain_in_options($old_domain, $new_domain)
    {
        global $wpdb;

        $options = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name, option_value
                FROM {$wpdb->options}
                WHERE option_value LIKE %s
                AND option_name NOT LIKE '_transient%'
                AND option_name NOT LIKE '%_cache_%'",
                '%' . $wpdb->esc_like($old_domain) . '%'
            )
        );

        $replaced_count = 0;
        foreach ($options as $opt) {
            $old_value = $opt->option_value;
            $new_value = $this->recursive_domain_replace($old_value, $old_domain, $new_domain);

            if ($new_value !== $old_value) {
                update_option($opt->option_name, $new_value);
                $replaced_count++;
            }
        }
    }

    /**
     * Replace domain in posts content and meta
     */
    private function replace_domain_in_posts($old_domain, $new_domain)
    {
        global $wpdb;

        // Replace in post content
        $posts_updated = $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->posts}
                SET post_content = REPLACE(post_content, %s, %s)
                WHERE post_content LIKE %s",
                $old_domain,
                $new_domain,
                '%' . $wpdb->esc_like($old_domain) . '%'
            )
        );

        // Replace in post excerpts
        $excerpts_updated = $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->posts}
                SET post_excerpt = REPLACE(post_excerpt, %s, %s)
                WHERE post_excerpt LIKE %s",
                $old_domain,
                $new_domain,
                '%' . $wpdb->esc_like($old_domain) . '%'
            )
        );

        // Replace in post meta
        $meta_updated = $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->postmeta}
                SET meta_value = REPLACE(meta_value, %s, %s)
                WHERE meta_value LIKE %s",
                $old_domain,
                $new_domain,
                '%' . $wpdb->esc_like($old_domain) . '%'
            )
        );
    }

    /**
     * Update site URLs if they match old domain
     */
    private function update_site_urls($old_domain, $new_domain)
    {
        $current_home = get_option('home');
        $current_siteurl = get_option('siteurl');

        if (strpos($current_home, $old_domain) === 0) {
            update_option('home', str_replace($old_domain, $new_domain, $current_home));
        }

        if (strpos($current_siteurl, $old_domain) === 0) {
            update_option('siteurl', str_replace($old_domain, $new_domain, $current_siteurl));
        }
    }

    /**
     * Recursively replace domain in arrays and serialized data
     */
    private function recursive_domain_replace($data, $old_domain, $new_domain)
    {
        if (is_string($data)) {
            if (is_serialized($data)) {
                $unserialized = maybe_unserialize($data);
                $updated = $this->replace_domain_in_array($unserialized, $old_domain, $new_domain);
                return maybe_serialize($updated);
            } else {
                return str_replace($old_domain, $new_domain, $data);
            }
        }

        return $data;
    }

    /**
     * Clean up unwanted paths specific to Freelancer demo
     * Remove /sites/2/ from media URLs and /version-2-0/ from other URLs
     */
    private function cleanup_freelancer_paths($selected_import)
    {
        if ($selected_import['import_file_name'] !== 'JobPortal Freelancer') {
            return;
        }

        try {
            $this->increase_import_limits();

            $unwanted_paths = [
                '/sites/2/' => '/',
                '/version-2-0/' => '/'
            ];

            foreach ($unwanted_paths as $unwanted_path => $replacement) {
                try {
                    $this->remove_unwanted_path_from_database_safe($unwanted_path, $replacement);
                    usleep(100000);
                } catch (Exception $e) {
                    error_log("Error cleaning path {$unwanted_path}: " . $e->getMessage());
                    continue;
                }
            }

            try {
                $this->cleanup_media_urls_safe();
                usleep(100000);
            } catch (Exception $e) {
                error_log("Error in cleanup_media_urls: " . $e->getMessage());
            }

            try {
                $this->cleanup_elementor_version_paths_safe();
                usleep(100000);
            } catch (Exception $e) {
                error_log("Error in cleanup_elementor_version_paths: " . $e->getMessage());
            }

            wp_cache_flush();
        } catch (Exception $e) {
            error_log("Critical error in cleanup_freelancer_paths: " . $e->getMessage());
        }
    }

    /**
     * Public method to run comprehensive cleanup of multisite paths
     * Can be called manually or via admin action
     */
    public function run_comprehensive_multisite_cleanup()
    {
        if (!current_user_can('manage_options')) {
            return false;
        }



        // Clean up /sites/2/ paths
        $this->cleanup_media_urls();

        // Clean up /version-2-0/ paths
        $this->cleanup_elementor_version_paths();

        $this->run_additional_cleanup();

        wp_cache_flush();

        if (defined('ELEMENTOR_VERSION') && class_exists('\Elementor\Plugin') && isset(\Elementor\Plugin::$instance)) {
            if (isset(\Elementor\Plugin::$instance->files_manager)) {
                \Elementor\Plugin::$instance->files_manager->clear_cache();
            }

            // Clear Elementor CSS cache
            if (isset(\Elementor\Plugin::$instance->posts_css_manager) && method_exists(\Elementor\Plugin::$instance->posts_css_manager, 'clear_cache')) {
                \Elementor\Plugin::$instance->posts_css_manager->clear_cache();
            }
        }

        // Clear other common caches
        if (function_exists('w3tc_flush_all')) {
            w3tc_flush_all();
        }

        if (function_exists('wp_cache_clear_cache')) {
            wp_cache_clear_cache();
        }

        // Clear transients
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'");

        // Force regenerate Elementor CSS
        if (defined('ELEMENTOR_VERSION')) {
            delete_option('elementor_scheme_color');
            delete_option('elementor_scheme_typography');
        }


        return true;
    }

    /**
     * Additional cleanup for any remaining multisite paths
     */
    private function run_additional_cleanup()
    {
        global $wpdb;

        $site_url = untrailingslashit(home_url());

        // Additional patterns that might be missed
        $site_url = untrailingslashit(home_url());
        $site_domain = str_replace(['http://', 'https://'], '', $site_url);

        $additional_patterns = [
            // Any remaining /sites/2/ patterns
            '/sites/2/' => '/',
            // Specific image paths
            '/wp-content/uploads/sites/2/2022/' => '/wp-content/uploads/2022/',
            '/wp-content/uploads/sites/2/2023/' => '/wp-content/uploads/2023/',
            '/wp-content/uploads/sites/2/2024/' => '/wp-content/uploads/2024/',
            '/wp-content/uploads/sites/2/2021/' => '/wp-content/uploads/2021/',
            // URL encoded versions
            '%2Fsites%2F2%2F' => '%2F',
            // With domains
            $site_url . '/wp-content/uploads/sites/2/' => $site_url . '/wp-content/uploads/',
            'https://' . $site_domain . '/wp-content/uploads/sites/2/' => 'https://' . $site_domain . '/wp-content/uploads/',
            'http://' . $site_domain . '/wp-content/uploads/sites/2/' => 'http://' . $site_domain . '/wp-content/uploads/',
        ];

        $total_cleaned = 0;

        // Tables to clean
        $tables_to_clean = [
            $wpdb->postmeta => ['meta_value'],
            $wpdb->options => ['option_value'],
            $wpdb->posts => ['post_content', 'post_excerpt'],
            $wpdb->comments => ['comment_content'],
            $wpdb->termmeta => ['meta_value'],
        ];

        foreach ($additional_patterns as $old_pattern => $new_pattern) {
            foreach ($tables_to_clean as $table => $columns) {
                foreach ($columns as $column) {
                    $where_clause = "";
                    if ($table === $wpdb->options) {
                        $where_clause = "AND option_name NOT LIKE '_transient%'";
                    }

                    $updated = $wpdb->query(
                        $wpdb->prepare(
                            "UPDATE {$table}
                            SET {$column} = REPLACE({$column}, %s, %s)
                            WHERE {$column} LIKE %s {$where_clause}",
                            $old_pattern,
                            $new_pattern,
                            '%' . $wpdb->esc_like($old_pattern) . '%'
                        )
                    );
                    $total_cleaned += $updated;
                }
            }
        }
    }



    /**
     * Remove unwanted paths from all database tables
     */
    private function remove_unwanted_path_from_database($unwanted_path, $replacement)
    {
        global $wpdb;

        // Get current site URL to build correct patterns
        $site_url = untrailingslashit(home_url());
        $old_pattern = $site_url . $unwanted_path;
        $new_pattern = $site_url . $replacement;

        $posts_updated = $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->posts}
                SET post_content = REPLACE(post_content, %s, %s)
                WHERE post_content LIKE %s",
                $old_pattern,
                $new_pattern,
                '%' . $wpdb->esc_like($old_pattern) . '%'
            )
        );

        $excerpts_updated = $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->posts}
                SET post_excerpt = REPLACE(post_excerpt, %s, %s)
                WHERE post_excerpt LIKE %s",
                $old_pattern,
                $new_pattern,
                '%' . $wpdb->esc_like($old_pattern) . '%'
            )
        );

        $meta_updated = $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->postmeta}
                SET meta_value = REPLACE(meta_value, %s, %s)
                WHERE meta_value LIKE %s",
                $old_pattern,
                $new_pattern,
                '%' . $wpdb->esc_like($old_pattern) . '%'
            )
        );

        $options_updated = $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->options}
                SET option_value = REPLACE(option_value, %s, %s)
                WHERE option_value LIKE %s
                AND option_name NOT LIKE '_transient%'",
                $old_pattern,
                $new_pattern,
                '%' . $wpdb->esc_like($old_pattern) . '%'
            )
        );
    }

    /**
     * Safe version: Remove unwanted paths with timeout protection
     */
    private function remove_unwanted_path_from_database_safe($unwanted_path, $replacement)
    {
        global $wpdb;

        try {
            // Get current site URL to build correct patterns
            $site_url = untrailingslashit(home_url());
            $old_pattern = $site_url . $unwanted_path;
            $new_pattern = $site_url . $replacement;

            $batch_size = 100;

            $posts_updated = $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$wpdb->posts}
                    SET post_content = REPLACE(post_content, %s, %s)
                    WHERE post_content LIKE %s LIMIT {$batch_size}",
                    $old_pattern,
                    $new_pattern,
                    '%' . $wpdb->esc_like($old_pattern) . '%'
                )
            );

            usleep(50000);

            $meta_updated = $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$wpdb->postmeta}
                    SET meta_value = REPLACE(meta_value, %s, %s)
                    WHERE meta_value LIKE %s LIMIT {$batch_size}",
                    $old_pattern,
                    $new_pattern,
                    '%' . $wpdb->esc_like($old_pattern) . '%'
                )
            );

            usleep(50000);

            $options_updated = $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$wpdb->options}
                    SET option_value = REPLACE(option_value, %s, %s)
                    WHERE option_value LIKE %s
                    AND option_name NOT LIKE '_transient%' LIMIT {$batch_size}",
                    $old_pattern,
                    $new_pattern,
                    '%' . $wpdb->esc_like($old_pattern) . '%'
                )
            );
        } catch (Exception $e) {
            error_log("Error in safe path removal: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Specifically clean media URLs with /sites/2/ pattern
     */
    private function cleanup_media_urls()
    {
        global $wpdb;

        $site_url = untrailingslashit(home_url());

        $cleanup_patterns = [
            '/wp-content/uploads/sites/2/' => '/wp-content/uploads/',
            $site_url . '/wp-content/uploads/sites/2/' => $site_url . '/wp-content/uploads/',
            str_replace('http://', 'https://', $site_url) . '/wp-content/uploads/sites/2/' => str_replace('http://', 'https://', $site_url) . '/wp-content/uploads/',
        ];

        $tables_and_columns = [
            $wpdb->posts => ['post_content', 'post_excerpt'],
            $wpdb->postmeta => ['meta_value'],
            $wpdb->options => ['option_value'],
            $wpdb->comments => ['comment_content'],
            $wpdb->termmeta => ['meta_value']
        ];

        $total_updated = 0;
        foreach ($cleanup_patterns as $old_pattern => $new_pattern) {
            foreach ($tables_and_columns as $table => $columns) {
                foreach ($columns as $column) {
                    $where_clause = "";
                    if ($table === $wpdb->options) {
                        $where_clause = "AND option_name NOT LIKE '_transient%'";
                    }

                    $updated = $wpdb->query(
                        $wpdb->prepare(
                            "UPDATE {$table}
                            SET {$column} = REPLACE({$column}, %s, %s)
                            WHERE {$column} LIKE %s {$where_clause}",
                            $old_pattern,
                            $new_pattern,
                            '%' . $wpdb->esc_like($old_pattern) . '%'
                        )
                    );
                    $total_updated += $updated;
                }
            }
        }

        $this->cleanup_elementor_json_data();
    }

    /**
     * Safe version: Clean media URLs with timeout protection
     */
    private function cleanup_media_urls_safe()
    {
        global $wpdb;

        try {
            $site_url = untrailingslashit(home_url());

            $cleanup_patterns = [
                '/wp-content/uploads/sites/2/' => '/wp-content/uploads/',
                $site_url . '/wp-content/uploads/sites/2/' => $site_url . '/wp-content/uploads/',
            ];

            $batch_size = 50;
            $total_updated = 0;

            foreach ($cleanup_patterns as $old_pattern => $new_pattern) {
                try {
                    $posts_updated = $wpdb->query(
                        $wpdb->prepare(
                            "UPDATE {$wpdb->posts}
                            SET post_content = REPLACE(post_content, %s, %s)
                            WHERE post_content LIKE %s LIMIT {$batch_size}",
                            $old_pattern,
                            $new_pattern,
                            '%' . $wpdb->esc_like($old_pattern) . '%'
                        )
                    );
                    $total_updated += $posts_updated;

                    usleep(50000);
                    $meta_updated = $wpdb->query(
                        $wpdb->prepare(
                            "UPDATE {$wpdb->postmeta}
                            SET meta_value = REPLACE(meta_value, %s, %s)
                            WHERE meta_value LIKE %s LIMIT {$batch_size}",
                            $old_pattern,
                            $new_pattern,
                            '%' . $wpdb->esc_like($old_pattern) . '%'
                        )
                    );
                    $total_updated += $meta_updated;

                    usleep(50000);
                } catch (Exception $e) {
                    error_log("Error cleaning media pattern {$old_pattern}: " . $e->getMessage());
                    continue;
                }
            }

            $this->cleanup_elementor_json_data_safe();
        } catch (Exception $e) {
            error_log("Error in safe media cleanup: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Clean up /sites/2/ paths in Elementor JSON data
     */
    private function cleanup_elementor_json_data()
    {
        global $wpdb;

        $site_url = untrailingslashit(home_url());

        // Patterns to clean
        $cleanup_patterns = [
            '/wp-content/uploads/sites/2/' => '/wp-content/uploads/',
            $site_url . '/wp-content/uploads/sites/2/' => $site_url . '/wp-content/uploads/',
            'https://' . str_replace(['http://', 'https://'], '', $site_url) . '/wp-content/uploads/sites/2/' => 'https://' . str_replace(['http://', 'https://'], '', $site_url) . '/wp-content/uploads/',
        ];

        // Get ALL Elementor data meta (not just ones with /sites/2/)
        $elementor_metas = $wpdb->get_results(
            "SELECT post_id, meta_key, meta_value FROM {$wpdb->postmeta}
             WHERE meta_key IN ('_elementor_data', '_elementor_page_settings')
             AND meta_value LIKE '%sites/2%'"
        );

        $updated_count = 0;
        foreach ($elementor_metas as $meta) {
            $original_data = $meta->meta_value;
            $cleaned_data = $original_data;

            // Apply all cleanup patterns
            foreach ($cleanup_patterns as $old_pattern => $new_pattern) {
                $cleaned_data = str_replace($old_pattern, $new_pattern, $cleaned_data);
            }

            // Update if changed
            if ($cleaned_data !== $original_data) {
                $result = $wpdb->update(
                    $wpdb->postmeta,
                    ['meta_value' => $cleaned_data],
                    ['post_id' => $meta->post_id, 'meta_key' => $meta->meta_key]
                );
                if ($result !== false) {
                    $updated_count++;
                }
            }
        }

        // Also clean Elementor Library posts (templates)
        $library_posts = $wpdb->get_results(
            "SELECT ID, post_content FROM {$wpdb->posts}
             WHERE post_type = 'elementor_library'
             AND post_content LIKE '%sites/2%'"
        );

        foreach ($library_posts as $post) {
            $original_content = $post->post_content;
            $cleaned_content = $original_content;

            foreach ($cleanup_patterns as $old_pattern => $new_pattern) {
                $cleaned_content = str_replace($old_pattern, $new_pattern, $cleaned_content);
            }

            if ($cleaned_content !== $original_content) {
                $result = $wpdb->update(
                    $wpdb->posts,
                    ['post_content' => $cleaned_content],
                    ['ID' => $post->ID]
                );
                if ($result !== false) {
                    $updated_count++;
                }
            }
        }
    }

    /**
     * Safe version: Clean Elementor JSON data with timeout protection
     */
    private function cleanup_elementor_json_data_safe()
    {
        global $wpdb;

        try {
            $site_url = untrailingslashit(home_url());

            // Patterns to clean (simplified for safety)
            $cleanup_patterns = [
                '/wp-content/uploads/sites/2/' => '/wp-content/uploads/',
                $site_url . '/wp-content/uploads/sites/2/' => $site_url . '/wp-content/uploads/',
            ];

            // Get Elementor data với LIMIT để tránh timeout
            $elementor_metas = $wpdb->get_results(
                "SELECT post_id, meta_key, meta_value FROM {$wpdb->postmeta}
                 WHERE meta_key IN ('_elementor_data', '_elementor_page_settings')
                 AND meta_value LIKE '%sites/2%'
                 LIMIT 20"
            );

            $updated_count = 0;
            foreach ($elementor_metas as $meta) {
                try {
                    $original_data = $meta->meta_value;
                    $cleaned_data = $original_data;

                    foreach ($cleanup_patterns as $old_pattern => $new_pattern) {
                        $cleaned_data = str_replace($old_pattern, $new_pattern, $cleaned_data);
                    }

                    if ($cleaned_data !== $original_data) {
                        $result = $wpdb->update(
                            $wpdb->postmeta,
                            ['meta_value' => $cleaned_data],
                            ['post_id' => $meta->post_id, 'meta_key' => $meta->meta_key]
                        );
                        if ($result !== false) {
                            $updated_count++;
                        }
                    }

                    usleep(25000);
                } catch (Exception $e) {
                    error_log("Error cleaning Elementor meta for post {$meta->post_id}: " . $e->getMessage());
                    continue;
                }
            }
        } catch (Exception $e) {
            error_log("Error in safe Elementor JSON cleanup: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Recursively clean URLs in Elementor data structure
     */
    private function recursive_clean_elementor_urls($data, $patterns)
    {
        if (is_string($data)) {
            foreach ($patterns as $old => $new) {
                $data = str_replace($old, $new, $data);
            }
            return $data;
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->recursive_clean_elementor_urls($value, $patterns);
            }
        }

        return $data;
    }

    /**
     * Specifically clean Elementor URLs with /version-2-0/ pattern
     */
    private function cleanup_elementor_version_paths()
    {
        global $wpdb;

        $site_url = untrailingslashit(home_url());
        $old_version_pattern = $site_url . '/version-2-0/';
        $new_version_pattern = $site_url . '/';

        // Focus on Elementor-specific meta keys
        $elementor_meta_keys = [
            '_elementor_data',
            '_elementor_page_settings'
        ];

        $total_updated = 0;
        foreach ($elementor_meta_keys as $meta_key) {
            $updated = $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$wpdb->postmeta}
                    SET meta_value = REPLACE(meta_value, %s, %s)
                    WHERE meta_key = %s
                    AND meta_value LIKE %s",
                    $old_version_pattern,
                    $new_version_pattern,
                    $meta_key,
                    '%' . $wpdb->esc_like($old_version_pattern) . '%'
                )
            );
            $total_updated += $updated;
        }

        // Also clean in Elementor library posts content
        $library_updated = $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->posts}
                SET post_content = REPLACE(post_content, %s, %s)
                WHERE post_type = 'elementor_library'
                AND post_content LIKE %s",
                $old_version_pattern,
                $new_version_pattern,
                '%' . $wpdb->esc_like($old_version_pattern) . '%'
            )
        );

        $total_updated += $library_updated;
        if (class_exists('\Elementor\Plugin')) {
            $this->refresh_elementor_cache();
        }
    }

    /**
     * Safe version: Clean Elementor version paths with timeout protection
     */
    private function cleanup_elementor_version_paths_safe()
    {
        global $wpdb;

        try {
            $site_url = untrailingslashit(home_url());
            $old_version_pattern = $site_url . '/version-2-0/';
            $new_version_pattern = $site_url . '/';

            $elementor_meta_keys = [
                '_elementor_data',
                '_elementor_page_settings'
            ];

            $batch_size = 25;
            $total_updated = 0;

            foreach ($elementor_meta_keys as $meta_key) {
                try {
                    $updated = $wpdb->query(
                        $wpdb->prepare(
                            "UPDATE {$wpdb->postmeta}
                            SET meta_value = REPLACE(meta_value, %s, %s)
                            WHERE meta_key = %s
                            AND meta_value LIKE %s
                            LIMIT {$batch_size}",
                            $old_version_pattern,
                            $new_version_pattern,
                            $meta_key,
                            '%' . $wpdb->esc_like($old_version_pattern) . '%'
                        )
                    );
                    $total_updated += $updated;

                    usleep(50000);
                } catch (Exception $e) {
                    error_log("Error cleaning meta key {$meta_key}: " . $e->getMessage());
                    continue;
                }
            }

            try {
                $library_updated = $wpdb->query(
                    $wpdb->prepare(
                        "UPDATE {$wpdb->posts}
                        SET post_content = REPLACE(post_content, %s, %s)
                        WHERE post_type = 'elementor_library'
                        AND post_content LIKE %s
                        LIMIT {$batch_size}",
                        $old_version_pattern,
                        $new_version_pattern,
                        '%' . $wpdb->esc_like($old_version_pattern) . '%'
                    )
                );
                $total_updated += $library_updated;
            } catch (Exception $e) {
                error_log("Error cleaning Elementor library posts: " . $e->getMessage());
            }

            if (class_exists('\Elementor\Plugin')) {
                try {
                    $this->refresh_elementor_cache();
                } catch (Exception $e) {
                    error_log("Error refreshing Elementor cache: " . $e->getMessage());
                }
            }
        } catch (Exception $e) {
            error_log("Error in safe Elementor version paths cleanup: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Manual cleanup helper - can be called independently for testing
     * Usage: JobPortal_Demo_Importer::manual_cleanup_freelancer_paths();
     */
    public static function manual_cleanup_freelancer_paths()
    {
        $importer = new self();
        $selected_import = ['import_file_name' => 'JobPortal Freelancer'];
        $importer->cleanup_freelancer_paths($selected_import);

        return "Freelancer paths cleanup completed manually.";
    }

    /**
     * Quick test function to run comprehensive cleanup
     * Can be called directly via URL parameter for testing
     */
    public static function test_comprehensive_cleanup()
    {
        if (!current_user_can('manage_options')) {
            return "Access denied";
        }

        $importer = new self();
        $result = $importer->run_comprehensive_multisite_cleanup();

        return $result ? "Comprehensive cleanup completed successfully!" : "Cleanup failed!";
    }

    /**
     * Test theme options URL cleaning
     * Usage: JobPortal_Demo_Importer::test_theme_options_cleaning();
     */
    public static function test_theme_options_cleaning()
    {
        $importer = new self();

        // Test data with problematic URLs
        $test_data = [
            'jobs_search_image' => [
                'url' => 'https://jobportal.example.com/freelance/wp-content/uploads/sites/2/2023/04/Banner-de-LinkedIn-Trabajo-Sencillo.png'
            ],
            'image_employer_dashboard' => [
                'url' => 'https://jobportal.example.com/freelance/wp-content/uploads/sites/2/2022/10/dashboard.svg'
            ]
        ];

        $selected_import = ['import_file_name' => 'JobPortal Freelancer'];
        $cleaned_data = $importer->clean_theme_options_urls($test_data, $selected_import);

        return [
            'original' => $test_data,
            'cleaned' => $cleaned_data
        ];
    }

    public static function manual_fix_menu_locations()
    {
        if (!current_user_can('manage_options')) {
            return "Access denied";
        }

        $importer = new self();
        $importer->setMenus();
        $importer->simple_menu_check();

        $locations = get_theme_mod('nav_menu_locations', []);
        return "Menu fix completed. Locations: " . json_encode($locations);
    }

    private function force_menu_assignment($menu_locations)
    {
        set_theme_mod('nav_menu_locations', $menu_locations);

        // Direct option update để đảm bảo
        $stylesheet = get_option('stylesheet');
        $theme_mods_option = 'theme_mods_' . $stylesheet;
        $theme_mods = get_option($theme_mods_option, array());
        $theme_mods['nav_menu_locations'] = $menu_locations;
        update_option($theme_mods_option, $theme_mods);

        return true;
    }



    /**
     * Create missing menus if they don't exist
     * Usage: JobPortal_Demo_Importer::create_missing_menus();
     */
    public static function create_missing_menus()
    {
        if (!current_user_can('manage_options')) {
            return "Access denied";
        }

        $importer = new self();
        $importer->create_default_menus();
        $importer->setMenus();

        return "Missing menus created and assigned";
    }

    /**
     * Check menu status - đơn giản
     * Usage: JobPortal_Demo_Importer::check_menu_status();
     */
    public static function check_menu_status()
    {
        if (!current_user_can('manage_options')) {
            return "Access denied";
        }

        $menus = wp_get_nav_menus();
        $locations = get_theme_mod('nav_menu_locations', []);

        return "Menus: " . count($menus) . ", Locations: " . count($locations);
    }

    /**
     * Update all jobs expiration dates after import
     * Sets random expiration between 55-90 days from current date
     */
    private function update_all_jobs_expiration()
    {
        $min_days = 55;
        $max_days = 90;

        if (!defined('JOBPORTAL_METABOX_PREFIX')) {
            define('JOBPORTAL_METABOX_PREFIX', 'jobportal_');
        }

        $args = array(
            'post_type' => 'jobs',
            'post_status' => 'any',
            'posts_per_page' => -1
        );

        $jobs_query = new WP_Query($args);
        $updated_count = 0;
        $current_date = date('Y-m-d');

        while ($jobs_query->have_posts()) {
            $jobs_query->the_post();
            $job_id = get_the_ID();
            $publish_date = get_the_date('Y-m-d');
            $current_status = get_post_status();

            $days_since_publish = (strtotime($current_date) - strtotime($publish_date)) / 86400;
            $random_additional_days = $min_days + ($job_id % ($max_days - $min_days + 1));
            $new_jobs_days_closing = ceil($days_since_publish) + $random_additional_days;

            update_post_meta($job_id, JOBPORTAL_METABOX_PREFIX . 'enable_jobs_expires', '0');
            update_post_meta($job_id, JOBPORTAL_METABOX_PREFIX . 'jobs_days_closing', $new_jobs_days_closing);

            if ($current_status == 'expired') {
                wp_update_post(array(
                    'ID' => $job_id,
                    'post_status' => 'publish'
                ));
            }
            $updated_count++;
        }
        wp_reset_postdata();
        return $updated_count;
    }

    /**
     * Fix company logos after import
     * Update attachment IDs to match imported media
     */
    private function fix_company_logos()
    {
        global $wpdb;
        // Get all companies
        $companies = get_posts([
            'post_type' => 'company',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ]);

        $fixed_count = 0;

        foreach ($companies as $company_id) {
            $logo_meta = get_post_meta($company_id, JOBPORTAL_METABOX_PREFIX . 'company_logo', true);

            if (!is_array($logo_meta) || !isset($logo_meta['url'])) {
                continue;
            }

            $logo_url = $logo_meta['url'];
            $old_id = isset($logo_meta['id']) ? $logo_meta['id'] : '';

            // Find attachment by URL
            $new_attachment_id = $this->find_attachment_by_url($logo_url);

            if ($new_attachment_id && $new_attachment_id != $old_id) {
                // Update with correct attachment ID
                $updated_logo_meta = [
                    'id' => $new_attachment_id,
                    'url' => $logo_url
                ];

                update_post_meta($company_id, JOBPORTAL_METABOX_PREFIX . 'company_logo', $updated_logo_meta);
                $fixed_count++;
            }
        }
        return $fixed_count;
    }

    /**
     * Find attachment ID by URL
     * Search for attachment post with matching URL
     */
    private function find_attachment_by_url($url)
    {
        global $wpdb;

        if (!$url) {
            return false;
        }

        // Get filename from URL
        $filename = basename($url);

        // Search for attachment with this filename
        $attachment_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT post_id FROM {$wpdb->postmeta}
                WHERE meta_key = '_wp_attached_file'
                AND meta_value LIKE %s
                LIMIT 1",
                '%' . $wpdb->esc_like($filename)
            )
        );

        if ($attachment_id) {
            return $attachment_id;
        }

        // Fallback: search by post title (filename without extension)
        $title_search = pathinfo($filename, PATHINFO_FILENAME);
        $attachment_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts}
                WHERE post_type = 'attachment'
                AND post_title LIKE %s
                LIMIT 1",
                '%' . $wpdb->esc_like($title_search) . '%'
            )
        );

        return $attachment_id ?: false;
    }

    /**
     * Fix candidate avatars after import
     * Update attachment IDs to match imported media
     */
    private function fix_candidate_avatars()
    {
        global $wpdb;
        // Get all candidates
        $candidates = get_posts([
            'post_type' => 'candidates',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ]);

        $fixed_count = 0;

        foreach ($candidates as $candidate_id) {
            $avatar_meta = get_post_meta($candidate_id, JOBPORTAL_METABOX_PREFIX . 'candidate_avatar', true);

            if (!is_array($avatar_meta) || !isset($avatar_meta['url'])) {
                continue;
            }

            $avatar_url = $avatar_meta['url'];
            $old_id = isset($avatar_meta['id']) ? $avatar_meta['id'] : '';

            // Find attachment by URL
            $new_attachment_id = $this->find_attachment_by_url($avatar_url);

            if ($new_attachment_id && $new_attachment_id != $old_id) {
                // Update with correct attachment ID
                $updated_avatar_meta = [
                    'id' => $new_attachment_id,
                    'url' => $avatar_url
                ];

                update_post_meta($candidate_id, JOBPORTAL_METABOX_PREFIX . 'candidate_avatar', $updated_avatar_meta);
                $fixed_count++;
            }
        }


        return $fixed_count;
    }

    private function replace_domain_in_array($data, $old_domain, $new_domain)
    {
        if (is_string($data)) {
            return str_replace($old_domain, $new_domain, $data);
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->replace_domain_in_array($value, $old_domain, $new_domain);
            }
        }

        if (is_object($data)) {
            foreach ($data as $key => $value) {
                $data->$key = $this->replace_domain_in_array($value, $old_domain, $new_domain);
            }
        }

        return $data;
    }

    public function jobportal_get_old_domain($selected_import)
    {
        $import_file_name = isset($selected_import['import_file_name']) ? $selected_import['import_file_name'] : '';

        switch ($import_file_name) {
            case 'JobPortal Jobs Board':
                return 'https://jobportal.example.com';
            case 'JobPortal Freelancer':
                return 'https://jobportal.example.com/freelance';
            default:
                return 'https://jobportal.example.com';
        }
    }

    public function replace_url_elementor_content($old_url, $new_url)
    {
        if ($old_url === $new_url || !$old_url) return;



        // Method 1: Use Elementor's built-in replace function
        if (defined('ELEMENTOR_VERSION') && class_exists('\Elementor\Utils') && method_exists('\Elementor\Utils', 'replace_urls')) {
            \Elementor\Utils::replace_urls($old_url, $new_url);
        }

        // Method 2: Manual database replacement for Elementor data
        $this->replace_elementor_data_manual($old_url, $new_url);

        // Method 3: Replace in Elementor kit settings
        $this->replace_elementor_kit_urls($old_url, $new_url);
    }

    /**
     * Manual replacement in Elementor post meta data
     */
    private function replace_elementor_data_manual($old_url, $new_url)
    {
        global $wpdb;

        // Replace in _elementor_data meta
        $elementor_data_updated = $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->postmeta}
                SET meta_value = REPLACE(meta_value, %s, %s)
                WHERE meta_key = '_elementor_data'
                AND meta_value LIKE %s",
                $old_url,
                $new_url,
                '%' . $wpdb->esc_like($old_url) . '%'
            )
        );

        // Replace in _elementor_page_settings meta
        $page_settings_updated = $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->postmeta}
                SET meta_value = REPLACE(meta_value, %s, %s)
                WHERE meta_key = '_elementor_page_settings'
                AND meta_value LIKE %s",
                $old_url,
                $new_url,
                '%' . $wpdb->esc_like($old_url) . '%'
            )
        );

        // Replace in Elementor library posts content
        $library_posts_updated = $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->posts} p
                SET post_content = REPLACE(post_content, %s, %s)
                WHERE post_type = 'elementor_library'
                AND post_content LIKE %s",
                $old_url,
                $new_url,
                '%' . $wpdb->esc_like($old_url) . '%'
            )
        );
    }

    /**
     * Replace URLs in Elementor kit settings specifically
     */
    private function replace_elementor_kit_urls($old_url, $new_url)
    {
        if (!class_exists('\Elementor\Plugin')) {
            return;
        }

        $kit_id = false;
        if (class_exists('\Elementor\Plugin') && isset(\Elementor\Plugin::$instance) && isset(\Elementor\Plugin::$instance->kits_manager)) {
            $kit_id = \Elementor\Plugin::$instance->kits_manager->get_active_id();
        }
        if (!$kit_id) {
            return;
        }

        $kit_settings = get_post_meta($kit_id, '_elementor_page_settings', true);
        if (!is_array($kit_settings)) {
            return;
        }

        $original_settings = json_encode($kit_settings);
        $updated_settings_json = str_replace($old_url, $new_url, $original_settings);
        $updated_settings = json_decode($updated_settings_json, true);

        if ($updated_settings && $updated_settings !== $kit_settings) {
            update_post_meta($kit_id, '_elementor_page_settings', $updated_settings);


            // Clear cache after kit update
            $this->refresh_elementor_cache();
        }
    }

    /**
     * Ensure service post type is enabled when on import page
     * This prevents taxonomy import failures
     */
    public function ensure_service_enabled_on_import()
    {
        // Check if we're on the One Click Demo Import page
        if (isset($_GET['page']) && $_GET['page'] === 'one-click-demo-import') {
            $current_options = get_option(JOBPORTAL_OPTIONS_NAME, array());

            // Only update if service is not already enabled
            if (!isset($current_options['enable_post_type_service']) || $current_options['enable_post_type_service'] !== '1') {
                $current_options['enable_option_state'] = '1';
                $current_options['enable_option_country'] = '1';
                $current_options['enable_job_alerts'] = '1';
                $current_options['enable_post_type_service'] = '1';
                update_option(JOBPORTAL_OPTIONS_NAME, $current_options);
            }
        }
    }

    /**
     * Execute after import setup
     *
     * Main orchestration method called by OCDI after demo import completes.
     * Handles:
     * - Resource limit increases
     * - Basic configuration setup
     * - Scheduling cleanup tasks (different for Freelancer vs standard demos)
     *
     * @since 1.0.0
     * @param array $selected_import Import configuration with 'import_file_name' key
     * @return void
     */
    public function afterImportSetup(array $selected_import): void
    {
        try {
            $this->increase_import_limits();
            $this->setup_basic_configuration($selected_import);

            if ($selected_import['import_file_name'] === 'JobPortal Freelancer') {
                $this->schedule_freelancer_cleanup();
            } else {
                $this->schedule_standard_cleanup($selected_import);
            }
        } catch (Exception $e) {
            error_log("JobPortal Import Error in afterImportSetup: " . $e->getMessage());
        }
    }

    /**
     * Setup basic configuration (fast operations)
     */
    private function setup_basic_configuration($selected_import)
    {
        try {
            $current_options = get_option(JOBPORTAL_OPTIONS_NAME, array());
            $current_options['enable_option_state'] = '1';
            $current_options['enable_option_country'] = '1';
            $current_options['enable_job_alerts'] = '1';
            $current_options['enable_post_type_service'] = '1';
            update_option(JOBPORTAL_OPTIONS_NAME, $current_options);

            $this->setElementorSettings($selected_import);
            $this->setMenus();
            $this->setPages($selected_import);
            $this->create_and_activate_child_theme();
            $this->import_theme_options($selected_import);

            if (get_option('permalink_structure') !== '/%postname%/') {
                update_option('permalink_structure', '/%postname%/');
                flush_rewrite_rules();
            }
        } catch (Exception $e) {
            error_log('JobPortal Import Error in setup_basic_configuration: ' . $e->getMessage());
        }
    }

    /**
     * Schedule cleanup tasks for Freelancer demo (avoid timeout)
     */
    private function schedule_freelancer_cleanup()
    {
        wp_schedule_single_event(time() + self::CLEANUP_DELAY_BASIC, 'jobportal_freelancer_basic_cleanup');
        wp_schedule_single_event(time() + self::CLEANUP_DELAY_FULL, 'jobportal_freelancer_full_cleanup');
        if (!has_action('jobportal_freelancer_basic_cleanup', [$this, 'run_freelancer_basic_cleanup'])) {
            add_action('jobportal_freelancer_basic_cleanup', [$this, 'run_freelancer_basic_cleanup']);
        }
        if (!has_action('jobportal_freelancer_full_cleanup', [$this, 'run_freelancer_full_cleanup'])) {
            add_action('jobportal_freelancer_full_cleanup', [$this, 'run_freelancer_full_cleanup']);
        }
    }

    /**
     * Schedule standard cleanup tasks
     */
    private function schedule_standard_cleanup($selected_import)
    {
        wp_schedule_single_event(time() + self::CLEANUP_DELAY_STANDARD, 'jobportal_standard_cleanup', [$selected_import]);
        if (!has_action('jobportal_standard_cleanup', [$this, 'run_standard_cleanup'])) {
            add_action('jobportal_standard_cleanup', [$this, 'run_standard_cleanup']);
        }
    }

    /**
     * Basic cleanup for Freelancer demo (scheduled task)
     *
     * First cleanup phase for Freelancer import, runs 5 seconds after import.
     * Performs lightweight operations:
     * - Domain URL replacement
     * - Job expiration date updates
     *
     * @since 1.0.0
     * @return void
     */
    public function run_freelancer_basic_cleanup(): void
    {
        try {
            $this->increase_import_limits();

            $selected_import = ['import_file_name' => 'JobPortal Freelancer'];

            // Domain replacement
            $this->jobportal_replace_demo_domain($selected_import);

            // Basic jobs update
            $this->update_all_jobs_expiration();
        } catch (Exception $e) {
            error_log('JobPortal Import Error in run_freelancer_basic_cleanup: ' . $e->getMessage());
        }
    }

    /**
     * Full cleanup for Freelancer demo (scheduled later)
     *
     * Second cleanup phase for Freelancer import, runs 30 seconds after import.
     * Performs heavy operations:
     * - Multisite path cleanup (/sites/2/, /version-2-0/)
     * - Company logo and candidate avatar fixes
     * - Failed media import retry
     * - Menu location verification
     *
     * @since 1.0.0
     * @return void
     */
    public function run_freelancer_full_cleanup(): void
    {
        try {
            $this->increase_import_limits();

            $selected_import = ['import_file_name' => 'JobPortal Freelancer'];
            $this->cleanup_freelancer_paths($selected_import);
            $this->run_comprehensive_multisite_cleanup();
            $this->run_final_freelancer_cleanup();
            $this->fix_company_logos();
            $this->fix_candidate_avatars();
            $retried_count = $this->retry_failed_media_imports();

            $this->simple_menu_check();
        } catch (Exception $e) {
            error_log('JobPortal Import Error in run_freelancer_full_cleanup: ' . $e->getMessage());
        }
    }

    /**
     * Standard cleanup tasks (scheduled)
     *
     * Cleanup for non-Freelancer demos, runs 5 seconds after import.
     * Performs standard maintenance:
     * - Domain replacement
     * - Logo and avatar fixes
     * - Job expiration updates
     * - Failed media retry
     * - Menu verification
     *
     * @since 1.0.0
     * @param array $selected_import Import configuration array
     * @return void
     */
    public function run_standard_cleanup(array $selected_import): void
    {
        try {
            $this->increase_import_limits();

            $this->jobportal_replace_demo_domain($selected_import);
            $this->fix_company_logos();
            $this->fix_candidate_avatars();
            $this->update_all_jobs_expiration();
            $retried_count = $this->retry_failed_media_imports();
            $this->simple_menu_check();
        } catch (Exception $e) {
            error_log('JobPortal Import Error in run_standard_cleanup: ' . $e->getMessage());
        }
    }

    /**
     * Increase memory limit and timeout for import operations
     */
    private function increase_import_limits()
    {
        $current_memory = ini_get('memory_limit');
        if (intval($current_memory) < self::MEMORY_LIMIT_THRESHOLD) {
            ini_set('memory_limit', self::MEMORY_LIMIT_IMPORT);
        }

        $current_time = ini_get('max_execution_time');
        if (intval($current_time) < self::MAX_EXECUTION_TIME) {
            set_time_limit(self::MAX_EXECUTION_TIME);
        }
        ini_set('max_input_vars', self::MAX_INPUT_VARS);
    }

    public function prepare_media_import()
    {
        $this->increase_upload_limits();

        if (function_exists('wp_cache_delete')) {
            wp_cache_delete('upload_errors', 'jobportal_import');
        }
    }

    public function before_content_import()
    {
        $this->increase_import_limits();
    }

    public function after_content_import($selected_import)
    {
        global $wpdb;
        $pages_after = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'page' AND post_status != 'trash'");

        if ($pages_after == 0) {
            error_log("JobPortal Import: WARNING - No pages were imported! Check XML file and import process.");
        }
    }

    private function increase_upload_limits()
    {
        $max_size = '128M';
        ini_set('upload_max_filesize', $max_size);
        ini_set('post_max_size', $max_size);
        ini_set('max_file_uploads', '100');
        ini_set('memory_limit', '512M');
    }

    public function add_custom_mime_types($mimes)
    {
        $mimes['svg'] = 'image/svg+xml';
        $mimes['webp'] = 'image/webp';
        $mimes['json'] = 'application/json';

        return $mimes;
    }

    public function handle_upload_errors($file)
    {
        if (isset($file['error']) && $file['error'] !== UPLOAD_ERR_OK) {
            $this->log_upload_error($file);

            if (strpos($file['type'], 'image/') === 0) {
                return $this->create_placeholder_image($file);
            }
        }

        return $file;
    }

    /**
     * Log upload error để debug
     */
    private function log_upload_error($file)
    {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'File quá lớn (upload_max_filesize)',
            UPLOAD_ERR_FORM_SIZE => 'File quá lớn (MAX_FILE_SIZE)',
            UPLOAD_ERR_PARTIAL => 'File upload không hoàn thành',
            UPLOAD_ERR_NO_FILE => 'Không có file được upload',
            UPLOAD_ERR_NO_TMP_DIR => 'Thiếu thư mục temp',
            UPLOAD_ERR_CANT_WRITE => 'Không thể ghi file',
            UPLOAD_ERR_EXTENSION => 'Upload bị chặn bởi extension'
        ];

        $error_code = $file['error'] ?? 0;
        $error_message = $error_messages[$error_code] ?? 'Lỗi không xác định';



        // Lưu lỗi vào cache để xử lý sau
        $upload_errors = wp_cache_get('upload_errors', 'jobportal_import') ?: [];
        $upload_errors[] = [
            'file' => $file['name'],
            'error' => $error_message,
            'time' => current_time('mysql')
        ];
        wp_cache_set('upload_errors', $upload_errors, 'jobportal_import', 3600);
    }

    /**
     * Tạo placeholder image khi upload lỗi
     */
    private function create_placeholder_image($file)
    {
        try {
            if (!function_exists('imagecreate')) {

                return $file;
            }
            $upload_dir = wp_upload_dir();
            $placeholder_name = 'placeholder-' . sanitize_file_name($file['name']);
            $placeholder_path = $upload_dir['path'] . '/' . $placeholder_name;

            $image = imagecreate(300, 200);
            $bg_color = imagecolorallocate($image, 240, 240, 240);
            $text_color = imagecolorallocate($image, 100, 100, 100);

            $text = 'Image Placeholder';
            imagestring($image, 3, 100, 90, $text, $text_color);

            imagejpeg($image, $placeholder_path, 80);
            imagedestroy($image);

            $file['tmp_name'] = $placeholder_path;
            $file['size'] = filesize($placeholder_path);
            $file['type'] = 'image/jpeg';
            $file['error'] = UPLOAD_ERR_OK;
        } catch (Exception $e) {
            error_log("JobPortal Import: Failed to create placeholder: " . $e->getMessage());
        }

        return $file;
    }

    /**
     * Fix FontAwesome icons bị thiếu
     */
    public function fix_fontawesome_icons()
    {
        // Chỉ chạy khi có Elementor
        if (!defined('ELEMENTOR_VERSION')) {
            return;
        }

        // Hook vào FontAwesome để thêm các icon bị thiếu
        add_filter('pre_option_elementor_load_fa4_shim', '__return_true');
        add_action('wp_enqueue_scripts', [$this, 'enqueue_fontawesome_fixes']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_fontawesome_fixes']);

        // Suppress FontAwesome warnings trong admin
        if (is_admin()) {
            add_action('admin_init', [$this, 'suppress_fontawesome_warnings']);
        }
    }

    /**
     * Enqueue FontAwesome fixes
     */
    public function enqueue_fontawesome_fixes()
    {
        // Thêm CSS để fix các icon bị thiếu
        wp_add_inline_style('elementor-frontend', $this->get_fontawesome_fixes_css());
    }

    /**
     * CSS fixes cho FontAwesome icons
     */
    private function get_fontawesome_fixes_css()
    {
        return '
        /* FontAwesome icon fixes */
        .fa-wallet:before { content: "\f555"; }
        .fas.fa-wallet:before { content: "\f555"; }
        .far.fa-wallet:before { content: "\f555"; }

        .fa-shield-alt:before { content: "\f3ed"; }
        .fas.fa-shield-alt:before { content: "\f3ed"; }
        .far.fa-shield-alt:before { content: "\f3ed"; }

        .fa-shield-check:before { content: "\f2f7"; }
        .fas.fa-shield-check:before { content: "\f2f7"; }
        .far.fa-shield-check:before { content: "\f2f7"; }

        .fa-briefcase:before { content: "\f0b1"; }
        .fas.fa-briefcase:before { content: "\f0b1"; }
        .far.fa-briefcase:before { content: "\f0b1"; }

        .fa-user-md-chat:before { content: "\f0f0"; }
        .fas.fa-user-md-chat:before { content: "\f0f0"; }
        .far.fa-user-md-chat:before { content: "\f0f0"; }

        /* Fallback icons cho missing icons */
        .fa-user-md-chat:before { content: "\f086"; } /* chat icon */
        ';
    }

    /**
     * Thêm missing FontAwesome icons vào Elementor
     */
    public function add_missing_fontawesome_icons($tabs)
    {
        if (!isset($tabs['fa-solid'])) {
            return $tabs;
        }

        // Thêm các icon bị thiếu vào FontAwesome tab
        $missing_icons = [
            'wallet' => 'Wallet',
            'shield-alt' => 'Shield Alt',
            'shield-check' => 'Shield Check',
            'briefcase' => 'Briefcase',
            'user-md-chat' => 'User MD Chat',
        ];

        foreach ($missing_icons as $icon_key => $icon_label) {
            if (!isset($tabs['fa-solid']['icons'][$icon_key])) {
                $tabs['fa-solid']['icons'][$icon_key] = $icon_label;
            }
        }

        return $tabs;
    }

    /**
     * Suppress FontAwesome warnings
     */
    public function suppress_fontawesome_warnings()
    {
        // Set error handler để suppress FontAwesome warnings
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            // Suppress FontAwesome undefined array key warnings
            if (
                strpos($errstr, 'Undefined array key') !== false &&
                strpos($errfile, 'font-awesome.php') !== false
            ) {
                return true; // Suppress the warning
            }

            // Suppress specific FontAwesome icons warnings
            $suppressed_icons = ['wallet', 'shield-alt', 'shield-check', 'briefcase', 'user-md-chat'];
            foreach ($suppressed_icons as $icon) {
                if (strpos($errstr, "\"$icon\"") !== false) {
                    return true;
                }
            }

            return false; // Let other errors through
        }, E_WARNING | E_NOTICE);
    }

    /**
     * Manual retry failed media imports
     */
    public function retry_failed_media_imports()
    {
        $upload_errors = wp_cache_get('upload_errors', 'jobportal_import') ?: [];

        if (empty($upload_errors)) {
            return 0;
        }

        $retry_count = 0;
        foreach ($upload_errors as $error) {
            if ($this->retry_single_media_import($error['file'])) {
                $retry_count++;
            }
        }

        wp_cache_delete('upload_errors', 'jobportal_import');

        return $retry_count;
    }

    private function retry_single_media_import($filename)
    {
        try {
            $demo_assets_path = plugin_dir_path(__DIR__) . 'assets/import/';
            $possible_paths = [
                $demo_assets_path . '01/' . $filename,
                $demo_assets_path . '02/' . $filename,
                $demo_assets_path . '01/images/' . $filename,
                $demo_assets_path . '02/images/' . $filename,
            ];

            foreach ($possible_paths as $source_path) {
                if (file_exists($source_path)) {
                    return $this->import_media_file($source_path, $filename);
                }
            }


            return false;
        } catch (Exception $e) {
            error_log("JobPortal Import: Error retrying media import for {$filename}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Import một media file cụ thể
     */
    private function import_media_file($source_path, $filename)
    {
        try {
            $upload_dir = wp_upload_dir();
            $target_path = $upload_dir['path'] . '/' . $filename;

            // Copy file
            if (!copy($source_path, $target_path)) {
                return false;
            }

            // Create attachment
            $attachment = [
                'guid' => $upload_dir['url'] . '/' . $filename,
                'post_mime_type' => wp_check_filetype($filename)['type'],
                'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
                'post_content' => '',
                'post_status' => 'inherit'
            ];

            $attachment_id = wp_insert_attachment($attachment, $target_path);

            if (!is_wp_error($attachment_id)) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                $attachment_data = wp_generate_attachment_metadata($attachment_id, $target_path);
                wp_update_attachment_metadata($attachment_id, $attachment_data);

                return true;
            }

            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Final cleanup specifically for Freelancer demo using Elementor's replace_urls
     * Runs after all other cleanup operations
     */
    private function run_final_freelancer_cleanup()
    {


        $site_url = untrailingslashit(home_url());

        // Define URL pairs for replacement (old -> new)
        $url_replacements = [
            // Primary multisite URL cleanup
            $site_url . '/wp-content/uploads/sites/2/' => $site_url . '/wp-content/uploads/',
            '/wp-content/uploads/sites/2/' => '/wp-content/uploads/',

            // Handle both HTTP and HTTPS
            str_replace('http://', 'https://', $site_url) . '/wp-content/uploads/sites/2/' => str_replace('http://', 'https://', $site_url) . '/wp-content/uploads/',
            str_replace('https://', 'http://', $site_url) . '/wp-content/uploads/sites/2/' => str_replace('https://', 'http://', $site_url) . '/wp-content/uploads/',

            // Original demo URLs
            'https://jobportal.example.com/freelance/wp-content/uploads/sites/2/' => $site_url . '/wp-content/uploads/',
            'https://jobportal.example.com/wp-content/uploads/sites/2/' => $site_url . '/wp-content/uploads/',
        ];

        // Use Elementor's powerful URL replacement first (most effective)
        if (defined('ELEMENTOR_VERSION') && class_exists('\Elementor\Utils')) {
            foreach ($url_replacements as $old_url => $new_url) {
                if ($old_url !== $new_url && !empty($old_url)) {
                    // Validate URLs before using Elementor's replace_urls
                    $is_valid_url = function_exists('filter_var') && defined('FILTER_VALIDATE_URL') ? filter_var($old_url, FILTER_VALIDATE_URL) : true;
                    if ($is_valid_url || (strpos($old_url, 'http') !== 0 && strpos($old_url, '/') === 0)) {

                        try {
                            if (method_exists('\Elementor\Utils', 'replace_urls')) {
                                \Elementor\Utils::replace_urls($old_url, $new_url);
                            }
                        } catch (Exception $e) {
                        }
                    } else {
                    }
                }
            }
        } else {
        }

        // Clear all caches after cleanup
        $this->clear_all_caches_after_cleanup();
    }

    /**
     * Manual final cleanup as fallback
     */
    private function manual_final_cleanup()
    {
        global $wpdb;

        $site_url = untrailingslashit(home_url());

        // More comprehensive patterns for final cleanup
        $final_patterns = [
            '/wp-content/uploads/sites/2/' => '/wp-content/uploads/',
            '/sites/2/' => '/',
            $site_url . '/wp-content/uploads/sites/2/' => $site_url . '/wp-content/uploads/',
            'https://' . str_replace(['http://', 'https://'], '', $site_url) . '/wp-content/uploads/sites/2/' => 'https://' . str_replace(['http://', 'https://'], '', $site_url) . '/wp-content/uploads/',
            'http://' . str_replace(['http://', 'https://'], '', $site_url) . '/wp-content/uploads/sites/2/' => 'http://' . str_replace(['http://', 'https://'], '', $site_url) . '/wp-content/uploads/',
        ];

        $total_updated = 0;

        // Clean all tables that might contain URLs
        $tables_to_clean = [
            $wpdb->posts => ['post_content', 'post_excerpt'],
            $wpdb->postmeta => ['meta_value'],
            $wpdb->options => ['option_value'],
            $wpdb->comments => ['comment_content'],
            $wpdb->termmeta => ['meta_value'],
        ];

        foreach ($final_patterns as $old_pattern => $new_pattern) {
            foreach ($tables_to_clean as $table => $columns) {
                foreach ($columns as $column) {
                    $result = $wpdb->query(
                        $wpdb->prepare(
                            "UPDATE {$table}
                             SET {$column} = REPLACE({$column}, %s, %s)
                             WHERE {$column} LIKE %s",
                            $old_pattern,
                            $new_pattern,
                            '%' . $wpdb->esc_like($old_pattern) . '%'
                        )
                    );
                    if ($result !== false) {
                        $total_updated += $result;
                    }
                }
            }
        }
    }

    /**
     * Clear all caches after cleanup operations
     */
    private function clear_all_caches_after_cleanup()
    {
        // WordPress core cache
        wp_cache_flush();

        // Elementor cache
        if (class_exists('\Elementor\Plugin') && isset(\Elementor\Plugin::$instance)) {
            if (isset(\Elementor\Plugin::$instance->files_manager)) {
                \Elementor\Plugin::$instance->files_manager->clear_cache();
            }
            if (isset(\Elementor\Plugin::$instance->posts_css_manager)) {
                \Elementor\Plugin::$instance->posts_css_manager->clear_cache();
            }
        }

        // Popular caching plugins
        if (function_exists('w3tc_flush_all')) {
            w3tc_flush_all();
        }
        if (function_exists('wp_cache_clear_cache')) {
            wp_cache_clear_cache();
        }
        if (function_exists('rocket_clean_domain')) {
            rocket_clean_domain();
        }
    }

    /**
     * Validate import file before processing
     */
    public function validate_import_file($file_path, $selected_import)
    {
        if (empty($file_path) || !file_exists($file_path)) {
            return new WP_Error('file_not_found', 'Import file not found: ' . $file_path);
        }

        $file_size = filesize($file_path);
        if ($file_size === 0) {
            return new WP_Error('file_empty', 'Import file is empty: ' . $file_path);
        }

        if (pathinfo($file_path, PATHINFO_EXTENSION) === 'xml') {
            $xml_content = file_get_contents($file_path);
            if (strpos($xml_content, '<rss') === false && strpos($xml_content, '<?xml') === false) {
                return new WP_Error('invalid_xml', 'Invalid XML file format: ' . $file_path);
            }
        }

        return $file_path;
    }

    /**
     * Initialize the demo importer
     */
    public static function init()
    {
        return new self();
    }
}

// Initialize the demo importer
JobPortal_Demo_Importer::init();
