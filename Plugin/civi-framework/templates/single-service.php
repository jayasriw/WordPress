<?php

/**
 * The Template for displaying all single service
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

get_header('civi');

/**
 * @Hook: civi_single_service_before
 *
 * @hooked single_service_thumbnail
 */
do_action('civi_single_service_before');

?>

<?php
/**
 * @Hook: civi_layout_wrapper_start
 *
 * @hooked layout_wrapper_start
 */
do_action('civi_layout_wrapper_start');
?>

<?php
/**
 * @Hook: civi_output_content_wrapper_start
 *
 * @hooked output_content_wrapper_start
 */
do_action('civi_output_content_wrapper_start');
?>

<?php while (have_posts()) : the_post(); ?>

    <?php civi_get_template_part('content', 'single-service'); ?>

<?php endwhile; // end of the loop.
?>

<?php
/**
 * @Hook: civi_output_content_wrapper_end
 *
 * @hooked output_content_wrapper_end
 */
do_action('civi_output_content_wrapper_end');
?>

<?php
/**
 * @hooked civi_sidebar_service
 */
do_action('civi_sidebar_service');
?>

<?php
/**
 * @Hook: civi_layout_wrapper_end
 *
 * @hooked layout_wrapper_end
 */
do_action('civi_layout_wrapper_end');
?>

<?php
/**
 * @Hook: civi_single_service_after
 *
 * @hooked related_service
 */
do_action('civi_single_service_after');

get_footer('civi');
