<?php

/**
 * The Template for displaying candidate archive
 */

defined('ABSPATH') || exit;
$item_amount = civi_get_option('archive_candidate_items_amount', '12');
wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'select-location');
wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'candidate-archive');
wp_localize_script(
    CIVI_PLUGIN_PREFIX . 'candidate-archive',
    'civi_candidate_archive_vars',
    array(
        'not_candidate' => esc_html__('No candidate found', 'civi-framework'),
        'item_amount' => $item_amount,
    )
);

$key  = isset($_GET['s']) ? civi_clean(wp_unslash($_GET['s'])) : '';
$content_candidate              = civi_get_option('archive_candidate_layout', 'layout-list');
$hide_candidate_top_filter_fields = civi_get_option('hide_candidate_top_filter_fields');
$enable_candidate_filter_top = civi_get_option('enable_candidate_filter_top');
$candidate_filter_sidebar_option = civi_get_option('candidate_filter_sidebar_option');
$content_candidate = !empty($_GET['layout']) ? civi_validate_layout($_GET['layout'], 'candidate') : $content_candidate;
if ($content_candidate === false) {
    $content_candidate = civi_get_option('archive_candidate_layout', 'layout-list');
}
$candidate_filter_sidebar_option = !empty($_GET['filter']) ? Civi_Helper::civi_clean(wp_unslash($_GET['filter'])) : $candidate_filter_sidebar_option;

$enable_candidate_show_map = civi_get_option('enable_candidate_show_map');
$candidate_map_postion = civi_get_option('candidate_map_postion');
$candidate_map_postion = !empty($_GET['map']) ? Civi_Helper::civi_clean(wp_unslash($_GET['map'])) : $candidate_map_postion;
$enable_candidate_show_map = !empty($_GET['has_map']) ? Civi_Helper::civi_clean(wp_unslash($_GET['has_map'])) : $enable_candidate_show_map;

$tax_query = array();
$args = array(
    'posts_per_page'      => $item_amount,
    'post_type'           => 'candidate',
    'ignore_sticky_posts' => 1,
    'post_status'         => 'publish',
    'tax_query'           => $tax_query,
    's'                   => $key,
    'meta_key' => 'civi-candidate_featured',
    'orderby' => 'meta_value date',
    'order' => 'DESC',
);

if (is_tax() && !is_search()) {
    $term_slug = get_query_var('term');
    $taxonomy = get_query_var('taxonomy');
    $current_term = get_term_by('slug', $term_slug, $taxonomy);
    if (!$current_term) {
        $current_term = get_term_by('name', $term_slug, $taxonomy);
    }
} else {
    $current_term = null;
}

$current_term_name = '';
if (!empty($current_term)) {
    $current_term_name = $current_term->name;
} elseif (is_tax()) {
    $queried_object = get_queried_object();
    if ($queried_object && !is_wp_error($queried_object)) {
        $current_term_name = $queried_object->name;
    }
}

$taxonomy_name = '';
if (is_tax() && !is_search() && !empty($current_term)) {
    $taxonomy_title = $current_term->name;
    $taxonomy_name = $current_term->taxonomy;
    if (!empty($taxonomy_name)) {
        $tax_query[] = array(
            'taxonomy' => $taxonomy_name,
            'field' => 'slug',
            'terms' => $current_term->slug
        );
    }
}

$candidate_location_values = array();
if (isset($_SERVER['QUERY_STRING'])) {
    $query_string = $_SERVER['QUERY_STRING'];
    $params = explode('&', $query_string);

    foreach ($params as $param) {
        if (strpos($param, 'candidate_locations=') === 0) {
            $value = substr($param, strlen('candidate_locations='));
            $value = urldecode($value);
            if (!empty($value)) {
                $candidate_location_values[] = $value;
            }
        }
    }
}

if (empty($candidate_location_values) && isset($_GET['candidate_locations'])) {
    $val = $_GET['candidate_locations'];
    if (is_array($val)) {
        foreach ($val as $v) {
            if (!empty($v)) {
                $candidate_location_values[] = $v;
            }
        }
    } elseif (!empty($val)) {
        $candidate_location_values = array($val);
    }
}

