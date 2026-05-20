<?php

namespace JobPortal_Elementor;

use Elementor\Group_Control_Base;
use Elementor\Controls_Manager;

defined('ABSPATH') || exit;

/**
 * Elementor advanced border control.
 *
 * A base control for creating border control. Displays input fields to define
 * border type, border width and border color.
 *
 * @since 1.0.0
 */
class Group_Control_Button extends Group_Control_Base
{

	protected static $fields;

	public static function get_type()
	{
		return 'button';
	}

	protected function init_fields()
	{
		$fields = [];

		$fields['heading'] = [
			'label'     => esc_html__('Button', 'jobportal'),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		];

		$fields['style'] = [
			'label'   => esc_html__('Button Style', 'jobportal'),
			'type'    => Controls_Manager::SELECT,
			'default' => 'classic',
			'options' => Widget_Utils::get_button_style(),
		];

		$fields['text'] = [
			'label'   => esc_html__('Button Text', 'jobportal'),
			'type'    => Controls_Manager::TEXT,
			'dynamic' => [
				'active' => true,
			],
		];

		$fields['link'] = [
			'label'       => esc_html__('Link', 'jobportal'),
			'type'        => Controls_Manager::URL,
			'dynamic'     => [
				'active' => true,
			],
			'placeholder' => esc_attr__('https://your-link.com', 'jobportal'),
			'default'     => [
				'url' => '#',
			],
		];

		$fields['icon'] = [
			'label'       => esc_html__('Button Icon', 'jobportal'),
			'type'        => Controls_Manager::ICONS,
			'label_block' => true,
		];

		$fields['icon_align'] = [
			'label'       => esc_html__('Icon Position', 'jobportal'),
			'type'        => Controls_Manager::CHOOSE,
			'options'     => [
				'left'  => [
					'title' => esc_html__('Left', 'jobportal'),
					'icon'  => 'eicon-h-align-left',
				],
				'right' => [
					'title' => esc_html__('Right', 'jobportal'),
					'icon'  => 'eicon-h-align-right',
				],
			],
			'default'     => 'left',
			'toggle'      => false,
			'label_block' => false,
			'condition'   => [
				'icon[value]!' => '',
			],
		];

		$fields['size'] = [
			'label'   => esc_html__('Button Size', 'jobportal'),
			'type'    => Controls_Manager::SELECT,
			'default' => 'nm',
			'options' => [
				'xs' => esc_html__('Extra Small', 'jobportal'),
				'sm' => esc_html__('Small', 'jobportal'),
				'nm' => esc_html__('Normal', 'jobportal'),
				'lg' => esc_html__('Large', 'jobportal'),
			],
		];

		return $fields;
	}

	protected function get_default_options()
	{
		return [
			'popover' => false,
		];
	}
}
