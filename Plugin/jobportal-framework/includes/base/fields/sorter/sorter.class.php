<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if (!class_exists('GLF_Field_Sorter')) {
    class GLF_Field_Sorter extends GLF_Field
    {
        /**
         * Enqueue field resources
         */
        function enqueue()
        {
            wp_enqueue_script(GLF_BASE_RESOURCE_PREFIX . 'sorter', GLF_BASE_URL . 'fields/sorter/assets/sorter.js', array(), GLF_VER, true);
            wp_enqueue_style(GLF_BASE_RESOURCE_PREFIX . 'sorter', GLF_BASE_URL . 'fields/sorter/assets/sorter.min.css', array(), GLF_VER, true);
        }

        function render_content($content_args = '')
        {
            $field_value = $this->get_value();
            if (!is_array($field_value)) {
                $field_value = array();
            }

            $default = $this->get_default();

            $result = array();
            foreach ($default as $group_key => $group_items) {
                $result[$group_key] = array();
            }

            $processed_items = array();

            foreach ($field_value as $group_key => $group_items) {
                if (!isset($result[$group_key]) || !is_array($group_items)) {
                    continue;
                }

                foreach ($group_items as $item_key => $item_value) {
                    if ($item_key === '__no_value__') {
                        continue;
                    }

                    if (!in_array($item_key, $processed_items)) {
                        if ($group_key === 'top' && in_array($item_key, array('jobs-custom-fields', 'jobs-salary'))) {
                            $result['disable'][$item_key] = $item_value;
                        } else {
                            $result[$group_key][$item_key] = $item_value;
                        }
                        $processed_items[] = $item_key;
                    }
                }
            }

            foreach ($default as $group_key => $group_items) {
                foreach ($group_items as $item_key => $item_value) {
                    if ($item_key !== '__no_value__' && !in_array($item_key, $processed_items)) {
                        $result[$group_key][$item_key] = $item_value;
                    }
                }
            }

            $field_value = $result;

?>
            <div class="glf-field-sorter-inner glf-clearfix">
                <?php foreach ($field_value as $group_key => $group): ?>
                    <div class="glf-field-sorter-group" data-group="<?php echo esc_attr($group_key); ?>">
                        <div class="glf-field-sorter-title"><?php esc_html_e($group_key); ?></div>
                        <div class="glf-field-sorter-items">
                            <?php foreach ($group as $item_key => $item_value): ?>
                                <?php if ($item_key === '__no_value__') continue; ?>
                                <div class="glf-field-sorter-item" data-id="<?php echo esc_attr($item_key); ?>">
                                    <input data-field-control="" type="hidden"
                                        name="<?php echo $this->get_input_name(); ?>[<?php echo esc_attr($group_key); ?>][<?php echo esc_attr($item_key); ?>]"
                                        value="<?php echo esc_attr($item_value); ?>" />
                                    <?php esc_html_e($item_value); ?>
                                </div>
                            <?php endforeach; ?>
                            <input data-field-control="" type="hidden"
                                name="<?php echo $this->get_input_name(); ?>[<?php echo esc_attr($group_key); ?>][__no_value__]"
                                value="__no_value__" />
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
<?php
        }

        /**
         * Get default value
         *
         * @return array
         */
        function get_default()
        {
            $default = array(
                'top' => array(),
                'sidebar' => array(
                    'jobs-salary' => esc_html__('Salary', 'jobportal-framework'),
                    'jobs-custom-fields' => esc_html__('Custom Fields', 'jobportal-framework')
                ),
                'enable' => array(),
                'disable' => array()
            );
            $field_default = isset($this->params['default']) ? $this->params['default'] : array();
            if (empty($field_default)) {
                $field_default = $default;
            }

            return $field_default;
        }
    }
}
