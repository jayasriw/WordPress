<?php
if (!defined('ABSPATH')) {
	exit;
}
if (!class_exists('JobPortal_Service')) {
	/**
	 * Class JobPortal_Service
	 */
	class JobPortal_Service
	{
		/**
		 * Submit review
		 */
		public function submit_review_ajax()
		{
			check_ajax_referer('jobportal_submit_review_ajax_nonce', 'jobportal_security_submit_review');

			if (! is_user_logged_in()) {
				wp_send_json_error(['message' => __('You must be logged in to submit a review.', 'jobportal-framework')], 401);
			}

			global $wpdb;
			$current_user = wp_get_current_user();
			$user_id      = (int) $current_user->ID;
			$user         = get_user_by('id', $user_id);

			$service_id = isset($_POST['service_id']) ? (int) wp_unslash($_POST['service_id']) : 0;
			if ($service_id <= 0) {
				wp_send_json_error(['message' => __('Invalid service ID.', 'jobportal-framework')], 422);
			}

			// clamp rating values 1–5
			$clamp = static function ($v) {
				return max(1, min(5, (int) $v));
			};
			$rating_salary_value  = $clamp($_POST['rating_salary']  ?? 0);
			$rating_service_value = $clamp($_POST['rating_service'] ?? 0);
			$rating_skill_value   = $clamp($_POST['rating_skill']   ?? 0);
			$rating_work_value    = $clamp($_POST['rating_work']    ?? 0);

			// secure query: check existing review
			$sql = $wpdb->prepare(
				"SELECT c.comment_ID, m.meta_value
         FROM {$wpdb->comments} c
         INNER JOIN {$wpdb->commentmeta} m ON m.comment_id = c.comment_ID
         WHERE c.comment_post_ID = %d
           AND c.user_id = %d
           AND m.meta_key = %s
         ORDER BY c.comment_ID DESC
         LIMIT 1",
				$service_id,
				$user_id,
				'service_rating'
			);
			$my_review = $wpdb->get_row($sql);

			$comment_approved = get_option('comment_moderation') ? 0 : 1;
			$message_raw      = isset($_POST['message']) ? wp_unslash($_POST['message']) : '';
			$comment_content  = wp_kses_post($message_raw);

			$service_rating = ($rating_salary_value + $rating_service_value + $rating_skill_value + $rating_work_value) / 4;
			$service_rating = number_format((float) $service_rating, 2, '.', '');

			if (null === $my_review) {
				// Insert new review
				$data = [
					'comment_post_ID'      => $service_id,
					'comment_content'      => $comment_content,
					'comment_date'         => current_time('mysql'),
					'comment_approved'     => $comment_approved,
					'comment_author'       => $user->user_login,
					'comment_author_email' => $user->user_email,
					'comment_author_url'   => $user->user_url,
					'user_id'              => $user_id,
				];

				$comment_id = wp_insert_comment($data);
				if (!$comment_id || is_wp_error($comment_id)) {
					wp_send_json_error(['message' => __('Could not save review.', 'jobportal-framework')], 500);
				}

				add_comment_meta($comment_id, 'service_salary_rating',  $rating_salary_value);
				add_comment_meta($comment_id, 'service_service_rating', $rating_service_value);
				add_comment_meta($comment_id, 'service_skill_rating',   $rating_skill_value);
				add_comment_meta($comment_id, 'service_work_rating',    $rating_work_value);
				add_comment_meta($comment_id, 'service_rating',         $service_rating);

				if ($comment_approved) {
					apply_filters('jobportal_service_rating_meta', $service_id, $service_rating);
				}

				$comment_thumb = $this->handle_review_uploads($_FILES['files'] ?? []);
				if ($comment_thumb) {
					add_comment_meta($comment_id, 'comment_thumb', $comment_thumb);
				}

				jobportal_get_data_ajax_notification($service_id, 'add-review-service');
			} else {
				// Update existing review
				$update = [
					'comment_ID'       => (int) $my_review->comment_ID,
					'comment_post_ID'  => $service_id,
					'comment_content'  => $comment_content,
					'comment_date'     => current_time('mysql'),
					'comment_approved' => $comment_approved,
				];

				$ok = wp_update_comment($update);
				if (!$ok) {
					wp_send_json_error(['message' => __('Could not update review.', 'jobportal-framework')], 500);
				}

				update_comment_meta($my_review->comment_ID, 'service_salary_rating',  $rating_salary_value);
				update_comment_meta($my_review->comment_ID, 'service_service_rating', $rating_service_value);
				update_comment_meta($my_review->comment_ID, 'service_skill_rating',   $rating_skill_value);
				update_comment_meta($my_review->comment_ID, 'service_work_rating',    $rating_work_value);
				update_comment_meta($my_review->comment_ID, 'service_rating',         $service_rating, $my_review->meta_value);

				if ($comment_approved) {
					apply_filters('jobportal_service_rating_meta', $service_id, $service_rating, false, $my_review->meta_value);
				}

				$comment_thumb = $this->handle_review_uploads($_FILES['files'] ?? []);
				if ($comment_thumb) {
					update_comment_meta($my_review->comment_ID, 'comment_thumb', $comment_thumb);
				}
			}

			// secure query: get all reviews
			$comments_query = $wpdb->prepare(
				"SELECT c.comment_ID, c.comment_approved, m.meta_value
         FROM {$wpdb->comments} c
         INNER JOIN {$wpdb->commentmeta} m ON m.comment_id = c.comment_ID
         WHERE c.comment_post_ID = %d
           AND m.meta_key = %s
           AND (c.comment_approved = 1 OR c.user_id = %d)",
				$service_id,
				'service_rating',
				$user_id
			);
			$get_comments = $wpdb->get_results($comments_query);

			$rating_number = 0;
			$total_reviews = 0;
			$total_stars   = 0;

			if (!empty($get_comments)) {
				foreach ($get_comments as $comment) {
					if ($comment->comment_approved == 1) {
						if (!empty($comment->meta_value) && $comment->meta_value != 0.00) {
							$total_reviews++;
						}
						if ($comment->meta_value > 0) {
							$total_stars += $comment->meta_value;
						}
					}
				}
				if ($total_reviews > 0) {
					$rating_number = number_format($total_stars / $total_reviews, 1);
				}
			}

			update_post_meta($service_id, 'total_point_review', (int) $rating_number);

			wp_send_json_success([
				'message'        => __('Review submitted successfully.', 'jobportal-framework'),
				'service_rating' => $service_rating,
				'approved'       => (int) $comment_approved,
			]);
		}

		/**
		 * Secure file uploads for review thumbs
		 */
		private function handle_review_uploads($files)
		{
			if (empty($files) || !isset($files['name'])) {
				return [];
			}

			$countfiles    = count($files['name']);
			$comment_thumb = [];

			for ($i = 0; $i < $countfiles; $i++) {
				if ($files['error'][$i] !== UPLOAD_ERR_OK) {
					continue;
				}

				$submitted_file = [
					'error'    => $files['error'][$i],
					'name'     => sanitize_file_name($files['name'][$i]),
					'size'     => (int) $files['size'][$i],
					'tmp_name' => $files['tmp_name'][$i],
					'type'     => $files['type'][$i],
				];

				$movefile = wp_handle_upload($submitted_file, ['test_form' => false]);
				if (isset($movefile['file'])) {
					$filetype = wp_check_filetype($movefile['file'], null);
					if (!in_array($filetype['type'], ['image/jpeg', 'image/png', 'image/gif'], true)) {
						continue;
					}

					$attachment_details = [
						'guid'           => $movefile['url'],
						'post_mime_type' => $filetype['type'],
						'post_title'     => preg_replace('/\.[^.]+$/', '', basename($submitted_file['name'])),
						'post_content'   => '',
						'post_status'    => 'inherit'
					];

					$attach_id   = wp_insert_attachment($attachment_details, $movefile['file']);
					$attach_data = wp_generate_attachment_metadata($attach_id, $movefile['file']);
					wp_update_attachment_metadata($attach_id, $attach_data);

					$comment_thumb[] = $attach_id;
				}
			}

			return $comment_thumb;
		}


		/**
		 * @param $service_id
		 * @param $rating_value
		 * @param bool|true $comment_exist
		 * @param int $old_rating_value
		 */
		public function rating_meta_filter($service_id, $rating_value, $comment_exist = true, $old_rating_value = 0)
		{
			update_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_rating', $rating_value);
		}

		/**
		 * Submit review
		 */
		public function submit_reply_ajax()
		{
			check_ajax_referer('jobportal_submit_reply_ajax_nonce', 'jobportal_security_submit_reply');
			if (! is_user_logged_in()) {
				wp_send_json_error(['message' => __('You must be logged in to reply.', 'jobportal-framework')], 401);
			}
			global $wpdb, $current_user;
			wp_get_current_user();
			$user_id  = $current_user->ID;
			$user     = get_user_by('id', $user_id);
			$service_id = isset($_POST['service_id']) ? jobportal_clean(wp_unslash($_POST['service_id'])) : '';
			$comment_approved = 1;
			$auto_publish_review_service = get_option('comment_moderation');
			if ($auto_publish_review_service == 1) {
				$comment_approved = 0;
			}
			$data = array();
			$user = $user->data;

			$data['comment_post_ID']      = $service_id;
			$data['comment_content']      = isset($_POST['message']) ? wp_filter_post_kses($_POST['message']) : '';
			$data['comment_date']         = current_time('mysql');
			$data['comment_approved']     = $comment_approved;
			$data['comment_author']       = $user->user_login;
			$data['comment_author_email'] = $user->user_email;
			$data['comment_author_url']   = $user->user_url;
			$data['comment_parent']       = isset($_POST['comment_id']) ? jobportal_clean(wp_unslash($_POST['comment_id'])) : '';
			$data['user_id']              = $user_id;

			$comment_id = wp_insert_comment($data);

			echo json_encode(array('success' => true));

			wp_die();
		}

		/**
		 * Company submit
		 */
		public function service_submit_ajax()
		{
			check_ajax_referer('jobportal_service_submit_ajax_nonce', 'jobportal_security_service_submit');

			if (! is_user_logged_in()) {
				echo json_encode(array('success' => false, 'message' => esc_html__('You must be logged in.', 'jobportal-framework')));
				wp_die();
			}

			$service_form               = isset($_REQUEST['service_form']) ? jobportal_clean(wp_unslash($_REQUEST['service_form'])) : '';
			$service_id                 = isset($_REQUEST['service_id']) ? jobportal_clean(wp_unslash($_REQUEST['service_id'])) : '';
			$service_title              = isset($_REQUEST['service_title']) ? jobportal_clean(wp_unslash($_REQUEST['service_title'])) : '';
			$service_categories         = isset($_REQUEST['service_categories']) ? jobportal_clean(wp_unslash($_REQUEST['service_categories'])) : '';
			$service_skills         = isset($_REQUEST['service_skills']) ? jobportal_clean(wp_unslash($_REQUEST['service_skills'])) : '';
			$service_price      = isset($_REQUEST['service_price']) ? jobportal_clean(wp_unslash($_REQUEST['service_price'])) : '';
			$service_currency       = isset($_REQUEST['service_currency']) ? wp_kses_post(wp_unslash($_REQUEST['service_currency'])) : '';
			$service_time      = isset($_REQUEST['service_time']) ? jobportal_clean(wp_unslash($_REQUEST['service_time'])) : '';
			$service_time_type       = isset($_REQUEST['service_time_type']) ? jobportal_clean(wp_unslash($_REQUEST['service_time_type'])) : '';
			$service_des        = isset($_REQUEST['service_des']) ? jobportal_clean(wp_unslash($_REQUEST['service_des'])) : '';
			$service_languages      = isset($_REQUEST['service_languages']) ? jobportal_clean(wp_unslash($_REQUEST['service_languages'])) : '';
			$service_languages_level     = isset($_REQUEST['service_languages_level']) ? jobportal_clean(wp_unslash($_REQUEST['service_languages_level'])) : '';

			$service_thumbnail_url = isset($_REQUEST['service_thumbnail_url']) ? jobportal_clean(wp_unslash($_REQUEST['service_thumbnail_url'])) : '';
			$service_thumbnail_id  = isset($_REQUEST['service_thumbnail_id']) ? jobportal_clean(wp_unslash($_REQUEST['service_thumbnail_id'])) : '';
			$jobportal_gallery_ids          = isset($_REQUEST['jobportal_gallery_ids']) ? jobportal_clean(wp_unslash($_REQUEST['jobportal_gallery_ids'])) : '';
			$service_video_url      = isset($_REQUEST['service_video_url']) ? jobportal_clean(wp_unslash($_REQUEST['service_video_url'])) : '';
			$service_map_location       = isset($_REQUEST['service_map_location']) ? jobportal_clean(wp_unslash($_REQUEST['service_map_location'])) : '';
			$service_map_address        = isset($_REQUEST['service_map_address']) ? jobportal_clean(wp_unslash($_REQUEST['service_map_address'])) : '';
			$service_location       = isset($_REQUEST['service_location']) ? jobportal_clean(wp_unslash($_REQUEST['service_location'])) : '';
			$service_latitude      = isset($_REQUEST['service_latitude']) ? jobportal_clean(wp_unslash($_REQUEST['service_latitude'])) : '';
			$service_longtitude       = isset($_REQUEST['service_longtitude']) ? jobportal_clean(wp_unslash($_REQUEST['service_longtitude'])) : '';

			$service_addons_title       = isset($_REQUEST['service_addons_title']) ? jobportal_clean(wp_unslash($_REQUEST['service_addons_title'])) : '';
			$service_addons_price        = isset($_REQUEST['service_addons_price']) ? jobportal_clean(wp_unslash($_REQUEST['service_addons_price'])) : '';
			$service_addons_description      = isset($_REQUEST['service_addons_description']) ? jobportal_clean(wp_unslash($_REQUEST['service_addons_description'])) : '';

			$service_faq_title      = isset($_REQUEST['service_faq_title']) ? jobportal_clean(wp_unslash($_REQUEST['service_faq_title'])) : '';
			$service_faq_description       = isset($_REQUEST['service_faq_description']) ? jobportal_clean(wp_unslash($_REQUEST['service_faq_description'])) : '';

			global $current_user;
			wp_get_current_user();
			$user_id = $current_user->ID;

			$new_service = array();
			$new_service['post_type'] = 'service';
			$new_service['post_author'] = $user_id;

			if (isset($service_title)) {
				$new_service['post_title'] = $service_title;
			}

			if (isset($service_url)) {
				$new_service['post_name'] = $service_url;
			}

			if (isset($service_des)) {
				$new_service['post_content'] = $service_des;
			}

			$submit_action = $service_form;
			$auto_publish         = jobportal_get_option('service_auto_publish', 1);
			$auto_publish_edited  = jobportal_get_option('service_auto_publish_edited', 1);
			$paid_submission_type = jobportal_get_option('candidate_paid_submission_type', 'no');
			$enable_candidate_service_fee = jobportal_get_option('enable_candidate_service_fee');
			$candidate_number_service_fee = jobportal_get_option('candidate_number_service_fee');

			if ($submit_action == 'submit-service') {
				$service_id = 0;
				if ($auto_publish == 1) {
					$new_service['post_status'] = 'publish';
				} else {
					$new_service['post_status'] = 'pending';
				}
				if (!empty($new_service['post_title'])) {
					$service_id = wp_insert_post($new_service, true);
				}
				if ($service_id > 0) {
					if ($paid_submission_type == 'candidate_per_package') {
						$candidate_package_key = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'candidate_package_key', $user_id);
						update_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'candidate_package_key', $candidate_package_key);
						$candidate_package_number_service = intval(get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'candidate_package_number_service', $user_id));
						if ($candidate_package_number_service - 1 >= 0) {
							update_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'candidate_package_number_service', $candidate_package_number_service - 1);
						}
					}
					update_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'enable_candidate_package_expires', 0);
					update_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_featured', 0);
					update_post_meta($service_id, 'total_point_review', 0);
				}
				echo json_encode(array('success' => true));
			} elseif ($submit_action == 'edit-service') {
				$service_id        = absint(wp_unslash($service_id));
				$new_service['ID'] = intval($service_id);
				if ($auto_publish_edited == 1) {
					$new_service['post_status'] = 'publish';
				} else {
					$new_service['post_status'] = 'pending';
				}
				if ($paid_submission_type == 'candidate_per_package') {
					$jobportal_candidate_package = new JobPortal_candidate_package();
					$check_candidate_package = $jobportal_candidate_package->user_candidate_package_available($user_id);
					if (($check_candidate_package == -1) || ($check_candidate_package == 0)) {
						return -1;
					}
				}

				$service_id = wp_update_post($new_service);
				echo json_encode(array('success' => true));
			}

			if ($service_id > 0) {
				//Category
				if (!empty($service_categories)) {
					$service_categories = intval($service_categories);
					wp_set_object_terms($service_id, $service_categories, 'service-categories');
				}

				if (!empty($service_skills)) {
					$service_skills = array_map('intval', $service_skills);
					wp_set_object_terms($service_id, $service_skills, 'service-skills');
				}

				if (!empty($service_languages)) {
					$service_languages = array_map('intval', $service_languages);
					wp_set_object_terms($service_id, $service_languages, 'service-language');
				}

				if (!empty($service_location)) {
					$service_location = intval($service_location);
					wp_set_object_terms($service_id, $service_location, 'service-location');
				}

				//Field

				if (isset($service_price)) {
					update_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_price', $service_price);

					$price_received = intval($service_price) * (100 - intval($candidate_number_service_fee)) / 100;
					if ($enable_candidate_service_fee === '1' && !empty($candidate_number_service_fee)) {
						update_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_price_received', $price_received);
					}
				}

				if (isset($service_currency)) {
					update_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_currency_type', $service_currency);
				}

				if (isset($service_time)) {
					update_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_number_time', $service_time);
				}

				if (isset($service_time_type)) {
					update_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_time_type', $service_time_type);
				}

				if (isset($service_languages_level)) {
					update_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_language_level', $service_languages_level);
				}

				if (isset($service_video_url)) {
					update_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_video_url', $service_video_url);
				}

				if (isset($service_map_address)) {
					update_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_address', $service_map_address);
				}

				if (isset($service_map_location)) {
					$lat_lng = $service_map_location;
					$address = $service_map_address;
					$arr_location = array(
						'location' => $lat_lng,
						'address' => $address,
					);
					update_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_location', $arr_location);
					update_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_address', $service_map_address);
					update_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_latitude', $service_latitude);
					update_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_longtitude', $service_longtitude);
				}

				if (!empty($service_addons_title)) {
					$addons_data  = array();
					for ($i = 0; $i < count($service_addons_title); $i++) {
						$addons_data[] = array(
							JOBPORTAL_METABOX_PREFIX . 'service_addons_title'   => $service_addons_title[$i],
							JOBPORTAL_METABOX_PREFIX . 'service_addons_price'    => $service_addons_price[$i],
							JOBPORTAL_METABOX_PREFIX . 'service_addons_description'    => $service_addons_description[$i],
						);
					}
					update_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_tab_addon', $addons_data);
				}

				if (!empty($service_faq_title)) {
					$faq_data  = array();
					for ($i = 0; $i < count($service_faq_title); $i++) {
						$faq_data[] = array(
							JOBPORTAL_METABOX_PREFIX . 'service_faq_title'   => $service_faq_title[$i],
							JOBPORTAL_METABOX_PREFIX . 'service_faq_description'    => $service_faq_description[$i],
						);
					}
					update_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_tab_faq', $faq_data);
				}

				if (isset($service_thumbnail_url) && isset($service_thumbnail_id)) {
					$service_thumbnail = array(
						'id'  => $service_thumbnail_id,
						'url' => $service_thumbnail_url,
					);
					update_post_meta($service_id, '_thumbnail_id', $service_thumbnail_id);
				}

				if (isset($jobportal_gallery_ids)) {
					$str_img_ids = '';
					foreach ($jobportal_gallery_ids as $service_img_id) {
						$jobportal_gallery_ids[] = intval($service_img_id);
						$str_img_ids .= '|' . intval($service_img_id);
					}
					$str_img_ids = substr($str_img_ids, 1);
					update_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_images', $str_img_ids);
				}
			}

			wp_die();
		}
	}
}
