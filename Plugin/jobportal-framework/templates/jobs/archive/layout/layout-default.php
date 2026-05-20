<?php

/**
 * The Template for displaying jobs archive
 */

defined('ABSPATH') || exit;

wp_enqueue_script('plupload');
wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'select-location');
$candidate_resume = isset($candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_resume_id_list']) ? $candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_resume_id_list'][0] : '';
$filename = basename(get_attached_file($candidate_resume));
$ajax_url = admin_url('admin-ajax.php');
$cv_file = jobportal_get_option('jobportal-cv-type');
$cv_max_file_size = jobportal_get_option('jobportal_image_max_file_size', '1000kb');

$upload_nonce = wp_create_nonce('jobportal_thumbnail_allow_upload');
$url = JOBPORTAL_AJAX_URL . '?action=jobportal_thumbnail_upload_ajax&nonce=' . esc_attr($upload_nonce);
$text = '<i class="far fa-arrow-from-bottom large"></i> ' . esc_html__('Browse', 'jobportal-framework');

wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'upload-cv');

wp_localize_script(
	JOBPORTAL_PLUGIN_PREFIX . 'upload-cv',
	'jobportal_upload_cv_vars',
	array(
		'ajax_url' => $ajax_url,
		'title' => esc_html__('Valid file formats', 'jobportal-framework'),
		'cv_file' => $cv_file,
		'cv_max_file_size' => $cv_max_file_size,
		'upload_nonce' => $upload_nonce,
		'url' => $url,
		'text' => $text,
	)
);
wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'jobs-archive');

$items_amount = jobportal_get_option('archive_jobs_items_amount', '12');
$content_jobs = jobportal_get_option('archive_jobs_layout', 'layout-list');
$content_jobs = !empty($_GET['layout']) ? jobportal_validate_layout($_GET['layout'], 'jobs') : $content_jobs;
if ($content_jobs === false) {
    $content_jobs = jobportal_get_option('archive_jobs_layout', 'layout-list');
}
$hide_jobs_top_filter_fields = jobportal_get_option('hide_jobs_top_filter_fields');
$enable_jobs_filter_top = jobportal_get_option('enable_jobs_filter_top');
$enable_jobs_show_map = jobportal_get_option('enable_jobs_show_map');
$jobs_map_postion = jobportal_get_option('jobs_map_postion');

if ($enable_jobs_show_map == 1 || $content_jobs == 'layout-full') {
	$jobs_filter_sidebar_option = 'filter-canvas';
} else {
	$jobs_filter_sidebar_option = jobportal_get_option('jobs_filter_sidebar_option');
}

$jobs_filter_sidebar_option = !empty($_GET['filter']) ? JobPortal_Helper::jobportal_clean(wp_unslash($_GET['filter'])) : $jobs_filter_sidebar_option;
$jobs_map_postion = !empty($_GET['map']) ? JobPortal_Helper::jobportal_clean(wp_unslash($_GET['map'])) : $jobs_map_postion;
$enable_jobs_show_map = !empty($_GET['has_map']) ? JobPortal_Helper::jobportal_clean(wp_unslash($_GET['has_map'])) : $enable_jobs_show_map;

$key = isset($_GET['s']) ? jobportal_clean(wp_unslash($_GET['s'])) : '';

$archive_class = array();
$archive_class[] = 'content-jobs area-jobs area-archive';

$class_scrollbar = '';
if ($content_jobs == 'layout-list') {
	$class_inner[] = 'layout-list';
} else if ($content_jobs == 'layout-full') {
	$archive_class[] = 'column-1';
	$class_scrollbar = 'custom-scrollbar';
} else {
	$class_inner[] = 'layout-grid';
}

$tax_query = array();
$meta_query = array();
$args = array(
	'posts_per_page' => $items_amount,
	'post_type' => 'jobs',
	'ignore_sticky_posts' => 1,
	'tax_query' => $tax_query,
	's' => $key,
	'meta_key' => JOBPORTAL_METABOX_PREFIX . 'jobs_featured',
	'orderby' => 'meta_value_num date',
	'order' => 'DESC',
);

$enable_jobs_show_expires = jobportal_get_option('enable_jobs_show_expires');
if ($enable_jobs_show_expires == 1) {
	$args['post_status']  = array('publish', 'expired');
} else {
	$args['post_status']  = 'publish';
}

