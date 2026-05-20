<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if (!class_exists('JobPortal_Admin_service_order')) {
    /**
     * Class JobPortal_Admin_service_order
     */
    class JobPortal_Admin_service_order
    {
        /**
         * Register custom columns
         * @param $columns
         * @return array
         */
        public function register_custom_column_titles($columns)
        {
            $columns['cb']              = '<input type="checkbox" />';
            $columns['thumb']           = esc_html__('Avatar', 'jobportal-framework');
            $columns['title']           = esc_html__('Service Name', 'jobportal-framework');
            $columns['buyer']           = esc_html__('Buyer', 'jobportal-framework');
            $columns['price']           = esc_html__('Price', 'jobportal-framework');
            $columns['payment_method']  = esc_html__('Payment', 'jobportal-framework');
            $columns['status']          = esc_html__('Status', 'jobportal-framework');
            $columns['activate_date']   = esc_html__('Activate Date', 'jobportal-framework');

            $custom_order = [
                'cb',
                'thumb',
                'title',
                'buyer',
                'price',
                'payment_method',
                'status',
                'activate_date'
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
                    'meta_key' => JOBPORTAL_METABOX_PREFIX . 'service_order_payment_status',
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
            $post_status = get_post_meta($post->ID, JOBPORTAL_METABOX_PREFIX . 'service_order_payment_status', true);
            if ($post->post_type == 'service_order') {
                if ($post_status === 'inprogress') {
                    $actions['pending-order'] = '<a href="' . wp_nonce_url(add_query_arg('pending_order', $post->ID), 'pending_order') . '">' . esc_html__('Pending', 'jobportal-framework') . '</a>';
                } elseif ($post_status === 'pending') {
                    $actions['inprogress-order'] = '<a href="' . wp_nonce_url(add_query_arg('inprogress_order', $post->ID), 'inprogress_order') . '">' . esc_html__('Active', 'jobportal-framework') . '</a>';
                }
            }
            return $actions;
        }

        /**
         * Approve Service
         */
        public function service_order_inprogress()
        {
            if (!empty($_GET['inprogress_order']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'inprogress_order')) {
                $post_id = absint(jobportal_clean(wp_unslash($_GET['inprogress_order'])));
                update_post_meta($post_id, JOBPORTAL_METABOX_PREFIX . 'service_order_payment_status', 'inprogress');

                wp_redirect(remove_query_arg('inprogress_order', add_query_arg('inprogress_order', $post_id, admin_url('edit.php?post_type=service_order'))));
                exit;
            }
        }

        public function service_order_pending()
        {
            if (!empty($_GET['pending_order']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'pending_order')) {
                $post_id = absint(jobportal_clean(wp_unslash($_GET['pending_order'])));
                update_post_meta($post_id, JOBPORTAL_METABOX_PREFIX . 'service_order_payment_status', 'pending');

                wp_redirect(remove_query_arg('pending_order', add_query_arg('pending_order', $post_id, admin_url('edit.php?post_type=service_order'))));
                exit;
            }
        }

        /**
         * Display custom column for service_order
         * @param $column
         */
        public function display_custom_column($column)
        {
            global $post;
            $service_order_meta = get_post_meta($post->ID, JOBPORTAL_METABOX_PREFIX . 'service_order_meta', true);
            $payment_method = JobPortal_service_order::get_service_order_payment_method($service_order_meta['service_order_payment_method']);
            switch ($column) {
                case 'thumb':
                    $author_id = $service_order_meta['service_order_user_id'];
                    $service_avatar = get_the_author_meta('author_avatar_image_url', $author_id);
                    if (!empty($service_avatar)) {
                        echo '<img src = " ' . $service_avatar . '" alt=""/>';
                    } else {
                        echo '&ndash;';
                    }
                    break;
                case 'buyer':
                    $user_info = get_userdata($service_order_meta['service_order_user_id']);
                    if ($user_info) {
                        echo '<a href="' . get_edit_user_link($service_order_meta['service_order_user_id']) . '">' . esc_attr($user_info->display_name) . '</a>';
                    }
                    break;
                case 'payment_method':
                    echo $payment_method;
                    if ($payment_method !== 'Woocommerce') {
                        echo '<a href="' . get_edit_user_link($service_order_meta['service_order_user_id']) . '">' . esc_html__(' (View)', 'jobportal-framework') . '</a>';
                    }
                    break;
                case 'price':
                    echo $service_order_meta['service_order_item_price'];
                    break;
                case 'status':
                    $service_order_payment_status = get_post_meta($post->ID, JOBPORTAL_METABOX_PREFIX . 'service_order_payment_status', true);
                    if ($service_order_payment_status == 'inprogress') {
                        echo '<span class="label jobportal-label-violet">' . esc_html__('In Process', 'jobportal-framework') . '</span>';
                    } elseif ($service_order_payment_status == 'transferring') {
                        echo '<span class="label jobportal-label-pink">' . esc_html__('Transferring', 'jobportal-framework') . '</span>';
                    } elseif ($service_order_payment_status == 'canceled') {
                        echo '<span class="label jobportal-label-gray">' . esc_html__('Canceled', 'jobportal-framework') . '</span>';
                    } elseif ($service_order_payment_status == 'completed') {
                        echo '<span class="label jobportal-label-blue">' . esc_html__('Completed', 'jobportal-framework') . '</span>';
                    } elseif ($service_order_payment_status == 'expired') {
                        echo '<span class="label jobportal-label-gray">' . esc_html__('Expired', 'jobportal-framework') . '</span>';
                    } elseif ($service_order_payment_status == 'refund') {
                        echo '<span class="label jobportal-label-gray">' . esc_html__('Refund', 'jobportal-framework') . '</span>';
                    } else {
                        echo '<span class="label jobportal-label-yellow">' . esc_html__('Pending', 'jobportal-framework') . '</span>';
                    }
                    break;
                case 'activate_date':
                    $service_package_activate_date = $service_order_meta['service_order_purchase_date'];
                    echo $service_package_activate_date;
                    break;
            }
        }

        /**
         * Modify service_order slug
         * @param $existing_slug
         * @return string
         */
        public function modify_service_order_slug($existing_slug)
        {
            $service_order_url_slug = jobportal_get_option('service_order_url_slug');
            if ($service_order_url_slug) {
                return $service_order_url_slug;
            }
            return $existing_slug;
        }

        /**
         * Filter Restrict
         */
        public function filter_restrict_manage_service_order()
        {
            global $typenow;
            $post_type = 'service_order';
            if ($typenow == $post_type) {
                //Status
                $values = array(
                    'pending' => esc_html__('Pending', 'jobportal-framework'),
                    'inprogress' => esc_html__('In Process', 'jobportal-framework'),
                    'transferring' => esc_html__('Transferring', 'jobportal-framework'),
                    'canceled' => esc_html__('Canceled', 'jobportal-framework'),
                    'completed' => esc_html__('Completed', 'jobportal-framework'),
                    'expired' => esc_html__('Expired', 'jobportal-framework'),
                    'refund' => esc_html__('Refund', 'jobportal-framework'),
                );
?>
                <select name="service_order_payment_status">
                    <option value=""><?php esc_html_e('All Status', 'jobportal-framework'); ?></option>
                    <?php $current_v = isset($_GET['service_order_payment_status']) ? jobportal_clean(wp_unslash($_GET['service_order_payment_status'])) : '';
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
                    'Paypal' => esc_html__('Paypal', 'jobportal-framework'),
                    'Stripe' => esc_html__('Stripe', 'jobportal-framework'),
                    'Wire_Transfer' => esc_html__('Wire Transfer', 'jobportal-framework'),
                );
                ?>
                <select name="service_order_payment_method">
                    <option value=""><?php esc_html_e('All Payment', 'jobportal-framework'); ?></option>
                    <?php $current_v = isset($_GET['service_order_payment_method']) ? wp_unslash(jobportal_clean($_GET['service_order_payment_method'])) : '';
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
                <?php $service_order_user = isset($_GET['service_order_user']) ? jobportal_clean(wp_unslash($_GET['service_order_user'])) : ''; ?>
                <input type="text" placeholder="<?php esc_attr_e('Search user id', 'jobportal-framework'); ?>" name="service_order_user" value="<?php echo esc_attr($service_order_user); ?>">
<?php }
        }

        /**
         * service_order_filter
         * @param $query
         */
        public function service_order_filter($query)
        {
            global $pagenow;
            $post_type = 'service_order';
            $q_vars    = &$query->query_vars;
            $filter_arr = array();
            if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type) {
                $service_order_user = isset($_GET['service_order_user']) ? jobportal_clean(wp_unslash($_GET['service_order_user'])) : '';
                if ($service_order_user !== '') {
                    $filter_arr[] = array(
                        'key' => JOBPORTAL_METABOX_PREFIX . 'service_order_user_id',
                        'value' => $service_order_user,
                        'compare' => '==',
                    );
                }

                $service_order_payment_status = isset($_GET['service_order_payment_status']) ? jobportal_clean(wp_unslash($_GET['service_order_payment_status'])) : '';
                if ($service_order_payment_status !== '') {
                    $filter_arr[] = array(
                        'key' => JOBPORTAL_METABOX_PREFIX . 'service_order_payment_status',
                        'value' => $service_order_payment_status,
                        'compare' => '=',
                    );
                }

                $service_order_payment_method = isset($_GET['service_order_payment_method']) ? jobportal_clean(wp_unslash($_GET['service_order_payment_method'])) : '';
                if ($service_order_payment_method !== '') {
                    $filter_arr[] = array(
                        'key' => JOBPORTAL_METABOX_PREFIX . 'service_order_payment_method',
                        'value' => $service_order_payment_method,
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
