<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $hide_service_fields, $current_user;
$image_max_file_size = civi_get_option('civi_image_max_file_size', '1000kb');
civi_get_thumbnail_enqueue();
?>
<?php if (!in_array('fields_service_cover_image', $hide_service_fields)) : ?>
    <div class="service-fields-wrapper">
        <div class="service-fields-thumbnail civi-fields-thumbnail">
            <label><?php esc_html_e('Cover image', 'civi-framework'); ?></label>
            <div class="form-field">
                <div id="civi_thumbnail_errors" class="errors-log"></div>
                <div id="civi_thumbnail_container" class="file-upload-block preview">
                    <div id="civi_thumbnail_view"></div>
                    <div id="civi_add_thumbnail">
                        <i class="far fa-arrow-from-bottom large"></i>
                        <p id="civi_drop_thumbnail">
                            <button type="button"
                                    id="civi_select_thumbnail"><?php esc_html_e('Click here', 'civi-framework') ?></button>
                            <?php esc_html_e(' or drop files to upload', 'civi-framework') ?>
                        </p>
                    </div>
                    <input type="hidden" class="thumbnail_url form-control" name="service_thumbnail_url" value=""
                           id="thumbnail_url">
                    <input type="hidden" class="thumbnail_id" name="service_thumbnail_id" value="" id="thumbnail_id"/>
                </div>
            </div>
            <p class="civi-thumbnail-size"><?php esc_html_e('The cover image size should be max 1920 x 400px', 'civi-framework') ?></p>
        </div>
    </div>
<?php endif; ?>
