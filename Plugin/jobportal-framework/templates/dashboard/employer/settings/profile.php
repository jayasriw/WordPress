<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $current_user;
wp_get_current_user();
$default_image = JOBPORTAL_THEME_URI . '/assets/images/default-user-image.png';
$user_id = $current_user->ID;
$user_login = $current_user->user_login;
$user_firstname = get_the_author_meta('first_name', $user_id);
$user_lastname = get_the_author_meta('last_name', $user_id);
$user_email = get_the_author_meta('user_email', $user_id);
$author_mobile_number = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'author_mobile_number', $user_id);
$phone_code = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'phone_code', $user_id);
$author_avatar_image_url = get_the_author_meta('author_avatar_image_url', $user_id);
$author_avatar_image_id = get_the_author_meta('author_avatar_image_id', $user_id);
$user_demo = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'user_demo', $user_id);

if (!$author_avatar_image_url) {
    $author_avatar_image_url = $default_image;
}
wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'settings');
wp_localize_script(
    JOBPORTAL_PLUGIN_PREFIX . 'settings',
    'jobportal_settings_vars',
    array(
        'ajax_url' => JOBPORTAL_AJAX_URL,
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
jobportal_get_avatar_enqueue();
?>

<div class="form-dashboard">
    <form class="block-from form-settings">
        <h6><?php esc_html_e('Personal info', 'jobportal-framework') ?></h6>
        <div class="jobportal-user-avatar">
            <div class="avatar jobportal-fields-avatar">
                <label><?php esc_html_e('Your photo', 'jobportal-framework'); ?></label>
                <div class="form-field">
                    <div id="jobportal_avatar_errors" class="errors-log"></div>
                    <div id="jobportal_avatar_container" class="file-upload-block preview">
                        <div id="jobportal_avatar_view" data-image-id="<?php echo $author_avatar_image_id; ?>"
                            data-image-url="<?php if (!empty($author_avatar_image_url)) {
                                                echo $author_avatar_image_url;
                                            } ?>"></div>
                        <div id="jobportal_add_avatar">
                            <i class="far fa-arrow-from-bottom large"></i>
                            <p id="jobportal_drop_avatar">
                                <button type="button"
                                    id="jobportal_select_avatar"><?php esc_html_e('Upload', 'jobportal-framework') ?></button>
                            </p>
                        </div>
                        <input type="hidden" class="avatar_url author_avatar_image_url form-control"
                            name="author_avatar_image_url" value="<?php echo esc_attr($author_avatar_image_url); ?>"
                            id="author_avatar_image_url">
                        <input type="hidden" class="avatar_id author_avatar_image_id" name="author_avatar_image_id"
                            value="<?php echo esc_attr($author_avatar_image_id); ?>" id="author_avatar_image_id" />
                    </div>
                </div>
            </div>
            <p class="des-avatar"><?php esc_html_e('Update your photo manually, if the photo is not set the default Avatar will be the same as your login email account.', 'jobportal-framework') ?></p>
        </div>
        <div class="row">
            <div class="form-group col-md-6">
                <label for="user_firstname"><?php esc_html_e('First name', 'jobportal-framework') ?></label>
                <input type="text" id="user_firstname" name="user_firstname"
                    value="<?php echo esc_attr($user_firstname); ?>">
            </div>
            <div class="form-group col-md-6">
                <label for="user_lastname"><?php esc_html_e('Last name', 'jobportal-framework') ?></label>
                <input type="text" id="user_lastname" name="user_lastname"
                    value="<?php echo esc_attr($user_lastname); ?>">
            </div>
            <div class="form-group col-md-6">
                <label for="user_email"><?php esc_html_e('Email address', 'jobportal-framework') ?></label>
                <input type="email" id="user_email" name="user_email" value="<?php echo esc_attr($user_email); ?>">
            </div>
            <div class="form-group col-md-6">
                <label for="author_mobile_number"><?php esc_html_e('Phone number', 'jobportal-framework') ?></label>
                <div class="tel-group">
                    <select name="prefix_code" class="jobportal-select2 prefix-code">
                        <?php
                        $prefix_code = phone_prefix_code();
                        $default_phone = JobPortal_Helper::jobportal_get_option('default_phone_number');
                        $selected_prefix = JobPortal_Helper::get_prefix_key_from_phone($author_mobile_number, $prefix_code, $default_phone);
                        foreach ($prefix_code as $key => $value) {
                            $selected = ($key == $selected_prefix) ? 'selected' : '';
                            echo '<option value="' . esc_attr($key) . '" data-dial-code="' . esc_attr($value['code']) . '" ' . $selected . '>' . esc_html($value['name']) . ' (' . esc_html($value['code']) . ')</option>';
                        }
                        ?>
                    </select>
                    <?php
                    $default_phone_code = isset($prefix_code[$selected_prefix]) ? $prefix_code[$selected_prefix]['code'] : (isset($prefix_code[$default_phone]) ? $prefix_code[$default_phone]['code'] : '');
                    $input_value = !empty($author_mobile_number) ? preg_replace('/^' . preg_quote($default_phone_code, '/') . '0+/', $default_phone_code, preg_replace('/[^0-9+]/', '', $author_mobile_number)) : $default_phone_code;
                    ?>
                    <input type="tel" id="author_mobile_number" name="author_mobile_number"
                        data-prefix="<?php echo esc_attr($default_phone_code); ?>"
                        value="<?php echo esc_attr($input_value); ?>"
                        placeholder="<?php esc_attr_e('Enter phone', 'jobportal-framework') ?>"
                        pattern="\+[0-9]{8,12}"
                        required>
                </div>
            </div>
        </div>
        <?php wp_nonce_field('jobportal_update_profile_ajax_nonce', 'jobportal_security_update_profile'); ?>
        <button type="submit" class="jobportal-button" id="jobportal_update_profile">
            <span><?php esc_html_e('Save changes', 'jobportal-framework'); ?></span>
            <span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
        </button>
    </form>
    <!-- Employer form -->
    <form class="block-from form-password form-change-password">
        <h6><?php esc_html_e('Change password', 'jobportal-framework') ?></h6>
        <div class="row">
            <div class="form-group col-md-12">
                <label for="oldpass"><?php esc_html_e('Current password', 'jobportal-framework') ?></label>
                <input class="form-control" type="password" id="oldpass" name="oldpass" value=""
                    placeholder="<?php esc_attr_e('Enter current password', 'jobportal-framework'); ?>">
                <span toggle="#oldpass" class="fa fa-fw fa-eye field-icon jobportal-toggle-password"></span>
            </div>
            <div class="form-group col-md-12">
                <label for="newpass"><?php esc_html_e('New password', 'jobportal-framework') ?></label>
                <input class="form-control" type="password" id="newpass" name="nnewpass" value=""
                    placeholder="<?php esc_attr_e('Enter new password', 'jobportal-framework'); ?>">
                <span toggle="#newpass" class="fa fa-fw fa-eye field-icon jobportal-toggle-password"></span>
            </div>
            <div class="form-group col-md-12">
                <label for="confirmpass"><?php esc_html_e('Confirm new password', 'jobportal-framework') ?></label>
                <input class="form-control" type="password" id="confirmpass" name="confirmpass" value=""
                    placeholder="<?php esc_attr_e('Enter confirm password', 'jobportal-framework'); ?>">
                <span toggle="#confirmpass" class="fa fa-fw fa-eye field-icon jobportal-toggle-password"></span>
            </div>
        </div>
        <?php wp_nonce_field('jobportal_change_password_ajax_nonce', 'jobportal_security_change_password'); ?>
        <div class="message"></div>
        <?php if ($user_demo == 'yes') : ?>
            <button class="jobportal-button btn-add-to-message"
                data-text="<?php echo esc_attr__('This is a "Demo" account, so you can not change it', 'jobportal-framework'); ?>">
                <?php esc_html_e('Save changes', 'jobportal-framework'); ?>
            </button>
        <?php else : ?>
            <button type="submit" class="jobportal-button button-password" id="jobportal_change_pass">
                <span><?php esc_html_e('Save changes', 'jobportal-framework'); ?></span>
                <span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
            </button>
        <?php endif; ?>
    </form>
    <?php if ($user_demo == 'yes') : ?>
        <a class="btn-add-to-message delete-account"
            data-text="<?php echo esc_attr__('This is a "Demo" account so you not cant deactive it', 'jobportal-framework'); ?>"
            href="#"><?php esc_html_e('Deactive account', 'jobportal-framework') ?></a></li>
    <?php else : ?>
        <a href="#" class="delete-account"
            id="btn-setting-deactive"><?php esc_html_e('Deactive account', 'jobportal-framework') ?></a>
    <?php endif; ?>
</div>
