<?php

namespace JobPortal_Elementor;

defined('ABSPATH') || exit;

class WPML_Translatable_Nodes
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
		add_action('init', [$this, 'wp_init']);
	}

	public function wp_init()
	{
		add_filter('wpml_elementor_widgets_to_translate', [$this, 'wpml_widgets_to_translate_filter']);
	}

	public function get_translatable_node()
	{
		require_once JOBPORTAL_ELEMENTOR_DIR . '/wpml/class-translate-widget-google-map.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/wpml/class-translate-widget-list.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/wpml/class-translate-widget-attribute-list.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/wpml/class-translate-widget-pricing-table.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/wpml/class-translate-widget-table.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/wpml/class-translate-widget-modern-carousel.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/wpml/class-translate-widget-modern-slider.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/wpml/class-translate-widget-team-member-carousel.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/wpml/class-translate-widget-testimonial-carousel.php';

		$widgets["jobportal-attribute-list'] = [
			'fields'            => [],
			'integration-class' => '\JobPortal_Elementor\Translate_Widget_Attribute_List',
		];

		$widgets["jobportal-heading'] = [
			'fields' => [
				[
					'field'       => 'title',
					'type'        => esc_html__('Modern Heading: Primary', 'jobportal'),
					'editor_type' => 'AREA',
				],
				'title_link' => [
					'field'       => 'url',
					'type'        => esc_html__('Modern Heading: Link', 'jobportal'),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'description',
					'type'        => esc_html__('Modern Heading: Description', 'jobportal'),
					'editor_type' => 'AREA',
				],
				[
					'field'       => 'sub_title_text',
					'type'        => esc_html__('Modern Heading: Secondary', 'jobportal'),
					'editor_type' => 'AREA',
				],
			],
		];

		$widgets["jobportal-button'] = [
			'fields' => [
				[
					'field'       => 'text',
					'type'        => esc_html__('Button: Text', 'jobportal'),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'badge_text',
					'type'        => esc_html__('Button: Badge', 'jobportal'),
					'editor_type' => 'LINE',
				],
				'link' => [
					'field'       => 'url',
					'type'        => esc_html__('Button: Link', 'jobportal'),
					'editor_type' => 'LINK',
				],
			],
		];

		$widgets["jobportal-banner'] = [
			'fields' => [
				[
					'field'       => 'title_text',
					'type'        => esc_html__('Banner: Title', 'jobportal'),
					'editor_type' => 'LINE',
				],
				'link' => [
					'field'       => 'url',
					'type'        => esc_html__('Banner: Link', 'jobportal'),
					'editor_type' => 'LINK',
				],
			],
		];

		$widgets["jobportal-circle-progress-chart'] = [
			'fields' => [
				[
					'field'       => 'inner_content_text',
					'type'        => esc_html__('Circle Chart: Text', 'jobportal'),
					'editor_type' => 'LINE',
				],
			],
		];

		$widgets["jobportal-flip-box'] = [
			'fields' => [
				[
					'field'       => 'title_text_a',
					'type'        => esc_html__('Flip Box: Front Title', 'jobportal'),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'description_text_a',
					'type'        => esc_html__('Flip Box: Front Description', 'jobportal'),
					'editor_type' => 'AREA',
				],
				[
					'field'       => 'title_text_b',
					'type'        => esc_html__('Flip Box: Back Title', 'jobportal'),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'description_text_b',
					'type'        => esc_html__('Flip Box: Back Description', 'jobportal'),
					'editor_type' => 'AREA',
				],
				[
					'field'       => 'button_text',
					'type'        => esc_html__('Flip Box: Button Text', 'jobportal'),
					'editor_type' => 'LINE',
				],
				'link' => [
					'field'       => 'url',
					'type'        => esc_html__('Flip Box: Link', 'jobportal'),
					'editor_type' => 'LINK',
				],
			],
		];

		$widgets["jobportal-google-map'] = [
			'fields'            => [],
			'integration-class' => '\JobPortal_Elementor\Translate_Widget_Google_Map',
		];

		$widgets["jobportal-icon'] = [
			'fields' => [
				'link' => [
					'field'       => 'url',
					'type'        => esc_html__('Icon: Link', 'jobportal'),
					'editor_type' => 'LINK',
				],
			],
		];

		$widgets["jobportal-icon-box'] = [
			'fields' => [
				[
					'field'       => 'title_text',
					'type'        => esc_html__('Icon Box: Title', 'jobportal'),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'description_text',
					'type'        => esc_html__('Icon Box: Description', 'jobportal'),
					'editor_type' => 'AREA',
				],
				'link'        => [
					'field'       => 'url',
					'type'        => esc_html__('Icon Box: Link', 'jobportal'),
					'editor_type' => 'LINK',
				],
				[
					'field'       => 'button_text',
					'type'        => esc_html__('Icon Box: Button', 'jobportal'),
					'editor_type' => 'LINE',
				],
				'button_link' => [
					'field'       => 'url',
					'type'        => esc_html__('Icon Box: Button Link', 'jobportal'),
					'editor_type' => 'LINK',
				],
			],
		];

		$widgets["jobportal-image-box'] = [
			'fields' => [
				[
					'field'       => 'title_text',
					'type'        => esc_html__('Image Box: Title', 'jobportal'),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'description_text',
					'type'        => esc_html__('Image Box: Content', 'jobportal'),
					'editor_type' => 'AREA',
				],
				'link' => [
					'field'       => 'url',
					'type'        => esc_html__('Image Box: Link', 'jobportal'),
					'editor_type' => 'LINK',
				],
				[
					'field'       => 'button_text',
					'type'        => esc_html__('Image Box: Button', 'jobportal'),
					'editor_type' => 'LINE',
				],
			],
		];

		$widgets["jobportal-list'] = [
			'fields'            => [],
			'integration-class' => '\JobPortal_Elementor\Translate_Widget_List',
		];

		$widgets["jobportal-popup-video'] = [
			'fields' => [
				[
					'field'       => 'video_text',
					'type'        => esc_html__('Popup Video: Text', 'jobportal'),
					'editor_type' => 'LINE',
				],
				'video_url' => [
					'field'       => 'url',
					'type'        => esc_html__('Popup Video: Link', 'jobportal'),
					'editor_type' => 'LINK',
				],
				[
					'field'       => 'poster_caption',
					'type'        => esc_html__('Popup Video: Caption', 'jobportal'),
					'editor_type' => 'AREA',
				],
			],
		];

		$widgets["jobportal-pricing-table'] = [
			'fields'            => [
				[
					'field'       => 'heading',
					'type'        => esc_html__('Pricing Table: Heading', 'jobportal'),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'sub_heading',
					'type'        => esc_html__('Pricing Table: Description', 'jobportal'),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'currency',
					'type'        => esc_html__('Pricing Table: Currency', 'jobportal'),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'price',
					'type'        => esc_html__('Pricing Table: Price', 'jobportal'),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'period',
					'type'        => esc_html__('Pricing Table: Period', 'jobportal'),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'button_text',
					'type'        => esc_html__('Pricing Table: Button', 'jobportal'),
					'editor_type' => 'LINE',
				],
				'button_link' => [
					'field'       => 'url',
					'type'        => esc_html__('Pricing Table: Button Link', 'jobportal'),
					'editor_type' => 'LINK',
				],
			],
			'integration-class' => '\JobPortal_Elementor\Translate_Widget_Pricing_Table',
		];

		$widgets["jobportal-table'] = [
			'fields'            => [],
			'integration-class' => [
				'\JobPortal_Elementor\Translate_Widget_Pricing_Table_Head',
				'\JobPortal_Elementor\Translate_Widget_Pricing_Table_Body',
			],
		];

		$widgets["jobportal-team-member'] = [
			'fields' => [
				[
					'field'       => 'name',
					'type'        => esc_html__('Team Member: Name', 'jobportal'),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'content',
					'type'        => esc_html__('Team Member: Content', 'jobportal'),
					'editor_type' => 'AREA',
				],
				[
					'field'       => 'position',
					'type'        => esc_html__('Team Member: Position', 'jobportal'),
					'editor_type' => 'LINE',
				],
				'profile' => [
					'field'       => 'url',
					'type'        => esc_html__('Team Member: Profile', 'jobportal'),
					'editor_type' => 'LINK',
				],
			],
		];

		$widgets["jobportal-modern-carousel'] = [
			'fields'            => [],
			'integration-class' => '\JobPortal_Elementor\Translate_Widget_Modern_Carousel',
		];

		$widgets["jobportal-modern-slider'] = [
			'fields'            => [],
			'integration-class' => '\JobPortal_Elementor\Translate_Widget_Modern_Slider',
		];

		$widgets["jobportal-team-member-carousel'] = [
			'fields'            => [],
			'integration-class' => '\JobPortal_Elementor\Translate_Widget_Team_Member_Carousel',
		];

		$widgets["jobportal-testimonial-carousel'] = [
			'fields'            => [],
			'integration-class' => '\JobPortal_Elementor\Translate_Widget_Testimonial_Carousel',
		];

		return $widgets;
	}

	public function wpml_widgets_to_translate_filter($widgets)
	{
		$jobportal_widgets = $this->get_translatable_node();

		foreach ($jobportal_widgets as $widget_name => $widget) {
			$widgets[$widget_name]               = $widget;
			$widgets[$widget_name]['conditions'] = [
				'widgetType' => $widget_name,
			];
		}

		return $widgets;
	}
}

WPML_Translatable_Nodes::instance()->initialize();
