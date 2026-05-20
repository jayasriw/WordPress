<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$cv_file = civi_get_option('civi-cv-type');
$cv_max_file_size = civi_get_option('civi_image_max_file_size', '1000kb');
$text = '<i class="far fa-arrow-from-bottom large"></i> ' . esc_attr(sprintf(esc_html__('Upload CV (%s)', 'civi-framework'), $cv_file));
$upload_nonce = wp_create_nonce('civi_thumbnail_allow_upload');
$url = CIVI_AJAX_URL .  '?action=civi_thumbnail_upload_ajax&nonce=' . esc_attr($upload_nonce);

wp_enqueue_script('plupload');
wp_enqueue_script('jquery-validate');
wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'upload-cv');
wp_localize_script(
    CIVI_PLUGIN_PREFIX . 'upload-cv',
    'civi_upload_cv_vars',
    array(
        'ajax_url'    => CIVI_AJAX_URL,
        'title'   => esc_html__('Valid file formats', 'civi-framework'),
        'cv_file' => $cv_file,
        'cv_max_file_size' => $cv_max_file_size,
        'upload_nonce' => $upload_nonce,
        'url' => $url,
        'text' => $text,
    )
);

global $current_user;
$user_id = $current_user->ID;
$candidate_id =  $fileUrl = '';
if (in_array('civi_user_candidate', (array)$current_user->roles)) {
    $args_candidate = array(
        'post_type' => 'candidate',
        'author' => $user_id,
    );
    $query = new WP_Query($args_candidate);
    $candidate_id = $query->post->ID;
}
$jobs_id = get_the_ID();
$jobs_select_apply = !empty(get_post_meta($jobs_id, CIVI_METABOX_PREFIX . 'jobs_select_apply')) ? get_post_meta($jobs_id, CIVI_METABOX_PREFIX . 'jobs_select_apply')[0] : '';

$candidate_phone = $candidate_email = '';
if (!empty($candidate_id)) {
    $candidate_resume = get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_resume_id_list', false);
    $candidate_resume = !empty($candidate_resume) ? $candidate_resume[0] : '';
    $fileName = basename(get_attached_file($candidate_resume));
    $candidate_email = !empty(get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_email')) ? get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_email')[0] : '';
    $candidate_phone = !empty(get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_phone')) ? get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_phone')[0] : '';
    if (!empty(wp_get_attachment_url($candidate_resume))) {
        $fileUrl = wp_get_attachment_url($candidate_resume);
    }
}

$show_field_jobs_apply = civi_get_option('show_field_jobs_apply');
$candidate_meta_data = get_post_custom($candidate_id);
$candidate_current_position = isset($candidate_meta_data[CIVI_METABOX_PREFIX . 'candidate_current_position']) ? $candidate_meta_data[CIVI_METABOX_PREFIX . 'candidate_current_position'][0] : '';
$candidate_categories = isset($candidate_meta_data[CIVI_METABOX_PREFIX . 'candidate_categories']) ? $candidate_meta_data[CIVI_METABOX_PREFIX . 'candidate_categories'][0] : '';
$candidate_dob = isset($candidate_meta_data[CIVI_METABOX_PREFIX . 'candidate_dob']) ? $candidate_meta_data[CIVI_METABOX_PREFIX . 'candidate_dob'][0] : '';
$candidate_age = isset($candidate_meta_data[CIVI_METABOX_PREFIX . 'candidate_age']) ? $candidate_meta_data[CIVI_METABOX_PREFIX . 'candidate_age'][0] : '';
$candidate_gender = isset($candidate_meta_data[CIVI_METABOX_PREFIX . 'candidate_gender']) ? $candidate_meta_data[CIVI_METABOX_PREFIX . 'candidate_gender'][0] : '';
$candidate_languages = isset($candidate_meta_data[CIVI_METABOX_PREFIX . 'candidate_languages']) ? $candidate_meta_data[CIVI_METABOX_PREFIX . 'candidate_languages'][0] : '';
$candidate_qualification = isset($candidate_meta_data[CIVI_METABOX_PREFIX . 'candidate_qualification']) ? $candidate_meta_data[CIVI_METABOX_PREFIX . 'candidate_qualification'][0] : '';
$candidate_yoe = isset($candidate_meta_data[CIVI_METABOX_PREFIX . 'candidate_yoe']) ? $candidate_meta_data[CIVI_METABOX_PREFIX . 'candidate_yoe'][0] : '';
if (empty($show_field_jobs_apply)) {
    $col = 'col-md-12';
    $max_width = '';
} else {
    $col = 'col-md-6';
    $max_width = '550px';
}
?>

