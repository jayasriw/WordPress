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
$candidate_verify_id_before_id = $user_id;
$candidate_verify_id_before_url = get_the_author_meta('author_verify_id_before_image_url', $user_id);
$candidate_cover_image_id = get_post_thumbnail_id($current_user->ID);
$candidate_cover_image_url = get_the_post_thumbnail_url($current_user->ID, 'full');
$image_max_file_size = civi_get_option('civi_image_max_file_size', '1000kb');
$verify_id_before_upload_nonce = wp_create_nonce('civi_verify_id_before_allow_upload');
$verify_id_before_type         = civi_get_option('civi_image_type');
$verify_id_before_file_size    = civi_get_option('civi_image_max_file_size', '1000kb');
$verify_id_before_url          = CIVI_AJAX_URL . '?action=civi_verify_id_before_upload_ajax&nonce=' . esc_attr($verify_id_before_upload_nonce);
$verify_id_before_text         = esc_html__('Upload', 'civi-framework');
$verify_id_after_upload_nonce = wp_create_nonce('civi_verify_id_after_allow_upload');
$verify_id_after_type         = civi_get_option('civi_image_type');
$verify_id_after_file_size    = civi_get_option('civi_image_max_file_size', '1000kb');
$verify_id_after_url          = CIVI_AJAX_URL . '?action=civi_verify_id_after_upload_ajax&nonce=' . esc_attr($verify_id_after_upload_nonce);
$verify_id_after_text         = esc_html__('Upload', 'civi-framework');
$verify_id_selfie_upload_nonce = wp_create_nonce('civi_verify_id_selfie_allow_upload');
$verify_id_selfie_type         = civi_get_option('civi_image_type');
$verify_id_selfie_file_size    = civi_get_option('civi_image_max_file_size', '1000kb');
$verify_id_selfie_url          = CIVI_AJAX_URL . '?action=civi_verify_id_selfie_upload_ajax&nonce=' . esc_attr($verify_id_selfie_upload_nonce);
$verify_id_selfie_text         = esc_html__('Upload', 'civi-framework');
$identity_verification = get_user_meta($user_id, 'identity_verification', true);
wp_enqueue_script('plupload');
wp_localize_script(
    CIVI_PLUGIN_PREFIX . 'settings',
    'civi_settings_vars',
    array(
        'ajax_url' => CIVI_AJAX_URL,
        'site_url' => get_site_url(),
        'logout_countdown_text' => esc_html__('You will be logged out in {countdown} seconds for security reasons.', 'civi-framework'),
        'ajax_error_text' => esc_html__('An error occurred, please try again!', 'civi-framework'),
        'verify_id_before_title'        => esc_html__('Valid file formats', 'civi-framework'),
        'verify_id_before_type'         => $verify_id_before_type,
        'verify_id_before_file_size'    => $verify_id_before_file_size,
        'verify_id_before_upload_nonce' => $verify_id_before_upload_nonce,
        'verify_id_before_url'          => $verify_id_before_url,
        'verify_id_before_text'         => $verify_id_before_text,
        'verify_id_after_title'        => esc_html__('Valid file formats', 'civi-framework'),
        'verify_id_after_type'         => $verify_id_after_type,
        'verify_id_after_file_size'    => $verify_id_after_file_size,
        'verify_id_after_upload_nonce' => $verify_id_after_upload_nonce,
        'verify_id_after_url'          => $verify_id_after_url,
        'verify_id_after_text'         => $verify_id_after_text,
        'verify_id_selfie_title'        => esc_html__('Valid file formats', 'civi-framework'),
        'verify_id_selfie_type'         => $verify_id_selfie_type,
        'verify_id_selfie_file_size'    => $verify_id_selfie_file_size,
        'verify_id_selfie_upload_nonce' => $verify_id_selfie_upload_nonce,
        'verify_id_selfie_url'          => $verify_id_selfie_url,
        'verify_id_selfie_text'         => $verify_id_selfie_text,
    )
);

