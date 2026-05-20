<?php

/**
 * General Option
 *
 * @package JobPortal Theme
 * @version 1.0.0
 */

$panel = 'general';

$default = jobportal_get_default_theme_options();

// General
JobPortal_Kirki::add_panel($panel, array(
	'title'    => esc_html__('General', 'jobportal'),
	'priority' => 10,
));

// Site Identity
JobPortal_Kirki::add_section('site_identity', array(
	'title'    => esc_html__('Site Identity', 'jobportal'),
	'priority' => 10,
	'panel'    => $panel,
));

JobPortal_Kirki::add_field('theme', [
	'type'            => 'image',
	'priority'        => 80,
	'settings'        => 'logo_dark',
	'label'           => esc_html__('Logo Dark', 'jobportal'),
	'section'         => 'site_identity',
	'default'         => $default['logo_dark'],
]);

JobPortal_Kirki::add_field('theme', [
	'type'            => 'image',
	'priority'        => 80,
	'settings'        => 'logo_dark_retina',
	'label'           => esc_html__('Logo Dark Retina', 'jobportal'),
	'section'         => 'site_identity',
	'default'         => $default['logo_dark_retina'],
]);

JobPortal_Kirki::add_field('theme', [
	'type'            => 'image',
	'priority'        => 80,
	'settings'        => 'logo_light',
	'label'           => esc_html__('Logo Light', 'jobportal'),
	'section'         => 'site_identity',
	'default'         => $default['logo_light'],
]);

JobPortal_Kirki::add_field('theme', [
	'type'            => 'image',
	'priority'        => 80,
	'settings'        => 'logo_light_retina',
	'label'           => esc_html__('Logo Light Retina', 'jobportal'),
	'section'         => 'site_identity',
	'default'         => $default['logo_light_retina'],
]);

// Page Loading Effect
JobPortal_Kirki::add_section('page_loading_effect', array(
	'title'    => esc_html__('Page Loading Effect', 'jobportal'),
	'priority' => 10,
	'panel'    => $panel,
));

