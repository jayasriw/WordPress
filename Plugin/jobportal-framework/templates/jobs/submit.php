<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$custom_field_jobs = jobportal_render_custom_field('jobs');
$jobportal_jobs_page_id = jobportal_get_option('jobportal_jobs_dashboard_page_id', 0);
wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'jobs-submit');
wp_enqueue_script('jquery-validate');
wp_localize_script(
    JOBPORTAL_PLUGIN_PREFIX . 'jobs-submit',
    'jobportal_submit_vars',
    array(
        'ajax_url' => JOBPORTAL_AJAX_URL,
        'not_found' => esc_html__("We didn't find any results, you can retry with other keyword.", 'jobportal-framework'),
        'not_jobs' => esc_html__('No jobs found', 'jobportal-framework'),
        'generate' => esc_html__('Generate', 'jobportal-framework'),
        'regenerate' => esc_html__('Regenerate', 'jobportal-framework'),
        'generating' => esc_html__('Generating...', 'jobportal-framework'),
        'jobs_dashboard' => get_page_link($jobportal_jobs_page_id),
        'custom_field_jobs' => $custom_field_jobs,
        'ai_nonce' => wp_create_nonce('jobportal_ai_generate_nonce'),
        // AI Messages
        'regenerate' => esc_html__('Regenerate', 'jobportal-framework'),
        'generate' => esc_html__('Generate', 'jobportal-framework'),
        'enter_description' => esc_html__('Please enter a description.', 'jobportal-framework'),
        'min_length' => esc_html__('The description should be at least 20 characters.', 'jobportal-framework'),
        'max_length' => esc_html__('Description too long. Please keep under 2000 characters.', 'jobportal-framework'),
        'avoid_sensitive' => esc_html__('Please avoid sensitive personal information.', 'jobportal-framework'),
        'replace_placeholder' => esc_html__('Please replace placeholder text with actual information.', 'jobportal-framework'),
        'select_tone' => esc_html__('Please select a tone.', 'jobportal-framework'),
        'select_language' => esc_html__('Please select a language.', 'jobportal-framework'),
        'connection_error' => esc_html__('Connection error. Please try again.', 'jobportal-framework'),
        'timeout_warning' => esc_html__('Still working... The AI helper is taking longer than expected.', 'jobportal-framework'),
        'timeout_error' => esc_html__('The AI helper took too long to respond. Please try again.', 'jobportal-framework'),
        'ai_timeout' => (int) jobportal_get_option('ai_timeout_limit', 120000),
        'ai_timeout_warning' => (int) jobportal_get_option('ai_timeout_warning_limit', 45000),
        // Theme options for price formatting
        'currency_position' => jobportal_get_option('currency_position'),
        'salary_text_minimum' => jobportal_get_option('salary_text_minimum', esc_html__('Min: ', 'jobportal-framework')),
        'salary_text_maximum' => jobportal_get_option('salary_text_maximum', esc_html__('Max: ', 'jobportal-framework')),
        'salary_text_negotiable' => jobportal_get_option('salary_text_negotiable', esc_html__('Negotiable Price', 'jobportal-framework')),
        'thousand_separator' => jobportal_get_option('thousand_separator', ','),
        'decimal_separator' => jobportal_get_option('decimal_separator', '.'),
        // Debug: show actual values
        'debug_thousand_orig' => jobportal_get_option('thousand_separator'),
        'debug_thousand_empty' => empty(jobportal_get_option('thousand_separator')) ? 'YES' : 'NO',
    )
);
$form = 'submit-jobs';
$action = 'add_jobs';
$jobs_id = get_the_ID();

global $current_user, $hide_jobs_fields, $hide_jobs_group_fields;
$hide_jobs_fields = jobportal_get_option('hide_jobs_fields', array());
if (!is_array($hide_jobs_fields)) {
    $hide_jobs_fields = array();
}
$hide_jobs_group_fields = jobportal_get_option('hide_jobs_group_fields', array());
if (!is_array($hide_jobs_group_fields)) {
    $hide_jobs_group_fields = array();
}
$jobs_salary_active   = jobportal_get_option('enable_single_jobs_salary', '1');
if ($jobs_salary_active) {
    $layout = array('general', 'salary', 'apply', 'company', 'location', 'thumbnail', 'gallery', 'video');
} else {
    $layout = array('general', 'apply', 'company', 'location', 'thumbnail', 'gallery', 'video');
}

