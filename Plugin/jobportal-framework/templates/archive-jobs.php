<?php

/**
 * The Template for displaying jobs archives
 */

defined('ABSPATH') || exit;

get_header('jobportal');

$jobs_map_position = $map_event = '';
$content_jobs = jobportal_get_option('archive_jobs_layout', 'layout-list');
$content_jobs = !empty($_GET['layout']) ? jobportal_validate_layout($_GET['layout'], 'jobs') : $content_jobs;
if ($content_jobs === false) {
    $content_jobs = jobportal_get_option('archive_jobs_layout', 'layout-list');
}
$enable_jobs_show_map = jobportal_get_option('enable_jobs_show_map', 1);
$enable_jobs_show_map = !empty($_GET['has_map']) ? JobPortal_Helper::jobportal_clean(wp_unslash($_GET['has_map'])) : $enable_jobs_show_map;

if ($enable_jobs_show_map == 1) {
    $archive_jobs_filter = 'filter-canvas';
    $jobs_map_position = jobportal_get_option('jobs_map_position');
    $jobs_map_position = !empty($_GET['map']) ? JobPortal_Helper::jobportal_clean(wp_unslash($_GET['map'])) : $jobs_map_position;
    if ($jobs_map_position == 'map-right') {
        $map_event = 'map-event';
    }
} else if ($content_jobs == 'layout-full') {
    $archive_jobs_filter = 'filter-canvas';
} else {
    $archive_jobs_filter = jobportal_get_option('jobs_filter_sidebar_option', 'filter-left');
};
$archive_jobs_filter = !empty($_GET['filter']) ? JobPortal_Helper::jobportal_clean(wp_unslash($_GET['filter'])) : $archive_jobs_filter;
$archive_classes = array('archive-layout', 'archive-jobs', $archive_jobs_filter, $map_event, $jobs_map_position);
?>

<div class="<?php echo join(' ', $archive_classes); ?>">
    <?php jobportal_get_template('jobs/archive/layout/layout-default.php'); ?>
</div>
<?php
get_footer('jobportal');
