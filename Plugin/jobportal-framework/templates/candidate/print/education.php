<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$candidate_details_prints = jobportal_get_option('candidate_details_prints');
foreach ($candidate_details_prints as $print){
    if (!in_array('enable_print_sp_education', $candidate_details_prints)) {
        return;
    }
}

$candidate_educations = get_post_meta($candidate_id, JOBPORTAL_METABOX_PREFIX . 'candidate_education_list', false);
$candidate_educations = !empty($candidate_educations) ? $candidate_educations[0] : '';
if (empty($candidate_educations[0][JOBPORTAL_METABOX_PREFIX . 'candidate_education_title'])) {
    return;
}
?>

<div class="block-archive-inner candidate-single-field">
    <h4 class="title-candidate"><?php esc_html_e('Education', 'jobportal-framework') ?></h4>
    <?php foreach ($candidate_educations as $education) : ?>
        <?php if (!empty($education[JOBPORTAL_METABOX_PREFIX . 'candidate_education_title'])) : ?>
            <div class="single candidate-education">
                <?php if (!empty($education[JOBPORTAL_METABOX_PREFIX . 'candidate_education_title'])) : ?>
                    <div class="education-title time-dot">
                        <?php echo $education[JOBPORTAL_METABOX_PREFIX . 'candidate_education_title']; ?>
                    </div>
                <?php endif; ?>
                <div class="education-details time-line">
                    <?php if (!empty($education[JOBPORTAL_METABOX_PREFIX . 'candidate_education_level'])) : ?>
                        <span><?php echo $education[JOBPORTAL_METABOX_PREFIX . 'candidate_education_level']; ?></span>
                    <?php endif; ?>
                    <?php if (!empty($education[JOBPORTAL_METABOX_PREFIX . 'candidate_education_from'])) : ?>
                        <span><?php echo $education[JOBPORTAL_METABOX_PREFIX . 'candidate_education_from']; ?></span>
                    <?php endif; ?>
                    <span>-</span>
                    <?php if (!empty($education[JOBPORTAL_METABOX_PREFIX . 'candidate_education_to'])) : ?>
                        <span><?php echo $education[JOBPORTAL_METABOX_PREFIX . 'candidate_education_to']; ?></span>
                    <?php endif; ?>
                    <?php if (!empty($education[JOBPORTAL_METABOX_PREFIX . 'candidate_education_description'])) : ?>
                        <span><?php echo $education[JOBPORTAL_METABOX_PREFIX . 'candidate_education_description']; ?></span>
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
        if ($field['tabs'] == 'education') {
            $check_tabs = true;
        }
    }

    if(count($custom_field_candidate) > 0){
        if ($check_tabs == true) : ?>
            <?php foreach ($custom_field_candidate as $field) {
                if ($field['tabs'] == 'education') { ?>
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