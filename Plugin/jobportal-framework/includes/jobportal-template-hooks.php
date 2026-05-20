<?php

/**
 * layout_wrapper_start
 */
function layout_wrapper_start()
{
	$type_single_jobs = 'type-1';
	$class_layout = array('site-layout');

	if (is_single() && get_post_type() == 'company') {
		$single_company_style = jobportal_get_option('single_company_style');
		// Support ?style=large-cover-img or ?layout=large-cover-img (or cover-img, no-image) for style
		if (!empty($_GET['style'])) {
			$single_company_style = jobportal_clean(wp_unslash($_GET['style']));
		} elseif (!empty($_GET['layout']) && in_array($_GET['layout'], array('cover-img', 'no-image', 'large-cover-img'))) {
			$single_company_style = jobportal_clean(wp_unslash($_GET['layout']));
		}

		if ($single_company_style == 'large-cover-img') {
			$class_layout[] = 'has-large-thumbnail';
		}
	}

	if (is_single() && get_post_type() == 'candidate') {
		$single_candidate_style = jobportal_get_option('single_candidate_style');
		// Support ?style=large-cover-img or ?layout=large-cover-img (or cover-img, no-image) for style
		if (!empty($_GET['style'])) {
			$single_candidate_style = jobportal_clean(wp_unslash($_GET['style']));
		} elseif (!empty($_GET['layout']) && in_array($_GET['layout'], array('cover-img', 'no-image', 'large-cover-img'))) {
			$single_candidate_style = jobportal_clean(wp_unslash($_GET['layout']));
		}

		if ($single_candidate_style == 'large-cover-img') {
			$class_layout[] = 'has-large-thumbnail';
		}
	}

	if (is_single() && get_post_type() == 'jobs') {
		$single_jobs_style = jobportal_get_option('single_jobs_style');
		// Support ?style=large-cover-img or ?layout=large-cover-img (or cover-img, no-image) for style
		if (!empty($_GET['style'])) {
			$single_jobs_style = jobportal_clean(wp_unslash($_GET['style']));
		} elseif (!empty($_GET['layout']) && in_array($_GET['layout'], array('cover-img', 'no-image', 'large-cover-img'))) {
			$single_jobs_style = jobportal_clean(wp_unslash($_GET['layout']));
		}

		if ($single_jobs_style == 'large-cover-img') {
			$class_layout[] = 'has-large-thumbnail';
		}
	}


	if (is_tax() || is_archive()) {
		$class_layout[] = 'has-sidebar';
	}

	if (is_single() && ((get_post_type() == 'jobs') || (get_post_type() == 'company') || (get_post_type() == 'candidate') || (get_post_type() == 'service'))) {
		$class_layout[] = 'has-sidebar';
	}

?>
	<div class="main-content">
		<div class="container">
			<div class="<?php echo join(' ', $class_layout); ?>">
			<?php
		}

		/**
		 * layout_wrapper_end
		 */
		function layout_wrapper_end()
		{
			?>
			</div>
		</div>
	</div>
<?php
		}

		/**
		 * output_content_wrapper
		 */
		function output_content_wrapper_start()
		{
			jobportal_get_template('global/wrapper-start.php');
		}

		/**
		 * output_content_wrapper
		 */
		function output_content_wrapper_end()
		{
			jobportal_get_template('global/wrapper-end.php');
		}

		/**
		 * archive jobs before
		 */
		function archive_jobs_post()
		{
			jobportal_get_template('global/related-post.php');
		}

		/**
		 * archive page title
		 */
		function archive_page_title()
		{
			jobportal_get_template('archive-jobs/page-title.php');
		}

		/**
		 * archive information
		 */
		function archive_information()
		{
			jobportal_get_template('archive-jobs/information.php');
		}

		/**
		 * archive categories
		 */
		function archive_categories()
		{
			jobportal_get_template('archive-jobs/categories.php');
		}

		/**
		 * archive map filter
		 */
		function archive_map_filter()
		{
			wp_enqueue_script('google-map');
			wp_enqueue_script('markerclusterer');
			$map_type = jobportal_get_option('map_type', 'mapbox');
			$mapbox_style = jobportal_get_option('mapbox_style', 'streets-v11');
			if ($map_type == 'mapbox') {
				$mapbox_api_key = jobportal_get_option('mapbox_api_key');
				$map_zoom_level = jobportal_get_option('map_zoom_level');
				$google_map_style = jobportal_get_option('mapbox_style', 'streets-v11');
			} else if ($map_type == 'openstreetmap') {
				$openstreetmap_api_key = jobportal_get_option('openstreetmap_api_key');
				$map_zoom_level = jobportal_get_option('map_zoom_level');
				$openstreetmap_style = jobportal_get_option('openstreetmap_style', 'streets-v11');
			}
			jobportal_get_map_enqueue();
?>
	<div class="jobportal-filter-search-map">
		<div class="entry-map">
			<input id="pac-input" class="controls" type="text" placeholder="<?php esc_html_e('Search...', 'jobportal-framework'); ?>">
			<?php if ($map_type == 'google_map') { ?>
				<div id="jobs-map-filter" class="jobportal-map-filter maptype" style="width: 100%;" data-maptype="<?php echo $map_type; ?>"></div>
			<?php } else if ($map_type == 'openstreetmap') { ?>
				<div
					id="maps"
					class="jobportal-openstreetmap-filter maptype"
					style="width: 100%; height: 100%;"
					data-maptype="<?php echo $map_type; ?>"
					data-key="<?php if ($openstreetmap_api_key) {
											echo $openstreetmap_api_key;
										} ?>"
					data-level="<?php if ($map_zoom_level) {
												echo $map_zoom_level;
											} ?>"
					data-style="<?php if ($openstreetmap_style) {
												echo $openstreetmap_style;
											} ?>"></div>
			<?php } else { ?>
				<div
					id="map"
					class="maptype"
					style="width: 100%; height: 100%;"
					data-key="<?php if ($mapbox_api_key) {
											echo $mapbox_api_key;
										} ?>"
					data-level="<?php if ($map_zoom_level) {
												echo $map_zoom_level;
											} ?>"
					data-type="<?php if ($mapbox_style) {
												echo $mapbox_style;
											} ?>"></div>
			<?php } ?>
			<div class="jobportal-loading-effect"><span class="jobportal-dual-ring"></span></div>
			<div class="no-result"><span><?php esc_html_e("We didn't find any results", 'jobportal-framework'); ?></span>
			</div>
		</div>
	</div>
<?php
		}

		/**
		 * archive jobs top filter
		 */
		function archive_jobs_top_filter()
		{
			wp_enqueue_script('jquery-ui-autocomplete');
			wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'search-autocomplete');

			$jobs_search_fields = jobportal_get_option('jobs_search_field');
			$jobs_search_fields_top = isset($jobs_search_fields['top']) ? $jobs_search_fields['top'] : array();
			unset($jobs_search_fields_top['__no_value__']);
			unset($jobs_search_fields_top['salary']);

			$search_color = $search_image = $search_title_color = '';
			$enable_jobs_search_bg = jobportal_get_option('enable_jobs_search_bg');
			$enable_jobs_search_location = jobportal_get_option('enable_jobs_search_location_top', '1');
			$jobs_search_color = jobportal_get_option('jobs_search_color');
			$jobs_search_image = jobportal_get_option('jobs_search_image');
			$candidate_search_color = jobportal_get_option('candidate_search_color');
			$enable_jobs_search_location_radius = jobportal_get_option('enable_jobs_search_location_radius');
			$enable_jobs_search_bg = !empty($_GET['has_bg']) ? jobportal_clean(wp_unslash($_GET['has_bg'])) : $enable_jobs_search_bg;
			if ($enable_jobs_search_bg == 1) {
				$class_inner = 'has-bg';
			} else {
				$class_inner = '';
			}
			if (!empty($jobs_search_color)) {
				$search_color = 'color :' . $jobs_search_color . ';';
			}
			if (!empty($jobs_search_image['url'])) {
				$search_image = "background-image : url({$jobs_search_image['url']})";
			}
