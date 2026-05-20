<?php

namespace Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Plugin;

defined('ABSPATH') || exit;

Plugin::instance()->widgets_manager->register(new Widget_Candidate_Category());

class Widget_Candidate_Category extends Widget_Base
{

	public function get_post_type()
	{
		return 'candidate';
	}

	public function get_name()
	{
		return 'civi-candidate-category';
	}

	public function get_title()
	{
		return esc_html__('Candidate Category', 'civi-framework');
	}

	public function get_icon()
	{
		return 'civi-badge eicon-preferences';
	}

	public function get_keywords()
	{
		return ['candidate', 'category'];
	}

	public function get_style_depends()
	{
		return [CIVI_PLUGIN_PREFIX . 'candidate-category'];
	}

	protected function register_controls()
	{
		$this->register_layout_section();
		$this->register_layout_style_section();
		$this->register_title_style_section();
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
                'layout-01' => esc_html__('Layout 01', 'civi-framework'),
                'layout-02' => esc_html__('Layout 02', 'civi-framework'),
            ],
            'default' => 'layout-01',
        ]);

		$repeater = new Repeater();

		$taxonomy_terms = get_categories(
			array(
				'taxonomy' => 'candidate_categories',
				'orderby' => 'name',
				'order' => 'ASC',
				'hide_empty' => true,
				'parent' => 0,
			)
		);

		$categories = [];
		foreach ($taxonomy_terms as $category) {
			$categories[$category->slug] = $category->name;
		}
		$repeater->add_control(
			'category',
			[
				'label' => esc_html__('Categories', 'civi-framework'),
				'type' => Controls_Manager::SELECT,
				'options' => $categories,
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'selected_icon',
			[
				'label' => esc_html__('Icon', 'civi-framework'),
				'type' => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'default' => [
					'value' => 'fas fa-star',
					'library' => 'fa-solid',
				],
			]
		);

		$repeater->add_control(
			'icon_item_color',
			[
				'label' => esc_html__('Icon Color', 'civi-framework'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .icon-cate i' => 'color: {{VALUE}};',
				],
			]
		);

		$repeater->add_control(
			'icon_item_bg_color',
			[
				'label' => esc_html__('Icon Background Color', 'civi-framework'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .icon-cate:before' => 'background-color: {{VALUE}};',
				],
			]
		);

		$repeater->add_control(
			'item_color',
			[
				'label' => esc_html__('Color', 'civi-framework'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .cate-inner .cate-title' => 'color: {{VALUE}};',
					'{{WRAPPER}} {{CURRENT_ITEM}} .cate-inner .icon-arrow i' => 'color: {{VALUE}};',
				],
			]
		);

		$repeater->add_control(
			'item_bg_color',
			[
				'label' => esc_html__('Background Color', 'civi-framework'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .cate-inner' => 'background-color: {{VALUE}};',
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
						'text' => esc_html__('Category #1', 'civi-framework'),
					],
					[
						'text' => esc_html__('Category #2', 'civi-framework'),
					],
					[
						'text' => esc_html__('Category #3', 'civi-framework'),
					],
				],
			]
		);

		$this->add_control(
			'show_icon',
			[
				'label' => esc_html__('Show Icon', 'civi-framework'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_arrow',
			[
				'label' => esc_html__('Show Arrow', 'civi-framework'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition'	=> array(
					'layout' => 'layout-01'
				),
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label' => esc_html__('Columns', 'civi-framework'),
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
					'{{WRAPPER}} .elementor-carousel .list-cate-item' => 'padding-left: calc({{SIZE}}{{UNIT}}/2); padding-right: calc({{SIZE}}{{UNIT}}/2)',
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
					'{{WRAPPER}} .elementor-carousel .list-cate-item' => 'padding-top: calc({{SIZE}}{{UNIT}}/2); padding-bottom: calc({{SIZE}}{{UNIT}}/2)',
					'{{WRAPPER}} .slick-list' => 'margin-top: calc(-{{SIZE}}{{UNIT}}/2);margin-bottom: calc(-{{SIZE}}{{UNIT}}/2)',
					'{{WRAPPER}} .elementor-grid' => 'grid-row-gap: {{SIZE}}{{UNIT}}',
				],
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

		$this->add_responsive_control('box_padding', [
			'label' => esc_html__('Padding', 'civi-framework'),
			'type' => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', '%'],
			'selectors' => [
				'{{WRAPPER}} .cate-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		]);

		$this->add_responsive_control('layout_border_radius', [
			'label' => esc_html__('Border Radius', 'civi-framework'),
			'type' => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', '%'],
			'selectors' => [
				'{{WRAPPER}} .civi-image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
				'label' => esc_html__('Title', 'civi-framework'),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_spacing',
			[
				'label' => esc_html__('Spacing', 'civi-framework'),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .cate-content' => 'margin-top: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'label'    => esc_html__('Typography', 'civi-framework'),
				'selector' => '{{WRAPPER}} .cate-title',
			]
		);

		$this->end_controls_section();
	}

	protected function render()
	{
		$settings = $this->get_settings_for_display();
?>
	<div class="elementor-grid-candidate <?php echo $settings['layout']; ?>">
		<div class="elementor-grid">
		<?php
		foreach ($settings['categories_list'] as $categorry) {
			$item_id = $categorry['_id'];
			$item_key = 'item_' . $item_id;
			$has_icon = !empty($categorry['icon']);
			if (!$has_icon && !empty($categorry['selected_icon']['value'])) {
				$has_icon = true;
			}
			$migrated = isset($categorry['__fa4_migrated']['selected_icon']);
			$is_new = !isset($categorry['icon']) && Icons_Manager::is_migration_allowed();
			$term_link = '';
			$category_slug = $categorry['category'];
			if (!empty($category_slug)) {
				$cate = get_term_by('slug', $category_slug, 'candidate_categories');
				if ($cate) {
					$term_name = $cate->name;
					$term_count = $cate->count;
					$term_link = get_term_link($cate, 'candidate_categories');
					$term_des = $cate->description;
				}
				$this->add_render_attribute($item_key, 'class', array(
					'list-cate-item',
					'elementor-repeater-item-' . $item_id,
				));
			?>
				<div <?php echo $this->get_render_attribute_string($item_key); ?>>
					<div class="cate-inner">
						<?php if ($has_icon && $settings['show_icon'] == 'yes') : ?>
							<span class="icon-cate">
								<?php
									if ($is_new || $migrated) {
										Icons_Manager::render_icon($categorry['selected_icon'], ['aria-hidden' => 'true']);
									} elseif (!empty($categorry['icon'])) {
								?>
								<i <?php echo $this->get_render_attribute_string('i'); ?>></i>
								<?php
									}
								?>
							</span>
						<?php endif; ?>
						<div class="cate-content">
							<?php if (!empty($term_name)) : ?>
								<h4 class="cate-title"><?php esc_html_e($term_name); ?></h4>
							<?php endif; ?>
							<?php if ($settings['show_arrow'] == 'yes') { ?>
								<div class="icon-arrow"><i class="fas fa-arrow-right"></i></div>
							<?php } ?>
						</div>
						<a class="civi-link-item" href="<?php echo esc_url($term_link) ?>"></a>
					</div>
				</div>
		<?php }
		} ?>
	</div>
	</div>
<?php
	}
}
?>
