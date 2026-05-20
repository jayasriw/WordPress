<?php

$section = 'header';

$default = jobportal_get_default_theme_options();

// Header
JobPortal_Kirki::add_section($section, array(
	'title'    => esc_html__('Header', 'jobportal'),
	'priority' => 50,
));

JobPortal_Kirki::add_field('theme', [
	'type'     => 'notice',
	'settings' => 'header_customize',
	'label'    => esc_html__('Header Customize', 'jobportal'),
	'section'  => $section,
]);

JobPortal_Kirki::add_field('theme', [
	'type'     => 'select',
	'settings' => 'header_type',
	'label'    => esc_html__('Header Type', 'jobportal'),
	'section'  => $section,
	'default'  => $default['header_type'],
	'choices'  => jobportal_get_header_elementor(),
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'color',
	'settings'  => 'header_background',
	'label'     => esc_html__('Background Color', 'jobportal'),
	'section'   => $section,
	'transport' => 'postMessage',
	'default'   => $default['header_background'],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'color',
	'settings'  => 'header_sticky_background',
	'label'     => esc_html__('Background Color Header Sticky', 'jobportal'),
	'section'   => $section,
	'transport' => 'postMessage',
	'default'   => $default['header_sticky_background'],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'toggle',
	'settings'  => 'top_bar_enable',
	'label'     => esc_html__('Enable Top Bar', 'jobportal'),
	'section'   => $section,
	'transport' => 'postMessage',
	'default'   => $default['top_bar_enable'],
]);


JobPortal_Kirki::add_field('theme', [
	'type'      => 'toggle',
	'settings'  => 'sticky_header',
	'label'     => esc_html__('Enable Sticky', 'jobportal'),
	'section'   => $section,
	'transport' => 'postMessage',
	'default'   => $default['sticky_header'],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'toggle',
	'settings'  => 'float_header',
	'label'     => esc_html__('Enable Float', 'jobportal'),
	'section'   => $section,
	'transport' => 'postMessage',
	'default'   => $default['float_header'],
]);


JobPortal_Kirki::add_field('theme', [
	'type'      => 'toggle',
	'settings'  => 'show_canvas_menu',
	'label'     => esc_html__('Show Canvas Menu', 'jobportal'),
	'section'   => $section,
	'default'   => $default['show_canvas_menu'],
	'active_callback' => [
		[
			'setting'  => 'header_type',
			'operator' => '==',
			'value'    => '',
		]
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'toggle',
	'settings'  => 'show_main_menu',
	'label'     => esc_html__('Show Main Menu', 'jobportal'),
	'section'   => $section,
	'default'   => $default['show_main_menu'],
	'active_callback' => [
		[
			'setting'  => 'header_type',
			'operator' => '==',
			'value'    => '',
		]
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'toggle',
	'settings'  => 'show_login',
	'label'     => esc_html__('Show Login', 'jobportal'),
	'section'   => $section,
	'default'   => $default['show_login'],
	'active_callback' => [
		[
			'setting'  => 'header_type',
			'operator' => '==',
			'value'    => '',
		]
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'toggle',
	'settings'  => 'show_icon_noti',
	'label'     => esc_html__('Show Notification', 'jobportal'),
	'section'   => $section,
	'default'   => $default['show_icon_noti'],
	'active_callback' => [
		[
			'setting'  => 'header_type',
			'operator' => '==',
			'value'    => '',
		]
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'toggle',
	'settings'  => 'show_search_icon',
	'label'     => esc_html__('Show Search (Mobile)', 'jobportal'),
	'section'   => $section,
	'default'   => $default['show_search_icon'],
	'active_callback' => [
		[
			'setting'  => 'header_type',
			'operator' => '==',
			'value'    => '',
		]
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'toggle',
	'settings'  => 'show_add_jobs_button',
	'label'     => esc_html__('Show Add Jobs/Update Profile', 'jobportal'),
	'section'   => $section,
	'default'   => $default['show_add_jobs_button'],
	'active_callback' => [
		[
			'setting'  => 'header_type',
			'operator' => '==',
			'value'    => '',
		]
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'slider',
	'settings'  => 'logo_width',
	'label'     => esc_html__('Logo Width', 'jobportal'),
	'section'   => $section,
	'transport' => 'postMessage',
	'default'   => $default['logo_width'],
	'choices'   => [
		'min'  => 0,
		'max'  => 500,
		'step' => 1,
	],
	'active_callback' => [
		[
			'setting'  => 'header_type',
			'operator' => '==',
			'value'    => '',
		]
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'slider',
	'settings'  => 'header_padding_top',
	'label'     => esc_html__('Padding Top', 'jobportal'),
	'section'   => $section,
	'transport' => 'postMessage',
	'default'   => $default['header_padding_top'],
	'choices'   => [
		'min'  => 0,
		'max'  => 200,
		'step' => 1,
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'slider',
	'settings'  => 'header_padding_bottom',
	'label'     => esc_html__('Padding Bottom', 'jobportal'),
	'section'   => $section,
	'transport' => 'postMessage',
	'default'   => $default['header_padding_bottom'],
	'choices'   => [
		'min'  => 0,
		'max'  => 500,
		'step' => 1,
	],
]);
