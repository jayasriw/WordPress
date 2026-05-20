<?php

if (!defined("ABSPATH")) {
	exit();
}

if (!class_exists("Civi_Enqueue")) {
	/**
	 *  Class Civi_Enqueue
	 */
	class Civi_Enqueue
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

			if (!class_exists('Civi_Framework')) {
				wp_enqueue_style(
					'font-awesome-all',
					CIVI_THEME_URI . '/assets/fonts/font-awesome/css/fontawesome-all.min.css',
					array(),
					'5.10.0',
					'all'
				);
			}
			wp_enqueue_style(
				'line-awesome-min',
				CIVI_THEME_URI . '/assets/fonts/line-awesome/css/line-awesome.min.css',
				array(),
				'1.1.0',
				'all'
			);

			wp_enqueue_style(
				"slick",
				CIVI_THEME_URI . "/assets/libs/slick/slick.css",
				[],
				"1.8.1",
				"all"
			);

			wp_enqueue_style(
				"slick-theme",
				CIVI_THEME_URI . "/assets/libs/slick/slick-theme.css",
				[],
				"1.8.1",
				"all"
			);

			wp_enqueue_style(
				"civi-swiper",
				CIVI_THEME_URI . "/assets/libs/swiper/css/swiper.min.css",
				[],
				"5.3.8",
				"all"

			);


			wp_enqueue_style('growl', CIVI_THEME_URI . '/assets/libs/growl/css/jquery.growl.min.css', array(), '1.3.3', 'all');

			/*
			 * Enqueue Theme Styles
			 */
			wp_enqueue_style(
				"civi-font-inter",
				CIVI_THEME_URI . "/assets/fonts/inter/font.min.css"
			);

			$enable_rtl_mode = Civi_Helper::civi_get_option(
				"enable_rtl_mode",
				0
			);
			if (is_rtl() || $enable_rtl_mode) {
				wp_enqueue_style(
					"civi_minify-style",
					CIVI_THEME_URI . "/style-rtl.min.css",
					[],
					CIVI_THEME_VER
				);
				wp_enqueue_style(
					"civi_custom-rtl-style",
					CIVI_THEME_URI . "/assets/scss/rtl/custom-rtl.css",
					[],
					CIVI_THEME_VER
				);
			} else {
				wp_enqueue_style(
					"civi_minify-style",
					CIVI_THEME_URI . "/style.min.css",
					[],
					CIVI_THEME_VER
				);
			}
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
				wp_register_style('civi-el-widget-' . $value, CIVI_ELEMENTOR_URI  . '/assets/scss/' . $value . '.min.css');
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
				CIVI_THEME_URI . "/assets/libs/waypoints/jquery.waypoints.js",
				["jquery"],
				"4.0.1",
				true
			);

			wp_enqueue_script(
				"matchheight",
				CIVI_THEME_URI .
					"/assets/libs/matchHeight/jquery.matchHeight-min.js",
				["jquery"],
				"0.7.0",
				true
			);

			wp_enqueue_script(
				"imagesloaded",
				CIVI_THEME_URI .
					"/assets/libs/imagesloaded/imagesloaded.min.js",
				["jquery"],
				null,
				true
			);

			wp_enqueue_script('growl', CIVI_THEME_URI . '/assets/libs/growl/js/jquery.growl.min.js', array('jquery'), '1.3.3', true);

			wp_register_script(
				"isotope-masonry",
				CIVI_THEME_URI . "/assets/libs/isotope/js/isotope.pkgd.min.js",
				["jquery"],
				"3.0.6",
				true
			);

			wp_register_script(
				"packery-mode",
				CIVI_THEME_URI .
					"/assets/libs/packery-mode/packery-mode.pkgd.min.js",
				["jquery"],
				"3.0.6",
				true
			);

			wp_enqueue_script(
				"validate",
				CIVI_THEME_URI . "/assets/libs/validate/jquery.validate.min.js",
				["jquery"],
				"1.19.5",
				true
			);

			wp_register_script(
				"civi-grid-layout",
				CIVI_THEME_URI . "/assets/js/grid-layout.min.js",
				[
					"jquery",
					"imagesloaded",
					"matchheight",
					"isotope-masonry",
					"packery-mode",
				],
				CIVI_THEME_VER,
				true
			);

			/*
			 * Enqueue Theme Scripts
			 */
			wp_enqueue_script(
				"civi-swiper-wrapper",
				CIVI_THEME_URI . "/assets/js/swiper-wrapper.min.js",
				["jquery"],
				CIVI_THEME_VER,
				true
			);

			$civi_swiper_js = [
				"prevText" => esc_html__("Prev", "civi"),
				"nextText" => esc_html__("Next", "civi"),
			];
			wp_localize_script(
				"civi-swiper-wrapper",
				'$civiSwiper',
				$civi_swiper_js
			);

			wp_enqueue_script(
				"google-gsi-client",
				"https://accounts.google.com/gsi/client",
				[],
				null,
				true
			);

			wp_enqueue_script(
				"civi-main-js",
				CIVI_THEME_URI . "/assets/js/main.js",
				["jquery"],
				CIVI_THEME_VER,
				true
			);

			wp_register_script(
				"civi-swiper",
				CIVI_THEME_URI . "/assets/libs/swiper/js/swiper.min.js",
				["jquery"],
				"5.3.8",
				true
			);

			wp_register_script('civi-group-widget-carousel', CIVI_ELEMENTOR_URI . '/assets/js/widgets/group-widget-carousel.js', array(
				'jquery',
				'civi-swiper',
				'civi-swiper-wrapper',
			), null, true);

			if (!class_exists('Civi_Framework')) {
				wp_enqueue_script(
					"slick",
					CIVI_THEME_URI . "/assets/libs/slick/slick.min.js",
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

			$google_id = Civi_Helper::civi_get_option(
				"google_login_api",
				"406259942299-s0m5o0ecdf8khdiittl1r6cd3pdjqsum.apps.googleusercontent.com"
			);
			$sticky_header = Civi_Helper::get_setting("sticky_header");
			$float_header = Civi_Helper::get_setting("float_header");
			$currency_position = Civi_Helper::civi_get_option('currency_position');

			wp_localize_script("civi-main-js", "theme_vars", [
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
				"salary_text_minimum" => Civi_Helper::civi_get_option('salary_text_minimum', esc_html__('Min: ', 'civi')),
				"salary_text_maximum" => Civi_Helper::civi_get_option('salary_text_maximum', esc_html__('Max: ', 'civi')),
				"salary_text_negotiable" => Civi_Helper::civi_get_option('salary_text_negotiable', esc_html__('Negotiable Price', 'civi')),
				"thousand_separator" => Civi_Helper::civi_get_option('thousand_separator', ','),
				"decimal_separator" => Civi_Helper::civi_get_option('decimal_separator', '.'),
				"send_user_info" => esc_html__("Sending user info, please wait...", "civi"),
				"forget_password" => esc_html__("Checking your email, please wait...", "civi"),
				"change_password" => esc_html__("Checking your password, please wait...", "civi"),
				"coupon_applied" => esc_html__("Coupon code already applied", "civi"),
				"notice_cookie_enable" => Civi_Helper::civi_get_option('enable_cookie'),
				"enable_search_box_dropdown" => Civi_Helper::civi_get_option('enable_search_box_dropdown'),
				"limit_search_box" => Civi_Helper::civi_get_option('limit_search_box') ? intval(Civi_Helper::civi_get_option('limit_search_box')) : 0,
				"notice_cookie_confirm" => isset($_COOKIE["notice_cookie_confirm"]) ? "yes" : "no",
				"notice_cookie_messages" => Civi_Cookie::instance()->get_notice_cookie_messages(),
				"sticky_header" => $sticky_header,
				"float_header" => $float_header,

				// Form Validation - General Messages
				"required" => esc_html__("This field is required", "civi"),
				"remote" => esc_html__("Please fix this field", "civi"),
				"email" => esc_html__("A valid email address is required", "civi"),
				"date" => esc_html__("Please enter a valid date", "civi"),
				"dateISO" => esc_html__("Please enter a valid date (ISO)", "civi"),
				"number" => esc_html__("Please enter a valid number.", "civi"),
				"digits" => esc_html__("Please enter only digits", "civi"),
				"creditcard" => esc_html__("Please enter a valid credit card number", "civi"),
				"equalTo" => esc_html__("Please enter the same value again", "civi"),
				"accept" => esc_html__("Please enter a value with a valid extension", "civi"),
				"maxlength" => esc_html__("Please enter no more than {0} characters", "civi"),
				"minlength" => esc_html__("Please enter at least {0} characters", "civi"),
				"rangelength" => esc_html__("Please enter a value between {0} and {1} characters long", "civi"),
				"range" => esc_html__("Please enter a value between {0} and {1}", "civi"),
				"max" => esc_html__("Please enter a value less than or equal to {0}", "civi"),
				"min" => esc_html__("Please enter a value greater than or equal to {0}", "civi"),
				"invalid_phone" => esc_html__("Please enter a valid phone number.", "civi"),
				"no_special_chars" => esc_html__("Please enter a value without special characters or spaces", "civi"),

				// Form Register - Specific Field Messages
				"reg_company_name_noSpecialChars" => esc_html__("Username can only contain letters and numbers, no special characters or spaces.", "civi"),
				"reg_email_email" => esc_html__("Please enter a valid email address.", "civi"),
				"reg_phone_phoneNumber" => esc_html__("Please enter a valid phone number.", "civi"),
				"reg_password_minlength" => esc_html__("Password must be at least 5 characters long.", "civi"),
				"reg_password_maxlength" => esc_html__("Password cannot exceed 32 characters.", "civi"),
				"accept_account_required" => esc_html__("You must accept the terms and privacy policy.", "civi"),

				// Other Messages
				"login_error" => esc_html__("Username or password is wrong. Please try again", "civi"),
				"captcha_failed" => esc_html__("Captcha failed", "civi"),
				"captcha_success" => esc_html__("Captcha success", "civi"),
				"login_success" => esc_html__("Login success", "civi"),
				"waiting_approval" => esc_html__("Account is waiting for admin approval", "civi"),
				"username_already" => esc_html__("Username already", "civi"),
				"email_already" => esc_html__("Email already exists", "civi"),
				"username_email_existing" => esc_html__("Username/Email address is existing", "civi"),

			// reCAPTCHA Messages
			"recaptcha_verification_failed" => esc_html__("reCAPTCHA verification failed. Please try again.", "civi"),
			"recaptcha_complete_verification" => esc_html__("Please complete the reCAPTCHA verification.", "civi"),
			"recaptcha_not_configured" => esc_html__("reCAPTCHA is not properly configured.", "civi"),
			"recaptcha_invalid_secret_key" => esc_html__("reCAPTCHA secret key is invalid or missing. Please check your reCAPTCHA configuration.", "civi"),
			"recaptcha_invalid_response" => esc_html__("reCAPTCHA verification token is invalid or expired. Please try again.", "civi"),
			"recaptcha_timeout" => esc_html__("reCAPTCHA verification token has expired. Please refresh the page and try again.", "civi"),
			"recaptcha_bad_request" => esc_html__("reCAPTCHA request is invalid. Please check your configuration.", "civi"),
			"recaptcha_error" => esc_html__("reCAPTCHA verification failed. Please try again.", "civi"),

				// AJAX Error Messages
				"ajax_error" => esc_html__("An error occurred. Please try again.", "civi"),
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
