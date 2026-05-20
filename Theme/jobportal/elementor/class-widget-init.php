<?php

namespace JobPortal_Elementor;

use Elementor\Plugin;

defined('ABSPATH') || exit;

class Widget_Init
{

	private static $_instance = null;

	public static function instance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function initialize()
	{
		add_action('elementor/elements/categories_registered', [$this, 'add_elementor_widget_categories']);
		add_action('elementor/element/after_add_attributes', [$this, 'add_elementor_attribute']);

		// Registered Widgets.
		add_action('elementor/widgets/register', [$this, 'init_widgets']);
		//add_action( 'elementor/widgets/register', [ $this, 'remove_unwanted_widgets' ], 15 );

		add_action('elementor/frontend/after_register_scripts', [$this, 'after_register_scripts']);

		add_action('elementor/editor/after_enqueue_scripts', [$this, 'enqueue_editor_scripts']);

		// Modify original widgets settings.
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/original/modify-base.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/original/section.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/original/column.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/original/accordion.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/original/animated-headline.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/original/counter.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/original/form.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/original/heading.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/original/icon-box.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/original/progress.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/original/countdown.php';
	}

	/**
	 * Register scripts for widgets.
	 */
	public function after_register_scripts()
	{
		// Fix Wordpress old version not registered this script.
		if (!wp_script_is('imagesloaded', 'registered')) {
			wp_register_script('imagesloaded', JOBPORTAL_THEME_URI . '/assets/libs/imagesloaded/imagesloaded.min.js', array('jquery'), null, true);
		}

		wp_register_script('circle-progress', JOBPORTAL_THEME_URI . '/assets/libs/circle-progress/circle-progress.min.js', array('jquery'), null, true);
		wp_register_script("jobportal-widget-circle-progress', JOBPORTAL_ELEMENTOR_URI . '/assets/js/widgets/widget-circle-progress.js', array(
			'jquery',
			'circle-progress',
		), null, true);

		wp_register_script("jobportal-swiper-wrapper', JOBPORTAL_THEME_URI . '/assets/js/swiper-wrapper.js', array('jquery'), JOBPORTAL_THEME_VER, true);
		wp_register_script("jobportal-group-widget-carousel', JOBPORTAL_ELEMENTOR_URI . '/assets/js/widgets/group-widget-carousel.js', array(
			'jquery',
			"jobportal-swiper',
			"jobportal-swiper-wrapper',
		), null, true);
		$jobportal_swiper_js = array(
			'prevText' => esc_html__('Prev', 'jobportal'),
			'nextText' => esc_html__('Next', 'jobportal'),
		);
		wp_localize_script("jobportal-swiper-wrapper', '$jobportalSwiper', $jobportal_swiper_js);

        wp_register_script('scrollmonitor', JOBPORTAL_ELEMENTOR_URI . '/assets/libs/scrollmonitor/scrollmonitor.min.js', array('jquery'), null, true);

        wp_register_script('anime', JOBPORTAL_ELEMENTOR_URI . '/assets/libs/anime/anime.min.js', array('jquery'), null, true);

        wp_register_script("jobportal-grid-query', JOBPORTAL_ELEMENTOR_URI . '/assets/js/widgets/grid-query.min.js', array('jquery'), null, true);

		wp_register_script("jobportal-widget-modern-menu', JOBPORTAL_ELEMENTOR_URI . '/assets/js/widgets/widget-modern-menu.js', array('jquery'), null, true);
		wp_register_script("jobportal-widget-modern-tabs', JOBPORTAL_ELEMENTOR_URI . '/assets/js/widgets/widget-modern-tabs.js', array('jquery'), null, true);

		wp_register_script("jobportal-widget-grid-post', JOBPORTAL_ELEMENTOR_URI . '/assets/js/widgets/widget-grid-post.js', array("jobportal-grid-layout'), null, true);
		wp_register_script("jobportal-group-widget-grid', JOBPORTAL_ELEMENTOR_URI . '/assets/js/widgets/group-widget-grid.js', array("jobportal-grid-layout'), null, true);

		wp_register_script("jobportal-widget-google-map', JOBPORTAL_ELEMENTOR_URI . '/assets/js/widgets/widget-google-map.js', array('jquery'), null, true);

		wp_register_script("jobportal-widget-testimonial-carousel', JOBPORTAL_ELEMENTOR_URI . '/assets/js/widgets/widget-testimonial.js', array(
			'jquery',
		), null, true);

		wp_register_script("jobportal-widget-list', JOBPORTAL_ELEMENTOR_URI . '/assets/js/widgets/widget-list.js', array(
			'jquery',
		), null, true);

		wp_register_script("jobportal-widget-user-form', JOBPORTAL_ELEMENTOR_URI . '/assets/js/widgets/widget-user-form.js', array(
			'jquery',
		), null, true);

		wp_register_script("jobportal-social-networks', JOBPORTAL_ELEMENTOR_URI . '/assets/js/widgets/widget-social-networks.js', array(
			'jquery',
		), null, true);

		wp_register_script("jobportal-widget-flip-box', JOBPORTAL_ELEMENTOR_URI . '/assets/js/widgets/widget-flip-box.js', array(
			'jquery',
			'imagesloaded',
		), null, true);

		wp_register_script('typed', JOBPORTAL_ELEMENTOR_URI . '/assets/libs/typed/typed.min.js', array('jquery'), null, true);
		wp_register_script('vivus', JOBPORTAL_ELEMENTOR_URI . '/assets/libs/vivus/vivus.min.js', array('jquery'), null, true);
		wp_register_script("jobportal-widget-fancy-heading', JOBPORTAL_ELEMENTOR_URI . '/assets/js/widgets/widget-fancy-heading.js', array(
			'jquery',
			'typed',
		), null, true);

		wp_register_script("jobportal-widget-accordion', JOBPORTAL_ELEMENTOR_URI . '/assets/js/widgets/widget-accordion.js', array(
			'jquery',
		), null, true);

