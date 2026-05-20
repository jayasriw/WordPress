<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$social_sharing = jobportal_get_option('social_sharing');
if (empty($social_sharing)) {
    return;
}
$sharing_facebook = $sharing_twitter = $sharing_linkedin = $sharing_tumblr = $sharing_pinterest = $sharing_whatapp = '1';
if (is_array($social_sharing) && count($social_sharing) > 0) {
    $sharing_facebook = in_array('facebook', $social_sharing);
    $sharing_twitter = in_array('twitter', $social_sharing);
    $sharing_linkedin = in_array('linkedin', $social_sharing);
    $sharing_tumblr = in_array('tumblr', $social_sharing);
    $sharing_pinterest = in_array('pinterest', $social_sharing);
    $sharing_whatapp = in_array('whatapp', $social_sharing);
}

?>
<div class="social-share">
    <div class="list-social-icon">
        <?php if ($sharing_facebook == 1) : ?>
            <a class="facebook" rel="noopener noreferrer" data-url="<?php echo esc_attr(get_the_permalink($post_id)); ?>" href="javascript:void(0)">
                <i class="fab fa-facebook-f"></i>
            </a>
        <?php endif; ?>
        <?php if ($sharing_twitter == 1) : ?>
            <a class="twitter" rel="noopener noreferrer" data-url="<?php echo esc_attr(get_the_permalink($post_id)); ?>" href="javascript:void(0)">
                <!-- fab fa-twitter -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currrentColor">
                    <path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z" />
                </svg>
            </a>
        <?php endif; ?>

        <?php if ($sharing_linkedin == 1) : ?>
            <a class="linkedin" rel="noopener noreferrer" data-url="<?php echo esc_attr(get_the_permalink($post_id)); ?>" data-title="<?php echo esc_attr(get_the_title($post_id)); ?>" href="javascript:void(0)">
                <i class="fab fa-linkedin-in"></i>
            </a>
        <?php endif; ?>

        <?php if ($sharing_tumblr == 1) : ?>
            <a class="tumblr" rel="noopener noreferrer" data-url="<?php echo esc_attr(get_the_permalink($post_id)); ?>" data-name="<?php echo esc_attr(get_the_title($post_id)); ?>" data-description="<?php echo esc_attr(get_the_excerpt()); ?>" href="javascript:void(0)">
                <i class="fab fa-tumblr"></i>
            </a>
        <?php endif; ?>

        <?php if ($sharing_pinterest == 1) : ?>
            <a class="pinterest" rel="noopener noreferrer" data-url="<?php echo esc_attr(get_the_permalink($post_id)); ?>" data-description="<?php echo esc_attr(get_the_title($post_id)); ?>" data-media="<?php $arrImages = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
                                                                                                                                                                                                            echo has_post_thumbnail() ? esc_attr($arrImages[0]) : ""; ?>" href="javascript:void(0)">
                <i class="fab fa-pinterest-p"></i>
            </a>
        <?php endif; ?>

        <?php if ($sharing_whatapp == 1) : ?>
            <a class="whatapp" rel="noopener noreferrer" data-url="<?php echo esc_attr(get_the_permalink($post_id)); ?>" data-description="<?php echo esc_attr(get_the_title($post_id)); ?>" data-media="<?php $arrImages = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
                                                                                                                                                                                                            echo has_post_thumbnail() ? esc_attr($arrImages[0]) : ""; ?>" href="javascript:void(0)">
                <i class="fab fa-whatsapp"></i>
            </a>
        <?php endif; ?>
    </div>
</div>
