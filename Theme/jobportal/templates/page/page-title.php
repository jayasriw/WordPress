<?php

/**
 * The template for displaying page title
 */

$page_title = $page_title_des = $page_title_blog_name = '';
$page_title_classes = array();
$page_title_css = 'page-title-orther';
$page_title_show = get_post_meta(get_the_ID(), "jobportal-page_title_show', true);
if ($page_title_show == '0') {
	return;
}



if (is_home()) {
	$page_title_blog_name   = JobPortal_Helper::get_setting('page_title_blog_name');
	$enable_page_title_blog = JobPortal_Helper::get_setting('enable_page_title_blog');
	$page_title             = $page_title_blog_name;
	$page_title_css 		= 'page-title-blog';

	if (empty($enable_page_title_blog)) {
		$page_title_classes[] = 'hide';
	}
}

if (!is_singular() && !is_front_page()) {
	if (!is_404()) {
		$page_title_css = 'page-title-blog';
	}
	if (is_404()) {
		$page_title_css 	  = 'page-title-other';
		$page_title           = esc_html__('404 Error', 'jobportal');
		$page_title_des       = esc_html__("Sorry, we couldn't find that page.", 'jobportal');
	} elseif (is_tag()) {
		$page_title = single_tag_title(esc_html__("Tags: ", 'jobportal'), false);
	} elseif (is_category() || is_tax()) {
		$page_title = single_cat_title('', false);
	} elseif (is_author()) {
		global $wp_query;
		$current_author = $wp_query->get_queried_object();
		$current_author_meta = get_user_meta($current_author->ID);
		if (empty($current_author->first_name) && empty($current_author->last_name)) {
			$page_title = $current_author->user_login;
		} else {
			$page_title = $current_author->first_name . ' ' . $current_author->last_name;
		}
	} elseif (is_day()) {
		$page_title = sprintf(esc_html__('Daily Archives: %s', 'jobportal'), get_the_date());
	} elseif (is_month()) {
		$page_title = sprintf(esc_html__('Monthly Archives: %s', 'jobportal'), get_the_date(_x('F Y', 'monthly archives date format', 'jobportal')));
	} elseif (is_year()) {
		$page_title = sprintf(esc_html__('Yearly Archives: %s', 'jobportal'), get_the_date(_x('Y', 'yearly archives date format', 'jobportal')));
	} elseif (is_search()) {
		$key = isset($_GET['s']) ? JobPortal_Helper::jobportal_clean(wp_unslash($_GET['s'])) : '';
		$page_title = sprintf(esc_html__('Search Results: "%s"', 'jobportal'), $key);
	} elseif (is_tax('post_format', 'post-format-aside')) {
		$page_title = esc_html__('Asides', 'jobportal');
	} elseif (is_tax('post_format', 'post-format-gallery')) {
		$page_title = esc_html__('Galleries', 'jobportal');
	} elseif (is_tax('post_format', 'post-format-image')) {
		$page_title = esc_html__('Images', 'jobportal');
	} elseif (is_tax('post_format', 'post-format-video')) {
		$page_title = esc_html__('Videos', 'jobportal');
	} elseif (is_tax('post_format', 'post-format-quote')) {
		$page_title = esc_html__('Quotes', 'jobportal');
	} elseif (is_tax('post_format', 'post-format-link')) {
		$page_title = esc_html__('Links', 'jobportal');
	} elseif (is_tax('post_format', 'post-format-status')) {
		$page_title = esc_html__('Statuses', 'jobportal');
	} elseif (is_tax('post_format', 'post-format-audio')) {
		$page_title = esc_html__('Audios', 'jobportal');
	} elseif (is_tax('post_format', 'post-format-chat')) {
		$page_title = esc_html__('Chats', 'jobportal');
	}
}

if (is_singular()) {
	if (!$page_title) {
		$page_title = get_the_title(get_the_ID());
	}
}

$page_title_classes[] = $page_title_css;

?>

<div class="page-title <?php echo join(' ', $page_title_classes); ?>">
	<div class="container">
		<div class="entry-detail">
			<?php get_template_part('templates/global/breadcrumb'); ?>
			<?php if (!empty($page_title)) { ?>
				<h1 class="entry-title">
					<?php echo wp_kses($page_title, JobPortal_Helper::jobportal_kses_allowed_html()); ?>
				</h1>
			<?php } ?>

			<?php if (!empty($page_title_des)) { ?>
				<div class="sub-title">
					<p><?php esc_html_e($page_title_des); ?></p>
				</div>
			<?php } ?>
		</div>
	</div>
</div>