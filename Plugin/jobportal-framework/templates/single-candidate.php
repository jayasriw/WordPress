<?php

/**
 * The Template for displaying all single candidate
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}


get_header('jobportal');

/**
 * @Hook: jobportal_single_candidate_before
 *
 * @hooked gallery_candidate
 */
do_action('jobportal_single_candidate_before');

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
 * @Hook: jobportal_single_candidate_hero
 *
 * @hooked jobportal_single_candidate_hero
 */
do_action('jobportal_single_candidate_hero');
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

    <?php jobportal_get_template_part('content', 'single-candidate'); ?>

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
 * @hooked jobportal_sidebar_candidate
 */
do_action('jobportal_candidate_sidebar');
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
 * @Hook: jobportal_single_candidate_after
 *
 * @hooked related_candidate
 */
do_action('jobportal_single_candidate_after');

get_footer('jobportal');
