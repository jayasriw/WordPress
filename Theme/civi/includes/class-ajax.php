<?php
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('Civi_Ajax_Include')) {

	/**
	 *  Class Civi_Ajax
	 */
	class Civi_Ajax_Include
	{

		/**
		 * The constructor.
		 */
		public function __construct()
		{
			add_action('wp_ajax_get_login_user', array($this, 'get_login_user'));
			add_action('wp_ajax_nopriv_get_login_user', array($this, 'get_login_user'));

			add_action('wp_ajax_get_register_user', array($this, 'get_register_user'));
			add_action('wp_ajax_nopriv_get_register_user', array($this, 'get_register_user'));

			add_action('wp_ajax_verify_code', array($this, 'verify_code'));
			add_action('wp_ajax_nopriv_verify_code', array($this, 'verify_code'));

			add_action('wp_ajax_civi_verify_resend', array($this, 'civi_verify_resend'));
			add_action('wp_ajax_nopriv_civi_verify_resend', array($this, 'civi_verify_resend'));

			// Reset password
			add_action('wp_ajax_civi_verify_email', array($this, 'civi_verify_email'));
			add_action('wp_ajax_nopriv_civi_verify_email', array($this, 'civi_verify_email'));

			add_action('wp_ajax_civi_reset_password_ajax', array($this, 'reset_password_ajax'));
			add_action('wp_ajax_nopriv_civi_reset_password_ajax', array($this, 'reset_password_ajax'));

			add_action('wp_ajax_change_password_ajax', array($this, 'change_password_ajax'));
			add_action('wp_ajax_nopriv_change_password_ajax', array($this, 'change_password_ajax'));
		}

		//////////////////////////////////////////////////////////////////
		// Ajax Login
		//////////////////////////////////////////////////////////////////
		function get_login_user()
		{
			// Security check with nonce
			check_ajax_referer('login_nonce', 'security');

			// Sanitize input data
			$email       = sanitize_text_field($_POST['email']);
			$password    = sanitize_text_field($_POST['password']);
			$reload      = esc_url_raw($_POST['reload']);

			$user_login                  = $email;
			$url_redirect                = '';
			$enable_redirect_after_login = Civi_Helper::civi_get_option('enable_redirect_after_login');
			$enable_status               = Civi_Helper::civi_get_option('enable_status_user');
			$enable_captcha              = Civi_Helper::civi_get_option('enable_captcha');
			$redirect_for_admin          = Civi_Helper::civi_get_option('redirect_for_admin');
			$redirect_for_employer       = Civi_Helper::civi_get_option('redirect_for_employer');
			$redirect_for_candidate      = Civi_Helper::civi_get_option('redirect_for_candidate');

		// Verify reCAPTCHA only if enabled
		if ($enable_captcha === '1') {
			$recaptcha_secret_key = Civi_Helper::civi_get_option('recaptcha_secret_key');
			$recaptcha_response = sanitize_text_field($_POST['g_recaptcha_response'] ?? '');
			$recaptcha_version = Civi_Helper::civi_get_option('recaptcha_version', 'v2');

			$verify_result = $this->verify_recaptcha($recaptcha_secret_key, $recaptcha_response, $recaptcha_version);
			if (!$verify_result['valid']) {
				// Use specific error message if available, otherwise use generic
				$msg = isset($verify_result['error_message_key']) ? $verify_result['error_message_key'] : 'recaptcha_error';
				$debug_info = array('success' => false, 'messages' => $msg, 'class' => 'text-error');

				// Add debug info if WP_DEBUG is enabled
				if (defined('WP_DEBUG') && WP_DEBUG && isset($verify_result['debug'])) {
					$debug_info['debug'] = $verify_result['debug'];
				}

				echo json_encode($debug_info);
				wp_die();
			}
		}

		if (is_email($email)) {
				if (!(email_exists($email))) {
					$msg = 'login_error';
					echo json_encode(array('success' => false, 'messages' => $msg, 'class' => 'text-error'));
					wp_die();
				}
				$current_user = get_user_by('email', $email);
				$user_login   = $current_user->user_login;

				if (!wp_check_password($password, $current_user->data->user_pass, $current_user->ID)) {
					$msg = 'login_error';
					echo json_encode(array('success' => false, 'messages' => $msg, 'class' => 'text-error'));
					wp_die();
				}
			}

			// reCAPTCHA verification already handled above

			$array                  = array();
			$array['user_login']    = $user_login;
			$array['user_password'] = $password;
			$array['remember']      = true;
			$user                   = wp_signon($array, false);

			if (!is_wp_error($user)) {

				if ($enable_status) {
					$user        = get_user_by('login', $user_login);
					$user_id     = $user->ID;
					$user_status = get_the_author_meta(CIVI_METABOX_PREFIX . 'user_status', $user_id);
					if ($user_status === 'approve') {
						$msg = 'login_success';
					} else {
						wp_logout();
						$msg = 'waiting_approval';
						echo json_encode(array('success' => false, 'messages' => $msg, 'class' => 'text-error'));
						wp_die();
					}
				}

				$users = get_user_by('login', $user_login);
				if (in_array('civi_user_candidate', (array) $users->roles) && $enable_redirect_after_login && $redirect_for_candidate != '') {
					if ($redirect_for_candidate == 'reload') {
						$url_redirect = $reload;
					} else {
						$url_redirect = get_page_link($redirect_for_candidate);
					}
				} else if (in_array('civi_user_employer', (array) $users->roles) && $enable_redirect_after_login && $redirect_for_employer != '') {
					if ($redirect_for_employer == 'reload') {
						$url_redirect = $reload;
					} else {
						$url_redirect = get_page_link($redirect_for_employer);
					}
				} else if (in_array('administrator', (array) $users->roles) && $enable_redirect_after_login && $redirect_for_admin != '') {
					if ($redirect_for_admin == 'reload') {
						$url_redirect = $reload;
					} else {
						$url_redirect = get_page_link($redirect_for_admin);
					}
				}
				$url_redirect = add_query_arg('login', 'success', $url_redirect);
				$msg = 'login_success';

				echo json_encode(array(
					'success'      => true,
					'messages'     => $msg,
					'class'        => 'text-success',
					'url_redirect' => $url_redirect
				));
			} else {
				$msg = 'login_error';
				echo json_encode(array('success' => false, 'messages' => $msg, 'class' => 'text-error'));
			}
			wp_die();
		}

		//////////////////////////////////////////////////////////////////
		// Ajax Register
		//////////////////////////////////////////////////////////////////
		function get_register_user()
		{
			check_ajax_referer('register_nonce', 'security'); // Nonce for security

			// Sanitize user input
			$account_type = sanitize_text_field($_POST['account_type']);
			$firstname    = sanitize_text_field($_POST['firstname']);
			$lastname     = sanitize_text_field($_POST['lastname']);
			$companyname  = sanitize_text_field($_POST['companyname']);
			$email        = sanitize_email($_POST['email']);
			$phone        = sanitize_text_field($_POST['phone']);
			$phone_code   = sanitize_text_field($_POST['phone_code']);
			$password     = $_POST['password'];
			$user_login   = sanitize_user($companyname);
			$url_redirect                = '';
			$enable_redirect_after_login = Civi_Helper::civi_get_option('enable_redirect_after_login');
			$enable_captcha              = Civi_Helper::civi_get_option('enable_captcha');
			$enable_status               = Civi_Helper::civi_get_option('enable_status_user');
			$enable_verify_user          = Civi_Helper::civi_get_option('enable_verify_user');
			$redirect_for_admin     = Civi_Helper::civi_get_option('redirect_for_admin');
			$redirect_for_employer  = Civi_Helper::civi_get_option('redirect_for_employer');
			$redirect_for_candidate = Civi_Helper::civi_get_option('redirect_for_candidate');
		$type_name_candidate    = Civi_Helper::civi_get_option('type_name_candidate');

		$enabled_user_role = Civi_Helper::civi_get_option('enable_user_role');
		$default_user_role = Civi_Helper::civi_get_option('enable_default_user_role');

		if ($enabled_user_role === '0') {
			$account_type = ($default_user_role === 'candidate') ? 'civi_user_candidate' : 'civi_user_employer';
		}

		// Verify reCAPTCHA only if enabled
		if ($enable_captcha === '1') {
			$recaptcha_secret_key = Civi_Helper::civi_get_option('recaptcha_secret_key');
			$recaptcha_response = sanitize_text_field($_POST['g_recaptcha_response'] ?? '');
			$recaptcha_version = Civi_Helper::civi_get_option('recaptcha_version', 'v2');

			$verify_result = $this->verify_recaptcha($recaptcha_secret_key, $recaptcha_response, $recaptcha_version);
			if (!$verify_result['valid']) {
				// Use specific error message if available, otherwise use generic
				$msg = isset($verify_result['error_message_key']) ? $verify_result['error_message_key'] : 'recaptcha_error';
				$debug_info = array('success' => false, 'messages' => $msg, 'class' => 'text-error');

				// Add debug info if WP_DEBUG is enabled
				if (defined('WP_DEBUG') && WP_DEBUG && isset($verify_result['debug'])) {
					$debug_info['debug'] = $verify_result['debug'];
				}

				echo json_encode($debug_info);
				wp_die();
			}
		}

		$userdata = array(
				'user_login'   => $user_login,
				'first_name'   => $firstname,
				'last_name'    => $lastname,
				'user_email'   => $email,
				'user_phone'   => $phone,
				'user_pass'    => $password,
				'account_type' => $account_type
			);

			if ($type_name_candidate === 'fl-name') {
				$userdata['display_name'] = $firstname . ' ' . $lastname;
			} else {
				$userdata['display_name'] = $companyname;
			}

			global $wpdb;
			$user = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT user_login, user_email FROM {$wpdb->users} WHERE user_login = %s OR user_email = %s",
					$user_login,
					$email
				)
			);
			if ($user) {
				$msg = '';
				if ($user->user_login == $user_login) {
					$msg = 'username_already';
				} else {
					$msg = 'email_already';
				}
				echo json_encode(array('success' => false, 'messages' => $msg, 'class' => 'text-error'));
				wp_die();
			} else {
				if ($enable_verify_user) {
					// reCAPTCHA verification already handled above
					$code = rand(100000, 999999);
					update_option($email, $code);
					$args = array(
						'code_verify_user' => $code,
					);
					Civi_Helper::civi_send_email($email, 'mail_verify_user', $args);
					echo json_encode(array(
						'success'      => true,
						'verify'       => true,
						'user_login'   => $user_login,
						'password'     => $password,
						'email'        => $email,
						'userdata'     => $userdata,
						'class'        => 'text-success',
						'url_redirect' => $url_redirect
					));
				} else {
					// Store account_type, phone, and phone_code in global variables so user_register hook can access them
					global $civi_registering_account_type, $civi_registering_phone, $civi_registering_phone_code;
					$civi_registering_account_type = $account_type;
					$civi_registering_phone = $phone;
					$civi_registering_phone_code = $phone_code;

					$user_id = wp_insert_user($userdata);

					if (is_wp_error($user_id) || $user_id == 0) {
						$user_login = substr($email, 0, strpos($email, '@'));
						$userdata   = array(
							'user_login'   => $user_login,
							'first_name'   => $firstname,
							'last_name'    => $lastname,
							'user_email'   => $email,
							'user_pass'    => $password,
							'account_type' => $account_type
						);

						if ($type_name_candidate === 'fl-name') {
							$userdata['display_name'] = $firstname . ' ' . $lastname;
						} else {
							$userdata['display_name'] = $companyname;
						}

						$user_id = wp_insert_user($userdata);
					}

					// Clean up global variables after user creation
					$civi_registering_account_type = null;
					$civi_registering_phone = null;
					$civi_registering_phone_code = null;

					if (!is_wp_error($user_id) && $user_id > 0) {
						update_user_meta($user_id, CIVI_METABOX_PREFIX . 'author_mobile_number', $phone);
						update_user_meta($user_id, CIVI_METABOX_PREFIX . 'phone_code', $phone_code);
					}

					$msg = '';

					// reCAPTCHA verification already handled above

					if (!is_wp_error($user_id)) {
						if ($account_type == 'civi_user_employer') {
							$u = new WP_User($user_id);
							if (get_role('civi_user_candidate')) {
								$u->remove_role('civi_user_candidate');
							}
							$u->add_role('civi_user_employer');
						}

					$admin_email = get_option('admin_email');
					$args = array(
						'your_name'             => $user_login,
						'user_login_register'   => $email,
						'user_email'            => $email,
						'user_email_register'   => $email,
						'user_pass_register'    => $password,
						'user_login'            => $email,     // Backward compatibility
						'user_password'         => $password,  // Backward compatibility
						'first_name'            => $firstname,
						'last_name'             => $lastname
					);

					if ($enable_status === '1') {
						$msg = 'awaiting_admin_approval';
						$msg_text = esc_html__('Registration successful. Awaiting admin approval.', 'civi');

							$send_emails = apply_filters('before_civi_send_email_register_user', true, $email, $args);
							if ($send_emails) {
								Civi_Helper::civi_send_email($email, 'mail_register_user', $args);
							}

							$send_admin_emails = apply_filters('before_civi_send_email_register_admin', true, $admin_email, $args);
							if ($send_admin_emails) {
								Civi_Helper::civi_send_email($admin_email, 'admin_mail_register_user', $args);
							}

							echo json_encode(array(
								'success'        => true,
								'messages'       => $msg,
								'messages_text'  => $msg_text,
								'class'          => 'text-success',
								'url_redirect'   => ''
							));
						} else {
							$creds                  = array();
							$creds['user_login']    = $user_login;
							$creds['user_email']    = $email;
							$creds['user_password'] = $password;
							$creds['remember']      = true;
							$user                   = wp_signon($creds, false);
							$msg                    = esc_html__('Register success', 'civi');

							$send_emails = apply_filters('before_civi_send_email_register_user', true, $email, $args);
							if ($send_emails) {
								Civi_Helper::civi_send_email($email, 'mail_register_user', $args);
							}

							$send_admin_emails = apply_filters('before_civi_send_email_register_admin', true, $admin_email, $args);
							if ($send_admin_emails) {
								Civi_Helper::civi_send_email($admin_email, 'admin_mail_register_user', $args);
							}

							$users = get_user_by('login', $user_login);
							if (in_array('civi_user_candidate', (array) $users->roles) && $enable_redirect_after_login && $redirect_for_candidate != '') {
								$url_redirect = get_page_link($redirect_for_candidate);
							} else if (in_array('civi_user_employer', (array) $users->roles) && $enable_redirect_after_login && $redirect_for_employer != '') {
								$url_redirect = get_page_link($redirect_for_employer);
							} else if (in_array('administrator', (array) $users->roles) && $enable_redirect_after_login && $redirect_for_admin != '') {
								$url_redirect = get_page_link($redirect_for_admin);
							}
							$url_redirect = add_query_arg('login', 'success', $url_redirect);

							echo json_encode(array(
								'success'      => true,
								'messages'     => $msg,
								'class'        => 'text-success',
								'url_redirect' => $url_redirect
							));
						}
					} else {
						$msg = 'username_email_existing';
						echo json_encode(array('success' => false, 'messages' => $msg, 'class' => 'text-error'));
					}
				}
			}

			wp_die();
		}

		//////////////////////////////////////////////////////////////////
		// Verify User
		//////////////////////////////////////////////////////////////////
		function verify_code()
		{
			// Security check with nonce
			check_ajax_referer('verify_code_nonce', 'security');
			$verify_code       = sanitize_text_field($_POST['verify_code']);
			$account_type      = sanitize_text_field($_POST['account_type']);
			$firstname         = sanitize_text_field($_POST['firstname']);
			$lastname          = sanitize_text_field($_POST['lastname']);
			$phone             = sanitize_text_field($_POST['phone']);
			$phone_code        = sanitize_text_field($_POST['phone_code']);
			$companyname       = sanitize_text_field($_POST['companyname']);
			$email             = sanitize_email($_POST['email']);
			$password          = $_POST['password'];
			$user_login        = $companyname;
			$url_redirect      = '';

			// Get options
			$enable_redirect_after_login = Civi_Helper::civi_get_option('enable_redirect_after_login');
			$enable_verify_user          = Civi_Helper::civi_get_option('enable_verify_user');
			$enable_status               = Civi_Helper::civi_get_option('enable_status_user');
			$redirect_for_admin          = Civi_Helper::civi_get_option('redirect_for_admin');
			$redirect_for_employer       = Civi_Helper::civi_get_option('redirect_for_employer');
			$redirect_for_candidate      = Civi_Helper::civi_get_option('redirect_for_candidate');
			$type_name_candidate         = Civi_Helper::civi_get_option('type_name_candidate');

			// Verify code
			if ($enable_verify_user) {
				if (intval($verify_code) !== intval(get_option($email))) {
					$msg = esc_html__('The code mail is incorrect or has expired.', 'civi');
					echo json_encode(array(
						'success'      => false,
						'messages'     => $msg,
						'class'        => 'text-error',
						'url_redirect' => $url_redirect
					));
					wp_die();
				}
			}

			$userdata = array(
				'user_login'   => $user_login,
				'first_name'   => $firstname,
				'last_name'    => $lastname,
				'user_email'   => $email,
				'user_pass'    => $password,
				'account_type' => $account_type,
				'display_name' => ($type_name_candidate === 'fl-name') ? $firstname . ' ' . $lastname : $companyname
			);

			// Store account_type, phone, and phone_code in global variables so user_register hook can access them
			global $civi_registering_account_type, $civi_registering_phone, $civi_registering_phone_code;
			$civi_registering_account_type = $account_type;
			$civi_registering_phone = $phone;
			$civi_registering_phone_code = $phone_code;

			// Create user
			$user_id = wp_insert_user($userdata);

			if (is_wp_error($user_id) || $user_id === 0) {
				$user_login = substr($email, 0, strpos($email, '@'));
				$userdata['user_login'] = $user_login;
				// Global variable still set, so second attempt will also work correctly
				$user_id = wp_insert_user($userdata);
			}

			// Clean up global variables after user creation
			$civi_registering_account_type = null;
			$civi_registering_phone = null;
			$civi_registering_phone_code = null;

			// Update user meta only if user creation was successful
			if (!is_wp_error($user_id) && $user_id > 0) {
				update_user_meta($user_id, CIVI_METABOX_PREFIX . 'author_mobile_number', $phone);
				update_user_meta($user_id, CIVI_METABOX_PREFIX . 'phone_code', $phone_code);

				// Change role to employer if needed (only for successful user creation)
				if ($account_type === 'civi_user_employer') {
					$u = new WP_User($user_id);
					if (get_role('civi_user_candidate')) {
						$u->remove_role('civi_user_candidate');
					}
					$u->add_role('civi_user_employer');
				}
			}

			$admin_email = get_option('admin_email');
			$args = array(
				'your_name'             => $user_login,
				'user_login_register'   => $email,
				'user_email'            => $email,
				'user_email_register'   => $email,
				'user_pass_register'    => $password,
				'first_name'            => $firstname,
				'last_name'             => $lastname
			);

			if ($enable_status === '1') {
				Civi_Helper::civi_send_email($email, 'mail_register_user', $args);
				Civi_Helper::civi_send_email($admin_email, 'admin_mail_register_user', $args);
				delete_option($email);
				echo json_encode(array(
					'success'      => true,
					'messages'     => esc_html__('Registration successful. Awaiting admin approval.', 'civi'),
					'class'        => 'text-success',
					'url_redirect' => ''
				));
			} else {
				$creds = array(
					'user_login'    => $user_login,
					'user_password' => $password,
					'remember'      => true
				);
				$user = wp_signon($creds, false);
				if (is_wp_error($user)) {
					echo json_encode(array(
						'success'      => false,
						'messages'     => $user->get_error_message(),
						'class'        => 'text-error',
						'url_redirect' => ''
					));
					wp_die();
				}
				Civi_Helper::civi_send_email($email, 'mail_register_user', $args);
				Civi_Helper::civi_send_email($admin_email, 'admin_mail_register_user', $args);
				$users = get_user_by('login', $user_login);
				if (in_array('civi_user_candidate', (array) $users->roles) && $enable_redirect_after_login && $redirect_for_candidate) {
					$url_redirect = get_page_link($redirect_for_candidate);
				} elseif (in_array('civi_user_employer', (array) $users->roles) && $enable_redirect_after_login && $redirect_for_employer) {
					$url_redirect = get_page_link($redirect_for_employer);
				} elseif (in_array('administrator', (array) $users->roles) && $enable_redirect_after_login && $redirect_for_admin) {
					$url_redirect = get_page_link($redirect_for_admin);
				}
				$url_redirect = add_query_arg('login', 'success', $url_redirect);
				delete_option($email);
				echo json_encode(array(
					'success'      => true,
					'messages'     => esc_html__('Verify success', 'civi'),
					'class'        => 'text-success',
					'url_redirect' => $url_redirect
				));
			}

			wp_die();
		}

		//////////////////////////////////////////////////////////////////
		// Ajax fb login or register
		//////////////////////////////////////////////////////////////////
		function civi_verify_resend()
		{
			// Security check with nonce
			check_ajax_referer('verify_resend_nonce', 'security');

			// Sanitize inputs
			$companyname = sanitize_text_field($_POST['companyname']);
			$email       = sanitize_email($_POST['email']);
			$resend      = sanitize_text_field($_POST['resend']);
			$user_login  = $companyname;

			$enable_verify_user = Civi_Helper::civi_get_option('enable_verify_user');

			if ($enable_verify_user && $resend == 'gmail') {
				$code = rand(100000, 999999);
				update_option($email, $code);
				$args = array(
					'code_verify_user' => $code,
				);
				Civi_Helper::civi_send_email($email, 'mail_verify_user', $args);
				echo json_encode(array(
					'success'      => true,
					'cookie_name'  => str_replace(' ', '_', $cookie_name),
					'cookie_value' => $cookie_value,
					'user_login'   => $user_login,
				));
				wp_die();
			}
		}

		//////////////////////////////////////////////////////////////////
		// Ajax reset password
		//////////////////////////////////////////////////////////////////
		function civi_verify_email()
		{
			$email = sanitize_email($_POST['email']);
			if (empty($email)) {
				echo json_encode(array(
					'success' => false,
					'class'   => 'text-error',
					'message' => esc_html__('Please enter your email address.', 'civi')
				));
				wp_die();
			}
			if (!is_email($email)) {
				echo json_encode(array(
					'success' => false,
					'class'   => 'text-error',
					'message' => esc_html__('Invalid email format.', 'civi')
				));
				wp_die();
			}
			if (!email_exists($email)) {
				echo json_encode(array(
					'success' => false,
					'class'   => 'text-error',
					'message' => esc_html__('This email address is not registered.', 'civi')
				));
				wp_die();
			}
			$code = rand(100000, 999999);
			update_option($email . 'verify_code', $code);
			$args = array(
				'code_verify_user' => $code,
			);
			Civi_Helper::civi_send_email($email, 'mail_verify_user', $args);
			echo json_encode(array(
				'success' => true,
				'message' => esc_html__('Please check your email to get the verification code.', 'civi'),
				'class'   => 'text-success'
			));
			wp_die();
		}

		function reset_password_ajax()
		{
			if ($_POST['type'] == 'elementor') {
				check_ajax_referer('civi_reset_password_ajax_nonce', 'el_civi_security_reset_password');
			} else {
				check_ajax_referer('civi_reset_password_ajax_nonce', 'civi_security_reset_password');
			}

			$allowed_html = array();
			$user_login   = wp_kses($_POST['user_login'], $allowed_html);
			$verify_code = $_POST['verify_code'];

			if (empty($user_login)) {
				echo json_encode(array(
					'success' => false,
					'class'   => 'text-warning',
					'message' => esc_html__('Enter your email address.', 'civi')
				));
				wp_die();
			}

			$code = get_option($user_login . 'verify_code');

			if ($code != $verify_code) {
				echo json_encode(array(
					'success' => false,
					'class'   => 'text-error',
					'message' => esc_html__('The code is incorrect or has expired.', 'civi')
				));
				wp_die();
			}

			if (strpos($user_login, '@')) {
				$user_data = get_user_by('email', trim($user_login));
				if (empty($user_data)) {
					echo json_encode(array(
						'success' => false,
						'class'   => 'text-error',
						'message' => esc_html__('There is no user registered with that email address.', 'civi')
					));
					wp_die();
				}
			} else {
				$login     = trim($user_login);
				$user_data = get_user_by('login', $login);

				if (!$user_data) {
					echo json_encode(array(
						'success' => false,
						'class'   => 'text-error',
						'message' => esc_html__('Invalid username', 'civi')
					));
					wp_die();
				}
			}

			$user_login = $user_data->user_login;
			$user_email = $user_data->user_email;
			$key        = get_password_reset_key($user_data);
			if (is_wp_error($key)) {
				echo json_encode(array('success' => false, 'message' => $key));
				wp_die();
			}

			$message = esc_html__('Someone has requested a password reset for the following account:', 'civi') . "\r\n\r\n";
			$message .= network_home_url('/') . "\r\n\r\n";
			$message .= sprintf(esc_html__('Username: %s', 'civi'), $user_login) . "\r\n\r\n";
			$message .= esc_html__('If this was a mistake, just ignore this email and nothing will happen.', 'civi') . "\r\n\r\n";
			$message .= esc_html__('To reset your password, visit the following address:', 'civi') . "\r\n\r\n";
			$message .= get_home_url() . '?action=rp&key=' . $key . '&login=' . rawurlencode($user_login);

			if (is_multisite()) {
				$blogname = $GLOBALS['current_site']->site_name;
			} else {
				$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
			}

			$title   = sprintf(esc_html__('[%s] Password Reset', 'civi'), $blogname);
			$title   = apply_filters('retrieve_password_title', $title, $user_login, $user_data);
			$message = apply_filters('retrieve_password_message', $message, $key, $user_login, $user_data);
			if ($message && !wp_mail($user_email, wp_specialchars_decode($title), $message)) {
				echo json_encode(array(
					'success' => false,
					'class'   => 'text-error',
					'message' => esc_html__('The email could not be sent.', 'civi') . "\r\n" . esc_html__('Possible reason: your host may have disabled the mail() function.', 'civi')
				));
				wp_die();
			} else {
				echo json_encode(array(
					'success' => true,
					'class'   => 'text-success',
					'message' => esc_html__('Please check your email for the link to reset your password.', 'civi')
				));
				wp_die();
			}
		}

		function change_password_ajax()
		{
			// Security check with nonce
			check_ajax_referer('change_password_nonce', 'security');

			$new_password = sanitize_text_field($_POST['new_password']);
			$confirm_password = sanitize_text_field($_POST['confirm_password']);
			$key = sanitize_text_field($_POST['key']);
			$login = sanitize_text_field($_POST['login']);
			$user = get_user_by('login', $login);

			// Check and sanitize input data
			if (empty($new_password) || empty($confirm_password)) {
				echo json_encode(array(
					'success' => false,
					'class'   => 'text-error',
					'message' => esc_html__('Please provide both current and new password.', 'civi')
				));
				wp_die(); // Terminate execution after sending the response
			}

			if (!$user) {
				echo json_encode(array(
					'success' => false,
					'class'   => 'text-error',
					'message' => esc_html__('Invalid username', 'civi')
				));
				wp_die(); // Terminate execution after sending the response
			}

			$check = check_password_reset_key($key, $user->user_login);

			if (is_wp_error($check)) {
				echo json_encode(array(
					'success' => false,
					'class'   => 'text-error',
					'message' => esc_html__('The password reset key is invalid or has expired.', 'civi')
				));
				wp_die(); // Terminate execution after sending the response
			}

			// Check confirm password
			if ($new_password != $confirm_password) {
				echo json_encode(array(
					'success' => false,
					'class'   => 'text-error',
					'message' => esc_html__('Confirm password does not match.', 'civi')
				));
				wp_die(); // Terminate execution after sending the response
			}

			// Change the password
			wp_set_password($new_password, $user->ID);

			// Return success response
			echo json_encode(array(
				'success' => true,
				'class'   => 'text-success',
				'message' => esc_html__('Your password has been successfully changed. Please re-login!', 'civi')
			));
			wp_die(); // Terminate execution after sending the response
		}

	/**
	 * Verify reCAPTCHA response (v2 or v3)
	 * Returns array with 'valid' boolean and optional 'debug' info
	 */
	private function verify_recaptcha($secret_key, $response, $version = 'v2')
	{
		$debug_info = array();

		if (empty($secret_key) || empty($response)) {
			$debug_info['error'] = 'Missing secret key or response';
			$debug_info['has_secret'] = !empty($secret_key);
			$debug_info['has_response'] = !empty($response);
			if (defined('WP_DEBUG') && WP_DEBUG) {
				error_log('reCAPTCHA verify: ' . $debug_info['error']);
			}
			return array('valid' => false, 'debug' => $debug_info);
		}

		// Skip reCAPTCHA verification for local development
		$current_domain = $_SERVER['HTTP_HOST'] ?? '';
		$is_local = strpos($current_domain, 'localhost') !== false ||
					strpos($current_domain, '.local') !== false ||
					strpos($current_domain, '127.0.0.1') !== false ||
					strpos($current_domain, '192.168.') !== false;

		if ($is_local) {
			// For local development, accept any non-empty response
			$debug_info['local_dev'] = true;
			return array('valid' => !empty($response), 'debug' => $debug_info);
		}

		$verify_url = "https://www.google.com/recaptcha/api/siteverify";
		$verify_data = array(
			'secret' => $secret_key,
			'response' => $response,
			'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
		);

		$verify_request = wp_remote_post($verify_url, array(
			'body' => $verify_data,
			'timeout' => 30
		));

		if (is_wp_error($verify_request)) {
			$debug_info['error'] = 'Request error: ' . $verify_request->get_error_message();
			if (defined('WP_DEBUG') && WP_DEBUG) {
				error_log('reCAPTCHA verify: ' . $debug_info['error']);
			}
			return array('valid' => false, 'debug' => $debug_info);
		}

		$response_body = wp_remote_retrieve_body($verify_request);
		$verify_result = json_decode($response_body, true);

		// Check if JSON decode was successful
		if (json_last_error() !== JSON_ERROR_NONE) {
			$debug_info['error'] = 'JSON decode error: ' . json_last_error_msg();
			$debug_info['response_body'] = substr($response_body, 0, 200); // First 200 chars
			if (defined('WP_DEBUG') && WP_DEBUG) {
				error_log('reCAPTCHA verify: ' . $debug_info['error']);
			}
			return array('valid' => false, 'debug' => $debug_info);
		}

		// Check if result is valid
		if (!is_array($verify_result) || !isset($verify_result['success'])) {
			$debug_info['error'] = 'Invalid response format';
			$debug_info['response'] = $verify_result;
			if (defined('WP_DEBUG') && WP_DEBUG) {
				error_log('reCAPTCHA verify: ' . $debug_info['error']);
			}
			return array('valid' => false, 'debug' => $debug_info);
		}

		if (!$verify_result['success']) {
			$error_codes = isset($verify_result['error-codes']) ? $verify_result['error-codes'] : array('unknown');
			$error_codes_str = implode(', ', $error_codes);
			$debug_info['error'] = 'Verification failed';
			$debug_info['error_codes'] = $error_codes_str;
			$debug_info['version'] = $version;

			// Determine specific error message based on error codes
			$error_message_key = 'recaptcha_error';
			if (in_array('invalid-input-secret', $error_codes) || in_array('missing-input-secret', $error_codes)) {
				$error_message_key = 'recaptcha_invalid_secret_key';
			} elseif (in_array('invalid-input-response', $error_codes) || in_array('missing-input-response', $error_codes)) {
				$error_message_key = 'recaptcha_invalid_response';
			} elseif (in_array('timeout-or-duplicate', $error_codes)) {
				$error_message_key = 'recaptcha_timeout';
			} elseif (in_array('bad-request', $error_codes)) {
				$error_message_key = 'recaptcha_bad_request';
			}
			$debug_info['error_message_key'] = $error_message_key;

			if (defined('WP_DEBUG') && WP_DEBUG) {
				error_log('reCAPTCHA verify: ' . $debug_info['error'] . ' - ' . $error_codes_str);
			}
			return array('valid' => false, 'debug' => $debug_info, 'error_message_key' => $error_message_key);
		}

		// For v3, check score threshold
		if ($version === 'v3') {
			$score_threshold = floatval(Civi_Helper::civi_get_option('recaptcha_v3_score_threshold', '0.5'));
			$score = isset($verify_result['score']) ? floatval($verify_result['score']) : 0;

			$debug_info['version'] = 'v3';
			$debug_info['score'] = $score;
			$debug_info['threshold'] = $score_threshold;
			$debug_info['action'] = isset($verify_result['action']) ? $verify_result['action'] : 'unknown';

			if (defined('WP_DEBUG') && WP_DEBUG) {
				error_log('reCAPTCHA v3 verify: Score = ' . $score . ', Threshold = ' . $score_threshold);
			}

			$is_valid = $score >= $score_threshold;
			if (!$is_valid) {
				$debug_info['error'] = 'Score below threshold';
				if (defined('WP_DEBUG') && WP_DEBUG) {
					error_log('reCAPTCHA v3 verify: ' . $debug_info['error']);
				}
			}

			return array('valid' => $is_valid, 'debug' => $debug_info);
		}

		// For v2, just check success
		$debug_info['version'] = 'v2';
		return array('valid' => true, 'debug' => $debug_info);
	}
	}
}
