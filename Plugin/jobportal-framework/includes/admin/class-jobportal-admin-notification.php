<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if (!class_exists('JobPortal_Admin_Notification')) {
    /**
     * Class JobPortal_Admin_Notification
     */
    class JobPortal_Admin_Notification
    {

        /**
         * Register custom columns
         * @param $columns
         * @return array
         */
        public function register_custom_column_titles($columns)
        {
            $columns['thumb']  = esc_html__('User', 'jobportal-framework');
            $columns['cb']     = '<input type="checkbox" />';
            $columns['title']  = esc_html__('Title', 'jobportal-framework');
            $columns['action'] = esc_html__('Action', 'jobportal-framework');
            $columns['author'] = esc_html__('Author', 'jobportal-framework');
            $columns['date']   = esc_html__('Date', 'jobportal-framework');

            $custom_order = ['cb', 'thumb', 'title', 'action', 'author', 'date'];
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
         * sortable_columns
         * @param $columns
         * @return mixed
         */
        public function sortable_columns($columns)
        {
            $columns['title'] = 'title';
            $columns['action'] = 'action';
            $columns['author'] = 'author';
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
            return $vars;
        }
        /**
         * Display custom column for messages
         * @param $column
         */
        public function display_custom_column($column)
        {
            global $post;
            $post_id = $post->ID;
            switch ($column) {
                case 'thumb':
                    $user_send = get_post_meta($post_id, JOBPORTAL_METABOX_PREFIX . 'user_send_noti', true);
                    $avatar = get_the_author_meta('author_avatar_image_url', $user_send);
                    if (!empty($avatar)) {
                        echo '<img src = " ' . $avatar . '" alt=""/>';
                    } else {
                        echo '&ndash;';
                    }
                    break;
                case 'action':
                    $action = get_post_meta($post_id, JOBPORTAL_METABOX_PREFIX . 'action_noti', true);
                    $action = trim($action, "-");
                    echo $action;
                    break;
                case 'author':
                    echo '<a href="' . esc_url(add_query_arg('author', $post->post_author)) . '">' . get_the_author() . '</a>';
                    break;
            }
        }

        /**
         * notification_filter
         * @param $query
         */
        public function notification_filter($query)
        {
            global $pagenow;
            $post_type = 'notification';
            $q_vars    = &$query->query_vars;
            $filter_arr = array();
            if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type) {
                $notification_user = isset($_GET['notification_user']) ? jobportal_clean(wp_unslash($_GET['notification_user'])) : '';
                if ($notification_user !== '') {
                    $user = get_user_by('login', $notification_user);
                    $user_id = -1;
                    if ($user) {
                        $user_id = $user->ID;
                    }
                    $filter_arr[] = array(
                        'key' => JOBPORTAL_METABOX_PREFIX . 'notification_user_id',
                        'value' => $user_id,
                        'compare' => 'IN',
                    );
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
            if ($post->post_type == 'notification') {
                unset($actions['view']);
            }
            return $actions;
        }
    }
}
