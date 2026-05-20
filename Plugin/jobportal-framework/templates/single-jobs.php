<?php

/**
 * The Template for displaying all single jobs
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

get_header('jobportal');

/**
 * @Hook: jobportal_single_jobs_before
 *
 * @hooked gallery_jobs
 */
do_action('jobportal_single_jobs_before');

?>

<?php
/**
 * @Hook: jobportal_layout_wrapper_start
 *
 * @hooked layout_wrapper_start
 */
do_action('jobportal_layout_wrapper_start');
?>

<?php
/**
 * @Hook: jobportal_output_content_wrapper_start
 *
 * @hooked output_content_wrapper_start
 */
do_action('jobportal_output_content_wrapper_start');
?>

<?php while (have_posts()) : the_post(); ?>

    <?php jobportal_get_template_part('content', 'single-jobs'); ?>

<?php endwhile; // end of the loop.
?>

<?php
/**
 * @Hook: jobportal_output_content_wrapper_end
 *
 * @hooked output_content_wrapper_end
 */
do_action('jobportal_output_content_wrapper_end');
?>

<?php

/**
 * @hooked jobportal_sidebar_jobs
 */
do_action('jobportal_sidebar_jobs');

?>

<?php
/**
 * @Hook: jobportal_layout_wrapper_end
 *
 * @hooked layout_wrapper_end
 */
do_action('jobportal_layout_wrapper_end');
?>

<?php
/**
 * @Hook: jobportal_single_jobs_after
 *
 * @hooked related_jobs
 */
do_action('jobportal_single_jobs_after');

get_footer('jobportal');
