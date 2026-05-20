<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

if (!class_exists('JobPortal_Admin')) {
	/**
	 * Class JobPortal_Admin
	 */
	class JobPortal_Admin
	{
		public function __construct()
		{
			add_action('wp_ajax_jobportal_approve_candidate', array($this, 'ajax_approve_candidate_bridge'));
			add_action('admin_enqueue_scripts', array($this, 'enqueue_candidate_admin_scripts_bridge'));
			add_action('wp_ajax_jobportal_reset_email_templates', array($this, 'ajax_reset_email_templates'));
		}


		public function ajax_approve_candidate_bridge()
		{
			if (class_exists('JobPortal_Admin_Candidate')) {
				$inst = new JobPortal_Admin_Candidate();
				$inst->ajax_approve_candidate();
			}
			wp_die();
		}

		public function enqueue_candidate_admin_scripts_bridge($hook)
		{
			if (class_exists('JobPortal_Admin_Candidate')) {
				$inst = new JobPortal_Admin_Candidate();
				$inst->enqueue_candidate_admin_scripts($hook);
			}

			// Enqueue script for email template reset
			if (strpos($hook, 'theme-options') !== false || strpos($hook, 'appearance_page') !== false) {
				wp_enqueue_script('jobportal-reset-email-templates', JOBPORTAL_PLUGIN_URL . 'assets/js/admin/reset-email-templates.js', array('jquery'), JOBPORTAL_THEME_VERSION, true);
				wp_localize_script('jobportal-reset-email-templates', 'jobportalResetEmailTemplates', array(
					'ajax_url' => admin_url('admin-ajax.php'),
					'nonce' => wp_create_nonce('jobportal_reset_email_templates'),
					'confirm_message' => esc_html__('Are you sure you want to reset all email templates to default? This action cannot be undone.', 'jobportal-framework'),
					'processing' => esc_html__('Processing...', 'jobportal-framework'),
					'success_message' => esc_html__('Templates reset successfully!', 'jobportal-framework'),
					'error_message' => esc_html__('An error occurred. Please try again.', 'jobportal-framework'),
				));
			}
		}

		/**
		 * Remove admin bar
		 * @return bool
		 */
		function remove_admin_bar()
		{
			if (!current_user_can('administrator') && !is_admin()) {
				show_admin_bar(false);
			}
		}

		/**
		 * Check if it is a jobs edit page.
		 * @return bool
		 */
		public function is_jobportal_admin()
		{
			if (is_admin()) {
				global $pagenow;
				if (in_array($pagenow, array('edit.php', 'post.php', 'post-new.php', 'edit-tags.php'))) {
					global $post_type;
					if ('jobs' == $post_type) {
						return true;
					}
				}
			}
			return false;
		}

		/**
		 * Register admin_menu
		 */
		public function admin_menu()
		{
			$enable_claim_listing = jobportal_get_option('enable_claim_listing', '1');
			if ($enable_claim_listing) :
				add_menu_page(
					esc_html__('Claim Listing', 'jobportal-framework'),
					esc_html__('Claim Listing', 'jobportal-framework'),
					'manage_options',
					'claim_listing',
					array($this, 'menu_claim_listing_callback'),
					'dashicons-list-view',
					12
				);
			endif;
		}

		public function menu_claim_listing_callback()
		{
			$claim_email = $claim_name = $claim_username = $claim_status = '';

			$meta_query = array(
				'relative' => 'AND',
				array(
					'key' => 'jobportal-claim_request',
					'value' => 1,
					'compare' => '=',
				),
			);
			if (isset($_GET['claim_name']) && $_GET['claim_name'] != '') {
				$claim_name = $_GET['claim_name'];
				$meta_query[] = array(
					'key' => 'jobportal-cd_your_name',
					'value' => $_GET['claim_name'],
					'compare' => 'LIKE',
				);
			}
			if (isset($_GET['claim_email']) && $_GET['claim_email'] != '') {
				$claim_email = $_GET['claim_email'];
				$meta_query[] = array(
					'key' => 'jobportal-cd_your_email',
					'value' => $_GET['claim_email'],
					'compare' => 'LIKE',
				);
			}
			if (isset($_GET['claim_username']) && $_GET['claim_username'] != '') {
				$claim_username = $_GET['claim_username'];
				$meta_query[] = array(
					'key' => 'jobportal-cd_your_username',
					'value' => $_GET['claim_username'],
					'compare' => 'LIKE',
				);
			}
			if (isset($_GET['claim_status']) && $_GET['claim_status'] != '') {
				$claim_status = $_GET['claim_status'];
				$meta_query[] = array(
					'key' => 'jobportal-cd_status',
					'value' => $_GET['claim_status'],
					'compare' => '=',
				);
			}
			$paged = isset($_REQUEST['paged']) ? max(1, (int)$_REQUEST['paged']) : 1;
			$args = array(
				'post_type' => 'jobs',
				'posts_per_page' => 20,
				'paged' => $paged,
				'post_status' => 'publish',
				'meta_query' => $meta_query,
			);
			// The Query
			$the_query = new WP_Query($args);
			$count = $the_query->found_posts;
?>
			<div class="jobportal-wrap wrap about-wrap claim-wrap">
				<div class="entry-search">
					<div class="claim-action">
						<a href="#" class="button button-delete"><?php esc_html_e('Delete', 'jobportal-framework'); ?></a>
					</div>
					<form action="" method="GET" class="claimFilter">
						<div class="field-group">
							<input type="text" name="claim_name" value="<?php echo $claim_name; ?>" placeholder="<?php esc_html_e('Name', 'jobportal-framework'); ?>">
							<input type="email" name="claim_email" value="<?php echo $claim_email; ?>" placeholder="<?php esc_html_e('Email', 'jobportal-framework'); ?>">
							<input type="text" name="claim_username" value="<?php echo $claim_username; ?>" placeholder="<?php esc_html_e('Username', 'jobportal-framework'); ?>">
							<select name="claim_status" id="claim_status">
								<option value=""><?php esc_html_e('All Status', 'jobportal-framework'); ?></option>
								<option value="pending" <?php if ($claim_status == 'pending') {
																					echo 'selected';
																				} ?>><?php esc_html_e('Pending', 'jobportal-framework'); ?></option>
								<option value="accept" <?php if ($claim_status == 'accept') {
																					echo 'selected';
																				} ?>><?php esc_html_e('Accept', 'jobportal-framework'); ?></option>
								<option value="refuse" <?php if ($claim_status == 'refuse') {
																					echo 'selected';
																				} ?>><?php esc_html_e('Refuse', 'jobportal-framework'); ?></option>
							</select>
							<input type="hidden" name="page" value="claim_listing">
							<input type="submit" name="submit" value="<?php esc_html_e('Filter', 'jobportal-framework'); ?>">
						</div>
					</form>
					<div class="total"><?php printf(_n('%s item', '%s items', $count, 'jobportal-framework'), '<span class="count">' . esc_html($count) . '</span>'); ?></div>
				</div>

				<div class="wrap-content">
					<form action="" method="POST">
						<table class="table-changelogs">
							<thead>
								<tr>
									<th><input type="checkbox" id="checkall" name="claim_item"></th>
									<th><?php esc_html_e('Name', 'jobportal-framework'); ?></th>
									<th><?php esc_html_e('Email', 'jobportal-framework'); ?></th>
									<th><?php esc_html_e('Username', 'jobportal-framework'); ?></th>
									<th><?php esc_html_e('Listing Url', 'jobportal-framework'); ?></th>
									<th><?php esc_html_e('Messager', 'jobportal-framework'); ?></th>
									<th><?php esc_html_e('Status', 'jobportal-framework'); ?></th>
									<th><?php esc_html_e('Action', 'jobportal-framework'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								// The Loop
								if ($the_query->have_posts()) {

									$i = 0;
									while ($the_query->have_posts()) {
										$the_query->the_post();
										$i++;
										$id = get_the_ID();
										$cd_your_name = get_post_meta($id, JOBPORTAL_METABOX_PREFIX . 'cd_your_name', true);
										$cd_your_email = get_post_meta($id, JOBPORTAL_METABOX_PREFIX . 'cd_your_email', true);
										$cd_your_listing = get_post_meta($id, JOBPORTAL_METABOX_PREFIX . 'cd_your_listing', true);
										$cd_your_username = get_post_meta($id, JOBPORTAL_METABOX_PREFIX . 'cd_your_username', true);
										$cd_messager = get_post_meta($id, JOBPORTAL_METABOX_PREFIX . 'cd_messager', true);
										$cd_status = get_post_meta($id, JOBPORTAL_METABOX_PREFIX . 'cd_status', true);
										$verified_listing = get_post_meta($id, JOBPORTAL_METABOX_PREFIX . 'verified_listing', true);
										echo '<tr>';
										echo '<td><input type="checkbox" name="claim_item"></td>';
										echo '<td>' . $cd_your_name . '</td>';
										echo '<td>' . $cd_your_email . '</td>';
										echo '<td>' . $cd_your_username . '</td>';
										echo '<td><a href="' . $cd_your_listing . '" target="_Blank">' . $cd_your_listing . '</a></td>';
										echo '<td>' . $cd_messager . '</td>';
										if ($cd_status == 'pending') {
											$value = 'pending';
										} else if ($verified_listing == 1) {
											$value = 'accept';
										} else {
											$value = 'refuse';
										}
										$data = 'data-status="' . $value . '"';
										echo '<td class="status"' . $data . '>' . $value . '</td>';
										echo '<td>
                                                <input type="submit" data-status="accept" data-jobs_id="' . $id . '" class="button button-primary" value="' . esc_attr__('Accept', 'jobportal-framework') . '">
                                                <input type="submit" data-status="refuse" data-jobs_id="' . $id . '" class="button button-secondary" value="' . esc_attr__('Refuse', 'jobportal-framework') . '">
                                                <input type="submit" data-status="delete" data-jobs_id="' . $id . '" class="button button-delete" value="' . esc_attr__('Delete', 'jobportal-framework') . '"></td>';
										echo '</tr>';
									}
								} else {
									echo '<tr class="align-center">';
									echo '<td colspan="7">' . esc_attr__('No result', 'jobportal-framework') . '</td>';
									echo '</tr>';
								}
								/* Restore original Post Data */
								wp_reset_postdata();
								?>
							</tbody>
						</table>
						<div class="pagination">
							<?php
							$big = 999999999; // need an unlikely integer

							echo paginate_links(array(
								'base' => admin_url('admin.php?page=claim_listing&paged=%#%'),
								'format' => '?paged=%#%',
								'current' => max(1, $paged),
								'total' => $the_query->max_num_pages,
								'prev_text' => '<i class="far fa-angle-left"></i>',
								'next_text' => '<i class="far fa-angle-right"></i>',
							));
							?>
						</div>
						<div class="jobportal-loading-effect"><span class="jobportal-dual-ring small"></span></div>
					</form>
				</div><!-- end .wrap-content -->
			</div>
<?php
		}

		/**
		 * Register post_type
		 * @param $post_types
		 * @return mixed
		 */
		public function register_post_type($post_types)
		{
			$post_types['jobs'] = array(
				'labels' => array(
					'name' => esc_html__('Jobs', 'jobportal-framework'),
					'singular_name' => esc_html__('Jobs', 'jobportal-framework'),
					'all_items' => esc_html__('Jobs', 'jobportal-framework'),
				),
				'supports' => array('title', 'editor', 'excerpt', 'author', 'thumbnail', 'revisions', 'page-attributes', 'comments', 'elementor'),
				'menu_icon' => 'dashicons-hammer',
				'can_export' => true,
				'show_in_rest' => true,
				'capability_type' => 'jobs',
				'map_meta_cap' => true,
				'rewrite' => array(
					'slug' => apply_filters('jobportal_jobs_slug', 'jobs'),
					'with_front' => false,
				),
				'show_ui'	=> true,
				'menu_position' => 3,
				'has_archive' => apply_filters('jobportal_jobs_has_archive', 'jobs'),
				'show_in_menu' => true,
			);

			$post_types['applicants'] = array(
				'labels' => array(
					'name' => esc_html__('Applicants', 'jobportal-framework'),
					'singular_name' => esc_html__('Applicants', 'jobportal-framework'),
					'all_items' => esc_html__('Applicants', 'jobportal-framework'),
				),
				'menu_icon' => 'dashicons-universal-access-alt',
				'capabilities' => $this->get_applicants_capabilities(),
				'map_meta_cap' => true,
				'supports' => array('title'),
				'rewrite' => array(
					'slug' => apply_filters('jobportal_applicants_slug', 'applicants'),
				),
				'show_in_admin_bar' => true,
				'menu_position' => 4,
			);

			if (jobportal_get_option('enable_job_alerts') === '1') {
				$post_types['job_alerts'] = array(
					'labels' => array(
						'name' => esc_html__('Job Alerts', 'jobportal-framework'),
						'singular_name' => esc_html__('Job Alerts', 'jobportal-framework'),
						'all_items' => esc_html__('Job Alerts', 'jobportal-framework'),
					),
					'menu_icon'         => 'dashicons-email-alt',
					'map_meta_cap' => true,
					'supports' => array('title'),
					'show_in_admin_bar' => true,
					'menu_position' => 5,
				);
			}

			$post_types['company'] = array(
				'labels' => array(
					'name' => esc_html__('Companies', 'jobportal-framework'),
					'singular_name' => esc_html__('Companies', 'jobportal-framework'),
					'all_items' => esc_html__('Companies', 'jobportal-framework'),
				),
				'supports' => array('title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'elementor'),
				'menu_icon' => 'dashicons-admin-multisite',
				'can_export' => true,
				'show_in_rest' => true,
				'capability_type' => 'company',
				'map_meta_cap' => true,
				'rewrite' => array(
					'slug' => apply_filters('jobportal_company_slug', 'company'),
					'with_front' => false,
				),
				'has_archive' => apply_filters('jobportal_company_has_archive', 'company'),
				'show_in_admin_bar' => true,
				'menu_position' => 6,
			);

			$post_types['package'] = array(
				'labels' => array(
					'name' => esc_html__('Package', 'jobportal-framework'),
					'singular_name' => esc_html__('Package', 'jobportal-framework'),
					'all_items' => esc_html__('Package', 'jobportal-framework'),
				),
				'supports' => array('title', 'thumbnail'),
				'menu_icon' => 'dashicons-archive',
				'can_export' => true,
				'show_in_rest' => true,
				'capability_type' => 'package',
				'map_meta_cap' => true,
				'rewrite' => array(
					'slug' => apply_filters('jobportal_package_slug', 'package'),
				),
				'show_ui'	=> true,
				'show_in_menu' => true,
				'menu_position' => 7,
			);

			$post_types['user_package'] = array(
				'labels' => array(
					'name' => esc_html__('User Packages', 'jobportal-framework'),
					'singular_name' => esc_html__('User Packages', 'jobportal-framework'),
					'all_items' => esc_html__('User Packages', 'jobportal-framework'),
				),
				'supports' => array('title', 'excerpt'),
				'menu_icon' => 'dashicons-money',
				'can_export' => true,
				'capabilities' => $this->get_user_package_capabilities(),
				'map_meta_cap' => true,
				'rewrite' => array(
					'slug' => apply_filters('jobportal_user_package_slug', 'user_package'),
				),
				'show_ui'	=> true,
				'show_in_menu' => true,
				'menu_position' => 8,
			);

			$post_types['invoice'] = array(
				'labels' => array(
					'name' => esc_html__('Invoices', 'jobportal-framework'),
					'singular_name' => esc_html__('Invoices', 'jobportal-framework'),
					'all_items' => esc_html__('Invoices', 'jobportal-framework'),
				),
				'supports' => array('title', 'excerpt'),
				'menu_icon' => 'dashicons-list-view',
				'capabilities' => $this->get_invoice_capabilities(),
				'map_meta_cap' => true,
				'rewrite' => array(
					'slug' => apply_filters('jobportal_invoice_slug', 'invoice'),
				),
				'show_ui'	=> true,
				'show_in_menu' => true,
				'menu_position' => 9,
			);

			if (jobportal_get_option('enable_post_type_service') === '1') {
				$post_types['service_order'] = array(
					'labels' => array(
						'name' => esc_html__('Service Order', 'jobportal-framework'),
						'singular_name' => esc_html__('Service Order', 'jobportal-framework'),
						'all_items' => esc_html__('Service Order', 'jobportal-framework'),
					),
					'supports' => array('title'),
					'menu_icon' => 'dashicons-printer',
					'capabilities' => $this->get_order_capabilities(),
					'map_meta_cap' => true,
					'rewrite' => array(
						'slug' => apply_filters('jobportal_service_order_slug', 'service_order'),
					),
					'show_ui'	=> true,
					'show_in_menu' => true,
					'menu_position' => 10,
				);
				$post_types['service_withdraw'] = array(
					'labels' => array(
						'name' => esc_html__('Service Withdraw', 'jobportal-framework'),
						'singular_name' => esc_html__('Service Withdraw', 'jobportal-framework'),
						'all_items' => esc_html__('Service Withdraw', 'jobportal-framework'),
					),
					'supports' => array('title'),
					'menu_icon' => 'dashicons-money-alt',
					'capabilities' => $this->get_service_withdraw_capabilities(),
					'map_meta_cap' => true,
					'rewrite' => array(
						'slug' => apply_filters('jobportal_service_withdraw_slug', 'service_withdraw'),
					),
					'show_ui'	=> true,
					'show_in_menu' => true,
					'menu_position' => 11,
				);
			}

			$post_types['candidate'] = array(
				'labels' => array(
					'name' => esc_html__('Candidates', 'jobportal-framework'),
					'singular_name' => esc_html__('Candidates', 'jobportal-framework'),
					'all_items' => esc_html__('Candidates', 'jobportal-framework'),
				),
				'supports' => array('title', 'editor', 'excerpt', 'author', 'thumbnail', 'page-attributes', 'comments', 'elementor'),
				'menu_icon' => 'dashicons-buddicons-buddypress-logo',
				'can_export' => true,
				'show_in_rest' => true,
				'capabilities' => $this->get_candidate_capabilities(),
				'map_meta_cap' => true,
				'rewrite' => array(
					'slug' => apply_filters('jobportal_candidate_slug', 'candidate'),
					'with_front' => false,
				),
				'has_archive' => apply_filters('jobportal_candidate_has_archive', 'candidate'),
				'show_ui'	=> true,
				'show_in_menu' => true,
				'menu_position' => 12,
			);

			if (jobportal_get_option('enable_post_type_service') === '1') {
				$post_types['service'] = array(
					'labels' => array(
						'name' => esc_html__('Services', 'jobportal-framework'),
						'singular_name' => esc_html__('Services', 'jobportal-framework'),
						'all_items' => esc_html__('Services', 'jobportal-framework'),
					),
					'supports' => array('title', 'editor', 'excerpt', 'author', 'thumbnail', 'revisions', 'page-attributes', 'comments', 'elementor'),
					'menu_icon' => 'dashicons-hammer',
					'can_export' => true,
					'show_in_rest' => true,
					'map_meta_cap' => true,
					'rewrite' => array(
						'slug' => apply_filters('jobportal_service_slug', 'services'),
						'with_front' => false,
					),
					'show_ui'	=> true,
					'has_archive' => apply_filters('jobportal_service_has_archive', 'services'),
					'show_in_menu' => true,
					'menu_position' => 13,
				);
			}

			$post_types['candidate_package'] = array(
				'labels' => array(
					'name' => esc_html__('Package', 'jobportal-framework'),
					'singular_name' => esc_html__('Package', 'jobportal-framework'),
					'all_items' => esc_html__('Package', 'jobportal-framework'),
				),
				'supports' => array('title', 'thumbnail'),
				'menu_icon' => 'dashicons-archive',
				'can_export' => true,
				'show_in_rest' => true,
				'map_meta_cap' => true,
				'rewrite' => array(
					'slug' => apply_filters('jobportal_candidate_package_slug', 'candidate_package'),
				),
				'show_ui'	=> true,
				'show_in_menu' => true,
				'menu_position' => 14,
			);
			$post_types['candidate_order'] = array(
				'labels' => array(
					'name' => esc_html__('Order', 'jobportal-framework'),
					'singular_name' => esc_html__('Order', 'jobportal-framework'),
					'all_items' => esc_html__('Service Order', 'jobportal-framework'),
				),
				'supports' => array('title'),
				'menu_icon' => 'dashicons-list-view',
				'capabilities' => $this->get_order_capabilities(),
				'map_meta_cap' => true,
				'rewrite' => array(
					'slug' => apply_filters('jobportal_order_slug', 'candidate_order'),
				),
				'show_ui'	=> true,
				'show_in_menu' => true,
				'menu_position' => 15,
			);

			$post_types['package_coupon'] = array(
				'labels' => array(
					'name' => esc_html__('Package Coupon', 'jobportal-framework'),
					'singular_name' => esc_html__('Package Coupon', 'jobportal-framework'),
					'all_items' => esc_html__('Package Coupon', 'jobportal-framework'),
				),
				'supports' => array('title'),
				'menu_icon'         => 'dashicons-tickets',
				'has_archive'       => false,
				'publicly_queryable' => false,
				'show_in_rest'		=> false,
				'show_ui'	=> true,
				'show_in_menu' => true,
				'menu_position' => 16,
			);

			$post_types['messages'] = array(
				'labels' => array(
					'name' => esc_html__('Messages', 'jobportal-framework'),
					'singular_name' => esc_html__('Messages', 'jobportal-framework'),
					'all_items' => esc_html__('Messages', 'jobportal-framework'),
				),
				'supports' => array('title', 'excerpt'),
				'menu_icon'         => 'dashicons-format-chat',
				'has_archive'       => false,
				'publicly_queryable' => false,
				'show_in_rest'		=> false,
				'show_ui'	=> true,
				'show_in_menu' => true,
				'menu_position' => 16,
			);

			$post_types['notification'] = array(
				'labels' => array(
					'name' => esc_html__('Notification', 'jobportal-framework'),
					'singular_name' => esc_html__('Notification', 'jobportal-framework'),
					'all_items' => esc_html__('Notification', 'jobportal-framework'),
				),
				'supports' => array('title', 'excerpt'),
				'menu_icon'         => 'dashicons-bell',
				'has_archive'       => false,
				'publicly_queryable' => false,
				'show_in_rest'		=> false,
				'show_ui'	=> true,
				'show_in_menu' => true,
				'menu_position' => 17,
			);

			$post_types['meetings'] = array(
				'labels' => array(
					'name' => esc_html__('Meetings', 'jobportal-framework'),
					'singular_name' => esc_html__('Meetings', 'jobportal-framework'),
					'all_items' => esc_html__('Meetings', 'jobportal-framework'),
				),
				'supports' => array('title'),
				'menu_icon' => 'dashicons-calendar-alt',
				'capabilities' => $this->get_meetings_capabilities(),
				'map_meta_cap' => true,
				'rewrite' => array(
					'slug' => apply_filters('jobportal_meetings_slug', 'meetings'),
				),
				'show_ui'	=> true,
				'show_in_menu' => true,
				'menu_position' => 18,
			);

			return $post_types;
		}

		/**
		 * Register post status
		 */
		public function register_post_status()
		{
			register_post_status('expired', array(
				'label' => _x('Expired', 'post status', 'jobportal-framework'),
				'public' => true,
				'exclude_from_search' => true,
				'show_in_admin_all_list' => true,
				'show_in_admin_status_list' => true,
				'label_count' => _n_noop('Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>', 'jobportal-framework'),
			));

			register_post_status('pause', array(
				'label' => _x('Pause', 'post status', 'jobportal-framework'),
				'public' => true,
				'exclude_from_search' => true,
				'show_in_admin_all_list' => true,
				'show_in_admin_status_list' => true,
				'label_count' => _n_noop('Pause <span class="count">(%s)</span>', 'Pause <span class="count">(%s)</span>', 'jobportal-framework'),
			));

			register_post_status('canceled', array(
				'label' => _x('Canceled', 'post status', 'jobportal-framework'),
				'public' => true,
				'exclude_from_search' => true,
				'show_in_admin_all_list' => true,
				'show_in_admin_status_list' => true,
				'label_count' => _n_noop('Canceled <span class="count">(%s)</span>', 'Canceled <span class="count">(%s)</span>', 'jobportal-framework'),
			));

			register_post_status('hidden', array(
				'label' => _x('Hidden', 'post status', 'jobportal-framework'),
				'public' => true,
				'exclude_from_search' => true,
				'show_in_admin_all_list' => true,
				'show_in_admin_status_list' => true,
				'label_count' => _n_noop('Hidden <span class="count">(%s)</span>', 'Hidden <span class="count">(%s)</span>', 'jobportal-framework'),
			));
		}

		/**
		 * Get invoice capabilities
		 * @return mixed
		 */
		private function get_invoice_capabilities()
		{
			$caps = array(
				'create_posts' => 'do_not_allow',
				'edit_post' => 'edit_invoices',
				'delete_posts' => 'delete_invoices'
			);
			return apply_filters('get_invoice_capabilities', $caps);
		}

		/**
		 * Get order capabilities
		 * @return mixed
		 */
		private function get_order_capabilities()
		{
			$caps = array(
				'create_posts' => 'do_not_allow',
				'edit_post' => 'edit_order',
				'delete_posts' => true,
			);
			return apply_filters('get_order_capabilities', $caps);
		}

		/**
		 * Get order capabilities
		 * @return mixed
		 */
		private function get_service_withdraw_capabilities()
		{
			$caps = array(
				'create_posts' => 'do_not_allow',
				'edit_post' => 'edit_order',
				'delete_posts' => true,
			);
			return apply_filters('get_order_capabilities', $caps);
		}

		/**
		 * Get applicants capabilities
		 * @return mixed
		 */
		private function get_applicants_capabilities()
		{
			$caps = array(
				'create_posts' => 'do_not_allow',
				'edit_post' => 'edit_applicants',
				'delete_posts' => true,
			);
			return apply_filters('get_applicants_capabilities', $caps);
		}

		/**
		 * Get meetings capabilities
		 * @return mixed
		 */
		private function get_meetings_capabilities()
		{
			$caps = array(
				'create_posts' => 'do_not_allow',
				'edit_post' => 'edit_meetings',
				'delete_posts' => true,
			);
			return apply_filters('get_meetings_capabilities', $caps);
		}

		/**
		 * Get user_package capabilities
		 * @return mixed
		 */
		private function get_user_package_capabilities()
		{
			$caps = array(
				'create_posts' => 'do_not_allow',
				'edit_post' => 'edit_user_packages',
				'delete_posts' => 'do_not_allow'
			);
			return apply_filters('get_user_package_capabilities', $caps);
		}

		/**
		 * Get candidate capabilities
		 * @return mixed
		 */
		private function get_candidate_capabilities()
		{
			$caps = array(
				'create_posts' => 'do_not_allow',
				'edit_post' => 'edit_candidate',
				'delete_post' => 'delete_candidate',
			);
			return apply_filters('get_candidate_capabilities', $caps);
		}

		/**
		 * Register taxonomy
		 * @param $taxonomies
		 * @return mixed
		 */
		public function register_taxonomy($taxonomies)
		{
			// Candidate taxonomy
			$taxonomies['candidate_categories'] = array(
				'post_type' => 'candidate',
				'hierarchical' => true,
				'show_in_rest' => true,
				'label' => esc_html__('Categories', 'jobportal-framework'),
				'singular_name' => esc_html__('Candidate Category', 'jobportal-framework'),
				'rewrite' => array(
					'slug' => apply_filters('jobportal_candidate_categories_slug', 'candidate_categories'),
				),
			);
			$taxonomies['candidate_ages'] = array(
				'post_type' => 'candidate',
				'hierarchical' => true,
				'show_in_rest' => true,
				'label' => esc_html__('Ages', 'jobportal-framework'),
				'singular_name' => esc_html__('Candidate Age', 'jobportal-framework'),
				'rewrite' => array(
					'slug' => apply_filters('jobportal_candidate_ages_slug', 'candidate_ages'),
				),
			);

			$taxonomies['candidate_languages'] = array(
				'post_type' => 'candidate',
				'hierarchical' => true,
				'show_in_rest' => true,
				'label' => esc_html__('Languages', 'jobportal-framework'),
				'singular_name' => esc_html__('Candidate Language', 'jobportal-framework'),
				'rewrite' => array(
					'slug' => apply_filters('jobportal_candidate_languages_slug', 'candidate_languages'),
				),
			);
			$taxonomies['candidate_qualification'] = array(
				'post_type' => 'candidate',
				'hierarchical' => true,
				'show_in_rest' => true,
				'label' => esc_html__('Qualification', 'jobportal-framework'),
				'singular_name' => esc_html__('Candidate Qualification', 'jobportal-framework'),
				'rewrite' => array(
					'slug' => apply_filters('jobportal_candidate_qualification_slug', 'candidate_qualification'),
				),
			);

			$taxonomies['candidate_yoe'] = array(
				'post_type' => 'candidate',
				'hierarchical' => true,
				'show_in_rest' => true,
				'label' => esc_html__('Years of Experience', 'jobportal-framework'),
				'singular_name' => esc_html__('Candidate Years of Experience', 'jobportal-framework'),
				'rewrite' => array(
					'slug' => apply_filters('jobportal_candidate_yoe_slug', 'candidate_yoe'),
				),
			);

			$taxonomies['candidate_education_levels'] = array(
				'post_type' => 'candidate',
				'hierarchical' => true,
				'show_in_rest' => true,
				'label' => esc_html__('Levels of Education', 'jobportal-framework'),
				'singular_name' => esc_html__('Candidate Level of Education', 'jobportal-framework'),
				'rewrite' => array(
					'slug' => apply_filters('jobportal_candidate_education_levels_slug', 'candidate_education_levels'),
				),
			);

			$taxonomies['candidate_skills'] = array(
				'post_type' => 'candidate',
				'hierarchical' => true,
				'show_in_rest' => true,
				'label' => esc_html__('Skills', 'jobportal-framework'),
				'singular_name' => esc_html__('Candidate Skill', 'jobportal-framework'),
				'rewrite' => array(
					'slug' => apply_filters('jobportal_candidate_skills_slug', 'candidate_skills'),
				),
			);

			$taxonomies['candidate_gender'] = array(
				'post_type' => 'candidate',
				'hierarchical' => true,
				'show_in_rest' => true,
				'label' => esc_html__('Gender', 'jobportal-framework'),
				'singular_name' => esc_html__('Candidate Gender', 'jobportal-framework'),
				'rewrite' => array(
					'slug' => apply_filters('jobportal_candidate_gender_slug', 'candidate_gender'),
				),
			);

			$taxonomies['candidate_locations'] = array(
				'post_type' => 'candidate',
				'hierarchical' => true,
				'show_in_rest' => true,
				'label' => esc_html__('City / Town', 'jobportal-framework'),
				'singular_name' => esc_html__('City', 'jobportal-framework'),
				'rewrite' => array(
					'slug' => apply_filters('jobportal_candidate_locations_slug', 'candidate_locations'),
				),
			);

			if (jobportal_get_option('enable_option_state') === '1') {
				$taxonomies['candidate_state'] = array(
					'post_type' => 'candidate',
					'hierarchical' => true,
					'show_in_rest' => true,
					'label' => esc_html__('Province / State', 'jobportal-framework'),
					'singular_name' => esc_html__('State', 'jobportal-framework'),
					'rewrite' => array(
						'slug' => apply_filters('jobportal_candidate_state_slug', 'candidate_state'),
					),
				);
			}

			//Jobs
			$taxonomies['jobs-categories'] = array(
				'post_type' => 'jobs',
				'hierarchical' => true,
				'show_in_rest' => true,
				'label' => esc_html__('Categories', 'jobportal-framework'),
				'singular_name' => esc_html__('Jobs Categories', 'jobportal-framework'),
				'rewrite' => array(
					'slug' => apply_filters('jobportal_jobs_categories_slug', 'jobs-categories'),
				),
			);
			$taxonomies['jobs-skills'] = array(
				'post_type' => 'jobs',
				'hierarchical' => true,
				'show_in_rest' => true,
				'label' => esc_html__('Skills', 'jobportal-framework'),
				'singular_name' => esc_html__('Jobs Skills', 'jobportal-framework'),
				'rewrite' => array(
					'slug' => apply_filters('jobportal_jobs_skills_slug', 'jobs-skills'),
				),
			);
			$taxonomies['jobs-type'] = array(
				'post_type' => 'jobs',
				'hierarchical' => true,
				'show_in_rest' => true,
				'label' => esc_html__('Type', 'jobportal-framework'),
				'singular_name' => esc_html__('Jobs Type', 'jobportal-framework'),
				'rewrite' => array(
					'slug' => apply_filters('jobportal_jobs_type_slug', 'jobs-type'),
				),
			);
			$taxonomies['jobs-career'] = array(
				'post_type' => 'jobs',
				'hierarchical' => true,
				'show_in_rest' => true,
				'label' => esc_html__('Career', 'jobportal-framework'),
				'singular_name' => esc_html__('Jobs Career', 'jobportal-framework'),
				'rewrite' => array(
					'slug' => apply_filters('jobportal_jobs_career_slug', 'jobs-career'),
				),
			);
			$taxonomies['jobs-experience'] = array(
				'post_type' => 'jobs',
				'hierarchical' => true,
				'show_in_rest' => true,
				'label' => esc_html__('Experience', 'jobs-framework'),
				'singular_name' => esc_html__('Jobs Experience', 'jobportal-framework'),
				'rewrite' => array(
					'slug' => apply_filters('jobportal_jobs_experience_slug', 'jobs-experience'),
				),
			);
			$taxonomies['jobs-qualification'] = array(
				'post_type' => 'jobs',
				'hierarchical' => true,
				'show_in_rest' => true,
				'label' => esc_html__('Qualification', 'jobportal-framework'),
				'singular_name' => esc_html__('Jobs Qualification', 'jobportal-framework'),
				'rewrite' => array(
					'slug' => apply_filters('jobportal_jobs_qualification_slug', 'jobs-qualification'),
				),
			);
			$taxonomies['jobs-gender'] = array(
				'post_type' => 'jobs',
				'hierarchical' => true,
				'show_in_rest' => true,
				'label' => esc_html__('Gender', 'jobportal-framework'),
				'singular_name' => esc_html__('Jobs Gender', 'jobportal-framework'),
				'rewrite' => array(
					'slug' => apply_filters('jobportal_jobs_gender_slug', 'jobs-gender'),
				),
			);
			$taxonomies['jobs-location'] = array(
				'post_type' => 'jobs',
				'hierarchical' => true,
				'show_in_rest' => true,
				'label' => esc_html__('City / Town', 'jobportal-framework'),
				'singular_name' => esc_html__('City', 'jobportal-framework'),
				'rewrite' => array(
					'slug' => apply_filters('jobportal_jobs_location_slug', 'jobs-location'),
				),
			);
			if (jobportal_get_option('enable_option_state') === '1') {
				$taxonomies['jobs-state'] = array(
					'post_type' => 'jobs',
					'hierarchical' => true,
					'show_in_rest' => true,
					'label' => esc_html__('Province / State', 'jobportal-framework'),
					'singular_name' => esc_html__('State', 'jobportal-framework'),
					'rewrite' => array(
						'slug' => apply_filters('jobportal_jobs_state_slug', 'jobs-state'),
					),
				);
			}

			//Company
			$taxonomies['company-categories'] = array(
				'post_type' => 'company',
				'hierarchical' => true,
				'show_in_rest' => true,
				'label' => esc_html__('Categories', 'jobportal-framework'),
				'singular_name' => esc_html__('Company Categories', 'jobportal-framework'),
				'rewrite' => array(
					'slug' => apply_filters('jobportal_company_categories_slug', 'company-categories'),
				),
			);
			$taxonomies['company-size'] = array(
				'post_type' => 'company',
				'hierarchical' => true,
				'show_in_rest' => true,
				'label' => esc_html__('Size', 'jobportal-framework'),
				'singular_name' => esc_html__('Company Size', 'jobportal-framework'),
				'rewrite' => array(
					'slug' => apply_filters('jobportal_company_size_slug', 'company-size'),
				),
			);
			$taxonomies['company-location'] = array(
				'post_type' => 'company',
				'hierarchical' => true,
				'show_in_rest' => true,
				'label' => esc_html__('City / Town', 'jobportal-framework'),
				'singular_name' => esc_html__('City', 'jobportal-framework'),
				'rewrite' => array(
					'slug' => apply_filters('jobportal_company_location_slug', 'company-location'),
				),
			);
			if (jobportal_get_option('enable_option_state') === '1') {
				$taxonomies['company-state'] = array(
					'post_type' => 'company',
					'hierarchical' => true,
					'show_in_rest' => true,
					'label' => esc_html__('Province / State', 'jobportal-framework'),
					'singular_name' => esc_html__('State', 'jobportal-framework'),
					'rewrite' => array(
						'slug' => apply_filters('jobportal_company_state_slug', 'company-state'),
					),
				);
			}
			//Service
			if (jobportal_get_option('enable_post_type_service') === '1') {
				$taxonomies['service-categories'] = array(
					'post_type' => 'service',
					'hierarchical' => true,
					'show_in_rest' => true,
					'label' => esc_html__('Categories', 'jobportal-framework'),
					'singular_name' => esc_html__('Service Categories', 'jobportal-framework'),
					'rewrite' => array(
						'slug' => apply_filters('jobportal_service_categories_slug', 'service-categories'),
					),
				);

				$taxonomies['service-skills'] = array(
					'post_type' => 'service',
					'hierarchical' => true,
					'show_in_rest' => true,
					'label' => esc_html__('Skills', 'jobportal-framework'),
					'singular_name' => esc_html__('Service Skills', 'jobportal-framework'),
					'rewrite' => array(
						'slug' => apply_filters('jobportal_service_skills_slug', 'service-skills'),
					),
				);
				$taxonomies['service-language'] = array(
					'post_type' => 'service',
					'hierarchical' => true,
					'show_in_rest' => true,
					'label' => esc_html__('Language', 'jobportal-framework'),
					'singular_name' => esc_html__('Service Language', 'jobportal-framework'),
					'rewrite' => array(
						'slug' => apply_filters('jobportal_service_language_slug', 'service-language'),
					),
				);
				$taxonomies['service-location'] = array(
					'post_type' => 'service',
					'hierarchical' => true,
					'show_in_rest' => true,
					'label' => esc_html__('City / Town', 'jobportal-framework'),
					'singular_name' => esc_html__('City', 'jobportal-framework'),
					'rewrite' => array(
						'slug' => apply_filters('jobportal_service_location_slug', 'service-location'),
					),
				);
				if (jobportal_get_option('enable_option_state') === '1') {
					$taxonomies['service-state'] = array(
						'post_type' => 'service',
						'hierarchical' => true,
						'show_in_rest' => true,
						'label' => esc_html__('Province / State', 'jobportal-framework'),
						'singular_name' => esc_html__('State', 'jobportal-framework'),
						'rewrite' => array(
							'slug' => apply_filters('jobportal_service_state_slug', 'service-state'),
						),
					);
				}
			}

			return $taxonomies;
		}

		/**
		 * Register meta term
		 * @param $configs
		 * @return mixed
		 */
		public function register_term_meta($configs)
		{
			$configs['jobs-experience-settings'] = apply_filters('jobportal_register_term_meta_jobs_experience', array(
				'name' => esc_html__('', 'jobportal-framework'),
				'layout' => 'horizontal',
				'taxonomy' => array('jobs-experience'),
				'fields' => array(
					array(
						'id' => 'jobs_experience_order',
						'title' => esc_html__('Number Order by', 'jobportal-framework'),
						'type' => 'text',
						'col' => '10',
						'pattern' => '[0-9]*',
						'default' => '',
						'desc' => esc_html__('Enter a numeric value to control the display order of this term. Lower numbers will be displayed first when listing terms. Leave empty to use default alphabetical order.', 'jobportal-framework'),
					),
				)
			));

			$configs['company-size-settings'] = apply_filters('jobportal_register_term_meta_company_size', array(
				'name' => esc_html__('', 'jobportal-framework'),
				'layout' => 'horizontal',
				'taxonomy' => array('company-size'),
				'fields' => array(
					array(
						'id' => 'company_size_order',
						'title' => esc_html__('Number Order by', 'jobportal-framework'),
						'type' => 'text',
						'col' => '10',
						'pattern' => '[0-9]*',
						'default' => '',
						'desc' => esc_html__('Enter a numeric value to control the display order of this term. Lower numbers will be displayed first when listing terms. Leave empty to use default alphabetical order.', 'jobportal-framework'),
					),
				)
			));

			$configs['candidate-experience-settings'] = apply_filters('jobportal_register_term_meta_candidate_experience', array(
				'name' => esc_html__('', 'jobportal-framework'),
				'layout' => 'horizontal',
				'taxonomy' => array('candidate_yoe'),
				'fields' => array(
					array(
						'id' => 'candidate_experience_order',
						'title' => esc_html__('Number Order by', 'jobportal-framework'),
						'type' => 'text',
						'col' => '10',
						'pattern' => '[0-9]*',
						'default' => '',
						'desc' => esc_html__('Enter a numeric value to control the display order of this term. Lower numbers will be displayed first when listing terms. Leave empty to use default alphabetical order.', 'jobportal-framework'),
					),
				)
			));

			$configs['jobs-career-settings'] = apply_filters('jobportal_register_term_meta_jobs_career', array(
				'name' => esc_html__('', 'jobportal-framework'),
				'layout' => 'horizontal',
				'taxonomy' => array('jobs-career'),
				'fields' => array(
					array(
						'id' => 'jobs_career_order',
						'title' => esc_html__('Number Order by', 'jobportal-framework'),
						'type' => 'text',
						'col' => '10',
						'pattern' => '[0-9]*',
						'default' => '',
						'desc' => esc_html__('Enter a numeric value to control the display order of this term. Lower numbers will be displayed first when listing terms. Leave empty to use default alphabetical order.', 'jobportal-framework'),
					),
				)
			));

			$configs['jobs-qualification-settings'] = apply_filters('jobportal_register_term_meta_jobs_qualification', array(
				'name' => esc_html__('', 'jobportal-framework'),
				'layout' => 'horizontal',
				'taxonomy' => array('jobs-qualification'),
				'fields' => array(
					array(
						'id' => 'jobs_qualification_order',
						'title' => esc_html__('Number Order by', 'jobportal-framework'),
						'type' => 'text',
						'col' => '10',
						'pattern' => '[0-9]*',
						'default' => '',
						'desc' => esc_html__('Enter a numeric value to control the display order of this term. Lower numbers will be displayed first when listing terms. Leave empty to use default alphabetical order.', 'jobportal-framework'),
					),
				)
			));

			$configs['candidate-ages-settings'] = apply_filters('jobportal_register_term_meta_candidate_ages', array(
				'name' => esc_html__('', 'jobportal-framework'),
				'layout' => 'horizontal',
				'taxonomy' => array('candidate_ages'),
				'fields' => array(
					array(
						'id' => 'candidate_ages_order',
						'title' => esc_html__('Number Order by', 'jobportal-framework'),
						'type' => 'text',
						'col' => '10',
						'pattern' => '[0-9]*',
						'default' => '',
						'desc' => esc_html__('Enter a numeric value to control the display order of this term. Lower numbers will be displayed first when listing terms. Leave empty to use default alphabetical order.', 'jobportal-framework'),
					),
				)
			));

			$configs['candidate-qualification-settings'] = apply_filters('jobportal_register_term_meta_candidate_qualification', array(
				'name' => esc_html__('', 'jobportal-framework'),
				'layout' => 'horizontal',
				'taxonomy' => array('candidate_qualification'),
				'fields' => array(
					array(
						'id' => 'candidate_qualification_order',
						'title' => esc_html__('Number Order by', 'jobportal-framework'),
						'type' => 'text',
						'col' => '10',
						'pattern' => '[0-9]*',
						'default' => '',
						'desc' => esc_html__('Enter a numeric value to control the display order of this term. Lower numbers will be displayed first when listing terms. Leave empty to use default alphabetical order.', 'jobportal-framework'),
					),
				)
			));

			$configs['service-categories-settings'] = apply_filters('jobportal_register_term_meta_service_categories', array(
				'name' => esc_html__('', 'jobportal-framework'),
				'layout' => 'horizontal',
				'taxonomy' => array('service-categories'),
				'fields' => array(
					array(
						'id' => 'service_categories_order',
						'title' => esc_html__('Number Order by', 'jobportal-framework'),
						'type' => 'text',
						'col' => '10',
						'pattern' => '[0-9]*',
						'default' => '',
						'desc' => esc_html__('Enter a numeric value to control the display order of this term. Lower numbers will be displayed first when listing terms. Leave empty to use default alphabetical order.', 'jobportal-framework'),
					),
				)
			));

			$configs['service-skills-settings'] = apply_filters('jobportal_register_term_meta_service_skills', array(
				'name' => esc_html__('', 'jobportal-framework'),
				'layout' => 'horizontal',
				'taxonomy' => array('service-skills'),
				'fields' => array(
					array(
						'id' => 'service_skills_order',
						'title' => esc_html__('Number Order by', 'jobportal-framework'),
						'type' => 'text',
						'col' => '10',
						'pattern' => '[0-9]*',
						'default' => '',
						'desc' => esc_html__('Enter a numeric value to control the display order of this term. Lower numbers will be displayed first when listing terms. Leave empty to use default alphabetical order.', 'jobportal-framework'),
					),
				)
			));

			$configs['service-language-settings'] = apply_filters('jobportal_register_term_meta_service_language', array(
				'name' => esc_html__('', 'jobportal-framework'),
				'layout' => 'horizontal',
				'taxonomy' => array('service-language'),
				'fields' => array(
					array(
						'id' => 'service_language_order',
						'title' => esc_html__('Number Order by', 'jobportal-framework'),
						'type' => 'text',
						'col' => '10',
						'pattern' => '[0-9]*',
						'default' => '',
						'desc' => esc_html__('Enter a numeric value to control the display order of this term. Lower numbers will be displayed first when listing terms. Leave empty to use default alphabetical order.', 'jobportal-framework'),
					),
				)
			));

			$configs['jobs-skills-settings'] = apply_filters('jobportal_register_term_meta_jobs_skills', array(
				'name' => esc_html__('', 'jobportal-framework'),
				'layout' => 'horizontal',
				'taxonomy' => array('jobs-skills'),
				'fields' => array(
					array(
						'id' => 'jobs_skills_order',
						'title' => esc_html__('Number Order by', 'jobportal-framework'),
						'type' => 'text',
						'col' => '10',
						'pattern' => '[0-9]*',
						'default' => '',
						'desc' => esc_html__('Enter a numeric value to control the display order of this term. Lower numbers will be displayed first when listing terms. Leave empty to use default alphabetical order.', 'jobportal-framework'),
					),
				)
			));

			$configs['jobs-type-settings'] = apply_filters('jobportal_register_term_meta_jobs_type', array(
				'name' => esc_html__('', 'jobportal-framework'),
				'layout' => 'horizontal',
				'taxonomy' => array('jobs-type'),
				'fields' => array(
					array(
						'id' => 'jobs_type_order',
						'title' => esc_html__('Number Order by', 'jobportal-framework'),
						'type' => 'text',
						'col' => '10',
						'pattern' => '[0-9]*',
						'default' => '',
						'desc' => esc_html__('Enter a numeric value to control the display order of this term. Lower numbers will be displayed first when listing terms. Leave empty to use default alphabetical order.', 'jobportal-framework'),
					),
				)
			));

			$configs['jobs-gender-settings'] = apply_filters('jobportal_register_term_meta_jobs_gender', array(
				'name' => esc_html__('', 'jobportal-framework'),
				'layout' => 'horizontal',
				'taxonomy' => array('jobs-gender'),
				'fields' => array(
					array(
						'id' => 'jobs_gender_order',
						'title' => esc_html__('Number Order by', 'jobportal-framework'),
						'type' => 'text',
						'col' => '10',
						'pattern' => '[0-9]*',
						'default' => '',
						'desc' => esc_html__('Enter a numeric value to control the display order of this term. Lower numbers will be displayed first when listing terms. Leave empty to use default alphabetical order.', 'jobportal-framework'),
					),
				)
			));

			$configs['jobs-categories-settings'] = apply_filters('jobportal_register_term_meta_jobs_categories', array(
				'name' => esc_html__('', 'jobportal-framework'),
				'layout' => 'horizontal',
				'taxonomy' => array('jobs-categories'),
				'fields' => array(
					array(
						'id' => 'jobs_categories_order',
						'title' => esc_html__('Number Order by', 'jobportal-framework'),
						'type' => 'text',
						'col' => '10',
						'pattern' => '[0-9]*',
						'default' => '',
						'desc' => esc_html__('Enter a numeric value to control the display order of this term. Lower numbers will be displayed first when listing terms. Leave empty to use default alphabetical order.', 'jobportal-framework'),
					),
				)
			));

			$configs['candidate-languages-settings'] = apply_filters('jobportal_register_term_meta_candidate_languages', array(
				'name' => esc_html__('', 'jobportal-framework'),
				'layout' => 'horizontal',
				'taxonomy' => array('candidate_languages'),
				'fields' => array(
					array(
						'id' => 'candidate_languages_order',
						'title' => esc_html__('Number Order by', 'jobportal-framework'),
						'type' => 'text',
						'col' => '10',
						'pattern' => '[0-9]*',
						'default' => '',
						'desc' => esc_html__('Enter a numeric value to control the display order of this term. Lower numbers will be displayed first when listing terms. Leave empty to use default alphabetical order.', 'jobportal-framework'),
					),
				)
			));

			$configs['candidate-education-levels-settings'] = apply_filters('jobportal_register_term_meta_candidate_education_levels', array(
				'name' => esc_html__('', 'jobportal-framework'),
				'layout' => 'horizontal',
				'taxonomy' => array('candidate_education_levels'),
				'fields' => array(
					array(
						'id' => 'candidate_education_levels_order',
						'title' => esc_html__('Number Order by', 'jobportal-framework'),
						'type' => 'text',
						'col' => '10',
						'pattern' => '[0-9]*',
						'default' => '',
						'desc' => esc_html__('Enter a numeric value to control the display order of this term. Lower numbers will be displayed first when listing terms. Leave empty to use default alphabetical order.', 'jobportal-framework'),
					),
				)
			));

			$configs['candidate-skills-settings'] = apply_filters('jobportal_register_term_meta_candidate_skills', array(
				'name' => esc_html__('', 'jobportal-framework'),
				'layout' => 'horizontal',
				'taxonomy' => array('candidate_skills'),
				'fields' => array(
					array(
						'id' => 'candidate_skills_order',
						'title' => esc_html__('Number Order by', 'jobportal-framework'),
						'type' => 'text',
						'col' => '10',
						'pattern' => '[0-9]*',
						'default' => '',
						'desc' => esc_html__('Enter a numeric value to control the display order of this term. Lower numbers will be displayed first when listing terms. Leave empty to use default alphabetical order.', 'jobportal-framework'),
					),
				)
			));

			$configs['candidate-gender-settings'] = apply_filters('jobportal_register_term_meta_candidate_gender', array(
				'name' => esc_html__('', 'jobportal-framework'),
				'layout' => 'horizontal',
				'taxonomy' => array('candidate_gender'),
				'fields' => array(
					array(
						'id' => 'candidate_gender_order',
						'title' => esc_html__('Number Order by', 'jobportal-framework'),
						'type' => 'text',
						'col' => '10',
						'pattern' => '[0-9]*',
						'default' => '',
						'desc' => esc_html__('Enter a numeric value to control the display order of this term. Lower numbers will be displayed first when listing terms. Leave empty to use default alphabetical order.', 'jobportal-framework'),
					),
				)
			));

			$configs['candidate-categories-settings'] = apply_filters('jobportal_register_term_meta_candidate_categories', array(
				'name' => esc_html__('', 'jobportal-framework'),
				'layout' => 'horizontal',
				'taxonomy' => array('candidate_categories'),
				'fields' => array(
					array(
						'id' => 'candidate_categories_order',
						'title' => esc_html__('Number Order by', 'jobportal-framework'),
						'type' => 'text',
						'col' => '10',
						'pattern' => '[0-9]*',
						'default' => '',
						'desc' => esc_html__('Enter a numeric value to control the display order of this term. Lower numbers will be displayed first when listing terms. Leave empty to use default alphabetical order.', 'jobportal-framework'),
					),
				)
			));

			$configs['company-categories-settings'] = apply_filters('jobportal_register_term_meta_company_categories', array(
				'name' => esc_html__('', 'jobportal-framework'),
				'layout' => 'horizontal',
				'taxonomy' => array('company-categories'),
				'fields' => array(
					array(
						'id' => 'company_categories_order',
						'title' => esc_html__('Number Order by', 'jobportal-framework'),
						'type' => 'text',
						'col' => '10',
						'pattern' => '[0-9]*',
						'default' => '',
						'desc' => esc_html__('Enter a numeric value to control the display order of this term. Lower numbers will be displayed first when listing terms. Leave empty to use default alphabetical order.', 'jobportal-framework'),
					),
				)
			));

			if (jobportal_get_option('enable_option_country') === '1' && jobportal_get_option('enable_option_state') === '1') {
				$select_option_country = jobportal_get_option('select_option_country');
				$countries = jobportal_get_countries();
				$keys = $values = array();
				if (!empty($select_option_country)) {
					foreach ($select_option_country as $key_country => $option_country) {
						if (array_key_exists($option_country, $countries)) {
							$keys[] = $option_country;
							$values[] = $countries[$option_country];
						}
					}
					$list_country = array_combine($keys, $values);
				} else {
					$list_country = $countries;
				}

				$taxonomy_state = array('jobs-state', 'company-state', 'candidate_state', 'service-state');
				foreach ($taxonomy_state  as $term) {
					$configs[$term . '-settings'] = apply_filters('jobportal_register_term_meta_' . $term, array(
						'name'     => '',
						'layout' => 'horizontal',
						'taxonomy' => array($term),
						'fields' => array(
							array(
								'id'      => $term . '-country',
								'title'   => esc_html__('Country', 'jobportal-framework'),
								'type'    => 'select',
								'options' => $list_country,
							),
						)
					));
				}
			}

			if (jobportal_get_option('enable_option_state') === '1') {
				$taxonomy_state_location = array(
					'jobs-state' => 'jobs-location',
					'company-state' => 'company-location',
					'candidate_state' => 'candidate_locations',
					'service-state' => 'service-location'
				);
				foreach ($taxonomy_state_location as $key => $value) {
					$list_state = jobportal_get_option_taxonomy($key);
					$configs[$value . 'state-location'] = apply_filters('jobportal_register_state_location_' . $value, array(
						'name'     => '',
						'layout' => 'horizontal',
						'taxonomy' => array($value),
						'fields' => array(
							array(
								'id'      => $value . '-state',
								'title'   => esc_html__('Province / State', 'jobportal-framework'),
								'default' => '',
								'type'    => 'select',
								'options' => $list_state,
							),
						)
					));
				}
			}

			return apply_filters('jobportal_register_term_meta', $configs);
		}

		/**
		 * Register meta boxes
		 * @param $configs
		 * @return mixed
		 */
		public function register_meta_boxes($configs)
		{
			$meta_prefix = JOBPORTAL_METABOX_PREFIX;
			$dec_point = jobportal_get_option('decimal_separator', '.');
			$format_number = '^[0-9]+([' . $dec_point . '][0-9]+)?$';

			//Custom field jobs
			$render_custom_field_jobs = jobportal_render_custom_field('jobs');
			$custom_field_jobs = array();
			if (count($render_custom_field_jobs) > 0) {
				$custom_field_jobs = array(
					array(
						'id' => "{$meta_prefix}custom_field_jobs_tab",
						'title' => esc_html__('Additional Fields', 'jobportal-framework'),
						'icon' => 'dashicons dashicons-welcome-add-page',
						'fields' => $render_custom_field_jobs
					),
				);
			}

			//Custom field company
			$render_custom_field_company = jobportal_render_custom_field('company');

			//Custom field candidate
			$render_custom_field_candidate = jobportal_render_custom_field('candidate');
			$custom_field_candidate = array();
			if (count($render_custom_field_candidate) > 0) {
				$custom_field_candidate = array(
					array(
						'id' => "{$meta_prefix}custom_field_candidate_tab",
						'title' => esc_html__('Additional Fields', 'jobportal-framework'),
						'icon' => 'dashicons dashicons-welcome-add-page',
						'fields' => $render_custom_field_candidate
					),
				);
			}

			$candidate_package_service = $candidate_package_service_featured = $candidate_package_jobs_apply = $candidate_package_jobs_wishlist =
				$candidate_package_company_follow = $candidate_package_contact_company = $candidate_package_info_company =
				$candidate_package_send_message = $candidate_package_review_and_commnent = array();

			if (jobportal_get_option('enable_post_type_service') === '1') {

				//Package Service
				$candidate_package_service = array(
					'type' => 'row',
					'col' => '4',
					'fields' => array(
						array(
							'id' => "{$meta_prefix}enable_package_service_unlimited",
							'title' => esc_html__('Unlimited Service', 'jobportal-framework'),
							'type' => 'button_set',
							'options' => array(
								'1' => esc_html__('Yes', 'jobportal-framework'),
								'0' => esc_html__('No', 'jobportal-framework'),
							),
							'default' => '0',
						),
						array(
							'id' => "{$meta_prefix}candidate_package_number_service",
							'title' => esc_html__('Number Service', 'jobportal-framework'),
							'type' => 'text',
							'default' => '',
							'pattern' => '[0-9]*',
							'required' => array("{$meta_prefix}enable_package_service_unlimited", '=', '0'),
						),
					)
				);
				$candidate_package_service_featured = array(
					'type' => 'row',
					'col' => '4',
					'fields' => array(
						array(
							'id' => "{$meta_prefix}enable_package_service_featured_unlimited",
							'title' => esc_html__('Unlimited Service Featured', 'jobportal-framework'),
							'type' => 'button_set',
							'options' => array(
								'1' => esc_html__('Yes', 'jobportal-framework'),
								'0' => esc_html__('No', 'jobportal-framework'),
							),
							'default' => '0',
						),
						array(
							'id' => "{$meta_prefix}candidate_package_number_service_featured",
							'title' => esc_html__('Number Featured Service', 'jobportal-framework'),
							'type' => 'text',
							'default' => '',
							'pattern' => '[0-9]*',
							'required' => array("{$meta_prefix}enable_package_service_featured_unlimited", '=', '0'),
						),
					)
				);

				//Single Service
				$configs['service_meta_boxes'] = apply_filters('jobportal_register_meta_boxes_service', array(
					'name' => esc_html__('Service Information', 'jobportal-framework'),
					'post_type' => array('service'),
					'section' => array_merge(
						apply_filters('jobportal_register_meta_boxes_service_top', array()),
						apply_filters(
							'jobportal_register_meta_boxes_service_main',
							array_merge(
								array(
									array(
										'id' => "{$meta_prefix}details_candidate_basic",
										'title' => esc_html__('Basic', 'jobportal-framework'),
										'icon' => 'dashicons dashicons-admin-home',
										'fields' => array(
											array(
												'id' => "{$meta_prefix}service_featured",
												'title' => esc_html__('Mark this service as featured ?', 'jobportal-framework'),
												'type' => 'button_set',
												'col' => '4',
												'options' => array(
													'1' => esc_html__('Yes', 'jobportal-framework'),
													'0' => esc_html__('No', 'jobportal-framework'),
												),
												'default' => '0',
											),
										)
									),
									array(
										'id' => "{$meta_prefix}details_candidate_package",
										'title' => esc_html__('package', 'jobportal-framework'),
										'icon' => 'dashicons dashicons-carrot',
										'fields' => array(
											array(
												'type' => 'row',
												'col' => '12',
												'fields' => array(
													array(
														'id' => "{$meta_prefix}service_price",
														'title' => esc_html__('Start Price', 'jobportal-framework'),
														'default' => '30',
														'type' => 'text',
														'col' => '4',
													),
													array(
														'id' => "{$meta_prefix}service_number_time",
														'title' => esc_html__('Transfer time', 'jobportal-framework'),
														'default' => '3',
														'type' => 'text',
														'col' => '4',
													),
													array(
														'id' => "{$meta_prefix}service_time_type",
														'title' => esc_html__('Time Type', 'jobportal-framework'),
														'type' => 'select',
														'options' => array(
															'hr' => esc_html__('Hour', 'jobportal-framework'),
															'day' => esc_html__('Day', 'jobportal-framework'),
															'week' => esc_html__('Week', 'jobportal-framework'),
															'month' => esc_html__('Month', 'jobportal-framework'),
														),
														'col' => '4',
														'default' => 'hr',
													),
													array(
														'id' => "{$meta_prefix}service_language_level",
														'title' => esc_html__('Languages Level', 'jobportal-framework'),
														'type' => 'select',
														'options' => array(
															'basic' => esc_html__('Basic', 'jobportal-framework'),
															'conversational' => esc_html__('Conversational', 'jobportal-framework'),
															'fluent' => esc_html__('Fluent', 'jobportal-framework'),
															'native' => esc_html__('Native or Bilingual', 'jobportal-framework'),
															'professional' => esc_html__('Professional', 'jobportal-framework'),
														),
														'col' => '4',
														'default' => 'native',
													),
												)
											),
											array(
												'id' => "{$meta_prefix}service_tab_addon",
												'type' => 'panel',
												'title' => esc_html__('Add ons', 'jobportal-framework'),
												'sort' => true,
												'panel_title' => 'label',
												'fields' => array(
													array(
														'type' => 'row',
														'col' => '12',
														'fields' => array(
															array(
																'id' => "{$meta_prefix}service_addons_title",
																'title' => esc_html__('Title', 'jobportal-framework'),
																'type' => 'text',
																'col' => '6',
															),
															array(
																'id' => "{$meta_prefix}service_addons_price",
																'title' => esc_html__('Price', 'jobportal-framework'),
																'type' => 'text',
																'col' => '6',
															),
															array(
																'id' => "{$meta_prefix}service_addons_description",
																'title' => esc_html__('Description', 'jobportal-framework'),
																'type' => 'textarea',
																'col' => '12',
															),
														)
													)
												)
											),
										)
									),
									array(
										'id' => "{$meta_prefix}details_service_faq",
										'title' => esc_html__('Faqs', 'jobportal-framework'),
										'icon' => 'dashicons dashicons-palmtree',
										'fields' => array(
											array(
												'id' => "{$meta_prefix}service_tab_faq",
												'type' => 'panel',
												'title' => esc_html__('Faqs', 'jobportal-framework'),
												'sort' => true,
												'panel_title' => 'label',
												'fields' => array(
													array(
														'type' => 'row',
														'col' => '12',
														'fields' => array(
															array(
																'id' => "{$meta_prefix}service_faq_title",
																'title' => esc_html__('Title', 'jobportal-framework'),
																'type' => 'text',
																'col' => '12',
															),
															array(
																'id' => "{$meta_prefix}service_faq_description",
																'title' => esc_html__('Description', 'jobportal-framework'),
																'type' => 'textarea',
																'col' => '12',
															),
														)
													)
												)
											),
										)
									),
									array(
										'id' => "{$meta_prefix}location_tab",
										'title' => esc_html__('Location', 'jobportal-framework'),
										'icon' => 'dashicons-location-alt',
										'fields' => array(
											array(
												'type' => 'row',
												'col' => '12',
												'fields' => array(
													array(
														'id' => "{$meta_prefix}service_address",
														'title' => esc_html__('Marker position on map', 'jobportal-framework'),
														'desc' => esc_html__('Full Address', 'jobportal-framework'),
														'type' => 'text',
														'col' => 4
													),
													array(
														'id' => "{$meta_prefix}service_latitude",
														'title' => esc_html__('Latitude', 'jobportal-framework'),
														'desc' => esc_html__('Latitude Details', 'jobportal-framework'),
														'type' => 'text',
														'col' => 4
													),
													array(
														'id' => "{$meta_prefix}service_longtitude",
														'title' => esc_html__('Longtitude', 'jobportal-framework'),
														'desc' => esc_html__('Longtitude Details', 'jobportal-framework'),
														'type' => 'text',
														'col' => 4
													),
													array(
														'id' => "{$meta_prefix}service_location",
														'title' => esc_html__('Service Location at Google Map', 'jobportal-framework'),
														'desc' => esc_html__('Drag the google map marker to point your service location.', 'jobportal-framework'),
														'type' => 'map',
														'address_field' => "{$meta_prefix}service_address",
													),
												)
											)
										)
									),
									array(
										'id' => "{$meta_prefix}gallery_service_tab",
										'title' => esc_html__('Gallery Images', 'jobportal-framework'),
										'icon' => 'dashicons-format-gallery',
										'fields' => array(
											array(
												'id' => "{$meta_prefix}service_images",
												'title' => esc_html__('Gallery', 'jobportal-framework'),
												'type' => 'gallery',
											),
										)
									),
									array(
										'id' => "{$meta_prefix}video_service_tab",
										'title' => esc_html__('Video', 'jobportal-framework'),
										'icon' => 'dashicons-video-alt3',
										'fields' => array(
											array(
												'id' => "{$meta_prefix}service_video_url",
												'title' => esc_html__('Video URL', 'jobportal-framework'),
												'desc' => esc_html__('Input only URL. YouTube, Vimeo, SWF File and MOV File', 'jobportal-framework'),
												'type' => 'text',
												'col' => 12,
											),
											array(
												'id' => "{$meta_prefix}service_video_image",
												'title' => esc_html__('Video Image', 'jobportal-framework'),
												'type' => 'gallery',
												'col' => 12,
											),
										)
									),
								)
							)
						),
						apply_filters('jobportal_register_meta_boxes_service_bottom', array())
					),
				));

				//Order Service
				$configs['service_order_meta_boxes'] = array(
					'name' => esc_html__('Service Order Settings', 'jobportal-framework'),
					'post_type' => array('service_order'),
					'fields' => array(
						array(
							'type' => 'row',
							'col' => '12',
							'fields' => array(
								array(
									'id' => "{$meta_prefix}service_order_payment_status",
									'title' => esc_html__('Status', 'jobportal-framework'),
									'type' => 'button_set',
									'options' => array(
										'pending' => esc_html__('Pending', 'jobportal-framework'),
										'inprogress' => esc_html__('In Process', 'jobportal-framework'),
										'transferring' => esc_html__('Transferring', 'jobportal-framework'),
										'canceled' => esc_html__('Canceled', 'jobportal-framework'),
										'completed' => esc_html__('Completed', 'jobportal-framework'),
										'expired' =>  esc_html__('Expired', 'jobportal-framework'),
										'refund' => esc_html__('Refund', 'jobportal-framework'),
									),
									'default' => 'pending',
								),
							)
						),
						array(
							'type' => 'row',
							'col' => '12',
							'fields' => array(
								array(
									'id' => "{$meta_prefix}service_order_user_id",
									'title' => esc_html__('User Order id', 'jobportal-framework'),
									'default' => '',
									'type' => 'text',
									'col' => '4',
								),
								array(
									'id' => "{$meta_prefix}service_order_author_service",
									'title' => esc_html__('Author Service', 'jobportal-framework'),
									'default' => '',
									'type' => 'text',
									'col' => '4',
								),
								array(
									'id' => "{$meta_prefix}service_order_item_id",
									'title' => esc_html__('Package id', 'jobportal-framework'),
									'default' => '',
									'type' => 'text',
									'col' => '4',
								),
								array(
									'id' => "{$meta_prefix}service_order_price",
									'title' => esc_html__('Price', 'jobportal-framework'),
									'default' => '',
									'type' => 'text',
									'col' => '4',
								),
								array(
									'id' => "{$meta_prefix}service_order_date",
									'title' => esc_html__('Activate Date', 'jobportal-framework'),
									'default' => '',
									'type' => 'text',
									'col' => '4',
								),
								array(
									'id' => "{$meta_prefix}service_order_payment_method",
									'title' => esc_html__('Payment Method', 'jobportal-framework'),
									'default' => '',
									'type' => 'text',
									'col' => '4',
								),
							)
						),
						array(
							'type' => 'divide',
						),
						array(
							'type' => 'row',
							'col' => '12',
							'fields' => array(
								array(
									'id' => "{$meta_prefix}service_refund_payment_method",
									'title' => esc_html__('Payment method refund', 'jobportal-framework'),
									'type' => 'select',
									'options' => array(
										'wire_transfer' => esc_html__('Wire Transfer', 'jobportal-framework'),
										'stripe' => esc_html__('Pay With Stripe', 'jobportal-framework'),
										'paypal' => esc_html__('Pay With Paypal', 'jobportal-framework'),
									),
									'col' => '4',
									'default' => 'wire_transfer',
									'required' => array("{$meta_prefix}service_order_payment_status", '=', 'refund'),

								),
								array(
									'id' => "{$meta_prefix}service_refund_content",
									'title' => esc_html__('Content refund', 'jobportal-framework'),
									'type' => 'textarea',
									'default' => '',
									'col' => '12',
									'required' => array("{$meta_prefix}service_order_payment_status", '=', 'refund'),
								),
							)
						),
					),
				);

				//Withdrawals
				$configs['service_withdraw_meta_boxes'] = array(
					'name' => esc_html__('Service Withdraw Settings', 'jobportal-framework'),
					'post_type' => array('service_withdraw'),
					'fields' => array(
						array(
							'type' => 'row',
							'col' => '12',
							'fields' => array(
								array(
									'id' => "{$meta_prefix}service_withdraw_status",
									'title' => esc_html__('Status', 'jobportal-framework'),
									'type' => 'button_set',
									'options' => array(
										'pending' => esc_html__('Pending', 'jobportal-framework'),
										'completed' => esc_html__('Completed', 'jobportal-framework'),
										'canceled' => esc_html__('Canceled', 'jobportal-framework'),
									),
									'default' => 'pending',
								),
							)
						),
						array(
							'type' => 'row',
							'col' => '12',
							'fields' => array(
								array(
									'id' => "{$meta_prefix}service_withdraw_user_id",
									'title' => esc_html__('User Id', 'jobportal-framework'),
									'default' => '',
									'type' => 'text',
									'col' => '6',
								),
								array(
									'id' => "{$meta_prefix}service_withdraw_payment_method",
									'title' => esc_html__('Payment method refund', 'jobportal-framework'),
									'default' => '',
									'type' => 'text',
									'col' => '6',
								),
								array(
									'id' => "{$meta_prefix}service_withdraw_price",
									'title' => esc_html__('Price', 'jobportal-framework'),
									'default' => '',
									'type' => 'text',
									'col' => '6',
								),
								array(
									'id' => "{$meta_prefix}service_withdraw_total_price",
									'title' => esc_html__('Available Balance', 'jobportal-framework'),
									'default' => '',
									'type' => 'text',
									'col' => '6',
								),
							)
						),
					),
				);
			}
			if (jobportal_get_option('enable_candidate_package_jobs_apply') === '1') {
				$candidate_package_jobs_apply = array(
					'type' => 'row',
					'col' => '4',
					'fields' => array(
						array(
							'id' => "{$meta_prefix}show_package_jobs_apply",
							'title' => esc_html__('Show Jobs Apply', 'jobportal-framework'),
							'type' => 'button_set',
							'options' => array(
								'1' => esc_html__('Yes', 'jobportal-framework'),
								'0' => esc_html__('No', 'jobportal-framework'),
							),
							'default' => '0',
						),
						array(
							'id' => "{$meta_prefix}enable_package_jobs_apply_unlimited",
							'title' => esc_html__('Unlimited Jobs Apply', 'jobportal-framework'),
							'type' => 'button_set',
							'options' => array(
								'1' => esc_html__('Yes', 'jobportal-framework'),
								'0' => esc_html__('No', 'jobportal-framework'),
							),
							'default' => '0',
							'required' => array("{$meta_prefix}show_package_jobs_apply", '=', '1'),
						),
						array(
							'id' => "{$meta_prefix}candidate_package_number_jobs_apply",
							'title' => esc_html__('Number Jobs Apply', 'jobportal-framework'),
							'type' => 'text',
							'default' => '',
							'pattern' => '[0-9]*',
							'required' => array(
								array("{$meta_prefix}show_package_jobs_apply", '=', '1'),
								array("{$meta_prefix}enable_package_jobs_apply_unlimited", '!=', '1')
							),
						),
					)
				);
			}

			if (jobportal_get_option('enable_candidate_package_jobs_wishlist') === '1') {
				$candidate_package_jobs_wishlist = array(
					'type' => 'row',
					'col' => '4',
					'fields' => array(
						array(
							'id' => "{$meta_prefix}show_package_jobs_wishlist",
							'title' => esc_html__('Show Jobs Wishlist', 'jobportal-framework'),
							'type' => 'button_set',
							'options' => array(
								'1' => esc_html__('Yes', 'jobportal-framework'),
								'0' => esc_html__('No', 'jobportal-framework'),
							),
							'default' => '0',
						),
						array(
							'id' => "{$meta_prefix}enable_package_jobs_wishlist_unlimited",
							'title' => esc_html__('Unlimited Jobs Wishlist', 'jobportal-framework'),
							'type' => 'button_set',
							'options' => array(
								'1' => esc_html__('Yes', 'jobportal-framework'),
								'0' => esc_html__('No', 'jobportal-framework'),
							),
							'default' => '0',
							'required' => array(
								array("{$meta_prefix}show_package_jobs_wishlist", '=', '1'),
							),
						),
						array(
							'id' => "{$meta_prefix}candidate_package_number_jobs_wishlist",
							'title' => esc_html__('Number Jobs Wishlist', 'jobportal-framework'),
							'type' => 'text',
							'default' => '',
							'pattern' => '[0-9]*',
							'required' => array(
								array("{$meta_prefix}show_package_jobs_wishlist", '=', '1'),
								array("{$meta_prefix}enable_package_jobs_wishlist_unlimited", '!=', '1')
							),
						),
					)
				);
			}

			if (jobportal_get_option('enable_candidate_package_company_follow') === '1') {
				$candidate_package_company_follow = array(
					'type' => 'row',
					'col' => '4',
					'fields' => array(
						array(
							'id' => "{$meta_prefix}show_package_company_follow",
							'title' => esc_html__('Show Company Follow', 'jobportal-framework'),
							'type' => 'button_set',
							'options' => array(
								'1' => esc_html__('Yes', 'jobportal-framework'),
								'0' => esc_html__('No', 'jobportal-framework'),
							),
							'default' => '0',
						),
						array(
							'id' => "{$meta_prefix}enable_package_company_follow_unlimited",
							'title' => esc_html__('Unlimited Company Follow', 'jobportal-framework'),
							'type' => 'button_set',
							'options' => array(
								'1' => esc_html__('Yes', 'jobportal-framework'),
								'0' => esc_html__('No', 'jobportal-framework'),
							),
							'default' => '0',
							'required' => array(
								array("{$meta_prefix}show_package_company_follow", '=', '1'),
							),
						),
						array(
							'id' => "{$meta_prefix}candidate_package_number_company_follow",
							'title' => esc_html__('Number Company Follow', 'jobportal-framework'),
							'type' => 'text',
							'default' => '',
							'pattern' => '[0-9]*',
							'required' => array(
								array("{$meta_prefix}show_package_company_follow", '=', '1'),
								array("{$meta_prefix}enable_package_company_follow_unlimited", '!=', '1')
							),
						),
					)
				);
			}

			if (jobportal_get_option('enable_candidate_package_contact_company') === '1') {
				$candidate_package_contact_company = array(
					'id' => "{$meta_prefix}show_package_contact_company",
					'title' => esc_html__('View contact company in jobs', 'jobportal-framework'),
					'type' => 'button_set',
					'options' => array(
						'1' => esc_html__('Yes', 'jobportal-framework'),
						'0' => esc_html__('No', 'jobportal-framework'),
					),
					'default' => '0',
				);
			}

			if (jobportal_get_option('enable_candidate_package_info_company') === '1') {
				$candidate_package_info_company =  array(
					'id' => "{$meta_prefix}show_package_info_company",
					'title' => esc_html__('View info company', 'jobportal-framework'),
					'type' => 'button_set',
					'options' => array(
						'1' => esc_html__('Yes', 'jobportal-framework'),
						'0' => esc_html__('No', 'jobportal-framework'),
					),
					'default' => '0',
				);
			}

			if (jobportal_get_option('enable_candidate_package_send_message') === '1') {
				$candidate_package_send_message =  array(
					'id' => "{$meta_prefix}show_package_send_message",
					'title' => esc_html__('Send Message', 'jobportal-framework'),
					'type' => 'button_set',
					'options' => array(
						'1' => esc_html__('Yes', 'jobportal-framework'),
						'0' => esc_html__('No', 'jobportal-framework'),
					),
					'default' => '0',
				);
			}

			if (jobportal_get_option('enable_candidate_package_review_and_commnent') === '1') {
				$candidate_package_review_and_commnent =  array(
					'id' => "{$meta_prefix}show_package_review_and_commnent",
					'title' => esc_html__('Review and Commnet', 'jobportal-framework'),
					'type' => 'button_set',
					'options' => array(
						'1' => esc_html__('Yes', 'jobportal-framework'),
						'0' => esc_html__('No', 'jobportal-framework'),
					),
					'default' => '',
				);
			}

			//Company Package
			$company_package_download_cv_candidate = $company_package_candidate_follow  = $company_package_invite_candidate = $company_package_send_message_candidate =
				$company_package_print_candidate = $company_package_review_candidate = $company_package_info_candidate = array();

			if (jobportal_get_option('enable_company_package_download_cv') === '1') {
				$company_package_download_cv_candidate = array(
					'type' => 'row',
					'col' => '4',
					'fields' => array(
						array(
							'id' => "{$meta_prefix}show_package_company_download_cv",
							'title' => esc_html__('Download CV', 'jobportal-framework'),
							'type' => 'button_set',
							'options' => array(
								'1' => esc_html__('Yes', 'jobportal-framework'),
								'0' => esc_html__('No', 'jobportal-framework'),
							),
							'default' => '1',
						),
						array(
							'id' => "{$meta_prefix}enable_package_download_cv_unlimited",
							'title' => esc_html__('Unlimited Download CV', 'jobportal-framework'),
							'type' => 'button_set',
							'options' => array(
								'1' => esc_html__('Yes', 'jobportal-framework'),
								'0' => esc_html__('No', 'jobportal-framework'),
							),
							'default' => '0',
							'required' => array("{$meta_prefix}show_package_company_download_cv", '=', '1'),
						),
						array(
							'id' => "{$meta_prefix}company_package_number_download_cv",
							'title' => esc_html__('Number Download CV', 'jobportal-framework'),
							'type' => 'text',
							'default' => '',
							'pattern' => '[0-9]*',
							'required' => array(
								array("{$meta_prefix}show_package_company_download_cv", '=', '1'),
								array("{$meta_prefix}enable_package_download_cv_unlimited", '!=', '1')
							),
						),
					)
				);
			}

			if (jobportal_get_option('enable_company_package_candidate_follow') === '1') {
				$company_package_candidate_follow = array(
					'type' => 'row',
					'col' => '4',
					'fields' => array(
						array(
							'id' => "{$meta_prefix}show_package_company_candidate_follow",
							'title' => esc_html__('Follow Candidates', 'jobportal-framework'),
							'type' => 'button_set',
							'options' => array(
								'1' => esc_html__('Yes', 'jobportal-framework'),
								'0' => esc_html__('No', 'jobportal-framework'),
							),
							'default' => '1',
						),
						array(
							'id' => "{$meta_prefix}enable_package_candidate_follow_unlimited",
							'title' => esc_html__('Unlimited Candidate Follow', 'jobportal-framework'),
							'type' => 'button_set',
							'options' => array(
								'1' => esc_html__('Yes', 'jobportal-framework'),
								'0' => esc_html__('No', 'jobportal-framework'),
							),
							'default' => '0',
							'required' => array("{$meta_prefix}show_package_company_candidate_follow", '=', '1'),
						),
						array(
							'id' => "{$meta_prefix}company_package_number_candidate_follow",
							'title' => esc_html__('Number Candidate Follow', 'jobportal-framework'),
							'type' => 'text',
							'default' => '',
							'pattern' => '[0-9]*',
							'required' => array(
								array("{$meta_prefix}show_package_company_candidate_follow", '=', '1'),
								array("{$meta_prefix}enable_package_candidate_follow_unlimited", '!=', '1')
							),
						),
					)
				);
			}


			if (jobportal_get_option('enable_company_package_invite') === '1') {
				$company_package_invite_candidate =  array(
					'id' => "{$meta_prefix}show_package_company_invite",
					'title' => esc_html__('Invite Candidates', 'jobportal-framework'),
					'type' => 'button_set',
					'options' => array(
						'1' => esc_html__('Yes', 'jobportal-framework'),
						'0' => esc_html__('No', 'jobportal-framework'),
					),
					'default' => '0',
				);
			}

			if (jobportal_get_option('enable_company_package_send_message') === '1') {
				$company_package_send_message_candidate =  array(
					'id' => "{$meta_prefix}show_package_company_send_message",
					'title' => esc_html__('Send Messages Candidate', 'jobportal-framework'),
					'type' => 'button_set',
					'options' => array(
						'1' => esc_html__('Yes', 'jobportal-framework'),
						'0' => esc_html__('No', 'jobportal-framework'),
					),
					'default' => '0',
				);
			}

			if (jobportal_get_option('enable_company_package_print') === '1') {
				$company_package_print_candidate =  array(
					'id' => "{$meta_prefix}show_package_company_print",
					'title' => esc_html__('Print Candidate Profile', 'jobportal-framework'),
					'type' => 'button_set',
					'options' => array(
						'1' => esc_html__('Yes', 'jobportal-framework'),
						'0' => esc_html__('No', 'jobportal-framework'),
					),
					'default' => '0',
				);
			}

			if (jobportal_get_option('enable_company_package_review_and_commnent') === '1') {
				$company_package_review_candidate =  array(
					'id' => "{$meta_prefix}show_package_company_review_and_commnent",
					'title' => esc_html__('Review and Comments', 'jobportal-framework'),
					'type' => 'button_set',
					'options' => array(
						'1' => esc_html__('Yes', 'jobportal-framework'),
						'0' => esc_html__('No', 'jobportal-framework'),
					),
					'default' => '0',
				);
			}

			if (jobportal_get_option('enable_company_package_info') === '1') {
				$company_package_info_candidate =  array(
					'id' => "{$meta_prefix}show_package_company_info",
					'title' => esc_html__('View Information Company', 'jobportal-framework'),
					'type' => 'button_set',
					'options' => array(
						'1' => esc_html__('Yes', 'jobportal-framework'),
						'0' => esc_html__('No', 'jobportal-framework'),
					),
					'default' => '0',
				);
			}


			$candidate_custom_social = array();
			$jobportal_social_fields = jobportal_get_option('jobportal_social_fields');
			if (is_array($jobportal_social_fields) && !empty($jobportal_social_fields)) {
				foreach ($jobportal_social_fields as $key => $value) {
					$candidate_custom_social[] = array(
						'id' => "{$meta_prefix}candidate_{$value['social_name']}",
						'title' => $value['social_name'],
						'type' => 'text',
						'col' => '6',
					);
				}
			}

			$candidate_custom_social0 = isset($candidate_custom_social[0]) ? $candidate_custom_social[0] : array();
			$candidate_custom_social1 = isset($candidate_custom_social[1]) ? $candidate_custom_social[1] : array();
			$candidate_custom_social2 = isset($candidate_custom_social[2]) ? $candidate_custom_social[2] : array();
			$candidate_custom_social3 = isset($candidate_custom_social[3]) ? $candidate_custom_social[3] : array();
			$configs['jobs_meta_boxes'] = apply_filters('jobportal_register_meta_boxes_jobs', array(
				'name' => esc_html__('Jobs Information', 'jobportal-framework'),
				'post_type' => array('jobs'),
				'section' => array_merge(
					apply_filters('jobportal_register_meta_boxes_jobs_top', array()),
					apply_filters(
						'jobportal_register_meta_boxes_jobs_main',
						array_merge(
							array(
								array(
									'id' => "{$meta_prefix}details_tab",
									'title' => esc_html__('Basic Infomation', 'jobportal-framework'),
									'icon' => 'dashicons-admin-home',
									'fields' => array(
										array(
											'type' => 'row',
											'col' => '12',
											'fields' => array(
												array(
													'id' => "{$meta_prefix}enable_jobs_package_expires",
													'type' => 'button_set',
													'title' => esc_html__('Enable package expires', 'jobportal-framework'),
													'desc' => esc_html__('Turn on when you want package to expire', 'jobportal-framework'),
													'options' => array(
														'1' => esc_html__('On', 'jobportal-framework'),
														'0' => esc_html__('Off', 'jobportal-framework'),
													),
													'col' => '4',
													'default' => '0'
												),

												array(
													'id' => "{$meta_prefix}enable_jobs_expires",
													'type' => 'button_set',
													'title' => esc_html__('Enable jobs expires', 'jobportal-framework'),
													'desc' => esc_html__('Turn on when you want jobs to expire', 'jobportal-framework'),
													'options' => array(
														'1' => esc_html__('On', 'jobportal-framework'),
														'0' => esc_html__('Off', 'jobportal-framework'),
													),
													'col' => '4',
													'default' => '0'
												),
												array(
													'id' => "{$meta_prefix}jobs_days_closing",
													'title' => esc_html__('Number of days to apply', 'jobportal-framework'),
													'desc' => esc_html__('Enter the number of days to apply for jobs', 'jobportal-framework'),
													'default' => '',
													'type' => 'text',
													'col' => '4',
													'required' => array(
														array("{$meta_prefix}enable_jobs_expires", '=', '0'),
													),
												),
												array(
													'id' => "{$meta_prefix}jobs_featured",
													'title' => esc_html__('Mark this jobs as featured ?', 'jobportal-framework'),
													'type' => 'button_set',
													'col' => '4',
													'options' => array(
														'1' => esc_html__('Yes', 'jobportal-framework'),
														'0' => esc_html__('No', 'jobportal-framework'),
													),
													'default' => '0',
												),
												array(
													'id' => "{$meta_prefix}jobs_quantity",
													'title' => esc_html__('Quantity to be recruited ', 'jobportal-framework'),
													'type' => 'select',
													'desc' => esc_html__('Select quantity', 'jobportal-framework'),
													'options' => array(
														'' => 'None',
														'1' => '1',
														'2' => '2',
														'3' => '3',
														'4' => '4',
														'5' => '5',
														'6' => '6',
														'7' => '7',
														'8' => '8',
														'9' => '9',
														'10' => '10+',
													),
													'col' => '4',
													'default' => 'quantity1',
												),
											)
										),
									)
								),
							),
							array(
								array(
									'id' => "{$meta_prefix}details_salary",
									'title' => esc_html__('Salary', 'jobportal-framework'),
									'icon' => 'dashicons dashicons-money-alt',
									'fields' => array(
										array(
											'type' => 'row',
											'col' => '12',
											'fields' => array(
												array(
													'id' => "{$meta_prefix}jobs_salary_show",
													'title' => esc_html__('Show pay by', 'jobportal-framework'),
													'type' => 'select',
													'options' => array(
														'range' => 'Range',
														'starting_amount' => 'Starting amount',
														'maximum_amount' => 'Maximum amount',
														'agree' => 'Negotiable Price',
													),
													'col' => '4',
													'default' => 'range',
												),
												array(
													'id' => "{$meta_prefix}jobs_salary_rate",
													'title' => esc_html__('Rate', 'jobportal-framework'),
													'type' => 'select',
													'options' => array(
														'' => esc_html__('None', 'jobportal-framework'),
														'hour' => esc_html__('Per Hour', 'jobportal-framework'),
														'day' => esc_html__('Per Day', 'jobportal-framework'),
														'week' => esc_html__('Per Week', 'jobportal-framework'),
														'month' => esc_html__('Per Month', 'jobportal-framework'),
														'year' => esc_html__('Per Year', 'jobportal-framework'),
													),
													'col' => '4',
													'default' => 'hour',
													'required' => array(
														array("{$meta_prefix}jobs_salary_show", '!=', 'agree')
													),
												),
												array(
													'id' => "{$meta_prefix}jobs_currency_type",
													'title' => esc_html__('Currency Type', 'jobportal-framework'),
													'type' => 'select',
													'options' => jobportal_get_select_currency_type(),
													'col' => '4',
												),
												array(
													'id' => "{$meta_prefix}jobs_salary_minimum",
													'title' => esc_html__('Minimum', 'jobportal-framework'),
													'desc' => esc_html__('Example Value: 450', 'jobportal-framework'),
													'type' => 'text',
													'pattern' => "{$format_number}",
													'default' => '450',
													'col' => '4',
													'required' => array(
														array("{$meta_prefix}jobs_salary_show", '=', 'range')
													),
												),
												array(
													'id' => "{$meta_prefix}jobs_salary_maximum",
													'title' => esc_html__('Maximum', 'jobportal-framework'),
													'desc' => esc_html__('Example Value: 900', 'jobportal-framework'),
													'type' => 'text',
													'pattern' => "{$format_number}",
													'default' => '900',
													'col' => '4',
													'required' => array(
														array("{$meta_prefix}jobs_salary_show", '=', 'range')
													),
												),
												array(
													'id' => "{$meta_prefix}jobs_maximum_price",
													'title' => esc_html__('Maximum Price', 'jobportal-framework'),
													'desc' => esc_html__('Example Value: 1000', 'jobportal-framework'),
													'type' => 'text',
													'pattern' => "{$format_number}",
													'default' => '',
													'col' => '4',
													'required' => array(
														array("{$meta_prefix}jobs_salary_show", '=', 'maximum_amount')
													),
												),
												array(
													'id' => "{$meta_prefix}jobs_minimum_price",
													'title' => esc_html__('Minimum Price', 'jobportal-framework'),
													'desc' => esc_html__('Example Value: 100', 'jobportal-framework'),
													'type' => 'text',
													'pattern' => "{$format_number}",
													'default' => '',
													'col' => '4',
													'required' => array(
														array("{$meta_prefix}jobs_salary_show", '=', 'starting_amount')
													),
												),
												array(
													'id' => "{$meta_prefix}jobs_salary_convert_min",
													'title' => esc_html__('Convert Min', 'jobportal-framework'),
													'type' => 'text',
													'col' => '4',
												),
												array(
													'id' => "{$meta_prefix}jobs_salary_convert_max",
													'title' => esc_html__('Convert Max', 'jobportal-framework'),
													'type' => 'text',
													'col' => '4',
												),
												array(
													'id' => "{$meta_prefix}jobs_price_convert_min",
													'title' => esc_html__('Convert Min', 'jobportal-framework'),
													'type' => 'text',
													'col' => '4',
												),
												array(
													'id' => "{$meta_prefix}jobs_price_convert_max",
													'title' => esc_html__('Convert Max', 'jobportal-framework'),
													'type' => 'text',
													'col' => '4',
												),
											)
										),
									)
								)
							),
							array(
								array(
									'id' => "{$meta_prefix}jobs_apply",
									'title' => esc_html__('Apply', 'jobportal-framework'),
									'icon' => 'dashicons-email',
									'fields' => array(
										array(
											'type' => 'row',
											'col' => '12',
											'fields' => array_merge(
												array(
													array(
														'id' => "{$meta_prefix}jobs_select_apply",
														'title' => esc_html__('Select Type', 'jobportal-framework'),
														'type' => 'select',
														'col' => 6,
														'options' => apply_filters(
															'jobportal_fields_select_apply_jobs',
															array(
																'email' => esc_html__('By Email', 'jobportal-framework'),
																'external' => esc_html__('External Apply', 'jobportal-framework'),
																'internal' => esc_html__('Internal Apply', 'jobportal-framework'),
																'call-to' => esc_html__('Call To Apply', 'jobportal-framework'),
															)
														)
													),
													array(
														'id' => "{$meta_prefix}jobs_apply_email",
														'title' => esc_html__('Job apply email', 'jobportal-framework'),
														'type' => 'text',
														'col' => 6,
														'required' => array(
															array("{$meta_prefix}jobs_select_apply", '=', 'email'),
														),
													),
													array(
														'id' => "{$meta_prefix}jobs_apply_external",
														'title' => esc_html__('Job apply external', 'jobportal-framework'),
														'type' => 'text',
														'col' => 6,
														'required' => array(
															array("{$meta_prefix}jobs_select_apply", '=', 'external'),
														),
													),
													array(
														'id' => "{$meta_prefix}jobs_apply_call_to",
														'title' => esc_html__('Job Call To Apply', 'jobportal-framework'),
														'type' => 'text',
														'col' => 6,
														'required' => array(
															array("{$meta_prefix}jobs_select_apply", '=', 'call-to'),
														),
													),
												),
												apply_filters('jobportal_fields_jobs_apply', array())
											)
										)
									)
								)
							),
							array(
								array(
									'id' => "{$meta_prefix}jobs_company",
									'title' => esc_html__('Company', 'jobportal-framework'),
									'icon' => 'dashicons dashicons-building',
									'fields' => array(
										array(
											'id' => "{$meta_prefix}jobs_select_company",
											'title' => esc_html__('Select company', 'jobportal-framework'),
											'type' => 'select',
											'options' => jobportal_select_post_company(),
										),
									)
								)
							),
							array(
								array(
									'id' => "{$meta_prefix}location_tab",
									'title' => esc_html__('Location', 'jobportal-framework'),
									'icon' => 'dashicons-location-alt',
									'fields' => array(
										array(
											'type' => 'row',
											'col' => '12',
											'fields' => array(
												array(
													'id' => "{$meta_prefix}jobs_address",
													'title' => esc_html__('Marker position on map', 'jobportal-framework'),
													'desc' => esc_html__('Address Details', 'jobportal-framework'),
													'type' => 'text',
													'col' => 4
												),
												array(
													'id' => "{$meta_prefix}jobs_latitude",
													'title' => esc_html__('Latitude', 'jobportal-framework'),
													'desc' => esc_html__('Latitude Details', 'jobportal-framework'),
													'type' => 'text',
													'col' => 4
												),
												array(
													'id' => "{$meta_prefix}jobs_longtitude",
													'title' => esc_html__('Longtitude', 'jobportal-framework'),
													'desc' => esc_html__('Longtitude Details', 'jobportal-framework'),
													'type' => 'text',
													'col' => 4
												),
												array(
													'id' => "{$meta_prefix}jobs_location",
													'title' => esc_html__('Jobs Location at Google Map', 'jobportal-framework'),
													'desc' => esc_html__('Drag the google map marker to point your jobs location. You can also use the address field above to search for your jobs', 'jobportal-framework'),
													'type' => 'map',
													'address_field' => "{$meta_prefix}jobs_address",
												),
											)
										)
									)
								),
								array(
									'id' => "{$meta_prefix}gallery_tab",
									'title' => esc_html__('Gallery Images', 'jobportal-framework'),
									'icon' => 'dashicons-format-gallery',
									'fields' => array(
										array(
											'id' => "{$meta_prefix}jobs_images",
											'title' => esc_html__('Gallery Images', 'jobportal-framework'),
											'type' => 'gallery',
										),
									)
								),
								array(
									'id' => "{$meta_prefix}video_tab",
									'title' => esc_html__('Video', 'jobportal-framework'),
									'icon' => 'dashicons-video-alt3',
									'fields' => array(
										array(
											'id' => "{$meta_prefix}jobs_video_url",
											'title' => esc_html__('Video URL', 'jobportal-framework'),
											'desc' => esc_html__('Input only URL. YouTube, Vimeo, SWF File and MOV File', 'jobportal-framework'),
											'type' => 'text',
											'col' => 12,
										),
										array(
											'id' => "{$meta_prefix}jobs_video_image",
											'title' => esc_html__('Video Image', 'jobportal-framework'),
											'type' => 'gallery',
											'col' => 12,
										),
									)
								),
							),
							$custom_field_jobs
						)
					),
					apply_filters('jobportal_register_meta_boxes_jobs_bottom', array())
				),
			));
			$configs['company_meta_boxes'] = apply_filters('jobportal_register_meta_boxes_company', array(
				'name' => esc_html__('Company Information', 'jobportal-framework'),
				'post_type' => array('company'),
				'section' => array_merge(
					apply_filters('jobportal_register_meta_boxes_company_top', array()),
					apply_filters(
						'jobportal_register_meta_boxes_company_main',
						array_merge(
							array(
								array(
									'id' => "{$meta_prefix}details_company_tab",
									'title' => esc_html__('Basic Infomation', 'jobportal-framework'),
									'icon' => 'dashicons-admin-home',
									'fields' => array(
										array(
											'type' => 'row',
											'col' => '12',
											'fields' => array(
												array(
													'id' => "{$meta_prefix}company_green_tick",
													'type' => 'button_set',
													'title' => esc_html__('Enable Green Tick', 'jobportal-framework'),
													'subtitle' => esc_html__('Enable/Disable Green Tick', 'jobportal-framework'),
													'options' => array(
														'1' => esc_html__('On', 'jobportal-framework'),
														'0' => esc_html__('Off', 'jobportal-framework'),
													),
													'default' => '0',
												),
												array(
													'id' => "{$meta_prefix}company_website",
													'title' => esc_html__(' Website ', 'jobportal-framework'),
													'type' => 'text',
													'col' => '6',
												),
												array(
													'id' => "{$meta_prefix}company_phone",
													'title' => esc_html__('Phone number', 'jobportal-framework'),
													'type' => 'text',
													'col' => '6',
												),
												array(
													'id' => "{$meta_prefix}company_email",
													'title' => esc_html__('Email', 'jobportal-framework'),
													'type' => 'text',
													'col' => '6',
												),
												array(
													'id' => "{$meta_prefix}company_founded",
													'title' => esc_html__('Founded In', 'jobportal-framework'),
													'type' => 'select',
													'options' => jobportal_get_company_founded(false),
													'col' => '6',
												),
											)
										),
									)
								),
								array(
									'id' => "{$meta_prefix}details_company_social",
									'title' => esc_html__('Social Network', 'jobportal-framework'),
									'icon' => 'dashicons dashicons-networking',
									'fields' => array(
										array(
											'type' => 'row',
											'fields' => array(
												array(
													'id' => "{$meta_prefix}company_twitter",
													'title' => esc_html__('Twitter', 'jobportal-framework'),
													'type' => 'text',
													'col' => '6',
												),
												array(
													'id' => "{$meta_prefix}company_linkedin",
													'title' => esc_html__('Linkedin', 'jobportal-framework'),
													'type' => 'text',
													'col' => '6',
												),

												array(
													'id' => "{$meta_prefix}company_facebook",
													'title' => esc_html__('Facebook', 'jobportal-framework'),
													'type' => 'text',
													'col' => '6',
												),
												array(
													'id' => "{$meta_prefix}company_instagram",
													'title' => esc_html__('Instagram', 'jobportal-framework'),
													'type' => 'text',
													'col' => '6',
												),
											)
										),
										array(
											'type' => 'divide'
										),
										array(
											'id' => "{$meta_prefix}company_social_tabs",
											'type' => 'panel',
											'title' => esc_html__('Social Network', 'jobportal-framework'),
											'sort' => true,
											'panel_title' => 'label',
											'fields' => array(
												array(
													'type' => 'row',
													'col' => '12',
													'fields' => array(
														array(
															'id' => "{$meta_prefix}company_social_name",
															'type' => 'select',
															'options' => jobportal_get_repeater_social(''),
															'col' => '6',
															'title' => esc_html__('Name', 'jobportal-framework'),
														),
														array(
															'id' => "{$meta_prefix}company_social_url",
															'type' => 'text',
															'col' => '6',
															'title' => esc_html__('Url', 'jobportal-framework'),
														),
													)
												)
											)
										),
									)
								),
								array(
									'id' => "{$meta_prefix}company_logo_tab",
									'title' => esc_html__('Logo', 'jobportal-framework'),
									'icon' => 'dashicons dashicons-format-image',
									'fields' => array(
										array(
											'type' => 'row',
											'col' => '12',
											'fields' => array(
												array(
													'id' => "{$meta_prefix}company_logo",
													'title' => esc_html__('Logo', 'jobportal-framework'),
													'type' => 'image',
												),
											)
										),
									)
								),
								array(
									'id' => "{$meta_prefix}location_tab",
									'title' => esc_html__('Location', 'jobportal-framework'),
									'icon' => 'dashicons-location-alt',
									'fields' => array(
										array(
											'type' => 'row',
											'col' => '12',
											'fields' => array(
												array(
													'id' => "{$meta_prefix}company_address",
													'title' => esc_html__('Marker position on map', 'jobportal-framework'),
													'desc' => esc_html__('Full Address', 'jobportal-framework'),
													'type' => 'text',
													'col' => 4
												),
												array(
													'id' => "{$meta_prefix}company_latitude",
													'title' => esc_html__('Latitude', 'jobportal-framework'),
													'desc' => esc_html__('Latitude Details', 'jobportal-framework'),
													'type' => 'text',
													'col' => 4
												),
												array(
													'id' => "{$meta_prefix}company_longtitude",
													'title' => esc_html__('Longtitude', 'jobportal-framework'),
													'desc' => esc_html__('Longtitude Details', 'jobportal-framework'),
													'type' => 'text',
													'col' => 4
												),
												array(
													'id' => "{$meta_prefix}company_location",
													'title' => esc_html__('Company Location at Google Map', 'jobportal-framework'),
													'desc' => esc_html__('Drag the google map marker to point your company location. You can also use the address field above to search for your company', 'jobportal-framework'),
													'type' => 'map',
													'address_field' => "{$meta_prefix}company_address",
												),
											)
										)
									)
								),
								array(
									'id' => "{$meta_prefix}gallery_company_tab",
									'title' => esc_html__('Gallery Images', 'jobportal-framework'),
									'icon' => 'dashicons-format-gallery',
									'fields' => array(
										array(
											'id' => "{$meta_prefix}company_images",
											'title' => esc_html__('JobPortal Gallery Images', 'jobportal-framework'),
											'type' => 'gallery',
										),
									)
								),
								array(
									'id' => "{$meta_prefix}video_company_tab",
									'title' => esc_html__('Video', 'jobportal-framework'),
									'icon' => 'dashicons-video-alt3',
									'fields' => array(
										array(
											'id' => "{$meta_prefix}company_video_url",
											'title' => esc_html__('Video URL', 'jobportal-framework'),
											'desc' => esc_html__('Input only URL. YouTube, Vimeo, SWF File and MOV File', 'jobportal-framework'),
											'type' => 'text',
											'col' => 12,
										),
										array(
											'id' => "{$meta_prefix}company_video_image",
											'title' => esc_html__('Video Image', 'jobportal-framework'),
											'type' => 'gallery',
											'col' => 12,
										),
									)
								),
								array(
									'id' => "{$meta_prefix}custom_field_company_tab",
									'title' => esc_html__('Additional Fields', 'jobportal-framework'),
									'icon' => 'dashicons dashicons-welcome-add-page',
									'fields' => $render_custom_field_company,
								)
							)
						)
					),
					apply_filters('jobportal_register_meta_boxes_company_bottom', array())
				),
			));

			$configs['candidate_meta_boxes'] = apply_filters('jobportal_register_meta_boxes_candidate', array(
				'name' => esc_html__('Candidate Information', 'jobportal-framework'),
				'post_type' => array('candidate'),
				'section' => array_merge(
					apply_filters('jobi_register_meta_boxes_candidate_top', array()),
					apply_filters(
						'jobi_register_meta_boxes_candidate_main',
						array_merge(
							array(
								array(
									'id' => "{$meta_prefix}details_tab",
									'title' => esc_html__('Basic Infomation', 'jobportal-framework'),
									'icon' => 'dashicons-admin-home',
									'fields' => array(
										array(
											'type' => 'row',
											'col' => '12',
											'fields' => array(
												array(
													'id' => "{$meta_prefix}candidate_first_name",
													'title' => esc_html__('First Name', 'jobportal-framework'),
													'type' => 'text',
													'default' => '',
													'col' => '6',
												),
												array(
													'id' => "{$meta_prefix}candidate_last_name",
													'title' => esc_html__('Last Name', 'jobportal-framework'),
													'type' => 'text',
													'default' => '',
													'col' => '6',
												),
												array(
													'id' => "{$meta_prefix}candidate_email",
													'title' => esc_html__('Email Address', 'jobportal-framework'),
													'type' => 'text',
													'default' => '',
													'col' => '6',
												),
												array(
													'id' => "{$meta_prefix}candidate_phone",
													'title' => esc_html__('Phone Number', 'jobportal-framework'),
													'type' => 'text',
													'default' => '',
													'col' => '6',
												),
												array(
													'id' => "{$meta_prefix}candidate_current_position",
													'title' => esc_html__('Current Position', 'jobportal-framework'),
													'type' => 'text',
													'default' => '',
													'col' => '6',
												),
												array(
													'id' => "{$meta_prefix}candidate_dob",
													'title' => esc_html__('Date Of Birth', 'jobportal-framework'),
													'type' => 'text',
													'default' => '',
													'pattern' => '(?:19|20)[0-9]{2}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-9])|(?:(?!02)(?:0[1-9]|1[0-2])-(?:30))|(?:(?:0[13578]|1[02])-31))',
													'placeholder' => 'yyyy-mm-dd',
													'maxlength' => '10',
													'col' => '6',
												),
											)
										),


										array(
											'type' => 'divide'
										),

										array(
											'type' => 'row',
											'fields' => array(
												array(
													'id' => "{$meta_prefix}candidate_offer_salary",
													'title' => esc_html__('Offered Salary', 'jobportal-framework'),
													'type' => 'text',
													'pattern' => '^[0-9]+([.][0-9]+)?$',
													'default' => '',
													'col' => '4',
												),
												array(
													'id' => "{$meta_prefix}candidate_salary_type",
													'title' => esc_html__('Salary Type', 'jobportal-framework'),
													'type' => 'select',
													'options' => array(
														'' => esc_html__('None', 'jobportal-framework'),
														'hr' => esc_html__('Hourly', 'jobportal-framework'),
														'day' => esc_html__('Daily', 'jobportal-framework'),
														'month' => esc_html__('Monthly', 'jobportal-framework'),
														'year' => esc_html__('Yearly', 'jobportal-framework'),
													),
													'col' => '4',
													'default' => 'hr',
												),
												array(
													'id' => "{$meta_prefix}candidate_currency_type",
													'title' => esc_html__('Currency Type', 'jobportal-framework'),
													'type' => 'select',
													'options' => jobportal_get_select_currency_type(),
													'col' => '4',
												),
												array(
													'id' => "{$meta_prefix}candidate_featured",
													'title' => esc_html__('Mark this candidate as featured ?', 'jobportal-framework'),
													'type' => 'button_set',
													'options' => array(
														'1' => esc_html__('Yes', 'jobportal-framework'),
														'0' => esc_html__('No', 'jobportal-framework'),
													),
													'default' => '0',
												),
												apply_filters('jobportal_register_meta_boxes_candidate_center', array()),
												apply_filters('jobportal_register_meta_boxes_candidate_bottom', array())
											)
										)
									)
								)
							),

							array(
								array(
									'id' => "{$meta_prefix}details_candidate_social",
									'title' => esc_html__('Social Network', 'jobportal-framework'),
									'icon' => 'dashicons dashicons-networking',
									'fields' => array(
										array(
											'type' => 'row',
											'fields' => array(
												array(
													'id' => "{$meta_prefix}candidate_twitter",
													'title' => esc_html__('Twitter', 'jobportal-framework'),
													'type' => 'text',
													'col' => '6',
												),
												array(
													'id' => "{$meta_prefix}candidate_linkedin",
													'title' => esc_html__('Linkedin', 'jobportal-framework'),
													'type' => 'text',
													'col' => '6',
												),

												array(
													'id' => "{$meta_prefix}candidate_facebook",
													'title' => esc_html__('Facebook', 'jobportal-framework'),
													'type' => 'text',
													'col' => '6',
												),
												array(
													'id' => "{$meta_prefix}candidate_instagram",
													'title' => esc_html__('Instagram', 'jobportal-framework'),
													'type' => 'text',
													'col' => '6',
												),

												$candidate_custom_social0,
												$candidate_custom_social1,
												$candidate_custom_social2,
												$candidate_custom_social3,
											)
										),
										array(
											'type' => 'divide'
										),
										array(
											'id' => "{$meta_prefix}candidate_social_tabs",
											'type' => 'panel',
											'title' => esc_html__('Social Network', 'jobportal-framework'),
											'sort' => true,
											'panel_title' => 'label',
											'fields' => array(
												array(
													'type' => 'row',
													'col' => '12',
													'fields' => array(
														array(
															'id' => "{$meta_prefix}candidate_social_name",
															'type' => 'select',
															'options' => jobportal_get_repeater_social(''),
															'col' => '6',
															'title' => esc_html__('Name', 'jobportal-framework'),
														),
														array(
															'id' => "{$meta_prefix}candidate_social_url",
															'type' => 'text',
															'col' => '6',
															'title' => esc_html__('Url', 'jobportal-framework'),
														),
													)
												)
											)
										),
									)
								)
							),
							array(
								array(
									'id' => "{$meta_prefix}candidate_resume_tab",
									'title' => esc_html__('My Resume', 'jobportal-framework'),
									'icon' => 'dashicons-format-gallery',
									'fields' => array(
										array(
											'id' => "{$meta_prefix}candidate_resume_id_list",
											'title' => esc_html__('Candidate Resume', 'jobportal-framework'),
											'type' => 'file',
										),
									)
								),
								array(
									'id' => "{$meta_prefix}candidate_location_tab",
									'title' => esc_html__('Location', 'jobportal-framework'),
									'icon' => 'dashicons-location-alt',
									'fields' => array(
										array(
											'type' => 'row',
											'col' => '12',
											'fields' => array(
												array(
													'id' => "{$meta_prefix}candidate_address",
													'title' => esc_html__('Marker position on map', 'jobportal-framework'),
													'desc' => esc_html__('Address Details', 'jobportal-framework'),
													'type' => 'text',
													'col' => 4
												),
												array(
													'id' => "{$meta_prefix}candidate_latitude",
													'title' => esc_html__('Latitude', 'jobportal-framework'),
													'desc' => esc_html__('Latitude Details', 'jobportal-framework'),
													'type' => 'text',
													'col' => 4
												),
												array(
													'id' => "{$meta_prefix}candidate_longtitude",
													'title' => esc_html__('Longitude', 'jobportal-framework'),
													'desc' => esc_html__('Longitude Details', 'jobportal-framework'),
													'type' => 'text',
													'col' => 4
												),
												array(
													'id' => "{$meta_prefix}candidate_location",
													'title' => esc_html__('Location at Google Map', 'jobportal-framework'),
													'desc' => esc_html__('Drag the google map marker to point your candidate location. You can also use the address field above to search for your candidate', 'jobportal-framework'),
													'type' => 'map',
													'address_field' => "{$meta_prefix}candidate_address",
												),
											)
										)

									)
								),
								array(
									'id' => "{$meta_prefix}candidate_education_tabs",
									'title' => esc_html__('Education', 'jobportal-framework'),
									'icon' => 'dashicons-editor-ul',
									'fields' => array(
										array(
											'id' => "{$meta_prefix}candidate_education_list",
											'type' => 'panel',
											'title' => esc_html__('Education', 'jobportal-framework'),
											'sort' => true,
											'panel_title' => 'label',
											'fields' => array(
												array(
													'type' => 'row',
													'col' => '12',
													'fields' => array(
														array(
															'id' => "{$meta_prefix}candidate_education_title",
															'title' => esc_html__('Title', 'jobportal-framework'),
															'type' => 'text',
															'default' => '',
															'col' => '6'
														),
														array(
															'id' => "{$meta_prefix}candidate_education_level",
															'title' => esc_html__('Level of Education', 'jobportal-framework'),
															'type' => 'text',
															'default' => '',
															'col' => '6'
														),
														array(
															'id' => "{$meta_prefix}candidate_education_from",
															'title' => esc_html__('From', 'jobportal-framework'),
															'type' => 'text',
															'default' => '',
															'col' => '6'
														),
														array(
															'id' => "{$meta_prefix}candidate_education_to",
															'title' => esc_html__('To', 'jobportal-framework'),
															'type' => 'text',
															'default' => '',
															'col' => '6'
														),
														array(
															'id' => "{$meta_prefix}candidate_education_description",
															'title' => esc_html__('Description', 'jobportal-framework'),
															'type' => 'textarea',
															'default' => '',
															'col' => '12'
														),
													)
												)
											)
										)
									)
								),
								array(
									'id' => "{$meta_prefix}candidate_experience_tab",
									'title' => esc_html__('Experiencies', 'jobportal-framework'),
									'icon' => 'dashicons-location-alt',
									'fields' => array(
										array(
											'id' => "{$meta_prefix}candidate_experience_list",
											'type' => 'panel',
											'title' => esc_html__('Work Experience', 'jobportal-framework'),
											'sort' => true,
											'panel_title' => 'label',
											'fields' => array(
												array(
													'type' => 'row',
													'col' => '12',
													'fields' => array(
														array(
															'id' => "{$meta_prefix}candidate_experience_job",
															'title' => esc_html__('Job Title', 'jobportal-framework'),
															'type' => 'text',
															'default' => '',
															'col' => '6'
														),
														array(
															'id' => "{$meta_prefix}candidate_experience_company",
															'title' => esc_html__('Company', 'jobportal-framework'),
															'type' => 'text',
															'default' => '',
															'col' => '6'
														),
														array(
															'id' => "{$meta_prefix}candidate_experience_from",
															'title' => esc_html__('From', 'jobportal-framework'),
															'type' => 'text',
															'default' => '',
															'col' => '6'
														),
														array(
															'id' => "{$meta_prefix}candidate_experience_to",
															'title' => esc_html__('To', 'jobportal-framework'),
															'type' => 'text',
															'default' => '',
															'col' => '6'
														),
														array(
															'id' => "{$meta_prefix}candidate_experience_description",
															'title' => esc_html__('Description', 'jobportal-framework'),
															'type' => 'textarea',
															'default' => '',
															'col' => '12'
														),
													)
												)
											)
										)
									)
								),
								array(
									'id' => "{$meta_prefix}candidate_project_tab",
									'title' => esc_html__('Projects', 'jobportal-framework'),
									'icon' => 'dashicons-format-gallery',
									'fields' => array(
										array(
											'id' => "{$meta_prefix}candidate_project_list",
											'type' => 'panel',
											'title' => esc_html__('Projects', 'jobportal-framework'),
											'sort' => true,
											'panel_title' => 'label',
											'fields' => array(
												array(
													'type' => 'row',
													'col' => '12',
													'fields' => array(
														array(
															'id' => "{$meta_prefix}candidate_project_image_id",
															'title' => esc_html__('A screenshot of Project', 'jobportal-framework'),
															'type' => 'image',
															'default' => '',
															'col' => '12'
														),
														array(
															'id' => "{$meta_prefix}candidate_project_title",
															'title' => esc_html__('Project Title', 'jobportal-framework'),
															'type' => 'text',
															'default' => '',
															'col' => '6'
														),
														array(
															'id' => "{$meta_prefix}candidate_project_link",
															'title' => esc_html__('Link', 'jobportal-framework'),
															'type' => 'text',
															'default' => '',
															'col' => '6'
														),
														array(
															'id' => "{$meta_prefix}candidate_project_description",
															'title' => esc_html__('Description', 'jobportal-framework'),
															'type' => 'textarea',
															'default' => '',
															'col' => '12'
														),
													)
												)
											)
										)
									)
								),
								array(
									'id' => "{$meta_prefix}candidate_award_tab",
									'title' => esc_html__('Awards', 'jobportal-framework'),
									'icon' => 'dashicons-video-alt3',
									'fields' => array(
										array(
											'id' => "{$meta_prefix}candidate_award_list",
											'type' => 'panel',
											'title' => esc_html__('Awards', 'jobportal-framework'),
											'sort' => true,
											'panel_title' => 'label',
											'fields' => array(
												array(
													'type' => 'row',
													'col' => '12',
													'fields' => array(
														array(
															'id' => "{$meta_prefix}candidate_award_title",
															'title' => esc_html__('Title', 'jobportal-framework'),
															'type' => 'text',
															'default' => '',
															'col' => '6'
														),
														array(
															'id' => "{$meta_prefix}candidate_award_date",
															'title' => esc_html__('Date Awarded', 'jobportal-framework'),
															'type' => 'text',
															'default' => '',
															'col' => '6'
														),
														array(
															'id' => "{$meta_prefix}candidate_award_description",
															'title' => esc_html__('Description', 'jobportal-framework'),
															'type' => 'textarea',
															'default' => '',
															'col' => '12'
														),
													)
												)
											)
										)
									)
								),
								array(
									'id' => "{$meta_prefix}candidate_video_gallery_tab",
									'title' => esc_html__('Video and Gallery', 'jobportal-framework'),
									'icon' => 'dashicons-video-alt3',
									'fields' => array(
										array(
											'id' => "{$meta_prefix}candidate_galleries",
											'title' => esc_html__('Gallery', 'jobportal-framework'),
											'type' => 'gallery',
											'default' => '',
											'col' => '12'
										),
										array(
											'id' => "{$meta_prefix}candidate_video_url",
											'title' => esc_html__('Video URL', 'jobportal-framework'),
											'type' => 'text',
											'default' => '',
											'col' => '6'
										),
									)
								),
							),
							$custom_field_candidate
						)
					),
					apply_filters('jobi_register_meta_boxes_candidate_bottom', array())
				),
			));

			$configs['package_meta_boxes'] = array(
				'name' => esc_html__('Package Settings', 'jobportal-framework'),
				'post_type' => array('package'),
				'fields' => array_merge(
					array(
						array(
							'type' => 'row',
							'col' => '4',
							'fields' => array(
								array(
									'id' => "{$meta_prefix}package_unlimited_job",
									'title' => esc_html__('Unlimited Job', 'jobportal-framework'),
									'type' => 'button_set',
									'options' => array(
										'1' => esc_html__('Yes', 'jobportal-framework'),
										'0' => esc_html__('No', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => "{$meta_prefix}package_number_job",
									'title' => esc_html__('Number Listings', 'jobportal-framework'),
									'type' => 'text',
									'default' => '',
									'pattern' => '[0-9]*',
									'required' => array("{$meta_prefix}package_unlimited_job", '=', '0'),
								),
							)
						),
						array(
							'type' => 'row',
							'col' => '4',
							'fields' => array(
								array(
									'id' => "{$meta_prefix}package_unlimited_job_featured",
									'title' => esc_html__('Unlimited Featured Job', 'jobportal-framework'),
									'type' => 'button_set',
									'options' => array(
										'1' => esc_html__('Yes', 'jobportal-framework'),
										'0' => esc_html__('No', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => "{$meta_prefix}package_number_featured",
									'title' => esc_html__('Number Featured Listings', 'jobportal-framework'),
									'type' => 'text',
									'default' => '',
									'pattern' => '[0-9]*',
									'required' => array("{$meta_prefix}package_unlimited_job_featured", '=', '0'),
								),
							)
						),
						$company_package_candidate_follow,
						$company_package_download_cv_candidate,
						$result_package = apply_filters('jobportal_register_field_package_jobs', array()),
						array(
							'type' => 'row',
							'col' => '4',
							'fields' => array(
								$company_package_invite_candidate,
								$company_package_send_message_candidate,
								$company_package_print_candidate,
								$company_package_review_candidate,
								$company_package_info_candidate,
							)
						),
						array(
							'type' => 'divide'
						),
						array(
							'type' => 'row',
							'col' => '4',
							'fields' => array(
								array(
									'id' => "{$meta_prefix}package_free",
									'title' => esc_html__('Free package', 'jobportal-framework'),
									'type' => 'button_set',
									'options' => array(
										'1' => esc_html__('Yes', 'jobportal-framework'),
										'0' => esc_html__('No', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => "{$meta_prefix}package_price",
									'title' => esc_html__('Package Price', 'jobportal-framework'),
									'type' => 'text',
									'required' => array("{$meta_prefix}package_free", '=', '0'),
								),
							)
						),
						array(
							'type' => 'row',
							'col' => '4',
							'fields' => array(
								array(
									'id' => "{$meta_prefix}package_unlimited_time",
									'title' => esc_html__('Unlimited time', 'jobportal-framework'),
									'type' => 'button_set',
									'options' => array(
										'1' => esc_html__('Yes', 'jobportal-framework'),
										'0' => esc_html__('No', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => "{$meta_prefix}package_time_unit",
									'title' => esc_html__('Time Unit', 'jobportal-framework'),
									'type' => 'button_set',
									'options' => array(
										'Day' => esc_html__('Day', 'jobportal-framework'),
										'Week' => esc_html__('Week', 'jobportal-framework'),
										'Month' => esc_html__('Month', 'jobportal-framework'),
										'Year' => esc_html__('Year', 'jobportal-framework'),
									),
									'default' => 'Day',
									'required' => array("{$meta_prefix}package_unlimited_time", '=', '0'),
								),
								array(
									'id' => "{$meta_prefix}package_period",
									'title' => esc_html__('Number Time', 'jobportal-framework'),
									'type' => 'text',
									'default' => '1',
									'pattern' => '[0-9]*',
									'required' => array("{$meta_prefix}package_unlimited_time", '=', '0'),
								),
							)
						),
						array(
							'type' => 'row',
							'col' => '4',
							'fields' => array(
								array(
									'id' => "{$meta_prefix}package_order_display",
									'title' => esc_html__('Order Number Display Via Frontend', 'jobportal-framework'),
									'type' => 'text',
									'default' => '1',
									'pattern' => '[0-9]*',
								),
								array(
									'id' => "{$meta_prefix}package_featured",
									'title' => esc_html__('Is Featured?', 'jobportal-framework'),
									'type' => 'button_set',
									'options' => array(
										'1' => esc_html__('Yes', 'jobportal-framework'),
										'0' => esc_html__('No', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => "{$meta_prefix}package_visible",
									'title' => esc_html__('Is Visible?', 'jobportal-framework'),
									'type' => 'button_set',
									'options' => array(
										'1' => esc_html__('Yes', 'jobportal-framework'),
										'0' => esc_html__('No', 'jobportal-framework'),
									),
									'default' => '1',
								),
							)
						),
						array(
							'type' => 'divide'
						),
						array(
							'id' => "{$meta_prefix}package_additional_details",
							'type' => 'repeater',
							'title' => esc_html__('Additional details:', 'jobportal-framework'),
							'col' => '6',
							'sort' => true,
							'fields' => array(
								array(
									'id' => "{$meta_prefix}package_details_text",
									'type' => 'text',
									'default' => esc_html__('Limited support', 'jobportal-framework'),
								),
							)
						),
					),
					apply_filters('package_meta_boxes_candidate_bottom', array())
				),
			);
			$configs['candidate_package_meta_boxes'] = array(
				'name' => esc_html__('Candidate Package', 'jobportal-framework'),
				'post_type' => array('candidate_package'),
				'fields' => array(
					$candidate_package_service,
					$candidate_package_service_featured,
					$candidate_package_jobs_apply,
					$candidate_package_jobs_wishlist,
					$candidate_package_company_follow,
					array(
						'type' => 'row',
						'col' => '4',
						'fields' => array(
							$candidate_package_contact_company,
							$candidate_package_info_company,
							$candidate_package_send_message,
							$candidate_package_review_and_commnent,
							apply_filters('jobportal_register_field_package_info_candidate', array()),
						)
					),
					array(
						'type' => 'divide'
					),
					array(
						'type' => 'row',
						'col' => '4',
						'fields' => array(
							array(
								'id' => "{$meta_prefix}candidate_package_free",
								'title' => esc_html__('Free Package', 'jobportal-framework'),
								'type' => 'button_set',
								'options' => array(
									'1' => esc_html__('Yes', 'jobportal-framework'),
									'0' => esc_html__('No', 'jobportal-framework'),
								),
								'default' => '0',
							),
							array(
								'id' => "{$meta_prefix}candidate_package_price",
								'title' => esc_html__('Package Price', 'jobportal-framework'),
								'type' => 'text',
								'required' => array("{$meta_prefix}candidate_package_free", '=', '0'),
							),
						)
					),
					array(
						'type' => 'row',
						'col' => '4',
						'fields' => array(
							array(
								'id' => "{$meta_prefix}enable_package_service_unlimited_time",
								'title' => esc_html__('Unlimited time', 'jobportal-framework'),
								'type' => 'button_set',
								'options' => array(
									'1' => esc_html__('Yes', 'jobportal-framework'),
									'0' => esc_html__('No', 'jobportal-framework'),
								),
								'default' => '0',
							),
							array(
								'id' => "{$meta_prefix}candidate_package_time_unit",
								'title' => esc_html__('Time Unit', 'jobportal-framework'),
								'type' => 'button_set',
								'options' => array(
									'Day' => esc_html__('Day', 'jobportal-framework'),
									'Week' => esc_html__('Week', 'jobportal-framework'),
									'Month' => esc_html__('Month', 'jobportal-framework'),
									'Year' => esc_html__('Year', 'jobportal-framework'),
								),
								'default' => 'Day',
								'required' => array("{$meta_prefix}enable_package_service_unlimited_time", '=', '0'),
							),
							array(
								'id' => "{$meta_prefix}candidate_package_period",
								'title' => esc_html__('Number Time', 'jobportal-framework'),
								'type' => 'text',
								'default' => '1',
								'pattern' => '[0-9]*',
								'required' => array("{$meta_prefix}enable_package_service_unlimited_time", '=', '0'),
							),
						)
					),
					array(
						'type' => 'row',
						'col' => '4',
						'fields' => array(
							array(
								'id' => "{$meta_prefix}candidate_package_order_display",
								'title' => esc_html__('Order Number Display Via Frontend', 'jobportal-framework'),
								'type' => 'text',
								'default' => '1',
								'pattern' => '[0-9]*',
							),
							array(
								'id' => "{$meta_prefix}candidate_package_featured",
								'title' => esc_html__('Is Featured?', 'jobportal-framework'),
								'type' => 'button_set',
								'options' => array(
									'1' => esc_html__('Yes', 'jobportal-framework'),
									'0' => esc_html__('No', 'jobportal-framework'),
								),
								'default' => '0',
							),
							array(
								'id' => "{$meta_prefix}candidate_package_visible",
								'title' => esc_html__('Is Visible?', 'jobportal-framework'),
								'type' => 'button_set',
								'options' => array(
									'1' => esc_html__('Yes', 'jobportal-framework'),
									'0' => esc_html__('No', 'jobportal-framework'),
								),
								'default' => '1',
							),
						)
					),
					array(
						'type' => 'divide'
					),
					array(
						'id' => "{$meta_prefix}candidate_package_additional_details",
						'type' => 'repeater',
						'title' => esc_html__('Custom Field Package', 'jobportal-framework'),
						'col' => '6',
						'sort' => true,
						'fields' => array(
							array(
								'id' => "{$meta_prefix}candidate_package_details_text",
								'type' => 'text',
								'default' => esc_html__('Limited support', 'jobportal-framework'),
							),
						)
					),
				),
			);

			$configs['candidate_order_meta_boxes'] = array(
				'name' => esc_html__('Service Order Settings', 'jobportal-framework'),
				'post_type' => array('candidate_order'),
				'fields' => array(
					array(
						'type' => 'row',
						'col' => '12',
						'fields' => array(
							array(
								'id' => "{$meta_prefix}candidate_order_payment_status",
								'title' => esc_html__('Status', 'jobportal-framework'),
								'type' => 'button_set',
								'col' => '4',
								'options' => array(
									'0' => esc_html__('Pending', 'jobportal-framework'),
									'1' => esc_html__('Active', 'jobportal-framework'),
								),
								'default' => 'pending',
							),
						)
					),
					array(
						'type' => 'row',
						'col' => '12',
						'fields' => array(
							array(
								'id' => "{$meta_prefix}candidate_order_user_id",
								'title' => esc_html__('User Buyer id', 'jobportal-framework'),
								'default' => '',
								'type' => 'text',
								'col' => '4',
							),
							array(
								'id' => "{$meta_prefix}candidate_order_item_id",
								'title' => esc_html__('Package id', 'jobportal-framework'),
								'default' => '',
								'type' => 'text',
								'col' => '4',
							),
							array(
								'id' => "{$meta_prefix}candidate_order_price",
								'title' => esc_html__('Price', 'jobportal-framework'),
								'default' => '30',
								'type' => 'text',
								'col' => '4',
							),
							array(
								'id' => "{$meta_prefix}candidate_order_date",
								'title' => esc_html__('Activate Date', 'jobportal-framework'),
								'default' => '',
								'type' => 'text',
								'col' => '4',
							),
							array(
								'id' => "{$meta_prefix}candidate_order_payment_method",
								'title' => esc_html__('Payment Method', 'jobportal-framework'),
								'default' => '',
								'type' => 'text',
								'col' => '4',
							),
						)
					),
				),
			);

			$date_applicants = get_the_date(get_option('date_format'));
			$configs['applicants_meta_boxes'] = array(
				'name' => esc_html__('Applicants Settings', 'jobportal-framework'),
				'post_type' => array('applicants'),
				'fields' => array(
					array(
						'type' => 'row',
						'col' => '6',
						'fields' => array(
							array(
								'id' => "{$meta_prefix}applicants_status",
								'title' => esc_html__('Status', 'jobportal-framework'),
								'type' => 'button_set',
								'options' => array(
									'pending' => esc_html__('Pending', 'jobportal-framework'),
									'approved' => esc_html__('Approved', 'jobportal-framework'),
									'rejected' => esc_html__('Rejected', 'jobportal-framework'),
								),
								'default' => 'pending',
							),
							array(
								'id' => "{$meta_prefix}applicants_type",
								'title' => esc_html__('Type Apply', 'jobportal-framework'),
								'type' => 'button_set',
								'options' => array(
									'email' => esc_html__('Email', 'jobportal-framework'),
									'internal' => esc_html__('Internal', 'jobportal-framework'),
								),
								'default' => 'email',
							),
						)
					),
					array(
						'type' => 'row',
						'col' => '6',
						'fields' => array(
							array(
								'id' => "{$meta_prefix}applicants_author",
								'title' => esc_html__('Name Apply', 'jobportal-framework'),
								'type' => 'text',
								'default' => '',
								'required' => array("{$meta_prefix}applicants_type", '=', 'email'),
							),
							array(
								'id' => "{$meta_prefix}applicants_phone",
								'title' => esc_html__('Phone', 'jobportal-framework'),
								'type' => 'text',
								'default' => '',
								'required' => array("{$meta_prefix}applicants_type", '=', 'email'),
							),
							array(
								'id' => "{$meta_prefix}applicants_email",
								'title' => esc_html__('Email Address', 'jobportal-framework'),
								'type' => 'text',
								'default' => '',
								'required' => array("{$meta_prefix}applicants_type", '=', 'email'),
							),
							array(
								'id' => "{$meta_prefix}applicants_cv",
								'title' => esc_html__('Cv Url', 'jobportal-framework'),
								'type' => 'text',
								'default' => '',
							),
						)
					),
					array(
						'id' => "{$meta_prefix}applicants_message",
						'title' => esc_html__('Message', 'jobportal-framework'),
						'type' => 'textarea',
						'default' => '',
					),
					array(
						'type' => 'row',
						'col' => '12',
						'fields' => array(
							array(
								'id' => "{$meta_prefix}applicants_jobs_id",
								'title' => esc_html__('Jobs ID', 'jobportal-framework'),
								'type' => 'text',
								'col' => '6',
								'default' => '',
							),
							array(
								'id' => "{$meta_prefix}applicants_date",
								'title' => esc_html__('Post Date', 'jobportal-framework'),
								'type' => 'text',
								'col' => '6',
								'default' => $date_applicants,
							),
						)
					),
				),
			);

			$args = [
				'post_type'      => ['package', 'candidate_package'],
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			];

			$posts = get_posts($args);
			$package_coupon = [];

			foreach ($posts as $post) {
				// Convert post type to a readable format
				$formatted_type = ucwords(str_replace('_', ' ', $post->post_type));

				$package_coupon[$post->ID] = $post->post_title . ' (' . $formatted_type . ')';
			}

			$configs['package_coupon_meta_boxes'] = array(
				'name' => esc_html__('Package Coupon Settings', 'jobportal-framework'),
				'post_type' => array('package_coupon'),
				'fields' => array(
					array(
						'type' => 'row',
						'col' => '6',
						'fields' => array(
							array(
								'id' => "{$meta_prefix}coupon_type",
								'title' => esc_html__('Coupon Type', 'jobportal-framework'),
								'type' => 'button_set',
								'options' => array(
									'fixed' => esc_html__('Fixed', 'jobportal-framework'),
									'percentage' => esc_html__('Percentage', 'jobportal-framework'),
								),
								'default' => 'fixed',
							),
							array(
								'id' => "{$meta_prefix}amount",
								'title' => esc_html__('Amount', 'jobportal-framework'),
								'type' => 'text',
								'default' => '',
							),
						)
					),
					array(
						'type' => 'row',
						'col' => '6',
						'fields' => array(
							array(
								'id' => "{$meta_prefix}coupon_limit",
								'title' => esc_html__('Coupon Limit', 'jobportal-framework'),
								'type' => 'button_set',
								'options' => array(
									'uses' => esc_html__('Number of uses', 'jobportal-framework'),
									'date' => esc_html__('Date', 'jobportal-framework'),
								),
								'default' => 'uses',
							),
							array(
								'id' => "{$meta_prefix}coupon_uses",
								'title' => esc_html__('Number of uses', 'jobportal-framework'),
								'type' => 'text',
								'input_type' => 'number',
								'default' => '50',
								'required' => array("{$meta_prefix}coupon_limit", '=', 'uses'),
								'desc' => esc_html__('Enter the number of uses for the discount code.', 'jobportal-framework'),
							),
							array(
								'id' => "{$meta_prefix}coupon_date",
								'title' => esc_html__('Date', 'jobportal-framework'),
								'type' => 'text',
								'input_type' => 'date',
								'default' => '',
								'required' => array("{$meta_prefix}coupon_limit", '=', 'date'),
								'desc' => esc_html__('Enter the expiration date of the discount code.', 'jobportal-framework'),
							),
						)
					),
					array(
						'id' => "{$meta_prefix}package_selected",
						'title' => esc_html__('Package', 'jobportal-framework'),
						'type' => 'checkbox_list',
						'options' => $package_coupon,
						'default' => '',
					),
				),
			);

			$configs['meetings_meta_boxes'] = array(
				'name' => esc_html__('Meetings Settings', 'jobportal-framework'),
				'post_type' => array('meetings'),
				'fields' => array(
					array(
						'id' => "{$meta_prefix}meeting_status",
						'title' => esc_html__('Status', 'jobportal-framework'),
						'type' => 'button_set',
						'options' => array(
							'upcoming' => esc_html__('Upcoming', 'jobportal-framework'),
							'completed' => esc_html__('Completed', 'jobportal-framework'),
						),
						'default' => 'upcoming',
					),
					array(
						'type' => 'row',
						'col' => '6',
						'fields' => array(
							array(
								'id' => "{$meta_prefix}meeting_with",
								'title' => esc_html__('Meeting With', 'jobportal-framework'),
								'type' => 'text',
								'default' => '',
							),
							array(
								'id' => "{$meta_prefix}meeting_date",
								'title' => esc_html__('Date', 'jobportal-framework'),
								'type' => 'text',
								'default' => '',
							),
							array(
								'id' => "{$meta_prefix}meeting_time",
								'title' => esc_html__('Time', 'jobportal-framework'),
								'type' => 'text',
								'default' => '',
							),
							array(
								'id' => "{$meta_prefix}meeting_time_duration",
								'title' => esc_html__('Time Duration', 'jobportal-framework'),
								'type' => 'text',
								'default' => '',
							),
						)
					),
					array(
						'id' => "{$meta_prefix}meeting_message",
						'title' => esc_html__('Message', 'jobportal-framework'),
						'type' => 'textarea',
						'default' => '',
					),
				),
			);
			if (post_type_exists('job_alerts')) {
				$configs['job_alerts_meta_boxes'] = array(
					'name' => esc_html__('Job Alerts Infomation', 'jobportal-framework'),
					'post_type' => array('job_alerts'),
					'fields' => array(
						array(
							'type' => 'row',
							'fields' => array(
								array(
									'id' => "{$meta_prefix}job_alerts_email",
									'title' => esc_html__('Email', 'jobportal-framework'),
									'type' => 'text',
									'default' => '',
									'col' => '6',
								),
								array(
									'id' => "{$meta_prefix}job_alerts_phone",
									'title' => esc_html__('Phone', 'jobportal-framework'),
									'type' => 'text',
									'default' => '',
									'col' => '6',
								),
								array(
									'id' => "{$meta_prefix}job_alerts_location",
									'title' => esc_html__('Location', 'jobportal-framework'),
									'type' => 'select',
									'options' => jobportal_get_taxonomy('jobs-location', false, true, true),
									'default' => '',
									'col' => '3',
								),
								array(
									'id' => "{$meta_prefix}job_alerts_categories",
									'title' => esc_html__('Categories', 'jobportal-framework'),
									'type' => 'select',
									'options' => jobportal_get_taxonomy('jobs-categories', false, true, true),
									'default' => '',
									'col' => '3',
								),
								array(
									'id' => "{$meta_prefix}job_alerts_experience",
									'title' => esc_html__('Experience', 'jobportal-framework'),
									'type' => 'select',
									'options' => jobportal_get_taxonomy('jobs-experience', false, true, true),
									'default' => '',
									'col' => '3',
								),
								array(
									'id' => "{$meta_prefix}job_alerts_frequency",
									'title' => esc_html__('Frequency', 'jobportal-framework'),
									'type' => 'select',
									'options' => array(
										''	=> esc_html__('Select an option', 'jobportal-framework'),
										'daily'	=> esc_html__('Daily', 'jobportal-framework'),
										'weekly'	=> esc_html__('Weekly', 'jobportal-framework'),
										'monthly'	=> esc_html__('Monthly', 'jobportal-framework'),
									),
									'default' => '',
									'col' => '3',
								),
								array(
									'id' => "{$meta_prefix}job_alerts_skill",
									'type' => 'checkbox_list',
									'title' => esc_html__('Skills', 'jobportal-framework'),
									'options' => jobportal_get_taxonomy('jobs-skills', false, true, true),
									'value_inline' => true,
									'default' => array(),
									'col' => '12',
								),
								array(
									'id' => "{$meta_prefix}job_alerts_type",
									'type' => 'checkbox_list',
									'title' => esc_html__('Type', 'jobportal-framework'),
									'options' => jobportal_get_taxonomy('jobs-type', false, true, true),
									'value_inline' => true,
									'default' => array(),
									'col' => '12',
								),
							)
						),
					),
				);
			}

			// Page
			$configs['jobportal_page_options'] = array(
				'name' => esc_html__('Page Options', 'jobportal-framework'),
				'post_type' => array('page'),
				'section' => array_merge(
					apply_filters('jobportal_register_meta_boxes_page_top', array()),
					apply_filters(
						'jobportal_register_meta_boxes_page_main',
						array_merge(
							array(
								array(
									'id' => "{$meta_prefix}page_Layout",
									'title' => esc_html__('Page Layout', 'jobportal-framework'),
									'icon' => 'dashicons-admin-home',
									'fields' => array(
										array(
											'type' => 'row',
											'col' => '12',
											'fields' => array(
												array(
													'id' => "{$meta_prefix}page_body_bg",
													'title' => esc_html__('Body Background', 'jobportal-framework'),
													'type' => 'color',
													'col' => '12',
													'default' => '',
												),
												array(
													'id' => "{$meta_prefix}page_pt_deskop",
													'title' => esc_html__('Padding Top (Deskop)', 'jobportal-framework'),
													'type' => 'text',
													'default' => '',
													'col' => '6',
													'pattern' => '[0-9]*',
												),
												array(
													'id' => "{$meta_prefix}page_pb_deskop",
													'title' => esc_html__('Padding Bottom (Deskop)', 'jobportal-framework'),
													'type' => 'text',
													'default' => '',
													'col' => '6',
													'pattern' => '[0-9]*',
												),
												array(
													'id' => "{$meta_prefix}page_pt_mobie",
													'title' => esc_html__('Padding Top (Mobie)', 'jobportal-framework'),
													'type' => 'text',
													'default' => '',
													'col' => '6',
													'pattern' => '[0-9]*',
												),
												array(
													'id' => "{$meta_prefix}page_pb_mobie",
													'title' => esc_html__('Padding Bottom (Mobie)', 'jobportal-framework'),
													'type' => 'text',
													'default' => '',
													'col' => '6',
													'pattern' => '[0-9]*',
												),
											)
										),

									)
								)
							),
							array(
								array(
									'id' => "{$meta_prefix}page_header",
									'title' => esc_html__('Page Header', 'jobportal-framework'),
									'icon' => 'dashicons-before dashicons-smiley',
									'fields' => array(
										array(
											'type' => 'row',
											'col' => '12',
											'fields' => array(
												array(
													'id' => "{$meta_prefix}header_show",
													'title' => esc_html__('Show Header', 'jobportal-framework'),
													'type' => 'button_set',
													'options' => array(
														'1' => esc_html__('Yes', 'jobportal-framework'),
														'0' => esc_html__('No', 'jobportal-framework'),
													),
													'col' => '4',
													'default' => '1',
												),
												array(
													'id' => "{$meta_prefix}header_style",
													'title' => esc_html__('Header Style', 'jobportal-framework'),
													'type' => 'button_set',
													'options' => array(
														'dark' => esc_html__('Dark', 'jobportal-framework'),
														'light' => esc_html__('Light', 'jobportal-framework'),
													),
													'col' => '4',
													'default' => 'dark',
													'required' => array("{$meta_prefix}header_show", '=', '1'),
												),
												array(
													'id' => "{$meta_prefix}show_top_bar",
													'title' => esc_html__('Show Top Bar', 'jobportal-framework'),
													'type' => 'button_set',
													'options' => array(
														'1' => esc_html__('Yes', 'jobportal-framework'),
														'0' => esc_html__('No', 'jobportal-framework'),
													),
													'col' => '4',
													'default' => '0',
													'required' => array("{$meta_prefix}header_show", '=', '1'),
												),
												array(
													'id' => "{$meta_prefix}header_type",
													'title' => esc_html__('Header Type', 'jobportal-framework'),
													'type' => 'select',
													'default' => '',
													'options' => jobportal_get_header_elementor(),
													'col' => '4',
													'required' => array("{$meta_prefix}header_show", '=', '1'),
												),
												array(
													'id' => "{$meta_prefix}show_header_float",
													'title' => esc_html__('Header Float', 'jobportal-framework'),
													'type' => 'select',
													'options' => array(
														'' => esc_html__('Default', 'jobportal-framework'),
														'1' => esc_html__('Yes', 'jobportal-framework'),
														'0' => esc_html__('No', 'jobportal-framework'),
													),
													'col' => '4',
													'default' => '',
													'required' => array("{$meta_prefix}header_show", '=', '1'),
												),
												array(
													'id' => "{$meta_prefix}show_header_sticky",
													'title' => esc_html__('Header Sticky', 'jobportal-framework'),
													'type' => 'select',
													'options' => array(
														'' => esc_html__('Default', 'jobportal-framework'),
														'1' => esc_html__('Yes', 'jobportal-framework'),
														'0' => esc_html__('No', 'jobportal-framework'),
													),
													'col' => '4',
													'default' => '',
													'required' => array("{$meta_prefix}header_show", '=', '1'),
												),
												array(
													'id' => "{$meta_prefix}show_header_rtl",
													'title' => esc_html__('Header Rtl', 'jobportal-framework'),
													'type' => 'select',
													'options' => array(
														'' => esc_html__('Default', 'jobportal-framework'),
														'1' => esc_html__('Yes', 'jobportal-framework'),
														'0' => esc_html__('No', 'jobportal-framework'),
													),
													'col' => '4',
													'default' => '',
													'required' => array("{$meta_prefix}header_show", '=', '1'),
												),
											)
										),

									)
								)
							),
							array(
								array(
									'id' => "{$meta_prefix}page_footer",
									'title' => esc_html__('Page Footer', 'jobportal-framework'),
									'icon' => 'dashicons-excerpt-view',
									'fields' => array(
										array(
											'type' => 'row',
											'col' => '12',
											'fields' => array(
												array(
													'id' => "{$meta_prefix}footer_show",
													'title' => esc_html__('Show Footer', 'jobportal-framework'),
													'type' => 'button_set',
													'options' => array(
														'1' => esc_html__('Yes', 'jobportal-framework'),
														'0' => esc_html__('No', 'jobportal-framework'),
													),
													'col' => '4',
													'default' => '1',
												),
												array(
													'id' => "{$meta_prefix}footer_type",
													'title' => esc_html__('Footer Type', 'jobportal-framework'),
													'type' => 'select',
													'default' => '',
													'options' => jobportal_get_footer_elementor(),
													'col' => '4',
													'required' => array("{$meta_prefix}footer_show", '=', '1'),
												)
											)
										),

									)
								)
							),
							array(
								array(
									'id' => "{$meta_prefix}page_title",
									'title' => esc_html__('Page Title', 'jobportal-framework'),
									'icon' => 'dashicons-analytics',
									'fields' => array(
										array(
											'type' => 'row',
											'col' => '12',
											'fields' => array(
												array(
													'id' => "{$meta_prefix}page_title_show",
													'title' => esc_html__('Show Page Title', 'jobportal-framework'),
													'type' => 'button_set',
													'options' => array(
														'1' => esc_html__('Yes', 'jobportal-framework'),
														'0' => esc_html__('No', 'jobportal-framework'),
													),
													'col' => '4',
													'default' => '0',
												),
												array(
													'id' => "{$meta_prefix}page_title_color",
													'title' => esc_html__('Text Color', 'jobportal-framework'),
													'type' => 'color',
													'col' => '4',
													'default' => '',
													'required' => array("{$meta_prefix}page_title_show", '=', '1'),
												),
												array(
													'id' => "{$meta_prefix}page_title_bg",
													'title' => esc_html__('Background Color', 'jobportal-framework'),
													'type' => 'color',
													'col' => '4',
													'default' => '',
													'required' => array("{$meta_prefix}page_title_show", '=', '1'),
												),
												array(
													'id' => "{$meta_prefix}page_title_image",
													'title' => esc_html__('Background Image', 'jobportal-framework'),
													'type' => 'image',
													'default' => '',
													'required' => array("{$meta_prefix}page_title_show", '=', '1'),
												)
											)
										),

									)
								)
							)
						)
					),
					apply_filters('jobportal_register_meta_boxes_page_bottom', array())
				),
			);

			return apply_filters('jobportal_register_meta_boxes', $configs);
		}

		/**
		 * Register options config
		 * @param $configs
		 * @return mixed
		 */
		public function register_options_config($configs)
		{
			if (function_exists('pll_the_languages')) {
				$configs['jobportal-framework'] = array(
					'layout' => 'horizontal',
					'page_title' => esc_html__('Theme Options', 'jobportal-framework'),
					'menu_title' => esc_html__('Theme Options', 'jobportal-framework'),
					'option_name' => pll_current_language() . '_jobportal-framework',
					'permission' => 'edit_theme_options',
					'section' => array_merge(
						apply_filters('jobportal_register_options_config_top', array()),
						array(
							$this->general_option(),
							$this->jobs_option(),
							$this->company_option(),
							$this->candidate_option(),
							$this->service_option(),
							$this->payout_option(),
							$this->social_network(),
							$this->login_option(),
							$this->locations_option(),
							$this->google_map_option(),
							$this->price_format_option(),
							$this->payment_option(),
							$this->user_option(),
							$this->url_slugs_option(),
							$this->setup_page(),
							$this->ai_helper(),
							$this->email_management_option(),
							$this->custom_field_jobs_option(),
							$this->custom_field_company_option(),
							$this->custom_field_candidate_option(),
						),
						apply_filters('jobportal_register_options_config_bottom', array())
					)
				);
			} else if (defined('ICL_SITEPRESS_VERSION')) {
				$current_language = apply_filters('wpml_current_language', NULL);

				if ($current_language) {
					$option_name = $current_language . '_jobportal-framework';
				} else {
					$option_name = 'jobportal-framework';
				}
				$configs['jobportal-framework'] = array(
					'layout' => 'horizontal',
					'page_title' => esc_html__('Theme Options', 'jobportal-framework'),
					'menu_title' => esc_html__('Theme Options', 'jobportal-framework'),
					'option_name' => $option_name,
					'permission' => 'edit_theme_options',
					'section' => array_merge(
						apply_filters('jobportal_register_options_config_top', array()),
						array(
							$this->general_option(),
							$this->jobs_option(),
							$this->company_option(),
							$this->candidate_option(),
							$this->service_option(),
							$this->payout_option(),
							$this->social_network(),
							$this->login_option(),
							$this->locations_option(),
							$this->google_map_option(),
							$this->price_format_option(),
							$this->payment_option(),
							$this->user_option(),
							$this->url_slugs_option(),
							$this->setup_page(),
							$this->ai_helper(),
							$this->email_management_option(),
							$this->custom_field_jobs_option(),
							$this->custom_field_company_option(),
							$this->custom_field_candidate_option(),
						),
						apply_filters('jobportal_register_options_config_bottom', array())
					)
				);
			} else {
				$configs['jobportal-framework'] = array(
					'layout' => 'horizontal',
					'page_title' => esc_html__('Theme Options', 'jobportal-framework'),
					'menu_title' => esc_html__('Theme Options', 'jobportal-framework'),
					'option_name' => 'jobportal-framework',
					'permission' => 'edit_theme_options',
					'section' => array_merge(
						apply_filters('jobportal_register_options_config_top', array()),
						array(
							$this->general_option(),
							$this->jobs_option(),
							$this->company_option(),
							$this->candidate_option(),
							$this->service_option(),
							$this->payout_option(),
							$this->social_network(),
							$this->login_option(),
							$this->locations_option(),
							$this->google_map_option(),
							$this->price_format_option(),
							$this->payment_option(),
							$this->user_option(),
							$this->url_slugs_option(),
							$this->setup_page(),
							$this->ai_helper(),
							$this->email_management_option(),
							$this->custom_field_jobs_option(),
							$this->custom_field_company_option(),
							$this->custom_field_candidate_option(),
						),
						apply_filters('jobportal_register_options_config_bottom', array())
					)
				);
			}
			return apply_filters('jobportal_register_options_config', $configs);
		}

		/**
		 * @return mixed|void
		 */
		private function general_option()
		{
			$prefix_code = phone_prefix_code();
			$keys = $values = array();
			foreach ($prefix_code as $key => $value) {
				$keys[] = $key;
				$values[] = $value['name'];
			}
			$phone_code_select = array_combine($keys, $values);

			return apply_filters('jobportal_register_option_general', array(
				'id' => 'jobportal_general_option',
				'title' => esc_html__('General Option', 'jobportal-framework'),
				'icon' => 'dashicons-admin-multisite',
				'fields' => array_merge(
					apply_filters('jobportal_register_option_general_top', array()),
					array(
						array(
							'id' => 'enable_24_time_format',
							'type' => 'button_set',
							'title' => esc_html__('Enable Time Format', 'jobportal-framework'),
							'subtitle' => esc_html__('Enable/Disable Time Format (24H)', 'jobportal-framework'),
							'desc' => '',
							'options' => array(
								'1' => esc_html__('On', 'jobportal-framework'),
								'0' => esc_html__('Off', 'jobportal-framework'),
							),
							'default' => '0',
						),
						array(
							'id' => 'enable_cookie',
							'type' => 'button_set',
							'title' => esc_html__('Enable Cookie Notice', 'jobportal-framework'),
							'subtitle' => esc_html__('Enable/Disable Popup Cookie Notice', 'jobportal-framework'),
							'desc' => '',
							'options' => array(
								'1' => esc_html__('On', 'jobportal-framework'),
								'0' => esc_html__('Off', 'jobportal-framework'),
							),
							'default' => '0',
						),
						array(
							'id' => "enable_back_top",
							'type' => 'button_set',
							'title' => esc_html__('Enable Back To Top', 'jobportal-framework'),
							'subtitle' => esc_html__('Enable/Disable Enable Back To Top', 'jobportal-framework'),
							'options' => array(
								'1' => esc_html__('On', 'jobportal-framework'),
								'0' => esc_html__('Off', 'jobportal-framework'),
							),
							'default' => '0',
						),
						array(
							'id' => 'enable_search_box_dropdown',
							'type' => 'button_set',
							'title' => esc_html__('Enable Search Box', 'jobportal-framework'),
							'subtitle' => esc_html__('Enable/Disable Search Box for Dropdown', 'jobportal-framework'),
							'desc' => '',
							'options' => array(
								'1' => esc_html__('On', 'jobportal-framework'),
								'0' => esc_html__('Off', 'jobportal-framework'),
							),
							'default' => '0',
						),
						array(
							'id' => 'limit_search_box',
							'title' => esc_html__('Limit Search Box', 'jobportal-framework'),
							'type' => 'text',
							'default' => '6',
							'pattern' => '[0-9]*',
							'subtitle' => 'If the option selects more than the set number, a search box will be displayed.',
							'required' => array(
								array("enable_search_box_dropdown", '=', '1')
							),
						),
						array(
							'id' => 'enable_rtl_mode',
							'type' => 'button_set',
							'title' => esc_html__('Enable RTL Mode', 'jobportal-framework'),
							'subtitle' => esc_html__('Enable/Disable RTL mode', 'jobportal-framework'),
							'desc' => '',
							'options' => array(
								'1' => esc_html__('On', 'jobportal-framework'),
								'0' => esc_html__('Off', 'jobportal-framework'),
							),
							'default' => '0'
						),
						array(
							'id' => 'default_phone_number',
							'type' => 'select',
							'title' => esc_html__('Default Phone Number', 'jobportal-framework'),
							'subtitle' => esc_html__('Choose Default Phone Number', 'jobportal-framework'),
							'options' => $phone_code_select,
							'default' => '0',
						),
						array(
							'id' => 'jobportal-cv-type',
							'title' => esc_html__('Cv Types', 'jobportal-framework'),
							'type' => 'text',
							'default' => 'doc,docx,pdf',
							'subtitle' => 'Add "," to separate file formats',
						),
						array(
							'id' => 'jobportal_image_type',
							'title' => esc_html__('Types Upload Image', 'jobportal-framework'),
							'type' => 'text',
							'default' => 'jpg,jpeg,png,gif,webp',
							'subtitle' => 'Add "," to separate file formats',
						),
						array(
							'id' => 'jobportal_max_gallery_images',
							'type' => 'text',
							'title' => esc_html__('Maximum Images', 'jobportal-framework'),
							'subtitle' => esc_html__('Maximum images allowed for single jobs.', 'jobportal-framework'),
							'default' => '5',
						),
						array(
							'id' => 'jobportal_image_max_file_size',
							'type' => 'text',
							'title' => esc_html__('Maximum File Size', 'jobportal-framework'),
							'subtitle' => esc_html__('Maximum upload image size. For example 10kb, 500kb, 1mb, 10mb, 100mb', 'jobportal-framework'),
							'default' => '1000kb',
						),
						array(
							'id' => 'header_script',
							'type' => 'ace_editor',
							'title' => esc_html__('Header Script', 'jobportal-framework'),
							'subtitle' => esc_html__('Add custom scripts inside HEAD tag. You need to have a SCRIPT tag around scripts.', 'jobportal-framework'),
							'default' => ''
						),
						array(
							'id' => 'footer_script',
							'type' => 'ace_editor',
							'title' => esc_html__('Footer Script', 'jobportal-framework'),
							'subtitle' => esc_html__('Add custom scripts you might want to be loaded in the footer of your website. You need to have a SCRIPT tag around scripts.', 'jobportal-framework'),
							'default' => ''
						),
					),
					apply_filters('jobportal_register_option_general_bottom', array())
				)
			));
		}

		/**
		 * @return mixed|void
		 */
		private function payout_option()
		{
			return apply_filters('jobportal_register_option_payout', array(
				'id' => 'jobportal_payout_option',
				'title' => esc_html__('Payout Option', 'jobportal-framework'),
				'icon' => 'dashicons dashicons-index-card',
				'fields' => array_merge(
					apply_filters('jobportal_register_option_payout_top', array()),
					apply_filters('jobportal_register_option_payout_main', array(
						array(
							'id' => "enable_payout_paypal",
							'type' => 'button_set',
							'title' => esc_html__('Enable Payout Paypal', 'jobportal-framework'),
							'subtitle' => esc_html__('Enable/Disable Payout Paypal', 'jobportal-framework'),
							'options' => array(
								'1' => esc_html__('On', 'jobportal-framework'),
								'0' => esc_html__('Off', 'jobportal-framework'),
							),
							'default' => '1',
						),
						array(
							'id' => "enable_payout_stripe",
							'type' => 'button_set',
							'title' => esc_html__('Enable Payout Stripe', 'jobportal-framework'),
							'subtitle' => esc_html__('Enable/Disable Payout Stripe', 'jobportal-framework'),
							'options' => array(
								'1' => esc_html__('On', 'jobportal-framework'),
								'0' => esc_html__('Off', 'jobportal-framework'),
							),
							'default' => '1',
						),
						array(
							'id' => "enable_payout_bank_transfer",
							'type' => 'button_set',
							'title' => esc_html__('Enable Payout Bank Transfer', 'jobportal-framework'),
							'subtitle' => esc_html__('Enable/Disable Bank Transfer', 'jobportal-framework'),
							'options' => array(
								'1' => esc_html__('On', 'jobportal-framework'),
								'0' => esc_html__('Off', 'jobportal-framework'),
							),
							'default' => '1',
						),
						array(
							'id' => "custom_payout_setting",
							'type' => 'panel',
							'title' => esc_html__('Custom Payout', 'jobportal-framework'),
							'sort' => true,
							'panel_title' => 'label',
							'fields' => array(
								array(
									'title' => esc_html__('Name Payout', 'jobportal-framework'),
									'id' => "name",
									'type' => 'text',
									'subtitle' => esc_html__('Enter Same "Name Payout" if you want multiple fields in Payout', 'jobportal-framework'),
									'default' => '',
								),
								array(
									'title' => esc_html__('Label', 'jobportal-framework'),
									'id' => "label",
									'type' => 'text',
									'default' => '',
								),
								array(
									'title' => esc_html__('ID', 'jobportal-framework'),
									'id' => "id",
									'type' => 'text',
									'placeholder' => esc_html__('Enter field ID', 'jobportal-framework'),
									'desc' => esc_html__('ID cannot be duplicated', 'jobportal-framework'),
									'default' => '',
								),
								array(
									'title' => esc_html__('Field Type', 'jobportal-framework'),
									'id' => "type",
									'type' => 'select',
									'default' => 'text',
									'options' => array(
										'text' => esc_html__('Text', 'jobportal-framework'),
										'number' => esc_html__('Number', 'jobportal-framework'),
										'email' => esc_html__('Email', 'jobportal-framework'),
									)
								),
							)
						)
					)),
					apply_filters('jobportal_register_option_payout_bottom', array())
				)
			));
		}

		/**
		 * @return mixed|void
		 */
		private function setup_page()
		{
			$service_page_id = $payment_service_page_id = $service_payment_completed_page_id = $candidate_service_page_id = $submit_service_page_id = array();
			if (jobportal_get_option('enable_post_type_service') === '1') {
				$service_page_id =  array(
					'id' => 'jobportal_employer_service_page_id',
					'title' => esc_html__('Services Page', 'jobportal-framework'),
					'type' => 'select',
					'data' => 'page',
					'data_args' => array(
						'numberposts' => -1,
					)
				);
				$payment_service_page_id =  array(
					'id' => 'jobportal_payment_service_page_id',
					'title' => esc_html__('Payment Service Page', 'jobportal-framework'),
					'type' => 'select',
					'data' => 'page',
					'data_args' => array(
						'numberposts' => -1,
					)
				);
				$service_payment_completed_page_id = array(
					'id' => 'jobportal_service_payment_completed_page_id',
					'title' => esc_html__('Payment Service Completed Page', 'jobportal-framework'),
					'type' => 'select',
					'data' => 'page',
					'data_args' => array(
						'numberposts' => -1,
					)
				);
				$candidate_service_page_id = array(
					'id' => 'jobportal_candidate_service_page_id',
					'title' => esc_html__('Services Page', 'jobportal-framework'),
					'type' => 'select',
					'data' => 'page',
					'data_args' => array(
						'numberposts' => -1,
					)
				);
				$submit_service_page_id = array(
					'id' => 'jobportal_submit_service_page_id',
					'title' => esc_html__('Submit Service', 'jobportal-framework'),
					'type' => 'select',
					'data' => 'page',
					'data_args' => array(
						'numberposts' => -1,
					)
				);
			}

			return apply_filters('jobportal_register_setup_page', array(
				'id' => 'jobportal_setup_page',
				'title' => esc_html__('Setup Page', 'jobportal-framework'),
				'icon' => 'dashicons-admin-page',
				'fields' => array_merge(
					apply_filters('jobportal_register_setup_page_employer_top', array()),
					array(
						array(
							'id' => 'terms_condition',
							'title' => esc_html__('Terms & Conditions', 'jobportal-framework'),
							'type' => 'select',
							'data' => 'page',
							'data_args' => array(
								'numberposts' => -1,
							)
						),
						array(
							'id' => 'privacy_policy',
							'title' => esc_html__('Privacy Policy', 'jobportal-framework'),
							'type' => 'select',
							'data' => 'page',
							'data_args' => array(
								'numberposts' => -1,
							)
						),
						array(
							'id' => 'jobportal_update_profile_page_id',
							'title' => esc_html__('Update Profile', 'jobportal-framework'),
							'type' => 'select',
							'data' => 'page',
							'data_args' => array(
								'numberposts' => -1,
							)
						),
						array(
							'id' => 'jobportal_add_jobs_page_id',
							'title' => esc_html__('Post Jobs (Login)', 'jobportal-framework'),
							'type' => 'select',
							'data' => 'page',
							'data_args' => array(
								'numberposts' => -1,
							)
						),
						array(
							'id' => 'jobportal_add_jobs_not_page_id',
							'title' => esc_html__('Post Jobs (Not Login)', 'jobportal-framework'),
							'type' => 'select',
							'data' => 'page',
							'data_args' => array(
								'numberposts' => -1,
							)
						),
						apply_filters('jobportal_register_setup_page_employer_option_main', array(
							'id' => 'jobportal_register_setup_page_employer_option_main',
							'type' => 'group',
							'title' => esc_html__('Employer Setting', 'jobportal-framework'),
							'fields' => array(
								array(
									'id' => 'jobportal_dashboard_page_id',
									'title' => esc_html__('Dashboard Page', 'jobportal-framework'),
									'type' => 'select',
									'data' => 'page',
									'data_args' => array(
										'numberposts' => -1,
									)
								),
								array(
									'id' => 'jobportal_jobs_dashboard_page_id',
									'title' => esc_html__('Jobs Page', 'jobportal-framework'),
									'type' => 'select',
									'data' => 'page',
									'data_args' => array(
										'numberposts' => -1,
									)
								),
								array(
									'id' => 'jobportal_jobs_submit_page_id',
									'title' => esc_html__('Jobs Submit Page', 'jobportal-framework'),
									'type' => 'select',
									'data' => 'page',
									'data_args' => array(
										'numberposts' => -1,
									)
								),
								array(
									'id' => 'jobportal_applicants_page_id',
									'title' => esc_html__('Applicants Page', 'jobportal-framework'),
									'type' => 'select',
									'data' => 'page',
									'data_args' => array(
										'numberposts' => -1,
									)
								),
								array(
									'id' => 'jobportal_candidates_page_id',
									'title' => esc_html__('Candidates Page', 'jobportal-framework'),
									'type' => 'select',
									'data' => 'page',
									'data_args' => array(
										'numberposts' => -1,
									)
								),
								array(
									'id' => 'jobportal_user_package_page_id',
									'title' => esc_html__('User Package Page', 'jobportal-framework'),
									'type' => 'select',
									'data' => 'page',
									'data_args' => array(
										'numberposts' => -1,
									)
								),
								array(
									'id' => 'jobportal_company_page_id',
									'title' => esc_html__('Company Page', 'jobportal-framework'),
									'type' => 'select',
									'data' => 'page',
									'data_args' => array(
										'numberposts' => -1,
									)
								),
								array(
									'id' => 'jobportal_submit_company_page_id',
									'title' => esc_html__('Submit Company Page', 'jobportal-framework'),
									'type' => 'select',
									'data' => 'page',
									'data_args' => array(
										'numberposts' => -1,
									)
								),
								array(
									'id' => 'jobportal_messages_page_id',
									'title' => esc_html__('Messages Page', 'jobportal-framework'),
									'type' => 'select',
									'data' => 'page',
									'data_args' => array(
										'numberposts' => -1,
									)
								),
								array(
									'id' => 'jobportal_meetings_page_id',
									'title' => esc_html__('Meetings Page', 'jobportal-framework'),
									'type' => 'select',
									'data' => 'page',
									'data_args' => array(
										'numberposts' => -1,
									)
								),
								array(
									'id' => 'jobportal_settings_page_id',
									'title' => esc_html__('Settings Page', 'jobportal-framework'),
									'type' => 'select',
									'data' => 'page',
									'data_args' => array(
										'numberposts' => -1,
									)
								),
								array(
									'id' => 'jobportal_package_page_id',
									'title' => esc_html__('Package Page', 'jobportal-framework'),
									'type' => 'select',
									'data' => 'page',
									'data_args' => array(
										'numberposts' => -1,
									)
								),
								array(
									'id' => 'jobportal_payment_page_id',
									'title' => esc_html__('Payment Jobs Page', 'jobportal-framework'),
									'type' => 'select',
									'data' => 'page',
									'data_args' => array(
										'numberposts' => -1,
									)
								),
								array(
									'id' => 'jobportal_payment_completed_page_id',
									'title' => esc_html__('Payment Jobs Completed Page', 'jobportal-framework'),
									'type' => 'select',
									'data' => 'page',
									'data_args' => array(
										'numberposts' => -1,
									)
								),
								$service_page_id,
								$payment_service_page_id,
								$service_payment_completed_page_id,
							),
						))
					),
					apply_filters('jobportal_register_setup_page_employer_bottom', array()),
					apply_filters('jobportal_register_setup_page_candidate_top', array()),
					array(
						apply_filters('jobportal_register_setup_page_candidate_option_main', array(
							'id' => 'jobportal_register_setup_page_candidate_option_main',
							'type' => 'group',
							'title' => esc_html__('Candidate Setting', 'jobportal-framework'),
							'fields' => array(
								array(
									'id' => 'jobportal_candidate_dashboard_page_id',
									'title' => esc_html__('Dashboard Page', 'jobportal-framework'),
									'type' => 'select',
									'data' => 'page',
									'data_args' => array(
										'numberposts' => -1,
									)
								),
								array(
									'id' => 'jobportal_candidate_profile_page_id',
									'title' => esc_html__('Profile Page', 'jobportal-framework'),
									'type' => 'select',
									'data' => 'page',
									'data_args' => array(
										'numberposts' => -1,
									)
								),
								array(
									'id' => 'jobportal_my_jobs_page_id',
									'title' => esc_html__('My Jobs Page', 'jobportal-framework'),
									'type' => 'select',
									'data' => 'page',
									'data_args' => array(
										'numberposts' => -1,
									)
								),
								array(
									'id' => 'jobportal_candidate_user_package_page_id',
									'title' => esc_html__('User Package Page', 'jobportal-framework'),
									'type' => 'select',
									'data' => 'page',
									'data_args' => array(
										'numberposts' => -1,
									)
								),
								array(
									'id' => 'jobportal_candidate_reviews_page_id',
									'title' => esc_html__('My Reviews Page', 'jobportal-framework'),
									'type' => 'select',
									'data' => 'page',
									'data_args' => array(
										'numberposts' => -1,
									)
								),
								array(
									'id' => 'jobportal_candidate_company_page_id',
									'title' => esc_html__('My Following', 'jobportal-framework'),
									'type' => 'select',
									'data' => 'page',
									'data_args' => array(
										'numberposts' => -1,
									)
								),
								array(
									'id' => 'jobportal_candidate_messages_page_id',
									'title' => esc_html__('Messages Page', 'jobportal-framework'),
									'type' => 'select',
									'data' => 'page',
									'data_args' => array(
										'numberposts' => -1,
									)
								),
								array(
									'id' => 'jobportal_candidate_meetings_page_id',
									'title' => esc_html__('Meetings Page', 'jobportal-framework'),
									'type' => 'select',
									'data' => 'page',
									'data_args' => array(
										'numberposts' => -1,
									)
								),
								array(
									'id' => 'jobportal_candidate_settings_page_id',
									'title' => esc_html__('Settings Page', 'jobportal-framework'),
									'type' => 'select',
									'data' => 'page',
									'data_args' => array(
										'numberposts' => -1,
									)
								),
								array(
									'id' => 'jobportal_candidate_package_page_id',
									'title' => esc_html__('Package Page', 'jobportal-framework'),
									'type' => 'select',
									'data' => 'page',
									'data_args' => array(
										'numberposts' => -1,
									)
								),
								array(
									'id' => 'jobportal_candidate_payment_page_id',
									'title' => esc_html__('Payment Page', 'jobportal-framework'),
									'type' => 'select',
									'data' => 'page',
									'data_args' => array(
										'numberposts' => -1,
									)
								),
								array(
									'id' => 'jobportal_candidate_payment_completed_page_id',
									'title' => esc_html__('Payment Completed Page', 'jobportal-framework'),
									'type' => 'select',
									'data' => 'page',
									'data_args' => array(
										'numberposts' => -1,
									)
								),
								$candidate_service_page_id,
								$submit_service_page_id,
							),
						)),
					),
					apply_filters('jobportal_register_setup_page_candidate_bottom', array())
				)
			));
		}

		/**
		 * @return mixed|void
		 */
		private function url_slugs_option()
		{
			$option_url_service_slugs = array();

			if (jobportal_get_option('enable_post_type_service') === '1') {
				$option_url_service_slugs = array(
					'id' => 'jobportal_main_group',
					'type' => 'group',
					'title' => esc_html__('Service', 'jobportal-framework'),
					'fields' => array(
						array(
							'id' => 'service_url_slug',
							'title' => esc_html__('Service Slug', 'jobportal-framework'),
							'type' => 'text',
							'default' => 'services',
						),
						array(
							'id' => 'service_categories_url_slug',
							'title' => esc_html__('Service Slug', 'jobportal-framework'),
							'type' => 'text',
							'default' => 'service-categories',
						),
						array(
							'id' => 'service_location_url_slug',
							'title' => esc_html__('Location Slug', 'jobportal-framework'),
							'type' => 'text',
							'default' => 'service-location',
						),
						array(
							'id' => 'service_skills_url_slug',
							'title' => esc_html__('Skills Slug', 'jobportal-framework'),
							'type' => 'text',
							'default' => 'service-skills',
						),
						array(
							'id' => 'service_language_url_slug',
							'title' => esc_html__('Language Slug', 'jobportal-framework'),
							'type' => 'text',
							'default' => 'service-language',
						),
					),
				);
			}

			return
				apply_filters(
					'jobportal_register_option_url_slugs',
					array(
						'id' => 'jobportal_url_slugs_option',
						'title' => esc_html__('Url Slug', 'jobportal-framework'),
						'icon' => 'dashicons-admin-links',
						'fields' => array(
							array(
								'id' => 'enable_slug_categories',
								'type' => 'button_set',
								'title' => esc_html__('Slug Categories', 'jobportal-framework'),
								'subtitle' => esc_html__('Show/Hidden Slug Categories', 'jobportal-framework'),
								'desc' => '',
								'options' => array(
									'1' => esc_html__('On', 'jobportal-framework'),
									'0' => esc_html__('Off', 'jobportal-framework'),
								),
								'default' => '1',
							),

							//Jobs
							apply_filters('jobportal_register_option_url_jobs_slugs_top', array()),
							apply_filters('jobportal_register_option_url_jobs_slugs_center', array(
								'id' => 'jobportal_main_group',
								'type' => 'group',
								'title' => esc_html__('Jobs', 'jobportal-framework'),
								'fields' => array(
									array(
										'id' => 'jobs_url_slug',
										'title' => esc_html__('Jobs Slug', 'jobportal-framework'),
										'type' => 'text',
										'default' => 'jobs',
									),
									array(
										'id' => 'jobs_type_url_slug',
										'title' => esc_html__('Type Slug', 'jobportal-framework'),
										'type' => 'text',
										'default' => 'jobs-type',
									),
									array(
										'id' => 'jobs_categories_url_slug',
										'title' => esc_html__('Categories Slug', 'jobportal-framework'),
										'type' => 'text',
										'default' => 'jobs-categories',
									),
									array(
										'id' => 'jobs_skills_url_slug',
										'title' => esc_html__('Skills Slug', 'jobportal-framework'),
										'type' => 'text',
										'default' => 'jobs-skills',
									),
									array(
										'id' => 'jobs_location_url_slug',
										'title' => esc_html__('Location Slug', 'jobportal-framework'),
										'type' => 'text',
										'default' => 'jobs-location',
									),
									array(
										'id' => 'jobs_career_url_slug',
										'title' => esc_html__('Career Slug', 'jobportal-framework'),
										'type' => 'text',
										'default' => 'jobs-career',
									),
									array(
										'id' => 'jobs_experience_url_slug',
										'title' => esc_html__('Experience Slug', 'jobportal-framework'),
										'type' => 'text',
										'default' => 'jobs-experience',
									),
									array(
										'id' => 'jobs_qualification_url_slug',
										'title' => esc_html__('Qualification Slug', 'jobportal-framework'),
										'type' => 'text',
										'default' => 'jobs-qualification',
									),
									array(
										'id' => 'jobs_gender_url_slug',
										'title' => esc_html__('Gender Slug', 'jobportal-framework'),
										'type' => 'text',
										'default' => 'jobs-gender',
									),
								),
							)),
							apply_filters('jobportal_register_option_url_jobs_slugs_bottom', array()),

							//Company
							apply_filters('jobportal_register_option_url_company_slugs_top', array()),
							apply_filters('jobportal_register_option_url_company_slugs_center', array(
								'id' => 'jobportal_main_group',
								'type' => 'group',
								'title' => esc_html__('Company', 'jobportal-framework'),
								'fields' => array(
									array(
										'id' => 'company_url_slug',
										'title' => esc_html__('Company Slug', 'jobportal-framework'),
										'type' => 'text',
										'default' => 'companies',
									),
									array(
										'id' => 'company_categories_url_slug',
										'title' => esc_html__('Categories Slug', 'jobportal-framework'),
										'type' => 'text',
										'default' => 'company-categories',
									),
									array(
										'id' => 'company_location_url_slug',
										'title' => esc_html__('Location Slug', 'jobportal-framework'),
										'type' => 'text',
										'default' => 'company-location',
									),
									array(
										'id' => 'company_size_url_slug',
										'title' => esc_html__('Size Slug', 'jobportal-framework'),
										'type' => 'text',
										'default' => 'company-size',
									),
								),
							)),
							apply_filters('jobportal_register_option_url_company_slugs_bottom', array()),

							//Candidate
							apply_filters('jobportal_register_option_url_candidate_slugs_top', array()),
							apply_filters('jobportal_register_option_url_candidate_slugs_center', array(
								'id' => 'jobportal_main_group',
								'type' => 'group',
								'title' => esc_html__('Candidate', 'jobportal-framework'),
								'fields' => array(
									array(
										'id' => 'candidate_url_slug',
										'title' => esc_html__('Candidate Slug', 'jobportal-framework'),
										'type' => 'text',
										'default' => 'candidates',
									),
									array(
										'id' => 'candidate_categories_url_slug',
										'title' => esc_html__('Categories Slug', 'jobportal-framework'),
										'type' => 'text',
										'default' => 'candidate_categories',
									),
									array(
										'id' => 'candidate_ages_url_slug',
										'title' => esc_html__('Ages Slug', 'jobportal-framework'),
										'type' => 'text',
										'default' => 'candidate-ages',
									),
									array(
										'id' => 'candidate_languages_url_slug',
										'title' => esc_html__('Languages Slug', 'jobportal-framework'),
										'type' => 'text',
										'default' => 'candidate-languages',
									),
									array(
										'id' => 'candidate_qualification_url_slug',
										'title' => esc_html__('Qualification Slug', 'jobportal-framework'),
										'type' => 'text',
										'default' => 'candidate-qualification',
									),
									array(
										'id' => 'candidate_salary_types_url_slug',
										'title' => esc_html__('Salary Types Slug', 'jobportal-framework'),
										'type' => 'text',
										'default' => 'candidate-salary-types',
									),
									array(
										'id' => 'candidate_yoe_url_slug',
										'title' => esc_html__('Yoe Slug', 'jobportal-framework'),
										'type' => 'text',
										'default' => 'candidate-yoe',
									),
									array(
										'id' => 'candidate_education_levels_url_slug',
										'title' => esc_html__('Education Levels Slug', 'jobportal-framework'),
										'type' => 'text',
										'default' => 'candidate-education-levels',
									),
									array(
										'id' => 'candidate_skills_url_slug',
										'title' => esc_html__('Skills Slug', 'jobportal-framework'),
										'type' => 'text',
										'default' => 'candidate-skills',
									),
									array(
										'id' => 'candidate_gender_url_slug',
										'title' => esc_html__('Gender Slug', 'jobportal-framework'),
										'type' => 'text',
										'default' => 'candidate-gender',
									),
									array(
										'id' => 'candidate_locations_url_slug',
										'title' => esc_html__('City Slug', 'jobportal-framework'),
										'type' => 'text',
										'default' => 'candidate-locations',
									),
								),
							)),
							apply_filters('jobportal_register_option_url_candidate_slugs_bottom', array()),

							//Service
							apply_filters('jobportal_register_option_url_service_slugs_top', array()),
							apply_filters(
								'jobportal_register_option_url_service_slugs_center',
								$option_url_service_slugs
							),
							apply_filters('jobportal_register_option_url_service_slugs_bottom', array()),

							//Other
							apply_filters('jobportal_register_option_url_other_slugs_top', array()),
							apply_filters('jobportal_register_option_url_other_slugs_center', array(
								'id' => 'jobportal_main_group',
								'type' => 'group',
								'title' => esc_html__('Other', 'jobportal-framework'),
								'fields' => array(
									array(
										'id' => 'package_url_slug',
										'title' => esc_html__('Package Slug', 'jobportal-framework'),
										'type' => 'text',
										'default' => 'package',
									),
									array(
										'id' => 'invoice_url_slug',
										'title' => esc_html__('Invoice Slug', 'jobportal-framework'),
										'type' => 'text',
										'default' => 'invoice',
									)
								),
							)),
							apply_filters('jobportal_register_option_url_other_slugs_bottom', array()),
						),
					)
				);
		}

		/**
		 * @return mixed|void
		 */
		private function custom_field_jobs_option()
		{
			return apply_filters('jobportal_register_option_custom_field_jobs', array(
				'id' => 'jobportal_custom_field_jobs_option',
				'title' => esc_html__('Custom Field (Jobs)', 'jobportal-framework'),
				'icon' => 'dashicons dashicons-admin-customizer',
				'fields' => array_merge(
					apply_filters('jobportal_register_option_custom_field_jobs_top', array()),
					apply_filters('jobportal_register_option_custom_field_jobs_main', array(
						array(
							'id' => "custom_field_jobs",
							'type' => 'panel',
							'title' => esc_html__('Additional Field', 'jobportal-framework'),
							'sort' => true,
							'panel_title' => 'label',
							'fields' => array(
								array(
									'title' => esc_html__('Label', 'jobportal-framework'),
									'id' => "label",
									'type' => 'text',
									'default' => '',
								),
								array(
									'title' => esc_html__('ID', 'jobportal-framework'),
									'id' => "id",
									'type' => 'text',
									'placeholder' => esc_html__('Enter field ID', 'jobportal-framework'),
									'desc' => esc_html__('ID values cannot be changed after being set!', 'jobportal-framework'),
									'default' => '',
								),
								array(
									'title' => esc_html__('Field Type', 'jobportal-framework'),
									'id' => "field_type",
									'type' => 'select',
									'default' => 'text',
									'options' => array(
										'text' => esc_html__('Text', 'jobportal-framework'),
										'url' => esc_html__('Video', 'jobportal-framework'),
										'textarea' => esc_html__('Textarea', 'jobportal-framework'),
										'image' => esc_html__('Image', 'jobportal-framework'),
										'select' => esc_html__('Select', 'jobportal-framework'),
										'checkbox_list' => esc_html__('Checkbox', 'jobportal-framework'),
									)
								),
								array(
									'title'      => esc_html__('Show in Filter Sidebar', 'jobportal-framework'),
									'label'      => esc_html__('Enable to show', 'jobportal-framework'),
									'id'         => "show_in_filter",
									'type'       => 'checkbox',
									'default'    => '',
									'desc'       => esc_html__('Enable to show this field in the filter sidebar.', 'jobportal-framework'),
									'required'   => array(
										array('field_type', '!=', 'image'),
										array('field_type', '!=', 'url')
									),
								),
								array(
									'title'     => esc_html__('Filter Display Order', 'jobportal-framework'),
									'id'        => 'job_custom_field_sidebar_order',
									'type'      => 'number',
									'min'       => 0,
									'step'      => 1,
									'default'   => 0,
									'desc'      => esc_html__('Set display order for this filter in the sidebar (lower numbers show first)', 'jobportal-framework'),
									'required'  => array(
										array('field_type', '!=', 'image'),
										array('field_type', '!=', 'url')
									),
								),
								array(
									'title' => esc_html__('Options Value', 'jobportal-framework'),
									'subtitle' => esc_html__('Input each per line', 'jobportal-framework'),
									'id' => "select_choices",
									'type' => 'textarea',
									'default' => '',
									'required' => array(
										"field_type",
										'in',
										array('checkbox_list', 'select')
									),
								),
							)
						)
					)),
					apply_filters('jobportal_register_option_custom_field_jobs_bottom', array())
				)
			));
		}
		private function custom_field_company_option()
		{
			return apply_filters('jobportal_register_option_custom_field_company', array(
				'id' => 'jobportal_custom_field_company_option',
				'title' => esc_html__('Custom Field (Company)', 'jobportal-framework'),
				'icon' => 'dashicons dashicons-admin-customizer',
				'fields' => array_merge(
					apply_filters('jobportal_register_option_custom_field_company_top', array()),
					apply_filters('jobportal_register_option_custom_field_company_main', array(
						array(
							'id' => "custom_field_company",
							'type' => 'panel',
							'title' => esc_html__('Additional Field', 'jobportal-framework'),
							'sort' => true,
							'panel_title' => 'label',
							'fields' => array(
								array(
									'title' => esc_html__('Label', 'jobportal-framework'),
									'id' => "label",
									'type' => 'text',
									'default' => '',
								),
								array(
									'title' => esc_html__('ID', 'jobportal-framework'),
									'id' => "id",
									'type' => 'text',
									'placeholder' => esc_html__('Enter field ID', 'jobportal-framework'),
									'desc' => esc_html__('ID values cannot be changed after being set!', 'jobportal-framework'),
									'default' => '',
								),
								array(
									'title' => esc_html__('Field Type', 'jobportal-framework'),
									'id' => "field_type",
									'type' => 'select',
									'default' => 'text',
									'options' => array(
										'text' => esc_html__('Text', 'jobportal-framework'),
										'url' => esc_html__('Video', 'jobportal-framework'),
										'textarea' => esc_html__('Textarea', 'jobportal-framework'),
										'image' => esc_html__('Image', 'jobportal-framework'),
										'select' => esc_html__('Select', 'jobportal-framework'),
										'checkbox_list' => esc_html__('Checkbox', 'jobportal-framework'),
									)
								),
								array(
									'title' => esc_html__('Options Value', 'jobportal-framework'),
									'subtitle' => esc_html__('Input each per line', 'jobportal-framework'),
									'id' => "select_choices",
									'type' => 'textarea',
									'default' => '',
									'required' => array(
										"field_type",
										'in',
										array('checkbox_list', 'select')
									),
								),
							)
						)
					)),
					apply_filters('jobportal_register_option_custom_field_company_bottom', array())
				)
			));
		}

		private function custom_field_candidate_option()
		{
			return apply_filters('jobportal_register_option_custom_field_candidate', array(
				'id' => 'jobportal_custom_field_candidate_option',
				'title' => esc_html__('Custom Field (Candidate)', 'jobportal-framework'),
				'icon' => 'dashicons dashicons-admin-customizer',
				'fields' => array_merge(
					apply_filters('jobportal_register_option_custom_field_candidate_top', array()),
					apply_filters('jobportal_register_option_custom_field_candidate_main', array(
						array(
							'id' => "custom_field_candidate",
							'type' => 'panel',
							'title' => esc_html__('Additional Field', 'jobportal-framework'),
							'sort' => true,
							'panel_title' => 'label',
							'fields' => array(
								array(
									'title' => esc_html__('Tabs', 'jobportal-framework'),
									'id' => "tabs",
									'type' => 'select',
									'default' => 'text',
									'options' => array(
										'info' => esc_html__('Info', 'jobportal-framework'),
										'education' => esc_html__('Education', 'jobportal-framework'),
										'experience' => esc_html__('Experience', 'jobportal-framework'),
										'skills' => esc_html__('Skills', 'jobportal-framework'),
										'projects' => esc_html__('Projects', 'jobportal-framework'),
										'awards' => esc_html__('Awards', 'jobportal-framework'),
										'new' => esc_html__('New Tabs', 'jobportal-framework'),
									),
									'default' => 'new',
								),
								array(
									'title' => esc_html__('Name Tabs', 'jobportal-framework'),
									'id' => "section",
									'type' => 'text',
									'default' => '',
									'required' => array(
										array("tabs", '=', 'new')
									),
								),
								array(
									'title' => esc_html__('Label', 'jobportal-framework'),
									'id' => "label",
									'type' => 'text',
									'default' => '',
								),
								array(
									'title' => esc_html__('ID', 'jobportal-framework'),
									'id' => "id",
									'type' => 'text',
									'placeholder' => esc_html__('Enter field ID', 'jobportal-framework'),
									'desc' => esc_html__('ID values cannot be changed after being set!', 'jobportal-framework'),
									'default' => '',
								),
								array(
									'title' => esc_html__('Field Type', 'jobportal-framework'),
									'id' => "field_type",
									'type' => 'select',
									'default' => 'text',
									'options' => array(
										'text' => esc_html__('Text', 'jobportal-framework'),
										'url' => esc_html__('Video', 'jobportal-framework'),
										'textarea' => esc_html__('Textarea', 'jobportal-framework'),
										'image' => esc_html__('Image', 'jobportal-framework'),
										'select' => esc_html__('Select', 'jobportal-framework'),
										'checkbox_list' => esc_html__('Checkbox', 'jobportal-framework'),
									)
								),
								array(
									'title' => esc_html__('Options Value', 'jobportal-framework'),
									'subtitle' => esc_html__('Input each per line', 'jobportal-framework'),
									'id' => "select_choices",
									'type' => 'textarea',
									'default' => '',
									'required' => array(
										"field_type",
										'in',
										array('checkbox_list', 'select')
									),
								),
							)
						)
					)),
					apply_filters('jobportal_register_option_custom_field_candidate_bottom', array())
				)
			));
		}

		function additional_details_field($meta_prefix)
		{
			if (!class_exists('JobPortal_Framework')) {
				return array(
					'id' => "{$meta_prefix}additional_features",
					'title' => esc_html__('Additional details:', 'jobportal-framework'),
					'type' => 'custom',
					'default' => array(),
					'template' => JOBPORTAL_PLUGIN_DIR . '/includes/admin/templates/additional-details-field.php',
				);
			}
			return array(
				'id' => "{$meta_prefix}additional_features",
				'type' => 'repeater',
				'title' => esc_html__('Additional details:', 'jobportal-framework'),
				'col' => '6',
				'sort' => true,
				'fields' => array(
					array(
						'id' => "{$meta_prefix}additional_feature_title",
						'title' => esc_html__('Title:', 'jobportal-framework'),
						'desc' => esc_html__('Enter additional title', 'jobportal-framework'),
						'type' => 'text',
						'default' => '',
						'col' => '5',
					),
					array(
						'id' => "{$meta_prefix}additional_feature_value",
						'title' => esc_html__('Value', 'jobportal-framework'),
						'desc' => esc_html__('Enter additional value', 'jobportal-framework'),
						'type' => 'text',
						'default' => '',
						'col' => '7',
					),
				)
			);
		}

		/**
		 * @return mixed|void
		 */
		private function price_format_option()
		{
			return apply_filters('jobportal_register_option_price_format', array(
				'id' => 'jobportal_price_format_option',
				'title' => esc_html__('Currency Option', 'jobportal-framework'),
				'icon' => 'dashicons-money',
				'fields' => array_merge(
					apply_filters('jobportal_register_option_price_format_top', array()),
					apply_filters('jobportal_register_option_price_format_main', array(
						array(
							'id' => 'currency_position',
							'title' => esc_html__('Currency Sign Position', 'jobportal-framework'),
							'type' => 'select',
							'options' => array(
								'before' => esc_html__('Before ($59)', 'jobportal-framework'),
								'after' => esc_html__('After (59$)', 'jobportal-framework'),
							),
							'default' => 'before',
						),
						array(
							'id' => 'thousand_separator',
							'title' => esc_html__('Thousand Separator', 'jobportal-framework'),
							'type' => 'text',
							'default' => ',',
						),
						array(
							'id' => 'decimal_separator',
							'title' => esc_html__('Decimal Separator', 'jobportal-framework'),
							'type' => 'text',
							'default' => '.',
						),
						array(
							'id' => 'decimal_places',
							'title' => esc_html__('Decimal Places', 'jobportal-framework'),
							'subtitle' => esc_html__('Choose how to display price decimals', 'jobportal-framework'),
							'desc' => esc_html__('Auto: smart display (200 or 199.99). Fixed: always show same decimals (200.00 or 199.99).', 'jobportal-framework'),
							'type' => 'select',
							'options' => array(
								'auto' => esc_html__('Auto (flexible display)', 'jobportal-framework'),
								'0' => esc_html__('0 (whole numbers only)', 'jobportal-framework'),
								'1' => esc_html__('1 decimal place', 'jobportal-framework'),
								'2' => esc_html__('2 decimal places', 'jobportal-framework'),
								'3' => esc_html__('3 decimal places', 'jobportal-framework'),
							),
							'default' => 'auto',
						),
						array(
							'id' => 'currency_type_default',
							'title' => esc_html__('Currency Type (Default)', 'jobportal-framework'),
							'type' => 'text',
							'default' => 'USD',
						),
						array(
							'id' => 'currency_sign_default',
							'title' => esc_html__('Currency Sign (Default)', 'jobportal-framework'),
							'type' => 'text',
							'default' => '$',
						),
						array(
							'id' => 'salary_text_minimum',
							'title' => esc_html__('Salary Text - Minimum', 'jobportal-framework'),
							'type' => 'text',
							'default' => esc_html__('Min: ', 'jobportal-framework'),
							'desc' => esc_html__('Text displayed before minimum salary amount', 'jobportal-framework'),
						),
						array(
							'id' => 'salary_text_maximum',
							'title' => esc_html__('Salary Text - Maximum', 'jobportal-framework'),
							'type' => 'text',
							'default' => esc_html__('Max: ', 'jobportal-framework'),
							'desc' => esc_html__('Text displayed before maximum salary amount', 'jobportal-framework'),
						),
						array(
							'id' => 'salary_text_negotiable',
							'title' => esc_html__('Salary Text - Negotiable', 'jobportal-framework'),
							'type' => 'text',
							'default' => esc_html__('Negotiable Price', 'jobportal-framework'),
							'desc' => esc_html__('Text displayed for negotiable salary', 'jobportal-framework'),
						),
						array(
							'id' => "currency_fields",
							'type' => 'panel',
							'title' => esc_html__('Currency Field', 'jobportal-framework'),
							'sort' => true,
							'panel_title' => 'label',
							'fields' => array(
								array(
									'id' => 'currency_type',
									'title' => esc_html__('Currency Type', 'jobportal-framework'),
									'type' => 'text',
									'default' => 'VND',
								),
								array(
									'id' => 'currency_sign',
									'title' => esc_html__('Currency Sign', 'jobportal-framework'),
									'type' => 'text',
									'default' => 'đ',
								),
								array(
									'id' => 'currency_conversion',
									'title' => esc_html__('Currency Conversion', 'jobportal-framework'),
									'type' => 'text',
									'desc' => esc_html__('Convert currency values ​​based on default currency', 'jobportal-framework'),
									'default' => '',
								),
							)
						)
					)),
					apply_filters('jobportal_register_option_price_format_bottom', array())
				)
			));
		}

		/**
		 * @return mixed|void
		 */
		private function ai_helper()
		{
			return apply_filters('jobportal_register_option_ai_helper', array(
				'id' => 'jobportal_ai_helper',
				'title' => esc_html__('AI Helper', 'jobportal-framework'),
				'icon' => 'dashicons-smiley',
				'fields' => array_merge(
					apply_filters('jobportal_register_option_ai_helper_top', array()),
					apply_filters('jobportal_register_option_ai_helper_main', array(
						array(
							'id' => 'enable_ai_helper',
							'type' => 'button_set',
							'title' => esc_html__('Show AI Helper', 'jobportal-framework'),
							'subtitle' => esc_html__('Show/Hidden AI Helper in Post Job. Automatically generate your job descriptions with ChatGPT.', 'jobportal-framework'),
							'desc' => esc_html__('Enable this feature to allow users to generate job descriptions using AI. Make sure you have configured a valid OpenAI API key below.', 'jobportal-framework'),
							'options' => array(
								'1' => esc_html__('On', 'jobportal-framework'),
								'0' => esc_html__('Off', 'jobportal-framework'),
							),
							'default' => '0',
						),
						array(
							'id' => 'ai_key',
							'title' => esc_html__('OpenAI API Key', 'jobportal-framework'),
							'type' => 'text',
							'subtitle' => esc_html__('Enter your OpenAI API key', 'jobportal-framework'),
							'desc' => esc_html__('Get your API key from https://platform.openai.com/api-keys. Keep this key secure and never share it publicly.', 'jobportal-framework'),
							'default' => '',
							'required' => array('enable_ai_helper', '=', '1'),
						),
						array(
							'id' => 'ai_model',
							'title' => esc_html__('AI Model', 'jobportal-framework'),
							'subtitle' => esc_html__('Select the OpenAI model to use', 'jobportal-framework'),
							'type' => 'select',
							'options' => model_ai_helper(),
							'desc' => esc_html__('Choose the AI model based on your needs and API key access. GPT-4o is recommended for best quality. GPT-3.5 Turbo is more cost-effective. Note: You must have access to the selected model in your OpenAI account.', 'jobportal-framework'),
							'default' => 'gpt-3.5-turbo',
							'required' => array('enable_ai_helper', '=', '1'),
						),
						array(
							'id' => 'ai_temperature',
							'title' => esc_html__('Temperature', 'jobportal-framework'),
							'subtitle' => esc_html__('Control the creativity of AI responses', 'jobportal-framework'),
							'type' => 'text',
							'desc' => esc_html__('Range: 0.0 to 2.0. Lower values (0.0-0.5) produce more focused and deterministic output. Higher values (0.7-2.0) increase creativity and randomness. Recommended: 0.7 for balanced results.', 'jobportal-framework'),
							'default' => '0.7',
							'validate' => 'numeric',
							'required' => array('enable_ai_helper', '=', '1'),
						),
						array(
							'id' => 'ai_max_tokens',
							'title' => esc_html__('Max Tokens', 'jobportal-framework'),
							'subtitle' => esc_html__('Maximum length of generated content', 'jobportal-framework'),
							'type' => 'text',
							'desc' => esc_html__('Maximum number of tokens in the response. Higher values allow longer job descriptions but cost more. Recommended: 2048 tokens (approximately 1500 words).', 'jobportal-framework'),
							'default' => '2048',
							'validate' => 'numeric',
							'required' => array('enable_ai_helper', '=', '1'),
						),
						array(
							'id' => 'ai_tone',
							'title' => esc_html__('Default Tone', 'jobportal-framework'),
							'subtitle' => esc_html__('Default writing tone for AI generation', 'jobportal-framework'),
							'type' => 'select',
							'options' => tone_ai_helper(),
							'desc' => esc_html__('Users can override this when generating descriptions. This sets the default tone if not specified.', 'jobportal-framework'),
							'required' => array('enable_ai_helper', '=', '1'),
						),
						array(
							'id' => 'ai_language',
							'title' => esc_html__('Default Language', 'jobportal-framework'),
							'subtitle' => esc_html__('Default language for AI generation', 'jobportal-framework'),
							'type' => 'select',
							'options' => language_ai_helper(),
							'desc' => esc_html__('Users can override this when generating descriptions. This sets the default language if not specified.', 'jobportal-framework'),
							'required' => array('enable_ai_helper', '=', '1'),
						),
					)),
					apply_filters('jobportal_register_option_ai_helper_bottom', array())
				)
			));
		}

		/**
		 * @return mixed|void
		 */
		private function locations_option()
		{
			return apply_filters('jobportal_register_option_locations', array(
				'id' => 'jobportal_locations_option',
				'title' => esc_html__('Locations Option', 'jobportal-framework'),
				'icon' => 'dashicons-location-alt',
				'fields' => array_merge(
					apply_filters('jobportal_register_option_locations_top', array()),
					apply_filters('jobportal_register_option_locations_main', array(
						array(
							'id' => "enable_option_state",
							'type' => 'button_set',
							'title' => esc_html__('Enable State', 'jobportal-framework'),
							'subtitle' => esc_html__('Enable/Disable State', 'jobportal-framework'),
							'options' => array(
								'1' => esc_html__('On', 'jobportal-framework'),
								'0' => esc_html__('Off', 'jobportal-framework'),
							),
							'default' => '0',
						),
						array(
							'id' => "enable_option_country",
							'type' => 'button_set',
							'title' => esc_html__('Enable Country', 'jobportal-framework'),
							'subtitle' => esc_html__('Enable/Disable Country', 'jobportal-framework'),
							'options' => array(
								'1' => esc_html__('On', 'jobportal-framework'),
								'0' => esc_html__('Off', 'jobportal-framework'),
							),
							'default' => '0',
							'required' => array("enable_option_state", '=', '1'),
						),
						array(
							'id' => "select_option_country",
							'title' => esc_html__('Country', 'jobportal-framework'),
							'subtitle' => esc_html__('Please Choose Country. If no country is selected will automatically take all the country', 'jobportal-framework'),
							'type' => 'checkbox_list',
							'options' => jobportal_get_countries(),
							'value_inline' => false,
							'default' => array(),
							'required' => array(
								array('enable_option_state', '=', '1'),
								array('enable_option_country', '=', '1')
							),
						),
					)),
					apply_filters('jobportal_register_option_locations_bottom', array())
				)
			));
		}

		/**
		 * @return mixed|void
		 */
		private function google_map_option()
		{
			return apply_filters('jobportal_register_option_google_map', array(
				'id' => 'jobportal_google_map_option',
				'title' => esc_html__('Maps Option', 'jobportal-framework'),
				'icon' => 'dashicons-admin-site',
				'fields' => array_merge(
					apply_filters('jobportal_register_option_google_map_top', array()),
					apply_filters('jobportal_register_option_google_map_main', array(
						array(
							'id' => 'map_effects',
							'title' => esc_html__('Maps Effects', 'jobportal-framework'),
							'type' => 'select',
							'options' => array(
								'' => esc_html__('None', 'jobportal-framework'),
								'shine' => esc_html__('Shine', 'jobportal-framework'),
								'popup' => esc_html__('Popup', 'jobportal-framework'),
							),
							'default' => 'shine',
						),
						array(
							'id' => 'map_type',
							'title' => esc_html__('Maps Type', 'jobportal-framework'),
							'type' => 'select',
							'options' => array(
								'google_map' => esc_html__('Google Map', 'jobportal-framework'),
								'mapbox' => esc_html__('Mapbox', 'jobportal-framework'),
								'openstreetmap' => esc_html__('OpenStreetMap', 'jobportal-framework'),
							),
							'default' => 'mapbox',
						),
						array(
							'id' => 'map_ssl',
							'title' => esc_html__('Maps SSL', 'jobportal-framework'),
							'subtitle' => esc_html__('Use maps with ssl', 'jobportal-framework'),
							'type' => 'button_set',
							'options' => array(
								'1' => esc_html__('Yes', 'jobportal-framework'),
								'0' => esc_html__('No', 'jobportal-framework'),
							),
							'default' => '0',
						),
						array(
							'id' => 'googlemap_type',
							'title' => esc_html__('Google Maps Type', 'jobportal-framework'),
							'type' => 'select',
							'options' => array(
								'roadmap' => esc_html__('Roadmap', 'jobportal-framework'),
								'satellite' => esc_html__('Satellite', 'jobportal-framework'),
								'hybrid' => esc_html__('Hybrid', 'jobportal-framework'),
								'terrain' => esc_html__('Terrain', 'jobportal-framework'),
							),
							'default' => 'roadmap',
							'required' => array("map_type", '=', 'google_map'),
						),
						array(
							'id' => 'googlemap_api_key',
							'type' => 'text',
							'title' => esc_html__('Google Maps API KEY', 'jobportal-framework'),
							'subtitle' => esc_html__('Enter your google maps api key', 'jobportal-framework'),
							'default' => 'AIzaSyBvPDNG6pePr9iFpeRKaOlaZF_l0oT3lWk',
							'required' => array("map_type", '=', 'google_map'),
						),
						array(
							'id' => 'mapbox_api_key',
							'type' => 'text',
							'title' => esc_html__('Mapbox API KEY', 'jobportal-framework'),
							'subtitle' => esc_html__('Enter your mapbox api key', 'jobportal-framework'),
							'default' => 'pk.eyJ1Ijoic2F5aTc3NDciLCJhIjoiY2tpcXRmYW1tMWpjMjJzbGllbThieTFlaCJ9.eDj6zNLBZpG-veFqXiyVPw',
							'required' => array("map_type", '=', 'mapbox'),
						),
						array(
							'id' => 'openstreetmap_api_key',
							'type' => 'text',
							'title' => esc_html__('OpenStreetMap API KEY', 'jobportal-framework'),
							'subtitle' => esc_html__('Enter your OpenStreetMap api key', 'jobportal-framework'),
							'default' => 'pk.eyJ1Ijoic2F5aTc3NDciLCJhIjoiY2tpcXRmYW1tMWpjMjJzbGllbThieTFlaCJ9.eDj6zNLBZpG-veFqXiyVPw',
							'required' => array("map_type", '=', 'openstreetmap'),
						),
						array(
							'id' => 'map_pin_cluster',
							'title' => esc_html__('Pin Cluster', 'jobportal-framework'),
							'subtitle' => esc_html__('Use pin cluster on map', 'jobportal-framework'),
							'type' => 'button_set',
							'options' => array(
								'1' => esc_html__('Yes', 'jobportal-framework'),
								'0' => esc_html__('No', 'jobportal-framework'),
							),
							'default' => '0',
							'required' => array("map_type", '=', 'google_map'),
						),
						array(
							'id' => 'googlemap_style',
							'type' => 'ace_editor',
							'title' => esc_html__('Style for Google Map', 'jobportal-framework'),
							'subtitle' => sprintf(
								__('Use %s https://snazzymaps.com/ %s to create styles', 'jobportal-framework'),
								'<a href="https://snazzymaps.com/" target="_blank">',
								'</a>'
							),
							'default' => '',
							'required' => array("map_type", '=', 'google_map'),
						),
						array(
							'id' => 'mapbox_style',
							'title' => esc_html__('Style for Mapbox', 'jobportal-framework'),
							'type' => 'select',
							'options' => array(
								'streets-v11' => esc_html__('Streets', 'jobportal-framework'),
								'light-v10' => esc_html__('Light', 'jobportal-framework'),
								'dark-v10' => esc_html__('Dark', 'jobportal-framework'),
								'outdoors-v11' => esc_html__('Outdoors', 'jobportal-framework'),
								'satellite-v9' => esc_html__('Satellite', 'jobportal-framework'),
							),
							'required' => array("map_type", '=', 'mapbox'),
						),
						array(
							'id' => 'openstreetmap_style',
							'title' => esc_html__('Style for OpenStreetMap', 'jobportal-framework'),
							'type' => 'select',
							'options' => array(
								'streets-v11' => esc_html__('Streets', 'jobportal-framework'),
								'light-v10' => esc_html__('Light', 'jobportal-framework'),
								'dark-v10' => esc_html__('Dark', 'jobportal-framework'),
								'outdoors-v11' => esc_html__('Outdoors', 'jobportal-framework'),
								'satellite-v9' => esc_html__('Satellite', 'jobportal-framework'),
							),
							'required' => array("map_type", '=', 'openstreetmap'),
						),
						array(
							'id' => 'map_zoom_level',
							'type' => 'text',
							'title' => esc_html__('Default Map Zoom', 'jobportal-framework'),
							'default' => '3'
						),
						array(
							'id' => 'map_lat_default',
							'type' => 'text',
							'title' => esc_html__('Default Map Latitude', 'jobportal-framework'),
							'default' => '59.325'
						),
						array(
							'id' => 'map_lng_default',
							'type' => 'text',
							'title' => esc_html__('Default Map Longitude ', 'jobportal-framework'),
							'default' => '18.070'
						),
					)),
					apply_filters('jobportal_register_option_google_map_bottom', array())
				)
			));
		}

		/**
		 * @return mixed|void
		 */
		private function payment_option()
		{
			$option_payment_service = array();

			if (jobportal_get_option('enable_post_type_service') === '1') {
				$option_payment_service = array(
					'id' => 'jobportal_main_group',
					'type' => 'group',
					'title' => esc_html__('Service Settings', 'jobportal-framework'),
					'fields' => array(
						array(
							'id' => 'jobportal_service_paypal',
							'type' => 'info',
							'style' => 'info',
							'title' => esc_html__('Paypal Setting', 'jobportal-framework'),
						),
						array(
							'id' => 'service_enable_paypal',
							'title' => esc_html__('Enable Paypal', 'jobportal-framework'),
							'type' => 'button_set',
							'options' => array(
								'1' => esc_html__('Enabled', 'jobportal-framework'),
								'0' => esc_html__('Disabled', 'jobportal-framework'),
							),
							'default' => '1',
						),
						array(
							'id' => 'service_paypal_api',
							'type' => 'select',
							'required' => array(
								array('service_enable_paypal', '=', '1'),
							),
							'title' => esc_html__('Paypal API', 'jobportal-framework'),
							'subtitle' => esc_html__('Sandbox = test API. LIVE = real payments API', 'jobportal-framework'),
							'desc' => esc_html__('Update PayPal settings according to API type selection', 'jobportal-framework'),
							'options' => array(
								'sandbox' => esc_html__('Sandbox', 'jobportal-framework'),
								'live' => esc_html__('Live', 'jobportal-framework')
							),
							'default' => 'sandbox',
						),
						array(
							'id' => 'service_paypal_client_id',
							'type' => 'text',
							'required' => array(
								array('service_enable_paypal', '=', '1'),
							),
							'title' => esc_html__('Paypal Client ID', 'jobportal-framework'),
							'subtitle' => '',
							'default' => '',
						),
						array(
							'id' => 'service_paypal_client_secret_key',
							'type' => 'text',
							'required' => array(
								array('service_enable_paypal', '=', '1'),
							),
							'title' => esc_html__('Paypal Client Secret Key', 'jobportal-framework'),
							'subtitle' => '',
							'default' => '',
						),
						array(
							'id' => 'jobportal_service_stripe',
							'type' => 'info',
							'style' => 'info',
							'title' => esc_html__('Stripe Setting', 'jobportal-framework'),
						),
						array(
							'id' => 'service_enable_stripe',
							'title' => esc_html__('Enable Stripe', 'jobportal-framework'),
							'type' => 'button_set',
							'options' => array(
								'1' => esc_html__('Enabled', 'jobportal-framework'),
								'0' => esc_html__('Disabled', 'jobportal-framework'),
							),
							'default' => '1',
						),
						array(
							'id' => 'service_stripe_secret_key',
							'type' => 'text',
							'required' => array(
								array('service_enable_stripe', '=', '1'),
							),
							'title' => esc_html__('Stripe Secret Key', 'jobportal-framework'),
							'subtitle' => esc_html__('Info is taken from your account at https://dashboard.stripe.com/login', 'jobportal-framework'),
							'default' => '',
						),
						array(
							'id' => 'service_tripe_publishable_key',
							'type' => 'text',
							'required' => array(
								array('service_enable_stripe', '=', '1'),
							),
							'title' => esc_html__('Stripe Publishable Key', 'jobportal-framework'),
							'subtitle' => esc_html__('Info is taken from your account at https://dashboard.stripe.com/login', 'jobportal-framework'),
							'default' => '',
						),
						array(
							'id' => 'jobportal_service_razor',
							'type' => 'info',
							'style' => 'info',
							'title' => esc_html__('Razor Setting', 'jobportal-framework'),
						),
						array(
							'id' => 'service_enable_razor',
							'title' => esc_html__('Enable Razor', 'jobportal-framework'),
							'type' => 'button_set',
							'options' => array(
								'1' => esc_html__('Enabled', 'jobportal-framework'),
								'0' => esc_html__('Disabled', 'jobportal-framework'),
							),
							'default' => '0',
						),
						array(
							'id' => 'service_razor_key_id',
							'type' => 'text',
							'required' => array(
								array('service_enable_razor', '=', '1'),
							),
							'title' => esc_html__('Razor Key ID', 'jobportal-framework'),
							'subtitle' => esc_html__('Info is taken from your account at https://dashboard.razorpay.com/', 'jobportal-framework'),
							'default' => '',
						),
						array(
							'id' => 'service_razor_key_secret',
							'type' => 'text',
							'required' => array(
								array('service_enable_razor', '=', '1'),
							),
							'title' => esc_html__('Razor Key Secret', 'jobportal-framework'),
							'subtitle' => esc_html__('Info is taken from your account at https://dashboard.razorpay.com/', 'jobportal-framework'),
							'default' => '',
						),
						array(
							'id' => 'jobportal_service_wire_transfer',
							'type' => 'info',
							'style' => 'info',
							'title' => esc_html__('Wire Transfer Setting', 'jobportal-framework'),
						),
						array(
							'id' => 'service_enable_wire_transfer',
							'title' => esc_html__('Enable Wire Transfer', 'jobportal-framework'),
							'type' => 'button_set',
							'options' => array(
								'1' => esc_html__('Enabled', 'jobportal-framework'),
								'0' => esc_html__('Disabled', 'jobportal-framework'),
							),
							'default' => '1',
						),
						array(
							'id' => 'service_wire_transfer_card_number',
							'type' => 'text',
							'required' => array(
								array('service_enable_wire_transfer', '=', '1'),
							),
							'title' => esc_html__('Account Number', 'jobportal-framework'),
							'subtitle' => esc_html__('Bank account number for wire transfer', 'jobportal-framework'),
							'placeholder' => esc_html__('e.g., 1234567890', 'jobportal-framework'),
							'desc' => esc_html__('Enter your bank account number exactly as it appears on your bank statement', 'jobportal-framework'),
							'default' => '',
						),
						array(
							'id' => 'service_wire_transfer_card_name',
							'type' => 'text',
							'required' => array(
								array('service_enable_wire_transfer', '=', '1'),
							),
							'title' => esc_html__('Account Holder Name', 'jobportal-framework'),
							'subtitle' => esc_html__('Name of the account holder (beneficiary name)', 'jobportal-framework'),
							'placeholder' => esc_html__('e.g., John Doe or Company Name', 'jobportal-framework'),
							'desc' => esc_html__('Enter the full name exactly as it appears on the bank account', 'jobportal-framework'),
							'default' => '',
						),
						array(
							'id' => 'service_wire_transfer_bank_name',
							'type' => 'text',
							'required' => array(
								array('service_enable_wire_transfer', '=', '1'),
							),
							'title' => esc_html__('Bank Name', 'jobportal-framework'),
							'subtitle' => esc_html__('Full name of the bank', 'jobportal-framework'),
							'placeholder' => esc_html__('e.g., Bank of America, HSBC, etc.', 'jobportal-framework'),
							'desc' => esc_html__('Enter the complete official name of your bank', 'jobportal-framework'),
							'default' => '',
						),
						array(
							'id' => 'service_wire_transfer_bank_address',
							'type' => 'textarea',
							'required' => array(
								array('service_enable_wire_transfer', '=', '1'),
							),
							'title' => esc_html__('Bank Address', 'jobportal-framework'),
							'subtitle' => esc_html__('Full address of the bank (street, city, country)', 'jobportal-framework'),
							'placeholder' => esc_html__('e.g., 123 Main Street, New York, NY 10001, USA', 'jobportal-framework'),
							'desc' => esc_html__('Enter the complete address including street, city, state/province, postal code, and country', 'jobportal-framework'),
							'default' => '',
						),
						array(
							'id' => 'service_wire_transfer_swift_bic',
							'type' => 'text',
							'required' => array(
								array('service_enable_wire_transfer', '=', '1'),
							),
							'title' => esc_html__('SWIFT/BIC Code', 'jobportal-framework'),
							'subtitle' => esc_html__('Required for international transfers (8-11 characters)', 'jobportal-framework'),
							'placeholder' => esc_html__('e.g., CHASUS33 or CHASUS33XXX', 'jobportal-framework'),
							'desc' => esc_html__('Enter your bank\'s SWIFT or BIC code. Format: 4 letters (bank) + 2 letters (country) + 2 characters (location) + optional 3 characters (branch)', 'jobportal-framework'),
							'default' => '',
						),
						array(
							'id' => 'service_wire_transfer_iban',
							'type' => 'text',
							'required' => array(
								array('service_enable_wire_transfer', '=', '1'),
							),
							'title' => esc_html__('IBAN', 'jobportal-framework'),
							'subtitle' => esc_html__('International Bank Account Number (required for EU transfers)', 'jobportal-framework'),
							'placeholder' => esc_html__('e.g., GB82 WEST 1234 5698 7654 32', 'jobportal-framework'),
							'desc' => esc_html__('Enter your IBAN (required for European transfers). Format: 2 letters (country) + 2 digits (check) + up to 30 alphanumeric characters', 'jobportal-framework'),
							'default' => '',
						),
						array(
							'id' => 'service_wire_transfer_routing_number',
							'type' => 'text',
							'required' => array(
								array('service_enable_wire_transfer', '=', '1'),
							),
							'title' => esc_html__('Routing Number / ABA', 'jobportal-framework'),
							'subtitle' => esc_html__('Required for US transfers (9 digits)', 'jobportal-framework'),
							'placeholder' => esc_html__('e.g., 021000021', 'jobportal-framework'),
							'desc' => esc_html__('Enter your 9-digit routing number (also known as ABA routing transit number) for US bank accounts', 'jobportal-framework'),
							'default' => '',
						),
						array(
							'id' => 'service_wire_transfer_info',
							'type' => 'editor',
							'required' => array(
								array('service_enable_wire_transfer', '=', '1'),
							),
							'title' => esc_html__('Wire Transfer Instructions', 'jobportal-framework'),
							'subtitle' => esc_html__('Additional instructions for customers (HTML allowed)', 'jobportal-framework'),
							'args' => array(
								'media_buttons' => false,
								'quicktags' => true,
							),
							'default' => '',
						),
						array(
							'id' => 'jobportal_service_woocheckout',
							'type' => 'info',
							'style' => 'info',
							'title' => esc_html__('Woocommerce Setting', 'jobportal-framework'),
						),
						array(
							'id' => 'service_enable_woocheckout',
							'title' => esc_html__('Enable Woocommerce Checkout', 'jobportal-framework'),
							'type' => 'button_set',
							'subtitle' => esc_html__('Works when you activate plugin woocomerce and checkout page', 'jobportal-framework'),
							'options' => array(
								'1' => esc_html__('Enabled', 'jobportal-framework'),
								'0' => esc_html__('Disabled', 'jobportal-framework'),
							),
							'default' => '0',
						),
					),
				);
			}
			return apply_filters('jobportal_register_option_payment', array(
				'id' => 'jobportal_payment_option',
				'title' => esc_html__('Payment Option', 'jobportal-framework'),
				'icon' => 'dashicons-cart',
				'fields' => array(
					//Employer
					apply_filters('jobportal_register_option_payment_top', array()),
					apply_filters('jobportal_register_option_payment_main', array(
						'id' => 'jobportal_main_group',
						'type' => 'group',
						'title' => esc_html__('Employer Settings', 'jobportal-framework'),
						'fields' => array(
							array(
								'id' => 'paid_submission_type',
								'type' => 'select',
								'title' => esc_html__('Paid Submission Type', 'jobportal-framework'),
								'subtitle' => '',
								'options' => array(
									'no' => esc_html__('Free Submit', 'jobportal-framework'),
									'per_package' => esc_html__('Pay Per Package', 'jobportal-framework')
								),
								'default' => 'no',
							),
							array(
								'id' => 'jobportal_paypal',
								'type' => 'info',
								'style' => 'info',
								'title' => esc_html__('Paypal Setting', 'jobportal-framework'),
								'required' => array('paid_submission_type', '!=', 'no'),
							),
							array(
								'id' => 'enable_paypal',
								'title' => esc_html__('Enable Paypal', 'jobportal-framework'),
								'type' => 'button_set',
								'options' => array(
									'1' => esc_html__('Enabled', 'jobportal-framework'),
									'0' => esc_html__('Disabled', 'jobportal-framework'),
								),
								'default' => '0',
								'required' => array('paid_submission_type', '!=', 'no'),
							),
							array(
								'id' => 'paypal_api',
								'type' => 'select',
								'required' => array(
									array('enable_paypal', '=', '1'),
									array('paid_submission_type', '!=', 'no')
								),
								'title' => esc_html__('Paypal Api', 'jobportal-framework'),
								'subtitle' => esc_html__('Sandbox = test API. LIVE = real payments API', 'jobportal-framework'),
								'desc' => esc_html__('Update PayPal settings according to API type selection', 'jobportal-framework'),
								'options' => array(
									'sandbox' => esc_html__('Sandbox', 'jobportal-framework'),
									'live' => esc_html__('Live', 'jobportal-framework')
								),
								'default' => 'sandbox',
							),
							array(
								'id' => 'paypal_client_id',
								'type' => 'text',
								'required' => array(
									array('enable_paypal', '=', '1'),
									array('paid_submission_type', '!=', 'no')
								),
								'title' => esc_html__('Paypal Client ID', 'jobportal-framework'),
								'subtitle' => '',
								'default' => '',
							),
							array(
								'id' => 'paypal_client_secret_key',
								'type' => 'text',
								'required' => array(
									array('enable_paypal', '=', '1'),
									array('paid_submission_type', '!=', 'no')
								),
								'title' => esc_html__('Paypal Client Secret Key', 'jobportal-framework'),
								'subtitle' => '',
								'default' => '',
							),
							array(
								'id' => 'jobportal_stripe',
								'type' => 'info',
								'style' => 'info',
								'title' => esc_html__('Stripe Setting', 'jobportal-framework'),
								'required' => array('paid_submission_type', '!=', 'no'),
							),
							array(
								'id' => 'enable_stripe',
								'title' => esc_html__('Enable Stripe', 'jobportal-framework'),
								'type' => 'button_set',
								'options' => array(
									'1' => esc_html__('Enabled', 'jobportal-framework'),
									'0' => esc_html__('Disabled', 'jobportal-framework'),
								),
								'default' => '0',
								'required' => array('paid_submission_type', '!=', 'no'),
							),
							array(
								'id' => 'stripe_secret_key',
								'type' => 'text',
								'required' => array(
									array('enable_stripe', '=', '1'),
									array('paid_submission_type', '!=', 'no')
								),
								'title' => esc_html__('Stripe Secret Key', 'jobportal-framework'),
								'subtitle' => esc_html__('Info is taken from your account at https://dashboard.stripe.com/login', 'jobportal-framework'),
								'default' => '',
							),
							array(
								'id' => 'stripe_publishable_key',
								'type' => 'text',
								'required' => array(
									array('enable_stripe', '=', '1'),
									array('paid_submission_type', '!=', 'no')
								),
								'title' => esc_html__('Stripe Publishable Key', 'jobportal-framework'),
								'subtitle' => esc_html__('Info is taken from your account at https://dashboard.stripe.com/login', 'jobportal-framework'),
								'default' => '',
							),
							array(
								'id' => 'jobportal_employer_razor',
								'type' => 'info',
								'style' => 'info',
								'title' => esc_html__('Razor Setting', 'jobportal-framework'),
							),
							array(
								'id' => 'employer_enable_razor',
								'title' => esc_html__('Enable Razor', 'jobportal-framework'),
								'type' => 'button_set',
								'options' => array(
									'1' => esc_html__('Enabled', 'jobportal-framework'),
									'0' => esc_html__('Disabled', 'jobportal-framework'),
								),
								'default' => '0',
							),
							array(
								'id' => 'razor_key_id',
								'type' => 'text',
								'required' => array(
									array('employer_enable_razor', '=', '1'),
								),
								'title' => esc_html__('Razor Key ID', 'jobportal-framework'),
								'subtitle' => esc_html__('Info is taken from your account at https://dashboard.razorpay.com/', 'jobportal-framework'),
								'default' => '',
							),
							array(
								'id' => 'razor_key_secret',
								'type' => 'text',
								'required' => array(
									array('employer_enable_razor', '=', '1'),
								),
								'title' => esc_html__('Razor Key Secret', 'jobportal-framework'),
								'subtitle' => esc_html__('Info is taken from your account at https://dashboard.razorpay.com/', 'jobportal-framework'),
								'default' => '',
							),
							array(
								'id' => 'jobportal_wire_transfer',
								'type' => 'info',
								'style' => 'info',
								'title' => esc_html__('Wire Transfer Setting', 'jobportal-framework'),
								'required' => array('paid_submission_type', '!=', 'no'),
							),
							array(
								'id' => 'enable_wire_transfer',
								'title' => esc_html__('Enable Wire Transfer', 'jobportal-framework'),
								'type' => 'button_set',
								'options' => array(
									'1' => esc_html__('Enabled', 'jobportal-framework'),
									'0' => esc_html__('Disabled', 'jobportal-framework'),
								),
								'default' => '0',
								'required' => array('paid_submission_type', '!=', 'no'),
							),
							array(
								'id' => 'wire_transfer_card_number',
								'type' => 'text',
								'required' => array(
									array('enable_wire_transfer', '=', '1'),
									array('paid_submission_type', '!=', 'no')
								),
								'title' => esc_html__('Account Number', 'jobportal-framework'),
								'subtitle' => esc_html__('Bank account number for wire transfer', 'jobportal-framework'),
								'placeholder' => esc_html__('e.g., 1234567890', 'jobportal-framework'),
								'desc' => esc_html__('Enter your bank account number exactly as it appears on your bank statement', 'jobportal-framework'),
								'default' => '',
							),
							array(
								'id' => 'wire_transfer_card_name',
								'type' => 'text',
								'required' => array(
									array('enable_wire_transfer', '=', '1'),
									array('paid_submission_type', '!=', 'no')
								),
								'title' => esc_html__('Account Holder Name', 'jobportal-framework'),
								'subtitle' => esc_html__('Name of the account holder (beneficiary name)', 'jobportal-framework'),
								'placeholder' => esc_html__('e.g., John Doe or Company Name', 'jobportal-framework'),
								'desc' => esc_html__('Enter the full name exactly as it appears on the bank account', 'jobportal-framework'),
								'default' => '',
							),
							array(
								'id' => 'wire_transfer_bank_name',
								'type' => 'text',
								'required' => array(
									array('enable_wire_transfer', '=', '1'),
									array('paid_submission_type', '!=', 'no')
								),
								'title' => esc_html__('Bank Name', 'jobportal-framework'),
								'subtitle' => esc_html__('Full name of the bank', 'jobportal-framework'),
								'placeholder' => esc_html__('e.g., Bank of America, HSBC, etc.', 'jobportal-framework'),
								'desc' => esc_html__('Enter the complete official name of your bank', 'jobportal-framework'),
								'default' => '',
							),
							array(
								'id' => 'wire_transfer_bank_address',
								'type' => 'textarea',
								'required' => array(
									array('enable_wire_transfer', '=', '1'),
									array('paid_submission_type', '!=', 'no')
								),
								'title' => esc_html__('Bank Address', 'jobportal-framework'),
								'subtitle' => esc_html__('Full address of the bank (street, city, country)', 'jobportal-framework'),
								'placeholder' => esc_html__('e.g., 123 Main Street, New York, NY 10001, USA', 'jobportal-framework'),
								'desc' => esc_html__('Enter the complete address including street, city, state/province, postal code, and country', 'jobportal-framework'),
								'default' => '',
							),
							array(
								'id' => 'wire_transfer_swift_bic',
								'type' => 'text',
								'required' => array(
									array('enable_wire_transfer', '=', '1'),
									array('paid_submission_type', '!=', 'no')
								),
								'title' => esc_html__('SWIFT/BIC Code', 'jobportal-framework'),
								'subtitle' => esc_html__('Required for international transfers (8-11 characters)', 'jobportal-framework'),
								'placeholder' => esc_html__('e.g., CHASUS33 or CHASUS33XXX', 'jobportal-framework'),
								'desc' => esc_html__('Enter your bank\'s SWIFT or BIC code. Format: 4 letters (bank) + 2 letters (country) + 2 characters (location) + optional 3 characters (branch)', 'jobportal-framework'),
								'default' => '',
							),
							array(
								'id' => 'wire_transfer_iban',
								'type' => 'text',
								'required' => array(
									array('enable_wire_transfer', '=', '1'),
									array('paid_submission_type', '!=', 'no')
								),
								'title' => esc_html__('IBAN', 'jobportal-framework'),
								'subtitle' => esc_html__('International Bank Account Number (required for EU transfers)', 'jobportal-framework'),
								'placeholder' => esc_html__('e.g., GB82 WEST 1234 5698 7654 32', 'jobportal-framework'),
								'desc' => esc_html__('Enter your IBAN (required for European transfers). Format: 2 letters (country) + 2 digits (check) + up to 30 alphanumeric characters', 'jobportal-framework'),
								'default' => '',
							),
							array(
								'id' => 'wire_transfer_routing_number',
								'type' => 'text',
								'required' => array(
									array('enable_wire_transfer', '=', '1'),
									array('paid_submission_type', '!=', 'no')
								),
								'title' => esc_html__('Routing Number / ABA', 'jobportal-framework'),
								'subtitle' => esc_html__('Required for US transfers (9 digits)', 'jobportal-framework'),
								'placeholder' => esc_html__('e.g., 021000021', 'jobportal-framework'),
								'desc' => esc_html__('Enter your 9-digit routing number (also known as ABA routing transit number) for US bank accounts', 'jobportal-framework'),
								'default' => '',
							),
							array(
								'id' => 'wire_transfer_info',
								'type' => 'editor',
								'required' => array(
									array('enable_wire_transfer', '=', '1'),
									array('paid_submission_type', '!=', 'no')
								),
								'title' => esc_html__('Wire Transfer Instructions', 'jobportal-framework'),
								'subtitle' => esc_html__('Additional instructions for customers (HTML allowed)', 'jobportal-framework'),
								'args' => array(
									'media_buttons' => false,
									'quicktags' => true,
								),
								'default' => '',
							),
							array(
								'id' => 'jobportal_woocheckout',
								'type' => 'info',
								'style' => 'info',
								'title' => esc_html__('Woocommerce Setting', 'jobportal-framework'),
								'required' => array('paid_submission_type', '!=', 'no'),
							),
							array(
								'id' => 'enable_woocheckout',
								'title' => esc_html__('Enable Woocommerce Checkout', 'jobportal-framework'),
								'type' => 'button_set',
								'subtitle' => esc_html__('Works when you activate plugin woocomerce and checkout page', 'jobportal-framework'),
								'options' => array(
									'1' => esc_html__('Enabled', 'jobportal-framework'),
									'0' => esc_html__('Disabled', 'jobportal-framework'),
								),
								'default' => '0',
								'required' => array('paid_submission_type', '!=', 'no'),
							),
						),
					)),
					apply_filters('jobportal_register_option_payment_bottom', array()),

					//Candidate
					apply_filters('jobportal_register_option_payment_candidate_top', array()),
					apply_filters('jobportal_register_option_payment_candidate_main', array(
						'id' => 'jobportal_main_group',
						'type' => 'group',
						'title' => esc_html__('Candidate Settings', 'jobportal-framework'),
						'fields' => array(
							array(
								'id' => 'candidate_paid_submission_type',
								'type' => 'select',
								'title' => esc_html__('Paid Submission Type', 'jobportal-framework'),
								'subtitle' => '',
								'options' => array(
									'no' => esc_html__('Free Submit', 'jobportal-framework'),
									'candidate_per_package' => esc_html__('Pay Per Package', 'jobportal-framework')
								),
								'default' => 'no',
							),
							array(
								'id' => 'jobportal_candidate_paypal',
								'type' => 'info',
								'style' => 'info',
								'title' => esc_html__('Paypal Setting', 'jobportal-framework'),
								'required' => array('candidate_paid_submission_type', '!=', 'no'),
							),
							array(
								'id' => 'candidate_enable_paypal',
								'title' => esc_html__('Enable Paypal', 'jobportal-framework'),
								'type' => 'button_set',
								'options' => array(
									'1' => esc_html__('Enabled', 'jobportal-framework'),
									'0' => esc_html__('Disabled', 'jobportal-framework'),
								),
								'default' => '0',
								'required' => array('candidate_paid_submission_type', '!=', 'no'),
							),
							array(
								'id' => 'candidate_paypal_api',
								'type' => 'select',
								'required' => array(
									array('candidate_enable_paypal', '=', '1'),
									array('candidate_paid_submission_type', '!=', 'no')
								),
								'title' => esc_html__('Paypal Api', 'jobportal-framework'),
								'subtitle' => esc_html__('Sandbox = test API. LIVE = real payments API', 'jobportal-framework'),
								'desc' => esc_html__('Update PayPal settings according to API type selection', 'jobportal-framework'),
								'options' => array(
									'sandbox' => esc_html__('Sandbox', 'jobportal-framework'),
									'live' => esc_html__('Live', 'jobportal-framework')
								),
								'default' => 'sandbox',
							),
							array(
								'id' => 'candidate_paypal_client_id',
								'type' => 'text',
								'required' => array(
									array('candidate_enable_paypal', '=', '1'),
									array('candidate_paid_submission_type', '!=', 'no')
								),
								'title' => esc_html__('Paypal Client ID', 'jobportal-framework'),
								'subtitle' => '',
								'default' => '',
							),
							array(
								'id' => 'candidate_paypal_client_secret_key',
								'type' => 'text',
								'required' => array(
									array('candidate_enable_paypal', '=', '1'),
									array('candidate_paid_submission_type', '!=', 'no')
								),
								'title' => esc_html__('Paypal Client Secret Key', 'jobportal-framework'),
								'subtitle' => '',
								'default' => '',
							),
							array(
								'id' => 'jobportal_candidate_stripe',
								'type' => 'info',
								'style' => 'info',
								'title' => esc_html__('Stripe Setting', 'jobportal-framework'),
								'required' => array('candidate_paid_submission_type', '!=', 'no'),
							),
							array(
								'id' => 'candidate_enable_stripe',
								'title' => esc_html__('Enable Stripe', 'jobportal-framework'),
								'type' => 'button_set',
								'options' => array(
									'1' => esc_html__('Enabled', 'jobportal-framework'),
									'0' => esc_html__('Disabled', 'jobportal-framework'),
								),
								'default' => '0',
								'required' => array('candidate_paid_submission_type', '!=', 'no'),
							),
							array(
								'id' => 'candidate_stripe_secret_key',
								'type' => 'text',
								'required' => array(
									array('candidate_enable_stripe', '=', '1'),
									array('candidate_paid_submission_type', '!=', 'no')
								),
								'title' => esc_html__('Stripe Secret Key', 'jobportal-framework'),
								'subtitle' => esc_html__('Info is taken from your account at https://dashboard.stripe.com/login', 'jobportal-framework'),
								'default' => '',
							),
							array(
								'id' => 'candidate_tripe_publishable_key',
								'type' => 'text',
								'required' => array(
									array('candidate_enable_stripe', '=', '1'),
									array('candidate_paid_submission_type', '!=', 'no')
								),
								'title' => esc_html__('Stripe Publishable Key', 'jobportal-framework'),
								'subtitle' => esc_html__('Info is taken from your account at https://dashboard.stripe.com/login', 'jobportal-framework'),
								'default' => '',
							),
							array(
								'id' => 'jobportal_candidate_razor',
								'type' => 'info',
								'style' => 'info',
								'title' => esc_html__('Razor Setting', 'jobportal-framework'),
							),
							array(
								'id' => 'candidate_enable_razor',
								'title' => esc_html__('Enable Razor', 'jobportal-framework'),
								'type' => 'button_set',
								'options' => array(
									'1' => esc_html__('Enabled', 'jobportal-framework'),
									'0' => esc_html__('Disabled', 'jobportal-framework'),
								),
								'default' => '0',
							),
							array(
								'id' => 'candidate_razor_key_id',
								'type' => 'text',
								'required' => array(
									array('candidate_enable_razor', '=', '1'),
								),
								'title' => esc_html__('Razor Key ID', 'jobportal-framework'),
								'subtitle' => esc_html__('Info is taken from your account at https://dashboard.razorpay.com/', 'jobportal-framework'),
								'default' => '',
							),
							array(
								'id' => 'candidate_razor_key_secret',
								'type' => 'text',
								'required' => array(
									array('candidate_enable_razor', '=', '1'),
								),
								'title' => esc_html__('Razor Key Secret', 'jobportal-framework'),
								'subtitle' => esc_html__('Info is taken from your account at https://dashboard.razorpay.com/', 'jobportal-framework'),
								'default' => '',
							),
							array(
								'id' => 'jobportal_candidate_wire_transfer',
								'type' => 'info',
								'style' => 'info',
								'title' => esc_html__('Wire Transfer Setting', 'jobportal-framework'),
								'required' => array('candidate_paid_submission_type', '!=', 'no'),
							),
							array(
								'id' => 'candidate_enable_wire_transfer',
								'title' => esc_html__('Enable Wire Transfer', 'jobportal-framework'),
								'type' => 'button_set',
								'options' => array(
									'1' => esc_html__('Enabled', 'jobportal-framework'),
									'0' => esc_html__('Disabled', 'jobportal-framework'),
								),
								'default' => '0',
								'required' => array('candidate_paid_submission_type', '!=', 'no'),
							),
							array(
								'id' => 'candidate_wire_transfer_card_number',
								'type' => 'text',
								'required' => array(
									array('candidate_enable_wire_transfer', '=', '1'),
									array('candidate_paid_submission_type', '!=', 'no')
								),
								'title' => esc_html__('Account Number', 'jobportal-framework'),
								'subtitle' => esc_html__('Bank account number for wire transfer', 'jobportal-framework'),
								'placeholder' => esc_html__('e.g., 1234567890', 'jobportal-framework'),
								'desc' => esc_html__('Enter your bank account number exactly as it appears on your bank statement', 'jobportal-framework'),
								'default' => '',
							),
							array(
								'id' => 'candidate_wire_transfer_card_name',
								'type' => 'text',
								'required' => array(
									array('candidate_enable_wire_transfer', '=', '1'),
									array('candidate_paid_submission_type', '!=', 'no')
								),
								'title' => esc_html__('Account Holder Name', 'jobportal-framework'),
								'subtitle' => esc_html__('Name of the account holder (beneficiary name)', 'jobportal-framework'),
								'placeholder' => esc_html__('e.g., John Doe or Company Name', 'jobportal-framework'),
								'desc' => esc_html__('Enter the full name exactly as it appears on the bank account', 'jobportal-framework'),
								'default' => '',
							),
							array(
								'id' => 'candidate_wire_transfer_bank_name',
								'type' => 'text',
								'required' => array(
									array('candidate_enable_wire_transfer', '=', '1'),
									array('candidate_paid_submission_type', '!=', 'no')
								),
								'title' => esc_html__('Bank Name', 'jobportal-framework'),
								'subtitle' => esc_html__('Full name of the bank', 'jobportal-framework'),
								'placeholder' => esc_html__('e.g., Bank of America, HSBC, etc.', 'jobportal-framework'),
								'desc' => esc_html__('Enter the complete official name of your bank', 'jobportal-framework'),
								'default' => '',
							),
							array(
								'id' => 'candidate_wire_transfer_bank_address',
								'type' => 'textarea',
								'required' => array(
									array('candidate_enable_wire_transfer', '=', '1'),
									array('candidate_paid_submission_type', '!=', 'no')
								),
								'title' => esc_html__('Bank Address', 'jobportal-framework'),
								'subtitle' => esc_html__('Full address of the bank (street, city, country)', 'jobportal-framework'),
								'placeholder' => esc_html__('e.g., 123 Main Street, New York, NY 10001, USA', 'jobportal-framework'),
								'desc' => esc_html__('Enter the complete address including street, city, state/province, postal code, and country', 'jobportal-framework'),
								'default' => '',
							),
							array(
								'id' => 'candidate_wire_transfer_swift_bic',
								'type' => 'text',
								'required' => array(
									array('candidate_enable_wire_transfer', '=', '1'),
									array('candidate_paid_submission_type', '!=', 'no')
								),
								'title' => esc_html__('SWIFT/BIC Code', 'jobportal-framework'),
								'subtitle' => esc_html__('Required for international transfers (8-11 characters)', 'jobportal-framework'),
								'placeholder' => esc_html__('e.g., CHASUS33 or CHASUS33XXX', 'jobportal-framework'),
								'desc' => esc_html__('Enter your bank\'s SWIFT or BIC code. Format: 4 letters (bank) + 2 letters (country) + 2 characters (location) + optional 3 characters (branch)', 'jobportal-framework'),
								'default' => '',
							),
							array(
								'id' => 'candidate_wire_transfer_iban',
								'type' => 'text',
								'required' => array(
									array('candidate_enable_wire_transfer', '=', '1'),
									array('candidate_paid_submission_type', '!=', 'no')
								),
								'title' => esc_html__('IBAN', 'jobportal-framework'),
								'subtitle' => esc_html__('International Bank Account Number (required for EU transfers)', 'jobportal-framework'),
								'placeholder' => esc_html__('e.g., GB82 WEST 1234 5698 7654 32', 'jobportal-framework'),
								'desc' => esc_html__('Enter your IBAN (required for European transfers). Format: 2 letters (country) + 2 digits (check) + up to 30 alphanumeric characters', 'jobportal-framework'),
								'default' => '',
							),
							array(
								'id' => 'candidate_wire_transfer_routing_number',
								'type' => 'text',
								'required' => array(
									array('candidate_enable_wire_transfer', '=', '1'),
									array('candidate_paid_submission_type', '!=', 'no')
								),
								'title' => esc_html__('Routing Number / ABA', 'jobportal-framework'),
								'subtitle' => esc_html__('Required for US transfers (9 digits)', 'jobportal-framework'),
								'placeholder' => esc_html__('e.g., 021000021', 'jobportal-framework'),
								'desc' => esc_html__('Enter your 9-digit routing number (also known as ABA routing transit number) for US bank accounts', 'jobportal-framework'),
								'default' => '',
							),
							array(
								'id' => 'candidate_wire_transfer_info',
								'type' => 'editor',
								'required' => array(
									array('candidate_enable_wire_transfer', '=', '1'),
									array('candidate_paid_submission_type', '!=', 'no')
								),
								'title' => esc_html__('Wire Transfer Instructions', 'jobportal-framework'),
								'subtitle' => esc_html__('Additional instructions for customers (HTML allowed)', 'jobportal-framework'),
								'args' => array(
									'media_buttons' => false,
									'quicktags' => true,
								),
								'default' => '',
							),
							array(
								'id' => 'jobportal_candidate_woocheckout',
								'type' => 'info',
								'style' => 'info',
								'title' => esc_html__('Woocommerce Setting', 'jobportal-framework'),
								'required' => array('candidate_paid_submission_type', '!=', 'no'),
							),
							array(
								'id' => 'candidate_enable_woocheckout',
								'title' => esc_html__('Enable Woocommerce Checkout', 'jobportal-framework'),
								'type' => 'button_set',
								'subtitle' => esc_html__('Works when you activate plugin woocomerce and checkout page', 'jobportal-framework'),
								'options' => array(
									'1' => esc_html__('Enabled', 'jobportal-framework'),
									'0' => esc_html__('Disabled', 'jobportal-framework'),
								),
								'default' => '0',
								'required' => array('candidate_paid_submission_type', '!=', 'no'),
							),
						),
					)),
					apply_filters('jobportal_register_option_payment_candidate_bottom', array()),

					//Service
					apply_filters('jobportal_register_option_payment_service_top', array()),
					apply_filters(
						'jobportal_register_option_payment_service_main',
						$option_payment_service,
					),
					apply_filters('jobportal_register_option_payment_service_bottom', array())

				)
			));
		}

		/**
		 * @return mixed|void
		 */
		private function login_option()
		{
			return apply_filters('jobportal_register_option_login', array(
				'id' => 'jobportal_login_option',
				'title' => esc_html__('Login/Register', 'jobportal-framework'),
				'icon' => 'dashicons-admin-users',
				'fields' => array(

					//General Login
					apply_filters('jobportal_register_option_genera_login_page_top', array()),
					apply_filters('jobportal_register_option_genera_login_page_main', array(
						'id' => 'jobportal_login_general_group',
						'type' => 'group',
						'title' => esc_html__('General Option', 'jobportal-framework'),
						'fields' => array(
							array(
								'id' => 'enable_user_name_after_login',
								'type' => 'button_set',
								'title' => esc_html__('Enable User Name After Login', 'jobportal-framework'),
								'subtitle' => esc_html__('Enable/Disable User Name After Login', 'jobportal-framework'),
								'options' => array(
									'1' => esc_html__('On', 'jobportal-framework'),
									'0' => esc_html__('Off', 'jobportal-framework'),
								),
								'default' => '1'
							),
							array(
								'id' => 'enable_redirect_after_login',
								'type' => 'button_set',
								'title' => esc_html__('Enable Redirect After Login', 'jobportal-framework'),
								'subtitle' => esc_html__('Enable/Disable Redirect After Login', 'jobportal-framework'),
								'desc' => '',
								'options' => array(
									'1' => esc_html__('On', 'jobportal-framework'),
									'0' => esc_html__('Off', 'jobportal-framework'),
								),
								'default' => '0'
							),
							array(
								'id' => 'redirect_for_admin',
								'title' => esc_html__('Redirect For Admin', 'jobportal-framework'),
								'subtitle' => esc_html__('Select redirect page after admin login.', 'jobportal-framework'),
								'type' => 'select',
								'data' => 'page',
								'data_args' => array(
									'numberposts' => -1,
								),
								'required' => array('enable_redirect_after_login', '!=', '0'),
							),
							array(
								'id' => 'redirect_for_candidate',
								'title' => esc_html__('Redirect For Candidate', 'jobportal-framework'),
								'subtitle' => esc_html__('Select redirect page after candidate login.', 'jobportal-framework'),
								'type' => 'select',
								'data' => 'page',
								'data_args' => array(
									'numberposts' => -1,
								),
								'required' => array('enable_redirect_after_login', '!=', '0'),
							),
							array(
								'id' => 'redirect_for_employer',
								'title' => esc_html__('Redirect For Employer', 'jobportal-framework'),
								'subtitle' => esc_html__('Select redirect page after employer login.', 'jobportal-framework'),
								'type' => 'select',
								'data' => 'page',
								'data_args' => array(
									'numberposts' => -1,
								),
								'required' => array('enable_redirect_after_login', '!=', '0'),
							),
							array(
								'id' => 'enable_user_role',
								'type' => 'button_set',
								'title' => esc_html__('Enable User Role', 'jobportal-framework'),
								'subtitle' => esc_html__('Enable/Disable User Role In Form Register', 'jobportal-framework'),
								'desc' => '',
								'options' => array(
									'1' => esc_html__('On', 'jobportal-framework'),
									'0' => esc_html__('Off', 'jobportal-framework'),
								),
								'default' => '1'
							),
							array(
								'id' => 'enable_default_user_role',
								'type' => 'button_set',
								'title' => esc_html__('Enable Default User Role', 'jobportal-framework'),
								'subtitle' => esc_html__('Enable/Disable Default User Role', 'jobportal-framework'),
								'desc' => '',
								'options' => array(
									'candidate' => esc_html__('Candidate', 'jobportal-framework'),
									'employer' => esc_html__('Employer', 'jobportal-framework'),
								),
								'default' => 'candidate',
								'required' => array("enable_user_role", '!=', '1'),
							),
						),
					)),
					apply_filters('jobportal_register_option_genera_login_page_bottom', array()),

					//Verify Login
					apply_filters('jobportal_register_option_verify_login_page_top', array()),
					apply_filters('jobportal_register_option_verify_login_page_main', array(
						'id' => 'jobportal_login_verify_group',
						'type' => 'group',
						'title' => esc_html__('Verify Option', 'jobportal-framework'),
						'fields' => array(
							array(
								'id' => 'enable_status_user',
								'type' => 'button_set',
								'title' => esc_html__('Enable Verify Your Account Information', 'jobportal-framework'),
								'subtitle' => esc_html__('Enable/Disable Verify Your Account Information (Status) After Register', 'jobportal-framework'),
								'desc' => '',
								'options' => array(
									'1' => esc_html__('On', 'jobportal-framework'),
									'0' => esc_html__('Off', 'jobportal-framework'),
								),
								'default' => '0'
							),
							array(
								'id' => 'enable_captcha',
								'type' => 'button_set',
								'title' => esc_html__('Enable Google Captcha', 'jobportal-framework'),
								'subtitle' => esc_html__('Enable/Disable Google Captcha', 'jobportal-framework'),
								'desc' => '',
								'options' => array(
									'1' => esc_html__('On', 'jobportal-framework'),
									'0' => esc_html__('Off', 'jobportal-framework'),
								),
								'default' => '0'
							),
							array(
								'id' => "recaptcha_site_key",
								'title' => esc_html__('reCaptcha Site Key', 'jobportal-framework'),
								'default' => '',
								'type' => 'text',
								'required' => array("enable_captcha", '!=', '0'),
							),
							array(
								'id' => "recaptcha_secret_key",
								'title' => esc_html__('reCaptcha Secret Key', 'jobportal-framework'),
								'default' => '',
								'type' => 'text',
								'required' => array("enable_captcha", '!=', '0'),
							),
							array(
								'id' => "recaptcha_version",
								'title' => esc_html__('reCAPTCHA Version', 'jobportal-framework'),
								'subtitle' => esc_html__('Choose reCAPTCHA version (v2 checkbox or v3 invisible)', 'jobportal-framework'),
								'desc' => esc_html__('v2: Traditional checkbox "I\'m not a robot" | v3: Invisible score-based verification', 'jobportal-framework'),
								'type' => 'button_set',
								'options' => array(
									'v2' => esc_html__('v2 (Checkbox)', 'jobportal-framework'),
									'v3' => esc_html__('v3 (Invisible)', 'jobportal-framework'),
								),
								'default' => 'v2',
								'required' => array("enable_captcha", '!=', '0'),
							),
							array(
								'id' => "recaptcha_v3_score_threshold",
								'title' => esc_html__('reCAPTCHA v3 Score Threshold', 'jobportal-framework'),
								'subtitle' => esc_html__('Minimum score to pass verification (0.0 - 1.0)', 'jobportal-framework'),
								'desc' => esc_html__('Higher score = more strict. Recommended: 0.5', 'jobportal-framework'),
								'type' => 'select',
								'options' => array(
									'0.0' => '0.0 (Very Lenient)',
									'0.1' => '0.1',
									'0.2' => '0.2',
									'0.3' => '0.3',
									'0.4' => '0.4',
									'0.5' => '0.5 (Recommended)',
									'0.6' => '0.6',
									'0.7' => '0.7',
									'0.8' => '0.8',
									'0.9' => '0.9',
									'1.0' => '1.0 (Very Strict)',
								),
								'default' => '0.5',
								'required' => array(
									array("enable_captcha", '!=', '0'),
									array("recaptcha_version", '=', 'v3')
								),
							),
							array(
								'id' => "recaptcha_v3_hide_badge",
								'title' => esc_html__('Hide reCAPTCHA v3 Badge', 'jobportal-framework'),
								'subtitle' => esc_html__('Hide the reCAPTCHA badge from the page', 'jobportal-framework'),
								'desc' => esc_html__('If hidden, you must add a privacy notice to your site', 'jobportal-framework'),
								'type' => 'button_set',
								'options' => array(
									'0' => esc_html__('Show', 'jobportal-framework'),
									'1' => esc_html__('Hide', 'jobportal-framework'),
								),
								'default' => '0',
								'required' => array(
									array("enable_captcha", '!=', '0'),
									array("recaptcha_version", '=', 'v3')
								),
							),
							array(
								'id' => 'enable_verify_user',
								'type' => 'button_set',
								'title' => esc_html__('Enable Verify Email After Register', 'jobportal-framework'),
								'subtitle' => esc_html__('Enable/Disable Verify Email After Register', 'jobportal-framework'),
								'desc' => '',
								'options' => array(
									'1' => esc_html__('On', 'jobportal-framework'),
									'0' => esc_html__('Off', 'jobportal-framework'),
								),
								'default' => '0'
							),
							array(
								'id' => "verify_user_time",
								'title' => esc_html__('Verification Expiration Time', 'jobportal-framework'),
								'subtitle' => esc_html__('Enter the expiration time of the verification code (second)', 'jobportal-framework'),
								'default' => '900',
								'type' => 'text',
								'required' => array("enable_verify_user", '!=', '0'),
							),
							array(
								'id' => 'enable_identity_verification',
								'type' => 'button_set',
								'title' => esc_html__('Enable Identity Verification', 'jobportal-framework'),
								'subtitle' => esc_html__('Enable/Disable Identity Verification', 'jobportal-framework'),
								'desc' => '',
								'options' => array(
									'1' => esc_html__('On', 'jobportal-framework'),
									'0' => esc_html__('Off', 'jobportal-framework'),
								),
								'default' => '1'
							),
							array(
								'id' => 'pending_support_link',
								'type' => 'text',
								'title' => esc_html__('Pending Support Link', 'jobportal-framework'),
								'subtitle' => esc_html__('URL for Contact Support link on pending approval panel', 'jobportal-framework'),
								'default' => '',
								'required' => array('enable_status_user', '!=', '0'),
							),
							array(
								'id' => 'enable_pending_next_step',
								'type' => 'button_set',
								'title' => esc_html__('Enable Pending Next Steps', 'jobportal-framework'),
								'subtitle' => esc_html__('Enable/Disable Next steps button', 'jobportal-framework'),
								'options' => array(
									'1' => esc_html__('On', 'jobportal-framework'),
									'0' => esc_html__('Off', 'jobportal-framework'),
								),
								'default' => '0',
								'required' => array('enable_status_user', '!=', '0'),
							),
							array(
								'id' => 'pending_next_step_page',
								'type' => 'select',
								'title' => esc_html__('Pending Next Steps Page', 'jobportal-framework'),
								'subtitle' => esc_html__('Select a page to show as Next steps button', 'jobportal-framework'),
								'data' => 'page',
								'data_args' => array(
									'numberposts' => -1,
								),
								'default' => '',
								'required' => array(
									array('enable_status_user', '!=', '0'),
									array('enable_pending_next_step', '=', '1'),
								),
							),
							array(
								'id' => 'pending_estimated_review_time',
								'type' => 'text',
								'title' => esc_html__('Estimated Review Time', 'jobportal-framework'),
								'subtitle' => esc_html__('Displayed on pending approval panel (e.g., 24–48 hours)', 'jobportal-framework'),
								'default' => '24–48 hours',
								'required' => array('enable_status_user', '!=', '0'),
							),
						),
					)),
					apply_filters('jobportal_register_option_verify_login_page_bottom', array()),

					//Social Login
					apply_filters('jobportal_register_option_social_login_page_top', array()),
					apply_filters('jobportal_register_option_social_login_page_main', array(
						'id' => 'jobportal_login_social_group',
						'type' => 'group',
						'title' => esc_html__('Social Option', 'jobportal-framework'),
						'fields' => array(
							array(
								'id' => 'enable_social_login',
								'type' => 'button_set',
								'title' => esc_html__('Enable Social Login', 'jobportal-framework'),
								'subtitle' => esc_html__('Enable/Disable Social Login', 'jobportal-framework'),
								'desc' => '',
								'options' => array(
									'1' => esc_html__('On', 'jobportal-framework'),
									'0' => esc_html__('Off', 'jobportal-framework'),
								),
								'default' => '1'
							),
							array(
								'id' => "shortcode_social_login",
								'title' => esc_html__('Shortcode Social Login', 'jobportal-framework'),
								'subtitle' => esc_html__('Enter the shortcode provided by your social login plugin (e.g., WP Social Login or miniOrange Social Login).', 'jobportal-framework'),
								'default' => '',
								'desc' => sprintf(
									esc_html__('Ex: %1$s. For setup instructions, see: %2$s', 'jobportal-framework'),
									'[wp-social-login]',
									'<a href="https://uxper.gitbook.io/jobportal-job-board-wordpress-theme/faqs/how-to-set-up-social-login-google-facebook-linkedin...-on-the-jobportal-theme" target="_blank" rel="noopener">Documentation</a>'
								),
								'type' => 'text',
								'required' => array("enable_social_login", '!=', '0'),
							),
						),
					)),
					apply_filters('jobportal_register_option_social_login_page_bottom', array()),
				)
			));
		}

		/**
		 * Social network
		 * @return mixed
		 */

		private function social_network()
		{
			return apply_filters('jobportal_register_social_option', array(
				'id' => 'jobportal_social_option',
				'title' => esc_html__('Social Network', 'jobportal-framework'),
				'icon' => 'dashicons dashicons-networking',
				'fields' => array_merge(
					apply_filters('jobportal_register_social_option_top', array()),
					array(
						array(
							'id' => "enable_social_twitter",
							'type' => 'button_set',
							'title' => esc_html__('Enable Twitter', 'jobportal-framework'),
							'subtitle' => esc_html__('Enable/Disable Twitter', 'jobportal-framework'),
							'options' => array(
								'1' => esc_html__('On', 'jobportal-framework'),
								'0' => esc_html__('Off', 'jobportal-framework'),
							),
							'default' => '1',
						),
						array(
							'id' => "enable_social_linkedin",
							'type' => 'button_set',
							'title' => esc_html__('Enable Linkedin', 'jobportal-framework'),
							'subtitle' => esc_html__('Enable/Disable Linkedin', 'jobportal-framework'),
							'options' => array(
								'1' => esc_html__('On', 'jobportal-framework'),
								'0' => esc_html__('Off', 'jobportal-framework'),
							),
							'default' => '1',
						),
						array(
							'id' => "enable_social_facebook",
							'type' => 'button_set',
							'title' => esc_html__('Enable Facebook', 'jobportal-framework'),
							'subtitle' => esc_html__('Enable/Disable Facebook', 'jobportal-framework'),
							'options' => array(
								'1' => esc_html__('On', 'jobportal-framework'),
								'0' => esc_html__('Off', 'jobportal-framework'),
							),
							'default' => '1',
						),
						array(
							'id' => "enable_social_instagram",
							'type' => 'button_set',
							'title' => esc_html__('Enable Instagram', 'jobportal-framework'),
							'subtitle' => esc_html__('Enable/Disable Instagram', 'jobportal-framework'),
							'options' => array(
								'1' => esc_html__('On', 'jobportal-framework'),
								'0' => esc_html__('Off', 'jobportal-framework'),
							),
							'default' => '1',
						),
						array(
							'id' => "jobportal_social_fields",
							'type' => 'panel',
							'title' => esc_html__('Social Field', 'jobportal-framework'),
							'sort' => true,
							'panel_title' => 'label',
							'fields' => array(
								array(
									'id' => 'social_name',
									'title' => esc_html__('Social Name', 'jobportal-framework'),
									'type' => 'text',
								),
								array(
									'id' => 'social_icon',
									'title' => esc_html__('Social Icon', 'jobportal-framework'),
									'type' => 'text',
									'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
								),
							)
						)
					),
					apply_filters('jobportal_register_social_option_bottom', array())
				),
			));
		}

		/**
		 * @return mixed|void
		 */
		private function user_option()
		{
			$user_navigation_employer_show_servive = $user_navigation_employer_image_service = $user_navigation_candidate_image_service = $user_navigation_candidate_show_servive = array();

			if (jobportal_get_option('enable_post_type_service') === '1') {
				$user_navigation_employer_show_servive = array(
					'id' => 'show_employer_employer_service',
					'type' => 'button_set',
					'title' => esc_html__('Show "Services"', 'jobportal-framework'),
					'subtitle' => esc_html__('Show/Hide "Services" on navigation', 'jobportal-framework'),
					'desc' => '',
					'options' => array(
						'1' => esc_html__('On', 'jobportal-framework'),
						'0' => esc_html__('Off', 'jobportal-framework'),
					),
					'default' => '1'
				);
				$user_navigation_employer_image_service = array(
					'id' => 'image_employer_employer_service',
					'type' => 'image',
					'url' => true,
					'title' => esc_html__('Icon Services', 'jobportal-framework'),
					'subtitle' => esc_html__('Choose icon for services', 'jobportal-framework'),
					'required' => array('show_employer_employer_service', '!=', '0'),
				);
				$user_navigation_candidate_show_servive = array(
					'id' => 'show_candidate_service',
					'type' => 'button_set',
					'title' => esc_html__('Show "My Service"', 'jobportal-framework'),
					'subtitle' => esc_html__('Show/Hide "My Service" on navigation', 'jobportal-framework'),
					'desc' => '',
					'options' => array(
						'1' => esc_html__('On', 'jobportal-framework'),
						'0' => esc_html__('Off', 'jobportal-framework'),
					),
					'default' => '1'
				);
				$user_navigation_candidate_image_service = array(
					'id' => 'image_candidate_service',
					'type' => 'image',
					'url' => true,
					'title' => esc_html__('Icon My Service', 'jobportal-framework'),
					'subtitle' => esc_html__('Choose icon for My Service', 'jobportal-framework'),
					'required' => array('show_candidate_service', '!=', '0'),
				);
			}

			return apply_filters('jobportal_register_user_option', array(
				'id' => 'jobportal_user_option',
				'title' => esc_html__('User Navigation', 'jobportal-framework'),
				'icon' => 'dashicons-groups',
				'fields' => array_merge(
					apply_filters('jobportal_register_user_employer_option_top', array()),
					array(
						apply_filters(
							'jobportal_register_user_employer_option_main',
							array(
								'id' => 'jobportal_user_option_employer',
								'type' => 'group',
								'title' => esc_html__('Employer Setting', 'jobportal-framework'),
								'fields' => array(
									array(
										'id' => 'show_employer_jobs_post_your',
										'type' => 'button_set',
										'title' => esc_html__('Show "Post Your"', 'jobportal-framework'),
										'subtitle' => esc_html__('Show/Hide "Post your first job!"', 'jobportal-framework'),
										'desc' => '',
										'options' => array(
											'1' => esc_html__('On', 'jobportal-framework'),
											'0' => esc_html__('Off', 'jobportal-framework'),
										),
										'default' => '1'
									),
									array(
										'id' => 'show_employer_payout',
										'type' => 'button_set',
										'title' => esc_html__('Show "Payout"', 'jobportal-framework'),
										'subtitle' => esc_html__('Show/Hide "Payout"', 'jobportal-framework'),
										'desc' => '',
										'options' => array(
											'1' => esc_html__('On', 'jobportal-framework'),
											'0' => esc_html__('Off', 'jobportal-framework'),
										),
										'default' => '1'
									),
									array(
										'id' => 'type_icon_employer',
										'type' => 'select',
										'title' => esc_html__('Icon Type', 'jobportal-framework'),
										'default' => 'svg',
										'options' => array(
											'image' => esc_html__('Image', 'jobportal-framework'),
											'svg' => esc_html__('Svg', 'jobportal-framework')
										),
									),
									array(
										'id' => 'show_employer_dashboard',
										'type' => 'button_set',
										'title' => esc_html__('Show "Dashboard"', 'jobportal-framework'),
										'subtitle' => esc_html__('Show/Hide "Dashboard" on navigation', 'jobportal-framework'),
										'desc' => '',
										'options' => array(
											'1' => esc_html__('On', 'jobportal-framework'),
											'0' => esc_html__('Off', 'jobportal-framework'),
										),
										'default' => '1'
									),
									array(
										'id' => 'image_employer_dashboard',
										'type' => 'image',
										'url' => true,
										'title' => esc_html__('Icon Dashboard', 'jobportal-framework'),
										'subtitle' => esc_html__('Choose icon for dashboard', 'jobportal-framework'),
										'required' => array('show_employer_dashboard', '!=', '0'),
									),
									array(
										'id' => 'show_employer_jobs_dashboard',
										'type' => 'button_set',
										'title' => esc_html__('Show "Jobs"', 'jobportal-framework'),
										'subtitle' => esc_html__('Show/Hide "Jobs" on navigation', 'jobportal-framework'),
										'desc' => '',
										'options' => array(
											'1' => esc_html__('On', 'jobportal-framework'),
											'0' => esc_html__('Off', 'jobportal-framework'),
										),
										'default' => '1'
									),
									array(
										'id' => 'image_employer_jobs_dashboard',
										'type' => 'image',
										'url' => true,
										'title' => esc_html__('Icon Jobs', 'jobportal-framework'),
										'subtitle' => esc_html__('Choose icon for jobs', 'jobportal-framework'),
										'required' => array('show_employer_jobs_dashboard', '!=', '0'),
									),
									array(
										'id' => 'show_employer_applicants',
										'type' => 'button_set',
										'title' => esc_html__('Show "Applicants"', 'jobportal-framework'),
										'subtitle' => esc_html__('Show/Hide "Applicants" on navigation', 'jobportal-framework'),
										'desc' => '',
										'options' => array(
											'1' => esc_html__('On', 'jobportal-framework'),
											'0' => esc_html__('Off', 'jobportal-framework'),
										),
										'default' => '1'
									),
									array(
										'id' => 'image_employer_applicants',
										'type' => 'image',
										'url' => true,
										'title' => esc_html__('Icon Aplicants', 'jobportal-framework'),
										'subtitle' => esc_html__('Choose icon for applicants', 'jobportal-framework'),
										'required' => array('show_employer_applicants', '!=', '0'),
									),
									array(
										'id' => 'show_employer_candidates',
										'type' => 'button_set',
										'title' => esc_html__('Show "Candidates"', 'jobportal-framework'),
										'subtitle' => esc_html__('Show/Hide "Candidates" on navigation', 'jobportal-framework'),
										'desc' => '',
										'options' => array(
											'1' => esc_html__('On', 'jobportal-framework'),
											'0' => esc_html__('Off', 'jobportal-framework'),
										),
										'default' => '1'
									),
									array(
										'id' => 'image_employer_candidates',
										'type' => 'image',
										'url' => true,
										'title' => esc_html__('Icon Candidates', 'jobportal-framework'),
										'subtitle' => esc_html__('Choose icon for Candidates', 'jobportal-framework'),
										'required' => array('show_employer_candidates', '!=', '0'),
									),
									array(
										'id' => 'show_employer_user_package',
										'type' => 'button_set',
										'title' => esc_html__('Show "User Package"', 'jobportal-framework'),
										'subtitle' => esc_html__('Show/Hide "User Package" on navigation', 'jobportal-framework'),
										'desc' => '',
										'options' => array(
											'1' => esc_html__('On', 'jobportal-framework'),
											'0' => esc_html__('Off', 'jobportal-framework'),
										),
										'default' => '1'
									),
									array(
										'id' => 'image_employer_user_package',
										'type' => 'image',
										'url' => true,
										'title' => esc_html__('Icon User Package', 'jobportal-framework'),
										'subtitle' => esc_html__('Choose icon for User Package', 'jobportal-framework'),
										'required' => array('show_employer_user_package', '!=', '0'),
									),
									array(
										'id' => 'show_employer_messages',
										'type' => 'button_set',
										'title' => esc_html__('Show "Messages"', 'jobportal-framework'),
										'subtitle' => esc_html__('Show/Hide "Messages" on navigation', 'jobportal-framework'),
										'desc' => '',
										'options' => array(
											'1' => esc_html__('On', 'jobportal-framework'),
											'0' => esc_html__('Off', 'jobportal-framework'),
										),
										'default' => '1'
									),
									array(
										'id' => 'image_employer_messages',
										'type' => 'image',
										'url' => true,
										'title' => esc_html__('Icon Messages', 'jobportal-framework'),
										'subtitle' => esc_html__('Choose icon for messages', 'jobportal-framework'),
										'required' => array('show_employer_messages', '!=', '0'),
									),
									array(
										'id' => 'show_employer_meetings',
										'type' => 'button_set',
										'title' => esc_html__('Show "Meetings"', 'jobportal-framework'),
										'subtitle' => esc_html__('Show/Hide "Meetings" on navigation', 'jobportal-framework'),
										'desc' => '',
										'options' => array(
											'1' => esc_html__('On', 'jobportal-framework'),
											'0' => esc_html__('Off', 'jobportal-framework'),
										),
										'default' => '1'
									),
									array(
										'id' => 'image_employer_meetings',
										'type' => 'image',
										'url' => true,
										'title' => esc_html__('Icon Meetings', 'jobportal-framework'),
										'subtitle' => esc_html__('Choose icon for meetings', 'jobportal-framework'),
										'required' => array('show_employer_meetings', '!=', '0'),
									),
									array(
										'id' => 'show_employer_company',
										'type' => 'button_set',
										'title' => esc_html__('Show "Company"', 'jobportal-framework'),
										'subtitle' => esc_html__('Show/Hide "Company" on navigation', 'jobportal-framework'),
										'desc' => '',
										'options' => array(
											'1' => esc_html__('On', 'jobportal-framework'),
											'0' => esc_html__('Off', 'jobportal-framework'),
										),
										'default' => '1'
									),
									array(
										'id' => 'image_employer_company',
										'type' => 'image',
										'url' => true,
										'title' => esc_html__('Icon Company', 'jobportal-framework'),
										'subtitle' => esc_html__('Choose icon for company', 'jobportal-framework'),
										'required' => array('show_employer_company', '!=', '0'),
									),
									array(
										'id' => 'show_employer_settings',
										'type' => 'button_set',
										'title' => esc_html__('Show "Settings"', 'jobportal-framework'),
										'subtitle' => esc_html__('Show/Hide "Settings" on navigation', 'jobportal-framework'),
										'desc' => '',
										'options' => array(
											'1' => esc_html__('On', 'jobportal-framework'),
											'0' => esc_html__('Off', 'jobportal-framework'),
										),
										'default' => '1'
									),
									array(
										'id' => 'image_employer_settings',
										'type' => 'image',
										'url' => true,
										'title' => esc_html__('Icon Settings', 'jobportal-framework'),
										'subtitle' => esc_html__('Choose icon for settings', 'jobportal-framework'),
										'required' => array('show_employer_settings', '!=', '0'),
									),
									array(
										'id' => 'show_employer_logout',
										'type' => 'button_set',
										'title' => esc_html__('Show "Logout"', 'jobportal-framework'),
										'subtitle' => esc_html__('Show/Hide "Logout" on navigation', 'jobportal-framework'),
										'desc' => '',
										'options' => array(
											'1' => esc_html__('On', 'jobportal-framework'),
											'0' => esc_html__('Off', 'jobportal-framework'),
										),
										'default' => '1'
									),
									array(
										'id' => 'image_employer_logout',
										'type' => 'image',
										'url' => true,
										'title' => esc_html__('Icon Logout', 'jobportal-framework'),
										'subtitle' => esc_html__('Choose icon for logout', 'jobportal-framework'),
										'required' => array('show_employer_logout', '!=', '0'),
									),
									$user_navigation_employer_show_servive,
									$user_navigation_employer_image_service,
								),
							),
						),
					),
					apply_filters('jobportal_register_user_candidate_option_top', array()),
					array(
						apply_filters(
							'jobportal_register_user_candidate_option_main',
							array(
								'id' => 'jobportal_user_option_candidate',
								'type' => 'group',
								'title' => esc_html__('Candidate Setting', 'jobportal-framework'),
								'fields' => array(
									array(
										'id' => 'show_candidate_payout',
										'type' => 'button_set',
										'title' => esc_html__('Show "Payout"', 'jobportal-framework'),
										'subtitle' => esc_html__('Show/Hide "Payout"', 'jobportal-framework'),
										'desc' => '',
										'options' => array(
											'1' => esc_html__('On', 'jobportal-framework'),
											'0' => esc_html__('Off', 'jobportal-framework'),
										),
										'default' => '1'
									),
									array(
										'id' => 'type_icon_candidate',
										'type' => 'select',
										'title' => esc_html__('Icon Type', 'jobportal-framework'),
										'default' => 'svg',
										'options' => array(
											'image' => esc_html__('Image', 'jobportal-framework'),
											'svg' => esc_html__('Svg', 'jobportal-framework')
										),
									),
									array(
										'id' => 'show_candidate_dashboard',
										'type' => 'button_set',
										'title' => esc_html__('Show "Dashboard"', 'jobportal-framework'),
										'subtitle' => esc_html__('Show/Hide "Dashboard" on navigation', 'jobportal-framework'),
										'desc' => '',
										'options' => array(
											'1' => esc_html__('On', 'jobportal-framework'),
											'0' => esc_html__('Off', 'jobportal-framework'),
										),
										'default' => '1'
									),
									array(
										'id' => 'image_candidate_dashboard',
										'type' => 'image',
										'url' => true,
										'title' => esc_html__('Icon Dashboard', 'jobportal-framework'),
										'subtitle' => esc_html__('Choose icon for dashboard', 'jobportal-framework'),
										'required' => array('show_candidate_dashboard', '!=', '0'),
									),
									array(
										'id' => 'show_candidate_profile',
										'type' => 'button_set',
										'title' => esc_html__('Show "Profile"', 'jobportal-framework'),
										'subtitle' => esc_html__('Show/Hide "Profile" on navigation', 'jobportal-framework'),
										'desc' => '',
										'options' => array(
											'1' => esc_html__('On', 'jobportal-framework'),
											'0' => esc_html__('Off', 'jobportal-framework'),
										),
										'default' => '1'
									),
									array(
										'id' => 'image_candidate_profile',
										'type' => 'image',
										'url' => true,
										'title' => esc_html__('Icon Profile', 'jobportal-framework'),
										'subtitle' => esc_html__('Choose icon for profile', 'jobportal-framework'),
										'required' => array('show_candidate_profile', '!=', '0'),
									),
									array(
										'id' => 'show_my_jobs',
										'type' => 'button_set',
										'title' => esc_html__('Show "My Jobs"', 'jobportal-framework'),
										'subtitle' => esc_html__('Show/Hide "My Jobs" on navigation', 'jobportal-framework'),
										'desc' => '',
										'options' => array(
											'1' => esc_html__('On', 'jobportal-framework'),
											'0' => esc_html__('Off', 'jobportal-framework'),
										),
										'default' => '1'
									),
									array(
										'id' => 'image_my_jobs',
										'type' => 'image',
										'url' => true,
										'title' => esc_html__('Icon My Jobs', 'jobportal-framework'),
										'subtitle' => esc_html__('Choose icon for My Jobs', 'jobportal-framework'),
										'required' => array('show_my_jobs', '!=', '0'),
									),
									array(
										'id' => 'show_candidate_user_package',
										'type' => 'button_set',
										'title' => esc_html__('Show "Package"', 'jobportal-framework'),
										'subtitle' => esc_html__('Show/Hide "Package" on navigation', 'jobportal-framework'),
										'desc' => '',
										'options' => array(
											'1' => esc_html__('On', 'jobportal-framework'),
											'0' => esc_html__('Off', 'jobportal-framework'),
										),
										'default' => '1'
									),
									array(
										'id' => 'image_candidate_user_package',
										'type' => 'image',
										'url' => true,
										'title' => esc_html__('Icon User Package', 'jobportal-framework'),
										'subtitle' => esc_html__('Choose icon for User Package', 'jobportal-framework'),
										'required' => array('show_candidate_user_package', '!=', '0'),
									),
									array(
										'id' => 'show_candidate_reviews',
										'type' => 'button_set',
										'title' => esc_html__('Show "My Reviews"', 'jobportal-framework'),
										'subtitle' => esc_html__('Show/Hide "My Reviews" on navigation', 'jobportal-framework'),
										'desc' => '',
										'options' => array(
											'1' => esc_html__('On', 'jobportal-framework'),
											'0' => esc_html__('Off', 'jobportal-framework'),
										),
										'default' => '1'
									),
									array(
										'id' => 'image_candidate_reviews',
										'type' => 'image',
										'url' => true,
										'title' => esc_html__('Icon My Reviews', 'jobportal-framework'),
										'subtitle' => esc_html__('Choose icon for My Reviews', 'jobportal-framework'),
										'required' => array('show_candidate_reviews', '!=', '0'),
									),
									array(
										'id' => 'show_candidate_company',
										'type' => 'button_set',
										'title' => esc_html__('Show "My Following"', 'jobportal-framework'),
										'subtitle' => esc_html__('Show/Hide "My Following" on navigation', 'jobportal-framework'),
										'desc' => '',
										'options' => array(
											'1' => esc_html__('On', 'jobportal-framework'),
											'0' => esc_html__('Off', 'jobportal-framework'),
										),
										'default' => '1'
									),
									array(
										'id' => 'image_candidate_company',
										'type' => 'image',
										'url' => true,
										'title' => esc_html__('Icon My Following', 'jobportal-framework'),
										'subtitle' => esc_html__('Choose icon for My Following', 'jobportal-framework'),
										'required' => array('show_candidate_company', '!=', '0'),
									),
									array(
										'id' => 'show_candidate_messages',
										'type' => 'button_set',
										'title' => esc_html__('Show "Messages"', 'jobportal-framework'),
										'subtitle' => esc_html__('Show/Hide "Messages" on navigation', 'jobportal-framework'),
										'desc' => '',
										'options' => array(
											'1' => esc_html__('On', 'jobportal-framework'),
											'0' => esc_html__('Off', 'jobportal-framework'),
										),
										'default' => '1'
									),
									array(
										'id' => 'image_candidate_messages',
										'type' => 'image',
										'url' => true,
										'title' => esc_html__('Icon Messages', 'jobportal-framework'),
										'subtitle' => esc_html__('Choose icon for messages', 'jobportal-framework'),
										'required' => array('show_candidate_messages', '!=', '0'),
									),
									array(
										'id' => 'show_candidate_meetings',
										'type' => 'button_set',
										'title' => esc_html__('Show "Meetings"', 'jobportal-framework'),
										'subtitle' => esc_html__('Show/Hide "Meetings" on navigation', 'jobportal-framework'),
										'desc' => '',
										'options' => array(
											'1' => esc_html__('On', 'jobportal-framework'),
											'0' => esc_html__('Off', 'jobportal-framework'),
										),
										'default' => '1'
									),
									array(
										'id' => 'image_candidate_meetings',
										'type' => 'image',
										'url' => true,
										'title' => esc_html__('Icon Meetings', 'jobportal-framework'),
										'subtitle' => esc_html__('Choose icon for meetings', 'jobportal-framework'),
										'required' => array('show_candidate_meetings', '!=', '0'),
									),
									array(
										'id' => 'show_candidate_settings',
										'type' => 'button_set',
										'title' => esc_html__('Show "Settings"', 'jobportal-framework'),
										'subtitle' => esc_html__('Show/Hide "Settings" on navigation', 'jobportal-framework'),
										'desc' => '',
										'options' => array(
											'1' => esc_html__('On', 'jobportal-framework'),
											'0' => esc_html__('Off', 'jobportal-framework'),
										),
										'default' => '1'
									),
									array(
										'id' => 'image_candidate_settings',
										'type' => 'image',
										'url' => true,
										'title' => esc_html__('Icon Settings', 'jobportal-framework'),
										'subtitle' => esc_html__('Choose icon for settings', 'jobportal-framework'),
										'required' => array('show_candidate_settings', '!=', '0'),
									),
									array(
										'id' => 'show_candidate_logout',
										'type' => 'button_set',
										'title' => esc_html__('Show "Logout"', 'jobportal-framework'),
										'subtitle' => esc_html__('Show/Hide "Logout" on navigation', 'jobportal-framework'),
										'desc' => '',
										'options' => array(
											'1' => esc_html__('On', 'jobportal-framework'),
											'0' => esc_html__('Off', 'jobportal-framework'),
										),
										'default' => '1'
									),
									array(
										'id' => 'image_candidate_logout',
										'type' => 'image',
										'url' => true,
										'title' => esc_html__('Icon Logout', 'jobportal-framework'),
										'subtitle' => esc_html__('Choose icon for logout', 'jobportal-framework'),
										'required' => array('show_candidate_logout', '!=', '0'),
									),
									$user_navigation_candidate_show_servive,
									$user_navigation_candidate_image_service,
								),
							),
						),
					),
					apply_filters('jobportal_register_user_option_bottom', array())
				)
			));
		}

		/**
		 * Jobs page option
		 * @return mixed
		 */
		private function jobs_option()
		{
			return
				apply_filters('jobportal_register_option_listing_setting_page', array(
					'id' => 'jobportal_listing_setting_page_option',
					'title' => esc_html__('Jobs Option', 'jobportal-framework'),
					'icon' => 'dashicons-list-view',
					'fields' => array(
						//General Jobs
						apply_filters('jobportal_register_option_genera_jobs_page_top', array()),
						apply_filters('jobportal_register_option_genera_jobs_page_main', array(
							'id' => 'jobportal_main_group',
							'type' => 'group',
							'title' => esc_html__('General Jobs', 'jobportal-framework'),
							'fields' => array(
								array(
									'id' => 'enable_extend_expired_jobs',
									'type' => 'button_set',
									'title' => esc_html__('Extend Expired Jobs', 'jobportal-framework'),
									'subtitle' => esc_html__('Enable/Disable extend expired jobs', 'jobportal-framework'),
									'desc' => '',
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '0'
								),
								array(
									'id' => 'enable_apply_login',
									'type' => 'button_set',
									'title' => esc_html__('Require Login to Apply Jobs', 'jobportal-framework'),
									'subtitle' => esc_html__('Force users to login before applying (mail, phone, external)', 'jobportal-framework'),
									'desc' => '',
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '0'
								),
								array(
									'id' => "enable_job_alerts",
									'type' => 'button_set',
									'title' => esc_html__('Enable Job Alerts', 'jobportal-framework'),
									'subtitle' => esc_html__('Enable/Disable Job Alerts', 'jobportal-framework'),
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => 'jobportal_job_alerts_page_id',
									'title' => esc_html__('Job Alerts', 'jobportal-framework'),
									'type' => 'select',
									'data' => 'page',
									'data_args' => array(
										'numberposts' => -1,
									),
									'subtitle' => esc_html__('Select page for job alerts', 'jobportal-framework'),
									'required' => array("enable_job_alerts", '=', '1'),
								),
								array(
									'id' => "enable_status_urgent",
									'type' => 'button_set',
									'title' => esc_html__('Enable Status Urgent', 'jobportal-framework'),
									'subtitle' => esc_html__('Enable/Disable Status Urgent', 'jobportal-framework'),
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '1',
								),
								array(
									'id' => "number_status_urgent",
									'title' => esc_html__('Number Status Urgent', 'jobportal-framework'),
									'subtitle' => esc_html__('Enter number of days status urgent', 'jobportal-framework'),
									'default' => '3',
									'type' => 'text',
									'required' => array("enable_status_urgent", '=', '1'),
								),
								array(
									'id' => "jobs_number_days",
									'title' => esc_html__('Number of days to apply', 'jobportal-framework'),
									'subtitle' => esc_html__('Enter number of days to apply', 'jobportal-framework'),
									'default' => '30',
									'type' => 'text',
								),
							),
						)),
						apply_filters('jobportal_register_option_genera_jobs_page_bottom', array()),

						//Archive Jobs
						apply_filters('jobportal_register_option_archive_jobs_page_top', array()),
						apply_filters('jobportal_register_option_archive_jobs_page_main', array(
							'id' => 'jobportal_main_group',
							'type' => 'group',
							'title' => esc_html__('Archive Jobs', 'jobportal-framework'),
							'fields' => array(
								array(
									'id' => 'archive_jobs_layout',
									'type' => 'select',
									'title' => esc_html__('Jobs Layout', 'jobportal-framework'),
									'default' => 'layout-list',
									'options' => array(
										'layout-list' => esc_html__('Layout List', 'jobportal-framework'),
										'layout-grid' => esc_html__('Layout Grid', 'jobportal-framework'),
										'layout-full' => esc_html__('Layout Full', 'jobportal-framework')
									)
								),
								array(
									'id' => 'archive_jobs_items_amount',
									'type' => 'text',
									'title' => esc_html__('Items Amount', 'jobportal-framework'),
									'default' => 12,
									'pattern' => '[0-9]*',
								),
								array(
									'id' => 'jobs_pagination_type',
									'type' => 'select',
									'title' => esc_html__('Type Pagination', 'jobportal-framework'),
									'default' => 'number',
									'options' => array(
										'number' => esc_html__('Number', 'jobportal-framework'),
										'loadmore' => esc_html__('Load More', 'jobportal-framework')
									)
								),
								array(
									'id' => "jobs_filter_sidebar_option",
									'title' => esc_html__('Postion Filter ', 'jobportal-framework'),
									'type' => 'select',
									'options' => array(
										'filter-left' => 'Filter Left',
										'filter-right' => 'Filter Right',
										'filter-canvas' => 'Filter Canvas',
									),
									'default' => 'left',
									'required' => array(
										array("enable_jobs_show_map", '=', '0'),
										array("archive_jobs_layout", '!=', 'layout-full'),
									),
								),

								array(
									'id' => 'enable_jobs_filter_top',
									'type' => 'button_set',
									'title' => esc_html__('Show Top Filter', 'jobportal-framework'),
									'subtitle' => esc_html__('Show/Hidden Top Filter', 'jobportal-framework'),
									'desc' => '',
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '1',
								),
								array(
									'id' => 'enable_jobs_show_map',
									'type' => 'button_set',
									'title' => esc_html__('Show Maps', 'jobportal-framework'),
									'subtitle' => esc_html__('Show/Hidden Maps', 'jobportal-framework'),
									'desc' => '',
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '0',
									'required' => array(
										array("archive_jobs_layout", '!=', 'layout-full'),
									),
								),
								array(
									'id' => "jobs_map_postion",
									'title' => esc_html__('Postion Maps ', 'jobportal-framework'),
									'type' => 'select',
									'options' => array(
										'map-right' => 'Map Right',
										'map-top' => 'Map Top',
									),
									'default' => 'right',
									'required' => array(
										array("enable_jobs_show_map", '=', '1'),
										array("archive_jobs_layout", '!=', 'layout-full'),
									),
								),
								array(
									'id' => 'enable_jobs_show_des',
									'type' => 'button_set',
									'title' => esc_html__('Show Description', 'jobportal-framework'),
									'subtitle' => esc_html__('Show/Hidden Description', 'jobportal-framework'),
									'desc' => '',
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => 'enable_jobs_show_expires',
									'type' => 'button_set',
									'title' => esc_html__('Show Expired Jobs', 'jobportal-framework'),
									'subtitle' => esc_html__('Show/Hide Expired Jobs', 'jobportal-framework'),
									'desc' => '',
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '0',
								),
							),
						)),
						apply_filters('jobportal_register_option_archive_jobs_page_bottom', array()),
						//Single Jobs
						apply_filters('jobportal_register_option_single_jobs_page_top', array()),
						apply_filters('jobportal_register_option_single_jobs_page_main', array(
							'id' => 'jobs_page_main_group',
							'type' => 'group',
							'title' => esc_html__('Single Jobs', 'jobportal-framework'),
							'fields' => array(
								array(
									'id' => "enable_google_job_schema",
									'type' => 'button_set',
									'title' => esc_html__('Enable Google Job Schema', 'jobportal-framework'),
									'subtitle' => esc_html__('Enable/Disable Google Job Schema', 'jobportal-framework'),
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => "enable_job_login_to_view",
									'type' => 'button_set',
									'title' => esc_html__('Enable Job Login To View', 'jobportal-framework'),
									'subtitle' => esc_html__('Enable/Disable Job Login To View', 'jobportal-framework'),
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => 'enable_sticky_sidebar_type',
									'type' => 'button_set',
									'title' => esc_html__('Enable Sticky Sidebar', 'jobportal-framework'),
									'subtitle' => esc_html__('Enable/Disable sticky sidebar when scroll', 'jobportal-framework'),
									'desc' => '',
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '1',
								),
								array(
									'id' => 'enable_single_jobs_salary',
									'type' => 'button_set',
									'title' => esc_html__('Enable Jobs Salary', 'jobportal-framework'),
									'subtitle' => esc_html__('Enable/Disable Jobs Salary', 'jobportal-framework'),
									'desc' => '',
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '1',
								),
								array(
									'id' => 'enable_single_jobs_related',
									'type' => 'button_set',
									'title' => esc_html__('Enable Related Jobs', 'jobportal-framework'),
									'subtitle' => esc_html__('Enable/Disable Related Jobs', 'jobportal-framework'),
									'desc' => '',
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '1',
								),

								array(
									'id' => 'enable_toggle_show_more_apply',
									'type' => 'button_set',
									'title' => esc_html__('Enable Toggle Show More Apply', 'jobportal-framework'),
									'subtitle' => esc_html__('Enable/Disable Toggle Show More (Below the mobile device)', 'jobportal-framework'),
									'desc' => '',
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '1',
								),

								array(
									'id' => "enable_date_posted",
									'type' => 'button_set',
									'title' => esc_html__('Enable Date Posted', 'jobportal-framework'),
									'subtitle' => esc_html__('Enable/Disable Date Posted', 'jobportal-framework'),
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '1',
								),
								array(
									'id' => "enable_closing_date",
									'type' => 'button_set',
									'title' => esc_html__('Enable Closing Date', 'jobportal-framework'),
									'subtitle' => esc_html__('Enable/Disable Closing Date', 'jobportal-framework'),
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '1',
								),

								array(
									'id' => 'enable_single_jobs_apply',
									'type' => 'button_set',
									'title' => esc_html__('Enable Jobs Apply', 'jobportal-framework'),
									'subtitle' => esc_html__('Enable/Disable Apply', 'jobportal-framework'),
									'desc' => '',
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '1',
								),

								array(
									'id' => 'enable_required_upload_cv',
									'type' => 'button_set',
									'title' => esc_html__('Enable Required Upload Cv (*)', 'jobportal-framework'),
									'subtitle' => esc_html__('Enable/Disable Required Upload Cv', 'jobportal-framework'),
									'desc' => '',
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '1',
									'required' => array("enable_single_jobs_apply", '=', '1'),
								),

								array(
									'id' => 'show_field_jobs_apply',
									'type' => 'checkbox_list',
									'title' => esc_html__('Show Field Form Apply', 'jobportal-framework'),
									'subtitle' => esc_html__('Choose the field you want to display in the mail application form', 'jobportal-framework'),
									'options' => array(
										'position' => esc_html__('Current Position', 'jobportal-framework'),
										'categories' => esc_html__('Categories', 'jobportal-framework'),
										'date' => esc_html__('Date of Birth', 'jobportal-framework'),
										'age' => esc_html__('Age', 'jobportal-framework'),
										'gender' => esc_html__('Gender', 'jobportal-framework'),
										'languages' => esc_html__('Languages', 'jobportal-framework'),
										'qualification' => esc_html__('Qualification', 'jobportal-framework'),
										'experience' => esc_html__('Years of Experience', 'jobportal-framework'),
									),
									'value_inline' => false,
									'default' => array(),
									'required' => array("enable_single_jobs_apply", '=', '1'),
								),

								array(
									'id' => "single_jobs_style",
									'title' => esc_html__('Style Thumbnail Images', 'jobportal-framework'),
									'type' => 'select',
									'options' => array(
										'cover-img' => 'Type 1 - Cover Image',
										'no-image' => 'Type 2 - No Image',
										'large-cover-img' => 'Type 3 - Large Cover Image',
									),
								),

								array(
									'id' => 'single_jobs_image_size',
									'type' => 'text',
									'title' => esc_html__('Image Size', 'jobportal-framework'),
									'subtitle' => esc_html__('Enter image size. Alternatively enter size in pixels (Example : 770x250 (Not Include Unit, Space))', 'jobportal-framework'),
									'default' => '770x250',
								),

								array(
									'id' => 'jobs_details_order',
									'type' => 'sortable',
									'title' => esc_html__('Jobs Content Order', 'jobportal-framework'),
									'desc' => esc_html__('Drag and drop layout manager, to quickly organize your jobs content details.', 'jobportal-framework'),
									'options' => array(
										'enable_sp_head' => esc_html__('Head', 'jobportal-framework'),
										'enable_sp_insights' => esc_html__('Insights', 'jobportal-framework'),
										'enable_sp_description' => esc_html__('Description', 'jobportal-framework'),
										'enable_sp_skills' => esc_html__('Skills', 'jobportal-framework'),
										'enable_sp_gallery' => esc_html__('Gallery', 'jobportal-framework'),
										'enable_sp_video' => esc_html__('Video', 'jobportal-framework'),
										'enable_sp_map' => esc_html__('Map', 'jobportal-framework'),
									),
									'default' => array('enable_sp_skills', 'enable_sp_head', 'enable_sp_description', 'enable_sp_video', 'enable_sp_map', 'enable_sp_insights')
								),
								array(
									'id' => 'jobs_details_sidebar_order',
									'type' => 'sortable',
									'title' => esc_html__('Jobs Sidebar Order', 'jobportal-framework'),
									'desc' => esc_html__('Drag and drop layout manager, to quickly organize your jobs sidebar order.', 'jobportal-framework'),
									'options' => array(
										'enable_sidebar_sp_apply' => esc_html__('Apply', 'jobportal-framework'),
										'enable_sidebar_sp_insights' => esc_html__('Insights', 'jobportal-framework'),
										'enable_sidebar_sp_company' => esc_html__('Company', 'jobportal-framework'),
									),
									'default' => array('enable_sidebar_sp_apply', 'enable_sidebar_sp_insights', 'enable_sidebar_sp_company')
								),

								array(
									'id' => 'social_sharing',
									'type' => 'checkbox_list',
									'title' => esc_html__('Show Social Sharing', 'jobportal-framework'),
									'subtitle' => esc_html__('Choose which fields you want to show on social sharing?', 'jobportal-framework'),
									'options' => array(
										'facebook' => esc_html__('Facebook', 'jobportal-framework'),
										'twitter' => esc_html__('Twitter', 'jobportal-framework'),
										'linkedin' => esc_html__('Linkedin', 'jobportal-framework'),
										'tumblr' => esc_html__('Tumblr', 'jobportal-framework'),
										'pinterest' => esc_html__('Pinterest', 'jobportal-framework'),
										'whatapp' => esc_html__('Whatapp', 'jobportal-framework'),
									),
									'value_inline' => false,
									'default' => array('facebook', 'twitter', 'linkedin', 'tumblr', 'pinterest', 'whatapp')
								),
							),
						)),
						apply_filters('jobportal_register_option_single_jobs_page_bottom', array()),

						//Jobs Submit
						apply_filters('jobportal_jobs_option_jobs_submit_top', array()),
						apply_filters('jobportal_jobs_option_jobs_submit_main', array(
							'id' => 'jobs_submit_group',
							'title' => esc_html__('Jobs Submit', 'jobportal-framework'),
							'type' => 'group',
							'fields' => array(
								array(
									'id' => 'jobs_date_mode',
									'title' => esc_html__('Closing Date Mode', 'jobportal-framework'),
									'type' => 'button_set',
									'options' => array(
										'days' => esc_html__('Number of Days', 'jobportal-framework'),
										'closing_date' => esc_html__('Closing Date Picker', 'jobportal-framework'),
									),
									'default' => 'days',
									'desc' => esc_html__('Choose whether to use number of days or a specific closing date', 'jobportal-framework'),
								),
								array(
									'id' => 'auto_publish',
									'title' => esc_html__('Automatically publish the submitted jobs?', 'jobportal-framework'),
									'type' => 'button_set',
									'options' => array(
										'1' => esc_html__('Yes', 'jobportal-framework'),
										'0' => esc_html__('No', 'jobportal-framework'),
									),
									'default' => '1',
								),

								array(
									'id' => 'auto_publish_edited',
									'title' => esc_html__('Automatically publish the edited jobs?', 'jobportal-framework'),
									'type' => 'button_set',
									'options' => array(
										'1' => esc_html__('Yes', 'jobportal-framework'),
										'0' => esc_html__('No', 'jobportal-framework'),
									),
									'default' => '1',
								),
								array(
									'id' => 'enable_add_new_job_location',
									'title' => esc_html__('Enable Add New Location', 'jobportal-framework'),
									'type' => 'button_set',
									'options' => array(
										'1' => esc_html__('Yes', 'jobportal-framework'),
										'0' => esc_html__('No', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => 'enable_add_new_job_categories',
									'title' => esc_html__('Enable Add New Categories', 'jobportal-framework'),
									'type' => 'button_set',
									'options' => array(
										'1' => esc_html__('Yes', 'jobportal-framework'),
										'0' => esc_html__('No', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => 'section_jobs_hide_group_fields',
									'title' => esc_html__('Hide Submit Group Form Fields', 'jobportal-framework'),
									'type' => 'group',

									'fields' => array(
										array(
											'id' => 'hide_jobs_group_fields',
											'type' => 'checkbox_list',
											'title' => esc_html__('Hide Submit Form Groups', 'jobportal-framework'),
											'subtitle' => esc_html__('Choose which fields you want to hide on group field jobs?', 'jobportal-framework'),
											'options' => array(
												'general' => esc_html__('General', 'jobportal-framework'),
												'salary' => esc_html__('Salary', 'jobportal-framework'),
												'apply' => esc_html__('Apply', 'jobportal-framework'),
												'social' => esc_html__('Social network', 'jobportal-framework'),
												'company' => esc_html__('Company', 'jobportal-framework'),
												'location' => esc_html__('Location', 'jobportal-framework'),
												'thumbnail' => esc_html__('Cover Image', 'jobportal-framework'),
												'gallery' => esc_html__('Gallery', 'jobportal-framework'),
												'video' => esc_html__('Video', 'jobportal-framework'),
											),
											'value_inline' => false,
											'default' => array()
										),
										array(
											'id' => 'hide_jobs_fields',
											'type' => 'checkbox_list',
											'title' => esc_html__('Hide Submit Form Fields', 'jobportal-framework'),
											'subtitle' => esc_html__('Choose which fields you want to hide on field jobs?', 'jobportal-framework'),
											'options' => array(
												'fields_jobs_name' => esc_html__('Name', 'jobportal-framework'),
												'fields_jobs_category' => esc_html__('Category', 'jobportal-framework'),
												'fields_jobs_type' => esc_html__('Type', 'jobportal-framework'),
												'fields_jobs_skills' => esc_html__('Skills', 'jobportal-framework'),
												'fields_jobs_des' => esc_html__('Description', 'jobportal-framework'),
												'fields_jobs_career' => esc_html__('Career', 'jobportal-framework'),
												'fields_jobs_experience' => esc_html__('Experience', 'jobportal-framework'),
												'fields_jobs_qualification' => esc_html__('Qualification', 'jobportal-framework'),
												'fields_jobs_quantity' => esc_html__('Quantity', 'jobportal-framework'),
												'fields_jobs_gender' => esc_html__('Gender', 'jobportal-framework'),
												'fields_closing_days' => esc_html__('Closing', 'jobportal-framework'),
												'fields_jobs_location' => esc_html__('Location', 'jobportal-framework'),
												'fields_map' => esc_html__('Maps', 'jobportal-framework'),
											),
											'value_inline' => false,
											'default' => array()
										),
										array(
											'id' => 'hide_jobs_apply_fields',
											'type' => 'checkbox_list',
											'title' => esc_html__('Hide Job apply type', 'jobportal-framework'),
											'subtitle' => esc_html__('Choose which fields you want to hide on field job apply type?', 'jobportal-framework'),
											'options' => array(
												'fields_jobs_apply_email' => esc_html__('By Email', 'jobportal-framework'),
												'fields_jobs_apply_external' => esc_html__('External Apply', 'jobportal-framework'),
												'fields_jobs_apply_internal' => esc_html__('Internal Apply', 'jobportal-framework'),
												'fields_jobs_call_to_apply' => esc_html__('Call To Apply', 'jobportal-framework'),
											),
											'value_inline' => false,
											'default' => array()
										),
										array(
											'id' => 'hide_jobs_salary_fields',
											'type' => 'checkbox_list',
											'title' => esc_html__('Hide Job Salary', 'jobportal-framework'),
											'subtitle' => esc_html__('Choose which fields you want to hide on field job salary?', 'jobportal-framework'),
											'options' => array(
												'fields_jobs_salary_range' => esc_html__('Range', 'jobportal-framework'),
												'fields_jobs_salary_starting' => esc_html__('Starting Amount', 'jobportal-framework'),
												'fields_jobs_salary_maximum' => esc_html__('Maximum Amount', 'jobportal-framework'),
												'fields_jobs_salary_negotiable' => esc_html__('Negotiable Price', 'jobportal-framework'),
											),
											'value_inline' => false,
											'default' => array()
										),
									)
								),
							)
						)),
						apply_filters('jobportal_jobs_option_jobs_submit_bottom', array()),

						//Jobs Search
						apply_filters('jobportal_register_option_search_page_top', array()),
						apply_filters('jobportal_register_option_search_page_main', array(
							'id' => 'jobs_search_group',
							'title' => esc_html__('Search Jobs', 'jobportal-framework'),
							'type' => 'group',
							'fields' => array(
								array(
									'id' => 'enable_jobs_search_location_top',
									'type' => 'button_set',
									'title' => esc_html__('Enable Search City/Town (Top)', 'jobportal-framework'),
									'subtitle' => esc_html__('Show/Hidden Search City/Town', 'jobportal-framework'),
									'desc' => '',
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => 'enable_jobs_search_location_radius',
									'type' => 'button_set',
									'title' => esc_html__('Enable Search location radius', 'jobportal-framework'),
									'subtitle' => esc_html__('Show/Hidden search location radius', 'jobportal-framework'),
									'desc' => '',
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '1',
								),
								array(
									'id' => 'enable_jobs_search_bg',
									'type' => 'button_set',
									'title' => esc_html__('Enable Background', 'jobportal-framework'),
									'subtitle' => esc_html__('Show/Hidden Background', 'jobportal-framework'),
									'desc' => '',
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => "jobs_search_color",
									'title' => esc_html__('Color', 'jobportal-framework'),
									'type' => 'color',
									'col' => '12',
									'default' => '',
									'required' => array(
										array("enable_jobs_search_bg", '=', '1'),
									),
								),
								array(
									'id' => "jobs_search_image",
									'title' => esc_html__('Image', 'jobportal-framework'),
									'type' => 'image',
									'default' => '',
									'col' => '12',
									'required' => array(
										array("enable_jobs_search_bg", '=', '1'),
									),
								),
								array(
									'id' => 'jobs_search_field',
									'title' => esc_html__('Search Fields', 'jobportal-framework'),
									'type' => 'sorter',
									'default' => array(
										'top' => array(
											'jobs-categories' => esc_html__('Categories', 'jobportal-framework'),
										),
										'sidebar' => array(
											'jobs-type' => esc_html__('Type', 'jobportal-framework'),
											'jobs-salary' => esc_html__('Salary', 'jobportal-framework'),
											'jobs-career' => esc_html__('Career', 'jobportal-framework'),
											'jobs-experience' => esc_html__('Experience', 'jobportal-framework'),
											'jobs-posting-date' => esc_html__('Last Updated', 'jobportal-framework'),
										),
										'disable' => array(
											'jobs-skills' => esc_html__('Skills', 'jobportal-framework'),
											'jobs-location' => esc_html__('Locations', 'jobportal-framework'),
											'jobs-gender' => esc_html__('Gender', 'jobportal-framework'),
											'jobs-qualification' => esc_html__('Qualification', 'jobportal-framework'),
											'jobs-custom-fields' => esc_html__('Custom Fields', 'jobportal-framework'),
										)
									),
								),
								array(
									'id' => 'jobs_search_fields_jobs-categories',
									'title' => esc_html__('Icon Categories', 'jobportal-framework'),
									'type' => 'text',
									'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
								),
								array(
									'id' => 'jobs_search_fields_jobs-type',
									'title' => esc_html__('Icon Type', 'jobportal-framework'),
									'type' => 'text',
									'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
								),
								array(
									'id' => 'jobs_search_fields_jobs-career',
									'title' => esc_html__('Icon Career', 'jobportal-framework'),
									'type' => 'text',
									'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
								),
								array(
									'id' => 'jobs_search_fields_jobs-experience',
									'title' => esc_html__('Icon Experience', 'jobportal-framework'),
									'type' => 'text',
									'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
								),
								array(
									'id' => 'jobs_search_fields_jobs-gender',
									'title' => esc_html__('Icon Gender', 'jobportal-framework'),
									'type' => 'text',
									'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
								),
								array(
									'id' => 'jobs_search_fields_location',
									'title' => esc_html__('Icon City', 'jobportal-framework'),
									'type' => 'text',
									'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
								),
								array(
									'id' => 'jobs_search_fields_state',
									'title' => esc_html__('Icon State', 'jobportal-framework'),
									'type' => 'text',
									'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
								),
								array(
									'id' => 'jobs_search_fields_country',
									'title' => esc_html__('Icon Country', 'jobportal-framework'),
									'type' => 'text',
									'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
								),
							)
						)),
						apply_filters('jobportal_register_option_search_page_bottom', array()),
					),
				));
		}


		/**
		 * Company page option
		 * @return mixed
		 */
		private function company_option()
		{
			return
				apply_filters('jobportal_register_company_option_listing_setting_page', array(
					'id' => 'jobportal_listing_company_setting_page_option',
					'title' => esc_html__('Companies Option', 'jobportal-framework'),
					'icon' => 'dashicons-awards',
					'fields' => array(
						//Archive Company
						apply_filters('jobportal_register_option_archive_company_page_top', array()),
						apply_filters('jobportal_register_option_archive_company_page_main', array(
							'id' => 'jobportal_main_group',
							'type' => 'group',
							'title' => esc_html__('Archive Company', 'jobportal-framework'),
							'fields' => array(
								array(
									'id' => 'archive_company_layout',
									'type' => 'select',
									'title' => esc_html__('Company Layout', 'jobportal-framework'),
									'default' => 'layout-list',
									'options' => array(
										'layout-list' => esc_html__('Layout List', 'jobportal-framework'),
										'layout-grid' => esc_html__('Layout Grid', 'jobportal-framework'),
									)
								),
								array(
									'id' => 'archive_company_thumbnail_size',
									'type' => 'text',
									'title' => esc_html__('Thumbnail Size', 'jobportal-framework'),
									'subtitle' => esc_html__('Enter image size. Alternatively enter size in pixels. Example : 330x180 (Not Include Unit, Space)', 'jobportal-framework'),
									'default' => '576x327',
								),
								array(
									'id' => 'archive_company_items_amount',
									'type' => 'text',
									'title' => esc_html__('Items Amount', 'jobportal-framework'),
									'default' => 12,
									'pattern' => '[0-9]*',
								),
								array(
									'id' => 'company_pagination_type',
									'type' => 'select',
									'title' => esc_html__('Type Pagination', 'jobportal-framework'),
									'default' => 'number',
									'options' => array(
										'number' => esc_html__('Number', 'jobportal-framework'),
										'loadmore' => esc_html__('Load More', 'jobportal-framework')
									)
								),
								array(
									'id' => "company_filter_sidebar_option",
									'title' => esc_html__('Postion Filter ', 'jobportal-framework'),
									'type' => 'select',
									'options' => array(
										'filter-left' => 'Filter Left',
										'filter-right' => 'Filter Right',
									),
									'default' => 'left',
								),
								array(
									'id' => 'enable_company_filter_top',
									'type' => 'button_set',
									'title' => esc_html__('Show Top Filter', 'jobportal-framework'),
									'subtitle' => esc_html__('Show/Hidden Top Filter', 'jobportal-framework'),
									'desc' => '',
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '1',
								),
								array(
									'id' => 'enable_company_show_map',
									'type' => 'button_set',
									'title' => esc_html__('Show Maps', 'jobportal-framework'),
									'subtitle' => esc_html__('Show/Hidden Maps', 'jobportal-framework'),
									'desc' => '',
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => "company_map_postion",
									'title' => esc_html__('Postion Maps ', 'jobportal-framework'),
									'type' => 'select',
									'options' => array(
										'map-right' => 'Map Right',
										'map-top' => 'Map Top',
									),
									'default' => 'right',
									'required' => array(
										array("enable_company_show_map", '=', '1'),
									),
								),
								array(
									'id' => 'enable_company_show_des',
									'type' => 'button_set',
									'title' => esc_html__('Show Description', 'jobportal-framework'),
									'subtitle' => esc_html__('Show/Hidden Description', 'jobportal-framework'),
									'desc' => '',
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '1',
								),
							),
						)),
						apply_filters('jobportal_register_option_archive_company_page_bottom', array()),

						//Single Company
						apply_filters('jobportal_register_option_single_company_page_top', array()),
						apply_filters('jobportal_register_option_single_company_page_main', array(
							'id' => 'company_page_main_group',
							'type' => 'group',
							'title' => esc_html__('Single Company', 'jobportal-framework'),
							'fields' => array(
								array(
									'id' => "enable_google_company_schema",
									'type' => 'button_set',
									'title' => esc_html__('Enable Google Company Schema', 'jobportal-framework'),
									'subtitle' => esc_html__('Enable/Disable Google Company Schema (Organization)', 'jobportal-framework'),
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '1',
								),
								array(
									'id' => "enable_company_login_to_view",
									'type' => 'button_set',
									'title' => esc_html__('Enable Company Login To View', 'jobportal-framework'),
									'subtitle' => esc_html__('Enable/Disable Company Login To View', 'jobportal-framework'),
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => 'enable_sticky_company_sidebar_type',
									'type' => 'button_set',
									'title' => esc_html__('Enable Sticky Sidebar', 'jobportal-framework'),
									'subtitle' => esc_html__('Enable/Disable sticky sidebar when scroll', 'jobportal-framework'),
									'desc' => '',
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '1',
								),
								array(
									'id' => 'enable_single_company_related',
									'type' => 'button_set',
									'title' => esc_html__('Enable Related Company', 'jobportal-framework'),
									'subtitle' => esc_html__('Enable/Disable Related Company', 'jobportal-framework'),
									'desc' => '',
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '1',
								),

								array(
									'id' => 'enable_single_company_review',
									'type' => 'button_set',
									'title' => esc_html__('Enable Company Review', 'jobportal-framework'),
									'subtitle' => esc_html__('Enable/Disable Company Review', 'jobportal-framework'),
									'desc' => '',
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '1',
								),

								array(
									'id' => "single_company_style",
									'title' => esc_html__('Style Thumbnail Images', 'jobportal-framework'),
									'type' => 'select',
									'options' => array(
										'cover-img' => 'Type 1 - Cover Image',
										'no-image' => 'Type 2 - No Cover Image',
										'large-cover-img' => 'Type 3 - Large Cover Image',
									),
								),

								array(
									'id' => 'single_company_image_size',
									'type' => 'text',
									'title' => esc_html__('Image Size', 'jobportal-framework'),
									'subtitle' => esc_html__('Enter image size. Alternatively enter size in pixels. Example : 330x180 (Not Include Unit, Space)', 'jobportal-framework'),
									'default' => '',
								),

								array(
									'id' => 'company_details_order',
									'type' => 'sortable',
									'title' => esc_html__('Company Content Order', 'jobportal-framework'),
									'desc' => esc_html__('Drag and drop layout manager, to quickly organize your company content details.', 'jobportal-framework'),
									'options' => array(
										'enable_sp_head' => esc_html__('Head', 'jobportal-framework'),
										'enable_sp_overview' => esc_html__('Overview', 'jobportal-framework'),
										'enable_sp_gallery' => esc_html__('Gallery', 'jobportal-framework'),
										'enable_sp_video' => esc_html__('Video', 'jobportal-framework'),
									),
									'default' => array('enable_sp_company_head', 'enable_sp_company_overview', 'enable_sp_company_gallery', 'enable_sp_video')
								),
								array(
									'id' => 'company_details_sidebar_order',
									'type' => 'sortable',
									'title' => esc_html__('Company Sidebar Order', 'jobportal-framework'),
									'desc' => esc_html__('Drag and drop layout manager, to quickly organize your company sidebar order.', 'jobportal-framework'),
									'options' => array(
										'enable_sidebar_sp_info' => esc_html__('Information', 'jobportal-framework'),
										'enable_sidebar_sp_location' => esc_html__('Location', 'jobportal-framework'),
									),
									'default' => array('enable_sidebar_sp_info', 'enable_sidebar_sp_location'),
								),
							),
						)),
						apply_filters('jobportal_register_option_single_company_page_bottom', array()),

						//Company Submit
						apply_filters('jobportal_option_company_submit_top', array()),
						apply_filters('jobportal_option_company_submit_main', array(
							'id' => 'company_submit_group',
							'title' => esc_html__('Company Submit', 'jobportal-framework'),
							'type' => 'group',
							'fields' => array(
								array(
									'id' => 'company_auto_publish',
									'title' => esc_html__('Automatically publish the submitted Company?', 'jobportal-framework'),
									'type' => 'button_set',
									'options' => array(
										'1' => esc_html__('Yes', 'jobportal-framework'),
										'0' => esc_html__('No', 'jobportal-framework'),
									),
									'default' => '1',
								),

								array(
									'id' => 'company_auto_publish_edited',
									'title' => esc_html__('Automatically publish the edited Company?', 'jobportal-framework'),
									'type' => 'button_set',
									'options' => array(
										'1' => esc_html__('Yes', 'jobportal-framework'),
										'0' => esc_html__('No', 'jobportal-framework'),
									),
									'default' => '1',
								),

								array(
									'id' => 'enable_add_new_company_location',
									'title' => esc_html__('Enable Add New Location', 'jobportal-framework'),
									'type' => 'button_set',
									'options' => array(
										'1' => esc_html__('Yes', 'jobportal-framework'),
										'0' => esc_html__('No', 'jobportal-framework'),
									),
									'default' => '0',
								),

								array(
									'id' => 'enable_add_new_company_categories',
									'title' => esc_html__('Enable Add New Categories', 'jobportal-framework'),
									'type' => 'button_set',
									'options' => array(
										'1' => esc_html__('Yes', 'jobportal-framework'),
										'0' => esc_html__('No', 'jobportal-framework'),
									),
									'default' => '0',
								),

								array(
									'id' => 'value_founded_min',
									'type' => 'text',
									'title' => esc_html__('Founded Date Min', 'jobportal-framework'),
									'subtitle' => esc_html__('Enter values founded date min', 'jobportal-framework'),
									'default' => '2010',
								),
								array(
									'id' => 'section_company_hide_group_fields',
									'title' => esc_html__('Hide Submit Group Form Fields', 'jobportal-framework'),
									'type' => 'group',

									'fields' => array(
										array(
											'id' => 'hide_company_group_fields',
											'type' => 'checkbox_list',
											'title' => esc_html__('Hide Submit Form Groups', 'jobportal-framework'),
											'subtitle' => esc_html__('Choose which fields you want to hide on group fields company?', 'jobportal-framework'),
											'options' => array(
												'general' => esc_html__('General', 'jobportal-framework'),
												'media' => esc_html__('Media', 'jobportal-framework'),
												'social' => esc_html__('Social network', 'jobportal-framework'),
												'location' => esc_html__('Location', 'jobportal-framework'),
												'gallery' => esc_html__('Gallery', 'jobportal-framework'),
												'video' => esc_html__('Video', 'jobportal-framework'),
											),
											'value_inline' => false,
											'default' => array()
										),
										array(
											'id' => 'hide_company_fields',
											'type' => 'checkbox_list',
											'title' => esc_html__('Hide Submit Form Fields', 'jobportal-framework'),
											'subtitle' => esc_html__('Choose which fields you want to hide on fields company?', 'jobportal-framework'),
											'options' => array(
												'fields_company_name' => esc_html__('Name', 'jobportal-framework'),
												'fields_company_category' => esc_html__('Category', 'jobportal-framework'),
												'fields_company_url' => esc_html__('Url', 'jobportal-framework'),
												'fields_company_about' => esc_html__('About', 'jobportal-framework'),
												'fields_company_website' => esc_html__('Website', 'jobportal-framework'),
												'fields_company_phone' => esc_html__('Phone', 'jobportal-framework'),
												'fields_company_email' => esc_html__('Email', 'jobportal-framework'),
												'fields_company_founded' => esc_html__('Founded', 'jobportal-framework'),
												'fields_company_size' => esc_html__('Size', 'jobportal-framework'),
												'fields_closing_logo' => esc_html__('Logo', 'jobportal-framework'),
												'fields_company_thumbnail' => esc_html__('Thumbnail', 'jobportal-framework'),
												'fields_company_location' => esc_html__('Location', 'jobportal-framework'),
												'fields_company_map' => esc_html__('Maps', 'jobportal-framework'),
											),
											'value_inline' => false,
											'default' => array()
										),
									)
								),
							)
						)),
						apply_filters('jobportal_option_company_submit_bottom', array()),

						//Company Search
						apply_filters('jobportal_register_option_search_company_page_top', array()),
						apply_filters('jobportal_register_option_search_company_page_main', array(
							'id' => 'company_search_group',
							'title' => esc_html__('Search Company', 'jobportal-framework'),
							'type' => 'group',
							'fields' => array(
								array(
									'id' => 'enable_company_search_location_top',
									'type' => 'button_set',
									'title' => esc_html__('Enable Search City/Town (Top)', 'jobportal-framework'),
									'subtitle' => esc_html__('Show/Hidden Search City/Town', 'jobportal-framework'),
									'desc' => '',
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => 'enable_company_search_location_radius',
									'type' => 'button_set',
									'title' => esc_html__('Enable Search location radius', 'jobportal-framework'),
									'subtitle' => esc_html__('Show/Hidden search location radius', 'jobportal-framework'),
									'desc' => '',
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '1',
								),
								array(
									'id' => 'enable_company_search_bg',
									'type' => 'button_set',
									'title' => esc_html__('Enable Background', 'jobportal-framework'),
									'subtitle' => esc_html__('Show/Hidden Background', 'jobportal-framework'),
									'desc' => '',
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => "company_search_color",
									'title' => esc_html__('Color', 'jobportal-framework'),
									'type' => 'color',
									'col' => '12',
									'default' => '',
									'required' => array(
										array("enable_company_search_bg", '=', '1'),
									),
								),
								array(
									'id' => "company_search_image",
									'title' => esc_html__('Image', 'jobportal-framework'),
									'type' => 'image',
									'default' => '',
									'col' => '12',
									'required' => array(
										array("enable_company_search_bg", '=', '1'),
									),
								),
								array(
									'id' => 'company_search_fields',
									'title' => esc_html__('Search Fields', 'jobportal-framework'),
									'type' => 'sorter',
									'default' => array(
										'top' => array(
											'company-categories' => esc_html__('Categories', 'jobportal-framework'),
										),
										'sidebar' => array(
											'company-rating' => esc_html__('Rating', 'jobportal-framework'),
											'company-founded' => esc_html__('Founded', 'jobportal-framework'),
											'company-size' => esc_html__('Size', 'jobportal-framework'),
										),
										'disable' => array(
											'company-location' => esc_html__('Location', 'jobportal-framework'),
										)
									),
								),
								array(
									'id' => 'company_search_fields_company-categories',
									'title' => esc_html__('Icon Categories', 'jobportal-framework'),
									'type' => 'text',
									'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
								),
								array(
									'id' => 'company_search_fields_company-rating',
									'title' => esc_html__('Icon Rating', 'jobportal-framework'),
									'type' => 'text',
									'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
								),
								array(
									'id' => 'company_search_fields_company-founded',
									'title' => esc_html__('Icon Founded', 'jobportal-framework'),
									'type' => 'text',
									'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
								),
								array(
									'id' => 'company_search_fields_company-size',
									'title' => esc_html__('Icon Size', 'jobportal-framework'),
									'type' => 'text',
									'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
								),
								array(
									'id' => 'company_search_fields_location',
									'title' => esc_html__('Icon City', 'jobportal-framework'),
									'type' => 'text',
									'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
								),
								array(
									'id' => 'company_search_fields_state',
									'title' => esc_html__('Icon State', 'jobportal-framework'),
									'type' => 'text',
									'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
								),
								array(
									'id' => 'company_search_fields_country',
									'title' => esc_html__('Icon Country', 'jobportal-framework'),
									'type' => 'text',
									'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
								),
							)
						)),
						apply_filters('jobportal_register_option_search_company_page_bottom', array()),
						//Company Package
						apply_filters('jobportal_register_option_company_package_page_top', array()),
						apply_filters('jobportal_register_option_company_package_page_main', array(
							'id' => 'company_package_group',
							'title' => esc_html__('Company Package', 'jobportal-framework'),
							'type' => 'group',
							'fields' => array(
								array(
									'id' => "enable_company_package_candidate_follow",
									'title' => esc_html__('Enable/Disable Candidate Follow', 'jobportal-framework'),
									'type' => 'button_set',
									'options' => array(
										'1' => esc_html__('Yes', 'jobportal-framework'),
										'0' => esc_html__('No', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => "enable_company_package_download_cv",
									'title' => esc_html__('Enable/Disable Download Cv', 'jobportal-framework'),
									'type' => 'button_set',
									'options' => array(
										'1' => esc_html__('Yes', 'jobportal-framework'),
										'0' => esc_html__('No', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => "enable_company_package_invite",
									'title' => esc_html__('Enable/Disable Invite', 'jobportal-framework'),
									'type' => 'button_set',
									'options' => array(
										'1' => esc_html__('Yes', 'jobportal-framework'),
										'0' => esc_html__('No', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => "enable_company_package_send_message",
									'title' => esc_html__('Enable/Disable Send Message', 'jobportal-framework'),
									'type' => 'button_set',
									'options' => array(
										'1' => esc_html__('Yes', 'jobportal-framework'),
										'0' => esc_html__('No', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => "enable_company_package_print",
									'title' => esc_html__('Enable/Disable Print', 'jobportal-framework'),
									'type' => 'button_set',
									'options' => array(
										'1' => esc_html__('Yes', 'jobportal-framework'),
										'0' => esc_html__('No', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => "enable_company_package_review_and_commnent",
									'title' => esc_html__('Enable/Disable Review And Comment', 'jobportal-framework'),
									'type' => 'button_set',
									'options' => array(
										'1' => esc_html__('Yes', 'jobportal-framework'),
										'0' => esc_html__('No', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => "enable_company_package_info",
									'title' => esc_html__('Enable/Disable profile information', 'jobportal-framework'),
									'type' => 'button_set',
									'options' => array(
										'1' => esc_html__('Yes', 'jobportal-framework'),
										'0' => esc_html__('No', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => 'hide_company_candidate_info_fields',
									'type' => 'checkbox_list',
									'title' => esc_html__('Hide Field candidate information', 'jobportal-framework'),
									'subtitle' => esc_html__('Choose which fields you want to hide on candidate information', 'jobportal-framework'),
									'options' => array(
										'salary' => esc_html__('Offered Salary', 'jobportal-framework'),
										'time' => esc_html__('Experience time', 'jobportal-framework'),
										'languages' => esc_html__('Languages', 'jobportal-framework'),
										'gender' => esc_html__('Gender', 'jobportal-framework'),
										'qualification' => esc_html__('Qualification', 'jobportal-framework'),
										'age' => esc_html__('Age', 'jobportal-framework'),
										'phone' => esc_html__('Phone', 'jobportal-framework'),
										'email' => esc_html__('Email', 'jobportal-framework'),
										'social' => esc_html__('Social', 'jobportal-framework'),
									),
									'value_inline' => false,
									'default' => array('phone', 'email', 'social'),
									'required' => array("enable_company_package_info", '=', '1'),
								),
							)
						)),
						apply_filters('jobportal_register_option_company_package_page_bottom', array())
					),
				));
		}

		/**
		 * Candidate page option
		 * @return mixed
		 */
		private function candidate_option()
		{
			$enable_single_candidate_service = array();
			if (jobportal_get_option('enable_post_type_service') === '1') {
				$enable_single_candidate_service = array(
					'id' => 'enable_single_candidate_service',
					'type' => 'button_set',
					'title' => esc_html__('Enable Services', 'jobportal-framework'),
					'subtitle' => esc_html__('Enable/Disable Services', 'jobportal-framework'),
					'desc' => '',
					'options' => array(
						'1' => esc_html__('On', 'jobportal-framework'),
						'0' => esc_html__('Off', 'jobportal-framework'),
					),
					'default' => '1',
				);
			}

			return
				apply_filters('jobportal_register_candidate_option_listing_setting_page', array(
					'id' => 'jobportal_listing_candidate_setting_page_option',
					'title' => esc_html__('Candidates Option', 'jobportal-framework'),
					'icon' => 'dashicons-businessperson',
					'fields' => array(
						//Archive Candidate
						apply_filters('jobportal_register_option_archive_candidate_page_top', array()),
						apply_filters('jobportal_register_option_archive_candidate_page_main', array(
							'id' => 'jobportal_main_group',
							'type' => 'group',
							'title' => esc_html__('Archive Candidate', 'jobportal-framework'),
							'fields' => array(
								array(
									'id' => 'archive_candidate_layout',
									'type' => 'select',
									'title' => esc_html__('Candidate Layout', 'jobportal-framework'),
									'default' => 'layout-list',
									'options' => array(
										'layout-list' => esc_html__('Layout List', 'jobportal-framework'),
										'layout-grid' => esc_html__('Layout Grid', 'jobportal-framework')
									)
								),
								array(
									'id' => 'archive_candidate_items_amount',
									'type' => 'text',
									'title' => esc_html__('Items Amount', 'jobportal-framework'),
									'default' => 12,
									'pattern' => '[0-9]*',
								),
								array(
									'id' => 'candidate_pagination_type',
									'type' => 'select',
									'title' => esc_html__('Type Pagination', 'jobportal-framework'),
									'default' => 'number',
									'options' => array(
										'number' => esc_html__('Number', 'jobportal-framework'),
										'loadmore' => esc_html__('Load More', 'jobportal-framework')
									)
								),
								array(
									'id' => "candidate_filter_sidebar_option",
									'title' => esc_html__('Postion Filter ', 'jobportal-framework'),
									'type' => 'select',
									'options' => array(
										'filter-left' => 'Filter Left',
										'filter-right' => 'Filter Right',
									),
									'default' => 'left',
								),
								array(
									'id' => 'enable_candidate_filter_top',
									'type' => 'button_set',
									'title' => esc_html__('Show Top Filter', 'jobportal-framework'),
									'subtitle' => esc_html__('Show/Hidden Top Filter', 'jobportal-framework'),
									'desc' => '',
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '1',
								),
								array(
									'id' => 'enable_candidate_show_map',
									'type' => 'button_set',
									'title' => esc_html__('Show Maps', 'jobportal-framework'),
									'subtitle' => esc_html__('Show/Hidden Maps', 'jobportal-framework'),
									'desc' => '',
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => "candidate_map_postion",
									'title' => esc_html__('Postion Maps ', 'jobportal-framework'),
									'type' => 'select',
									'options' => array(
										'map-right' => 'Map Right',
										'map-top' => 'Map Top',
									),
									'default' => 'right',
									'required' => array(
										array("enable_candidate_show_map", '=', '1'),
									),
								),
								array(
									'id' => 'enable_candidate_show_des',
									'type' => 'button_set',
									'title' => esc_html__('Show Description', 'jobportal-framework'),
									'subtitle' => esc_html__('Show/Hidden Description', 'jobportal-framework'),
									'desc' => '',
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '1',
								),
							),
						)),
						apply_filters('jobportal_register_option_archive_candidate_page_bottom', array()),

						//Single Candidate
						apply_filters('jobportal_register_option_single_candidate_page_top', array()),
						apply_filters('jobportal_register_option_single_candidate_page_main', array(
							'id' => 'candidate_page_main_group',
							'type' => 'group',
							'title' => esc_html__('Single Candidate', 'jobportal-framework'),
							'fields' => array(
								array(
									'id' => "enable_google_candidate_schema",
									'type' => 'button_set',
									'title' => esc_html__('Enable Google Candidate Schema', 'jobportal-framework'),
									'subtitle' => esc_html__('Enable/Disable Google Candidate Schema (Person)', 'jobportal-framework'),
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '1',
								),
								array(
									'id' => "enable_candidate_login_to_view",
									'type' => 'button_set',
									'title' => esc_html__('Enable Candidate Login To View', 'jobportal-framework'),
									'subtitle' => esc_html__('Enable/Disable Candidate Login To View', 'jobportal-framework'),
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => 'enable_sticky_candidate_sidebar_type',
									'type' => 'button_set',
									'title' => esc_html__('Enable Sticky Sidebar', 'jobportal-framework'),
									'subtitle' => esc_html__('Enable/Disable sticky sidebar when scroll', 'jobportal-framework'),
									'desc' => '',
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '1',
								),

								$enable_single_candidate_service,

								array(
									'id' => 'enable_single_candidate_review',
									'type' => 'button_set',
									'title' => esc_html__('Enable Candidate Review', 'jobportal-framework'),
									'subtitle' => esc_html__('Enable/Disable Candidate Review', 'jobportal-framework'),
									'desc' => '',
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '1',
								),

								array(
									'id' => 'enable_single_candidate_download_cv',
									'type' => 'button_set',
									'title' => esc_html__('Enable Candidate Download CV', 'jobportal-framework'),
									'subtitle' => esc_html__('Enable/Disable Candidate Download CV', 'jobportal-framework'),
									'desc' => '',
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '0',
								),

								array(
									'id' => "single_candidate_style",
									'title' => esc_html__('Style Thumbnail Images', 'jobportal-framework'),
									'type' => 'select',
									'options' => array(
										'cover-img' => 'Type 1 - Cover Image',
										'no-image' => 'Type 2 - No Cover Image',
										'large-cover-img' => 'Type 3 - Large Cover Image',
									),
								),

								array(
									'id' => 'single_candidate_image_size',
									'type' => 'text',
									'title' => esc_html__('Image Size', 'jobportal-framework'),
									'subtitle' => esc_html__('Enter image size. Alternatively enter size in pixels. Example : 330x180 (Not Include Unit, Space)', 'jobportal-framework'),
									'default' => '',
								),

								array(
									'id' => 'candidates_details_order',
									'type' => 'sortable',
									'title' => esc_html__('Candidate Content Order', 'jobportal-framework'),
									'desc' => esc_html__('Drag and drop layout manager, to quickly organize your candidate content details.', 'jobportal-framework'),
									'options' => array(
										'enable_sp_video' => esc_html__('Video', 'jobportal-framework'),
										'enable_sp_skills' => esc_html__('Skills', 'jobportal-framework'),
										'enable_sp_experience' => esc_html__('Experience', 'jobportal-framework'),
										'enable_sp_education' => esc_html__('Education', 'jobportal-framework'),
										'enable_sp_projects' => esc_html__('Projects', 'jobportal-framework'),
										'enable_sp_awards' => esc_html__('Awards', 'jobportal-framework'),
									),
									'default' => array('enable_sp_head', 'enable_sp_video', 'enable_sp_skills', 'enable_sp_experience', 'enable_sp_education', 'enable_sp_projects', 'enable_sp_awards')
								),

								array(
									'id' => 'candidate_details_sidebar_order',
									'type' => 'sortable',
									'title' => esc_html__('Candidate Sidebar Order', 'jobportal-framework'),
									'desc' => esc_html__('Drag and drop layout manager, to quickly organize your candidate sidebar order.', 'jobportal-framework'),
									'options' => array(
										'enable_sidebar_sp_info' => esc_html__('Information', 'jobportal-framework'),
										'enable_sidebar_sp_location' => esc_html__('Location', 'jobportal-framework'),
									),
									'default' => array('enable_sidebar_sp_info', 'enable_sidebar_sp_location'),
								),
								array(
									'id' => 'candidate_details_prints',
									'type' => 'sortable',
									'title' => esc_html__('Candidate Print', 'jobportal-framework'),
									'desc' => esc_html__('Drag and drop layout manager, to quickly organize your candidate print.', 'jobportal-framework'),
									'options' => array(
										'enable_print_sp_info' => esc_html__('Info', 'jobportal-framework'),
										'enable_print_sp_skills' => esc_html__('Skills', 'jobportal-framework'),
										'enable_print_sp_awards' => esc_html__('Awards', 'jobportal-framework'),
										'enable_print_sp_education' => esc_html__('Education', 'jobportal-framework'),
										'enable_print_sp_experience' => esc_html__('Experience', 'jobportal-framework'),
										'enable_print_sp_projects' => esc_html__('Projects', 'jobportal-framework'),
									),
									'default' => array('enable_print_sp_info', 'enable_print_sp_skills', 'enable_print_sp_awards', 'enable_print_sp_education', 'enable_print_sp_experience', 'enable_print_sp_projects'),
								),
							),
						)),
						apply_filters('jobportal_register_option_single_candidate_page_bottom', array()),

						//Candidate Profile
						apply_filters('jobportal_option_candidate_profile_top', array()),
						apply_filters('jobportal_option_candidate_profile_main', array(
							'id' => 'candidate_profile_group',
							'title' => esc_html__('Candidate Profile', 'jobportal-framework'),
							'type' => 'group',
							'fields' => array(
								array(
									'id' => 'section_candidate_hide_group_fields',
									'title' => esc_html__('Hide Submit Group Form Fields', 'jobportal-framework'),
									'type' => 'group',
									'fields' => array(
										array(
											'id' => 'enable_candidate_language_multiple',
											'type' => 'button_set',
											'title' => esc_html__('Enable Language Multiple', 'jobportal-framework'),
											'subtitle' => esc_html__('Show/Hidden Multiple', 'jobportal-framework'),
											'desc' => '',
											'options' => array(
												'1' => esc_html__('On', 'jobportal-framework'),
												'0' => esc_html__('Off', 'jobportal-framework'),
											),
											'default' => '0',
										),
										array(
											'id' => 'type_name_candidate',
											'type' => 'select',
											'title' => esc_html__('Name Candidate', 'jobportal-framework'),
											'subtitle' => esc_html__('Display type name candidate after registration', 'jobportal-framework'),
											'options' => array(
												'user-name' => esc_html__('User Name', 'jobportal-framework'),
												'fl-name' => esc_html__('First Name + Last Name', 'jobportal-framework'),
											),
											'default' => 'user-name',
										),
										array(
											'id' => 'hide_candidate_group_fields',
											'type' => 'checkbox_list',
											'title' => esc_html__('Hide Submit Form Groups', 'jobportal-framework'),
											'subtitle' => esc_html__('Choose which fields you want to hide on Candidate Profile', 'jobportal-framework'),
											'options' => array(
												'info' => esc_html__('Basic Info', 'jobportal-framework'),
												'education' => esc_html__('Education', 'jobportal-framework'),
												'experience' => esc_html__('Experience', 'jobportal-framework'),
												'skills' => esc_html__('Skills', 'jobportal-framework'),
												'projects' => esc_html__('Projects', 'jobportal-framework'),
												'awards' => esc_html__('Awards', 'jobportal-framework'),
											),
											'value_inline' => false,
											'default' => array()
										),
										array(
											'id' => 'hide_candidate_fields',
											'type' => 'checkbox_list',
											'title' => esc_html__('Hide Submit Form Fields', 'jobportal-framework'),
											'subtitle' => esc_html__('Choose which fields you want to hide candidate profile', 'jobportal-framework'),
											'options' => array(
												'fields_candidate_avatar' => esc_html__('Avatar', 'jobportal-framework'),
												'fields_candidate_thumbnail' => esc_html__('Thumbnail', 'jobportal-framework'),
												'fields_candidate_first_name' => esc_html__('First name', 'jobportal-framework'),
												'fields_candidate_last_name' => esc_html__('Last name', 'jobportal-framework'),
												'fields_candidate_email_address' => esc_html__('Email address', 'jobportal-framework'),
												'fields_candidate_phone_number' => esc_html__('Phone number', 'jobportal-framework'),
												'fields_candidate_current_position' => esc_html__('Current Position', 'jobportal-framework'),
												'fields_candidate_categories' => esc_html__('Categories', 'jobportal-framework'),
												'fields_candidate_description' => esc_html__('Description', 'jobportal-framework'),
												'fields_candidate_date_of_birth' => esc_html__('Date of Birth', 'jobportal-framework'),
												'fields_candidate_age' => esc_html__('Age', 'jobportal-framework'),
												'fields_candidate_gender' => esc_html__('Gender', 'jobportal-framework'),
												'fields_closing_languages' => esc_html__('Languages', 'jobportal-framework'),
												'fields_candidate_qualification' => esc_html__('Qualification', 'jobportal-framework'),
												'fields_candidate_experience' => esc_html__('Years of Experience', 'jobportal-framework'),
												'fields_candidate_salary' => esc_html__('Salary', 'jobportal-framework'),
												'fields_candidate_resume' => esc_html__('Resume', 'jobportal-framework'),
												'fields_candidate_social' => esc_html__('Social Network', 'jobportal-framework'),
												'fields_candidate_my_profile' => esc_html__('My Profile', 'jobportal-framework'),
												'fields_candidate_location' => esc_html__('Location', 'jobportal-framework'),
												'fields_candidate_map' => esc_html__('Map', 'jobportal-framework'),
												'fields_candidate_gallery' => esc_html__('Gallery', 'jobportal-framework'),
												'fields_candidate_video' => esc_html__('Video', 'jobportal-framework'),
											),
											'value_inline' => false,
											'default' => array()
										),
									)
								),
							)
						)),
						apply_filters('jobportal_option_candidate_profile_bottom', array()),
						apply_filters('jobportal_option_candidate_submit_bottom', array()),
						//Candidate Search
						apply_filters('jobportal_register_option_search_candidate_page_top', array()),
						apply_filters('jobportal_register_option_search_candidate_page_main', array(
							'id' => 'candidate_search_group',
							'title' => esc_html__('Candidate Search', 'jobportal-framework'),
							'type' => 'group',
							'fields' => array(
								array(
									'id' => 'enable_candidate_search_location_top',
									'type' => 'button_set',
									'title' => esc_html__('Enable Search City/Town (Top)', 'jobportal-framework'),
									'subtitle' => esc_html__('Show/Hidden Search City/Town', 'jobportal-framework'),
									'desc' => '',
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => 'enable_candidate_search_location_radius',
									'type' => 'button_set',
									'title' => esc_html__('Enable Search location radius', 'jobportal-framework'),
									'subtitle' => esc_html__('Show/Hidden search location radius', 'jobportal-framework'),
									'desc' => '',
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '1',
								),
								array(
									'id' => 'enable_candidate_search_bg',
									'type' => 'button_set',
									'title' => esc_html__('Enable Background', 'jobportal-framework'),
									'subtitle' => esc_html__('Show/Hidden Background', 'jobportal-framework'),
									'desc' => '',
									'options' => array(
										'1' => esc_html__('On', 'jobportal-framework'),
										'0' => esc_html__('Off', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => "candidate_search_color",
									'title' => esc_html__('Color', 'jobportal-framework'),
									'type' => 'color',
									'col' => '12',
									'default' => '',
									'required' => array(
										array("enable_candidate_search_bg", '=', '1'),
									),
								),
								array(
									'id' => "candidate_search_image",
									'title' => esc_html__('Image', 'jobportal-framework'),
									'type' => 'image',
									'default' => '',
									'col' => '12',
									'required' => array(
										array("enable_candidate_search_bg", '=', '1'),
									),
								),
								array(
									'id' => 'candidate_search_fields',
									'title' => esc_html__('Search Fields', 'jobportal-framework'),
									'type' => 'sorter',
									'default' => array(
										'top' => array(
											'candidate_categories' => esc_html__('Categories', 'jobportal-framework'),
										),
										'sidebar' => array(
											'candidate_rating' => esc_html__('Rating', 'jobportal-framework'),
											'candidate_yoe' => esc_html__('Experience', 'jobportal-framework'),
											'candidate_qualification' => esc_html__('Qualification', 'jobportal-framework'),
											'candidate_gender' => esc_html__('Gender', 'jobportal-framework'),
										),
										'disable' => array(
											'candidate_locations' => esc_html__('Location', 'jobportal-framework'),
											'candidate_ages' => esc_html__('Ages', 'jobportal-framework'),
											'candidate_skills' => esc_html__('Skills', 'jobportal-framework'),
											'candidate_languages' => esc_html__('Languages', 'jobportal-framework'),
										)
									),
								),
								array(
									'id' => 'candidate_search_fields_candidate_categories',
									'title' => esc_html__('Icon Categories', 'jobportal-framework'),
									'type' => 'text',
									'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
								),
								array(
									'id' => 'candidate_search_fields_candidate_rating',
									'title' => esc_html__('Icon Rating', 'jobportal-framework'),
									'type' => 'text',
									'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
								),
								array(
									'id' => 'candidate_search_fields_candidate_yoe',
									'title' => esc_html__('Icon Experience', 'jobportal-framework'),
									'type' => 'text',
									'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
								),
								array(
									'id' => 'candidate_search_fields_candidate_qualification',
									'title' => esc_html__('Icon Qualification', 'jobportal-framework'),
									'type' => 'text',
									'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
								),
								array(
									'id' => 'candidate_search_fields_candidate_ages',
									'title' => esc_html__('Icon Ages', 'jobportal-framework'),
									'type' => 'text',
									'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
								),
								array(
									'id' => 'candidate_search_fields_candidate_gender',
									'title' => esc_html__('Icon Gender', 'jobportal-framework'),
									'type' => 'text',
									'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
								),
								array(
									'id' => 'candidate_search_fields_candidate_skills',
									'title' => esc_html__('Icon Skills', 'jobportal-framework'),
									'type' => 'text',
									'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
								),
								array(
									'id' => 'candidate_search_fields_candidate_languages',
									'title' => esc_html__('Icon Languages', 'jobportal-framework'),
									'type' => 'text',
									'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
								),
								array(
									'id' => 'candidate_search_fields_location',
									'title' => esc_html__('Icon City', 'jobportal-framework'),
									'type' => 'text',
									'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
								),
								array(
									'id' => 'candidate_search_fields_state',
									'title' => esc_html__('Icon State', 'jobportal-framework'),
									'type' => 'text',
									'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
								),
								array(
									'id' => 'candidate_search_fields_country',
									'title' => esc_html__('Icon Country', 'jobportal-framework'),
									'type' => 'text',
									'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
								),
							)
						)),
						apply_filters('jobportal_register_option_search_candidate_page_bottom', array()),


						//Candidate Package
						apply_filters('jobportal_register_option_candidate_package_page_top', array()),
						apply_filters('jobportal_register_option_candidate_package_page_main', array(
							'id' => 'candidate_package_group',
							'title' => esc_html__('Candidate Package', 'jobportal-framework'),
							'type' => 'group',
							'fields' => array(
								array(
									'id' => "enable_candidate_package_jobs_apply",
									'title' => esc_html__('Enable/Disable Jobs Apply', 'jobportal-framework'),
									'type' => 'button_set',
									'options' => array(
										'1' => esc_html__('Yes', 'jobportal-framework'),
										'0' => esc_html__('No', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => "enable_candidate_package_jobs_wishlist",
									'title' => esc_html__('Enable/Disable Jobs Wishlist', 'jobportal-framework'),
									'type' => 'button_set',
									'options' => array(
										'1' => esc_html__('Yes', 'jobportal-framework'),
										'0' => esc_html__('No', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => "enable_candidate_package_company_follow",
									'title' => esc_html__('Enable/Disable Company follow', 'jobportal-framework'),
									'type' => 'button_set',
									'options' => array(
										'1' => esc_html__('Yes', 'jobportal-framework'),
										'0' => esc_html__('No', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => "enable_candidate_package_send_message",
									'title' => esc_html__('Enable/Disable Send Message', 'jobportal-framework'),
									'type' => 'button_set',
									'options' => array(
										'1' => esc_html__('Yes', 'jobportal-framework'),
										'0' => esc_html__('No', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => "enable_candidate_package_review_and_commnent",
									'title' => esc_html__('Enable/Disable Review And Comment', 'jobportal-framework'),
									'type' => 'button_set',
									'options' => array(
										'1' => esc_html__('Yes', 'jobportal-framework'),
										'0' => esc_html__('No', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => "enable_candidate_package_contact_company",
									'title' => esc_html__('Enable/Disable Contact Company In Jobs', 'jobportal-framework'),
									'type' => 'button_set',
									'options' => array(
										'1' => esc_html__('Yes', 'jobportal-framework'),
										'0' => esc_html__('No', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => 'hide_candidate_contact_company_fields',
									'type' => 'checkbox_list',
									'title' => esc_html__('Hide Field Contact Company In Jobs', 'jobportal-framework'),
									'subtitle' => esc_html__('Choose which fields you want to hide on Contact Company In Jobs', 'jobportal-framework'),
									'options' => array(
										'description' => esc_html__('Description', 'jobportal-framework'),
										'categories' => esc_html__('Categories', 'jobportal-framework'),
										'size' => esc_html__('Size', 'jobportal-framework'),
										'founded' => esc_html__('Founded in', 'jobportal-framework'),
										'location' => esc_html__('Location', 'jobportal-framework'),
										'phone' => esc_html__('Phone', 'jobportal-framework'),
										'email' => esc_html__('Email', 'jobportal-framework'),
										'social' => esc_html__('Social', 'jobportal-framework'),
									),
									'value_inline' => false,
									'default' => array('phone', 'email', 'social'),
									'required' => array("enable_candidate_package_contact_company", '=', '1'),
								),
								array(
									'id' => "enable_candidate_package_info_company",
									'title' => esc_html__('Enable/Disable Information Company', 'jobportal-framework'),
									'type' => 'button_set',
									'options' => array(
										'1' => esc_html__('Yes', 'jobportal-framework'),
										'0' => esc_html__('No', 'jobportal-framework'),
									),
									'default' => '0',
								),
								array(
									'id' => 'hide_candidate_info_company_fields',
									'type' => 'checkbox_list',
									'title' => esc_html__('Hide Field Info Company', 'jobportal-framework'),
									'subtitle' => esc_html__('Choose which fields you want to hide on Information Company', 'jobportal-framework'),
									'options' => array(
										'categories' => esc_html__('Categories', 'jobportal-framework'),
										'size' => esc_html__('Size', 'jobportal-framework'),
										'founded' => esc_html__('Founded in', 'jobportal-framework'),
										'location' => esc_html__('Location', 'jobportal-framework'),
										'phone' => esc_html__('Phone', 'jobportal-framework'),
										'email' => esc_html__('Email', 'jobportal-framework'),
										'social' => esc_html__('Social', 'jobportal-framework'),
									),
									'value_inline' => false,
									'default' => array('phone', 'email', 'social'),
									'required' => array("enable_candidate_package_info_company", '=', '1'),
								),
							)
						)),
						apply_filters('jobportal_register_option_candidate_package_page_bottom', array())
					),
				));
		}


		/**
		 * Service page option
		 * @return mixed
		 */

		private function service_option()
		{
			//Archive Service
			$option_archive_service = $option_single_service = $option_submit_service = $option_search_service = array();
			if (jobportal_get_option('enable_post_type_service') === '1') {

				//Archive Service
				$option_archive_service = array(
					'id' => 'jobportal_archive_service_group',
					'type' => 'group',
					'title' => esc_html__('Archive Service', 'jobportal-framework'),
					'fields' => array(
						array(
							'id' => 'archive_service_layout',
							'type' => 'select',
							'title' => esc_html__('Service Layout', 'jobportal-framework'),
							'default' => 'layout-list',
							'options' => array(
								'layout-list' => esc_html__('Layout List', 'jobportal-framework'),
								'layout-grid' => esc_html__('Layout Grid', 'jobportal-framework'),
							)
						),
						array(
							'id' => 'archive_service_items_amount',
							'type' => 'text',
							'title' => esc_html__('Items Amount', 'jobportal-framework'),
							'default' => 12,
							'pattern' => '[0-9]*',
						),
						array(
							'id' => 'service_pagination_type',
							'type' => 'select',
							'title' => esc_html__('Type Pagination', 'jobportal-framework'),
							'default' => 'number',
							'options' => array(
								'number' => esc_html__('Number', 'jobportal-framework'),
								'loadmore' => esc_html__('Load More', 'jobportal-framework')
							)
						),
						array(
							'id' => "service_filter_sidebar_option",
							'title' => esc_html__('Postion Filter ', 'jobportal-framework'),
							'type' => 'select',
							'options' => array(
								'filter-left' => 'Filter Left',
								'filter-right' => 'Filter Right',
							),
							'default' => 'left',
						),
						array(
							'id' => 'enable_service_filter_top',
							'type' => 'button_set',
							'title' => esc_html__('Show Top Filter', 'jobportal-framework'),
							'subtitle' => esc_html__('Show/Hidden Top Filter', 'jobportal-framework'),
							'desc' => '',
							'options' => array(
								'1' => esc_html__('On', 'jobportal-framework'),
								'0' => esc_html__('Off', 'jobportal-framework'),
							),
							'default' => '0',
						),
						array(
							'id' => 'enable_service_show_map',
							'type' => 'button_set',
							'title' => esc_html__('Show Maps', 'jobportal-framework'),
							'subtitle' => esc_html__('Show/Hidden Maps', 'jobportal-framework'),
							'desc' => '',
							'options' => array(
								'1' => esc_html__('On', 'jobportal-framework'),
								'0' => esc_html__('Off', 'jobportal-framework'),
							),
							'default' => '0',
						),
						array(
							'id' => "service_map_postion",
							'title' => esc_html__('Postion Maps ', 'jobportal-framework'),
							'type' => 'select',
							'options' => array(
								'map-right' => 'Map Right',
								'map-top' => 'Map Top',
							),
							'default' => 'right',
							'required' => array(
								array("enable_service_show_map", '=', '1'),
							),
						),
						array(
							'id' => 'enable_service_show_des',
							'type' => 'button_set',
							'title' => esc_html__('Show Description', 'jobportal-framework'),
							'subtitle' => esc_html__('Show/Hidden Description', 'jobportal-framework'),
							'desc' => '',
							'options' => array(
								'1' => esc_html__('On', 'jobportal-framework'),
								'0' => esc_html__('Off', 'jobportal-framework'),
							),
							'default' => '0',
						),
					),
				);

				//Single Service
				$option_single_service = array(
					'id' => 'service_single_service_group',
					'type' => 'group',
					'title' => esc_html__('Single Service', 'jobportal-framework'),
					'fields' => array(
						array(
							'id' => "enable_google_service_schema",
							'type' => 'button_set',
							'title' => esc_html__('Enable Google Service Schema', 'jobportal-framework'),
							'subtitle' => esc_html__('Enable/Disable Google Service Schema (Service)', 'jobportal-framework'),
							'options' => array(
								'1' => esc_html__('On', 'jobportal-framework'),
								'0' => esc_html__('Off', 'jobportal-framework'),
							),
							'default' => '1',
						),
						array(
							'id' => 'enable_sticky_service_sidebar_type',
							'type' => 'button_set',
							'title' => esc_html__('Enable Sticky Sidebar', 'jobportal-framework'),
							'subtitle' => esc_html__('Enable/Disable sticky sidebar when scroll', 'jobportal-framework'),
							'desc' => '',
							'options' => array(
								'1' => esc_html__('On', 'jobportal-framework'),
								'0' => esc_html__('Off', 'jobportal-framework'),
							),
							'default' => '1',
						),
						array(
							'id' => 'enable_single_service_related',
							'type' => 'button_set',
							'title' => esc_html__('Enable Service Related', 'jobportal-framework'),
							'subtitle' => esc_html__('Enable/Disable Service Related', 'jobportal-framework'),
							'desc' => '',
							'options' => array(
								'1' => esc_html__('On', 'jobportal-framework'),
								'0' => esc_html__('Off', 'jobportal-framework'),
							),
							'default' => '1',
						),
						array(
							'id' => 'enable_single_service_review',
							'type' => 'button_set',
							'title' => esc_html__('Enable Service Review', 'jobportal-framework'),
							'subtitle' => esc_html__('Enable/Disable Service Review', 'jobportal-framework'),
							'desc' => '',
							'options' => array(
								'1' => esc_html__('On', 'jobportal-framework'),
								'0' => esc_html__('Off', 'jobportal-framework'),
							),
							'default' => '1',
						),
						array(
							'id' => 'services_details_order',
							'type' => 'sortable',
							'title' => esc_html__('Service Content Order', 'jobportal-framework'),
							'desc' => esc_html__('Drag and drop layout manager, to quickly organize your service content details.', 'jobportal-framework'),
							'options' => array(
								'enable_sp_gallery' => esc_html__('Gallery', 'jobportal-framework'),
								'enable_sp_descriptions' => esc_html__('Descriptions', 'jobportal-framework'),
								'enable_sp_skills' => esc_html__('Skills', 'jobportal-framework'),
								'enable_sp_location' => esc_html__('Location', 'jobportal-framework'),
								'enable_sp_video' => esc_html__('Video', 'jobportal-framework'),
								'enable_sp_faq' => esc_html__('Faq', 'jobportal-framework'),
							),
							'default' => array('enable_sp_gallery', 'enable_sp_descriptions', 'enable_sp_skills', 'enable_sp_location', 'enable_sp_video', 'enable_sp_faq', 'enable_sp_review')
						),
						array(
							'id' => 'service_details_sidebar_order',
							'type' => 'sortable',
							'title' => esc_html__('Service Sidebar Order', 'jobportal-framework'),
							'desc' => esc_html__('Drag and drop layout manager, to quickly organize your service sidebar order.', 'jobportal-framework'),
							'options' => array(
								'enable_sidebar_sp_package' => esc_html__('Package', 'jobportal-framework'),
								'enable_sidebar_sp_info' => esc_html__('Information', 'jobportal-framework'),
							),
							'default' => array('enable_sidebar_sp_package', 'enable_sidebar_sp_info'),
						),
					),
				);
				//Submit Service
				$option_submit_service = array(
					'id' => 'service_submit_group',
					'title' => esc_html__('Service Submit', 'jobportal-framework'),
					'type' => 'group',
					'fields' => array(
						array(
							'id' => 'service_auto_publish',
							'title' => esc_html__('Automatically publish the submitted Service?', 'jobportal-framework'),
							'type' => 'button_set',
							'options' => array(
								'1' => esc_html__('Yes', 'jobportal-framework'),
								'0' => esc_html__('No', 'jobportal-framework'),
							),
							'default' => '1',
						),

						array(
							'id' => 'service_auto_publish_edited',
							'title' => esc_html__('Automatically publish the edited Service?', 'jobportal-framework'),
							'type' => 'button_set',
							'options' => array(
								'1' => esc_html__('Yes', 'jobportal-framework'),
								'0' => esc_html__('No', 'jobportal-framework'),
							),
							'default' => '1',
						),
						array(
							'id' => 'section_service_hide_fields',
							'title' => esc_html__('Hide Submit Form Fields', 'jobportal-framework'),
							'type' => 'group',
							'fields' => array(
								array(
									'id' => 'hide_service_fields',
									'type' => 'checkbox_list',
									'title' => esc_html__('Hide Submit Form Fields', 'jobportal-framework'),
									'subtitle' => esc_html__('Choose which fields you want to hide on New Property page?', 'jobportal-framework'),
									'options' => array(
										'fields_service_title' => esc_html__('Title', 'jobportal-framework'),
										'fields_service_category' => esc_html__('Category', 'jobportal-framework'),
										'fields_service_price' => esc_html__('Price', 'jobportal-framework'),
										'fields_service_delivery_time' => esc_html__('Transfer time', 'jobportal-framework'),
										'fields_service_description' => esc_html__('Description', 'jobportal-framework'),
										'fields_service_languages' => esc_html__('Languages', 'jobportal-framework'),
										'fields_service_language_level' => esc_html__('Languages level', 'jobportal-framework'),
										'fields_service_location' => esc_html__('Location', 'jobportal-framework'),
										'fields_service_map' => esc_html__('Maps', 'jobportal-framework'),
										'fields_service_cover_image' => esc_html__('Cover image', 'jobportal-framework'),
										'fields_closing_gallery' => esc_html__('Gallery', 'jobportal-framework'),
										'fields_service_video' => esc_html__('Video', 'jobportal-framework'),
										'fields_service_skills' => esc_html__('Skills', 'jobportal-framework'),
										'fields_service_addons' => esc_html__('Addons', 'jobportal-framework'),
										'fields_service_faq' => esc_html__('Faqs', 'jobportal-framework'),
									),
									'value_inline' => false,
									'default' => array()
								),
							)
						),
					)
				);

				//Search Service
				$option_search_service = array(
					'id' => 'service_search_group',
					'title' => esc_html__('Search Service', 'jobportal-framework'),
					'type' => 'group',
					'fields' => array(
						array(
							'id' => 'enable_service_search_location_top',
							'type' => 'button_set',
							'title' => esc_html__('Enable Search City/Town (Top)', 'jobportal-framework'),
							'subtitle' => esc_html__('Show/Hidden Search City/Town', 'jobportal-framework'),
							'desc' => '',
							'options' => array(
								'1' => esc_html__('On', 'jobportal-framework'),
								'0' => esc_html__('Off', 'jobportal-framework'),
							),
							'default' => '0',
						),
						array(
							'id' => 'enable_service_search_location_radius',
							'type' => 'button_set',
							'title' => esc_html__('Enable Search location radius', 'jobportal-framework'),
							'subtitle' => esc_html__('Show/Hidden search location radius', 'jobportal-framework'),
							'desc' => '',
							'options' => array(
								'1' => esc_html__('On', 'jobportal-framework'),
								'0' => esc_html__('Off', 'jobportal-framework'),
							),
							'default' => '1',
						),
						array(
							'id' => 'enable_service_search_bg',
							'type' => 'button_set',
							'title' => esc_html__('Enable Background', 'jobportal-framework'),
							'subtitle' => esc_html__('Show/Hidden Background', 'jobportal-framework'),
							'desc' => '',
							'options' => array(
								'1' => esc_html__('On', 'jobportal-framework'),
								'0' => esc_html__('Off', 'jobportal-framework'),
							),
							'default' => '0',
						),
						array(
							'id' => "service_search_color",
							'title' => esc_html__('Color', 'jobportal-framework'),
							'type' => 'color',
							'col' => '12',
							'default' => '',
							'required' => array(
								array("enable_service_search_bg", '=', '1'),
							),
						),
						array(
							'id' => "service_search_image",
							'title' => esc_html__('Image', 'jobportal-framework'),
							'type' => 'image',
							'default' => '',
							'col' => '12',
							'required' => array(
								array("enable_service_search_bg", '=', '1'),
							),
						),
						array(
							'id' => 'service_search_fields',
							'title' => esc_html__('Search Fields', 'jobportal-framework'),
							'type' => 'sorter',
							'default' => array(
								'top' => array(
									'service-categories' => esc_html__('Categories', 'jobportal-framework'),
								),
								'sidebar' => array(
									'service-rating' => esc_html__('Rating', 'jobportal-framework'),
									'service-price' => esc_html__('Price', 'jobportal-framework'),
									'service-language-level' => esc_html__('Languages Level', 'jobportal-framework'),
								),
								'disable' => array(
									'service-location' => esc_html__('Location', 'jobportal-framework'),
									'service-skills' => esc_html__('Skills', 'jobportal-framework'),
									'service-language' => esc_html__('Language', 'jobportal-framework'),
								)
							),
						),
						array(
							'id' => 'service_search_fields_service-categories',
							'title' => esc_html__('Icon Categories', 'jobportal-framework'),
							'type' => 'text',
							'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
						),
						array(
							'id' => 'service_search_fields_service-rating',
							'title' => esc_html__('Icon Rating', 'jobportal-framework'),
							'type' => 'text',
							'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
						),
						array(
							'id' => 'service_search_fields_service-skills',
							'title' => esc_html__('Icon Skills', 'jobportal-framework'),
							'type' => 'text',
							'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
						),
						array(
							'id' => 'service_search_fields_service-language',
							'title' => esc_html__('Icon Language', 'jobportal-framework'),
							'type' => 'text',
							'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
						),
						array(
							'id' => 'service_search_fields_service-language-level',
							'title' => esc_html__('Icon Languages Level', 'jobportal-framework'),
							'type' => 'text',
							'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
						),
						array(
							'id' => 'service_search_fields_location',
							'title' => esc_html__('Icon City', 'jobportal-framework'),
							'type' => 'text',
							'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
						),
						array(
							'id' => 'service_search_fields_state',
							'title' => esc_html__('Icon State', 'jobportal-framework'),
							'type' => 'text',
							'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
						),
						array(
							'id' => 'service_search_fields_country',
							'title' => esc_html__('Icon Country', 'jobportal-framework'),
							'type' => 'text',
							'desc' => esc_html__('Please enter the html code of the "fontawesome" icon to display it', 'jobportal-framework'),
						),
					)
				);
			}

			return
				apply_filters('jobportal_register_service_option_listing_setting_page', array(
					'id' => 'jobportal_listing_service_setting_page_option',
					'title' => esc_html__('Services Option', 'jobportal-framework'),
					'icon' => 'dashicons-nametag',
					'fields' => array(
						array(
							'id' => 'enable_post_type_service',
							'type' => 'button_set',
							'title' => esc_html__('Enable Services', 'jobportal-framework'),
							'subtitle' => esc_html__('Enable/Disable Services', 'jobportal-framework'),
							'desc' => '',
							'options' => array(
								'1' => esc_html__('On', 'jobportal-framework'),
								'0' => esc_html__('Off', 'jobportal-framework'),
							),
							'default' => '0',
						),

						array(
							'id' => 'enable_candidate_service_fee',
							'type' => 'button_set',
							'title' => esc_html__('Enable Candidate Service Fee', 'jobportal-framework'),
							'subtitle' => esc_html__('Enable/Disable Candidate Service Fee', 'jobportal-framework'),
							'desc' => '',
							'options' => array(
								'1' => esc_html__('On', 'jobportal-framework'),
								'0' => esc_html__('Off', 'jobportal-framework'),
							),
							'default' => '1',
							'required' => array(
								array("enable_post_type_service", '=', '1'),
							),
						),

						array(
							'id' => "candidate_number_service_fee",
							'title' => esc_html__('Number Candidate Service Fee', 'jobportal-framework'),
							'subtitle' => esc_html__('Enter (%) Candidate Service Fee', 'jobportal-framework'),
							'type' => 'text',
							'default' => '10',
							'pattern' => '[0-9]*',
							'required' => array(
								array("enable_post_type_service", '=', '1'),
								array("enable_candidate_service_fee", '=', '1')
							),
						),

						//Archive Service
						apply_filters('jobportal_register_option_archive_service_page_top', array()),
						apply_filters(
							'jobportal_register_option_archive_service_page_main',
							$option_archive_service
						),
						apply_filters('jobportal_register_option_archive_service_page_bottom', array()),

						//Single Service
						apply_filters('jobportal_register_option_single_service_page_top', array()),
						apply_filters(
							'jobportal_register_option_single_service_page_main',
							$option_single_service
						),
						apply_filters('jobportal_register_option_single_service_page_bottom', array()),

						//Service Submit
						apply_filters('jobportal_option_service_submit_top', array()),
						apply_filters(
							'jobportal_option_service_submit_main',
							$option_submit_service
						),
						apply_filters('jobportal_option_service_submit_bottom', array()),

						//Service Search
						apply_filters('jobportal_register_option_search_service_page_top', array()),
						apply_filters(
							'jobportal_register_option_search_service_page_main',
							$option_search_service
						),
						apply_filters('jobportal_register_option_search_service_page_bottom', array())
					),
				));
		}

		/**
		 * @return mixed|void
		 */
		private function email_management_option()
		{
			return apply_filters('jobportal_register_option_email_management', array(
				'id' => 'jobportal_email_management_option',
				'title' => esc_html__('Email Template', 'jobportal-framework'),
				'icon' => 'dashicons-email-alt',
				'fields' => array_merge(
					apply_filters('jobportal_register_option_email_management_top', array()),
					array(
						//Header
						array(
							'id' => 'email-header',
							'title' => esc_html__('Header Email', 'jobportal-framework'),
							'type' => 'group',
							'toggle_default' => false,
							'fields' => array(
								array(
									'id' => 'logo_email',
									'type' => 'text',
									'title' => esc_html__('Logo Email', 'jobportal-framework'),
									'default' => '',
									'subtitle' => esc_html__('Choose link logo for email', 'jobportal-framework'),
								),
								array(
									'id' => 'title_email',
									'type' => 'text',
									'title' => esc_html__('Title', 'jobportal-framework'),
									'default' => esc_html__('Welcome to %website_url!', 'jobportal-framework'),
								),
							)
						),
						//Content
						array(
							'id' => 'email-content',
							'title' => esc_html__('Content Email', 'jobportal-framework'),
							'type' => 'group',
							'toggle_default' => false,
							'fields' => array_merge(
								apply_filters('jobportal_register_option_email_content_top', array()),
								array(
									array(
										'id' => 'email-new-user',
										'title' => esc_html__('New Registed User', 'jobportal-framework'),
										'type' => 'group',
										'toggle_default' => false,
										'fields' => array(
											array(
												'id' => 'jobportal_user_mail_register_user',
												'type' => 'info',
												'style' => 'info',
												'title' => esc_html__('User Email', 'jobportal-framework'),
											),
											array(
												'id' => 'subject_mail_register_user',
												'type' => 'text',
												'title' => esc_html__('Subject', 'jobportal-framework'),
												'default' => esc_html__('Welcome to %website_name - Your Account Details', 'jobportal-framework'),
											),
											array(
												'id' => 'mail_register_user',
												'type' => 'editor',
												'args' => array(
													'media_buttons' => true,
													'quicktags' => true,
												),
												'title' => esc_html__('Content', 'jobportal-framework'),
												'subtitle' => esc_html__('Available placeholders: %user_login, %user_password, %website_url, %website_name', 'jobportal-framework'),
												'default' => esc_html__(
													'Hello,

Welcome to %website_name! We\'re excited to have you join our job board community.

Your account has been successfully created. Here are your login credentials:

Username: %user_login
Password: %user_password

🔐 Security Tip: Please change your password after your first login for better security.

What\'s next?
• Complete your profile to increase your visibility
• Browse available job opportunities
• Set up job alerts to never miss a perfect match

Login to your account: %website_url

If you have any questions or need assistance, please don\'t hesitate to contact our support team.

Best regards,
The %website_name Team',
													'jobportal-framework'
												),
											),
											array(
												'id' => 'jobportal_admin_mail_register_user',
												'type' => 'info',
												'style' => 'info',
												'title' => esc_html__('Admin Email', 'jobportal-framework'),
											),
											array(
												'id' => 'subject_admin_mail_register_user',
												'type' => 'text',
												'title' => esc_html__('Subject', 'jobportal-framework'),
												'default' => esc_html__('New User Registration', 'jobportal-framework'),
											),
											array(
												'id' => 'admin_mail_register_user',
												'type' => 'editor',
												'args' => array(
													'media_buttons' => true,
													'quicktags' => true,
												),
												'title' => esc_html__('Content', 'jobportal-framework'),
												'subtitle' => esc_html__('Available placeholders: %user_login, %website_url, %website_name', 'jobportal-framework'),
												'default' => esc_html__(
													'New user registration on %website_url.
												E-mail: %user_login',
													'jobportal-framework'
												),
											)
										)
									),
									array(
										'id' => 'email-new-user',
										'title' => esc_html__('Deactive User', 'jobportal-framework'),
										'type' => 'group',
										'toggle_default' => false,
										'fields' => array(
											array(
												'id' => 'jobportal_user_mail_deactive_user',
												'type' => 'info',
												'style' => 'info',
												'title' => esc_html__('User Email', 'jobportal-framework'),
											),
											array(
												'id' => 'subject_mail_deactive_user',
												'type' => 'text',
												'title' => esc_html__('Subject', 'jobportal-framework'),
												'default' => esc_html__('Your Account on ' . get_bloginfo('name') . ' Has Been Deleted', 'jobportal-framework'),
											),
											array(
												'id' => 'mail_deactive_user',
												'type' => 'editor',
												'args' => array(
													'media_buttons' => true,
													'quicktags' => true,
												),
												'title' => esc_html__('Content', 'jobportal-framework'),
												'subtitle' => esc_html__('Available placeholders: %user_login, %website_url, %website_name', 'jobportal-framework'),
												'default' => esc_html__(
													'Hello %user_login,
										Your account on ' . get_bloginfo('name') . ' has been deleted.
										If this was done by you, no further action is needed.
										If you did not request this deletion or believe this was a mistake, please contact our support team immediately.
										Best regards,',
													'jobportal-framework'
												),
											),
											array(
												'id' => 'jobportal_admin_mail_deactive_user',
												'type' => 'info',
												'style' => 'info',
												'title' => esc_html__('Admin Email', 'jobportal-framework'),
											),
											array(
												'id' => 'subject_admin_mail_deactive_user',
												'type' => 'text',
												'title' => esc_html__('Subject', 'jobportal-framework'),
												'default' => esc_html__('A User Account Has Been Deleted', 'jobportal-framework'),
											),
											array(
												'id' => 'admin_mail_deactive_user',
												'type' => 'editor',
												'args' => array(
													'media_buttons' => true,
													'quicktags' => true,
												),
												'title' => esc_html__('Content', 'jobportal-framework'),
												'subtitle' => esc_html__('Available placeholders: %user_login, %user_email, %website_url, %website_name', 'jobportal-framework'),
												'default' => esc_html__(
													'The following user account has been deleted from the system:
												Username: %user_login
												Email: %user_email
												Please review if any issues arise.
												Best regards,',
													'jobportal-framework'
												),
											)
										)
									),
									array(
										'id' => 'mail-verify-user',
										'title' => esc_html__('Verify User', 'jobportal-framework'),
										'type' => 'group',
										'toggle_default' => false,
										'fields' => array(
											array(
												'id' => 'jobportal_user_mail_verify_user',
												'type' => 'info',
												'style' => 'info',
												'title' => esc_html__('User Email', 'jobportal-framework'),
											),
											array(
												'id' => 'subject_mail_verify_user',
												'type' => 'text',
												'title' => esc_html__('Subject', 'jobportal-framework'),
												'default' => esc_html__('Account Verification', 'jobportal-framework'),
											),
											array(
												'id' => 'mail_verify_user',
												'type' => 'editor',
												'args' => array(
													'media_buttons' => true,
													'quicktags' => true,
												),
												'title' => esc_html__('Content', 'jobportal-framework'),
												'subtitle' => esc_html__('Available placeholders: %code_verify_user, %website_url, %website_name', 'jobportal-framework'),
												'default' => esc_html__(
													"To verify your email address, please use the following One Time Password (OTP):
													%code_verify_user
												If you have any problems, please contact us.
												Thank you!",
													'jobportal-framework'
												),
											)
										)
									),
									array(
										'id' => 'mail-pending-identify-user',
										'title' => esc_html__('Pending Identify User', 'jobportal-framework'),
										'type' => 'group',
										'toggle_default' => false,
										'fields' => array(
											array(
												'id' => 'jobportal_user_mail_pending_identify_for_user',
												'type' => 'info',
												'style' => 'info',
												'title' => esc_html__('For User', 'jobportal-framework'),
											),
											array(
												'id' => 'subject_mail_pending_identify_for_user',
												'type' => 'text',
												'title' => esc_html__('Subject', 'jobportal-framework'),
												'default' => esc_html__('Account Verification', 'jobportal-framework'),
											),
											array(
												'id' => 'mail_pending_identify_for_user',
												'type' => 'editor',
												'args' => array(
													'media_buttons' => true,
													'quicktags' => true,
												),
												'title' => esc_html__('Content', 'jobportal-framework'),
												'subtitle' => esc_html__('Available placeholders: %user_login, %website_url, %website_name', 'jobportal-framework'),
												'default' => esc_html__(
													"Hi %user_login,
										Your identification is pending.
										Please wait for the admin to approve your identification.
										Best regards,",
													'jobportal-framework'
												),
											),
											array(
												'id' => 'jobportal_user_mail_pending_identify_for_admin',
												'type' => 'info',
												'style' => 'info',
												'title' => esc_html__('For Admin', 'jobportal-framework'),
											),
											array(
												'id' => 'subject_mail_pending_identify_for_admin',
												'type' => 'text',
												'title' => esc_html__('Subject', 'jobportal-framework'),
												'default' => esc_html__('Account Verification', 'jobportal-framework'),
											),
											array(
												'id' => 'mail_pending_identify_for_admin',
												'type' => 'editor',
												'args' => array(
													'media_buttons' => true,
													'quicktags' => true,
												),
												'title' => esc_html__('Content', 'jobportal-framework'),
												'subtitle' => esc_html__('Available placeholders: %user_login, %user_email, %before_image, %after_image, %website_url, %website_name', 'jobportal-framework'),
												'default' => esc_html__(
													"Hi Admin,
													A user has submitted their identification for verification.
													Please review the information below:
													Username: %user_login
													Email: %user_email
													Before Image: %before_image
													After Image: %after_image
													Please take the necessary action to approve or reject the identification.
													Best regards,",
													'jobportal-framework'
												),
											)
										)
									),
									array(
										'id' => 'mail-approve-identify-user',
										'title' => esc_html__('Approve Identify User', 'jobportal-framework'),
										'type' => 'group',
										'toggle_default' => false,
										'fields' => array(
											array(
												'id' => 'jobportal_user_mail_approve_identify_for_user',
												'type' => 'info',
												'style' => 'info',
												'title' => esc_html__('For User', 'jobportal-framework'),
											),
											array(
												'id' => 'subject_mail_approve_identify_for_user',
												'type' => 'text',
												'title' => esc_html__('Subject', 'jobportal-framework'),
												'default' => esc_html__('Account Verification', 'jobportal-framework'),
											),
											array(
												'id' => 'mail_approve_identify_for_user',
												'type' => 'editor',
												'args' => array(
													'media_buttons' => true,
													'quicktags' => true,
												),
												'title' => esc_html__('Content', 'jobportal-framework'),
												'subtitle' => esc_html__('Available placeholders: %user_login, %website_url, %website_name', 'jobportal-framework'),
												'default' => esc_html__(
													"Hi %user_login,
										Your identification has been approved.",
													'jobportal-framework'
												),
											),
											array(
												'id' => 'jobportal_user_mail_approve_identify_for_admin',
												'type' => 'info',
												'style' => 'info',
												'title' => esc_html__('For Admin', 'jobportal-framework'),
											),
											array(
												'id' => 'subject_mail_approve_identify_for_admin',
												'type' => 'text',
												'title' => esc_html__('Subject', 'jobportal-framework'),
												'default' => esc_html__('Account Verification', 'jobportal-framework'),
											),
											array(
												'id' => 'mail_approve_identify_for_admin',
												'type' => 'editor',
												'args' => array(
													'media_buttons' => true,
													'quicktags' => true,
												),
												'title' => esc_html__('Content', 'jobportal-framework'),
												'subtitle' => esc_html__('Available placeholders: %user_login, %user_email, %website_url, %website_name', 'jobportal-framework'),
												'default' => esc_html__(
													"Hi Admin,
													Information of user who has been approved:
													Username: %user_login
													Email: %user_email",
													'jobportal-framework'
												),
											)
										)
									),
									array(
										'id' => 'mail-unverify-identify-user',
										'title' => esc_html__('Unverify Identify User', 'jobportal-framework'),
										'type' => 'group',
										'toggle_default' => false,
										'fields' => array(
											array(
												'id' => 'jobportal_user_mail_unverify_identify_for_user',
												'type' => 'info',
												'style' => 'info',
												'title' => esc_html__('For User', 'jobportal-framework'),
											),
											array(
												'id' => 'subject_mail_unverify_identify_for_user',
												'type' => 'text',
												'title' => esc_html__('Subject', 'jobportal-framework'),
												'default' => esc_html__('Account Verification', 'jobportal-framework'),
											),
											array(
												'id' => 'mail_unverify_identify_for_user',
												'type' => 'editor',
												'args' => array(
													'media_buttons' => true,
													'quicktags' => true,
												),
												'title' => esc_html__('Content', 'jobportal-framework'),
												'subtitle' => esc_html__('Available placeholders: %user_login, %website_url, %website_name', 'jobportal-framework'),
												'default' => esc_html__(
													"Hi %user_login,
										Your identification has been rejected.
										Please contact us for more information.",
													'jobportal-framework'
												),
											),
											array(
												'id' => 'jobportal_user_mail_unverify_identify_for_admin',
												'type' => 'info',
												'style' => 'info',
												'title' => esc_html__('For Admin', 'jobportal-framework'),
											),
											array(
												'id' => 'subject_mail_unverify_identify_for_admin',
												'type' => 'text',
												'title' => esc_html__('Subject', 'jobportal-framework'),
												'default' => esc_html__('Account Verification', 'jobportal-framework'),
											),
											array(
												'id' => 'mail_unverify_identify_for_admin',
												'type' => 'editor',
												'args' => array(
													'media_buttons' => true,
													'quicktags' => true,
												),
												'title' => esc_html__('Content', 'jobportal-framework'),
												'subtitle' => esc_html__('Available placeholders: %user_login, %user_email, %website_url, %website_name', 'jobportal-framework'),
												'default' => esc_html__(
													"Hi Admin,
													Information of user who has been rejected:
													Username: %user_login
													Email: %user_email",
													'jobportal-framework'
												),
											)
										)
									),
									array(
										'id' => 'email-activated-package',
										'title' => esc_html__('Activated Package', 'jobportal-framework'),
										'type' => 'group',
										'toggle_default' => false,
										'fields' => array(
											array(
												'id' => 'jobportal_user_mail_activated_package',
												'type' => 'info',
												'style' => 'info',
												'title' => esc_html__('User Email', 'jobportal-framework'),
											),
											array(
												'id' => 'subject_mail_activated_package',
												'type' => 'text',
												'title' => esc_html__('Subject', 'jobportal-framework'),
												'default' => esc_html__('Your Package Has Been Activated - Start Posting Jobs Now!', 'jobportal-framework'),
											),
											array(
												'id' => 'mail_activated_package',
												'type' => 'editor',
												'args' => array(
													'media_buttons' => true,
													'quicktags' => true,
												),
												'title' => esc_html__('Content', 'jobportal-framework'),
												'subtitle' => esc_html__('Available placeholders: %pdf_file, %website_url, %website_name', 'jobportal-framework'),
												'default' => esc_html__(
													'Hello,

Great news! Your package purchase has been successfully activated on %website_name.

🎉 What this means for you:
• Your package is now active and ready to use
• You can start posting job listings immediately
• Access all premium features included in your plan

📄 Invoice Details:
Your invoice has been attached to this email for your records.

📋 Next Steps:
1. Log in to your dashboard at %website_url
2. Create your first job posting
3. Manage applications from qualified candidates

Need help getting started? Our support team is here to assist you.

Thank you for choosing %website_name!

Best regards,
The %website_name Team',
													'jobportal-framework'
												),
											),
											array(
												'id' => 'jobportal_admin_mail_activated_package',
												'type' => 'info',
												'style' => 'info',
												'title' => esc_html__('Admin Email', 'jobportal-framework'),
											),
											array(
												'id' => 'subject_admin_mail_activated_package',
												'type' => 'text',
												'title' => esc_html__('Subject', 'jobportal-framework'),
												'default' => esc_html__('Your package has been purchased by 1 user', 'jobportal-framework'),
											),
											array(
												'id' => 'admin_mail_activated_package',
												'type' => 'editor',
												'args' => array(
													'media_buttons' => true,
													'quicktags' => true,
												),
												'title' => esc_html__('Content', 'jobportal-framework'),
												'subtitle' => esc_html__('Available placeholders: %user_login, %user_email, %package_id, %website_url, %website_name', 'jobportal-framework'),
												'default' => esc_html__(
													"We are excited to inform you that a user has successfully purchased your package! Here are the details:User Name: %user_login,User Email: %user_email,Package ID: %package_id",
													'jobportal-framework'
												),
											),
										)
									),
									array(
										'id' => 'new-jobs-apply',
										'title' => esc_html__('Apply Jobs', 'jobportal-framework'),
										'type' => 'group',
										'toggle_default' => false,
										'fields' => array(
											array(
												'id' => 'jobportal_info_mail_candidate_apply',
												'type' => 'info',
												'style' => 'info',
												'title' => esc_html__('User Email', 'jobportal-framework'),
											),
											array(
												'id' => 'subject_mail_candidate_apply',
												'type' => 'text',
												'title' => esc_html__('Subject', 'jobportal-framework'),
												'default' => esc_html__('Application Submitted Successfully - %job_title', 'jobportal-framework'),
											),
											array(
												'id' => 'mail_candidate_apply',
												'type' => 'editor',
												'args' => array(
													'media_buttons' => true,
													'quicktags' => true,
												),
												'title' => esc_html__('Content', 'jobportal-framework'),
												'subtitle' => esc_html__('Available placeholders: %applicant_name, %job_link, %job_url, %website_url, %website_name', 'jobportal-framework'),
												'default' => esc_html__(
													'Hello %applicant_name,

✅ Your job application has been submitted successfully!

📋 Application Details:
• Position: %job_link
• View Job Posting: %job_url

📝 What Happens Next:
1. The employer will review your application
2. If shortlisted, they\'ll contact you directly
3. You can track your application status in your dashboard

💼 While You Wait:
• Continue browsing other opportunities
• Update your profile to stand out
• Apply to more positions that match your skills

📊 Track Your Applications:
View all your applications and their status in your candidate dashboard: %website_url

Good luck with your application!

Best regards,
The %website_name Team',
													'jobportal-framework'
												),
											),
											array(
												'id' => 'jobportal_info_mail_employer_apply',
												'type' => 'info',
												'style' => 'info',
												'title' => esc_html__('Admin Email', 'jobportal-framework'),
											),
											array(
												'id' => 'subject_mail_employer_apply',
												'type' => 'text',
												'title' => esc_html__('Subject', 'jobportal-framework'),
												'default' => esc_html__('New Application Received - %job_title', 'jobportal-framework'),
											),
											array(
												'id' => 'mail_employer_apply',
												'type' => 'editor',
												'args' => array(
													'media_buttons' => true,
													'quicktags' => true,
												),
												'title' => esc_html__('Content', 'jobportal-framework'),
												'subtitle' => esc_html__('Available placeholders: %job_link, %job_url, %cv_url, %applicant_name, %applicant_url, %website_url, %website_name', 'jobportal-framework'),
												'default' => esc_html__(
													'Hello,

🎯 You\'ve received a new job application!

📋 Application Details:
• Position: %job_link
• View Job Posting: %job_url

👤 Candidate Information:
• Applicant Name: %applicant_name
• View Candidate Profile: %applicant_url
• Download CV/Resume: %cv_url

📊 Next Steps:
1. Review the candidate\'s profile and CV
2. Check their qualifications and experience
3. Shortlist or contact the candidate if interested
4. Manage all applications from your dashboard

💼 Manage Applications:
View and manage all applications for this position in your employer dashboard: %website_url

Don\'t forget to respond to candidates in a timely manner to maintain a positive employer brand!

Best regards,
The %website_name Team',
													'jobportal-framework'
												),
											),
											array(
												'id' => 'jobportal_info_mail_candidate_apply_nlogin',
												'type' => 'info',
												'style' => 'info',
												'title' => esc_html__('Candidate Email (Not Login)', 'jobportal-framework'),
											),
											array(
												'id' => 'subject_mail_candidate_apply_nlogin',
												'type' => 'text',
												'title' => esc_html__('Subject', 'jobportal-framework'),
												'default' => esc_html__('There is 1 candidate applied for your job', 'jobportal-framework'),
											),
											array(
												'id' => 'mail_candidate_apply_nlogin',
												'type' => 'editor',
												'args' => array(
													'media_buttons' => true,
													'quicktags' => true,
												),
												'title' => esc_html__('Content', 'jobportal-framework'),
												'subtitle' => esc_html__('Available placeholders: %job_link, %job_url, %cv_url, %message, %website_url, %website_name', 'jobportal-framework'),
												'default' => esc_html__(
													'Hi,
												Your jobs on %website_url has been applied.
												Jobs Title: %job_link
												Jobs Url: %job_url
												CV Url: %cv_url
												Message: %message',
													'jobportal-framework'
												),
											),
										)
									),
									array(
										'id' => 'email-activated-listing',
										'title' => esc_html__('Activated Jobs', 'jobportal-framework'),
										'type' => 'group',
										'toggle_default' => false,
										'fields' => array(
											array(
												'id' => 'jobportal_user_mail_activated_listing',
												'type' => 'info',
												'style' => 'info',
												'title' => esc_html__('User Email', 'jobportal-framework'),
											),
											array(
												'id' => 'subject_mail_activated_listing',
												'type' => 'text',
												'title' => esc_html__('Subject', 'jobportal-framework'),
												'default' => esc_html__('Your purchase was activated', 'jobportal-framework'),
											),
											array(
												'id' => 'mail_activated_listing',
												'type' => 'editor',
												'args' => array(
													'media_buttons' => true,
													'quicktags' => true,
												),
												'title' => esc_html__('Content', 'jobportal-framework'),
												'subtitle' => esc_html__('Available placeholders: %website_url, %website_name', 'jobportal-framework'),
												'default' => esc_html__('Hi there, Your purchase on %website_url is activated! You should go and check it out.', 'jobportal-framework'),
											)
										)
									),
									array(
										'id' => 'email-approved-listing',
										'title' => esc_html__('Approved Jobs', 'jobportal-framework'),
										'type' => 'group',
										'toggle_default' => false,
										'fields' => array(
											array(
												'id' => 'jobportal_user_mail_approved_listing',
												'type' => 'info',
												'style' => 'info',
												'title' => esc_html__('User Email', 'jobportal-framework'),
											),
											array(
												'id' => 'subject_mail_approved_listing',
												'type' => 'text',
												'title' => esc_html__('Subject', 'jobportal-framework'),
												'default' => esc_html__('Your listing approved', 'jobportal-framework'),
											),
											array(
												'id' => 'mail_approved_listing',
												'type' => 'editor',
												'args' => array(
													'media_buttons' => true,
													'quicktags' => true,
												),
												'title' => esc_html__('Content', 'jobportal-framework'),
												'subtitle' => esc_html__('Available placeholders: %user_login, %job_title, %job_url, %website_url, %website_name', 'jobportal-framework'),
												'default' => esc_html__(
													"Hi there,
										Your jobs on %website_url has been approved.
										Your Name:%user_login
										Jobs Title:%job_title
										Jobs Url: %job_url",
													'jobportal-framework'
												),
											)
										)
									),
									array(
										'id' => 'mail-approved-user-status',
										'title' => esc_html__('Approved User Status (Candidate)', 'jobportal-framework'),
										'type' => 'group',
										'toggle_default' => false,
										'fields' => array(
											array(
												'id' => 'jobportal_user_mail_approved_user_status',
												'type' => 'info',
												'style' => 'info',
												'title' => esc_html__('User Email', 'jobportal-framework'),
											),
											array(
												'id' => 'subject_mail_approved_user_status',
												'type' => 'text',
												'title' => esc_html__('Subject', 'jobportal-framework'),
												'default' => esc_html__('Congratulations! Your Candidate Profile Has Been Approved', 'jobportal-framework'),
											),
											array(
												'id' => 'mail_approved_user_status',
												'type' => 'editor',
												'args' => array(
													'media_buttons' => true,
													'quicktags' => true,
												),
												'title' => esc_html__('Content', 'jobportal-framework'),
												'subtitle' => esc_html__('Available placeholders: %user_login, %candidate_title, %candidate_url, %website_url, %website_name', 'jobportal-framework'),
												'default' => esc_html__(
													'Hello %user_login,

🎉 Great news! Your candidate profile has been reviewed and approved.

Your profile is now live and visible to employers on %website_name!

Your Profile:
• Profile Title: %candidate_title
• View Profile: %candidate_url

What\'s Next:
1. Complete your profile to increase your chances
   - Add a professional photo
   - Upload your resume/CV
   - Add your skills and experience
   - Write a compelling bio

2. Start applying for jobs
   - Browse available positions
   - Set up job alerts
   - Connect with employers

3. Build your professional network
   - Follow companies you\'re interested in
   - Engage with job postings

Pro Tip: A complete profile is 3x more likely to be viewed by employers!

Ready to find your dream job? Start browsing: %website_url

If you need any assistance, our support team is here to help.

Best of luck in your job search!

Best regards,
The %website_name Team',
													'jobportal-framework'
												),
											)
										)
									),
									array(
										'id' => 'mail-approved-user-status-company',
										'title' => esc_html__('Approved User Status (Employer)', 'jobportal-framework'),
										'type' => 'group',
										'toggle_default' => false,
										'fields' => array(
											array(
												'id' => 'jobportal_user_mail_approved_user_status_company',
												'type' => 'info',
												'style' => 'info',
												'title' => esc_html__('User Email', 'jobportal-framework'),
											),
											array(
												'id' => 'subject_mail_approved_user_status_company',
												'type' => 'text',
												'title' => esc_html__('Subject', 'jobportal-framework'),
												'default' => esc_html__('Your Employer Account Has Been Approved - Start Hiring Today!', 'jobportal-framework'),
											),
											array(
												'id' => 'mail_approved_user_status_company',
												'type' => 'editor',
												'args' => array(
													'media_buttons' => true,
													'quicktags' => true,
												),
												'title' => esc_html__('Content', 'jobportal-framework'),
												'subtitle' => esc_html__('Available placeholders: %user_login, %company_title, %company_url, %website_url, %website_name', 'jobportal-framework'),
												'default' => esc_html__(
													'Hello %user_login,

Congratulations! Your employer account has been approved.

You\'re all set to start finding the perfect candidates for your team on %website_name!

Your Company Profile:
• Company: %company_title
• View Profile: %company_url

What You Can Do Now:
1. Post Job Listings
   - Create detailed job descriptions
   - Set requirements and qualifications
   - Choose your preferred package

2. Manage Applications
   - Review candidate profiles
   - Filter and shortlist applicants
   - Schedule interviews

3. Build Your Brand
   - Complete your company profile
   - Add company logo and description
   - Showcase your company culture

💡 Pro Tip: Companies with complete profiles receive 40% more applications!

Access Your Dashboard:
Log in to manage your job postings and applications: %website_url

Need help getting started? Check out our employer guide or contact our support team.

Welcome to %website_name!

Best regards,
The %website_name Team',
													'jobportal-framework'
												),
											)
										)
									),
									array(
										'id' => 'email-expired-listing',
										'title' => esc_html__('Expired Jobs', 'jobportal-framework'),
										'type' => 'group',
										'toggle_default' => false,
										'fields' => array(
											array(
												'id' => 'jobportal_user_mail_expired_listing',
												'type' => 'info',
												'style' => 'info',
												'title' => esc_html__('User Email', 'jobportal-framework'),
											),
											array(
												'id' => 'subject_mail_expired_listing',
												'type' => 'text',
												'title' => esc_html__('Subject', 'jobportal-framework'),
												'default' => esc_html__('Your Job Posting Has Expired - Renew Now', 'jobportal-framework'),
											),
											array(
												'id' => 'mail_expired_listing',
												'type' => 'editor',
												'args' => array(
													'media_buttons' => true,
													'quicktags' => true,
												),
												'title' => esc_html__('Content', 'jobportal-framework'),
												'subtitle' => esc_html__('Available placeholders: %job_title, %job_url, %website_url, %website_name', 'jobportal-framework'),
												'default' => esc_html__(
													'Hello,

Your job posting has expired on %website_name.

Expired Job:
• Position: %job_title
• View Job: %job_url

Renew Your Job Posting:
To continue receiving applications, you can:
1. Renew this job posting from your dashboard
2. Extend the posting duration
3. Create a new job posting

Benefits of Renewing:
• Continue receiving applications
• Maintain visibility to active job seekers
• Access to your candidate database

Manage Your Job Postings:
Log in to your employer dashboard to renew or manage your job postings: %website_url

Need help? Contact our support team for assistance.

Best regards,
The %website_name Team',
													'jobportal-framework'
												),
											)
										)
									),
									array(
										'id' => 'email-job-alerts',
										'title' => esc_html__('Job Alerts', 'jobportal-framework'),
										'type' => 'group',
										'toggle_default' => false,
										'fields' => array(
											array(
												'id' => 'jobportal_first_mail_job_alerts',
												'type' => 'info',
												'style' => 'info',
												'title' => esc_html__('First Email', 'jobportal-framework'),
											),
											array(
												'id' => 'subject_first_mail_job_alerts',
												'type' => 'text',
												'title' => esc_html__('Subject', 'jobportal-framework'),
												'default' => esc_html__('New Job Announcement', 'jobportal-framework'),
											),
											array(
												'id' => 'first_mail_job_alerts',
												'type' => 'editor',
												'args' => array(
													'media_buttons' => true,
													'quicktags' => true,
												),
												'title' => esc_html__('Content', 'jobportal-framework'),
												'subtitle' => esc_html__('Available placeholders: %frequency, %unregister_link, %website_url, %website_name', 'jobportal-framework'),
												'default' => wp_kses_post(
													'Hello,
												Thank you for signing up, you will receive %frequency job related information.
												Best regards,
												Do not notify me anymore? <a href="%unregister_link">Click here</a>',
												),
											),
											array(
												'id' => 'jobportal_last_mail_job_alerts',
												'type' => 'info',
												'style' => 'info',
												'title' => esc_html__('Last Email', 'jobportal-framework'),
											),
											array(
												'id' => 'subject_last_mail_job_alerts',
												'type' => 'text',
												'title' => esc_html__('Subject', 'jobportal-framework'),
												'default' => esc_html__('New Job Announcement', 'jobportal-framework'),
											),
											array(
												'id' => 'last_mail_job_alerts',
												'type' => 'editor',
												'args' => array(
													'media_buttons' => true,
													'quicktags' => true,
												),
												'title' => esc_html__('Content', 'jobportal-framework'),
												'subtitle' => esc_html__('Available placeholders: %number, %list_job, %unregister_link, %website_url, %website_name', 'jobportal-framework'),
												'default' => wp_kses_post(
													'Hello,
										There are %number jobs found at your request, job listing below:
										%list_job
										Best regards,
										Do not notify me anymore? <a href="%unregister_link">Click here</a>',
												),
											),
										)
									),
									array(
										'id' => 'email-approved-apply',
										'title' => esc_html__('Approved Applicants', 'jobportal-framework'),
										'type' => 'group',
										'toggle_default' => false,
										'fields' => array(
											array(
												'id' => 'jobportal_user_mail_approved_applicants',
												'type' => 'info',
												'style' => 'info',
												'title' => esc_html__('User Email', 'jobportal-framework'),
											),
											array(
												'id' => 'subject_mail_approved_applicants',
												'type' => 'text',
												'title' => esc_html__('Subject', 'jobportal-framework'),
												'default' => esc_html__('Congratulations! Your Application Has Been Approved', 'jobportal-framework'),
											),
											array(
												'id' => 'mail_approved_applicants',
												'type' => 'editor',
												'args' => array(
													'media_buttons' => true,
													'quicktags' => true,
												),
												'title' => esc_html__('Content', 'jobportal-framework'),
												'subtitle' => esc_html__('Available placeholders: %employer_name, %job_link, %job_url, %website_url, %website_name', 'jobportal-framework'),
												'default' => esc_html__(
													'Hello,

🎉 Great news! Your application has been approved by the employer.

📋 Application Details:
• Employer: %employer_name
• Position: %job_link
• View Job Posting: %job_url

✨ What This Means:
The employer has reviewed your application and is interested in moving forward. They may contact you soon to discuss next steps, which could include:
• Phone or video interview
• In-person interview
• Additional assessments

💼 Prepare for Next Steps:
• Review the job description again
• Prepare questions about the role
• Be ready to discuss your experience
• Check your email and phone regularly

📞 Stay Connected:
Make sure your contact information is up to date in your profile so the employer can reach you easily.

Good luck with the next steps!

Best regards,
The %website_name Team',
													'jobportal-framework'
												),
											)
										)
									),
									array(
										'id' => 'email-job-invite',
										'title' => esc_html__('Invite Jobs', 'jobportal-framework'),
										'type' => 'group',
										'toggle_default' => false,
										'fields' => array(
											array(
												'id' => 'jobportal_user_mail_job_invite',
												'type' => 'info',
												'style' => 'info',
												'title' => esc_html__('User Email', 'jobportal-framework'),
											),
											array(
												'id' => 'subject_mail_job_invite',
												'type' => 'text',
												'title' => esc_html__('Subject', 'jobportal-framework'),
												'default' => esc_html__('You\'ve Been Invited to Apply - %employer_name', 'jobportal-framework'),
											),
											array(
												'id' => 'mail_job_invite',
												'type' => 'editor',
												'args' => array(
													'media_buttons' => true,
													'quicktags' => true,
												),
												'title' => esc_html__('Content', 'jobportal-framework'),
												'subtitle' => esc_html__('Available placeholders: %employer_name, %jobs_invite_links, %website_url, %website_name', 'jobportal-framework'),
												'default' => esc_html__(
													'Hello,

🌟 Exciting news! You\'ve been personally invited to apply for a position.

📋 Invitation Details:
• Employer: %employer_name
• Invited Positions: %jobs_invite_links

✨ Why You Were Invited:
The employer has reviewed your profile and believes you might be a great fit for their team. This is a great opportunity!

📝 Next Steps:
1. Review the job postings you\'ve been invited to
2. If interested, submit your application
3. Highlight why you\'re interested in the role
4. Showcase your relevant skills and experience

💡 Pro Tip: Personalized applications for invited positions have a higher success rate!

Ready to apply? Click on the job links above to view details and submit your application.

Good luck!

Best regards,
The %website_name Team',
													'jobportal-framework'
												),
											)
										)
									),
									array(
										'id' => 'email-new-wire-transfer',
										'title' => esc_html__('New Wire Transfer', 'jobportal-framework'),
										'type' => 'group',
										'toggle_default' => false,
										'fields' => array(
											array(
												'id' => 'jobportal_user_mail_new_wire_transfer',
												'type' => 'info',
												'style' => 'info',
												'title' => esc_html__('User Email', 'jobportal-framework'),
											),
											array(
												'id' => 'subject_mail_new_wire_transfer',
												'type' => 'text',
												'title' => esc_html__('Subject', 'jobportal-framework'),
												'default' => esc_html__('Wire Transfer Payment Request Received', 'jobportal-framework'),
											),
											array(
												'id' => 'mail_new_wire_transfer',
												'type' => 'editor',
												'args' => array(
													'media_buttons' => true,
													'quicktags' => true,
												),
												'title' => esc_html__('Content', 'jobportal-framework'),
												'subtitle' => esc_html__('Available placeholders: %invoice_no, %total_price, %pdf_file, %user_login, %user_display_name, %user_email, %first_name, %last_name, %website_url, %website_name', 'jobportal-framework'),
												'default' => esc_html__(
													'Hello %user_display_name,

Thank you for your wire transfer payment request for your package purchase on %website_name.

📋 Payment Details:
• Invoice Number: %invoice_no
• Amount: %total_price
• Payment Method: Wire Transfer

⏳ What happens next:
1. We have received your payment request
2. Our team will verify your wire transfer payment
3. Once verified, your package will be activated automatically
4. You\'ll receive a confirmation email when your package is active

📄 Invoice:
Your invoice has been attached to this email. Please keep it for your records.

⏱️ Processing Time:
Wire transfer payments typically take 1-3 business days to process. We\'ll notify you as soon as your payment is confirmed.

If you have any questions about your payment, please contact our support team.

Thank you for your patience!

Best regards,
The %website_name Team',
													'jobportal-framework'
												),
											),
											array(
												'id' => 'jobportal_admin_mail_new_wire_transfer',
												'type' => 'info',
												'style' => 'info',
												'title' => esc_html__('Admin Email', 'jobportal-framework'),
											),
											array(
												'id' => 'subject_admin_mail_new_wire_transfer',
												'type' => 'text',
												'title' => esc_html__('Subject', 'jobportal-framework'),
												'default' => esc_html__('New Wire Transfer Payment Request Received', 'jobportal-framework'),
											),
											array(
												'id' => 'admin_mail_new_wire_transfer',
												'type' => 'editor',
												'args' => array(
													'media_buttons' => true,
													'quicktags' => true,
												),
												'title' => esc_html__('Content', 'jobportal-framework'),
												'subtitle' => esc_html__('Available placeholders: %invoice_no, %total_price, %pdf_file, %user_login, %user_display_name, %user_email, %first_name, %last_name, %website_url, %website_name', 'jobportal-framework'),
												'default' => esc_html__(
													'We received your Wire Transfer payment request on  %website_url !
										Please follow the instructions below in order to start submitting properties as soon as possible.
										The invoice number is: %invoice_no, Amount: %total_price.',
													'jobportal-framework'
												),
											)
										)
									),
									array(
										'id' => 'email-meetings',
										'title' => esc_html__('Notification Meetings', 'jobportal-framework'),
										'type' => 'group',
										'toggle_default' => false,
										'fields' => array(
											array(
												'id' => 'jobportal_info_mail_notification_meetings',
												'type' => 'info',
												'style' => 'info',
												'title' => esc_html__('Notification Meetings', 'jobportal-framework'),
											),
											array(
												'id' => 'subject_mail_notification_meetings',
												'type' => 'text',
												'title' => esc_html__('Subject', 'jobportal-framework'),
												'default' => esc_html__('You have a notification about the meeting', 'jobportal-framework'),
											),
											array(
												'id' => 'mail_notification_meetings',
												'type' => 'editor',
												'args' => array(
													'media_buttons' => true,
													'quicktags' => true,
												),
												'title' => esc_html__('Content', 'jobportal-framework'),
												'subtitle' => esc_html__('Available placeholders: %jobs_meetings, %date_time, %website_url, %website_name', 'jobportal-framework'),
												'default' => esc_html__(
													'There is a meeting coming up at %website_url!
												Job related meeting: %jobs_meetings!
												Time for the meeting to start %date_time.',
													'jobportal-framework'
												),
											),
										)
									)
								),
								apply_filters('jobportal_register_option_email_content_bottom', array()),
							)
						),
						//Footer
						array(
							'id' => 'email-footer',
							'title' => esc_html__('Footer Email', 'jobportal-framework'),
							'type' => 'group',
							'toggle_default' => false,
							'fields' => array(
								array(
									'id' => 'mail_footer_user',
									'type' => 'editor',
									'args' => array(
										'media_buttons' => true,
										'quicktags' => true,
									),
									'title' => esc_html__('Content', 'jobportal-framework'),
									'subtitle' => esc_html__('Available placeholders: %website_url, %website_name', 'jobportal-framework'),
									'default' => esc_html__(
										'Do you need help? Contact us
                                        T. (00) 658 54332
                                        E. hello@uxper.co
                                        © 2025 Uxper. All Right Reserved.',
										'jobportal-framework'
									),
								),
							)
						),
						// Reset Templates Button
						array(
							'id' => 'email-reset-templates',
							'type' => 'info',
							'style' => 'info',
							'title' => '',
							'desc' => '<div class="jobportal-reset-email-templates-wrap">
								<button type="button" id="jobportal-reset-email-templates-btn" class="button button-link-delete">
									' . esc_html__('Reset All Templates to Default', 'jobportal-framework') . '
								</button>
								<p class="description jobportal-reset-email-templates-description">' . esc_html__('Reset all email templates to theme default content.', 'jobportal-framework') . '</p>
								<div id="jobportal-reset-email-templates-message"></div>
							</div>',
						),
					),
					apply_filters('jobportal_register_option_email_management_bottom', array())
				)
			));
		}

		/**
		 * AJAX handler to reset all email templates to default
		 */
		public function ajax_reset_email_templates()
		{
			check_ajax_referer('jobportal_reset_email_templates', 'nonce');

			if (!current_user_can('manage_options')) {
				wp_send_json_error(array('message' => esc_html__('Permission denied', 'jobportal-framework')));
			}

			$email_templates = $this->get_all_email_template_defaults();

			$options = get_option('jobportal-framework', array());
			$reset_count = 0;

			foreach ($email_templates as $template_id => $default_value) {
				if (isset($default_value)) {
					$options[$template_id] = $default_value;
					$reset_count++;
				}
			}

			foreach ($email_templates as $template_id => $default_value) {
				$subject_id = 'subject_' . $template_id;
				if (isset($email_templates[$subject_id])) {
					$options[$subject_id] = $email_templates[$subject_id];
				}
			}

			update_option('jobportal-framework', $options);

			wp_send_json_success(array(
				'message' => sprintf(esc_html__('Successfully reset %d email templates to default values.', 'jobportal-framework'), $reset_count)
			));
		}

		/**
		 * Get all email template defaults
		 * @return array
		 */
		private function get_all_email_template_defaults()
		{
			$defaults = array();
			$email_option = $this->email_management_option();

			if (isset($email_option['fields']) && is_array($email_option['fields'])) {
				$this->extract_defaults_from_fields($email_option['fields'], $defaults);
			}

			return $defaults;
		}

		/**
		 * Recursively extract default values from fields
		 * @param array $fields
		 * @param array &$defaults
		 */
		private function extract_defaults_from_fields($fields, &$defaults)
		{
			foreach ($fields as $field) {
				if (isset($field['id']) && isset($field['default'])) {
					$defaults[$field['id']] = $field['default'];
				}

				if (isset($field['fields']) && is_array($field['fields'])) {
					$this->extract_defaults_from_fields($field['fields'], $defaults);
				}
			}
		}
	}
}
