<?php

/**
 * Elementor Fixes
 * Fix various Elementor warnings and compatibility issues
 */

if (!defined('ABSPATH')) {
    exit;
}

class JobPortal_Elementor_Fixes
{
    private static $_instance = null;

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        add_action('init', [$this, 'init_fixes'], 1);
    }

        public function init_fixes()
    {
        // Fix FontAwesome warnings
        add_filter('elementor/icons_manager/native', [$this, 'fix_fontawesome_warnings'], 5);

        // Suppress specific warnings in production
        if (!WP_DEBUG) {
            add_action('init', [$this, 'suppress_elementor_warnings']);
        }

        // Fix FontAwesome Pro compatibility
        add_action('elementor/frontend/after_register_styles', [$this, 'fix_fontawesome_pro_conflicts'], 25);

        // Ensure proper error handling for icon SVG data
        add_filter('elementor/icons_manager/additional_tabs', [$this, 'validate_icon_data'], 10, 1);

        // Intercept FontAwesome icon rendering to prevent warnings
        add_filter('elementor/icons_manager/get_icon_html', [$this, 'fix_missing_icon_html'], 10, 3);

        // Add emergency error handler for FontAwesome specific errors
        add_action('wp_loaded', [$this, 'setup_fontawesome_error_handler']);

        // Intercept FontAwesome icon SVG data to prevent null array access
        add_filter('elementor/icons_manager/data_source', [$this, 'fix_fontawesome_icon_data'], 10, 2);

        // Add filter to ensure icon data structure before Elementor accesses it
        add_filter('elementor/icons_manager/additional_tabs', [$this, 'ensure_icon_data_structure'], 5);

        // Setup error suppression for FontAwesome warnings using output buffering
        add_action('elementor/frontend/before_render', [$this, 'start_error_suppression']);
        add_action('elementor/frontend/after_render', [$this, 'end_error_suppression']);
        add_action('elementor/editor/before_enqueue_scripts', [$this, 'start_error_suppression']);
        add_action('elementor/editor/after_enqueue_scripts', [$this, 'end_error_suppression']);
    }

        /**
     * Fix FontAwesome warnings by ensuring proper array structure
     */
    public function fix_fontawesome_warnings($icons)
    {
        if (!is_array($icons)) {
            return $icons;
        }

        // Add missing common FontAwesome icons that are often referenced but missing
        $missing_icons = $this->get_missing_fontawesome_icons();

        foreach ($missing_icons as $icon_key => $icon_config) {
            if (!isset($icons[$icon_config['family']])) {
                continue;
            }

            // Add the missing icon to the appropriate family
            if (!isset($icons[$icon_config['family']]['icons'])) {
                $icons[$icon_config['family']]['icons'] = [];
            }

            if (!isset($icons[$icon_config['family']]['icons'][$icon_key])) {
                $icons[$icon_config['family']]['icons'][$icon_key] = [
                    'name' => $icon_key,
                    'title' => $icon_config['title'],
                    'categories' => [$icon_config['category']],
                    'styles' => $icon_config['styles']
                ];
            }
        }

        foreach ($icons as $key => &$icon_data) {
            if (!is_array($icon_data)) {
                continue;
            }

            // Ensure required keys exist to prevent "Undefined array key" warnings
            $required_keys = ['check', 'name', 'label', 'url', 'enqueue', 'prefix', 'displayPrefix', 'labelIcon', 'ver', 'native'];

            foreach ($required_keys as $required_key) {
                if (!isset($icon_data[$required_key])) {
                    switch ($required_key) {
                        case 'check':
                            $icon_data[$required_key] = true;
                            break;
                        case 'name':
                            $icon_data[$required_key] = $key;
                            break;
                        case 'label':
                            $icon_data[$required_key] = ucfirst(str_replace(['-', '_'], ' ', $key));
                            break;
                        case 'url':
                        case 'enqueue':
                            $icon_data[$required_key] = false;
                            break;
                        case 'prefix':
                            $icon_data[$required_key] = 'fa-';
                            break;
                        case 'displayPrefix':
                            $icon_data[$required_key] = 'fas';
                            break;
                        case 'labelIcon':
                            $icon_data[$required_key] = 'fab fa-font-awesome';
                            break;
                        case 'ver':
                            $icon_data[$required_key] = '5.10.0';
                            break;
                        case 'native':
                            $icon_data[$required_key] = true;
                            break;
                    }
                }
            }

            // Ensure icons array exists and is properly structured
            if (isset($icon_data['icons']) && is_array($icon_data['icons'])) {
                foreach ($icon_data['icons'] as $icon_name => &$icon_info) {
                    if (!is_array($icon_info)) {
                        $icon_info = [
                            'name' => $icon_name,
                            'title' => ucfirst(str_replace(['-', '_'], ' ', $icon_name))
                        ];
                    }

                    if (!isset($icon_info['name'])) {
                        $icon_info['name'] = $icon_name;
                    }

                    if (!isset($icon_info['title'])) {
                        $icon_info['title'] = ucfirst(str_replace(['-', '_'], ' ', $icon_name));
                    }
                }
            }
        }

        return $icons;
    }

    /**
     * Get list of commonly missing FontAwesome icons
     */
    private function get_missing_fontawesome_icons()
    {
                return [
            'wallet' => [
                'family' => 'fa-solid',
                'title' => 'Wallet',
                'category' => 'payment',
                'styles' => ['solid', 'regular']
            ],
            'shield-check' => [
                'family' => 'fa-solid',
                'title' => 'Shield Check',
                'category' => 'security',
                'styles' => ['solid', 'regular']
            ],
            'shield-alt' => [
                'family' => 'fa-solid',
                'title' => 'Shield Alt',
                'category' => 'security',
                'styles' => ['solid', 'regular']
            ],
            'pen' => [
                'family' => 'fa-solid',
                'title' => 'Pen',
                'category' => 'writing',
                'styles' => ['solid', 'regular']
            ],
            'user-md-chat' => [
                'family' => 'fa-solid',
                'title' => 'User Medical Chat',
                'category' => 'medical',
                'styles' => ['solid', 'regular']
            ],
            'briefcase' => [
                'family' => 'fa-solid',
                'title' => 'Briefcase',
                'category' => 'business',
                'styles' => ['solid', 'regular']
            ],
            'credit-card' => [
                'family' => 'fa-solid',
                'title' => 'Credit Card',
                'category' => 'payment',
                'styles' => ['solid', 'regular']
            ],
            'money-bill' => [
                'family' => 'fa-solid',
                'title' => 'Money Bill',
                'category' => 'payment',
                'styles' => ['solid', 'regular']
            ],
            'coins' => [
                'family' => 'fa-solid',
                'title' => 'Coins',
                'category' => 'payment',
                'styles' => ['solid']
            ],
            'hand-holding-dollar' => [
                'family' => 'fa-solid',
                'title' => 'Hand Holding Dollar',
                'category' => 'payment',
                'styles' => ['solid']
            ],
            'piggy-bank' => [
                'family' => 'fa-solid',
                'title' => 'Piggy Bank',
                'category' => 'payment',
                'styles' => ['solid']
            ],
            'user-ninja' => [
                'family' => 'fa-solid',
                'title' => 'User Ninja',
                'category' => 'users',
                'styles' => ['solid']
            ]
        ];
    }

    /**
     * Suppress specific Elementor warnings in production
     */
    public function suppress_elementor_warnings()
    {
        if (function_exists('error_reporting')) {
            // Store current error reporting level
            $current_error_reporting = error_reporting();

            // Add filter to suppress warnings only from Elementor FontAwesome files
            add_action('elementor/frontend/before_render', function() {
                set_error_handler([$this, 'custom_error_handler'], E_WARNING | E_NOTICE);
            });

            add_action('elementor/frontend/after_render', function() use ($current_error_reporting) {
                restore_error_handler();
                error_reporting($current_error_reporting);
            });
        }
    }

    /**
     * Custom error handler to suppress specific FontAwesome warnings
     */
    public function custom_error_handler($errno, $errstr, $errfile, $errline)
    {
        // Only suppress specific FontAwesome warnings
        if (strpos($errfile, 'font-awesome.php') !== false &&
            (strpos($errstr, 'Undefined array key') !== false ||
             strpos($errstr, 'Trying to access array offset on value of type null') !== false ||
             strpos($errstr, 'user-ninja') !== false)) {
            return true; // Suppress the warning
        }

        // Let other errors pass through
        return false;
    }

    /**
     * Fix FontAwesome Pro conflicts with Elementor
     */
    public function fix_fontawesome_pro_conflicts()
    {
        // Ensure FontAwesome Pro styles don't conflict with Elementor's
        wp_add_inline_style('elementor-frontend', '
            .elementor-icon i[class*="fa-"]:before {
                font-family: "Font Awesome 5 Pro", "Font Awesome 5 Free" !important;
            }
            .elementor-icon i.fas:before {
                font-weight: 900;
                font-family: "Font Awesome 5 Pro", "Font Awesome 5 Free" !important;
            }
            .elementor-icon i.far:before {
                font-weight: 400;
                font-family: "Font Awesome 5 Pro", "Font Awesome 5 Free" !important;
            }
            .elementor-icon i.fal:before {
                font-weight: 300;
                font-family: "Font Awesome 5 Pro" !important;
            }
            .elementor-icon i.fab:before {
                font-weight: 400;
                font-family: "Font Awesome 5 Brands" !important;
            }
        ');
    }

    /**
     * Validate icon data to prevent errors
     */
    public function validate_icon_data($tabs)
    {
        if (!is_array($tabs)) {
            return $tabs;
        }

        foreach ($tabs as $key => &$tab) {
            if (!is_array($tab)) {
                continue;
            }

            // Ensure tab has required structure
            if (!isset($tab['icons'])) {
                $tab['icons'] = [];
            }

            if (!is_array($tab['icons'])) {
                $tab['icons'] = [];
            }

            // Validate each icon in the tab
            foreach ($tab['icons'] as $icon_key => &$icon) {
                if (!is_array($icon)) {
                    $icon = ['id' => $icon_key, 'title' => $icon_key];
                }

                if (!isset($icon['id'])) {
                    $icon['id'] = $icon_key;
                }

                if (!isset($icon['title'])) {
                    $icon['title'] = $icon_key;
                }
            }
        }

        return $tabs;
    }

    /**
     * Fix missing icon HTML to prevent warnings
     */
    public function fix_missing_icon_html($html, $icon, $attributes)
    {
        // If icon HTML is empty or contains error, provide fallback
        if (empty($html) || strpos($html, 'undefined') !== false) {
            if (isset($icon['value']) && !empty($icon['value'])) {
                $icon_class = $icon['value'];

                // Ensure proper FontAwesome classes
                if (strpos($icon_class, 'fa-') === 0 && strpos($icon_class, 'fa ') !== 0) {
                    $icon_class = 'fas ' . $icon_class;
                }

                // Generate safe HTML
                $attributes_string = '';
                if (is_array($attributes)) {
                    foreach ($attributes as $attr => $value) {
                        $attributes_string .= ' ' . esc_attr($attr) . '="' . esc_attr($value) . '"';
                    }
                }

                $html = '<i class="' . esc_attr($icon_class) . '"' . $attributes_string . '></i>';
            }
        }

        return $html;
    }

    /**
     * Setup emergency error handler for FontAwesome warnings
     */
    public function setup_fontawesome_error_handler()
    {
        if (!WP_DEBUG && !WP_DEBUG_DISPLAY) {
            set_error_handler(function($errno, $errstr, $errfile, $errline) {
                // Suppress FontAwesome related warnings specifically
                if (strpos($errfile, 'font-awesome.php') !== false &&
                    (strpos($errstr, 'Undefined array key') !== false ||
                     strpos($errstr, 'Trying to access array offset on value of type null') !== false ||
                     strpos($errstr, 'user-ninja') !== false ||
                     strpos($errstr, 'wallet') !== false ||
                     strpos($errstr, 'shield-check') !== false)) {

                    // Log for debugging but don't display
                    if (WP_DEBUG_LOG) {
                        error_log("Suppressed FontAwesome warning: {$errstr} in {$errfile}:{$errline}");
                    }

                    return true; // Suppress the warning
                }

                // Let other errors through
                return false;
            }, E_WARNING | E_NOTICE);
        }
    }

    /**
     * Fix FontAwesome icon data to prevent null array access
     * Intercepts icon data before Elementor processes it
     */
    public function fix_fontawesome_icon_data($icon_data, $icon_key)
    {
        // Ensure icon_data is always an array
        if (!is_array($icon_data)) {
            $icon_data = [];
        }

        // Ensure required keys exist to prevent "Undefined array key" warnings
        $required_keys = ['path', 'width', 'height', 'viewBox', 'unicode'];

        foreach ($required_keys as $key) {
            if (!isset($icon_data[$key])) {
                // Provide default values
                switch ($key) {
                    case 'path':
                        $icon_data[$key] = '';
                        break;
                    case 'width':
                        $icon_data[$key] = 512;
                        break;
                    case 'height':
                        $icon_data[$key] = 512;
                        break;
                    case 'viewBox':
                        $icon_data[$key] = '0 0 512 512';
                        break;
                    case 'unicode':
                        $icon_data[$key] = '';
                        break;
                }
            }
        }

        return $icon_data;
    }

    /**
     * Ensure icon data structure is properly initialized
     * This runs before Elementor processes icon data
     */
    public function ensure_icon_data_structure($tabs)
    {
        if (!is_array($tabs)) {
            return $tabs;
        }

        foreach ($tabs as $key => &$tab) {
            if (!is_array($tab)) {
                continue;
            }

            // Ensure tab has proper structure
            if (!isset($tab['icons'])) {
                $tab['icons'] = [];
            }

            if (!is_array($tab['icons'])) {
                $tab['icons'] = [];
            }

            // Ensure each icon has proper structure
            foreach ($tab['icons'] as $icon_key => &$icon) {
                if (!is_array($icon)) {
                    $icon = [];
                }

                // Ensure required icon properties exist
                if (!isset($icon['path'])) {
                    $icon['path'] = '';
                }
                if (!isset($icon['width'])) {
                    $icon['width'] = 512;
                }
                if (!isset($icon['height'])) {
                    $icon['height'] = 512;
                }
                if (!isset($icon['viewBox'])) {
                    $icon['viewBox'] = '0 0 512 512';
                }
            }
        }

        return $tabs;
    }

    private $fontawesome_error_handler_active = false;

    /**
     * Start error suppression for FontAwesome warnings
     */
    public function start_error_suppression()
    {
        if ($this->fontawesome_error_handler_active) {
            return;
        }

        // Store previous error handler
        $previous_handler = set_error_handler([$this, 'handle_fontawesome_warnings'], E_WARNING | E_NOTICE);

        // Store it if it wasn't null
        if ($previous_handler !== null) {
            $this->previous_error_handler = $previous_handler;
        }

        $this->fontawesome_error_handler_active = true;
    }

    /**
     * End error suppression for FontAwesome warnings
     */
    public function end_error_suppression()
    {
        if (!$this->fontawesome_error_handler_active) {
            return;
        }

        restore_error_handler();
        $this->fontawesome_error_handler_active = false;
    }

    /**
     * Handle FontAwesome warnings specifically
     */
    public function handle_fontawesome_warnings($errno, $errstr, $errfile, $errline)
    {
        // Check if this is a FontAwesome warning we want to suppress
        if (strpos($errfile, 'font-awesome.php') !== false &&
            (strpos($errstr, 'Undefined array key') !== false ||
             strpos($errstr, 'Trying to access array offset on value of type null') !== false ||
             strpos($errstr, 'user-ninja') !== false)) {

            // Log for debugging if enabled
            if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
                error_log("Suppressed FontAwesome warning: {$errstr} in {$errfile}:{$errline}");
            }

            return true; // Suppress the warning
        }

        // Pass through to previous handler or default behavior
        if (isset($this->previous_error_handler) && is_callable($this->previous_error_handler)) {
            return call_user_func($this->previous_error_handler, $errno, $errstr, $errfile, $errline);
        }

        // Use default PHP error handler behavior for other warnings
        return false;
    }

    private $previous_error_handler = null;
}

// Initialize the fixes
JobPortal_Elementor_Fixes::instance();
