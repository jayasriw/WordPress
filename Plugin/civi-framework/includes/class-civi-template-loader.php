<?php

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('Civi_Template_Loader')) {
	/**
	 * Civi_Template_Loader
	 */
	class Civi_Template_Loader
	{
		/**
		 * Constructor
		 * *******************************************************
		 */
		public function __construct()
		{
			$this->template_jobs_hooks();
			$this->template_company_hooks();
			$this->template_candidate_hooks();
			$this->template_service_hooks();
			$this->includes();
			$civi_ajax = new Civi_Ajax();

			add_filter('script_loader_tag', array($this, 'add_defer_facebook'), 10, 2);
			add_filter('body_class', array($this, 'civi_login_to_view'));

			add_action('after_post_job_form', array($this, 'ai_form_generate'));

			add_filter('wp_mail_from_name', array($this, 'custom_wp_mail_from_name'));
			add_action('auto_check_expired_package', array($this, 'handle_auto_check_expired_package'), 10, 1);

			add_action('wp_ajax_civi_ajax_select2', array($civi_ajax, 'civi_ajax_select2'));
			add_action('wp_ajax_nopriv_civi_ajax_select2', array($civi_ajax, 'civi_ajax_select2'));

			add_action('wp_ajax_civi_ajax_ui', array($civi_ajax, 'civi_ajax_ui'));
			add_action('wp_ajax_nopriv_civi_ajax_ui', array($civi_ajax, 'civi_ajax_ui'));
		}

		/**
		 * Includes library for plugin
		 * *******************************************************
		 */
		private function includes()
		{
			require_once CIVI_PLUGIN_DIR . 'includes/civi-template-hooks.php';
		}

		/**
		 * Register the stylesheets for the public-facing side of the site.
		 */
		public function admin_enqueue()
		{
			$min_suffix = civi_get_option('enable_min_css', 0) == 1 ? '.min' : '';

			wp_enqueue_style('line-awesome', CIVI_PLUGIN_URL . 'assets/libs/line-awesome/css/line-awesome' . $min_suffix . '.css', array(), '1.1.0', 'all');

			wp_enqueue_style('hint', CIVI_PLUGIN_URL . 'assets/libs/hint/hint.min.css', array(), '2.6.0', 'all');

			wp_enqueue_script('lottie', CIVI_PLUGIN_URL . 'assets/libs/lottie/lottie.min.js', array('jquery'), false, true);

			wp_enqueue_script('magnific-popup', CIVI_PLUGIN_URL . 'assets/libs/magnific-popup/jquery.magnific-popup.min.js', array('jquery'), false, true);

			wp_enqueue_style('magnific-popup', CIVI_PLUGIN_URL . 'assets/libs/magnific-popup/magnific-popup.css', array(), CIVI_PLUGIN_VER, 'all');

			wp_enqueue_style(CIVI_PLUGIN_PREFIX . '-admin', CIVI_PLUGIN_URL . 'assets/css/_admin' . $min_suffix . '.css', array(), CIVI_PLUGIN_VER, 'all');

			wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'import', CIVI_PLUGIN_URL . 'assets/js/import' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_localize_script(
				CIVI_PLUGIN_PREFIX . 'import',
				'civi_import_vars',
				array(
					'ajax_url' => CIVI_AJAX_URL,
					'animation_url' => CIVI_PLUGIN_URL . 'assets/animation/',
				)
			);

			wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'admin', CIVI_PLUGIN_URL . 'assets/js/admin' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_localize_script(
				CIVI_PLUGIN_PREFIX . 'admin',
				'civi_admin_vars',
				array(
					'ajax_url' => CIVI_AJAX_URL,
				)
			);
		}

		/**
		 * Register the JavaScript for the admin area.
		 */
		public function enqueue_scripts()
		{
			$min_suffix = civi_get_option('enable_min_js', 0) == 1 ? '.min' : '';

		wp_enqueue_script('waypoints', CIVI_PLUGIN_URL . 'assets/libs/waypoints/jquery.waypoints' . $min_suffix . '.js', array('jquery'), '4.0.1', true);

		wp_enqueue_script('select2', CIVI_PLUGIN_URL . 'assets/libs/select2/js/select2.min.js', array('jquery'), '4.0.13', true);

		$this->enqueue_flatpickr($min_suffix);

			wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'jobs', CIVI_PLUGIN_URL . 'assets/js/jobs/jobs' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_localize_script(CIVI_PLUGIN_PREFIX . 'jobs', 'civi_jobs_vars', array(
				'expires_past_date' => esc_html__('Expires (past date)', 'civi-framework'),
				'expires_one_day' => esc_html__('Expires in 1 day', 'civi-framework'),
				'expires_many_days' => esc_html__('Expires in %d days', 'civi-framework'),
			));

			wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'company', CIVI_PLUGIN_URL . 'assets/js/company/company' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'candidate', CIVI_PLUGIN_URL . 'assets/js/candidate/candidate' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'apply', CIVI_PLUGIN_URL . 'assets/js/jobs/apply' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'invite', CIVI_PLUGIN_URL . 'assets/js/jobs/invite' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'dashboard', CIVI_PLUGIN_URL . 'assets/js/dashboard/dashboard' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			//register
			wp_register_script('slick', CIVI_PLUGIN_URL . 'assets/libs/slick/slick.min.js', array('jquery'), '1.8.1', false);

			wp_register_script('lightgallery', CIVI_PLUGIN_URL . 'assets/libs/lightgallery/js/lightgallery.min.js', array('jquery'), false, false);

			wp_register_script('lity', CIVI_PLUGIN_URL . 'assets/libs/lity/js/lity' . $min_suffix . '.js', array('jquery'), false, true);

			wp_register_script('chart', CIVI_PLUGIN_URL . 'assets/libs/chart/chart' . $min_suffix . '.js', array('jquery'), false, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'light-gallery', CIVI_PLUGIN_URL . 'assets/js/loop/light-gallery' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'wishlist', CIVI_PLUGIN_URL . 'assets/js/jobs/wishlist' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'messages-dashboard', CIVI_PLUGIN_URL . 'assets/js/dashboard/messages' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'notification', CIVI_PLUGIN_URL . 'assets/js/dashboard/notification' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'payout', CIVI_PLUGIN_URL . 'assets/js/dashboard/payout' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'company-follow', CIVI_PLUGIN_URL . 'assets/js/company/follow' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'candidate-follow', CIVI_PLUGIN_URL . 'assets/js/candidate/follow' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'chart', CIVI_PLUGIN_URL . 'assets/js/dashboard/chart' . $min_suffix . '.js', array('jquery'), '', true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'jobs-dashboard', CIVI_PLUGIN_URL . 'assets/js/dashboard/jobs' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'applicants-dashboard', CIVI_PLUGIN_URL . 'assets/js/dashboard/applicants' . $min_suffix . '.js', array('jquery'), time(), true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'candidates-dashboard', CIVI_PLUGIN_URL . 'assets/js/dashboard/candidates' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'follow-candidate', CIVI_PLUGIN_URL . 'assets/js/dashboard/follow-candidate' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'invite-candidate', CIVI_PLUGIN_URL . 'assets/js/dashboard/invite-candidate' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'my-wishlist', CIVI_PLUGIN_URL . 'assets/js/dashboard/my-wishlist' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'employer-wishlist', CIVI_PLUGIN_URL . 'assets/js/dashboard/employer-wishlist' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'employer-service-order', CIVI_PLUGIN_URL . 'assets/js/dashboard/employer-service-order' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'candidate-service-order', CIVI_PLUGIN_URL . 'assets/js/dashboard/candidate-service-order' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'my-follow', CIVI_PLUGIN_URL . 'assets/js/dashboard/my-follow' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'my-apply', CIVI_PLUGIN_URL . 'assets/js/dashboard/my-apply' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'my-review', CIVI_PLUGIN_URL . 'assets/js/dashboard/my-review' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'my-invite', CIVI_PLUGIN_URL . 'assets/js/dashboard/my-invite' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'my-service', CIVI_PLUGIN_URL . 'assets/js/dashboard/my-service' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'my-wallet', CIVI_PLUGIN_URL . 'assets/js/dashboard/my-wallet' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

		wp_register_script(CIVI_PLUGIN_PREFIX . 'button-loading', CIVI_PLUGIN_URL . 'assets/js/utils/button-loading.js', array('jquery'), CIVI_PLUGIN_VER, true);

		wp_register_script(CIVI_PLUGIN_PREFIX . 'user-package-pagination', CIVI_PLUGIN_URL . 'assets/js/dashboard/user-package-pagination' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

		wp_register_script(CIVI_PLUGIN_PREFIX . 'user-package-warning', CIVI_PLUGIN_URL . 'assets/js/dashboard/user-package-warning' . $min_suffix . '.js', array('jquery', CIVI_PLUGIN_PREFIX . 'button-loading'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'jobs-submit', CIVI_PLUGIN_URL . 'assets/js/jobs/submit' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			$map_type_for_deps = civi_get_option('map_type', 'google_map');
			$jobs_archive_deps = array('jquery');
			if ($map_type_for_deps === 'google_map') {
				$jobs_archive_deps[] = 'google-map';
			}
			wp_register_script(CIVI_PLUGIN_PREFIX . 'jobs-archive', CIVI_PLUGIN_URL . 'assets/js/jobs/archive.js', $jobs_archive_deps, CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'candidate-submit', CIVI_PLUGIN_URL . 'assets/js/candidate/submit.js', array('jquery'), null, true);

			$candidate_archive_deps = array('jquery');
			if ($map_type_for_deps === 'google_map') {
				$candidate_archive_deps[] = 'google-map';
			}
			wp_register_script(CIVI_PLUGIN_PREFIX . 'candidate-archive', CIVI_PLUGIN_URL . 'assets/js/candidate/archive' . $min_suffix . '.js', $candidate_archive_deps, CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'candidate-review', CIVI_PLUGIN_URL . 'assets/js/candidate/review' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'download-cv', CIVI_PLUGIN_URL . 'assets/js/candidate/download-cv' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'company-submit', CIVI_PLUGIN_URL . 'assets/js/company/submit' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			$company_archive_deps = array('jquery');
			if ($map_type_for_deps === 'google_map') {
				$company_archive_deps[] = 'google-map';
			}
			wp_register_script(CIVI_PLUGIN_PREFIX . 'company-archive', CIVI_PLUGIN_URL . 'assets/js/company/archive' . $min_suffix . '.js', $company_archive_deps, CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'company-review', CIVI_PLUGIN_URL . 'assets/js/company/review' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'company-related', CIVI_PLUGIN_URL . 'assets/js/company/related' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'company-dashboard', CIVI_PLUGIN_URL . 'assets/js/dashboard/company' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			$service_archive_deps = array('jquery');
			if ($map_type_for_deps === 'google_map') {
				$service_archive_deps[] = 'google-map';
			}
			wp_register_script(CIVI_PLUGIN_PREFIX . 'service-archive', CIVI_PLUGIN_URL . 'assets/js/service/archive' . $min_suffix . '.js', $service_archive_deps, CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'service-submit', CIVI_PLUGIN_URL . 'assets/js/service/submit' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'settings', CIVI_PLUGIN_URL . 'assets/js/settings' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script('jquery-validate', CIVI_PLUGIN_URL . 'assets/libs/validate/jquery.validate.min.js', array('jquery'), '1.19.5', true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'meetings', CIVI_PLUGIN_URL . 'assets/js/dashboard/meetings' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script('stripe-checkout', 'https://checkout.stripe.com/checkout.js', array(), null, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'service-review', CIVI_PLUGIN_URL . 'assets/js/service/review' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'service-wishlist', CIVI_PLUGIN_URL . 'assets/js/service/wishlist' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'candidate-payment', CIVI_PLUGIN_URL . 'assets/js/candidate/payment' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'service', CIVI_PLUGIN_URL . 'assets/js/service/service' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'service-payment', CIVI_PLUGIN_URL . 'assets/js/service/payment' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'payment', CIVI_PLUGIN_URL . 'assets/js/payment/payment' . $min_suffix . '.js', array('jquery', 'wp-util'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'candidate-print', CIVI_PLUGIN_URL . 'assets/js/candidate/print' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			//Loop

			wp_register_script(CIVI_PLUGIN_PREFIX . 'search-autocomplete', CIVI_PLUGIN_URL . 'assets/js/loop/search-autocomplete' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'thumbnail', CIVI_PLUGIN_URL . 'assets/js/loop/thumbnail' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'thumbnail', CIVI_PLUGIN_URL . 'assets/js/loop/thumbnail' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'avatar', CIVI_PLUGIN_URL . 'assets/js/loop/avatar' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'custom_image', CIVI_PLUGIN_URL . 'assets/js/loop/custom-image' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'gallery', CIVI_PLUGIN_URL . 'assets/js/loop/gallery' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'upload-cv', CIVI_PLUGIN_URL . 'assets/js/loop/upload-cv' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'social-network', CIVI_PLUGIN_URL . 'assets/js/loop/social-network' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'map-box-submit', CIVI_PLUGIN_URL . 'assets/js/loop/map/submit/map-box' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'openstreet-map-submit', CIVI_PLUGIN_URL . 'assets/js/loop/map/submit/openstreet-map' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'google-map-submit', CIVI_PLUGIN_URL . 'assets/js/loop/map/submit/google-map' . $min_suffix . '.js', array('jquery', 'google-map'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'map-box-single', CIVI_PLUGIN_URL . 'assets/js/loop/map/single/map-box' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'openstreet-map-single', CIVI_PLUGIN_URL . 'assets/js/loop/map/single/openstreet-map' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'google-map-single', CIVI_PLUGIN_URL . 'assets/js/loop/map/single/google-map' . $min_suffix . '.js', array('jquery', 'google-map'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'login-to-view', CIVI_PLUGIN_URL . 'assets/js/loop/login-to-view' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'select-location', CIVI_PLUGIN_URL . 'assets/js/loop/select-location' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'mess_image', CIVI_PLUGIN_URL . 'assets/js/loop/mess-image.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_register_script(CIVI_PLUGIN_PREFIX . 'mess_file', CIVI_PLUGIN_URL . 'assets/js/loop/mess-file.js', array('jquery'), CIVI_PLUGIN_VER, true);

			$payment_data = array(
				'ajax_url' => CIVI_AJAX_URL,
				'processing_text' => esc_html__('Processing, Please wait...', 'civi-framework')
			);
			wp_localize_script(CIVI_PLUGIN_PREFIX . 'payment', 'civi_payment_vars', $payment_data);
			wp_localize_script(CIVI_PLUGIN_PREFIX . 'service-payment', 'civi_payment_vars', $payment_data);
			wp_localize_script(CIVI_PLUGIN_PREFIX . 'candidate-payment', 'civi_payment_vars', $payment_data);

			wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'template', CIVI_PLUGIN_URL . 'assets/js/template' . $min_suffix . '.js', array('jquery'), CIVI_PLUGIN_VER, true);

			wp_add_inline_script(CIVI_PLUGIN_PREFIX . 'template', 'var Civi_Inline_Style = document.getElementById( \'civi_main-style-inline-css\' );');

			$archive_jobs_items_amount = civi_get_option('archive_jobs_items_amount', '12');
			$map_zoom_level = civi_get_option('map_zoom_level', '12');
			$map_pin_cluster = civi_get_option('map_pin_cluster', 1);
			$map_type = civi_get_option('map_type', 'google_map');
			$google_map_type = 'roadmap';
			if ($map_type == 'google_map') {
				$google_map_style = civi_get_option('googlemap_style', '');
				$google_map_type = civi_get_option('googlemap_type', 'roadmap');
			} else {
				$google_map_style = civi_get_option('mapbox_style', 'streets-v11');
			}
			if ($map_type == 'mapbox') {
				$api_key = civi_get_option('mapbox_api_key');
			} else if ($map_type == 'openstreetmap') {
				$api_key = civi_get_option('openstreetmap_api_key');
			} else {
				$api_key = civi_get_option('googlemap_api_key');
			}

			$google_map_needed = 'true';
			$map_marker_icon_url = CIVI_PLUGIN_URL . 'assets/images/map-marker-icon.png';
			$map_cluster_icon_url = CIVI_PLUGIN_URL . 'assets/images/cluster-icon.png';
			$map_effects = civi_get_option('map_effects');
			$enable_archive_map = civi_get_option('enable_archive_map', 1);

			$item_amount = $archive_jobs_items_amount;
			$date_format = get_option('date_format');
			$taxonomy_name = get_query_var('taxonomy');
			$default_lat = civi_get_option('map_lat_default', '-37.9721047');
			$default_lng = civi_get_option('map_lng_default', '144.7228153');

			$wp_locale = get_locale();
			$locale_lang = substr($wp_locale, 0, 2);

			if (function_exists('pll_current_language')) {
				$locale_lang = pll_current_language('slug') ?: $locale_lang;
			} else if (defined('ICL_SITEPRESS_VERSION')) {
				$wpml_lang = apply_filters('wpml_current_language', null);
				if ($wpml_lang) {
					$locale_lang = substr($wpml_lang, 0, 2);
				}
			}

			wp_localize_script(
				CIVI_PLUGIN_PREFIX . 'template',
				'civi_template_vars',
				array(
					'ajax_url' => CIVI_AJAX_URL,
					'not_found' => esc_html__("We didn't find any results, you can retry with other keyword.", 'civi-framework'),
					'not_jobs' => esc_html__('No jobs found', 'civi-framework'),
					'not_file' => esc_html__('Please upload the appropriate file format', 'civi-framework'),
					'no_results' => esc_html__('No Results', 'civi-framework'),
					'default_lat' => $default_lat,
					'default_lng' => $default_lng,
					'item_amount' => $item_amount,
					'wishlist_save' => esc_html__('Save', 'civi-framework'),
					'wishlist_saved' => esc_html__('Saved', 'civi-framework'),
					'follow_save' => esc_html__('Follow', 'civi-framework'),
					'follow_saved' => esc_html__('Following', 'civi-framework'),
					'apply_saved' => esc_html__('Applied', 'civi-framework'),
					'login_to_view' => esc_html__('Please login to view', 'civi-framework'),
					'package_expires' => esc_html__('The quantity in your package has reached its limit or your package has expired', 'civi-framework'),
					'marker_image_size' => '100x100',
					'date_format' => $date_format,
					'googlemap_default_zoom' => $map_zoom_level,
					'map_pin_cluster' => $map_pin_cluster,
					'map_api_key' => $api_key,
					'marker_default_icon' => $map_marker_icon_url,
					'clusterIcon' => $map_cluster_icon_url,
					'map_effects' => $map_effects,
					'map_type' => $map_type,
					'google_map_needed' => $google_map_needed,
					'flatpickr_locale' => $locale_lang,
					'wp_locale' => $wp_locale,
					'google_map_style' => $google_map_style,
					'google_map_type' => $google_map_type,
					'enable_archive_map' => $enable_archive_map,
					'sending_text' => esc_html__('Sending email, Please wait...', 'civi-framework'),
					'ajax_nonce' => wp_create_nonce('civi_ajax_nonce'),
				)
			);

			// Google map API
			$map_ssl = civi_get_option('map_ssl', 0);
			$map_type = civi_get_option('map_type', '');

			if ($map_type == 'google_map') {
				// Đăng ký script callback trước Google Maps API
				wp_register_script('google-maps-callback', CIVI_PLUGIN_URL . 'assets/js/google-maps-callback.js', array('jquery'), CIVI_PLUGIN_VER, false);

				$googlemap_api_key = civi_get_option('googlemap_api_key', 'AIzaSyBvPDNG6pePr9iFpeRKaOlaZF_l0oT3lWk');
				if (esc_html($map_ssl) == 1 || is_ssl()) {
					wp_register_script('google-map', 'https://maps-api-ssl.google.com/maps/api/js?libraries=places&language=' . get_locale() . '&key=' . esc_html($googlemap_api_key) . '&callback=civiGoogleMapsCallback', array('jquery', 'google-maps-callback'), CIVI_PLUGIN_VER, true);
				} else {
					wp_register_script('google-map', 'http://maps.googleapis.com/maps/api/js?libraries=places&language=' . get_locale() . '&key=' . esc_html($googlemap_api_key) . '&callback=civiGoogleMapsCallback', array('jquery', 'google-maps-callback'), CIVI_PLUGIN_VER, true);
				}

				// Đăng ký autocomplete fix script
				wp_register_script(CIVI_PLUGIN_PREFIX . 'autocomplete-fix', CIVI_PLUGIN_URL . 'assets/js/autocomplete-fix.js', array('jquery', 'google-map'), CIVI_PLUGIN_VER, true);
			}

			if ($map_pin_cluster != 0) {
				wp_register_script('markerclusterer', CIVI_PLUGIN_URL . 'assets/libs/markerclusterer/markerclusterer.js', array('jquery', 'google-map'), false, true);
			}

			// Mapbox
			if ($map_type == 'mapbox') {
				wp_register_script(CIVI_PLUGIN_PREFIX . 'es6-promisel', CIVI_PLUGIN_URL . 'assets/libs/mapbox/es6-promise.min.js', array('jquery'), '1.0.0', false);
				wp_register_script(CIVI_PLUGIN_PREFIX . 'es6-promise', CIVI_PLUGIN_URL . 'assets/libs/mapbox/es6-promise.auto.min.js', array('jquery', CIVI_PLUGIN_PREFIX . 'es6-promisel'), '1.0.0', false);
				wp_register_script(CIVI_PLUGIN_PREFIX . 'mapbox-gl', CIVI_PLUGIN_URL . 'assets/libs/mapbox/mapbox-gl.js', array('jquery', CIVI_PLUGIN_PREFIX . 'es6-promise'), '1.13.3', false);
				wp_register_script(CIVI_PLUGIN_PREFIX . 'mapbox-gl-geocoder', CIVI_PLUGIN_URL . 'assets/libs/mapbox/mapbox-gl-geocoder.min.js', array('jquery', CIVI_PLUGIN_PREFIX . 'mapbox-gl'), '1.0.0', false);
			}

			// Openstreetmap
			if ($map_type == 'openstreetmap') {
				wp_register_script(CIVI_PLUGIN_PREFIX . 'leaflet', CIVI_PLUGIN_URL . 'assets/libs/leaflet/leaflet.js', array('jquery'), '1.0.0', true);
				wp_register_script(CIVI_PLUGIN_PREFIX . 'leaflet-src', CIVI_PLUGIN_URL . 'assets/libs/leaflet/leaflet-src.js', array('jquery'), '1.0.0', true);
				wp_register_script(CIVI_PLUGIN_PREFIX . 'esri-leaflet', CIVI_PLUGIN_URL . 'assets/libs/leaflet/esri-leaflet.js', array('jquery'), '1.0.0', true);
				wp_register_script(CIVI_PLUGIN_PREFIX . 'esri-leaflet-geocoder', CIVI_PLUGIN_URL . 'assets/libs/leaflet/esri-leaflet-geocoder.js', array('jquery'), '1.0.0', true);
			}
		}

		/**
		 * Register the stylesheets for the public-facing side of the site.
		 */
		public function enqueue_styles()
		{
			$min_suffix = civi_get_option('enable_min_css', 0) == 1 ? '.min' : '';

			wp_enqueue_style('font-awesome-all', CIVI_PLUGIN_URL . '/assets/libs/font-awesome/css/fontawesomess.min.css', array(), '5.2.0', 'all');

			wp_enqueue_style('hint', CIVI_PLUGIN_URL . 'assets/libs/hint/hint.min.css', array(), '2.6.0', 'all');

			wp_enqueue_style('slick', CIVI_PLUGIN_URL . 'assets/libs/slick/slick.min.css', array(), CIVI_PLUGIN_VER, 'all');

			wp_enqueue_style('slick-theme', CIVI_PLUGIN_URL . 'assets/libs/slick/slick-theme.css', array(), CIVI_PLUGIN_VER, 'all');

			wp_enqueue_style('select2', CIVI_PLUGIN_URL . 'assets/libs/select2/css/select2.min.css', array(), '4.0.13', 'all');

			//RTL
			$enable_rtl_mode = civi_get_option("enable_rtl_mode");
			if (is_rtl() || $enable_rtl_mode) {
				wp_enqueue_style(CIVI_PLUGIN_PREFIX . '-rtl', CIVI_PLUGIN_URL . 'assets/css/rtl/_rtl' . $min_suffix . '.css', array(), CIVI_PLUGIN_VER, 'all');
				wp_enqueue_style(CIVI_PLUGIN_PREFIX . '-custom-rtl', CIVI_PLUGIN_URL . 'assets/css/rtl/_custom-rtl' . $min_suffix . '.css', array(), CIVI_PLUGIN_VER, 'all');
			} else {
				wp_enqueue_style(CIVI_PLUGIN_PREFIX . '-style', CIVI_PLUGIN_URL . 'assets/scss/style.min.css', array(), CIVI_PLUGIN_VER, 'all');
			}

			//Post
			wp_dequeue_style('wp-block-library');
			wp_dequeue_style('wp-block-library-theme');
			if ((is_single() || is_archive()) && get_post_type() == 'post') {
				wp_enqueue_style('wp-block-library');
				wp_enqueue_style('wp-block-library-theme');
			}

			//WooCommerce
			if (class_exists('WooCommerce')) {
				wp_enqueue_style('checkout-woocomerce', CIVI_PLUGIN_URL . 'assets/scss/package/woocomerce.min.css', array(), CIVI_PLUGIN_VER, 'all');
			}

			//Register
			wp_register_style('lightgallery', CIVI_PLUGIN_URL . 'assets/libs/lightgallery/css/lightgallery.min.css', array(), false, 'all');

			wp_register_style('lity', CIVI_PLUGIN_URL . 'assets/libs/lity/css/lity.min.css', array(), CIVI_PLUGIN_VER, 'all');

			wp_register_style(CIVI_PLUGIN_PREFIX . 'dashboard', CIVI_PLUGIN_URL . 'assets/scss/dashboard/dashboard.min.css', array(), CIVI_PLUGIN_VER, 'all');

			//Map
			$map_type = civi_get_option('map_type', 'mapbox');
			if ($map_type == 'mapbox') {
				wp_register_style(CIVI_PLUGIN_PREFIX . 'mapbox-gl', CIVI_PLUGIN_URL . 'assets/libs/mapbox/mapbox-gl.css', array());
				wp_register_style(CIVI_PLUGIN_PREFIX . 'mapbox-gl-geocoder', CIVI_PLUGIN_URL . 'assets/libs/mapbox/mapbox-gl-geocoder.css', array());
			}
			if ($map_type == 'openstreetmap') {
				wp_register_style(CIVI_PLUGIN_PREFIX . 'leaflet', CIVI_PLUGIN_URL . 'assets/libs/leaflet/leaflet.css', array());
				wp_register_style(CIVI_PLUGIN_PREFIX . 'esri-leaflet', CIVI_PLUGIN_URL . 'assets/libs/leaflet/esri-leaflet-geocoder.css', array());
			}
			if ($map_type == 'google_map') {
				wp_register_style(CIVI_PLUGIN_PREFIX . 'autocomplete-fix', CIVI_PLUGIN_URL . 'assets/scss/_autocomplete-fix.scss', array());
			}

			//Elementor

			wp_enqueue_style('widget-style', CIVI_PLUGIN_URL . 'modules/elementor/assets/css/widget.min.css', array(), CIVI_THEME_VERSION);

			wp_enqueue_style(CIVI_PLUGIN_PREFIX . 'search-horizontal', CIVI_PLUGIN_URL . 'modules/elementor/assets/scss/search-horizontal.min.css', array());

			wp_enqueue_style(CIVI_PLUGIN_PREFIX . 'search-vertical', CIVI_PLUGIN_URL . 'modules/elementor/assets/scss/search-vertical.min.css', array());

			wp_register_style(CIVI_PLUGIN_PREFIX . 'jobs', CIVI_PLUGIN_URL . 'modules/elementor/assets/scss/jobs.min.css', array());

			wp_register_style(CIVI_PLUGIN_PREFIX . 'job-alerts', CIVI_PLUGIN_URL . 'modules/elementor/assets/scss/job-alerts.css', array());

			wp_register_style(CIVI_PLUGIN_PREFIX . 'jobs-apply', CIVI_PLUGIN_URL . 'modules/elementor/assets/scss/jobs-apply.min.css', array());

			wp_register_style(CIVI_PLUGIN_PREFIX . 'jobs-animation', CIVI_PLUGIN_URL . 'modules/elementor/assets/scss/jobs-animation.min.css', array());

			wp_register_style(CIVI_PLUGIN_PREFIX . 'jobs-category', CIVI_PLUGIN_URL . 'modules/elementor/assets/scss/jobs-category.min.css', array());

			wp_register_style(CIVI_PLUGIN_PREFIX . 'jobs-location', CIVI_PLUGIN_URL . 'modules/elementor/assets/scss/jobs-location.min.css', array());

			wp_register_style(CIVI_PLUGIN_PREFIX . 'companies', CIVI_PLUGIN_URL . 'modules/elementor/assets/scss/companies.min.css', array());

			wp_register_style(CIVI_PLUGIN_PREFIX . 'candidates', CIVI_PLUGIN_URL . 'modules/elementor/assets/scss/candidates.min.css', array());

			wp_register_style(CIVI_PLUGIN_PREFIX . 'companies-category', CIVI_PLUGIN_URL . 'modules/elementor/assets/scss/companies-category.min.css', array());

			wp_register_style(CIVI_PLUGIN_PREFIX . 'candidate-category', CIVI_PLUGIN_URL . 'modules/elementor/assets/scss/candidate-category.min.css', array());

			wp_register_style(CIVI_PLUGIN_PREFIX . 'candidate-box', CIVI_PLUGIN_URL . 'modules/elementor/assets/scss/candidate-box.min.css', array());

			wp_register_style(CIVI_PLUGIN_PREFIX . 'package', CIVI_PLUGIN_URL . 'modules/elementor/assets/scss/package.min.css', array());

			wp_register_style(CIVI_PLUGIN_PREFIX . 'advanced-archive', CIVI_PLUGIN_URL . 'modules/elementor/assets/scss/advanced-archive.min.css', array());

			if (post_type_exists('service')) {
				wp_register_style(CIVI_PLUGIN_PREFIX . 'service', CIVI_PLUGIN_URL . 'modules/elementor/assets/scss/service.css', array());
				wp_register_style(CIVI_PLUGIN_PREFIX . 'service-category', CIVI_PLUGIN_URL . 'modules/elementor/assets/scss/service-category.css', array());
			}
		}

		/**
		 * @return Jobs taxonomy
		 */
		function is_jobs_taxonomy()
		{
			return is_tax(get_object_taxonomies('jobs'));
		}

		/**
		 * @return company taxonomy
		 */
		function is_company_taxonomy()
		{
			return is_tax(get_object_taxonomies('company'));
		}

		/**
		 * @return candidate taxonomy
		 */
		function is_candidate_taxonomy()
		{
			return is_tax(get_object_taxonomies('candidate'));
		}

		/**
		 * @return service taxonomy
		 */
		function is_service_taxonomy()
		{
			return is_tax(get_object_taxonomies('service'));
		}

		/**
		 * @param $template
		 * @return string
		 */
		public function template_loader($template)
		{
			$find = array();
			$file = '';

			if (is_embed()) {
				return $template;
			}

			//Jobs
			if (is_single() && (get_post_type() == 'jobs')) {
				if (get_post_type() == 'jobs') {
					$file = 'single-jobs.php';
				}
				$find[] = $file;
				$find[] = CIVI()->template_path() . $file;
			} elseif ($this->is_jobs_taxonomy()) {
				$term = get_queried_object();

				if (is_tax()) {
					$file = 'taxonomy-jobs.php';
				} else {
					$file = 'archive-jobs.php';
				}

				$find[] = 'taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
				$find[] = CIVI()->template_path() . 'taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
				$find[] = 'taxonomy-' . $term->taxonomy . '.php';
				$find[] = CIVI()->template_path() . 'taxonomy-' . $term->taxonomy . '.php';
				$find[] = $file;
				$find[] = CIVI()->template_path() . $file;
			} elseif (is_post_type_archive('jobs') || is_page('jobs') || (is_search() && isset($_GET['post_type']) && $_GET['post_type'] == 'jobs')) {
				$file = 'archive-jobs.php';
				$find[] = $file;
				$find[] = CIVI()->template_path() . $file;
			}

			//Company
			if (is_single() && (get_post_type() == 'company')) {
				if (get_post_type() == 'company') {
					$file = 'single-company.php';
				}
				$find[] = $file;
				$find[] = CIVI()->template_path() . $file;
			} elseif ($this->is_company_taxonomy()) {
				$term = get_queried_object();

				if (is_tax()) {
					$file = 'taxonomy-company.php';
				} else {
					$file = 'archive-company.php';
				}

				$find[] = 'taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
				$find[] = CIVI()->template_path() . 'taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
				$find[] = 'taxonomy-' . $term->taxonomy . '.php';
				$find[] = CIVI()->template_path() . 'taxonomy-' . $term->taxonomy . '.php';
				$find[] = $file;
				$find[] = CIVI()->template_path() . $file;
			} elseif (is_post_type_archive('company') || is_page('company') || (is_search() && isset($_GET['post_type']) && $_GET['post_type'] == 'company')) {
				$file = 'archive-company.php';
				$find[] = $file;
				$find[] = CIVI()->template_path() . $file;
			}

			// Candidate
			if (is_single() && (get_post_type() == 'candidate')) {
				if (get_post_type() == 'candidate') {
					$file = 'single-candidate.php';
				}
				$find[] = $file;
				$find[] = CIVI()->template_path() . $file;
			} elseif ($this->is_candidate_taxonomy()) {
				$term = get_queried_object();

				if (is_tax()) {
					$file = 'taxonomy-candidates.php';
				} else {
					$file = 'archive-candidates.php';
				}

				$find[] = 'taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
				$find[] = CIVI()->template_path() . 'taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
				$find[] = 'taxonomy-' . $term->taxonomy . '.php';
				$find[] = CIVI()->template_path() . 'taxonomy-' . $term->taxonomy . '.php';
				$find[] = $file;
				$find[] = CIVI()->template_path() . $file;
			} elseif (is_post_type_archive('candidate') || is_page('candidate') || (is_search() && isset($_GET['post_type']) && $_GET['post_type'] == 'candidate')) {
				$file = 'archive-candidates.php';
				$find[] = $file;
				$find[] = CIVI()->template_path() . $file;
			}

			//Service
			if (civi_get_option('enable_post_type_service') === '1') {
				if (is_single() && (get_post_type() == 'service')) {
					if (get_post_type() == 'service') {
						$file = 'single-service.php';
					}
					$find[] = $file;
					$find[] = CIVI()->template_path() . $file;
				} elseif ($this->is_service_taxonomy()) {
					$term = get_queried_object();

					if (is_tax()) {
						$file = 'taxonomy-service.php';
					} else {
						$file = 'archive-service.php';
					}

					$find[] = 'taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
					$find[] = CIVI()->template_path() . 'taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
					$find[] = 'taxonomy-' . $term->taxonomy . '.php';
					$find[] = CIVI()->template_path() . 'taxonomy-' . $term->taxonomy . '.php';
					$find[] = $file;
					$find[] = CIVI()->template_path() . $file;
				} elseif (is_post_type_archive('service') || is_page('service') || (is_search() && isset($_GET['post_type']) && $_GET['post_type'] == 'service')) {
					$file = 'archive-service.php';
					$find[] = $file;
					$find[] = CIVI()->template_path() . $file;
				}
			}


			//Shortcode
			if (
				civi_page_shortcode('[civi_dashboard]') || civi_page_shortcode('[civi_candidate_dashboard]') || civi_page_shortcode('[civi_jobs]') || civi_page_shortcode('[civi_jobs_performance]')
				|| civi_page_shortcode('[civi_jobs_submit]') || civi_page_shortcode('[civi_applicants]') || civi_page_shortcode('[civi_candidates]')
				|| civi_page_shortcode('[civi_user_package]') || civi_page_shortcode('[civi_messages]')
				|| civi_page_shortcode('[civi_company]') || civi_page_shortcode('[civi_submit_company]')
				|| civi_page_shortcode('[civi_meetings]') || civi_page_shortcode('[civi_settings]') || civi_page_shortcode('[civi_candidate_settings]') || civi_page_shortcode('[civi_package]') || civi_page_shortcode('[civi_payment]')
				|| civi_page_shortcode('[civi_payment_completed]') || civi_page_shortcode('[civi_my_jobs]') || civi_page_shortcode('[civi_candidate_company]')
				|| civi_page_shortcode('[civi_candidate_profile]') || civi_page_shortcode('[civi_candidate_my_review]') || civi_page_shortcode('[civi_candidate_meetings]')
				|| civi_page_shortcode('[civi_submit_service]') || civi_page_shortcode('[civi_candidate_service]') || civi_page_shortcode('[civi_employer_service]') || civi_page_shortcode('[civi_candidate_user_package]') || civi_page_shortcode('[civi_candidate_package]')
				|| civi_page_shortcode('[civi_candidate_payment]') || civi_page_shortcode('[civi_service_payment]') || civi_page_shortcode('[civi_candidate_payment_completed]') || civi_page_shortcode('[civi_service_payment_completed]') || civi_page_shortcode('[woocommerce_checkout]')
			) {
				$file = 'page-dashboard.php';
				$find[] = $file;
				$find[] = CIVI()->template_path() . $file;
			}

			if ($file) {
				$template = locate_template(array_unique($find));
				if (!$template) {
					$template = CIVI_PLUGIN_DIR . 'templates/' . $file;
				}
			}

			return $template;
		}

		/**
		 * Register all of the hooks jobs
		 */
		private function template_jobs_hooks()
		{
			// Global
			add_action('civi_layout_wrapper_start', 'layout_wrapper_start');
			add_action('civi_layout_wrapper_end', 'layout_wrapper_end');
			add_action('civi_output_content_wrapper_start', 'output_content_wrapper_start');
			add_action('civi_output_content_wrapper_end', 'output_content_wrapper_end');
			add_action('civi_sidebar_jobs', 'sidebar_jobs');

			// Taxonomy Jobs & Categories
			$archive_city_layout_style = civi_get_option('archive_city_layout_style', 'layout-default');
			$layout = !empty($_GET['layout']) ? civi_clean(wp_unslash($_GET['layout'])) : '';
			if (!empty($layout)) {
				$archive_city_layout_style = $layout;
			}

			switch ($archive_city_layout_style) {
				case 'layout-list':

					add_action('civi_archive_jobs_before', 'archive_page_title', 5);
					add_action('civi_archive_jobs_before', 'archive_jobs_post', 5);

					add_action('civi_tax_categories_before', 'archive_page_title', 5);
					add_action('civi_tax_categories_before', 'archive_categories', 10);

					break;

				case 'layout-top-filter':

					add_action('civi_archive_jobs_before', 'archive_page_title', 5);
					add_action('civi_archive_jobs_before', 'archive_jobs_post', 5);

					add_action('civi_tax_categories_before', 'archive_page_title', 5);
					add_action('civi_tax_categories_before', 'archive_categories', 10);

					break;

				case 'layout-default':

					add_action('civi_archive_jobs_before', 'archive_page_title', 5);
					add_action('civi_archive_jobs_before', 'archive_information', 10);
					add_action('civi_archive_jobs_before', 'archive_categories', 15);
					add_action('civi_archive_jobs_before', 'archive_jobs_post', 20);

					add_action('civi_tax_categories_before', 'archive_page_title', 5);
					add_action('civi_tax_categories_before', 'archive_information', 10);
					add_action('civi_tax_categories_before', 'archive_categories', 20);
					break;

				default:
					# code...
					break;
			}

			add_action('civi_archive_map_filter', 'archive_map_filter');
			add_action('civi_archive_jobs_sidebar_filter', 'archive_jobs_sidebar_filter', 10, 2);
			add_action('civi_archive_jobs_top_filter', 'archive_jobs_top_filter', 10, 3);

			//Jobs details order default
			$jobs_details_order_default = array(
				'sort_order' => 'enable_sp_skills|enable_sp_gallery|enable_sp_description|enable_sp_map|enable_sp_video|enable_sp_insights',
				'enable_sp_head' => 'enable_sp_head',
				'enable_sp_insights' => 'enable_sp_insights',
				'enable_sp_description' => 'enable_sp_description',
				'enable_sp_skills' => 'enable_sp_skills',
				'enable_sp_gallery' => 'enable_sp_gallery',
				'enable_sp_video' => 'enable_sp_video',
				'enable_sp_map' => 'enable_sp_map',
			);

			$jobs_details_order = civi_get_option('jobs_details_order', $jobs_details_order_default);

			$skills_nb_order = $video_nb_order = $related_nb_order = $description_nb_order = $insights_nb_order = $map_nb_order = $thumbnail_nb_order = $head_nb_order = $apply_nb_order = $gallery_nb_order = 0;

			if (!empty($jobs_details_order)) {
				$jobs_details_sort_order = explode('|', $jobs_details_order['sort_order']);

				foreach ($jobs_details_sort_order as $key => $value) {
					switch ($value) {
						case 'enable_sp_skills':
							$skills_nb_order = $key;
							break;

						case 'enable_sp_description':
							$description_nb_order = $key;
							break;

						case 'enable_sp_insights':
							$insights_nb_order = $key;
							break;

						case 'enable_sp_map':
							$map_nb_order = $key;
							break;

						case 'enable_sp_video':
							$video_nb_order = $key;
							break;

						case 'enable_sp_head':
							$head_nb_order = $key;
							break;

						case 'enable_sp_gallery':
							$gallery_nb_order = $key;
							break;

						default:
							# code...
							break;
					}
				}
			}

			//Jobs details order sidebar
			$jobs_details_sidebar_order_default = array(
				'sort_order' => 'enable_sidebar_sp_apply|enable_sidebar_sp_insights|enable_sidebar_sp_related',
				'enable_sidebar_sp_apply' => 'enable_sidebar_sp_apply',
				'enable_sidebar_sp_insights' => 'enable_sidebar_sp_insights',
				'enable_sidebar_sp_company' => 'enable_sidebar_sp_company',
			);

			$jobs_details_sidebar_order = civi_get_option('jobs_details_sidebar_order', $jobs_details_sidebar_order_default);

			$apply_nb_sidebar_order = $insights_nb_sidebar_order = $company_nb_sidebar_order = 0;

			if (!empty($jobs_details_order)) {
				$jobs_details_sidebar_sort_order = explode('|', $jobs_details_sidebar_order['sort_order']);
				foreach ($jobs_details_sidebar_sort_order as $key => $value) {
					switch ($value) {
						case 'enable_sidebar_sp_apply':
							$apply_nb_sidebar_order = $key;
							break;

						case 'enable_sidebar_sp_insights':
							$insights_nb_sidebar_order = $key;
							break;

						case 'enable_sidebar_sp_company':
							$company_nb_sidebar_order = $key;
							break;
						default:
							# code...
							break;
					}
				}
			}

			//Type single jobs
			$type_single_jobs = 'type-1';
			if (!empty($_GET['type'])) {
				$type_single_jobs = civi_clean(wp_unslash($_GET['type']));
			} elseif (!empty($_GET['layout']) && preg_match('/^type-[123]$/', $_GET['layout'])) {
				$type_single_jobs = civi_clean(wp_unslash($_GET['layout']));
			}

			$enable_single_jobs_related = civi_get_option('enable_single_jobs_related', '1');
			$enable_single_jobs_apply = civi_get_option('enable_single_jobs_apply', '1');

			$single_jobs_style = civi_get_option('single_jobs_style');
			if (!empty($_GET['style'])) {
				$single_jobs_style = civi_clean(wp_unslash($_GET['style']));
			} elseif (!empty($_GET['layout']) && in_array($_GET['layout'], array('cover-img', 'no-image', 'large-cover-img'))) {
				$single_jobs_style = civi_clean(wp_unslash($_GET['layout']));
			}

			$content_jobs = civi_get_option('archive_jobs_layout', 'layout-list');
			if (!empty($_GET['layout']) && preg_match('/^layout-(full|list|grid)$/', $_GET['layout'])) {
				$content_jobs = civi_clean(wp_unslash($_GET['layout']));
			}
			if (!empty($_POST['layout'])) {
				$content_jobs = civi_clean(wp_unslash($_POST['layout']));
			}

			$render_custom_field_jobs = civi_render_custom_field('jobs');

			if ($content_jobs == 'layout-full') {
				if ($single_jobs_style != 'no-image') {
					add_action('civi_preview_jobs_before_summary', 'single_jobs_thumbnail', 0);
				}
				if (in_array('enable_sp_head', $jobs_details_order)) {
					add_action('civi_preview_jobs_before_summary', 'single_jobs_head', $head_nb_order);
				}

				if (in_array('enable_sp_insights', $jobs_details_order)) {
					add_action('civi_preview_jobs_summary', 'single_jobs_insights', $insights_nb_order);
				}

				if (in_array('enable_sp_skills', $jobs_details_order)) {
					add_action('civi_preview_jobs_summary', 'single_jobs_skills', $skills_nb_order);
				}

				if (in_array('enable_sp_description', $jobs_details_order)) {
					add_action('civi_preview_jobs_summary', 'single_jobs_description', $description_nb_order);
				}

				if (in_array('enable_sp_gallery', $jobs_details_order)) {
					add_action('civi_preview_jobs_summary', 'gallery_jobs', $gallery_nb_order);
				}

				if (in_array('enable_sp_video', $jobs_details_order)) {
					add_action('civi_preview_jobs_summary', 'single_jobs_video', $video_nb_order);
				}

				if (in_array('enable_sp_map', $jobs_details_order)) {
					add_action('civi_preview_jobs_summary', 'single_jobs_map', $map_nb_order);
				}
			}

			switch ($type_single_jobs) {
				case 'type-1':

					if ($single_jobs_style == 'cover-img') {
						add_action('civi_single_jobs_after_summary', 'single_jobs_thumbnail');
					} elseif ($single_jobs_style == 'no-image') {
						// Type 2 - No Image: Don't show thumbnail
					} elseif ($single_jobs_style == 'large-cover-img') {
						add_action('civi_single_jobs_before', 'single_jobs_thumbnail');
					} else {
						// Default: show thumbnail after summary
						add_action('civi_single_jobs_after_summary', 'single_jobs_thumbnail');
					}

					if (in_array('enable_sp_head', $jobs_details_order)) {
						add_action('civi_single_jobs_summary', 'single_jobs_head', $head_nb_order);
					}

					if (in_array('enable_sp_insights', $jobs_details_order)) {
						add_action('civi_single_jobs_summary', 'single_jobs_insights', $insights_nb_order);
					}

					if (in_array('enable_sp_skills', $jobs_details_order)) {
						add_action('civi_single_jobs_summary', 'single_jobs_skills', $skills_nb_order);
					}

					if (in_array('enable_sp_description', $jobs_details_order)) {
						add_action('civi_single_jobs_summary', 'single_jobs_description', $description_nb_order);
					}

					if (in_array('enable_sp_video', $jobs_details_order)) {
						add_action('civi_single_jobs_summary', 'single_jobs_video', $video_nb_order);
					}

					if (in_array('enable_sp_map', $jobs_details_order)) {
						add_action('civi_single_jobs_summary', 'single_jobs_map', $map_nb_order);
					}

					if (in_array('enable_sp_gallery', $jobs_details_order)) {
						add_action('civi_single_jobs_summary', 'gallery_jobs', $gallery_nb_order);
					}

					if (count($render_custom_field_jobs) > 0) {
						add_action('civi_single_jobs_summary', 'single_jobs_additional');
					}

					if ($enable_single_jobs_apply) {
						add_action('civi_after_content_single_jobs_summary', 'single_jobs_apply');
					}

					if ($enable_single_jobs_related) {
						add_action('civi_after_content_single_jobs_summary', 'single_jobs_related');
					}

					//Sidebar
					if (in_array('enable_sidebar_sp_apply', $jobs_details_sidebar_order)) {
						add_action('civi_single_jobs_sidebar', 'single_jobs_sidebar_apply', $apply_nb_sidebar_order);
					}
					if (in_array('enable_sidebar_sp_insights', $jobs_details_sidebar_order)) {
						add_action('civi_single_jobs_sidebar', 'single_jobs_sidebar_insights', $insights_nb_sidebar_order);
					}
					if (in_array('enable_sidebar_sp_company', $jobs_details_sidebar_order)) {
						add_action('civi_single_jobs_sidebar', 'single_jobs_sidebar_company', $company_nb_sidebar_order);
					}

					break;

				case 'type-2':

					if ($single_jobs_style == 'cover-img') {
						add_action('civi_single_jobs_after_summary', 'single_jobs_thumbnail');
					} elseif ($single_jobs_style == 'no-image') {
						// Type 2 - No Image: Don't show thumbnail
					} elseif ($single_jobs_style == 'large-cover-img') {
						add_action('civi_single_jobs_before', 'single_jobs_thumbnail');
					} else {
						// Default: show thumbnail after summary
						add_action('civi_single_jobs_after_summary', 'single_jobs_thumbnail');
					}

					if (in_array('enable_sp_head', $jobs_details_order)) {
						add_action('civi_single_jobs_summary', 'single_jobs_head', $head_nb_order);
					}

					if (in_array('enable_sp_skills', $jobs_details_order)) {
						add_action('civi_single_jobs_summary', 'single_jobs_skills', $skills_nb_order);
					}

					if (in_array('enable_sp_description', $jobs_details_order)) {
						add_action('civi_single_jobs_summary', 'single_jobs_description', $description_nb_order);
					}

					if (count($render_custom_field_jobs) > 0) {
						add_action('civi_single_jobs_summary', 'single_jobs_additional');
					}

					if ($enable_single_jobs_apply) {
						add_action('civi_after_content_single_jobs_summary', 'single_jobs_apply');
					}

					if ($enable_single_jobs_related) {
						add_action('civi_after_content_single_jobs_summary', 'single_jobs_related');
					}

					//Sidebar
					if (in_array('enable_sidebar_sp_apply', $jobs_details_sidebar_order)) {
						add_action('civi_single_jobs_sidebar', 'single_jobs_sidebar_apply', $apply_nb_sidebar_order);
					}
					add_action('civi_single_jobs_sidebar', 'single_jobs_sidebar_insights', $insights_nb_sidebar_order);
					if (in_array('enable_sidebar_sp_company', $jobs_details_sidebar_order)) {
						add_action('civi_single_jobs_sidebar', 'single_jobs_sidebar_company', $company_nb_sidebar_order);
					}

					break;
				case 'type-3':

					if ($single_jobs_style == 'cover-img') {
						add_action('civi_single_jobs_after_summary', 'single_jobs_thumbnail');
					} elseif ($single_jobs_style == 'no-image') {
						// Type 2 - No Image: Don't show thumbnail
					} elseif ($single_jobs_style == 'large-cover-img') {
						add_action('civi_single_jobs_before', 'single_jobs_thumbnail');
					} else {
						// Default: show thumbnail after summary
						add_action('civi_single_jobs_after_summary', 'single_jobs_thumbnail');
					}

					if (in_array('enable_sp_head', $jobs_details_order)) {
						add_action('civi_single_jobs_summary', 'single_jobs_head', $head_nb_order);
					}

					if (in_array('enable_sp_insights', $jobs_details_order)) {
						add_action('civi_single_jobs_summary', 'single_jobs_insights', $insights_nb_order);
					}

					if (in_array('enable_sp_skills', $jobs_details_order)) {
						add_action('civi_single_jobs_summary', 'single_jobs_skills', $skills_nb_order);
					}

					if (in_array('enable_sp_description', $jobs_details_order)) {
						add_action('civi_single_jobs_summary', 'single_jobs_description', $description_nb_order);
					}

					add_action('civi_single_jobs_summary', 'single_jobs_video', $video_nb_order);

					add_action('civi_single_jobs_summary', 'single_jobs_map', $map_nb_order);

					add_action('civi_single_jobs_summary', 'gallery_jobs', $gallery_nb_order);

					if (count($render_custom_field_jobs) > 0) {
						add_action('civi_single_jobs_summary', 'single_jobs_additional');
					}

					if ($enable_single_jobs_apply) {
						add_action('civi_after_content_single_jobs_summary', 'single_jobs_apply');
					}

					if ($enable_single_jobs_related) {
						add_action('civi_after_content_single_jobs_summary', 'single_jobs_related');
					}

					//Sidebar
					if (in_array('enable_sidebar_sp_apply', $jobs_details_sidebar_order)) {
						add_action('civi_single_jobs_sidebar', 'single_jobs_sidebar_apply', $apply_nb_sidebar_order);
					}
					if (in_array('enable_sidebar_sp_company', $jobs_details_sidebar_order)) {
						add_action('civi_single_jobs_sidebar', 'single_jobs_sidebar_company', $company_nb_sidebar_order);
					}

					break;

				default:
					# code...
					break;
			}
		}

		/**
		 * Register all of the hooks company
		 */
		private function template_company_hooks()
		{
			// Global
			add_action('civi_layout_wrapper_start', 'layout_wrapper_start');
			add_action('civi_layout_wrapper_end', 'layout_wrapper_end');
			add_action('civi_output_content_wrapper_start', 'output_content_wrapper_start');
			add_action('civi_output_content_wrapper_end', 'output_content_wrapper_end');
			add_action('civi_sidebar_company', 'sidebar_company');

			add_action('civi_archive_company_sidebar_filter', 'archive_company_sidebar_filter', 10, 3);
			add_action('civi_archive_company_top_filter', 'archive_company_top_filter', 10, 3);

			//Jobs details order default
			$company_details_order_default = array(
				'sort_order' => 'enable_sp_head|enable_sp_overview|enable_sp_gallery',
				'enable_sp_head' => 'enable_sp_head',
				'enable_sp_overview' => 'enable_sp_overview',
				'enable_sp_gallery' => 'enable_sp_gallery',
				'enable_sp_video' => 'enable_sp_video',
			);

			$company_details_order = civi_get_option('company_details_order', $company_details_order_default);

			$head_nb_order = $video_nb_order = $overview_nb_order = $gallery_nb_order = 0;

			if (!empty($company_details_order)) {
				$company_details_sort_order = explode('|', $company_details_order['sort_order']);

				foreach ($company_details_sort_order as $key => $value) {
					switch ($value) {
						case 'enable_sp_head':
							$head_nb_order = $key;
							break;

						case 'enable_sp_overview':
							$overview_nb_order = $key;
							break;

						case 'enable_sp_gallery':
							$gallery_nb_order = $key;
							break;

						case 'enable_sp_video':
							$video_nb_order = $key;
							break;

						default:
							# code...
							break;
					}
				}
			}

			//Company details order sidebar
			$company_details_sidebar_order_default = array(
				'sort_order' => 'enable_sidebar_sp_info|enable_sidebar_sp_location',
				'enable_sidebar_sp_info' => 'enable_sidebar_sp_info',
				'enable_sidebar_sp_location' => 'enable_sidebar_sp_location',
			);

			$company_details_sidebar_order = civi_get_option('company_details_sidebar_order', $company_details_sidebar_order_default);

			$info_nb_sidebar_order = $location_nb_sidebar_order = 0;

			$render_custom_field_company = civi_render_custom_field('company');

			if (!empty($company_details_order)) {
				$company_details_sidebar_sort_order = explode('|', $company_details_sidebar_order['sort_order']);
				foreach ($company_details_sidebar_sort_order as $key => $value) {
					switch ($value) {
						case 'enable_sidebar_sp_info':
							$info_nb_sidebar_order = $key;
							break;

						case 'enable_sidebar_sp_location':
							$location_nb_sidebar_order = $key;
							break;

						default:
							# code...
							break;
					}
				}
			}

			//Type single company
			$type_single_company = 'type-1';
			$enable_single_company_related = civi_get_option('enable_single_company_related', '1');
			$enable_single_company_review = civi_get_option('enable_single_company_review', '1');

			// Style for thumbnail: cover-img, no-image, large-cover-img
			$single_company_style = civi_get_option('single_company_style');
			// Support ?style=large-cover-img or ?layout=large-cover-img (or cover-img, no-image) for style
			if (!empty($_GET['style'])) {
				$single_company_style = civi_clean(wp_unslash($_GET['style']));
			} elseif (!empty($_GET['layout']) && in_array($_GET['layout'], array('cover-img', 'no-image', 'large-cover-img'))) {
				$single_company_style = civi_clean(wp_unslash($_GET['layout']));
			}

			switch ($type_single_company) {
				case 'type-1':

					if ($single_company_style == 'cover-img') {
						add_action('civi_single_company_after_summary', 'single_company_thumbnail');
					} elseif ($single_company_style == 'no-image') {
						// Type 2 - No Image: Don't show thumbnail
					} elseif ($single_company_style == 'large-cover-img') {
						add_action('civi_single_company_before', 'single_company_thumbnail', 10);
					} else {
						// Default: show thumbnail before content
						add_action('civi_single_company_before', 'single_company_thumbnail', 10);
					}

					if (in_array('enable_sp_head', $company_details_order)) {
						add_action('civi_single_company_summary', 'single_company_head', $head_nb_order);
					}

					if (in_array('enable_sp_overview', $company_details_order)) {
						add_action('civi_single_company_summary', 'single_company_overview', $overview_nb_order);
					}

					if (in_array('enable_sp_gallery', $company_details_order)) {
						add_action('civi_single_company_summary', 'single_company_gallery', $gallery_nb_order);
					}

					if (in_array('enable_sp_video', $company_details_order)) {
						add_action('civi_single_company_summary', 'single_company_video', $video_nb_order);
					}

					if (count($render_custom_field_company) > 0) {
						add_action('civi_single_company_summary', 'single_company_additional');
					}

					if ($enable_single_company_related) {
						add_action('civi_after_content_single_company_summary', 'single_company_related');
					}

					if ($enable_single_company_review) {
						add_action('civi_after_content_single_company_summary', 'single_company_review');
					}

					//Sidebar
					if (in_array('enable_sidebar_sp_info', $company_details_sidebar_order)) {
						add_action('civi_single_company_sidebar', 'single_company_sidebar_info', $info_nb_sidebar_order);
					}
					if (in_array('enable_sidebar_sp_location', $company_details_sidebar_order)) {
						add_action('civi_single_company_sidebar', 'single_company_sidebar_location', $location_nb_sidebar_order);
					}

					break;

				default:
					# code...
					break;
			}
		}

		/**
		 * Register all of the hooks candidate
		 */
		private function template_candidate_hooks()
		{
			// Global
			add_action('civi_layout_wrapper_start', 'layout_wrapper_start');
			add_action('civi_layout_wrapper_end', 'layout_wrapper_end');
			add_action('civi_output_content_wrapper_start', 'output_content_wrapper_start');
			add_action('civi_output_content_wrapper_end', 'output_content_wrapper_end');
			add_action('civi_candidate_sidebar', 'sidebar_candidate');

			// Candidate Search and Filter page
			add_action('civi_archive_candidate_sidebar_filter', 'archive_candidate_sidebar_filter', 10, 3);
			add_action('civi_archive_candidate_top_filter', 'archive_candidate_top_filter', 10, 3);

			// Candidate details order default
			$candidates_details_order_default = array(
				'sort_order' => 'enable_sp_video|enable_sp_skills|enable_sp_experience|enable_sp_education|enable_sp_projects|enable_sp_awards',
				'enable_sp_video' => 'enable_sp_video',
				'enable_sp_skills' => 'enable_sp_skills',
				'enable_sp_experience' => 'enable_sp_experience',
				'enable_sp_education' => 'enable_sp_education',
				'enable_sp_projects' => 'enable_sp_projects',
				'enable_sp_awards' => 'enable_sp_awards',
			);

			$candidates_details_order = civi_get_option('candidates_details_order', $candidates_details_order_default);

			$video_nb_order = $skills_nb_order = $experience_nb_order = $education_nb_order = $projects_nb_order = $awards_nb_order = 0;

			if (!empty($candidates_details_order)) {
				$candidate_details_sort_order = explode('|', $candidates_details_order['sort_order']);

				foreach ($candidate_details_sort_order as $key => $value) {
					switch ($value) {
						case 'enable_sp_video':
							$video_nb_order = $key;
							break;
						case 'enable_sp_skills':
							$skills_nb_order = $key;
							break;
						case 'enable_sp_experience':
							$experience_nb_order = $key;
							break;
						case 'enable_sp_education':
							$education_nb_order = $key;
							break;
						case 'enable_sp_projects':
							$projects_nb_order = $key;
							break;
						case 'enable_sp_awards':
							$awards_nb_order = $key;
							break;

						default:
							# code...
							break;
					}
				}
			}

			//candidate details order sidebar
			$candidate_details_sidebar_order_default = array(
				'sort_order' => 'enable_sidebar_sp_info|enable_sidebar_sp_location',
				'enable_sidebar_sp_info' => 'enable_sidebar_sp_info',
				'enable_sidebar_sp_location' => 'enable_sidebar_sp_location',
			);

			$candidate_details_sidebar_order = civi_get_option('candidate_details_sidebar_order', $candidate_details_sidebar_order_default);

			$info_nb_sidebar_order = $location_nb_sidebar_order = 0;

			if (!empty($candidates_details_order)) {
				$candidate_details_sidebar_sort_order = explode('|', $candidate_details_sidebar_order['sort_order']);
				foreach ($candidate_details_sidebar_sort_order as $key => $value) {
					switch ($value) {
						case 'enable_sidebar_sp_info':
							$info_nb_sidebar_order = $key;
							break;

						case 'enable_sidebar_sp_location':
							$location_nb_sidebar_order = $key;
							break;

						default:
							# code...
							break;
					}
				}
			}

			//Type single candidate
			$type_single_candidate = 'type-1';
			$enable_single_candidate_review = civi_get_option('enable_single_candidate_review', '1');
			$enable_single_candidate_service = civi_get_option('single_candidate_style');

			// Style for thumbnail: cover-img, no-image, large-cover-img
			$single_candidate_style = civi_get_option('single_candidate_style');
			// Support ?style=large-cover-img or ?layout=large-cover-img (or cover-img, no-image) for style
			if (!empty($_GET['style'])) {
				$single_candidate_style = civi_clean(wp_unslash($_GET['style']));
			} elseif (!empty($_GET['layout']) && in_array($_GET['layout'], array('cover-img', 'no-image', 'large-cover-img'))) {
				$single_candidate_style = civi_clean(wp_unslash($_GET['layout']));
			}
			$custom_field_candidate = civi_render_custom_field('candidate');

			switch ($type_single_candidate) {
				case 'type-1':
					if ($single_candidate_style == 'cover-img') {
						add_action('civi_single_candidate_after_summary', 'single_candidate_thumbnail');
					} elseif ($single_candidate_style == 'no-image') {
						// Type 2 - No Image: Don't show thumbnail
					} elseif ($single_candidate_style == 'large-cover-img') {
						add_action('civi_single_candidate_before', 'single_candidate_thumbnail');
					} else {
						// Default: show thumbnail before content
						add_action('civi_single_candidate_before', 'single_candidate_thumbnail');
					}

					add_action('civi_single_candidate_summary', 'single_candidate_head');
					add_action('civi_single_candidate_summary', 'single_candidate_about_me');
					add_action('civi_single_candidate_summary', 'single_candidate_photos');

					if (in_array('enable_sp_video', $candidates_details_order)) {
						add_action('civi_after_content_single_candidate_summary', 'single_candidate_video', $video_nb_order);
					}
					if (in_array('enable_sp_skills', $candidates_details_order)) {
						add_action('civi_after_content_single_candidate_summary', 'single_candidate_skills', $skills_nb_order);
					}
					if (in_array('enable_sp_experience', $candidates_details_order)) {
						add_action('civi_after_content_single_candidate_summary', 'single_candidate_experience', $experience_nb_order);
					}
					if (in_array('enable_sp_education', $candidates_details_order)) {
						add_action('civi_after_content_single_candidate_summary', 'single_candidate_education', $education_nb_order);
					}
					if (in_array('enable_sp_projects', $candidates_details_order)) {
						add_action('civi_after_content_single_candidate_summary', 'single_candidate_projects', $projects_nb_order);
					}
					if (in_array('enable_sp_awards', $candidates_details_order)) {
						add_action('civi_after_content_single_candidate_summary', 'single_candidate_awards', $awards_nb_order);
					}

					if (count($custom_field_candidate) > 0) {
						add_action('civi_after_content_single_candidate_summary', 'single_candidate_additional');
					}

					if ($enable_single_candidate_service) {
						add_action('civi_after_content_single_candidate_summary', 'single_candidate_service');
					}

					if ($enable_single_candidate_review) {
						add_action('civi_after_content_single_candidate_summary', 'single_candidate_review');
					}

					//Sidebar
					if (in_array('enable_sidebar_sp_info', $candidate_details_sidebar_order)) {
						add_action('civi_single_candidate_sidebar', 'single_candidate_sidebar_info', $info_nb_sidebar_order);
					}
					if (in_array('enable_sidebar_sp_location', $candidate_details_sidebar_order)) {
						add_action('civi_single_candidate_sidebar', 'single_candidate_sidebar_location', $location_nb_sidebar_order);
					}

					break;

				default:
					# code...
					break;
			}
		}

		/**
		 * Register all of the hooks company
		 */
		private function template_service_hooks()
		{
			// Global
			add_action('civi_layout_wrapper_start', 'layout_wrapper_start');
			add_action('civi_layout_wrapper_end', 'layout_wrapper_end');
			add_action('civi_output_content_wrapper_start', 'output_content_wrapper_start');
			add_action('civi_output_content_wrapper_end', 'output_content_wrapper_end');
			add_action('civi_sidebar_service', 'sidebar_service');

			add_action('civi_archive_service_sidebar_filter', 'archive_service_sidebar_filter', 10, 3);
			add_action('civi_archive_service_top_filter', 'archive_service_top_filter', 10, 3);

			// Service details order default
			$services_details_order_default = array(
				'sort_order' => 'enable_sp_gallery|enable_sp_descriptions|enable_sp_skills|enable_sp_location|enable_sp_faq|enable_sp_review',
				'enable_sp_gallery' => 'enable_sp_gallery',
				'enable_sp_descriptions' => 'enable_sp_descriptions',
				'enable_sp_skills' => 'enable_sp_skills',
				'enable_sp_location' => 'enable_sp_location',
				'enable_sp_faq' => 'enable_sp_faq',
				'enable_sp_review' => 'enable_sp_review',
			);

			$services_details_order = civi_get_option('services_details_order', $services_details_order_default);

			if (!is_array($services_details_order)) {
				$services_details_order = $services_details_order_default;
			}

			$service_details_sort_order = [];
			if (!empty($services_details_order['sort_order']) && is_string($services_details_order['sort_order'])) {
				$service_details_sort_order = explode('|', $services_details_order['sort_order']);
			}

			$gallery_nb_order = $descriptions_nb_order = $skills_nb_order = $location_nb_order = $faq_nb_order = $video_nb_order = $review_nb_order = 0;
			foreach ($service_details_sort_order as $key => $value) {
				switch ($value) {
					case 'enable_sp_gallery':
						$gallery_nb_order = $key;
						break;
					case 'enable_sp_descriptions':
						$descriptions_nb_order = $key;
						break;
					case 'enable_sp_skills':
						$skills_nb_order = $key;
						break;
					case 'enable_sp_location':
						$location_nb_order = $key;
						break;
					case 'enable_sp_faq':
						$faq_nb_order = $key;
						break;
					case 'enable_sp_video':
						$video_nb_order = $key;
						break;
					case 'enable_sp_review':
						$review_nb_order = $key;
						break;
					default:
						break;
				}
			}

			// Service details order sidebar
			$service_details_sidebar_order_default = array(
				'sort_order' => 'enable_sidebar_sp_package|enable_sidebar_sp_info',
				'enable_sidebar_sp_package' => 'enable_sidebar_sp_package',
				'enable_sidebar_sp_info' => 'enable_sidebar_sp_info',
			);

			$service_details_sidebar_order = civi_get_option('service_details_sidebar_order', $service_details_sidebar_order_default);
			if (!is_array($service_details_sidebar_order)) {
				$service_details_sidebar_order = $service_details_sidebar_order_default;
			}

			$service_details_sidebar_sort_order = [];
			if (!empty($service_details_sidebar_order['sort_order']) && is_string($service_details_sidebar_order['sort_order'])) {
				$service_details_sidebar_sort_order = explode('|', $service_details_sidebar_order['sort_order']);
			}

			$package_nb_sidebar_order = $info_nb_sidebar_order = 0;
			foreach ($service_details_sidebar_sort_order as $key => $value) {
				switch ($value) {
					case 'enable_sidebar_sp_package':
						$package_nb_sidebar_order = $key;
						break;
					case 'enable_sidebar_sp_info':
						$info_nb_sidebar_order = $key;
						break;
					default:
						break;
				}
			}

			// Type single service
			$type_single_service = 'type-1';
			$enable_single_service_related = civi_get_option('enable_single_service_related');
			$enable_single_service_review = civi_get_option('enable_single_service_review', '1');
			// Note: Service doesn't have thumbnail style options, this is kept for future use
			$single_service_style = civi_get_option('single_service_style', '');

			switch ($type_single_service) {
				case 'type-1':

					add_action('civi_single_service_before', 'single_service_head');

					if (in_array('enable_sp_gallery', $service_details_sort_order)) {
						add_action('civi_single_service_summary', 'single_service_gallery', $gallery_nb_order);
					}
					if (in_array('enable_sp_descriptions', $service_details_sort_order)) {
						add_action('civi_single_service_summary', 'single_service_descriptions', $descriptions_nb_order);
					}
					if (in_array('enable_sp_skills', $service_details_sort_order)) {
						add_action('civi_single_service_summary', 'single_service_skills', $skills_nb_order);
					}
					if (in_array('enable_sp_location', $service_details_sort_order)) {
						add_action('civi_single_service_summary', 'single_service_location', $location_nb_order);
					}
					if (in_array('enable_sp_faq', $service_details_sort_order)) {
						add_action('civi_single_service_summary', 'single_service_faq', $faq_nb_order);
					}
					if (in_array('enable_sp_video', $service_details_sort_order)) {
						add_action('civi_single_service_summary', 'single_service_video', $video_nb_order);
					}
					if ($enable_single_service_review) {
						add_action('civi_single_service_summary', 'single_service_review');
					}
					if ($enable_single_service_related) {
						add_action('civi_after_content_single_service_summary', 'single_service_related');
					}

					// Sidebar
					if (in_array('enable_sidebar_sp_package', $service_details_sidebar_sort_order)) {
						add_action('civi_single_service_sidebar', 'single_service_sidebar_package', $package_nb_sidebar_order);
					}
					if (in_array('enable_sidebar_sp_info', $service_details_sidebar_sort_order)) {
						add_action('civi_single_service_sidebar', 'single_service_sidebar_info', $info_nb_sidebar_order);
					}

					break;

				default:
					break;
			}
		}

		public function send_meeting_notification()
		{
			global $current_user;
			$user_id = $current_user->ID;
			$args_upcoming = array(
				'post_type' => 'meetings',
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'author' => $user_id,
				'orderby' => 'date',
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key' => CIVI_METABOX_PREFIX . 'meeting_status',
						'value' => 'completed',
						'compare' => '!='
					)
				),
			);
			$data_upcoming = new WP_Query($args_upcoming);
			if ($data_upcoming->have_posts()) {
				while ($data_upcoming->have_posts()) : $data_upcoming->the_post();
					$meeting_id = get_the_ID();
					$meeting_date = get_post_meta($meeting_id, CIVI_METABOX_PREFIX . 'meeting_date', true);
					$meeting_time = get_post_meta($meeting_id, CIVI_METABOX_PREFIX . 'meeting_time', true);
					$meeting_with = get_post_meta($meeting_id, CIVI_METABOX_PREFIX . 'meeting_with', true);
					$current_date = date('Y-m-d');

					$email_sent = get_post_meta($meeting_id, 'meeting_email_sent', true);

					if ($email_sent) {
						return;
					}

					$user = get_user_by('login', $meeting_with);
					$user_email = '';
					if (!empty($user)) {
						$user_email = $user->user_email;
					}
					$date = new DateTime($meeting_date);
					$date->modify('-1 day');
					$previousDay = $date->format('Y-m-d');
					$meeting_date_time = $meeting_date . ' ' . $meeting_time;

					$args_mail = array(
						'website_url' =>  get_option('siteurl'),
						'jobs_meetings' => get_the_title($meeting_id),
						'date_time' => $meeting_date_time,
					);
					if (strtotime($meeting_date) > strtotime($current_date) && strtotime($previousDay) == strtotime($current_date)) {
						civi_send_email($user_email, 'mail_notification_meetings', $args_mail);
						wp_clear_scheduled_hook('send_meeting_notification', array($meeting_id));

						update_post_meta($meeting_id, 'meeting_email_sent', true);
					}
				endwhile;
			}
		}

		public function schedule_meeting_notification($meeting_id, $meeting_time)
		{
			$email_time = strtotime('-1 day', strtotime($meeting_time));
			wp_schedule_single_event($email_time, 'send_meeting_notification', array($meeting_id));
		}

		public function setup_meeting_notifications()
		{
			global $current_user;
			$user_id = $current_user->ID;
			$args_upcoming = array(
				'post_type' => 'meetings',
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'author' => $user_id,
				'orderby' => 'date',
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key' => CIVI_METABOX_PREFIX . 'meeting_status',
						'value' => 'completed',
						'compare' => '!='
					)
				),
			);
			$data_upcoming = new WP_Query($args_upcoming);
			if ($data_upcoming->have_posts()) {
				while ($data_upcoming->have_posts()) : $data_upcoming->the_post();
					$meeting_id = get_the_ID();
					$meeting_time = get_post_meta($meeting_id, CIVI_METABOX_PREFIX . 'meeting_time', true);
					$this->schedule_meeting_notification($meeting_id, $meeting_time);
				endwhile;
			}
		}

		/**
		 * Form apply jobs
		 */
		public function civi_form_apply_jobs($jobs_id)
		{
			if (!empty($jobs_id)) {
				$jobs_select_apply = get_post_meta($jobs_id, CIVI_METABOX_PREFIX . 'jobs_select_apply');
				$jobs_select_apply = isset($jobs_select_apply) ? $jobs_select_apply[0] : '';
				if ($jobs_select_apply == 'email') {
					civi_get_template('jobs/apply/gmail.php');
				} elseif ($jobs_select_apply == 'internal') {
					civi_get_template('jobs/apply/internal.php');
				} else {
					civi_get_template('jobs/apply/call-to.php', array(
						'jobs_id' => $jobs_id,
					));
				}
			}
		}

		/**
		 * Form reschedule meeting
		 */
		public function civi_form_reschedule_meeting()
		{
			$applicants = civi_get_option('civi_applicants_page_id');
			$meetings_employer = civi_get_option('civi_meetings_page_id');
			$meetings_candidate = civi_get_option('civi_candidate_meetings_page_id');
			if (is_page($applicants) || is_page($meetings_employer) || is_page($meetings_candidate)) :
				civi_get_template('jobs/meeting/reschedule.php');
			endif;
		}

		/**
		 * Form meetings popup
		 */
		public function civi_form_setting_meetings()
		{
			global $current_user;
			$user_id = $current_user->ID;
			$meetings = civi_get_option('civi_meetings_page_id');
			$zoom_link = get_user_meta($user_id, CIVI_METABOX_PREFIX . 'metting_zoom_link', true);
			$zoom_link = isset($zoom_link) ? $zoom_link : '';
			$zoom_pw = get_user_meta($user_id, CIVI_METABOX_PREFIX . 'metting_zoom_pw', true);
			$zoom_pw = isset($zoom_pw) ? $zoom_pw : '';
			if (is_page($meetings)) : ?>
				<div class="form-popup civi-form-meetings" id="civi-form-setting-meetings">
					<div class="bg-overlay"></div>
					<form class="meetings-popup custom-scrollbar">
						<a href="#" class="btn-close"><i class="far fa-times"></i></a>
						<h5><?php esc_html_e('Zoom Settings', 'civi-framework'); ?></h5>
						<div class="row">
							<div class="form-group col-md-12">
								<label for="zoomlink"><?php esc_html_e('Personal Link', 'civi-framework'); ?>
									<sup>*</sup></label>
								<input type="url" id="zoomlink" value="<?php esc_html_e($zoom_link) ?>" name="zoomlink" placeholder="<?php echo esc_attr__('Enter Link', 'civi-framework') ?>" required>
							</div>
							<div class="form-group col-md-12">
								<label for="zoompw"><?php esc_html_e('Password', 'civi-framework'); ?>
									<sup>*</sup></label>
								<input class="form-control" type="password" id="zoompw" name="zoompw" value="<?php esc_html_e($zoom_pw) ?>" placeholder="<?php esc_attr_e('Enter password', 'civi-framework'); ?>" required>
								<span toggle="#zoompw" class="fa fa-fw fa-eye field-icon civi-toggle-password"></span>
							</div>
						</div>
						<div class="button-wrapper">
							<a href="#" class="civi-button button-outline button-block  button-cancel"><?php esc_html_e('Cancel', 'civi-framework'); ?></a>
							<button class="civi-button button-block" id="btn-saved-meetings" type="submit">
								<?php esc_html_e('Saved settings', 'civi-framework'); ?>
								<span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
							</button>
						</div>
					</form>
				</div>
			<?php endif;
		}

		/**
		 * Form messages popup
		 */
		public function civi_form_setting_messages()
		{
			if (is_single() && ((get_post_type() == 'candidate') || (get_post_type() == 'company') || get_post_type() == 'jobs' || get_post_type() == 'service') || is_post_type_archive('jobs')) :
				wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'messages-dashboard');
			?>
				<div class="form-popup civi-form-popup" id="form-messages-popup">
					<div class="bg-overlay"></div>
					<form class="messages-popup inner-popup custom-scrollbar">
						<a href="#" class="btn-close"><i class="far fa-times"></i></a>
						<h5>
							<?php esc_html_e('Send message', 'civi-framework'); ?>
						</h5>
						<div class="row">
							<div class="form-group col-md-12">
								<label for="title_message"><?php esc_html_e('Title', 'civi-framework'); ?>
									<sup>*</sup></label>
								<input type="text" id="title_message" value="" name="title_message" placeholder="<?php echo esc_attr__('Enter Title', 'civi-framework') ?>" required>
							</div>
							<div class="form-group col-md-12">
								<label><?php esc_html_e('Content', 'civi-framework') ?><sup> *</sup></label>
								<textarea name="content_message" cols="30" rows="7" placeholder="<?php esc_attr_e('Enter Content', 'civi-framework'); ?>"></textarea>
							</div>
						</div>
						<div class="civi-message-error"></div>
						<div class="button-wrapper">
							<a href="#" class="civi-button button-outline button-block button-cancel"><?php esc_html_e('Cancel', 'civi-framework'); ?></a>
							<button class="civi-button button-block" id="btn-send-messages" type="submit">
								<?php esc_html_e('Send Messages', 'civi-framework'); ?>
								<span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
							</button>
						</div>
					</form>
				</div>
			<?php endif;
		}

		/**
		 * Form messages applicants
		 */
		public function civi_form_mess_applicants()
		{
			$applicants = civi_get_option('civi_applicants_page_id');
			$jobs_dashboard = civi_get_option('civi_jobs_dashboard_page_id');
			if (is_page($applicants) || is_page($jobs_dashboard)) :
			?>
				<div class="form-popup civi-form-popup" id="form-messages-applicants">
					<div class="bg-overlay"></div>
					<form class="messages-popup inner-popup custom-scrollbar">
						<a href="#" class="btn-close"><i class="far fa-times"></i></a>
						<h5>
							<?php esc_html_e('Content Message', 'civi-framework'); ?>
						</h5>
						<div class="content-mess"></div>
						<div class="button-wrapper">
							<a href="#" class="civi-button button-block btn-realy-mess">
								<?php esc_html_e('Reply messages', 'civi-framework'); ?>
								<span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
							</a>
							<a href="#" class="civi-button button-block button-outline-accent button-cancel"><?php esc_html_e('Cancel messages', 'civi-framework'); ?></a>
						</div>
						<input type="hidden" name="link_mess" value="<?php echo civi_get_permalink('messages'); ?>">
					</form>
				</div>
			<?php
			endif;
		}

		/**
		 * Form setting deactive
		 */
		public function civi_form_setting_deactive()
		{
			global $current_user;
			$user_id = $current_user->ID;
			$settings_employer = civi_get_option('civi_settings_page_id');
			$settings_candidate = civi_get_option('civi_candidate_settings_page_id');
			$nonce_url = wp_nonce_url(get_site_url() . '?action=civi_deactive_user&user_id=' . $user_id, 'deactive_' . $user_id);

			// Check if button form submit is clicked
			if (isset($_POST['action']) && $_POST['action'] == 'civi_deactive_user' && isset($_POST['user_id']) && $_POST['user_id'] == $user_id) {
				if (wp_verify_nonce($_POST['_wpnonce'], 'civi_deactive_user_action')) {
					// Deactivate user
					$user = get_userdata($user_id);
					if ($user) {

						$args = array(
							'user_login_deactive' => $user->user_login,
							'user_email_deactive' => $user->user_email,
						);

						$admin_email = get_option('admin_email');

						civi_send_email($user->user_email, 'mail_deactive_user', $args);
						civi_send_email($admin_email, 'admin_mail_deactive_user', $args);

						// Delete all posts of user
						$posts = get_posts([
							'author' => $user_id,
							'numberposts' => -1,
							'post_type' => 'any',
							'post_status' => 'any',
						]);

						foreach ($posts as $post) {
							wp_delete_post($post->ID, true);
						}

						// Delete all user meta data
						$wpdb->delete($wpdb->usermeta, ['user_id' => $user_id]);

						// Delete user
						wp_delete_user($user_id);

						wp_logout();
					}
					// Redirect to homepage or any other page
					wp_redirect(home_url());
					exit;
				} else {
					wp_die(__('Nonce verification failed.', 'civi-framework'));
				}
			}

			if (is_page($settings_employer) || is_page($settings_candidate)) :
			?>
				<div class="form-popup civi-form-popup" id="form-setting-deactive">
					<div class="bg-overlay"></div>
					<form method="post" action="#" class="setting-popup deactive-user-form inner-popup custom-scrollbar">
						<h5><?php esc_html_e('Do you want to delete your account along with all data?', 'civi-framework'); ?></h5>
						<div class="button-wrapper">
							<a href="#" class="civi-button button-outline button-block button-cancel"><?php esc_html_e('No', 'civi-framework'); ?></a>
							<button class="civi-button button-block" type="submit" name="deactive_user"><?php esc_html_e('Yes', 'civi-framework') ?></button>
						</div>
						<?php wp_nonce_field('civi_deactive_user_action'); ?>
						<input type="hidden" name="action" value="civi_deactive_user">
						<input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
					</form>
				</div>
			<?php
			endif;
		}

		/**
		 * Form candidate user package
		 */
		public function civi_form_candidate_user_package()
		{
			$candidate_user_package = civi_get_option('civi_candidate_user_package_page_id');
			if (is_page($candidate_user_package)) : ?>
				<div class="form-popup civi-form-popup" id="form-candidate-user-package">
					<div class="bg-overlay"></div>
					<form class="user-package-popup inner-popup custom-scrollbar">
						<a href="#" class="btn-close"><i class="far fa-times"></i></a>
						<h5><?php esc_html_e('Package overview', 'civi-framework'); ?></h5>
						<?php civi_get_template('candidate/package/overview.php'); ?>
					</form>
				</div>

			<?php
			endif;
		}

		/**
		 * Form employer user package
		 */
		public function civi_form_employer_user_package()
		{
			$employer_user_package = civi_get_option('civi_user_package_page_id');
			if (is_page($employer_user_package)) : ?>
				<div class="form-popup civi-form-popup" id="form-employer-user-package">
					<div class="bg-overlay"></div>
					<form class="user-package-popup inner-popup custom-scrollbar">
						<a href="#" class="btn-close"><i class="far fa-times"></i></a>
						<h5><?php esc_html_e('Package overview', 'civi-framework'); ?></h5>
						<?php civi_get_template('company/package/overview.php'); ?>
					</form>
				</div>

			<?php
			endif;
		}

		/**
		 * Form candidate order refund
		 */
		public function civi_form_service_order_refund()
		{
			$civi_employer_service = civi_get_option('civi_employer_service_page_id');
			if (is_page($civi_employer_service)) : ?>
				<div class="form-popup civi-form-popup" id="form-service-order-refund">
					<div class="bg-overlay"></div>
					<form class="service-order-refund-popup inner-popup custom-scrollbar">
						<a href="#" class="btn-close"><i class="far fa-times"></i></a>
						<h5><?php esc_html_e('Refund reason', 'civi-framework'); ?></h5>
						<p><?php esc_html_e('Refund reason', 'civi-framework'); ?></p>
						<div class="row">
							<div class="form-group col-md-12">
								<div class="form-select">
									<div class="select2-field">
										<select class="search-control civi-select2" name="service_payment">
											<option value="wire_transfer"><?php esc_html_e('Wire Transfer', 'civi-framework') ?></option>
											<option value="stripe"><?php esc_html_e('Pay With Stripe', 'civi-framework') ?></option>
											<option value="paypal"><?php esc_html_e('Pay With Paypal', 'civi-framework') ?></option>
										</select>
									</div>
								</div>
							</div>
							<div class="form-group col-md-12">
								<textarea name="service_content_refund" cols="30" rows="5" placeholder="<?php esc_attr_e('Add refund reason...', 'civi-framework'); ?>"></textarea>
							</div>
						</div>
						<div class="civi-message-error"></div>
						<div class="btn-wrapper">
							<button class="civi-button" id="btn-service-refund" type="submit">
								<?php esc_html_e('Refund', 'civi-framework'); ?>
								<span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
							</button>
						</div>
					</form>
				</div>

			<?php
			endif;
		}

		/**
		 * Form candidate view-reason
		 */
		public function civi_form_service_view_reason()
		{
			$civi_employer_service = civi_get_option('civi_employer_service_page_id');
			$civi_candidate_service = civi_get_option('civi_candidate_service_page_id');
			if (is_page($civi_employer_service) || is_page($civi_candidate_service)) : ?>
				<div class="form-popup civi-form-popup" id="form-service-view-reason">
					<div class="bg-overlay"></div>
					<form class="service-view-reason-popup inner-popup custom-scrollbar">
						<a href="#" class="btn-close"><i class="far fa-times"></i></a>
						<h5><?php esc_html_e('Refund reason', 'civi-framework'); ?></h5>
						<div class="content-refund-reason"></div>
					</form>
				</div>

			<?php
			endif;
		}

		/**
		 * Form candidate view-reason
		 */
		public function civi_form_service_withdraw()
		{
			$civi_candidate_service = civi_get_option('civi_candidate_service_page_id');
			$link_candidate_settings = civi_get_permalink('candidate_settings');
			$custom_payout = civi_get_option('custom_payout_setting');
			$enable_paypal = civi_get_option('enable_payout_paypal');
			$enable_stripe = civi_get_option('enable_payout_stripe');
			$enable_bank = civi_get_option('enable_payout_bank_transfer');
			if (is_page($civi_candidate_service)) : ?>
				<div class="form-popup civi-form-popup" id="form-service-withdraw">
					<div class="bg-overlay"></div>
					<form class="service-withdraw-popup inner-popup custom-scrollbar">
						<a href="#" class="btn-close"><i class="far fa-times"></i></a>
						<h5><?php esc_html_e('Withdrawals', 'civi-framework'); ?></h5>
						<p>
							<?php esc_html_e('If you have not entered your payout information', 'civi-framework'); ?>
							<a href="<?php echo $link_candidate_settings; ?>"><?php esc_html_e('click here!', 'civi-framework'); ?></a>
						</p>
						<div class="row">
							<div class="form-group col-md-12">
								<div class="form-select">
									<div class="select2-field">
										<select class="search-control civi-select2" name="withdraw_payment">
											<?php if ($enable_bank === '1') { ?>
												<option value="wire_transfer"><?php esc_html_e('Wire Transfer', 'civi-framework') ?></option>
											<?php } ?>
											<?php if ($enable_stripe === '1') { ?>
												<option value="stripe"><?php esc_html_e('Pay With Stripe', 'civi-framework') ?></option>
											<?php } ?>
											<?php if ($enable_paypal === '1') { ?>
												<option value="paypal"><?php esc_html_e('Pay With Paypal', 'civi-framework') ?></option>
											<?php } ?>
											<?php
											$name_same = array();
											if (!empty($custom_payout)) :
												foreach ($custom_payout as $field) :
													if (!empty($field['name'])) :
														if (!in_array($field['name'], $name_same)) {
															$field_id = str_replace(' ', '-', $field['name']);
															echo '<option value="' . $field_id . '">' . $field['name'] . '</option>';
															$name_same[] = $field['name'];
														}
													endif;
												endforeach;
											endif;
											?>
										</select>
									</div>
								</div>
							</div>
							<div class="form-group col-md-12">
								<input type="number" name="withdraw_price" pattern="[-+]?[0-9]" placeholder="<?php echo esc_attr__('Add amount of money', 'civi-framework') ?>">
							</div>
						</div>
						<div class="civi-message-error"></div>
						<div class="btn-wrapper">
							<button class="civi-button" id="btn-submit-withdraw" type="submit">
								<span class="btn-loader"><i class="fas fa-arrow-to-bottom"></i></span>
								<?php esc_html_e('Withdrawals', 'civi-framework'); ?>
							</button>
						</div>
					</form>
				</div>

			<?php
			endif;
		}

		/**
		 * Form Invite Candidate
		 */
		public function civi_form_invite_candidate()
		{
			if (is_single() && (get_post_type() == 'candidate')) :
				civi_get_template('jobs/invite.php');
			endif;
		}

		/**
		 * Google job schema
		 */
		public function civi_add_google_job_schema()
		{
			$enable_google_job_schema = civi_get_option('enable_google_job_schema');
			if ((is_single() && (get_post_type() == 'jobs') && $enable_google_job_schema)) :
				global $post;

				$jobs_id = get_the_ID();
				$job_title = get_the_title($post);
				$job_description = get_post_field('post_content', $post);
				$job_date_posted = get_the_date('Y-m-d', $post);

				$jobs_days_single = get_post_meta($jobs_id, CIVI_METABOX_PREFIX . 'jobs_days_closing', true);
				$enable_jobs_expires = get_post_meta($jobs_id, CIVI_METABOX_PREFIX . 'enable_jobs_expires', true);
				if ($enable_jobs_expires == '1') {
					$jobs_days_closing   = '0';
				} else {
					if ($jobs_days_single) {
						$jobs_days_closing = $jobs_days_single;
					} else {
						$jobs_days_closing   = civi_get_option('jobs_number_days', true);
					}
				}
				$current_date = get_the_date('Y-m-d', $jobs_id);
				$expiration_date = date('Y-m-d', strtotime($current_date . '+' . $jobs_days_closing . ' days'));
				$job_expiration_date = civi_convert_date_format($expiration_date);
				$jobs_skills = get_the_terms($jobs_id, 'jobs-skills');
				$skills_name = array();
				if (is_array($jobs_skills)) :
					foreach ($jobs_skills as $skills) {
						if ($skills->term_id !== '') {
							$skills_name[] = $skills->name;
						}
					}
				endif;
				$skills_name = implode(',', $skills_name);

			$jobs_select_company    = get_post_meta($jobs_id, CIVI_METABOX_PREFIX . 'jobs_select_company');
			$company_id = isset($jobs_select_company[0]) ? $jobs_select_company[0] : '';
			$job_hiring_organization_name = get_the_title($company_id);
			$job_hiring_organization_url = get_post_permalink($company_id);

			// Get company additional information
			$company_logo = get_post_meta($company_id, CIVI_METABOX_PREFIX . 'company_logo');
			$company_logo_url = !empty($company_logo[0]['url']) ? $company_logo[0]['url'] : '';
			$company_address = get_post_meta($company_id, CIVI_METABOX_PREFIX . 'company_address');
			$company_address_value = !empty($company_address[0]) ? $company_address[0] : '';
			$company_email = get_post_meta($company_id, CIVI_METABOX_PREFIX . 'company_email');
			$company_email_value = !empty($company_email[0]) ? $company_email[0] : '';
			$company_phone = get_post_meta($company_id, CIVI_METABOX_PREFIX . 'company_phone');
			$company_phone_value = !empty($company_phone[0]) ? $company_phone[0] : '';
			$company_website = get_post_meta($company_id, CIVI_METABOX_PREFIX . 'company_website');
			$company_website_value = !empty($company_website[0]) ? $company_website[0] : '';

			// Get company location for address
			$company_location = get_the_terms($company_id, 'company-location');
			$company_location_name = array();
			$company_location_id = array();
			if (is_array($company_location) && !is_wp_error($company_location)) {
				foreach ($company_location as $location) {
					if (isset($location->name)) {
						$company_location_name[] = $location->name;
					}
					if (isset($location->term_id) && $location->term_id !== '') {
						$company_location_id[] = (int)$location->term_id;
					}
				}
			}

			// Get company state and country for address
			$company_address_region = '';
			$company_address_country = '';
			$enable_option_state = civi_get_option('enable_option_state');
			$enable_option_country = civi_get_option('enable_option_country');
			$company_state_id = '';

			if ($enable_option_state === '1' && !empty($company_location_id)) {
				// Get state_id from the first company location
				$first_company_location_id = isset($company_location_id[0]) ? (int)$company_location_id[0] : 0;
				if ($first_company_location_id > 0) {
					$company_state_id = get_term_meta($first_company_location_id, 'company-location-state', true);
					if (!empty($company_state_id)) {
						$company_state_by_id = get_term_by('id', $company_state_id, 'company-state');
						if (!empty($company_state_by_id) && !is_wp_error($company_state_by_id)) {
							$company_address_region = $company_state_by_id->name;
						}
					}
				}
			}

			if ($enable_option_state === '1' && $enable_option_country === '1' && !empty($company_state_id)) {
				$company_country_id = get_term_meta($company_state_id, 'company-state-country', true);
				if (!empty($company_country_id)) {
					$countries = civi_get_countries();
					$company_country_val = array();
					foreach ($countries as $k => $v) {
						if ($k == $company_country_id) {
							$company_country_val[] = $v;
						}
					}
					if (!empty($company_country_val)) {
						$company_address_country = implode('', $company_country_val);
					}
				}
			} elseif ($enable_option_country === '1' && empty($company_address_country)) {
				// Get default country from select_option_country if available
				$select_option_country = civi_get_option('select_option_country');
				$countries = civi_get_countries();
				if (!empty($select_option_country) && is_array($select_option_country)) {
					$first_country_key = reset($select_option_country);
					if (!empty($first_country_key) && isset($countries[$first_country_key])) {
						$company_address_country = $countries[$first_country_key];
					}
				}
			}

			// Get job identifier (URL)
			$job_identifier = get_permalink($jobs_id);

			// Check if job is remote
			$jobs_type_terms = get_the_terms($jobs_id, 'jobs-type');
			$job_location_type = '';
			if (is_array($jobs_type_terms) && !is_wp_error($jobs_type_terms)) {
				foreach ($jobs_type_terms as $type_term) {
					if (isset($type_term->slug) && strtolower($type_term->slug) === 'remote') {
						$job_location_type = 'TELECOMMUTE';
						break;
					}
				}
			}

			// Get company categories for industry
			$company_categories = get_the_terms($company_id, 'company-categories');
			$industry_values = array();
			if (is_array($company_categories) && !is_wp_error($company_categories)) {
				foreach ($company_categories as $category) {
					if (isset($category->name)) {
						$industry_values[] = $category->name;
					}
				}
			}

			$jobs_location = get_the_terms($jobs_id, 'jobs-location');
			$location_name = $location_id = array();
			if (is_array($jobs_location) && !is_wp_error($jobs_location)) :
				foreach ($jobs_location as $location) {
					if (isset($location->term_id) && $location->term_id !== '') {
						// Ensure name is a string, not an array
						$location_name[] = is_string($location->name) ? $location->name : (string)$location->name;
						$location_id[] = (int)$location->term_id;
					}
				}
			endif;

			$enable_option_state = civi_get_option('enable_option_state');
			$enable_option_country = civi_get_option('enable_option_country');
			// Ensure all values are strings before imploding
			$location_name = array_filter(array_map('strval', $location_name));
			$job_address_locality = !empty($location_name) ? implode(',', $location_name) : '';
			$job_address_region = '';
			$job_address_country = '';
			$state_id = '';

			// Get default country from select_option_country if available
			$select_option_country = civi_get_option('select_option_country');
			$countries = civi_get_countries();
			if (!empty($select_option_country) && is_array($select_option_country)) {
				$first_country_key = reset($select_option_country);
				if (!empty($first_country_key) && isset($countries[$first_country_key])) {
					$job_address_country = $countries[$first_country_key];
				}
			}

			if ($enable_option_state === '1' && !empty($location_id)) {
				// Get state_id from the first location (get_term_meta expects a single term ID)
				$first_location_id = isset($location_id[0]) ? (int)$location_id[0] : 0;
				if ($first_location_id > 0) {
					$state_id = get_term_meta($first_location_id, 'jobs-location-state', true);
					if (!empty($state_id)) {
						$state_by_id = get_term_by('id', $state_id, 'jobs-state');
						if (!empty($state_by_id) && !is_wp_error($state_by_id)) {
							$job_address_region = $state_by_id->name;
						}
					}
				}
			}
			if ($enable_option_state === '1' && $enable_option_country === '1' && !empty($state_id)) {
				$country_id = get_term_meta($state_id, 'jobs-state-country', true);
				if (!empty($country_id)) {
					$countries = civi_get_countries();
					$country_val = array();
					foreach ($countries as $k => $v) {
						if ($k == $country_id) {
							$country_val[] = $v;
						}
					}
					if (!empty($country_val)) {
						$job_address_country = implode('', $country_val);
					}
				}
			} elseif ($enable_option_country === '1' && empty($job_address_country)) {
				// Get default country from select_option_country if available
				$select_option_country = civi_get_option('select_option_country');
				$countries = civi_get_countries();
				if (!empty($select_option_country) && is_array($select_option_country)) {
					$first_country_key = reset($select_option_country);
					if (!empty($first_country_key) && isset($countries[$first_country_key])) {
						$job_address_country = $countries[$first_country_key];
					}
				}
			}
				$job_salary = get_post_meta($jobs_id, CIVI_METABOX_PREFIX . 'jobs_salary_show', true);
				$job_employment_type = get_post_meta($jobs_id, CIVI_METABOX_PREFIX . 'jobs_type', true);
				$job_street_address = get_post_meta($jobs_id, CIVI_METABOX_PREFIX . 'jobs_location', true);
				$job_postal_code = get_post_meta($jobs_id, CIVI_METABOX_PREFIX . 'jobs_postal_code', true);
			?>
				<script type="application/ld+json">
					{
						"@context": "https://schema.org",
						"@type": "JobPosting",
						"identifier": {
							"@type": "PropertyValue",
							"name": "<?php echo esc_js(get_bloginfo('name')); ?>",
							"value": "<?php echo esc_js($job_identifier); ?>"
						},
						"title": "<?php echo esc_js(is_array($job_title) ? implode(', ', $job_title) : $job_title); ?>",
						"datePosted": "<?php echo esc_js(is_array($job_date_posted) ? implode(', ', $job_date_posted) : $job_date_posted); ?>",
						"validThrough": "<?php echo esc_js(is_array($job_expiration_date) ? implode(', ', $job_expiration_date) : $job_expiration_date); ?>",
						"skills": "<?php echo esc_js(is_array($skills_name) ? implode(', ', $skills_name) : $skills_name); ?>",
						"description": <?php echo json_encode($job_description); ?>,
						"hiringOrganization": {
							"@type": "Organization",
							"name": "<?php echo esc_js(is_array($job_hiring_organization_name) ? implode(', ', $job_hiring_organization_name) : $job_hiring_organization_name); ?>",
							"sameAs": "<?php echo esc_js(is_array($job_hiring_organization_url) ? implode(', ', $job_hiring_organization_url) : $job_hiring_organization_url); ?>"<?php if (!empty($company_logo_url)) : ?>,
							"logo": "<?php echo esc_js($company_logo_url); ?>"<?php endif; ?><?php if (!empty($company_website_value)) : ?>,
							"url": "<?php echo esc_js($company_website_value); ?>"<?php endif; ?><?php if (!empty($company_email_value) || !empty($company_phone_value) || !empty($company_address_value)) : ?>,
							"address": {
								"@type": "PostalAddress"<?php if (!empty($company_address_value)) : ?>,
								"streetAddress": "<?php echo esc_js($company_address_value); ?>"<?php endif; ?><?php if (!empty($company_location_name)) : ?>,
								"addressLocality": "<?php echo esc_js(implode(', ', $company_location_name)); ?>"<?php endif; ?><?php if (!empty($company_address_region)) : ?>,
								"addressRegion": "<?php echo esc_js($company_address_region); ?>"<?php endif; ?><?php if (!empty($company_address_country)) : ?>,
								"addressCountry": "<?php echo esc_js($company_address_country); ?>"<?php endif; ?>
							}<?php if (!empty($company_email_value)) : ?>,
							"email": "<?php echo esc_js($company_email_value); ?>"<?php endif; ?><?php if (!empty($company_phone_value)) : ?>,
							"telephone": "<?php echo esc_js($company_phone_value); ?>"<?php endif; ?><?php endif; ?>
						},
						"jobLocation": {
							"@type": "Place",
							"address": {
								"@type": "PostalAddress",
								"streetAddress": "<?php echo esc_js(is_array($job_street_address) ? implode(', ', $job_street_address) : ($job_street_address ?: '')); ?>",
								"addressLocality": "<?php echo esc_js(is_array($job_address_locality) ? implode(', ', $job_address_locality) : $job_address_locality); ?>",
								"addressRegion": "<?php echo esc_js(is_array($job_address_region) ? implode(', ', $job_address_region) : $job_address_region); ?>",
								"postalCode": "<?php echo esc_js(is_array($job_postal_code) ? implode(', ', $job_postal_code) : ($job_postal_code ?: '')); ?>",
								"addressCountry": "<?php echo esc_js(is_array($job_address_country) ? implode(', ', $job_address_country) : $job_address_country); ?>"
							}
						}<?php if (!empty($job_location_type)) : ?>,
						"jobLocationType": "<?php echo esc_js($job_location_type); ?>"<?php endif; ?><?php if (!empty($industry_values)) : ?>,
						"industry": "<?php echo esc_js(implode(', ', $industry_values)); ?>"<?php endif; ?>,
						"baseSalary": {
							"@type": "MonetaryAmount",
							"currency": "USD",
							"value": {
								"@type": "QuantitativeValue",
								"value": "<?php echo esc_js(is_array($job_salary) ? implode(', ', $job_salary) : ($job_salary ?: '0')); ?>",
								"unitText": "YEAR"
							}
						},
						"employmentType": "<?php echo esc_js(is_array($job_employment_type) ? implode(', ', $job_employment_type) : ($job_employment_type ?: 'FULL_TIME')); ?>"
					}
				</script>
			<?php endif;
		}

		/**
		 * Google company schema (Organization)
		 */
		public function civi_add_company_schema()
		{
			$enable_google_company_schema = civi_get_option('enable_google_company_schema', '1');
			if ((is_single() && (get_post_type() == 'company') && $enable_google_company_schema)) :
				global $post;

				$company_id = get_the_ID();
				$company_name = get_the_title($post);
				$company_description = get_post_field('post_content', $post);
				$company_url = get_permalink($company_id);

				// Get company logo
				$company_logo = get_post_meta($company_id, CIVI_METABOX_PREFIX . 'company_logo');
				$company_logo_url = !empty($company_logo[0]['url']) ? $company_logo[0]['url'] : '';

				// Get company contact information
				$company_website = get_post_meta($company_id, CIVI_METABOX_PREFIX . 'company_website');
				$company_website_value = !empty($company_website[0]) ? $company_website[0] : '';
				$company_phone = get_post_meta($company_id, CIVI_METABOX_PREFIX . 'company_phone');
				$company_phone_value = !empty($company_phone[0]) ? $company_phone[0] : '';
				$company_email = get_post_meta($company_id, CIVI_METABOX_PREFIX . 'company_email');
				$company_email_value = !empty($company_email[0]) ? $company_email[0] : '';

				// Get company address
				$company_address = get_post_meta($company_id, CIVI_METABOX_PREFIX . 'company_address');
				$company_address_value = !empty($company_address[0]) ? $company_address[0] : '';

				// Get company location
				$company_location = get_the_terms($company_id, 'company-location');
				$company_location_name = array();
				$company_location_id = array();
				if (is_array($company_location) && !is_wp_error($company_location)) {
					foreach ($company_location as $location) {
						if (isset($location->name)) {
							$company_location_name[] = $location->name;
						}
						if (isset($location->term_id) && $location->term_id !== '') {
							$company_location_id[] = (int)$location->term_id;
						}
					}
				}

				// Get company state and country
				$company_address_region = '';
				$company_address_country = '';
				$enable_option_state = civi_get_option('enable_option_state');
				$enable_option_country = civi_get_option('enable_option_country');
				$company_state_id = '';

				if ($enable_option_state === '1' && !empty($company_location_id)) {
					$first_company_location_id = isset($company_location_id[0]) ? (int)$company_location_id[0] : 0;
					if ($first_company_location_id > 0) {
						$company_state_id = get_term_meta($first_company_location_id, 'company-location-state', true);
						if (!empty($company_state_id)) {
							$company_state_by_id = get_term_by('id', $company_state_id, 'company-state');
							if (!empty($company_state_by_id) && !is_wp_error($company_state_by_id)) {
								$company_address_region = $company_state_by_id->name;
							}
						}
					}
				}

				if ($enable_option_state === '1' && $enable_option_country === '1' && !empty($company_state_id)) {
					$company_country_id = get_term_meta($company_state_id, 'company-state-country', true);
					if (!empty($company_country_id)) {
						$countries = civi_get_countries();
						$company_country_val = array();
						foreach ($countries as $k => $v) {
							if ($k == $company_country_id) {
								$company_country_val[] = $v;
							}
						}
						if (!empty($company_country_val)) {
							$company_address_country = implode('', $company_country_val);
						}
					}
				}

				// Get company categories
				$company_categories = get_the_terms($company_id, 'company-categories');
				$industry_values = array();
				if (is_array($company_categories) && !is_wp_error($company_categories)) {
					foreach ($company_categories as $category) {
						if (isset($category->name)) {
							$industry_values[] = $category->name;
						}
					}
				}

				// Get company size
				$company_size = get_the_terms($company_id, 'company-size');
				$number_of_employees = '';
				if (is_array($company_size) && !is_wp_error($company_size) && !empty($company_size)) {
					$number_of_employees = $company_size[0]->name;
				}

				// Get company founded year
				$company_founded = get_post_meta($company_id, CIVI_METABOX_PREFIX . 'company_founded');
				$founding_date = !empty($company_founded[0]) ? $company_founded[0] . '-01-01' : '';

				// Get social media profiles
				$same_as = array();
				$company_facebook = get_post_meta($company_id, CIVI_METABOX_PREFIX . 'company_facebook');
				if (!empty($company_facebook[0])) {
					$same_as[] = $company_facebook[0];
				}
				$company_twitter = get_post_meta($company_id, CIVI_METABOX_PREFIX . 'company_twitter');
				if (!empty($company_twitter[0])) {
					$same_as[] = $company_twitter[0];
				}
				$company_linkedin = get_post_meta($company_id, CIVI_METABOX_PREFIX . 'company_linkedin');
				if (!empty($company_linkedin[0])) {
					$same_as[] = $company_linkedin[0];
				}
				$company_instagram = get_post_meta($company_id, CIVI_METABOX_PREFIX . 'company_instagram');
				if (!empty($company_instagram[0])) {
					$same_as[] = $company_instagram[0];
				}
				if (!empty($company_website_value)) {
					$same_as[] = $company_website_value;
				}
			?>
				<script type="application/ld+json">
					{
						"@context": "https://schema.org",
						"@type": "Organization",
						"name": "<?php echo esc_js($company_name); ?>",
						"url": "<?php echo esc_js($company_url); ?>"<?php if (!empty($company_logo_url)) : ?>,
						"logo": "<?php echo esc_js($company_logo_url); ?>"<?php endif; ?><?php if (!empty($company_description)) : ?>,
						"description": <?php echo json_encode(wp_strip_all_tags($company_description)); ?><?php endif; ?><?php if (!empty($company_website_value)) : ?>,
						"sameAs": <?php echo json_encode($same_as); ?><?php endif; ?><?php if (!empty($company_email_value) || !empty($company_phone_value) || !empty($company_address_value) || !empty($company_location_name)) : ?>,
						"address": {
							"@type": "PostalAddress"<?php if (!empty($company_address_value)) : ?>,
							"streetAddress": "<?php echo esc_js($company_address_value); ?>"<?php endif; ?><?php if (!empty($company_location_name)) : ?>,
							"addressLocality": "<?php echo esc_js(implode(', ', $company_location_name)); ?>"<?php endif; ?><?php if (!empty($company_address_region)) : ?>,
							"addressRegion": "<?php echo esc_js($company_address_region); ?>"<?php endif; ?><?php if (!empty($company_address_country)) : ?>,
							"addressCountry": "<?php echo esc_js($company_address_country); ?>"<?php endif; ?>
						}<?php if (!empty($company_email_value)) : ?>,
						"email": "<?php echo esc_js($company_email_value); ?>"<?php endif; ?><?php if (!empty($company_phone_value)) : ?>,
						"telephone": "<?php echo esc_js($company_phone_value); ?>"<?php endif; ?><?php endif; ?><?php if (!empty($industry_values)) : ?>,
						"industry": <?php echo json_encode($industry_values); ?><?php endif; ?><?php if (!empty($number_of_employees)) : ?>,
						"numberOfEmployees": {
							"@type": "QuantitativeValue",
							"value": "<?php echo esc_js($number_of_employees); ?>"
						}<?php endif; ?><?php if (!empty($founding_date)) : ?>,
						"foundingDate": "<?php echo esc_js($founding_date); ?>"<?php endif; ?>
					}
				</script>
			<?php endif;
		}

		/**
		 * Google candidate schema (Person)
		 */
		public function civi_add_candidate_schema()
		{
			$enable_google_candidate_schema = civi_get_option('enable_google_candidate_schema', '1');
			if ((is_single() && (get_post_type() == 'candidate') && $enable_google_candidate_schema)) :
				global $post;

				$candidate_id = get_the_ID();
				$candidate_name = get_the_title($post);
				$candidate_description = get_post_field('post_content', $post);
				$candidate_url = get_permalink($candidate_id);

				// Get candidate name
				$candidate_first_name = get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_first_name', true);
				$candidate_last_name = get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_last_name', true);
				$given_name = !empty($candidate_first_name) ? $candidate_first_name : '';
				$family_name = !empty($candidate_last_name) ? $candidate_last_name : '';
				$full_name = trim($given_name . ' ' . $family_name);
				if (empty($full_name)) {
					$full_name = $candidate_name;
				}

				// Get candidate avatar
				$author_id = get_post_field('post_author', $candidate_id);
				$candidate_avatar = get_the_author_meta('author_avatar_image_url', $author_id);
				$candidate_image = !empty($candidate_avatar) ? $candidate_avatar : '';

				// Get candidate contact information
				$candidate_email = get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_email', true);
				$candidate_phone = get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_phone', true);
				$candidate_website = get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_website', true);

				// Get candidate address
				$candidate_address = get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_address', true);

				// Get candidate location
				$candidate_location = get_the_terms($candidate_id, 'candidate_locations');
				$candidate_location_name = array();
				$candidate_location_id = array();
				if (is_array($candidate_location) && !is_wp_error($candidate_location)) {
					foreach ($candidate_location as $location) {
						if (isset($location->name)) {
							$candidate_location_name[] = $location->name;
						}
						if (isset($location->term_id) && $location->term_id !== '') {
							$candidate_location_id[] = (int)$location->term_id;
						}
					}
				}

				// Get candidate state and country
				$candidate_address_region = '';
				$candidate_address_country = '';
				$enable_option_state = civi_get_option('enable_option_state');
				$enable_option_country = civi_get_option('enable_option_country');
				$candidate_state_id = '';

				if ($enable_option_state === '1' && !empty($candidate_location_id)) {
					$first_candidate_location_id = isset($candidate_location_id[0]) ? (int)$candidate_location_id[0] : 0;
					if ($first_candidate_location_id > 0) {
						$candidate_state_id = get_term_meta($first_candidate_location_id, 'candidate_locations-state', true);
						if (!empty($candidate_state_id)) {
							$candidate_state_by_id = get_term_by('id', $candidate_state_id, 'candidate_state');
							if (!empty($candidate_state_by_id) && !is_wp_error($candidate_state_by_id)) {
								$candidate_address_region = $candidate_state_by_id->name;
							}
						}
					}
				}

				if ($enable_option_state === '1' && $enable_option_country === '1' && !empty($candidate_state_id)) {
					$candidate_country_id = get_term_meta($candidate_state_id, 'candidate_state-country', true);
					if (!empty($candidate_country_id)) {
						$countries = civi_get_countries();
						$candidate_country_val = array();
						foreach ($countries as $k => $v) {
							if ($k == $candidate_country_id) {
								$candidate_country_val[] = $v;
							}
						}
						if (!empty($candidate_country_val)) {
							$candidate_address_country = implode('', $candidate_country_val);
						}
					}
				} elseif ($enable_option_country === '1' && empty($candidate_address_country)) {
					// Get default country from select_option_country if available
					$select_option_country = civi_get_option('select_option_country');
					$countries = civi_get_countries();
					if (!empty($select_option_country) && is_array($select_option_country)) {
						$first_country_key = reset($select_option_country);
						if (!empty($first_country_key) && isset($countries[$first_country_key])) {
							$candidate_address_country = $countries[$first_country_key];
						}
					}
				} elseif ($enable_option_country === '1' && empty($candidate_address_country)) {
					// Get default country from select_option_country if available
					$select_option_country = civi_get_option('select_option_country');
					$countries = civi_get_countries();
					if (!empty($select_option_country) && is_array($select_option_country)) {
						$first_country_key = reset($select_option_country);
						if (!empty($first_country_key) && isset($countries[$first_country_key])) {
							$candidate_address_country = $countries[$first_country_key];
						}
					}
				}

				// Get candidate skills
				$candidate_skills = get_the_terms($candidate_id, 'jobs-skills');
				$skills_values = array();
				if (is_array($candidate_skills) && !is_wp_error($candidate_skills)) {
					foreach ($candidate_skills as $skill) {
						if (isset($skill->name)) {
							$skills_values[] = $skill->name;
						}
					}
				}

				// Get candidate categories
				$candidate_categories = get_the_terms($candidate_id, 'candidate_categories');
				$job_title_values = array();
				if (is_array($candidate_categories) && !is_wp_error($candidate_categories)) {
					foreach ($candidate_categories as $category) {
						if (isset($category->name)) {
							$job_title_values[] = $category->name;
						}
					}
				}

				// Get current position
				$candidate_current_position = get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_current_position', true);
				$job_title = !empty($candidate_current_position) ? $candidate_current_position : (!empty($job_title_values) ? $job_title_values[0] : '');

				// Get social media profiles
				$same_as = array();
				$candidate_facebook = get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_facebook', true);
				if (!empty($candidate_facebook)) {
					$same_as[] = $candidate_facebook;
				}
				$candidate_twitter = get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_twitter', true);
				if (!empty($candidate_twitter)) {
					$same_as[] = $candidate_twitter;
				}
				$candidate_linkedin = get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_linkedin', true);
				if (!empty($candidate_linkedin)) {
					$same_as[] = $candidate_linkedin;
				}
				$candidate_instagram = get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_instagram', true);
				if (!empty($candidate_instagram)) {
					$same_as[] = $candidate_instagram;
				}
				if (!empty($candidate_website)) {
					$same_as[] = $candidate_website;
				}
			?>
				<script type="application/ld+json">
					{
						"@context": "https://schema.org",
						"@type": "Person",
						"name": "<?php echo esc_js($full_name); ?>"<?php if (!empty($given_name)) : ?>,
						"givenName": "<?php echo esc_js($given_name); ?>"<?php endif; ?><?php if (!empty($family_name)) : ?>,
						"familyName": "<?php echo esc_js($family_name); ?>"<?php endif; ?><?php if (!empty($candidate_url)) : ?>,
						"url": "<?php echo esc_js($candidate_url); ?>"<?php endif; ?><?php if (!empty($candidate_image)) : ?>,
						"image": "<?php echo esc_js($candidate_image); ?>"<?php endif; ?><?php if (!empty($candidate_description)) : ?>,
						"description": <?php echo json_encode(wp_strip_all_tags($candidate_description)); ?><?php endif; ?><?php if (!empty($job_title)) : ?>,
						"jobTitle": "<?php echo esc_js($job_title); ?>"<?php endif; ?><?php if (!empty($candidate_email)) : ?>,
						"email": "<?php echo esc_js($candidate_email); ?>"<?php endif; ?><?php if (!empty($candidate_phone)) : ?>,
						"telephone": "<?php echo esc_js($candidate_phone); ?>"<?php endif; ?><?php if (!empty($same_as)) : ?>,
						"sameAs": <?php echo json_encode($same_as); ?><?php endif; ?><?php if (!empty($candidate_address) || !empty($candidate_location_name)) : ?>,
						"address": {
							"@type": "PostalAddress"<?php if (!empty($candidate_address)) : ?>,
							"streetAddress": "<?php echo esc_js($candidate_address); ?>"<?php endif; ?><?php if (!empty($candidate_location_name)) : ?>,
							"addressLocality": "<?php echo esc_js(implode(', ', $candidate_location_name)); ?>"<?php endif; ?><?php if (!empty($candidate_address_region)) : ?>,
							"addressRegion": "<?php echo esc_js($candidate_address_region); ?>"<?php endif; ?><?php if (!empty($candidate_address_country)) : ?>,
							"addressCountry": "<?php echo esc_js($candidate_address_country); ?>"<?php endif; ?>
						}<?php endif; ?><?php if (!empty($skills_values)) : ?>,
						"knowsAbout": <?php echo json_encode($skills_values); ?><?php endif; ?>
					}
				</script>
			<?php endif;
		}

		/**
		 * Google service schema (Service)
		 */
		public function civi_add_service_schema()
		{
			$enable_google_service_schema = civi_get_option('enable_google_service_schema', '1');
			if ((is_single() && (get_post_type() == 'service') && $enable_google_service_schema)) :
				global $post;

				$service_id = get_the_ID();
				$service_name = get_the_title($post);
				$service_description = get_post_field('post_content', $post);
				$service_url = get_permalink($service_id);

				// Get service provider (candidate)
				$author_id = get_post_field('post_author', $service_id);
				$candidate_id = civi_id_service_to_candidate($service_id);
				$provider_name = '';
				$provider_url = '';
				if (!empty($candidate_id)) {
					$provider_name = get_the_title($candidate_id);
					$provider_url = get_permalink($candidate_id);
				}

				// Get service image
				$service_thumbnail = get_the_post_thumbnail_url($service_id, 'full');
				$service_image = !empty($service_thumbnail) ? $service_thumbnail : '';

				// Get service price
				$service_price = get_post_meta($service_id, CIVI_METABOX_PREFIX . 'service_price', true);
				$service_currency = get_post_meta($service_id, CIVI_METABOX_PREFIX . 'service_currency_type', true);
				$currency_sign_default = civi_get_option('currency_sign_default', 'USD');
				$currency_code = !empty($service_currency) ? $service_currency : $currency_sign_default;

				// Get service location
				$service_location = get_the_terms($service_id, 'service-location');
				$service_location_name = array();
				$service_location_id = array();
				if (is_array($service_location) && !is_wp_error($service_location)) {
					foreach ($service_location as $location) {
						if (isset($location->name)) {
							$service_location_name[] = $location->name;
						}
						if (isset($location->term_id) && $location->term_id !== '') {
							$service_location_id[] = (int)$location->term_id;
						}
					}
				}

				// Get service address
				$service_address_meta = get_post_meta($service_id, CIVI_METABOX_PREFIX . 'service_location', true);
				$service_street_address = '';
				if (is_array($service_address_meta) && isset($service_address_meta['address'])) {
					$service_street_address = $service_address_meta['address'];
				}

				// Get service state and country
				$service_address_region = '';
				$service_address_country = '';
				$enable_option_state = civi_get_option('enable_option_state');
				$enable_option_country = civi_get_option('enable_option_country');
				$service_state_id = '';

				if ($enable_option_state === '1' && !empty($service_location_id)) {
					$first_service_location_id = isset($service_location_id[0]) ? (int)$service_location_id[0] : 0;
					if ($first_service_location_id > 0) {
						$service_state_id = get_term_meta($first_service_location_id, 'service-location-state', true);
						if (!empty($service_state_id)) {
							$service_state_by_id = get_term_by('id', $service_state_id, 'service-state');
							if (!empty($service_state_by_id) && !is_wp_error($service_state_by_id)) {
								$service_address_region = $service_state_by_id->name;
							}
						}
					}
				}

				if ($enable_option_state === '1' && $enable_option_country === '1' && !empty($service_state_id)) {
					$service_country_id = get_term_meta($service_state_id, 'service-state-country', true);
					if (!empty($service_country_id)) {
						$countries = civi_get_countries();
						$service_country_val = array();
						foreach ($countries as $k => $v) {
							if ($k == $service_country_id) {
								$service_country_val[] = $v;
							}
						}
						if (!empty($service_country_val)) {
							$service_address_country = implode('', $service_country_val);
						}
					}
				} elseif ($enable_option_country === '1' && empty($service_address_country)) {
					// Get default country from select_option_country if available
					$select_option_country = civi_get_option('select_option_country');
					$countries = civi_get_countries();
					if (!empty($select_option_country) && is_array($select_option_country)) {
						$first_country_key = reset($select_option_country);
						if (!empty($first_country_key) && isset($countries[$first_country_key])) {
							$service_address_country = $countries[$first_country_key];
						}
					}
				}

				// Get service categories
				$service_categories = get_the_terms($service_id, 'service-categories');
				$category_values = array();
				if (is_array($service_categories) && !is_wp_error($service_categories)) {
					foreach ($service_categories as $category) {
						if (isset($category->name)) {
							$category_values[] = $category->name;
						}
					}
				}

				// Get service skills
				$service_skills = get_the_terms($service_id, 'service-skills');
				$skills_values = array();
				if (is_array($service_skills) && !is_wp_error($service_skills)) {
					foreach ($service_skills as $skill) {
						if (isset($skill->name)) {
							$skills_values[] = $skill->name;
						}
					}
				}

				// Get service area
				$area_served = !empty($service_location_name) ? implode(', ', $service_location_name) : '';
			?>
				<script type="application/ld+json">
					{
						"@context": "https://schema.org",
						"@type": "Service",
						"name": "<?php echo esc_js($service_name); ?>",
						"description": <?php echo json_encode(wp_strip_all_tags($service_description)); ?><?php if (!empty($service_url)) : ?>,
						"url": "<?php echo esc_js($service_url); ?>"<?php endif; ?><?php if (!empty($service_image)) : ?>,
						"image": "<?php echo esc_js($service_image); ?>"<?php endif; ?><?php if (!empty($provider_name)) : ?>,
						"provider": {
							"@type": "Person",
							"name": "<?php echo esc_js($provider_name); ?>"<?php if (!empty($provider_url)) : ?>,
							"url": "<?php echo esc_js($provider_url); ?>"<?php endif; ?>
						}<?php endif; ?><?php if (!empty($service_price)) : ?>,
						"offers": {
							"@type": "Offer",
							"price": "<?php echo esc_js($service_price); ?>",
							"priceCurrency": "<?php echo esc_js($currency_code); ?>"
						}<?php endif; ?><?php if (!empty($service_street_address) || !empty($service_location_name)) : ?>,
						"areaServed": {
							"@type": "Place",
							"address": {
								"@type": "PostalAddress"<?php if (!empty($service_street_address)) : ?>,
								"streetAddress": "<?php echo esc_js($service_street_address); ?>"<?php endif; ?><?php if (!empty($service_location_name)) : ?>,
								"addressLocality": "<?php echo esc_js(implode(', ', $service_location_name)); ?>"<?php endif; ?><?php if (!empty($service_address_region)) : ?>,
								"addressRegion": "<?php echo esc_js($service_address_region); ?>"<?php endif; ?><?php if (!empty($service_address_country)) : ?>,
								"addressCountry": "<?php echo esc_js($service_address_country); ?>"<?php endif; ?>
							}
						}<?php elseif (!empty($area_served)) : ?>,
						"areaServed": "<?php echo esc_js($area_served); ?>"<?php endif; ?><?php if (!empty($category_values)) : ?>,
						"serviceType": <?php echo json_encode($category_values); ?><?php endif; ?><?php if (!empty($skills_values)) : ?>,
						"keywords": <?php echo json_encode($skills_values); ?><?php endif; ?>
					}
				</script>
			<?php endif;
		}

		public function wpa_show_permalinks($post_link, $post)
		{
			if (is_object($post) && $post->post_type == 'jobs') {
				$custom_statuses = array('expired', 'hidden', 'pause', 'canceled');
				if (in_array($post->post_status, $custom_statuses) && strpos($post_link, '?') !== false) {
					$jobs_slug = apply_filters('civi_jobs_slug', 'jobs');
					$terms = wp_get_object_terms($post->ID, 'jobs-categories');
					$jobs_categories_url_slug = civi_get_option('jobs_categories_url_slug');
					$category_slug = ($terms && !is_wp_error($terms)) ? $terms[0]->slug : $jobs_categories_url_slug;
					$post_link = home_url('/' . $jobs_slug . '/' . $category_slug . '/' . $post->post_name . '/');
					return $post_link;
				}

				$terms = wp_get_object_terms($post->ID, 'jobs-categories');
				$jobs_categories_url_slug = civi_get_option('jobs_categories_url_slug');
				if ($terms) {
					return str_replace('%jobs-categories%', $terms[0]->slug, $post_link);
				} else {
					return str_replace('%jobs-categories%', $jobs_categories_url_slug, $post_link);
				}
			}
			if (is_object($post) && $post->post_type == 'company') {
				$terms = wp_get_object_terms($post->ID, 'company-categories');
				$company_categories_url_slug = civi_get_option('company_categories_url_slug');
				if ($terms) {
					return str_replace('%company-categories%', $terms[0]->slug, $post_link);
				} else {
					return str_replace('%company-categories%', $company_categories_url_slug, $post_link);
				}
			}
			if (is_object($post) && $post->post_type == 'candidate') {
				$terms = wp_get_object_terms($post->ID, 'candidate_categories');
				$candidate_categories_url_slug = civi_get_option('candidate_categories_url_slug');
				if ($terms) {
					return str_replace('%candidate_categories%', $terms[0]->slug, $post_link);
				} else {
					return str_replace('%candidate_categories%', $candidate_categories_url_slug, $post_link);
				}
			}
			if (is_object($post) && $post->post_type == 'service') {
				$terms = wp_get_object_terms($post->ID, 'service-categories');
				$service_categories_url_slug = civi_get_option('service_categories_url_slug');
				if ($terms) {
					return str_replace('%service-categories%', $terms[0]->slug, $post_link);
				} else {
					return str_replace('%service-categories%', $service_categories_url_slug, $post_link);
				}
			}
			return $post_link;
		}

		public function generated_rewrite_rules()
		{
			add_rewrite_rule(
				'^jobs/(.*)/(.*)/?$',
				'index.php?post_type=jobs&name=$matches[2]',
				'top'
			);
			add_rewrite_rule(
				'^company/(.*)/(.*)/?$',
				'index.php?post_type=company&name=$matches[2]',
				'top'
			);
		}

		public function add_defer_facebook($tag, $handle)
		{
			if ('facebook-api' === $handle) {
				$tag = str_replace(' src', ' defer="defer" src', $tag);
			}
			return $tag;
		}

		public function civi_login_to_view($classes)
		{
			$enable_job_login_to_view = civi_get_option('enable_job_login_to_view');
			$enable_company_login_to_view = civi_get_option('enable_company_login_to_view');
			$enable_candidate_login_to_view = civi_get_option('enable_candidate_login_to_view');

			if ((($enable_job_login_to_view == 1 && get_post_type() == 'jobs')
					|| ($enable_company_login_to_view == 1 && get_post_type() == 'company')
					|| ($enable_candidate_login_to_view == 1 && get_post_type() == 'candidate'))
				&& is_single() && !is_user_logged_in()
			) {
				$classes[] = 'civi-ltw';
			} else {
				$classes[] = '';
			}
			return $classes;
		}

		public function ai_form_generate()
		{
			$enable_ai_helper = civi_get_option('enable_ai_helper');
			$ai_key = civi_get_option('ai_key');

			if ($enable_ai_helper != 1 || $ai_key == '') {
				return;
			}
			?>
			<div id="ai-popup" class="ai-popup popup">
				<div class="bg-overlay"></div>
				<div class="inner-popup">
					<a href="#" class="btn-close">
						<i class="far fa-times"></i>
					</a>
					<h4><?php esc_html_e('Generate description', 'civi-framework'); ?></h4>
					<div class="generate-content">
						<div class="left">
							<form action="#" class="ai-generate">
								<div class="field-group">
									<label for="ai_prompt"><?php esc_html_e('Prompt', 'civi-framework'); ?></label>
									<div class="form-textarea">
										<textarea name="ai_prompt" id="ai_prompt" cols="30" rows="6"><?php esc_html_e('Write a job description for a [Job Title] role at [Company Name].', 'civi-framework'); ?></textarea>
									</div>
								</div>
								<div class="field-group">
									<label for="ai_tone"><?php esc_html_e('Tone of voice', 'civi-framework') ?></label>
									<div class="form-select">
										<div class="select2-field select2-multiple">
											<select id="ai_tone" data-placeholder="<?php esc_attr_e('Select tone', 'civi-framework'); ?>" class="civi-select2" name="ai_tone">
												<?php
												if (tone_ai_helper()) {
													$ai_tone = civi_get_option('ai_tone');
													foreach (tone_ai_helper() as $tone_ai_helper_key => $tone_ai_helper_value) {
												?>
														<option value="<?php echo esc_attr($tone_ai_helper_key); ?>" <?php if ($ai_tone == $tone_ai_helper_key) {
																																														echo 'selected';
																																													} ?>><?php echo esc_html($tone_ai_helper_value); ?></option>
												<?php
													}
												}
												?>
											</select>
										</div>
									</div>
								</div>
								<div class="field-group">
									<label for="ai_language"><?php esc_html_e('Languages', 'civi-framework') ?></label>
									<div class="form-select">
										<div class="select2-field select2-multiple">
											<select id="ai_language" data-placeholder="<?php esc_attr_e('Select language', 'civi-framework'); ?>" class="civi-select2" name="ai_language">
												<?php
												if (language_ai_helper()) {
													$ai_language = civi_get_option('ai_language');
													foreach (language_ai_helper() as $language_ai_helper_key => $language_ai_helper_value) {
												?>
														<option value="<?php echo esc_attr($language_ai_helper_key); ?>" <?php if ($ai_language == $language_ai_helper_key) {
																																																echo 'selected';
																																															} ?>><?php echo esc_html($language_ai_helper_value); ?></option>
												<?php
													}
												}
												?>
											</select>
										</div>
									</div>
								</div>
								<div class="field-notice">
									<p></p>
								</div>
								<div class="field-submit">
									<button class="civi-button button-outline">
										<span class="text"><?php esc_html_e('Generate', 'civi-framework') ?></span>
										<span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
									</button>
								</div>
							</form>
						</div>
						<div class="right">
							<h5><?php esc_html_e('Suggestion', 'civi-framework'); ?></h5>
							<div class="suggestion"></div>
							<a href="#" class="civi-button keep-generate"><i class="fal fa-check"></i><?php esc_html_e('Keep', 'civi-framework'); ?></a>
						</div>
					</div>

				</div>
			</div>
<?php
		}

		// Define custom "From" name
		public function custom_wp_mail_from_name($name)
		{
			return get_bloginfo();
		}

		public function handle_auto_check_expired_package($user_id)
		{
			$args = array(
				'post_type'      => 'jobs',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'author'         => $user_id,
			);

			$query = new WP_Query($args);

			if ($query->have_posts()) {
				while ($query->have_posts()) {
					$query->the_post();
					$job_data = array(
						'ID'          => get_the_ID(),
						'post_status' => 'expired',
					);

					wp_update_post($job_data, true);
				}
				wp_reset_postdata();
			}
		}

		/**
		 * Enqueue Flatpickr globally with smart locale detection
		 * @param string $min_suffix
		 */
		private function enqueue_flatpickr($min_suffix = '')
		{
			wp_enqueue_style('flatpickr', CIVI_PLUGIN_URL . 'assets/libs/flatpickr/css/flatpickr.min.css', array(), '4.6.13', 'all');
			wp_enqueue_script('flatpickr', CIVI_PLUGIN_URL . 'assets/libs/flatpickr/js/flatpickr.min.js', array('jquery'), '4.6.13', true);

			wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'flatpickr-init', CIVI_PLUGIN_URL . 'assets/js/flatpickr-init' . $min_suffix . '.js', array('flatpickr', 'jquery'), CIVI_PLUGIN_VER, true);

			$locale = get_locale();
			$locale_lang = substr($locale, 0, 2);

			if (function_exists('pll_current_language')) {
				$polylang_lang = pll_current_language('slug');
				if ($polylang_lang) {
					$locale_lang = substr($polylang_lang, 0, 2);
				}
			} else if (defined('ICL_SITEPRESS_VERSION')) {
				$wpml_lang = apply_filters('wpml_current_language', null);
				if ($wpml_lang) {
					$locale_lang = substr($wpml_lang, 0, 2);
				}
			}

			if (isset($_GET['flatpickr_locale']) && !empty($_GET['flatpickr_locale'])) {
				$test_locale = sanitize_text_field($_GET['flatpickr_locale']);
				$locale_lang = $test_locale;
			}

			$l10n_base_dir = CIVI_PLUGIN_DIR . 'assets/libs/flatpickr/l10n/';
			$l10n_base_url = CIVI_PLUGIN_URL . 'assets/libs/flatpickr/l10n/';

			$locale_map = array(
				'zh' => 'zh',
				'zh-tw' => 'zh-tw',
				'zh-cn' => 'zh',
				'vi' => 'vn',
				'vn' => 'vn',
				'ja' => 'ja',
				'ko' => 'ko',
				'th' => 'th',
				'id' => 'id',
				'fr' => 'fr',
				'de' => 'de',
				'es' => 'es',
				'es-mx' => 'es',
				'es-ar' => 'es',
				'es-co' => 'es',
				'es-cl' => 'es',
				'es-pe' => 'es',
				'es-ve' => 'es',
				'es-cr' => 'es',
				'es-ec' => 'es',
				'es-gt' => 'es',
				'es-pa' => 'es',
				'es-do' => 'es',
				'es-cu' => 'es',
				'es-bo' => 'es',
				'es-hn' => 'es',
				'es-ni' => 'es',
				'es-py' => 'es',
				'es-sv' => 'es',
				'es-uy' => 'es',
				'it' => 'it',
				'pt' => 'pt',
				'pt-br' => 'pt',
				'pt-pt' => 'pt',
				'ru' => 'ru',
				'nl' => 'nl',
				'pl' => 'pl',
				'tr' => 'tr',
				'ar' => 'ar',
				'he' => 'he',
				'af' => 'af',
				'sw' => 'sw',
				'af-za' => 'af',
				'zu' => 'af',
				'xh' => 'af',
				'bn' => 'bn',
				'bn-bd' => 'bn',
				'bn_BD' => 'bn',
				'bd' => 'bn',
			);

			$cdn_base = 'https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/';

			$locale_file_name = null;
			$locale_loaded = false;

			$check_local_file = function($file_name) use ($l10n_base_dir, $l10n_base_url, &$locale_loaded) {
				if ($file_name && file_exists($l10n_base_dir . $file_name . '.js')) {
					wp_enqueue_script('flatpickr-l10n', $l10n_base_url . $file_name . '.js', array('flatpickr'), '4.6.13', true);
					wp_script_add_data('flatpickr-l10n', 'crossorigin', 'anonymous');
					$locale_loaded = true;
					return true;
				}
				return false;
			};

			if ($check_local_file($locale_lang)) {
			} else if ($check_local_file($locale)) {
			} else {
				if (isset($locale_map[$locale_lang])) {
					$locale_file_name = $locale_map[$locale_lang];
				} else if (strpos($locale_lang, 'zh') === 0 || strpos($locale, 'zh') === 0) {
					$locale_file_name = 'zh';
				} else if (strpos($locale_lang, 'es-') === 0 || strpos($locale_lang, 'es_') === 0) {
					$locale_file_name = 'es';
				} else if (strpos($locale_lang, 'pt-') === 0 || strpos($locale_lang, 'pt_') === 0) {
					$locale_file_name = 'pt';
				} else if (strpos($locale_lang, 'af') === 0 || $locale_lang === 'zu' || $locale_lang === 'xh') {
					$locale_file_name = 'af';
				} else if (strpos($locale_lang, 'bn') === 0 || $locale_lang === 'bd') {
					$locale_file_name = 'bn';
				}

				if ($locale_file_name && $check_local_file($locale_file_name)) {
				} else if (strpos($locale_lang, 'en-ng') === 0 || strpos($locale_lang, 'en_NG') === 0 || $locale_lang === 'ng' || strpos($locale_lang, 'en-zw') === 0 || strpos($locale_lang, 'en_ZW') === 0 || $locale_lang === 'zw') {
				} else if ($locale_file_name) {
					$cdn_url = $cdn_base . $locale_file_name . '.js';
					wp_enqueue_script('flatpickr-l10n', $cdn_url, array('flatpickr'), '4.6.13', true);
					wp_script_add_data('flatpickr-l10n', 'crossorigin', 'anonymous');
				}
			}

			$date_format = get_option('date_format');
			wp_localize_script(CIVI_PLUGIN_PREFIX . 'flatpickr-init', 'civi_flatpickr_vars', array(
				'locale' => $locale_lang,
				'wp_locale' => $locale,
				'date_format' => $date_format,
			));

			global $civi_template_vars;
			if (!isset($civi_template_vars)) {
				$civi_template_vars = array();
			}
			$civi_template_vars['flatpickr_locale'] = $locale_lang;
			$civi_template_vars['wp_locale'] = $locale;
		}
	}
}
?>
