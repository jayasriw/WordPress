<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
/**
 * @var $isRTL
 * @var $candidate_id
 */
$the_post = get_post( $candidate_id );
if ( $the_post->post_type != 'candidate' ) {
    esc_html_e( 'Posts ineligible to print!', 'jobportal-framework' );
    return;
}

wp_enqueue_script( 'jquery' );
wp_enqueue_script('slick-print', JOBPORTAL_PLUGIN_URL . 'assets/libs/slick/slick.min.js', array('jquery'), '1.8.1', false);
wp_enqueue_script('template-print', JOBPORTAL_PLUGIN_URL . 'assets/js/template.js', array('jquery'), JOBPORTAL_PLUGIN_VER, false);
wp_add_inline_script( 'jquery', 'jQuery(window).load(function(){ print(); });' );

// Actions
remove_action( 'wp_head', '_wp_render_title_tag', 1 );
remove_action( 'wp_head', 'wp_resource_hints', 2 );
remove_action( 'wp_head', 'feed_links', 2 );
remove_action( 'wp_head', 'feed_links_extra', 3 );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );
remove_action( 'publish_future_post', 'check_and_publish_future_post', 10 );
remove_action( 'wp_head', 'noindex', 1 );
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'rel_canonical' );
remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
remove_action( 'wp_head', 'wp_custom_css_cb', 101 );
remove_action( 'wp_head', 'wp_site_icon', 99 );

function enqueue_print_candidate(){
    wp_enqueue_style(JOBPORTAL_PLUGIN_PREFIX . '-style', JOBPORTAL_PLUGIN_URL . 'assets/scss/style.min.css', array(), JOBPORTAL_PLUGIN_VER, 'all');
    wp_enqueue_style('lity-print', JOBPORTAL_PLUGIN_URL . 'assets/libs/lity/css/lity.min.css', array(), JOBPORTAL_PLUGIN_VER, 'all');
}
add_action('wp_enqueue_scripts', 'enqueue_print_candidate');

$head_arr = array('head','additional','about-me','photos');
$candidate_details_prints = jobportal_get_option('candidate_details_prints');
?>
<html <?php language_attributes(); ?>>
<head>
    <?php wp_head(); ?>
</head>
<body>
<div id="candidate-print-wrap">
    <div class="candidate-print-inner">
        <div class="block-candidate-warrper">
            <div class="block-archive-top">
                <?php foreach ( $head_arr as $value ):
                    jobportal_get_template( 'candidate/print/' . $value . '.php', array(
                        'candidate_id' => $candidate_id,
                    ));
                endforeach; ?>
            </div>
            <?php if (!empty($candidate_details_prints)) {
                $candidate_sort_order = explode('|', $candidate_details_prints['sort_order']);
                foreach ($candidate_sort_order as $value) {
                    $v = str_replace('enable_print_sp_', '', $value);
                    jobportal_get_template( 'candidate/print/' . $v . '.php', array(
                        'candidate_id' => $candidate_id,
                    ));
                }
            } ?>
        </div>
    </div>
</div>
</body>
</html>