wp_get_current_user();
$user_id = $current_user->ID;
$paid_submission_type = jobportal_get_option('paid_submission_type', 'no');
$user_package_id = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'package_id', $user_id);
$package_number_job = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'package_number_job', $user_id);
$package_unlimited_job = get_post_meta($user_package_id, JOBPORTAL_METABOX_PREFIX . 'package_unlimited_job', true);
$notice_text = $shortcode = '';

$jobportal_package = new JobPortal_Package();
$get_expired_date = $jobportal_package->get_expired_date($user_package_id, $user_id);
$current_date = date('Y-m-d');

$d1 = strtotime($get_expired_date);
$d2 = strtotime($current_date);

if ($get_expired_date === 'Never Expires' || $get_expired_date === 'Unlimited') {
    $d1 = 999999999999999999999999;
}

if ($paid_submission_type == 'no') {
    if (in_array('jobportal_user_candidate', (array)$current_user->roles)) {
        $notice_text = esc_html__("Sorry, you can't view this page as Candidate, register Employer account to get access.", 'jobportal-framework');
    }
} else {
    if (in_array('jobportal_user_candidate', (array)$current_user->roles)) {
        $notice_text = esc_html__("Sorry, you can't view this page as Candidate, register Employer account to get access.", 'jobportal-framework');
    } elseif ((in_array('jobportal_user_employer', (array)$current_user->roles) && $user_package_id == '') || $d1 < $d2) {
        $notice_text = esc_html__("You have not purchased the package. Please choose 1 of the packages now.", 'jobportal-framework');
        $shortcode = '1';
    } elseif (in_array('jobportal_user_employer', (array)$current_user->roles) && $package_number_job < 1 && $package_unlimited_job != '1') {
        $notice_text = esc_html__("The package you selected has reached its allowable limit. Please come back later!", 'jobportal-framework');
    }
}

$has_package = true;
if ($paid_submission_type == 'per_package') {
    $current_package_key = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'package_key', $user_id);
    $jobs_package_key = get_post_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'package_key', true);
    $jobportal_profile = new JobPortal_Profile();
    $check_package = $jobportal_profile->user_package_available($user_id);
    if (($check_package == -1) || ($check_package == 0)) {
        $has_package = false;
    }
}
?>

