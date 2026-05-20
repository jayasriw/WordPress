<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $hide_jobs_fields, $current_user;
$user_id = $current_user->ID;
$jobs_user_thumbnail_id = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'jobs_user_thumbnail_ids', true);
$jobs_user_images = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'jobs_user_images', true);
$jobs_user_image_url = wp_get_attachment_image_src($jobs_user_thumbnail_id, 'full');
jobportal_get_thumbnail_enqueue();
?>
<div class="jobs-fields-thumbnail jobportal-fields-thumbnail">
    <label><?php esc_html_e('Cover image', 'jobportal-framework'); ?></label>
    <div class="form-field">
        <div id="jobportal_thumbnail_errors" class="errors-log"></div>
        <div id="jobportal_thumbnail_container" class="file-upload-block preview">
            <div id="jobportal_thumbnail_view" data-image-id="<?php echo $jobs_user_thumbnail_id; ?>" data-image-url="<?php if (!empty($jobs_user_image_url)) {
                                                                                                                        echo $jobs_user_image_url[0];
                                                                                                                    } ?>"></div>
            <div id="jobportal_add_thumbnail">
                <i class="far fa-arrow-from-bottom large"></i>
                <p id="jobportal_drop_thumbnail">
                    <button type="button" id="jobportal_select_thumbnail"><?php esc_html_e('Click here', 'jobportal-framework') ?></button>
                    <?php esc_html_e(' or drop files to upload', 'jobportal-framework') ?>
                </p>
            </div>
            <input type="hidden" class="thumbnail_url form-control" name="jobs_thumbnail_url" value="" id="thumbnail_url">
            <input type="hidden" class="thumbnail_id" name="jobs_thumbnail_id" value="" id="thumbnail_id" />
        </div>
    </div>
</div>
<p class="jobportal-thumbnail-size"><?php esc_html_e('The cover image size should be max 1920 x 400px', 'jobportal-framework') ?></p>