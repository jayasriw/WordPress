<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$layout = array('general', 'location', 'thumbnail', 'gallery', 'video');

foreach ($layout as $value) {
    switch ($value) {
        case 'general':
            $name = esc_html__('Basic info', 'jobportal-framework');
            break;
        case 'location':
            $name = esc_html__('Location', 'jobportal-framework');
            break;
        case 'thumbnail':
            $name = esc_html__('Cover image', 'jobportal-framework');
            break;
        case 'gallery':
            $name = esc_html__('Gallery', 'jobportal-framework');
            break;
        case 'video':
            $name = esc_html__('Video', 'jobportal-framework');
            break;
    } ?>
    <div class="block-from" id="<?php echo 'service-submit-' . esc_attr($value); ?>">
        <h6><?php echo $name ?></h6>
        <?php jobportal_get_template('service/edit/overview/' . $value . '.php'); ?>
    </div>
<?php } ?>