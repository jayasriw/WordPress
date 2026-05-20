<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!is_user_logged_in()) {
    jobportal_get_template('global/access-denied.php', array('type' => 'not_login'));
    return;
}
global $current_user;
$user_id = apply_filters('jobportal_modify_user_id', $current_user->ID);
$id = get_the_ID();
$jobs_id = isset($_GET['jobs_id']) ? jobportal_clean(wp_unslash($_GET['jobs_id'])) : '';
$pages = isset($_GET['pages']) ? jobportal_clean(wp_unslash($_GET['pages'])) : '';
$current_date = date('Y-m-d');
$jobportal_package = new JobPortal_Package();
$package_id = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'package_id', $user_id);
$expired_date = $jobportal_package->get_expired_date($package_id, $user_id);
$paid_submission_type = jobportal_get_option('paid_submission_type', 'no');
$extend_expired_jobs = jobportal_get_option('enable_extend_expired_jobs');
$verify_author = false;
// Check author of $jobs_id is current user login
if (!empty($jobs_id)) {
    $post = get_post($jobs_id);
    if ($post->post_author == $user_id) {
        $verify_author = true;
    }
}

if (!empty($jobs_id) && $pages == 'edit' && $verify_author) {
    jobportal_get_template('jobs/edit.php');
} else if (!empty($jobs_id) && $pages == 'performance' && $verify_author) {
    jobportal_get_template('dashboard/employer/jobs-performance.php');
} else {
    $posts_per_page = 10;
    wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'jobs-dashboard');
    wp_localize_script(
        JOBPORTAL_PLUGIN_PREFIX . 'jobs-dashboard',
        'jobportal_jobs_dashboard_vars',
        array(
            'ajax_url'    => JOBPORTAL_AJAX_URL,
            'not_jobs'   => esc_html__('No jobs found', 'jobportal-framework'),
        )
    );
    $jobs_classes = array('jobportal-jobs', 'grid', 'columns-4');
    $tax_query = $meta_query = array();
    global $current_user;
    wp_get_current_user();
    $user_id = $current_user->ID;
    $jobportal_profile = new JobPortal_Profile();

    $args_ex = array(
        'post_type'           => 'jobs',
        'post_status'         => 'publish',
        'ignore_sticky_posts' => 1,
        'posts_per_page'      => -1,
        'author'              => $user_id,
    );
    $args_ex = apply_filters('jobportal_modify_author_query_args', $args_ex);
    $data_ex = new WP_Query($args_ex);
    $id_ex = array();
    if ($data_ex->have_posts()) {
        while ($data_ex->have_posts()) : $data_ex->the_post();
            $id_ex[] = get_the_ID();
        endwhile;
    }

    if (!empty($id_ex) && $paid_submission_type == 'per_package') {
        if ($current_date >= $expired_date) {
            foreach ($id_ex as $value) {
                update_post_meta($value, JOBPORTAL_METABOX_PREFIX . 'enable_jobs_package_expires', 1);
            }
        } else {
            foreach ($id_ex as $value) {
                update_post_meta($value, JOBPORTAL_METABOX_PREFIX . 'enable_jobs_package_expires', 0);
            }
        }
    }

    $args = array(
        'post_type'           => 'jobs',
        'post_status'         => array('publish', 'expired', 'pending', 'pause'),
        'ignore_sticky_posts' => 1,
        'posts_per_page'      => $posts_per_page,
        'offset'              => (max(1, get_query_var('paged')) - 1) * $posts_per_page,
        'author'              => $user_id,
        'orderby'               => 'date',
    );
    $args = apply_filters('jobportal_modify_author_query_args', $args);
    $data = new WP_Query($args);

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
    $invoice_id = ($data_invoice->have_posts() && $data_invoice->post) ? $data_invoice->post->ID : 0;
    $invoice_status = ($invoice_id > 0) ? get_post_meta($invoice_id, JOBPORTAL_METABOX_PREFIX . 'invoice_payment_status', true) : '';
