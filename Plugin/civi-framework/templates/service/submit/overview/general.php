<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $hide_service_fields;
$currency_sign_default = civi_get_option('currency_sign_default');
$enable_candidate_service_fee = civi_get_option('enable_candidate_service_fee');
$candidate_number_service_fee = civi_get_option('candidate_number_service_fee');
?>

<div class="row">
    <?php if (!in_array('fields_service_title', $hide_service_fields)) : ?>
        <div class="form-group col-md-12">
            <label for="service_title"><?php esc_html_e('Service title', 'civi-framework') ?> <sup>*</sup></label>
            <input type="text" id="service_title" name="service_title"
                   placeholder="<?php esc_attr_e('Enter title', 'civi-framework') ?>">
        </div>
    <?php endif; ?>
    <?php if (!in_array('fields_service_category', $hide_service_fields)) : ?>
        <div class="form-group col-md-12">
            <label><?php esc_html_e('Service Categories', 'civi-framework') ?> <sup>*</sup></label>
            <div class="select2-field">
                <select name="service_categories" class="civi-select2">
                    <?php civi_get_taxonomy('service-categories', false, true, false, true, 'service_categories_order'); ?>
                </select>
            </div>
        </div>
    <?php endif; ?>
    <?php if (!in_array('fields_service_price', $hide_service_fields)) : ?>
        <div class="form-group col-md-6">
            <label for="service_price"><?php echo sprintf(esc_html__('Service Price (%s)', 'civi-framework'), $currency_sign_default) ?> <sup>*</sup></label>
            <input type="number" id="service_price" name="service_price" pattern="[-+]?[0-9]" placeholder="0">
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
            <input type="number" id="service_time" name="service_time" pattern="[-+]?[0-9]" placeholder="0">
        </div>
        <div class="form-group col-md-6">
            <label><?php esc_html_e('Time Type', 'civi-framework') ?> </label>
            <div class="select2-field">
                <select name="service_time_type" class="civi-select2">
                    <option value="hr"><?php esc_html_e('Hour', 'civi-framework'); ?></option>
                    <option value="day"><?php esc_html_e('Day', 'civi-framework'); ?></option>
                    <option value="week"><?php esc_html_e('Week', 'civi-framework'); ?></option>
                    <option value="year"><?php esc_html_e('Year', 'civi-framework'); ?></option>
                </select>
            </div>
        </div>
    <?php endif; ?>
    <?php if (!in_array('fields_service_description', $hide_service_fields)) : ?>
        <div class="form-group col-md-12">
            <label class="label-des-service"><?php esc_html_e('Description', 'civi-framework'); ?>
                <sup>*</sup></label>
            <?php
            $content = '';
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
                    <select data-placeholder="<?php esc_attr_e('Select language', 'civi-framework'); ?>" multiple="multiple"
                            class="civi-select2" name="service_languages">
                        <?php civi_get_taxonomy('service-language', false, false, false, true, 'service_language_order'); ?>
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
                    <option value="basic"><?php esc_html_e('Basic', 'civi-framework'); ?></option>
                    <option value="conversational"><?php esc_html_e('Conversational', 'civi-framework'); ?></option>
                    <option value="fluent"><?php esc_html_e('Fluent', 'civi-framework'); ?></option>
                    <option value="native"><?php esc_html_e('Native or Bilingual', 'civi-framework'); ?></option>
                    <option value="professional"><?php esc_html_e('Professional', 'civi-framework'); ?></option>
                </select>
            </div>
        </div>
    <?php endif; ?>
</div>
