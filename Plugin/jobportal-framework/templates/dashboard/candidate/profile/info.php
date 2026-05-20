<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly 
}
$hide_candidate_fields = jobportal_get_option('hide_candidate_fields', array());
if (!is_array($hide_candidate_fields)) {
    $hide_candidate_fields = array();
}
$layout_info = array('resume', 'social', 'gallery', 'video');
?>

<div id="tab-info" class="tab-info">
    <?php jobportal_get_template('dashboard/candidate/profile/info/basic.php') ?>
    <?php jobportal_get_template('dashboard/candidate/profile/info/location.php') ?>
    <?php foreach ($layout_info as $value) {
        switch ($value) {
            case 'resume':
                break;
            case 'social':
                break;
            case 'gallery':
                break;
            case 'video':
                break;
        }
        if (!in_array('fields_candidate_' . $value, $hide_candidate_fields)) : ?>
            <?php jobportal_get_template('dashboard/candidate/profile/info/'. $value .'.php') ?>
        <?php endif;
    } ?>
    <?php jobportal_custom_field_candidate('info'); ?>
</div>