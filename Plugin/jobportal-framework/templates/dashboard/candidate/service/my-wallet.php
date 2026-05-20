<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'my-wallet');
wp_localize_script(
    JOBPORTAL_PLUGIN_PREFIX . 'my-wallet',
    'jobportal_my_wallet_vars',
    array(
        'ajax_url' => JOBPORTAL_AJAX_URL,
        'not_wallet' => esc_html__('No wallet found', 'jobportal-framework'),
    )
);

global $current_user;
$user_id = $current_user->ID;
$posts_per_page = 10;
$currency_sign_default = jobportal_get_option('currency_sign_default');
$currency_position = jobportal_get_option('currency_position');
$jobportal_candidate_package = new JobPortal_candidate_package();
$check_candidate_package = $jobportal_candidate_package->user_candidate_package_available($user_id);

$total_price = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'service_withdraw_total_price', true);
if(empty($total_price)){
    $total_price = 0;
}
if ($currency_position == 'before') {
    $total_price = $currency_sign_default . $total_price;
} else {
    $total_price = $total_price . $currency_sign_default;
}

$args = array(
    'post_type' => 'service_withdraw',
    'ignore_sticky_posts' => 1,
    'posts_per_page' => $posts_per_page,
    'post_status'  => 'publish',
    'offset' => (max(1, get_query_var('paged')) - 1) * $posts_per_page,
    'meta_query' => array(
        array(
            'key' => JOBPORTAL_METABOX_PREFIX . 'service_withdraw_user_id',
            'value' => $user_id,
            'compare' => '==',
        )
    ),
);

$data = new WP_Query($args);
?>

