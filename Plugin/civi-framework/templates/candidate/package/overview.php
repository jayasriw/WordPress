<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $current_user;
$user_id = $current_user->ID;
$candidate_package_id = get_the_author_meta(CIVI_METABOX_PREFIX . 'candidate_package_id', $user_id);
$enable_package_service_unlimited_time = get_post_meta($candidate_package_id, CIVI_METABOX_PREFIX . 'enable_package_service_unlimited_time', true);
$enable_package_service_unlimited = get_post_meta($candidate_package_id, CIVI_METABOX_PREFIX . 'enable_package_service_unlimited', true);
$enable_package_service_featured_unlimited = get_post_meta($candidate_package_id, CIVI_METABOX_PREFIX . 'enable_package_service_featured_unlimited', true);
$candidate_package_number_service = get_user_meta($user_id, CIVI_METABOX_PREFIX . 'candidate_package_number_service', true) ?: 0;
$candidate_package_number_service_featured = get_user_meta($user_id, CIVI_METABOX_PREFIX . 'candidate_package_number_service_featured', true) ?: 0;
$candidate_package_additional = get_post_meta($candidate_package_id, CIVI_METABOX_PREFIX . 'candidate_package_additional_details', true);
if ($candidate_package_additional > 0) {
    $candidate_package_additional_text = get_post_meta($candidate_package_id, CIVI_METABOX_PREFIX . 'candidate_package_details_text', true);
}
$field_package = array('jobs_apply', 'jobs_wishlist', 'company_follow', 'contact_company', 'info_company', 'send_message', 'review_and_commnent');
?>
<ul class="civi-overview-package">
    <?php if (civi_get_option('enable_post_type_service') === '1') { ?>
        <?php if ($enable_package_service_unlimited == 1 || $candidate_package_number_service > 0) : ?>
            <li>
                <span class="name"><?php esc_html_e('Number of services', 'civi-framework') ?></span>
                <span class="content">
                    <?php if ($enable_package_service_unlimited == 1) {
                        esc_html_e('Unlimited', 'civi-framework');
                    } else {
                        echo $candidate_package_number_service;
                    } ?>
                </span>
            </li>
        <?php endif; ?>
        <?php if ($enable_package_service_featured_unlimited == 1 || $candidate_package_number_service_featured > 0) : ?>
            <li>
                <span class="name"><?php esc_html_e('Featured Services', 'civi-framework') ?></span>
                <span class="content">
                    <?php if ($enable_package_service_featured_unlimited == 1) {
                        esc_html_e('Unlimited', 'civi-framework');
                    } else {
                        echo $candidate_package_number_service_featured;
                    } ?>
                </span>
            </li>
        <?php endif; ?>
    <?php } ?>
    <?php foreach ($field_package as $field) :
        $show_option = civi_get_option('enable_candidate_package_' . $field);
        $show_field = get_post_meta($candidate_package_id, CIVI_METABOX_PREFIX . 'show_package_' . $field, true);
        $field_unlimited = get_post_meta($candidate_package_id, CIVI_METABOX_PREFIX . 'enable_package_' . $field . '_unlimited', true);
        $field_number = get_user_meta($user_id, CIVI_METABOX_PREFIX . 'candidate_package_number_' . $field, true) ?: 0;
        if ($field_number === '-1') {
            $field_number = 0;
        }
        $is_check = false;
        switch ($field) {
            case 'jobs_apply':
                $name = esc_html__('Number of jobs available to apply for', 'civi-framework');
                break;
            case 'jobs_wishlist':
                $name = esc_html__('Number of jobs wishlist', 'civi-framework');
                break;
            case 'company_follow':
                $name = esc_html__('Number of companies followed', 'civi-framework');
                break;
            case 'contact_company':
                $name = esc_html__('View company in job details', 'civi-framework');
                $is_check = true;
                break;
            case 'info_company':
                $name = esc_html__('View company information', 'civi-framework');
                $is_check = true;
                break;
            case 'send_message':
                $name = esc_html__('Send Messages', 'civi-framework');
                $is_check = true;
                break;
            case 'review_and_commnent':
                $name = esc_html__('Review and comment', 'civi-framework');
                $is_check = true;
                break;
        }
        if ($show_field == 1 && $show_option == 1 && ($field_unlimited == 1 || $field_number > 0 || $is_check)) : ?>
            <li>
                <span class="name"><?php echo $name; ?></span>
                <span class="content">
                    <?php if ($is_check == true) { ?>
                        <i class="fas fa-check"></i>
                    <?php } else { ?>
                        <?php if ($field_unlimited == 1) { ?>
                            <?php esc_html_e('Unlimited', 'civi-framework'); ?>
                        <?php } else { ?>
                            <?php echo $field_number; ?>
                        <?php } ?>
                    <?php } ?>
                </span>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php if ($candidate_package_additional > 0 && !empty($candidate_package_additional_text)) :
        foreach ($candidate_package_additional_text as $additional) :
            if (!empty($additional)) : ?>
                <li>
                    <span class="name"><?php echo $additional; ?></span>
                    <span class="content">
                        <i class="fas fa-check"></i>
                    </span>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</ul>
