<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if (!class_exists('JobPortal_Admin_Company')) {
    /**
     * Class JobPortal_Admin_Company
     */
    class JobPortal_Admin_Company
    {
        /**
         * Register custom columns
         * @param $columns
         * @return array
         */
        public function register_custom_column_titles($columns)
        {
            unset($columns['tags']);

            $columns['thumb']    = esc_html__('Logo', 'jobportal-framework');
            $columns['title']    = esc_html__('Title', 'jobportal-framework');
            $columns['location'] = esc_html__('Location', 'jobportal-framework');
            $columns['size']     = esc_html__('Size', 'jobportal-framework');
            $columns['author']   = esc_html__('Author', 'jobportal-framework');

            if (class_exists('WPSEO_Frontend')) {
                $custom_order = [
                    'cb',
                    'thumb',
                    'title',
                    'location',
                    'size',
                    'author',
                    'date',
                    'wpseo-score',
                    'wpseo-score-readability',
                    'wpseo-title',
                    'wpseo-metadesc',
                    'wpseo-focuskw',
                    'wpseo-links',
                    'wpseo-linked'
                ];
            } else {
                $custom_order = ['cb', 'thumb', 'title', 'location', 'size', 'author', 'date'];
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
         * Display custom column for company
         * @param $column
         */
        public function display_custom_column($column)
        {
            global $post;
            switch ($column) {
                case 'thumb':
                    $company_logo = get_post_meta($post->ID, JOBPORTAL_METABOX_PREFIX . 'company_logo');
                    $company_logo_url = '';
                    if (!empty($company_logo) && is_array($company_logo) && isset($company_logo[0]) && is_array($company_logo[0]) && isset($company_logo[0]['url'])) {
                        $company_logo_url = $company_logo[0]['url'];
                    }
                    if (!empty($company_logo_url)) {
                        echo '<img src = " ' . esc_url($company_logo_url) . '" alt=""/>';
                    } else {
                        echo '&ndash;';
                    }
                    break;
                case 'location':
                    echo jobportal_admin_taxonomy_terms($post->ID, 'company-location', 'company');
                    break;
                case 'size':
                    echo jobportal_admin_taxonomy_terms($post->ID, 'company-size', 'company');
                    break;
                case 'author':
                    echo '<a href="' . esc_url(add_query_arg('author', $post->post_author)) . '">' . get_the_author() . '</a>';
                    break;
            }
        }

        /**
         * sortable_columns
         * @param $columns
         * @return mixed
         */
        public function sortable_columns($columns)
        {
            $columns['location'] = 'location';
            $columns['size'] = 'size';
            $columns['author'] = 'author';
            $columns['post_date'] = 'post_date';
            return $columns;
        }

        /**
         * Modify company slug
         * @param $existing_slug
         * @return string
         */
        public function modify_company_url_slug($existing_slug)
        {
            $company_url_slug = jobportal_get_option('company_url_slug');
            $enable_slug_categories = jobportal_get_option('enable_slug_categories');
            if ($company_url_slug) {
                if ($enable_slug_categories == 1) {
                    return $company_url_slug . '/%company-categories%';
                } else {
                    return $company_url_slug;
                }
            }
            return $existing_slug;
        }

        public function modify_company_has_archive($existing_slug)
        {
            $company_url_slug = jobportal_get_option('company_url_slug');
            if ($company_url_slug) {
                return $company_url_slug;
            }
            return $existing_slug;
        }

        /**
         * @param $actions
         * @param $post
         * @return mixed
         */
        public function modify_list_row_actions($actions, $post)
        {
            // Check for your post type.
            if ($post->post_type == 'company') {
                if (in_array($post->post_status, array('pending'))) {
                    $actions['company-approve'] = '<a href="' . wp_nonce_url(add_query_arg('approve_company', $post->ID), 'approve_company') . '">' . esc_html__('Approve', 'jobportal-framework') . '</a>';
                }
            }
            return $actions;
        }

        /** Approve company */
        public function approve_company()
        {
            if (!empty($_GET['approve_company']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'approve_company') && current_user_can('publish_post', $_GET['approve_company'])) {
                $post_id = absint(jobportal_clean(wp_unslash($_GET['approve_company'])));
                $listing_data = array(
                    'ID' => $post_id,
                    'post_status' => 'publish'
                );
                wp_update_post($listing_data);

                $author_id = get_post_field('post_author', $post_id);
                $user = get_user_by('id', $author_id);
                $user_email = $user->user_email;

                $args = array(
                    'your_name' => $user->user_login,
                    'listing_title' => get_the_title($post_id),
                    'listing_url' => get_permalink($post_id)
                );
                jobportal_send_email($user_email, 'mail_approved_user_status_company', $args);
                do_action('jobportal_approve_company', $author_id, $post_id);
                wp_redirect(remove_query_arg('approve_company', add_query_arg('approve_company', $post_id, admin_url('edit.php?post_type=company'))));
                exit;
            }
        }

        /**
         * Modify company slug
         * @param $existing_slug
         * @return string
         */
        public function modify_company_categories_url_slug($existing_slug)
        {
            $company_categories_url_slug = jobportal_get_option('company_categories_url_slug');
            if ($company_categories_url_slug) {
                return $company_categories_url_slug;
            }
            return $existing_slug;
        }

        /**
         * Modify location slug
         * @param $existing_slug
         * @return string
         */
        public function modify_company_location_url_slug($existing_slug)
        {
            $company_location_url_slug = jobportal_get_option('company_location_url_slug');
            if ($company_location_url_slug) {
                return $company_location_url_slug;
            }
            return $existing_slug;
        }

        /**
         * Modify location slug
         * @param $existing_slug
         * @return string
         */
        public function modify_company_size_url_slug($existing_slug)
        {
            $company_size_url_slug = jobportal_get_option('company_size_url_slug');
            if ($company_size_url_slug) {
                return $company_size_url_slug;
            }
            return $existing_slug;
        }


        /**
         * filter_restrict_manage_company
         */
        public function filter_restrict_manage_company()
        {
            global $typenow;
            $post_type = 'company';
            if ($typenow == $post_type) {
                $taxonomy_arr  = array('company-location', 'company-size');
                foreach ($taxonomy_arr as $taxonomy) {
                    $selected      = isset($_GET[$taxonomy]) ? jobportal_clean(wp_unslash($_GET[$taxonomy])) : '';
                    $info_taxonomy = get_taxonomy($taxonomy);
                    wp_dropdown_categories(array(
                        'show_option_all' => __("All {$info_taxonomy->label}"),
                        'taxonomy'        => $taxonomy,
                        'name'            => $taxonomy,
                        'orderby'         => 'name',
                        'selected'        => $selected,
                        'hide_empty'      => false,
                    ));
                }
?>
                <?php
            };
        }

        /**
         * h_filter
         * @param $query
         */
        public function company_filter($query)
        {
            global $pagenow;
            $post_type = 'company';
            $q_vars    = &$query->query_vars;
            if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type) {
                $taxonomy_arr  = array('company-location', 'company-size');
                foreach ($taxonomy_arr as $taxonomy) {
                    if (isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0) {
                        $term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
                        $q_vars[$taxonomy] = $term->slug;
                    }
                }
            }
        }

        public function add_badge_menu()
        {
            global $menu;
            $company_count = wp_count_posts('company')->pending;
            if ($company_count && is_array($menu)) {
                foreach ($menu as $key => $value) {
                    if ($menu[$key][2] == 'edit.php?post_type=company') {
                        $menu[$key][0] .= ' <span class="update-plugins">' . $company_count . '</span>';
                        return;
                    }
                }
            }
        }
    }
}
