<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $current_user;
$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$paid_submission_type = civi_get_option('paid_submission_type', 'no');
if ($paid_submission_type != 'per_package') {
    echo civi_get_template_html('global/access-denied.php', array('type' => 'free_submit'));
    return;
}

?>
<div class="civi-package-wrap">
    <div class="civi-heading">
        <h2 class="entry-title"><?php esc_html_e('Create a job post', 'civi-framework') ?></h2>
        <div class="choose-package">
            <h4><?php esc_html_e('Choose Package', 'civi-framework') ?></h4>
            <p><?php esc_html_e('Select a package from above and submit job', 'civi-framework') ?></p>
        </div>
    </div>
    <div class="row">
        <?php
        $user_package_id = get_the_author_meta(CIVI_METABOX_PREFIX . 'package_id', $user_id);
        $args = array(
            'post_type' => 'package',
            'posts_per_page' => -1,
            'orderby' => 'meta_value',
            'meta_key' => CIVI_METABOX_PREFIX . 'package_order_display',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => CIVI_METABOX_PREFIX . 'package_visible',
                    'value' => '1',
                    'compare' => '=',
                )
            )
        );
        $data = new WP_Query($args);
        $total_records = $data->found_posts;
        $css_class = 'civi-package-col';
        while ($data->have_posts()) : $data->the_post();
            $package_id = get_the_ID();
            $package_time_unit = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'package_time_unit', true);
            $package_period = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'package_period', true);
            $package_num_job = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'package_number_job', true) ?: 0;
            $package_free = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'package_free', true);
            $used_free_package = get_user_meta($user_id, 'used_free_package', true);
            if ($package_free == 1 && $used_free_package === 'yes') {
                continue;
            }
            if ($package_free == 1) {
                $package_price = 0;
            } else {
                $package_price = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'package_price', true);
            }
            $package_unlimited_job = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'package_unlimited_job', true);
            $package_unlimited_time = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'package_unlimited_time', true);
            $package_featured_job = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'package_unlimited_job_featured', true);
            $package_num_featured_job = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'package_number_featured', true) ?: 0;
            $package_featured = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'package_featured', true);
            $package_additional = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'package_additional_details', true);
            if ($package_additional > 0) {
                $package_additional_text = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'package_details_text', true);
            }

            if ($package_period > 1) {
                $package_time_unit .= 's';
            }
            if ($package_featured == 1) {
                $is_featured = ' active';
            } else {
                $is_featured = '';
            }
            $civi_package = new civi_Package();
            $get_expired_date = $civi_package->get_expired_date($package_id, $user_id);
            $current_date = date('Y-m-d');

            $d1 = strtotime($get_expired_date);
            $d2 = strtotime($current_date);

            if ($get_expired_date === 'Never Expires') {
                $d1 = 999999999999999999999999;
            }

            if ($user_package_id == $package_id && $d1 > $d2) {
                $is_current = 'current';
            } else {
                $is_current = '';
            }
            $payment_link = civi_get_permalink('payment');
            $payment_process_link = add_query_arg('package_id', $package_id, $payment_link);
            $field_package = array('candidate_follow', 'download_cv', 'invite', 'send_message', 'print', 'review_and_commnent', 'info');

            if ($package_unlimited_time == 1) {
                $head_time_unit = esc_html__('never expires', 'civi-framework');
            } else {
                if ($package_period === '1') {
                    $head_time_unit = get_head_time_unit($package_time_unit);
                } elseif ($package_period === '') {
                    $head_time_unit = '';
                } else {
                    $head_time_unit = $package_period . get_head_time_unit($package_time_unit);
                }
            }

        ?>
            <div class="<?php echo esc_attr($css_class); ?>">
                <div class="civi-package-item panel panel-default <?php echo esc_attr($is_current); ?> <?php echo esc_attr($is_featured); ?>">
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="civi-package-thumbnail"><?php the_post_thumbnail(); ?></div>
                    <?php endif; ?>
                    <div class="civi-package-title">
                        <h2 class="entry-title">
                            <?php the_title(); ?>
                        </h2>
                        <?php if ($package_featured == 1) { ?>
                            <span class="recommended"><?php esc_html_e('Recommended', 'civi-framework'); ?></span>
                        <?php } ?>
                    </div>
                    <div class="civi-package-price">
                        <span>
                            <?php
                            if ($package_price > 0) {
                                echo civi_get_format_money($package_price, '', null, true);
                            } else {
                                esc_html_e('Free', 'civi-framework');
                            }
                            ?>
                            <span class="time-unit"><?php echo $head_time_unit; ?></span>
                        </span>
                    </div>
                    <ul class="list-group custom-scrollbar">
                        <?php if ($package_unlimited_job == 1 || $package_num_job > 0) : ?>
                            <li class="list-group-item">
                                <i class="fas fa-check"></i>
                                <?php
                                if ($package_unlimited_job == 1) {
                                    echo esc_html__('Unlimited job postings', 'civi-framework');
                                } else {
                                    echo esc_html(sprintf(
                                        _n('%s job posting', '%s job postings', $package_num_job, 'civi-framework'),
                                        number_format_i18n($package_num_job)
                                    ));
                                }
                                ?>
                            </li>
                        <?php endif; ?>
                        <?php if ($package_featured_job == 1 || $package_num_featured_job > 0) : ?>
                            <li class="list-group-item">
                                <i class="fas fa-check"></i>
                                <?php
                                if ($package_featured_job == 1) {
                                    echo esc_html__('Unlimited featured jobs', 'civi-framework');
                                } else {
                                    echo esc_html(sprintf(
                                        _n('%s featured job', '%s featured jobs', $package_num_featured_job, 'civi-framework'),
                                        number_format_i18n($package_num_featured_job)
                                    ));
                                }
                                ?>
                            </li>
                        <?php endif; ?>
                        <?php foreach ($field_package as $field) :
                            $show_option = civi_get_option('enable_company_package_' . $field);
                            $show_field = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'show_package_company_' . $field, true);
                            $field_unlimited = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'enable_package_' . $field . '_unlimited', true);
                            $field_number = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'company_package_number_' . $field, true) ?: 0;
                            $is_check = true;
                            switch ($field) {
                                case 'candidate_follow':
                                    $name_singular = esc_html__('%s candidate follow', 'civi-framework');
                                    $name_plural = esc_html__('%s candidates follow', 'civi-framework');
                                    $is_check = false;
                                    break;
                                case 'download_cv':
                                    $name_singular = esc_html__('%s CV download', 'civi-framework');
                                    $name_plural = esc_html__('%s CV downloads', 'civi-framework');
                                    $is_check = false;
                                    break;
                                case 'invite':
                                    $name = esc_html__('Invite Candidates', 'civi-framework');
                                    $is_check = true;
                                    break;
                                case 'send_message':
                                    $name = esc_html__('Send Messages', 'civi-framework');
                                    $is_check = true;
                                    break;
                                case 'print':
                                    $name = esc_html__('Print candidate profiles', 'civi-framework');
                                    $is_check = true;
                                    break;
                                case 'review_and_commnent':
                                    $name = esc_html__('Review and comment', 'civi-framework');
                                    $is_check = true;
                                    break;
                                case 'info':
                                    $name = esc_html__('View candidate information', 'civi-framework');
                                    $is_check = true;
                                    break;
                            }
                            if ($show_field == 1 && $show_option == 1 && ($field_unlimited == 1 || $field_number > 0 || $is_check)) :
                        ?>
                                <li class="list-group-item">
                                    <i class="fas fa-check"></i>
                                    <?php if ($is_check == true) { ?>
                                        <span class="badge"><?php esc_html_e($name); ?></span>
                                    <?php } else { ?>
                                        <?php if ($field_unlimited == 1) { ?>
                                            <?php
                                            switch ($field) {
                                                case 'candidate_follow':
                                                    echo esc_html__('Unlimited candidate follow', 'civi-framework');
                                                    break;
                                                case 'download_cv':
                                                    echo esc_html__('Unlimited CV downloads', 'civi-framework');
                                                    break;
                                            }
                                            ?>
                                        <?php } else { ?>
                                            <?php
                                        switch ($field) {
                                            case 'candidate_follow':
                                                echo esc_html(sprintf(_n('%s candidate follow', '%s candidates follow', $field_number, 'civi-framework'), number_format_i18n($field_number)));
                                                break;
                                            case 'download_cv':
                                                echo esc_html(sprintf(_n('%s CV download', '%s CV downloads', $field_number, 'civi-framework'), number_format_i18n($field_number)));
                                                break;
                                        }
                                        ?>
                                        <?php } ?>
                                    <?php } ?>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <?php if ($package_additional > 0 && !empty($package_additional_text)) {
                            foreach ($package_additional_text as $value) {
                                if (!empty($value)) {
                        ?>
                                    <li class="list-group-item">
                                        <i class="fas fa-check"></i>
                                        <span class="badge"><?php esc_html_e($value); ?></span>
                                    </li>
                        <?php
                                }
                            }
                        } ?>
                    </ul>
                    <div class="civi-package-choose">
                        <?php
                        $user_demo = get_the_author_meta(CIVI_METABOX_PREFIX . 'user_demo', $user_id);
                        if ($user_demo == 'yes') { ?>
                            <?php if ($user_package_id == $package_id && $d1 > $d2) { ?>
                                <a href="#" class="civi-button button-block btn-add-to-message"
                                    data-text="<?php echo esc_attr__('This is a "Demo" account, so you can not change it', 'civi-framework'); ?>">
                                    <?php esc_html_e('Package Activated', 'civi-framework'); ?>
                                    <span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
                                </a> <?php } else { ?>
                                <a href="#" class="civi-button button-outline button-block btn-add-to-message"
                                    data-text="<?php echo esc_attr__('This is a "Demo" account, so you can not change it', 'civi-framework'); ?>">
                                    <?php esc_html_e('Get Started', 'civi-framework'); ?>
                                    <span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
                                </a>
                            <?php } ?>
                        <?php } else { ?>
                            <?php if ($user_package_id == $package_id && $d1 > $d2) { ?>

                                <?php
                                // Find the invoice for the CURRENT package of user (with status = 1)
                                $args_invoice = array(
                                    'post_type'           => 'invoice',
                                    'posts_per_page'      => 1,
                                    'meta_query'          => array(
                                        'relation' => 'AND',
                                        array(
                                            'key'     => CIVI_METABOX_PREFIX . 'invoice_user_id',
                                            'value'   => $user_id,
                                            'compare' => '='
                                        ),
                                        array(
                                            'key'     => CIVI_METABOX_PREFIX . 'invoice_item_id',
                                            'value'   => $user_package_id,
                                            'compare' => '='
                                        ),
                                        array(
                                            'key'     => CIVI_METABOX_PREFIX . 'invoice_payment_status',
                                            'value'   => '1',
                                            'compare' => '='
                                        )
                                    ),
                                );
                                $data_invoice = new WP_Query($args_invoice);
                                $invoice_status = 1;
                                if (!empty($data_invoice->post)) {
                                    $invoice_id = $data_invoice->post->ID;
                                    $invoice_status = get_post_meta($invoice_id, CIVI_METABOX_PREFIX . 'invoice_payment_status', true);
                                }

                                if ($invoice_status == 0) { ?>
                                    <a href="<?php echo esc_url($payment_process_link); ?>"
                                        class="civi-button button-block btn-pending">
                                        <?php esc_html_e('Package Pending', 'civi-framework'); ?>
                                        <span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
                                    </a>
                                <?php } else { ?>
                                    <a href="<?php echo esc_url($payment_process_link); ?>"
                                        class="civi-button button-block">
                                        <?php esc_html_e('Package Activated', 'civi-framework'); ?>
                                        <span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
                                    </a>
                                <?php } ?>

                            <?php } else { ?>
                                <a href="<?php echo esc_url($payment_process_link); ?>" class="civi-button button-outline button-block">
                                    <?php esc_html_e('Get Started', 'civi-framework'); ?>
                                    <span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
                                </a>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
        <?php wp_reset_postdata(); ?>
    </div>
</div>

<?php
// Include the package impact warning modal
// Use direct include since get_template_part() looks in theme folder
include(CIVI_PLUGIN_DIR . 'templates/jobs/package/package-impact-modal.php');

// Enqueue button loading utility
include(CIVI_PLUGIN_DIR . 'templates/partials/enqueue-button-loading.php');

// Enqueue package warning JavaScript
wp_enqueue_script(
    'civi-employer-package-warning',
    CIVI_PLUGIN_URL . 'assets/js/employer/employer-package-warning.js',
    array('jquery'),
    time(), // Cache busting
    true
);

// Localize script with i18n strings and AJAX data
wp_localize_script('civi-employer-package-warning', 'civiEmployerPackageWarning', array(
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('civi_package_nonce'),
    'i18n' => array(
        'expires' => esc_html__('Expires', 'civi-framework'),
        'jobs' => esc_html__('Jobs', 'civi-framework'),
        'featured_jobs' => esc_html__('Featured Jobs', 'civi-framework'),
        'candidate_follow' => esc_html__('Candidate Follow', 'civi-framework'),
        'download_cv' => esc_html__('CV Downloads', 'civi-framework'),
        'exceeded' => esc_html__('Exceeded', 'civi-framework'),
        'will_expire' => esc_html__('Will expire', 'civi-framework'),
        'will_unfeature' => esc_html__('Will un-feature', 'civi-framework'),
        'will_unfollow' => esc_html__('Will unfollow', 'civi-framework'),
        'jobs_oldest' => esc_html__('jobs (oldest first)', 'civi-framework'),
        'jobs_newest' => esc_html__('jobs (newest first)', 'civi-framework'),
        'candidates_newest' => esc_html__('candidates (newest first)', 'civi-framework'),
        'remaining' => esc_html__('remaining', 'civi-framework'),
    )
));

?>
