<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $jobs_data, $jobs_meta_data, $hide_jobs_fields;
?>
<div class="row">
    <div class="form-group col-md-6">
        <label><?php esc_html_e('Show pay by', 'jobportal-framework'); ?></label>
        <div class="select2-field">
			<select id="select-salary-pay" name="jobs_salary_show" class="jobportal-select2">
				<option <?php if (isset($jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_salary_show'][0])) {
					if ($jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_salary_show'][0] == "range") {
						echo 'selected';
					}
				} ?> value="range"><?php esc_html_e('Range', 'jobportal-framework'); ?></option>
				<option <?php if (isset($jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_salary_show'][0])) {
					if ($jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_salary_show'][0] == "starting_amount") {
						echo 'selected';
					}
				} ?> value="starting_amount"><?php esc_html_e('Starting amount', 'jobportal-framework'); ?></option>
				<option <?php if (isset($jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_salary_show'][0])) {
					if ($jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_salary_show'][0] == "maximum_amount") {
						echo 'selected';
					}
				} ?> value="maximum_amount"><?php esc_html_e('Maximum amount', 'jobportal-framework'); ?></option>
				<option <?php if (isset($jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_salary_show'][0])) {
					if ($jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_salary_show'][0] == "agree") {
						echo 'selected';
					}
				} ?> value="agree"><?php esc_html_e('Negotiable Price', 'jobportal-framework'); ?></option>
			</select>
		</div>
    </div>
    <div class="form-group col-md-6">
        <label><?php esc_html_e('Currency', 'jobportal-framework'); ?></label>
        <div class="select2-field">
			<select name="jobs_currency_type" class="jobportal-select2">
				<?php jobportal_get_select_currency_type(true); ?>
			</select>
		</div>
    </div>
    <div class="jobportal-section-salary-select" id="range">
        <div class="form-group col-md-6">
            <label for="jobs_salary_minimum"><?php esc_html_e('Minimum', 'jobportal-framework'); ?></label>
            <input type="text" id="jobs_salary_minimum" name="jobs_salary_minimum"
                   value="<?php if (isset($jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_salary_minimum'][0])) {
                       echo $jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_salary_minimum'][0];
                   } ?>">
        </div>
        <div class="form-group col-md-6">
            <label for="jobs_salary_maximum"><?php esc_html_e('Maximum', 'jobportal-framework'); ?></label>
            <input type="text" id="jobs_salary_maximum" name="jobs_salary_maximum"
                   value="<?php if (isset($jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_salary_maximum'][0])) {
                       echo $jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_salary_maximum'][0];
                   } ?>">
        </div>
    </div>
    <div class="jobportal-section-salary-select col-md-6" id="starting_amount">
        <label for="jobs_minimum_price"><?php esc_html_e('Minimum', 'jobportal-framework'); ?></label>
        <input type="text" id="jobs_minimum_price" name="jobs_minimum_price"
               value="<?php if (isset($jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_minimum_price'][0])) {
                   echo $jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_minimum_price'][0];
               } ?>">
    </div>
    <div class="jobportal-section-salary-select col-md-6" id="maximum_amount">
        <label for="jobs_maximum_price"><?php esc_html_e('Maximum', 'jobportal-framework'); ?></label>
        <input type="text" id="jobs_maximum_price" name="jobs_maximum_price"
               value="<?php if (isset($jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_maximum_price'][0])) {
                   echo $jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_maximum_price'][0];
               } ?>">
    </div>
    <div class="form-group col-md-6">
        <label><?php esc_html_e('Rate', 'jobportal-framework'); ?></label>
        <div class="select2-field">
			<select name="jobs_salary_rate" class="jobportal-select2">
				<option value=""><?php esc_html_e('None', 'jobportal-framework'); ?></option>
				<option <?php if (isset($jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_salary_rate'][0])) {
					if ($jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_salary_rate'][0] == "hour") {
						echo 'selected';
					}
				} ?> value="hour"><?php esc_html_e('Per Hour', 'jobportal-framework'); ?></option>
				<option <?php if (isset($jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_salary_rate'][0])) {
					if ($jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_salary_rate'][0] == "day") {
						echo 'selected';
					}
				} ?> value="day"><?php esc_html_e('Per Day', 'jobportal-framework'); ?></option>
				<option <?php if (isset($jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_salary_rate'][0])) {
					if ($jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_salary_rate'][0] == "week") {
						echo 'selected';
					}
				} ?> value="week"><?php esc_html_e('Per Week', 'jobportal-framework'); ?></option>
				<option <?php if (isset($jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_salary_rate'][0])) {
					if ($jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_salary_rate'][0] == "month") {
						echo 'selected';
					}
				} ?> value="month"><?php esc_html_e('Per Month', 'jobportal-framework'); ?></option>
				<option <?php if (isset($jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_salary_rate'][0])) {
					if ($jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_salary_rate'][0] == "year") {
						echo 'selected';
					}
				} ?> value="year"><?php esc_html_e('Per Year', 'jobportal-framework'); ?></option>
			</select>
		</div>
    </div>
    <div class="form-group col-md-6 hidden">
        <label for="jobs_rate_convert_min"><?php esc_html_e('Maximum', 'jobportal-framework'); ?></label>
        <input type="number" id="jobs_rate_convert_min" name="jobs_rate_convert_min"
               value="<?php if (isset($jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_maximum_price'][0])) {
                   echo $jobs_meta_data[JOBPORTAL_METABOX_PREFIX . 'jobs_maximum_price'][0];
               } ?>">
    </div>
</div>
