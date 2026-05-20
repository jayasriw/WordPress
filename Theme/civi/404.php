<?php

/**
 * The template for displaying 404 pages (not found).
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 */

get_header();

$type = Civi_Helper::get_setting('page_404_type');
$title = Civi_Helper::get_setting('page_404_title');
$des = Civi_Helper::get_setting('page_404_des');
$btn = Civi_Helper::get_setting('page_404_btn');
$image = Civi_Helper::get_setting('page_404_image');

if ($type !== '') {
	if (defined('ELEMENTOR_VERSION')) {
		echo \Elementor\Plugin::$instance->frontend->get_builder_content($type);
	} else {
		$page404 = get_post($type);
		if (!empty($page404->post_content)) {
			echo wp_kses_post($page404->post_content);
		}
	}
} else { ?>
	<div class="main-content content-page page-404">
		<div class="container">
			<div class="site-layout">
				<div class="area-404 align-center">
					<h2><?php echo $title; ?></h2>
					<p><?php echo $des; ?></p>
					<img src="<?php echo $image; ?>" alt="<?php esc_attr_e('Image 404', 'civi'); ?>">
					<a class="civi-button button-outline-accent button-icon-right" href="<?php echo esc_url(home_url()); ?>">
						<?php echo $btn; ?>
						<i class="fas fa-chevron-right"></i>
					</a>
				</div>
			</div>
		</div>
	</div>
<?php } ?>

<?php
get_footer();
