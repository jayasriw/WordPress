<?php
if (!defined('ABSPATH')) {
  exit;
}
if (!class_exists('GLF_Field_Checkbox')) {
  class GLF_Field_Checkbox extends GLF_Field
  {
    function render_content($content_args = '')
    {
      $field_value = $this->get_value();
      $checked = !empty($field_value) ? 'checked="checked"' : '';
      $label   = isset($this->params['label']) ? $this->params['label'] : '';
?>
      <div class="glf-field-checkbox-inner">
        <label>
          <input
            type="checkbox"
            name="<?php echo esc_attr($this->get_name()); ?>"
            value="1"
            <?php echo $checked; ?> />
          <span><?php echo esc_html($label); ?></span>
        </label>
      </div>
<?php
    }

    function get_default()
    {
      $field_default = isset($this->params['default']) ? $this->params['default'] : '';
      return $this->is_clone() ? array($field_default) : $field_default;
    }
  }
}
