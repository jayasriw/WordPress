<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $hide_candidate_fields, $candidate_data, $candidate_meta_data;
$candidate_resume = isset($candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_resume_id_list']) ? $candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_resume_id_list'][0] : '';
$filename = basename(get_attached_file($candidate_resume));
$ajax_url = admin_url('admin-ajax.php');
$cv_file = jobportal_get_option('jobportal-cv-type');
$cv_max_file_size = jobportal_get_option('jobportal_image_max_file_size', '1000kb');

$upload_nonce = wp_create_nonce('jobportal_thumbnail_allow_upload');
$url = JOBPORTAL_AJAX_URL . '?action=jobportal_thumbnail_upload_ajax&nonce=' . esc_attr($upload_nonce);
$text = '<i class="far fa-arrow-from-bottom large"></i> ' . esc_html__('Browse', 'jobportal-framework');


wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'upload-cv');
wp_localize_script(
    JOBPORTAL_PLUGIN_PREFIX . 'upload-cv',
    'jobportal_upload_cv_vars',
    array(
        'ajax_url' => $ajax_url,
        'title' => esc_html__('Valid file formats', 'jobportal-framework'),
        'cv_file' => $cv_file,
        'cv_max_file_size' => $cv_max_file_size,
        'upload_nonce' => $upload_nonce,
        'url' => $url,
        'text' => $text,
    )
);
$cv_file = jobportal_get_option('jobportal-cv-type');
?>
<?php if (!in_array('fields_candidate_resume', $hide_candidate_fields)) : ?>
<div class="resume block-from">
    <h6><?php esc_html_e('Resume', 'jobportal-framework') ?></h6>
    <div class="candidate-resume">
        <div class="form-group col-md-12 jobportal-upload-cv">
            <label for="jobportal_select_cv"><?php esc_html_e('CV Attachment', 'jobportal-framework'); ?></label>
            <div class="form-field">
                <div id="cv_errors_log" class="errors-log"></div>
                <div id="jobportal_cv_plupload_container" class="file-upload-block preview">
                    <div class="jobportal_cv_file jobportal_add-cv">
                        <p id="jobportal_drop_cv" data-attachment-id="<?php echo esc_attr($candidate_resume); ?>">
                            <button class="jobportal-button" type="button" id="jobportal_select_cv">
                                <i class="far fa-arrow-from-bottom large"></i>
                                <?php if (!empty($candidate_resume)) { ?>
                                    <span><?php esc_html_e($filename); ?></span>
                                <?php } else { ?>
                                    <span><?php esc_html_e('Browse', 'jobportal-framework'); ?></span>
                                <?php } ?>
                            </button>
                            <?php if (!empty($candidate_resume)) { ?>
                                <a class="icon cv-icon-delete" data-attachment-id="<?php esc_attr_e($candidate_resume) ?>" href="#"><i class="far fa-trash-alt large"></i></a>
                            <?php } ?>
                        </p>
                    </div>
                    <span class="file-type"><?php echo esc_attr(sprintf(esc_html__('Upload file: %s', 'jobportal-framework'), $cv_file)); ?></span>
                </div>
            </div>
        </div>

    </div>
</div>
<?php endif; ?>
