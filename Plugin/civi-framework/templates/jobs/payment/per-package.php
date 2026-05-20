<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $current_user;
$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$package_id = isset($_GET['package_id']) ? civi_clean(wp_unslash($_GET['package_id'])) : '';
$user_package_id = get_the_author_meta(CIVI_METABOX_PREFIX . 'package_id', $user_id);
$civi_profile = new Civi_Profile();
$check_package = $civi_profile->user_package_available($user_id);

$user_demo = get_the_author_meta(CIVI_METABOX_PREFIX . 'user_demo', $user_id);
$package_free = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'package_free', true);

if ($package_free == 1) {
    $package_price = 0;
} else {
    $package_price = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'package_price', true);
}
$package_time_unit = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'package_time_unit', true);
$package_num_job = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'package_number_job', true) ?: 0;
$package_period = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'package_period', true);
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
$terms_condition = civi_get_option('terms_condition');
$allowed_html = array(
    'a' => array(
        'href' => array(),
        'title' => array(),
        'target' => array()
    ),
    'strong' => array()
);
$enable_paypal = civi_get_option('enable_paypal', 1);
$enable_stripe = civi_get_option('enable_stripe', 1);
$enable_woocheckout = civi_get_option('enable_woocheckout', 1);
$enable_wire_transfer = civi_get_option('enable_wire_transfer', 1);
$employer_enable_razor = civi_get_option('employer_enable_razor', 1);
$select_packages_link = civi_get_permalink('package');
$field_package = array('candidate_follow', 'download_cv', 'invite', 'send_message', 'print', 'review_and_commnent', 'info');
?>

