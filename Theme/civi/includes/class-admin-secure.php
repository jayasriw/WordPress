<?php
if (!defined('ABSPATH')) {
    exit;
}

class AdminSecurityHandler
{
    private $blocked_roles = array(
        'civi_user_candidate',
        'civi_user_employer',
        'customer',
        'subscriber',
        'restricted_user'
    );

    public function __construct()
    {
        add_action('init', array($this, 'create_custom_user_role'));
        add_action('admin_init', array($this, 'block_admin_access'));
        add_action('after_setup_theme', array($this, 'hide_admin_bar_for_non_admins'));
        add_action('ajax_query_attachments_args', array($this, 'restrict_media_library_ajax'));
        add_action('admin_menu', array($this, 'remove_admin_menus'));
        add_filter('posts_where', array($this, 'restrict_media_library_query'));
        add_action('wp_head', array($this, 'add_redirect_notice_script'));
    }

    public function block_admin_access()
    {
        if (!is_admin()) {
            return;
        }

        if (wp_doing_ajax()) {
            return;
        }

        if (!is_user_logged_in()) {
            $this->handle_non_logged_in_user();
            return;
        }

        if ($this->should_allow_access()) {
            return;
        }

        $this->handle_blocked_user();
    }

    private function handle_non_logged_in_user()
    {
        global $pagenow;
        if (in_array($pagenow, array('wp-login.php', 'wp-register.php'))) {
            return;
        }

        $redirect_to = $this->get_secure_redirect_url();
        $redirect_to = apply_filters('admin_security_login_redirect', $redirect_to);

        wp_safe_redirect(wp_login_url($redirect_to));
        exit;
    }

    private function handle_blocked_user()
    {
        $this->log_access_attempt();
        $redirect_url = $this->get_homepage_redirect_url();
        $redirect_url = apply_filters('admin_security_non_admin_redirect', $redirect_url);

        wp_safe_redirect($redirect_url);
        exit;
    }

    private function get_homepage_redirect_url()
    {
        $front_page_id = get_option('page_on_front');

        if ($front_page_id && $front_page_id != 0) {
            $redirect_url = get_permalink($front_page_id);
        } else {
            $redirect_url = home_url('/');
        }

        $add_redirect_notice = apply_filters('admin_security_add_redirect_notice', true);
        if ($add_redirect_notice) {
            $redirect_url = add_query_arg('admin_redirect', '1', $redirect_url);
        }

        return $redirect_url;
    }

    private function get_secure_redirect_url()
    {
        $redirect_to = admin_url();

        if (isset($_SERVER['REQUEST_URI'])) {
            $request_uri = $_SERVER['REQUEST_URI'];

            if (strpos($request_uri, '/wp-admin/') === 0) {
                $redirect_to = admin_url(ltrim($request_uri, '/wp-admin/'));
            }
        }

        $redirect_to = wp_sanitize_redirect($redirect_to);

        return $redirect_to;
    }

    private function log_access_attempt()
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $current_user = wp_get_current_user();
            $attempted_page = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'unknown';

            error_log(sprintf(
                'Admin access denied - User: %s (ID: %d) attempted to access: %s',
                $current_user->user_login,
                $current_user->ID,
                $attempted_page
            ));
        }
    }

    public function hide_admin_bar_for_non_admins()
    {
        if (!is_user_logged_in()) {
            return;
        }

        if (!$this->should_allow_access()) {
            show_admin_bar(false);
        }
    }

    public function restrict_media_library_ajax($query)
    {
        if (!is_user_logged_in() || current_user_can('administrator')) {
            return $query;
        }

        $query['author'] = get_current_user_id();
        return $query;
    }

    public function restrict_media_library_query($where)
    {
        global $wpdb, $pagenow;

        if (!is_admin() || !is_user_logged_in() || current_user_can('administrator')) {
            return $where;
        }

        if ('upload.php' === $pagenow || 'media-upload.php' === $pagenow) {
            $where .= " AND {$wpdb->posts}.post_author = " . get_current_user_id();
        }

        return $where;
    }

    public function create_custom_user_role()
    {
        if (!get_role('restricted_user')) {
            add_role('restricted_user', __('Restricted User', 'civi'), array(
                'read' => true,
                'edit_posts' => false,
                'upload_files' => true,
                'edit_pages' => false,
                'publish_posts' => false,
                'delete_posts' => false,
                'edit_profile' => true,
            ));
        }
    }

    public function remove_admin_menus()
    {
        if (!is_user_logged_in() || $this->should_allow_access()) {
            return;
        }

        $restricted_menus = array(
            'edit.php',
            'edit.php?post_type=page',
            'edit-comments.php',
            'themes.php',
            'plugins.php',
            'users.php',
            'tools.php',
            'options-general.php',
        );

        foreach ($restricted_menus as $menu) {
            remove_menu_page($menu);
        }

        remove_submenu_page('upload.php', 'media-new.php');
    }

    public function add_redirect_notice_script()
    {
        if (!isset($_GET['admin_redirect']) || $_GET['admin_redirect'] !== '1') {
            return;
        }

        if (!is_user_logged_in()) {
            return;
        }

        $current_user = wp_get_current_user();
        $user_display_name = $current_user->display_name ?: $current_user->user_login;

        $message = apply_filters(
            'admin_security_redirect_message',
            sprintf(__('Hello %s! You do not have permission to access the admin area.', 'civi'), $user_display_name)
        );

        $show_notice = apply_filters('admin_security_show_redirect_notice', true);

        if ($show_notice && $message): ?>
            <script type="text/javascript">
                document.addEventListener('DOMContentLoaded', function() {
                    var notice = document.createElement('div');
                    notice.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #f0ad4e;
                color: white;
                padding: 15px 20px;
                border-radius: 5px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.2);
                z-index: 9999;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                font-size: 14px;
                max-width: 300px;
                opacity: 0;
                transform: translateX(100%);
                transition: all 0.3s ease;
            `;
                    notice.innerHTML = `
                <?php echo esc_js($message); ?>
                <button onclick="this.parentElement.remove()" style="
                    background: none;
                    border: none;
                    color: white;
                    font-size: 18px;
                    float: right;
                    cursor: pointer;
                    margin-left: 10px;
                    line-height: 1;
                ">&times;</button>
            `;

                    document.body.appendChild(notice);

                    setTimeout(function() {
                        notice.style.opacity = '1';
                        notice.style.transform = 'translateX(0)';
                    }, 100);

                    setTimeout(function() {
                        notice.style.opacity = '0';
                        notice.style.transform = 'translateX(100%)';
                        setTimeout(function() {
                            if (notice.parentNode) {
                                notice.parentNode.removeChild(notice);
                            }
                        }, 300);
                    }, 5000);
                });
            </script>
<?php endif;
    }

    private function should_allow_access()
    {
        // Always allow if user has manage_options or edit_posts capability
        if (current_user_can('manage_options') || current_user_can('edit_posts')) {
            return true;
        }

        $current_user = wp_get_current_user();
        $user_roles = (array) $current_user->roles;

        // If user has no roles, block them (or handle as needed, but standard users usually have a role)
        if (empty($user_roles)) {
            return false;
        }

        // Check if ALL of the user's roles are in the blocked list
        $cleanup_roles = array_values($user_roles);
        $blocked_intersection = array_intersect($cleanup_roles, $this->blocked_roles);

        // If the number of blocked roles matches the total number of user roles,
        // it means all their roles are blocked.
        if (count($blocked_intersection) === count($cleanup_roles)) {
            return false;
        }

        // User has at least one role that is NOT blocked
        return true;
    }
}

new AdminSecurityHandler();
