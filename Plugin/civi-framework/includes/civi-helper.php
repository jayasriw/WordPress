<?php

/**
 * Get Option - Cached version for better performance
 */
if (!function_exists('civi_get_option')) {
	function civi_get_option($key, $default = '')
	{
		// Static cache to avoid repeated database queries
		static $options_cache = array();

		// Determine the option name based on language settings
		if (function_exists('pll_the_languages')) {
			$option_name = pll_current_language() . '_civi-framework';
		} else if (defined('ICL_SITEPRESS_VERSION')) {
			$current_language = apply_filters('wpml_current_language', NULL);
			$option_name = $current_language ? $current_language . '_civi-framework' : 'civi-framework';
		} else {
			$option_name = 'civi-framework';
		}

		// Check cache first
		if (!isset($options_cache[$option_name])) {
			$options_cache[$option_name] = get_option($option_name);
		}

		$option = $options_cache[$option_name];

		return (isset($option[$key])) ? apply_filters('civi/get_option', $option[$key], $option, $key) : $default;
	}
}

/**
 * Clear civi options cache - call when options are updated
 */
if (!function_exists('civi_clear_options_cache')) {
	function civi_clear_options_cache()
	{
		// Force cache refresh on next call
		global $civi_options_cleared;
		$civi_options_cleared = true;
	}
	add_action('update_option_civi-framework', 'civi_clear_options_cache');
}

/**
 * Check nonce
 *
 * @param string $action Action name.
 * @param string $nonce Nonce.
 */
if (!function_exists('verify_nonce')) {
	function verify_nonce($action = '', $nonce = '')
	{

		if (!$nonce && isset($_REQUEST['_wpnonce'])) {
			$nonce = sanitize_text_field(wp_unslash($_REQUEST['_wpnonce']));
		}

		return wp_verify_nonce($nonce, $action);
	}
}

/**
 * Check theme support
 */
if (!function_exists('is_theme_support')) {
	function is_theme_support()
	{
		return current_theme_supports('civi-framework');
	}
}

/**
 * Check has shortcode
 */
if (!function_exists('civi_page_shortcode')) {
	function civi_page_shortcode($shortcode = null)
	{

		$post = get_post(get_the_ID());

		$found = false;

		if (empty($post->post_content)) {
			return $found;
		}

		if (wp_strip_all_tags($post->post_content) === $shortcode) {
			$found = true;
		}

		// return our final results
		return $found;
	}
}

/**
 * Insert custom header script.
 *
 * @return void
 */
function civi_custom_header_js()
{
	if (civi_get_option('header_script', '') && !is_admin()) {
		echo civi_get_option('header_script', ''); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

add_action('wp_head', 'civi_custom_header_js', 99);

/**
 * Insert custom footer script.
 *
 * @return void
 */
function civi_footer_scripts()
{
	echo do_shortcode(civi_get_option('footer_script', ''));
}

add_action('wp_footer', 'civi_footer_scripts');

/**
 * Convert text to 1 line
 *
 * @param $str
 *
 * @return string
 */
if (!function_exists('text2line')) {
	function text2line($str)
	{
		return trim(preg_replace("/[\r\v\n\t]*/", '', $str));
	}
}

/**
 * Get template part (for templates like the shop-loop).
 *
 * @param mixed $slug
 * @param string $name (default: '')
 */
if (!function_exists('civi_get_template_part')) {
	function civi_get_template_part($slug, $name = '')
	{
		$template = '';
		if ($name) {
			$template = locate_template(array(
				"{$slug}-{$name}.php",
				CIVI()->template_path() . "{$slug}-{$name}.php"
			));
		}

		// Get default slug-name.php
		if (!$template && $name && file_exists(CIVI_PLUGIN_DIR . "templates/{$slug}-{$name}.php")) {
			$template = CIVI_PLUGIN_DIR . "templates/{$slug}-{$name}.php";
		}

		if (!$template) {
			$template = locate_template(array("{$slug}.php", CIVI()->template_path() . "{$slug}.php"));
		}

		// Allow 3rd party plugins to filter template file from their plugin.
		$template = apply_filters('civi_get_template_part', $template, $slug, $name);

		if ($template) {
			load_template($template, false);
		}
	}
}

/**
 * Get other templates (e.g. product attributes) passing attributes and including the file.
 */
if (!function_exists('civi_get_template')) {
	function civi_get_template($template_name, $args = array(), $template_path = '', $default_path = '')
	{
		if (!empty($args) && is_array($args)) {
			extract($args);
		}

		$located = civi_locate_template($template_name, $template_path, $default_path);

		if (!file_exists($located)) {
			_doing_it_wrong(__FUNCTION__, sprintf('<code>%s</code> does not exist.', $located), '2.1');

			return;
		}

		// Allow 3rd party plugin filter template file from their plugin.
		$located = apply_filters('civi_get_template', $located, $template_name, $args, $template_path, $default_path);

		do_action('civi_before_template_part', $template_name, $template_path, $located, $args);

		include($located);

		do_action('civi_after_template_part', $template_name, $template_path, $located, $args);
	}
}

/**
 * Like civi_get_template, but returns the HTML instead of outputting.
 */
if (!function_exists('civi_get_template_html')) {
	function civi_get_template_html($template_name, $args = array(), $template_path = '', $default_path = '')
	{
		ob_start();
		civi_get_template($template_name, $args, $template_path, $default_path);

		return ob_get_clean();
	}
}

/**
 * Send email
 */
if (!function_exists('civi_send_email')) {
	function civi_send_email($email, $email_type, $args = array())
	{
		$content = civi_get_option($email_type, '');
		$subject = civi_get_option('subject_' . $email_type, '');

		if (empty($content) || empty($subject)) {
			$fallback_options = get_option('civi-framework');
			if (empty($content) && is_array($fallback_options) && isset($fallback_options[$email_type])) {
				$content = $fallback_options[$email_type];
			}
			if (empty($subject) && is_array($fallback_options) && isset($fallback_options['subject_' . $email_type])) {
				$subject = $fallback_options['subject_' . $email_type];
			}
		}

		if (function_exists('icl_translate')) {
			$content = icl_translate('civi-framework', 'civi_email_' . $content, $content);
			$subject = icl_translate('civi-framework', 'civi_email_subject_' . $subject, $subject);
		}
		$args['website_url']  = get_option('siteurl');
		$args['website_name'] = get_option('blogname');
		$user                 = get_user_by('email', $email);
		if (!empty($user)) {
			$args['username'] = $user->user_login;
		}

		// Alias for templates that use {job_apply} / %job_apply (employer "view application" link).
		if (empty($args['job_apply']) && !empty($args['job_link'])) {
			$args['job_apply'] = $args['job_link'];
		}

		// Many email templates use {user_url} / %user_url for the applicant profile; job apply passes applicant_url only.
		if (empty($args['user_url']) && !empty($args['applicant_url'])) {
			$args['user_url'] = $args['applicant_url'];
		}

		// Registration admin email may use %user_email_register; ensure token is populated when only user_email is set.
		if (empty($args['user_email_register']) && !empty($args['user_email'])) {
			$args['user_email_register'] = $args['user_email'];
		}

		// Backward compatibility for registration placeholders.
		// Support both old tokens (%user_login, %user_password)
		// and new tokens (%user_login_register, %user_pass_register).
		if (empty($args['user_login']) && !empty($args['user_login_register'])) {
			$args['user_login'] = $args['user_login_register'];
		}
		if (empty($args['user_login_register']) && !empty($args['user_login'])) {
			$args['user_login_register'] = $args['user_login'];
		}
		if (empty($args['user_password']) && !empty($args['user_pass_register'])) {
			$args['user_password'] = $args['user_pass_register'];
		}
		if (empty($args['user_pass_register']) && !empty($args['user_password'])) {
			$args['user_pass_register'] = $args['user_password'];
		}

		$keys = array_keys($args);
		usort($keys, function ($a, $b) {
			$la = strlen($a);
			$lb = strlen($b);
			if ($la === $lb) return 0;
			return ($la > $lb) ? -1 : 1;
		});
		foreach ($keys as $key) {
			$val = isset($args[$key]) ? $args[$key] : '';

			// Normalize empty values: treat null, false, empty string, and whitespace as empty
			$is_empty = (empty($val) || (is_string($val) && trim($val) === ''));

			// If value is empty, use fallback text instead of empty string
			if ($is_empty) {
				// Skip fallback for critical fields that should always have values
				if (in_array($key, array('job_title', 'job_url', 'jobs_url', 'website_url', 'website_name'))) {
					// These should always have values, keep empty if missing
					$val = '';
				} elseif ($key === 'cv_url') {
					$val = esc_html__('No CV uploaded', 'civi-framework');
				} elseif ($key === 'message') {
					$val = esc_html__('No message', 'civi-framework');
				} elseif ($key === 'phone') {
					$val = esc_html__('Not provided', 'civi-framework');
				} elseif ($key === 'applicant_email') {
					$val = esc_html__('Not provided', 'civi-framework');
				} elseif ($key === 'applicant_name') {
					// Only show fallback if truly empty (shouldn't happen for logged-in users)
					$val = esc_html__('N/A', 'civi-framework');
				} elseif ($key === 'applicant_url') {
					// Special handling for applicant_url - show "N/A - Guest Applicant" for empty
					$val = esc_html__('N/A - Guest Applicant', 'civi-framework');
				} elseif (strpos($key, 'url') !== false || strpos($key, 'link') !== false) {
					// For URL/link fields, use N/A (but job_link/job_url handled separately)
					if ($key !== 'job_link' && $key !== 'job_url' && $key !== 'jobs_url') {
						$val = esc_html__('N/A', 'civi-framework');
					}
				} else {
					// For other optional fields, use N/A as default
					$val = esc_html__('N/A', 'civi-framework');
				}
			}

			// HTML body: wrap bare http(s) URLs in <a>; subject line stays plain text.
			$val_content = $val;
			if (is_string($val) && $val !== '' && stripos($val, '<a') === false) {
				$t = trim($val);
				if ($t !== '' && preg_match('#\Ahttps?://#i', $t)) {
					$val_content = '<a href="' . esc_url($t) . '">' . esc_html($t) . '</a>';
				}
			}

			// Always replace placeholder, even if value is empty (will be replaced with fallback above).
			// Use boundary-safe % replacement so %user_email does not match inside %user_email_register.
			$pct_pattern = '#%' . preg_quote($key, '#') . '(?![a-z0-9_])#i';
			$subject     = preg_replace_callback($pct_pattern, function () use ($val) {
				return $val;
			}, $subject);
			$content = preg_replace_callback($pct_pattern, function () use ($val_content) {
				return $val_content;
			}, $content);
			$subject = str_replace('{' . $key . '}', $val, $subject);
			$content = str_replace('{' . $key . '}', $val_content, $content);
		}

		$civi_replace_mail_tokens = function (&$subj, &$cont, $token, $replacement) {
			$subj = str_replace(array('%' . $token, '{' . $token . '}'), array($replacement, $replacement), $subj);
			$cont = str_replace(array('%' . $token, '{' . $token . '}'), array($replacement, $replacement), $cont);
		};

		// Support both old and new placeholders for backward compatibility
		// Handle %job_link / {job_link} - create link if not already set
		if (strpos($content, '%job_link') !== false || strpos($content, '{job_link}') !== false
			|| strpos($subject, '%job_link') !== false || strpos($subject, '{job_link}') !== false) {
			if (isset($args['job_link']) && !empty($args['job_link'])) {
				$civi_replace_mail_tokens($subject, $content, 'job_link', $args['job_link']);
			} elseif (isset($args['jobs_apply_link']) && !empty($args['jobs_apply_link'])) {
				$civi_replace_mail_tokens($subject, $content, 'job_link', $args['jobs_apply_link']);
			} elseif (isset($args['job_title']) && isset($args['job_url']) && !empty($args['job_title']) && !empty($args['job_url'])) {
				$linked = '<a href="' . esc_url($args['job_url']) . '">' . esc_html($args['job_title']) . '</a>';
				$civi_replace_mail_tokens($subject, $content, 'job_link', $linked);
			} elseif (isset($args['jobs_apply']) && isset($args['jobs_url']) && !empty($args['jobs_apply']) && !empty($args['jobs_url'])) {
				$linked = '<a href="' . esc_url($args['jobs_url']) . '">' . esc_html($args['jobs_apply']) . '</a>';
				$civi_replace_mail_tokens($subject, $content, 'job_link', $linked);
			} else {
				$civi_replace_mail_tokens($subject, $content, 'job_link', esc_html__('N/A', 'civi-framework'));
			}
		}
		// Handle %jobs_apply_link / {jobs_apply_link}
		if (strpos($content, '%jobs_apply_link') !== false || strpos($content, '{jobs_apply_link}') !== false
			|| strpos($subject, '%jobs_apply_link') !== false || strpos($subject, '{jobs_apply_link}') !== false) {
			if (isset($args['jobs_apply_link']) && !empty($args['jobs_apply_link'])) {
				$civi_replace_mail_tokens($subject, $content, 'jobs_apply_link', $args['jobs_apply_link']);
			} elseif (isset($args['job_link']) && !empty($args['job_link'])) {
				$civi_replace_mail_tokens($subject, $content, 'jobs_apply_link', $args['job_link']);
			} elseif (isset($args['jobs_apply']) && isset($args['jobs_url']) && !empty($args['jobs_apply']) && !empty($args['jobs_url'])) {
				$linked = '<a href="' . esc_url($args['jobs_url']) . '">' . esc_html($args['jobs_apply']) . '</a>';
				$civi_replace_mail_tokens($subject, $content, 'jobs_apply_link', $linked);
			} else {
				$civi_replace_mail_tokens($subject, $content, 'jobs_apply_link', esc_html__('N/A', 'civi-framework'));
			}
		}
		// Handle %jobs_apply / {jobs_apply} (text only, not link)
		$has_jobs_apply_plain = (strpos($content, '%jobs_apply') !== false || strpos($content, '{jobs_apply}') !== false
			|| strpos($subject, '%jobs_apply') !== false || strpos($subject, '{jobs_apply}') !== false);
		$has_jobs_apply_link_tok = (strpos($content, '%jobs_apply_link') !== false || strpos($content, '{jobs_apply_link}') !== false
			|| strpos($subject, '%jobs_apply_link') !== false || strpos($subject, '{jobs_apply_link}') !== false);
		if ($has_jobs_apply_plain && ! $has_jobs_apply_link_tok) {
			if (isset($args['jobs_apply']) && !empty($args['jobs_apply'])) {
				$civi_replace_mail_tokens($subject, $content, 'jobs_apply', $args['jobs_apply']);
			} elseif (isset($args['job_title']) && !empty($args['job_title'])) {
				$civi_replace_mail_tokens($subject, $content, 'jobs_apply', $args['job_title']);
			} else {
				$civi_replace_mail_tokens($subject, $content, 'jobs_apply', esc_html__('N/A', 'civi-framework'));
			}
		}
		if (strpos($content, '%jobs_invite_links') === false && isset($args['jobs_invite']) && isset($args['jobs_invite_links'])) {
			$civi_replace_mail_tokens($subject, $content, 'jobs_invite', $args['jobs_invite_links']);
		}

		// Handle any remaining unmatched placeholders with fallback text
		// This ensures all placeholders are replaced, even if not in $args
		$fallback_placeholders = array(
			'applicant_url' => esc_html__('N/A - Guest Applicant', 'civi-framework'),
			'user_url' => esc_html__('Guest', 'civi-framework'),
			'cv_url' => esc_html__('No CV uploaded', 'civi-framework'),
			'message' => esc_html__('No message', 'civi-framework'),
			'phone' => esc_html__('Not provided', 'civi-framework'),
			'applicant_email' => esc_html__('Not provided', 'civi-framework'),
			'job_url' => esc_html__('N/A', 'civi-framework'),
			'jobs_url' => esc_html__('N/A', 'civi-framework'),
		);

		foreach ($fallback_placeholders as $placeholder => $fallback_text) {
			if (strpos($content, '%' . $placeholder) !== false) {
				$content = str_replace('%' . $placeholder, $fallback_text, $content);
			}
			if (strpos($content, '{' . $placeholder . '}') !== false) {
				$content = str_replace('{' . $placeholder . '}', $fallback_text, $content);
			}
			if (strpos($subject, '%' . $placeholder) !== false) {
				$subject = str_replace('%' . $placeholder, $fallback_text, $subject);
			}
			if (strpos($subject, '{' . $placeholder . '}') !== false) {
				$subject = str_replace('{' . $placeholder . '}', $fallback_text, $subject);
			}
		}

		// Apply wpautop AFTER all placeholders are replaced to preserve HTML
		$content = wpautop($content);

		ob_start();
		civi_get_template("mail/mail.php", array(
			'content' => $content,
		));
		$message = ob_get_clean();

		$headers = apply_filters('civi_contact_mail_header', array(
			'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>',
			'Content-Type: text/html; charset=UTF-8'
		));

		$attachments = array();
		if (array_key_exists('pdf_file', $args)) {
			$parts = explode('/uploads', $args['pdf_file']);
			if (count($parts) > 1) {
				$path_after_uploads = '/uploads' . $parts[1];
				$attachments = array(WP_CONTENT_DIR . $path_after_uploads);
			}
		}

		if (trim(strip_tags($content)) === '' && trim($subject) === '') {
			return;
		}

		@wp_mail(
			$email,
			$subject,
			$message,
			$headers,
			$attachments
		);
	}
}

/**
 * Convert date format
 */
if (!function_exists('civi_convert_date_format')) {
	function civi_convert_date_format($date_string)
	{
		$date_timestamp = strtotime($date_string);
		$formatted_date = date_i18n(get_option('date_format'), $date_timestamp);

		return $formatted_date;
	}
}

if (!function_exists('civi_convert_wp_format_to_flatpickr')) {
	/**
	 * Convert WordPress date format to Flatpickr format
	 *
	 * @param string $wp_format WordPress date format (e.g., "F j, Y", "Y-m-d", "d/m/Y")
	 * @return string Flatpickr date format
	 */
	function civi_convert_wp_format_to_flatpickr($wp_format)
	{
		if (empty($wp_format)) {
			return 'Y-m-d';
		}

		$format_map = array(
			'F j, Y' => 'F j, Y',
			'F j, Y g:i a' => 'F j, Y',
			'Y-m-d' => 'Y-m-d',
			'Y/m/d' => 'Y-m-d',
			'd/m/Y' => 'd/m/Y',
			'd-m-Y' => 'd/m/Y',
			'm/d/Y' => 'm/d/Y',
			'm-d-Y' => 'm/d/Y',
			'd.m.Y' => 'd.m.Y',
			'j F Y' => 'j F Y',
			'j M Y' => 'j M Y',
		);

		if (isset($format_map[$wp_format])) {
			return $format_map[$wp_format];
		}

		if (strpos($wp_format, 'Y') !== false && strpos($wp_format, 'm') !== false && strpos($wp_format, 'd') !== false) {
			$y_pos = strpos($wp_format, 'Y');
			$m_pos = strpos($wp_format, 'm');
			$d_pos = strpos($wp_format, 'd');

			if ($y_pos !== false && $m_pos !== false && $d_pos !== false) {
				if ($y_pos < $m_pos && $m_pos < $d_pos) {
					return 'Y-m-d';
				} else if ($d_pos < $m_pos && $m_pos < $y_pos) {
					return 'd/m/Y';
				} else if ($m_pos < $d_pos && $d_pos < $y_pos) {
					return 'm/d/Y';
				}
			}
		}

		if (strpos($wp_format, '/') !== false) {
			return 'd/m/Y';
		} else if (strpos($wp_format, '-') !== false) {
			return 'Y-m-d';
		} else if (strpos($wp_format, '.') !== false) {
			return 'd.m.Y';
		}

		return 'Y-m-d';
	}
}

if (!function_exists('civi_format_date_by_locale')) {
	/**
	 * Format date according to current locale (matching Flatpickr format)
	 *
	 * @param string $date_string Date string (Y-m-d format or any strtotime compatible)
	 * @param string|null $locale Locale code (optional, auto-detect if not provided)
	 * @return string Formatted date string
	 */
	function civi_format_date_by_locale($date_string, $locale = null)
	{
		if (empty($date_string)) {
			return '';
		}

		$date_timestamp = strtotime($date_string);
		if ($date_timestamp === false) {
			return $date_string;
		}

		if ($locale === null) {
			$locale = get_locale();
		}
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

		static $locale_format_map = null;
		if ($locale_format_map === null) {
			$locale_format_map = array(
				'zh' => 'Y年m月d日',
				'zh-cn' => 'Y年m月d日',
				'zh-tw' => 'Y年m月d日',
				'zh_CN' => 'Y年m月d日',
				'zh_TW' => 'Y年m月d日',
				'vi' => 'd/m/Y',
				'vn' => 'd/m/Y',
				'ja' => 'Y年m月d日',
				'ko' => 'Y년 m월 d일',
				'th' => 'd/m/Y',
				'id' => 'd/m/Y',
				'fr' => 'd/m/Y',
				'de' => 'd.m.Y',
				'es' => 'd/m/Y',
				'es-mx' => 'd/m/Y',
				'es-ar' => 'd/m/Y',
				'es-co' => 'd/m/Y',
				'es-cl' => 'd/m/Y',
				'es-pe' => 'd/m/Y',
				'es-ve' => 'd/m/Y',
				'es-cr' => 'd/m/Y',
				'es-ec' => 'd/m/Y',
				'es-gt' => 'd/m/Y',
				'es-pa' => 'd/m/Y',
				'es-do' => 'd/m/Y',
				'es-cu' => 'd/m/Y',
				'es-bo' => 'd/m/Y',
				'es-hn' => 'd/m/Y',
				'es-ni' => 'd/m/Y',
				'es-py' => 'd/m/Y',
				'es-sv' => 'd/m/Y',
				'es-uy' => 'd/m/Y',
				'it' => 'd/m/Y',
				'pt' => 'd/m/Y',
				'pt-br' => 'd/m/Y',
				'pt-pt' => 'd/m/Y',
				'pt_BR' => 'd/m/Y',
				'pt_PT' => 'd/m/Y',
				'ru' => 'd.m.Y',
				'nl' => 'd-m-Y',
				'pl' => 'd.m.Y',
				'tr' => 'd/m/Y',
				'ar' => 'd/m/Y',
				'he' => 'd/m/Y',
				'af' => 'd/m/Y',
				'sw' => 'd/m/Y',
				'af-za' => 'd/m/Y',
				'zu' => 'd/m/Y',
				'xh' => 'd/m/Y',
				'en-ng' => 'd/m/Y',
				'en_NG' => 'd/m/Y',
				'en-zw' => 'd/m/Y',
				'en_ZW' => 'd/m/Y',
				'ng' => 'd/m/Y',
				'zw' => 'd/m/Y',
				'bn' => 'd/m/Y',
				'bn-bd' => 'd/m/Y',
				'bn_BD' => 'd/m/Y',
				'bd' => 'd/m/Y',
			);
		}

		$wp_date_format = get_option('date_format');
		$locale_specific_format = isset($locale_format_map[$locale_lang])
			? $locale_format_map[$locale_lang]
			: (isset($locale_format_map[$locale]) ? $locale_format_map[$locale] : null);

		$is_english = ($locale_lang === 'en' || $locale === 'en_US' || $locale === 'en_GB');
		$is_cjk = ($locale_lang === 'zh' || $locale_lang === 'ja' || $locale_lang === 'ko' || strpos($locale_lang, 'zh') === 0);

		$format = null;

		if ($locale_specific_format) {
			if ($is_cjk) {
				$format = $locale_specific_format;
			} else if ($wp_date_format === 'F j, Y' && $is_english) {
				$format = 'F j, Y';
			} else {
				$wp_converted = civi_convert_wp_format_to_flatpickr($wp_date_format);

				$standard_formats = array('Y-m-d', 'd/m/Y', 'm/d/Y');
				$is_standard_wp = in_array($wp_date_format, $standard_formats, true);
				$is_standard_locale = in_array($locale_specific_format, $standard_formats, true);

				if ($wp_converted === $locale_specific_format) {
					$format = $wp_converted;
				} else if ($is_english) {
					$format = $wp_converted;
				} else if ($is_standard_wp && $is_standard_locale) {
					if (($wp_date_format === 'Y-m-d' && ($locale_specific_format === 'd/m/Y' || $locale_specific_format === 'm/d/Y')) ||
						(($wp_date_format === 'd/m/Y' || $wp_date_format === 'm/d/Y') && $locale_specific_format === 'Y-m-d')
					) {
						$format = $locale_specific_format;
					} else {
						$format = $wp_converted;
					}
				} else {
					$format = $locale_specific_format;
				}
			}
		}

		if ($format === null) {
			$format = ($wp_date_format === 'F j, Y' && $is_english)
				? 'F j, Y'
				: civi_convert_wp_format_to_flatpickr($wp_date_format);
		}

		$cjk_chars = array('年', '月', '日', '년', '월', '일');
		$has_cjk = false;
		foreach ($cjk_chars as $char) {
			if (strpos($format, $char) !== false) {
				$has_cjk = true;
				break;
			}
		}

		if ($has_cjk) {
			$year = date('Y', $date_timestamp);
			$month = date('n', $date_timestamp);
			$day = date('j', $date_timestamp);

			$format = str_replace(array('Y', 'm', 'n', 'd', 'j'), array($year, str_pad($month, 2, '0', STR_PAD_LEFT), $month, str_pad($day, 2, '0', STR_PAD_LEFT), $day), $format);
			return $format;
		} else if ($format === 'F j, Y') {
			return date_i18n($format, $date_timestamp);
		} else {
			return date($format, $date_timestamp);
		}
	}
}

/**
 * Get total posts by user id - Optimized with caching
 */
if (!function_exists('get_total_posts_by_user')) {
	function get_total_posts_by_user($user_id, $post_type = 'post')
	{
		// Use transient caching for performance
		$cache_key = 'civi_user_posts_' . $user_id . '_' . $post_type;
		$cached_count = get_transient($cache_key);

		if ($cached_count !== false) {
			return (int) $cached_count;
		}

		$args = array(
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'author'         => $user_id,
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => false,
		);
		$posts = new WP_Query($args);
		$count = $posts->found_posts;
		wp_reset_postdata();

		// Cache for 1 hour
		set_transient($cache_key, $count, HOUR_IN_SECONDS);

		return $count;
	}
}

/**
 * Clear user posts count cache
 */
if (!function_exists('civi_clear_user_posts_cache')) {
	function civi_clear_user_posts_cache($post_id)
	{
		$post = get_post($post_id);
		if ($post) {
			$cache_key = 'civi_user_posts_' . $post->post_author . '_' . $post->post_type;
			delete_transient($cache_key);
		}
	}
	add_action('save_post', 'civi_clear_user_posts_cache');
	add_action('delete_post', 'civi_clear_user_posts_cache');
}

/**
 * Get page id
 */
if (!function_exists('civi_get_page_id')) {
	function civi_get_page_id($page)
	{
		$page_id = civi_get_option('civi_' . $page . '_page_id');
		if ($page_id) {
			return absint(function_exists('pll_get_post') ? pll_get_post($page_id) : $page_id);
		} else {
			return 0;
		}
	}
}

/**
 * Get permalink
 */
if (!function_exists('civi_get_permalink')) {
	function civi_get_permalink($page)
	{
		if ($page_id = civi_get_page_id($page)) {
			return get_permalink($page_id);
		} else {
			return false;
		}
	}
}

/**
 * allow submit
 */
if (!function_exists('civi_allow_submit')) {
	function civi_allow_submit()
	{
		$enable_submit_jobs_via_frontend = civi_get_option('enable_submit_jobs_via_frontend', 1);
		$user_can_submit                 = civi_get_option('user_can_submit', 1);

		$allow_submit = true;
		if ($enable_submit_jobs_via_frontend != 1) {
			$allow_submit = false;
		} else {
			if ($user_can_submit != 1) {
				$allow_submit = false;
			}
		}

		return $allow_submit;
	}
}

/**
 * Total View Candidate
 */
if (!function_exists('civi_total_view_candidate')) {
	function civi_total_view_candidate($number_days = 7)
	{
		global $current_user;
		wp_get_current_user();
		$user_id = $current_user->ID;

		$args = array(
			'post_type'           => 'candidate',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => 1,
			'posts_per_page'      => -1,
			'author'              => $user_id,
		);

		$data         = new WP_Query($args);
		$total_post   = $data->found_posts;
		$views_values = array();
		if ($total_post > 0) {
			while ($data->have_posts()) : $data->the_post();
				$id         = get_the_ID();
				$views_date = get_post_meta($id, 'civi_view_candidate_date', true);
				$item       = array();
				for ($i = $number_days; $i >= 0; $i--) {
					$date = date("Y-m-d", strtotime("-" . $i . " day"));

					if (isset($views_date[$date])) {
						$item[] = $views_date[$date];
					} else {
						$item[] = 0;
					}
				}
				array_push($views_values, $item);
			endwhile;
		}
		wp_reset_postdata();
		$results_value = array();
		for ($i = 0; $i <= $number_days; $i++) {
			$views_item = 0;
			foreach ($views_values as $views_value) {
				$views_item += $views_value[$i];
			}
			array_push($results_value, $views_item);
		}

		return $results_value;
	}
}

/**
 * Company Green Tick
 */
if (!function_exists('civi_company_green_tick')) {
	function civi_company_green_tick($company_id)
	{
		if (empty($company_id)) {
			return;
		}
		$company_green_tick = get_post_meta($company_id, CIVI_METABOX_PREFIX . 'company_green_tick', true);
		if ($company_green_tick == 1) : ?>
			<div class="civi-check-company tip active">
				<div class="tip-content">
					<h4><?php esc_html_e('Conditions for a green tick:', 'civi-framework') ?></h4>
					<ul class="list-check">
						<li class="check-webs active">
							<i class="fas fa-check"></i>
							<?php esc_html_e('Website has been verified', 'civi-framework') ?>
						</li>
						<li class="check-phone active">
							<i class="fas fa-check"></i>
							<?php esc_html_e('Phone has been verified', 'civi-framework') ?>
						</li>
						<li class="check-location active">
							<i class="fas fa-check"></i>
							<?php esc_html_e('Location has been verified', 'civi-framework') ?>
						</li>
					</ul>
				</div>
			</div>
		<?php endif;
	}
}


/**
 * Activated Jobs - Optimized with proper query
 */
if (!function_exists('civi_total_actived_jobs')) {
	function civi_total_actived_jobs()
	{
		global $current_user;
		$user_id = $current_user->ID;

		$args = array(
			'post_type'      => 'jobs',
			'posts_per_page' => 1, // We only need count, not actual posts
			'author'         => $user_id,
			'fields'         => 'ids',
			'no_found_rows'  => false,
		);

		$data       = new WP_Query($args);
		$total_post = $data->found_posts;
		wp_reset_postdata();

		return $total_post;
	}
}
/**
 * Total Applications - Optimized
 */
if (!function_exists('civi_total_applications_jobs')) {
	function civi_total_applications_jobs()
	{
		global $current_user;
		$user_id = $current_user->ID;

		// Use fields => 'ids' to only get post IDs, much faster
		$args_jobs = array(
			'post_type'           => 'jobs',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => 1,
			'posts_per_page'      => -1,
			'author'              => $user_id,
			'fields'              => 'ids',
			'no_found_rows'       => true,
		);
		$data_jobs = new WP_Query($args_jobs);
		$jobs_employer_id = $data_jobs->posts;
		if (empty($jobs_employer_id)) {
			wp_reset_postdata();
			return 0;
		}

		$args = array(
			'post_type'           => 'applicants',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => 1,
			'posts_per_page'      => 1,
			'fields'              => 'ids',
			'no_found_rows'       => false,
			'meta_query'          => array(
				array(
					'key'     => CIVI_METABOX_PREFIX . 'applicants_jobs_id',
					'value'   => $jobs_employer_id,
					'compare' => 'IN'
				)
			),
		);

		$data = new WP_Query($args);
		$total_post = $data->found_posts;
		wp_reset_postdata();

		return $total_post;
	}
}

/**
 * Total meetings - Optimized
 */
if (!function_exists('civi_total_meeting')) {
	function civi_total_meeting($user)
	{
		if (empty($user)) {
			return 0;
		}
		global $current_user;
		$user_id = $current_user->ID;

		if ($user == 'employer') {
			$args = array(
				'post_type'      => 'meetings',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'author'         => $user_id,
				'fields'         => 'ids',
				'no_found_rows'  => false,
			);
		} elseif ($user == 'candidate') {
			// Use fields => 'ids' for better performance
			$args_applicants = array(
				'post_type'           => 'applicants',
				'ignore_sticky_posts' => 1,
				'posts_per_page'      => -1,
				'post_status'         => 'publish',
				'author'              => $user_id,
				'fields'              => 'ids',
				'no_found_rows'       => true,
			);
			$data_applicants = new WP_Query($args_applicants);
			$applicants_id = $data_applicants->posts;

			if (empty($applicants_id)) {
				wp_reset_postdata();
				return 0;
			}

			$args = array(
				'post_type'           => 'meetings',
				'ignore_sticky_posts' => 1,
				'posts_per_page'      => 1,
				'post_status'         => 'publish',
				'fields'              => 'ids',
				'no_found_rows'       => false,
				'meta_query'          => array(
					'relation' => 'AND',
					array(
						'key'     => CIVI_METABOX_PREFIX . 'meeting_applicants_id',
						'value'   => $applicants_id,
						'compare' => 'IN'
					),
					array(
						'key'     => CIVI_METABOX_PREFIX . 'meeting_status',
						'value'   => 'completed',
						'compare' => '!='
					)
				),
			);
		}

		$data = new WP_Query($args);
		$total_post = $data->found_posts;
		wp_reset_postdata();

		return $total_post;
	}
}

/**
 * Total View Jobs
 */
if (!function_exists('civi_total_view_jobs')) {
	function civi_total_view_jobs($number_days = 7)
	{
		global $current_user;
		wp_get_current_user();
		$user_id = $current_user->ID;

		$args = array(
			'post_type'           => 'jobs',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => 1,
			'posts_per_page'      => -1,
			'author'              => $user_id,
		);

		$data         = new WP_Query($args);
		$total_post   = $data->found_posts;
		$views_values = array();
		if ($total_post > 0) {
			while ($data->have_posts()) : $data->the_post();
				$id         = get_the_ID();
				$views_date = get_post_meta($id, 'civi_view_by_date', true);
				$item       = array();
				for ($i = $number_days; $i >= 0; $i--) {
					$date = date("Y-m-d", strtotime("-" . $i . " day"));

					if (isset($views_date[$date])) {
						$item[] = $views_date[$date];
					} else {
						$item[] = 0;
					}
				}
				array_push($views_values, $item);
			endwhile;
		}
		wp_reset_postdata();
		$results_value = array();
		for ($i = 0; $i <= $number_days; $i++) {
			$views_item = 0;
			foreach ($views_values as $views_value) {
				$views_item += $views_value[$i];
			}
			array_push($results_value, $views_item);
		}

		return $results_value;
	}
}

if (!function_exists('civi_get_user_display_name')) {
	function civi_get_user_display_name($user_id)
	{
		if (empty($user_id)) {
			return esc_html__('Unknown User', 'civi-framework');
		}

		$user = get_userdata(intval($user_id));
		if (!$user) {
			return esc_html__('Unknown User', 'civi-framework');
		}

		$first_name = get_user_meta($user_id, 'first_name', true);
		$last_name = get_user_meta($user_id, 'last_name', true);

		if (!empty($first_name) || !empty($last_name)) {
			$full_name = trim($first_name . ' ' . $last_name);
			if (!empty($full_name)) {
				return $full_name;
			}
		}

		return $user->display_name;
	}
}

/**
 * Get candidate display name
 * Priority: full name (first_name + last_name) > username > post title
 */
if (!function_exists('civi_get_candidate_display_name')) {
	function civi_get_candidate_display_name($candidate_id)
	{
		if (empty($candidate_id)) {
			return '';
		}

		// Get candidate first name and last name from post meta
		$candidate_first_name = get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_first_name', true);
		$candidate_last_name = get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_last_name', true);

		// Try to get full name from candidate meta
		if (!empty($candidate_first_name) || !empty($candidate_last_name)) {
			$full_name = trim($candidate_first_name . ' ' . $candidate_last_name);
			if (!empty($full_name)) {
				return $full_name;
			}
		}

		// Fallback to username
		$author_id = get_post_field('post_author', $candidate_id);
		if (!empty($author_id)) {
			$author_user = get_userdata($author_id);
			if ($author_user && !empty($author_user->user_login)) {
				return $author_user->user_login;
			}
		}

		// Final fallback to post title
		return get_the_title($candidate_id);
	}
}
/**
 * Total view jobs details
 */
if (!function_exists('civi_total_view_jobs_details')) {
	function civi_total_view_jobs_details($jobs_id)
	{

		if ($jobs_id) {
			$jobs_id = $jobs_id;
		} else {
			$jobs_id = get_the_ID();
		}
		$views_values = get_post_meta($jobs_id, 'civi_view_by_date', true);
		$views        = 0;
		if ($views_values) {
			foreach ($views_values as $values) {
				$views += $values;
			}
		}
		if ($views > 1) {
			$text = esc_html__('views', 'civi-framework');
		} else {
			$text = esc_html__('view', 'civi-framework');
		}
		?>
		<div class="jobs-view">
			<i class="fal fa-eye"></i>
			<span class="count"><?php echo sprintf('%1s (%2s)', $views, $text) ?></span>
		</div>
		<?php
	}
}

/**
 * Total Applications Jobs ID - Optimized
 */
if (!function_exists('civi_total_applications_jobs_id')) {
	/**
	 * Get total applications of a job
	 *
	 * @param int $jobs_id The ID of job
	 *
	 * @return int Total applications of job
	 */
	function civi_total_applications_jobs_id($jobs_id)
	{
		$args = array(
			'post_type'           => 'applicants',
			'ignore_sticky_posts' => 1,
			'posts_per_page'      => 1,
			'fields'              => 'ids',
			'no_found_rows'       => false,
			'post_status'         => 'any',
			'meta_query'          => array(
				array(
					'key'     => CIVI_METABOX_PREFIX . 'applicants_jobs_id',
					'value'   => $jobs_id,
					'compare' => '='
				)
			),
		);
		$data = new WP_Query($args);
		$total_post = $data->found_posts;
		wp_reset_postdata();

		return $total_post;
	}
}


/**
 * Total Jobs Apply - Optimized
 */
if (!function_exists('civi_total_jobs_apply')) {
	function civi_total_jobs_apply($jobs_id, $number_days = 7)
	{
		if (empty($jobs_id)) {
			return array();
		}

		$total_apply = array();
		for ($i = $number_days; $i >= 0; $i--) {
			$date = date("Y-m-d", strtotime("-" . $i . " day"));
			$args = array(
				'post_type'           => 'applicants',
				'ignore_sticky_posts' => 1,
				'posts_per_page'      => 1,
				'fields'              => 'ids',
				'no_found_rows'       => false,
				'meta_query'          => array(
					'relation' => 'AND',
					array(
						'key'     => CIVI_METABOX_PREFIX . 'applicants_jobs_id',
						'value'   => $jobs_id,
						'compare' => '='
					),
					array(
						'key'     => CIVI_METABOX_PREFIX . 'applicants_date',
						'value'   => $date,
						'compare' => '='
					),
				),
			);
			$data = new WP_Query($args);
			$total_apply[] = $data->found_posts;
			wp_reset_postdata();
		}

		return $total_apply;
	}
}


/**
 * Jobs Date
 */
if (!function_exists('civi_view_jobs_date')) {
	function civi_view_jobs_date($jobs_id, $number_days = 7)
	{

		if (empty($jobs_id)) {
			return;
		}
		$views_date = get_post_meta($jobs_id, 'civi_view_by_date', true);
		if (!is_array($views_date)) {
			$views_date = array();
		}

		$views_values = array();
		for ($i = $number_days; $i >= 0; $i--) {
			$date = date("Y-m-d", strtotime("-" . $i . " day"));
			if (isset($views_date[$date])) {
				$views_values[] = $views_date[$date];
			} else {
				$views_values[] = 0;
			}
		}

		return $views_values;
	}
}

/**
 * User Review
 */
if (!function_exists('civi_total_user_review')) {
	function civi_total_user_review()
	{

		global $current_user;
		wp_get_current_user();
		$user_id = $current_user->ID;

		global $wpdb;
		$comments_query = "SELECT * FROM $wpdb->comments as comment INNER JOIN $wpdb->commentmeta AS meta WHERE meta.meta_key = 'jobs_rating' AND meta.comment_id = comment.comment_ID AND ( comment.comment_approved = 1 OR comment.user_id = $user_id )";

		$get_comments = $wpdb->get_results($comments_query);

		$comment_author = array();
		if (!is_null($get_comments)) {
			foreach ($get_comments as $comment) {
				$comment_id      = $comment->comment_ID;
				$post_id         = $comment->comment_post_ID;
				$comment_user_id = $comment->user_id;
				$post_author_id  = get_post_field('post_author', $post_id);
				if ($post_author_id == $user_id) {
					$comment_author[] = $comment_id;
				}
			}
		}
		$total_post = count($comment_author);

		add_user_meta($user_id, 'user_list_comment_id', $comment_author);

		return $total_post;
	}
}

if (!function_exists('civi_admin_taxonomy_terms')) {
	function civi_admin_taxonomy_terms($post_id, $taxonomy, $post_type)
	{

		$terms = get_the_terms($post_id, $taxonomy);

		if (!is_wp_error($terms) && $terms != false) {
			$results = array();
			foreach ($terms as $term) {
				$results[] = sprintf(
					'<a href="%s">%s</a>',
					esc_url(add_query_arg(array(
						'post_type' => $post_type,
						$taxonomy   => $term->slug
					), 'edit.php')),
					esc_html(sanitize_term_field('name', $term->name, $term->term_id, $taxonomy, 'display'))
				);
			}

			return join(', ', $results);
		}

		return false;
	}
}

/**
 * civi_admin_taxonomy_terms
 */
if (!function_exists('civi_admin_taxonomy_terms')) {
	function civi_admin_taxonomy_terms($post_id, $taxonomy, $post_type)
	{

		$terms = get_the_terms($post_id, $taxonomy);

		if (!is_wp_error($terms) && $terms != false) {
			$results = array();
			foreach ($terms as $term) {
				$results[] = sprintf(
					'<a href="%s">%s</a>',
					esc_url(add_query_arg(array(
						'post_type' => $post_type,
						$taxonomy   => $term->slug
					), 'edit.php')),
					esc_html(sanitize_term_field('name', $term->name, $term->term_id, $taxonomy, 'display'))
				);
			}

			return join(', ', $results);
		}

		return false;
	}
}

/**
 * Get format number
 */
if (!function_exists('civi_get_format_number')) {
	function civi_get_format_number($number, $decimals = 0)
	{
		$number = doubleval($number);
		if ($number) {
			$dec_point     = civi_get_option('decimal_separator', '.');
			$thousands_sep = civi_get_option('thousand_separator', ',');

			// Handle empty separators
			if (empty($dec_point)) {
				$dec_point = '.';
			}
			if (empty($thousands_sep)) {
				$thousands_sep = ' ';
			}

			// Prevent same separators
			if ($thousands_sep === $dec_point) {
				if ($dec_point === ',') {
					$thousands_sep = ' ';
				} else if ($dec_point === '.') {
					$thousands_sep = ',';
				} else {
					$thousands_sep = ',';
				}
			}

			return number_format($number, $decimals, $dec_point, $thousands_sep);
		} else {
			return 0;
		}
	}
}

/**
 * Custom Field Candidate
 */
if (!function_exists('civi_custom_field_candidate')) {
	function civi_custom_field_candidate($tabs, $newTab = false)
	{
		$custom_field_candidate = civi_render_custom_field('candidate');
		$candidate_id           = civi_get_post_id_candidate();
		$candidate_data         = get_post($candidate_id);

		$check_tabs = false;
		foreach ($custom_field_candidate as $field) {
			if ($field['tabs'] == $tabs) {
				$check_tabs = true;
			}
		}

		if (count($custom_field_candidate) > 0) {
			if ($newTab == true) { ?>
				<div class="row">
					<?php foreach ($custom_field_candidate as $field) {
						if ($field['section'] == $tabs) { ?>
					<?php civi_get_template("dashboard/candidate/profile/additional/field.php", array(
								'field'          => $field,
								'candidate_data' => $candidate_data
							));
						}
					} ?>
				</div>
				<?php } else {
				if ($check_tabs == true) : ?>
					<div class="candidate-additional block-from">
						<h6><?php esc_html_e('Additional Filed', 'civi-framework') ?></h6>
						<div class="row">
							<?php foreach ($custom_field_candidate as $field) {
								if ($field['tabs'] == $tabs) { ?>
							<?php civi_get_template("dashboard/candidate/profile/additional/field.php", array(
										'field'          => $field,
										'candidate_data' => $candidate_data
									));
								}
							} ?>
						</div>
					</div>
				<?php endif;
			}
		}
	}
}
/**
 * Custom Field Single Candidate
 */
if (!function_exists('civi_custom_field_single_candidate')) {
	function civi_custom_field_single_candidate($tabs, $newTab = false)
	{
		$custom_field_candidate = civi_render_custom_field('candidate');
		$candidate_id           = civi_get_post_id_candidate();
		$candidate_data         = get_post($candidate_id);

		$check_tabs = false;
		foreach ($custom_field_candidate as $field) {
			if ($field['tabs'] == $tabs) {
				$check_tabs = true;
			}
		}

		if (count($custom_field_candidate) > 0) {
			if ($newTab == true) { ?>
				<?php foreach ($custom_field_candidate as $field) {
					if ($field['section'] == $tabs) { ?>
				<?php civi_get_template("candidate/single/additional/field.php", array(
							'field'          => $field,
							'candidate_data' => $candidate_data
						));
					}
				} ?>
				<?php } else {
				if ($check_tabs == true) : ?>
					<?php foreach ($custom_field_candidate as $field) {
						if ($field['tabs'] == $tabs) { ?>
					<?php civi_get_template("candidate/single/additional/field.php", array(
								'field'          => $field,
								'candidate_data' => $candidate_data
							));
						}
					} ?>
			<?php endif;
			}
		}
	}
}

/**
 * Get Data List Messages
 *
 * @param bool $first Get only first message
 * @param bool $status_pending Filter by pending status only
 * @param int  $paged Page number for pagination (default: 1)
 * @param int  $posts_per_page Number of messages per page (default: 15, -1 for all)
 * @return WP_Query
 */
if (!function_exists('civi_get_data_list_message')) {
	function civi_get_data_list_message($first = false, $status_pending = false, $paged = 1, $posts_per_page = 15)
	{
		global $current_user;
		$user_id = apply_filters('civi_modify_user_id', $current_user->ID);

		$args = array(
			'post_type'  => 'messages',
			'order'      => 'DESC',
			'paged'      => $paged,
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key'     => CIVI_METABOX_PREFIX . 'creator_message',
					'value'   => strval($user_id),
					'compare' => '='
				),
				array(
					'key'     => CIVI_METABOX_PREFIX . 'reply_message',
					'value'   => strval($user_id),
					'compare' => '='
				)
			),
		);

		if ($status_pending == true) {
			$args['post_status'] = 'pending';
		} else {
			$args['post_status'] = array('publish', 'pending');
		}

		if ($first == true) {
			$args['posts_per_page'] = 1;
		} else {
			$args['posts_per_page'] = $posts_per_page;
		}

		$data = new WP_Query($args);

		return $data;
	}
}

