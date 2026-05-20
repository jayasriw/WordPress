<?php

/**
 *  Plugin Name: Civi Framework
 *  Plugin URI: https://uxper.co/
 *  Description: Civi Framework.
 *  Version: 2.2.6
 *  Author: Uxper
 *  Author URI: https://uxper.co/
 *  Text Domain: civi-framework
 *
 *  @package Civi Framework
 *  @author uxper
 *
 **/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('Civi_Framework')) {
    class Civi_Framework
    {

        public function __construct()
        {
            if (!$this->is_civi_theme_active()) {
                add_action('admin_notices', array($this, 'theme_not_active_notice'));
                return;
            }

            $this->define_constants();
            $this->load_textdomain();

            register_deactivation_hook(__FILE__, array($this, 'civi_deactivate'));
            add_action('plugins_loaded', array($this, 'includes'));
            add_filter('upload_mimes', array($this, 'civi_svg_upload'));
            add_filter('wp_handle_upload_prefilter', array($this, 'sanitize_svg_upload'));
            add_filter('kirki/config', array($this, 'kirki_update_url'), 10, 1);

            if (is_multisite()) {
                $blog_id = get_current_blog_id();
                $upload_path = WP_CONTENT_DIR . '/uploads/sites/' . $blog_id . '/';
            }
        }

        /**
         * Check Civi theme is active
         */
        private function is_civi_theme_active()
        {
            $theme = wp_get_theme();
            $parent_theme = wp_get_theme(get_template());

            // Check current theme and parent theme
            $theme_name = strtolower($theme->get('Name'));
            $theme_slug = strtolower($theme->get_stylesheet());
            $parent_name = strtolower($parent_theme->get('Name'));
            $parent_slug = strtolower(get_template());

            // Chấp nhận nếu theme hoặc parent theme có chứa "civi"
            return (strpos($theme_name, 'civi') !== false ||
                    strpos($theme_slug, 'civi') !== false ||
                    strpos($parent_name, 'civi') !== false ||
                    strpos($parent_slug, 'civi') !== false);
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
                    <strong><?php esc_html_e('Civi Framework Notice:', 'civi-framework'); ?></strong>
                    <?php
                    printf(
                        esc_html__('Plugin Civi Framework yêu cầu theme Civi hoặc Civi Child phải được active. Theme hiện tại của bạn là: %s. Vui lòng active theme Civi để sử dụng plugin này.', 'civi-framework'),
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

            if (!defined('CIVI_PLUGIN_FILE')) {
                define('CIVI_PLUGIN_FILE', __FILE__);
            }

            if (!defined('CIVI_PLUGIN_NAME')) {
                define('CIVI_PLUGIN_NAME', $plugin_dir_name);
            }

            if (!defined('CIVI_PLUGIN_DIR')) {
                define('CIVI_PLUGIN_DIR', plugin_dir_path(__FILE__));
            }

            if (!defined('CIVI_PLUGIN_URL')) {
                define('CIVI_PLUGIN_URL', trailingslashit(plugins_url(CIVI_PLUGIN_NAME)));
            }

            if (!defined('CIVI_PLUGIN_PREFIX')) {
                define('CIVI_PLUGIN_PREFIX', 'civi');
            }

            if (!defined('CIVI_METABOX_PREFIX')) {
                define('CIVI_METABOX_PREFIX', 'civi-');
            }

            if (function_exists('pll_the_languages') && !defined(strtoupper(pll_current_language()) . '_' . 'CIVI_OPTIONS_NAME')) {
                define(strtoupper(pll_current_language()) . '_' . 'CIVI_OPTIONS_NAME', pll_current_language() . '_civi-framework');
            } else if (defined('ICL_SITEPRESS_VERSION')) {
                $current_language = apply_filters('wpml_current_language', NULL);

                if ($current_language) {
                    define(strtoupper($current_language) . '_' . 'CIVI_OPTIONS_NAME', $current_language . '_civi-framework');
                } else {
                    define('CIVI_OPTIONS_NAME', 'civi-framework');
                }
            } else {
                define('CIVI_OPTIONS_NAME', 'civi-framework');
            }

            if (!defined('CIVI_THEME_NAME')) {
                define('CIVI_THEME_NAME', $theme->get('Name'));
            }

            if (!defined('CIVI_THEME_SLUG')) {
                define('CIVI_THEME_SLUG', $theme->get_stylesheet());
            }

            if (!defined('CIVI_THEME_VERSION')) {
                define('CIVI_THEME_VERSION', $theme->get('Version'));
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

            if (!defined('CIVI_PLUGIN_VER')) {
                define('CIVI_PLUGIN_VER', $theme->get('Version'));
            }

            if (!defined('CIVI_AJAX_URL')) {
                $ajax_url = admin_url('admin-ajax.php', 'relative');
                define('CIVI_AJAX_URL', $ajax_url);
            }
        }

        public function load_textdomain()
        {
            $mofile = CIVI_PLUGIN_DIR . 'languages/' . 'civi-framework-' . get_locale() . '.mo';

            if (file_exists($mofile)) {
                load_textdomain('civi-framework', $mofile);
            }
        }

        /**
         * The code that runs during plugin deactivation.
         */
        public function civi_deactivate()
        {
            require_once CIVI_PLUGIN_DIR . 'includes/class-civi-deactivator.php';
            Civi_Deactivator::deactivate();
        }

        /**
         * Upload Svg
         */
        public function civi_svg_upload($mimes)
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
                require_once CIVI_PLUGIN_DIR . 'includes/class-civi-svg-sanitizer.php';
                
                $dirty_svg = file_get_contents($file['tmp_name']);
                $clean_svg = Civi_SVG_Sanitizer::sanitize($dirty_svg);
                
                if ($clean_svg === false) {
                    $file['error'] = __('SVG file contains potentially malicious code and was rejected for security reasons.', 'civi-framework');
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
                add_filter('civi_base_url', 'base_url', 1);

                function base_url()
                {
                    return CIVI_PLUGIN_URL . 'includes/base/';
                }
                require_once CIVI_PLUGIN_DIR . 'includes/base/base.php';
            }

            include_once(CIVI_PLUGIN_DIR . 'includes/fpdf/fpdf.php');

            // Security Helper
            include_once(CIVI_PLUGIN_DIR . 'includes/civi-security.php');

            // Core
            include_once(CIVI_PLUGIN_DIR . 'includes/class-civi-core.php');

            // Location Search
            include_once(CIVI_PLUGIN_DIR . 'includes/class-civi-location-search.php');

            // Kirki
            include_once(CIVI_PLUGIN_DIR . 'includes/kirki/kirki.php');

            // Base Widget
            include_once(CIVI_PLUGIN_DIR . 'modules/widgets/base.php');

            // Base Elementor
            include_once(CIVI_PLUGIN_DIR . 'modules/elementor/base.php');

            // Civi Import Data
            include_once(CIVI_PLUGIN_DIR . 'includes/class-civi-import.php');

            // Elementor Fixes
            include_once(CIVI_PLUGIN_DIR . 'includes/fixes/class-elementor-fixes.php');

            // PHP Error Suppressor for production
            include_once(CIVI_PLUGIN_DIR . 'includes/fixes/class-php-error-suppressor.php');


            // Elementor URL Replacer (Quick method)
            include_once(CIVI_PLUGIN_DIR . 'includes/fixes/class-elementor-url-replacer.php');

            // WooCommerce Integration
            include_once(CIVI_PLUGIN_DIR . 'includes/fixes/class-woocommerce-integration.php');

            // // Quick URL Fix (Immediate solution)
            // include_once(CIVI_PLUGIN_DIR . 'includes/fixes/class-quick-url-fix.php');
        }

        /**
         *  Kirki update url
         **/
        public function kirki_update_url($config)
        {
            $config['url_path'] = CIVI_PLUGIN_URL . '/includes/kirki/';

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

    new Civi_Framework();
}
