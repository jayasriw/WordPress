<?php

/**
 * PHP Error Suppressor for production sites
 * Suppresses specific warnings that don't affect functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class JobPortal_PHP_Error_Suppressor
{
    private static $_instance = null;
    private $original_error_handler = null;

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        // Only activate in production environment
        if (!WP_DEBUG && !WP_DEBUG_DISPLAY) {
            add_action('init', [$this, 'setup_error_suppression'], 1);
        }
    }

    public function setup_error_suppression()
    {
        // Set up custom error handler
        $this->original_error_handler = set_error_handler([$this, 'custom_error_handler'], E_WARNING | E_NOTICE);

        // Also suppress errors on specific actions
        add_action('elementor/frontend/before_render', [$this, 'suppress_elementor_errors']);
        add_action('elementor/frontend/after_render', [$this, 'restore_error_handler']);

        add_action('elementor/editor/before_enqueue_scripts', [$this, 'suppress_elementor_errors']);
        add_action('elementor/editor/after_enqueue_scripts', [$this, 'restore_error_handler']);
    }

    /**
     * Custom error handler to suppress specific known warnings
     */
    public function custom_error_handler($errno, $errstr, $errfile, $errline)
    {
                        $suppress_patterns = [
            // Elementor FontAwesome warnings
            'Undefined array key "check"',
            'Undefined array key "wallet"',
            'Undefined array key "shield-check"',
            'Undefined array key "shield-alt"',
            'Undefined array key "pen"',
            'Undefined array key "user-md-chat"',
            'Undefined array key "briefcase"',
            'Undefined array key "credit-card"',
            'Undefined array key "money-bill"',
            'Undefined array key "coins"',
            'Undefined array key "hand-holding-dollar"',
            'Undefined array key "piggy-bank"',
            'Trying to access array offset on value of type null',

            // Other common plugin warnings that don't affect functionality
            'Undefined index:',
            'Undefined offset:',
            'Undefined variable:',
        ];

        $suppress_files = [
            'font-awesome.php',
            'elementor/core/page-assets/data-managers/',
            'elementor/includes/controls/',
        ];

        // Check if this error should be suppressed
        foreach ($suppress_patterns as $pattern) {
            if (strpos($errstr, $pattern) !== false) {
                foreach ($suppress_files as $file_pattern) {
                    if (strpos($errfile, $file_pattern) !== false) {
                        // Log suppressed error for debugging (if logging is enabled)
                        if (WP_DEBUG_LOG) {
                            error_log("JobPortal Suppressed Warning: {$errstr} in {$errfile} on line {$errline}");
                        }
                        return true; // Suppress the error
                    }
                }
            }
        }

        // For other errors, call the original error handler or return false
        if ($this->original_error_handler) {
            return call_user_func($this->original_error_handler, $errno, $errstr, $errfile, $errline);
        }

        return false; // Let PHP handle the error normally
    }

    /**
     * Temporarily suppress errors for Elementor operations
     */
    public function suppress_elementor_errors()
    {
        if (function_exists('error_reporting')) {
            // Store current level and reduce error reporting
            $this->original_error_level = error_reporting();
            error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);
        }
    }

    /**
     * Restore original error handler
     */
    public function restore_error_handler()
    {
        if (isset($this->original_error_level)) {
            error_reporting($this->original_error_level);
        }
    }

    /**
     * Add CSS to hide JavaScript console errors from appearing visually
     */
    public function add_error_hiding_styles()
    {
        if (!WP_DEBUG) {
            echo '<style>
                .php-error, .php-warning, .php-notice {
                    display: none !important;
                }
                .elementor-error {
                    display: none !important;
                }
            </style>';
        }
    }
}

// Initialize only in production
if (!WP_DEBUG && !WP_DEBUG_DISPLAY) {
    JobPortal_PHP_Error_Suppressor::instance();
}
