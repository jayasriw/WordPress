<?php
defined( 'ABSPATH' ) || exit;

remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'wp_generator' );

// Remove https://api.w.org/
remove_action( 'wp_head', 'rest_output_link_header', 10 );
remove_action( 'template_redirect', 'rest_output_link_header', 11 );

// Remove enqueue style
function remove_block_css() {
    wp_dequeue_style( 'wp-block-library-theme' );
}
add_action( 'wp_enqueue_scripts', 'remove_block_css', 100 );

// Remove unused links on admin bar.
function jobportal_child_demo_admin_bar_remove_logo() {
	/**
	 * @var WP_Admin_Bar $wp_admin_bar
	 */
	global $wp_admin_bar;
	
	$wp_admin_bar->remove_menu( 'wp-logo' );

	$wp_admin_bar->remove_menu( 'comments' );

	$wp_admin_bar->remove_menu( 'updates' );
}

add_action( 'wp_before_admin_bar_render', 'jobportal_child_demo_admin_bar_remove_logo', 0 );

// Remove type attribute from script and style tags added by WordPress.
add_action( 'wp_loaded', 'jobportal_child_demo_output_buffer_start' );
function jobportal_child_demo_output_buffer_start() {
	ob_start( 'jobportal_child_demo_output_callback' );
}

add_action( 'shutdown', 'jobportal_child_demo_output_buffer_end' );
function jobportal_child_demo_output_buffer_end() {
	if ( ob_get_length() ) {
		ob_end_flush();
	}
}

function jobportal_child_demo_output_callback( $buffer ) {
	return preg_replace( "%[ ]type=[\'\"]text\/(javascript|css)[\'\"]%", '', $buffer );
}

// Remove Recent Comments wp_head CSS
add_action( 'widgets_init', 'jobportal_child_demo_remove_recent_comments_style' );
function jobportal_child_demo_remove_recent_comments_style() {
	global $wp_widget_factory;
	remove_action( 'wp_head', array(
		$wp_widget_factory->widgets['WP_Widget_Recent_Comments'],
		'recent_comments_style',
	) );
}


/**
 * Theme functions and definitions.
 */
function jobportal_child_enqueue_styles() {

    wp_enqueue_style( 'jobportal-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( 'jobportal_minify-style' ),
        wp_get_theme()->get('Version')
    );

}
add_action(  'wp_enqueue_scripts', 'jobportal_child_enqueue_styles' );

/**
 * Enqueue child scripts
 */
add_action( 'wp_enqueue_scripts', 'jobportal_child_enqueue_scripts' );
if ( ! function_exists( 'jobportal_child_enqueue_scripts' ) ) {

	function jobportal_child_enqueue_scripts() {
		wp_enqueue_script( 'jobportal-child-script',
			trailingslashit( get_stylesheet_directory_uri() ) . 'script.js',
			array( 'jquery' ),
			null,
			true );
	}

}