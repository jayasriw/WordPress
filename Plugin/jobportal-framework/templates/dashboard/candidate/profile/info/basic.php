<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $hide_candidate_fields, $candidate_data, $candidate_meta_data, $current_user;
$user_id = $current_user->ID;

$enable_candidate_language_multiple = jobportal_get_option('enable_candidate_language_multiple', '0');

$candidate_id = jobportal_get_post_id_candidate();

if (empty($candidate_id) || $candidate_id == 0) {
    $candidate_id = isset($_GET['candidate_id']) ? intval($_GET['candidate_id']) : 0;
}

if (empty($candidate_id) || $candidate_id == 0) {
    $candidate_posts = get_posts([
        'post_type' => 'candidate',
        'author' => $current_user->ID,
        'posts_per_page' => 1,
        'post_status' => ['publish', 'draft', 'pending']
    ]);

    if (!empty($candidate_posts)) {
        $candidate_id = $candidate_posts[0]->ID;
    }
}

if (empty($candidate_id) || $candidate_id == 0) {
    $new_candidate = wp_insert_post([
        'post_type' => 'candidate',
        'post_status' => 'draft',
        'post_author' => $current_user->ID,
        'post_title' => $current_user->display_name . ' - Candidate Profile'
    ]);

    if (!is_wp_error($new_candidate)) {
        $candidate_id = $new_candidate;
    }
}

$candidate_des = isset($candidate_data->post_content) ? $candidate_data->post_content : '';
$candidate_first_name = isset($candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_first_name']) ? $candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_first_name'][0] : '';
$candidate_last_name = isset($candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_last_name']) ? $candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_last_name'][0] : '';
$candidate_email = isset($candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_email']) ? $candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_email'][0] : '';
$candidate_current_position = isset($candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_current_position']) ? $candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_current_position'][0] : '';
$candidate_categories = isset($candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_categories']) ? $candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_categories'][0] : '';
$candidate_dob = isset($candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_dob']) ? $candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_dob'][0] : '';
$candidate_age = isset($candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_age']) ? $candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_age'][0] : '';
$candidate_gender = isset($candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_gender']) ? $candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_gender'][0] : '';
$candidate_languages = isset($candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_languages']) ? $candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_languages'][0] : '';
$candidate_qualification = isset($candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_qualification']) ? $candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_qualification'][0] : '';
$candidate_yoe = isset($candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_yoe']) ? $candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_yoe'][0] : '';
$candidate_salary_type = isset($candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_salary_type']) ? $candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_salary_type'][0] : '';
$candidate_offer_salary = isset($candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_offer_salary']) ? $candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_offer_salary'][0] : '';
$candidate_show_my_profile = isset($candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_show_my_profile']) ? $candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_show_my_profile'][0] : '';

$date_format = get_option('date_format');
$phone_code = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'phone_code', true);
$user_phone = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'author_mobile_number', true);

if ($user_phone) {
    $candidate_phone = $user_phone;
} else {
    $candidate_phone = isset($candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_phone']) ? $candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_phone'][0] : '';
}

$candidate_avatar_id = $user_id;
$candidate_avatar_url = get_the_author_meta('author_avatar_image_url', $user_id);
$candidate_cover_image_id = '';
$candidate_cover_image_url = '';

if ($candidate_id > 0 && isset($candidate_data->ID)) {
    $candidate_cover_image_id = get_post_thumbnail_id($candidate_data->ID);
    $candidate_cover_image_url = get_the_post_thumbnail_url($candidate_data->ID, 'full');
}

$image_max_file_size = jobportal_get_option('jobportal_image_max_file_size', '1000kb');

// Enqueue scripts
jobportal_get_thumbnail_enqueue();
jobportal_get_avatar_enqueue();