$terms_condition = civi_get_option('terms_condition');
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
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'civi_verify_id_form_nonce')) {
        wp_die(__('Security check failed.', 'civi-framework'));
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

    if (empty($verify_id_before_id)) {
        $missing = 'front-side';
    } elseif (empty($verify_id_after_id)) {
        $missing = 'back-side';
    } else {
        // Save data to database
        update_user_meta($user_id, 'verify_id_before_id', $verify_id_before_id);
        update_user_meta($user_id, 'verify_id_after_id', $verify_id_after_id);
        update_user_meta($user_id, 'identity_document', $identity_document);
        update_user_meta($user_id, 'identity_verification', 'pending');

        // Send email notification to the admin
        Civi_Helper::civi_send_email(
            $user_email,
            'mail_pending_identify_for_user',
            array(
                'user_id' => $user_id,
                'user_email' => $user_email,
            )
        );

        Civi_Helper::civi_send_email(
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
    <form action="<?php echo get_permalink(); ?>" class="block-from form-verify-id" method="post" id="civi-verify-id-form" enctype="multipart/form-data">
        <input type="hidden" name="user_id" value="<?php echo esc_attr($user_id); ?>" id="user_id" required />
        <h6><?php esc_html_e('Verify Your Identity', 'civi-framework') ?></h6>
        <?php
        if ($identity_verification == 'verified') {
            echo '<div class="success-message">';
            echo '<i class="fas fa-check-circle"></i>';
            echo '<p>' . esc_html__('Your identity has been verified successfully.', 'civi-framework') . '</p>';
            echo '</div>';
        } elseif ($success || $identity_verification == 'pending') {
            echo '<div class="warning-message">';
            echo '<i class="fas fa-check-circle"></i>';
            echo '<p>' . esc_html__('Your identity verification request has been submitted successfully. Please wait for admin approval.', 'civi-framework') . '</p>';
            echo '</div>';
        } else {
            if ($missing == 'front-side' || $missing == 'back-side') {
                echo '<div class="warning-message">';
                echo '<i class="fas fa-exclamation-circle"></i>';
                echo '<p>' . esc_html__('Please upload both sides of your identity document.', 'civi-framework') . '</p>';
                echo '</div>';
            }
        ?>
            <div class="row">
                <div class="form-group col-md-12">
                    <label for="identity_document"><?php esc_html_e('Select a Document to Upload', 'civi-framework') ?></label>
                    <div class="select2-field">
                        <select class="point-mark civi-select2" name="identity_document" id="identity_document" required>
                            <option value="National ID" selected="selected"><?php esc_html_e('National ID', 'civi-framework') ?></option>
                            <option value="Passport"><?php esc_html_e('Passport', 'civi-framework') ?></option>
                            <option value="Driving License"><?php esc_html_e('Driving License', 'civi-framework') ?></option>
                        </select>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <div class="candidate-fields-verify-id-before civi-fields-verify-id-before">
                        <label><?php esc_html_e('Front Side', 'civi-framework'); ?></label>
                        <div class="form-field">
                            <div id="civi_verify_id_before_errors" class="errors-log"></div>
                            <div id="civi_verify_id_before_container" class="file-upload-block preview">
                                <div id="civi_verify_id_before_view" data-image-id="<?php echo $candidate_verify_id_before_id; ?>" data-image-url="<?php if (!empty($candidate_verify_id_before_url)) {
                                                                                                                                                        echo $candidate_verify_id_before_url;
                                                                                                                                                    } ?>"></div>
                                <div id="civi_add_verify_id_before">
                                    <i class="far fa-arrow-from-bottom large"></i>
                                    <p id="civi_drop_verify_id_before">
                                        <button type="button" id="civi_select_verify_id_before"><?php esc_html_e('Upload', 'civi-framework') ?></button>
                                    </p>
                                </div>
                                <input type="hidden" class="verify_id_before_url form-control" name="author_verify_id_before_image_url" value="" id="verify_id_before_url">
                                <input type="hidden" class="verify_id_before_id" name="author_verify_id_before_image_id" value="" id="verify_id_before_id" />
                            </div>
                        </div>
                        <div class="field-note"><?php echo sprintf(__('Maximum file size: %s.', 'civi-framework'), $image_max_file_size); ?></div>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <div class="candidate-fields-verify-id-after civi-fields-verify-id-after">
                        <label><?php esc_html_e('Back Side', 'civi-framework'); ?></label>
                        <div class="form-field">
                            <div id="civi_verify_id_after_errors" class="errors-log"></div>
                            <div id="civi_verify_id_after_container" class="file-upload-block preview">
                                <div id="civi_verify_id_after_view" data-image-id="<?php echo $candidate_verify_id_after_id; ?>" data-image-url="<?php if (!empty($candidate_verify_id_after_url)) {
                                                                                                                                                        echo $candidate_verify_id_after_url;
                                                                                                                                                    } ?>"></div>
                                <div id="civi_add_verify_id_after">
                                    <i class="far fa-arrow-from-bottom large"></i>
                                    <p id="civi_drop_verify_id_after">
                                        <button type="button" id="civi_select_verify_id_after"><?php esc_html_e('Upload', 'civi-framework') ?></button>
                                    </p>
                                </div>
                                <input type="hidden" class="verify_id_after_url form-control" name="author_verify_id_after_image_url" value="" id="verify_id_after_url">
                                <input type="hidden" class="verify_id_after_id" name="author_verify_id_after_image_id" value="" id="verify_id_after_id" />
                            </div>
                        </div>
                        <div class="field-note"><?php echo sprintf(__('Maximum file size: %s.', 'civi-framework'), $image_max_file_size); ?></div>
                    </div>
                </div>
            </div>
            <div class="form-submit">
                <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('civi_verify_id_form_nonce'); ?>" required />
                <button type="submit" class="civi-button verify-id-submit" name="submit" id="verify-id-submit" value="submit">
                    <?php esc_html_e('Submit', 'civi-framework') ?>
                    <div class="btn-loading"><i class="fas fa-spinner fa-spin"></i></div>
                </button>
                <p><?php echo sprintf(wp_kses(__('By clicking "Submit", you agree to <a target="_blank" href="%s"><strong>Terms & Conditions</strong></a> first', 'civi-framework'), $allowed_html), get_permalink($terms_condition)); ?></p>
            </div>
        <?php } ?>
    </form>
</div>