		wp_register_script("jobportal-widget-accordion-image', JOBPORTAL_ELEMENTOR_URI . '/assets/js/widgets/widget-accordion-image.js', array(
			'jquery',
		), null, true);

        wp_register_script("jobportal-widget-morphing', JOBPORTAL_ELEMENTOR_URI . '/assets/js/widgets/widget-morphing.js', array(
            'jquery',
        ), null, true);

		wp_register_script("jobportal-widget-gallery-justified-content', JOBPORTAL_ELEMENTOR_URI . '/assets/js/widgets/widget-gallery-justified-content.js', array(
			'justifiedGallery',
		), null, true);

		wp_register_script('countdown', JOBPORTAL_ELEMENTOR_URI . '/assets/libs/jquery.countdown/js/jquery.countdown.min.js', array('jquery'), JOBPORTAL_THEME_VER, true);
	}

	/**
	 * enqueue scripts in editor mode.
	 */
	public function enqueue_editor_scripts()
	{
		wp_enqueue_script("jobportal-widget-accordion', JOBPORTAL_ELEMENTOR_URI . '/assets/js/editor.js', array('jquery'), null, true);
	}

	/**
	 * @param \Elementor\Elements_Manager $elements_manager
	 *
	 * Add category.
	 */
	function add_elementor_widget_categories($elements_manager)
	{
		$elements_manager->add_category('jobportal', [
			'title' => esc_html__('JobPortal', 'jobportal'),
			'icon'  => 'fa fa-plug',
		]);
	}

	/**
	 * @param \Elementor\Elements_Manager $element_base
	 *
	 * Add attribute.
	 */
	function add_elementor_attribute($element_base)
	{
		$settings = $element_base->get_settings_for_display();

		$_animation = !empty($settings['_animation']);
		$animation = !empty($settings['animation']);
		$has_animation = $_animation && 'none' !== $settings['_animation'] || $animation && 'none' !== $settings['animation'];

		if ($has_animation) {
			$is_static_render_mode = Plugin::$instance->frontend->is_static_render_mode();

			$jobportal_effect = array(
				'JobPortalSlideInDown',
				'JobPortalSlideInLeft',
				'JobPortalSlideInRight',
				'JobPortalSlideInUp',
				'JobPortalBottomToTop',
				'JobPortalSpin',
				'JobPortalMoving01',
				'JobPortalMoving02',
				'JobPortalMoving03',
				'JobPortalMoving04',
				'JobPortalMoving05',
			);

			$jobportal_current_effect = $jobportal_animation = '';
			if (!empty($settings['animation'])) {
				$jobportal_animation = $settings['animation'];
			} elseif (!empty($settings['_animation'])) {
				$jobportal_animation = $settings['_animation'];
			}

			if (!empty($jobportal_animation)) {
				if ($jobportal_animation == 'JobPortalSlideInDown') {
					$jobportal_current_effect = "jobportal-slide-in-down';
				} elseif ($jobportal_animation == 'JobPortalSlideInLeft') {
					$jobportal_current_effect = "jobportal-slide-in-left';
				} elseif ($jobportal_animation == 'JobPortalSlideInRight') {
					$jobportal_current_effect = "jobportal-slide-in-right';
				} elseif ($jobportal_animation == 'JobPortalSlideInUp') {
					$jobportal_current_effect = "jobportal-slide-in-up';
				} elseif ($jobportal_animation == 'JobPortalBottomToTop') {
					$jobportal_current_effect = "jobportal-bottom-to-top';
				} elseif ($jobportal_animation == 'JobPortalSpin') {
					$jobportal_current_effect = "jobportal-spin';
				} elseif ($jobportal_animation == 'JobPortalMoving01') {
					$jobportal_current_effect = "jobportal-moving-01';
				} elseif ($jobportal_animation == 'JobPortalMoving02') {
					$jobportal_current_effect = "jobportal-moving-02';
				} elseif ($jobportal_animation == 'JobPortalMoving03') {
					$jobportal_current_effect = "jobportal-moving-03';
				} elseif ($jobportal_animation == 'JobPortalMoving04') {
					$jobportal_current_effect = "jobportal-moving-04';
				} elseif ($jobportal_animation == 'JobPortalMoving05') {
					$jobportal_current_effect = "jobportal-moving-05';
				}

				if (!$is_static_render_mode && in_array($jobportal_animation, $jobportal_effect)) {
					// Hide the element until the animation begins
					$element_base->add_render_attribute('_wrapper', 'class', ["jobportal-elementor-loading', $jobportal_current_effect]);
				}
			}
		}
	}

	/**
	 * Init Widgets
	 *
	 * Include widgets files and register them
	 *
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public function init_widgets()
	{

		// Include Widget files.
		require_once JOBPORTAL_ELEMENTOR_DIR . '/module-query.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/base.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/form/form-base.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/posts/posts-base.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/carousel/carousel-base.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/carousel/posts-carousel-base.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/carousel/static-carousel.php';

		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/accordion.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/accordion-image.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/button.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/circle-progress-chart.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/google-map.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/heading.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/fancy-heading.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/icon.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/icon-box.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/number-box.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/user-form.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/job-search.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/image-box.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/image-rotate.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/image-animation.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/image-layers.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/image-gallery.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/banner.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/nav-menu.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/shapes.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/flip-box.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/instagram.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/attribute-list.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/gradation.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/timeline.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/list.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/pricing-table.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/twitter.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/team-member.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/social-networks.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/popup-video.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/separator.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/table.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/modern-tabs.php';
        require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/morphing.php';

		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/grid/grid-base.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/grid/static-grid.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/grid/client-logo.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/grid/view-demo.php';

		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/posts/blog.php';

		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/testimonial-grid.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/carousel/testimonial-carousel.php';

		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/carousel/team-member-carousel.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/carousel/image-carousel.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/carousel/modern-carousel.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/carousel/modern-slider.php';
		require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/carousel/freelancer-carousel.php';

        require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/header/account.php';
        require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/header/notification.php';
        require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/header/search-popup.php';

		// Register Widgets.
		Plugin::instance()->widgets_manager->register(new Widget_Accordion());
		Plugin::instance()->widgets_manager->register(new Widget_Accordion_Image());
		Plugin::instance()->widgets_manager->register(new Widget_Button());
		Plugin::instance()->widgets_manager->register(new Widget_Client_Logo());
		Plugin::instance()->widgets_manager->register(new Widget_Circle_Progress_Chart());
		Plugin::instance()->widgets_manager->register(new Widget_Google_Map());
		Plugin::instance()->widgets_manager->register(new Widget_Heading());
		Plugin::instance()->widgets_manager->register(new Widget_Icon());
		Plugin::instance()->widgets_manager->register(new Widget_Icon_Box());
		Plugin::instance()->widgets_manager->register(new Widget_Number_Box());
		Plugin::instance()->widgets_manager->register(new Widget_User_Form());
		Plugin::instance()->widgets_manager->register(new Widget_Job_Search());
		Plugin::instance()->widgets_manager->register(new Widget_Image_Box());
		Plugin::instance()->widgets_manager->register(new Widget_Image_Rotate());
		Plugin::instance()->widgets_manager->register(new Widget_Image_Animation());
		Plugin::instance()->widgets_manager->register(new Widget_Image_Layers());
		Plugin::instance()->widgets_manager->register(new Widget_Image_Gallery());
		Plugin::instance()->widgets_manager->register(new Widget_Image_Carousel());
		Plugin::instance()->widgets_manager->register(new Widget_Freelancer_Carousel());
		Plugin::instance()->widgets_manager->register(new Widget_Banner());
		Plugin::instance()->widgets_manager->register(new Widget_Nav_Menu());
		Plugin::instance()->widgets_manager->register(new Widget_Shapes());
		Plugin::instance()->widgets_manager->register(new Widget_Modern_Carousel());
		Plugin::instance()->widgets_manager->register(new Widget_Modern_Slider());
		Plugin::instance()->widgets_manager->register(new Widget_Instagram());
		Plugin::instance()->widgets_manager->register(new Widget_Flip_Box());
		Plugin::instance()->widgets_manager->register(new Widget_Blog());
		Plugin::instance()->widgets_manager->register(new Widget_Attribute_List());
		Plugin::instance()->widgets_manager->register(new Widget_List());
		Plugin::instance()->widgets_manager->register(new Widget_Fancy_Heading());
		Plugin::instance()->widgets_manager->register(new Widget_Gradation());
		Plugin::instance()->widgets_manager->register(new Widget_Timeline());
		Plugin::instance()->widgets_manager->register(new Widget_Pricing_Table());
		Plugin::instance()->widgets_manager->register(new Widget_Twitter());
		Plugin::instance()->widgets_manager->register(new Widget_Team_Member());
		Plugin::instance()->widgets_manager->register(new Widget_Team_Member_Carousel());
		Plugin::instance()->widgets_manager->register(new Widget_Testimonial_Carousel());
		Plugin::instance()->widgets_manager->register(new Widget_Testimonial_Grid());
		Plugin::instance()->widgets_manager->register(new Widget_Social_Networks());
		Plugin::instance()->widgets_manager->register(new Widget_Popup_Video());
		Plugin::instance()->widgets_manager->register(new Widget_Separator());
		Plugin::instance()->widgets_manager->register(new Widget_Table());
		Plugin::instance()->widgets_manager->register(new Widget_View_Demo());
		Plugin::instance()->widgets_manager->register(new Widget_Moderm_Tabs());
        Plugin::instance()->widgets_manager->register(new Widget_Account());
        Plugin::instance()->widgets_manager->register(new Widget_Notification());
        Plugin::instance()->widgets_manager->register(new Widget_Search_Popup());
        Plugin::instance()->widgets_manager->register(new Widget_Morphing());

		/**
		 * Include & Register Dependency Widgets.
		 */

		if (function_exists('mc4wp_get_forms')) {
			require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/form/mailchimp-form.php';

			Plugin::instance()->widgets_manager->register(new Widget_Mailchimp_Form());
		}

		if (defined('WPCF7_VERSION')) {
			require_once JOBPORTAL_ELEMENTOR_DIR . '/widgets/form/contact-form-7.php';

			Plugin::instance()->widgets_manager->register(new Widget_Contact_Form_7());
		}
	}

	/**
	 * @param \Elementor\Widgets_Manager $widgets_manager
	 *
	 * Remove unwanted widgets
	 */
	function remove_unwanted_widgets($widgets_manager)
	{
		$elementor_widget_blacklist = array(
			'theme-site-logo',
		);

		foreach ($elementor_widget_blacklist as $widget_name) {
			$widgets_manager->unregister_widget_type($widget_name);
		}
	}
}

Widget_Init::instance()->initialize();
