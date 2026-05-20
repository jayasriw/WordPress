<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Helper functions
 */
if (!class_exists('Civi_Helper')) {

    class Civi_Helper
    {

        /**
         * The constructor.
         */
        function __construct()
        {
            add_action('delete_attachment', array($this, 'civi_delete_resized_images'));

            add_filter('body_class', array($this, 'civi_body_class'));
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
        public static function civi_get_option($key, $default = '')
        {
            $option = '';
            if (class_exists('Civi_Framework')) {
                $option = civi_get_option($key, $default);
            }
            return (isset($option)) ? $option : $default;
        }

        /**
         * Clean Variable
         */
        public static function civi_clean($var)
        {
            if (is_array($var)) {
                return array_map('civi_clean', $var);
            } else {
                return is_scalar($var) ? sanitize_text_field($var) : $var;
            }
        }

        /**
         * Get Setting
         */
        public static function civi_body_class($classes)
        {

            $enable_rtl_mode  = Civi_Helper::civi_get_option('enable_rtl_mode', 0);

            if (is_rtl() || $enable_rtl_mode) {
                $classes[] = 'rtl';
            }

            return $classes;
        }

        /**
         * Check has shortcode
         */
        public static function civi_page_shortcode($shortcode = NULL)
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
        public static function civi_send_email($email, $email_type, $args = array())
        {

            $content = Civi_Helper::civi_get_option($email_type, '');
            $subject = Civi_Helper::civi_get_option('subject_' . $email_type, '');

            if (function_exists('icl_translate')) {
                $content = icl_translate('civi', 'civi_email_' . $content, $content);
                $subject = icl_translate('civi', 'civi_email_subject_' . $subject, $subject);
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
            civi_get_template("mail/mail.php", array(
                'content' => $content,
            ));
            $message = ob_get_clean();

            $headers = apply_filters('civi_contact_mail_header', array('From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>', 'Content-Type: text/html; charset=UTF-8'));

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
        public static function civi_kses_allowed_html()
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

        public static function civi_image_captcha($captcha)
        {

            if (empty($captcha)) return;

            // Generate a 50x24 standard captcha image
            $im = imagecreatetruecolor(50, 40);

            // Accent color
            $bg = imagecolorallocate($im, 0, 116, 86);
            $bg = apply_filters('civi_image_captcha_bg_color', $bg, $im);

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
        public static function civi_image_resize($data, $image_size)
        {
            if (preg_match('/\d+x\d+/', $image_size)) {
                $image_sizes = explode('x', $image_size);
                $image_src  = self::civi_image_resize_id($data, $image_sizes[0], $image_sizes[1], true);
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
        public static function civi_image_resize_url($url, $width = NULL, $height = NULL, $crop = true, $retina = false)
        {

            global $wpdb;

            if (empty($url))
                return new WP_Error('no_image_url', esc_html__('No image URL has been entered.', 'civi'), $url);

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
                return new WP_Error('bmp_mime_type', esc_html__('Image is BMP. Please use either JPG or PNG.', 'civi'), $url);
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
        public static function civi_image_resize_id($images_id, $width = NULL, $height = NULL, $crop = true, $retina = false)
        {
            $output = '';
            $image_src = wp_get_attachment_image_src($images_id, 'full');
            if ($image_src) {
                $resize = self::civi_image_resize_url($image_src[0], $width, $height, $crop, $retina);
                if ($resize != null && is_array($resize)) {
                    $output = $resize['url'];
                }
            }
            return $output;
        }

        /**
         * Delete resized images
         */
        public static function civi_delete_resized_images($post_id)
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
                    'name' => esc_html__('Åland Islands', 'civi'),
                    'code' => '+358',
                ),
                'af' => array(
                    'name' => esc_html__('Afghanistan', 'civi'),
                    'code' => '+93',
                ),
                'al' => array(
                    'name' => esc_html__('Albania', 'civi'),
                    'code' => '+355',
                ),
                'dz' => array(
                    'name' => esc_html__('Algeria', 'civi'),
                    'code' => '+213',
                ),
                'as' => array(
                    'name' => esc_html__('American Samoa', 'civi'),
                    'code' => '+1684',
                ),
                'ad' => array(
                    'name' => esc_html__('Andorra', 'civi'),
                    'code' => '+376',
                ),
                'ao' => array(
                    'name' => esc_html__('Angola', 'civi'),
                    'code' => '+244',
                ),
                'ai' => array(
                    'name' => esc_html__('Anguilla', 'civi'),
                    'code' => '+1264',
                ),
                'ag' => array(
                    'name' => esc_html__('Antigua and Barbuda', 'civi'),
                    'code' => '+1268',
                ),
                'ar' => array(
                    'name' => esc_html__('Argentina', 'civi'),
                    'code' => '+54',
                ),
                'am' => array(
                    'name' => esc_html__('Armenia', 'civi'),
                    'code' => '+374',
                ),
                'aw' => array(
                    'name' => esc_html__('Aruba', 'civi'),
                    'code' => '+297',
                ),
                'au' => array(
                    'name' => esc_html__('Australia', 'civi'),
                    'code' => '+61',
                ),
                'at' => array(
                    'name' => esc_html__('Austria', 'civi'),
                    'code' => '+43',
                ),
                'az' => array(
                    'name' => esc_html__('Azerbaijan', 'civi'),
                    'code' => '+994',
                ),
                'bs' => array(
                    'name' => esc_html__('Bahamas', 'civi'),
                    'code' => '+1242',
                ),
                'bh' => array(
                    'name' => esc_html__('Bahrain', 'civi'),
                    'code' => '+973',
                ),
                'bd' => array(
                    'name' => esc_html__('Bangladesh', 'civi'),
                    'code' => '+880',
                ),
                'bb' => array(
                    'name' => esc_html__('Barbados', 'civi'),
                    'code' => '+1246',
                ),
                'by' => array(
                    'name' => esc_html__('Belarus', 'civi'),
                    'code' => '+375',
                ),
                'be' => array(
                    'name' => esc_html__('Belgium', 'civi'),
                    'code' => '+32',
                ),
                'bz' => array(
                    'name' => esc_html__('Belize', 'civi'),
                    'code' => '+501',
                ),
                'bj' => array(
                    'name' => esc_html__('Benin', 'civi'),
                    'code' => '+229',
                ),
                'bm' => array(
                    'name' => esc_html__('Bermuda', 'civi'),
                    'code' => '+1441',
                ),
                'bt' => array(
                    'name' => esc_html__('Bhutan', 'civi'),
                    'code' => '+975',
                ),
                'bo' => array(
                    'name' => esc_html__('Bolivia', 'civi'),
                    'code' => '+591',
                ),
                'ba' => array(
                    'name' => esc_html__('Bosnia and Herzegovina', 'civi'),
                    'code' => '+387',
                ),
                'bw' => array(
                    'name' => esc_html__('Botswana', 'civi'),
                    'code' => '+267',
                ),
                'br' => array(
                    'name' => esc_html__('Brazil', 'civi'),
                    'code' => '+55',
                ),
                'io' => array(
                    'name' => esc_html__('British Indian Ocean Territory', 'civi'),
                    'code' => '+246',
                ),
                'vg' => array(
                    'name' => esc_html__('British Virgin Islands', 'civi'),
                    'code' => '+1284',
                ),
                'bn' => array(
                    'name' => esc_html__('Brunei', 'civi'),
                    'code' => '+673',
                ),
                'bg' => array(
                    'name' => esc_html__('Bulgaria', 'civi'),
                    'code' => '+359',
                ),
                'bf' => array(
                    'name' => esc_html__('Burkina Faso', 'civi'),
                    'code' => '+226',
                ),
                'bi' => array(
                    'name' => esc_html__('Burundi', 'civi'),
                    'code' => '+257',
                ),
                'kh' => array(
                    'name' => esc_html__('Cambodia', 'civi'),
                    'code' => '+855',
                ),
                'cm' => array(
                    'name' => esc_html__('Cameroon', 'civi'),
                    'code' => '+237',
                ),
                'ca' => array(
                    'name' => esc_html__('Canada', 'civi'),
                    'code' => '+1',
                ),
                'cv' => array(
                    'name' => esc_html__('Cape Verde', 'civi'),
                    'code' => '+238',
                ),
                'bq' => array(
                    'name' => esc_html__('Caribbean Netherlands', 'civi'),
                    'code' => '+599',
                ),
                'ky' => array(
                    'name' => esc_html__('Cayman Islands', 'civi'),
                    'code' => '+1345',
                ),
                'cf' => array(
                    'name' => esc_html__('Central African Republic', 'civi'),
                    'code' => '+236',
                ),
                'td' => array(
                    'name' => esc_html__('Chad', 'civi'),
                    'code' => '+235',
                ),
                'cl' => array(
                    'name' => esc_html__('Chile', 'civi'),
                    'code' => '+56',
                ),
                'cn' => array(
                    'name' => esc_html__('China', 'civi'),
                    'code' => '+86',
                ),
                'cx' => array(
                    'name' => esc_html__('Christmas Island', 'civi'),
                    'code' => '+61',
                ),
                'co' => array(
                    'name' => esc_html__('Colombia', 'civi'),
                    'code' => '+57',
                ),
                'km' => array(
                    'name' => esc_html__('Comoros', 'civi'),
                    'code' => '+269',
                ),
                'cd' => array(
                    'name' => esc_html__('Congo DRC', 'civi'),
                    'code' => '+243',
                ),
                'cg' => array(
                    'name' => esc_html__('Congo Republic', 'civi'),
                    'code' => '+242',
                ),
                'ck' => array(
                    'name' => esc_html__('Cook Islands', 'civi'),
                    'code' => '+682',
                ),
                'cr' => array(
                    'name' => esc_html__('Costa Rica', 'civi'),
                    'code' => '+506',
                ),
                'ci' => array(
                    'name' => esc_html__('Côte d’Ivoire', 'civi'),
                    'code' => '+225',
                ),
                'hr' => array(
                    'name' => esc_html__('Croatia', 'civi'),
                    'code' => '+385',
                ),
                'cu' => array(
                    'name' => esc_html__('Cuba', 'civi'),
                    'code' => '+53',
                ),
                'cw' => array(
                    'name' => esc_html__('Curaçao', 'civi'),
                    'code' => '+599',
                ),
                'cy' => array(
                    'name' => esc_html__('Cyprus', 'civi'),
                    'code' => '+357',
                ),
                'cz' => array(
                    'name' => esc_html__('Czech Republic', 'civi'),
                    'code' => '+420',
                ),
                'dk' => array(
                    'name' => esc_html__('Denmark', 'civi'),
                    'code' => '+45',
                ),
                'dj' => array(
                    'name' => esc_html__('Djibouti', 'civi'),
                    'code' => '+253',
                ),
                'dm' => array(
                    'name' => esc_html__('Dominica', 'civi'),
                    'code' => '+1767',
                ),
                'do' => array(
                    'name' => esc_html__('Dominican Republic', 'civi'),
                    'code' => '+1',
                ),
                'ec' => array(
                    'name' => esc_html__('Ecuador', 'civi'),
                    'code' => '+593',
                ),
                'eg' => array(
                    'name' => esc_html__('Egypt', 'civi'),
                    'code' => '+20',
                ),
                'sv' => array(
                    'name' => esc_html__('El Salvador', 'civi'),
                    'code' => '+503',
                ),
                'gq' => array(
                    'name' => esc_html__('Equatorial Guinea', 'civi'),
                    'code' => '+240',
                ),
                'er' => array(
                    'name' => esc_html__('Eritrea', 'civi'),
                    'code' => '+291',
                ),
                'ee' => array(
                    'name' => esc_html__('Estonia', 'civi'),
                    'code' => '+372',
                ),
                'et' => array(
                    'name' => esc_html__('Ethiopia', 'civi'),
                    'code' => '+251',
                ),
                'fk' => array(
                    'name' => esc_html__('Falkland Islands', 'civi'),
                    'code' => '+500',
                ),
                'fo' => array(
                    'name' => esc_html__('Faroe Islands', 'civi'),
                    'code' => '+298',
                ),
                'fj' => array(
                    'name' => esc_html__('Fiji', 'civi'),
                    'code' => '+679',
                ),
                'fi' => array(
                    'name' => esc_html__('Finland', 'civi'),
                    'code' => '+358',
                ),
                'fr' => array(
                    'name' => esc_html__('France', 'civi'),
                    'code' => '+33',
                ),
                'gf' => array(
                    'name' => esc_html__('French Guiana', 'civi'),
                    'code' => '+594',
                ),
                'pf' => array(
                    'name' => esc_html__('French Polynesia', 'civi'),
                    'code' => '+689',
                ),
                'ga' => array(
                    'name' => esc_html__('Gabon', 'civi'),
                    'code' => '+241',
                ),
                'gm' => array(
                    'name' => esc_html__('Gambia', 'civi'),
                    'code' => '+220',
                ),
                'ge' => array(
                    'name' => esc_html__('Georgia', 'civi'),
                    'code' => '+995',
                ),
                'de' => array(
                    'name' => esc_html__('Germany', 'civi'),
                    'code' => '+49',
                ),
                'gh' => array(
                    'name' => esc_html__('Ghana', 'civi'),
                    'code' => '+233',
                ),
                'gi' => array(
                    'name' => esc_html__('Gibraltar', 'civi'),
                    'code' => '+350',
                ),
                'gr' => array(
                    'name' => esc_html__('Greece', 'civi'),
                    'code' => '+30',
                ),
                'gl' => array(
                    'name' => esc_html__('Greenland', 'civi'),
                    'code' => '+299',
                ),
                'gd' => array(
                    'name' => esc_html__('Grenada', 'civi'),
                    'code' => '+1473',
                ),
                'gp' => array(
                    'name' => esc_html__('Guadeloupe', 'civi'),
                    'code' => '+590',
                ),
                'gu' => array(
                    'name' => esc_html__('Guam', 'civi'),
                    'code' => '+1671',
                ),
                'gt' => array(
                    'name' => esc_html__('Guatemala', 'civi'),
                    'code' => '+502',
                ),
                'gg' => array(
                    'name' => esc_html__('Guernsey', 'civi'),
                    'code' => '+44',
                ),
                'gn' => array(
                    'name' => esc_html__('Guinea', 'civi'),
                    'code' => '+224',
                ),
                'gw' => array(
                    'name' => esc_html__('Guinea-Bissau', 'civi'),
                    'code' => '+245',
                ),
                'gy' => array(
                    'name' => esc_html__('Guyana', 'civi'),
                    'code' => '+592',
                ),
                'ht' => array(
                    'name' => esc_html__('Haiti', 'civi'),
                    'code' => '+509',
                ),
                'hn' => array(
                    'name' => esc_html__('Honduras', 'civi'),
                    'code' => '+504',
                ),
                'hk' => array(
                    'name' => esc_html__('Hong Kong', 'civi'),
                    'code' => '+852',
                ),
                'hu' => array(
                    'name' => esc_html__('Hungary', 'civi'),
                    'code' => '+36',
                ),
                'is' => array(
                    'name' => esc_html__('Iceland', 'civi'),
                    'code' => '+354',
                ),
                'in' => array(
                    'name' => esc_html__('India', 'civi'),
                    'code' => '+91',
                ),
                'id' => array(
                    'name' => esc_html__('Indonesia', 'civi'),
                    'code' => '+62',
                ),
                'ir' => array(
                    'name' => esc_html__('Iran', 'civi'),
                    'code' => '+98',
                ),
                'iq' => array(
                    'name' => esc_html__('Iraq', 'civi'),
                    'code' => '+964',
                ),
                'ie' => array(
                    'name' => esc_html__('Ireland', 'civi'),
                    'code' => '+353',
                ),
                'im' => array(
                    'name' => esc_html__('Isle of Man', 'civi'),
                    'code' => '+44',
                ),
                'il' => array(
                    'name' => esc_html__('Israel', 'civi'),
                    'code' => '+972',
                ),
                'it' => array(
                    'name' => esc_html__('Italy', 'civi'),
                    'code' => '+39',
                ),
                'jm' => array(
                    'name' => esc_html__('Jamaica', 'civi'),
                    'code' => '+1876',
                ),
                'jp' => array(
                    'name' => esc_html__('Japan', 'civi'),
                    'code' => '+81',
                ),
                'je' => array(
                    'name' => esc_html__('Jersey', 'civi'),
                    'code' => '+44',
                ),
                'jo' => array(
                    'name' => esc_html__('Jordan', 'civi'),
                    'code' => '+962',
                ),
                'kz' => array(
                    'name' => esc_html__('Kazakhstan', 'civi'),
                    'code' => '+7',
                ),
                'ke' => array(
                    'name' => esc_html__('Kenya', 'civi'),
                    'code' => '+254',
                ),
                'ki' => array(
                    'name' => esc_html__('Kiribati', 'civi'),
                    'code' => '+686',
                ),
                'xk' => array(
                    'name' => esc_html__('Kosovo', 'civi'),
                    'code' => '+383',
                ),
                'kw' => array(
                    'name' => esc_html__('Kuwait', 'civi'),
                    'code' => '+965',
                ),
                'kg' => array(
                    'name' => esc_html__('Kyrgyzstan', 'civi'),
                    'code' => '+996',
                ),
                'la' => array(
                    'name' => esc_html__('Laos', 'civi'),
                    'code' => '+856',
                ),
                'lv' => array(
                    'name' => esc_html__('Latvia', 'civi'),
                    'code' => '+371',
                ),
                'lb' => array(
                    'name' => esc_html__('Lebanon', 'civi'),
                    'code' => '+961',
                ),
                'ls' => array(
                    'name' => esc_html__('Lesotho', 'civi'),
                    'code' => '+266',
                ),
                'lr' => array(
                    'name' => esc_html__('Liberia', 'civi'),
                    'code' => '+231',
                ),
                'ly' => array(
                    'name' => esc_html__('Libya', 'civi'),
                    'code' => '+218',
                ),
                'li' => array(
                    'name' => esc_html__('Liechtenstein', 'civi'),
                    'code' => '+423',
                ),
                'lt' => array(
                    'name' => esc_html__('Lithuania', 'civi'),
                    'code' => '+370',
                ),
                'lu' => array(
                    'name' => esc_html__('Luxembourg', 'civi'),
                    'code' => '+352',
                ),
                'mo' => array(
                    'name' => esc_html__('Macau', 'civi'),
                    'code' => '+853',
                ),
                'mk' => array(
                    'name' => esc_html__('Macedonia', 'civi'),
                    'code' => '+389',
                ),
                'mg' => array(
                    'name' => esc_html__('Madagascar', 'civi'),
                    'code' => '+261',
                ),
                'mw' => array(
                    'name' => esc_html__('Malawi', 'civi'),
                    'code' => '+265',
                ),
                'my' => array(
                    'name' => esc_html__('Malaysia', 'civi'),
                    'code' => '+60',
                ),
                'mv' => array(
                    'name' => esc_html__('Maldives', 'civi'),
                    'code' => '+960',
                ),
                'ml' => array(
                    'name' => esc_html__('Mali', 'civi'),
                    'code' => '+223',
                ),
                'mt' => array(
                    'name' => esc_html__('Malta', 'civi'),
                    'code' => '+356',
                ),
                'mh' => array(
                    'name' => esc_html__('Marshall Islands', 'civi'),
                    'code' => '+692',
                ),
                'mq' => array(
                    'name' => esc_html__('Martinique', 'civi'),
                    'code' => '+596',
                ),
                'mr' => array(
                    'name' => esc_html__('Mauritania', 'civi'),
                    'code' => '+222',
                ),
                'mu' => array(
                    'name' => esc_html__('Mauritius', 'civi'),
                    'code' => '+230',
                ),
                'yt' => array(
                    'name' => esc_html__('Mayotte', 'civi'),
                    'code' => '+262',
                ),
                'mx' => array(
                    'name' => esc_html__('Mexico', 'civi'),
                    'code' => '+52',
                ),
                'fm' => array(
                    'name' => esc_html__('Micronesia', 'civi'),
                    'code' => '+691',
                ),
                'md' => array(
                    'name' => esc_html__('Moldova', 'civi'),
                    'code' => '+373',
                ),
                'mc' => array(
                    'name' => esc_html__('Monaco', 'civi'),
                    'code' => '+377',
                ),
                'mn' => array(
                    'name' => esc_html__('Mongolia', 'civi'),
                    'code' => '+976',
                ),
                'me' => array(
                    'name' => esc_html__('Montenegro', 'civi'),
                    'code' => '+382',
                ),
                'ms' => array(
                    'name' => esc_html__('Montserrat', 'civi'),
                    'code' => '+1664',
                ),
                'ma' => array(
                    'name' => esc_html__('Morocco', 'civi'),
                    'code' => '+212',
                ),
                'mz' => array(
                    'name' => esc_html__('Mozambique', 'civi'),
                    'code' => '+258',
                ),
                'mm' => array(
                    'name' => esc_html__('Myanmar', 'civi'),
                    'code' => '+95',
                ),
                'na' => array(
                    'name' => esc_html__('Namibia', 'civi'),
                    'code' => '+264',
                ),
                'nr' => array(
                    'name' => esc_html__('Nauru', 'civi'),
                    'code' => '+674',
                ),
                'np' => array(
                    'name' => esc_html__('Nepal', 'civi'),
                    'code' => '+977',
                ),
                'nl' => array(
                    'name' => esc_html__('Netherlands', 'civi'),
                    'code' => '+31',
                ),
                'nc' => array(
                    'name' => esc_html__('New Caledonia', 'civi'),
                    'code' => '+687',
                ),
                'nz' => array(
                    'name' => esc_html__('New Zealand', 'civi'),
                    'code' => '+64',
                ),
                'ni' => array(
                    'name' => esc_html__('Nicaragua', 'civi'),
                    'code' => '+505',
                ),
                'ne' => array(
                    'name' => esc_html__('Niger', 'civi'),
                    'code' => '+227',
                ),
                'ng' => array(
                    'name' => esc_html__('Nigeria', 'civi'),
                    'code' => '+234',
                ),
                'nu' => array(
                    'name' => esc_html__('Niue', 'civi'),
                    'code' => '+683',
                ),
                'nf' => array(
                    'name' => esc_html__('Norfolk Island', 'civi'),
                    'code' => '+672',
                ),
                'kp' => array(
                    'name' => esc_html__('North Korea', 'civi'),
                    'code' => '+850',
                ),
                'mp' => array(
                    'name' => esc_html__('Northern Mariana Islands', 'civi'),
                    'code' => '+1670',
                ),
                'no' => array(
                    'name' => esc_html__('Norway', 'civi'),
                    'code' => '+47',
                ),
                'om' => array(
                    'name' => esc_html__('Oman', 'civi'),
                    'code' => '+968',
                ),
                'pk' => array(
                    'name' => esc_html__('Pakistan', 'civi'),
                    'code' => '+92',
                ),
                'pw' => array(
                    'name' => esc_html__('Palau', 'civi'),
                    'code' => '+680',
                ),
                'ps' => array(
                    'name' => esc_html__('Palestine', 'civi'),
                    'code' => '+970',
                ),
                'pa' => array(
                    'name' => esc_html__('Panama', 'civi'),
                    'code' => '+507',
                ),
                'pg' => array(
                    'name' => esc_html__('Papua New Guinea', 'civi'),
                    'code' => '+675',
                ),
                'py' => array(
                    'name' => esc_html__('Paraguay', 'civi'),
                    'code' => '+595',
                ),
                'pe' => array(
                    'name' => esc_html__('Peru', 'civi'),
                    'code' => '+51',
                ),
                'ph' => array(
                    'name' => esc_html__('Philippines', 'civi'),
                    'code' => '+63',
                ),
                'pl' => array(
                    'name' => esc_html__('Poland', 'civi'),
                    'code' => '+48',
                ),
                'pt' => array(
                    'name' => esc_html__('Portugal', 'civi'),
                    'code' => '+351',
                ),
                'qa' => array(
                    'name' => esc_html__('Qatar', 'civi'),
                    'code' => '+974',
                ),
                're' => array(
                    'name' => esc_html__('Réunion', 'civi'),
                    'code' => '+262',
                ),
                'ro' => array(
                    'name' => esc_html__('Romania', 'civi'),
                    'code' => '+40',
                ),
                'ru' => array(
                    'name' => esc_html__('Russia', 'civi'),
                    'code' => '+7',
                ),
                'rw' => array(
                    'name' => esc_html__('Rwanda', 'civi'),
                    'code' => '+250',
                ),
                'bl' => array(
                    'name' => esc_html__('Saint Barthélemy', 'civi'),
                    'code' => '+590',
                ),
                'sh' => array(
                    'name' => esc_html__('Saint Helena', 'civi'),
                    'code' => '+290',
                ),
                'kn' => array(
                    'name' => esc_html__('Saint Kitts and Nevis', 'civi'),
                    'code' => '+1869',
                ),
                'lc' => array(
                    'name' => esc_html__('Saint Lucia', 'civi'),
                    'code' => '+1758',
                ),
                'mf' => array(
                    'name' => esc_html__('Saint Martin', 'civi'),
                    'code' => '+590',
                ),
                'pm' => array(
                    'name' => esc_html__('Saint Pierre and Miquelon', 'civi'),
                    'code' => '+508',
                ),
                'vc' => array(
                    'name' => esc_html__('Saint Vincent and the Grenadines', 'civi'),
                    'code' => '+1784',
                ),
                'ws' => array(
                    'name' => esc_html__('Samoa', 'civi'),
                    'code' => '+685',
                ),
                'sm' => array(
                    'name' => esc_html__('San Marino', 'civi'),
                    'code' => '+378',
                ),
                'st' => array(
                    'name' => esc_html__('São Tomé and Príncipe', 'civi'),
                    'code' => '+239',
                ),
                'sa' => array(
                    'name' => esc_html__('Saudi Arabia', 'civi'),
                    'code' => '+966',
                ),
                'sn' => array(
                    'name' => esc_html__('Senegal', 'civi'),
                    'code' => '+221',
                ),
                'rs' => array(
                    'name' => esc_html__('Serbia', 'civi'),
                    'code' => '+381',
                ),
                'sc' => array(
                    'name' => esc_html__('Seychelles', 'civi'),
                    'code' => '+248',
                ),
                'sl' => array(
                    'name' => esc_html__('Sierra Leone', 'civi'),
                    'code' => '+232',
                ),
                'sg' => array(
                    'name' => esc_html__('Singapore', 'civi'),
                    'code' => '+65',
                ),
                'sx' => array(
                    'name' => esc_html__('Sint Maarten', 'civi'),
                    'code' => '+1721',
                ),
                'sk' => array(
                    'name' => esc_html__('Slovakia', 'civi'),
                    'code' => '+421',
                ),
                'si' => array(
                    'name' => esc_html__('Slovenia', 'civi'),
                    'code' => '+386',
                ),
                'sb' => array(
                    'name' => esc_html__('Solomon Islands', 'civi'),
                    'code' => '+677',
                ),
                'so' => array(
                    'name' => esc_html__('Somalia', 'civi'),
                    'code' => '+252',
                ),
                'za' => array(
                    'name' => esc_html__('South Africa', 'civi'),
                    'code' => '+27',
                ),
                'kr' => array(
                    'name' => esc_html__('South Korea', 'civi'),
                    'code' => '+82',
                ),
                'ss' => array(
                    'name' => esc_html__('South Sudan', 'civi'),
                    'code' => '+211',
                ),
                'es' => array(
                    'name' => esc_html__('Spain', 'civi'),
                    'code' => '+34',
                ),
                'lk' => array(
                    'name' => esc_html__('Sri Lanka', 'civi'),
                    'code' => '+94',
                ),
                'sd' => array(
                    'name' => esc_html__('Sudan', 'civi'),
                    'code' => '+249',
                ),
                'sr' => array(
                    'name' => esc_html__('Suriname', 'civi'),
                    'code' => '+597',
                ),
                'sj' => array(
                    'name' => esc_html__('Svalbard and Jan Mayen', 'civi'),
                    'code' => '+47',
                ),
                'sz' => array(
                    'name' => esc_html__('Swaziland', 'civi'),
                    'code' => '+268',
                ),
                'se' => array(
                    'name' => esc_html__('Sweden', 'civi'),
                    'code' => '+46',
                ),
                'ch' => array(
                    'name' => esc_html__('Switzerland', 'civi'),
                    'code' => '+41',
                ),
                'sy' => array(
                    'name' => esc_html__('Syria', 'civi'),
                    'code' => '+963',
                ),
                'tw' => array(
                    'name' => esc_html__('Taiwan', 'civi'),
                    'code' => '+886',
                ),
                'tj' => array(
                    'name' => esc_html__('Tajikistan', 'civi'),
                    'code' => '+992',
                ),
                'tz' => array(
                    'name' => esc_html__('Tanzania', 'civi'),
                    'code' => '+255',
                ),
                'th' => array(
                    'name' => esc_html__('Thailand', 'civi'),
                    'code' => '+66',
                ),
                'tl' => array(
                    'name' => esc_html__('Timor-Leste', 'civi'),
                    'code' => '+670',
                ),
                'tg' => array(
                    'name' => esc_html__('Togo', 'civi'),
                    'code' => '+228',
                ),
                'tk' => array(
                    'name' => esc_html__('Tokelau', 'civi'),
                    'code' => '+690',
                ),
                'tk' => array(
                    'name' => esc_html__('Tokelau', 'civi'),
                    'code' => '+690',
                ),
                'to' => array(
                    'name' => esc_html__('Tonga', 'civi'),
                    'code' => '+676',
                ),
                'tt' => array(
                    'name' => esc_html__('Trinidad and Tobago', 'civi'),
                    'code' => '+1868',
                ),
                'tn' => array(
                    'name' => esc_html__('Tunisia', 'civi'),
                    'code' => '+216',
                ),
                'tr' => array(
                    'name' => esc_html__('Turkey', 'civi'),
                    'code' => '+90',
                ),
                'tm' => array(
                    'name' => esc_html__('Turkmenistan', 'civi'),
                    'code' => '+993',
                ),
                'tc' => array(
                    'name' => esc_html__('Turks and Caicos Islands', 'civi'),
                    'code' => '+1649',
                ),
                'tv' => array(
                    'name' => esc_html__('Tuvalu', 'civi'),
                    'code' => '+688',
                ),
                'ug' => array(
                    'name' => esc_html__('Uganda', 'civi'),
                    'code' => '+256',
                ),
                'ua' => array(
                    'name' => esc_html__('Ukraine', 'civi'),
                    'code' => '+380',
                ),
                'ae' => array(
                    'name' => esc_html__('United Arab Emirates', 'civi'),
                    'code' => '+971',
                ),
                'gb' => array(
                    'name' => esc_html__('United Kingdom', 'civi'),
                    'code' => '+44',
                ),
                'us' => array(
                    'name' => esc_html__('United States', 'civi'),
                    'code' => '+1',
                ),
                'uy' => array(
                    'name' => esc_html__('Uruguay', 'civi'),
                    'code' => '+598',
                ),
                'uz' => array(
                    'name' => esc_html__('Uzbekistan', 'civi'),
                    'code' => '+998',
                ),
                'vu' => array(
                    'name' => esc_html__('Vanuatu', 'civi'),
                    'code' => '+678',
                ),
                'va' => array(
                    'name' => esc_html__('Vatican City', 'civi'),
                    'code' => '+39',
                ),
                've' => array(
                    'name' => esc_html__('Venezuela', 'civi'),
                    'code' => '+58',
                ),
                'vn' => array(
                    'name' => esc_html__('Vietnam', 'civi'),
                    'code' => '+84',
                ),
                'wf' => array(
                    'name' => esc_html__('Wallis and Futuna', 'civi'),
                    'code' => '+681',
                ),
                'eh' => array(
                    'name' => esc_html__('Western Sahara', 'civi'),
                    'code' => '+212',
                ),
                'ye' => array(
                    'name' => esc_html__('Yemen', 'civi'),
                    'code' => '+967',
                ),
                'zm' => array(
                    'name' => esc_html__('Zambia', 'civi'),
                    'code' => '+260',
                ),
                'zw' => array(
                    'name' => esc_html__('Zimbabwe', 'civi'),
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
