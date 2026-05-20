<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!is_user_logged_in()) {
    jobportal_get_template('global/access-denied.php', array('type' => 'not_login'));
    return;
}
global $current_user;
$user_id = $current_user->ID;
$service_id = get_the_ID();
$service_id = isset($_GET['service_id']) ? jobportal_clean(wp_unslash($_GET['service_id'])) : '';

if (!empty($service_id)) {
    jobportal_get_template('service/edit.php');
} else {
    global $current_user;
    wp_get_current_user();
    $user_id = $current_user->ID;

    $paid_submission_type = jobportal_get_option('candidate_paid_submission_type');
    $jobportal_candidate_package = new JobPortal_candidate_package();
    $check_candidate_package = $jobportal_candidate_package->user_candidate_package_available($user_id);

    $posts_per_page = 10;
    wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'my-service');
    wp_localize_script(
        JOBPORTAL_PLUGIN_PREFIX . 'my-service',
        'jobportal_candidate_service_vars',
        array(
            'ajax_url'    => JOBPORTAL_AJAX_URL,
            'not_service'   => esc_html__('No service found', 'jobportal-framework'),
        )
    );
    $tax_query = $meta_query = array();
    $args = array(
        'post_type'           => 'service',
        'post_status'         => array('publish', 'pending', 'pause'),
        'ignore_sticky_posts' => 1,
        'posts_per_page'      => $posts_per_page,
        'offset'              => (max(1, get_query_var('paged')) - 1) * $posts_per_page,
        'author'              => $user_id,
        'orderby'               => 'date',
    );
    $data = new WP_Query($args);
