<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $hide_service_fields, $current_user, $service_data;
$user_id = $current_user->ID;

$service_location = get_post_meta($service_data->ID, CIVI_METABOX_PREFIX . 'service_location', true);
$service_map_address = isset($service_location['address']) ? $service_location['address'] : '';
$service_map_location = isset($service_location['location']) ? $service_location['location'] : '';

$map_type = civi_get_option('map_type', 'mapbox');
$map_default_position = civi_get_option('map_default_position', '');
$lat = civi_get_option('map_lat_default','59.325');
$lng = civi_get_option('map_lng_default','18.070');
if (!empty($service_location['location'])) {
    list($lat, $lng) = !empty($service_location['location']) ? explode(',', $service_location['location']) : array('', '');
} else {
    if ($map_default_position) {
        if ($map_default_position['location']) {
            list($lat, $lng) = !empty($map_default_position['location']) ? explode(',', $map_default_position['location']) : array('', '');
        }
    }
}
civi_get_map_type($lng,$lat,'#submit_service_form');
?>
<?php if (!in_array('fields_service_location', $hide_service_fields)) : ?>
<div class="row">
    <?php if (!in_array('fields_service_location', $hide_service_fields)) : ?>
        <div class="form-group col-md-6">
            <label><?php esc_html_e('Location', 'civi-framework') ?></label>
            <div class="select2-field">
                <select name="service_location" class="civi-select2">
                    <?php civi_get_taxonomy_location('service-location','service-state','service-location-state','service-state-country',$service_data->ID); ?>
                </select>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!in_array('fields_service_map', $hide_service_fields)) : ?>
        <div class="form-group col-md-6">
            <label for="search-location"><?php esc_html_e('Marker location on map', 'civi-framework') ?></label>
            <input type="text" id="search-location" class="form-control" name="civi_map_address"
                   value="<?php echo esc_attr($service_map_address); ?>"
                   placeholder="<?php esc_attr_e('Full Address', 'civi-framework'); ?>" autocomplete="off">
            <input type="hidden" class="form-control service-map-location" name="civi_map_location"
                   value="<?php echo esc_attr($service_map_location); ?>"/>
            <div id="geocoder" class="geocoder"></div>
        </div>

        <div class="form-group col-md-12 service-fields-map">
            <div class="service-fields service-map">
                <?php if ($map_type == 'google_map') { ?>
                    <div class="map_canvas maptype civi-map-wrapper" id="map"></div>
                <?php } else if ($map_type == 'openstreetmap') { ?>
                    <div id="openstreetmap_location" class="civi-map-wrapper"></div>
                <?php } else { ?>
                    <div id="mapbox_location" class="civi-map-wrapper"></div>
                <?php } ?>
            </div>
        </div>
        <div class="form-group col-md-6">
            <label for="service_longtitude"><?php esc_html_e('Marker longtitude', 'civi-framework'); ?></label>
            <input type="text" id="service_longtitude" name="civi_longtitude" value="<?php echo $lng ?>"
                   placeholder="<?php esc_attr_e('0.0000000', 'civi-framework') ?>">
        </div>
        <div class="form-group col-md-6">
            <label for="service_latitude"><?php esc_html_e('Marker latitude', 'civi-framework'); ?></label>
            <input type="text" id="service_latitude" name="civi_latitude" value="<?php echo $lat ?>"
                   placeholder="<?php esc_attr_e('0.0000000', 'civi-framework') ?>">
        </div>
    <?php endif; ?>
</div>
<?php endif;?>