/**
 * Get total unread message
 */
if (!function_exists('civi_get_total_unread_message')) {
	function civi_get_total_unread_message()
	{
		$data_list    = civi_get_data_list_message(false, true);
		$total_unread = $data_list->found_posts;

		if ($total_unread > 0) { ?>
			<span class="badge"><?php esc_html_e($total_unread) ?></span>
			<?php } else {
			return;
		}
	}
}


/**
 * Get Data Notification
 */
if (!function_exists('civi_get_data_notification')) {
	function civi_get_data_notification()
	{
		global $current_user;
		$user_id = $current_user->ID;

		// Only get notifications for current user
		$args = array(
			'post_type'      => 'notification',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'meta_query'     => array(
				array(
					'key'     => CIVI_METABOX_PREFIX . 'user_receive_noti',
					'value'   => (string) $user_id,
					'compare' => '='
				),
			),
			'orderby'        => 'date',
			'order'          => 'DESC'
		);

		$data = get_posts($args);

		return $data;
	}
}

/**
 * Get Data Ajax Notification
 */
if (!function_exists('civi_get_data_ajax_notification')) {
	function civi_get_data_ajax_notification($post_current_id, $actions)
	{
		global $current_user;
		$user_id = $current_user->ID;

		// Validate inputs
		if (empty($post_current_id) || empty($actions) || empty($user_id)) {
			return;
		}

		// Check if post exists
		$post = get_post($post_current_id);
		if (!$post) {
			return;
		}

		$user_receive = get_post_field('post_author', $post_current_id);

		// Don't send notification to yourself or if no receiver
		if (empty($user_receive) || intval($user_receive) === intval($user_id)) {
			return;
		}

		$link         = get_the_permalink($post_current_id);
		$page_link    = '#';
		$mess_noti    = '';
		$action_label = '';

		// Ensure link is valid
		if (empty($link) || $link === false) {
			$link = '';
		}

		//Action
		if (
			in_array("civi_user_employer", (array) $current_user->roles)
			|| in_array("civi_user_candidate", (array) $current_user->roles)
		) {
			switch ($actions) {
				case 'add-apply':
					$mess_noti = esc_html__('A new applicant on job', 'civi-framework');
					$action_label   = esc_html__('Applicant', 'civi-framework');
					$page_link = civi_get_permalink('applicants');
					break;
				case 'add-message':
					$mess_noti = esc_html__('A new message', 'civi-framework');
					$action_label   = esc_html__('Message', 'civi-framework');
					$page_link = civi_get_permalink('messages');
					$link      = '';
					break;
				case 'add-wishlist':
					$mess_noti = esc_html__('A new wishlist on job', 'civi-framework');
					$action_label   = esc_html__('Wishlist', 'civi-framework');
					$page_link = $link; // Link to job detail
					break;
				case 'add-invite':
					$mess_noti = esc_html__('A new invite', 'civi-framework');
					$action_label   = esc_html__('Invite', 'civi-framework');
					$page_link = civi_get_permalink('my_jobs') . '#tab-invite';
					$link      = '';
					break;
				case 'add-follow-company':
					$mess_noti = esc_html__('A new follow on company', 'civi-framework');
					$action_label   = esc_html__('Follow Company', 'civi-framework');
					$page_link = $link; // Link to company profile
					break;
				case 'add-review-company':
					$mess_noti = esc_html__('A new review on company', 'civi-framework');
					$page_link = $link; // Link to company profile
					$action_label   = esc_html__('Review Company', 'civi-framework');
					break;

				case 'add-follow-candidate':
					$mess_noti = esc_html__('A new follow', 'civi-framework');
					$action_label   = esc_html__('Follow Candidate', 'civi-framework');
					$link      = '';
					$page_link = civi_get_permalink('candidate_company');
					break;
				case 'add-meeting':
					$mess_noti    = esc_html__('A new meeting on job', 'civi-framework');
					$action_label      = esc_html__('Meeting', 'civi-framework');
					$jobs_id      = get_post_meta($post_current_id, CIVI_METABOX_PREFIX . 'mee_jobs_id', true);
					// Validate jobs_id and get permalink
					if (!empty($jobs_id) && get_post($jobs_id)) {
						$link = get_the_permalink($jobs_id);
						if (empty($link) || $link === false) {
							$link = '';
						}
					} else {
						$link = '';
					}
					$user_receive = get_post_meta($post_current_id, CIVI_METABOX_PREFIX . 'user_receive_mee', true);
					// Additional check for user_receive in meeting case
					if (empty($user_receive) || intval($user_receive) === intval($user_id)) {
						return;
					}
					$page_link    = civi_get_permalink('candidate_meetings');
					break;
				case 'add-review-candidate':
					$mess_noti = esc_html__('A new review on your profile', 'civi-framework');
					$action_label   = esc_html__('Review Candidate', 'civi-framework');
					$page_link = $link; // Link to candidate profile
					break;
				case 'add-review-service':
					$mess_noti = esc_html__('A new review on service', 'civi-framework');
					$action_label   = esc_html__('Review Service', 'civi-framework');
					$page_link = $link; // Link to service detail
					break;
			}
		}

		// Check if notification data is valid
		if (empty($mess_noti) || empty($action_label)) {
			return; // Unknown action type
		}

		// Ensure page_link fallback
		if (empty($page_link) || $page_link === false) {
			$page_link = '#';
		}

		//New
		$post_title = get_the_title($post_current_id);

		// Validate post title
		if (empty($post_title)) {
			$post_title = esc_html__('Notification', 'civi-framework');
		}

		$new_post   = array(
			'post_type'   => 'notification',
			'post_status' => 'publish',
			'post_title'  => $post_title,
		);

		$post_id = wp_insert_post($new_post, true);

		// Check for errors
		if (is_wp_error($post_id) || empty($post_id)) {
			error_log('Civi Notification Error: Failed to create notification - ' .
				($post_id instanceof WP_Error ? $post_id->get_error_message() : 'Unknown error'));
			return;
		}

		// Save notification metadata
		update_post_meta($post_id, CIVI_METABOX_PREFIX . 'user_send_noti', $user_id);
		update_post_meta($post_id, CIVI_METABOX_PREFIX . 'user_receive_noti', $user_receive);
		update_post_meta($post_id, CIVI_METABOX_PREFIX . 'link_post_noti', $link);
		update_post_meta($post_id, CIVI_METABOX_PREFIX . 'mess_noti', $mess_noti);
		update_post_meta($post_id, CIVI_METABOX_PREFIX . 'action_noti', $action_label);
		update_post_meta($post_id, CIVI_METABOX_PREFIX . 'link_page_noti', $page_link);
	}
}

/**
 * Get founded max year based on settings
 */
if (!function_exists('civi_get_founded_max_year')) {
	function civi_get_founded_max_year()
	{
		return intval(date('Y'));
	}
}

/**
 * Get company founded
 */
if (!function_exists('civi_get_company_founded')) {
	function civi_get_company_founded($option = true)
	{
		global $company_meta_data;
		$founded_min = intval(civi_get_option('value_founded_min'));

		$founded_max = civi_get_founded_max_year();
		if (!empty($founded_min) && !empty($founded_min)) {
			if ($option) {
				for ($founded = $founded_min; $founded <= $founded_max; $founded++) { ?>
					<option value="<?php echo $founded ?>" <?php if (isset($company_meta_data[CIVI_METABOX_PREFIX . 'company_founded'][0])) {
																										if ($company_meta_data[CIVI_METABOX_PREFIX . 'company_founded'][0] == $founded) {
																											echo 'selected';
																										}
																									} ?>><?php echo $founded ?></option>
				<?php } ?>
			<?php } else {
				$foundeds = array();
				for ($founded = $founded_min; $founded <= $founded_max; $founded++) {
					$foundeds[] = $founded;
				};

				return array_combine($foundeds, $foundeds);
			};
		};
	};
}

/**
 * Get social network
 */
if (!function_exists('civi_get_social_network')) {
	function civi_get_social_network($id, $post_type)
	{
		$social_name        = $social_icon = $social_name_field = $social_url_field = $value_icon = array();
		$civi_social_fields = civi_get_option('civi_social_fields');
		$civi_social_tabs   = get_post_meta($id, CIVI_METABOX_PREFIX . $post_type . '_social_tabs');

		if (is_array($civi_social_tabs)) {
			foreach ($civi_social_tabs as $key => $value) {
				if (is_array($value)) {
					foreach ($value as $k1 => $v1) {
						$social_name_field[] = $v1[CIVI_METABOX_PREFIX . $post_type . '_social_name'];
						$social_url_field[]  = $v1[CIVI_METABOX_PREFIX . $post_type . '_social_url'];
					}
				}
			}
		}

		if (is_array($civi_social_fields)) {
			foreach ($civi_social_fields as $key => $value) {
				$social_name[] = $value['social_name'];
				$social_icon[] = $value['social_icon'];
			}
		}

		$civi_social_field = array_combine($social_name, $social_icon);
		$icon_filter       = array_filter(
			$civi_social_field,
			function ($key) use ($social_name_field) {
				if (in_array($key, $social_name_field)) {
					return $social_name_field;
				}
			},
			ARRAY_FILTER_USE_KEY
		);
		ksort($icon_filter);
		$civi_social_fields = array_combine($social_name_field, $social_url_field);
		$url_filter         = array_filter(
			$civi_social_fields,
			function ($key) use ($social_name_field) {
				if (in_array($key, $social_name_field)) {
					return $social_name_field;
				}
			},
			ARRAY_FILTER_USE_KEY
		);
		ksort($url_filter);
		$value_icon = array_values($icon_filter);
		$value_url  = array_values($url_filter);
		if (!empty($value_icon) && !empty($value_url)) {
			$civi_socials = array_combine($value_icon, $value_url);
			foreach ($civi_socials as $key => $value) {
				if (!empty($value)) {
					echo '<li><a href="' . esc_url($value) . '">' . $key . '</a></li>';
				}
			}
		}
	};
}
/**
 * Image size
 */
if (!function_exists('civi_image_resize')) {
	function civi_image_resize($data, $image_size)
	{
		if (preg_match('/\d+x\d+/', $image_size)) {
			$image_sizes = explode('x', $image_size);
			$image_src   = civi_image_resize_id($data, $image_sizes[0], $image_sizes[1], true);
		} else {
			if (!in_array($image_size, array('full', 'thumbnail'))) {
				$image_size = 'full';
			}
			$image_src = wp_get_attachment_image_src($data, $image_size);
			if ($image_src && !empty($image_src[0])) {
				$image_src = $image_src[0];
			}
		}

		return $image_src;
	}
}
/**
 * Image resize by url
 */
