<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$candidate_details_prints = civi_get_option('candidate_details_prints');
foreach ($candidate_details_prints as $print){
    if (!in_array('enable_print_sp_skills', $candidate_details_prints)) {
        return;
    }
}

$candidate_skills = get_the_terms($candidate_id, 'candidate_skills');

if ($candidate_skills == false || is_wp_error($candidate_skills)) {
    return;
}

?>

<div class="block-archive-inner candidate-single-field">
    <h4 class="title-candidate"><?php esc_html_e('Skills', 'civi-framework') ?></h4>
    <div class="candidate-skills">
        <?php foreach ($candidate_skills as $skill) {
            $skill_link = get_term_link($skill, 'candidate_skills'); ?>
            <a href="<?php echo esc_url($skill_link); ?>" class="label label-skills">
                <?php esc_html_e($skill->name); ?>
            </a>
        <?php } ?>
    </div>
    <?php
    $custom_field_candidate = civi_render_custom_field('candidate');
    $candidate_meta_data = get_post_custom($candidate_id);
    $candidate_data = get_post($candidate_id);
    $check_tabs = false;
    foreach ($custom_field_candidate as $field) {
        if ($field['tabs'] == 'skills') {
            $check_tabs = true;
        }
    }

    if(count($custom_field_candidate) > 0){
        if ($check_tabs == true) : ?>
            <?php foreach ($custom_field_candidate as $field) {
                if ($field['tabs'] == 'skills') { ?>
                    <?php civi_get_template("candidate/print/additional/field.php",array(
                        'field' => $field,
                        'candidate_data' => $candidate_data,
                        'candidate_meta_data' => $candidate_meta_data
                    ));
                }} ?>
        <?php endif;
    }
    ?>
</div>