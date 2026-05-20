<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>
<div class="row">
    <div class="form-group col-md-6">
        <label><?php esc_html_e('Select company', 'jobportal-framework') ?></label>
            <div class="select2-field">
				<select name="jobs_select_company" class="jobportal-select2">
					<?php jobportal_select_post_company(true); ?>
				</select>
			</div>
        <a href="<?php echo get_permalink(jobportal_get_option('jobportal_submit_company_page_id')); ?>" class="jobportal-button button-link" target="_blank"company><i class="fal fa-angle-down"></i><?php esc_html_e('Create new company', 'jobportal-framework'); ?></a>
    </div>
</div>
