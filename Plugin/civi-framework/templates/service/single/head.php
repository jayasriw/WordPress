<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$service_id = get_the_ID();
$author_id = get_post_field('post_author', $service_id);
$candidate_id = civi_id_service_to_candidate($service_id);
$candidate_avatar = get_the_author_meta('author_avatar_image_url', $author_id);
$candidate_current_position = get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_current_position', true);
$candidate_featured = get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_featured', true);
$service_featured  = get_post_meta($service_id, CIVI_METABOX_PREFIX . 'service_featured', true);
$enable_service_review = civi_get_option('enable_single_service_review', '1');
?>
<div class="container">
    <div class="service-head-details">
        <div class="head-left">
            <?php get_template_part('templates/global/breadcrumb'); ?>
            <div class="info">
                <div class="info-left">
                    <h1>
                        <?php echo get_the_title(); ?>
                        <?php if ($service_featured == '1') : ?>
                            <span class="tooltip featured" data-title="<?php esc_attr_e('Featured', 'civi-framework') ?>">
                                    <img src="<?php echo esc_attr(CIVI_PLUGIN_URL . 'assets/images/icon-featured.svg'); ?>" alt="">
                                </span>
                        <?php endif; ?>
                    </h1>
                    <div class="info-inner">
                        <?php if (!empty($candidate_avatar)) : ?>
                            <img class="image-candidates" src="<?php echo esc_attr($candidate_avatar) ?>" alt=""/>
                        <?php else : ?>
                            <div class="image-candidates"><i class="far fa-camera"></i></div>
                        <?php endif; ?>
                        <?php if (!empty(get_the_title($candidate_id))) : ?>
                            <h4><?php echo get_the_title($candidate_id); ?></h4>
                            <?php if ($candidate_featured == 1) : ?>
                                <span class="tooltip" data-title="<?php echo esc_attr__('Featured', 'civi-framework') ?>"><i
                                            class="fas fa-check"></i></span>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if (!empty($candidate_current_position)) { ?>
                            <div class="candidate-current-position candidate-wrapper">
                                <?php esc_html_e($candidate_current_position); ?>
                            </div>
                        <?php } ?>
                        <?php if ($enable_service_review) : ?>
                            <?php echo civi_get_total_rating('service', $service_id); ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="info-right">
                    <div class="toggle-social">
                        <a href="#" class="jobs-share btn-share tooltip" data-title="<?php esc_attr_e('Share', 'civi-framework') ?>">
                            <i class="fas fa-share-alt"></i>
                        </a>
                        <?php civi_get_template('global/social-share.php', array(
                            'post_id' => $service_id,
                        )); ?>
                    </div>
                    <?php civi_get_template('service/wishlist.php', array(
                        'service_id' => $service_id,
                    )); ?>
                </div>
            </div>
        </div>
    </div>
</div>
