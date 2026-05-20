<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

/**
 *  Class JobPortal_Admin_Candidate
 */
class JobPortal_Admin_Candidate
{
	/**
	 *  Register custom columns
	 *
	 *  @param  $columns
	 *  @return  array
	 *
	 */
	public function register_custom_column_titles($columns)
	{
		unset($columns['tags']);

		$columns['thumb']  = esc_html__('Avatar', 'jobportal-framework');
		$columns['title']  = esc_html__('Full Name', 'jobportal-framework');
		$columns['position'] = esc_html__('Position', 'jobportal-framework');
		$columns['location'] = esc_html__('Location', 'jobportal-framework');
		$columns['contact'] = esc_html__('Contact', 'jobportal-framework');
		$columns['cate']   = esc_html__('Categories', 'jobportal-framework');
		$columns['skills'] = esc_html__('Skills', 'jobportal-framework');
		$columns['author'] = esc_html__('Author', 'jobportal-framework');
		$columns['status'] = esc_html__('Status', 'jobportal-framework');

		$custom_order = ['cb', 'thumb', 'title', 'position', 'location', 'contact', 'cate', 'skills', 'author', 'status'];
		$new_columns  = [];

		foreach ($custom_order as $colname) {
			if (isset($columns[$colname])) {
				$new_columns[$colname] = $columns[$colname];
			}
		}

		foreach ($columns as $key => $value) {
			if (!isset($new_columns[$key])) {
				$new_columns[$key] = $value;
			}
		}

		return $new_columns;
	}

