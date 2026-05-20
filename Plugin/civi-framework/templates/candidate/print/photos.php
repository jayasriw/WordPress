<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$candidate_galleries     = get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_galleries', true);
$attach_id         = get_post_thumbnail_id($candidate_id);
?>
<?php if (!empty($candidate_galleries)) : ?>
    <div class="block-archive-inner candidate-gallery-details">
        <h4 class="title-candidate"><?php esc_html_e('Photos', 'civi-framework') ?></h4>
        <div class="entry-candidate-element">
            <div class="row">
            <?php
            $civi_candidate_galleries = explode('|', $candidate_galleries);
            $count = count($civi_candidate_galleries);
            foreach ($civi_candidate_galleries as $key => $image) :
                if ($image) {
                    $image_full_src = wp_get_attachment_image_src($image, 'full');
                    if (isset($image_full_src[0])) {
                        $thumb_src      = $image_full_src[0];
                    }
                }
                if (!empty($thumb_src)) {
                    ?>
                    <div class="col-4">
                        <figure>
                            <a href="<?php echo esc_url($thumb_src); ?>" class="lgbox">
                                <img src="<?php echo esc_url($thumb_src); ?>" alt="<?php the_title_attribute(); ?>" title="<?php the_title_attribute(); ?>">
                            </a>
                        </figure>
                    </div>
                <?php } ?>
            <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>