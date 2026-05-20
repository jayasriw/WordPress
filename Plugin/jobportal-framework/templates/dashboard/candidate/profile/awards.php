<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $candidate_data;
$candidate_award_list = get_post_meta($candidate_data->ID, JOBPORTAL_METABOX_PREFIX . 'candidate_award_list', false);
$candidate_award_list = !empty($candidate_award_list) ? $candidate_award_list[0] : '';
$candidate_award_quantity = !empty($candidate_award_list) ? count($candidate_award_list) : '';
$date_format = get_option('date_format');
?>

<div id="tab-awards" class="tab-info">

    <div class="awards-info block-from">
        <h5><?php esc_html_e('Awards', 'jobportal-framework') ?></h5>

        <div class="sub-head"><?php esc_html_e('We recommend at least one award entry', 'jobportal-framework') ?></div>

        <div class="jobportal-candidate-wrapper">
            <?php if (!empty($candidate_award_list)) :
                foreach ($candidate_award_list as $index => $candidate_award) : ?>
                    <div class="row">
                        <div class="group-title col-md-12">
                            <i class="delete-group fas fa-times"></i>
                            <h6 class="project">
                                <?php esc_html_e('Award', 'jobportal-framework') ?>
                                <span><?php echo $index + 1 ?></span>
                            </h6>
                            <i class="fas fa-angle-up"></i>
                        </div>
                        <div class="form-group col-md-6">
                            <label><?php esc_html_e('Title', 'jobportal-framework') ?></label>
                            <input type="text" name="candidate_award_title[]" placeholder="<?php esc_attr_e('Name of award', 'jobportal-framework'); ?>" value="<?php echo esc_attr($candidate_award[JOBPORTAL_METABOX_PREFIX . 'candidate_award_title']) ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label><?php esc_html_e('Date awarded', 'jobportal-framework') ?></label>
                            <input type="date" placeholder="<?php echo $date_format; ?>" name="candidate_award_date[]" value="<?php echo esc_attr($candidate_award[JOBPORTAL_METABOX_PREFIX . 'candidate_award_date']) ?>">
                        </div>
                        <div class="form-group col-md-12">
                            <label><?php esc_html_e('Description', 'jobportal-framework') ?></label>
                            <textarea name="candidate_award_description[]" cols="30" rows="7" placeholder="<?php esc_attr_e('Short description', 'jobportal-framework'); ?>"><?php echo esc_attr($candidate_award[JOBPORTAL_METABOX_PREFIX . 'candidate_award_description']) ?></textarea>
                        </div>
                    </div>
            <?php endforeach;
            endif;
            ?>

            <button type="button" class="btn-more profile-fields"><i class="far fa-angle-down"></i><?php esc_html_e('Add another award', 'jobportal-framework') ?></button>

            <template id="template-item-award" data-size="<?php echo esc_attr($candidate_award_quantity) ?>">
                <div class="row">
                    <div class="group-title col-md-12">
                        <i class="delete-group fas fa-times"></i>
                        <h6 class="project">
                            <?php esc_html_e('Award', 'jobportal-framework') ?>
                            <span></span>
                        </h6>
                        <i class="fas fa-angle-up"></i>
                    </div>
                    <div class="form-group col-md-6">
                        <label><?php esc_html_e('Title', 'jobportal-framework') ?></label>
                        <input type="text" name="candidate_award_title[]" placeholder="<?php esc_attr_e('Name of award', 'jobportal-framework'); ?>" value="">
                    </div>
                    <div class="form-group col-md-6">
                        <label><?php esc_html_e('Date awarded', 'jobportal-framework') ?></label>
                        <input type="date" placeholder="<?php echo $date_format; ?>" name="candidate_award_date[]" value="">
                    </div>
                    <div class="form-group col-md-12">
                        <label><?php esc_html_e('Description', 'jobportal-framework') ?></label>
                        <textarea name="candidate_award_description[]" cols="30" rows="7" placeholder="<?php esc_attr_e('Short description', 'jobportal-framework'); ?>"></textarea>
                    </div>
                </div>
            </template>
        </div>
    </div>
    <?php jobportal_custom_field_candidate('awards'); ?>
</div>