	/**
	 *  Display custom column for candidates
	 *
	 *  @param  $column
	 *
	 */
	public function display_custom_column($column)
	{
		global $post;
		$candidate_id = $post->ID;

		switch ($column) {
			case 'thumb':
				// Use post object directly instead of get_post_field to avoid extra query
				$author_id = $post->post_author;
				$candidate_avatar = get_the_author_meta('author_avatar_image_url', $author_id);

				// Cache placeholder URL to avoid repeated string concatenation
				static $placeholder_url = null;
				if (null === $placeholder_url) {
					$placeholder_url = JOBPORTAL_PLUGIN_URL . 'assets/images/default-user-image.png';
				}

				// Common style for both avatar and placeholder (circular)
				$avatar_style = 'width: 50px; height: 50px; object-fit: cover; border-radius: 50%; display: block;';

				if (!empty($candidate_avatar)) {
					echo '<img src="' . esc_url($candidate_avatar) . '" alt="' . esc_attr__('Candidate Avatar', 'jobportal-framework') . '" style="' . esc_attr($avatar_style) . '" />';
				} else {
					echo '<img src="' . esc_url($placeholder_url) . '" alt="' . esc_attr__('No Avatar', 'jobportal-framework') . '" style="' . esc_attr($avatar_style) . ' opacity: 0.7;" />';
				}
				break;

			case 'title':
				// Display full name using helper function
				if (function_exists('jobportal_get_candidate_display_name')) {
					$full_name = jobportal_get_candidate_display_name($candidate_id);
					echo '<strong><a href="' . esc_url(get_edit_post_link($candidate_id)) . '">' . esc_html($full_name) . '</a></strong>';
				} else {
					// Fallback to post title
					echo '<strong><a href="' . esc_url(get_edit_post_link($candidate_id)) . '">' . esc_html(get_the_title($candidate_id)) . '</a></strong>';
				}
				break;

			case 'position':
				$current_position = get_post_meta($candidate_id, JOBPORTAL_METABOX_PREFIX . 'candidate_current_position', true);
				if (!empty($current_position)) {
					echo '<span title="' . esc_attr($current_position) . '">' . esc_html($current_position) . '</span>';
				} else {
					echo '<span style="color: #999;">&ndash;</span>';
				}
				break;

			case 'location':
				$candidate_locations = get_the_terms($candidate_id, 'candidate_locations');
				if (!empty($candidate_locations) && !is_wp_error($candidate_locations)) {
					$location_names = array();
					foreach ($candidate_locations as $location) {
						$location_names[] = $location->name;
					}
					echo '<span title="' . esc_attr(implode(', ', $location_names)) . '">' . esc_html(implode(', ', array_slice($location_names, 0, 2))) . '</span>';
					if (count($location_names) > 2) {
						echo ' <span style="color: #999;">+' . (count($location_names) - 2) . '</span>';
					}
				} else {
					echo '<span style="color: #999;">&ndash;</span>';
				}
				break;

			case 'contact':
				// Batch load meta to reduce queries
				$meta_values = get_post_meta($candidate_id);
				$candidate_email = isset($meta_values[JOBPORTAL_METABOX_PREFIX . 'candidate_email']) ? $meta_values[JOBPORTAL_METABOX_PREFIX . 'candidate_email'][0] : '';
				$candidate_phone = isset($meta_values[JOBPORTAL_METABOX_PREFIX . 'candidate_phone']) ? $meta_values[JOBPORTAL_METABOX_PREFIX . 'candidate_phone'][0] : '';

				echo '<div style="display: flex; flex-direction: column; gap: 2px; font-size: 12px;">';
				if (!empty($candidate_email)) {
					echo '<a href="mailto:' . esc_attr($candidate_email) . '" title="' . esc_attr($candidate_email) . '">';
					echo '<span class="dashicons dashicons-email" style="font-size: 14px; vertical-align: middle; margin-right: 4px;"></span>';
					echo esc_html($candidate_email);
					echo '</a>';
				}
				if (!empty($candidate_phone)) {
					echo '<a href="tel:' . esc_attr($candidate_phone) . '" title="' . esc_attr($candidate_phone) . '">';
					echo '<span class="dashicons dashicons-phone" style="font-size: 14px; vertical-align: middle; margin-right: 4px;"></span>';
					echo esc_html($candidate_phone);
					echo '</a>';
				}
				if (empty($candidate_email) && empty($candidate_phone)) {
					echo '<span style="color: #999;">&ndash;</span>';
				}
				echo '</div>';
				break;

			case 'cate':
				echo jobportal_admin_taxonomy_terms($candidate_id, 'candidate_categories', 'candidate');
				break;

			case 'skills':
				echo jobportal_admin_taxonomy_terms($candidate_id, 'candidate_skills', 'candidate');
				break;

			case 'author':
				echo '<a href="' . esc_url(add_query_arg('author', $post->post_author)) . '">' . get_the_author() . '</a>';
				break;

			case 'status':
				$approval_status = get_post_meta($candidate_id, JOBPORTAL_METABOX_PREFIX . 'candidate_approval_status', true);
				$post_status = get_post_status($candidate_id);

				// Cache date format options to avoid repeated get_option() calls
				static $date_format = null;
				static $time_format = null;
				if (null === $date_format) {
					$date_format = get_option('date_format');
					$time_format = get_option('time_format');
				}

				$post_date = date_i18n($date_format . ' ' . $time_format, strtotime(get_the_date('Y-m-d H:i:s', $candidate_id)));

				echo '<div style="display: flex; flex-direction: column; gap: 4px;">';

				if ($approval_status === 'locked') {
					echo '<span class="jobportal-status-badge jobportal-status-locked" title="' . esc_attr__('Profile locked - User role changed to Employer', 'jobportal-framework') . '">';
					echo '<span class="dashicons dashicons-lock" style="color: #d63638;"></span> ';
					echo esc_html__('Locked', 'jobportal-framework');
					echo '</span>';
				} elseif ($post_status === 'publish') {
					echo '<span class="jobportal-status-badge jobportal-status-published">';
					echo '<span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span> ';
					echo esc_html__('Published', 'jobportal-framework');
					echo '</span>';
				} elseif ($post_status === 'pending') {
					echo '<span class="jobportal-status-badge jobportal-status-pending">';
					echo '<span class="dashicons dashicons-clock" style="color: #dba617;"></span> ';
					echo esc_html__('Pending', 'jobportal-framework');
					echo '</span>';
				} else {
					echo '<span class="jobportal-status-badge jobportal-status-draft">';
					echo '<span class="dashicons dashicons-edit" style="color: #646970;"></span> ';
					echo esc_html__('Draft', 'jobportal-framework');
					echo '</span>';
				}

				echo '<small style="color: #646970; font-size: 11px;">' . esc_html($post_date) . '</small>';
				echo '</div>';
				break;
		}
	}

	/**
	 *  Sortable columns
	 *
	 *  @param  $columns
	 *  @return mixed
	 *
	 */
	public function sortable_columns($columns)
	{
		$columns['title'] = 'title';
		$columns['position'] = 'position';
		$columns['location'] = 'location';
		$columns['contact'] = 'contact';
		$columns['cate'] = 'cate';
		$columns['skills'] = 'skills';
		$columns['author'] = 'author';
		$columns['status'] = 'status';
		$columns['post_date'] = 'post_date';
		return $columns;
	}