?>
    <div class="entry-my-page my-service">
        <div class="search-dashboard-wrapper">
            <div class="search-left">
                <div class="select2-field">
                    <select class="search-control jobportal-select2" name="service_status">
                        <option value=""><?php esc_html_e('All service', 'jobportal-framework') ?></option>
                        <option value="publish"><?php esc_html_e('Opening', 'jobportal-framework') ?></option>
                        <option value="pause"><?php esc_html_e('Paused', 'jobportal-framework') ?></option>
                        <option value="pending"><?php esc_html_e('Pending', 'jobportal-framework') ?></option>
                    </select>
                </div>
                <div class="action-search">
                    <input class="service-search-control" type="text" name="service_search" placeholder="<?php esc_attr_e('Search service title', 'jobportal-framework') ?>">
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
                        <option value="featured"><?php esc_html_e('Featured', 'jobportal-framework') ?></option>
                    </select>
                </div>
            </div>
        </div>
        <?php if ($data->have_posts()) { ?>
            <div class="table-dashboard-wapper">
                <table class="table-dashboard <?php if ($check_candidate_package == -1 || $check_candidate_package == 0) {
                                                    echo 'expired';
                                                } ?>" id="my-service">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('TITLE', 'jobportal-framework') ?></th>
                            <th><?php esc_html_e('Price', 'jobportal-framework') ?></th>
                            <th><?php esc_html_e('STATUS', 'jobportal-framework') ?></th>
                            <th><?php esc_html_e('POSTED', 'jobportal-framework') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($data->have_posts()) : $data->the_post(); ?>
                            <?php
                            $service_id = get_the_ID();
                            $status = get_post_status($service_id);
                            $service_skills = get_the_terms($service_id, 'service-skills');
                            $service_categories =  get_the_terms($service_id, 'service-categories');
                            $service_location =  get_the_terms($service_id, 'service-location');
                            $public_date = get_the_date(get_option('date_format'));
                            $thumbnail = get_the_post_thumbnail_url($service_id, '70x70');
                            $service_featured  = intval(get_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_featured', true));

                            $currency_sign_default = jobportal_get_option('currency_sign_default');
                            $number_start_price = get_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_price', true);
                            $start_price = jobportal_get_format_money($number_start_price);
                            ?>
                            <tr>
                                <td>
                                    <div class="service-header">
                                        <?php if (!empty($thumbnail)) : ?>
                                            <img class="thumbnail" src="<?php echo $thumbnail; ?>" alt="" />
                                        <?php endif; ?>
                                        <div class="content">
                                            <h3 class="title-my-service">
                                                <a href="<?php echo get_the_permalink($service_id) ?>">
                                                    <?php echo get_the_title($service_id); ?>
                                                    <?php if ($service_featured == 1) : ?>
                                                        <span class="tooltip featured" data-title="<?php esc_attr_e('Featured', 'jobportal-framework') ?>">
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
                                <td>
                                    <div class="price">
                                        <?php echo $start_price; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($status == 'publish') : ?>
                                        <span class="label label-open"><?php esc_html_e('Opening', 'jobportal-framework') ?></span>
                                    <?php endif; ?>
                                    <?php if ($status == 'pending') : ?>
                                        <span class="label label-pending"><?php esc_html_e('Pending', 'jobportal-framework') ?></span>
                                    <?php endif; ?>
                                    <?php if ($status == 'pause') : ?>
                                        <span class="label label-pause"><?php esc_html_e('Pause', 'jobportal-framework') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="start-time"><?php echo $public_date; ?></span>
                                </td>
                                <?php
                                ?>
                                <td class="action-setting service-control">
                                    <a href="#" class="icon-setting"><i class="fal fa-ellipsis-h"></i></a>
                                    <ul class="action-dropdown">
                                        <?php
                                        $service_dashboard_link = jobportal_get_permalink('service_dashboard');
                                        $candidate_package_number_service_featured = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'candidate_package_number_service_featured', $user_id);
                                        $user_demo = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'user_demo', $user_id);
                                        switch ($status) {
                                            case 'publish':
                                        ?>
                                                <li><a class="btn-edit"
                                                        href="<?php echo esc_url($service_dashboard_link); ?>?service_id=<?php echo esc_attr($service_id); ?>"><?php esc_html_e('Edit', 'jobportal-framework'); ?></a>
                                                </li>
                                                <?php if ($user_demo == 'yes') { ?>
                                                    <li><a class="btn-add-to-message" href="#"
                                                            data-text="<?php echo esc_attr__('This is a "Demo" account, so you can not change it', 'jobportal-framework'); ?>"><?php esc_html_e('Paused', 'jobportal-framework'); ?></a>
                                                    </li>
                                                <?php } else { ?>
                                                    <?php if ($check_candidate_package !== -1 && $check_candidate_package !== 0) { ?>
                                                        <li><a class="btn-pause" service-id="<?php echo esc_attr($service_id); ?>"
                                                                href="<?php echo get_the_permalink($service_id); ?>"><?php esc_html_e('Paused', 'jobportal-framework') ?></a>
                                                        </li>
                                                    <?php }
                                                    if ($candidate_package_number_service_featured > 0 && $service_featured !== 1 && $check_candidate_package !== -1  && $check_candidate_package !== 0) { ?>
                                                        <li><a class="btn-featured" service-id="<?php echo esc_attr($service_id); ?>" href="<?php echo get_the_permalink($service_id); ?>"><?php esc_html_e('Featured', 'jobportal-framework') ?></a></li>
                                                    <?php } ?>
                                                <?php } ?>
                                            <?php
                                                break;
                                            case 'pending': ?>
                                                <li><a class="btn-edit"
                                                        href="<?php echo esc_url($service_dashboard_link); ?>?service_id=<?php echo esc_attr($service_id); ?>"><?php esc_html_e('Edit', 'jobportal-framework'); ?></a>
                                                </li>
                                            <?php
                                                break;
                                            case 'pause':
                                            ?>
                                                <li><a class="btn-edit"
                                                        href="<?php echo esc_url($service_dashboard_link); ?>?service_id=<?php echo esc_attr($service_id); ?>"><?php esc_html_e('Edit', 'jobportal-framework'); ?></a>
                                                </li>
                                                <?php if ($check_candidate_package !== -1 && $check_candidate_package !== 0) { ?>
                                                    <li><a class="btn-show" service-id="<?php echo esc_attr($service_id); ?>"
                                                            href="<?php echo get_the_permalink($service_id); ?>"><?php esc_html_e('Continue', 'jobportal-framework'); ?>
                                                        </a>
                                                    </li>
                                        <?php }
                                                break;
                                        }
                                        ?>
                                    </ul>
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
        <?php $max_num_pages = $data->max_num_pages;
        $total_post = $data->found_posts;
        if ($total_post > $posts_per_page) { ?>
            <div class="pagination-dashboard">
                <?php jobportal_get_template('global/pagination.php', array('total_post' => $total_post, 'max_num_pages' => $max_num_pages, 'type' => 'dashboard', 'layout' => 'number'));
                wp_reset_postdata(); ?>
            </div>
        <?php } ?>
    </div>
<?php } ?>
