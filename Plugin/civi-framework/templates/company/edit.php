<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

if (!is_user_logged_in()) {
	civi_get_template('global/access-denied.php', array('type' => 'not_login'));
	return;
}

$company_id = isset($_GET['company_id']) ? civi_clean(wp_unslash($_GET['company_id'])) : '';
$layout = array('general', 'media', 'social', 'location', 'gallery', 'video', 'additional');
$form     = 'edit-company';
$action   = 'edit_company';

$custom_field_company = civi_render_custom_field('company');
global $company_data, $company_meta_data, $current_user, $hide_company_fields, $hide_company_group_fields;
$user_id = $current_user->ID;
if ($form == 'edit-company') {
	$company_data      = get_post($company_id);
	$company_meta_data = get_post_custom($company_data->ID);
}
$user_demo = get_the_author_meta(CIVI_METABOX_PREFIX . 'user_demo', $user_id);

$civi_company_page_id  = civi_get_option('civi_company_page_id', 0);
wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'company-submit');
wp_enqueue_script('jquery-validate');
wp_localize_script(
	CIVI_PLUGIN_PREFIX . 'company-submit',
	'civi_submit_vars',
	array(
		'ajax_url'  => CIVI_AJAX_URL,
		'not_found' => esc_html__("We didn't find any results, you can retry with other keyword.", 'civi-framework'),
		'not_company' => esc_html__('No company found', 'civi-framework'),
		'company_dashboard' => get_page_link($civi_company_page_id),
		'custom_field_company' => $custom_field_company,
	)
);
$hide_company_fields = civi_get_option('hide_company_fields', array());
if (!is_array($hide_company_fields)) {
	$hide_company_fields = array();
}

$hide_company_group_fields = civi_get_option('hide_company_group_fields', array());
if (!is_array($hide_company_group_fields)) {
	$hide_company_group_fields = array();
}

