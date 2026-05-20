<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $current_user;
$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$package_id = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'package_id', $user_id);
$package_unlimited_job = get_post_meta($package_id, JOBPORTAL_METABOX_PREFIX . 'package_unlimited_job', true);
$package_unlimited_featured_job = get_post_meta($package_id, JOBPORTAL_METABOX_PREFIX . 'package_unlimited_job_featured', true);
$package_num_job = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'package_number_job', true);
$package_num_featured_job = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'package_number_featured', true);
$package_activate = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'package_activate_date', true);
$package_activate_date = jobportal_convert_date_format($package_activate);
$package_time_unit = get_post_meta($package_id, JOBPORTAL_METABOX_PREFIX . 'package_time_unit', true);
$package_period = get_post_meta($package_id, JOBPORTAL_METABOX_PREFIX . 'package_period', true);
$package_unlimited_time = get_post_meta($package_id, JOBPORTAL_METABOX_PREFIX . 'package_unlimited_time', true);
$package_name = get_the_title($package_id);
$user_info = get_userdata($user_id);
$jobportal_package = new JobPortal_Package();
$expired_date = $jobportal_package->get_expired_date($package_id, $user_id);
$paid_submission_type = jobportal_get_option('paid_submission_type', 'no');

$current_date = date('Y-m-d');
if ($current_date < $expired_date) {
    $seconds = strtotime($expired_date) - strtotime($current_date);
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
    $expired_jobs = $dtF->diff($dtT)->format('%a');
} else {
    $expired_jobs = 0;
}
?>
<?php if ($paid_submission_type !== 'per_package') : ?>
    <p class="notice"><i class="fal fa-exclamation-circle"></i>
        <?php esc_html_e("You are on free submit active", 'jobportal-framework'); ?>
        <?php if (jobportal_get_option('enable_post_type_service') === '1') { ?>
            <a href="<?php echo jobportal_get_permalink('submit_service'); ?>">
                <?php esc_html_e('Add Service', 'jobportal-framework'); ?>
            </a>
        <?php } ?>
    </p>
