<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if($job_id){
	$jobs_id = $job_id;
} else {
	$jobs_id = get_the_ID();
}
$content = get_post_field('post_content', $jobs_id);
if (isset($content) && !empty($content)) : ?>
    <div class="block-archive-inner jobs-description-details jobportal-description-details">
        <h4 class="title-jobs"><?php esc_html_e('Description', 'jobportal-framework') ?></h4>
        <div class="jobportal-description">
            <?php echo wp_kses_post($content); ?>
        </div>
        <div class="toggle-description">
            <a href="#" class="show-more-description"><?php esc_html_e('Show more', 'jobportal-framework'); ?><i class="fas fa-angle-down"></i></a>
            <a href="#" class="hide-all-description"><?php esc_html_e('Hide less', 'jobportal-framework'); ?><i class="fas fa-angle-up"></i></a>
        </div>
    </div>
<?php endif; ?>