if (!empty($candidate_location_values)) {
    $location_ids = array();

    foreach ($candidate_location_values as $loc) {
        $loc = civi_clean(wp_unslash($loc));
        $loc = trim($loc);
        if (empty($loc)) {
            continue;
        }

        $location_term = Civi_Location_Search::find_location_term($loc, 'candidate_locations');

        if ($location_term && !is_wp_error($location_term) && is_object($location_term)) {
            $location_ids[] = $location_term->term_id;
        }
    }

    if (!empty($location_ids)) {
        $tax_query[] = array(
            'taxonomy' => 'candidate_locations',
            'field' => 'term_id',
            'terms' => $location_ids,
            'operator' => 'IN'
        );
    }
}

$candidate_categories_param = isset($_GET['candidate_categories']) ? civi_clean(wp_unslash($_GET['candidate_categories'])) : '';
if (!empty($candidate_categories_param)) {
    $tokens = is_array($candidate_categories_param) ? $candidate_categories_param : explode(',', (string) $candidate_categories_param);
    $cat_ids = array();
    foreach ($tokens as $token) {
        $token = trim((string) $token);
        if ($token === '') {
            continue;
        }
        if (preg_match('/^\d+$/', $token)) {
            $term = get_term((int) $token, 'candidate_categories');
        } else {
            $term = get_term_by('slug', $token, 'candidate_categories');
            if (!$term || is_wp_error($term)) {
                $term = get_term_by('name', $token, 'candidate_categories');
            }
        }
        if ($term && !is_wp_error($term)) {
            $cat_ids[] = (int) $term->term_id;
        }
    }
    if (!empty($cat_ids)) {
        $tax_query[] = array(
            'taxonomy' => 'candidate_categories',
            'field' => 'term_id',
            'terms' => $cat_ids
        );
    }
}

// Generic helper to resolve URL tokens (slug/ID/name) to term IDs for a taxonomy
$__resolve_terms = function ($param_value, $taxonomy) {
    if (empty($param_value)) {
        return array();
    }
    $tokens = is_array($param_value) ? $param_value : explode(',', (string) $param_value);
    $ids = array();
    foreach ($tokens as $token) {
        $token = trim((string) $token);
        if ($token === '') {
            continue;
        }
        if (preg_match('/^\d+$/', $token)) {
            $term = get_term((int) $token, $taxonomy);
        } else {
            $term = get_term_by('slug', $token, $taxonomy);
            if (!$term || is_wp_error($term)) {
                $term = get_term_by('name', $token, $taxonomy);
            }
        }
        if ($term && !is_wp_error($term)) {
            $ids[] = (int) $term->term_id;
        }
    }
    return $ids;
};

// Additional candidate taxonomies from URL (accept slug or ID)
$candidate_ages_param = isset($_GET['candidate_ages']) ? civi_clean(wp_unslash($_GET['candidate_ages'])) : '';
if (!empty($candidate_ages_param)) {
    $ids = $__resolve_terms($candidate_ages_param, 'candidate_ages');
    if (!empty($ids)) {
        $tax_query[] = array(
            'taxonomy' => 'candidate_ages',
            'field' => 'term_id',
            'terms' => $ids,
        );
    }
}

$candidate_languages_param = isset($_GET['candidate_languages']) ? civi_clean(wp_unslash($_GET['candidate_languages'])) : '';
if (!empty($candidate_languages_param)) {
    $ids = $__resolve_terms($candidate_languages_param, 'candidate_languages');
    if (!empty($ids)) {
        $tax_query[] = array(
            'taxonomy' => 'candidate_languages',
            'field' => 'term_id',
            'terms' => $ids,
        );
    }
}

$candidate_qualification_param = isset($_GET['candidate_qualification']) ? civi_clean(wp_unslash($_GET['candidate_qualification'])) : '';
if (!empty($candidate_qualification_param)) {
    $ids = $__resolve_terms($candidate_qualification_param, 'candidate_qualification');
    if (!empty($ids)) {
        $tax_query[] = array(
            'taxonomy' => 'candidate_qualification',
            'field' => 'term_id',
            'terms' => $ids,
        );
    }
}

