<?php
if (!defined('ABSPATH')) {
	exit;
}

use Razorpay\Api\Api;
use Razorpay\Api\Errors;

if (!class_exists('Civi_Candidate_Payment')) {
	/**
	 * Class Civi_Candidate_Payment
	 */
	class Civi_Candidate_Payment
	{
		protected $civi_order;
		protected $civi_candidate_package;
		protected $civi_trans_log;

		/**
		 * Construct
		 */
		public function __construct()
		{
			$this->civi_candidate_package = new Civi_Candidate_Package();
			$this->civi_order = new Civi_Candidate_Order();
			$this->civi_trans_log = new Civi_Candidate_Trans_Log();

			add_action(
				'woocommerce_payment_complete',
				array($this, 'event_woocommerce_payment_complete')
			);

			add_action(
				'woocommerce_order_status_changed',
				array($this, 'event_woocommerce_order_status_changed'),
				10,
				3
			);
		}

		/**
		 * Event woocommerce_payment_complete
		 */
		public function event_woocommerce_payment_complete($order_id)
		{
			$order = wc_get_order($order_id);
			if ($order) {
				// Get product id
				$items = $order->get_items();
				foreach ($items as $item) {
					$product_id = $item->get_product_id();
					if ($product_id) {
						// Get candidate order id
						$order_package_id = get_post_meta($product_id, '_candidate_order_package_id', true);
						if ($order_package_id && ($order->get_status() == 'completed' || $order->get_status() == 'processing')) {
							// Update order status
							update_post_meta($order_package_id, CIVI_METABOX_PREFIX . 'candidate_order_payment_status', '1');
							update_post_meta($order_package_id, CIVI_METABOX_PREFIX . 'candidate_order_wc_order_id', $order->get_order_number());

							// Assign package to user when payment is successful
							$user_id = $order->get_user_id();
							// Get package ID from order meta (stored as candidate_order_item_id)
							$candidate_package_id = get_post_meta($order_package_id, CIVI_METABOX_PREFIX . 'candidate_order_item_id', true);
							if ($candidate_package_id && $user_id) {
								$this->civi_candidate_package->activate_package_with_cleanup($user_id, $candidate_package_id);
							}
						} else {
							// Update order status
							update_post_meta($order_package_id, CIVI_METABOX_PREFIX . 'candidate_order_payment_status', '0');
						}
					}
				}
			}
		}

		/**
		 * Event woocommerce_order_status_changed
		 */
		public function event_woocommerce_order_status_changed($order_id, $old_status, $new_status)
		{
			$order = wc_get_order($order_id);
			if ($order) {
				// Get product id
				$items = $order->get_items();
				foreach ($items as $item) {
					$product_id = $item->get_product_id();
					if ($product_id) {
						// Get candidate order id
						$order_package_id = get_post_meta($product_id, '_candidate_order_package_id', true);
						if ($order_package_id && ($new_status == 'completed' || $new_status == 'processing')) {
							// Update order status
							update_post_meta($order_package_id, CIVI_METABOX_PREFIX . 'candidate_order_payment_status', '1');
							update_post_meta($order_package_id, CIVI_METABOX_PREFIX . 'candidate_order_wc_order_id', $order->get_order_number());

							// Assign package to user when payment is successful
							$user_id = $order->get_user_id();
							$candidate_package_id = get_post_meta($order_package_id, CIVI_METABOX_PREFIX . 'candidate_order_item_id', true);
							if ($candidate_package_id && $user_id) {
								$this->civi_candidate_package->activate_package_with_cleanup($user_id, $candidate_package_id);
							}
						} else {
							// Update order status
							update_post_meta($order_package_id, CIVI_METABOX_PREFIX . 'candidate_order_payment_status', '0');
						}
					}
				}
			}
		}

		/**
		 * candidate_payment candidate_package by stripe
		 * @param $candidate_package_id
		 */
		public function candidate_stripe_payment_per_package($candidate_package_id)
		{
			require_once(CIVI_PLUGIN_DIR . 'includes/partials/candidate/stripe-php/init.php');

			$candidate_stripe_secret_key      = civi_get_option('candidate_stripe_secret_key');
			$candidate_tripe_publishable_key = civi_get_option('candidate_tripe_publishable_key');

			$current_user = wp_get_current_user();
			$user_id      = $current_user->ID;
			$user_email   = get_the_author_meta('user_email', $user_id);

			$stripe = array(
				"secret_key"      => $candidate_stripe_secret_key,
				"publishable_key" => $candidate_tripe_publishable_key
			);

			\MyStripe\Stripe::setApiKey($stripe['secret_key']);

			// Lấy giá gói & tên gói
			$candidate_package_price = get_post_meta($candidate_package_id, CIVI_METABOX_PREFIX . 'candidate_package_price', true);
			$candidate_package_name  = get_the_title($candidate_package_id);

			// Nếu giá = 0 thì bỏ qua stripe
			if (floatval($candidate_package_price) <= 0) {
				// Gán gói trực tiếp cho user (nếu cần)
				update_user_meta($user_id, CIVI_METABOX_PREFIX . 'candidate_package_id', $candidate_package_id);

				// Chuyển hướng tới trang hoàn tất
				$payment_completed_link = civi_get_permalink('candidate_payment_completed');
				wp_redirect($payment_completed_link);
				exit;
			}

			// Nếu giá > 0 thì xử lý thanh toán stripe
			// Validate and convert to cents with proper rounding
			$candidate_package_price = floatval($candidate_package_price);

			// Validate: handle NaN, Infinity, negative values
			if (!is_numeric($candidate_package_price) || is_infinite($candidate_package_price) || is_nan($candidate_package_price) || $candidate_package_price < 0) {
				$candidate_package_price = 0;
			}

			// Round to 2 decimal places first
			$candidate_package_price = round($candidate_package_price, 2);

			// Max value check: Stripe max is 99999999 cents (~$999,999.99)
			$max_cents = 99999999;
			$candidate_package_price_cents = (int) round($candidate_package_price * 100);
			if ($candidate_package_price_cents > $max_cents) {
				$candidate_package_price_cents = $max_cents;
			}

			$currency_code = civi_get_option('currency_type_default', 'USD');
			$candidate_package_price = $candidate_package_price_cents;

			$payment_completed_link = civi_get_permalink('candidate_payment_completed');
			$stripe_processor_link  = add_query_arg(array('payment_method' => 2), $payment_completed_link);

			wp_enqueue_script('stripe-checkout');
			wp_localize_script('stripe-checkout', 'civi_stripe_vars', array(
				'civi_stripe_candidate_per_package' => array(
					'key'    => $candidate_tripe_publishable_key,
					'params' => array(
						'amount'         => $candidate_package_price,
						'email'          => $user_email,
						'currency'       => $currency_code,
						'zipCode'        => true,
						'billingAddress' => true,
						'name'           => esc_html__('Pay with Credit Card', 'civi-framework'),
						'description'    => wp_kses_post(sprintf(__('%s Package Service Payment', 'civi-framework'), $candidate_package_name))
					)
				)
			));
?>
			<form class="civi-candidate-stripe-form" action="<?php echo esc_url($stripe_processor_link) ?>" method="post" id="civi_stripe_candidate_per_package">
				<button class="civi-stripe-button" style="display: none !important;"></button>
				<input type="hidden" id="candidate_package_id" name="candidate_package_id" value="<?php echo esc_attr($candidate_package_id) ?>">
				<input type="hidden" id="payment_money" name="payment_money" value="<?php echo esc_attr($candidate_package_price) ?>">
			</form>
		<?php
		}

		public function civi_razor_package_candidate_addons($package_id)
		{
			$payment_completed_link = civi_get_permalink('candidate_payment_completed');
		?>

			<form name='razorpayform' id="civi_razor_paymentform" action="<?= $payment_completed_link ?>" method="POST">
				<input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
				<input type="hidden" name="razorpay_signature" id="razorpay_signature">
				<input type="hidden" name="rzp_QP_form_submit" value="1">
			</form>

<?php
		}

		public function civi_candidate_razor_package_verify()
		{
			$payment_completed_link = civi_get_permalink('candidate_payment_completed');
			$callback_url           = add_query_arg(
				[
					'payment_method'      => 4,
					'razorpay_payment_id' => sanitize_text_field($_REQUEST['razorpay_payment_id']),
					'razorpay_order_id'   => $_REQUEST['razorpay_order_id'],
					'razorpay_signature'  => sanitize_text_field($_REQUEST['razorpay_signature']),
					'candidate_package_id' => $_REQUEST['candidate_package_id'],
				],
				$payment_completed_link
			);

			echo $callback_url;
			wp_die();
		}

		public function civi_razor_package_candidate_create_order()
		{
			require_once(CIVI_PLUGIN_DIR . 'includes/partials/candidate/razorpay-php/Razorpay.php');

			$orderID = mt_rand(0, mt_getrandmax());

			$payment_completed_link = civi_get_permalink('candidate_payment_completed');
			$callback_url           = add_query_arg(
				[
					'payment_method'      => 4,
				],
				$payment_completed_link
			);

			$key_id_razor  = civi_get_option('candidate_razor_key_id');
			$key_secret    = civi_get_option('candidate_razor_key_secret');
			$currency_code = civi_get_option('currency_type_default', 'USD');
			$order_id      = mt_rand(0, mt_getrandmax());

			$candidate_package_id    = $_REQUEST['candidate_package_id'];
			$coupon_amount           = $_REQUEST['coupon_amount'] ? intval($_REQUEST['coupon_amount']) : 0;
			$package_price = get_post_meta($candidate_package_id, CIVI_METABOX_PREFIX . 'candidate_package_price', true);
			$package_name  = get_the_title($candidate_package_id);
			$api = new Api($key_id_razor, $key_secret);
			if ($coupon_amount) {
				$package_price = $package_price - $coupon_amount;
			}
			// Calls the helper function to create order data
			$data = $this->getOrderCreationData($orderID, $package_price);
			$api->order->create($data);
			try {
				$razorpayOrder = $api->order->create($data);
			} catch (Exception $e) {
				$razorpayArgs['error'] = 'Wordpress Error : ' . $e->getMessage();
			}
			if (isset($razorpayArgs['error']) === false) {
				$razorpayArgs = [
					'key'          => $key_id_razor,
					'name'         => get_bloginfo('name'),
					// 'amount'       => $total_price,
					'currency'     => $currency_code,
					'description'  => wp_kses_post(sprintf(__('%s Package Payment', 'civi-framework'), $package_name)),
					'order_id'     => $razorpayOrder['id'],
					'notes'        => [
						'quick_payment_order_id' => $order_id,
					],
					'callback_url' => $callback_url,
				];
			}

			$jsson = json_encode($razorpayArgs);
			echo $jsson;
			wp_die();
		}

		function getOrderCreationData($orderID, $amount)
		{
			$data = array(
				'receipt'         => $orderID,
				'amount'          => (int) round($amount * 100),
				'currency'        => civi_get_option('currency_type_default', 'USD'),
				'payment_capture' => 0
			);

			return $data;
		}

		public function razor_candidate_payment_completed()
		{
			require_once(CIVI_PLUGIN_DIR . 'includes/partials/candidate/razorpay-php/Razorpay.php');

			$current_user   = wp_get_current_user();
			$user_id        = $current_user->ID;
			$user_email     = $current_user->user_email;
			$payment_method = 'Razor';

			$key_id_razor  = civi_get_option('candidate_razor_key_id');
			$key_secret    = civi_get_option('candidate_razor_key_secret');
			$api           = new Api($key_id_razor, $key_secret);

			$attributes = $this->getPostAttributes();

			if (!empty($attributes)) {
				$success = true;

				try {
					$api->utility->verifyPaymentSignature($attributes);
				} catch (Exception $e) {
					$success = false;
					$error = '<div class="alert alert-error" role="alert"><strong>' . esc_html__('Error!', 'civi-framework') . ' </strong> ' . $e->getMessage() . '</div>';
					echo wp_kses_post($error);
				}

				if ($success === true) {
					$candidate_package_id = absint(wp_unslash($_REQUEST['candidate_package_id']));
					update_user_meta($user_id, CIVI_METABOX_PREFIX . 'candidate_package_id', $candidate_package_id);

					$this->civi_candidate_package->activate_package_with_cleanup($user_id, $candidate_package_id);
					$this->civi_order->insert_candidate_order('Package', $candidate_package_id, $user_id, 0, $payment_method, 1, $_REQUEST['razorpay_order_id'], $user_id);
					$args = array();
					// Use the same email template as employer package activation
					civi_send_email($user_email, 'mail_activated_package', $args);
				} else {
					$error = '<div class="alert alert-error" role="alert">' . wp_kses_post(__('<strong>Error!</strong> Transaction failed', 'civi-framework')) . '</div>';
					echo wp_kses_post($error);
				}
			}
		}

		protected function getPostAttributes()
		{
			if (isset($_REQUEST['razorpay_payment_id'])) {
				return array(
					'razorpay_payment_id' => sanitize_text_field($_REQUEST['razorpay_payment_id']),
					'razorpay_order_id'   => $_REQUEST['razorpay_order_id'],
					'razorpay_signature'  => sanitize_text_field($_REQUEST['razorpay_signature'])
				);
			}

			return array();
		}

		private function get_paypal_access_token($url, $postArgs)
		{
			$client_id = civi_get_option('paypal_client_id');
			$secret_key = civi_get_option('paypal_client_secret_key');

			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_USERPWD, $client_id . ":" . $secret_key);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $postArgs);
			$response = curl_exec($curl);
			if (empty($response)) {
				die(curl_error($curl));
				curl_close($curl);
			} else {
				$info = curl_getinfo($curl);
				curl_close($curl);
				if ($info['http_code'] != 200 && $info['http_code'] != 201) {
					echo "Received error: " . $info['http_code'] . "\n";
					echo "Raw response:" . $response . "\n";
					die();
				}
			}
			$response = json_decode($response);
			return $response->access_token;
		}

		private function execute_paypal_request($url, $jsonData, $access_token)
		{
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array(
				'Authorization: Bearer ' . $access_token,
				'Accept: application/json',
				'Content-Type: application/json'
			));

			curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
			$response = curl_exec($curl);
			if (empty($response)) {
				die(curl_error($curl));
				curl_close($curl);
			} else {
				$info = curl_getinfo($curl);
				curl_close($curl);
				if ($info['http_code'] != 200 && $info['http_code'] != 201) {
					echo "Received error: " . $info['http_code'] . "\n";
					echo "Raw response:" . $response . "\n";
					die();
				}
			}
			$jsonResponse = json_decode($response, TRUE);
			return $jsonResponse;
		}

		/**
		 * candidate_payment per package by Paypal
		 */
		public function candidate_paypal_payment_per_package_ajax()
		{
			check_ajax_referer('civi_candidate_payment_ajax_nonce', 'civi_candidate_security_payment');
			global $current_user;
			wp_get_current_user();
			$user_id = $current_user->ID;

			$blogInfo = esc_url(home_url());

			$candidate_package_id = $_POST['candidate_package_id'];
			$coupon_amount = $_POST['coupon_amount'] ? intval($_POST['coupon_amount']) : 0;
			$candidate_package_id = intval($candidate_package_id);
			$candidate_package_price = get_post_meta($candidate_package_id, CIVI_METABOX_PREFIX . 'candidate_package_price', true);
			$candidate_package_name = get_the_title($candidate_package_id);

			if (empty($candidate_package_price) && empty($candidate_package_id)) {
				exit();
			}

			if (!empty($coupon_amount)) {
				$candidate_package_price = $candidate_package_price - $coupon_amount;
			}

			$currency = civi_get_option('currency_type_default');
			$payment_description = $candidate_package_name . ' ' . esc_html__('Membership payment on ', 'civi-framework') . $blogInfo;
			$is_paypal_live = civi_get_option('paypal_api');
			$host = 'https://api.sandbox.paypal.com';
			if ($is_paypal_live == 'live') {
				$host = 'https://api.paypal.com';
			}
			$url = $host . '/v1/oauth2/token';
			$postArgs = 'grant_type=client_credentials';
			$access_token = $this->get_paypal_access_token($url, $postArgs);
			$url = $host . '/v1/payments/payment';
			$payment_completed_link = civi_get_permalink('candidate_payment_completed');
			$return_url = add_query_arg(array('payment_method' => 1), $payment_completed_link);
			$dash_profile_link = civi_get_permalink('candidate_dashboard');

			$payment = array(
				'intent' => 'sale',
				"redirect_urls" => array(
					"return_url" => $return_url,
					"cancel_url" => $dash_profile_link
				),
				'payer' => array("payment_method" => "paypal"),
			);


			$payment['transactions'][0] = array(
				'amount' => array(
					'total' => $candidate_package_price,
					'currency' => $currency,
					'details' => array(
						'subtotal' => $candidate_package_price,
						'tax' => '0.00',
						'shipping' => '0.00'
					)
				),
				'description' => $payment_description
			);

			$payment['transactions'][0]['item_list']['items'][] = array(
				'quantity' => '1',
				'name' => esc_html__('candidate_payment Package', 'civi-framework'),
				'price' => $candidate_package_price,
				'currency' => $currency,
				'sku' => $candidate_package_name . ' ' . esc_html__('candidate_payment Package', 'civi-framework'),
			);

			$jsonEncode = json_encode($payment);
			$json_response = $this->execute_paypal_request($url, $jsonEncode, $access_token);
			$payment_approval_url = $payment_execute_url = '';
			foreach ($json_response['links'] as $link) {
				if ($link['rel'] == 'execute') {
					$payment_execute_url = $link['href'];
				} else if ($link['rel'] == 'approval_url') {
					$payment_approval_url = $link['href'];
				}
			}
			$output['payment_execute_url'] = $payment_execute_url;
			$output['access_token'] = $access_token;
			$output['candidate_package_id'] = $candidate_package_id;
			update_user_meta($user_id, CIVI_METABOX_PREFIX . 'paypal_transfer', $output);

			print $payment_approval_url;
			wp_die();
		}

		/**
		 * candidate_payment per package by wire transfer
		 */
		public function candidate_wire_transfer_per_package_ajax()
		{
			check_ajax_referer('civi_candidate_payment_ajax_nonce', 'civi_candidate_security_payment');
			global $current_user;
			$current_user = wp_get_current_user();

			if (!is_user_logged_in()) {
				exit('No Login');
			}
			$user_id = $current_user->ID;
			$user_email = $current_user->user_email;
			$admin_email = get_bloginfo('admin_email');
			$candidate_package_id = $_POST['candidate_package_id'];
			$coupon_amount = $_POST['coupon_amount'] ? intval($_POST['coupon_amount']) : 0;
			$candidate_package_id = intval($candidate_package_id);
			$total_price = get_post_meta($candidate_package_id, CIVI_METABOX_PREFIX . 'candidate_package_price', true);
			if (!empty($coupon_amount)) {
				$total_price = $total_price - $coupon_amount;
			}
			$total_price = civi_get_format_money($total_price);
			$payment_method = 'Wire_Transfer';
			// insert order (package will be assigned when admin confirms payment)
			$order_id = $this->civi_order->insert_candidate_order('Package', $candidate_package_id, $user_id, 0, $payment_method, 0);
			$user = get_userdata($user_id);

			$args = array(
				'invoice_no' => $order_id,
				'total_price' => $total_price,
				'user_login' => $user->user_login,
				'user_display_name' => $user->display_name,
				'user_email' => $user->user_email,
				'first_name' => $user->first_name,
				'last_name' => $user->last_name,
			);
			/*
             * Send email
             * */
			civi_send_email($user_email, 'mail_new_wire_transfer', $args);
			civi_send_email($admin_email, 'admin_mail_new_wire_transfer', $args);
			$payment_completed_link = civi_get_permalink('candidate_payment_completed');

			$return_link = add_query_arg(array('payment_method' => 3, 'order_id' => $order_id), $payment_completed_link);
			print $return_link;
			wp_die();
		}

		/**
		 * candidate_payment per package by Woocommerce
		 */
		public function candidate_woocommerce_payment_per_package_ajax()
		{
			check_ajax_referer('civi_candidate_payment_ajax_nonce', 'civi_candidate_security_payment');
			global $current_user, $wpdb;
			wp_get_current_user();

			$user_id            = $current_user->ID;
			$candidate_package_id         = $_POST['candidate_package_id'];
			$coupon_amount         = $_POST['coupon_amount'] ? intval($_POST['coupon_amount']) : 0;
			$candidate_package_title      = get_the_title($candidate_package_id);
			$candidate_package_price      = get_post_meta($candidate_package_id, CIVI_METABOX_PREFIX . 'candidate_package_price', true);
			$checkout_url       = wc_get_checkout_url();
			$payment_method = 'Woocommerce';

			if (!empty($coupon_amount)) {
				$candidate_package_price = $candidate_package_price - $coupon_amount;
			}

			// insert order
			$order_id = $this->civi_order->insert_candidate_order('Package', $candidate_package_id, $user_id, 0, $payment_method, 0);

			$query = $wpdb->prepare(
				'SELECT ID FROM ' . $wpdb->posts . '
                WHERE post_title = %s
                AND post_type = \'product\'',
				$candidate_package_title
			);
			$wpdb->query($query);

			if ($wpdb->num_rows) {
				$product_id = $wpdb->get_var($query);
				if ($product_id) {
					$objProduct = wc_get_product($product_id);
					if ($objProduct) {
						$objProduct->set_name($candidate_package_title);
						$objProduct->set_price($candidate_package_price);
						$objProduct->set_regular_price($candidate_package_price);
						$objProduct->set_catalog_visibility('hidden');
						$objProduct->save();
					}
				}
			} else {
				$objProduct         = new WC_Product();

				$objProduct->set_name($candidate_package_title);
				$objProduct->set_price($candidate_package_price);
				$objProduct->set_status("");
				$objProduct->set_catalog_visibility('hidden');
				$objProduct->set_regular_price($candidate_package_price);
				$product_id = $objProduct->save();
			}

			// Update order_id to product
			update_post_meta($product_id, '_candidate_order_package_id', $order_id);

			global $woocommerce;
			$woocommerce->cart->empty_cart();
			$woocommerce->cart->add_to_cart($product_id);

			$total_price = get_post_meta($candidate_package_id, CIVI_METABOX_PREFIX . 'candidate_package_price', true);
			if (!empty($coupon_amount)) {
				$total_price = $total_price - $coupon_amount;
			}
			$total_price = civi_get_format_money($total_price);
			// Package will be assigned when WooCommerce payment is completed (see event_woocommerce_payment_complete)

			$url = add_query_arg(array(
				'candidate_package_id' => esc_attr($candidate_package_id),
			), $checkout_url);

			print $url;
			wp_die();
		}

		/**
		 * Free candidate_package
		 */
		public function candidate_free_package_ajax()
		{
			check_ajax_referer('civi_candidate_payment_ajax_nonce', 'civi_candidate_security_payment');
			global $current_user;
			$current_user = wp_get_current_user();
			if (!is_user_logged_in()) {
				exit('No Login');
			}
			$user_id = $current_user->ID;
			$candidate_package_id = isset($_POST['candidate_package_id']) ? absint(wp_unslash($_POST['candidate_package_id'])) : 0;
			$payment_method = 'Free_Package';
			// insert order
			$order_id = $this->civi_order->insert_candidate_order('Package', $candidate_package_id, $user_id, 0, $payment_method, 1);

			$this->civi_candidate_package->activate_package_with_cleanup($user_id, $candidate_package_id);
			update_user_meta($user_id, CIVI_METABOX_PREFIX . 'free_candidate_package', 'yes');
			$payment_completed_link = civi_get_permalink('candidate_payment_completed');
			$return_link = add_query_arg(array('payment_method' => 3, 'free_candidate_package' => $order_id), $payment_completed_link);
			echo esc_url_raw($return_link);
			wp_die();
		}

		/**
		 * candidate_stripe_payment_completed
		 */
		public function stripe_payment_completed()
		{
			require_once(CIVI_PLUGIN_DIR . 'includes/partials/candidate/stripe-php/init.php');
			$candidate_paid_submission_type = civi_get_option('candidate_paid_submission_type');
			$current_user = wp_get_current_user();
			$user_id = $current_user->ID;
			$user_email = $current_user->user_email;
			$admin_email = get_bloginfo('admin_email');
			$currency_code = civi_get_option('currency_type_default', 'USD');
			$payment_method = 'Stripe';
			$candidate_stripe_secret_key = civi_get_option('candidate_stripe_secret_key');
			$candidate_tripe_publishable_key = civi_get_option('candidate_tripe_publishable_key');
			$stripe = array(
				"secret_key" => $candidate_stripe_secret_key,
				"publishable_key" => $candidate_tripe_publishable_key
			);
			\MyStripe\Stripe::setApiKey($stripe['secret_key']);
			$stripeEmail = '';
			if (is_email($_POST['stripeEmail'])) {
				$stripeEmail = sanitize_email(wp_unslash($_POST['stripeEmail']));
			} else {
				wp_die('None Mail');
			}

			if (isset($_POST['candidate_id']) && !is_numeric($_POST['candidate_id'])) {
				die();
			}

			if (isset($_POST['candidate_package_id']) && !is_numeric($_POST['candidate_package_id'])) {
				die();
			}

			if (isset($_POST['payment_money']) && !is_numeric($_POST['payment_money'])) {
				die();
			}

			if (isset($_POST['payment_for']) && !is_numeric($_POST['payment_for'])) {
				die();
			}
			$payment_for = 0;
			$paymentId = 0;
			if (isset($_POST['payment_for'])) {
				$payment_for = absint(wp_unslash($_POST['payment_for']));
			}
			try {
				$token = isset($_POST['stripeToken']) ? civi_clean(wp_unslash($_POST['stripeToken'])) : '';
				$payment_money = isset($_POST['payment_money']) ? absint(wp_unslash($_POST['payment_money'])) :  0;
				$customer = \MyStripe\Customer::create(array(
					"email" => $stripeEmail,
					"source" => $token
				));
				$charge = \MyStripe\Charge::create(array(
					"amount" => $payment_money,
					'customer' => $customer->id,
					"currency" => $currency_code,
				));
				$payerId = $customer->id;
				if (isset($charge->id) && (!empty($charge->id))) {
					$paymentId = $charge->id;
				}
				$payment_Status = '';
				if (isset($charge->status) && (!empty($charge->status))) {
					$payment_Status = $charge->status;
				}

				if ($payment_Status == "succeeded") {
					if ($candidate_paid_submission_type == 'candidate_per_package') {
						//candidate_payment Stripe candidate_package
						$candidate_package_id = absint(wp_unslash($_POST['candidate_package_id']));
						update_user_meta($user_id, CIVI_METABOX_PREFIX . 'candidate_package_id', $candidate_package_id);
						$candidate_package_price = get_post_meta($candidate_package_id, CIVI_METABOX_PREFIX . 'candidate_package_price', true);
						if ($payment_money != $candidate_package_price * 100) {
							wp_die('No joke');
							return;
						}
						$this->civi_candidate_package->activate_package_with_cleanup($user_id, $candidate_package_id);
						$this->civi_order->insert_candidate_order('Package', $candidate_package_id, $user_id, 0, $payment_method, 1, $paymentId, $payerId);
						$args = array();
						// Use the same email template as employer package activation
						civi_send_email($user_email, 'mail_activated_package', $args);
					}
				} else {
					$message = esc_html__('Transaction failed', 'civi-framework');
					if ($candidate_paid_submission_type == 'per_listing') {
						//candidate_payment Stripe listing
						$candidate_id = absint(wp_unslash($_POST['candidate_id']));

						if ($payment_for == 3) {
							$this->civi_trans_log->insert_trans_log('Upgrade_To_Featured', $candidate_id, $user_id, 3, $payment_method, 0, $paymentId, $payerId, 0, $message);
						} else {
							if ($payment_for == 2) {
								$this->civi_trans_log->insert_trans_log('Listing_With_Featured', $candidate_id, $user_id, 2, $payment_method, 0, $paymentId, $payerId, 0, $message);
							} else {
								$this->civi_trans_log->insert_trans_log('Listing', $candidate_id, $user_id, 1, $payment_method, 0, $paymentId, $payerId, 0, $message);
							}
						}
					} else if ($candidate_paid_submission_type == 'candidate_per_package') {
						//candidate_payment Stripe candidate_package
						$candidate_package_id = absint(wp_unslash($_POST['candidate_package_id']));
						$this->civi_trans_log->insert_trans_log('Package', $candidate_package_id, $user_id, 0, $payment_method, 0, $paymentId, $payerId, 0, $message);
					}

					$error = '<div class="alert alert-error" role="alert">' . wp_kses_post(__('<strong>Error!</strong> Transaction failed', 'civi-framework')) . '</div>';
					echo wp_kses_post($error);
				}
			} catch (Exception $e) {
				$error = '<div class="alert alert-error" role="alert"><strong>' . esc_html__('Error!', 'civi-framework') . ' </strong> ' . $e->getMessage() . '</div>';
				echo wp_kses_post($error);
			}
		}

		/**
		 * paypal_payment_completed
		 */
		public function paypal_payment_completed()
		{
			global $current_user;
			wp_get_current_user();
			$user_id = $current_user->ID;
			$user_email = $current_user->user_email;
			$admin_email = get_bloginfo('admin_email');
			$allowed_html = array();
			$payment_method = 'Paypal';
			$candidate_paid_submission_type = civi_get_option('candidate_paid_submission_type', 'no');
			try {
				if (isset($_GET['token']) && isset($_GET['PayerID'])) {
					$payerId = wp_kses(civi_clean(wp_unslash($_GET['PayerID'])), $allowed_html);
					$paymentId = wp_kses(civi_clean(wp_unslash($_GET['paymentId'])), $allowed_html);
					$transfered_data = get_user_meta($user_id, CIVI_METABOX_PREFIX . 'paypal_transfer', true);
					if (empty($transfered_data)) {
						return;
					}
					$payment_execute_url = $transfered_data['payment_execute_url'];
					$token = $transfered_data['access_token'];

					$payment_execute = array(
						'payer_id' => $payerId
					);
					$json = json_encode($payment_execute);
					$json_response = $this->execute_paypal_request($payment_execute_url, $json, $token);
					delete_user_meta($user_id, CIVI_METABOX_PREFIX . 'paypal_transfer');
					if ($json_response['state'] == 'approved') {
						if ($candidate_paid_submission_type == 'candidate_per_package') {
							$candidate_package_id = $transfered_data['candidate_package_id'];
							$this->civi_candidate_package->activate_package_with_cleanup($user_id, $candidate_package_id);
							$this->civi_order->insert_candidate_order('Package', $candidate_package_id, $user_id, 0, $payment_method, 1, $paymentId, $payerId);

							// fix update update payment
							update_user_meta($user_id, CIVI_METABOX_PREFIX . 'candidate_package_id', $candidate_package_id);

							$args = array();
							// Use the same email template as employer package activation
							civi_send_email($user_email, 'mail_activated_package', $args);
						}
					} else {
						$message = esc_html__('Transaction failed', 'civi-framework');
						if ($candidate_paid_submission_type == 'per_listing') {
							$payment_for = $transfered_data['payment_for'];
							$candidate_id = $transfered_data['candidate_id'];
							if ($payment_for == 3) {
								$this->civi_trans_log->insert_trans_log('Upgrade_To_Featured', $candidate_id, $user_id, 3, $payment_method, 0, $paymentId, $payerId, 0, $message);
							} else {
								if ($payment_for == 2) {
									$this->civi_trans_log->insert_trans_log('Listing_With_Featured', $candidate_id, $user_id, 2, $payment_method, 0, $paymentId, $payerId, 0, $message);
								} else {
									$this->civi_trans_log->insert_trans_log('Listing', $candidate_id, $user_id, 1, $payment_method, 0, $paymentId, $payerId, 0, $message);
								}
							}
						} else if ($candidate_paid_submission_type == 'candidate_per_package') {
							$candidate_package_id = $transfered_data['candidate_package_id'];
							$this->civi_trans_log->insert_trans_log('Package', $candidate_package_id, $user_id, 0, $payment_method, 0, $paymentId, $payerId, 0, $message);
						}
						$error = '<div class="alert alert-error" role="alert">' . sprintf(__('<strong>Error!</strong> Transaction failed', 'civi-framework')) . '</div>';
						print $error;
					}
				}
			} catch (Exception $e) {
				$error = '<div class="alert alert-error" role="alert"><strong>Error!</strong> ' . $e->getMessage() . '</div>';
				print $error;
			}
		}
	}
}
