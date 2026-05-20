<?php

if ( !defined('ABSPATH') ) {
	exit; // Exit if accessed directly
}

if ( !class_exists('GLF_Field_Number') ) {
	class GLF_Field_Number extends GLF_Field
	{
		function enqueue() {
			// Add any specific styles or scripts for the number field here
		}

		function render_content($content_args = '')
		{
			$field_value = $this->get_value();

			$attr = array();
			$attr[] = 'type="number"';

			if (isset($this->params['min'])) {
				$attr[] = sprintf('min="%s"', esc_attr($this->params['min']));
			}
			if (isset($this->params['max'])) {
				$attr[] = sprintf('max="%s"', esc_attr($this->params['max']));
			}
			if (isset($this->params['step'])) {
				$attr[] = sprintf('step="%s"', esc_attr($this->params['step']));
			}
			if (isset($this->params['placeholder'])) {
				$attr[] = sprintf('placeholder="%s"', esc_attr($this->params['placeholder']));
			}
			if (isset($this->params['panel_title']) && $this->params['panel_title']) {
				$attr[] = 'data-panel-title="true"';
			}

			?>
			<div class="glf-field-number-inner">
				<input
					data-field-control=""
					class="glf-number"
					<?php echo join(' ', $attr); ?>
					name="<?php echo esc_attr($this->get_name()); ?>"
					value="<?php echo esc_attr($field_value); ?>"
				/>
			</div>
			<?php
		}
	}
}
