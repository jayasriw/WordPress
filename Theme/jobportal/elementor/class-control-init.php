<?php

namespace JobPortal_Elementor;

defined('ABSPATH') || exit;

class Control_Init
{

	private static $_instance = null;

	public static function instance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function initialize()
	{
		require_once JOBPORTAL_ELEMENTOR_DIR . '/class-font-awesome-pro.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/class-font-elementor.php';

		/**
		 * Register Controls.
		 */
		add_action('elementor/controls/controls_registered', array($this, 'init_controls'));

		/**
		 * Edit Controls.
		 */
		// Add custom Motion Effect - Entrance Animation.
		add_filter('elementor/controls/animations/additional_animations', [
			$this,
			'add_custom_entrance_animations',
		]);

		/**
		 * Add custom shape divider
		 */
		add_filter('elementor/shapes/additional_shapes', [$this, 'add_custom_shape_divider']);
	}

	public function add_custom_shape_divider($additional_shapes)
	{
		$additional_shapes['center-curve'] = [
			'title'        => esc_html__('Curve Alt', 'jobportal'),
			'has_negative' => true,
			'height_only'  => true,
			'url'          => get_template_directory_uri() . '/assets/shape-divider/center-curve.svg',
			'path'         => get_template_directory() . '/assets/shape-divider/center-curve.svg',
		];

		$additional_shapes['tilt-curve'] = [
			'title'       => esc_html__('Tile Curve', 'jobportal'),
			'has_flip'    => true,
			'height_only' => true,
			'url'         => get_template_directory_uri() . '/assets/shape-divider/curve-tilt.svg',
			'path'        => get_template_directory() . '/assets/shape-divider/curve-tilt.svg',
		];

		$additional_shapes['mountain-alt'] = [
			'title'       => esc_html__('Mountain Alt', 'jobportal'),
			'has_flip'    => true,
			'height_only' => true,
			'url'         => get_template_directory_uri() . '/assets/shape-divider/mountain-alt.svg',
			'path'        => get_template_directory() . '/assets/shape-divider/mountain-alt.svg',
		];

		return $additional_shapes;
	}

	public function add_custom_entrance_animations($animations)
	{
		$animations['By JobPortal'] = [
			'JobPortalFadeInDown'   => 'JobPortal - Fade In Down',
			'JobPortalFadeInLeft'   => 'JobPortal - Fade In Left',
			'JobPortalFadeInRight'  => 'JobPortal - Fade In Right',
			'JobPortalFadeInUp'     => 'JobPortal - Fade In Up',
			'JobPortalSlideInDown'  => 'JobPortal - Slide In Down',
			'JobPortalSlideInLeft'  => 'JobPortal - Slide In Left',
			'JobPortalSlideInRight' => 'JobPortal - Slide In Right',
			'JobPortalSlideInUp'    => 'JobPortal - Slide In Up',
			'JobPortalBottomToTop'    => 'JobPortal - Bottom To Top',
			'JobPortalSpin'    => 'JobPortal - Spin',
			'JobPortalMoving01'    => 'JobPortal - Moving 01',
			'JobPortalMoving02'    => 'JobPortal - Moving 02',
			'JobPortalMoving03'    => 'JobPortal - Moving 03',
			'JobPortalMoving04'    => 'JobPortal - Moving 04',
			'JobPortalMoving05'    => 'JobPortal - Moving 05',
		];

		return $animations;
	}

	/**
	 * @param \Elementor\Controls_Manager $controls_manager
	 *
	 * Include controls files and register them
	 */
	public function init_controls($controls_manager)
	{
		// Include controls files.
		require_once JOBPORTAL_ELEMENTOR_DIR . '/controls/group-control-text-gradient.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/controls/group-control-text-stroke.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/controls/group-control-advanced-border.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/controls/group-control-button.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/controls/group-control-tooltip.php';

		// Group Control.
		$controls_manager->add_group_control(Group_Control_Text_Gradient::get_type(), new Group_Control_Text_Gradient());
		$controls_manager->add_group_control(Group_Control_Text_Stroke::get_type(), new Group_Control_Text_Stroke());
		$controls_manager->add_group_control(Group_Control_Advanced_Border::get_type(), new Group_Control_Advanced_Border());
		$controls_manager->add_group_control(Group_Control_Button::get_type(), new Group_Control_Button());
		$controls_manager->add_group_control(Group_Control_Tooltip::get_type(), new Group_Control_Tooltip());
	}
}

Control_Init::instance()->initialize();
