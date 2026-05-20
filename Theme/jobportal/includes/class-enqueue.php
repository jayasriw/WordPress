<?php

if (!defined("ABSPATH")) {
	exit();
}

if (!class_exists("JobPortal_Enqueue")) {
	/**
	 *  Class JobPortal_Enqueue
	 */
	class JobPortal_Enqueue
	{
		/**
		 * The constructor.
		 */
		function __construct()
		{
			add_action("wp_enqueue_scripts", [$this, "enqueue_styles"]);
			add_action("wp_enqueue_scripts", [$this, "enqueue_scripts"]);

			add_action("wp_enqueue_scripts", [$this, "el_register_styles"]);
		}

		/**
		 * Register the stylesheets for the public-facing side of the site.
		 */
		public function enqueue_styles()
		{
			/*
			 * Enqueue Third Party Styles
			 */

			if (!class_exists('JobPortal_Framework')) {
				wp_enqueue_style(
					'font-awesome-all',
					JOBPORTAL_THEME_URI . '/assets/fonts/font-awesome/css/fontawesome-all.min.css',
					array(),
					'5.10.0',
					'all'
				);
			}
			wp_enqueue_style(
				'line-awesome-min',
				JOBPORTAL_THEME_URI . '/assets/fonts/line-awesome/css/line-awesome.min.css',
				array(),
				'1.1.0',
				'all'
			);

			wp_enqueue_style(
				"slick",
				JOBPORTAL_THEME_URI . "/assets/libs/slick/slick.css",
				[],
				"1.8.1",
				"all"
			);

			wp_enqueue_style(
				"slick-theme",
				JOBPORTAL_THEME_URI . "/assets/libs/slick/slick-theme.css",
				[],
				"1.8.1",
				"all"
			);

			wp_enqueue_style(
				"jobportal-swiper",
				JOBPORTAL_THEME_URI . "/assets/libs/swiper/css/swiper.min.css",
				[],
				"5.3.8",
				"all"

			);


			wp_enqueue_style('growl', JOBPORTAL_THEME_URI . '/assets/libs/growl/css/jquery.growl.min.css', array(), '1.3.3', 'all');

			/*
			 * Enqueue Theme Styles
			 */
			wp_enqueue_style(
				"jobportal-font-inter",
				JOBPORTAL_THEME_URI . "/assets/fonts/inter/font.min.css"
			);

			$enable_rtl_mode = JobPortal_Helper::jobportal_get_option(
				"enable_rtl_mode",
				0
			);
			if (is_rtl() || $enable_rtl_mode) {
				wp_enqueue_style(
					"jobportal_minify-style",
					JOBPORTAL_THEME_URI . "/style-rtl.min.css",
					[],
					JOBPORTAL_THEME_VER
				);
				wp_enqueue_style(
					"jobportal_custom-rtl-style",
					JOBPORTAL_THEME_URI . "/assets/scss/rtl/custom-rtl.css",
					[],
					JOBPORTAL_THEME_VER
				);
			} else {
				wp_enqueue_style(
					"jobportal_minify-style",
					JOBPORTAL_THEME_URI . "/style.min.css",
					[],
					JOBPORTAL_THEME_VER
				);
			}

			// Enqueue JobPortal color scheme overrides
			wp_enqueue_style(
				"jobportal-color-scheme",
				JOBPORTAL_THEME_URI . "/assets/css/color-scheme.css",
				["jobportal_minify-style"],
				JOBPORTAL_THEME_VER
			);
		}


		public function el_register_styles()
		{
			$style = [
				'accordion',
				'accordion-image',
				'attribute-list',
				'banner',
				'blog',
				'circle-progress-chart',
				'client-logo',
				'contact-form-7',
				'fancy-heading',
				'flip-box',
				'google-map',
				'gradation',
				'heading',
				'icon',
				'icon-box',
				'number-box',
				'user-form',
				'job-search',
				'image-animation',
				'image-box',
				'image-carousel',
				'image-gallery',
				'image-layers',
				'image-rotate',
				'instagram',
				'list',
				'mailchimp-form',
				'modern-carousel',
				'modern-menu',
				'modern-slider',
				'freelancer-carousel',
				'modern-tabs',
				'popup-video',
				'pricing',
				'separator',
				'shapes',
				'social-networks',
				'table',
				'account',
				'team-member',
				'team-member-carousel',
				'testimonial-carousel',
				'testimonial-grid',
				'timeline',
				'twitter',
				'morphing',
				'view-demo'
			];

			foreach ($style as $key => $value) {
				wp_register_style("jobportal-el-widget-' . $value, JOBPORTAL_ELEMENTOR_URI  . '/assets/scss/' . $value . '.min.css');
			}
		}

		/**
		 * Register the JavaScript for the admin area.
		 */
		public function enqueue_scripts()
		{
			/*
			 * Enqueue Third Party Scripts
			 */

			wp_enqueue_script(
				"waypoints",
				JOBPORTAL_THEME_URI . "/assets/libs/waypoints/jquery.waypoints.js",
				["jquery"],
				"4.0.1",
				true
			);

			wp_enqueue_script(
				"matchheight",
				JOBPORTAL_THEME_URI .
					"/assets/libs/matchHeight/jquery.matchHeight-min.js",
				["jquery"],
				"0.7.0",
				true
			);

			wp_enqueue_script(
				"imagesloaded",
				JOBPORTAL_THEME_URI .
					"/assets/libs/imagesloaded/imagesloaded.min.js",
				["jquery"],
				null,
				true
			);

			wp_enqueue_script('growl', JOBPORTAL_THEME_URI . '/assets/libs/growl/js/jquery.growl.min.js', array('jquery'), '1.3.3', true);

			wp_register_script(
				"isotope-masonry",
				JOBPORTAL_THEME_URI . "/assets/libs/isotope/js/isotope.pkgd.min.js",
				["jquery"],
				"3.0.6",
				true
			);

			wp_register_script(
				"packery-mode",
				JOBPORTAL_THEME_URI .
					"/assets/libs/packery-mode/packery-mode.pkgd.min.js",
				["jquery"],
				"3.0.6",
				true
			);

			wp_enqueue_script(
				"validate",
				JOBPORTAL_THEME_URI . "/assets/libs/validate/jquery.validate.min.js",
				["jquery"],
				"1.19.5",
				true
			);

			wp_register_script(
				"jobportal-grid-layout",
				JOBPORTAL_THEME_URI . "/assets/js/grid-layout.min.js",
				[
					"jquery",
					"imagesloaded",
					"matchheight",
					"isotope-masonry",
					"packery-mode",
				],
				JOBPORTAL_THEME_VER,
				true
			);

			/*
			 * Enqueue Theme Scripts
			 */
			wp_enqueue_script(
				"jobportal-swiper-wrapper",
				JOBPORTAL_THEME_URI . "/assets/js/swiper-wrapper.min.js",
				["jquery"],
				JOBPORTAL_THEME_VER,
				true
			);

			$jobportal_swiper_js = [
				"prevText" => esc_html__("Prev", "jobportal"),
				"nextText" => esc_html__("Next", "jobportal"),
			];
			wp_localize_script(
				"jobportal-swiper-wrapper",
				'$jobportalSwiper',
				$jobportal_swiper_js
			);

			wp_enqueue_script(
				"google-gsi-client",
				"https://accounts.google.com/gsi/client",
				[],
				null,
				true
			);

			wp_enqueue_script(
				"jobportal-main-js",
				JOBPORTAL_THEME_URI . "/assets/js/main.js",
				["jquery"],
				JOBPORTAL_THEME_VER,
				true
			);

			wp_register_script(
				"jobportal-swiper",
				JOBPORTAL_THEME_URI . "/assets/libs/swiper/js/swiper.min.js",
				["jquery"],
				"5.3.8",
				true
			);

			wp_register_script("jobportal-group-widget-carousel', JOBPORTAL_ELEMENTOR_URI . '/assets/js/widgets/group-widget-carousel.js', array(
				'jquery',
				"jobportal-swiper',
				"jobportal-swiper-wrapper',
			), null, true);

			if (!class_exists('JobPortal_Framework')) {
				wp_enqueue_script(
					"slick",
					JOBPORTAL_THEME_URI . "/assets/libs/slick/slick.min.js",
					["jquery"],
					"1.8.1",
					true
				);
			}


			$ajax_url = admin_url("admin-ajax.php");
			$current_lang = apply_filters("wpml_current_language", null);

			if ($current_lang) {
				$ajax_url = add_query_arg("lang", $current_lang, $ajax_url);
			}

			$google_id = JobPortal_Helper::jobportal_get_option(
				"google_login_api",
				"406259942299-s0m5o0ecdf8khdiittl1r6cd3pdjqsum.apps.googleusercontent.com"
			);
			$sticky_header = JobPortal_Helper::get_setting("sticky_header");
			$float_header = JobPortal_Helper::get_setting("float_header");
			$currency_position = JobPortal_Helper::jobportal_get_option('currency_position');

			wp_localize_script("jobportal-main-js", "theme_vars", [
				"ajax_url" => esc_url($ajax_url),
				"login_nonce" => wp_create_nonce("login_nonce"),
				"register_nonce" => wp_create_nonce("register_nonce"),
				"verify_code_nonce" => wp_create_nonce("verify_code_nonce"),
				"verify_resend_nonce" => wp_create_nonce("verify_resend_nonce"),
				"fb_login_nonce" => wp_create_nonce("fb_login_nonce"),
				"google_login_nonce" => wp_create_nonce("google_login_nonce"),
				"change_password_nonce" => wp_create_nonce("change_password_nonce"),
				"social_login_nonce" => wp_create_nonce("social_login_nonce"),
				"create_company_nonce" => wp_create_nonce("create_company_nonce"),
				"write_message_nonce" => wp_create_nonce("write_message_nonce"),
				"send_message_nonce" => wp_create_nonce("send_message_nonce"),
				"job_apply_nonce" => wp_create_nonce("job_apply_nonce"),
				"filter_apply_nonce" => wp_create_nonce("filter_apply_nonce"),
				"filter_jobs_nonce" => wp_create_nonce("filter_jobs_nonce"),
				"currency_position" => $currency_position,
				"google_id" => $google_id,
				"salary_text_minimum" => JobPortal_Helper::jobportal_get_option('salary_text_minimum', esc_html__('Min: ', 'jobportal')),
				"salary_text_maximum" => JobPortal_Helper::jobportal_get_option('salary_text_maximum', esc_html__('Max: ', 'jobportal')),
				"salary_text_negotiable" => JobPortal_Helper::jobportal_get_option('salary_text_negotiable', esc_html__('Negotiable Price', 'jobportal')),
				"thousand_separator" => JobPortal_Helper::jobportal_get_option('thousand_separator', ','),
				"decimal_separator" => JobPortal_Helper::jobportal_get_option('decimal_separator', '.'),
				"send_user_info" => esc_html__("Sending user info, please wait...", "jobportal"),
				"forget_password" => esc_html__("Checking your email, please wait...", "jobportal"),
				"change_password" => esc_html__("Checking your password, please wait...", "jobportal"),
				"coupon_applied" => esc_html__("Coupon code already applied", "jobportal"),
				"notice_cookie_enable" => JobPortal_Helper::jobportal_get_option('enable_cookie'),
				"enable_search_box_dropdown" => JobPortal_Helper::jobportal_get_option('enable_search_box_dropdown'),
				"limit_search_box" => JobPortal_Helper::jobportal_get_option('limit_search_box') ? intval(JobPortal_Helper::jobportal_get_option('limit_search_box')) : 0,
				"notice_cookie_confirm" => isset($_COOKIE["notice_cookie_confirm"]) ? "yes" : "no",
				"notice_cookie_messages" => JobPortal_Cookie::instance()->get_notice_cookie_messages(),
				"sticky_header" => $sticky_header,
				"float_header" => $float_header,

				// Form Validation - General Messages
				"required" => esc_html__("This field is required", "jobportal"),
				"remote" => esc_html__("Please fix this field", "jobportal"),
				"email" => esc_html__("A valid email address is required", "jobportal"),
				"date" => esc_html__("Please enter a valid date", "jobportal"),
				"dateISO" => esc_html__("Please enter a valid date (ISO)", "jobportal"),
				"number" => esc_html__("Please enter a valid number.", "jobportal"),
				"digits" => esc_html__("Please enter only digits", "jobportal"),
				"creditcard" => esc_html__("Please enter a valid credit card number", "jobportal"),
				"equalTo" => esc_html__("Please enter the same value again", "jobportal"),
				"accept" => esc_html__("Please enter a value with a valid extension", "jobportal"),
				"maxlength" => esc_html__("Please enter no more than {0} characters", "jobportal"),
				"minlength" => esc_html__("Please enter at least {0} characters", "jobportal"),
				"rangelength" => esc_html__("Please enter a value between {0} and {1} characters long", "jobportal"),
				"range" => esc_html__("Please enter a value between {0} and {1}", "jobportal"),
				"max" => esc_html__("Please enter a value less than or equal to {0}", "jobportal"),
				"min" => esc_html__("Please enter a value greater than or equal to {0}", "jobportal"),
				"invalid_phone" => esc_html__("Please enter a valid phone number.", "jobportal"),
				"no_special_chars" => esc_html__("Please enter a value without special characters or spaces", "jobportal"),

				// Form Register - Specific Field Messages
				"reg_company_name_noSpecialChars" => esc_html__("Username can only contain letters and numbers, no special characters or spaces.", "jobportal"),
				"reg_email_email" => esc_html__("Please enter a valid email address.", "jobportal"),
				"reg_phone_phoneNumber" => esc_html__("Please enter a valid phone number.", "jobportal"),
				"reg_password_minlength" => esc_html__("Password must be at least 5 characters long.", "jobportal"),
				"reg_password_maxlength" => esc_html__("Password cannot exceed 32 characters.", "jobportal"),
				"accept_account_required" => esc_html__("You must accept the terms and privacy policy.", "jobportal"),

				// Other Messages
				"login_error" => esc_html__("Username or password is wrong. Please try again", "jobportal"),
				"captcha_failed" => esc_html__("Captcha failed", "jobportal"),
				"captcha_success" => esc_html__("Captcha success", "jobportal"),
				"login_success" => esc_html__("Login success", "jobportal"),
				"waiting_approval" => esc_html__("Account is waiting for admin approval", "jobportal"),
				"username_already" => esc_html__("Username already", "jobportal"),
				"email_already" => esc_html__("Email already exists", "jobportal"),
				"username_email_existing" => esc_html__("Username/Email address is existing", "jobportal"),

			// reCAPTCHA Messages
			"recaptcha_verification_failed" => esc_html__("reCAPTCHA verification failed. Please try again.", "jobportal"),
			"recaptcha_complete_verification" => esc_html__("Please complete the reCAPTCHA verification.", "jobportal"),
			"recaptcha_not_configured" => esc_html__("reCAPTCHA is not properly configured.", "jobportal"),
			"recaptcha_invalid_secret_key" => esc_html__("reCAPTCHA secret key is invalid or missing. Please check your reCAPTCHA configuration.", "jobportal"),
			"recaptcha_invalid_response" => esc_html__("reCAPTCHA verification token is invalid or expired. Please try again.", "jobportal"),
			"recaptcha_timeout" => esc_html__("reCAPTCHA verification token has expired. Please refresh the page and try again.", "jobportal"),
			"recaptcha_bad_request" => esc_html__("reCAPTCHA request is invalid. Please check your configuration.", "jobportal"),
			"recaptcha_error" => esc_html__("reCAPTCHA verification failed. Please try again.", "jobportal"),

				// AJAX Error Messages
				"ajax_error" => esc_html__("An error occurred. Please try again.", "jobportal"),
			]);

			/*
			 * The comment-reply script.
			 */
			if (
				is_singular() &&
				comments_open() &&
				get_option("thread_comments")
			) {
				wp_enqueue_script("comment-reply");
			}
		}
	}
}
