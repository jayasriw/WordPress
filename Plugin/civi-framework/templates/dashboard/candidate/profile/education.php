<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $candidate_data;
$candidate_education_list = get_post_meta($candidate_data->ID, CIVI_METABOX_PREFIX . 'candidate_education_list', false);
$candidate_education_list = !empty($candidate_education_list) ? $candidate_education_list[0] : '';
$candidate_education_quantity = !empty($candidate_education_list) ?  count($candidate_education_list) : 1;
$date_format = get_option('date_format');
$text_present = esc_html__('Present', 'civi-framework');
?>

<script>
    var text_present = '<?php echo esc_js($text_present); ?>';
    var date_format = '<?php echo esc_js($date_format); ?>';
</script>

<div id="tab-education" class="tab-info">
    <div class="education-info block-from">
        <h5 class="education"><?php esc_html_e('Education', 'civi-framework') ?></h5>

        <div class="sub-head"><?php esc_html_e('We recommend at least one education entry.', 'civi-framework') ?></div>

        <div class="civi-candidate-wrapper">
            <?php if (!empty($candidate_education_list)) :
                foreach ($candidate_education_list as $index => $candidate_education) :
                    $candidate_education_to = !empty($candidate_education[CIVI_METABOX_PREFIX . 'candidate_education_to']) ? $candidate_education[CIVI_METABOX_PREFIX . 'candidate_education_to'] : '';
                    if ($candidate_education_to == $text_present) {
                        $is_check = 'checked';
                        $checkbox_value = 'present';
                    } else {
                        $is_check = '';
                        $checkbox_value = '';
                    }
            ?>
                    <div class="row">
                        <div class="group-title col-md-12">
                            <i class="delete-group fas fa-times"></i>
                            <h6 class="education">
                                <?php echo esc_html_e('Education', 'civi-framework') ?>
                                <span><?php echo $index + 1 ?></span>
                            </h6>
                            <i class="fas fa-angle-up"></i>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="candidate_education_title_<?php echo esc_attr($index); ?>"><?php esc_html_e('Title', 'civi-framework') ?></label>
                            <input type="text" id="candidate_education_title_<?php echo esc_attr($index); ?>" name="candidate_education_title[]" placeholder="<?php esc_attr_e('Enter Title', 'civi-framework'); ?>" value="<?php echo esc_attr($candidate_education[CIVI_METABOX_PREFIX . 'candidate_education_title']) ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="candidate_education_level_<?php echo esc_attr($index); ?>"><?php esc_html_e('Level of Education', 'civi-framework') ?></label>
                            <input type="text" id="candidate_education_level_<?php echo esc_attr($index); ?>" name="candidate_education_level[]" placeholder="<?php esc_attr_e('Enter Level', 'civi-framework'); ?>" value="<?php echo esc_attr($candidate_education[CIVI_METABOX_PREFIX . 'candidate_education_level']) ?>">
                        </div>
                        <div class="form-group col-md-12">
                            <label class="label-present">
                                <input <?php echo $is_check; ?> type="checkbox" id="candidate_education_check_<?php echo esc_attr($index); ?>" class="custom-checkbox input-control" name="candidate_education_check[]" value="<?php echo esc_attr($checkbox_value); ?>" />
                                <?php esc_html_e('Choose at the present time', 'civi-framework') ?>
                            </label>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="candidate_education_from_<?php echo esc_attr($index); ?>"><?php esc_html_e('From', 'civi-framework') ?></label>
                            <input type="date" id="candidate_education_from_<?php echo esc_attr($index); ?>" placeholder="<?php echo esc_attr($date_format); ?>" name="candidate_education_from[]" value="<?php echo esc_attr($candidate_education[CIVI_METABOX_PREFIX . 'candidate_education_from']) ?>">
                        </div>
                        <div class="form-group col-md-6 present-to">
                            <label for="candidate_education_to_<?php echo esc_attr($index); ?>"><?php esc_html_e('To', 'civi-framework') ?></label>
                            <?php if ($candidate_education_to == $text_present) : ?>
                                <input disabled class="text-present" type="text" id="candidate_education_to_<?php echo esc_attr($index); ?>" name="candidate_education_to[]" value="<?php echo esc_attr($text_present); ?>">
                            <?php else : ?>
                                <input type="date" id="candidate_education_to_<?php echo esc_attr($index); ?>" placeholder="<?php echo esc_attr($date_format); ?>" name="candidate_education_to[]" value="<?php echo esc_attr($candidate_education_to); ?>">
                            <?php endif; ?>
                        </div>

                        <div class="form-group col-md-12">
                            <label for="candidate_education_description_<?php echo esc_attr($index); ?>"><?php esc_html_e('Description', 'civi-framework') ?></label>
                            <textarea id="candidate_education_description_<?php echo esc_attr($index); ?>" name="candidate_education_description[]" cols="30" placeholder="<?php esc_attr_e('Short description', 'civi-framework'); ?>" rows="7"><?php echo esc_textarea($candidate_education[CIVI_METABOX_PREFIX . 'candidate_education_description']) ?></textarea>
                        </div>
                    </div>
            <?php endforeach;
            endif;
            ?>
            <button type="button" class="btn-more profile-fields"><i class="far fa-angle-down"></i><?php esc_html_e('Add another education', 'civi-framework') ?></button>
            <template id="template-item-education" data-size="<?php echo esc_attr($candidate_education_quantity) ?>">
                <div class="row">
                    <div class="group-title col-md-12">
                        <i class="delete-group fas fa-times"></i>
                        <h6 class="education">
                            <?php echo esc_html_e('Education', 'civi-framework') ?>
                            <span></span>
                        </h6>
                        <i class="fas fa-angle-up"></i>
                    </div>
                    <div class="form-group col-md-6">
                        <label><?php esc_html_e('Title', 'civi-framework') ?></label>
                        <input type="text" name="candidate_education_title[]" value="" placeholder="<?php esc_attr_e('Enter Title', 'civi-framework'); ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label><?php esc_html_e('Level of Education', 'civi-framework') ?></label>
                        <input type="text" name="candidate_education_level[]" value="" placeholder="<?php esc_attr_e('Enter Level', 'civi-framework'); ?>">
                    </div>
                    <div class="form-group col-md-12">
                        <label class="label-present">
                            <input type="checkbox" class="custom-checkbox input-control" name="candidate_education_check[]" value="" />
                            <?php esc_html_e('Choose at the present time', 'civi-framework') ?>
                        </label>
                    </div>
                    <div class="form-group col-md-6">
                        <label><?php esc_html_e('From', 'civi-framework') ?></label>
                        <input type="date" placeholder="<?php echo esc_attr($date_format); ?>" name="candidate_education_from[]" value="">
                    </div>
                    <div class="form-group col-md-6 present-to">
                        <label><?php esc_html_e('To', 'civi-framework') ?></label>
                        <input type="date" placeholder="<?php echo esc_attr($date_format); ?>" name="candidate_education_to[]" value="">
                    </div>
                    <div class="form-group col-md-12">
                        <label><?php esc_html_e('Description', 'civi-framework') ?></label>
                        <textarea name="candidate_education_description[]" cols="30" rows="7" placeholder="<?php esc_attr_e('Short description', 'civi-framework'); ?>"></textarea>
                    </div>
                </div>
            </template>
        </div>
    </div>
    <?php civi_custom_field_candidate('education'); ?>
</div>
