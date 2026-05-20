<?php

// Exit if accessed directly.
if (!defined("ABSPATH")) {
	exit();
}

/**
 * Initial setup for this theme
 *
 */
class Civi_Init
{
	/**
	 * The constructor.
	 */
	function __construct()
	{
		// class Civi_Helper
		new Civi_Helper();

		// class Civi_Enqueue
		new Civi_Enqueue();

		// class Civi_Kirki
		new Civi_Kirki();

		// class Civi_Customizer
		new Civi_Customizer();

		// class Civi_Ajax
		new Civi_Ajax_Include();

		// Load the theme's textdomain.
		add_action("after_setup_theme", [$this, "load_theme_textdomain"]);

		// Register navigation menus.
		add_action("after_setup_theme", [$this, "register_nav_menus"]);

		// Add theme supports.
		add_action("after_setup_theme", [$this, "add_theme_supports"]);

		// Register nav menu.
		add_action("after_setup_theme", [$this, "register_menus"]);

		// Register widget areas.
		add_action("widgets_init", [$this, "widgets_init"]);

		// Register head template.
		add_action("wp_body_open", [$this, "loading_effect"], 9999);

		// Register footer template.
		add_action("wp_footer", [$this, "global_template"]);

		// Add reCAPTCHA styling
		add_action("wp_head", [$this, "recaptcha_styling"]);

		// Support editor style.
		add_editor_style(["/assets/css/editor-style.css"]);
	}

	/**
	 * Registers the Menus.
	 *
	 * @access public
	 */
	public function register_nav_menus()
	{
		// Register all menu locations
		register_nav_menus([
			"primary" => esc_html__("Primary", "civi"),
			"main_menu" => esc_html__("Main Menu", "civi"),
			"mobile_menu" => esc_html__("Mobile Menu", "civi"),
		]);
	}

	/**
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 *
	 * @access public
	 */
	public function load_theme_textdomain()
	{
		load_theme_textdomain("civi", CIVI_THEME_DIR . "/languages");
	}

	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 *
	 * @access public
	 */
	function add_theme_supports()
	{
		/*
		 * Add default posts and comments RSS feed links to head.
		 */
		add_theme_support("automatic-feed-links");

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support("title-tag");

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support("post-thumbnails");

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support("html5", [
			"search-form",
			"comment-form",
			"comment-list",
			"gallery",
			"caption",
		]);

		/**
		 * Add post-formats support.
		 */
		add_theme_support(
			'post-formats',
			array(
				'link',
				'aside',
				'image',
				'quote',
				'video',
			)
		);

		/*
		 * Support selective refresh for widget
		 */
		add_theme_support("customize-selective-refresh-widgets");

		/*
		 * wp-block-styles
		 */
		add_theme_support("wp-block-styles");

		/*
		 * responsive-embeds
		 */
		add_theme_support("responsive-embeds");

		// Add support for full and wide align images.
		add_theme_support('align-wide');

		/*
		 * custom-logo
		 */
		$logo_width  = 300;
		$logo_height = 100;
		add_theme_support(
			'custom-logo',
			array(
				'height'               => $logo_height,
				'width'                => $logo_width,
				'flex-width'           => true,
				'flex-height'          => true,
				'unlink-homepage-logo' => true,
			)
		);

		/*
		 * Optimize speed for homepage
		 */
		add_theme_support("civi");
	}

	/**
	 * Register nav menu.
	 */
	function register_menus()
	{
		register_nav_menus([
			"primary" => esc_html__("Primary Menu", "civi"),
		]);

		register_nav_menus([
			"main_menu" => esc_html__("Main Menu", "civi"),
		]);

		register_nav_menus([
			"mobile_menu" => esc_html__("Mobile Menu", "civi"),
		]);
	}

	/**
	 * Register widget area.
	 *
	 * @access public
	 * @link   https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
	 */
	function widgets_init()
	{
		register_sidebar([
			"id" => "sidebar",
			"name" => esc_html__("Sidebar", "civi"),
			"description" => esc_html__("Add widgets here.", "civi"),
			"before_widget" => '<section id="%1$s" class="widget %2$s">',
			"after_widget" => "</section>",
			"before_title" => '<h3 class="widget-title">',
			"after_title" => "</h3>",
		]);
		register_sidebar([
			"id" => "jobs_sidebar",
			"name" => esc_html__("Jobs Sidebar", "civi"),
			"description" => esc_html__("Add widgets here.", "civi"),
			"before_widget" => '<section id="%1$s" class="widget %2$s">',
			"after_widget" => "</section>",
			"before_title" => '<h3 class="widget-title">',
			"after_title" => "</h3>",
		]);
		register_sidebar([
			"id" => "company_sidebar",
			"name" => esc_html__("Company Sidebar", "civi"),
			"description" => esc_html__("Add widgets here.", "civi"),
			"before_widget" => '<section id="%1$s" class="widget %2$s">',
			"after_widget" => "</section>",
			"before_title" => '<h3 class="widget-title">',
			"after_title" => "</h3>",
		]);
		register_sidebar(array(
			'id'            => 'candidate_sidebar',
			'name'          => esc_html__('Candidate Sidebar', 'civi'),
			'description'   => esc_html__('Add widgets here.', 'civi'),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		));
		register_sidebar(array(
			'id'            => 'service_sidebar',
			'name'          => esc_html__('Service Sidebar', 'civi'),
			'description'   => esc_html__('Add widgets here.', 'civi'),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		));
	}

	/**
	 * Register global template
	 */
	function loading_effect()
	{
		get_template_part("templates/global/site-loading");
	}

	/**
	 * Register global template
	 */
	function global_template()
	{
		get_template_part("templates/global/account");

		get_template_part("templates/global/canvas-search");
	}

	/**
	 * Add reCAPTCHA v3 badge styling
	 */
	function recaptcha_styling()
	{
		$enable_captcha = Civi_Helper::civi_get_option('enable_captcha');
		$recaptcha_version = Civi_Helper::civi_get_option('recaptcha_version', 'v2');

		if ($enable_captcha === '1' && $recaptcha_version === 'v3') {
			$hide_badge = Civi_Helper::civi_get_option('recaptcha_v3_hide_badge', '0');

			echo '<style id="civi-recaptcha-styling">';
			if ($hide_badge === '1') {
				echo '.grecaptcha-badge { visibility: hidden !important; opacity: 0 !important; }';
			}

			echo '</style>';
		}
	}
}
