<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if (!class_exists('JobPortal_Admin_Applicants')) {
    /**
     * Class JobPortal_Admin_Applicants
     */
    class JobPortal_Admin_Applicants
    {

        /**
         * Register custom columns
         * @param $columns
         * @return array
         */
        public function register_custom_column_titles($columns)
        {
            $columns['thumb'] = esc_html__('Avatar', 'jobportal-framework');
            $columns['cb'] = '<input type="checkbox" />';
            $columns['title'] = esc_html__('Jobs Title', 'jobportal-framework');
            $columns['applicants_status'] = esc_html__('Status', 'jobportal-framework');
            $columns['post_author'] = esc_html__('Name Apply', 'jobportal-framework');
            $columns['applicants_type'] = esc_html__('Type Apply', 'jobportal-framework');
            $columns['date'] = esc_html__('Date', 'jobportal-framework');

            $custom_order = ['cb', 'thumb', 'title', 'applicants_status', 'post_author', 'applicants_type', 'date'];
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
         * sortable_columns
         * @param $columns
         * @return mixed
         */
        public function sortable_columns($columns)
        {
            $columns['title'] = 'title';
            $columns['post_author'] = 'post_author';
            $columns['applicants_status'] = 'applicants_status';
            $columns['post_author'] = 'post_author';
            $columns['applicants_type'] = 'applicants_type';
            $columns['date'] = 'date';
            return $columns;
        }

        /**
         * @param $vars
         * @return array
         */
        public function column_orderby($vars)
        {
            if (!is_admin())
                return $vars;

            if (isset($vars['orderby']) && 'applicants_status' == $vars['orderby']) {
                $vars = array_merge($vars, array(
                    'meta_key' => JOBPORTAL_METABOX_PREFIX . 'applicants_status',
                    'orderby' => 'meta_value_num',
                ));
            }
            return $vars;
        }
        /**
         * Display custom column for applicants
         * @param $column
         */
        public function display_custom_column($column)
        {
            global $post;
            $post_id = $post->ID;
            switch ($column) {
                case 'thumb':
                    $author_id = get_post_field('post_author', $post_id);
                    $candidate_avatar = get_the_author_meta('author_avatar_image_url', $author_id);
                    if (!empty($candidate_avatar)) {
                        echo '<img src = " ' . $candidate_avatar . '" alt=""/>';
                    } else {
                        echo '&ndash;';
                    }
                    break;
                case 'post_author':
                    $applicants_author = get_post_meta($post_id, JOBPORTAL_METABOX_PREFIX . 'applicants_author', true);
                    echo $applicants_author;
                    break;
                case 'applicants_type':
                    $applicants_type = get_post_meta($post_id, JOBPORTAL_METABOX_PREFIX . 'applicants_type', true);
                    if ($applicants_type == 'internal') {
                        $applicants_types = 'Internal Apply';
                    } else {
                        $applicants_types = 'Email Apply';
                    }
                    echo $applicants_types;
                    break;
                case 'applicants_status':
                    $applicants_status = get_post_meta($post->ID, JOBPORTAL_METABOX_PREFIX . 'applicants_status', true);
                    if ($applicants_status == 'rejected') {
                        echo '<span class="label jobportal-label-gray">' . esc_html__('Rejected', 'jobportal-framework') . '</span>';
                    } elseif ($applicants_status == 'approved') {
                        echo '<span class="label jobportal-label-blue">' . esc_html__('Approved', 'jobportal-framework') . '</span>';
                    } else {
                        echo '<span class="label jobportal-label-yellow">' . esc_html__('Pending', 'jobportal-framework') . '</span>';
                    }
                    break;
            }
        }

        /**
         * Modify applicants slug
         * @param $existing_slug
         * @return string
         */
        public function modify_applicants_slug($existing_slug)
        {
            $applicants_url_slug = jobportal_get_option('applicants_url_slug');
            if ($applicants_url_slug) {
                return $applicants_url_slug;
            }
            return $existing_slug;
        }
        /**
         * filter_restrict_manage_applicants
         */
        public function filter_restrict_manage_applicants()
        {
            global $typenow;
            $post_type = 'applicants';

            if ($typenow == $post_type) {
                //Applicants Status
                $values = array(
                    'approved' => esc_html__('Approved', 'jobportal-framework'),
                    'pending' => esc_html__('Pending', 'jobportal-framework'),
                    'rejected' => esc_html__('Rejected', 'jobportal-framework'),
                );
?>
                <select name="applicants_status">
                    <option value=""><?php esc_html_e('All Status', 'jobportal-framework'); ?></option>
                    <?php $current_v = isset($_GET['applicants_status']) ? jobportal_clean(wp_unslash($_GET['applicants_status'])) : '';
                    foreach ($values as $value => $label) {
                        printf(
                            '<option value="%s"%s>%s</option>',
                            $value,
                            $value == $current_v ? ' selected="selected"' : '',
                            $label
                        );
                    }
                    ?>
                </select>
<?php }
        }

        /**
         * applicants_filter
         * @param $query
         */
        public function applicants_filter($query)
        {
            global $pagenow;
            $post_type = 'applicants';
            $q_vars    = &$query->query_vars;
            $filter_arr = array();
            if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type) {
                $applicants_user = isset($_GET['applicants_user']) ? jobportal_clean(wp_unslash($_GET['applicants_user'])) : '';
                if ($applicants_user !== '') {
                    $user = get_user_by('login', $applicants_user);
                    $user_id = -1;
                    if ($user) {
                        $user_id = $user->ID;
                    }
                    $filter_arr[] = array(
                        'key' => JOBPORTAL_METABOX_PREFIX . 'applicants_user_id',
                        'value' => $user_id,
                        'compare' => 'IN',
                    );
                }

                $_applicants_status = isset($_GET['applicants_status']) ? jobportal_clean(wp_unslash($_GET['applicants_status'])) : '';

                if ($_applicants_status !== '') {
                    $applicants_status = 0;
                    if ($_applicants_status == 'paid') {
                        $applicants_status = 1;
                    }
                    $filter_arr[] = array(
                        'key' => JOBPORTAL_METABOX_PREFIX . 'applicants_payment_status',
                        'value' => $applicants_status,
                        'compare' => '=',
                    );
                }
                if (!empty($filter_arr)) {
                    $q_vars['meta_query'] = $filter_arr;
                }
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
            if ($post->post_type == 'applicants') {
                unset($actions['view']);
            }
            return $actions;
        }
    }
}
