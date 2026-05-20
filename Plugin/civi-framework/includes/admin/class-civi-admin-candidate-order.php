<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if (!class_exists('Civi_Admin_candidate_order')) {
    /**
     * Class Civi_Admin_candidate_order
     */
    class Civi_Admin_candidate_order
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
            $columns['title']           = esc_html__('Title', 'civi-framework');
            $columns['buyer']           = esc_html__('Buyer', 'civi-framework');
            $columns['name_package']    = esc_html__('Package Name', 'civi-framework');
            $columns['price']           = esc_html__('Price', 'civi-framework');
            $columns['payment_method']  = esc_html__('Payment', 'civi-framework');
            $columns['status']          = esc_html__('Status', 'civi-framework');
            $columns['activate_date']   = esc_html__('Activate Date', 'civi-framework');
            $columns['expires_date']    = esc_html__('Expires Date', 'civi-framework');

            $custom_order = [
                'cb',
                'thumb',
                'title',
                'buyer',
                'name_package',
                'price',
                'payment_method',
                'status',
                'activate_date',
                'expires_date'
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

            if (isset($vars['orderby']) && 'status' == $vars['orderby']) {
                $vars = array_merge($vars, array(
                    'meta_key' => CIVI_METABOX_PREFIX . 'candidate_order_status',
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
            $post_status = get_post_meta($post->ID, CIVI_METABOX_PREFIX . 'candidate_order_payment_status', true);
            if ($post->post_type == 'candidate_order') {
                if ($post_status == 1) {
                    $actions['candidate_order-pending'] = '<a href="' . wp_nonce_url(add_query_arg('pending_order', $post->ID), 'pending_order') . '">' . esc_html__('Pending', 'civi-framework') . '</a>';
                } else {
                    $actions['candidate_order-active'] = '<a href="' . wp_nonce_url(add_query_arg('active_order', $post->ID), 'active_order') . '">' . esc_html__('Active', 'civi-framework') . '</a>';
                }
            }
            return $actions;
        }

        /**
         * Approve Service
         */
        public function candidate_order_active()
        {
            if (!empty($_GET['active_order']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'active_order')) {
                $current_date = date('Y-m-d');
                $post_id = absint(civi_clean(wp_unslash($_GET['active_order'])));

                $civi_order = new Civi_Candidate_Order();
                $order_meta = $civi_order->get_candidate_order_meta($post_id);

                $user_id = isset($order_meta['candidate_order_user_id']) ? $order_meta['candidate_order_user_id'] : 0;
                $candidate_package_id = isset($order_meta['candidate_order_item_id']) ? $order_meta['candidate_order_item_id'] : 0;

                update_post_meta($post_id, CIVI_METABOX_PREFIX . 'candidate_order_payment_status', 1);
                update_post_meta($post_id, CIVI_METABOX_PREFIX . 'candidate_order_date', $current_date);

                if ($user_id) {
                    update_user_meta($user_id, CIVI_METABOX_PREFIX . 'package_activate_date', $current_date);

                    if ($candidate_package_id) {
                        $civi_candidate_package = new Civi_Candidate_Package();
                        $civi_candidate_package->insert_user_candidate_package($user_id, $candidate_package_id);

                        $user = get_user_by('id', $user_id);
                        if ($user) {
                            $candidate_package_price = get_post_meta($candidate_package_id, CIVI_METABOX_PREFIX . 'candidate_package_price', true);
                            $candidate_package_free = get_post_meta($candidate_package_id, CIVI_METABOX_PREFIX . 'candidate_package_free', true);
                            $payment_method = isset($order_meta['candidate_order_payment_method']) ? $order_meta['candidate_order_payment_method'] : '';

                            if (floatval($candidate_package_price) > 0 && $candidate_package_free != 1 && $payment_method != 'Free_Package') {
                                $args = array();
                                civi_send_email($user->user_email, 'mail_activated_package', $args);
                            }

                            $admin_email = get_bloginfo('admin_email');
                            $package_admin_args = array(
                                'user_login' => $user->user_login,
                                'user_email' => $user->user_email,
                                'package_id' => $candidate_package_id,
                            );
                            civi_send_email($admin_email, 'admin_mail_activated_package', $package_admin_args);
                        }
                    }
                }

                wp_redirect(remove_query_arg('active_order', add_query_arg('active_order', $post_id, admin_url('edit.php?post_type=candidate_order'))));
                exit;
            }
        }

        public function candidate_order_pending()
        {
            if (!empty($_GET['pending_order']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'pending_order')) {
                $post_id = absint(civi_clean(wp_unslash($_GET['pending_order'])));
                update_post_meta($post_id, CIVI_METABOX_PREFIX . 'candidate_order_payment_status', 0);

                wp_redirect(remove_query_arg('pending_order', add_query_arg('pending_order', $post_id, admin_url('edit.php?post_type=candidate_order'))));
                exit;
            }
        }

        /**
         * Display custom column for candidate_order
         * @param $column
         */
        public function display_custom_column($column)
        {
            global $post;
            $candidate_order_meta = get_post_meta($post->ID, CIVI_METABOX_PREFIX . 'candidate_order_meta', true);
            switch ($column) {
                case 'thumb':
                    $author_id = $candidate_order_meta['candidate_order_user_id'];
                    $candidate_avatar = get_the_author_meta('author_avatar_image_url', $author_id);
                    if (!empty($candidate_avatar)) {
                        echo '<img src = " ' . $candidate_avatar . '" alt=""/>';
                    } else {
                        echo '&ndash;';
                    }
                    break;
                case 'buyer':
                    $user_info = get_userdata($candidate_order_meta['candidate_order_user_id']);
                    if ($user_info) {
                        esc_html_e($user_info->display_name);
                    }
                    break;
                case 'name_package':
                    $candidate_package_id = $candidate_order_meta['candidate_order_item_id'];
                    $name_package = get_the_title($candidate_package_id);
                    echo $name_package;
                    break;
                case 'payment_method':
                    echo Civi_candidate_order::get_candidate_order_payment_method($candidate_order_meta['candidate_order_payment_method']);
                    break;
                case 'price':
                    echo $candidate_order_meta['candidate_order_item_price'];
                    break;
                case 'status':
                    $candidate_order_status = get_post_meta($post->ID, CIVI_METABOX_PREFIX . 'candidate_order_payment_status', true);
                    if ($candidate_order_status == 0) {
                        echo '<span class="label civi-label-red">' . esc_html__('Pending', 'civi-framework') . '</span>';
                    } else {
                        echo '<span class="label civi-label-blue">' . esc_html__('Active', 'civi-framework') . '</span>';
                    }
                    break;
                case 'activate_date':
                    $candidate_package_activate_date = $candidate_order_meta['candidate_order_purchase_date'];
                    echo $candidate_package_activate_date;
                    break;
                case 'expires_date':
                    $expired_time = '';
                    $candidate_package_id = $candidate_order_meta['candidate_order_item_id'];
                    $candidate_package_time_unit = get_post_meta($candidate_package_id, CIVI_METABOX_PREFIX . 'candidate_package_time_unit', true);
                    $candidate_package_period = get_post_meta($candidate_package_id, CIVI_METABOX_PREFIX . 'candidate_package_period', true);
                    $candidate_package_activate_date = strtotime($candidate_order_meta['candidate_order_purchase_date']);
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
                    $enable_package_service_unlimited_time = get_post_meta($candidate_package_id, CIVI_METABOX_PREFIX . 'enable_package_service_unlimited_time', true);
                    if ($enable_package_service_unlimited_time == 1) {
                        $expired_date = esc_html__('Never Expires');
                    } else {
                        $expired_date = date_i18n('Y-m-d', $expired_time);
                    }
                    echo $expired_date;
                    break;
            }
        }

        /**
         * Modify candidate_order slug
         * @param $existing_slug
         * @return string
         */
        public function modify_candidate_order_slug($existing_slug)
        {
            $candidate_order_url_slug = civi_get_option('candidate_order_url_slug');
            if ($candidate_order_url_slug) {
                return $candidate_order_url_slug;
            }
            return $existing_slug;
        }

        /**
         * Filter Restrict
         */
        public function filter_restrict_manage_candidate_order()
        {
            global $typenow;
            $post_type = 'candidate_order';
            if ($typenow == $post_type) {
                //Invoice Status
                $values = array(
                    '0' => esc_html__('Pending', 'civi-framework'),
                    '1' => esc_html__('Active', 'civi-framework'),
                );
?>
                <select name="candidate_order_status">
                    <option value=""><?php esc_html_e('All Status', 'civi-framework'); ?></option>
                    <?php $current_v = isset($_GET['candidate_order_status']) ? civi_clean(wp_unslash($_GET['candidate_order_status'])) : '';
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
                    'Paypal' => esc_html__('Paypal', 'civi-framework'),
                    'Stripe' => esc_html__('Stripe', 'civi-framework'),
                    'Wire_Transfer' => esc_html__('Wire Transfer', 'civi-framework'),
                    'Free_Package' => esc_html__('Free Package', 'civi-framework'),
                );
                ?>
                <select name="candidate_order_payment_method">
                    <option value=""><?php esc_html_e('All Payment', 'civi-framework'); ?></option>
                    <?php $current_v = isset($_GET['candidate_order_payment_method']) ? wp_unslash(civi_clean($_GET['candidate_order_payment_method'])) : '';
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
                <?php $candidate_order_user = isset($_GET['candidate_order_user']) ? civi_clean(wp_unslash($_GET['candidate_order_user'])) : ''; ?>
                <input type="text" placeholder="<?php esc_attr_e('Buyer', 'civi-framework'); ?>" name="candidate_order_user" value="<?php echo esc_attr($candidate_order_user); ?>">
<?php }
        }

        /**
         * candidate_order_filter
         * @param $query
         */
        public function candidate_order_filter($query)
        {
            global $pagenow;
            $post_type = 'candidate_order';
            $q_vars    = &$query->query_vars;
            $filter_arr = array();
            if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type) {
                $candidate_order_user = isset($_GET['candidate_order_user']) ? civi_clean(wp_unslash($_GET['candidate_order_user'])) : '';
                if ($candidate_order_user !== '') {
                    $user = get_user_by('login', $candidate_order_user);
                    $user_id = -1;
                    if ($user) {
                        $user_id = $user->ID;
                    }
                    $filter_arr[] = array(
                        'key' => CIVI_METABOX_PREFIX . 'candidate_order_user_id',
                        'value' => $user_id,
                        'compare' => 'IN',
                    );
                }

                $candidate_order_status = isset($_GET['candidate_order_status']) ? civi_clean(wp_unslash($_GET['candidate_order_status'])) : '';
                if ($candidate_order_status !== '') {
                    $candidate_order_status = 0;
                    if ($candidate_order_status == '1') {
                        $candidate_order_status = 1;
                    }
                    $filter_arr[] = array(
                        'key' => CIVI_METABOX_PREFIX . 'candidate_order_status',
                        'value' => $candidate_order_status,
                        'compare' => '=',
                    );
                }

                $candidate_order_payment_method = isset($_GET['candidate_order_payment_method']) ? civi_clean(wp_unslash($_GET['candidate_order_payment_method'])) : '';
                if ($candidate_order_payment_method !== '') {
                    $filter_arr[] = array(
                        'key' => CIVI_METABOX_PREFIX . 'candidate_order_payment_method',
                        'value' => $candidate_order_payment_method,
                        'compare' => '=',
                    );
                }
            }
        }
    }
}
