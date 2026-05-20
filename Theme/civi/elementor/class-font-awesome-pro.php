<?php

namespace Civi_Elementor;

defined('ABSPATH') || exit;

class Font_Awesome_Pro
{

	private static $_instance = null;

	public static function instance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function initialize()
	{
		add_action('elementor/frontend/after_register_styles', function () {
			foreach (['solid', 'regular', 'brands'] as $style) {
				wp_deregister_style('elementor-icons-fa-' . $style);
			}
		}, 20);

		add_filter('elementor/icons_manager/native', [$this, 'replace_font_awesome_pro'], 10);

		add_action('elementor/editor/after_enqueue_scripts', [$this, 'admin_enqueue_scripts'], 11);
		add_action('elementor/frontend/after_enqueue_scripts', [$this, 'admin_enqueue_scripts'], 11);
	}

	public function replace_font_awesome_pro($settings)
	{
		$json_url = CIVI_ELEMENTOR_ASSETS . '/libs/font-awesome-pro/%s.json';
		$version  = '5.10.0-pro';

		// Ensure all required keys are present to prevent "Undefined array key" warnings
		$default_icon_config = [
			'check'         => true,
			'url'           => false,
			'enqueue'       => false,
			'prefix'        => 'fa-',
			'ver'           => $version,
			'native'        => true,
		];

		$icons['fa-regular'] = array_merge($default_icon_config, [
			'name'          => 'fa-regular',
			'label'         => esc_html__('Font Awesome - Regular Pro', 'civi'),
			'displayPrefix' => 'far',
			'labelIcon'     => 'fab fa-font-awesome-alt',
			'fetchJson'     => sprintf($json_url, 'regular'),
		]);

		$icons['fa-solid'] = array_merge($default_icon_config, [
			'name'          => 'fa-solid',
			'label'         => esc_html__('Font Awesome - Solid Pro', 'civi'),
			'displayPrefix' => 'fas',
			'labelIcon'     => 'fab fa-font-awesome',
			'fetchJson'     => sprintf($json_url, 'solid'),
		]);

		$icons['fa-brands'] = array_merge($default_icon_config, [
			'name'          => 'fa-brands',
			'label'         => esc_html__('Font Awesome - Brands Pro', 'civi'),
			'displayPrefix' => 'fab',
			'labelIcon'     => 'fab fa-font-awesome-flag',
			'fetchJson'     => sprintf($json_url, 'brands'),
		]);

		$icons['fa-light'] = array_merge($default_icon_config, [
			'name'          => 'fa-light',
			'label'         => esc_html__('Font Awesome - Light Pro', 'civi'),
			'displayPrefix' => 'fal',
			'labelIcon'     => 'fal fa-flag',
			'fetchJson'     => sprintf($json_url, 'light'),
		]);

		// Ensure all icon families have proper icons arrays with common missing icons
		$common_missing_icons = $this->get_common_missing_icons();

		foreach ($icons as $family => &$family_data) {
			if (!isset($family_data['icons'])) {
				$family_data['icons'] = [];
			}

			// Add missing icons to each family
			foreach ($common_missing_icons as $icon_name => $icon_data) {
				if (!isset($family_data['icons'][$icon_name])) {
					$family_data['icons'][$icon_name] = $icon_data;
				}
			}
		}

		// Remove old from plugin.
		unset(
			$settings['fa-solid'],
			$settings['fa-regular'],
			$settings['fa-brands'],
			$settings['fa-light']
		);

		return array_merge($icons, $settings);
	}

	/**
	 * Get common missing FontAwesome icons
	 */
	private function get_common_missing_icons()
	{
		        return [
            'wallet' => [
                'name' => 'wallet',
                'title' => 'Wallet',
                'categories' => ['payment', 'money'],
                'styles' => ['solid', 'regular']
            ],
            'shield-check' => [
                'name' => 'shield-check',
                'title' => 'Shield Check',
                'categories' => ['security', 'protection'],
                'styles' => ['solid', 'regular']
            ],
            'shield-alt' => [
                'name' => 'shield-alt',
                'title' => 'Shield Alt',
                'categories' => ['security', 'protection'],
                'styles' => ['solid', 'regular']
            ],
            'pen' => [
                'name' => 'pen',
                'title' => 'Pen',
                'categories' => ['writing', 'edit'],
                'styles' => ['solid', 'regular']
            ],
            'user-md-chat' => [
                'name' => 'user-md-chat',
                'title' => 'User Medical Chat',
                'categories' => ['medical', 'communication'],
                'styles' => ['solid', 'regular']
            ],
            'briefcase' => [
                'name' => 'briefcase',
                'title' => 'Briefcase',
                'categories' => ['business', 'work'],
                'styles' => ['solid', 'regular']
            ],
            'credit-card' => [
                'name' => 'credit-card',
                'title' => 'Credit Card',
                'categories' => ['payment', 'money'],
                'styles' => ['solid', 'regular']
            ],
            'money-bill' => [
                'name' => 'money-bill',
                'title' => 'Money Bill',
                'categories' => ['payment', 'money'],
                'styles' => ['solid', 'regular']
            ],
            'coins' => [
                'name' => 'coins',
                'title' => 'Coins',
                'categories' => ['payment', 'money'],
                'styles' => ['solid']
            ],
            'hand-holding-dollar' => [
                'name' => 'hand-holding-dollar',
                'title' => 'Hand Holding Dollar',
                'categories' => ['payment', 'money'],
                'styles' => ['solid']
            ],
            'piggy-bank' => [
                'name' => 'piggy-bank',
                'title' => 'Piggy Bank',
                'categories' => ['payment', 'money'],
                'styles' => ['solid']
            ]
        ];
	}

	function admin_enqueue_scripts()
	{
		wp_enqueue_style(
			'fonts-awesome-all',
			CIVI_THEME_URI . '/assets/fonts/font-awesome/css/fontawesome-all.min.css',
			array(),
			'5.10.0',
			'all'
		);
	}
}

Font_Awesome_Pro::instance()->initialize();