?>
	<div class=" archive-jobs-top archive-filter-top <?php echo $class_inner; ?>" <?php if ($enable_jobs_search_bg == 1) { ?> style="<?php echo  $search_image ?>" <?php } ?>>
		<div class="container">
			<h2 <?php if ($enable_jobs_search_bg == 1) { ?> style="<?php echo $search_color; ?>" <?php } ?>><?php esc_html_e('Find Your Dream Jobs', 'jobportal-framework'); ?></h2>
			<form method="post" class="form-jobs-top-filter form-archive-top-filter">
				<div class="row">
					<?php
					$jobs_skills = array();
					$taxonomy_kills = get_categories(
						array(
							'taxonomy' => 'jobs-skills',
							'orderby' => 'name',
							'order' => 'ASC',
							'hide_empty' => false,
							'number' => 88,
							'parent' => 0
						)
					);
					if (!empty($taxonomy_kills)) {
						foreach ($taxonomy_kills as $term) {
							$jobs_skills[] = $term->name;
						}
					}
					$jobs_keyword = json_encode($jobs_skills);
					$id = apply_filters('jobportal/search-control/id', 'jobs_filter_search');
					?>
					<div class="form-group">
						<input
							class="jobs-search-control archive-search-control jobportal-ajax-ui"
							data-key='<?php echo $jobs_keyword ?>'
							data-taxonomy="jobs-skills"
							id="<?php echo esc_attr($id); ?>"
							type="text"
							name="jobs_filter_search"
							placeholder="<?php esc_attr_e('Jobs title or keywords', 'jobportal-framework') ?>"
							value="<?php if (isset($_GET['s']) && $_GET['s'] != '') {
												echo esc_html(jobportal_clean(wp_unslash($_GET['s'])));
											} ?>" autocomplete="off">
						<span class="btn-filter-search"><i class="far fa-search"></i></span>
						<span class="ui-autocomplete-spinner" style="display:none;">
							<i class="fa fa-spinner fa-spin"></i>
						</span>
					</div>
					<?php if ($enable_jobs_search_location === '1') { ?>
						<?php
						$custom_location_field = apply_filters('jobportal_search_top_job_location_field', '', 'jobs-location', array());
						if (!empty($custom_location_field)) {
							echo $custom_location_field;
						} else {
						?>
							<div class="form-group jobportal-form-location">
								<input
									class="archive-search-location jobportal-ajax-ui"
									type="text"
									name="jobs-search-location"
									data-taxonomy="jobs-location"
									placeholder="<?php esc_attr_e('All Cities', 'jobportal-framework') ?>"
									autocomplete="off"
									value="<?php
													if (isset($_GET['jobs-location'])) {
														$location_value = $_GET['jobs-location'];
														if (is_array($location_value)) {
															$location_value = array_filter($location_value);
															if (!empty($location_value)) {
																echo esc_attr(jobportal_clean(wp_unslash($location_value[0])));
															}
														} elseif (!empty($location_value)) {
															echo esc_attr(jobportal_clean(wp_unslash($location_value)));
														}
													}
													?>">
								<span class="ui-autocomplete-spinner" style="display:none;">
									<i class="fa fa-spinner fa-spin"></i>
								</span>
								<?php do_action('jobportal_search_horizontal_after_location'); ?>
								<?php
								$taxonomy = 'jobs-location';
								$term_count = wp_count_terms($taxonomy, ['hide_empty' => false]);
								?>
								<?php if ($term_count <= 150): ?>
									<select name="jobs-location-top" class="jobportal-select2 hide">
										<?php jobportal_get_taxonomy('jobs-location', false, false); ?>
									</select>
								<?php else: ?>
									<select name="jobs-location-top" class="jobportal-ajax-select2 hide" data-taxonomy="<?php echo esc_attr($taxonomy); ?>" data-placeholder="<?php esc_attr_e('Select location', 'jobportal-framework'); ?>">
										<?php
										$selected_location = isset($_GET['jobs-location-top']) ? sanitize_text_field($_GET['jobs-location-top']) : '';
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
								<span class="icon-arrow">
									<i class="fal fa-angle-down"></i>
								</span>
								<?php if ($enable_jobs_search_location_radius === '1') { ?>
									<span class="radius">
										<span class="labels"><?php esc_html_e('Radius:', 'jobportal-framework') ?></span>
										<input type="number" name="jobs_number_radius" value="25" placeholder="0" />
										<span class="distance"><?php esc_html_e('Km', 'jobportal-framework') ?></span>
									</span>
								<?php } ?>
							</div>
						<?php } ?>
					<?php } ?>
					<?php foreach ($jobs_search_fields_top as $key_field => $field) {
						$jobs_search_fields_icon = jobportal_get_option('jobs_search_fields_' . $key_field);
						if ($key_field == 'jobs-location') {
							jobportal_content_option_taxonomy('jobs', 'top');
						} else {
							$taxonomy = $key_field;
							$term_count = wp_count_terms($taxonomy, ['hide_empty' => false]);
					?>
							<div class="form-group">
								<?php echo $jobs_search_fields_icon; ?>
								<?php if ($term_count <= 150): ?>
									<select name="<?php echo esc_attr($key_field) ?>" class="jobportal-select2">
										<?php
										echo '<option value="">' . esc_html__(sprintf(esc_html__('All %s', 'jobportal-framework'), $field), 'jobportal-framework') . '</option>';
										// Apply order for specific taxonomies
										$order_enabled = false;
										$order_meta_key = '';
										if ($key_field == 'jobs-skills') {
											$order_enabled = true;
											$order_meta_key = 'jobs_skills_order';
										} elseif ($key_field == 'jobs-type') {
											$order_enabled = true;
											$order_meta_key = 'jobs_type_order';
										} elseif ($key_field == 'jobs-career') {
											$order_enabled = true;
											$order_meta_key = 'jobs_career_order';
										} elseif ($key_field == 'jobs-experience') {
											$order_enabled = true;
											$order_meta_key = 'jobs_experience_order';
										} elseif ($key_field == 'jobs-qualification') {
											$order_enabled = true;
											$order_meta_key = 'jobs_qualification_order';
										} elseif ($key_field == 'jobs-gender') {
											$order_enabled = true;
											$order_meta_key = 'jobs_gender_order';
										} elseif ($key_field == 'jobs-categories') {
											$order_enabled = true;
											$order_meta_key = 'jobs_categories_order';
										}
										jobportal_get_taxonomy($key_field, false, false, false, $order_enabled, $order_meta_key);
										?>
									</select>
								<?php else: ?>
									<select name="<?php echo esc_attr($key_field) ?>" class="jobportal-ajax-select2" data-taxonomy="<?php echo esc_attr($taxonomy); ?>" data-placeholder="<?php esc_attr_e('Select an option', 'jobportal-framework'); ?>">
										<?php
										$selected_value = isset($_GET[$key_field]) ? sanitize_text_field($_GET[$key_field]) : '';
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
					<?php } ?>
					<div class="form-group">
						<span class="jobportal-clear-top-filter"><?php esc_html_e('Clear', 'jobportal-framework') ?></span>
						<input
							type="hidden"
							name="has_map"
							value="<?php if (isset($_GET['has_map']) && $_GET['has_map'] == '1') {
												echo '1';
											} else {
												echo '0';
											} ?>">
						<button type="submit" class="btn-top-filter jobportal-button" name="jobs-top-filter">
							<?php esc_html_e('Search', 'jobportal-framework') ?>
							<span class="btn-loading"><i class="fal fa-spinner fa-spin medium"></i></span>
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
<?php }

		/**
		 * Archive jobs sidebar filter
		 */
		function archive_jobs_sidebar_filter($current_term, $total_post)
		{
			wp_enqueue_script('jquery-ui-slider');
			$total_post_for_n = max(1, (int) $total_post);
			$key = isset($_GET['s']) ? jobportal_clean(wp_unslash($_GET['s'])) : '';
			$location = isset($_GET['jobs_location']) ? jobportal_clean(wp_unslash($_GET['jobs_location'])) : '';
			$filter_classes = array();
			$term_id = '';
			$taxonomy_name = '';
			if (is_tax() && !is_search()) {
				$queried_object = get_queried_object();
				if ($queried_object && !is_wp_error($queried_object) && isset($queried_object->term_id)) {
					$term_id = $queried_object->term_id;
					$taxonomy_name = $queried_object->taxonomy;
				}
			}

			// Build context filters for accurate counting
			$context_filters = array();
			if (!empty($term_id) && !empty($taxonomy_name)) {
				$context_filters['current_term'] = $term_id;
				$context_filters['type_term'] = $taxonomy_name;
			}

			// Add other filters from current request
			$context_filters['location'] = isset($_GET['jobs_location']) ? jobportal_clean(wp_unslash($_GET['jobs_location'])) : '';
			$context_filters['salary_min'] = isset($_GET['jobs_filter_salary_min']) ? jobportal_clean(wp_unslash($_GET['jobs_filter_salary_min'])) : '';
			$context_filters['salary_max'] = isset($_GET['jobs_filter_salary_max']) ? jobportal_clean(wp_unslash($_GET['jobs_filter_salary_max'])) : '';
			$context_filters['categories'] = isset($_GET['jobs-categories_id']) ? jobportal_clean(wp_unslash($_GET['jobs-categories_id'])) : '';
			$context_filters['types'] = isset($_GET['jobs-type_id']) ? jobportal_clean(wp_unslash($_GET['jobs-type_id'])) : '';
			$context_filters['skills'] = isset($_GET['jobs-skills_id']) ? jobportal_clean(wp_unslash($_GET['jobs-skills_id'])) : '';
			$context_filters['experience'] = isset($_GET['jobs-experience_id']) ? jobportal_clean(wp_unslash($_GET['jobs-experience_id'])) : '';
			$context_filters['career'] = isset($_GET['jobs-career_id']) ? jobportal_clean(wp_unslash($_GET['jobs-career_id'])) : '';
			$context_filters['gender'] = isset($_GET['jobs-gender_id']) ? jobportal_clean(wp_unslash($_GET['jobs-gender_id'])) : '';
			$context_filters['qualification'] = isset($_GET['jobs-qualification_id']) ? jobportal_clean(wp_unslash($_GET['jobs-qualification_id'])) : '';

			$enable_jobs_show_expires = jobportal_get_option('enable_jobs_show_expires');
			$context_filters['include_expired'] = ($enable_jobs_show_expires === true || $enable_jobs_show_expires === 1 || $enable_jobs_show_expires === '1');

			$jobs_search_fields = jobportal_get_option('jobs_search_field');
			$jobs_search_fields_sidebar = isset($jobs_search_fields['sidebar']) ? $jobs_search_fields['sidebar'] : array();
			unset($jobs_search_fields_sidebar['__no_value__']);

			$jobs_search_fields_sidebar = apply_filters('jobportal_archive_jobs_sidebar_filter_fields', $jobs_search_fields_sidebar, $current_term, $total_post);

			$job_custom_fields = jobportal_render_custom_field('jobs', true);
			$custom_field_ids = array();
			if (!empty($job_custom_fields)) {
				foreach ($job_custom_fields as $f) {
					$custom_field_ids[] = $f['id'];
				}
			}
			ob_start();
?>
	<div class="archive-filter <?php echo join(' ', $filter_classes); ?>">
		<div class="bg-overlay"></div>
		<div class="inner-filter custom-scrollbar">

			<div class="jobportal-nav-filter">
				<div class="jobportal-filter-toggle">
					<span><?php esc_html_e('Filter', 'jobportal-framework'); ?></span>
				</div>
				<div class="jobportal-clear-filter">
					<i class="far fa-sync fa-spin"></i>
					<span><?php esc_html_e('Clear All', 'jobportal-framework'); ?></span>
				</div>
			</div>

			<div class="jobportal-menu-filter">
				<?php
				if ($jobs_search_fields_sidebar) : foreach ($jobs_search_fields_sidebar as $field => $v) {
						$order = isset($v['order']) ? $v['order'] : 'title';
						$meta_key = isset($v['meta_key']) ? $v['meta_key'] : '';
						$meta_type = isset($v['meta_type']) ? $v['meta_type'] : 'NUMERIC';
						switch ($field) {
							case 'jobs-salary':
								$max_salary_raw = jobportal_get_maximum_job_salary_optimized();
								$max_salary = jobportal_round_salary_max($max_salary_raw);
								$min_salary_raw = jobportal_get_minimum_job_salary_optimized();
								$min_salary = $min_salary_raw;

								$currency_sign_default = jobportal_get_option('currency_sign_default');
								$currency_position     = jobportal_get_option('currency_position');
								$thousand_separator = jobportal_get_option('thousand_separator');
								if (empty($thousand_separator)) {
									$thousand_separator = ' ';
								}
								$decimal_separator = jobportal_get_option('decimal_separator');

								// Initial formatted salary range for display
								$initial_min_salary = jobportal_format_salary($min_salary, $currency_sign_default, $currency_position, $thousand_separator, $decimal_separator);
								$initial_max_salary = jobportal_format_salary($max_salary, $currency_sign_default, $currency_position, $thousand_separator, $decimal_separator);
								$initial_salary_range = $initial_min_salary . ' - ' . $initial_max_salary;
				?>
								<div class="filter-salary">
									<div class="entry-filter entry-filter-salary-range">
										<h4><?php esc_html_e('Salary', 'jobportal-framework'); ?></h4>
										<div id="range-slider-salary"
											class="range-slider"
											data-salary-min="<?php echo esc_attr($min_salary); ?>"
											data-salary-max="<?php echo esc_attr($max_salary); ?>"
											data-salary-max-raw="<?php echo esc_attr($max_salary_raw); ?>"
											data-currency-sign="<?php echo esc_attr($currency_sign_default); ?>"
											data-currency-position="<?php echo esc_attr($currency_position); ?>"
											data-thousand-separator="<?php echo esc_attr($thousand_separator); ?>"
											data-decimal-separator="<?php echo esc_attr($decimal_separator); ?>">
											<div id="slider-range-salary"></div>
											<input type="text" id="salary-amount" readonly value="<?php echo esc_attr($initial_salary_range); ?>">
											<input type="hidden" id="jobs_filter_salary_min" name="jobs_filter_salary_min">
											<input type="hidden" id="jobs_filter_salary_max" name="jobs_filter_salary_max">
										</div>
									</div>
								</div>
							<?php
								break;

							case 'jobs-posting-date':
								$posting_date_options = [
									''    => __('All Time', 'jobportal-framework'),
									'1'   => __('Last 24 Hours', 'jobportal-framework'),
									'7'   => __('Last 7 Days', 'jobportal-framework'),
									'30'  => __('Last 30 Days', 'jobportal-framework'),
									'90'  => __('Last 90 Days', 'jobportal-framework')
								];

								$posting_date_options = apply_filters('jobportal_archive_jobs_posting_date_options', $posting_date_options);
							?>
								<div class="filter-posting-date">
									<div class="entry-filter">
										<h4><?php esc_html_e('Last Updated', 'jobportal-framework'); ?></h4>
										<div class="posting-date-filter">
											<select name="jobs_posting_date" class="jobportal-select2">
												<?php foreach ($posting_date_options as $value => $label): ?>
													<option value="<?php echo esc_attr($value); ?>">
														<?php echo esc_html($label); ?>
													</option>
												<?php endforeach; ?>
											</select>
										</div>
									</div>
								</div>
								<?php
								break;

							case 'jobs-custom-fields':
								if (!empty($job_custom_fields)) {
									usort($job_custom_fields, function ($a, $b) {
										$orderA = isset($a['job_custom_field_sidebar_order']) && $a['job_custom_field_sidebar_order'] !== '' ? (int)trim($a['job_custom_field_sidebar_order']) : 999999;
										$orderB = isset($b['job_custom_field_sidebar_order']) && $b['job_custom_field_sidebar_order'] !== '' ? (int)trim($b['job_custom_field_sidebar_order']) : 999999;
										if ($orderA !== $orderB) return $orderA - $orderB;
										$titleA = strtolower($a['title'] ?? '');
										$titleB = strtolower($b['title'] ?? '');
										if ($titleA !== $titleB) return strcmp($titleA, $titleB);
										$idA = strtolower($a['id'] ?? '');
										$idB = strtolower($b['id'] ?? '');
										return strcmp($idA, $idB);
									});
									foreach ($job_custom_fields as $job_custom_field) {
								?>
										<div class="entry-filter entry-filter-custom">
											<h4><?php echo esc_html($job_custom_field['title']); ?></h4>
											<div class="custom-filter-field">
												<?php
												switch ($job_custom_field['type']) {
													case 'text':
													case 'textarea':
												?>
														<input type="text" name="<?php echo esc_attr($job_custom_field['id']); ?>" value="" />
													<?php
														break;
													case 'select':
													?>
														<select name="<?php echo esc_attr($job_custom_field['id']); ?>" class="jobportal-select2">
															<option value=""><?php esc_html_e('All', 'jobportal-framework'); ?></option>
															<?php foreach ($job_custom_field['options'] as $opt_val => $opt_label): ?>
																<option value="<?php echo esc_attr($opt_val); ?>"><?php echo esc_html($opt_label); ?></option>
															<?php endforeach; ?>
														</select>
														<?php
														break;
													case 'checkbox_list':
														foreach ($job_custom_field['options'] as $opt_val => $opt_label):
														?>
															<label>
																<input type="checkbox" name="<?php echo esc_attr($job_custom_field['id']); ?>[]" value="<?php echo esc_attr($opt_val); ?>" />
																<?php echo esc_html($opt_label); ?>
															</label>
												<?php
														endforeach;
														break;
												}
												?>
											</div>
										</div>
									<?php
									}
								}
								break;

							case 'jobs-location':
								if (jobportal_taxonomy_has_terms('jobs-location')): ?>
									<div class="entry-filter entry-filter-locations">
										<h4><?php esc_html_e('Locations', 'jobportal-framework'); ?></h4>
										<div class="locations-filter">
											<?php jobportal_content_option_taxonomy('jobs'); ?>
										</div>
									</div>
				<?php endif;
								break;

							case 'jobs-categories':
								if (jobportal_taxonomy_has_terms('jobs-categories')):
									$title = esc_html__('Jobs Categories', 'jobportal-framework');
									get_search_filter_submenu('jobs-categories', $title, true, 'meta_value', 'jobs_categories_order', 'NUMERIC', $context_filters);
								endif;
								break;

							case 'jobs-skills':
								if (jobportal_taxonomy_has_terms('jobs-skills')):
									$title = esc_html__('Jobs Skills', 'jobportal-framework');
									get_search_filter_submenu('jobs-skills', $title, true, 'meta_value', 'jobs_skills_order', 'NUMERIC', $context_filters);
								endif;
								break;

							case 'jobs-type':
								if (jobportal_taxonomy_has_terms('jobs-type')):
									$title = esc_html__('Jobs Type', 'jobportal-framework');
									get_search_filter_submenu('jobs-type', $title, true, 'meta_value', 'jobs_type_order', 'NUMERIC', $context_filters);
								endif;
								break;

							case 'jobs-experience':
								if (jobportal_taxonomy_has_terms('jobs-experience')):
									$title = esc_html__('Jobs Experience', 'jobportal-framework');
									get_search_filter_submenu('jobs-experience', $title, true, 'meta_value', 'jobs_experience_order', 'NUMERIC', $context_filters);
								endif;
								break;

							case 'jobs-career':
								if (jobportal_taxonomy_has_terms('jobs-career')):
									$title = esc_html__('Jobs Career', 'jobportal-framework');
									get_search_filter_submenu('jobs-career', $title, true, 'meta_value', 'jobs_career_order', 'NUMERIC', $context_filters);
								endif;
								break;

							case 'jobs-gender':
								if (jobportal_taxonomy_has_terms('jobs-gender')):
									$title = esc_html__('Jobs Gender', 'jobportal-framework');
									get_search_filter_submenu('jobs-gender', 'Jobs Gender', true, 'meta_value', 'jobs_gender_order', 'NUMERIC', $context_filters);
								endif;
								break;

							case 'jobs-qualification':
								if (jobportal_taxonomy_has_terms('jobs-qualification')):
									$title = esc_html__('Jobs Qualification', 'jobportal-framework');
									get_search_filter_submenu('jobs-qualification', $title, true, 'meta_value', 'jobs_qualification_order', 'NUMERIC', $context_filters);
								endif;
								break;
						}
					}
				endif;
				?>
			</div>
		</div>

		<div class="show-result">
			<a href="#" class="jobportal-button button-block">
				<span><?php echo esc_html__('Show', 'jobportal-framework'); ?></span>
				<span class="result-count">
					<?php if (!empty($key)) { ?>
						<?php printf(esc_html__('%1$s %2$s for "%3$s"', 'jobportal-framework'), '<span>' . $total_post . '</span>', _n('job', 'jobs', $total_post_for_n === 0 ? 1 : $total_post_for_n, 'jobportal-framework'), $key); ?>
					<?php } else { ?>
						<?php printf(esc_html__('%1$s %2$s', 'jobportal-framework'), '<span>' . $total_post . '</span>', _n('job', 'jobs', $total_post_for_n === 0 ? 1 : $total_post_for_n, 'jobportal-framework')); ?>
					<?php } ?>
				</span>
			</a>
		</div>
		<input type="hidden" name="search_fields_sidebar" value='<?php echo json_encode($jobs_search_fields_sidebar); ?>'>
		<input type="hidden" name="current_term" value="<?php echo esc_attr($term_id); ?>">
		<input type="hidden" name="type_term" value="<?php echo esc_attr($taxonomy_name); ?>">
		<input type="hidden" name="title" value="<?php echo esc_attr($key); ?>">
		<input type="hidden" name="jobs_location" value="<?php echo esc_attr($location); ?>">
	</div>
	<script>
		window.jobportal_custom_filter_fields = <?php echo json_encode($custom_field_ids); ?>;
	</script>
<?php
			$output = ob_get_clean();
			echo apply_filters('jobportal_archive_jobs_sidebar_filter_output', $output, $current_term, $total_post);
		}

		/**
		 * archive company top filter
		 */
		function archive_company_top_filter()
		{
			wp_enqueue_script('jquery-ui-autocomplete');
			wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'search-autocomplete');

			$company_search_fields = jobportal_get_option('company_search_fields');
			$company_search_fields_top = isset($company_search_fields['top']) ? $company_search_fields['top'] : array();
			unset($company_search_fields_top['__no_value__']);

			$search_color = $search_image = '';
			$enable_company_search_bg = jobportal_get_option('enable_company_search_bg');
			$enable_company_search_location = jobportal_get_option('enable_company_search_location_top', '1');
			$company_search_color = jobportal_get_option('company_search_color');
			$company_search_image = jobportal_get_option('company_search_image');
			$enable_company_search_location_radius = jobportal_get_option('enable_company_search_location_radius');
			$enable_company_search_bg = !empty($_GET['has_bg']) ? jobportal_clean(wp_unslash($_GET['has_bg'])) : $enable_company_search_bg;
			if ($enable_company_search_bg == 1) {
				$class_inner = 'has-bg';
			} else {
				$class_inner = '';
			}
			if (!empty($company_search_color)) {
				$search_color = 'color :' . $company_search_color . ';';
			}
			if (!empty($company_search_image['url'])) {
				$search_image = "background-image : url({$company_search_image['url']})";
			}
