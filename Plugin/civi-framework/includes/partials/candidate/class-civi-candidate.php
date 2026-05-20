<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Civi_Candidate')) {
    /**
     * Class Civi_Candidate
     */
    class Civi_Candidate
    {

        /**
         * Jobs breadcrumb
         */
        public function civi_candidate_breadcrumb()
        { ?>
            <div class="container container-breadcrumb">
                <?php get_template_part('templates/global/breadcrumb'); ?>
            </div>
<?php }

        public function civi_set_candidate_view_date()
        {
            $id = get_the_ID();
            $today = date('Y-m-d', time());
            $views_date = get_post_meta($id, 'civi_view_candidate_date', true);
            if ($views_date != '' || is_array($views_date)) {
                if (!isset($views_date[$today])) {
                    if (count($views_date) > 60) {
                        array_shift($views_date);
                    }
                    $views_date[$today] = 1;
                } else {
                    $views_date[$today] = intval($views_date[$today]) + 1;
                }
            } else {
                $views_date = array();
                $views_date[$today] = 1;
            }
            update_post_meta($id, 'civi_view_candidate_date', $views_date);
        }

        /**
         * upload candidate img
         */
        public function upload_candidate_attachment_ajax()
        {

            $nonce = isset($_REQUEST['nonce']) ? civi_clean(wp_unslash($_REQUEST['nonce'])) : '';
            if (!wp_verify_nonce($nonce, 'candidate_allow_upload')) {
                $ajax_response = array('success' => false, 'reason' => esc_html__('Security check failed!', 'civi-framework'));
                echo json_encode($ajax_response);
                wp_die();
            }

            if (!is_user_logged_in()) {
                $ajax_response = array('success' => false, 'reason' => esc_html__('You must be logged in.', 'civi-framework'));
                echo json_encode($ajax_response);
                wp_die();
            }

            $submitted_file = $_FILES['candidate_upload_file']; // WPCS: sanitization ok, input var ok.

            $uploaded_image = wp_handle_upload($submitted_file, array('test_form' => false));
            if (isset($uploaded_image['file'])) {
                $file_name = basename($submitted_file['name']);
                $file_type = wp_check_filetype($uploaded_image['file']);
                $attachment_details = array(
                    'guid'           => $uploaded_image['url'],
                    'post_mime_type' => $file_type['type'],
                    'post_title'     => preg_replace('/\.[^.]+$/', '', basename($file_name)),
                    'post_content'   => '',
                    'post_status'    => 'inherit'
                );

                $attach_id     = wp_insert_attachment($attachment_details, $uploaded_image['file']);
                $attach_data   = wp_generate_attachment_metadata($attach_id, $uploaded_image['file']);
                wp_update_attachment_metadata($attach_id, $attach_data);
                $thumbnail_url = wp_get_attachment_thumb_url($attach_id);
                $fullimage_url = wp_get_attachment_image_src($attach_id, 'full');

                $ajax_response = array(
                    'success'         => true,
                    'url'             => $thumbnail_url,
                    'attachment_id'   => $attach_id,
                    'attachment_name' => $file_name,
                    'full_image'      => $fullimage_url[0]
                );
                echo json_encode($ajax_response);
                wp_die();
            } else {
                $ajax_response = array('success' => false, 'reason' => esc_html__('Image upload failed!', 'civi-framework'));
                echo json_encode($ajax_response);
                wp_die();
            }
        }

        /**
         * Remove candidate img
         */
        public function remove_candidate_attachment_ajax()
        {
            $nonce   = isset($_POST['removeNonce']) ? civi_clean(wp_unslash($_POST['removeNonce'])) : '';
            $user_id = isset($_POST['user_id']) ? civi_clean(wp_unslash($_POST['user_id'])) : '';
            if (!wp_verify_nonce($nonce, 'candidate_allow_upload')) {
                $json_response = array(
                    'success' => false,
                    'reason'  => esc_html__('Security check fails', 'civi-framework')
                );
                echo json_encode($json_response);
                wp_die();
            }

            if (!is_user_logged_in()) {
                $json_response = array(
                    'success' => false,
                    'reason'  => esc_html__('You must be logged in.', 'civi-framework')
                );
                echo json_encode($json_response);
                wp_die();
            }

            $success = false;
            if (isset($_POST['candidate_id']) && isset($_POST['attachment_id'])) {
                $candidate_id  = absint(wp_unslash($_POST['candidate_id']));
                $attachment_id = absint(wp_unslash($_POST['attachment_id']));
                $type          = isset($_POST['type']) ? civi_clean(wp_unslash($_POST['type'])) : '';

                if ($candidate_id > 0) {
                    if ($type === 'gallery') {
                        $candidate_gallery = get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_gallery', true);

                        $found_img_key = array_search($attachment_id, $candidate_gallery);

                        if ($found_img_key !== false) {
                            unset($candidate_gallery[$found_img_key]);
                            update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_gallery', $candidate_gallery);
                        }
                    } else {
                        delete_post_meta($candidate_id, CIVI_METABOX_PREFIX . '_thumbnail_id', $attachment_id);
                    }

                    $marked_for_deletion = get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'marked_for_deletion', true);
                    if (!is_array($marked_for_deletion)) {
                        $marked_for_deletion = array();
                    }
                    if (!in_array($attachment_id, $marked_for_deletion)) {
                        $marked_for_deletion[] = $attachment_id;
                        update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'marked_for_deletion', $marked_for_deletion);
                    }

                    $success = true;
                }
            }
            if ($user_id) {
                update_user_meta($user_id, 'author_avatar_image_url', CIVI_THEME_URI . '/assets/images/default-user-image.png');
            }
            $ajax_response = array(
                'success' => $success,
                'url'     => get_the_author_meta('author_avatar_image_url', $user_id),
            );

            echo json_encode($ajax_response);
            wp_die();
        }

        /**
         * Candidate submit
         */
        public function candidate_submit_ajax()
        {
            check_ajax_referer('candidate_submit_ajax_nonce', 'civi_security_candidate_submit');

            if (!is_user_logged_in()) {
                echo json_encode(array('success' => false, 'message' => esc_html__('You must be logged in.', 'civi-framework')));
                wp_die();
            }
            $candidate_id                     = isset($_REQUEST['candidate_id']) ? civi_clean(wp_unslash($_REQUEST['candidate_id'])) : '';

            $candidate_first_name             = isset($_REQUEST['candidate_first_name']) ? civi_clean(wp_unslash($_REQUEST['candidate_first_name'])) : '';
            $candidate_last_name              = isset($_REQUEST['candidate_last_name']) ? civi_clean(wp_unslash($_REQUEST['candidate_last_name'])) : '';
            $candidate_email                  = isset($_REQUEST['candidate_email']) ? civi_clean(wp_unslash($_REQUEST['candidate_email'])) : '';
            $candidate_phone                  = isset($_REQUEST['candidate_phone']) ? civi_clean(wp_unslash($_REQUEST['candidate_phone'])) : '';
            $candidate_phone_code             = isset($_REQUEST['candidate_phone_code']) ? civi_clean(wp_unslash($_REQUEST['candidate_phone_code'])) : '';
            $candidate_current_position       = isset($_REQUEST['candidate_current_position']) ? civi_clean(wp_unslash($_REQUEST['candidate_current_position'])) : '';
            $candidate_categories             = isset($_REQUEST['candidate_categories']) ? (wp_unslash($_REQUEST['candidate_categories'])) : '';
            $candidate_des                    = isset($_REQUEST['candidate_des']) ? wp_kses_post(wp_unslash($_REQUEST['candidate_des'])) : '';
            $candidate_dob                    = isset($_REQUEST['candidate_dob']) ? civi_clean(wp_unslash($_REQUEST['candidate_dob'])) : '';
            $candidate_age                    = isset($_REQUEST['candidate_age']) ? civi_clean(wp_unslash($_REQUEST['candidate_age'])) : '';
            $candidate_gender                 = isset($_REQUEST['candidate_gender']) ? civi_clean(wp_unslash($_REQUEST['candidate_gender'])) : '';
            $candidate_languages              = isset($_REQUEST['candidate_languages']) ? civi_clean(wp_unslash($_REQUEST['candidate_languages'])) : '';
            $candidate_qualification          = isset($_REQUEST['candidate_qualification']) ? civi_clean(wp_unslash($_REQUEST['candidate_qualification'])) : '';
            $candidate_yoe                    = isset($_REQUEST['candidate_yoe']) ? civi_clean(wp_unslash($_REQUEST['candidate_yoe'])) : '';
            $candidate_offer_salary           = isset($_REQUEST['candidate_offer_salary']) ? civi_clean(wp_unslash($_REQUEST['candidate_offer_salary'])) : '';
            $candidate_salary_type            = isset($_REQUEST['candidate_salary_type']) ? civi_clean(wp_unslash($_REQUEST['candidate_salary_type'])) : '';
            $candidate_currency_type          = isset($_REQUEST['candidate_currency_type']) ? civi_clean(wp_unslash($_REQUEST['candidate_currency_type'])) : '';

            $candidate_education_title        = isset($_REQUEST['candidate_education_title']) ? civi_clean(wp_unslash($_REQUEST['candidate_education_title'])) : array();
            $candidate_education_level        = isset($_REQUEST['candidate_education_level']) ? civi_clean(wp_unslash($_REQUEST['candidate_education_level'])) : array();
            $candidate_education_from         = isset($_REQUEST['candidate_education_from']) ? civi_clean(wp_unslash($_REQUEST['candidate_education_from'])) : array();
            $candidate_education_to           = isset($_REQUEST['candidate_education_to']) ? civi_clean(wp_unslash($_REQUEST['candidate_education_to'])) : array();
            $candidate_education_description  = isset($_REQUEST['candidate_education_description']) ? civi_clean(wp_unslash($_REQUEST['candidate_education_description'])) : array();

            $candidate_experience_job         = isset($_REQUEST['candidate_experience_job']) ? civi_clean(wp_unslash($_REQUEST['candidate_experience_job'])) : array();
            $candidate_experience_company     = isset($_REQUEST['candidate_experience_company']) ? civi_clean(wp_unslash($_REQUEST['candidate_experience_company'])) : array();
            $candidate_experience_from        = isset($_REQUEST['candidate_experience_from']) ? civi_clean(wp_unslash($_REQUEST['candidate_experience_from'])) : array();
            $candidate_experience_to          = isset($_REQUEST['candidate_experience_to']) ? civi_clean(wp_unslash($_REQUEST['candidate_experience_to'])) : array();
            $candidate_experience_description = isset($_REQUEST['candidate_experience_description']) ? civi_clean(wp_unslash($_REQUEST['candidate_experience_description'])) : array();

            $candidate_skills                 = isset($_REQUEST['candidate_skills']) ? civi_clean(wp_unslash($_REQUEST['candidate_skills'])) : array();

            $candidate_project_title          = isset($_REQUEST['candidate_project_title']) ? civi_clean(wp_unslash($_REQUEST['candidate_project_title'])) : array();
            $candidate_project_link           = isset($_REQUEST['candidate_project_link']) ? civi_clean(wp_unslash($_REQUEST['candidate_project_link'])) : array();
            $candidate_project_description    = isset($_REQUEST['candidate_project_description']) ? civi_clean(wp_unslash($_REQUEST['candidate_project_description'])) : array();
            $candidate_project_image_id       = isset($_REQUEST['candidate_project_image_id']) ? civi_clean(wp_unslash($_REQUEST['candidate_project_image_id'])) : array();
            $candidate_project_image_url      = isset($_REQUEST['candidate_project_image_url']) ? civi_clean(wp_unslash($_REQUEST['candidate_project_image_url'])) : array();

            $candidate_award_title            = isset($_REQUEST['candidate_award_title']) ? civi_clean(wp_unslash($_REQUEST['candidate_award_title'])) : array();
            $candidate_award_date             = isset($_REQUEST['candidate_award_date']) ? civi_clean(wp_unslash($_REQUEST['candidate_award_date'])) : array();
            $candidate_award_description      = isset($_REQUEST['candidate_award_description']) ? civi_clean(wp_unslash($_REQUEST['candidate_award_description'])) : array();

            $candidate_cover_image_id         = isset($_REQUEST['candidate_cover_image_id']) ? civi_clean(wp_unslash($_REQUEST['candidate_cover_image_id'])) : '';
            $candidate_cover_image_url        = isset($_REQUEST['candidate_cover_image_url']) ? civi_clean(wp_unslash($_REQUEST['candidate_cover_image_url'])) : '';
            $author_avatar_image_id           = isset($_REQUEST['author_avatar_image_id']) ? civi_clean(wp_unslash($_REQUEST['author_avatar_image_id'])) : '';
            $author_avatar_image_url           = isset($_REQUEST['author_avatar_image_url']) ? civi_clean(wp_unslash($_REQUEST['author_avatar_image_url'])) : '';

            $candidate_resume           = isset($_REQUEST['candidate_resume']) ? civi_clean(wp_unslash($_REQUEST['candidate_resume'])) : '';

            $candidate_twitter = isset($_REQUEST['candidate_twitter']) ? civi_clean(wp_unslash($_REQUEST['candidate_twitter'])) : '';
            $candidate_linkedin = isset($_REQUEST['candidate_linkedin']) ? civi_clean(wp_unslash($_REQUEST['candidate_linkedin'])) : '';
            $candidate_facebook = isset($_REQUEST['candidate_facebook']) ? civi_clean(wp_unslash($_REQUEST['candidate_facebook'])) : '';
            $candidate_instagram = isset($_REQUEST['candidate_instagram']) ? civi_clean(wp_unslash($_REQUEST['candidate_instagram'])) : '';
            $candidate_social_name = isset($_REQUEST['candidate_social_name']) ? civi_clean(wp_unslash($_REQUEST['candidate_social_name'])) : '';
            $candidate_social_url = isset($_REQUEST['candidate_social_url']) ? civi_clean(wp_unslash($_REQUEST['candidate_social_url'])) : '';
            $candidate_social_data  = isset($_REQUEST['candidate_social_data']) ? civi_clean(wp_unslash($_REQUEST['candidate_social_data'])) : '';

            $candidate_map_location       = isset($_REQUEST['civi_map_location']) ? civi_clean(wp_unslash($_REQUEST['civi_map_location'])) : '';
            $candidate_map_address        = isset($_REQUEST['civi_map_address']) ? civi_clean(wp_unslash($_REQUEST['civi_map_address'])) : '';
            $candidate_latitude        = isset($_REQUEST['civi_latitude']) ? civi_clean(wp_unslash($_REQUEST['civi_latitude'])) : '';
            $candidate_longtitude        = isset($_REQUEST['candidate_longtitude']) ? civi_clean(wp_unslash($_REQUEST['candidate_longtitude'])) : '';
            $candidate_location       = isset($_REQUEST['candidate_location']) ? civi_clean(wp_unslash($_REQUEST['candidate_location'])) : '';

            $civi_gallery_ids             = isset($_REQUEST['civi_gallery_ids']) ? civi_clean(wp_unslash($_REQUEST['civi_gallery_ids'])) : array();
            $candidate_video_url              = isset($_REQUEST['candidate_video_url']) ? civi_clean(wp_unslash($_REQUEST['candidate_video_url'])) : '';
            $candidate_profile_strength         = isset($_REQUEST['candidate_profile_strength']) ? civi_clean(wp_unslash($_REQUEST['candidate_profile_strength'])) : '';
            $custom_field_candidate        = isset($_REQUEST['custom_field_candidate']) ? civi_clean(wp_unslash($_REQUEST['custom_field_candidate'])) : '';

            global $current_user;
            wp_get_current_user();
            $user_id = $current_user->ID;

            $new_candidate = array();
            $new_candidate['post_type'] = 'candidate';
            $new_candidate['post_author'] = $user_id;

            if (isset($candidate_des)) {
                $new_candidate['post_content'] = $candidate_des;
            }

            $candidate_id        = absint(wp_unslash($candidate_id));
            $new_candidate['ID'] = intval($candidate_id);

            $new_candidate['post_status'] = 'publish';

            $candidate_id = wp_update_post($new_candidate);

            echo json_encode(array('success' => true));

            if ($candidate_id > 0) {

                if (isset($candidate_first_name)) {
                    update_user_meta($user_id, 'first_name', $candidate_first_name);
                    update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_first_name', $candidate_first_name);
                }

                if (isset($candidate_last_name)) {
                    update_user_meta($user_id, 'last_name', $candidate_last_name);
                    update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_last_name', $candidate_last_name);
                }

                if (isset($candidate_first_name) && isset($candidate_last_name)) {
                    $type_name_candidate = civi_get_option('type_name_candidate');
                    if ($type_name_candidate === 'fl-name') {
                        $full_name = $candidate_first_name . ' ' . $candidate_last_name;
                        $userdata = array(
                            'ID' => $user_id,
                            'display_name' => $full_name,
                        );
                        wp_update_user($userdata);

                        $data = array(
                            'ID' => $candidate_id,
                            'post_type' => 'candidate',
                            'post_title'   => $full_name,
                        );
                        wp_update_post($data);
                    }
                }

                if (isset($candidate_email)) {
                    update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_email', $candidate_email);
                }

                if (isset($candidate_phone)) {
                    update_user_meta($user_id, CIVI_METABOX_PREFIX . 'author_mobile_number', $candidate_phone);
                    update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_phone', $candidate_phone);
                }

                if (isset($candidate_phone_code)) {
                    update_user_meta($user_id, CIVI_METABOX_PREFIX . 'phone_code', $candidate_phone_code);
                }

                if (isset($candidate_current_position)) {
                    update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_current_position', $candidate_current_position);
                }

                if (isset($candidate_dob)) {
                    update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_dob', $candidate_dob);
                }

                if (isset($candidate_offer_salary)) {
                    update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_offer_salary', $candidate_offer_salary);
                }

                if (isset($candidate_salary_type)) {
                    update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_salary_type', $candidate_salary_type);
                }

                if (isset($candidate_currency_type)) {
                    update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_currency_type', $candidate_currency_type);
                }

                if (isset($candidate_resume)) {
                    update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_resume_id_list', $candidate_resume);
                }

                if (isset($candidate_twitter)) {
                    update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_twitter', $candidate_twitter);
                }

                if (isset($candidate_linkedin)) {
                    update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_linkedin', $candidate_linkedin);
                }

                if (isset($candidate_facebook)) {
                    update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_facebook', $candidate_facebook);
                }

                if (isset($candidate_instagram)) {
                    update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_instagram', $candidate_instagram);
                }

                if (isset($candidate_video_url)) {
                    update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_video_url', $candidate_video_url);
                }

                if (isset($candidate_profile_strength)) {
                    update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_profile_strength', $candidate_profile_strength);
                }

                //Taxonomy
                if (isset($candidate_categories)) {
                    $candidate_categories = intval($candidate_categories);
                    wp_set_object_terms($candidate_id, $candidate_categories, 'candidate_categories');
                }

                if (isset($candidate_age)) {
                    $candidate_age = intval($candidate_age);
                    $array = array(
                        'candidate_id' => $candidate_id,
                        'candidate_age' => $candidate_age,
                    );
                    $candidate_age = apply_filters('civi_candidate_age_meta', $array);

                    if (is_array($candidate_age)) {
                        wp_set_object_terms($candidate_id, $candidate_age['candidate_age'], 'candidate_ages');
                    } else {
                        wp_set_object_terms($candidate_id, $candidate_age, 'candidate_ages');
                    }
                }

                if (!empty($candidate_languages)) {
                    if (civi_get_option('enable_candidate_language_multiple') === '1') {
                        $candidate_languages = array_map('intval', $candidate_languages);
                        wp_set_object_terms($candidate_id, $candidate_languages, 'candidate_languages');
                    } else {
                        $candidate_languages = intval($candidate_languages);
                        wp_set_object_terms($candidate_id, $candidate_languages, 'candidate_languages');
                    }
                }

                if (isset($candidate_qualification)) {
                    $candidate_qualification = intval($candidate_qualification);
                    wp_set_object_terms($candidate_id, $candidate_qualification, 'candidate_qualification');
                }

                if (isset($candidate_yoe)) {
                    $candidate_yoe = intval($candidate_yoe);
                    wp_set_object_terms($candidate_id, $candidate_yoe, 'candidate_yoe');
                }

                if (isset($candidate_gender)) {
                    $candidate_gender = intval($candidate_gender);
                    wp_set_object_terms($candidate_id, $candidate_gender, 'candidate_gender');
                }

                if (isset($candidate_location)) {
                    $candidate_location = intval($candidate_location);
                    wp_set_object_terms($candidate_id, $candidate_location, 'candidate_locations');
                }

                if (isset($author_avatar_image_id) && isset($author_avatar_image_url)) {
                    update_user_meta($user_id, 'author_avatar_image_id', $author_avatar_image_id);
                    update_user_meta($user_id, 'author_avatar_image_url', $author_avatar_image_url);
                } else {
                    delete_user_meta($user_id, 'author_avatar_image_id');
                    delete_user_meta($user_id, 'author_avatar_image_url');
                }

                if (isset($civi_gallery_ids)) {
                    $str_img_ids = '';
                    foreach ($civi_gallery_ids as $gallery_id) {
                        $civi_gallery_ids[] = intval($gallery_id);
                        $str_img_ids .= '|' . intval($gallery_id);
                    }
                    $str_img_ids = substr($str_img_ids, 1);
                    update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_galleries', $str_img_ids);
                }
                if (isset($candidate_map_location)) {
                    $lat_lng = $candidate_map_location;
                    $address = $candidate_map_address;
                    $arr_location = array(
                        'location' => $lat_lng,
                        'address' => $address,
                    );
                    update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_location', $arr_location);
                    update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_address', $candidate_map_address);
                    update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_latitude', $candidate_latitude);
                    update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_longtitude', $candidate_longtitude);
                }

                if (!empty($candidate_social_name)) {
                    $social_data  = array();
                    for ($i = 1; $i < count($candidate_social_name); $i++) {
                        $social_data[] = array(
                            CIVI_METABOX_PREFIX . 'candidate_social_name'   => $candidate_social_name[$i],
                            CIVI_METABOX_PREFIX . 'candidate_social_url'    => $candidate_social_url[$i],
                        );
                    }
                    update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_social_tabs', $social_data);
                }

                if (!empty($candidate_social_data)) {
                    foreach ($candidate_social_data as $key => $value) {
                        update_post_meta($candidate_id, CIVI_METABOX_PREFIX . $key, $value);
                    }
                }

                if (isset($candidate_cover_image_id) && !empty($candidate_cover_image_url)) {
                    update_post_meta($candidate_id, '_thumbnail_id', $candidate_cover_image_id);
                    update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_cover_image_id', $candidate_cover_image_id);
                    update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_cover_image_url', $candidate_cover_image_url);
                } else {
                    delete_post_meta($candidate_id, '_thumbnail_id');
                    delete_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_cover_image_id');
                    delete_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_cover_image_url');
                }

                if (isset($candidate_education_title)) {
                    $education_data = array();
                    for ($i = 0; $i < count($candidate_education_title); $i++) {
                        $education_data[] = array(
                            CIVI_METABOX_PREFIX . 'candidate_education_title'       => $candidate_education_title[$i],
                            CIVI_METABOX_PREFIX . 'candidate_education_level'       => $candidate_education_level[$i],
                            CIVI_METABOX_PREFIX . 'candidate_education_from'        => $candidate_education_from[$i],
                            CIVI_METABOX_PREFIX . 'candidate_education_to'          => $candidate_education_to[$i],
                            CIVI_METABOX_PREFIX . 'candidate_education_description' => $candidate_education_description[$i],
                        );
                    }
                    update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_education_list', $education_data);
                }

                if (isset($candidate_experience_job)) {

                    $experience_data = array();
                    for ($i = 0; $i < count($candidate_experience_job); $i++) {
                        $experience_data[] = array(
                            CIVI_METABOX_PREFIX . 'candidate_experience_job'         => $candidate_experience_job[$i],
                            CIVI_METABOX_PREFIX . 'candidate_experience_company'     => $candidate_experience_company[$i],
                            CIVI_METABOX_PREFIX . 'candidate_experience_from'        => $candidate_experience_from[$i],
                            CIVI_METABOX_PREFIX . 'candidate_experience_to'          => $candidate_experience_to[$i],
                            CIVI_METABOX_PREFIX . 'candidate_experience_description' => $candidate_experience_description[$i]
                        );
                    }

                    update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_experience_list', $experience_data);
                }

                if (isset($candidate_skills)) {
                    $candidate_skills = array_map('intval', $candidate_skills);
                    wp_set_object_terms($candidate_id, $candidate_skills, 'candidate_skills');
                }

                if (isset($candidate_project_title)) {

                    $project_data = array();
                    for ($i = 0; $i < count($candidate_project_title); $i++) {
                        $candidate_project_image = array(
                            'id'  => $candidate_project_image_id[$i],
                            'url'  => $candidate_project_image_url[$i],
                        );
                        $project_data[] = array(
                            CIVI_METABOX_PREFIX . 'candidate_project_title'       => $candidate_project_title[$i],
                            CIVI_METABOX_PREFIX . 'candidate_project_link'        => $candidate_project_link[$i],
                            CIVI_METABOX_PREFIX . 'candidate_project_description' => $candidate_project_description[$i],
                            CIVI_METABOX_PREFIX . 'candidate_project_image_id'    =>  $candidate_project_image,
                        );
                    }

                    update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_project_list', $project_data);
                }

                if (isset($candidate_award_title)) {

                    $award_data = array();
                    for ($i = 0; $i < count($candidate_award_title); $i++) {
                        $award_data[] = array(
                            CIVI_METABOX_PREFIX . 'candidate_award_title'       => $candidate_award_title[$i],
                            CIVI_METABOX_PREFIX . 'candidate_award_date'        => $candidate_award_date[$i],
                            CIVI_METABOX_PREFIX . 'candidate_award_description' => $candidate_award_description[$i],
                        );
                    }

                    update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_award_list', $award_data);
                }

                $get_additional = civi_render_custom_field('candidate');
                if (count($get_additional) > 0 && !empty($custom_field_candidate)) {
                    foreach ($get_additional as $key => $field) {
                        if (count($custom_field_candidate) > 0 && isset($custom_field_candidate[$field['id']])) {
                            if ($field['type'] == 'checkbox_list') {
                                $arr = array();
                                foreach ($custom_field_candidate[$field['id']] as $v) {
                                    $arr[] = $v;
                                }
                                update_post_meta($candidate_id, $field['id'], $arr);
                            } elseif ($field['type'] == 'image') {
                                $custom_field_candidate_url = wp_get_attachment_url($custom_field_candidate[$field['id']]);
                                $custom_image = array(
                                    'id'  => $custom_field_candidate[$field['id']],
                                    'url' => $custom_field_candidate_url,
                                );
                                update_post_meta($candidate_id, $field['id'], $custom_image);
                            } elseif ($field['type'] == 'select') {
                                $selected_value = $custom_field_candidate[$field['id']];
                                if (is_array($selected_value)) {
                                    $validated_values = array_map('trim', $selected_value);
                                    $validated_values = array_intersect($validated_values, array_map('trim', array_values($field['options'])));
                                    if (!empty($validated_values)) {
                                        update_post_meta($candidate_id, $field['id'], $validated_values);
                                    }
                                } else {
                                    $selected_value = trim($selected_value);

                                    if (in_array($selected_value, array_map('trim', array_values($field['options'])))) {
                                        update_post_meta($candidate_id, $field['id'], $selected_value);
                                    }
                                }
                            } elseif ($field['type'] == 'url') {
                                $video_url = $custom_field_candidate[$field['id']];
                                if (filter_var($video_url, FILTER_VALIDATE_URL)) {
                                    update_post_meta($candidate_id, $field['id'], $video_url);
                                }
                            } elseif ($field['type'] == 'textarea') {
                                $textarea_content = sanitize_textarea_field($custom_field_candidate[$field['id']]);
                                update_post_meta($candidate_id, $field['id'], $textarea_content);
                            } elseif ($field['type'] == 'text') {
                                $text_value = sanitize_text_field($custom_field_candidate[$field['id']]);
                                update_post_meta($candidate_id, $field['id'], $text_value);
                            } else {
                                update_post_meta($candidate_id, $field['id'], $custom_field_candidate[$field['id']]);
                            }
                        }
                    }
                }
            }

            $marked_for_deletion = get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'marked_for_deletion', true);
            if (is_array($marked_for_deletion) && !empty($marked_for_deletion)) {
                foreach ($marked_for_deletion as $attachment_id) {
                    wp_delete_attachment($attachment_id);
                }
                delete_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'marked_for_deletion');
            }

            wp_die();
        }

        public function save_candidate_profile_strength_ajax() {
            $nonce = isset($_REQUEST['nonce']) ? civi_clean(wp_unslash($_REQUEST['nonce'])) : '';
            if (!wp_verify_nonce($nonce, 'save_profile_strength')) {
                wp_send_json_error(array('message' => 'Security check failed'));
                wp_die();
            }

            if (!is_user_logged_in()) {
                wp_send_json_error(array('message' => 'Not authenticated'));
                wp_die();
            }

            $candidate_profile_strength = isset($_REQUEST['profile_strength']) ? intval($_REQUEST['profile_strength']) : 0;
            $candidate_id = civi_get_post_id_candidate();

            if (!empty($candidate_id)) {
                update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_profile_strength', $candidate_profile_strength);
                wp_send_json_success(array('message' => 'Profile strength saved'));
            } else {
                wp_send_json_error(array('message' => 'Candidate not found'));
            }

            wp_die();
        }

        /**
         * submit review
         */
        public function submit_review_ajax()
        {
            check_ajax_referer('civi_submit_review_ajax_nonce', 'civi_security_submit_review');

            if (! is_user_logged_in()) {
                wp_send_json_error(['message' => __('You must be logged in to submit a review.', 'civi-framework')], 401);
            }

            global $wpdb;
            $current_user = wp_get_current_user();
            $user_id      = (int) $current_user->ID;
            $user         = get_user_by('id', $user_id);

            $candidate_id = isset($_POST['candidate_id']) ? (int) wp_unslash($_POST['candidate_id']) : 0;
            if ($candidate_id <= 0) {
                wp_send_json_error(['message' => __('Invalid candidate ID.', 'civi-framework')], 422);
            }

            // clamp ratings 1–5
            $clamp = static function ($v) {
                return max(1, min(5, (int) $v));
            };
            $rating_working_value = $clamp($_POST['rating_working'] ?? 0);
            $rating_team_value    = $clamp($_POST['rating_team'] ?? 0);
            $rating_skill_value   = $clamp($_POST['rating_skill'] ?? 0);
            $rating_salary_value  = $clamp($_POST['rating_salary'] ?? 0);

            // Secure query
            $sql = $wpdb->prepare(
                "SELECT c.comment_ID, m.meta_value
         FROM {$wpdb->comments} c
         INNER JOIN {$wpdb->commentmeta} m ON m.comment_id = c.comment_ID
         WHERE c.comment_post_ID = %d
           AND c.user_id = %d
           AND m.meta_key = %s
         ORDER BY c.comment_ID DESC
         LIMIT 1",
                $candidate_id,
                $user_id,
                'candidate_rating'
            );
            $my_review = $wpdb->get_row($sql);

            $comment_approved = get_option('comment_moderation') ? 0 : 1;
            $message_raw      = isset($_POST['message']) ? wp_unslash($_POST['message']) : '';
            $comment_content  = wp_kses_post($message_raw);

            $candidate_rating = ($rating_working_value + $rating_team_value + $rating_skill_value + $rating_salary_value) / 4;
            $candidate_rating = number_format((float) $candidate_rating, 2, '.', '');

            if (null === $my_review) {
                // Insert new
                $data = [
                    'comment_post_ID'      => $candidate_id,
                    'comment_content'      => $comment_content,
                    'comment_date'         => current_time('mysql'),
                    'comment_approved'     => $comment_approved,
                    'comment_author'       => $user->user_login,
                    'comment_author_email' => $user->user_email,
                    'comment_author_url'   => $user->user_url,
                    'user_id'              => $user_id,
                ];

                $comment_id = wp_insert_comment($data);
                if (!$comment_id || is_wp_error($comment_id)) {
                    wp_send_json_error(['message' => __('Could not save your review.', 'civi-framework')], 500);
                }

                add_comment_meta($comment_id, 'candidate_salary_rating',   $rating_working_value);
                add_comment_meta($comment_id, 'candidate_candidate_rating', $rating_team_value);
                add_comment_meta($comment_id, 'candidate_skill_rating',    $rating_skill_value);
                add_comment_meta($comment_id, 'candidate_work_rating',     $rating_salary_value);
                add_comment_meta($comment_id, 'candidate_rating',          $candidate_rating);

                if ($comment_approved) {
                    apply_filters('civi_candidate_rating_meta', $candidate_id, $candidate_rating);
                }

                $comment_thumb = $this->handle_review_uploads($_FILES['files'] ?? []);
                if ($comment_thumb) {
                    add_comment_meta($comment_id, 'comment_thumb', $comment_thumb);
                }

                civi_get_data_ajax_notification($candidate_id, 'add-review-candidate');
            } else {
                // Update existing
                $update = [
                    'comment_ID'       => (int) $my_review->comment_ID,
                    'comment_post_ID'  => $candidate_id,
                    'comment_content'  => $comment_content,
                    'comment_date'     => current_time('mysql'),
                    'comment_approved' => $comment_approved,
                ];

                $ok = wp_update_comment($update);
                if (!$ok) {
                    wp_send_json_error(['message' => __('Could not update your review.', 'civi-framework')], 500);
                }

                update_comment_meta($my_review->comment_ID, 'candidate_salary_rating',   $rating_working_value);
                update_comment_meta($my_review->comment_ID, 'candidate_candidate_rating', $rating_team_value);
                update_comment_meta($my_review->comment_ID, 'candidate_skill_rating',    $rating_skill_value);
                update_comment_meta($my_review->comment_ID, 'candidate_work_rating',     $rating_salary_value);
                update_comment_meta($my_review->comment_ID, 'candidate_rating',          $candidate_rating, $my_review->meta_value);

                if ($comment_approved) {
                    apply_filters('civi_candidate_rating_meta', $candidate_id, $candidate_rating, false, $my_review->meta_value);
                }

                $comment_thumb = $this->handle_review_uploads($_FILES['files'] ?? []);
                if ($comment_thumb) {
                    update_comment_meta($my_review->comment_ID, 'comment_thumb', $comment_thumb);
                }
            }

            wp_send_json_success([
                'message'           => __('Review submitted successfully.', 'civi-framework'),
                'candidate_rating'  => $candidate_rating,
                'approved'          => (int) $comment_approved,
            ]);
        }

        /**
         * Secure file uploads for review thumbs
         */
        private function handle_review_uploads($files)
        {
            if (empty($files) || !isset($files['name'])) {
                return [];
            }

            $countfiles    = count($files['name']);
            $comment_thumb = [];

            for ($i = 0; $i < $countfiles; $i++) {
                if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                    continue;
                }

                $submitted_file = [
                    'error'    => $files['error'][$i],
                    'name'     => sanitize_file_name($files['name'][$i]),
                    'size'     => (int) $files['size'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'type'     => $files['type'][$i],
                ];

                $movefile = wp_handle_upload($submitted_file, ['test_form' => false]);
                if (isset($movefile['file'])) {
                    $filetype = wp_check_filetype($movefile['file'], null);
                    if (!in_array($filetype['type'], ['image/jpeg', 'image/png', 'image/gif'], true)) {
                        continue; // chỉ chấp nhận ảnh
                    }

                    $attachment_details = [
                        'guid'           => $movefile['url'],
                        'post_mime_type' => $filetype['type'],
                        'post_title'     => preg_replace('/\.[^.]+$/', '', basename($submitted_file['name'])),
                        'post_content'   => '',
                        'post_status'    => 'inherit'
                    ];

                    $attach_id   = wp_insert_attachment($attachment_details, $movefile['file']);
                    $attach_data = wp_generate_attachment_metadata($attach_id, $movefile['file']);
                    wp_update_attachment_metadata($attach_id, $attach_data);

                    $comment_thumb[] = $attach_id;
                }
            }

            return $comment_thumb;
        }


        /**
         * @param $candidate_id
         * @param int $added_star The new rating, can be negative or positive
         * @param int $old_overall_rate
         * @param int $new_review_count
         */

        /**
         * @param $candidate_id
         * @param $rating_value
         * @param bool|true $comment_exist
         * @param int $old_rating_value
         */
        public function rating_meta_filter($candidate_id, $rating_value, $comment_exist = true, $old_rating_value = 0)
        {
            update_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_rating', $rating_value);
        }

        /**
         * submit review
         */
        public function submit_reply_ajax()
        {
            check_ajax_referer('civi_submit_reply_ajax_nonce', 'civi_security_submit_reply');
            global $wpdb, $current_user;
            wp_get_current_user();
            $user_id  = $current_user->ID;
            $user     = get_user_by('id', $user_id);
            $candidate_id = isset($_POST['candidate_id']) ? civi_clean(wp_unslash($_POST['candidate_id'])) : '';
            $comment_approved = 1;
            $auto_publish_review_candidate = get_option('comment_moderation');
            if ($auto_publish_review_candidate == 1) {
                $comment_approved = 0;
            }
            $data = array();
            $user = $user->data;

            $data['comment_post_ID']      = $candidate_id;
            $data['comment_content']      = isset($_POST['message']) ? wp_filter_post_kses($_POST['message']) : '';
            $data['comment_date']         = current_time('mysql');
            $data['comment_approved']     = $comment_approved;
            $data['comment_author']       = $user->user_login;
            $data['comment_author_email'] = $user->user_email;
            $data['comment_author_url']   = $user->user_url;
            $data['comment_parent']       = isset($_POST['comment_id']) ? civi_clean(wp_unslash($_POST['comment_id'])) : '';
            $data['user_id']              = $user_id;

            $comment_id = wp_insert_comment($data);

            echo json_encode(array('success' => true));

            wp_die();
        }
    }
}