$pagination_type = jobportal_get_option('jobs_pagination_type');
if ($pagination_type == 'loadpage') {
	$paged_load = isset($_GET['nagi-paged']) ? jobportal_clean(wp_unslash($_GET['nagi-paged'])) : '1';
	$args['paged'] = $paged_load;
}

$meta_query[] = array(
	'relation' => 'OR',
	array(
		'key' => JOBPORTAL_METABOX_PREFIX . 'enable_jobs_package_expires',
		'compare' => 'NOT EXISTS',
	),
	array(
		'key' => JOBPORTAL_METABOX_PREFIX . 'enable_jobs_package_expires',
		'value' => 0,
		'compare' => '=',
	)
);

$company_id = isset($_GET['company_id']) ? jobportal_clean(wp_unslash($_GET['company_id'])) : '';
if ($company_id) {
	$meta_query[] = array(
		'key' => JOBPORTAL_METABOX_PREFIX . 'jobs_select_company',
		'value' => $company_id,
		'compare' => '=='
	);
}

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

// Location filter handling
$jobs_location = isset($_GET['jobs-location']) ? jobportal_clean(wp_unslash($_GET['jobs-location'])) : '';
if (!empty($jobs_location)) {
	$location_term = JobPortal_Location_Search::find_location_term($jobs_location, 'jobs-location');

	if ($location_term && !is_wp_error($location_term) && is_object($location_term)) {
		$tax_query[] = array(
			'taxonomy' => 'jobs-location',
			'field' => 'term_id',
			'terms' => $location_term->term_id
		);
	} else {
		$tax_query[] = array(
			'taxonomy' => 'jobs-location',
			'field' => 'name',
			'terms' => sanitize_text_field($jobs_location)
		);
	}
}

