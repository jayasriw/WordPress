<?php
$key_employer = array(
	"dashboard"      => esc_html__('Dashboard', 'jobportal-framework'),
	"jobs_dashboard" => esc_html__('Jobs', 'jobportal-framework'),
	"applicants"     => esc_html__('Applicants', 'jobportal-framework'),
	"candidates"     => esc_html__('Candidates', 'jobportal-framework'),
	"user_package"   => esc_html__('Package', 'jobportal-framework'),
	"messages"       => esc_html__('Messages', 'jobportal-framework'),
	"meetings"       => esc_html__('Meetings', 'jobportal-framework'),
	"company"        => esc_html__('Company', 'jobportal-framework'),
);

if (jobportal_get_option('enable_post_type_service') === '1') {
	$key_employer["service"] = esc_html__('Services', 'jobportal-framework');
}
$key_employer["settings"] = esc_html__('Settings', 'jobportal-framework');
$key_employer["logout"]   = esc_html__('Logout', 'jobportal-framework');

$current_user     = wp_get_current_user();
$enable_post_your = jobportal_get_option('show_employer_jobs_post_your');
?>
<div class="nav-dashboard-inner">
	<div class="bg-overlay"></div>
	<div class="nav-dashboard-wapper custom-scrollbar">
		<div class="nav-dashboard nav-employer_dashboard">
			<div class="nav-dashboard-header">
				<div class="header-wrap">
					<?php echo JobPortal_Templates::site_logo('dark'); ?>
				</div>
				<a href="#" class="closebtn">
					<i class="fas fa-arrow-left"></i>
				</a>
			</div>
			<?php if (in_array('jobportal_user_employer', (array) $current_user->roles) || current_user_can('administrator')) : ?>
				<ul class="list-nav-dashboard">
					<?php
					foreach ($key_employer as $key => $value) {
						if ($key ==  'service') {
							$key = 'employer_service';
						}
						$show_employer  = jobportal_get_option('show_employer_' . $key, '1');
						$image_employer = jobportal_get_option('image_employer_' . $key, '');
						$id             = jobportal_get_option('jobportal_' . $key . '_page_id');
					?>
						<?php if ($show_employer) : ?>
							<li class="nav-item <?php if (is_page($id) && $key !== "logout") : echo esc_attr__('active');
												endif; ?>">
								<?php if ($key === "logout") { ?>
									<a href="<?php echo wp_logout_url(home_url()); ?>">
									<?php } else { ?>
										<a href="<?php echo get_permalink($id); ?>" class="jobportal-icon-items"
											data-title="<?php echo $value; ?>">
										<?php } ?>
										<?php if (! empty($image_employer['url'])) : ?>
											<span class="image">
												<?php if (jobportal_get_option('type_icon_employer') === 'svg') { ?>
													<object class="jobportal-svg" type="image/svg+xml"
														data="<?php echo esc_url($image_employer['url']) ?>"></object>
												<?php } else { ?>
													<img src="<?php echo esc_url($image_employer['url']) ?>"
														alt="<?php echo $value; ?>" />
												<?php } ?>
											</span>
										<?php endif; ?>
										<span><?php echo $value; ?></span>
										<?php if ($key === "messages") { ?>
											<?php jobportal_get_total_unread_message(); ?>
										<?php } ?>
										</a>
							</li>
						<?php endif; ?>
					<?php } ?>
				</ul>
			<?php endif; ?>
			<?php if ($enable_post_your == 1) : ?>
				<div class="nav-dashboard-footer">
					<h4><?php esc_html_e('Post your first job!', 'jobportal-framework'); ?></h4>
					<p><?php esc_html_e('Your first 2 job postings for just $50 each.', 'jobportal-framework'); ?></p>
					<a href="<?php echo jobportal_get_permalink('jobs_submit'); ?>" class="jobportal-button"><i
							class="far fa-plus"></i><?php esc_html_e('Post a job', 'jobportal-framework'); ?></a>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<a href="#" class="icon-nav-mobie">
		<i class="far fa-bars"></i>
	</a>
</div>
