<?php
/**
 * Security helper functions
 *
 * @package Civi_Framework
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!function_exists('civi_validate_layout')) {
    /**
     * Validates and sanitizes layout parameter against allowed values
     *
     * @param string $layout The layout value from user input
     * @param string $type   The type of layout (jobs, company, candidate, service)
     * @return string|false  Returns sanitized layout or false if invalid
     */
    function civi_validate_layout($layout, $type = 'jobs') {
        $allowed_layouts = array(
            'jobs'      => array('layout-list', 'layout-grid', 'layout-full'),
            'company'   => array('layout-list', 'layout-grid'),
            'candidate' => array('layout-list', 'layout-grid'),
            'service'   => array('layout-list', 'layout-grid'),
        );

        $type_layouts = isset($allowed_layouts[$type]) ? $allowed_layouts[$type] : array();

        // Clean the input first
        $layout = sanitize_file_name(wp_unslash($layout));

        if (in_array($layout, $type_layouts, true)) {
            return $layout;
        }

        return false;
    }
}
