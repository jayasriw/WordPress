<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $hide_candidate_fields, $current_user, $candidate_data;
$user_id = $current_user->ID;
$user_demo = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'user_demo', $user_id);
$image_max_file_size = jobportal_get_option('jobportal_image_max_file_size', '1000kb');
jobportal_get_gallery_enqueue();
?>
<div class="jobportal-candidate-galleries jobportal-upload-gallery block-from">
    <h6><?php esc_html_e('Gallery', 'jobportal-framework') ?></h6>
    <div class="candidate-fields-media jobportal-fields-gallery">
        <label><?php esc_html_e('Image', 'jobportal-framework'); ?></label>
        <div class="candidate-fields candidate-fields-file candidate-gallery-image">
            <div class="field-media-wrapper">
                <div class="media-gallery">
                    <div id="jobportal_gallery_thumbs">
                        <?php
                        $candidate_img_arg = get_post_meta($candidate_data->ID, JOBPORTAL_METABOX_PREFIX . 'candidate_galleries', false);
                        $candidate_images = (isset($candidate_img_arg) && is_array($candidate_img_arg) && count($candidate_img_arg) > 0) ? $candidate_img_arg[0] : '';
                        $candidate_images = explode('|', $candidate_images);
                        $candidate_images = array_unique($candidate_images);
                        if (!empty($candidate_images[0])) {
                            foreach ($candidate_images as $attach_id) {
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
</div>
