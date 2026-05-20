<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Helper functions
 */
if (!class_exists('JobPortal_Helper')) {

    class JobPortal_Helper
    {

        /**
         * The constructor.
         */
        function __construct()
        {
            add_action('delete_attachment', array($this, 'jobportal_delete_resized_images'));

            add_filter('body_class', array($this, 'jobportal_body_class'));
        }

        /**
         * Get Setting
         */
        public static function get_setting($key)
        {
            $option = '';
            $option = get_option_customize($key);
            return $option;
        }

        /**
         * Get Option
         */
        public static function jobportal_get_option($key, $default = '')
        {
            $option = '';
            if (class_exists('JobPortal_Framework')) {
                $option = jobportal_get_option($key, $default);
            }
            return (isset($option)) ? $option : $default;
        }

        /**
         * Clean Variable
         */
        public static function jobportal_clean($var)
        {
            if (is_array($var)) {
                return array_map('jobportal_clean', $var);
            } else {
                return is_scalar($var) ? sanitize_text_field($var) : $var;
            }
        }

        /**
         * Get Setting
         */
        public static function jobportal_body_class($classes)
        {

            $enable_rtl_mode  = JobPortal_Helper::jobportal_get_option('enable_rtl_mode', 0);

            if (is_rtl() || $enable_rtl_mode) {
                $classes[] = 'rtl';
            }

            return $classes;
        }

        /**
         * Check has shortcode
         */
        public static function jobportal_page_shortcode($shortcode = NULL)
        {

            $post = get_post(get_the_ID());

            if (empty($post->post_content)) {
                return false;
            }

            $found = false;

            if ($post->post_content === $shortcode) {
                $found = true;
            }

            // return our final results
            return $found;
        }


        /**
         * Send email
         */
        public static function jobportal_send_email($email, $email_type, $args = array())
        {

            $content = JobPortal_Helper::jobportal_get_option($email_type, '');
            $subject = JobPortal_Helper::jobportal_get_option('subject_' . $email_type, '');

            if (function_exists('icl_translate')) {
                $content = icl_translate('jobportal', 'jobportal_email_' . $content, $content);
                $subject = icl_translate('jobportal', 'jobportal_email_subject_' . $subject, $subject);
            }
            $content = wpautop($content);
            $args['website_url'] = get_option('siteurl');
            $args['website_name'] = get_option('blogname');
            // Recipient ($email) is not always the registered user (e.g. admin notification).
            if (!array_key_exists('user_email', $args) || $args['user_email'] === '' || $args['user_email'] === null) {
                $args['user_email'] = $email;
            }
            $user = get_user_by('email', $email);
            if (!empty($user)) {
                $args['username'] = $user->user_login;
            }

            // Backward compatibility for registration placeholders.
            // Support both old tokens (%user_login, %user_password)
            // and new tokens (%user_login_register, %user_pass_register).
            if (empty($args['user_login']) && !empty($args['user_login_register'])) {
                $args['user_login'] = $args['user_login_register'];
            }
            if (empty($args['user_login_register']) && !empty($args['user_login'])) {
                $args['user_login_register'] = $args['user_login'];
            }
            if (empty($args['user_password']) && !empty($args['user_pass_register'])) {
                $args['user_password'] = $args['user_pass_register'];
            }
            if (empty($args['user_pass_register']) && !empty($args['user_password'])) {
                $args['user_pass_register'] = $args['user_password'];
            }

            $keys = array_keys($args);
            usort($keys, function ($a, $b) {
                $la = strlen($a);
                $lb = strlen($b);
                if ($la === $lb) {
                    return 0;
                }
                return ($la > $lb) ? -1 : 1;
            });
            foreach ($keys as $key) {
                $val = isset($args[$key]) ? $args[$key] : '';
                if (is_scalar($val)) {
                    $val = (string) $val;
                } else {
                    $val = '';
                }
                // Avoid %user_email matching inside %user_email_register (prefix collision).
                $pct_pattern = '#%' . preg_quote($key, '#') . '(?![a-z0-9_])#i';
                $subject     = preg_replace_callback($pct_pattern, function () use ($val) {
                    return $val;
                }, $subject);
                $content = preg_replace_callback($pct_pattern, function () use ($val) {
                    return $val;
                }, $content);
                $subject = str_replace('{' . $key . '}', $val, $subject);
                $content = str_replace('{' . $key . '}', $val, $content);
            }

            ob_start();
            jobportal_get_template("mail/mail.php", array(
                'content' => $content,
            ));
            $message = ob_get_clean();

            $headers = apply_filters('jobportal_contact_mail_header', array('From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>', 'Content-Type: text/html; charset=UTF-8'));

            @wp_mail(
                $email,
                $subject,
                $message,
                $headers
            );
        }

        /**
         * Allowed_html
         */
        public static function jobportal_kses_allowed_html()
        {
            $allowed_tags = array(
                'a' => array(
                    'id'    => array(),
                    'class' => array(),
                    'href'  => array(),
                    'rel'   => array(),
                    'title' => array(),
                ),
                'abbr' => array(
                    'title' => array(),
                ),
                'b' => array(),
                'blockquote' => array(
                    'cite'  => array(),
                ),
                'cite' => array(
                    'title' => array(),
                ),
                'code' => array(),
                'del' => array(
                    'datetime' => array(),
                    'title' => array(),
                ),
                'dd' => array(),
                'div' => array(
                    'class' => array(),
                    'title' => array(),
                    'style' => array(),
                ),
                'dl' => array(),
                'dt' => array(),
                'em' => array(),
                'h1' => array(),
                'h2' => array(),
                'h3' => array(),
                'h4' => array(),
                'h5' => array(),
                'h6' => array(),
                'i' => array(
                    'class' => array(),
                ),
                'img' => array(
                    'alt'    => array(),
                    'class'  => array(),
                    'height' => array(),
                    'src'    => array(),
                    'width'  => array(),
                ),
                'li' => array(
                    'class' => array(),
                ),
                'ol' => array(
                    'class' => array(),
                ),
                'p' => array(
                    'class' => array(),
                ),
                'q' => array(
                    'cite' => array(),
                    'title' => array(),
                ),
                'span' => array(
                    'class' => array(),
                    'title' => array(),
                    'style' => array(),
                ),
                'strike' => array(),
                'strong' => array(),
                'ul' => array(
                    'class' => array(),
                ),
            );

            return $allowed_tags;
        }

        public static function jobportal_image_captcha($captcha)
        {

            if (empty($captcha)) return;

            // Generate a 50x24 standard captcha image
            $im = imagecreatetruecolor(50, 40);

            // Accent color
            $bg = imagecolorallocate($im, 0, 116, 86);
            $bg = apply_filters('jobportal_image_captcha_bg_color', $bg, $im);

            // White color
            $fg = imagecolorallocate($im, 255, 255, 255);

            // Give the image a blue background
            imagefill($im, 0, 0, $bg);

            // Print the captcha text in the image
            // with random position & size
            imagestring($im, 24, 8, 11, $captcha, $fg);

            ob_start();

            // Finally output the captcha as
            // PNG image the browser
            imagepng($im);

            $imgData = ob_get_clean();

            // Free memory
            imagedestroy($im);

            echo '<img src="data:image/png;base64,' . base64_encode($imgData) . '" />';
        }

        /**
         * Image size
         */
        public static function jobportal_image_resize($data, $image_size)
        {
            if (preg_match('/\d+x\d+/', $image_size)) {
                $image_sizes = explode('x', $image_size);
                $image_src  = self::jobportal_image_resize_id($data, $image_sizes[0], $image_sizes[1], true);
            } else {
                if (!in_array($image_size, array('full', 'thumbnail'))) {
                    $image_size = 'full';
                }
                $image_src = wp_get_attachment_image_src($data, $image_size);
                if ($image_src && !empty($image_src[0])) {
                    $image_src = $image_src[0];
                }
            }
            return $image_src;
        }

        /**
         * Image resize by url
         */
        public static function jobportal_image_resize_url($url, $width = NULL, $height = NULL, $crop = true, $retina = false)
        {

            global $wpdb;

            if (empty($url))
                return new WP_Error('no_image_url', esc_html__('No image URL has been entered.', 'jobportal'), $url);

            if (class_exists('Jetpack') && method_exists('Jetpack', 'get_active_modules') && in_array('photon', Jetpack::get_active_modules())) {
                $args_crop = array(
                    'resize' => $width . ',' . $height,
                    'crop' => '0,0,' . $width . 'px,' . $height . 'px'
                );
                $url = jetpack_photon_url($url, $args_crop);
            }

            // Get default size from database
            $width = ($width) ? $width : get_option('thumbnail_size_w');
            $height = ($height) ? $height : get_option('thumbnail_size_h');

            // Allow for different retina sizes
            $retina = $retina ? ($retina === true ? 2 : $retina) : 1;

            // Get the image file path
            $file_path = parse_url($url);
            $file_path = $_SERVER['DOCUMENT_ROOT'] . $file_path['path'];

            // Check for Multisite
            if (is_multisite()) {
                global $blog_id;
                $blog_details = get_blog_details($blog_id);
                $file_path = str_replace($blog_details->path, '/', $file_path);
                //$file_path = str_replace($blog_details->path . 'files/', '/wp-content/blogs.dir/' . $blog_id . '/files/', $file_path);
            }

            // Destination width and height variables
            $dest_width = $width * $retina;
            $dest_height = $height * $retina;

            // File name suffix (appended to original file name)
            $suffix = "{$dest_width}x{$dest_height}";

            // Some additional info about the image
            $info = pathinfo($file_path);
            $dir = $info['dirname'];
            $ext = $name = '';
            if (!empty($info['extension'])) {
                $ext = $info['extension'];
                $name = wp_basename($file_path, ".$ext");
            }

            if ('bmp' == $ext) {
                return new WP_Error('bmp_mime_type', esc_html__('Image is BMP. Please use either JPG or PNG.', 'jobportal'), $url);
            }

            // Suffix applied to filename
            $suffix = "{$dest_width}x{$dest_height}";

            // Get the destination file name
            $dest_file_name = "{$dir}/{$name}-{$suffix}.{$ext}";

            if (!file_exists($dest_file_name)) {

                /*
	             *  Bail if this image isn't in the Media Library.
	             *  We only want to resize Media Library images, so we can be sure they get deleted correctly when appropriate.
	             */
                $query = $wpdb->prepare("SELECT * FROM $wpdb->posts WHERE guid='%s'", $url);
                $get_attachment = $wpdb->get_results($query);
                // if (!$get_attachment)
                //     return array('url' => $url, 'width' => $width, 'height' => $height);

                // Load Wordpress Image Editor
                $editor = wp_get_image_editor($file_path);
                if (is_wp_error($editor))
                    return array('url' => $url, 'width' => $width, 'height' => $height);

                // Get the original image size
                $size = $editor->get_size();
                $orig_width = $size['width'];
                $orig_height = $size['height'];

                $src_x = $src_y = 0;
                $src_w = $orig_width;
                $src_h = $orig_height;

                if ($crop) {

                    $cmp_x = $orig_width / $dest_width;
                    $cmp_y = $orig_height / $dest_height;

                    // Calculate x or y coordinate, and width or height of source
                    if ($cmp_x > $cmp_y) {
                        $src_w = round($orig_width / $cmp_x * $cmp_y);
                        $src_x = round(($orig_width - ($orig_width / $cmp_x * $cmp_y)) / 2);
                    } else if ($cmp_y > $cmp_x) {
                        $src_h = round($orig_height / $cmp_y * $cmp_x);
                        $src_y = round(($orig_height - ($orig_height / $cmp_y * $cmp_x)) / 2);
                    }
                }

                // Time to crop the image!
                $editor->crop($src_x, $src_y, $src_w, $src_h, $dest_width, $dest_height);

                // Now let's save the image
                $saved = $editor->save($dest_file_name);

                // Get resized image information
                $resized_url = str_replace(wp_basename($url), wp_basename($saved['path']), $url);
                $resized_width = $saved['width'];
                $resized_height = $saved['height'];
                $resized_type = $saved['mime-type'];

                // Add the resized dimensions to original image metadata (so we can delete our resized images when the original image is delete from the Media Library)
                if ($get_attachment) {
                    $metadata = wp_get_attachment_metadata($get_attachment[0]->ID);
                    if (isset($metadata['image_meta'])) {
                        $metadata['image_meta']['resized_images'][] = $resized_width . 'x' . $resized_height;
                        wp_update_attachment_metadata($get_attachment[0]->ID, $metadata);
                    }
                }

                // Create the image array
                $image_array = array(
                    'url' => $resized_url,
                    'width' => $resized_width,
                    'height' => $resized_height,
                    'type' => $resized_type
                );
            } else {
                $image_array = array(
                    'url' => str_replace(wp_basename($url), wp_basename($dest_file_name), $url),
                    'width' => $dest_width,
                    'height' => $dest_height,
                    'type' => $ext
                );
            }

            // Return image array
            return $image_array;
        }

        /**
         * Image resize by id
         */
        public static function jobportal_image_resize_id($images_id, $width = NULL, $height = NULL, $crop = true, $retina = false)
        {
            $output = '';
            $image_src = wp_get_attachment_image_src($images_id, 'full');
            if ($image_src) {
                $resize = self::jobportal_image_resize_url($image_src[0], $width, $height, $crop, $retina);
                if ($resize != null && is_array($resize)) {
                    $output = $resize['url'];
                }
            }
            return $output;
        }

        /**
         * Delete resized images
         */
        public static function jobportal_delete_resized_images($post_id)
        {
            // Get attachment image metadata
            $metadata = wp_get_attachment_metadata($post_id);
            if (!$metadata)
                return;

            // Do some bailing if we cannot continue
            if (!isset($metadata['file']) || !isset($metadata['image_meta']['resized_images']))
                return;
            $pathinfo = pathinfo($metadata['file']);
            $resized_images = $metadata['image_meta']['resized_images'];

            // Get Wordpress uploads directory (and bail if it doesn't exist)
            $wp_upload_dir = wp_upload_dir();
            $upload_dir = $wp_upload_dir['basedir'];
            if (!is_dir($upload_dir))
                return;

            // Delete the resized images
            foreach ($resized_images as $dims) {

                // Get the resized images filename
                $file = $upload_dir . '/' . $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '-' . $dims . '.' . $pathinfo['extension'];

                // Delete the resized image
                @unlink($file);
            }
        }

        /**
         * Phone prefix code
         */
        public static function phone_prefix_code()
        {
            return array(
                'ax' => array(
                    'name' => esc_html__('Åland Islands', 'jobportal'),
                    'code' => '+358',
                ),
                'af' => array(
                    'name' => esc_html__('Afghanistan', 'jobportal'),
                    'code' => '+93',
                ),
                'al' => array(
                    'name' => esc_html__('Albania', 'jobportal'),
                    'code' => '+355',
                ),
                'dz' => array(
                    'name' => esc_html__('Algeria', 'jobportal'),
                    'code' => '+213',
                ),
                'as' => array(
                    'name' => esc_html__('American Samoa', 'jobportal'),
                    'code' => '+1684',
                ),
                'ad' => array(
                    'name' => esc_html__('Andorra', 'jobportal'),
                    'code' => '+376',
                ),
                'ao' => array(
                    'name' => esc_html__('Angola', 'jobportal'),
                    'code' => '+244',
                ),
                'ai' => array(
                    'name' => esc_html__('Anguilla', 'jobportal'),
                    'code' => '+1264',
                ),
                'ag' => array(
                    'name' => esc_html__('Antigua and Barbuda', 'jobportal'),
                    'code' => '+1268',
                ),
                'ar' => array(
                    'name' => esc_html__('Argentina', 'jobportal'),
                    'code' => '+54',
                ),
                'am' => array(
                    'name' => esc_html__('Armenia', 'jobportal'),
                    'code' => '+374',
                ),
                'aw' => array(
                    'name' => esc_html__('Aruba', 'jobportal'),
                    'code' => '+297',
                ),
                'au' => array(
                    'name' => esc_html__('Australia', 'jobportal'),
                    'code' => '+61',
                ),
                'at' => array(
                    'name' => esc_html__('Austria', 'jobportal'),
                    'code' => '+43',
                ),
                'az' => array(
                    'name' => esc_html__('Azerbaijan', 'jobportal'),
                    'code' => '+994',
                ),
                'bs' => array(
                    'name' => esc_html__('Bahamas', 'jobportal'),
                    'code' => '+1242',
                ),
                'bh' => array(
                    'name' => esc_html__('Bahrain', 'jobportal'),
                    'code' => '+973',
                ),
                'bd' => array(
                    'name' => esc_html__('Bangladesh', 'jobportal'),
                    'code' => '+880',
                ),
                'bb' => array(
                    'name' => esc_html__('Barbados', 'jobportal'),
                    'code' => '+1246',
                ),
                'by' => array(
                    'name' => esc_html__('Belarus', 'jobportal'),
                    'code' => '+375',
                ),
                'be' => array(
                    'name' => esc_html__('Belgium', 'jobportal'),
                    'code' => '+32',
                ),
                'bz' => array(
                    'name' => esc_html__('Belize', 'jobportal'),
                    'code' => '+501',
                ),
                'bj' => array(
                    'name' => esc_html__('Benin', 'jobportal'),
                    'code' => '+229',
                ),
                'bm' => array(
                    'name' => esc_html__('Bermuda', 'jobportal'),
                    'code' => '+1441',
                ),
                'bt' => array(
                    'name' => esc_html__('Bhutan', 'jobportal'),
                    'code' => '+975',
                ),
                'bo' => array(
                    'name' => esc_html__('Bolivia', 'jobportal'),
                    'code' => '+591',
                ),
                'ba' => array(
                    'name' => esc_html__('Bosnia and Herzegovina', 'jobportal'),
                    'code' => '+387',
                ),
                'bw' => array(
                    'name' => esc_html__('Botswana', 'jobportal'),
                    'code' => '+267',
                ),
                'br' => array(
                    'name' => esc_html__('Brazil', 'jobportal'),
                    'code' => '+55',
                ),
                'io' => array(
                    'name' => esc_html__('British Indian Ocean Territory', 'jobportal'),
                    'code' => '+246',
                ),
                'vg' => array(
                    'name' => esc_html__('British Virgin Islands', 'jobportal'),
                    'code' => '+1284',
                ),
                'bn' => array(
                    'name' => esc_html__('Brunei', 'jobportal'),
                    'code' => '+673',
                ),
                'bg' => array(
                    'name' => esc_html__('Bulgaria', 'jobportal'),
                    'code' => '+359',
                ),
                'bf' => array(
                    'name' => esc_html__('Burkina Faso', 'jobportal'),
                    'code' => '+226',
                ),
                'bi' => array(
                    'name' => esc_html__('Burundi', 'jobportal'),
                    'code' => '+257',
                ),
                'kh' => array(
                    'name' => esc_html__('Cambodia', 'jobportal'),
                    'code' => '+855',
                ),
                'cm' => array(
                    'name' => esc_html__('Cameroon', 'jobportal'),
                    'code' => '+237',
                ),
                'ca' => array(
                    'name' => esc_html__('Canada', 'jobportal'),
                    'code' => '+1',
                ),
                'cv' => array(
                    'name' => esc_html__('Cape Verde', 'jobportal'),
                    'code' => '+238',
                ),
                'bq' => array(
                    'name' => esc_html__('Caribbean Netherlands', 'jobportal'),
                    'code' => '+599',
                ),
                'ky' => array(
                    'name' => esc_html__('Cayman Islands', 'jobportal'),
                    'code' => '+1345',
                ),
                'cf' => array(
                    'name' => esc_html__('Central African Republic', 'jobportal'),
                    'code' => '+236',
                ),
                'td' => array(
                    'name' => esc_html__('Chad', 'jobportal'),
                    'code' => '+235',
                ),
                'cl' => array(
                    'name' => esc_html__('Chile', 'jobportal'),
                    'code' => '+56',
                ),
                'cn' => array(
                    'name' => esc_html__('China', 'jobportal'),
                    'code' => '+86',
                ),
                'cx' => array(
                    'name' => esc_html__('Christmas Island', 'jobportal'),
                    'code' => '+61',
                ),
                'co' => array(
                    'name' => esc_html__('Colombia', 'jobportal'),
                    'code' => '+57',
                ),
                'km' => array(
                    'name' => esc_html__('Comoros', 'jobportal'),
                    'code' => '+269',
                ),
                'cd' => array(
                    'name' => esc_html__('Congo DRC', 'jobportal'),
                    'code' => '+243',
                ),
                'cg' => array(
                    'name' => esc_html__('Congo Republic', 'jobportal'),
                    'code' => '+242',
                ),
                'ck' => array(
                    'name' => esc_html__('Cook Islands', 'jobportal'),
                    'code' => '+682',
                ),
                'cr' => array(
                    'name' => esc_html__('Costa Rica', 'jobportal'),
                    'code' => '+506',
                ),
                'ci' => array(
                    'name' => esc_html__('Côte d’Ivoire', 'jobportal'),
                    'code' => '+225',
                ),
                'hr' => array(
                    'name' => esc_html__('Croatia', 'jobportal'),
                    'code' => '+385',
                ),
                'cu' => array(
                    'name' => esc_html__('Cuba', 'jobportal'),
                    'code' => '+53',
                ),
                'cw' => array(
                    'name' => esc_html__('Curaçao', 'jobportal'),
                    'code' => '+599',
                ),
                'cy' => array(
                    'name' => esc_html__('Cyprus', 'jobportal'),
                    'code' => '+357',
                ),
                'cz' => array(
                    'name' => esc_html__('Czech Republic', 'jobportal'),
                    'code' => '+420',
                ),
                'dk' => array(
                    'name' => esc_html__('Denmark', 'jobportal'),
                    'code' => '+45',
                ),
                'dj' => array(
                    'name' => esc_html__('Djibouti', 'jobportal'),
                    'code' => '+253',
                ),
                'dm' => array(
                    'name' => esc_html__('Dominica', 'jobportal'),
                    'code' => '+1767',
                ),
                'do' => array(
                    'name' => esc_html__('Dominican Republic', 'jobportal'),
                    'code' => '+1',
                ),
                'ec' => array(
                    'name' => esc_html__('Ecuador', 'jobportal'),
                    'code' => '+593',
                ),
                'eg' => array(
                    'name' => esc_html__('Egypt', 'jobportal'),
                    'code' => '+20',
                ),
                'sv' => array(
                    'name' => esc_html__('El Salvador', 'jobportal'),
                    'code' => '+503',
                ),
                'gq' => array(
                    'name' => esc_html__('Equatorial Guinea', 'jobportal'),
                    'code' => '+240',
                ),
                'er' => array(
                    'name' => esc_html__('Eritrea', 'jobportal'),
                    'code' => '+291',
                ),
                'ee' => array(
                    'name' => esc_html__('Estonia', 'jobportal'),
                    'code' => '+372',
                ),
                'et' => array(
                    'name' => esc_html__('Ethiopia', 'jobportal'),
                    'code' => '+251',
                ),
                'fk' => array(
                    'name' => esc_html__('Falkland Islands', 'jobportal'),
                    'code' => '+500',
                ),
                'fo' => array(
                    'name' => esc_html__('Faroe Islands', 'jobportal'),
                    'code' => '+298',
                ),
                'fj' => array(
                    'name' => esc_html__('Fiji', 'jobportal'),
                    'code' => '+679',
                ),
                'fi' => array(
                    'name' => esc_html__('Finland', 'jobportal'),
                    'code' => '+358',
                ),
                'fr' => array(
                    'name' => esc_html__('France', 'jobportal'),
                    'code' => '+33',
                ),
                'gf' => array(
                    'name' => esc_html__('French Guiana', 'jobportal'),
                    'code' => '+594',
                ),
                'pf' => array(
                    'name' => esc_html__('French Polynesia', 'jobportal'),
                    'code' => '+689',
                ),
                'ga' => array(
                    'name' => esc_html__('Gabon', 'jobportal'),
                    'code' => '+241',
                ),
                'gm' => array(
                    'name' => esc_html__('Gambia', 'jobportal'),
                    'code' => '+220',
                ),
                'ge' => array(
                    'name' => esc_html__('Georgia', 'jobportal'),
                    'code' => '+995',
                ),
                'de' => array(
                    'name' => esc_html__('Germany', 'jobportal'),
                    'code' => '+49',
                ),
                'gh' => array(
                    'name' => esc_html__('Ghana', 'jobportal'),
                    'code' => '+233',
                ),
                'gi' => array(
                    'name' => esc_html__('Gibraltar', 'jobportal'),
                    'code' => '+350',
                ),
                'gr' => array(
                    'name' => esc_html__('Greece', 'jobportal'),
                    'code' => '+30',
                ),
                'gl' => array(
                    'name' => esc_html__('Greenland', 'jobportal'),
                    'code' => '+299',
                ),
                'gd' => array(
                    'name' => esc_html__('Grenada', 'jobportal'),
                    'code' => '+1473',
                ),
                'gp' => array(
                    'name' => esc_html__('Guadeloupe', 'jobportal'),
                    'code' => '+590',
                ),
                'gu' => array(
                    'name' => esc_html__('Guam', 'jobportal'),
                    'code' => '+1671',
                ),
                'gt' => array(
                    'name' => esc_html__('Guatemala', 'jobportal'),
                    'code' => '+502',
                ),
                'gg' => array(
                    'name' => esc_html__('Guernsey', 'jobportal'),
                    'code' => '+44',
                ),
                'gn' => array(
                    'name' => esc_html__('Guinea', 'jobportal'),
                    'code' => '+224',
                ),
                'gw' => array(
                    'name' => esc_html__('Guinea-Bissau', 'jobportal'),
                    'code' => '+245',
                ),
                'gy' => array(
                    'name' => esc_html__('Guyana', 'jobportal'),
                    'code' => '+592',
                ),
                'ht' => array(
                    'name' => esc_html__('Haiti', 'jobportal'),
                    'code' => '+509',
                ),
                'hn' => array(
                    'name' => esc_html__('Honduras', 'jobportal'),
                    'code' => '+504',
                ),
                'hk' => array(
                    'name' => esc_html__('Hong Kong', 'jobportal'),
                    'code' => '+852',
                ),
                'hu' => array(
                    'name' => esc_html__('Hungary', 'jobportal'),
                    'code' => '+36',
                ),
                'is' => array(
                    'name' => esc_html__('Iceland', 'jobportal'),
                    'code' => '+354',
                ),
                'in' => array(
                    'name' => esc_html__('India', 'jobportal'),
                    'code' => '+91',
                ),
                'id' => array(
                    'name' => esc_html__('Indonesia', 'jobportal'),
                    'code' => '+62',
                ),
                'ir' => array(
                    'name' => esc_html__('Iran', 'jobportal'),
                    'code' => '+98',
                ),
                'iq' => array(
                    'name' => esc_html__('Iraq', 'jobportal'),
                    'code' => '+964',
                ),
                'ie' => array(
                    'name' => esc_html__('Ireland', 'jobportal'),
                    'code' => '+353',
                ),
                'im' => array(
                    'name' => esc_html__('Isle of Man', 'jobportal'),
                    'code' => '+44',
                ),
                'il' => array(
                    'name' => esc_html__('Israel', 'jobportal'),
                    'code' => '+972',
                ),
                'it' => array(
                    'name' => esc_html__('Italy', 'jobportal'),
                    'code' => '+39',
                ),
                'jm' => array(
                    'name' => esc_html__('Jamaica', 'jobportal'),
                    'code' => '+1876',
                ),
                'jp' => array(
                    'name' => esc_html__('Japan', 'jobportal'),
                    'code' => '+81',
                ),
                'je' => array(
                    'name' => esc_html__('Jersey', 'jobportal'),
                    'code' => '+44',
                ),
                'jo' => array(
                    'name' => esc_html__('Jordan', 'jobportal'),
                    'code' => '+962',
                ),
                'kz' => array(
                    'name' => esc_html__('Kazakhstan', 'jobportal'),
                    'code' => '+7',
                ),
                'ke' => array(
                    'name' => esc_html__('Kenya', 'jobportal'),
                    'code' => '+254',
                ),
                'ki' => array(
                    'name' => esc_html__('Kiribati', 'jobportal'),
                    'code' => '+686',
                ),
                'xk' => array(
                    'name' => esc_html__('Kosovo', 'jobportal'),
                    'code' => '+383',
                ),
                'kw' => array(
                    'name' => esc_html__('Kuwait', 'jobportal'),
                    'code' => '+965',
                ),
                'kg' => array(
                    'name' => esc_html__('Kyrgyzstan', 'jobportal'),
                    'code' => '+996',
                ),
                'la' => array(
                    'name' => esc_html__('Laos', 'jobportal'),
                    'code' => '+856',
                ),
                'lv' => array(
                    'name' => esc_html__('Latvia', 'jobportal'),
                    'code' => '+371',
                ),
                'lb' => array(
                    'name' => esc_html__('Lebanon', 'jobportal'),
                    'code' => '+961',
                ),
                'ls' => array(
                    'name' => esc_html__('Lesotho', 'jobportal'),
                    'code' => '+266',
                ),
                'lr' => array(
                    'name' => esc_html__('Liberia', 'jobportal'),
                    'code' => '+231',
                ),
                'ly' => array(
                    'name' => esc_html__('Libya', 'jobportal'),
                    'code' => '+218',
                ),
                'li' => array(
                    'name' => esc_html__('Liechtenstein', 'jobportal'),
                    'code' => '+423',
                ),
                'lt' => array(
                    'name' => esc_html__('Lithuania', 'jobportal'),
                    'code' => '+370',
                ),
                'lu' => array(
                    'name' => esc_html__('Luxembourg', 'jobportal'),
                    'code' => '+352',
                ),
                'mo' => array(
                    'name' => esc_html__('Macau', 'jobportal'),
                    'code' => '+853',
                ),
                'mk' => array(
                    'name' => esc_html__('Macedonia', 'jobportal'),
                    'code' => '+389',
                ),
                'mg' => array(
                    'name' => esc_html__('Madagascar', 'jobportal'),
                    'code' => '+261',
                ),
                'mw' => array(
                    'name' => esc_html__('Malawi', 'jobportal'),
                    'code' => '+265',
                ),
                'my' => array(
                    'name' => esc_html__('Malaysia', 'jobportal'),
                    'code' => '+60',
                ),
                'mv' => array(
                    'name' => esc_html__('Maldives', 'jobportal'),
                    'code' => '+960',
                ),
                'ml' => array(
                    'name' => esc_html__('Mali', 'jobportal'),
                    'code' => '+223',
                ),
                'mt' => array(
                    'name' => esc_html__('Malta', 'jobportal'),
                    'code' => '+356',
                ),
                'mh' => array(
                    'name' => esc_html__('Marshall Islands', 'jobportal'),
                    'code' => '+692',
                ),
                'mq' => array(
                    'name' => esc_html__('Martinique', 'jobportal'),
                    'code' => '+596',
                ),
                'mr' => array(
                    'name' => esc_html__('Mauritania', 'jobportal'),
                    'code' => '+222',
                ),
                'mu' => array(
                    'name' => esc_html__('Mauritius', 'jobportal'),
                    'code' => '+230',
                ),
                'yt' => array(
                    'name' => esc_html__('Mayotte', 'jobportal'),
                    'code' => '+262',
                ),
                'mx' => array(
                    'name' => esc_html__('Mexico', 'jobportal'),
                    'code' => '+52',
                ),
                'fm' => array(
                    'name' => esc_html__('Micronesia', 'jobportal'),
                    'code' => '+691',
                ),
                'md' => array(
                    'name' => esc_html__('Moldova', 'jobportal'),
                    'code' => '+373',
                ),
                'mc' => array(
                    'name' => esc_html__('Monaco', 'jobportal'),
                    'code' => '+377',
                ),
                'mn' => array(
                    'name' => esc_html__('Mongolia', 'jobportal'),
                    'code' => '+976',
                ),
                'me' => array(
                    'name' => esc_html__('Montenegro', 'jobportal'),
                    'code' => '+382',
                ),
                'ms' => array(
                    'name' => esc_html__('Montserrat', 'jobportal'),
                    'code' => '+1664',
                ),
                'ma' => array(
                    'name' => esc_html__('Morocco', 'jobportal'),
                    'code' => '+212',
                ),
                'mz' => array(
                    'name' => esc_html__('Mozambique', 'jobportal'),
                    'code' => '+258',
                ),
                'mm' => array(
                    'name' => esc_html__('Myanmar', 'jobportal'),
                    'code' => '+95',
                ),
                'na' => array(
                    'name' => esc_html__('Namibia', 'jobportal'),
                    'code' => '+264',
                ),
                'nr' => array(
                    'name' => esc_html__('Nauru', 'jobportal'),
                    'code' => '+674',
                ),
                'np' => array(
                    'name' => esc_html__('Nepal', 'jobportal'),
                    'code' => '+977',
                ),
                'nl' => array(
                    'name' => esc_html__('Netherlands', 'jobportal'),
                    'code' => '+31',
                ),
                'nc' => array(
                    'name' => esc_html__('New Caledonia', 'jobportal'),
                    'code' => '+687',
                ),
                'nz' => array(
                    'name' => esc_html__('New Zealand', 'jobportal'),
                    'code' => '+64',
                ),
                'ni' => array(
                    'name' => esc_html__('Nicaragua', 'jobportal'),
                    'code' => '+505',
                ),
                'ne' => array(
                    'name' => esc_html__('Niger', 'jobportal'),
                    'code' => '+227',
                ),
                'ng' => array(
                    'name' => esc_html__('Nigeria', 'jobportal'),
                    'code' => '+234',
                ),
                'nu' => array(
                    'name' => esc_html__('Niue', 'jobportal'),
                    'code' => '+683',
                ),
                'nf' => array(
                    'name' => esc_html__('Norfolk Island', 'jobportal'),
                    'code' => '+672',
                ),
                'kp' => array(
                    'name' => esc_html__('North Korea', 'jobportal'),
                    'code' => '+850',
                ),
                'mp' => array(
                    'name' => esc_html__('Northern Mariana Islands', 'jobportal'),
                    'code' => '+1670',
                ),
                'no' => array(
                    'name' => esc_html__('Norway', 'jobportal'),
                    'code' => '+47',
                ),
                'om' => array(
                    'name' => esc_html__('Oman', 'jobportal'),
                    'code' => '+968',
                ),
                'pk' => array(
                    'name' => esc_html__('Pakistan', 'jobportal'),
                    'code' => '+92',
                ),
                'pw' => array(
                    'name' => esc_html__('Palau', 'jobportal'),
                    'code' => '+680',
                ),
                'ps' => array(
                    'name' => esc_html__('Palestine', 'jobportal'),
                    'code' => '+970',
                ),
                'pa' => array(
                    'name' => esc_html__('Panama', 'jobportal'),
                    'code' => '+507',
                ),
                'pg' => array(
                    'name' => esc_html__('Papua New Guinea', 'jobportal'),
                    'code' => '+675',
                ),
                'py' => array(
                    'name' => esc_html__('Paraguay', 'jobportal'),
                    'code' => '+595',
                ),
                'pe' => array(
                    'name' => esc_html__('Peru', 'jobportal'),
                    'code' => '+51',
                ),
                'ph' => array(
                    'name' => esc_html__('Philippines', 'jobportal'),
                    'code' => '+63',
                ),
                'pl' => array(
                    'name' => esc_html__('Poland', 'jobportal'),
                    'code' => '+48',
                ),
                'pt' => array(
                    'name' => esc_html__('Portugal', 'jobportal'),
                    'code' => '+351',
                ),
                'qa' => array(
                    'name' => esc_html__('Qatar', 'jobportal'),
                    'code' => '+974',
                ),
                're' => array(
                    'name' => esc_html__('Réunion', 'jobportal'),
                    'code' => '+262',
                ),
                'ro' => array(
                    'name' => esc_html__('Romania', 'jobportal'),
                    'code' => '+40',
                ),
                'ru' => array(
                    'name' => esc_html__('Russia', 'jobportal'),
                    'code' => '+7',
                ),
                'rw' => array(
                    'name' => esc_html__('Rwanda', 'jobportal'),
                    'code' => '+250',
                ),
                'bl' => array(
                    'name' => esc_html__('Saint Barthélemy', 'jobportal'),
                    'code' => '+590',
                ),
                'sh' => array(
                    'name' => esc_html__('Saint Helena', 'jobportal'),
                    'code' => '+290',
                ),
                'kn' => array(
                    'name' => esc_html__('Saint Kitts and Nevis', 'jobportal'),
                    'code' => '+1869',
                ),
                'lc' => array(
                    'name' => esc_html__('Saint Lucia', 'jobportal'),
                    'code' => '+1758',
                ),
                'mf' => array(
                    'name' => esc_html__('Saint Martin', 'jobportal'),
                    'code' => '+590',
                ),
                'pm' => array(
                    'name' => esc_html__('Saint Pierre and Miquelon', 'jobportal'),
                    'code' => '+508',
                ),
                'vc' => array(
                    'name' => esc_html__('Saint Vincent and the Grenadines', 'jobportal'),
                    'code' => '+1784',
                ),
                'ws' => array(
                    'name' => esc_html__('Samoa', 'jobportal'),
                    'code' => '+685',
                ),
                'sm' => array(
                    'name' => esc_html__('San Marino', 'jobportal'),
                    'code' => '+378',
                ),
                'st' => array(
                    'name' => esc_html__('São Tomé and Príncipe', 'jobportal'),
                    'code' => '+239',
                ),
                'sa' => array(
                    'name' => esc_html__('Saudi Arabia', 'jobportal'),
                    'code' => '+966',
                ),
                'sn' => array(
                    'name' => esc_html__('Senegal', 'jobportal'),
                    'code' => '+221',
                ),
                'rs' => array(
                    'name' => esc_html__('Serbia', 'jobportal'),
                    'code' => '+381',
                ),
                'sc' => array(
                    'name' => esc_html__('Seychelles', 'jobportal'),
                    'code' => '+248',
                ),
                'sl' => array(
                    'name' => esc_html__('Sierra Leone', 'jobportal'),
                    'code' => '+232',
                ),
                'sg' => array(
                    'name' => esc_html__('Singapore', 'jobportal'),
                    'code' => '+65',
                ),
                'sx' => array(
                    'name' => esc_html__('Sint Maarten', 'jobportal'),
                    'code' => '+1721',
                ),
                'sk' => array(
                    'name' => esc_html__('Slovakia', 'jobportal'),
                    'code' => '+421',
                ),
                'si' => array(
                    'name' => esc_html__('Slovenia', 'jobportal'),
                    'code' => '+386',
                ),
                'sb' => array(
                    'name' => esc_html__('Solomon Islands', 'jobportal'),
                    'code' => '+677',
                ),
                'so' => array(
                    'name' => esc_html__('Somalia', 'jobportal'),
                    'code' => '+252',
                ),
                'za' => array(
                    'name' => esc_html__('South Africa', 'jobportal'),
                    'code' => '+27',
                ),
                'kr' => array(
                    'name' => esc_html__('South Korea', 'jobportal'),
                    'code' => '+82',
                ),
                'ss' => array(
                    'name' => esc_html__('South Sudan', 'jobportal'),
                    'code' => '+211',
                ),
                'es' => array(
                    'name' => esc_html__('Spain', 'jobportal'),
                    'code' => '+34',
                ),
                'lk' => array(
                    'name' => esc_html__('Sri Lanka', 'jobportal'),
                    'code' => '+94',
                ),
                'sd' => array(
                    'name' => esc_html__('Sudan', 'jobportal'),
                    'code' => '+249',
                ),
                'sr' => array(
                    'name' => esc_html__('Suriname', 'jobportal'),
                    'code' => '+597',
                ),
                'sj' => array(
                    'name' => esc_html__('Svalbard and Jan Mayen', 'jobportal'),
                    'code' => '+47',
                ),
                'sz' => array(
                    'name' => esc_html__('Swaziland', 'jobportal'),
                    'code' => '+268',
                ),
                'se' => array(
                    'name' => esc_html__('Sweden', 'jobportal'),
                    'code' => '+46',
                ),
                'ch' => array(
                    'name' => esc_html__('Switzerland', 'jobportal'),
                    'code' => '+41',
                ),
                'sy' => array(
                    'name' => esc_html__('Syria', 'jobportal'),
                    'code' => '+963',
                ),
                'tw' => array(
                    'name' => esc_html__('Taiwan', 'jobportal'),
                    'code' => '+886',
                ),
                'tj' => array(
                    'name' => esc_html__('Tajikistan', 'jobportal'),
                    'code' => '+992',
                ),
                'tz' => array(
                    'name' => esc_html__('Tanzania', 'jobportal'),
                    'code' => '+255',
                ),
                'th' => array(
                    'name' => esc_html__('Thailand', 'jobportal'),
                    'code' => '+66',
                ),
                'tl' => array(
                    'name' => esc_html__('Timor-Leste', 'jobportal'),
                    'code' => '+670',
                ),
                'tg' => array(
                    'name' => esc_html__('Togo', 'jobportal'),
                    'code' => '+228',
                ),
                'tk' => array(
                    'name' => esc_html__('Tokelau', 'jobportal'),
                    'code' => '+690',
                ),
                'tk' => array(
                    'name' => esc_html__('Tokelau', 'jobportal'),
                    'code' => '+690',
                ),
                'to' => array(
                    'name' => esc_html__('Tonga', 'jobportal'),
                    'code' => '+676',
                ),
                'tt' => array(
                    'name' => esc_html__('Trinidad and Tobago', 'jobportal'),
                    'code' => '+1868',
                ),
                'tn' => array(
                    'name' => esc_html__('Tunisia', 'jobportal'),
                    'code' => '+216',
                ),
                'tr' => array(
                    'name' => esc_html__('Turkey', 'jobportal'),
                    'code' => '+90',
                ),
                'tm' => array(
                    'name' => esc_html__('Turkmenistan', 'jobportal'),
                    'code' => '+993',
                ),
                'tc' => array(
                    'name' => esc_html__('Turks and Caicos Islands', 'jobportal'),
                    'code' => '+1649',
                ),
                'tv' => array(
                    'name' => esc_html__('Tuvalu', 'jobportal'),
                    'code' => '+688',
                ),
                'ug' => array(
                    'name' => esc_html__('Uganda', 'jobportal'),
                    'code' => '+256',
                ),
                'ua' => array(
                    'name' => esc_html__('Ukraine', 'jobportal'),
                    'code' => '+380',
                ),
                'ae' => array(
                    'name' => esc_html__('United Arab Emirates', 'jobportal'),
                    'code' => '+971',
                ),
                'gb' => array(
                    'name' => esc_html__('United Kingdom', 'jobportal'),
                    'code' => '+44',
                ),
                'us' => array(
                    'name' => esc_html__('United States', 'jobportal'),
                    'code' => '+1',
                ),
                'uy' => array(
                    'name' => esc_html__('Uruguay', 'jobportal'),
                    'code' => '+598',
                ),
                'uz' => array(
                    'name' => esc_html__('Uzbekistan', 'jobportal'),
                    'code' => '+998',
                ),
                'vu' => array(
                    'name' => esc_html__('Vanuatu', 'jobportal'),
                    'code' => '+678',
                ),
                'va' => array(
                    'name' => esc_html__('Vatican City', 'jobportal'),
                    'code' => '+39',
                ),
                've' => array(
                    'name' => esc_html__('Venezuela', 'jobportal'),
                    'code' => '+58',
                ),
                'vn' => array(
                    'name' => esc_html__('Vietnam', 'jobportal'),
                    'code' => '+84',
                ),
                'wf' => array(
                    'name' => esc_html__('Wallis and Futuna', 'jobportal'),
                    'code' => '+681',
                ),
                'eh' => array(
                    'name' => esc_html__('Western Sahara', 'jobportal'),
                    'code' => '+212',
                ),
                'ye' => array(
                    'name' => esc_html__('Yemen', 'jobportal'),
                    'code' => '+967',
                ),
                'zm' => array(
                    'name' => esc_html__('Zambia', 'jobportal'),
                    'code' => '+260',
                ),
                'zw' => array(
                    'name' => esc_html__('Zimbabwe', 'jobportal'),
                    'code' => '+263',
                ),
            );
        }

        public static function get_prefix_key_from_phone(string $phone, array $prefix_code, ?string $default_prefix = null): ?string
        {
            if (empty($phone) || !preg_match('/^[0-9+]+$/', $phone)) {
                return $default_prefix;
            }
            $phone = preg_replace('/[^0-9+]/', '', $phone);
            if ($phone[0] !== '+' && $default_prefix && isset($prefix_code[$default_prefix])) {
                $phone = $prefix_code[$default_prefix]['code'] . ltrim($phone, '0');
            }
            static $sorted_prefix_code = [];
            $prefix_hash = md5(serialize($prefix_code));
            if (!isset($sorted_prefix_code[$prefix_hash])) {
                $sorted_prefix_code[$prefix_hash] = $prefix_code;
                uasort($sorted_prefix_code[$prefix_hash], fn($a, $b) => strlen($b['code']) <=> strlen($a['code']));
            }
            foreach ($sorted_prefix_code[$prefix_hash] as $key => $value) {
                if (str_starts_with($phone, $value['code'])) {
                    $phone_length = strlen($phone) - strlen($value['code']);
                    $allowed_lengths = (array) ($value['length'] ?? []);
                    if (empty($allowed_lengths) || in_array($phone_length, $allowed_lengths)) {
                        return $key;
                    }
                }
            }
            return $default_prefix;
        }
    }
}
