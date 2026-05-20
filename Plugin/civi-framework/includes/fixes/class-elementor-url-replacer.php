<?php

/**
 * Elementor URL Replacer
 * Quick tool to replace URLs using Elementor's powerful replace_urls method
 */

if (!defined('ABSPATH')) {
    exit;
}

class Civi_Elementor_URL_Replacer
{
    private static $_instance = null;

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        // Add admin menu
        add_action('admin_menu', [$this, 'add_admin_menu']);

        // Add AJAX handler
        add_action('wp_ajax_civi_elementor_replace_urls', [$this, 'ajax_replace_urls']);

        // Add admin bar quick link for admins
        add_action('admin_bar_menu', [$this, 'add_admin_bar_link'], 999);
    }

    /**
     * Add admin menu item (currently disabled for future release)
     */
    public function add_admin_menu()
    {
        // Disabled for current release - will be enabled in future update
        return;

        add_submenu_page(
            'tools.php',
            'Elementor URL Replace',
            'Elementor URL Replace',
            'manage_options',
            'civi-elementor-url-replace',
            [$this, 'admin_page']
        );
    }

    /**
     * Add admin bar quick link (currently disabled for future release)
     */
    public function add_admin_bar_link($wp_admin_bar)
    {
        // Disabled for current release - will be enabled in future update
        return;

        if (!current_user_can('manage_options')) {
            return;
        }

        $wp_admin_bar->add_node([
            'id'    => 'civi-fix-urls',
            'title' => '🔧 Civi Fix URLs',
            'href'  => admin_url('tools.php?page=civi-elementor-url-replace'),
            'meta'  => [
                'title' => 'Quick URL replacement using Elementor Utils'
            ]
        ]);
    }

    /**
     * Admin page
     */
    public function admin_page()
    {
        $current_site_url = untrailingslashit(home_url());

                ?>
        <div class="wrap">
            <h1>Elementor URL Replacer</h1>
            <p>Advanced URL replacement tool using <code>\Elementor\Utils::replace_urls()</code> API.</p>

            <?php if (!defined('ELEMENTOR_VERSION')): ?>
                <div class="notice notice-error">
                    <p><strong>Error:</strong> Elementor is not installed or activated!</p>
                </div>
                <?php return; ?>
            <?php endif; ?>

            <div class="card" style="max-width: 800px;">
                <h2>Quick Fix - Remove /sites/2/ URLs</h2>
                <p>Automatically remove all URLs containing <code>/sites/2/</code> from media files.</p>

                <div style="background: #f0f6fc; padding: 15px; border-left: 4px solid #0073aa; margin: 15px 0;">
                    <h4>URLs to be replaced:</h4>
                    <ul style="margin: 10px 0;">
                        <li><code><?php echo $current_site_url; ?>/wp-content/uploads/sites/2/</code> → <code><?php echo $current_site_url; ?>/wp-content/uploads/</code></li>
                        <li><code>/wp-content/uploads/sites/2/</code> → <code>/wp-content/uploads/</code></li>
                        <li><code>https://civi.uxper.co/freelance/wp-content/uploads/sites/2/</code> → <code><?php echo $current_site_url; ?>/wp-content/uploads/</code></li>
                    </ul>
                </div>

                <p class="submit">
                    <button type="button" class="button-primary button-large" onclick="civiQuickFixSites2()" style="padding: 10px 20px;">
                        Quick Fix - Remove /sites/2/ URLs
                    </button>
                </p>
            </div>

            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h2>Custom URL Replacement</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Old URL</th>
                        <td>
                            <input type="url" id="old_url" class="regular-text" placeholder="https://old-domain.com" />
                            <p class="description">URL to be replaced</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">New URL</th>
                        <td>
                            <input type="url" id="new_url" class="regular-text" placeholder="<?php echo $current_site_url; ?>" value="<?php echo $current_site_url; ?>" />
                            <p class="description">Replacement URL</p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="button" class="button-secondary" onclick="civiCustomReplace()">
                        Replace Custom URLs
                    </button>
                </p>
            </div>

            <div id="replacement-results" style="margin-top: 20px;"></div>

            <div class="card" style="max-width: 800px; margin-top: 20px; background: #fff3cd; border-left: 4px solid #ffc107;">
                <h3>Important Notes</h3>
                <ul>
                    <li><strong>Backup database</strong> before running URL replacement</li>
                    <li>Uses <code>\Elementor\Utils::replace_urls()</code> - same API as Elementor Tools</li>
                    <li>Automatically clears cache after completion</li>
                    <li>Works on all post types, meta data, and Elementor data</li>
                </ul>
            </div>

            <div class="card" style="max-width: 800px; margin-top: 20px; background: #d1ecf1; border-left: 4px solid #17a2b8;">
                <h3>Auto-Run After Import</h3>
                <p>This tool <strong>automatically runs</strong> in the following cases:</p>
                <ul>
                    <li>After <strong>Freelancer demo</strong> import completes</li>
                    <li>Runs as the <strong>final step</strong> of import process</li>
                    <li>Only runs when URLs need fixing are detected</li>
                    <li>Includes <strong>delayed check</strong> after 30 seconds</li>
                </ul>
                <p><strong>Import execution order:</strong></p>
                <ol>
                    <li>Import content, widgets, customizer</li>
                    <li>Setup menus, pages, theme options</li>
                    <li>Domain replacement & basic cleanup</li>
                    <li>Fix company logos & avatars</li>
                    <li>Set permalinks</li>
                    <li><strong>Elementor URL Replace (FINAL STEP)</strong></li>
                    <li><strong>Delayed check after 30 seconds</strong></li>
                </ol>
            </div>
        </div>

        <script>
        function civiQuickFixSites2() {
            const button = event.target;
            const originalText = button.innerHTML;
                        button.innerHTML = 'Processing...';
            button.disabled = true;

            document.getElementById('replacement-results').innerHTML = '<div class="notice notice-info"><p>Removing /sites/2/ URLs...</p></div>';

            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=civi_elementor_replace_urls&type=quick_fix_sites2&_wpnonce=<?php echo wp_create_nonce('civi_elementor_replace'); ?>'
            })
            .then(response => response.json())
                        .then(data => {
                button.innerHTML = originalText;
                button.disabled = false;

                if (data.success) {
                    document.getElementById('replacement-results').innerHTML =
                        '<div class="notice notice-success"><p><strong>Success!</strong> ' + data.data.message + '</p></div>';
                } else {
                    document.getElementById('replacement-results').innerHTML =
                        '<div class="notice notice-error"><p><strong>Error:</strong> ' + data.data + '</p></div>';
                }
            })
            .catch(error => {
                button.innerHTML = originalText;
                button.disabled = false;
                document.getElementById('replacement-results').innerHTML =
                    '<div class="notice notice-error"><p><strong>Error:</strong> ' + error.message + '</p></div>';
            });
        }

        function civiCustomReplace() {
            const oldUrl = document.getElementById('old_url').value.trim();
            const newUrl = document.getElementById('new_url').value.trim();

            if (!oldUrl || !newUrl) {
                alert('Please enter both Old URL and New URL');
                return;
            }

            if (oldUrl === newUrl) {
                alert('Old URL and New URL cannot be the same');
                return;
            }

            if (!confirm('Are you sure you want to replace "' + oldUrl + '" with "' + newUrl + '"?')) {
                return;
            }

            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = 'Replacing...';
            button.disabled = true;

            document.getElementById('replacement-results').innerHTML = '<div class="notice notice-info"><p>Replacing URLs...</p></div>';

            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=civi_elementor_replace_urls&type=custom&old_url=' + encodeURIComponent(oldUrl) + '&new_url=' + encodeURIComponent(newUrl) + '&_wpnonce=<?php echo wp_create_nonce('civi_elementor_replace'); ?>'
            })
            .then(response => response.json())
            .then(data => {
                button.innerHTML = originalText;
                button.disabled = false;

                if (data.success) {
                    document.getElementById('replacement-results').innerHTML =
                        '<div class="notice notice-success"><p><strong>Success!</strong> ' + data.data.message + '</p></div>';
                } else {
                    document.getElementById('replacement-results').innerHTML =
                        '<div class="notice notice-error"><p><strong>Error:</strong> ' + data.data + '</p></div>';
                }
            })
            .catch(error => {
                button.innerHTML = originalText;
                button.disabled = false;
                document.getElementById('replacement-results').innerHTML =
                    '<div class="notice notice-error"><p><strong>Error:</strong> ' + error.message + '</p></div>';
            });
        }
        </script>
        <?php
    }

    /**
     * AJAX handler for URL replacement
     */
    public function ajax_replace_urls()
    {
        if (!check_ajax_referer('civi_elementor_replace', '_wpnonce', false)) {
            wp_die('Security check failed');
        }

        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }

        if (!defined('ELEMENTOR_VERSION') || !class_exists('\Elementor\Utils')) {
            wp_send_json_error('Elementor không có sẵn');
        }

        $type = sanitize_text_field($_POST['type']);
        $current_site_url = untrailingslashit(home_url());

        try {
            switch ($type) {
                case 'quick_fix_sites2':
                    $replacements = [
                        $current_site_url . '/wp-content/uploads/sites/2/' => $current_site_url . '/wp-content/uploads/',
                        '/wp-content/uploads/sites/2/' => '/wp-content/uploads/',
                        'https://civi.uxper.co/freelance/wp-content/uploads/sites/2/' => $current_site_url . '/wp-content/uploads/',
                        'https://civi.uxper.co/wp-content/uploads/sites/2/' => $current_site_url . '/wp-content/uploads/',
                    ];

                    foreach ($replacements as $old_url => $new_url) {
                        if ($old_url !== $new_url && !empty($old_url)) {
                            \Elementor\Utils::replace_urls($old_url, $new_url);
                            error_log("Elementor replaced: {$old_url} → {$new_url}");
                        }
                    }

                    $this->clear_all_caches();

                    wp_send_json_success([
                        'message' => 'Đã loại bỏ thành công tất cả /sites/2/ URLs. Cache đã được clear.',
                        'replacements' => count($replacements)
                    ]);
                    break;

                case 'custom':
                    $old_url = sanitize_url($_POST['old_url']);
                    $new_url = sanitize_url($_POST['new_url']);

                    if (empty($old_url) || empty($new_url)) {
                        wp_send_json_error('URLs không hợp lệ');
                    }

                    if ($old_url === $new_url) {
                        wp_send_json_error('Old URL và New URL giống nhau');
                    }

                    \Elementor\Utils::replace_urls($old_url, $new_url);
                    error_log("Elementor custom replacement: {$old_url} → {$new_url}");

                    $this->clear_all_caches();

                    wp_send_json_success([
                        'message' => "Đã thay thế thành công '{$old_url}' bằng '{$new_url}'. Cache đã được clear.",
                        'old_url' => $old_url,
                        'new_url' => $new_url
                    ]);
                    break;

                default:
                    wp_send_json_error('Loại replacement không hợp lệ');
            }
        } catch (Exception $e) {
            error_log("Elementor URL replacement error: " . $e->getMessage());
            wp_send_json_error('Lỗi: ' . $e->getMessage());
        }
    }

    /**
     * Clear all caches after URL replacement
     */
    private function clear_all_caches()
    {
        // WordPress cache
        wp_cache_flush();

        // Elementor cache
        if (class_exists('\Elementor\Plugin')) {
            \Elementor\Plugin::$instance->files_manager->clear_cache();
            if (method_exists(\Elementor\Plugin::$instance, 'posts_css_manager')) {
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
        if (function_exists('sg_cachepress_purge_cache')) {
            sg_cachepress_purge_cache();
        }
    }
}

// Initialize the URL replacer
Civi_Elementor_URL_Replacer::instance();