if (!function_exists('civi_image_resize_url')) {
	function civi_image_resize_url($url, $width = null, $height = null, $crop = true, $retina = false)
	{

		global $wpdb;

		if (empty($url)) {
			return array('url' => '', 'width' => 0, 'height' => 0);
		}

		if (class_exists('Jetpack') && method_exists('Jetpack', 'get_active_modules') && in_array('photon', Jetpack::get_active_modules())) {
			$args_crop = array(
				'resize' => $width . ',' . $height,
				'crop'   => '0,0,' . $width . 'px,' . $height . 'px'
			);
			$url       = jetpack_photon_url($url, $args_crop);
		}

		$width  = ($width) ? $width : get_option('thumbnail_size_w');
		$height = ($height) ? $height : get_option('thumbnail_size_h');

		$retina = $retina ? ($retina === true ? 2 : $retina) : 1;

		// Parse URL and validate
		$parsed_url = parse_url($url);
		if (false === $parsed_url || !isset($parsed_url['path'])) {
			return array('url' => $url, 'width' => $width, 'height' => $height);
		}

		// Try to get the file path using WordPress functions first
		$upload_dir = wp_upload_dir();
		$file_path = '';

		// Check if URL is in uploads directory
		if (strpos($url, $upload_dir['baseurl']) !== false) {
			$file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $url);
		} else {
			// Fallback to document root method, but validate it
			if (isset($_SERVER['DOCUMENT_ROOT']) && !empty($_SERVER['DOCUMENT_ROOT'])) {
				$file_path = $_SERVER['DOCUMENT_ROOT'] . $parsed_url['path'];
			} else {
				return array('url' => $url, 'width' => $width, 'height' => $height);
			}
		}

		// Normalize file path
		$file_path = wp_normalize_path($file_path);

		// Validate file path is within allowed directories (open_basedir check)
		if (!file_exists($file_path) || !is_readable($file_path)) {
			return array('url' => $url, 'width' => $width, 'height' => $height);
		}

		if (is_multisite()) {
			global $blog_id;
			$blog_details = get_blog_details($blog_id);
			if ($blog_details && isset($blog_details->path)) {
				$file_path = str_replace($blog_details->path, '/', $file_path);
			}
		}

		$dest_width = $width * $retina;

		$dest_height = $height * $retina;

		$suffix = "{$dest_width}x{$dest_height}";

		$info = pathinfo($file_path);
		$dir  = isset($info['dirname']) ? $info['dirname'] : '';
		$name = '';
		$ext  = '';

		if (!empty($info['extension'])) {
			$ext  = strtolower($info['extension']);
			$name = wp_basename($file_path, ".$ext");
		} else {
			// If no extension, try to determine from URL
			$path_parts = explode('.', basename($parsed_url['path']));
			if (count($path_parts) > 1) {
				$ext = strtolower(end($path_parts));
				$name = wp_basename($file_path, ".$ext");
			} else {
				return array('url' => $url, 'width' => $width, 'height' => $height);
			}
		}

		if (empty($ext) || empty($name)) {
			return array('url' => $url, 'width' => $width, 'height' => $height);
		}

		if ('bmp' == $ext) {
			return array('url' => $url, 'width' => $width, 'height' => $height);
		}

		$suffix = "{$dest_width}x{$dest_height}";

		$dest_file_name = "{$dir}/{$name}-{$suffix}.{$ext}";

		if (!file_exists($dest_file_name)) {

			$query          = $wpdb->prepare("SELECT * FROM $wpdb->posts WHERE guid='%s'", $url);
			$get_attachment = $wpdb->get_results($query);

			$editor = wp_get_image_editor($file_path);

			if (is_wp_error($editor)) {
				return array('url' => $url, 'width' => $width, 'height' => $height);
			}

			$size = $editor->get_size();
			if (is_wp_error($size)) {
				return array('url' => $url, 'width' => $width, 'height' => $height);
			}

			$orig_width  = isset($size['width']) ? $size['width'] : $width;
			$orig_height = isset($size['height']) ? $size['height'] : $height;

			$src_x = $src_y = 0;
			$src_w = $orig_width;
			$src_h = $orig_height;

			if ($crop) {

				$cmp_x = $orig_width / $dest_width;
				$cmp_y = $orig_height / $dest_height;

				if ($cmp_x > $cmp_y) {
					$src_w = round($orig_width / $cmp_x * $cmp_y);
					$src_x = round(($orig_width - ($orig_width / $cmp_x * $cmp_y)) / 2);
				} else if ($cmp_y > $cmp_x) {
					$src_h = round($orig_height / $cmp_y * $cmp_x);
					$src_y = round(($orig_height - ($orig_height / $cmp_y * $cmp_x)) / 2);
				}
			}

			$editor->crop($src_x, $src_y, $src_w, $src_h, $dest_width, $dest_height);

			$saved = $editor->save($dest_file_name);

			if (is_wp_error($saved) || !is_array($saved) || !isset($saved['path'])) {
				return array('url' => $url, 'width' => $width, 'height' => $height);
			}

			$resized_url    = str_replace(wp_basename($url), wp_basename($saved['path']), $url);
			$resized_width  = isset($saved['width']) ? $saved['width'] : $dest_width;
			$resized_height = isset($saved['height']) ? $saved['height'] : $dest_height;
			$resized_type   = isset($saved['mime-type']) ? $saved['mime-type'] : 'image/' . $ext;

			if ($get_attachment) {
				if ($get_attachment[0]->ID) {
					$metadata = wp_get_attachment_metadata($get_attachment[0]->ID);
					if (isset($metadata['image_meta'])) {
						$metadata['image_meta']['resized_images'][] = $resized_width . 'x' . $resized_height;
						wp_update_attachment_metadata($get_attachment[0]->ID, $metadata);
					}
				}
			}

			$image_array = array(
				'url'    => $resized_url,
				'width'  => $resized_width,
				'height' => $resized_height,
				'type'   => $resized_type
			);
		} else {
			$image_array = array(
				'url'    => str_replace(wp_basename($url), wp_basename($dest_file_name), $url),
				'width'  => $dest_width,
				'height' => $dest_height,
				'type'   => $ext
			);
		}

		return $image_array;
	}
}

/*
 * Image resize by id
 */
if (!function_exists('civi_image_resize_id')) {
	function civi_image_resize_id($images_id, $width = null, $height = null, $crop = true, $retina = false)
	{
		$output    = '';
		$image_src = wp_get_attachment_image_src($images_id, 'full');
		if (is_array($image_src)) {
			$resize = civi_image_resize_url($image_src[0], $width, $height, $crop, $retina);
			if ($resize != null && is_array($resize)) {
				$output = $resize['url'];
			}
		}

		return $output;
	}
}

/**
 * Get name company
 */
if (!function_exists('civi_select_post_company')) {
	function civi_select_post_company($type_option = false)
	{
		global $current_user, $jobs_meta_data;
		$user_id                  = $current_user->ID;
		$jobs_user_select_company = get_user_meta($user_id, CIVI_METABOX_PREFIX . 'jobs_user_select_company', true);
		$meta_query_args          = array(
			'post_type'      => 'company',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		);
		if ($type_option) {
			$meta_query_args['author'] = $user_id;
		}
		$meta_query     = new WP_Query($meta_query_args);
		$key_company    = array("");
		$values_company = array("None");
		foreach ($meta_query->posts as $post) {
			$values_company[] = $post->post_title;
			$key_company[]    = $post->ID;
		};
		if ($type_option) {
			echo '<option value="" data-url="">' . esc_html__('None', 'civi-framework') . '</option>';
			foreach ($meta_query->posts as $post) {
				$company_logo     = get_post_meta($post->ID, CIVI_METABOX_PREFIX . 'company_logo', false);
				$company_logo_url = isset($company_logo[0]['url']) ? $company_logo[0]['url'] : ''; ?>
				<option <?php if ((isset($jobs_meta_data[CIVI_METABOX_PREFIX . 'jobs_select_company']) && $jobs_meta_data[CIVI_METABOX_PREFIX . 'jobs_select_company'][0] == $post->ID) || (isset($jobs_user_select_company) && $jobs_user_select_company == $post->ID)) {
									echo 'selected';
								} ?> value="<?php echo $post->ID; ?>" data-url="<?php echo $company_logo_url ?>"><?php echo $post->post_title; ?>
				</option>
			<?php }
		} else {
			return array_combine($key_company, $values_company);
		}
	}
}

/**
 * Get posts company
 */
if (!function_exists('civi_posts_company')) {
	function civi_posts_company($company_id, $posts_per_page = -1)
	{
		if (empty($company_id)) {
			return;
		}
		$meta_query_args = array(
			'post_type'      => 'jobs',
			'post_status'    => 'publish',
			'posts_per_page' => $posts_per_page,
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => CIVI_METABOX_PREFIX . 'jobs_select_company',
					'value'   => $company_id,
					'compare' => '=='
				)
			),
		);
		$meta_query      = new WP_Query($meta_query_args);

		return $meta_query;
	}
}

/**
 * Get field count - Optimized
 */
if (!function_exists('civi_field_count')) {
	function civi_field_count($field, $key, $post_type)
	{
		if (empty($field)) {
			return 0;
		}
		$args = array(
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => false,
			'meta_query'     => array(
				array(
					'key'     => $key,
					'value'   => $field,
					'compare' => '=='
				)
			),
		);
		$data = new WP_Query($args);
		$count = $data->found_posts;
		wp_reset_postdata();

		return $count;
	}
}


/**
 * Get applicants status
 */
if (!function_exists('civi_applicants_status')) {
	function civi_applicants_status($id)
	{
		$applicants_status = get_post_meta($id, CIVI_METABOX_PREFIX . 'applicants_status');
		if (!empty($applicants_status)) {
			if ($applicants_status[0] == 'rejected') {
				echo '<span class="label label-close">' . esc_html__('Rejected', 'civi-framework') . '</span>';
			} elseif ($applicants_status[0] == 'approved') {
				echo '<span class="label label-open">' . esc_html__('Approved', 'civi-framework') . '</span>';
			} else {
				echo '<span class="label label-pending">' . esc_html__('Pending', 'civi-framework') . '</span>';
			}
		} else {
			echo '<span class="label label-pending">' . esc_html__('Pending', 'civi-framework') . '</span>';
		}
	}
}

/**
 * Get total post
 */
if (!function_exists('civi_total_post')) {
	function civi_total_post($post_type, $meta_key)
	{
		global $current_user;
		$user_id = $current_user->ID;
		if ($meta_key == 'my_wishlist') {
			$post_in = get_user_meta($user_id, CIVI_METABOX_PREFIX . 'my_wishlist', true);
		} elseif ($meta_key == 'my_follow') {
			$post_in = get_user_meta($user_id, CIVI_METABOX_PREFIX . 'my_follow', true);
		} elseif ($meta_key == 'my_invite') {
			$post_in = get_user_meta($user_id, CIVI_METABOX_PREFIX . 'my_invite', true);
		} elseif ($meta_key == 'follow_candidate') {
			$post_in = get_user_meta($user_id, CIVI_METABOX_PREFIX . 'follow_candidate', true);
		}

		$meta_query_args = array(
			'post_type'           => $post_type,
			'post__in'            => $post_in,
			'ignore_sticky_posts' => 1,
		);
		$meta_query      = new WP_Query($meta_query_args);
		if (!empty($post_in) && $meta_query->found_posts > 0) {
			return $meta_query->found_posts;
		} else {
			return 0;
		}
	}
}

/**
 * Get total my apply
 */
if (!function_exists('civi_total_my_apply')) {
	function civi_total_my_apply()
	{
		global $current_user;
		$user_id = $current_user->ID;
		$args    = array(
			'post_type'      => 'applicants',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'author'         => $user_id,
		);
		$data    = new WP_Query($args);

		return $data->found_posts;
	}
}


/**
 * Get service to candidate
 */
if (!function_exists('civi_id_service_to_candidate')) {
	function civi_id_service_to_candidate($service_id)
	{
		$author_id          = get_post_field('post_author', $service_id);
		$args_candidate     = array(
			'post_type'      => 'candidate',
			'posts_per_page' => 1,
			'author'         => $author_id,
		);
		$current_user_posts = get_posts($args_candidate);
		$candidate_id       = !empty($current_user_posts) ? $current_user_posts[0]->ID : '';

		return $candidate_id;
	}
}


/**
 * Get repeater social
 */
if (!function_exists('civi_get_repeater_social')) {
	function civi_get_repeater_social($social_selected, $type_option = false, $data = false)
	{
		$social_name = array();
		$civi_social_fields = civi_get_option('civi_social_fields');
		if (is_array($civi_social_fields) || is_object($civi_social_fields)) {
			if ($type_option) {
				echo '<option value="">' . esc_html__('None', 'civi-framework') . '</option>';
				foreach ($civi_social_fields as $social_fields) {
					if ($data) {
						$selected = ($social_selected == $social_fields['social_name']) ? 'selected' : '';
						echo '<option value="' . esc_attr($social_fields['social_name']) . '"' . $selected . '>' . esc_html($social_fields['social_name']) . '</option>';
					} else {
						echo '<option value="' . esc_attr($social_fields['social_name']) . '">' . esc_html($social_fields['social_name']) . '</option>';
					}
				}
			} else {
				foreach ($civi_social_fields as $social_fields) {
					$social_name[] = $social_fields['social_name'];
				}
				return array_combine($social_name, $social_name);
			}
		}
	}
}


/**
 * Get select currency type
 */
if (!function_exists('civi_get_select_currency_type')) {
	function civi_get_select_currency_type($options_selected = false)
	{
		global $current_user, $jobs_meta_data, $candidate_meta_data;
		$user_id                 = $current_user->ID;
		$keys                    = $values = array();
		$options_currency_type   = civi_get_option('currency_fields', true);
		$currency_type_default   = civi_get_option('currency_type_default');
		$currency_sign_default   = civi_get_option('currency_sign_default');
		$jobs_user_currency_type = get_user_meta($user_id, CIVI_METABOX_PREFIX . 'jobs_user_currency_type', true);
		$jobs_currency_type      = isset($jobs_meta_data[CIVI_METABOX_PREFIX . 'jobs_currency_type']) ? $jobs_meta_data[CIVI_METABOX_PREFIX . 'jobs_currency_type'][0] : '';
		$candidate_currency_type = isset($candidate_meta_data[CIVI_METABOX_PREFIX . 'candidate_currency_type']) ? $candidate_meta_data[CIVI_METABOX_PREFIX . 'candidate_currency_type'][0] : '';
		if (is_array($options_currency_type) || is_object($options_currency_type)) {
			foreach ($options_currency_type as $key => $value) {
				$keys[]   = $value['currency_sign'];
				$values[] = $value['currency_type'];
			}
		}
		if ($options_selected) {
			echo '<option value="' . $currency_sign_default . '">(' . $currency_sign_default . ') - ' . $currency_type_default . '</option>';
			foreach ($options_currency_type as $key => $value) { ?>
				<?php if ($value['currency_sign']) : ?>
					<option <?php if (!empty($options_currency_type) && ($jobs_user_currency_type == $value['currency_sign'] || $jobs_currency_type == $value['currency_sign'] || $candidate_currency_type == $value['currency_sign'])) {
										echo 'selected';
									} ?> value="<?php echo $value['currency_sign'] ?>">
						(<?php echo $value['currency_sign'] . ') - ' . $value['currency_type'] ?>
					</option>
				<?php endif; ?>
		<?php }
		} else {
			$currency_default = array($currency_sign_default => $currency_type_default);
			$currency         = array_combine($keys, $values);

			return array_merge($currency_default, $currency);
		}
	}
}


/**
 * Get Post ID Candidate
 */
if (!function_exists('civi_get_currency_type')) {
	function civi_get_post_id_candidate()
	{
		global $current_user;
		$user_id = $current_user->ID;
		$args_candidate     = array(
			'post_type'      => 'candidate',
			'posts_per_page' => 1,
			'author'         => $user_id,
			'post_status'    => array('publish', 'pending'),
		);
		$current_user_posts = get_posts($args_candidate);
		$candidate_id       = !empty($current_user_posts) ? $current_user_posts[0]->ID : '';

		return $candidate_id;
	}
}

/**
 * Get currency type
 */
if (!function_exists('civi_get_currency_type')) {
	function civi_get_currency_type($currency = 1)
	{
		$jobs_id            = get_the_ID();
		$jobs_currency_type = get_post_meta($jobs_id, CIVI_METABOX_PREFIX . 'jobs_currency_type', true);
		if ($currency == 1) {
			$currency_type = $jobs_currency_type;
		} else {
			$array_key       = civi_get_select_currency_type();
			$output_currency = array_filter($array_key, function ($k) {
				$jobs_id            = get_the_ID();
				$jobs_currency_type = get_post_meta($jobs_id, CIVI_METABOX_PREFIX . 'jobs_currency_type', true);

				return $k == $jobs_currency_type;
			}, ARRAY_FILTER_USE_KEY);
			$currency_type   = $output_currency[$jobs_currency_type];
		}

		return $currency_type;
	}
}
/**
 * Get salary jobs
 */
if (!function_exists('civi_get_salary_jobs')) {
	function civi_get_salary_jobs($jobs_id)
	{
		$jobs_salary_active = civi_get_option('enable_single_jobs_salary', '1');
		if (empty($jobs_salary_active) || $jobs_salary_active !== '1') {
			return '';
		}
		$jobs_salary_show = get_post_meta($jobs_id, CIVI_METABOX_PREFIX . 'jobs_salary_show', true);

		// If no salary show type is set, return empty
		if (empty($jobs_salary_show)) {
			return '';
		}
		$jobs_salary_rate = get_post_meta($jobs_id, CIVI_METABOX_PREFIX . 'jobs_salary_rate', true);

		if ($jobs_salary_rate == 'hour') {
			$jobs_salary_rate = esc_html__('/hour', 'civi-framework');
		} elseif ($jobs_salary_rate == 'day') {
			$jobs_salary_rate = esc_html__('/day', 'civi-framework');
		} elseif ($jobs_salary_rate == 'week') {
			$jobs_salary_rate = esc_html__('/week', 'civi-framework');
		} elseif ($jobs_salary_rate == 'month') {
			$jobs_salary_rate = esc_html__('/month', 'civi-framework');
		} elseif ($jobs_salary_rate == 'year') {
			$jobs_salary_rate = esc_html__('/year', 'civi-framework');
		} else {
			$jobs_salary_rate = '';
		}

		$jobs_currency_type    = get_post_meta($jobs_id, CIVI_METABOX_PREFIX . 'jobs_currency_type', true);
		$currency_sign_default = civi_get_option('currency_sign_default');

		$options_currency_type = civi_get_option('currency_fields', true);
		$keys                  = $values = array();
		if (is_array($options_currency_type)) {
			foreach ($options_currency_type as $key => $value) {
				$keys[]   = $value['currency_sign'];
				$values[] = $value['currency_conversion'];
			}
		}
		$conversion_combine = array_combine($keys, $values);
		$conversion_filter  = array_filter($conversion_combine, function ($k) use ($jobs_currency_type) {
			return $k == $jobs_currency_type;
		}, ARRAY_FILTER_USE_KEY);
		if ($currency_sign_default == $jobs_currency_type) {
			$currency_conversion = 1;
		} else {
			$currency_conversion = intval(implode($conversion_filter));
			if ($currency_conversion == 0) {
				$currency_conversion = 1;
			}
		}

		$jobs_salary_minimum = civi_get_format_number(intval(get_post_meta($jobs_id, CIVI_METABOX_PREFIX . 'jobs_salary_minimum', true)) * $currency_conversion);
		$jobs_salary_maximum = civi_get_format_number(intval(get_post_meta($jobs_id, CIVI_METABOX_PREFIX . 'jobs_salary_maximum', true)) * $currency_conversion);
		$jobs_maximum_price  = civi_get_format_number(intval(get_post_meta($jobs_id, CIVI_METABOX_PREFIX . 'jobs_maximum_price', true)) * $currency_conversion);
		$jobs_minimum_price  = civi_get_format_number(intval(get_post_meta($jobs_id, CIVI_METABOX_PREFIX . 'jobs_minimum_price', true)) * $currency_conversion);

		$currency_position = civi_get_option('currency_position');
		$currency_left     = $currency_right = '';
		if ($currency_position == 'before') {
			$currency_left = civi_get_currency_type();
		} else {
			$currency_right = civi_get_currency_type();
		}

		// Get text from theme options or use defaults
		$text_min = civi_get_option('salary_text_minimum', esc_html__('Min: ', 'civi-framework'));
		$text_max = civi_get_option('salary_text_maximum', esc_html__('Max: ', 'civi-framework'));
		$text_agree = civi_get_option('salary_text_negotiable', esc_html__('Negotiable Price', 'civi-framework'));

		// Ensure text_agree is never empty
		if (empty($text_agree)) {
			$text_agree = 'Negotiable Price';
		}

		if ($jobs_salary_show == 'range') {
			$salary = $currency_left . $jobs_salary_minimum . $currency_right
				. ' - '
				. $currency_left . $jobs_salary_maximum . $currency_right
				. $jobs_salary_rate;
		} elseif ($jobs_salary_show == 'starting_amount') {
			$salary = $text_min . $currency_left . $jobs_minimum_price . $currency_right . $jobs_salary_rate;
		} elseif ($jobs_salary_show == 'maximum_amount') {
			$salary = $text_max . $currency_left . $jobs_maximum_price . $currency_right . $jobs_salary_rate;
		} else {
			$salary = $text_agree;
		}

		if (empty($salary)) {
			$salary = $text_agree;
		}

		return $salary;
	}
}

/**
 * Get salary candidate
 */
if (!function_exists('civi_get_salary_candidate')) {
	function civi_get_salary_candidate($candidate_id, $border_line = '/')
	{
		if (empty($candidate_id)) {
			return;
		}
		$offer_salary = civi_get_format_number(intval(get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_offer_salary', true)));
		$salary_type       = !empty(get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_salary_type')) ? get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_salary_type')[0] : '';
		$currency_type     = !empty(get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_currency_type')) ? get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_currency_type')[0] : '';
		$currency_position = civi_get_option('currency_position');
		$currency_leff     = $currency_right = '';

		if ($salary_type == 'hr') {
			$salary_type = esc_html__('hour', 'civi-framework');
		} elseif ($salary_type == 'day') {
			$salary_type = esc_html__('day', 'civi-framework');
		} elseif ($salary_type == 'week') {
			$salary_type = esc_html__('week', 'civi-framework');
		} elseif ($salary_type == 'month') {
			$salary_type = esc_html__('month', 'civi-framework');
		} elseif ($salary_type == 'year') {
			$salary_type = esc_html__('year', 'civi-framework');
		} else {
			$salary_type = '';
		}

		if ($currency_position == 'before') {
			$currency_leff = $currency_type;
		} else {
			$currency_right = $currency_type;
		}
		?>
		<?php if (!empty($offer_salary)) { ?>
			<div class="candidate-salary">
				<?php echo sprintf(__('<span>%1$s%2$s</span>%3$s%4$s%5$s'), $currency_leff, $offer_salary, $currency_right, $border_line, $salary_type); ?>
			</div>
			<?php }
	}
}
/**
 * Get expiration apply
 */
if (!function_exists('get_head_time_unit')) {
	function get_head_time_unit($head_time_unit)
	{
		if ($head_time_unit == 'Day') {
			return esc_html__('Day', 'civi-framework');
		} else if ($head_time_unit == 'Days') {
			return esc_html__('Days', 'civi-framework');
		} else if ($head_time_unit == 'Week') {
			return esc_html__('week', 'civi-framework');
		} else if ($head_time_unit == 'Weeks') {
			return esc_html__('weeks', 'civi-framework');
		} else if ($head_time_unit == 'Month') {
			return esc_html__('month', 'civi-framework');
		} else if ($head_time_unit == 'Months') {
			return esc_html__('months', 'civi-framework');
		} else if ($head_time_unit == 'Year') {
			return esc_html__('year', 'civi-framework');
		} else if ($head_time_unit == 'Years') {
			return esc_html__('years', 'civi-framework');
		}
		return null;
	}
}

/**
 * Get expiration apply
 */
if (!function_exists('civi_get_expiration_apply')) {
	function civi_get_expiration_apply($jobs_id)
	{
		if (empty($jobs_id)) {
			return;
		}
		$enable_jobs_expires = get_post_meta($jobs_id, CIVI_METABOX_PREFIX . 'enable_jobs_expires', true);

		if ($enable_jobs_expires == '1') {
			$current_status = get_post_status($jobs_id);
			if ($current_status != 'expired') {
				$data = array(
					'ID'          => $jobs_id,
					'post_type'   => 'jobs',
					'post_status' => 'expired'
				);
				$result = wp_update_post($data);
			}
			return 0;
		}

		$status_by_job_id = apply_filters('civi_get_status_by_job_id', get_post_status($jobs_id));

		if ($status_by_job_id == 'expired') {
			update_post_meta($jobs_id, CIVI_METABOX_PREFIX . 'enable_jobs_expires', 1);
			return 0;
		}

		$public_date         = get_the_date('Y-m-d', $jobs_id);
		$current_date        = date('Y-m-d');
		$jobs_days_single    = get_post_meta($jobs_id, CIVI_METABOX_PREFIX . 'jobs_days_closing', true);

		if ($jobs_days_single) {
			$jobs_days_closing = $jobs_days_single;
		} else {
			$jobs_days_closing = civi_get_option('jobs_number_days', true);
		}

		$expiration_date = date('Y-m-d', strtotime($public_date . '+' . intval($jobs_days_closing) . ' days'));
		$seconds         = strtotime($expiration_date) - strtotime($current_date);
		$dtF             = new \DateTime('@0');
		$dtT             = new \DateTime("@$seconds");
		$expiration_days = $dtF->diff($dtT)->format('%a');
		$status = apply_filters('civi_get_expiration_apply', 'expired');
		if ($expiration_date > $public_date && $expiration_date > $current_date) :
			$expiration_days = $expiration_days;
		else :
			$data = array(
				'ID'          => $jobs_id,
				'post_type'   => 'jobs',
				'post_status' => $status
			);
			$result = wp_update_post($data);
			if ($status == 'expired') {
				update_post_meta($jobs_id, CIVI_METABOX_PREFIX . 'enable_jobs_expires', 1);
			}
			$expiration_days = 0;
		endif;
		return $expiration_days;
	}
}


/**
 * Get status apply
 */
if (!function_exists('civi_get_status_apply')) {
	function civi_get_status_apply($jobs_id)
	{
		if (empty($jobs_id)) {
			return;
		}
		global $current_user;
		$user_id = $current_user->ID;
		if (in_array('civi_user_candidate', (array) $current_user->roles)) {
			$args_candidate = array(
				'post_type' => 'candidate',
				'author'    => $user_id,
			);
			$query          = new WP_Query($args_candidate);
			$candidate_id   = $query->post->ID;
		}
		$post_status         = get_post_status($jobs_id);
		$key_apply           = false;
		$jobs_select_apply   = !empty(get_post_meta($jobs_id, CIVI_METABOX_PREFIX . 'jobs_select_apply')) ? get_post_meta($jobs_id, CIVI_METABOX_PREFIX . 'jobs_select_apply')[0] : '';
		$jobs_apply_external = !empty(get_post_meta($jobs_id, CIVI_METABOX_PREFIX . 'jobs_apply_external')) ? get_post_meta($jobs_id, CIVI_METABOX_PREFIX . 'jobs_apply_external')[0] : '';
		$my_apply            = get_user_meta($user_id, CIVI_METABOX_PREFIX . 'my_apply', true);
		if (!empty($my_apply)) {
			$key_apply = array_search($jobs_id, $my_apply);
		}

		$candidate_paid_submission_type      = civi_get_option('candidate_paid_submission_type');
		$check_package                       = civi_get_field_check_candidate_package('jobs_apply');
		$candidate_package_number_jobs_apply = intval(get_user_meta($user_id, CIVI_METABOX_PREFIX . 'candidate_package_number_jobs_apply', true));
		$enable_apply_login                  = civi_get_option('enable_apply_login');
		$is_external = apply_filters('civi_is_external_apply', ($jobs_select_apply == 'external'), $jobs_id, $jobs_select_apply);
		if ($enable_apply_login == '1') {
			if ((is_user_logged_in() && in_array('civi_user_candidate', (array) $current_user->roles))) {
				if ($key_apply !== false) { ?>
					<button class="civi-button button-disbale"><?php esc_html_e('Applied', 'civi-framework') ?></button>
				<?php } elseif ($post_status === "pause") { ?>
					<button class="civi-button button-disbale"><?php esc_html_e('Pause', 'civi-framework') ?></button>
				<?php } elseif (civi_get_expiration_apply($jobs_id) == 0) { ?>
					<button class="civi-button button-disbale"><?php esc_html_e('Expired', 'civi-framework') ?></button>
				<?php } else { ?>
					<?php if ($is_external) { ?>
						<?php
						$jobs_apply_external = ($jobs_select_apply != 'external') ? '#civi_form_apply_jobs' : $jobs_apply_external;
						$button_args = array(
							'jobs_id' => $jobs_id,
							'candidate_id' => $candidate_id,
							'jobs_apply_external' => $jobs_apply_external,
						);
						$btn_apply_class = ($jobs_select_apply != 'external') ? 'civi-button-apply civi_form_apply_jobs' : '';

						$default_button = sprintf(
							'<a href="%1$s" target="_blank" class="civi-button %2$s btn-apply-jobs-external" id="btn-apply-jobs-external-%3$s" data-candidate_id="%4$s">%5$s</a>',
							esc_url($jobs_apply_external),
							$btn_apply_class,
							$jobs_id,
							$candidate_id,
							esc_html__('Apply now', 'civi-framework')
						);

						echo apply_filters('civi_external_apply_button', $default_button, $button_args, $jobs_select_apply);
						?>
					<?php } else { ?>
						<?php if ($check_package == -1 || $check_package == 0 || ($candidate_paid_submission_type == 'candidate_per_package' && $candidate_package_number_jobs_apply < 1)) { ?>
							<button class="civi-button button-disbale"><?php esc_html_e('Expires Package', 'civi-framework') ?></button>
						<?php } else { ?>
							<a href="#civi_form_apply_jobs" class="civi-button civi-button-apply civi_form_apply_jobs <?php echo ($jobs_select_apply == 'call-to') ? 'btn-apply-jobs-external' : ''; ?>" <?php echo ($jobs_select_apply == 'call-to') ? ('id="btn-apply-jobs-external-' . $jobs_id . '"') : ''; ?> data-jobs_id="<?php echo $jobs_id ?>" data-candidate_id="<?php echo $candidate_id ?>"><?php esc_html_e('Apply now', 'civi-framework') ?></a>
						<?php } ?>
					<?php } ?>
				<?php } ?>
			<?php } else { ?>
				<div class="account logged-out">
					<a href="#popup-form" class="btn-login civi-button"><?php esc_html_e('Apply now', 'civi-framework') ?></a>
				</div>
			<?php } ?>
			<?php
		}

		if ($enable_apply_login !== '1') {
			if (is_user_logged_in()) {
				if ((in_array('civi_user_candidate', (array) $current_user->roles))) {
					if ($key_apply !== false) { ?>
						<button class="civi-button button-disbale"><?php esc_html_e('Applied', 'civi-framework') ?></button>
					<?php } elseif ($post_status === "pause") { ?>
						<button class="civi-button button-disbale"><?php esc_html_e('Pause', 'civi-framework') ?></button>
					<?php } elseif (civi_get_expiration_apply($jobs_id) == 0) { ?>
						<button class="civi-button button-disbale"><?php esc_html_e('Expired', 'civi-framework') ?></button>
					<?php } else { ?>
						<?php if ($is_external) { ?>
							<?php
							$jobs_apply_external = ($jobs_select_apply != 'external') ? '#civi_form_apply_jobs' : $jobs_apply_external;
							$button_args = array(
								'jobs_id' => $jobs_id,
								'candidate_id' => $candidate_id,
								'jobs_apply_external' => $jobs_apply_external,
							);
							$btn_apply_class = ($jobs_select_apply != 'external') ? 'civi-button-apply civi_form_apply_jobs' : '';

							$default_button = sprintf(
								'<a href="%1$s" target="_blank" class="civi-button %2$s btn-apply-jobs-external" id="btn-apply-jobs-external-%3$s" data-candidate_id="%4$s">%5$s</a>',
								esc_url($jobs_apply_external),
								$btn_apply_class,
								$jobs_id,
								$candidate_id,
								esc_html__('Apply now', 'civi-framework')
							);

							echo apply_filters('civi_external_apply_button', $default_button, $button_args, $jobs_select_apply);
							?>
						<?php } else { ?>
							<?php if ($check_package == -1 || $check_package == 0 || ($candidate_paid_submission_type == 'candidate_per_package' && $candidate_package_number_jobs_apply < 1)) { ?>
								<button class="civi-button button-disbale"><?php esc_html_e('Expires Package', 'civi-framework') ?></button>
							<?php } else { ?>
								<a href="#civi_form_apply_jobs" class="civi-button civi-button-apply civi_form_apply_jobs <?php echo ($jobs_select_apply == 'call-to') ? 'btn-apply-jobs-external' : ''; ?>" <?php echo ($jobs_select_apply == 'call-to') ? ('id="btn-apply-jobs-external-' . $jobs_id . '"') : ''; ?> data-jobs_id="<?php echo $jobs_id ?>" data-candidate_id="<?php echo $candidate_id ?>"><?php esc_html_e('Apply now', 'civi-framework') ?></a>
							<?php } ?>
						<?php } ?>
					<?php } ?>
				<?php } else { ?>
					<div class="account logged-out">
						<a href="#popup-form" class="btn-login civi-button"><?php esc_html_e('Apply now', 'civi-framework') ?></a>
					</div>
				<?php }
			} else {
				if ($key_apply !== false) { ?>
					<button class="civi-button button-disbale"><?php esc_html_e('Applied', 'civi-framework') ?></button>
				<?php } elseif ($post_status === "pause") { ?>
					<button class="civi-button button-disbale"><?php esc_html_e('Pause', 'civi-framework') ?></button>
				<?php } elseif (civi_get_expiration_apply($jobs_id) == 0) { ?>
					<button class="civi-button button-disbale"><?php esc_html_e('Expired', 'civi-framework') ?></button>
				<?php } else { ?>
					<?php if ($jobs_select_apply == 'external') { ?>
						<a href="<?php echo esc_url($jobs_apply_external) ?>" target="_blank" class="civi-button btn-apply-jobs-external" id="btn-apply-jobs-external-<?php echo $jobs_id ?>"><?php esc_html_e('Apply now', 'civi-framework') ?></a>
					<?php } else if ($jobs_select_apply == 'internal') { ?>
						<div class="account logged-out">
							<a href="#popup-form" class="btn-login civi-button"><?php esc_html_e('Apply now', 'civi-framework') ?></a>
						</div>
					<?php } else { ?>
						<a href="#civi_form_apply_jobs" class="civi-button civi-button-apply civi_form_apply_jobs" data-jobs_id="<?php echo $jobs_id ?>"><?php esc_html_e('Apply now', 'civi-framework') ?></a>
					<?php } ?>
		<?php }
			}
		}
	}
}

/**
 * Get Jobs Icon Status
 */
if (!function_exists('civi_get_icon_status')) {
	function civi_get_icon_status($jobs_id)
	{
		if (empty($jobs_id)) {
			return;
		}
		$jobs_meta_data       = get_post_custom($jobs_id);
		$jobs_featured        = isset($jobs_meta_data[CIVI_METABOX_PREFIX . 'jobs_featured']) ? $jobs_meta_data[CIVI_METABOX_PREFIX . 'jobs_featured'][0] : '0';
		$enable_jobs_expires  = get_post_meta($jobs_id, CIVI_METABOX_PREFIX . 'enable_jobs_expires', true);
		$enable_status_urgent = civi_get_option('enable_status_urgent', '1');
		$number_status_urgent = civi_get_option('number_status_urgent', '3');
		?>
		<?php if ($jobs_featured == '1' && civi_get_expiration_apply($jobs_id) != '0') : ?>
			<span class="tooltip featured" data-title="<?php esc_attr_e('Featured', 'civi-framework') ?>">
				<img src="<?php echo esc_attr(CIVI_PLUGIN_URL . 'assets/images/icon-featured.svg'); ?>" alt="<?php echo esc_attr__('featured', 'civi-framework') ?>">
			</span>
		<?php endif; ?>
		<?php if (civi_get_expiration_apply($jobs_id) == '0' && $enable_jobs_expires == '1') : ?>
			<span class="tooltip filled" data-title="<?php esc_attr_e('Filled', 'civi-framework') ?>">
				<img src="<?php echo esc_attr(CIVI_PLUGIN_URL . 'assets/images/icon-filled.svg'); ?>" alt="<?php echo esc_attr__('filled', 'civi-framework') ?>">
			</span>
		<?php endif; ?>
		<?php if (civi_get_expiration_apply($jobs_id) != '0' && $number_status_urgent > civi_get_expiration_apply($jobs_id) && $enable_status_urgent == '1' && $number_status_urgent !== '') : ?>
			<span class="tooltip urgent" data-title="<?php esc_attr_e('Urgent', 'civi-framework') ?>">
				<img src="<?php echo esc_attr(CIVI_PLUGIN_URL . 'assets/images/icon-urgent.svg'); ?>" alt="<?php echo esc_attr__('urgent', 'civi-framework') ?>">
			</span>
		<?php endif; ?>
	<?php }
}

/**
 * Get map enqueue
 */
if (!function_exists('civi_get_map_enqueue')) {
	function civi_get_map_enqueue()
	{
		$map_type = civi_get_option('map_type', 'mapbox');
		if ($map_type == 'google_map') {
			wp_enqueue_script('google-map');
			wp_enqueue_style(CIVI_PLUGIN_PREFIX . 'autocomplete-fix');
		} else if ($map_type == 'mapbox') {
			wp_enqueue_style(CIVI_PLUGIN_PREFIX . 'mapbox-gl');
			wp_enqueue_style(CIVI_PLUGIN_PREFIX . 'mapbox-gl-geocoder');

			// Geocoder depends on mapbox-gl; mapbox-gl depends on the es6-promise chain (see template loader).
			wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'mapbox-gl-geocoder');
		} else if ($map_type == 'openstreetmap') {
			wp_enqueue_style(CIVI_PLUGIN_PREFIX . 'mapbox-gl');
			wp_enqueue_style(CIVI_PLUGIN_PREFIX . 'leaflet');
			wp_enqueue_style(CIVI_PLUGIN_PREFIX . 'esri-leaflet');

			wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'mapbox-gl');
			wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'leaflet');
			wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'leaflet-src');
			wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'esri-leaflet');
			wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'esri-leaflet-geocoder');
		}
	}
}
/**
 * Get map type
 */
