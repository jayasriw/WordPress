<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $hide_service_fields, $current_user, $service_data;
$user_id = $current_user->ID;

$service_location = get_post_meta($service_data->ID, JOBPORTAL_METABOX_PREFIX . 'service_location', true);
$service_map_address = isset($service_location['address']) ? $service_location['address'] : '';
$service_map_location = isset($service_location['location']) ? $service_location['location'] : '';

$map_type = jobportal_get_option('map_type', 'mapbox');
$map_default_position = jobportal_get_option('map_default_position', '');
$lat = jobportal_get_option('map_lat_default','59.325');
$lng = jobportal_get_option('map_lng_default','18.070');
if (!empty($service_location['location'])) {
    list($lat, $lng) = !empty($service_location['location']) ? explode(',', $service_location['location']) : array('', '');
} else {
    if ($map_default_position) {
        if ($map_default_position['location']) {
            list($lat, $lng) = !empty($map_default_position['location']) ? explode(',', $map_default_position['location']) : array('', '');
        }
    }
}
jobportal_get_map_type($lng,$lat,'#submit_service_form');
?>
<?php if (!in_array('fields_service_location', $hide_service_fields)) : ?>
<div class="row">
    <?php if (!in_array('fields_service_location', $hide_service_fields)) : ?>
        <div class="form-group col-md-6">
            <label><?php esc_html_e('Location', 'jobportal-framework') ?></label>
            <div class="select2-field">
                <select name="service_location" class="jobportal-select2">
                    <?php jobportal_get_taxonomy_location('service-location','service-state','service-location-state','service-state-country',$service_data->ID); ?>
                </select>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!in_array('fields_service_map', $hide_service_fields)) : ?>
        <div class="form-group col-md-6">
            <label for="search-location"><?php esc_html_e('Marker location on map', 'jobportal-framework') ?></label>
            <input type="text" id="search-location" class="form-control" name="jobportal_map_address"
                   value="<?php echo esc_attr($service_map_address); ?>"
                   placeholder="<?php esc_attr_e('Full Address', 'jobportal-framework'); ?>" autocomplete="off">
            <input type="hidden" class="form-control service-map-location" name="jobportal_map_location"
                   value="<?php echo esc_attr($service_map_location); ?>"/>
            <div id="geocoder" class="geocoder"></div>
        </div>

        <div class="form-group col-md-12 service-fields-map">
            <div class="service-fields service-map">
                <?php if ($map_type == 'google_map') { ?>
                    <div class="map_canvas maptype jobportal-map-wrapper" id="map"></div>
                <?php } else if ($map_type == 'openstreetmap') { ?>
                    <div id="openstreetmap_location" class="jobportal-map-wrapper"></div>
                <?php } else { ?>
                    <div id="mapbox_location" class="jobportal-map-wrapper"></div>
                <?php } ?>
            </div>
        </div>
        <div class="form-group col-md-6">
            <label for="service_longtitude"><?php esc_html_e('Marker longtitude', 'jobportal-framework'); ?></label>
            <input type="text" id="service_longtitude" name="jobportal_longtitude" value="<?php echo $lng ?>"
                   placeholder="<?php esc_attr_e('0.0000000', 'jobportal-framework') ?>">
        </div>
        <div class="form-group col-md-6">
            <label for="service_latitude"><?php esc_html_e('Marker latitude', 'jobportal-framework'); ?></label>
            <input type="text" id="service_latitude" name="jobportal_latitude" value="<?php echo $lat ?>"
                   placeholder="<?php esc_attr_e('0.0000000', 'jobportal-framework') ?>">
        </div>
    <?php endif; ?>
</div>
<?php endif;?>