$company_logo_arg = get_post_meta($company_id, CIVI_METABOX_PREFIX . 'company_logo', false);
$company_logo_url = isset($company_logo_arg[0]['url']) ? $company_logo_arg[0]['url'] : '';
$content   = $company_data->post_content;
?>
<div class="entry-my-page submit-company-dashboard">
	<form action="#" method="post" id="submit_company_form" class="form-dashboard" enctype="multipart/form-data">
		<div class="content-company">
			<div class="row">
				<div class="col-lg-8 col-md-7">
					<div class="submit-company-header civi-submit-header">
						<div class="entry-title">
							<h4><?php esc_html_e('Update company', 'civi-framework') ?></h4>
						</div>
						<div class="button-wrapper">
							<a href="<?php echo civi_get_permalink('company'); ?>" class="civi-button button-outline">
								<?php esc_html_e('Cancel', 'civi-framework') ?>
							</a>
							<?php if ($user_demo == 'yes') : ?>
								<button class="civi-button btn-add-to-message" data-text="<?php echo esc_attr__('This is a "Demo" account, so you can not change it', 'civi-framework'); ?>">
									<span><?php esc_html_e('Update', 'civi-framework'); ?></span>
								</button>
							<?php else : ?>
								<button type="submit" class="btn-submit-company civi-button" name="submit_company" data-type="submit">
									<span><?php esc_html_e('Update', 'civi-framework'); ?></span>
									<span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
								</button>
							<?php endif; ?>
						</div>
					</div>
					<?php foreach ($layout as $value) {
						switch ($value) {
							case 'general':
								$name = esc_html__('Basic info', 'civi-framework');
								break;
							case 'media':
								$name = esc_html__('Media', 'civi-framework');
								break;
							case 'social':
								$name = esc_html__('Social network', 'civi-framework');
								break;
							case 'location':
								$name = esc_html__('Location', 'civi-framework');
								break;
							case 'gallery':
								$name = esc_html__('Gallery', 'civi-framework');
								break;
							case 'video':
								$name = esc_html__('Video', 'civi-framework');
								break;
							case 'additional':
								$name = esc_html__('Additional', 'civi-framework');
								break;
						}

						// Check if section should be hidden when all its fields are hidden
						$should_show_section = true;

						if ($value === 'general') {
							// Check if all general fields are hidden
							$general_fields = array(
								'fields_company_name',
								'fields_company_category',
								'fields_company_url',
								'fields_company_about',
								'fields_company_website',
								'fields_company_phone',
								'fields_company_email',
								'fields_company_founded',
								'fields_company_size'
							);
							$all_hidden = true;
							// Defensive check: ensure $hide_company_fields is array
							if (is_array($hide_company_fields) && !empty($general_fields)) {
								foreach ($general_fields as $field) {
									if (!in_array($field, $hide_company_fields, true)) {
										$all_hidden = false;
										break;
									}
								}
							} else {
								// If hide_company_fields is not array or empty, show section
								$all_hidden = false;
							}
							if ($all_hidden) {
								$should_show_section = false;
							}
						} elseif ($value === 'media') {
							// Check if both logo and thumbnail are hidden
							// Defensive check: ensure $hide_company_fields is array
							if (is_array($hide_company_fields)) {
								$logo_hidden = in_array('fields_closing_logo', $hide_company_fields, true);
								$thumbnail_hidden = in_array('fields_company_thumbnail', $hide_company_fields, true);
								if ($logo_hidden && $thumbnail_hidden) {
									$should_show_section = false;
								}
							}
						} elseif ($value === 'social') {
							// Check if all social networks are disabled
							// Handle edge cases: '0', false, empty string, null should be treated as disabled
							$enable_social_twitter = civi_get_option('enable_social_twitter', '1');
							$enable_social_linkedin = civi_get_option('enable_social_linkedin', '1');
							$enable_social_facebook = civi_get_option('enable_social_facebook', '1');
							$enable_social_instagram = civi_get_option('enable_social_instagram', '1');

							// Check if any social network is enabled (strict check for '1')
							$has_enabled = ($enable_social_twitter === '1' || $enable_social_twitter === 1 || $enable_social_twitter === true) ||
										   ($enable_social_linkedin === '1' || $enable_social_linkedin === 1 || $enable_social_linkedin === true) ||
										   ($enable_social_facebook === '1' || $enable_social_facebook === 1 || $enable_social_facebook === true) ||
										   ($enable_social_instagram === '1' || $enable_social_instagram === 1 || $enable_social_instagram === true);

							if (!$has_enabled) {
								$should_show_section = false;
							}
						} elseif ($value === 'location') {
							// Check if both location and map fields are hidden
							// Defensive check: ensure $hide_company_fields is array
							if (is_array($hide_company_fields)) {
								$location_hidden = in_array('fields_company_location', $hide_company_fields, true);
								$map_hidden = in_array('fields_company_map', $hide_company_fields, true);
								if ($location_hidden && $map_hidden) {
									$should_show_section = false;
								}
							}
						}
						// Gallery, Video, and Additional sections don't have hideable fields, so always show if not in group hide list

						// Final check: ensure group fields array is valid and section should be shown
						$is_group_hidden = is_array($hide_company_group_fields) && in_array($value, $hide_company_group_fields, true);

						if (!$is_group_hidden && $should_show_section) : ?>
							<div class="block-from" id="<?php echo 'company-submit-' . esc_attr($value); ?>">
								<h6><?php echo $name ?></h6>
								<?php civi_get_template('company/edit/' . $value . '.php'); ?>
							</div>
					<?php endif;
					} ?>

					<?php wp_nonce_field('civi_submit_company_action', 'civi_submit_company_nonce_field'); ?>

					<input type="hidden" name="company_form" value="<?php echo esc_attr($form); ?>" />
					<input type="hidden" name="company_action" value="<?php echo esc_attr($action) ?>" />
					<input type="hidden" name="company_id" value="<?php echo esc_attr($company_id); ?>" />
				</div>
				<div class="col-lg-4 col-md-5">
					<div class="widget-area-init has-sticky">
						<div class="about-company-dashboard block-archive-sidebar">
							<div class="header-about">
								<h3 class="title-company-about"><?php esc_html_e('Preview', 'civi-framework') ?></h3>
								<a class="civi-button button-outline-accent" href="<?php echo get_post_permalink($company_id); ?>" target="_blank">
									<span><?php esc_html_e('View', 'civi-framework') ?></span>
									<i class="fas fa-external-link-alt"></i>
								</a>
							</div>
							<div class="info-company">
								<?php if ($company_logo_url) : ?>
									<div class="img-company">
										<img src="<?php echo esc_url($company_logo_url) ?>" alt="" />
									</div>
								<?php else : ?>
									<div class="img-company"><i class="far fa-camera"></i></div>
								<?php endif; ?>
								<div class="company-right">
									<div class="title-wapper">
										<h4 class="title-about" data-title="<?php esc_attr_e('Company name', 'civi-framework') ?>"><?php esc_html_e('Company name', 'civi-framework') ?></h4>
										<?php
										// Check if general section is visible and has verifiable fields
										$general_visible = civi_is_company_section_visible('general', $hide_company_fields, $hide_company_group_fields);
										$location_visible = civi_is_company_section_visible('location', $hide_company_fields, $hide_company_group_fields);

										$show_website_check = $general_visible && is_array($hide_company_fields) && !in_array('fields_company_website', $hide_company_fields, true);
										$show_phone_check = $general_visible && is_array($hide_company_fields) && !in_array('fields_company_phone', $hide_company_fields, true);
										$show_location_check = $location_visible && is_array($hide_company_fields) && !in_array('fields_company_location', $hide_company_fields, true);

										if ($show_website_check || $show_phone_check || $show_location_check) : ?>
											<div class="civi-check-company tip">
												<div class="tip-content">
													<h4><?php esc_html_e('Conditions for a green tick:', 'civi-framework') ?></h4>
													<ul class="list-check">
														<?php if ($show_website_check) : ?>
															<li class="check-webs" data-verified="<?php esc_attr_e('Website has been verified', 'civi-framework') ?>" data-not-verified="<?php esc_attr_e('Website not been verified', 'civi-framework') ?>">
																<i class="fas fa-check"></i>
																<?php esc_html_e('Website not been verified', 'civi-framework') ?>
															</li>
														<?php endif; ?>
														<?php if ($show_phone_check) : ?>
															<li class="check-phone" data-verified="<?php esc_attr_e('Phone has been verified', 'civi-framework') ?>" data-not-verified="<?php esc_attr_e('Phone not been verified', 'civi-framework') ?>">
																<i class="fas fa-check"></i>
																<?php esc_html_e('Phone not been verified', 'civi-framework') ?>
															</li>
														<?php endif; ?>
														<?php if ($show_location_check) : ?>
															<li class="check-location" data-verified="<?php esc_attr_e('Location has been verified', 'civi-framework') ?>" data-not-verified="<?php esc_attr_e('Location not been verified', 'civi-framework') ?>">
																<i class="fas fa-check"></i>
																<?php esc_html_e('Location not been verified', 'civi-framework') ?>
															</li>
														<?php endif; ?>
													</ul>
												</div>
											</div>
										<?php endif; ?>
									</div>
									<i class="fas fa-map-marker-alt"></i><span class="location-about" data-location="<?php esc_attr_e('Location', 'civi-framework') ?>"><?php esc_html_e('Location', 'civi-framework') ?></span>
								</div>
							</div>
							<div class="des-about"><?php echo $content; ?></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