<?php else : ?>
    <?php if ($package_id && $current_date >= $expired_date) : ?>
        <p class="notice"><i class="fal fa-exclamation-circle"></i>
            <?php esc_html_e("Your package has expired please choose another one", 'jobportal-framework'); ?>
        </p>
    <?php endif; ?>
    <div class="entry-my-page packages-dashboard">
        <div class="entry-title">
            <h4><?php esc_html_e('My packages', 'jobportal-framework') ?></h4>
        </div>
        <?php if ($package_id) : ?>
            <div class="table-dashboard-wapper">
                <table class="table-dashboard <?php if ($current_date >= $expired_date && $paid_submission_type == 'per_package') {
                                                    echo 'expired';
                                                } ?>">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('ID', 'jobportal-framework') ?></th>
                            <th class="col-name"><?php esc_html_e('Package Name', 'jobportal-framework') ?></th>
                            <th><?php esc_html_e('Number Jobs', 'jobportal-framework') ?></th>
                            <th><?php esc_html_e('Number Featured', 'jobportal-framework') ?></th>
                            <th><?php esc_html_e('Job Duration', 'jobportal-framework') ?></th>
                            <th><?php esc_html_e('Status', 'jobportal-framework') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <span class="package-id">
                                    <?php if ($package_id) {
                                        echo "#$package_id";
                                    } ?>
                                </span>
                            </td>
                            <td>
                                <h3><a href="<?php echo jobportal_get_permalink('package') ?>"><?php echo esc_attr($package_name) ?></a></h3>
                                <p><?php echo esc_attr($package_activate_date) ?></p>
                            </td>
                            <td>
                                <span class="limit">
                                    <?php if ($package_unlimited_job == 1) {
                                        esc_html_e('Unlimited', 'jobportal-framework');
                                    } else {
                                        echo $package_num_job;
                                    } ?>
                                </span>
                            </td>
                            <td>
                                <span class="days">
                                    <?php if ($package_unlimited_featured_job == 1) {
                                        esc_html_e('Unlimited', 'jobportal-framework');
                                    } else {
                                        echo $package_num_featured_job;
                                    } ?>
                                </span>
                            </td>
                            <td>
                                <span class="remaining">
                                    <?php if ($package_unlimited_time == 1) {
                                        esc_html_e('never expires', 'jobportal-framework');
                                    } else {
                                        echo sprintf(esc_html__('%s Day', 'jobportal-framework'), $expired_jobs);
                                    } ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                global $current_user;
                                $user_id = $current_user->ID;
                                // Find the invoice for the CURRENT package of user (with status = 1)
                                $args_invoice = array(
                                    'post_type'           => 'invoice',
                                    'posts_per_page'      => 1,
                                    'meta_query'          => array(
                                        'relation' => 'AND',
                                        array(
                                            'key'     => JOBPORTAL_METABOX_PREFIX . 'invoice_user_id',
                                            'value'   => $user_id,
                                            'compare' => '='
                                        ),
                                        array(
                                            'key'     => JOBPORTAL_METABOX_PREFIX . 'invoice_item_id',
                                            'value'   => $package_id,
                                            'compare' => '='
                                        ),
                                        array(
                                            'key'     => JOBPORTAL_METABOX_PREFIX . 'invoice_payment_status',
                                            'value'   => '1',
                                            'compare' => '='
                                        )
                                    ),
                                );
                                $data_invoice = new WP_Query($args_invoice);
                                if (!empty($data_invoice->post)) :
                                    $invoice_id = $data_invoice->post->ID;
                                    $invoice_status = get_post_meta($invoice_id, JOBPORTAL_METABOX_PREFIX . 'invoice_payment_status', true);
                                    if ($invoice_status == 0) { ?>
                                        <span class="label label-pending"><?php esc_html_e('Pending', 'jobportal-framework') ?></span>

                                    <?php } else { ?>
                                        <?php if (($current_date < $expired_date) || ($package_unlimited_time == 1)) { ?>
                                            <span class="label label-open"><?php esc_html_e('Activated', 'jobportal-framework') ?></span>
                                        <?php } else { ?>
                                            <span class="label label-close"><?php esc_html_e('Expired', 'jobportal-framework') ?></span>
                                <?php
                                        }
                                    }
                                endif;
                                ?>
                            </td>
                            <td>
                                <a href="#form-employer-user-package" id="action-employer-user-package">
                                    <?php esc_html_e('Overview', 'jobportal-framework') ?>
                                    <span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <a href="<?php echo jobportal_get_permalink('package'); ?>" class="jobportal-button jobportal-new-package" id="jobportal-new-package-link">
            <i class="far fa-plus"></i><?php esc_html_e('Add new package', 'jobportal-framework') ?>
            <span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
        </a>
    </div>

    <?php
    // Include package warning modal template
    jobportal_get_template('dashboard/employer/package-warning-modal.php');
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
            'post_type'      => 'invoice',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => array(
                array(
                    'key'     => JOBPORTAL_METABOX_PREFIX . 'invoice_user_id',
                    'value'   => $user_id,
                    'compare' => '='
                )
            ),
        );
        $count_query = new WP_Query($args_count);
        $total_invoices = $count_query->found_posts;
        $max_pages = ($history_per_page > 0) ? ceil($total_invoices / $history_per_page) : 1;
        wp_reset_postdata();

        // Ensure current page doesn't exceed max pages
        if ($history_paged > $max_pages && $max_pages > 0) {
            $history_paged = $max_pages;
        }

        // Calculate display range
        $num_first = (($history_paged - 1) * $history_per_page) + 1;
        $num_last = min($history_paged * $history_per_page, $total_invoices);

        // Main query with pagination
        $args_invoices = array(
            'post_type'      => 'invoice',
            'posts_per_page' => $history_per_page,
            'paged'          => $history_paged,
            'meta_query'     => array(
                array(
                    'key'     => JOBPORTAL_METABOX_PREFIX . 'invoice_user_id',
                    'value'   => $user_id,
                    'compare' => '='
                )
            ),
            'orderby'        => 'date',
            'order'          => 'DESC'
        );
        $invoices = new WP_Query($args_invoices);

        // Payment page URL
        $payment_page_url = jobportal_get_permalink('payment');

        if ($invoices->have_posts()) :
        ?>
            <div class="table-dashboard-wapper">
                <table class="table-dashboard">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Invoice #', 'jobportal-framework') ?></th>
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
                        <?php while ($invoices->have_posts()) : $invoices->the_post();
                            $invoice_id = get_the_ID();
                            $invoice_package_id = get_post_meta($invoice_id, JOBPORTAL_METABOX_PREFIX . 'invoice_item_id', true);
                            $price = get_post_meta($invoice_id, JOBPORTAL_METABOX_PREFIX . 'invoice_price', true);
                            $invoice_date = get_post_meta($invoice_id, JOBPORTAL_METABOX_PREFIX . 'invoice_date', true);
                            $status = get_post_meta($invoice_id, JOBPORTAL_METABOX_PREFIX . 'invoice_payment_status', true);
                            $invoice_package_name = get_the_title($invoice_package_id);

                            // Get invoice meta (same as admin files)
                            $jobportal_invoice = new JobPortal_Invoice();
                            $invoice_meta = $jobportal_invoice->get_invoice_meta($invoice_id);
                            $payment_method = isset($invoice_meta['invoice_payment_method']) ? $invoice_meta['invoice_payment_method'] : get_post_meta($invoice_id, JOBPORTAL_METABOX_PREFIX . 'invoice_payment_method', true);

                            // Determine invoice status (not package status)
                            $is_pending = ($status == '0' || $status === '');
                            $is_paid = ($status == '1');
                            $is_canceled = ($status == '-1');

                            // Get active date and expired date
                            $active_date = '';
                            $expired_date = '';

                            if ($is_paid && $invoice_package_id) {
                                // Get invoice purchase date
                                $invoice_purchase_date = isset($invoice_meta['invoice_purchase_date']) ? $invoice_meta['invoice_purchase_date'] : $invoice_date;

                                // Check if this is the current active package
                                $current_package_id = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'package_id', $user_id);

                                if ($invoice_package_id == $current_package_id) {
                                    // Use current package activate date (from user meta, same as admin-user-package.php)
                                    $package_activate = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'package_activate_date', true);
                                    if ($package_activate) {
                                        // Format date only (remove time if exists) and use WordPress date format
                                        $active_timestamp = strtotime($package_activate);
                                        if ($active_timestamp) {
                                            $active_date = date_i18n(get_option('date_format'), $active_timestamp);
                                        }
                                    }

                                    // Calculate expired date using get_expired_date method (same as admin-user-package.php)
                                    $expired_date_result = $jobportal_package->get_expired_date($invoice_package_id, $user_id);
                                    if ($expired_date_result && $expired_date_result !== 'Never Expires') {
                                        $expired_timestamp = strtotime($expired_date_result);
                                        if ($expired_timestamp) {
                                            $expired_date = date_i18n(get_option('date_format'), $expired_timestamp);
                                        }
                                    } else {
                                        $expired_date = esc_html__('Never Expires', 'jobportal-framework');
                                    }
                                } else {
                                    // For past packages, use invoice purchase date (same logic as admin-candidate-order.php)
                                    if ($invoice_purchase_date) {
                                        // Format date only (remove time if exists) and use WordPress date format
                                        $active_timestamp = strtotime($invoice_purchase_date);
                                        if ($active_timestamp) {
                                            $active_date = date_i18n(get_option('date_format'), $active_timestamp);
                                        }

                                        // Calculate expired date based on package settings (same as admin-candidate-order.php)
                                        $package_time_unit = get_post_meta($invoice_package_id, JOBPORTAL_METABOX_PREFIX . 'package_time_unit', true);
                                        $package_period = get_post_meta($invoice_package_id, JOBPORTAL_METABOX_PREFIX . 'package_period', true);
                                        $package_unlimited_time = get_post_meta($invoice_package_id, JOBPORTAL_METABOX_PREFIX . 'package_unlimited_time', true);

                                        if ($package_unlimited_time == 1) {
                                            $expired_date = esc_html__('Never Expires', 'jobportal-framework');
                                        } else {
                                            $activate_timestamp = strtotime($invoice_purchase_date);
                                            $seconds = 0;
                                            switch ($package_time_unit) {
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
                                            if (is_numeric($activate_timestamp) && is_numeric($seconds) && is_numeric($package_period)) {
                                                $expired_timestamp = $activate_timestamp + ($seconds * $package_period);
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
                                    $wc_order_number = get_post_meta($invoice_id, JOBPORTAL_METABOX_PREFIX . 'invoice_wc_order_id', true);
                                    $display_id = $wc_order_number ? $wc_order_number : $invoice_id;
                                    ?>
                                    <span class="package-id">#<?php echo esc_html($display_id); ?></span>
                                </td>
                                <td>
                                    <?php if ($invoice_package_name) : ?>
                                        <a href="<?php echo get_permalink($invoice_package_id); ?>">
                                            <?php echo esc_html($invoice_package_name); ?>
                                        </a>
                                    <?php else : ?>
                                        <span class="text-muted"><?php esc_html_e('Package Removed', 'jobportal-framework'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo jobportal_get_format_money($price); ?></td>
                                <td>
                                    <?php
                                    $payment_method_display = JobPortal_Invoice::get_invoice_payment_method($payment_method);
                                    if (empty($payment_method_display)) {
                                        $payment_method_display = esc_html__('Unknown', 'jobportal-framework');
                                    }
                                    ?>
                                    <div class="payment-method-label"><?php echo esc_html($payment_method_display); ?></div>
                                    <?php if ($invoice_date) : ?>
                                        <small class="payment-date-label">
                                            <?php esc_html_e('Create at', 'jobportal-framework'); ?> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($invoice_date))); ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($invoice_date))); ?></td>
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

            <?php if ($total_invoices > 0) : ?>
                <!-- Pagination with items per page selector (same row) -->
                <div class="pagination-dashboard" data-storage-key="employer_history_per_page">
                    <div class="jobportal-pagination dashboard" data-type="number">
                        <!-- Items per page selector -->
                        <div class="items-pagination" data-max-number="<?php echo esc_attr($total_invoices); ?>">
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
                                    $total_invoices
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
    if (isset($total_invoices) && $total_invoices > 0) {
        wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'user-package-pagination');
    }

    wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'user-package-warning');
    wp_localize_script(JOBPORTAL_PLUGIN_PREFIX . 'user-package-warning', 'jobportal_package_vars', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('jobportal_employer_package_ajax_nonce'),
        'action' => 'jobportal_check_old_employer_package_before_activate'
    ));
    ?>
<?php endif; ?>

<?php
// Add content after user package dashboard
do_action('jobportal_dashboard_after_user_package');
?>
