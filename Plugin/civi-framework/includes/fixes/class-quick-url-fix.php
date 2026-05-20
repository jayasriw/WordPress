<?php

/**
 * Quick URL Fix
 * Immediate solution for fixing /sites/2/ URLs using Elementor Utils
 */

if (!defined('ABSPATH')) {
    exit;
}

class Civi_Quick_URL_Fix
{
    /**
     * Run immediate fix for /sites/2/ URLs
     * Call this function to fix URLs right away
     */
    public static function fix_sites_2_urls()
    {
        if (!defined('ELEMENTOR_VERSION') || !class_exists('\Elementor\Utils')) {
            error_log('Quick URL Fix: Elementor not available, skipping');
            return false;
        }

        $site_url = untrailingslashit(home_url());

        // URLs to replace
        $url_replacements = [
            // Current domain with /sites/2/
            $site_url . '/wp-content/uploads/sites/2/' => $site_url . '/wp-content/uploads/',

            // Generic /sites/2/ paths
            '/wp-content/uploads/sites/2/' => '/wp-content/uploads/',

            // Original demo domain
            'https://civi.uxper.co/freelance/wp-content/uploads/sites/2/' => $site_url . '/wp-content/uploads/',
            'https://civi.uxper.co/wp-content/uploads/sites/2/' => $site_url . '/wp-content/uploads/',

            // Both HTTP and HTTPS versions
            str_replace('http://', 'https://', $site_url) . '/wp-content/uploads/sites/2/' => str_replace('http://', 'https://', $site_url) . '/wp-content/uploads/',
            str_replace('https://', 'http://', $site_url) . '/wp-content/uploads/sites/2/' => str_replace('https://', 'http://', $site_url) . '/wp-content/uploads/',
        ];

        $replaced_count = 0;

        foreach ($url_replacements as $old_url => $new_url) {
            if ($old_url !== $new_url && !empty($old_url)) {
                try {
                    \Elementor\Utils::replace_urls($old_url, $new_url);
                    error_log("Quick Fix: Replaced {$old_url} → {$new_url}");
                    $replaced_count++;
                } catch (Exception $e) {
                    error_log("Quick Fix Error: " . $e->getMessage());
                }
            }
        }

        // Clear caches
        self::clear_caches();

        error_log("Quick URL Fix completed: {$replaced_count} URL patterns processed");

        return $replaced_count;
    }

    /**
     * Check if URLs need fixing
     */
    public static function check_sites_2_urls()
    {
        global $wpdb;

        // Quick check for /sites/2/ in database
        $count = $wpdb->get_var(
            "SELECT COUNT(*) FROM (
                SELECT 1 FROM {$wpdb->posts} WHERE post_content LIKE '%/sites/2/%'
                UNION ALL
                SELECT 1 FROM {$wpdb->postmeta} WHERE meta_value LIKE '%/sites/2/%'
                UNION ALL
                SELECT 1 FROM {$wpdb->options} WHERE option_value LIKE '%/sites/2/%'
                LIMIT 1
            ) as check_urls"
        );

