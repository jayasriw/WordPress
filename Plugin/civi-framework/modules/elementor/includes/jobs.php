<?php

namespace Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Plugin;

defined('ABSPATH') || exit;

Plugin::instance()->widgets_manager->register(new Widget_Jobs());

class Widget_Jobs extends Widget_Base
{

	const QUERY_CONTROL_ID = 'query';
	const QUERY_OBJECT_POST = 'post';

	public function get_post_type()
	{
		return 'jobs';
	}

	public function get_name()
	{
		return 'civi-jobs';
	}

	public function get_title()
	{
		return esc_html__('Jobs', 'civi-framework');
	}

	public function get_icon()
	{
		return 'civi-badge eicon-archive-title';
	}

	public function get_keywords()
	{
		return ['jobs', 'carousel'];
	}

	public function get_script_depends()
	{
		return [CIVI_PLUGIN_PREFIX . 'el-jobs-pagination'];
	}

	public function get_style_depends()
	{
		return [CIVI_PLUGIN_PREFIX . 'jobs'];
	}

	protected function register_controls()
	{
		$this->register_layout_section();
		$this->register_query_section();
		$this->register_slider_section();
		$this->register_layout_style_section();
	}

	private function register_layout_section()
	{
		$this->start_controls_section('layout_section', [
			'label' => esc_html__('Layout', 'civi-framework'),
			'tab' => Controls_Manager::TAB_CONTENT,
		]);

		$this->add_control('layout', [
			'label' => esc_html__('Layout', 'civi-framework'),
			'type' => Controls_Manager::SELECT,
			'options' => [
				'layout-list' => esc_html__('Layout List', 'civi-framework'),
				'layout-grid' => esc_html__('Layout Grid', 'civi-framework'),
			],
			'default' => 'layout-list',
		]);

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
					'{{WRAPPER}} .elementor-carousel .jobs-item-inner' => 'padding-left: calc({{SIZE}}{{UNIT}}/2); padding-right: calc({{SIZE}}{{UNIT}}/2)',
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
					'{{WRAPPER}} .elementor-carousel .jobs-item-inner' => 'padding-top: calc({{SIZE}}{{UNIT}}/2); padding-bottom: calc({{SIZE}}{{UNIT}}/2)',
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
		]);

		$this->add_control(
			'enable_pagination',
			[
				'label' => esc_html__('Enable Pagination', 'civi-framework'),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'enable_slider' => '',
				],
			]
		);

		$this->add_control(
			'type_pagination',
			[
				'label' => esc_html__('Type Pagination', 'civi-framework'),
				'type' => Controls_Manager::SELECT,
				'default' => 'number',
				'options' => [
					'number' => esc_html__('Number', 'civi-framework'),
					'loadmore' => esc_html__('Loadmore', 'civi-framework'),
				],
				'condition' => [
					'enable_pagination!' => '',
					'enable_slider' => '',
				],
			]
		);

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
			]
		);

		$taxonomies = array(
			esc_html__("Categories", 'civi-framework') => "jobs-categories",
			esc_html__("Skills", 'civi-framework') => "jobs-skills",
			esc_html__("Type", 'civi-framework') => "jobs-type",
			esc_html__("Location", 'civi-framework') => "jobs-location",
			esc_html__("Career", 'civi-framework') => "jobs-career",
			esc_html__("Experience", 'civi-framework') => "jobs-experience",
		);

		foreach ($taxonomies as $label_taxonomy => $taxonomy) {
			$categories = get_terms([
				'taxonomy' => $taxonomy,
				'hide_empty' => false,
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
				],
			]
		);

		$options_job = [];
		$args_job = array(
			'post_type' => $this->get_post_type(),
			'ignore_sticky_posts' => 1,
			'post_status' => 'publish',
			'meta_query' => array(
				array(
					'relation' => 'OR',
					array(
						'key' => CIVI_METABOX_PREFIX . 'enable_jobs_package_expires',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key' => CIVI_METABOX_PREFIX . 'enable_jobs_package_expires',
						'value' => 0,
						'compare' => '=',
					)
				)
			),
			'posts_per_page' => -1,
		);

		$data_job = new \WP_Query($args_job);
		if ($data_job->have_posts()) {
			while ($data_job->have_posts()) : $data_job->the_post();
				$id    = get_the_id();
				$title = get_the_title($id);
				$options_job[$id] = $title;
			endwhile;
		}
		wp_reset_postdata();

		$this->add_control('include_ids', [
			'label'       => esc_html__('Search & Select', 'civi-framework'),
			'type'        => Controls_Manager::SELECT2,
			'options'     => $options_job,
			'default'     => [],
			'label_block' => true,
			'multiple'    => true,
			'condition' => [
				'type_query' => 'title',
			],
		]);

		$this->end_controls_section();
	}

	private function register_slider_section()
	{
		$this->start_controls_section('slider_section', [
			'label' => esc_html__('Slider', 'civi-framework'),
			'tab' => Controls_Manager::TAB_CONTENT,
			'condition' => [
				'enable_slider' => 'yes',
			],
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
				'selector' => '{{WRAPPER}} .civi-jobs-item',
			]
		);

		$this->add_control('box_padding', [
			'label' => esc_html__('Padding', 'civi-framework'),
			'type' => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', '%'],
			'selectors' => [
				'{{WRAPPER}} .civi-jobs-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		]);

		$this->add_control('layout_border_radius', [
			'label' => esc_html__('Border Radius', 'civi-framework'),
			'type' => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', '%'],
			'selectors' => [
				'{{WRAPPER}} .civi-jobs-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		]);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'layout_border',
				'selector' => '{{WRAPPER}} .civi-jobs-item',
			]
		);

		$this->end_controls_section();
	}

	protected function render()
	{
		$is_rtl = is_rtl();
		$direction = $is_rtl ? 'rtl' : 'ltr';
		$settings = $this->get_settings_for_display();

		$widget_id = $this->get_id();

		$this->add_render_attribute('wrapper', 'class', 'civi-jobs');
		$this->add_render_attribute('wrapper', 'data-widget-id', $widget_id);

		$posts_per_page = !empty($settings['posts_per_page']) ? absint($settings['posts_per_page']) : 6;
		$args = array(
			'posts_per_page' => $posts_per_page,
			'post_type' => 'jobs',
			'ignore_sticky_posts' => 1,
			'post_status' => 'publish',
		);

		// Offset
		if (!empty($settings['enable_pagination'])) {
			$paged_key = 'paged_' . $widget_id; // Unique paged key cho mỗi widget
			$current_page = max(1, get_query_var($paged_key, 1));
			$args['offset'] = ($current_page - 1) * $posts_per_page;
		}

		//Query
		$tax_query = array();
		$meta_query_package = array(
			'relation' => 'OR',
			array(
				'key' => CIVI_METABOX_PREFIX . 'enable_jobs_package_expires',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key' => CIVI_METABOX_PREFIX . 'enable_jobs_package_expires',
				'value' => 0,
				'compare' => '=',
			)
		);

		if (!empty($settings['include_ids']) && $settings['type_query'] === 'title') {
			// Convert to array if string and ensure all values are integers
			$include_ids = is_array($settings['include_ids']) ? $settings['include_ids'] : explode(',', $settings['include_ids']);
			$include_ids = array_map('intval', $include_ids);
			$include_ids = array_filter($include_ids);
			if (!empty($include_ids)) {
				$args['post__in'] = $include_ids;
			}
		}

		if ($settings['type_query'] === 'orderby') {
			if (!empty($settings['orderby'])) {
				if ($settings['orderby'] === 'featured') {
					// Filter only featured jobs and sort by featured then date
					$meta_query = array(
						$meta_query_package,
						array(
							'key' => CIVI_METABOX_PREFIX . 'jobs_featured',
							'value' => 1,
							'type' => 'NUMERIC',
							'compare' => '=',
						)
					);
					$args['meta_key'] = CIVI_METABOX_PREFIX . 'jobs_featured';
					$args['orderby'] = 'meta_value_num date';
					$args['order'] = 'DESC';
				} elseif ($settings['orderby'] === 'oldest') {
					// Featured jobs first, then oldest
					$meta_query = array($meta_query_package);
					$args['meta_key'] = CIVI_METABOX_PREFIX . 'jobs_featured';
					$args['orderby'] = 'meta_value_num date';
					$args['order'] = 'ASC';
				} elseif ($settings['orderby'] === 'newest') {
					// Featured jobs first, then newest
					$meta_query = array($meta_query_package);
					$args['meta_key'] = CIVI_METABOX_PREFIX . 'jobs_featured';
					$args['orderby'] = 'meta_value_num date';
					$args['order'] = 'DESC';
				} elseif ($settings['orderby'] === 'random') {
					$meta_query = array($meta_query_package);
					$args['orderby'] = 'rand';
				} else {
					$meta_query = array($meta_query_package);
				}
			} else {
				// Default: Featured jobs first
				$meta_query = array($meta_query_package);
				$args['meta_key'] = CIVI_METABOX_PREFIX . 'jobs_featured';
				$args['orderby'] = 'meta_value_num date';
				$args['order'] = 'DESC';
			}
		} else {
			// Default for other query types: Featured jobs first
			$meta_query = array($meta_query_package);
			$args['meta_key'] = CIVI_METABOX_PREFIX . 'jobs_featured';
			$args['orderby'] = 'meta_value_num date';
			$args['order'] = 'DESC';
		}

		$filters = array();
		if ($settings['type_query'] === 'taxonomy') {
			$taxonomies = array("jobs-categories", "jobs-skills", "jobs-type", "jobs-location", "jobs-career", "jobs-experience");
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
			if (count($meta_query) > 1) {
				$args['meta_query'] = array(
					'relation' => 'AND',
					$meta_query
				);
			} else {
				$args['meta_query'] = $meta_query;
			}
		}

		$data = new \WP_Query($args);
		$total_post = $data->found_posts;

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
		if ('fade' === $settings['transition']) {
			$slick_options['fade'] = true;
		}

		$slick_data = wp_json_encode($slick_options);

		$carousel_classes = ['elementor-carousel'];
		$this->add_render_attribute('slides', [
			'class' => $carousel_classes,
			'data-slider_options' => $slick_data,
		]);
?>
		<div <?php echo $this->get_render_attribute_string('wrapper') ?>>
			<?php if ($data->have_posts()) { ?>
				<?php if ($settings['enable_slider'] === 'yes') { ?>
					<div class="elementor-slick-slider" dir="<?php echo esc_attr($direction); ?>">
						<div <?php echo $this->get_render_attribute_string('slides'); ?>>
							<?php while ($data->have_posts()) : $data->the_post(); ?>
								<div class="jobs-item-inner">
									<?php civi_get_template('content-jobs.php', array(
										'jobs_layout' => $settings['layout'],
									)); ?>
								</div>
							<?php endwhile; ?>
							<?php wp_reset_postdata(); ?>
						</div>
					</div>
				<?php } else { ?>
					<div class="elementor-grid-jobs" dir="<?php echo esc_attr($direction); ?>">
						<div class="elementor-grid">
							<?php while ($data->have_posts()) : $data->the_post(); ?>
								<?php civi_get_template('content-jobs.php', array(
									'jobs_layout' => $settings['layout'],
								)); ?>
							<?php endwhile; ?>
							<?php wp_reset_postdata(); ?>
						</div>
					</div>
				<?php } ?>
			<?php } else { ?>
				<div class="item-not-found"><?php esc_html_e('No item found', 'civi-framework'); ?></div>
			<?php } ?>

			<input type="hidden" name="widget_id" value="<?php echo esc_attr($widget_id) ?>">
			<input type="hidden" name="layout" value="<?php echo esc_attr($settings['layout']) ?>">
			<input type="hidden" name="item_amount" value="<?php echo esc_attr($posts_per_page); ?>">
			<input type="hidden" name="include_ids" value='<?php echo esc_attr(wp_json_encode($settings['include_ids'])); ?>'>
			<input type="hidden" name="type_query" value="<?php echo esc_attr($settings['type_query']) ?>">
			<input type="hidden" name="orderby" value="<?php echo esc_attr($settings['orderby']) ?>">

			<?php
			if (!empty($settings['enable_pagination'])) {
				$max_num_pages = $data->max_num_pages;
				civi_get_template('global/pagination.php', array(
					'filters' => $filters,
					'max_num_pages' => $max_num_pages,
					'total_post' => $total_post,
					'layout' => $settings['type_pagination'],
					'widget_id' => $widget_id
				));
				wp_reset_postdata();
			}
			?>
		</div>
<?php }
}
