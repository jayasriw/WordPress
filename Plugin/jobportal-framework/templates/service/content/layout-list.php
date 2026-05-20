<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
$service_id = get_the_ID();
if (!empty($services_id)) {
    $service_id = $services_id;
}
$candidate_id = jobportal_id_service_to_candidate($service_id);
$enable_service_des = jobportal_get_option('enable_service_show_des');
$currency_sign_default = jobportal_get_option('currency_sign_default');
$currency_position = jobportal_get_option('currency_position');
$enable_service_review = jobportal_get_option('enable_single_service_review', '1');

$author_id = get_post_field('post_author', $service_id);
$candidate_avatar = get_the_author_meta('author_avatar_image_url', $author_id);
$service_featured  = get_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_featured', true);
$service_item_class[] = 'jobportal-service-item';
if (!empty($layout)) {
	$service_item_class[] = $layout;
}
if ($service_featured == 1) {
    $service_item_class[] = 'jobportal-service-featured';
}
$service_item_class[] = 'service-' . $service_id;

$number_start_price = get_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_price', true);
$candidate_current_position = get_post_meta($candidate_id, JOBPORTAL_METABOX_PREFIX . 'candidate_current_position', true);
if ($currency_position == 'before') {
    $price = $currency_sign_default . $number_start_price;
} else {
    $price = $number_start_price . $currency_sign_default;
}
?>
<div class="<?php echo join(' ', $service_item_class); ?>">
    <?php if (has_post_thumbnail()) : ?>
        <div class="service-thumbnail">
            <?php echo the_post_thumbnail(); ?>
        </div>
    <?php endif; ?>
    <div class="service-content">
        <div class="service-header">
            <div class="service-header-left">
                <div class="service-info">
                    <?php if (!empty($candidate_current_position)) { ?>
                        <div class="candidate-current-position candidate-wrapper">
                            <?php esc_html_e($candidate_current_position); ?>
                        </div>
                    <?php } ?>
                    <?php if (!empty(get_the_title($service_id))) : ?>
                        <h2 class="service-title">
                            <a href="<?php echo get_the_permalink($service_id); ?>"><?php echo get_the_title($service_id); ?></a>
                        </h2>
                    <?php endif; ?>
                </div>
            </div>
            <div class="service-header-right">
                <div class="service-status-inner">
                    <?php if($service_featured == 1) : ?>
                        <div class="service-status">
                        <span class="tooltip featured" data-title="<?php esc_attr_e('Featured', 'jobportal-framework') ?>">
                        <img src="<?php echo esc_attr(JOBPORTAL_PLUGIN_URL . 'assets/images/icon-featured.svg'); ?>"
                             alt="<?php echo esc_attr__('featured', 'jobportal-framework') ?>">
                        </span>
                        </div>
                    <?php endif; ?>
                    <?php jobportal_get_template('service/wishlist.php', array(
                        'service_id' => $service_id,
                    )); ?>
                </div>
            </div>
        </div>
        <?php if (!empty(get_the_content($service_id)) && $enable_service_des) : ?>
            <div class="des-service">
                <?php echo wp_trim_words(get_the_content($service_id), 25); ?>
            </div>
        <?php endif; ?>
        <div class="service-bottom">
            <?php if (!empty($price)) : ?>
                <div class="price-inner">
                    <span><?php esc_html_e('From', 'jobportal-framework') ?></span>
                    <span class="price"><?php echo $price; ?></span>
                </div>
            <?php endif; ?>
            <div class="info-inner">
                <?php if (!empty($candidate_avatar)) : ?>
                    <img class="image-candidates" src="<?php echo esc_attr($candidate_avatar) ?>" alt=""/>
                <?php else : ?>
                    <div class="image-candidates"><i class="far fa-camera"></i></div>
                <?php endif; ?>
                <div class="info">
                    <?php if (!empty(get_the_title($candidate_id))) : ?>
                        <h4><?php echo get_the_title($candidate_id); ?></h4>
                    <?php endif; ?>
                    <?php if ($enable_service_review) : ?>
                        <?php echo jobportal_get_total_rating('service', $service_id); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
	<a class="jobportal-link-item" href="<?php echo get_post_permalink($service_id) ?>"></a>
</div>
