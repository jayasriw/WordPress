<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $current_user;
wp_get_current_user();
wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'service-wishlist');

$key = false;
$user_id = $current_user->ID;
$service_wishlist = get_user_meta($user_id, CIVI_METABOX_PREFIX . 'service_wishlist', true);
$id = get_the_ID();

if (!empty($service_id)) {
    $id = $service_id;
}

if (!empty($service_wishlist)) {
    $key = array_search($id, $service_wishlist);
}

$css_class = '';
if ($key !== false) {
    $css_class = 'added';
}
?>
<?php if (is_user_logged_in() && in_array('civi_user_employer', (array)$current_user->roles)) { ?>
    <a href="#" class="civi-service-wishlist btn-add-to-wishlist tooltip <?php echo esc_attr($css_class); ?>"
       data-service-id="<?php echo intval($id) ?>" data-title="<?php esc_attr_e('Wishlist', 'civi-framework') ?>">
        <span class="icon-heart">
            <i class="fas fa-heart"></i>
        </span>
    </a>
<?php } else { ?>
    <div class="logged-out">
        <a href="#popup-form" class="btn-login btn-add-to-wishlist tooltip notice-employer <?php echo esc_attr($css_class); ?>"
           data-service-id="<?php echo intval($id) ?>" data-title="<?php esc_attr_e('Wishlist', 'civi-framework') ?>" data-notice="<?php esc_attr_e('Please login Employer', 'civi-framework') ?>">
            <span class="icon-heart">
                <i class="fas fa-heart"></i>
            </span>
        </a>
    </div>
<?php } ?>