// Xử lý email (ưu tiên Mail)
$google_gmail = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'user-google-email', true);
if (!empty($google_gmail)) {
    $candidate_email = $google_gmail;
} else {
    $candidate_email = isset($candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_email']) ? $candidate_meta_data[JOBPORTAL_METABOX_PREFIX . 'candidate_email'][0] : '';
}
?>

<div class="candidate basic-info block-from">
    <h6><?php esc_html_e('Basic Information', 'jobportal-framework'); ?></h6>

    <input type="hidden" name="candidate_id" value="<?php echo esc_attr($candidate_id) ?>">

    <div class="jobportal-avatar-candidate">
        <?php if (!in_array('fields_candidate_avatar', $hide_candidate_fields)) : ?>
            <div class="candidate-fields-avatar jobportal-fields-avatar">
                <label for="jobportal_select_avatar"><?php esc_html_e('Your photo', 'jobportal-framework'); ?></label>
                <div class="form-field">
                    <div id="jobportal_avatar_errors" class="errors-log"></div>
                    <div id="jobportal_avatar_container" class="file-upload-block preview">
                        <div id="jobportal_avatar_view" data-image-id="<?php echo esc_attr($candidate_avatar_id); ?>" data-image-url="<?php if (!empty($candidate_avatar_url)) {
                                                                                                                            echo esc_url($candidate_avatar_url);
                                                                                                                        } ?>"></div>
                        <div id="jobportal_add_avatar">
                            <i class="far fa-arrow-from-bottom large"></i>
                            <p id="jobportal_drop_avatar">
                                <button type="button" id="jobportal_select_avatar"><?php esc_html_e('Upload', 'jobportal-framework') ?></button>
                            </p>
                        </div>
                        <input type="hidden" class="avatar_url form-control" name="author_avatar_image_url" value="<?php echo esc_attr($candidate_avatar_url); ?>" id="avatar_url">
                        <input type="hidden" class="avatar_id" name="author_avatar_image_id" value="<?php echo esc_attr($candidate_avatar_id); ?>" id="avatar_id" />
                    </div>
                </div>
                <div class="field-note"><?php echo sprintf(__('Maximum file size: %s.', 'jobportal-framework'), $image_max_file_size); ?></div>
            </div>
        <?php endif; ?>

        <?php if (!in_array('fields_candidate_thumbnail', $hide_candidate_fields)) : ?>
            <div class="candidate-fields-thumbnail jobportal-fields-thumbnail">
                <label for="jobportal_select_thumbnail"><?php esc_html_e('Cover image', 'jobportal-framework'); ?></label>
                <div class="form-field">
                    <div id="jobportal_thumbnail_errors" class="errors-log"></div>
                    <div id="jobportal_thumbnail_container" class="file-upload-block preview">
                        <div id="jobportal_thumbnail_view" data-image-id="<?php echo esc_attr($candidate_cover_image_id); ?>" data-image-url="<?php if (!empty($candidate_cover_image_url)) {
                                                                                                                                    echo esc_url($candidate_cover_image_url);
                                                                                                                                } ?>"></div>
                        <div id="jobportal_add_thumbnail">
                            <i class="far fa-arrow-from-bottom large"></i>
                            <p id="jobportal_drop_thumbnail">
                                <button type="button" id="jobportal_select_thumbnail"><?php esc_html_e('Click here', 'jobportal-framework') ?></button>
                                <?php esc_html_e(' or drop files to upload', 'jobportal-framework') ?>
                            </p>
                        </div>
                        <input type="hidden" class="thumbnail_url form-control" name="candidate_cover_image_url" value="<?php echo esc_attr($candidate_cover_image_url); ?>" id="thumbnail_url">
                        <input type="hidden" class="thumbnail_id" name="candidate_cover_image_id" value="<?php echo esc_attr($candidate_cover_image_id); ?>" id="thumbnail_id" />
                    </div>
                </div>
                <p class="jobportal-thumbnail-size"><?php esc_html_e('The cover image size should be max 1920 x 400px', 'jobportal-framework') ?></p>
            </div>
        <?php endif; ?>
    </div>

    <div class="row">
        <?php if (!in_array('fields_candidate_first_name', $hide_candidate_fields)) : ?>
            <div class="form-group col-md-6">
                <label for="user_firstname"><?php esc_html_e('First name', 'jobportal-framework') ?></label>
                <input class="point-mark" type="text" id="user_firstname" name="candidate_first_name" placeholder="<?php esc_attr_e('First name', 'jobportal-framework') ?>" value="<?php echo esc_attr($candidate_first_name); ?>">
            </div>
        <?php endif; ?>

        <?php if (!in_array('fields_candidate_last_name', $hide_candidate_fields)) : ?>
            <div class="form-group col-md-6">
                <label for="user_lastname"><?php esc_html_e('Last name', 'jobportal-framework') ?></label>
                <input class="point-mark" type="text" id="user_lastname" name="candidate_last_name" placeholder="<?php esc_attr_e('Last name', 'jobportal-framework') ?>" value="<?php echo esc_attr($candidate_last_name); ?>">
            </div>
        <?php endif; ?>

        <?php if (!in_array('fields_candidate_email_address', $hide_candidate_fields)) : ?>
            <div class="form-group col-md-6">
                <label for="user_email"><?php esc_html_e('Email address', 'jobportal-framework') ?></label>
                <input class="point-mark" type="email" id="user_email" name="candidate_email" placeholder="<?php esc_attr_e('Email', 'jobportal-framework') ?>" value="<?php echo esc_attr($candidate_email); ?>">
            </div>
        <?php endif; ?>

        <?php if (!in_array('fields_candidate_phone_number', $hide_candidate_fields)) : ?>
            <div class="form-group col-md-6">
                <label for="author_mobile_number"><?php esc_html_e('Phone number', 'jobportal-framework') ?></label>
                <div class="tel-group">
                    <select name="prefix_code" id="prefix_code" class="jobportal-select2 prefix-code">
                        <?php
                        $prefix_code = phone_prefix_code();
                        $default_phone = JobPortal_Helper::jobportal_get_option('default_phone_number');
                        $selected_prefix = JobPortal_Helper::get_prefix_key_from_phone($candidate_phone, $prefix_code, $default_phone);
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
                    <input class="point-mark" type="tel" id="author_mobile_number" name="candidate_phone"
                        data-prefix="<?php echo esc_attr($default_phone_code); ?>"
                        value="<?php echo esc_attr($input_value); ?>"
                        placeholder="<?php esc_attr_e('Enter phone', 'jobportal-framework') ?>"
                        pattern="\+[0-9]{8,12}"
                        required>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!in_array('fields_candidate_current_position', $hide_candidate_fields)) : ?>
            <div class="form-group col-md-6">
                <label for="candidate_current_position"><?php esc_html_e('Current Position', 'jobportal-framework') ?></label>
                <input class="point-mark" type="text" id="candidate_current_position" name="candidate_current_position" value="<?php echo esc_attr($candidate_current_position); ?>" placeholder="<?php esc_attr_e('Ex: UI/UX Designer', 'jobportal-framework'); ?>">
            </div>
        <?php endif; ?>

        <?php if (!in_array('fields_candidate_categories', $hide_candidate_fields)) : ?>
            <div class="form-group col-md-6">
                <label for="candidate_categories"><?php esc_html_e('Categories', 'jobportal-framework') ?></label>
                <div class="select2-field">
                    <?php if ($candidate_id > 0) : ?>
                        <?php
                        $taxonomy = 'candidate_categories';
                        $term_count = wp_count_terms($taxonomy, ['hide_empty' => false]);

                        if ($term_count <= 150): ?>
                            <select data-placeholder="<?php esc_attr_e('Select categories', 'jobportal-framework'); ?>"
                                class="point-mark jobportal-select2" name="candidate_categories" id="candidate_categories" data-taxonomy="<?php echo esc_attr($taxonomy); ?>">
                                <?php jobportal_get_taxonomy_by_post_id($candidate_id, $taxonomy, true); ?>
                            </select>
                        <?php else: ?>
                            <select data-placeholder="<?php esc_attr_e('Select categories', 'jobportal-framework'); ?>"
                                class="point-mark jobportal-ajax-select2" name="candidate_categories" id="candidate_categories" data-taxonomy="<?php echo esc_attr($taxonomy); ?>">
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
                    <?php else : ?>
                        <div class="alert alert-warning">
                            <?php if (!is_user_logged_in()) : ?>
                                <?php esc_html_e('Please log in to select categories.', 'jobportal-framework'); ?>
                            <?php else : ?>
                                <?php esc_html_e('Creating your candidate profile... Please refresh the page.', 'jobportal-framework'); ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!in_array('fields_candidate_description', $hide_candidate_fields)) : ?>
            <div class="form-group col-md-12">
                <label for="candidate_des"><?php esc_html_e('Description', 'jobportal-framework') ?></label>
                <?php
                $content = $candidate_des;
                $editor_id = 'candidate_des';
                $settings = array(
                    'wpautop' => true,
                    'media_buttons' => false,
                    'textarea_name' => $editor_id,
                    'textarea_rows' => get_option('default_post_edit_rows', 8),
                    'tabindex' => '',
                    'editor_css' => '',
                    'editor_class' => '',
                    'teeny' => false,
                    'dfw' => false,
                    'tinymce' => true,
                    'quicktags' => true
                );
                wp_editor(html_entity_decode(stripcslashes($content)), $editor_id, $settings);
                ?>
            </div>
        <?php endif; ?>

        <?php if (!in_array('fields_candidate_date_of_birth', $hide_candidate_fields)) : ?>
            <div class="form-group col-md-6">
                <label for="candidate_dob"><?php esc_html_e('Date of Birth', 'jobportal-framework') ?></label>
                <input class="point-mark" type="date" placeholder="<?php echo $date_format; ?>" id="candidate_dob" name="candidate_dob" value="<?php echo esc_attr($candidate_dob); ?>" max="<?php echo date('Y-m-d'); ?>">
            </div>
        <?php endif; ?>

        <?php if (!in_array('fields_candidate_age', $hide_candidate_fields)) : ?>
            <div class="form-group col-md-6">
                <label for="candidate_age"><?php esc_html_e('Age', 'jobportal-framework') ?></label>
                <div class="select2-field">
                    <select class="point-mark jobportal-select2" name="candidate_age" id="candidate_age">
                        <?php jobportal_get_taxonomy_by_post_id($candidate_id, 'candidate_ages', true, false, true, 'candidate_ages_order'); ?>
                    </select>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!in_array('fields_candidate_gender', $hide_candidate_fields)) : ?>
            <div class="form-group col-md-6">
                <label for="candidate_gender"><?php esc_html_e('Gender', 'jobportal-framework') ?></label>
                <div class="select2-field">
                    <select class="point-mark jobportal-select2" name="candidate_gender" id="candidate_gender">
                        <?php jobportal_get_taxonomy_by_post_id($candidate_id, 'candidate_gender', true, false, true, 'candidate_gender_order'); ?>
                    </select>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!in_array('fields_closing_languages', $hide_candidate_fields)) : ?>
            <?php if ($enable_candidate_language_multiple === '1') : ?>
                <div class="form-group col-md-6">
                    <label for="candidate_languages"><?php esc_html_e('Languages', 'jobportal-framework') ?></label>
                    <div class="form-select">
                        <div class="select2-field select2-multiple">
                            <select data-placeholder="<?php esc_attr_e('Select languages', 'jobportal-framework'); ?>" multiple="multiple" class="jobportal-select2 point-mark" name="candidate_languages" id="candidate_languages">
                                <?php jobportal_get_taxonomy_by_post_id($candidate_id, 'candidate_languages', false, false, true, 'candidate_languages_order'); ?>
                            </select>
                        </div>
                        <i class="fas fa-angle-down"></i>
                    </div>
                </div>
            <?php else : ?>
                <div class="form-group col-md-6">
                    <label for="candidate_languages"><?php esc_html_e('Languages', 'jobportal-framework') ?></label>
                    <div class="select2-field">
                        <select class="jobportal-select2 point-mark" name="candidate_languages" id="candidate_languages">
                            <?php jobportal_get_taxonomy_by_post_id($candidate_id, 'candidate_languages', true, false, true, 'candidate_languages_order'); ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!in_array('fields_candidate_qualification', $hide_candidate_fields)) : ?>
            <div class="form-group col-md-6">
                <label for="candidate_qualification"><?php esc_html_e('Qualification', 'jobportal-framework') ?></label>
                <div class="select2-field">
                    <select class="point-mark jobportal-select2" name="candidate_qualification" id="candidate_qualification">
                        <?php jobportal_get_taxonomy_by_post_id($candidate_id, 'candidate_qualification', true, false, true, 'candidate_qualification_order'); ?>
                    </select>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!in_array('fields_candidate_experience', $hide_candidate_fields)) : ?>
            <div class="form-group col-md-6">
                <label for="candidate_yoe"><?php esc_html_e('Years of Experience', 'jobportal-framework') ?></label>
                <div class="select2-field">
                    <select class="point-mark jobportal-select2" name="candidate_yoe" id="candidate_yoe">
                        <?php jobportal_get_taxonomy_by_post_id($candidate_id, 'candidate_yoe', true, false, true, 'candidate_experience_order'); ?>
                    </select>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!in_array('fields_candidate_salary', $hide_candidate_fields)) : ?>
            <div class="form-group col-md-6">
                <label for="candidate_offer_salary"><?php esc_html_e('Offer Salary', 'jobportal-framework') ?></label>
                <input class="point-mark" type="number" id="candidate_offer_salary" name="candidate_offer_salary" value="<?php echo esc_attr($candidate_offer_salary); ?>" placeholder="<?php esc_html_e('Ex: 100', 'jobportal-framework') ?>">
            </div>
        <?php endif; ?>

        <?php if (!in_array('fields_candidate_salary', $hide_candidate_fields)) : ?>
            <div class="form-group col-md-6">
                <label for="candidate_salary_type"><?php esc_html_e('Salary type', 'jobportal-framework'); ?></label>
                <div class="select2-field">
                    <select name="candidate_salary_type" id="candidate_salary_type" class="jobportal-select2 point-mark">
                        <option <?php selected($candidate_salary_type, ''); ?> value=""><?php esc_html_e('None', 'jobportal-framework'); ?></option>
                        <option <?php selected($candidate_salary_type, 'hr'); ?> value="hr"><?php esc_html_e('Hourly', 'jobportal-framework'); ?></option>
                        <option <?php selected($candidate_salary_type, 'day'); ?> value="day"><?php esc_html_e('Daily', 'jobportal-framework'); ?></option>
                        <option <?php selected($candidate_salary_type, 'month'); ?> value="month"><?php esc_html_e('Monthly', 'jobportal-framework'); ?></option>
                        <option <?php selected($candidate_salary_type, 'year'); ?> value="year"><?php esc_html_e('Yearly', 'jobportal-framework'); ?></option>
                    </select>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!in_array('fields_candidate_salary', $hide_candidate_fields)) : ?>
            <div class="form-group col-md-6">
                <label for="candidate_currency_display"><?php esc_html_e('Currency', 'jobportal-framework'); ?></label>
                <div class="select2-field">
                    <select name="candidate_currency_type" id="candidate_currency_display" class="jobportal-select2 point-mark">
                        <?php jobportal_get_select_currency_type(true); ?>
                    </select>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
