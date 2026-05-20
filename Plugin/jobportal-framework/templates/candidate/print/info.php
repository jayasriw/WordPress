<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$candidate_details_prints = jobportal_get_option('candidate_details_prints');
foreach ($candidate_details_prints as $print) {
    if (!in_array('enable_print_sp_info', $candidate_details_prints)) {
        return;
    }
}

$candidate_salary          = !empty(get_post_meta($candidate_id, JOBPORTAL_METABOX_PREFIX . 'candidate_offer_salary')) ? get_post_meta($candidate_id, JOBPORTAL_METABOX_PREFIX . 'candidate_offer_salary')[0] : '';
$candidate_yoe             = get_the_terms($candidate_id, 'candidate_yoe');
$candidate_languages       = get_the_terms($candidate_id, 'candidate_languages');
$candidate_location        = get_the_terms($candidate_id, 'candidate_locations');
$candidate_gender          = get_the_terms($candidate_id, 'candidate_gender');
$candidate_qualification   = get_the_terms($candidate_id, 'candidate_qualification');
$candidate_ages            = get_the_terms($candidate_id, 'candidate_ages');
$candidate_phone           = get_post_meta($candidate_id, JOBPORTAL_METABOX_PREFIX . 'candidate_phone', true);
$candidate_email           = get_post_meta($candidate_id, JOBPORTAL_METABOX_PREFIX . 'candidate_email', true);
$candidate_twitter         = get_post_meta($candidate_id, JOBPORTAL_METABOX_PREFIX . 'candidate_twitter', true);
$candidate_facebook        = get_post_meta($candidate_id, JOBPORTAL_METABOX_PREFIX . 'candidate_facebook', true);
$candidate_instagram       = get_post_meta($candidate_id, JOBPORTAL_METABOX_PREFIX . 'candidate_instagram', true);
$candidate_linkedin        = get_post_meta($candidate_id, JOBPORTAL_METABOX_PREFIX . 'candidate_linkedin', true);

$enable_social_twitter     = jobportal_get_option('enable_social_twitter', '1');
$enable_social_linkedin    = jobportal_get_option('enable_social_linkedin', '1');
$enable_social_facebook    = jobportal_get_option('enable_social_facebook', '1');
$enable_social_instagram   = jobportal_get_option('enable_social_instagram', '1');

$option_list_gender = array(
    'both' => esc_html__('Both', 'jobportal-framework'),
    'female' => esc_html__('Female', 'jobportal-framework'),
    'male' => esc_html__('Male', 'jobportal-framework'),
);

$classes = array();
$enable_sticky_sidebar_type = jobportal_get_option('enable_sticky_candidate_sidebar_type', 1);
if ($enable_sticky_sidebar_type) {
    $classes[] = 'has-sticky';
};
?>
<div class="candidate-sidebar block-archive-inner candidate-single-field <?php echo implode(" ", $classes); ?>">
    <h3 class="title-candidate"><?php esc_html_e('Information', 'jobportal-framework'); ?></h3>
    <div class="row">
        <?php if (!empty($candidate_salary)) : ?>
            <div class="info col-4">
                <p class="title-info"><?php esc_html_e('Offered Salary', 'jobportal-framework'); ?></p>
                <div class="details-info salary">
                    <?php jobportal_get_salary_candidate($candidate_id); ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if (is_array($candidate_yoe)) : ?>
            <div class="info col-4">
                <p class="title-info"><?php esc_html_e('Experience time', 'jobportal-framework'); ?></p>
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
            <div class="info col-4">
                <p class="title-info"><?php esc_html_e('Languages', 'jobportal-framework'); ?></p>
                <div class="list-cate">
                    <?php foreach ($candidate_languages as $language) {
                        echo '<span>' . esc_attr($language->name) . '</span>';
                    } ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if (!empty($candidate_gender)) : ?>
            <div class="info col-4">
                <p class="title-info"><?php esc_html_e('Gender', 'jobportal-framework'); ?></p>
                <div class="list-cate">
                    <?php foreach ($candidate_gender as $gender) {
                        echo esc_attr_e($gender->name);
                    } ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if (is_array($candidate_qualification)) : ?>
            <div class="info col-4">
                <p class="title-info"><?php esc_html_e('Qualification', 'jobportal-framework'); ?></p>
                <div class="list-cate">
                    <?php foreach ($candidate_qualification as $qualification) {
                        echo '<span>' . esc_attr($qualification->name) . '</span>';
                    } ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if (is_array($candidate_ages)) : ?>
            <div class="info col-4">
                <p class="title-info"><?php esc_html_e('Age', 'jobportal-framework'); ?></p>
                <div class="list-cate">
                    <?php foreach ($candidate_ages as $ages) {
                        echo esc_attr_e($ages->name);
                    } ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($candidate_phone) : ?>
            <div class="info col-4">
                <p class="title-info"><?php esc_html_e('Phone', 'jobportal-framework'); ?></p>
                <p class="details-info"><a href="tel:<?php esc_attr_e($candidate_phone); ?>"><?php esc_attr_e($candidate_phone); ?></a></p>
            </div>
        <?php endif; ?>
        <?php if ($candidate_email) : ?>
            <div class="info col-4">
                <p class="title-info"><?php esc_html_e('Email', 'jobportal-framework'); ?></p>
                <p class="details-info email"><a href="mailto:<?php esc_attr_e($candidate_email) ?>"><?php esc_attr_e($candidate_email); ?></a></p>
            </div>
        <?php endif; ?>
        <div class="col-4">
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
                <?php $jobportal_social_fields = jobportal_get_option('jobportal_social_fields');
                if (is_array($jobportal_social_fields) && !empty($jobportal_social_fields)) {
                    foreach ($jobportal_social_fields as $key => $value) {
                        $candidate_social_val = get_post_meta($candidate_id, JOBPORTAL_METABOX_PREFIX . 'candidate_' . $value['social_name'], true);
                        if (!empty($candidate_social_val)) { ?>
                            <li><a href="<?php echo $candidate_social_val; ?>"><?php echo $value['social_icon']; ?></a></li>
                <?php }
                    }
                } ?>
                <?php jobportal_get_social_network($candidate_id, 'candidate'); ?>
            </ul>
        </div>
    </div>

</div>
