<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $current_user;
$user_id = $current_user->ID;
$post_id = get_the_ID();
$check_package = jobportal_get_field_check_candidate_package('send_message');
?>
<?php if (is_user_logged_in() && in_array('jobportal_user_candidate', (array)$current_user->roles)) { ?>
    <?php if($check_package == -1 || $check_package == 0) {?>
        <a href="#" class="jobportal-button btn-add-to-message jobportal-send-mess" data-text="<?php echo esc_attr__('Package expired. Please select a new one.', 'jobportal-framework'); ?>">
            <?php esc_html_e('Send message', 'jobportal-framework'); ?>
        </a>
    <?php } else { ?>
        <a href="#form-messages-popup" class="jobportal-button jobportal-send-mess" id="jobportal-add-messages"
           data-post-current="<?php echo intval($post_id) ?>" data-author-id="<?php echo intval($user_id) ?>">
            <?php esc_html_e('Send message', 'jobportal-framework') ?>
        </a>
    <?php } ?>
<?php } else { ?>
    <div class="logged-out">
        <a href="#popup-form"
           class="jobportal-button btn-login jobportal-send-mess">
            <?php esc_html_e('Send message', 'jobportal-framework') ?>
        </a>
    </div>
<?php } ?>