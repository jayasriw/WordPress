<?php
if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('JobPortal_Candidate_Order')) {
    /**
     * Class JobPortal_Candidate_Order
     */
    class JobPortal_Candidate_Order
    {
        /**
         * Get total my candidate_order
         * @return int
         */
        public function get_total_my_candidate_order()
        {
            $args = array(
                'post_type' => 'candidate_order',
                'meta_query' => array(
                    array(
                        'key' => JOBPORTAL_METABOX_PREFIX . 'candidate_order_user_id',
                        'value' => get_current_user_id(),
                        'compare' => '='
                    )
                )
            );
            $candidate_orders = new WP_Query($args);
            wp_reset_postdata();
            return $candidate_orders->found_posts;
        }

        /**
         * Insert candidate_order
         * @param $payment_type
         * @param $item_id
         * @param $user_id
         * @param $payment_for
         * @param $payment_method
         * @param int $paid
         * @param string $payment_id
         * @param string $payer_id
         * @return int|WP_Error
         */
        public function insert_candidate_order($payment_type, $item_id, $user_id, $payment_for, $payment_method, $paid = 0, $payment_id = '', $payer_id = '')
        {
            $package_free = get_post_meta($item_id, JOBPORTAL_METABOX_PREFIX . 'candidate_package_free', true);
            if ($package_free == 1) {
                $total_money = 0;
            } else {
                $total_money = get_post_meta($item_id, JOBPORTAL_METABOX_PREFIX . 'candidate_package_price', true);
            }
            $time = time();
            $candidate_order_date = date('Y-m-d', $time);

            $package_coupon = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'package_coupon', true);
            $package_coupon_id = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'package_coupon_id', true);

            if ($package_coupon) {
                $total_money = $total_money - intval($package_coupon);
            }

            $jobportal_meta = array();
            $jobportal_meta['candidate_order_item_id'] = $item_id;
            $jobportal_meta['candidate_order_item_price'] = $total_money;
            $jobportal_meta['candidate_order_purchase_date'] = $candidate_order_date;
            $jobportal_meta['candidate_order_user_id'] = $user_id;
            $jobportal_meta['candidate_order_payment_method'] = $payment_method;
            $jobportal_meta['trans_payment_id'] = $payment_id;
            $jobportal_meta['trans_payer_id'] = $payer_id;
            $posttitle = 'Order_' . $payment_method . '_' . $total_money . $user_id;
            $args = array(
                'post_title'    => $posttitle,
                'post_status'    => 'publish',
                'post_type'     => 'candidate_order'
            );

            $rw = jobportal_get_page_by_title($posttitle, 'candidate_order');
            if (is_object($rw) && $rw->ID) {
                $candidate_order_payment_status = get_post_meta($rw->ID, JOBPORTAL_METABOX_PREFIX . 'candidate_order_payment_status', true);
            } else {
                $candidate_order_payment_status = '';
            }

            // Only assign package when payment is confirmed ($paid == 1)
            if ($paid == 1) {
                $jobportal_candidate_package = new jobportal_candidate_package();
                $jobportal_candidate_package->insert_user_candidate_package($user_id, $item_id);
            }

            if (empty($rw->ID) || ($rw->ID && $candidate_order_payment_status == '1')) {
                $candidate_order_id =  wp_insert_post($args);
                update_post_meta($candidate_order_id, JOBPORTAL_METABOX_PREFIX . 'candidate_order_user_id', $user_id);
                update_post_meta($candidate_order_id, JOBPORTAL_METABOX_PREFIX . 'candidate_order_item_id', $item_id);
                update_post_meta($candidate_order_id, JOBPORTAL_METABOX_PREFIX . 'candidate_order_price', $total_money);
                update_post_meta($candidate_order_id, JOBPORTAL_METABOX_PREFIX . 'candidate_order_date', $candidate_order_date);
                update_post_meta($candidate_order_id, JOBPORTAL_METABOX_PREFIX . 'candidate_order_payment_method', $payment_method);
                update_post_meta($candidate_order_id, JOBPORTAL_METABOX_PREFIX . 'candidate_order_payment_status', $paid);

                update_post_meta($candidate_order_id, JOBPORTAL_METABOX_PREFIX . 'trans_payment_id', $payment_id);
                update_post_meta($candidate_order_id, JOBPORTAL_METABOX_PREFIX . 'trans_payer_id', $payer_id);

                update_post_meta($candidate_order_id, JOBPORTAL_METABOX_PREFIX . 'candidate_order_meta', $jobportal_meta);
                $update_post = array(
                    'ID'         => $candidate_order_id,
                );
                wp_update_post($update_post);
            } else {
                $candidate_order_id = $rw->ID;
            }

            if ($package_coupon_id) {
                $counpon_used = get_post_meta($package_coupon_id, JOBPORTAL_METABOX_PREFIX . 'coupon_used', true);
                if (empty($counpon_used)) {
                    update_post_meta($package_coupon_id, JOBPORTAL_METABOX_PREFIX . 'coupon_used', 1);
                } else {
                    update_post_meta($package_coupon_id, JOBPORTAL_METABOX_PREFIX . 'coupon_used', $counpon_used + 1);
                }
            }

            delete_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'package_coupon');
            delete_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'package_coupon_id');

            do_action('jobportal_after_insert_candidate_order', $user_id, $candidate_order_id, $jobportal_meta);

            return $candidate_order_id;
        }

        /**
         * get_candidate_order_meta
         * @param $post_id
         * @param bool|false $field
         * @return array|bool|mixed
         */
        public function get_candidate_order_meta($post_id, $field = false)
        {
            $defaults = array(
                'candidate_order_item_id' => '',
                'candidate_order_item_price' => '',
                'candidate_order_purchase_date' => '',
                'candidate_order_user_id' => '',
                'candidate_order_payment_method' => '',
                'trans_payment_id' => '',
                'trans_payer_id' => '',
            );
            $meta = get_post_meta($post_id, JOBPORTAL_METABOX_PREFIX . 'candidate_order_meta', true);
            $meta = wp_parse_args((array)$meta, $defaults);

            if ($field) {
                if (isset($meta[$field])) {
                    return $meta[$field];
                } else {
                    return false;
                }
            }
            return $meta;
        }

        /**
         * @param $payment_method
         * @return string
         */
        public static function get_candidate_order_payment_method($payment_method)
        {
            switch ($payment_method) {
                case 'Paypal':
                    return esc_html__('Paypal', 'jobportal-framework');
                    break;
                case 'Stripe':
                    return esc_html__('Stripe', 'jobportal-framework');
                    break;
                case 'Wire_Transfer':
                    return esc_html__('Wire Transfer', 'jobportal-framework');
                    break;
                case 'Free_Package':
                    return esc_html__('Free Package', 'jobportal-framework');
                    break;
                case 'Woocommerce':
                    return esc_html__('Woocommerce', 'jobportal-framework');
                    break;
                default:
                    return '';
            }
        }
        /**
         * Print candidate_order
         */
        public function candidate_order_print_ajax()
        {
            if (!isset($_POST['candidate_order_id']) || !is_numeric($_POST['candidate_order_id'])) {
                return;
            }
            $candidate_order_id = absint(wp_unslash($_POST['candidate_order_id']));
            $isRTL = 'false';
            if (isset($_POST['isRTL'])) {
                $isRTL = $_POST['isRTL'];
            }
            jobportal_get_template('candidate_order/candidate_order-print.php', array('candidate_order_id' => intval($candidate_order_id), 'isRTL' => $isRTL));
            wp_die();
        }
    }
}
