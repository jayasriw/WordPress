<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $current_user;
$user_id = $current_user->ID;
$check_company_package = civi_get_field_check_candidate_package('contact_company');
$jobs_id = get_the_ID();
$jobs_select_company = get_post_meta($jobs_id, CIVI_METABOX_PREFIX . 'jobs_select_company');
$company_id = isset($jobs_select_company[0]) ? $jobs_select_company[0] : '';

// Social media settings
$enable_social_twitter = civi_get_option('enable_social_twitter', '1');
$enable_social_linkedin = civi_get_option('enable_social_linkedin', '1');
$enable_social_facebook = civi_get_option('enable_social_facebook', '1');
$enable_social_instagram = civi_get_option('enable_social_instagram', '1');

// Company data
if ($company_id === '') {
    // No company info: hide sidebar to avoid empty markup/notice spam
    return;
}

$company_logo = get_post_meta($company_id, CIVI_METABOX_PREFIX . 'company_logo');
$company_categories = get_the_terms($company_id, 'company-categories');
$company_founded = get_post_meta($company_id, CIVI_METABOX_PREFIX . 'company_founded');
$company_phone = get_post_meta($company_id, CIVI_METABOX_PREFIX . 'company_phone');
$company_email = get_post_meta($company_id, CIVI_METABOX_PREFIX . 'company_email');
$company_size = get_the_terms($company_id, 'company-size');
$company_website = get_post_meta($company_id, CIVI_METABOX_PREFIX . 'company_website');
$company_twitter = get_post_meta($company_id, CIVI_METABOX_PREFIX . 'company_twitter');
$company_facebook = get_post_meta($company_id, CIVI_METABOX_PREFIX . 'company_facebook');
$company_instagram = get_post_meta($company_id, CIVI_METABOX_PREFIX . 'company_instagram');
$company_linkedin = get_post_meta($company_id, CIVI_METABOX_PREFIX . 'company_linkedin');
$mycompany = get_post($company_id);
$meta_query = civi_posts_company($company_id);
$meta_query_post = civi_posts_company($company_id, 5);
$company_location = get_the_terms($company_id, 'company-location');

// Sticky sidebar
$classes = array();
$enable_sticky_sidebar_type = civi_get_option('enable_sticky_sidebar_type', 1);
if ($enable_sticky_sidebar_type) {
    $classes[] = 'has-sticky';
}

// Hide fields configuration
$hide_contact_company_fields = civi_get_option('hide_candidate_contact_company_fields', array());
if (!is_array($hide_contact_company_fields)) {
    $hide_contact_company_fields = array();
}

// Determine notice message
if ($user_id == 0) {
    $notice = esc_attr__("Please login to view", "civi-framework");
} elseif (in_array("civi_user_candidate", (array)$current_user->roles)) {
    $notice = ($check_company_package == -1 || $check_company_package == 0)
        ? esc_attr__("Please renew the package to view", "civi-framework")
        : '';
} else {
    $notice = esc_attr__("Please access the role Candidate and purchase the package to view", "civi-framework");
}

// Check if all fields are hidden
$all_fields_hidden = in_array("description", $hide_contact_company_fields) &&
    in_array("categories", $hide_contact_company_fields) &&
    in_array("size", $hide_contact_company_fields) &&
    in_array("founded", $hide_contact_company_fields) &&
    in_array("location", $hide_contact_company_fields) &&
    in_array("phone", $hide_contact_company_fields) &&
    in_array("email", $hide_contact_company_fields) &&
    in_array("social", $hide_contact_company_fields);

