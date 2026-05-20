<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

wp_enqueue_script(JOBPORTAL_PLUGIN_PREFIX . 'login-to-view');
$candidate_meta_data = get_post_custom($id);

global $post;

$id = get_the_ID();

$classes = array('jobportal-candidate-wrap', 'single-candidate-area');

?>
<div id="candidate-<?php the_ID(); ?>" <?php post_class($classes); ?>>
    <div class="block-candidate-warrper">
        <div class="block-archive-top">
            <?php
            /**
             * Hook: jobportal_single_candidate_after_summary hook.
             */
            do_action('jobportal_single_candidate_after_summary'); ?>
            <?php
            /**
             * Hook: jobportal_single_candidate_summary hook.
             */
            do_action('jobportal_single_candidate_summary');
            ?>
        </div>
        <?php
        /**
         * Hook: jobportal_after_content_single_candidate_summary hook.
         */
        do_action('jobportal_after_content_single_candidate_summary'); ?>
    </div>
</div>