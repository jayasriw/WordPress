<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $current_user;
wp_get_current_user();
wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'wishlist');

$key = false;
$user_id = $current_user->ID;
$my_wishlist = get_user_meta($user_id, CIVI_METABOX_PREFIX . 'my_wishlist', true);
$id = get_the_ID();
if (!empty($jobs_id)) {
    $id = $jobs_id;
}
if (!empty($my_wishlist)) {
    $key = array_search($id, $my_wishlist);
}
$css_class = '';
if ($key !== false) {
    $css_class = 'added';
}
$candidate_paid_submission_type = civi_get_option( 'candidate_paid_submission_type' );
$candidate_package_id = get_the_author_meta(CIVI_METABOX_PREFIX . 'candidate_package_id', $user_id);
$check_package = civi_get_field_check_candidate_package('jobs_wishlist');
$candidate_package_number_jobs_wishlist = get_user_meta($user_id, CIVI_METABOX_PREFIX . 'candidate_package_number_jobs_wishlist', true);
?>
<?php if (is_user_logged_in() && in_array('civi_user_candidate', (array)$current_user->roles)) { ?>
    <?php if ($check_package == -1 || $check_package == 0 || ($candidate_paid_submission_type == 'candidate_per_package' && $candidate_package_number_jobs_wishlist < 1)) { ?>
        <a href="#" class="btn-add-to-message btn-add-to-wishlist tooltip <?php echo esc_attr($css_class); ?>"
            data-title="<?php esc_attr_e('Wishlist', 'civi-framework') ?>"
           data-text="<?php echo esc_attr__('The quantity in your package has reached its limit or your package has expired', 'civi-framework'); ?>">
            <span class="icon-heart">
                <i class="fas fa-heart"></i>
            </span>
        </a>
    <?php } else { ?>
        <a href="#" class="civi-add-to-wishlist btn-add-to-wishlist tooltip <?php echo esc_attr($css_class); ?>"
           data-jobs-id="<?php echo intval($id) ?>" data-title="<?php esc_attr_e('Wishlist', 'civi-framework') ?>">
            <span class="icon-heart">
                <i class="fas fa-heart"></i>
            </span>
        </a>
    <?php } ?>
<?php } else { ?>
    <div class="logged-out">
        <a href="#popup-form" class="btn-login btn-add-to-wishlist tooltip <?php echo esc_attr($css_class); ?>"
           data-jobs-id="<?php echo intval($id) ?>" data-title="<?php esc_attr_e('Wishlist', 'civi-framework') ?>">
                <span class="icon-heart">
                    <i class="fas fa-heart"></i>
                </span>
        </a>
    </div>
<?php } ?>