// Jobs categories filter handling (accept slug or ID, comma-separated)
$jobs_categories_param = isset($_GET['jobs-categories']) ? jobportal_clean(wp_unslash($_GET['jobs-categories'])) : '';
if (!empty($jobs_categories_param)) {
	$tokens = is_array($jobs_categories_param) ? $jobs_categories_param : explode(',', (string) $jobs_categories_param);
	$cat_ids = array();
	foreach ($tokens as $token) {
		$token = trim((string) $token);
		if ($token === '') {
			continue;
		}
		if (preg_match('/^\d+$/', $token)) {
			$term = get_term((int) $token, 'jobs-categories');
		} else {
			$term = get_term_by('slug', $token, 'jobs-categories');
			if (!$term || is_wp_error($term)) {
				$term = get_term_by('name', $token, 'jobs-categories');
			}
		}
		if ($term && !is_wp_error($term)) {
			$cat_ids[] = (int) $term->term_id;
		}
	}
	if (!empty($cat_ids)) {
		$tax_query[] = array(
			'taxonomy' => 'jobs-categories',
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

$jobs_type_param = isset($_GET['jobs-type']) ? jobportal_clean(wp_unslash($_GET['jobs-type'])) : '';
if (!empty($jobs_type_param)) {
	$ids = $__resolve_terms($jobs_type_param, 'jobs-type');
	if (!empty($ids)) {
		$tax_query[] = array('taxonomy' => 'jobs-type', 'field' => 'term_id', 'terms' => $ids);
	}
}

$jobs_skills_param = isset($_GET['jobs-skills']) ? jobportal_clean(wp_unslash($_GET['jobs-skills'])) : '';
if (!empty($jobs_skills_param)) {
	$ids = $__resolve_terms($jobs_skills_param, 'jobs-skills');
	if (!empty($ids)) {
		$tax_query[] = array('taxonomy' => 'jobs-skills', 'field' => 'term_id', 'terms' => $ids);
	}
}

$jobs_experience_param = isset($_GET['jobs-experience']) ? jobportal_clean(wp_unslash($_GET['jobs-experience'])) : '';
if (!empty($jobs_experience_param)) {
	$ids = $__resolve_terms($jobs_experience_param, 'jobs-experience');
	if (!empty($ids)) {
		$tax_query[] = array('taxonomy' => 'jobs-experience', 'field' => 'term_id', 'terms' => $ids);
	}
}

$jobs_career_param = isset($_GET['jobs-career']) ? jobportal_clean(wp_unslash($_GET['jobs-career'])) : '';
if (!empty($jobs_career_param)) {
	$ids = $__resolve_terms($jobs_career_param, 'jobs-career');
	if (!empty($ids)) {
		$tax_query[] = array('taxonomy' => 'jobs-career', 'field' => 'term_id', 'terms' => $ids);
	}
}

$jobs_gender_param = isset($_GET['jobs-gender']) ? jobportal_clean(wp_unslash($_GET['jobs-gender'])) : '';
if (!empty($jobs_gender_param)) {
	$ids = $__resolve_terms($jobs_gender_param, 'jobs-gender');
	if (!empty($ids)) {
		$tax_query[] = array('taxonomy' => 'jobs-gender', 'field' => 'term_id', 'terms' => $ids);
	}
}

$jobs_qualification_param = isset($_GET['jobs-qualification']) ? jobportal_clean(wp_unslash($_GET['jobs-qualification'])) : '';
if (!empty($jobs_qualification_param)) {
	$ids = $__resolve_terms($jobs_qualification_param, 'jobs-qualification');
	if (!empty($ids)) {
		$tax_query[] = array('taxonomy' => 'jobs-qualification', 'field' => 'term_id', 'terms' => $ids);
	}
}

$tax_count = count($tax_query);
if ($tax_count > 0) {
	$args['tax_query'] = array_merge(array('relation' => 'AND'), $tax_query);
}

$args = apply_filters('jobportal/archive-jobs/layout-default/query/args', $args);
$data = new WP_Query($args);

// Prime meta cache for jobs and related companies
if ($data->have_posts()) {
    $job_ids = wp_list_pluck($data->posts, 'ID');

    // Collect company IDs from job meta
    $company_ids = array();
    foreach ($job_ids as $job_id) {
        $company_id = get_post_meta($job_id, JOBPORTAL_METABOX_PREFIX . 'jobs_select_company', true);
        if (!empty($company_id)) {
            $company_ids[] = (int) $company_id;
        }
    }
    if (!empty($company_ids)) {
        update_meta_cache('post', array_unique($company_ids));
    }
}

$total_post = $data->found_posts;
$total_post_for_n = max(1, (int) $total_post);

$first_job_id = 0;

if ($enable_jobs_show_map == 1) {
	$class_inner[] = 'has-map';
} else if ($content_jobs == 'layout-full') {
	$class_inner[] = 'layout-full';
} else {
	$class_inner[] = 'no-map';
}

if ($total_post <= 0) {
	$class_inner[] = 'only-left';
}
?>
<?php if ($enable_jobs_show_map == 1 && $jobs_map_postion == 'map-top') { ?>
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

<?php if ($enable_jobs_filter_top == 1) { ?>
	<?php do_action('jobportal_archive_jobs_top_filter', $current_term, $total_post); ?>
<?php } ?>

<div class="inner-content container <?php echo join(' ', $class_inner); ?>">
	<div class="col-left <?php echo $class_scrollbar; ?>">

		<?php if ($jobs_filter_sidebar_option !== 'filter-right') {
			do_action('jobportal_archive_jobs_sidebar_filter', $current_term, $total_post);
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
				<div class="btn-canvas-filter <?php if ($jobs_filter_sidebar_option !== 'filter-canvas' && $enable_jobs_show_map != 1) { ?>hidden-lg-up<?php } ?>">
					<a href="#"><i class="fal fa-filter"></i><?php esc_html_e('Filter', 'jobportal-framework'); ?></a>
				</div>
				<span class="result-count">
					<?php if (!empty($key)) { ?>
						<?php printf(_n('%1$s job for "%2$s"', '%1$s jobs for "%2$s"', $total_post_for_n, 'jobportal-framework'), '<span>' . $total_post . '</span>', esc_html($key)); ?>
					<?php } elseif (is_tax() && !empty($current_term_name)) { ?>
						<?php printf(_n('%1$s job for "%2$s"', '%1$s jobs for "%2$s"', $total_post_for_n, 'jobportal-framework'), '<span>' . $total_post . '</span>', esc_html($current_term_name)); ?>
					<?php } elseif (!empty($current_term_name)) { ?>
						<?php printf(_n('%1$s job for "%2$s"', '%1$s jobs for "%2$s"', $total_post_for_n, 'jobportal-framework'), '<span>' . $total_post . '</span>', esc_html($current_term_name)); ?>
					<?php } else { ?>
						<?php printf(_n('%1$s job', '%1$s jobs', $total_post_for_n, 'jobportal-framework'), '<span>' . $total_post . '</span>'); ?>
					<?php } ?>
				</span>
			</div>
			<div class="entry-right">
				<div class="entry-filter filter-wrapper inner-box">
					<div class="jobportal-clear-filter hidden-lg-up">
						<i class="far fa-sync fa-spin"></i>
						<span><?php esc_html_e('Clear All', 'jobportal-framework'); ?></span>
					</div>
					<?php
					if ($content_jobs != 'layout-full') {
					?>
						<div class="jobs-layout switch-layout">
							<a class="<?php if ($content_jobs == 'layout-grid') : echo 'active';
												endif; ?>" href="#" data-layout="layout-grid"><i class="far far fa-th-large icon-large"></i></a>
							<a class="<?php if ($content_jobs == 'layout-list') : echo 'active';
												endif; ?>" href="#" data-layout="layout-list"><i class="far fa-list icon-large"></i></a>
						</div>
					<?php
					}
					?>
					<span class="text-sort-by"><?php esc_html_e('Sort by', 'jobportal-framework'); ?></span>
					<select name="sort_by" class="sort-by filter-control jobportal-select2">
						<option value="featured" selected><?php esc_html_e('Featured', 'jobportal-framework'); ?></option>
						<option value="newest"><?php esc_html_e('Newest', 'jobportal-framework'); ?></option>
						<option value="oldest"><?php esc_html_e('Oldest', 'jobportal-framework'); ?></option>
					</select>
					<?php if ($enable_jobs_show_map == 1 && $jobs_map_postion == 'map-right') { ?>
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
					<?php printf(_n('%1$s job for "%2$s"', '%1$s jobs for "%2$s"', $total_post_for_n, 'jobportal-framework'), '<span>' . $total_post . '</span>', esc_html($key)); ?>
				<?php } elseif (is_tax() && !empty($current_term_name)) { ?>
					<?php printf(_n('%1$s job for "%2$s"', '%1$s jobs for "%2$s"', $total_post_for_n, 'jobportal-framework'), '<span>' . $total_post . '</span>', esc_html($current_term_name)); ?>
				<?php } elseif (!empty($current_term_name)) { ?>
					<?php printf(_n('%1$s job for "%2$s"', '%1$s jobs for "%2$s"', $total_post_for_n, 'jobportal-framework'), '<span>' . $total_post . '</span>', esc_html($current_term_name)); ?>
				<?php } else { ?>
					<?php printf(_n('%1$s job', '%1$s jobs', $total_post_for_n, 'jobportal-framework'), '<span>' . $total_post . '</span>'); ?>
				<?php } ?>
			</span>
			<div class="jobportal-clear-filter hidden-lg-up">
				<i class="far fa-sync fa-spin"></i>
				<span><?php esc_html_e('Clear All', 'jobportal-framework'); ?></span>
			</div>
		</div>

		<div class="<?php echo join(' ', $archive_class); ?>">
			<?php
			$i = 1;
			if ($data->have_posts()) { ?>
				<?php while ($data->have_posts()) : $data->the_post(); ?>
					<?php
					if ($i == 1) {
						$first_job_id = get_the_ID();
					}
					jobportal_get_template('content-jobs.php', array(
						'jobs_layout' => $content_jobs,
					));
					?>
				<?php $i++;
				endwhile; ?>
			<?php } else { ?>
				<div class="item-not-found"><?php esc_html_e('No item found', 'jobportal-framework'); ?></div>
			<?php } ?>
		</div>

		<?php
		$max_num_pages = $data->max_num_pages;
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

		<?php if ($jobs_filter_sidebar_option == 'filter-right' && $enable_jobs_show_map != 1) {
			do_action('jobportal_archive_jobs_sidebar_filter', $current_term, $total_post);
		} ?>

	</div>
	<?php
	if ($enable_jobs_show_map == 1 && $jobs_map_postion == 'map-right') {
		echo '<div class="col-right">';
		/**
		 * @Hook: jobportal_archive_map_filter
		 *
		 * @hooked archive_map_filter
		 */
		do_action('jobportal_archive_map_filter');
		echo '</div>';
	} elseif ($content_jobs == 'layout-full' && $total_post > 0) {
		echo '<div class="col-right preview-job-wrapper">';
		$post_id = $first_job_id;
		$company_id = get_post_meta($post_id, JOBPORTAL_METABOX_PREFIX . 'jobs_select_company');
		$company_id = !empty($company_id) ? $company_id[0] : '';
		$enable_social_twitter = jobportal_get_option('enable_social_twitter', '1');
		$enable_social_linkedin = jobportal_get_option('enable_social_linkedin', '1');
		$enable_social_facebook = jobportal_get_option('enable_social_facebook', '1');
		$enable_social_instagram = jobportal_get_option('enable_social_instagram', '1');
		if ($company_id !== '') {
			$company_logo   = get_post_meta($company_id, JOBPORTAL_METABOX_PREFIX . 'company_logo');
			$company_categories =  get_the_terms($company_id, 'company-categories');
			$company_founded =  get_post_meta($company_id, JOBPORTAL_METABOX_PREFIX . 'company_founded');
			$company_phone =  get_post_meta($company_id, JOBPORTAL_METABOX_PREFIX . 'company_phone');
			$company_email =  get_post_meta($company_id, JOBPORTAL_METABOX_PREFIX . 'company_email');
			$company_size =  get_the_terms($company_id,  'company-size');
			$company_website =  get_post_meta($company_id, JOBPORTAL_METABOX_PREFIX . 'company_website');
			$company_twitter   = get_post_meta($company_id, JOBPORTAL_METABOX_PREFIX . 'company_twitter');
			$company_facebook   = get_post_meta($company_id, JOBPORTAL_METABOX_PREFIX . 'company_facebook');
			$company_instagram   = get_post_meta($company_id, JOBPORTAL_METABOX_PREFIX . 'company_instagram');
			$company_linkedin   = get_post_meta($company_id, JOBPORTAL_METABOX_PREFIX . 'company_linkedin');
			$mycompany = get_post($company_id);
			$meta_query = jobportal_posts_company($company_id);
			$meta_query_post = jobportal_posts_company($company_id, 5);
			$company_location =  get_the_terms($company_id, 'company-location');
		}
	?>
		<div id="jobs-<?php echo $post_id; ?>">
			<div class="block-jobs-warrper">
				<div class="block-archive-top">
					<?php
					/**
					 * Hook: jobportal_preview_jobs_before_summary hook.
					 */
					do_action('jobportal_preview_jobs_before_summary', $post_id); ?>
					<div class="preview-tabs">
						<ul class="tab-nav">
							<li><a href="#job-detail" class="is-active"><?php esc_html_e('Job Detail', 'jobportal-framework'); ?></a></li>
							<?php
							if ($company_id !== '') {
							?>
								<li><a href="#company-overview"><?php esc_html_e('Company Overview', 'jobportal-framework'); ?></a></li>
							<?php
							}
							?>
						</ul>
						<div id="job-detail" class="tab-content is-active">
							<?php
							/**
							 * Hook: jobportal_preview_jobs_summary hook.
							 */
							do_action('jobportal_preview_jobs_summary', $post_id);
							?>
						</div>
						<?php
						if ($company_id !== '') {
						?>
							<div id="company-overview" class="tab-content">
								<div class="company-overview">
									<h4 class="title"><?php esc_html_e('Overview', 'jobportal-framework'); ?></h4>
									<?php if (!empty($mycompany->post_content)) : ?>
										<div class="content"><?php echo $mycompany->post_content; ?><a href="#"><?php esc_html_e('Read more', 'jobportal-framework'); ?></a></div>
									<?php endif; ?>
									<?php if (is_array($company_categories)) : ?>
										<div class="info">
											<p class="title-info"><?php esc_html_e('Categories', 'jobportal-framework'); ?></p>
											<div class="list-cate">
												<?php foreach ($company_categories as $categories) {
													$cate_link = get_term_link($categories, 'jobs-categories'); ?>
													<a href="<?php echo esc_url($cate_link); ?>" class="cate jobportal-link-bottom">
														<?php echo $categories->name; ?>
													</a>
												<?php } ?>
											</div>
										</div>
									<?php endif; ?>
									<?php if (is_array($company_size)) : ?>
										<div class="info">
											<p class="title-info"><?php esc_html_e('Company size', 'jobportal-framework'); ?></p>
											<div class="list-cate">
												<?php foreach ($company_size as $size) {
													echo $size->name;
												} ?>
											</div>
										</div>
									<?php endif; ?>
									<?php if (!empty($company_founded[0])) : ?>
										<div class="info">
											<p class="title-info"><?php esc_html_e('Founded in', 'jobportal-framework'); ?></p>
											<p class="details-info"><?php echo $company_founded[0]; ?></p>
										</div>
									<?php endif; ?>
									<?php if (is_array($company_location)) : ?>
										<div class="info">
											<p class="title-info"><?php esc_html_e('Location', 'jobportal-framework'); ?></p>
											<p class="details-info">
												<?php foreach ($company_location as $location) { ?>
													<span><?php echo $location->name; ?></span>
												<?php } ?>
											</p>
										</div>
									<?php endif; ?>
									<?php if (!empty($company_phone[0])) : ?>
										<div class="info">
											<p class="title-info"><?php esc_html_e('Phone', 'jobportal-framework'); ?></p>
											<p class="details-info company-phone"><a href="tel:<?php echo $company_phone[0]; ?>" data-phone="<?php echo $company_phone[0]; ?>"><?php echo substr($company_phone[0], 0, strlen($company_phone[0]) - 4); ?>****</a><i class="fal fa-eye"></i></p>
										</div>
									<?php endif; ?>
									<?php if (!empty($company_email[0])) : ?>
										<div class="info">
											<p class="title-info"><?php esc_html_e('Email', 'jobportal-framework'); ?></p>
											<p class="details-info email"><a href="mailto:<?php echo $company_email[0]; ?>"><?php echo $company_email[0]; ?></a></p>
										</div>
									<?php endif; ?>
									<ul class="list-social">
										<?php if (!empty($company_facebook[0]) && $enable_social_facebook == 1) : ?>
											<li><a href="<?php echo $company_facebook[0]; ?>"><i class="fab fa-facebook-f"></i></a></li>
										<?php endif; ?>
										<?php if (!empty($company_twitter[0]) && $enable_social_twitter == 1) : ?>
											<li><a href="<?php echo $company_twitter[0]; ?>">
													<!-- fab fa-twitter -->
													<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currrentColor">
														<path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z" />
													</svg>
												</a></li>
										<?php endif; ?>
										<?php if (!empty($company_linkedin[0]) && $enable_social_linkedin == 1) : ?>
											<li><a href="<?php echo $company_linkedin[0]; ?>"><i class="fab fa-linkedin"></i></a></li>
										<?php endif; ?>
										<?php if (!empty($company_instagram[0]) && $enable_social_instagram == 1) : ?>
											<li><a href="<?php echo $company_instagram[0]; ?>"><i class="fab fa-instagram"></i></a></li>
										<?php endif; ?>
										<?php jobportal_get_social_network($company_id, 'company'); ?>
									</ul>
									<?php if (!empty($company_website[0])) :
										$remove_url = array("http://", "https://");
										$name_website = str_replace($remove_url, "", $company_website[0]);
									?>
										<a href="<?php echo $company_website[0]; ?>" class="jobportal-button button-outline button-block button-visit" target="_blank"><?php esc_html_e('Visit ', 'jobportal-framework'); ?><?php echo $name_website ?><i class="fas fa-external-link"></i></a>
									<?php endif; ?>
									<?php jobportal_get_template('company/messages.php', array(
										'company_id' => $company_id,
									)); ?>
								</div>
								<div class="company-jobs">
									<h4 class="title"><?php esc_html_e('Jobs Opening', 'jobportal-framework'); ?></h4>
									<ul class="list-jobs">
										<?php foreach ($meta_query_post->posts as $post) {
											$id_job = $post->ID;
										?>
											<li class="list-items">
												<h6 class="title"><a href="<?php echo get_post_permalink($id_job) ?>"><?php echo get_the_title($id_job); ?></a></h6>
												<div class="info-company">
													<?php $jobs_categories = get_the_terms($post->ID, 'jobs-categories'); ?>
													<?php if (is_array($jobs_categories)) { ?>
														<div class="categories-wrapper">
															<?php foreach ($jobs_categories as $categories) {
																$cate_link = get_term_link($categories, 'jobs-categories'); ?>
																<div class="cate-wrapper">
																	<a href="<?php echo esc_url($cate_link); ?>" class="cate jobportal-link-bottom">
																		<?php echo $categories->name; ?>
																	</a>
																</div>
															<?php } ?>
														</div>
													<?php } ?>
												</div>
											</li>
										<?php }; ?>
									</ul>
									<a href="<?php echo esc_url(get_post_type_archive_link('jobs')) . '/?company_id=' . $company_id ?>" class="jobportal-button button-outline button-block">
										<?php esc_html_e('View all jobs', 'jobportal-framework'); ?>
									</a>
								</div>
							</div>
						<?php
						}
						?>
					</div>
				</div>
				<?php
				/**
				 * Hook: jobportal_after_content_single_jobs_summary hook.
				 */
				do_action('jobportal_after_content_single_jobs_summary', $post_id);
				?>
				<?php
				/**
				 * Hook: jobportal_apply_single_jobs hook.
				 */
				do_action('jobportal_apply_single_jobs', $post_id);
				?>
			</div>
		</div>
	<?php
		echo '</div>';
	}
	?>
</div>
