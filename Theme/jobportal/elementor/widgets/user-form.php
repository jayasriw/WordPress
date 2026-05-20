<?php

namespace JobPortal_Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

defined('ABSPATH') || exit;

class Widget_User_Form extends Base
{

	public function get_name()
	{
		return "jobportal-user-form';
	}

	public function get_title()
	{
		return esc_html__('Modern User Form', 'jobportal');
	}

	public function get_icon_part()
	{
		return 'eicon-form-horizontal';
	}

	public function get_keywords()
	{
		return ['form'];
	}

	public function get_script_depends()
	{
		return ['facebook-api', 'google-api', "jobportal-widget-user-form'];
	}

	public function get_style_depends()
	{
		return ["jobportal-el-widget-user-form'];
	}

	protected function register_controls()
	{
		$this->add_user_form_section();
	}

	private function add_user_form_section()
	{
		$this->start_controls_section('user_form_section', [
			'label' => esc_html__('User Form', 'jobportal'),
		]);

		$this->add_control('form', [
			'label'   => esc_html__('Form', 'jobportal'),
			'type'    => Controls_Manager::SELECT,
			'options' => [
				'login'   => 'Login',
				'register'   => 'Register',
				'login_register'   => 'Login & Register',
			],
			'default' => 'login',
		]);

		$this->add_control('role', [
			'label'   => esc_html__('Role', 'jobportal'),
			'type'    => Controls_Manager::SELECT,
			'options' => [
				'candidate'   => 'Candidate',
				'employer'   => 'Employer',
				'candidate_employer'   => 'Candidate or Employer',
			],
			'default' => 'candidate_employer',
			'condition'    => [
				'form!' => 'login',
			],
		]);

		$this->end_controls_section();
	}

	protected function render()
	{
		global $wp;
		$current_page_url = home_url($wp->request);
		$settings = $this->get_settings_for_display();
		$role = $settings['role'];
		$enable_captcha = \JobPortal_Helper::jobportal_get_option('enable_captcha');
		if ($enable_captcha) {
			// Check if session is not already started and headers haven't been sent
			if (\session_status() === PHP_SESSION_NONE && !\headers_sent()) {
				@\session_start();
			}
			// Set captcha value if session is active
			if (\session_status() === PHP_SESSION_ACTIVE) {
				$captcha = \rand(1000, 9999);
				$_SESSION['jobportal_captcha'] = $captcha;
			}
		}
		if (is_user_logged_in()) {
?>
			<p class="notice success"><i class="fal fa-check-circle"></i><?php esc_attr_e('You are logged in!', 'jobportal'); ?></p>
		<?php
		} else {
			// Add data attributes for JavaScript detection
			$enable_status_user = \JobPortal_Helper::jobportal_get_option('enable_status_user');
			$enable_verify_user = \JobPortal_Helper::jobportal_get_option('enable_verify_user');
		?>
			<div class="el-user-form"
				 data-enable-status-user="<?php echo esc_attr($enable_status_user); ?>"
				 data-enable-verify-user="<?php echo esc_attr($enable_verify_user); ?>">
			<?php
			if ($settings['form'] == 'login') {
			?>
				<form action="#" class="form-account active ux-login alway-show" method="post">

					<?php do_action('jobportal_user_demo_sign_in'); ?>

					<div class="form-group">
						<label for="el_ip_email" class="label-field"><?php esc_html_e('Account or Email', 'jobportal'); ?></label>
						<input type="text" id="el_ip_email" class="form-control input-field" name="email" placeholder="<?php esc_attr_e('Enter Account or Email', 'jobportal') ?>">
					</div>
					<div class="form-group">
						<label for="el_ip_password" class="label-field"><?php esc_html_e('Password', 'jobportal'); ?></label>
						<input type="password" id="el_ip_password" class="form-control input-field" name="password" autocomplete="on" placeholder="<?php esc_attr_e('Enter Password', 'jobportal') ?>">
					</div>

					<?php
					$enable_captcha = \JobPortal_Helper::jobportal_get_option('enable_captcha');
					$recaptcha_site_key = \JobPortal_Helper::jobportal_get_option('recaptcha_site_key');
					$recaptcha_version = \JobPortal_Helper::jobportal_get_option('recaptcha_version', 'v2');
					if ($enable_captcha && $recaptcha_site_key) :
						if ($recaptcha_version === 'v3') :
							echo '<script src="https://www.google.com/recaptcha/api.js?render=' . esc_attr($recaptcha_site_key) . '"></script>';
							echo '<input type="hidden" name="g_recaptcha_response" id="g_recaptcha_response_login" class="g-recaptcha-response">';
						else :
							echo '<div class="g-recaptcha" data-sitekey="' . esc_attr($recaptcha_site_key) . '" data-callback="verifyLoginCaptcha"></div>';
							echo '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
						endif;
					endif; ?>

					<div class="form-group">
						<div class="forgot-password">
							<span><?php esc_html_e('Forgot your password? ', 'jobportal'); ?></span>
							<a class="btn-reset-password" href="#"><?php esc_html_e('Reset password.', 'jobportal'); ?></a>
						</div>
					</div>

					<p class="msg"><?php esc_html_e('Sending login info, please wait...', 'jobportal'); ?></p>

					<div class="form-group">
						<input type="hidden" name="current_page" value="<?php echo $current_page_url; ?>">
						<input type="hidden" name="jobportal_recaptcha" value="<?php echo $enable_captcha; ?>">
						<button type="submit" class="gl-button btn button" value="<?php esc_attr_e('Sign in', 'jobportal'); ?>"><?php esc_html_e('Sign in', 'jobportal'); ?></button>
					</div>
				</form>
				<div class="jobportal-reset-password-wrap form-account">
					<div id="jobportal_messages_reset_password" class="jobportal_messages message"></div>
					<form method="post" enctype="multipart/form-data">
						<div class="form-group control-username">
							<input name="user_login" id="el_user_login" class="form-control control-icon" placeholder="<?php esc_attr_e('Enter your username or email', 'jobportal'); ?>">
							<?php wp_nonce_field('jobportal_reset_password_ajax_nonce', 'el_jobportal_security_reset_password'); ?>
							<input type="hidden" name="action" id="el_reset_password_action" value="jobportal_reset_password_ajax">
							<input type="hidden" name="type" value="elementor">
							<p class="msg"><?php esc_html_e('Sending info, please wait...', 'jobportal'); ?></p>
							<button type="submit" class="jobportal_forgetpass btn gl-button"><?php esc_html_e('Get new password', 'jobportal'); ?></button>
						</div>
					</form>
					<a class="back-to-login" href="#"><i class="fas fa-arrow-left"></i><?php esc_html_e('Back to login', 'jobportal'); ?></a>
				</div>
				<?php
				$enable_social_login = \JobPortal_Helper::jobportal_get_option('enable_social_login');
				$shortcode_social_login = \JobPortal_Helper::jobportal_get_option('shortcode_social_login');
				if ($enable_social_login && $shortcode_social_login) {
					echo '<div class="addon-login-wrap">';
					echo do_shortcode($shortcode_social_login);
					echo '</div>';
				}
				?>
			<?php
			} else if ($settings['form'] == 'register') {
			?>
				<form action="#" class="form-account active ux-register" method="post">
					<?php
					$enable_user_role = \JobPortal_Helper::jobportal_get_option('enable_user_role', '1');
					if ($enable_user_role && $role == 'candidate_employer') {
					?>
						<div class="form-group">
							<div class="row">
								<div class="col-6">
									<div class="col-group">
										<label for="jobportal_user_candidate1" class="label-field radio-field">
											<input type="radio" value="jobportal_user_candidate" id="jobportal_user_candidate1" name="account_type">
											<span><i class="fal fa-user"></i><?php esc_html_e('Candidate', 'jobportal'); ?></span>
										</label>
									</div>
								</div>
								<div class="col-6">
									<div class="col-group">
										<label for="jobportal_user_employer1" class="label-field radio-field">
											<input type="radio" value="jobportal_user_employer" id="jobportal_user_employer1" name="account_type" checked>
											<span><i class="fal fa-briefcase"></i><?php esc_html_e('Employer', 'jobportal'); ?></span>
										</label>
									</div>
								</div>
							</div>
						</div>
					<?php } else if ($role == 'candidate') { ?>
						<input type="radio" class="hide" value="jobportal_user_candidate" id="jobportal_user_candidate1" name="account_type" checked>
					<?php } else if ($role == 'employer') { ?>
						<input type="radio" class="hide" value="jobportal_user_employer" id="jobportal_user_employer1" name="account_type" checked>
					<?php } else { ?>
						<input type="radio" class="hide" value="jobportal_user_candidate" id="jobportal_user_candidate1" name="account_type" checked>
					<?php } ?>
					<div class="form-group">
						<div class="row">
							<div class="col-6">
								<div class="col-group">
									<label for="el_ip_reg_firstname" class="label-field"><?php esc_html_e('First Name', 'jobportal'); ?><span class="require">*</span></label>
									<input type="text" id="el_ip_reg_firstname" class="form-control input-field" name="reg_firstname" placeholder="<?php esc_attr_e('First Name', 'jobportal') ?>">
								</div>
							</div>
							<div class="col-6">
								<div class="col-group">
									<label for="ip_reg_lastname" class="label-field"><?php esc_html_e('Last Name', 'jobportal'); ?><span class="require">*</span></label>
									<input type="text" id="ip_reg_lastname" class="form-control input-field" name="reg_lastname" placeholder="<?php esc_attr_e('Last Name', 'jobportal') ?>">
								</div>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label for="ip_reg_company_name" class="label-field"><?php esc_html_e('Username', 'jobportal'); ?><span class="require">*</span></label>
						<input type="text" id="ip_reg_company_name" class="form-control input-field" name="reg_company_name" placeholder="<?php esc_attr_e('Enter Username', 'jobportal') ?>">
					</div>
					<div class="form-group">
						<label for="el_ip_reg_email" class="label-field"><?php esc_html_e('Email', 'jobportal'); ?><span class="require">*</span></label>
						<input type="email" id="el_ip_reg_email" class="form-control input-field" name="reg_email" placeholder="<?php esc_attr_e('Enter Email', 'jobportal') ?>">
					</div>
					<div class="form-group">
						<label for="ip_reg_phone" class="label-field"><?php esc_html_e('Phone number', 'jobportal'); ?><span class="require">*</span></label>
						<div class="tel-group">
							<select name="prefix_code" class="jobportal-select2 prefix-code">
								<?php
								$prefix_code = \JobPortal_Helper::phone_prefix_code();
								$default_phone = \JobPortal_Helper::jobportal_get_option('default_phone_number');
								$selected_prefix = \JobPortal_Helper::get_prefix_key_from_phone('', $prefix_code, $default_phone);
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
								placeholder="<?php esc_attr_e('Enter phone', 'jobportal') ?>"
								pattern="\+[0-9]{8,12}"
								required>
						</div>
					</div>
					<div class="form-group">
						<label for="ip_reg_password" class="label-field"><?php esc_html_e('Password', 'jobportal'); ?><span class="require">*</span></label>
						<input type="password" id="ip_reg_password" class="form-control input-field" name="reg_password" autocomplete="on" placeholder="<?php esc_attr_e('Enter Password', 'jobportal') ?>">
					</div>
					<?php
					$enable_captcha = \JobPortal_Helper::jobportal_get_option('enable_captcha');
					$recaptcha_site_key = \JobPortal_Helper::jobportal_get_option('recaptcha_site_key');
					$recaptcha_version = \JobPortal_Helper::jobportal_get_option('recaptcha_version', 'v2');
					if ($enable_captcha && $recaptcha_site_key) :
						if ($recaptcha_version === 'v3') :
							echo '<script src="https://www.google.com/recaptcha/api.js?render=' . esc_attr($recaptcha_site_key) . '"></script>';
							echo '<input type="hidden" name="g_recaptcha_response" id="g_recaptcha_response_register" class="g-recaptcha-response">';
						else :
							echo '<div class="g-recaptcha" data-sitekey="' . esc_attr($recaptcha_site_key) . '" data-callback="verifyRegisterCaptcha"></div>';
							echo '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
						endif;
					endif; ?>
					<div class="form-group accept-account">
						<?php
						$terms_login = \JobPortal_Helper::jobportal_get_option('terms_condition');
						$privacy_policy = \JobPortal_Helper::jobportal_get_option('privacy_policy');
						?>
						<label for="ip_accept_account1">
							<input type="checkbox" id="ip_accept_account1" class="form-control custom-checkbox" name="accept_account">
							<?php printf(esc_html__('Accept the %1$s and %2$s', 'jobportal'), '<a href="' . get_permalink($terms_login) . '">' . esc_html__('Terms', 'jobportal') . '</a>', '<a href="' . get_permalink($privacy_policy) . '">' . esc_html__('Privacy Policy', 'jobportal') . '</a>'); ?>
						</label>
					</div>
					<?php do_action('jobportal_before_sign_up_button'); ?>
					<p class="msg"><?php esc_html_e('Sending register info, please wait...', 'jobportal'); ?></p>

					<div class="form-group">
						<input type="hidden" name="jobportal_recaptcha" value="<?php echo $enable_captcha; ?>">
						<button type="submit" class="gl-button btn button" value="<?php esc_attr_e('Sign in', 'jobportal'); ?>"><?php esc_html_e('Sign up', 'jobportal'); ?></button>
					</div>
				</form>

				<form action="#" id="ux-verify" class="form-account ux-verify" method="post">
					<?php if (\JobPortal_Helper::jobportal_get_option('enable_verify_user') === '1') : ?>
						<div class="form-group">
							<label for="verify-code" class="label-field"><?php esc_html_e('Verify Email', 'jobportal'); ?></label>
							<input type="text" id="verify-code" class="form-control input-field" name="verify_code" placeholder="<?php esc_attr_e('Enter Code', 'jobportal') ?>">
							<a href="#" class="resend" data-resend="gmail">
								<?php esc_html_e('Resend', 'jobportal'); ?>
								<span class="btn-loading"><i class="fal fa-spinner fa-spin medium"></i></span>
							</a>
						</div>
					<?php endif; ?>
					<p class="msg"><?php esc_html_e('Sending register info, please wait...', 'jobportal'); ?></p>
					<div class="form-group">
						<button type="submit" class="gl-button btn button" value="<?php esc_attr_e('Verify', 'jobportal'); ?>"><?php esc_html_e('Verify', 'jobportal'); ?></button>
					</div>
				</form>
				<?php $enable_status_user = \JobPortal_Helper::jobportal_get_option('enable_status_user'); ?>
				<?php if ($enable_status_user !== '0') { ?>
				<div class="form-account ux-pending-approval" style="display:none;">
					<h3 class="title"><?php esc_html_e('Account created', 'jobportal'); ?></h3>
					<p class="desc"><?php esc_html_e('Pending admin approval. We will email you once approved.', 'jobportal'); ?></p>
					<?php
						$pending_support_link = \JobPortal_Helper::jobportal_get_option('pending_support_link');
						$pending_next_step_page = \JobPortal_Helper::jobportal_get_option('pending_next_step_page');
						$pending_estimated_time = \JobPortal_Helper::jobportal_get_option('pending_estimated_review_time');
						$enable_pending_next = \JobPortal_Helper::jobportal_get_option('enable_pending_next_step');
						$has_next = ($enable_pending_next === '1') && !empty($pending_next_step_page);
						$has_time = !empty($pending_estimated_time);
					?>
					<?php if ($has_time) { ?>
						<ul class="notes">
							<li><?php echo sprintf(esc_html__('Estimated review time: %s.', 'jobportal'), esc_html($pending_estimated_time)); ?></li>
							<li><?php esc_html_e('You can close this window and return later.', 'jobportal'); ?></li>
						</ul>
					<?php } ?>
					<div class="form-group actions">
						<a href="<?php echo esc_url(home_url('/')); ?>" class="gl-button btn button"><?php esc_html_e('Go to Homepage', 'jobportal'); ?></a>
						<?php if ($has_next) { ?>
							<a href="<?php echo esc_url(get_page_link($pending_next_step_page)); ?>" class="gl-button btn button btn-secondary"><?php esc_html_e('Next steps', 'jobportal'); ?></a>
						<?php } ?>
					</div>
					<?php if (!empty($pending_support_link)) { ?>
						<p class="support-link"><a href="<?php echo esc_url($pending_support_link); ?>"><?php esc_html_e('Contact support', 'jobportal'); ?></a></p>
					<?php } ?>
				</div>
				<?php } ?>
			<?php
			} else {
			?>
				<div class="el-user-form">
					<div class="el-uf-nav">
						<a href="#el-ux-login" class="btn-login active" data-form="el-ux-login"><?php echo esc_html__('Log in', 'jobportal'); ?></a>
						<a href="#el-ux-register" class="btn-register" data-form="el-ux-register"><?php echo esc_html__('Sign Up', 'jobportal'); ?></a>
					</div>
					<div class="el-uf-content">
						<div class="el-uf-item el-ux-login active">
							<form action="#" class="form-account active ux-login alway-show" method="post">

								<?php do_action('jobportal_user_demo_sign_in'); ?>

								<div class="form-group">
									<label for="el_ip_email" class="label-field"><?php esc_html_e('Account or Email', 'jobportal'); ?></label>
									<input type="text" id="el_ip_email" class="form-control input-field" name="email" placeholder="<?php esc_attr_e('Enter Account or Email', 'jobportal') ?>">
								</div>
								<div class="form-group">
									<label for="el_ip_password" class="label-field"><?php esc_html_e('Password', 'jobportal'); ?></label>
									<input type="password" id="el_ip_password" class="form-control input-field" name="password" autocomplete="on" placeholder="<?php esc_attr_e('Enter Password', 'jobportal') ?>">
								</div>

								<?php
								$enable_captcha = \JobPortal_Helper::jobportal_get_option('enable_captcha');
								$recaptcha_site_key = \JobPortal_Helper::jobportal_get_option('recaptcha_site_key');
								$recaptcha_version = \JobPortal_Helper::jobportal_get_option('recaptcha_version', 'v2');
								if ($enable_captcha && $recaptcha_site_key) :
									if ($recaptcha_version === 'v3') :
										echo '<script src="https://www.google.com/recaptcha/api.js?render=' . esc_attr($recaptcha_site_key) . '"></script>';
										echo '<input type="hidden" name="g_recaptcha_response" id="g_recaptcha_response_login_popup" class="g-recaptcha-response">';
									else :
										echo '<div class="g-recaptcha" data-sitekey="' . esc_attr($recaptcha_site_key) . '" data-callback="verifyLoginCaptcha"></div>';
										echo '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
									endif;
								endif;
								?>

								<div class="form-group">
									<div class="forgot-password">
										<span><?php esc_html_e('Forgot your password? ', 'jobportal'); ?></span>
										<a class="btn-reset-password" href="#"><?php esc_html_e('Reset password.', 'jobportal'); ?></a>
									</div>
								</div>

								<p class="msg"><?php esc_html_e('Sending login info, please wait...', 'jobportal'); ?></p>

								<div class="form-group">
									<input type="hidden" name="current_page" value="<?php echo $current_page_url; ?>">
									<input type="hidden" name="jobportal_recaptcha" value="<?php echo $enable_captcha; ?>">
									<button type="submit" class="gl-button btn button" value="<?php esc_attr_e('Sign in', 'jobportal'); ?>"><?php esc_html_e('Sign in', 'jobportal'); ?></button>
								</div>
							</form>
							<div class="jobportal-reset-password-wrap form-account">
								<div id="jobportal_messages_reset_password" class="jobportal_messages message"></div>
								<form method="post" enctype="multipart/form-data">
									<div class="form-group control-username">
										<input name="user_login" id="el_user_login" class="form-control control-icon" placeholder="<?php esc_attr_e('Enter your username or email', 'jobportal'); ?>">
										<?php wp_nonce_field('jobportal_reset_password_ajax_nonce', 'el_jobportal_security_reset_password'); ?>
										<input type="hidden" name="action" id="el_reset_password_action" value="jobportal_reset_password_ajax">
										<input type="hidden" name="type" value="elementor">
										<p class="msg"><?php esc_html_e('Sending info, please wait...', 'jobportal'); ?></p>
										<button type="submit" class="jobportal_forgetpass btn gl-button"><?php esc_html_e('Get new password', 'jobportal'); ?></button>
									</div>
								</form>
								<a class="back-to-login" href="#"><i class="fas fa-arrow-left"></i><?php esc_html_e('Back to login', 'jobportal'); ?></a>
							</div>
							<?php
							$enable_social_login = \JobPortal_Helper::jobportal_get_option('enable_social_login');
							$shortcode_social_login = \JobPortal_Helper::jobportal_get_option('shortcode_social_login');
							if ($enable_social_login && $shortcode_social_login) {
								echo '<div class="addon-login-wrap">';
								echo do_shortcode($shortcode_social_login);
								echo '</div>';
							}
							?>
						</div>
						<div class="el-uf-item el-ux-register">
							<form action="#" class="form-account active ux-register" method="post">
								<?php
								$enable_user_role = \JobPortal_Helper::jobportal_get_option('enable_user_role', '1');
								if ($enable_user_role && $role == 'candidate_employer') {
								?>
									<div class="form-group">
										<div class="row">
											<div class="col-6">
												<div class="col-group">
													<label for="jobportal_user_candidate1" class="label-field radio-field">
														<input type="radio" value="jobportal_user_candidate" id="jobportal_user_candidate1" name="account_type">
														<span><i class="fal fa-user"></i><?php esc_html_e('Candidate', 'jobportal'); ?></span>
													</label>
												</div>
											</div>
											<div class="col-6">
												<div class="col-group">
													<label for="jobportal_user_employer1" class="label-field radio-field">
														<input type="radio" value="jobportal_user_employer" id="jobportal_user_employer1" name="account_type" checked>
														<span><i class="fal fa-briefcase"></i><?php esc_html_e('Employer', 'jobportal'); ?></span>
													</label>
												</div>
											</div>
										</div>
									</div>
								<?php } else if ($role == 'candidate') { ?>
									<input type="radio" class="hide" value="jobportal_user_candidate" id="jobportal_user_candidate1" name="account_type" checked>
								<?php } else if ($role == 'employer') { ?>
									<input type="radio" class="hide" value="jobportal_user_employer" id="jobportal_user_employer1" name="account_type" checked>
								<?php } else { ?>
									<input type="radio" class="hide" value="jobportal_user_candidate" id="jobportal_user_candidate1" name="account_type" checked>
								<?php } ?>
								<div class="form-group">
									<div class="row">
										<div class="col-6">
											<div class="col-group">
												<label for="el_ip_reg_firstname" class="label-field"><?php esc_html_e('First Name', 'jobportal'); ?><span class="require">*</span></label>
												<input type="text" id="el_ip_reg_firstname" class="form-control input-field" name="reg_firstname" placeholder="<?php esc_attr_e('Name', 'jobportal') ?>">
											</div>
										</div>
										<div class="col-6">
											<div class="col-group">
												<label for="el_ip_reg_lastname" class="label-field"><?php esc_html_e('Last Name', 'jobportal'); ?><span class="require">*</span></label>
												<input type="text" id="el_ip_reg_lastname" class="form-control input-field" name="reg_lastname" placeholder="<?php esc_attr_e('Name', 'jobportal') ?>">
											</div>
										</div>
									</div>
								</div>
								<div class="form-group">
									<label for="el_ip_reg_company_name" class="label-field"><?php esc_html_e('Username', 'jobportal'); ?><span class="require">*</span></label>
									<input type="text" id="el_ip_reg_company_name" class="form-control input-field" name="reg_company_name" placeholder="<?php esc_attr_e('Enter Username', 'jobportal') ?>">
								</div>
								<div class="form-group">
									<label for="el_ip_reg_email" class="label-field"><?php esc_html_e('Email', 'jobportal'); ?><span class="require">*</span></label>
									<input type="email" id="el_ip_reg_email" class="form-control input-field" name="reg_email" placeholder="<?php esc_attr_e('Enter Email', 'jobportal') ?>">
								</div>
								<div class="form-group">
									<label for="ip_reg_phone" class="label-field"><?php esc_html_e('Phone number', 'jobportal'); ?><span class="require">*</span></label>
									<div class="tel-group">
										<select name="prefix_code" class="jobportal-select2 prefix-code">
											<?php
											$prefix_code = \JobPortal_Helper::phone_prefix_code();
											$default_phone = \JobPortal_Helper::jobportal_get_option('default_phone_number');
											$selected_prefix = \JobPortal_Helper::get_prefix_key_from_phone('', $prefix_code, $default_phone);
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
											placeholder="<?php esc_attr_e('Enter phone', 'jobportal') ?>"
											pattern="\+[0-9]{8,12}"
											required>
									</div>
								</div>
								<div class="form-group">
									<label for="el_ip_reg_password" class="label-field"><?php esc_html_e('Password', 'jobportal'); ?><span class="require">*</span></label>
									<input type="password" id="el_ip_reg_password" class="form-control input-field" name="reg_password" autocomplete="on" placeholder="<?php esc_attr_e('Enter Password', 'jobportal') ?>">
								</div>
								<?php
								$enable_captcha = \JobPortal_Helper::jobportal_get_option('enable_captcha');
								$recaptcha_site_key = \JobPortal_Helper::jobportal_get_option('recaptcha_site_key');
								$recaptcha_version = \JobPortal_Helper::jobportal_get_option('recaptcha_version', 'v2');
								if ($enable_captcha && $recaptcha_site_key) :
									if ($recaptcha_version === 'v3') :
										echo '<script src="https://www.google.com/recaptcha/api.js?render=' . esc_attr($recaptcha_site_key) . '"></script>';
										echo '<input type="hidden" name="g_recaptcha_response" id="g_recaptcha_response_register_popup" class="g-recaptcha-response">';
									else :
										echo '<div class="g-recaptcha" data-sitekey="' . esc_attr($recaptcha_site_key) . '" data-callback="verifyRegisterCaptcha"></div>';
										echo '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
									endif;
								endif; ?>
								<div class="form-group accept-account">
									<?php
									$terms_login 	= \JobPortal_Helper::jobportal_get_option('terms_condition');
									$privacy_policy = \JobPortal_Helper::jobportal_get_option('privacy_policy');
									?>
									<label for="ip_accept_account1">
										<input type="checkbox" id="ip_accept_account1" class="form-control custom-checkbox" name="accept_account">
										<?php printf(esc_html__('Accept the %1$s and %2$s', 'jobportal'), '<a href="' . get_permalink($terms_login) . '">' . esc_html__('Terms', 'jobportal') . '</a>', '<a href="' . get_permalink($privacy_policy) . '">' . esc_html__('Privacy Policy', 'jobportal') . '</a>'); ?>
									</label>
								</div>

								<?php do_action('jobportal_before_sign_up_button'); ?>

								<p class="msg"><?php esc_html_e('Sending register info, please wait...', 'jobportal'); ?></p>

								<div class="form-group">
									<input type="hidden" name="jobportal_recaptcha" value="<?php echo $enable_captcha; ?>">
									<button type="submit" class="gl-button btn button" value="<?php esc_attr_e('Sign in', 'jobportal'); ?>"><?php esc_html_e('Sign up', 'jobportal'); ?></button>
								</div>
							</form>

							<div class="form-account ux-pending-approval" style="display:none;">
								<h3 class="title"><?php esc_html_e('Account created', 'jobportal'); ?></h3>
								<p class="desc"><?php esc_html_e('Pending admin approval. We will email you once approved.', 'jobportal'); ?></p>
								<ul class="notes">
									<li><?php esc_html_e('Estimated review time: 24–48 hours.', 'jobportal'); ?></li>
									<li><?php esc_html_e('You can close this window and return later.', 'jobportal'); ?></li>
								</ul>
								<div class="form-group actions">
									<a href="<?php echo esc_url(home_url('/')); ?>" class="gl-button btn button"><?php esc_html_e('Go to Homepage', 'jobportal'); ?></a>
								</div>
							</div>

							<form action="#" id="ux-verify" class="form-account ux-verify" method="post">
								<?php if (\JobPortal_Helper::jobportal_get_option('enable_verify_user') === '1') : ?>
									<div class="form-group">
										<label for="verify-code" class="label-field"><?php esc_html_e('Verify Email', 'jobportal'); ?></label>
										<input type="text" id="verify-code" class="form-control input-field" name="verify_code" placeholder="<?php esc_attr_e('Enter Code', 'jobportal') ?>">
										<a href="#" class="resend" data-resend="gmail">
											<?php esc_html_e('Resend', 'jobportal'); ?>
											<span class="btn-loading"><i class="fal fa-spinner fa-spin medium"></i></span>
										</a>
									</div>
								<?php endif; ?>
								<p class="msg"><?php esc_html_e('Sending register info, please wait...', 'jobportal'); ?></p>
								<div class="form-group">
									<button type="submit" class="gl-button btn button" value="<?php esc_attr_e('Verify', 'jobportal'); ?>"><?php esc_html_e('Verify', 'jobportal'); ?></button>
								</div>
							</form>
						</div>
					</div>
				</div>
			<?php
			}

			// Add reCAPTCHA privacy notice if badge is hidden
			$enable_captcha = \JobPortal_Helper::jobportal_get_option('enable_captcha');
			$recaptcha_version = \JobPortal_Helper::jobportal_get_option('recaptcha_version', 'v2');
			$hide_badge = \JobPortal_Helper::jobportal_get_option('recaptcha_v3_hide_badge', '0');

			if ($enable_captcha === '1' && $recaptcha_version === 'v3' && $hide_badge === '1') {
				echo '<div class="jobportal-recaptcha-notice">';
				echo esc_html__('This site is protected by reCAPTCHA and the Google ', 'jobportal');
				echo '<a href="https://policies.google.com/privacy" target="_blank" rel="noopener">' . esc_html__('Privacy Policy', 'jobportal') . '</a>';
				echo esc_html__(' and ', 'jobportal');
				echo '<a href="https://policies.google.com/terms" target="_blank" rel="noopener">' . esc_html__('Terms of Service', 'jobportal') . '</a>';
				echo esc_html__(' apply.', 'jobportal');
				echo '</div>';
			}
			?>
			</div> <!-- Close el-user-form div -->
<?php
		}
	}
}