// Handle display logic
if ($user_id == 0 || ($check_company_package == -1 || $check_company_package == 0)) { ?>
    <div class="jobs-company-sidebar block-archive-sidebar company-sidebar <?php echo implode(" ", $classes); ?>">
        <div class="company-header">
            <?php if (!empty($company_logo[0]['url'])) : ?>
                <img src="<?php echo $company_logo[0]['url'] ?>" alt="" />
            <?php endif; ?>
            <?php if (get_the_title($company_id)) : ?>
                <div class="name">
                    <h2><a href="<?php echo get_post_permalink($company_id) ?>"><?php echo get_the_title($company_id); ?></a></h2>
                    <?php civi_company_green_tick($company_id); ?>
                    <p><a href="<?php echo get_post_permalink($company_id) ?>"><?php esc_html_e('View company profile', 'civi-framework') ?></a></p>
                </div>
            <?php endif; ?>
        </div>
        <?php if ($all_fields_hidden) : ?>
            <h3 class="title-company"><?php esc_html_e('Information', 'civi-framework'); ?></h3>
            <p class="notice">
                <i class="fal fa-exclamation-circle"></i>
                <?php echo esc_html($notice); ?>
            </p>
        <?php else: ?>
            <ul class="tab-company">
                <li class="tab-item"><a href="#tab-sidebar-overview"><?php esc_html_e('Overview', 'civi-framework'); ?></a></li>
                <li class="tab-item">
                    <a href="#tab-sidebar-jobs"><?php esc_html_e('Jobs', 'civi-framework'); ?>
                        <span><?php echo $meta_query->post_count ?></span>
                    </a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-info-company" id="tab-sidebar-overview">
                    <?php if (!empty($mycompany->post_content)) : ?>
                        <div class="content">
                            <?php if (!in_array("description", $hide_contact_company_fields)) : ?>
                                <?php echo wp_trim_words($mycompany->post_content, 25); ?>
                            <?php else: ?>
                                **************** ******************* **************** ****************
                                <a class="btn-add-to-message" href="#" data-text="<?php echo $notice; ?>">
                                    <i class="far fa-eye"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (is_array($company_categories)) : ?>
                        <div class="info">
                            <p class="title-info"><?php esc_html_e('Categories', 'civi-framework'); ?></p>
                            <div class="list-cate">
                                <?php if (!in_array("categories", $hide_contact_company_fields)) : ?>
                                    <?php foreach ($company_categories as $categories) {
                                        $cate_link = get_term_link($categories, 'jobs-categories'); ?>
                                        <a href="<?php echo esc_url($cate_link); ?>" class="cate civi-link-bottom">
                                            <?php echo $categories->name; ?>
                                        </a>
                                    <?php } ?>
                                <?php else: ?>
                                    *************
                                    <a class="btn-add-to-message" href="#" data-text="<?php echo $notice; ?>">
                                        <i class="far fa-eye"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (is_array($company_size)) : ?>
                        <div class="info">
                            <p class="title-info"><?php esc_html_e('Company size', 'civi-framework'); ?></p>
                            <div class="list-cate">
                                <?php if (!in_array("size", $hide_contact_company_fields)) : ?>
                                    <?php foreach ($company_size as $size) {
                                        echo $size->name;
                                    } ?>
                                <?php else: ?>
                                    *************
                                    <a class="btn-add-to-message" href="#" data-text="<?php echo $notice; ?>">
                                        <i class="far fa-eye"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($company_founded[0])) : ?>
                        <div class="info">
                            <p class="title-info"><?php esc_html_e('Founded in', 'civi-framework'); ?></p>
                            <p class="details-info">
                                <?php if (!in_array("founded", $hide_contact_company_fields)) : ?>
                                    <?php echo $company_founded[0]; ?>
                                <?php else: ?>
                                    *************
                                    <a class="btn-add-to-message" href="#" data-text="<?php echo $notice; ?>">
                                        <i class="far fa-eye"></i>
                                    </a>
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php endif; ?>
                    <?php if (is_array($company_location)) : ?>
                        <div class="info">
                            <p class="title-info"><?php esc_html_e('Location', 'civi-framework'); ?></p>
                            <p class="details-info">
                                <?php if (!in_array("location", $hide_contact_company_fields)) : ?>
                                    <?php foreach ($company_location as $location) { ?>
                                        <span><?php echo $location->name; ?></span>
                                    <?php } ?>
                                <?php else: ?>
                                    *************
                                    <a class="btn-add-to-message" href="#" data-text="<?php echo $notice; ?>">
                                        <i class="far fa-eye"></i>
                                    </a>
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($company_phone[0])) : ?>
                        <div class="info">
                            <p class="title-info"><?php esc_html_e('Phone', 'civi-framework'); ?></p>
                            <p class="details-info company-phone">
                                <?php if (!in_array("phone", $hide_contact_company_fields)) : ?>
                                    <a href="tel:<?php echo $company_phone[0]; ?>" data-phone="<?php echo $company_phone[0]; ?>"><?php echo substr($company_phone[0], 0, strlen($company_phone[0]) - 4); ?>****</a><i class="fal fa-eye"></i>
                                <?php else: ?>
                                    ***********
                                    <a class="btn-add-to-message" href="#" data-text="<?php echo $notice; ?>">
                                        <i class="far fa-eye"></i>
                                    </a>
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($company_email[0])) : ?>
                        <div class="info">
                            <p class="title-info"><?php esc_html_e('Email', 'civi-framework'); ?></p>
                            <p class="details-info email">
                                <?php if (!in_array("email", $hide_contact_company_fields)) : ?>
                                    <a href="mailto:<?php echo $company_email[0]; ?>"><?php echo $company_email[0]; ?></a>
                                <?php else: ?>
                                    *************
                                    <a class="btn-add-to-message" href="#" data-text="<?php echo $notice; ?>">
                                        <i class="far fa-eye"></i>
                                    </a>
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($company_website[0])) :
                        $remove_url = array("http://", "https://");
                        $name_website = str_replace($remove_url, "", $company_website[0]);

                        preg_match_all('/\p{L}/u', $name_website, $matches);
                        $length_website = count($matches[0]);
                        if ($length_website > 15) {
                            $name_website = 'website';
                        }
                    ?>
                        <a href="#" class="civi-button button-outline btn-add-to-message button-block" data-text="<?php echo $notice; ?>">
                            <?php esc_html_e('Visit ', 'civi-framework'); ?><?php echo $name_website ?><i class="fas fa-external-link"></i>
                        </a>
                    <?php endif; ?>
                    <?php civi_get_template('company/messages.php', array('company_id' => $company_id)); ?>
                    <ul class="list-social">
                        <?php if (!in_array("social", $hide_contact_company_fields)) : ?>
                            <?php if (!empty($company_facebook[0]) && $enable_social_facebook == 1) : ?>
                                <li><a href="<?php echo $company_facebook[0]; ?>"><i class="fab fa-facebook-f"></i></a></li>
                            <?php endif; ?>
                            <?php if (!empty($company_twitter[0]) && $enable_social_twitter == 1) : ?>
                                <li><a href="<?php echo $company_twitter[0]; ?>">
                                        <!-- fab fa-twitter -->
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currrentColor">
                                            <path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z" />
                                        </svg>
                                    </a></li>
                            <?php endif; ?>
                            <?php if (!empty($company_linkedin[0]) && $enable_social_linkedin == 1) : ?>
                                <li><a href="<?php echo $company_linkedin[0]; ?>"><i class="fab fa-linkedin"></i></a></li>
                            <?php endif; ?>
                            <?php if (!empty($company_instagram[0]) && $enable_social_instagram == 1) : ?>
                                <li><a href="<?php echo $company_instagram[0]; ?>"><i class="fab fa-instagram"></i></a></li>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if (!empty($company_facebook[0]) && $enable_social_facebook == 1) : ?>
                                <li><a class="btn-add-to-message" href="#" data-text="<?php echo $notice; ?>"><i class="fab fa-facebook-f"></i></a></li>
                            <?php endif; ?>
                            <?php if (!empty($company_twitter[0]) && $enable_social_twitter == 1) : ?>
                                <li><a class="btn-add-to-message" href="#" data-text="<?php echo $notice; ?>">
                                        <!-- fab fa-twitter -->
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currrentColor">
                                            <path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z" />
                                        </svg>
                                    </a></li>
                            <?php endif; ?>
                            <?php if (!empty($company_linkedin[0]) && $enable_social_linkedin == 1) : ?>
                                <li><a class="btn-add-to-message" href="#" data-text="<?php echo $notice; ?>"><i class="fab fa-linkedin"></i></a></li>
                            <?php endif; ?>
                            <?php if (!empty($company_instagram[0]) && $enable_social_instagram == 1) : ?>
                                <li><a class="btn-add-to-message" href="#" data-text="<?php echo $notice; ?>"><i class="fab fa-instagram"></i></a></li>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php civi_get_social_network($company_id, 'company'); ?>
                    </ul>
                </div>
                <div class="tab-info-company" id="tab-sidebar-jobs">
                    <ul class="list-jobs">
                        <?php foreach ($meta_query_post->posts as $post) {
                            $id_job = $post->ID;
                        ?>
                            <li class="list-items">
                                <h6 class="title"><a href="<?php echo get_post_permalink($id_job) ?>"><?php echo get_the_title($id_job); ?></a></h6>
                                <div class="info-company">
                                    <?php $jobs_categories = get_the_terms($post->ID, 'jobs-categories'); ?>
                                    <?php if (is_array($jobs_categories)) { ?>
                                        <div class="categories-wrapper">
                                            <?php foreach ($jobs_categories as $categories) {
                                                $cate_link = get_term_link($categories, 'jobs-categories'); ?>
                                                <div class="cate-wrapper">
                                                    <a href="<?php echo esc_url($cate_link); ?>" class="cate civi-link-bottom">
                                                        <?php echo $categories->name; ?>
                                                    </a>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </li>
                        <?php } ?>
                    </ul>
                    <a href="<?php echo esc_url(get_post_type_archive_link('jobs')) . '/?company_id=' . $company_id ?>" class="civi-button button-outline button-block">
                        <?php esc_html_e('View all jobs', 'civi-framework'); ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php } elseif ($check_company_package == 1 || $check_company_package == 2) { ?>
    <div class="jobs-company-sidebar block-archive-sidebar <?php echo implode(" ", $classes); ?>">
        <div class="company-header">
            <?php if (!empty($company_logo[0]['url'])) : ?>
                <img src="<?php echo $company_logo[0]['url'] ?>" alt="" />
            <?php endif; ?>
            <?php if (get_the_title($company_id)) : ?>
                <div class="name">
                    <h2><a href="<?php echo get_post_permalink($company_id) ?>"><?php echo get_the_title($company_id); ?></a></h2>
                    <?php civi_company_green_tick($company_id); ?>
                    <p><a href="<?php echo get_post_permalink($company_id) ?>"><?php esc_html_e('View company profile', 'civi-framework') ?></a></p>
                </div>
            <?php endif; ?>
        </div>
        <ul class="tab-company">
            <li class="tab-item"><a href="#tab-sidebar-overview"><?php esc_html_e('Overview', 'civi-framework'); ?></a></li>
            <li class="tab-item">
                <a href="#tab-sidebar-jobs"><?php esc_html_e('Jobs', 'civi-framework'); ?>
                    <span><?php echo $meta_query->post_count ?></span>
                </a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-info-company" id="tab-sidebar-overview">
                <?php if (!empty($mycompany->post_content)) : ?>
                    <div class="content"><?php echo wp_trim_words($mycompany->post_content, 25); ?></div>
                <?php endif; ?>
                <?php if (is_array($company_categories)) : ?>
                    <div class="info">
                        <p class="title-info"><?php esc_html_e('Categories', 'civi-framework'); ?></p>
                        <div class="list-cate">
                            <?php foreach ($company_categories as $categories) {
                                $cate_link = get_term_link($categories, 'jobs-categories'); ?>
                                <a href="<?php echo esc_url($cate_link); ?>" class="cate civi-link-bottom">
                                    <?php echo $categories->name; ?>
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (is_array($company_size)) : ?>
                    <div class="info">
                        <p class="title-info"><?php esc_html_e('Company size', 'civi-framework'); ?></p>
                        <div class="list-cate">
                            <?php foreach ($company_size as $size) {
                                echo $size->name;
                            } ?>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (!empty($company_founded[0])) : ?>
                    <div class="info">
                        <p class="title-info"><?php esc_html_e('Founded in', 'civi-framework'); ?></p>
                        <p class="details-info"><?php echo $company_founded[0]; ?></p>
                    </div>
                <?php endif; ?>
                <?php if (is_array($company_location)) : ?>
                    <div class="info">
                        <p class="title-info"><?php esc_html_e('Location', 'civi-framework'); ?></p>
                        <p class="details-info">
                            <?php foreach ($company_location as $location) { ?>
                                <span><?php echo $location->name; ?></span>
                            <?php } ?>
                        </p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($company_phone[0])) : ?>
                    <div class="info">
                        <p class="title-info"><?php esc_html_e('Phone', 'civi-framework'); ?></p>
                        <p class="details-info company-phone">
                            <a href="tel:<?php echo $company_phone[0]; ?>" data-phone="<?php echo $company_phone[0]; ?>"><?php echo substr($company_phone[0], 0, strlen($company_phone[0]) - 4); ?>****</a><i class="fal fa-eye"></i>
                        </p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($company_email[0])) : ?>
                    <div class="info">
                        <p class="title-info"><?php esc_html_e('Email', 'civi-framework'); ?></p>
                        <p class="details-info email">
                            <a href="mailto:<?php echo esc_attr($company_email[0]); ?>"><?php echo esc_html($company_email[0]); ?></a>
                        </p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($company_website[0])) :
                    $remove_url = array("http://", "https://");
                    $name_website = str_replace($remove_url, "", $company_website[0]);

                    preg_match_all('/\p{L}/u', $name_website, $matches);
                    $length_website = count($matches[0]);
                    if ($length_website > 15) {
                        $name_website = 'website';
                    }
                ?>
                    <a href="<?php echo $company_website[0]; ?>" class="civi-button button-outline button-block button-visit" target="_blank"><?php esc_html_e('Visit ', 'civi-framework'); ?><?php echo $name_website ?><i class="fas fa-external-link"></i></a>
                <?php endif; ?>
                <?php civi_get_template('company/messages.php', array('company_id' => $company_id)); ?>
                <ul class="list-social">
                    <?php if (!empty($company_facebook[0]) && $enable_social_facebook == 1) : ?>
                        <li><a href="<?php echo $company_facebook[0]; ?>"><i class="fab fa-facebook-f"></i></a></li>
                    <?php endif; ?>
                    <?php if (!empty($company_twitter[0]) && $enable_social_twitter == 1) : ?>
                        <li><a href="<?php echo $company_twitter[0]; ?>">
                                <!-- fab fa-twitter -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currrentColor">
                                    <path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z" />
                                </svg>
                            </a></li>
                    <?php endif; ?>
                    <?php if (!empty($company_linkedin[0]) && $enable_social_linkedin == 1) : ?>
                        <li><a href="<?php echo $company_linkedin[0]; ?>"><i class="fab fa-linkedin"></i></a></li>
                    <?php endif; ?>
                    <?php if (!empty($company_instagram[0]) && $enable_social_instagram == 1) : ?>
                        <li><a href="<?php echo $company_instagram[0]; ?>"><i class="fab fa-instagram"></i></a></li>
                    <?php endif; ?>
                    <?php civi_get_social_network($company_id, 'company'); ?>
                </ul>
            </div>
            <div class="tab-info-company" id="tab-sidebar-jobs">
                <ul class="list-jobs">
                    <?php foreach ($meta_query_post->posts as $post) {
                        $id_job = $post->ID;
                    ?>
                        <li class="list-items">
                            <h6 class="title"><a href="<?php echo get_post_permalink($id_job) ?>"><?php echo get_the_title($id_job); ?></a></h6>
                            <div class="info-company">
                                <?php $jobs_categories = get_the_terms($post->ID, 'jobs-categories'); ?>
                                <?php if (is_array($jobs_categories)) { ?>
                                    <div class="categories-wrapper">
                                        <?php foreach ($jobs_categories as $categories) {
                                            $cate_link = get_term_link($categories, 'jobs-categories'); ?>
                                            <div class="cate-wrapper">
                                                <a href="<?php echo esc_url($cate_link); ?>" class="cate civi-link-bottom">
                                                    <?php echo $categories->name; ?>
                                                </a>
                                            </div>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </li>
                    <?php } ?>
                </ul>
                <a href="<?php echo esc_url(get_post_type_archive_link('jobs')) . '/?company_id=' . $company_id ?>" class="civi-button button-outline button-block">
                    <?php esc_html_e('View all jobs', 'civi-framework'); ?>
                </a>
            </div>
        </div>
    </div>
<?php } ?>
