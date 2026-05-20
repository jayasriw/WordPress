<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $service_data, $service_meta_data, $hide_service_fields;
$currency_sign_default = civi_get_option('currency_sign_default');
$service_price = isset($service_meta_data[CIVI_METABOX_PREFIX . 'service_price']) ? $service_meta_data[CIVI_METABOX_PREFIX . 'service_price'][0] : '';
$service_time = isset($service_meta_data[CIVI_METABOX_PREFIX . 'service_number_time']) ? $service_meta_data[CIVI_METABOX_PREFIX . 'service_number_time'][0] : '';
$service_time_type = isset($service_meta_data[CIVI_METABOX_PREFIX . 'service_time_type']) ? $service_meta_data[CIVI_METABOX_PREFIX . 'service_time_type'][0] : '';
$service_language_level = isset($service_meta_data[CIVI_METABOX_PREFIX . 'service_language_level']) ? $service_meta_data[CIVI_METABOX_PREFIX . 'service_language_level'][0] : '';
$enable_candidate_service_fee = civi_get_option('enable_candidate_service_fee');
$candidate_number_service_fee = civi_get_option('candidate_number_service_fee');
?>
<div class="row">
    <?php if (!in_array('fields_service_title', $hide_service_fields)) : ?>
        <div class="form-group col-md-12">
            <label for="service_title"><?php esc_html_e('Service title', 'civi-framework') ?> <sup>*</sup></label>
            <input type="text" id="service_title" name="service_title"
                   placeholder="<?php esc_attr_e('Enter title', 'civi-framework') ?>"
                   value="<?php print sanitize_text_field($service_data->post_title); ?>">
        </div>
    <?php endif; ?>
    <?php if (!in_array('fields_service_category', $hide_service_fields)) : ?>
        <div class="form-group col-md-12">
            <label><?php esc_html_e('Service Categories', 'civi-framework') ?> <sup>*</sup></label>
            <div class="select2-field">
                <select name="service_categories" class="civi-select2">
                    <?php civi_get_taxonomy_by_post_id($service_data->ID, 'service-categories', true, false, true, 'service_categories_order'); ?>
                </select>
            </div>
        </div>
    <?php endif; ?>
    <?php if (!in_array('fields_service_price', $hide_service_fields)) : ?>
        <div class="form-group col-md-6">
            <label for="service_price"><?php echo sprintf(esc_html__('Service Price (%s)', 'civi-framework'), $currency_sign_default) ?> <sup>*</sup></label>
            <input type="number" id="service_price" name="service_price" pattern="[-+]?[0-9]" placeholder="0"
                   value="<?php echo $service_price; ?>">
        </div>
        <?php if ($enable_candidate_service_fee === '1' && !empty($candidate_number_service_fee)) :
            $candidate_number_service_fee = $candidate_number_service_fee . '%'; ?>
            <div class="form-group col-md-6">
                <label for="price_received"><?php echo sprintf(esc_html__('Price received (After %s fee)', 'civi-framework'), $candidate_number_service_fee) ?></label>
                <input type="number" id="price_received" name="price_received" pattern="[-+]?[0-9]" placeholder="0" data-price-received="<?php echo $candidate_number_service_fee;?>" disabled>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    <?php if (!in_array('fields_service_delivery_time', $hide_service_fields)) : ?>
        <div class="form-group col-md-6">
            <label for="service_time"><?php esc_html_e('Transfer time', 'civi-framework'); ?> <sup>*</sup></label>
            <input type="number" id="service_time" name="service_time" pattern="[-+]?[0-9]" placeholder="0"
                   value="<?php echo $service_time; ?>">
        </div>
        <div class="form-group col-md-6">
            <label><?php esc_html_e('Time Type', 'civi-framework') ?> </label>
            <div class="select2-field">
                <select name="service_time_type" class="civi-select2">
                    <option value="hr" <?php if ($service_time_type == 'hr') {
                        echo 'selected';
                    } ?>><?php esc_html_e('Hour', 'civi-framework'); ?></option>
                    <option value="day" <?php if ($service_time_type == 'day') {
                        echo 'selected';
                    } ?>><?php esc_html_e('Day', 'civi-framework'); ?></option>
                    <option value="week" <?php if ($service_time_type == 'week') {
                        echo 'selected';
                    } ?>><?php esc_html_e('Week', 'civi-framework'); ?></option>
                    <option value="month" <?php if ($service_time_type == 'month') {
                        echo 'selected';
                    } ?>><?php esc_html_e('Month', 'civi-framework'); ?></option>
                </select>
            </div>
        </div>
    <?php endif; ?>
    <?php if (!in_array('fields_service_description', $hide_service_fields)) : ?>
        <div class="form-group col-md-12">
            <label class="label-des-service"><?php esc_html_e('Description', 'civi-framework'); ?>
                <sup>*</sup></label>
            <?php
            $content = $service_data->post_content;
            $editor_id = 'service_des';
            $settings = array(
                'wpautop' => true,
                'media_buttons' => false,
                'textarea_name' => $editor_id,
                'textarea_rows' => get_option('default_post_edit_rows', 8),
                'tabindex' => '',
                'editor_css' => '',
                'editor_class' => '',
                'teeny' => false,
                'dfw' => false,
                'tinymce' => true,
                'quicktags' => true
            );
            wp_editor(html_entity_decode(stripcslashes($content)), $editor_id, $settings); ?>
        </div>
    <?php endif; ?>
    <?php if (!in_array('fields_service_languages', $hide_service_fields)) : ?>
        <div class="form-group col-md-6">
            <label><?php esc_html_e('Languages', 'civi-framework') ?></label>
            <div class="form-select">
                <div class="select2-field select2-multiple">
                    <select data-placeholder="<?php esc_attr_e('Select language', 'civi-framework'); ?>"
                            multiple="multiple"
                            class="civi-select2" name="service_languages">
                        <?php civi_get_taxonomy_by_post_id($service_data->ID, 'service-language', false, false, true, 'service_language_order'); ?>
                    </select>
                </div>
                <i class="fas fa-angle-down"></i>
            </div>
        </div>
    <?php endif; ?>
    <?php if (!in_array('fields_service_language_level', $hide_service_fields)) : ?>
        <div class="form-group col-md-6">
            <label><?php esc_html_e('Languages level', 'civi-framework') ?></label>
            <div class="select2-field">
                <select name="service_languages_level" class="civi-select2">
                    <option value="basic" <?php if ($service_language_level == 'basic') {
                        echo 'selected';
                    } ?>><?php esc_html_e('Basic', 'civi-framework'); ?></option>
                    <option value="conversational" <?php if ($service_language_level == 'conversational') {
                        echo 'selected';
                    } ?>><?php esc_html_e('Conversational', 'civi-framework'); ?></option>
                    <option value="fluent" <?php if ($service_language_level == 'fluent') {
                        echo 'selected';
                    } ?>><?php esc_html_e('Fluent', 'civi-framework'); ?></option>
                    <option value="native" <?php if ($service_language_level == 'native') {
                        echo 'selected';
                    } ?>><?php esc_html_e('Native or Bilingual', 'civi-framework'); ?></option>
                    <option value="professional" <?php if ($service_language_level == 'professional') {
                        echo 'selected';
                    } ?>><?php esc_html_e('Professional', 'civi-framework'); ?></option>
                </select>
            </div>
        </div>
    <?php endif; ?>
</div>
