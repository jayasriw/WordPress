<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $current_user;
$user_id = apply_filters('civi_modify_user_id', $current_user->ID);
$no_image_src = CIVI_PLUGIN_URL . 'assets/images/default-user-image.png';
$max_pages = isset($max_pages) ? $max_pages : 1;
$status = isset($status) ? $status : 'all';
?>
<ul class="message-list-items">
    <?php while ($data_list->have_posts()) : $data_list->the_post();
        $message_id = get_the_ID();
        $creator_message = get_post_meta($message_id, CIVI_METABOX_PREFIX . 'creator_message', true);
        $post_status = get_post_status($message_id);

        if (intval($creator_message) == $user_id) {
            $recipient = get_post_meta($message_id, CIVI_METABOX_PREFIX . 'recipient_message', true);
            $author_id = get_post_field('post_author', $recipient);
        } else {
            $author_id = $creator_message;
        }

        $class_status = '';
        if ($post_status == 'pending') {
            $class_status = 'unread';
        }

        $name = civi_get_user_display_name($author_id);
        $avatar = get_the_author_meta('author_avatar_image_url', $author_id);
        $time = human_time_diff(get_the_time('U', $message_id), current_time('timestamp'));

        $author_user = get_userdata($author_id);
        $author_roles = !empty($author_user->roles) ? $author_user->roles : array();
        $is_candidate = in_array('civi_user_candidate', $author_roles);
        $profile_url = '';
        if ($is_candidate) {
            $candidate_post_id = get_user_meta($author_id, 'civi-cpt_id', true);
            if (!empty($candidate_post_id)) {
                $profile_url = get_post_permalink($candidate_post_id);
            }
        }
    ?>
        <li class="list-user <?php echo esc_attr($class_status) ?>" data-mess-id="<?php echo esc_attr($message_id) ?>">
            <div class="thumb">
                <?php if ($profile_url) : ?>
                    <a href="<?php echo esc_url($profile_url); ?>" title="<?php esc_attr_e('View Profile', 'civi-framework'); ?>">
                    <?php endif; ?>
                    <?php if (!empty($avatar)) : ?>
                        <img src="<?php echo esc_url($avatar); ?>" alt="<?php esc_attr_e($name); ?>">
                    <?php else : ?>
                        <img src="<?php echo esc_url($no_image_src); ?>" alt="<?php esc_attr_e($name); ?>">
                    <?php endif; ?>
                    <?php if ($profile_url) : ?>
                    </a>
                <?php endif; ?>
            </div>
            <div class="detail">
                <div class="name">
                    <?php if ($profile_url) : ?>
                        <a href="<?php echo esc_url($profile_url); ?>" class="uname">
                            <span><?php esc_html_e($name); ?></span>
                        </a>
                    <?php else : ?>
                        <span class="uname"><?php esc_html_e($name); ?></span>
                    <?php endif; ?>
                    <span class="date"><?php echo sprintf(esc_html__('%s ago', 'civi-framework'), $time); ?></span>
                </div>
                <div class="desc">
                    <?php echo wp_trim_words(get_the_excerpt($message_id), 12); ?>
                </div>
            </div>
        </li>
    <?php endwhile; ?>
</ul>
<?php if ($max_pages > 1) : ?>
    <div class="load-more-messages" data-status="<?php echo esc_attr($status); ?>">
        <button class="btn-load-more" type="button">
            <span class="text"><?php esc_html_e('Load More', 'civi-framework'); ?></span>
            <span class="loading" style="display:none;"><i class="far fa-spinner fa-spin"></i></span>
        </button>
    </div>
<?php endif; ?>
