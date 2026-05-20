<?php

namespace JobPortal_Elementor;

use Elementor\Group_Control_Base;
use Elementor\Controls_Manager;

defined('ABSPATH') || exit;

/**
 * Elementor tooltip control.
 *
 * A base control for creating tooltip control.
 *
 * @since 1.0.0
 */
class Group_Control_Tooltip extends Group_Control_Base
{

	protected static $fields;

	public static function get_type()
	{
		return 'tooltip';
	}

	protected function init_fields()
	{
		$fields = [];

		$fields['skin'] = [
			'label'   => esc_html__('Tooltip Skin', 'jobportal'),
			'type'    => Controls_Manager::SELECT,
			'options' => [
				''        => esc_html__('Black', 'jobportal'),
				'white'   => esc_html__('White', 'jobportal'),
				'primary' => esc_html__('Primary', 'jobportal'),
			],
			'default' => '',
		];

		$fields['position'] = [
			'label'   => esc_html__('Tooltip Position', 'jobportal'),
			'type'    => Controls_Manager::SELECT,
			'options' => [
				'top'          => esc_html__('Top', 'jobportal'),
				'right'        => esc_html__('Right', 'jobportal'),
				'bottom'       => esc_html__('Bottom', 'jobportal'),
				'left'         => esc_html__('Left', 'jobportal'),
				'top-left'     => esc_html__('Top Left', 'jobportal'),
				'top-right'    => esc_html__('Top Right', 'jobportal'),
				'bottom-left'  => esc_html__('Bottom Left', 'jobportal'),
				'bottom-right' => esc_html__('Bottom Right', 'jobportal'),
			],
			'default' => 'top',
		];

		return $fields;
	}

	protected function get_default_options()
	{
		return [
			'popover' => [
				'starter_title' => _x('Tooltip', 'Tooltip Control', 'jobportal'),
				'starter_name'  => 'enable',
				'starter_value' => 'yes',
				'settings'      => [
					'render_type' => 'template',
				],
			],
		];
	}
}
