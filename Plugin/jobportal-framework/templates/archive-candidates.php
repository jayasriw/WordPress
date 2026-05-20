<?php

/**
 * The Template for displaying candidate archive
 */

defined('ABSPATH') || exit;

get_header('jobportal');
$map_event = $candidate_map_postion = '';
$content_candidate = jobportal_get_option('archive_candidate_layout', 'layout-list');
$enable_candidate_show_map = jobportal_get_option('enable_candidate_show_map', 1);
$enable_candidate_show_map = !empty($_GET['has_map']) ? JobPortal_Helper::jobportal_clean(wp_unslash($_GET['has_map'])) : $enable_candidate_show_map;

if ($enable_candidate_show_map == 1) {
    $archive_candidate_filter = 'filter-canvas';
    $candidate_map_postion = jobportal_get_option('candidate_map_postion');
    $candidate_map_postion = !empty($_GET['map']) ? JobPortal_Helper::jobportal_clean(wp_unslash($_GET['map'])) : $candidate_map_postion;
    if ($candidate_map_postion == 'map-right') {
        $map_event = 'map-event';
    }
} else {
    $archive_candidate_filter = jobportal_get_option('candidate_filter_sidebar_option', 'filter-left');
};
$archive_candidate_filter = !empty($_GET['filter']) ? JobPortal_Helper::jobportal_clean(wp_unslash($_GET['filter'])) : $archive_candidate_filter;
$content_candidate = !empty($_GET['layout']) ? jobportal_validate_layout($_GET['layout'], 'candidate') : $content_candidate;
if ($content_candidate === false) {
    $content_candidate = jobportal_get_option('archive_candidate_layout', 'layout-list');
}
$archive_classes = array('archive-layout', 'archive-candidates', $archive_candidate_filter, $map_event, $candidate_map_postion);

?>
<div class="<?php echo join(' ', $archive_classes); ?>">
    <?php jobportal_get_template('candidate/archive/layout/layout-default.php'); ?>
</div>
<?php
get_footer('jobportal');
