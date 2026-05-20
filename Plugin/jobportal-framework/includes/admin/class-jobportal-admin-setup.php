<?php
if (!defined('ABSPATH')) {
	exit;
}

require_once JOBPORTAL_PLUGIN_DIR . 'includes/admin/class-jobportal-exporter.php';
if (!class_exists('JobPortal_Admin_Setup')) {
	/**
	 * Class JobPortal_Admin_Setup
	 */
	class JobPortal_Admin_Setup
	{
		/**
		 * admin_menu
		 */
		public function admin_menu()
		{
			add_menu_page(
				esc_html__('JobPortal', 'jobportal-framework'),
				esc_html__('JobPortal', 'jobportal-framework'),
				'manage_options',
				'jobportal_welcome',
				array($this, 'menu_welcome_page_callback'),
				JOBPORTAL_PLUGIN_URL . 'assets/images/icon.png',
				2
			);
			add_submenu_page(
				'jobportal_welcome',
				esc_html__('Welcome', 'jobportal-framework'),
				esc_html__('Welcome', 'jobportal-framework'),
				'manage_options',
				'jobportal_welcome',
				array($this, 'menu_welcome_page_callback')
			);
			add_submenu_page(
				'jobportal_welcome',
				esc_html__('System', 'jobportal-framework'),
				esc_html__('System', 'jobportal-framework'),
				'manage_options',
				'jobportal_system',
				array($this, 'system_page_callback')
			);

			if (defined('WP_DEBUG') && true === WP_DEBUG) {
				add_submenu_page(
					'jobportal_welcome',
					esc_html__('Export', 'jobportal-framework'),
					esc_html__('Export', 'jobportal-framework'),
					'manage_options',
					'jobportal_export',
					array($this, 'export_page_callback')
				);
			};

			add_submenu_page(
				'jobportal_welcome',
				esc_html__('Theme Options', 'jobportal-framework'),
				esc_html__('Theme Options', 'jobportal-framework'),
				'manage_options',
				'admin.php?page=jobportal-framework'
			);

			add_submenu_page(
				'jobportal_welcome',
				esc_html__('Setup Page', 'jobportal-framework'),
				esc_html__('Setup Page', 'jobportal-framework'),
				'manage_options',
				'jobportal_setup',
				array($this, 'setup_page')
			);

			add_menu_page(
				esc_html__('JobPortal Jobs', 'jobportal-framework'),
				esc_html__('JobPortal Jobs', 'jobportal-framework'),
				'manage_options',
				'jobportal_jobs',
				'',
				JOBPORTAL_PLUGIN_URL . 'assets/images/icon1.png',
				2,
				5
			);

			add_submenu_page(
				'jobportal_jobs',
				esc_html__('Jobs', 'jobportal-framework'),
				esc_html__('Jobs', 'jobportal-framework'),
				'manage_options',
				'edit.php?post_type=jobs'
			);
			add_submenu_page(
				'jobportal_jobs',
				esc_html__('Applicants', 'jobportal-framework'),
				esc_html__('Applicants', 'jobportal-framework'),
				'manage_options',
				'edit.php?post_type=applicants'
			);

			if (jobportal_get_option('enable_job_alerts') === '1') {
				add_submenu_page(
					'jobportal_jobs',
					esc_html__('Job Alerts', 'jobportal-framework'),
					esc_html__('Job Alerts', 'jobportal-framework'),
					'manage_options',
					'edit.php?post_type=job_alerts'
				);
			}

			add_menu_page(
				esc_html__('JobPortal Employer', 'jobportal-framework'),
				esc_html__('JobPortal Employer', 'jobportal-framework'),
				'manage_options',
				'jobportal_employer',
				'',
				JOBPORTAL_PLUGIN_URL . 'assets/images/icon2.png',
				7
			);

			add_submenu_page(
				'jobportal_employer',
				esc_html__('Companies', 'jobportal-framework'),
				esc_html__('Companies', 'jobportal-framework'),
				'manage_options',
				'edit.php?post_type=company'
			);

			add_submenu_page(
				'jobportal_employer',
				esc_html__('Package', 'jobportal-framework'),
				esc_html__('Package', 'jobportal-framework'),
				'manage_options',
				'edit.php?post_type=package'
			);

			add_submenu_page(
				'jobportal_employer',
				esc_html__('User Package', 'jobportal-framework'),
				esc_html__('User Package', 'jobportal-framework'),
				'manage_options',
				'edit.php?post_type=user_package'
			);

			add_submenu_page(
				'jobportal_employer',
				esc_html__('Invoice', 'jobportal-framework'),
				esc_html__('Invoice', 'jobportal-framework'),
				'manage_options',
				'edit.php?post_type=invoice'
			);

			if (jobportal_get_option('enable_post_type_service') === '1') {
				add_submenu_page(
					'jobportal_employer',
					esc_html__('Service Order', 'jobportal-framework'),
					esc_html__('Service Order', 'jobportal-framework'),
					'manage_options',
					'edit.php?post_type=service_order'
				);
				add_submenu_page(
					'jobportal_employer',
					esc_html__('Service Withdraw', 'jobportal-framework'),
					esc_html__('Service Withdraw', 'jobportal-framework'),
					'manage_options',
					'edit.php?post_type=service_withdraw'
				);
			}

			add_menu_page(
				esc_html__('JobPortal Candidates', 'jobportal-framework'),
				esc_html__('JobPortal Candidates', 'jobportal-framework'),
				'manage_options',
				'jobportal_candidate',
				'',
				JOBPORTAL_PLUGIN_URL . 'assets/images/icon3.png',
				12
			);

			add_submenu_page(
				'jobportal_candidate',
				esc_html__('Candidates', 'jobportal-framework'),
				esc_html__('Candidates', 'jobportal-framework'),
				'manage_options',
				'edit.php?post_type=candidate'
			);

			if (jobportal_get_option('enable_post_type_service') === '1') {
				add_submenu_page(
					'jobportal_candidate',
					esc_html__('Service', 'jobportal-framework'),
					esc_html__('Service', 'jobportal-framework'),
					'manage_options',
					'edit.php?post_type=service'
				);
			}

			add_submenu_page(
				'jobportal_candidate',
				esc_html__('Package', 'jobportal-framework'),
				esc_html__('Package', 'jobportal-framework'),
				'manage_options',
				'edit.php?post_type=candidate_package'
			);

			add_submenu_page(
				'jobportal_candidate',
				esc_html__('Order', 'jobportal-framework'),
				esc_html__('Order', 'jobportal-framework'),
				'manage_options',
				'edit.php?post_type=candidate_order'
			);

			add_menu_page(
				esc_html__('JobPortal Extensions', 'jobportal-framework'),
				esc_html__('JobPortal Extensions', 'jobportal-framework'),
				'manage_options',
				'jobportal_extensions',
				'',
				JOBPORTAL_PLUGIN_URL . 'assets/images/icon4.png',
				18
			);

			add_submenu_page(
				'jobportal_extensions',
				esc_html__('Package Coupon', 'jobportal-framework'),
				esc_html__('Package Coupon', 'jobportal-framework'),
				'manage_options',
				'edit.php?post_type=package_coupon'
			);

			add_submenu_page(
				'jobportal_extensions',
				esc_html__('Messages', 'jobportal-framework'),
				esc_html__('Messages', 'jobportal-framework'),
				'manage_options',
				'edit.php?post_type=messages'
			);

			add_submenu_page(
				'jobportal_extensions',
				esc_html__('Notification', 'jobportal-framework'),
				esc_html__('Notification', 'jobportal-framework'),
				'manage_options',
				'edit.php?post_type=notification'
			);

			add_submenu_page(
				'jobportal_extensions',
				esc_html__('Meetings', 'jobportal-framework'),
				esc_html__('Meetings', 'jobportal-framework'),
				'manage_options',
				'edit.php?post_type=meetings'
			);

			add_menu_page(
				esc_html__('JobPortal Builder', 'jobportal-framework'),
				esc_html__('JobPortal Builder', 'jobportal-framework'),
				'manage_options',
				'jobportal_builder',
				'',
				JOBPORTAL_PLUGIN_URL . 'assets/images/icon5.png',
				22
			);

			add_submenu_page(
				'jobportal_builder',
				esc_html__('Header', 'jobportal-framework'),
				esc_html__('Header', 'jobportal-framework'),
				'manage_options',
				'edit.php?post_type=jobportal_header'
			);

			add_submenu_page(
				'jobportal_builder',
				esc_html__('Footer', 'jobportal-framework'),
				esc_html__('Footer', 'jobportal-framework'),
				'manage_options',
				'edit.php?post_type=jobportal_footer'
			);

			add_submenu_page(
				'jobportal_builder',
				esc_html__('Mega Menu', 'jobportal-framework'),
				esc_html__('Mega Menu', 'jobportal-framework'),
				'manage_options',
				'edit.php?post_type=jobportal_mega_menu'
			);
		}

	public function reorder_admin_menu()
	{
		// Check if we're in a safe environment (not during import/reset)
		if ($this->is_import_or_reset_active()) {
			return;
		}

		// Remove default menu items safely
		$this->safe_remove_menu_page('tools.php');
		$this->safe_remove_menu_page('edit.php'); // Remove posts
		$this->safe_remove_menu_page('edit.php?post_type=page'); // Remove pages
		$this->safe_remove_menu_page('upload.php');
		$this->safe_remove_menu_page('themes.php');
		$this->safe_remove_menu_page('plugins.php');
		$this->safe_remove_menu_page('users.php');

		// Reorder menu items safely
		$this->safe_add_menu_page(esc_html__('Posts', 'jobportal-framework'), esc_html__('Posts', 'jobportal-framework'), 'edit_posts', 'edit.php', '', 'dashicons-admin-post', 26);
		$this->safe_add_menu_page(esc_html__('Media', 'jobportal-framework'), esc_html__('Media', 'jobportal-framework'), 'manage_options', 'upload.php', '', 'dashicons-admin-media', 27);
		$this->safe_add_menu_page(esc_html__('Pages', 'jobportal-framework'), esc_html__('Pages', 'jobportal-framework'), 'edit_pages', 'edit.php?post_type=page', '', 'dashicons-admin-page', 28);
		$this->safe_add_menu_page(esc_html__('Appearance', 'jobportal-framework'), esc_html__('Appearance', 'jobportal-framework'), 'edit_theme_options', 'themes.php', '', 'dashicons-admin-appearance', 30);
		$this->safe_add_menu_page(esc_html__('Plugins', 'jobportal-framework'), esc_html__('Plugins', 'jobportal-framework'), 'activate_plugins', 'plugins.php', '', 'dashicons-admin-plugins', 31);
		$this->safe_add_menu_page(esc_html__('Users', 'jobportal-framework'), esc_html__('Users', 'jobportal-framework'), 'promote_users', 'users.php', '', 'dashicons-admin-users', 32);
		$this->safe_add_menu_page(esc_html__('Tools', 'jobportal-framework'), esc_html__('Tools', 'jobportal-framework'), 'manage_options', 'tools.php', '', 'dashicons-admin-tools', 33);
	}

	/**
	 * Check if import or reset is currently active
	 */
	private function is_import_or_reset_active()
	{
		// Check for OCDI import
		if (defined('OCDI_VERSION') && (isset($_GET['import']) || isset($_POST['import']))) {
			return true;
		}

		// Check for WP Reset
		if (isset($_GET['wp_reset']) || isset($_POST['wp_reset']) ||
			(isset($_GET['page']) && strpos($_GET['page'], 'wp-reset') !== false)) {
			return true;
		}

		// Check for any demo import plugins
		if (isset($_GET['import']) || isset($_POST['import']) ||
			isset($_GET['demo']) || isset($_POST['demo'])) {
			return true;
		}

		return false;
	}

	/**
	 * Safely remove menu page
	 */
	private function safe_remove_menu_page($menu_slug)
	{
		try {
			// Check if menu exists before removing
			global $menu;
			$menu_exists = false;

			if (is_array($menu)) {
				foreach ($menu as $item) {
					if (isset($item[2]) && $item[2] === $menu_slug) {
						$menu_exists = true;
						break;
					}
				}
			}

			if ($menu_exists) {
				remove_menu_page($menu_slug);
			}
		} catch (Exception $e) {
			// Silently fail to prevent errors
		}
	}

	/**
	 * Safely add menu page
	 */
	private function safe_add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function = '', $icon_url = '', $position = null)
	{
		try {
			// Check if user has required capability
			if (!current_user_can($capability)) {
				return;
			}

			// Check if menu already exists
			global $menu;
			$menu_exists = false;

			if (is_array($menu)) {
				foreach ($menu as $item) {
					if (isset($item[2]) && $item[2] === $menu_slug) {
						$menu_exists = true;
						break;
					}
				}
			}

			if (!$menu_exists) {
				add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position);
			}
		} catch (Exception $e) {
			// Silently fail to prevent errors
		}
	}

		public function menu_welcome_page_callback()
		{
			if (isset($_POST['purchase_code'])) {
				$purchase_info = JobPortal_Updater::check_purchase_code(sanitize_key($_POST['purchase_code']));
				update_option('uxper_purchase_code', $_POST['purchase_code']);
			}
			$purchase_code = get_option('uxper_purchase_code');
			$purchase_class = '';
			$verified = '';
			$check_code = esc_html__('Not verified', 'jobportal-framework');
			if ($purchase_code) {
				$purchase_code_info = JobPortal_Updater::check_purchase_code($purchase_code);
				if ($purchase_code_info['status_code'] === 200) {
					$purchase_class = 'verified hidden-code';
					$verified = 'verified';
					$check_code = esc_html__('Verified', 'jobportal-framework');
				}
			}
?>

			<?php
			$update = JobPortal_Updater::check_theme_update();
			$new_version = isset($update['new_version']) ? $update['new_version'] : JOBPORTAL_THEME_VERSION;
			$get_info = JobPortal_Updater::get_info();
			if ($update) {
			?>
				<div class="alert-wrap alert-success about-wrap">
					<div class="msg-update">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
							<rect x="0" fill="none" width="24" height="24"></rect>
							<g>
								<path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm1 15h-2v-2h2v2zm0-4h-2l-.5-6h3l-.5 6z"></path>
							</g>
						</svg>

						<div class="inner-msg">
							<?php
							if (JobPortal_Updater::check_valid_update()) {

								printf(
									__(
										'There is a new version of %1$s available. <a href="%2$s" %3$s>View version %4$s details</a> or <a href="%5$s" %6$s>update now</a>.',
										'jobportal-framework'
									),
									JOBPORTAL_THEME_NAME,
									esc_url(add_query_arg(
										'action',
										'uxper_get_changelogs',
										admin_url('admin-ajax.php')
									)),
									sprintf(
										'class="thickbox" name="Changelogs" aria-label="%s"',
										esc_attr(sprintf(
											__('View %1$s version %2$s details'),
											JOBPORTAL_THEME_NAME,
											JOBPORTAL_THEME_VERSION
										))
									),
									$new_version,
									wp_nonce_url(
										self_admin_url('update.php?action=upgrade-theme&theme=') . JOBPORTAL_THEME_SLUG,
										'upgrade-theme_' . JOBPORTAL_THEME_SLUG
									),
									sprintf(
										'id="update-theme" aria-label="%s"',
										esc_attr(sprintf(__('Update %s now'), JOBPORTAL_THEME_NAME))
									)
								);
							} else {

								printf(
									__(
										'There is a new version of %1$s available. <strong>Please enter your purchase code to update the theme.</strong>',
										'jobportal-framework'
									),
									JOBPORTAL_THEME_NAME
								);
							}
							?>
						</div>
					</div>
				</div>
			<?php
			}
			?>

			<div class="jobportal-wrap wrap about-wrap purchase-wrap">
				<div class="entry-heading">
					<h4><?php esc_html_e('Purchase code', 'jobportal-framework'); ?><span class="check-code <?php esc_html_e($verified); ?>"><?php esc_html_e($check_code); ?></span>
					</h4>
				</div>

				<form action="" class="purchase-form <?php echo esc_attr($purchase_class); ?>" method="post">
					<span class="purchase-icon">
						<svg class="valid" fill="#000000" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" width="20px" height="20px">
							<path d="M 22.78125 0 C 21.605469 -0.00390625 20.40625 0.164063 19.21875 0.53125 C 12.902344 2.492188 9.289063 9.269531 11.25 15.59375 L 11.25 15.65625 C 11.507813 16.367188 12.199219 18.617188 12.625 20 L 9 20 C 7.355469 20 6 21.355469 6 23 L 6 47 C 6 48.644531 7.355469 50 9 50 L 41 50 C 42.644531 50 44 48.644531 44 47 L 44 23 C 44 21.355469 42.644531 20 41 20 L 14.75 20 C 14.441406 19.007813 13.511719 16.074219 13.125 15 L 13.15625 15 C 11.519531 9.722656 14.5 4.109375 19.78125 2.46875 C 25.050781 0.832031 30.695313 3.796875 32.34375 9.0625 C 32.34375 9.066406 32.34375 9.089844 32.34375 9.09375 C 32.570313 9.886719 33.65625 13.40625 33.65625 13.40625 C 33.746094 13.765625 34.027344 14.050781 34.386719 14.136719 C 34.75 14.226563 35.128906 14.109375 35.375 13.832031 C 35.621094 13.550781 35.695313 13.160156 35.5625 12.8125 C 35.5625 12.8125 34.433594 9.171875 34.25 8.53125 L 34.25 8.5 C 32.78125 3.761719 28.601563 0.542969 23.9375 0.0625 C 23.550781 0.0234375 23.171875 0 22.78125 0 Z M 9 22 L 41 22 C 41.554688 22 42 22.445313 42 23 L 42 47 C 42 47.554688 41.554688 48 41 48 L 9 48 C 8.445313 48 8 47.554688 8 47 L 8 23 C 8 22.445313 8.445313 22 9 22 Z M 25 30 C 23.300781 30 22 31.300781 22 33 C 22 33.898438 22.398438 34.6875 23 35.1875 L 23 38 C 23 39.101563 23.898438 40 25 40 C 26.101563 40 27 39.101563 27 38 L 27 35.1875 C 27.601563 34.6875 28 33.898438 28 33 C 28 31.300781 26.699219 30 25 30 Z" />
						</svg>

						<svg class="invalid" fill="#000000" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" width="20px" height="20px">
							<path d="M 25 3 C 18.363281 3 13 8.363281 13 15 L 13 20 L 9 20 C 7.355469 20 6 21.355469 6 23 L 6 47 C 6 48.644531 7.355469 50 9 50 L 41 50 C 42.644531 50 44 48.644531 44 47 L 44 23 C 44 21.355469 42.644531 20 41 20 L 37 20 L 37 15 C 37 8.363281 31.636719 3 25 3 Z M 25 5 C 30.566406 5 35 9.433594 35 15 L 35 20 L 15 20 L 15 15 C 15 9.433594 19.433594 5 25 5 Z M 9 22 L 41 22 C 41.554688 22 42 22.445313 42 23 L 42 47 C 42 47.554688 41.554688 48 41 48 L 9 48 C 8.445313 48 8 47.554688 8 47 L 8 23 C 8 22.445313 8.445313 22 9 22 Z M 25 30 C 23.300781 30 22 31.300781 22 33 C 22 33.898438 22.398438 34.6875 23 35.1875 L 23 38 C 23 39.101563 23.898438 40 25 40 C 26.101563 40 27 39.101563 27 38 L 27 35.1875 C 27.601563 34.6875 28 33.898438 28 33 C 28 31.300781 26.699219 30 25 30 Z" />
						</svg>
					</span>
					<input class="purchase-code" name="purchase_code" type="text" value="<?php echo esc_attr($purchase_code); ?>" placeholder="<?php esc_attr_e('Purchase code', 'jobportal-framework'); ?>" autocomplete="off" />
					<input type="submit" class="button action" value="Submit" />
				</form>
				<div class="purchase-desc">
					<?php
					if (isset($_POST['purchase_code'])) {
						$purchase_info = JobPortal_Updater::check_purchase_code(sanitize_key($_POST['purchase_code']));
						if ($purchase_info['status_code'] !== 200) {
							esc_html_e('The purchase code was invalid.', 'jobportal-framework');
						} else {
							esc_html_e('Success! The purchase code was valid.', 'jobportal-framework');
						}
					} else {
						if ($purchase_code) {
							$purchase_info = JobPortal_Updater::check_purchase_code($purchase_code);
							if ($purchase_info['status_code'] === 200) {
								esc_html_e('Please do not provide purchase code to anyone.', 'jobportal-framework');
							} else {
								esc_html_e('The purchase code was invalid. Please try again.', 'jobportal-framework');
							}
						} else {
							esc_html_e('Show us your ThemeForest purchase code to get the automatic update.', 'jobportal-framework');
						}
					}
					?>
				</div>
			</div>

			<div class="jobportal-wrap wrap about-wrap welcome-wrap">
				<div class="wrap-column wrap-column-2 col-started">
					<div class="panel-column column-content">
						<h3><?php esc_html_e('Welcome to JobPortal Theme', 'jobportal-framework'); ?></h3>
						<p><?php esc_html_e("We've assembled some links to get you started", 'jobportal-framework'); ?></p>
						<div class="entry-heading started">
							<h4><?php esc_html_e('Get Started', 'jobportal-framework'); ?></h4>
						</div>
						<div class="entry-detail">

							<a href="<?php echo esc_url(admin_url('admin.php?page=one-click-demo-import')); ?>" class="button button-primary"><?php esc_html_e('Install Sample Data', 'jobportal-framework'); ?></a>

							<p>
								<span><?php esc_html_e('or,', 'jobportal-framework') ?></span>
								<a href="<?php echo esc_url(admin_url('customize.php')); ?>"><?php esc_html_e('Customize your site', 'jobportal-framework'); ?></a>
							</p>
						</div>
						<div class="box-wrap">
							<div class="box-detail">
								<span class="entry-title"><?php esc_html_e('Current Version: ', 'jobportal-framework'); ?></span>
								<p><?php esc_html_e(JOBPORTAL_THEME_VERSION); ?></p>
							</div>
							<div class="box-detail">
								<span class="entry-title">
									<?php esc_html_e('Lastest Version: ', 'jobportal-framework'); ?>
									<?php
									if (JobPortal_Updater::check_valid_update() && $update) {

										printf(
											__(
												'<a class="button uxper-update" href="%1$s" %2$s>Update now</a>',
												'jobportal-framework'
											),
											wp_nonce_url(
												self_admin_url('update.php?action=upgrade-theme&theme=') . JOBPORTAL_THEME_SLUG,
												'upgrade-theme_' . JOBPORTAL_THEME_SLUG
											),
											sprintf(
												'id="update-theme" aria-label="%s"',
												esc_attr(sprintf(__('Update %s now'), JOBPORTAL_THEME_NAME))
											)
										);
									}
									?>
								</span>
								<p><?php esc_html_e($new_version); ?></p>
							</div>
						</div>
						<div class="entry-detail">
							<a class="entry-title" href="<?php echo esc_attr($get_info['docs']); ?>" target="_blank"><?php esc_html_e('Online Documentation', 'jobportal-framework'); ?>
								<i class="fas fa-external-link-alt"></i>
							</a>
							<a class="entry-title" href="<?php echo esc_attr($get_info['support']); ?>" target="_blank"><?php esc_html_e('Request Support', 'jobportal-framework'); ?>
								<i class="fas fa-external-link-alt"></i>
							</a>
						</div>
					</div>
					<div class="panel-column column-image">
						<img src="<?php echo JOBPORTAL_PLUGIN_URL . '/assets/images/img-welcome.jpg' ?>" alt="" />
					</div>
				</div>
			</div>

			<?php
			$jobportal_tgm_plugins = apply_filters('jobportal_tgm_plugins', array());
			$installed_plugins = class_exists('TGM_Plugin_Activation') ? TGM_Plugin_Activation::$instance->plugins : array();
			$required_plugins_count = 0;
			?>
			<div class="jobportal-wrap wrap about-wrap plugins-wrap">
				<div class="entry-heading">
					<h4><?php esc_html_e('Plugins', 'jobportal-framework'); ?></h4>
					<p><?php esc_html_e('Please install and activate plugins to use all functionality.', 'jobportal-framework'); ?></p>
				</div>

				<div class="wrap-content">
					<?php if (!empty($jobportal_tgm_plugins) && class_exists('TGM_Plugin_Activation')) : ?>
						<div class="grid columns-3">
							<?php foreach ($jobportal_tgm_plugins as $plugin) : ?>
								<?php
								$plugin_obj = $installed_plugins[$plugin['slug']];
								$css_class = '';
								if ($plugin['required']) {
									if (TGM_Plugin_Activation::$instance->is_plugin_active($plugin['slug'])) {
										$css_class .= 'plugin-activated';
									} else {
										$css_class .= 'plugin-deactivated';
									}
								}

								$thumb = isset($plugin['thumb']) ? esc_html($plugin['thumb']) : '';
								?>
								<div class="item <?php echo esc_attr($css_class); ?>">
									<div class="plugin-thumb">
										<img src="<?php echo esc_url($thumb); ?>" alt="<?php esc_html_e($plugin['name']); ?>">

										<div class="plugin-type">
											<span><?php echo $plugin['required'] ? esc_html__('Required', 'jobportal-framework') : esc_html__('Recommended', 'jobportal-framework'); ?></span>
										</div>
									</div>
									<div class="entry-detail">
										<div class="plugin-name">
											<span><?php esc_html_e($plugin['name']); ?></span>
											<sup><?php echo isset($plugin['version']) ? esc_html($plugin['version']) : ''; ?></sup>
										</div>

										<div class="plugin-action">
											<?php echo JobPortal_Plugins::get_plugin_action($plugin_obj); ?>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>

					<?php else : ?>

						<p><?php esc_html_e('This theme doesn\'t require any plugins.', 'jobportal-framework'); ?></p>

					<?php endif; ?>

				</div><!-- end .wrap-content -->
			</div>

			<div class="jobportal-wrap wrap about-wrap changelogs-wrap">
				<div class="entry-heading">
					<h4><?php esc_html_e('Changelogs', 'jobportal-framework'); ?></h4>
				</div>

				<div class="wrap-content">
					<table class="table-changelogs">
						<thead>
							<tr>
								<th><?php esc_html_e('Version', 'jobportal-framework'); ?></th>
								<th><?php esc_html_e('Description', 'jobportal-framework'); ?></th>
								<th><?php esc_html_e('Date', 'jobportal-framework'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php echo JobPortal_Updater::get_changelogs(true); ?>
						</tbody>
					</table>
				</div><!-- end .wrap-content -->
			</div>

		<?php
		}

		public function system_page_callback()
		{
			add_thickbox();
			function jobportal_core_let_to_num($size)
			{
				$l = substr($size, -1);
				$ret = substr($size, 0, -1);
				switch (strtoupper($l)) {
					case 'P':
						$ret *= 1024;
					case 'T':
						$ret *= 1024;
					case 'G':
						$ret *= 1024;
					case 'M':
						$ret *= 1024;
					case 'K':
						$ret *= 1024;
				}

				return $ret;
			}

		?>
			<div class="jobportal-system-page">
				<div class="about-wrap box">
					<div class="box-header">
						<span class="icon"><i class="lar la-lightbulb"></i></span>
						<?php esc_html_e('WordPress Environment', 'jobportal-framework'); ?>
					</div>
					<div class="box-body">
						<table class="wp-list-table widefat striped system" cellspacing="0">
							<tbody>
								<tr>
									<td class="help"><?php echo '<a href="#" class="hint--right" aria-label="' . esc_attr__('The URL of your site\'s homepage.', 'jobportal-framework') . '">[?]</a>'; ?></td>
									<td class="title"><?php _e('Home URL', 'jobportal-framework'); ?></td>
									<td><?php form_option('home'); ?></td>
								</tr>
								<tr>
									<td class="help"><?php echo '<a href="#" class="hint--right" aria-label="' . esc_attr__('The root URL of your site.', 'jobportal-framework') . '">[?]</a>'; ?></td>
									<td class="title"><?php _e('Site URL', 'jobportal-framework'); ?></td>
									<td><?php form_option('siteurl'); ?></td>
								</tr>
								<tr>
									<td class="help"><?php echo '<a href="#" class="hint--right" aria-label="' . esc_attr__('The version of WordPress installed on your site.', 'jobportal-framework') . '">[?]</a>'; ?></td>
									<td class="title"><?php _e('WP Version', 'jobportal-framework'); ?></td>
									<td><?php bloginfo('version'); ?></td>
								</tr>
								<tr>
									<td class="help"><?php echo '<a href="#" class="hint--right" aria-label="' . esc_attr__('Whether or not you have WordPress Multisite enabled.', 'jobportal-framework') . '">[?]</a>'; ?></td>
									<td class="title"><?php _e('WP Multisite', 'jobportal-framework'); ?></td>
									<td>
										<?php if (is_multisite()) {
											echo '&#10004;';
										} else {
											echo '&ndash;';
										} ?>
									</td>
								</tr>
								<tr>
									<td class="help"><?php echo '<a href="#" class="hint--right" aria-label="' . esc_attr__('The maximum amount of memory (RAM) that your site can use at one time.', 'jobportal-framework') . '">[?]</a>'; ?></td>
									<td class="title"><?php _e('WP Memory Limit', 'jobportal-framework'); ?></td>
									<td>
										<?php
										$memory = jobportal_core_let_to_num(WP_MEMORY_LIMIT);

										if (function_exists('memory_get_usage')) {
											$server_memory = jobportal_core_let_to_num(@ini_get('memory_limit'));
											$memory = max($memory, $server_memory);
										}

										if ($memory < 134217728) {
											echo '<mark class="error">' . sprintf(__('%s - We recommend setting memory to at least 128MB. See: <a href="%s" target="_blank">Increasing memory allocated to PHP</a>', 'jobportal-framework'), size_format($memory), 'http://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP') . '</mark>';
										} else {
											echo '<mark class="yes">' . size_format($memory) . '</mark>';
										}
										?>
									</td>
								</tr>
								<tr>
									<td class="help"><?php echo '<a href="#" class="hint--right" aria-label="' . esc_attr__('Displays whether or not WordPress is in Debug Mode.', 'jobportal-framework') . '">[?]</a>'; ?></td>
									<td class="title"><?php _e('WP Debug Mode', 'jobportal-framework'); ?></td>
									<td>
										<?php if (defined('WP_DEBUG') && WP_DEBUG) {
											echo '<mark class="yes">&#10004;</mark>';
										} else {
											echo '&ndash;';
										} ?>
									</td>
								</tr>
								<tr>
									<td class="help"><?php echo '<a href="#" class="hint--right" aria-label="' . esc_attr__('The current language used by WordPress. Default = English', 'jobportal-framework') . '">[?]</a>'; ?></td>
									<td class="title"><?php _e('Language', 'jobportal-framework'); ?></td>
									<td><?php echo get_locale() ?></td>
								</tr>
								<tr>
									<td class="help"><?php echo '<a href="#" class="hint--right" aria-label="' . esc_attr__('The current theme name', 'jobportal-framework') . '">[?]</a>'; ?></td>
									<td class="title"><?php _e('Theme Name', 'jobportal-framework'); ?></td>
									<td><?php echo JOBPORTAL_THEME_NAME; ?></td>
								</tr>
								<tr>
									<td class="help"><?php echo '<a href="#" class="hint--right" aria-label="' . esc_attr__('The current theme version', 'jobportal-framework') . '">[?]</a>'; ?></td>
									<td class="title"><?php _e('Theme Version', 'jobportal-framework'); ?></td>
									<td><?php echo JOBPORTAL_THEME_VERSION; ?></td>
								</tr>
								<tr>
									<td class="help"><?php echo '<a href="#" class="hint--right" aria-label="' . esc_attr__('Installed plugins', 'jobportal-framework') . '">[?]</a>'; ?></td>
									<td class="title"><?php _e('Activated Plugins', 'jobportal-framework'); ?></td>
									<td>
										<?php
										$all_plugins = get_plugins();
										foreach ($all_plugins as $key => $val) {
											if (is_plugin_active($key)) {
												echo $val['Name'] . ' ' . $val['Version'] . ', ';
											}
										}
										?>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<div class="about-wrap box">
					<div class="box-header">
						<span class="icon"><i class="lar la-lightbulb"></i></span>
						<?php esc_html_e('Server Environment', 'jobportal-framework'); ?>
					</div>
					<div class="box-body">
						<table class="wp-list-table widefat striped system" cellspacing="0">
							<tbody>
								<tr>
									<td class="help"><?php echo '<a href="#" class="hint--right" aria-label="' . esc_attr__('Information about the web server that is currently hosting your site.', 'jobportal-framework') . '">[?]</a>'; ?></td>
									<td class="title"><?php _e('Server Info', 'jobportal-framework'); ?></td>
									<td><?php esc_html_e($_SERVER['SERVER_SOFTWARE']); ?></td>
								</tr>
								<tr>
									<td class="help"><?php echo '<a href="#" class="hint--right" aria-label="' . esc_attr__('The version of PHP installed on your hosting server.', 'jobportal-framework') . '">[?]</a>'; ?></td>
									<td class="title"><?php _e('PHP Version', 'jobportal-framework'); ?></td>
									<td><?php if (function_exists('phpversion')) {
											$php_version = esc_html(phpversion());

											if (version_compare($php_version, '5.6', '<')) {
												echo '<mark class="error">' . esc_html__('JobPortal framework requires PHP version 5.6 or greater. Please contact your hosting provider to upgrade PHP version.', 'jobportal-framework') . '</mark>';
											} else {
												echo $php_version;
											}
										}
										?></td>
								</tr>
								<?php if (function_exists('ini_get')) : ?>
									<tr>
										<td class="help"><?php echo '<a href="#" class="hint--right" aria-label="' . esc_attr__('The largest filesize that can be contained in one post.', 'jobportal-framework') . '">[?]</a>'; ?></td>
										<td class="title"><?php _e('PHP Post Max Size', 'jobportal-framework'); ?></td>
										<td><?php echo size_format(jobportal_core_let_to_num(ini_get('post_max_size'))); ?></td>
									</tr>
									<tr>
										<td class="help"><?php echo '<a href="#" class="hint--right" aria-label="' . esc_attr__('The amount of time (in seconds) that your site will spend on a single operation before timing out (to avoid server lockups)', 'jobportal-framework') . '">[?]</a>'; ?></td>
										<td class="title"><?php _e('PHP Time Limit', 'jobportal-framework'); ?></td>
										<td><?php
											$time_limit = ini_get('max_execution_time');

											if ($time_limit > 0 && $time_limit < 180) {
												echo '<mark class="error">' . sprintf(__('%s - We recommend setting max execution time to at least 180. See: <a href="%s" target="_blank">Increasing max execution to PHP</a>', 'jobportal-framework'), $time_limit, 'http://codex.wordpress.org/Common_WordPress_Errors#Maximum_execution_time_exceeded') . '</mark>';
											} else {
												echo '<mark class="yes">' . $time_limit . '</mark>';
											}
											?></td>
									</tr>
									<tr>
										<td class="help"><?php echo '<a href="#" class="hint--right" aria-label="' . esc_attr__('The maximum number of variables your server can use for a single function to avoid overloads.', 'jobportal-framework') . '">[?]</a>'; ?></td>
										<td class="title"><?php _e('PHP Max Input Vars', 'jobportal-framework'); ?></td>
										<td><?php
											$max_input_vars = ini_get('max_input_vars');

											if ($max_input_vars < 5000) {
												echo '<mark class="error">' . sprintf(__('%s - Max input vars limitation will truncate POST data such as menus. Required >= 5000', 'jobportal-framework'), $max_input_vars) . '</mark>';
											} else {
												echo '<mark class="yes">' . $max_input_vars . '</mark>';
											}
											?></td>
									</tr>
								<?php endif; ?>
								<tr>
									<td class="help"><?php echo '<a href="#" class="hint--right" aria-label="' . esc_attr__('The version of MySQL installed on your hosting server.', 'jobportal-framework') . '">[?]</a>'; ?></td>
									<td class="title"><?php _e('MySQL Version', 'jobportal-framework'); ?></td>
									<td>
										<?php
										global $wpdb;
										echo $wpdb->db_version();
										?>
									</td>
								</tr>
								<tr>
									<td class="help"><?php echo '<a href="#" class="hint--right" aria-label="' . esc_attr__('The largest filesize that can be uploaded to your WordPress installation.', 'jobportal-framework') . '">[?]</a>'; ?></td>
									<td class="title"><?php _e('Max Upload Size', 'jobportal-framework'); ?></td>
									<td><?php echo size_format(wp_max_upload_size()); ?></td>
								</tr>
								<tr>
									<td class="help"><?php echo '<a href="#" class="hint--right" aria-label="' . esc_attr__('The default timezone for your server.', 'jobportal-framework') . '">[?]</a>'; ?></td>
									<td class="title"><?php _e('Default Timezone is UTC', 'jobportal-framework'); ?></td>
									<td><?php
										$default_timezone = date_default_timezone_get();
										if ('UTC' !== $default_timezone) {
											echo '<mark class="error">&#10005; ' . sprintf(__('Default timezone is %s - it should be UTC', 'jobportal-framework'), $default_timezone) . '</mark>';
										} else {
											echo '<mark class="yes">&#10004;</mark>';
										} ?>
									</td>
								</tr>
								<?php
								$checks = array();
								// fsockopen/cURL
								$checks['fsockopen_curl']['name'] = 'fsockopen/cURL';
								$checks['fsockopen_curl']['help'] = '<a href="#" class="hint--right" aria-label="' . esc_attr__('Plugins may use it when communicating with remote services.', 'jobportal-framework') . '">[?]</a>';
								if (function_exists('fsockopen') || function_exists('curl_init')) {
									$checks['fsockopen_curl']['success'] = true;
								} else {
									$checks['fsockopen_curl']['success'] = false;
									$checks['fsockopen_curl']['note'] = __('Your server does not have fsockopen or cURL enabled. Please contact your hosting provider to enable it.', 'jobportal-framework') . '</mark>';
								}
								// DOMDocument
								$checks['dom_document']['name'] = 'DOMDocument';
								$checks['dom_document']['help'] = '<a href="#" class="hint--right" aria-label="' . esc_attr__('WordPress Importer use DOMDocument.', 'jobportal-framework') . '">[?]</a>';
								if (class_exists('DOMDocument')) {
									$checks['dom_document']['success'] = true;
								} else {
									$checks['dom_document']['success'] = false;
									$checks['dom_document']['note'] = sprintf(__('Your server does not have <a href="%s">the DOM extension</a> class enabled. Please contact your hosting provider to enable it.', 'jobportal-framework'), 'http://php.net/manual/en/intro.dom.php') . '</mark>';
								}
								// XMLReader
								$checks['xml_reader']['name'] = 'XMLReader';
								$checks['xml_reader']['help'] = '<a href="#" class="hint--right" aria-label="' . esc_attr__('WordPress Importer use XMLReader.', 'jobportal-framework') . '">[?]</a>';
								if (class_exists('XMLReader')) {
									$checks['xml_reader']['success'] = true;
								} else {
									$checks['xml_reader']['success'] = false;
									$checks['xml_reader']['note'] = sprintf(__('Your server does not have <a href="%s">the XMLReader extension</a> class enabled. Please contact your hosting provider to enable it.', 'jobportal-framework'), 'http://php.net/manual/en/intro.xmlreader.php') . '</mark>';
								}
								// WP Remote Get Check
								$checks['wp_remote_get']['name'] = __('Remote Get', 'jobportal-framework');
								$checks['wp_remote_get']['help'] = '<a href="#" class="hint--right" aria-label="' . esc_attr__('Retrieve the raw response from the HTTP request using the GET method.', 'jobportal-framework') . '">[?]</a>';
								$response = wp_remote_get(JOBPORTAL_PLUGIN_URL . 'assets/test.txt');

								if (!is_wp_error($response) && $response['response']['code'] >= 200 && $response['response']['code'] < 300) {
									$checks['wp_remote_get']['success'] = true;
								} else {
									$checks['wp_remote_get']['note'] = __(' WordPress function <a href="https://codex.wordpress.org/Function_Reference/wp_remote_get">wp_remote_get()</a> test failed. Please contact your hosting provider to enable it.', 'jobportal-framework');
									if (is_wp_error($response)) {
										$checks['wp_remote_get']['note'] .= ' ' . sprintf(__('Error: %s', 'jobportal-framework'), sanitize_text_field($response->get_error_message()));
									} else {
										$checks['wp_remote_get']['note'] .= ' ' . sprintf(__('Status code: %s', 'jobportal-framework'), sanitize_text_field($response['response']['code']));
									}
									$checks['wp_remote_get']['success'] = false;
								}
								foreach ($checks as $check) {
									$mark = !empty($check['success']) ? 'yes' : 'error';
								?>
									<tr>
										<td class="help"><?php echo isset($check['help']) ? $check['help'] : ''; ?></td>
										<td class="title"><?php esc_html_e($check['name']); ?></td>
										<td>
											<mark class="<?php echo $mark; ?>">
												<?php echo !empty($check['success']) ? '&#10004' : '&#10005'; ?><?php echo !empty($check['note']) ? wp_kses_data($check['note']) : ''; ?>
											</mark>
										</td>
									</tr>
								<?php
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		<?php
		}

		public function export_page_callback()
		{
			$export_items = JobPortal_Exporter::get_export_items();
		?>
			<div class="about-wrap jobportal-box jobportal-box--gray jobportal-box--export">
				<div class="jobportal-box__body grid columns-3">

					<?php
					/**
					 * Action: jobportal_box_export_before_content
					 */
					do_action('jobportal_box_export_before_content');
					?>

					<?php if (!empty($export_items)) : ?>
						<?php foreach ($export_items as $item) : ?>
							<?php if (isset($item['name'], $item['action'], $item['icon'])) : ?>
								<!-- Export <?php esc_html_e($item['name']); ?>-->
								<div class="jobportal-export-item jobportal-export-item--<?php echo esc_attr(sanitize_title($item['name'])); ?>">
									<form action="<?php echo esc_url(admin_url('/admin-post.php')); ?>" method="POST" class="jobportal-export-item__form">
										<?php if (isset($item['description'])) : ?>
											<span class="jobportal-export-item__help hint--right" aria-label="<?php echo esc_attr($item['description']); ?>"><i class="fal fa-question-circle"></i></span>
										<?php endif; ?>

										<input type="hidden" name="_wpnonce" value="<?php echo esc_attr(wp_create_nonce($item['action'])); ?>">
										<input type="hidden" name="action" value="<?php echo esc_attr($item['action']); ?>">

										<p class="jobportal-export-item__name"><i class="<?php echo esc_attr($item['icon']); ?>"></i><?php esc_html_e($item['name']); ?>
										</p>

										<p class="jobportal-export-item__description"><?php esc_html_e($item['description']); ?></p>

										<div class="jobportal-export-item__icon<?php echo esc_attr(isset($item['input_file_name']) && $item['input_file_name'] ? ' jobportal-export-item__icon--has-file-name-input' : ''); ?>">

											<?php if (isset($item['input_file_name'], $item['default_file_name']) && $item['input_file_name']) : ?>
												<input type="text" name="<?php echo esc_attr(sanitize_title($item['name']) . '-file-name'); ?>" id="<?php echo esc_attr(sanitize_title($item['name']) . '-file-name'); ?>" class="jobportal-export-item__input" value="<?php echo esc_attr($item['default_file_name']); ?>">
											<?php endif; ?>
										</div>

										<div class="jobportal-export-item__footer">
											<?php if (isset($item['export_page_url']) && !empty($item['export_page_url'])) : ?>
												<a href="<?php echo esc_url($item['export_page_url']); ?>" class="button jobportal-export-item__button"><?php esc_html_e('Export', 'jobportal-framework'); ?>
													<i class="las la-download"></i></a>
											<?php else : ?>
												<button type="submit" name="export" class="button jobportal-export-item__button"><?php esc_html_e('Export', 'jobportal-framework'); ?>
													<i class="las la-download"></i></button>
											<?php endif; ?>
										</div>
									</form>
								</div>
								<!-- /Export <?php esc_html_e($item['name']); ?> -->
							<?php endif; ?>
						<?php endforeach; ?>
					<?php endif; ?>

					<?php
					/**
					 * Action: jobportal_box_export_after_content
					 */
					do_action('jobportal_box_export_after_content');
					?>
				</div>
			</div>
		<?php
		}

		/**
		 * Redirect the setup page on first activation
		 */
		public function redirect()
		{
			// Bail if no activation redirect transient is set
			if (!get_transient('_jobportal_activation_redirect')) {
				return;
			}

			if (!current_user_can('manage_options')) {
				return;
			}

			// Delete the redirect transient
			delete_transient('_jobportal_activation_redirect');

			// Bail if activating from network, or bulk, or within an iFrame
			if (is_network_admin() || isset($_GET['activate-multi']) || defined('IFRAME_REQUEST')) {
				return;
			}

			if ((isset($_GET['action']) && 'upgrade-plugin' == $_GET['action']) && (isset($_GET['plugin']) && strstr($_GET['plugin'], 'jobportal-framework.php'))) {
				return;
			}

			wp_redirect(admin_url('admin.php?page=jobportal_setup'));
			exit;
		}

		/**
		 * Create page on first activation
		 * @param $title
		 * @param $content
		 * @param $option
		 */
		private function create_page($title, $content, $option)
		{
			$page_data = array(
				'post_status' => 'publish',
				'post_type' => 'page',
				'post_author' => 1,
				'post_name' => sanitize_title($title),
				'post_title' => $title,
				'post_content' => $content,
				'post_parent' => 0,
				'comment_status' => 'closed'
			);
			$page_id = wp_insert_post($page_data);
			if ($option) {
				if (function_exists('pll_the_languages')) {
					$config = get_option(pll_current_language() . '_jobportal-framework');
					$config[$option] = $page_id;
					update_option(pll_current_language() . '_jobportal-framework', $config);
				} else if (defined('ICL_SITEPRESS_VERSION')) {
					$current_language = apply_filters('wpml_current_language', NULL);
					if ($current_language) {
						$config = get_option($current_language . '_jobportal-framework');
						$config[$option] = $page_id;
						update_option($current_language . '_jobportal-framework', $config);
					} else {
						$config = get_option('jobportal-framework');
						$config[$option] = $page_id;
						update_option('jobportal-framework', $config);
					}
				} else {
					$config = get_option('jobportal-framework');
					$config[$option] = $page_id;
					update_option('jobportal-framework', $config);
				}
			}
		}

		/**
		 * Output page setup
		 */
		public function setup_page()
		{
			$step = !empty($_GET['step']) ? absint(wp_unslash($_GET['step'])) : 1;
			if (3 === $step && !empty($_POST)) {
				$create_pages = isset($_POST['jobportal-create-page']) ? jobportal_clean(wp_unslash($_POST['jobportal-create-page'])) : array();
				$page_titles = isset($_POST['jobportal-page-title']) ? jobportal_clean(wp_unslash($_POST['jobportal-page-title'])) : array();
				$pages_to_create = array(
					'dashboard' => '[jobportal_dashboard]',
					'candidate_dashboard' => '[jobportal_candidates]',
					'meetings' => '[jobportal_meetings]',
					'candidate_meetings' => '[jobportal_candidate_meetings]',
					'employer_settings' => '[jobportal_settings]',
					'candidate_settings' => '[jobportal_candidate_settings]',
					'jobs_dashboard' => '[jobportal_jobs]',
					'jobs_submit' => '[jobportal_jobs_submit]',
					'jobs_performance' => '[jobportal_jobs_performance]',
					'applicants' => '[jobportal_applicants]',
					'candidates' => '[jobportal_candidates]',
					'user_package' => '[jobportal_user_package]',
					'company' => '[jobportal_company]',
					'my_jobs' => '[jobportal_my_jobs]',
					'messages' => '[jobportal_messages]',
					'package' => '[jobportal_package]',
					'payment' => '[jobportal_payment]',
					'payment_service' => '[jobportal_service_payment]',
					'service_payment_completed' => '[jobportal_service_payment_completed]',
					'candidate_company' => '[jobportal_candidate_company]',
					'payment_completed' => '[jobportal_payment_completed]',
					'candidate_reviews' => '[jobportal_candidate_my_review]',
					'candidate_profile' => '[jobportal_candidate_profile]',
					'candidate_user_package' => '[jobportal_candidate_user_package]',
					'employer_service' => '[jobportal_employer_service]',
					'candidate_service' => '[jobportal_candidate_service]',
					'submit_service' => '[jobportal_submit_service]',
					'candidate_package' => '[jobportal_candidate_package]',
					'candidate_payment' => '[jobportal_candidate_payment]',
					'candidate_payment_completed' => '[jobportal_candidate_payment_completed]',
				);
				foreach ($pages_to_create as $page => $content) {
					if (!isset($create_pages[$page]) || empty($page_titles[$page])) {
						continue;
					}
					$this->create_page(sanitize_text_field($page_titles[$page]), $content, 'jobportal_' . $page . '_page_id');
				}
			}
		?>
			<div class="jobportal-setup-wrap jobportal-wrap about-wrap setup-wrap">
				<h3><?php esc_html_e('JobPortal Setup', 'jobportal-framework'); ?></h3>
				<ul class="jobportal-setup-steps">
					<li class="<?php if ($step === 1) echo 'jobportal-setup-active-step'; ?>"><?php esc_html_e('1. Introduction', 'jobportal-framework'); ?></li>
					<li class="<?php if ($step === 2) echo 'jobportal-setup-active-step'; ?>"><?php esc_html_e('2. Page Setup', 'jobportal-framework'); ?></li>
					<li class="<?php if ($step === 3) echo 'jobportal-setup-active-step'; ?>"><?php esc_html_e('3. Done', 'jobportal-framework'); ?></li>
				</ul>

				<?php if (1 === $step) : ?>

					<h3><?php esc_html_e('Setup Wizard Introduction', 'jobportal-framework'); ?></h3>
					<p><?php _e('Thanks for installing <em>JobPortal</em></em>!', 'jobportal-framework'); ?></p>
					<p><?php esc_html_e('This setup wizard will help you get started by creating the pages for jobs submission, jobs management, profile management, listing jobs, jobs wishlist, jobs booking...', 'jobportal-framework'); ?></p>
					<p><?php printf(__('If you want to skip the wizard and setup the pages and shortcodes yourself manually, the process is still relatively simple. Refer to the %sdocumentation%s for help.', 'jobportal-framework'), '<a href="#"', '</a>'); ?></p>

					<p class="submit">
						<a href="<?php echo esc_url(add_query_arg('step', 2)); ?>" class="button button-primary"><?php esc_html_e('Continue to page setup', 'jobportal-framework'); ?></a>
						<a href="<?php echo esc_url(admin_url('admin.php?page=jobportal_setup&step=3')); ?>" class="button"><?php esc_html_e('Skip setup. I will setup the plugin manually (Not Recommended)', 'jobportal-framework'); ?></a>
					</p>

				<?php endif; ?>
				<?php if (2 === $step) : ?>

					<h3><?php esc_html_e('Page Setup', 'jobportal-framework'); ?></h3>

					<p><?php printf(__('<em>jobportal-framework</em> includes %1$sshortcodes%2$s which can be used within your %3$spages%2$s to output content. These can be created for you below. For more information on the jobportal-framework shortcodes view the %4$sshortcode documentation%2$s.', 'jobportal-framework'), '<a href="https://codex.wordpress.org/shortcode" title="What is a shortcode?" target="_blank" class="help-page-link">', '</a>', '<a href="http://codex.wordpress.org/Pages" target="_blank" class="help-page-link">', '<a href="#" target="_blank" class="help-page-link">'); ?></p>

					<form action="<?php echo esc_url(add_query_arg('step', 3)); ?>" method="post">
						<table class="jobportal-shortcodes widefat">
							<thead>
								<tr>
									<th>&nbsp;</th>
									<th><?php esc_html_e('Page Title', 'jobportal-framework'); ?></th>
									<th><?php esc_html_e('Page Description', 'jobportal-framework'); ?></th>
									<th><?php esc_html_e('Content Shortcode', 'jobportal-framework'); ?></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td><input type="checkbox" checked="checked" name="jobportal-create-page[dashboard]" /></td>
									<td><input type="text" value="<?php echo esc_attr(_x('Dashboard Employer', 'Default page title (wizard)', 'jobportal-framework')); ?>" name="jobportal-page-title[dashboard]" /></td>
									<td>
										<p><?php esc_html_e('This page show dashboard.', 'jobportal-framework'); ?></p>
									</td>
									<td><code>[jobportal_dashboard]</code></td>
								</tr>
								<tr>
									<td><input type="checkbox" checked="checked" name="jobportal-create-page[jobs_performance]" /></td>
									<td><input type="text" value="<?php echo esc_attr(_x('Jobs Performance', 'Default page title (wizard)', 'jobportal-framework')); ?>" name="jobportal-page-title[jobs_performance]" /></td>
									<td>
										<p><?php esc_html_e('This page show jobs performance.', 'jobportal-framework'); ?></p>
									</td>
									<td><code>[jobportal_jobs_performance]</code></td>
								</tr>
								<tr>
									<td><input type="checkbox" checked="checked" name="jobportal-create-page[jobs]" /></td>
									<td><input type="text" value="<?php echo esc_attr(_x('Jobs', 'Default page title (wizard)', 'jobportal-framework')); ?>" name="jobportal-page-title[jobs]" /></td>
									<td>
										<p><?php esc_html_e('This page show all jobs.', 'jobportal-framework'); ?></p>
									</td>
									<td><code>[jobportal_jobs]</code></td>
								</tr>
								<tr>
									<td><input type="checkbox" checked="checked" name="jobportal-create-page[submit_jobs]" /></td>
									<td><input type="text" value="<?php echo esc_attr(_x('New Jobs', 'Default page title (wizard)', 'jobportal-framework')); ?>" name="jobportal-page-title[submit_jobs]" /></td>
									<td>
										<p><?php esc_html_e('This page allows users to add jobs to your website via the front-end.', 'jobportal-framework'); ?></p>
									</td>
									<td><code>[jobportal_jobs_submit]</code></td>
								</tr>
								<tr>
									<td><input type="checkbox" checked="checked" name="jobportal-create-page[applicants]" />
									</td>
									<td><input type="text" value="<?php echo esc_attr(_x('Applicants', 'Default page title (wizard)', 'jobportal-framework')); ?>" name="jobportal-page-title[applicants]" /></td>
									<td>
										<p><?php esc_html_e('This page allows users to view their own "Applicants" via the front-end.', 'jobportal-framework'); ?></p>
									</td>
									<td><code>[jobportal_applicants]</code></td>
								</tr>
								<tr>
									<td><input type="checkbox" checked="checked" name="jobportal-create-page[jobportal_candidates]" /></td>
									<td><input type="text" value="<?php echo esc_attr(_x('Candidates For Employer', 'Default page title (wizard)', 'jobportal-framework')); ?>" name="jobportal-page-title[jobportal_candidates]" /></td>
									<td>
										<p><?php esc_html_e('This page allows users to view their own "Candidates For Employer" via the front-end.', 'jobportal-framework'); ?></p>
									</td>
									<td><code>[jobportal_candidates]</code></td>
								</tr>
								<tr>
									<td><input type="checkbox" checked="checked" name="jobportal-create-page[package]" /></td>
									<td><input type="text" value="<?php echo esc_attr(_x('User Packages', 'Default page title (wizard)', 'jobportal-framework')); ?>" name="jobportal-page-title[package]" /></td>
									<td>
										<p><?php esc_html_e('This page allows users to view their own "User Package" via the front-end.', 'jobportal-framework'); ?></p>
									</td>
									<td><code>[jobportal_user_package]</code></td>
								</tr>
								<tr>
									<td><input type="checkbox" checked="checked" name="jobportal-create-page[messages]" /></td>
									<td><input type="text" value="<?php echo esc_attr(_x('Messages Employer', 'Default page title (wizard)', 'jobportal-framework')); ?>" name="jobportal-page-title[messages]" /></td>
									<td>
										<p><?php esc_html_e('This page allows users to view their own "Messages Employer" via the front-end.', 'jobportal-framework'); ?></p>
									</td>
									<td><code>[jobportal_messages]</code></td>
								</tr>
								<tr>
									<td><input type="checkbox" checked="checked" name="jobportal-create-page[company]" /></td>
									<td><input type="text" value="<?php echo esc_attr(_x('Company', 'Default page title (wizard)', 'jobportal-framework')); ?>" name="jobportal-page-title[packages]" /></td>
									<td>
										<p><?php esc_html_e('This page allows users to view their own "Company" via the front-end.', 'jobportal-framework'); ?></p>
									</td>
									<td><code>[jobportal_company]</code></td>
								</tr>
								<tr>
									<td><input type="checkbox" checked="checked" name="jobportal-create-page[submit_company]" /></td>
									<td><input type="text" value="<?php echo esc_attr(_x('New Company', 'Default page title (wizard)', 'jobportal-framework')); ?>" name="jobportal-page-title[submit_company]" /></td>
									<td>
										<p><?php esc_html_e('This page allows users to view their own "Company" via the front-end.', 'jobportal-framework'); ?></p>
									</td>
									<td><code>[jobportal_submit_company]</code></td>
								</tr>
								<tr>
									<td><input type="checkbox" checked="checked" name="jobportal-create-page[settings]" /></td>
									<td><input type="text" value="<?php echo esc_attr(_x('Settings Employer', 'Default page title (wizard)', 'jobportal-framework')); ?>" name="jobportal-page-title[settings]" /></td>
									<td>
										<p><?php esc_html_e('This page allows users to view their own "Settings Employer" via the front-end.', 'jobportal-framework'); ?></p>
									</td>
									<td><code>[jobportal_settings]</code></td>
								</tr>
								<tr>
									<td><input type="checkbox" checked="checked" name="jobportal-create-page[meetings]" /></td>
									<td><input type="text" value="<?php echo esc_attr(_x('Meetings Employer', 'Default page title (wizard)', 'jobportal-framework')); ?>" name="jobportal-page-title[meetings]" /></td>
									<td>
										<p><?php esc_html_e('This page allows users to view their own "Meetings Employer" via the front-end.', 'jobportal-framework'); ?></p>
									</td>
									<td><code>[jobportal_meetings]</code></td>
								</tr>
								<tr>
									<td><input type="checkbox" checked="checked" name="jobportal-create-page[package]" /></td>
									<td><input type="text" value="<?php echo esc_attr(_x('Packages', 'Default page title (wizard)', 'jobportal-framework')); ?>" name="jobportal-page-title[package]" /></td>
									<td>
										<p><?php esc_html_e('This page allows users to view their own "Packages" via the front-end.', 'jobportal-framework'); ?></p>
									</td>
									<td><code>[jobportal_package]</code></td>
								</tr>
								<tr>
									<td><input type="checkbox" checked="checked" name="jobportal-create-page[payment]" /></td>
									<td><input type="text" value="<?php echo esc_attr(_x('Payment', 'Default page title (wizard)', 'jobportal-framework')); ?>" name="jobportal-page-title[payment]" /></td>
									<td>
										<p><?php esc_html_e('This page allows users to view their own "Payment" via the front-end.', 'jobportal-framework'); ?></p>
									</td>
									<td><code>[jobportal_payment]</code></td>
								</tr>
								<tr>
									<td><input type="checkbox" checked="checked" name="jobportal-create-page[payment_completed]" /></td>
									<td><input type="text" value="<?php echo esc_attr(_x('Payment Completed', 'Default page title (wizard)', 'jobportal-framework')); ?>" name="jobportal-page-title[payment_completed]" /></td>
									<td>
										<p><?php esc_html_e('This page allows users to view their own "Payment Completed" via the front-end.', 'jobportal-framework'); ?></p>
									</td>
									<td><code>[jobportal_payment_completed]</code></td>
								</tr>
								<tr>
									<td><input type="checkbox" checked="checked" name="jobportal-create-page[candidate_dashboard]" /></td>
									<td><input type="text" value="<?php echo esc_attr(_x('Dashboard Candidate', 'Default page title (wizard)', 'jobportal-framework')); ?>" name="jobportal-page-title[candidate_dashboard]" /></td>
									<td>
										<p><?php esc_html_e('This page allows users to view their own "Dashboard Candidate" via the front-end.', 'jobportal-framework'); ?></p>
									</td>
									<td><code>[jobportal_candidate_dashboard]</code></td>
								</tr>
								<tr>
									<td><input type="checkbox" checked="checked" name="jobportal-create-page[candidate_settings]" /></td>
									<td><input type="text" value="<?php echo esc_attr(_x('Candidate Settings', 'Default page title (wizard)', 'jobportal-framework')); ?>" name="jobportal-page-title[candidate_settings]" /></td>
									<td>
										<p><?php esc_html_e('This page allows users to view their own "Candidate Settings" via the front-end.', 'jobportal-framework'); ?></p>
									</td>
									<td><code>[jobportal_candidate_settings]</code></td>
								</tr>
								<tr>
									<td><input type="checkbox" checked="checked" name="jobportal-create-page[candidate_company]" /></td>
									<td><input type="text" value="<?php echo esc_attr(_x('Candidate Company', 'Default page title (wizard)', 'jobportal-framework')); ?>" name="jobportal-page-title[candidate_company]" /></td>
									<td>
										<p><?php esc_html_e('This page allows users to view their own "Candidate Company" via the front-end.', 'jobportal-framework'); ?></p>
									</td>
									<td><code>[jobportal_candidate_company]</code></td>
								</tr>
								<tr>
									<td><input type="checkbox" checked="checked" name="jobportal-create-page[candidate_profile]" /></td>
									<td><input type="text" value="<?php echo esc_attr(_x('Candidate Profile', 'Default page title (wizard)', 'jobportal-framework')); ?>" name="jobportal-page-title[candidate_profile]" /></td>
									<td>
										<p><?php esc_html_e('This page allows users to view their own "Candidate Profile" via the front-end.', 'jobportal-framework'); ?></p>
									</td>
									<td><code>[jobportal_candidate_profile]</code></td>
								</tr>
								<tr>
									<td><input type="checkbox" checked="checked" name="jobportal-create-page[my_review]" /></td>
									<td><input type="text" value="<?php echo esc_attr(_x('My Review', 'Default page title (wizard)', 'jobportal-framework')); ?>" name="jobportal-page-title[my_review]" /></td>
									<td>
										<p><?php esc_html_e('This page allows users to view their own "My Review" via the front-end.', 'jobportal-framework'); ?></p>
									</td>
									<td><code>[jobportal_candidate_my_review]</code></td>
								</tr>
								<tr>
									<td><input type="checkbox" checked="checked" name="jobportal-create-page[candidate_meetings]" /></td>
									<td><input type="text" value="<?php echo esc_attr(_x('Candidate Meetings', 'Default page title (wizard)', 'jobportal-framework')); ?>" name="jobportal-page-title[candidate_meetings]" /></td>
									<td>
										<p><?php esc_html_e('This page allows users to view their own "Candidate Meetings" via the front-end.', 'jobportal-framework'); ?></p>
									</td>
									<td><code>[jobportal_candidate_meetings]</code></td>
								</tr>
								<tr>
									<td><input type="checkbox" checked="checked" name="jobportal-create-page[candidate_user_package]" /></td>
									<td><input type="text" value="<?php echo esc_attr(_x('Candidate User Package', 'Default page title (wizard)', 'jobportal-framework')); ?>" name="jobportal-page-title[candidate_user_package]" /></td>
									<td>
										<p><?php esc_html_e('This page allows users to view their own "Candidate User Package" via the front-end.', 'jobportal-framework'); ?></p>
									</td>
									<td><code>[jobportal_candidate_user_package]</code></td>
								</tr>
								<tr>
									<td><input type="checkbox" checked="checked" name="jobportal-create-page[candidate_package]" /></td>
									<td><input type="text" value="<?php echo esc_attr(_x('Candidate Package', 'Default page title (wizard)', 'jobportal-framework')); ?>" name="jobportal-page-title[candidate_package]" /></td>
									<td>
										<p><?php esc_html_e('This page allows users to view their own "Candidate Package" via the front-end.', 'jobportal-framework'); ?></p>
									</td>
									<td><code>[jobportal_candidate_package]</code></td>
								</tr>
								<tr>
									<td><input type="checkbox" checked="checked" name="jobportal-create-page[candidate_payment]" /></td>
									<td><input type="text" value="<?php echo esc_attr(_x('Candidate Payment', 'Default page title (wizard)', 'jobportal-framework')); ?>" name="jobportal-page-title[candidate_payment]" /></td>
									<td>
										<p><?php esc_html_e('This page allows users to view their own "Candidate Payment" via the front-end.', 'jobportal-framework'); ?></p>
									</td>
									<td><code>[jobportal_candidate_payment]</code></td>
								</tr>
								<tr>
									<td><input type="checkbox" checked="checked" name="jobportal-create-page[candidate_payment_completed]" /></td>
									<td><input type="text" value="<?php echo esc_attr(_x('Candidate Payment Completed', 'Default page title (wizard)', 'jobportal-framework')); ?>" name="jobportal-page-title[candidate_payment_completed]" /></td>
									<td>
										<p><?php esc_html_e('This page allows users to view their own "Candidate Payment Completed" via the front-end.', 'jobportal-framework'); ?></p>
									</td>
									<td><code>[jobportal_candidate_payment_completed]</code></td>
								</tr>
								<tr>
									<td><input type="checkbox" checked="checked" name="jobportal-create-page[employer_service]" /></td>
									<td><input type="text" value="<?php echo esc_attr(_x('Employer Service', 'Default page title (wizard)', 'jobportal-framework')); ?>" name="jobportal-page-title[employer_service]" /></td>
									<td>
										<p><?php esc_html_e('This page allows users to view their own "Employer Service" via the front-end.', 'jobportal-framework'); ?></p>
									</td>
									<td><code>[jobportal_employer_service]</code></td>
								</tr>
								<tr>
									<td><input type="checkbox" checked="checked" name="jobportal-create-page[candidate_service]" /></td>
									<td><input type="text" value="<?php echo esc_attr(_x('Candidate Service', 'Default page title (wizard)', 'jobportal-framework')); ?>" name="jobportal-page-title[candidate_service]" /></td>
									<td>
										<p><?php esc_html_e('This page allows users to view their own "Candidate Service" via the front-end.', 'jobportal-framework'); ?></p>
									</td>
									<td><code>[jobportal_candidate_service]</code></td>
								</tr>
								<tr>
									<td><input type="checkbox" checked="checked" name="jobportal-create-page[submit_service]" /></td>
									<td><input type="text" value="<?php echo esc_attr(_x('Service Submit', 'Default page title (wizard)', 'jobportal-framework')); ?>" name="jobportal-page-title[submit_service]" /></td>
									<td>
										<p><?php esc_html_e('This page allows users to view their own "Service Submit" via the front-end.', 'jobportal-framework'); ?></p>
									</td>
									<td><code>[jobportal_submit_service]</code></td>
								</tr>
								<tr>
									<td><input type="checkbox" checked="checked" name="jobportal-create-page[payment_service]" /></td>
									<td><input type="text" value="<?php echo esc_attr(_x('Service Payment', 'Default page title (wizard)', 'jobportal-framework')); ?>" name="jobportal-page-title[payment_service]" /></td>
									<td>
										<p><?php esc_html_e('This page allows users to view their own "Service Payment" via the front-end.', 'jobportal-framework'); ?></p>
									</td>
									<td><code>[jobportal_service_payment]</code></td>
								</tr>
								<tr>
									<td><input type="checkbox" checked="checked" name="jobportal-create-page[service_payment_completed]" /></td>
									<td><input type="text" value="<?php echo esc_attr(_x('Service Payment Completed', 'Default page title (wizard)', 'jobportal-framework')); ?>" name="jobportal-page-title[service_payment_completed]" /></td>
									<td>
										<p><?php esc_html_e('This page allows users to view their own "Service Payment Completed" via the front-end.', 'jobportal-framework'); ?></p>
									</td>
									<td><code>[jobportal_service_payment_completed]</code></td>
								</tr>
							</tbody>
							<tfoot>
								<tr>
									<th colspan="4">
										<input type="submit" class="button button-primary" value="<?php esc_html_e('Create selected pages', 'jobportal-framework'); ?>" />
										<a href="<?php echo esc_url(add_query_arg('step', 3)); ?>" class="button"><?php esc_html_e('Skip this step', 'jobportal-framework'); ?></a>
									</th>
								</tr>
							</tfoot>
						</table>
					</form>

				<?php endif; ?>
				<?php if (3 === $step) : ?>

					<h3><?php esc_html_e('All Done!', 'jobportal-framework'); ?></h3>

					<p><?php esc_html_e('Looks like you\'re all set to start using the plugin. In case you\'re wondering where to go next:', 'jobportal-framework'); ?></p>

					<ul class="jobportal-next-steps">
						<li>
							<a href="<?php echo admin_url('themes.php?page=jobportal-framework'); ?>"><?php esc_html_e('Plugin settings', 'jobportal-framework'); ?></a>
						</li>
						<li>
							<a href="<?php echo admin_url('post-new.php?post_type=jobs'); ?>"><?php esc_html_e('Add a jobs the back-end', 'jobportal-framework'); ?></a>
						</li>
						<?php if ($permalink = jobportal_get_permalink('jobs')) : ?>
							<li>
								<a href="<?php echo esc_url($permalink); ?>"><?php esc_html_e('Show all jobs', 'jobportal-framework'); ?></a>
							</li>
						<?php endif; ?>
						<?php if ($permalink = jobportal_get_permalink('submit_jobs')) : ?>
							<li>
								<a href="<?php echo esc_url($permalink); ?>"><?php esc_html_e('Add a jobs via the front-end', 'jobportal-framework'); ?></a>
							</li>
						<?php endif; ?>
						<?php if ($permalink = jobportal_get_permalink('jobs_dashboard')) : ?>
							<li>
								<a href="<?php echo esc_url($permalink); ?>"><?php esc_html_e('View user jobs', 'jobportal-framework'); ?></a>
							</li>
						<?php endif; ?>
						<?php if ($permalink = jobportal_get_permalink('my_profile')) : ?>
							<li>
								<a href="<?php echo esc_url($permalink); ?>"><?php esc_html_e('View user profile', 'jobportal-framework'); ?></a>
							</li>
						<?php endif; ?>
						<?php if ($permalink = jobportal_get_permalink('my_booking')) : ?>
							<li>
								<a href="<?php echo esc_url($permalink); ?>"><?php esc_html_e('View my booking', 'jobportal-framework'); ?></a>
							</li>
						<?php endif; ?>
						<?php if ($permalink = jobportal_get_permalink('bookings')) : ?>
							<li>
								<a href="<?php echo esc_url($permalink); ?>"><?php esc_html_e('View user bookings', 'jobportal-framework'); ?></a>
							</li>
						<?php endif; ?>
						<?php if ($permalink = jobportal_get_permalink('packages')) : ?>
							<li>
								<a href="<?php echo esc_url($permalink); ?>"><?php esc_html_e('View packages', 'jobportal-framework'); ?></a>
							</li>
						<?php endif; ?>
						<?php if ($permalink = jobportal_get_permalink('payment')) : ?>
							<li>
								<a href="<?php echo esc_url($permalink); ?>"><?php esc_html_e('View payment', 'jobportal-framework'); ?></a>
							</li>
						<?php endif; ?>
						<?php if ($permalink = jobportal_get_permalink('country')) : ?>
							<li>
								<a href="<?php echo esc_url($permalink); ?>"><?php esc_html_e('View country detail', 'jobportal-framework'); ?></a>
							</li>
						<?php endif; ?>
					</ul>
				<?php endif; ?>
			</div>
<?php
		}
	}
}