<?php if (!empty($notice_text)) { ?>
    <p class="notice"><i class="fal fa-exclamation-circle"></i><?php echo $notice_text; ?></p>
    <?php
    if ($shortcode == '1') {
        echo do_shortcode('[jobportal_package]');
    }
    ?>
<?php } else { ?>
    <div class="entry-my-page submit-jobs-dashboard">
        <form action="#" method="post" id="submit_jobs_form" class="form-dashboard" enctype="multipart/form-data" data-titleerror="<?php echo esc_html__('Please enter jobs name', 'jobportal-framework'); ?>" data-deserror="<?php echo esc_html__('Please enter jobs description', 'jobportal-framework'); ?>" data-caterror="<?php echo esc_html__('Please choose category', 'jobportal-framework'); ?>" data-typeerror="<?php echo esc_html__('Please choose type', 'jobportal-framework'); ?>" data-skillserror="<?php echo esc_html__('Please choose skills', 'jobportal-framework'); ?>">
            <div class="content-jobs">
                <div class="row">
                    <div class="col-lg-8 col-md-7">
                        <div class="submit-jobs-header jobportal-submit-header">
                            <div class="entry-title">
                                <h4><?php esc_html_e('Create a job post', 'jobportal-framework') ?></h4>
                            </div>
                            <div class="button-wrapper">
                                <a href="<?php echo jobportal_get_permalink('jobs_dashboard'); ?>" class="jobportal-button button-link">
                                    <?php esc_html_e('Cancel', 'jobportal-framework') ?>
                                </a>
                                <?php if (($has_package && $package_number_job > 0) || $paid_submission_type !== 'per_package') { ?>
                                    <button type="submit" class="btn-submit-draft jobportal-button button-outline" name="submit_draft">
                                        <?php esc_html_e('Save as draft', 'jobportal-framework') ?>
                                        <span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
                                    </button>
                                    <button type="submit" class="btn-submit-jobs jobportal-button" name="submit_jobs">
                                        <span><?php esc_html_e('Post job', 'jobportal-framework'); ?></span>
                                        <span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
                                    </button>
                                <?php } else { ?>
                                    <a class="jobportal-button package-out-stock" href="<?php echo jobportal_get_permalink('package'); ?>"><?php esc_html_e('Upgrade now', 'jobportal-framework'); ?></a>
                                <?php } ?>
                            </div>
                        </div>
                        <?php foreach ($layout as $value) {
                            switch ($value) {
                                case 'general':
                                    $name = esc_html__('Basic info', 'jobportal-framework');
                                    break;
                                case 'salary':
                                    $name = esc_html__('Salary', 'jobportal-framework');
                                    break;
                                case 'apply':
                                    $name = esc_html__('Job apply type', 'jobportal-framework');
                                    break;
                                case 'company':
                                    $name = esc_html__('Company', 'jobportal-framework');
                                    break;
                                case 'location':
                                    $name = esc_html__('Location', 'jobportal-framework');
                                    break;
                                case 'thumbnail':
                                    $name = esc_html__('Cover Image', 'jobportal-framework');
                                    break;
                                case 'gallery':
                                    $name = esc_html__('Gallery', 'jobportal-framework');
                                    break;
                                case 'video':
                                    $name = esc_html__('Video', 'jobportal-framework');
                                    break;
                            }

                            // Check if section should be hidden when all its fields are hidden
                            $should_show_section = jobportal_is_jobs_section_visible($value, $hide_jobs_fields, $hide_jobs_group_fields);

                            if ($should_show_section) : ?>
                                <div class="block-from" id="<?php echo 'jobs-submit-' . esc_attr($value); ?>">
                                    <h6><?php echo $name ?></h6>
                                    <?php jobportal_get_template('jobs/submit/' . $value . '.php'); ?>
                                </div>
                        <?php endif;
                        } ?>

                        <?php $custom_field_jobs = jobportal_render_custom_field('jobs');
                        if (count($custom_field_jobs) > 0) : ?>
                            <div class="block-from" id="jobs-submit-additional">
                                <h6><?php echo esc_html__('Additional', 'jobportal-framework'); ?></h6>
                                <?php jobportal_get_template('jobs/submit/additional.php'); ?>
                            </div>
                        <?php endif; ?>

                        <?php wp_nonce_field('jobportal_submit_jobs_action', 'jobportal_submit_jobs_nonce_field'); ?>

                        <input type="hidden" name="jobs_form" value="<?php echo esc_attr($form); ?>" />
                        <input type="hidden" name="jobs_action" value="<?php echo esc_attr($action) ?>" />
                        <input type="hidden" name="jobs_id" value="<?php echo esc_attr($jobs_id); ?>" />
                    </div>
                    <div class="col-lg-4 col-md-5">
                        <div class="widget-area-init has-sticky">
                            <h3 class="title-jobs-about"><?php esc_html_e('About this job', 'jobportal-framework') ?></h3>
                            <div class="about-jobs-dashboard block-archive-sidebar">
                                <div class="img-company"><i class="far fa-camera"></i></div>
                                <h4 class="title-about" data-title="<?php esc_attr_e('Title of job', 'jobportal-framework') ?>"><?php esc_html_e('Title of job', 'jobportal-framework') ?></h4>
                                <div class="info-jobs-wrapper">
                                    <?php esc_html_e('by', 'jobportal-framework'); ?>
                                    <span class="name-company" data-name="<?php esc_attr_e('Company Name', 'jobportal-framework') ?>"><?php esc_html_e('Company Name', 'jobportal-framework'); ?></span>
                                    <?php esc_html_e('in', 'jobportal-framework'); ?>
                                    <span class="cate-about" data-cate="<?php esc_attr_e('Category', 'jobportal-framework') ?>"><?php esc_html_e('Category', 'jobportal-framework'); ?></span>
                                    <div class="label-wrapper">
                                        <span class="label-type-inner"></span>
                                        <span class="label-location-inner"></span>
                                    </div>
                                    <?php
                                    if ($jobs_salary_active) {
                                        // Get text from theme options or use defaults
                                        $text_min = jobportal_get_option('salary_text_minimum', esc_html__('Minimum: ', 'jobportal-framework'));
                                        $text_max = jobportal_get_option('salary_text_maximum', esc_html__('Maximum: ', 'jobportal-framework'));
                                        $text_agree = jobportal_get_option('salary_text_negotiable', esc_html__('Negotiable Price', 'jobportal-framework'));

                                        // Ensure none are empty
                                        if (empty($text_min)) $text_min = esc_html__('Minimum: ', 'jobportal-framework');
                                        if (empty($text_max)) $text_max = esc_html__('Maximum: ', 'jobportal-framework');
                                        if (empty($text_agree)) $text_agree = esc_html__('Negotiable Price', 'jobportal-framework');

                                        echo '<div class="label label-price" data-text-min="' . esc_attr($text_min) . '" data-text-max="' . esc_attr($text_max) . '" data-text-agree="' . esc_attr($text_agree) . '">' . esc_html($text_agree) . '</div>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php do_action('after_post_job_form'); ?>

<?php } ?>