        return intval($count) > 0;
    }

    /**
     * Display admin notice if URLs need fixing
     */
    public static function maybe_show_fix_notice()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (self::check_sites_2_urls()) {
            add_action('admin_notices', function() {
                ?>
                <div class="notice notice-warning is-dismissible" id="civi-fix-urls-notice">
                    <p>
                        <strong>⚠️ Phát hiện URLs không đúng:</strong>
                        Có media URLs chứa <code>/sites/2/</code> cần được sửa.
                        <a href="<?php echo admin_url('tools.php?page=civi-elementor-url-replace'); ?>" class="button button-primary">🔧 Sửa ngay</a>
                        <button type="button" class="button" onclick="civiQuickFixUrls()">⚡ Quick Fix</button>
                    </p>
                </div>

                <script>
                function civiQuickFixUrls() {
                    if (!confirm('Chạy Quick Fix để loại bỏ /sites/2/ URLs?\n\nLưu ý: Nên backup database trước!')) {
                        return;
                    }

                    const notice = document.getElementById('civi-fix-urls-notice');
                    notice.innerHTML = '<p>⏳ Đang chạy Quick Fix...</p>';

                    fetch(ajaxurl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'action=civi_quick_fix_urls&_wpnonce=<?php echo wp_create_nonce('civi_quick_fix'); ?>'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            notice.innerHTML = '<p style="color: green;">✅ ' + data.data.message + '</p>';
                            setTimeout(() => notice.style.display = 'none', 5000);
                        } else {
                            notice.innerHTML = '<p style="color: red;">❌ Lỗi: ' + data.data + '</p>';
                        }
                    })
                    .catch(error => {
                        notice.innerHTML = '<p style="color: red;">❌ Lỗi: ' + error.message + '</p>';
                    });
                }
                </script>
                <?php
            });
        }
    }

    /**
     * AJAX handler for quick fix
     */
    public static function ajax_quick_fix()
    {
        if (!check_ajax_referer('civi_quick_fix', '_wpnonce', false)) {
            wp_die('Security check failed');
        }

        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }

        $replaced_count = self::fix_sites_2_urls();

        if ($replaced_count !== false) {
            wp_send_json_success([
                'message' => "Quick Fix hoàn thành! Đã xử lý {$replaced_count} URL patterns. Cache đã được clear."
            ]);
        } else {
            wp_send_json_error('Elementor không có sẵn hoặc có lỗi xảy ra');
        }
    }

    /**
     * Clear all caches
     */
    private static function clear_caches()
    {
        wp_cache_flush();

        if (class_exists('\Elementor\Plugin')) {
            \Elementor\Plugin::$instance->files_manager->clear_cache();
            if (method_exists(\Elementor\Plugin::$instance, 'posts_css_manager')) {
                \Elementor\Plugin::$instance->posts_css_manager->clear_cache();
            }
        }

        if (function_exists('w3tc_flush_all')) {
            w3tc_flush_all();
        }
    }

        /**
     * Initialize hooks
     */
    public static function init()
    {
        // Add AJAX handler
        add_action('wp_ajax_civi_quick_fix_urls', [__CLASS__, 'ajax_quick_fix']);

        // Check and show notice on admin pages
        add_action('admin_init', [__CLASS__, 'maybe_show_fix_notice']);

        // Temporarily disabled auto-fix to prevent import conflicts
        // add_action('ocdi/after_import', [__CLASS__, 'auto_fix_after_import'], 999);
    }

        /**
     * Auto-run URL fix after demo import completes
     * Uses delayed approach to prevent import conflicts
     */
    public static function auto_fix_after_import($selected_import)
    {
        try {
            if (!isset($selected_import['import_file_name']) ||
                $selected_import['import_file_name'] !== 'Civi Freelancer') {
                return;
            }

            // Clear any existing scheduled events first
            $timestamp = wp_next_scheduled('civi_delayed_url_fix');
            if ($timestamp) {
                wp_unschedule_event($timestamp, 'civi_delayed_url_fix');
            }

            // Schedule new delayed event
            wp_schedule_single_event(time() + 30, 'civi_delayed_url_fix');

            // Register the hook
            if (!has_action('civi_delayed_url_fix', [__CLASS__, 'run_delayed_url_fix'])) {
                add_action('civi_delayed_url_fix', [__CLASS__, 'run_delayed_url_fix']);
            }

            error_log("Scheduled delayed URL fix for 30 seconds after import completion");

        } catch (Exception $e) {
            error_log("Error scheduling delayed URL fix: " . $e->getMessage());
        }
    }

        /**
     * Run delayed URL fix (30 seconds after import)
     */
    public static function run_delayed_url_fix()
    {
        try {
            error_log("Running delayed URL fix...");

            // Ensure Elementor is available
            if (!defined('ELEMENTOR_VERSION') || !class_exists('\Elementor\Utils')) {
                error_log("Delayed URL fix: Elementor not available");
                return;
            }

            // Check if URLs still need fixing
            if (self::check_sites_2_urls()) {
                $result = self::fix_sites_2_urls();
                if ($result !== false) {
                    error_log("Delayed URL fix completed: {$result} URL patterns processed");
                } else {
                    error_log("Delayed URL fix: Failed to process URLs");
                }
            } else {
                error_log("Delayed URL fix: No URLs need fixing");
            }

        } catch (Exception $e) {
            error_log("Delayed URL fix error: " . $e->getMessage());
        }
    }
}

// Initialize
Civi_Quick_URL_Fix::init();
