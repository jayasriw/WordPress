<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if (!class_exists('Civi_Admin_Service_Withdraw')) {
    /**
     * Class Civi_Admin_Service_Withdraw
     */
    class Civi_Admin_Service_Withdraw
    {
        /**
         * Register custom columns
         * @param $columns
         * @return array
         */
        public function register_custom_column_titles($columns)
        {
            $columns['cb']              = '<input type="checkbox" />';
            $columns['thumb']           = esc_html__('Avatar', 'civi-framework');
            $columns['title']           = esc_html__('Candidate Name', 'civi-framework');
            $columns['price']           = esc_html__('Price', 'civi-framework');
            $columns['payment_method']  = esc_html__('Payment', 'civi-framework');
            $columns['status']          = esc_html__('Status', 'civi-framework');
            $columns['request_date']    = esc_html__('Request Date', 'civi-framework');
            $columns['process_date']    = esc_html__('Process Date', 'civi-framework');

            $custom_order = [
                'cb',
                'thumb',
                'title',
                'price',
                'payment_method',
                'status',
                'request_date',
                'process_date'
            ];

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
            $columns['status'] = 'status';
            $columns['payment_method'] = 'payment_method';
            $columns['title'] = 'title';
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

            if (isset($vars['orderby']) && 'status' == $vars['orderby']) {
                $vars = array_merge($vars, array(
                    'meta_key' => CIVI_METABOX_PREFIX . 'service_withdraw_status',
                    'orderby' => 'meta_value_num',
                ));
            }

            return $vars;
        }

        /**
         * @param $actions
         * @param $post
         * @return mixed
         */
        public function modify_list_row_actions($actions, $post)
        {
            // Check for your post type.
            $post_status = get_post_meta($post->ID, CIVI_METABOX_PREFIX . 'service_withdraw_status', true);
            $post_price = get_post_meta($post->ID, CIVI_METABOX_PREFIX . 'service_withdraw_price', true);
            $total_price = get_post_meta($post->ID, CIVI_METABOX_PREFIX . 'service_withdraw_total_price', true);
            if ($post->post_type == 'service_withdraw') {
                if ($post_price > $total_price) {
                    $actions['ex-withdraw'] = '<span>' . esc_html__('Not enough balance', 'civi-framework') . '</span>';
                } else {
                    if ($post_status === 'pending') {
                        $actions['completed-withdraw'] = '<a href="' . wp_nonce_url(add_query_arg('completed_withdraw', $post->ID), 'completed_withdraw') . '">' . esc_html__('Completed', 'civi-framework') . '</a>';
                        $actions['canceled-withdraw'] = '<a href="' . wp_nonce_url(add_query_arg('canceled_withdraw', $post->ID), 'canceled_withdraw') . '">' . esc_html__('Canceled', 'civi-framework') . '</a>';
                    } elseif ($post_status === 'completed') {
                        $actions['pending-withdraw'] = '<a href="' . wp_nonce_url(add_query_arg('pending_withdraw', $post->ID), 'pending_withdraw') . '">' . esc_html__('Pending', 'civi-framework') . '</a>';
                    }
                }
            }
            return $actions;
        }

        public function service_withdraw_active()
        {
            if (!empty($_GET['completed_withdraw']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'completed_withdraw')) {
                $post_id = absint(civi_clean(wp_unslash($_GET['completed_withdraw'])));
                $author_id = get_post_meta($post_id, CIVI_METABOX_PREFIX . 'service_withdraw_user_id', true);
                $total_price = get_user_meta($author_id, CIVI_METABOX_PREFIX . 'service_withdraw_total_price', true);
                $post_price = get_post_meta($post_id, CIVI_METABOX_PREFIX . 'service_withdraw_price', true);
                $current_date = date('Y-m-d');

                update_post_meta($post_id, CIVI_METABOX_PREFIX . 'service_withdraw_status', 'completed');
                update_post_meta($post_id, CIVI_METABOX_PREFIX . 'service_withdraw_process_date', $current_date);
                if ($total_price >= $post_price) {
                    $price = $total_price - $post_price;
                    update_user_meta($author_id, CIVI_METABOX_PREFIX . 'service_withdraw_total_price', $price);
                }

                wp_redirect(remove_query_arg('completed_withdraw', add_query_arg('completed_withdraw', $post_id, admin_url('edit.php?post_type=service_withdraw'))));
                exit;
            }
        }

        public function service_withdraw_pending()
        {
            if (!empty($_GET['pending_withdraw']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'pending_withdraw')) {
                $post_id = absint(civi_clean(wp_unslash($_GET['pending_withdraw'])));
                update_post_meta($post_id, CIVI_METABOX_PREFIX . 'service_withdraw_status', 'pending');

                wp_redirect(remove_query_arg('pending_withdraw', add_query_arg('pending_withdraw', $post_id, admin_url('edit.php?post_type=service_withdraw'))));
                exit;
            }
        }

        public function service_withdraw_canceled()
        {
            if (!empty($_GET['canceled_withdraw']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'canceled_withdraw')) {
                $post_id = absint(civi_clean(wp_unslash($_GET['canceled_withdraw'])));
                $current_date = date('Y-m-d');
                update_post_meta($post_id, CIVI_METABOX_PREFIX . 'service_withdraw_status', 'canceled');
                update_post_meta($post_id, CIVI_METABOX_PREFIX . 'service_withdraw_process_date', $current_date);

                wp_redirect(remove_query_arg('canceled_withdraw', add_query_arg('canceled_withdraw', $post_id, admin_url('edit.php?post_type=service_withdraw'))));
                exit;
            }
        }

        /**
         * Display custom column for service_withdraw
         * @param $column
         */
        public function display_custom_column($column)
        {
            global $post;
            $author_id = get_post_meta($post->ID, CIVI_METABOX_PREFIX . 'service_withdraw_user_id', true);
            $payment_method = get_post_meta($post->ID, CIVI_METABOX_PREFIX . 'service_withdraw_payment_method', true);
            $payment_method = str_replace(['-', '_'], ' ', $payment_method);
            $price = get_post_meta($post->ID, CIVI_METABOX_PREFIX . 'service_withdraw_price', true);
            $currency_position = civi_get_option('currency_position');
            $currency_sign_default = civi_get_option('currency_sign_default');
            if ($currency_position == 'before') {
                $price = $currency_sign_default . $price;
            } else {
                $price = $price . $currency_sign_default;
            }
            $service_avatar = get_the_author_meta('author_avatar_image_url', $author_id);
            $request_date = get_the_date('Y-m-d');
            $process_date = get_post_meta($post->ID, CIVI_METABOX_PREFIX . 'service_withdraw_process_date', true);
            if (empty($process_date)) {
                $process_date = '...';
            }
            switch ($column) {
                case 'thumb':
                    if (!empty($service_avatar)) {
                        echo '<img src = " ' . $service_avatar . '" alt=""/>';
                    } else {
                        echo '&ndash;';
                    }
                    break;
                case 'price':
                    echo $price;
                    break;
                case 'payment_method':
                    echo $payment_method;
                    echo '<a href="' . get_edit_user_link($author_id) . '">' . esc_html__(' (View)', 'civi-framework') . '</a>';
                    break;
                case 'status':
                    $service_withdraw_status = get_post_meta($post->ID, CIVI_METABOX_PREFIX . 'service_withdraw_status', true);
                    if ($service_withdraw_status == 'completed') {
                        echo '<span class="label civi-label-blue">' . esc_html__('Completed', 'civi-framework') . '</span>';
                    } elseif ($service_withdraw_status == 'canceled') {
                        echo '<span class="label civi-label-gray">' . esc_html__('Canceled', 'civi-framework') . '</span>';
                    } else {
                        echo '<span class="label civi-label-yellow">' . esc_html__('Pending', 'civi-framework') . '</span>';
                    }
                    break;
                case 'request_date':
                    echo $request_date;
                    break;
                case 'process_date':
                    echo $process_date;
                    break;
            }
        }

        /**
         * Modify service_withdraw slug
         * @param $existing_slug
         * @return string
         */
        public function modify_service_withdraw_slug($existing_slug)
        {
            $service_withdraw_url_slug = civi_get_option('service_withdraw_url_slug');
            if ($service_withdraw_url_slug) {
                return $service_withdraw_url_slug;
            }
            return $existing_slug;
        }

        /**
         * Filter Restrict
         */
        public function filter_restrict_manage_service_withdraw()
        {
            global $typenow;
            $post_type = 'service_withdraw';
            if ($typenow == $post_type) {
                //Status
                $values = array(
                    'completed' => esc_html__('Completed', 'civi-framework'),
                    'pending' => esc_html__('Pending', 'civi-framework'),
                    'canceled' => esc_html__('Canceled', 'civi-framework'),
                );
?>
                <select name="service_withdraw_status">
                    <option value=""><?php esc_html_e('All Status', 'civi-framework'); ?></option>
                    <?php $current_v = isset($_GET['service_withdraw_status']) ? civi_clean(wp_unslash($_GET['service_withdraw_status'])) : '';
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
                <?php
                //Payment method
                $values = array(
                    'paypal' => esc_html__('Paypal', 'civi-framework'),
                    'stripe' => esc_html__('Stripe', 'civi-framework'),
                    'wire_transfer' => esc_html__('Wire Transfer', 'civi-framework'),
                );
                ?>
                <select name="service_withdraw_payment_method">
                    <option value=""><?php esc_html_e('All Payment', 'civi-framework'); ?></option>
                    <?php $current_v = isset($_GET['service_withdraw_payment_method']) ? wp_unslash(civi_clean($_GET['service_withdraw_payment_method'])) : '';
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
                <?php $service_withdraw_user = isset($_GET['service_withdraw_user']) ? civi_clean(wp_unslash($_GET['service_withdraw_user'])) : ''; ?>
                <input type="text" placeholder="<?php esc_attr_e('Search user id', 'civi-framework'); ?>" name="service_withdraw_user" value="<?php echo esc_attr($service_withdraw_user); ?>">
<?php }
        }

        /**
         * service_withdraw_filter
         * @param $query
         */
        public function service_withdraw_filter($query)
        {
            global $pagenow;
            $post_type = 'service_withdraw';
            $q_vars    = &$query->query_vars;
            $filter_arr = array();
            if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type) {
                $service_withdraw_user = isset($_GET['service_withdraw_user']) ? civi_clean(wp_unslash($_GET['service_withdraw_user'])) : '';
                if ($service_withdraw_user !== '') {
                    $filter_arr[] = array(
                        'key' => CIVI_METABOX_PREFIX . 'service_withdraw_user_id',
                        'value' => $service_withdraw_user,
                        'compare' => '=',
                    );
                }

                $service_withdraw_status = isset($_GET['service_withdraw_status']) ? civi_clean(wp_unslash($_GET['service_withdraw_status'])) : '';
                if ($service_withdraw_status !== '') {
                    $filter_arr[] = array(
                        'key' => CIVI_METABOX_PREFIX . 'service_withdraw_status',
                        'value' => $service_withdraw_status,
                        'compare' => '=',
                    );
                }

                $service_withdraw_payment_method = isset($_GET['service_withdraw_payment_method']) ? civi_clean(wp_unslash($_GET['service_withdraw_payment_method'])) : '';
                if ($service_withdraw_payment_method !== '') {
                    $filter_arr[] = array(
                        'key' => CIVI_METABOX_PREFIX . 'service_withdraw_payment_method',
                        'value' => $service_withdraw_payment_method,
                        'compare' => '=',
                    );
                }

                if (!empty($filter_arr)) {
                    $q_vars['meta_query'] = $filter_arr;
                }
            }
        }
    }
}
