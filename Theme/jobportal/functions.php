<?php

/**
 * Direct error suppression for textdomain issues
 */
// Suppress the specific error immediately when this file loads
if (!function_exists('jobportal_direct_error_suppression')) {
	function jobportal_direct_error_suppression()
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
	jobportal_direct_error_suppression();
}

/**
 * Suppress textdomain errors immediately
 */
function jobportal_suppress_textdomain_notices()
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
jobportal_suppress_textdomain_notices();

/**
 * Start output buffering to catch and suppress textdomain errors
 */
function jobportal_start_error_suppression()
{
	ob_start(function ($buffer) {
		$buffer = preg_replace('/Function _load_textdomain_just_in_time was called incorrectly.*?miniorange-login-openid.*?<\/p>/s', '', $buffer);
		return $buffer;
	});
}
add_action('muplugins_loaded', 'jobportal_start_error_suppression', 1);

/**
 * Define constants
 */
$jobportal_theme = wp_get_theme();

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

if (!empty($jobportal_theme['Template'])) {
	$jobportal_theme = wp_get_theme($jobportal_theme['Template']);
}

if (!defined('JOBPORTAL_THEME_NAME')) {
	define('JOBPORTAL_THEME_NAME', $jobportal_theme['Name']);
}

if (!defined('JOBPORTAL_THEME_SLUG')) {
	define('JOBPORTAL_THEME_SLUG', $jobportal_theme['Template']);
}

if (!defined('JOBPORTAL_THEME_VER')) {
	define('JOBPORTAL_THEME_VER', $jobportal_theme['Version']);
}

if (!defined('JOBPORTAL_THEME_DIR')) {
	define('JOBPORTAL_THEME_DIR', trailingslashit(get_template_directory()));
}

if (!defined('JOBPORTAL_THEME_URI')) {
	define('JOBPORTAL_THEME_URI', get_template_directory_uri());
}

if (!defined('JOBPORTAL_THEME_PREFIX')) {
	define('JOBPORTAL_THEME_PREFIX', 'jobportal_');
}

if (!defined('JOBPORTAL_METABOX_PREFIX')) {
	define('JOBPORTAL_METABOX_PREFIX', 'jobportal-');
}

if (!defined('JOBPORTAL_CUSTOMIZER_DIR')) {
	define('JOBPORTAL_CUSTOMIZER_DIR', JOBPORTAL_THEME_DIR . '/customizer');
}

if (!defined('JOBPORTAL_IMAGES')) {
	define('JOBPORTAL_IMAGES', JOBPORTAL_THEME_URI . '/assets/images/');
}

define('JOBPORTAL_ELEMENTOR_DIR', get_template_directory() . DS . 'elementor');
define('JOBPORTAL_ELEMENTOR_URI', get_template_directory_uri() . '/elementor');
define('JOBPORTAL_ELEMENTOR_ASSETS', get_template_directory_uri() . '/elementor/assets');

/**
 * Load Theme Class.
 *
 */
foreach (glob(get_template_directory() . '/includes/*.php') as $theme_class) {
	require_once($theme_class);
}

require_once JOBPORTAL_ELEMENTOR_DIR . '/class-entry.php';

function jobportal_load_elementor_options()
{
	update_option('elementor_disable_typography_schemes', 'yes');
}

add_action('after_switch_theme', 'jobportal_load_elementor_options');

add_filter('wp_mail_smtp_core_wp_mail_function_incorrect_location_notice', '__return_false');

/**
 * Optimize database queries by priming caches and reducing duplicate queries
 */
function jobportal_optimize_queries()
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
add_action('init', 'jobportal_optimize_queries', 1);

/**
 * Disable jQuery Migrate warnings in production
 * These warnings come from third-party libraries (select2, slick, waypoints)
 * and don't affect functionality
 */
function jobportal_disable_jquery_migrate_warnings()
{
	if (!is_admin()) {
		wp_add_inline_script('jquery-migrate', 'jQuery.migrateMute = true; jQuery.migrateTrace = false;', 'before');
	}
}
add_action('wp_enqueue_scripts', 'jobportal_disable_jquery_migrate_warnings', 100);

/**
 * Suppress miniorange textdomain errors immediately
 */
function jobportal_suppress_miniorange_errors()
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
add_action('muplugins_loaded', 'jobportal_suppress_miniorange_errors', 1);
add_action('plugins_loaded', 'jobportal_suppress_miniorange_errors', 1);
add_action('init', 'jobportal_suppress_miniorange_errors', 1);

/**
 * Handle textdomain loading errors gracefully
 */
function jobportal_handle_textdomain_errors()
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
add_action('init', 'jobportal_handle_textdomain_errors', 1);

/**
 * Fix miniorange plugin textdomain loading timing
 */
function jobportal_fix_miniorange_textdomain()
{
	if (function_exists('load_plugin_textdomain')) {
		$plugin_path = WP_PLUGIN_DIR . '/miniorange-login-openid';
		if (file_exists($plugin_path)) {
			load_plugin_textdomain('miniorange-login-openid', false, 'miniorange-login-openid/languages');
		}
	}
}
add_action('plugins_loaded', 'jobportal_fix_miniorange_textdomain', 5);

/**
 * Override WordPress error reporting for textdomain issues
 */
function jobportal_override_textdomain_error_reporting()
{
	$original_error_reporting = error_reporting();

	add_action('wp_loaded', function () use ($original_error_reporting) {
		error_reporting($original_error_reporting);
	}, 1);

	error_reporting(E_ALL & ~E_NOTICE & ~E_USER_NOTICE);
}
add_action('muplugins_loaded', 'jobportal_override_textdomain_error_reporting', 1);

/**
 * Init the theme
 *
 */
new JobPortal_Init();
