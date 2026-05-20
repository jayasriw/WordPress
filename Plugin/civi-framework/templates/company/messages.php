<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $current_user;
$user_id = $current_user->ID;
$post_id = get_the_ID();
$check_package = civi_get_field_check_candidate_package('send_message');
?>
<?php if (is_user_logged_in() && in_array('civi_user_candidate', (array)$current_user->roles)) { ?>
    <?php if($check_package == -1 || $check_package == 0) {?>
        <a href="#" class="civi-button btn-add-to-message civi-send-mess" data-text="<?php echo esc_attr__('Package expired. Please select a new one.', 'civi-framework'); ?>">
            <?php esc_html_e('Send message', 'civi-framework'); ?>
        </a>
    <?php } else { ?>
        <a href="#form-messages-popup" class="civi-button civi-send-mess" id="civi-add-messages"
           data-post-current="<?php echo intval($post_id) ?>" data-author-id="<?php echo intval($user_id) ?>">
            <?php esc_html_e('Send message', 'civi-framework') ?>
        </a>
    <?php } ?>
<?php } else { ?>
    <div class="logged-out">
        <a href="#popup-form"
           class="civi-button btn-login civi-send-mess">
            <?php esc_html_e('Send message', 'civi-framework') ?>
        </a>
    </div>
<?php } ?>