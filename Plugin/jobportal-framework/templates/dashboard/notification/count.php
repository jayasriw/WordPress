<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $current_user;

// Count only unread notifications
$count = 0;
if (!empty($data_notification)) {
    foreach ($data_notification as $noti) {
        $is_read = get_post_meta($noti->ID, JOBPORTAL_METABOX_PREFIX . 'notification_read', true);
        // Check if NOT read (empty or not equal to '1')
        if (empty($is_read) || $is_read !== '1') {
            $count++;
        }
    }
    if ($count > 99) {
        $count = '99+';
    }
}
?>

<?php if (in_array('jobportal_user_candidate', (array)$current_user->roles)
    || in_array('jobportal_user_employer', (array)$current_user->roles)) { ?>
    <a href="#" class="icon-noti">
        <i class="far fa-bell"></i>
        <span><?php echo $count; ?></span>
    </a>
<?php } else { ?>
    <div class="logged-out">
        <a href="#popup-form" class="btn-login icon-noti notice-employer" data-notice="<?php esc_attr_e('Please login Employer or Candidate', 'jobportal-framework') ?>">
            <i class="far fa-bell"></i>
            <span>0</span>
        </a>
    </div>
<?php } ?>

