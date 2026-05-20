<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$candidate_details_prints = civi_get_option('candidate_details_prints');
foreach ($candidate_details_prints as $print){
    if (!in_array('enable_print_sp_projects', $candidate_details_prints)) {
        return;
    }
}

$candidate_project = get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_project_list', false);
$candidate_project = !empty($candidate_project) ? $candidate_project[0] : '';
if (empty($candidate_project[0][CIVI_METABOX_PREFIX . 'candidate_project_image_id']['url'])) {
    return;
}
?>
<div class="block-archive-inner candidate-project-details">
    <h4 class="title-candidate"><?php esc_html_e('Projects', 'civi-framework') ?></h4>
    <div class="entry-candidate-element">
        <div class="row">
            <?php
            foreach ($candidate_project as $project) :
                $thumb_src = $project[CIVI_METABOX_PREFIX . 'candidate_project_image_id']['url'];
                if (!empty($project[CIVI_METABOX_PREFIX . 'candidate_project_link'])) {
                    $project_link = $project[CIVI_METABOX_PREFIX . 'candidate_project_link'];
                } else {
                    $project_link = '#';
                }
                ?>
                <?php if (!empty($project[CIVI_METABOX_PREFIX . 'candidate_project_image_id']['url'])) : ?>
                <div class="col-6">
                    <figure>
                        <a href="<?php echo esc_url($project_link); ?>" target="_blank" class="project">
                            <img src="<?php echo esc_url($thumb_src); ?>" alt="<?php the_title_attribute(); ?>"
                                 title="<?php the_title_attribute(); ?>">
                            <div class="content-project">
                                <?php if (!empty($project[CIVI_METABOX_PREFIX . 'candidate_project_title'])) : ?>
                                    <h4><?php echo $project[CIVI_METABOX_PREFIX . 'candidate_project_title']; ?></h4>
                                <?php endif; ?>
                                <div class="project-inner">
                                    <?php if (!empty($project[CIVI_METABOX_PREFIX . 'candidate_project_description'])) : ?>
                                        <p><?php echo $project[CIVI_METABOX_PREFIX . 'candidate_project_description']; ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($project[CIVI_METABOX_PREFIX . 'candidate_project_title'])) : ?>
                                        <span class="civi-button button-border-bottom"><?php esc_html_e('View Project', 'civi-framework') ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    </figure>
                </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    $custom_field_candidate = civi_render_custom_field('candidate');
    $candidate_meta_data = get_post_custom($candidate_id);
    $candidate_data = get_post($candidate_id);
    $check_tabs = false;
    foreach ($custom_field_candidate as $field) {
        if ($field['tabs'] == 'projects') {
            $check_tabs = true;
        }
    }

    if(count($custom_field_candidate) > 0){
        if ($check_tabs == true) : ?>
            <?php foreach ($custom_field_candidate as $field) {
                if ($field['tabs'] == 'projects') { ?>
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