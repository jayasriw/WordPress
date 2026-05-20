<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
wp_enqueue_script('razorpay_checkout', 'https://checkout.razorpay.com/v1/checkout.js', null, null);
if (!is_user_logged_in()) {
    echo jobportal_get_template_html('global/access-denied.php', array('type' => 'not_login'));
    return;
}
$allow_submit = jobportal_allow_submit();
if (!$allow_submit) {
    echo jobportal_get_template_html('global/access-denied.php', array('type' => 'not_permission'));
    return;
}
$candidate_package_id = isset($_GET['candidate_package_id']) ? absint(wp_unslash($_GET['candidate_package_id']))  : '';
$candidate_id   = isset($_GET['candidate_id']) ? absint(wp_unslash($_GET['candidate_id']))  : '';
if (empty($candidate_package_id) && empty($candidate_id)) {
    echo ("<script>location.href = '" . home_url() . "'</script>");
}
set_time_limit(700);
$candidate_paid_submission_type = jobportal_get_option('candidate_paid_submission_type');
?>
<div class="payment-wrap">
    <?php
    do_action('jobportal_candidate_payment_before');
    if ($candidate_paid_submission_type == 'candidate_per_package') {
        jobportal_get_template('candidate/payment/per-package.php');
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
    wp_nonce_field('jobportal_candidate_payment_ajax_nonce', 'jobportal_candidate_security_payment');
    do_action('jobportal_candidate_payment_after');
    ?>
</div>
