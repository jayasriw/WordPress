<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $current_user;
$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$service_id = isset($_GET['service_id']) ? jobportal_clean(wp_unslash($_GET['service_id'])) : '';
$jobportal_candidate_package = new JobPortal_candidate_package();
$check_candidate_package = $jobportal_candidate_package->user_candidate_package_available($user_id);

$args = array(
    'post_type'           => 'service',
    'post_status'  => array('publish','expired'),
    'posts_per_page'      => -1,
    'author'              => $user_id,
);
$data = new WP_Query($args);
$posts = $data->posts;
foreach($posts as $post) {
    $id_ex = $post->ID;
    if($check_candidate_package == -1 || $check_candidate_package == 0){
        update_post_meta($id_ex, JOBPORTAL_METABOX_PREFIX . 'enable_service_package_expires', 1);
    } else {
        update_post_meta($id_ex, JOBPORTAL_METABOX_PREFIX . 'enable_service_package_expires', 0);
    }
}

if (!empty($service_id)) {
    jobportal_get_template('dashboard/candidate/service/my-service.php');
} else { ?>
    <?php if ($check_candidate_package == -1 || $check_candidate_package == 0) : ?>
        <p class="notice"><i class="fal fa-exclamation-circle"></i>
            <?php esc_html_e("Package expired. Please select a new one.", 'jobportal-framework'); ?>
        </p>
    <?php endif; ?>
    <div class="jobportal-service-dashboard entry-my-page">
        <div class="entry-title">
            <h4><?php esc_html_e('My Service', 'jobportal-framework'); ?></h4>
            <div class="button-wrapper">
                <a href="#form-service-withdraw"
                   class="jobportal-button button-outline-accent" id="btn-service-withdraw">
                    <i class="fas fa-arrow-to-bottom"></i><?php esc_html_e('Withdrawals', 'jobportal-framework') ?>
                </a>
                <?php if ($check_candidate_package == -1 || $check_candidate_package == 0) : ?>
                    <a href="<?php echo get_permalink(jobportal_get_option('jobportal_candidate_package_page_id')); ?>"
                       class="jobportal-button">
                        <i class="far fa-plus"></i><?php esc_html_e('Create new service', 'jobportal-framework') ?>
                    </a>
                <?php else : ?>
                    <a href="<?php echo get_permalink(jobportal_get_option('jobportal_submit_service_page_id')); ?>"
                       class="jobportal-button">
                        <i class="far fa-plus"></i><?php esc_html_e('Create new service', 'jobportal-framework') ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="tab-dashboard">
            <ul class="tab-list">
                <li class="tab-item tab-service-item"><a
                            href="#tab-service"><?php esc_html_e('My Service', 'jobportal-framework'); ?></a></li>
                <li class="tab-item tab-orders-item"><a
                            href="#tab-orders"><?php esc_html_e('My Orders', 'jobportal-framework'); ?></a></li>
                <li class="tab-item tab-wallet-item"><a
                            href="#tab-wallet"><?php esc_html_e('My Wallet', 'jobportal-framework'); ?></a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-info" id="tab-service">
                    <?php jobportal_get_template('dashboard/candidate/service/my-service.php'); ?>
                </div>
                <div class="tab-info" id="tab-orders">
                    <?php jobportal_get_template('dashboard/candidate/service/my-orders.php'); ?>
                </div>
                <div class="tab-info" id="tab-wallet">
                    <?php jobportal_get_template('dashboard/candidate/service/my-wallet.php'); ?>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
