<?php

/**
 * Direct error suppression for textdomain issues
 */
// Suppress the specific error immediately when this file loads
if (!function_exists('civi_direct_error_suppression')) {
	function civi_direct_error_suppression()
	{
		if (function_exists('_doing_it_wrong')) {
			add_filter('doing_it_wrong_trigger_error', function ($trigger, $function_name, $message) {
				if (
					$function_name === '_load_textdomain_just_in_time' &&
					strpos($message, 'miniorange-login-openid') !== false
				) {
					return false;
				}
				return $trigger;
			}, 10, 3);
		}
	}
	civi_direct_error_suppression();
}

/**
 * Suppress textdomain errors immediately
 */
function civi_suppress_textdomain_notices()
{
	add_filter('doing_it_wrong_trigger_error', function ($trigger, $function_name, $message) {
		if (
			$function_name === '_load_textdomain_just_in_time' &&
			strpos($message, 'miniorange-login-openid') !== false
		) {
			return false;
		}
		return $trigger;
	}, 10, 3);
}

/**
 * Suppress textdomain errors immediately
 */
civi_suppress_textdomain_notices();

/**
 * Start output buffering to catch and suppress textdomain errors
 */
function civi_start_error_suppression()
{
	ob_start(function ($buffer) {
		$buffer = preg_replace('/Function _load_textdomain_just_in_time was called incorrectly.*?miniorange-login-openid.*?<\/p>/s', '', $buffer);
		return $buffer;
	});
}
add_action('muplugins_loaded', 'civi_start_error_suppression', 1);

/**
 * Define constants
 */
$civi_theme = wp_get_theme();

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

if (!empty($civi_theme['Template'])) {
	$civi_theme = wp_get_theme($civi_theme['Template']);
}

if (!defined('CIVI_THEME_NAME')) {
	define('CIVI_THEME_NAME', $civi_theme['Name']);
}

if (!defined('CIVI_THEME_SLUG')) {
	define('CIVI_THEME_SLUG', $civi_theme['Template']);
}

if (!defined('CIVI_THEME_VER')) {
	define('CIVI_THEME_VER', $civi_theme['Version']);
}

if (!defined('CIVI_THEME_DIR')) {
	define('CIVI_THEME_DIR', trailingslashit(get_template_directory()));
}

if (!defined('CIVI_THEME_URI')) {
	define('CIVI_THEME_URI', get_template_directory_uri());
}

if (!defined('CIVI_THEME_PREFIX')) {
	define('CIVI_THEME_PREFIX', 'civi_');
}

if (!defined('CIVI_METABOX_PREFIX')) {
	define('CIVI_METABOX_PREFIX', 'civi-');
}

if (!defined('CIVI_CUSTOMIZER_DIR')) {
	define('CIVI_CUSTOMIZER_DIR', CIVI_THEME_DIR . '/customizer');
}

if (!defined('CIVI_IMAGES')) {
	define('CIVI_IMAGES', CIVI_THEME_URI . '/assets/images/');
}

define('CIVI_ELEMENTOR_DIR', get_template_directory() . DS . 'elementor');
define('CIVI_ELEMENTOR_URI', get_template_directory_uri() . '/elementor');
define('CIVI_ELEMENTOR_ASSETS', get_template_directory_uri() . '/elementor/assets');

/**
 * Load Theme Class.
 *
 */
foreach (glob(get_template_directory() . '/includes/*.php') as $theme_class) {
	require_once($theme_class);
}

require_once CIVI_ELEMENTOR_DIR . '/class-entry.php';

function civi_load_elementor_options()
{
	update_option('elementor_disable_typography_schemes', 'yes');
}

add_action('after_switch_theme', 'civi_load_elementor_options');

add_filter('wp_mail_smtp_core_wp_mail_function_incorrect_location_notice', '__return_false');

/**
 * Optimize database queries by priming caches and reducing duplicate queries
 */
function civi_optimize_queries()
{
	// Prime post meta cache for main queries to reduce duplicate meta queries
	add_action('the_posts', function ($posts, $query) {
		if (!empty($posts) && !$query->get('suppress_filters')) {
			$post_ids = wp_list_pluck($posts, 'ID');
			update_meta_cache('post', $post_ids);
		}
		return $posts;
	}, 10, 2);

	// Prime user meta cache for logged-in users
	add_action('init', function () {
		if (is_user_logged_in()) {
			$user_id = get_current_user_id();
			update_meta_cache('user', array($user_id));
		}
	}, 1);
}
add_action('init', 'civi_optimize_queries', 1);

/**
 * Disable jQuery Migrate warnings in production
 * These warnings come from third-party libraries (select2, slick, waypoints)
 * and don't affect functionality
 */
function civi_disable_jquery_migrate_warnings()
{
	if (!is_admin()) {
		wp_add_inline_script('jquery-migrate', 'jQuery.migrateMute = true; jQuery.migrateTrace = false;', 'before');
	}
}
add_action('wp_enqueue_scripts', 'civi_disable_jquery_migrate_warnings', 100);

/**
 * Suppress miniorange textdomain errors immediately
 */
function civi_suppress_miniorange_errors()
{
	add_filter('doing_it_wrong_trigger_error', function ($trigger, $function_name, $message) {
		if (
			$function_name === '_load_textdomain_just_in_time' &&
			strpos($message, 'miniorange-login-openid') !== false
		) {
			return false;
		}
		return $trigger;
	}, 10, 3);
}
add_action('muplugins_loaded', 'civi_suppress_miniorange_errors', 1);
add_action('plugins_loaded', 'civi_suppress_miniorange_errors', 1);
add_action('init', 'civi_suppress_miniorange_errors', 1);

/**
 * Handle textdomain loading errors gracefully
 */
function civi_handle_textdomain_errors()
{
	add_filter('doing_it_wrong_trigger_error', function ($trigger, $function_name, $message) {
		if (
			$function_name === '_load_textdomain_just_in_time' &&
			strpos($message, 'miniorange-login-openid') !== false
		) {
			return false;
		}
		return $trigger;
	}, 10, 3);
}
add_action('init', 'civi_handle_textdomain_errors', 1);

/**
 * Fix miniorange plugin textdomain loading timing
 */
function civi_fix_miniorange_textdomain()
{
	if (function_exists('load_plugin_textdomain')) {
		$plugin_path = WP_PLUGIN_DIR . '/miniorange-login-openid';
		if (file_exists($plugin_path)) {
			load_plugin_textdomain('miniorange-login-openid', false, 'miniorange-login-openid/languages');
		}
	}
}
add_action('plugins_loaded', 'civi_fix_miniorange_textdomain', 5);

/**
 * Override WordPress error reporting for textdomain issues
 */
function civi_override_textdomain_error_reporting()
{
	$original_error_reporting = error_reporting();

	add_action('wp_loaded', function () use ($original_error_reporting) {
		error_reporting($original_error_reporting);
	}, 1);

	error_reporting(E_ALL & ~E_NOTICE & ~E_USER_NOTICE);
}
add_action('muplugins_loaded', 'civi_override_textdomain_error_reporting', 1);

/**
 * Init the theme
 *
 */
new Civi_Init();
