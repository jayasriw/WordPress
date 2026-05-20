<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'candidate-payment');
global $current_user;
$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$candidate_package_id = isset($_GET['candidate_package_id']) ? jobportal_clean(wp_unslash($_GET['candidate_package_id'])) : '';
$user_candidate_package_id = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'candidate_package_id', $user_id);
$jobportal_candidate = new JobPortal_candidate_package();
$check_candidate_package = $jobportal_candidate->user_candidate_package_available($user_id);

$candidate_package_free = get_post_meta($candidate_package_id, JOBPORTAL_METABOX_PREFIX . 'candidate_package_free', true);

if ($candidate_package_free == 1) {
    $candidate_package_price = 0;
} else {
    $candidate_package_price = get_post_meta($candidate_package_id, JOBPORTAL_METABOX_PREFIX . 'candidate_package_price', true);
}
$candidate_package_time_unit = get_post_meta($candidate_package_id, JOBPORTAL_METABOX_PREFIX . 'candidate_package_time_unit', true);
$candidate_package_number_service = get_post_meta($candidate_package_id, JOBPORTAL_METABOX_PREFIX . 'candidate_package_number_service', true) ?: 0;
$candidate_package_period = get_post_meta($candidate_package_id, JOBPORTAL_METABOX_PREFIX . 'candidate_package_period', true);
$enable_package_service_unlimited = get_post_meta($candidate_package_id, JOBPORTAL_METABOX_PREFIX . 'enable_package_service_unlimited', true);
$enable_package_service_unlimited_time = get_post_meta($candidate_package_id, JOBPORTAL_METABOX_PREFIX . 'enable_package_service_unlimited_time', true);
$candidate_package_featured_candidate = get_post_meta($candidate_package_id, JOBPORTAL_METABOX_PREFIX . 'enable_package_service_featured_unlimited', true);
$candidate_package_number_service_featured = get_post_meta($candidate_package_id, JOBPORTAL_METABOX_PREFIX . 'candidate_package_number_service_featured', true) ?: 0;
$candidate_package_featured = get_post_meta($candidate_package_id, JOBPORTAL_METABOX_PREFIX . 'candidate_package_featured', true);
$candidate_package_title = get_the_title($candidate_package_id);
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
$terms_condition = jobportal_get_option('terms_condition');
$allowed_html = array(
    'a' => array(
        'href' => array(),
        'title' => array(),
        'target' => array()
    ),
    'strong' => array()
);
$candidate_enable_paypal = jobportal_get_option('candidate_enable_paypal', 1);
$candidate_enable_stripe = jobportal_get_option('candidate_enable_stripe', 1);
$candidate_enable_razor = jobportal_get_option('candidate_enable_razor', 1);
$candidate_enable_woocheckout = jobportal_get_option('candidate_enable_woocheckout', 1);
$candidate_enable_wire_transfer = jobportal_get_option('candidate_enable_wire_transfer', 1);
$select_candidate_packages_link = jobportal_get_permalink('candidate_package');
$field_package = array('jobs_apply', 'jobs_wishlist', 'company_follow', 'contact_company', 'info_company', 'send_message', 'review_and_commnent');
?>

