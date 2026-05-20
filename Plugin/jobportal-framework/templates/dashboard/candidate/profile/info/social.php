<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'social-network');

global $candidate_data, $candidate_meta_data;
$candidate_twitter = isset($candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_twitter']) ? $candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_twitter'][0] : '';
$candidate_linkedin = isset($candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_linkedin']) ? $candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_linkedin'][0] : '';
$candidate_facebook = isset($candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_facebook']) ? $candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_facebook'][0] : '';
$candidate_instagram = isset($candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_instagram']) ? $candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_instagram'][0] : '';
$enable_social_twitter = jobportal_get_option('enable_social_twitter', '1');
$enable_social_linkedin = jobportal_get_option('enable_social_linkedin', '1');
$enable_social_facebook = jobportal_get_option('enable_social_facebook', '1');
$enable_social_instagram = jobportal_get_option('enable_social_instagram', '1');
?>

<div class="social-network block-from" id="candidate-submit-social">
    <h6><?php esc_html_e('Social Network', 'jobportal-framework') ?></h6>
    <div class="row jobportal-social-network">
        <?php if ($enable_social_twitter == 1) : ?>
            <div class="form-group col-12 col-sm-6">
                <label for="candidate_twitter"><?php esc_html_e('Twitter', 'jobportal-framework') ?></label>
                <input
                    type="url"
                    name="candidate_twitter"
                    id="candidate_twitter"
                    class="point-mark"
                    value="<?php echo esc_attr($candidate_twitter) ?>"
                    placeholder="<?php esc_attr_e('twitter.com/candidate', 'jobportal-framework') ?>">
            </div>
        <?php endif; ?>
        <?php if ($enable_social_linkedin == 1) : ?>
            <div class="form-group col-12 col-sm-6">
                <label for="candidate_linkedin"><?php esc_html_e('Linkedin', 'jobportal-framework') ?></label>
                <input type="url" name="candidate_linkedin" id="candidate_linkedin"
                    class="point-mark"
                    value="<?php echo esc_attr($candidate_linkedin) ?>"
                    placeholder="<?php esc_attr_e('linkedin.com/candidate', 'jobportal-framework') ?>">
            </div>
        <?php endif; ?>
        <?php if ($enable_social_facebook == 1) : ?>
            <div class="form-group col-12 col-sm-6">
                <label for="candidate_facebook"><?php esc_html_e('Facebook', 'jobportal-framework') ?></label>
                <input type="url" name="candidate_facebook" id="candidate_facebook"
                    class="point-mark"
                    value="<?php echo esc_attr($candidate_facebook) ?>"
                    placeholder="<?php esc_attr_e('facebook.com/candidate', 'jobportal-framework') ?>">
            </div>
        <?php endif; ?>
        <?php if ($enable_social_instagram == 1) : ?>
            <div class="form-group col-12 col-sm-6">
                <label for="candidate_instagram"><?php esc_html_e('Instagram', 'jobportal-framework') ?></label>
                <input type="url" name="candidate_instagram" id="candidate_instagram"
                    class="point-mark"
                    value="<?php echo esc_attr($candidate_instagram) ?>"
                    placeholder="<?php esc_attr_e('instagram.com/candidate', 'jobportal-framework') ?>">
            </div>
        <?php endif; ?>
        <?php $jobportal_social_fields = jobportal_get_option('jobportal_social_fields');
        if (is_array($jobportal_social_fields) && !empty($jobportal_social_fields)) {
            foreach ($jobportal_social_fields as $key => $value) {
                $candidate_social_val = isset($candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_' . $value['social_name']]) ? $candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_' . $value['social_name']][0] : '';
        ?>
                <div class="form-group col-12 col-sm-6">
                    <label for="candidate_<?php echo esc_attr($value['social_name']); ?>"><?php echo esc_html($value['social_name']); ?></label>
                    <input class="candidate-social-input" type="url" name="candidate_<?php echo esc_html($value['social_name']); ?>" id="candidate_<?php echo esc_html($value['social_name']); ?>"
                        value="<?php echo esc_attr($candidate_social_val) ?>"
                        placeholder="<?php esc_attr_e($value['social_name'] . '.com/candidate', 'jobportal-framework') ?>">
                </div>
        <?php }
        } ?>
    </div>

    <div class="field-social-clone">
        <div class="clone-wrap">
            <div class="soical-remove-inner">
                <a href="#" class="remove-social"><i class="fas fa-times"></i></a>
                <span><?php esc_html_e('Network', 'jobportal-framework') ?><span class="number-network"></span></span>
            </div>
            <div class="row field-wrap">
                <div class="form-group col-12 col-sm-6">
                    <label for="candidate_social_name_new"><?php esc_html_e('Name', 'jobportal-framework') ?></label>
                    <input type="text" id="candidate_social_name_new" name="candidate_social_name[]"
                        placeholder="<?php esc_attr_e('Candidate', 'jobportal-framework') ?>">
                </div>
                <div class="form-group col-12 col-sm-6">
                    <label for="candidate_social_url_new"><?php esc_html_e('Url', 'jobportal-framework') ?></label>
                    <input type="url" id="candidate_social_url_new" name="candidate_social_url[]"
                        placeholder="<?php esc_attr_e('url.com/candidate', 'jobportal-framework') ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="add-social-list">
        <?php
        $candidate_social_tab = get_post_meta($candidate_data->ID, JOBPORTAL_METABOX_PREFIX . 'candidate_social_tabs', false);
        $i = 0;
        if (is_array($candidate_social_tab)) {
            foreach ($candidate_social_tab as $social) {
                if (is_array($social)) {
                    foreach ($social as $k1 => $social_v1) {
        ?>

                        <div class="clone-wrap">
                            <div class="soical-remove-inner">
                                <a href="#" class="remove-social"><i class="fas fa-times"></i></a>
                                <span><?php esc_html_e('Network', 'jobportal-framework') ?><span
                                        class="number-network"></span></span>
                            </div>
                            <div class="row field-wrap">
                                <div class="col col-md-6">
                                    <label for="candidate_social_name_<?php echo esc_attr($i); ?>"><?php esc_html_e('Name', 'jobportal-framework') ?></label>
                                    <input type="text" id="candidate_social_name_<?php echo esc_attr($i); ?>" name="candidate_social_name[]"
                                        value="<?php echo esc_attr($social_v1[JOBPORTAL_METABOX_PREFIX . 'candidate_social_name']); ?>"
                                        placeholder="<?php esc_attr_e('Candidate', 'jobportal-framework') ?>">
                                </div>
                                <div class="col col-md-6">
                                    <label for="candidate_social_url_<?php echo esc_attr($i); ?>"><?php esc_html_e('Url', 'jobportal-framework') ?></label>
                                    <input type="url" id="candidate_social_url_<?php echo esc_attr($i); ?>" name="candidate_social_url[]"
                                        value="<?php echo esc_attr($social_v1[JOBPORTAL_METABOX_PREFIX . 'candidate_social_url']); ?>"
                                        placeholder="<?php esc_attr_e('url.com/candidate', 'jobportal-framework') ?>">
                                </div>
                            </div>
                        </div>
        <?php }
                    $i++;
                }
            }
        } ?>
    </div>
    <a class="jobportal-button button-link add-social" href="#addsocial">
        <span class="jobportal-button-icon"><i class="far fa-chevron-down"></i></span>
        <?php esc_html_e('Add more', 'jobportal-framework') ?>
    </a>
</div>
