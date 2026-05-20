<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!is_user_logged_in()) {
    jobportal_get_template('global/access-denied.php', array('type' => 'not_login'));
    return;
}

$jobs_id = isset($_GET['jobs_id']) ? jobportal_clean(wp_unslash($_GET['jobs_id'])) : '';


$jobs_salary_active   = jobportal_get_option('enable_single_jobs_salary', '1');
if ($jobs_salary_active) {
    $layout = array('general', 'salary', 'apply', 'company', 'location', 'additional', 'thumbnail', 'gallery', 'video');
} else {
    $layout = array('general', 'apply', 'company', 'location', 'additional', 'thumbnail', 'gallery', 'video');
}

$form     = 'edit-jobs';
$action   = 'edit_jobs';

global $jobs_data, $jobs_meta_data, $current_user, $hide_jobs_fields, $hide_jobs_group_fields;
if ($form == 'edit-jobs') {
    $jobs_data      = get_post($jobs_id);
    $jobs_meta_data = get_post_custom($jobs_data->ID);
}

$custom_field_jobs = jobportal_render_custom_field('jobs');
$jobportal_jobs_page_id  = jobportal_get_option('jobportal_jobs_dashboard_page_id', 0);
wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'jobs-submit');
wp_enqueue_script('jquery-validate');
wp_localize_script(
    JOBPORTAL_PLUGIN_PREFIX . 'jobs-submit',
    'jobportal_submit_vars',
    array(
        'ajax_url'  => JOBPORTAL_AJAX_URL,
        'not_found' => esc_html__("We didn't find any results, you can retry with other keyword.", 'jobportal-framework'),
        'not_jobs' => esc_html__('No jobs found', 'jobportal-framework'),
        'jobs_dashboard' => get_page_link($jobportal_jobs_page_id),
        'custom_field_jobs' => $custom_field_jobs,

        // AI Generate Messages
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
    )
);
wp_get_current_user();
$user_id = $current_user->ID;
$paid_submission_type = jobportal_get_option('paid_submission_type', 'no');
$user_demo = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'user_demo', $user_id);

$hide_jobs_fields = jobportal_get_option('hide_jobs_fields', array());
if (!is_array($hide_jobs_fields)) {
    $hide_jobs_fields = array();
}

$hide_jobs_group_fields = jobportal_get_option('hide_jobs_group_fields', array());
if (!is_array($hide_jobs_group_fields)) {
    $hide_jobs_group_fields = array();
}
?>
<div class="entry-my-page submit-jobs-dashboard">
    <form action="#" method="post" id="submit_jobs_form" class="form-dashboard" enctype="multipart/form-data">
        <div class="content-jobs">
            <div class="row">
                <div class="col-lg-8 col-md-7">

                    <div class="submit-jobs-header jobportal-submit-header">
                        <div class="entry-title">
                            <h4><?php esc_html_e('Edit job post', 'jobportal-framework') ?></h4>
                        </div>
                        <div class="button-wrapper">
                            <a href="<?php echo jobportal_get_permalink('jobs_dashboard'); ?>" class="jobportal-button button-link">
                                <?php esc_html_e('Cancel', 'jobportal-framework') ?>
                            </a>
                            <?php if ($user_demo == 'yes') : ?>
                                <button class="jobportal-button btn-add-to-message" data-text="<?php echo esc_attr__('This is a "Demo" account, so you can not change it', 'jobportal-framework'); ?>">
                                    <span><?php esc_html_e('Update', 'jobportal-framework'); ?></span>
                                </button>
                            <?php else : ?>
                                <button type="submit" class="btn-submit-jobs jobportal-button" name="submit_jobs">
                                    <span><?php esc_html_e('Update', 'jobportal-framework'); ?></span>
                                    <span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
                                </button>
                            <?php endif; ?>
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
                            case 'additional':
                                $name = esc_html__('Additional', 'jobportal-framework');
                                break;
                        }

                        // Check if section should be hidden when all its fields are hidden
                        $should_show_section = jobportal_is_jobs_section_visible($value, $hide_jobs_fields, $hide_jobs_group_fields);

                        if ($should_show_section) : ?>
                            <div class="block-from" id="<?php echo 'jobs-submit-' . esc_attr($value); ?>">
                                <h6><?php echo $name ?></h6>
                                <?php jobportal_get_template('jobs/edit/' . $value . '.php'); ?>
                            </div>
                    <?php endif;
                    } ?>

                    <?php wp_nonce_field('jobportal_submit_jobs_action', 'jobportal_submit_jobs_nonce_field'); ?>

                    <input type="hidden" name="jobs_form" value="<?php echo esc_attr($form); ?>" />
                    <input type="hidden" name="jobs_action" value="<?php echo esc_attr($action) ?>" />
                    <input type="hidden" name="jobs_id" value="<?php echo esc_attr($jobs_id); ?>" />
                </div>
                <div class="col-lg-4 col-md-5">
                    <div class="widget-area-init has-sticky">
                        <div class="header-about">
                            <h3 class="title-jobs-about"><?php esc_html_e('About this job', 'jobportal-framework') ?></h3>
                            <a class="jobportal-button button-outline-accent" href="<?php echo esc_url(get_permalink($jobs_id)); ?>" target="_blank">
                                <span><?php esc_html_e('View', 'jobportal-framework') ?></span>
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                        <div class="about-jobs-dashboard">
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
