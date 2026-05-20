<?php

namespace Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Plugin;

defined('ABSPATH') || exit;

Plugin::instance()->widgets_manager->register(new Widget_Service());

class Widget_Service extends Widget_Base
{

	public function get_post_type()
	{
		return 'service';
	}

	public function get_name()
	{
		return 'civi-service';
	}

	public function get_title()
	{
		return esc_html__('Service', 'civi-framework');
	}

	public function get_icon()
	{
		return 'civi-badge eicon-cogs';
	}

	public function get_keywords()
	{
		return ['service', 'carousel'];
	}

	public function get_script_depends()
	{
		return [CIVI_PLUGIN_PREFIX . 'el-service'];
	}

	public function get_style_depends()
	{
		return [CIVI_PLUGIN_PREFIX . 'service'];
	}

	protected function register_controls()
	{
		$this->register_layout_section();
		$this->register_query_section();
		$this->register_slider_section();
		$this->register_tab_style_section();
		$this->register_layout_style_section();
	}

	private function register_layout_section()
	{
		$this->start_controls_section('layout_section', [
			'label' => esc_html__('Layout', 'civi-framework'),
			'tab' => Controls_Manager::TAB_CONTENT,
		]);

		$this->add_control(
			'enable_tab',
			[
				'label' => esc_html__('Enable Tab', 'civi-framework'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'no',
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'title',
			[
				'label'       => esc_html__('Title', 'civi-framework'),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__('Enter title tab', 'civi-framework'),
			]
		);

		$repeater->add_control(
			'type',
			[
				'label' => esc_html__('Type', 'civi-framework'),
				'type' => Controls_Manager::SELECT,
				'default' => 'featured',
				'options' => array(
					'featured' => esc_html__('Featured', 'civi-framework'),
					'newest' => esc_html__('Newest', 'civi-framework'),
					'top_rate' => esc_html__('Top rate', 'civi-framework'),
				),
				'label_block' => true,
			]
		);

		$this->add_control(
			'service_list',
			[
				'label' => '',
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'title' => esc_html__('Featured', 'civi-framework'),
						'type' => 'featured',
					],
					[
						'title' => esc_html__('Newest', 'civi-framework'),
						'type' => 'newest',
					],
					[
						'title' => esc_html__('Top rate', 'civi-framework'),
						'type' => 'top_rate',
					],
				],
				'condition' => [
					'enable_tab' => 'yes',
				],
			]
		);

		$this->add_control(
			'enable_slider',
			[
				'label' => esc_html__('Enable Slider', 'civi-framework'),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label' => esc_html__('Columns', 'civi-framework'),
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'elementor-grid%s-',
				'min' => 1,
				'max' => 4,
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
					'enable_slider!' => 'yes',
				],
			]
		);

		$this->add_control(
			'posts_per_page',
			[
				'label' => esc_html__('Posts Per Page', 'civi-framework'),
				'type' => Controls_Manager::NUMBER,
				'default' => 6,
			]
		);

