<?php

namespace Elementor;

use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Plugin;

defined('ABSPATH') || exit;

Plugin::instance()->widgets_manager->register(new Widget_Advanced_Archive());

class Widget_Advanced_Archive extends Widget_Base
{

    public function get_name()
    {
        return 'jobportal-advanced-archive';
    }

    public function get_title()
    {
        return esc_html__('Advanced Archive', 'jobportal-framework');
    }

    public function get_icon()
    {
        return 'jobportal-badge eicon-archive-posts';
    }

    public function get_keywords()
    {
        return ['jobs', 'companies', 'candidate'];
    }

    public function get_style_depends()
    {
        return [JOBPORTAL_PLUGIN_PREFIX . 'advanced-archive'];
    }

    protected function register_controls()
    {
        $this->add_content_section();
        $this->add_sidebar_section();
    }

    private function add_content_section()
    {
        $this->start_controls_section('content_section', [
            'label' => esc_html__('Content', 'jobportal-framework'),
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);

        $options =  array(
            'jobs' => esc_html__('Jobs', 'jobportal-framework'),
            'company' => esc_html__('Companies', 'jobportal-framework'),
            'candidate' => esc_html__('Candidates', 'jobportal-framework'),
        );

        if(jobportal_get_option('enable_post_type_service') === '1'){
            $options['service'] = esc_html__('Services', 'jobportal-framework');
        }

        $this->add_control('post_type', [
            'label' => esc_html__('Post Type', 'jobportal-framework'),
            'type' => Controls_Manager::SELECT,
            'options' => $options,
            'default' => 'jobs',
        ]);

        $this->add_control(
            'color_featured',
            [
                'label' => esc_html__('Color Featured ', 'jobportal-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .jobportal-jobs-featured ' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .jobportal-candidate-featured ' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .jobportal-company-featured ' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function add_sidebar_section()
    {
        $this->start_controls_section('sidebar_section', [
            'label' => esc_html__('Sidebar', 'jobportal-framework'),
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control(
            'show_count',
            [
                'label' => esc_html__('Show Count', 'jobportal-framework'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'sidebar_checkbox_style',
            [
                'label' => esc_html__('Check Box Style', 'jobportal-framework'),
                'type' => Controls_Manager::SELECT,
                'options' => array(
                    'square' => esc_html__('Square', 'jobportal-framework'),
                    'round' => esc_html__('Round', 'jobportal-framework'),
                ),
                'default' => 'square',
                'label_block' => true,
                'prefix_class' => 'jobportal-checkbox-',
            ]
        );

        $this->add_control(
            'sidebar_range',
            [
                'label' => esc_html__('Slider Range Color', 'jobportal-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-slider .ui-slider-range' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} #slider-range .ui-state-default' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'sidebar_background',
            [
                'label' => esc_html__('Background Color', 'jobportal-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .inner-content .inner-filter' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control('sidebar_space', [
            'label'     => esc_html__('Spacing', 'jobportal-framework'),
            'type'      => Controls_Manager::SLIDER,
            'range'     => [
                'px' => [
                    'min' => 0,
                    'max' => 200,
                ],
            ],
            'selectors' => [
                '{{WRAPPER}} .inner-content .inner-filter' => 'margin-right: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->add_control('sidebar_padding', [
            'label' => esc_html__('Padding', 'jobportal-framework'),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%'],
            'selectors' => [
                '{{WRAPPER}} .inner-content .inner-filter' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_control('sidebar_border_radius', [
            'label' => esc_html__('Border Radius', 'jobportal-framework'),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%'],
            'selectors' => [
                '{{WRAPPER}} .inner-content .inner-filter' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $this->add_render_attribute('wrapper', 'class', 'jobportal-advanced-archive');

        if($settings['show_count'] !== 'yes'){
            $this->add_render_attribute('wrapper', 'class', 'off-count');
        }

        if($settings['post_type'] == 'jobs'){
            $jobs_map_postion = $map_event = '';
            $content_jobs = jobportal_get_option('archive_jobs_layout', 'layout-list');
            $content_jobs = !empty($_GET['layout']) ? jobportal_validate_layout($_GET['layout'], 'jobs') : $content_jobs;
            if ($content_jobs === false) {
                $content_jobs = jobportal_get_option('archive_jobs_layout', 'layout-list');
            }
            $enable_jobs_show_map = jobportal_get_option('enable_jobs_show_map', 1);
            $enable_jobs_show_map = !empty($_GET['has_map']) ? JobPortal_Helper::jobportal_clean(wp_unslash($_GET['has_map'])) : $enable_jobs_show_map;

            if ($enable_jobs_show_map == 1) {
                $archive_jobs_filter = 'filter-canvas';
                $jobs_map_postion = jobportal_get_option('jobs_map_postion');
                $jobs_map_postion = !empty($_GET['map']) ? JobPortal_Helper::jobportal_clean(wp_unslash($_GET['map'])) : $jobs_map_postion;
                if ($jobs_map_postion == 'map-right') {
                    $map_event = 'map-event';
                }
            } else if($content_jobs == 'layout-full') {
                $archive_jobs_filter = 'filter-canvas';
            } else {
                $archive_jobs_filter = jobportal_get_option('jobs_filter_sidebar_option', 'filter-left');
            };
            $archive_jobs_filter = !empty($_GET['filter']) ? JobPortal_Helper::jobportal_clean(wp_unslash($_GET['filter'])) : $archive_jobs_filter;
            $archive_classes = array('archive-layout', 'archive-jobs', $archive_jobs_filter, $map_event, $jobs_map_postion);
        } elseif ($settings['post_type'] == 'company'){
            $company_map_postion = $map_event = '';
            $content_company              = jobportal_get_option('archive_company_layout', 'layout-list');
            $enable_company_show_map = jobportal_get_option('enable_company_show_map', 1);
            $enable_company_show_map = !empty($_GET['has_map']) ? JobPortal_Helper::jobportal_clean(wp_unslash($_GET['has_map'])) : $enable_company_show_map;

            $map_event = '';
            if ($enable_company_show_map == 1) {
                $archive_company_filter = 'filter-canvas';
                $company_map_postion = jobportal_get_option('company_map_postion');
                $company_map_postion = !empty($_GET['map']) ? JobPortal_Helper::jobportal_clean(wp_unslash($_GET['map'])) : $company_map_postion;
                if ($company_map_postion == 'map-right') {
                    $map_event = 'map-event';
                }
            } else {
                $archive_company_filter = jobportal_get_option('company_filter_sidebar_option', 'filter-left');
            };
            $archive_company_filter = !empty($_GET['filter']) ? JobPortal_Helper::jobportal_clean(wp_unslash($_GET['filter'])) : $archive_company_filter;
            $content_company = !empty($_GET['layout']) ? jobportal_validate_layout($_GET['layout'], 'company') : $content_company;
            if ($content_company === false) {
                $content_company = jobportal_get_option('archive_company_layout', 'layout-list');
            }
            $archive_classes = array('archive-layout', 'archive-company', $archive_company_filter,$map_event, $company_map_postion);
        } elseif ($settings['post_type'] == 'candidate'){
            $map_event = $candidate_map_postion = '';
            $content_candidate = jobportal_get_option('archive_candidate_layout', 'layout-list');
            $enable_candidate_show_map = jobportal_get_option('enable_candidate_show_map', 1);
            $enable_candidate_show_map = !empty($_GET['has_map']) ? JobPortal_Helper::jobportal_clean(wp_unslash($_GET['has_map'])) : $enable_candidate_show_map;

            if ($enable_candidate_show_map == 1) {
                $archive_candidate_filter = 'filter-canvas';
                $candidate_map_postion = jobportal_get_option('candidate_map_postion');
                $candidate_map_postion = !empty($_GET['map']) ? JobPortal_Helper::jobportal_clean(wp_unslash($_GET['map'])) : $candidate_map_postion;
                if ($candidate_map_postion == 'map-right') {
                    $map_event = 'map-event';
                }
            } else {
                $archive_candidate_filter = jobportal_get_option('candidate_filter_sidebar_option', 'filter-left');
            };
            $archive_candidate_filter = !empty($_GET['filter']) ? JobPortal_Helper::jobportal_clean(wp_unslash($_GET['filter'])) : $archive_candidate_filter;
            $content_candidate = !empty($_GET['layout']) ? jobportal_validate_layout($_GET['layout'], 'candidate') : $content_candidate;
            if ($content_candidate === false) {
                $content_candidate = jobportal_get_option('archive_candidate_layout', 'layout-list');
            }
            $archive_classes = array('archive-layout', 'archive-candidates', $archive_candidate_filter,$map_event, $candidate_map_postion);
        } elseif ($settings['post_type'] == 'service' && jobportal_get_option('enable_post_type_service') === '1'){
            $service_map_postion = $map_event = '';
            $content_service              = jobportal_get_option('archive_service_layout', 'layout-list');
            $enable_service_show_map = jobportal_get_option('enable_service_show_map', 1);
            $enable_service_show_map = !empty($_GET['has_map']) ? JobPortal_Helper::jobportal_clean(wp_unslash($_GET['has_map'])) : $enable_service_show_map;

            $map_event = '';
            if ($enable_service_show_map == 1) {
                $archive_service_filter = 'filter-canvas';
                $service_map_postion = jobportal_get_option('service_map_postion');
                $service_map_postion = !empty($_GET['map']) ? JobPortal_Helper::jobportal_clean(wp_unslash($_GET['map'])) : $service_map_postion;
                if ($service_map_postion == 'map-right') {
                    $map_event = 'map-event';
                }
            } else {
                $archive_service_filter = jobportal_get_option('service_filter_sidebar_option', 'filter-left');
            };
            $archive_service_filter = !empty($_GET['filter']) ? JobPortal_Helper::jobportal_clean(wp_unslash($_GET['filter'])) : $archive_service_filter;
            $content_service = !empty($_GET['layout']) ? jobportal_validate_layout($_GET['layout'], 'service') : $content_service;
            if ($content_service === false) {
                $content_service = jobportal_get_option('archive_service_layout', 'layout-list');
            }
            $archive_classes = array('archive-layout', 'archive-service', $archive_service_filter,$map_event, $service_map_postion);
        }


        echo '<div ' . $this->get_render_attribute_string('wrapper') . '>';
        echo '<div class="' . join(' ', $archive_classes) . '">';
        if($settings['post_type'] == 'jobs'){
            jobportal_get_template('jobs/archive/layout/layout-default.php');
        } elseif ($settings['post_type'] == 'company'){
            jobportal_get_template('company/archive/layout/layout-default.php');
        } elseif ($settings['post_type'] == 'candidate'){
            jobportal_get_template('candidate/archive/layout/layout-default.php');
        } elseif ($settings['post_type'] == 'service'){
            jobportal_get_template('service/archive/layout/layout-default.php');
        }
        echo '</div>';
        echo '</div>';
    }
}
