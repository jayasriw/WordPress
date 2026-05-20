<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $current_user;
wp_get_current_user();
$user_id = $current_user->ID;
$user_login = $current_user->user_login;
$user_demo = get_the_author_meta(CIVI_METABOX_PREFIX . 'user_demo', $user_id);
$ajax_url = admin_url('admin-ajax.php');
$upload_nonce = wp_create_nonce('civi_thumbnail_allow_upload');

wp_enqueue_script('plupload');
wp_enqueue_script('jquery-validate');
wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'settings');
wp_localize_script(
    CIVI_PLUGIN_PREFIX . 'settings',
    'civi_settings_vars',
    array(
        'ajax_url' => CIVI_AJAX_URL,
        'site_url' => get_site_url(),
        'verify_id_before_title' => '',
        'verify_id_before_type' => '',
        'verify_id_before_file_size' => '',
        'verify_id_before_text' => '',
        'verify_id_before_url' => '',
        'verify_id_before_upload_nonce' => '',
        'verify_id_after_title' => '',
        'verify_id_after_type' => '',
        'verify_id_after_file_size' => '',
        'verify_id_after_text' => '',
        'verify_id_after_url' => '',
        'verify_id_after_upload_nonce' => '',
        'verify_id_selfie_title' => '',
        'verify_id_selfie_type' => '',
        'verify_id_selfie_file_size' => '',
        'verify_id_selfie_text' => '',
        'verify_id_selfie_url' => '',
        'verify_id_selfie_upload_nonce' => '',
    )
);
?>
<div class="form-dashboard">
    <!-- Candidate form -->
    <form action="#" class="block-from form-password form-change-password">
        <h6><?php esc_html_e('Change password', 'civi-framework') ?></h6>
        <div class="row">
            <div class="form-group col-md-12">
                <label for="oldpass"><?php esc_html_e('Current password', 'civi-framework') ?></label>
                <input class="form-control" type="password" id="oldpass" name="oldpass" value=""
                    placeholder="<?php esc_attr_e('Enter current password', 'civi-framework'); ?>">
                <span toggle="#oldpass" class="fa fa-fw fa-eye field-icon civi-toggle-password"></span>
            </div>
            <div class="form-group col-md-12">
                <label for="newpass"><?php esc_html_e('New password', 'civi-framework') ?></label>
                <input class="form-control" type="password" id="newpass" name="nnewpass" value=""
                    placeholder="<?php esc_attr_e('Enter new password', 'civi-framework'); ?>">
                <span toggle="#newpass" class="fa fa-fw fa-eye field-icon civi-toggle-password"></span>
            </div>
            <div class="form-group col-md-12">
                <label for="confirmpass"><?php esc_html_e('Confirm new password', 'civi-framework') ?></label>
                <input class="form-control" type="password" id="confirmpass" name="confirmpass" value=""
                    placeholder="<?php esc_attr_e('Enter confirm password', 'civi-framework'); ?>">
                <span toggle="#confirmpass" class="fa fa-fw fa-eye field-icon civi-toggle-password"></span>
            </div>
        </div>
        <?php wp_nonce_field('civi_change_password_ajax_nonce', 'civi_security_change_password'); ?>
        <div class="message"></div>
        <?php if ($user_demo == 'yes') : ?>
            <button class="civi-button btn-add-to-message"
                data-text="<?php echo esc_attr__('This is a "Demo" account, so you can not change it', 'civi-framework'); ?>">
                <?php esc_html_e('Save changes', 'civi-framework'); ?>
            </button>
        <?php else : ?>
            <button class="civi-button button-password" id="civi_change_pass">
                <span><?php esc_html_e('Save changes', 'civi-framework'); ?></span>
                <span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
            </button>
        <?php endif; ?>
    </form>
    <?php if ($user_demo == 'yes') : ?>
        <a class="btn-add-to-message delete-account"
            data-text="<?php echo esc_attr__('This is a "Demo" account so you not cant deactive it', 'civi-framework'); ?>"
            href="#"><?php esc_html_e('Deactive account', 'civi-framework') ?></a></li>
    <?php else : ?>
        <a href="#" class="delete-account"
            id="btn-setting-deactive"><?php esc_html_e('Deactive account', 'civi-framework') ?></a>
    <?php endif; ?>
</div>
