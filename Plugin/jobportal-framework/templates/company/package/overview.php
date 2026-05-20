<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $current_user;
$user_id = $current_user->ID;
$package_id = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'package_id', $user_id);
$package_unlimited_job = get_post_meta($package_id, JOBPORTAL_METABOX_PREFIX . 'package_unlimited_job', true);
$package_unlimited_featured_job = get_post_meta($package_id, JOBPORTAL_METABOX_PREFIX . 'package_unlimited_job_featured', true);
$package_num_job = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'package_number_job', true) ?: 0;
$package_num_featured_job = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'package_number_featured', true) ?: 0;

$package_additional = get_post_meta($package_id, JOBPORTAL_METABOX_PREFIX . 'package_additional_details', true);
if ($package_additional > 0) {
    $package_additional_text = get_post_meta($package_id, JOBPORTAL_METABOX_PREFIX . 'package_details_text', true);
}
$field_package = array('candidate_follow', 'download_cv', 'invite', 'send_message', 'print', 'review_and_commnent', 'info');
?>
<ul class="jobportal-overview-package">
    <?php if ($package_unlimited_job == 1 || $package_num_job > 0) : ?>
        <li>
            <span class="name"><?php esc_html_e('Number of jobs', 'jobportal-framework') ?></span>
            <span class="content">
                <?php if ($package_unlimited_job == 1) {
                    esc_html_e('Unlimited', 'jobportal-framework');
                } else {
                    echo $package_num_job;
                } ?>
            </span>
        </li>
    <?php endif; ?>
    <?php if ($package_unlimited_featured_job == 1 || $package_num_featured_job > 0) : ?>
        <li>
            <span class="name"><?php esc_html_e('Featured jobs', 'jobportal-framework') ?></span>
            <span class="content">
                <?php if ($package_unlimited_featured_job == 1) {
                    esc_html_e('Unlimited', 'jobportal-framework');
                } else {
                    echo $package_num_featured_job;
                } ?>
            </span>
        </li>
    <?php endif; ?>
    <?php foreach ($field_package as $field) :
        $show_option = jobportal_get_option('enable_company_package_' . $field);
        $show_field = get_post_meta($package_id, JOBPORTAL_METABOX_PREFIX . 'show_package_company_' . $field, true);
        $field_unlimited = get_post_meta($package_id, JOBPORTAL_METABOX_PREFIX . 'enable_package_' . $field . '_unlimited', true);
        $field_number = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'package_number_' . $field, true);
        if ($field_number == '') {
            $field_number = get_post_meta($package_id, JOBPORTAL_METABOX_PREFIX . 'company_package_number_' . $field, true) ?: 0;
        } elseif ($field_number === '-1') {
            $field_number = 0;
        } else {
            $field_number = $field_number ?: 0;
        }

        $is_check = true;
        switch ($field) {
            case 'candidate_follow':
                $name = esc_html__('Number of candidates follow', 'jobportal-framework');
                $is_check = false;
                break;
            case 'download_cv':
                $name = esc_html__('Number of CV downloads', 'jobportal-framework');
                $is_check = false;
                break;
            case 'invite':
                $name = esc_html__('Invite Candidates', 'jobportal-framework');
                break;
            case 'send_message':
                $name = esc_html__('Send Messages', 'jobportal-framework');
                $is_check = true;
                break;
            case 'print':
                $name = esc_html__('Print candidate profiles', 'jobportal-framework');
                break;
            case 'review_and_commnent':
                $name = esc_html__('Review and comment', 'jobportal-framework');
                $is_check = true;
                break;
            case 'info':
                $name = esc_html__('View candidate information', 'jobportal-framework');
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
                            <?php esc_html_e('Unlimited', 'jobportal-framework'); ?>
                        <?php } else { ?>
                            <?php echo $field_number; ?>
                        <?php } ?>
                    <?php } ?>
                </span>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php if ($package_additional > 0 && !empty($package_additional_text)) {
        foreach ($package_additional_text as $value) {
            if (!empty($value)) : ?>
                <li>
                    <span class="name"><?php echo $value; ?></span>
                    <span class="content">
                        <i class="fas fa-check"></i>
                    </span>
                </li>
            <?php endif; ?>
    <?php }
    } ?>
</ul>
