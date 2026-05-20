<?php

/**
 * Calzones: Default Options
 * Created by letruong272@gmail.com
 *
 * @package WordPress
 * @subpackage Calzones Theme
 * @since 1.0
 */

/**
 *  Get default options
 */
if (!function_exists('jobportal_get_default_theme_options')) {
	function jobportal_get_default_theme_options()
	{
		$defaults = array();

		/**
		 *  General
		 */
		$defaults['logo_dark']         = JOBPORTAL_IMAGES . 'logo.png';
		$defaults['logo_dark_retina']  = JOBPORTAL_IMAGES . 'logo-retina.png';
		$defaults['logo_light']        = JOBPORTAL_IMAGES . 'logo-light.png';
		$defaults['logo_light_retina'] = JOBPORTAL_IMAGES . 'logo-light-retina.png';

		$defaults['type_loading_effect'] 	  = 'none';
		$defaults['animation_loading_effect'] = 'css-1';
		$defaults['image_loading_effect'] 	  = '';
		$defaults['enable_fulltext_search']   = '0';

		$defaults['url_facebook'] 	 = '';
		$defaults['url_twitter'] 	 = '';
		$defaults['url_instagram'] 	 = '';
		$defaults['url_youtube'] 	 = '';
		$defaults['url_google_plus'] = '';
		$defaults['url_skype'] 	  	 = '';
		$defaults['url_linkedin'] 	 = '';
		$defaults['url_pinterest'] 	 = '';
		$defaults['url_slack'] 	  	 = '';
		$defaults['url_rss'] 	  	 = '';

		$defaults['page_title_text_color']     = '#111';
		$defaults['page_title_bg_color']       = '#f9f9f9';
		$defaults['page_title_bg_image']       = '';
		$defaults['page_title_bg_size']        = 'auto';
		$defaults['page_title_bg_repeat']      = 'no-repeat';
		$defaults['page_title_bg_position']    = 'right top';
		$defaults['page_title_bg_attachment']  = 'scroll';
		$defaults['page_title_font_size']      = 40;
		$defaults['page_title_letter_spacing'] = 0;

		/**
		 *  Color
		 */
		$defaults['text_color'] 	  	   = '#475569';
		$defaults['accent_color'] 	  	   = '#0d9488';
		$defaults['primary_color'] 	  	   = '#1e293b';
		$defaults['secondary_color'] 	   = '#64748b';
		$defaults['border_color'] 	  	   = '#e2e8f0';
		$defaults['body_background_color'] = '#ffffff';
		$defaults['bg_body_image'] 	  	   = '';
		$defaults['bg_body_size'] 	  	   = 'auto';
		$defaults['bg_body_repeat'] 	   = 'no-repeat';
		$defaults['bg_body_position'] 	   = 'left top';
		$defaults['bg_body_attachment']    = 'scroll';

		/**
		 *  Typography
		 */
		$defaults['font-style'] 	= array('bold', 'italic');
		$defaults['font-family'] 	= 'Inter';
		$defaults['font-size'] 		= '16px';
		$defaults['font-weight'] 	= 'normal';
		$defaults['letter-spacing'] = 'inherit';

		$defaults['heading-font-style'] 	= array('bold', 'italic');
		$defaults['heading-font-family'] 	= 'Inter';
		$defaults['heading-font-size'] 		= '34px';
		$defaults['heading-line-height'] 	= 'inherit';
		$defaults['heading-variant'] 		= '500';
		$defaults['heading-letter-spacing'] = 'inherit';

		/**
		 *  Layout
		 */
		$defaults['layout_content'] = 'fullwidth';
		$defaults['content_width']  = 1920;
		$defaults['layout_sidebar'] = 'right-sidebar';
		$defaults['sidebar_width']  = 400;

		/**
		 *  Header
		 */
		$defaults['header_type'] = '';

		$defaults['header_background']           = '#ffffff';
		$defaults['top_bar_enable']              = '0';
		$defaults['sticky_header']               = '0';
		$defaults['header_sticky_background']    = '#0f172a';
		$defaults['float_header']                = '0';
		$defaults['show_canvas_menu']            = '0';
		$defaults['show_main_menu']              = '1';
		$defaults['show_login']                  = '1';
		$defaults['show_add_jobs_button']        = '1';
		$defaults['show_icon_noti']              = '1';
		$defaults['show_search_icon']            = '1';
		$defaults['logo_width']                  = '80';
		$defaults['header_padding_top']          = '8';
		$defaults['header_padding_bottom']       = '8';

		/**
		 *  Top Bar
		 */
		$defaults['top_bar_ringbell']  = JOBPORTAL_IMAGES . 'ringbell.svg';
		$defaults['top_bar_text']      = esc_html__('Subscribe for job alerts by email!', 'jobportal');
		$defaults['top_bar_link']      = '#';
		$defaults['top_bar_phone']     = esc_html__('+84-65854332', 'jobportal');
		$defaults['top_bar_email']     = esc_html__('hello@jobportal.example.com', 'jobportal');
		$defaults['top_bar_color']     = '#94a3b8';
		$defaults['top_bar_bg_color']  = '#0f172a';

		/**
		 *  Footer
		 */
		$defaults['footer_type'] = '';
		$defaults['footer_copyright_enable'] = true;
		$defaults['footer_copyright_text']   = esc_html__('© 2025 JobPortal. All Rights Reserved.', 'jobportal');

		/**
		 *  404
		 */
		$defaults['page_404_type'] = '';
		$defaults['page_404_image']   = JOBPORTAL_IMAGES . 'img-404.png';
		$defaults['page_404_title']   = esc_html__('Hmm, that didn’t work.', 'jobportal');
		$defaults['page_404_des']   = esc_html__('The page you are looking for cannot be found', 'jobportal');
		$defaults['page_404_btn']   = esc_html__('Go to home page', 'jobportal');

		/**
		 *  Blog
		 */
		$defaults['blog_sidebar']                   = 'right-sidebar';
		$defaults['blog_sidebar_width']             = 400;
		$defaults['blog_image_size']             	= '740x640';
		$defaults['blog_content_layout']            = 'layout-list';
		$defaults['blog_enable_categories']         = '0';
		$defaults['blog_number_column']             = 'columns-3';
		$defaults['enable_page_title_blog']         = '1';
		$defaults['page_title_blog_name']         	= esc_html__('Our Blog', 'jobportal');
		$defaults['style_page_title_blog']          = 'normal';
		$defaults['bg_page_title_blog']             = '';
		$defaults['color_page_title_blog']          = '#111';
		$defaults['bg_image_page_title_blog']       = '';
		$defaults['bg_size_page_title_blog']        = 'auto';
		$defaults['bg_repeat_page_title_blog']      = 'no-repeat';
		$defaults['bg_position_page_title_blog']    = 'right top';
		$defaults['bg_attachment_page_title_blog']  = 'scroll';
		$defaults['font_size_page_title_blog']      = 40;
		$defaults['letter_spacing_page_title_blog'] = 0;

		/**
		 *  Single Post
		 */
		$defaults['post_single_sidebar'] = 'right-sidebar';
		$defaults['post_comment']  	     = '1';

		return $defaults;
	}
}

