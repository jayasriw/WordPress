<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $current_user;
$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$candidate_paid_submission_type = jobportal_get_option('candidate_paid_submission_type');
if ($candidate_paid_submission_type !== 'candidate_per_package') {
    echo jobportal_get_template_html('global/access-denied.php', array('type' => 'free_submit'));
    return;
}

?>
<div class="jobportal-package-wrap">
    <div class="jobportal-heading">
        <h2 class="entry-title"><?php esc_html_e('Create a package candidate', 'jobportal-framework') ?></h2>
        <div class="choose-package">
            <h4><?php esc_html_e('Choose Package', 'jobportal-framework') ?></h4>
            <p><?php esc_html_e('Select a candidate package from above and submit candidate', 'jobportal-framework') ?></p>
        </div>
    </div>
    <div class="row">
        <?php
        $user_candidate_package_id = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'candidate_package_id', $user_id);
        $args = array(
            'post_type' => 'candidate_package',
            'posts_per_page' => -1,
            'orderby' => 'meta_value',
            'meta_key' => JOBPORTAL_METABOX_PREFIX . 'candidate_package_order_display',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => JOBPORTAL_METABOX_PREFIX . 'candidate_package_visible',
                    'value' => '1',
                    'compare' => '=',
                )
            )
        );
        $data = new WP_Query($args);
        $total_records = $data->found_posts;
        $css_class = 'jobportal-package-col';
        while ($data->have_posts()) : $data->the_post();
            $candidate_package_id = get_the_ID();
            $candidate_package_time_unit = get_post_meta($candidate_package_id, JOBPORTAL_METABOX_PREFIX . 'candidate_package_time_unit', true);
            $candidate_package_period = get_post_meta($candidate_package_id, JOBPORTAL_METABOX_PREFIX . 'candidate_package_period', true);
            $candidate_package_number_service = get_post_meta($candidate_package_id, JOBPORTAL_METABOX_PREFIX . 'candidate_package_number_service', true) ?: 0;
            $candidate_package_free = get_post_meta($candidate_package_id, JOBPORTAL_METABOX_PREFIX . 'candidate_package_free', true);
            if ($candidate_package_free == 1) {
                $candidate_package_price = 0;
            } else {
                $candidate_package_price = get_post_meta($candidate_package_id, JOBPORTAL_METABOX_PREFIX . 'candidate_package_price', true);
            }
            $enable_package_service_unlimited = get_post_meta($candidate_package_id, JOBPORTAL_METABOX_PREFIX . 'enable_package_service_unlimited', true);
            $enable_package_service_unlimited_time = get_post_meta($candidate_package_id, JOBPORTAL_METABOX_PREFIX . 'enable_package_service_unlimited_time', true);
            $candidate_package_featured_candidate = get_post_meta($candidate_package_id, JOBPORTAL_METABOX_PREFIX . 'enable_package_service_featured_unlimited', true);
            $candidate_package_number_service_featured = get_post_meta($candidate_package_id, JOBPORTAL_METABOX_PREFIX . 'candidate_package_number_service_featured', true) ?: 0;
            $candidate_package_featured = get_post_meta($candidate_package_id, JOBPORTAL_METABOX_PREFIX . 'candidate_package_featured', true);
            $candidate_package_additional = get_post_meta($candidate_package_id, JOBPORTAL_METABOX_PREFIX . 'candidate_package_additional_details', true);
            if ($candidate_package_additional > 0) {
                $candidate_package_additional_text = get_post_meta($candidate_package_id, JOBPORTAL_METABOX_PREFIX . 'candidate_package_details_text', true);
            }

            if ($candidate_package_period > 1) {
                $candidate_package_time_unit .= 's';
            }
            if ($candidate_package_featured == 1) {
                $is_featured = ' active';
            } else {
                $is_featured = '';
            }
            $jobportal_candidate_package = new jobportal_candidate_package();
            $get_expired_date = $jobportal_candidate_package->get_expired_date($candidate_package_id, $user_id);
            $current_date = date('Y-m-d');

            $d1 = strtotime($get_expired_date);
            $d2 = strtotime($current_date);

            if ($get_expired_date === 'Never Expires') {
                $d1 = 999999999999999999999999;
            }

            if ($user_candidate_package_id == $candidate_package_id && $d1 > $d2) {
                $is_current = 'current';
            } else {
                $is_current = '';
            }
            $payment_link = jobportal_get_permalink('candidate_payment');
            $payment_process_link = add_query_arg('candidate_package_id', $candidate_package_id, $payment_link);
            $field_package = array('jobs_apply', 'jobs_wishlist', 'company_follow', 'contact_company', 'info_company', 'send_message', 'review_and_commnent');

            if ($enable_package_service_unlimited_time == 1) {
                $head_time_unit = esc_html__('never expires', 'jobportal-framework');
            } else {
                if ($candidate_package_period === '1') {
                    $head_time_unit = get_head_time_unit($candidate_package_time_unit);
                } elseif ($candidate_package_period === '') {
                    $head_time_unit = '';
                } else {
                    $head_time_unit = $candidate_package_period . get_head_time_unit($candidate_package_time_unit);
                }
            }

        ?>
            <div class="<?php echo esc_attr($css_class); ?>">
                <div class="jobportal-package-item panel panel-default <?php echo esc_attr($is_current); ?> <?php echo esc_attr($is_featured); ?>">
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="jobportal-package-thumbnail"><?php the_post_thumbnail(); ?></div>
                    <?php endif; ?>
                    <div class="jobportal-package-title">
                        <h2 class="entry-title"><?php the_title(); ?></h2>
                        <?php if ($candidate_package_featured == 1) { ?>
                            <span class="recommended"><?php esc_html_e('Recommended', 'jobportal-framework'); ?></span>
                        <?php } ?>
                    </div>
                    <div class="jobportal-package-price">
                        <?php
                        if ($candidate_package_price > 0) {
                            echo jobportal_get_format_money($candidate_package_price, '', null, true);
                        } else {
                            esc_html_e('Free', 'jobportal-framework');
                        }
                        ?>
                        <span class="time-unit"><?php echo $head_time_unit; ?></span>
                    </div>
                    <ul class="list-group custom-scrollbar">
                        <?php if (jobportal_get_option('enable_post_type_service') === '1') { ?>
                            <?php if ($enable_package_service_unlimited == 1 || $candidate_package_number_service > 0) : ?>
                                <li class="list-group-item">
                                    <i class="fas fa-check"></i>
                                    <?php
                                    if ($enable_package_service_unlimited == 1) {
                                        echo esc_html__('Unlimited service postings', 'jobportal-framework');
                                    } else {
                                        echo esc_html(sprintf(
                                            _n('%s service posting', '%s service postings', $candidate_package_number_service, 'jobportal-framework'),
                                            number_format_i18n($candidate_package_number_service)
                                        ));
                                    }
                                    ?>
                                </li>
                            <?php endif; ?>
                            <?php if ($candidate_package_featured_candidate == 1 || $candidate_package_number_service_featured > 0) : ?>
                                <li class="list-group-item">
                                    <i class="fas fa-check"></i>
                                    <?php
                                    if ($candidate_package_featured_candidate == 1) {
                                        echo esc_html__('Unlimited featured services', 'jobportal-framework');
                                    } else {
                                        echo esc_html(sprintf(
                                            _n('%s featured service', '%s featured services', $candidate_package_number_service_featured, 'jobportal-framework'),
                                            number_format_i18n($candidate_package_number_service_featured)
                                        ));
                                    }
                                    ?>
                                </li>
                            <?php endif; ?>
                        <?php } ?>
                        <?php foreach ($field_package as $field) :
                            $show_field = get_post_meta($candidate_package_id, JOBPORTAL_METABOX_PREFIX . 'show_package_' . $field, true);
                            $field_number = get_post_meta($candidate_package_id, JOBPORTAL_METABOX_PREFIX . 'candidate_package_number_' . $field, true) ?: 0;
                            $field_unlimited = get_post_meta($candidate_package_id, JOBPORTAL_METABOX_PREFIX . 'enable_package_' . $field . '_unlimited', true);
                            $is_check = false;
                            switch ($field) {
                                case 'jobs_apply':
                                    $name_singular = esc_html__('Apply for %s job', 'jobportal-framework');
                                    $name_plural = esc_html__('Apply for %s jobs', 'jobportal-framework');
                                    $is_check = false;
                                    break;
                                case 'jobs_wishlist':
                                    $name_singular = esc_html__('Wishlist %s job', 'jobportal-framework');
                                    $name_plural = esc_html__('Wishlist %s jobs', 'jobportal-framework');
                                    $is_check = false;
                                    break;
                                case 'company_follow':
                                    $name_singular = esc_html__('Follow %s company', 'jobportal-framework');
                                    $name_plural = esc_html__('Follow %s companies', 'jobportal-framework');
                                    $is_check = false;
                                    break;
                                case 'contact_company':
                                    $name = esc_html__('View company in jobs', 'jobportal-framework');
                                    $is_check = true;
                                    break;
                                case 'info_company':
                                    $name = esc_html__('View company information', 'jobportal-framework');
                                    $is_check = true;
                                    break;
                                case 'send_message':
                                    $name_singular = esc_html__('Send %s message', 'jobportal-framework');
                                    $name_plural = esc_html__('Send %s messages', 'jobportal-framework');
                                    $is_check = false;
                                    break;
                                case 'review_and_commnent':
                                    $name_singular = esc_html__('Write %s review/comment', 'jobportal-framework');
                                    $name_plural = esc_html__('Write %s reviews/comments', 'jobportal-framework');
                                    $is_check = false;
                                    break;
                            }
                            if (intval($show_field) == 1 && ($field_unlimited == 1 || $field_number > 0 || $is_check)) : ?>
                                <li class="list-group-item">
                                    <i class="fas fa-check"></i>
                                    <?php if ($is_check == true) { ?>
                                        <span class="badge"><?php esc_html_e($name); ?></span>
                                    <?php } else { ?>
                                        <?php if ($field_unlimited == 1) { ?>
                                            <?php
                                            switch ($field) {
                                                case 'jobs_apply':
                                                    echo esc_html__('Unlimited job applications', 'jobportal-framework');
                                                    break;
                                                case 'jobs_wishlist':
                                                    echo esc_html__('Unlimited job wishlist', 'jobportal-framework');
                                                    break;
                                                case 'company_follow':
                                                    echo esc_html__('Unlimited company follow', 'jobportal-framework');
                                                    break;
                                                case 'send_message':
                                                    echo esc_html__('Unlimited messages', 'jobportal-framework');
                                                    break;
                                                case 'review_and_commnent':
                                                    echo esc_html__('Unlimited reviews/comments', 'jobportal-framework');
                                                    break;
                                            }
                                            ?>
                                        <?php } else { ?>
                                            <?php
                                        switch ($field) {
                                            case 'jobs_apply':
                                                echo esc_html(sprintf(_n('Apply for %s job', 'Apply for %s jobs', $field_number, 'jobportal-framework'), number_format_i18n($field_number)));
                                                break;
                                            case 'jobs_wishlist':
                                                echo esc_html(sprintf(_n('Wishlist %s job', 'Wishlist %s jobs', $field_number, 'jobportal-framework'), number_format_i18n($field_number)));
                                                break;
                                            case 'company_follow':
                                                echo esc_html(sprintf(_n('Follow %s company', 'Follow %s companies', $field_number, 'jobportal-framework'), number_format_i18n($field_number)));
                                                break;
                                            case 'send_message':
                                                echo esc_html(sprintf(_n('Send %s message', 'Send %s messages', $field_number, 'jobportal-framework'), number_format_i18n($field_number)));
                                                break;
                                            case 'review_and_commnent':
                                                echo esc_html(sprintf(_n('Write %s review/comment', 'Write %s reviews/comments', $field_number, 'jobportal-framework'), number_format_i18n($field_number)));
                                                break;
                                        }
                                        ?>
                                        <?php } ?>
                                    <?php } ?>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <?php if ($candidate_package_additional > 0 && !empty($candidate_package_additional_text)) {
                            foreach ($candidate_package_additional_text as $value) {
                                if (!empty($value)) : ?>
                                    <li class="list-group-item">
                                        <i class="fas fa-check"></i>
                                        <span class="badge"><?php esc_html_e($value); ?></span>
                                    </li>
                                <?php endif; ?>
                        <?php }
                        } ?>
                    </ul>
                    <div class="jobportal-package-choose">
                        <?php
                        $user_demo = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'user_demo', $user_id);
                        if ($user_demo == 'yes') { ?>
                            <?php if ($user_candidate_package_id == $candidate_package_id && $d1 > $d2) { ?>
                                <a href="#" class="jobportal-button button-block btn-add-to-message"
                                    data-text="<?php echo esc_attr__('This is a "Demo" account, so you can not change it', 'jobportal-framework'); ?>">
                                    <?php esc_html_e('Package Activated', 'jobportal-framework'); ?>
                                    <span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
                                </a>
                            <?php } else { ?>
                                <a href="#" class="jobportal-button button-outline button-block btn-add-to-message"
                                    data-text="<?php echo esc_attr__('This is a "Demo" account, so you can not change it', 'jobportal-framework'); ?>">
                                    <?php esc_html_e('Get Started', 'jobportal-framework'); ?>
                                    <span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
                                </a>
                            <?php } ?>
                        <?php } else { ?>
                            <?php if ($user_candidate_package_id == $candidate_package_id && $d1 > $d2) { ?>
                                <?php
                                // Find the order for the CURRENT package of user (with status = 1)
                                $args_order = array(
                                    'post_type'           => 'candidate_order',
                                    'posts_per_page'      => 1,
                                    'meta_query'          => array(
                                        'relation' => 'AND',
                                        array(
                                            'key'     => JOBPORTAL_METABOX_PREFIX . 'candidate_order_user_id',
                                            'value'   => $user_id,
                                            'compare' => '='
                                        ),
                                        array(
                                            'key'     => JOBPORTAL_METABOX_PREFIX . 'candidate_order_item_id',
                                            'value'   => $user_candidate_package_id,
                                            'compare' => '='
                                        ),
                                        array(
                                            'key'     => JOBPORTAL_METABOX_PREFIX . 'candidate_order_payment_status',
                                            'value'   => '1',
                                            'compare' => '='
                                        )
                                    ),
                                );
                                $data_order = new WP_Query($args_order);
                                $order_status = '-1';
                                if (!empty($data_order->post)) {
                                    $order_id = $data_order->post->ID;
                                    $order_status = get_post_meta($order_id, JOBPORTAL_METABOX_PREFIX . 'candidate_order_payment_status', true);
                                }

                                if ($order_status === '0') { ?>
                                    <a href="<?php echo esc_url($payment_process_link); ?>"
                                        class="jobportal-button button-block btn-pending">
                                        <?php esc_html_e('Package Pending', 'jobportal-framework'); ?>
                                        <span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
                                    </a>
                                <?php } else { ?>
                                    <a href="<?php echo esc_url($payment_process_link); ?>"
                                        class="jobportal-button button-block">
                                        <?php esc_html_e('Package Activated', 'jobportal-framework'); ?>
                                        <span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
                                    </a>
                                <?php } ?>
                            <?php } else { ?>
                                <a href="<?php echo esc_url($payment_process_link); ?>"
                                    class="jobportal-button button-outline button-block">
                                    <?php esc_html_e('Get Started', 'jobportal-framework'); ?>
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
// Include Package Impact Warning Modal
jobportal_get_template('candidate/package/package-impact-modal.php');

// Enqueue button loading utility
include(JOBPORTAL_PLUGIN_DIR . 'templates/partials/enqueue-button-loading.php');

// Enqueue Package Warning JS
wp_enqueue_script(
    'jobportal-package-warning',
    JOBPORTAL_PLUGIN_URL . 'assets/js/candidate/package-warning.js',
    array('jquery'),
    time(),
    true
);

// Localize script with AJAX URL and nonce
wp_localize_script('jobportal-package-warning', 'jobportal_package_warning_vars', array(
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('jobportal_package_warning_nonce'),
    'i18n' => array(
        'never_expires' => esc_html__('Never Expires', 'jobportal-framework'),
        'expires_on' => esc_html__('Expires:', 'jobportal-framework'),
        'remaining' => esc_html__('remaining', 'jobportal-framework'),
        'featured_services_will_unfeature' => esc_html__('featured services will be unfeatured', 'jobportal-framework'),
        'wishlist_items_will_remove' => esc_html__('wishlist items will be removed', 'jobportal-framework'),
        'companies_will_unfollow' => esc_html__('companies will be unfollowed', 'jobportal-framework'),
        'services' => esc_html__('Services', 'jobportal-framework'),
        'featured_services' => esc_html__('Featured Services', 'jobportal-framework'),
        'jobs_apply' => esc_html__('Job Applications', 'jobportal-framework'),
        'jobs_wishlist' => esc_html__('Wishlist Items', 'jobportal-framework'),
        'company_follow' => esc_html__('Company Follows', 'jobportal-framework')
    )
));
?>
