<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $current_user;
wp_get_current_user();
$user_id = $current_user->ID;
$user_login = $current_user->user_login;
$user_demo = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'user_demo', $user_id);
$ajax_url = admin_url('admin-ajax.php');
$employer_verify_id_before_id = $user_id;
$employer_verify_id_before_url = get_the_author_meta('author_verify_id_before_image_url', $user_id);
$employer_cover_image_id = get_post_thumbnail_id($current_user->ID);
$employer_cover_image_url = get_the_post_thumbnail_url($current_user->ID, 'full');
$image_max_file_size = jobportal_get_option('jobportal_image_max_file_size', '1000kb');
$verify_id_before_upload_nonce = wp_create_nonce('jobportal_verify_id_before_allow_upload');
$verify_id_before_type         = jobportal_get_option('jobportal_image_type');
$verify_id_before_file_size    = jobportal_get_option('jobportal_image_max_file_size', '1000kb');
$verify_id_before_url          = JOBPORTAL_AJAX_URL . '?action=jobportal_verify_id_before_upload_ajax&nonce=' . esc_attr($verify_id_before_upload_nonce);
$verify_id_before_text         = esc_html__('Upload', 'jobportal-framework');
$verify_id_after_upload_nonce = wp_create_nonce('jobportal_verify_id_after_allow_upload');
$verify_id_after_type         = jobportal_get_option('jobportal_image_type');
$verify_id_after_file_size    = jobportal_get_option('jobportal_image_max_file_size', '1000kb');
$verify_id_after_url          = JOBPORTAL_AJAX_URL . '?action=jobportal_verify_id_after_upload_ajax&nonce=' . esc_attr($verify_id_after_upload_nonce);
$verify_id_after_text         = esc_html__('Upload', 'jobportal-framework');
$verify_id_selfie_upload_nonce = wp_create_nonce('jobportal_verify_id_selfie_allow_upload');
$verify_id_selfie_type         = jobportal_get_option('jobportal_image_type');
$verify_id_selfie_file_size    = jobportal_get_option('jobportal_image_max_file_size', '1000kb');
$verify_id_selfie_url          = JOBPORTAL_AJAX_URL . '?action=jobportal_verify_id_selfie_upload_ajax&nonce=' . esc_attr($verify_id_selfie_upload_nonce);
$verify_id_selfie_text         = esc_html__('Upload', 'jobportal-framework');
$identity_verification = get_user_meta($user_id, 'identity_verification', true);
wp_enqueue_script('plupload');
wp_localize_script(
    JOBPORTAL_PLUGIN_PREFIX . 'settings',
    'jobportal_settings_vars',
    array(
        'ajax_url' => JOBPORTAL_AJAX_URL,
        'site_url' => get_site_url(),
        'logout_countdown_text' => esc_html__('You will be logged out in {countdown} seconds for security reasons.', 'jobportal-framework'),
        'ajax_error_text' => esc_html__('An error occurred, please try again!', 'jobportal-framework'),
        'verify_id_before_title'        => esc_html__('Valid file formats', 'jobportal-framework'),
        'verify_id_before_type'         => $verify_id_before_type,
        'verify_id_before_file_size'    => $verify_id_before_file_size,
        'verify_id_before_upload_nonce' => $verify_id_before_upload_nonce,
        'verify_id_before_url'          => $verify_id_before_url,
        'verify_id_before_text'         => $verify_id_before_text,
        'verify_id_after_title'        => esc_html__('Valid file formats', 'jobportal-framework'),
        'verify_id_after_type'         => $verify_id_after_type,
        'verify_id_after_file_size'    => $verify_id_after_file_size,
        'verify_id_after_upload_nonce' => $verify_id_after_upload_nonce,
        'verify_id_after_url'          => $verify_id_after_url,
        'verify_id_after_text'         => $verify_id_after_text,
        'verify_id_selfie_title'        => esc_html__('Valid file formats', 'jobportal-framework'),
        'verify_id_selfie_type'         => $verify_id_selfie_type,
        'verify_id_selfie_file_size'    => $verify_id_selfie_file_size,
        'verify_id_selfie_upload_nonce' => $verify_id_selfie_upload_nonce,
        'verify_id_selfie_url'          => $verify_id_selfie_url,
        'verify_id_selfie_text'         => $verify_id_selfie_text,
    )
);

