<?php

namespace Elementor;

use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Plugin;

defined('ABSPATH') || exit;

Plugin::instance()->widgets_manager->register(new Widget_Search_Vertical());

class Widget_Search_Vertical extends Widget_Base
{

	public function get_name()
	{
		return 'jobportal-search-vertical';
	}

	public function get_title()
	{
		return esc_html__('Search Vertical PostTypes', 'jobportal-framework');
	}

	public function get_icon()
	{
		return 'jobportal-badge eicon-search-results';
	}

	public function get_keywords()
	{
		return ['jobs', 'companies', 'candidate', 'search'];
	}

	public function get_script_depends()
	{
		return [
			JOBPORTAL_PLUGIN_PREFIX . 'search-vertical',
			JOBPORTAL_PLUGIN_PREFIX . 'search-location',
			'jquery-ui-autocomplete'
		];
	}

	public function get_style_depends()
	{
		return [JOBPORTAL_PLUGIN_PREFIX . 'search-vertical'];
	}

	protected function register_controls()
	{
		$this->add_layout_section();
		$this->add_layout_jobs_section();
		$this->add_layout_companies_section();
		$this->add_layout_candidates_section();
		$this->add_layout_service_section();
		$this->add_layout_style_section();
		$this->add_nav_style_section();
	}

