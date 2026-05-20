<?php
if (!defined('ABSPATH')) {
    exit;
}
global $current_user;
$user_id = apply_filters('civi_modify_user_id', $current_user->ID);
$user_demo = get_the_author_meta(CIVI_METABOX_PREFIX . 'user_demo', $user_id);
$no_image_src = CIVI_PLUGIN_URL . 'assets/images/default-user-image.png';

$creator_message = get_post_meta($message_id, CIVI_METABOX_PREFIX . 'creator_message', true);
$recipient_message = get_post_meta($message_id, CIVI_METABOX_PREFIX . 'recipient_message', true);

if (intval($creator_message) == $user_id) {
    $author_id = get_post_field('post_author', $recipient_message);
} else {
    $author_id = $creator_message;
}

$author_id = intval($author_id);
if (empty($author_id)) {
    $author_id = 0;
}

$name = civi_get_user_display_name($author_id);
$title = get_the_title($message_id);

$avatar = get_the_author_meta('author_avatar_image_url', $author_id);
if (empty($avatar)) {
    $avatar = $no_image_src;
}

$phone = get_the_author_meta(CIVI_METABOX_PREFIX . 'author_mobile_number', $author_id);

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
<div class="left">
    <div class="thumb">
        <?php if ($profile_url) : ?>
            <a href="<?php echo esc_url($profile_url); ?>" title="<?php esc_attr_e('View Profile', 'civi-framework'); ?>">
        <?php endif; ?>
                <img src="<?php echo esc_url($avatar); ?>" alt="<?php esc_attr_e($name); ?>">
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
        </div>
        <?php if (!empty($title)) : ?>
            <div class="info">
                <?php if ($profile_url) : ?>
                    <a href="<?php echo esc_url($profile_url); ?>">
                        <?php esc_html_e($title); ?>
                    </a>
                <?php else : ?>
                    <?php esc_html_e($title); ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<div class="right">
    <?php if (!empty($phone)) : ?>
        <a href="tel:<?php esc_attr_e($phone); ?>" class="action phone tooltip" data-title="<?php esc_attr_e('Phone', 'civi-framework'); ?>">
            <i class="fas fa-phone-alt"></i>
        </a>
    <?php endif; ?>
    <div class="action action-setting">
        <a href="#" class="icon-setting"><i class="fal fa-ellipsis-v"></i></a>
        <ul class="action-dropdown">
            <?php if ($user_demo == 'yes') : ?>
                <li><a class="btn-add-to-message" data-text="<?php echo esc_attr__('This is a "Demo" account so you cannot delete it', 'civi-framework'); ?>" href="#"><?php esc_html_e('Delete', 'civi-framework'); ?></a></li>
            <?php else : ?>
                <li><a class="btn-delete" data-mess-id="<?php esc_attr_e($message_id); ?>" href="#"><?php esc_html_e('Delete', 'civi-framework'); ?></a></li>
            <?php endif; ?>
        </ul>
    </div>
</div>
