<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $current_user;
wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'candidate-follow');

$key = false;
$user_id = $current_user->ID;
$follow_candidate = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'follow_candidate', true);
$id = get_the_ID();
if (!empty($candidate_id)) {
    $id = $candidate_id;
}

if (!empty($follow_candidate)) {
    $key = array_search($id, $follow_candidate);
}

$css_class = '';
if ($key !== false) {
    $css_class = 'added';
}


$paid_submission_type = jobportal_get_option('paid_submission_type');
$package_id = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'package_id', $user_id);
$jobportal_profile = new JobPortal_Profile();
$check_package = $jobportal_profile->user_package_available($user_id);
$show_package_follow = get_post_meta($package_id, JOBPORTAL_METABOX_PREFIX . 'show_package_company_candidate_follow', true);
$enable_company_package_follow = jobportal_get_option('enable_company_package_candidate_follow');
$company_package_number_candidate_follow = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'package_number_candidate_follow', true);
?>
<?php if (is_user_logged_in() && in_array('jobportal_user_employer', (array)$current_user->roles)) { ?>
    <?php if ($paid_submission_type == 'per_package' && $enable_company_package_follow === '1' && ($show_package_follow === '0' || $company_package_number_candidate_follow <= 0 || ($check_package == -1 || $check_package == 0))) {?>
        <a href="#" class="jobportal-button button-outline-accent btn-add-to-message tooltip <?php echo esc_attr($css_class); ?>"
           data-text="<?php echo esc_attr__('The quantity in your package has reached its limit or your package has expired', 'jobportal-framework'); ?>">
            <?php if ($key !== false) { ?>
                <span class="icon-plus"><i class="far fa-check"></i></span>
                <?php esc_html_e('Following', 'jobportal-framework') ?>
            <?php } else { ?>
                <span class="icon-plus"><i class="far fa-plus"></i></span>
                <?php esc_html_e('Follow', 'jobportal-framework') ?>
            <?php } ?>
        </a>
    <?php } else { ?>
        <a href="#"
           class="jobportal-button button-outline-accent jobportal-add-to-follow-candidate add-follow-candidate <?php echo esc_attr($css_class); ?>"
           data-candidate-id="<?php echo intval($id) ?>">
            <?php if ($key !== false) { ?>
                <span class="icon-plus"><i class="far fa-check"></i></span>
                <?php esc_html_e('Following', 'jobportal-framework') ?>
            <?php } else { ?>
                <span class="icon-plus"><i class="far fa-plus"></i></span>
                <?php esc_html_e('Follow', 'jobportal-framework') ?>
            <?php } ?>
        </a>
    <?php } ?>
<?php } else { ?>
    <div class="logged-out">
        <a href="#popup-form"
           class="jobportal-button button-outline-accent btn-login notice-employer add-follow-candidate <?php echo esc_attr($css_class); ?>"
           data-candidate-id="<?php echo intval($id) ?>" data-notice="<?php esc_attr_e('Please login role Employer to view', 'jobportal-framework') ?>">
            <span class="icon-plus">
                <i class="far fa-plus"></i>
            </span>
            <?php esc_html_e('Follow', 'jobportal-framework') ?>
        </a>
    </div>
<?php } ?>