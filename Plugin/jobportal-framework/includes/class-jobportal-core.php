<?php

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('JobPortal_Core')) {
	/**
	 *  The core plugin class
	 *  Class JobPortal_Core
	 */
	class JobPortal_Core
	{

		/**
		 * Instance variable for singleton pattern
		 */
		private static $instance = null;

		/**
		 * Return class instance
		 */
		public static function instance()
		{
			if (null == self::$instance) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Define the core functionality of the plugin
		 */
		public function __construct()
		{
			$this->include_library();
			$this->template_hooks();
			$this->admin_hooks();

			add_filter('woocommerce_show_admin_notice', array($this, 'hide_woocommerce_admin_notice'), 10, 2);
		}

		function hide_woocommerce_admin_notice($show, $notice)
		{
			if ('template_files' === $notice) {
				return false;
			}

			return $show;
		}

		/**
		 * Load the required dependencies for this plugin
		 */
		private function include_library()
		{
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/jobportal-helper.php';
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/jobportal-util.php';

			require_once JOBPORTAL_PLUGIN_DIR . 'includes/class-jobportal-capability.php';
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/class-jobportal-template-loader.php';
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/class-jobportal-shortcodes.php';
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/class-jobportal-ajax.php';
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/class-jobportal-user.php';
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/class-jobportal-breadcrumb.php';
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/class-jobportal-header.php';
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/class-jobportal-footer.php';
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/class-jobportal-search.php';

			// Mega Menu
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/mega-menu/class-mega-menu.php';
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/mega-menu/class-walker-nav-menu.php';

			// Google Review
			include_once JOBPORTAL_PLUGIN_DIR . 'includes/google-review/class-google-review.php';

			// Update
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/admin/class-jobportal-updater.php';

			// Admin
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/admin/class-jobportal-plugins.php';
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/admin/class-jobportal-admin-setup.php';
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/admin/class-jobportal-admin.php';
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/admin/class-jobportal-admin-package.php';
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/admin/class-jobportal-admin-user-package.php';
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/admin/class-jobportal-admin-invoice.php';
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/admin/class-jobportal-metaboxes.php';
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/admin/class-jobportal-profile.php';
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/admin/class-jobportal-schedule.php';
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/admin/class-jobportal-rest-api.php';
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/admin/class-jobportal-admin-applicants.php';
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/admin/class-jobportal-admin-meetings.php';
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/admin/class-jobportal-admin-messages.php';
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/admin/class-jobportal-admin-notification.php';
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/admin/class-jobportal-admin-jobs.php';
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/admin/class-jobportal-admin-job-alerts.php';
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/admin/class-jobportal-admin-company.php';
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/admin/class-jobportal-admin-candidate.php';
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/admin/class-jobportal-admin-candidate-package.php';
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/admin/class-jobportal-admin-candidate-order.php';
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/admin/class-jobportal-admin-service.php';
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/admin/class-jobportal-admin-service-order.php';
			require_once JOBPORTAL_PLUGIN_DIR . 'includes/admin/class-jobportal-admin-service-withdraw.php';

			// Partials
			include_once JOBPORTAL_PLUGIN_DIR . 'includes/partials/package/class-jobportal-package.php';
			include_once JOBPORTAL_PLUGIN_DIR . 'includes/partials/payment/class-jobportal-payment.php';
			include_once JOBPORTAL_PLUGIN_DIR . 'includes/partials/payment/class-jobportal-trans-log.php';
			include_once JOBPORTAL_PLUGIN_DIR . 'includes/partials/invoice/class-jobportal-invoice.php';
			include_once JOBPORTAL_PLUGIN_DIR . 'includes/partials/applicants/class-jobportal-applicants.php';
			include_once JOBPORTAL_PLUGIN_DIR . 'includes/partials/meetings/class-jobportal-meetings.php';
			include_once JOBPORTAL_PLUGIN_DIR . 'includes/partials/messages/class-jobportal-messages.php';
			include_once JOBPORTAL_PLUGIN_DIR . 'includes/partials/notification/class-jobportal-notification.php';
			include_once JOBPORTAL_PLUGIN_DIR . 'includes/partials/jobs/class-jobportal-jobs.php';
			include_once JOBPORTAL_PLUGIN_DIR . 'includes/partials/company/class-jobportal-company.php';
			include_once JOBPORTAL_PLUGIN_DIR . 'includes/partials/candidate/class-jobportal-candidate.php';
			include_once JOBPORTAL_PLUGIN_DIR . 'includes/partials/candidate/class-jobportal-candidate-order.php';
			include_once JOBPORTAL_PLUGIN_DIR . 'includes/partials/candidate/class-jobportal-candidate-package.php';
			include_once JOBPORTAL_PLUGIN_DIR . 'includes/partials/candidate/class-jobportal-candidate-payment.php';
			include_once JOBPORTAL_PLUGIN_DIR . 'includes/partials/candidate/class-jobportal-candidate-trans-log.php';
			include_once JOBPORTAL_PLUGIN_DIR . 'includes/partials/service/class-jobportal-service.php';
			include_once JOBPORTAL_PLUGIN_DIR . 'includes/partials/service/class-jobportal-service-order.php';
			include_once JOBPORTAL_PLUGIN_DIR . 'includes/partials/service/class-jobportal-service-payment.php';
			include_once JOBPORTAL_PLUGIN_DIR . 'includes/partials/service/class-jobportal-service-withdraw.php';

			// Init Search Optimization
			if (class_exists('JobPortal_Search')) {
				JobPortal_Search::instance();
			}
		}

		/**
		 * Register all of the hooks related to the admin area functionality
		 */
		private function admin_hooks()
		{
			/**
			 * Hook JobPortal_Admin_Setup
			 */
			if (is_admin()) {
				$setup_page = new JobPortal_Admin_Setup();
				add_action('admin_menu', array($setup_page, 'admin_menu'), 12);
				add_action('admin_menu', array($setup_page, 'reorder_admin_menu'), 999);
			}

			/**
			 * Hook JobPortal_Admin
			 */
			$jobportal_admin = new JobPortal_Admin();
			add_filter('glf_meta_box_config', array($jobportal_admin, 'register_meta_boxes'));
			add_filter('glf_register_post_type', array($jobportal_admin, 'register_post_type'));
			add_filter('glf_register_taxonomy', array($jobportal_admin, 'register_taxonomy'));
			add_filter('glf_register_term_meta', array($jobportal_admin, 'register_term_meta'));

			add_filter('glf_option_config', array($jobportal_admin, 'register_options_config'));
			add_action('init', array($jobportal_admin, 'register_post_status'));
			add_action('after_setup_theme', array($jobportal_admin, 'remove_admin_bar'));

			/**
			 * Hook JobPortal_Admin_Jobs
			 */
			$jobportal_admin_jobs = new JobPortal_Admin_Jobs();
			add_filter('jobportal_jobs_slug', array($jobportal_admin_jobs, 'modify_jobs_slug'));
			add_filter('jobportal_jobs_has_archive', array($jobportal_admin_jobs, 'modify_jobs_has_archive'));
			add_filter('jobportal_jobs_type_slug', array($jobportal_admin_jobs, 'modify_jobs_type_slug'));
			add_filter('jobportal_jobs_tags_slug', array($jobportal_admin_jobs, 'modify_jobs_tags_slug'));
			add_filter('jobportal_jobs_categories_slug', array($jobportal_admin_jobs, 'modify_jobs_categories_slug'));
			add_filter('jobportal_jobs_skills_slug', array($jobportal_admin_jobs, 'modify_jobs_skills_slug'));
			add_filter('jobportal_jobs_location_slug', array($jobportal_admin_jobs, 'modify_jobs_location_slug'));
			add_filter('jobportal_jobs_career_slug', array($jobportal_admin_jobs, 'modify_jobs_career_slug'));
			add_filter('jobportal_jobs_experience_slug', array($jobportal_admin_jobs, 'modify_jobs_experience_slug'));
			add_filter('jobportal_jobs_qualification_slug', array($jobportal_admin_jobs, 'modify_jobs_qualification_slug'));
			add_filter('jobportal_jobs_gender_slug', array($jobportal_admin_jobs, 'modify_jobs_gender_slug'));
			add_action('restrict_manage_posts', array($jobportal_admin_jobs, 'filter_restrict_manage_jobs'));
			add_action('admin_enqueue_scripts', array($jobportal_admin_jobs, 'enqueue_admin_scripts'));

			add_filter('parse_query', array($jobportal_admin_jobs, 'jobs_filter'));
			add_filter('posts_clauses', array($jobportal_admin_jobs, 'posts_clauses'), 10, 1);
			add_action('admin_init', array($jobportal_admin_jobs, 'approve_jobs'));
			add_action('admin_init', array($jobportal_admin_jobs, 'expire_jobs'));
			add_action('admin_init', array($jobportal_admin_jobs, 'hidden_jobs'));
			add_action('admin_init', array($jobportal_admin_jobs, 'show_jobs'));
			add_action('admin_init', array($jobportal_admin_jobs, 'add_badge_menu'));

			add_action('wp_ajax_jobportal_action_claim_listing', array($jobportal_admin_jobs, 'action_claim_listing'));
			add_action('wp_ajax_nopriv_jobportal_action_claim_listing', array($jobportal_admin_jobs, 'action_claim_listing'));

			add_action('wp_ajax_jobportal_ajax_approve_job', array($jobportal_admin_jobs, 'ajax_approve_jobs'));
			add_action('wp_ajax_jobportal_ajax_expire_job', array($jobportal_admin_jobs, 'ajax_expire_jobs'));
			add_action('wp_ajax_jobportal_ajax_hide_job', array($jobportal_admin_jobs, 'ajax_hide_jobs'));
			add_action('wp_ajax_jobportal_ajax_show_job', array($jobportal_admin_jobs, 'ajax_show_jobs'));

			add_action('wp_ajax_auto_description_generate', array($jobportal_admin_jobs, 'auto_description_generate'));
			add_action('wp_ajax_nopriv_auto_description_generate', array($jobportal_admin_jobs, 'auto_description_generate'));

			$jobportal_admin_job_alerts = new JobPortal_Admin_Job_Alerts();

			/**
			 * Hook JobPortal_Package_Admin
			 */
			$jobportal_admin_package = new JobPortal_Admin_Package();
			add_filter('jobportal_package_slug', array($jobportal_admin_package, 'modify_package_slug'));

			/**
			 * Hook JobPortal_Admin_candidate_package
			 */
			$jobportal_admin_candidate_package = new JobPortal_Admin_candidate_package();
			add_filter('jobportal_candidate_package_slug', array($jobportal_admin_candidate_package, 'modify_candidate_package_slug'));

			// User Packages Post Type
			$jobportal_user_package_admin = new JobPortal_User_Package_Admin();
			add_filter('jobportal_user_package_slug', array($jobportal_user_package_admin, 'modify_user_package_slug'));
			add_action('restrict_manage_posts', array($jobportal_user_package_admin, 'filter_restrict_manage_user_package'));
			add_action('before_delete_post', array($jobportal_user_package_admin, 'action_delete_post'));
			add_filter('parse_query', array($jobportal_user_package_admin, 'user_package_filter'));

			/**
			 * Hook JobPortal_Invoice_Admin
			 */
			$jobportal_admin_invoice = new JobPortal_Admin_Invoice();
			add_action('jobportal_invoice_slug', array($jobportal_admin_invoice, 'modify_invoice_slug'));
			add_action('restrict_manage_posts', array($jobportal_admin_invoice, 'filter_restrict_manage_invoice'));
			add_action('parse_query', array($jobportal_admin_invoice, 'invoice_filter'));
			add_action('admin_init', array($jobportal_admin_invoice, 'invoice_active'));
			add_action('admin_init', array($jobportal_admin_invoice, 'invoice_pending'));

			/**
			 * Hook JobPortal_Admin_Applicants
			 */
			$jobportal_admin_applicants = new JobPortal_Admin_Applicants();
			add_action('jobportal_applicants_slug', array($jobportal_admin_applicants, 'modify_applicants_slug'));
			add_action('restrict_manage_posts', array($jobportal_admin_applicants, 'filter_restrict_manage_applicants'));
			add_action('parse_query', array($jobportal_admin_applicants, 'applicants_filter'));

			/**
			 * Hook JobPortal_Meetings_Admin
			 */
			$jobportal_admin_meetings = new JobPortal_Admin_Meetings();
			add_action('jobportal_meetings_slug', array($jobportal_admin_meetings, 'modify_meetings_slug'));
			add_action('restrict_manage_posts', array($jobportal_admin_meetings, 'filter_restrict_manage_meetings'));
			add_action('parse_query', array($jobportal_admin_meetings, 'meetings_filter'));

			/**
			 * Hook JobPortal_Admin_Messages
			 */
			$jobportal_admin_messages = new JobPortal_Admin_Messages();
			add_action('parse_query', array($jobportal_admin_messages, 'messages_filter'));

			/**
			 * Hook JobPortal_Admin_Notification
			 */
			$jobportal_admin_notification = new JobPortal_Admin_Notification();
			add_action('parse_query', array($jobportal_admin_notification, 'notification_filter'));

			/**
			 * Hook JobPortal_Admin_candidate_order
			 */
			$jobportal_admin_candidate_order = new JobPortal_Admin_candidate_order();
			add_action('parse_query', array($jobportal_admin_candidate_order, 'candidate_order_filter'));
			add_action('restrict_manage_posts', array($jobportal_admin_candidate_order, 'filter_restrict_manage_candidate_order'));
			add_action('admin_init', array($jobportal_admin_candidate_order, 'candidate_order_active'));
			add_action('admin_init', array($jobportal_admin_candidate_order, 'candidate_order_pending'));

			/**
			 * Hook JobPortal_Admin_service_order
			 */
			$jobportal_admin_service_order = new JobPortal_Admin_service_order();
			add_action('parse_query', array($jobportal_admin_service_order, 'service_order_filter'));
			add_action('restrict_manage_posts', array($jobportal_admin_service_order, 'filter_restrict_manage_service_order'));
			add_action('admin_init', array($jobportal_admin_service_order, 'service_order_inprogress'));
			add_action('admin_init', array($jobportal_admin_service_order, 'service_order_pending'));

			/**
			 * Hook JobPortal_Admin_Service_Withdraw
			 */
			$jobportal_admin_service_withdraw = new JobPortal_Admin_Service_Withdraw();
			add_action('parse_query', array($jobportal_admin_service_withdraw, 'service_withdraw_filter'));
			add_action('restrict_manage_posts', array($jobportal_admin_service_withdraw, 'filter_restrict_manage_service_withdraw'));
			add_action('admin_init', array($jobportal_admin_service_withdraw, 'service_withdraw_active'));
			add_action('admin_init', array($jobportal_admin_service_withdraw, 'service_withdraw_pending'));
			add_action('admin_init', array($jobportal_admin_service_withdraw, 'service_withdraw_canceled'));

			/**
			 * Hook JobPortal_Commany_Admin
			 */
			$jobportal_admin_company = new JobPortal_Admin_Company();
			add_filter('jobportal_company_slug', array($jobportal_admin_company, 'modify_company_url_slug'));
			add_filter('jobportal_company_has_archive', array($jobportal_admin_company, 'modify_company_has_archive'));
			add_filter('jobportal_company_categories_slug', array($jobportal_admin_company, 'modify_company_categories_url_slug'));
			add_filter('jobportal_company_location_slug', array($jobportal_admin_company, 'modify_company_location_url_slug'));
			add_filter('jobportal_company_size_slug', array($jobportal_admin_company, 'modify_company_size_url_slug'));
			add_action('restrict_manage_posts', array($jobportal_admin_company, 'filter_restrict_manage_company'));
			add_action('parse_query', array($jobportal_admin_company, 'company_filter'));
			add_action('admin_init', array($jobportal_admin_company, 'approve_company'));
			add_action('admin_init', array($jobportal_admin_company, 'add_badge_menu'));

			/**
			 * Hook JobPortal_Admin_Candidate
			 */
			$jobportal_admin_candidate = new JobPortal_Admin_Candidate();
			add_filter('jobportal_candidate_slug', array($jobportal_admin_candidate, 'modify_candidate_slug'));
			add_filter('jobportal_candidate_has_archive', array($jobportal_admin_candidate, 'modify_candidate_has_archive'));
			add_filter('jobportal_candidate_categories_slug', array($jobportal_admin_candidate, 'modify_candidate_categories_url_slug'));
			add_filter('jobportal_candidate_ages_slug', array($jobportal_admin_candidate, 'modify_candidate_ages_url_slug'));
			add_filter('jobportal_candidate_languages_slug', array($jobportal_admin_candidate, 'modify_candidate_languages_url_slug'));
			add_filter('jobportal_candidate_qualification_slug', array($jobportal_admin_candidate, 'modify_candidate_qualification_url_slug'));
			add_filter('jobportal_candidate_yoe_slug', array($jobportal_admin_candidate, 'modify_candidate_yoe_url_slug'));
			add_filter('jobportal_candidate_salary_types_slug', array($jobportal_admin_candidate, 'modify_candidate_salary_types_url_slug'));
			add_filter('jobportal_candidate_education_levels_slug', array($jobportal_admin_candidate, 'modify_candidate_education_levels_url_slug'));
			add_filter('jobportal_candidate_skills_slug', array($jobportal_admin_candidate, 'modify_candidate_skills_url_slug'));
			add_filter('jobportal_candidate_gender_slug', array($jobportal_admin_candidate, 'modify_candidate_gender_url_slug'));
			add_filter('jobportal_candidate_locations_slug', array($jobportal_admin_candidate, 'modify_candidate_locations_url_slug'));
			add_action('restrict_manage_posts', array($jobportal_admin_candidate, 'filter_restrict_manage_candidate'));
			add_action('admin_post_export_candidates_csv', array($jobportal_admin_candidate, 'export_candidates_csv'));
			add_action('admin_enqueue_scripts', array($jobportal_admin_candidate, 'enqueue_candidate_admin_scripts'));
			add_action('admin_init', array($jobportal_admin_candidate, 'show_candidates'));
			add_action('admin_init', array($jobportal_admin_candidate, 'approve_candidate'));
			add_action('admin_init', array($jobportal_admin_candidate, 'add_badge_menu'));

			/**
			 * Hook JobPortal_Admin_Candidate
			 */
			$jobportal_admin_service = new JobPortal_Admin_Service();
			add_filter('jobportal_service_slug', array($jobportal_admin_service, 'modify_service_slug'));
			add_filter('jobportal_service_has_archive', array($jobportal_admin_service, 'modify_service_has_archive'));
			add_filter('jobportal_service_categories_slug', array($jobportal_admin_service, 'modify_service_categories_url_slug'));
			add_filter('jobportal_service_skills_slug', array($jobportal_admin_service, 'modify_service_skills_url_slug'));
			add_filter('jobportal_service_location_slug', array($jobportal_admin_service, 'modify_service_location_url_slug'));
			add_filter('jobportal_service_language_slug', array($jobportal_admin_service, 'modify_service_language_url_slug'));
			add_action('restrict_manage_posts', array($jobportal_admin_service, 'filter_restrict_manage_service'));
			add_action('parse_query', array($jobportal_admin_service, 'service_filter'));
			add_action('admin_init', array($jobportal_admin_service, 'approve_service'));

			/**
			 * Hook JobPortal_Rest_API
			 */
			$jobportal_rest_api = new JobPortal_Rest_API();
			add_action('rest_api_init', array($jobportal_rest_api, 'register_fields_api'));

			$profile = new JobPortal_Profile();
			add_filter('show_user_profile', array($profile, 'custom_user_profile_fields'));
			add_filter('edit_user_profile', array($profile, 'custom_user_profile_fields'));
			add_action('edit_user_profile_update', array($profile, 'update_custom_user_profile_fields'));
			add_action('personal_options_update', array($profile, 'update_custom_user_profile_fields'));
			add_action('admin_head', array($profile, 'my_profile_upload_js'));

			/**
			 * Hook JobPortal_Plugins
			 */
			$jobportal_plugins = new JobPortal_Plugins();
			add_action('wp_ajax_process_plugin_actions', array($jobportal_plugins, 'process_plugin_actions'));
			add_action('wp_ajax_nopriv_process_plugin_actions', array($jobportal_plugins, 'process_plugin_actions'));

			/**
			 * Hook JobPortal_Metaboxes
			 */
			$jobportal_metaboxes = new JobPortal_Metaboxes();
			add_action('load-post.php', array($jobportal_metaboxes, 'meta_boxes_setup'));
			add_action('load-post-new.php', array($jobportal_metaboxes, 'meta_boxes_setup'));

			/**
			 * Hook JobPortal Schedule
			 */
			$jobportal_schedule = new JobPortal_Schedule();
			register_deactivation_hook(__FILE__, array($jobportal_schedule, 'jobportal_per_listing_check_expire'));
			add_action('init', array($jobportal_schedule, 'scheduled_hook'));
			add_action('jobportal_per_listing_check_expire', array($jobportal_schedule, 'per_listing_check_expire'));

			if (is_admin()) {
				global $pagenow;

				// candidates custom columns
				if ($pagenow == 'edit.php' && isset($_GET['post_type']) && esc_attr($_GET['post_type']) == 'candidate') {
					add_filter('manage_edit-candidate_columns', array($jobportal_admin_candidate, 'register_custom_column_titles'));
					add_action('manage_posts_custom_column', array($jobportal_admin_candidate, 'display_custom_column'));
					add_filter('manage_edit-candidate_sortable_columns', array($jobportal_admin_candidate, 'sortable_columns'));
					add_filter('request', array($jobportal_admin_candidate, 'column_orderby'));
					add_filter('posts_clauses', array($jobportal_admin_candidate, 'posts_clauses'), 10, 1);
					add_filter('post_row_actions', array($jobportal_admin_candidate, 'modify_list_row_actions'), 10, 2);
				}

				// service custom columns
				if ($pagenow == 'edit.php' && isset($_GET['post_type']) && esc_attr($_GET['post_type']) == 'service') {
					add_filter('manage_edit-service_columns', array($jobportal_admin_service, 'register_custom_column_titles'));
					add_action('manage_posts_custom_column', array($jobportal_admin_service, 'display_custom_column'));
					add_filter('manage_edit-service_sortable_columns', array($jobportal_admin_service, 'sortable_columns'));
					add_filter('post_row_actions', array($jobportal_admin_service, 'modify_list_row_actions'), 10, 2);
				}

				// jobs custom columns
				if ($pagenow == 'edit.php' && isset($_GET['post_type']) && esc_attr($_GET['post_type']) == 'jobs') {
					add_filter('manage_edit-jobs_columns', array($jobportal_admin_jobs, 'register_custom_column_titles'));
					add_action('manage_posts_custom_column', array($jobportal_admin_jobs, 'display_custom_column'));
					add_filter('manage_edit-jobs_sortable_columns', array($jobportal_admin_jobs, 'sortable_columns'));
					add_filter('request', array($jobportal_admin_jobs, 'column_orderby'));
					add_filter('post_row_actions', array($jobportal_admin_jobs, 'modify_list_row_actions'), 10, 2);
				}

				// job alerts custom columns
				if ($pagenow == 'edit.php' && isset($_GET['post_type']) && esc_attr($_GET['post_type']) == 'job_alerts') {
					add_filter('manage_edit-job_alerts_columns', array($jobportal_admin_job_alerts, 'register_custom_column_titles'));
					add_action('manage_posts_custom_column', array($jobportal_admin_job_alerts, 'display_custom_column'));
					add_filter('manage_edit-job_alerts_sortable_columns', array($jobportal_admin_job_alerts, 'sortable_columns'));
				}

				// package custom columns
				if ($pagenow == 'edit.php' && isset($_GET['post_type']) && esc_attr($_GET['post_type']) == 'package') {
					add_filter('manage_edit-package_columns', array($jobportal_admin_package, 'register_custom_column_titles'));
					add_action('manage_posts_custom_column', array($jobportal_admin_package, 'display_custom_column'));
					add_filter('post_row_actions', array($jobportal_admin_package, 'modify_list_row_actions'), 10, 2);
				}

				// user package custom columns
				if ($pagenow == 'edit.php' && isset($_GET['post_type']) && esc_attr($_GET['post_type']) == 'user_package') {
					add_filter('manage_edit-user_package_columns', array($jobportal_user_package_admin, 'register_custom_column_titles'));
					add_action('manage_posts_custom_column', array($jobportal_user_package_admin, 'display_custom_column'));
					add_action('before_delete_post', array($jobportal_user_package_admin, 'action_delete_post'));
					add_filter('post_row_actions', array($jobportal_user_package_admin, 'modify_list_row_actions'), 10, 2);
				}

				// Invoice custom columns
				if ($pagenow == 'edit.php' && isset($_GET['post_type']) && esc_attr($_GET['post_type']) == 'invoice') {
					add_filter('manage_edit-invoice_columns', array($jobportal_admin_invoice, 'register_custom_column_titles'));
					add_action('manage_posts_custom_column', array($jobportal_admin_invoice, 'display_custom_column'));
					add_filter('manage_edit-invoice_sortable_columns', array($jobportal_admin_invoice, 'sortable_columns'));
					add_filter('request', array($jobportal_admin_invoice, 'column_orderby'));
					add_filter('post_row_actions', array($jobportal_admin_invoice, 'modify_list_row_actions'), 10, 2);
				}

				// Company custom columns
				if ($pagenow == 'edit.php' && isset($_GET['post_type']) && esc_attr($_GET['post_type']) == 'company') {
					add_filter('manage_edit-company_columns', array($jobportal_admin_company, 'register_custom_column_titles'));
					add_action('manage_posts_custom_column', array($jobportal_admin_company, 'display_custom_column'));
					add_filter('manage_edit-company_sortable_columns', array($jobportal_admin_company, 'sortable_columns'));
					add_filter('post_row_actions', array($jobportal_admin_company, 'modify_list_row_actions'), 10, 2);
				}

				// Applicants custom columns
				if ($pagenow == 'edit.php' && isset($_GET['post_type']) && esc_attr($_GET['post_type']) == 'applicants') {
					add_filter('manage_edit-applicants_columns', array($jobportal_admin_applicants, 'register_custom_column_titles'));
					add_action('manage_posts_custom_column', array($jobportal_admin_applicants, 'display_custom_column'));
					add_filter('manage_edit-applicants_sortable_columns', array($jobportal_admin_applicants, 'sortable_columns'));
					add_filter('request', array($jobportal_admin_applicants, 'column_orderby'));
					add_filter('post_row_actions', array($jobportal_admin_applicants, 'modify_list_row_actions'), 10, 2);
				}

				// Meetings custom columns
				if ($pagenow == 'edit.php' && isset($_GET['post_type']) && esc_attr($_GET['post_type']) == 'meetings') {
					add_filter('manage_edit-meetings_columns', array($jobportal_admin_meetings, 'register_custom_column_titles'));
					add_action('manage_posts_custom_column', array($jobportal_admin_meetings, 'display_custom_column'));
					add_filter('manage_edit-meetings_sortable_columns', array($jobportal_admin_meetings, 'sortable_columns'));
					add_filter('request', array($jobportal_admin_meetings, 'column_orderby'));
					add_filter('post_row_actions', array($jobportal_admin_meetings, 'modify_list_row_actions'), 10, 2);
				}

				// Messages custom columns
				if ($pagenow == 'edit.php' && isset($_GET['post_type']) && esc_attr($_GET['post_type']) == 'messages') {
					add_filter('manage_edit-messages_columns', array($jobportal_admin_messages, 'register_custom_column_titles'));
					add_action('manage_posts_custom_column', array($jobportal_admin_messages, 'display_custom_column'));
					add_filter('manage_edit-messages_sortable_columns', array($jobportal_admin_messages, 'sortable_columns'));
					add_filter('request', array($jobportal_admin_messages, 'column_orderby'));
					add_filter('post_row_actions', array($jobportal_admin_messages, 'modify_list_row_actions'), 10, 2);
				}

				// Notification custom columns
				if ($pagenow == 'edit.php' && isset($_GET['post_type']) && esc_attr($_GET['post_type']) == 'notification') {
					add_filter('manage_edit-notification_columns', array($jobportal_admin_notification, 'register_custom_column_titles'));
					add_action('manage_posts_custom_column', array($jobportal_admin_notification, 'display_custom_column'));
					add_filter('manage_edit-notification_sortable_columns', array($jobportal_admin_notification, 'sortable_columns'));
					add_filter('request', array($jobportal_admin_notification, 'column_orderby'));
					add_filter('post_row_actions', array($jobportal_admin_notification, 'modify_list_row_actions'), 10, 2);
				}

				// Candidate order custom columns
				if ($pagenow == 'edit.php' && isset($_GET['post_type']) && esc_attr($_GET['post_type']) == 'candidate_order') {
					add_filter('manage_edit-candidate_order_columns', array($jobportal_admin_candidate_order, 'register_custom_column_titles'));
					add_action('manage_posts_custom_column', array($jobportal_admin_candidate_order, 'display_custom_column'));
					add_filter('manage_edit-candidate_order_sortable_columns', array($jobportal_admin_candidate_order, 'sortable_columns'));
					add_filter('request', array($jobportal_admin_candidate_order, 'column_orderby'));
					add_filter('post_row_actions', array($jobportal_admin_candidate_order, 'modify_list_row_actions'), 10, 2);
				}

				// Candidate package custom columns
				if ($pagenow == 'edit.php' && isset($_GET['post_type']) && esc_attr($_GET['post_type']) == 'candidate_package') {
					add_filter('manage_edit-candidate_package_columns', array($jobportal_admin_candidate_package, 'register_custom_column_titles'));
					add_action('manage_posts_custom_column', array($jobportal_admin_candidate_package, 'display_custom_column'));
					add_filter('post_row_actions', array($jobportal_admin_candidate_package, 'modify_list_row_actions'), 10, 2);
				}

				// Service order custom columns
				if ($pagenow == 'edit.php' && isset($_GET['post_type']) && esc_attr($_GET['post_type']) == 'service_order') {
					add_filter('manage_edit-service_order_columns', array($jobportal_admin_service_order, 'register_custom_column_titles'));
					add_action('manage_posts_custom_column', array($jobportal_admin_service_order, 'display_custom_column'));
					add_filter('manage_edit-service_order_sortable_columns', array($jobportal_admin_service_order, 'sortable_columns'));
					add_filter('request', array($jobportal_admin_service_order, 'column_orderby'));
					add_filter('post_row_actions', array($jobportal_admin_service_order, 'modify_list_row_actions'), 10, 2);
				}

				// Service withdraw custom columns
				if ($pagenow == 'edit.php' && isset($_GET['post_type']) && esc_attr($_GET['post_type']) == 'service_withdraw') {
					add_filter('manage_edit-service_withdraw_columns', array($jobportal_admin_service_withdraw, 'register_custom_column_titles'));
					add_action('manage_posts_custom_column', array($jobportal_admin_service_withdraw, 'display_custom_column'));
					add_filter('manage_edit-service_withdraw_sortable_columns', array($jobportal_admin_service_withdraw, 'sortable_columns'));
					add_filter('request', array($jobportal_admin_service_withdraw, 'column_orderby'));
					add_filter('post_row_actions', array($jobportal_admin_service_withdraw, 'modify_list_row_actions'), 10, 2);
				}
			}
		}

		/**
		 * Register all of the hooks related to the public-facing functionality
		 */
		private function template_hooks()
		{
			/**
			 * Hook JobPortal_Template_Loader
			 */
			$jobportal_template_loader = new JobPortal_Template_Loader();

			add_action('jobportal_apply_single_jobs', array($jobportal_template_loader, 'jobportal_form_apply_jobs'), 1);
			add_action('wp_footer', array($jobportal_template_loader, 'jobportal_form_setting_meetings'));
			add_action('wp_footer', array($jobportal_template_loader, 'jobportal_form_reschedule_meeting'));
			add_action('wp_footer', array($jobportal_template_loader, 'jobportal_form_setting_messages'));
			add_action('wp_footer', array($jobportal_template_loader, 'jobportal_form_invite_candidate'));
			add_action('wp_footer', array($jobportal_template_loader, 'jobportal_form_mess_applicants'));
			add_action('wp_footer', array($jobportal_template_loader, 'jobportal_form_setting_deactive'));
			add_action('wp_footer', array($jobportal_template_loader, 'jobportal_form_candidate_user_package'));
			add_action('wp_footer', array($jobportal_template_loader, 'jobportal_form_employer_user_package'));
			add_action('wp_footer', array($jobportal_template_loader, 'jobportal_form_service_order_refund'));
			add_action('wp_footer', array($jobportal_template_loader, 'jobportal_form_service_view_reason'));
			add_action('wp_footer', array($jobportal_template_loader, 'jobportal_form_service_withdraw'));
			add_action('wp_head', array($jobportal_template_loader, 'jobportal_add_google_job_schema'));
			add_action('wp_head', array($jobportal_template_loader, 'jobportal_add_company_schema'));
			add_action('wp_head', array($jobportal_template_loader, 'jobportal_add_candidate_schema'));
			add_action('wp_head', array($jobportal_template_loader, 'jobportal_add_service_schema'));
			add_action('send_meeting_notification', array($jobportal_template_loader, 'send_meeting_notification'));
			add_action('init', array($jobportal_template_loader, 'setup_meeting_notifications'));

			add_action('post_type_link', array($jobportal_template_loader, 'wpa_show_permalinks'), 1, 2);
			add_action('init', array($jobportal_template_loader, 'generated_rewrite_rules'));

			add_filter('template_include', array($jobportal_template_loader, 'template_loader'));
			add_action('admin_enqueue_scripts', array($jobportal_template_loader, 'admin_enqueue'));
			add_action('wp_enqueue_scripts', array($jobportal_template_loader, 'enqueue_styles'));
			add_action('wp_enqueue_scripts', array($jobportal_template_loader, 'enqueue_scripts'));
			add_action('wp_enqueue_scripts', array($this, 'enqueue_ajax_nonces'));

			/**
			 * Hook JobPortal_Ajax
			 */
			$jobportal_ajax = new JobPortal_Ajax();

			add_action('wp_ajax_preview_job', array($jobportal_ajax, 'preview_job'));
			add_action('wp_ajax_nopriv_preview_job', array($jobportal_ajax, 'preview_job'));

			add_action('wp_ajax_jobportal_jobs_archive_ajax', array($jobportal_ajax, 'jobportal_jobs_archive_ajax'));
			add_action('wp_ajax_nopriv_jobportal_jobs_archive_ajax', array($jobportal_ajax, 'jobportal_jobs_archive_ajax'));

			add_action('wp_ajax_jobportal_company_archive_ajax', array($jobportal_ajax, 'jobportal_company_archive_ajax'));
			add_action('wp_ajax_nopriv_jobportal_company_archive_ajax', array($jobportal_ajax, 'jobportal_company_archive_ajax'));

			add_action('wp_ajax_jobportal_service_archive_ajax', array($jobportal_ajax, 'jobportal_service_archive_ajax'));
			add_action('wp_ajax_nopriv_jobportal_service_archive_ajax', array($jobportal_ajax, 'jobportal_service_archive_ajax'));

			add_action('wp_ajax_jobportal_filter_jobs_dashboard', array($jobportal_ajax, 'jobportal_filter_jobs_dashboard'));

			add_action('wp_ajax_jobportal_filter_applicants_dashboard', array($jobportal_ajax, 'jobportal_filter_applicants_dashboard'));

			add_action('wp_ajax_jobportal_read_mess_ajax_load', array($jobportal_ajax, 'jobportal_read_mess_ajax_load'));
			add_action('wp_ajax_nopriv_jobportal_read_mess_ajax_load', array($jobportal_ajax, 'jobportal_read_mess_ajax_load'));

			add_action('wp_ajax_jobportal_realy_mess_ajax_load', array($jobportal_ajax, 'jobportal_realy_mess_ajax_load'));
			add_action('wp_ajax_nopriv_jobportal_realy_mess_ajax_load', array($jobportal_ajax, 'jobportal_realy_mess_ajax_load'));

			add_action('wp_ajax_jobportal_filter_my_wishlist', array($jobportal_ajax, 'jobportal_filter_my_wishlist'));
			add_action('wp_ajax_nopriv_jobportal_filter_my_wishlist', array($jobportal_ajax, 'jobportal_filter_my_wishlist'));

			add_action('wp_ajax_jobportal_filter_employer_wishlist', array($jobportal_ajax, 'jobportal_filter_employer_wishlist'));
			add_action('wp_ajax_nopriv_jobportal_filter_employer_wishlist', array($jobportal_ajax, 'jobportal_filter_employer_wishlist'));

			add_action('wp_ajax_jobportal_filter_my_follow', array($jobportal_ajax, 'jobportal_filter_my_follow'));
			add_action('wp_ajax_nopriv_jobportal_filter_my_follow', array($jobportal_ajax, 'jobportal_filter_my_follow'));

			add_action('wp_ajax_jobportal_filter_my_review', array($jobportal_ajax, 'jobportal_filter_my_review'));
			add_action('wp_ajax_nopriv_jobportal_filter_my_review', array($jobportal_ajax, 'jobportal_filter_my_review'));

			add_action('wp_ajax_jobportal_filter_my_invite', array($jobportal_ajax, 'jobportal_filter_my_invite'));
			add_action('wp_ajax_nopriv_jobportal_filter_my_invite', array($jobportal_ajax, 'jobportal_filter_my_invite'));

			add_action('wp_ajax_jobportal_filter_follow_candidate', array($jobportal_ajax, 'jobportal_filter_follow_candidate'));
			add_action('wp_ajax_nopriv_jobportal_filter_follow_candidate', array($jobportal_ajax, 'jobportal_filter_follow_candidate'));

			add_action('wp_ajax_jobportal_filter_invite_candidate', array($jobportal_ajax, 'jobportal_filter_invite_candidate'));
			add_action('wp_ajax_nopriv_jobportal_filter_invite_candidate', array($jobportal_ajax, 'jobportal_filter_invite_candidate'));

			add_action('wp_ajax_jobportal_filter_my_apply', array($jobportal_ajax, 'jobportal_filter_my_apply'));
			add_action('wp_ajax_nopriv_jobportal_filter_my_apply', array($jobportal_ajax, 'jobportal_filter_my_apply'));

			add_action('wp_ajax_jobportal_filter_company_dashboard', array($jobportal_ajax, 'jobportal_filter_company_dashboard'));

			add_action('wp_ajax_jobportal_company_related', array($jobportal_ajax, 'jobportal_company_related'));
			add_action('wp_ajax_nopriv_jobportal_company_related', array($jobportal_ajax, 'jobportal_company_related'));

			add_action('wp_ajax_jobportal_filter_candidates_dashboard', array($jobportal_ajax, 'jobportal_filter_candidates_dashboard'));

			add_action('wp_ajax_jobportal_update_profile_ajax', array($jobportal_ajax, 'jobportal_update_profile_ajax'));
			add_action('wp_ajax_nopriv_jobportal_update_profile_ajax', array($jobportal_ajax, 'jobportal_update_profile_ajax'));

			add_action('wp_ajax_jobportal_change_password_ajax', array($jobportal_ajax, 'jobportal_change_password_ajax'));

			//update payout
			add_action('wp_ajax_jobportal_update_payout_ajax', array($jobportal_ajax, 'jobportal_update_payout_ajax'));
			add_action('wp_ajax_nopriv_jobportal_update_payout_ajax', array($jobportal_ajax, 'jobportal_update_payout_ajax'));

			//chart jobs
			add_action('wp_ajax_jobportal_chart_ajax', array($jobportal_ajax, 'jobportal_chart_ajax'));
			add_action('wp_ajax_nopriv_jobportal_chart_ajax', array($jobportal_ajax, 'jobportal_chart_ajax'));

			//chart employer
			add_action('wp_ajax_jobportal_chart_employer_ajax', array($jobportal_ajax, 'jobportal_chart_employer_ajax'));
			add_action('wp_ajax_nopriv_jobportal_chart_employer_ajax', array($jobportal_ajax, 'jobportal_chart_employer_ajax'));

			//chart candidate
			add_action('wp_ajax_jobportal_chart_candidate_ajax', array($jobportal_ajax, 'jobportal_chart_candidate_ajax'));
			add_action('wp_ajax_nopriv_jobportal_chart_candidate_ajax', array($jobportal_ajax, 'jobportal_chart_candidate_ajax'));

			// Add to wishlist
			add_action('wp_ajax_jobportal_add_to_wishlist', array($jobportal_ajax, 'jobportal_add_to_wishlist'));
			add_action('wp_ajax_nopriv_jobportal_add_to_wishlist', array($jobportal_ajax, 'jobportal_add_to_wishlist'));

			// Add to service wishlist
			add_action('wp_ajax_jobportal_service_wishlist', array($jobportal_ajax, 'jobportal_service_wishlist'));
			add_action('wp_ajax_nopriv_jobportal_service_wishlist', array($jobportal_ajax, 'jobportal_service_wishlist'));

			// Add to service addons
			add_action('wp_ajax_jobportal_service_addons', array($jobportal_ajax, 'jobportal_service_addons'));
			add_action('wp_ajax_nopriv_jobportal_service_addons', array($jobportal_ajax, 'jobportal_service_addons'));

			// Add to follow company
			add_action('wp_ajax_jobportal_add_to_follow', array($jobportal_ajax, 'jobportal_add_to_follow'));
			add_action('wp_ajax_nopriv_jobportal_add_to_follow', array($jobportal_ajax, 'jobportal_add_to_follow'));

			// Add to follow candidate
			add_action('wp_ajax_jobportal_add_to_follow_candidate', array($jobportal_ajax, 'jobportal_add_to_follow_candidate'));
			add_action('wp_ajax_nopriv_jobportal_add_to_follow_candidate', array($jobportal_ajax, 'jobportal_add_to_follow_candidate'));

			// Add to download cv candidate
			add_action('wp_ajax_jobportal_candidate_download_cv', array($jobportal_ajax, 'jobportal_candidate_download_cv'));
			add_action('wp_ajax_nopriv_jobportal_candidate_download_cv', array($jobportal_ajax, 'jobportal_candidate_download_cv'));


			// Add to apply
			add_action('wp_ajax_jobs_add_to_apply', array($jobportal_ajax, 'jobs_add_to_apply'));
			add_action('wp_ajax_nopriv_jobs_add_to_apply', array($jobportal_ajax, 'jobs_add_to_apply'));

			// Add to invite
			add_action('wp_ajax_jobportal_add_to_invite', array($jobportal_ajax, 'jobportal_add_to_invite'));
			add_action('wp_ajax_nopriv_jobportal_add_to_invite', array($jobportal_ajax, 'jobportal_add_to_invite'));

			// Ajax search
			add_action('wp_ajax_jobportal_search_jobs_ajax', array($jobportal_ajax, 'jobportal_search_jobs_ajax'));
			add_action('wp_ajax_nopriv_jobportal_search_jobs_ajax', array($jobportal_ajax, 'jobportal_search_jobs_ajax'));

			// Ajax Search Candidate
			add_action('wp_ajax_jobportal_candidate_archive_ajax', array($jobportal_ajax, 'jobportal_candidate_archive_ajax'));
			add_action('wp_ajax_nopriv_jobportal_candidate_archive_ajax', array($jobportal_ajax, 'jobportal_candidate_archive_ajax'));

			// Ajax Thumbnail
			add_action('wp_ajax_jobportal_thumbnail_upload_ajax', array($jobportal_ajax, 'jobportal_thumbnail_upload_ajax'));
			add_action('wp_ajax_nopriv_jobportal_thumbnail_upload_ajax', array($jobportal_ajax, 'jobportal_thumbnail_upload_ajax'));

			add_action('wp_ajax_jobportal_thumbnail_remove_ajax', array($jobportal_ajax, 'jobportal_thumbnail_remove_ajax'));
			add_action('wp_ajax_nopriv_jobportal_thumbnail_remove_ajax', array($jobportal_ajax, 'jobportal_thumbnail_remove_ajax'));

			// Ajax Avatar
			add_action('wp_ajax_jobportal_avatar_upload_ajax', array($jobportal_ajax, 'jobportal_avatar_upload_ajax'));
			add_action('wp_ajax_nopriv_jobportal_avatar_upload_ajax', array($jobportal_ajax, 'jobportal_avatar_upload_ajax'));

			add_action('wp_ajax_jobportal_avatar_remove_ajax', array($jobportal_ajax, 'jobportal_avatar_remove_ajax'));
			add_action('wp_ajax_nopriv_jobportal_avatar_remove_ajax', array($jobportal_ajax, 'jobportal_avatar_remove_ajax'));

			// Verify ID
			add_action('wp_ajax_jobportal_verify_id_before_upload_ajax', array($jobportal_ajax, 'jobportal_verify_id_before_upload_ajax'));
			add_action('wp_ajax_nopriv_jobportal_verify_id_before_upload_ajax', array($jobportal_ajax, 'jobportal_verify_id_before_upload_ajax'));

			add_action('wp_ajax_jobportal_verify_id_before_remove_ajax', array($jobportal_ajax, 'jobportal_verify_id_before_remove_ajax'));
			add_action('wp_ajax_nopriv_jobportal_verify_id_before_remove_ajax', array($jobportal_ajax, 'jobportal_verify_id_before_remove_ajax'));

			add_action('wp_ajax_jobportal_verify_id_after_upload_ajax', array($jobportal_ajax, 'jobportal_verify_id_after_upload_ajax'));
			add_action('wp_ajax_nopriv_jobportal_verify_id_after_upload_ajax', array($jobportal_ajax, 'jobportal_verify_id_after_upload_ajax'));

			add_action('wp_ajax_jobportal_verify_id_after_remove_ajax', array($jobportal_ajax, 'jobportal_verify_id_after_remove_ajax'));
			add_action('wp_ajax_nopriv_jobportal_verify_id_after_remove_ajax', array($jobportal_ajax, 'jobportal_verify_id_after_remove_ajax'));

			add_action('wp_ajax_jobportal_verify_id_selfie_upload_ajax', array($jobportal_ajax, 'jobportal_verify_id_selfie_upload_ajax'));
			add_action('wp_ajax_nopriv_jobportal_verify_id_selfie_upload_ajax', array($jobportal_ajax, 'jobportal_verify_id_selfie_upload_ajax'));

			add_action('wp_ajax_jobportal_verify_id_selfie_remove_ajax', array($jobportal_ajax, 'jobportal_verify_id_selfie_remove_ajax'));
			add_action('wp_ajax_nopriv_jobportal_verify_id_selfie_remove_ajax', array($jobportal_ajax, 'jobportal_verify_id_selfie_remove_ajax'));

			// Ajax Custom Image
			add_action('wp_ajax_jobportal_custom_image_upload_ajax', array($jobportal_ajax, 'jobportal_custom_image_upload_ajax'));
			add_action('wp_ajax_nopriv_jobportal_custom_image_upload_ajax', array($jobportal_ajax, 'jobportal_custom_image_upload_ajax'));

			add_action('wp_ajax_jobportal_custom_image_remove_ajax', array($jobportal_ajax, 'jobportal_custom_image_remove_ajax'));
			add_action('wp_ajax_nopriv_jobportal_custom_image_remove_ajax', array($jobportal_ajax, 'jobportal_custom_image_remove_ajax'));

			// Ajax Mess Image
			add_action('wp_ajax_jobportal_mess_image_upload_ajax', array($jobportal_ajax, 'jobportal_mess_image_upload_ajax'));
			add_action('wp_ajax_nopriv_jobportal_mess_image_upload_ajax', array($jobportal_ajax, 'jobportal_mess_image_upload_ajax'));

			add_action('wp_ajax_jobportal_mess_image_remove_ajax', array($jobportal_ajax, 'jobportal_mess_image_remove_ajax'));
			add_action('wp_ajax_nopriv_jobportal_mess_image_remove_ajax', array($jobportal_ajax, 'jobportal_mess_image_remove_ajax'));

			// Ajax Gallery
			add_action('wp_ajax_jobportal_gallery_upload_ajax', array($jobportal_ajax, 'jobportal_gallery_upload_ajax'));
			add_action('wp_ajax_nopriv_jobportal_gallery_upload_ajax', array($jobportal_ajax, 'jobportal_gallery_upload_ajax'));

			add_action('wp_ajax_jobportal_gallery_remove_ajax', array($jobportal_ajax, 'jobportal_gallery_remove_ajax'));
			add_action('wp_ajax_nopriv_jobportal_gallery_remove_ajax', array($jobportal_ajax, 'jobportal_agallery_remove_ajax'));

			// Ajax Elementor
			add_action('wp_ajax_jobportal_el_jobs_pagination_ajax', array($jobportal_ajax, 'jobportal_el_jobs_pagination_ajax'));
			add_action('wp_ajax_nopriv_jobportal_el_jobs_pagination_ajax', array($jobportal_ajax, 'jobportal_el_jobs_pagination_ajax'));

			// Service
			add_action('wp_ajax_jobportal_filter_my_service', array($jobportal_ajax, 'jobportal_filter_my_service'));
			add_action('wp_ajax_nopriv_jobportal_filter_my_service', array($jobportal_ajax, 'jobportal_filter_my_service'));

			add_action('wp_ajax_jobportal_employer_order_service', array($jobportal_ajax, 'jobportal_employer_order_service'));
			add_action('wp_ajax_nopriv_jobportal_employer_order_service', array($jobportal_ajax, 'jobportal_employer_order_service'));

			add_action('wp_ajax_jobportal_candidate_order_service', array($jobportal_ajax, 'jobportal_candidate_order_service'));
			add_action('wp_ajax_nopriv_jobportal_candidate_order_service', array($jobportal_ajax, 'jobportal_candidate_order_service'));

			add_action('wp_ajax_jobportal_candidate_wallet_service', array($jobportal_ajax, 'jobportal_candidate_wallet_service'));
			add_action('wp_ajax_nopriv_jobportal_candidate_wallet_service', array($jobportal_ajax, 'jobportal_candidate_wallet_service'));

			add_action('wp_ajax_jobportal_submit_withdraw', array($jobportal_ajax, 'jobportal_submit_withdraw'));
			add_action('wp_ajax_nopriv_jobportal_submit_withdraw', array($jobportal_ajax, 'jobportal_submit_withdraw'));

			// Locations
			add_action('wp_ajax_jobportal_select_country', array($jobportal_ajax, 'jobportal_select_country'));
			add_action('wp_ajax_nopriv_jobportal_select_country', array($jobportal_ajax, 'jobportal_select_country'));

			add_action('wp_ajax_jobportal_select_state', array($jobportal_ajax, 'jobportal_select_state'));
			add_action('wp_ajax_nopriv_jobportal_select_state', array($jobportal_ajax, 'jobportal_select_state'));

			// Apply Package Coupon
			add_action('wp_ajax_jobportal_apply_package_coupon', array($jobportal_ajax, 'jobportal_apply_package_coupon'));
			add_action('wp_ajax_nopriv_jobportal_apply_package_coupon', array($jobportal_ajax, 'jobportal_apply_package_coupon'));

			/**
			 * Hook JobPortal_Jobs
			 */
			$jobportal_jobs = new JobPortal_Jobs();
			add_filter('jobportal_single_jobs_before', array($jobportal_jobs, 'jobportal_set_jobs_view_date'));
			add_filter('jobportal_single_jobs_before', array($jobportal_jobs, 'jobportal_jobs_breadcrumb'));

			add_action('wp_ajax_jobs_submit_ajax', array($jobportal_jobs, 'jobs_submit_ajax'));
			add_action('wp_ajax_nopriv_jobs_submit_ajax', array($jobportal_jobs, 'jobs_submit_ajax'));

			/**
			 * Hook JobPortal_company
			 */
			$jobportal_company = new JobPortal_Company();
			add_action('jobportal_single_company_before', array($jobportal_company, 'jobportal_company_breadcrumb'), 5);

			add_action('wp_ajax_jobportal_company_submit_review_ajax', array($jobportal_company, 'submit_review_ajax'));
			add_action('wp_ajax_nopriv_jobportal_company_submit_review_ajax', array($jobportal_company, 'submit_review_ajax'));

			add_filter('jobportal_company_rating_meta', array($jobportal_company, 'rating_meta_filter'), 4, 9);

			add_action('wp_ajax_jobportal_company_submit_reply_ajax', array($jobportal_company, 'submit_reply_ajax'));
			add_action('wp_ajax_nopriv_company_submit_reply_ajax', array($jobportal_company, 'submit_reply_ajax'));

			add_action('wp_ajax_company_submit_ajax', array($jobportal_company, 'company_submit_ajax'));
			add_action('wp_ajax_nopriv_company_submit_ajax', array($jobportal_company, 'company_submit_ajax'));

			/**
			 * Hook JobPortal_Payment
			 */
			$jobportal_payment = new JobPortal_Payment();
			add_action('wp_ajax_jobportal_paypal_payment_per_package_ajax', array($jobportal_payment, 'paypal_payment_per_package_ajax'));
			add_action('wp_ajax_nopriv_jobportal_paypal_payment_per_package_ajax', array($jobportal_payment, 'paypal_payment_per_package_ajax'));

			add_action('wp_ajax_jobportal_stripe_create_invoice_per_package', array($jobportal_payment, 'stripe_create_invoice_per_package_ajax'));

			add_action('wp_ajax_jobportal_razor_package_create_order', array($jobportal_payment, 'jobportal_razor_package_create_order'));
			add_action('wp_ajax_jobportal_razor_payment_verify', array($jobportal_payment, 'jobportal_razor_payment_verify'));

			add_action('wp_ajax_jobportal_wire_transfer_per_package_ajax', array($jobportal_payment, 'wire_transfer_per_package_ajax'));
			add_action('wp_ajax_nopriv_jobportal_wire_transfer_per_package_ajax', array($jobportal_payment, 'wire_transfer_per_package_ajax'));

			add_action('wp_ajax_jobportal_free_package_ajax', array($jobportal_payment, 'free_package_ajax'));
			add_action('wp_ajax_nopriv_jobportal_free_package_ajax', array($jobportal_payment, 'free_package_ajax'));

			add_action('wp_ajax_jobportal_woocommerce_payment_per_package_ajax', array($jobportal_payment, 'woocommerce_payment_per_package_ajax'));
			add_action('wp_ajax_nopriv_jobportal_woocommerce_payment_per_package_ajax', array($jobportal_payment, 'woocommerce_payment_per_package_ajax'));

			/**
			 * Hook JobPortal_Candidate
			 */
			$jobportal_candidate = new JobPortal_Candidate();
			add_action('jobportal_single_candidate_before', array($jobportal_candidate, 'jobportal_candidate_breadcrumb'), 5);
			add_filter('jobportal_single_candidate_before', array($jobportal_candidate, 'jobportal_set_candidate_view_date'));
			add_filter('jobportal_candidate_rating_meta', array($jobportal_candidate, 'rating_meta_filter'), 4, 9);
			add_filter('update_jobportal_candidate_meta_rating', array($jobportal_candidate, 'update_rating_meta'), 4, 9);

			add_action('wp_ajax_jobportal_candidate_submit_review_ajax', array($jobportal_candidate, 'submit_review_ajax'));
			add_action('wp_ajax_nopriv_jobportal_candidate_submit_review_ajax', array($jobportal_candidate, 'submit_review_ajax'));


			add_action('wp_ajax_jobportal_candidate_submit_reply_ajax', array($jobportal_company, 'submit_reply_ajax'));
			add_action('wp_ajax_nopriv_candidate_submit_reply_ajax', array($jobportal_company, 'submit_reply_ajax'));

			add_action('wp_ajax_upload_candidate_attachment_ajax', array($jobportal_candidate, 'upload_candidate_attachment_ajax'));
			add_action('wp_ajax_nopriv_upload_candidate_attachment_ajax', array($jobportal_candidate, 'upload_candidate_attachment_ajax'));

			add_action('wp_ajax_remove_candidate_attachment_ajax', array($jobportal_candidate, 'remove_candidate_attachment_ajax'));
			add_action('wp_ajax_nopriv_remove_candidate_attachment_ajax', array($jobportal_candidate, 'remove_candidate_attachment_ajax'));

			add_action('wp_ajax_candidate_submit_ajax', array($jobportal_candidate, 'candidate_submit_ajax'));
			add_action('wp_ajax_nopriv_candidate_submit_ajax', array($jobportal_candidate, 'candidate_submit_ajax'));

			add_action('wp_ajax_save_candidate_profile_strength', array($jobportal_candidate, 'save_candidate_profile_strength_ajax'));

			add_action('wp_ajax_jobportal_candidate_print_ajax', array($jobportal_ajax, 'jobportal_candidate_print_ajax'));
			add_action('wp_ajax_nopriv_jobportal_candidate_print_ajax', array($jobportal_ajax, 'jobportal_candidate_print_ajax'));
			/**
			 * Hook JobPortal_Service
			 */
			$jobportal_service = new JobPortal_Service();
			add_action('wp_ajax_jobportal_service_submit_review_ajax', array($jobportal_service, 'submit_review_ajax'));
			add_action('wp_ajax_nopriv_jobportal_service_submit_review_ajax', array($jobportal_service, 'submit_review_ajax'));

			add_filter('jobportal_service_rating_meta', array($jobportal_service, 'rating_meta_filter'), 4, 9);

			add_action('wp_ajax_jobportal_service_submit_reply_ajax', array($jobportal_service, 'submit_reply_ajax'));
			add_action('wp_ajax_nopriv_service_submit_reply_ajax', array($jobportal_service, 'submit_reply_ajax'));

			add_action('wp_ajax_service_submit_ajax', array($jobportal_service, 'service_submit_ajax'));
			add_action('wp_ajax_nopriv_service_submit_ajax', array($jobportal_service, 'service_submit_ajax'));
			/**
			 * Hook JobPortal_Meetings
			 */
			$jobportal_meetings = new JobPortal_Meetings();
			add_action('wp_ajax_jobportal_meetings_settings', array($jobportal_meetings, 'jobportal_meetings_settings'));
			add_action('wp_ajax_nopriv_jobportal_meetings_settings', array($jobportal_meetings, 'jobportal_meetings_settings'));

			add_action('wp_ajax_jobportal_meetings_reschedule_ajax', array($jobportal_meetings, 'jobportal_meetings_reschedule_ajax'));
			add_action('wp_ajax_nopriv_jobportal_meetings_reschedule_ajax', array($jobportal_meetings, 'jobportal_meetings_reschedule_ajax'));

			add_action('wp_ajax_jobportal_meetings_upcoming_dashboard', array($jobportal_meetings, 'jobportal_meetings_upcoming_dashboard'));
			add_action('wp_ajax_nopriv_jobportal_meetings_upcoming_dashboard', array($jobportal_meetings, 'jobportal_meetings_upcoming_dashboard'));

			add_action('wp_ajax_jobportal_meetings_completed_dashboard', array($jobportal_meetings, 'jobportal_meetings_completed_dashboard'));
			add_action('wp_ajax_nopriv_jobportal_meetings_completed_dashboard', array($jobportal_meetings, 'jobportal_meetings_completed_dashboard'));

			add_action('wp_ajax_jobportal_meetings_candidate_dashboard', array($jobportal_meetings, 'jobportal_meetings_candidate_dashboard'));
			add_action('wp_ajax_nopriv_jobportal_meetings_candidate_dashboard', array($jobportal_meetings, 'jobportal_meetings_candidate_dashboard'));

			/**
			 * Hook JobPortal_Messages
			 */
			$jobportal_messages = new JobPortal_Messages();
			add_action('wp_ajax_jobportal_send_messages', array($jobportal_messages, 'jobportal_send_messages'));
			add_action('wp_ajax_nopriv_jobportal_send_messages', array($jobportal_messages, 'jobportal_send_messages'));

			add_action('wp_ajax_jobportal_write_messages', array($jobportal_messages, 'jobportal_write_messages'));
			add_action('wp_ajax_nopriv_jobportal_write_messages', array($jobportal_messages, 'jobportal_write_messages'));

			add_action('wp_ajax_jobportal_messages_list_user', array($jobportal_messages, 'jobportal_messages_list_user'));
			add_action('wp_ajax_nopriv_jobportal_messages_list_user', array($jobportal_messages, 'jobportal_messages_list_user'));

			add_action('wp_ajax_jobportal_refresh_messages', array($jobportal_messages, 'jobportal_refresh_messages'));
			add_action('wp_ajax_nopriv_jobportal_refresh_messages', array($jobportal_messages, 'jobportal_refresh_messages'));

			add_action('wp_ajax_jobportal_load_more_messages', array($jobportal_messages, 'jobportal_load_more_messages'));
			add_action('wp_ajax_nopriv_jobportal_load_more_messages', array($jobportal_messages, 'jobportal_load_more_messages'));

			/**
			 * Hook JobPortal_Notification
			 */
			$jobportal_notification = new JobPortal_Notification();
			add_action('wp_ajax_jobportal_refresh_notification', array($jobportal_notification, 'jobportal_refresh_notification'));
			add_action('wp_ajax_nopriv_jobportal_refresh_notification', array($jobportal_notification, 'jobportal_refresh_notification'));

			/**
			 * Hook JobPortal Candidate Payment
			 */
			$jobportal_candidate_payment = new JobPortal_Candidate_Payment();
			add_action('wp_ajax_jobportal_candidate_paypal_payment_per_package_ajax', array($jobportal_candidate_payment, 'candidate_paypal_payment_per_package_ajax'));
			add_action('wp_ajax_nopriv_jobportal_candidate_paypal_payment_per_package_ajax', array($jobportal_candidate_payment, 'candidate_paypal_payment_per_package_ajax'));

			add_action('wp_ajax_jobportal_candidate_wire_transfer_per_package_ajax', array($jobportal_candidate_payment, 'candidate_wire_transfer_per_package_ajax'));
			add_action('wp_ajax_nopriv_jobportal_candidate_wire_transfer_per_package_ajax', array($jobportal_candidate_payment, 'candidate_wire_transfer_per_package_ajax'));

			add_action('wp_ajax_jobportal_razor_package_candidate_create_order', array($jobportal_candidate_payment, 'jobportal_razor_package_candidate_create_order'));
			add_action('wp_ajax_jobportal_candidate_razor_package_verify', array($jobportal_candidate_payment, 'jobportal_candidate_razor_package_verify'));

			add_action('wp_ajax_jobportal_candidate_free_package_ajax', array($jobportal_candidate_payment, 'candidate_free_package_ajax'));
			add_action('wp_ajax_nopriv_jobportal_candidate_free_package_ajax', array($jobportal_candidate_payment, 'candidate_free_package_ajax'));

			add_action('wp_ajax_jobportal_candidate_woocommerce_payment_per_package_ajax', array($jobportal_candidate_payment, 'candidate_woocommerce_payment_per_package_ajax'));
			add_action('wp_ajax_nopriv_jobportal_candidate_woocommerce_payment_per_package_ajax', array($jobportal_candidate_payment, 'candidate_woocommerce_payment_per_package_ajax'));

			/**
			 * Hook JobPortal Candidate Package Check
			 */
			add_action('wp_ajax_jobportal_check_old_package_before_activate', array($jobportal_ajax, 'jobportal_check_old_package_before_activate'));

			/**
			 * Hook JobPortal Candidate Package Impact Check (for warning modal)
			 */
			$jobportal_candidate_package = new JobPortal_candidate_package();
			add_action('wp_ajax_jobportal_check_candidate_package_impact', array($jobportal_candidate_package, 'ajax_check_package_impact'));

			// Employer package impact check
			$jobportal_package = new JobPortal_Package();
			add_action('wp_ajax_jobportal_check_employer_package_impact', array($jobportal_package, 'ajax_check_package_impact'));

			/**
			 * Hook JobPortal Employer Package Check
			 */
			add_action('wp_ajax_jobportal_check_old_employer_package_before_activate', array($jobportal_ajax, 'jobportal_check_old_employer_package_before_activate'));

			/**
			 * Hook JobPortal Service Payment
			 */
			$jobportal_service_payment = new JobPortal_Service_Payment();
			add_action('wp_ajax_jobportal_paypal_payment_service_addons', array($jobportal_service_payment, 'jobportal_paypal_payment_service_addons'));
			add_action('wp_ajax_nopriv_jobportal_paypal_payment_service_addons', array($jobportal_service_payment, 'jobportal_paypal_payment_service_addons'));

			add_action('wp_ajax_jobportal_wire_transfer_service_addons', array($jobportal_service_payment, 'jobportal_wire_transfer_service_addons'));
			add_action('wp_ajax_nopriv_jobportal_wire_transfer_service_addons', array($jobportal_service_payment, 'jobportal_wire_transfer_service_addons'));

			add_action('wp_ajax_jobportal_razor_service_create_order', array($jobportal_service_payment, 'jobportal_razor_service_create_order'));
			add_action('wp_ajax_jobportal_razor_service_payment_verify', array($jobportal_service_payment, 'jobportal_razor_service_payment_verify'));

			add_action('wp_ajax_jobportal_woocommerce_payment_service_addons', array($jobportal_service_payment, 'jobportal_woocommerce_payment_service_addons'));
			add_action('wp_ajax_nopriv_jobportal_woocommerce_payment_service_addons', array($jobportal_service_payment, 'jobportal_woocommerce_payment_service_addons'));
		}

		/**
		 * Enqueue AJAX nonces for security
		 */
		public function enqueue_ajax_nonces()
		{
			wp_localize_script('jquery', 'jobportal_ajax_nonce', array(
				// Withdraw
				'withdraw' => wp_create_nonce('jobportal_withdraw_nonce'),

				// Wishlist & Follow
				'wishlist' => wp_create_nonce('jobportal_wishlist_nonce'),
				'service_wishlist' => wp_create_nonce('jobportal_service_wishlist_nonce'),
				'follow' => wp_create_nonce('jobportal_follow_nonce'),
				'invite' => wp_create_nonce('jobportal_invite_nonce'),
				'follow_candidate' => wp_create_nonce('jobportal_follow_candidate_nonce'),

				// Service
			'service_addons'   => wp_create_nonce('jobportal_service_addons_nonce'),
			'submit_review'    => wp_create_nonce('jobportal_submit_review_ajax_nonce'),
			'submit_reply'     => wp_create_nonce('jobportal_submit_reply_ajax_nonce'),
			'create_company'   => wp_create_nonce('create_company_nonce'),
			'submit_service'   => wp_create_nonce('jobportal_service_submit_nonce'),
			'send_message'     => wp_create_nonce('send_message_nonce'),
			'write_message'    => wp_create_nonce('write_message_nonce'),
			'update_profile'   => wp_create_nonce('jobportal_update_profile_ajax_nonce'),
			'change_password'  => wp_create_nonce('jobportal_change_password_ajax_nonce'),
            'download_cv_candidate' => wp_create_nonce('jobportal_download_cv_candidate_nonce'),
            'jobportal_candidate_print' => wp_create_nonce('jobportal_candidate_print_nonce'),
            'update_payout' => wp_create_nonce('jobportal_update_payout_ajax_nonce'),
            'jobportal_chart_nonce' => wp_create_nonce('jobportal_chart_nonce'),
            ));
		}

		/**
		 * Get template path
		 */
		public function template_path()
		{
			return apply_filters('jobportal_template_path', 'jobportal-framework/');
		}
	}
}

if (!function_exists("JOBPORTAL")) {
	function JOBPORTAL()
	{
		return JobPortal_Core::instance();
	}
}
// Global for backwards compatibility.
$GLOBALS["JobPortal_Core"] = JOBPORTAL();
