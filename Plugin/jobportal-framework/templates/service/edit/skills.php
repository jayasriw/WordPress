<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $service_data,$hide_service_fields;
?>
<?php if (!in_array('fields_service_category', $hide_service_fields)) : ?>
    <div class="form-group col-md-12">
        <div class="skills-info block-from">
            <h6><?php esc_html_e('Skills', 'jobportal-framework') ?></h6>
            <div class="row">
                <div class="form-group col-md-12">
                    <label for="service_skills"><?php esc_html_e('Select Skills', 'jobportal-framework') ?></label>
                    <div class="form-select">
                        <div class="select2-field select2-multiple">
                            <select data-placeholder="<?php esc_attr_e('Select skills', 'jobportal-framework'); ?>" multiple="multiple"
                                    class="jobportal-select2" name="service_skills">
                                <?php jobportal_get_taxonomy_by_post_id($service_data->ID, 'service-skills', true, false, true, 'service_skills_order'); ?>
                            </select>
                        </div>
                        <i class="fas fa-angle-down"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
