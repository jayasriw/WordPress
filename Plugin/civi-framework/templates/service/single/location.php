<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$service_id    = get_the_ID();
$service_location = get_post_meta($service_id, CIVI_METABOX_PREFIX . 'service_location', true);
$service_address = get_post_meta($service_id, CIVI_METABOX_PREFIX . 'service_address');
$service_address = isset($service_address) ? $service_address[0] : '';
$map_type = civi_get_option('map_type', 'mapbox');
if (!empty($service_location['location']) && !empty($service_address)) {
    list($lat, $lng) = !empty($service_location['location']) ? explode(',', $service_location['location']) : array('', '');
} else {
    return;
}
civi_get_single_map_type($lng,$lat);
?>
<div class="civi-block-inner block-archive-inner service-maps-details">
    <h4 class="title-service"><?php esc_html_e('Location', 'civi-framework') ?></h4>
    <div class="entry-detail">
        <?php if ($map_type == 'google_map') { ?>
            <div id="google_map" class="civi-map-wrapper"></div>
        <?php } else if ($map_type == 'openstreetmap') { ?>
            <div id="openstreetmap_map" class="civi-map-wrapper"></div>
        <?php } else { ?>
            <div id="mapbox_map" class="civi-map-wrapper"></div>
        <?php } ?>
    </div>
</div>
