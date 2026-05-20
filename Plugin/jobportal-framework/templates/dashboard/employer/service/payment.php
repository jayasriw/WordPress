<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'service-payment');
wp_enqueue_script('razorpay_checkout', 'https://checkout.razorpay.com/v1/checkout.js', null, null);
global $current_user;
$current_user = wp_get_current_user();
$user_id = $current_user->ID;

$currency_sign_default = jobportal_get_option('currency_sign_default');
$service_id = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'package_addons_service_id', true);
$total_addons = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'package_addons_price_total', true);
$price_addons = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'package_addons_price_addons', true);
// Validate and round prices
$total_addons = floatval($total_addons);
$price_addons = floatval($price_addons);
$number_start_price = get_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_price', true);
$number_start_price = floatval($number_start_price);

// Validate: handle NaN, Infinity, negative values
if (!is_numeric($total_addons) || is_infinite($total_addons) || is_nan($total_addons) || $total_addons < 0) {
    $total_addons = 0;
}
if (!is_numeric($price_addons) || is_infinite($price_addons) || is_nan($price_addons) || $price_addons < 0) {
    $price_addons = 0;
}
if (!is_numeric($number_start_price) || is_infinite($number_start_price) || is_nan($number_start_price) || $number_start_price < 0) {
    $number_start_price = 0;
}

$total_addons = round($total_addons, 2);
$price_addons = round($price_addons, 2);
$number_start_price = round($number_start_price, 2);
$service_featured  = get_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_featured', true);
$service_skills = get_the_terms($service_id, 'service-skills');
$service_categories =  get_the_terms($service_id, 'service-categories');
$service_location =  get_the_terms($service_id, 'service-location');
$thumbnail = get_the_post_thumbnail_url($service_id, '70x70');
$number_delivery_time = get_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_number_time', true);
$delivery_rate = get_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_time_type', true);

$currency_position = jobportal_get_option('currency_position');
if ($currency_position == 'before') {
    $start_price = $currency_sign_default . $number_start_price;
    $price_addons = $currency_sign_default . $price_addons;
    $total_addons = $currency_sign_default . $total_addons;
} else {
    $start_price = $number_start_price . $currency_sign_default;
    $price_addons = $currency_sign_default . $price_addons;
    $total_addons = $currency_sign_default . $total_addons;
}

