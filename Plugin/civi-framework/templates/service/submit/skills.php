<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $hide_service_fields;
?>
<?php if (!in_array('fields_service_category', $hide_service_fields)) : ?>
    <div class="form-group col-md-12">
        <div class="skills-info block-from">
            <h6><?php esc_html_e('Skills', 'civi-framework') ?></h6>
            <div class="row">
                <div class="form-group col-md-12">
                    <label for="service_skills"><?php esc_html_e('Select Skills', 'civi-framework') ?></label>
                    <div class="form-select">
                        <div class="select2-field select2-multiple">
                            <select data-placeholder="<?php esc_attr_e('Select skills', 'civi-framework'); ?>" multiple="multiple"
                                    class="civi-select2" name="service_skills">
                                <?php civi_get_taxonomy('service-skills', false, false, false, true, 'service_skills_order'); ?>
                            </select>
                        </div>
                        <i class="fas fa-angle-down"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
