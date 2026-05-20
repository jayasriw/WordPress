<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $hide_jobs_fields, $current_user;
$user_id = $current_user->ID;

$jobs_location = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'jobs_user_location', true);
$jobs_map_address = isset($jobs_location['address']) ? $jobs_location['address'] : '';
$jobs_map_location = isset($jobs_location['location']) ? $jobs_location['location'] : '';

$map_type = jobportal_get_option('map_type', 'mapbox');
$map_default_position = jobportal_get_option('map_default_position', '');
$enable_add_new_job_location = jobportal_get_option('enable_add_new_job_location', '');
$lat = jobportal_get_option('map_lat_default','59.325');
$lng = jobportal_get_option('map_lng_default','18.070');
if (!empty($jobs_location['location'])) {
    list($lat, $lng) = !empty($jobs_location['location']) ? explode(',', $jobs_location['location']) : array('', '');
} else {
    if ($map_default_position) {
        if ($map_default_position['location']) {
            list($lat, $lng) = !empty($map_default_position['location']) ? explode(',', $map_default_position['location']) : array('', '');
        }
    }
}

jobportal_get_map_type($lng,$lat,'#submit_jobs_form');

$col = '6';
if( $enable_add_new_job_location ){
	$col = '12';
}
?>
<div class="row">
    <?php if (!in_array('fields_jobs_location', $hide_jobs_fields)) : ?>
        <div class="form-group col-lg-6">
            <label><?php esc_html_e('Location', 'jobportal-framework') ?></label>
            <div class="select2-field">
				<select name="jobs_location" class="jobportal-select2">
					<?php jobportal_get_taxonomy_location('jobs-location','jobs-state','jobs-location-state','jobs-state-country'); ?>
				</select>
			</div>
        </div>
		<?php if( $enable_add_new_job_location ) : ?>
			<div class="form-group col-md-6">
				<label for="jobs_new_location"><?php esc_html_e('Add New Location', 'jobportal-framework'); ?></label>
				<input type="text" id="jobs_new_location" name="jobs_new_location" value="" placeholder="<?php esc_attr_e('Enter new location', 'jobportal-framework'); ?>">
			</div>
		<?php endif; ?>

		<?php if (!in_array('fields_map', $hide_jobs_fields)) : ?>
			<div class="form-group col-lg-<?php echo $col; ?>" >
				<label for="search-location"><?php esc_html_e('Marker location on map', 'jobportal-framework') ?></label>
				<div class="input-area">
					<input type="text" id="search-location" class="form-control" name="jobportal_map_address"
						value="<?php echo esc_attr($jobs_map_address); ?>"
						placeholder="<?php esc_attr_e('Full Address', 'jobportal-framework'); ?>" autocomplete="off">
				</div>
				<input type="hidden" class="form-control jobs-map-location" name="jobportal_map_location"
					value="<?php echo esc_attr($jobs_map_location); ?>"/>
				<div id="geocoder" class="geocoder"></div>
			</div>
			<div class="form-group col-md-12 jobs-fields-map">
				<div class="jobs-fields jobs-map">
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
				<label for="jobs_longtitude"><?php esc_html_e('Marker longtitude', 'jobportal-framework'); ?></label>
				<input type="text" id="jobs_longtitude" name="jobportal_longtitude" value="<?php echo esc_attr($lng); ?>"
					placeholder="<?php esc_attr_e('0.0000000', 'jobportal-framework') ?>">
			</div>
			<div class="form-group col-md-6">
				<label for="jobs_latitude"><?php esc_html_e('Marker latitude', 'jobportal-framework'); ?></label>
				<input type="text" id="jobs_latitude" name="jobportal_latitude" value="<?php echo esc_attr($lat); ?>"
					placeholder="<?php esc_attr_e('0.0000000', 'jobportal-framework') ?>">
			</div>
		<?php endif; ?>
    <?php endif; ?>
</div>
