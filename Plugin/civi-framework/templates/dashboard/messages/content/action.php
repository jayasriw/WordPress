<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

wp_enqueue_script('plupload');
wp_enqueue_script('jquery-validate');
$mess_image_upload_nonce = wp_create_nonce('civi_mess_image_allow_upload');
$mess_image_type = civi_get_option('civi_image_type');
$mess_image_id = wp_create_nonce('civi_mess_image_id');
$mess_image_file_size = civi_get_option('civi_image_max_file_size', '1000kb');

$file_type = civi_get_option('civi-cv-type');
$max_file_size = civi_get_option('civi_image_max_file_size', '1000kb');
$file_upload_nonce = wp_create_nonce('civi_thumbnail_allow_upload');
$file_url = CIVI_AJAX_URL . '?action=civi_thumbnail_upload_ajax&nonce=' . esc_attr($file_upload_nonce);


wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'mess_image');
wp_localize_script(
    CIVI_PLUGIN_PREFIX . 'mess_image',
    'civi_mess_image_vars',
    array(
        'ajax_url' => CIVI_AJAX_URL,
        'mess_image_title' => esc_html__('Valid file formats', 'civi-framework'),
        'mess_image_type' => $mess_image_type,
        'mess_image_file_size' => $mess_image_file_size,
        'mess_image_id' => $mess_image_id,
        'mess_image_upload_nonce' => $mess_image_upload_nonce,
    )
);

wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'mess_file');
wp_localize_script(
    CIVI_PLUGIN_PREFIX . 'mess_file',
    'civi_mess_file_vars',
    array(
        'ajax_url' => CIVI_AJAX_URL,
        'title' => esc_html__('Valid file formats', 'civi-framework'),
        'file_type' => $file_type,
        'max_file_size' => $max_file_size,
        'file_upload_nonce' => $file_upload_nonce,
        'file_url' => $file_url,
    )
);
?>
<div class="content-write">
    <div id="civi_mess_image_view_<?php echo esc_attr($mess_image_id); ?>" class="custom-image-view"></div>
    <div id="civi_mess_file_view"></div>
    <textarea placeholder="<?php esc_attr_e('Write your message', 'civi-framework'); ?>" name="uxper_send_mess"></textarea>
</div>
<div class="mess-action">
    <div class="civi-fields-mess_image">
        <div id="civi_mess_image_container_<?php echo esc_attr($mess_image_id); ?>" class="file-upload-block preview">
            <div id="civi_add_mess_image_<?php echo esc_attr($mess_image_id); ?>" class="custom-image-add">
                <p id="civi_drop_mess_image_<?php echo esc_attr($mess_image_id); ?>" style="margin-bottom: 0">
                    <button type="button" class="tooltip" id="civi_select_mess_image_<?php echo esc_attr($mess_image_id); ?>"
                        data-title="<?php echo esc_attr__('Upload Image', 'civi-framework') ?>">
                        <i class="far fa-images"></i>
                    </button>
                </p>
            </div>
            <input type="hidden" class="mess_image_url" value="" id="mess_image_url_<?php echo esc_attr($mess_image_id); ?>">
            <input type="hidden" class="mess_image_id" value="" id="mess_image_id_<?php echo esc_attr($mess_image_id); ?>" />
        </div>
        <input type="hidden" class="image-id" value="<?php echo esc_attr($mess_image_id); ?>">
    </div>
    <div class="civi-upload-file">
        <div class="form-field">
            <div id="civi_file_container" class="file-upload-block preview">
                <div class="civi_cv_file civi_add-cv">
                    <p id="civi_drop_file" style="margin-bottom: 0">
                        <button type="button" class="tooltip" id="civi_select_file"
                            data-title="<?php echo esc_attr__('Upload File', 'civi-framework') ?>">
                            <i class="far fa-file-upload"></i>
                        </button>
                    </p>
                </div>
                <input type="hidden" class="file_url form-control" name="file_url" value="" id="file_url">
            </div>
        </div>
    </div>
    <button id="btn-write-message">
        <?php esc_html_e('Send', 'civi-framework'); ?>
        <span class="btn-loading"><i class="far fa-spinner fa-spin large"></i></span>
    </button>
</div>
