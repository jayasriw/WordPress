<?php

namespace Civi_Elementor;

use Elementor\Controls_Manager;

defined('ABSPATH') || exit;

class Widget_Contact_Form_7 extends Form_Base
{

	public function get_name()
	{
		return 'civi-contact-form-7';
	}

	public function get_title()
	{
		return esc_html__('Contact Form 7', 'civi');
	}

	public function get_keywords()
	{
		return ['contact', 'form'];
	}

    public function get_style_depends()
    {
        return ['civi-el-widget-contact-form-7'];
    }

	private function get_form_list()
	{
		$forms = [];

		$cf7 = get_posts('post_type="wpcf7_contact_form"&numberposts=-1');

		if ($cf7) {
			foreach ($cf7 as $cform) {
				$forms[$cform->ID] = $cform->post_title;
			}
		} else {
			$forms[0] = esc_html__('No contact forms found', 'civi');
		}

		return $forms;
	}

	/**
	 * Get first key of array
	 *
	 * @see array_key_first()
	 *
	 * @param $arr
	 *
	 * @return int|string
	 */
	private function get_form_default($arr)
	{
		foreach ($arr as $key => $unused) {
			return $key;
		}

		return 0;
	}

	protected function register_controls()
	{
		$this->add_content_section();

		$this->add_field_contact_style_section();

		$this->add_button_contact_style_section();
	}

    protected function add_field_contact_style_section()
    {
        $this->start_controls_section('form_field_style_section', [
            'label' => esc_html__('Field', 'civi'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_responsive_control('field_padding', [
            'label'      => esc_html__('Padding', 'civi'),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%'],
            'selectors'  => [
                '{{WRAPPER}} .wpcf7-form-control' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_responsive_control('field_border_width', [
            'label'       => esc_html__('Border Width', 'civi'),
            'type'        => Controls_Manager::DIMENSIONS,
            'placeholder' => '1',
            'size_units'  => ['px'],
            'selectors'   => [
                '{{WRAPPER}} .wpcf7-form-control' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_responsive_control('field_border_radius', [
            'label'       => esc_html__('Border Radius', 'civi'),
            'type'        => Controls_Manager::DIMENSIONS,
            'placeholder' => '5',
            'size_units'  => ['px', '%'],
            'selectors'   => [
                '{{WRAPPER}} .wpcf7-form-control' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->start_controls_tabs('field_colors_tabs');

        $this->start_controls_tab('field_colors_normal_tab', [
            'label' => esc_html__('Normal', 'civi'),
        ]);

        $this->add_control('field_text_color', [
            'label'     => esc_html__('Text Color', 'civi'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .wpcf7-form-control' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_control('field_border_color', [
            'label'     => esc_html__('Border Color', 'civi'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .wpcf7-form-control' => 'border-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('field_background_color', [
            'label'     => esc_html__('Background Color', 'civi'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .wpcf7-form-control' => 'background-color: {{VALUE}};',
            ],
        ]);

        $this->end_controls_tab();

        $this->start_controls_tab('field_colors_focus_tab', [
            'label' => esc_html__('Focus', 'civi'),
        ]);

        $this->add_control('field_text_focus_color', [
            'label'     => esc_html__('Text Color', 'civi'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .wpcf7-form-control:focus' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_control('field_border_focus_color', [
            'label'     => esc_html__('Border Color', 'civi'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .wpcf7-form-control:focus' => 'border-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('field_background_focus_color', [
            'label'     => esc_html__('Background Color', 'civi'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .wpcf7-form-control:focus' => 'background-color: {{VALUE}};',
            ],
        ]);

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    protected function add_button_contact_style_section()
    {
        $this->start_controls_section('form_button_style_section', [
            'label' => esc_html__('Button', 'civi'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_responsive_control('button_margin', [
            'label'      => esc_html__('Margin', 'civi'),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%'],
            'selectors'  => [
                '{{WRAPPER}} .wpcf7-submit' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_responsive_control('button_padding', [
            'label'      => esc_html__('Padding', 'civi'),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%'],
            'selectors'  => [
                '{{WRAPPER}} .wpcf7-submit' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_responsive_control('button_border_width', [
            'label'       => esc_html__('Border Width', 'civi'),
            'type'        => Controls_Manager::DIMENSIONS,
            'placeholder' => '1',
            'size_units'  => ['px'],
            'selectors'   => [
                '{{WRAPPER}} .wpcf7-submit' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_responsive_control('button_border_radius', [
            'label'       => esc_html__('Border Radius', 'civi'),
            'type'        => Controls_Manager::DIMENSIONS,
            'placeholder' => '5',
            'size_units'  => ['px', '%'],
            'selectors'   => [
                '{{WRAPPER}} .wpcf7-submit' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->start_controls_tabs('button_colors_tabs');

        $this->start_controls_tab('button_colors_normal_tab', [
            'label' => esc_html__('Normal', 'civi'),
        ]);

        $this->add_control('button_text_color', [
            'label'     => esc_html__('Text Color', 'civi'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .wpcf7-submit' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_control('button_border_color', [
            'label'     => esc_html__('Border Color', 'civi'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .wpcf7-submit' => 'border-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('button_background_color', [
            'label'     => esc_html__('Background Color', 'civi'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .wpcf7-submit' => 'background-color: {{VALUE}};',
            ],
        ]);

        $this->end_controls_tab();

        $this->start_controls_tab('button_colors_hover_tab', [
            'label' => esc_html__('Hover', 'civi'),
        ]);

        $this->add_control('button_text_hover_color', [
            'label'     => esc_html__('Text Color', 'civi'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .wpcf7-submit:hover' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_control('button_border_hover_color', [
            'label'     => esc_html__('Border Color', 'civi'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .wpcf7-submit:hover' => 'border-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('button_background_hover_color', [
            'label'     => esc_html__('Background Color', 'civi'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .wpcf7-submit:hover' => 'background-color: {{VALUE}};',
            ],
        ]);

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

	private function add_content_section()
	{
		$this->start_controls_section('content_section', [
			'label' => esc_html__('Layout', 'civi'),
		]);

		$form_list    = $this->get_form_list();
		$form_default = $this->get_form_default($form_list);

		$this->add_control('form_id', [
			'label'   => esc_html__('Select Form', 'civi'),
			'type'    => Controls_Manager::SELECT,
			'options' => $form_list,
			'default' => $form_default,
		]);

		$this->add_control('style', [
			'label'        => esc_html__('Style', 'civi'),
			'type'         => Controls_Manager::SELECT,
			'options'      => [
				'01' => '01',
			],
			'default'      => '01',
			'prefix_class' => 'civi-contact-form-style-',
		]);

		$this->end_controls_section();
	}

	protected function render()
	{
		$settings = $this->get_settings_for_display();
		$form_id  = isset($settings['form_id']) ? $settings['form_id'] : 0;

		$this->add_render_attribute('box', 'class', 'civi-contact-form-7');
?>
		<div <?php $this->print_render_attribute_string('box') ?>>
			<?php echo do_shortcode('[contact-form-7 id="' . $form_id . '"]'); ?>
		</div>
<?php
	}
}
