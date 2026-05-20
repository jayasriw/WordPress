<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $company_data, $company_meta_data, $hide_company_fields;
$company_logo_arg = get_post_meta($company_data->ID, JOBPORTAL_METABOX_PREFIX . 'company_logo', false);
$company_logo_id = isset($company_logo_arg[0]['id']) ? $company_logo_arg[0]['id'] : '';
$company_logo_url = isset($company_logo_arg[0]['url']) ? $company_logo_arg[0]['url'] : '';
$company_thumbnail_id = get_post_thumbnail_id($company_data->ID);
$company_thumbnail_id = !empty($company_thumbnail_id) ? $company_thumbnail_id : '';
$company_image_url = get_the_post_thumbnail_url($company_data->ID, 'full');
$company_image_url = !empty($company_image_url) ? $company_image_url : '';
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
                <div id="jobportal_avatar_view" data-image-id="<?php echo esc_attr($company_logo_id); ?>" data-image-url="<?php echo esc_url($company_logo_url); ?>"></div>
                <div id="jobportal_add_avatar">
                    <i class="far fa-arrow-from-bottom large"></i>
                    <p id="jobportal_drop_avatar">
                        <button type="button" id="jobportal_select_avatar"><?php esc_html_e('Upload', 'jobportal-framework') ?></button>
                    </p>
                </div>
                <input type="hidden" class="avatar_url form-control" name="company_avatar_url" value="<?php echo esc_url($company_logo_url); ?>" id="avatar_url">
                <input type="hidden" class="avatar_id" name="company_avatar_id" value="<?php echo esc_attr($company_logo_id); ?>" id="avatar_id" />
            </div>
        </div>
        <div class="field-note"><?php echo sprintf(__('Maximum file size: %s.', 'jobportal-framework'), esc_html($image_max_file_size)); ?></div>
    </div>
    <?php endif; ?>

    <?php if (!in_array('fields_company_thumbnail', $hide_company_fields)) : ?>
    <div class="company-fields-thumbnail jobportal-fields-thumbnail">
        <label><?php esc_html_e('Cover image', 'jobportal-framework'); ?></label>
        <div class="form-field">
            <div id="jobportal_thumbnail_errors" class="errors-log"></div>
            <div id="jobportal_thumbnail_container" class="file-upload-block preview">
                <div id="jobportal_thumbnail_view" data-image-id="<?php echo esc_attr($company_thumbnail_id); ?>" data-image-url="<?php echo esc_url($company_image_url); ?>"></div>
                <div id="jobportal_add_thumbnail">
                    <i class="far fa-arrow-from-bottom large"></i>
                    <p id="jobportal_drop_thumbnail">
                        <button type="button" id="jobportal_select_thumbnail"><?php esc_html_e('Click here', 'jobportal-framework') ?></button>
                        <?php esc_html_e(' or drop files to upload', 'jobportal-framework') ?>
                    </p>
                </div>
                <input type="hidden" class="thumbnail_url form-control" name="company_thumbnail_url" value="<?php echo esc_url($company_image_url); ?>" id="thumbnail_url">
                <input type="hidden" class="thumbnail_id" name="company_thumbnail_id" value="<?php echo esc_attr($company_thumbnail_id); ?>" id="thumbnail_id" />
            </div>
        </div>
        <p class="jobportal-thumbnail-size"><?php esc_html_e('The cover image size should be max 1920 x 400px', 'jobportal-framework') ?></p>
    </div>
    <?php endif; ?>
</div>
