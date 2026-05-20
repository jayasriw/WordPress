<?php

/**
 * The Template for displaying content candidate
 */

defined('ABSPATH') || exit;

$content_candidate= civi_get_option('archive_company_layout', 'layout-list');
$content_candidate = !empty($_GET['layout']) ? civi_validate_layout($_GET['layout'], 'candidate') : $content_candidate;
if ($content_candidate === false) {
    $content_candidate = civi_get_option('archive_company_layout', 'layout-list');
}
$content_button_type = '';
$etw = 0;

if (!empty($candidate_layout)) {
    $content_candidate = $candidate_layout;
}

if (!empty($button_type)) {
    $content_button_type = $button_type;
}

if (!empty($excerpt_trim_words)) {
    $etw = $excerpt_trim_words;
}

$id = $image_size = '';

$id = get_the_ID();

if (!empty($candidate_id)) {
    $id = $candidate_id;
}

if (!empty($custom_candidate_image_size)) {
    $image_size = $custom_company_image_size;
}

$effect_class = 'skeleton-loading';

civi_get_template('candidate/content/' . $content_candidate . '.php', array(
    'candidate_id'                => $id,
    'custom_candidate_image_size' => $image_size,
    'layout'                      => $content_candidate,
    'content_button_type'         => $content_button_type,
    'etw'         				  => $etw,
    'effect_class'                => $effect_class,
));
