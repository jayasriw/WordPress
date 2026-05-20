<?php

/**
 * The Template for displaying company archive
 */

defined('ABSPATH') || exit;

$founded_min = jobportal_get_option('value_founded_min');

$founded_max = jobportal_get_founded_max_year();
$item_amount = jobportal_get_option('archive_company_items_amount', '12');
wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'company-archive');
wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'select-location');
wp_localize_script(
    JOBPORTAL_PLUGIN_PREFIX . 'company-archive',
    'jobportal_company_archive_vars',
    array(
        'not_company' => esc_html__('No company found', 'jobportal-framework'),
        'range_min' => $founded_min,
        'range_max' => $founded_max,
        'item_amount' => $item_amount,
    )
);

$content_company              = jobportal_get_option('archive_company_layout', 'layout-list');
$hide_company_top_filter_fields = jobportal_get_option('hide_company_top_filter_fields');
$enable_company_filter_top = jobportal_get_option('enable_company_filter_top');
$company_filter_sidebar_option = jobportal_get_option('company_filter_sidebar_option');
$content_company = !empty($_GET['layout']) ? jobportal_validate_layout($_GET['layout'], 'company') : $content_company;
if ($content_company === false) {
    $content_company = jobportal_get_option('archive_company_layout', 'layout-list');
}
$company_filter_sidebar_option = !empty($_GET['filter']) ? JobPortal_Helper::jobportal_clean(wp_unslash($_GET['filter'])) : $company_filter_sidebar_option;

$enable_company_show_map = jobportal_get_option('enable_company_show_map');
$company_map_postion = jobportal_get_option('company_map_postion');
$company_map_postion = !empty($_GET['map']) ? JobPortal_Helper::jobportal_clean(wp_unslash($_GET['map'])) : $company_map_postion;
$enable_company_show_map = !empty($_GET['has_map']) ? JobPortal_Helper::jobportal_clean(wp_unslash($_GET['has_map'])) : $enable_company_show_map;

if ($content_company == 'layout-list') {
    $class_view = 'list-view';
    $class_inner[] = 'layout-list';
} else {
    $class_view = 'grid-view';
    $class_inner[] = 'layout-grid';
}

$key          = isset($_GET['s']) ? urldecode(jobportal_clean(wp_unslash($_GET['s']))) : '';
$archive_class   = array();
$archive_class[] = 'content-company area-company area-archive';
$archive_class[] = $class_view;

