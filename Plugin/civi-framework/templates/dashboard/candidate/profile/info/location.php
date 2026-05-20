<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $hide_candidate_fields, $current_user, $candidate_data;
$user_id = $current_user->ID;

$candidate_location = get_post_meta($candidate_data->ID, CIVI_METABOX_PREFIX . 'candidate_location', true);
$candidate_map_address = isset($candidate_location['address']) ? $candidate_location['address'] : '';
$candidate_map_location = isset($candidate_location['location']) ? $candidate_location['location'] : '';

$map_type = civi_get_option('map_type', 'mapbox');
$map_default_position = civi_get_option('map_default_position', '');
$lat = civi_get_option('map_lat_default', '59.325');
$lng = civi_get_option('map_lng_default', '18.070');
if (!empty($candidate_location['location'])) {
    list($lat, $lng) = !empty($candidate_location['location']) ? explode(',', $candidate_location['location']) : array('', '');
} else {
    if ($map_default_position) {
        if ($map_default_position['location']) {
            list($lat, $lng) = !empty($map_default_position['location']) ? explode(',', $map_default_position['location']) : array('', '');
        }
    }
}

civi_get_map_type($lng, $lat, '#candidate-profile-form');

?>
<?php
// Check if location section should be visible (at least one field is visible)
$location_hidden = is_array($hide_candidate_fields) && in_array('fields_candidate_location', $hide_candidate_fields, true);
$map_hidden = is_array($hide_candidate_fields) && in_array('fields_candidate_map', $hide_candidate_fields, true);
$location_visible = !($location_hidden && $map_hidden);

if ($location_visible) : ?>
    <div class="candidate-submit-location block-from civi-map-form" id="submit_candidate_form">
        <h6><?php esc_html_e('Location', 'civi-framework') ?></h6>
        <div class="row">
            <?php if (!in_array('fields_candidate_location', $hide_candidate_fields)) : ?>
                <div class="form-group col-lg-6">
                    <label for="candidate_location"><?php esc_html_e('Location', 'civi-framework') ?></label>
                    <div class="select2-field">
                        <select name="candidate_location" id="candidate_location" class="civi-select2 point-mark">
                            <?php civi_get_taxonomy_location('candidate_locations', 'candidate_state', 'candidate_locations-state', 'candidate_state-country', $candidate_data->ID); ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (!in_array('fields_candidate_map', $hide_candidate_fields)) : ?>
                <div class="form-group col-lg-6">
                    <label for="search-location"><?php esc_html_e('Marker location on map', 'civi-framework') ?></label>
                    <input type="text" id="search-location" class="form-control" name="civi_map_address"
                        value="<?php echo esc_attr($candidate_map_address); ?>"
                        placeholder="<?php esc_attr_e('Full Address', 'civi-framework'); ?>" autocomplete="off">
                    <input type="hidden" class="form-control candidate-map-location" name="civi_map_location"
                        value="<?php echo esc_attr($candidate_map_location); ?>" />
                    <div id="geocoder" class="geocoder"></div>
                </div>
                <div class="form-group col-md-12 candidate-fields-map">
                    <div class="candidate-fields candidate-map">
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
                    <label for="candidate_longtitude"><?php esc_html_e('Marker longitude', 'civi-framework'); ?></label>
                    <input type="text" id="candidate_longtitude" name="civi_longtitude" value="<?php echo $lng ?>"
                        placeholder="<?php esc_attr_e('0.0000000', 'civi-framework') ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="candidate_latitude"><?php esc_html_e('Marker latitude', 'civi-framework'); ?></label>
                    <input type="text" id="candidate_latitude" name="civi_latitude" value="<?php echo $lat ?>"
                        placeholder="<?php esc_attr_e('0.0000000', 'civi-framework') ?>">
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
