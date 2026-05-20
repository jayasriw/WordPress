<?php

namespace Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Plugin;

defined('ABSPATH') || exit;

Plugin::instance()->widgets_manager->register(new Widget_Jobs_Location());

class Widget_Jobs_Location extends Widget_Base
{

    const QUERY_CONTROL_ID = 'query';
    const QUERY_OBJECT_POST = 'post';

    public function get_post_type()
    {
        return 'jobs';
    }

    public function get_name()
    {
        return 'jobportal-jobs-location';
    }

    public function get_title()
    {
        return esc_html__('Jobs Location', 'jobportal-framework');
    }

    public function get_icon()
    {
        return 'jobportal-badge eicon-archive-title';
    }

    public function get_keywords()
    {
        return ['jobs', 'location', 'carousel'];
    }

    public function get_style_depends()
    {
        return [JOBPORTAL_PLUGIN_PREFIX . 'jobs-location'];
    }

    protected function register_controls()
    {
        $this->register_layout_section();
        $this->register_slider_section();
        $this->register_layout_style_section();
        $this->register_title_style_section();
        $this->register_count_style_section();
    }

    private function register_layout_section()
    {
        $this->start_controls_section('layout_section', [
            'label' => esc_html__('Layout', 'jobportal-framework'),
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_responsive_control('text_align', [
            'label' => esc_html__('Alignment', 'jobportal-framework'),
            'type' => Controls_Manager::CHOOSE,
            'options' => array(
                'left' => [
                    'title' => esc_html__('Left', 'jobportal-framework'),
                    'icon' => 'eicon-text-align-left',
                ],
                'center' => [
                    'title' => esc_html__('Center', 'jobportal-framework'),
                    'icon' => 'eicon-text-align-center',
                ],
                'right' => [
                    'title' => esc_html__('Right', 'jobportal-framework'),
                    'icon' => 'eicon-text-align-right',
                ],
            ),
            'default' => '',
            'selectors' => [
                '{{WRAPPER}} .cate-inner' => 'text-align: {{VALUE}};',
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
        ]);

        $repeater = new Repeater();
        $taxonomy_terms = get_categories(
            array(
                'taxonomy' => 'jobs-location',
                'orderby' => 'name',
                'order' => 'ASC',
                'hide_empty' => false,
                'parent' => 0,
            )
        );

        $categories = [];
        foreach ($taxonomy_terms as $location) {
            $categories[$location->slug] = $location->name;
        }
        $repeater->add_control(
            'location',
            [
                'label' => esc_html__('Categories', 'jobportal-framework'),
                'type' => Controls_Manager::SELECT,
                'options' => $categories,
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'image',
            [
                'label' => esc_html__('Choose Image', 'jobportal-framework'),
                'type' => Controls_Manager::MEDIA,
                'dynamic' => [
                    'active' => true,
                ],
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
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
                        'text' => esc_html__('Location #1', 'jobportal-framework'),
                    ],
                    [
                        'text' => esc_html__('Location #2', 'jobportal-framework'),
                    ],
                    [
                        'text' => esc_html__('Location #3', 'jobportal-framework'),
                    ],
                    [
                        'text' => esc_html__('Location #3', 'jobportal-framework'),
                    ],
                ],
                //                'title_field' => '{{{ location }}}',
            ]
        );

        $this->add_control(
            'hover_box',
            [
                'label' => esc_html__('Hover Box', 'jobportal-framework'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
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
            'show_slider',
            [
                'label' => esc_html__('Show Slider', 'jobportal-framework'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
            ]
        );

        $this->add_responsive_control(
            'columns',
            [
                'label' => esc_html__('Columns', 'jobportal-framework'),
                'type' => Controls_Manager::NUMBER,
                'prefix_class' => 'elementor-grid%s-',
                'min' => 1,
                'max' => 6,
                'default' => 4,
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
                    '{{WRAPPER}} .elementor-grid' => 'grid-row-gap: {{SIZE}}{{UNIT}}',
                ],
                'condition' => [
                    'show_slider!' => 'yes',
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

        $this->add_control(
            'thumbnail_size',
            [
                'label' => esc_html__('Image Size', 'jobportal-framework'),
                'type' => Controls_Manager::TEXT,
                'placeholder' => esc_html__('Example: 300x300', 'jobportal-framework'),
                'default' => '',
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

        $this->add_control('thumbnail_border_radius', [
            'label' => esc_html__('Image Border Radius', 'jobportal-framework'),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%'],
            'selectors' => [
                '{{WRAPPER}} .image-cate img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                '{{WRAPPER}} .jobportal-image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_control('box_padding', [
            'label' => esc_html__('Padding Box', 'jobportal-framework'),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%'],
            'selectors' => [
                '{{WRAPPER}} .cate-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_control('content_padding', [
            'label' => esc_html__('Padding Content', 'jobportal-framework'),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%'],
            'selectors' => [
                '{{WRAPPER}} .cate-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_control('layout_border_radius', [
            'label' => esc_html__('Border Radius', 'jobportal-framework'),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%'],
            'selectors' => [
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
                'tab' => Controls_Manager::TAB_STYLE,
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
                    '{{WRAPPER}} .cate-title' => 'margin-top: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => esc_html__('Text Color', 'jobportal-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cate-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => esc_html__('Typography', 'jobportal-framework'),
                'selector' => '{{WRAPPER}} .cate-title',
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
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_count' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'count_spacing',
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
                    '{{WRAPPER}} .cate-count' => 'margin-top: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_control(
            'count_color',
            [
                'label' => esc_html__('Text Color', 'jobportal-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cate-count' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'count_typography',
                'label' => esc_html__('Typography', 'jobportal-framework'),
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
            'autoplaySpeed' => (isset($settings['autoplay_speed']) ? absint($settings['autoplay_speed']) : 0),
            'autoplay' => (('yes' === $settings['autoplay']) ? true : false),
            'infinite' => (('yes' === $settings['infinite']) ? true : false),
            'pauseOnHover' => (('yes' === $settings['pause_on_hover']) ? true : false),
            'centerMode' => (('yes' === $settings['center_mode']) ? true : false),
            'speed' => absint($settings['transition_speed']),
            'arrows' => $show_arrows,
            'dots' => $show_dots,
            'rtl' => $is_rtl,
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

        $hover_box = '';
        if ($settings['hover_box'] === 'yes') {
            $hover_box = 'hover-box';
        }
        $this->add_render_attribute('box', 'class', array(
            'list-cate-item',
            'jobportal-box',
            $hover_box
        ));

        $carousel_classes = ['elementor-carousel'];
        $this->add_render_attribute('slides', [
            'class' => $carousel_classes,
            'data-slider_options' => $slick_data,
        ]);

?>
        <?php if ($settings['show_slider'] === 'yes') { ?>
            <div class="elementor-slick-slider" dir="<?php echo esc_attr($direction); ?>">
                <div <?php echo $this->get_render_attribute_string('slides'); ?>>
                <?php } else { ?>
                    <div class="elementor-grid-jobs" dir="<?php echo esc_attr($direction); ?>">
                        <div class="elementor-grid">
                        <?php } ?>
                        <?php foreach ($settings['categories_list'] as $category) {
                            $location_slug = $category['location'];
                            $term_name = '';
                            $term_count = 0;
                            $term_link = '#';
                            $image_src = '';

                            if (!empty($location_slug)) {
                                $cate = get_term_by('slug', $location_slug, 'jobs-location');
                                if ($cate && !is_wp_error($cate)) {
                                    $term_name = $cate->name;
                                    $term_count = $cate->count;
                                    $term_link = get_term_link($cate, 'jobs-location');
                                    if (is_wp_error($term_link)) {
                                        $term_link = '#';
                                    }
                                }
                            }

                            $thumbnail_size = $settings['thumbnail_size'];
                            $width = $height = '';
                            if (!empty($thumbnail_size)) {
                                if (preg_match('/\d+x\d+/', $thumbnail_size)) {
                                    $thumbnail_size = explode('x', $thumbnail_size);
                                    $width = $thumbnail_size[0];
                                    $height = $thumbnail_size[1];
                                }
                            }
                            if (isset($category['image']['url']) && !empty($category['image']['url'])) {
                                $image_src_full = jobportal_image_resize_url($category['image']['url'], $width, $height);
                                if (!is_wp_error($image_src_full) && is_array($image_src_full) && isset($image_src_full['url'])) {
                                    $image_src = $image_src_full['url'];
                                } else {
                                    $image_src = $category['image']['url'];
                                }
                            }
                        ?>
                            <div <?php echo $this->get_render_attribute_string('box'); ?>>
                                <div class="cate-inner">
                                    <span class="image-cate">
                                        <?php if (!empty($image_src)) { ?>
                                            <a href="<?php echo esc_url($term_link) ?>" class="jobportal-image">
                                                <img src="<?php echo esc_url($image_src); ?>" width="<?php echo esc_attr($width) ?>"
                                                    height="<?php echo esc_attr($height) ?>" alt="<?php echo esc_attr($term_name); ?>">
                                            </a>
                                        <?php } ?>
                                    </span>
                                    <div class="cate-content">
                                        <?php if (!empty($term_name)): ?>
                                            <h4 class="cate-title"><a
                                                    href="<?php echo esc_url($term_link) ?>"><?php echo esc_html($term_name); ?></a>
                                            </h4>
                                        <?php endif; ?>
                                        <?php if ($settings['show_count'] === 'yes' && isset($term_count)): ?>
                                            <p class="cate-count"><?php echo sprintf(_n('%s job', '%s jobs', $term_count, 'jobportal-framework'), number_format_i18n($term_count)); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <a class="link-box" href="<?php echo esc_url($term_link) ?>"></a>
                            </div>
                        <?php } ?>
                        <?php if ($settings['show_slider'] === 'yes') { ?>
                        </div>
                    </div>
                <?php } else { ?>
                </div>
            </div>
        <?php } ?>
<?php
    }
}
?>
