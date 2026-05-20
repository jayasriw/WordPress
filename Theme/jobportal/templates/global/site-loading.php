<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

$type_loading_effect      = JobPortal_Helper::get_setting('type_loading_effect');
$animation_loading_effect = JobPortal_Helper::get_setting('animation_loading_effect');
$image_loading_effect     = JobPortal_Helper::get_setting('image_loading_effect');

$args = array('css-1'  => '<span class="jobportal-ldef-circle jobportal-ldef-loading"><span></span></span>', 'css-2'  => '<span class="jobportal-ldef-dual-ring jobportal-ldef-loading"></span>', 'css-3' => '<span class="jobportal-ldef-facebook jobportal-ldef-loading"><span></span><span></span><span></span></span>', 'css-4'  => '<span class="jobportal-ldef-heart jobportal-ldef-loading"><span></span></span>', 'css-5'  => '<span class="jobportal-ldef-ring jobportal-ldef-loading"><span></span><span></span><span></span><span></span></span>', 'css-6'  => '<span class="jobportal-ldef-roller jobportal-ldef-loading"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></span>', 'css-7'  => '<span class="jobportal-ldef-default jobportal-ldef-loading"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></span>', 'css-8'  => '<span class="jobportal-ldef-ellipsis jobportal-ldef-loading"><span></span><span></span><span></span><span></span></span>', 'css-9'  => '<span class="jobportal-ldef-grid jobportal-ldef-loading"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></span>', 'css-10'  => '<span class="jobportal-ldef-hourglass jobportal-ldef-loading"></span>', 'css-11'  => '<span class="jobportal-ldef-ripple jobportal-ldef-loading"><span></span><span></span></span>', 'css-12'  => '<span class="jobportal-ldef-spinner jobportal-ldef-loading"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></span>');

?>

<?php if ($type_loading_effect !== 'none') { ?>

	<div class="page-loading-effect">
		<div class="bg-overlay"></div>

		<div class="entry-loading">
			<?php if ($type_loading_effect == 'css_animation') { ?>
				<?php echo wp_kses($args[$animation_loading_effect], JobPortal_Helper::jobportal_kses_allowed_html()); ?>
			<?php } ?>

			<?php if ($type_loading_effect == 'image') { ?>
				<img src="<?php echo esc_url($image_loading_effect); ?>" alt="<?php esc_attr_e('Image Effect', 'jobportal'); ?>">
			<?php } ?>
		</div>
	</div>

<?php } ?>