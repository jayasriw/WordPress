<?php

$section = 'color';

$default = jobportal_get_default_theme_options();

// Color
JobPortal_Kirki::add_section($section, array(
	'title'    => esc_html__('Color', 'jobportal'),
	'priority' => 30,
));

// Content
JobPortal_Kirki::add_field('theme', [
	'type'     => 'notice',
	'settings' => 'color_content',
	'label'    => esc_html__('Content', 'jobportal'),
	'section'  => $section,
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'color',
	'settings'  => 'text_color',
	'label'     => esc_html__('Text', 'jobportal'),
	'section'   => $section,
	'transport' => 'postMessage',
	'default'   => $default['text_color'],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'color',
	'settings'  => 'accent_color',
	'label'     => esc_html__('Accent', 'jobportal'),
	'section'   => $section,
	'transport' => 'postMessage',
	'default'   => $default['accent_color'],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'color',
	'settings'  => 'primary_color',
	'label'     => esc_html__('Primary', 'jobportal'),
	'section'   => $section,
	'transport' => 'postMessage',
	'default'   => $default['primary_color'],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'color',
	'settings'  => 'secondary_color',
	'label'     => esc_html__('Secondary', 'jobportal'),
	'section'   => $section,
	'transport' => 'postMessage',
	'default'   => $default['secondary_color'],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'color',
	'settings'  => 'border_color',
	'label'     => esc_html__('Border', 'jobportal'),
	'section'   => $section,
	'transport' => 'postMessage',
	'default'   => $default['border_color'],
]);

// Background
JobPortal_Kirki::add_field('theme', [
	'type'     => 'notice',
	'settings' => 'color_bg_body',
	'label'    => esc_html__('Background', 'jobportal'),
	'section'  => $section,
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'color',
	'settings'  => 'body_background_color',
	'label'     => esc_html__('Body Background', 'jobportal'),
	'section'   => $section,
	'transport' => 'postMessage',
	'default'   => $default['body_background_color'],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'image',
	'settings'  => 'bg_body_image',
	'label'     => esc_html__('Body BG Image', 'jobportal'),
	'section'   => $section,
	'transport' => 'postMessage',
	'default'   => $default['bg_body_image'],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'select',
	'settings'  => 'bg_body_size',
	'label'     => esc_html__('Background Size', 'jobportal'),
	'section'   => $section,
	'transport' => 'postMessage',
	'default'   => $default['bg_body_size'],
	'choices'   => [
		'auto'    => esc_html__('Auto', 'jobportal'),
		'cover'   => esc_html__('Cover', 'jobportal'),
		'contain' => esc_html__('Contain', 'jobportal'),
		'initial' => esc_html__('Initial', 'jobportal'),
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'select',
	'settings'  => 'bg_body_repeat',
	'label'     => esc_html__('Background Repeat', 'jobportal'),
	'section'   => $section,
	'transport' => 'postMessage',
	'default'   => $default['bg_body_repeat'],
	'choices'   => [
		'no-repeat' => esc_html__('No Repeat', 'jobportal'),
		'repeat'    => esc_html__('Repeat', 'jobportal'),
		'repeat-x'  => esc_html__('Repeat X', 'jobportal'),
		'repeat-y'  => esc_html__('Repeat Y', 'jobportal'),
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'select',
	'settings'  => 'bg_body_position',
	'label'     => esc_html__('Background Position', 'jobportal'),
	'section'   => $section,
	'transport' => 'postMessage',
	'default'   => $default['bg_body_position'],
	'choices'   => [
		'left top'      => esc_html__('Left Top', 'jobportal'),
		'left center'   => esc_html__('Left Center', 'jobportal'),
		'left bottom'   => esc_html__('Left Bottom', 'jobportal'),
		'right top'     => esc_html__('Right Top', 'jobportal'),
		'right center'  => esc_html__('Right Center', 'jobportal'),
		'right bottom'  => esc_html__('Right Bottom', 'jobportal'),
		'center top'    => esc_html__('Center Top', 'jobportal'),
		'center center' => esc_html__('Center Center', 'jobportal'),
		'center bottom' => esc_html__('Center Bottom', 'jobportal'),
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'select',
	'settings'  => 'bg_body_attachment',
	'label'     => esc_html__('Background Attachment', 'jobportal'),
	'section'   => $section,
	'transport' => 'postMessage',
	'default'   => $default['bg_body_attachment'],
	'choices'   => [
		'scroll' => esc_html__('Scroll', 'jobportal'),
		'fixed'  => esc_html__('Fixed', 'jobportal'),
	],
]);
