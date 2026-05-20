<?php

namespace Elementor;

use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Plugin;
use Elementor\Utils;

defined('ABSPATH') || exit;

Plugin::instance()->widgets_manager->register(new Widget_Search_Horizontal());

class Widget_Search_Horizontal extends Widget_Base
{

    public function get_name()
    {
        return 'jobportal-search-horizontal';
    }

    public function get_title()
    {
        return esc_html__('Search Horizontal PostTypes', 'jobportal-framework');
    }

    public function get_icon()
    {
        return 'jobportal-badge eicon-search';
    }

    public function get_keywords()
    {
        return ['jobs', 'companies', 'candidate', 'search'];
    }

    public function get_script_depends()
    {
        return [JOBPORTAL_PLUGIN_PREFIX . 'search-horizontal', JOBPORTAL_PLUGIN_PREFIX . 'search-location', 'jquery-ui-autocomplete'];
    }

    public function get_style_depends()
    {
        return [JOBPORTAL_PLUGIN_PREFIX . 'search-horizontal'];
    }

    protected function register_controls()
    {
        $this->add_layout_section();
        $this->add_layout_jobs_section();
        $this->add_layout_companies_section();
        $this->add_layout_candidates_section();
        $this->add_layout_service_section();
        $this->add_layout_style_section();
    }

