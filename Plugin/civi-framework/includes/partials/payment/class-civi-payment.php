<?php
if (!defined('ABSPATH')) {
	exit;
}

use Razorpay\Api\Api;
use Razorpay\Api\Errors;

if (!class_exists('Civi_Payment')) {
	/**
	 * Class Civi_Payment
	 */
	class Civi_Payment
	{
		protected $civi_invoice;
		protected $civi_package;
		protected $civi_trans_log;

		/**
		 * Construct
		 */
		public function __construct()
		{
			$this->civi_package = new Civi_Package();
			$this->civi_invoice = new Civi_Invoice();
			$this->civi_trans_log = new Civi_Trans_Log();

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
					// Get invoice_id
					$order_package_id = get_post_meta($product_id, '_order_package_id', true);
					if ($order_package_id && ($order->get_status() == 'completed' || $order->get_status() == 'processing')) {
						update_post_meta($order_package_id, CIVI_METABOX_PREFIX . 'invoice_payment_status', '1');
						update_post_meta($order_package_id, CIVI_METABOX_PREFIX . 'invoice_wc_order_id', $order->get_order_number());
						$user_id = $order->get_user_id();
						// Get package ID from invoice meta (stored as invoice_item_id)
						$package_id = get_post_meta($order_package_id, CIVI_METABOX_PREFIX . 'invoice_item_id', true);
						if (!$package_id) {
							$package_id = get_post_meta($order_package_id, CIVI_METABOX_PREFIX . 'package_id', true);
						}

						// Assign package to user when payment is successful
						if ($package_id && $user_id) {
							$invoice_pdf_id = get_post_meta($order_package_id, CIVI_METABOX_PREFIX . 'invoice_pdf_id', true);
							$this->civi_package->activate_package_with_cleanup($user_id, $package_id, $invoice_pdf_id);
						}

						do_action('civi_woocommerce_payment_success', $user_id, $package_id, $order_package_id);
					} else {
						// Update order status
						update_post_meta($order_package_id, CIVI_METABOX_PREFIX . 'invoice_payment_status', '0');
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
						// Get invoice id
						$order_package_id = get_post_meta($product_id, '_order_package_id', true);
						if ($order_package_id && ($new_status == 'completed' || $new_status == 'processing')) {
							// Update order status
							update_post_meta($order_package_id, CIVI_METABOX_PREFIX . 'invoice_payment_status', '1');
							update_post_meta($order_package_id, CIVI_METABOX_PREFIX . 'invoice_wc_order_id', $order->get_order_number());

							// Assign package to user when payment is successful
							$user_id = $order->get_user_id();
							$package_id = get_post_meta($order_package_id, CIVI_METABOX_PREFIX . 'invoice_item_id', true);
							if (!$package_id) {
								$package_id = get_post_meta($order_package_id, CIVI_METABOX_PREFIX . 'package_id', true);
							}
							if ($package_id && $user_id) {
								$invoice_pdf_id = get_post_meta($order_package_id, CIVI_METABOX_PREFIX . 'invoice_pdf_id', true);
								$this->civi_package->activate_package_with_cleanup($user_id, $package_id, $invoice_pdf_id);
							}
						} else {
							// Update order status
							update_post_meta($order_package_id, CIVI_METABOX_PREFIX . 'invoice_payment_status', '0');
						}
					}
				}
			}
		}

		/**
		 * Payment package by stripe
		 * @param $package_id
		 */
		public function stripe_payment_per_package($package_id)
		{
			require_once(CIVI_PLUGIN_DIR . 'includes/partials/payment/stripe-php/init.php');
			$stripe_secret_key = civi_get_option('stripe_secret_key');
			$stripe_publishable_key = civi_get_option('stripe_publishable_key');

			$current_user = wp_get_current_user();

			$user_id = $current_user->ID;
			$user_email = get_the_author_meta('user_email', $user_id);

			$stripe = array(
				"secret_key" => $stripe_secret_key,
				"publishable_key" => $stripe_publishable_key
			);

			\MyStripe\Stripe::setApiKey($stripe['secret_key']);
			$package_price = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'package_price', true);
			$package_name = get_the_title($package_id);
			//update_user_meta($user_id, CIVI_METABOX_PREFIX . 'package_id', $package_id);

			// Validate and convert to cents with proper rounding
			$package_price = floatval($package_price);

			// Validate: handle NaN, Infinity, negative values
			if (!is_numeric($package_price) || is_infinite($package_price) || is_nan($package_price) || $package_price < 0) {
				$package_price = 0;
			}

			// Round to 2 decimal places first
			$package_price = round($package_price, 2);

			// Max value check: Stripe max is 99999999 cents (~$999,999.99)
			$max_cents = 99999999;
			$package_price_cents = (int) round($package_price * 100);
			if ($package_price_cents > $max_cents) {
				$package_price_cents = $max_cents;
			}

			$currency_code = civi_get_option('currency_type_default', 'USD');
			$package_price = $package_price_cents;
			$payment_completed_link = civi_get_permalink('payment_completed');
			$stripe_processor_link = add_query_arg(array('payment_method' => 2), $payment_completed_link);
			wp_enqueue_script('stripe-checkout');
			wp_localize_script('stripe-checkout', 'civi_stripe_vars', array(
				'civi_stripe_per_package' => array(
					'key' => $stripe_publishable_key,
					'params' => array(
						'amount' => $package_price,
						'email' => $user_email,
						'currency' => $currency_code,
						'zipCode' => true,
						'billingAddress' => true,
						'name' => esc_html__('Pay with Credit Card', 'civi-framework'),
						'description' => wp_kses_post(sprintf(__('%s Package Payment', 'civi-framework'), $package_name))
					)
				)
			));
