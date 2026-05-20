<?php

$section = '404';

$default = jobportal_get_default_theme_options();

// Page 404
JobPortal_Kirki::add_section($section, array(
	'title'    => esc_html__('Page 404', 'jobportal'),
	'priority' => 50,
));

JobPortal_Kirki::add_field('theme', [
	'type'     => 'select',
	'settings' => 'page_404_type',
	'label'    => esc_html__('Page 404', 'jobportal'),
	'section'  => $section,
	'default'  => $default['page_404_type'],
	'choices'  => jobportal_get_elementor_library(),
]);

JobPortal_Kirki::add_field('theme', [
	'type'     => 'text',
	'settings' => 'page_404_title',
	'label'    => esc_html__('Title', 'jobportal'),
	'section'  => $section,
	'default'  => $default['page_404_title'],
	'active_callback' => [
		[
			'setting'  => 'page_404_type',
			'operator' => '==',
			'value'    => '',
		]
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'     => 'text',
	'settings' => 'page_404_des',
	'label'    => esc_html__('Description', 'jobportal'),
	'section'  => $section,
	'default'  => $default['page_404_des'],
	'active_callback' => [
		[
			'setting'  => 'page_404_type',
			'operator' => '==',
			'value'    => '',
		]
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'            => 'image',
	'priority'        => 80,
	'settings'        => 'page_404_image',
	'label'           => esc_html__('Image', 'jobportal'),
	'section'         => $section,
	'default'         => $default['page_404_image'],
	'active_callback' => [
		[
			'setting'  => 'page_404_type',
			'operator' => '==',
			'value'    => '',
		]
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'     => 'text',
	'settings' => 'page_404_btn',
	'label'    => esc_html__('Button', 'jobportal'),
	'section'  => $section,
	'default'  => $default['page_404_btn'],
	'active_callback' => [
		[
			'setting'  => 'page_404_type',
			'operator' => '==',
			'value'    => '',
		]
	],
]);