<div class="row">
    <div class="col-lg-8 col-md-7 col-sm-6">
        <?php if ($candidate_package_price > 0) : ?>
            <div class="jobportal-payment-method-wrap">
                <div class="entry-heading">
                    <h2 class="entry-title"><?php esc_html_e('Payment Method', 'jobportal-framework'); ?></h2>
                </div>
                <?php if ($candidate_enable_paypal != 0) : ?>
                    <div class="radio active">
                        <label>
                            <input type="radio" class="payment-paypal" name="jobportal_candidate_payment_method"
                                value="paypal" checked>
                            <img src="<?php echo esc_attr(JOBPORTAL_PLUGIN_URL . 'assets/images/paypal.png'); ?>"
                                alt="<?php esc_html_e('Paypal', 'jobportal-framework'); ?>">
                            <?php esc_html_e('Pay With Paypal', 'jobportal-framework'); ?>
                        </label>
                    </div>
                <?php endif; ?>

                <?php if ($candidate_enable_stripe != 0) : ?>
                    <div class="radio">
                        <label>
                            <input type="radio" class="payment-stripe" name="jobportal_candidate_payment_method"
                                value="stripe">
                            <img src="<?php echo esc_attr(JOBPORTAL_PLUGIN_URL . 'assets/images/stripe.png'); ?>"
                                alt="<?php esc_html_e('Stripe', 'jobportal-framework'); ?>">
                            <?php esc_html_e('Pay with Credit Card', 'jobportal-framework'); ?>
                        </label>
                        <?php
                        $jobportal_payment = new JobPortal_Candidate_Payment();
                        $jobportal_payment->candidate_stripe_payment_per_package($candidate_package_id);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if ($candidate_enable_razor != 0) : ?>
                    <div class="radio">
                        <label>
                            <input type="radio" class="payment-razor" name="jobportal_candidate_payment_method" value="razor">
                            <img src="https://cdn.razorpay.com/static/assets/logo/payment.svg" alt="<?php esc_html_e('Razor', 'jobportal-framework'); ?>">
                            <?php esc_html_e('Pay with Razor', 'jobportal-framework'); ?>
                        </label>
                        <?php
                        $jobportal_payment = new JobPortal_Candidate_Payment();
                        $jobportal_payment->jobportal_razor_package_candidate_addons($candidate_package_id);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if ($candidate_enable_woocheckout != 0): ?>
                    <div class="radio">
                        <label>
                            <input type="radio" class="payment-woocheckout" name="jobportal_candidate_payment_method"
                                value="woocheckout">
                            <img src="<?php echo esc_attr(JOBPORTAL_PLUGIN_URL . 'assets/images/woocommerce-logo.png'); ?>"
                                alt="<?php esc_html_e('Woocommerce', 'jobportal-framework'); ?>">
                            <?php esc_html_e('Pay with Woocommerce', 'jobportal-framework'); ?>
                        </label>
                    </div>
                <?php endif; ?>

                <?php if ($candidate_enable_wire_transfer != 0) : ?>
                    <div class="radio wire-transfer">
                        <label>
                            <input type="radio" name="jobportal_candidate_payment_method" value="wire_transfer">
                            <i class="fas fa-window-restore"></i><?php esc_html_e('Wire Transfer', 'jobportal-framework'); ?>
                        </label>
                    </div>
                    <div class="jobportal-wire-transfer-info">
                        <?php
                        $html_info = jobportal_get_option('candidate_wire_transfer_info', '');
                        if ($html_info) {
                            echo '<div class="wire-transfer-instructions">';
                            echo wp_kses_post($html_info);
                            echo '</div>';
                        }
                        ?>
                    </div>
                <?php endif; ?>

            </div>
        <?php endif; ?>
        <input type="hidden" name="jobportal_candidate_package_id" value="<?php echo esc_attr($candidate_package_id); ?>">
        <input type="hidden" name="coupon_code" value="">
        <input type="hidden" name="coupon_amount" value="">
        <div class="package-coupon">
            <form action="#" method="post" class="package-coupon-form">
                <input type="text" name="jobportal_coupon" placeholder="<?php echo esc_attr__('Coupon code', 'jobportal-framework'); ?>">
                <input type="hidden" name="jobportal_package_id" value="<?php echo esc_attr($candidate_package_id); ?>">
                <input type="hidden" name="action" value="jobportal_apply_package_coupon">
                <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('jobportal_apply_package_coupon')); ?>">
                <button type="submit" class="btn btn-success btn-submit gl-button">
                    <?php esc_html_e('Apply', 'jobportal-framework'); ?>
                </button>
            </form>
            <div class="package-coupon-result"></div>
        </div>
        <p class="terms-conditions"><i
                class="fa fa-hand-o-right"></i> <?php echo sprintf(wp_kses(__('Please read <a target="_blank" href="%s"><strong>Terms & Conditions</strong></a> first', 'jobportal-framework'), $allowed_html), get_permalink($terms_condition)); ?>
        </p>
        <?php if ($candidate_package_price > 0) : ?>
            <button id="jobportal_payment_candidate_package" type="submit"
                class="btn btn-success btn-submit gl-button"><?php esc_html_e('Pay Now', 'jobportal-framework'); ?></button>
            <?php else :
            $user_free_candidate_package = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'free_candidate_package', $user_id);
            if ($user_free_candidate_package == 'yes' && $check_candidate_package == 1) : ?>
                <div class="jobportal-message alert alert-warning"
                    role="alert"><?php esc_html_e('You have already used your first free package, please choose different package.', 'jobportal-framework'); ?></div>
            <?php else : ?>
                <button id="jobportal_free_candidate_package" type="submit"
                    class="btn btn-success btn-submit jobportal-button"><?php esc_html_e('Get Free Listing Package', 'jobportal-framework'); ?></button>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="col-lg-4 col-md-5 col-sm-6">
        <div class="jobportal-payment-for jobportal-package-wrap panel panel-default">
            <div class="entry-heading">
                <h2 class="entry-title"><?php esc_html_e('Selected Package', 'jobportal-framework'); ?></h2>
            </div>
            <div class="jobportal-package-item panel panel-default <?php echo esc_attr($is_featured); ?>">
                <?php if (has_post_thumbnail($candidate_package_id)) : ?>
                    <div class="jobportal-package-thumbnail"><?php echo get_the_post_thumbnail($candidate_package_id); ?></div>
                <?php endif; ?>

                <div class="jobportal-package-title">
                    <h2 class="entry-title"><?php echo get_the_title($candidate_package_id); ?></h2>
                </div>

                <ul class="list-group custom-scrollbar">
                    <li class="list-group-item">
                        <i class="fas fa-check"></i>
                        <?php esc_html_e('Package live for', 'jobportal-framework'); ?>
                        <span class="badge">
                            <?php if ($enable_package_service_unlimited_time == 1) {
                                esc_html_e('never expires', 'jobportal-framework');
                            } else {
                                esc_html_e($candidate_package_period . ' ' . JobPortal_Package::get_time_unit($candidate_package_time_unit));
                            } ?>
                        </span>
                    </li>
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
                                $name = esc_html__('View information company', 'jobportal-framework');
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
                <div class="jobportal-total-price">
                    <span><?php esc_html_e('Total', 'jobportal-framework'); ?></span>
                    <div class="old-price"></div>
                    <span class="price">
                        <?php
                        if ($candidate_package_price > 0) {
                            echo jobportal_get_format_money($candidate_package_price, '', null, true);
                        } else {
                            esc_html_e('Free', 'jobportal-framework');
                        }
                        ?>
                    </span>
                </div>
                <a class="jobportal-button"
                    href="<?php echo esc_url($select_candidate_packages_link); ?>"><?php esc_html_e('Change Package', 'jobportal-framework'); ?></a>
            </div>
        </div>
    </div>
</div>