	private function add_layout_section()
	{
		$this->start_controls_section('layout_section', [
			'label' => esc_html__('Layout', 'jobportal-framework'),
			'tab'   => Controls_Manager::TAB_CONTENT,
		]);

		$this->add_control('layout', [
			'label'        => esc_html__('Layout', 'jobportal-framework'),
			'type'         => Controls_Manager::SELECT,
			'options'      => [

				'01' => esc_html__('Layout 01', 'jobportal-framework'),
			],
			'default'      => '01',
			'prefix_class' => 'jobportal-search-vertical-layout-',
		]);

		$this->add_control(
			'show_jobs',
			[
				'label'   => esc_html__('Show Jobs', 'jobportal-framework'),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_company',
			[
				'label'   => esc_html__('Show Company', 'jobportal-framework'),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_candidate',
			[
				'label'   => esc_html__('Show Candidate', 'jobportal-framework'),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_service',
			[
				'label'   => esc_html__('Show Service', 'jobportal-framework'),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_redirect',
			[
				'label'   => esc_html__('Show ajax page redirect', 'jobportal-framework'),
				'type'    => Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control('link_redirect', [
			'label'     => esc_html__('Link', 'jobportal-framework'),
			'type'      => Controls_Manager::URL,
			'dynamic'   => [
				'active' => true,
			],
			'default'   => [
				'url' => '',
			],
			'condition' => [
				'show_redirect' => 'yes',
			],
		]);

		$this->end_controls_section();
	}

	private function add_layout_jobs_section()
	{
		$this->start_controls_section('layout_jobs', [
			'label'     => esc_html__('Jobs', 'jobportal-framework'),
			'tab'       => Controls_Manager::TAB_CONTENT,
			'condition' => [
				'show_jobs' => 'yes',
			],
		]);

		$taxonomies_jobs = array(
			"Categories" => "jobs-categories",
			"Skills"     => "jobs-skills",
			"Type"       => "jobs-type",
			"Location"   => "jobs-location",
			"Career"     => "jobs-career",
			"Experience" => "jobs-experience",
		);

		foreach ($taxonomies_jobs as $label_jobs => $jobs) {
			$this->add_control(
				'show_' . $jobs,
				[
					'label'   => esc_html__('Show ' . $label_jobs, 'jobportal-framework'),
					'type'    => Controls_Manager::SWITCHER,
					'default' => '',
				]
			);
			$this->add_control('icon_' . $jobs, [
				'label'     => esc_html__('Icon ' . $label_jobs, 'jobportal-framework'),
				'type'      => Controls_Manager::ICONS,
				'default'   => [],
				'condition' => [
					'show_' . $jobs => 'yes',
				],
			]);
		};

		$this->end_controls_section();
	}

	private function add_layout_companies_section()
	{
		$this->start_controls_section('layout_company', [
			'label'     => esc_html__('Companies', 'jobportal-framework'),
			'tab'       => Controls_Manager::TAB_CONTENT,
			'condition' => [
				'show_company' => 'yes',
			],
		]);

		$taxonomies_company = array(
			"Categories" => "company-categories",
			"Location"   => "company-location",
			"Size"       => "company-size",
		);

		foreach ($taxonomies_company as $label_company => $company) {
			$this->add_control(
				'show_' . $company,
				[
					'label'   => esc_html__('Show ' . $label_company, 'jobportal-framework'),
					'type'    => Controls_Manager::SWITCHER,
					'default' => '',
				]
			);
			$this->add_control('icon_' . $company, [
				'label'     => esc_html__('Icon ' . $label_company, 'jobportal-framework'),
				'type'      => Controls_Manager::ICONS,
				'default'   => [],
				'condition' => [
					'show_' . $company => 'yes',
				],
			]);
		};

		$this->end_controls_section();
	}

	private function add_layout_candidates_section()
	{
		$this->start_controls_section('layout_candidate', [
			'label'     => esc_html__('Candidates', 'jobportal-framework'),
			'tab'       => Controls_Manager::TAB_CONTENT,
			'condition' => [
				'show_candidate' => 'yes',
			],
		]);

		$taxonomies_candidate = array(
			"Categories"    => "candidate_categories",
			"Ages"          => "candidate_ages",
			"Languages"     => "candidate_languages",
			"Qualification" => "candidate_qualification",
			"Yoe"           => "candidate_yoe",
			"Education"     => "candidate_education_levels",
			"Skills"        => "candidate_skills",
			"Locations"     => "candidate_locations",
		);

		foreach ($taxonomies_candidate as $label_candidate => $candidate) {
			$this->add_control(
				'show_' . $candidate,
				[
					'label'   => esc_html__('Show ' . $label_candidate, 'jobportal-framework'),
					'type'    => Controls_Manager::SWITCHER,
					'default' => '',
				]
			);
			$this->add_control('icon_' . $candidate, [
				'label'     => esc_html__('Icon ' . $label_candidate, 'jobportal-framework'),
				'type'      => Controls_Manager::ICONS,
				'default'   => [],
				'condition' => [
					'show_' . $candidate => 'yes',
				],
			]);
		};

		$this->end_controls_section();
	}

	private function add_layout_service_section()
	{
		$this->start_controls_section('layout_service', [
			'label'     => esc_html__('Service', 'jobportal-framework'),
			'tab'       => Controls_Manager::TAB_CONTENT,
			'condition' => [
				'show_service' => 'yes',
			],
		]);

		$taxonomies_service = array(
			"Categories" => "service-categories",
			"Skills"     => "service-skills",
			"Location"   => "service-location",
			"Language"   => "service-language",
		);

		foreach ($taxonomies_service as $label_service => $service) {
			$this->add_control(
				'show_' . $service,
				[
					'label'   => esc_html__('Show ' . $label_service, 'jobportal-framework'),
					'type'    => Controls_Manager::SWITCHER,
					'default' => '',
				]
			);
			$this->add_control('icon_' . $service, [
				'label'     => esc_html__('Icon ' . $label_service, 'jobportal-framework'),
				'type'      => Controls_Manager::ICONS,
				'default'   => [],
				'condition' => [
					'show_' . $service => 'yes',
				],
			]);
		};

		$this->end_controls_section();
	}

	private function add_layout_style_section()
	{
		$this->start_controls_section('layout_style_section', [
			'label' => esc_html__('Layout', 'jobportal-framework'),
			'tab'   => Controls_Manager::TAB_STYLE,
		]);

		$this->add_responsive_control(
			'box_max-width',
			[
				'label'     => esc_html__('Max Width', 'jobportal-framework'),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 1000,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .jobportal-search-vertical' => 'max-width: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_responsive_control('text_align', [
			'label'                => esc_html__('Alignment', 'jobportal-framework'),
			'type'                 => Controls_Manager::CHOOSE,
			'options'              => array(
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
			'selectors_dictionary' => [
				'left'   => 'margin-right: auto',
				'center' => 'margin: 0 auto',
				'right'  => 'margin-left: auto',
			],
			'selectors'            => [
				'{{WRAPPER}} .jobportal-search-vertical' => '{{VALUE}}',
			],
		]);

		$this->add_control('box_border_radius', [
			'label'      => esc_html__('Border Radius', 'jobportal-framework'),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', '%'],
			'selectors'  => [
				'{{WRAPPER}} .jobportal-search-vertical' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		]);

		$this->add_control('box_padding', [
			'label'      => esc_html__('Padding Box', 'jobportal-framework'),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', '%'],
			'selectors'  => [
				'{{WRAPPER}} .jobportal-search-vertical' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		]);

		$this->end_controls_section();
	}

	private function add_nav_style_section()
	{
		$this->start_controls_section('nav_style_section', [
			'label' => esc_html__('Nav', 'jobportal-framework'),
			'tab'   => Controls_Manager::TAB_STYLE,
		]);

		$this->add_responsive_control('nav_text_align', [
			'label'     => esc_html__('Alignment', 'jobportal-framework'),
			'type'      => Controls_Manager::CHOOSE,
			'options'   => array(
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
			'default'   => '',
			'selectors' => [
				'{{WRAPPER}} .tab-dashboard' => 'text-align: {{VALUE}};',
			],
		]);

		$this->add_control(
			'nav_spacing',
			[
				'label'     => esc_html__('Spacing', 'jobportal-framework'),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .tab-list' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_group_control(Group_Control_Typography::get_type(), [
			'name'     => 'nav_title',
			'selector' => '{{WRAPPER}} .tab-item a',
		]);

		$this->add_control(
			'nav_color',
			[
				'label'     => esc_html__('Text Color', 'jobportal-framework'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tab-item a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'nav_color_active',
			[
				'label'     => esc_html__('Active Color', 'jobportal-framework'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tab-dashboard .tab-item.active a' => 'color: {{VALUE}};',
					'{{WRAPPER}} .tab-dashboard .tab-item:before'   => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render()
	{
		$settings = $this->get_settings_for_display();
		$this->add_render_attribute('wrapper', 'class', 'jobportal-search-vertical');
		if ($settings['show_jobs'] == 'yes') {
			$taxonomy_key_jobs       = 'jobs-skills';
			$search_placeholder_jobs = esc_attr__('Jobs title or keywords', 'jobportal-framework');
			$taxonomies_field_jobs   = array(
				esc_html__('Locations', 'jobportal-framework')  => "jobs-location",
				esc_html__('Categories', 'jobportal-framework') => "jobs-categories",
				esc_html__('Skills', 'jobportal-framework')     => "jobs-skills",
				esc_html__('Type', 'jobportal-framework')       => "jobs-type",
				esc_html__('Career', 'jobportal-framework')     => "jobs-career",
				esc_html__('Experience', 'jobportal-framework') => "jobs-experience",
			);
		}
		if ($settings['show_company'] == 'yes') {
			$taxonomy_key_company       = 'company-categories';
			$search_placeholder_company = esc_attr__('Company title or keywords', 'jobportal-framework');
			$taxonomies_field_company   = array(
				esc_html__('Locations', 'jobportal-framework')  => "company-location",
				esc_html__('Categories', 'jobportal-framework') => "company-categories",
				esc_html__('Size', 'jobportal-framework')       => "company-size",
			);
		}
		if ($settings['show_candidate'] == 'yes') {
			$taxonomy_key_candidate       = 'candidate_skills';
			$search_placeholder_candidate = esc_attr__('Candidate title or keywords', 'jobportal-framework');
			$taxonomies_field_candidate   = array(
				esc_html__('Locations', 'jobportal-framework')     => "candidate_locations",
				esc_html__('Categories', 'jobportal-framework')    => "candidate_categories",
				esc_html__('Ages', 'jobportal-framework')          => "candidate_ages",
				esc_html__('Languages', 'jobportal-framework')     => "candidate_languages",
				esc_html__('Qualification', 'jobportal-framework') => "candidate_qualification",
				esc_html__('Yoe', 'jobportal-framework')           => "candidate_yoe",
				esc_html__('Education', 'jobportal-framework')     => "candidate_education_levels",
				esc_html__('Skills', 'jobportal-framework')        => "candidate_skills",
			);
		}
		if ($settings['show_service'] == 'yes') {
			$taxonomy_key_service       = 'service-skills';
			$search_placeholder_service = esc_attr__('Service title or keywords', 'jobportal-framework');
			$taxonomies_field_service   = array(
				esc_html__('Locations', 'jobportal-framework')  => "service-location",
				esc_html__('Categories', 'jobportal-framework') => "service-categories",
				esc_html__('Skills', 'jobportal-framework')     => "service-skills",
				esc_html__('Language', 'jobportal-framework')   => "service-language",
			);
		}
?>
		<div <?php echo $this->get_render_attribute_string('wrapper') ?>>
			<div class="tab-post-type tab-dashboard">
				<ul class="tab-list">
					<?php if ($settings['show_jobs'] == 'yes') { ?>
						<li class="tab-item tab-jobs-item"><a
								href="#tab-jobs"><?php esc_html_e('For Jobs', 'jobportal-framework'); ?></a></li>
					<?php } ?>
					<?php if ($settings['show_company'] == 'yes') { ?>
						<li class="tab-item tab-company-item"><a
								href="#tab-company"><?php esc_html_e('For Companies', 'jobportal-framework'); ?></a>
						</li>
					<?php } ?>
					<?php if ($settings['show_candidate'] == 'yes') { ?>
						<li class="tab-item tab-candidate-item"><a
								href="#tab-candidate"><?php esc_html_e('For Candidates', 'jobportal-framework'); ?></a>
						</li>
					<?php } ?>
					<?php if ($settings['show_service'] == 'yes') { ?>
						<li class="tab-item tab-service-item"><a
								href="#tab-service"><?php esc_html_e('For Services', 'jobportal-framework'); ?></a>
						</li>
					<?php } ?>
				</ul>
				<div class="tab-content">
					<?php if ($settings['show_jobs'] == 'yes') { ?>
						<div class="tab-info" id="tab-jobs">
							<?php $this->print_content_form('jobs', $settings, $taxonomy_key_jobs, $search_placeholder_jobs, $taxonomies_field_jobs); ?>
						</div>
					<?php } ?>
					<?php if ($settings['show_company'] == 'yes') { ?>
						<div class="tab-info" id="tab-company">
							<?php $this->print_content_form('company', $settings, $taxonomy_key_company, $search_placeholder_company, $taxonomies_field_company); ?>
						</div>
					<?php } ?>
					<?php if ($settings['show_candidate'] == 'yes') { ?>
						<div class="tab-info" id="tab-candidate">
							<?php $this->print_content_form('candidate', $settings, $taxonomy_key_candidate, $search_placeholder_candidate, $taxonomies_field_candidate); ?>
						</div>
					<?php } ?>
					<?php if ($settings['show_service'] == 'yes') { ?>
						<div class="tab-info" id="tab-service">
							<?php $this->print_content_form('service', $settings, $taxonomy_key_service, $search_placeholder_service, $taxonomies_field_service); ?>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
	<?php }

	private function print_content_form($post_type, array $settings, $taxonomy_key, $search_placeholder, $taxonomies_field)
	{
		if ($settings['show_redirect'] == 'yes' && !empty($settings['link_redirect']['url'])) {
			$link_redirect = trailingslashit($settings['link_redirect']['url']);
		} else {
			$archive_link = get_post_type_archive_link($post_type);
			if (!empty($archive_link)) {
				$link_redirect = trailingslashit($archive_link);
			} else {
				$link_redirect = trailingslashit(home_url('/'));
			}
		}
		$link_redirect = apply_filters('jobportal_search_vertical_redirect_url', $link_redirect, $settings, $this);
	?>
		<form action="<?php echo esc_url($link_redirect); ?>" method="get" class="form-search-vertical">
			<div class="search-vertical-inner">
				<?php $key_name     = array();
				$taxonomy_post_type = get_terms(
					array(
						'taxonomy' => $taxonomy_key,
						'orderby' => 'name',
						'order' => 'ASC',
						'hide_empty' => false,
					)
				);
				if (! empty($taxonomy_post_type)) {
					foreach ($taxonomy_post_type as $term) {
						$key_name[] = $term->name;
					}
				}
				$post_type_keyword = json_encode($key_name);
				?>
				<div class="form-group">
					<input class="search-vertical-<?php echo $post_type; ?> jobportal-ajax-ui" data-key='<?php echo $post_type_keyword ?>'
						type="text" name="s" data-taxonomy="<?php echo $taxonomy_key; ?>"
						placeholder="<?php echo $search_placeholder; ?>" autocomplete="off">
					<span class="btn-filter-search"><i class="far fa-search"></i></span>
					<span class="ui-autocomplete-spinner" style="display:none;">
						<i class="fa fa-spinner fa-spin"></i>
					</span>
				</div>

				<?php foreach ($taxonomies_field as $label_field => $field) {
					if ($settings['show_' . $field]) {
						if ($field == 'jobs-location' || $field == 'company-location' || $field == 'candidate_locations' || $field == 'service-location') {
							$taxonomy = $field;
							$term_count = wp_count_terms($taxonomy, ['hide_empty' => false]);
				?>
					<div class="form-group jobportal-form-location">
						<?php if ($term_count <= 150): ?>
							<input class="input-search-location jobportal-ajax-ui" type="text" name="<?php echo $field ?>"
								placeholder="<?php esc_attr_e('All Cities', 'jobportal-framework') ?>" autocomplete="off" data-taxonomy="<?php echo esc_attr($taxonomy); ?>"
								value="<?php
								if (isset($_GET[$field])) {
									$location_value = $_GET[$field];
									if (is_array($location_value)) {
										$location_value = array_filter($location_value);
										if (!empty($location_value)) {
											$term = get_term($location_value[0], $taxonomy);
											if ($term && !is_wp_error($term)) {
												echo esc_attr($term->name);
											}
										}
									} elseif (!empty($location_value)) {
										if (is_numeric($location_value)) {
											$term = get_term($location_value, $taxonomy);
											if ($term && !is_wp_error($term)) {
												echo esc_attr($term->name);
											}
										} else {
											echo esc_attr($location_value);
										}
									}
								}
								?>">
							<select class="jobportal-select2 hide">
								<option value=""><?php esc_html_e('All Cities', 'jobportal-framework') ?></option>
								<?php jobportal_get_taxonomy($field, true, false); ?>
							</select>
						<?php else: ?>
							<input class="input-search-location jobportal-ajax-ui" type="text" name="<?php echo $field ?>"
								placeholder="<?php esc_attr_e('All Cities', 'jobportal-framework') ?>" autocomplete="off" data-taxonomy="<?php echo esc_attr($taxonomy); ?>"
								value="<?php
								if (isset($_GET[$field])) {
									$location_value = $_GET[$field];
									if (is_array($location_value)) {
										$location_value = array_filter($location_value);
										if (!empty($location_value)) {
											$term = get_term($location_value[0], $taxonomy);
											if ($term && !is_wp_error($term)) {
												echo esc_attr($term->name);
											}
										}
									} elseif (!empty($location_value)) {
										if (is_numeric($location_value)) {
											$term = get_term($location_value, $taxonomy);
											if ($term && !is_wp_error($term)) {
												echo esc_attr($term->name);
											}
										} else {
											echo esc_attr($location_value);
										}
									}
								}
								?>">
							<select class="jobportal-ajax-select2 hide" data-taxonomy="<?php echo esc_attr($taxonomy); ?>" data-placeholder="<?php esc_attr_e('Select location', 'jobportal-framework'); ?>">
									<?php
									$selected_location = isset($_GET[$field]) ? sanitize_text_field($_GET[$field]) : '';
									if (!empty($selected_location)) {
										$location_ids = explode(',', $selected_location);
										foreach ($location_ids as $location_id) {
											$term = get_term($location_id, $taxonomy);
											if (!is_wp_error($term) && $term) {
												echo '<option value="' . esc_attr($term->term_id) . '" selected>' . esc_html($term->name) . '</option>';
											}
										}
									}
									?>
								</select>
							<?php endif; ?>
								<span class="icon-location">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="none"
										xmlns="http://www.w3.org/2000/svg">
										<g clip-path="url(#clip0_8969_23265)">
											<path d="M13 1L13.001 4.062C14.7632 4.28479 16.4013 5.08743 17.6572 6.34351C18.9131 7.5996 19.7155 9.23775 19.938 11H23V13L19.938 13.001C19.7153 14.7631 18.9128 16.401 17.6569 17.6569C16.401 18.9128 14.7631 19.7153 13.001 19.938L13 23H11V19.938C9.23775 19.7155 7.5996 18.9131 6.34351 17.6572C5.08743 16.4013 4.28479 14.7632 4.062 13.001L1 13V11H4.062C4.28459 9.23761 5.08713 7.59934 6.34324 6.34324C7.59934 5.08713 9.23761 4.28459 11 4.062V1H13ZM12 6C10.4087 6 8.88258 6.63214 7.75736 7.75736C6.63214 8.88258 6 10.4087 6 12C6 13.5913 6.63214 15.1174 7.75736 16.2426C8.88258 17.3679 10.4087 18 12 18C13.5913 18 15.1174 17.3679 16.2426 16.2426C17.3679 15.1174 18 13.5913 18 12C18 10.4087 17.3679 8.88258 16.2426 7.75736C15.1174 6.63214 13.5913 6 12 6ZM12 10C12.5304 10 13.0391 10.2107 13.4142 10.5858C13.7893 10.9609 14 11.4696 14 12C14 12.5304 13.7893 13.0391 13.4142 13.4142C13.0391 13.7893 12.5304 14 12 14C11.4696 14 10.9609 13.7893 10.5858 13.4142C10.2107 13.0391 10 12.5304 10 12C10 11.4696 10.2107 10.9609 10.5858 10.5858C10.9609 10.2107 11.4696 10 12 10Z"
												fill="#999999" />
										</g>
										<defs>
											<clipPath id="clip0_8969_23265">
												<rect width="24" height="24" fill="white" />
											</clipPath>
										</defs>
									</svg>
								</span>
								<span class="icon-arrow">
									<i class="fal fa-angle-down"></i>
								</span>
								<span class="ui-autocomplete-spinner" style="display:none;">
									<i class="fa fa-spinner fa-spin"></i>
								</span>
							</div>
						<?php } else {
							$taxonomy = $field;
							$term_count = wp_count_terms($taxonomy, ['hide_empty' => false]);
						?>
							<div class="form-group">
								<?php Icons_Manager::render_icon($settings['icon_' . $field], ['aria-hidden' => 'true']); ?>
								<?php if ($term_count <= 150): ?>
									<select name="<?php echo $field ?>" class="jobportal-select2">
										<option value=""><?php echo sprintf(esc_html__('All %s', 'jobportal-framework'), $label_field) ?></option>
										<?php jobportal_get_taxonomy($field, true, false); ?>
									</select>
								<?php else: ?>
									<select name="<?php echo $field ?>" class="jobportal-ajax-select2" data-taxonomy="<?php echo esc_attr($taxonomy); ?>" data-placeholder="<?php echo sprintf(esc_attr__('Select %s', 'jobportal-framework'), $label_field); ?>">
										<?php
										$selected_value = isset($_GET[$field]) ? sanitize_text_field($_GET[$field]) : '';
										if (!empty($selected_value)) {
											$term_ids = explode(',', $selected_value);
											foreach ($term_ids as $term_id) {
												$term = get_term($term_id, $taxonomy);
												if (!is_wp_error($term) && $term) {
													echo '<option value="' . esc_attr($term->term_id) . '" selected>' . esc_html($term->name) . '</option>';
												}
											}
										}
										?>
									</select>
								<?php endif; ?>
							</div>
						<?php } ?>
				<?php }
				} ?>
				<div class="form-group">
					<button type="submit" class="btn-search-vertical jobportal-button">
						<?php esc_html_e('Search', 'jobportal-framework') ?>
					</button>
				</div>
			</div>
			<?php if ($settings['show_redirect'] !== 'yes') { ?>
				<input type="hidden" name="post_type" class="post-type" value="<?php echo $post_type; ?>">
			<?php } ?>
		</form>
<?php }
}
