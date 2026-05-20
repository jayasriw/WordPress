<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $hide_service_fields;

?>
<?php if (!in_array('fields_service_video', $hide_service_fields)) : ?>
    <div class="form-group col-md-12">
        <label for="service_video_url"><?php esc_html_e('Introduction video Url', 'jobportal-framework') ?></label>
        <input type="url" id="service_video_url" name="service_video_url"
               placeholder="<?php esc_attr_e('Enter url video', 'jobportal-framework') ?>">
    </div>
<?php endif; ?>