<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

wp_enqueue_script('plupload');
wp_enqueue_script('jquery-validate');
wp_enqueue_script('jquery-ui-datepicker');
wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'candidate-submit');
wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'candidate');
$custom_field_candidate = jobportal_render_custom_field('candidate');
wp_localize_script(
    JOBPORTAL_PLUGIN_PREFIX . 'candidate-submit',
    'jobportal_candidate_vars',
    array(
        'ajax_url' => JOBPORTAL_AJAX_URL,
        'site_url' => get_site_url(),
        'text_present' => esc_attr__('Present', 'jobportal-framework'),
        'custom_field_candidate' => $custom_field_candidate,
        'save_strength_nonce' => wp_create_nonce('save_profile_strength'),
    )
);

global $current_user, $hide_candidate_fields, $hide_candidate_group_fields, $candidate_data, $candidate_meta_data;
wp_get_current_user();
$candidate_id = jobportal_get_post_id_candidate();
if (!empty($candidate_id)) {
    $candidate_data = get_post($candidate_id);
    $candidate_meta_data = get_post_custom($candidate_data->ID);
}
$user_id = $current_user->ID;
$user_demo = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'user_demo', $user_id);

$ajax_url = admin_url('admin-ajax.php');
$upload_nonce = wp_create_nonce('candidate_allow_upload');

$profile_strength_percent = isset($candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_profile_strength']) ? $candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_profile_strength'][0] : '';
if (empty($profile_strength_percent)) {
    $profile_strength_percent = 0;
}

$hide_candidate_fields = jobportal_get_option('hide_candidate_fields', array());
if (!is_array($hide_candidate_fields)) {
    $hide_candidate_fields = array();
}
$hide_candidate_group_fields = jobportal_get_option('hide_candidate_group_fields', array());
if (!is_array($hide_candidate_group_fields)) {
    $hide_candidate_group_fields = array();
}
$layout = apply_filters('jobportal/dashboard/candidate/profile/layout', array('info', 'education', 'experience', 'skills', 'projects', 'awards'));
?>

