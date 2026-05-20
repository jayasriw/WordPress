<?php

namespace JobPortal_Elementor;

defined('ABSPATH') || exit;

class Widget_Utils
{
	public static function get_control_options_horizontal_alignment()
	{
		return [
			'left'   => [
				'title' => esc_html__('Left', 'jobportal'),
				'icon'  => 'eicon-h-align-left',
			],
			'center' => [
				'title' => esc_html__('Center', 'jobportal'),
				'icon'  => 'eicon-h-align-center',
			],
			'right'  => [
				'title' => esc_html__('Right', 'jobportal'),
				'icon'  => 'eicon-h-align-right',
			],
		];
	}

	public static function get_control_options_horizontal_alignment_full()
	{
		return [
			'left'    => [
				'title' => esc_html__('Left', 'jobportal'),
				'icon'  => 'eicon-h-align-left',
			],
			'center'  => [
				'title' => esc_html__('Center', 'jobportal'),
				'icon'  => 'eicon-h-align-center',
			],
			'right'   => [
				'title' => esc_html__('Right', 'jobportal'),
				'icon'  => 'eicon-h-align-right',
			],
			'stretch' => [
				'title' => esc_html__('Stretch', 'jobportal'),
				'icon'  => 'eicon-h-align-stretch',
			],
		];
	}

	public static function get_control_options_vertical_alignment()
	{
		return [
			'top'    => [
				'title' => esc_html__('Top', 'jobportal'),
				'icon'  => 'eicon-v-align-top',
			],
			'middle' => [
				'title' => esc_html__('Middle', 'jobportal'),
				'icon'  => 'eicon-v-align-middle',
			],
			'bottom' => [
				'title' => esc_html__('Bottom', 'jobportal'),
				'icon'  => 'eicon-v-align-bottom',
			],
		];
	}

	public static function get_control_options_vertical_full_alignment()
	{
		return [
			'top'     => [
				'title' => esc_html__('Top', 'jobportal'),
				'icon'  => 'eicon-v-align-top',
			],
			'middle'  => [
				'title' => esc_html__('Middle', 'jobportal'),
				'icon'  => 'eicon-v-align-middle',
			],
			'bottom'  => [
				'title' => esc_html__('Bottom', 'jobportal'),
				'icon'  => 'eicon-v-align-bottom',
			],
			'stretch' => [
				'title' => esc_html__('Stretch', 'jobportal'),
				'icon'  => 'eicon-v-align-stretch',
			],
		];
	}

	public static function get_control_options_text_align()
	{
		return [
			'left'   => [
				'title' => esc_html__('Left', 'jobportal'),
				'icon'  => 'eicon-text-align-left',
			],
			'center' => [
				'title' => esc_html__('Center', 'jobportal'),
				'icon'  => 'eicon-text-align-center',
			],
			'right'  => [
				'title' => esc_html__('Right', 'jobportal'),
				'icon'  => 'eicon-text-align-right',
			],
		];
	}

	public static function get_control_options_flex_align()
	{
		return [
			'flex-start'   => [
				'title' => esc_html__('Left', 'jobportal'),
				'icon'  => 'eicon-text-align-left',
			],
			'center' => [
				'title' => esc_html__('Center', 'jobportal'),
				'icon'  => 'eicon-text-align-center',
			],
			'flex-end'  => [
				'title' => esc_html__('Right', 'jobportal'),
				'icon'  => 'eicon-text-align-right',
			],
		];
	}

	public static function get_control_options_text_align_full()
	{
		return [
			'left'    => [
				'title' => esc_html__('Left', 'jobportal'),
				'icon'  => 'eicon-text-align-left',
			],
			'center'  => [
				'title' => esc_html__('Center', 'jobportal'),
				'icon'  => 'eicon-text-align-center',
			],
			'right'   => [
				'title' => esc_html__('Right', 'jobportal'),
				'icon'  => 'eicon-text-align-right',
			],
			'justify' => [
				'title' => esc_html__('Justified', 'jobportal'),
				'icon'  => 'eicon-text-align-justify',
			],
		];
	}

