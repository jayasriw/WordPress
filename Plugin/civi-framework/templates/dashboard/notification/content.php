<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $current_user;
?>
<?php if (!empty($data_notification)) { ?>
    <div class="head-noti">
        <div class="head-left">
            <span class="noti-refresh">
                <i class="far fa-sync fa-spin"></i>
                <?php esc_html_e('Refresh', 'civi-framework'); ?>
            </span>
            <span class="noti-clear">
                <i class="far fa-trash-alt"></i>
                <?php esc_html_e('Clear All', 'civi-framework'); ?>
            </span>
        </div>
        <a href="#" class="close-noti">
            <i class="far fa-times"></i>
        </a>
    </div>
    <ul>
        <?php foreach ($data_notification as $data) {
            $post_id = $data->ID;
            $user_send_id = get_post_meta($post_id, CIVI_METABOX_PREFIX . 'user_send_noti', true);

            // Validate user_send_id
            if (empty($user_send_id)) {
                $user_send_id = 0;
            }

            $user_avatar = get_the_author_meta('author_avatar_image_url', $user_send_id);

            // Get user display name using helper function
            $user_send = civi_get_user_display_name($user_send_id);
            // Check if notification is read (must be exactly '1')
            $is_read = get_post_meta($post_id, CIVI_METABOX_PREFIX . 'notification_read', true) === '1';

            // Get notification message
            $mess_noti = get_post_meta($post_id, CIVI_METABOX_PREFIX . 'mess_noti', true);
            if (empty($mess_noti)) {
                $mess_noti = esc_html__('Notification', 'civi-framework');
            }

            // Get time ago with validation
            $post_time = get_the_time('U', $post_id);
            if ($post_time) {
                $time = human_time_diff($post_time, current_time('timestamp'));
            } else {
                $time = esc_html__('recently', 'civi-framework');
            }

            $link = get_post_meta($post_id, CIVI_METABOX_PREFIX . 'link_post_noti', true);
            $notification_title = get_the_title($post_id);

            if (!empty($link) && !empty($notification_title)) {
                $link_noti = '<a href="' . esc_url($link) . '">' . esc_html($notification_title) . '</a>';
            } else {
                $link_noti = '';
            }

            $page_link = get_post_meta($post_id, CIVI_METABOX_PREFIX . 'link_page_noti', true);
            if (!empty($page_link) && $page_link !== '#') {
                $link_page = esc_url($page_link);
            } else {
                $link_page = '#';
            }

        ?>
            <li class="<?php echo $is_read ? '' : 'is-unread'; ?>">
                <?php if (!empty($user_avatar)) : ?>
                    <img class="avatar" src="<?php echo esc_attr($user_avatar) ?>" alt="" />
                <?php else : ?>
                    <span class="avatar"><i class="far fa-camera"></i></span>
                <?php endif; ?>
                <span class="content-wrapper">
                    <span class="content">
                        <?php
                        // Build notification message
                        $notification_message = esc_html($mess_noti);
                        if (!empty($link_noti)) {
                            $notification_message .= ' ' . $link_noti;
                        }
                        $notification_message .= ' ' . esc_html__('by', 'civi-framework') . ' ';
                        $notification_message .= '<span class="athour">' . esc_html($user_send) . '</span>';

                        echo wp_kses_post($notification_message);
                        ?>
                    </span>
                    <span class="date">
                        <?php echo sprintf(esc_html__('%s ago', 'civi-framework'), esc_html($time)); ?>
                    </span>
                </span>
                <?php if (!$is_read) : ?>
                    <a href="#" class="btn-quick-read" data-noti-id="<?php echo esc_attr($post_id); ?>" title="<?php esc_attr_e('Mark as Read', 'civi-framework'); ?>">
                        <span class="unread-dot"></span>
                    </a>
                <?php endif; ?>
                <span class="action action-setting">
                    <a href="#" class="icon-setting-civi"><i class="fal fa-ellipsis-v"></i></a>
                    <span class="action-dropdown">
                        <?php if (!$is_read) : ?>
                            <a class="btn-mark-read" data-noti-id="<?php echo esc_attr($post_id); ?>" href="#">
                                <i class="far fa-check"></i>
                                <?php esc_html_e('Mark as Read', 'civi-framework'); ?>
                            </a>
                        <?php else : ?>
                            <a class="btn-mark-unread" data-noti-id="<?php echo esc_attr($post_id); ?>" href="#">
                                <i class="far fa-circle"></i>
                                <?php esc_html_e('Mark as Unread', 'civi-framework'); ?>
                            </a>
                        <?php endif; ?>
                        <a class="btn-delete" data-noti-id="<?php echo esc_attr($post_id); ?>" href="#">
                            <i class="far fa-trash-alt"></i>
                            <?php esc_html_e('Delete', 'civi-framework'); ?>
                        </a>
                    </span>
                </span>
                <a href="<?php echo esc_url($link_page) ?>" class="link-page" data-noti-id="<?php echo esc_attr($post_id); ?>" data-is-read="<?php echo $is_read ? '1' : '0'; ?>"<?php echo ($link_page === '#') ? ' onclick="return false;"' : ''; ?>></a>
            </li>
        <?php } ?>
    </ul>
<?php } else { ?>
    <span class="empty"><?php esc_html_e('You do not have any notifications.', 'civi-framework'); ?></span>
<?php } ?>
