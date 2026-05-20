<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if($job_id){
	$jobs_id = $job_id;
} else {
	$jobs_id = get_the_ID();
}

?>
<div class="block-archive-inner jobs-apply-details">
    <div class="info-apply">
        <h4><?php esc_html_e('Interested in this job?', 'jobportal-framework') ?></h4>
        <p class="days">
            <span> <?php echo jobportal_get_expiration_apply($jobs_id); ?> </span><?php esc_html_e('days left to apply', 'jobportal-framework') ?>
        </p>
    </div>
    <?php jobportal_get_status_apply($jobs_id);?>
</div>
