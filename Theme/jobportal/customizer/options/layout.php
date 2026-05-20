<?php

$section = 'layout';

$default = jobportal_get_default_theme_options();

// Layout
JobPortal_Kirki::add_section($section, array(
	'title'    => esc_html__('Layout', 'jobportal'),
	'priority' => 40,
));

JobPortal_Kirki::add_field('theme', [
	'type'      => 'radio-image',
	'settings'  => 'layout_content',
	'label'     => esc_html__('Layout Content', 'jobportal'),
	'section'   => $section,
	'transport' => 'postMessage',
	'default'   => $default['layout_content'],
	'choices'   => [
		'container' => get_template_directory_uri() . '/customizer/assets/images/boxed.png',
		'fullwidth' => get_template_directory_uri() . '/customizer/assets/images/full-width.png',
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'slider',
	'settings'  => 'content_width',
	'label'     => esc_html__('Content Width', 'jobportal'),
	'section'   => $section,
	'transport' => 'postMessage',
	'default'   => $default['content_width'],
	'choices'   => [
		'min'  => 992,
		'max'  => 1920,
		'step' => 1,
	],
	'active_callback' => [
		[
			'setting'  => 'layout_content',
			'operator' => '==',
			'value'    => 'fullwidth',
		]
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'radio-image',
	'settings'  => 'layout_sidebar',
	'label'     => esc_html__('Layout Sidebar', 'jobportal'),
	'section'   => $section,
	'transport' => 'postMessage',
	'default'   => $default['layout_sidebar'],
	'choices'   => [
		'left-sidebar'  => get_template_directory_uri() . '/customizer/assets/images/left-sidebar.png',
		'no-sidebar' 	=> get_template_directory_uri() . '/customizer/assets/images/no-sidebar.png',
		'right-sidebar' => get_template_directory_uri() . '/customizer/assets/images/right-sidebar.png',
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'slider',
	'settings'  => 'sidebar_width',
	'label'     => esc_html__('Sidebar Width', 'jobportal'),
	'section'   => $section,
	'transport' => 'postMessage',
	'default'   => $default['sidebar_width'],
	'choices'   => [
		'min'  => 270,
		'max'  => 420,
		'step' => 1,
	],
	'active_callback' => [
		[
			'setting'  => 'layout_sidebar',
			'operator' => '!=',
			'value'    => 'no-sidebar',
		]
	],
]);
