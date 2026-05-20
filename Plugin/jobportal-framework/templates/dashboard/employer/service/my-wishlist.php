<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'employer-wishlist');
wp_localize_script(
    JOBPORTAL_PLUGIN_PREFIX . 'employer-wishlist',
    'jobportal_employer_wishlist_vars',
    array(
        'ajax_url' => JOBPORTAL_AJAX_URL,
        'not_service' => esc_html__('No service found', 'jobportal-framework'),
    )
);

global $current_user;
$user_id = $current_user->ID;
$user_demo = get_the_author_meta(JOBPORTAL_METABOX_PREFIX . 'user_demo', $user_id);
$service_wishlist = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'service_wishlist', true);
$posts_per_page = 10;

if (empty($service_wishlist)) {
    $service_wishlist = array(0);
}
$args = array(
    'post_type' => 'service',
    'post__in' => $service_wishlist,
    'ignore_sticky_posts' => 1,
    'posts_per_page' => $posts_per_page,
    'offset' => (max(1, get_query_var('paged')) - 1) * $posts_per_page,
);
$data = new WP_Query($args);
?>

<div class="jobportal-employer-wishlist entry-my-page">
    <div class="search-dashboard-wrapper">
        <div class="search-left">
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
                </select>
            </div>
        </div>
    </div>
    <?php if ($data->have_posts()) { ?>
        <div class="table-dashboard-wapper">
            <table class="table-dashboard" id="employer-wishlist">
                <thead>
                <tr>
                    <th><?php esc_html_e('Service Title', 'jobportal-framework') ?></th>
                    <th><?php esc_html_e('Provider', 'jobportal-framework') ?></th>
                    <th><?php esc_html_e('Price', 'jobportal-framework') ?></th>
                    <th><?php esc_html_e('Posted Date', 'jobportal-framework') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php while ($data->have_posts()) : $data->the_post(); ?>
                    <?php
                    $service_id = get_the_ID();
                    $service_skills = get_the_terms($service_id, 'service-skills');
                    $service_categories =  get_the_terms($service_id, 'service-categories');
                    $service_location =  get_the_terms($service_id, 'service-location');
                    $public_date = get_the_date(get_option('date_format'));
                    $thumbnail = get_the_post_thumbnail_url($service_id, '70x70');
                    $author_id = get_post_field( 'post_author', $service_id );
                    $author_name = get_the_author_meta( 'display_name', $author_id );
                    $args_candidate = array(
                        'post_type' => 'candidate',
                        'posts_per_page' => 1,
                        'author' => $author_id,
                    );
                    $current_user_posts = get_posts($args_candidate);
                    $candidate_id = !empty($current_user_posts) ? $current_user_posts[0]->ID : '';
                    $service_featured  = intval(get_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_featured', true));

                    $currency_sign_default = jobportal_get_option('currency_sign_default');
                    $number_start_price = get_post_meta($service_id, JOBPORTAL_METABOX_PREFIX . 'service_price', true);
                    $start_price = jobportal_get_format_money($number_start_price);
                    ?>
                    <tr>
                        <td>
                            <div class="service-header">
                                <?php if(!empty($thumbnail)) :?>
                                    <img class="thumbnail" src="<?php echo $thumbnail;?>" alt="" />
                                <?php endif; ?>
                                <div class="content">
                                    <h3 class="title-my-service">
                                        <a href="<?php echo get_the_permalink($service_id)?>">
                                            <?php echo get_the_title($service_id); ?>
                                            <?php if($service_featured == 1) : ?>
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
                        <td class="author">
                            <a href="<?php echo get_post_permalink($candidate_id) ?>"><?php echo $author_name;?></a>
                        </td>
                        <td class="price">
                            <?php echo $start_price;?>
                        </td>
                        <td class="start-time">
                            <?php echo $public_date; ?>
                        </td>
                        <td class="action-setting service-control">
                            <a href="#" class="icon-setting"><i class="fal fa-ellipsis-h"></i></a>
                            <ul class="action-dropdown">
                                <?php if ($user_demo == 'yes') : ?>
                                    <li><a class="btn-add-to-message" data-text="<?php echo esc_attr__('This is a "Demo" account so you not cant delete it', 'jobportal-framework'); ?>" href="#"><?php esc_html_e('Delete', 'jobportal-framework') ?></a></li>
                                <?php else : ?>
                                    <li><a class="btn-delete" service-id="<?php echo esc_attr($service_id); ?>" href="#"><?php esc_html_e('Delete', 'jobportal-framework') ?></a></li>
                                <?php endif; ?>
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
    <?php $total_post = $data->found_posts;
    if ($total_post > $posts_per_page) { ?>
        <div class="pagination-dashboard pagination-wishlist">
            <?php $max_num_pages = $data->max_num_pages;
            jobportal_get_template('global/pagination.php', array('total_post' => $total_post, 'max_num_pages' => $max_num_pages, 'type' => 'dashboard', 'layout' => 'number'));
            wp_reset_postdata(); ?>
        </div>
    <?php } ?>

</div>
