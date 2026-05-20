<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$service_id = get_the_ID();
$author_id = get_post_field('post_author', $service_id);
$args_candidate = array(
    'post_type' => 'candidate',
    'posts_per_page' => 1,
    'author' => $author_id,
);
$current_user_posts = get_posts($args_candidate);
$candidate_id = !empty($current_user_posts) ? $current_user_posts[0]->ID : '';
$candidate_avatar = get_the_author_meta('author_avatar_image_url', $author_id);
$candidate_featured = get_post_meta($candidate_id, JOBPORTAL_METABOX_PREFIX . 'candidate_featured', true);
$candidate_yoe             = get_the_terms($candidate_id, 'candidate_yoe');
$candidate_qualification   = get_the_terms($candidate_id, 'candidate_qualification');
$enable_candidate_review = jobportal_get_option('enable_single_candidate_review', '1');

$classes = array();
$enable_sticky_sidebar_type = jobportal_get_option('enable_sticky_service_sidebar_type');
$currency_sign_default = jobportal_get_option('currency_sign_default');
if ($enable_sticky_sidebar_type) {
    $classes[] = 'has-sticky';
}
?>
<div class="service-info-sidebar block-archive-sidebar <?php echo implode(" ", $classes); ?>">
    <h4><?php esc_html_e('Seller information', 'jobportal-framework') ?></h4>
    <div class="info-inner">
        <?php if (!empty($candidate_avatar)) : ?>
            <img class="image-candidates" src="<?php echo esc_attr($candidate_avatar) ?>" alt=""/>
        <?php else : ?>
            <div class="image-candidates"><i class="far fa-camera"></i></div>
        <?php endif; ?>
        <div class="inner-right">
            <?php if (!empty(get_the_title($candidate_id))) : ?>
                <h4><?php echo get_the_title($candidate_id); ?></h4>
                <?php if ($candidate_featured == 1) : ?>
                    <span class="tooltip" data-title="<?php echo esc_attr__('Featured', 'jobportal-framework') ?>"><i
                                class="fas fa-check"></i></span>
                <?php endif; ?>
            <?php endif; ?>
            <?php if($enable_candidate_review) : ?>
                <?php echo jobportal_get_total_rating('candidate', $candidate_id); ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="info-content candidate-sidebar">
        <?php if (is_array($candidate_yoe)) : ?>
            <div class="info">
                <p class="title-info"><?php esc_html_e('Experience time', 'jobportal-framework'); ?></p>
                <div class="list-cate">
                    <?php foreach ($candidate_yoe as $yoe) {
                        $yoe_link = get_term_link($yoe, 'candidate_yoe'); ?>
                        <a href="<?php echo esc_url($yoe_link); ?>">
                            <?php esc_attr_e($yoe->name); ?>
                        </a>
                    <?php } ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if (is_array($candidate_qualification)) : ?>
            <div class="info">
                <p class="title-info"><?php esc_html_e('Qualification', 'jobportal-framework'); ?></p>
                <div class="list-cate">
                    <?php foreach ($candidate_qualification as $qualification) {
                        echo esc_attr_e($qualification->name);
                    } ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="button-wrapper">
            <a href="<?php echo get_the_permalink($candidate_id); ?>" class="jobportal-button button-block button-outline">
                <?php esc_html_e('View profile', 'jobportal-framework'); ?>
            </a>
            <?php jobportal_get_template('candidate/messages.php', array(
                'candidate_id' => $candidate_id,
            )); ?>
        </div>
    </div>
</div>
