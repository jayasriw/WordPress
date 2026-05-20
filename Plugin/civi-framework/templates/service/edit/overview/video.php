<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $service_meta_data,$hide_service_fields;
?>
<?php if (!in_array('fields_service_video', $hide_service_fields)) : ?>
    <div class="form-group col-md-12">
        <label for="service_video_url"><?php esc_html_e('Introduction video Url', 'civi-framework') ?></label>
        <input type="url" id="service_video_url" name="service_video_url" value="<?php if (isset($service_meta_data[CIVI_METABOX_PREFIX . 'service_video_url'][0])) {
            echo $service_meta_data[CIVI_METABOX_PREFIX . 'service_video_url'][0];
        } ?>" placeholder="<?php esc_attr_e('Enter url video', 'civi-framework') ?>">
    </div>
<?php endif; ?>