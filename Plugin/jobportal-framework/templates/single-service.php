<?php

/**
 * The Template for displaying all single service
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

get_header('jobportal');

/**
 * @Hook: jobportal_single_service_before
 *
 * @hooked single_service_thumbnail
 */
do_action('jobportal_single_service_before');

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

    <?php jobportal_get_template_part('content', 'single-service'); ?>

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
 * @hooked jobportal_sidebar_service
 */
do_action('jobportal_sidebar_service');
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
 * @Hook: jobportal_single_service_after
 *
 * @hooked related_service
 */
do_action('jobportal_single_service_after');

get_footer('jobportal');
