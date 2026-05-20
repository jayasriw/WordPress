<?php

if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('Civi_User')) {

    class Civi_User
    {

        function __construct()
        {
            add_action('init', array($this, 'add_user_roles'));
            add_action('init', array($this, 'civi_deactive_user'));
            add_filter('pre_option_default_role', array($this, 'add_user_roles_default'));
            add_action('wp_footer', array($this, 'jobs_single_bottombar'));
            add_action('user_register', array($this, 'create_a_profile_post_for_new_candidate'), 10, 1);
            add_action('civi_delayed_avatar_sync_after_register', array($this, 'delayed_avatar_sync_after_register'), 10, 1);
            add_action('set_user_role', array($this, 'handle_role_change'), 10, 3);
            add_action('init', array($this, 'register_candidate_archive_cpt'));
            add_filter('wp_insert_post_data', array($this, 'prevent_locked_candidate_publish'), 10, 2);

            if (civi_get_option('enable_status_user') === '1') {
                add_filter('manage_users_columns', array($this, 'custom_add_user_column'));
                add_filter('manage_users_custom_column', array($this, 'custom_display_user_column_data'), 10, 3);
                add_filter('user_row_actions', array($this, 'add_user_status_action_link'), 10, 2);
                add_action('wp_ajax_civi_approve_user_status', array($this, 'ajax_approve_user_status'));
                add_action('admin_enqueue_scripts', array($this, 'enqueue_users_scripts'));
            }

            if (civi_get_option('enable_job_alerts') === '1') {
                add_action('wp_footer', array($this, 'job_alert_form'));
            }

            $enable_identity_verification  = civi_get_option('enable_identity_verification');
            if ($enable_identity_verification === '1') {
                add_filter('manage_users_columns', array($this, 'add_identity_verification_column'));
                add_filter('manage_users_custom_column', array($this, 'display_identity_verification_column_data'), 10, 3);
                add_filter('user_row_actions', array($this, 'add_custom_user_action_link'), 10, 2);
                add_action('init', array($this, 'handle_identity_verification'));
            }

            // Add custom columns to users list
            add_filter('manage_users_columns', array($this, 'add_user_custom_columns'));
            add_filter('manage_users_custom_column', array($this, 'display_user_custom_column_data'), 10, 3);
            add_filter('manage_users_sortable_columns', array($this, 'add_user_sortable_columns'));
            add_action('pre_get_users', array($this, 'handle_user_sorting'));
            add_filter('users_clauses', array($this, 'handle_user_post_count_sorting'), 10, 2);
        // Thêm meta box vào user profile
        add_action('show_user_profile', array($this, 'civi_identity_verification_meta_box'));
        add_action('edit_user_profile', array($this, 'civi_identity_verification_meta_box'));
        add_action('personal_options_update', array($this, 'civi_save_identity_verification_meta_box'));
        add_action('edit_user_profile_update', array($this, 'civi_save_identity_verification_meta_box'));
        }
        /**
         * Hiển thị meta box xác thực danh tính trên trang user profile
         */
        public function civi_identity_verification_meta_box($user)
        {
            if (!current_user_can('edit_users')) return;
            $verify_id_before_id = get_user_meta($user->ID, 'verify_id_before_id', true);
            $verify_id_after_id = get_user_meta($user->ID, 'verify_id_after_id', true);
            $identity_document = get_user_meta($user->ID, 'identity_document', true);
            $identity_verification = get_user_meta($user->ID, 'identity_verification', true);
            $before_url = $verify_id_before_id ? wp_get_attachment_url($verify_id_before_id) : '';
            $after_url = $verify_id_after_id ? wp_get_attachment_url($verify_id_after_id) : '';
            ?>
            <h2><?php esc_html_e('Identity Verification', 'civi-framework'); ?></h2>
            <table class="form-table">
                <tr>
                    <th><label><?php esc_html_e('Document Type', 'civi-framework'); ?></label></th>
                    <td>
                        <input type="text" name="identity_document" value="<?php echo esc_attr($identity_document); ?>" />
                    </td>
                </tr>
                <tr>
                    <th><label><?php esc_html_e('Front Side Image', 'civi-framework'); ?></label></th>
                    <td>
                        <?php if ($before_url): ?>
                            <a href="<?php echo esc_url($before_url); ?>" target="_blank"><img src="<?php echo esc_url($before_url); ?>" style="max-width:120px;" /></a><br>
                        <?php endif; ?>
                        <input type="number" name="verify_id_before_id" value="<?php echo esc_attr($verify_id_before_id); ?>" />
                    </td>
                </tr>
                <tr>
                    <th><label><?php esc_html_e('Back Side Image', 'civi-framework'); ?></label></th>
                    <td>
                        <?php if ($after_url): ?>
                            <a href="<?php echo esc_url($after_url); ?>" target="_blank"><img src="<?php echo esc_url($after_url); ?>" style="max-width:120px;" /></a><br>
                        <?php endif; ?>
                        <input type="number" name="verify_id_after_id" value="<?php echo esc_attr($verify_id_after_id); ?>" />
                    </td>
                </tr>
                <tr>
                    <th><label><?php esc_html_e('Verification Status', 'civi-framework'); ?></label></th>
                    <td>
                        <select name="identity_verification">
                            <option value="pending" <?php selected($identity_verification, 'pending'); ?>>Pending</option>
                            <option value="verified" <?php selected($identity_verification, 'verified'); ?>>Verified</option>
                            <option value="" <?php selected($identity_verification, ''); ?>>Not Verified</option>
                        </select>
                    </td>
                </tr>
            </table>
            <?php
        }

        /**
         * Lưu meta box xác thực danh tính khi cập nhật user profile
         */
        public function civi_save_identity_verification_meta_box($user_id)
        {
            if (!current_user_can('edit_users')) return;
            if (isset($_POST['verify_id_before_id'])) {
                update_user_meta($user_id, 'verify_id_before_id', intval($_POST['verify_id_before_id']));
            }
            if (isset($_POST['verify_id_after_id'])) {
                update_user_meta($user_id, 'verify_id_after_id', intval($_POST['verify_id_after_id']));
            }
            if (isset($_POST['identity_document'])) {
                update_user_meta($user_id, 'identity_document', sanitize_text_field($_POST['identity_document']));
            }
            if (isset($_POST['identity_verification'])) {
                update_user_meta($user_id, 'identity_verification', sanitize_text_field($_POST['identity_verification']));
            }
        }

        public static function add_user_roles()
        {
            add_role(
                'civi_user_candidate',
                esc_html__('Candidate', 'civi-framework'),
                array(
                    'read' => true,
                    'edit_posts' => false,
                    'delete_posts' => false,
                    'upload_files' => true,
                    'preview_posts' => true,
                )
            );
            add_role(
                'civi_user_employer',
                esc_html__('Employer', 'civi-framework'),
                array(
                    'read' => true,
                    'edit_posts' => false,
                    'delete_posts' => false,
                    'upload_files' => true,
                    'preview_posts' => true,
                    'edit_jobs'          => true,
                    'publish_jobs'       => true,
                    'delete_jobs'        => true,
                    'edit_published_jobs' => true,
                    'delete_published_jobs' => true,
                    'edit_others_jobs'   => false,
                    'delete_others_jobs' => false,
                )
            );
        }

        public function handle_identity_verification()
        {
            if (!is_admin()) {
                return;
            }
            if (!isset($_REQUEST['verify']) || !isset($_REQUEST['id'])) {
                return;
            }
            $user_id = $_REQUEST['id'];
            $user = get_userdata($user_id);
            $admin_email = get_option('admin_email');
            $args = array(
                'user_login' => $user->user_login,
                'user_email' => $user->user_email,
            );
            if (isset($_REQUEST['verify']) && $_REQUEST['verify'] == 'true') {
                update_user_meta($user_id, 'identity_verification', 'verified');
                civi_send_email($user->user_email, 'mail_approve_identify_for_user', $args);
                civi_send_email($admin_email, 'mail_approve_identify_for_admin', $args);
                $data = array(
                    'post_type' => 'candidate',
                    'author' => $user_id,
                    'post_status' => 'publish',
                );
                $candidate = get_posts($data);
                if (!empty($candidate)) {
                    $candidate_id = $candidate[0]->ID;
                } else {
                    $candidate_id = 0;
                }
                update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_featured', 1);
            }

            if (isset($_REQUEST['verify']) && $_REQUEST['verify'] == 'false') {
                update_user_meta($user_id, 'identity_verification', '');
                civi_send_email($user->user_email, 'mail_unverify_identify_for_user', $args);
                civi_send_email($admin_email, 'mail_unverify_identify_for_admin', $args);
            }
        }

        public function civi_deactive_user()
        {
            if (
                isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' &&
                isset($_POST['action']) && $_POST['action'] === 'civi_deactive_user'
            ) {
                if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'civi_deactive_user_action')) {
                    wp_die(__('Security check failed.', 'civi-framework'));
                }
                if (!is_user_logged_in()) {
                    wp_die(__('You must be logged in to perform this action.', 'civi-framework'));
                }

                global $wpdb;
                $user_id = get_current_user_id();
                $current_user = wp_get_current_user();

                if (in_array('administrator', $current_user->roles)) {
                    wp_die(__('Administrators cannot be deactivated this way.', 'civi-framework'));
                }

                $user_id_post = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

                if ($user_id != $user_id_post) {
                    wp_die(__('Security check failed.', 'civi-framework'));
                }

                if (!function_exists('wp_delete_user')) {
                    require_once ABSPATH . 'wp-admin/includes/user.php';
                }

                $user = get_userdata($user_id);
                if ($user) {

                    $args = array(
                        'user_login_deactive' => $user->user_login,
                        'user_email_deactive' => $user->user_email,
                    );

                    $admin_email = get_option('admin_email');

                    civi_send_email($user->user_email, 'mail_deactive_user', $args);
                    civi_send_email($admin_email, 'admin_mail_deactive_user', $args);

                    $posts = get_posts([
                        'author' => $user_id,
                        'numberposts' => -1,
                        'post_type' => 'any',
                        'post_status' => 'any',
                    ]);

                    foreach ($posts as $post) {
                        wp_delete_post($post->ID, true);
                    }

                    $wpdb->delete($wpdb->usermeta, ['user_id' => $user_id]);

                    wp_delete_user($user_id);
                }

                wp_logout();

                wp_redirect(home_url());
                exit;
            }
        }

        public static function add_user_roles_default()
        {
            return 'civi_user_candidate';
        }

        public function handle_role_change($user_id, $role, $old_roles)
        {
            $user = get_userdata($user_id);
            if ($user && in_array('administrator', (array) $user->roles, true)) {
                return;
            }

            if ($role === 'civi_user_candidate') {
                $existing_candidate_id = get_user_meta($user_id, 'civi-cpt_id', true);
                $existing_post = !empty($existing_candidate_id) ? get_post($existing_candidate_id) : null;

                if ($existing_post && $existing_post->post_type === 'candidate' && $existing_post->post_status !== 'trash') {
                    // Re-activate existing profile
                    delete_post_meta($existing_candidate_id, CIVI_METABOX_PREFIX . 'candidate_approval_status');
                    $this->sync_candidate_status_by_user_status($existing_candidate_id, $user_id);
                } else {
                    // Create new profile or find orphaned one
                    $new_profile_id = $this->create_profile_for_new_candidate($user_id);
                    if ($new_profile_id > 0) {
                        update_user_meta($user_id, 'civi-cpt_id', $new_profile_id);
                        $this->sync_candidate_status_by_user_status($new_profile_id, $user_id);
                    }
                }
            }

            if ($role === 'civi_user_employer') {
                $existing_candidate_id = get_user_meta($user_id, 'civi-cpt_id', true);
                $existing_post = !empty($existing_candidate_id) ? get_post($existing_candidate_id) : null;

                if ($existing_post && $existing_post->post_type === 'candidate' && $existing_post->post_status !== 'trash') {
                    // Lock candidate profile when switching to employer
                    wp_update_post(array('ID' => $existing_candidate_id, 'post_status' => 'draft'));
                    update_post_meta($existing_candidate_id, CIVI_METABOX_PREFIX . 'candidate_approval_status', 'locked');
                }

                // Ensure employer has company profile
                $existing_company_id = get_user_meta($user_id, 'civi-company_id', true);
                if (empty($existing_company_id) || !get_post($existing_company_id)) {
                    $new_company_id = $this->create_profile_for_new_employer($user_id);
                    if ($new_company_id > 0) {
                        update_user_meta($user_id, 'civi-company_id', $new_company_id);
                    }
                }
            }
        }

        /**
         * Sync candidate status by user status
         * @param $candidate_id
         * @param $user_id
         */
        private function sync_candidate_status_by_user_status($candidate_id, $user_id)
        {
            $enable_status_user = civi_get_option('enable_status_user');

            if ($enable_status_user === '1') {
                $user_status = get_user_meta($user_id, CIVI_METABOX_PREFIX . 'user_status', true);
                $new_status = ($user_status === 'approve') ? 'publish' : 'pending';
                $approval_status = ($user_status === 'approve') ? 'approved' : 'pending';
            } else {
                $new_status = 'publish';
                $approval_status = 'approved';
            }

            wp_update_post(array('ID' => $candidate_id, 'post_status' => $new_status));
            update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_approval_status', $approval_status);
        }

        public function register_candidate_archive_cpt()
        {
            register_post_type('candidate_archive', array(
                'labels' => array(
                    'name' => __('Candidate Archives', 'civi-framework'),
                ),
                'public' => false,
                'show_ui' => false,
                'show_in_menu' => false,
                'supports' => array('title'),
            ));
        }

        /**
         * Prevent locked candidate publish
         */
        public function prevent_locked_candidate_publish($data, $postarr)
        {
            if ($data['post_type'] !== 'candidate') {
                return $data;
            }

            if ($data['post_status'] === 'publish') {
                $post_id = isset($postarr['ID']) ? $postarr['ID'] : 0;

                if ($post_id > 0) {
                    $approval_status = get_post_meta($post_id, CIVI_METABOX_PREFIX . 'candidate_approval_status', true);

                    if ($approval_status === 'locked') {
                        $data['post_status'] = 'draft';

                        add_action('admin_notices', function () {
                            echo '<div class="notice notice-error is-dismissible">';
                            echo '<p><strong>' . esc_html__('Cannot Publish', 'civi-framework') . ':</strong> ';
                            echo esc_html__('This candidate profile is locked because the user role has been changed to Employer. Please change the user role back to Candidate to enable publishing.', 'civi-framework');
                            echo '</p>';
                            echo '</div>';
                        });
                    }
                }
            }

            return $data;
        }

        public function enqueue_users_scripts($hook)
        {
            if ($hook !== 'users.php') {
                return;
            }
            wp_enqueue_script('civi-approve-user', CIVI_PLUGIN_URL . 'assets/js/admin/approve-user.js', array('jquery'), CIVI_THEME_VERSION, true);
            wp_localize_script('civi-approve-user', 'civiApproveUser', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'i18n' => array(
                    'approved' => __('Approved', 'civi-framework'),
                    'processing' => __('Processing...', 'civi-framework'),
                )
            ));
        }

        public function enforce_candidate_status_by_author_role($data, $postarr)
        {
            if (isset($data['post_type']) && $data['post_type'] === 'candidate') {
                $author_id = isset($postarr['post_author']) ? (int) $postarr['post_author'] : 0;
                if ($author_id > 0) {
                    $user = get_userdata($author_id);
                    if ($user && !in_array('civi_user_candidate', (array) $user->roles, true)) {
                        $data['post_status'] = 'draft';
                    }
                }
            }
            return $data;
        }

        public function add_identity_verification_column($columns)
        {
            $columns['identity_verification'] = esc_html__('Identity Verification', 'civi-framework');
            return $columns;
        }

        public function display_identity_verification_column_data($value, $column_name, $user_id)
        {
            if ('identity_verification' !== $column_name) {
                return $value;
            }

            $user = get_userdata($user_id);
            if (!$user) {
                return $value;
            }

            $user_roles = $user->roles;
            if (!in_array('civi_user_candidate', $user_roles) && !in_array('civi_user_employer', $user_roles)) {
                return $value;
            }

            // Get all meta in one call to reduce queries
            $identity_verification = get_user_meta($user_id, 'identity_verification', true);
            $verify_id_before_id = get_user_meta($user_id, 'verify_id_before_id', true);
            $verify_id_after_id = get_user_meta($user_id, 'verify_id_after_id', true);

            if ($identity_verification == 'verified') {
                return '<span class="label civi-label-green">' . esc_html__('Verified', 'civi-framework') . '</span><br>';
            }

            if ($identity_verification == 'pending' && !empty($verify_id_before_id) && !empty($verify_id_after_id)) {
                // Cache attachment URLs to avoid repeated calls
                $before_url = wp_get_attachment_url($verify_id_before_id);
                $after_url = wp_get_attachment_url($verify_id_after_id);

                if ($before_url && $after_url) {
                    $html = '<span class="label civi-label-yellow">' . esc_html__('Pending', 'civi-framework') . '</span><br>';
                    $html .= '<div class="civi-identity-verification-images">';
                    $html .= '<a href="' . esc_url($before_url) . '" target="_blank"><img src="' . esc_url($before_url) . '" alt="ID Before" /></a>';
                    $html .= '<a href="' . esc_url($after_url) . '" target="_blank"><img src="' . esc_url($after_url) . '" alt="ID After" /></a>';
                    $html .= '</div>';
                    return $html;
                }
            }

            return '<span class="label civi-label-red">' . esc_html__('Not Verified', 'civi-framework') . '</span>';
        }

        /**
         * Add custom columns to users list
         *
         * @param array $columns Existing columns
         * @return array Modified columns
         */
        public function add_user_custom_columns($columns)
        {
            $columns['user_created_at'] = esc_html__('Created At', 'civi-framework');
            return $columns;
        }

        /**
         * Display custom column data
         *
         * @param string $value Current column value
         * @param string $column_name Column name
         * @param int $user_id User ID
         * @return string Column value
         */
        public function display_user_custom_column_data($value, $column_name, $user_id)
        {
            if ('user_created_at' !== $column_name) {
                return $value;
            }

            // Cache date format options to avoid repeated get_option() calls
            static $date_format = null;
            static $time_format = null;

            if (null === $date_format) {
                $date_format = get_option('date_format');
                $time_format = get_option('time_format');
            }

            $user = get_userdata($user_id);
            if (!$user || empty($user->user_registered)) {
                return esc_html__('N/A', 'civi-framework');
            }

            $registered_date = strtotime($user->user_registered);
            return date_i18n($date_format . ' ' . $time_format, $registered_date);
        }

        /**
         * Make columns sortable (Created At, Role, Name, Posts)
         *
         * @param array $columns Existing sortable columns
         * @return array Modified sortable columns
         */
        public function add_user_sortable_columns($columns)
        {
            $columns['user_created_at'] = 'registered';
            $columns['role'] = 'role';
            $columns['name'] = 'name';
            $columns['posts'] = 'post_count';
            return $columns;
        }

        /**
         * Handle sorting for standard columns (registered, role, display_name)
         *
         * @param WP_User_Query $query User query object
         */
        public function handle_user_sorting($query)
        {
            if (!is_admin() || !$query->is_main_query()) {
                return;
            }

            $orderby = $query->get('orderby');
            $sortable_fields = array(
                'registered' => 'registered',
                'role' => 'role',
                'name' => 'display_name',
            );

            if (isset($sortable_fields[$orderby])) {
                $query->set('orderby', $sortable_fields[$orderby]);
            }
        }

        /**
         * Handle sorting by post count using users_clauses filter
         *
         * Note: This uses a subquery which may be slower on large sites.
         * Consider adding indexes on wp_posts.post_author and wp_posts.post_status for better performance.
         *
         * @param array $clauses Query clauses
         * @param WP_User_Query $query User query object
         * @return array Modified clauses
         */
        public function handle_user_post_count_sorting($clauses, $query)
        {
            if (!is_admin()) {
                return $clauses;
            }

            global $pagenow;
            if ('users.php' !== $pagenow) {
                return $clauses;
            }

            $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : '';
            if ('post_count' !== $orderby) {
                return $clauses;
            }

            global $wpdb;
            $order = isset($_GET['order']) && strtoupper($_GET['order']) === 'ASC' ? 'ASC' : 'DESC';

            // Add JOIN to count published posts
            // Using subquery with proper indexes (post_author, post_status) for optimal performance
            $clauses['join'] .= " LEFT JOIN (
                SELECT post_author, COUNT(*) as post_count
                FROM {$wpdb->posts}
                WHERE post_status = 'publish'
                GROUP BY post_author
            ) as post_counts ON {$wpdb->users}.ID = post_counts.post_author";

            // Modify ORDER BY clause
            $clauses['orderby'] = sprintf(
                "post_counts.post_count %s, {$wpdb->users}.display_name ASC",
                esc_sql($order)
            );

            return $clauses;
        }

        public function add_user_status_action_link($actions, $user_object)
        {
            // Cache option check to avoid repeated calls for each user row
            static $enable_status = null;
            if (null === $enable_status) {
                $enable_status = civi_get_option('enable_status_user');
            }

            if ($enable_status !== '1') {
                return $actions;
            }

            $user_status = get_user_meta($user_object->ID, CIVI_METABOX_PREFIX . 'user_status', true);
            if ($user_status !== 'approve' && current_user_can('promote_user', $user_object->ID)) {
                $nonce = wp_create_nonce('civi_approve_user_status');
                $actions['civi-approve-user-status'] = '<a href="#" class="civi-approve-user-status" data-user-id="' . esc_attr($user_object->ID) . '" data-nonce="' . esc_attr($nonce) . '">' . esc_html__('Approve User Status', 'civi-framework') . '</a>';
            }
            return $actions;
        }

        public function handle_user_status_approval()
        {
            // switch to AJAX handler
        }

        public function ajax_approve_user_status()
        {
            check_ajax_referer('civi_approve_user_status');
            if (!current_user_can('promote_users')) {
                wp_send_json_error(array('message' => __('Permission denied', 'civi-framework')));
            }
            $user_id = isset($_POST['user_id']) ? absint($_POST['user_id']) : 0;
            if (!$user_id) {
                wp_send_json_error(array('message' => __('Invalid user', 'civi-framework')));
            }

            update_user_meta($user_id, CIVI_METABOX_PREFIX . 'user_status', 'approve');

            $candidate_post_id = get_user_meta($user_id, 'civi-cpt_id', true);
            if (!empty($candidate_post_id)) {
                wp_update_post(array('ID' => $candidate_post_id, 'post_status' => 'publish'));
                update_post_meta($candidate_post_id, CIVI_METABOX_PREFIX . 'candidate_approval_status', 'approved');
            }

            $user = get_userdata($user_id);
            if ($user) {
                $user_email = $user->user_email;
                $user_roles = (array) $user->roles;
                $is_employer = in_array('civi_user_employer', $user_roles, true);
                $company_post_id = get_user_meta($user_id, 'civi-company_id', true);

                if ($is_employer) {
                    $args = array(
                        'user_login' => $user->user_login,
                        'company_title' => !empty($company_post_id) ? get_the_title($company_post_id) : '',
                        'company_url' => !empty($company_post_id) ? get_permalink($company_post_id) : ''
                    );
                    civi_send_email($user_email, 'mail_approved_user_status_company', $args);
                } else {
                    $args = array(
                        'user_login' => $user->user_login,
                        'candidate_title' => !empty($candidate_post_id) ? get_the_title($candidate_post_id) : '',
                        'candidate_url' => !empty($candidate_post_id) ? get_permalink($candidate_post_id) : ''
                    );
                    civi_send_email($user_email, 'mail_approved_user_status', $args);
                }
            }

            wp_send_json_success(array('message' => __('Approved', 'civi-framework')));
        }

        public function add_custom_user_action_link($actions, $user_object)
        {
            $user_id = $user_object->ID;
            $user_email = $user_object->user_email;
            $user_roles = $user_object->roles;
            $identity_verification = get_user_meta($user_id, 'identity_verification', true);

            if ($identity_verification == 'pending' && (in_array('civi_user_candidate', $user_roles) || in_array('civi_user_employer', $user_roles))) {
                $url = admin_url('users.php?s=' . $user_email . '&verify=true&id=' . $user_id . '&new_role&paged=1&action2=-1&new_role2');
                $actions['custom_action'] = '<a href="' . esc_url($url) . '">Verify</a>';
                $url = admin_url('users.php?s=' . $user_email . '&verify=false&id=' . $user_id . '&new_role&paged=1&action2=-1&new_role2');
                $actions['custom_action_reject'] = '<a href="' . esc_url($url) . '">Reject</a>';
            }
            if ($identity_verification == 'verified' && (in_array('civi_user_candidate', $user_roles) || in_array('civi_user_employer', $user_roles))) {
                $url = admin_url('users.php?s=' . $user_email . '&verify=false&id=' . $user_id . '&new_role&paged=1&action2=-1&new_role2');
                $actions['custom_action'] = '<a href="' . esc_url($url) . '">Unverify</a>';
            }
            return $actions;
        }

        public static function custom_add_user_column($columns)
        {
            $columns['custom_column'] = esc_html__('Status', 'civi-framework');
            return $columns;
        }

        public static function custom_display_user_column_data($value, $column_name, $user_id)
        {
            if ('custom_column' === $column_name) {
                $status = '';
                $user_status = get_the_author_meta(CIVI_METABOX_PREFIX . 'user_status', $user_id);
                if (empty($user_status) || $user_status == 'pending') {
                    $user_status = '<span class="label civi-label-yellow">' . esc_html__('Pending', 'civi-framework') . '</span>';
                } else {
                    $user_status = '<span class="label civi-label-blue">' . esc_html__('Approved', 'civi-framework') . '</span>';
                }
                return $user_status;
            }
            return $value;
        }

        public static function jobs_single_bottombar()
        {
            if (is_singular('jobs')) {
                $social_sharing = civi_get_option('social_sharing');
                $jobs_id = get_the_ID(); ?>
                <div class="civi-apply-bottombar">
                    <?php civi_get_status_apply($jobs_id); ?>
                    <?php if (!empty($social_sharing)) : ?>
                        <div class="toggle-social">
                            <a href="#" class="jobs-share btn-share">
                                <i class="fas fa-share-alt"></i>
                            </a>
                            <?php civi_get_template('global/social-share.php', array(
                                'post_id' => $jobs_id,
                            )); ?>
                        </div>
                    <?php endif; ?>
                    <?php civi_get_template('jobs/wishlist.php', array(
                        'jobs_id' => $jobs_id,
                    )); ?>
                </div>
            <?php
            }
        }

        public function create_a_profile_post_for_new_candidate($user_id)
        {
            $user_object = get_userdata($user_id);
            if (!$user_object) {
                return;
            }

            // Check if user already has a candidate profile to prevent duplicates
            $existing_profile_id = get_user_meta($user_id, 'civi-cpt_id', true);
            $existing_post = !empty($existing_profile_id) ? get_post($existing_profile_id) : null;

            // Check if this is a new registration with account_type set
            global $civi_registering_account_type;
            $is_new_registration = isset($civi_registering_account_type);

            // If registering as employer but has candidate profile, remove it
            if ($is_new_registration && $civi_registering_account_type === 'civi_user_employer' && $existing_post) {
                wp_delete_post($existing_profile_id, true); // Force delete
                delete_user_meta($user_id, 'civi-cpt_id');
                // Continue to skip creating new profile for employer
                return;
            }

            // Verify the post exists and is not trashed (for non-registration updates)
            if ($existing_post && $existing_post->post_type === 'candidate' && $existing_post->post_status !== 'trash') {
                return $existing_profile_id;
            }

            // Fallback: Search for existing candidate profile by author to prevent race conditions
            $existing_profiles = get_posts(array(
                'post_type' => 'candidate',
                'post_status' => array('publish', 'pending', 'draft'),
                'author' => $user_id,
                'posts_per_page' => 1,
                'orderby' => 'ID',
                'order' => 'ASC',
                'fields' => 'ids'
            ));

            if (!empty($existing_profiles)) {
                $found_profile_id = $existing_profiles[0];
                // Update user meta to sync
                update_user_meta($user_id, 'civi-cpt_id', $found_profile_id);
                return $found_profile_id;
            }

            // Clean up orphaned meta if post was deleted
            if (!empty($existing_profile_id) && !$existing_post) {
                delete_user_meta($user_id, 'civi-cpt_id');
            }

            $user_roles = (array) $user_object->roles;

            $is_candidate = false;

            if (is_admin() && !wp_doing_ajax()) {
                $is_candidate = in_array('civi_user_candidate', $user_roles, true);
            } else {
                // Check global variable first (set during registration)
                global $civi_registering_account_type;
                if (isset($civi_registering_account_type)) {
                    $is_candidate = ($civi_registering_account_type === 'civi_user_candidate');
                } elseif (isset($_POST['account_type'])) {
                    $is_candidate = (sanitize_text_field(wp_unslash($_POST['account_type'])) === 'civi_user_candidate');
                } else {
                    $is_candidate = in_array('civi_user_candidate', $user_roles, true);
                }
            }

            if (!$is_candidate) {
                return;
            }

            $new_profile_id = $this->create_profile_for_new_candidate($user_id);
            if ($new_profile_id > 0) {
                update_user_meta($user_id, 'civi-cpt_id', $new_profile_id);
            }

            return $new_profile_id;
        }

        private function create_profile_for_new_candidate($user_id)
        {
            $user = get_userdata($user_id);
            if (!$user) {
                return 0;
            }

            // Double-check to prevent duplicate profile creation
            $existing_profile_id = get_user_meta($user_id, 'civi-cpt_id', true);
            $existing_post = !empty($existing_profile_id) ? get_post($existing_profile_id) : null;

            if ($existing_post && $existing_post->post_type === 'candidate' && $existing_post->post_status !== 'trash') {
                return $existing_profile_id;
            }

            // Final check: Query for any existing candidate profile
            $existing_profiles = get_posts(array(
                'post_type' => 'candidate',
                'post_status' => array('publish', 'pending', 'draft'),
                'author' => $user_id,
                'posts_per_page' => 1,
                'fields' => 'ids'
            ));

            if (!empty($existing_profiles)) {
                $profile_id = $existing_profiles[0];
                update_user_meta($user_id, 'civi-cpt_id', $profile_id);
                return $profile_id;
            }

            $type_name_candidate = civi_get_option('type_name_candidate');
            $enable_status_user = civi_get_option('enable_status_user');

            $user_status = get_user_meta($user_id, CIVI_METABOX_PREFIX . 'user_status', true);
            if ($enable_status_user === '1') {
                $post_status = ($user_status === 'approve') ? 'publish' : 'pending';
            } else {
                $post_status = 'publish';
            }

            $new_profile = array(
                'post_author' => $user_id,
                'post_type'   => 'candidate',
                'post_status' => $post_status
            );

            if ($type_name_candidate === 'fl-name') {
                $first_name = $user->first_name;
                $last_name = $user->last_name;
                $new_profile['post_title'] = trim($first_name . ' ' . $last_name);

                if (empty($new_profile['post_title'])) {
                    $new_profile['post_title'] = sanitize_user($user->user_login, true);
                }
            } else {
                $new_profile['post_title'] = sanitize_user($user->user_login, true);
            }

            if (empty($new_profile['post_title'])) {
                return 0;
            }

            $new_profile_id = wp_insert_post($new_profile, true);

            if ($new_profile_id && !is_wp_error($new_profile_id)) {
                $approval_status = ($post_status === 'publish') ? 'approved' : 'pending';
                update_post_meta($new_profile_id, CIVI_METABOX_PREFIX . 'candidate_approval_status', $approval_status);

                $this->sync_user_to_candidate_data($user_id, $new_profile_id);
                $this->sync_complete_avatar_data($user_id, $new_profile_id);
                $this->update_user_candidate_avatar($user_id);
                wp_schedule_single_event(time() + 3, 'civi_delayed_avatar_sync_after_register', array($user_id));
            }
            return $new_profile_id;
        }

        private function sync_user_to_candidate_data($user_id, $candidate_id)
        {
            if (empty($user_id) || empty($candidate_id)) {
                return;
            }

            $user_data = get_userdata($user_id);
            if (!$user_data) {
                return;
            }
            $enable_status_user = civi_get_option('enable_status_user');
            update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_user_id', $user_id);
            update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_first_name', $user_data->first_name);
            update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_last_name', $user_data->last_name);
            update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_email', $user_data->user_email);

            // Check for phone in global variable first (set during registration before user meta)
            global $civi_registering_phone;
            $phone = isset($civi_registering_phone) ? $civi_registering_phone : get_user_meta($user_id, CIVI_METABOX_PREFIX . 'author_mobile_number', true);

            if (!empty($phone)) {
                update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_phone', $phone);
            }
            update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_profile_strength', 10);
            update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_featured', 0);
            if ($enable_status_user === '1') {
                update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_approval_status', 'pending');
            } else {
                update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_approval_status', 'approved');
            }
        }

        private function sync_complete_avatar_data($user_id, $candidate_id)
        {
            if (empty($user_id) || empty($candidate_id)) {
                return;
            }

            $existing_avatar_url = get_user_meta($user_id, 'author_avatar_image_url', true);
            $existing_avatar_id = get_user_meta($user_id, 'author_avatar_image_id', true);

            if (empty($existing_avatar_url)) {
                $wp_avatar_url = $this->get_actual_wordpress_avatar_url($user_id);
                if (!empty($wp_avatar_url) && !$this->is_default_avatar($wp_avatar_url)) {
                    update_user_meta($user_id, 'author_avatar_image_url', $wp_avatar_url);
                    if (strpos($wp_avatar_url, home_url()) !== false) {
                        $avatar_id = attachment_url_to_postid($wp_avatar_url);
                        if ($avatar_id > 0) {
                            update_user_meta($user_id, 'author_avatar_image_id', $avatar_id);
                            $existing_avatar_id = $avatar_id;
                        }
                    }

                    $existing_avatar_url = $wp_avatar_url;
                }
            }

            if (!empty($existing_avatar_url)) {
                update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_avatar_image_url', $existing_avatar_url);
                if (!empty($existing_avatar_id)) {
                    update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_avatar_image_id', $existing_avatar_id);
                }
            }
        }
        private function get_actual_wordpress_avatar_url($user_id)
        {
            if (empty($user_id)) {
                return '';
            }
            $avatar_html = get_avatar($user_id, 96);
            if (preg_match('/src=["\']([^"\']+)["\']/', $avatar_html, $matches)) {
                return $matches[1];
            }
            return get_avatar_url($user_id, array('size' => 96));
        }

        private function is_default_avatar($avatar_url)
        {
            if (empty($avatar_url)) {
                return true;
            }

            $default_patterns = array(
                'default-user-image.png',
                'gravatar.com/avatar',
                'secure.gravatar.com/avatar',
                'd=mm',
                'd=identicon',
                'd=wavatar',
                'd=retro',
                'd=robohash',
                'd=blank'
            );

            foreach ($default_patterns as $pattern) {
                if (strpos($avatar_url, $pattern) !== false) {
                    return true;
                }
            }

            return false;
        }

        public function update_user_candidate_avatar($user_id)
        {
            if (empty($user_id)) {
                return false;
            }

            $candidate_posts = get_posts(array(
                'post_type' => 'candidate',
                'author' => $user_id,
                'posts_per_page' => 1,
                'post_status' => 'any'
            ));

            if (empty($candidate_posts)) {
                return false;
            }

            $candidate_id = $candidate_posts[0]->ID;

            $this->sync_complete_avatar_data($user_id, $candidate_id);

            return true;
        }

        public function batch_update_user_candidate_avatars($user_ids)
        {
            $results = array();

            if (empty($user_ids) || !is_array($user_ids)) {
                return $results;
            }

            foreach ($user_ids as $user_id) {
                $results[$user_id] = $this->update_user_candidate_avatar($user_id);
            }

            return $results;
        }

        public function delayed_avatar_sync_after_register($user_id)
        {
            $this->update_user_candidate_avatar($user_id);
        }

        public function job_alert_form()
        {
            global $current_user;
            if (in_array("civi_user_employer", (array) $current_user->roles)) {
                return;
            }
            $current_page_id = get_the_ID();
            $alerts_title = esc_html__('Job Alert', 'civi-framework');
            $alerts_desc = esc_html__('Subscribe to receive instant alerts of new relevant jobs directly to your email inbox.', 'civi-framework');
            $alerts_button_title = esc_html__('Subscribe', 'civi-framework');
            $civi_job_alerts_page_id  = civi_get_option('civi_job_alerts_page_id');
            if (($current_page_id == $civi_job_alerts_page_id) || isset($_COOKIE["cookie_job_alerts"]) || (get_post_type() != 'jobs')) {
                return;
            }
            ?>
            <div class="alert-form">
                <a href="#" class="close"><i class="far fa-times"></i></a>
                <div class="inner">
                    <div class="head">
                        <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M21.7042 19.316C21.5254 17.691 24.5704 12.5448 21.9617 9.08601C21.6917 8.71101 22.0667 7.92351 22.3179 7.39226C22.9279 6.10726 22.7979 5.05726 21.5792 4.42976C20.3604 3.80226 19.4192 4.37976 18.7042 5.42976C18.3667 5.92976 17.8192 6.67976 17.4317 6.64101C13.1442 6.36726 10.5267 11.716 9.08041 12.4498C7.37041 13.3173 3.45541 13.896 2.79416 16.2735C1.93291 19.346 4.65666 22.4085 10.2367 25.446C15.8167 28.4835 19.8617 29.1035 21.9492 26.696C23.5679 24.8373 21.9154 21.2298 21.7042 19.316Z" fill="#191919" />
                            <path d="M18.3748 29.1702C18.01 29.1692 17.6456 29.1467 17.2835 29.1027C15.2048 28.8527 12.7435 27.9452 9.7585 26.3202C6.7735 24.6952 4.67225 23.1252 3.331 21.5089C1.821 19.6914 1.316 17.8377 1.831 16.0002C2.46225 13.7502 5.10725 12.8327 7.03975 12.1664C7.57981 11.994 8.10909 11.7895 8.62475 11.5539C8.87475 11.4289 9.466 10.7327 9.93975 10.1789C11.5223 8.32016 13.886 5.53766 17.2798 5.62516C17.5019 5.39094 17.7011 5.13603 17.8748 4.86391C18.9848 3.23891 20.4998 2.75391 22.0323 3.54141C22.8098 3.94141 24.481 5.15891 23.2173 7.82266C23.0819 8.08965 22.9686 8.36724 22.8785 8.65266C24.8473 11.4552 23.8135 14.9902 23.1285 17.3439C22.9223 18.0477 22.666 18.9227 22.6985 19.2064C22.7847 19.7692 22.9041 20.3264 23.056 20.8752C23.5823 22.9739 24.2373 25.5864 22.7048 27.3477C21.6535 28.5614 20.1998 29.1702 18.3748 29.1702ZM17.1248 7.62516C14.7385 7.62516 12.8573 9.83766 11.471 11.4689C10.7323 12.3439 10.1485 13.0252 9.536 13.3352C8.93867 13.6139 8.32408 13.8539 7.696 14.0539C6.0985 14.6052 4.10975 15.2927 3.761 16.5377C3.07725 18.9764 5.4185 21.6764 10.7198 24.5627C16.021 27.4489 19.5423 27.9377 21.1985 26.0389C22.046 25.0639 21.5323 23.0139 21.1185 21.3652C20.942 20.7278 20.8063 20.0798 20.7123 19.4252C20.6373 18.7427 20.8898 17.8764 21.2123 16.7814C21.8373 14.6427 22.6948 11.7139 21.1673 9.68766L21.156 9.67266C20.5385 8.81891 21.0685 7.70266 21.4185 6.96391C21.9885 5.76266 21.4898 5.50641 21.126 5.31891C20.8123 5.15766 20.2873 4.88766 19.536 5.98891C18.9673 6.82391 18.286 7.70766 17.3635 7.63391C17.2798 7.62516 17.1998 7.62516 17.1248 7.62516Z" fill="#191919" />
                            <path d="M21.7042 19.316C21.5254 17.691 24.5704 12.5448 21.9617 9.08601C21.6917 8.71101 22.0667 7.92351 22.3179 7.39226C22.9279 6.10726 22.7979 5.05726 21.5792 4.42976C20.3604 3.80226 19.4192 4.37976 18.7042 5.42976C18.3667 5.92976 17.8192 6.67976 17.4317 6.64101C13.1442 6.36726 10.5267 11.716 9.08041 12.4498C7.37041 13.3173 3.45541 13.896 2.79416 16.2735C1.93291 19.346 4.65666 22.4085 10.2367 25.446C15.8167 28.4835 19.8617 29.1035 21.9492 26.696C23.5679 24.8373 21.9154 21.2298 21.7042 19.316Z" fill="#FFD75E" />
                            <path d="M10.3053 18.8889C10.7028 18.9151 15.759 21.6839 16.1065 21.9589C16.454 22.2339 15.559 23.3851 14.019 23.4989C12.479 23.6126 10.2765 22.6939 9.83403 21.9351C9.39153 21.1764 9.90778 18.8626 10.3053 18.8889Z" fill="#ED0006" />
                            <path d="M15.8232 21.8089C15.5807 21.8227 13.9344 23.5302 12.1982 22.6277C9.79315 21.3777 10.7394 19.2852 10.5732 19.0027C10.4069 18.7202 5.79565 17.0727 5.26815 17.9502C4.7844 18.7564 7.4194 21.6139 11.2369 23.5902C15.1232 25.6014 18.6632 26.3127 19.1244 25.4927C19.6707 24.5102 16.0644 21.7952 15.8232 21.8089Z" fill="white" />
                            <path d="M2.48001 9.8367C2.99126 7.8892 5.09001 6.0042 6.98001 5.52795C7.45001 5.41045 7.11126 4.2867 6.58126 4.40295C4.50876 4.86545 2.28251 6.6017 1.26001 9.3567C1.00001 10.0442 2.38376 10.2055 2.48001 9.8367Z" fill="#191919" />
                            <path d="M5.84292 9.59303C6.68542 8.36053 7.80417 8.07803 8.86667 7.96803C9.33667 7.92053 9.33667 6.71803 8.84542 6.75678C7.54792 6.86053 6.02917 7.11428 4.79292 8.82053C4.37542 9.39178 5.60292 9.94553 5.84292 9.59303Z" fill="#191919" />
                            <path d="M27.699 14.6729C29.2715 16.0091 30.074 18.7841 29.679 20.7454C29.579 21.2316 30.7202 21.5954 30.8465 21.0529C31.3465 18.9279 30.7215 15.8654 28.6365 13.6979C28.1152 13.1566 27.4015 14.4204 27.699 14.6729Z" fill="#191919" />
                            <path d="M26.4837 18.0432C27.17 19.4107 26.8675 20.5532 26.4362 21.5607C26.2462 22.0057 27.3337 22.542 27.5425 22.0832C28.135 20.7795 28.5875 19.427 27.6475 17.417C27.3425 16.762 26.2887 17.6532 26.4837 18.0432Z" fill="#191919" />
                        </svg>
                        <span><?php echo $alerts_title; ?></span>
                    </div>
                    <div class="content">
                        <div class="desc"><?php echo $alerts_desc; ?></div>
                        <a href="<?php echo esc_url(get_page_link($civi_job_alerts_page_id)); ?>" class="civi-button"><?php echo $alerts_button_title; ?></a>
                    </div>
                </div>
            </div>
<?php
        }

        public static function quick_update_avatar($user_id)
        {
            $instance = new self();
            return $instance->update_user_candidate_avatar($user_id);
        }

        public function create_a_profile_post_for_new_employer($user_id)
        {
            $user_object = get_userdata($user_id);
            if (!$user_object) {
                return;
            }

            // Check if user already has an employer profile to prevent duplicates
            $existing_profile_id = get_user_meta($user_id, 'civi-company_id', true);
            $existing_post = !empty($existing_profile_id) ? get_post($existing_profile_id) : null;

            // Verify the post exists and is not trashed
            if ($existing_post && $existing_post->post_type === 'company' && $existing_post->post_status !== 'trash') {
                return $existing_profile_id;
            }

            // Fallback: Search for existing company profile by author
            $existing_profiles = get_posts(array(
                'post_type' => 'company',
                'post_status' => array('publish', 'pending', 'draft'),
                'author' => $user_id,
                'posts_per_page' => 1,
                'orderby' => 'ID',
                'order' => 'ASC',
                'fields' => 'ids'
            ));

            if (!empty($existing_profiles)) {
                $found_profile_id = $existing_profiles[0];
                update_user_meta($user_id, 'civi-company_id', $found_profile_id);
                return $found_profile_id;
            }

            // Clean up orphaned meta
            if (!empty($existing_profile_id) && !$existing_post) {
                delete_user_meta($user_id, 'civi-company_id');
            }

            $user_roles = (array) $user_object->roles;

            $is_employer = false;

            if (is_admin() && !wp_doing_ajax()) {
                $is_employer = in_array('civi_user_employer', $user_roles, true);
            } else {
                if (isset($_POST['account_type'])) {
                    $is_employer = (sanitize_text_field(wp_unslash($_POST['account_type'])) === 'civi_user_employer');
                } else {
                    $is_employer = in_array('civi_user_employer', $user_roles, true);
                }
            }

            if (!$is_employer) {
                return;
            }

            $new_profile_id = $this->create_profile_for_new_employer($user_id);
            if ($new_profile_id > 0) {
                update_user_meta($user_id, 'civi-company_id', $new_profile_id);
            }

            return $new_profile_id;
        }

        private function create_profile_for_new_employer($user_id)
        {
            $user = get_userdata($user_id);
            if (!$user) {
                return 0;
            }

            // Double-check to prevent duplicate profile creation
            $existing_profile_id = get_user_meta($user_id, 'civi-company_id', true);
            $existing_post = !empty($existing_profile_id) ? get_post($existing_profile_id) : null;

            if ($existing_post && $existing_post->post_type === 'company' && $existing_post->post_status !== 'trash') {
                return $existing_profile_id;
            }

            // Final check: Query for existing company profile
            $existing_profiles = get_posts(array(
                'post_type' => 'company',
                'post_status' => array('publish', 'pending', 'draft'),
                'author' => $user_id,
                'posts_per_page' => 1,
                'fields' => 'ids'
            ));

            if (!empty($existing_profiles)) {
                $profile_id = $existing_profiles[0];
                update_user_meta($user_id, 'civi-company_id', $profile_id);
                return $profile_id;
            }

            $enable_status_user = civi_get_option('enable_status_user');

            $new_profile = array(
                'post_author' => $user_id,
                'post_type'   => 'company',
                'post_status' => ($enable_status_user === '1') ? 'pending' : 'publish',
                'post_title'  => sanitize_user($user->user_login, true) . ' Company'
            );

            if (empty($new_profile['post_title'])) {
                return 0;
            }

            $new_profile_id = wp_insert_post($new_profile, true);

            if ($new_profile_id && !is_wp_error($new_profile_id)) {
                $this->sync_user_to_company_data($user_id, $new_profile_id);
            }
            return $new_profile_id;
        }

        private function sync_user_to_company_data($user_id, $company_id)
        {
            if (empty($user_id) || empty($company_id)) {
                return;
            }

            $user_data = get_userdata($user_id);
            if (!$user_data) {
                return;
            }

            $enable_status_user = civi_get_option('enable_status_user');

            update_post_meta($company_id, CIVI_METABOX_PREFIX . 'company_user_id', $user_id);
            update_post_meta($company_id, CIVI_METABOX_PREFIX . 'company_email', $user_data->user_email);
            update_post_meta($company_id, CIVI_METABOX_PREFIX . 'company_featured', 0);

            if ($enable_status_user === '1') {
                update_post_meta($company_id, CIVI_METABOX_PREFIX . 'company_approval_status', 'pending');
            } else {
                update_post_meta($company_id, CIVI_METABOX_PREFIX . 'company_approval_status', 'approved');
            }
        }
    }
    new Civi_User();
}

if (!function_exists('civi_update_user_avatar')) {
    function civi_update_user_avatar($user_id)
    {
        return Civi_User::quick_update_avatar($user_id);
    }
}
