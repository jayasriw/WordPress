<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$service_id    = get_the_ID();
$service_faq = get_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_tab_faq', true);
if(empty($service_faq[0]['jobportal-service_faq_title'])){
    return;
}
?>
<div class="jobportal-block-inner block-archive-inner service-faq-details">
    <h4 class="title-service"><?php esc_html_e('FAQ', 'jobportal-framework') ?></h4>
    <?php foreach ($service_faq as $faq) { ?>
        <?php if(!empty($faq['jobportal-service_faq_title'])) : ?>
        <div class="faq-inner">
            <div class="faq-header">
                <h5><?php echo $faq['jobportal-service_faq_title']; ?></h5>
                <span><i class="fas fa-chevron-down"></i></span>
            </div>
            <div class="faq-content">
                <?php echo $faq['jobportal-service_faq_description']; ?>
            </div>
        </div>
        <?php endif; ?>
    <?php } ?>
</div>