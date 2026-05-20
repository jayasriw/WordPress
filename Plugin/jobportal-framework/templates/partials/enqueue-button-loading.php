<?php
/**
 * Enqueue button-loading utility script
 *
 * This file is included in package templates to ensure the button-loading utility is available
 *
 * @package jobportal-framework
 */

if (!defined('ABSPATH')) {
    exit;
}

// Register button loading utility (global - available for all buttons)
wp_enqueue_script(
    JOBPORTAL_PLUGIN_PREFIX . 'button-loading',
    JOBPORTAL_PLUGIN_URL . 'assets/js/utils/button-loading.js',
    array('jquery'),
    JOBPORTAL_PLUGIN_VER,
    true
);