	public static function get_button_style()
	{
		return [
			'classic' => esc_html__('Classic', 'jobportal'),
			'outline' => esc_html__('Outline', 'jobportal'),
			'link'    => esc_html__('Link', 'jobportal'),
			'border-bottom' => esc_html__('Border Bottom', 'jobportal'),
		];
	}

	public static function get_button_shape()
	{
		return [
			'rounded' => esc_html__('Rounded', 'jobportal'),
			'square'  => esc_html__('Square', 'jobportal'),
			'round'   => esc_html__('Round', 'jobportal'),
		];
	}

	public static function get_button_size()
	{
		return [
			'xs' => esc_html__('Extra Small', 'jobportal'),
			'sm' => esc_html__('Small', 'jobportal'),
			'md' => esc_html__('Medium', 'jobportal'),
			'lg' => esc_html__('Large', 'jobportal'),
			'xl' => esc_html__('Extra Large', 'jobportal'),
		];
	}

	/**
	 * Get recommended social icons for control ICONS.
	 *
	 * @return array
	 */
	public static function get_recommended_social_icons()
	{
		return [
			'fa-brands' => [
				'android',
				'apple',
				'behance',
				'bitbucket',
				'codepen',
				'delicious',
				'deviantart',
				'digg',
				'dribbble',
				'envelope',
				'facebook',
				"facebook-f",
				"facebook-messenger",
				"facebook-square",
				'flickr',
				'foursquare',
				'free-code-camp',
				'github',
				'gitlab',
				'globe',
				'houzz',
				'instagram',
				'jsfiddle',
				'link',
				'linkedin',
				'medium',
				'meetup',
				'mix',
				'mixcloud',
				'odnoklassniki',
				'pinterest',
				'product-hunt',
				'reddit',
				'rss',
				'shopping-cart',
				'skype',
				'slideshare',
				'snapchat',
				'soundcloud',
				'spotify',
				'stack-overflow',
				'steam',
				'telegram',
				'thumb-tack',
				'tripadvisor',
				'tumblr',
				'twitch',
				'twitter',
				'viber',
				'vimeo',
				'vk',
				'weibo',
				'weixin',
				'whatsapp',
				'wordpress',
				'xing',
				'yelp',
				'youtube',
				'500px',
			],
		];
	}

	public static function get_grid_metro_size()
	{
		return [
			'1:1'   => esc_html__('Width 1 - Height 1', 'jobportal'),
			'1:2'   => esc_html__('Width 1 - Height 2', 'jobportal'),
			'1:0.7' => esc_html__('Width 1 - Height 70%', 'jobportal'),
			'1:1.3' => esc_html__('Width 1 - Height 130%', 'jobportal'),
			'2:1'   => esc_html__('Width 2 - Height 1', 'jobportal'),
			'2:2'   => esc_html__('Width 2 - Height 2', 'jobportal'),
		];
	}

	public static function get_user_roles()
	{
		return [
			'guest' => esc_html__('Guest', 'jobportal'),
			'logged_in' => esc_html__('Logged In User', 'jobportal'),
			'jobportal_user_candidate' => esc_html__('Candidate', 'jobportal'),
			'jobportal_user_candidate_has_package' => esc_html__('Candidate Has Package', 'jobportal'),
			'jobportal_user_candidate_no_package' => esc_html__('Candidate No Package', 'jobportal'),
			'jobportal_user_employer' => esc_html__('Employer', 'jobportal'),
			'jobportal_user_employer_has_package' => esc_html__('Employer Has Package', 'jobportal'),
			'jobportal_user_employer_no_package' => esc_html__('Employer No Package', 'jobportal'),
			'administrator' => esc_html__('Administrator', 'jobportal'),
		];
	}

	public static function check_user_has_package($user_id)
	{
		if (class_exists('JobPortal_Package')) {
			$jobportal_package = new \JobPortal_Package();
			if (method_exists($jobportal_package, 'get_active_package_info')) {
				$package_info = $jobportal_package->get_active_package_info($user_id);
				return !empty($package_info);
			}
		}
		return false;
	}
}