<div class="row">
    <div class="col-lg-8 col-md-7 col-sm-6">
        <?php if ($package_price > 0) : ?>
            <div class="civi-payment-method-wrap">
                <div class="entry-heading">
                    <h2 class="entry-title"><?php esc_html_e('Payment Method', 'civi-framework'); ?></h2>
                </div>
                <?php if ($enable_paypal != 0) : ?>
                    <div class="radio active">
                        <label>
                            <input type="radio" class="payment-paypal" name="civi_payment_method" value="paypal" checked>
                            <img src="<?php echo esc_attr(CIVI_PLUGIN_URL . 'assets/images/paypal.png'); ?>" alt="<?php esc_html_e('Paypal', 'civi-framework'); ?>">
                            <?php esc_html_e('Pay With Paypal', 'civi-framework'); ?>
                        </label>
                    </div>
                <?php endif; ?>
                <?php if ($enable_stripe != 0) : ?>
                    <div class="radio">
                        <label>
                            <input type="radio" class="payment-stripe" name="civi_payment_method" value="stripe">
                            <img src="<?php echo esc_attr(CIVI_PLUGIN_URL . 'assets/images/stripe.png'); ?>" alt="<?php esc_html_e('Stripe', 'civi-framework'); ?>">
                            <?php esc_html_e('Pay with Credit Card', 'civi-framework'); ?>
                        </label>
                        <?php
                        $civi_payment = new Civi_Payment();
                        $civi_payment->stripe_payment_per_package($package_id);
                        ?>
                    </div>
                <?php endif; ?>
                <?php if ($employer_enable_razor != 0) : ?>
                    <div class="radio">
                        <label>
                            <input type="radio" class="payment-razor" name="civi_payment_method" value="razor">
                            <img src="https://cdn.razorpay.com/static/assets/logo/payment.svg" alt="<?php esc_html_e('Razor', 'civi-framework'); ?>">
                            <?php esc_html_e('Pay with Razor', 'civi-framework'); ?>
                        </label>
                        <?php
                        $civi_payment = new Civi_Payment();
                        $civi_payment->civi_razor_package_addons($package_id);
                        ?>
                    </div>
                <?php endif; ?>
                <?php if ($enable_woocheckout != 0) : ?>
                    <div class="radio">
                        <label>
                            <input type="radio" class="payment-woocheckout" name="civi_payment_method" value="woocheckout">
                            <img src="<?php echo esc_attr(CIVI_PLUGIN_URL . 'assets/images/woocommerce-logo.png'); ?>" alt="<?php esc_html_e('Woocommerce', 'civi-framework'); ?>">
                            <?php esc_html_e('Pay with Woocommerce', 'civi-framework'); ?>
                        </label>
                    </div>
                <?php endif; ?>
                <?php if ($enable_wire_transfer != 0) : ?>
                    <div class="radio wire-transfer">
                        <label>
                            <input type="radio" name="civi_payment_method" value="wire_transfer">
                            <i class="fas fa-window-restore"></i><?php esc_html_e('Wire Transfer', 'civi-framework'); ?>
                        </label>
                    </div>
                    <div class="civi-wire-transfer-info">
                        <?php
                        $html_info = civi_get_option('wire_transfer_info', '');
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
        <input type="hidden" name="civi_package_id" value="<?php echo esc_attr($package_id); ?>">
        <input type="hidden" name="coupon_code" value="">
        <input type="hidden" name="coupon_amount" value="">
        <div class="package-coupon">
            <form action="#" method="post" class="package-coupon-form">
                <input type="text" name="civi_coupon" placeholder="<?php echo esc_attr__('Coupon code', 'civi-framework'); ?>">
                <input type="hidden" name="civi_package_id" value="<?php echo esc_attr($package_id); ?>">
                <input type="hidden" name="action" value="civi_apply_package_coupon">
                <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('civi_apply_package_coupon')); ?>">
                <button type="submit" class="btn btn-success btn-submit gl-button">
                    <?php esc_html_e('Apply', 'civi-framework'); ?>
                </button>
            </form>
            <div class="package-coupon-result"></div>
        </div>
        <p class="terms-conditions"><i class="fa fa-hand-o-right"></i> <?php echo sprintf(wp_kses(__('Please read <a target="_blank" href="%s"><strong>Terms & Conditions</strong></a> first', 'civi-framework'), $allowed_html), get_permalink($terms_condition)); ?></p>
        <?php if ($package_price > 0) : ?>
            <?php if ($user_demo == 'yes') : ?>
                <button type="submit" class="btn btn-success btn-submit gl-button btn-add-to-message" data-text="<?php echo esc_attr__('This is a "Demo" account so you not cant change it', 'civi-framework'); ?>">
                    <?php esc_html_e('Pay Now', 'civi-framework'); ?>
                </button>
            <?php else : ?>
                <button id="civi_payment_package" type="submit" class="btn btn-success btn-submit gl-button"><?php esc_html_e('Pay Now', 'civi-framework'); ?></button>
            <?php endif; ?>
            <?php else :
            $user_free_package = get_the_author_meta(CIVI_METABOX_PREFIX . 'free_package', $user_id);
            if ($user_free_package == 'yes' && ($check_package == 1 || $check_package == 2)) : ?>
                <div class="civi-message alert alert-warning" role="alert"><?php esc_html_e('You have already used your first free package, please choose different package.', 'civi-framework'); ?></div>
            <?php else : ?>
                <button id="civi_free_package" type="submit" class="btn btn-success btn-submit civi-button"><?php esc_html_e('Get Free Listing Package', 'civi-framework'); ?></button>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="col-lg-4 col-md-5 col-sm-6">
        <div class="civi-payment-for civi-package-wrap panel panel-default">
            <div class="entry-heading">
                <h2 class="entry-title"><?php esc_html_e('Selected Package', 'civi-framework'); ?></h2>
            </div>
            <div class="civi-package-item panel panel-default <?php echo esc_attr($is_featured); ?>">
                <?php if (has_post_thumbnail($package_id)) : ?>
                    <div class="civi-package-thumbnail"><?php echo get_the_post_thumbnail($package_id); ?></div>
                <?php endif; ?>
                <div class="civi-package-title">
                    <h2 class="entry-title"><?php echo get_the_title($package_id); ?></h2>
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
                                $name_singular = __('%s candidate follow', 'civi-framework');
                                $name_plural = __('%s candidates follow', 'civi-framework');
                                $is_check = false;
                                break;
                            case 'download_cv':
                                $name_singular = __('%s CV download', 'civi-framework');
                                $name_plural = __('%s CV downloads', 'civi-framework');
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
                        if ($show_field == 1 && $show_option == 1 && ($field_unlimited == 1 || $field_number > 0 || $is_check)) : ?>
                            <li class="list-group-item">
                                <i class="fas fa-check"></i>
                                <?php if ($is_check == true) { ?>
                                    <span class="badge"><?php esc_html_e($name); ?></span>
                                <?php } else { ?>
                                    <?php if ($field_unlimited == 1) { ?>
                                        <?php
                                        switch ($field) {
                                            case 'candidate_follow':
                                                echo esc_html__('Unlimited candidate follows', 'civi-framework');
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
                    <?php if ($package_additional > 0 && !empty($package_additional_text)) :
                        foreach ($package_additional_text as $value) :
                            if (!empty($value)) : ?>
                                <li class="list-group-item">
                                    <i class="fas fa-check"></i>
                                    <span class="badge"><?php esc_html_e($value); ?></span>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <li class="list-group-item">
                        <i class="fas fa-check"></i>
                        <?php esc_html_e('Package live for', 'civi-framework'); ?>
                        <span class="badge">
                            <?php if ($package_unlimited_time == 1) {
                                esc_html_e('never expires', 'civi-framework');
                            } else {
                                esc_html_e($package_period . ' ' . Civi_Package::get_time_unit($package_time_unit));
                            } ?>
                        </span>
                    </li>
                </ul>
                <div class="civi-total-price">
                    <span><?php esc_html_e('Total', 'civi-framework'); ?></span>
                    <div class="old-price"></div>
                    <span class="price">
                        <?php
                        if ($package_price > 0) {
                            echo civi_get_format_money($package_price, '', null, true);
                        } else {
                            esc_html_e('Free', 'civi-framework');
                        }
                        ?>
                    </span>
                </div>
                <a class="civi-button" href="<?php echo esc_url($select_packages_link); ?>"><?php esc_html_e('Change Package', 'civi-framework'); ?></a>
            </div>
        </div>
    </div>
</div>