<div id="candidate-profile" class="candidate-profile">

    <div class="entry-my-page candidate-profile-dashboard">

        <div class="entry-title">
            <h4><?php esc_html_e('Profile Settings', 'jobportal-framework') ?></h4>
        </div>

        <div class="tab-dashboard">
            <ul class="tab-list candidate-profile-tab">
                <?php foreach ($layout as $value) {
                    switch ($value) {
                        case 'info':
                            $name = esc_html__('Basic Info', 'jobportal-framework');
                            $class = '';
                            break;
                        case 'education':
                            $name = esc_html__('Education', 'jobportal-framework');
                            $class = 'repeater';
                            break;
                        case 'experience':
                            $name = esc_html__('Experience', 'jobportal-framework');
                            $class = 'repeater';
                            break;
                        case 'skills':
                            $name = esc_html__('Skills', 'jobportal-framework');
                            $class = '';
                            break;
                        case 'projects':
                            $name = esc_html__('Projects', 'jobportal-framework');
                            $class = 'repeater ';
                            break;
                        case 'awards':
                            $name = esc_html__('Awards', 'jobportal-framework');
                            $class = 'repeater';
                            break;
                    }
                    // Check if section should be visible
                    $should_show_tab = jobportal_is_candidate_section_visible($value, $hide_candidate_fields, $hide_candidate_group_fields);

                    if ($should_show_tab) : ?>
                        <li class="tab-item <?php esc_attr_e($class); ?>"><a href="#tab-<?php esc_attr_e($value) ?>"><?php esc_html_e($name) ?></a>
                        </li>
                <?php endif;
                } ?>

                <?php $custom_field_candidate = jobportal_render_custom_field('candidate');
                if (count($custom_field_candidate) > 0) :
                    $tabs_array = array();
                    foreach ($custom_field_candidate as $field) {
                        if ((!in_array($field['section'], $tabs_array)) && !empty($field['section'])) {
                            $tabs_array[] = $field['section'];
                        }
                    }
                    foreach ($tabs_array as $value) {
                        $tabs_id = str_replace(" ", "-", $value); ?>
                        <li class="tab-item"><a href="#tab-<?php echo $tabs_id ?>"><?php echo $value; ?></a></li>
                    <?php } ?>
                <?php endif; ?>
            </ul>

            <div class="tab-content row">
                <form action="#" method="post" enctype="multipart/form-data" id="candidate-profile-form" class="candidate-profile-form form-dashboard  col-lg-8 col-md-7">
                    <input type="hidden" name="candidate_profile_strength" value="<?php esc_attr_e($profile_strength_percent) ?>">
                    <?php wp_nonce_field('candidate_submit_ajax_nonce', 'jobportal_security_candidate_submit'); ?>
                    <?php foreach ($layout as $value) {
                        switch ($value) {
                            case 'info':
                                break;
                            case 'education':
                                break;
                            case 'experience':
                                break;
                            case 'skills':
                                break;
                            case 'projects':
                                break;
                            case 'awards':
                                break;
                        }

                        // Check if section should be hidden when all its fields are hidden
                        $should_show_section = jobportal_is_candidate_section_visible($value, $hide_candidate_fields, $hide_candidate_group_fields);

                        if ($should_show_section) : ?>
                            <?php jobportal_get_template('dashboard/candidate/profile/' . $value . '.php'); ?>
                    <?php endif;
                    } ?>

                    <?php $custom_field_candidate = jobportal_render_custom_field('candidate');

                    if (count($custom_field_candidate) > 0) :
                        $sections = [];
                        foreach ($custom_field_candidate as $field) {
                            if (!empty($field['section'])) {
                                if (in_array($field['section'], $sections)) {
                                    continue;
                                }

                                $sections[] = $field['section'];
                                $tabs_id = str_replace(" ", "-", $field['section']); ?>
                                <div id="tab-<?php echo $tabs_id; ?>" class="tab-info block-from">
                                    <h5><?php echo $field['section']; ?></h5>
                                    <?php jobportal_custom_field_candidate($field['section'], true); ?>
                                </div>
                        <?php }
                        } ?>
                    <?php endif; ?>

                    <div class="button-wrapper">
                        <a href="<?php echo jobportal_get_permalink('candidate_dashboard'); ?>" class="jobportal-button button-outline">
                            <?php esc_html_e('Cancel', 'jobportal-framework') ?>
                        </a>
                        <?php if ($user_demo == 'yes') : ?>
                            <button class="jobportal-button btn-add-to-message" data-text="<?php echo esc_attr__('This is a "Demo" account, so you can not change it', 'jobportal-framework'); ?>">
                                <span><?php esc_html_e('Update', 'jobportal-framework'); ?></span>
                            </button>
                        <?php else : ?>
                            <button type="submit" class="btn-update-profile jobportal-button" name="submit_profile">
                                <span><?php esc_html_e('Update', 'jobportal-framework'); ?></span>
                                <span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
                            </button>
                        <?php endif; ?>
                    </div>
                </form>

                <div class="candidate-profile-strength col-lg-4 col-md-5">
                    <div class="has-sticky">
                        <div class="profile-strength tip" style="--pct: <?php echo esc_attr($profile_strength_percent) ?>">
                            <h1><span><?php echo esc_attr($profile_strength_percent) ?></span><span>%</span></h1>
                            <div><?php esc_html_e('Profile Strength', 'jobportal-framework') ?></div>
                            <div class="tip-content post-bottom">
                                <ul class="profile-list-check">
                                    <?php foreach ($layout as $value) {
                                        switch ($value) {
                                            case 'info':
                                                $name = esc_html__('Basic Info', 'jobportal-framework');
                                                break;
                                            case 'education':
                                                $name = esc_html__('Education', 'jobportal-framework');
                                                break;
                                            case 'experience':
                                                $name = esc_html__('Experience', 'jobportal-framework');
                                                break;
                                            case 'skills':
                                                $name = esc_html__('Skills', 'jobportal-framework');
                                                break;
                                            case 'projects':
                                                $name = esc_html__('Projects', 'jobportal-framework');
                                                break;
                                            case 'awards':
                                                $name = esc_html__('Awards', 'jobportal-framework');
                                                break;
                                        }
                                        // Check if section should be visible
                                        $should_show_check = jobportal_is_candidate_section_visible($value, $hide_candidate_fields, $hide_candidate_group_fields);

                                        if ($should_show_check) : ?>
                                            <li class="profile-check-item" id="<?php echo 'profile-check-' . $value ?>" data-has-check="<?php echo sprintf(__('%s has enough information', 'jobportal-framework'), $name); ?>" data-not-check="<?php echo sprintf(__('%s not enough information', 'jobportal-framework'), $name); ?>">
                                                <i class="fas fa-check"></i>
                                                <span><?php echo sprintf(__('%s not enough information', 'jobportal-framework'), $name); ?></span>
                                            </li>
                                    <?php endif;
                                    } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