$candidate_yoe_param = isset($_GET['candidate_yoe']) ? civi_clean(wp_unslash($_GET['candidate_yoe'])) : '';
if (!empty($candidate_yoe_param)) {
    $ids = $__resolve_terms($candidate_yoe_param, 'candidate_yoe');
    if (!empty($ids)) {
        $tax_query[] = array(
            'taxonomy' => 'candidate_yoe',
            'field' => 'term_id',
            'terms' => $ids,
        );
    }
}

$candidate_education_levels_param = isset($_GET['candidate_education_levels']) ? civi_clean(wp_unslash($_GET['candidate_education_levels'])) : '';
if (!empty($candidate_education_levels_param)) {
    $ids = $__resolve_terms($candidate_education_levels_param, 'candidate_education_levels');
    if (!empty($ids)) {
        $tax_query[] = array(
            'taxonomy' => 'candidate_education_levels',
            'field' => 'term_id',
            'terms' => $ids,
        );
    }
}

$candidate_skills_param = isset($_GET['candidate_skills']) ? civi_clean(wp_unslash($_GET['candidate_skills'])) : '';
if (!empty($candidate_skills_param)) {
    $ids = $__resolve_terms($candidate_skills_param, 'candidate_skills');
    if (!empty($ids)) {
        $tax_query[] = array(
            'taxonomy' => 'candidate_skills',
            'field' => 'term_id',
            'terms' => $ids,
        );
    }
}

$tax_count = count($tax_query);
if ($tax_count > 0) {
    $args['tax_query'] = array_merge(array('relation' => 'AND'), $tax_query);
}

$args = apply_filters('civi/archive-candidate/layout-default/query/args', $args);
$data = new WP_Query($args);
$total_post = $data->found_posts;
$total_post_for_n = max(1, (int) $total_post);
$archive_class = array();

if ($content_candidate == 'layout-list') {
    $class_view = 'list-view';
    $class_inner[] = 'layout-list';
} else {
    $class_view = 'grid-view';
    $class_inner[] = 'layout-grid';
}

$archive_class[] = $class_view;
$archive_class[] = 'content-candidate area-candidates area-archive';

if ($enable_candidate_show_map == 1) {
    $class_inner[] = 'has-map';
} else {
    $class_inner[] = 'no-map';
}
?>
<?php if ($enable_candidate_show_map == 1 && $candidate_map_postion == 'map-top') { ?>
    <div class="col-right">
        <?php
        /**
         * @Hook: civi_archive_map_filter
         *
         * @hooked archive_map_filter
         */
        do_action('civi_archive_map_filter');
        ?>
    </div>
<?php } ?>

<?php if ($enable_candidate_filter_top == 1) { ?>
    <?php do_action('civi_archive_candidate_top_filter', $current_term, $total_post); ?>
<?php } ?>

