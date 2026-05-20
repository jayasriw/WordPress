<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $hide_company_fields;
$image_max_file_size = jobportal_get_option('jobportal_image_max_file_size', '1000kb');
jobportal_get_gallery_enqueue();
?>
<div class="company-fields-media jobportal-fields-gallery">
    <label><?php esc_html_e('Image', 'jobportal-framework'); ?></label>
    <div class="company-fields company-fields-file company-gallery-image">
        <div class="field-media-wrapper">
            <div class="media-gallery">
                <div id="jobportal_gallery_thumbs"></div>
            </div>
            <div id="jobportal_gallery_errors" class="errors-log"></div>
            <div class="jobportal-gallery-wrapper">
                <div class="jobportal-gallery-inner">
                    <div id="jobportal_gallery_container">
                        <button type="button" id="jobportal_select_gallery" class="btn btn-primary">
                            <i class="far fa-arrow-from-bottom large"></i>
                            <?php esc_html_e('Upload ', 'jobportal-framework'); ?>
                        </button>
                    </div>
                </div>
                <div class="field-note"><?php echo sprintf(__('Maximum file size: %s.', 'jobportal-framework'), $image_max_file_size); ?></div>
            </div>
        </div>
    </div>
</div>
