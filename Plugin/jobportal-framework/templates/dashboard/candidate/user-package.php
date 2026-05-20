<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $current_user;
$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$candidate_package_id = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'candidate_package_id', $user_id);
$candidate_package_activate = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'candidate_package_activate_date', true);
$candidate_package_activate_date = jobportal_convert_date_format($candidate_package_activate);
$candidate_package_time_unit = get_post_meta($candidate_package_id, JOBPORTAL_METABOX_PREFIX . 'candidate_package_time_unit', true);
$candidate_package_period = get_post_meta($candidate_package_id, JOBPORTAL_METABOX_PREFIX . 'candidate_package_period', true);
$enable_package_service_unlimited_time = get_post_meta($candidate_package_id, JOBPORTAL_METABOX_PREFIX . 'enable_package_service_unlimited_time', true);
$candidate_package_name = get_the_title($candidate_package_id);
$user_info = get_userdata($user_id);
$jobportal_candidate_package = new JobPortal_candidate_package();
$expired_date = $jobportal_candidate_package->get_expired_date($candidate_package_id, $user_id);
$check_candidate_package = $jobportal_candidate_package->user_candidate_package_available($user_id);
$candidate_paid_submission_type = jobportal_get_option('candidate_paid_submission_type');
// $expired_date_format = date(get_option('date_format'), strtotime($expired_date));
$expired_date_format = date_i18n(get_option('date_format'), strtotime($expired_date));
$candidate_package_activate_date = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'candidate_package_activate_date', true);
$activate_date_format = date_i18n(get_option('date_format'), strtotime($candidate_package_activate_date));

$candidate_package_number_jobs_applys = intval(get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'candidate_package_number_jobs_apply', true));

$current_date = date('Y-m-d');
if ($current_date < $expired_date) {
    $seconds = strtotime($expired_date) - strtotime($current_date);
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
    $expired_days = $dtF->diff($dtT)->format('%a');
} else {
    $expired_days = 0;
}
?>
<?php if ($candidate_paid_submission_type !== 'candidate_per_package') : ?>
    <p class="notice"><i class="fal fa-exclamation-circle"></i>
        <?php esc_html_e("You are on free submit active", 'jobportal-framework'); ?>
        <?php if (jobportal_get_option('enable_post_type_service') === '1') { ?>
            <a href="<?php echo jobportal_get_permalink('submit_service'); ?>">
                <?php esc_html_e('Add Service', 'jobportal-framework'); ?>
            </a>
        <?php } ?>
    </p>
