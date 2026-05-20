<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $current_user;
$user_id = apply_filters('civi_modify_user_id', $current_user->ID);
$no_image_src = CIVI_PLUGIN_URL . 'assets/images/default-user-image.png';

$creator_user_id = get_post_meta($message_id, CIVI_METABOX_PREFIX . 'creator_message', true);
$recipient_user_id = get_post_meta($message_id, CIVI_METABOX_PREFIX . 'recipient_message', true);
$user_file_url = get_post_meta($message_id, CIVI_METABOX_PREFIX . 'mess_file_url', true);
if (!empty($user_file_url)) {
    $user_file_name = basename($user_file_url);
}
$user_thumbnail_url = get_the_post_thumbnail_url($message_id, 'full');
$avatar = get_the_author_meta('author_avatar_image_url', $creator_user_id);
$display_name = civi_get_user_display_name($creator_user_id);

if (intval($creator_user_id) == $user_id) {
    $author_id = get_post_field('post_author', $recipient_user_id);
} else {
    $author_id = $creator_user_id;
}
$name_author = civi_get_user_display_name($author_id);

$args_write = array(
    'post_type' => 'messages',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'order' => 'ASC',
    'meta_query' => array(
        array(
            'key' => CIVI_METABOX_PREFIX . 'post_message_reply',
            'value' => $message_id,
            'compare' => '=='
        )
    ),
);
$data_write = new WP_Query($args_write);

if (intval($creator_user_id) === $user_id) {
    $card = 'card-send';
} else {
    $card = 'card-receive';
}
?>
<div class="card-mess <?php echo esc_attr($card); ?>">
    <div class="thumb">
        <?php if (!empty($avatar)) : ?>
            <img src="<?php echo esc_url($avatar); ?>" alt="">
        <?php else : ?>
            <img src="<?php echo esc_url($no_image_src); ?>" alt="">
        <?php endif; ?>
    </div>
    <div class="detail">
        <div class="name">
            <?php if (intval($creator_user_id) === $user_id) : ?>
                <span class="uname"><?php esc_html_e('You', 'civi-framework'); ?></span>
            <?php else : ?>
                <span class="uname"><?php esc_html_e($display_name); ?></span>
            <?php endif; ?>
            <span class="date"><?php echo sprintf(esc_html__('%s ago', 'civi-framework'),  human_time_diff(get_the_time('U', $message_id), current_time('timestamp'))); ?></span>
        </div>
        <div class="desc">
            <?php echo get_the_excerpt($message_id); ?>
        </div>
        <?php if (has_post_thumbnail($message_id)) : ?>
            <div class="thumbnail">
                <?php echo get_the_post_thumbnail($message_id); ?>
                <a href="<?php echo esc_url($user_thumbnail_url); ?>" download class="download">
                    <i class="far fa-download" style="color: #fff;"></i>
                </a>
            </div>
        <?php endif; ?>
        <?php if (!empty($user_file_url)) : ?>
            <div class="file">
                <a href="<?php echo esc_url($user_file_url); ?>" download class="download">
                    <i class="far fa-download"></i>
                    <?php echo esc_html($user_file_name); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php if ($data_write->have_posts()) { ?>
    <?php while ($data_write->have_posts()) : $data_write->the_post();
        $message_id = get_the_ID();
        $creator_message =
            apply_filters(
                'civi_modify_user_id',
                get_post_meta($message_id, CIVI_METABOX_PREFIX . 'creator_message_user', true)
            );
        $file_url = get_post_meta($message_id, CIVI_METABOX_PREFIX . 'mess_file_url', true);
        $file_name = '';
        if (!empty($file_url)) {
            $file_name = basename($file_url);
        }
        $thumbnail_url = get_the_post_thumbnail_url($message_id, 'full');
        $avatar = get_the_author_meta('author_avatar_image_url', $creator_message);
        if (intval($creator_message) === $user_id) {
            $card = 'card-send';
        } else {
            $card = 'card-receive';
        }
        $time = human_time_diff(get_the_time('U', $message_id), current_time('timestamp'));
    ?>
        <div class="card-mess <?php echo esc_attr($card); ?>">
            <div class="thumb">
                <div class="thumb">
                    <?php if (!empty($avatar)) : ?>
                        <img src="<?php echo esc_url($avatar); ?>" alt="">
                    <?php else : ?>
                        <img src="<?php echo esc_url($no_image_src); ?>" alt="">
                    <?php endif; ?>
                </div>
            </div>
            <div class="detail">
                <div class="name">
                    <?php if (intval($creator_message) === $user_id) : ?>
                        <span class="uname"><?php esc_html_e('You', 'civi-framework'); ?></span>
                    <?php else : ?>
                        <span class="uname"><?php esc_html_e($name_author); ?></span>
                    <?php endif; ?>
                    <span class="date"><?php echo sprintf(esc_html__('%s ago', 'civi-framework'), $time); ?></span>
                </div>
                <div class="desc">
                    <?php echo get_the_excerpt($message_id); ?>
                </div>
                <?php if (has_post_thumbnail($message_id)) : ?>
                    <div class="thumbnail">
                        <?php echo get_the_post_thumbnail($message_id); ?>
                        <a href="<?php echo esc_url($thumbnail_url); ?>" download class="download">
                            <i class="far fa-download" style="color: #fff;"></i>
                        </a>
                    </div>
                <?php endif; ?>
                <?php if (!empty($file_url)) : ?>
                    <div class="file">
                        <a href="<?php echo esc_url($file_url); ?>" download class="download">
                            <i class="far fa-download"></i>
                            <?php echo esc_html($file_name); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>
<?php } ?>
