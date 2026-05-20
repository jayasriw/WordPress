<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'candidate-service-order');
wp_localize_script(
    JOBPORTAL_PLUGIN_PREFIX . 'candidate-service-order',
    'jobportal_service_order_vars',
    array(
        'ajax_url' => JOBPORTAL_AJAX_URL,
        'not_service' => esc_html__('No service found', 'jobportal-framework'),
    )
);

global $current_user;
$user_id = $current_user->ID;
$jobportal_candidate = new JobPortal_candidate_package();
$check_candidate_package = $jobportal_candidate->user_candidate_package_available($user_id);
$posts_per_page = 10;
$args = array(
    'post_type' => 'service_order',
    'ignore_sticky_posts' => 1,
    'posts_per_page' => $posts_per_page,
    'offset' => (max(1, get_query_var('paged')) - 1) * $posts_per_page,
    'post_status' => 'publish',
    'meta_query' => array(
        array(
            'key' => JOBPORTAL_METABOX_PREFIX . 'service_order_author_id',
            'value' => $user_id,
            'compare' => '==',
        )
    ),
);
$data = new WP_Query($args);
?>

<div class="jobportal-service-order entry-my-page">
    <div class="search-dashboard-wrapper">
        <div class="search-left">
            <div class="select2-field">
                <select class="search-control jobportal-select2" name="service_status">
                    <option value=""><?php esc_html_e('All status', 'jobportal-framework') ?></option>
                    <option value="pending"><?php esc_html_e('Pending', 'jobportal-framework') ?></option>
                    <option value="inprogress"><?php esc_html_e('In Process', 'jobportal-framework') ?></option>
                    <option value="transferring"><?php esc_html_e('Transferring', 'jobportal-framework') ?></option>
                    <option value="canceled"><?php esc_html_e('Canceled', 'jobportal-framework') ?></option>
                    <option value="completed"><?php esc_html_e('Completed', 'jobportal-framework') ?></option>
                </select>
            </div>
            <div class="action-search">
                <input class="service-search-control" type="text" name="service_search"
                       placeholder="<?php esc_attr_e('Search service title', 'jobportal-framework') ?>">
                <button class="btn-search">
                    <i class="far fa-search"></i>
                </button>
            </div>
        </div>
        <div class="search-right">
            <label class="text-sorting"><?php esc_html_e('Sort by', 'jobportal-framework') ?></label>
            <div class="select2-field">
                <select class="search-control action-sorting jobportal-select2" name="service_sort_by">
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
            } ?>" id="candidate-service-order">
                <thead>
                <tr>
                    <th><?php esc_html_e('ID', 'jobportal-framework') ?></th>
                    <th><?php esc_html_e('Service Title', 'jobportal-framework') ?></th>
                    <th><?php esc_html_e('Customer', 'jobportal-framework') ?></th>
                    <th><?php esc_html_e('Status', 'jobportal-framework') ?></th>
                    <th><?php esc_html_e('Price', 'jobportal-framework') ?></th>
                    <th><?php esc_html_e('Order Date', 'jobportal-framework') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php while ($data->have_posts()) : $data->the_post(); ?>
                    <?php
                    $order_id = get_the_ID();
                    $service_id = get_post_meta($order_id, JOBPORTAL_METABOX_PREFIX . 'service_order_item_id', true);
                    $service_skills = get_the_terms($service_id, 'service-skills');
                    $service_categories = get_the_terms($service_id, 'service-categories');
                    $service_location = get_the_terms($service_id, 'service-location');
                    $public_date = get_the_date(get_option('date_format'));
                    $thumbnail = get_the_post_thumbnail_url($service_id, '70x70');
                    $service_featured = get_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_featured', true);
                    $author_id = get_post_field('post_author', $order_id);
                    $author_name = get_the_author_meta('display_name', $author_id);
                    $status = get_post_meta($order_id, JOBPORTAL_METABOX_PREFIX . 'service_order_payment_status', true);
                    $total_price = get_post_meta($order_id, JOBPORTAL_METABOX_PREFIX . 'service_order_price', true);
                    $service_refund_content = get_post_meta($order_id, JOBPORTAL_METABOX_PREFIX . 'service_refund_content', true);

                    $active_date = strtotime(get_the_date('Y-m-d H:i:s'));
                    $current_time = strtotime(current_datetime()->format('Y-m-d H:i:s'));
                    $service_time_type = get_post_meta($order_id, JOBPORTAL_METABOX_PREFIX . 'service_order_time_type', true);
                    $number_delivery_time = intval(get_post_meta($order_id, JOBPORTAL_METABOX_PREFIX . 'service_order_number_time', true));
                    switch ($service_time_type) {
                        case 'hr':
                            $seconds = 60 * 60;
                            break;
                        case 'day':
                            $seconds = 60 * 60 * 24;
                            break;
                        case 'week':
                            $seconds = 60 * 60 * 24 * 7;
                            break;
                        case 'month':
                            $seconds = 60 * 60 * 24 * 30;
                            break;
                    }
                    if (is_numeric($active_date) && is_numeric($seconds) && is_numeric($number_delivery_time)) {
                        $expired_time = $active_date + ($seconds * $number_delivery_time);
                    } else {
                        $expired_time = 0;
                    }

                    if ($current_time < $expired_time) {
                        $seconds = $expired_time - $current_time;
                        $dtF = new \DateTime('@0');
                        $dtT = new \DateTime("@$seconds");
                        $expired_days = $dtF->diff($dtT)->format('%a');
                        $expired_hours = $dtF->diff($dtT)->format('%h');
                        $expired_minutes = $dtF->diff($dtT)->format('%i');
                        if($expired_days > 0){
                            if($expired_days === '1'){
                                $expired_date = sprintf(esc_html__('%1s day %2s hours', 'jobportal-framework'), $expired_days,$expired_hours);
                            } else {
                                $expired_date = sprintf(esc_html__('%1s days %2s hours', 'jobportal-framework'), $expired_days,$expired_hours);
                            }
                        } else {
                            if($expired_hours === '1'){
                                $expired_date = sprintf(esc_html__('%1s hour %2s minutes', 'jobportal-framework'), $expired_hours,$expired_minutes);
                            } else {
                                $expired_date = sprintf(esc_html__('%1s hours %2s minutes', 'jobportal-framework'), $expired_hours,$expired_minutes);
                            }
                        }
                    } else {
                        $expired_date = esc_html__('expired', 'jobportal-framework');
                        update_post_meta($order_id, JOBPORTAL_METABOX_PREFIX . 'service_order_payment_status', 'expired');
                    }
                    $status = get_post_meta($order_id, JOBPORTAL_METABOX_PREFIX . 'service_order_payment_status', true);
                    ?>
                    <tr>
                        <td>
                            <span><?php echo '#' . $order_id; ?></span>
                        </td>
                        <td>
                            <div class="service-header">
                                <?php if (!empty($thumbnail)) : ?>
                                    <img class="thumbnail" src="<?php echo $thumbnail; ?>" alt=""/>
                                <?php endif; ?>
                                <div class="content">
                                    <h3 class="title-my-service">
                                        <a href="<?php echo get_the_permalink($service_id) ?>">
                                            <?php echo get_the_title($service_id); ?>
                                            <?php if ($service_featured === '1') : ?>
                                                <span class="tooltip featured"
                                                      data-title="<?php esc_attr_e('Featured', 'jobportal-framework') ?>">
                                                    <img src="<?php echo esc_attr(JOBPORTAL_PLUGIN_URL . 'assets/images/icon-featured.svg'); ?>"
                                                         alt="<?php echo esc_attr__('featured', 'jobportal-framework') ?>">
                                                    </span>
                                            <?php endif; ?>
                                        </a>
                                    </h3>
                                    <p>
                                        <?php if (is_array($service_categories)) {
                                            foreach ($service_categories as $categories) { ?>
                                                <span class="cate"><?php esc_html_e($categories->name); ?></span>
                                            <?php }
                                        } ?>
                                        <?php if (is_array($service_skills)) {
                                            foreach ($service_skills as $skills) { ?>
                                                <?php esc_html_e('/ ' . $skills->name); ?>
                                            <?php }
                                        } ?>
                                        <?php if (is_array($service_location)) {
                                            foreach ($service_location as $location) { ?>
                                                <?php esc_html_e('/ ' . $location->name); ?>
                                            <?php }
                                        } ?>
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="author">
                            <?php echo $author_name; ?>
                        </td>
                        <td class="status">
                            <?php jobportal_service_order_status($status); ?>
                        </td>
                        <td class="price">
                            <?php echo $total_price; ?>
                        </td>
                        <td class="start-time">
                            <?php echo $public_date; ?>
                            <span>(<?php echo $expired_date; ?>)</span>
                        </td>
                        <?php if($status === 'inprogress' || $status === 'refund') : ?>
                            <td class="action-setting service-control">
                                <a href="#" class="icon-setting"><i class="fal fa-ellipsis-h"></i></a>
                                <ul class="action-dropdown">
                                    <?php switch ($status) {
                                        case 'inprogress': ?>
                                            <li><a class="btn-delivery" item-id="<?php echo esc_attr($order_id); ?>"
                                                   href="#"><?php esc_html_e('Transfer', 'jobportal-framework') ?></a></li>
                                            <li><a class="btn-canceled" item-id="<?php echo esc_attr($order_id); ?>"
                                                   href="#"><?php esc_html_e('Cancel', 'jobportal-framework') ?></a></li>
                                            <?php break;
                                        case 'refund': ?>
                                            <?php if(!empty($service_refund_content)) : ?>
                                                <li><a class="btn-view-reason" order-id="<?php echo esc_attr($order_id); ?>" data-content-refund="<?php echo $service_refund_content;?>"
                                                       href="#form-service-view-reason"><?php esc_html_e('View reason', 'jobportal-framework') ?></a>
                                                </li>
                                            <?php else: ?>
                                                <li><a class="btn-add-to-message" href="#" data-text="<?php echo esc_attr__('Refund reason text is empty'); ?>"><?php esc_html_e('View reason', 'jobportal-framework'); ?></a></li>
                                            <?php endif;?>
                                            <?php break;
                                    } ?>
                                </ul>
                            </td>
                        <?php else: ?>
                            <td></td>
                        <?php endif; ?>
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
