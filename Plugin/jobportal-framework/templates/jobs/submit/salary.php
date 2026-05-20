<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $hide_jobs_fields, $current_user;
$user_id = $current_user->ID;
$jobs_user_salary_show = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'jobs_user_salary_show', true);
$jobs_user_salary_minimum = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'jobs_user_salary_minimum', true);
$jobs_user_salary_maximum = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'jobs_user_salary_maximum', true);
$jobs_user_salary_rate = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'jobs_user_salary_rate', true);
$jobs_user_maximum_price = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'jobs_user_maximum_price', true);
$jobs_user_minimum_price = get_user_meta($user_id, JOBPORTAL_METABOX_PREFIX . 'jobs_user_minimum_price', true);
$hide_jobs_salary = jobportal_get_option('hide_jobs_salary_fields');
if(empty($hide_jobs_salary)){
    $hide_jobs_salary = array();
}
?>
<div class="row">
    <div class="form-group col-md-6">
        <label><?php esc_html_e('Show pay by', 'jobportal-framework'); ?></label>
        <div class="select2-field">
			<select id="select-salary-pay" name="jobs_salary_show" class="jobportal-select2">
                <?php if (!in_array('fields_jobs_salary_range', $hide_jobs_salary)) : ?>
				<option <?php if ($jobs_user_salary_show == "range" || $jobs_user_salary_show == "") {
					echo 'selected';
				} ?> value="range"><?php esc_html_e('Range', 'jobportal-framework'); ?></option>
                <?php endif; ?>

                <?php if (!in_array('fields_jobs_salary_starting', $hide_jobs_salary)) : ?>
                <option <?php if ($jobs_user_salary_show == "starting_amount") {
					echo 'selected';
				} ?> value="starting_amount"><?php esc_html_e('Starting amount', 'jobportal-framework'); ?></option>
                <?php endif; ?>

                <?php if (!in_array('fields_jobs_salary_maximum', $hide_jobs_salary)) : ?>
                <option <?php if ($jobs_user_salary_show == "maximum_amount") {
					echo 'selected';
				} ?> value="maximum_amount"><?php esc_html_e('Maximum amount', 'jobportal-framework'); ?></option>
                <?php endif; ?>

                <?php if (!in_array('fields_jobs_salary_negotiable', $hide_jobs_salary)) : ?>
                <option <?php if ($jobs_user_salary_show == "agree") {
					echo 'selected';
				} ?> value="agree"><?php esc_html_e('Negotiable Price', 'jobportal-framework'); ?></option>
                <?php endif; ?>
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

    <?php if (!in_array('fields_jobs_salary_range', $hide_jobs_salary)) : ?>
    <div class="jobportal-section-salary-select" id="range">
        <div class="form-group col-md-6">
            <label for="jobs_salary_minimum"><?php esc_html_e('Minimum', 'jobportal-framework'); ?></label>
            <input type="number" id="jobs_salary_minimum" name="jobs_salary_minimum" pattern="[-+]?[0-9]"
                   value="<?php echo $jobs_user_salary_minimum ?>">
        </div>
        <div class="form-group col-md-6">
            <label for="jobs_salary_maximum"><?php esc_html_e('Maximum', 'jobportal-framework'); ?></label>
            <input type="number" id="jobs_salary_maximum" name="jobs_salary_maximum" pattern="[-+]?[0-9]"
                   value="<?php echo $jobs_user_salary_maximum ?>">
        </div>
    </div>
    <?php endif; ?>

    <?php if (!in_array('fields_jobs_salary_starting', $hide_jobs_salary)) : ?>
    <div class="jobportal-section-salary-select col-md-6" id="starting_amount">
        <label for="jobs_minimum_price"><?php esc_html_e('Minimum', 'jobportal-framework'); ?></label>
        <input type="text" id="jobs_minimum_price" name="jobs_minimum_price" pattern="[-+]?[0-9]"
               value="<?php echo $jobs_user_minimum_price ?>">
    </div>
    <?php endif; ?>

    <?php if (!in_array('fields_jobs_salary_maximum', $hide_jobs_salary)) : ?>
    <div class="jobportal-section-salary-select col-md-6" id="maximum_amount">
        <label for="jobs_maximum_price"><?php esc_html_e('Maximum', 'jobportal-framework'); ?></label>
        <input type="text" id="jobs_maximum_price" name="jobs_maximum_price" pattern="[-+]?[0-9]"
               value="<?php echo $jobs_user_maximum_price ?>">
    </div>
    <?php endif; ?>

    <div class="form-group col-md-6" id="jobs_rate">
        <label><?php esc_html_e('Rate', 'jobportal-framework'); ?></label>
        <div class="select2-field">
			<select name="jobs_salary_rate" class="jobportal-select2">
				<option <?php if ($jobs_user_salary_rate == "hour") {
					echo 'selected';
				} ?> value="hour"><?php esc_html_e('Per Hour', 'jobportal-framework'); ?></option>
				<option <?php if ($jobs_user_salary_rate == "day") {
					echo 'selected';
				} ?> value="day"><?php esc_html_e('Per Day', 'jobportal-framework'); ?></option>
				<option <?php if ($jobs_user_salary_rate == "week") {
					echo 'selected';
				} ?> value="week"><?php esc_html_e('Per Week', 'jobportal-framework'); ?></option>
				<option <?php if ($jobs_user_salary_rate == "month") {
					echo 'selected';
				} ?> value="month"><?php esc_html_e('Per Month', 'jobportal-framework'); ?></option>
				<option <?php if ($jobs_user_salary_rate == "year") {
					echo 'selected';
				} ?> value="year"><?php esc_html_e('Per Year', 'jobportal-framework'); ?></option>
			</select>
		</div>
    </div>
</div>
