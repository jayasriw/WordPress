<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
wp_enqueue_style('lity');
wp_enqueue_script('lity');
$jobs_id = get_the_ID();
$jobs_meta_data = get_post_custom($jobs_id);
$jobs_data = get_post($jobs_id);
$custom_field_jobs = jobportal_render_custom_field('jobs');
$image_src = JOBPORTAL_PLUGIN_URL . 'assets/images/bg-video.webp';
if (count($custom_field_jobs) <= 0) {
    return;
}
function has_any_shortcode($content)
{
    return preg_match('/\[[a-zA-Z0-9_-]+/', $content);
}
?>
<?php foreach ($custom_field_jobs as $key => $field) { ?>
    <?php switch ($field['type']) {
        case 'text':
            if (!empty($jobs_meta_data[$field['id']][0])) { ?>
                <div class="block-archive-inner jobs-additional-text">
                    <div class="additional-wrapper">
                        <h4 class="title-jobs"><?php echo $field['title']; ?></h4>
                        <div class="content">
                            <?php
                            if (has_any_shortcode($jobs_meta_data[$field['id']][0])) {
                                echo do_shortcode(sanitize_text_field($jobs_meta_data[$field['id']][0]));
                            } else {
                                echo sanitize_text_field($jobs_meta_data[$field['id']][0]);
                            }
                            ?>
                        </div>
                    </div>
                </div>
            <?php }
            break;
        case 'url':
            if (!empty($jobs_meta_data[$field['id']])) { ?>
                <div class="block-archive-inner jobs-additional-url">
                    <div class="additional-wrapper">
                        <h4 class="title-jobs"><?php echo $field['title']; ?></h4>
                        <div class="embed-responsive embed-responsive-16by9 embed-responsive-full">
                            <?php echo wp_oembed_get($jobs_meta_data[$field['id']][0], array('wmode' => 'transparent')); ?>
                        </div>
                    </div>
                </div>
            <?php }
            break;
        case 'textarea':
            if (!empty($jobs_meta_data[$field['id']])) { ?>
                <div class="block-archive-inner jobs-additional-textarea">
                    <div class="additional-wrapper">
                        <h4 class="title-jobs"><?php echo $field['title']; ?></h4>
                        <div class="content">
                            <?php
                            if (has_any_shortcode($jobs_meta_data[$field['id']][0])) {
                                echo do_shortcode(sanitize_text_field($jobs_meta_data[$field['id']][0]));
                            } else {
                                echo sanitize_text_field($jobs_meta_data[$field['id']][0]);
                            }
                            ?>
                        </div>
                    </div>
                </div>
            <?php }
            break;
        case 'select':
            if (!empty($jobs_meta_data[$field['id']])) { ?>
                <div class="block-archive-inner jobs-additional-select">
                    <div class="additional-wrapper">
                        <h4 class="title-jobs"><?php echo $field['title']; ?></h4>
                        <div class="content">
                            <?php echo sanitize_text_field($jobs_meta_data[$field['id']][0]); ?>
                        </div>
                    </div>
                </div>
            <?php }
            break;
        case 'checkbox_list':
            $jobs_field = get_post_meta($jobs_data->ID, $field['id'], true);
            if (is_string($jobs_field) && $jobs_field && @unserialize($jobs_field) !== false) {
                $jobs_field = unserialize($jobs_field);
            }
            $jobs_field = (array) $jobs_field;
            $has_selected_options = false;
            if (!empty($jobs_field) && !empty($field['options'])) {
                foreach ($field['options'] as $opt_value) {
                    if (in_array($opt_value, $jobs_field)) {
                        $has_selected_options = true;
                        break;
                    }
                }
            }
            if ($has_selected_options) {
            ?>
                <div class="block-archive-inner jobs-additional-checkbox_list">
                    <div class="additional-wrapper">
                        <h4 class="title-jobs"><?php echo esc_html($field['title']); ?></h4>
                        <div class="content">
                            <?php
                            foreach ($field['options'] as $opt_value) :
                                if (in_array($opt_value, $jobs_field)) : ?>
                                    <div class="label label-skills"><?php echo esc_html($opt_value); ?></div>
                            <?php endif;
                            endforeach;
                            ?>
                        </div>
                    </div>
                </div>
            <?php
            }
            break;
        case 'image':
            $jobs_field = get_post_meta($jobs_data->ID, $field['id'], true);
            if (!empty($jobs_field['url'])) { ?>
                <div class="block-archive-inner jobs-additional-image">
                    <div class="additional-wrapper">
                        <h4 class="title-jobs"><?php echo $field['title']; ?></h4>
                        <img src="<?php echo esc_html($jobs_field['url']); ?>" alt="<?php echo esc_attr($field['title']); ?>" />
                    </div>
                </div>
<?php }
            break;
    }
} ?>