if (!function_exists('civi_get_map_type')) {
	function civi_get_map_type($lng, $lat, $form_submit)
	{
		$map_type       = civi_get_option('map_type', 'mapbox');
		$map_zoom_level = civi_get_option('map_zoom_level', '3');
		$map_marker     = CIVI_PLUGIN_URL . 'assets/images/map-marker-icon.png';
		civi_get_map_enqueue();

		if ($map_type == 'google_map') {
			wp_enqueue_script('google-maps-callback');
			wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'google-map-submit');
			wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'autocomplete-fix');
			wp_localize_script(
				CIVI_PLUGIN_PREFIX . 'google-map-submit',
				'civi_google_map_submit_vars',
				array(
					'lng'         => $lng,
					'lat'         => $lat,
					'map_zoom'    => $map_zoom_level,
					'map_style'   => json_encode(civi_get_option('googlemap_style')),
					'map_type'    => civi_get_option('googlemap_type', 'roadmap'),
					'map_marker'  => $map_marker,
					'api_key'     => civi_get_option('googlemap_api_key'),
					'form_submit' => $form_submit,
				)
			);
		} else if ($map_type == 'openstreetmap') {
			wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'openstreet-map-submit');
			wp_localize_script(
				CIVI_PLUGIN_PREFIX . 'openstreet-map-submit',
				'civi_openstreet_map_submit_vars',
				array(
					'lng'         => $lng,
					'lat'         => $lat,
					'map_zoom'    => $map_zoom_level,
					'map_style'   => civi_get_option('openstreetmap_style', 'streets-v11'),
					'map_marker'  => $map_marker,
					'api_key'     => civi_get_option('openstreetmap_api_key'),
					'form_submit' => $form_submit,
				)
			);
		} else {
			wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'map-box-submit');
			wp_localize_script(
				CIVI_PLUGIN_PREFIX . 'map-box-submit',
				'civi_map_box_submit_vars',
				array(
					'lng'         => $lng,
					'lat'         => $lat,
					'map_zoom'    => $map_zoom_level,
					'map_style'   => civi_get_option('mapbox_style', 'streets-v11'),
					'map_marker'  => $map_marker,
					'api_key'     => civi_get_option('mapbox_api_key'),
					'form_submit' => $form_submit,
				)
			);
		}
	}
}

/**
 * Get single map type
 */
if (!function_exists('civi_get_single_map_type')) {
	function civi_get_single_map_type($lng, $lat)
	{

		$map_type       = civi_get_option('map_type', 'mapbox');
		$map_zoom_level = civi_get_option('map_zoom_level', '3');
		$map_marker     = CIVI_PLUGIN_URL . 'assets/images/map-marker-icon.png';
		civi_get_map_enqueue();

		if ($map_type == 'google_map') {
			wp_enqueue_script('google-maps-callback');
			wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'google-map-single');
			wp_localize_script(
				CIVI_PLUGIN_PREFIX . 'google-map-single',
				'civi_google_map_single_vars',
				array(
					'lng'        => $lng,
					'lat'        => $lat,
					'map_zoom'   => $map_zoom_level,
					'map_style'  => json_encode(civi_get_option('googlemap_style')),
					'map_type'   => civi_get_option('googlemap_type', 'roadmap'),
					'api_key'    => civi_get_option('googlemap_api_key'),
					'map_marker' => $map_marker,
				)
			);
		} else if ($map_type == 'openstreetmap') {
			wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'openstreet-map-single');
			wp_localize_script(
				CIVI_PLUGIN_PREFIX . 'openstreet-map-single',
				'civi_openstreet_map_single_vars',
				array(
					'lng'        => $lng,
					'lat'        => $lat,
					'map_zoom'   => $map_zoom_level,
					'map_style'  => civi_get_option('openstreetmap_style', 'streets-v11'),
					'api_key'    => civi_get_option('openstreetmap_api_key'),
					'map_marker' => $map_marker,
				)
			);
		} else {
			wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'map-box-single');
			wp_localize_script(
				CIVI_PLUGIN_PREFIX . 'map-box-single',
				'civi_map_box_single_vars',
				array(
					'lng'        => $lng,
					'lat'        => $lat,
					'map_zoom'   => $map_zoom_level,
					'map_style'  => civi_get_option('mapbox_style', 'streets-v11'),
					'api_key'    => civi_get_option('mapbox_api_key'),
					'map_marker' => $map_marker,
				)
			);
		}
	}
}

/**
 * Get thumbnail enqueue
 */
if (!function_exists('civi_get_thumbnail_enqueue')) {
	function civi_get_thumbnail_enqueue()
	{
		wp_enqueue_script('plupload');
		wp_enqueue_script('jquery-validate');
		$thumbnail_upload_nonce = wp_create_nonce('civi_thumbnail_allow_upload');
		$thumbnail_type         = civi_get_option('civi_image_type');
		$thumbnail_file_size    = civi_get_option('civi_image_max_file_size', '1000kb');
		$thumbnail_url          = CIVI_AJAX_URL . '?action=civi_thumbnail_upload_ajax&nonce=' . esc_attr($thumbnail_upload_nonce);
		$thumbnail_text         = esc_html__('Click here', 'civi-framework');

		wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'thumbnail');
		wp_localize_script(
			CIVI_PLUGIN_PREFIX . 'thumbnail',
			'civi_thumbnail_vars',
			array(
				'ajax_url'               => CIVI_AJAX_URL,
				'thumbnail_title'        => esc_html__('Valid file formats', 'civi-framework'),
				'thumbnail_type'         => $thumbnail_type,
				'thumbnail_file_size'    => $thumbnail_file_size,
				'thumbnail_upload_nonce' => $thumbnail_upload_nonce,
				'thumbnail_url'          => $thumbnail_url,
				'thumbnail_text'         => $thumbnail_text,
			)
		);
	}
}

/**
 * Get avatar enqueue
 */
if (!function_exists('civi_get_avatar_enqueue')) {
	function civi_get_avatar_enqueue()
	{
		wp_enqueue_script('plupload');
		wp_enqueue_script('jquery-validate');
		$avatar_upload_nonce = wp_create_nonce('civi_avatar_allow_upload');
		$avatar_type         = civi_get_option('civi_image_type');
		$avatar_file_size    = civi_get_option('civi_image_max_file_size', '1000kb');
		$avatar_url          = CIVI_AJAX_URL . '?action=civi_avatar_upload_ajax&nonce=' . esc_attr($avatar_upload_nonce);
		$avatar_text         = esc_html__('Upload', 'civi-framework');

		wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'avatar');
		wp_localize_script(
			CIVI_PLUGIN_PREFIX . 'avatar',
			'civi_avatar_vars',
			array(
				'ajax_url'            => CIVI_AJAX_URL,
				'avatar_title'        => esc_html__('Valid file formats', 'civi-framework'),
				'avatar_type'         => $avatar_type,
				'avatar_file_size'    => $avatar_file_size,
				'avatar_upload_nonce' => $avatar_upload_nonce,
				'avatar_url'          => $avatar_url,
				'avatar_text'         => $avatar_text,
			)
		);
	}
}


/**
 * Get custom_image enqueue
 */
if (!function_exists('civi_get_custom_image_enqueue')) {
	function civi_get_custom_image_enqueue()
	{
		wp_enqueue_script('plupload');
		wp_enqueue_script('jquery-validate');
		$custom_image_upload_nonce = wp_create_nonce('civi_custom_image_allow_upload');
		$custom_image_type         = civi_get_option('civi_image_type');
		$custom_image_file_size    = civi_get_option('civi_image_max_file_size', '1000kb');
		$custom_image_text         = esc_html__('Click here', 'civi-framework');

		wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'custom_image');
		wp_localize_script(
			CIVI_PLUGIN_PREFIX . 'custom_image',
			'civi_custom_image_vars',
			array(
				'ajax_url'                  => CIVI_AJAX_URL,
				'custom_image_title'        => esc_html__('Valid file formats', 'civi-framework'),
				'custom_image_type'         => $custom_image_type,
				'custom_image_file_size'    => $custom_image_file_size,
				'custom_image_upload_nonce' => $custom_image_upload_nonce,
				'custom_image_text'         => $custom_image_text,
			)
		);
	}
}

/**
 * Get gallery enqueue
 */
if (!function_exists('civi_get_gallery_enqueue')) {
	function civi_get_gallery_enqueue()
	{
		wp_enqueue_script('plupload');
		wp_enqueue_script('jquery-ui-sortable');
		$gallery_upload_nonce = wp_create_nonce('civi_gallery_allow_upload');
		$gallery_type         = civi_get_option('civi_image_type');
		$gallery_file_size    = civi_get_option('civi_image_max_file_size', '1000kb');
		$gallery_max_images   = civi_get_option('civi_max_gallery_images', 5);
		$gallery_url          = CIVI_AJAX_URL . '?action=civi_gallery_upload_ajax&nonce=' . esc_attr($gallery_upload_nonce);

		wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'gallery');
		wp_localize_script(
			CIVI_PLUGIN_PREFIX . 'gallery',
			'civi_gallery_vars',
			array(
				'ajax_url'             => CIVI_AJAX_URL,
				'gallery_title'        => esc_html__('Valid file formats', 'civi-framework'),
				'gallery_type'         => $gallery_type,
				'gallery_file_size'    => $gallery_file_size,
				'gallery_max_images'   => $gallery_max_images,
				'gallery_upload_nonce' => $gallery_upload_nonce,
				'gallery_url'          => $gallery_url,
			)
		);
	}
}

/**
 * Format money, format currency
 */
if (!function_exists('civi_get_format_money')) {
	function civi_get_format_money($money, $price_unit = '', $decimals = null, $small_sign = false, $is_currency_sign = true)
	{
		if (is_string($money)) {
			$money = preg_replace('/[^\d.,\-]/', '', $money);
			$money = str_replace(',', '.', $money);
		}

		$money = floatval($money);
		if (!is_numeric($money) || is_infinite($money) || is_nan($money)) {
			$money = 0;
		}

		$currency_sign = civi_get_option('currency_sign_default', '$');
		$currency_type = civi_get_option('currency_type_default', 'USD');
		$currency_position = civi_get_option('currency_position', 'before');
		$decimal_separator = civi_get_option('decimal_separator', '.');
		$thousand_separator = civi_get_option('thousand_separator', ',');

		if (empty($decimal_separator) || !is_string($decimal_separator)) {
			$decimal_separator = '.';
		}
		if (empty($thousand_separator) || !is_string($thousand_separator)) {
			$thousand_separator = ',';
		}

		if ($decimals === null) {
			$decimal_places_setting = civi_get_option('decimal_places', 'auto');
			if ($decimal_places_setting === 'auto') {
				$decimals = 2;
			} else {
				$decimals = intval($decimal_places_setting);
			}
		} else {
			$decimals = intval($decimals);
		}

		if ($decimals < 0 || $decimals > 10) {
			$decimals = 2;
		}

		if ($thousand_separator === $decimal_separator) {
			if ($decimal_separator === ',') {
				$thousand_separator = ' ';
			} else if ($decimal_separator === '.') {
				$thousand_separator = ',';
			} else {
				$thousand_separator = ',';
			}
		}

		try {
			$formatted_price = number_format($money, $decimals, $decimal_separator, $thousand_separator);
		} catch (Exception $e) {
			$formatted_price = number_format($money, 2, '.', ',');
		}

		$decimal_places_setting = civi_get_option('decimal_places', 'auto');
		if ($decimal_places_setting === 'auto' && $decimals > 0 && $money == floor($money)) {
			try {
				$formatted_price = number_format($money, 0, $decimal_separator, $thousand_separator);
			} catch (Exception $e) {
				$formatted_price = number_format($money, 0, '.', ',');
			}
		}

		$currency = '';
		if ($is_currency_sign == true) {
			$currency = !empty($currency_sign) ? $currency_sign : '$';
		} else {
			$currency = !empty($currency_type) ? $currency_type : 'USD';
		}

		if ($small_sign == true && !empty($currency)) {
			$currency = '<sup>' . esc_html($currency) . '</sup>';
		} else {
			$currency = esc_html($currency);
		}

		if ($currency_position == 'after') {
			return $formatted_price . $currency;
		} else {
			return $currency . $formatted_price;
		}
	}
}
/**
 * Get total reviews
 */
if (!function_exists('civi_get_total_reviews')) {
	function civi_get_total_reviews()
	{
		global $wpdb, $current_user;
		$user_id     = $current_user->ID;
		$my_reviews  = $wpdb->get_results("SELECT * FROM $wpdb->comments as comment INNER JOIN $wpdb->commentmeta AS meta WHERE comment.user_id = $user_id AND meta.meta_key = 'company_rating' AND meta.comment_id = comment.comment_ID ORDER BY comment.comment_ID DESC LIMIT 999");
		$company_ids = array();
		foreach ($my_reviews as $my_review) {
			$company_ids[] = $my_review->comment_post_ID;
		}

		$args = array(
			'post_type'           => 'company',
			'post__in'            => $company_ids,
			'ignore_sticky_posts' => 1,
			'posts_per_page'      => -1,
		);

		$data = new WP_Query($args);
		if (!empty($company_ids)) {
			$total_post = $data->found_posts;
		} else {
			$total_post = 0;
		}

		return $total_post;
	}
}

/**
 * Get total rating
 */
if (!function_exists('civi_get_total_rating')) {
	function civi_get_total_rating($post_type, $id)
	{
		global $wpdb;
		$current_user = wp_get_current_user();
		$user_id      = $current_user->ID;
		$rating       = $total_reviews = $total_stars = 0;

		if ($post_type == 'company') {
			$comments_query = "SELECT * FROM $wpdb->comments as comment INNER JOIN $wpdb->commentmeta AS meta WHERE comment.comment_post_ID = $id AND meta.meta_key = 'company_rating' AND meta.comment_id = comment.comment_ID AND ( comment.comment_approved = 1 OR comment.user_id = $user_id )";
		} elseif ($post_type == 'candidate') {
			$comments_query = "SELECT * FROM $wpdb->comments as comment INNER JOIN $wpdb->commentmeta AS meta WHERE comment.comment_post_ID = $id AND meta.meta_key = 'candidate_rating' AND meta.comment_id = comment.comment_ID AND ( comment.comment_approved = 1 OR comment.user_id = $user_id )";
		} elseif ($post_type == 'service') {
			$comments_query = "SELECT * FROM $wpdb->comments as comment INNER JOIN $wpdb->commentmeta AS meta WHERE comment.comment_post_ID = $id AND meta.meta_key = 'service_rating' AND meta.comment_id = comment.comment_ID AND ( comment.comment_approved = 1 OR comment.user_id = $user_id )";
		}
		$get_comments = $wpdb->get_results($comments_query);
		if (!is_null($get_comments)) {
			foreach ($get_comments as $comment) {
				if ($comment->comment_approved == 1) {
					if (!empty($comment->meta_value) && $comment->meta_value != 0.00) {
						$total_reviews++;
					}
					if ($comment->meta_value > 0) {
						$total_stars += $comment->meta_value;
					}
				}
			}
			if ($total_reviews != 0) {
				$rating = number_format($total_stars / $total_reviews, 1);
			}
		}
		update_post_meta($id, 'civi-' . $post_type . '_rating', $rating);
	?>

		<div class="civi-rating-wrapper">
			<span class="rating-count">
				<i class="fas fa-star"></i>
				<span><?php esc_html_e($rating); ?>
				</span>
			</span>
			<span class="review-count"><?php printf(_n('(%s Review)', '(%s Reviews)', $total_reviews, 'civi-framework'), $total_reviews); ?></span>
		</div>

		<?php }
}

/**
 * Get service order status
 */
if (!function_exists('civi_candidate_package_status')) {
	function civi_service_order_status($status)
	{
		if ($status == 'inprogress') : ?>
			<span class="label label-pause tooltip" data-title="<?php echo esc_attr__('Service in Process', 'civi-framework') ?>"><?php esc_html_e('In Process', 'civi-framework') ?></span>
		<?php elseif ($status == 'transferring') : ?>
			<span class="label label-transferring tooltip" data-title="<?php echo esc_attr__('The candidate has handed over the service', 'civi-framework') ?>"><?php esc_html_e('Transferring', 'civi-framework') ?></span>
		<?php elseif ($status == 'canceled') : ?>
			<span class="label label-close tooltip" data-title="<?php echo esc_attr__('Candidate has canceled', 'civi-framework') ?>"><?php esc_html_e('Canceled', 'civi-framework') ?></span>
		<?php elseif ($status == 'completed') : ?>
			<span class="label label-open tooltip" data-title="<?php echo esc_attr__('Service is completed', 'civi-framework') ?>"><?php esc_html_e('Completed', 'civi-framework') ?></span>
		<?php elseif ($status == 'expired') : ?>
			<span class="label label-close tooltip" data-title="<?php echo esc_attr__('Service has expired', 'civi-framework') ?>"><?php esc_html_e('Expired', 'civi-framework') ?></span>
		<?php elseif ($status == 'refund') : ?>
			<span class="label label-close tooltip" data-title="<?php echo esc_attr__('Employer has requested a refund', 'civi-framework') ?>"><?php esc_html_e('Refund', 'civi-framework') ?></span>
		<?php else : ?>
			<span class="label label-pending tooltip" data-title="<?php echo esc_attr__('Wait for admin to approve', 'civi-framework') ?>"><?php esc_html_e('Pending', 'civi-framework') ?></span>
		<?php endif;

		return $status;
	}
}


/**
 * Get wallet total price
 */
if (!function_exists('civi_candidate_package_status')) {
	function civi_wallet_total_price($status = 'pending')
	{
		global $current_user;
		$user_id       = $current_user->ID;
		$args_withdraw = array(
			'post_type'           => 'service_withdraw',
			'ignore_sticky_posts' => 1,
			'posts_per_page'      => -1,
			'post_status'         => 'publish',
			'meta_query'          => array(
				'relation' => 'AND',
				array(
					'key'     => CIVI_METABOX_PREFIX . 'service_withdraw_user_id',
					'value'   => $user_id,
					'compare' => '==',
				),
				array(
					'key'     => CIVI_METABOX_PREFIX . 'service_withdraw_status',
					'value'   => $status,
					'compare' => '==',
				)
			),
		);
		$data_withdraw = new WP_Query($args_withdraw);
		$total_price   = 0;
		if ($data_withdraw->have_posts()) {
			while ($data_withdraw->have_posts()) : $data_withdraw->the_post();
				$withdraw_id = get_the_ID();
				$price       = get_post_meta($withdraw_id, CIVI_METABOX_PREFIX . 'service_withdraw_price', true);
				if (empty($price)) {
					$price = 0;
				}
				$total_price += $price;
			endwhile;
		}

		$currency_sign_default = civi_get_option('currency_sign_default');
		$currency_position     = civi_get_option('currency_position');
		if ($currency_position == 'before') {
			$total_price = $currency_sign_default . $total_price;
		} else {
			$total_price = $total_price . $currency_sign_default;
		}

		return $total_price;
	}
}
/**
 * Get candidate package status
 */
if (!function_exists('civi_candidate_package_status')) {
	function civi_candidate_package_status()
	{
		global $current_user;
		$user_id    = $current_user->ID;

		// Get user's current package ID
		$current_package_id = get_user_meta($user_id, CIVI_METABOX_PREFIX . 'candidate_package_id', true);

		if (empty($current_package_id)) {
			return '-1';
		}

		// Find the order for the current package (with status = 1)
		$args_order = array(
			'post_type'      => 'candidate_order',
			'posts_per_page' => 1,
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => CIVI_METABOX_PREFIX . 'candidate_order_user_id',
					'value'   => $user_id,
					'compare' => '='
				),
				array(
					'key'     => CIVI_METABOX_PREFIX . 'candidate_order_item_id',
					'value'   => $current_package_id,
					'compare' => '='
				),
				array(
					'key'     => CIVI_METABOX_PREFIX . 'candidate_order_payment_status',
					'value'   => '1',
					'compare' => '='
				)
			),
		);
		$data_order = new WP_Query($args_order);
		$status     = '-1';
		if (!empty($data_order->post)) {
			$order_id = $data_order->post->ID;
			$status   = get_post_meta($order_id, CIVI_METABOX_PREFIX . 'candidate_order_payment_status', true);
		}

		return $status;
	}
}

/**
 * Check candidate package
 */
if (!function_exists('civi_check_candidate_package')) {
	function civi_check_candidate_package()
	{
		global $current_user;
		$user_id                        = $current_user->ID;
		$candidate_paid_submission_type = civi_get_option('candidate_paid_submission_type');
		$package_status                 = intval(civi_candidate_package_status());
		$has_candidate_package          = true;
		if ($candidate_paid_submission_type == 'candidate_per_package') {
			$civi_candidate_package  = new Civi_candidate_package();
			$check_candidate_package = $civi_candidate_package->user_candidate_package_available($user_id);
			if (($check_candidate_package == -1) || ($check_candidate_package == 0) || ($package_status !== 1)) {
				$has_candidate_package = false;
			}
		}

		return $has_candidate_package;
	}
}

/**
 * Get field number package
 */
if (!function_exists('civi_number_candidate_package_ajax')) {
	function civi_number_candidate_package_ajax($field)
	{
		if (empty($field)) {
			return;
		}
		global $current_user;
		$user_id                        = $current_user->ID;
		$candidate_paid_submission_type = civi_get_option('candidate_paid_submission_type');
		$candidate_package_id           = get_the_author_meta(CIVI_METABOX_PREFIX . 'candidate_package_id', $user_id);
		$check_package                  = civi_check_candidate_package();
		$show_package_field             = get_post_meta($candidate_package_id, CIVI_METABOX_PREFIX . 'show_package_' . $field, true);
		if ($show_package_field == 1 && $check_package) {
			if ($candidate_paid_submission_type == 'candidate_per_package') {
				$candidate_package_number_field = intval(get_the_author_meta(CIVI_METABOX_PREFIX . 'candidate_package_number_' . $field, $user_id));
				if ($candidate_package_number_field - 1 >= -1) {
					update_user_meta($user_id, CIVI_METABOX_PREFIX . 'candidate_package_number_' . $field, $candidate_package_number_field - 1);
				}
			}
		}
	}
}

/**
 * Get field check package
 */
if (!function_exists('civi_get_field_check_candidate_package')) {
	function civi_get_field_check_candidate_package($field)
	{
		global $current_user;
		$user_id                        = $current_user->ID;
		$candidate_paid_submission_type = civi_get_option('candidate_paid_submission_type');
		$candidate_package_id           = get_the_author_meta(CIVI_METABOX_PREFIX . 'candidate_package_id', $user_id);
		$check_package                  = civi_check_candidate_package();
		$show_package_field             = get_post_meta($candidate_package_id, CIVI_METABOX_PREFIX . 'show_package_' . $field, true);
		$enable_option_field            = civi_get_option('enable_candidate_package_' . $field);
		$check                          = 0;
		if ($candidate_paid_submission_type == 'candidate_per_package') {
			if ($show_package_field == '1') {
				if ($check_package) {
					$check = 1;
				} else {
					$check = -1;
				}
			} else {
				$check = 0;
			}
		} else {
			$check = 1;
		}
		if ($enable_option_field !== '1' || in_array("administrator", (array) $current_user->roles)) {
			$check = 2;
		}

		return $check;
	}
}

/**
 * Get employer field check package
 */
if (!function_exists('civi_get_field_check_employer_package')) {
	function civi_get_field_check_employer_package($field)
	{
		global $current_user;
		$user_id = apply_filters('civi_modify_user_id', $current_user->ID);
		$paid_submission_type = civi_get_option('paid_submission_type');
		$package_id           = get_the_author_meta(CIVI_METABOX_PREFIX . 'package_id', $user_id);
		$civi_profile         = new Civi_Profile();
		$check_package        = $civi_profile->user_package_available($user_id);
		$show_package_field   = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'show_package_company_' . $field, true);
		$enable_option_field  = civi_get_option('enable_company_package_' . $field);
		$args_invoice         = array(
			'post_type'      => 'invoice',
			'posts_per_page' => 1,
			'author'         => $user_id,
		);
		$data_invoice         = new WP_Query($args_invoice);
		$invoice_status       = '1';
		if (!empty($data_invoice->post)) {
			$invoice_id     = $data_invoice->post->ID;
			$invoice_status = get_post_meta($invoice_id, CIVI_METABOX_PREFIX . 'invoice_payment_status', true);
		}

		$check = 0;
		if ($paid_submission_type == 'per_package') {
			if ($show_package_field === '1') {
				if ($check_package) {
					$check = 1;
				} else {
					$check = -1;
				}
			} else {
				$check = 0;
			}
		} else {
			$check = 1;
		}
		if ($invoice_status === '0') {
			$check = -1;
		}
		if ($enable_option_field !== '1' || in_array("administrator", (array) $current_user->roles)) {
			$check = 2;
		}

		return $check;
	}
}

