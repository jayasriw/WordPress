<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $current_user;
$user_id = $current_user->ID;
$post_id = get_the_ID();
$check_package_send_message = civi_get_field_check_employer_package('send_message');
?>
<?php if (is_user_logged_in() && in_array('civi_user_employer', (array)$current_user->roles)) { ?>
    <?php if($check_package_send_message == -1 || $check_package_send_message == 0) {?>
        <a href="#" class="civi-button btn-add-to-message" data-text="<?php echo esc_attr__('Package expired. Please select a new one.', 'civi-framework'); ?>">
            <i class="fal fa-paper-plane"></i>
            <?php esc_html_e('Message', 'civi-framework') ?>
        </a>
    <?php } else { ?>
        <a href="#form-messages-popup" class="civi-button" id="civi-add-messages"
           data-post-current="<?php echo intval($post_id) ?>" data-author-id="<?php echo intval($user_id) ?>">
            <i class="fal fa-paper-plane"></i>
            <?php esc_html_e('Message', 'civi-framework') ?>
        </a>
    <?php } ?>
<?php } else { ?>
    <div class="logged-out">
        <a href="#popup-form"
           class="civi-button btn-login notice-employer"
           data-candidate-id="<?php echo intval($user_id) ?>"
           data-notice="<?php esc_attr_e('Please login role Employer to view', 'civi-framework') ?>">
            <i class="fal fa-paper-plane"></i>
            <?php esc_html_e('Message', 'civi-framework') ?>
        </a>
    </div>
<?php } ?>