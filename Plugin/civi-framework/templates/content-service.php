<?php

/**
 * The Template for displaying content service
 */

defined('ABSPATH') || exit;

wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'login-to-view');
$content_service = civi_get_option('archive_service_layout', 'layout-list');
$content_service = !empty($_GET['layout']) ? civi_validate_layout($_GET['layout'], 'service') : $content_service;
if ($content_service === false) {
    $content_service = civi_get_option('archive_service_layout', 'layout-list');
}

if (!empty($service_layout)) {
    $content_service = $service_layout;
}

$id = $image_size = '';

$id = get_the_ID();

if (!empty($services_id)) {
    $id = $services_id;
}

if (!empty($custom_service_image_size)) {
    $image_size = $custom_service_image_size;
}

$effect_class = 'skeleton-loading';
civi_get_template('service/content/' . $content_service . '.php', array(
    'services_id'                => $id,
    'custom_service_image_size' => $image_size,
    'layout'                  => $content_service,
    'effect_class'            => $effect_class,
));
