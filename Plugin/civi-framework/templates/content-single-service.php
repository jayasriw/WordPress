<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$service_meta_data = get_post_custom($id);
global $post;

$id = get_the_ID();

$classes = array('civi-service-wrap', 'single-service-area');

?>
<div id="service-<?php the_ID(); ?>" <?php post_class($classes); ?>>
    <div class="block-service-warrper">
        <?php
        /**
         * Hook: civi_single_service_after_summary hook.
         */
        do_action('civi_single_service_after_summary');

        /**
         * Hook: civi_single_service_summary hook.
         */
        do_action('civi_single_service_summary');

        /**
         * Hook: civi_after_content_single_service_summary hook.
         */
        do_action('civi_after_content_single_service_summary'); ?>
    </div>
</div>