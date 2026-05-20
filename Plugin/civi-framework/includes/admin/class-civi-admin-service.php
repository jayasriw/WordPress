<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if (!class_exists('Civi_Admin_Service')) {
    /**
     * Class Civi_Admin_Service
     */
    class Civi_Admin_Service
    {
        /**
         * Register custom columns
         * @param $columns
         * @return array
         */
        public function register_custom_column_titles($columns)
        {
            unset($columns['tags']);

            $columns['thumb']  = esc_html__('Thumbnail', 'civi-framework');
            $columns['title']  = esc_html__('Title', 'civi-framework');
            $columns['skills'] = esc_html__('Skills', 'civi-framework');
            $columns['author'] = esc_html__('Author', 'civi-framework');

            $custom_order = ['cb', 'thumb', 'title', 'skills', 'author', 'date'];
            $new_columns  = [];

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
         * Display custom column for service
         * @param $column
         */
        public function display_custom_column($column)
        {
            global $post;
            switch ($column) {
                case 'thumb':
                    if (has_post_thumbnail()) {
                        the_post_thumbnail('thumbnail', array(
                            'class' => 'attachment-thumbnail attachment-thumbnail-small',
                        ));
                    } else {
                        echo '&ndash;';
                    }
                    break;
                case 'skills':
                    echo civi_admin_taxonomy_terms($post->ID, 'service-skills', 'service');
                    break;
                case 'author':
                    echo '<a href="' . esc_url(add_query_arg('author', $post->post_author)) . '">' . get_the_author() . '</a>';
                    break;
            }
        }

        /**
         * @param $actions
         * @param $post
         * @return mixed
         */
        public function modify_list_row_actions($actions, $post)
        {
            // Check for your post type.
            if ($post->post_type == 'service') {
                if (in_array($post->post_status, array('pending'))) {
                    $actions['service-approve'] = '<a href="' . wp_nonce_url(add_query_arg('approve_service', $post->ID), 'approve_service') . '">' . esc_html__('Approve', 'civi-framework') . '</a>';
                }
            }
            return $actions;
        }

        /**
         * Approve service
         */
        public function approve_service()
        {
            if (!empty($_GET['approve_service']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'approve_service') && current_user_can('publish_post', $_GET['approve_service'])) {
                $post_id = absint(civi_clean(wp_unslash($_GET['approve_service'])));
                $listing_data = array(
                    'ID' => $post_id,
                    'post_status' => 'publish'
                );
                wp_update_post($listing_data);
                update_post_meta($post_id, CIVI_METABOX_PREFIX . 'enable_service_package_expires', 0);
                update_post_meta($post_id, CIVI_METABOX_PREFIX . 'service_featured', 0);
                wp_redirect(remove_query_arg('approve_service', add_query_arg('approve_service', $post_id, admin_url('edit.php?post_type=service'))));
                exit;
            }
        }

        /**
         * sortable_columns
         * @param $columns
         * @return mixed
         */
        public function sortable_columns($columns)
        {
            $columns['skills'] = 'skills';
            $columns['author'] = 'author';
            $columns['post_date'] = 'post_date';
            return $columns;
        }

        /**
         * Modify service slug
         * @param $existing_slug
         * @return string
         */
        public function modify_service_categories_url_slug($existing_slug)
        {
            $service_categories_url_slug = civi_get_option('service_categories_url_slug');
            if ($service_categories_url_slug) {
                return $service_categories_url_slug;
            }
            return $existing_slug;
        }

        /**
         * Modify skills slug
         * @param $existing_slug
         * @return string
         */
        public function modify_service_skills_url_slug($existing_slug)
        {
            $service_skills_url_slug = civi_get_option('service_skills_url_slug');
            if ($service_skills_url_slug) {
                return $service_skills_url_slug;
            }
            return $existing_slug;
        }

        /**
         * Modify location slug
         * @param $existing_slug
         * @return string
         */
        public function modify_service_location_url_slug($existing_slug)
        {
            $service_location_url_slug = civi_get_option('service_location_url_slug');
            if ($service_location_url_slug) {
                return $service_location_url_slug;
            }
            return $existing_slug;
        }

        /**
         * Modify language slug
         * @param $existing_slug
         * @return string
         */
        public function modify_service_language_url_slug($existing_slug)
        {
            $service_language_url_slug = civi_get_option('service_language_url_slug');
            if ($service_language_url_slug) {
                return $service_language_url_slug;
            }
            return $existing_slug;
        }


        /**
         * Modify service slug
         * @param $existing_slug
         * @return string
         */
        public function modify_service_slug($existing_slug)
        {
            $service_url_slug = civi_get_option('service_url_slug');
            $enable_slug_categories = civi_get_option('enable_slug_categories');
            if ($service_url_slug) {
                if ($enable_slug_categories == 1) {
                    return $service_url_slug . '/%service-categories%';
                } else {
                    return $service_url_slug;
                }
            }
            return $existing_slug;
        }

        public function modify_service_has_archive($existing_slug)
        {
            $service_url_slug = civi_get_option('service_url_slug');
            if ($service_url_slug) {
                return $service_url_slug;
            }
            return $existing_slug;
        }

        /**
         * Modify service tags slug
         * @param $existing_slug
         * @return string
         */
        public function modify_service_tags_slug($existing_slug)
        {
            $service_tags_url_slug = civi_get_option('service_tags_url_slug');
            if ($service_tags_url_slug) {
                return $service_tags_url_slug;
            }
            return $existing_slug;
        }


        /**
         * filter_restrict_manage_service
         */
        public function filter_restrict_manage_service()
        {
            global $typenow;
            $post_type = 'service';
            if ($typenow == $post_type) {
                $taxonomy_arr  = array('service-categories', 'service-skills');
                foreach ($taxonomy_arr as $taxonomy) {
                    $selected      = isset($_GET[$taxonomy]) ? civi_clean(wp_unslash($_GET[$taxonomy])) : '';
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
        public function service_filter($query)
        {
            global $pagenow;
            $post_type = 'service';
            $q_vars    = &$query->query_vars;
            if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type) {
                $taxonomy_arr  = array('service-categories', 'service-skills');
                foreach ($taxonomy_arr as $taxonomy) {
                    if (isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0) {
                        $term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
                        $q_vars[$taxonomy] = $term->slug;
                    }
                }
            }
        }
    }
}
