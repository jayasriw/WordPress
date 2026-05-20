<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $service_data, $hide_service_fields;
$service_addons = get_post_meta($service_data->ID, JOBPORTAL_METABOX_PREFIX . 'service_tab_addon', false);
$service_addons = !empty($service_addons) ? $service_addons[0] : '';
?>
<?php if (!in_array('fields_service_addons', $hide_service_fields)) : ?>
    <div class="form-group col-md-12">
        <div class="addons-info block-from">
            <h6><?php esc_html_e('Add-Ons', 'jobportal-framework') ?></h6>
            <div class="jobportal-addons-wrapper">
                <?php if (!empty($service_addons)) :
                    foreach ($service_addons as $index => $addons) :?>
                    <div class="row">
                        <div class="group-title col-md-12">
                            <i class="delete-group fas fa-times"></i>
                            <h6 class="education">
                                <?php echo esc_html_e('Service', 'jobportal-framework') ?>
                                <span><?php echo $index + 1 ?></span>
                            </h6>
                            <i class="fas fa-angle-up"></i>
                        </div>
                        <div class="form-group col-md-6">
                            <label><?php esc_html_e('Title', 'jobportal-framework') ?></label>
                            <input type="text" name="service_addons_title[]"
                                   placeholder="<?php esc_attr_e('Enter Title', 'jobportal-framework'); ?>"
                            value="<?php echo $addons['jobportal-service_addons_title'] ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label><?php esc_html_e('Price', 'jobportal-framework') ?></label>
                            <input type="text" name="service_addons_price[]"
                                   placeholder="<?php esc_attr_e('Enter Price', 'jobportal-framework'); ?>"
                                   value="<?php echo $addons['jobportal-service_addons_price'] ?>">
                        </div>
                        <div class="form-group col-md-12">
                            <label><?php esc_html_e('Description', 'jobportal-framework') ?></label>
                            <textarea name="service_addons_description[]" cols="30"
                                      placeholder="<?php esc_attr_e('Short description', 'jobportal-framework'); ?>"
                                      rows="7"><?php echo $addons['jobportal-service_addons_description'] ?></textarea>
                        </div>
                    </div>
                <?php endforeach;
                endif;
                ?>

                <button type="button" class="btn-more service-fields"><i
                            class="far fa-angle-down"></i><?php esc_html_e('Add more', 'jobportal-framework') ?></button>
                <template id="template-service-addons">
                    <div class="row">
                        <div class="group-title col-md-12">
                            <i class="delete-group fas fa-times"></i>
                            <h6 class="education">
                                <?php echo esc_html_e('Service', 'jobportal-framework') ?>
                                <span>1</span>
                            </h6>
                            <i class="fas fa-angle-up"></i>
                        </div>
                        <div class="form-group col-md-6">
                            <label><?php esc_html_e('Title', 'jobportal-framework') ?></label>
                            <input type="text" name="service_addons_title[]"
                                   placeholder="<?php esc_attr_e('Enter Title', 'jobportal-framework'); ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label><?php esc_html_e('Price', 'jobportal-framework') ?></label>
                            <input type="text" name="service_addons_price[]"
                                   placeholder="<?php esc_attr_e('Enter Price', 'jobportal-framework'); ?>">
                        </div>
                        <div class="form-group col-md-12">
                            <label><?php esc_html_e('Description', 'jobportal-framework') ?></label>
                            <textarea name="service_addons_description[]" cols="30"
                                      placeholder="<?php esc_attr_e('Short description', 'jobportal-framework'); ?>"
                                      rows="7"></textarea>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
<?php endif; ?>