<?php else : ?>
    <?php if ($candidate_package_id && $current_date >= $expired_date) : ?>
        <p class="notice"><i class="fal fa-exclamation-circle"></i>
            <?php esc_html_e("Package expired. Please select a new one.", 'jobportal-framework'); ?>
        </p>
    <?php endif; ?>
    <div class="entry-my-page packages-dashboard my-candidate-package">
        <div class="entry-title">
            <h4><?php esc_html_e('My Package', 'jobportal-framework') ?></h4>
            <?php if (jobportal_get_option('enable_post_type_service') === '1') { ?>
                <a href="<?php echo jobportal_get_permalink('submit_service'); ?>"
                    class="jobportal-button button-outline-accent">
                    <i class="far fa-plus"></i><?php esc_html_e('Create new service', 'jobportal-framework') ?>
                </a>
            <?php } ?>
        </div>
        <?php if ($candidate_package_id) : ?>
            <div class="table-dashboard-wapper">
                <table class="table-dashboard <?php if ($check_candidate_package == -1 || $check_candidate_package == 0) {
                                                    echo 'expired';
                                                } ?>">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('ID', 'jobportal-framework') ?></th>
                            <th><?php esc_html_e('Package Name', 'jobportal-framework') ?></th>
                            <th><?php esc_html_e('Status', 'jobportal-framework') ?></th>
                            <th><?php esc_html_e('Activation Date', 'jobportal-framework') ?></th>
                            <th><?php esc_html_e('Expiration Date', 'jobportal-framework') ?></th>
                            <th><?php esc_html_e('Date Remaining', 'jobportal-framework') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <span class="package-id">
                                    <?php if ($candidate_package_id) {
                                        echo "#$candidate_package_id";
                                    } ?>
                                </span>
                            </td>
                            <td>
                                <h3>
                                    <a href="<?php echo jobportal_get_permalink('package_candidate') ?>"><?php echo esc_attr($candidate_package_name) ?></a>
                                </h3>
                                <p><?php echo esc_attr($candidate_package_activate_date) ?></p>
                            </td>
                            <td>
                                <?php $package_status = jobportal_candidate_package_status();
                                if ($package_status === '0') { ?>
                                    <span class="label label-pending"><?php esc_html_e('Pending', 'jobportal-framework') ?></span>
                                <?php } elseif ($package_status === '-1') { ?>
                                    <span class="label label-close"><?php esc_html_e('Canceled', 'jobportal-framework') ?></span>
                                <?php } else { ?>
                                    <?php if (($current_date < $expired_date) || ($enable_package_service_unlimited_time == 1)) { ?>
                                        <?php if ($candidate_package_number_jobs_applys < 1) { ?>
                                            <span class="label label-close"><?php esc_html_e('Finished', 'jobportal-framework') ?></span>
                                        <?php } else { ?>
                                            <span class="label label-open"><?php esc_html_e('Activated', 'jobportal-framework') ?></span>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <span class="label label-close"><?php esc_html_e('Expired', 'jobportal-framework') ?></span>
                                    <?php } ?>
                                <?php } ?>
                            </td>
                            <td>
                                <span class="active-date">
                                    <?php if ($enable_package_service_unlimited_time == 1) {
                                        esc_html_e('Unlimited', 'jobportal-framework');
                                    } else {
                                        echo $activate_date_format;
                                    } ?>
                                </span>
                            </td>
                            <td>
                                <span class="expired-date">
                                    <?php if ($enable_package_service_unlimited_time == 1) {
                                        esc_html_e('Unlimited', 'jobportal-framework');
                                    } else {
                                        echo $expired_date_format;
                                    } ?>
                                </span>
                            </td>
                            <td>
                                <span class="remaining">
                                    <?php if ($enable_package_service_unlimited_time == 1) {
                                        esc_html_e('Never Expires', 'jobportal-framework');
                                    } else {
                                        echo sprintf(esc_html__('%s Day', 'jobportal-framework'), $expired_days);
                                    } ?>
                                </span>
                            </td>
                            <td>
                                <a href="#form-candidate-user-package" id="action-user-package">
                                    <?php esc_html_e('Overview', 'jobportal-framework') ?>
                                    <span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <a href="<?php echo jobportal_get_permalink('candidate_package'); ?>" class="jobportal-button jobportal-new-package" id="jobportal-new-package-link">
            <i class="far fa-plus"></i><?php esc_html_e('Add new package', 'jobportal-framework') ?>
            <span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
        </a>
    </div>

    <?php
    // Include package warning modal template
    jobportal_get_template('dashboard/candidate/package-warning-modal.php');
    ?>

    <!-- Purchase History Section -->
    <div class="entry-my-page packages-dashboard purchase-history">
        <div class="entry-title">
            <h4><?php esc_html_e('Purchase History', 'jobportal-framework') ?></h4>
        </div>

        <?php
        // Get items per page from URL or localStorage (default 5)
        $history_per_page = isset($_GET['history_per_page']) ? absint($_GET['history_per_page']) : 5;
        $history_per_page = in_array($history_per_page, array(5, 10, 20)) ? $history_per_page : 5;
        $history_paged = isset($_GET['history_paged']) ? max(1, absint($_GET['history_paged'])) : 1;

        // First query to get total count
        $args_count = array(
            'post_type'      => 'candidate_order',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => array(
                array(
                    'key'     => JOBPORTAL_METABOX_PREFIX . 'candidate_order_user_id',
                    'value'   => $user_id,
                    'compare' => '='
                )
            ),
        );
        $count_query = new WP_Query($args_count);
        $total_orders = $count_query->found_posts;
        $max_pages = ($history_per_page > 0) ? ceil($total_orders / $history_per_page) : 1;
        wp_reset_postdata();

        // Ensure current page doesn't exceed max pages
        if ($history_paged > $max_pages && $max_pages > 0) {
            $history_paged = $max_pages;
        }

        // Calculate display range
        $num_first = (($history_paged - 1) * $history_per_page) + 1;
        $num_last = min($history_paged * $history_per_page, $total_orders);

        // Main query with pagination
        $args_orders = array(
            'post_type'      => 'candidate_order',
            'posts_per_page' => $history_per_page,
            'paged'          => $history_paged,
            'meta_query'     => array(
                array(
                    'key'     => JOBPORTAL_METABOX_PREFIX . 'candidate_order_user_id',
                    'value'   => $user_id,
                    'compare' => '='
                )
            ),
            'orderby'        => 'date',
            'order'          => 'DESC'
        );
        $orders = new WP_Query($args_orders);

        if ($orders->have_posts()) :
        ?>
            <div class="table-dashboard-wapper">
                <table class="table-dashboard">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Order #', 'jobportal-framework') ?></th>
                            <th><?php esc_html_e('Package', 'jobportal-framework') ?></th>
                            <th><?php esc_html_e('Price', 'jobportal-framework') ?></th>
                            <th><?php esc_html_e('Payment', 'jobportal-framework') ?></th>
                            <th><?php esc_html_e('Date', 'jobportal-framework') ?></th>
                            <th><?php esc_html_e('Active Date', 'jobportal-framework') ?></th>
                            <th><?php esc_html_e('Expired Date', 'jobportal-framework') ?></th>
                            <th><?php esc_html_e('Status', 'jobportal-framework') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($orders->have_posts()) : $orders->the_post();
                            $order_id = get_the_ID();
                            $order_package_id = get_post_meta($order_id, JOBPORTAL_METABOX_PREFIX . 'candidate_order_item_id', true);
                            $price = get_post_meta($order_id, JOBPORTAL_METABOX_PREFIX . 'candidate_order_price', true);
                            $order_date = get_post_meta($order_id, JOBPORTAL_METABOX_PREFIX . 'candidate_order_date', true);
                            $status = get_post_meta($order_id, JOBPORTAL_METABOX_PREFIX . 'candidate_order_payment_status', true);
                            $package_name = get_the_title($order_package_id);

                            // Get candidate order meta (same as admin files)
                            $jobportal_candidate_order = new JobPortal_Candidate_Order();
                            $order_meta = $jobportal_candidate_order->get_candidate_order_meta($order_id);
                            $payment_method = isset($order_meta['candidate_order_payment_method']) ? $order_meta['candidate_order_payment_method'] : get_post_meta($order_id, JOBPORTAL_METABOX_PREFIX . 'candidate_order_payment_method', true);

                            // Determine invoice status (not package status)
                            $is_pending = ($status == '0' || $status === '');
                            $is_paid = ($status == '1');
                            $is_canceled = ($status == '-1');

                            // Get active date and expired date
                            $active_date = '';
                            $expired_date = '';

                            if ($is_paid && $order_package_id) {
                                // Get order purchase date
                                $order_purchase_date = isset($order_meta['candidate_order_purchase_date']) ? $order_meta['candidate_order_purchase_date'] : $order_date;

                                // Check if this is the current active package
                                $current_package_id = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'candidate_package_id', $user_id);

                                if ($order_package_id == $current_package_id) {
                                    // Use current package activate date (from user meta, same as admin-user-package.php)
                                    $candidate_package_activate = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'candidate_package_activate_date', true);
                                    if ($candidate_package_activate) {
                                        // Format date only (remove time if exists) and use WordPress date format
                                        $active_timestamp = strtotime($candidate_package_activate);
                                        if ($active_timestamp) {
                                            $active_date = date_i18n(get_option('date_format'), $active_timestamp);
                                        }
                                    }

                                    // Calculate expired date using get_expired_date method (same as admin-user-package.php)
                                    $expired_date_result = $jobportal_candidate_package->get_expired_date($order_package_id, $user_id);
                                    if ($expired_date_result && $expired_date_result !== 'Never Expires') {
                                        $expired_timestamp = strtotime($expired_date_result);
                                        if ($expired_timestamp) {
                                            $expired_date = date_i18n(get_option('date_format'), $expired_timestamp);
                                        }
                                    } else {
                                        $expired_date = esc_html__('Never Expires', 'jobportal-framework');
                                    }
                                } else {
                                    // For past packages, use order purchase date (same logic as admin-candidate-order.php)
                                    if ($order_purchase_date) {
                                        // Format date only (remove time if exists) and use WordPress date format
                                        $active_timestamp = strtotime($order_purchase_date);
                                        if ($active_timestamp) {
                                            $active_date = date_i18n(get_option('date_format'), $active_timestamp);
                                        }

                                        // Calculate expired date based on package settings (same as admin-candidate-order.php)
                                        $candidate_package_time_unit = get_post_meta($order_package_id, JOBPORTAL_METABOX_PREFIX . 'candidate_package_time_unit', true);
                                        $candidate_package_period = get_post_meta($order_package_id, JOBPORTAL_METABOX_PREFIX . 'candidate_package_period', true);
                                        $enable_package_service_unlimited_time = get_post_meta($order_package_id, JOBPORTAL_METABOX_PREFIX . 'enable_package_service_unlimited_time', true);

                                        if ($enable_package_service_unlimited_time == 1) {
                                            $expired_date = esc_html__('Never Expires', 'jobportal-framework');
                                        } else {
                                            $activate_timestamp = strtotime($order_purchase_date);
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
                                            if (is_numeric($activate_timestamp) && is_numeric($seconds) && is_numeric($candidate_package_period)) {
                                                $expired_timestamp = $activate_timestamp + ($seconds * $candidate_package_period);
                                                $expired_date = date_i18n(get_option('date_format'), $expired_timestamp);
                                            }
                                        }
                                    }
                                }
                            }
                        ?>
                            <tr>
                                <td>
                                    <?php
                                    $wc_order_number = get_post_meta($order_id, JOBPORTAL_METABOX_PREFIX . 'candidate_order_wc_order_id', true);
                                    $display_id = $wc_order_number ? $wc_order_number : $order_id;
                                    ?>
                                    <span class="package-id">#<?php echo esc_html($display_id); ?></span>
                                </td>
                                <td>
                                    <?php if ($package_name) : ?>
                                        <a href="<?php echo get_permalink($order_package_id); ?>">
                                            <?php echo esc_html($package_name); ?>
                                        </a>
                                    <?php else : ?>
                                        <span class="text-muted"><?php esc_html_e('Package Removed', 'jobportal-framework'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo jobportal_get_format_money($price); ?></td>
                                <td>
                                    <?php
                                    $payment_method_display = JobPortal_Candidate_Order::get_candidate_order_payment_method($payment_method);
                                    if (empty($payment_method_display)) {
                                        $payment_method_display = esc_html__('Unknown', 'jobportal-framework');
                                    }
                                    ?>
                                    <div class="payment-method-label"><?php echo esc_html($payment_method_display); ?></div>
                                    <?php if ($order_date) : ?>
                                        <small class="payment-date-label">
                                            <?php esc_html_e('Create at', 'jobportal-framework'); ?> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($order_date))); ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($order_date))); ?></td>
                                <td>
                                    <?php if ($active_date) : ?>
                                        <?php echo esc_html($active_date); ?>
                                    <?php else : ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($expired_date) : ?>
                                        <?php echo esc_html($expired_date); ?>
                                    <?php else : ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($is_pending) : ?>
                                        <span class="label label-pending"><?php esc_html_e('Pending', 'jobportal-framework') ?></span>
                                    <?php elseif ($is_canceled) : ?>
                                        <span class="label label-close"><?php esc_html_e('Canceled', 'jobportal-framework') ?></span>
                                    <?php elseif ($is_paid) : ?>
                                        <span class="label label-open"><?php esc_html_e('Paid', 'jobportal-framework') ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_orders > 0) : ?>
                <!-- Pagination with items per page selector (same row) -->
                <div class="pagination-dashboard" data-storage-key="candidate_history_per_page">
                    <div class="jobportal-pagination dashboard" data-type="number">
                        <!-- Items per page selector -->
                        <div class="items-pagination" data-max-number="<?php echo esc_attr($total_orders); ?>">
                            <div class="select2-field">
                                <select class="search-control select-pagination" id="history-per-page-select" name="item_amount">
                                    <option value="5" <?php selected($history_per_page, 5); ?>>5</option>
                                    <option value="10" <?php selected($history_per_page, 10); ?>>10</option>
                                    <option value="20" <?php selected($history_per_page, 20); ?>>20</option>
                                </select>
                            </div>
                            <label class="text-pagination">
                                <?php echo sprintf(
                                    __('<span class="num-first">%d</span> - <span class="num-last">%d</span> of <span class="num-total">%d</span> items', 'jobportal-framework'),
                                    $num_first,
                                    $num_last,
                                    $total_orders
                                ); ?>
                            </label>
                        </div>

                        <?php if ($max_pages > 1) : ?>
                            <!-- Page numbers -->
                            <div class="pagination">
                                <?php
                                $current_url = remove_query_arg(array('history_paged', 'history_per_page'));

                                // Previous
                                if ($history_paged > 1) :
                                    $prev_url = add_query_arg(array('history_paged' => $history_paged - 1, 'history_per_page' => $history_per_page), $current_url);
                                ?>
                                    <a class="page-numbers" href="<?php echo esc_url($prev_url); ?>"><i class="fal fa-chevron-left"></i></a>
                                <?php endif; ?>

                                <?php
                                // First page
                                if ($history_paged > 2) :
                                    $first_url = add_query_arg(array('history_paged' => 1, 'history_per_page' => $history_per_page), $current_url);
                                ?>
                                    <a class="page-numbers" href="<?php echo esc_url($first_url); ?>">1</a>
                                    <?php if ($history_paged > 3) : ?>
                                        <span class="page-numbers dots">...</span>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php
                                // Show only: prev, current, next (3 numbers max around current)
                                $start_page = max(1, $history_paged - 1);
                                $end_page = min($max_pages, $history_paged + 1);

                                for ($i = $start_page; $i <= $end_page; $i++) :
                                    $page_url = add_query_arg(array('history_paged' => $i, 'history_per_page' => $history_per_page), $current_url);
                                    if ($i == $history_paged) :
                                ?>
                                        <span class="page-numbers current"><?php echo $i; ?></span>
                                    <?php else : ?>
                                        <a class="page-numbers" href="<?php echo esc_url($page_url); ?>"><?php echo $i; ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php
                                // Last page
                                if ($history_paged < $max_pages - 1) :
                                    if ($history_paged < $max_pages - 2) : ?>
                                        <span class="page-numbers dots">...</span>
                                    <?php endif;
                                    $last_url = add_query_arg(array('history_paged' => $max_pages, 'history_per_page' => $history_per_page), $current_url);
                                    ?>
                                    <a class="page-numbers" href="<?php echo esc_url($last_url); ?>"><?php echo $max_pages; ?></a>
                                <?php endif; ?>

                                <?php
                                // Next
                                if ($history_paged < $max_pages) :
                                    $next_url = add_query_arg(array('history_paged' => $history_paged + 1, 'history_per_page' => $history_per_page), $current_url);
                                ?>
                                    <a class="page-numbers" href="<?php echo esc_url($next_url); ?>"><i class="fal fa-chevron-right"></i></a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php else : ?>
            <p class="item-not-found"><?php esc_html_e('No purchase history found.', 'jobportal-framework') ?></p>
        <?php endif;
        wp_reset_postdata();
        ?>
    </div>

    <?php
    if (isset($total_orders) && $total_orders > 0) {
        wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'user-package-pagination');
    }

    wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'user-package-warning');
    wp_localize_script(JOBPORTAL_PLUGIN_PREFIX . 'user-package-warning', 'jobportal_package_vars', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('jobportal_candidate_package_ajax_nonce')
    ));
    ?>
<?php endif; ?>
