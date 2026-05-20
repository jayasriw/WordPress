<?php
$column_count = 1;
while ($jobportal_query->have_posts()) :
	$jobportal_query->the_post();
	$classes = array('grid-item', 'post-item');

	$size = JobPortal_Image::elementor_parse_image_size($settings, '480x356');

	if (3 === $column_count) {
		$classes[] = 'highlight-item';
		$size      = '500x680';
	}
?>
	<div <?php post_class(implode(' ', $classes)); ?> <?php if (3 === $column_count) : ?> data-width="2" <?php endif; ?>>
		<div class="post-wrapper jobportal-box">

			<?php if (has_post_thumbnail()) { ?>
				<div class="post-feature post-thumbnail jobportal-image">
					<a href="<?php the_permalink(); ?>">
						<?php JobPortal_Image::the_post_thumbnail(array('size' => $size)); ?>
					</a>
				</div>
			<?php } ?>

			<div class="post-caption">
				<?php JobPortal_Post::instance()->the_category(); ?>

				<h3 class="post-title">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h3>

				<div class="post-meta">
					<div class="inner">
						<?php JobPortal_Post::instance()->meta_date_template(); ?>

						<?php JobPortal_Post::instance()->meta_view_count_template(); ?>
					</div>
				</div>
			</div>

		</div>
	</div>
<?php
	$column_count++;

	if ($column_count > 3) {
		$column_count = 1;
	}
endwhile;