?>
    <?php
    if ($paid_submission_type == 'per_package') {
        if (empty($package_id)) {
    ?>
            <p class="notice">
                <i class="fal fa-exclamation-circle"></i>
                <?php esc_html_e("No package found. Please buy a package to continue.", 'jobportal-framework'); ?>
                <a href="<?php echo jobportal_get_permalink('package'); ?>">
                    <?php esc_html_e('Buy Package', 'jobportal-framework'); ?>
                </a>
            </p>
        <?php
        } elseif ($current_date >= $expired_date) {
        ?>
            <p class="notice">
                <i class="fal fa-exclamation-circle"></i>
                <?php esc_html_e("Package expired. Please select a new one.", 'jobportal-framework'); ?>
                <a href="<?php echo jobportal_get_permalink('package'); ?>">
                    <?php esc_html_e('Add Package', 'jobportal-framework'); ?>
                </a>
            </p>
        <?php
        } elseif ($invoice_status == '0') {
        ?>
            <p class="notice">
                <i class="fal fa-exclamation-circle"></i>
                <?php esc_html_e("Your package is not activated yet. Please complete the payment to start using it.", 'jobportal-framework'); ?>
                <a href="<?php echo jobportal_get_permalink('package'); ?>">
                    <?php esc_html_e('Pay Now', 'jobportal-framework'); ?>
                </a>
            </p>
    <?php
        }
    }
    ?>

    <div class="entry-my-page jobs-dashboard <?php if ($current_date >= $expired_date && $paid_submission_type == 'per_package') {
                                                    echo 'expired';
                                                } ?>">
        <div class="entry-title">
            <h4><?php esc_html_e('Manage jobs', 'jobportal-framework') ?></h4>
        </div>
        <div class="search-dashboard-wrapper">
            <div class="search-left">
                <div class="select2-field">
                    <select class="search-control jobportal-select2" name="jobs_status">
                        <option value=""><?php esc_html_e('All jobs', 'jobportal-framework') ?></option>
                        <option value="publish"><?php esc_html_e('Opening', 'jobportal-framework') ?></option>
                        <option value="pause"><?php esc_html_e('Paused', 'jobportal-framework') ?></option>
                        <option value="expired"><?php esc_html_e('Closed', 'jobportal-framework') ?></option>
                        <option value="pending"><?php esc_html_e('Pending', 'jobportal-framework') ?></option>
                    </select>
                </div>
                <div class="action-search">
                    <input class="jobs-search-control" type="text" name="jobs_search" placeholder="<?php esc_attr_e('Search jobs title', 'jobportal-framework') ?>">
                    <button class="btn-search">
                        <i class="far fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="search-right">
                <label class="text-sorting"><?php esc_html_e('Sort by', 'jobportal-framework') ?></label>
                <div class="select2-field">
                    <select class="search-control action-sorting jobportal-select2" name="jobs_sort_by">
                        <option value="newest"><?php esc_html_e('Newest', 'jobportal-framework') ?></option>
                        <option value="oldest"><?php esc_html_e('Oldest', 'jobportal-framework') ?></option>
                        <option value="featured"><?php esc_html_e('Featured', 'jobportal-framework') ?></option>
                    </select>
                </div>
            </div>
        </div>
        <?php if ($data->have_posts()) { ?>
            <div class="table-dashboard-wapper">
                <table class="table-dashboard" id="my-jobs">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('TITLE', 'jobportal-framework') ?></th>
                            <th><?php esc_html_e('APPLICANTS', 'jobportal-framework') ?></th>
                            <th><?php esc_html_e('STATUS', 'jobportal-framework') ?></th>
                            <th><?php esc_html_e('POSTED', 'jobportal-framework') ?></th>
                            <th><?php esc_html_e('EXPIRED', 'jobportal-framework') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $ids = $jobs_expires = array(); ?>
                        <?php while ($data->have_posts()) : $data->the_post(); ?>
                            <?php
                            $id = get_the_ID();
                            $ids[] = $id;
                            global $current_user;
                            wp_get_current_user();
                            $user_id = $current_user->ID;
                            $status = get_post_status($id);
                            $jobs_type = get_the_terms($id, 'jobs-type');
                            $jobs_categories =  get_the_terms($id, 'jobs-categories');
                            $jobs_location =  get_the_terms($id, 'jobs-location');
                            $public_date = get_the_date('Y-m-d');
                            $current_date = date('Y-m-d');
                            $jobs_days_single = get_post_meta($id, JOBPORTAL_METABOX_PREFIX . 'jobs_days_closing', true);
                            $enable_jobs_expires = get_post_meta($id, JOBPORTAL_METABOX_PREFIX . 'enable_jobs_expires', true);
                            if ($enable_jobs_expires == '1') {
                                $jobs_days_closing   = '0';
                            } else {
                                if ($jobs_days_single) {
                                    $jobs_days_closing = $jobs_days_single;
                                } else {
                                    $jobs_days_closing   = jobportal_get_option('jobs_number_days', true);
                                }
                            }

                            $expiration_date = date('Y-m-d', strtotime($public_date . '+' . intval($jobs_days_closing) . ' days'));
                            $jobs_featured    = get_post_meta($id, JOBPORTAL_METABOX_PREFIX . 'jobs_featured', true);

                            $val_expiration_date = date_i18n(get_option('date_format'), strtotime($public_date . '+' . intval($jobs_days_closing) . ' days'));
                            $val_public_date = get_the_date(get_option('date_format'));
                            ?>
                            <tr>
                                <td>
                                    <h3 class="title-jobs-dashboard">
                                        <a href="<?php echo jobportal_get_permalink('jobs_dashboard') ?>?pages=performance&tab=statics&jobs_id=<?php echo esc_attr($id); ?>">
                                            <?php echo jobportal_get_icon_status($id); ?>
                                            <?php echo get_the_title($id); ?>
                                        </a>
                                    </h3>
                                    <p>
                                        <?php if (is_array($jobs_categories)) {
                                            foreach ($jobs_categories as $categories) { ?>
                                                <?php esc_html_e($categories->name); ?>
                                        <?php }
                                        } ?>
                                        <?php if (is_array($jobs_type)) {
                                            foreach ($jobs_type as $type) { ?>
                                                <?php esc_html_e('/ ' . $type->name); ?>
                                        <?php }
                                        } ?>
                                        <?php if (is_array($jobs_location)) {
                                            foreach ($jobs_location as $location) { ?>
                                                <?php esc_html_e('/ ' . $location->name); ?>
                                        <?php }
                                        } ?>
                                    </p>
                                </td>
                                <td>
                                    <div class="number-applicant">
                                        <span class="number"><?php echo jobportal_total_applications_jobs_id($id); ?></span>
                                        <?php if (jobportal_total_applications_jobs_id($id) > 1) { ?>
                                            <a href="<?php echo jobportal_get_permalink('jobs_dashboard') ?>?pages=performance&tab=applicants&jobs_id=<?php echo esc_attr($id); ?>"><?php esc_html_e('Applicants', 'jobportal-framework') ?></a>
                                        <?php } else { ?>
                                            <a href="<?php echo jobportal_get_permalink('jobs_dashboard') ?>?pages=performance&tab=applicants&jobs_id=<?php echo esc_attr($id); ?>"><?php esc_html_e('Application', 'jobportal-framework') ?></a>
                                        <?php } ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($enable_jobs_expires == '1' || $status == 'expired') : ?>
                                        <span class="label label-close"><?php esc_html_e('Closed', 'jobportal-framework') ?></span>
                                    <?php endif; ?>
                                    <?php if ($status == 'publish' && $enable_jobs_expires != '1') : ?>
                                        <span class="label label-open"><?php esc_html_e('Opening', 'jobportal-framework') ?></span>
                                    <?php endif; ?>
                                    <?php if ($status == 'pending') : ?>
                                        <span class="label label-pending"><?php esc_html_e('Pending', 'jobportal-framework') ?></span>
                                    <?php endif; ?>
                                    <?php if ($status == 'pause') : ?>
                                        <span class="label label-pause"><?php esc_html_e('Pause', 'jobportal-framework') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="start-time"><?php echo $val_public_date ?></span>
                                </td>
                                <td>
                                    <span class="expires-time">
                                        <?php if ($expiration_date > $public_date && $expiration_date > $current_date) : ?>
                                            <?php echo $val_expiration_date ?>
                                        <?php else : ?>
                                            <span><?php esc_html_e('Expired', 'jobportal-framework') ?></span>
                                        <?php endif ?>
                                    </span>
                                </td>
                                <?php
                                ?>
                                <td class="action-setting jobs-control">
                                    <?php
                                    $jobs_dashboard_link = jobportal_get_permalink('jobs_dashboard');
                                    $paid_submission_type = jobportal_get_option('paid_submission_type', 'no');
                                    $check_package = $jobportal_profile->user_package_available($user_id);
                                    $package_num_featured_job = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'package_number_featured', $user_id);
                                    $package_unlimited_featured_job = get_post_meta($package_id, JOBPORTAL_METABOX_PREFIX . 'package_unlimited_job_featured', true);
                                    $user_demo = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'user_demo', $user_id);
                                    if ($check_package == -1 && $paid_submission_type == 'per_package') : ?>
                                        <a href="#" class="icon-setting btn-add-to-message" data-text="<?php echo esc_attr__('Your package has expired. Please buy a new one.', 'jobportal-framework'); ?>">
                                            <i class="fal fa-ellipsis-h"></i>
                                        </a>
                                    <?php elseif ($invoice_status == '0' && $paid_submission_type == 'per_package') : ?>
                                        <a href="#" class="icon-setting btn-add-to-message" data-text="<?php echo esc_attr__('Your package is not active. Please complete payment.', 'jobportal-framework'); ?>">
                                            <i class="fal fa-ellipsis-h"></i>
                                        </a>
                                    <?php elseif ($status !== 'expired') : ?>
                                        <a href="#" class="icon-setting"><i class="fal fa-ellipsis-h"></i></a>
                                        <ul class="action-dropdown">
                                            <?php
                                            switch ($status) {
                                                case 'publish':
                                                    if ($paid_submission_type == 'per_package') {

                                                        if ($check_package != -1 && $check_package != 0) { ?>
                                                            <li><a class="btn-edit" href="<?php echo esc_url($jobs_dashboard_link); ?><?php echo strpos(esc_url($jobs_dashboard_link), '?') ? '&' : '?' ?>pages=edit&jobs_id=<?php echo esc_attr($id); ?>"><?php esc_html_e('Edit', 'jobportal-framework'); ?></a></li>
                                                        <?php }

                                                        if ($user_demo == 'yes') { ?>

                                                            <li><a class="btn-add-to-message" href="#" data-text="<?php echo esc_attr__('This is a "Demo" account, so you can not change it', 'jobportal-framework'); ?>"><?php esc_html_e('Paused', 'jobportal-framework'); ?></a></li>
                                                            <?php if ($jobs_featured != 1) { ?>
                                                                <li><a class="btn-add-to-message" href="#" data-text="<?php echo esc_attr__('This is a "Demo" account, so you can not change it', 'jobportal-framework'); ?>"><?php esc_html_e('Mark featured', 'jobportal-framework'); ?></a></li>
                                                            <?php } ?>
                                                            <li><a class="btn-add-to-message" href="#" data-text="<?php echo esc_attr__('This is a "Demo" account, so you can not change it', 'jobportal-framework'); ?>"><?php esc_html_e('Mark Filled', 'jobportal-framework'); ?></a></li>

                                                            <?php } else {

                                                            if ($check_package != -1 && $check_package != 0) { ?>
                                                                <li><a class="btn-pause" jobs-id="<?php echo esc_attr($id); ?>" href="<?php echo get_the_permalink($id); ?>"><?php esc_html_e('Paused', 'jobportal-framework') ?></a></li>
                                                            <?php }

                                                            if (($package_unlimited_featured_job == '1' || $package_num_featured_job > 0) && $jobs_featured != 1 && $check_package != -1 && $check_package != 0) { ?>
                                                                <li><a class="btn-mark-featured" jobs-id="<?php echo esc_attr($id); ?>" href="<?php echo get_the_permalink($id); ?>"><?php esc_html_e('Mark featured', 'jobportal-framework') ?></a></li>
                                                            <?php }

                                                            if ($check_package != -1 && $check_package != 0) { ?>
                                                                <li><a class="btn-mark-filled" jobs-id="<?php echo esc_attr($id); ?>" href="<?php echo get_the_permalink($id); ?>"><?php esc_html_e('Mark Filled', 'jobportal-framework') ?></a></li>
                                                            <?php }
                                                        }

                                                        // Always show View detail regardless of package status
                                                        ?>
                                                        <li><a href="<?php echo get_the_permalink($id); ?>"><?php esc_html_e('View detail', 'jobportal-framework') ?></a></li>
                                                        <?php
                                                    } else { ?>

                                                        <li><a class="btn-edit" href="<?php echo esc_url($jobs_dashboard_link); ?>?pages=edit&jobs_id=<?php echo esc_attr($id); ?>"><?php esc_html_e('Edit', 'jobportal-framework'); ?></a></li>

                                                        <?php if ($user_demo == 'yes') { ?>
                                                            <li><a class="btn-add-to-message" href="#" data-text="<?php echo esc_attr__('This is a "Demo" account, so you can not change it', 'jobportal-framework'); ?>"><?php esc_html_e('Paused', 'jobportal-framework'); ?></a></li>
                                                            <?php if ($jobs_featured != 1) { ?>
                                                                <li><a class="btn-add-to-message" href="#" data-text="<?php echo esc_attr__('This is a "Demo" account, so you can not change it', 'jobportal-framework'); ?>"><?php esc_html_e('Mark featured', 'jobportal-framework'); ?></a></li>
                                                            <?php } ?>
                                                            <li><a class="btn-add-to-message" href="#" data-text="<?php echo esc_attr__('This is a "Demo" account, so you can not change it', 'jobportal-framework'); ?>"><?php esc_html_e('Mark Filled', 'jobportal-framework'); ?></a></li>
                                                        <?php } else { ?>
                                                            <li><a class="btn-pause" jobs-id="<?php echo esc_attr($id); ?>" href="<?php echo get_the_permalink($id); ?>"><?php esc_html_e('Paused', 'jobportal-framework') ?></a></li>
                                                            <?php if ($jobs_featured != 1) { ?>
                                                                <li><a class="btn-mark-featured" jobs-id="<?php echo esc_attr($id); ?>" href="<?php echo get_the_permalink($id); ?>"><?php esc_html_e('Mark featured', 'jobportal-framework') ?></a></li>
                                                            <?php } ?>
                                                            <li><a class="btn-mark-filled" jobs-id="<?php echo esc_attr($id); ?>" href="<?php echo get_the_permalink($id); ?>"><?php esc_html_e('Mark Filled', 'jobportal-framework') ?></a></li>
                                                        <?php } ?>

                                                        <li><a href="<?php echo get_the_permalink($id); ?>"><?php esc_html_e('View detail', 'jobportal-framework') ?></a></li>
                                                    <?php }
                                                    break;
                                                case 'pending': ?>
                                                    <li><a class="btn-edit" href="<?php echo esc_url($jobs_dashboard_link); ?>?pages=edit&jobs_id=<?php echo esc_attr($id); ?>"><?php esc_html_e('Edit', 'jobportal-framework'); ?></a></li>
                                                <?php
                                                    break;
                                                case 'pause':
                                                ?>
                                                    <li><a class="btn-edit" href="<?php echo esc_url($jobs_dashboard_link); ?>?pages=edit&jobs_id=<?php echo esc_attr($id); ?>"><?php esc_html_e('Edit', 'jobportal-framework'); ?></a></li>
                                                    <li><a class="btn-show" jobs-id="<?php echo esc_attr($id); ?>" href="<?php echo get_the_permalink($id); ?>"><?php esc_html_e('Continue', 'jobportal-framework'); ?></a>
                                                <?php
                                            } ?>
                                        </ul>
                                    <?php else : ?>
                                        <?php if ($extend_expired_jobs == 1) : ?>
                                            <a href="#" class="icon-setting"><i class="fal fa-ellipsis-h"></i></a>
                                            <ul class="action-dropdown">
                                                <li><a class="btn-extend" jobs-id="<?php echo esc_attr($id); ?>"><?php esc_html_e('Extend', 'jobportal-framework'); ?></a></li>
                                            </ul>
                                        <?php else: ?>
                                            <a href="#" class="icon-setting btn-add-to-message" data-text="<?php echo esc_attr__('Jobs has expired so you can not change it', 'jobportal-framework'); ?>"><i class="fal fa-ellipsis-h"></i></a>
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <div class="jobportal-loading-effect"><span class="jobportal-dual-ring"></span></div>
            </div>
        <?php } else { ?>
            <div class="item-not-found"><?php esc_html_e('No item found', 'jobportal-framework'); ?></div>
        <?php } ?>
        <?php $max_num_pages = $data->max_num_pages;
        $total_post = $data->found_posts;
        if ($total_post > $posts_per_page) { ?>
            <div class="pagination-dashboard">
                <?php jobportal_get_template('global/pagination.php', array('total_post' => $total_post, 'max_num_pages' => $max_num_pages, 'type' => 'dashboard', 'layout' => 'number'));
                wp_reset_postdata(); ?>
            </div>
        <?php } ?>
    </div>
<?php } ?>