$rate = '';
if ($number_delivery_time === '1') {
    if ($delivery_rate == 'hr') {
        $rate = esc_html__('hour', 'jobportal-framework');
    } elseif ($delivery_rate == 'day') {
        $rate = esc_html__('day', 'jobportal-framework');
    } elseif ($delivery_rate == 'week') {
        $rate = esc_html__('week', 'jobportal-framework');
    } elseif ($delivery_rate == 'month') {
        $rate = esc_html__('month', 'jobportal-framework');
    }
} else {
    if ($delivery_rate == 'hr') {
        $rate = esc_html__('hours', 'jobportal-framework');
    } elseif ($delivery_rate == 'day') {
        $rate = esc_html__('days', 'jobportal-framework');
    } elseif ($delivery_rate == 'week') {
        $rate = esc_html__('weeks', 'jobportal-framework');
    } elseif ($delivery_rate == 'month') {
        $rate = esc_html__('months', 'jobportal-framework');
    }
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
$service_enable_paypal = jobportal_get_option('service_enable_paypal', 1);
$service_enable_stripe = jobportal_get_option('service_enable_stripe', 1);
$service_enable_razor = jobportal_get_option('service_enable_razor', 1);
$service_enable_woocheckout = jobportal_get_option('service_enable_woocheckout', 1);
$service_enable_wire_transfer = jobportal_get_option('service_enable_wire_transfer', 1);
?>
<div class="payment-wrap">
    <div class="row">
        <div class="col-lg-8 col-md-7 col-sm-6">
            <div class="jobportal-payment-method-wrap">
                <div class="entry-heading">
                    <h2 class="entry-title"><?php esc_html_e('Payment Method', 'jobportal-framework'); ?></h2>
                </div>
                <?php if ($service_enable_paypal != 0) : ?>
                    <div class="radio active">
                        <label>
                            <input type="radio" class="payment-paypal" name="jobportal_payment_method" value="paypal" checked>
                            <img src="<?php echo esc_attr(JOBPORTAL_PLUGIN_URL . 'assets/images/paypal.png'); ?>" alt="<?php esc_html_e('Paypal', 'jobportal-framework'); ?>">
                            <?php esc_html_e('Pay With Paypal', 'jobportal-framework'); ?>
                        </label>
                    </div>
                <?php endif; ?>
                <?php if ($service_enable_stripe != 0) : ?>
                    <div class="radio">
                        <label>
                            <input type="radio" class="payment-stripe" name="jobportal_payment_method" value="stripe">
                            <img src="<?php echo esc_attr(JOBPORTAL_PLUGIN_URL . 'assets/images/stripe.png'); ?>" alt="<?php esc_html_e('Stripe', 'jobportal-framework'); ?>">
                            <?php esc_html_e('Pay with Credit Card', 'jobportal-framework'); ?>
                        </label>
                        <?php
                        $jobportal_payment = new JobPortal_Service_Payment();
                        $jobportal_payment->jobportal_stripe_payment_service_addons($service_id);
                        ?>
                    </div>
                <?php endif; ?>
                <?php if ($service_enable_razor != 0) : ?>
                    <div class="radio">
                        <label>
                            <input type="radio" class="payment-razor" name="jobportal_payment_method" value="razor">
                            <img src="https://cdn.razorpay.com/static/assets/logo/payment.svg" alt="<?php esc_html_e('Razor', 'jobportal-framework'); ?>">
                            <?php esc_html_e('Pay with Razor', 'jobportal-framework'); ?>
                        </label>
                        <?php
                        $jobportal_payment = new JobPortal_Service_Payment();
                        $jobportal_payment->jobportal_razor_payment_service_addons($service_id);
                        ?>
                    </div>
                <?php endif; ?>
                <?php if ($service_enable_woocheckout != 0): ?>
                    <div class="radio">
                        <label>
                            <input type="radio" class="payment-woocheckout" name="jobportal_payment_method" value="woocheckout">
                            <img src="<?php echo esc_attr(JOBPORTAL_PLUGIN_URL . 'assets/images/woocommerce-logo.png'); ?>" alt="<?php esc_html_e('Woocommerce', 'jobportal-framework'); ?>">
                            <?php esc_html_e('Pay with Woocommerce', 'jobportal-framework'); ?>
                        </label>
                    </div>
                <?php endif; ?>
                <?php if ($service_enable_wire_transfer != 0) : ?>
                    <div class="radio wire-transfer">
                        <label>
                            <input type="radio" name="jobportal_payment_method" value="wire_transfer">
                            <i class="fas fa-window-restore"></i><?php esc_html_e('Wire Transfer', 'jobportal-framework'); ?>
                        </label>
                    </div>
                    <div class="jobportal-wire-transfer-info">
                        <?php
                        $html_info = jobportal_get_option('service_wire_transfer_info', '');
                        if ($html_info) {
                            echo '<div class="wire-transfer-instructions">';
                            echo wp_kses_post($html_info);
                            echo '</div>';
                        }
                        ?>
                    </div>
                <?php endif; ?>
            </div>
            <p class="terms-conditions"><i class="fa fa-hand-o-right"></i> <?php echo sprintf(wp_kses(__('Please read <a target="_blank" href="%s"><strong>Terms & Conditions</strong></a> first', 'jobportal-framework'), $allowed_html), get_permalink($terms_condition)); ?></p>
            <button id="jobportal_payment_service" type="submit" class="btn btn-success btn-submit gl-button"><?php esc_html_e('Pay Now', 'jobportal-framework'); ?></button>
        </div>
        <div class="col-lg-4 col-md-5 col-sm-6">
            <div class="jobportal-package-wrap package-service">
                <div class="entry-heading">
                    <h2 class="entry-title"><?php esc_html_e('Order summary', 'jobportal-framework'); ?></h2>
                </div>
                <div class="jobportal-package-item">
                    <div class="package-header">
                        <?php if (!empty($thumbnail)) : ?>
                            <img class="thumbnail" src="<?php echo $thumbnail; ?>" alt="" />
                        <?php endif; ?>
                        <h3 class="title-my-service">
                            <a href="<?php echo get_the_permalink($service_id) ?>">
                                <?php echo get_the_title($service_id); ?>
                            </a>
                        </h3>
                    </div>
                    <div class="package-content">
                        <p>
                            <span class="title"><?php esc_html_e('Basic price', 'jobportal-framework') ?></span>
                            <span class="price"><?php echo $start_price; ?></span>
                        </p>
                        <p>
                            <span class="title"><?php esc_html_e('Add ons service price', 'jobportal-framework') ?></span>
                            <span class="price"><?php echo $price_addons; ?></span>
                        </p>
                    </div>
                    <div class="package-bottom">
                        <p>
                            <span class="total"><?php esc_html_e('Total', 'jobportal-framework') ?></span>
                            <span class="price"><?php echo $total_addons; ?></span>
                        </p>
                        <p>
                            <span class="title"><?php esc_html_e('Transfer time', 'jobportal-framework') ?></span>
                            <span class="delivery-time"><?php echo $number_delivery_time . ' ' . $rate; ?></span>
                        </p>
                        <a class="jobportal-button" href="<?php echo esc_url(get_post_type_archive_link('service')) ?>"><?php esc_html_e('Change Service', 'jobportal-framework'); ?></a>
                    </div>
                </div>
                <?php $total_addons_clean = preg_replace('/[^0-9]/', '', $total_addons); ?>
                <input type="hidden" name="total_price" value="<?php echo esc_attr($total_addons_clean); ?>">
            </div>
        </div>
    </div>
    <input type="hidden" name="service_id" value="<?php echo $service_id; ?>">
    <?php wp_nonce_field('jobportal_service_payment_ajax_nonce', 'jobportal_service_security_payment'); ?>
</div>
