<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $service_data, $hide_service_fields;
$service_faq = get_post_meta($service_data->ID, CIVI_METABOX_PREFIX . 'service_tab_faq', false);
$service_faq = !empty($service_faq) ? $service_faq[0] : '';
?>
<?php if (!in_array('fields_service_faq', $hide_service_fields)) : ?>
    <div class="form-group col-md-12">
        <div class="addons-info block-from">
            <h6><?php esc_html_e('FAQs', 'civi-framework') ?></h6>
            <div class="civi-addons-wrapper">
                <?php if (!empty($service_faq)) :
                    foreach ($service_faq as $index => $faqs) :?>
                <div class="row">
                    <div class="group-title col-md-12">
                        <i class="delete-group fas fa-times"></i>
                        <h6 class="education">
                            <?php echo esc_html_e('Faq', 'civi-framework') ?>
                            <span><?php echo $index + 1 ?></span>
                        </h6>
                        <i class="fas fa-angle-up"></i>
                    </div>
                    <div class="form-group col-md-12">
                        <label><?php esc_html_e('Question', 'civi-framework') ?></label>
                        <input type="text" name="service_faq_title[]"
                               placeholder="<?php esc_attr_e('Enter Question', 'civi-framework'); ?>"
                               value="<?php echo $faqs['civi-service_faq_title'] ?>">
                    </div>
                    <div class="form-group col-md-12">
                        <label><?php esc_html_e('Answer', 'civi-framework') ?></label>
                        <textarea name="service_faq_description[]" cols="30"
                                  placeholder="<?php esc_attr_e('Enter Answer', 'civi-framework'); ?>"
                                  rows="7"><?php echo $faqs['civi-service_faq_description'] ?></textarea>
                    </div>
                </div>
                <?php endforeach;
                endif;
                ?>

                <button type="button" class="btn-more service-fields"><i
                            class="far fa-angle-down"></i><?php esc_html_e('Add more', 'civi-framework') ?></button>
                <template id="template-service-addons">
                    <div class="row">
                        <div class="group-title col-md-12">
                            <i class="delete-group fas fa-times"></i>
                            <h6 class="education">
                                <?php echo esc_html_e('Faq', 'civi-framework') ?>
                                <span>1</span>
                            </h6>
                            <i class="fas fa-angle-up"></i>
                        </div>
                        <div class="form-group col-md-12">
                            <label><?php esc_html_e('Question', 'civi-framework') ?></label>
                            <input type="text" name="service_faq_title[]"
                                   placeholder="<?php esc_attr_e('Enter Question', 'civi-framework'); ?>">
                        </div>
                        <div class="form-group col-md-12">
                            <label><?php esc_html_e('Answer', 'civi-framework') ?></label>
                            <textarea name="service_faq_description[]" cols="30"
                                      placeholder="<?php esc_attr_e('Enter Answer', 'civi-framework'); ?>"
                                      rows="7"></textarea>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
<?php endif; ?>
