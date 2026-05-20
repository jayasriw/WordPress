<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $current_user, $company_data, $company_meta_data, $hide_company_fields;
$user_id = $current_user->ID;
$image_max_file_size = jobportal_get_option('jobportal_image_max_file_size', '1000kb');
$user_demo = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'user_demo', $user_id);
jobportal_get_gallery_enqueue();
?>
<div class="company-fields-media jobportal-fields-gallery">
    <label><?php esc_html_e('Image', 'jobportal-framework'); ?></label>
    <div class="company-fields company-fields-file company-gallery-image">
        <div class="field-media-wrapper">
            <div class="media-gallery">
                <div id="jobportal_gallery_thumbs">
                    <?php
                    $company_img_arg = get_post_meta($company_data->ID, JOBPORTAL_METABOX_PREFIX . 'company_images', false);
                    $company_images  = (isset($company_img_arg) && is_array($company_img_arg) && count($company_img_arg) > 0) ? $company_img_arg[0] : '';
                    $company_images  = explode('|', $company_images);
                    $company_images  = array_unique($company_images);
                    if (!empty($company_images[0])) {
                        foreach ($company_images as $attach_id) {
                            echo '<div class="media-thumb-wrap">';
                            echo '<figure class="media-thumb">';
                            echo wp_get_attachment_image($attach_id, 'thumbnail');
                            echo '<div class="media-item-actions">';
                            if ($user_demo == 'yes') { ?>
                                <a class="btn-add-to-message" data-text="<?php echo esc_attr__('This is a "Demo" account so you not cant delete it', 'jobportal-framework'); ?>" href="#">
                                    <i class="far fa-trash-alt large"></i>
                                </a>
                            <?php } else {
                                echo '<a class="icon icon-gallery-delete" data-attachment-id="' . intval($attach_id) . '" href="javascript:void(0)">';
                                echo '<i class="far fa-trash-alt large"></i>';
                                echo '</a>';
                            }
                            echo '<input type="hidden" class="jobportal_gallery_ids" name="jobportal_gallery_ids[]" value="' . intval($attach_id) . '">';
                            echo '<span style="display: none;" class="icon icon-loader">';
                            echo '<i class="fal fa-spinner fa-spin large"></i>';
                            echo '</span>';
                            echo '</div>';
                            echo '</figure>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
            </div>
            <div id="jobportal_gallery_errors" class="errors-log"></div>
            <div class="jobportal-gallery-wrapper">
                <div class="jobportal-gallery-inner">
                    <div id="jobportal_gallery_container">
                        <button type="button" id="jobportal_select_gallery" class="btn btn-primary">
                            <i class="far fa-arrow-from-bottom large"></i>
                            <?php esc_html_e('Upload ', 'jobportal-framework'); ?>
                        </button>
                    </div>
                </div>
                <div class="field-note"><?php echo sprintf(__('Maximum file size: %s.', 'jobportal-framework'), $image_max_file_size); ?></div>
            </div>
        </div>
    </div>
</div>