	/**
	 * Handle column sorting
	 *
	 * @param array $vars Query vars
	 * @return array Modified query vars
	 */
	public function column_orderby($vars)
	{
		if (!is_admin()) {
			return $vars;
		}

		global $pagenow;
		if ('edit.php' !== $pagenow || !isset($_GET['post_type']) || 'candidate' !== $_GET['post_type']) {
			return $vars;
		}

		if (!isset($vars['orderby'])) {
			return $vars;
		}

		switch ($vars['orderby']) {
			case 'position':
				$vars = array_merge($vars, array(
					'meta_key' => JOBPORTAL_METABOX_PREFIX . 'candidate_current_position',
					'orderby' => 'meta_value',
				));
				break;

			case 'location':
				// Taxonomy sorting - handled via posts_clauses filter
				// Set a placeholder orderby to trigger posts_clauses
				$vars['orderby'] = 'name';
				break;

			case 'contact':
				// Sort by email (primary contact info)
				$vars = array_merge($vars, array(
					'meta_key' => JOBPORTAL_METABOX_PREFIX . 'candidate_email',
					'orderby' => 'meta_value',
				));
				break;

			case 'status':
				$vars = array_merge($vars, array(
					'meta_key' => JOBPORTAL_METABOX_PREFIX . 'candidate_approval_status',
					'orderby' => 'meta_value',
				));
				break;

			case 'cate':
			case 'skills':
				// Taxonomy sorting - handled via posts_clauses filter
				$vars['orderby'] = 'name';
				break;

			case 'title':
				// Sort by post title (which may be full name)
				$vars['orderby'] = 'title';
				break;

			case 'author':
				$vars['orderby'] = 'author';
				break;
		}

		return $vars;
	}

	/**
	 * Handle taxonomy and complex sorting via posts_clauses
	 *
	 * @param array $clauses Query clauses
	 * @return array Modified clauses
	 */
	public function posts_clauses($clauses)
	{
		global $wpdb, $pagenow;

		if (!is_admin() || 'edit.php' !== $pagenow) {
			return $clauses;
		}

		if (!isset($_GET['post_type']) || 'candidate' !== $_GET['post_type']) {
			return $clauses;
		}

		$orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : '';
		$order = isset($_GET['order']) && strtoupper($_GET['order']) === 'ASC' ? 'ASC' : 'DESC';

		// Handle location sorting (taxonomy)
		if ('location' === $orderby) {
			$taxonomy = 'candidate_locations';
			$clauses['join'] .= " LEFT JOIN (
				SELECT tr.object_id, t.name
				FROM {$wpdb->term_relationships} tr
				INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
				WHERE tt.taxonomy = '{$taxonomy}'
				GROUP BY tr.object_id
				ORDER BY t.name ASC
			) as location_terms ON {$wpdb->posts}.ID = location_terms.object_id";

			$clauses['orderby'] = "location_terms.name {$order}, {$wpdb->posts}.post_title ASC";
		}
		// Handle category sorting (taxonomy)
		elseif ('cate' === $orderby) {
			$taxonomy = 'candidate_categories';
			$clauses['join'] .= " LEFT JOIN (
				SELECT tr.object_id, t.name
				FROM {$wpdb->term_relationships} tr
				INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
				WHERE tt.taxonomy = '{$taxonomy}'
				GROUP BY tr.object_id
				ORDER BY t.name ASC
			) as category_terms ON {$wpdb->posts}.ID = category_terms.object_id";