$tax_query = array();
$args = array(
    'posts_per_page'      => $item_amount,
    'post_type'           => 'company',
    'ignore_sticky_posts' => 1,
    'post_status'         => 'publish',
    'tax_query'           => $tax_query,
    's'                   => $key,
    'orderby'             => 'meta_value',
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

$company_location_values = array();
if (isset($_SERVER['QUERY_STRING'])) {
    $query_string = $_SERVER['QUERY_STRING'];
    $params = explode('&', $query_string);

    foreach ($params as $param) {
        if (strpos($param, 'company-location=') === 0) {
            $value = substr($param, strlen('company-location='));
            $value = urldecode($value);
            if (!empty($value)) {
                $company_location_values[] = $value;
            }
        }
    }
}

if (empty($company_location_values) && isset($_GET['company-location'])) {
    $val = $_GET['company-location'];
    if (is_array($val)) {
        foreach ($val as $v) {
            if (!empty($v)) {
                $company_location_values[] = $v;
            }
        }
    } elseif (!empty($val)) {
        $company_location_values = array($val);
    }
}

if (!empty($company_location_values)) {
    $location_ids = array();

    foreach ($company_location_values as $loc) {
        $loc = jobportal_clean(wp_unslash($loc));
        $loc = trim($loc);
        if (empty($loc)) {
            continue;
        }

        $location_term = JobPortal_Location_Search::find_location_term($loc, 'company-location');

        if ($location_term && !is_wp_error($location_term) && is_object($location_term)) {
            $location_ids[] = $location_term->term_id;
        }
    }

    if (!empty($location_ids)) {
        $tax_query[] = array(
            'taxonomy' => 'company-location',
            'field' => 'term_id',
            'terms' => $location_ids,
            'operator' => 'IN'
        );
    }
}

$company_categories_param = isset($_GET['company-categories']) ? jobportal_clean(wp_unslash($_GET['company-categories'])) : '';
if (!empty($company_categories_param)) {
    $tokens = is_array($company_categories_param) ? $company_categories_param : explode(',', (string) $company_categories_param);
    $cat_ids = array();
    foreach ($tokens as $token) {
        $token = trim((string) $token);
        if ($token === '') {
            continue;
        }
        if (preg_match('/^\d+$/', $token)) {
            $term = get_term((int) $token, 'company-categories');
        } else {
            $term = get_term_by('slug', $token, 'company-categories');
            if (!$term || is_wp_error($term)) {
                $term = get_term_by('name', $token, 'company-categories');
            }
        }
        if ($term && !is_wp_error($term)) {
            $cat_ids[] = (int) $term->term_id;
        }
    }
    if (!empty($cat_ids)) {
        $tax_query[] = array(
            'taxonomy' => 'company-categories',
            'field' => 'term_id',
            'terms' => $cat_ids
        );
    }
}

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

// Company size filter from URL (slug or ID, comma-separated)
$company_size_param = isset($_GET['company-size']) ? jobportal_clean(wp_unslash($_GET['company-size'])) : '';
if (!empty($company_size_param)) {
    $ids = $__resolve_terms($company_size_param, 'company-size');
    if (!empty($ids)) {
        $tax_query[] = array(
            'taxonomy' => 'company-size',
            'field' => 'term_id',
            'terms' => $ids,
        );
    }
}

$tax_count = count($tax_query);
if ($tax_count > 0) {
    $args['tax_query'] = array_merge(array('relation' => 'AND'), $tax_query);
}

$args = apply_filters('jobportal/archive-company/layout-default/query/args', $args);

$data       = new WP_Query($args);
$total_post = $data->found_posts;
$total_post_for_n = max(1, (int) $total_post);

if ($enable_company_show_map == 1) {
    $class_inner[] = 'has-map';
} else {
    $class_inner[] = 'no-map';
}
?>
<?php if ($enable_company_show_map == 1 && $company_map_postion == 'map-top') { ?>
    <div class="col-right">
        <?php
        /**
         * @Hook: jobportal_archive_map_filter
         *
         * @hooked archive_map_filter
         */
        do_action('jobportal_archive_map_filter');
        ?>
    </div>
<?php } ?>

<?php if ($enable_company_filter_top == 1) { ?>
    <?php do_action('jobportal_archive_company_top_filter', $current_term, $total_post); ?>
<?php } ?>

<div class="inner-content container <?php echo join(' ', $class_inner); ?>">
    <div class="col-left">
        <?php if ($company_filter_sidebar_option !== 'filter-right') {
            do_action('jobportal_archive_company_sidebar_filter', $current_term, $total_post);
        } ?>

        <?php
        /**
         * @Hook: jobportal_output_content_wrapper_start
         *
         * @hooked output_content_wrapper_start
         */
        do_action('jobportal_output_content_wrapper_start');
        ?>
        <div class="filter-wrapper">
            <div class="entry-left">
                <div class="btn-canvas-filter <?php if ($enable_company_show_map != 1) { ?>hidden-lg-up<?php } ?>">
                    <a href="#"><i class="fal fa-filter"></i><?php esc_html_e('Filter', 'jobportal-framework'); ?></a>
                </div>
                <span class="result-count">
                    <?php if (!empty($key)) { ?>
                        <?php printf(_n('%1$s company for "%2$s"', '%1$s companies for "%2$s"', $total_post_for_n, 'jobportal-framework'), '<span>' . $total_post . '</span>', $key); ?>
                    <?php } elseif (is_tax()) { ?>
                        <?php printf(_n('%1$s company for "%2$s"', '%1$s companies for "%2$s"', $total_post_for_n, 'jobportal-framework'), '<span>' . $total_post . '</span>', $current_term_name); ?>
                    <?php } else { ?>
                        <?php printf(_n('%1$s company', '%1$s companies', $total_post_for_n, 'jobportal-framework'), '<span>' . $total_post . '</span>'); ?>
                    <?php } ?>
                </span>
            </div>
            <div class="entry-right">
                <div class="entry-filter filter-wrapper inner-box">
                    <div class="jobportal-clear-filter hidden-lg-up">
                        <i class="far fa-sync fa-spin"></i>
                        <span><?php esc_html_e('Clear All', 'jobportal-framework'); ?></span>
                    </div>
                    <div class="company-layout switch-layout">
                        <a class="<?php if ($content_company == 'layout-grid') : echo 'active';
                                    endif; ?>" href="#" data-layout="layout-grid"><i class="far far fa-th-large icon-large"></i></a>
                        <a class="<?php if ($content_company == 'layout-list') : echo 'active';
                                    endif; ?>" href="#" data-layout="layout-list"><i class="far fa-list icon-large"></i></a>
                    </div>
                    <span class="text-sort-by"><?php esc_html_e('Sort by', 'jobportal-framework'); ?></span>
                    <select name="sort_by" class="sort-by filter-control jobportal-select2">
                        <option value="newest"><?php esc_html_e('Newest', 'jobportal-framework'); ?></option>
                        <option value="oldest"><?php esc_html_e('Oldest', 'jobportal-framework'); ?></option>
                        <option value="rating"><?php esc_html_e('Rating', 'jobportal-framework'); ?></option>
                    </select>
                    <?php if ($enable_company_show_map == 1 && $company_map_postion == 'map-right') { ?>
                        <div class="btn-control btn-switch btn-hide-map">
                            <span class="text-switch"><?php esc_html_e('Map', 'jobportal-framework'); ?></span>
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
                    <?php printf(_n('%1$s company for "%2$s"', '%1$s companies for "%2$s"', $total_post_for_n, 'jobportal-framework'), '<span>' . $total_post . '</span>', $key); ?>
                <?php } elseif (is_tax()) { ?>
                    <?php printf(_n('%1$s company for "%2$s"', '%1$s companies for "%2$s"', $total_post_for_n, 'jobportal-framework'), '<span>' . $total_post . '</span>', $current_term_name); ?>
                <?php } else { ?>
                    <?php printf(_n('%1$s company', '%1$s companies', $total_post_for_n, 'jobportal-framework'), '<span>' . $total_post . '</span>'); ?>
                <?php } ?>
            </span>
            <div class="jobportal-clear-filter hidden-lg-up">
                <i class="far fa-sync fa-spin"></i>
                <span><?php esc_html_e('Clear All', 'jobportal-framework'); ?></span>
            </div>
        </div>

        <div class="<?php echo join(' ', $archive_class); ?>">
            <?php if ($data->have_posts()) { ?>
                <?php while ($data->have_posts()) : $data->the_post(); ?>
                    <?php jobportal_get_template('content-company.php', array(
                        'company_layout' => $content_company,
                    )); ?>
                <?php endwhile; ?>
            <?php } else { ?>
                <div class="item-not-found"><?php esc_html_e('No item found', 'jobportal-framework'); ?></div>
            <?php } ?>
        </div>

        <?php
        $max_num_pages = $data->max_num_pages;
        $pagination_type = jobportal_get_option('company_pagination_type');
        jobportal_get_template('global/pagination.php', array('max_num_pages' => $max_num_pages, 'type' => 'ajax-call', 'pagination_type' => $pagination_type));
        wp_reset_postdata();
        ?>
        <?php
        /**
         * @Hook: jobportal_output_content_wrapper_end
         *
         * @hooked output_content_wrapper_end
         */
        do_action('jobportal_output_content_wrapper_end');
        ?>

        <?php if ($company_filter_sidebar_option == 'filter-right') {
            do_action('jobportal_archive_company_sidebar_filter', $current_term, $total_post);
        } ?>

    </div>
    <?php if ($enable_company_show_map == 1 && $company_map_postion == 'map-right') { ?>
        <div class="col-right">
            <?php
            /**
             * @Hook: jobportal_archive_map_filter
             *
             * @hooked archive_map_filter
             */
            do_action('jobportal_archive_map_filter');
            ?>
        </div>
    <?php } ?>
</div>
