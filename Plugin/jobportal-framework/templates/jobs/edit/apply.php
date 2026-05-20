<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $jobs_meta_data;
$jobs_select_apply = isset($jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_select_apply']) ? $jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_select_apply'][0] : '';
$jobs_apply_email = isset($jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_apply_email']) ? $jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_apply_email'][0] : '';
$jobs_apply_external = isset($jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_apply_external']) ? $jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_apply_external'][0] : '';
$jobs_apply_call_to = isset($jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_apply_call_to']) ? $jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_apply_call_to'][0] : '';
$hide_jobs_apply = jobportal_get_option('hide_jobs_apply_fields');
if (empty($hide_jobs_apply)) {
    $hide_jobs_apply = array();
}
?>
<div class="row">
    <div class="form-group col-md-6">
        <label><?php esc_html_e('Select type', 'jobportal-framework') ?></label>
        <div class="select2-field">
            <select id="select-apply-type" name="jobs_select_apply" class="jobportal-select2">
                <?php if (!in_array('fields_jobs_apply_email', $hide_jobs_apply)) : ?>
                    <option <?php if ($jobs_select_apply == "email") {
                                echo 'selected';
                            } ?> value="email"><?php esc_html_e('By Email', 'jobportal-framework') ?></option>
                <?php endif; ?>

                <?php if (!in_array('fields_jobs_apply_external', $hide_jobs_apply)) : ?>
                    <option <?php if ($jobs_select_apply == "external") {
                                echo 'selected';
                            } ?> value="external"><?php esc_html_e('External Apply', 'jobportal-framework') ?></option>
                <?php endif; ?>

                <?php if (!in_array('fields_jobs_apply_internal', $hide_jobs_apply)) : ?>
                    <option <?php if ($jobs_select_apply == "internal") {
                                echo 'selected';
                            } ?> value="internal"><?php esc_html_e('Internal Apply', 'jobportal-framework') ?></option>
                <?php endif; ?>

                <?php if (!in_array('fields_jobs_call_to_apply', $hide_jobs_apply)) : ?>
                    <option <?php if ($jobs_select_apply == "call-to") {
                                echo 'selected';
                            } ?> value="call-to"><?php esc_html_e('Call To Apply', 'jobportal-framework') ?></option>
                <?php endif; ?>
            </select>
        </div>
    </div>

    <?php if (!in_array('fields_jobs_apply_email', $hide_jobs_apply)) : ?>
        <div class="jobportal-section-apply-select form-group col-md-6" id="email">
            <label for="jobs_apply_email"><?php esc_html_e('Job apply email', 'jobportal-framework') ?></label>
            <input type="email" id="jobs_apply_email" name="jobs_apply_email"
                value="<?php echo esc_attr($jobs_apply_email) ?>"
                placeholder="<?php esc_attr_e('Enter email', 'jobportal-framework') ?>">
        </div>
    <?php endif; ?>

    <?php if (!in_array('fields_jobs_apply_external', $hide_jobs_apply)) : ?>
        <div class="jobportal-section-apply-select form-group col-md-6" id="external">
            <label for="jobs_apply_external"><?php esc_html_e('Job apply external', 'jobportal-framework') ?></label>
            <input type="url" id="jobs_apply_external" name="jobs_apply_external"
                value="<?php echo esc_attr($jobs_apply_external) ?>"
                placeholder="<?php esc_attr_e('Enter url', 'jobportal-framework') ?>">
        </div>
    <?php endif; ?>

    <?php if (!in_array('fields_jobs_call_to_apply', $hide_jobs_apply)) : ?>
        <div class="jobportal-section-apply-select form-group col-md-6" id="call-to">
            <label for="jobs_apply_call_to"><?php esc_html_e('Call to apply', 'jobportal-framework') ?></label>
            <div class="tel-group">
                <select name="prefix_code" class="jobportal-select2 prefix-code">
                    <?php
                    $prefix_code = phone_prefix_code();
                    $default_phone = JobPortal_Helper::jobportal_get_option('default_phone_number');
                    $selected_prefix = JobPortal_Helper::get_prefix_key_from_phone($jobs_apply_call_to, $prefix_code, $default_phone);
                    foreach ($prefix_code as $key => $value) {
                        $selected = ($key == $selected_prefix) ? 'selected' : '';
                        echo '<option value="' . esc_attr($key) . '" data-dial-code="' . esc_attr($value['code']) . '" ' . $selected . '>' . esc_html($value['name']) . ' (' . esc_html($value['code']) . ')</option>';
                    }
                    ?>
                </select>
                <?php
                $default_phone_code = isset($prefix_code[$selected_prefix]) ? $prefix_code[$selected_prefix]['code'] : (isset($prefix_code[$default_phone]) ? $prefix_code[$default_phone]['code'] : '');
                $input_value = !empty($jobs_apply_call_to) ? preg_replace('/^' . preg_quote($default_phone_code, '/') . '0+/', $default_phone_code, preg_replace('/[^0-9+]/', '', $jobs_apply_call_to)) : $default_phone_code;
                ?>
                <input type="tel" id="jobs_apply_call_to" name="jobs_apply_call_to"
                    data-prefix="<?php echo esc_attr($default_phone_code); ?>"
                    value="<?php echo esc_attr($input_value); ?>"
                    placeholder="<?php esc_attr_e('Enter phone', 'jobportal-framework') ?>"
                    pattern="\+[0-9]{8,12}"
                    required>
            </div>
        </div>
    <?php endif; ?>

</div>
