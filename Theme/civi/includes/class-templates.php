<?php

if (!defined("ABSPATH")) {
	exit();
}

if (!class_exists("Civi_Templates")) {
	/**
	 *  Class Civi_Templates
	 */
	class Civi_Templates
	{
		public static function site_logo($type = "")
		{
			$logo = "";
			$logo_retina = "";
			$logo_light = Civi_Helper::get_setting("logo_light");
			$logo_light_retina = Civi_Helper::get_setting("logo_light_retina");

			if ($type == "dark") {
				$logo_dark = Civi_Helper::get_setting("logo_dark");
				$logo_dark_retina = Civi_Helper::get_setting("logo_dark_retina");

				if ($logo_dark) {
					$logo = $logo_dark;
				}

				if ($logo_dark_retina) {
					$logo_retina = $logo_dark_retina;
				}
			}

			if ($type == "light") {
				if ($logo_light) {
					$logo = $logo_light;
				}

				if ($logo_light_retina) {
					$logo_retina = $logo_light_retina;
				}
			}

			$site_name = get_bloginfo("name", "display");

			ob_start();
?>
			<?php if (!empty($logo)) : ?>
				<div class="site-logo">
					<a href="<?php echo esc_url(home_url("/")); ?>" title="<?php echo esc_attr($site_name); ?>">
						<img src="<?php echo esc_url($logo); ?>"
							srcset="<?php echo esc_url($logo_retina); ?> 2x"
							data-light="<?php echo esc_url($logo_light); ?>"
							data-light-retina="<?php echo esc_url($logo_light_retina); ?>"
							data-original="<?php echo esc_url($logo); ?>"
							data-original-retina="<?php echo esc_url($logo_retina); ?>"
							alt="<?php echo esc_attr($site_name); ?>"
							class="logo-img">
					</a>
				</div>
			<?php else : ?>
				<div class="site-logo">
					<?php $blog_info = get_bloginfo("name"); ?>
					<?php if (!empty($blog_info)) : ?>
						<h1 class="site-title">
							<a href="<?php echo esc_url(home_url("/")); ?>"
								title="<?php echo esc_attr($site_name); ?>">
								<?php bloginfo("name"); ?>
							</a>
						</h1>
						<p><?php bloginfo("description"); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		<?php
			return ob_get_clean();
		}

		public static function style_logo()
		{
			$id = get_the_ID();
			$header_style = '';
			if (!empty($id)) {
				$header_style = get_post_meta($id, 'civi-header_style', true);
			}
			if ($header_style == 'light') {
				$header_logo = Civi_Templates::site_logo('light');
			} else {
				$header_logo = Civi_Templates::site_logo('dark');
			}
			return $header_logo;
		}

		public static function main_menu()
		{
			$show_main_menu = Civi_Helper::get_setting("show_main_menu");

			if (!$show_main_menu) {
				return;
			}

			ob_start();
		?>
			<div class="site-menu main-menu desktop-menu default-menu">
				<?php
				$args = array();

				$defaults = array(
					'menu_class' => 'menu',
					'container' => '',
					'theme_location' => 'main_menu',
				);

				$args = wp_parse_args($args, $defaults);

				if (has_nav_menu('main_menu') && class_exists('Civi_Walker_Nav_Menu')) {
					$args['walker'] = new Civi_Walker_Nav_Menu;
				}

				if (has_nav_menu('main_menu')) {
					wp_nav_menu($args);
				}
				?>
			</div>
		<?php return ob_get_clean();
		}

		public static function site_menu()
		{
			if (!class_exists("Civi_Framework")) {
				return;
			}

			ob_start();
		?>
			<div class="site-menu desktop-menu default-menu">
				<?php
				$args = array();

				$defaults = array(
					'menu_class' => 'menu',
					'container' => '',
					'theme_location' => 'primary',
				);

				$args = wp_parse_args($args, $defaults);

				if (has_nav_menu('primary') && class_exists('Civi_Walker_Nav_Menu')) {
					$args['walker'] = new Civi_Walker_Nav_Menu;
				}

				wp_nav_menu($args);
				?>
			</div>
		<?php return ob_get_clean();
		}

		public static function mobile_menu()
		{

			ob_start();
		?>
			<div class="bg-overlay"></div>

			<div class="site-menu area-menu mobile-menu default-menu">

				<div class="inner-menu custom-scrollbar">

					<a href="#" class="btn-close">
						<i class="far fa-times"></i>
					</a>

					<?php if (!class_exists("Civi_Framework")) : ?>
						<?php echo self::site_logo("dark"); ?>
					<?php endif; ?>

					<?php if (class_exists("Civi_Framework")) : ?>
						<div class="top-mb-menu">
							<?php echo self::account(); ?>
						</div>
					<?php endif; ?>

					<?php
					$args = [
						"menu_class" => "menu",
						"container" => "",
						"theme_location" => "mobile_menu",
					];
					wp_nav_menu($args);
					?>

					<?php echo self::add_jobs(); ?>
				</div>
			</div>
		<?php return ob_get_clean();
		}

		public static function canvas_menu()
		{
			$show_canvas_menu = Civi_Helper::get_setting("show_canvas_menu");

			ob_start();
		?>
			<div class="mb-menu canvas-menu canvas-left <?php if (!$show_canvas_menu) {
																										echo "d-hidden";
																									} ?>">
				<a href="#" class="icon-menu">
					<i class="far fa-bars"></i>
				</a>

				<?php echo self::mobile_menu(); ?>
			</div>
		<?php return ob_get_clean();
		}

		public static function search_icon($search_type = "icon", $ajax = false)
		{
			$ajax_class = "";
			if ($ajax) {
				$ajax_class = "civi-ajax-search";
			}

			$show_search_icon = Civi_Helper::get_setting("show_search_icon");
			if (!$show_search_icon) {
				return;
			}

			ob_start();
		?>
			<div class="block-search search-<?php echo esc_attr($search_type); ?>
			<?php echo esc_attr($ajax_class); ?>">
				<div class="icon-search">
					<i class="far fa-search"></i>
				</div>
			</div>
			<?php return ob_get_clean();
		}

		public static function post_categories()
		{
			ob_start();

			$count_posts = wp_count_posts();
			$category_id = "";
			$blog_sidebar = Civi_Helper::get_setting("blog_sidebar");
			$sidebar = !empty($_GET["sidebar"])
				? Civi_Helper::civi_clean(wp_unslash($_GET["sidebar"]))
				: $blog_sidebar;

			if (is_category()) {
				$cate = get_category(get_query_var("cat"));
				$category_id = $cate->cat_ID;
			}
			$categories = get_categories([
				"orderby" => "count",
				"order" => "DESC",
				"number" => 5,
				"parent" => 0,
				"hide_empty" => true,
				"hierarchical" => true,
			]);

			if ($categories) : ?>
				<div class="civi-categories">
					<ul class="list-categories">
						<li class="<?php if (!is_front_page() && is_home()) :
													echo esc_attr("active");
												endif; ?>">
							<a href="<?php echo get_post_type_archive_link("post"); ?>">
								<span class="entry-name"><?php esc_html_e("All", "civi"); ?></span>
							</a>
						</li>
						<?php foreach ($categories as $category) {
							$category_link = get_category_link($category->term_id); ?>
							<li class="<?php if ($category_id == $category->term_id) :
														echo esc_attr("active");
													endif; ?>">
								<a href="<?php echo esc_url($category_link); ?>">
									<span class="entry-name"><?php esc_html_e($category->name); ?></span>
								</a>
							</li>
						<?php
						} ?>
					</ul>
				</div>
			<?php endif;

			return ob_get_clean();
		}

		public static function account()
		{
			$show_login = Civi_Helper::get_setting("show_login");

			if (
				!class_exists("Civi_Framework") ||
				(!$show_login)
			) {
				return;
			}

			ob_start();
			?>
			<?php if (is_user_logged_in()) {

				$accent_color 	   	   = Civi_Helper::get_setting('accent_color');
				$secondary_color 	   = Civi_Helper::get_setting('secondary_color');
				$enable_service = Civi_Helper::civi_get_option('enable_post_type_service');
				$currency_sign_default = Civi_Helper::civi_get_option('currency_sign_default');
				$currency_position = Civi_Helper::civi_get_option('currency_position');

				$current_user = wp_get_current_user();
				// Priority: full name (first_name + last_name) > display_name > user_login
				$first_name = get_user_meta($current_user->ID, 'first_name', true);
				$last_name = get_user_meta($current_user->ID, 'last_name', true);
				if (!empty($first_name) || !empty($last_name)) {
					$user_name = trim($first_name . ' ' . $last_name);
				} else {
					$user_name = !empty($current_user->display_name) ? $current_user->display_name : $current_user->user_login;
				}
				$user_id = $current_user->ID;
				$user_link = get_edit_user_link($current_user->ID);
				$avatar_url = get_avatar_url($current_user->ID);
				$author_avatar_image_url = get_the_author_meta(
					"author_avatar_image_url",
					$current_user->ID
				);
				$author_avatar_image_id = get_the_author_meta(
					"author_avatar_image_id",
					$current_user->ID
				);
				if (!empty($author_avatar_image_url)) {
					$avatar_url = $author_avatar_image_url;
				}
				$current_user = wp_get_current_user();
				$key_employer = [
					"dashboard" => esc_html__('Dashboard', 'civi'),
					"jobs_dashboard" => esc_html__('Jobs', 'civi'),
					"applicants" => esc_html__('Applicants', 'civi'),
					"candidates" => esc_html__('Candidates', 'civi'),
					"user_package" => esc_html__('Package', 'civi'),
					"messages" => esc_html__('Messages', 'civi'),
					"meetings" => esc_html__('Meetings', 'civi'),
					"company" => esc_html__('Company', 'civi'),
				];

				if (Civi_Helper::civi_get_option('enable_post_type_service') === '1') {
					$key_employer["service"] = esc_html__('Services', 'civi');
				}
				$key_employer["settings"] = esc_html__('Settings', 'civi');
				$key_employer["logout"] = esc_html__('Logout', 'civi');

				$key_candidate =  apply_filters(
					'civi/dashboard/candidate/nav',
					[
						"candidate_dashboard"    => esc_html__('Dashboard', 'civi'),
						"candidate_profile"      => esc_html__('Profile', 'civi'),
						"my_jobs"                => esc_html__('My Jobs', 'civi'),
						"candidate_user_package" => esc_html__('Package', 'civi'),
						"candidate_reviews"      => esc_html__('My Reviews', 'civi'),
						"candidate_company"      => esc_html__('My Following', 'civi'),
						"candidate_messages"     => esc_html__('Messages', 'civi'),
						"candidate_meetings"     => esc_html__('Meetings', 'civi'),
					]
				);

				if (Civi_Helper::civi_get_option('enable_post_type_service') === '1') {
					$key_candidate["candidate_service"] = esc_html__('Services', 'civi');
				}
				$key_candidate["candidate_settings"] = esc_html__('Settings', 'civi');
				$key_candidate["candidate_logout"] = esc_html__('Logout', 'civi');
				$enable_user_name  = Civi_Helper::civi_get_option('enable_user_name_after_login', 1);

			?>
				<div class="account logged-in">
					<?php if ($avatar_url) : ?>
						<div class="user-show">
							<a class="avatar" href="#">
								<img src="<?php echo esc_url($avatar_url); ?>"
									title="<?php echo esc_attr($user_name); ?>"
									alt="<?php echo esc_attr($user_name); ?>">
								<span>
									<?php if ($enable_user_name) {
										echo esc_html($user_name);
									} ?>
									<?php
									if (($enable_service == '1' || $enable_service === 1 || $enable_service === true) &&
										in_array("civi_user_candidate", (array)$current_user->roles)
									) {
										$total_price = get_user_meta($user_id, CIVI_METABOX_PREFIX . 'service_withdraw_total_price', true);
										if (empty($total_price)) {
											$total_price = 0;
										}
										if ($currency_position == 'before') {
											$formatted_price = $currency_sign_default . $total_price;
										} else {
											$formatted_price = $total_price . $currency_sign_default;
										}

										echo '<span class="price">(' . esc_html($formatted_price) . ')</span>';
									} ?>
								</span>
								<i class="far fa-chevron-down"></i>
							</a>
						</div>
					<?php endif; ?>
					<?php if (
						in_array("civi_user_candidate", (array)$current_user->roles) ||
						in_array("civi_user_employer", (array)$current_user->roles)
					) : ?>
						<div class="user-control civi-nav-dashboard" data-secondary="<?php echo $secondary_color; ?>" data-accent="<?php echo $accent_color; ?>">
							<div class="inner-control nav-dashboard">
								<ul class="list-nav-dashboard">
									<?php if (in_array("civi_user_employer", (array)$current_user->roles)) :
										foreach ($key_employer as $key => $value) {
											if ($key ===  'service') {
												$key = 'employer_service';
											}
											$show_employer = Civi_Helper::civi_get_option("show_employer_" . $key, "1");
											$image_employer = Civi_Helper::civi_get_option("image_employer_" . $key);
											$id = Civi_Helper::civi_get_option("civi_" . $key . "_page_id");
									?>
											<?php if ($show_employer) : ?>
												<li class="nav-item nav-employer <?php if (is_page($id) && $key !== "logout") :
																														echo esc_attr("active");
																													endif; ?>">
													<?php if ($key === "logout") { ?>
														<a href="<?php echo wp_logout_url(home_url()); ?>">
														<?php } else { ?>
															<a href="<?php echo get_permalink($id); ?>" class="civi-icon-items">
															<?php } ?>
															<?php if (!empty($image_employer["url"])) : ?>
																<span class="image">
																	<?php if (civi_get_option('type_icon_employer') === 'svg') { ?>
																		<object class="civi-svg" type="image/svg+xml" data="<?php echo esc_url($image_employer['url']) ?>"></object>
																	<?php } else { ?>
																		<img src="<?php echo esc_url($image_employer['url']) ?>" alt="<?php echo $value; ?>" />
																	<?php } ?>
																</span>
															<?php endif; ?>
															<span><?php esc_html_e($value) ?></span>
															<?php if ($key === "messages") { ?>
																<?php civi_get_total_unread_message(); ?>
															<?php } ?>
															</a>
												</li>
											<?php endif; ?>
										<?php
										} ?>
										<?php
									elseif (in_array("civi_user_candidate", (array)$current_user->roles)) :

										foreach ($key_candidate as $key => $value) :

											$show_candidate = Civi_Helper::civi_get_option('show_' . $key, '1');

											if (!$show_candidate) {
												continue;
											}

											$id = Civi_Helper::civi_get_option("civi_" . $key . "_page_id");
											$image_candidate = Civi_Helper::civi_get_option("image_" . $key, "");

											$class_active = (is_page($id) && $key !== "candidate_logout") ? 'active' : '';

											$link_url = '';
											$link_url = $key === "candidate_logout" ? wp_logout_url(home_url()) : get_permalink($id);

											$html_icon = '';
											if (!empty($image_candidate['url'])) {
												if (civi_get_option("type_icon_candidate") === "svg") {
													$html_icon =
														'<object class="civi-svg" type="image/svg+xml" data="' .
														esc_url($image_candidate["url"]) .
														'"></object>';
												} else {
													$html_icon =
														'<img src="' .
														esc_url($image_candidate["url"]) .
														'" alt="' .
														$value .
														'"/>';
												}
											}
										?>
											<li class="nav-item nav-candidate <?php esc_html_e($class_active) ?>">
												<a href="<?php echo esc_url($link_url) ?>">
													<?php if (!empty($image_candidate["url"])) { ?>
														<span class="image">
															<?php echo $html_icon; ?>
														</span>
													<?php } ?>
													<span><?php esc_html_e($value); ?></span>
													<?php if ($key === "candidate_messages") { ?>
														<?php civi_get_total_unread_message(); ?>
													<?php } ?>
												</a>
											</li>

										<?php endforeach; ?>
									<?php endif; ?>
								</ul>
							</div>
						</div>
					<?php endif; ?>
				</div>
			<?php
			} else {
			?>
				<div class="account logged-out">
					<?php if ($show_login) : ?>
						<a href="#popup-form" class="btn-login">
							<?php esc_html_e("Login", "civi"); ?></a>
					<?php endif; ?>
				</div>
			<?php
			} ?>
		<?php return ob_get_clean();
		}

		public static function notification()
		{
			$show_icon_noti = Civi_Helper::get_setting("show_icon_noti");
			if (!class_exists("Civi_Framework") || !$show_icon_noti || !is_user_logged_in()) {
				return;
			}

			ob_start();

			civi_get_template('dashboard/notification/notification.php');

			return ob_get_clean();
		}


		public static function add_jobs()
		{
			global $current_user;
			$user_id = $current_user->ID;

			$show_add_jobs_button = Civi_Helper::get_setting(
				"show_add_jobs_button"
			);

			if (!class_exists("Civi_Framework") || !$show_add_jobs_button) {
				return;
			}
			$add_jobs = $add_jobs_not = $update_profile = '#';
			if (Civi_Helper::civi_get_option("civi_add_jobs_page_id")) {
				$add_jobs = get_page_link(Civi_Helper::civi_get_option("civi_add_jobs_page_id"));
			}

			if (Civi_Helper::civi_get_option("civi_add_jobs_not_page_id")) {
				$add_jobs_not = get_page_link(Civi_Helper::civi_get_option("civi_add_jobs_not_page_id"));
			}

			if (Civi_Helper::civi_get_option("civi_update_profile_page_id")) {
				$update_profile = get_page_link(Civi_Helper::civi_get_option("civi_update_profile_page_id"));
			}

			if (Civi_Helper::civi_get_option("civi_package_page_id")) {
				$package = get_page_link(Civi_Helper::civi_get_option("civi_package_page_id"));
			}

			$enable_login_to_submit = Civi_Helper::civi_get_option(
				"enable_login_to_submit",
				"1"
			);

			$paid_submission_type = Civi_Helper::civi_get_option('paid_submission_type');
			$package_number_job = get_the_author_meta(CIVI_METABOX_PREFIX . 'package_number_job', $user_id);
			$has_package = true;
			if ($paid_submission_type == 'per_package') {
				$civi_profile = new Civi_Profile();
				$check_package = $civi_profile->user_package_available($user_id);
				if (($check_package == -1) || ($check_package == 0)) {
					$has_package = false;
				}
			}

			do_action('civi_before_add_jobs_output', $user_id, $has_package, $paid_submission_type);

			ob_start();
		?>
			<?php
			if (apply_filters('civi_add_jobs_show_button', ($enable_login_to_submit == "1" && !is_user_logged_in()), $user_id)) {
			?>
				<a href="<?php echo esc_url($add_jobs_not); ?>" class="civi-button add-job">
					<?php esc_html_e("Post a job", "civi"); ?>
				</a>
			<?php } else { ?>
				<?php if (in_array('civi_user_candidate', (array)$current_user->roles)) { ?>
					<a href="<?php echo esc_url($update_profile); ?>" class="add-job civi-button">
						<?php esc_html_e("Update Profile", "civi"); ?>
					</a>
				<?php } else { ?>
					<?php
					if (apply_filters('civi_add_jobs_has_package', ($has_package && $package_number_job > 0) || $paid_submission_type !== 'per_package', $user_id)) {
					?> <a href="<?php echo esc_url($add_jobs); ?>" class="add-job civi-button">
							<?php esc_html_e("Post a job", "civi"); ?>
						</a>
					<?php } else { ?>
						<a href="<?php echo esc_url($package); ?>" class="add-job civi-button">
							<?php esc_html_e("Post a job", "civi"); ?>
						</a>
					<?php } ?>
				<?php } ?>
			<?php } ?>
		<?php
			$output = ob_get_clean();
			return apply_filters('civi_add_jobs_output', $output, $user_id);
		}

		//Top Bar
		public static function top_bar()
		{
			$top_bar_text = Civi_Helper::get_setting("top_bar_text");
			$top_bar_link = Civi_Helper::get_setting("top_bar_link");
			$top_bar_phone = Civi_Helper::get_setting("top_bar_phone");
			$top_bar_email = Civi_Helper::get_setting("top_bar_email");
			$top_bar_ringbell = Civi_Helper::get_setting('top_bar_ringbell');

			ob_start(); ?>
			<?php if (!empty($top_bar_text) || !empty($top_bar_ringbell)) : ?>
				<div class="col-lg-7 left-top-bar">
					<div class="top-bar-text">
						<?php if (!empty($top_bar_link)) : ?>
							<a href="<?php echo esc_url($top_bar_link); ?>">
							<?php endif; ?>
							<?php if (!empty($top_bar_ringbell)) : ?>
								<span class="icon-ringbell">
									<img src="<?php echo esc_url($top_bar_ringbell); ?>" alt="" />
								</span>
							<?php endif; ?>
							<?php if (!empty($top_bar_text)) : ?>
								<?php echo wp_kses_post($top_bar_text) ?>
							<?php endif; ?>
							<?php if (!empty($top_bar_link)) : ?>
							</a>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if (!empty($top_bar_phone) || !empty($top_bar_email)) : ?>
				<div class="col-lg-5 right-top-bar">
					<?php if (!empty($top_bar_phone)) : ?>
						<span class="top-bar-phone"><i class="fal fa-phone"></i><?php esc_html_e($top_bar_phone); ?></span>
					<?php endif; ?>
					<?php if (!empty($top_bar_email)) : ?>
						<span class="top-bar-email"><i class="fal fa-envelope"></i><?php esc_html_e($top_bar_email); ?></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<?php return ob_get_clean();
		}


		public static function page_title()
		{
			ob_start();

			get_template_part("templates/page/page-title");

			return ob_get_clean();
		}

		public static function post_thumbnail()
		{
			ob_start();

			get_template_part("templates/post/post-thumbnail");

			return ob_get_clean();
		}

		/**
		 * Render comments
		 * *******************************************************
		 */
		public static function render_comments($comment, $args, $depth)
		{
			self::civi_get_template("post/comment", [
				"comment" => $comment,
				"args" => $args,
				"depth" => $depth,
			]);
		}

		/**
		 * Get template
		 * *******************************************************
		 */
		public static function civi_get_template($slug, $args = [])
		{
			if ($args && is_array($args)) {
				extract($args);
			}
			$located = locate_template(["templates/{$slug}.php"]);

			if (!file_exists($located)) {
				_doing_it_wrong(
					__FUNCTION__,
					sprintf("<code>%s</code> does not exist.", $slug),
					"1.0"
				);
				return;
			}
			include $located;
		}

		/**
		 * Display navigation to next/previous set of posts when applicable.
		 */
		public static function pagination()
		{
			global $wp_query, $wp_rewrite;

			// Don't print empty markup if there's only one page.
			if ($wp_query->max_num_pages < 2) {
				return;
			}

			$paged = get_query_var("paged")
				? intval(get_query_var("paged"))
				: 1;
			$pagenum_link = wp_kses(
				get_pagenum_link(),
				Civi_Helper::civi_kses_allowed_html()
			);
			$query_args = [];
			$url_parts = explode("?", $pagenum_link);

			if (isset($url_parts[1])) {
				wp_parse_str($url_parts[1], $query_args);
			}

			$pagenum_link = esc_url(
				remove_query_arg(array_keys($query_args), $pagenum_link)
			);
			$pagenum_link = trailingslashit($pagenum_link) . "%_%";

			$format =
				$wp_rewrite->using_index_permalinks() &&
				!strpos($pagenum_link, "index.php")
				? "index.php/"
				: "";
			$format .= $wp_rewrite->using_permalinks()
				? user_trailingslashit(
					$wp_rewrite->pagination_base . "/%#%",
					"paged"
				)
				: "?paged=%#%";

			// Set up paginated links.
			$links = paginate_links([
				"format" => $format,
				"total" => $wp_query->max_num_pages,
				"current" => $paged,
				"add_args" => array_map("urlencode", $query_args),
				"prev_text" => '<i class="far fa-angle-left"></i>',
				"next_text" => '<i class="far fa-angle-right"></i>',
				"type" => "list",
				"end_size" => 3,
				"mid_size" => 3,
			]);

			if ($links) { ?>

				<div class="posts-pagination">
					<?php echo wp_kses($links, Civi_Helper::civi_kses_allowed_html()); ?>
				</div><!-- .pagination -->

<?php }
		}
	}
}
