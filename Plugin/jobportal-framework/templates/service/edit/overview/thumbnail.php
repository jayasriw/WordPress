<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $service_data, $hide_service_fields, $current_user;
$image_max_file_size = jobportal_get_option('jobportal_image_max_file_size', '1000kb');
$service_thumbnail_id = get_post_thumbnail_id($service_data->ID);
$service_thumbnail_id = !empty($service_thumbnail_id) ? $service_thumbnail_id : '';
$service_image_url = get_the_post_thumbnail_url($service_data->ID, 'full');
$service_image_url = !empty($service_image_url) ? $service_image_url : '';
jobportal_get_thumbnail_enqueue();
?>
<?php if (!in_array('fields_service_cover_image', $hide_service_fields)) : ?>
    <div class="service-fields-wrapper">
        <div class="service-fields-thumbnail jobportal-fields-thumbnail">
            <label><?php esc_html_e('Cover image', 'jobportal-framework'); ?></label>
            <div class="form-field">
                <div id="jobportal_thumbnail_errors" class="errors-log"></div>
                <div id="jobportal_thumbnail_container" class="file-upload-block preview">
                    <div id="jobportal_thumbnail_view" data-image-id="<?php echo $service_thumbnail_id; ?>" data-image-url="<?php echo $service_image_url; ?>"></div>
                    <div id="jobportal_add_thumbnail">
                        <i class="far fa-arrow-from-bottom large"></i>
                        <p id="jobportal_drop_thumbnail">
                            <button type="button"
                                    id="jobportal_select_thumbnail"><?php esc_html_e('Click here', 'jobportal-framework') ?></button>
                            <?php esc_html_e(' or drop files to upload', 'jobportal-framework') ?>
                        </p>
                    </div>
                    <input type="hidden" class="thumbnail_url form-control" name="service_thumbnail_url" value="<?php echo $service_image_url; ?>"
                           id="thumbnail_url">
                    <input type="hidden" class="thumbnail_id" name="service_thumbnail_id" value="<?php echo $service_thumbnail_id; ?>" id="thumbnail_id"/>
                </div>
            </div>
            <p class="jobportal-thumbnail-size"><?php esc_html_e('The cover image size should be max 1920 x 400px', 'jobportal-framework') ?></p>
        </div>
    </div>
<?php endif; ?>