/**
 * Get comment time
 */
if (!function_exists('civi_get_comment_time')) {
	function civi_get_comment_time($comment_id = 0)
	{
		return sprintf(
			_x('%s ago', 'Human-readable time', 'civi-framework'),
			human_time_diff(
				get_comment_date('U', $comment_id),
				current_time('timestamp')
			)
		);
	}
}

/**
 * Get other templates (e.g. product attributes) passing attributes and including the file.
 *
 * @access public
 *
 * @param string $template_name
 * @param array $args (default: array())
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 */
if (!function_exists('civi_get_template')) {
	function civi_get_template($template_name, $args = array(), $template_path = '', $default_path = '')
	{
		if (!empty($args) && is_array($args)) {
			extract($args);
		}

		$located = civi_locate_template($template_name, $template_path, $default_path);

		if (!file_exists($located)) {
			_doing_it_wrong(__FUNCTION__, sprintf('<code>%s</code> does not exist.', $located), '2.1');

			return;
		}

		// Allow 3rd party plugin filter template file from their plugin.
		$located = apply_filters('civi_get_template', $located, $template_name, $args, $template_path, $default_path);

		do_action('civi_before_template_part', $template_name, $template_path, $located, $args);

		include($located);

		do_action('civi_after_template_part', $template_name, $template_path, $located, $args);
	}
}

/**
 * Locate a template and return the path for inclusion.
 */
if (!function_exists('civi_locate_template')) {
	function civi_locate_template($template_name, $template_path = '', $default_path = '')
	{
		if (!$template_path) {
			$template_path = CIVI()->template_path();
		}

		if (!$default_path) {
			$default_path = CIVI_PLUGIN_DIR . 'templates/';
		}

		// Look within passed path within the theme - this is priority.
		$template = locate_template(
			array(
				trailingslashit($template_path) . $template_name,
				$template_name
			)
		);

		// Get default template/
		if (!$template) {
			$template = $default_path . $template_name;
		}

		// Return what we found.
		return apply_filters('civi_locate_template', $template, $template_name, $template_path);
	}
}

/**
 * civi_get_jobs_by_category
 */
if (!function_exists('civi_get_jobs_by_category')) {
	function civi_get_jobs_by_category($total = 3, $show = 3, $category = 0, $exclude = '')
	{
		// Check if expired jobs should be included
		$enable_jobs_show_expires = civi_get_option('enable_jobs_show_expires');
		$post_status = ($enable_jobs_show_expires == 1) ? array('publish', 'expired') : 'publish';

		$args = array(
			'posts_per_page'      => $total,
			'post_type'           => 'jobs',
			'post_status'         => $post_status,
			'ignore_sticky_posts' => 1,
			'exclude'             => $exclude,
			'orderby'             => array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			),
			'tax_query'           => array(
				'relation' => 'AND',
				array(
					'taxonomy' => 'jobs-categories',
					'field'    => 'id',
					'terms'    => $category
				)
			),
			'meta_query'          => array(
				array(
					'key'     => CIVI_METABOX_PREFIX . 'enable_jobs_package_expires',
					'value'   => 0,
					'compare' => '=='
				)
			),
		);
		$job  = get_posts($args);
		ob_start();
		?>
		<?php foreach ($job as $jobs) { ?>
			<?php civi_get_template('content-jobs.php', array(
				'jobs_id'     => $jobs->ID,
				'jobs_layout' => 'layout-list',
			)); ?>
		<?php } ?>
		<?php
		return ob_get_clean();
	}
}
/**
 * get_taxonomy
 */
if (!function_exists('civi_get_taxonomy')) {
	function civi_get_taxonomy($taxonomy_name, $value_as_slug = false, $show_default_none = true, $render_array = false, $order = false, $meta_key = '')
	{
		if ($taxonomy_name === 'jobs-salary') return;

		global $current_user;
		$user_id = $current_user->ID;

		$args = array(
			'orderby'    => 'name',
			'parent'     => 0,
			'hide_empty' => false,
		);

		$terms = get_terms($taxonomy_name, $args);

		if (is_wp_error($terms)) {
			$terms = array();
		}

		if ($order == true && !empty($meta_key) && !empty($terms)) {
			usort($terms, function ($a, $b) use ($meta_key) {
				$a_meta = (int) get_term_meta($a->term_id, $meta_key, true);
				$b_meta = (int) get_term_meta($b->term_id, $meta_key, true);

				if ($a_meta === $b_meta) {
					return strcasecmp($a->name, $b->name);
				}

				if ($a_meta == 0 && $b_meta != 0) return 1;
				if ($a_meta != 0 && $b_meta == 0) return -1;

				return $a_meta - $b_meta;
			});
		}

		$result = array();

		foreach ($terms as $term) {
			$term_children            = get_terms($taxonomy_name, array(
				'parent'     => $term->term_id,
				'hide_empty' => false
			));
			$result[$term->term_id] = array();
			foreach ($term_children as $child) {
				$child_level_2                               = get_terms($taxonomy_name, array(
					'parent'     => $child->term_id,
					'hide_empty' => false
				));
				$result[$term->term_id][$child->term_id] = array();
				foreach ($child_level_2 as $grandchild) {
					$result[$term->term_id][$child->term_id][$grandchild->term_id] = array();
				}
			}
		}
		if ($render_array) {
			$list = array(
				'' => esc_html__('Select an option', 'civi-framework')
			);
			foreach ($result as $key => $val) {
				$term_detail  = get_term_by('id', $key, $taxonomy_name);
				$list[$key] = $term_detail->name;
				if (is_array($val)) {
					foreach ($val as $key => $val1) {
						$term_detail1 = get_term_by('id', $key, $taxonomy_name);
						$list[$key] = $term_detail1->name;
						if (is_array($val1)) {
							foreach ($val1 as $key => $val2) {

								$term_detail2 = get_term_by('id', $key, $taxonomy_name);
								$list[$key] = $term_detail2->name;
							}
						}
					}
				}
			}

			return $list;
		} else {
			if ($show_default_none) {
				echo '<option value="">' . esc_html__('Select an option', 'civi-framework') . '</option>';
			}
			if (!empty($result)) {
				if ($value_as_slug) {
					foreach ($result as $key => $val) {
						$term_detail = get_term_by('id', $key, $taxonomy_name);
						$jobs_user   = get_user_meta($user_id, CIVI_METABOX_PREFIX . $taxonomy_name . '_user');
						$jobs_user   = !empty($jobs_user) ? $jobs_user[0] : '';

						$jobs_user_term = null;
						if (!empty($jobs_user)) {
							if (is_array($jobs_user)) {
								$jobs_user = reset($jobs_user);
							}
							if (is_numeric($jobs_user)) {
								$jobs_user_term = get_term_by('id', intval($jobs_user), $taxonomy_name);
							} else {
								$jobs_user_term = get_term_by('slug', $jobs_user, $taxonomy_name);
							}
						}

						if (!empty($jobs_user_term)) {
							echo '<option value="' . $term_detail->slug . '" data-level="1">' . $term_detail->name . '</option>';
							if (is_array($val)) {
								foreach ($val as $key => $val1) {
									$term_detail1 = $jobs_user_term;
									echo '<option value="' . $term_detail1->slug . '" data-level="2">' . $term_detail1->name . '</option>';
									if (is_array($val1)) {
										foreach ($val1 as $key => $val2) {
											$term_detail2 = $jobs_user_term;
											echo '<option value="' . $term_detail2->slug . '" data-level="3">' . $term_detail2->name . '</option>';
										}
									}
								}
							}
						} else {
							echo '<option value="' . $term_detail->slug . '" data-level="1">' . $term_detail->name . '</option>';
							if (is_array($val)) {
								foreach ($val as $key => $val1) {
									$term_detail1 = get_term_by('id', $key, $taxonomy_name);
									echo '<option value="' . $term_detail1->slug . '" data-level="2">' . $term_detail1->name . '</option>';
									if (is_array($val1)) {
										foreach ($val1 as $key => $val2) {
											$term_detail2 = get_term_by('id', $key, $taxonomy_name);
											echo '<option value="' . $term_detail2->slug . '" data-level="3">' . $term_detail2->name . '</option>';
										}
									}
								}
							}
						}
					}
				} else {
					foreach ($result as $key => $value) {
						$term_detail = get_term_by('id', $key, $taxonomy_name);
						$jobs_user   = get_user_meta($user_id, CIVI_METABOX_PREFIX . $taxonomy_name . '_user');
						$jobs_user   = !empty($jobs_user) ? $jobs_user[0] : '';

						$jobs_user_id = 0;
						if (!empty($jobs_user)) {
							if (is_array($jobs_user)) {
								$jobs_user = reset($jobs_user);
							}
							if (is_numeric($jobs_user)) {
								$jobs_user_id = intval($jobs_user);
							} elseif (is_string($jobs_user)) {
								$jobs_user_term = get_term_by('slug', $jobs_user, $taxonomy_name);
								if ($jobs_user_term && !is_wp_error($jobs_user_term)) {
									$jobs_user_id = intval($jobs_user_term->term_id);
								}
							}
						}

						if (!empty($jobs_user_id)) { ?>

							<?php if ($show_default_none) { ?>
								<option <?php echo selected((int)$key === (int)$jobs_user_id, true, false); ?> value="<?php echo esc_attr($key); ?>" data-level="1"><?php echo esc_html(trim($term_detail->name)); ?></option>
								<?php
								if (is_array($value)) {
									foreach ($value as $key => $val) {
										$term_detail1 = get_term_by('id', $key, $taxonomy_name);
								?>
										<option <?php echo selected((int)$jobs_user_id === (int)$key, true, false); ?> value="<?php echo esc_attr($key); ?>" data-level="2"><?php echo esc_html(trim($term_detail1->name)); ?></option>
										<?php
										if (is_array($val)) {
											foreach ($val as $key => $v) {
												$term_detail2 = get_term_by('id', $key, $taxonomy_name);
										?>
												<option <?php echo selected((int)$jobs_user_id === (int)$key, true, false); ?> value="<?php echo esc_attr($key); ?>" data-level="3"><?php echo esc_html(trim($term_detail2->name)); ?></option>
								<?php
											}
										}
									}
								}
								?>
							<?php } else { ?>

								<?php foreach ((array)$jobs_user as $key => $value) {
									$term_detail1 = get_term_by('id', $key, $taxonomy_name);
								?>
									<option <?php echo selected((int)$value === (int)$term_detail->term_id, true, false); ?> value="<?php echo esc_attr($term_detail->term_id); ?>"><?php echo esc_html($term_detail->name); ?></option>
								<?php } ?>
							<?php } ?>
						<?php } else { ?>
							<option <?php if (isset($_GET[$taxonomy_name]) && $_GET[$taxonomy_name] == $term_detail->slug) {
												echo 'selected';
											} ?> value="<?php echo esc_attr($term_detail->term_id); ?>" data-level="1"><?php echo esc_html(trim($term_detail->name)); ?></option>
							<?php
							if (is_array($value)) {
								foreach ($value as $key => $val) {
									$term_detail1 = get_term_by('id', $key, $taxonomy_name);
							?>
									<option <?php if (isset($_GET[$taxonomy_name]) && $_GET[$taxonomy_name] == $term_detail1->slug) {
														echo 'selected';
													} ?> value="<?php echo esc_attr($term_detail1->term_id); ?>" data-level="2"><?php echo esc_html(trim($term_detail1->name)); ?></option>
									<?php
									if (is_array($val)) {
										foreach ($val as $key => $v) {
											$term_detail2 = get_term_by('id', $key, $taxonomy_name);
									?>
											<option <?php if (isset($_GET[$taxonomy_name]) && $_GET[$taxonomy_name] == $term_detail2->slug) {
																echo 'selected';
															} ?> value="<?php echo esc_attr($term_detail2->term_id); ?>" data-level="3"><?php echo esc_html(trim($term_detail2->name)); ?></option>
							<?php
										}
									}
								}
							}
							?>
		<?php
						}
					}
				}
			}
		}
	}
}

/**
 * Get find nearby cities - Optimized version with caching
 */
if (!function_exists('civi_find_nearby_cities')) {
	function civi_find_nearby_cities($city_name, $radius_km)
	{
		// Input validation
		$city_name = trim($city_name);
		$radius_km = intval($radius_km);

		if (empty($city_name) || $radius_km <= 0) {
			return array();
		}

		// Generate cache key
		$cache_key = 'civi_nearby_cities_' . md5($city_name . '_' . $radius_km);
		$cache_duration = 12 * HOUR_IN_SECONDS; // 12 hours cache

		// Check cache first
		$cached_result = get_transient($cache_key);
		if ($cached_result !== false) {
			return $cached_result;
		}

		// Get coordinates with caching
		$coordinates = civi_get_city_coordinates($city_name);
		if (empty($coordinates)) {
			// Return original city if coordinates not found
			$result = array($city_name);
			set_transient($cache_key, $result, $cache_duration);
			return $result;
		}

		$latitude = $coordinates['lat'];
		$longitude = $coordinates['lng'];

		// Get nearby cities from Overpass API
		$nearby_cities = civi_get_nearby_cities_from_overpass($latitude, $longitude, $radius_km, $city_name);
		// Ensure we always have at least the original city
		if (empty($nearby_cities)) {
			$nearby_cities = array($city_name);
		}

		// Cache the result
		set_transient($cache_key, $nearby_cities, $cache_duration);

		return $nearby_cities;
	}
}

/**
 * Get city coordinates with caching
 */
if (!function_exists('civi_get_city_coordinates')) {
	function civi_get_city_coordinates($city_name)
	{
		$cache_key = 'civi_coordinates_' . md5($city_name);
		$cache_duration = 24 * HOUR_IN_SECONDS; // 24 hours cache

		// Check cache first
		$cached_coordinates = get_transient($cache_key);
		if ($cached_coordinates !== false) {
			return $cached_coordinates;
		}

		$map_type = civi_get_option('map_type', 'mapbox');
		$coordinates = array();

		try {
			switch ($map_type) {
				case 'mapbox':
					$coordinates = civi_geocode_mapbox($city_name);
					break;
				case 'openstreetmap':
					$coordinates = civi_geocode_openstreetmap($city_name);
					break;
				default:
					$coordinates = civi_geocode_google($city_name);
					break;
			}

			// Cache coordinates if found
			if (!empty($coordinates)) {
				set_transient($cache_key, $coordinates, $cache_duration);
			}
		} catch (Exception $e) {
			// Silent error handling
		}

		return $coordinates;
	}
}

/**
 * Geocode using Mapbox API
 */
if (!function_exists('civi_geocode_mapbox')) {
	function civi_geocode_mapbox($city_name)
	{
		$mapbox_api_key = civi_get_option('mapbox_api_key');
		if (empty($mapbox_api_key)) {
			return array();
		}

		$url = 'https://api.mapbox.com/geocoding/v5/mapbox.places/' . urlencode($city_name) . '.json?access_token=' . $mapbox_api_key;

		$response = wp_remote_get($url, array(
			'timeout' => 10,
			'user-agent' => 'WordPress-Plugin'
		));

		if (is_wp_error($response)) {
			return array();
		}

		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

		if (isset($data['features'][0]['center'])) {
			return array(
				'lng' => $data['features'][0]['center'][0],
				'lat' => $data['features'][0]['center'][1]
			);
		}

		return array();
	}
}

/**
 * Geocode using OpenStreetMap API
 */
if (!function_exists('civi_geocode_openstreetmap')) {
	function civi_geocode_openstreetmap($city_name)
	{
		$url = 'https://nominatim.openstreetmap.org/search?format=json&q=' . urlencode($city_name) . '&limit=1';

		$response = wp_remote_get($url, array(
			'timeout' => 10,
			'user-agent' => 'WordPress-Plugin'
		));

		if (is_wp_error($response)) {
			return array();
		}

		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

		if (isset($data[0]['lat']) && isset($data[0]['lon'])) {
			return array(
				'lat' => $data[0]['lat'],
				'lng' => $data[0]['lon']
			);
		}

		return array();
	}
}

/**
 * Geocode using Google Maps API
 */
if (!function_exists('civi_geocode_google')) {
	function civi_geocode_google($city_name)
	{
		$google_maps_api_key = civi_get_option('googlemap_api_key');
		if (empty($google_maps_api_key)) {
			return array();
		}

		$url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($city_name) . '&key=' . $google_maps_api_key;

		$response = wp_remote_get($url, array(
			'timeout' => 10,
			'user-agent' => 'WordPress-Plugin'
		));

		if (is_wp_error($response)) {
			return array();
		}

		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

		if (isset($data['results'][0]['geometry']['location'])) {
			return array(
				'lng' => $data['results'][0]['geometry']['location']['lng'],
				'lat' => $data['results'][0]['geometry']['location']['lat']
			);
		}

		return array();
	}
}

/**
 * Get nearby cities from Overpass API with optimized query
 */
if (!function_exists('civi_get_nearby_cities_from_overpass')) {
	function civi_get_nearby_cities_from_overpass($latitude, $longitude, $radius_km, $city_name)
	{
		$radius_meters = $radius_km * 1000;

		// Optimized query - reduced complexity and timeout
		$query = "[out:json][timeout:15];
				  (
					node(around:{$radius_meters},{$latitude},{$longitude})[place=city];
					node(around:{$radius_meters},{$latitude},{$longitude})[place=town];
				  );
				  out body;";

		$response = wp_remote_post('http://overpass-api.de/api/interpreter', array(
			'body' => $query,
			'timeout' => 15,
			'user-agent' => 'WordPress-Plugin'
		));

		if (is_wp_error($response)) {
			// Return original city if API fails
			return array($city_name);
		}

		$http_code = wp_remote_retrieve_response_code($response);
		if ($http_code !== 200) {
			// Return original city if API fails
			return array($city_name);
		}

		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

		$nearby_cities = array();

		// Always include the original city first
		$nearby_cities[] = $city_name;

		if (isset($data['elements']) && !empty($data['elements'])) {
			foreach ($data['elements'] as $element) {
				if (isset($element['tags']['name']) && !empty($element['tags']['name'])) {
					$nearby_cities[] = $element['tags']['name'];
				}
			}
		}

		$nearby_cities = array_unique($nearby_cities);
		sort($nearby_cities);

		return $nearby_cities;
	}
}

/**
 * Get state country
 */
if (!function_exists('civi_get_state_country')) {
	function civi_get_state_country($city_id, $term_state, $city_state, $state_country)
	{
		$enable_option_state   = civi_get_option('enable_option_state');
		$enable_option_country = civi_get_option('enable_option_country');

		$state_name  = $country_name = $state_by_id = $country_by_id = '';
		$country_val = array();
		if ($enable_option_state === '1') {
			$state_id = get_term_meta($city_id, $city_state, true);
			if (!empty($state_id)) {
				$state_by_id = get_term_by('id', $state_id, $term_state);
				if (!empty($state_by_id)) {
					$state_name = ', ' . $state_by_id->name;
				}
			}
		}

		if ($enable_option_state === '1' && $enable_option_country === '1') {
			$country_id = get_term_meta($state_id, $state_country, true);
			$countries  = civi_get_countries();
			foreach ($countries as $k => $v) {
				if ($k == $country_id) {
					$country_val[] = $v;
				}
			}
			if (!empty($country_val)) {
				$country_name = ', ' . implode('', $country_val);
			}
		}

		$location = $state_name . $country_name;

		return $location;
	}
}

/**
 * Get label location
 */
if (!function_exists('civi_get_label_location')) {
	function civi_get_label_location($post_id, $taxonomy_name, $term_state, $city_state, $state_country)
	{
		$taxonomy_location = get_the_terms($post_id, $taxonomy_name);
		if (is_array($taxonomy_location)) {
			foreach ($taxonomy_location as $location) {
				$location_link = get_term_link($location, $taxonomy_name);
				echo '<a class="label label-location" href="' . esc_url($location_link) . '">';
				echo ' <i class="fas fa-map-marker-alt"></i>';
				echo esc_html($location->name);
				echo civi_get_state_country($location->term_id, $term_state, $city_state, $state_country);
				echo '</a>';
			}
		}
	}
}
/**
 * Get taxonomy location
 */
if (!function_exists('civi_get_taxonomy_location')) {
	function civi_get_taxonomy_location($taxonomy_name, $term_state, $city_state, $state_country, $post_id = '')
	{
		$args  = array(
			'orderby'    => 'name',
			'parent'     => 0,
			'hide_empty' => false,
		);
		$terms = get_terms($taxonomy_name, $args);

		$result = array();
		foreach ($terms as $term) {
			$term_children            = get_terms($taxonomy_name, array(
				'parent'     => $term->term_id,
				'hide_empty' => false
			));
			$result[$term->term_id] = array();
			foreach ($term_children as $child) {
				$child_level_2                               = get_terms($taxonomy_name, array(
					'parent'     => $child->term_id,
					'hide_empty' => false
				));
				$result[$term->term_id][$child->term_id] = array();
				foreach ($child_level_2 as $grandchild) {
					$result[$term->term_id][$child->term_id][$grandchild->term_id] = array();
				}
			}
		}

		$target_by_id = array();
		$tax_terms    = get_the_terms($post_id, $taxonomy_name);
		if (!empty($tax_terms)) {
			foreach ($tax_terms as $tax_term) {
				$target_by_id[] = $tax_term->term_id;
			}
		}

		global $current_user;
		$user_id = $current_user->ID;

		if ($taxonomy_name === 'jobs-location') {
			$location_user = get_user_meta($user_id, CIVI_METABOX_PREFIX . 'jobs-location_user', true);
		} elseif ($taxonomy_name === 'candidate_locations') {
			$location_user = get_user_meta($user_id, CIVI_METABOX_PREFIX . 'candidate_locations_user', true);
		} elseif ($taxonomy_name === 'company-location') {
			$location_user = get_user_meta($user_id, CIVI_METABOX_PREFIX . 'company-location_user', true);
		} elseif ($taxonomy_name === 'service-location') {
			$location_user = get_user_meta($user_id, CIVI_METABOX_PREFIX . 'service-location_user', true);
		}

		if (!empty($location_user)) {
			$target_by_id[] = $location_user;
		}

		echo '<option value="">' . esc_html__('Select an option', 'civi-framework') . '</option>';
		foreach ($result as $key => $val) {
			$term_detail        = get_term_by('id', $key, $taxonomy_name);
			$name_state_country = civi_get_state_country($term_detail->term_id, $term_state, $city_state, $state_country);
			if (in_array($term_detail->term_id, $target_by_id)) {
				echo '<option value="' . $term_detail->term_id . '" selected data-level="1">' . $term_detail->name . $name_state_country . '</option>';
			} else {
				echo '<option value="' . $term_detail->term_id . '" data-level="1">' . $term_detail->name . $name_state_country . '</option>';
			}

			if (is_array($val)) {
				foreach ($val as $key => $val1) {
					$term_detail1        = get_term_by('id', $key, $taxonomy_name);
					$name_state_country1 = civi_get_state_country($term_detail->term_id, $term_state, $city_state, $state_country);
					if (in_array($term_detail1->term_id, $target_by_id)) {
						echo '<option value="' . $term_detail1->term_id . '" selected data-level="2">' . $term_detail1->name . $name_state_country1 . '</option>';
					} else {
						echo '<option value="' . $term_detail1->term_id . '" data-level="2">' . $term_detail1->name . $name_state_country1 . '</option>';
					}
					if (is_array($val1)) {
						foreach ($val1 as $key => $val2) {
							$term_detail2        = get_term_by('id', $key, $taxonomy_name);
							$name_state_country2 = civi_get_state_country($term_detail2->term_id, $term_state, $city_state, $state_country);
							if (in_array($term_detail2->term_id, $target_by_id)) {
								echo '<option value="' . $term_detail2->term_id . '" selected data-level="3">' . $term_detail2->name . $name_state_country2 . '</option>';
							} else {
								echo '<option value="' . $term_detail2->term_id . '" data-level="3">' . $term_detail2->name . $name_state_country2 . '</option>';
							}
						}
					}
				}
			}
		}
	}
}

/**
 * Get taxonomy slug by post id
 */
if (!function_exists('civi_get_taxonomy_slug_by_post_id')) {
	function civi_get_taxonomy_slug_by_post_id($post_id, $taxonomy_name)
	{
		$tax_terms = get_the_terms($post_id, $taxonomy_name);
		if (!empty($tax_terms)) {
			foreach ($tax_terms as $tax_term) {
				return $tax_term->slug;
			}
		}

		return null;
	}
}
/**
 * civi_get_taxonomy_slug
 */
if (!function_exists('civi_get_taxonomy_slug')) {
	function civi_get_taxonomy_slug($taxonomy_name, $target_term_slug = '', $prefix = '')
	{
		$taxonomy_terms = get_categories(
			array(
				'taxonomy'   => $taxonomy_name,
				'orderby'    => 'name',
				'order'      => 'ASC',
				'hide_empty' => false,
				'parent'     => 0
			)
		);

		if (!empty($taxonomy_terms)) {
			foreach ($taxonomy_terms as $term) {
				if ($target_term_slug == $term->slug) {
					echo '<option value="' . $term->slug . '" selected>' . $prefix . $term->name . '</option>';
				} else {
					echo '<option value="' . $term->slug . '">' . $prefix . $term->name . '</option>';
				}
			}
		}
	}
}

/**
 * get_taxonomy_by_post_id
 */
if (!function_exists('civi_get_taxonomy_by_post_id')) {
	function civi_get_taxonomy_by_post_id($post_id, $taxonomy_name, $show_default_none = true, $is_target_by_name = false, $order = false, $meta_key = '')
	{
		$args = array(
			'orderby'    => 'name',
			'parent'     => 0,
			'hide_empty' => false,
		);

		$args = apply_filters('civi/get_taxonomy_by_post_id/args', $args, $post_id, $taxonomy_name, $order);

		$terms = get_terms($taxonomy_name, $args);

		if (is_wp_error($terms)) {
			$terms = array();
		}

		if ($order == true && !empty($meta_key) && !empty($terms)) {
			usort($terms, function ($a, $b) use ($meta_key) {
				$a_meta = (int) get_term_meta($a->term_id, $meta_key, true);
				$b_meta = (int) get_term_meta($b->term_id, $meta_key, true);

				if ($a_meta === $b_meta) {
					return strcasecmp($a->name, $b->name);
				}

				if ($a_meta == 0 && $b_meta != 0) return 1;
				if ($a_meta != 0 && $b_meta == 0) return -1;

				return $a_meta - $b_meta;
			});
		}

		$result = array();

		foreach ($terms as $term) {
			$term_children            = get_terms($taxonomy_name, array(
				'parent'     => $term->term_id,
				'hide_empty' => false
			));
			$result[$term->term_id] = array();
			foreach ($term_children as $child) {
				$child_level_2                               = get_terms($taxonomy_name, array(
					'parent'     => $child->term_id,
					'hide_empty' => false
				));
				$result[$term->term_id][$child->term_id] = array();
				foreach ($child_level_2 as $grandchild) {
					$result[$term->term_id][$child->term_id][$grandchild->term_id] = array();
				}
			}
		}
		$target_by_name = array();
		$target_by_id   = array();
		$tax_terms      = get_the_terms($post_id, $taxonomy_name);
		if ($is_target_by_name) {
			if (!empty($tax_terms)) {
				foreach ($tax_terms as $tax_term) {
					$target_by_name[] = $tax_term->name;
				}
			}
			if ($show_default_none) {
				if (empty($target_by_name)) {
					echo '<option value="" selected>' . esc_html__('None', 'civi-framework') . '</option>';
				} else {
					echo '<option value="">' . esc_html__('None', 'civi-framework') . '</option>';
				}
			}
			civi_get_taxonomy_target_by_name($result, $target_by_name, $taxonomy_name);
		} else {
			if (!empty($tax_terms)) {
				foreach ($tax_terms as $tax_term) {
					$target_by_id[] = $tax_term->term_id;
				}
			}
			if ($show_default_none) {
				if ($target_by_id == 0 || empty($target_by_id)) {
					echo '<option value="" selected>' . esc_html__('Select an option', 'civi-framework') . '</option>';
				} else {
					echo '<option value="">' . esc_html__('Select an option', 'civi-framework') . '</option>';
				}
			}
			civi_get_taxonomy_target_by_id($result, $target_by_id, $taxonomy_name);
		}
	}
}

/**
 * get_taxonomy_target_by_name
 */
if (!function_exists('civi_get_taxonomy_target_by_name')) {
	function civi_get_taxonomy_target_by_name($taxonomy_terms, $target_term_name, $taxonomy_name, $prefix = "")
	{
		if (!empty($taxonomy_terms)) {
			foreach ($taxonomy_terms as $key => $val) {
				$term_detail = get_term_by('id', $key, $taxonomy_name);
				if (in_array($term_detail->name, $target_term_name)) {
					echo '<option value="' . $term_detail->slug . '" data-level="1" selected>' . $prefix . $term_detail->name . '</option>';
				} else {
					echo '<option value="' . $term_detail->slug . '" data-level="1">' . $term_detail->name . '</option>';
				}
				if (is_array($val)) {
					foreach ($val as $key => $val1) {
						$term_detail1 = get_term_by('id', $key, $taxonomy_name);
						if (in_array($term_detail1->name, $target_term_name)) {
							echo '<option value="' . $term_detail1->slug . '" data-level="2" selected>' . $prefix . $term_detail1->name . '</option>';
						} else {
							echo '<option value="' . $term_detail1->slug . '" data-level="2">' . $term_detail1->name . '</option>';
						}
					}
				}
			}
		}
	}
}

/**
 * get_taxonomy_target_by_id
 */
if (!function_exists('civi_get_taxonomy_target_by_id')) {
	function civi_get_taxonomy_target_by_id($taxonomy_terms, $target_term_id, $taxonomy_name, $prefix = "")
	{
		if (!empty($taxonomy_terms)) {
			foreach ($taxonomy_terms as $key => $val) {
				$term_detail = get_term_by('id', $key, $taxonomy_name);
				if (in_array($term_detail->term_id, $target_term_id)) {
					echo '<option value="' . $term_detail->term_id . '" data-level="1" selected>' . $prefix . $term_detail->name . '</option>';
				} else {
					echo '<option value="' . $term_detail->term_id . '" data-level="1">' . $term_detail->name . '</option>';
				}
				if (is_array($val)) {
					foreach ($val as $key => $val1) {
						$term_detail1 = get_term_by('id', $key, $taxonomy_name);
						if (in_array($term_detail1->term_id, $target_term_id)) {
							echo '<option value="' . $term_detail1->term_id . '" data-level="2" selected>' . $prefix . $term_detail1->name . '</option>';
						} else {
							echo '<option value="' . $term_detail1->term_id . '" data-level="2">' . $term_detail1->name . '</option>';
						}
					}
				}
			}
		}
	}
}

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 *
 * @param string|array $var Data to sanitize.
 *
 * @return string|array
 */
