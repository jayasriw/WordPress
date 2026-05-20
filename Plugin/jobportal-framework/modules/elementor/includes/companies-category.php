<?php

namespace Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Plugin;

defined('ABSPATH') || exit;

Plugin::instance()->widgets_manager->register(new Widget_Companies_Category());

class Widget_Companies_Category extends Widget_Base
{

    const QUERY_CONTROL_ID = 'query';
    const QUERY_OBJECT_POST = 'post';

    public function get_post_type()
    {
        return 'company';
    }

    public function get_name()
    {
        return 'jobportal-companies-category';
    }

    public function get_title()
    {
        return esc_html__('Companies Category', 'jobportal-framework');
    }

    public function get_icon()
    {
        return 'jobportal-badge eicon-tabs';
    }

    public function get_keywords()
    {
        return ['company', 'category', 'carousel'];
    }

    public function get_style_depends()
    {
        return [JOBPORTAL_PLUGIN_PREFIX . 'companies-category'];
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

        $repeater = new Repeater();

        $taxonomy_terms = get_categories(
            array(
                'taxonomy' => 'company-categories',
                'orderby' => 'name',
                'order' => 'ASC',
                'hide_empty' => true,
                'parent' => 0,
            )
        );

        $categories = $category_slug = [];
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
            'show_count',
            [
                'label' => esc_html__('Show Count', 'jobportal-framework'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_logo',
            [
                'label' => esc_html__('Show Logo', 'jobportal-framework'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_button',
            [
                'label' => esc_html__('Show Button', 'jobportal-framework'),
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

        $this->add_responsive_control('content_text_align', [
            'label' => esc_html__('Text Align', 'jobportal-framework'),
            'type' => Controls_Manager::CHOOSE,
            'default' => 'left',
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
            'selectors' => [
                '{{WRAPPER}} .list-cate-item' => 'text-align: {{VALUE}};',
            ],
        ]);

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'box_background',
                'label' => esc_html__('Background', 'jobportal-framework'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .cate-content',
            ]
        );

        $this->add_responsive_control('box_padding', [
            'label' => esc_html__('Padding', 'jobportal-framework'),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%'],
            'selectors' => [
                '{{WRAPPER}} .cate-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_responsive_control('layout_border_radius', [
            'label' => esc_html__('Border Radius', 'jobportal-framework'),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%'],
            'selectors' => [
                '{{WRAPPER}} .cate-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'layout_border',
                'selector' => '{{WRAPPER}} .cate-content',
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
                    '{{WRAPPER}} .cate-title' => 'margin-bottom: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => esc_html__('Text Color', 'jobportal-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cate-title a' => 'color: {{VALUE}};',
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
        <?php if ($settings['show_slider'] == 'yes') { ?>
            <div class="elementor-slick-slider" dir="<?php echo esc_attr($direction); ?>">
                <div <?php echo $this->get_render_attribute_string('slides'); ?>>
                <?php } else { ?>
                    <div class="elementor-grid-companies" dir="<?php echo esc_attr($direction); ?>">
                        <div class="elementor-grid">
                        <?php } ?>
                        <?php foreach ($settings['categories_list'] as $categorry) {
                            $category_slug = $categorry['category'];
                            // Initialize variables to prevent undefined variable errors
                            $term_name = '';
                            $term_count = 0;
                            $term_link = '#';
                            $has_valid_category = false;

                            if (!empty($category_slug)) {
                                $cate = get_term_by('slug', $category_slug, 'company-categories');
                                if ($cate && !is_wp_error($cate) && !empty($cate->term_id)) {
                                    $has_valid_category = true;
                                    $term_name = isset($cate->name) ? $cate->name : '';
                                    // Use accurate count function that respects post status
                                    if (function_exists('jobportal_get_term_post_count_for_sidebar')) {
                                        $term_count = jobportal_get_term_post_count_for_sidebar($cate->term_id, 'company-categories');
                                    } else {
                                        // Fallback to default count if function doesn't exist
                                        $term_count = isset($cate->count) ? intval($cate->count) : 0;
                                    }
                                    // Ensure count is always an integer
                                    $term_count = intval($term_count);

                                    // Get term link with error handling
                                    $term_link_result = get_term_link($cate, 'company-categories');
                                    if (!is_wp_error($term_link_result)) {
                                        $term_link = $term_link_result;
                                    }
                                }
                            }

                            // Skip rendering if category doesn't exist
                            if (!$has_valid_category) {
                                continue;
                            }

                            $args_company = array(
                                'posts_per_page' => 4,
                                'post_type' => 'company',
                                'post_status' => 'publish',
                                'orderby' => 'rand',
                                'meta_query' => array(
                                    array(
                                        'key' => JOBPORTAL_METABOX_PREFIX . 'company_logo',
                                        'value' => '',
                                        'compare' => '!=',
                                    )
                                ),
                                'tax_query' => array(
                                    array(
                                        'taxonomy' => 'company-categories',
                                        'field' => 'slug',
                                        'terms' => $category_slug,
                                    ),
                                )
                            );
                            $data_company = new \WP_Query($args_company);
                        ?>
                            <div class="<?php echo 'list-cate-item' ?>">
                                <div class="cate-content">
                                    <div class="cate-inner-content">
                                        <?php if (!empty($term_name)) : ?>
                                            <h4 class="cate-title"><a href="<?php echo esc_url($term_link) ?>"><?php esc_html_e($term_name); ?></a></h4>
                                        <?php endif; ?>
                                        <?php if ($settings['show_count'] == 'yes' && isset($term_count)) : ?>
                                            <p class="cate-count"><?php echo sprintf(_n('%s company is actively hiring', '%s companies are actively hiring', $term_count, 'jobportal-framework'), number_format_i18n($term_count)); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($data_company->have_posts() && $settings['show_logo'] == 'yes') { ?>
                                        <div class="list-company">
                                            <?php while ($data_company->have_posts()) : $data_company->the_post();
                                                $company_id = get_the_ID();
                                                $company_logo = get_post_meta($company_id, JOBPORTAL_METABOX_PREFIX . 'company_logo');
                                            ?>
                                                <a class="company-img" href="<?php echo get_the_permalink($company_id); ?>">
                                                    <?php if (!empty($company_logo[0]['url'])) : ?>
                                                        <img class="logo-company" src="<?php echo $company_logo[0]['url'] ?>" alt="" />
                                                    <?php else : ?>
                                                        <div class="logo-company"><i class="far fa-camera"></i></div>
                                                    <?php endif; ?>
                                                </a>
                                            <?php endwhile; ?>
                                        </div>
                                    <?php }
                                    wp_reset_postdata(); ?>
                                    <?php if (isset($term_count) && $term_count > 0 && $settings['show_button'] == 'yes') : ?>
                                        <div class="button-wrapper">
                                            <a class="jobportal-button button-outline-accent button-icon-right" href="<?php echo esc_url($term_link) ?>">
                                                <span><?php esc_html_e('View all', 'jobportal-framework') ?></span><i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php }
                        ?>
                        </div>
                    </div>
            <?php }
    }
