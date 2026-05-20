<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if (!class_exists('JobPortal_Admin_Jobs')) {
    /**
     * Class JobPortal_Admin_Jobs
     */
    class JobPortal_Admin_Jobs
    {
        /**
         * Constants
         */
        const POST_TYPE = 'jobs';
        const LOGO_MAX_WIDTH = 50;
        const SELECT2_MIN_OPTIONS = 10;
        const SELECT2_DEFAULT_WIDTH = '200px';
        const SELECT2_COMPANY_WIDTH = '250px';

        /**
         * Status labels
         */
        private static $status_labels = array(
            'publish' => 'Published',
            'pending' => 'Pending',
            'draft' => 'Draft',
            'expired' => 'Expired',
            'hidden' => 'Hidden',
            'trash' => 'Trash'
        );

        /**
         * Status styles
         */
        private static $status_styles = array(
            'publish' => array('bg' => '#f0f9ff', 'color' => '#0284c7', 'border' => '#bae6fd'),
            'pending' => array('bg' => '#fffbeb', 'color' => '#d97706', 'border' => '#fde68a'),
            'draft' => array('bg' => '#f9fafb', 'color' => '#6b7280', 'border' => '#e5e7eb'),
            'expired' => array('bg' => '#fef2f2', 'color' => '#dc2626', 'border' => '#fecaca'),
            'hidden' => array('bg' => '#f5f3ff', 'color' => '#7c3aed', 'border' => '#ddd6fe'),
            'trash' => array('bg' => '#fef2f2', 'color' => '#991b1b', 'border' => '#fecaca')
        );

        /**
         * Salary rate labels
         */
        private static $salary_rate_labels = array(
            'hour' => '/hour',
            'day' => '/day',
            'week' => '/week',
            'month' => '/month',
            'year' => '/year'
        );

        /**
         * Get company logo URL
         * @param int $company_id
         * @return string
         */
        private function get_company_logo_url($company_id)
        {
            if (!$company_id) {
                return '';
            }

            $company_logo = get_post_meta($company_id, JOBPORTAL_METABOX_PREFIX . 'company_logo');
            if (!empty($company_logo) && is_array($company_logo) && isset($company_logo[0]) && is_array($company_logo[0]) && isset($company_logo[0]['url'])) {
                return $company_logo[0]['url'];
            }

            return '';
        }

        /**
         * Get company ID from job
         * @param int $post_id
         * @return int
         */
        private function get_job_company_id($post_id)
        {
            $jobs_select_company = get_post_meta($post_id, JOBPORTAL_METABOX_PREFIX . 'jobs_select_company');
            return isset($jobs_select_company[0]) ? absint($jobs_select_company[0]) : 0;
        }

        /**
         * Format salary rate text
         * @param string $rate
         * @return string
         */
        private function format_salary_rate($rate)
        {
            if (empty($rate)) {
                return '';
            }

            if (isset(self::$salary_rate_labels[$rate])) {
                return esc_html__(self::$salary_rate_labels[$rate], 'jobportal-framework');
            }

            return '/' . $rate;
        }

        /**
         * Format salary amount
         * @param mixed $amount
         * @return string
         */
        private function format_salary_amount($amount)
        {
            return number_format(floatval($amount), 0, '.', ',');
        }

        /**
         * Get status label
         * @param string $status
         * @return string
         */
        private function get_status_label($status)
        {
            if (isset(self::$status_labels[$status])) {
                return esc_html__(self::$status_labels[$status], 'jobportal-framework');
            }
            return ucfirst($status);
        }

        /**
         * Get status style
         * @param string $status
         * @return array
         */
        private function get_status_style($status)
        {
            return isset(self::$status_styles[$status]) ? self::$status_styles[$status] : self::$status_styles['draft'];
        }

        /**
         * Display thumb column
         * @param int $post_id
         */
        private function display_thumb_column($post_id)
        {
            $company_id = $this->get_job_company_id($post_id);
            $logo_url = $this->get_company_logo_url($company_id);

            if (!empty($logo_url)) {
                $style = sprintf('max-width: %dpx; height: auto;', self::LOGO_MAX_WIDTH);
                echo '<img src="' . esc_url($logo_url) . '" alt="' . esc_attr(get_the_title($company_id)) . '" style="' . esc_attr($style) . '"/>';
            } else {
                $placeholder_url = JOBPORTAL_PLUGIN_URL . 'assets/images/job-img.webp';
                $style = sprintf('max-width: %dpx; height: auto; border-radius: 50%%; border: 3px solid #eee;', self::LOGO_MAX_WIDTH);
                echo '<img src="' . esc_url($placeholder_url) . '" alt="' . esc_attr__('No image', 'jobportal-framework') . '" style="' . esc_attr($style) . '"/>';
            }
        }

        /**
         * Display status column
         * @param string $status
         */
        private function display_status_column($status)
        {
            $status_label = $this->get_status_label($status);
            $style = $this->get_status_style($status);

            $badge_style = sprintf(
                'display: inline-block; padding: 4px 10px; border-radius: 6px; background-color: %s; color: %s; border: 1px solid %s; font-size: 12px; font-weight: 500; letter-spacing: 0.3px; line-height: 1.4;',
                esc_attr($style['bg']),
                esc_attr($style['color']),
                esc_attr($style['border'])
            );

            echo '<span style="' . $badge_style . '">' . esc_html($status_label) . '</span>';
        }

        /**
         * Display company column
         * @param int $post_id
         */
        private function display_company_column($post_id)
        {
            $company_id = $this->get_job_company_id($post_id);

            if (!$company_id) {
                echo '&ndash;';
                return;
            }

            $company_title = get_the_title($company_id);
            if ($company_title) {
                echo '<a href="' . esc_url(get_edit_post_link($company_id)) . '">' . esc_html($company_title) . '</a>';
            } else {
                echo '&ndash;';
            }
        }

        /**
         * Display location column
         * @param int $post_id
         */
        private function display_location_column($post_id)
        {
            $locations = get_the_terms($post_id, 'jobs-location');

            if ($locations && !is_wp_error($locations)) {
                $location_names = array_map(function($location) {
                    return $location->name;
                }, $locations);
                echo esc_html(implode(', ', $location_names));
            } else {
                echo '&ndash;';
            }
        }

        /**
         * Display salary column
         * @param int $post_id
         */
        private function display_salary_column($post_id)
        {
            $salary_show = get_post_meta($post_id, JOBPORTAL_METABOX_PREFIX . 'jobs_salary_show', true);

            if (empty($salary_show)) {
                echo '&ndash;';
                return;
            }

            $currency = get_post_meta($post_id, JOBPORTAL_METABOX_PREFIX . 'jobs_currency_type', true) ?: '';
            $rate = get_post_meta($post_id, JOBPORTAL_METABOX_PREFIX . 'jobs_salary_rate', true);
            $rate_text = $this->format_salary_rate($rate);

            switch ($salary_show) {
                case 'range':
                    $this->display_salary_range($post_id, $currency, $rate_text);
                    break;

                case 'maximum_amount':
                    $this->display_salary_maximum($post_id, $currency, $rate_text);
                    break;

                case 'starting_amount':
                    $this->display_salary_starting($post_id, $currency, $rate_text);
                    break;

                case 'agree':
                    echo esc_html__('Negotiable', 'jobportal-framework');
                    break;

                default:
                    echo '&ndash;';
                    break;
            }
        }

        /**
         * Display salary range
         * @param int $post_id
         * @param string $currency
         * @param string $rate_text
         */
        private function display_salary_range($post_id, $currency, $rate_text)
        {
            $minimum = get_post_meta($post_id, JOBPORTAL_METABOX_PREFIX . 'jobs_salary_minimum', true);
            $maximum = get_post_meta($post_id, JOBPORTAL_METABOX_PREFIX . 'jobs_salary_maximum', true);

            if ($minimum && $maximum) {
                $min_formatted = $this->format_salary_amount($minimum);
                $max_formatted = $this->format_salary_amount($maximum);
                echo esc_html($currency . $min_formatted . ' - ' . $currency . $max_formatted . $rate_text);
            } elseif ($minimum) {
                $min_formatted = $this->format_salary_amount($minimum);
                echo esc_html(esc_html__('From', 'jobportal-framework') . ' ' . $currency . $min_formatted . $rate_text);
            } elseif ($maximum) {
                $max_formatted = $this->format_salary_amount($maximum);
                echo esc_html(esc_html__('Up to', 'jobportal-framework') . ' ' . $currency . $max_formatted . $rate_text);
            } else {
                echo '&ndash;';
            }
        }

        /**
         * Display salary maximum
         * @param int $post_id
         * @param string $currency
         * @param string $rate_text
         */
        private function display_salary_maximum($post_id, $currency, $rate_text)
        {
            $maximum = get_post_meta($post_id, JOBPORTAL_METABOX_PREFIX . 'jobs_maximum_price', true);

            if ($maximum) {
                $max_formatted = $this->format_salary_amount($maximum);
                echo esc_html(esc_html__('Up to', 'jobportal-framework') . ' ' . $currency . $max_formatted . $rate_text);
            } else {
                echo '&ndash;';
            }
        }

        /**
         * Display salary starting
         * @param int $post_id
         * @param string $currency
         * @param string $rate_text
         */
        private function display_salary_starting($post_id, $currency, $rate_text)
        {
            $minimum = get_post_meta($post_id, JOBPORTAL_METABOX_PREFIX . 'jobs_minimum_price', true);

            if ($minimum) {
                $min_formatted = $this->format_salary_amount($minimum);
                echo esc_html(esc_html__('From', 'jobportal-framework') . ' ' . $currency . $min_formatted . $rate_text);
            } else {
                echo '&ndash;';
            }
        }

        /**
         * Handle job status change via AJAX
         * @param int $post_id
         * @param string $new_status
         * @param string $email_template
         * @return bool|WP_Error
         */
        private function change_job_status($post_id, $new_status, $email_template = '')
        {
            $listing_data = array(
                'ID' => $post_id,
                'post_status' => $new_status
            );

            $result = wp_update_post($listing_data);

            if (is_wp_error($result)) {
                return $result;
            }

            // Send email if template provided
            if (!empty($email_template)) {
                $author_id = get_post_field('post_author', $post_id);
                $user = get_user_by('id', $author_id);

                if ($user) {
                    $args = array(
                        'job_title' => get_the_title($post_id),
                        'job_url' => get_permalink($post_id)
                    );

                    if ($email_template === 'mail_approved_listing') {
                        $args['user_login'] = $user->user_login;
                    }

                    jobportal_send_email($user->user_email, $email_template, $args);
                }
            }

            return true;
        }

        /**
         * Register custom columns
         * @param $columns
         * @return array
         */
        public function register_custom_column_titles($columns)
        {
            unset($columns['tags']);

            $columns['thumb']    = esc_html__('Logo', 'jobportal-framework');
            $columns['title']    = esc_html__('Jobs Title', 'jobportal-framework');
            $columns['status']   = esc_html__('Status', 'jobportal-framework');
            $columns['company']  = esc_html__('Company', 'jobportal-framework');
            $columns['type']     = esc_html__('Type', 'jobportal-framework');
            $columns['location'] = esc_html__('Location', 'jobportal-framework');
            $columns['salary']   = esc_html__('Salary', 'jobportal-framework');
            $columns['skills']   = esc_html__('Skills', 'jobportal-framework');
            $columns['featured'] = '<span data-tip="' . esc_html__('Featured?', 'jobportal-framework') . '" class="tips dashicons dashicons-star-filled"></span>';
            $columns['author']   = esc_html__('Author', 'jobportal-framework');

            $custom_order = [
                'cb',
                'thumb',
                'title',
                'status',
                'company',
                'type',
                'location',
                'salary',
                'skills',
                'featured',
                'author',
                'date'
            ];

            if (class_exists('WPSEO_Frontend')) {
                $custom_order = array_merge($custom_order, [
                    'wpseo-score',
                    'wpseo-score-readability',
                    'wpseo-title',
                    'wpseo-metadesc',
                    'wpseo-focuskw',
                    'wpseo-links',
                    'wpseo-linked'
                ]);
            }

            $new_columns = [];

            foreach ($custom_order as $colname) {
                if (isset($columns[$colname])) {
                    $new_columns[$colname] = $columns[$colname];
                }
            }

            foreach ($columns as $key => $value) {
                if (!isset($new_columns[$key])) {
                    $new_columns[$key] = $value;
                }
            }

            return $new_columns;
        }

        /**
         * Display custom column for jobs
         * @param $column
         */
        public function display_custom_column($column)
        {
            global $post;
            $post_id = $post->ID;
            switch ($column) {
                case 'thumb':
                    $this->display_thumb_column($post_id);
                    break;

                case 'status':
                    $this->display_status_column($post->post_status);
                    break;

                case 'company':
                    $this->display_company_column($post_id);
                    break;
                case 'type':
                    echo jobportal_admin_taxonomy_terms($post->ID, 'jobs-type', 'jobs');
                    break;
                case 'location':
                    $this->display_location_column($post->ID);
                    break;

                case 'salary':
                    $this->display_salary_column($post_id);
                    break;
                case 'skills':
                    echo jobportal_admin_taxonomy_terms($post->ID, 'jobs-skills', 'jobs');
                    break;
                case 'featured':
                    $featured = get_post_meta($post->ID, JOBPORTAL_METABOX_PREFIX . 'jobs_featured', true);
                    if ($featured == 1) {
                        echo '<i data-tip="' .  esc_html__('Featured', 'jobportal-framework') . '" class="tips accent-color dashicons dashicons-star-filled"></i>';
                    } else {
                        echo '<i data-tip="' .  esc_html__('Not Featured', 'jobportal-framework') . '" class="tips dashicons dashicons-star-empty"></i>';
                    }
                    break;
                case 'author':
                    echo '<a href="' . esc_url(add_query_arg('author', $post->post_author)) . '">' . get_the_author() . '</a>';
                    break;
            }
        }

        /**
         * Create AJAX action link
         * @param string $action
         * @param int $post_id
         * @param string $label
         * @return string
         */
        private function create_ajax_action_link($action, $post_id, $label)
        {
            $nonce = wp_create_nonce('jobportal_' . $action . '_job_' . $post_id);
            return '<a href="#" class="jobportal-ajax-action" data-action="' . esc_attr($action) . '" data-post-id="' . esc_attr($post_id) . '" data-nonce="' . esc_attr($nonce) . '">' . esc_html($label) . '</a>';
        }

        /**
         * Modify list row actions
         * @param array $actions
         * @param WP_Post $post
         * @return array
         */
        public function modify_list_row_actions($actions, $post)
        {
            if ($post->post_type !== self::POST_TYPE) {
                return $actions;
            }

            $post_id = $post->ID;
            $status = $post->post_status;

            // Approve action
            if (in_array($status, array('pending', 'expired'))) {
                $actions['jobs-approve'] = $this->create_ajax_action_link('approve', $post_id, esc_html__('Approve', 'jobportal-framework'));
            }

            // Expire action
            if (in_array($status, array('publish', 'pending'))) {
                $actions['jobs-expired'] = $this->create_ajax_action_link('expire', $post_id, esc_html__('Expire', 'jobportal-framework'));
            }

            // Hide action
            if ($status === 'publish') {
                $actions['jobs-hidden'] = $this->create_ajax_action_link('hide', $post_id, esc_html__('Hide', 'jobportal-framework'));
            }

            // Show action
            if ($status === 'hidden') {
                $actions['jobs-show'] = $this->create_ajax_action_link('show', $post_id, esc_html__('Show', 'jobportal-framework'));
            }

            return $actions;
        }

        /**
         * sortable_columns
         * @param $columns
         * @return mixed
         */
        public function sortable_columns($columns)
        {
            $columns['status'] = 'status';
            $columns['company'] = 'company';
            $columns['type'] = 'type';
            $columns['location'] = 'location';
            $columns['salary'] = 'salary';
            $columns['skills'] = 'skills';
            $columns['featured'] = 'featured';
            $columns['author'] = 'author';
            $columns['post_date'] = 'post_date';
            return $columns;
        }

        /**
         * @param $vars
         * @return array
         */
        public function column_orderby($vars)
        {
            if (!is_admin()) {
                return $vars;
            }

            global $pagenow;
            if ($pagenow != 'edit.php' || !isset($vars['post_type']) || $vars['post_type'] != 'jobs') {
                return $vars;
            }

            if (!isset($vars['orderby'])) {
                return $vars;
            }

            switch ($vars['orderby']) {
                case 'status':
                    $vars['orderby'] = 'post_status';
                    break;

                case 'company':
                    $vars = array_merge($vars, array(
                        'meta_key' => JOBPORTAL_METABOX_PREFIX . 'jobs_select_company',
                        'orderby' => 'meta_value_num',
                    ));
                    break;

                case 'type':
                case 'location':
                case 'skills':
                    // Taxonomy sorting - handled via posts_clauses filter
                    $vars['orderby'] = 'name';
                    break;

                case 'salary':
                    // Sort by minimum salary
                    $vars = array_merge($vars, array(
                        'meta_key' => JOBPORTAL_METABOX_PREFIX . 'jobs_salary_minimum',
                        'orderby' => 'meta_value_num',
                    ));
                    break;

                case 'featured':
                    $vars = array_merge($vars, array(
                        'meta_key' => JOBPORTAL_METABOX_PREFIX . 'jobs_featured',
                        'orderby' => 'meta_value_num',
                    ));
                    break;

                case 'author':
                    $vars['orderby'] = 'author';
                    break;

                case 'post_date':
                    $vars['orderby'] = 'date';
                    break;
            }

            return $vars;
        }

        /**
         * Handle taxonomy sorting via posts_clauses
         * @param array $clauses Query clauses
         * @return array Modified clauses
         */
        public function posts_clauses($clauses)
        {
            global $wpdb, $pagenow;

            if (!is_admin() || 'edit.php' !== $pagenow) {
                return $clauses;
            }

            if (!isset($_GET['post_type']) || 'jobs' !== $_GET['post_type']) {
                return $clauses;
            }

            $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : '';
            $order = isset($_GET['order']) && strtoupper($_GET['order']) === 'ASC' ? 'ASC' : 'DESC';

            // Handle taxonomy sorting (type, location, skills)
            if (in_array($orderby, array('type', 'location', 'skills'))) {
                $taxonomy_map = array(
                    'type' => 'jobs-type',
                    'location' => 'jobs-location',
                    'skills' => 'jobs-skills'
                );

                if (isset($taxonomy_map[$orderby])) {
                    $taxonomy = $taxonomy_map[$orderby];
                    $clauses['join'] .= "
                        LEFT OUTER JOIN {$wpdb->term_relationships} AS rel ON {$wpdb->posts}.ID = rel.object_id
                        LEFT OUTER JOIN {$wpdb->term_taxonomy} AS tax ON rel.term_taxonomy_id = tax.term_taxonomy_id
                        LEFT OUTER JOIN {$wpdb->terms} AS terms ON tax.term_id = terms.term_id
                    ";
                    $clauses['where'] .= " AND (tax.taxonomy = '{$taxonomy}' OR tax.taxonomy IS NULL)";
                    $clauses['groupby'] = "{$wpdb->posts}.ID";
                    $clauses['orderby'] = "terms.name {$order}, {$wpdb->posts}.post_title ASC";
                }
            }

            return $clauses;
        }

        /**
         * Modify jobs slug
         * @param $existing_slug
         * @return string
         */
        public function modify_jobs_slug($existing_slug)
        {
            $jobs_url_slug = jobportal_get_option('jobs_url_slug');
            $enable_slug_categories = jobportal_get_option('enable_slug_categories');
            if ($jobs_url_slug) {
                if ($enable_slug_categories == 1) {
                    return $jobs_url_slug . '/%jobs-categories%';
                } else {
                    return $jobs_url_slug;
                }
            }
            return $existing_slug;
        }

        public function modify_jobs_has_archive($existing_slug)
        {
            $jobs_url_slug = jobportal_get_option('jobs_url_slug');
            if ($jobs_url_slug) {
                return $jobs_url_slug;
            }
            return $existing_slug;
        }

        /**
         * Generic method to modify taxonomy slug
         * @param string $existing_slug
         * @param string $option_key
         * @return string
         */
        private function modify_taxonomy_slug($existing_slug, $option_key)
        {
            $url_slug = jobportal_get_option($option_key);
            return $url_slug ? $url_slug : $existing_slug;
        }

        /**
         * Modify jobs type slug
         * @param $existing_slug
         * @return string
         */
        public function modify_jobs_type_slug($existing_slug)
        {
            return $this->modify_taxonomy_slug($existing_slug, 'jobs_type_url_slug');
        }

        /**
         * Modify jobs tags slug
         * @param $existing_slug
         * @return string
         */
        public function modify_jobs_tags_slug($existing_slug)
        {
            return $this->modify_taxonomy_slug($existing_slug, 'jobs_tags_url_slug');
        }

        /**
         * Modify jobs categories slug
         * @param $existing_slug
         * @return string
         */
        public function modify_jobs_categories_slug($existing_slug)
        {
            return $this->modify_taxonomy_slug($existing_slug, 'jobs_categories_url_slug');
        }

        /**
         * Modify jobs skills slug
         * @param $existing_slug
         * @return string
         */
        public function modify_jobs_skills_slug($existing_slug)
        {
            return $this->modify_taxonomy_slug($existing_slug, 'jobs_skills_url_slug');
        }

        /**
         * Modify jobs location slug
         * @param $existing_slug
         * @return string
         */
        public function modify_jobs_location_slug($existing_slug)
        {
            return $this->modify_taxonomy_slug($existing_slug, 'jobs_location_url_slug');
        }

        /**
         * Modify jobs career slug
         * @param $existing_slug
         * @return string
         */
        public function modify_jobs_career_slug($existing_slug)
        {
            return $this->modify_taxonomy_slug($existing_slug, 'jobs_career_url_slug');
        }

        /**
         * Modify jobs experience slug
         * @param $existing_slug
         * @return string
         */
        public function modify_jobs_experience_slug($existing_slug)
        {
            return $this->modify_taxonomy_slug($existing_slug, 'jobs_experience_url_slug');
        }

        /**
         * Modify jobs qualification slug
         * @param $existing_slug
         * @return string
         */
        public function modify_jobs_qualification_slug($existing_slug)
        {
            return $this->modify_taxonomy_slug($existing_slug, 'jobs_qualification_url_slug');
        }

        /**
         * Modify jobs gender slug
         * @param $existing_slug
         * @return string
         */
        public function modify_jobs_gender_slug($existing_slug)
        {
            return $this->modify_taxonomy_slug($existing_slug, 'jobs_gender_url_slug');
        }


        /**
         * Approve_jobs
         */
        public function approve_jobs()
        {
            if (!empty($_GET['approve_listing']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'approve_listing') && current_user_can('publish_post', $_GET['approve_listing'])) {
                $post_id = absint(jobportal_clean(wp_unslash($_GET['approve_listing'])));
                $listing_data = array(
                    'ID' => $post_id,
                    'post_status' => 'publish'
                );
                wp_update_post($listing_data);

                $author_id = get_post_field('post_author', $post_id);
                $user = get_user_by('id', $author_id);
                $user_email = $user->user_email;

                $args = array(
                    'user_login' => $user->user_login,
                    'job_title' => get_the_title($post_id),
                    'job_url' => get_permalink($post_id)
                );
                jobportal_send_email($user_email, 'mail_approved_listing', $args);
                wp_redirect(remove_query_arg('approve_listing', add_query_arg('approve_listing', $post_id, admin_url('edit.php?post_type=jobs'))));
                exit;
            }
        }

        /**
         * Expire jobs
         */
        public function expire_jobs()
        {
            if (!empty($_GET['expire_listing']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'expire_listing') && current_user_can('publish_post', $_GET['expire_listing'])) {
                $post_id = absint(jobportal_clean(wp_unslash($_GET['expire_listing'])));

                $listing_data = array(
                    'ID' => $post_id,
                    'post_status' => 'expired'
                );
                wp_update_post($listing_data);

                $author_id = get_post_field('post_author', $post_id);
                $user = get_user_by('id', $author_id);
                $user_email = $user->user_email;

                $args = array(
                    'job_title' => get_the_title($post_id),
                    'job_url' => get_permalink($post_id)
                );
                jobportal_send_email($user_email, 'mail_expired_listing', $args);

                wp_redirect(remove_query_arg('expire_listing', add_query_arg('expire_listing', $post_id, admin_url('edit.php?post_type=jobs'))));
                exit;
            }
        }

        /**
         * Hidden jobs
         */
        public function hidden_jobs()
        {
            if (!empty($_GET['hidden_listing']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'hidden_listing') && current_user_can('publish_post', $_GET['hidden_listing'])) {
                $post_id = absint(jobportal_clean(wp_unslash($_GET['hidden_listing'])));
                $listing_data = array(
                    'ID' => $post_id,
                    'post_status' => 'hidden'
                );
                wp_update_post($listing_data);
                wp_redirect(remove_query_arg('hidden_listing', add_query_arg('hidden_listing', $post_id, admin_url('edit.php?post_type=jobs'))));
                exit;
            }
        }

        /**
         * Show jobs
         */
        public function show_jobs()
        {
            if (!empty($_GET['show_listing']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'show_listing') && current_user_can('publish_post', $_GET['show_listing'])) {
                $post_id = absint(jobportal_clean(wp_unslash($_GET['show_listing'])));
                $listing_data = array(
                    'ID' => $post_id,
                    'post_status' => 'publish'
                );
                wp_update_post($listing_data);
                wp_redirect(remove_query_arg('show_listing', add_query_arg('show_listing', $post_id, admin_url('edit.php?post_type=jobs'))));
                exit;
            }
        }

        /**
         * AJAX handler for approve jobs
         */
        public function ajax_approve_jobs()
        {
            $this->handle_ajax_status_change('approve', 'publish', 'mail_approved_listing', esc_html__('Job approved successfully.', 'jobportal-framework'), esc_html__('Failed to approve job.', 'jobportal-framework'));
        }

        /**
         * AJAX handler for expire jobs
         */
        public function ajax_expire_jobs()
        {
            $this->handle_ajax_status_change('expire', 'expired', 'mail_expired_listing', esc_html__('Job expired successfully.', 'jobportal-framework'), esc_html__('Failed to expire job.', 'jobportal-framework'));
        }

        /**
         * AJAX handler for hide jobs
         */
        public function ajax_hide_jobs()
        {
            $this->handle_ajax_status_change('hide', 'hidden', '', esc_html__('Job hidden successfully.', 'jobportal-framework'), esc_html__('Failed to hide job.', 'jobportal-framework'));
        }

        /**
         * AJAX handler for show jobs
         */
        public function ajax_show_jobs()
        {
            $this->handle_ajax_status_change('show', 'publish', '', esc_html__('Job shown successfully.', 'jobportal-framework'), esc_html__('Failed to show job.', 'jobportal-framework'));
        }

        /**
         * Handle AJAX status change
         * @param string $action Action name (approve, expire, hide, show)
         * @param string $new_status New post status
         * @param string $email_template Email template to send
         * @param string $success_message Success message
         * @param string $error_message Error message
         */
        private function handle_ajax_status_change($action, $new_status, $email_template, $success_message, $error_message)
        {
            if (!isset($_POST['post_id']) || !isset($_POST['nonce'])) {
                wp_send_json_error(array('message' => esc_html__('Missing required parameters.', 'jobportal-framework')));
            }

            $post_id = absint(jobportal_clean(wp_unslash($_POST['post_id'])));
            check_ajax_referer('jobportal_' . $action . '_job_' . $post_id, 'nonce');

            if (!current_user_can('publish_post', $post_id)) {
                wp_send_json_error(array('message' => esc_html__('You do not have permission to perform this action.', 'jobportal-framework')));
            }

            $result = $this->change_job_status($post_id, $new_status, $email_template);

            if (is_wp_error($result)) {
                wp_send_json_error(array('message' => $error_message));
            }

            wp_send_json_success(array(
                'message' => $success_message,
                'new_status' => $new_status
            ));
        }

        /**
         * Enqueue admin scripts for jobs filters
         */
        public function enqueue_admin_scripts($hook)
        {
            global $typenow;
            if ($hook != 'edit.php' || $typenow != 'jobs') {
                return;
            }

            // Enqueue Select2
            wp_enqueue_script('select2', JOBPORTAL_PLUGIN_URL . 'assets/libs/select2/js/select2.min.js', array('jquery'), '4.0.13', true);
            wp_enqueue_style('select2', JOBPORTAL_PLUGIN_URL . 'assets/libs/select2/css/select2.min.css', array(), '4.0.13');

            // Add custom CSS for filter spacing
            $custom_css = '
                .jobportal-filter-wrapper {
                    display: inline-block;
                    margin-right: 10px;
                    margin-bottom: 10px;
                    vertical-align: top;
                }
                .jobportal-filter-wrapper .select2-container {
                    margin-right: 0;
                }
            ';
            wp_add_inline_style('select2', $custom_css);

            // Enqueue custom script for filter initialization
            $select2_config = array(
                'default_width' => self::SELECT2_DEFAULT_WIDTH,
                'company_width' => self::SELECT2_COMPANY_WIDTH,
                'min_options' => self::SELECT2_MIN_OPTIONS,
                'search_companies' => esc_js(__('Search companies...', 'jobportal-framework')),
                'search_authors' => esc_js(__('Search authors...', 'jobportal-framework')),
            );

            wp_add_inline_script('select2', $this->get_select2_init_script($select2_config));
        }

        /**
         * Render filter dropdown
         * @param string $name
         * @param string $id
         * @param array $options
         * @param mixed $selected
         * @param string $placeholder
         */
        private function render_filter_dropdown($name, $id, $options, $selected, $placeholder)
        {
            echo '<span class="jobportal-filter-wrapper" style="display: inline-block; margin-right: 10px; margin-bottom: 10px;">';
            echo '<select name="' . esc_attr($name) . '" id="' . esc_attr($id) . '" class="jobportal-select2" data-placeholder="' . esc_attr($placeholder) . '">';
            echo '<option value="">' . esc_html($placeholder) . '</option>';

            foreach ($options as $value => $label) {
                $is_selected = selected($selected, $value, false);
                echo '<option value="' . esc_attr($value) . '"' . $is_selected . '>' . esc_html($label) . '</option>';
            }

            echo '</select>';
            echo '</span>';
        }

        /**
         * Filter restrict manage jobs
         */
        public function filter_restrict_manage_jobs()
        {
            global $typenow;

            if ($typenow !== self::POST_TYPE) {
                return;
            }

            // Filter by Status
            $selected_status = isset($_GET['jobs_status']) ? jobportal_clean(wp_unslash($_GET['jobs_status'])) : '';
            $statuses = array(
                'publish' => esc_html__('Published', 'jobportal-framework'),
                'pending' => esc_html__('Pending', 'jobportal-framework'),
                'draft' => esc_html__('Draft', 'jobportal-framework'),
                'expired' => esc_html__('Expired', 'jobportal-framework'),
                'hidden' => esc_html__('Hidden', 'jobportal-framework'),
            );
            $this->render_filter_dropdown('jobs_status', 'jobs_status', $statuses, $selected_status, esc_html__('All Statuses', 'jobportal-framework'));

            // Filter by Company
            $selected_company = isset($_GET['jobs_company']) ? absint($_GET['jobs_company']) : 0;
            $companies = get_posts(array(
                'post_type' => 'company',
                'posts_per_page' => -1,
                'orderby' => 'title',
                'order' => 'ASC',
            ));

            if (!empty($companies)) {
                $company_options = array();
                foreach ($companies as $company) {
                    $company_options[$company->ID] = $company->post_title;
                }
                $this->render_filter_dropdown('jobs_company', 'jobs_company', $company_options, $selected_company, esc_html__('All Companies', 'jobportal-framework'));
            }

            // Filter by Taxonomy (Type, Location, Skills)
            $taxonomy_arr = array('jobs-type', 'jobs-location', 'jobs-skills');
            foreach ($taxonomy_arr as $taxonomy) {
                $selected = isset($_GET[$taxonomy]) ? jobportal_clean(wp_unslash($_GET[$taxonomy])) : '';
                $info_taxonomy = get_taxonomy($taxonomy);
                $taxonomy_label = $info_taxonomy ? $info_taxonomy->label : ucfirst(str_replace('jobs-', '', $taxonomy));
                echo '<span class="jobportal-filter-wrapper" style="display: inline-block; margin-right: 10px; margin-bottom: 10px;">';
                echo '<select name="' . esc_attr($taxonomy) . '" id="' . esc_attr($taxonomy) . '" class="jobportal-select2" data-placeholder="' . esc_attr(sprintf(__('All %s', 'jobportal-framework'), $taxonomy_label)) . '">';
                echo '<option value="">' . esc_html(sprintf(__('All %s', 'jobportal-framework'), $taxonomy_label)) . '</option>';
                $terms = get_terms(array(
                    'taxonomy' => $taxonomy,
                    'hide_empty' => false,
                    'orderby' => 'name',
                    'order' => 'ASC',
                ));
                if (!is_wp_error($terms) && !empty($terms)) {
                    foreach ($terms as $term) {
                        $term_selected = ($selected == $term->term_id) ? 'selected' : '';
                        echo '<option value="' . esc_attr($term->term_id) . '" ' . $term_selected . '>' . esc_html($term->name) . '</option>';
                    }
                }
                echo '</select>';
                echo '</span>';
            }

            // Filter by Featured
            $selected_featured = isset($_GET['jobs_featured']) ? jobportal_clean(wp_unslash($_GET['jobs_featured'])) : '';
            $featured_options = array(
                '1' => esc_html__('Featured', 'jobportal-framework'),
                '0' => esc_html__('Not Featured', 'jobportal-framework'),
            );
            $this->render_filter_dropdown('jobs_featured', 'jobs_featured', $featured_options, $selected_featured, esc_html__('All Featured', 'jobportal-framework'));

            // Filter by Author
            $selected_author = isset($_GET['jobs_author']) ? absint($_GET['jobs_author']) : 0;
            $authors = get_users(array(
                'capability' => 'edit_posts',
                'orderby' => 'display_name',
                'order' => 'ASC',
            ));

            $author_options = array();
            foreach ($authors as $author) {
                $author_options[$author->ID] = $author->display_name;
            }
            $this->render_filter_dropdown('jobs_author', 'jobs_author', $author_options, $selected_author, esc_html__('All Authors', 'jobportal-framework'));
        }

        /**
         * Jobs filter
         * @param WP_Query $query
         */
        public function jobs_filter($query)
        {
            global $pagenow;

            if ($pagenow !== 'edit.php') {
                return;
            }

            $q_vars = &$query->query_vars;

            if (!isset($q_vars['post_type']) || $q_vars['post_type'] !== self::POST_TYPE) {
                return;
            }

            // Filter by Status
            if (isset($_GET['jobs_status']) && !empty($_GET['jobs_status'])) {
                $q_vars['post_status'] = jobportal_clean(wp_unslash($_GET['jobs_status']));
            }

            // Filter by Company
            if (isset($_GET['jobs_company']) && !empty($_GET['jobs_company'])) {
                $company_id = absint($_GET['jobs_company']);
                $q_vars['meta_query'][] = array(
                    'key' => JOBPORTAL_METABOX_PREFIX . 'jobs_select_company',
                    'value' => $company_id,
                    'compare' => '='
                );
            }

            // Filter by Taxonomy (Type, Location, Skills)
            $taxonomy_arr = array('jobs-skills', 'jobs-type', 'jobs-location');
            foreach ($taxonomy_arr as $taxonomy) {
                if (isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0) {
                    $term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
                    if ($term) {
                        $q_vars[$taxonomy] = $term->slug;
                    }
                }
            }

            // Filter by Featured
            if (isset($_GET['jobs_featured']) && $_GET['jobs_featured'] !== '') {
                $featured_value = absint($_GET['jobs_featured']);
                $q_vars['meta_query'][] = array(
                    'key' => JOBPORTAL_METABOX_PREFIX . 'jobs_featured',
                    'value' => $featured_value,
                    'compare' => '='
                );
            }

            // Filter by Author
            if (isset($_GET['jobs_author']) && !empty($_GET['jobs_author'])) {
                $q_vars['author'] = absint($_GET['jobs_author']);
            }
        }

        public function add_badge_menu()
        {
            global $menu;
            $jobs_count = wp_count_posts('jobs')->pending;
            if ($jobs_count && is_array($menu)) {
                foreach ($menu as $key => $value) {
                    if ($menu[$key][2] == 'edit.php?post_type=jobs') {
                        $menu[$key][0] .= ' <span class="update-plugins">' . $jobs_count . '</span>';
                        return;
                    }
                }
            }
        }

        public function auto_description_generate()
        {
            // Verify nonce for security
            check_ajax_referer('jobportal_ai_generate_nonce', 'security');

            if (!isset($_POST['keywords'])) {
                echo json_encode(array('success' => false, 'message' => esc_html__('Missing required parameters.', 'jobportal-framework')));
                wp_die();
            }

            // Get and sanitize input parameters
            $keywords = isset($_POST['keywords']) ? sanitize_text_field(trim($_POST['keywords'])) : '';
            $tone = isset($_POST['tone']) ? sanitize_text_field($_POST['tone']) : '';
            $language = isset($_POST['language']) ? sanitize_text_field($_POST['language']) : '';

            // Validate keywords
            if (empty($keywords)) {
                echo json_encode(array('success' => false, 'message' => esc_html__('Please enter a description.', 'jobportal-framework')));
                wp_die();
            }

            if (strlen($keywords) < 20) {
                echo json_encode(array('success' => false, 'message' => esc_html__('The description should be at least 20 characters.', 'jobportal-framework')));
                wp_die();
            }

            if (strlen($keywords) > 2000) {
                echo json_encode(array('success' => false, 'message' => esc_html__('Description too long. Please keep under 2000 characters.', 'jobportal-framework')));
                wp_die();
            }

            // Get AI configuration
            $ai_model = jobportal_get_option('ai_model', 'gpt-3.5-turbo');
            $ai_temperature = jobportal_get_option('ai_temperature', '0.7');
            $ai_max_tokens = jobportal_get_option('ai_max_tokens', '2048');
            $ai_key = jobportal_get_option('ai_key', '');

            // Validate API key
            if (empty($ai_key)) {
                echo json_encode(array('success' => false, 'message' => esc_html__('OpenAI API Key is not configured. Please configure it in admin settings.', 'jobportal-framework')));
                wp_die();
            }

            // Validate model
            if (empty($ai_model)) {
                $ai_model = 'gpt-3.5-turbo';
            }

            // Validate and sanitize temperature
            $temperature = floatval($ai_temperature);
            if ($temperature < 0 || $temperature > 2) {
                $temperature = 0.7; // Default to 0.7 if invalid
            }

            // Validate and sanitize max_tokens
            $max_tokens = intval($ai_max_tokens);
            if ($max_tokens < 100 || $max_tokens > 4096) {
                $max_tokens = 2048; // Default to 2048 if invalid (range: 100-4096)
            }

            // Build prompt with tone and language
            $prompt = $keywords;
            if (!empty($tone)) {
                $prompt .= ' Writing style and tone: ' . $tone . '.';
            }

            if (!empty($language)) {
                $prompt .= ' Write in: ' . $language . '.';
            }

            // Prepare API request
            $payload = array(
                'messages' => array(
                    array('role' => 'system', 'content' => 'You can start the conversation.'),
                    array('role' => 'user', 'content' => $prompt)
                )
            );

            $endpoint = 'https://api.openai.com/v1/chat/completions';
            $body = array(
                'temperature' => $temperature,
                'max_tokens' => $max_tokens,
                'model' => $ai_model,
                'messages' => $payload['messages']
            );

            $args = array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $ai_key
                ),
                'body' => json_encode($body),
                'timeout' => 300,
            );

            // Make API request
            $response = wp_remote_post($endpoint, $args);

            // Handle WordPress HTTP errors
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                echo json_encode(array(
                    'success' => false,
                    'message' => esc_html__('Connection error: ', 'jobportal-framework') . esc_html($error_message)
                ));
                wp_die();
            }

            // Get response body
            $response_code = wp_remote_retrieve_response_code($response);
            $response_body_raw = wp_remote_retrieve_body($response);

            // Check for empty response
            if (empty($response_body_raw)) {
                echo json_encode(array(
                    'success' => false,
                    'message' => esc_html__('Empty response from API. Please try again.', 'jobportal-framework')
                ));
                wp_die();
            }

            // Decode JSON response
            $response_body = json_decode($response_body_raw);

            // Check for JSON decode errors
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo json_encode(array(
                    'success' => false,
                    'message' => esc_html__('Invalid response format from API.', 'jobportal-framework')
                ));
                wp_die();
            }

            // Handle API errors
            if (isset($response_body->error)) {
                $error_code = isset($response_body->error->code) ? $response_body->error->code : '';
                $error_message = isset($response_body->error->message) ? $response_body->error->message : esc_html__('Unknown API error', 'jobportal-framework');

                if ($error_code === 'invalid_api_key') {
                    echo json_encode(array(
                        'success' => false,
                        'message' => esc_html__('Invalid API Key. Please check your OpenAI API key in admin settings.', 'jobportal-framework')
                    ));
                } else {
                    echo json_encode(array(
                        'success' => false,
                        'message' => esc_html($error_message)
                    ));
                }
                wp_die();
            }

            // Validate successful response structure
            if (!isset($response_body->choices) || !is_array($response_body->choices) || empty($response_body->choices)) {
                echo json_encode(array(
                    'success' => false,
                    'message' => esc_html__('Invalid response structure from API.', 'jobportal-framework')
                ));
                wp_die();
            }

            if (!isset($response_body->choices[0]->message->content) || empty($response_body->choices[0]->message->content)) {
                echo json_encode(array(
                    'success' => false,
                    'message' => esc_html__('Empty content in API response.', 'jobportal-framework')
                ));
                wp_die();
            }

            // Success - return generated content
            $generated_content = $response_body->choices[0]->message->content;
            echo json_encode(array(
                'success' => true,
                'message' => wpautop(wp_kses_post($generated_content))
            ));

            wp_die();
        }

        /**
         * Get Select2 initialization script
         * @param array $config
         * @return string
         */
        private function get_select2_init_script($config)
        {
            return sprintf('
                jQuery(document).ready(function($) {
                    var defaultConfig = {
                        width: "%s",
                        allowClear: true,
                        placeholder: function() {
                            return $(this).data("placeholder") || "";
                        },
                        minimumResultsForSearch: 0
                    };

                    // Initialize Select2 for all filter dropdowns
                    $("#jobs_status, #jobs_company, #jobs_featured, #jobs_author, select[name^=\'jobs-\']").select2(defaultConfig);

                    // For company dropdown, enable search if there are many options
                    var $companySelect = $("#jobs_company");
                    if ($companySelect.find("option").length > %d) {
                        $companySelect.select2("destroy").select2({
                            width: "%s",
                            allowClear: true,
                            placeholder: "%s",
                            minimumResultsForSearch: 0
                        });
                    }

                    // For taxonomy dropdowns, enable search
                    $("select[name^=\'jobs-\']").each(function() {
                        var $select = $(this);
                        if ($select.find("option").length > %d) {
                            $select.select2("destroy").select2({
                                width: "%s",
                                allowClear: true,
                                placeholder: $select.data("placeholder") || "",
                                minimumResultsForSearch: 0
                            });
                        }
                    });

                    // For author dropdown, enable search
                    var $authorSelect = $("#jobs_author");
                    if ($authorSelect.find("option").length > %d) {
                        $authorSelect.select2("destroy").select2({
                            width: "%s",
                            allowClear: true,
                            placeholder: "%s",
                            minimumResultsForSearch: 0
                        });
                    }
                });
            ',
                $config['default_width'],
                $config['min_options'],
                $config['company_width'],
                $config['search_companies'],
                $config['min_options'],
                $config['default_width'],
                $config['min_options'],
                $config['default_width'],
                $config['search_authors']
            );
        }
    }
}
