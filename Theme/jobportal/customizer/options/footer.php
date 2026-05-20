<?php

$section = 'footer';

$default = jobportal_get_default_theme_options();

// Footer
JobPortal_Kirki::add_section($section, array(
	'title'    => esc_html__('Footer', 'jobportal'),
	'priority' => 50,
));

JobPortal_Kirki::add_field('theme', [
	'type'            => 'notice',
	'settings'        => 'footer_customize',
	'label'           => esc_html__('Footer Customize', 'jobportal'),
	'section'         => $section,
	'partial_refresh' => [
		'header_type' => [
			'selector'        => 'footer.site-footer',
			'render_callback' => 'wp_get_document_title',
		],
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'     => 'select',
	'settings' => 'footer_type',
	'label'    => esc_html__('Footer Default', 'jobportal'),
	'section'  => $section,
	'default'  => $default['footer_type'],
	'choices'  => jobportal_get_footer_elementor(),
]);

JobPortal_Kirki::add_field('theme', [
	'type'     => 'text',
	'settings' => 'footer_copyright_text',
	'label'    => esc_html__('Copyright', 'jobportal'),
	'section'  => $section,
	'default'  => $default['footer_copyright_text'],
]);