			$clauses['orderby'] = "category_terms.name {$order}, {$wpdb->posts}.post_title ASC";
		}
		// Handle skills sorting (taxonomy)
		elseif ('skills' === $orderby) {
			$taxonomy = 'candidate_skills';
			$clauses['join'] .= " LEFT JOIN (
				SELECT tr.object_id, t.name
				FROM {$wpdb->term_relationships} tr
				INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
				WHERE tt.taxonomy = '{$taxonomy}'
				GROUP BY tr.object_id
				ORDER BY t.name ASC
			) as skills_terms ON {$wpdb->posts}.ID = skills_terms.object_id";

			$clauses['orderby'] = "skills_terms.name {$order}, {$wpdb->posts}.post_title ASC";
		}

		return $clauses;
	}

	/**
	 *  Modify Candidate Slug
	 *
	 *  @param  $existing_slug
	 *  @return $string
	 *
	 */
	public function modify_candidate_slug($existing_slug)
	{
		$candidate_url_slug = jobportal_get_option('candidate_url_slug');
		$enable_slug_categories = jobportal_get_option('enable_slug_categories');
		if ($candidate_url_slug) {
			if ($enable_slug_categories == 1) {
				return $candidate_url_slug . '/%candidate_categories%';
			} else {
				return $candidate_url_slug;
			}
		}

		return $existing_slug;
	}

	public function modify_candidate_has_archive($existing_slug)
	{
		$candidate_url_slug = jobportal_get_option('candidate_url_slug');
		if ($candidate_url_slug) {
			return $candidate_url_slug;
		}
		return $existing_slug;
	}

	/**
	 * Modify candidate categories slug
	 * @param $existing_slug
	 * @return string
	 */
	public function modify_candidate_categories_url_slug($existing_slug)
	{
		$candidate_categories_url_slug = jobportal_get_option('candidate_categories_url_slug');
		if ($candidate_categories_url_slug) {
			return $candidate_categories_url_slug;
		}
		return $existing_slug;
	}

	/**
	 * Modify candidate ages slug
	 * @param $existing_slug
	 * @return string
	 */
	public function modify_candidate_ages_url_slug($existing_slug)
	{
		$candidate_ages_url_slug = jobportal_get_option('candidate_ages_url_slug');
		if ($candidate_ages_url_slug) {
			return $candidate_ages_url_slug;
		}
		return $existing_slug;
	}

	/**
	 * Modify candidate languages slug
	 * @param $existing_slug
	 * @return string
	 */
	public function modify_candidate_languages_url_slug($existing_slug)
	{
		$candidate_languages_url_slug = jobportal_get_option('candidate_languages_url_slug');
		if ($candidate_languages_url_slug) {
			return $candidate_languages_url_slug;
		}
		return $existing_slug;
	}

	/**
	 * Modify candidate qualification slug
	 * @param $existing_slug
	 * @return string
	 */
	public function modify_candidate_qualification_url_slug($existing_slug)
	{
		$candidate_qualification_url_slug = jobportal_get_option('candidate_qualification_url_slug');
		if ($candidate_qualification_url_slug) {
			return $candidate_qualification_url_slug;
		}
		return $existing_slug;
	}

	/**
	 * Modify candidate salary types slug
	 * @param $existing_slug
	 * @return string
	 */
	public function modify_candidate_salary_types_url_slug($existing_slug)
	{
		$candidate_salary_types_url_slug = jobportal_get_option('candidate_salary_types_url_slug');
		if ($candidate_salary_types_url_slug) {
			return $candidate_salary_types_url_slug;
		}
		return $existing_slug;
	}

	/**
	 * Modify candidate yoe slug
	 * @param $existing_slug
	 * @return string
	 */
	public function modify_candidate_yoe_url_slug($existing_slug)
	{
		$candidate_yoe_url_slug = jobportal_get_option('candidate_yoe_url_slug');
		if ($candidate_yoe_url_slug) {
			return $candidate_yoe_url_slug;
		}
		return $existing_slug;
	}

	/**
	 * Modify candidate education levels slug
	 * @param $existing_slug
	 * @return string
	 */
	public function modify_candidate_education_levels_url_slug($existing_slug)
	{
		$candidate_education_levels_url_slug = jobportal_get_option('candidate_education_levels_url_slug');
		if ($candidate_education_levels_url_slug) {
			return $candidate_education_levels_url_slug;
		}
		return $existing_slug;
	}

	/**
	 * Modify candidate skills slug
	 * @param $existing_slug
	 * @return string
	 */
	public function modify_candidate_skills_url_slug($existing_slug)
	{
		$candidate_skills_url_slug = jobportal_get_option('candidate_skills_url_slug');
		if ($candidate_skills_url_slug) {
			return $candidate_skills_url_slug;
		}
		return $existing_slug;
	}

	/**
	 * Modify candidate gender slug
	 * @param $existing_slug
	 * @return string
	 */
	public function modify_candidate_gender_url_slug($existing_slug)
	{
		$candidate_gender_url_slug = jobportal_get_option('candidate_gender_url_slug');
		if ($candidate_gender_url_slug) {
			return $candidate_gender_url_slug;
		}
		return $existing_slug;
	}

	/**
	 * Modify candidate locations slug
	 * @param $existing_slug
	 * @return string
	 */
	public function modify_candidate_locations_url_slug($existing_slug)
	{
		$candidate_locations_url_slug = jobportal_get_option('candidate_locations_url_slug');
		if ($candidate_locations_url_slug) {
			return $candidate_locations_url_slug;
		}
		return $existing_slug;
	}

	/**
	 * Approve candidate
	 */
	public function approve_candidate()
	{
		if (!empty($_GET['approve_candidate']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'approve_candidate') && current_user_can('publish_post', $_GET['approve_candidate'])) {
			$post_id = absint(jobportal_clean(wp_unslash($_GET['approve_candidate'])));
			$listing_data = array(
				'ID' => $post_id,
				'post_status' => 'publish'
			);
			wp_update_post($listing_data);

			// Send email notification to candidate
			$author_id = get_post_field('post_author', $post_id);
			$user = get_user_by('id', $author_id);
			if ($user) {
				$user_email = $user->user_email;
				$user_roles = (array) $user->roles;
				$is_employer = in_array('jobportal_user_employer', $user_roles, true);
				$company_post_id = get_user_meta($author_id, 'jobportal-company_id', true);

				$args = array(
					'user_login' => $user->user_login,
					'candidate_title' => get_the_title($post_id),
					'candidate_url' => get_permalink($post_id)
				);
				jobportal_send_email($user_email, 'mail_approved_candidate', $args);
				update_user_meta($author_id, JOBPORTAL_METABOX_PREFIX . 'user_status', 'approve');

				// Check user role before sending approved user status email
				if ($is_employer) {
					$args_company = array(
						'user_login' => $user->user_login,
						'company_title' => !empty($company_post_id) ? get_the_title($company_post_id) : '',
						'company_url' => !empty($company_post_id) ? get_permalink($company_post_id) : ''
					);
					jobportal_send_email($user_email, 'mail_approved_user_status_company', $args_company);
				} else {
					jobportal_send_email($user_email, 'mail_approved_user_status', $args);
				}
			}

			update_post_meta($post_id, JOBPORTAL_METABOX_PREFIX . 'candidate_approval_status', 'approved');

			wp_redirect(remove_query_arg('approve_candidate', add_query_arg('approve_candidate', $post_id, admin_url('edit.php?post_type=candidate'))));
			exit;
		}
	}

	public function ajax_approve_candidate()
	{
		check_ajax_referer('jobportal_approve_candidate');
		$post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
		if (!$post_id || !current_user_can('publish_post', $post_id)) {
			wp_send_json_error(array('message' => esc_html__('Permission denied', 'jobportal-framework')));
		}
		wp_update_post(array('ID' => $post_id, 'post_status' => 'publish'));
		$author_id = get_post_field('post_author', $post_id);
		$user = get_user_by('id', $author_id);
		if ($user) {
			$user_email = $user->user_email;
			$user_roles = (array) $user->roles;
			$is_employer = in_array('jobportal_user_employer', $user_roles, true);
			$company_post_id = get_user_meta($author_id, 'jobportal-company_id', true);

			$args = array(
				'user_login' => $user->user_login,
				'candidate_title' => get_the_title($post_id),
				'candidate_url' => get_permalink($post_id)
			);
			jobportal_send_email($user_email, 'mail_approved_candidate', $args);
			update_user_meta($author_id, JOBPORTAL_METABOX_PREFIX . 'user_status', 'approve');

			// Check user role before sending approved user status email
			if ($is_employer) {
				$args_company = array(
					'user_login' => $user->user_login,
					'company_title' => !empty($company_post_id) ? get_the_title($company_post_id) : '',
					'company_url' => !empty($company_post_id) ? get_permalink($company_post_id) : ''
				);
				jobportal_send_email($user_email, 'mail_approved_user_status_company', $args_company);
			} else {
				jobportal_send_email($user_email, 'mail_approved_user_status', $args);
			}
		}
		update_post_meta($post_id, JOBPORTAL_METABOX_PREFIX . 'candidate_approval_status', 'approved');
		wp_send_json_success(array('message' => esc_html__('Approved', 'jobportal-framework')));
	}

	/**
	 * @param $actions
	 * @param $post
	 * @return mixed
	 */
	public function modify_list_row_actions($actions, $post)
	{
		// Check for your post type.
		if ($post->post_type == 'candidate') {
			if (in_array($post->post_status, array('pending'))) {
				$nonce = wp_create_nonce('jobportal_approve_candidate');
				$actions['candidate-approve'] = '<a href="#" class="jobportal-approve-candidate" data-post-id="' . esc_attr($post->ID) . '" data-nonce="' . esc_attr($nonce) . '">' . esc_html__('Approve', 'jobportal-framework') . '</a>';
			}
		}
		return $actions;
	}

	/**
	 * filter_restrict_manage_company
	 */
	public function filter_restrict_manage_candidate()
	{
		global $typenow;
		$post_type = 'candidate';
		if ($typenow == $post_type) {
			$taxonomy_arr  = array('candidate_categories', 'candidate_skills');
			foreach ($taxonomy_arr as $taxonomy) {
				$selected      = isset($_GET[$taxonomy]) ? jobportal_clean(wp_unslash($_GET[$taxonomy])) : '';
				$info_taxonomy = get_taxonomy($taxonomy);
				wp_dropdown_categories(array(
					'show_option_all' => __("All {$info_taxonomy->label}"),
					'taxonomy'        => $taxonomy,
					'name'            => $taxonomy,
					'orderby'         => 'name',
					'selected'        => $selected,
					'hide_empty'      => false,
				));
			}

			// Add Export CSV button
			$export_url = wp_nonce_url(
				add_query_arg(array(
					'action' => 'export_candidates_csv',
					'post_type' => 'candidate',
				), admin_url('admin-post.php')),
				'export_candidates_csv'
			);
			?>
			<a href="<?php echo esc_url($export_url); ?>" id="jobportal-export-csv-btn" class="button" style="margin-left: 10px;">
				<span class="dashicons dashicons-download" style="vertical-align: middle; margin-top: 3px;"></span>
				<?php esc_html_e('Export CSV', 'jobportal-framework'); ?>
			</a>
			<?php
		}
	}

	public function enqueue_candidate_admin_scripts($hook)
	{
		if ($hook !== 'edit.php' || !isset($_GET['post_type']) || $_GET['post_type'] !== 'candidate') {
			return;
		}

		// Enqueue approve candidate script
		wp_enqueue_script('jobportal-approve-candidate', JOBPORTAL_PLUGIN_URL . 'assets/js/admin/approve-candidate.js', array('jquery'), JOBPORTAL_THEME_VERSION, true);
		wp_localize_script('jobportal-approve-candidate', 'jobportalApproveCandidate', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'i18n' => array(
				'approved' => __('Approved', 'jobportal-framework'),
				'processing' => __('Processing...', 'jobportal-framework'),
			)
		));
	}

	/**
	 *  Show Candidate
	 *
	 */
	public function show_candidates()
	{
		if (!empty($_GET['show_listing']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'show_listing') && current_user_can('publish_post', $_GET['show_listing'])) {
			$post_id = absint(jobportal_clean(wp_unslash($_GET['show_listing'])));
			$listing_data   = array(
				'ID'            => $post_id,
				'post_status'   => 'publish'
			);

			wp_update_post($listing_data);
			wp_redirect(remove_query_arg('show_listing', add_query_arg('show_listing', $post_id, admin_url('edit.php?post_type=candidate'))));
			exit;
		}
	}

	/**
	 * h_filter
	 * @param $query
	 */
	public function candidate_filter($query)
	{
		global $pagenow;
		$post_type = 'candidate';
		$q_vars    = &$query->query_vars;
		if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type) {
			$taxonomy_arr  = array('candidate_categories', 'candidate_skills');
			foreach ($taxonomy_arr as $taxonomy) {
				if (isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0) {
					$term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
					if ($term && !is_wp_error($term)) {
						$q_vars[$taxonomy] = $term->slug;
					}
				}
			}

			// Cache candidate users query to avoid repeated calls
			static $candidate_users = null;
			if (null === $candidate_users) {
				$candidate_users = get_users(array(
					'role' => 'jobportal_user_candidate',
					'fields' => 'ID',
					'number' => 9999, // Limit to prevent memory issues
				));
			}

			if (!empty($candidate_users)) {
				$q_vars['author__in'] = $candidate_users;
			}
		}
	}

	public function add_badge_menu()
	{
		global $menu;

		// Cache post count to avoid repeated queries
		static $candidate_count = null;
		if (null === $candidate_count) {
			$counts = wp_count_posts('candidate');
			$candidate_count = isset($counts->pending) ? $counts->pending : 0;
		}

		if ($candidate_count && is_array($menu)) {
			foreach ($menu as $key => $value) {
				if ($menu[$key][2] == 'edit.php?post_type=candidate') {
					$menu[$key][0] .= ' <span class="update-plugins">' . $candidate_count . '</span>';
					return;
				}
			}
		}
	}

	/**
	 * Export candidates to CSV
	 * Optimized for large datasets with batch processing
	 */
	public function export_candidates_csv()
	{
		// Check nonce
		if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'export_candidates_csv')) {
			wp_die(esc_html__('Security check failed', 'jobportal-framework'));
		}

		// Check user capability
		if (!current_user_can('export')) {
			wp_die(esc_html__('You do not have permission to export candidates', 'jobportal-framework'));
		}

		// Increase limits for large exports
		@ini_set('memory_limit', '512M');
		@set_time_limit(300); // 5 minutes

		// Get query args from current filter
		$args = array(
			'post_type' => 'candidate',
			'posts_per_page' => 500, // Process in batches
			'post_status' => 'any',
			'orderby' => 'date',
			'order' => 'DESC',
			'no_found_rows' => false, // We need found_posts for pagination
		);

		// Apply filters if set
		if (isset($_GET['candidate_categories']) && !empty($_GET['candidate_categories'])) {
			$args['tax_query'][] = array(
				'taxonomy' => 'candidate_categories',
				'field' => 'term_id',
				'terms' => intval($_GET['candidate_categories']),
			);
		}

		if (isset($_GET['candidate_skills']) && !empty($_GET['candidate_skills'])) {
			if (!isset($args['tax_query'])) {
				$args['tax_query'] = array('relation' => 'AND');
			}
			$args['tax_query'][] = array(
				'taxonomy' => 'candidate_skills',
				'field' => 'term_id',
				'terms' => intval($_GET['candidate_skills']),
			);
		}

		if (isset($_GET['author']) && !empty($_GET['author'])) {
			$args['author'] = intval($_GET['author']);
		}

		if (isset($_GET['post_status']) && !empty($_GET['post_status'])) {
			$args['post_status'] = sanitize_text_field($_GET['post_status']);
		}

		// Set headers for CSV download
		$filename = 'candidates-export-' . date('Y-m-d-H-i-s') . '.csv';
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=' . $filename);
		header('Pragma: no-cache');
		header('Expires: 0');

		// Open output stream
		$output = fopen('php://output', 'w');

		// Add UTF-8 BOM for Excel compatibility
		fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

		// CSV Headers
		$headers = array(
			esc_html__('ID', 'jobportal-framework'),
			esc_html__('Full Name', 'jobportal-framework'),
			esc_html__('Position', 'jobportal-framework'),
			esc_html__('Location', 'jobportal-framework'),
			esc_html__('Email', 'jobportal-framework'),
			esc_html__('Phone', 'jobportal-framework'),
			esc_html__('Categories', 'jobportal-framework'),
			esc_html__('Skills', 'jobportal-framework'),
			esc_html__('Author', 'jobportal-framework'),
			esc_html__('Status', 'jobportal-framework'),
			esc_html__('Approval Status', 'jobportal-framework'),
			esc_html__('Date Created', 'jobportal-framework'),
			esc_html__('Date Modified', 'jobportal-framework'),
			esc_html__('URL', 'jobportal-framework'),
		);
		fputcsv($output, $headers);

		// Cache date format
		$date_format = get_option('date_format') . ' ' . get_option('time_format');

		// Cache for batch loading
		$author_cache = array();
		$meta_keys = array(
			JOBPORTAL_METABOX_PREFIX . 'candidate_current_position',
			JOBPORTAL_METABOX_PREFIX . 'candidate_email',
			JOBPORTAL_METABOX_PREFIX . 'candidate_phone',
			JOBPORTAL_METABOX_PREFIX . 'candidate_approval_status',
		);

		// Process in batches
		$paged = 1;
		$max_pages = 0;

		do {
			$args['paged'] = $paged;
			$query = new WP_Query($args);

			if (!$query->have_posts()) {
				break;
			}

			// Store max pages from first query
			if ($max_pages === 0) {
				$max_pages = $query->max_num_pages;
			}

			// Batch load meta data for all posts in this batch
			$post_ids = wp_list_pluck($query->posts, 'ID');
			$batch_meta = array();

			// Load all meta in one query per meta key
			global $wpdb;
			if (!empty($post_ids)) {
				$placeholders = implode(',', array_fill(0, count($post_ids), '%d'));
				foreach ($meta_keys as $meta_key) {
					$prepared = $wpdb->prepare(
						"SELECT post_id, meta_value FROM {$wpdb->postmeta}
						WHERE post_id IN ($placeholders)
						AND meta_key = %s",
						array_merge($post_ids, array($meta_key))
					);
					$meta_results = $wpdb->get_results($prepared);

					foreach ($meta_results as $meta) {
						if (!isset($batch_meta[$meta->post_id])) {
							$batch_meta[$meta->post_id] = array();
						}
						$batch_meta[$meta->post_id][$meta_key] = $meta->meta_value;
					}
				}

				// Batch load taxonomy terms
				$taxonomies = array('candidate_locations', 'candidate_categories', 'candidate_skills');
				$batch_terms = array();
				foreach ($taxonomies as $taxonomy) {
					$terms = wp_get_object_terms($post_ids, $taxonomy, array('fields' => 'all_with_object_id'));
					if (!is_wp_error($terms) && !empty($terms)) {
						foreach ($terms as $term) {
							if (!isset($batch_terms[$term->object_id])) {
								$batch_terms[$term->object_id] = array();
							}
							if (!isset($batch_terms[$term->object_id][$taxonomy])) {
								$batch_terms[$term->object_id][$taxonomy] = array();
							}
							$batch_terms[$term->object_id][$taxonomy][] = $term->name;
						}
					}
				}
			}

			// Process each candidate
			foreach ($query->posts as $candidate) {
				$candidate_id = $candidate->ID;

				// Get full name
				$full_name = '';
				if (function_exists('jobportal_get_candidate_display_name')) {
					$full_name = jobportal_get_candidate_display_name($candidate_id);
				} else {
					$full_name = get_the_title($candidate_id);
				}

				// Get position from batch
				$position = isset($batch_meta[$candidate_id][JOBPORTAL_METABOX_PREFIX . 'candidate_current_position'])
					? $batch_meta[$candidate_id][JOBPORTAL_METABOX_PREFIX . 'candidate_current_position']
					: '';

				// Get locations from batch
				$location_names = isset($batch_terms[$candidate_id]['candidate_locations'])
					? $batch_terms[$candidate_id]['candidate_locations']
					: array();
				$location_str = implode('; ', $location_names);

				// Get contact info from batch
				$email = isset($batch_meta[$candidate_id][JOBPORTAL_METABOX_PREFIX . 'candidate_email'])
					? $batch_meta[$candidate_id][JOBPORTAL_METABOX_PREFIX . 'candidate_email']
					: '';
				$phone = isset($batch_meta[$candidate_id][JOBPORTAL_METABOX_PREFIX . 'candidate_phone'])
					? $batch_meta[$candidate_id][JOBPORTAL_METABOX_PREFIX . 'candidate_phone']
					: '';

				// Get categories from batch
				$category_names = isset($batch_terms[$candidate_id]['candidate_categories'])
					? $batch_terms[$candidate_id]['candidate_categories']
					: array();
				$category_str = implode('; ', $category_names);

				// Get skills from batch
				$skill_names = isset($batch_terms[$candidate_id]['candidate_skills'])
					? $batch_terms[$candidate_id]['candidate_skills']
					: array();
				$skill_str = implode('; ', $skill_names);

				// Get author (with cache)
				$author_id = $candidate->post_author;
				if (!isset($author_cache[$author_id])) {
					$author = get_userdata($author_id);
					$author_cache[$author_id] = $author ? $author->display_name : '';
				}
				$author_name = $author_cache[$author_id];

				// Get status
				$post_status = $candidate->post_status;
				$approval_status = isset($batch_meta[$candidate_id][JOBPORTAL_METABOX_PREFIX . 'candidate_approval_status'])
					? $batch_meta[$candidate_id][JOBPORTAL_METABOX_PREFIX . 'candidate_approval_status']
					: '';

				// Get dates
				$date_created = date_i18n($date_format, strtotime($candidate->post_date));
				$date_modified = date_i18n($date_format, strtotime($candidate->post_modified));

				// Get URL
				$candidate_url = get_permalink($candidate_id);

				// Prepare row data
				$row = array(
					$candidate_id,
					$full_name,
					$position,
					$location_str,
					$email,
					$phone,
					$category_str,
					$skill_str,
					$author_name,
					$post_status,
					$approval_status,
					$date_created,
					$date_modified,
					$candidate_url,
				);

				fputcsv($output, $row);
			}

			// Clear memory
			wp_reset_postdata();
			unset($batch_meta, $batch_terms, $query);

			$paged++;
		} while ($paged <= $max_pages);

		fclose($output);
		exit;
	}
}
