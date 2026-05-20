<?php

namespace JobPortal_Elementor;

use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use \Elementor\Group_Control_Border;
use Elementor\Icons_Manager;

defined('ABSPATH') || exit;

class Widget_Button extends Base
{

	public function get_name()
	{
		return "jobportal-button';
	}

	public function get_title()
	{
		return esc_html__('Advanced Button', 'jobportal');
	}

	public function get_icon_part()
	{
		return 'eicon-button';
	}

    public function get_style_depends()
    {
        return ["jobportal-el-widget-button'];
    }

	protected function register_controls()
	{
		$this->register_button_content();
		$this->register_button_wrap_style();
		$this->register_button_icon_style();
	}

	private function register_button_content()
	{
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__('Button', 'jobportal')
			]
		);

		$this->add_control(
			'text',
			[
				'label' => esc_html__('Text', 'jobportal'),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__('Button', 'jobportal'),
				'placeholder' => esc_html__('Button', 'jobportal'),
			]
		);

		$this->add_control(
            'open_lr_form',
            [
                'label' => esc_html__('Click Form Account', 'jobportal'),
                'description' => esc_html__('Not logged in, click the account popup form.', 'jobportal'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
            ]
        );

		$this->add_control(
			'link',
			[
				'label' => esc_html__('Link', 'jobportal'),
				'type' => Controls_Manager::URL,
				'dynamic' => [
					'active' => true,
				],
				'placeholder' => esc_html__('https://your-link.com', 'jobportal'),
				'default' => [
					'url' => '#',
				],
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'role',
			[
				'label' => esc_html__('Role', 'jobportal'),
				'type' => Controls_Manager::SELECT,
				'options' => Widget_Utils::get_user_roles(),
				'default' => 'guest',
			]
		);

		$repeater->add_control(
			'text',
			[
				'label' => esc_html__('Text', 'jobportal'),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__('Button', 'jobportal'),
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$repeater->add_control(
			'link',
			[
				'label' => esc_html__('Link', 'jobportal'),
				'type' => Controls_Manager::URL,
				'dynamic' => [
					'active' => true,
				],
				'placeholder' => esc_html__('https://your-link.com', 'jobportal'),
				'default' => [
					'url' => '#',
				],
			]
		);

		$repeater->add_control(
			'role_hide',
			[
				'label' => esc_html__('Hide button', 'jobportal'),
				'description' => esc_html__('Hide this button for users matching this role.', 'jobportal'),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'role_based_items_desc',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw' => esc_html__('The order matters. The first matching role from top to bottom will be used. Place specific roles (e.g., Employer) above generic ones (e.g., Logged In User).', 'jobportal'),
				'content_classes' => 'jobportal',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'role_based_items',
			[
				'label' => esc_html__('Role Based Options', 'jobportal'),
				'description' => esc_html__('The order matters. The first matching role from top to bottom will be used. Place specific roles (e.g., Employer) above generic ones (e.g., Logged In User).', 'jobportal'),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'title_field' => '{{{ role }}}',
			]
		);


		$this->add_control(
			'type',
			[
				'label' => esc_html__('Type', 'jobportal'),
				'type' => Controls_Manager::SELECT,
				'default' => 'classic',
				'separator'     => 'before',
				'options' => Widget_Utils::get_button_style(),
			]
		);

		$this->add_control(
			'shape',
			[
				'label' => esc_html__('Shape', 'jobportal'),
				'type' => Controls_Manager::SELECT,
				'default' => 'rounded',
				'options' => Widget_Utils::get_button_shape(),
				'condition' => [
					'type[value]!' => 'link',
				],
			]
		);

		$this->add_control(
			'size',
			[
				'label' => esc_html__('Size', 'jobportal'),
				'type' => Controls_Manager::SELECT,
				'default' => 'md',
				'options' =>  Widget_Utils::get_button_size(),
			]
		);

		$this->add_control(
			'icon',
			[
				'label' => esc_html__('Icon', 'jobportal'),
				'type' => Controls_Manager::ICONS,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'icon_align',
			[
				'label' => esc_html__('Icon Position', 'jobportal'),
				'type' => Controls_Manager::SELECT,
				'default' => 'left',
				'options' => [
					'left' => esc_html__('Before', 'jobportal'),
					'right' => esc_html__('After', 'jobportal'),
				],
				'condition' => [
					'icon[value]!' => '',
				],
			]
		);

		$this->end_controls_section();
	}

	private function register_button_wrap_style()
	{
		$this->start_controls_section(
			'section_wrap_style',
			[
				'label' => esc_html__('Button', 'jobportal'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'typography',
				'selector' => '{{WRAPPER}} .jobportal-button',
			]
		);

		$this->add_control(
			'button_gradient_background',
			[
				'label' => esc_html__('Use Gradient Background', 'jobportal'),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Show', 'jobportal'),
				'label_off' => esc_html__('Hide', 'jobportal'),
				'return_value' => 'yes',
				'default' => '',
				'condition' => [
					'type!' => 'link',
				],
			]
		);

		$this->start_controls_tabs('tabs_button_style');

		$this->start_controls_tab(
			'tab_button_normal',
			[
				'label' => esc_html__('Normal', 'jobportal'),
			]
		);

		$this->add_control(
			'button_text_color',
			[
				'label' => esc_html__('Text Color', 'jobportal'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .jobportal-button' => 'fill: {{VALUE}}; color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_border_color',
			[
				'label' => esc_html__('Border Color', 'jobportal'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .jobportal-button' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'type!' => 'link',
				],
			]
		);

		$this->add_control(
			'button_background_color',
			[
				'label' => esc_html__('Background Color', 'jobportal'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .jobportal-button' => 'background-color: {{VALUE}};',
				],
				'condition' => [
					'type!' => ['link', 'outline'],
					'button_gradient_background' => ''
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'button_background_gradient_color',
				'types' => ['gradient', 'classic'],
				'selector' => '{{WRAPPER}} .jobportal-button',
				'condition' => [
					'type!' => ['link', 'outline'],
					'button_gradient_background' => 'yes'
				],
			]
		);


		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			[
				'label' => esc_html__('Hover', 'jobportal'),
			]
		);


		$this->add_control(
			'button_text_color_hover',
			[
				'label' => esc_html__('Text Color', 'jobportal'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .jobportal-button:hover' => 'fill: {{VALUE}}; color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_border_color_hover',
			[
				'label' => esc_html__('Border Color', 'jobportal'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .jobportal-button:hover' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'type!' => 'link',
				],
			]
		);

		$this->add_control(
			'button_background_color_hover',
			[
				'label' => esc_html__('Background Color', 'jobportal'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .jobportal-button:hover' => 'background-color: {{VALUE}};',
				],
				'condition' => [
					'type!' => ['link'],
					'button_gradient_background' => ''
				],
			]
		);


		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'button_background_gradient_color_hover',
				'types' => ['gradient', 'classic'],
				'selector' => '{{WRAPPER}} .jobportal-button:hover',
				'condition' => [
					'type!' => ['link'],
					'button_gradient_background' => 'yes'
				],
			]
		);

		$this->add_control(
			'hover_animation',
			[
				'label' => esc_html__('Hover Animation', 'jobportal'),
				'type' => Controls_Manager::HOVER_ANIMATION,
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();


		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'border',
				'selector' => '{{WRAPPER}} .jobportal-button',
				'separator' => 'before',
			]
		);


		$this->add_control(
			'border_radius',
			[
				'label' => esc_html__('Border Radius', 'jobportal'),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .jobportal-button' => 'border-radius: {{SIZE}}px;',
				],
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label' => esc_html__('Alignment', 'jobportal'),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left'    => [
						'title' => esc_html__('Left', 'jobportal'),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__('Center', 'jobportal'),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__('Right', 'jobportal'),
						'icon' => 'eicon-text-align-right',
					],
				],
				'default' => '',
				'prefix_class' => 'elementor%s-align-',
			]
		);

		$this->add_responsive_control(
			'button_width',
			[
				'label'      => esc_html__('Width', 'jobportal'),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => ['px', '%'],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 500,
						'step' => 1,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .jobportal-button' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);


		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'button_box_shadow',
				'selector' => '{{WRAPPER}} .jobportal-button',
			]
		);

		$this->add_responsive_control(
			'text_padding',
			[
				'label' => esc_html__('Padding', 'jobportal'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} .jobportal-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);
		$this->end_controls_section();
	}

	private function register_button_icon_style()
	{
		$this->start_controls_section(
			'section_icon_style',
			[
				'label' => esc_html__('Icon', 'jobportal'),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'icon[value]!' => '',
				],
			]
		);

		$this->add_control(
			'icon_indent',
			[
				'label' => esc_html__('Icon Spacing', 'jobportal'),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .button-icon-right i' => 'margin-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .button-icon-right svg' => 'margin-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .button-icon-left i' => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .button-icon-left svg' => 'margin-right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label'      => esc_html__('Size', 'jobportal'),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => ['px', 'em', 'rem'],
				'range'      => [
					'px' => [
						'min' => 1,
						'max' => 200,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .jobportal-button-icon' => 'font-size: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->start_controls_tabs('tabs_icon_style');

		$this->start_controls_tab(
			'tab_icon_normal',
			[
				'label' => esc_html__('Normal', 'jobportal'),
			]
		);

		$this->add_control(
			'icon_color',
			[
				'label' => esc_html__('Color', 'jobportal'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .jobportal-button-icon' => 'fill: {{VALUE}}; color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();


		$this->start_controls_tab(
			'tab_icon_hover',
			[
				'label' => esc_html__('Hover', 'jobportal'),
			]
		);

		$this->add_control(
			'icon_color_hover',
			[
				'label' => esc_html__('Color', 'jobportal'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .jobportal-button-icon:hover' => 'fill: {{VALUE}}; color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function render()
	{
		$settings = $this->get_settings_for_display();

		if (!empty($settings['role_based_items'])) {
			$current_user = wp_get_current_user();
			$user_roles = (array) $current_user->roles;
			$user_id = $current_user->ID;
			$has_package = Widget_Utils::check_user_has_package($user_id);

			foreach ($settings['role_based_items'] as $item) {
				$role = $item['role'];
				$matched = false;

				if ($role === 'guest' && !is_user_logged_in()) {
					$matched = true;
				} elseif (is_user_logged_in()) {
					if ($role === 'logged_in') {
						$matched = true;
					} elseif ($role === 'jobportal_user_candidate' && in_array('jobportal_user_candidate', $user_roles)) {
						$matched = true;
					} elseif ($role === 'jobportal_user_candidate_has_package' && in_array('jobportal_user_candidate', $user_roles) && $has_package) {
						$matched = true;
					} elseif ($role === 'jobportal_user_candidate_no_package' && in_array('jobportal_user_candidate', $user_roles) && !$has_package) {
						$matched = true;
					} elseif ($role === 'jobportal_user_employer' && in_array('jobportal_user_employer', $user_roles)) {
						$matched = true;
					} elseif ($role === 'jobportal_user_employer_has_package' && in_array('jobportal_user_employer', $user_roles) && $has_package) {
						$matched = true;
					} elseif ($role === 'jobportal_user_employer_no_package' && in_array('jobportal_user_employer', $user_roles) && !$has_package) {
						$matched = true;
					} elseif ($role === 'administrator' && in_array('administrator', $user_roles)) {
						$matched = true;
					}
				}

				if ($matched) {
					if (!empty($item['role_hide']) && $item['role_hide'] === 'yes') {
						return;
					}
					if (!empty($item['text'])) {
						$settings['text'] = $item['text'];
					}
					if (!empty($item['link']['url'])) {
						$settings['link'] = $item['link'];

						// Reset link attributes and re-add with new link
						$this->remove_render_attribute('wrapper', 'href');
						$this->remove_render_attribute('wrapper', 'target');
						$this->remove_render_attribute('wrapper', 'rel');
						$this->add_link_attributes('wrapper', $settings['link']);
					}
					break;
				}
			}
		}

		$wrapper_classes = array(
			"jobportal-button',
			"button-{$settings['size']}",
			"button-{$settings['shape']}",
		);

		if ($settings['type'] !== '') {
			$wrapper_classes[] = "button-{$settings['type']}";
		}

		if ($settings['hover_animation']) {
			$wrapper_classes[] = "elementor-animation-{$settings['hover_animation']}";
		}

		if (!empty($settings['icon']) && !empty($settings['icon']['value'])) {
			$wrapper_classes[] = "button-icon-{$settings['icon_align']}";
		}

		if( $settings['open_lr_form'] == 'yes' && !is_user_logged_in()){
			$wrapper_classes[] = 'btn-login';
		}

		$this->add_render_attribute('wrapper', 'class', $wrapper_classes);

		$this->add_inline_editing_attributes('text', 'none');

		if($settings['open_lr_form'] == 'yes'){
            if (!is_user_logged_in()) {
                $this->add_render_attribute('wrapper', 'href', '#popup-form');
            } else {
                if (!empty($settings['link']['url'])) {
                    $this->add_link_attributes('wrapper', $settings['link']);
                }
            }
        } else {
            if (!empty($settings['link']['url'])) {
                $this->add_link_attributes('wrapper', $settings['link']);
            }
        }
?>
<?php
	if( $settings['open_lr_form'] == 'yes' && !is_user_logged_in()){
		echo '<div class="logged-out">';
	}
?>
		<a <?php echo $this->get_render_attribute_string('wrapper') ?>>
			<?php if (!empty($settings['icon']) && !empty($settings['icon']['value']) && ($settings['icon_align'] === 'left')) : ?>
				<span class="jobportal-button-icon"><?php Icons_Manager::render_icon($settings['icon'], ['aria-hidden' => 'true']); ?></span>
			<?php endif; ?>
			<span <?php echo $this->get_render_attribute_string('text') ?>><?php esc_html_e($settings['text']); ?></span>
			<?php if (!empty($settings['icon']) && !empty($settings['icon']['value']) && ($settings['icon_align'] === 'right')) : ?>
				<span class="jobportal-button-icon"><?php Icons_Manager::render_icon($settings['icon'], ['aria-hidden' => 'true']); ?></span>
			<?php endif; ?>
		</a>
<?php
	if($settings['open_lr_form'] == 'yes' && !is_user_logged_in()){
		echo '</div>';
	}
?>
	<?php
	}

	protected function content_template()
	{
		// @formatter:off
	?>
		<# var wrapper_classes=[ "jobportal-button' , 'button-' + settings.size, 'button-' + settings.shape, ]; if (settings.type !=='' ) { wrapper_classes.push('button-' + settings.type); } if ( settings.hover_animation ) { wrapper_classes.push('elementor-animation-' + settings.hover_animation); } if ((settings.icon !=='' ) && (settings.icon.value !=='' )) { wrapper_classes.push('button-icon-' + settings.icon_align); } var iconHTML=elementor.helpers.renderIcon( view, settings.icon, { 'aria-hidden' : true }, 'i' , 'object' ); view.addRenderAttribute('wrapper', 'class' , wrapper_classes); view.addRenderAttribute('text', 'class' , "jobportal-button-text' ); view.addInlineEditingAttributes( 'text' , 'none' ); #>
			<a href="{{ settings.link.url }}" {{{ view.getRenderAttributeString( 'wrapper' ) }}}>
				<# if ((iconHTML.rendered) && (settings.icon_align==='left' )) { #>
					<span class="jobportal-button-icon">{{{ iconHTML.value }}}</span>
					<# } #>
						<span {{{ view.getRenderAttributeString( 'text' ) }}}>{{{ settings.text }}}</span>
						<# if ((iconHTML.rendered) && (settings.icon_align==='right' )) { #>
							<span class="jobportal-button-icon">{{{ iconHTML.value }}}</span>
							<# } #>
			</a>
	<?php
		// @formatter:off
	}
}