if (!function_exists('civi_clean')) {
	function civi_clean($var)
	{
		if (is_array($var)) {
			return array_map('civi_clean', $var);
		} else {
			return is_scalar($var) ? sanitize_textarea_field($var) : $var;
		}
	}
}

if (!function_exists('civi_clean_double_val')) {
	function civi_clean_double_val($string)
	{
		$string = preg_replace('/&#36;/', '', $string);
		$string = preg_replace('/[^A-Za-z0-9\-]/', '', $string);
		$string = preg_replace('/\D/', '', $string);

		return $string;
	}
}

/**
 * server protocol
 */
if (!function_exists('civi_server_protocol')) {
	if (!function_exists('civi_render_custom_field')) {
		function civi_render_custom_field($post_type, $only_filter = false)
		{
			$support_filter_post_types = array('jobs');

			if ($post_type == 'company') {
				$form_fields = civi_get_option('custom_field_company');
			} elseif ($post_type == 'candidate') {
				$form_fields = civi_get_option('custom_field_candidate');
			} else {
				$form_fields = civi_get_option('custom_field_jobs');
			}

			$meta_prefix = CIVI_METABOX_PREFIX;
			$configs = array();

			if ($form_fields && is_array($form_fields)) {
				foreach ($form_fields as $key => $field) {
					if ($only_filter && in_array($post_type, $support_filter_post_types)) {
						if (empty($field['show_in_filter'])) {
							continue;
						}
					}

					if (!empty($field['label'])) {
						$type   = $field['field_type'];
						$config = array(
							'title' => $field['label'],
							'id'    => $meta_prefix . sanitize_title($field['id']),
							'type'  => $type,
						);
						if (isset($field['job_custom_field_sidebar_order'])) {
							$config['job_custom_field_sidebar_order'] = (int) $field['job_custom_field_sidebar_order'];
						}
						$first_opt = '';
						switch ($type) {
							case 'checkbox_list':
							case 'select':
								$options     = array();
								$options_arr = isset($field['select_choices']) ? $field['select_choices'] : '';
								$options_arr = str_replace("\r\n", "\n", $options_arr);
								$options_arr = str_replace("\r", "\n", $options_arr);
								$options_arr = explode("\n", $options_arr);
								$first_opt   = !empty($options_arr) ? $options_arr[0] : '';
								foreach ($options_arr as $opt_value) {
									$options[$opt_value] = $opt_value;
								}
								$config['options'] = $options;
								break;
						}

						if ($post_type == 'candidate') {
							$config['tabs']    = $field['tabs'];
							$config['section'] = $field['section'];
						}

						if (in_array($type, array('select'))) {
							$config['default'] = $first_opt;
						}
						$configs[] = $config;
					}
				}
			}

			return $configs;
		}
	}
}

//GET SEARCH FILTER ITEM
if (!function_exists('get_search_filter_submenu')) {
	function get_search_filter_submenu($taxonomy_name, $title, $load_children = true, $order = 'title', $meta_key = '', $meta_type = 'NUMERIC', $context_filters = array())
	{
		if (isset($_GET[$taxonomy_name . '_id'])) {
			$tax_selected_id_list = civi_clean(wp_unslash($_GET[$taxonomy_name . '_id']));
		} else {
			$tax_selected_id_list = array();
		}

		$class_list_wrapper = 'filter-control custom-scrollbar ' . $taxonomy_name;

		$submenu_arg = array(
			'taxonomy_name'        => $taxonomy_name,
			'taxonomy_parent_id'   => 0,
			'tax_selected_id_list' => $tax_selected_id_list,
			'class_list_wrapper'   => $class_list_wrapper,
		);

		$class_wrapper = 'filter-' . $taxonomy_name;

		?>
		<div class="<?php echo esc_attr($class_wrapper); ?>">
			<div class="entry-filter">
				<h4><?php echo esc_attr($title); ?></h4>
				<?php echo render_item_checkbox($submenu_arg, $load_children, $order, $meta_key, $meta_type, $context_filters); ?>
			</div>
		</div>
		<?php
	}
}
//GET CHECKBOX ITEM FOR SUBMENU
if (!function_exists('render_item_checkbox')) {
	function render_item_checkbox($submenu_arg = array(), $load_children = true, $order = 'title', $meta_key = '', $meta_type = 'NUMERIC', $context_filters = array())
	{
		$taxonomy_name        = $submenu_arg['taxonomy_name'];
		$taxonomy_parent_id   = $submenu_arg['taxonomy_parent_id'];
		$tax_selected_id_list = $submenu_arg['tax_selected_id_list'];
		$class_list_wrapper   = $submenu_arg['class_list_wrapper'];

		static $all_terms_cache = [];
		$cache_key = $taxonomy_name . '_' . $order . '_' . $meta_key;

		if (!isset($all_terms_cache[$cache_key])) {
			$args = array(
				'taxonomy'   => $taxonomy_name,
				'hide_empty' => 0,
				'fields'     => 'all',
			);

			$args['orderby'] = 'title';
			$args['order'] = 'ASC';

			$all_terms = get_terms($args);

			if (is_wp_error($all_terms) || empty($all_terms)) {
				return;
			}

			if (empty($meta_key)) {
				foreach ($all_terms as $term) {
					$term->priority = (int) get_term_meta($term->term_id, 'priority', true);
				}
			}

			$all_terms_cache[$cache_key] = $all_terms;
		}
		$all_terms = $all_terms_cache[$cache_key];

		$taxonomy_object_list = array_filter($all_terms, function ($term) use ($taxonomy_parent_id) {
			return $term->parent == $taxonomy_parent_id;
		});

		if (empty($taxonomy_object_list)) {
			return;
		}

		if (!empty($meta_key)) {
			usort($taxonomy_object_list, function ($a, $b) use ($meta_key, $meta_type) {
				$meta_a = get_term_meta($a->term_id, $meta_key, true);
				$meta_b = get_term_meta($b->term_id, $meta_key, true);

				$has_meta_a = $meta_a !== '';
				$has_meta_b = $meta_b !== '';

				if ($has_meta_a && !$has_meta_b) return -1;
				if (!$has_meta_a && $has_meta_b) return 1;

				if (!$has_meta_a && !$has_meta_b) {
					return strcasecmp($a->name, $b->name);
				}

				if ($meta_type === 'NUMERIC') {
					$meta_a = (int) $meta_a;
					$meta_b = (int) $meta_b;

					if ($meta_a !== $meta_b) {
						return $meta_a - $meta_b;
					}
				} else {
					$result = strcasecmp($meta_a, $meta_b);
					if ($result !== 0) return $result;
				}

				return strcasecmp($a->name, $b->name);
			});
		}

		$list = '<ul class="' . esc_attr($class_list_wrapper) . '">';
		foreach ($taxonomy_object_list as $term_object) {
			$check = in_array($term_object->term_id, (array)$tax_selected_id_list) ? 'checked' : '';

			$child_categories = array_filter($all_terms, function ($term) use ($term_object) {
				return $term->parent == $term_object->term_id;
			});

			// For sidebar count display, respect enable_jobs_show_expires setting
			$term_count = civi_get_term_post_count_for_sidebar($term_object->term_id, $taxonomy_name);

			$child_count = 0;
			foreach ($child_categories as $child_category) {
				$child_count += civi_get_term_post_count_for_sidebar($child_category->term_id, $taxonomy_name);
			}
			$total_count = $term_count + $child_count;

			$list .= '<li>';
			$list .= '<input type="checkbox" class="custom-checkbox input-control" name="' . esc_attr($taxonomy_name) . '_id[]" value="' . esc_attr($term_object->term_id) . '" id="civi_' . esc_attr($term_object->term_id) . '" ' . $check . '/>';
			$list .= '<label for="civi_' . esc_attr($term_object->term_id) . '">' . esc_html($term_object->name) . '<span class="count">(' . esc_html($total_count) . ')</span></label>';

			if ($load_children) {
				$submenu_arg['class_list_wrapper'] = '';
				$submenu_arg['taxonomy_parent_id'] = $term_object->term_id;
				$list .= render_item_checkbox($submenu_arg, true, $order, $meta_key, $meta_type, $context_filters);
			}

			$list .= '</li>';
		}

		return $list . '</ul>';
	}
}

//GET CITY FROM ADDRESS
if (!function_exists('get_city_from_address')) {
	function get_city_from_address($address)
	{
		$api_key       = civi_get_option('googlemap_api_key', 'AIzaSyBvPDNG6pePr9iFpeRKaOlaZF_l0oT3lWk');
		$geocodeApiUrl = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address) . '&key=' . $api_key;

		$response = file_get_contents($geocodeApiUrl);

		$data = json_decode($response, true);
		if ($data['status'] === 'OK') {
			foreach ($data['results'][0]['address_components'] as $component) {
				if (in_array('locality', $component['types'])) {
					return $component['long_name'];
				}
			}
		}

		return null;
	}
}

// CHECK IS CITY
if (!function_exists('is_city_name')) {
	function is_city_name($name)
	{
		$apiUsername = 'ductrung'; // Replace with your GeoNames username

		// Make a request to the GeoNames API
		$url      = "http://api.geonames.org/searchJSON?q=" . urlencode($name) . "&maxRows=1&username=" . $apiUsername;
		$response = file_get_contents($url);

		// Parse the response as JSON
		$data = json_decode($response, true);

		// Check if the API response contains a city
		if (isset($data['geonames']) && count($data['geonames']) > 0) {
			$type = $data['geonames'][0]['fclName'];

			return $type == 'city';
		}

		return false; // City not found
	}
}

/**
 * Register Model of AI
 */
if (!function_exists('model_ai_helper')) {
	function model_ai_helper()
	{
		return array(
			'gpt-4o'             => esc_html__('GPT-4o (Latest, Recommended)', 'civi-framework'),
			'gpt-4o-mini'        => esc_html__('GPT-4o Mini (Fast & Cost-effective)', 'civi-framework'),
			'gpt-4-turbo'        => esc_html__('GPT-4 Turbo', 'civi-framework'),
			'gpt-4'               => esc_html__('GPT-4 (Legacy)', 'civi-framework'),
			'gpt-3.5-turbo'      => esc_html__('GPT-3.5 Turbo (Economy)', 'civi-framework'),
		);
	}
}

/**
 * Register Tone of AI
 */
if (!function_exists('tone_ai_helper')) {
	function tone_ai_helper()
	{
		return array(
			'professional' => esc_html__('Professional', 'civi-framework'),
			'funny'        => esc_html__('Funny', 'civi-framework'),
			'casual'       => esc_html__('Casual', 'civi-framework'),
			'excited'      => esc_html__('Excited', 'civi-framework'),
			'witty'        => esc_html__('Witty', 'civi-framework'),
			'sarcastic'    => esc_html__('Sarcastic', 'civi-framework'),
			'feminine'     => esc_html__('Feminine', 'civi-framework'),
			'masculine'    => esc_html__('Masculine', 'civi-framework'),
			'bold'         => esc_html__('Bold', 'civi-framework'),
			'dramatic'     => esc_html__('Dramatic', 'civi-framework'),
			'grumpy'       => esc_html__('Grumpy', 'civi-framework'),
			'secretive'    => esc_html__('Secretive', 'civi-framework'),
		);
	}
}

/**
 * Register Language of AI
 */
if (!function_exists('language_ai_helper')) {
	function language_ai_helper()
	{
		return array(
			'en' => esc_html__('English (en)', 'civi-framework'),
			'zh' => esc_html__('中文 (zh)', 'civi-framework'),
			'hi' => esc_html__('हिन्दी (hi)', 'civi-framework'),
			'es' => esc_html__('Español (es)', 'civi-framework'),
			'fr' => esc_html__('Français (fr)', 'civi-framework'),
			'bn' => esc_html__('বাংলা (bn)', 'civi-framework'),
			'ar' => esc_html__('العربية (ar)', 'civi-framework'),
			'ru' => esc_html__('Русский (ru)', 'civi-framework'),
			'pt' => esc_html__('Português (pt)', 'civi-framework'),
			'id' => esc_html__('Bahasa Indonesia (id)', 'civi-framework'),
			'ur' => esc_html__('اردو (ur)', 'civi-framework'),
			'ja' => esc_html__('日本語 (ja)', 'civi-framework'),
			'de' => esc_html__('Deutsch (de)', 'civi-framework'),
			'jv' => esc_html__('Basa Jawa (jv)', 'civi-framework'),
			'pa' => esc_html__('ਪੰਜਾਬੀ (pa)', 'civi-framework'),
			'te' => esc_html__('తెలుగు (te)', 'civi-framework'),
			'mr' => esc_html__('मराठी (mr)', 'civi-framework'),
			'ko' => esc_html__('한국어 (ko)', 'civi-framework'),
			'tr' => esc_html__('Türkçe (tr)', 'civi-framework'),
			'ta' => esc_html__('தமிழ் (ta)', 'civi-framework'),
			'it' => esc_html__('Italiano (it)', 'civi-framework'),
			'vi' => esc_html__('Tiếng Việt (vi)', 'civi-framework'),
			'th' => esc_html__('ไทย (th)', 'civi-framework'),
			'pl' => esc_html__('Polski (pl)', 'civi-framework'),
			'fa' => esc_html__('فارسی (fa)', 'civi-framework'),
			'uk' => esc_html__('Українська (uk)', 'civi-framework'),
			'ms' => esc_html__('Bahasa Melayu (ms)', 'civi-framework'),
			'ro' => esc_html__('Română (ro)', 'civi-framework'),
			'nl' => esc_html__('Nederlands (nl)', 'civi-framework'),
			'hu' => esc_html__('Magyar (hu)', 'civi-framework'),
			'he' => esc_html__('Hebrew (he)', 'civi-framework'),
			'sk' => esc_html__('Slovenčina (sk)', 'civi-framework'),
		);
	}
}
/**
 * Register Phone Prefix Code
 */
if (!function_exists('phone_prefix_code')) {
	function phone_prefix_code()
	{
		return apply_filters(
			'civi_phone_prefix_code',
			array(
				'ax' => array(
					'name' => esc_html__('Åland Islands', 'civi-framework'),
					'code' => '+358',
				),
				'af' => array(
					'name' => esc_html__('Afghanistan', 'civi-framework'),
					'code' => '+93',
				),
				'al' => array(
					'name' => esc_html__('Albania', 'civi-framework'),
					'code' => '+355',
				),
				'dz' => array(
					'name' => esc_html__('Algeria', 'civi-framework'),
					'code' => '+213',
				),
				'as' => array(
					'name' => esc_html__('American Samoa', 'civi-framework'),
					'code' => '+1684',
				),
				'ad' => array(
					'name' => esc_html__('Andorra', 'civi-framework'),
					'code' => '+376',
				),
				'ao' => array(
					'name' => esc_html__('Angola', 'civi-framework'),
					'code' => '+244',
				),
				'ai' => array(
					'name' => esc_html__('Anguilla', 'civi-framework'),
					'code' => '+1264',
				),
				'ag' => array(
					'name' => esc_html__('Antigua and Barbuda', 'civi-framework'),
					'code' => '+1268',
				),
				'ar' => array(
					'name' => esc_html__('Argentina', 'civi-framework'),
					'code' => '+54',
				),
				'am' => array(
					'name' => esc_html__('Armenia', 'civi-framework'),
					'code' => '+374',
				),
				'aw' => array(
					'name' => esc_html__('Aruba', 'civi-framework'),
					'code' => '+297',
				),
				'au' => array(
					'name' => esc_html__('Australia', 'civi-framework'),
					'code' => '+61',
				),
				'at' => array(
					'name' => esc_html__('Austria', 'civi-framework'),
					'code' => '+43',
				),
				'az' => array(
					'name' => esc_html__('Azerbaijan', 'civi-framework'),
					'code' => '+994',
				),
				'bs' => array(
					'name' => esc_html__('Bahamas', 'civi-framework'),
					'code' => '+1242',
				),
				'bh' => array(
					'name' => esc_html__('Bahrain', 'civi-framework'),
					'code' => '+973',
				),
				'bd' => array(
					'name' => esc_html__('Bangladesh', 'civi-framework'),
					'code' => '+880',
				),
				'bb' => array(
					'name' => esc_html__('Barbados', 'civi-framework'),
					'code' => '+1246',
				),
				'by' => array(
					'name' => esc_html__('Belarus', 'civi-framework'),
					'code' => '+375',
				),
				'be' => array(
					'name' => esc_html__('Belgium', 'civi-framework'),
					'code' => '+32',
				),
				'bz' => array(
					'name' => esc_html__('Belize', 'civi-framework'),
					'code' => '+501',
				),
				'bj' => array(
					'name' => esc_html__('Benin', 'civi-framework'),
					'code' => '+229',
				),
				'bm' => array(
					'name' => esc_html__('Bermuda', 'civi-framework'),
					'code' => '+1441',
				),
				'bt' => array(
					'name' => esc_html__('Bhutan', 'civi-framework'),
					'code' => '+975',
				),
				'bo' => array(
					'name' => esc_html__('Bolivia', 'civi-framework'),
					'code' => '+591',
				),
				'ba' => array(
					'name' => esc_html__('Bosnia and Herzegovina', 'civi-framework'),
					'code' => '+387',
				),
				'bw' => array(
					'name' => esc_html__('Botswana', 'civi-framework'),
					'code' => '+267',
				),
				'br' => array(
					'name' => esc_html__('Brazil', 'civi-framework'),
					'code' => '+55',
				),
				'io' => array(
					'name' => esc_html__('British Indian Ocean Territory', 'civi-framework'),
					'code' => '+246',
				),
				'vg' => array(
					'name' => esc_html__('British Virgin Islands', 'civi-framework'),
					'code' => '+1284',
				),
				'bn' => array(
					'name' => esc_html__('Brunei', 'civi-framework'),
					'code' => '+673',
				),
				'bg' => array(
					'name' => esc_html__('Bulgaria', 'civi-framework'),
					'code' => '+359',
				),
				'bf' => array(
					'name' => esc_html__('Burkina Faso', 'civi-framework'),
					'code' => '+226',
				),
				'bi' => array(
					'name' => esc_html__('Burundi', 'civi-framework'),
					'code' => '+257',
				),
				'kh' => array(
					'name' => esc_html__('Cambodia', 'civi-framework'),
					'code' => '+855',
				),
				'cm' => array(
					'name' => esc_html__('Cameroon', 'civi-framework'),
					'code' => '+237',
				),
				'ca' => array(
					'name' => esc_html__('Canada', 'civi-framework'),
					'code' => '+1',
				),
				'cv' => array(
					'name' => esc_html__('Cape Verde', 'civi-framework'),
					'code' => '+238',
				),
				'bq' => array(
					'name' => esc_html__('Caribbean Netherlands', 'civi-framework'),
					'code' => '+599',
				),
				'ky' => array(
					'name' => esc_html__('Cayman Islands', 'civi-framework'),
					'code' => '+1345',
				),
				'cf' => array(
					'name' => esc_html__('Central African Republic', 'civi-framework'),
					'code' => '+236',
				),
				'td' => array(
					'name' => esc_html__('Chad', 'civi-framework'),
					'code' => '+235',
				),
				'cl' => array(
					'name' => esc_html__('Chile', 'civi-framework'),
					'code' => '+56',
				),
				'cn' => array(
					'name' => esc_html__('China', 'civi-framework'),
					'code' => '+86',
				),
				'cx' => array(
					'name' => esc_html__('Christmas Island', 'civi-framework'),
					'code' => '+61',
				),
				'co' => array(
					'name' => esc_html__('Colombia', 'civi-framework'),
					'code' => '+57',
				),
				'km' => array(
					'name' => esc_html__('Comoros', 'civi-framework'),
					'code' => '+269',
				),
				'cd' => array(
					'name' => esc_html__('Congo DRC', 'civi-framework'),
					'code' => '+243',
				),
				'cg' => array(
					'name' => esc_html__('Congo Republic', 'civi-framework'),
					'code' => '+242',
				),
				'ck' => array(
					'name' => esc_html__('Cook Islands', 'civi-framework'),
					'code' => '+682',
				),
				'cr' => array(
					'name' => esc_html__('Costa Rica', 'civi-framework'),
					'code' => '+506',
				),
				'ci' => array(
					'name' => esc_html__("Côte d'Ivoire", 'civi-framework'),
					'code' => '+225',
				),
				'hr' => array(
					'name' => esc_html__('Croatia', 'civi-framework'),
					'code' => '+385',
				),
				'cu' => array(
					'name' => esc_html__('Cuba', 'civi-framework'),
					'code' => '+53',
				),
				'cw' => array(
					'name' => esc_html__('Curaçao', 'civi-framework'),
					'code' => '+599',
				),
				'cy' => array(
					'name' => esc_html__('Cyprus', 'civi-framework'),
					'code' => '+357',
				),
				'cz' => array(
					'name' => esc_html__('Czech Republic', 'civi-framework'),
					'code' => '+420',
				),
				'dk' => array(
					'name' => esc_html__('Denmark', 'civi-framework'),
					'code' => '+45',
				),
				'dj' => array(
					'name' => esc_html__('Djibouti', 'civi-framework'),
					'code' => '+253',
				),
				'dm' => array(
					'name' => esc_html__('Dominica', 'civi-framework'),
					'code' => '+1767',
				),
				'do' => array(
					'name' => esc_html__('Dominican Republic', 'civi-framework'),
					'code' => '+1',
				),
				'ec' => array(
					'name' => esc_html__('Ecuador', 'civi-framework'),
					'code' => '+593',
				),
				'eg' => array(
					'name' => esc_html__('Egypt', 'civi-framework'),
					'code' => '+20',
				),
				'sv' => array(
					'name' => esc_html__('El Salvador', 'civi-framework'),
					'code' => '+503',
				),
				'gq' => array(
					'name' => esc_html__('Equatorial Guinea', 'civi-framework'),
					'code' => '+240',
				),
				'er' => array(
					'name' => esc_html__('Eritrea', 'civi-framework'),
					'code' => '+291',
				),
				'ee' => array(
					'name' => esc_html__('Estonia', 'civi-framework'),
					'code' => '+372',
				),
				'et' => array(
					'name' => esc_html__('Ethiopia', 'civi-framework'),
					'code' => '+251',
				),
				'fk' => array(
					'name' => esc_html__('Falkland Islands', 'civi-framework'),
					'code' => '+500',
				),
				'fo' => array(
					'name' => esc_html__('Faroe Islands', 'civi-framework'),
					'code' => '+298',
				),
				'fj' => array(
					'name' => esc_html__('Fiji', 'civi-framework'),
					'code' => '+679',
				),
				'fi' => array(
					'name' => esc_html__('Finland', 'civi-framework'),
					'code' => '+358',
				),
				'fr' => array(
					'name' => esc_html__('France', 'civi-framework'),
					'code' => '+33',
				),
				'gf' => array(
					'name' => esc_html__('French Guiana', 'civi-framework'),
					'code' => '+594',
				),
				'pf' => array(
					'name' => esc_html__('French Polynesia', 'civi-framework'),
					'code' => '+689',
				),
				'ga' => array(
					'name' => esc_html__('Gabon', 'civi-framework'),
					'code' => '+241',
				),
				'gm' => array(
					'name' => esc_html__('Gambia', 'civi-framework'),
					'code' => '+220',
				),
				'ge' => array(
					'name' => esc_html__('Georgia', 'civi-framework'),
					'code' => '+995',
				),
				'de' => array(
					'name' => esc_html__('Germany', 'civi-framework'),
					'code' => '+49',
				),
				'gh' => array(
					'name' => esc_html__('Ghana', 'civi-framework'),
					'code' => '+233',
				),
				'gi' => array(
					'name' => esc_html__('Gibraltar', 'civi-framework'),
					'code' => '+350',
				),
				'gr' => array(
					'name' => esc_html__('Greece', 'civi-framework'),
					'code' => '+30',
				),
				'gl' => array(
					'name' => esc_html__('Greenland', 'civi-framework'),
					'code' => '+299',
				),
				'gd' => array(
					'name' => esc_html__('Grenada', 'civi-framework'),
					'code' => '+1473',
				),
				'gp' => array(
					'name' => esc_html__('Guadeloupe', 'civi-framework'),
					'code' => '+590',
				),
				'gu' => array(
					'name' => esc_html__('Guam', 'civi-framework'),
					'code' => '+1671',
				),
				'gt' => array(
					'name' => esc_html__('Guatemala', 'civi-framework'),
					'code' => '+502',
				),
				'gg' => array(
					'name' => esc_html__('Guernsey', 'civi-framework'),
					'code' => '+44',
				),
				'gn' => array(
					'name' => esc_html__('Guinea', 'civi-framework'),
					'code' => '+224',
				),
				'gw' => array(
					'name' => esc_html__('Guinea-Bissau', 'civi-framework'),
					'code' => '+245',
				),
				'gy' => array(
					'name' => esc_html__('Guyana', 'civi-framework'),
					'code' => '+592',
				),
				'ht' => array(
					'name' => esc_html__('Haiti', 'civi-framework'),
					'code' => '+509',
				),
				'hn' => array(
					'name' => esc_html__('Honduras', 'civi-framework'),
					'code' => '+504',
				),
				'hk' => array(
					'name' => esc_html__('Hong Kong', 'civi-framework'),
					'code' => '+852',
				),
				'hu' => array(
					'name' => esc_html__('Hungary', 'civi-framework'),
					'code' => '+36',
				),
				'is' => array(
					'name' => esc_html__('Iceland', 'civi-framework'),
					'code' => '+354',
				),
				'in' => array(
					'name' => esc_html__('India', 'civi-framework'),
					'code' => '+91',
				),
				'id' => array(
					'name' => esc_html__('Indonesia', 'civi-framework'),
					'code' => '+62',
				),
				'ir' => array(
					'name' => esc_html__('Iran', 'civi-framework'),
					'code' => '+98',
				),
				'iq' => array(
					'name' => esc_html__('Iraq', 'civi-framework'),
					'code' => '+964',
				),
				'ie' => array(
					'name' => esc_html__('Ireland', 'civi-framework'),
					'code' => '+353',
				),
				'im' => array(
					'name' => esc_html__('Isle of Man', 'civi-framework'),
					'code' => '+44',
				),
				'il' => array(
					'name' => esc_html__('Israel', 'civi-framework'),
					'code' => '+972',
				),
				'it' => array(
					'name' => esc_html__('Italy', 'civi-framework'),
					'code' => '+39',
				),
				'jm' => array(
					'name' => esc_html__('Jamaica', 'civi-framework'),
					'code' => '+1876',
				),
				'jp' => array(
					'name' => esc_html__('Japan', 'civi-framework'),
					'code' => '+81',
				),
				'je' => array(
					'name' => esc_html__('Jersey', 'civi-framework'),
					'code' => '+44',
				),
				'jo' => array(
					'name' => esc_html__('Jordan', 'civi-framework'),
					'code' => '+962',
				),
				'kz' => array(
					'name' => esc_html__('Kazakhstan', 'civi-framework'),
					'code' => '+7',
				),
				'ke' => array(
					'name' => esc_html__('Kenya', 'civi-framework'),
					'code' => '+254',
				),
				'ki' => array(
					'name' => esc_html__('Kiribati', 'civi-framework'),
					'code' => '+686',
				),
				'xk' => array(
					'name' => esc_html__('Kosovo', 'civi-framework'),
					'code' => '+383',
				),
				'kw' => array(
					'name' => esc_html__('Kuwait', 'civi-framework'),
					'code' => '+965',
				),
				'kg' => array(
					'name' => esc_html__('Kyrgyzstan', 'civi-framework'),
					'code' => '+996',
				),
				'la' => array(
					'name' => esc_html__('Laos', 'civi-framework'),
					'code' => '+856',
				),
				'lv' => array(
					'name' => esc_html__('Latvia', 'civi-framework'),
					'code' => '+371',
				),
				'lb' => array(
					'name' => esc_html__('Lebanon', 'civi-framework'),
					'code' => '+961',
				),
				'ls' => array(
					'name' => esc_html__('Lesotho', 'civi-framework'),
					'code' => '+266',
				),
				'lr' => array(
					'name' => esc_html__('Liberia', 'civi-framework'),
					'code' => '+231',
				),
				'ly' => array(
					'name' => esc_html__('Libya', 'civi-framework'),
					'code' => '+218',
				),
				'li' => array(
					'name' => esc_html__('Liechtenstein', 'civi-framework'),
					'code' => '+423',
				),
				'lt' => array(
					'name' => esc_html__('Lithuania', 'civi-framework'),
					'code' => '+370',
				),
				'lu' => array(
					'name' => esc_html__('Luxembourg', 'civi-framework'),
					'code' => '+352',
				),
				'mo' => array(
					'name' => esc_html__('Macau', 'civi-framework'),
					'code' => '+853',
				),
				'mk' => array(
					'name' => esc_html__('Macedonia', 'civi-framework'),
					'code' => '+389',
				),
				'mg' => array(
					'name' => esc_html__('Madagascar', 'civi-framework'),
					'code' => '+261',
				),
				'mw' => array(
					'name' => esc_html__('Malawi', 'civi-framework'),
					'code' => '+265',
				),
				'my' => array(
					'name' => esc_html__('Malaysia', 'civi-framework'),
					'code' => '+60',
				),
				'mv' => array(
					'name' => esc_html__('Maldives', 'civi-framework'),
					'code' => '+960',
				),
				'ml' => array(
					'name' => esc_html__('Mali', 'civi-framework'),
					'code' => '+223',
				),
				'mt' => array(
					'name' => esc_html__('Malta', 'civi-framework'),
					'code' => '+356',
				),
				'mh' => array(
					'name' => esc_html__('Marshall Islands', 'civi-framework'),
					'code' => '+692',
				),
				'mq' => array(
					'name' => esc_html__('Martinique', 'civi-framework'),
					'code' => '+596',
				),
				'mr' => array(
					'name' => esc_html__('Mauritania', 'civi-framework'),
					'code' => '+222',
				),
				'mu' => array(
					'name' => esc_html__('Mauritius', 'civi-framework'),
					'code' => '+230',
				),
				'yt' => array(
					'name' => esc_html__('Mayotte', 'civi-framework'),
					'code' => '+262',
				),
				'mx' => array(
					'name' => esc_html__('Mexico', 'civi-framework'),
					'code' => '+52',
				),
				'fm' => array(
					'name' => esc_html__('Micronesia', 'civi-framework'),
					'code' => '+691',
				),
				'md' => array(
					'name' => esc_html__('Moldova', 'civi-framework'),
					'code' => '+373',
				),
				'mc' => array(
					'name' => esc_html__('Monaco', 'civi-framework'),
					'code' => '+377',
				),
				'mn' => array(
					'name' => esc_html__('Mongolia', 'civi-framework'),
					'code' => '+976',
				),
				'me' => array(
					'name' => esc_html__('Montenegro', 'civi-framework'),
					'code' => '+382',
				),
				'ms' => array(
					'name' => esc_html__('Montserrat', 'civi-framework'),
					'code' => '+1664',
				),
				'ma' => array(
					'name' => esc_html__('Morocco', 'civi-framework'),
					'code' => '+212',
				),
				'mz' => array(
					'name' => esc_html__('Mozambique', 'civi-framework'),
					'code' => '+258',
				),
				'mm' => array(
					'name' => esc_html__('Myanmar', 'civi-framework'),
					'code' => '+95',
				),
				'na' => array(
					'name' => esc_html__('Namibia', 'civi-framework'),
					'code' => '+264',
				),
				'nr' => array(
					'name' => esc_html__('Nauru', 'civi-framework'),
					'code' => '+674',
				),
				'np' => array(
					'name' => esc_html__('Nepal', 'civi-framework'),
					'code' => '+977',
				),
				'nl' => array(
					'name' => esc_html__('Netherlands', 'civi-framework'),
					'code' => '+31',
				),
				'nc' => array(
					'name' => esc_html__('New Caledonia', 'civi-framework'),
					'code' => '+687',
				),
				'nz' => array(
					'name' => esc_html__('New Zealand', 'civi-framework'),
					'code' => '+64',
				),
				'ni' => array(
					'name' => esc_html__('Nicaragua', 'civi-framework'),
					'code' => '+505',
				),
				'ne' => array(
					'name' => esc_html__('Niger', 'civi-framework'),
					'code' => '+227',
				),
				'ng' => array(
					'name' => esc_html__('Nigeria', 'civi-framework'),
					'code' => '+234',
				),
				'nu' => array(
					'name' => esc_html__('Niue', 'civi-framework'),
					'code' => '+683',
				),
				'nf' => array(
					'name' => esc_html__('Norfolk Island', 'civi-framework'),
					'code' => '+672',
				),
				'kp' => array(
					'name' => esc_html__('North Korea', 'civi-framework'),
					'code' => '+850',
				),
				'mp' => array(
					'name' => esc_html__('Northern Mariana Islands', 'civi-framework'),
					'code' => '+1670',
				),
				'no' => array(
					'name' => esc_html__('Norway', 'civi-framework'),
					'code' => '+47',
				),
				'om' => array(
					'name' => esc_html__('Oman', 'civi-framework'),
					'code' => '+968',
				),
				'pk' => array(
					'name' => esc_html__('Pakistan', 'civi-framework'),
					'code' => '+92',
				),
				'pw' => array(
					'name' => esc_html__('Palau', 'civi-framework'),
					'code' => '+680',
				),
				'ps' => array(
					'name' => esc_html__('Palestine', 'civi-framework'),
					'code' => '+970',
				),
				'pa' => array(
					'name' => esc_html__('Panama', 'civi-framework'),
					'code' => '+507',
				),
				'pg' => array(
					'name' => esc_html__('Papua New Guinea', 'civi-framework'),
					'code' => '+675',
				),
				'py' => array(
					'name' => esc_html__('Paraguay', 'civi-framework'),
					'code' => '+595',
				),
				'pe' => array(
					'name' => esc_html__('Peru', 'civi-framework'),
					'code' => '+51',
				),
				'ph' => array(
					'name' => esc_html__('Philippines', 'civi-framework'),
					'code' => '+63',
				),
				'pl' => array(
					'name' => esc_html__('Poland', 'civi-framework'),
					'code' => '+48',
				),
				'pt' => array(
					'name' => esc_html__('Portugal', 'civi-framework'),
					'code' => '+351',
				),
				'qa' => array(
					'name' => esc_html__('Qatar', 'civi-framework'),
					'code' => '+974',
				),
				're' => array(
					'name' => esc_html__('Réunion', 'civi-framework'),
					'code' => '+262',
				),
				'ro' => array(
					'name' => esc_html__('Romania', 'civi-framework'),
					'code' => '+40',
				),
				'ru' => array(
					'name' => esc_html__('Russia', 'civi-framework'),
					'code' => '+7',
				),
				'rw' => array(
					'name' => esc_html__('Rwanda', 'civi-framework'),
					'code' => '+250',
				),
				'bl' => array(
					'name' => esc_html__('Saint Barthelemy', 'civi-framework'),
					'code' => '+590',
				),
				'sh' => array(
					'name' => esc_html__('Saint Helena', 'civi-framework'),
					'code' => '+290',
				),
				'kn' => array(
					'name' => esc_html__('Saint Kitts and Nevis', 'civi-framework'),
					'code' => '+1869',
				),
				'lc' => array(
					'name' => esc_html__('Saint Lucia', 'civi-framework'),
					'code' => '+1758',
				),
				'mf' => array(
					'name' => esc_html__('Saint Martin', 'civi-framework'),
					'code' => '+590',
				),
				'pm' => array(
					'name' => esc_html__('Saint Pierre and Miquelon', 'civi-framework'),
					'code' => '+508',
				),
				'vc' => array(
					'name' => esc_html__('Saint Vincent and the Grenadines', 'civi-framework'),
					'code' => '+1784',
				),
				'ws' => array(
					'name' => esc_html__('Samoa', 'civi-framework'),
					'code' => '+685',
				),
				'sm' => array(
					'name' => esc_html__('San Marino', 'civi-framework'),
					'code' => '+378',
				),
				'st' => array(
					'name' => esc_html__('São Tomé and Príncipe', 'civi-framework'),
					'code' => '+239',
				),
				'sa' => array(
					'name' => esc_html__('Saudi Arabia', 'civi-framework'),
					'code' => '+966',
				),
				'sn' => array(
					'name' => esc_html__('Senegal', 'civi-framework'),
					'code' => '+221',
				),
				'rs' => array(
					'name' => esc_html__('Serbia', 'civi-framework'),
					'code' => '+381',
				),
				'sc' => array(
					'name' => esc_html__('Seychelles', 'civi-framework'),
					'code' => '+248',
				),
				'sl' => array(
					'name' => esc_html__('Sierra Leone', 'civi-framework'),
					'code' => '+232',
				),
				'sg' => array(
					'name' => esc_html__('Singapore', 'civi-framework'),
					'code' => '+65',
				),
				'sx' => array(
					'name' => esc_html__('Sint Maarten', 'civi-framework'),
					'code' => '+1721',
				),
				'sk' => array(
					'name' => esc_html__('Slovakia', 'civi-framework'),
					'code' => '+421',
				),
				'si' => array(
					'name' => esc_html__('Slovenia', 'civi-framework'),
					'code' => '+386',
				),
				'sb' => array(
					'name' => esc_html__('Solomon Islands', 'civi-framework'),
					'code' => '+677',
				),
				'so' => array(
					'name' => esc_html__('Somalia', 'civi-framework'),
					'code' => '+252',
				),
				'za' => array(
					'name' => esc_html__('South Africa', 'civi-framework'),
					'code' => '+27',
				),
				'kr' => array(
					'name' => esc_html__('South Korea', 'civi-framework'),
					'code' => '+82',
				),
				'ss' => array(
					'name' => esc_html__('South Sudan', 'civi-framework'),
					'code' => '+211',
				),
				'es' => array(
					'name' => esc_html__('Spain', 'civi-framework'),
					'code' => '+34',
				),
				'lk' => array(
					'name' => esc_html__('Sri Lanka', 'civi-framework'),
					'code' => '+94',
				),
				'sd' => array(
					'name' => esc_html__('Sudan', 'civi-framework'),
					'code' => '+249',
				),
				'sr' => array(
					'name' => esc_html__('Suriname', 'civi-framework'),
					'code' => '+597',
				),
				'sj' => array(
					'name' => esc_html__('Svalbard and Jan Mayen', 'civi-framework'),
					'code' => '+47',
				),
				'sz' => array(
					'name' => esc_html__('Swaziland', 'civi-framework'),
					'code' => '+268',
				),
				'se' => array(
					'name' => esc_html__('Sweden', 'civi-framework'),
					'code' => '+46',
				),
				'ch' => array(
					'name' => esc_html__('Switzerland', 'civi-framework'),
					'code' => '+41',
				),
				'sy' => array(
					'name' => esc_html__('Syria', 'civi-framework'),
					'code' => '+963',
				),
				'tw' => array(
					'name' => esc_html__('Taiwan', 'civi-framework'),
					'code' => '+886',
				),
				'tj' => array(
					'name' => esc_html__('Tajikistan', 'civi-framework'),
					'code' => '+992',
				),
				'tz' => array(
					'name' => esc_html__('Tanzania', 'civi-framework'),
					'code' => '+255',
				),
				'th' => array(
					'name' => esc_html__('Thailand', 'civi-framework'),
					'code' => '+66',
				),
				'tl' => array(
					'name' => esc_html__('Timor-Leste', 'civi-framework'),
					'code' => '+670',
				),
				'tg' => array(
					'name' => esc_html__('Togo', 'civi-framework'),
					'code' => '+228',
				),
				'tk' => array(
					'name' => esc_html__('Tokelau', 'civi-framework'),
					'code' => '+690',
				),
				'to' => array(
					'name' => esc_html__('Tonga', 'civi-framework'),
					'code' => '+676',
				),
				'tt' => array(
					'name' => esc_html__('Trinidad and Tobago', 'civi-framework'),
					'code' => '+1868',
				),
				'tn' => array(
					'name' => esc_html__('Tunisia', 'civi-framework'),
					'code' => '+216',
				),
				'tr' => array(
					'name' => esc_html__('Turkey', 'civi-framework'),
					'code' => '+90',
				),
				'tm' => array(
					'name' => esc_html__('Turkmenistan', 'civi-framework'),
					'code' => '+993',
				),
				'tc' => array(
					'name' => esc_html__('Turks and Caicos Islands', 'civi-framework'),
					'code' => '+1649',
				),
				'tv' => array(
					'name' => esc_html__('Tuvalu', 'civi-framework'),
					'code' => '+688',
				),
				'ug' => array(
					'name' => esc_html__('Uganda', 'civi-framework'),
					'code' => '+256',
				),
				'ua' => array(
					'name' => esc_html__('Ukraine', 'civi-framework'),
					'code' => '+380',
				),
				'ae' => array(
					'name' => esc_html__('United Arab Emirates', 'civi-framework'),
					'code' => '+971',
				),
				'gb' => array(
					'name' => esc_html__('United Kingdom', 'civi-framework'),
					'code' => '+44',
				),
				'us' => array(
					'name' => esc_html__('United States', 'civi-framework'),
					'code' => '+1',
				),
				'uy' => array(
					'name' => esc_html__('Uruguay', 'civi-framework'),
					'code' => '+598',
				),
				'uz' => array(
					'name' => esc_html__('Uzbekistan', 'civi-framework'),
					'code' => '+998',
				),
				'vu' => array(
					'name' => esc_html__('Vanuatu', 'civi-framework'),
					'code' => '+678',
				),
				'va' => array(
					'name' => esc_html__('Vatican City', 'civi-framework'),
					'code' => '+39',
				),
				've' => array(
					'name' => esc_html__('Venezuela', 'civi-framework'),
					'code' => '+58',
				),
				'vn' => array(
					'name' => esc_html__('Vietnam', 'civi-framework'),
					'code' => '+84',
				),
				'wf' => array(
					'name' => esc_html__('Wallis and Futuna', 'civi-framework'),
					'code' => '+681',
				),
				'eh' => array(
					'name' => esc_html__('Western Sahara', 'civi-framework'),
					'code' => '+212',
				),
				'ye' => array(
					'name' => esc_html__('Yemen', 'civi-framework'),
					'code' => '+967',
				),
				'zm' => array(
					'name' => esc_html__('Zambia', 'civi-framework'),
					'code' => '+260',
				),
				'zw' => array(
					'name' => esc_html__('Zimbabwe', 'civi-framework'),
					'code' => '+263',
				),
			)
		);
	}
}