<div class="inner-content container <?php echo join(' ', $class_inner); ?>">
    <div class="col-left">
        <?php if ($candidate_filter_sidebar_option !== 'filter-right') {
            do_action('civi_archive_candidate_sidebar_filter', $current_term, $total_post);
        } ?>

        <?php
        /**
         * @Hook: civi_output_content_wrapper_start
         *
         * @hooked output_content_wrapper_start
         */
        do_action('civi_output_content_wrapper_start');
        ?>
        <div class="filter-wrapper">
            <div class="entry-left">
                <div class="btn-canvas-filter <?php if ($enable_candidate_show_map != 1) { ?>hidden-lg-up<?php } ?>">
                    <a href="#"><i class="fal fa-filter"></i><?php esc_html_e('Filter', 'civi-framework'); ?></a>
                </div>
                <span class="result-count">
                    <?php if (!empty($key)) { ?>
                        <?php printf(_n('%1$s candidate for "%2$s"', '%1$s candidates for "%2$s"', $total_post_for_n, 'civi-framework'), '<span>' . $total_post . '</span>', $key); ?>
                    <?php } elseif (is_tax()) { ?>
                        <?php printf(_n('%1$s candidate for "%2$s"', '%1$s candidates for "%2$s"', $total_post_for_n, 'civi-framework'), '<span>' . $total_post . '</span>', $current_term_name); ?>
                    <?php } else { ?>
                        <?php printf(_n('%1$s candidate', '%1$s candidates', $total_post_for_n, 'civi-framework'), '<span>' . $total_post . '</span>'); ?>
                    <?php } ?>
                </span>
            </div>
            <div class="entry-right">
                <div class="entry-filter filter-wrapper inner-box">
                    <div class="civi-clear-filter hidden-lg-up">
                        <i class="far fa-sync fa-spin"></i>
                        <span><?php esc_html_e('Clear All', 'civi-framework'); ?></span>
                    </div>
                    <div class="candidate-layout switch-layout">
                        <a class="<?php if ($content_candidate == 'layout-grid') : echo 'active';
                                    endif; ?>" href="#" data-layout="layout-grid"><i class="far far fa-th-large icon-large"></i></a>
                        <a class="<?php if ($content_candidate == 'layout-list') : echo 'active';
                                    endif; ?>" href="#" data-layout="layout-list"><i class="far fa-list icon-large"></i></a>
                    </div>
                    <span class="text-sort-by"><?php esc_html_e('Sort by', 'civi-framework'); ?></span>
                    <select name="sort_by" class="sort-by filter-control civi-select2">
                        <option value="newest"><?php esc_html_e('Newest', 'civi-framework'); ?></option>
                        <option value="oldest"><?php esc_html_e('Oldest', 'civi-framework'); ?></option>
                        <option value="rating"><?php esc_html_e('Rating', 'civi-framework'); ?></option>
                    </select>
                    <?php if ($enable_candidate_show_map == 1 && $candidate_map_postion == 'map-right') { ?>
                        <div class="btn-control btn-switch btn-hide-map">
                            <span class="text-switch"><?php esc_html_e('Map', 'civi-framework'); ?></span>
                            <label class="switch">
                                <input type="checkbox" value="hide_map">
                                <span class="slider round"></span>
                            </label>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <div class="entry-mobie">
            <span class="result-count">
                <?php if (!empty($key)) { ?>
                    <?php printf(_n('%1$s candidate for "%2$s"', '%1$s candidates for "%2$s"', $total_post_for_n, 'civi-framework'), '<span>' . $total_post . '</span>', $key); ?>
                <?php } elseif (is_tax()) { ?>
                    <?php printf(_n('%1$s candidate for "%2$s"', '%1$s candidates for "%2$s"', $total_post_for_n, 'civi-framework'), '<span>' . $total_post . '</span>', $current_term_name); ?>
                <?php } else { ?>
                    <?php printf(_n('%1$s candidate', '%1$s candidates', $total_post_for_n, 'civi-framework'), '<span>' . $total_post . '</span>'); ?>
                <?php } ?>
            </span>
            <div class="civi-clear-filter hidden-lg-up">
                <i class="far fa-sync fa-spin"></i>
                <span><?php esc_html_e('Clear All', 'civi-framework'); ?></span>
            </div>
        </div>

        <div class="<?php echo join(' ', $archive_class); ?>">
            <?php if ($data->have_posts()) { ?>
                <?php while ($data->have_posts()) : $data->the_post(); ?>
                    <?php civi_get_template('content-candidate.php', array(
                        'candidate_layout' => $content_candidate,
                    )); ?>
                <?php endwhile; ?>
            <?php } else { ?>
                <div class="item-not-found"><?php esc_html_e('No item found', 'civi-framework'); ?></div>
            <?php } ?>
        </div>

        <?php
        $max_num_pages = $data->max_num_pages;
        $pagination_type = civi_get_option('candidate_pagination_type');
        civi_get_template('global/pagination.php', array('max_num_pages' => $max_num_pages, 'type' => 'ajax-call', 'pagination_type' => $pagination_type));
        wp_reset_postdata();
        ?>
        <?php
        /**
         * @Hook: civi_output_content_wrapper_end
         *
         * @hooked output_content_wrapper_end
         */
        do_action('civi_output_content_wrapper_end');
        ?>

        <?php if ($candidate_filter_sidebar_option == 'filter-right') {
            do_action('civi_archive_candidate_sidebar_filter', $current_term, $total_post);
        } ?>

    </div>
    <?php if ($enable_candidate_show_map == 1 && $candidate_map_postion == 'map-right') { ?>
        <div class="col-right">
            <?php
            /**
             * @Hook: civi_archive_map_filter
             *
             * @hooked archive_map_filter
             */
            do_action('civi_archive_map_filter');
            ?>
        </div>
    <?php } ?>
</div>
