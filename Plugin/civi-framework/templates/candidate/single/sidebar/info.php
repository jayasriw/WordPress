<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $current_user;
$user_id = $current_user->ID;
$candidate_id              = get_the_ID();
$post_author_id = get_post_field('post_author', $candidate_id);
$candidate_salary          = !empty(get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_offer_salary')) ? get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_offer_salary')[0] : '';
$candidate_yoe             = get_the_terms($candidate_id, 'candidate_yoe');
$candidate_languages       = get_the_terms($candidate_id, 'candidate_languages');
$candidate_location        = get_the_terms($candidate_id, 'candidate_locations');
$candidate_gender          = get_the_terms($candidate_id, 'candidate_gender');
$candidate_qualification   = get_the_terms($candidate_id, 'candidate_qualification');
$candidate_ages            = get_the_terms($candidate_id, 'candidate_ages');
$candidate_phone           = get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_phone', true);
$candidate_email           = get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_email', true);
$candidate_twitter         = get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_twitter', true);
$candidate_facebook        = get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_facebook', true);
$candidate_instagram       = get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_instagram', true);
$candidate_linkedin        = get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_linkedin', true);

$enable_social_twitter     = civi_get_option('enable_social_twitter', '1');
$enable_social_linkedin    = civi_get_option('enable_social_linkedin', '1');
$enable_social_facebook    = civi_get_option('enable_social_facebook', '1');
$enable_social_instagram   = civi_get_option('enable_social_instagram', '1');

$classes = array();
$enable_sticky_sidebar_type = civi_get_option('enable_sticky_candidate_sidebar_type', 1);
if ($enable_sticky_sidebar_type) {
    $classes[] = 'has-sticky';
};

