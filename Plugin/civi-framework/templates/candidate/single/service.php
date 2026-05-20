<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$candidate_id     = get_the_ID();
$author_id = get_post_field('post_author', $candidate_id);
$enable_single_candidate_service = civi_get_option('enable_single_candidate_service');
$enable_service = civi_get_option('enable_post_type_service');

$args = array(
    'posts_per_page' => 3,
    'post_type' => 'service',
    'post_status' => 'publish',
    'ignore_sticky_posts' => 1,
    'author' => $author_id,
);
$get_service = get_posts($args);
?>
<?php if ($enable_single_candidate_service === '1' && $enable_service === '1' && !empty($get_service)) : ?>
    <div class="civi-block-inner block-archive-inner candidate-service-details">
        <div class="header-service">
            <h4 class="title-service"><?php esc_html_e('Services', 'civi-framework') ?></h4>
            <a href="<?php echo get_post_type_archive_link('service') ?>"
               class="civi-button button-border-bottom"><?php esc_html_e('View all service', 'civi-framework') ?></a>
        </div>
        <div class="related-inner">
            <?php foreach ($get_service as $service) {?>
                <?php civi_get_template('content-service.php', array(
                    'services_id'  => $service->ID,
                    'service_layout' => 'layout-list',
                )); ?>
            <?php } ?>
        </div>
    </div>
<?php endif; ?>