?>
			<form class="civi-stripe-form" action="<?php echo esc_url($stripe_processor_link) ?>" method="post" id="civi_stripe_per_package">
				<button class="civi-stripe-button" style="display: none !important;"></button>
				<input type="hidden" id="package_id" name="package_id" value="<?php echo esc_attr($package_id) ?>">
				<input type="hidden" id="payment_money" name="payment_money" value="<?php echo esc_attr($package_price) ?>">
				<input type="hidden" id="stripe_invoice_id" name="invoice_id" value="">
			</form>
		<?php

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
		 * Payment per package by Paypal
		 */
		public function paypal_payment_per_package_ajax()
		{
			check_ajax_referer('civi_payment_ajax_nonce', 'civi_security_payment');
			global $current_user;
			wp_get_current_user();
			$user_id = $current_user->ID;

			$blogInfo = esc_url(home_url());

			$package_id = $_POST['package_id'];
			$coupon_amount = $_POST['coupon_amount'] ? intval($_POST['coupon_amount']) : 0;
			$package_id = intval($package_id);
			$package_price = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'package_price', true);
			$package_name = get_the_title($package_id);

			if (empty($package_price) && empty($package_id)) {
				exit();
			}

			$package_price = $package_price - $coupon_amount;

			// Tạo invoice pending để hiển thị ngay trong lịch sử
			$invoice_id = $this->civi_invoice->insert_invoice('Package', $package_id, $user_id, 0, 'Paypal', 0, '', '', true);

			$currency = civi_get_option('currency_type_default', 'USD');
			$payment_description = $package_name . ' ' . esc_html__('Membership payment on ', 'civi-framework') . $blogInfo;
			$is_paypal_live = civi_get_option('paypal_api');
			$host = 'https://api.sandbox.paypal.com';
			if ($is_paypal_live == 'live') {
				$host = 'https://api.paypal.com';
			}
			$url = $host . '/v1/oauth2/token';
			$postArgs = 'grant_type=client_credentials';
			$access_token = $this->get_paypal_access_token($url, $postArgs);
			$url = $host . '/v1/payments/payment';
			$payment_completed_link = civi_get_permalink('payment_completed');
			$return_url = add_query_arg(array('payment_method' => 1), $payment_completed_link);
			$dash_profile_link = civi_get_permalink('employer_dashboard');

			$payment = array(
				'intent' => 'sale',
				'redirect_urls' => array(
					'return_url' => $return_url,
					'cancel_url' => $dash_profile_link
				),
				'payer' => array('payment_method' => 'paypal'),
			);


			$payment['transactions'][0] = array(
				'amount' => array(
					'total' => $package_price,
					'currency' => $currency,
					'details' => array(
						'subtotal' => $package_price,
						'tax' => '0.00',
						'shipping' => '0.00'
					)
				),
				'description' => $payment_description
			);

			$payment['transactions'][0]['item_list']['items'][] = array(
				'quantity' => '1',
				'name' => esc_html__('Payment Package', 'civi-framework'),
				'price' => $package_price,
				'currency' => $currency,
				'sku' => $package_name . ' ' . esc_html__('Payment Package', 'civi-framework'),
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
			$output['package_id'] = $package_id;
			$output['invoice_id'] = $invoice_id;
			update_user_meta($user_id, CIVI_METABOX_PREFIX . 'paypal_transfer', $output);

			print $payment_approval_url;
			wp_die();
		}

		/**
		 * Tạo invoice pending cho Stripe (per package) trước khi mở checkout
		 */
		public function stripe_create_invoice_per_package_ajax()
		{
			check_ajax_referer('civi_payment_ajax_nonce', 'civi_security_payment');
			global $current_user;
			wp_get_current_user();
			$user_id = $current_user->ID;
			if (!is_user_logged_in()) {
				wp_send_json_error(array('message' => __('No Login', 'civi-framework')));
			}

			$package_id = isset($_POST['package_id']) ? absint($_POST['package_id']) : 0;
			if (!$package_id) {
				wp_send_json_error(array('message' => __('Missing package', 'civi-framework')));
			}

			$payment_method = 'Stripe';
			$invoice_id = $this->civi_invoice->insert_invoice('Package', $package_id, $user_id, 0, $payment_method, 0, '', '', true);

			wp_send_json_success(array('invoice_id' => $invoice_id));
		}

		public function civi_razor_package_addons($package_id)
		{
			$payment_completed_link = civi_get_permalink('payment_completed');
		?>

			<form name='razorpayform' id="civi_razor_paymentform" action="<?= $payment_completed_link ?>" method="POST">
				<input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
				<input type="hidden" name="razorpay_signature" id="razorpay_signature">
				<input type="hidden" name="rzp_QP_form_submit" value="1">
			</form>

<?php
		}

		public function civi_razor_payment_verify()
		{
			$payment_completed_link = civi_get_permalink('payment_completed');
			$callback_url           = add_query_arg(
				[
					'payment_method'      => 4,
					'razorpay_payment_id' => sanitize_text_field($_REQUEST['razorpay_payment_id']),
					'razorpay_order_id'   => $_REQUEST['razorpay_order_id'],
					'razorpay_signature'  => sanitize_text_field($_REQUEST['razorpay_signature']),
					'package_id' => $_REQUEST['package_id'],
					'invoice_id' => isset($_REQUEST['invoice_id']) ? absint($_REQUEST['invoice_id']) : '',
				],
				$payment_completed_link
			);

			echo $callback_url;
			wp_die();
		}

		public function civi_razor_package_create_order()
		{
			require_once(CIVI_PLUGIN_DIR . 'includes/partials/payment/razorpay-php/Razorpay.php');

			$orderID = mt_rand(0, mt_getrandmax());
			global $current_user;
			wp_get_current_user();
			$user_id = $current_user->ID;

			$payment_completed_link = civi_get_permalink('payment_completed');
			$callback_url           = add_query_arg(
				[
					'payment_method'      => 4,
				],
				$payment_completed_link
			);

			$key_id_razor  = civi_get_option('razor_key_id');
			$key_secret    = civi_get_option('razor_key_secret');
			$currency_code = civi_get_option('currency_type_default', 'USD');
			$order_id      = mt_rand(0, mt_getrandmax());

			$package_id    = $_REQUEST['package_id'];
			$coupon_amount    = $_REQUEST['coupon_amount'] ? intval($_REQUEST['coupon_amount']) : 0;

			$package_price = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'package_price', true);
			$package_name  = get_the_title($package_id);
			$api = new Api($key_id_razor, $key_secret);
			if ($coupon_amount) {
				$package_price = $package_price - $coupon_amount;
			}

			// Tạo invoice pending cho Razor
			$invoice_id = $this->civi_invoice->insert_invoice('Package', $package_id, $user_id, 0, 'Razor', 0, '', '', true);

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
					'invoice_id'   => $invoice_id,
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

		public function razor_payment_completed()
		{
			require_once(CIVI_PLUGIN_DIR . 'includes/partials/payment/razorpay-php/Razorpay.php');

			$current_user   = wp_get_current_user();
			$user_id        = $current_user->ID;
			$user_email     = $current_user->user_email;
			$payment_method = 'Razor';

			$key_id_razor  = civi_get_option('razor_key_id');
			$key_secret    = civi_get_option('razor_key_secret');
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
					$package_id = absint(wp_unslash($_REQUEST['package_id']));
					$invoice_id = isset($_REQUEST['invoice_id']) ? absint($_REQUEST['invoice_id']) : 0;
					update_user_meta($user_id, CIVI_METABOX_PREFIX . 'package_id', $package_id);
					$package_price = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'package_price', true);
					$package_free = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'package_free', true);
					if ($invoice_id) {
						update_post_meta($invoice_id, CIVI_METABOX_PREFIX . 'invoice_payment_status', 1);
						update_post_meta($invoice_id, CIVI_METABOX_PREFIX . 'trans_payment_id', $_REQUEST['razorpay_order_id']);
						update_post_meta($invoice_id, CIVI_METABOX_PREFIX . 'trans_payer_id', $user_id);
					} else {
						$invoice_id = $this->civi_invoice->insert_invoice('Package', $package_id, $user_id, 0, $payment_method, 1, $_REQUEST['razorpay_order_id'], $user_id, true);
					}
					$invoice_pdf_id = get_post_meta($invoice_id, CIVI_METABOX_PREFIX . 'invoice_pdf_id', true);
					$this->civi_package->activate_package_with_cleanup($user_id, $package_id, $invoice_pdf_id);

					if (floatval($package_price) > 0 && $package_free != 1 && $payment_method != 'Free_Package') {
						$args = array();
						civi_send_email($user_email, 'mail_activated_package', $args);
					}
					do_action('civi_razor_payment_success', $user_id, $package_id, $payment_id);
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

		/**
		 * Payment per package by wire transfer
		 */
		public function wire_transfer_per_package_ajax()
		{
			check_ajax_referer('civi_payment_ajax_nonce', 'civi_security_payment');
			global $current_user;
			$current_user = wp_get_current_user();

			if (!is_user_logged_in()) {
				exit('No Login');
			}
			$user_id = $current_user->ID;
			$user_email = $current_user->user_email;
			$admin_email = get_bloginfo('admin_email');
			$package_id = $_POST['package_id'];
			$coupon_amount = $_POST['coupon_amount'] ? intval($_POST['coupon_amount']) : 0;
			$package_id = intval($package_id);
			$total_price = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'package_price', true);
			if (!empty($coupon_amount)) {
				$total_price = $total_price - $coupon_amount;
			}
		$total_price = civi_get_format_money($total_price);
		$payment_method = 'Wire_Transfer';
		// insert invoice (package will be assigned when admin confirms payment)
		$invoice_id = $this->civi_invoice->insert_invoice('Package', $package_id, $user_id, 0, $payment_method, 0, '', '', true);

			$invoice_pdf_id = get_post_meta($invoice_id, CIVI_METABOX_PREFIX . 'invoice_pdf_id', true);
			$attachment_url = $invoice_pdf_id ? wp_get_attachment_url($invoice_pdf_id) : '';
			$user = get_userdata($user_id);

			$args = array(
				'invoice_no' => $invoice_id,
				'total_price' => $total_price,
				'pdf_file' => $attachment_url,
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
			do_action('civi_wire_transfer_payment_success', $user_id, $package_id, $invoice_id);
			$payment_completed_link = civi_get_permalink('payment_completed');

			$return_link = add_query_arg(array('payment_method' => 3, 'order_id' => $invoice_id), $payment_completed_link);
			print $return_link;
			wp_die();
		}

		/**
		 * Payment per package by Woocommerce
		 */
		public function woocommerce_payment_per_package_ajax()
		{
			check_ajax_referer('civi_payment_ajax_nonce', 'civi_security_payment');
			global $current_user, $wpdb;
			wp_get_current_user();

			$user_id            = $current_user->ID;
			$package_id         = $_POST['package_id'];
			$coupon_amount      = $_POST['coupon_amount'] ? intval($_POST['coupon_amount']) : 0;
			$package_title      = get_the_title($package_id);
			$package_price      = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'package_price', true);
			$checkout_url       = wc_get_checkout_url();
			$payment_method = 'Woocommerce';

			if (!empty($coupon_amount)) {
				$package_price = $package_price - $coupon_amount;
			}

			// insert invoice
			$invoice_id = $this->civi_invoice->insert_invoice('Package', $package_id, $user_id, 0, $payment_method, 0, '', '', true);

			$query = $wpdb->prepare(
				'SELECT ID FROM ' . $wpdb->posts . '
                WHERE post_title = %s
                AND post_type = \'product\'',
				$package_title
			);
			$wpdb->query($query);

			if ($wpdb->num_rows) {
				$product_id = $wpdb->get_var($query);
			} else {
				$objProduct         = new WC_Product();

				$objProduct->set_name($package_title);
				$objProduct->set_price($package_price);
				$objProduct->set_status("");
				$objProduct->set_catalog_visibility('hidden');
				$objProduct->set_regular_price($package_price);
				$product_id = $objProduct->save();
			}

			// Update order_id to product
			update_post_meta($product_id, '_order_package_id', $invoice_id);

			global $woocommerce;
			$woocommerce->cart->empty_cart();
			$woocommerce->cart->add_to_cart($product_id);

		$total_price = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'package_price', true);
		if (!empty($coupon_amount)) {
			$total_price = $total_price - $coupon_amount;
		}
		$total_price = civi_get_format_money($total_price);
		// Package will be assigned when WooCommerce payment is completed (see event_woocommerce_payment_complete)

		$url = add_query_arg(array(
			'package_id' => esc_attr($package_id),
		), $checkout_url);

			print $url;
			wp_die();
		}

		/**
		 * Free package
		 */
		public function free_package_ajax()
		{
			check_ajax_referer('civi_payment_ajax_nonce', 'civi_security_payment');
			global $current_user;
			$user_id = $current_user->ID;
			$current_user = wp_get_current_user();
			$used_free_package = get_user_meta($user_id, 'used_free_package', true);
			if (!is_user_logged_in()) {
				exit('No Login');
			}
			if ($used_free_package === 'yes') {
				exit('Used');
			}
			$user_id = $current_user->ID;
			$package_id = isset($_POST['package_id']) ? absint(wp_unslash($_POST['package_id'])) : 0;
			$payment_method = 'Free_Package';
			// Insert invoice
			$invoice_id = $this->civi_invoice->insert_invoice('Package', $package_id, $user_id, 0, $payment_method, 1);
			$invoice_pdf_id = get_post_meta($invoice_id, CIVI_METABOX_PREFIX . 'invoice_pdf_id', true);
			$this->civi_package->activate_package_with_cleanup($user_id, $package_id, $invoice_pdf_id);
			update_user_meta($user_id, CIVI_METABOX_PREFIX . 'free_package', 'yes');
			update_user_meta($user_id, 'used_free_package', 'yes');

			do_action('civi_free_package_success', $user_id, $package_id, $invoice_id);

			$payment_completed_link = civi_get_permalink('payment_completed');
			$return_link = add_query_arg(array('payment_method' => 3, 'free_package' => $invoice_id), $payment_completed_link);
			echo esc_url_raw($return_link);
			wp_die();
		}

		/**
		 * stripe_payment_completed
		 */
		public function stripe_payment_completed()
		{
			require_once(CIVI_PLUGIN_DIR . 'includes/partials/payment/stripe-php/init.php');
			$paid_submission_type = civi_get_option('paid_submission_type');
			$current_user = wp_get_current_user();
			$user_id = $current_user->ID;
			$user_login = $current_user->user_login;
			$user_email = $current_user->user_email;
			$admin_email = get_bloginfo('admin_email');
			$currency_code = civi_get_option('currency_type_default', 'USD');
			$payment_method = 'Stripe';
			$stripe_secret_key = civi_get_option('stripe_secret_key');
			$stripe_publishable_key = civi_get_option('stripe_publishable_key');
			$stripe = array(
				"secret_key" => $stripe_secret_key,
				"publishable_key" => $stripe_publishable_key
			);
			\MyStripe\Stripe::setApiKey($stripe['secret_key']);
			$stripeEmail = '';
			if (is_email($_POST['stripeEmail'])) {
				$stripeEmail = sanitize_email(wp_unslash($_POST['stripeEmail']));
			} else {
				wp_die('None Mail');
			}

			if (isset($_POST['jobs_id']) && !is_numeric($_POST['jobs_id'])) {
				die();
			}

			if (isset($_POST['package_id']) && !is_numeric($_POST['package_id'])) {
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
					if ($paid_submission_type == 'per_package') {
						//Payment Stripe package
						$package_id = absint(wp_unslash($_POST['package_id']));
						update_user_meta($user_id, CIVI_METABOX_PREFIX . 'package_id', $package_id);
						// $package_price = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'package_price', true);
						// if ($payment_money != $package_price * 100) {
						// 	wp_die('No joke');
						// 	return;
						// }
						$invoice_id = isset($_POST['invoice_id']) ? absint(wp_unslash($_POST['invoice_id'])) : 0;
						if ($invoice_id) {
							update_post_meta($invoice_id, CIVI_METABOX_PREFIX . 'invoice_payment_status', 1);
							update_post_meta($invoice_id, CIVI_METABOX_PREFIX . 'trans_payment_id', $paymentId);
							update_post_meta($invoice_id, CIVI_METABOX_PREFIX . 'trans_payer_id', $payerId);
						} else {
							$invoice_id = $this->civi_invoice->insert_invoice('Package', $package_id, $user_id, 0, $payment_method, 1, $paymentId, $payerId, true);
						}
						$invoice_pdf_id = get_post_meta($invoice_id, CIVI_METABOX_PREFIX . 'invoice_pdf_id', true);
						$this->civi_package->activate_package_with_cleanup($user_id, $package_id, $invoice_pdf_id);
						$attachment_url = wp_get_attachment_url($invoice_pdf_id);
						$args = array(
							'pdf_file' => $attachment_url,
						);
						//civi_send_email($user_email, 'mail_activated_package', $args);

						$package_admin_args = array(
							'user_login' => $user_login,
							'user_email' => $user_email,
							'package_id' => $package_id,
						);
						//civi_send_email($admin_email, 'admin_mail_activated_package', $package_admin_args);
						do_action('civi_stripe_payment_success', $user_id, $package_id, $user_id, $paymentId, $payerId);
					}
				} else {
					$message = esc_html__('Transaction failed', 'civi-framework');
					if ($paid_submission_type == 'per_listing') {
						//Payment Stripe listing
						$jobs_id = absint(wp_unslash($_POST['jobs_id']));

						if ($payment_for == 3) {
							$this->civi_trans_log->insert_trans_log('Upgrade_To_Featured', $jobs_id, $user_id, 3, $payment_method, 0, $paymentId, $payerId, 0, $message);
						} else {
							if ($payment_for == 2) {
								$this->civi_trans_log->insert_trans_log('Listing_With_Featured', $jobs_id, $user_id, 2, $payment_method, 0, $paymentId, $payerId, 0, $message);
							} else {
								$this->civi_trans_log->insert_trans_log('Listing', $jobs_id, $user_id, 1, $payment_method, 0, $paymentId, $payerId, 0, $message);
							}
						}
					} else if ($paid_submission_type == 'per_package') {
						//Payment Stripe package
						$package_id = absint(wp_unslash($_POST['package_id']));
						$this->civi_trans_log->insert_trans_log('Package', $package_id, $user_id, 0, $payment_method, 0, $paymentId, $payerId, 0, $message);
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
			$paid_submission_type = civi_get_option('paid_submission_type', 'no');
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
						if ($paid_submission_type == 'per_package') {
							$package_id = $transfered_data['package_id'];
							$package_price = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'package_price', true);
							$package_free = get_post_meta($package_id, CIVI_METABOX_PREFIX . 'package_free', true);

							$invoice_id = isset($transfered_data['invoice_id']) ? absint($transfered_data['invoice_id']) : 0;
							if ($invoice_id) {
								update_post_meta($invoice_id, CIVI_METABOX_PREFIX . 'invoice_payment_status', 1);
								update_post_meta($invoice_id, CIVI_METABOX_PREFIX . 'trans_payment_id', $paymentId);
								update_post_meta($invoice_id, CIVI_METABOX_PREFIX . 'trans_payer_id', $payerId);
							} else {
								$invoice_id = $this->civi_invoice->insert_invoice('Package', $package_id, $user_id, 0, $payment_method, 1, $paymentId, $payerId, true);
							}

							$invoice_pdf_id = get_post_meta($invoice_id, CIVI_METABOX_PREFIX . 'invoice_pdf_id', true);
							$this->civi_package->activate_package_with_cleanup($user_id, $package_id, $invoice_pdf_id);
							update_user_meta($user_id, CIVI_METABOX_PREFIX . 'package_id', $package_id);

							if (floatval($package_price) > 0 && $package_free != 1 && $payment_method != 'Free_Package') {
								$attachment_url = wp_get_attachment_url($invoice_pdf_id);
								$args = array(
									'pdf_file' => $attachment_url,
								);
								civi_send_email($user_email, 'mail_activated_package', $args);
							}
							do_action('civi_paypal_payment_success', $user_id, $package_id, $paymentId, $payerId);
						}
					} else {
						$message = esc_html__('Transaction failed', 'civi-framework');
						if ($paid_submission_type == 'per_listing') {
							$payment_for = $transfered_data['payment_for'];
							$jobs_id = $transfered_data['jobs_id'];
							if ($payment_for == 3) {
								$this->civi_trans_log->insert_trans_log('Upgrade_To_Featured', $jobs_id, $user_id, 3, $payment_method, 0, $paymentId, $payerId, 0, $message);
							} else {
								if ($payment_for == 2) {
									$this->civi_trans_log->insert_trans_log('Listing_With_Featured', $jobs_id, $user_id, 2, $payment_method, 0, $paymentId, $payerId, 0, $message);
								} else {
									$this->civi_trans_log->insert_trans_log('Listing', $jobs_id, $user_id, 1, $payment_method, 0, $paymentId, $payerId, 0, $message);
								}
							}
						} else if ($paid_submission_type == 'per_package') {
							$package_id = $transfered_data['package_id'];
							$this->civi_trans_log->insert_trans_log('Package', $package_id, $user_id, 0, $payment_method, 0, $paymentId, $payerId, 0, $message);
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