JobPortal_Kirki::add_field('theme', [
	'type'     => 'radio',
	'settings' => 'type_loading_effect',
	'label'    => esc_html__('Type Loading Effect', 'jobportal'),
	'section'  => 'page_loading_effect',
	'default'  => $default['type_loading_effect'],
	'choices'  => [
		'none'   		=> esc_html__('None', 'jobportal'),
		'css_animation' => esc_html__('CSS Animation', 'jobportal'),
		'image'  		=> esc_html__('Image', 'jobportal'),
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'     => 'radio-buttonset',
	'settings' => 'animation_loading_effect',
	'label'    => esc_html__('Animation Type', 'jobportal'),
	'section'  => 'page_loading_effect',
	'default'  => $default['animation_loading_effect'],
	'choices'  => [
		'css-1'  => '<span class="jobportal-ldef-circle jobportal-ldef-loading"><span></span></span>',
		'css-2'  => '<span class="jobportal-ldef-dual-ring jobportal-ldef-loading"></span>',
		'css-3'  => '<span class="jobportal-ldef-facebook jobportal-ldef-loading"><span></span><span></span><span></span></span>',
		'css-4'  => '<span class="jobportal-ldef-heart jobportal-ldef-loading"><span></span></span>',
		'css-5'  => '<span class="jobportal-ldef-ring jobportal-ldef-loading"><span></span><span></span><span></span><span></span></span>',
		'css-6'  => '<span class="jobportal-ldef-roller jobportal-ldef-loading"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></span>',
		'css-7'  => '<span class="jobportal-ldef-default jobportal-ldef-loading"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></span>',
		'css-8'  => '<span class="jobportal-ldef-ellipsis jobportal-ldef-loading"><span></span><span></span><span></span><span></span></span>',
		'css-9'  => '<span class="jobportal-ldef-grid jobportal-ldef-loading"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></span>',
		'css-10' => '<span class="jobportal-ldef-hourglass jobportal-ldef-loading"></span>',
		'css-11' => '<span class="jobportal-ldef-ripple jobportal-ldef-loading"><span></span><span></span></span>',
		'css-12' => '<span class="jobportal-ldef-spinner jobportal-ldef-loading"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></span>',
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'     => 'image',
	'settings' => 'image_loading_effect',
	'label'    => esc_html__('Image', 'jobportal'),
	'section'  => 'page_loading_effect',
	'default'  => $default['image_loading_effect'],
]);

// Page Title
JobPortal_Kirki::add_section('page_title', array(
	'title'    => esc_html__('Page Title', 'jobportal'),
	'priority' => 10,
	'panel'    => $panel,
));


JobPortal_Kirki::add_field('theme', [
	'type'      => 'color',
	'settings'  => 'page_title_text_color',
	'label'     => esc_html__('Text Color', 'jobportal'),
	'section'   => 'page_title',
	'transport' => 'postMessage',
	'default'   => $default['page_title_text_color'],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'color',
	'settings'  => 'page_title_bg_color',
	'label'     => esc_html__('Background Color', 'jobportal'),
	'section'   => 'page_title',
	'transport' => 'postMessage',
	'default'   => $default['page_title_bg_color'],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'image',
	'settings'  => 'page_title_bg_image',
	'label'     => esc_html__('Background Image', 'jobportal'),
	'section'   => 'page_title',
	'transport' => 'postMessage',
	'default'   => $default['page_title_bg_image'],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'select',
	'settings'  => 'page_title_bg_size',
	'label'     => esc_html__('Background Size', 'jobportal'),
	'section'   => 'page_title',
	'default'   => $default['page_title_bg_size'],
	'transport' => 'postMessage',
	'choices'   => [
		'auto'    => esc_html__('Auto', 'jobportal'),
		'cover'   => esc_html__('Cover', 'jobportal'),
		'contain' => esc_html__('Contain', 'jobportal'),
		'initial' => esc_html__('Initial', 'jobportal'),
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'select',
	'settings'  => 'page_title_bg_repeat',
	'label'     => esc_html__('Background Repeat', 'jobportal'),
	'section'   => 'page_title',
	'default'   => $default['page_title_bg_repeat'],
	'transport' => 'postMessage',
	'choices'   => [
		'no-repeat' => esc_html__('No Repeat', 'jobportal'),
		'repeat'    => esc_html__('Repeat', 'jobportal'),
		'repeat-x'  => esc_html__('Repeat X', 'jobportal'),
		'repeat-y'  => esc_html__('Repeat Y', 'jobportal'),
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'select',
	'settings'  => 'page_title_bg_position',
	'label'     => esc_html__('Background Position', 'jobportal'),
	'section'   => 'page_title',
	'default'   => $default['page_title_bg_position'],
	'transport' => 'postMessage',
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
	'settings'  => 'page_title_bg_attachment',
	'label'     => esc_html__('Background Attachment', 'jobportal'),
	'section'   => 'page_title',
	'default'   => $default['page_title_bg_attachment'],
	'transport' => 'postMessage',
	'choices'   => [
		'scroll' => esc_html__('Scroll', 'jobportal'),
		'fixed'  => esc_html__('Fixed', 'jobportal'),
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'slider',
	'settings'  => 'page_title_font_size',
	'label'     => esc_html__('Font Size', 'jobportal'),
	'section'   => 'page_title',
	'transport' => 'postMessage',
	'default'   => $default['page_title_font_size'],
	'choices'   => [
		'min'  => 12,
		'max'  => 50,
		'step' => 1,
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'slider',
	'settings'  => 'page_title_letter_spacing',
	'label'     => esc_html__('Letter Spacing', 'jobportal'),
	'section'   => 'page_title',
	'transport' => 'postMessage',
	'default'   => $default['page_title_letter_spacing'],
	'choices'   => [
		'min'  => 0,
		'max'  => 10,
		'step' => 0.5,
	],
]);

// Search Settings
JobPortal_Kirki::add_section('search_settings', array(
	'title'    => esc_html__('Search Settings', 'jobportal'),
	'priority' => 120,
	'panel'    => $panel,
));

JobPortal_Kirki::add_field('theme', [
	'type'        => 'switch',
	'settings'    => 'enable_fulltext_search',
	'label'       => esc_html__('Enable MySQL FULLTEXT Search', 'jobportal'),
	'description' => esc_html__('Enable this to use MySQL FULLTEXT index for significantly faster search performance on large sites. Note: You must create the index first via the admin notice. This method might ignore words shorter than 4 characters depending on your server configuration.', 'jobportal'),
	'section'     => 'search_settings',
	'default'     => $default['enable_fulltext_search'],
	'choices'     => [
		'on'  => esc_html__('On', 'jobportal'),
		'off' => esc_html__('Off', 'jobportal'),
	],
]);
