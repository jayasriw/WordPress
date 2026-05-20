<?php
if (!defined("ABSPATH")) {
    exit(); // Exit if accessed directly
}

wp_enqueue_script(CIVI_PLUGIN_PREFIX . 'candidate-submit');
$custom_field_candidate = civi_render_custom_field('candidate');
wp_localize_script(
    CIVI_PLUGIN_PREFIX . 'candidate-submit',
    'civi_candidate_vars',
    array(
        'ajax_url' => CIVI_AJAX_URL,
        'site_url' => get_site_url(),
        'text_present' => esc_attr__('Present', 'civi-framework'),
        'custom_field_candidate' => $custom_field_candidate,
        'save_strength_nonce' => wp_create_nonce('save_profile_strength'),
    )
);

$key_dashboard = apply_filters(
    'civi/dashboard/candidate/nav',
    [
        "candidate_dashboard" => esc_html__('Dashboard', 'civi-framework'),
        "candidate_profile" => esc_html__('Profile', 'civi-framework'),
        "my_jobs" => esc_html__('My Jobs', 'civi-framework'),
        "candidate_user_package" => esc_html__('Package', 'civi-framework'),
        "candidate_reviews" => esc_html__('My Reviews', 'civi-framework'),
        "candidate_company" => esc_html__('My Following', 'civi-framework'),
        "candidate_messages" => esc_html__('Messages', 'civi-framework'),
        "candidate_meetings" => esc_html__('Meetings', 'civi-framework'),
    ]
);

if (civi_get_option('enable_post_type_service') === '1') {
    $key_dashboard["candidate_service"] = esc_html__('Services', 'civi-framework');
}
$key_dashboard["candidate_settings"] = esc_html__('Settings', 'civi-framework');
$key_dashboard["candidate_logout"] = esc_html__('Logout', 'civi-framework');

$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$candidate_id = civi_get_post_id_candidate();
$profile_strength_percent = get_post_meta(
    $candidate_id,
    CIVI_METABOX_PREFIX . "candidate_profile_strength",
    true
);
if (empty($profile_strength_percent)) {
    $profile_strength_percent = 0;
}
?>

<div class="nav-dashboard-inner">
    <div class="bg-overlay"></div>
    <div class="nav-dashboard-wapper custom-scrollbar">
        <div class="nav-dashboard nav-candidate_dashboard">
            <div class="nav-dashboard-header">
                <div class="header-wrap">
                    <?php echo Civi_Templates::site_logo("dark"); ?>
                </div>
                <a href="#" class="closebtn">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </div>

            <?php if (in_array("civi_user_candidate", (array)$current_user->roles) || current_user_can('administrator')): ?>

                <ul class="list-nav-dashboard">

                    <?php foreach ($key_dashboard as $key => $value):

                        $show_candidate = civi_get_option("show_" . $key, "1");

                        if (!$show_candidate) {
                            continue;
                        }

                        $id = civi_get_option("civi_" . $key . "_page_id");
                        $image_candidate = civi_get_option("image_" . $key, "");
                        $type_candidate = civi_get_option("type_" . $key);

                        $class_active =
                            is_page($id) && $key !== "candidate_logout" ? "active" : "";

                        $link_url = "";
                        $link_url =
                            $key === "candidate_logout"
                            ? wp_logout_url(home_url())
                            : get_permalink($id);

                        $html_icon = "";
                        if (!empty($image_candidate["url"])) {
                            if (civi_get_option("type_icon_candidate") === "svg") {
                                $html_icon =
                                    '<object class="civi-svg" type="image/svg+xml" data="' .
                                    esc_url($image_candidate["url"]) .
                                    '"></object>';
                            } else {
                                $html_icon =
                                    '<img src="' .
                                    esc_url($image_candidate["url"]) .
                                    '" alt="' .
                                    $value .
                                    '"/>';
                            }
                        }
                    ?>
                        <li class="nav-item <?php esc_html_e($class_active); ?>">
                            <a href="<?php echo esc_url($link_url); ?>" data-title="<?php echo $value; ?>">
                                <?php if (!empty($image_candidate["url"])) { ?>
                                    <span class="image">
                                        <?php echo $html_icon; ?>
                                    </span>
                                <?php } ?>
                                <span><?php echo $value; ?></span>
                                <?php if ($key === "candidate_messages") { ?>
                                    <?php civi_get_total_unread_message(); ?>
                                <?php } ?>
                            </a>
                        </li>
                    <?php
                    endforeach; ?>

                </ul>

            <?php endif; ?>

            <div class="nav-profile-strength">
                <div class="profile-strength left-sidebar" style="--pct: <?php esc_attr_e(
                                                                                $profile_strength_percent
                                                                            ); ?>">
                    <div class="title">
                        <span><?php esc_html_e("Profile Strength:", "civi-framework"); ?></span>
                        <span><?php esc_attr_e($profile_strength_percent); ?></span><span>%</span>
                    </div>
                    <div class="left-strength">
                        <span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <a href="#" class="icon-nav-mobie">
        <i class="far fa-bars"></i>
    </a>
</div>
