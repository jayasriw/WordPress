<?php
if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('Civi_candidate_package')) {
    /**
     * Class Civi_candidate_package
     */
    class Civi_candidate_package
    {
        /**
         * get_time_unit
         * @param $time_unit
         * @return null|string
         */
        public static function get_time_unit($time_unit)
        {
            if ($time_unit == 'Day') {
                return esc_html__('day', 'civi-framework');
            } else if ($time_unit == 'Day') {
                return esc_html__('days', 'civi-framework');
            } else if ($time_unit == 'Week') {
                return esc_html__('week', 'civi-framework');
            } else if ($time_unit == 'Weeks') {
                return esc_html__('weeks', 'civi-framework');
            } else if ($time_unit == 'Month') {
                return esc_html__('month', 'civi-framework');
            } else if ($time_unit == 'Months') {
                return esc_html__('months', 'civi-framework');
            } else if ($time_unit == 'Year') {
                return esc_html__('year', 'civi-framework');
            } else if ($time_unit == 'Years') {
                return esc_html__('years', 'civi-framework');
            }
            return null;
        }

        /**
         * Insert service candidate_package
         * @param $user_id
         * @param $candidate_package_id
         */
        public function insert_user_candidate_package($user_id, $candidate_package_id)
        {
            //Service - Reset quota về giá trị gói mới (không cộng dồn)
            $candidate_package_number_service = get_post_meta($candidate_package_id, CIVI_METABOX_PREFIX . 'candidate_package_number_service', true);
            $candidate_package_number_service_featured = get_post_meta($candidate_package_id, CIVI_METABOX_PREFIX . 'candidate_package_number_service_featured', true);
            $enable_package_service_unlimited = get_post_meta($candidate_package_id, CIVI_METABOX_PREFIX . 'enable_package_service_unlimited', true);
            $enable_package_service_featured_unlimited = get_post_meta($candidate_package_id, CIVI_METABOX_PREFIX . 'enable_package_service_featured_unlimited', true);

            if ($enable_package_service_unlimited == 1) {
                $candidate_package_number_service = 999999999999999999;
            }

            // Reset service quota
            update_user_meta($user_id, CIVI_METABOX_PREFIX . 'candidate_package_number_service', $candidate_package_number_service);

            // Featured services: Đếm số featured services đang active và trừ vào quota gói mới
            if ($enable_package_service_featured_unlimited == 1) {
                // Unlimited featured, không cần trừ
                update_user_meta($user_id, CIVI_METABOX_PREFIX . 'candidate_package_number_service_featured', 999999999999999999);
            } else {
                // Đếm số featured services đang active
                $active_featured_count = $this->count_active_featured_services($user_id);
                // Trừ số featured services đang active vào quota gói mới
                $remaining_featured = intval($candidate_package_number_service_featured) - intval($active_featured_count);
                // Đảm bảo quota không âm
                $remaining_featured = max(0, $remaining_featured);
                update_user_meta($user_id, CIVI_METABOX_PREFIX . 'candidate_package_number_service_featured', $remaining_featured);
            }

            	//Field - Reset quota về giá trị gói mới cho tất cả các field (jobs_apply, jobs_wishlist, company_follow, etc.)
	// State-based fields need to deduct current usage, action-based fields just reset
	$field_package = array('jobs_apply', 'jobs_wishlist', 'company_follow');
	$state_based_fields = array('jobs_wishlist', 'company_follow'); // These need current count deducted

	foreach ($field_package as $field) {
		$show_field = get_post_meta($candidate_package_id, CIVI_METABOX_PREFIX . 'show_package_' . $field, true);
		$field_number = get_post_meta($candidate_package_id, CIVI_METABOX_PREFIX . 'candidate_package_number_' . $field, true);
		$field_unlimited = get_post_meta($candidate_package_id, CIVI_METABOX_PREFIX . 'enable_package_' . $field . '_unlimited', true);
		if (intval($show_field) == 1) {
			if ($field_unlimited == 1) {
				$field_number = 999999999999999999;
			} else if (in_array($field, $state_based_fields)) {
				// STATE-BASED: Deduct current usage from quota (like featured services)
				$current_count = 0;

				if ($field === 'jobs_wishlist') {
					// Count current wishlist items
					$wishlist = get_user_meta($user_id, 'my_wishlist', true);
					$current_count = is_array($wishlist) ? count($wishlist) : 0;
				} else if ($field === 'company_follow') {
					// Count current follows
					$follows = get_user_meta($user_id, 'my_follow', true);
					$current_count = is_array($follows) ? count($follows) : 0;
				}

				// Deduct current usage from new quota
				$field_number = intval($field_number) - $current_count;
				// Ensure quota is not negative
				$field_number = max(0, $field_number);
			}
			// ACTION-BASED (jobs_apply, etc.): Just reset to package quota (no deduction needed)

			update_user_meta($user_id, CIVI_METABOX_PREFIX . 'candidate_package_number_' . $field, $field_number);
		}
	}

            do_action('civi_ajax_field_package_candidate', $user_id, $candidate_package_id);

            $time = time();
            $date = date('Y-m-d H:i:s', $time);
            update_user_meta($user_id, CIVI_METABOX_PREFIX . 'candidate_package_activate_date', $date);
            update_user_meta($user_id, CIVI_METABOX_PREFIX . 'candidate_package_id', $candidate_package_id);
            $candidate_package_key = uniqid();
            update_user_meta($user_id, CIVI_METABOX_PREFIX . 'candidate_package_key', $candidate_package_key);
        }

        public function get_expired_date($candidate_package_id, $candidate_package_user_id)
        {
            $enable_package_service_unlimited_time = get_post_meta($candidate_package_id, CIVI_METABOX_PREFIX . 'enable_package_service_unlimited_time', true);
            if ($enable_package_service_unlimited_time == 1) {
                $expired_date = esc_html__('Never Expires');
            } else {
                $expired_date = $this->get_expired_time($candidate_package_id, $candidate_package_user_id);
                $expired_date = date_i18n('Y-m-d', $expired_date);
            }
            return $expired_date;
        }

        public function get_expired_time($candidate_package_id,$candidate_package_user_id)
        {
            $expired_time = '';
            $candidate_package_time_unit = get_post_meta($candidate_package_id, CIVI_METABOX_PREFIX . 'candidate_package_time_unit', true);
            $candidate_package_period = get_post_meta($candidate_package_id, CIVI_METABOX_PREFIX . 'candidate_package_period', true);
            $candidate_package_activate_date = strtotime(get_user_meta($candidate_package_user_id, CIVI_METABOX_PREFIX . 'candidate_package_activate_date', true));
            $seconds = 0;
            switch ($candidate_package_time_unit) {
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
            if (is_numeric($candidate_package_activate_date) && is_numeric($seconds) && is_numeric($candidate_package_period)) {
                $expired_time = $candidate_package_activate_date + ($seconds * $candidate_package_period);
            }
            return $expired_time;
        }

        public function user_candidate_package_available($user_id)
        {
            $candidate_paid_submission_type      = civi_get_option( 'candidate_paid_submission_type' );
            if($candidate_paid_submission_type == 'candidate_per_package') {
                $candidate_package_id = get_the_author_meta(CIVI_METABOX_PREFIX . 'candidate_package_id', $user_id);
                if (empty($candidate_package_id)) {
                    return 0;
                } else {
                    $enable_package_service_unlimited_time = get_post_meta($candidate_package_id, CIVI_METABOX_PREFIX . 'enable_package_service_unlimited_time', true);
                    if ($enable_package_service_unlimited_time == 0) {
                        $expired_date = strtotime($this->get_expired_date($candidate_package_id, $user_id));
                        $current_date = strtotime(date('Y-m-d'));
                        if ($current_date >= $expired_date) {
                            return -1;
                        }
                    }
                }
            }
            return 1;
        }

        public function get_service_expired($user_id)
        {
            $check_candidate_package = $this->user_candidate_package_available($user_id);
            $args_expired = array(
                'post_type'           => 'service',
                'post_status'         => 'pause',
                'posts_per_page'      => -1,
                'author'              => $user_id,
            );
            $data_expired = new WP_Query($args_expired);
            if ($data_expired->have_posts()) {
                while ($data_expired->have_posts()) : $data_expired->the_post();
                    $service_id =  get_the_ID();
                endwhile;
            }
        }

        /**
         * Count active featured services for user
         * @param $user_id
         * @return int
         */
        private function count_active_featured_services($user_id)
        {
            $args = array(
                'post_type' => 'service',
                'post_status' => 'publish',
                'author' => $user_id,
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => CIVI_METABOX_PREFIX . 'service_featured',
                        'value' => '1',
                        'compare' => '='
                    )
                )
            );
            $query = new WP_Query($args);
            return intval($query->found_posts);
        }

        /**
         * Check if user has active package that hasn't expired
         * @param $user_id
         * @return array|false Returns package info if active, false otherwise
         */
        public function get_active_package_info($user_id)
        {
            $candidate_package_id = get_the_author_meta(CIVI_METABOX_PREFIX . 'candidate_package_id', $user_id);
            if (empty($candidate_package_id)) {
                return false;
            }

            $enable_package_service_unlimited_time = get_post_meta($candidate_package_id, CIVI_METABOX_PREFIX . 'enable_package_service_unlimited_time', true);
            $current_date = date('Y-m-d');
            $expired_date = $this->get_expired_date($candidate_package_id, $user_id);

            // Check if package is expired
            if ($enable_package_service_unlimited_time != 1 && $current_date >= $expired_date) {
                return false;
            }

            // Get package info
            $package_name = get_the_title($candidate_package_id);
            $activate_date = get_user_meta($user_id, CIVI_METABOX_PREFIX . 'candidate_package_activate_date', true);

            // Check if service feature is enabled
            $enable_post_type_service = civi_get_option('enable_post_type_service');

            // Get remaining quota
            $remaining_quota = array();

            // Only include service quota if service feature is enabled
            if ($enable_post_type_service === '1') {
                $remaining_quota['service'] = intval(get_user_meta($user_id, CIVI_METABOX_PREFIX . 'candidate_package_number_service', true));
                $remaining_quota['service_featured'] = intval(get_user_meta($user_id, CIVI_METABOX_PREFIX . 'candidate_package_number_service_featured', true));
            }

            $remaining_quota['jobs_apply'] = intval(get_user_meta($user_id, CIVI_METABOX_PREFIX . 'candidate_package_number_jobs_apply', true));
            $remaining_quota['jobs_wishlist'] = intval(get_user_meta($user_id, CIVI_METABOX_PREFIX . 'candidate_package_number_jobs_wishlist', true));
            $remaining_quota['company_follow'] = intval(get_user_meta($user_id, CIVI_METABOX_PREFIX . 'candidate_package_number_company_follow', true));

            return array(
                'package_id' => $candidate_package_id,
                'package_name' => $package_name,
                'expired_date' => $expired_date,
                'expired_date_format' => ($expired_date !== 'Never Expires') ? date_i18n(get_option('date_format'), strtotime($expired_date)) : esc_html__('Never Expires', 'civi-framework'),
                'activate_date' => $activate_date,
                'remaining_quota' => $remaining_quota,
                'is_unlimited_time' => ($enable_package_service_unlimited_time == 1)
            );
        }

        /**
         * Calculate the impact of switching to a new package
         * @param int $user_id
         * @param int $new_package_id
         * @return array Impact data
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

            // Calculate impact for state-based resources
            $impact = array(
                'has_active_package' => true,
                'current_package' => $current_package_info,
                'new_package_id' => $new_package_id,
                'new_package_name' => get_the_title($new_package_id),
                'new_package_quotas' => $new_package_quotas,
                'cleanup_required' => array(),
                'quota_changes' => array()
            );

            // Check featured services (state-based)
            if (isset($new_package_quotas['service_featured']) && !$new_package_quotas['service_featured_unlimited']) {
                $excess = $current_resources['featured_services'] - $new_package_quotas['service_featured'];
                if ($excess > 0) {
                    $impact['cleanup_required']['featured_services'] = array(
                        'current' => $current_resources['featured_services'],
                        'new_limit' => $new_package_quotas['service_featured'],
                        'to_remove' => $excess,
                        'action' => 'un-feature'
                    );
                }
            }

            // Check wishlist (state-based)
            if (isset($new_package_quotas['jobs_wishlist']) && !$new_package_quotas['jobs_wishlist_unlimited']) {
                $excess = $current_resources['wishlist_count'] - $new_package_quotas['jobs_wishlist'];
                if ($excess > 0) {
                    $impact['cleanup_required']['jobs_wishlist'] = array(
                        'current' => $current_resources['wishlist_count'],
                        'new_limit' => $new_package_quotas['jobs_wishlist'],
                        'to_remove' => $excess,
                        'action' => 'remove'
                    );
                }
            }

            // Check company follow (state-based)
            if (isset($new_package_quotas['company_follow']) && !$new_package_quotas['company_follow_unlimited']) {
                $excess = $current_resources['follow_count'] - $new_package_quotas['company_follow'];
                if ($excess > 0) {
                    $impact['cleanup_required']['company_follow'] = array(
                        'current' => $current_resources['follow_count'],
                        'new_limit' => $new_package_quotas['company_follow'],
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
         * Get package quotas configuration
         * @param int $package_id
         * @return array
         */
        public function get_package_quotas($package_id)
        {
            $quotas = array();

            // Service quotas
            $quotas['service'] = intval(get_post_meta($package_id, CIVI_METABOX_PREFIX . 'candidate_package_number_service', true));
            $quotas['service_unlimited'] = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'enable_package_service_unlimited', true) == 1;

            $quotas['service_featured'] = intval(get_post_meta($package_id, CIVI_METABOX_PREFIX . 'candidate_package_number_service_featured', true));
            $quotas['service_featured_unlimited'] = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'enable_package_service_featured_unlimited', true) == 1;

            // Field quotas
            $fields = array('jobs_apply', 'jobs_wishlist', 'company_follow', 'send_message', 'review_and_commnent');
            foreach ($fields as $field) {
                $quotas[$field] = intval(get_post_meta($package_id, CIVI_METABOX_PREFIX . 'candidate_package_number_' . $field, true));
                $quotas[$field . '_unlimited'] = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'enable_package_' . $field . '_unlimited', true) == 1;
                $quotas[$field . '_enabled'] = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'show_package_' . $field, true) == 1;
            }

            return $quotas;
        }

        /**
         * Get current resource counts for user (optimized with single queries)
         * @param int $user_id
         * @return array
         */
        public function get_current_resource_counts($user_id)
        {
            global $wpdb;

            // Get featured services count with direct SQL for performance
            $featured_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(p.ID) FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                WHERE p.post_type = 'service'
                AND p.post_status = 'publish'
                AND p.post_author = %d
                AND pm.meta_key = %s
                AND pm.meta_value = '1'",
                $user_id,
                CIVI_METABOX_PREFIX . 'service_featured'
            ));

            // Get wishlist and follow counts
            $wishlist = get_user_meta($user_id, CIVI_METABOX_PREFIX . 'my_wishlist', true);
            $follows = get_user_meta($user_id, CIVI_METABOX_PREFIX . 'my_follow', true);

            return array(
                'featured_services' => intval($featured_count),
                'wishlist_count' => is_array($wishlist) ? count($wishlist) : 0,
                'follow_count' => is_array($follows) ? count($follows) : 0
            );
        }

        /**
         * Auto un-feature services when exceeding quota (newest first)
         * @param int $user_id
         * @param int $count Number of services to un-feature
         * @return int Number of services un-featured
         */
        public function auto_unfeature_services($user_id, $count)
        {
            if ($count <= 0) return 0;

            global $wpdb;

            // Get featured services ordered by date DESC (newest first) with direct SQL for performance
            $service_ids = $wpdb->get_col($wpdb->prepare(
                "SELECT p.ID FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                WHERE p.post_type = 'service'
                AND p.post_status = 'publish'
                AND p.post_author = %d
                AND pm.meta_key = %s
                AND pm.meta_value = '1'
                ORDER BY p.post_date DESC
                LIMIT %d",
                $user_id,
                CIVI_METABOX_PREFIX . 'service_featured',
                $count
            ));

            $unfeatured = 0;
            foreach ($service_ids as $service_id) {
                update_post_meta($service_id, CIVI_METABOX_PREFIX . 'service_featured', '0');
                $unfeatured++;
            }

            return $unfeatured;
        }

        /**
         * Auto remove wishlist items when exceeding quota (newest first)
         * @param int $user_id
         * @param int $count Number of items to remove
         * @return int Number of items removed
         */
        public function auto_remove_wishlist_items($user_id, $count)
        {
            if ($count <= 0) return 0;

            $wishlist = get_user_meta($user_id, CIVI_METABOX_PREFIX . 'my_wishlist', true);

            if (!is_array($wishlist) || empty($wishlist)) {
                return 0;
            }

            $original_count = count($wishlist);

            // Remove from end of array (newest items - assuming array_push was used to add)
            $wishlist = array_slice($wishlist, 0, -$count);

            update_user_meta($user_id, CIVI_METABOX_PREFIX . 'my_wishlist', $wishlist);

            return $original_count - count($wishlist);
        }

        /**
         * Auto unfollow companies when exceeding quota (newest first)
         * @param int $user_id
         * @param int $count Number of companies to unfollow
         * @return int Number of companies unfollowed
         */
        public function auto_unfollow_companies($user_id, $count)
        {
            if ($count <= 0) return 0;

            $follows = get_user_meta($user_id, CIVI_METABOX_PREFIX . 'my_follow', true);

            if (!is_array($follows) || empty($follows)) {
                return 0;
            }

            $original_count = count($follows);

            // Remove from end of array (newest follows)
            $follows = array_slice($follows, 0, -$count);

            update_user_meta($user_id, CIVI_METABOX_PREFIX . 'my_follow', $follows);

            return $original_count - count($follows);
        }

        /**
         * Process package activation with auto cleanup
         * @param int $user_id
         * @param int $new_package_id
         * @return array Result of activation
         */
        public function activate_package_with_cleanup($user_id, $new_package_id)
        {
            $cleanup_results = array();

            // Get new package quotas
            $new_quotas = $this->get_package_quotas($new_package_id);
            $current_resources = $this->get_current_resource_counts($user_id);

            // Step 1: Auto cleanup state-based resources

            // Featured services
            if (!$new_quotas['service_featured_unlimited']) {
                $excess = $current_resources['featured_services'] - $new_quotas['service_featured'];
                if ($excess > 0) {
                    $cleanup_results['featured_services'] = $this->auto_unfeature_services($user_id, $excess);
                }
            }

            // Wishlist
            if ($new_quotas['jobs_wishlist_enabled'] && !$new_quotas['jobs_wishlist_unlimited']) {
                $excess = $current_resources['wishlist_count'] - $new_quotas['jobs_wishlist'];
                if ($excess > 0) {
                    $cleanup_results['wishlist'] = $this->auto_remove_wishlist_items($user_id, $excess);
                }
            }

            // Company follow
            if ($new_quotas['company_follow_enabled'] && !$new_quotas['company_follow_unlimited']) {
                $excess = $current_resources['follow_count'] - $new_quotas['company_follow'];
                if ($excess > 0) {
                    $cleanup_results['company_follow'] = $this->auto_unfollow_companies($user_id, $excess);
                }
            }

            // Step 2: Call original insert function
            $this->insert_user_candidate_package($user_id, $new_package_id);

            return array(
                'success' => true,
                'cleanup_results' => $cleanup_results
            );
        }

        /**
         * AJAX handler for checking package switch impact
         */
        public function ajax_check_package_impact()
        {
            // Verify nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'civi_package_warning_nonce')) {
                wp_send_json_error(array('message' => esc_html__('Invalid security token', 'civi-framework')));
                return;
            }

            // Check user logged in
            if (!is_user_logged_in()) {
                wp_send_json_error(array('message' => esc_html__('Please login first', 'civi-framework')));
                return;
            }

            $user_id = get_current_user_id();
            $new_package_id = isset($_POST['new_package_id']) ? intval($_POST['new_package_id']) : 0;

            if (!$new_package_id) {
                wp_send_json_error(array('message' => esc_html__('Invalid package ID', 'civi-framework')));
                return;
            }

            // Calculate impact
            $impact = $this->calculate_package_switch_impact($user_id, $new_package_id);

            wp_send_json_success($impact);
        }
    }
}