		$this->add_responsive_control(
			'column_gap',
			[
				'label' => __('Columns Gap', 'civi-framework'),
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
					'{{WRAPPER}} .elementor-carousel .service-item-inner' => 'padding-left: calc({{SIZE}}{{UNIT}}/2); padding-right: calc({{SIZE}}{{UNIT}}/2)',
					'{{WRAPPER}} .slick-list' => 'margin-left: calc(-{{SIZE}}{{UNIT}}/2);margin-right: calc(-{{SIZE}}{{UNIT}}/2)',
					'{{WRAPPER}} .elementor-grid' => 'grid-column-gap: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_responsive_control(
			'row_gap',
			[
				'label' => esc_html__('Rows Gap', 'civi-framework'),
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
					'{{WRAPPER}} .elementor-carousel .service-item-inner' => 'padding-top: calc({{SIZE}}{{UNIT}}/2); padding-bottom: calc({{SIZE}}{{UNIT}}/2)',
					'{{WRAPPER}} .slick-list' => 'margin-top: calc(-{{SIZE}}{{UNIT}}/2);margin-bottom: calc(-{{SIZE}}{{UNIT}}/2)',
					'{{WRAPPER}} .elementor-grid' => 'grid-row-gap: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->end_controls_section();
	}

	private function register_query_section()
	{
		$this->start_controls_section('query_section', [
			'label' => esc_html__('Query', 'civi-framework'),
			'tab' => Controls_Manager::TAB_CONTENT,
			'condition' => [
				'enable_tab!' => 'yes',
			],
		]);

		$this->add_control(
			'type_query',
			[
				'label' => esc_html__('Filter', 'civi-framework'),
				'type' => Controls_Manager::SELECT,
				'default' => 'orderby',
				'options' => [
					'title' => esc_html__('Title', 'civi-framework'),
					'orderby' => esc_html__('Orderby', 'civi-framework'),
					'taxonomy' => esc_html__('Taxonomy', 'civi-framework'),
				],
				'condition' => [
					'enable_tab!' => 'yes',
				],
			]
		);

		$taxonomies = array(
			esc_html__("Categories", 'civi-framework') => "service-categories",
			esc_html__("Skills", 'civi-framework') => "service-skills",
			esc_html__("Location", 'civi-framework') => "service-location",
			esc_html__("Language", 'civi-framework') => "service-language",
		);

		foreach ($taxonomies as $label_taxonomy => $taxonomy) {
			$categories = get_terms([
				'taxonomy' => $taxonomy,
				'hide_empty' => true,
			]);

			$options = array();
			foreach ($categories as $category) {
				if (!empty($category) && $category->slug != 'uncategorized') {
					$options[$category->slug] = $category->name;
				}
			}

			$this->add_control($taxonomy, [
				'label' => $label_taxonomy,
				'type' => Controls_Manager::SELECT2,
				'options' => $options,
				'default' => [],
				'label_block' => true,
				'multiple' => true,
				'condition' => [
					'type_query' => 'taxonomy',
					'enable_tab!' => 'yes',
				],
			]);
		}

		$this->add_control(
			'orderby',
			[
				'label' => esc_html__('Order By', 'civi-framework'),
				'type' => Controls_Manager::SELECT,
				'default' => 'newest',
				'options' => [
					'featured' => esc_html__('Featured', 'civi-framework'),
					'oldest' => esc_html__('Oldest', 'civi-framework'),
					'newest' => esc_html__('Newest', 'civi-framework'),
					'random' => esc_html__('Random', 'civi-framework'),
				],
				'condition' => [
					'type_query' => 'orderby',
					'enable_tab!' => 'yes',
				],
			]
		);

		$options_service = [];
		$args_service = array(
			'post_type' => $this->get_post_type(),
			'ignore_sticky_posts' => 1,
			'post_status' => 'publish',
		);

		$data_service = new \WP_Query($args_service);
		if ($data_service->have_posts()) {
			while ($data_service->have_posts()) : $data_service->the_post();
				$id = get_the_id();
				$slug = get_post_field('post_name', $id);
				$title = get_the_title($id);
				$options_service[$slug] = $title;
			endwhile;
		}
		wp_reset_postdata();

		$this->add_control('include_ids', [
			'label'       => esc_html__('Search & Select', 'civi-framework'),
			'type'        => Controls_Manager::SELECT2,
			'options'     => $options_service,
			'default'     => [],
			'label_block' => true,
			'multiple'    => true,
			'condition' => [
				'type_query' => 'title',
				'enable_tab!' => 'yes',
			],
		]);

		$this->end_controls_section();
	}

	private function register_slider_section()
	{
		$this->start_controls_section('slider_section', [
			'label' => esc_html__('Slider', 'civi-framework'),
			'tab' => Controls_Manager::TAB_CONTENT,
		]);

		$slides_to_show = range(1, 10);
		$slides_to_show = array_combine($slides_to_show, $slides_to_show);

		$this->add_control(
			'slides_to_show',
			[
				'label' => esc_html__('Slides to Show', 'civi-framework'),
				'type' => Controls_Manager::SELECT,
				'default' => '2',
				'options' => [
					'' => esc_html__('Default', 'civi-framework'),
				] + $slides_to_show,
			]
		);

		$this->add_control(
			'slides_to_scroll',
			[
				'label' => esc_html__('Slides to Scroll', 'civi-framework'),
				'type' => Controls_Manager::SELECT,
				'description' => esc_html__('Set how many slides are scrolled per swipe.', 'civi-framework'),
				'default' => '1',
				'options' => [
					'' => esc_html__('Default', 'civi-framework'),
				] + $slides_to_show,
			]
		);

		$this->add_control(
			'slides_to_show_tablet',
			[
				'label' => esc_html__('Slides to Show (Tablet)', 'civi-framework'),
				'type' => Controls_Manager::SELECT,
				'default' => '2',
				'options' => [
					'' => esc_html__('Default', 'civi-framework'),
				] + $slides_to_show,
			]
		);

		$this->add_control(
			'slides_to_scroll_tablet',
			[
				'label' => esc_html__('Slides to Scroll (Tablet)', 'civi-framework'),
				'type' => Controls_Manager::SELECT,
				'description' => esc_html__('Set how many slides are scrolled per swipe on tablet.', 'civi-framework'),
				'default' => '1',
				'options' => [
					'' => esc_html__('Default', 'civi-framework'),
				] + $slides_to_show,
			]
		);

		$this->add_control(
			'slides_to_show_mobile',
			[
				'label' => esc_html__('Slides to Show (Mobile)', 'civi-framework'),
				'type' => Controls_Manager::SELECT,
				'default' => '1',
				'options' => [
					'' => esc_html__('Default', 'civi-framework'),
				] + $slides_to_show,
			]
		);

		$this->add_control(
			'slides_to_scroll_mobile',
			[
				'label' => esc_html__('Slides to Scroll (Mobile)', 'civi-framework'),
				'type' => Controls_Manager::SELECT,
				'description' => esc_html__('Set how many slides are scrolled per swipe on mobile.', 'civi-framework'),
				'default' => '1',
				'options' => [
					'' => esc_html__('Default', 'civi-framework'),
				] + $slides_to_show,
			]
		);

		$this->add_control(
			'slides_number_row',
			[
				'label' => esc_html__('Number Row', 'civi-framework'),
				'type' => Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 4,
				'default' => 1,
			]
		);

		$this->add_control(
			'navigation',
			[
				'label' => esc_html__('Navigation', 'civi-framework'),
				'type' => Controls_Manager::SELECT,
				'default' => 'both',
				'options' => [
					'both' => esc_html__('Arrows and Dots', 'civi-framework'),
					'arrows' => esc_html__('Arrows', 'civi-framework'),
					'dots' => esc_html__('Dots', 'civi-framework'),
					'none' => esc_html__('None', 'civi-framework'),
				],
			]
		);

		$this->add_control(
			'center_mode',
			[
				'label' => esc_html__('Center Mode', 'civi-framework'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'no',
			]
		);

		$this->add_control(
			'pause_on_hover',
			[
				'label' => esc_html__('Pause on Hover', 'civi-framework'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label' => esc_html__('Autoplay', 'civi-framework'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'no',
			]
		);

		$this->add_control(
			'autoplay_speed',
			[
				'label' => esc_html__('Autoplay Speed', 'civi-framework'),
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
				'label' => esc_html__('Infinite Loop', 'civi-framework'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'transition',
			[
				'label' => esc_html__('Transition', 'civi-framework'),
				'type' => Controls_Manager::SELECT,
				'default' => 'slide',
				'options' => [
					'slide' => esc_html__('Slide', 'civi-framework'),
					'fade' => esc_html__('Fade', 'civi-framework'),
				],
			]
		);

		$this->add_control(
			'transition_speed',
			[
				'label' => esc_html__('Transition Speed', 'civi-framework') . ' (ms)',
				'type' => Controls_Manager::NUMBER,
				'default' => 500,
			]
		);

		$this->end_controls_section();
	}

	private function register_tab_style_section()
	{
		$this->start_controls_section(
			'section_tab_style',
			[
				'label' => esc_html__('Tab', 'civi-framework'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'type',
			[
				'label' => esc_html__('Type', 'civi-framework'),
				'type' => Controls_Manager::SELECT,
				'default' => 'fullfield',
				'options' => [
					'fullfield' => esc_html__('Fullfield', 'civi-framework'),
					'underline' => esc_html__('Underline', 'civi-framework'),
				],
			]
		);

		$this->add_responsive_control('nav_h_align', [
			'label' => esc_html__('Alignment horizontal', 'civi-framework'),
			'type' => Controls_Manager::CHOOSE,
			'options' => array(
				'flex-start' => [
					'title' => esc_html__('Left', 'civi-framework'),
					'icon' => 'eicon-h-align-left',
				],
				'center' => [
					'title' => esc_html__('Center', 'civi-framework'),
					'icon' => ' eicon-h-align-center',
				],
				'flex-end' => [
					'title' => esc_html__('Right', 'civi-framework'),
					'icon' => 'eicon-h-align-right',
				],
			),
			'default' => 'center',
			'selectors' => [
				'{{WRAPPER}} .service-nav' => 'justify-content: {{VALUE}};',
			],
		]);

		$this->add_responsive_control('nav_space', [
			'label'     => esc_html__('Spacing', 'civi-framework'),
			'type'      => Controls_Manager::SLIDER,
			'range'     => [
				'px' => [
					'min' => 0,
					'max' => 200,
				],
			],
			'selectors' => [
				'{{WRAPPER}} .service-nav'       => 'margin: 0 0 {{SIZE}}{{UNIT}} 0;',
			],
		]);

		$this->end_controls_section();
	}

	private function register_layout_style_section()
	{
		$this->start_controls_section(
			'section_layout_style',
			[
				'label' => esc_html__('Layout', 'civi-framework'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'box_background',
				'label' => esc_html__('Background', 'civi-framework'),
				'types' => ['classic', 'gradient'],
				'selector' => '{{WRAPPER}} .civi-service-item',
			]
		);

		$this->add_control('box_padding', [
			'label' => esc_html__('Padding', 'civi-framework'),
			'type' => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', '%'],
			'selectors' => [
				'{{WRAPPER}} .civi-service-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		]);

		$this->add_control('layout_border_radius', [
			'label' => esc_html__('Border Radius', 'civi-framework'),
			'type' => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', '%'],
			'selectors' => [
				'{{WRAPPER}} .civi-service-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		]);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'layout_border',
				'selector' => '{{WRAPPER}} .civi-service-item',
			]
		);

		$this->end_controls_section();
	}

	protected function render()
	{
		$is_rtl = is_rtl();
		$direction = $is_rtl ? 'rtl' : 'ltr';
		$settings = $this->get_settings_for_display();
		$this->add_render_attribute('wrapper', 'class', 'civi-service');
		$args = array(
			'posts_per_page' => $settings['posts_per_page'],
			'post_type' => 'service',
			'ignore_sticky_posts' => 1,
			'post_status' => 'publish',
		);

		//Query
		$tax_query = array();
		$meta_query = array();

		if (!empty($settings['include_ids']) && $settings['type_query'] == 'title') {
			$args['post_name__in'] = $settings['include_ids'];
		}

		if ($settings['type_query'] == 'orderby') {
			if (!empty($settings['orderby'])) {
				if ($settings['orderby'] == 'featured') {
					$meta_query[] = array(
						'key' => CIVI_METABOX_PREFIX . 'service_featured',
						'value' => 1,
						'type' => 'NUMERIC',
						'compare' => '=',
					);
				}
				if ($settings['orderby'] == 'oldest') {
					$args['orderby'] = array(
						'menu_order' => 'DESC',
						'date' => 'ASC',
					);
				}
				if ($settings['orderby'] == 'newest') {
					$args['orderby'] = array(
						'menu_order' => 'ASC',
						'date' => 'DESC',
					);
				}
				if ($settings['orderby'] == 'random') {
					$args['meta_key'] = '';
					$args['orderby'] = 'rand';
					$args['order'] = 'ASC';
				}
			}
		}

		$filters = array();
		if ($settings['type_query'] == 'taxonomy') {
			$taxonomies = array("service-categories", "service-skills", "service-location", "service-language");
			foreach ($taxonomies as $taxonomy) {
				if (!empty($settings[$taxonomy])) {
					$tax_query[] = array(
						'taxonomy' => $taxonomy,
						'field' => 'slug',
						'terms' => $settings[$taxonomy],
					);
					$filters[$taxonomy] = $settings[$taxonomy];
				}
			}
		}

		if (!empty($tax_query)) {
			$args['tax_query'] = array(
				'relation' => 'AND',
				$tax_query
			);
		}

		if (!empty($meta_query)) {
			$args['meta_query'] = array(
				'relation' => 'AND',
				$meta_query
			);
		}

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
			'autoplaySpeed' => (isset($settings['autoplay_speed']) ? absint($settings['autoplay_speed']) : 0),
			'autoplay' => (('yes' === $settings['autoplay']) ? true : false),
			'infinite' => (('yes' === $settings['infinite']) ? true : false),
			'pauseOnHover' => (('yes' === $settings['pause_on_hover']) ? true : false),
			'centerMode' => (('yes' === $settings['center_mode']) ? true : false),
			'speed' => absint($settings['transition_speed']),
			'arrows' => $show_arrows,
			'dots' => $show_dots,
			'rtl' => $is_rtl,
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

?>
		<div <?php echo $this->get_render_attribute_string('wrapper') ?>>
			<?php
			if ($settings['enable_tab'] && $settings['service_list']) {
			?>
				<div class="service-tabs">
					<ul class="service-nav <?php echo $settings['type']; ?>">
						<?php
						$i = 0;
						foreach ($settings['service_list'] as $service) {
							$i++;
						?>
							<li><a href="#st_<?php echo esc_attr__($service['type']); ?>" class="<?php if ($i == 1) {
																																											echo 'active';
																																										} ?>"><?php echo esc_html__($service['title']); ?></a></li>
						<?php } ?>
					</ul>
					<?php
					$j = 0;
					foreach ($settings['service_list'] as $service) {
						$j++;
						if ($service['type'] == 'featured') {
							$meta_query[] = array(
								'key' => CIVI_METABOX_PREFIX . 'service_featured',
								'value' => 1,
								'type' => 'NUMERIC',
								'compare' => '=',
							);
							$args['meta_query'] = array(
								'relation' => 'AND',
								$meta_query
							);
						} elseif ($service['type'] == 'newest') {
							$args['orderby'] = array(
								'menu_order' => 'ASC',
								'date' => 'DESC',
							);
							$args['meta_query'] = array(
								'relation' => 'AND',
							);
						} elseif ($service['type'] == 'top_rate') {
							$args['meta_query'] = array(
								'relation' => 'AND',
							);
							$args['meta_key'] = 'total_point_review';
							$args['orderby'] = 'meta_value_num';
							$args['order'] = 'DESC';
						}
						$data = new \WP_Query($args);
						$total_post = $data->found_posts;
					?>
						<div id="st_<?php echo esc_attr__($service['type']); ?>" class="service-tab-content <?php if ($j == 1) {
																																																	echo 'active';
																																																} ?>">
							<?php if ($data->have_posts()) { ?>
								<?php if ($settings['enable_slider'] == 'yes') { ?>
									<div class="elementor-slick-slider" dir="<?php echo esc_attr($direction); ?>">
										<div <?php echo $this->get_render_attribute_string('slides'); ?>>
											<?php while ($data->have_posts()) : $data->the_post(); ?>
												<div class="service-item-inner">
													<?php civi_get_template('content-service.php', array(
														'service_layout' => 'layout-grid',
													)); ?>
												</div>
											<?php endwhile; ?>
										</div>
									</div>
								<?php } else { ?>
									<div class="elementor-grid-jobs">
										<div class="elementor-grid">
											<?php while ($data->have_posts()) : $data->the_post(); ?>
												<?php civi_get_template('content-service.php', array(
													'service_layout' => 'layout-grid',
												)); ?>
											<?php endwhile; ?>
										</div>
									</div>
								<?php } ?>
							<?php } else { ?>
								<div class="item-not-found"><?php esc_html_e('No item found', 'civi-framework'); ?></div>
							<?php } ?>
						</div>
					<?php } ?>
				</div>
			<?php
			} else {
				$data = new \WP_Query($args);
				$total_post = $data->found_posts;
			?>
				<?php if ($data->have_posts()) { ?>
					<?php if ($settings['enable_slider'] == 'yes') { ?>
						<div class="elementor-slick-slider" dir="<?php echo esc_attr($direction); ?>">
							<div <?php echo $this->get_render_attribute_string('slides'); ?>>
								<?php while ($data->have_posts()) : $data->the_post(); ?>
									<div class="service-item-inner">
										<?php civi_get_template('content-service.php', array(
											'service_layout' => 'layout-grid',
										)); ?>
									</div>
								<?php endwhile; ?>
							</div>
						</div>
					<?php } else { ?>
						<div class="elementor-grid-jobs">
							<div class="elementor-grid">
								<?php while ($data->have_posts()) : $data->the_post(); ?>
									<?php civi_get_template('content-service.php', array(
										'service_layout' => 'layout-grid',
									)); ?>
								<?php endwhile; ?>
							</div>
						</div>
					<?php } ?>
				<?php } else { ?>
					<div class="item-not-found"><?php esc_html_e('No item found', 'civi-framework'); ?></div>
				<?php } ?>
			<?php } ?>
			<input type="hidden" name="layout" value="layout-grid">
			<input type="hidden" name="item_amount" value="<?php echo $settings['posts_per_page'] ?>">
			<input type="hidden" name="include_ids" value='<?php echo json_encode($settings['include_ids']) ?>'>
			<input type="hidden" name="type_query" value="<?php echo $settings['type_query'] ?>">
			<input type="hidden" name="orderby" value="<?php echo $settings['orderby'] ?>">
		</div>
<?php }
}