?>
	<div class="archive-company-top archive-filter-top <?php echo $class_inner; ?>" <?php if ($enable_company_search_bg == 1) { ?> style="<?php echo $search_image ?>" <?php } ?>>
		<div class="container">
			<h2 <?php if ($enable_company_search_bg == 1) { ?> style="<?php echo $search_color ?>" <?php } ?>><?php esc_html_e('Companies Hiring Internationally', 'jobportal-framework'); ?></h2>
			<form method="post" class="form-company-top-filter form-archive-top-filter">
				<div class="row">
					<?php $company_categories = array();
					$taxonomy_categories = get_categories(
						array(
							'taxonomy' => 'company-categories',
							'orderby' => 'name',
							'order' => 'ASC',
							'hide_empty' => false,
							'parent' => 0,
							'number' => 88
						)
					);
					if (!empty($taxonomy_categories)) {
						foreach ($taxonomy_categories as $term) {
							$company_categories[] = $term->name;
						}
					}
					$company_keyword = json_encode($company_categories);
					$id = apply_filters('jobportal/search-control/id', 'company_filter_search');
					?>
					<div class="form-group">
						<input class="company-search-control archive-search-control jobportal-ajax-ui" data-key='<?php echo $company_keyword ?>' data-taxonomy="company-categories" id="<?php echo esc_attr($id); ?>" type="text" name="company_filter_search" placeholder="<?php esc_attr_e('Company title or keywords', 'jobportal-framework') ?>" autocomplete="off">
						<span class="btn-filter-search"><i class="far fa-search"></i></span>
						<span class="ui-autocomplete-spinner" style="display:none;">
							<i class="fa fa-spinner fa-spin"></i>
						</span>
					</div>
					<?php if ($enable_company_search_location === '1') { ?>
						<?php
						$custom_location_field = apply_filters('jobportal_search_top_company_location_field', '', 'company-location', array());
						if (!empty($custom_location_field)) {
							echo $custom_location_field;
						} else {
						?>
							<div class="form-group jobportal-form-location">
								<input class="archive-search-location jobportal-ajax-ui" type="text" name="company-search-location"
									data-taxonomy="company-location"
									placeholder="<?php esc_attr_e('All Cities', 'jobportal-framework') ?>"
									value="<?php
													if (isset($_GET['company-location'])) {
														$location_value = $_GET['company-location'];
														if (is_array($location_value)) {
															$location_value = array_filter($location_value);
															if (!empty($location_value)) {
																echo esc_attr(jobportal_clean(wp_unslash($location_value[0])));
															}
														} elseif (!empty($location_value)) {
															echo esc_attr(jobportal_clean(wp_unslash($location_value)));
														}
													}
													?>">
								<span class="ui-autocomplete-spinner" style="display:none;">
									<i class="fa fa-spinner fa-spin"></i>
								</span>
								<?php
								$taxonomy = 'company-location';
								$term_count = wp_count_terms($taxonomy, ['hide_empty' => false]);
								?>
								<?php if ($term_count <= 150): ?>
									<select name="company-location-top" class="jobportal-select2 hide">
										<?php jobportal_get_taxonomy('company-location', false, false); ?>
									</select>
								<?php else: ?>
									<select name="company-location-top" class="jobportal-ajax-select2 hide" data-taxonomy="<?php echo esc_attr($taxonomy); ?>" data-placeholder="<?php esc_attr_e('Select location', 'jobportal-framework'); ?>">
										<?php
										$selected_location = isset($_GET['company-location-top']) ? sanitize_text_field($_GET['company-location-top']) : '';
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
								<span class="icon-arrow">
									<i class="fal fa-angle-down"></i>
								</span>
								<?php if ($enable_company_search_location_radius === '1') { ?>
									<span class="radius">
										<span class="labels"><?php esc_html_e('Radius:', 'jobportal-framework') ?></span>
										<input type="number" name="company_number_radius" value="" placeholder="0" />
										<span class="distance"><?php esc_html_e('Km', 'jobportal-framework') ?></span>
									</span>
								<?php } ?>
							</div>
						<?php } ?>
					<?php } ?>
					<?php if ($company_search_fields_top) : foreach ($company_search_fields_top as $field => $v) {
							switch ($field) {
								case 'company-rating':
									$company_search_icon_ratting = jobportal_get_option('company_search_fields_company-rating'); ?>
									<div class="form-group">
										<select name="company-rating" class="jobportal-select2">
											<option value=""><?php echo esc_html__('All Rating', 'jobportal-framework'); ?></option>
											<option value="rating_five"><?php echo esc_html__('Five Star', 'jobportal-framework'); ?></option>
											<option value="rating_four"><?php echo esc_html__('Four Star', 'jobportal-framework'); ?></option>
											<option value="rating_three"><?php echo esc_html__('Three Star', 'jobportal-framework'); ?></option>
											<option value="rating_two"><?php echo esc_html__('Two Star', 'jobportal-framework'); ?></option>
											<option value="rating_one"><?php echo esc_html__('One Star', 'jobportal-framework'); ?></option>
										</select>
										<?php echo $company_search_icon_ratting; ?>
									</div>
								<?php break;
								case 'company-size':
									$company_search_icon_size = jobportal_get_option('company_search_fields_company-size');
									$taxonomy = 'company-size';
									$term_count = wp_count_terms($taxonomy, ['hide_empty' => false]);
								?>
									<div class="form-group">
										<?php if ($term_count <= 150): ?>
											<select name="company-size" class="jobportal-select2">
												<?php echo '<option value="">' . esc_html__('All Size', 'jobportal-framework') . '</option>'; ?>
												<?php jobportal_get_taxonomy('company-size', false, false, false, true, 'company_size_order'); ?>
											</select>
										<?php else: ?>
											<select name="company-size" class="jobportal-ajax-select2" data-taxonomy="<?php echo esc_attr($taxonomy); ?>" data-placeholder="<?php esc_attr_e('Select size', 'jobportal-framework'); ?>">
												<?php
												$selected_value = isset($_GET['company-size']) ? sanitize_text_field($_GET['company-size']) : '';
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
										<?php echo $company_search_icon_size; ?>
									</div>
								<?php break;
								case 'company-location':
									jobportal_content_option_taxonomy('company', 'top');
									break;
								case 'company-categories':
									$company_search_icon_categories = jobportal_get_option('company_search_fields_company-categories');
								?>
									<div class="form-group">
										<?php
										$taxonomy = 'company-categories';
										$term_count = wp_count_terms($taxonomy, ['hide_empty' => false]);
										?>
										<?php if ($term_count <= 150): ?>
											<select name="company_categories" class="jobportal-select2" data-taxonomy="<?php echo esc_attr($taxonomy); ?>">
												<?php echo '<option value="">' . esc_html__('All Categories', 'jobportal-framework') . '</option>'; ?>
												<?php jobportal_get_taxonomy('company-categories', false, false, false, true, 'company_categories_order'); ?>
											</select>
										<?php else: ?>
											<select name="company_categories" class="jobportal-ajax-select2" data-taxonomy="<?php echo esc_attr($taxonomy); ?>">
												<?php echo '<option value="">' . esc_html__('All Categories', 'jobportal-framework') . '</option>'; ?>
											</select>
										<?php endif; ?>
										<?php echo $company_search_icon_categories; ?>
									</div>
								<?php break;
								case 'company-founded':
									$company_search_icon_founded = jobportal_get_option('company_search_fields_company-founded');
								?>
									<div class="form-group">
										<select name="company-founded" class="jobportal-select2">
											<?php echo '<option value="">' . esc_html__('All Founded', 'jobportal-framework') . '</option>'; ?>
											<?php echo jobportal_get_company_founded(true) ?>
										</select>
										<?php echo $company_search_icon_founded; ?>
									</div>
					<?php break;
							}
						}
					endif;
					?>

					<div class="form-group">
						<span class="jobportal-clear-top-filter"><?php esc_html_e('Clear', 'jobportal-framework') ?></span>
						<input type="hidden" name="has_map"
							value="<?php if (isset($_GET['has_map']) && $_GET['has_map'] == '1') {
												echo '1';
											} else {
												echo '0';
											} ?>">
						<button type="submit" class="btn-top-filter jobportal-button" name="company-top-filter">
							<?php esc_html_e('Search', 'jobportal-framework') ?>
							<span class="btn-loading"><i class="fal fa-spinner fa-spin medium"></i></span>
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
<?php }

		/**
		 * Archive company sidebar filter (chuẩn hóa, tương tự jobs)
		 */
		function archive_company_sidebar_filter($current_term, $total_post)
		{
			wp_enqueue_script('jquery-ui-slider');
			$total_post_for_n = max(1, (int) $total_post);
			$key = isset($_GET['s']) ? jobportal_clean(wp_unslash($_GET['s'])) : '';
			$location = isset($_GET['company_location']) ? jobportal_clean(wp_unslash($_GET['company_location'])) : '';
			$filter_classes = array();

			$term_id = '';
			$taxonomy_name = '';



			if (is_tax() && !is_search()) {
				$queried_object = get_queried_object();
				if ($queried_object && !is_wp_error($queried_object) && isset($queried_object->term_id)) {
					$term_id = $queried_object->term_id;
					$taxonomy_name = $queried_object->taxonomy;
				}
			}

			// Build context filters for accurate counting
			$context_filters = array();
			if (!empty($term_id) && !empty($taxonomy_name)) {
				$context_filters['current_term'] = $term_id;
				$context_filters['type_term'] = $taxonomy_name;
			}

			// Add other filters from current request
			$context_filters['location'] = isset($_GET['company_location']) ? jobportal_clean(wp_unslash($_GET['company_location'])) : '';
			$context_filters['categories'] = isset($_GET['company-categories_id']) ? jobportal_clean(wp_unslash($_GET['company-categories_id'])) : '';
			$context_filters['industry'] = isset($_GET['company-industry_id']) ? jobportal_clean(wp_unslash($_GET['company-industry_id'])) : '';
			$context_filters['type'] = isset($_GET['company-type_id']) ? jobportal_clean(wp_unslash($_GET['company-type_id'])) : '';
			$context_filters['size'] = isset($_GET['company-size_id']) ? jobportal_clean(wp_unslash($_GET['company-size_id'])) : '';

			$company_search_fields = jobportal_get_option('company_search_fields');
			$company_search_fields_sidebar = isset($company_search_fields['sidebar']) ? $company_search_fields['sidebar'] : array();
			unset($company_search_fields_sidebar['__no_value__']);

			/* Filter hooks for sidebar fields */
			$company_search_fields_sidebar = apply_filters('jobportal_archive_company_sidebar_filter_fields', $company_search_fields_sidebar, $current_term, $total_post);
			ob_start();
?>
	<div class="archive-filter <?php echo join(' ', $filter_classes); ?>">
		<div class="bg-overlay"></div>
		<div class="inner-filter custom-scrollbar">
			<div class="jobportal-nav-filter">
				<div class="jobportal-filter-toggle">
					<span><?php esc_html_e('Filter', 'jobportal-framework'); ?></span>
				</div>
				<div class="jobportal-clear-filter">
					<i class="far fa-sync fa-spin"></i>
					<span><?php esc_html_e('Clear All', 'jobportal-framework'); ?></span>
				</div>
			</div>
			<div class="jobportal-menu-filter">
				<?php
				if ($company_search_fields_sidebar) :
					foreach ($company_search_fields_sidebar as $field => $v) {
						$order = isset($v['order']) ? $v['order'] : 'title';
						$meta_key = isset($v['meta_key']) ? $v['meta_key'] : '';
						$meta_type = isset($v['meta_type']) ? $v['meta_type'] : 'NUMERIC';

						switch ($field) {
							case 'company-rating': ?>
								<div class="filter-rating">
									<div class="entry-filter">
										<h4><?php esc_html_e('Rating', 'jobportal-framework'); ?></h4>
										<ul class="rating filter-control custom-scrollbar">
											<?php for ($i = 5; $i >= 1; $i--): ?>
												<li>
													<input type="checkbox" id="company_rating_<?php echo $i; ?>" class="custom-checkbox input-control" name="company_rating[]" value="rating_<?php echo $i; ?>" />
													<label for="company_rating_<?php echo $i; ?>">
														<?php for ($j = 1; $j <= $i; $j++): ?>
															<i class="fas fa-star"></i>
														<?php endfor; ?>
													</label>
												</li>
											<?php endfor; ?>
										</ul>
									</div>
								</div>
								<?php
								break;

							case 'company-size':
								if (jobportal_taxonomy_has_terms('company-size')):
									$title = esc_html__('Company Size', 'jobportal-framework');
									get_search_filter_submenu('company-size', $title, true, 'meta_value', 'company_size_order', 'NUMERIC', $context_filters);
								endif;
								break;

							case 'company-location':
								if (jobportal_taxonomy_has_terms('company-location')): ?>
									<div class="entry-filter entry-filter-locations">
										<h4><?php esc_html_e('Locations', 'jobportal-framework'); ?></h4>
										<div class="locations-filter">
											<?php jobportal_content_option_taxonomy('company'); ?>
										</div>
									</div>
								<?php endif;
								break;

							case 'company-categories':
								if (jobportal_taxonomy_has_terms('company-categories')):
									$title = esc_html__('Categories', 'jobportal-framework');
									get_search_filter_submenu('company-categories', $title, true, 'meta_value', 'company_categories_order', 'NUMERIC', $context_filters);
								endif;
								break;

							case 'company-industry':
								if (jobportal_taxonomy_has_terms('company-industry')):
									$title = esc_html__('Industry', 'jobportal-framework');
									get_search_filter_submenu('company-industry', $title, true, $order, $meta_key, $meta_type, $context_filters);
								endif;
								break;

							case 'company-type':
								if (jobportal_taxonomy_has_terms('company-type')):
									$title = esc_html__('Company Type', 'jobportal-framework');
									get_search_filter_submenu('company-type', $title, true, $order, $meta_key, $meta_type, $context_filters);
								endif;
								break;

							case 'company-founded': ?>
								<div class="filter-founded">
									<div class="entry-filter">
										<h4><?php esc_html_e('Founded Date', 'jobportal-framework'); ?></h4>
										<div id="range-slider">
											<div id="slider-range"></div>
											<p><input type="text" id="amount" readonly></p>
										</div>
									</div>
								</div>
				<?php
								break;
						}
					}
				endif;
				?>
			</div>
		</div>
		<div class="show-result">
			<a href="#" class="jobportal-button button-block">
				<span><?php echo esc_html__('Show', 'jobportal-framework'); ?></span>
				<span class="result-count">
					<?php if (!empty($key)) { ?>
						<?php printf(esc_html__('%1$s %2$s for "%3$s"', 'jobportal-framework'), '<span>' . $total_post . '</span>', _n('company', 'companies', $total_post_for_n, 'jobportal-framework'), $key); ?>
					<?php } else { ?>
						<?php printf(esc_html__('%1$s %2$s', 'jobportal-framework'), '<span>' . $total_post . '</span>', _n('company', 'companies', $total_post_for_n, 'jobportal-framework')); ?>
					<?php } ?>
				</span>
			</a>
		</div>
		<input type="hidden" name="search_fields_sidebar" value='<?php echo json_encode($company_search_fields_sidebar); ?>'>
		<input type="hidden" name="current_term" value="<?php echo esc_attr($term_id); ?>">
		<input type="hidden" name="type_term" value="<?php echo esc_attr($taxonomy_name); ?>">
		<input type="hidden" name="title" value="<?php echo esc_attr($key); ?>">
		<input type="hidden" name="company_location" value="<?php echo esc_attr($location); ?>">
	</div>
<?php
			$output = ob_get_clean();
			echo apply_filters('jobportal_archive_company_sidebar_filter_output', $output, $current_term, $total_post);
		}


		/**
		 * Archive candidate top filter
		 */
		function archive_candidate_top_filter()
		{
			wp_enqueue_script('jquery-ui-autocomplete');
			wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'search-autocomplete');
			$candidate_search_fields = jobportal_get_option('candidate_search_fields');
			$candidate_search_fields_top = isset($candidate_search_fields['top']) ? $candidate_search_fields['top'] : array();
			unset($candidate_search_fields_top['__no_value__']);

			$search_color = $search_image = $search_title_color = '';

			$enable_candidate_search_bg = jobportal_get_option('enable_candidate_search_bg');
			$enable_candidate_search_location = jobportal_get_option('enable_candidate_search_location_top', '1');
			$candidate_search_color = jobportal_get_option('candidate_search_color');
			$candidate_search_image = jobportal_get_option('candidate_search_image');
			$enable_candidate_search_location_radius = jobportal_get_option('enable_candidate_search_location_radius');
			$enable_candidate_search_bg = !empty($_GET['has_bg']) ? jobportal_clean(wp_unslash($_GET['has_bg'])) : $enable_candidate_search_bg;
			if ($enable_candidate_search_bg == 1) {
				$class_inner = 'has-bg';
			} else {
				$class_inner = '';
			}
			if (!empty($candidate_search_color)) {
				$search_color = 'color :' . $candidate_search_color . ';';
			}
			if (!empty($candidate_search_image['url'])) {
				$search_image = "background-image : url({$candidate_search_image['url']})";
			}
			$candidate_search_color = jobportal_get_option('candidate_search_color');

			if (!empty($candidate_search_color)) {
				$search_title_color = 'color :' . $candidate_search_color . ';';
			}
			$key = isset($_GET['s']) ? jobportal_clean(wp_unslash($_GET['s'])) : '';
