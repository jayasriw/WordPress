<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$candidate_details_prints = jobportal_get_option('candidate_details_prints');
foreach ($candidate_details_prints as $print){
    if (!in_array('enable_print_sp_awards', $candidate_details_prints)) {
        return;
    }
}

$candidate_awards = get_post_meta($candidate_id, JOBPORTAL_METABOX_PREFIX . 'candidate_award_list', false);
$candidate_awards = !empty($candidate_awards) ? $candidate_awards[0] : '';
if (empty($candidate_awards[0][JOBPORTAL_METABOX_PREFIX . 'candidate_award_title'])) {
    return;
}
?>

<div class="block-archive-inner candidate-single-field">
    <h4 class="title-candidate"><?php esc_html_e('Honors & awards', 'jobportal-framework') ?></h4>
    <?php foreach ($candidate_awards as $award) : ?>
        <?php if (!empty($award[JOBPORTAL_METABOX_PREFIX . 'candidate_award_title'])) : ?>
            <div class="single candidate-award">
                <?php if (!empty($award[JOBPORTAL_METABOX_PREFIX . 'candidate_award_title'])) : ?>
                    <div class="award-title time-dot">
                        <?php echo $award[JOBPORTAL_METABOX_PREFIX . 'candidate_award_title']; ?>
                    </div>
                <?php endif; ?>
                <div class="award-details time-line">
                    <?php if (!empty($award[JOBPORTAL_METABOX_PREFIX . 'candidate_award_date'])) : ?>
                        <span><?php echo $award[JOBPORTAL_METABOX_PREFIX . 'candidate_award_date']; ?></span>
                    <?php endif; ?>
                    <?php if (!empty($award[JOBPORTAL_METABOX_PREFIX . 'candidate_award_description'])) : ?>
                        <span><?php echo $award[JOBPORTAL_METABOX_PREFIX . 'candidate_award_description']; ?></span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php
    $custom_field_candidate = jobportal_render_custom_field('candidate');
    $candidate_meta_data = get_post_custom($candidate_id);
    $candidate_data = get_post($candidate_id);
    $check_tabs = false;
    foreach ($custom_field_candidate as $field) {
        if ($field['tabs'] == 'awards') {
            $check_tabs = true;
        }
    }

    if(count($custom_field_candidate) > 0){
        if ($check_tabs == true) : ?>
            <?php foreach ($custom_field_candidate as $field) {
                if ($field['tabs'] == 'awards') { ?>
                    <?php jobportal_get_template("candidate/print/additional/field.php",array(
                        'field' => $field,
                        'candidate_data' => $candidate_data,
                        'candidate_meta_data' => $candidate_meta_data
                    ));
                }} ?>
        <?php endif;
    }
    ?>
</div>