$check_package_employer = civi_get_field_check_employer_package('info');
$hide_info_candidate_fields = civi_get_option('hide_company_candidate_info_fields', array());
if (!is_array($hide_info_candidate_fields)) {
    $hide_info_candidate_fields = array();
}
if (in_array("civi_user_employer", (array)$current_user->roles)) {
    $notice =  esc_attr__("Please renew the package to view", "civi-framework");
} else {
    $notice =  esc_attr__("Please access the role Employer and purchase the package to view", "civi-framework");
}
?>
<?php if (($check_package_employer == -1 || $check_package_employer == 0) && $user_id != $post_author_id) { ?>
    <?php if (
        in_array("salary", $hide_info_candidate_fields) && in_array("time", $hide_info_candidate_fields) && in_array("languages", $hide_info_candidate_fields)
        && in_array("gender", $hide_info_candidate_fields) && in_array("qualification", $hide_info_candidate_fields) && in_array("age", $hide_info_candidate_fields)
        && in_array("phone", $hide_info_candidate_fields) && in_array("email", $hide_info_candidate_fields) && in_array("social", $hide_info_candidate_fields)
    ) : ?>
        <?php if (in_array("civi_user_employer", (array)$current_user->roles)) { ?>
            <div class="candidate-sidebar block-archive-sidebar candidate-sidebar <?php echo implode(" ", $classes); ?>">
                <h3 class="title-candidate"><?php esc_html_e('Information', 'civi-framework'); ?></h3>
                <p class="notice">
                    <i class="fal fa-exclamation-circle"></i>
                    <?php esc_html_e("Please renew the package to see the full information", "civi-framework"); ?>
                </p>
            </div>
        <?php } else { ?>
            <div class="candidate-sidebar block-archive-sidebar candidate-sidebar <?php echo implode(" ", $classes); ?>">
                <h3 class="title-candidate"><?php esc_html_e('Information', 'civi-framework'); ?></h3>
                <p class="notice">
                    <i class="fal fa-exclamation-circle"></i>
                    <?php esc_html_e("Please access the role Employer and purchase the package to view full information", "civi-framework"); ?>
                </p>
            </div>
        <?php } ?>
    <?php else: ?>
        <div class="candidate-sidebar block-archive-sidebar candidate-sidebar <?php echo implode(" ", $classes); ?>">
            <h3 class="title-candidate"><?php esc_html_e('Information', 'civi-framework'); ?></h3>
            <?php if (!empty($candidate_salary)) : ?>
                <div class="info">
                    <p class="title-info"><?php esc_html_e('Offered Salary', 'civi-framework'); ?></p>
                    <div class="details-info salary">
                        <?php if (!in_array("salary", $hide_info_candidate_fields)) : ?>
                            <?php civi_get_salary_candidate($candidate_id); ?>
                        <?php else: ?>
                            *************
                            <a class="btn-add-to-message" href="#"
                                data-text="<?php echo $notice; ?>">
                                <i class="far fa-eye"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (is_array($candidate_yoe)) : ?>
                <div class="info">
                    <p class="title-info"><?php esc_html_e('Experience time', 'civi-framework'); ?></p>
                    <div class="list-cate">
                        <?php if (!in_array("time", $hide_info_candidate_fields)) : ?>
                            <?php foreach ($candidate_yoe as $yoe) {
                                $yoe_link = get_term_link($yoe, 'candidate_yoe'); ?>
                                <a href="<?php echo esc_url($yoe_link); ?>">
                                    <?php esc_attr_e($yoe->name); ?>
                                </a>
                            <?php } ?>
                        <?php else: ?>
                            ********
                            <a class="btn-add-to-message" href="#"
                                data-text="<?php echo $notice; ?>">
                                <i class="far fa-eye"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (is_array($candidate_languages)) : ?>
                <div class="info">
                    <p class="title-info"><?php esc_html_e('Languages', 'civi-framework'); ?></p>
                    <div class="list-cate">
                        <?php if (!in_array("languages", $hide_info_candidate_fields)) : ?>
                            <?php foreach ($candidate_languages as $language) {
                                echo '<span>' . esc_attr($language->name) . '</span>';
                            } ?>
                        <?php else: ?>
                            *************
                            <a class="btn-add-to-message" href="#"
                                data-text="<?php echo $notice; ?>">
                                <i class="far fa-eye"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (!empty($candidate_gender)) : ?>
                <div class="info">
                    <p class="title-info"><?php esc_html_e('Gender', 'civi-framework'); ?></p>
                    <div class="list-cate">
                        <?php if (!in_array("gender", $hide_info_candidate_fields)) : ?>
                            <?php foreach ($candidate_gender as $gender) {
                                echo esc_attr_e($gender->name);
                            } ?>
                        <?php else: ?>
                            ******
                            <a class="btn-add-to-message" href="#"
                                data-text="<?php echo $notice; ?>">
                                <i class="far fa-eye"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (is_array($candidate_qualification)) : ?>
                <div class="info">
                    <p class="title-info"><?php esc_html_e('Qualification', 'civi-framework'); ?></p>
                    <div class="list-cate">
                        <?php if (!in_array("qualification", $hide_info_candidate_fields)) : ?>
                            <?php foreach ($candidate_qualification as $qualification) {
                                echo '<span>' . esc_attr($qualification->name) . '</span>';
                            } ?>
                        <?php else: ?>
                            *************
                            <a class="btn-add-to-message" href="#"
                                data-text="<?php echo $notice; ?>">
                                <i class="far fa-eye"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (is_array($candidate_ages)) : ?>
                <div class="info">
                    <p class="title-info"><?php esc_html_e('Age', 'civi-framework'); ?></p>
                    <div class="list-cate">
                        <?php if (!in_array("age", $hide_info_candidate_fields)) : ?>
                            <?php foreach ($candidate_ages as $ages) {
                                echo esc_attr_e($ages->name);
                            } ?>
                        <?php else: ?>
                            **********
                            <a class="btn-add-to-message" href="#"
                                data-text="<?php echo $notice; ?>">
                                <i class="far fa-eye"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($candidate_phone) : ?>
                <div class="info">
                    <p class="title-info"><?php esc_html_e('Phone', 'civi-framework'); ?></p>
                    <p class="details-info">
                        <?php if (!in_array("phone", $hide_info_candidate_fields)) : ?>
                            <a href="tel:<?php esc_attr_e($candidate_phone); ?>"><?php esc_attr_e($candidate_phone); ?></a>
                        <?php else: ?>
                            ************
                            <a class="btn-add-to-message" href="#"
                                data-text="<?php echo $notice; ?>">
                                <i class="far fa-eye"></i>
                            </a>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
            <?php if ($candidate_email) : ?>
                <div class="info">
                    <p class="title-info"><?php esc_html_e('Email', 'civi-framework'); ?></p>
                    <p class="details-info email">
                        <?php if (!in_array("phone", $hide_info_candidate_fields)) : ?>
                            <a href="mailto:<?php esc_attr_e($candidate_email) ?>"><?php esc_attr_e($candidate_email); ?></a>
                        <?php else: ?>
                            *************
                            <a class="btn-add-to-message" href="#"
                                data-text="<?php echo $notice; ?>">
                                <i class="far fa-eye"></i>
                            </a>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
            <ul class="list-social">
                <?php
                $social_networks = [
                    'facebook'  => ['url' => $candidate_facebook, 'enabled' => $enable_social_facebook, 'icon' => '<i class="fab fa-facebook-f"></i>'],
                    'twitter'   => ['url' => $candidate_twitter, 'enabled' => $enable_social_twitter, 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currrentColor">
										<path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z" />
									</svg>'],
                    'linkedin'  => ['url' => $candidate_linkedin, 'enabled' => $enable_social_linkedin, 'icon' => '<i class="fab fa-linkedin"></i>'],
                    'instagram' => ['url' => $candidate_instagram, 'enabled' => $enable_social_instagram, 'icon' => '<i class="fab fa-instagram"></i>'],
                ];

                foreach ($social_networks as $key => $social) :
                    if ($social['enabled'] == 1 && !empty($social['url'])) :
                        if (!in_array("social", $hide_info_candidate_fields)) : ?>
                            <li>
                                <a href="<?php echo esc_url($social['url']); ?>">
                                    <?php
                                    if ($key === 'twitter') {
                                        // Hiển thị SVG cho Twitter
                                        echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currrentColor">
										<path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z" />
									</svg>';
                                    } else {
                                        echo $social['icon'];
                                    }
                                    ?>
                                </a>
                            </li>
                        <?php else : ?>
                            <li>
                                <a class="btn-add-to-message" href="#" data-text="<?php echo esc_attr($notice); ?>">
                                    <?php
                                    if ($key === 'twitter') {
                                        echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currrentColor">
										<path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z" />
									</svg>';
                                    } else {
                                        echo $social['icon'];
                                    }
                                    ?>
                                </a>
                            </li>
                            <?php endif;
                    endif;
                endforeach;
                $civi_social_fields = civi_get_option('civi_social_fields');
                if (is_array($civi_social_fields) && !empty($civi_social_fields)) {
                    foreach ($civi_social_fields as $key => $value) {
                        $candidate_social_val = get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_' . $value['social_name'], true);
                        if (!empty($candidate_social_val)) :
                            if (!in_array("social", $hide_info_candidate_fields)) : ?>
                                <li><a href="<?php echo esc_url($candidate_social_val); ?>"><?php echo $value['social_icon']; ?></a></li>
                            <?php else : ?>
                                <li><a class="btn-add-to-message" href="#" data-text="<?php echo esc_attr($notice); ?>"><?php echo $value['social_icon']; ?></a></li>
                <?php endif;
                        endif;
                    }
                }
                ?>
            </ul>
            <?php
            do_action('civi/candidate/single/sidebar/info/after');
            ?>
        </div>
    <?php endif; ?>
<?php } ?>
<?php if ($check_package_employer == 1 || $check_package_employer == 2 || $user_id == $post_author_id) : ?>
    <div class="candidate-sidebar block-archive-sidebar candidate-sidebar <?php echo implode(" ", $classes); ?>">
        <h3 class="title-candidate"><?php esc_html_e('Information', 'civi-framework'); ?></h3>
        <?php if (!empty($candidate_salary)) : ?>
            <div class="info">
                <p class="title-info"><?php esc_html_e('Offered Salary', 'civi-framework'); ?></p>
                <div class="details-info salary">
                    <?php civi_get_salary_candidate($candidate_id); ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if (is_array($candidate_yoe)) : ?>
            <div class="info">
                <p class="title-info"><?php esc_html_e('Experience time', 'civi-framework'); ?></p>
                <div class="list-cate">
                    <?php foreach ($candidate_yoe as $yoe) {
                        $yoe_link = get_term_link($yoe, 'candidate_yoe'); ?>
                        <a href="<?php echo esc_url($yoe_link); ?>">
                            <?php esc_attr_e($yoe->name); ?>
                        </a>
                    <?php } ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if (is_array($candidate_languages)) : ?>
            <div class="info">
                <p class="title-info"><?php esc_html_e('Languages', 'civi-framework'); ?></p>
                <div class="list-cate">
                    <?php foreach ($candidate_languages as $language) {
                        echo '<span>' . esc_attr($language->name) . '</span>';
                    } ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if (!empty($candidate_gender)) : ?>
            <div class="info">
                <p class="title-info"><?php esc_html_e('Gender', 'civi-framework'); ?></p>
                <div class="list-cate">
                    <?php foreach ($candidate_gender as $gender) {
                        echo esc_attr_e($gender->name);
                    } ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if (is_array($candidate_qualification)) : ?>
            <div class="info">
                <p class="title-info"><?php esc_html_e('Qualification', 'civi-framework'); ?></p>
                <div class="list-cate">
                    <?php foreach ($candidate_qualification as $qualification) {
                        echo '<span>' . esc_attr($qualification->name) . '</span>';
                    } ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if (is_array($candidate_ages)) : ?>
            <div class="info">
                <p class="title-info"><?php esc_html_e('Age', 'civi-framework'); ?></p>
                <div class="list-cate">
                    <?php foreach ($candidate_ages as $ages) {
                        echo esc_attr_e($ages->name);
                    } ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($candidate_phone) : ?>
            <div class="info">
                <p class="title-info"><?php esc_html_e('Phone', 'civi-framework'); ?></p>
                <p class="details-info"><a href="tel:<?php esc_attr_e($candidate_phone); ?>"><?php esc_attr_e($candidate_phone); ?></a></p>
            </div>
        <?php endif; ?>
        <?php if ($candidate_email) : ?>
            <div class="info">
                <p class="title-info"><?php esc_html_e('Email', 'civi-framework'); ?></p>
                <p class="details-info email"><a href="mailto:<?php esc_attr_e($candidate_email) ?>"><?php esc_attr_e($candidate_email); ?></a></p>
            </div>
        <?php endif; ?>
        <ul class="list-social">
            <?php if (!empty($candidate_facebook) && $enable_social_facebook == 1) : ?>
                <li><a href="<?php echo $candidate_facebook; ?>"><i class="fab fa-facebook-f"></i></a></li>
            <?php endif; ?>
            <?php if (!empty($candidate_twitter) && $enable_social_twitter == 1) : ?>
                <li><a href="<?php echo $candidate_twitter; ?>">
                        <!-- fab fa-twitter -->
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currrentColor">
                            <path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z" />
                        </svg>
                    </a></li>
            <?php endif; ?>
            <?php if (!empty($candidate_linkedin) && $enable_social_linkedin == 1) : ?>
                <li><a href="<?php echo $candidate_linkedin; ?>"><i class="fab fa-linkedin"></i></a></li>
            <?php endif; ?>
            <?php if (!empty($candidate_instagram) && $enable_social_instagram == 1) : ?>
                <li><a href="<?php echo $candidate_instagram; ?>"><i class="fab fa-instagram"></i></a></li>
            <?php endif; ?>
            <?php $civi_social_fields = civi_get_option('civi_social_fields');
            if (is_array($civi_social_fields) && !empty($civi_social_fields)) {
                foreach ($civi_social_fields as $key => $value) {
                    $candidate_social_val = get_post_meta($candidate_id, CIVI_METABOX_PREFIX . 'candidate_' . $value['social_name'], true);
                    if (!empty($candidate_social_val)) { ?>
                        <li><a href="<?php echo $candidate_social_val; ?>"><?php echo $value['social_icon']; ?></a></li>
            <?php }
                }
            } ?>
            <?php civi_get_social_network($candidate_id, 'candidate'); ?>
        </ul>
        <?php
        do_action('civi/candidate/single/sidebar/info/after');
        ?>
    </div>
<?php endif; ?>
