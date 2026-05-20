<?php
if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('JobPortal_Package')) {
    /**
     * Class JobPortal_Package
     */
    class JobPortal_Package
    {
        /**
         * get_time_unit
         * @param $time_unit
         * @return null|string
         */
        public static function get_time_unit($time_unit)
        {
            if ($time_unit == 'Day') {
                return esc_html__('day', 'jobportal-framework');
            } else if ($time_unit == 'Day') {
                return esc_html__('days', 'jobportal-framework');
            } else if ($time_unit == 'Week') {
                return esc_html__('week', 'jobportal-framework');
            } else if ($time_unit == 'Weeks') {
                return esc_html__('weeks', 'jobportal-framework');
            } else if ($time_unit == 'Month') {
                return esc_html__('month', 'jobportal-framework');
            } else if ($time_unit == 'Months') {
                return esc_html__('months', 'jobportal-framework');
            } else if ($time_unit == 'Year') {
                return esc_html__('year', 'jobportal-framework');
            } else if ($time_unit == 'Years') {
                return esc_html__('years', 'jobportal-framework');
            }
            return null;
        }

        /**
         * Insert agent package
         * @param $user_id
         * @param $package_id
         */
        public function insert_user_package($user_id, $package_id, $invoice_pdf_id = '', $skip_cleanup = false)
        {
            $args = array(
                'post_type' => 'user_package',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => JOBPORTAL_METABOX_PREFIX . 'package_user_id',
                        'value' => $user_id,
                        'compare' => '='
                    )
                ),
            );
            $user_package = new WP_Query($args);
            wp_reset_postdata();
            $existed_post = $user_package->found_posts;

            if ($existed_post < 1) {
                $args = array(
                    'post_title' => '#' . $user_id,
                    'post_type' => 'user_package',
                    'post_status' => 'publish'
                );
                $post_id = wp_insert_post($args);
                update_post_meta($post_id, JOBPORTAL_METABOX_PREFIX . 'package_user_id', $user_id);
            }
            $package_number_job = get_post_meta($package_id, JOBPORTAL_METABOX_PREFIX . 'package_number_job', true);
            $package_number_featured = get_post_meta($package_id, JOBPORTAL_METABOX_PREFIX . 'package_number_featured', true);
            $package_unlimited_job = get_post_meta($package_id, JOBPORTAL_METABOX_PREFIX . 'package_unlimited_job', true);
            $package_number_follow = get_post_meta($package_id, JOBPORTAL_METABOX_PREFIX . 'company_package_number_candidate_follow', true);
            $package_follow_unlimited = get_post_meta($package_id, JOBPORTAL_METABOX_PREFIX . 'enable_package_candidate_follow_unlimited', true);
            $package_number_download_cv = get_post_meta($package_id, JOBPORTAL_METABOX_PREFIX . 'company_package_number_download_cv', true);
            $package_download_cv_unlimited = get_post_meta($package_id, JOBPORTAL_METABOX_PREFIX . 'enable_package_download_cv_unlimited', true);

            if ($package_follow_unlimited == 1) {
                $package_number_follow = 999999999999999999;
            }

            if ($package_unlimited_job == 1) {
                $package_number_job = 999999999999999999;
            }

            if ($package_download_cv_unlimited == 1) {
                $package_number_download_cv = 999999999999999999;
            }


            // State-based quotas need current count deducted, action-based just reset
            $state_based_fields = array('package_number_job', 'package_number_featured', 'package_number_candidate_follow');

            // Step 1: Auto cleanup if needed (skip if cleanup already done by activate_package_with_cleanup)
            if (!$skip_cleanup) {
                // Jobs - expire excess jobs if quota exceeded
                if ($package_unlimited_job != 1) {
                    $active_jobs_count = $this->count_active_jobs($user_id);
                    $package_job_quota = max(0, intval($package_number_job)); // Ensure non-negative
                    $excess = $active_jobs_count - $package_job_quota;
                    if ($excess > 0) {
                        $this->auto_expire_jobs($user_id, $excess);
                    }
                }

                // Featured jobs - unfeature excess if quota exceeded
                $active_featured_count = $this->count_active_featured_jobs($user_id);
                $package_featured_quota = max(0, intval($package_number_featured)); // Ensure non-negative
                $excess_featured = $active_featured_count - $package_featured_quota;
                if ($excess_featured > 0) {
                    $this->auto_unfeature_jobs($user_id, $excess_featured);
                }

                // Candidate follow - unfollow excess if quota exceeded
                if ($package_follow_unlimited != 1) {
                    $follows = get_user_meta($user_id, 'follow_candidate', true) ?: [];
                    $current_follow_count = is_array($follows) ? count($follows) : 0;
                    $package_follow_quota = max(0, intval($package_number_follow)); // Ensure non-negative
                    $excess_follow = $current_follow_count - $package_follow_quota;
                    if ($excess_follow > 0) {
                        $this->auto_unfollow_candidates($user_id, $excess_follow);
                    }
                }
            }

            // Step 2: Calculate and set remaining quotas
            // Jobs - deduct current active jobs count
            $active_jobs_count = $this->count_active_jobs($user_id);
            $remaining_jobs = max(0, intval($package_number_job) - $active_jobs_count);
            update_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'package_number_job', $remaining_jobs);

            // Featured jobs - deduct current featured count
            $active_featured_count = $this->count_active_featured_jobs($user_id);
            $remaining_featured = max(0, intval($package_number_featured) - $active_featured_count);
            update_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'package_number_featured', $remaining_featured);

            // Candidate follow - deduct current follow count
            $follows = get_user_meta($user_id, 'follow_candidate', true) ?: [];
            $current_follow_count = is_array($follows) ? count($follows) : 0;
            $remaining_follow = max(0, intval($package_number_follow) - $current_follow_count);
            update_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'package_number_candidate_follow', $remaining_follow);

            // Action-based - just reset
            update_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'package_number_download_cv', $package_number_download_cv);

            do_action('jobportal_ajax_field_package_jobs', $user_id, $package_id);

            $time = time();
            $date = date('Y-m-d H:i:s', $time);
            update_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'package_activate_date', $date);
            update_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'package_id', $package_id);
            $package_key = uniqid();
            update_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'package_key', $package_key);

            $user = get_user_by('id', $user_id);
            $user_email = $user->user_email;
            $package_args = array();
            $package_args['website_url'] = get_option('siteurl');
            $package_args['website_name'] = get_option('blogname');
            $package_args['user_login'] = $user->user_login;

            if ($invoice_pdf_id) {
                $package_args['pdf_file'] = wp_get_attachment_url($invoice_pdf_id);;
            }

            // jobportal_send_email($user_email, 'mail_activated_package', $package_args);
            $admin_email = get_bloginfo('admin_email');
            $package_admin_args = array(
                'user_login' => $user->user_login,
                'user_email' => $user_email,
                'package_id' => $package_id,
            );
            if ($invoice_pdf_id) {
                $package_admin_args['pdf_file'] = wp_get_attachment_url($invoice_pdf_id);;
            }
            global $current_user;
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;
            $expired_date = $this->get_expired_time($package_id, $user_id);
            if (!wp_next_scheduled('auto_check_expired_package', array($user_id))) {
                wp_schedule_single_event($expired_date, 'auto_check_expired_package', array($user_id));
            }
            // jobportal_send_email($admin_email, 'admin_mail_activated_package', $package_admin_args);
        }

        public function get_expired_date($package_id, $package_user_id)
        {
            $package_unlimited_time = get_post_meta($package_id, JOBPORTAL_METABOX_PREFIX . 'package_unlimited_time', true);
            if ($package_unlimited_time == 1) {
                $expired_date = esc_html__('Never Expires');
            } else {
                $expired_date = $this->get_expired_time($package_id, $package_user_id);
                $expired_date = date_i18n('Y-m-d', $expired_date);
            }
            return $expired_date;
        }

        public function get_expired_time($package_id, $package_user_id)
        {
            $expired_time = '';
            $package_time_unit = get_post_meta($package_id, JOBPORTAL_METABOX_PREFIX . 'package_time_unit', true);
            $package_period = get_post_meta($package_id, JOBPORTAL_METABOX_PREFIX . 'package_period', true);
            $package_activate_date = strtotime(get_user_meta($package_user_id, JOBPORTAL_METABOX_PREFIX . 'package_activate_date', true));
            $seconds = 0;
            switch ($package_time_unit) {
                case 'Day':
                    $seconds = 60 * 60 * 24;
                    break;
                case 'Week':
                    $seconds = 60 * 60 * 24 * 7;
                    break;
                case 'Month':
                    $seconds = 60 * 60 * 24 * 30;
                    break;
                case 'Year':
                    $seconds = 60 * 60 * 24 * 365;
                    break;
            }
            if (is_numeric($package_activate_date) && is_numeric($seconds) && is_numeric($package_period)) {
                $expired_time = $package_activate_date + ($seconds * $package_period);
            }
            return $expired_time;
        }

        /**
         * Count active jobs (publish status) for user
         * @param int $user_id
         * @return int
         */
        public function count_active_jobs($user_id)
        {
            $args = [
                'post_type' => 'jobs',
                'post_status' => 'publish',
                'author' => $user_id,
                'posts_per_page' => -1,
            ];
            $jobs = get_posts($args);
            wp_reset_postdata();
            return count($jobs);
        }

        /**
         * Count active featured jobs for user
         * @param int $user_id
         * @return int
         */
        public function count_active_featured_jobs($user_id)
        {
            $args = [
                'post_type' => 'jobs',
                'post_status' => 'publish',
                'author' => $user_id,
                'posts_per_page' => -1,
                'meta_query' => [
                    ['key' => 'jobs_featured', 'value' => '1']
                ]
            ];
            $jobs = get_posts($args);
            wp_reset_postdata();
            return count($jobs);
        }

        /**
         * Auto expire oldest jobs
         * @param int $user_id
         * @param int $count
         * @return int Number of jobs expired
         */
        public function auto_expire_jobs($user_id, $count)
        {
            // Edge case: count must be positive
            if ($count <= 0) {
                return 0;
            }

            global $wpdb;

            // PERFORMANCE: Get IDs to expire with single query
            $job_ids = $wpdb->get_col($wpdb->prepare("
                SELECT ID
                FROM {$wpdb->posts}
                WHERE post_author = %d
                    AND post_type = 'jobs'
                    AND post_status = 'publish'
                ORDER BY post_date ASC
                LIMIT %d
            ", $user_id, $count));

            if (empty($job_ids)) {
                return 0;
            }

            // PERFORMANCE: Bulk update status in single query
            $ids_placeholder = implode(',', array_map('intval', $job_ids));
            $wpdb->query("
                UPDATE {$wpdb->posts}
                SET post_status = 'expired'
                WHERE ID IN ($ids_placeholder)
            ");

            // Clear caches for updated posts
            foreach ($job_ids as $id) {
                clean_post_cache($id);
            }

            return count($job_ids);
        }

        /**
         * Auto un-feature newest jobs
         * @param int $user_id
         * @param int $count
         * @return int Number of jobs un-featured
         */
        public function auto_unfeature_jobs($user_id, $count)
        {
            // Edge case: count must be positive
            if ($count <= 0) {
                return 0;
            }

            global $wpdb;

            // PERFORMANCE: Get IDs to unfeature with single query
            $job_ids = $wpdb->get_col($wpdb->prepare("
                SELECT p.ID
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                WHERE p.post_author = %d
                    AND p.post_type = 'jobs'
                    AND p.post_status = 'publish'
                    AND pm.meta_key = 'jobs_featured'
                    AND pm.meta_value = '1'
                ORDER BY p.post_date DESC
                LIMIT %d
            ", $user_id, $count));

            if (empty($job_ids)) {
                return 0;
            }

            // PERFORMANCE: Bulk update meta in single query
            $ids_placeholder = implode(',', array_map('intval', $job_ids));
            $wpdb->query("
                UPDATE {$wpdb->postmeta}
                SET meta_value = '0'
                WHERE meta_key = 'jobs_featured'
                    AND post_id IN ($ids_placeholder)
            ");

            // Clear caches for updated posts
            foreach ($job_ids as $id) {
                clean_post_cache($id);
            }

            return count($job_ids);
        }

        /**
         * Auto unfollow newest candidates
         * @param int $user_id
         * @param int $count
         * @return int Number of candidates unfollowed
         */
        public function auto_unfollow_candidates($user_id, $count)
        {
            // Edge case: count must be positive
            if ($count <= 0) {
                return 0;
            }

            $follows = get_user_meta($user_id, 'follow_candidate', true) ?: [];
            if (empty($follows)) {
                return 0;
            }
            $original_count = count($follows);
            // Remove from end (newest added)
            $follows = array_slice($follows, 0, -$count);
            update_user_meta($user_id, 'follow_candidate', $follows);
            return $original_count - count($follows);
        }

        /**
         * Get package quotas
         * @param int $package_id
         * @return array
         */
        public function get_package_quotas($package_id)
        {
            $quotas = [];

            // PERFORMANCE: Get ALL meta in one query instead of 7 separate queries
            $all_meta = get_post_meta($package_id);

            // Helper function to safely get meta value
            $get_meta = function($key) use ($all_meta) {
                $full_key = JOBPORTAL_METABOX_PREFIX . $key;
                return isset($all_meta[$full_key][0]) ? $all_meta[$full_key][0] : '';
            };

            // Jobs
            $quotas['jobs'] = intval($get_meta('package_number_job'));
            $quotas['jobs_unlimited'] = ($get_meta('package_unlimited_job') == 1);
            if ($quotas['jobs_unlimited']) {
                $quotas['jobs'] = 999999999999999999;
            }

            // Featured jobs
            $quotas['jobs_featured'] = intval($get_meta('package_number_featured'));
            $quotas['jobs_featured_unlimited'] = false; // No unlimited option for featured

            // Candidate follow
            $quotas['candidate_follow'] = intval($get_meta('company_package_number_candidate_follow'));
            $quotas['candidate_follow_unlimited'] = ($get_meta('enable_package_candidate_follow_unlimited') == 1);
            $quotas['candidate_follow_enabled'] = !empty($quotas['candidate_follow']) || $quotas['candidate_follow_unlimited'];
            if ($quotas['candidate_follow_unlimited']) {
                $quotas['candidate_follow'] = 999999999999999999;
            }

            // Download CV
            $quotas['download_cv'] = intval($get_meta('company_package_number_download_cv'));
            $quotas['download_cv_unlimited'] = ($get_meta('enable_package_download_cv_unlimited') == 1);
            if ($quotas['download_cv_unlimited']) {
                $quotas['download_cv'] = 999999999999999999;
            }

            return $quotas;
        }

        /**
         * Get current resource counts
         * @param int $user_id
         * @return array
         */
        public function get_current_resource_counts($user_id)
        {
            global $wpdb;
            $counts = [];

            // PERFORMANCE: Combine both job queries into one with conditional counting
            $sql = $wpdb->prepare("
                SELECT
                    COUNT(*) as total_jobs,
                    SUM(CASE WHEN pm.meta_value = '1' THEN 1 ELSE 0 END) as featured_jobs
                FROM {$wpdb->posts} p
                LEFT JOIN {$wpdb->postmeta} pm
                    ON p.ID = pm.post_id
                    AND pm.meta_key = 'jobs_featured'
                WHERE p.post_author = %d
                    AND p.post_type = 'jobs'
                    AND p.post_status = 'publish'
            ", $user_id);

            $result = $wpdb->get_row($sql);

            $counts['active_jobs'] = intval($result->total_jobs);
            $counts['featured_jobs'] = intval($result->featured_jobs);

            // Candidate follows
            $follows = get_user_meta($user_id, 'follow_candidate', true) ?: [];
            $counts['follow_count'] = is_array($follows) ? count($follows) : 0;

            return $counts;
        }

        /**
         * Calculate package switch impact
         * @param int $user_id
         * @param int $new_package_id
         * @return array
         */
        public function calculate_package_switch_impact($user_id, $new_package_id)
        {
            $current_package_info = $this->get_active_package_info($user_id);

            if (!$current_package_info) {
                return array(
                    'has_active_package' => false,
                    'needs_warning' => false
                );
            }

            // Get new package quotas
            $new_package_quotas = $this->get_package_quotas($new_package_id);

            // Get current resource counts
            $current_resources = $this->get_current_resource_counts($user_id);

            // Calculate impact
            $impact = array(
                'has_active_package' => true,
                'current_package' => $current_package_info,
                'new_package_id' => $new_package_id,
                'new_package_name' => get_the_title($new_package_id),
                'new_package_quotas' => $new_package_quotas,
                'cleanup_required' => array(),
                'quota_changes' => array()
            );

            // Check jobs (state-based)
            if (!$new_package_quotas['jobs_unlimited']) {
                $excess = $current_resources['active_jobs'] - $new_package_quotas['jobs'];
                if ($excess > 0) {
                    $impact['cleanup_required']['jobs'] = array(
                        'current' => $current_resources['active_jobs'],
                        'new_limit' => $new_package_quotas['jobs'],
                        'to_remove' => $excess,
                        'action' => 'expire'
                    );
                }
            }

            // Check featured jobs (state-based)
            if (!$new_package_quotas['jobs_featured_unlimited']) {
                $excess = $current_resources['featured_jobs'] - $new_package_quotas['jobs_featured'];
                if ($excess > 0) {
                    $impact['cleanup_required']['jobs_featured'] = array(
                        'current' => $current_resources['featured_jobs'],
                        'new_limit' => $new_package_quotas['jobs_featured'],
                        'to_remove' => $excess,
                        'action' => 'un-feature'
                    );
                }
            }

            // Check candidate follow (state-based)
            if (isset($new_package_quotas['candidate_follow']) && !$new_package_quotas['candidate_follow_unlimited']) {
                $excess = $current_resources['follow_count'] - $new_package_quotas['candidate_follow'];
                if ($excess > 0) {
                    $impact['cleanup_required']['candidate_follow'] = array(
                        'current' => $current_resources['follow_count'],
                        'new_limit' => $new_package_quotas['candidate_follow'],
                        'to_remove' => $excess,
                        'action' => 'unfollow'
                    );
                }
            }

            // Check if warning is needed
            $impact['needs_warning'] = !empty($impact['cleanup_required']) ||
                ($current_package_info['remaining_quota'] &&
                 array_sum(array_filter($current_package_info['remaining_quota'], 'is_numeric')) > 0);

            return $impact;
        }

        /**
         * Activate package with cleanup
         * @param int $user_id
         * @param int $new_package_id
         * @param string $invoice_pdf_id
         * @return array
         */
        public function activate_package_with_cleanup($user_id, $new_package_id, $invoice_pdf_id = '')
        {
            $cleanup_results = array();

            // Get new package quotas
            $new_quotas = $this->get_package_quotas($new_package_id);
            $current_resources = $this->get_current_resource_counts($user_id);

            // Step 1: Auto cleanup state-based resources

            // Jobs
            if (!$new_quotas['jobs_unlimited']) {
                $excess = $current_resources['active_jobs'] - $new_quotas['jobs'];
                if ($excess > 0) {
                    $cleanup_results['jobs'] = $this->auto_expire_jobs($user_id, $excess);
                }
            }

            // Featured jobs
            if (!$new_quotas['jobs_featured_unlimited']) {
                $excess = $current_resources['featured_jobs'] - $new_quotas['jobs_featured'];
                if ($excess > 0) {
                    $cleanup_results['featured_jobs'] = $this->auto_unfeature_jobs($user_id, $excess);
                }
            }

            // Candidate follow
            if ($new_quotas['candidate_follow_enabled'] && !$new_quotas['candidate_follow_unlimited']) {
                $excess = $current_resources['follow_count'] - $new_quotas['candidate_follow'];
                if ($excess > 0) {
                    $cleanup_results['candidate_follow'] = $this->auto_unfollow_candidates($user_id, $excess);
                }
            }

            // Step 2: Call original insert function (skip cleanup since we already did it)
            $this->insert_user_package($user_id, $new_package_id, $invoice_pdf_id, true);

            return array(
                'success' => true,
                'cleanup_results' => $cleanup_results
            );
        }

        /**
         * AJAX handler for checking package impact
         */
        public function ajax_check_package_impact()
        {
            check_ajax_referer('jobportal_package_nonce', 'security');

            $user_id = get_current_user_id();
            $new_package_id = isset($_POST['package_id']) ? intval($_POST['package_id']) : 0;

            if (!$user_id || !$new_package_id) {
                wp_send_json_error(['message' => 'Invalid request']);
            }

            $impact = $this->calculate_package_switch_impact($user_id, $new_package_id);

            wp_send_json_success($impact);
        }

        /**
         * Check if user has active package that hasn't expired
         * @param $user_id
         * @return array|false Returns package info if active, false otherwise
         */
        public function get_active_package_info($user_id)
        {
            $package_id = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'package_id', $user_id);
            if (empty($package_id)) {
                return false;
            }

            $package_unlimited_time = get_post_meta($package_id, JOBPORTAL_METABOX_PREFIX . 'package_unlimited_time', true);
            $current_date = date('Y-m-d');
            $expired_date = $this->get_expired_date($package_id, $user_id);

            // Check if package is expired
            if ($package_unlimited_time != 1 && $current_date >= $expired_date) {
                return false;
            }

            // Get package info
            $package_name = get_the_title($package_id);
            $activate_date = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'package_activate_date', true);

            // Get remaining quota
            $remaining_quota = array();

            $remaining_quota['jobs'] = intval(get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'package_number_job', true));
            $remaining_quota['jobs_featured'] = intval(get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'package_number_featured', true));
            $remaining_quota['candidate_follow'] = intval(get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'package_number_candidate_follow', true));
            $remaining_quota['download_cv'] = intval(get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'package_number_download_cv', true));

            return array(
                'package_id' => $package_id,
                'package_name' => $package_name,
                'expired_date' => $expired_date,
                'expired_date_format' => ($expired_date !== 'Never Expires') ? date_i18n(get_option('date_format'), strtotime($expired_date)) : esc_html__('Never Expires', 'jobportal-framework'),
                'activate_date' => $activate_date,
                'remaining_quota' => $remaining_quota,
                'is_unlimited_time' => ($package_unlimited_time == 1)
            );
        }
    }
}
