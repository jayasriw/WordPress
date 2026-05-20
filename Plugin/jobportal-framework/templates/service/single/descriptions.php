<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$content = get_the_content();
if (isset($content) && !empty($content)) : ?>
    <div class="jobportal-block-inner block-archive-inner jobportal-description-details">
        <h4 class="title-service"><?php esc_html_e('Descriptions', 'jobportal-framework') ?></h4>
        <div class="jobportal-description">
            <?php echo the_content(); ?>
        </div>
        <div class="toggle-description">
            <a href="#" class="show-more-description"><?php esc_html_e('Show more', 'jobportal-framework'); ?><i class="fas fa-angle-down"></i></a>
            <a href="#" class="hide-all-description"><?php esc_html_e('Hide less', 'jobportal-framework'); ?><i class="fas fa-angle-up"></i></a>
        </div>
    </div>
<?php endif; ?>