<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'login-to-view');
$company_meta_data = get_post_custom($id);
global $post;

$id = get_the_ID();

$classes = array('jobportal-company-wrap', 'single-company-area');

?>
<div id="company-<?php the_ID(); ?>" <?php post_class($classes); ?>>
    <div class="block-company-warrper">
        <div class="block-archive-top">
            <?php
            /**
             * Hook: jobportal_single_company_after_summary hook.
             */
            do_action('jobportal_single_company_after_summary'); ?>
            <?php
            /**
             * Hook: jobportal_single_company_summary hook.
             */
            do_action('jobportal_single_company_summary');
            ?>
        </div>
        <?php
        /**
         * Hook: jobportal_after_content_single_company_summary hook.
         */
        do_action('jobportal_after_content_single_company_summary'); ?>
    </div>
</div>