    private function add_layout_section()
    {
        $this->start_controls_section('layout_section', [
            'label' => esc_html__('Layout', 'jobportal-framework'),
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('layout', [
            'label' => esc_html__('Layout', 'jobportal-framework'),
            'type' => Controls_Manager::SELECT,
            'options' => [

                '01' => esc_html__('Layout 01', 'jobportal-framework'),
            ],
            'default' => '01',
            'prefix_class' => 'jobportal-search-horizontal-layout-',
        ]);

        $this->add_control('post_type', [
            'label' => esc_html__('Post Type', 'jobportal-framework'),
            'type' => Controls_Manager::SELECT,
            'options' => [
                'jobs' => esc_html__('Jobs', 'jobportal-framework'),
                'company' => esc_html__('Companies', 'jobportal-framework'),
                'candidate' => esc_html__('Candidates', 'jobportal-framework'),
                'service' => esc_html__('Service', 'jobportal-framework'),
            ],
            'default' => 'jobs',
        ]);

        $this->add_control(
            'show_popular',
            [
                'label' => esc_html__('Show Popular', 'jobportal-framework'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $taxonomy_terms = get_categories(
            array(
                'taxonomy' => 'candidate_skills',
                'hide_empty' => false,
                'orderby' => 'name',
                'order' => 'ASC'
            )
        );

        $categories = [];
        foreach ($taxonomy_terms as $term) {
            if (is_object($term) && isset($term->term_id) && isset($term->name)) {
                $categories[$term->term_id] = $term->name;
            }
        }
        $this->add_control(
            'choose_options',
            [
                'label' => esc_html__('Choose Options', 'jobportal-framework'),
                'type' => Controls_Manager::SELECT2,
                'options' => $categories,
                'label_block' => true,
                'multiple'    => true,
                'condition' => [
                    'show_popular' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'show_arrow',
            [
                'label' => esc_html__('Show Arrow', 'jobportal-framework'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
            ]
        );

        $this->add_control(
            'show_clear',
            [
                'label' => esc_html__('Show Clear', 'jobportal-framework'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
            ]
        );

        $this->add_control(
            'show_redirect',
            [
                'label' => esc_html__('Show ajax page redirect', 'jobportal-framework'),
                'type' => Controls_Manager::SWITCHER,
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
            'label' => esc_html__('Jobs', 'jobportal-framework'),
            'tab' => Controls_Manager::TAB_CONTENT,
            'condition' => [
                'post_type' => 'jobs',
            ],
        ]);

        $taxonomies_jobs = array(
            "Categories" => "jobs-categories",
            "Skills" => "jobs-skills",
            "Type" => "jobs-type",
            "Location" => "jobs-location",
            "Career" => "jobs-career",
            "Experience" => "jobs-experience",
        );

        foreach ($taxonomies_jobs as $label_jobs => $jobs) {
            $this->add_control(
                'show_' . $jobs,
                [
                    'label' => esc_html__('Show ' . $label_jobs, 'jobportal-framework'),
                    'type' => Controls_Manager::SWITCHER,
                    'default' => '',
                ]
            );
            $this->add_control('icon_' . $jobs, [
                'label' => esc_html__('Icon ' . $label_jobs, 'jobportal-framework'),
                'type' => Controls_Manager::ICONS,
                'default' => [],
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
            'label' => esc_html__('Companies', 'jobportal-framework'),
            'tab' => Controls_Manager::TAB_CONTENT,
            'condition' => [
                'post_type' => 'company',
            ],
        ]);

        $taxonomies_company  = array(
            "Categories" => "company-categories",
            "Location" => "company-location",
            "Size" => "company-size",
        );

        foreach ($taxonomies_company as $label_company => $company) {
            $this->add_control(
                'show_' . $company,
                [
                    'label' => esc_html__('Show ' . $label_company, 'jobportal-framework'),
                    'type' => Controls_Manager::SWITCHER,
                    'default' => '',
                ]
            );
            $this->add_control('icon_' . $company, [
                'label' => esc_html__('Icon ' . $label_company, 'jobportal-framework'),
                'type' => Controls_Manager::ICONS,
                'default' => [],
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
            'label' => esc_html__('Candidates', 'jobportal-framework'),
            'tab' => Controls_Manager::TAB_CONTENT,
            'condition' => [
                'post_type' => 'candidate',
            ],
        ]);

        $taxonomies_candidate = array(
            "Categories" => "candidate_categories",
            "Ages" => "candidate_ages",
            "Languages" => "candidate_languages",
            "Qualification" => "candidate_qualification",
            "Yoe" => "candidate_yoe",
            "Education" => "candidate_education_levels",
            "Skills" => "candidate_skills",
            "Locations" => "candidate_locations",
        );

        foreach ($taxonomies_candidate as $label_candidate => $candidate) {
            $this->add_control(
                'show_' . $candidate,
                [
                    'label' => esc_html__('Show ' . $label_candidate, 'jobportal-framework'),
                    'type' => Controls_Manager::SWITCHER,
                    'default' => '',
                ]
            );
            $this->add_control('icon_' . $candidate, [
                'label' => esc_html__('Icon ' . $label_candidate, 'jobportal-framework'),
                'type' => Controls_Manager::ICONS,
                'default' => [],
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
            'label' => esc_html__('Service', 'jobportal-framework'),
            'tab' => Controls_Manager::TAB_CONTENT,
            'condition' => [
                'post_type' => 'service',
            ],
        ]);

        $taxonomies_service = array(
            "Categories" => "service-categories",
            "Skills" => "service-skills",
            "Location" => "service-location",
            "Language" => "service-language",
        );

        foreach ($taxonomies_service as $label_service => $service) {
            $this->add_control(
                'show_' . $service,
                [
                    'label' => esc_html__('Show ' . $label_service, 'jobportal-framework'),
                    'type' => Controls_Manager::SWITCHER,
                    'default' => '',
                ]
            );
            $this->add_control('icon_' . $service, [
                'label' => esc_html__('Icon ' . $label_service, 'jobportal-framework'),
                'type' => Controls_Manager::ICONS,
                'default' => [],
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
            'tab' => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_responsive_control(
            'box_max-width',
            [
                'label' => esc_html__('Max Width', 'jobportal-framework'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .search-horizontal-inner' => 'max-width: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

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
                '{{WRAPPER}} .jobportal-search-horizontal' => 'text-align: {{VALUE}};',
            ],
        ]);

        $this->add_control(
            'text_color',
            [
                'label' => esc_html__('Text Color', 'jobportal-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .popular-categories span' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'icon_color',
            [
                'label' => esc_html__('Categories Color', 'jobportal-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .list-category a' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {

        $settings = $this->get_settings_for_display();
        $has_arrow = '';
        if ($settings['show_arrow'] == 'yes') {
            $has_arrow = 'has-arrow';
        }
        $this->add_render_attribute('wrapper', 'class', array(
            'jobportal-search-horizontal',
            $has_arrow,
        ));
        if ($settings['post_type'] == 'jobs') {
            $taxonomy_key = 'jobs-skills';
            $search_placeholder = esc_attr__('Jobs title or keywords', 'jobportal-framework');
            $taxonomies_field = array(
                esc_html__('Locations', 'jobportal-framework') => "jobs-location",
                esc_html__('Categories', 'jobportal-framework') => "jobs-categories",
                esc_html__('Skills', 'jobportal-framework') => "jobs-skills",
                esc_html__('Type', 'jobportal-framework') => "jobs-type",
                esc_html__('Career', 'jobportal-framework') => "jobs-career",
                esc_html__('Experience', 'jobportal-framework') => "jobs-experience",
            );
        } elseif ($settings['post_type'] == 'company') {
            $taxonomy_key = 'company-categories';
            $search_placeholder = esc_attr__('Company title or keywords', 'jobportal-framework');
            $taxonomies_field  = array(
                esc_html__('Locations', 'jobportal-framework') => "company-location",
                esc_html__('Categories', 'jobportal-framework') => "company-categories",
                esc_html__('Size', 'jobportal-framework') => "company-size",
            );
        } elseif ($settings['post_type'] == 'candidate') {
            $taxonomy_key = 'candidate_skills';
            $search_placeholder = esc_attr__('Candidate title or keywords', 'jobportal-framework');
            $taxonomies_field = array(
                esc_html__('Locations', 'jobportal-framework') => "candidate_locations",
                esc_html__('Categories', 'jobportal-framework') => "candidate_categories",
                esc_html__('Ages', 'jobportal-framework') => "candidate_ages",
                esc_html__('Languages', 'jobportal-framework') => "candidate_languages",
                esc_html__('Qualification', 'jobportal-framework') => "candidate_qualification",
                esc_html__('Yoe', 'jobportal-framework') => "candidate_yoe",
                esc_html__('Education', 'jobportal-framework') => "candidate_education_levels",
                esc_html__('Skills', 'jobportal-framework') => "candidate_skills",
            );
        } elseif ($settings['post_type'] == 'service') {
            $taxonomy_key = 'service-skills';
            $search_placeholder = esc_attr__('Service title...', 'jobportal-framework');
            $taxonomies_field  = array(
                esc_html__('Location', 'jobportal-framework') => "service-location",
                esc_html__('Categories', 'jobportal-framework') => "service-categories",
                esc_html__('Skills', 'jobportal-framework') => "service-skills",
                esc_html__('Language', 'jobportal-framework') => "service-language",
            );
        }
        if ($settings['show_redirect'] == 'yes' && !empty($settings['link_redirect']['url'])) {
            $link_redirect = trailingslashit($settings['link_redirect']['url']);
        } else {
            $archive_link = get_post_type_archive_link($settings['post_type']);
            if (!empty($archive_link)) {
                $link_redirect = trailingslashit($archive_link);
            } else {
                $link_redirect = trailingslashit(home_url('/'));
            }
        }
        $link_redirect = apply_filters('jobportal_search_horizontal_redirect_url', $link_redirect, $settings, $this);
?>
        <div <?php echo $this->get_render_attribute_string('wrapper') ?>>
            <form action="<?php echo esc_url($link_redirect); ?>" method="get" class="form-search-horizontal">
                <div class="search-horizontal-inner">
                    <?php $key_name = array();
                    $taxonomy_post_type = get_terms(
                        array(
                            'taxonomy' => $taxonomy_key,
                            'orderby' => 'name',
                            'order' => 'ASC',
                            'hide_empty' => false,
                        )
                    );
                    if (!empty($taxonomy_post_type)) {
                        foreach ($taxonomy_post_type as $term) {
                            if (is_object($term) && isset($term->name)) {
                                $key_name[] = $term->name;
                            }
                        }
                    }
                    $id = apply_filters('jobportal/search-control/id', 'search-horizontal_filter_search');
                    ?>
                    <div class="form-group">
                        <input class="search-horizontal-control jobportal-ajax-ui"
                            id="<?php echo esc_attr($id); ?>" type="text" name="s"
                            placeholder="<?php echo $search_placeholder; ?>" autocomplete="off" data-taxonomy="<?php echo $taxonomy_key; ?>">
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
                                $custom_location_field = apply_filters('jobportal_search_horizontal_location_field', '', $field, $settings);
                                if (!empty($custom_location_field)) {
                                    echo $custom_location_field;
                                } else {
                    ?>
                                    <div class="form-group jobportal-form-location">
                                        <input class="input-search-location jobportal-ajax-ui" type="text" name="<?php echo $field ?>"
                                            placeholder="<?php esc_attr_e('All Cities', 'jobportal-framework') ?>" autocomplete="off" data-taxonomy="<?php echo esc_attr($taxonomy); ?>">
                                        <?php do_action('jobportal_search_horizontal_after_location'); ?>
                                        <?php if ($term_count <= 150): ?>
                                            <select class="jobportal-select2" data-placeholder="<?php esc_attr_e('All Cities', 'jobportal-framework') ?>">
                                                <option value=""><?php esc_html_e('All Cities', 'jobportal-framework') ?></option>
                                                <?php jobportal_get_taxonomy($field, true, false); ?>
                                            </select>
                                        <?php else: ?>
                                            <select class="jobportal-ajax-select2" data-taxonomy="<?php echo esc_attr($taxonomy); ?>" data-placeholder="<?php esc_attr_e('Select location', 'jobportal-framework'); ?>">
                                                <?php
                                                $selected_location = isset($_GET[$field]) ? sanitize_text_field($_GET[$field]) : '';
                                                if (!empty($selected_location)) {
                                                    $location_tokens = explode(',', $selected_location);
                                                    foreach ($location_tokens as $token) {
                                                        $token = trim($token);
                                                        if ($token === '') {
                                                            continue;
                                                        }
                                                        $term = \JobPortal_Location_Search::find_location_term($token, $taxonomy);
                                                        if (!is_wp_error($term) && $term) {
                                                            echo '<option value="' . esc_attr($term->term_id) . '" selected>' . esc_html($term->name) . '</option>';
                                                        }
                                                    }
                                                }
                                                ?>
                                            </select>
                                        <?php endif; ?>
                                        <span class="icon-location">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <g clip-path="url(#clip0_8969_23265)">
                                                    <path d="M13 1L13.001 4.062C14.7632 4.28479 16.4013 5.08743 17.6572 6.34351C18.9131 7.5996 19.7155 9.23775 19.938 11H23V13L19.938 13.001C19.7153 14.7631 18.9128 16.401 17.6569 17.6569C16.401 18.9128 14.7631 19.7153 13.001 19.938L13 23H11V19.938C9.23775 19.7155 7.5996 18.9131 6.34351 17.6572C5.08743 16.4013 4.28479 14.7632 4.062 13.001L1 13V11H4.062C4.28459 9.23761 5.08713 7.59934 6.34324 6.34324C7.59934 5.08713 9.23761 4.28459 11 4.062V1H13ZM12 6C10.4087 6 8.88258 6.63214 7.75736 7.75736C6.63214 8.88258 6 10.4087 6 12C6 13.5913 6.63214 15.1174 7.75736 16.2426C8.88258 17.3679 10.4087 18 12 18C13.5913 18 15.1174 17.3679 16.2426 16.2426C17.3679 15.1174 18 13.5913 18 12C18 10.4087 17.3679 8.88258 16.2426 7.75736C15.1174 6.63214 13.5913 6 12 6ZM12 10C12.5304 10 13.0391 10.2107 13.4142 10.5858C13.7893 10.9609 14 11.4696 14 12C14 12.5304 13.7893 13.0391 13.4142 13.4142C13.0391 13.7893 12.5304 14 12 14C11.4696 14 10.9609 13.7893 10.5858 13.4142C10.2107 13.0391 10 12.5304 10 12C10 11.4696 10.2107 10.9609 10.5858 10.5858C10.9609 10.2107 11.4696 10 12 10Z" fill="#999999" />
                                                </g>
                                                <defs>
                                                    <clipPath id="clip0_8969_23265">
                                                        <rect width="24" height="24" fill="white" />
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                        </span>
                                        <span class="icon-arrow<?php echo ($term_count > 150) ? ' ajax-mode' : ''; ?>">
                                            <i class="fal fa-angle-down"></i>
                                        </span>
                                        <span class="ui-autocomplete-spinner" style="display:none;">
                                            <i class="fa fa-spinner fa-spin"></i>
                                        </span>
                                    </div>
                                <?php }
                            } else {
                                $taxonomy = $field;
                                $term_count = wp_count_terms($taxonomy, ['hide_empty' => false]);
                                ?>
                                <div class="form-group">
                                    <?php Icons_Manager::render_icon($settings['icon_' . $field]); ?>
                                    <?php if ($term_count <= 150): ?>
                                        <select name="<?php echo $field ?>" class="jobportal-select2" data-placeholder="<?php echo sprintf(esc_attr__('All %s', 'jobportal-framework'), $label_field); ?>">
                                            <option value=""><?php echo sprintf(esc_html__('All %s', 'jobportal-framework'), $label_field) ?></option>
                                            <?php jobportal_get_taxonomy($field, true, false); ?>
                                        </select>
                                    <?php else: ?>
                                        <select name="<?php echo $field ?>" class="jobportal-ajax-select2" data-taxonomy="<?php echo esc_attr($taxonomy); ?>" data-placeholder="<?php echo sprintf(esc_attr__('Select %s', 'jobportal-framework'), $label_field); ?>">
                                            <?php
                                            $selected_value = isset($_GET[$field]) ? sanitize_text_field($_GET[$field]) : '';
                                            if (!empty($selected_value)) {
                                                $tokens = explode(',', $selected_value);
                                                foreach ($tokens as $token) {
                                                    $token = trim($token);
                                                    if ($token === '') {
                                                        continue;
                                                    }
                                                    if (\ctype_digit($token)) {
                                                        $term = get_term((int) $token, $taxonomy);
                                                    } else {
                                                        $term = get_term_by('slug', $token, $taxonomy);
                                                    }
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
                        <?php if ($settings['show_clear'] == 'yes') { ?>
                            <span class="jobportal-clear-top-filter"><?php esc_html_e('Clear', 'jobportal-framework') ?></span>
                        <?php } ?>
                        <button type="submit" class="btn-search-horizontal jobportal-button">
                            <?php esc_html_e('Search', 'jobportal-framework') ?>
                        </button>
                    </div>
                </div>
                <?php if ($settings['show_redirect'] !== 'yes') { ?>
                    <input type="hidden" name="post_type" class="post-type" value="<?php echo $settings['post_type'] ?>">
                <?php } ?>
            </form>

            <?php if ($settings['show_popular'] == 'yes') { ?>
                <div class="popular-categories">
                    <span><?php esc_html_e('Popular Searches: ', 'jobportal-framework'); ?></span>
                    <ul class="list-category">
                        <?php
                        if ($settings['choose_options']) {
                            foreach ($settings['choose_options'] as $key => $value) {
                                $term = get_term($value);
                                if ($term && is_object($term) && !is_wp_error($term) && isset($term->name)) {
                                    $term_link = get_term_link($term);
                        ?>
                                    <li>
                                        <a href="<?php echo esc_url($term_link); ?>"><?php echo esc_html($term->name); ?></a>
                                    </li>
                                    <?php
                                }
                            }
                        } else {
                            $taxonomy_terms = get_categories(
                                array(
                                    'taxonomy' => $taxonomy_key,
                                    'order' => 'DESC',
                                    'orderby' => 'rand',
                                    'hide_empty' => false,
                                )
                            );
                            shuffle($taxonomy_terms);

                            if (!empty($taxonomy_terms)) {
                                foreach ($taxonomy_terms as $index => $term) {
                                    if ($index < 2 && is_object($term) && !is_wp_error($term) && isset($term->name)) {
                                        $term_link = get_term_link($term);
                                    ?>
                                        <li>
                                            <a href="<?php echo esc_url($term_link); ?>"><?php echo esc_html($term->name); ?></a>
                                        </li>
                        <?php
                                    }
                                }
                            }
                        }
                        ?>
                    </ul>
                </div>
            <?php } ?>
        </div>
<?php }
}
