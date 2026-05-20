<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $hide_service_fields;
?>
<?php if (!in_array('fields_service_addons', $hide_service_fields)) : ?>
    <div class="form-group col-md-12">
        <div class="addons-info block-from">
            <h6><?php esc_html_e('Add-Ons', 'civi-framework') ?></h6>
            <div class="civi-addons-wrapper">
                <div class="row">
                    <div class="group-title col-md-12">
                        <i class="delete-group fas fa-times"></i>
                        <h6 class="education">
                            <?php echo esc_html_e('Service', 'civi-framework') ?>
                            <span>1</span>
                        </h6>
                        <i class="fas fa-angle-up"></i>
                    </div>
                    <div class="form-group col-md-6">
                        <label><?php esc_html_e('Title', 'civi-framework') ?></label>
                        <input type="text" name="service_addons_title[]"
                               placeholder="<?php esc_attr_e('Enter Title', 'civi-framework'); ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label><?php esc_html_e('Price', 'civi-framework') ?></label>
                        <input type="text" name="service_addons_price[]"
                               placeholder="<?php esc_attr_e('Enter Price', 'civi-framework'); ?>">
                    </div>
                    <div class="form-group col-md-12">
                        <label><?php esc_html_e('Description', 'civi-framework') ?></label>
                        <textarea name="service_addons_description[]" cols="30"
                                  placeholder="<?php esc_attr_e('Short description', 'civi-framework'); ?>"
                                  rows="7"></textarea>
                    </div>
                </div>

                <button type="button" class="btn-more service-fields"><i
                            class="far fa-angle-down"></i><?php esc_html_e('Add more', 'civi-framework') ?></button>
                <template id="template-service-addons">
                    <div class="row">
                        <div class="group-title col-md-12">
                            <i class="delete-group fas fa-times"></i>
                            <h6 class="education">
                                <?php echo esc_html_e('Service', 'civi-framework') ?>
                                <span>1</span>
                            </h6>
                            <i class="fas fa-angle-up"></i>
                        </div>
                        <div class="form-group col-md-6">
                            <label><?php esc_html_e('Title', 'civi-framework') ?></label>
                            <input type="text" name="service_addons_title[]"
                                   placeholder="<?php esc_attr_e('Enter Title', 'civi-framework'); ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label><?php esc_html_e('Price', 'civi-framework') ?></label>
                            <input type="text" name="service_addons_price[]"
                                   placeholder="<?php esc_attr_e('Enter Price', 'civi-framework'); ?>">
                        </div>
                        <div class="form-group col-md-12">
                            <label><?php esc_html_e('Description', 'civi-framework') ?></label>
                            <textarea name="service_addons_description[]" cols="30"
                                      placeholder="<?php esc_attr_e('Short description', 'civi-framework'); ?>"
                                      rows="7"></textarea>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
<?php endif; ?>