/**
 * Get content option taxonomy
 */
if (!function_exists('civi_content_option_taxonomy')) {
	function civi_content_option_taxonomy($post_type, $postion = 'sidebar')
	{
		if ($post_type == 'jobs') {
			$list_state = civi_get_option_taxonomy('jobs-state');
			$list_city  = civi_get_option_taxonomy('jobs-location');
		} elseif ($post_type == 'company') {
			$list_state = civi_get_option_taxonomy('company-state');
			$list_city  = civi_get_option_taxonomy('company-location');
		} elseif ($post_type == 'candidate') {
			$list_state = civi_get_option_taxonomy('candidate_state');
			$list_city  = civi_get_option_taxonomy('candidate_locations');
		} elseif ($post_type == 'service') {
			$list_state = civi_get_option_taxonomy('service-state');
			$list_city  = civi_get_option_taxonomy('service-location');
		}

		$icon_city    = civi_get_option($post_type . '_search_fields_location');
		$icon_state   = civi_get_option($post_type . '_search_fields_state');
		$icon_country = civi_get_option($post_type . '_search_fields_country');

		if (civi_get_option('enable_option_country') === '1') { ?>
			<div class="form-group">
				<?php if ($postion == 'top') {
					echo $icon_country;
				} ?>
				<select class="civi-select-country civi-select2" data-post-type="<?php echo $post_type; ?>">
					<option value=""><?php esc_html_e('Select Countries', 'civi-framework'); ?></option>
					<?php civi_get_select_option_countries(); ?>
				</select>
			</div>
		<?php } ?>
		<?php if (civi_get_option('enable_option_state') === '1') { ?>
			<div class="form-group">
				<?php if ($postion == 'top') {
					echo $icon_state;
				} ?>
				<select class="civi-select-state civi-select2" data-post-type="<?php echo $post_type; ?>">
					<?php if (civi_get_option('enable_option_country') === '1') {
						echo '<option value="">' . esc_html__('Select States', 'civi-framework') . '</option>';
					} else {
						echo '<option value="">' . esc_html__('Select States', 'civi-framework') . '</option>';
						foreach ($list_state as $k => $v) {
							echo '<option value="' . $k . '">' . $v . '</option>';
						}
					} ?>
				</select>
			</div>
		<?php } ?>
		<div class="form-group">
			<?php if ($postion == 'top') {
				echo $icon_city;
			} ?>
			<select class="civi-select-city civi-select2">
				<?php if (civi_get_option('enable_option_state') === '1') {
					echo '<option value="">' . esc_html__('Select Cities', 'civi-framework') . '</option>';
				} else {
					echo '<option value="">' . esc_html__('Select Cities', 'civi-framework') . '</option>';
					foreach ($list_city as $k => $v) {
						echo '<option value="' . $k . '">' . $v . '</option>';
					}
				} ?>
			</select>
		</div>
<?php
	}
}

/**
 * Get option taxonomy
 */
if (!function_exists('civi_get_option_taxonomy')) {
	function civi_get_option_taxonomy($taxonomy)
	{
		$taxonomy_terms = get_categories(
			array(
				'taxonomy'   => $taxonomy,
				'orderby'    => 'name',
				'order'      => 'ASC',
				'hide_empty' => false,
				'parent'     => 0
			)
		);
		$keys           = $values = array();
		foreach ($taxonomy_terms as $terms) {
			$keys[]   = $terms->term_id;
			$values[] = $terms->name;
		}
		$list_location = array_combine($keys, $values);

		return $list_location;
	}
}

/**
 * Get select option countries
 */
if (!function_exists('civi_get_select_option_countries')) {
	function civi_get_select_option_countries()
	{
		$select_option_country = civi_get_option('select_option_country');
		$countries             = civi_get_countries();
		$keys                  = $values = array();
		if (!empty($select_option_country)) {
			foreach ($select_option_country as $key_country => $option_country) {
				if (array_key_exists($option_country, $countries)) {
					$keys[]   = $option_country;
					$values[] = $countries[$option_country];
				}
			}
			$list_country = array_combine($keys, $values);
		} else {
			$list_country = $countries;
		}

		foreach ($list_country as $k => $v) {
			echo '<option value="' . $k . '">' . $v . '</option>';
		}
	}
}
/**
 * Get countries
 */
if (!function_exists('civi_get_countries')) {
	function civi_get_countries()
	{
		$countries = apply_filters('civi_get_countries', array(
			'AF'  => esc_html__('Afghanistan', 'civi-framework'),
			'AX'  => esc_html__('Aland Islands', 'civi-framework'),
			'AL'  => esc_html__('Albania', 'civi-framework'),
			'DZ'  => esc_html__('Algeria', 'civi-framework'),
			'AS'  => esc_html__('American Samoa', 'civi-framework'),
			'AD'  => esc_html__('Andorra', 'civi-framework'),
			'AO'  => esc_html__('Angola', 'civi-framework'),
			'AI'  => esc_html__('Anguilla', 'civi-framework'),
			'AQ'  => esc_html__('Antarctica', 'civi-framework'),
			'AG'  => esc_html__('Antigua and Barbuda', 'civi-framework'),
			'AR'  => esc_html__('Argentina', 'civi-framework'),
			'AM'  => esc_html__('Armenia', 'civi-framework'),
			'AW'  => esc_html__('Aruba', 'civi-framework'),
			'AU'  => esc_html__('Australia', 'civi-framework'),
			'AT'  => esc_html__('Austria', 'civi-framework'),
			'AZ'  => esc_html__('Azerbaijan', 'civi-framework'),
			'BS'  => esc_html__('Bahamas the', 'civi-framework'),
			'BH'  => esc_html__('Bahrain', 'civi-framework'),
			'BD'  => esc_html__('Bangladesh', 'civi-framework'),
			'BB'  => esc_html__('Barbados', 'civi-framework'),
			'BY'  => esc_html__('Belarus', 'civi-framework'),
			'BE'  => esc_html__('Belgium', 'civi-framework'),
			'BZ'  => esc_html__('Belize', 'civi-framework'),
			'BJ'  => esc_html__('Benin', 'civi-framework'),
			'BM'  => esc_html__('Bermuda', 'civi-framework'),
			'BT'  => esc_html__('Bhutan', 'civi-framework'),
			'BO'  => esc_html__('Bolivia', 'civi-framework'),
			'BA'  => esc_html__('Bosnia and Herzegovina', 'civi-framework'),
			'BW'  => esc_html__('Botswana', 'civi-framework'),
			'BV'  => esc_html__('Bouvet Island (Bouvetoya)', 'civi-framework'),
			'BR'  => esc_html__('Brazil', 'civi-framework'),
			'IO'  => esc_html__('British Indian Ocean Territory (Chagos Archipelago)', 'civi-framework'),
			'VG'  => esc_html__('British Virgin Islands', 'civi-framework'),
			'BN'  => esc_html__('Brunei Darussalam', 'civi-framework'),
			'BG'  => esc_html__('Bulgaria', 'civi-framework'),
			'BF'  => esc_html__('Burkina Faso', 'civi-framework'),
			'BI'  => esc_html__('Burundi', 'civi-framework'),
			'KH'  => esc_html__('Cambodia', 'civi-framework'),
			'CM'  => esc_html__('Cameroon', 'civi-framework'),
			'CA'  => esc_html__('Canada', 'civi-framework'),
			'CV'  => esc_html__('Cape Verde', 'civi-framework'),
			'KY'  => esc_html__('Cayman Islands', 'civi-framework'),
			'CF'  => esc_html__('Central African Republic', 'civi-framework'),
			'TD'  => esc_html__('Chad', 'civi-framework'),
			'CL'  => esc_html__('Chile', 'civi-framework'),
			'CN'  => esc_html__('China', 'civi-framework'),
			'CX'  => esc_html__('Christmas Island', 'civi-framework'),
			'CC'  => esc_html__('Cocos (Keeling) Islands', 'civi-framework'),
			'CO'  => esc_html__('Colombia', 'civi-framework'),
			'KM'  => esc_html__('Comoros the', 'civi-framework'),
			'CD'  => esc_html__('Congo', 'civi-framework'),
			'CG'  => esc_html__('Congo the', 'civi-framework'),
			'CK'  => esc_html__('Cook Islands', 'civi-framework'),
			'CR'  => esc_html__('Costa Rica', 'civi-framework'),
			'CI'  => esc_html__("Cote d'Ivoire", 'civi-framework'),
			'HR'  => esc_html__('Croatia', 'civi-framework'),
			'CU'  => esc_html__('Cuba', 'civi-framework'),
			'CY'  => esc_html__('Cyprus', 'civi-framework'),
			'CZ'  => esc_html__('Czech Republic', 'civi-framework'),
			'DK'  => esc_html__('Denmark', 'civi-framework'),
			'DJ'  => esc_html__('Djibouti', 'civi-framework'),
			'DM'  => esc_html__('Dominica', 'civi-framework'),
			'DO'  => esc_html__('Dominican Republic', 'civi-framework'),
			'EC'  => esc_html__('Ecuador', 'civi-framework'),
			'EG'  => esc_html__('Egypt', 'civi-framework'),
			'SV'  => esc_html__('El Salvador', 'civi-framework'),
			'GQ'  => esc_html__('Equatorial Guinea', 'civi-framework'),
			'ER'  => esc_html__('Eritrea', 'civi-framework'),
			'EE'  => esc_html__('Estonia', 'civi-framework'),
			'ET'  => esc_html__('Ethiopia', 'civi-framework'),
			'FO'  => esc_html__('Faroe Islands', 'civi-framework'),
			'FK'  => esc_html__('Falkland Islands (Malvinas)', 'civi-framework'),
			'FJ'  => esc_html__('Fiji the Fiji Islands', 'civi-framework'),
			'FI'  => esc_html__('Finland', 'civi-framework'),
			'FR'  => esc_html__('France', 'civi-framework'),
			'GF'  => esc_html__('French Guiana', 'civi-framework'),
			'PF'  => esc_html__('French Polynesia', 'civi-framework'),
			'TF'  => esc_html__('French Southern Territories', 'civi-framework'),
			'GA'  => esc_html__('Gabon', 'civi-framework'),
			'GM'  => esc_html__('Gambia the', 'civi-framework'),
			'GE'  => esc_html__('Georgia', 'civi-framework'),
			'DE'  => esc_html__('Germany', 'civi-framework'),
			'GH'  => esc_html__('Ghana', 'civi-framework'),
			'GI'  => esc_html__('Gibraltar', 'civi-framework'),
			'GR'  => esc_html__('Greece', 'civi-framework'),
			'GL'  => esc_html__('Greenland', 'civi-framework'),
			'GD'  => esc_html__('Grenada', 'civi-framework'),
			'GP'  => esc_html__('Guadeloupe', 'civi-framework'),
			'GU'  => esc_html__('Guam', 'civi-framework'),
			'GT'  => esc_html__('Guatemala', 'civi-framework'),
			'GG'  => esc_html__('Guernsey', 'civi-framework'),
			'GN'  => esc_html__('Guinea', 'civi-framework'),
			'GW'  => esc_html__('Guinea-Bissau', 'civi-framework'),
			'GY'  => esc_html__('Guyana', 'civi-framework'),
			'HT'  => esc_html__('Haiti', 'civi-framework'),
			'HM'  => esc_html__('Heard Island and McDonald Islands', 'civi-framework'),
			'VA'  => esc_html__('Holy See (Vatican City State)', 'civi-framework'),
			'HN'  => esc_html__('Honduras', 'civi-framework'),
			'HK'  => esc_html__('Hong Kong', 'civi-framework'),
			'HU'  => esc_html__('Hungary', 'civi-framework'),
			'IS'  => esc_html__('Iceland', 'civi-framework'),
			'IN'  => esc_html__('India', 'civi-framework'),
			'ID'  => esc_html__('Indonesia', 'civi-framework'),
			'IR'  => esc_html__('Iran', 'civi-framework'),
			'IQ'  => esc_html__('Iraq', 'civi-framework'),
			'IE'  => esc_html__('Ireland', 'civi-framework'),
			'IM'  => esc_html__('Isle of Man', 'civi-framework'),
			'IL'  => esc_html__('Israel', 'civi-framework'),
			'IT'  => esc_html__('Italy', 'civi-framework'),
			'JM'  => esc_html__('Jamaica', 'civi-framework'),
			'JP'  => esc_html__('Japan', 'civi-framework'),
			'JE'  => esc_html__('Jersey', 'civi-framework'),
			'JO'  => esc_html__('Jordan', 'civi-framework'),
			'KZ'  => esc_html__('Kazakhstan', 'civi-framework'),
			'KE'  => esc_html__('Kenya', 'civi-framework'),
			'KI'  => esc_html__('Kiribati', 'civi-framework'),
			'KP'  => esc_html__('Korea', 'civi-framework'),
			'KR'  => esc_html__('Korea', 'civi-framework'),
			'KW'  => esc_html__('Kuwait', 'civi-framework'),
			'KG'  => esc_html__('Kyrgyz Republic', 'civi-framework'),
			'LA'  => esc_html__('Lao', 'civi-framework'),
			'LV'  => esc_html__('Latvia', 'civi-framework'),
			'LB'  => esc_html__('Lebanon', 'civi-framework'),
			'LS'  => esc_html__('Lesotho', 'civi-framework'),
			'LR'  => esc_html__('Liberia', 'civi-framework'),
			'LY'  => esc_html__('Libyan Arab Jamahiriya', 'civi-framework'),
			'LI'  => esc_html__('Liechtenstein', 'civi-framework'),
			'LT'  => esc_html__('Lithuania', 'civi-framework'),
			'LU'  => esc_html__('Luxembourg', 'civi-framework'),
			'MO'  => esc_html__('Macao', 'civi-framework'),
			'MK'  => esc_html__('Macedonia', 'civi-framework'),
			'MG'  => esc_html__('Madagascar', 'civi-framework'),
			'MW'  => esc_html__('Malawi', 'civi-framework'),
			'MY'  => esc_html__('Malaysia', 'civi-framework'),
			'MV'  => esc_html__('Maldives', 'civi-framework'),
			'ML'  => esc_html__('Mali', 'civi-framework'),
			'MT'  => esc_html__('Malta', 'civi-framework'),
			'MH'  => esc_html__('Marshall Islands', 'civi-framework'),
			'MQ'  => esc_html__('Martinique', 'civi-framework'),
			'MR'  => esc_html__('Mauritania', 'civi-framework'),
			'MU'  => esc_html__('Mauritius', 'civi-framework'),
			'YT'  => esc_html__('Mayotte', 'civi-framework'),
			'MX'  => esc_html__('Mexico', 'civi-framework'),
			'FM'  => esc_html__('Micronesia', 'civi-framework'),
			'MD'  => esc_html__('Moldova', 'civi-framework'),
			'MC'  => esc_html__('Monaco', 'civi-framework'),
			'MN'  => esc_html__('Mongolia', 'civi-framework'),
			'ME'  => esc_html__('Montenegro', 'civi-framework'),
			'MS'  => esc_html__('Montserrat', 'civi-framework'),
			'MA'  => esc_html__('Morocco', 'civi-framework'),
			'MZ'  => esc_html__('Mozambique', 'civi-framework'),
			'MM'  => esc_html__('Myanmar', 'civi-framework'),
			'NA'  => esc_html__('Namibia', 'civi-framework'),
			'NR'  => esc_html__('Nauru', 'civi-framework'),
			'NP'  => esc_html__('Nepal', 'civi-framework'),
			'AN'  => esc_html__('Netherlands Antilles', 'civi-framework'),
			'NL'  => esc_html__('Netherlands the', 'civi-framework'),
			'NC'  => esc_html__('New Caledonia', 'civi-framework'),
			'NZ'  => esc_html__('New Zealand', 'civi-framework'),
			'NI'  => esc_html__('Nicaragua', 'civi-framework'),
			'NE'  => esc_html__('Niger', 'civi-framework'),
			'NG'  => esc_html__('Nigeria', 'civi-framework'),
			'NU'  => esc_html__('Niue', 'civi-framework'),
			'NF'  => esc_html__('Norfolk Island', 'civi-framework'),
			'MP'  => esc_html__('Northern Mariana Islands', 'civi-framework'),
			'NO'  => esc_html__('Norway', 'civi-framework'),
			'OM'  => esc_html__('Oman', 'civi-framework'),
			'PK'  => esc_html__('Pakistan', 'civi-framework'),
			'PW'  => esc_html__('Palau', 'civi-framework'),
			'PS'  => esc_html__('Palestinian Territory', 'civi-framework'),
			'PA'  => esc_html__('Panama', 'civi-framework'),
			'PG'  => esc_html__('Papua New Guinea', 'civi-framework'),
			'PY'  => esc_html__('Paraguay', 'civi-framework'),
			'PE'  => esc_html__('Peru', 'civi-framework'),
			'PH'  => esc_html__('Philippines', 'civi-framework'),
			'PN'  => esc_html__('Pitcairn Islands', 'civi-framework'),
			'PL'  => esc_html__('Poland', 'civi-framework'),
			'PT'  => esc_html__('Portugal, Portuguese Republic', 'civi-framework'),
			'PR'  => esc_html__('Puerto Rico', 'civi-framework'),
			'QA'  => esc_html__('Qatar', 'civi-framework'),
			'RE'  => esc_html__('Reunion', 'civi-framework'),
			'RO'  => esc_html__('Romania', 'civi-framework'),
			'RU'  => esc_html__('Russian Federation', 'civi-framework'),
			'RW'  => esc_html__('Rwanda', 'civi-framework'),
			'BL'  => esc_html__('Saint Barthelemy', 'civi-framework'),
			'SH'  => esc_html__('Saint Helena', 'civi-framework'),
			'KN'  => esc_html__('Saint Kitts and Nevis', 'civi-framework'),
			'LC'  => esc_html__('Saint Lucia', 'civi-framework'),
			'MF'  => esc_html__('Saint Martin', 'civi-framework'),
			'PM'  => esc_html__('Saint Pierre and Miquelon', 'civi-framework'),
			'VC'  => esc_html__('Saint Vincent and the Grenadines', 'civi-framework'),
			'WS'  => esc_html__('Samoa', 'civi-framework'),
			'SM'  => esc_html__('San Marino', 'civi-framework'),
			'ST'  => esc_html__('Sao Tome and Principe', 'civi-framework'),
			'SA'  => esc_html__('Saudi Arabia', 'civi-framework'),
			'SN'  => esc_html__('Senegal', 'civi-framework'),
			'RS'  => esc_html__('Serbia', 'civi-framework'),
			'SC'  => esc_html__('Seychelles', 'civi-framework'),
			'SL'  => esc_html__('Sierra Leone', 'civi-framework'),
			'SG'  => esc_html__('Singapore', 'civi-framework'),
			'SK'  => esc_html__('Slovakia (Slovak Republic)', 'civi-framework'),
			'SI'  => esc_html__('Slovenia', 'civi-framework'),
			'SB'  => esc_html__('Solomon Islands', 'civi-framework'),
			'SO'  => esc_html__('Somalia, Somali Republic', 'civi-framework'),
			'ZA'  => esc_html__('South Africa', 'civi-framework'),
			'GS'  => esc_html__('South Georgia and the South Sandwich Islands', 'civi-framework'),
			'ES'  => esc_html__('Spain', 'civi-framework'),
			'LK'  => esc_html__('Sri Lanka', 'civi-framework'),
			'SD'  => esc_html__('Sudan', 'civi-framework'),
			'SR'  => esc_html__('Suriname', 'civi-framework'),
			'SJ'  => esc_html__('Svalbard & Jan Mayen Islands', 'civi-framework'),
			'SZ'  => esc_html__('Swaziland', 'civi-framework'),
			'SE'  => esc_html__('Sweden', 'civi-framework'),
			'CH'  => esc_html__('Switzerland, Swiss Confederation', 'civi-framework'),
			'SY'  => esc_html__('Syrian Arab Republic', 'civi-framework'),
			'TW'  => esc_html__('Taiwan', 'civi-framework'),
			'TJ'  => esc_html__('Tajikistan', 'civi-framework'),
			'TZ'  => esc_html__('Tanzania', 'civi-framework'),
			'TH'  => esc_html__('Thailand', 'civi-framework'),
			'TL'  => esc_html__('Timor-Leste', 'civi-framework'),
			'TG'  => esc_html__('Togo', 'civi-framework'),
			'TK'  => esc_html__('Tokelau', 'civi-framework'),
			'TO'  => esc_html__('Tonga', 'civi-framework'),
			'TT'  => esc_html__('Trinidad and Tobago', 'civi-framework'),
			'TN'  => esc_html__('Tunisia', 'civi-framework'),
			'TR'  => esc_html__('Turkey', 'civi-framework'),
			'TM'  => esc_html__('Turkmenistan', 'civi-framework'),
			'TC'  => esc_html__('Turks and Caicos Islands', 'civi-framework'),
			'TV'  => esc_html__('Tuvalu', 'civi-framework'),
			'UG'  => esc_html__('Uganda', 'civi-framework'),
			'UA'  => esc_html__('Ukraine', 'civi-framework'),
			'AE'  => esc_html__('United Arab Emirates', 'civi-framework'),
			'GB'  => esc_html__('United Kingdom', 'civi-framework'),
			'SCL' => esc_html__('Scotland', 'civi-framework'),
			'WL'  => esc_html__('Wales', 'civi-framework'),
			'NIR' => esc_html__('Northern Ireland', 'civi-framework'),
			'US'  => esc_html__('United States', 'civi-framework'),
			'UM'  => esc_html__('United States Minor Outlying Islands', 'civi-framework'),
			'VI'  => esc_html__('United States Virgin Islands', 'civi-framework'),
			'UY'  => esc_html__('Uruguay, Eastern Republic of', 'civi-framework'),
			'UZ'  => esc_html__('Uzbekistan', 'civi-framework'),
			'VU'  => esc_html__('Vanuatu', 'civi-framework'),
			'VE'  => esc_html__('Venezuela', 'civi-framework'),
			'VN'  => esc_html__('Vietnam', 'civi-framework'),
			'WF'  => esc_html__('Wallis and Futuna', 'civi-framework'),
			'EH'  => esc_html__('Western Sahara', 'civi-framework'),
			'YE'  => esc_html__('Yemen', 'civi-framework'),
			'ZM'  => esc_html__('Zambia', 'civi-framework'),
			'ZW'  => esc_html__('Zimbabwe', 'civi-framework'),
			'SVG' => esc_html__('Saint Vincent', 'civi-framework'),
		));

		return $countries;
	}
}

