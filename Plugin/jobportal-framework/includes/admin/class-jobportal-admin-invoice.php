<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if (!class_exists('JobPortal_Admin_Invoice')) {
    /**
     * Class JobPortal_Admin_Invoice
     */
    class JobPortal_Admin_Invoice
    {

        /**
         * Get product by name
         */
        public function get_product_by_name($post_name, $output = OBJECT)
        {
            global $wpdb;
            $post = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type='product'", $post_name));
            if ($post)
                return get_post($post, $output);

            return null;
        }

        /**
         * Register custom columns
         * @param $columns
         * @return array
         */
        public function register_custom_column_titles($columns)
        {
            $columns['cb']                     = '<input type="checkbox" />';
            $columns['title']                  = esc_html__('Invoice', 'jobportal-framework');
            $columns['invoice_status']        = esc_html__('Status', 'jobportal-framework');
            $columns['invoice_payment_method'] = esc_html__('Payment Method', 'jobportal-framework');
            $columns['invoice_payment_type']  = esc_html__('Payment Type', 'jobportal-framework');
            $columns['invoice_price']         = esc_html__('Money', 'jobportal-framework');
            $columns['invoice_user_id']       = esc_html__('Name', 'jobportal-framework');
            $columns['date']                  = esc_html__('Date', 'jobportal-framework');

            $custom_order = [
                'cb',
                'title',
                'invoice_status',
                'invoice_payment_method',
                'invoice_payment_type',
                'invoice_price',
                'invoice_user_id',
                'date'
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
            $columns['title'] = 'title';
            $columns['invoice_status'] = 'invoice_status';
            $columns['invoice_payment_method'] = 'invoice_payment_method';
            $columns['invoice_payment_type'] = 'invoice_payment_type';
            $columns['invoice_price'] = 'invoice_price';
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

            if (isset($vars['orderby']) && 'invoice_payment_method' == $vars['orderby']) {
                $vars = array_merge($vars, array(
                    'meta_key' => JOBPORTAL_METABOX_PREFIX . 'invoice_payment_method',
                    'orderby' => 'meta_value',
                ));
            }
            if (isset($vars['orderby']) && 'invoice_payment_type' == $vars['orderby']) {
                $vars = array_merge($vars, array(
                    'meta_key' => JOBPORTAL_METABOX_PREFIX . 'invoice_payment_type',
                    'orderby' => 'meta_value',
                ));
            }
            if (isset($vars['orderby']) && 'invoice_price' == $vars['orderby']) {
                $vars = array_merge($vars, array(
                    'meta_key' => JOBPORTAL_METABOX_PREFIX . 'invoice_price',
                    'orderby' => 'meta_value_num',
                ));
            }
            if (isset($vars['orderby']) && 'invoice_status' == $vars['orderby']) {
                $vars = array_merge($vars, array(
                    'meta_key' => JOBPORTAL_METABOX_PREFIX . 'invoice_payment_status',
                    'orderby' => 'meta_value_num',
                ));
            }
            return $vars;
        }
        /**
         * Display custom column for invoice
         * @param $column
         */
        public function display_custom_column($column)
        {
            global $post;
            $invoice_meta = get_post_meta($post->ID, JOBPORTAL_METABOX_PREFIX . 'invoice_meta', true);
            switch ($column) {
                case 'invoice_payment_method':
                    echo JobPortal_Invoice::get_invoice_payment_method($invoice_meta['invoice_payment_method']);
                    break;
                case 'invoice_payment_type':
                    echo JobPortal_Invoice::get_invoice_payment_type($invoice_meta['invoice_payment_type']);
                    break;
                case 'invoice_price':
                    esc_html_e($invoice_meta['invoice_item_price']);
                    break;
                case 'invoice_user_id':
                    $user_info = get_userdata($invoice_meta['invoice_user_id']);
                    if ($user_info) {
                        esc_html_e($user_info->display_name);
                    }
                    break;
                case 'invoice_status':
                    $invoice_status = get_post_meta($post->ID, JOBPORTAL_METABOX_PREFIX . 'invoice_payment_status', true);
                    if ($invoice_status == 0) {
                        echo '<span class="label jobportal-label-red">' . esc_html__('Pending', 'jobportal-framework') . '</span>';
                    } else {
                        echo '<span class="label jobportal-label-blue">' . esc_html__('Active', 'jobportal-framework') . '</span>';
                    }
                    break;
            }
        }

        /**
         * Active Invoice
         */
        public function invoice_active()
        {
            if (!empty($_GET['invoice_active']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'invoice_active')) {
                $post_id = absint(jobportal_clean(wp_unslash($_GET['invoice_active'])));
                update_post_meta($post_id, JOBPORTAL_METABOX_PREFIX . 'invoice_payment_status', 1);

                // Get invoice meta to check if it's a Package payment
                $jobportal_invoice = new JobPortal_Invoice();
                $jobportal_meta = $jobportal_invoice->get_invoice_meta($post_id);

                // If it's a Package payment, activate package and send email
                if ($jobportal_meta && $jobportal_meta['invoice_payment_type'] == 'Package') {
                    $user_id = $jobportal_meta['invoice_user_id'];
                    $package_id = $jobportal_meta['invoice_item_id'];
                    $user = get_user_by('id', $user_id);

                    if ($user && $package_id) {
                        $jobportal_package = new JobPortal_Package();
                        $invoice_pdf_id = get_post_meta($post_id, JOBPORTAL_METABOX_PREFIX . 'invoice_pdf_id', true);

                        // Activate package (or re-activate to ensure it's active)
                        $jobportal_package->insert_user_package($user_id, $package_id, $invoice_pdf_id);

                        $package_price = get_post_meta($package_id, JOBPORTAL_METABOX_PREFIX . 'package_price', true);
                        $package_free = get_post_meta($package_id, JOBPORTAL_METABOX_PREFIX . 'package_free', true);
                        $payment_method = isset($jobportal_meta['invoice_payment_method']) ? $jobportal_meta['invoice_payment_method'] : '';

                        // Send activated package email if not free package
                        if (floatval($package_price) > 0 && $package_free != 1 && $payment_method != 'Free_Package') {
                            $attachment_url = wp_get_attachment_url($invoice_pdf_id);
                            $args = array(
                                'pdf_file' => $attachment_url,
                            );
                            jobportal_send_email($user->user_email, 'mail_activated_package', $args);
                        }

                        // Send admin notification
                        $admin_email = get_bloginfo('admin_email');
                        $package_admin_args = array(
                            'user_login' => $user->user_login,
                            'user_email' => $user->user_email,
                            'package_id' => $package_id,
                        );
                        jobportal_send_email($admin_email, 'admin_mail_activated_package', $package_admin_args);
                    }
                }

                wp_redirect(remove_query_arg('invoice_active', add_query_arg('invoice_active', $post_id, admin_url('edit.php?post_type=invoice'))));
                exit;
            }
        }

        /**
         * Pending Invoice
         */
        public function invoice_pending()
        {
            if (!empty($_GET['invoice_pending']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'invoice_pending')) {
                $post_id = absint(jobportal_clean(wp_unslash($_GET['invoice_pending'])));
                update_post_meta($post_id, JOBPORTAL_METABOX_PREFIX . 'invoice_payment_status', 0);

                wp_redirect(remove_query_arg('invoice_pending', add_query_arg('invoice_pending', $post_id, admin_url('edit.php?post_type=invoice'))));
                exit;
            }
        }

        /**
         * Get invoices by place
         * @param $jobs_id
         */
        public function get_invoices_by_place($jobs_id)
        {
            $args = array(
                'post_type' => 'invoice',
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => JOBPORTAL_METABOX_PREFIX . 'invoice_item_id',
                        'value' => $jobs_id,
                        'compare' => '=',
                        'type' => 'NUMERIC'
                    ),
                    array(
                        'key' => JOBPORTAL_METABOX_PREFIX . 'invoice_payment_type',
                        'value' => 'Package',
                        'compare' => '!=',
                        'type' => 'CHAR'
                    )
                )
            );
            $invoices = get_posts($args);
            if (!$invoices) {
                esc_html_e('No invoice', 'jobportal-framework');
            } else {
                foreach ($invoices as $invoice) :
                    if ($invoice->ID > 0) :
?>
                        <a title="<?php esc_attr_e('Click to view invoice', 'jobportal-framework') ?>" href="<?php echo get_edit_post_link($invoice->ID) ?>"><?php esc_html_e($invoice->ID); ?></a>
                <?php
                    endif;
                endforeach;
            }
        }

        /**
         * Modify invoice slug
         * @param $existing_slug
         * @return string
         */
        public function modify_invoice_slug($existing_slug)
        {
            $invoice_url_slug = jobportal_get_option('invoice_url_slug');
            if ($invoice_url_slug) {
                return $invoice_url_slug;
            }
            return $existing_slug;
        }
        /**
         * filter_restrict_manage_invoice
         */
        public function filter_restrict_manage_invoice()
        {
            global $typenow;
            $post_type = 'invoice';

            if ($typenow == $post_type) {
                //Invoice Status
                $values = array(
                    'pend' => esc_html__('Pending', 'jobportal-framework'),
                    'paid' => esc_html__('Active', 'jobportal-framework'),
                );
                ?>
                <select name="invoice_status">
                    <option value=""><?php esc_html_e('All Status', 'jobportal-framework'); ?></option>
                    <?php $current_v = isset($_GET['invoice_status']) ? jobportal_clean(wp_unslash($_GET['invoice_status'])) : '';
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
                    'Free_Package' => esc_html__('Free Package', 'jobportal-framework'),
                );
                ?>
                <select name="invoice_payment_method">
                    <option value=""><?php esc_html_e('All Payment Methods', 'jobportal-framework'); ?></option>
                    <?php $current_v = isset($_GET['invoice_payment_method']) ? wp_unslash(jobportal_clean($_GET['invoice_payment_method'])) : '';
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
                $invoice_user = isset($_GET['invoice_user']) ? jobportal_clean(wp_unslash($_GET['invoice_user'])) : ''; ?>
                <input type="text" placeholder="<?php esc_attr_e('Buyer', 'jobportal-framework'); ?>" name="invoice_user" value="<?php echo esc_attr($invoice_user); ?>">
<?php }
        }

        /**
         * invoice_filter
         * @param $query
         */
        public function invoice_filter($query)
        {
            global $pagenow;
            $post_type = 'invoice';
            $q_vars    = &$query->query_vars;
            $filter_arr = array();
            if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type) {
                $invoice_user = isset($_GET['invoice_user']) ? jobportal_clean(wp_unslash($_GET['invoice_user'])) : '';
                if ($invoice_user !== '') {
                    $user = get_user_by('login', $invoice_user);
                    $user_id = -1;
                    if ($user) {
                        $user_id = $user->ID;
                    }
                    $filter_arr[] = array(
                        'key' => JOBPORTAL_METABOX_PREFIX . 'invoice_user_id',
                        'value' => $user_id,
                        'compare' => 'IN',
                    );
                }

                $_invoice_status = isset($_GET['invoice_status']) ? jobportal_clean(wp_unslash($_GET['invoice_status'])) : '';

                if ($_invoice_status !== '') {
                    $invoice_status = 0;
                    if ($_invoice_status == 'paid') {
                        $invoice_status = 1;
                    }
                    $filter_arr[] = array(
                        'key' => JOBPORTAL_METABOX_PREFIX . 'invoice_payment_status',
                        'value' => $invoice_status,
                        'compare' => '=',
                    );
                }

                $invoice_payment_method = isset($_GET['invoice_payment_method']) ? jobportal_clean(wp_unslash($_GET['invoice_payment_method'])) : '';

                if ($invoice_payment_method !== '') {
                    $filter_arr[] = array(
                        'key' => JOBPORTAL_METABOX_PREFIX . 'invoice_payment_method',
                        'value' => $invoice_payment_method,
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
            if ($post->post_type == 'invoice') {
                unset($actions['view']);
            }
            // Check for your post type.
            $post_status = get_post_meta($post->ID, JOBPORTAL_METABOX_PREFIX . 'invoice_payment_status', true);
            if ($post->post_type == 'invoice') {
                if ($post_status == 1) {
                    $actions['invoice-pending'] = '<a href="' . wp_nonce_url(add_query_arg('invoice_pending', $post->ID), 'invoice_pending') . '">' . esc_html__('Pending', 'jobportal-framework') . '</a>';
                } else {
                    $actions['invoice-active'] = '<a href="' . wp_nonce_url(add_query_arg('invoice_active', $post->ID), 'invoice_active') . '">' . esc_html__('Active', 'jobportal-framework') . '</a>';
                }
            }
            return $actions;
        }
    }
}