?>
	<div class="archive-candidate-top archive-filter-top <?php echo $class_inner; ?>" <?php if ($enable_candidate_search_bg == 1) { ?> style="<?php echo $search_image ?>" <?php } ?>>
		<div class="container">
			<h2 <?php if ($enable_candidate_search_bg == 1) { ?> style="<?php echo $search_color; ?>" <?php } ?>><?php esc_html_e('Hire people for your business', 'jobportal-framework'); ?></h2>
			<form method="post" class="form-candidate-top-filter form-archive-top-filter">
				<div class="row">
					<?php $candidate_skills = array();
					$taxonomy_skills = get_categories(
						array(
							'taxonomy' => 'candidate_skills',
							'orderby' => 'name',
							'order' => 'ASC',
							'hide_empty' => false,
							'parent' => 0
						)
					);
					if (!empty($taxonomy_skills)) {
						foreach ($taxonomy_skills as $term) {
							$candidate_skills[] = $term->name;
						}
					}
					$candidate_keyword = json_encode($candidate_skills);
					?>
					<div class="form-group">
						<input
							type="text"
							value="<?php echo $key; ?>"
							id="candidate_filter_search"
							name="candidate_filter_search"
							data-key='<?php echo $candidate_keyword ?>'
							data-taxonomy="candidate_skills"
							class="candidate-search-control archive-search-control jobportal-ajax-ui"
							placeholder="<?php esc_attr_e('Candidate title or keywords', 'jobportal-framework') ?>"
							autocomplete="off">
						<span class="btn-filter-search"><i class="far fa-search"></i></span>
						<span class="ui-autocomplete-spinner" style="display:none;">
							<i class="fa fa-spinner fa-spin"></i>
						</span>
					</div>
					<?php if ($enable_candidate_search_location === '1') { ?>
						<?php
						$custom_location_field = apply_filters('jobportal_search_top_candidate_location_field', '', 'candidate_locations', array());
						if (!empty($custom_location_field)) {
							echo $custom_location_field;
						} else {
						?>
							<div class="form-group jobportal-form-location">
								<input
									type="text"
									class="archive-search-location jobportal-ajax-ui"
									name="candidate-search-location"
									data-taxonomy="candidate_locations"
									placeholder="<?php esc_attr_e('All Cities', 'jobportal-framework') ?>"
									value="<?php
													if (isset($_GET['candidate_locations'])) {
														$location_value = $_GET['candidate_locations'];
														if (is_array($location_value)) {
															$location_value = array_filter($location_value);
															if (!empty($location_value)) {
																echo esc_attr(jobportal_clean(wp_unslash($location_value[0])));
															}
														} elseif (!empty($location_value)) {
															echo esc_attr(jobportal_clean(wp_unslash($location_value)));
														}
													}
													?>">
								<span class="ui-autocomplete-spinner" style="display:none;">
									<i class="fa fa-spinner fa-spin"></i>
								</span>
								<select name="candidate-location-top" class="jobportal-select2 hide">
									<?php jobportal_get_taxonomy('candidate_locations', false, false); ?>
								</select>
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
								<span class="icon-arrow">
									<i class="fal fa-angle-down"></i>
								</span>
								<?php if ($enable_candidate_search_location_radius === '1') { ?>
									<span class="radius">
										<span class="labels"><?php esc_html_e('Radius:', 'jobportal-framework') ?></span>
										<input type="number" name="candidate_number_radius" value="" placeholder="0" />
										<span class="distance"><?php esc_html_e('Km', 'jobportal-framework') ?></span>
									</span>
								<?php } ?>
							</div>
						<?php } ?>
					<?php } ?>
					<?php if ($candidate_search_fields_top) : foreach ($candidate_search_fields_top as $field => $v) {
							switch ($field) {
								case 'candidate_rating':
									$candidate_search_icon_ratting = jobportal_get_option('candidate_search_fields_candidate_rating'); ?>
									<div class="form-group">
										<select name="candidate_rating" class="jobportal-select2">
											<option value=""><?php echo esc_html__('All Rating', 'jobportal-framework'); ?></option>
											<option value="rating_five"><?php echo esc_html__('Five Star', 'jobportal-framework'); ?></option>
											<option value="rating_four"><?php echo esc_html__('Four Star', 'jobportal-framework'); ?></option>
											<option value="rating_three"><?php echo esc_html__('Three Star', 'jobportal-framework'); ?></option>
											<option value="rating_two"><?php echo esc_html__('Two Star', 'jobportal-framework'); ?></option>
											<option value="rating_one"><?php echo esc_html__('One Star', 'jobportal-framework'); ?></option>
										</select>
										<?php echo $candidate_search_icon_ratting; ?>
									</div>
								<?php break;
								case 'candidate_gender':
									$candidate_search_icon_gender = jobportal_get_option('candidate_search_fields_candidate_gender');
									$taxonomy = 'candidate_gender';
									$term_count = wp_count_terms($taxonomy, ['hide_empty' => false]);
								?>
									<div class="form-group">
										<?php if ($term_count <= 150): ?>
											<select name="candidate_gender" class="jobportal-select2">
												<?php echo '<option value="">' . esc_html__('All Gender', 'jobportal-framework') . '</option>'; ?>
												<?php jobportal_get_taxonomy('candidate_gender', false, false, false, true, 'candidate_gender_order'); ?>
											</select>
										<?php else: ?>
											<select name="candidate_gender" class="jobportal-ajax-select2" data-taxonomy="<?php echo esc_attr($taxonomy); ?>" data-placeholder="<?php esc_attr_e('Select gender', 'jobportal-framework'); ?>">
												<?php
												$selected_value = isset($_GET['candidate_gender']) ? sanitize_text_field($_GET['candidate_gender']) : '';
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
										<?php echo $candidate_search_icon_gender; ?>
									</div>
								<?php break;
								case 'candidate_locations':
									jobportal_content_option_taxonomy('candidate', 'top');
									break;
								case 'candidate_categories':
									$candidate_search_icon_categories = jobportal_get_option('candidate_search_fields_candidate_categories');
								?>
									<div class="form-group">
										<?php
										$taxonomy = 'candidate_categories';
										$term_count = wp_count_terms($taxonomy, ['hide_empty' => false]);

										if ($term_count <= 150): ?>
											<select data-placeholder="<?php esc_attr_e('Select categories', 'jobportal-framework'); ?>"
												class="jobportal-select2" name="candidate_categories" data-taxonomy="<?php echo esc_attr($taxonomy); ?>">
												<?php echo '<option value="">' . esc_html__('All Categories', 'jobportal-framework') . '</option>'; ?>
												<?php jobportal_get_taxonomy($taxonomy, false, false, false, true, 'candidate_categories_order'); ?>
											</select>
										<?php else: ?>
											<select data-placeholder="<?php esc_attr_e('Select categories', 'jobportal-framework'); ?>"
												class="jobportal-ajax-select2" name="candidate_categories" data-taxonomy="<?php echo esc_attr($taxonomy); ?>">
											</select>
										<?php endif; ?>
										<?php echo $candidate_search_icon_categories; ?>
									</div>
								<?php break;
								case 'candidate_yoe':
									$candidate_search_icon_yoe = jobportal_get_option('candidate_search_fields_candidate_yoe');
									$taxonomy = 'candidate_yoe';
									$term_count = wp_count_terms($taxonomy, ['hide_empty' => false]);
								?>
									<div class="form-group">
										<?php if ($term_count <= 150): ?>
											<select name="candidate_yoe" class="jobportal-select2">
												<?php echo '<option value="">' . esc_html__('All Experience', 'jobportal-framework') . '</option>'; ?>
												<?php jobportal_get_taxonomy('candidate_yoe', false, false, false, true, 'candidate_experience_order'); ?>
											</select>
										<?php else: ?>
											<select name="candidate_yoe" class="jobportal-ajax-select2" data-taxonomy="<?php echo esc_attr($taxonomy); ?>" data-placeholder="<?php esc_attr_e('Select experience', 'jobportal-framework'); ?>">
												<?php
												$selected_value = isset($_GET['candidate_yoe']) ? sanitize_text_field($_GET['candidate_yoe']) : '';
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
										<?php echo $candidate_search_icon_yoe; ?>
									</div>
								<?php break;
								case 'candidate_qualification':
									$candidate_search_icon_qualification = jobportal_get_option('candidate_search_fields_candidate_qualification');
									$taxonomy = 'candidate_qualification';
									$term_count = wp_count_terms($taxonomy, ['hide_empty' => false]);
								?>
									<div class="form-group">
										<?php if ($term_count <= 150): ?>
											<select name="candidate_qualification" class="jobportal-select2">
												<?php echo '<option value="">' . esc_html__('All Qualification', 'jobportal-framework') . '</option>'; ?>
												<?php jobportal_get_taxonomy('candidate_qualification', false, false, false, true, 'candidate_qualification_order'); ?>
											</select>
										<?php else: ?>
											<select name="candidate_qualification" class="jobportal-ajax-select2" data-taxonomy="<?php echo esc_attr($taxonomy); ?>" data-placeholder="<?php esc_attr_e('Select qualification', 'jobportal-framework'); ?>">
												<?php
												$selected_value = isset($_GET['candidate_qualification']) ? sanitize_text_field($_GET['candidate_qualification']) : '';
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
										<?php echo $candidate_search_icon_qualification; ?>
									</div>
								<?php break;
								case 'candidate_ages':
									$candidate_search_icon_ages = jobportal_get_option('candidate_search_fields_candidate_ages');
									$taxonomy = 'candidate_ages';
									$term_count = wp_count_terms($taxonomy, ['hide_empty' => false]);
								?>
									<div class="form-group">
										<?php if ($term_count <= 150): ?>
											<select name="candidate_ages" class="jobportal-select2">
												<?php echo '<option value="">' . esc_html__('All Ages', 'jobportal-framework') . '</option>'; ?>
												<?php jobportal_get_taxonomy('candidate_ages', false, false, false, true, 'candidate_ages_order'); ?>
											</select>
										<?php else: ?>
											<select name="candidate_ages" class="jobportal-ajax-select2" data-taxonomy="<?php echo esc_attr($taxonomy); ?>" data-placeholder="<?php esc_attr_e('Select age', 'jobportal-framework'); ?>">
												<?php
												$selected_value = isset($_GET['candidate_ages']) ? sanitize_text_field($_GET['candidate_ages']) : '';
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
										<?php echo $candidate_search_icon_ages; ?>
									</div>
								<?php break;

								case 'candidate_skills':
									$candidate_search_icon_skills = jobportal_get_option('candidate_search_fields_candidate_skills');
									$taxonomy = 'candidate_skills';
									$term_count = wp_count_terms($taxonomy, ['hide_empty' => false]);
								?>
									<div class="form-group">
										<?php if ($term_count <= 150): ?>
											<select name="candidate_skills" class="jobportal-select2">
												<?php echo '<option value="">' . esc_html__('All Skills', 'jobportal-framework') . '</option>'; ?>
												<?php jobportal_get_taxonomy('candidate_skills', false, false, false, true, 'candidate_skills_order'); ?>
											</select>
										<?php else: ?>
											<select name="candidate_skills" class="jobportal-ajax-select2" data-taxonomy="<?php echo esc_attr($taxonomy); ?>" data-placeholder="<?php esc_attr_e('Select skills', 'jobportal-framework'); ?>">
												<?php
												$selected_value = isset($_GET['candidate_skills']) ? sanitize_text_field($_GET['candidate_skills']) : '';
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
										<?php echo $candidate_search_icon_skills; ?>
									</div>
								<?php break;
								case 'candidate_languages':
									$candidate_search_icon_languages = jobportal_get_option('candidate_search_fields_candidate_languages');
									$taxonomy = 'candidate_languages';
									$term_count = wp_count_terms($taxonomy, ['hide_empty' => false]);
								?>
									<div class="form-group">
										<?php if ($term_count <= 150): ?>
											<select name="candidate_languages" class="jobportal-select2">
												<?php echo '<option value="">' . esc_html__('All Languages', 'jobportal-framework') . '</option>'; ?>
												<?php jobportal_get_taxonomy('candidate_languages', false, false, false, true, 'candidate_languages_order'); ?>
											</select>
										<?php else: ?>
											<select name="candidate_languages" class="jobportal-ajax-select2" data-taxonomy="<?php echo esc_attr($taxonomy); ?>" data-placeholder="<?php esc_attr_e('Select languages', 'jobportal-framework'); ?>">
												<?php
												$selected_value = isset($_GET['candidate_languages']) ? sanitize_text_field($_GET['candidate_languages']) : '';
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
										<?php echo $candidate_search_icon_languages; ?>
									</div>
					<?php break;
							}
						}
					endif;
					?>
					<div class="form-group">
						<span class="jobportal-clear-top-filter"><?php esc_html_e('Clear', 'jobportal-framework') ?></span>
						<input type="hidden" name="has_map" value="<?php if (isset($_GET['has_map']) && $_GET['has_map'] == '1') {
																													echo '1';
																												} else {
																													echo '0';
																												} ?>">
						<button type="submit" class="btn-top-filter jobportal-button" name="candidate-top-filter">
							<?php esc_html_e('Search', 'jobportal-framework') ?>
							<span class="btn-loading"><i class="fal fa-spinner fa-spin medium"></i></span>
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
<?php }

		/**
		 * Archive candidate sidebar filter
		 */
		function archive_candidate_sidebar_filter($current_term, $total_post)
		{
			$key = isset($_GET['s']) ? jobportal_clean(wp_unslash($_GET['s'])) : '';
			$total_post_for_n = max(1, (int) $total_post);
			$filter_classes = array();

			$term_id = '';
			$taxonomy_name = '';



			if (is_tax() && !is_search()) {
				$queried_object = get_queried_object();
				if ($queried_object && !is_wp_error($queried_object) && isset($queried_object->term_id)) {
					$term_id = $queried_object->term_id;
					$taxonomy_name = $queried_object->taxonomy;
				}
			}

			// Build context filters for accurate counting
			$context_filters = array();
			if (!empty($term_id) && !empty($taxonomy_name)) {
				$context_filters['current_term'] = $term_id;
				$context_filters['type_term'] = $taxonomy_name;
			}

			// Add other filters from current request
			$context_filters['location'] = isset($_GET['candidate_location']) ? jobportal_clean(wp_unslash($_GET['candidate_location'])) : '';
			$context_filters['categories'] = isset($_GET['candidate_categories_id']) ? jobportal_clean(wp_unslash($_GET['candidate_categories_id'])) : '';
			$context_filters['qualification'] = isset($_GET['candidate_qualification_id']) ? jobportal_clean(wp_unslash($_GET['candidate_qualification_id'])) : '';
			$context_filters['ages'] = isset($_GET['candidate_ages_id']) ? jobportal_clean(wp_unslash($_GET['candidate_ages_id'])) : '';
			$context_filters['skills'] = isset($_GET['candidate_skills_id']) ? jobportal_clean(wp_unslash($_GET['candidate_skills_id'])) : '';
			$context_filters['languages'] = isset($_GET['candidate_languages_id']) ? jobportal_clean(wp_unslash($_GET['candidate_languages_id'])) : '';
			$context_filters['gender'] = isset($_GET['candidate_gender_id']) ? jobportal_clean(wp_unslash($_GET['candidate_gender_id'])) : '';
			$context_filters['yoe'] = isset($_GET['candidate_yoe_id']) ? jobportal_clean(wp_unslash($_GET['candidate_yoe_id'])) : '';

			$candidate_search_fields = jobportal_get_option('candidate_search_fields');
			$candidate_search_fields_sidebar = isset($candidate_search_fields['sidebar']) ? $candidate_search_fields['sidebar'] : array();
			unset($candidate_search_fields_sidebar['__no_value__']);

			// Hook handle search fields sidebar for candidate
			$candidate_search_fields_sidebar = apply_filters('jobportal_archive_candidate_sidebar_filter_fields', $candidate_search_fields_sidebar, $current_term, $total_post);

			ob_start();
?>
	<div class="archive-filter <?php echo join(' ', $filter_classes); ?>">
		<div class="bg-overlay"></div>
		<div class="inner-filter custom-scrollbar">
			<div class="jobportal-nav-filter">
				<div class="jobportal-filter-toggle">
					<span><?php esc_html_e('Filter', 'jobportal-framework'); ?></span>
				</div>
				<div class="jobportal-clear-filter">
					<i class="far fa-sync fa-spin"></i>
					<span><?php esc_html_e('Clear All', 'jobportal-framework'); ?></span>
				</div>
			</div>
			<div class="jobportal-menu-filter">
				<?php
				if ($candidate_search_fields_sidebar) :
					foreach ($candidate_search_fields_sidebar as $field => $v) {
						$order = isset($v['order']) ? $v['order'] : 'title';
						$meta_key = isset($v['meta_key']) ? $v['meta_key'] : '';
						$meta_type = isset($v['meta_type']) ? $v['meta_type'] : 'NUMERIC';

						switch ($field) {
							case 'candidate_rating': ?>
								<div class="filter-rating">
									<div class="entry-filter">
										<h4><?php esc_html_e('Rating', 'jobportal-framework'); ?></h4>
										<ul class="rating filter-control custom-scrollbar">
											<?php for ($i = 5; $i >= 1; $i--): ?>
												<li>
													<input
														type="checkbox"
														id="candidate_rating_<?php echo $i; ?>"
														class="custom-checkbox input-control"
														name="candidate_rating[]"
														value="rating_<?php echo $i; ?>" />
													<label for="candidate_rating_<?php echo $i; ?>">
														<?php for ($j = 1; $j <= $i; $j++): ?>
															<i class="fas fa-star"></i>
														<?php endfor; ?>
													</label>
												</li>
											<?php endfor; ?>
										</ul>
									</div>
								</div>
								<?php
								break;

							case 'candidate_yoe':
								if (jobportal_taxonomy_has_terms('candidate_yoe')):
									$title = esc_html__('Experience Level', 'jobportal-framework');
									get_search_filter_submenu('candidate_yoe', $title, true, 'meta_value', 'candidate_experience_order', 'NUMERIC', $context_filters);
								endif;
								break;

							case 'candidate_locations':
								// Check if there is location data
								$has_locations = jobportal_taxonomy_has_terms('candidate_locations') || jobportal_taxonomy_has_terms('candidate_state');
								if ($has_locations) : ?>
									<div class="entry-filter entry-filter-locations">
										<h4><?php esc_html_e('Locations', 'jobportal-framework'); ?></h4>
										<div class="locations-filter">
											<?php jobportal_content_option_taxonomy('candidate'); ?>
										</div>
									</div>
				<?php endif;
								break;

							case 'candidate_categories':
								if (jobportal_taxonomy_has_terms('candidate_categories')):
									$title = esc_html__('Categories', 'jobportal-framework');
									get_search_filter_submenu('candidate_categories', $title, true, 'meta_value', 'candidate_categories_order', 'NUMERIC', $context_filters);
								endif;
								break;

							case 'candidate_qualification':
								if (jobportal_taxonomy_has_terms('candidate_qualification')):
									$title = esc_html__('Qualification', 'jobportal-framework');
									get_search_filter_submenu('candidate_qualification', $title, true, 'meta_value', 'candidate_qualification_order', 'NUMERIC', $context_filters);
								endif;
								break;

							case 'candidate_ages':
								if (jobportal_taxonomy_has_terms('candidate_ages')):
									$title = esc_html__('Ages', 'jobportal-framework');
									get_search_filter_submenu('candidate_ages', $title, true, 'meta_value', 'candidate_ages_order', 'NUMERIC', $context_filters);
								endif;
								break;

							case 'candidate_skills':
								if (jobportal_taxonomy_has_terms('candidate_skills')):
									$title = esc_html__('Skills', 'jobportal-framework');
									get_search_filter_submenu('candidate_skills', $title, true, 'meta_value', 'candidate_skills_order', 'NUMERIC', $context_filters);
								endif;
								break;

							case 'candidate_languages':
								if (jobportal_taxonomy_has_terms('candidate_languages')):
									$title = esc_html__('Languages', 'jobportal-framework');
									get_search_filter_submenu('candidate_languages', $title, true, 'meta_value', 'candidate_languages_order', 'NUMERIC', $context_filters);
								endif;
								break;

							case 'candidate_gender':
								if (jobportal_taxonomy_has_terms('candidate_gender')):
									$title = esc_html__('Gender', 'jobportal-framework');
									get_search_filter_submenu('candidate_gender', $title, true, 'meta_value', 'candidate_gender_order', 'NUMERIC', $context_filters);
								endif;
								break;
						}
					}
				endif;
				?>
			</div>
		</div>
		<div class="show-result">
			<a href="#" class="jobportal-button button-block">
				<span><?php echo esc_html__('Show', 'jobportal-framework'); ?></span>
				<span class="result-count">
					<?php if (!empty($key)) { ?>
						<?php printf(esc_html__('%1$s %2$s for "%3$s"', 'jobportal-framework'), '<span>' . $total_post . '</span>', _n('candidate', 'candidates', $total_post_for_n, 'jobportal-framework'), $key); ?>
					<?php } else { ?>
						<?php printf(esc_html__('%1$s %2$s', 'jobportal-framework'), '<span>' . $total_post . '</span>', _n('candidate', 'candidates', $total_post_for_n, 'jobportal-framework')); ?>
					<?php } ?>
				</span>
			</a>
		</div>
		<input type="hidden" name="search_fields_sidebar" value='<?php echo json_encode($candidate_search_fields_sidebar); ?>'>
		<input type="hidden" name="current_term" value="<?php echo esc_attr($term_id); ?>">
		<input type="hidden" name="type_term" value="<?php echo esc_attr($taxonomy_name); ?>">
		<input type="hidden" name="title" value="<?php echo esc_attr($key); ?>">
	</div>
<?php
			$output = ob_get_clean();
			echo apply_filters('jobportal_archive_candidate_sidebar_filter_output', $output, $current_term, $total_post);
		}

		/**
		 * archive service top filter
		 */
		function archive_service_top_filter()
		{
			wp_enqueue_script('jquery-ui-autocomplete');
			wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'search-autocomplete');

			$service_search_fields = jobportal_get_option('service_search_fields');
			$service_search_fields_top = isset($service_search_fields['top']) ? $service_search_fields['top'] : array();
			unset($service_search_fields_top['__no_value__']);

			$search_color = $search_image = '';
			$enable_service_search_bg = jobportal_get_option('enable_service_search_bg');
			$enable_service_search_location = jobportal_get_option('enable_service_search_location_top', '1');
			$service_search_color = jobportal_get_option('service_search_color');
			$service_search_image = jobportal_get_option('service_search_image');
			$enable_service_search_location_radius = jobportal_get_option('enable_service_search_location_radius');
			$enable_service_search_bg = !empty($_GET['has_bg']) ? jobportal_clean(wp_unslash($_GET['has_bg'])) : $enable_service_search_bg;
			if ($enable_service_search_bg == 1) {
				$class_inner = 'has-bg';
			} else {
				$class_inner = '';
			}
			if (!empty($service_search_color)) {
				$search_color = 'background-color :' . $service_search_color . ';';
			}
			if (!empty($service_search_image['url'])) {
				$search_image = "background-image : url({$service_search_image['url']})";
			}
