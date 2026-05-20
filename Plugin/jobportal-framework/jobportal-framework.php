<?php

/**
 *  Plugin Name: JobPortal Framework
 *  Plugin URI: https://jobportal.example.com/
 *  Description: JobPortal Framework - Core functionality plugin for the JobPortal theme.
 *  Version: 1.0.0
 *  Author: JobPortal Team
 *  Author URI: https://jobportal.example.com/
 *  Text Domain: jobportal-framework
 *
 *  @package JobPortal Framework
 *  @author jobportal
 *
 **/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('JobPortal_Framework')) {
    class JobPortal_Framework
    {

        public function __construct()
        {
            if (!$this->is_jobportal_theme_active()) {
                add_action('admin_notices', array($this, 'theme_not_active_notice'));
                return;
            }

            $this->define_constants();
            $this->load_textdomain();

            register_deactivation_hook(__FILE__, array($this, 'jobportal_deactivate'));
            add_action('plugins_loaded', array($this, 'includes'));
            add_filter('upload_mimes', array($this, 'jobportal_svg_upload'));
            add_filter('wp_handle_upload_prefilter', array($this, 'sanitize_svg_upload'));
            add_filter('kirki/config', array($this, 'kirki_update_url'), 10, 1);

            if (is_multisite()) {
                $blog_id = get_current_blog_id();
                $upload_path = WP_CONTENT_DIR . '/uploads/sites/' . $blog_id . '/';
            }
        }

        /**
         * Check JobPortal theme is active
         */
        private function is_jobportal_theme_active()
        {
            $theme = wp_get_theme();
            $parent_theme = wp_get_theme(get_template());

            // Check current theme and parent theme
            $theme_name = strtolower($theme->get('Name'));
            $theme_slug = strtolower($theme->get_stylesheet());
            $parent_name = strtolower($parent_theme->get('Name'));
            $parent_slug = strtolower(get_template());

            // Chấp nhận nếu theme hoặc parent theme có chứa "jobportal"
            return (strpos($theme_name, 'jobportal') !== false ||
                    strpos($theme_slug, 'jobportal') !== false ||
                    strpos($parent_name, 'jobportal') !== false ||
                    strpos($parent_slug, 'jobportal') !== false);
        }

        /**
         * Hiển thị thông báo khi theme không đúng
         */
        public function theme_not_active_notice()
        {
            $theme = wp_get_theme();
            ?>
            <div class="notice notice-error">
                <p>
                    <strong><?php esc_html_e('JobPortal Framework Notice:', 'jobportal-framework'); ?></strong>
                    <?php
                    printf(
                        esc_html__('Plugin JobPortal Framework requires the JobPortal or JobPortal Child theme to be active. Your current theme is: %s. Please activate the JobPortal theme to use this plugin.', 'jobportal-framework'),
                        '<strong>' . esc_html($theme->get('Name')) . '</strong>'
                    );
                    ?>
                </p>
            </div>
            <?php
        }

        /**
         *  Define constant
         **/
        private function define_constants()
        {
            $theme = wp_get_theme(get_template());

            $plugin_dir_name = dirname(__FILE__);
            $plugin_dir_name = str_replace('\\', '/', $plugin_dir_name);
            $plugin_dir_name = explode('/', $plugin_dir_name);
            $plugin_dir_name = end($plugin_dir_name);

            if (!defined('JOBPORTAL_PLUGIN_FILE')) {
                define('JOBPORTAL_PLUGIN_FILE', __FILE__);
            }

            if (!defined('JOBPORTAL_PLUGIN_NAME')) {
                define('JOBPORTAL_PLUGIN_NAME', $plugin_dir_name);
            }

            if (!defined('JOBPORTAL_PLUGIN_DIR')) {
                define('JOBPORTAL_PLUGIN_DIR', plugin_dir_path(__FILE__));
            }

            if (!defined('JOBPORTAL_PLUGIN_URL')) {
                define('JOBPORTAL_PLUGIN_URL', trailingslashit(plugins_url(JOBPORTAL_PLUGIN_NAME)));
            }

            if (!defined('JOBPORTAL_PLUGIN_PREFIX')) {
                define('JOBPORTAL_PLUGIN_PREFIX', 'jobportal');
            }

            if (!defined('JOBPORTAL_METABOX_PREFIX')) {
                define('JOBPORTAL_METABOX_PREFIX', 'jobportal-');
            }

            if (function_exists('pll_the_languages') && !defined(strtoupper(pll_current_language()) . '_' . 'JOBPORTAL_OPTIONS_NAME')) {
                define(strtoupper(pll_current_language()) . '_' . 'JOBPORTAL_OPTIONS_NAME', pll_current_language() . '_jobportal-framework');
            } else if (defined('ICL_SITEPRESS_VERSION')) {
                $current_language = apply_filters('wpml_current_language', NULL);

                if ($current_language) {
                    define(strtoupper($current_language) . '_' . 'JOBPORTAL_OPTIONS_NAME', $current_language . '_jobportal-framework');
                } else {
                    define('JOBPORTAL_OPTIONS_NAME', 'jobportal-framework');
                }
            } else {
                define('JOBPORTAL_OPTIONS_NAME', 'jobportal-framework');
            }

            if (!defined('JOBPORTAL_THEME_NAME')) {
                define('JOBPORTAL_THEME_NAME', $theme->get('Name'));
            }

            if (!defined('JOBPORTAL_THEME_SLUG')) {
                define('JOBPORTAL_THEME_SLUG', $theme->get_stylesheet());
            }

            if (!defined('JOBPORTAL_THEME_VERSION')) {
                define('JOBPORTAL_THEME_VERSION', $theme->get('Version'));
            }

            if (!defined('GLF_THEME_DIR')) {
                define('GLF_THEME_DIR', get_template_directory());
            }

            if (!defined('GLF_THEME_URL')) {
                define('GLF_THEME_URL', get_template_directory_uri());
            }

            if (!defined('GLF_THEME_SLUG')) {
                define('GLF_THEME_SLUG', get_template());
            }

            if (!defined('JOBPORTAL_PLUGIN_VER')) {
                define('JOBPORTAL_PLUGIN_VER', $theme->get('Version'));
            }

            if (!defined('JOBPORTAL_AJAX_URL')) {
                $ajax_url = admin_url('admin-ajax.php', 'relative');
                define('JOBPORTAL_AJAX_URL', $ajax_url);
            }
        }

        public function load_textdomain()
        {
            $mofile = JOBPORTAL_PLUGIN_DIR . 'languages/' . 'jobportal-framework-' . get_locale() . '.mo';

            if (file_exists($mofile)) {
                load_textdomain('jobportal-framework', $mofile);
            }
        }

        /**
         * The code that runs during plugin deactivation.
         */
        public function jobportal_deactivate()
        {
            require_once JOBPORTAL_PLUGIN_DIR . 'includes/class-jobportal-deactivator.php';
            JobPortal_Deactivator::deactivate();
        }

        /**
         * Upload Svg
         */
        public function jobportal_svg_upload($mimes)
        {
            $mimes['svg'] = 'image/svg+xml';
            return $mimes;
        }

        /**
         * Sanitize SVG uploads to prevent XSS attacks
         */
        public function sanitize_svg_upload($file)
        {
            if ($file['type'] === 'image/svg+xml') {
                // Load sanitizer class
                require_once JOBPORTAL_PLUGIN_DIR . 'includes/class-jobportal-svg-sanitizer.php';
                
                $dirty_svg = file_get_contents($file['tmp_name']);
                $clean_svg = JobPortal_SVG_Sanitizer::sanitize($dirty_svg);
                
                if ($clean_svg === false) {
                    $file['error'] = __('SVG file contains potentially malicious code and was rejected for security reasons.', 'jobportal-framework');
                } else {
                    file_put_contents($file['tmp_name'], $clean_svg);
                }
            }
            return $file;
        }

        /**
         *  Includes
         **/
        public function includes()
        {

            if (!class_exists('Base_Framework')) {
                add_filter('jobportal_base_url', 'base_url', 1);

                function base_url()
                {
                    return JOBPORTAL_PLUGIN_URL . 'includes/base/';
                }
                require_once JOBPORTAL_PLUGIN_DIR . 'includes/base/base.php';
            }

            include_once(JOBPORTAL_PLUGIN_DIR . 'includes/fpdf/fpdf.php');

            // Security Helper
            include_once(JOBPORTAL_PLUGIN_DIR . 'includes/jobportal-security.php');

            // Core
            include_once(JOBPORTAL_PLUGIN_DIR . 'includes/class-jobportal-core.php');

            // Location Search
            include_once(JOBPORTAL_PLUGIN_DIR . 'includes/class-jobportal-location-search.php');

            // Kirki
            include_once(JOBPORTAL_PLUGIN_DIR . 'includes/kirki/kirki.php');

            // Base Widget
            include_once(JOBPORTAL_PLUGIN_DIR . 'modules/widgets/base.php');

            // Base Elementor
            include_once(JOBPORTAL_PLUGIN_DIR . 'modules/elementor/base.php');

            // JobPortal Import Data
            include_once(JOBPORTAL_PLUGIN_DIR . 'includes/class-jobportal-import.php');

            // Elementor Fixes
            include_once(JOBPORTAL_PLUGIN_DIR . 'includes/fixes/class-elementor-fixes.php');

            // PHP Error Suppressor for production
            include_once(JOBPORTAL_PLUGIN_DIR . 'includes/fixes/class-php-error-suppressor.php');


            // Elementor URL Replacer (Quick method)
            include_once(JOBPORTAL_PLUGIN_DIR . 'includes/fixes/class-elementor-url-replacer.php');

            // WooCommerce Integration
            include_once(JOBPORTAL_PLUGIN_DIR . 'includes/fixes/class-woocommerce-integration.php');

            // // Quick URL Fix (Immediate solution)
            // include_once(JOBPORTAL_PLUGIN_DIR . 'includes/fixes/class-quick-url-fix.php');
        }

        /**
         *  Kirki update url
         **/
        public function kirki_update_url($config)
        {
            $config['url_path'] = JOBPORTAL_PLUGIN_URL . '/includes/kirki/';

            return $config;
        }

        /**
         *  Fix Upload Path Multisite
         **/
        public function fix_upload_paths($data)
        {
            $data['basedir'] = $data['basedir'] . '/sites/' . get_current_blog_id();
            $data['path'] = $data['basedir'] . $data['subdir'];
            $data['baseurl'] = $data['baseurl'] . '/sites/' . get_current_blog_id();
            $data['url'] = $data['baseurl'] . $data['subdir'];

            return $data;
        }
    }

    new JobPortal_Framework();
}
