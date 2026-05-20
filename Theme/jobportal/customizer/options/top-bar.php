<?php

$section = 'top-bar';

$default = jobportal_get_default_theme_options();

// Top Bar
JobPortal_Kirki::add_section($section, array(
	'title'    => esc_html__('Top Bar', 'jobportal'),
	'priority' => 50,
));

JobPortal_Kirki::add_field('theme', [
	'type'     => 'notice',
	'settings' => 'top_bar_customize',
	'label'    => esc_html__('Top Bar Customize', 'jobportal'),
	'section'  => $section,
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'image',
	'settings'  => 'top_bar_ringbell',
	'label'     => esc_html__('Icon ring bell', 'jobportal'),
	'section'   => $section,
	'default'   => $default['top_bar_ringbell'],
]);

JobPortal_Kirki::add_field('theme', [
	'type'     => 'text',
	'settings' => 'top_bar_text',
	'label'    => esc_html__('Text Left', 'jobportal'),
	'section'  => $section,
	'default'  => $default['top_bar_text'],
]);

JobPortal_Kirki::add_field('theme', [
	'type'     => 'text',
	'settings' => 'top_bar_link',
	'label'    => esc_html__('Link', 'jobportal'),
	'section'  => $section,
	'default'  => $default['top_bar_link'],
]);

JobPortal_Kirki::add_field('theme', [
	'type'     => 'text',
	'settings' => 'top_bar_phone',
	'label'    => esc_html__('Phone', 'jobportal'),
	'section'  => $section,
	'default'  => $default['top_bar_phone'],
]);

JobPortal_Kirki::add_field('theme', [
	'type'     => 'text',
	'settings' => 'top_bar_email',
	'label'    => esc_html__('Email', 'jobportal'),
	'section'  => $section,
	'default'  => $default['top_bar_email'],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'color',
	'settings'  => 'top_bar_color',
	'label'     => esc_html__('Color', 'jobportal'),
	'section'   => $section,
	'transport' => 'postMessage',
	'default'   => $default['top_bar_color'],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'color',
	'settings'  => 'top_bar_bg_color',
	'label'     => esc_html__('Background Color', 'jobportal'),
	'section'   => $section,
	'transport' => 'postMessage',
	'default'   => $default['top_bar_bg_color'],
]);
