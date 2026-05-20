<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$candidate_id = get_the_ID();
$classes = array();
$custom_candidate_image_size = jobportal_get_option('single_candidate_image_size');
$width = $height = '';
if (preg_match('/\d+x\d+/', $custom_candidate_image_size)) {
    $attach_id = get_post_thumbnail_id($candidate_id);
    $image_sizes = explode('x', $custom_candidate_image_size);
    $width         = $image_sizes[0];
    $height         = $image_sizes[1];
    $image_src      = jobportal_image_resize_id($attach_id, $width, $height, true);
}

$single_candidate_style = jobportal_get_option('single_candidate_style');
// Support ?style=large-cover-img or ?layout=large-cover-img (or cover-img, no-image) for style
if (!empty($_GET['style'])) {
    $single_candidate_style = jobportal_clean(wp_unslash($_GET['style']));
} elseif (!empty($_GET['layout']) && in_array($_GET['layout'], array('cover-img', 'no-image', 'large-cover-img'))) {
    $single_candidate_style = jobportal_clean(wp_unslash($_GET['layout']));
}
if ($single_candidate_style == 'large-cover-img') {
    $classes[] = 'has-large-thumbnail';
}

// Type 2 - No Image: Don't display thumbnail
if ($single_candidate_style == 'no-image') {
    return;
}

if (has_post_thumbnail()) : ?>
    <div class="candidate-thumbnail-details <?php echo implode(" ", $classes); ?>">
        <div class="container">
            <?php if ($width !== '' & $height !== '') { ?>
                <img width="<?php echo esc_attr($width) ?>" height="<?php echo esc_attr($height) ?>" src="<?php echo esc_url($image_src) ?>" alt="<?php echo get_the_title($candidate_id); ?>" />
            <?php } else { ?>
                <?php echo the_post_thumbnail(); ?>
            <?php } ?>
        </div>
    </div>
<?php endif; ?>
