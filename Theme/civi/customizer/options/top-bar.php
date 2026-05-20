<?php

$section = 'top-bar';

$default = civi_get_default_theme_options();

// Top Bar
Civi_Kirki::add_section($section, array(
	'title'    => esc_html__('Top Bar', 'civi'),
	'priority' => 50,
));

Civi_Kirki::add_field('theme', [
	'type'     => 'notice',
	'settings' => 'top_bar_customize',
	'label'    => esc_html__('Top Bar Customize', 'civi'),
	'section'  => $section,
]);

Civi_Kirki::add_field('theme', [
	'type'      => 'image',
	'settings'  => 'top_bar_ringbell',
	'label'     => esc_html__('Icon ring bell', 'civi'),
	'section'   => $section,
	'default'   => $default['top_bar_ringbell'],
]);

Civi_Kirki::add_field('theme', [
	'type'     => 'text',
	'settings' => 'top_bar_text',
	'label'    => esc_html__('Text Left', 'civi'),
	'section'  => $section,
	'default'  => $default['top_bar_text'],
]);

Civi_Kirki::add_field('theme', [
	'type'     => 'text',
	'settings' => 'top_bar_link',
	'label'    => esc_html__('Link', 'civi'),
	'section'  => $section,
	'default'  => $default['top_bar_link'],
]);

Civi_Kirki::add_field('theme', [
	'type'     => 'text',
	'settings' => 'top_bar_phone',
	'label'    => esc_html__('Phone', 'civi'),
	'section'  => $section,
	'default'  => $default['top_bar_phone'],
]);

Civi_Kirki::add_field('theme', [
	'type'     => 'text',
	'settings' => 'top_bar_email',
	'label'    => esc_html__('Email', 'civi'),
	'section'  => $section,
	'default'  => $default['top_bar_email'],
]);

Civi_Kirki::add_field('theme', [
	'type'      => 'color',
	'settings'  => 'top_bar_color',
	'label'     => esc_html__('Color', 'civi'),
	'section'   => $section,
	'transport' => 'postMessage',
	'default'   => $default['top_bar_color'],
]);

Civi_Kirki::add_field('theme', [
	'type'      => 'color',
	'settings'  => 'top_bar_bg_color',
	'label'     => esc_html__('Background Color', 'civi'),
	'section'   => $section,
	'transport' => 'postMessage',
	'default'   => $default['top_bar_bg_color'],
]);
