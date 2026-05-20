<?php

/**
 * The Template for displaying service archive
 */

defined('ABSPATH') || exit;

get_header('civi');
$service_map_postion = $map_event = '';
$content_service              = civi_get_option('archive_service_layout', 'layout-list');
$enable_service_show_map = civi_get_option('enable_service_show_map', 1);
$enable_service_show_map = !empty($_GET['has_map']) ? Civi_Helper::civi_clean(wp_unslash($_GET['has_map'])) : $enable_service_show_map;

$map_event = '';
if ($enable_service_show_map == 1) {
    $archive_service_filter = 'filter-canvas';
    $service_map_postion = civi_get_option('service_map_postion');
    $service_map_postion = !empty($_GET['map']) ? Civi_Helper::civi_clean(wp_unslash($_GET['map'])) : $service_map_postion;
    if ($service_map_postion == 'map-right') {
        $map_event = 'map-event';
    }
} else {
    $archive_service_filter = civi_get_option('service_filter_sidebar_option', 'filter-left');
};
$archive_service_filter = !empty($_GET['filter']) ? Civi_Helper::civi_clean(wp_unslash($_GET['filter'])) : $archive_service_filter;
$content_service = !empty($_GET['layout']) ? civi_validate_layout($_GET['layout'], 'service') : $content_service;
if ($content_service === false) {
    $content_service = civi_get_option('archive_service_layout', 'layout-list');
}
$archive_classes = array('archive-layout', 'archive-service', $archive_service_filter,$map_event, $service_map_postion);
?>

<div class="<?php echo join(' ', $archive_classes); ?>">
    <?php civi_get_template('service/archive/layout/layout-default.php'); ?>
</div>
<?php
get_footer('civi');
