<?php

namespace JobPortal_Elementor;

use Elementor\Controls_Manager;

defined('ABSPATH') || exit;

class Widget_Shapes extends Base
{

	public function get_name()
	{
		return "jobportal-shapes';
	}

	public function get_title()
	{
		return esc_html__('Modern Shapes', 'jobportal');
	}

	public function get_icon_part()
	{
		return 'eicon-favorite';
	}

	public function get_keywords()
	{
		return ['shapes'];
	}

	protected function register_controls()
	{
		$this->add_content_section();
	}

    public function get_style_depends()
    {
        return ["jobportal-el-widget-shapes'];
    }

	private function add_content_section()
	{
		$this->start_controls_section('content_section', [
			'label' => esc_html__('Shape', 'jobportal'),
		]);

		$this->add_control('type', [
			'label'        => esc_html__('Type', 'jobportal'),
			'type'         => Controls_Manager::SELECT,
			'options'      => [
				'circle'        => esc_html__('Circle', 'jobportal'),
				'border-circle' => esc_html__('Border Circle', 'jobportal'),
				'distortion'    => esc_html__('Distortion', 'jobportal'),
			],
			'default'      => 'circle',
			'prefix_class' => "jobportal-shape-',
		]);

		$this->add_responsive_control('shape_size', [
			'label'     => esc_html__('Size', 'jobportal'),
			'type'      => Controls_Manager::SLIDER,
			'range'     => [
				'px' => [
					'min' => 5,
					'max' => 500,
				],
			],
			'default'   => [
				'unit' => 'px',
				'size' => 50,
			],
			'selectors' => [
				'{{WRAPPER}} .shape' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				'{{WRAPPER}} svg'    => 'width: {{SIZE}}{{UNIT}};',
			],
		]);

		$this->add_responsive_control('shape_border_size', [
			'label'     => esc_html__('Border', 'jobportal'),
			'type'      => Controls_Manager::SLIDER,
			'range'     => [
				'px' => [
					'min' => 1,
					'max' => 50,
				],
			],
			'default'   => [
				'unit' => 'px',
				'size' => 3,
			],
			'selectors' => [
				'{{WRAPPER}} .shape' => 'border-width: {{SIZE}}{{UNIT}};',
			],
			'condition' => [
				'type' => ['border-circle'],
			],
		]);

		$this->add_control('shape_color', [
			'label'     => esc_html__('Color', 'jobportal'),
			'type'      => Controls_Manager::COLOR,
			'default'   => '',
			'selectors' => [
				'{{WRAPPER}} .shape'                => 'color: {{VALUE}};',
				'{{WRAPPER}} .elementor-shape-fill' => 'fill: {{VALUE}};',
			],
			'global' => [
				'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Colors::COLOR_PRIMARY,
			],
		]);

		$this->end_controls_section();
	}

	protected function render()
	{
		$settings = $this->get_settings_for_display();

		$this->add_render_attribute('box', 'class', "jobportal-shape');
?>
		<div <?php $this->print_render_attribute_string('box') ?>>
			<?php if ('distortion' === $settings['type']) : ?>
				<?php echo \JobPortal_Helper::get_file_contents(JOBPORTAL_THEME_DIR . '/assets/shape/' . $settings['type'] . '.svg'); ?>
			<?php else : ?>
				<div class="shape"></div>
			<?php endif; ?>
		</div>
<?php
	}
}
