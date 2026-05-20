<?php

/**
 * The Template for displaying company archive
 */

defined('ABSPATH') || exit;

get_header('jobportal');
wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'login-to-view');
$company_map_postion = $map_event = '';
$content_company              = jobportal_get_option('archive_company_layout', 'layout-list');
$enable_company_show_map = jobportal_get_option('enable_company_show_map', 1);
$enable_company_show_map = !empty($_GET['has_map']) ? JobPortal_Helper::jobportal_clean(wp_unslash($_GET['has_map'])) : $enable_company_show_map;

$map_event = '';
if ($enable_company_show_map == 1) {
    $archive_company_filter = 'filter-canvas';
    $company_map_postion = jobportal_get_option('company_map_postion');
    $company_map_postion = !empty($_GET['map']) ? JobPortal_Helper::jobportal_clean(wp_unslash($_GET['map'])) : $company_map_postion;
    if ($company_map_postion == 'map-right') {
        $map_event = 'map-event';
    }
} else {
    $archive_company_filter = jobportal_get_option('company_filter_sidebar_option', 'filter-left');
};
$archive_company_filter = !empty($_GET['filter']) ? JobPortal_Helper::jobportal_clean(wp_unslash($_GET['filter'])) : $archive_company_filter;
$content_company = !empty($_GET['layout']) ? jobportal_validate_layout($_GET['layout'], 'company') : $content_company;
if ($content_company === false) {
    $content_company = jobportal_get_option('archive_company_layout', 'layout-list');
}
$archive_classes = array('archive-layout', 'archive-company', $archive_company_filter,$map_event, $company_map_postion);
?>

<div class="<?php echo join(' ', $archive_classes); ?>">
    <?php jobportal_get_template('company/archive/layout/layout-default.php'); ?>
</div>
<?php
get_footer('jobportal');
