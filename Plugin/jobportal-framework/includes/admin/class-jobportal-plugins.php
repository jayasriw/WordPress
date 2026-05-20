<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('JobPortal_Plugins')) {

    class JobPortal_Plugins
    {

        private static $instance = null;

        /**
         * Instance
         *
         * Ensures only one instance of the class is loaded or can be loaded.
         *
         */
        public static function instance()
        {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public static function get_plugin_action($plugin)
        {
            $tgmpa_instance             = TGM_Plugin_Activation::$instance;
            $installed_plugins          = get_plugins();
            $actions                    = '';
            $plugin['sanitized_plugin'] = $plugin['name'];

            // Plugin in wordpress.org.
            if (!$plugin['version']) {
                $plugin['version'] = $tgmpa_instance->does_plugin_have_update($plugin['slug']);
            }

            if (!isset($installed_plugins[$plugin['file_path']])) {
                // Display Install link.
                $actions = sprintf(
                    __('<a href="%1$s" title="Install %2$s">Install</a>', 'jobportal-framework'),
                    esc_url(
                        wp_nonce_url(
                            add_query_arg(
                                array(
                                    'page'          => rawurlencode(TGM_Plugin_Activation::$instance->menu),
                                    'plugin'        => rawurlencode($plugin['slug']),
                                    'tgmpa-install' => 'install-plugin',
                                ),
                                $tgmpa_instance->get_tgmpa_url()
                            ),
                            'tgmpa-install',
                            'tgmpa-nonce'
                        )
                    ),
                    $plugin['sanitized_plugin']
                );
            } elseif (version_compare($installed_plugins[$plugin['file_path']]['Version'], $plugin['version'], '<')) {
                // Display update link.
                $actions = sprintf(
                    __('<a href="%1$s" title="Update %2$s">Update</a>', 'jobportal-framework'),
                    wp_nonce_url(
                        add_query_arg(
                            array(
                                'page'         => rawurlencode(TGM_Plugin_Activation::$instance->menu),
                                'plugin'       => rawurlencode($plugin['slug']),
                                'tgmpa-update' => 'update-plugin',
                            ),
                            $tgmpa_instance->get_tgmpa_url()
                        ),
                        'tgmpa-update',
                        'tgmpa-nonce'
                    ),
                    $plugin['sanitized_plugin']
                );
            } elseif (is_plugin_inactive($plugin['file_path'])) {
                // Display Active link.
                $actions = sprintf(
                    __('<a href="%1$s" title="Activate %2$s" data-slug="%3$s" data-source="%4$s" data-plugin-action="activate-plugin" data-nonce="%5$s" class="jobportal-plugin-action plugin-activate">Activate</a>', 'jobportal-framework'),
                    '#',
                    $plugin['name'],
                    $plugin['slug'],
                    $plugin['file_path'],
                    wp_create_nonce('process_plugin_actions_nonce')
                );
            } elseif (is_plugin_active($plugin['file_path'])) {
                // Display deactivate link.
                $actions = sprintf(
                    __('<a href="%1$s" title="Deactivate %2$s" data-slug="%3$s" data-source="%4$s" data-plugin-action="deactivate-plugin" data-nonce="%5$s" class="jobportal-plugin-action plugin-deactivate">Deactivate</a>', 'jobportal-framework'),
                    '#',
                    $plugin['name'],
                    $plugin['slug'],
                    $plugin['file_path'],
                    wp_create_nonce('process_plugin_actions_nonce')
                );
            }

            return $actions;
        }

        /**
         * Install, Update, Activate, Deactivate plugin
         */
        public function process_plugin_actions()
        {
            if (! current_user_can('activate_plugins')) {
                wp_send_json_error(esc_html__('Permission denied.', 'jobportal-framework'), 403);
            }

            if (empty($_POST['_wpnonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'process_plugin_actions_nonce')) {
                wp_send_json_error(esc_html__('Invalid nonce.', 'jobportal-framework'), 400);
            }

            $slug          = '';
            $nonce         = '';
            $source        = '';
            $plugin_action = '';

            if (!class_exists('TGM_Plugin_Activation')) {
                wp_send_json_error(esc_html__('TGM_Plugin_Activation does not exist', 'jobportal-framework'));
            }

            // Get action (install, update, activate or deactivate).
            if (isset($_POST['plugin_action'])) {
                $plugin_action = sanitize_text_field(wp_unslash($_POST['plugin_action']));
            }

            // Get plugin slug.
            if (isset($_POST['slug'])) {
                $slug = sanitize_text_field(wp_unslash($_POST['slug']));
            }

            // Get plugin source.
            if (isset($_POST['source'])) {
                $source = sanitize_text_field(wp_unslash($_POST['source']));
            }

            if (empty($source)) {
                wp_send_json_error(esc_html__('Installation package not available.', 'jobportal-framework'));
            }

            if (!class_exists('Plugin_Upgrader', false)) {
                require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            }
            wp_cache_flush();

            // Create a new instance of Plugin_Upgrader.
            $upgrader = new Plugin_Upgrader();

            if (in_array($plugin_action, ['activate-plugin', 'deactivate-plugin'], true)) {
                if ('activate-plugin' === $plugin_action) {
                    $result = activate_plugin($source);
                    if (is_wp_error($result)) {
                        wp_send_json_error($result->get_error_message(), 500);
                    }
                    $nonce = wp_create_nonce('process_plugin_actions_nonce');
                    wp_send_json_success(array('nonce' => $nonce, 'message' => __('Plugin activated.', 'jobportal-framework')));
                }

                if ('deactivate-plugin' === $plugin_action) {
                    deactivate_plugins($source);
                    $nonce = wp_create_nonce('process_plugin_actions_nonce');
                    wp_send_json_success(array('nonce' => $nonce, 'message' => __('Plugin deactivated.', 'jobportal-framework')));
                }
            } else {
                wp_send_json_error(__('Invalid plugin action.', 'jobportal-framework'));
            }

            wp_send_json_error(esc_html__('Invalid action.', 'jobportal-framework'), 400);
        }
    }
}
