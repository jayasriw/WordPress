<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$enable_time_format = civi_get_option('enable_24_time_format');
?>
<div class="form-popup civi-form-meetings" id="civi-form-reschedule-meeting">
    <div class="bg-overlay"></div>
    <form class="meetings-popup custom-scrollbar">
        <a href="#" class="btn-close"><i class="far fa-times"></i></a>
        <h5><?php esc_html_e('Create Meeting', 'civi-framework'); ?></h5>
        <div class="row">
            <div class="form-group col-md-12">
                <label for="type_meetings"><?php esc_html_e('Type', 'civi-framework'); ?></label>
                <div class="select2-field">
					<select name="type_meetings" id="type_meetings" class="civi-select2">
						<option value=""><?php esc_html_e('Zoom', 'civi-framework'); ?></option>
					</select>
				</div>
            </div>
            <div class="form-group col-md-12">
                <label for="date_meetings"><?php esc_html_e('Date', 'civi-framework'); ?></label>
                <input type="date" id="date_meetings" name="date_meetings" />
            </div>
            <div class="form-group col-md-12 form-time-meetings">
                <label for="time_meetings"><?php esc_html_e('Time', 'civi-framework'); ?></label>
                <div class="select2-field">
					<select name="time_meetings" id="time_meetings" class="civi-select2">
						<?php foreach (range(0, 86399, 900) as $time) {
							$value_time = gmdate('H:i', $time);
							if($enable_time_format == 1){ ?>
							    <option value="<?php echo esc_attr($value_time) ?>"><?php echo $value_time ?></option>
                            <?php } else { ?>
                                <option value="<?php echo esc_attr($value_time) ?>"><?php esc_html_e(gmdate(get_option('time_format'), $time)) ?></option>
                            <?php } ?>
						<?php } ?>
					</select>
				</div>
            </div>
            <div class="form-group col-md-12">
                <label for="timeduration_meetings"><?php esc_html_e('Time Duration', 'civi-framework'); ?></label>
                <input type="number" value="" id="timeduration_meetings" name="timeduration_meetings" placeholder="<?php echo esc_attr_e('Minute', 'civi-framework'); ?>" />
            </div>
            <div class="form-group col-md-12">
                <label for="message_meetings"><?php esc_html_e('Note', 'civi-framework'); ?></label>
                <textarea rows="4" cols="50" id="message_meetings" name="message_meetings" placeholder="<?php echo esc_attr_e('Note', 'civi-framework'); ?>"></textarea>
            </div>
        </div>
        <div class="message_error"></div>
        <div class="button-wrapper">
            <a href="#" class="civi-button button-outline button-block button-cancel"><?php esc_html_e('Cancel', 'civi-framework'); ?></a>
            <button class="civi-button button-block" id="btn-meetings-reschedule" type="submit">
                <?php esc_html_e('Send Meeting', 'civi-framework'); ?>
                <span class="btn-loading"><i class="fal fa-spinner fa-spin large"></i></span>
            </button>
        </div>
    </form>
</div>
