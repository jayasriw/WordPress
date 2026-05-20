<?php

/**
 * Fired during plugin deactivation
 *
 */
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('JobPortal_Deactivator')) {
	require_once JOBPORTAL_PLUGIN_DIR . 'includes/admin/class-jobportal-schedule.php';
	/**
	 * Fired during plugin deactivation
	 * Class JobPortal_Deactivator
	 */
	class JobPortal_Deactivator
	{
		/**
		 * Run when plugin deactivated
		 */
		public static function deactivate()
		{
			JobPortal_Schedule::clear_scheduled_hook();
		}
	}
}
