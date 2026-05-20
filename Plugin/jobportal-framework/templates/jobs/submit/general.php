<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $hide_jobs_fields, $current_user;
$jobs_id = get_the_ID();
$jobs_days_closing = jobportal_get_option('jobs_number_days', true);
$jobs_date_mode = jobportal_get_option('jobs_date_mode', 'days');
$user_id = $current_user->ID;
$jobs_user_post_title = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'jobs_user_post_title', true);
$jobs_user_post_des = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'jobs_user_post_des', true);
$jobs_user_quantity = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'jobs_user_quantity', true);
$jobs_user_gender = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'jobs_user_gender', true);
$jobs_user_days_closing = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'jobs_user_days_closing', true);
$jobs_user_closing_date = '';

if ($jobs_user_days_closing && $jobs_date_mode === 'closing_date') {
	$current_date = date('Y-m-d');
	$closing_timestamp = strtotime($current_date . '+' . intval($jobs_user_days_closing) . ' days');
	if ($closing_timestamp) {
		$jobs_user_closing_date = date('Y-m-d', $closing_timestamp);
	}
}

$jobs_user_skills = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'jobs-skills_user', true);
$enable_add_new_job_categories = jobportal_get_option('enable_add_new_job_categories');
$enable_ai_helper = jobportal_get_option('enable_ai_helper');
$ai_key = jobportal_get_option('ai_key');
?>
<div class="row">
    <?php if (!in_array('fields_jobs_name', $hide_jobs_fields)) : ?>
        <div class="form-group col-md-12">
            <label for="jobs_title"><?php esc_html_e('Job title', 'jobportal-framework') ?> <sup>*</sup></label>
            <input type="text" id="jobs_title" name="jobs_title"
                placeholder="<?php esc_attr_e('Name', 'jobportal-framework') ?>"
                value="<?php echo $jobs_user_post_title ?>">
        </div>
    <?php endif; ?>
    <?php if (!in_array('fields_jobs_category', $hide_jobs_fields)) : ?>
        <div class="form-group col-lg-6">
            <label><?php esc_html_e('Jobs Categories', 'jobportal-framework') ?> <sup>*</sup></label>
            <div class="select2-field">
                <?php
                $taxonomy = 'jobs-categories';
                $term_count = wp_count_terms($taxonomy, ['hide_empty' => false]);

                if ($term_count <= 150): ?>
                    <select data-placeholder="<?php esc_attr_e('Select categories', 'jobportal-framework'); ?>"
                        class="jobportal-select2" name="jobs_categories" data-taxonomy="<?php echo esc_attr($taxonomy); ?>">
                        <?php jobportal_get_taxonomy($taxonomy, false, true); ?>
                    </select>
                <?php else: ?>
                    <select data-placeholder="<?php esc_attr_e('Select categories', 'jobportal-framework'); ?>"
                        class="jobportal-ajax-select2" name="jobs_categories" data-taxonomy="<?php echo esc_attr($taxonomy); ?>">
                    </select>
                <?php endif; ?>
            </div>
        </div>
        <?php if ($enable_add_new_job_categories) : ?>
            <div class="form-group col-md-6">
                <label for="jobs_new_categories"><?php esc_html_e('Add New Categories', 'jobportal-framework'); ?></label>
                <input type="text" id="jobs_new_categories" name="jobs_new_categories" value="" placeholder="<?php esc_attr_e('Enter new Categories', 'jobportal-framework'); ?>">
            </div>
        <?php endif; ?>
    <?php endif; ?>
    <?php if (!in_array('fields_jobs_type', $hide_jobs_fields)) : ?>
        <div class="form-group col-lg-6">
            <label><?php esc_html_e('Job type', 'jobportal-framework') ?> <sup>*</sup></label>
            <div class="form-select">
                <div class="select2-field select2-multiple">
                    <select data-placeholder="<?php esc_attr_e('Select an option', 'jobportal-framework'); ?>"
                        multiple="multiple" class="jobportal-select2" name="jobs_type">
                        <?php jobportal_get_taxonomy('jobs-type', false, true, false, true, 'jobs_type_order'); ?>
                    </select>
                </div>
                <i class="fas fa-angle-down"></i>
            </div>
        </div>
    <?php endif; ?>
    <?php if (!in_array('fields_jobs_skills', $hide_jobs_fields)) : ?>
        <div class="form-group col-md-12">
            <label><?php esc_html_e('Skills', 'jobportal-framework') ?> <sup>*</sup></label>
            <div class="form-select">
                <div class="select2-field select2-multiple">
                    <?php
                    $taxonomy = 'jobs-skills';
                    $term_count = wp_count_terms($taxonomy, ['hide_empty' => false]);

                    if ($term_count <= 150): ?>
                        <select data-placeholder="<?php esc_attr_e('Select skills', 'jobportal-framework'); ?>" multiple="multiple"
                            class="jobportal-select2" name="jobs_skills" data-taxonomy="<?php echo esc_attr($taxonomy); ?>">
                            <?php
                            jobportal_get_taxonomy($taxonomy, false, false);
                            if (!empty($jobs_user_skills) && is_array($jobs_user_skills)) {
                                foreach ($jobs_user_skills as $skill_id) {
                                    $term = get_term($skill_id, $taxonomy);
                                    if (!is_wp_error($term) && $term) {
                                        echo '<option value="' . esc_attr($term->term_id) . '" selected>' . esc_html($term->name) . '</option>';
                                    }
                                }
                            }
                            ?>
                        </select>
                    <?php else: ?>
                        <select data-placeholder="<?php esc_attr_e('Select skills', 'jobportal-framework'); ?>" multiple="multiple"
                            class="jobportal-ajax-select2" name="jobs_skills" data-taxonomy="<?php echo esc_attr($taxonomy); ?>">
                            <?php
                            if (!empty($jobs_user_skills) && is_array($jobs_user_skills)) {
                                foreach ($jobs_user_skills as $skill_id) {
                                    $term = get_term($skill_id, $taxonomy);
                                    if (!is_wp_error($term) && $term) {
                                        echo '<option value="' . esc_attr($term->term_id) . '" selected>' . esc_html($term->name) . '</option>';
                                    }
                                }
                            }
                            ?>
                        </select>
                    <?php endif; ?>
                </div>
                <i class="fas fa-angle-down"></i>
            </div>
        </div>
    <?php endif; ?>
    <?php if (!in_array('fields_jobs_des', $hide_jobs_fields)) : ?>
        <div class="form-group col-md-12">
            <div class="flex">
                <label class="label-des-jobs"><?php esc_html_e('Description', 'jobportal-framework'); ?> <sup>*</sup></label>
                <?php
                if ($enable_ai_helper == 1 && $ai_key != '') {
                ?>
                    <div class="ai-helper-wrapper">
                        <span class="ai-helper" data-popup="ai-popup"><i class="fal fa-magic"></i><?php esc_html_e('AI Helper', 'jobportal-framework'); ?></span>
                    </div>
                <?php
                }
                ?>
            </div>
            <?php
            if ($jobs_user_post_des) {
                $content = "$jobs_user_post_des";
            } else {
                $content = '';
            }
            $editor_id = 'jobs_des';
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
    <?php if (!in_array('fields_jobs_career', $hide_jobs_fields)) : ?>
        <div class="form-group col-lg-6">
            <label><?php esc_html_e('Career level', 'jobportal-framework') ?></label>
            <div class="select2-field">
                <select name="jobs_career" class="jobportal-select2">
                    <?php jobportal_get_taxonomy('jobs-career', false, true, false, true, 'jobs_career_order'); ?>
                </select>
            </div>
        </div>
    <?php endif; ?>
    <?php if (!in_array('fields_jobs_experience', $hide_jobs_fields)) : ?>
        <div class="form-group col-lg-6">
            <label><?php esc_html_e('Experience', 'jobportal-framework') ?></label>
            <div class="select2-field">
                <select name="jobs_experience" class="jobportal-select2">
                    <?php jobportal_get_taxonomy('jobs-experience', false, true, false, true, 'jobs_experience_order'); ?>
                </select>
            </div>
        </div>
    <?php endif; ?>
    <?php if (!in_array('fields_jobs_qualification', $hide_jobs_fields)) : ?>
        <div class="form-group col-lg-6">
            <label><?php esc_html_e('Qualification', 'jobportal-framework') ?></label>
            <div class="form-select">
                <div class="select2-field select2-multiple">
                    <select data-placeholder="<?php esc_attr_e('Select an option', 'jobportal-framework'); ?>"
                        multiple="multiple" class="jobportal-select2" name="jobs_qualification">
                        <?php jobportal_get_taxonomy('jobs-qualification', false, false, false, true, 'jobs_qualification_order'); ?>
                    </select>
                </div>
                <i class="fas fa-angle-down"></i>
            </div>
        </div>
    <?php endif; ?>
    <?php if (!in_array('fields_jobs_quantity', $hide_jobs_fields)) : ?>
        <div class="form-group col-lg-6">
            <label><?php esc_html_e('Quantity to be recruited', 'jobportal-framework') ?></label>
            <div class="select2-field">
                <select name="jobs_quantity" class="jobportal-select2">
                    <?php for ($quantity = 0; $quantity <= 10; $quantity++) {
                        if ($quantity == 0) { ?>
                            <option selected value=""><?php esc_attr_e('Select an option', 'jobportal-framework'); ?></option>
                        <?php
                        } else {
                        ?>
                            <option <?php if ($jobs_user_quantity == $quantity) {
                                        echo 'selected';
                                    } ?> value="<?php echo $quantity; ?>">
                                <?php if ($quantity == 10) {
                                    echo $quantity . '+';
                                } else {
                                    echo $quantity;
                                } ?>
                            </option>
                    <?php }
                    } ?>
                </select>
            </div>
        </div>
    <?php endif; ?>
    <?php if (!in_array('fields_jobs_gender', $hide_jobs_fields)) : ?>
        <div class="form-group col-lg-6">
            <label><?php esc_html_e('Gender', 'jobportal-framework') ?></label>
            <div class="select2-field">
                <select name="jobs_gender" class="jobportal-select2">
                    <?php jobportal_get_taxonomy('jobs-gender', false, true, false, true, 'jobs_gender_order'); ?>
                </select>
            </div>
        </div>
    <?php endif; ?>
    <?php if (!in_array('fields_closing_days', $hide_jobs_fields)) : ?>
        <div class="form-group col-lg-6">
            <?php if ($jobs_date_mode === 'closing_date') : ?>
                <label for="jobs_closing_date"><?php esc_html_e('Closing Date', 'jobportal-framework'); ?></label>
                <input type="date" id="jobs_closing_date" name="jobs_closing_date"
                    value="<?php echo esc_attr($jobs_user_closing_date); ?>" min="<?php echo date('Y-m-d'); ?>">
                <small class="form-text text-muted" id="remaining_days_text"><?php esc_html_e('Remaining days will be calculated automatically', 'jobportal-framework'); ?></small>
                <input type="hidden" id="jobs_days_closing" name="jobs_days_closing" value="<?php echo esc_attr($jobs_user_days_closing); ?>">
            <?php else : ?>
                <label for="jobs_days_closing"><?php esc_html_e('Closing days', 'jobportal-framework'); ?></label>
                <input type="text" id="jobs_days_closing" name="jobs_days_closing"
                    placeholder="<?php echo $jobs_days_closing; ?>" value="<?php echo $jobs_user_days_closing; ?>">
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
