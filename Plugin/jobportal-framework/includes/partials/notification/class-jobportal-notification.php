<?php
if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('JobPortal_Notification')) {
    /**
     * Class JobPortal_Notification
     */
    class JobPortal_Notification
    {
        /**
         * Notification refresh
         */
        public function jobportal_refresh_notification()
        {
            // Check if user is logged in
            if (!is_user_logged_in()) {
                echo json_encode(array('success' => false, 'message' => 'User not logged in'));
                wp_die();
            }

            global $current_user;
            $current_user_id = $current_user->ID;

            $noti_id = isset($_REQUEST['noti_id']) ? intval(jobportal_clean(wp_unslash($_REQUEST['noti_id']))) : 0;
            $action_click = isset($_REQUEST['action_click']) ? jobportal_clean(wp_unslash($_REQUEST['action_click'])) : '';

            // Handle individual notification actions
            if (!empty($noti_id)) {
                // Verify this notification belongs to current user
                $user_receive_noti = get_post_meta($noti_id, JOBPORTAL_METABOX_PREFIX . 'user_receive_noti', true);

                if ($user_receive_noti != $current_user_id) {
                    echo json_encode(array('success' => false, 'message' => 'Unauthorized access'));
                    wp_die();
                }

                // Verify notification exists and is of correct post type
                $notification_post = get_post($noti_id);
                if (!$notification_post || $notification_post->post_type !== 'notification') {
                    echo json_encode(array('success' => false, 'message' => 'Invalid notification'));
                    wp_die();
                }

                switch ($action_click) {
                    case 'delete':
                        wp_delete_post($noti_id, true);
                        break;
                    case 'mark_read':
                        update_post_meta($noti_id, JOBPORTAL_METABOX_PREFIX . 'notification_read', '1');
                        break;
                    case 'mark_unread':
                        delete_post_meta($noti_id, JOBPORTAL_METABOX_PREFIX . 'notification_read');
                        break;
                }
            }

            // Handle clear all action
            if ($action_click == 'clear') {
                $posts = jobportal_get_data_notification();
                foreach ($posts as $post) :
                    $id = $post->ID;
                    // Double check ownership before deleting
                    $user_receive_noti = get_post_meta($id, JOBPORTAL_METABOX_PREFIX . 'user_receive_noti', true);
                    if ($user_receive_noti == $current_user_id) {
                        wp_delete_post($id, true);
                    }
                    wp_reset_postdata();
                endforeach;
            }

            $data_notification = jobportal_get_data_notification();

            // Extra security: Filter out any notifications not belonging to current user
            if (!empty($data_notification)) {
                $data_notification = array_filter($data_notification, function($notification) use ($current_user_id) {
                    $user_receive_noti = get_post_meta($notification->ID, JOBPORTAL_METABOX_PREFIX . 'user_receive_noti', true);
                    return $user_receive_noti == $current_user_id;
                });
            }

            // Count only unread notifications
            $count = 0;
            if (!empty($data_notification)) {
                foreach ($data_notification as $notification) {
                    $is_read = get_post_meta($notification->ID, JOBPORTAL_METABOX_PREFIX . 'notification_read', true);
                    // Check if NOT read (empty or not equal to '1')
                    if (empty($is_read) || $is_read !== '1') {
                        $count++;
                    }
                }
                if ($count > 99) {
                    $count = '99+';
                }
            }

            ob_start();
            jobportal_get_template('dashboard/notification/content.php', array(
                'data_notification' => $data_notification,
            ));

            $noti_content = ob_get_clean();

            // Log for debugging
            error_log('Notification Action: ' . $action_click);
            error_log('Notification ID: ' . $noti_id);
            error_log('User ID: ' . $current_user_id);
            error_log('Notification Count: ' . $count);

            echo json_encode(array('success' => true,'count' => $count, 'noti_content' => $noti_content));

            wp_die();
        }
    }
}
