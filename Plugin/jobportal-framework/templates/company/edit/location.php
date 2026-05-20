<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $hide_company_fields, $current_user, $company_data;
$user_id = $current_user->ID;

$company_location = get_post_meta($company_data->ID, JOBPORTAL_METABOX_PREFIX . 'company_location', true);
$company_map_address = isset($company_location['address']) ? $company_location['address'] : '';
$company_map_location = isset($company_location['location']) ? $company_location['location'] : '';

$map_type = jobportal_get_option('map_type', 'mapbox');
$map_default_position = jobportal_get_option('map_default_position', '');
$lat = jobportal_get_option('map_lat_default', '59.325');
$lng = jobportal_get_option('map_lng_default', '18.070');
if (!empty($company_location['location'])) {
    list($lat, $lng) = !empty($company_location['location']) ? explode(',', $company_location['location']) : array('', '');
} else {
    if ($map_default_position) {
        if ($map_default_position['location']) {
            list($lat, $lng) = !empty($map_default_position['location']) ? explode(',', $map_default_position['location']) : array('', '');
        }
    }
}
jobportal_get_map_type($lng, $lat, '#submit_company_form');

$enable_add_new_company_location = jobportal_get_option('enable_add_new_company_location');
$col = '6';
if ($enable_add_new_company_location) {
    $col = '12';
}
?>
<div class="row">
    <?php if (!in_array('fields_company_location', $hide_company_fields)) : ?>
        <div class="form-group col-md-6">
            <label><?php esc_html_e('Location', 'jobportal-framework') ?></label>
            <div class="select2-field">
                <select name="company_location" class="jobportal-select2">
                    <?php jobportal_get_taxonomy_location('company-location', 'company-state', 'company-location-state', 'company-state-country', $company_data->ID); ?>
                </select>
            </div>
        </div>
        <?php if ($enable_add_new_company_location) : ?>
            <div class="form-group col-md-6">
                <label for="company_new_location"><?php esc_html_e('Add New Location', 'jobportal-framework'); ?></label>
                <input type="text" id="company_new_location" name="company_new_location" value="" placeholder="<?php esc_attr_e('Enter new location', 'jobportal-framework'); ?>">
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!in_array('fields_company_map', $hide_company_fields)) : ?>
        <div class="form-group col-md-6">
            <label for="search-location"><?php esc_html_e('Marker location on map', 'jobportal-framework') ?></label>
            <input type="text" id="search-location" class="form-control" name="jobportal_map_address"
                value="<?php echo esc_attr($company_map_address); ?>"
                placeholder="<?php esc_attr_e('Full Address', 'jobportal-framework'); ?>" autocomplete="off">
            <input type="hidden" class="form-control company-map-location" name="jobportal_map_location"
                value="<?php echo esc_attr($company_map_location); ?>" />
            <div id="geocoder" class="geocoder"></div>
        </div>

        <div class="form-group col-md-12 company-fields-map">
            <div class="company-fields company-map">
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
            <label for="company_longtitude"><?php esc_html_e('Marker longtitude', 'jobportal-framework'); ?></label>
            <input type="text" id="company_longtitude" name="jobportal_longtitude" value="<?php echo $lng ?>"
                placeholder="<?php esc_attr_e('0.0000000', 'jobportal-framework') ?>">
        </div>
        <div class="form-group col-md-6">
            <label for="company_latitude"><?php esc_html_e('Marker latitude', 'jobportal-framework'); ?></label>
            <input type="text" id="company_latitude" name="jobportal_latitude" value="<?php echo $lat ?>"
                placeholder="<?php esc_attr_e('0.0000000', 'jobportal-framework') ?>">
        </div>
    <?php endif; ?>
</div>