/**
 * Get page by title
 */
if (!function_exists('civi_get_page_by_title')) {
	function civi_get_page_by_title($title, $post_type)
	{
		$query = new WP_Query(
			array(
				'post_type'              => $post_type,
				'title'                  => $title,
				'posts_per_page'         => 1,
				'no_found_rows'          => true,
				'ignore_sticky_posts'    => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
			)
		);
		if (!empty($query->post)) {
			$fetched_page = $query->post;
			return $fetched_page;
		} else {
			return false;
		}
	}
}

class PDF extends FPDF
{
	// Convert UTF-8 to ISO-8859-1
	function convertToISO($string)
	{
		return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $string);
	}
}

function generate_pdf_with_fpdf($invoice_id)
{
	$logo_dark = Civi_Helper::get_setting("logo_dark");
	$civi_invoice = new Civi_Invoice();
	$civi_meta = $civi_invoice->get_invoice_meta($invoice_id);
	$user_info = get_userdata($civi_meta['invoice_user_id']);

	// Create a new PDF document
	$pdf = new PDF();
	$pdf->AddPage();
	$pdf->SetFont('Arial', 'B', 16);

	// Get the width of the page
	$pageWidth = $pdf->GetPageWidth();
	// Set the width of the image
	$imageWidth = 30; // Adjust this value as needed

	// Calculate the x position for the image to align right
	$xPosition = $pageWidth - $imageWidth - 15;
	// Set some content to print
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell(15, 7, $pdf->convertToISO(esc_html__('From:', 'civi-framework')));
	$pdf->Cell(0, 7, $pdf->convertToISO(esc_html__('Uxper Company', 'civi-framework')), 0, 1);
	$pdf->Cell(25, 7, $pdf->convertToISO(esc_html__('Receipient:', 'civi-framework')));
	$pdf->Cell(0, 7, $pdf->convertToISO($user_info->display_name) . '(' . $pdf->convertToISO($user_info->user_email) . ')', 0, 1);
	$pdf->Line(11, 30, 200, 30);
	$pdf->Ln(15);

	$pdf->Cell(50, 10, $pdf->convertToISO(esc_html__('Invoice ID:', 'civi-framework')));
	$pdf->Cell(0, 10, '11245', 0, 1);
	$pdf->Cell(50, 10, $pdf->convertToISO(esc_html__('Payment Method:', 'civi-framework')));
	$pdf->Cell(0, 10, $pdf->convertToISO($civi_meta['invoice_payment_method']), 0, 1);
	$pdf->Cell(50, 10, $pdf->convertToISO(esc_html__('Payment Type:', 'civi-framework')));
	$pdf->Cell(0, 10, $pdf->convertToISO($civi_meta['invoice_payment_type']), 0, 1);
	$pdf->Cell(50, 10, $pdf->convertToISO(esc_html__('Package ID:', 'civi-framework')));
	$pdf->Cell(0, 10, $pdf->convertToISO($civi_meta['invoice_item_id']), 0, 1);
	$pdf->Cell(50, 10, $pdf->convertToISO(esc_html__('Package Name:', 'civi-framework')));
	$pdf->Cell(0, 10, $pdf->convertToISO(get_the_title($civi_meta['invoice_item_id'])), 0, 1);
	$pdf->Cell(50, 10, $pdf->convertToISO(esc_html__('Package Price:', 'civi-framework')));
	$pdf->Cell(0, 10, $pdf->convertToISO($civi_meta['invoice_item_price']), 0, 1);
	$pdf->Cell(50, 10, $pdf->convertToISO(esc_html__('Purchase Date:', 'civi-framework')));
	$pdf->Cell(0, 10, $pdf->convertToISO($civi_meta['invoice_purchase_date']), 0, 1);
	$pdf->Cell(50, 10, $pdf->convertToISO(esc_html__('Buyer Name:', 'civi-framework')));
	$pdf->Cell(0, 10, $pdf->convertToISO($user_info->display_name), 0, 1);
	$pdf->Cell(50, 10, $pdf->convertToISO(esc_html__('Buyer Email:', 'civi-framework')));
	$pdf->Cell(0, 10, $pdf->convertToISO($user_info->user_email), 0, 1);

	// Output a PDF file directly to the browser
	// Save the PDF to a temporary location
	$upload_dir = wp_upload_dir();
	$pdf_path = $upload_dir['path'] . '/invoice_' . $invoice_id . '.pdf';
	$pdf->Output($pdf_path, 'F');

	// Check for errors
	if (!file_exists($pdf_path)) {
		return new WP_Error('pdf_generation_failed', 'PDF generation failed.');
	}

	// Prepare an array of file data suitable for WordPress media library
	$filetype = wp_check_filetype(basename($pdf_path), null);
	$attachment = array(
		'guid' => $upload_dir['url'] . '/' . basename($pdf_path),
		'post_mime_type' => $filetype['type'],
		'post_title' => sanitize_file_name(basename($pdf_path)),
		'post_content' => '',
		'post_status' => 'inherit'
	);

	// Insert the attachment to the WordPress media library
	$attach_id = wp_insert_attachment($attachment, $pdf_path);

	// Include the function to generate attachment metadata
	require_once(ABSPATH . 'wp-admin/includes/image.php');

	// Generate and save attachment metadata
	$attach_data = wp_generate_attachment_metadata($attach_id, $pdf_path);
	wp_update_attachment_metadata($attach_id, $attach_data);

	return $attach_id;
}

/**
 * Get maximum job salary from all possible meta fields.
 *
 * @return int
 */
if (!function_exists('civi_get_maximum_job_salary_optimized')) {
	function civi_get_maximum_job_salary_optimized()
	{
		global $wpdb;

		$meta_keys = array(
			CIVI_METABOX_PREFIX . 'jobs_salary_maximum',
			CIVI_METABOX_PREFIX . 'jobs_maximum_price',
			CIVI_METABOX_PREFIX . 'jobs_salary_minimum',
			CIVI_METABOX_PREFIX . 'jobs_minimum_price',
		);

		$meta_key_in = implode("','", array_map('esc_sql', $meta_keys));

		// Check if expired jobs should be included
		$enable_jobs_show_expires = civi_get_option('enable_jobs_show_expires');
		$post_status_condition = ($enable_jobs_show_expires == 1)
			? "AND p.post_status IN ('publish', 'expired')"
			: "AND p.post_status = 'publish'";

		$sql = "
            SELECT MAX(CAST(pm.meta_value AS UNSIGNED)) AS max_salary
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE pm.meta_key IN ('$meta_key_in')
            AND p.post_type = 'jobs'
            $post_status_condition
            AND pm.meta_value != ''
        ";

		$max_salary = $wpdb->get_var($sql);
		$final_result = intval($max_salary);

		if (empty($final_result) || $final_result == 0) {
			$final_result = 500;
		}

		return $final_result;
	}
}

/**
 * Get minimum job salary - simplified to always return 0.
 *
 * @return int
 */
if (!function_exists('civi_get_minimum_job_salary_optimized')) {
	function civi_get_minimum_job_salary_optimized()
	{
		return 0;
	}
}

/**
 * Rounding salary to the nearest maximum value.
 *
 * @param int $number
 * @return int
 */

if (!function_exists('civi_round_salary_max')) {
	function civi_round_salary_max($number)
	{
		if ($number < 100) {
			return ceil($number / 10) * 10;
		}
		if ($number < 200) {
			return ceil($number / 50) * 50;
		}
		if ($number < 1000) {
			return ceil($number / 100) * 100;
		}

		$exponent = floor(log10($number));
		$base = pow(10, $exponent - 1);

		$raw = $number / $base;
		if ($raw <= 10) {
			$step = 1 * $base;
		} elseif ($raw <= 20) {
			$step = 2 * $base;
		} elseif ($raw <= 50) {
			$step = 5 * $base;
		} else {
			$step = 10 * $base;
		}

		return ceil($number / $step) * $step;
	}
}
/**
 * Get accurate term post count including expired posts if enabled
 *
 * @param int $term_id Term ID
 * @param string $taxonomy Taxonomy name
 * @param bool $include_expired Whether to include expired posts
 * @return int Post count
 */
if (!function_exists('civi_get_term_post_count')) {
	function civi_get_term_post_count($term_id, $taxonomy, $include_expired = false)
	{
		global $wpdb;

		$original_include_expired = $include_expired;
		if (is_bool($include_expired)) {
			$include_expired = $include_expired;
		} else {
			$include_expired = !empty($include_expired) && ($include_expired == 1 || $include_expired === '1' || $include_expired === true);
		}
		$cache_key = "civi_term_count_{$term_id}_{$taxonomy}_" . ($include_expired ? 'expired' : 'publish');

		static $count_cache = array();
		if (isset($count_cache[$cache_key])) {
			return $count_cache[$cache_key];
		}

		$post_type = civi_get_post_type_from_taxonomy($taxonomy);

		if ($include_expired) {
			$post_status_condition = "AND p.post_status IN ('publish', 'expired')";
			$exclude_status_condition = "";
		} else {
			$post_status_condition = "AND p.post_status = 'publish'";
			$exclude_status_condition = "AND p.post_status NOT IN ('trash', 'auto-draft', 'inherit')";
		}


		if ($post_type !== 'jobs' && $post_type !== 'candidate') {
			$term = get_term($term_id, $taxonomy);
			return $term && !is_wp_error($term) ? intval($term->count) : 0;
		}

		$package_expires_condition = '';
		if ($post_type === 'jobs') {
			$package_expires_condition = "AND (
				NOT EXISTS (
					SELECT 1 FROM {$wpdb->postmeta} pm1
					WHERE pm1.post_id = p.ID
					AND pm1.meta_key = %s
				)
				OR EXISTS (
					SELECT 1 FROM {$wpdb->postmeta} pm2
					WHERE pm2.post_id = p.ID
					AND pm2.meta_key = %s
					AND pm2.meta_value = '0'
				)
			)";
		}

		if (!empty($package_expires_condition)) {
			$meta_key = CIVI_METABOX_PREFIX . 'enable_jobs_package_expires';
			$sql = $wpdb->prepare("
				SELECT COUNT(DISTINCT p.ID)
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
				INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				WHERE tt.term_id = %d
				AND tt.taxonomy = %s
				AND p.post_type = %s
				{$post_status_condition}
				{$exclude_status_condition}
				AND (
					NOT EXISTS (
						SELECT 1 FROM {$wpdb->postmeta} pm1
						WHERE pm1.post_id = p.ID
						AND pm1.meta_key = %s
					)
					OR EXISTS (
						SELECT 1 FROM {$wpdb->postmeta} pm2
						WHERE pm2.post_id = p.ID
						AND pm2.meta_key = %s
						AND pm2.meta_value = '0'
					)
				)
			", $term_id, $taxonomy, $post_type, $meta_key, $meta_key);
		} else {
			$sql = $wpdb->prepare("
				SELECT COUNT(DISTINCT p.ID)
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
				INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				WHERE tt.term_id = %d
				AND tt.taxonomy = %s
				AND p.post_type = %s
				{$post_status_condition}
				{$exclude_status_condition}
			", $term_id, $taxonomy, $post_type);
		}

		$count = $wpdb->get_var($sql);
		$count = intval($count);


		$count_cache[$cache_key] = $count;

		return $count;
	}
}

if (!function_exists('civi_short_number')) {
	function civi_short_number($number, $decimal = 1)
	{
		$abs = abs($number);
		if ($abs >= 1000000000) {
			return round($number / 1000000000, $decimal) . __('B', 'civi-framework');
		} elseif ($abs >= 1000000) {
			return round($number / 1000000, $decimal) . __('M', 'civi-framework');
		} elseif ($abs >= 1000) {
			return round($number / 1000, $decimal) . __('K', 'civi-framework');
		}
		return number_format($number, 0, ',', '.');
	}
}

// Function to format salary
if (!function_exists('civi_format_salary')) {
	function civi_format_salary($amount, $currency_sign_default, $currency_position, $thousand_separator, $decimal_separator)
	{
		// Handle empty separators
		if (empty($decimal_separator)) {
			$decimal_separator = '.';
		}
		if (empty($thousand_separator)) {
			$thousand_separator = ' ';
		}

		// Prevent same separators
		if ($thousand_separator === $decimal_separator) {
			if ($decimal_separator === ',') {
				$thousand_separator = ' ';
			} else if ($decimal_separator === '.') {
				$thousand_separator = ',';
			} else {
				$thousand_separator = ',';
			}
		}

		$formatted = number_format($amount, 0, $decimal_separator, $thousand_separator);
		if ($currency_position === 'before') {
			return $currency_sign_default . $formatted;
		} else {
			return $formatted . $currency_sign_default;
		}
	}
}

/**
 * Helper function to handle singular/plural forms with translation support
 */
if (!function_exists('civi_get_singular_plural_text')) {
	/**
	 * @deprecated 1.0.0 Use sprintf(_n()) instead for proper translation extraction.
	 */
	function civi_get_singular_plural_text($singular, $plural, $count)
	{
		return sprintf(_n($singular, $plural, $count, 'civi-framework'), $count);
	}
}

/**
 * Helper function to display count with proper pluralization for category/location widgets
 * Supports languages with multiple plural forms (e.g., Slovakia)
 * Always displays count, including when count = 0
 *
 * @param string $singular Singular form (e.g., '%s job')
 * @param string $plural Plural form (e.g., '%s jobs')
 * @param int $count The count number
 * @return string Formatted count text
 */
if (!function_exists('civi_format_count_text')) {
	/**
	 * @deprecated 1.0.0 Use sprintf(_n()) instead for proper translation extraction.
	 */
	function civi_format_count_text($singular, $plural, $count)
	{
		// Ensure count is an integer
		$count = intval($count);
		// Use _n() for proper pluralization (supports languages with multiple plural forms)
		// Use number_format_i18n() for locale-specific number formatting
		return sprintf(_n($singular, $plural, $count, 'civi-framework'), number_format_i18n($count));
	}
}



/**
 * Helper function to check if taxonomy has terms
 *
 * @param string $taxonomy The taxonomy to check
 * @return bool True if has terms, False if not
 */
if (!function_exists('civi_taxonomy_has_terms')) {
	function civi_taxonomy_has_terms($taxonomy)
	{
		$terms = get_terms(array(
			'taxonomy' => $taxonomy,
			'hide_empty' => false,
			'number' => 1
		));
		return !empty($terms) && !is_wp_error($terms);
	}
}

/**
 * Helper function to render filter with taxonomy check
 *
 * @param string $taxonomy The taxonomy to check
 * @param string $title The title to display
 * @param callable $render_callback Function to render filter content
 * @param mixed ...$args Additional arguments for the callback
 */
/**
 * Get post type from taxonomy name
 *
 * @param string $taxonomy Taxonomy name
 * @return string Post type
 */
if (!function_exists('civi_get_post_type_from_taxonomy')) {
	function civi_get_post_type_from_taxonomy($taxonomy)
	{
		if (strpos($taxonomy, 'company-') === 0) {
			return 'company';
		} elseif (strpos($taxonomy, 'candidate_') === 0) {
			return 'candidate';
		} elseif (strpos($taxonomy, 'service-') === 0) {
			return 'service';
		}
		return 'jobs'; // default
	}
}

/**
 * Get term post count for sidebar filters (respects enable_jobs_show_expires setting)
 *
 * @param int $term_id Term ID
 * @param string $taxonomy Taxonomy name
 * @return int Post count
 */
if (!function_exists('civi_get_term_post_count_for_sidebar')) {
	function civi_get_term_post_count_for_sidebar($term_id, $taxonomy)
	{
		global $wpdb;

		// Static cache to avoid duplicate queries within the same request
		static $cache = array();
		$cache_key = $term_id . '_' . $taxonomy;

		if (isset($cache[$cache_key])) {
			return $cache[$cache_key];
		}

		// Try to get from object cache first (persistent cache)
		$transient_key = 'civi_term_count_' . $term_id . '_' . md5($taxonomy);
		$cached_count = get_transient($transient_key);

		if ($cached_count !== false) {
			$cache[$cache_key] = intval($cached_count);
			return $cache[$cache_key];
		}

		$post_type = civi_get_post_type_from_taxonomy($taxonomy);

		// Check enable_jobs_show_expires setting to determine which statuses to count
		$enable_jobs_show_expires = civi_get_option('enable_jobs_show_expires');
		if ($enable_jobs_show_expires === true || $enable_jobs_show_expires === 1 || $enable_jobs_show_expires === '1') {
			// Include expired jobs
			$statuses = array('publish', 'expired');
		} else {
			// Only published jobs
			$statuses = array('publish');
		}

		$statuses_list = implode("','", array_map('esc_sql', $statuses));
		$post_status_condition = "AND p.post_status IN ('{$statuses_list}')";
		$params = array($term_id, $taxonomy, $post_type);

		$sql = "
			SELECT COUNT(DISTINCT p.ID)
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
			INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
			WHERE tt.term_id = %d
			AND tt.taxonomy = %s
			AND p.post_type = %s
			{$post_status_condition}
		";

		$count = intval($wpdb->get_var($wpdb->prepare($sql, $params)));

		// Cache the result for 1 hour
		set_transient($transient_key, $count, HOUR_IN_SECONDS);
		$cache[$cache_key] = $count;

		return $count;
	}
}

/**
 * Clear term post count cache when a post is saved/updated/deleted
 */
if (!function_exists('civi_clear_term_count_cache')) {
	function civi_clear_term_count_cache($post_id)
	{
		$post = get_post($post_id);
		if (!$post || !in_array($post->post_type, array('jobs', 'service', 'company', 'candidate'))) {
			return;
		}

		// Get all taxonomies for this post type
		$taxonomies = get_object_taxonomies($post->post_type);
		foreach ($taxonomies as $taxonomy) {
			$terms = wp_get_post_terms($post_id, $taxonomy, array('fields' => 'ids'));
			if (!is_wp_error($terms)) {
				foreach ($terms as $term_id) {
					$transient_key = 'civi_term_count_' . $term_id . '_' . md5($taxonomy);
					delete_transient($transient_key);
				}
			}
		}
	}
	add_action('save_post', 'civi_clear_term_count_cache', 10, 1);
	add_action('delete_post', 'civi_clear_term_count_cache', 10, 1);
	add_action('wp_trash_post', 'civi_clear_term_count_cache', 10, 1);
	add_action('transition_post_status', function ($new_status, $old_status, $post) {
		civi_clear_term_count_cache($post->ID);
	}, 10, 3);
}

/**
 * Check if a company section should be visible based on hide settings
 *
 * @param string $section_name Section name (general, media, social, location, gallery, video, additional)
 * @param array $hide_company_fields Array of hidden field names
 * @param array $hide_company_group_fields Array of hidden group field names
 * @return bool True if section should be visible, false otherwise
 */
if (!function_exists('civi_is_company_section_visible')) {
	function civi_is_company_section_visible($section_name, $hide_company_fields, $hide_company_group_fields)
	{
		// Check if section is in group hide list
		if (is_array($hide_company_group_fields) && in_array($section_name, $hide_company_group_fields, true)) {
			return false;
		}

		// Check section-specific visibility logic
		if ($section_name === 'general') {
			$general_fields = array(
				'fields_company_name',
				'fields_company_category',
				'fields_company_url',
				'fields_company_about',
				'fields_company_website',
				'fields_company_phone',
				'fields_company_email',
				'fields_company_founded',
				'fields_company_size'
			);
			if (is_array($hide_company_fields) && !empty($general_fields)) {
				foreach ($general_fields as $field) {
					if (!in_array($field, $hide_company_fields, true)) {
						return true; // At least one field is visible
					}
				}
				return false; // All fields are hidden
			}
			return true;
		} elseif ($section_name === 'media') {
			if (is_array($hide_company_fields)) {
				$logo_hidden = in_array('fields_closing_logo', $hide_company_fields, true);
				$thumbnail_hidden = in_array('fields_company_thumbnail', $hide_company_fields, true);
				return !($logo_hidden && $thumbnail_hidden);
			}
			return true;
		} elseif ($section_name === 'social') {
			$enable_social_twitter = civi_get_option('enable_social_twitter', '1');
			$enable_social_linkedin = civi_get_option('enable_social_linkedin', '1');
			$enable_social_facebook = civi_get_option('enable_social_facebook', '1');
			$enable_social_instagram = civi_get_option('enable_social_instagram', '1');
			$has_enabled = ($enable_social_twitter === '1' || $enable_social_twitter === 1 || $enable_social_twitter === true) ||
				($enable_social_linkedin === '1' || $enable_social_linkedin === 1 || $enable_social_linkedin === true) ||
				($enable_social_facebook === '1' || $enable_social_facebook === 1 || $enable_social_facebook === true) ||
				($enable_social_instagram === '1' || $enable_social_instagram === 1 || $enable_social_instagram === true);
			return $has_enabled;
		} elseif ($section_name === 'location') {
			if (is_array($hide_company_fields)) {
				$location_hidden = in_array('fields_company_location', $hide_company_fields, true);
				$map_hidden = in_array('fields_company_map', $hide_company_fields, true);
				return !($location_hidden && $map_hidden);
			}
			return true;
		}
		// Gallery, Video, and Additional sections don't have hideable fields
		return true;
	}
}

/**
 * Check if a candidate section should be visible based on hide settings
 *
 * @param string $section_name Section name (info, education, experience, skills, projects, awards)
 * @param array $hide_candidate_fields Array of hidden field names
 * @param array $hide_candidate_group_fields Array of hidden group field names
 * @return bool True if section should be visible, false otherwise
 */
if (!function_exists('civi_is_candidate_section_visible')) {
	function civi_is_candidate_section_visible($section_name, $hide_candidate_fields, $hide_candidate_group_fields)
	{
		// Check if section is in group hide list
		if (is_array($hide_candidate_group_fields) && in_array($section_name, $hide_candidate_group_fields, true)) {
			return false;
		}

		// Check section-specific visibility logic
		if ($section_name === 'info') {
			// Info section has multiple sub-sections: basic, location, resume, social, gallery, video
			// Check basic fields
			$basic_fields = array(
				'fields_candidate_avatar',
				'fields_candidate_thumbnail',
				'fields_candidate_first_name',
				'fields_candidate_last_name',
				'fields_candidate_email_address',
				'fields_candidate_phone_number',
				'fields_candidate_current_position',
				'fields_candidate_categories',
				'fields_candidate_description',
				'fields_candidate_date_of_birth',
				'fields_candidate_age',
				'fields_candidate_gender',
				'fields_closing_languages',
				'fields_candidate_qualification',
				'fields_candidate_experience',
				'fields_candidate_salary'
			);

			// Check location fields
			$location_hidden = is_array($hide_candidate_fields) && in_array('fields_candidate_location', $hide_candidate_fields, true);
			$map_hidden = is_array($hide_candidate_fields) && in_array('fields_candidate_map', $hide_candidate_fields, true);
			$location_visible = !($location_hidden && $map_hidden);

			// Check other sub-sections
			$resume_visible = !(is_array($hide_candidate_fields) && in_array('fields_candidate_resume', $hide_candidate_fields, true));
			$social_visible = !(is_array($hide_candidate_fields) && in_array('fields_candidate_social', $hide_candidate_fields, true));
			$gallery_visible = !(is_array($hide_candidate_fields) && in_array('fields_candidate_gallery', $hide_candidate_fields, true));
			$video_visible = !(is_array($hide_candidate_fields) && in_array('fields_candidate_video', $hide_candidate_fields, true));

			// Check if any basic field is visible
			$basic_visible = false;
			if (is_array($hide_candidate_fields) && !empty($basic_fields)) {
				foreach ($basic_fields as $field) {
					if (!in_array($field, $hide_candidate_fields, true)) {
						$basic_visible = true;
						break;
					}
				}
			} else {
				$basic_visible = true; // If hide_candidate_fields is not array or empty, show basic
			}

			// Section is visible if at least one sub-section is visible
			return $basic_visible || $location_visible || $resume_visible || $social_visible || $gallery_visible || $video_visible;
		}

		// Education, Experience, Skills, Projects, Awards sections don't have individual hideable fields
		// They are controlled by group hide settings only
		return true;
	}
}

/**
 * Check if a jobs section should be visible based on hide settings
 *
 * @param string $section_name Section name (general, salary, apply, company, location, thumbnail, gallery, video)
 * @param array $hide_jobs_fields Array of hidden field names
 * @param array $hide_jobs_group_fields Array of hidden group field names
 * @return bool True if section should be visible, false otherwise
 */
if (!function_exists('civi_is_jobs_section_visible')) {
	function civi_is_jobs_section_visible($section_name, $hide_jobs_fields, $hide_jobs_group_fields)
	{
		// Check if section is in group hide list
		if (is_array($hide_jobs_group_fields) && in_array($section_name, $hide_jobs_group_fields, true)) {
			return false;
		}

		// Check section-specific visibility logic
		if ($section_name === 'general') {
			// Check if all general fields are hidden
			$general_fields = array(
				'fields_jobs_name',
				'fields_jobs_category',
				'fields_jobs_type',
				'fields_jobs_skills',
				'fields_jobs_des',
				'fields_jobs_career',
				'fields_jobs_experience',
				'fields_jobs_qualification',
				'fields_jobs_quantity',
				'fields_jobs_gender',
				'fields_closing_days'
			);
			if (is_array($hide_jobs_fields) && !empty($general_fields)) {
				foreach ($general_fields as $field) {
					if (!in_array($field, $hide_jobs_fields, true)) {
						return true; // At least one field is visible
					}
				}
				return false; // All fields are hidden
			}
			return true;
		} elseif ($section_name === 'location') {
			// Check if both location and map fields are hidden
			if (is_array($hide_jobs_fields)) {
				$location_hidden = in_array('fields_jobs_location', $hide_jobs_fields, true);
				$map_hidden = in_array('fields_map', $hide_jobs_fields, true);
				return !($location_hidden && $map_hidden);
			}
			return true;
		}
		// Salary, Apply, Company, Thumbnail, Gallery, Video sections don't have individual hideable fields
		// They are controlled by group hide settings only
		return true;
	}
}
