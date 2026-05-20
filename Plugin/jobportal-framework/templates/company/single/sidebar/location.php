<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$company_id    = get_the_ID();
$company_location = get_post_meta($company_id, JOBPORTAL_METABOX_PREFIX . 'company_location', true);

$map_type = jobportal_get_option('map_type', 'mapbox');
if (!empty($company_location['location'])) {
    list($lat, $lng) = !empty($company_location['location']) ? explode(',', $company_location['location']) : array('', '');
    $map_direction = "http://maps.google.com/?q=" . $company_location['location'];
} else {
    return;
}

$classes = array();
$enable_sticky_sidebar_type = jobportal_get_option('enable_sticky_company_sidebar_type', 1);
if ($enable_sticky_sidebar_type) {
    $classes[] = 'has-sticky';
}

jobportal_get_single_map_type($lng,$lat);

?>
<div class="block-archive-sidebar company-sidebar <?php echo implode(" ", $classes); ?>">
    <div class="jobportal-location">
        <h3><?php esc_html_e('Location', 'jobportal-framework') ?></h3>
        <a href="<?php echo esc_url($map_direction) ?>" class="jobportal-button button-border-bottom" target="_blank"><?php esc_html_e('Get direction', 'jobportal-framework') ?></a>
    </div>
    <div class="entry-detail">
        <?php if ($map_type == 'google_map') { ?>
            <div id="google_map" class="jobportal-map-wrapper"></div>
        <?php } else if ($map_type == 'openstreetmap') { ?>
            <div id="openstreetmap_map" class="jobportal-map-wrapper"></div>
        <?php } else { ?>
            <div id="mapbox_map" class="jobportal-map-wrapper"></div>
        <?php } ?>
    </div>
</div>
