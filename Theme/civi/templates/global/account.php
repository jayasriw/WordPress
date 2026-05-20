<?php
$enable_captcha = Civi_Helper::civi_get_option('enable_captcha');
$captcha = rand(1000, 9999);

global $current_user;
global $wp;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
if (isset($_GET['action']) && $_GET['action'] == 'rp') {
	$class_open = 'open';
} else {
	$class_open = '';
}

$current_page_url = home_url($wp->request);

?>
<div class="popup popup-account <?php esc_html_e($class_open) ?>" id="popup-form">
	<div class="bg-overlay"></div>
	<div class="inner-popup custom-scrollbar">
		<a href="#" class="btn-close">
			<i class="far fa-times large"></i>
		</a>
		<div class="head-popup">
			<div class="tabs-form">
				<a class="btn-login active" href="#ux-login" data-form="ux-login"><?php esc_html_e('Log in', 'civi'); ?></a>
				<a class="btn-register" href="#ux-register" data-form="ux-register"><?php esc_html_e('Sign Up', 'civi'); ?></a>
				<div class="loading-effect"><span class="civi-dual-ring"></span></div>
			</div>
			<?php if (is_user_logged_in()) { ?>
				<p class="notice"><i class="fal fa-exclamation-circle"></i><?php esc_html_e('Please login role Candidate to view', 'civi'); ?></p>
			<?php } ?>
		</div>

		<div class="body-popup">

			<?php
			if (isset($_GET['action']) && $_GET['action'] == 'rp') :
			?>

				<div class="civi-new-password-wrap">
					<form action="#" method="post">
						<div class="form-group control-password">
							<input name="new_password" type="password" id="new-password" class="form-control control-icon" placeholder="<?php esc_attr_e('Enter new password', 'civi'); ?>">
							<span><i class="fas fa-eye"></i></span>
						</div>
						<div class="form-group control-password">
							<input name="confirm_password" type="password" id="confirm-password" class="form-control control-icon" placeholder="<?php esc_attr_e('Enter confirm password', 'civi'); ?>">
							<span><i class="fas fa-eye"></i></span>
						</div>
						<div class="button-wrap">
							<a href="#" class="generate-password"><?php esc_html_e('Generate', 'civi'); ?></a>
							<button type="submit" id="civi_newpass" class="btn gl-button"><?php esc_html_e('Save password', 'civi'); ?></button>
							<input type="hidden" name="login" id="login" value="<?php esc_html_e($_GET['login']) ?>">
							<input type="hidden" name="key" id="key" value="<?php esc_html_e($_GET['key']) ?>">
							<p class="msg"><?php esc_html_e('Sending info, please wait...', 'civi'); ?></p>
						</div>
					</form>
				</div>

			<?php else : ?>

				<form action="#" class="form-account active ux-login" method="post">

					<?php do_action('civi_user_demo_sign_in'); ?>

					<div class="form-group">
						<label for="ip_email" class="label-field"><?php esc_html_e('Account or Email', 'civi'); ?></label>
						<input type="text" id="ip_email" class="form-control input-field" name="email" placeholder="<?php esc_attr_e('Enter Account or Email', 'civi') ?>">
					</div>
					<div class="form-group">
						<label for="ip_password" class="label-field"><?php esc_html_e('Password', 'civi'); ?></label>
						<input type="password" id="ip_password" class="form-control input-field" name="password" autocomplete="on" placeholder="<?php esc_attr_e('Enter Password', 'civi') ?>">
						<span toggle="#ip_password" class="fa fa-fw fa-eye field-icon civi-toggle-password"></span>
					</div>

					<?php
					$enable_captcha = Civi_Helper::civi_get_option('enable_captcha');
					$recaptcha_site_key = Civi_Helper::civi_get_option('recaptcha_site_key');
					$recaptcha_version = Civi_Helper::civi_get_option('recaptcha_version', 'v2');
					if ($enable_captcha && $recaptcha_site_key) :
						if ($recaptcha_version === 'v3') :
							echo '<script src="https://www.google.com/recaptcha/api.js?render=' . esc_attr($recaptcha_site_key) . '"></script>';
							echo '<input type="hidden" name="g_recaptcha_response" id="g_recaptcha_response_login_template" class="g-recaptcha-response">';
						else :
							echo '<div class="g-recaptcha" data-sitekey="' . esc_attr($recaptcha_site_key) . '" data-callback="verifyLoginCaptcha"></div>';
							echo '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
						endif;
					endif;
					?>

					<p class="msg"><?php esc_html_e('Sending login info, please wait...', 'civi'); ?></p>

					<div class="form-group">
						<div class="forgot-password">
							<span><?php esc_html_e('Forgot your password? ', 'civi'); ?></span>
							<a class="btn-reset-password" href="#"><?php esc_html_e('Reset password.', 'civi'); ?></a>
						</div>
					</div>

					<div class="form-group">
						<input type="hidden" name="current_page" value="<?php echo $current_page_url; ?>">
						<input type="hidden" name="civi_recaptcha" value="<?php echo $enable_captcha; ?>">
						<button type="submit" class="gl-button btn button" value="<?php esc_attr_e('Sign in', 'civi'); ?>"><?php esc_html_e('Sign in', 'civi'); ?></button>
					</div>
				</form>

				<div class="civi-reset-password-wrap form-account">
					<div id="civi_messages_reset_password" class="civi_messages message"></div>
					<form method="post" enctype="multipart/form-data">
						<div class="form-group control-username">
							<div class="form-field email-field">
								<input name="user_login" id="user_login" class="form-control control-icon" placeholder="<?php esc_attr_e('Enter your email', 'civi'); ?>" required>
								<a href="#" class="verify-email" data-title="<?php esc_attr_e('Resend', 'civi'); ?>"><?php echo esc_html__('Verify', 'civi'); ?></a>
							</div>
							<div class="form-field verify-field">
								<input name="verify_code" id="verify_code" class="form-control control-icon" placeholder="<?php esc_attr_e('Verify code', 'civi'); ?>">
							</div>
							<?php wp_nonce_field('civi_reset_password_ajax_nonce', 'civi_security_reset_password'); ?>
							<input type="hidden" name="action" id="reset_password_action" value="civi_reset_password_ajax">
							<input type="hidden" name="type" value="file">
							<p class="msg"><?php esc_html_e('Sending info, please wait...', 'civi'); ?></p>
							<button type="submit" class="civi_forgetpass btn gl-button"><?php esc_html_e('Get new password', 'civi'); ?></button>
						</div>
					</form>
					<a class="back-to-login" href="#"><i class="fas fa-arrow-left"></i><?php esc_html_e('Back to login', 'civi'); ?></a>
				</div>

				<form action="#" class="form-account ux-register" method="post">
					<?php
					$enable_user_role = Civi_Helper::civi_get_option('enable_user_role', '1');
					$enable_default_user_role = Civi_Helper::civi_get_option('enable_default_user_role');
					if ($enable_user_role) {
					?>
						<div class="form-group">
							<div class="row">
								<div class="col-6">
									<div class="col-group">
										<label for="civi_user_candidate" class="label-field radio-field">
											<input type="radio" value="civi_user_candidate" id="civi_user_candidate" name="account_type">
											<span><i class="fal fa-user"></i><?php esc_html_e('Candidate', 'civi'); ?></span>
										</label>
									</div>
								</div>
								<div class="col-6">
									<div class="col-group">
										<label for="civi_user_employer" class="label-field radio-field">
											<input type="radio" value="civi_user_employer" id="civi_user_employer" name="account_type" checked>
											<span><i class="fal fa-briefcase"></i><?php esc_html_e('Employer', 'civi'); ?></span>
										</label>
									</div>
								</div>
							</div>
						</div>
					<?php } else { ?>
						<?php if ($enable_default_user_role === 'employer') { ?>
							<input type="radio" checked value="civi_user_employer" id="civi_user_employer" name="account_type" class="hide">
						<?php } else { ?>
							<input type="radio" checked value="civi_user_candidate" id="civi_user_candidate" name="account_type" class="hide">
						<?php } ?>
					<?php } ?>
					<div class="form-group">
						<div class="row">
							<div class="col-6">
								<div class="col-group">
									<label for="ip_reg_firstname" class="label-field"><?php esc_html_e('First Name', 'civi'); ?></label>
									<input type="text" id="ip_reg_firstname" class="form-control input-field" name="reg_firstname" placeholder="<?php esc_attr_e('First Name', 'civi') ?>">
								</div>
							</div>
							<div class="col-6">
								<div class="col-group">
									<label for="ip_reg_lastname" class="label-field"><?php esc_html_e('Last Name', 'civi'); ?></label>
									<input type="text" id="ip_reg_lastname" class="form-control input-field" name="reg_lastname" placeholder="<?php esc_attr_e('Last Name', 'civi') ?>">
								</div>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label for="ip_reg_company_name" class="label-field"><?php esc_html_e('Username', 'civi'); ?></label>
						<input type="text" id="ip_reg_company_name" class="form-control input-field" name="reg_company_name" placeholder="<?php esc_attr_e('Enter Username', 'civi') ?>">
					</div>
					<div class="form-group">
						<label for="ip_reg_email" class="label-field"><?php esc_html_e('Email', 'civi'); ?></label>
						<input type="email" id="ip_reg_email" class="form-control input-field" name="reg_email" placeholder="<?php esc_attr_e('Enter Email', 'civi') ?>">
					</div>
					<div class="form-group">
						<label for="ip_reg_phone" class="label-field"><?php esc_html_e('Phone number', 'civi') ?></label>
						<div class="tel-group">
							<select name="prefix_code" class="civi-select2 prefix-code">
								<?php
								$prefix_code = Civi_Helper::phone_prefix_code();
								$default_phone = Civi_Helper::civi_get_option('default_phone_number');
								$selected_prefix = Civi_Helper::get_prefix_key_from_phone('', $prefix_code, $default_phone);
								foreach ($prefix_code as $key => $value) {
									$selected = ($key == $selected_prefix) ? 'selected' : '';
									echo '<option value="' . esc_attr($key) . '" data-dial-code="' . esc_attr($value['code']) . '" ' . $selected . '>' . esc_html($value['name']) . ' (' . esc_html($value['code']) . ')</option>';
								}
								?>
							</select>
							<?php
							$default_phone_code = isset($prefix_code[$selected_prefix]) ? $prefix_code[$selected_prefix]['code'] : (isset($prefix_code[$default_phone]) ? $prefix_code[$default_phone]['code'] : '');
							$input_value = !empty($default_phone) ? preg_replace('/^' . preg_quote($default_phone_code, '/') . '0+/', $default_phone_code, preg_replace('/[^0-9+]/', '', $default_phone)) : $default_phone_code;
							?>
							<input type="tel" id="ip_reg_phone" name="reg_phone"
								data-prefix="<?php echo esc_attr($default_phone_code); ?>"
								value="<?php echo esc_attr($input_value); ?>"
								placeholder="<?php esc_attr_e('Enter phone', 'civi') ?>"
								pattern="\+[0-9]{8,12}"
								required>
						</div>
					</div>
					<div class="form-group">
						<label for="ip_reg_password" class="label-field"><?php esc_html_e('Password', 'civi'); ?></label>
						<input type="password" id="ip_reg_password" class="form-control input-field" name="reg_password" autocomplete="on" placeholder="<?php esc_attr_e('Enter Password', 'civi') ?>">
						<span toggle="#ip_reg_password" class="fa fa-fw fa-eye field-icon civi-toggle-password"></span>
					</div>
					<?php
					$enable_captcha = Civi_Helper::civi_get_option('enable_captcha');
					$recaptcha_site_key = Civi_Helper::civi_get_option('recaptcha_site_key');
					$recaptcha_version = Civi_Helper::civi_get_option('recaptcha_version', 'v2');
					if ($enable_captcha && $recaptcha_site_key) :
						if ($recaptcha_version === 'v3') :
							echo '<script src="https://www.google.com/recaptcha/api.js?render=' . esc_attr($recaptcha_site_key) . '"></script>';
							echo '<input type="hidden" name="g_recaptcha_response" id="g_recaptcha_response_register_template" class="g-recaptcha-response">';
						else :
							echo '<div class="g-recaptcha" data-sitekey="' . esc_attr($recaptcha_site_key) . '" data-callback="verifyRegisterCaptcha"></div>';
							echo '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
						endif;
					endif;
					?>

					<div class="form-group accept-account">
						<?php
						$terms_condition 	= Civi_Helper::civi_get_option('terms_condition');
						$privacy_policy = Civi_Helper::civi_get_option('privacy_policy');
						?>
						<label for="ip_accept_account">
							<input type="checkbox" id="ip_accept_account" class="form-control custom-checkbox" name="accept_account">
							<?php printf(esc_html__('Accept the %1$s and %2$s', 'civi'), '<a href="' . get_permalink($terms_condition) . '">' . esc_html__('Terms', 'civi') . '</a>', '<a href="' . get_permalink($privacy_policy) . '">' . esc_html__('Privacy Policy', 'civi') . '</a>'); ?>
						</label>
					</div>

					<p class="msg"><?php esc_html_e('Sending register info, please wait...', 'civi'); ?></p>

					<div class="form-group">
						<input type="hidden" name="civi_recaptcha" value="<?php echo $enable_captcha; ?>">
						<button type="submit" class="gl-button btn button" value="<?php esc_attr_e('Sign in', 'civi'); ?>"><?php esc_html_e('Sign up', 'civi'); ?></button>
					</div>
				</form>

				<form action="#" id="ux-verify" class="form-account ux-verify" method="post">
					<?php if (Civi_Helper::civi_get_option('enable_verify_user') === '1') : ?>
						<div class="form-group">
							<label for="verify-code" class="label-field"><?php esc_html_e('Verify Email', 'civi'); ?></label>
							<input type="text" id="verify-code" class="form-control input-field" name="verify_code" placeholder="<?php esc_attr_e('Enter Code', 'civi') ?>">
							<a href="#" class="resend" data-resend="gmail">
								<?php esc_html_e('Resend', 'civi'); ?>
								<span class="btn-loading"><i class="fal fa-spinner fa-spin medium"></i></span>
							</a>
						</div>
					<?php endif; ?>
					<p class="msg"><?php esc_html_e('Sending register info, please wait...', 'civi'); ?></p>
					<div class="form-group">
						<button type="submit" class="gl-button btn button" value="<?php esc_attr_e('Verify', 'civi'); ?>"><?php esc_html_e('Verify', 'civi'); ?></button>
					</div>
				</form>

				<?php $enable_status_user = Civi_Helper::civi_get_option('enable_status_user'); ?>
				<?php if ($enable_status_user !== '0') { ?>
				<div class="form-account ux-pending-approval" style="display:none;">
					<h3 class="title"><?php esc_html_e('Account created', 'civi'); ?></h3>
					<p class="desc"><?php esc_html_e('Pending admin approval. We will email you once approved.', 'civi'); ?></p>
					<?php
						$pending_support_link = Civi_Helper::civi_get_option('pending_support_link');
						$pending_next_step_page = Civi_Helper::civi_get_option('pending_next_step_page');
						$pending_estimated_time = Civi_Helper::civi_get_option('pending_estimated_review_time');
						$enable_pending_next = Civi_Helper::civi_get_option('enable_pending_next_step');
						$has_next = ($enable_pending_next === '1') && !empty($pending_next_step_page);
						$has_time = !empty($pending_estimated_time);
					?>
					<?php if ($has_time) { ?>
						<ul class="notes">
							<li><?php echo sprintf(esc_html__('Estimated review time: %s.', 'civi'), esc_html($pending_estimated_time)); ?></li>
							<li><?php esc_html_e('You can close this window and return later.', 'civi'); ?></li>
						</ul>
					<?php } ?>
					<div class="form-group actions">
						<a href="<?php echo esc_url(home_url('/')); ?>" class="gl-button btn button"><?php esc_html_e('Go to Homepage', 'civi'); ?></a>
						<?php if ($has_next) { ?>
							<a href="<?php echo esc_url(get_page_link($pending_next_step_page)); ?>" class="gl-button btn button btn-secondary"><?php esc_html_e('Next steps', 'civi'); ?></a>
						<?php } ?>
					</div>
					<?php if (!empty($pending_support_link)) { ?>
						<p class="support-link"><a href="<?php echo esc_url($pending_support_link); ?>"><?php esc_html_e('Contact support', 'civi'); ?></a></p>
					<?php } ?>
				</div>
				<?php } ?>

			<?php endif; ?>
		</div>
		<?php
		$enable_social_login = \Civi_Helper::civi_get_option('enable_social_login');
		$shortcode_social_login = \Civi_Helper::civi_get_option('shortcode_social_login');
		if ($enable_social_login && $shortcode_social_login) {
			echo '<div class="footer-popup addon-login-wrap">';
			echo do_shortcode($shortcode_social_login);
			echo '</div>';
		}

		// Add reCAPTCHA privacy notice if badge is hidden
		$enable_captcha = \Civi_Helper::civi_get_option('enable_captcha');
		$recaptcha_version = \Civi_Helper::civi_get_option('recaptcha_version', 'v2');
		$hide_badge = \Civi_Helper::civi_get_option('recaptcha_v3_hide_badge', '0');

		if ($enable_captcha === '1' && $recaptcha_version === 'v3' && $hide_badge === '1') {
			echo '<div class="civi-recaptcha-notice">';
			echo esc_html__('This site is protected by reCAPTCHA and the Google ', 'civi');
			echo '<a href="https://policies.google.com/privacy" target="_blank" rel="noopener">' . esc_html__('Privacy Policy', 'civi') . '</a>';
			echo esc_html__(' and ', 'civi');
			echo '<a href="https://policies.google.com/terms" target="_blank" rel="noopener">' . esc_html__('Terms of Service', 'civi') . '</a>';
			echo esc_html__(' apply.', 'civi');
			echo '</div>';
		}
		?>
	</div>
</div>