<div class="jobportal-service-withdraw entry-my-page">
    <div class="jobportal-dashboard">
        <div class="total-action">
            <ul class="action-wrapper row">
                <li class="col-md-4 col-sm-12">
                    <div class="available-balance jobportal-boxdb">
                        <div class="entry-detai ">
                            <h3 class="entry-title"><?php esc_html_e('Available Balance', 'jobportal-framework'); ?></h3>
                            <span class="entry-number"><?php echo $total_price;?></span>
                        </div>
                        <div class="icon-total">
                            <img src="<?php echo esc_attr(JOBPORTAL_PLUGIN_URL . 'assets/images/icon-wallet-01.svg'); ?>" alt="<?php esc_attr_e('jobs', 'jobportal-framework'); ?>">
                        </div>
                    </div>
                </li>
                <li class="col-md-4 col-sm-12">
                    <div class="pending-balance jobportal-boxdb">
                        <div class="entry-detai ">
                            <h3 class="entry-title"><?php esc_html_e('Pending Balance', 'jobportal-framework'); ?></h3>
                            <span class="entry-number"><?php echo jobportal_wallet_total_price('pending'); ?></span>
                        </div>
                        <div class="icon-total">
                            <img src="<?php echo esc_attr(JOBPORTAL_PLUGIN_URL . 'assets/images/icon-wallet-02.svg'); ?>" alt="<?php esc_attr_e('applications', 'jobportal-framework'); ?>">
                        </div>
                    </div>
                </li>
                <li class="col-md-4 col-sm-12">
                    <div class="withdrawn jobportal-boxdb">
                        <div class="entry-detai ">
                            <h3 class="entry-title"><?php esc_html_e('Withdrawn', 'jobportal-framework'); ?></h3>
                            <span class="entry-number"><?php echo jobportal_wallet_total_price('completed'); ?></span>
                        </div>
                        <div class="icon-total">
                            <img src="<?php echo esc_attr(JOBPORTAL_PLUGIN_URL . 'assets/images/icon-wallet-03.svg'); ?>" alt="<?php esc_attr_e('interviews', 'jobportal-framework'); ?>">
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <div class="search-dashboard-wrapper">
        <div class="search-left">
            <div class="select2-field">
                <select class="search-control jobportal-select2" name="wallet_method">
                    <option value=""><?php esc_html_e('Payout Method', 'jobportal-framework') ?></option>
                    <option value="wire_transfer"><?php esc_html_e('Wire Transfer', 'jobportal-framework') ?></option>
                    <option value="stripe"><?php esc_html_e('Pay With Stripe', 'jobportal-framework') ?></option>
                    <option value="paypal"><?php esc_html_e('Pay With Paypal', 'jobportal-framework') ?></option>
                </select>
            </div>
            <div class="select2-field">
                <select class="search-control jobportal-select2" name="wallet_status">
                    <option value=""><?php esc_html_e('All status', 'jobportal-framework') ?></option>
                    <option value="pending"><?php esc_html_e('Pending', 'jobportal-framework') ?></option>
                    <option value="completed"><?php esc_html_e('Completed', 'jobportal-framework') ?></option>
                    <option value="canceled"><?php esc_html_e('Canceled', 'jobportal-framework') ?></option>
                </select>
            </div>
        </div>
        <div class="search-right">
            <label class="text-sorting"><?php esc_html_e('Sort by', 'jobportal-framework') ?></label>
            <div class="select2-field">
                <select class="search-control action-sorting jobportal-select2" name="wallet_sort_by">
                    <option value="newest"><?php esc_html_e('Newest', 'jobportal-framework') ?></option>
                    <option value="oldest"><?php esc_html_e('Oldest', 'jobportal-framework') ?></option>
                </select>
            </div>
        </div>
    </div>
    <?php if ($data->have_posts()) { ?>
        <div class="table-dashboard-wapper">
            <table class="table-dashboard <?php if ($check_candidate_package == -1 || $check_candidate_package == 0) {
                echo 'expired';
            } ?>" id="my-wallet">
                <thead>
                <tr>
                    <th><?php esc_html_e('Payout Method', 'jobportal-framework') ?></th>
                    <th><?php esc_html_e('Status', 'jobportal-framework') ?></th>
                    <th><?php esc_html_e('Price', 'jobportal-framework') ?></th>
                    <th><?php esc_html_e('Request Date', 'jobportal-framework') ?></th>
                    <th><?php esc_html_e('Process Date', 'jobportal-framework') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php while ($data->have_posts()) : $data->the_post(); ?>
                    <?php
                    $withdraw_id = get_the_ID();
                    $payment_method = get_post_meta($withdraw_id, JOBPORTAL_METABOX_PREFIX . 'service_withdraw_payment_method',true);
                    $payment_method = str_replace(['-','_'], ' ', $payment_method);
                    $price = get_post_meta($withdraw_id, JOBPORTAL_METABOX_PREFIX . 'service_withdraw_price',true);
                    $status = get_post_meta($withdraw_id, JOBPORTAL_METABOX_PREFIX . 'service_withdraw_status',true);
                    $request_date =  get_the_date(get_option('date_format'));
                    $process_date = get_post_meta($withdraw_id, JOBPORTAL_METABOX_PREFIX . 'service_withdraw_process_date',true);
                    if(empty($process_date)){
                        $process_date = '...';
                    } else {
                        $process_date = jobportal_convert_date_format($process_date);
                    }
                    $currency_position = jobportal_get_option('currency_position');
                    $currency_sign_default = jobportal_get_option('currency_sign_default');
                    if ($currency_position == 'before') {
                        $price = $currency_sign_default . $price;
                    } else {
                        $price = $price . $currency_sign_default;
                    }
                    ?>
                    <tr>
                        <td>
                            <?php echo $payment_method;?>
                        </td>
                        <td>
                            <?php if ($status == 'pending') : ?>
                                <span class="label label-pending"><?php esc_html_e('Pending', 'jobportal-framework') ?></span>
                            <?php elseif ($status == 'canceled') : ?>
                                <span class="label label-close"><?php esc_html_e('Canceled', 'jobportal-framework') ?></span>
                            <?php elseif ($status == 'completed') : ?>
                                <span class="label label-open"><?php esc_html_e('Completed', 'jobportal-framework') ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="price">
                            <?php echo $price; ?>
                        </td>
                        <td>
                            <?php echo $request_date; ?>
                        </td>
                        <td>
                            <?php echo $process_date; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            <div class="jobportal-loading-effect"><span class="jobportal-dual-ring"></span></div>
        </div>
    <?php } else { ?>
        <div class="item-not-found"><?php esc_html_e('No item found', 'jobportal-framework'); ?></div>
    <?php } ?>
    <?php $total_post = $data->found_posts;
    if ($total_post > $posts_per_page) { ?>
        <div class="pagination-dashboard pagination-wishlist">
            <?php $max_num_pages = $data->max_num_pages;
            jobportal_get_template('global/pagination.php', array('total_post' => $total_post, 'max_num_pages' => $max_num_pages, 'type' => 'dashboard', 'layout' => 'number'));
            wp_reset_postdata(); ?>
        </div>
    <?php } ?>

</div>
