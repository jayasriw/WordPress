<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $candidate_data;
$candidate_experience_list = get_post_meta($candidate_data->ID, JOBPORTAL_METABOX_PREFIX . 'candidate_experience_list', false);
$candidate_experience_list = !empty($candidate_experience_list) ? $candidate_experience_list[0] : '';
$candidate_experience_quantity = !empty($candidate_experience_list) ? count($candidate_experience_list) : '';
$date_format = get_option('date_format');
$text_present = esc_html__('Present', 'jobportal-framework');
?>

<div id="tab-experience" class="tab-info">
    <div class="experience-info block-from">
        <h5><?php esc_html_e('Experience', 'jobportal-framework') ?></h5>

        <div class="sub-head"><?php esc_html_e('We recommend at least one experience entry.', 'jobportal-framework') ?></div>

        <div class="jobportal-candidate-wrapper">
            <?php
            if (!empty($candidate_experience_list)) :
                foreach ($candidate_experience_list as $index => $candidate_experience) :
                    $candidate_experience_to = !empty($candidate_experience[JOBPORTAL_METABOX_PREFIX . 'candidate_experience_to']) ? $candidate_experience[JOBPORTAL_METABOX_PREFIX . 'candidate_experience_to'] : '';
                    if ($candidate_experience_to == $text_present) {
                        $is_check = 'checked';
                    } else {
                        $is_check = '';
                    }
            ?>
                    <div class="row">
                        <div class="group-title col-md-12">
                            <i class="delete-group fas fa-times"></i>
                            <h6 class="experience">
                                <?php echo esc_html_e('Experience', 'jobportal-framework') ?>
                                <span><?php echo $index + 1 ?></span>
                            </h6>
                            <i class="fas fa-angle-up"></i>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="candidate_experience_job_<?php echo esc_attr($index); ?>"><?php esc_html_e('Job Title', 'jobportal-framework') ?></label>
                            <input type="text" id="candidate_experience_job_<?php echo esc_attr($index); ?>" name="candidate_experience_job[]" placeholder="<?php esc_attr_e('Enter Job Title', 'jobportal-framework'); ?>" value="<?php echo esc_attr($candidate_experience[JOBPORTAL_METABOX_PREFIX . 'candidate_experience_job']) ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="candidate_experience_company_<?php echo esc_attr($index); ?>"><?php esc_html_e('Company', 'jobportal-framework') ?></label>
                            <input type="text" id="candidate_experience_company_<?php echo esc_attr($index); ?>" name="candidate_experience_company[]" placeholder="<?php esc_attr_e('Enter Company', 'jobportal-framework'); ?>" value="<?php echo esc_attr($candidate_experience[JOBPORTAL_METABOX_PREFIX . 'candidate_experience_company']) ?>">
                        </div>
                        <div class="form-group col-md-12">
                            <label class="label-present">
                                <input <?php echo $is_check; ?> type="checkbox" id="candidate_experience_check_<?php echo esc_attr($index); ?>" class="custom-checkbox input-control" name="candidate_experience_check[]" value="present" />
                                <?php esc_html_e('Choose at the present time', 'jobportal-framework') ?>
                            </label>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="candidate_experience_from_<?php echo esc_attr($index); ?>"><?php esc_html_e('From', 'jobportal-framework') ?></label>
                            <input type="date" id="candidate_experience_from_<?php echo esc_attr($index); ?>" placeholder="<?php echo esc_attr($date_format); ?>" name="candidate_experience_from[]" value="<?php echo esc_attr($candidate_experience[JOBPORTAL_METABOX_PREFIX . 'candidate_experience_from']) ?>">
                        </div>
                        <div class="form-group col-md-6 present-to">
                            <label for="candidate_experience_to_<?php echo esc_attr($index); ?>"><?php esc_html_e('To', 'jobportal-framework') ?></label>
                            <?php if ($candidate_experience_to == $text_present) {
                                echo '<input disabled class="text-present" type="text" id="candidate_experience_to_' . esc_attr($index) . '" name="candidate_experience_to[]" value="' . esc_attr__('Present', 'jobportal-framework') . '">';
                            } else {
                                echo '<input type="date" id="candidate_experience_to_' . esc_attr($index) . '" placeholder="' . esc_attr($date_format) . '" name="candidate_experience_to[]" value="' . esc_attr($candidate_experience_to) . '">';
                            } ?>
                        </div>
                        <div class="form-group col-md-12">
                            <label for="candidate_experience_description_<?php echo esc_attr($index); ?>"><?php esc_html_e('Description', 'jobportal-framework') ?></label>
                            <textarea id="candidate_experience_description_<?php echo esc_attr($index); ?>" name="candidate_experience_description[]" cols="30" placeholder="<?php esc_attr_e('Short description', 'jobportal-framework'); ?>" rows="7"><?php echo esc_textarea($candidate_experience[JOBPORTAL_METABOX_PREFIX . 'candidate_experience_description']) ?></textarea>
                        </div>
                    </div>
            <?php endforeach;
            endif;
            ?>

            <button type="button" class="btn-more profile-fields"><i class="far fa-angle-down"></i><?php esc_html_e('Add another experience', 'jobportal-framework') ?>
            </button>

            <template id="template-item-experience" data-size="<?php echo esc_attr($candidate_experience_quantity) ?>">
                <div class="row">
                    <div class="group-title col-md-12">
                        <i class="delete-group fas fa-times"></i>
                        <h6 class="experience">
                            <?php echo esc_html_e('Experience', 'jobportal-framework') ?>
                            <span></span>
                        </h6>
                        <i class="fas fa-angle-up"></i>
                    </div>
                    <div class="form-group col-md-6">
                        <label><?php esc_html_e('Job Title', 'jobportal-framework') ?></label>
                        <input type="text" name="candidate_experience_job[]" value="" placeholder="<?php esc_attr_e('Enter Job Title', 'jobportal-framework'); ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label><?php esc_html_e('Company', 'jobportal-framework') ?></label>
                        <input type="text" name="candidate_experience_company[]" value="" placeholder="<?php esc_attr_e('Enter Company', 'jobportal-framework'); ?>">
                    </div>
                    <div class="form-group col-md-12">
                        <label class="label-present">
                            <input type="checkbox" class="custom-checkbox input-control" name="candidate_experience_check[]" value="" />
                            <?php esc_html_e('Choose at the present time', 'jobportal-framework') ?>
                        </label>
                    </div>
                    <div class="form-group col-md-6">
                        <label><?php esc_html_e('From', 'jobportal-framework') ?></label>
                        <input type="date" placeholder="<?php echo esc_attr($date_format); ?>" name="candidate_experience_from[]" value="">
                    </div>
                    <div class="form-group col-md-6 present-to">
                        <label><?php esc_html_e('To', 'jobportal-framework') ?></label>
                        <input type="date" placeholder="<?php echo esc_attr($date_format); ?>" name="candidate_experience_to[]" value="">
                    </div>
                    <div class="form-group col-md-12">
                        <label><?php esc_html_e('Description', 'jobportal-framework') ?></label>
                        <textarea name="candidate_experience_description[]" cols="30" rows="7" placeholder="<?php esc_attr_e('Short description', 'jobportal-framework'); ?>"></textarea>
                    </div>
                </div>
            </template>
        </div>
    </div>
    <?php jobportal_custom_field_candidate('experience'); ?>
</div>
