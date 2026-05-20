<?php

/**
 * The Template for displaying content jobs
 */

defined('ABSPATH') || exit;

wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'login-to-view');
$content_jobs = jobportal_get_option('archive_jobs_layout', 'layout-list');
$content_jobs = !empty($_GET['layout']) ? jobportal_validate_layout($_GET['layout'], 'jobs') : $content_jobs;
if ($content_jobs === false) {
    $content_jobs = jobportal_get_option('archive_jobs_layout', 'layout-list');
}

if (!empty($jobs_layout)) {
    $content_jobs = $jobs_layout;
}

$id = $image_size = '';

$id = get_the_ID();

if (!empty($jobs_id)) {
    $id = $jobs_id;
}

if (!empty($custom_jobs_image_size)) {
    $image_size = $custom_jobs_image_size;
}

$effect_class = 'skeleton-loading';

jobportal_get_template('jobs/content/' . $content_jobs . '.php', array(
    'jobs_id'                => $id,
    'custom_jobs_image_size' => $image_size,
    'layout'                  => $content_jobs,
    'effect_class'            => $effect_class,
));
