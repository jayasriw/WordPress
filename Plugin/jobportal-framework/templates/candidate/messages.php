<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $current_user;
$user_id = $current_user->ID;
$post_id = get_the_ID();
$check_package_send_message = jobportal_get_field_check_employer_package('send_message');
?>
<?php if (is_user_logged_in() && in_array('jobportal_user_employer', (array)$current_user->roles)) { ?>
    <?php if($check_package_send_message == -1 || $check_package_send_message == 0) {?>
        <a href="#" class="jobportal-button btn-add-to-message" data-text="<?php echo esc_attr__('Package expired. Please select a new one.', 'jobportal-framework'); ?>">
            <i class="fal fa-paper-plane"></i>
            <?php esc_html_e('Message', 'jobportal-framework') ?>
        </a>
    <?php } else { ?>
        <a href="#form-messages-popup" class="jobportal-button" id="jobportal-add-messages"
           data-post-current="<?php echo intval($post_id) ?>" data-author-id="<?php echo intval($user_id) ?>">
            <i class="fal fa-paper-plane"></i>
            <?php esc_html_e('Message', 'jobportal-framework') ?>
        </a>
    <?php } ?>
<?php } else { ?>
    <div class="logged-out">
        <a href="#popup-form"
           class="jobportal-button btn-login notice-employer"
           data-candidate-id="<?php echo intval($user_id) ?>"
           data-notice="<?php esc_attr_e('Please login role Employer to view', 'jobportal-framework') ?>">
            <i class="fal fa-paper-plane"></i>
            <?php esc_html_e('Message', 'jobportal-framework') ?>
        </a>
    </div>
<?php } ?>