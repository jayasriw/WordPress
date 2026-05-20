<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $hide_company_fields, $current_user;
$image_max_file_size = jobportal_get_option('jobportal_image_max_file_size', '1000kb');
jobportal_get_thumbnail_enqueue();
jobportal_get_avatar_enqueue();
?>
<div class="company-fields-wrapper">
    <?php if (!in_array('fields_closing_logo', $hide_company_fields)) : ?>
    <div class="company-fields-avatar jobportal-fields-avatar">
        <label><?php esc_html_e('Logo', 'jobportal-framework'); ?></label>
        <div class="form-field">
            <div id="jobportal_avatar_errors" class="errors-log"></div>
            <div id="jobportal_avatar_container" class="file-upload-block preview">
                <div id="jobportal_avatar_view"></div>
                <div id="jobportal_add_avatar">
                    <i class="far fa-arrow-from-bottom large"></i>
                    <p id="jobportal_drop_avatar">
                        <button type="button" id="jobportal_select_avatar"><?php esc_html_e('Upload', 'jobportal-framework') ?></button>
                    </p>
                </div>
                <input type="hidden" class="avatar_url form-control" name="company_avatar_url" value="" id="avatar_url">
                <input type="hidden" class="avatar_id" name="company_avatar_id" value="" id="avatar_id" />
            </div>
        </div>
        <div class="field-note"><?php echo sprintf(__('Maximum file size: %s.', 'jobportal-framework'), $image_max_file_size); ?></div>
    </div>
    <?php endif; ?>

    <?php if (!in_array('fields_company_thumbnail', $hide_company_fields)) : ?>
    <div class="company-fields-thumbnail jobportal-fields-thumbnail">
        <label><?php esc_html_e('Cover image', 'jobportal-framework'); ?></label>
        <div class="form-field">
            <div id="jobportal_thumbnail_errors" class="errors-log"></div>
            <div id="jobportal_thumbnail_container" class="file-upload-block preview">
                <div id="jobportal_thumbnail_view"></div>
                <div id="jobportal_add_thumbnail">
                    <i class="far fa-arrow-from-bottom large"></i>
                    <p id="jobportal_drop_thumbnail">
                        <button type="button" id="jobportal_select_thumbnail"><?php esc_html_e('Click here', 'jobportal-framework') ?></button>
                        <?php esc_html_e(' or drop files to upload', 'jobportal-framework') ?>
                    </p>
                </div>
                <input type="hidden" class="thumbnail_url form-control" name="company_thumbnail_url" value="" id="thumbnail_url">
                <input type="hidden" class="thumbnail_id" name="company_thumbnail_id" value="" id="thumbnail_id" />
            </div>
        </div>
        <p class="jobportal-thumbnail-size"><?php esc_html_e('The cover image size should be max 1920 x 400px', 'jobportal-framework') ?></p>
    </div>
    <?php endif; ?>
</div>
