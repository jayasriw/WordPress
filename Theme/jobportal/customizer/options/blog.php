<?php

$panel = 'blog';

$default = jobportal_get_default_theme_options();

// Blog
JobPortal_Kirki::add_panel($panel, array(
	'title'    => esc_html__('Blog', 'jobportal'),
	'priority' => 70,
));

// Blog archive
JobPortal_Kirki::add_section('blog_archive', array(
	'title' => esc_html__('Blog Archive', 'jobportal'),
	'panel' => $panel,
));

JobPortal_Kirki::add_field('theme', [
	'type'            => 'notice',
	'settings'        => 'blog_customize',
	'label'           => esc_html__('Blog Customize', 'jobportal'),
	'section'         => 'blog_archive',
	'partial_refresh' => [
		'blog_customize' => [
			'selector'        => '#primary.content-blog',
			'render_callback' => 'wp_get_document_title',
		],
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'radio-image',
	'settings'  => 'blog_sidebar',
	'label'     => esc_html__('Sidebar Layout', 'jobportal'),
	'section'   => 'blog_archive',
	'transport' => 'postMessage',
	'default'   => $default['blog_sidebar'],
	'choices'   => [
		'left-sidebar'  => get_template_directory_uri() . '/customizer/assets/images/left-sidebar.png',
		'no-sidebar' 	=> get_template_directory_uri() . '/customizer/assets/images/no-sidebar.png',
		'right-sidebar' => get_template_directory_uri() . '/customizer/assets/images/right-sidebar.png',
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'slider',
	'settings'  => 'blog_sidebar_width',
	'label'     => esc_html__('Sidebar Width', 'jobportal'),
	'section'   => 'blog_archive',
	'transport' => 'postMessage',
	'default'   => $default['blog_sidebar_width'],
	'choices'   => [
		'min'  => 270,
		'max'  => 420,
		'step' => 1,
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'     => 'text',
	'settings' => 'blog_image_size',
	'label'    => esc_html__('Image size', 'jobportal'),
	'section'  => 'blog_archive',
	'default'  => $default['blog_image_size'],
]);

JobPortal_Kirki::add_field('theme', [
	'type'     => 'radio-image',
	'settings' => 'blog_content_layout',
	'label'    => esc_html__('Content Layout', 'jobportal'),
	'section'  => 'blog_archive',
	'default'  => $default['blog_content_layout'],
	'choices'  => [
		'layout-grid' => get_template_directory_uri() . '/customizer/assets/images/layout-grid.png',
		'layout-list' => get_template_directory_uri() . '/customizer/assets/images/layout-list.png',
		'layout-masonry' => get_template_directory_uri() . '/customizer/assets/images/layout-masonry.png',
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'radio-image',
	'settings'  => 'blog_number_column',
	'label'     => esc_html__('Columns', 'jobportal'),
	'section'   => 'blog_archive',
	'transport' => 'postMessage',
	'default'   => $default['blog_number_column'],
	'choices'   => [
		'columns-2' => get_template_directory_uri() . '/customizer/assets/images/col-2.png',
		'columns-3' => get_template_directory_uri() . '/customizer/assets/images/col-3.png',
		'columns-4' => get_template_directory_uri() . '/customizer/assets/images/col-4.png',
	],
	'active_callback' => [
		[
			'setting'  => 'blog_content_layout',
			'operator' => '!=',
			'value'    => 'layout-list',
		]
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'toggle',
	'settings'  => 'blog_enable_categories',
	'label'     => esc_html__('Enable Head Categories', 'jobportal'),
	'section'   => 'blog_archive',
	'transport' => 'postMessage',
	'default'   => $default['blog_enable_categories'],
	'active_callback' => [
		[
			'setting'  => 'blog_content_layout',
			'operator' => '!=',
			'value'    => 'layout-list',
		]
	],
]);

// Single post
JobPortal_Kirki::add_section('single_post', array(
	'title' => esc_html__('Single Post', 'jobportal'),
	'panel' => $panel,
));

JobPortal_Kirki::add_field('theme', [
	'type'      => 'radio-image',
	'settings'  => 'post_single_sidebar',
	'label'     => esc_html__('Sidebar Layout', 'jobportal'),
	'section'   => 'single_post',
	'transport' => 'postMessage',
	'default'   => $default['post_single_sidebar'],
	'choices'   => [
		'left-sidebar'  => get_template_directory_uri() . '/customizer/assets/images/left-sidebar.png',
		'no-sidebar' 	=> get_template_directory_uri() . '/customizer/assets/images/no-sidebar.png',
		'right-sidebar' => get_template_directory_uri() . '/customizer/assets/images/right-sidebar.png',
	],
]);

// Page Title
JobPortal_Kirki::add_section('page_title_blog', array(
	'title' => esc_html__('Page Title', 'jobportal'),
	'panel' => $panel,
));

JobPortal_Kirki::add_field('theme', [
	'type'            => 'notice',
	'settings'        => 'page_title_blog',
	'label'           => esc_html__('Page Title', 'jobportal'),
	'section'         => 'page_title_blog',
	'partial_refresh' => [
		'page_title_blog' => [
			'selector'        => '.page-title-blog',
			'render_callback' => 'wp_get_document_title',
		],
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'toggle',
	'settings'  => 'enable_page_title_blog',
	'label'     => esc_html__('Enable Page Title', 'jobportal'),
	'section'   => 'page_title_blog',
	'transport' => 'postMessage',
	'default'   => $default['enable_page_title_blog'],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'text',
	'settings'  => 'page_title_blog_name',
	'label'     => esc_html__('Title', 'jobportal'),
	'section'   => 'page_title_blog',
	'transport' => 'postMessage',
	'default'   => $default['page_title_blog_name'],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'radio-image',
	'settings'  => 'style_page_title_blog',
	'section'   => 'page_title_blog',
	'transport' => 'postMessage',
	'multiple'  => 1,
	'default'   => $default['style_page_title_blog'],
	'choices'   => [
		'normal' => get_template_directory_uri() . '/customizer/assets/images/text-uppercase.png',
		'italic' => get_template_directory_uri() . '/customizer/assets/images/text-italic.png',
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'color',
	'settings'  => 'bg_page_title_blog',
	'label'     => esc_html__('Background Color', 'jobportal'),
	'section'   => 'page_title_blog',
	'transport' => 'postMessage',
	'default'   => $default['bg_page_title_blog'],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'color',
	'settings'  => 'color_page_title_blog',
	'label'     => esc_html__('Text Color', 'jobportal'),
	'section'   => 'page_title_blog',
	'transport' => 'postMessage',
	'default'   => $default['color_page_title_blog'],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'image',
	'settings'  => 'bg_image_page_title_blog',
	'label'     => esc_html__('Background Image', 'jobportal'),
	'section'   => 'page_title_blog',
	'transport' => 'postMessage',
	'default'   => $default['bg_image_page_title_blog'],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'select',
	'settings'  => 'bg_size_page_title_blog',
	'label'     => esc_html__('Background Size', 'jobportal'),
	'section'   => 'page_title_blog',
	'transport' => 'postMessage',
	'default'   => $default['bg_size_page_title_blog'],
	'choices'   => [
		'auto'    => esc_html__('Auto', 'jobportal'),
		'cover'   => esc_html__('Cover', 'jobportal'),
		'contain' => esc_html__('Contain', 'jobportal'),
		'initial' => esc_html__('Initial', 'jobportal'),
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'select',
	'settings'  => 'bg_repeat_page_title_blog',
	'label'     => esc_html__('Background Repeat', 'jobportal'),
	'section'   => 'page_title_blog',
	'transport' => 'postMessage',
	'default'   => $default['bg_repeat_page_title_blog'],
	'choices'   => [
		'no-repeat' => esc_html__('No Repeat', 'jobportal'),
		'repeat'    => esc_html__('Repeat', 'jobportal'),
		'repeat-x'  => esc_html__('Repeat X', 'jobportal'),
		'repeat-y'  => esc_html__('Repeat Y', 'jobportal'),
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'select',
	'settings'  => 'bg_position_page_title_blog',
	'label'     => esc_html__('Background Position', 'jobportal'),
	'section'   => 'page_title_blog',
	'transport' => 'postMessage',
	'default'   => $default['bg_position_page_title_blog'],
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
	'settings'  => 'bg_attachment_page_title_blog',
	'label'     => esc_html__('Background Attachment', 'jobportal'),
	'section'   => 'page_title_blog',
	'transport' => 'postMessage',
	'default'   => $default['bg_attachment_page_title_blog'],
	'choices'   => [
		'scroll' => esc_html__('Scroll', 'jobportal'),
		'fixed'  => esc_html__('Fixed', 'jobportal'),
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'slider',
	'settings'  => 'font_size_page_title_blog',
	'label'     => esc_html__('Font Size', 'jobportal'),
	'section'   => 'page_title_blog',
	'transport' => 'postMessage',
	'default'   => $default['font_size_page_title_blog'],
	'choices'   => [
		'min'  => 12,
		'max'  => 50,
		'step' => 1,
	],
]);

JobPortal_Kirki::add_field('theme', [
	'type'      => 'slider',
	'settings'  => 'letter_spacing_page_title_blog',
	'label'     => esc_html__('Letter Spacing', 'jobportal'),
	'section'   => 'page_title_blog',
	'transport' => 'postMessage',
	'default'   => $default['letter_spacing_page_title_blog'],
	'choices'   => [
		'min'  => 0,
		'max'  => 10,
		'step' => 0.5,
	],
]);
