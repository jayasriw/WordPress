<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
wp_enqueue_script('razorpay_checkout', 'https://checkout.razorpay.com/v1/checkout.js', null, null);
if (!is_user_logged_in()) {
    echo jobportal_get_template_html('global/access-denied.php', array('type' => 'not_login'));
    return;
}
wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'payment');
$allow_submit = jobportal_allow_submit();
if (!$allow_submit) {
    echo jobportal_get_template_html('global/access-denied.php', array('type' => 'not_permission'));
    return;
}
$package_id = isset($_GET['package_id']) ? absint(wp_unslash($_GET['package_id']))  : '';
$jobs_id   = isset($_GET['jobs_id']) ? absint(wp_unslash($_GET['jobs_id']))  : '';
$is_upgrade = isset($_GET['is_upgrade']) ? absint(wp_unslash($_GET['is_upgrade']))  : '';
if ($is_upgrade == 1) {
    $prop_featured = get_post_meta($jobs_id, JOBPORTAL_METABOX_PREFIX . 'place_featured', true);
    if ($prop_featured == 1) {
        echo ("<script>location.href = '" . home_url() . "'</script>");
    }
}
if (empty($package_id) && empty($jobs_id)) {
    echo ("<script>location.href = '" . home_url() . "'</script>");
}
$JobPortal_jobs = new JobPortal_Jobs();

if (!empty($jobs_id) && !$JobPortal_jobs->user_can_edit_place($jobs_id)) {
    echo ("<script>location.href = '" . home_url() . "'</script>");
}
$paid_submission_type = jobportal_get_option('paid_submission_type', 'no');
?>
<div class="payment-wrap">
    <?php
    do_action('jobportal_payment_before');
    if ($paid_submission_type == 'per_package') {
        jobportal_get_template('jobs/payment/per-package.php');
    } else { ?>
        <p class="notice"><i class="fal fa-exclamation-circle"></i>
            <?php esc_html_e("You are on free submit active", 'jobportal-framework'); ?>
            <?php if (jobportal_get_option('enable_post_type_service') === '1') { ?>
                <a href="<?php echo jobportal_get_permalink('submit_service'); ?>">
                    <?php esc_html_e('Add Service', 'jobportal-framework'); ?>
                </a>
            <?php } ?>
        </p>
    <?php }
    wp_nonce_field('jobportal_payment_ajax_nonce', 'jobportal_security_payment');
    do_action('jobportal_payment_after');
    ?>
</div>
