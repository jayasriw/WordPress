<?php

namespace Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Plugin;

defined('ABSPATH') || exit;

Plugin::instance()->widgets_manager->register(new Widget_Jobs_Category());

class Widget_Jobs_Category extends Widget_Base
{

	const QUERY_CONTROL_ID = 'query';
	const QUERY_OBJECT_POST = 'post';

	public function get_post_type()
	{
		return 'jobs';
	}

	public function get_name()
	{
		return 'jobportal-jobs-category';
	}

	public function get_title()
	{
		return esc_html__('Jobs Category', 'jobportal-framework');
	}

	public function get_icon()
	{
		return 'jobportal-badge eicon-folder-o';
	}

	public function get_keywords()
	{
		return ['jobs', 'category', 'carousel'];
	}

	public function get_style_depends()
	{
		return [JOBPORTAL_PLUGIN_PREFIX . 'jobs-category'];
	}

	protected function register_controls()
	{
		$this->register_layout_section();
		$this->register_slider_section();
		$this->register_layout_style_section();
		$this->register_title_style_section();
		$this->register_icon_style_section();
		$this->register_count_style_section();
	}

	private function register_layout_section()
	{
		$this->start_controls_section('layout_section', [
			'label' => esc_html__('Layout', 'jobportal-framework'),
			'tab' => Controls_Manager::TAB_CONTENT,
		]);

		$this->add_control(
			'layout',
			[
				'label' => esc_html__('Layout', 'jobportal-framework'),
				'type' => Controls_Manager::SELECT,
				'default' => '01',
				'options' => [
					'01' => esc_html__('01', 'jobportal-framework'),
					'02' => esc_html__('02', 'jobportal-framework'),
				],
				'prefix_class' => 'jobportal-layout-',
			]
		);

		$this->add_control('hover_effect', [
			'label'        => esc_html__('Hover Effect', 'jobportal-framework'),
			'type'         => Controls_Manager::SELECT,
			'options'      => [
				''         => esc_html__('None', 'jobportal-framework'),
				'zoom-in'  => esc_html__('Zoom In', 'jobportal-framework'),
				'zoom-out' => esc_html__('Zoom Out', 'jobportal-framework'),
				'move-up'  => esc_html__('Move Up', 'jobportal-framework'),
			],
			'default'      => '',
			'prefix_class' => 'jobportal-animation-',
			'condition' => [
				'layout' => '02',
			],
		]);

		$this->add_control('position', [
			'label'        => esc_html__('Position', 'jobportal-framework'),
			'type'         => Controls_Manager::CHOOSE,
			'default'      => 'top',
			'options'      => [
				'left'  => [
					'title' => esc_html__('Left', 'jobportal-framework'),
					'icon'  => 'eicon-h-align-left',
				],
				'top'   => [
					'title' => esc_html__('Top', 'jobportal-framework'),
					'icon'  => 'eicon-v-align-top',
				],
				'right' => [
					'title' => esc_html__('Right', 'jobportal-framework'),
					'icon'  => 'eicon-h-align-right',
				],
			],
			'prefix_class' => 'elementor-position-',
		]);

		$repeater = new Repeater();

		$taxonomy_terms = get_categories(
			array(
				'taxonomy' => 'jobs-categories',
				'orderby' => 'name',
				'order' => 'ASC',
				'hide_empty' => false,
			)
		);

		$categories = [];
		foreach ($taxonomy_terms as $category) {
			$categories[$category->slug] = $category->name;
		}
		$repeater->add_control(
			'category',
			[
				'label' => esc_html__('Categories', 'jobportal-framework'),
				'type' => Controls_Manager::SELECT,
				'options' => $categories,
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'selected_icon',
			[
				'label' => esc_html__('Icon', 'jobportal-framework'),
				'type' => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'default' => [
					'value' => 'fas fa-star',
					'library' => 'fa-solid',
				],
			]
		);

		$repeater->add_control('image', [
			'label'   => esc_html__('Choose Image', 'jobportal-framework'),
			'type'    => Controls_Manager::MEDIA,
		]);

		$repeater->add_control(
			'icon_item_color',
			[
				'label' => esc_html__('Text Color', 'jobportal-framework'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .icon-cate' => 'color: {{VALUE}};',
				],
			]
		);

		$repeater->add_control(
			'icon_item_bg_color',
			[
				'label' => esc_html__('Background Color', 'jobportal-framework'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .icon-cate' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'categories_list',
			[
				'label' => '',
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'text' => esc_html__('Category #1', 'jobportal-framework'),
					],
					[
						'text' => esc_html__('Category #2', 'jobportal-framework'),
					],
					[
						'text' => esc_html__('Category #3', 'jobportal-framework'),
					],
				],
				//                'title_field' => '{{{ category }}}',
			]
		);

		$this->add_control(
			'show_icon',
			[
				'label' => esc_html__('Show Icon', 'jobportal-framework'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_arrow',
			[
				'label' => esc_html__('Show Arrow', 'jobportal-framework'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'layout' => '02',
				],
			]
		);

		$this->add_control(
			'show_count',
			[
				'label' => esc_html__('Show Count', 'jobportal-framework'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_description',
			[
				'label' => esc_html__('Show Description', 'jobportal-framework'),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'text_style',
			[
				'label' => esc_html__('Text style', 'jobportal-framework'),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'show_slider!' => 'yes',
				],
			]
		);

		$this->add_control(
			'show_list_cate',
			[
				'label' => esc_html__('Show List Categories', 'jobportal-framework'),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'show_slider!' => 'yes',
				],
				'separator'  => 'before',
			]
		);

		$this->add_control('text_cate', [
			'label'       => esc_html__('Text', 'jobportal-framework'),
			'type'        => Controls_Manager::TEXT,
			'default'     => esc_html__('View all categories', 'jobportal-framework'),
			'condition' => [
				'show_list_cate!' => '',
				'show_slider!' => 'yes',
			],
		]);

		$this->add_control('link_cate', [
			'label'     => esc_html__('Link', 'jobportal-framework'),
			'type'      => Controls_Manager::URL,
			'dynamic'   => [
				'active' => true,
			],
			'default'   => [
				'url' => '',
			],
			'condition' => [
				'show_list_cate!' => '',
				'show_slider!' => 'yes',
			],
		]);

		$this->add_control(
			'show_slider',
			[
				'label' => esc_html__('Show Slider', 'jobportal-framework'),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
				'separator'  => 'before',
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label' => esc_html__('Columns', 'jobportal-framework'),
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'elementor-grid%s-',
				'min' => 1,
				'max' => 8,
				'default' => 2,
				'required' => true,
				'device_args' => [
					Controls_Stack::RESPONSIVE_TABLET => [
						'required' => false,
					],
					Controls_Stack::RESPONSIVE_MOBILE => [
						'required' => false,
					],
				],
				'min_affected_device' => [
					Controls_Stack::RESPONSIVE_DESKTOP => Controls_Stack::RESPONSIVE_TABLET,
					Controls_Stack::RESPONSIVE_TABLET => Controls_Stack::RESPONSIVE_TABLET,
				],
				'condition' => [
					'show_slider!' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'column_gap',
			[
				'label' => __('Columns Gap', 'jobportal-framework'),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 30,
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-carousel .list-cate-item' => 'padding-left: calc({{SIZE}}{{UNIT}}/2); padding-right: calc({{SIZE}}{{UNIT}}/2)',
					'{{WRAPPER}} .slick-list' => 'margin-left: calc(-{{SIZE}}{{UNIT}}/2);margin-right: calc(-{{SIZE}}{{UNIT}}/2)',
					'{{WRAPPER}} .elementor-grid' => 'grid-column-gap: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_responsive_control(
			'row_gap',
			[
				'label' => esc_html__('Rows Gap', 'jobportal-framework'),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 30,
				],
				'frontend_available' => true,
				'selectors' => [
					'{{WRAPPER}} .elementor-carousel .list-cate-item' => 'padding-top: calc({{SIZE}}{{UNIT}}/2); padding-bottom: calc({{SIZE}}{{UNIT}}/2)',
					'{{WRAPPER}} .slick-list' => 'margin-top: calc(-{{SIZE}}{{UNIT}}/2);margin-bottom: calc(-{{SIZE}}{{UNIT}}/2)',
					'{{WRAPPER}} .elementor-grid' => 'grid-row-gap: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->end_controls_section();
	}

	private function register_slider_section()
	{
		$this->start_controls_section('slider_section', [
			'label' => esc_html__('Slider', 'jobportal-framework'),
			'tab' => Controls_Manager::TAB_CONTENT,
			'condition' => [
				'show_slider' => 'yes',
			],
		]);

		$slides_to_show = range(1, 10);
		$slides_to_show = array_combine($slides_to_show, $slides_to_show);

		$this->add_control(
			'slides_to_show',
			[
				'label' => esc_html__('Slides to Show', 'jobportal-framework'),
				'type' => Controls_Manager::SELECT,
				'default' => '2',
				'options' => [
					'' => esc_html__('Default', 'jobportal-framework'),
				] + $slides_to_show,
			]
		);

		$this->add_control(
			'slides_to_scroll',
			[
				'label' => esc_html__('Slides to Scroll', 'jobportal-framework'),
				'type' => Controls_Manager::SELECT,
				'description' => esc_html__('Set how many slides are scrolled per swipe.', 'jobportal-framework'),
				'default' => '1',
				'options' => [
					'' => esc_html__('Default', 'jobportal-framework'),
				] + $slides_to_show,
			]
		);

		$this->add_control(
			'slides_to_show_tablet',
			[
				'label' => esc_html__('Slides to Show (Tablet)', 'jobportal-framework'),
				'type' => Controls_Manager::SELECT,
				'default' => '2',
				'options' => [
					'' => esc_html__('Default', 'jobportal-framework'),
				] + $slides_to_show,
			]
		);

		$this->add_control(
			'slides_to_scroll_tablet',
			[
				'label' => esc_html__('Slides to Scroll (Tablet)', 'jobportal-framework'),
				'type' => Controls_Manager::SELECT,
				'description' => esc_html__('Set how many slides are scrolled per swipe on tablet.', 'jobportal-framework'),
				'default' => '1',
				'options' => [
					'' => esc_html__('Default', 'jobportal-framework'),
				] + $slides_to_show,
			]
		);

		$this->add_control(
			'slides_to_show_mobile',
			[
				'label' => esc_html__('Slides to Show (Mobile)', 'jobportal-framework'),
				'type' => Controls_Manager::SELECT,
				'default' => '1',
				'options' => [
					'' => esc_html__('Default', 'jobportal-framework'),
				] + $slides_to_show,
			]
		);

		$this->add_control(
			'slides_to_scroll_mobile',
			[
				'label' => esc_html__('Slides to Scroll (Mobile)', 'jobportal-framework'),
				'type' => Controls_Manager::SELECT,
				'description' => esc_html__('Set how many slides are scrolled per swipe on mobile.', 'jobportal-framework'),
				'default' => '1',
				'options' => [
					'' => esc_html__('Default', 'jobportal-framework'),
				] + $slides_to_show,
			]
		);

		$this->add_control(
			'slides_number_row',
			[
				'label' => esc_html__('Number Row', 'jobportal-framework'),
				'type' => Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 4,
				'default' => 1,
			]
		);

		$this->add_control(
			'navigation',
			[
				'label' => esc_html__('Navigation', 'jobportal-framework'),
				'type' => Controls_Manager::SELECT,
				'default' => 'both',
				'options' => [
					'both' => esc_html__('Arrows and Dots', 'jobportal-framework'),
					'arrows' => esc_html__('Arrows', 'jobportal-framework'),
					'dots' => esc_html__('Dots', 'jobportal-framework'),
					'none' => esc_html__('None', 'jobportal-framework'),
				],
			]
		);

		$this->add_control(
			'center_mode',
			[
				'label' => esc_html__('Center Mode', 'jobportal-framework'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'no',
			]
		);

		$this->add_control(
			'pause_on_hover',
			[
				'label' => esc_html__('Pause on Hover', 'jobportal-framework'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label' => esc_html__('Autoplay', 'jobportal-framework'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'no',
			]
		);

		$this->add_control(
			'autoplay_speed',
			[
				'label' => esc_html__('Autoplay Speed', 'jobportal-framework'),
				'type' => Controls_Manager::NUMBER,
				'default' => 5000,
				'condition' => [
					'autoplay' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .slick-slide-bg' => 'animation-duration: calc({{VALUE}}ms*1.2); transition-duration: calc({{VALUE}}ms)',
				],
			]
		);

		$this->add_control(
			'infinite',
			[
				'label' => esc_html__('Infinite Loop', 'jobportal-framework'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'transition',
			[
				'label' => esc_html__('Transition', 'jobportal-framework'),
				'type' => Controls_Manager::SELECT,
				'default' => 'slide',
				'options' => [
					'slide' => esc_html__('Slide', 'jobportal-framework'),
					'fade' => esc_html__('Fade', 'jobportal-framework'),
				],
			]
		);

		$this->add_control(
			'transition_speed',
			[
				'label' => esc_html__('Transition Speed', 'jobportal-framework') . ' (ms)',
				'type' => Controls_Manager::NUMBER,
				'default' => 500,
			]
		);

		$this->end_controls_section();
	}

	private function register_layout_style_section()
	{
		$this->start_controls_section(
			'section_layout_style',
			[
				'label' => esc_html__('Layout', 'jobportal-framework'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control('content_text_align', [
			'label'        => esc_html__('Text Align', 'jobportal-framework'),
			'type'         => Controls_Manager::CHOOSE,
			'default'      => 'left',
			'options'      => array(
				'left'   => [
					'title' => esc_html__('Left', 'jobportal-framework'),
					'icon'  => 'eicon-text-align-left',
				],
				'center' => [
					'title' => esc_html__('Center', 'jobportal-framework'),
					'icon'  => 'eicon-text-align-center',
				],
				'right'  => [
					'title' => esc_html__('Right', 'jobportal-framework'),
					'icon'  => 'eicon-text-align-right',
				],
			),
			'selectors'    => [
				'{{WRAPPER}} .list-cate-item' => 'text-align: {{VALUE}};',
			],
		]);

		$this->add_control(
			'overlay_color',
			[
				'label' => esc_html__('Overlay Color', 'jobportal-framework'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .has-image .cate-inner:before' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'box_background',
				'label' => esc_html__('Background', 'jobportal-framework'),
				'types' => ['classic', 'gradient'],
				'selector' => '{{WRAPPER}} .cate-inner',
			]
		);

		$this->add_responsive_control('box_padding', [
			'label' => esc_html__('Padding', 'jobportal-framework'),
			'type' => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', '%'],
			'selectors' => [
				'{{WRAPPER}} .cate-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		]);

		$this->add_responsive_control('layout_border_radius', [
			'label' => esc_html__('Border Radius', 'jobportal-framework'),
			'type' => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', '%'],
			'selectors' => [
				'{{WRAPPER}} .jobportal-image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				'{{WRAPPER}} .cate-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		]);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'layout_border',
				'selector' => '{{WRAPPER}} .cate-inner',
			]
		);

		$this->end_controls_section();
	}

	private function register_title_style_section()
	{
		$this->start_controls_section(
			'section_title_style',
			[
				'label' => esc_html__('Title', 'jobportal-framework'),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_spacing',
			[
				'label' => esc_html__('Spacing', 'jobportal-framework'),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .cate-title' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'  => esc_html__('Text Color', 'jobportal-framework'),
				'type'   => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cate-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'label'    => esc_html__('Typography', 'jobportal-framework'),
				'selector' => '{{WRAPPER}} .cate-title',
			]
		);

		$this->end_controls_section();
	}

	private function register_icon_style_section()
	{
		$this->start_controls_section(
			'section_icon_style',
			[
				'label' => esc_html__('Icon', 'jobportal-framework'),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_icon' => 'yes',
				],
			]
		);

		$this->add_control(
			'icon_spacing',
			[
				'label' => esc_html__('Spacing', 'jobportal-framework'),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}}.elementor-position-top .icon-cate' => 'margin-bottom: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}}.elementor-position-left .icon-cate' => 'margin-right: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}}.elementor-position-right .icon-cate' => 'margin-left: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'icon_size',
			[
				'label' => esc_html__('Size', 'jobportal-framework'),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .icon-cate' => 'font-size: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'icon_width',
			[
				'label' => esc_html__('Width', 'jobportal-framework'),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .icon-cate' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'icon_color',
			[
				'label'  => esc_html__('Text Color', 'jobportal-framework'),
				'type'   => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .icon-cate' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'icon_bg_color',
			[
				'label'  => esc_html__('Background Color', 'jobportal-framework'),
				'type'   => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .icon-cate' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	private function register_count_style_section()
	{
		$this->start_controls_section(
			'section_count_style',
			[
				'label' => esc_html__('Count', 'jobportal-framework'),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_count' => 'yes',
				],
			]
		);

		$this->add_control(
			'count_color',
			[
				'label'  => esc_html__('Text Color', 'jobportal-framework'),
				'type'   => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cate-count' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'count_typography',
				'label'    => esc_html__('Typography', 'jobportal-framework'),
				'selector' => '{{WRAPPER}} .cate-count',
			]
		);

		$this->end_controls_section();
	}

	protected function render()
	{
		$is_rtl = is_rtl();
		$direction = $is_rtl ? 'rtl' : 'ltr';
		$settings = $this->get_settings_for_display();

		//Slider
		$show_dots = (in_array($settings['navigation'], ['dots', 'both']));
		$show_arrows = (in_array($settings['navigation'], ['arrows', 'both']));

		// Fallback values for responsive settings
		$slides_to_show = !empty($settings['slides_to_show']) ? absint($settings['slides_to_show']) : 2;
		$slides_to_scroll = !empty($settings['slides_to_scroll']) ? absint($settings['slides_to_scroll']) : 1;
		$slides_to_show_tablet = !empty($settings['slides_to_show_tablet']) ? absint($settings['slides_to_show_tablet']) : $slides_to_show;
		$slides_to_show_mobile = !empty($settings['slides_to_show_mobile']) ? absint($settings['slides_to_show_mobile']) : 1;
		$slides_to_scroll_tablet = !empty($settings['slides_to_scroll_tablet']) ? absint($settings['slides_to_scroll_tablet']) : $slides_to_scroll;
		$slides_to_scroll_mobile = !empty($settings['slides_to_scroll_mobile']) ? absint($settings['slides_to_scroll_mobile']) : 1;

		$slick_options = [
			'slidesToShow' => $slides_to_show,
			'slidesToScroll' => $slides_to_scroll,
			'autoplaySpeed' => isset($settings['autoplay_speed']) ? absint($settings['autoplay_speed']) : 0,
			'autoplay' => ('yes' === $settings['autoplay']) ? true : false,
			'infinite' => ('yes' === $settings['infinite']) ? true : false,
			'pauseOnHover' => ('yes' === $settings['pause_on_hover']) ? true : false,
			'centerMode' => ('yes' === $settings['center_mode']) ? true : false,
			'speed' => absint($settings['transition_speed']),
			'arrows' => $show_arrows ? true : false,
			'dots' => $show_dots ? true : false,
			'rtl' => $is_rtl ? true : false,
			'rows' => absint($settings['slides_number_row']),
			'responsive' => [
				[
					'breakpoint' => 1024,
					'settings' => [
						'slidesToShow' => $slides_to_show_tablet,
						'slidesToScroll' => $slides_to_scroll_tablet,
					]
				],
				[
					'breakpoint' => 767,
					'settings' => [
						'slidesToShow' => $slides_to_show_mobile,
						'slidesToScroll' => $slides_to_scroll_mobile,
					]
				],
				[
					'breakpoint' => 567,
					'settings' => [
						'slidesToShow' => 1,
						'slidesToScroll' => 1,
					]
				]
			]
		];
		$slick_data = wp_json_encode($slick_options);

		if ('fade' === $settings['transition']) {
			$slick_options['fade'] = true;
		}

		$carousel_classes = ['elementor-carousel'];
		$this->add_render_attribute('slides', [
			'class' => $carousel_classes,
			'data-slider_options' => $slick_data,
		]);

		$link_cate = '#';
		if (!empty($settings['link_cate']['url'])) {
			$link_cate = $settings['link_cate']['url'];
		}

?>
		<?php if ($settings['show_slider'] == 'yes') { ?>
			<div class="elementor-slick-slider" dir="<?php echo esc_attr($direction); ?>">
				<div <?php echo $this->get_render_attribute_string('slides'); ?>>
				<?php } else { ?>
					<div class="elementor-grid-jobs" dir="<?php echo esc_attr($direction); ?>">
						<div class="elementor-grid">
						<?php } ?>
						<?php foreach ($settings['categories_list'] as $categorry) {
							$item_id = $categorry['_id'];
							$item_key = 'item_' . $item_id;
							$has_icon = !empty($categorry['icon']);
							if (!$has_icon && !empty($categorry['selected_icon']['value'])) {
								$has_icon = true;
							}
							$migrated = isset($categorry['__fa4_migrated']['selected_icon']);
							$is_new = !isset($categorry['icon']) && Icons_Manager::is_migration_allowed();

							$category_slug = $categorry['category'];
							// Initialize variables to prevent undefined variable errors
							$term_name = '';
							$term_count = 0;
							$term_link = '#';
							$term_des = '';
							$has_valid_category = false;

							if (!empty($category_slug)) {
								$cate = get_term_by('slug', $category_slug, 'jobs-categories');
								if ($cate && !is_wp_error($cate) && !empty($cate->term_id)) {
									$has_valid_category = true;
									$term_name = isset($cate->name) ? $cate->name : '';
									// Use accurate count function that respects post status and package expires
									if (function_exists('jobportal_get_term_post_count_for_sidebar')) {
										$term_count = jobportal_get_term_post_count_for_sidebar($cate->term_id, 'jobs-categories');
									} else {
										// Fallback to default count if function doesn't exist
										$term_count = isset($cate->count) ? intval($cate->count) : 0;
									}
									// Ensure count is always an integer
									$term_count = intval($term_count);

									// Get term link with error handling
									$term_link_result = get_term_link($cate, 'jobs-categories');
									if (!is_wp_error($term_link_result)) {
										$term_link = $term_link_result;
									}

									$term_des = isset($cate->description) ? $cate->description : '';
								}
							}

							// Skip rendering if category doesn't exist
							if (!$has_valid_category) {
								continue;
							}
							$image_style = $has_image = '';
							if ($categorry['image']['url'] !== '') {
								$image_style = $categorry['image']['url'];
								$has_image = ' has-image';
							}
							$this->add_render_attribute($item_key, 'class', array(
								'list-cate-item',
								'jobportal-box' . $has_image,
								'elementor-repeater-item-' . $item_id,
							));
						?>
							<div <?php echo $this->get_render_attribute_string($item_key); ?>>
								<?php if ($settings['layout'] == '02') { ?>
									<div class="jobportal-image">
										<div class="image">
											<img src="<?php echo $image_style; ?>" alt="">
										</div>
									<?php } ?>
									<div class="cate-inner">
										<?php if ($has_icon && $settings['show_icon'] == 'yes') : ?>
											<span class="icon-cate">
												<?php if ($is_new || $migrated) {
													Icons_Manager::render_icon($categorry['selected_icon'], ['aria-hidden' => 'true']);
												} elseif (!empty($categorry['icon'])) {
												?><i <?php echo $this->get_render_attribute_string('i'); ?>></i><?php
																																												} ?>
											<?php endif; ?>
											</span>
											<div class="cate-content">
												<?php if (!empty($term_name)) : ?>
													<h4 class="cate-title"><?php esc_html_e($term_name); ?></h4>
												<?php endif; ?>
												<?php if ($settings['show_count'] == 'yes' && isset($term_count)) : ?>
													<p class="cate-count"><?php echo sprintf( _n( '%s job', '%s jobs', $term_count, 'jobportal-framework' ), number_format_i18n( $term_count ) ); ?></p>
												<?php endif; ?>
												<?php
												if ($settings['show_description'] == 'yes') { ?>
													<div class="cate-des"><?php esc_html_e($term_des); ?></div>
												<?php } ?>
												<?php if ($settings['show_arrow'] == 'yes' && $settings['layout'] == '02') { ?>
													<div class="icon-arrow"><span><?php esc_html_e('Learn more', 'jobportal-framework'); ?></span><i class="fas fa-arrow-right"></i></div>
												<?php } ?>
											</div>
											<a class="jobportal-link-item" href="<?php echo esc_url($term_link) ?>"></a>
									</div>
									<?php if ($settings['layout'] == '02') { ?>
									</div>
								<?php } ?>
							</div>
						<?php }
						?>
						<?php if ($settings['show_list_cate'] !== '' && !empty($settings['text_cate'] && $settings['show_slider'] !== 'yes')) { ?>
							<?php if ($settings['text_style'] === 'yes') :  ?>
						</div>
						<div class="list-cate-item text-style">
							<div class="cate-inner view-cate">
								<a href="<?php echo $link_cate; ?>" class="jobportal-button button-border-bottom">
									<?php esc_html_e($settings['text_cate']) ?>
								</a>
							</div>
						</div>
					<?php else : ?>
						<div class="list-cate-item <?php if ($settings['text_style'] === 'yes') {
																					echo ' text-style';
																				}  ?>">
							<div class="cate-inner view-cate">
								<a href="<?php echo $link_cate; ?>" class="jobportal-button button-border-bottom">
									<?php esc_html_e($settings['text_cate']) ?>
								</a>
							</div>
						</div>
					</div>
				<?php endif; ?>
			<?php } ?>
				</div>
		<?php }
}