$terms_condition = jobportal_get_option('terms_condition');
$allowed_html = array(
    'a' => array(
        'href' => array(),
        'title' => array(),
        'target' => array()
    ),
    'strong' => array()
);

$missing = false;
$success = false;

// Check if submit button is clicked
if (isset($_POST['submit'])) {

    // Verify nonce for security
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'jobportal_verify_id_employer_form_nonce')) {
        wp_die(__('Security check failed.', 'jobportal-framework'));
    }

    // Sanitize input data
    $user_id = intval($_POST['user_id']);
    $user = get_userdata($user_id);
    $user_email = $user->user_email;
    $admin_email = get_option('admin_email');
    $user_login = $user->user_login;
    $identity_document = sanitize_text_field($_POST['identity_document']);
    $verify_id_before_id = intval($_POST['author_verify_id_before_image_id']);
    $verify_id_after_id = intval($_POST['author_verify_id_after_image_id']);
    if (empty($verify_id_before_id) || $verify_id_before_id == 0) {
        $missing = 'front-side';
    } else if (empty($verify_id_after_id) || $verify_id_after_id == 0) {
        $missing = 'back-side';
    } else {
        // Save data to database
        update_user_meta($user_id, 'verify_id_before_id', $verify_id_before_id);
        update_user_meta($user_id, 'verify_id_after_id', $verify_id_after_id);
        update_user_meta($user_id, 'identity_document', $identity_document);
        update_user_meta($user_id, 'identity_verification', 'pending');

        // Send email notification to the admin
        JobPortal_Helper::jobportal_send_email(
            $user_email,
            'mail_pending_identify_for_user',
            array(
                'user_login' => $user_login,
                'user_email' => $user_email,
            )
        );

        JobPortal_Helper::jobportal_send_email(
            $admin_email,
            'mail_pending_identify_for_admin',
            array(
                'user_login' => $user_login,
                'user_email' => $user_email,
                'identity_document' => $identity_document,
                'before_image' => wp_get_attachment_url($verify_id_before_id),
                'after_image' => wp_get_attachment_url($verify_id_after_id),
            )
        );

        $success = true;
    }
}
?>
<div class="form-dashboard">
    <form action="<?php echo get_permalink(); ?>" class="block-from form-verify-id" method="post" id="jobportal-verify-id-form" enctype="multipart/form-data">
        <input type="hidden" name="user_id" value="<?php echo esc_attr($user_id); ?>" id="user_id" required />
        <h6><?php esc_html_e('Verify Your Identity', 'jobportal-framework') ?></h6>
        <?php
        if ($identity_verification == 'verified') {
            echo '<div class="success-message">';
            echo '<i class="fas fa-check-circle"></i>';
            echo '<p>' . esc_html__('Your identity has been verified successfully.', 'jobportal-framework') . '</p>';
            echo '</div>';
        } elseif ($success || $identity_verification == 'pending') {
            echo '<div class="warning-message">';
            echo '<i class="fas fa-check-circle"></i>';
            echo '<p>' . esc_html__('Your identity verification request has been submitted successfully. Please wait for admin approval.', 'jobportal-framework') . '</p>';
            echo '</div>';
        } else {
            if ($missing == 'front-side' || $missing == 'back-side') {
                echo '<div class="warning-message">';
                echo '<i class="fas fa-exclamation-circle"></i>';
                echo '<p>' . esc_html__('Please upload both sides of your identity document.', 'jobportal-framework') . '</p>';
                echo '</div>';
            }
        ?>
            <div class="row">
                <div class="form-group col-md-12">
                    <label for="identity_document"><?php esc_html_e('Select a Document to Upload', 'jobportal-framework') ?></label>
                    <div class="select2-field">
                        <select class="point-mark jobportal-select2" name="identity_document" id="identity_document" required>
                            <option value="National ID" selected="selected"><?php esc_html_e('National ID', 'jobportal-framework') ?></option>
                            <option value="Passport"><?php esc_html_e('Passport', 'jobportal-framework') ?></option>
                            <option value="Driving License"><?php esc_html_e('Driving License', 'jobportal-framework') ?></option>
                        </select>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <div class="employer-fields-verify-id-before jobportal-fields-verify-id-before">
                        <label><?php esc_html_e('Front Side', 'jobportal-framework'); ?></label>
                        <div class="form-field">
                            <div id="jobportal_verify_id_before_errors" class="errors-log"></div>
                            <div id="jobportal_verify_id_before_container" class="file-upload-block preview">
                                <div id="jobportal_verify_id_before_view" data-image-id="<?php echo $employer_verify_id_before_id; ?>" data-image-url="<?php if (!empty($employer_verify_id_before_url)) {
                                                                                                                                                        echo $employer_verify_id_before_url;
                                                                                                                                                    } ?>"></div>
                                <div id="jobportal_add_verify_id_before">
                                    <i class="far fa-arrow-from-bottom large"></i>
                                    <p id="jobportal_drop_verify_id_before">
                                        <button type="button" id="jobportal_select_verify_id_before"><?php esc_html_e('Upload', 'jobportal-framework') ?></button>
                                    </p>
                                </div>
                                <input type="hidden" class="verify_id_before_url form-control" name="author_verify_id_before_image_url" value="" id="verify_id_before_url">
                                <input type="hidden" class="verify_id_before_id" name="author_verify_id_before_image_id" value="" id="verify_id_before_id" />
                            </div>
                        </div>
                        <div class="field-note"><?php echo sprintf(__('Maximum file size: %s.', 'jobportal-framework'), $image_max_file_size); ?></div>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <div class="employer-fields-verify-id-after jobportal-fields-verify-id-after">
                        <label><?php esc_html_e('Back Side', 'jobportal-framework'); ?></label>
                        <div class="form-field">
                            <div id="jobportal_verify_id_after_errors" class="errors-log"></div>
                            <div id="jobportal_verify_id_after_container" class="file-upload-block preview">
                                <div id="jobportal_verify_id_after_view" data-image-id="<?php echo $employer_verify_id_after_id; ?>" data-image-url="<?php if (!empty($employer_verify_id_after_url)) {
                                                                                                                                                    echo $employer_verify_id_after_url;
                                                                                                                                                } ?>"></div>
                                <div id="jobportal_add_verify_id_after">
                                    <i class="far fa-arrow-from-bottom large"></i>
                                    <p id="jobportal_drop_verify_id_after">
                                        <button type="button" id="jobportal_select_verify_id_after"><?php esc_html_e('Upload', 'jobportal-framework') ?></button>
                                    </p>
                                </div>
                                <input type="hidden" class="verify_id_after_url form-control" name="author_verify_id_after_image_url" value="" id="verify_id_after_url">
                                <input type="hidden" class="verify_id_after_id" name="author_verify_id_after_image_id" value="" id="verify_id_after_id" />
                            </div>
                        </div>
                        <div class="field-note"><?php echo sprintf(__('Maximum file size: %s.', 'jobportal-framework'), $image_max_file_size); ?></div>
                    </div>
                </div>
            </div>
            <div class="form-submit">
                <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('jobportal_verify_id_employer_form_nonce'); ?>" />
                <button type="submit" class="jobportal-button verify-id-submit" name="submit" id="verify-id-submit" value="submit">
                    <?php esc_html_e('Submit', 'jobportal-framework') ?>
                    <div class="btn-loading"><i class="fas fa-spinner fa-spin"></i></div>
                </button>
                <p><?php echo sprintf(wp_kses(__('By clicking "Submit", you agree to <a target="_blank" href="%s"><strong>Terms & Conditions</strong></a> first', 'jobportal-framework'), $allowed_html), get_permalink($terms_condition)); ?></p>
            </div>
        <?php } ?>
    </form>
</div>
