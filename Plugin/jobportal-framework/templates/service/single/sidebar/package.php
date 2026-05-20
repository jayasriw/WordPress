<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$payment_url = jobportal_get_permalink('payment_service');
wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'service');
wp_localize_script(
    JOBPORTAL_PLUGIN_PREFIX . 'service',
    'jobportal_addons_vars',
    array(
        'ajax_url' => JOBPORTAL_AJAX_URL,
        'payment_url' => $payment_url,
    )
);

global $current_user;
$service_id = get_the_ID();
$classes = array();
$enable_sticky_sidebar_type = jobportal_get_option('enable_sticky_service_sidebar_type');
$currency_sign_default = jobportal_get_option('currency_sign_default');
$enable_candidate_service_fee = jobportal_get_option('enable_candidate_service_fee');
$candidate_number_service_fee = jobportal_get_option('candidate_number_service_fee');
if ($enable_sticky_sidebar_type) {
    $classes[] = 'has-sticky';
}

$number_start_price = get_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_price', true);
$number_delivery_time = get_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_number_time', true);
$delivery_rate = get_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_time_type', true);
$language_level = get_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_language_level', true);
$service_language = get_the_terms($service_id, 'service-language');
$service_addon = get_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_tab_addon', true);

$currency_position = jobportal_get_option('currency_position');
if ($currency_position == 'before') {
    $start_price = $currency_sign_default . $number_start_price;
} else {
    $start_price = $number_start_price . $currency_sign_default;
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

if ($language_level == 'basic') {
    $language = esc_html__('Basic', 'jobportal-framework');
} elseif ($language_level == 'conversational') {
    $language = esc_html__('Conversational', 'jobportal-framework');
} elseif ($language_level == 'fluent') {
    $language = esc_html__('Fluent', 'jobportal-framework');
} elseif ($language_level == 'native') {
    $language = esc_html__('Native or Bilingual', 'jobportal-framework');
} elseif ($language_level == 'professional') {
    $language = esc_html__('Professional', 'jobportal-framework');
}
?>
<div class="service-package-sidebar block-archive-sidebar <?php echo implode(" ", $classes); ?>">
    <div class="package-top">
        <span><?php echo esc_html__('Starting at:', 'jobportal-framework'); ?></span>
        <span class="price"><?php echo $start_price; ?></span>
    </div>
    <div class="package-center">
        <ul class="content">
            <?php if (!empty($number_delivery_time)) : ?>
                <li>
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15 2H19C19.2652 2 19.5196 2.10536 19.7071 2.29289C19.8946 2.48043 20 2.73478 20 3V19C20 19.2652 19.8946 19.5196 19.7071 19.7071C19.5196 19.8946 19.2652 20 19 20H1C0.734784 20 0.48043 19.8946 0.292893 19.7071C0.105357 19.5196 0 19.2652 0 19V3C0 2.73478 0.105357 2.48043 0.292893 2.29289C0.48043 2.10536 0.734784 2 1 2H5V0H7V2H13V0H15V2ZM13 4H7V6H5V4H2V8H18V4H15V6H13V4ZM18 10H2V18H18V10Z"
                            fill="#007456" />
                    </svg>
                    <span><?php echo esc_html__('Transfer time', 'jobportal-framework'); ?></span>
                    <span class="delivery-time"><?php echo $number_delivery_time . ' ' . $rate; ?></span>
                </li>
            <?php endif; ?>
            <?php if (!empty($language)) : ?>
                <li>
                    <svg width="21" height="19" viewBox="0 0 21 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 13V15C3 16.0544 3.81588 16.9182 4.85074 16.9945L5 17H8V19H5C2.79086 19 1 17.2091 1 15V13H3ZM16 8L20.4 19H18.245L17.044 16H12.954L11.755 19H9.601L14 8H16ZM15 10.8852L13.753 14H16.245L15 10.8852ZM6 0V2H10V9H6V12H4V9H0V2H4V0H6ZM15 1C17.2091 1 19 2.79086 19 5V7H17V5C17 3.89543 16.1046 3 15 3H12V1H15ZM4 4H2V7H4V4ZM8 4H6V7H8V4Z"
                            fill="#007456" />
                    </svg>
                    <span><?php echo esc_html__('Languages level', 'jobportal-framework'); ?></span>
                    <span class="languages-level"><?php echo $language; ?></span>
                </li>
            <?php endif; ?>
            <?php if (!empty($service_language)) : ?>
                <li>
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 20C4.47715 20 0 15.5228 0 10C0 4.47715 4.47715 0 10 0C15.5228 0 20 4.47715 20 10C20 15.5228 15.5228 20 10 20ZM7.71002 17.6674C6.74743 15.6259 6.15732 13.3742 6.02731 11H2.06189C2.458 14.1765 4.71639 16.7747 7.71002 17.6674ZM8.0307 11C8.1811 13.4388 8.8778 15.7297 10 17.752C11.1222 15.7297 11.8189 13.4388 11.9693 11H8.0307ZM17.9381 11H13.9727C13.8427 13.3742 13.2526 15.6259 12.29 17.6674C15.2836 16.7747 17.542 14.1765 17.9381 11ZM2.06189 9H6.02731C6.15732 6.62577 6.74743 4.37407 7.71002 2.33256C4.71639 3.22533 2.458 5.8235 2.06189 9ZM8.0307 9H11.9693C11.8189 6.56122 11.1222 4.27025 10 2.24799C8.8778 4.27025 8.1811 6.56122 8.0307 9ZM12.29 2.33256C13.2526 4.37407 13.8427 6.62577 13.9727 9H17.9381C17.542 5.8235 15.2836 3.22533 12.29 2.33256Z"
                            fill="#007456" />
                    </svg>
                    <span><?php echo esc_html__('Language', 'jobportal-framework'); ?></span>
                    <span class="service-location">
                        <?php foreach ($service_language as $location) { ?>
                            <span><?php echo $location->name; ?></span>
                        <?php } ?>
                    </span>
                </li>
            <?php endif; ?>
        </ul>
    </div>
    <?php if (!empty($service_addon[0]['jobportal-service_addons_title'])) : ?>
        <div class="package-bottom">
            <h4><?php echo esc_html__('Add-ons services', 'jobportal-framework'); ?></h4>
            <ul class="package-addons custom-scrollbar">
                <?php foreach ($service_addon as $key => $addon) {
                    $count = $key + 1;
                    if ($currency_position == 'before') {
                        $addon_price = $currency_sign_default . $addon['jobportal-service_addons_price'];
                    } else {
                        $addon_price = $addon['jobportal-service_addons_price'] . $currency_sign_default;
                    }
                ?>
                    <?php if (!empty($addon['jobportal-service_addons_title'])) : ?>
                        <li>
                            <input type="checkbox" id="package-addons-<?php echo $count; ?>" class="custom-checkbox input-control" name="package_addons[]" value="<?php echo $addon['jobportal-service_addons_price']; ?>" />
                            <label for="package-addons-<?php echo $count; ?>">
                                <span class="addons-left">
                                    <span class="title"><?php echo $addon['jobportal-service_addons_title']; ?></span>
                                    <span class="content"><?php echo $addon['jobportal-service_addons_description']; ?></span>
                                </span>
                                <span class="price"><?php echo $addon_price; ?></span>
                            </label>
                        </li>
                    <?php endif; ?>
                <?php } ?>
            </ul>
        </div>
    <?php endif; ?>
    <div class="package-total" data-start-price="<?php echo $number_start_price ?>">
        <span><?php echo esc_html__('Total', 'jobportal-framework'); ?></span>
        <span class="price"><?php echo jobportal_get_format_money($number_start_price); ?></span>
    </div>
    <?php if ($enable_candidate_service_fee === '1' && !empty($candidate_number_service_fee)) :
        $candidate_number_service_fee = $candidate_number_service_fee . '%'; ?>
        <div class="service_fee">
            <?php echo sprintf(esc_html__('Freelancer will be deducted %s website maintenance service fee', 'jobportal-framework'), $candidate_number_service_fee); ?>
        </div>
    <?php endif; ?>
    <?php if (is_user_logged_in() && in_array('jobportal_user_employer', (array)$current_user->roles)) { ?>
        <a class="jobportal-button button-block" id="btn-submit-addons" href="#">
            <?php esc_html_e('Hire Now', 'jobportal-framework') ?>
            <span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
        </a>
    <?php } else { ?>
        <div class="logged-out">
            <a href="#popup-form" class="jobportal-button button-block btn-login tooltip notice-employer"
                data-notice="<?php esc_attr_e('Please access the role Employer', 'jobportal-framework') ?>">
                <?php esc_html_e('Hire Now', 'jobportal-framework') ?>
            </a>
        </div>
    <?php } ?>
    <input type="hidden" name="service_id" class="service_id" value="<?php echo $service_id; ?>">
    <input type="hidden" name="price_addons" class="price_addons" value="">
</div>
