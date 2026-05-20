<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $company_data, $company_meta_data, $hide_company_fields;
$company_website = isset($company_meta_data[JOBPORTAL_METABOX_PREFIX . 'company_website']) ? $company_meta_data[JOBPORTAL_METABOX_PREFIX . 'company_website'][0] : '';
$company_phone = isset($company_meta_data[JOBPORTAL_METABOX_PREFIX . 'company_phone']) ? $company_meta_data[JOBPORTAL_METABOX_PREFIX . 'company_phone'][0] : '';
$company_phone_code = isset($company_meta_data[JOBPORTAL_METABOX_PREFIX . 'company_phone_code']) ? $company_meta_data[JOBPORTAL_METABOX_PREFIX . 'company_phone_code'][0] : '';
$company_email = isset($company_meta_data[JOBPORTAL_METABOX_PREFIX . 'company_email']) ? $company_meta_data[JOBPORTAL_METABOX_PREFIX . 'company_email'][0] : '';
$enable_add_new_company_categories = jobportal_get_option('enable_add_new_company_categories');
?>

<div class="row">
    <?php if (!in_array('fields_company_name', $hide_company_fields)) : ?>
        <div class="form-group col-md-6">
            <label for="company_title"><?php esc_html_e('Company name', 'jobportal-framework') ?> <sup>*</sup></label>
            <input type="text" id="company_title" name="company_title"
                placeholder="<?php esc_attr_e('Name', 'jobportal-framework') ?>"
                value="<?php print sanitize_text_field($company_data->post_title); ?>">
        </div>
    <?php endif; ?>
    <?php if (!in_array('fields_company_category', $hide_company_fields)) : ?>
        <div class="form-group col-md-6">
            <label><?php esc_html_e('Categories', 'jobportal-framework') ?> <sup>*</sup></label>
            <div class="select2-field">
                <?php
                $taxonomy = 'company-categories';
                $company_id = !empty($company_data->ID) && is_numeric($company_data->ID) ? $company_data->ID : 0;

                if ($company_id === 0) {
                    return;
                }

                $term_count = wp_count_terms($taxonomy, ['hide_empty' => false]);

                if ($term_count <= 150): ?>
                    <select name="company_categories" class="jobportal-select2" data-taxonomy="<?php echo esc_attr($taxonomy); ?>">
                        <?php jobportal_get_taxonomy_by_post_id($company_id, $taxonomy, true); ?>
                    </select>
                <?php else: ?>
                    <select name="company_categories" class="jobportal-ajax-select2" data-taxonomy="<?php echo esc_attr($taxonomy); ?>">
                        <?php
                        $selected_categories = get_the_terms($company_id, $taxonomy);
                        if (!is_wp_error($selected_categories) && !empty($selected_categories)) {
                            foreach ($selected_categories as $term) {
                                echo '<option value="' . esc_attr($term->term_id) . '" selected>' . esc_html($term->name) . '</option>';
                            }
                        }
                        ?>
                    </select>
                <?php endif; ?>

            </div>
        </div>
        <?php if ($enable_add_new_company_categories) : ?>
            <div class="form-group col-md-12">
                <label for="company_new_categories"><?php esc_html_e('Add New Categories', 'jobportal-framework'); ?></label>
                <input type="text" id="company_new_categories" name="company_new_categories" value="" placeholder="<?php esc_attr_e('Enter new Categories', 'jobportal-framework'); ?>">
            </div>
        <?php endif; ?>
    <?php endif; ?>
    <?php if (!in_array('fields_company_url', $hide_company_fields)) : ?>
        <div class="form-group col-md-12">
            <label><?php esc_html_e('Company Url Slug', 'jobportal-framework') ?></label>
            <div class="company-url-warp">
                <input class="input-url" type="text"
                    placeholder="<?php echo esc_url(get_post_type_archive_link('company')) ?>" disabled>
                <input class="input-slug" type="text" id="company_url" name="company_url"
                    value="<?php print sanitize_text_field($company_data->post_name); ?>"
                    placeholder="<?php esc_attr_e('company-name', 'jobportal-framework') ?>">
            </div>
        </div>
    <?php endif; ?>
    <?php if (!in_array('fields_company_about', $hide_company_fields)) : ?>
        <div class="form-group col-md-12">
            <label class="label-des-company"><?php esc_html_e('About company', 'jobportal-framework'); ?>
                <sup>*</sup></label>
            <?php
            $content = $company_data->post_content;
            $editor_id = 'company_des';
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
    <?php if (!in_array('fields_company_website', $hide_company_fields)) : ?>
        <div class="form-group col-md-6">
            <label><?php esc_html_e(' Website', 'jobportal-framework'); ?></label>
            <input type="url" id="company_website" name="company_website" value="<?php echo $company_website; ?>"
                placeholder="<?php esc_attr_e('www.domain.com', 'jobportal-framework') ?>">
        </div>
    <?php endif; ?>
    <?php if (!in_array('fields_company_phone', $hide_company_fields)) : ?>
        <div class="form-group col-md-6">
            <label><?php esc_html_e('Phone Number', 'jobportal-framework'); ?></label>
            <div class="tel-group">
                <select name="prefix_code" class="jobportal-select2 prefix-code">
                    <?php
                    $prefix_code = phone_prefix_code();
                    $default_phone = JobPortal_Helper::jobportal_get_option('default_phone_number');
                    $selected_prefix = JobPortal_Helper::get_prefix_key_from_phone($company_phone, $prefix_code, $default_phone);
                    foreach ($prefix_code as $key => $value) {
                        $selected = ($key == $selected_prefix) ? 'selected' : '';
                        echo '<option value="' . esc_attr($key) . '" data-dial-code="' . esc_attr($value['code']) . '" ' . $selected . '>' . esc_html($value['name']) . ' (' . esc_html($value['code']) . ')</option>';
                    }
                    ?>
                </select>
                <?php
                $default_phone_code = isset($prefix_code[$selected_prefix]) ? $prefix_code[$selected_prefix]['code'] : (isset($prefix_code[$default_phone]) ? $prefix_code[$default_phone]['code'] : '');
                $input_value = !empty($company_phone) ? preg_replace('/^' . preg_quote($default_phone_code, '/') . '0+/', $default_phone_code, preg_replace('/[^0-9+]/', '', $company_phone)) : $default_phone_code;
                ?>
                <input type="tel" id="company_phone" name="company_phone"
                    data-prefix="<?php echo esc_attr($default_phone_code); ?>"
                    value="<?php echo esc_attr($input_value); ?>"
                    placeholder="<?php esc_attr_e('+00 12 334 5678', 'jobportal-framework') ?>"
                    pattern="\+[0-9]{8,12}"
                    required>
            </div>
        </div>
    <?php endif; ?>
    <?php if (!in_array('fields_company_email', $hide_company_fields)) : ?>
        <div class="form-group col-md-6">
            <label><?php esc_html_e('Email', 'jobportal-framework') ?> <sup>*</sup></label>
            <input type="email" id="company_email" name="company_email" value="<?php echo $company_email; ?>"
                placeholder="<?php esc_attr_e('hello@domain.com', 'jobportal-framework') ?>">
        </div>
    <?php endif; ?>
    <?php if (!in_array('fields_company_founded', $hide_company_fields)) : ?>
        <div class="form-group col-md-6">
            <label><?php esc_html_e('Founded in', 'jobportal-framework') ?></label>
            <div class="select2-field">
                <select name="company_founded" class="jobportal-select2">
                    <?php echo jobportal_get_company_founded(); ?>
                </select>
            </div>
        </div>
    <?php endif; ?>
    <?php if (!in_array('fields_company_size', $hide_company_fields)) : ?>
        <div class="form-group col-md-6">
            <label><?php esc_html_e('Company size', 'jobportal-framework') ?> <sup>*</sup></label>
            <div class="select2-field">
                <select name="company_size" class="jobportal-select2">
                    <?php jobportal_get_taxonomy_by_post_id($company_data->ID, 'company-size', true, false, true, 'company_size_order'); ?>
                </select>
            </div>
        </div>
    <?php endif; ?>
</div>