?>
	<div class="archive-service-top archive-filter-top <?php echo $class_inner; ?>" <?php if ($enable_service_search_bg == 1) { ?> style="<?php echo $search_color . $search_image ?>" <?php } ?>>
		<div class="container">
			<h2><?php esc_html_e('Service Listing', 'jobportal-framework'); ?></h2>
			<form method="post" class="form-service-top-filter form-archive-top-filter">
				<div class="row">
					<?php $service_skills = array();
					$taxonomy_skills = get_categories(
						array(
							'taxonomy' => 'service-skills',
							'orderby' => 'name',
							'order' => 'ASC',
							'hide_empty' => false,
							'parent' => 0
						)
					);
					if (!empty($taxonomy_skills)) {
						foreach ($taxonomy_skills as $term) {
							$service_skills[] = $term->name;
						}
					}
					$service_keyword = json_encode($service_skills);
					$id = apply_filters('jobportal/search-control/id', 'service_filter_search');
					?>
					<div class="form-group">
						<input class="service-search-control archive-search-control" data-key='<?php echo $service_keyword ?>' id="<?php echo esc_attr($id); ?>" type="text" name="service_filter_search" placeholder="<?php esc_attr_e('Service title or keywords', 'jobportal-framework') ?>" autocomplete="off">
						<span class="btn-filter-search"><i class="far fa-search"></i></span>
					</div>
					<?php if ($enable_service_search_location === '1') { ?>
						<div class="form-group jobportal-form-location">
							<input class="archive-search-location jobportal-ajax-ui" type="text" name="service-search-location"
								data-taxonomy="service-location"
								placeholder="<?php esc_attr_e('All Cities', 'jobportal-framework') ?>"
								value="<?php
												if (isset($_GET['service-location'])) {
													$location_value = $_GET['service-location'];
													if (is_array($location_value)) {
														$location_value = array_filter($location_value);
														if (!empty($location_value)) {
															echo esc_attr(jobportal_clean(wp_unslash($location_value[0])));
														}
													} elseif (!empty($location_value)) {
														echo esc_attr(jobportal_clean(wp_unslash($location_value)));
													}
												}
												?>">
							<span class="ui-autocomplete-spinner" style="display:none;">
								<i class="fa fa-spinner fa-spin"></i>
							</span>
							<select name="service-location-top" class="jobportal-select2 hide">
								<?php jobportal_get_taxonomy('service-location', false, false); ?>
							</select>
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
							<span class="icon-arrow">
								<i class="fal fa-angle-down"></i>
							</span>
							<?php if ($enable_service_search_location_radius === '1') { ?>
								<span class="radius">
									<span class="labels"><?php esc_html_e('Radius:', 'jobportal-framework') ?></span>
									<input type="number" name="service_number_radius" value="" placeholder="0" />
									<span class="distance"><?php esc_html_e('Km', 'jobportal-framework') ?></span>
								</span>
							<?php } ?>
						</div>
					<?php } ?>
					<?php if ($service_search_fields_top) : foreach ($service_search_fields_top as $field => $v) {
							switch ($field) {
								case 'service-rating':
									$service_search_icon_ratting = jobportal_get_option('service_search_fields_service-rating'); ?>
									<div class="form-group">
										<select name="service-rating" class="jobportal-select2">
											<option value=""><?php echo esc_html__('All Rating', 'jobportal-framework'); ?></option>
											<option value="rating_five"><?php echo esc_html__('Five Star', 'jobportal-framework'); ?></option>
											<option value="rating_four"><?php echo esc_html__('Four Star', 'jobportal-framework'); ?></option>
											<option value="rating_three"><?php echo esc_html__('Three Star', 'jobportal-framework'); ?></option>
											<option value="rating_two"><?php echo esc_html__('Two Star', 'jobportal-framework'); ?></option>
											<option value="rating_one"><?php echo esc_html__('One Star', 'jobportal-framework'); ?></option>
										</select>
										<?php echo $service_search_icon_ratting; ?>
									</div>
								<?php break;
								case 'service-language-level':
									$service_search_icon_language_level = jobportal_get_option('service_search_fields_service-language-level'); ?>
									<div class="form-group">
										<select name="service-language-level" class="jobportal-select2">
											<option value=""><?php echo esc_html__('All Languages Level', 'jobportal-framework'); ?></option>
											<option value="basic"><?php echo esc_html__('Basic', 'jobportal-framework'); ?></option>
											<option value="conversational"><?php echo esc_html__('Conversational', 'jobportal-framework'); ?></option>
											<option value="fluent"><?php echo esc_html__('Fluent', 'jobportal-framework'); ?></option>
											<option value="native"><?php echo esc_html__('Native or Bilingual', 'jobportal-framework'); ?></option>
											<option value="professional"><?php echo esc_html__('Professional', 'jobportal-framework'); ?></option>
										</select>
										<?php echo $service_search_icon_language_level; ?>
									</div>
								<?php break;
								case 'service-skills':
									$service_search_icon_skills = jobportal_get_option('service_search_fields_service-skills');
								?>
									<div class="form-group">
										<select name="service-skills" class="jobportal-select2">
											<?php echo '<option value="">' . esc_html__('All Skills', 'jobportal-framework') . '</option>'; ?>
											<?php jobportal_get_taxonomy('service-skills', false, false, false, true, 'service_skills_order'); ?>
										</select>
										<?php echo $service_search_icon_skills; ?>
									</div>
								<?php break;
								case 'service-location':
									jobportal_content_option_taxonomy('service', 'top');
									break;
								case 'service-categories':
									$service_search_icon_categories = jobportal_get_option('service_search_fields_service-categories');
								?>
									<div class="form-group">
										<select name="service-categories" class="jobportal-select2">
											<?php echo '<option value="">' . esc_html__('All Categories', 'jobportal-framework') . '</option>'; ?>
											<?php jobportal_get_taxonomy('service-categories', false, false, false, true, 'service_categories_order'); ?>
										</select>
										<?php echo $service_search_icon_categories; ?>
									</div>
								<?php break;
								case 'service-language':
									$service_search_icon_language = jobportal_get_option('service_search_fields_service-language');
								?>
									<div class="form-group">
										<select name="service-language" class="jobportal-select2">
											<?php echo '<option value="">' . esc_html__('All Languages', 'jobportal-framework') . '</option>'; ?>
											<?php jobportal_get_taxonomy('service-language', false, false, false, true, 'service_language_order'); ?>
										</select>
										<?php echo $service_search_icon_language; ?>
									</div>
					<?php break;
							}
						}
					endif;
					?>

					<div class="form-group">
						<span class="jobportal-clear-top-filter"><?php esc_html_e('Clear', 'jobportal-framework') ?></span>
						<button type="submit" class="btn-top-filter jobportal-button" name="service-top-filter">
							<?php esc_html_e('Search', 'jobportal-framework') ?>
							<span class="btn-loading"><i class="fal fa-spinner fa-spin medium"></i></span>
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
<?php }

		/**
		 * archive service sidebar filter
		 */
		function archive_service_sidebar_filter($current_term, $total_post)
		{
			wp_enqueue_script('jquery-ui-slider');
			$total_post_for_n = max(1, (int) $total_post);
			$filter_classes = array();
			$term_id = '';
			$taxonomy_name = '';

			if (is_tax() && !is_search()) {
				$queried_object = get_queried_object();
				if ($queried_object && !is_wp_error($queried_object) && isset($queried_object->term_id)) {
					$term_id = $queried_object->term_id;
					$taxonomy_name = $queried_object->taxonomy;
				}
			}

			// Build context filters for accurate counting
			$context_filters = array();
			if (!empty($term_id) && !empty($taxonomy_name)) {
				$context_filters['current_term'] = $term_id;
				$context_filters['type_term'] = $taxonomy_name;
			}

			// Add other filters from current request
			$context_filters['categories'] = isset($_GET['service-categories_id']) ? jobportal_clean(wp_unslash($_GET['service-categories_id'])) : '';
			$context_filters['skills'] = isset($_GET['service-skills_id']) ? jobportal_clean(wp_unslash($_GET['service-skills_id'])) : '';
			$context_filters['language'] = isset($_GET['service-language_id']) ? jobportal_clean(wp_unslash($_GET['service-language_id'])) : '';

			$service_search_fields = jobportal_get_option('service_search_fields');
			$service_search_fields_sidebar = isset($service_search_fields['sidebar']) ? $service_search_fields['sidebar'] : array();
			unset($service_search_fields_sidebar['__no_value__']);
?>
	<div class="archive-filter <?php echo join(' ', $filter_classes); ?>">
		<div class="bg-overlay"></div>
		<div class="inner-filter custom-scrollbar">
			<div class="jobportal-nav-filter">
				<div class="jobportal-filter-toggle">
					<span><?php esc_html_e('Filter', 'jobportal-framework'); ?></span>
				</div>
				<div class="jobportal-clear-filter">
					<i class="far fa-sync fa-spin"></i>
					<span><?php esc_html_e('Clear All', 'jobportal-framework'); ?></span>
				</div>
			</div>
			<div class="jobportal-menu-filter">
				<?php
				if ($service_search_fields_sidebar) : foreach ($service_search_fields_sidebar as $field => $v) {
						switch ($field) {
							case 'service-rating': ?>
								<div class="filter-rating">
									<div class="entry-filter">
										<h4><?php esc_html_e('Rating', 'jobportal-framework'); ?></h4>
										<ul class="rating filter-control custom-scrollbar">
											<li>
												<input type="checkbox" id="service_rating_five" class="custom-checkbox input-control" name="service_rating[]" value="rating_five" />
												<label for="service_rating_five">
													<i class="fas fa-star"></i>
													<i class="fas fa-star"></i>
													<i class="fas fa-star"></i>
													<i class="fas fa-star"></i>
													<i class="fas fa-star"></i>
												</label>
											</li>
											<li>
												<input type="checkbox" id="service_rating_four" class="custom-checkbox input-control" name="service_rating[]" value="rating_four" />
												<label for="service_rating_four">
													<i class="fas fa-star"></i>
													<i class="fas fa-star"></i>
													<i class="fas fa-star"></i>
													<i class="fas fa-star"></i>
												</label>
											</li>
											<li>
												<input type="checkbox" id="service_rating_three" class="custom-checkbox input-control" name="service_rating[]" value="rating_three" />
												<label for="service_rating_three">
													<i class="fas fa-star"></i>
													<i class="fas fa-star"></i>
													<i class="fas fa-star"></i>
												</label>
											</li>
											<li>
												<input type="checkbox" id="service_rating_two" class="custom-checkbox input-control" name="service_rating[]" value="rating_two" />
												<label for="service_rating_two">
													<i class="fas fa-star"></i>
													<i class="fas fa-star"></i>
												</label>
											</li>
											<li>
												<input type="checkbox" id="service_rating_one" class="custom-checkbox input-control" name="service_rating[]" value="rating_one" />
												<label for="service_rating_one">
													<i class="fas fa-star"></i>
												</label>
											</li>
										</ul>
									</div>
								</div>
							<?php break;
							case 'service-price':
							?>
								<div class="filter-price">
									<div class="entry-filter">
										<h4><?php esc_html_e('Price', 'jobportal-framework'); ?></h4>
										<div class="price-filter">
											<div class="filter filter-price-min">
												<input type="number" name="service_filter_price_min" placeholder="<?php echo esc_attr__('Min', 'jobportal-framework') ?>" />
											</div>
											<div class="filter filter-price-max">
												<input type="number" name="service_filter_price_max" placeholder="<?php echo esc_attr__('Max', 'jobportal-framework') ?>" />
											</div>
											<select name="service_time_type" class="jobportal-select2">
												<option value=""><?php esc_html_e('Type', 'jobportal-framework') ?></option>
												<option value="hr"><?php esc_html_e('Hour', 'jobportal-framework') ?></option>
												<option value="day"><?php esc_html_e('Day', 'jobportal-framework') ?></option>
												<option value="week"><?php esc_html_e('Week', 'jobportal-framework') ?></option>
												<option value="month"><?php esc_html_e('Month', 'jobportal-framework') ?></option>
											</select>
										</div>
									</div>
								</div>
							<?php
								break;
							case 'service-language-level':
								$list_language = array(
									'basic' => esc_html__('Basic', 'jobportal-framework'),
									'conversational' => esc_html__('Conversational', 'jobportal-framework'),
									'fluent' => esc_html__('Fluent', 'jobportal-framework'),
									'native' => esc_html__('Native or Bilingual', 'jobportal-framework'),
									'professional' => esc_html__('Professional', 'jobportal-framework'),
								)
							?>
								<div class="filter-language-level">
									<div class="entry-filter">
										<h4><?php esc_html_e('Languages Level', 'jobportal-framework'); ?></h4>
										<ul class="filter-control custom-scrollbar">
											<?php foreach ($list_language as $keys => $value) { ?>
												<li>
													<input type="checkbox" id="service_language_<?php echo $keys; ?>" class="custom-checkbox input-control" name="service_language_level[]" value="<?php echo $keys; ?>" />
													<label for="service_language_<?php echo $keys; ?>">
														<?php echo $value; ?><span class="count">(<?php echo jobportal_field_count($keys, JOBPORTAL_METABOX_PREFIX . 'service_language_level', 'service'); ?>)</span>
													</label>
												</li>
											<?php } ?>
										</ul>
									</div>
								</div>
								<?php break;
							case 'service-location':
								if (jobportal_taxonomy_has_terms('service-location')): ?>
									<div class="entry-filter entry-filter-locations">
										<h4><?php esc_html_e('Locations', 'jobportal-framework'); ?></h4>
										<div class="locations-filter">
											<?php jobportal_content_option_taxonomy('service'); ?>
										</div>
									</div>
				<?php endif;
								break;
							case 'service-categories':
								if (jobportal_taxonomy_has_terms('service-categories')):
									$title = esc_html__('Categories', 'jobportal-framework');
									get_search_filter_submenu('service-categories', $title, true, 'meta_value', 'service_categories_order', 'NUMERIC', $context_filters);
								endif;
								break;
							case 'service-skills':
								if (jobportal_taxonomy_has_terms('service-skills')):
									$title = esc_html__('Skills', 'jobportal-framework');
									get_search_filter_submenu('service-skills', $title, true, 'meta_value', 'service_skills_order', 'NUMERIC', $context_filters);
								endif;
								break;
							case 'service-language':
								if (jobportal_taxonomy_has_terms('service-language')):
									$title = esc_html__('Language', 'jobportal-framework');
									get_search_filter_submenu('service-language', $title, true, 'meta_value', 'service_language_order', 'NUMERIC', $context_filters);
								endif;
								break;
						}
					}
				endif;
				?>
			</div>
		</div>
		<div class="show-result">
			<a href="#" class="jobportal-button button-block">
				<span><?php echo esc_html__('Show', 'jobportal-framework'); ?></span>
				<span class="result-count">
					<?php if (!empty($key)) { ?>
						<?php printf(esc_html__('%1$s %2$s for "%3$s"', 'jobportal-framework'), '<span>' . $total_post . '</span>', _n('service', 'services', $total_post_for_n, 'jobportal-framework'), $key); ?>
					<?php } else { ?>
						<?php printf(esc_html__('%1$s %2$s', 'jobportal-framework'), '<span>' . $total_post . '</span>', _n('service', 'services', $total_post_for_n, 'jobportal-framework')); ?>
					<?php } ?>
				</span>
			</a>
		</div>
		<input type="hidden" name="search_fields_sidebar" value='<?php echo json_encode($service_search_fields_sidebar); ?>'>
		<input type="hidden" name="current_term" value="<?php echo esc_attr($term_id); ?>">
		<input type="hidden" name="type_term" value="<?php echo esc_attr($taxonomy_name); ?>">
	</div>
<?php
		}

		// jobportal_oembed_get
		function jobportal_oembed_get($url, $args = '')
		{
			if ($url) {
				// Manually build the IFRAME embed with the related videos option disabled and autoplay turned on
				if (preg_match("/youtube.com\/watch\?v=([^&]+)/i", $url, $aMatch)) {
					return '<iframe width="560" height="315" src="http://www.youtube.com/embed/' . $aMatch[1] . '?rel=0&autoplay=1&controls=0&loop=1&mute=1&disablekb=1" allowfullscreen></iframe>';
				}

				require_once(ABSPATH . WPINC . '/class-oembed.php');
				$oembed = _wp_oembed_get_object();
				return $oembed->get_html($url, $args);
			}
		}

		/**
		 * sidebar jobs
		 */
		function sidebar_jobs()
		{
			jobportal_get_template('global/sidebar-jobs.php');
		}

		/**
		 * single jobs head
		 */
		function single_jobs_head($job_id)
		{
			jobportal_get_template('jobs/single/head.php', array(
				'job_id' => $job_id,
			));
		}

		/**
		 * Open Job Detail Tab
		 */
		function single_jobs_tabs($job_id)
		{
			echo '<div class="tab-content">';
			jobportal_get_template('jobs/single/insights.php', array(
				'job_id' => $job_id,
			));
			echo '</div>';
		}

		/**
		 * single jobs meta
		 */
		function single_jobs_insights($job_id)
		{
			jobportal_get_template('jobs/single/insights.php', array(
				'job_id' => $job_id,
			));
		}

		/**
		 * single jobs short description
		 */
		function single_jobs_short_description($job_id)
		{
			jobportal_get_template('jobs/single/short-description.php', array(
				'job_id' => $job_id,
			));
		}

		/**
		 * single jobs description
		 */
		function single_jobs_description($job_id)
		{
			jobportal_get_template('jobs/single/description.php', array(
				'job_id' => $job_id,
			));
		}

		/**
		 * single jobs_thumbnail
		 */
		function single_jobs_thumbnail($job_id)
		{
			jobportal_get_template('jobs/single/thumbnail.php', array(
				'job_id' => $job_id,
			));
		}

		/**
		 * single jobs skills
		 */
		function single_jobs_skills($job_id)
		{
			jobportal_get_template('jobs/single/skills.php', array(
				'job_id' => $job_id,
			));
		}

		/**
		 * single jobs map
		 */
		function single_jobs_map($job_id)
		{
			jobportal_get_template('jobs/single/map.php', array(
				'job_id' => $job_id,
			));
		}

		/**
		 * single jobs video
		 */
		function single_jobs_video($job_id)
		{
			jobportal_get_template('jobs/single/video.php', array(
				'job_id' => $job_id,
			));
		}

		/**
		 * single jobs gallery
		 */
		function gallery_jobs($job_id)
		{
			jobportal_get_template('jobs/single/gallery.php', array(
				'job_id' => $job_id,
			));
		}

		function single_jobs_additional()
		{
			jobportal_get_template('jobs/single/additional.php');
		}

		/**
		 * single jobs apply
		 */
		function single_jobs_apply($job_id)
		{
			jobportal_get_template('jobs/single/apply.php', array(
				'job_id' => $job_id,
			));
		}

		/**
		 * related jobs
		 */
		function single_jobs_related($job_id)
		{
			jobportal_get_template('jobs/single/related.php', array(
				'job_id' => $job_id,
			));
		}

		//Sidebar
		/**
		 * single jobs apply
		 */
		function single_jobs_sidebar_apply()
		{
			jobportal_get_template('jobs/single/sidebar/apply.php');
		}

		function single_jobs_sidebar_insights()
		{
			jobportal_get_template('jobs/single/sidebar/insights.php');
		}

		function single_jobs_sidebar_company()
		{
			jobportal_get_template('jobs/single/sidebar/company.php');
		}

		//Company

		/**
		 * sidebar company
		 */
		function sidebar_company()
		{
			jobportal_get_template('global/sidebar-company.php');
		}

		/**
		 * single company thumbnail
		 */
		function single_company_thumbnail()
		{
			jobportal_get_template('company/single/thumbnail.php');
		}

		/**
		 * single company head
		 */
		function single_company_head()
		{
			jobportal_get_template('company/single/head.php');
		}

		/**
		 * single company overview
		 */
		function single_company_overview()
		{
			jobportal_get_template('company/single/overview.php');
		}

		/**
		 * single company gallery
		 */
		function single_company_gallery()
		{
			jobportal_get_template('company/single/gallery.php');
		}

		/**
		 * single company video
		 */
		function single_company_video()
		{
			jobportal_get_template('company/single/video.php');
		}

		/**
		 * single company additional
		 */
		function single_company_additional()
		{
			jobportal_get_template('company/single/additional.php');
		}

		/**
		 * single company related
		 */
		function single_company_related()
		{
			jobportal_get_template('company/single/related.php');
		}

		/**
		 * single related review
		 */
		function single_company_review()
		{
			jobportal_get_template('company/single/review.php');
		}

		//Company Sidebar
		/**
		 * single sidebar company info
		 */
		function single_company_sidebar_info()
		{
			jobportal_get_template('company/single/sidebar/info.php');
		}

		/**
		 * single sidebar company location
		 */
		function single_company_sidebar_location()
		{
			jobportal_get_template('company/single/sidebar/location.php');
		}

		/**
		 * single candidate thumbnail
		 */
		function single_candidate_thumbnail()
		{
			jobportal_get_template('candidate/single/thumbnail.php');
		}

		/**
		 *  Single candidate head
		 */
		function single_candidate_head()
		{
			jobportal_get_template('candidate/single/head.php');
		}

		/**
		 *  Single candidate about me
		 */
		function single_candidate_about_me()
		{
			jobportal_get_template('candidate/single/about-me.php');
		}

		/**
		 *  Single candidate photos
		 */
		function single_candidate_photos()
		{
			jobportal_get_template('candidate/single/photos.php');
		}

		/**
		 * Single Candidate video
		 */
		function single_candidate_video()
		{
			jobportal_get_template('candidate/single/video.php');
		}

		/**
		 *  Single candidate skills
		 */
		function single_candidate_skills()
		{
			jobportal_get_template('candidate/single/skills.php');
		}

		/**
		 *  Single candidate experience
		 */
		function single_candidate_experience()
		{
			jobportal_get_template('candidate/single/experience.php');
		}

		/**
		 *  Single candidate education
		 */
		function single_candidate_education()
		{
			jobportal_get_template('candidate/single/education.php');
		}

		/**
		 *  Single candidate projects
		 */
		function single_candidate_projects()
		{
			jobportal_get_template('candidate/single/projects.php');
		}

		/**
		 *  Single candidate awards
		 */
		function single_candidate_awards()
		{
			jobportal_get_template('candidate/single/awards.php');
		}

		function single_candidate_additional()
		{
			jobportal_get_template('candidate/single/additional.php');
		}

		// Candidate Sidebar
		/**
		 *  Single sidebar candidate info
		 */
		function single_candidate_sidebar_info()
		{
			jobportal_get_template('candidate/single/sidebar/info.php');
		}

		/**
		 *  Single sidebar candidate location
		 */
		function single_candidate_sidebar_location()
		{
			jobportal_get_template('candidate/single/sidebar/location.php');
		}

		/**
		 * sidebar Candidate
		 */
		function sidebar_candidate()
		{
			jobportal_get_template('global/sidebar-candidate.php');
		}

		/**
		 *  Single candidate cover image
		 */
		function single_candidate_cover_hero()
		{
			jobportal_get_template('candidate/single/cover.php');
		}

		/**
		 *  Single candidate review
		 */
		function single_candidate_service()
		{
			jobportal_get_template('candidate/single/service.php');
		}

		/**
		 *  Single candidate review
		 */
		function single_candidate_review()
		{
			jobportal_get_template('candidate/single/review.php');
		}


		//Service

		/**
		 * sidebar service
		 */
		function sidebar_service()
		{
			jobportal_get_template('global/sidebar-service.php');
		}

		/**
		 * single service gallery
		 */
		function single_service_head()
		{
			jobportal_get_template('service/single/head.php');
		}

		/**
		 * single service gallery
		 */
		function single_service_gallery()
		{
			jobportal_get_template('service/single/gallery.php');
		}

		/**
		 * single service descriptions
		 */
		function single_service_descriptions()
		{
			jobportal_get_template('service/single/descriptions.php');
		}

		/**
		 * single service skills
		 */
		function single_service_skills()
		{
			jobportal_get_template('service/single/skills.php');
		}

		/**
		 * single service location
		 */
		function single_service_location()
		{
			jobportal_get_template('service/single/location.php');
		}

		/**
		 * single service video
		 */
		function single_service_video()
		{
			jobportal_get_template('service/single/video.php');
		}

		/**
		 * single service faq
		 */
		function single_service_faq()
		{
			jobportal_get_template('service/single/faq.php');
		}

		/**
		 * single related review
		 */
		function single_service_review()
		{
			jobportal_get_template('service/single/review.php');
		}

		/**
		 * single service related
		 */
		function single_service_related()
		{
			jobportal_get_template('service/single/related.php');
		}

		//Service Sidebar

		/**
		 * single sidebar service location
		 */
		function single_service_sidebar_package()
		{
			jobportal_get_template('service/single/sidebar/package.php');
		}

		/**
		 * single sidebar service info
		 */
		function single_service_sidebar_info()
		{
			jobportal_get_template('service/single/sidebar/info.php');
		}
