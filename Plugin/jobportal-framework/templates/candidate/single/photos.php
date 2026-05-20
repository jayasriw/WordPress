<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
wp_enqueue_style('lightgallery');
wp_enqueue_script('lightgallery');
wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'light-gallery');
wp_enqueue_script('slick');

$id = get_the_ID();
$candidate_galleries     = get_post_meta(get_the_ID(), JOBPORTAL_METABOX_PREFIX . 'candidate_galleries', true);
$attach_id         = get_post_thumbnail_id();
$show = 3;
?>
<?php if (!empty($candidate_galleries)) : ?>
    <div class="block-archive-inner candidate-gallery-details">
        <h4 class="title-candidate"><?php esc_html_e('Photos', 'jobportal-framework') ?></h4>
        <div class="entry-candidate-element">
            <div class="single-candidate-thumbs enable jobportal-light-gallery">
                <?php
                $slick_attributes = array(
                    '"slidesToShow": ' . $show,
                    '"slidesToScroll": 1',
                    '"dots": true',
                    '"autoplay": false',
                    '"autoplaySpeed": 5000',
                    '"responsive": [{ "breakpoint": 479, "settings": {"slidesToShow": 1} },{ "breakpoint": 768, "settings": {"slidesToShow": 2}} ]'
                );
                $wrapper_attributes[] = "data-slick='{" . implode(', ', $slick_attributes) . "}'";
                ?>
                <div class="jobportal-slick-carousel slick-nav" <?php echo implode(' ', $wrapper_attributes); ?>>
                    <?php
                    $jobportal_candidate_galleries = explode('|', $candidate_galleries);
                    $count = count($jobportal_candidate_galleries);
                    foreach ($jobportal_candidate_galleries as $key => $image) :
                        if ($image) {
                            $image_full_src = wp_get_attachment_image_src($image, 'full');
                            if (isset($image_full_src[0])) {
                                $thumb_src      = $image_full_src[0];
                            }
                        }
                        if (!empty($thumb_src)) {
                            ?>
                            <figure>
                                <a href="<?php echo esc_url($thumb_src); ?>" class="lgbox">
                                    <img src="<?php echo esc_url($thumb_src); ?>" alt="<?php the_title_attribute(); ?>" title="<?php the_title_attribute(); ?>">
                                </a>
                            </figure>
                        <?php } ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>