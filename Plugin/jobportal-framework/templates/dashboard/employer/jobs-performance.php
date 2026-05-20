<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$jobs_id = isset($_GET['jobs_id']) ? jobportal_clean(wp_unslash($_GET['jobs_id'])) : '';
$action = 'submit-meetings';

wp_enqueue_script('chart');
wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'chart');
wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'meetings');
wp_localize_script(
    JOBPORTAL_PLUGIN_PREFIX . 'meetings',
    'jobportal_meetings_vars',
    array(
        'ajax_url' => JOBPORTAL_AJAX_URL,
        'not_applicants' => esc_html__('No meetings found', 'jobportal-framework'),
    )
);
$number_days = '7';
$labels = array();
for ($i = $number_days; $i >= 0; $i--) {
    $date = strtotime(date("Y-m-d", strtotime("-" . $i . " day")));
    $labels[] = date_i18n(get_option('date_format'), $date);
}

wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'applicants-dashboard');
wp_localize_script(
    JOBPORTAL_PLUGIN_PREFIX . 'applicants-dashboard',
    'jobportal_applicants_dashboard_vars',
    array(
        'ajax_url' => JOBPORTAL_AJAX_URL,
        'not_applicants' => esc_html__('No applicants found', 'jobportal-framework'),
        'ajax_nonce' => wp_create_nonce('jobportal_ajax_nonce'),
    )
);
$id = get_the_ID();
$posts_per_page = 10;
global $current_user;
$user_id = $current_user->ID;
$tab_active = isset($_GET['tab']) ? jobportal_clean(wp_unslash($_GET['tab'])) : '';

$args_applicants = array(
    'post_type' => 'applicants',
    'ignore_sticky_posts' => 1,
    'post_status' => 'any',
    'posts_per_page' => $posts_per_page,
    'offset' => (max(1, get_query_var('paged')) - 1) * $posts_per_page,
    'meta_query' => array(
        'relation' => 'AND',
        array(
            'key' => JOBPORTAL_METABOX_PREFIX . 'applicants_jobs_id',
            'value' => $jobs_id,
            'compare' => '='
        )
    ),
);

$data_applicants = new WP_Query($args_applicants);
?>

