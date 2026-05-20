<?php
if (!defined('ABSPATH')) {
    exit;
}

$candidate_id = jobportal_get_post_id_candidate();

?>
<div id="tab-skills" class="tab-info">
    <div class="skills-info block-from">
        <h5><?php esc_html_e('Skills', 'jobportal-framework') ?></h5>
        <div class="sub-head"><?php esc_html_e('We recommend at least one skill entry', 'jobportal-framework') ?></div>
        <div class="row">
            <div class="form-group col-md-12">
                <label for="candidate_skills"><?php esc_html_e('Select Skills', 'jobportal-framework') ?></label>
                <div class="form-select">
                    <div class="select2-field select2-multiple">
                        <?php
                        $taxonomy = 'candidate_skills';
                        $term_count = wp_count_terms($taxonomy, ['hide_empty' => false]);
                        if ($term_count <= 150): ?>
                            <select data-placeholder="<?php esc_attr_e('Select skills', 'jobportal-framework'); ?>" multiple="multiple"
                                class="jobportal-select2 point-mark" name="candidate_skills[]">
                                <?php jobportal_get_taxonomy_by_post_id($candidate_id, $taxonomy, false); ?>
                            </select>
                        <?php else: ?>
                            <select data-placeholder="<?php esc_attr_e('Select skills', 'jobportal-framework'); ?>" multiple="multiple"
                                class="jobportal-ajax-select2 point-mark" name="candidate_skills[]" data-taxonomy="<?php echo esc_attr($taxonomy); ?>">
                                <?php
                                $candidate_skills = array();
                                $candidate_skills_terms = get_the_terms($candidate_id, 'candidate_skills');
                                if (!is_wp_error($candidate_skills_terms) && !empty($candidate_skills_terms)) {
                                    foreach ($candidate_skills_terms as $term) {
                                        $candidate_skills[] = $term->term_id;
                                    }
                                }
                                if (!empty($candidate_skills)) {
                                    foreach ($candidate_skills as $skill_id) {
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
        </div>
    </div>
    <?php jobportal_custom_field_candidate('skills'); ?>
</div>
