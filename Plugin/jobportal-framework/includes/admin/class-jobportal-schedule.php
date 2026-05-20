<?php
if (!defined('ABSPATH')) {
	exit;
}
if (!class_exists('JobPortal_Schedule')) {
	/**
	 * Class JobPortal_Schedule
	 */
	class JobPortal_Schedule
	{
		/**
		 * Check expire listing
		 */
		public function per_listing_check_expire()
		{
			$jobportal_profile = new JobPortal_Profile();
			$args = array(
				'post_type' => 'place',
				'post_status' => array('publish', 'pending', 'hidden'),
			);
			$data = new WP_Query($args);
			while ($data->have_posts()) : $data->the_post();
				$post_id = get_the_ID();
				$user_id = get_post_field('post_author', $post_id);
				$user = new WP_User($user_id);
				$user_role = $user->roles[0];
				$check_package = $jobportal_profile->user_package_available($user_id);

				if ($check_package == -1) {
					$args = array(
						'ID' => $post_id,
						'post_type' => 'jobs',
						'post_status' => 'expired'
					);
					wp_update_post($args);
					$user_email = $user->user_email;
					$args = array(
						'listing_title' => get_the_title($post_id),
						'listing_url' => get_permalink($post_id)
					);
					jobportal_send_email($user_email, 'mail_expired_listing', $args);
				}
			endwhile;
			wp_reset_postdata();
		}

		/**
		 * Scheduled hook
		 */
		public function scheduled_hook()
		{
			$paid_submission_type = jobportal_get_option('paid_submission_type', 'no');
			if ($paid_submission_type == 'per_package') {
				$jobportal_profile = new JobPortal_Profile();
                $args = array(
                    'post_type' => 'jobs',
                    'post_status' => array('publish', 'pending', 'hidden'),
                );
                $data = new WP_Query($args);
                while ($data->have_posts()): $data->the_post();
                    $post_id = get_the_ID();
                    $user_id = get_post_field('post_author', $post_id);
                    $check_package = $jobportal_profile->user_package_available($user_id);

                    if ($check_package == -1) {
                        if(!wp_next_scheduled('jobportal_per_listing_check_expire')) {

                            //twicedaily
                            wp_schedule_event(time(), 'twicedaily', 'jobportal_per_listing_check_expire');
                        }
                    }
                endwhile;
                wp_reset_postdata();
			}
		}

		public static function clear_scheduled_hook()
		{
			wp_clear_scheduled_hook('jobportal_per_listing_check_expire');
		}
	}
}