<div class="entry-my-page jobs-performance-dashboard mettings-action-dashboard">
    <div class="entry-title">
        <h4>
            <a href="<?php echo get_post_permalink($jobs_id) ?>" target="_blank">
                <?php echo get_the_title($jobs_id); ?>
            </a>
        </h4>
    </div>
    <div class="tab-dashboard-active">
        <ul class="tab-list-active">
            <li class="tab-item <?php if ($tab_active == 'statics') { ?>active<?php } ?>"><a href="#tab-statics"><?php esc_html_e('Statics', 'jobportal-framework'); ?></a></li>
            <li class="tab-item <?php if ($tab_active == 'applicants') { ?>active<?php } ?>"><a href="#tab-applicants"><?php esc_html_e('Applicants', 'jobportal-framework'); ?>
                    (<?php esc_html_e($data_applicants->found_posts) ?>)</a></li>
        </ul>
        <div class="tab-content">
            <div class="tab-info-active <?php if ($tab_active == 'statics') { ?>active<?php } ?>" id="tab-statics">
                <div class="jobportal-chart-wrapper">
                    <div class="chart-header">
                        <h4 class="title-chart"><?php esc_html_e('Job views', 'jobportal-framework'); ?></h4>
                        <div class="form-chart">
                            <div class="select2-field">
                                <select name="chart-date" class="jobportal-select2">
                                    <option value="7"><?php esc_html_e('7 days', 'jobportal-framework'); ?></option>
                                    <option value="15"><?php esc_html_e('15 days', 'jobportal-framework'); ?></option>
                                    <option value="30"><?php esc_html_e('30 days', 'jobportal-framework'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <canvas id="jobportal-dashboard_chart" data-labels="<?php echo esc_attr(json_encode($labels)); ?>" data-values_view="<?php echo esc_attr(json_encode(jobportal_view_jobs_date($jobs_id, $number_days))); ?>" data-label_view="<?php esc_attr_e('Page View', 'jobportal-framework'); ?>" data-values_apply="<?php echo esc_attr(json_encode(jobportal_total_jobs_apply($jobs_id, $number_days))); ?>" data-label_apply="<?php esc_attr_e('Apply Click', 'jobportal-framework'); ?>" data-jobs-id="<?php echo $jobs_id ?>">
                    </canvas>
                    <div class="jobportal-loading-effect"><span class="jobportal-dual-ring"></span></div>
                </div>
            </div>
            <div class="tab-info-active applicants-dashboard jobs_details <?php if ($tab_active == 'applicants') { ?>active<?php } ?>" id="tab-applicants">
                <div class="search-dashboard-wrapper">
                    <div class="search-left">
                        <div class="action-search">
                            <input class="search-control" type="text" name="applicants_search" placeholder="<?php esc_attr_e('Find by name', 'jobportal-framework') ?>">
                            <button class="btn-search">
                                <i class="far fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="search-right">
                        <label class="text-sorting"><?php esc_html_e('Sort by', 'jobportal-framework') ?></label>
                        <div class="select2-field">
                            <select class="search-control action-sorting jobportal-select2" name="applicants_sort_by">
                                <option value="newest"><?php esc_html_e('Newest', 'jobportal-framework') ?></option>
                                <option value="oldest"><?php esc_html_e('Oldest', 'jobportal-framework') ?></option>
                            </select>
                        </div>
                    </div>
                </div>
                <?php if ($data_applicants->have_posts() && !empty($jobs_id)) { ?>
                    <div class="table-dashboard-wapper">
                        <table class="table-dashboard" id="my-applicants">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Name', 'jobportal-framework') ?></th>
                                    <th><?php esc_html_e('Status', 'jobportal-framework') ?></th>
                                    <th><?php esc_html_e('Information', 'jobportal-framework') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($data_applicants->have_posts()) : $data_applicants->the_post(); ?>
                                    <?php
                                    $id = get_the_ID();
                                    global $current_user;
                                    wp_get_current_user();
                                    $user_id = $current_user->ID;
                                    $public_date = get_the_date(get_option('date_format'));
                                    $jobs_id = get_post_meta($id, JOBPORTAL_METABOX_PREFIX . 'applicants_jobs_id', true);
                                    $applicants_email = get_post_meta($id, JOBPORTAL_METABOX_PREFIX . 'applicants_email', true);
                                    $applicants_phone = get_post_meta($id, JOBPORTAL_METABOX_PREFIX . 'applicants_phone', true);
                                    $applicants_message = get_post_meta($id, JOBPORTAL_METABOX_PREFIX . 'applicants_message', true);
                                    $applicants_cv = get_post_meta($id, JOBPORTAL_METABOX_PREFIX . 'applicants_cv', true);
                                    $applicants_status = get_post_meta($id, JOBPORTAL_METABOX_PREFIX . 'applicants_status', true);
                                    $author_id = get_post_field('post_author', $id);
                                    $candidate_id = '';
                                    if (!empty($author_id)) {
                                        $args_candidate = array(
                                            'post_type' => 'candidate',
                                            'posts_per_page' => 1,
                                            'author' => $author_id,
                                        );
                                        $current_user_posts = get_posts($args_candidate);
                                        $candidate_id = !empty($current_user_posts) ? $current_user_posts[0]->ID : '';
                                        $candidate_avatar = get_the_author_meta('author_avatar_image_url', $author_id);
                                    }
                                    $read_mess = get_post_meta($id, JOBPORTAL_METABOX_PREFIX . 'read_mess', true);
                                    $reply_mess = get_post_meta($id, JOBPORTAL_METABOX_PREFIX . 'reply_mess', true);
                                    ?>
                                    <tr>
                                        <td class="info-user">
                                            <?php if (!empty($candidate_avatar)) : ?>
                                                <div class="image-applicants"><img class="image-candidates" src="<?php echo esc_url($candidate_avatar) ?>" alt="" /></div>
                                            <?php else : ?>
                                                <div class="image-applicants"><i class="far fa-camera"></i></div>
                                            <?php endif; ?>
                                            <div class="info-details">
                                                <?php if (!empty(get_the_author())) { ?>
                                                    <h3><a href="<?php echo get_post_permalink($candidate_id); ?>"><?php echo get_the_author(); ?></a></h3>
                                                <?php } else { ?>
                                                    <h3><?php esc_html_e('User not logged in', 'jobportal-framework'); ?></h3>
                                                <?php } ?>
                                                <?php if (!empty(get_the_title())) { ?>
                                                    <div class="applied"><?php esc_html_e('Applied:', 'jobportal-framework') ?>
                                                        <a href="<?php echo esc_url(get_permalink($jobs_id)); ?>" target="_blank">
                                                            <span> <?php esc_html_e(get_the_title()); ?></span>
                                                            <i class="fas fa-external-link-alt"></i>
                                                        </a>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </td>
                                        <td class="status">
                                            <div class="approved">
                                                <?php echo jobportal_applicants_status($id); ?>
                                                <span class="applied-time"><?php esc_html_e('Applied:', 'jobportal-framework') ?><?php esc_html_e($public_date) ?></span>
                                            </div>
                                        </td>
                                        <td class="info">
                                            <?php if (!empty($applicants_email)) { ?>
                                                <span class="gmail"><?php esc_html_e($applicants_email) ?></span>
                                            <?php } ?>
                                            <?php if (!empty($applicants_phone)) { ?>
                                                <span class="phone"><?php esc_html_e($applicants_phone) ?></span>
                                            <?php } ?>
                                        </td>
                                        <td class="applicants-control action-setting">
                                            <div class="list-action">
                                                <?php if (!empty(get_the_author())) { ?>
                                                    <a href="#" class="action icon-video tooltip btn-reschedule-meetings" data-id="<?php echo esc_attr($id); ?>" data-title="<?php esc_attr_e('Meetings', 'jobportal-framework') ?>"><i class="fas fa-video-plus"></i></a>
                                                    <?php if ($reply_mess !== 'yes') : ?>
                                                        <a href="#" class="action icon-messages tooltip" id="btn-mees-applicants"
                                                            data-apply="<?php esc_html_e(get_the_title()); ?>"
                                                            data-id="<?php echo esc_attr($id); ?>"
                                                            data-mess="<?php echo $applicants_message; ?>"
                                                            data-jobs-id="<?php echo $jobs_id; ?>"
                                                            data-title="<?php esc_attr_e('Messages', 'jobportal-framework') ?>">
                                                            <i class="fab fa-facebook-messenger <?php if ($read_mess === 'yes') {
                                                                                                    echo 'active';
                                                                                                } ?>"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                <?php } ?>
                                                <a href="<?php echo esc_url($applicants_cv); ?>" class="action icon-download tooltip" data-title="<?php esc_attr_e('Download CV', 'jobportal-framework') ?>"><i class="fas fa-download"></i></a>
                                                <div class="action">
                                                    <a href="#" class="icon-setting"><i class="fal fa-ellipsis-h"></i></a>
                                                    <ul class="action-dropdown">
                                                        <?php if (empty($applicants_status)) { ?>
                                                            <li><a class="btn-approved" applicants-id="<?php echo esc_attr($id); ?>" href="#"><?php esc_html_e('Approved', 'jobportal-framework') ?></a></li>
                                                            <li><a class="btn-rejected" applicants-id="<?php echo esc_attr($id); ?>" href="#"><?php esc_html_e('Rejected', 'jobportal-framework') ?></a></li>
                                                            <?php } else {
                                                            if ($applicants_status == 'approved') { ?>
                                                                <li><a class="btn-rejected" applicants-id="<?php echo esc_attr($id); ?>" href="#"><?php esc_html_e('Rejected', 'jobportal-framework') ?></a>
                                                                </li>
                                                            <?php } else { ?>
                                                                <li><a class="btn-approved" applicants-id="<?php echo esc_attr($id); ?>" href="#"><?php esc_html_e('Approved', 'jobportal-framework') ?></a>
                                                                </li>
                                                        <?php }
                                                        } ?>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        <div class="jobportal-loading-effect"><span class="jobportal-dual-ring"></span></div>
                        <input name="applicants_jobs_id" type="hidden" value="<?php echo esc_attr($jobs_id); ?>" />
                    </div>
                <?php } else { ?>
                    <div class="item-not-found"><?php esc_html_e('No item found', 'jobportal-framework'); ?></div>
                <?php } ?>
                <?php $total_post = $data_applicants->found_posts;
                if ($total_post > $posts_per_page && !empty($jobs_id)) { ?>
                    <div class="pagination-dashboard">
                        <?php $max_num_pages = $data_applicants->max_num_pages;
                        jobportal_get_template('global/pagination.php', array('total_post' => $total_post, 'max_num_pages' => $max_num_pages, 'type' => 'dashboard', 'layout' => 'number'));
                        wp_reset_postdata(); ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <input type="hidden" name="mettings_action" value="<?php echo esc_attr($action) ?>" />
</div>

<?php function jobportal_reschedule_meeting()
{
    jobportal_get_template('jobs/meeting/reschedule.php');
}

add_action('wp_footer', 'jobportal_reschedule_meeting');