<form action="#" method="post" class="form-popup form-popup-apply" id="civi_form_apply_jobs" enctype="multipart/form-data">
    <div class="bg-overlay"></div>
    <div class="apply-popup custom-scrollbar" style="max-width: <?php echo $max_width; ?>">
        <a href="#" class="btn-close"><i class="far fa-times"></i></a>
        <h5><?php esc_html_e('Apply for this job', 'civi-framework') ?></h5>
        <div class="row">
            <div class="form-group <?php echo $col; ?>">
                <label for="apply_email"><?php esc_html_e('Email address', 'civi-framework') ?></label>
                <input type="email" id="apply_email" name="apply_email" placeholder="<?php esc_attr_e('Enter email', 'civi-framework') ?>" value="<?php echo esc_attr($candidate_email) ?>">
            </div>
            <div class="form-group <?php echo $col; ?>">
                <?php
                $default_phone_number = civi_get_option('default_phone_number');
                ?>
                <label for="apply_phone"><?php esc_html_e('Phone', 'civi-framework') ?></label>
                <div class="tel-group">
                    <select name="prefix_code" class="civi-select2 prefix-code">
                        <?php
                        $prefix_code = phone_prefix_code();
                        $default_phone = Civi_Helper::civi_get_option('default_phone_number');
                        $selected_prefix = Civi_Helper::get_prefix_key_from_phone($candidate_phone, $prefix_code, $default_phone);
                        foreach ($prefix_code as $key => $value) {
                            $selected = ($key == $selected_prefix) ? 'selected' : '';
                            echo '<option value="' . esc_attr($key) . '" data-dial-code="' . esc_attr($value['code']) . '" ' . $selected . '>' . esc_html($value['name']) . ' (' . esc_html($value['code']) . ')</option>';
                        }
                        ?>
                    </select>
                    <?php
                    $default_phone_code = isset($prefix_code[$selected_prefix]) ? $prefix_code[$selected_prefix]['code'] : (isset($prefix_code[$default_phone]) ? $prefix_code[$default_phone]['code'] : '');
                    $input_value = !empty($candidate_phone) ? preg_replace('/^' . preg_quote($default_phone_code, '/') . '0+/', $default_phone_code, preg_replace('/[^0-9+]/', '', $candidate_phone)) : $default_phone_code;
                    ?>
                    <input type="tel" id="apply_phone" name="apply_phone"
                        data-prefix="<?php echo esc_attr($default_phone_code); ?>"
                        value="<?php echo esc_attr($input_value); ?>"
                        placeholder="<?php esc_attr_e('Enter phone', 'civi-framework') ?>"
                        pattern="\+[0-9]{8,12}"
                        required>
                </div>
            </div>

            <?php if (!empty($show_field_jobs_apply)) : ?>
                <?php if (in_array('position', $show_field_jobs_apply)) : ?>
                    <div class="form-group col-md-6">
                        <label for="candidate_current_position"><?php esc_html_e('Current Position', 'civi-framework') ?></label>
                        <input class="point-mark" type="text" id="candidate_current_position" name="candidate_current_position" value="<?php echo esc_attr($candidate_current_position); ?>" placeholder="<?php esc_attr_e('Ex: UI/UX Designer', 'civi-framework'); ?>">
                    </div>
                <?php endif; ?>
                <?php if (in_array('categories', $show_field_jobs_apply)) : ?>
                    <div class="form-group col-md-6">
                        <label for="candidate_categories"><?php esc_html_e('Categories', 'civi-framework') ?></label>
                        <div class="select2-field">
                            <?php
                            $taxonomy = 'candidate_categories';
                            $candidate_id = !empty($candidate_id) && is_numeric($candidate_id) ? $candidate_id : 0;
                            $term_count = wp_count_terms($taxonomy, ['hide_empty' => false]);

                            if ($term_count <= 150): ?>
                                <select data-placeholder="<?php esc_attr_e('Select categories', 'civi-framework'); ?>"
                                    class="point-mark civi-select2" name="candidate_categories" id="candidate_categories" data-taxonomy="<?php echo esc_attr($taxonomy); ?>">
                                    <?php civi_get_taxonomy_by_post_id($candidate_id, $taxonomy, true); ?>
                                </select>
                            <?php else: ?>
                                <select data-placeholder="<?php esc_attr_e('Select categories', 'civi-framework'); ?>"
                                    class="point-mark civi-ajax-select2" name="candidate_categories" id="candidate_categories" data-taxonomy="<?php echo esc_attr($taxonomy); ?>">
                                    <?php
                                    $selected_categories = get_the_terms($candidate_id, $taxonomy);
                                    if (!is_wp_error($selected_categories) && !empty($selected_categories)) {
                                        foreach ($selected_categories as $term) {
                                            echo '<option value="' . esc_attr($term->term_id) . '" selected>' . esc_html($term->name) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (in_array('date', $show_field_jobs_apply)) : ?>
                    <div class="form-group col-md-6">
                        <label for="candidate_dob"><?php esc_html_e('Date of Birth', 'civi-framework') ?></label>
                        <input class="point-mark" type="date" id="candidate_dob" name="candidate_dob" value="<?php echo esc_attr($candidate_dob); ?>" max="<?php echo date('Y-m-d'); ?>">
                    </div>
                <?php endif; ?>
                <?php if (in_array('age', $show_field_jobs_apply)) : ?>
                    <div class="form-group col-md-6">
                        <label for="candidate_age"><?php esc_html_e('Age', 'civi-framework') ?></label>
                        <div class="select2-field">
                            <select class="point-mark civi-select2" name="candidate_age" id="candidate_age">
                                <?php civi_get_taxonomy_by_post_id($candidate_id, 'candidate_ages', true, false, true, 'candidate_ages_order'); ?>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (in_array('gender', $show_field_jobs_apply)) : ?>
                    <div class="form-group col-md-6">
                        <label for="candidate_gender"><?php esc_html_e('Gender', 'civi-framework') ?></label>
                        <div class="select2-field">
                            <select class="point-mark civi-select2" name="candidate_gender" id="candidate_gender">
                                <?php civi_get_taxonomy_by_post_id($candidate_id, 'candidate_gender', true, false, true, 'candidate_gender_order'); ?>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (in_array('languages', $show_field_jobs_apply)) : ?>
                    <div class="form-group col-md-6">
                        <label for="candidate_languages"><?php esc_html_e('Languages', 'civi-framework') ?></label>
                        <div class="select2-field">
                            <select class="civi-select2 point-mark" name="candidate_languages" id="candidate_languages">
                                <?php civi_get_taxonomy_by_post_id($candidate_id, 'candidate_languages', true, false, true, 'candidate_languages_order'); ?>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (in_array('qualification', $show_field_jobs_apply)) : ?>
                    <div class="form-group col-md-6">
                        <label for="candidate_qualification"><?php esc_html_e('Qualification', 'civi-framework') ?></label>
                        <div class="select2-field">
                            <select class="point-mark civi-select2" name="candidate_qualification" id="candidate_qualification">
                                <?php civi_get_taxonomy_by_post_id($candidate_id, 'candidate_qualification', true, false, true, 'candidate_qualification_order'); ?>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (in_array('experience', $show_field_jobs_apply)) : ?>
                    <div class="form-group col-md-6">
                        <label for="candidate_yoe"><?php esc_html_e('Years of Experience', 'civi-framework') ?></label>
                        <div class="select2-field">
                            <select class="point-mark civi-select2" name="candidate_yoe" id="candidate_yoe">
                                <?php civi_get_taxonomy_by_post_id($candidate_id, 'candidate_yoe', true, false, true, 'candidate_experience_order'); ?>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="form-group col-md-12">
                <label for="apply_message"><?php esc_html_e('Message', 'civi-framework') ?></label>
                <textarea id="apply_message" name="apply_message" rows="4" cols="50"></textarea>
            </div>
            <div class="form-group col-md-12 civi-upload-cv">
                <div class="form-field">
                    <div id="cv_errors_log" class="errors-log"></div>
                    <div id="civi_cv_plupload_container" class="file-upload-block preview">
                        <div class="civi_cv_file civi_add-cv">
                            <p id="civi_drop_cv">
                                <?php if (!empty($fileName)) { ?>
                                    <button type="button" id="civi_select_cv">
                                        <i class="far fa-arrow-from-bottom large"></i>
                                        <?php esc_html_e($fileName); ?>
                                    </button>
                                <?php } else { ?>
                                    <button type="button" id="civi_select_cv">
                                        <i class="far fa-arrow-from-bottom large"></i>
                                        <?php echo esc_attr(sprintf(esc_html__('Upload CV (%s)', 'civi-framework'), $cv_file)); ?>
                                    </button>
                                <?php } ?>
                            </p>
                        </div>
                        <input type="hidden" class="cv_url form-control" name="jobs_cv_url" value="<?php echo esc_attr($fileUrl) ?>" id="cv_url">
                        <input type="hidden" class="type_apply form-control" name="type_apply" value="<?php esc_html_e($jobs_select_apply); ?>" id="type_apply">
                    </div>
                </div>
            </div>
        </div>
        <div class="message_error"></div>
        <div class="button-wrapper">
            <a href="#" class="civi-button button-outline button-block button-cancel"><?php esc_html_e('Cancel', 'civi-framework'); ?></a>
            <button type="submit" class="civi-button button-block btn-submit-apply-jobs" id="btn-apply-jobs-<?php echo $jobs_id ?>" data-jobs_id="<?php echo $jobs_id ?>">
                <?php esc_html_e('Apply Jobs', 'civi-framework'); ?>
                <span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
            </button>
        </div>
    </div>
</form>