/**
 *  Get theme options
 */
if (!function_exists('get_option_customize')) {
	function get_option_customize($key)
	{

		$value = null;

		$default_options = jobportal_get_default_theme_options();

		if (empty($key)) {
			return;
		}

		if (class_exists('JobPortal_Framework')) {
			$theme_option = Kirki::get_option($key);
		}

		if (isset($theme_option)) {
			$value = $theme_option;
		} elseif (isset($default_options[$key])) {
			$value = $default_options[$key];
		}

		// Polylang/WPML: Translate header/footer/404 post IDs
		if (($key === 'header_type' || $key === 'footer_type' || $key === 'header_dashboard_type' || $key === 'page_404_type') && !empty($value) && is_numeric($value)) {
			$translated_id = false;

			// Try Polylang
			if (function_exists('pll_get_post')) {
				$translated_id = pll_get_post($value);
			}

			// Try WPML fallback
			if (!$translated_id && has_filter('translate_object_id')) {
				$translated_id = apply_filters('wpml_object_id', $value, get_post_type($value), true);
			}

			if ($translated_id) {
				$value = $translated_id;
			}
		}

		return $value;
	}
}

/**
 *  Get theme mod
 */
if (!function_exists('jobportal_get_theme_mod')) {
	function jobportal_get_theme_mod($key)
	{

		$value = null;

		if (empty($key)) {
			return;
		}

		$theme_option = get_theme_mod($key);

		if (!empty($theme_option)) {
			$value = $theme_option;
		} else {
			$value = false;
		}

		return $value;
	}
}
