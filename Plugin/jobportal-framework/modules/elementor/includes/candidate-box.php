<?php

namespace Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Plugin;

defined('ABSPATH') || exit;

Plugin::instance()->widgets_manager->register(new Widget_Candidate_Box());

class Widget_Candidate_Box extends Widget_Base
{

    const QUERY_CONTROL_ID = 'query';
    const QUERY_OBJECT_POST = 'post';

    public function get_post_type()
    {
        return 'candidate';
    }

    public function get_name()
    {
        return 'jobportal-candidate-box';
    }

    public function get_title()
    {
        return esc_html__('Candidate Box', 'jobportal-framework');
    }

    public function get_icon()
    {
        return 'jobportal-badge eicon-preferences';
    }

    public function get_keywords()
    {
        return ['candidate', 'carousel'];
    }

    public function get_style_depends()
    {
        return [JOBPORTAL_PLUGIN_PREFIX . 'candidate-box'];
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
            'label' => esc_html__('Layout', 'jobportal-framework'),
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control(
            'enable_slider',
            [
                'label' => esc_html__('Enable Slider', 'jobportal-framework'),
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
                'label' => esc_html__('Posts Per Page', 'jobportal-framework'),
                'type' => Controls_Manager::NUMBER,
                'default' => 6,
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
                    '{{WRAPPER}} .elementor-carousel .jobportal-candidate-item' => 'padding-left: calc({{SIZE}}{{UNIT}}/2); padding-right: calc({{SIZE}}{{UNIT}}/2)',
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
                    '{{WRAPPER}} .elementor-carousel .jobportal-candidate-item' => 'padding-top: calc({{SIZE}}{{UNIT}}/2); padding-bottom: calc({{SIZE}}{{UNIT}}/2)',
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
            'label' => esc_html__('Query', 'jobportal-framework'),
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control(
            'type_query',
            [
                'label' => esc_html__('Filter', 'jobportal-framework'),
                'type' => Controls_Manager::SELECT,
                'default' => 'orderby',
                'options' => [
                    'title' => esc_html__('Title', 'jobportal-framework'),
                    'orderby' => esc_html__('Orderby', 'jobportal-framework'),
                    'taxonomy' => esc_html__('Taxonomy', 'jobportal-framework'),
                ],
            ]
        );

        $taxonomies = array(
            "Categories" => "candidate_categories",
            "Experience Level" => "candidate_yoe",
            "Qualification" => "candidate_qualification",
            "Ages" => "candidate_ages",
            "Skills" => "candidate_skills",
            "Languages" => "candidate_languages",
            "Gender" => "candidate_gender",
        );

        foreach ($taxonomies as $label_taxonomy => $taxonomy) {
            $categories = get_terms([
                'taxonomy' => $taxonomy,
                'hide_empty' => true,
            ]);

            $options = array();
            foreach ($categories as $category) {
                if (!empty($category) && $category->slug != 'uncategorized') {
                    $options[$category->term_id] = $category->name;
                }
            }

            $this->add_control($taxonomy, [
                'label' => esc_html__($label_taxonomy, 'jobportal-framework'),
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
                'label' => esc_html__('Order By', 'jobportal-framework'),
                'type' => Controls_Manager::SELECT,
                'default' => 'newest',
                'options' => [
                    'featured' => esc_html__('Featured', 'jobportal-framework'),
                    'oldest' => esc_html__('Oldest', 'jobportal-framework'),
                    'newest' => esc_html__('Newest', 'jobportal-framework'),
                    'random' => esc_html__('Random', 'jobportal-framework'),
                ],
                'condition' => [
                    'type_query' => 'orderby',
                ],
            ]
        );

        $options_candidate = [];
        $args_candidate = array(
            'post_type' => $this->get_post_type(),
            'ignore_sticky_posts' => 1,
            'post_status' => 'publish',
        );

        $data_candidate = new \WP_Query($args_candidate);
        if ($data_candidate->have_posts()) {
            while ($data_candidate->have_posts()) : $data_candidate->the_post();
                $id = get_the_id();
                $title = get_the_title($id);
                $options_candidate[$id] = $title;
            endwhile;
        }
        wp_reset_postdata();

        $this->add_control('include_ids', [
            'label' => esc_html__('Search & Select', 'jobportal-framework'),
            'type' => Controls_Manager::SELECT2,
            'options' => $options_candidate,
            'default' => [],
            'label_block' => true,
            'multiple' => true,
            'condition' => [
                'type_query' => 'title',
            ],
        ]);

        $this->end_controls_section();
    }

    private function register_slider_section()
    {
        $this->start_controls_section('slider_section', [
            'label' => esc_html__('Slider', 'jobportal-framework'),
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

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'box_background',
                'label' => esc_html__('Background', 'jobportal-framework'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .jobportal-candidate-item',
            ]
        );

        $this->add_control('box_padding', [
            'label' => esc_html__('Padding', 'jobportal-framework'),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%'],
            'selectors' => [
                '{{WRAPPER}} .jobportal-candidate-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_control('layout_border_radius', [
            'label' => esc_html__('Border Radius', 'jobportal-framework'),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%'],
            'selectors' => [
                '{{WRAPPER}} .jobportal-candidate-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'layout_border',
                'selector' => '{{WRAPPER}} .jobportal-candidate-item',
            ]
        );

        $this->add_control(
            'heading_title',
            [
                'label' => __('Title', 'jobportal-framework'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => esc_html__('Text Color', 'jobportal-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .candidates-title a' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(Group_Control_Typography::get_type(), [
            'name'     => 'title',
            'selector' => '{{WRAPPER}} .candidates-title',
        ]);

        $this->add_control(
            'heading_position',
            [
                'label' => __('Position', 'jobportal-framework'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'position_color',
            [
                'label' => esc_html__('Text Color', 'jobportal-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .candidate-current-position' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(Group_Control_Typography::get_type(), [
            'name'     => 'position',
            'selector' => '{{WRAPPER}} .candidate-current-position',
        ]);

        $this->end_controls_section();
    }

    protected function render()
    {
        $is_rtl = is_rtl();
        $direction = $is_rtl ? 'rtl' : 'ltr';
        $settings = $this->get_settings_for_display();
        $this->add_render_attribute('wrapper', 'class', 'jobportal-candidate');
        $args = array(
            'posts_per_page' => $settings['posts_per_page'],
            'post_type' => 'candidate',
            'ignore_sticky_posts' => 1,
            'post_status' => 'publish',
        );

        //Query
        $tax_query = array(
            array(
                'key' => 'jobportal-enable_candidate_package_expires',
                'value' => 0,
                'compare' => '=='
            )
        );
        $meta_query = array();

        if (!empty($settings['include_ids']) && $settings['type_query'] == 'title') {
            $args['post__in'] = $settings['include_ids'];
        }

        if ($settings['type_query'] == 'orderby') {
            if (!empty($settings['orderby'])) {
                if ($settings['orderby'] == 'featured') {
                    $meta_query[] = array(
                        'key' => JOBPORTAL_METABOX_PREFIX . 'candidate_featured',
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
            $taxonomies = array("candidate_categories", "candidate_yoe", "candidate_qualification", "candidate_ages", "candidate_skills", "candidate_languages", "candidate_gender");
            foreach ($taxonomies as $taxonomy) {
                if (!empty($settings[$taxonomy])) {
                    $tax_query[] = array(
                        'taxonomy' => $taxonomy,
                        'field' => 'term_id',
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
            <?php if ($data->have_posts()) { ?>
                <?php if ($settings['enable_slider'] == 'yes') { ?>
                    <div class="elementor-slick-slider" dir="<?php echo esc_attr($direction); ?>">
                        <div <?php echo $this->get_render_attribute_string('slides'); ?>>
                            <?php while ($data->have_posts()) : $data->the_post();
                                $this->print_candidate_content();
                            endwhile; ?>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="elementor-grid-candidate" dir="<?php echo esc_attr($direction); ?>">
                        <div class="elementor-grid">
                            <?php while ($data->have_posts()) : $data->the_post();
                                $this->print_candidate_content();
                            endwhile; ?>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="item-not-found"><?php esc_html_e('No item found', 'jobportal-framework'); ?></div>
            <?php } ?>
            <input type="hidden" name="item_amount" value="<?php echo $settings['posts_per_page'] ?>">
            <input type="hidden" name="include_ids" value='<?php echo json_encode($settings['include_ids']) ?>'>
            <input type="hidden" name="type_query" value="<?php echo $settings['type_query'] ?>">
            <input type="hidden" name="orderby" value="<?php echo $settings['orderby'] ?>">
        </div>
    <?php }

    private function print_candidate_content()
    { ?>
        <div class="jobportal-candidate-item">
            <div class="candidate-item-inner">
                <?php
                $candidate_id = get_the_ID();
                $author_id = get_post_field('post_author', $candidate_id);
                $candidate_avatar = get_the_author_meta('author_avatar_image_url', $author_id);
                $candidate_featured = get_post_meta($candidate_id, JOBPORTAL_METABOX_PREFIX . 'candidate_featured', true);
                $candidate_current_position = get_post_meta($candidate_id, JOBPORTAL_METABOX_PREFIX . 'candidate_current_position', true);

                // Get candidate display name: full name (first_name + last_name) > username > post title
                $candidate_display_name = jobportal_get_candidate_display_name($candidate_id);
                ?>
                <a class="candidate-img" href="<?php echo get_the_permalink($candidate_id); ?>">
                    <?php if (!empty($candidate_avatar)) : ?>
                        <img class="candidate-avatar" src="<?php echo esc_attr($candidate_avatar) ?>" alt=""/>
                    <?php else : ?>
                        <div class="candidate-avatar"><i class="far fa-camera"></i></div>
                    <?php endif; ?>
                </a>
                <div class="content">
                    <?php if (!empty($candidate_display_name)) : ?>
                        <h2 class="candidates-title">
                            <a href="<?php echo get_the_permalink($candidate_id); ?>"><?php echo esc_html($candidate_display_name); ?></a>
                            <?php if ($candidate_featured == 1) : ?>
                                <span class="tooltip" data-title="<?php echo esc_attr__('Featured', 'jobportal-framework') ?>"><i
                                            class="fas fa-check"></i></span>
                            <?php endif; ?>
                        </h2>
                    <?php endif; ?>
                    <?php if (!empty($candidate_current_position)) { ?>
                        <div class="candidate-current-position">
                            <?php esc_html_e($candidate_current_position); ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php }
}
